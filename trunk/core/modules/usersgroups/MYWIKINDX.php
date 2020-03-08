<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 *	MYWIKINDX WIKINDX class
 */
class MYWIKINDX
{
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $user;
    private $db;
    private $vars;
    private $bib;
    private $badInput;
    private $errorString = FALSE;
    private $userName = FALSE;
    private $userNameDisplay = FALSE;

    public function __construct()
    {
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->bib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        if ($this->session->getVar("setup_UserId")) {
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $recordset = $this->db->select('users', 'usersUsername');
            if (!$this->db->numRows($recordset)) {
                die($this->errors->text("dbError", "read"));
            }
            $row = $this->db->fetchRow($recordset);
            $this->userName = $row['usersUsername'];
            $this->userNameDisplay = ': ' . $row['usersUsername'];
        }
    }
    /**
     * init
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        // Anything in the session takes precedence
        if (($messageIn = $this->session->getVar("mywikindx_Message")) && ($itemIn = $this->session->getVar("mywikindx_Item"))) {
            $this->session->delVar("mywikindx_Message");
            $this->session->delVar("mywikindx_Item");
            $messageString = $messageIn;
            $item = $itemIn;
        } elseif (is_array($message)) {
            $messageString = $message[0];
            $item = $message[1];
        } else {
            $messageString = $message;
            $item = FALSE;
        }
        $configGroups = $this->getConfigGroups();
        if (empty($configGroups)) {
            return FALSE;
        }
        $this->errorString = $messageString;
        include_once("core/modules/help/HELPMESSAGES.php");
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('myWikindx'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "myWikindx", $this->userNameDisplay));
        $jsonArray = [];
        $jScript = 'index.php?action=usersgroups_MYWIKINDX_CORE&method=initConfigDiv';
        $jsonArray[] = [
            'startFunction' => 'triggerFromMultiSelect',
            'script' => "$jScript",
            'triggerField' => 'configMenu',
            'targetDiv' => 'configDiv',
        ];
        $js = \AJAX\jActionForm('onchange', $jsonArray);
        $pString = \HTML\tableStart('');
        $pString .= \HTML\trStart();
        if ($item) {
            $pString .= \HTML\td(\FORM\selectedBoxValue($this->messages->text(
                'config',
                'options'
            ), 'configMenu', $configGroups, $item, count($configGroups), FALSE, $js));
        } else {
            $pString .= \HTML\td(\FORM\selectFBoxValue($this->messages->text(
                'config',
                'options'
            ), 'configMenu', $configGroups, count($configGroups), FALSE, $js));
        }
        $pString .= \HTML\td(\HTML\div('configDiv', $this->getConfigDetails($configGroups, $item)), 'left top width80percent');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= '<script type="text/javascript" src="' . WIKINDX_BASE_URL . '/core/modules/usersgroups/mywikindx.js"></script>';
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * AJAX-based DIV content creator for configuration of selected menu items
     */
    public function initConfigDiv()
    {
        $configGroups = $this->getConfigGroups();
        $div = \HTML\div('divMenu', $this->getConfigDetails($configGroups));
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Edit user details
     */
    public function userConfigEdit()
    {
        if (!trim($this->vars['password']) || !trim($this->vars['passwordConfirm'])) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'user');
        }
        if (trim($this->vars['password']) != trim($this->vars['passwordConfirm'])) {
            $this->badInputLoad($this->errors->text("inputError", "invalid"), 'user');
        }
        if ((!array_key_exists('email', $this->vars) || !trim($this->vars['email']))) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'user');
        }
        $this->user->writeUser(FALSE); // FALSE = editing user
        include_once("core/modules/email/EMAIL.php");
        $emailClass = new EMAIL();
        if (!$emailClass->userEdit()) {
            $this->badInputLoad($this->errors->text("inputError", "mail", GLOBALS::getError()), 'user');
        }
        $this->init([$this->success->text("userEdit"), $this->vars['selectItem']]);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Edit resource display details
     */
    public function resourcesConfigEdit()
    {
        // checkInput writes the session
        $this->checkResourcesInput();
        // If this is a logged on user, write preferences to WKX_user_preferences
        if ($this->session->getVar("setup_UserId")) {
            $user = FACTORY_USER::getInstance();
            $user->writePreferences($this->session->getVar("setup_UserId"));
        }
        $this->init([$this->success->text("config"), $this->vars['selectItem']]);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Check resource display input
     */
    public function checkResourcesInput()
    {
        // All input good - write to session
        $required = ["Paging", "PagingMaxLinks", "StringLimit", "PagingTagCloud"];
        foreach ($required as $key) {
            if (!is_numeric($this->vars[$key]) || !is_int($this->vars[$key] + 0)) { // cast to number
                $this->badInputLoad($this->errors->text("inputError", "nan", " ($key) "), 'resources');
            }
            if (!array_key_exists($key, $this->vars) || !$this->vars[$key]) {
                $this->badInputLoad($this->errors->text("inputError", "missing", " ($key) "), 'resources');
            }
            if (($key == 'PagingMaxLinks') && ($this->vars[$key] < 4)) {
                $this->vars[$key] = 11;
            } elseif ($this->vars[$key] < 0) {
                $this->vars[$key] = -1;
            }
            GLOBALS::setUserVar($key, $this->vars[$key]);
        }
        GLOBALS::setUserVar("PagingStyle", $this->vars["PagingStyle"]);
        $this->session->delVar("sql_LastMulti"); // always reset in case of paging changes
        $this->session->delVar("sql_LastIdeaSearch"); // always reset in case of paging changes
        foreach (['UseWikindxKey', 'UseBibtexKey', 'DisplayBibtexLink', 'DisplayCmsLink', 'ListLink'] as $key) {
            // $this->session->setVar("setup_" . $key, TRUE);
            // $this->session->delVar("setup_" . $key);
            GLOBALS::setUserVar($key, array_key_exists($key, $this->vars));
        }
    }
    /**
     * Display appearance config options
     *
     * @return string
     */
    public function appearanceConfigDisplay()
    {
        $pString = $this->errorString;
        $pString .= \FORM\hidden("method", "appearanceConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        $subTd = \HTML\tableStart();
        $subTd .= \HTML\trStart();
        // Display the global template but change the default selection of the list to the default template when no template is defined or a template not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $templates = FACTORY_TEMPLATE::getInstance()->loadDir();
        $template = GLOBALS::getUserVar("Template", WIKINDX_TEMPLATE_DEFAULT);
        array_key_exists($template, $templates) ? $template = $template : $template = WIKINDX_TEMPLATE_DEFAULT;
        $subTd .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "template"),
            "Template",
            $templates,
            $template,
            4
        ) . " " . \HTML\span('*', 'required'));
        
        $menus[0] = $this->messages->text("config", "templateMenu1");
        $menus[1] = $this->messages->text("config", "templateMenu2");
        $menus[2] = $this->messages->text("config", "templateMenu3");
        $subTd .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "templateMenu"),
            "TemplateMenu",
            $menus,
            GLOBALS::getUserVar('TemplateMenu'),
            3
        )
             . " " . \HTML\span('*', 'required'));
        $subTd .= \HTML\trEnd();
        $subTd .= \HTML\tableEnd();
        $pString .= \HTML\td($subTd);
        
        // For the graphical interface, add the "auto" value that allows to say that the language is chosen by the browser.
        $LanguageNeutralChoice = "auto";
        $languages[$LanguageNeutralChoice] = "Auto";
        $languages = array_merge($languages, \LOCALES\getSystemLocales());
        
        // Don't use the session value in that case because the language could have been changed locally by the chooseLanguage plugin
        $userId = $this->session->getVar("setup_UserId");
        $this->db->formatConditions(['usersId' => $userId]);
        $language = $this->db->selectFirstField("users", "usersLanguage");
        array_key_exists($language, $languages) ? $language = $language : $language = $LanguageNeutralChoice;
        
        // Retrieve the language of the user config in session if missing in the db
        if ($language == $LanguageNeutralChoice) {
            $language = $this->session->getVar("setup_Language", $LanguageNeutralChoice);
            array_key_exists($language, $languages) ? $language = $language : $language = $LanguageNeutralChoice;
        }
        
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "language"),
            "Language",
            $languages,
            $language
        ) . " " . \HTML\span('*', 'required'));
        
        // Display the user style but change the default selection of the list to the default style when no style is defined or a style not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $styles = \LOADSTYLE\loadDir();
        $style = GLOBALS::getUserVar("Style", WIKINDX_STYLE_DEFAULT);
        array_key_exists($style, $styles) ? $style = $style : $style = WIKINDX_STYLE_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "style"),
            "Style",
            $styles,
            $style,
            4
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Edit appearance display details
     */
    public function appearanceConfigEdit()
    {
        // checkInput writes the session
        $this->checkAppearanceInput();
        // If this is a logged on user, write preferences to WKX_user_preferences
        if ($this->session->getVar("setup_UserId")) {
            $user = FACTORY_USER::getInstance();
            $user->writePreferences($this->session->getVar("setup_UserId"));
        }
        $this->session->setVar("mywikindx_Message", $this->success->text("config"));
        $this->session->setVar("mywikindx_Item", $this->vars['selectItem']);
        // need to use header() to ensure any change in appearance is immediately picked up.
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init");
    }
    /**
     * Check appearance display input
     */
    public function checkAppearanceInput()
    {
        $required = ["Language", "Template", "TemplateMenu", "Style"];
        foreach ($required as $key) {
            if (!array_key_exists($key, $this->vars) || (!$this->vars[$key] && ($key != 'TemplateMenu'))) {
                $this->badInputLoad($this->errors->text("inputError", "missing", " ($key) "), 'appearance');
            }
            GLOBALS::setUserVar($key, $this->vars[$key]);
            //           $array[$value] = $this->vars[$value];
        }
        /*        if (!array_key_exists("TemplateMenu", $this->vars))
                {
                    $this->badInputLoad($this->errors->text("inputError", "missing", " (TemplateMenu) "), 'appearance');
                }
                else
                {
                    $array['TemplateMenu'] = $this->vars['TemplateMenu'];
                }
        */        // All input good - write to session
//        $this->session->writeArray($array, "setup");
    }
    /**
     * Edit forgotten password details
     */
    public function forgetConfigEdit()
    {
        include_once('core/modules/usersgroups/FORGET.php');
        $forget = new FORGET();
        list($success, $message) = $forget->forgetWrite();
        if ($success === FALSE) {
            $this->badInputLoad($message, 'forget');
        }
        $this->init([$this->success->text("forgetUpdate"), $this->vars['selectItem']]);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Set email notification
     */
    public function notificationConfigEdit()
    {
        if (!array_key_exists('Notify', $this->vars) || !$this->vars['Notify']) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'notification');
        }
        $updateArray = ['usersNotify' => $this->vars['Notify']];
        if (array_key_exists('NotifyAdd', $this->vars) && array_key_exists('NotifyEdit', $this->vars)) {
            $this->session->setVar("mywikindx_NotifyAdd", TRUE);
            $this->session->setVar("mywikindx_NotifyEdit", TRUE);
            $updateArray['usersNotifyAddEdit'] = 'A';
        } elseif (array_key_exists('NotifyAdd', $this->vars)) {
            $this->session->setVar("mywikindx_NotifyAdd", TRUE);
            $this->session->delVar("mywikindx_NotifyEdit");
            $updateArray['usersNotifyAddEdit'] = 'N';
        } elseif (array_key_exists('NotifyEdit', $this->vars)) {
            $this->session->setVar("mywikindx_NotifyEdit", TRUE);
            $this->session->delVar("mywikindx_NotifyAdd");
            $updateArray['usersNotifyAddEdit'] = 'E';
        } else {
            $this->session->setVar("mywikindx_NotifyAdd", TRUE);
            $this->session->setVar("mywikindx_NotifyEdit", TRUE);
            $updateArray['usersNotifyAddEdit'] = 'A';
        }
        if (array_key_exists('NotifyThreshold', $this->vars)) {
            $this->session->setVar("mywikindx_NotifyThreshold", $this->vars['NotifyThreshold']);
            $updateArray['usersNotifyThreshold'] = $this->vars['NotifyThreshold'];
        }
        if (array_key_exists('DigestThreshold', $this->vars)) {
            $input = trim($this->vars['DigestThreshold']) + 0;
            if (is_int($input) && ($input > 0)) {
                $this->session->setVar("mywikindx_NotifyDigestThreshold", trim($this->vars['DigestThreshold']));
                $updateArray['usersNotifyDigestThreshold'] = trim($this->vars['DigestThreshold']);
            } else {
                $this->session->setVar("mywikindx_NotifyDigestThreshold", 100);
                $updateArray['usersNotifyDigestThreshold'] = 100;
            }
        }
        $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
        $this->db->update('users', $updateArray);
        $this->session->setVar("mywikindx_Notify", $this->vars['Notify']);
        $this->init([$this->success->text("notify"), $this->vars['selectItem']]);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Display user groups config options
     *
     * @return string
     */
    public function userGroupsConfigDisplay()
    {
        $pString = $this->errorString;
        $pString .= \FORM\hidden("method", "userGroupsConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        if (!$groups = $this->user->listUserGroups()) {
            $pString .= \HTML\td($this->messages->text("user", "noGroups"));
            $radios = \HTML\p(\FORM\radioButton(FALSE, "method", "createUserGroupInit", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "createGroup"), FALSE, "left");
        } else {
            $pString .= \HTML\td(\FORM\selectFBoxValue($this->messages->text("user", "groups"), "groupId", $groups, 5));
            if (!$this->session->getVar("mywikindx_group_radio")) {
                $checked = TRUE;
            } else {
                $checked = $this->session->getVar("mywikindx_group_radio") == 'create' ? TRUE : FALSE;
            }
            $radios = \HTML\p(\FORM\radioButton(FALSE, "method", "createUserGroupInit", $checked) .
                "&nbsp;&nbsp;" . $this->messages->text("user", "createGroup"), FALSE, "left");
            $checked = $this->session->getVar("mywikindx_group_radio") == 'edit' ? TRUE : FALSE;
            $radios .= \HTML\p(\FORM\radioButton(FALSE, "method", "editUserGroupInit", $checked) .
                "&nbsp;&nbsp;" . $this->messages->text("user", "editGroup"), FALSE, "left");
            $checked = $this->session->getVar("mywikindx_group_radio") == 'delete' ? TRUE : FALSE;
            $radios .= \HTML\p(\FORM\radioButton(FALSE, "method", "deleteUserGroupInit", $checked) .
                "&nbsp;&nbsp;" . $this->messages->text("user", "deleteGroup"), FALSE, "left");
        }
        $pString .= \HTML\td($radios);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display further user groups settings for creating
     *
     * @param mixed $error
     */
    public function createUserGroupInit($error = FALSE)
    {
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $this->session->delVar("mywikindx_group_add");
        if (!$error) {
            $this->session->delVar("mywikindx_groupTitle");
            $this->session->delVar("mywikindx_groupDescription");
        }
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "createGroup")
        ));
        $this->session->delVar("mywikindx_groupLock");
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script type="text/javascript" src="' . WIKINDX_BASE_URL . '/core/modules/usersgroups/mywikindx.js"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "createUserGroup");
        $pString .= $tinymce->loadBasicTextarea();
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $sessVar = $this->session->issetVar("mywikindx_groupTitle") ?
            \HTML\dbToFormTidy($this->session->getVar("mywikindx_groupTitle")) : FALSE;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "groupTitle"),
            "title",
            $sessVar,
            50,
            255
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $sessVar = $this->session->issetVar("mywikindx_groupDescription") ?
            \HTML\dbToFormTidy($this->session->getVar("mywikindx_groupDescription")) : FALSE;
        $pString .= \HTML\td(\FORM\textAreaInput(
            $this->messages->text("user", "groupDescription"),
            "description",
            $sessVar,
            80,
            10
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $users = $this->user->grabAll(TRUE);
        unset($users[$this->session->getVar("setup_UserId")]);
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "multiples"));
        $pString .= \HTML\p(\FORM\selectFBoxValueMultiple(
            $this->messages->text("user", "groupUserAdd"),
            "addUsers",
            $users,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $jString = "onclick=\"javascript:sendMywikindxInput();return true;\"";
        $pString .= \HTML\p(\FORM\formSubmitButton($this->messages->text("submit", "Add"), FALSE, $jString));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader("usersgroups_WIKINDX_CORE&amp;method=init", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "userGroups");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Create the user group
     */
    public function createUserGroup()
    {
        if (array_key_exists('description', $this->vars) && trim($this->vars['description'])) {
            $description = json_decode($this->vars['description']);
            $this->session->setVar("mywikindx_groupDescription", $description);
            $fields[] = 'usergroupsDescription';
            $values[] = $description;
        }
        if (!$title = json_decode($this->vars['title'])) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'userGroups');
        }
        $this->session->setVar("mywikindx_groupTitle", $title);
        if ($this->session->getVar("mywikindx_groupLock")) {
            $this->badInputLoad($this->errors->text("done", "group"), 'userGroups');
        }
        $this->checkUserGroupExists($title, FALSE);
        $userId = $this->session->getVar("setup_UserId");
        $fields[] = 'usergroupsTitle';
        $values[] = $title;
        $fields[] = 'usergroupsAdminId';
        $values[] = $userId;
        $this->db->insert('user_groups', $fields, $values);
        $groupId = $this->db->lastAutoId();
        $userIds[] = $userId;
        $users = json_decode($this->vars['addUsers']);
        foreach ($users as $userId) {
            if (!$userId) { // IGNORE
                continue;
            }
            $userIds[] = $userId;
        }
        // Insert new users
        foreach ($userIds as $id) {
            $this->db->insert('user_groups_users', ['usergroupsusersUserId', 'usergroupsusersGroupId'], [$id, $groupId]);
        }
        $this->session->setVar("mywikindx_groupLock", TRUE);
        $this->session->delVar("mywikindx_groupDescription");
        $this->session->delVar("mywikindx_groupTitle");
        $this->init([$this->success->text("groupAdd"), 'userGroups']);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Display further user groups settings for editing
     *
     * @param false|string $error
     */
    public function editUserGroupInit($error = FALSE)
    {
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $this->session->delVar("mywikindx_group_edit");
        if (!$error) {
            $this->session->delVar("mywikindx_groupTitle");
            $this->session->delVar("mywikindx_groupDescription");
        }
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "editGroup")
        ));
        $this->session->delVar("mywikindx_groupLock");
        if (!array_key_exists('groupId', $this->vars) || !$this->vars['groupId']) {
            $this->badInputPopup($this->errors->text("inputError", "missing"));
        }
        $this->checkValidUserGroup();
        $groupUsers = [];
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script type="text/javascript" src="' . WIKINDX_BASE_URL . '/core/modules/usersgroups/mywikindx.js"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "editUserGroup");
        $pString .= \FORM\hidden("groupId", $this->vars['groupId']);
        $pString .= $tinymce->loadBasicTextarea();
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        if ($this->session->issetVar("mywikindx_groupTitle")) {
            $title = \HTML\dbToFormTidy($this->session->getVar("mywikindx_groupTitle"));
            $description = \HTML\dbToFormTidy($this->session->getVar("mywikindx_groupDescription"));
        } else {
            $this->db->formatConditions(['usergroupsId' => $this->vars['groupId']]);
            $recordset = $this->db->select('user_groups', ['usergroupsTitle', 'usergroupsDescription']);
            $row = $this->db->fetchRow($recordset);
            $title = \HTML\dbToFormTidy($row['usergroupsTitle']);
            $description = \HTML\dbToFormTidy($row['usergroupsDescription']);
            $this->db->formatConditions(['usergroupsusersGroupId' => $this->vars['groupId']]);
            $this->db->leftJoin('users', 'usersId', 'usergroupsusersUserId');
            $recordset = $this->db->select(
                'user_groups_users',
                ['usergroupsusersUserId', 'usersUsername', 'usersFullname', 'usersAdmin']
            );
            // add 0 => IGNORE to $array
            $groupUsers[0] = $this->messages->text("misc", "ignore");
            while ($row = $this->db->fetchRow($recordset)) {
                if (!$row['usergroupsusersUserId']) {
                    continue;
                }
                if ($row['usergroupsusersUserId'] == $this->session->getVar("setup_UserId")) {
                    continue;
                }
                $groupUsers[$row['usergroupsusersUserId']] = \HTML\dbToFormTidy($row['usersUsername']);
                if ($row['usersFullname']) {
                    $groupUsers[$row['usergroupsusersUserId']] .= " (" . \HTML\dbToFormTidy($row['usersFullname']) . ")";
                }
                if ($row['usersAdmin']) {
                    $groupUsers[$row['usergroupsusersUserId']] .= " ADMIN";
                }
            }
        }
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "groupTitle"),
            "title",
            $title,
            50,
            255
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\textAreaInput(
            $this->messages->text("user", "groupDescription"),
            "description",
            $description,
            80,
            10
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $users = $this->user->grabAll(TRUE);
        // add 0 => IGNORE to $array
        $temp[0] = $this->messages->text("misc", "ignore");
        foreach ($users as $key => $value) {
            if ($key == $this->session->getVar("setup_UserId")) {
                continue;
            }
            if (array_key_exists($key, $groupUsers)) {
                continue;
            }
            $temp[$key] = $value;
        }
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "multiples"));
        if (count($groupUsers) > 1) {
            $pString .= \HTML\td(\FORM\selectFBoxValueMultiple($this->messages->text(
                "user",
                "groupUserDelete"
            ), "deleteUsers", $groupUsers, 10) . BR . \HTML\span($hint, 'hint'));
        }
        if (count($temp) > 1) {
            $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
                $this->messages->text("user", "groupUserAdd"),
                "addUsers",
                $temp,
                10
            ) . BR . \HTML\span($hint, 'hint'));
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        
        $jString = "onclick=\"javascript:sendMywikindxInput();return true;\"";
        $pString .= \HTML\p(\FORM\formSubmitButton($this->messages->text("submit", "Edit"), FALSE, $jString));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader("usersgroups_WIKINDX_CORE&amp;method=init", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "userGroups");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * edit a user group
     */
    public function editUserGroup()
    {
        if ($this->session->getVar("mywikindx_groupLock")) {
            $this->badInputLoad($this->errors->text("done", "group"), 'userGroups');
        }
        if (!array_key_exists('groupId', $this->vars) || !$this->vars['groupId']) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'userGroups');
        }
        if (!$title = json_decode(trim($this->vars['title']))) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'userGroups');
        }
        $this->session->setVar("mywikindx_groupTitle", $title);
        $this->checkValidUserGroup('userGroups');
        $this->checkUserGroupExists($title, $this->vars['groupId']); // Check for title duplicate
        if ($description = trim($this->vars['description'])) {
            $description = json_decode($description);
            $this->session->setVar("mywikindx_groupDescription", $description);
            $updateArray['usergroupsDescription'] = $description;
        } else {
            $this->db->formatConditions(['usergroupsId' => $this->vars['groupId']]);
            $this->db->updateNull('user_groups', 'usergroupsDescription');
        }
        $updateArray['usergroupsTitle'] = $title;
        $this->db->formatConditions(['usergroupsId' => $this->vars['groupId']]);
        $this->db->update('user_groups', $updateArray);
        // delete any users
        if (array_key_exists('deleteUsers', $this->vars)) {
            $users = json_decode($this->vars['deleteUsers']);
            foreach ($users as $userId) { // to be deleted
                if (!$userId) { // 'IGNORE'
                    continue;
                }
                $this->db->formatConditions(['usergroupsusersGroupId' => $this->vars['groupId']]);
                $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
                $this->db->delete('user_groups_users');
            }
        }
        // Insert new users
        $addUsers = [];
        $users = json_decode($this->vars['addUsers']);
        foreach ($users as $userId) {
            if (!$userId) { // IGNORE
                continue;
            }
            $addUsers[] = $userId;
        }
        foreach ($addUsers as $id) {
            $this->db->insert(
                'user_groups_users',
                ['usergroupsusersUserId', 'usergroupsusersGroupId'],
                [$id, $this->vars['groupId']]
            );
        }
        $this->session->setVar("mywikindx_groupLock", TRUE);
        $this->session->delVar("mywikindx_groupDescription");
        $this->session->delVar("mywikindx_groupTitle");
        $this->init([$this->success->text("groupEdit"), 'userGroups']);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Display further user groups settings for deleting
     *
     * @param mixed $error
     */
    public function deleteUserGroupInit($error = FALSE)
    {
        if (!array_key_exists('groupId', $this->vars) || !$this->vars['groupId']) {
            $this->badInputPopup($this->errors->text("inputError", "invalid"));
        }
        $this->checkValidUserGroup();
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "deleteGroup")
        ));
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $this->db->formatConditions(['usergroupsId' => $this->vars['groupId']]);
        $title = \HTML\strong($this->db->selectFirstField('user_groups', 'usergroupsTitle'));
        $pString .= \HTML\p($this->messages->text("user", "deleteConfirmGroup") . ":&nbsp;&nbsp;" .
            \HTML\nlToHtml($title));
        $pString .= \HTML\p($this->messages->text("user", "deleteGroup2"));
        $this->session->delVar("mywikindx_group_delete");
        $this->session->delVar("mywikindx_groupLock");
        $pString .= '<script type="text/javascript" src="' . WIKINDX_BASE_URL . '/core/modules/usersgroups/mywikindx.js"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "deleteUserGroup");
        $pString .= \FORM\hidden("groupId", $this->vars['groupId']);
        $jString = "onclick=\"javascript:sendMywikindxInput();return true;\"";
        $pString .= \HTML\p(\FORM\formSubmitButton($this->messages->text("submit", "Delete"), FALSE, $jString));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader("usersgroups_WIKINDX_CORE&amp;method=init", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "userGroups");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Delete user group
     */
    public function deleteUserGroup()
    {
        if (!array_key_exists('groupId', $this->vars) || !$this->vars['groupId']) {
            $this->badInputLoad($this->errors->text("inputError", "invalid"), 'userGroups');
        }
        $this->checkValidUserGroup('userGroups');
        // Get any bibliographyIds and delete those bibliographies
        $this->db->formatConditions(['userbibliographyUserGroupId' => $this->vars['groupId']]);
        $recordset = $this->db->select('user_bibliography', 'userbibliographyId');
        while ($row = $this->db->fetchRow($recordset)) {
            $bibIds[] = $row['userbibliographyId'];
        }
        if (isset($bibIds)) {
            $this->db->formatConditionsOneField($bibIds, 'userbibliographyresourceBibliographyId');
            $this->db->delete('user_bibliography_resource');
        }
        $this->db->formatConditions(['userbibliographyUserGroupId' => $this->vars['groupId']]);
        $this->db->delete('user_bibliography');
        // delete users from usergroup
        $this->db->formatConditions(['usergroupsusersGroupId' => $this->vars['groupId']]);
        $this->db->delete('user_groups_users');
        // Delete usergroup
        $this->db->formatConditions(['usergroupsId' => $this->vars['groupId']]);
        $this->db->delete('user_groups');
        $this->init([$this->success->text("groupDelete"), 'userGroups']);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Display user tags config options
     *
     * @return string
     */
    public function userTagsConfigDisplay()
    {
        // Get this user's user tags
        $userTagsObject = FACTORY_USERTAGS::getInstance();
        $userTags = $userTagsObject->grabAll();
        $pString = $this->errorString;
        $pString .= \FORM\hidden("method", "userTagsConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        if (empty($userTags)) {
            $pString .= \HTML\td($this->messages->text("user", "noUserTags"));
            $radios = \HTML\p(\FORM\radioButton(FALSE, "method", "createUserTagInit", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "createUserTag"), FALSE, "left");
        } else {
            $pString .= \HTML\td(\FORM\selectFBoxValue(FALSE, "tagId", $userTags, 5));
            $radios = \HTML\p(\FORM\radioButton(FALSE, "method", "createUserTagInit", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "createUserTag"), FALSE, "left");
            $radios .= \HTML\p(\FORM\radioButton(FALSE, "method", "editUserTagInit", FALSE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "editUserTag"), FALSE, "left");
            $radios .= \HTML\p(\FORM\radioButton(FALSE, "method", "deleteUserTagInit", FALSE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "deleteUserTag"), FALSE, "left");
        }
        $pString .= \HTML\td($radios);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display further user tags settings for creating
     *
     * @param mixed $error
     */
    public function createUserTagInit($error = FALSE)
    {
        $this->session->delVar("mywikindx_tag_add");
        if (!$error) {
            $this->session->delVar("mywikindx_tagTitle");
        }
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "createUserTag")
        ));
        $this->session->delVar("mywikindx_tagLock");
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script type="text/javascript" src="' . WIKINDX_BASE_URL . '/core/modules/usersgroups/mywikindx.js"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "createUserTag");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $sessVar = $this->session->issetVar("mywikindx_tagTitle") ?
            \HTML\dbToFormTidy($this->session->getVar("mywikindx_tagTitle")) : FALSE;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "tagTitle"),
            "title",
            $sessVar,
            50,
            255
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $jString = "onclick=\"javascript:sendMywikindxInput();return true;\"";
        $pString .= \HTML\p(\FORM\formSubmitButton($this->messages->text("submit", "Add"), FALSE, $jString));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader("usersgroups_WIKINDX_CORE&amp;method=init", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "userTags");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * create a user tag
     */
    public function createUserTag()
    {
        if ($this->session->getVar("mywikindx_tagLock")) {
            $this->badInputLoad($this->errors->text("done", "userTag"), 'userTags');
        }
        if (!$title = json_decode(trim($this->vars['title']))) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'userTags');
        }
        $this->session->setVar("mywikindx_Title", $title);
        $userTagsObject = FACTORY_USERTAGS::getInstance();
        if ($userTagsObject->checkExists($title)) {
            $this->badInputLoad($this->errors->text('inputError', 'userTagExists'), 'userTags');
        }
        $fields[] = 'usertagsTag';
        $values[] = $title;
        $fields[] = 'usertagsUserId';
        $values[] = $this->session->getVar("setup_UserId");
        $this->db->insert('user_tags', $fields, $values);
        $this->session->setVar("mywikindx_tagLock", TRUE);
        $this->session->delVar("mywikindx_Title");
        $this->init([$this->success->text("usertagAdd"), 'userTags']);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Display further user tags settings for editing
     *
     * @param mixed $error
     */
    public function editUserTagInit($error = FALSE)
    {
        $this->session->delVar("mywikindx_tag_edit");
        if (!$error) {
            $this->session->delVar("mywikindx_tagTitle");
        }
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "editUserTag")
        ));
        $this->session->delVar("mywikindx_tagLock");
        if (!array_key_exists('tagId', $this->vars) || !$this->vars['tagId']) {
            $this->badInputPopup($this->errors->text("inputError", "missing"));
        }
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script type="text/javascript" src="' . WIKINDX_BASE_URL . '/core/modules/usersgroups/mywikindx.js"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "editUserTag");
        $pString .= \FORM\hidden("tagId", $this->vars['tagId']);
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        if ($this->session->issetVar("mywikindx_tagTitle")) {
            $title = \HTML\dbToFormTidy($this->session->getVar("mywikindx_tagTitle"));
        } else {
            $this->db->formatConditions(['usertagsId' => $this->vars['tagId']]);
            $recordset = $this->db->select('user_tags', 'usertagsTag');
            $row = $this->db->fetchRow($recordset);
            $title = \HTML\dbToFormTidy($row['usertagsTag']);
        }
        $userTagsObject = FACTORY_USERTAGS::getInstance();
        if (!$userTagsObject->checkExists($title)) {
            $this->badInputPopup($this->errors->text('inputError', 'invalid'));
        }
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "tagTitle"),
            "title",
            $title,
            50,
            255
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $jString = "onclick=\"javascript:sendMywikindxInput();return true;\"";
        $pString .= \HTML\p(\FORM\formSubmitButton($this->messages->text("submit", "Edit"), FALSE, $jString));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader("usersgroups_WIKINDX_CORE&amp;method=init", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "userTags");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * edit a user tag
     */
    public function editUserTag()
    {
        if ($this->session->getVar("mywikindx_tagLock")) {
            $this->badInputLoad($this->errors->text("done", "userTag"), 'userTags');
        }
        if (!$title = json_decode(trim($this->vars['title']))) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'userTags');
        }
        if (!$tagId = json_decode($this->vars['tagId'])) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'userTags');
        }
        $this->session->setVar("mywikindx_Title", $title);
        $userTagsObject = FACTORY_USERTAGS::getInstance();
        $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['usertagsId' => $tagId]);
        $recordSet = $this->db->select('user_tags', 'usertagsId');
        if (!$this->db->numRows($recordSet)) {
            $this->badInputLoad($this->errors->text('inputError', 'invalid'), 'userTags');
        }
        $this->db->formatConditions(['usertagsId' => $tagId]);
        $this->db->update('user_tags', ['usertagsTag' => $title]);
        $this->session->setVar("mywikindx_tagLock", TRUE);
        $this->session->delVar("mywikindx_Title");
        $this->init([$this->success->text("usertagEdit"), 'userTags']);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Display further user tags settings for deleting
     *
     * @param mixed $error
     */
    public function deleteUserTagInit($error = FALSE)
    {
        if (!$tagId = json_decode($this->vars['tagId'])) {
            $this->badInputPopup($this->errors->text("inputError", "missing"));
        }
        $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['usertagsId' => $tagId]);
        $recordSet = $this->db->select('user_tags', 'usertagsId');
        if (!$this->db->numRows($recordSet)) {
            $this->badInputPopup($this->errors->text('inputError', 'invalid'));
        }
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "deleteUserTag")
        ));
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $this->db->formatConditions(['usertagsId' => $this->vars['tagId']]);
        $title = \HTML\strong($this->db->selectFirstField('user_tags', 'usertagsTag'));
        $pString .= \HTML\p($this->messages->text("user", "deleteConfirmUserTag") . ":&nbsp;&nbsp;" .
            \HTML\nlToHtml($title));
        $this->session->delVar("mywikindx_tag_delete");
        $this->session->delVar("mywikindx_tagLock");
        $pString .= '<script type="text/javascript" src="' . WIKINDX_BASE_URL . '/core/modules/usersgroups/mywikindx.js"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "deleteUserTag");
        $pString .= \FORM\hidden("tagId", $this->vars['tagId']);
        $jString = "onclick=\"javascript:sendMywikindxInput();return true;\"";
        $pString .= \HTML\p(\FORM\formSubmitButton($this->messages->text("submit", "Delete"), FALSE, $jString));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader("usersgroups_WIKINDX_CORE&amp;method=init", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "userTags");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Delete user tag
     */
    public function deleteUserTag()
    {
        if (!$tagId = json_decode($this->vars['tagId'])) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'userTags');
        }
        $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['usertagsId' => $tagId]);
        $recordSet = $this->db->select('user_tags', 'usertagsId');
        if (!$this->db->numRows($recordSet)) {
            $this->badInputLoad($this->errors->text('inputError', 'invalid'), 'userTags');
        }
        // Delete usertag
        $this->db->formatConditions(['usertagsId' => $this->vars['tagId']]);
        $this->db->delete('user_tags');
        // Remove user_tag ids from resource_user_tags.TagIds
        $this->db->formatConditions(['resourceusertagsTagId' => $this->vars['tagId']]);
        $this->db->delete('resource_user_tags');
        $this->init([$this->success->text("usertagDelete"), 'userTags']);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Display user bibs config options
     */
    public function userBibsConfigDisplay()
    {
        $bibs = $this->getBibs();
        $pString = $this->errorString;
        $pString .= \FORM\hidden("method", "userBibsConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        $groups = FALSE;
        $this->db->formatConditions(['usergroupsAdminId' => $this->session->getVar("setup_UserId")]);
        if ($this->db->numRows($this->db->select('user_groups', ['usergroupsId', 'usergroupsTitle']))) {
            $groups = TRUE;
        }
        if (empty($bibs)) {
            $pString .= \HTML\td($this->messages->text("user", "noBibs"));
            $radios = \HTML\p(\FORM\radioButton(FALSE, "method", "createUserBibInit", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "createBib"), FALSE, "left");
            if ($groups) {
                $radios .= \HTML\p(\FORM\radioButton(FALSE, "method", "createGroupBibInit", FALSE) . "&nbsp;&nbsp;" .
                    $this->messages->text("user", "createGroupBib"), FALSE, "left");
            }
        } else {
            $pString .= \HTML\td(\FORM\selectFBoxValue($this->messages->text("user", "bib"), "bibId", $bibs, 5));
            $radios = \HTML\p(\FORM\radioButton(FALSE, "method", "createUserBibInit", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "createBib"), FALSE, "left");
            if ($groups) {
                $radios .= \HTML\p(\FORM\radioButton(FALSE, "method", "createGroupBibInit", FALSE) . "&nbsp;&nbsp;" .
                    $this->messages->text("user", "createGroupBib"), FALSE, "left");
            }
            $radios .= \HTML\p(\FORM\radioButton(FALSE, "method", "editBibInit", FALSE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "editBib"), FALSE, "left");
            $radios .= \HTML\p(\FORM\radioButton(FALSE, "method", "deleteBibInit", FALSE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "deleteBib"), FALSE, "left");
        }
        $pString .= \HTML\td($radios);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display further user bibs settings for creating
     *
     * @param mixed $error
     * @param mixed $groupBib
     */
    public function createUserBibInit($error = FALSE, $groupBib = FALSE)
    {
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        // If creating a group bibliography, this user must own groups
        if ($groupBib) {
            GLOBALS::setTplVar('heading', $this->messages->text(
                "heading",
                "myWikindx",
                ": " . $this->messages->text("user", "createGroupBib")
            ));
            $this->db->formatConditions(['usergroupsAdminId' => $this->session->getVar("setup_UserId")]);
            $this->db->orderBy('usergroupsTitle');
            $recordset = $this->db->select('user_groups', ['usergroupsId', 'usergroupsTitle']);
            if (!$this->db->numRows($recordset)) {
                $this->badInputPopup($this->errors->text("inputError", "userHasNoGroups"));
            }
            while ($row = $this->db->fetchRow($recordset)) {
                $groups[$row['usergroupsId']] = \HTML\dbToFormTidy($row['usergroupsTitle']);
            }
            $groupString = \HTML\p(\FORM\selectFBoxValue(
                $this->messages->text("user", "addGroupsToBib"),
                "addUsers",
                $groups,
                10
            ));
        } else {
            $groupString = FALSE;
            GLOBALS::setTplVar('heading', $this->messages->text(
                "heading",
                "myWikindx",
                ": " . $this->messages->text("user", "createBib")
            ));
        }
        $this->session->delVar("mywikindx_bib_add");
        $this->session->delVar("mywikindx_bibLock");
        if (!$error) {
            $this->session->delVar("mywikindx_bibTitle");
            $this->session->delVar("mywikindx_bibDescription");
        }
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script type="text/javascript" src="' . WIKINDX_BASE_URL . '/core/modules/usersgroups/mywikindx.js"></script>';
        $pString .= \FORM\formHeader(FALSE);
        if ($groupBib) {
            $pString .= \FORM\hidden("method", "createGroupBib");
        } else {
            $pString .= \FORM\hidden("method", "createUserBib");
        }
        $pString .= $tinymce->loadBasicTextarea();
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $sessVar = $this->session->issetVar("mywikindx_bibTitle") ?
            \HTML\dbToFormTidy($this->session->getVar("mywikindx_bibTitle")) : FALSE;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "bibTitle"),
            "title",
            $sessVar,
            50,
            255
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $sessVar = $this->session->issetVar("mywikindx_bibDescription") ?
            \HTML\dbToFormTidy($this->session->getVar("mywikindx_bibDescription")) : FALSE;
        $pString .= \HTML\td(\FORM\textAreaInput(
            $this->messages->text("user", "bibDescription"),
            "description",
            $sessVar,
            80,
            10
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= $groupString;
        $jString = "onclick=\"javascript:sendMywikindxInput();return true;\"";
        $pString .= \HTML\p(\FORM\formSubmitButton($this->messages->text("submit", "Add"), FALSE, $jString));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader("usersgroups_WIKINDX_CORE&amp;method=init", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "userGroups");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Display further group bibs settings for creating
     *
     * @param mixed $error
     */
    public function createGroupBibInit($error = FALSE)
    {
        $this->createUserBibInit(FALSE, TRUE);
    }
    /**
     * create a user/group bibliography
     */
    public function createGroupBib()
    {
        $this->createUserBib();
    }
    /**
     * create a user/group bibliography
     */
    public function createUserBib()
    {
        if ($this->session->getVar("mywikindx_bibLock")) {
            $this->badInputLoad($this->errors->text("done", "bibliography"), 'userBibs');
        }
        if (array_key_exists('description', $this->vars)) {
            $description = json_decode(trim($this->vars['description']));
            if ($description) {
                $this->session->setVar("mywikindx_Description", $description);
                $fields[] = 'userbibliographyDescription';
                $values[] = $description;
            }
        }
        if (!$title = json_decode(trim($this->vars['title']))) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'userBibs');
        }
        $this->session->setVar("mywikindx_Title", $title);
        $this->checkBibliographyExists($title);
        $fields[] = 'userbibliographyTitle';
        $values[] = $title;
        $fields[] = 'userbibliographyUserId';
        $values[] = $this->session->getVar("setup_UserId");
        $groupIds = json_decode($this->vars['addUsers']);
        if ($groupIds[0]) { // [0] will be '0' if not a group bibliography
            $groupId = $groupIds[0];
            $fields[] = 'userbibliographyUserGroupId';
            $values[] = $groupId;
        } else {
            $groupId = FALSE;
        }
        $this->db->insert('user_bibliography', $fields, $values);
        $this->session->setVar("mywikindx_bibLock", TRUE);
        $this->session->delVar("mywikindx_Description");
        $this->session->delVar("mywikindx_Title");
        $this->session->setVar("setup_Bibliographies", TRUE);
        $this->init([$this->success->text("bibliographyAdd"), 'userBibs']);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Display further user/group bibs settings for editing
     *
     * @param mixed $error
     */
    public function editBibInit($error = FALSE)
    {
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $this->session->delVar("mywikindx_bib_edit");
        if (!$error) {
            $this->session->delVar("mywikindx_bibTitle");
            $this->session->delVar("mywikindx_bibDescription");
        }
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "editBib")
        ));
        $this->session->delVar("mywikindx_bibLock");
        if (!array_key_exists('bibId', $this->vars) || !$this->vars['bibId']) {
            $this->badInputPopup($this->errors->text("inputError", "missing"));
        }
        $this->checkValidBibliography($this->vars['bibId']);
        $groupUsers = [];
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script type="text/javascript" src="' . WIKINDX_BASE_URL . '/core/modules/usersgroups/mywikindx.js"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "editBib");
        $pString .= \FORM\hidden("bibId", $this->vars['bibId']);
        $pString .= $tinymce->loadBasicTextarea();
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        if ($this->session->issetVar("mywikindx_Title")) {
            $title = \HTML\dbToFormTidy($this->session->getVar("mywikindx_Title"));
            $description = \HTML\dbToFormTidy($this->session->getVar("mywikindx_Description"));
        } else {
            $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar("setup_UserId")]);
            $this->db->formatConditions(['userbibliographyId' => $this->vars['bibId']]);
            $recordset = $this->db->select(
                'user_bibliography',
                ['userbibliographyTitle', 'userbibliographyDescription', 'userbibliographyUserGroupId']
            );
            $row = $this->db->fetchRow($recordset);
            $description = \HTML\dbToFormTidy($row['userbibliographyDescription']);
            $title = \HTML\dbToFormTidy($row['userbibliographyTitle']);
            $userGroupId = $row['userbibliographyUserGroupId'];
        }
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "bibTitle"),
            "title",
            $title,
            50,
            255
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\textAreaInput(
            $this->messages->text("user", "bibDescription"),
            "description",
            $description,
            80,
            10
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        if ($userGroupId) { // This is a user group bibliography
            $pString .= BR . "&nbsp;" . BR;
            $pString .= \HTML\tableStart();
            $pString .= \HTML\trStart();
            $pString .= \FORM\hidden('groupBib', TRUE);
            $this->db->formatConditions(['usergroupsAdminId' => $this->session->getVar("setup_UserId")]);
            $this->db->orderBy('usergroupsTitle');
            $recordset = $this->db->select('user_groups', ['usergroupsId', 'usergroupsTitle']);
            while ($row = $this->db->fetchRow($recordset)) {
                $groups[$row['usergroupsId']] = \HTML\dbToFormTidy($row['usergroupsTitle']);
            }
            $pString .= \HTML\p(\FORM\selectedBoxValue(
                $this->messages->text("user", "addGroupsToBib"),
                "groupId",
                $groups,
                $userGroupId,
                10
            ));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
        }
        $jString = "onclick=\"javascript:sendMywikindxInput();return true;\"";
        $pString .= \HTML\p(\FORM\formSubmitButton($this->messages->text("submit", "Edit"), FALSE, $jString));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader("usersgroups_WIKINDX_CORE&amp;method=init", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "userBibs");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * edit a bibliography
     */
    public function editBib()
    {
        if ($this->session->getVar("mywikindx_bibLock")) {
            $this->badInputLoad($this->errors->text("done", "bibliography"), 'userBibs');
        }
        if (!$bibId = json_decode($this->vars['bibId'])) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'userBibs');
        }
        $this->checkValidBibliography($bibId, 'userBibs');
        if (!$title = json_decode(trim($this->vars['title']))) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'userBibs');
        }
        if ($description = json_decode(trim($this->vars['description']))) {
            $this->session->setVar("mywikindx_Description", $description);
            $updateArray['userbibliographyDescription'] = $description;
        } else {
            $this->db->formatConditions(['userbibliographyId' => $bibId]);
            $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar("setup_UserId")]);
            $this->db->updateNull('user_bibliography', 'userbibliographyDescription');
        }
        $updateArray['userbibliographyTitle'] = $title;
        if (array_key_exists('groupId', $this->vars)) { // user group bibliography
            $updateArray['userbibliographyUserGroupId'] = json_decode($this->vars['groupId']);
        }
        $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['userbibliographyId' => $bibId]);
        $this->db->update('user_bibliography', $updateArray);
        $this->session->setVar("mywikindx_bibLock", TRUE);
        $this->session->delVar("mywikindx_Description");
        $this->session->delVar("mywikindx_Title");
        $this->init([$this->success->text("bibliographyEdit"), 'userBibs']);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Ask for confirmation of delete bibliography
     *
     * @param mixed $error
     */
    public function deleteBibInit($error = FALSE)
    {
        if (!$bibId = $this->vars['bibId']) {
            $this->badInputPopup($this->errors->text("inputError", "missing"));
        }
        $this->checkValidBibliography($bibId);
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "deleteUserTag")
        ));
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $this->db->formatConditions(['userbibliographyId' => $bibId]);
        $title = \HTML\strong($this->db->selectFirstField('user_bibliography', 'userbibliographyTitle'));
        $pString .= \HTML\p($this->messages->text("user", "deleteConfirmBib") . ":&nbsp;&nbsp;" .
            \HTML\nlToHtml($title));
        $this->session->delVar("mywikindx_bib_delete");
        $this->session->delVar("mywikindx_bibLock");
        $pString .= '<script type="text/javascript" src="' . WIKINDX_BASE_URL . '/core/modules/usersgroups/mywikindx.js"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "deleteBib");
        $pString .= \FORM\hidden("bibId", $bibId);
        $jString = "onclick=\"javascript:sendMywikindxInput();return true;\"";
        $pString .= \HTML\p(\FORM\formSubmitButton($this->messages->text("submit", "Delete"), FALSE, $jString));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader("usersgroups_WIKINDX_CORE&amp;method=init", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "userBibs");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Delete user bibliography
     */
    public function deleteBib()
    {
        if ($this->session->getVar("mywikindx_bibLock")) {
            $this->badInputLoad($this->errors->text("done", "bibliography"), 'userBibs');
        }
        if (!$bibId = json_decode($this->vars['bibId'])) {
            $this->badInputLoad($this->errors->text("inputError", "missing"), 'userBibs');
        }
        $this->checkValidBibliography($bibId, 'userBibs');
        $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['userbibliographyId' => $bibId]);
        $this->db->delete('user_bibliography');
        // Get any bibliographyIds and delete those bibliographies
        $this->db->formatConditions(['userbibliographyresourceBibliographyId' => $bibId]);
        $this->db->delete('user_bibliography_resource');
        $this->session->setVar("mywikindx_bibLock", TRUE);
        if ($bibId == $this->session->getVar("mywikindx_Bibliography_use")) {
            $this->session->delVar("mywikindx_Bibliography_use");
        }
        $bibsU = $this->bib->getUserBibs();
        $bibsUG = $this->bib->getGroupBibs();
        if (empty($bibsU) && empty($bibsUG)) {
            $this->session->delVar("setup_Bibliographies");
        }
        $this->init([$this->success->text("bibliographyDelete"), 'userBibs']);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * create array of config menu items
     */
    private function getConfigGroups()
    {
        $groups = ['resources' => $this->messages->text('config', 'resources'),
            'appearance' => $this->messages->text('config', 'appearance'),
        ];
        if ($this->session->getVar("setup_UserId") != WIKINDX_RESTRICT_USERID) {
            if ($this->session->issetVar("mywikindx_Email") && WIKINDX_MAIL_USE) {
                $groups['forget'] = $this->messages->text('config', 'forget');
            }
            $groups['notification'] = $this->messages->text('config', 'notification');
            $groups['userTags'] = $this->messages->text("user", "userTags");
            $groups['userBibs'] = $this->messages->text('user', 'bib');
        }
        // Only for logged on users
        if ($this->session->getVar("setup_UserId")) {
            // Add user group administration only if there is more than one user.
            $resourceId = $this->db->select('users', 'usersId');
            if ($this->db->numRows($resourceId) > 1) {
                $groups['userGroups'] = $this->messages->text("user", "groups");
            }
            $user = ['user' => $this->messages->text('user', 'user')];
            $groups = $user + $groups;
        }

        return $groups;
    }
    /**
     * Get config details for menu items and put into form elements
     *
     * @param mixed $groups
     * @param mixed $item
     */
    private function getConfigDetails($groups, $item = FALSE)
    {
        if (array_key_exists('ajaxReturn', $this->vars)) {
            $item = $this->vars['ajaxReturn'];
        } elseif (!$item) { // grab the first of the list
            foreach ($groups as $item => $null) {
                break;
            }
        }
        if (($item != 'resources') || ($item != 'appearance')) {
            $gatekeep = FACTORY_GATEKEEP::getInstance();
            $gatekeep->requireSuper = FALSE;
            $gatekeep->init();
        }
        if ($item == 'user') {
            $password = FACTORY_PASSWORD::getInstance();
            $input = $this->session->getVar("setup_Username");
            list($formText, $jsString) = $password->createElements(FALSE);
            $pString = \FORM\formHeader("usersgroups_MYWIKINDX_CORE", 'onsubmit="return checkForm(' . $jsString . ');"');
        } elseif (($item == 'userGroups')) {
            $pString = \FORM\formHeader(FALSE);
            $pString .= \FORM\hidden("method", 'userGroupsConfigDisplay');
        } elseif (($item == 'userTags')) {
            $pString = \FORM\formHeader(FALSE);
            $pString .= \FORM\hidden("method", 'userTagsConfigDisplay');
        } elseif (($item == 'userBibs')) {
            $pString = \FORM\formHeader(FALSE);
            $pString .= \FORM\hidden("method", 'userBibsConfigDisplay');
        } else {
            $pString = \FORM\formHeader("usersgroups_MYWIKINDX_CORE");
        }
        $pString .= \FORM\hidden("selectItem", $item);
        switch ($item) {
            case 'user': // user configuration
                $pString .= $this->userConfigDisplay($formText);

                break;
            case 'resources': // resources display configuration
                $pString .= $this->resourcesConfigDisplay();

                break;
            case 'appearance': // appearance configuration
                $pString .= $this->appearanceConfigDisplay();

                break;
            case 'forget': // forgotten password configuration
                $pString .= $this->forgetConfigDisplay();

                break;
            case 'notification': // email notification configuration
                $pString .= $this->notificationConfigDisplay();

                break;
            case 'userGroups': // user groups configuration
                $pString .= $this->userGroupsConfigDisplay();

                break;
            case 'userTags': // user tags configuration
                $pString .= $this->userTagsConfigDisplay();

                break;
            case 'userBibs': // user bibs configuration
                $pString .= $this->userBibsConfigDisplay();

                break;
            default:
                $pString .= '';

                break;
        }
        if ($item == 'userGroups') {
            $pString .= \HTML\p(\FORM\formSubmitButton(
                $this->messages->text("submit", "Proceed"),
                FALSE,
                'onclick="return getMywikindxInputGroups();"'
            ));
        } elseif ($item == 'userTags') {
            $pString .= \HTML\p(\FORM\formSubmitButton(
                $this->messages->text("submit", "Proceed"),
                FALSE,
                'onclick="return getMywikindxInputTags();"'
            ));
        } elseif ($item == 'userBibs') {
            $pString .= \HTML\p(\FORM\formSubmitButton(
                $this->messages->text("submit", "Proceed"),
                FALSE,
                'onclick="return getMywikindxInputBibs();"'
            ));
        } else {
            $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        }
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * Display user config options
     *
     * @param mixed $formText
     */
    private function userConfigDisplay($formText)
    {
        $userArray = [['usersEmail' => 'Email'], ['usersFullname' => 'Fullname'], ['usersCookie' => 'Cookie']];
        $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
        $recordset = $this->db->select('users', $userArray);
        if (!$this->db->numRows($recordset)) {
            $this->badInputLoad($this->errors->text("dbError", "read"), 'user');
        }
        $row = $this->db->fetchRow($recordset);
        $pString = $this->errorString;
        $pString .= \FORM\hidden("method", "userConfigEdit");
        $pString .= \FORM\hidden("uname", $this->userName);
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        $pString .= $formText;
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "email"),
            "email",
            $row["Email"],
            30
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "fullname"),
            "fullname",
            $row["Fullname"],
            30
        ));
        $cookie = $row["Cookie"] == 'Y' ? 'CHECKED' : FALSE;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("user", "cookie"), "cookie", $cookie));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display resources display options
     */
    private function resourcesConfigDisplay()
    {
        $pString = $this->errorString;
        $pString .= \FORM\hidden("method", "resourcesConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "paging"),
            "Paging",
            GLOBALS::getUserVar('Paging'),
            5
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingMaxLinks"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "maxPaging"),
            "PagingMaxLinks",
            GLOBALS::getUserVar('PagingMaxLinks'),
            5
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        if (!GLOBALS::getUserVar("PagingTagCloud")) {
            GLOBALS::setUserVar("PagingTagCloud", WIKINDX_PAGING_TAG_CLOUD_DEFAULT);
        }
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "pagingTagCloud"),
            "PagingTagCloud",
            GLOBALS::getUserVar("PagingTagCloud"),
            5
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "stringLimit"),
            "StringLimit",
            GLOBALS::getUserVar("StringLimit"),
            5
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "pagingStyle"),
            "PagingStyle",
            ["N" => "Numerical", "A" => "Alphabetical"],
            GLOBALS::getUserVar('PagingStyle'),
            2
        ));
        $input = GLOBALS::getUserVar("UseWikindxKey") ? "CHECKED" : FALSE;
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "useWikindxKey"),
            "UseWikindxKey",
            $input
        ));
        $input = GLOBALS::getUserVar("UseBibtexKey") ? "CHECKED" : FALSE;
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "useBibtexKey"),
            "UseBibtexKey",
            $input
        ));
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $input = GLOBALS::getUserVar('DisplayBibtexLink') ? "CHECKED" : FALSE;
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "displayBibtexLink"),
            "DisplayBibtexLink",
            $input
        ));
        $input = GLOBALS::getUserVar('DisplayCmsLink') ? "CHECKED" : FALSE;
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "displayCmsLink"),
            "DisplayCmsLink",
            $input
        ));
        $input = GLOBALS::getUserVar('ListLink') ? "CHECKED" : FALSE;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "ListLink"), "ListLink", $input));
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display forgotten password config options
     */
    private function forgetConfigDisplay()
    {
        include_once('core/modules/usersgroups/FORGET.php');
        $forget = new FORGET();
        $pString = $this->errorString;
        $pString .= \FORM\hidden("method", "forgetConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($forget->forgetSet());
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Email notification config options
     */
    private function notificationConfigDisplay()
    {
        $pString = $this->errorString;
        $pString .= \FORM\hidden("method", "notificationConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        if (!$this->session->getVar("mywikindx_Notify")) {
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $recordset = $this->db->select('users', ['usersNotify', 'usersNotifyAddEdit',
                'usersNotifyThreshold', 'usersNotifyTimestamp', 'usersNotifyDigestThreshold', ]);
            $row = $this->db->fetchRow($recordset);
            $this->session->setVar("mywikindx_Notify", $row['usersNotify']);
            if ($row['usersNotifyAddEdit'] == 'A') {
                $this->session->setVar("mywikindx_NotifyAdd", TRUE);
                $this->session->setVar("mywikindx_NotifyEdit", TRUE);
            } elseif ($row['usersNotifyAddEdit'] == 'N') {
                $this->session->setVar("mywikindx_NotifyAdd", TRUE);
                $this->session->delVar("mywikindx_NotifyEdit");
            } elseif ($row['usersNotifyAddEdit'] == 'E') {
                $this->session->setVar("mywikindx_NotifyEdit", TRUE);
                $this->session->delVar("mywikindx_NotifyAdd");
            }
            $this->session->setVar("mywikindx_NotifyThreshold", $row['usersNotifyThreshold']);
            $this->session->setVar("mywikindx_NotifyTimestamp", $row['usersNotifyTimestamp']);
            $this->session->setVar("mywikindx_NotifyDigestThreshold", $row['usersNotifyDigestThreshold']);
        }
        $pString .= \HTML\tdStart();
        $checked = $this->session->getVar("mywikindx_Notify") == 'N' ? TRUE : FALSE;
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "N", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyNone"));
        $checked = $this->session->getVar("mywikindx_Notify") == 'A' ? TRUE : FALSE;
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "A", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyAll"));
        $checked = $this->session->getVar("mywikindx_Notify") == 'M' ? TRUE : FALSE;
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "M", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyMyBib"));
        $checked = $this->session->getVar("mywikindx_Notify") == 'C' ? TRUE : FALSE;
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "C", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyMyCreator"));
        $add = $this->session->issetVar("mywikindx_NotifyAdd") ? 'CHECKED' : FALSE;
        $edit = $this->session->issetVar("mywikindx_NotifyEdit") ? 'CHECKED' : FALSE;
        $pString .= \HTML\p($this->messages->text("user", "notifyAdd") . ":&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, "NotifyAdd", $add) . BR .
            $this->messages->text("user", "notifyEdit") . ":&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, "NotifyEdit", $edit));
        $array = [0 => $this->messages->text("user", "notifyImmediate"), 1 => 1, 7 => 7, 14 => 14, 28 => 28];
        $selected = $this->session->getVar("mywikindx_NotifyThreshold");
        if ($selected) {
            $pString .= \HTML\p(\FORM\selectedBoxValue(
                $this->messages->text("user", "notifyThreshold"),
                "NotifyThreshold",
                $array,
                $selected,
                5
            ));
        } else {
            $pString .= \HTML\p(\FORM\selectFBoxValue(
                $this->messages->text("user", "notifyThreshold"),
                "NotifyThreshold",
                $array,
                5
            ));
        }
        $pString .= \HTML\p(\FORM\textInput(
            $this->messages->text("user", "notifyDigestThreshold"),
            "DigestThreshold",
            $this->session->getVar("mywikindx_NotifyDigestThreshold"),
            5,
            255
        ));
        $pString .= \HTML\tdEnd();
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Get user's bibliographies
     */
    private function getBibs()
    {
        $bibsArray = [];
        // Get this user's bibliographies
        if ($this->session->getVar("mywikindx_Bibliographies")) {
            $bibsRaw = unserialize($this->session->getVar("mywikindx_Bibliographies"));
            foreach ($bibsRaw as $key => $value) {
                $bibsU[$key] = \HTML\dbToFormTidy($value);
            }
        }
        // Get this user's user group bibliographies
        if ($this->session->getVar("mywikindx_Groupbibliographies")) {
            $bibsRaw = unserialize($this->session->getVar("mywikindx_Groupbibliographies"));
            foreach ($bibsRaw as $key => $value) {
                $bibsUG[$key] = \HTML\dbToFormTidy($value);
            }
        }
        $bibsU = $this->bib->getUserBibs();
        $bibsUG = $this->bib->getGroupBibs();
        if (!empty($bibsU)) {
            $bibsArray[-1] = $this->messages->text('user', 'userBibs');
            foreach ($bibsU as $key => $value) {
                $bibsArray[$key] = $value;
            }
        }
        if (!empty($bibsUG)) {
            $bibsArray[-2] = $this->messages->text('user', 'userGroupBibs');
            foreach ($bibsUG as $key => $value) {
                $bibsArray[$key] = $value;
            }
        }

        return $bibsArray;
    }
    /**
     * Does user group already exist?
     * If $groupId, we're editing an existing user group.
     *
     * @param mixed $title
     * @param mixed $groupId
     */
    private function checkUserGroupExists($title, $groupId = FALSE)
    {
        if ($groupId) {
            $this->db->formatConditions(['usergroupsId' => $groupId], TRUE);
        }
        $recordset = $this->db->select('user_groups', ['usergroupsTitle', 'usergroupsId']);
        while ($row = $this->db->fetchRow($recordset)) {
            if ($title == $row['usergroupsTitle']) {
                $this->badInputPopup($this->errors->text("inputError", "groupExists"));
            }
        }
    }
    /**
     * check this user can edit, delete and deleteFrom bibliographies
     *
     * @param mixed $bibId
     * @param mixed $item
     */
    private function checkValidBibliography($bibId, $item = FALSE)
    {
        $this->db->formatConditions(['userbibliographyId' => $bibId]);
        $adminId = $this->db->selectFirstField('user_bibliography', 'userbibliographyUserId');
        if ($this->session->getVar("setup_UserId") != $adminId) {
            if ($item) {
                $this->badInputLoad($this->errors->text("inputError", "invalid"), $item);
            } else {
                $this->badInputPopup($this->errors->text("inputError", "invalid"));
            }
        }
    }
    /**
     * Does bibliography already exist?
     * If $bibId, we're editing an existing user bibliography.
     *
     * @param mixed $title
     * @param mixed $bibId
     */
    private function checkBibliographyExists($title, $bibId = FALSE)
    {
        if ($bibId) {
            $this->db->formatConditions(['userbibliographyId' => $bibId], TRUE);
        }
        $recordset = $this->db->select('user_bibliography', ['userbibliographyTitle', 'userbibliographyId']);
        while ($row = $this->db->fetchRow($recordset)) {
            if ($title == $row['userbibliographyTitle']) {
                if ($bibId) {
                    $this->badInputPopup($this->errors->text("inputError", "bibExists"));
                } else {
                    $this->badInputPopup($this->errors->text("inputError", "bibExists"));
                }
            }
        }
    }
    /**
     * check this user can edit, delete and deleteFrom from user groups
     *
     * @param mixed $item
     */
    private function checkValidUserGroup($item = FALSE)
    {
        $this->db->formatConditions(['usergroupsId' => $this->vars['groupId']]);
        $adminId = $this->db->selectFirstField('user_groups', 'usergroupsAdminId');
        if ($this->session->getVar("setup_UserId") != $adminId) {
            if ($item) {
                $this->badInputLoad($this->errors->text("inputError", "invalid"), $item);
            } else {
                $this->badInputPopup($this->errors->text("inputError", "invalid"));
            }
        }
    }
    /**
     * Error handling
     *
     * @param mixed $error
     * @param mixed $item
     */
    private function badInputLoad($error, $item = FALSE)
    {
        if ($item) {
            $this->badInput->close($error, $this, ['init', $item]);
        } else {
            $this->badInput->close($error, $this, 'init');
        }
    }
    /**
     * Error handling for a popup
     *
     * @param mixed $pString
     */
    private function badInputPopup($pString)
    {
        GLOBALS::clearTplVar('heading');
        $pString .= \FORM\formHeader("usersgroups_WIKINDX_CORE&amp;method=init", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "userBibs");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
}
