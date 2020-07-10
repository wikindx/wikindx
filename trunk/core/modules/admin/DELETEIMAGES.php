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
                $td = \FORM\formHeader('admin_DELETEIMAGES_CORE');
                $td .= \FORM\hidden('function', 'process');
                $size = $this->numUsedImages > 20 ? 20 : $this->numUsedImages;
                $td .= \FORM\selectFBoxValueMultiple($this->messages->text("misc", "usedImages"), "image_ids", $this->usedImages, $size, 80) .
                    BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint') . BR .
                    BR . \FORM\formSubmit($this->messages->text("submit", "Delete"));
                $td .= \FORM\formEnd();
                $pString .= \HTML\td($td);
            }
            if (!empty($this->unusedImages)) {
                $td = \FORM\formHeader('admin_DELETEIMAGES_CORE');
                $td .= \FORM\hidden('function', 'process');
                $size = $this->numUnusedImages > 20 ? 20 : $this->numUnusedImages;
                $td .= \FORM\selectFBoxValueMultiple($this->messages->text("misc", "unusedImages"), "image_ids", $this->unusedImages, $size, 80) .
                    BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint') . BR .
                    BR . \FORM\formSubmit($this->messages->text("submit", "Delete"));
                $td .= \FORM\formEnd();
                $pString .= \HTML\td($td);
            }
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
        }

        GLOBALS::addTplVar('content', $pString);
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
        include_once("core/file/images.php");
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
        if (!$this->validateInput()) {
            $this->display($this->errors->text("inputError", "missing"));
            FACTORY_CLOSE::getInstance();
        }
        foreach ($this->vars['image_ids'] as $image) {
            @unlink(WIKINDX_DIR_DATA_IMAGES . DIRECTORY_SEPARATOR . $image);
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
        return array_key_exists('image_ids', $this->vars);
    }
}
