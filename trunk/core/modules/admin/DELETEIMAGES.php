<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
 * DELETEREIMAGES class
 *
 * Delete images
 */
class DELETEIMAGES
{
    private $db;
    private $vars;
    private $messages;
    private $errors;
    private $success;
    private $session;
    private $badInput;
    private $gatekeep;
    private $usedImages = [];
    private $unusedImages = [];
    private $numUsedImages = 0;
    private $numUnusedImages = 0;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
    }
    /**
     * check we are allowed to delete and load appropriate method
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        $this->gatekeep->requireSuper = TRUE; // only admins can delete images if set to TRUE
        $this->gatekeep->init();
        if (array_key_exists('function', $this->vars)) {
            $function = $this->vars['function'];
            $this->{$function}();
        } else {
            $this->display();
        }
    }
    /**
     * display select box of images to delete
     *
     * @param false|string $message
     */
    private function display($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminImages"));
        $pString = $message ? $message : FALSE;
        if (!$this->grabImages()) {
            $pString .= $this->messages->text("misc", "noImages");
        } else {
            $pString .= \HTML\tableStart();
            $pString .= \HTML\trStart();
            if (!empty($this->usedImages)) {
            	$pString .= \HTML\td($this->displayUsedSelect());
            }
            if (!empty($this->unusedImages)) {
            	$pString .= \HTML\td($this->displayUnusedSelect());
            }
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
        }

        \AJAX\loadJavascript([WIKINDX_URL_BASE . '/core/modules/list/searchSelect.js?ver=' . WIKINDX_PUBLIC_VERSION]);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display used images select box
     *
     * @return string
     */
    private function displayUsedSelect()
    {
        $pString = \HTML\tableStart('generalTable');
        $pString .= \HTML\trStart();
		$td = \FORM\formHeader('admin_DELETEIMAGES_CORE');
		$td .= \FORM\hidden('function', 'process');
		$size = $this->numUsedImages > 20 ? 20 : $this->numUsedImages;
		$jScript = 'index.php?action=admin_DELETEIMAGES_CORE&method=displayUsedImages';
		$jsonArray[] = [
			'startFunction' => 'triggerFromMultiSelect',
			'script' => "$jScript",
			'triggerField' => 'used_image_ids',
			'targetDiv' => 'usedDiv',
		];
		$js = \AJAX\jActionForm('onclick', $jsonArray);
		$td .= \FORM\selectFBoxValueMultiple(
				$this->messages->text("misc", "usedImages"), 
				"used_image_ids", 
				$this->usedImages, 
				$size, 
				80, 
				$js
			) .
			BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint') . BR .
			BR . \FORM\formSubmit($this->messages->text("submit", "Delete"));
		$td .= \FORM\formEnd();
        $pString .= \HTML\td($td);
        $pString .= \HTML\td(\HTML\div('usedDiv', $this->displayUsedImages(TRUE)), 'left top width80percent');
		$pString .= \HTML\trEnd();
		$pString .= \HTML\tableEnd();
    	return $pString;
    }
    /**
     * Display unused images select box
     *
     * @return string
     */
    private function displayUnusedSelect()
    {
        $pString = \HTML\tableStart('generalTable');
        $pString .= \HTML\trStart();
		$td = \FORM\formHeader('admin_DELETEIMAGES_CORE');
		$td .= \FORM\hidden('function', 'process');
		$size = $this->numUnusedImages > 20 ? 20 : $this->numUnusedImages;
		$jScript = 'index.php?action=admin_DELETEIMAGES_CORE&method=displayUnusedImages';
		$jsonArray[] = [
			'startFunction' => 'triggerFromMultiSelect',
			'script' => "$jScript",
			'triggerField' => 'unused_image_ids',
			'targetDiv' => 'unusedDiv',
		];
		$js = \AJAX\jActionForm('onclick', $jsonArray);
		$td .= \FORM\selectFBoxValueMultiple(
				$this->messages->text("misc", "unusedImages"), 
				"unused_image_ids", 
				$this->unusedImages, 
				$size, 
				80, 
				$js
			) .
			BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint') . BR .
			BR . \FORM\formSubmit($this->messages->text("submit", "Delete"));
		$td .= \FORM\formEnd();
        $pString .= \HTML\td($td);
        $pString .= \HTML\td(\HTML\div('unusedDiv', $this->displayUnusedImages(TRUE)), 'left top width80percent');
		$pString .= \HTML\trEnd();
		$pString .= \HTML\tableEnd();
    	return $pString;
    }
    /**
     * Display thumbnails of selected used images
     *
     * @param bool $initialState Default FALSE
     * @return string
     */
    public function displayUsedImages($initialState = FALSE)
    {
    	$imageArray = array_keys($this->usedImages);
    	$pString = $this->displayImages($imageArray, $initialState);
		if ($initialState) {
	    	return $pString;
	    }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $pString]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Display thumbnails of selected unused images
     *
     * @param bool $initialState Default FALSE
     * @return string
     */
    public function displayUnusedImages($initialState = FALSE)
    {
    	$imageArray = array_keys($this->unusedImages);
    	$pString = $this->displayImages($imageArray, $initialState);
		if ($initialState) {
	    	return $pString;
	    }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $pString]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Display thumbnails of images
     *
     * @param array of images
     * @param bool $initialState
     * @return string
     */
    private function displayImages($imageArray, $initialState)
    {
    	$numCells = 0;
    	$maxCells = 3;
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
    	if ($initialState) { // grab first image in array (i.e. what is initially selected in the select box)
    	    $image = array_shift($imageArray);
    		$imagePath = WIKINDX_DIR_DATA_IMAGES . DIRECTORY_SEPARATOR . $image;
    		$imageUrl = WIKINDX_URL_DATA_IMAGES . DIRECTORY_SEPARATOR . $image;
    		list($width, $height) = $this->imageWH($imagePath);
    		$pString .= \HTML\td(\HTML\img($imageUrl, $width, $height));
    	}
    	else {
    		$array = explode(',', $this->vars['ajaxReturn']);
    		foreach ($array as $image) {
    			if ($numCells == $maxCells) {
    				$pString .= \HTML\trEnd();
    				$pString .= \HTML\trStart();
    				$numCells = 0;
    			}
    			$imagePath = WIKINDX_DIR_DATA_IMAGES . DIRECTORY_SEPARATOR . $image;
    			$imageUrl = WIKINDX_URL_DATA_IMAGES . DIRECTORY_SEPARATOR . $image;
				list($width, $height) = $this->imageWH($imagePath);
				$pString .= \HTML\td(\HTML\img($imageUrl, $width, $height));
				++$numCells;
    		}
    	}
    	while ($numCells < $maxCells) {
    		$pString .= \HTML\td('&nbsp;');
    		++$numCells;
    	}
		$pString .= \HTML\trEnd();
		$pString .= \HTML\tableEnd();
		return $pString;
    }
    /**
     * Return limited image width and height
     * @param string $image
     * @return array [width, height]
     */
    private function imageWH($image)
    {
    	$max_width = 100;
        $max_height = 100;
		$size = getimagesize($image);
		$width = $size[0];
        $height = $size[1];

        $new_width = $max_width;
        $new_height = $max_height;
        if (($width / $height) > ($new_width / $new_height)) {
            $new_height = $new_width * ($height / $width);
        } else {
            $new_width = $new_height * ($width / $height);
        }

        if ($new_width >= $width && $new_height >= $height) {
            $new_width = $width;
            $new_height = $height;
        }
    	return [$new_width, $new_height];
    }
    /**
     * Grab all images in array.
     *
     * key is the file name, value is the display
     *
     * @return bool
     */
    private function grabImages()
    {
        $encodeExplorer = new EncodeExplorer();
        $encodeExplorer->init();
        $location = new Location();
        $fileManager = new FileManager();
        $fileManager->run($location);
        $files = $encodeExplorer->run($location, TRUE);
        foreach ($files as $file) {
            $stmts = [];
            $fileName = rawurlencode($file->getName());
            $this->db->formatConditions($this->db->formatFields('resourcemetadataText') . $this->db->like('%', $fileName, '%'));
            $stmts[] = $this->db->selectNoExecute('resource_metadata', [['resourcemetadataId' => 'id']], TRUE, TRUE, TRUE);
            $this->db->formatConditions($this->db->formatFields('resourcetextAbstract') . $this->db->like('%', $fileName, '%'));
            $stmts[] = $this->db->selectNoExecute('resource_text', [['resourcetextId' => 'id']], TRUE, TRUE, TRUE);
            $this->db->formatConditions($this->db->formatFields('resourcetextNote') . $this->db->like('%', $fileName, '%'));
            $stmts[] = $this->db->selectNoExecute('resource_text', [['resourcetextId' => 'id']], TRUE, TRUE, TRUE);
            $this->db->formatConditions(['configName' => 'configDescription']);
            $this->db->formatConditions($this->db->formatFields('configText') . $this->db->like('%', $fileName, '%'));
            $stmts[] = $this->db->selectNoExecute('config', [['configId' => 'id']], TRUE, TRUE, TRUE);
            $resultSet = $this->db->query($this->db->union($stmts));
            if ($this->db->numRows($resultSet)) {
                $this->usedImages[$file->getName()] = EncodeExplorer::getFilename($file) . ' (' . \FILE\formatSize($file->getSize()) . ')';
                ++$this->numUsedImages;
            } else {
                $this->unusedImages[$file->getName()] = EncodeExplorer::getFilename($file) . ' (' . \FILE\formatSize($file->getSize()) . ')';
                ++$this->numUnusedImages;
            }
        }

        return ($this->numUsedImages || $this->numUnusedImages);
    }
    /*
    * process
    */
    private function process()
    {
        if (empty($array = $this->validateInput())) {
            $this->display($this->errors->text("inputError", "missing"));
            FACTORY_CLOSE::getInstance();
        }
        foreach ($array as $image) {
            $fileName = WIKINDX_DIR_DATA_IMAGES . DIRECTORY_SEPARATOR . $image;
			@unlink($fileName);
// Deal with metadata using the image
			$message = "[Image deleted by WIKINDX Administrator]";
			$image = rawurlencode($image);
            $this->db->formatConditions($this->db->formatFields('resourcemetadataText') . $this->db->like('%', $image, '%'));
            $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataText'], TRUE);
            while ($row = $this->db->fetchRow($resultset)) {
        		$text = preg_replace("/<img.*$image.*>/Uusi", $message, $row['resourcemetadataText']);
        		$this->db->formatConditions(['resourcemetadataId' => $row['resourcemetadataId']]);
        		$this->db->update('resource_metadata', ['resourcemetadataText' => $text]);
            }
            $this->db->formatConditions($this->db->formatFields('resourcetextAbstract') . $this->db->like('%', $image, '%'));
            $resultset = $this->db->select('resource_text', ['resourcetextId', 'resourcetextAbstract'], TRUE);
            while ($row = $this->db->fetchRow($resultset)) {
        		$text = preg_replace("/<img.*$image.*>/Uusi", $message, $row['resourcetextAbstract']);
        		$this->db->formatConditions(['resourcetextId' => $row['resourcetextId']]);
        		$this->db->update('resource_text', ['resourcetextAbstract' => $text]);
            }
            $this->db->formatConditions($this->db->formatFields('resourcetextNote') . $this->db->like('%', $image, '%'));
            $resultset = $this->db->select('resource_text', ['resourcetextId', 'resourcetextNote'], TRUE);
            while ($row = $this->db->fetchRow($resultset)) {
        		$text = preg_replace("/<img.*$image.*>/Uusi", $message, $row['resourcetextNote']);
        		$this->db->formatConditions(['resourcetextId' => $row['resourcetextId']]);
        		$this->db->update('resource_text', ['resourcetextNote' => $text]);
            }
            $this->db->formatConditions(['configName' => 'configDescription']);
            $this->db->formatConditions($this->db->formatFields('configText') . $this->db->like('%', $image, '%'));
            $resultset = $this->db->select('config', ['configId', 'configText'], TRUE);
            while ($row = $this->db->fetchRow($resultset)) {
        		$text = preg_replace("/<img.*$image.*>/Uusi", $message, $row['configText']);
        		$this->db->formatConditions(['configId' => $row['configId']]);
        		$this->db->update('config', ['configText' => $text]);
            }
        }
        $pString = $this->success->text("imageDelete");
        $this->display($pString);
    }
    /**
     * validate input
     *
     * @return bool
     */
    private function validateInput()
    {
    	$array = [];
        if (array_key_exists('used_image_ids', $this->vars)) {
        	return $this->vars['used_image_ids'];
        }
        if (array_key_exists('unused_image_ids', $this->vars)) {
        	return $this->vars['unused_image_ids'];
        }
        return $array;
    }
}
