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
 * EDITKEYWORDGROUP class
 */
class EDITKEYWORDGROUP
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $keyword;
    private $user;
    private $gatekeep;
    private $badInput;
    private $userId;
    private $kgs = [];
    private $keywords;
    private $userGroups;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->user = FACTORY_USER::getInstance();

        GLOBALS::setTplVar('heading', $this->messages->text("resources", "keywordGroup"));
        $this->userId = $this->session->getVar('setup_UserId');
    }
    /**
     * check we are allowed to edit and load appropriate method
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        $this->keywords = $this->keyword->grabAll();
// Check we have some keywords
        if ((!$this->keywords) || (count($this->keywords) < 2)) {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'tooFewKeywords'));

            return;
        }
        $this->db->formatConditions(['userkeywordgroupsUserId' => $this->userId]);
        $resultset = $this->db->select('user_keywordgroups', ['userkeywordgroupsId', 'userkeywordgroupsName', 'userkeywordgroupsDescription']);
        while ($row = $this->db->fetchRow($resultset)) {
        	$this->kgs[$row['userkeywordgroupsId']]['name'] = $row['userkeywordgroupsName'];
        	$this->kgs[$row['userkeywordgroupsId']]['description'] = $row['userkeywordgroupsDescription'];
        }
        $pString = $message ? \HTML\p($message, "error", "center") : FALSE;
        $pString .= $this->displayNewForm();
//        if (!empty($this->kgs)) {
//        	$pString .= \HTML\p(\HTML\hr());
//        	$pString .= $this->displayEditForm();
//        }
        GLOBALS::addTplVar('content', $pString);
// Load at end because .js initialization needs various DIVs to be in the page before they are made invisible
        \AJAX\loadJavascript(WIKINDX_BASE_URL . '/core/modules/edit/keywordgroup.js?ver=' . WIKINDX_PUBLIC_VERSION);
    }
    /** 
     * Display new keyword group form
     *
     */
    private function displayNewForm()
    {
    	$blank = '';
        $pString = \FORM\formHeader('edit_EDITKEYWORDGROUP_CORE', "onsubmit=\"selectAllNew();return true;\"");
        $pString .= \FORM\hidden("method", "new");
        $pString .= \HTML\tableStart('generalTable');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($this->displayNewKg());
        if ($this->userGroups = $this->user->listUserGroups()) {
			$pString .= \HTML\td($this->displayUserGroups());
			$blank .= \HTML\td('&nbsp;');
		}print_r($this->userGroups);
        $pString .= \HTML\td($this->displayKeywords());
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\formSubmit($this->messages->text("submit", "Add")));
        $blank .= \HTML\td('&nbsp;');
        $pString .= $blank;
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \FORM\formEnd();
        $pString .= \HTML\hr();
        return $pString;
    }
    /** 
     * Display keyword group edit form
     *
     */
    private function displayEditForm()
    {
        $pString = \FORM\formHeader('edit_EDITKEYWORDGROUP_CORE');
        $pString .= \FORM\hidden("method", "edit");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValue(FALSE, "kgIds", $kgs, 20));
        $pString .= \HTML\td($this->transferArrow());
        $td = \HTML\tableStart();
        $td .= \HTML\trStart();
        $td .= \HTML\td(\HTML\div('kgDiv', $this->displayEditKG(TRUE)));
        $td .= \HTML\trEnd();
        // Div and TD for glossary preceded by blank space
        $td .= \HTML\trStart();
        $td .= \HTML\td('&nbsp;');
        $td .= \HTML\trEnd();
        $td .= \HTML\trStart();
        $td .= \HTML\td($this->messages->text('resources', 'kgDescription') . BR . \HTML\div('descriptionDiv', $this->displayDescription()));
        $td .= \HTML\trEnd();
        $td .= \HTML\tableEnd();
        $pString .= \HTML\td($td);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
        $pString .= \FORM\formEnd();
        return $pString;
    }
    /**
     * Display interface to a new keyword group
     *
     */
    private function displayNewKG()
    {
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $textBox =  \FORM\textInput(
            $this->messages->text('resources', 'keywordGroupNew') . \HTML\span('*', 'required'),
            'KeywordGroup',
            FALSE,
            30,
            255
        );
        $description = \HTML\div('descriptionDiv', $this->displayDescription(TRUE));
		$pString .= \HTML\td($textBox . \HTML\p($description));
        $pString .= \HTML\td();
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        return $pString;
    }
    /**
     * Display interface to edit keyword group
     *
     * @param bool $initialDisplay
     */
    public function displayEditKG($initialDisplay = FALSE)
    {
        $keyword = $keywordId = FALSE;
        if (!$initialDisplay) {
            $this->db->formatConditions(['keywordId' => $this->vars['ajaxReturn']]);
            $recordset = $this->db->select('keyword', 'keywordKeyword');
            $row = $this->db->fetchRow($recordset);
            $keyword = \HTML\dbToFormTidy($row['keywordKeyword']);
            $keywordId = $this->vars['ajaxReturn'];
        }
        $pString = \FORM\hidden("editKeywordId", $keywordId);
        $pString .= \FORM\textInput(
            $this->messages->text('resources', 'keyword') . ' ' . \HTML\span('*', 'required'),
            'keyword',
            $keyword,
            30,
            255
        );
        if ($initialDisplay) {
            return $pString;
        }
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$pString"]));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Display the description textarea
     *
     * @param bool $initialDisplay
     */
    public function displayDescription($initialDisplay = FALSE)
    {
        if ($initialDisplay) {
            return \FORM\textareaInput($this->messages->text('resources', 'kgDescription'), "Description", FALSE, 50, 5);
        }
        $this->db->formatConditions(['kgId' => $this->vars['ajaxReturn']]);
        $recordset = $this->db->select('keyword', 'keywordGlossary');
        $row = $this->db->fetchRow($recordset);
        $glossary = \HTML\dbToFormTidy($row['keywordGlossary']);
        $pString = \FORM\textareaInput(FALSE, "Description", $glossary, 50, 5);
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $pString]));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * write new keyword group to the database
     */
    public function new()
    {
    	if (!$this->validateNewInput()) {
    		return;
    	}
// All OK to write if we get here . . .
		$fields[] = 'userkeywordgroupsName';
		$values[] = trim($this->vars['KeywordGroup']);
		$fields[] = 'userkeywordgroupsUserId';
		$values[] = $this->userId;
		if ($description = trim($this->vars['Description'])) {
			$fields[] = "userkeywordgroupsDescription";
    	    $values[] = $description;
    	}
        $this->db->insert('user_keywordgroups', $fields, $values);
        $autoId = $this->db->lastAutoId();
        foreach ($this->vars['SelectedKeyword'] as $id) {
        	$fields = $values = [];
        	$fields[] = 'userkgkeywordsKeywordGroupId';
			$values[] = $autoId;
        	$fields[] = 'userkgkeywordsKeywordId';
			$values[] = $id;
        	$this->db->insert('user_kg_keywords', $fields, $values);
		}
		if(!array_key_exists('SelectedUserGroup', $this->vars)) { // User Group can be NULL
				$fields = $values = [];
				$fields[] = 'userkgusergroupsKeywordGroupId';
				$values[] = $autoId;
				$this->db->insert('user_kg_usergroups', $fields, $values);
		}
		else {
			foreach ($this->vars['SelectedUserGroup'] as $id) {
				$fields = $values = [];
				$fields[] = 'userkgusergroupsKeywordGroupId';
				$values[] = $autoId;
				$fields[] = 'userkgusergroupsUserGroupId';
				$values[] = $id;
				$this->db->insert('user_kg_usergroups', $fields, $values);
			}
		}
        $this->init($this->success->text('keywordGroupNew'));
    }
    /**
     * check new input
     */
    private function validateNewInput()
    {
    	if (!array_key_exists('KeywordGroup', $this->vars) || !trim($this->vars['KeywordGroup'])) {
    		$this->init($this->errors->text('inputError', 'missing'));
    		return FALSE;
    	}
    	if (!array_key_exists('SelectedKeyword', $this->vars) || empty($this->vars['SelectedKeyword'])) {
    		$this->init($this->errors->text('inputError', 'missing'));
    		return FALSE;
    	}
        $resultset = $this->db->select('user_keywordgroups', ['userkeywordgroupsName']);
        while ($row = $this->db->fetchRow($resultset)) {
        	if ($row['userkeywordgroupsName'] == trim($this->vars['KeywordGroup'])) {
				$this->init($this->errors->text('inputError', 'groupExists'));
				return FALSE;
			}
        }
        return TRUE;
    }
    /**
     * write edits to the database
     */
    public function edit()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        if (!array_key_exists('editKeywordId', $this->vars) || !$this->vars['editKeywordId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $keyword = array_key_exists('keyword', $this->vars) ? trim($this->vars['keyword']) : FALSE;
        if (!$keyword) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        if ($keywordExistId = $this->keyword->checkExists($keyword)) {
            if ($keywordExistId != $this->vars['editKeywordId']) {
                return $this->confirmDuplicate($keywordExistId, $keyword);
            }
        }
        $updateArray['keywordKeyword'] = $keyword;
        $glossary = trim($this->vars['text']);
        if ($glossary) {
            $updateArray['keywordGlossary'] = $glossary;
        } else {
            $this->db->formatConditions(['keywordId' => $this->vars['editKeywordId']]);
            $this->db->updateNull('keyword', 'keywordGlossary');
        }
        $this->db->formatConditions(['keywordId' => $this->vars['editKeywordId']]);
        $this->db->update('keyword', $updateArray);
        // remove cache files for keywords
        $this->db->deleteCache('cacheKeywords');
        $this->db->deleteCache('cacheResourceKeywords');
        $this->db->deleteCache('cacheMetadataKeywords');
        $this->db->deleteCache('cacheQuoteKeywords');
        $this->db->deleteCache('cacheParaphraseKeywords');
        $this->db->deleteCache('cacheMusingKeywords');
        // send back to editDisplay with success message
        $this->init($this->success->text("keyword"));
    }
    /**
     * write to the database
     */
    public function editConfirm()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        if (!array_key_exists('editKeywordId', $this->vars) || !$this->vars['editKeywordId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        if (!array_key_exists('editKeywordExistId', $this->vars) || !$this->vars['editKeywordExistId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $editId = $this->vars['editKeywordId'];
        $existId = $this->vars['editKeywordExistId'];
        // Delete old keyword
        $this->db->formatConditions(['keywordId' => $editId]);
        $this->db->delete('keyword');
        // Update references to keyword
        $this->db->formatConditions(['resourcekeywordKeywordId' => $editId]);
        $this->db->update('resource_keyword', ['resourcekeywordKeywordId' => $existId]);
        // remove cache files for keywords
        $this->db->deleteCache('cacheKeywords');
        $this->db->deleteCache('cacheResourceKeywords');
        $this->db->deleteCache('cacheMetadataKeywords');
        $this->db->deleteCache('cacheQuoteKeywords');
        $this->db->deleteCache('cacheParaphraseKeywords');
        $this->db->deleteCache('cacheMusingKeywords');
        // send back to editDisplay with success message
        $this->init($this->success->text("keyword"));
    }
    /**
     * display user group select boxes
     *
     * @param bool default FALSE
     *
     * @return string
     */
    public function displayUserGroups($editForm = FALSE)
    {
    	if ($editForm) {
			$this->db->formatConditions(['resourcecategoryResourceId' => $this->vars['id']]);
			$resultset = $this->db->select('resource_category', ['resourcecategoryCategoryId', 'resourcecategorySubcategoryId']);
			while ($row = $this->db->fetchRow($resultset)) {
				$catArray[] = $row['resourcecategoryCategoryId'];
				if ($row['resourcecategorySubcategoryId']) {
					$this->subcatArray[] = $row['resourcecategorySubcategoryId'];
				}
			}
			$selectedCategories = [];
			foreach ($catArray as $key) {
				if (!array_key_exists($key, $this->categories)) {
					continue;
				}
				$selectedCategories[$key] = $this->categories[$key];
				unset($this->categories[$key]);
			}
		}

        $td = \HTML\tableStart();
        $td .= \HTML\trStart();
        $td .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "availableUserGroup"),
            'AvailableUserGroup',
            $this->userGroups,
            10
        ) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint'), 'padding3px left width18percent');
        list($toLeftImage, $toRightImage) = $this->transferArrows('selectUserGroup', 'discardUserGroup');
        $td .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'padding3px left width5percent');

		$td .= \HTML\td(\FORM\selectFBoxValueMultiple(
			$this->messages->text('select', "userGroup") . \HTML\span('*', 'required'),
			'SelectedUserGroup',
			[],
			10
		), 'padding3px left width18percent');

        $td .= \HTML\trEnd();
        $td .= \HTML\tableEnd();

        return $td;
    }
    /**
     * display keyword select boxes
     *
     * @param bool default FALSE
     *
     * @return string
     */
    public function displayKeywords($editForm = FALSE)
    {
    	if ($editForm) {
			$this->db->formatConditions(['resourcecategoryResourceId' => $this->vars['id']]);
			$resultset = $this->db->select('resource_category', ['resourcecategoryCategoryId', 'resourcecategorySubcategoryId']);
			while ($row = $this->db->fetchRow($resultset)) {
				$catArray[] = $row['resourcecategoryCategoryId'];
				if ($row['resourcecategorySubcategoryId']) {
					$this->subcatArray[] = $row['resourcecategorySubcategoryId'];
				}
			}
			$selectedCategories = [];
			foreach ($catArray as $key) {
				if (!array_key_exists($key, $this->categories)) {
					continue;
				}
				$selectedCategories[$key] = $this->categories[$key];
				unset($this->categories[$key]);
			}
		}

        $td = \HTML\tableStart();
        $td .= \HTML\trStart();
        $td .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "availableKeyword"),
            'AvailableKeyword',
            $this->keywords,
            10
        ) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint'), 'padding3px left width18percent');
        list($toLeftImage, $toRightImage) = $this->transferArrows('selectKeyword', 'discardKeyword');
        $td .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'padding3px left width5percent');

		$td .= \HTML\td(\FORM\selectFBoxValueMultiple(
			$this->messages->text('select', "keyword") . \HTML\span('*', 'required'),
			'SelectedKeyword',
			[],
			10
		), 'padding3px left width18percent');

        $td .= \HTML\trEnd();
        $td .= \HTML\tableEnd();

        return $td;
    }
    /**
     * transferArrows
     *
     * @param string $startFunction1
     * @param string $startFunction2
     *
     * @return array
     */
    private function transferArrows($startFunction1, $startFunction2)
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => $startFunction1,
        ];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => $startFunction2,
        ];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);
        return [$toLeftImage, $toRightImage];
    }
}
