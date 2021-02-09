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
    private $formData = [];

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

        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('keywordGroups'));
        GLOBALS::setTplVar('heading', $this->messages->text("resources", "keywordGroup"));
        $this->userId = $this->session->getVar('setup_UserId');
    }
    /**
     * check we are allowed to edit and load appropriate method
     *
     * @param string $message
     */
    public function init($message = FALSE)
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        $this->keywords = $this->keyword->grabAll();
        // Check we have some keywords
        if ((!$this->keywords) || (count($this->keywords) < 2))
        {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'tooFewKeywords'));

            return;
        }
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($this->displayNewForm());
        $pString .= \HTML\td($this->displayDeleteForm());
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\HTML\hr());
        $groups = $this->getGroups(TRUE);
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        if (!empty($groups))
        {
            $pString .= \HTML\td($this->displayEditForm($groups, TRUE));
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
        // Load at end because .js initialization needs various DIVs to be in the page before they are made invisible
        \AJAX\loadJavascript(WIKINDX_URL_BASE . '/core/modules/edit/keywordgroup.js?ver=' . WIKINDX_PUBLIC_VERSION);
    }
    /**
     * Confirm deletes
     */
    public function deleteConfirm()
    {
        if (!array_key_exists('delete_GroupId', $this->vars))
        {
            header("Location: index.php?action=edit_EDITKEYWORDGROUP_CORE&method=init&error=inputError_missing");
            die;
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "delete2", " (" .
            $this->messages->text("resources", "keywordGroup") . ")"));
        $input = array_values($this->vars['delete_GroupId']);
        $groups = $this->getGroups();
        $input = "'" . implode("', '", array_keys(array_intersect(array_flip($groups), $input))) . "'";
        $input = html_entity_decode($input);
        $pString = \HTML\p($this->messages->text("resources", "deleteConfirmKeywordGroups") . ":&nbsp;&nbsp;$input");
        $pString .= \FORM\formHeader("edit_EDITKEYWORDGROUP_CORE");
        $pString .= \FORM\hidden("delete_GroupId", base64_encode(serialize($this->vars['delete_GroupId'])));
        $pString .= \FORM\hidden("method", 'delete');
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Delete")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * write to the database
     */
    public function delete()
    {
        if (!array_key_exists('delete_GroupId', $this->vars) || !$this->vars['delete_GroupId'])
        {
            header("Location: index.php?action=edit_EDITKEYWORDGROUP_CORE&method=init&error=inputError_missing");
            die;
        }
        foreach (unserialize(base64_decode($this->vars['delete_GroupId'])) as $deleteId)
        {
            $this->db->formatConditions(['userkeywordgroupsId' => $deleteId]);
            $this->db->delete('user_keywordgroups');
            $this->db->formatConditions(['userkgkeywordsKeywordGroupId' => $deleteId]);
            $this->db->delete('user_kg_keywords');
            $this->db->formatConditions(['userkgusergroupsKeywordGroupId' => $deleteId]);
            $this->db->delete('user_kg_usergroups');
        }
        // send back to keyword group page with success message
        header("Location: index.php?action=edit_EDITKEYWORDGROUP_CORE&method=init&success=keywordGroupDelete");
        die;
    }
    /**
     * Display the name textbox for editing
     *
     * @param bool $initialDisplay (Default is FALSE)
     * @param mixed $kgId (Default is FALSE)
     */
    public function editDisplayName($initialDisplay = FALSE, $kgId = FALSE)
    {
        if (!$initialDisplay)
        {
            $kgId = $this->vars['ajaxReturn'];
        }
        if (array_key_exists('editName', $this->formData))
        {
            $name = $this->formData['editName'];
        }
        else
        {
            $this->db->formatConditions(['userkeywordgroupsId' => $kgId]);
            $recordset = $this->db->select('user_keywordgroups', 'userkeywordgroupsName');
            $row = $this->db->fetchRow($recordset);
            $name = \HTML\dbToFormTidy($row['userkeywordgroupsName']);
        }
        $pString = \HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text('resources', 'keywordGroupName'),
            'editName',
            $name,
            30,
            255
        );
        if ($initialDisplay)
        {
            return $pString;
        }
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $pString]));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Display the description textarea for editing
     *
     * @param bool $initialDisplay (Default is FALSE)
     * @param mixed $kgId (Default is FALSE)
     */
    public function editDisplayDescription($initialDisplay = FALSE, $kgId = FALSE)
    {
        if (!$initialDisplay)
        {
            $kgId = $this->vars['ajaxReturn'];
        }
        if (array_key_exists('editDescription', $this->formData))
        {
            $description = $this->formData['editDescription'];
        }
        else
        {
            $this->db->formatConditions(['userkeywordgroupsId' => $kgId]);
            $recordset = $this->db->select('user_keywordgroups', 'userkeywordgroupsDescription');
            $row = $this->db->fetchRow($recordset);
            $description = \HTML\dbToFormTidy($row['userkeywordgroupsDescription']);
        }
        $pString = \FORM\textareaInput($this->messages->text('resources', 'kgDescription'), 'editDescription', $description, 50, 5);
        if ($initialDisplay)
        {
            return $pString;
        }
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $pString]));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * write new keyword group to the database
     */
    public function new()
    {
        $this->validateNewInput();
        // All OK to write if we get here . . .
        $fields[] = 'userkeywordgroupsName';
        $values[] = \UTF8\mb_trim($this->vars['KeywordGroup']);
        $fields[] = 'userkeywordgroupsUserId';
        $values[] = $this->userId;
        if ($description = \UTF8\mb_trim($this->vars['Description']))
        {
            $fields[] = "userkeywordgroupsDescription";
            $values[] = $description;
        }
        $this->db->insert('user_keywordgroups', $fields, $values);
        $autoId = $this->db->lastAutoId();
        foreach ($this->vars['SelectedKeyword'] as $id)
        {
            $fields = $values = [];
            $fields[] = 'userkgkeywordsKeywordGroupId';
            $values[] = $autoId;
            $fields[] = 'userkgkeywordsKeywordId';
            $values[] = $id;
            $this->db->insert('user_kg_keywords', $fields, $values);
        }
        if (!array_key_exists('SelectedUserGroup', $this->vars))
        { // User Group can be NULL
            $fields = $values = [];
            $fields[] = 'userkgusergroupsKeywordGroupId';
            $values[] = $autoId;
            $this->db->insert('user_kg_usergroups', $fields, $values);
        }
        else
        {
            foreach ($this->vars['SelectedUserGroup'] as $id)
            {
                $fields = $values = [];
                $fields[] = 'userkgusergroupsKeywordGroupId';
                $values[] = $autoId;
                $fields[] = 'userkgusergroupsUserGroupId';
                $values[] = $id;
                $this->db->insert('user_kg_usergroups', $fields, $values);
            }
        }
        header("Location: index.php?action=edit_EDITKEYWORDGROUP_CORE&method=init&success=keywordGroupNew");
        die;
    }
    /**
     * write edits to the database
     */
    public function edit()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        $this->validateEditInput();
        // We delete existing rows and insert new rows
        $this->db->formatConditions(['userkeywordgroupsId' => $this->vars['kgIds']]);
        $this->db->delete('user_keywordgroups');
        $this->db->formatConditions(['userkgkeywordsKeywordGroupId' => $this->vars['kgIds']]);
        $this->db->delete('user_kg_keywords');
        $this->db->formatConditions(['userkgusergroupsKeywordGroupId' => $this->vars['kgIds']]);
        $this->db->delete('user_kg_usergroups');

        $fields[] = 'userkeywordgroupsName';
        $values[] = \UTF8\mb_trim($this->vars['editName']);
        $fields[] = 'userkeywordgroupsUserId';
        $values[] = $this->userId;
        if ($description = \UTF8\mb_trim($this->vars['editDescription']))
        {
            $fields[] = "userkeywordgroupsDescription";
            $values[] = $description;
        }
        $this->db->insert('user_keywordgroups', $fields, $values);
        $autoId = $this->db->lastAutoId();
        foreach ($this->vars['editSelectedKeyword'] as $id)
        {
            $fields = $values = [];
            $fields[] = 'userkgkeywordsKeywordGroupId';
            $values[] = $autoId;
            $fields[] = 'userkgkeywordsKeywordId';
            $values[] = $id;
            $this->db->insert('user_kg_keywords', $fields, $values);
        }
        if (!array_key_exists('editSelectedUserGroup', $this->vars))
        { // User Group can be NULL
            $fields = $values = [];
            $fields[] = 'userkgusergroupsKeywordGroupId';
            $values[] = $autoId;
            $this->db->insert('user_kg_usergroups', $fields, $values);
        }
        else
        {
            foreach ($this->vars['editSelectedUserGroup'] as $id)
            {
                $fields = $values = [];
                $fields[] = 'userkgusergroupsKeywordGroupId';
                $values[] = $autoId;
                $fields[] = 'userkgusergroupsUserGroupId';
                $values[] = $id;
                $this->db->insert('user_kg_usergroups', $fields, $values);
            }
        }
        // send back to keyword group page with success message
        header("Location: index.php?action=edit_EDITKEYWORDGROUP_CORE&method=init&success=keywordGroupEdit");
        die;
    }
    /**
     * display user group select boxes
     *
     * @return string
     */
    public function displayUserGroups()
    {
        $ugs = [];
        if (array_key_exists('SelectedUserGroup', $this->formData))
        {
            $ugs = array_intersect_key($this->userGroups, array_flip($this->formData['SelectedUserGroup']));
            natcasesort($ugs);
        }
        $selectBox = \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "userGroup"),
            'SelectedUserGroup',
            $ugs,
            10
        ), 'padding3px left width18percent');
        $td = \HTML\tableStart();
        $td .= \HTML\trStart();
        if (!empty($ugs))
        {
            $ugs = array_diff_key($this->userGroups, $ugs);
        }
        else
        {
            $ugs = $this->userGroups;
        }
        $td .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "availableUserGroup"),
            'AvailableUserGroup',
            $ugs,
            10
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint'), 'padding3px left width18percent');
        list($toLeftImage, $toRightImage) = $this->transferArrows('selectUserGroup', 'availableUserGroup');
        $td .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'padding3px left width5percent');

        $td .= $selectBox;

        $td .= \HTML\trEnd();
        $td .= \HTML\tableEnd();

        return $td;
    }
    /**
     * get the div for the available user groups select box for editing
     *
     * @param bool $initialDisplay Default FALSE
     * @param int $kgId
     *
     * @return string
     */
    public function editAvailableUGsDiv($initialDisplay = FALSE, $kgId = FALSE)
    {
        if (!$initialDisplay)
        {
            $kgId = $this->vars['ajaxReturn'];
        }
        $ugs = [];
        if (array_key_exists('editSelectedUserGroup', $this->formData))
        {
            $ugs = array_intersect_key($this->user->listUserGroups(), array_flip($this->formData['editSelectedUserGroup']));
        }
        else
        {
            $this->db->formatConditions(['userkgusergroupsKeywordGroupId' => $kgId]);
            $this->db->leftJoin('user_groups', 'usergroupsId', 'userkgusergroupsUserGroupId');
            $recordset = $this->db->select('user_kg_usergroups', ['usergroupsTitle', 'userkgusergroupsUserGroupId']);
            while ($row = $this->db->fetchRow($recordset))
            {
                $ugs[$row['userkgusergroupsUserGroupId']] = \HTML\dbToFormTidy($row['usergroupsTitle']);
            }
        }
        if (!empty($ugs))
        {
            $diff = array_diff_key($this->user->listUserGroups(), $ugs);
            natcasesort($diff);
        }
        $pString = \FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "availableUserGroup"),
            'editAvailableUserGroup',
            $diff,
            10
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint');
        if ($initialDisplay)
        {
            return $pString;
        }
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$pString"]));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * get the div for the selected user groups select box for editing
     *
     * @param bool $initialDisplay Default FALSE
     * @param int $kgId
     *
     * @return string
     */
    public function editSelectedUGsDiv($initialDisplay = FALSE, $kgId = FALSE)
    {
        if (!$initialDisplay)
        {
            $kgId = $this->vars['ajaxReturn'];
        }
        $ugs = [];
        if (array_key_exists('editSelectedUserGroup', $this->formData))
        {
            $ugs = array_intersect_key($this->user->listUserGroups(), array_flip($this->formData['editSelectedUserGroup']));
        }
        else
        {
            $this->db->formatConditions(['userkgusergroupsKeywordGroupId' => $kgId]);
            $this->db->leftJoin('user_groups', 'usergroupsId', 'userkgusergroupsUserGroupId');
            $recordset = $this->db->select('user_kg_usergroups', ['usergroupsTitle', 'userkgusergroupsUserGroupId']);
            while ($row = $this->db->fetchRow($recordset))
            {
                $ugs[$row['userkgusergroupsUserGroupId']] = \HTML\dbToFormTidy($row['usergroupsTitle']);
            }
        }
        if (!empty($ugs))
        {
            natcasesort($ugs);
        }
        $pString = \FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "userGroup"),
            'editSelectedUserGroup',
            array_filter($ugs),
            10
        );
        if ($initialDisplay)
        {
            return $pString;
        }
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$pString"]));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * get the div for the selected keyword select box for editing
     *
     * @param bool $initialDisplay (Default is FALSE)
     * @param mixed $kgId (Default is FALSE)
     *
     * @return string
     */
    public function editSelectedKeywordsDiv($initialDisplay = FALSE, $kgId = FALSE)
    {
        if (!$initialDisplay)
        {
            $kgId = $this->vars['ajaxReturn'];
        }
        if (array_key_exists('editSelectedKeyword', $this->formData))
        {
            $keywords = array_intersect_key($this->keywords, array_flip($this->formData['editSelectedKeyword']));
        }
        else
        {
            $this->db->formatConditions(['userkgkeywordsKeywordGroupId' => $kgId]);
            $this->db->leftJoin('keyword', 'keywordId', 'userkgkeywordsKeywordId');
            $recordset = $this->db->select('user_kg_keywords', ['keywordKeyword', 'userkgkeywordsKeywordId']);
            while ($row = $this->db->fetchRow($recordset))
            {
                $keywords[$row['userkgkeywordsKeywordId']] = \HTML\dbToFormTidy($row['keywordKeyword']);
            }
        }
        natcasesort($keywords);
        $pString = \HTML\span('*', 'required') . \FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "keyword"),
            'editSelectedKeyword',
            array_filter($keywords),
            10
        );
        if ($initialDisplay)
        {
            return $pString;
        }
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$pString"]));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * get the div for the selected keyword select box for editing
     *
     * @param bool $initialDisplay (Default is FALSE)
     * @param mixed $kgId (Default is FALSE)
     *
     * @return string
     */
    public function editAvailableKeywordsDiv($initialDisplay = FALSE, $kgId = FALSE)
    {
        if (!$initialDisplay)
        {
            $kgId = $this->vars['ajaxReturn'];
        }
        if (array_key_exists('editSelectedKeyword', $this->formData))
        {
            $keywords = array_intersect_key($this->keywords, array_flip($this->formData['editSelectedKeyword']));
        }
        else
        {
            $this->db->formatConditions(['userkgkeywordsKeywordGroupId' => $kgId]);
            $this->db->leftJoin('keyword', 'keywordId', 'userkgkeywordsKeywordId');
            $recordset = $this->db->select('user_kg_keywords', ['keywordKeyword', 'userkgkeywordsKeywordId']);
            while ($row = $this->db->fetchRow($recordset))
            {
                $keywords[$row['userkgkeywordsKeywordId']] = \HTML\dbToFormTidy($row['keywordKeyword']);
            }
        }
        $diff = array_diff_key($this->keyword->grabAll(), $keywords);
        natcasesort($diff);
        $pString = \HTML\span('*', 'required') . \FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "availableKeyword"),
            'editAvailableKeyword',
            $diff,
            10
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint');
        if ($initialDisplay)
        {
            return $pString;
        }
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$pString"]));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Display new keyword group form
     */
    private function displayNewForm()
    {
        $blank = '';
        $pString = \FORM\formHeader('edit_EDITKEYWORDGROUP_CORE', "onsubmit=\"selectAllNew();return true;\"");
        $pString .= \FORM\hidden("method", "new");
        $pString .= \HTML\tableStart('generalTable');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($this->displayNewKg());
        $pString .= \HTML\td($this->displayKeywords());
        if ($this->userGroups = $this->user->listUserGroups())
        {
            $pString .= \HTML\td($this->displayUserGroups());
            $blank .= \HTML\td('&nbsp;');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\formSubmit($this->messages->text("submit", "Add")));
        $blank .= \HTML\td('&nbsp;');
        $pString .= $blank;
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * Display delete keyword group form
     *
     * @param string $message
     */
    private function displayDeleteForm($message = FALSE)
    {
        if (empty($groups = $this->getGroups(TRUE)))
        {
            return '&nbsp;';
        }
        $pString = \FORM\formHeader('edit_EDITKEYWORDGROUP_CORE');
        $pString .= \FORM\hidden("method", "deleteConfirm");
        $pString .= \HTML\tableStart('generalTable');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text('misc', "keywordGroupDelete"),
            'delete_GroupId',
            $groups,
            10
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint'), 'padding3px left width18percent');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * Display keyword group edit form
     *
     * @param array $groiups keyword groups
     * @param bool $initialDisplay
     * @return mixed string
     */
    private function displayEditForm($groups, $initialDisplay = FALSE)
    {
        $blank = '';
        $kgId = FALSE;
        $js = $this->editOnChange();
        $pString = \FORM\formHeader('edit_EDITKEYWORDGROUP_CORE', "onsubmit=\"selectAllEdit();return true;\"");
        $pString .= \FORM\hidden("method", "edit");
        $pString .= \HTML\tableStart('generalTable');
        $pString .= \HTML\trStart();
        if ($initialDisplay)
        {
            if (array_key_exists('kgIds', $this->formData))
            {
                $kgId = $this->formData['kgIds'];
            }
            else
            {
                foreach ($groups as $kgId => $null)
                {
                    break;
                }
            }
            $pString .= \HTML\td(\FORM\selectedBoxValue(
                $this->messages->text('resources', 'keywordGroupEdit'),
                "kgIds",
                $groups,
                $kgId,
                10,
                FALSE,
                $js
            ));
        }
        else
        {
            $pString .= \HTML\td(\FORM\selectFBoxValue(
                $this->messages->text('resources', 'keywordGroupEdit'),
                "kgIds",
                $groups,
                10,
                FALSE,
                $js
            ));
        }
        $pString .= \HTML\td($this->getEditNameAndDescription($kgId));
        $pString .= \HTML\td($this->editDisplayKeywords(TRUE, $kgId));
        if ($this->userGroups = $this->user->listUserGroups())
        {
            $pString .= \HTML\td($this->editDisplayUGs(TRUE, $kgId));
            $blank .= \HTML\td('&nbsp;');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\formSubmit($this->messages->text("submit", "Edit")));
        $blank .= \HTML\td('&nbsp;');
        $pString .= $blank;
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * Display interface to a new keyword group
     *
     * @return string
     */
    private function displayNewKG()
    {
        $title = FALSE;
        if (array_key_exists('KeywordGroup', $this->formData))
        {
            $title = $this->formData['KeywordGroup'];
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $textBox = \HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text('resources', 'keywordGroupNew'),
            'KeywordGroup',
            $title,
            30,
            255
        );
        $description = $this->displayDescription();
        $pString .= \HTML\td($textBox . \HTML\p($description));
        $pString .= \HTML\td();
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display name and description for editing a keyword group
     *
     * @param int $kgID
     * @param mixed $kgId
     *
     * @return string
     */
    private function getEditNameAndDescription($kgId)
    {
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $name = \HTML\div('nameDiv', $this->editDisplayName(TRUE, $kgId));
        $description = \HTML\div('descriptionDiv', $this->editDisplayDescription(TRUE, $kgId));
        $pString .= \HTML\td($name . \HTML\p($description));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display the description textarea for new keyword groups
     *
     * @return string
     */
    private function displayDescription()
    {
        $description = FALSE;
        if (array_key_exists('Description', $this->formData))
        {
            $description = $this->formData['Description'];
        }

        return \FORM\textareaInput($this->messages->text('resources', 'kgDescription'), 'Description', $description, 50, 5);
    }
    /**
     * check new input
     */
    private function validateNewInput()
    {
        // First check for input
        $error = '';
        if (!array_key_exists('KeywordGroup', $this->vars) || !\UTF8\mb_trim($this->vars['KeywordGroup']))
        {
            $error = $this->errors->text('inputError', 'missing');
        }
        if (!array_key_exists('SelectedKeyword', $this->vars) || empty($this->vars['SelectedKeyword']))
        {
            $error = $this->errors->text('inputError', 'missing');
        }
        elseif (count($this->vars['SelectedKeyword']) < 2)
        {
            $error = $this->errors->text('inputError', 'tooFewKeywordGroups');
        }
        $groups = $this->getGroups();
        if (!empty($groups) && (in_array(\UTF8\mb_trim($this->vars['KeywordGroup']), $groups)))
        {
            $error = $this->errors->text('inputError', 'groupExists');
        }
        // Second, write any input to formData
        // Possible form fields – ensure fields are available whether filled in or not (NB checkbox fields do NOT exist in $this->vars if not checked)
        $this->formData = ['KeywordGroup' => $this->vars['KeywordGroup'], 'Description' => $this->vars['Description']];
        if (array_key_exists('SelectedKeyword', $this->vars))
        {
            $this->formData['SelectedKeyword'] = $this->vars['SelectedKeyword'];
        }
        if (array_key_exists('SelectedUserGroup', $this->vars))
        {
            $this->formData['SelectedUserGroup'] = $this->vars['SelectedUserGroup'];
        }
        if ($error)
        {
            $this->badInput->close($error, $this, 'init');
        }
    }
    /**
     * Get keyword groups
     *
     * @param bool Default FALSE: return all groups. If TRUE, return user's groups
     * @param mixed $user
     *
     * @return array
     */
    private function getGroups($user = FALSE)
    {
        $groups = [];
        if ($user)
        {
            $this->db->formatConditions(['userkeywordgroupsUserId' => $this->userId]);
        }
        $resultset = $this->db->select('user_keywordgroups', ['userkeywordgroupsName', 'userkeywordgroupsId']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $groups[$row['userkeywordgroupsId']] = \HTML\dbToFormTidy($row['userkeywordgroupsName']);
        }
        natcasesort($groups);

        return $groups;
    }
    private function validateEditInput()
    {
        // First check for input
        $error = '';
        if (!array_key_exists('kgIds', $this->vars))
        {
            $error = $this->errors->text('inputError', 'missing');
        }
        if (!array_key_exists('editName', $this->vars) || !\UTF8\mb_trim($this->vars['editName']))
        {
            $error = $this->errors->text('inputError', 'missing');
        }
        if (!array_key_exists('editSelectedKeyword', $this->vars) || empty($this->vars['editSelectedKeyword']))
        {
            $error = $this->errors->text('inputError', 'missing');
        }
        elseif (count($this->vars['editSelectedKeyword']) < 2)
        {
            $error = $this->errors->text('inputError', 'tooFewKeywordGroups');
        }
        $groups = $this->getGroups();
        if (!empty($groups) && ($key = array_search(\UTF8\mb_trim($this->vars['editName']), $groups)) !== FALSE)
        {
            if ($key != $this->vars['kgIds'])
            {
                $error = $this->errors->text('inputError', 'groupExists');
            }
        }
        // Second, write any input to formData
        // Possible form fields – ensure fields are available whether filled in or not (NB checkbox fields do NOT exist in $this->vars if not checked)
        $this->formData = ['kgIds' => $this->vars['kgIds'], 'editName' => $this->vars['editName'],
            'editDescription' => $this->vars['editDescription'], ];
        if (array_key_exists('editSelectedKeyword', $this->vars))
        {
            $this->formData['editSelectedKeyword'] = $this->vars['editSelectedKeyword'];
        }
        if (array_key_exists('editSelectedUserGroup', $this->vars))
        {
            $this->formData['editSelectedUserGroup'] = $this->vars['editSelectedUserGroup'];
        }
        if ($error)
        {
            $this->badInput->close($error, $this, 'init');
        }
    }
    /**
     * display user group select boxes for editing
     *
     * @param bool $initialDisplay Default FALSE
     * @param int $kgID
     * @param mixed $kgId
     *
     * @return string
     */
    private function editDisplayUGs($initialDisplay = FALSE, $kgId = FALSE)
    {
        $td = \HTML\tableStart();
        $td .= \HTML\trStart();
        $td .= \HTML\td(\HTML\div('availableUgDiv', $this->editAvailableUGsDiv($initialDisplay, $kgId), 'padding3px left width18percent'));
        
        list($toLeftImage, $toRightImage) = $this->transferArrows('edit_selectUserGroup', 'edit_availableUserGroup');
        $td .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'padding3px left width5percent');

        $td .= \HTML\td(\HTML\div('selectedUgDiv', $this->editSelectedUGsDiv($initialDisplay, $kgId)), 'padding3px left width18percent');

        $td .= \HTML\trEnd();
        $td .= \HTML\tableEnd();

        return $td;
    }
    /**
     * display keyword select boxes
     *
     * @return string
     */
    private function displayKeywords()
    {
        $keywords = [];
        if (array_key_exists('SelectedKeyword', $this->formData))
        {
            $keywords = array_intersect_key($this->keywords, array_flip($this->formData['SelectedKeyword']));
            natcasesort($keywords);
        }
        $selectBox = \HTML\td(\HTML\span('*', 'required') . \FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "keyword"),
            'SelectedKeyword',
            $keywords,
            10
        ), 'padding3px left width18percent');
        $td = \HTML\tableStart();
        $td .= \HTML\trStart();
        if (!empty($keywords))
        {
            $keywords = array_diff_key($this->keywords, $keywords);
        }
        else
        {
            $keywords = $this->keywords;
        }
        $td .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "availableKeyword"),
            'AvailableKeyword',
            $keywords,
            10
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint'), 'padding3px left width18percent');
        list($toLeftImage, $toRightImage) = $this->transferArrows('selectKeyword', 'availableKeyword');
        $td .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'padding3px left width5percent');

        $td .= $selectBox;

        $td .= \HTML\trEnd();
        $td .= \HTML\tableEnd();

        return $td;
    }
    /**
     * display keyword select boxes for editing
     *
     * @param bool $initialDisplay Default FALSE
     * @param int $kgID
     * @param mixed $kgId
     *
     * @return string
     */
    private function editDisplayKeywords($initialDisplay = FALSE, $kgId = FALSE)
    {
        $td = \HTML\tableStart();
        $td .= \HTML\trStart();
        $td .= \HTML\td(\HTML\div('availableKeywordDiv', $this->editAvailableKeywordsDiv($initialDisplay, $kgId)), 'padding3px left width18percent');
        list($toLeftImage, $toRightImage) = $this->transferArrows('edit_selectKeyword', 'edit_availableKeyword');
        $td .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'padding3px left width5percent');

        $td .= \HTML\td(\HTML\div('selectedKeywordDiv', $this->editSelectedKeywordsDiv($initialDisplay, $kgId)), 'padding3px left width18percent');

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
        if (!$startFunction2)
        {
            return [$toRightImage];
        }
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => $startFunction2,
        ];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);

        return [$toLeftImage, $toRightImage];
    }
    /**
     * editOnChange
     *
     * @return string
     */
    private function editOnChange()
    {
        $jScript = 'index.php?action=edit_EDITKEYWORDGROUP_CORE&method=editDisplayName';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'kgIds',
            'targetDiv' => 'nameDiv',
        ];
        $jScript = 'index.php?action=edit_EDITKEYWORDGROUP_CORE&method=editDisplayDescription';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'kgIds',
            'targetDiv' => 'descriptionDiv',
        ];
        $jScript = 'index.php?action=edit_EDITKEYWORDGROUP_CORE&method=editAvailableKeywordsDiv';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'kgIds',
            'targetDiv' => 'availableKeywordDiv',
        ];
        $jScript = 'index.php?action=edit_EDITKEYWORDGROUP_CORE&method=editSelectedKeywordsDiv';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'kgIds',
            'targetDiv' => 'selectedKeywordDiv',
        ];
        if ($this->user->listUserGroups())
        {
            $jScript = 'index.php?action=edit_EDITKEYWORDGROUP_CORE&method=editAvailableUGsDiv';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'kgIds',
                'targetDiv' => 'availableUgDiv',
            ];
            $jScript = 'index.php?action=edit_EDITKEYWORDGROUP_CORE&method=editSelectedUGsDiv';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'kgIds',
                'targetDiv' => 'selectedUgDiv',
            ];
        }

        return \AJAX\jActionForm('onchange', $jsonArray);
    }
}
