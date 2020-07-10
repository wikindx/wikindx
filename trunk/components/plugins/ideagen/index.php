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
  * ideagen class.
  *
  * Idea Generator
  *
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");


class ideagen_MODULE
{
    // The dump directory is the cache dircetory of this plugin
    const DUMP_DIRECTORY = WIKINDX_DIR_CACHE_PLUGINS . DIRECTORY_SEPARATOR . "backupmysql";

    public $authorize;
    public $menus;
    
    private $pluginmessages;
    private $coremessages;
    private $config;
    private $vars;
    private $db;
    private $session;
    private $cite;
    private $bibStyle;
    private $common;
    private $metadata;
    private $icons;
    private $quotesExist;
    private $quoteCommentsExist;
    private $paraphrasesExist;
    private $paraphraseCommentsExist;
    private $musingsExist;
    private $ideasExist;
    private $storedId = FALSE;
    private $lastId = FALSE;
    

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        include_once("core/messages/PLUGINMESSAGES.php");
        $this->pluginmessages = new PLUGINMESSAGES('ideagen', 'ideagenMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "config.php");
        $this->config = new ideagen_CONFIG();
        $this->authorize = $this->config->authorize;
        if ($menuInit) {
            $this->makeMenu($this->config->menus);
            return; // Need do nothing more as this is simply menu initialisation.
        }

        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize)) { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('heading'));
        $this->metadata = FACTORY_METADATA::getInstance();
        $this->db = FACTORY_DB::getInstance();
		if (!$this->checkMetadata()) {
        	GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text('noMetadata'), 'error'));
			FACTORY_CLOSE::getInstance(); // die
		}
    	$this->quotesExist = $this->quoteCommentsExist = $this->paraphrasesExist = $this->paraphraseCommentsExist = 
    		$this->musingsExist = $this->ideasExist = TRUE;
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->session->delVar("list_IdeaAllThreadIds");
        $this->cite = FACTORY_CITE::getInstance();
        $this->bibStyle = FACTORY_BIBSTYLE::getInstance();
        $this->common = FACTORY_RESOURCECOMMON::getInstance();
        $this->icons = FACTORY_LOADICONS::getInstance();
    }
    /**
     * This is the initial method called from the menu item
     */
    public function init()
    {
        return $this->display();
    }
    /**
     * display
     *
     * @param mixed $message
     */
    public function display()
    {
        $pString = FORM\formHeader("ideagen_generate");
        $pString .= HTML\p($this->pluginmessages->text('description'));
        $pString .= HTML\p(FORM\formSubmit($this->pluginmessages->text('generate')));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Select and display a metadata pair
     *
     */
    public function generate($message = FALSE)
    {
        if ($message) {
            $pString = $message;
        } else {
            $pString = '';
        }
        $pString .= FORM\formHeader("ideagen_generate");
        $pString .= HTML\p(FORM\formSubmit($this->pluginmessages->text('generateAgain')));
        $pString .= FORM\formEnd();
        $pString .= HTML\tableStart('generalTable');
        while (($returnSF = $this->selectFunction()) === FALSE) { // try again
    		$this->selectFunction();
    	}
        $return[] = $returnSF;
    	$this->storedId = $this->lastId;
        do {
        	$return[] = $this->selectFunction();
    		if ($this->storedId === $this->lastId) { // Is this metadataId the same as the last one?
    			array_pop($return); // Force another iteration . . .
    		}
        } while (sizeof($return) < 2);
        $count = 0;
        foreach ($return as $string)
        {
        	$pString .= $string;
        	++$count;
        	if ($count == 1) {
        	$pString .= HTML\trStart();
            $pString .= HTML\td(HTML\hr(), '', 3);
            $pString .= HTML\trEnd();
        	}
        }
        $pString .= HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
        GLOBALS::addTplVar('content', HTML\p(HTML\h($this->pluginmessages->text('addIdea'))));
        $this->ideaAdd();
    }
    /**
     * display the adding form for a new idea
     */
    public function ideaAdd()
    {
		include_once("core/modules/resource/TEXTQP.php");
        $textqp = new TEXTQP();
        $text = $metadataId = $owner = FALSE;
        $thisUserId = $this->session->getVar("setup_UserId");
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $pString = $tinymce->loadMetadataTextarea(['Text']);
        $pString .= \FORM\formHeader('ideas_IDEAS_CORE');
        $hidden = \FORM\hidden('method', 'edit');
        $idea['hidden'] = $pString;
		$private = 'Y';
        $idea['keyword'] = $hidden . $textqp->displayKeywordForm('idea', 'resourcemetadataId');
        // The second parameter ('Text') to textareaInput is the textarea name
        $idea['idea'] = \FORM\textareaInput(FALSE, 'Text', $text, 80, 10);
        $idea['ideaTitle'] = $this->coremessages->text("metadata", 'idea');
        $this->db->formatConditions(['usergroupsusersUserId' => $thisUserId]);
        $this->db->leftJoin('user_groups', 'usergroupsId', 'usergroupsusersGroupId');
        $recordset3 = $this->db->select('user_groups_users', ['usergroupsusersGroupId', 'usergroupsTitle']);
        $privateArray = ['Y' => $this->coremessages->text("resources", "private"),
            'N' => $this->coremessages->text("resources", "public"), ];
        if ($this->db->numRows($recordset3)) {
            while ($row = $this->db->fetchRow($recordset3)) {
                $privateArray[$row['usergroupsusersGroupId']] =
                    $this->coremessages->text("resources", "availableToGroups", \HTML\dbToFormTidy($row['usergroupsTitle']));
            }
            $idea['form']['private'] = \FORM\selectedBoxValue(
                $this->coremessages->text("resources", "ideaPrivate"),
                "private",
                $privateArray,
                $private,
                3
            );
        } else {
            $idea['form']['private'] = \FORM\selectedBoxValue(
                $this->coremessages->text("resources", "ideaPrivate"),
                "private",
                $privateArray,
                $private,
                2
            );
        }
        $idea['form']['submit'] = \FORM\formSubmit($this->coremessages->text("submit", "Save"));
        $idea['formfoot'] = \FORM\formEnd();
        $this->session->delVar("ideaLock");
        GLOBALS::addTplVar('ideaTemplate', TRUE);
        GLOBALS::addTplVar('idea', $idea);
    }
    /**
    * select the random function
    */
    private function selectFunction()
    {
    	$metadataArray = ['randomQuote', 'randomQuoteComment', 'randomParaphrase', 'randomParaphraseComment', 'randomMusing', 'randomIdea'];
    	if (!$this->quotesExist) {
    		unset($metadataArray[array_search('randomQuote', $metadataArray)]);
    	}
    	if (!$this->quoteCommentsExist) {
    		unset($metadataArray[array_search('randomQuoteComment', $metadataArray)]);
    	}
    	if (!$this->paraphrasesExist) {
    		unset($metadataArray[array_search('randomParaphrase', $metadataArray)]);
    	}
    	if (!$this->paraphraseCommentsExist) {
    		unset($metadataArray[array_search('randomParaphraseComment', $metadataArray)]);
    	}
    	if (!$this->musingsExist) {
    		unset($metadataArray[array_search('randomMusing', $metadataArray)]);
    	}
    	if (!$this->ideasExist) {
    		unset($metadataArray[array_search('randomIdea', $metadataArray)]);
    	}
    	$function = $metadataArray[array_rand($metadataArray, 1)];
    	$return = $this->{$function}();
    	return $return;
    }
    /**
     * Select a random quote ID for viewing
     */
    private function randomQuote()
    {
        $this->db->formatConditions(['resourcemetadataType' => 'q']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']);
        if (!$this->db->numRows($resultset)) {
        	$this->quotesExist = FALSE;
            return FALSE;
        }
        $row = $this->db->fetchRow($resultset);
        $this->lastId = $row['resourcemetadataId'];
        return $this->getQPMString($row, HTML\strong($this->pluginmessages->text('quote')));
    }
    /**
     * Select a random quote comment ID for viewing
     */
    private function randomQuoteComment()
    {
        $this->db->formatConditions(['resourcemetadataType' => 'qc']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', 
        	['resourcemetadataId', 'resourcemetadataMetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']);
        if (!$this->db->numRows($resultset)) {
        	$this->quoteCommentsExist = FALSE;
            return FALSE;
        }
        $row = $this->db->fetchRow($resultset);
        $this->lastId = $row['resourcemetadataId'];
        return $this->getQPMString($row, HTML\strong($this->pluginmessages->text('quoteComment')));
    }
    /**
     * Select a random paraphrase ID for viewing
     */
    private function randomParaphrase()
    {
        $this->db->formatConditions(['resourcemetadataType' => 'p']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']);
        if (!$this->db->numRows($resultset)) {
        	$this->paraphrasesExist = FALSE;
            return FALSE;
        }
        $row = $this->db->fetchRow($resultset);
        $this->lastId = $row['resourcemetadataId'];
        return $this->getQPMString($row, HTML\strong($this->pluginmessages->text('paraphrase')));
    }
    /**
     * Select a random paraphrase comment ID for viewing
     */
    private function randomParaphraseComment()
    {
        $this->db->formatConditions(['resourcemetadataType' => 'pc']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', 
        	['resourcemetadataId', 'resourcemetadataMetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']);
        if (!$this->db->numRows($resultset)) {
        	$this->paraphraseCommentsExist = FALSE;
            return FALSE;
        }
        $row = $this->db->fetchRow($resultset);
        $this->lastId = $row['resourcemetadataId'];
        return $this->getQPMString($row, HTML\strong($this->pluginmessages->text('paraphraseComment')));
    }
    /**
     * Select a random musing ID for viewing
     */
    private function randomMusing()
    {
        if ($userId = $this->session->getVar("setup_UserId")) {
            $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
            $this->db->formatConditions($this->db->formatFields('usergroupsusersGroupId') . $this->db->equal .
                $this->db->formatFields('resourcemetadataPrivate'));
            $subSql = $this->db->selectNoExecute('user_groups_users', 'usergroupsusersId', FALSE, TRUE, TRUE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('N')
                . $this->db->and .
                $this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('Y');
            $case1 = $this->db->caseWhen($subject, FALSE, $subSql, FALSE, FALSE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('Y');
            $result = $this->db->formatFields('resourcemetadataAddUserId') . $this->db->equal . $this->db->tidyInput($userId);
            $case2 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('N');
            $result = $this->db->tidyInput(1);
            $case3 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $this->db->formatConditions($case1 . $this->db->or . $case2 . $this->db->or . $case3);
        } elseif ($this->session->getVar("setup_ReadOnly")) {
            $this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
        }
        $this->db->formatConditions(['resourcemetadataType' => 'm']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']);
        if (!$this->db->numRows($resultset)) {
        	$this->musingsExist = FALSE;
            return FALSE;
        }
        $row = $this->db->fetchRow($resultset);
        $this->lastId = $row['resourcemetadataId'];
        return $this->getQPMString($row, HTML\strong($this->pluginmessages->text('musing')));
    }
    /**
    * Get string from quote, paraphrase, or musing
    */
    private function getQPMString($row, $label)
    {
    	if (!$row['resourcemetadataResourceId']) {// i.e. quote or paraphrase comment
    		$this->db->formatConditions(['resourcemetadataId' => $row['resourcemetadataMetadataId']]);
        	$resourceId = $this->db->selectFirstField('resource_metadata', 'resourcemetadataResourceId');
    	}
    	else {
	        $resourceId = $row['resourcemetadataResourceId'];
	    }
        $text = $this->cite->parseCitations($row['resourcemetadataText'], 'html');
        $text = HTML\tableStart("generalTable") . HTML\trStart() . HTML\td($text) . HTML\trEnd(). HTML\tableEnd();
        $resultset = $this->common->getResource($resourceId);
        $row = $this->db->fetchRow($resultset);
    	$resource = $this->bibStyle->process($row);
    	$td = HTML\trStart();
    	$td .= HTML\td($label);
        $td .= HTML\td($resource . BR . $text);
        $td .= HTML\td(\HTML\a($this->icons->getClass("viewmeta"), $this->icons->getHTML("viewmeta"), 
        	"index.php?action=resource_RESOURCEVIEW_CORE" . htmlentities("&id=" . $resourceId)));
        $td .= HTML\trEnd();
    	return $td;
    }
    /**
     * Select a random idea ID for viewing
     */
    private function randomIdea()
    {
    	if (!$this->metadata->setCondition('i') && $this->session->getVar("setup_ReadOnly")) {
            $this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
        }
        $this->db->formatConditions(['resourcemetadataType' => 'i']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataText', 'resourcemetadataId']);
        if (!$this->db->numRows($resultset)) {
        	$this->ideasExist = FALSE;
            return FALSE;
        }
        $row = $this->db->fetchRow($resultset);
        $this->lastId = $row['resourcemetadataId'];
        $text = $this->cite->parseCitations($row['resourcemetadataText'], 'html');
        $text = HTML\tableStart("generalTable") . HTML\trStart() . HTML\td($text) . HTML\trEnd(). HTML\tableEnd();
    	$td = HTML\trStart();
    	$td .= HTML\td(HTML\strong($this->pluginmessages->text('idea')));
        $td .= HTML\td($text);
        $td .= HTML\td(\HTML\a($this->icons->getClass("viewmeta"), $this->icons->getHTML("viewmeta"), 
        	"index.php?action=ideas_IDEAS_CORE" . htmlentities("&method=threadView&resourcemetadataId=" . $row['resourcemetadataId'])));
        $td .= HTML\trEnd();
        return $td;
    }
    /**
    * Check there are at least three items of metadata from which to select
    */
	private function checkMetadata()
	{
		$this->metadata->setCondition(FALSE, FALSE, TRUE);
		$this->db->limit(3, 0);
		$resultSet = $this->db->select('resource_metadata', 'resourcemetadataId');
		if ($this->db->numRows($resultSet) < 3) {
			return FALSE;
		}
		return TRUE;
	}
    /**
     * Make the menus
     *
     * @param mixed $menuArray
     */
    private function makeMenu($menuArray)
    {
        $this->menus = [$menuArray[0] => [$this->pluginmessages->text('menu') => "init"]];
    }
}
