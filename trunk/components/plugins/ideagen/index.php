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
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));


class ideagen_MODULE
{
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
    private $formData = [];
    private $displayItem = FALSE;
    private $gotFirst = FALSE;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        $this->session = FACTORY_SESSION::getInstance();
        // Conform to admin's configuration
        if ($this->session->getVar("setup_Superadmin"))
        {
            $this->displayItem = TRUE;
        }
        elseif (WIKINDX_METADATA_USERONLY && $this->session->getVar("setup_UserId"))
        {
            $this->displayItem = TRUE;
        }
        elseif (WIKINDX_METADATA_ALLOW)
        {
            $this->displayItem = TRUE;
        }
        if (!$this->displayItem)
        {
            return;
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('ideagen', 'ideagenMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $this->config = new ideagen_CONFIG();
        $this->authorize = $this->config->authorize;
        if ($menuInit)
        {
            $this->makeMenu($this->config->menus);

            return; // Need do nothing more as this is simply menu initialisation.
        }

        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('heading'));
        $this->metadata = FACTORY_METADATA::getInstance();
        $this->db = FACTORY_DB::getInstance();
        if (!$this->checkMetadata())
        {
            GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text('noMetadata'), 'error'));
            FACTORY_CLOSE::getInstance(); // die
        }
        $this->quotesExist = $this->quoteCommentsExist = $this->paraphrasesExist = $this->paraphraseCommentsExist =
            $this->musingsExist = $this->ideasExist = TRUE;
        $this->vars = GLOBALS::getVars();
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
        $pString = $this->display();
        GLOBALS::addTplVar('content', $pString);
        return; 
    }
    /**
     * display
     *
     * @param bool $again
     * @param string $result 
     * @return string
     */
    public function display($again = FALSE, $result = '&nbsp;')
    {
        $pString = FORM\formHeader("ideagen_generate");
        $pString .= HTML\p($this->pluginmessages->text('description'));
        $jScript = 'index.php?action=ideagen_ideagenOptions';
    // Item 1
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'item1KWType',
            'targetDiv' => 'item1KWs',
        ];
        $js = AJAX\jActionForm('onchange', $jsonArray);
        $pString .= HTML\tableStart('generalTable borderStyleSolid');
        $pString .= HTML\trStart();
        $itemKWTypes = [
            'item1ResourceKW' => $this->pluginmessages->text('itemResourceKW'),
            'item1MetadataKW' => $this->pluginmessages->text('itemMetadataKW'),
        	];
        reset($itemKWTypes);
        $selected = $this->session->getVar("ideagen_Item1KWType") ? $this->session->getVar("ideagen_Item1KWType") : key($itemKWTypes);
        $td = FORM\selectedBoxValue(
            $this->pluginmessages->text('item1'),
            "item1KWType",
            $itemKWTypes,
            $selected,
            2,
            FALSE,
            $js
        	) . BR;
        $td .= $this->itemKWs($selected, 'item1');
        $pString .= HTML\td($td);
        unset($jsonArray);
    // Item 2
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'item2KWType',
            'targetDiv' => 'item2KWs',
        ];
        $js = AJAX\jActionForm('onchange', $jsonArray);
        $itemKWTypes = [
            'item2ResourceKW' => $this->pluginmessages->text('itemResourceKW'),
            'item2MetadataKW' => $this->pluginmessages->text('itemMetadataKW'),
        	];
        reset($itemKWTypes);
        $selected = $this->session->getVar("ideagen_Item2KWType") ? $this->session->getVar("ideagen_Item2KWType") : key($itemKWTypes);
        $td = FORM\selectedBoxValue(
            $this->pluginmessages->text('item2'),
            "item2KWType",
            $itemKWTypes,
            $selected,
            2,
            FALSE,
            $js
        	) . BR;
        $td .= $this->itemKWs($selected, 'item2');
        $pString .= HTML\td($td);
        $pString .= HTML\td('&nbsp;');
        $pString .= HTML\td($result);
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        if ($again) {
	        $pString .= HTML\p(FORM\formSubmit($this->pluginmessages->text('generateAgain')));
    	} else {
	        $pString .= HTML\p(FORM\formSubmit($this->pluginmessages->text('generate')));
	    }
        $pString .= FORM\formEnd();
        return $pString;
    }
    /**
     * ideagenOptions
     * AJAX DIV handler
     */
    public function ideagenOptions()
    {
        if (($this->vars['ajaxReturn'] == 'item1ResourceKW') || ($this->vars['ajaxReturn'] == 'item1MetadataKW')) {
        	$pString = $this->itemKWs($this->vars['ajaxReturn'], 'item1');
        } else {
        	$pString = $this->itemKWs($this->vars['ajaxReturn'], 'item2');
        }
        GLOBALS::addTplVar('content', AJAX\encode_jArray(['innerHTML' => $pString]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Get keywords for item
     *
     * @param string $selected
     * @param string $item
     */
    private function itemKWs($selected, $item)
    {
    	if ($item == 'item1') {
    		$divTitle = 'item1KW';
    		$selectTitle = 'item1KWs';
    		if ($selected == 'item1ResourceKW') {
	    		$session = 'ideagen_Item1ResourceKWs';
	    	} else {
	    		$session = 'ideagen_Item1MetadataKWs';
	    	}
    	} else {
    		$divTitle = 'item2KW';
    		$selectTitle = 'item2KWs';
    		if ($selected == 'item2ResourceKW') {
	    		$session = 'ideagen_Item2ResourceKWs';
	    	} else {
	    		$session = 'ideagen_Item2MetadataKWs';
	    	}
    	}
	    $keywords[0] = $this->coremessages->text("misc", "ignore");
        if (($selected == 'item1ResourceKW') || ($selected == 'item2ResourceKW')) {
	        $rKeywords = $this->getKeywords();
	        $keywords += $rKeywords;
	    } else {
	    	$mKeywords = $this->getKeywords(TRUE);
	    	$keywords += $mKeywords;
	    }
	    $hint = \HTML\span(\HTML\aBrowse(
			'green',
			'',
			$this->coremessages->text("hint", "hint"),
			'#',
			"",
			$this->coremessages->text("hint", "multiples")), 'hint');
        $select = unserialize($this->session->getVar($session));
        if (!is_array($select)) {
        	$select = [];
        }
        if (($key = array_search(0, $select)) === 0) {
    		unset($select[$key]);
    	}
        if (count($select)) {
			return HTML\div($divTitle, FORM\selectedBoxValueMultiple(
				FALSE,
				$selectTitle,
				$keywords,
				$select,
				10
				) . BR . $hint
			);
        } else {
        	return HTML\div($divTitle, FORM\selectFBoxValueMultiple(
				FALSE,
				$selectTitle,
				$keywords,
				10
				) . BR . $hint
			);
        }
    }
    /**
     * Get keywords
     * Ensure the keyword lists represent viable metadata available to this user
     *
     * @param bool default FALSE. TRUE is metadata keywords
     * @return array
     */
    private function getKeywords($meta = FALSE) 
    {
		$keywords = [];
		$this->setPrivateConditions();
		$this->db->leftJoin('resource_keyword', 'resourcekeywordKeywordId', 'keywordId');
		if (!$meta) { // Resource keywords
			$this->db->formatConditions(['resourcemetadataType' => 'i'], TRUE); // not ideas which are independent of resources
			$this->db->leftJoin('resource_metadata', 'resourcemetadataResourceId', 'resourcekeywordResourceId');
		} else { // Metadata keywords
			$this->db->formatConditions(['resourcekeywordMetadataId' => 'IS NOT NULL'], TRUE); // not ideas which are independent of resources
			$this->db->leftJoin('resource_metadata', 'resourcemetadataMetadataId', 'resourcekeywordMetadataId');
		}
		$this->db->orderBy('keywordKeyword');
		$resultSet = $this->db->select('keyword', ['keywordId', 'keywordKeyword'], TRUE);
		while ($row = $this->db->fetchRow($resultSet)) {
			$keywords[$row['keywordId']] = $row['keywordKeyword'];
		}
		return $keywords;
    }
    /**
     * Set database conditions where some entries might be private or available only to groups
     */
    private function setPrivateConditions()
    {
        if ($this->session->getVar("setup_ReadOnly"))
        {
            $this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
        }
        elseif ($userId = $this->session->getVar("setup_UserId"))
        {
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
        }
    }
    /**
     * Keyword Conditions
     *
     */
    private function keywordConditions()
    {
    	if (!$this->gotFirst && array_key_exists('item1KWs', $this->vars)) {
    		if (($key = array_search(0, $this->vars['item1KWs'])) === 0) {
    			unset($this->vars['item1KWs'][$key]);
    		}
    		if (!count($this->vars['item1KWs'])) {
    			return;
    		}
    		$this->db->formatConditionsOneField($this->vars['item1KWs'], 'resourcekeywordKeywordId');
    		$this->db->leftJoin('resource_keyword', 'resourcekeywordMetadataId', 'resourcemetadataId');
    	} else if (array_key_exists('item2KWs', $this->vars)) {
    		if (($key = array_search(0, $this->vars['item2KWs'])) === 0) {
    			unset($this->vars['item2KWs'][$key]);
    		}
    		if (!count($this->vars['item2KWs'])) {
    			return;
    		}
    		$this->db->formatConditionsOneField($this->vars['item2KWs'], 'resourcekeywordKeywordId');
    		$this->db->leftJoin('resource_keyword', 'resourcekeywordMetadataId', 'resourcemetadataId');
    	}
    }
    /**
     * Select and display a metadata pair
     */
    public function generate()
    {
        if (array_key_exists('uuid', $this->vars))
        {
            \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
        }
        $this->storeSession();
        $uuid = \TEMPSTORAGE\getUuid($this->db);
        $pString = HTML\tableStart('generalTable');
        while (($returnSF = $this->selectFunction()) === FALSE)
        { // try again
            $this->selectFunction();
        }
        $this->gotFirst = TRUE;
        $return[] = $returnSF;
        $this->storedId = $this->lastId;
        do
        {
            $return[] = $this->selectFunction();
            if ($this->storedId === $this->lastId)
            { // Is this metadataId the same as the last one?
                array_pop($return); // Force another iteration . . .
            }
        } while (sizeof($return) < 2);
        $count = 0;
        foreach ($return as $string)
        {
            $pString .= $string;
            ++$count;
            if ($count == 1)
            {
                $pString .= HTML\trStart();
                $pString .= HTML\td(HTML\hr(), '', 3);
                $pString .= HTML\trEnd();
            }
        }
        // Store the two metadata ideas in case there is an error when adding a new idea
        \TEMPSTORAGE\store($this->db, $uuid, ['id1' => $this->storedId, 'id2' => $this->lastId]);
        $pString .= HTML\tableEnd();
        $pString = $this->display(TRUE, $pString);
        GLOBALS::addTplVar('content', $pString);
        GLOBALS::addTplVar('content', HTML\h($this->pluginmessages->text('addIdea')));
        $this->ideaAdd($uuid);
    }
    /**
     * display the adding form for a new idea
     *
     * @param string $uuid
     * @param mixed $message
     */
    public function ideaAdd($uuid, $message = FALSE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "modules", "resource", "TEXTQP.php"]));
        $textqp = new TEXTQP();
        $textqp->type = 'idea';
        $text = $metadataId = $owner = FALSE;
        $thisUserId = $this->session->getVar("setup_UserId");
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $pString = $tinymce->loadMetadataTextarea(['Text']);
        $pString .= \FORM\formHeader('ideas_IDEAS_CORE');
        $hidden = \FORM\hidden('method', 'edit');
        $hidden .= \FORM\hidden('uuid', $uuid);
        $hidden .= \FORM\hidden('ideaGen', TRUE);
        $idea['hidden'] = $pString;
        $private = array_key_exists('private', $this->formData) ? $this->formData['private'] : 'Y';
        if (array_key_exists('keywords', $this->formData))
        {
            $textqp->formData['keywords'] = $this->formData['keywords'];
        }
        $idea['keyword'] = $hidden . $textqp->displayKeywordForm();
        // The second parameter ('Text') to textareaInput is the textarea name
        $idea['idea'] = \FORM\textareaInput(FALSE, 'Text', $text, 80, 10);
        $idea['ideaTitle'] = $this->coremessages->text("metadata", 'idea');
        $this->db->formatConditions(['usergroupsusersUserId' => $thisUserId]);
        $this->db->leftJoin('user_groups', 'usergroupsId', 'usergroupsusersGroupId');
        $recordset3 = $this->db->select('user_groups_users', ['usergroupsusersGroupId', 'usergroupsTitle']);
        $privateArray = ['Y' => $this->coremessages->text("resources", "private"),
            'N' => $this->coremessages->text("resources", "public"), ];
        if ($this->db->numRows($recordset3))
        {
            while ($row = $this->db->fetchRow($recordset3))
            {
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
        }
        else
        {
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
     * Error handling when adding an idea (missing Text)
     * Called from core/modules/ideas/IDEAS.php
     */
    public function ideaAddError()
    {
        $this->formData = \TEMPSTORAGE\fetch($this->db, $this->vars['uuid']);
        $this->storedId = $this->formData['id1'];
        $this->lastId = $this->formData['id2'];
        $this->db->formatConditions(['resourcemetadataId' => $this->storedId]);
        $rows[] = $this->db->selectFirstRow('resource_metadata', ['resourcemetadataId', 'resourcemetadataResourceId',
            'resourcemetadataMetadataId', 'resourcemetadataType', 'resourcemetadataText', ]);
        $this->db->formatConditions(['resourcemetadataId' => $this->lastId]);
        $rows[] = $this->db->selectFirstRow('resource_metadata', ['resourcemetadataId', 'resourcemetadataResourceId',
            'resourcemetadataMetadataId', 'resourcemetadataType', 'resourcemetadataText', ]);
        foreach ($rows as $row)
        {
            if ($row['resourcemetadataType'] == 'q')
            {
                $return[] = $this->getQPMString($row, HTML\strong($this->pluginmessages->text('quote')));
            }
            elseif ($row['resourcemetadataType'] == 'p')
            {
                $return[] = $this->getQPMString($row, HTML\strong($this->pluginmessages->text('paraphrase')));
            }
            elseif ($row['resourcemetadataType'] == 'qc')
            {
                $return[] = $this->getQPMString($row, HTML\strong($this->pluginmessages->text('quoteComment')));
            }
            elseif ($row['resourcemetadataType'] == 'pc')
            {
                $return[] = $this->getQPMString($row, HTML\strong($this->pluginmessages->text('paraphraseComment')));
            }
            elseif ($row['resourcemetadataType'] == 'm')
            {
                $return[] = $this->getQPMString($row, HTML\strong($this->pluginmessages->text('musing')));
            }
            elseif ($row['resourcemetadataType'] == 'i')
            {
                $return[] = $this->getQPMString($row, HTML\strong($this->pluginmessages->text('idea')));
            }
        }
        $count = 0;
        $pString = FORM\formHeader("ideagen_generate");
        $uuid = \TEMPSTORAGE\getUuid($this->db);
        $pString .= \FORM\hidden('uuid', $uuid);
        $pString .= HTML\p(FORM\formSubmit($this->pluginmessages->text('generateAgain')));
        $pString .= FORM\formEnd();
        $pString .= HTML\tableStart('generalTable');
        foreach ($return as $string)
        {
            $pString .= $string;
            ++$count;
            if ($count == 1)
            {
                $pString .= HTML\trStart();
                $pString .= HTML\td(HTML\hr(), '', 3);
                $pString .= HTML\trEnd();
            }
        }
        $pString .= HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
        GLOBALS::addTplVar('content', HTML\p(HTML\h($this->pluginmessages->text('addIdea'))));
        $this->ideaAdd($this->vars['uuid']);
    }
    /**
     * select the random function
     */
    private function selectFunction()
    {
        $metadataArray = ['randomQuote', 'randomQuoteComment', 'randomParaphrase', 'randomParaphraseComment', 'randomMusing', 'randomIdea'];
        if (!$this->quotesExist)
        {
            unset($metadataArray[array_search('randomQuote', $metadataArray)]);
        }
        if (!$this->quoteCommentsExist)
        {
            unset($metadataArray[array_search('randomQuoteComment', $metadataArray)]);
        }
        if (!$this->paraphrasesExist)
        {
            unset($metadataArray[array_search('randomParaphrase', $metadataArray)]);
        }
        if (!$this->paraphraseCommentsExist)
        {
            unset($metadataArray[array_search('randomParaphraseComment', $metadataArray)]);
        }
        if (!$this->musingsExist)
        {
            unset($metadataArray[array_search('randomMusing', $metadataArray)]);
        }
        if (!$this->ideasExist)
        {
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
        $this->keywordConditions();
        $this->db->formatConditions(['resourcemetadataType' => 'q']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']);
        if (!$this->db->numRows($resultset))
        {
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
        $this->setPrivateConditions();
        $this->keywordConditions();
        $this->db->formatConditions(['resourcemetadataType' => 'qc']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select(
            'resource_metadata',
            ['resourcemetadataId', 'resourcemetadataMetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']
        );
        if (!$this->db->numRows($resultset))
        {
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
        $this->keywordConditions();
        $this->db->formatConditions(['resourcemetadataType' => 'p']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']);
        if (!$this->db->numRows($resultset))
        {
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
        $this->setPrivateConditions();
        $this->keywordConditions();
        $this->db->formatConditions(['resourcemetadataType' => 'pc']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select(
            'resource_metadata',
            ['resourcemetadataId', 'resourcemetadataMetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']
        );
        if (!$this->db->numRows($resultset))
        {
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
        $this->setPrivateConditions();
        $this->keywordConditions();
        $this->db->formatConditions(['resourcemetadataType' => 'm']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']);
        if (!$this->db->numRows($resultset))
        {
            $this->musingsExist = FALSE;

            return FALSE;
        }
        $row = $this->db->fetchRow($resultset);
        $this->lastId = $row['resourcemetadataId'];

        return $this->getQPMString($row, HTML\strong($this->pluginmessages->text('musing')));
    }
    /**
     * Get string from quote, paraphrase, or musing
     *
     * @param mixed $row
     * @param mixed $label
     */
    private function getQPMString($row, $label)
    {
        if (!$row['resourcemetadataResourceId'])
        {// i.e. quote or paraphrase comment or idea
            $this->db->formatConditions(['resourcemetadataId' => $row['resourcemetadataMetadataId']]);
            $resourceId = $this->db->selectFirstField('resource_metadata', 'resourcemetadataResourceId');
        }
        else
        {
            $resourceId = $row['resourcemetadataResourceId'];
        }
        $text = $this->cite->parseCitations($row['resourcemetadataText'], 'html');
        $text = HTML\tableStart("generalTable") . HTML\trStart() . HTML\td($text) . HTML\trEnd() . HTML\tableEnd();
        $resultset = $this->common->getResource($resourceId);
        $row = $this->db->fetchRow($resultset);
        $resource = $this->bibStyle->process($row);
        $td = HTML\trStart();
        $td .= HTML\td($label);
        $td .= HTML\td($resource . BR . $text);
        $td .= HTML\td(\HTML\a(
            $this->icons->getClass("viewmeta"),
            $this->icons->getHTML("viewmeta"),
            "index.php?action=resource_RESOURCEVIEW_CORE" . htmlentities("&id=" . $resourceId)
        ));
        $td .= HTML\trEnd();

        return $td;
    }
    /**
     * Select a random idea ID for viewing
     */
    private function randomIdea()
    {
        if (!$this->metadata->setCondition('i') && $this->session->getVar("setup_ReadOnly"))
        {
            $this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
        }
        $this->keywordConditions();
        $this->db->formatConditions(['resourcemetadataType' => 'i']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataText', 'resourcemetadataId']);
        if (!$this->db->numRows($resultset))
        {
            $this->ideasExist = FALSE;

            return FALSE;
        }
        $row = $this->db->fetchRow($resultset);
        $this->lastId = $row['resourcemetadataId'];
        $text = $this->cite->parseCitations($row['resourcemetadataText'], 'html');
        $text = HTML\tableStart("generalTable") . HTML\trStart() . HTML\td($text) . HTML\trEnd() . HTML\tableEnd();
        $td = HTML\trStart();
        $td .= HTML\td(HTML\strong($this->pluginmessages->text('idea')));
        $td .= HTML\td($text);
        $td .= HTML\td(\HTML\a(
            $this->icons->getClass("viewmeta"),
            $this->icons->getHTML("viewmeta"),
            "index.php?action=ideas_IDEAS_CORE" . htmlentities("&method=threadView&resourcemetadataId=" . $row['resourcemetadataId'])
        ));
        $td .= HTML\trEnd();

        return $td;
    }
    /**
     * Check there are at least three items of metadata from which to select
     */
    private function checkMetadata()
    {
        $count1 = $count2 = 0;
        $this->metadata->setCondition(FALSE, FALSE, TRUE);
        $this->db->limit(3, 0);
        $resultSet = $this->db->select('resource_metadata', 'resourcemetadataId');
        $count1 = $this->db->numRows($resultSet);
        if ($count1 < 3)
        { // check for ideas
            $this->metadata->setCondition('i', FALSE, TRUE);
            $this->db->limit(3, 0);
            $resultSet = $this->db->select('resource_metadata', 'resourcemetadataId');
            $count2 = $this->db->numRows($resultSet);
        }
        if (($count1 + $count2) < 3)
        {
            return FALSE;
        }

        return TRUE;
    }
    /**
     * Store session
     */
    private function storeSession() 
    {
		$array['Item1KWType'] = $this->vars['item1KWType'];
		$array['Item2KWType'] = $this->vars['item2KWType'];
        if ($this->vars['item1KWType'] == 'item1ResourceKW') {
			$array['Item1ResourceKWs'] = serialize($this->vars['item1KWs']); 
		} else if ($this->vars['item1KWType'] == 'item1MetadataKW') {
			$array['Item1MetadataKWs'] = serialize($this->vars['item1KWs']); 
		}
		if ($this->vars['item2KWType'] == 'item2ResourceKW') {
			$array['Item2ResourceKWs'] = serialize($this->vars['item2KWs']); 
		} else if ($this->vars['item2KWType'] == 'item2MetadataKW') {
			$array['Item2MetadataKWs'] = serialize($this->vars['item2KWs']); 
		}
		$this->session->writeArray($array, 'ideagen');
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
