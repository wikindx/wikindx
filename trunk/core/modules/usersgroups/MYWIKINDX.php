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
    private $messageString = FALSE;
    private $usersUsername = FALSE;
    private $userNameDisplay = FALSE;
    private $formData = [];

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
            $this->usersUsername = $row['usersUsername'];
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
        if (($messageIn = $this->session->getVar("mywikindx_Message")) && ($item = $this->session->getVar("mywikindx_Item"))) {
            $this->session->delVar("mywikindx_Message");
            $this->session->delVar("mywikindx_Item");
            $this->messageString = $messageIn;
        } elseif (is_array($message)) {
            $this->messageString = $message[0];
            $item = $message[1];
        } elseif (array_key_exists('message', $this->vars) && array_key_exists('selectItem', $this->vars)) {
        	$this->messageString = $this->vars['message'];
        	$item = $this->vars['selectItem'];
        } else {
            $this->messageString = $message;
            $item = FALSE;
        }
        $configGroups = $this->getConfigGroups();
        if (empty($configGroups)) {
            return FALSE;
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "help", "HELPMESSAGES.php"]));
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
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
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
        $error = '';
    	if ($this->session->getVar('setup_UserId') == WIKINDX_SUPERADMIN_ID)
    	{
			if ((!$email = trim($this->vars['email'])) || !trim($this->vars['usersUsername']) || 
				!trim($this->vars['password']) || !trim($this->vars['passwordConfirm'])) {
				$error = $this->errors->text("inputError", "missing");
			} else {
// Reinject the username after a change otherwise the value is taken from the db before the change
        		$this->usersUsername = trim($this->vars['usersUsername']);
			}
			$this->formData['usersUsername'] = trim($this->vars['usersUsername']);
        }
        elseif ((!$email = trim($this->vars['email'])) || !trim($this->vars['password']) || !trim($this->vars['passwordConfirm'])) {
			$error = $this->errors->text("inputError", "missing");
        }
        elseif (trim($this->vars['password']) != trim($this->vars['passwordConfirm'])) {
            $error = $this->errors->text("inputError", "invalid");
        }
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
			$error = $this->errors->text('inputError', 'invalidMail');
		}
        $this->formData['email'] = $email;
        $this->formData['fullname'] = trim($this->vars['fullname']);
		if (array_key_exists('cookie', $this->vars)) {
			$this->formData['cookie'] = TRUE;
		}
        if ($error) {
        	$this->badInputLoad($error, 'user');
        }
        $this->user->writeUser(FALSE); // FALSE = editing user
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
        $emailClass = new EMAIL();
        if (!$emailClass->userEdit()) {
            $this->badInputLoad($this->errors->text("inputError", "mail", GLOBALS::getError()), 'user');
        }
        $message = rawurlencode($this->success->text("userEdit"));
        $selectItem = $this->vars['selectItem'];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=$selectItem");
        die;
    }
    /**
     * Edit resource display details
     */
    public function resourcesConfigEdit()
    {
        // checkInput writes the session
        $this->checkResourcesInput();
        // If this is a logged on user, write preferences to user table
        if ($this->session->getVar("setup_UserId"))
        {
			foreach (["Paging", "PagingMaxLinks", "StringLimit", "PagingTagCloud", "PagingStyle"] as $key)
			{
				$updateArray['users' . $key] = $this->formData[$key];
				GLOBALS::setUserVar($key, $this->formData[$key]);
			}
        	foreach (['UseWikindxKey', 'UseBibtexKey', 'DisplayBibtexLink', 'DisplayCmsLink', 'ListLink'] as $key)
        	{
        		$value = 0;
        		if (array_key_exists($key, $this->formData)) {
        			$value = 1;
        		}
        		GLOBALS::setUserVar($key, $value);
        		$value = is_bool($value) ? var_export($value, true) : $value;
				$updateArray['users' . $key] = $value;
        	}
			$this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
			$this->db->update('users', $updateArray);
        }
        $message = rawurlencode($this->success->text("config"));
        $selectItem = $this->vars['selectItem'];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=$selectItem");
        die;
    }
    /**
     * Check resource display input
     */
    public function checkResourcesInput()
    {
    	$error = '';
        $required = ["Paging", "PagingMaxLinks", "StringLimit", "PagingTagCloud"];
        foreach ($required as $key) {
            if (!is_numeric($this->vars[$key]) || !is_int($this->vars[$key] + 0)) { // cast to number
                $error = $this->errors->text("inputError", "nan", " ($key) ");
            } else {
            	$this->formData[$key] = $this->vars[$key];
            }
            if (!array_key_exists($key, $this->vars) || !$this->vars[$key]) {
                $error = $this->errors->text("inputError", "missing", " ($key) ");
            } else {
            	$this->formData[$key] = $this->vars[$key];
            }
            if (($key == 'PagingMaxLinks') && ($this->vars[$key] < 4)) {
                $this->formData[$key] = 11;
            } elseif ($this->vars[$key] < 0) {
                $this->formData[$key] = -1;
            }
        }
        foreach (['PagingStyle', 'UseWikindxKey', 'UseBibtexKey', 'DisplayBibtexLink', 'DisplayCmsLink', 'ListLink'] as $key) {
        	if (array_key_exists($key, $this->vars)) {
	        	$this->formData[$key] = $this->vars[$key];
	        }
        }
        if ($error) {
        	$this->badInputLoad($error, 'resources');
        }
        $this->session->delVar("sql_LastMulti"); // always reset in case of paging changes
        $this->session->delVar("sql_LastIdeaSearch"); // always reset in case of paging changes
    }
    /**
     * Display appearance config options
     *
     * @return string
     */
    public function appearanceConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \FORM\hidden("method", "appearanceConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        $subTd = \HTML\tableStart();
        $subTd .= \HTML\trStart();
        // Display the global template but change the default selection of the list to the default template when no template is defined or a template not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $templates = FACTORY_TEMPLATE::getInstance()->loadDir();
        $field = array_key_exists('Template', $this->formData) 
        	? $this->formData['Template'] : GLOBALS::getUserVar("Template", WIKINDX_TEMPLATE_DEFAULT);
        $field = array_key_exists($field, $templates) ? $field : WIKINDX_TEMPLATE_DEFAULT;
        $subTd .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "template"),
            "Template",
            $templates,
            $field,
            4
        ));
        
        $menus[0] = $this->messages->text("config", "templateMenu1");
        $menus[1] = $this->messages->text("config", "templateMenu2");
        $menus[2] = $this->messages->text("config", "templateMenu3");
        $field = array_key_exists('TemplateMenu', $this->formData) ? $this->formData['TemplateMenu'] : GLOBALS::getUserVar("TemplateMenu");
        $subTd .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "templateMenu"),
            "TemplateMenu",
            $menus,
            $field,
            3
        ));
        $subTd .= \HTML\trEnd();
        $subTd .= \HTML\tableEnd();
        $pString .= \HTML\td($subTd);
        
        // For the graphical interface, add the "auto" value that allows to say that the language is chosen by the browser.
        $LanguageNeutralChoice = "auto";
        $languages[$LanguageNeutralChoice] = "Auto";
        $languages = array_merge($languages, \LOCALES\getSystemLocales());
        $field = array_key_exists('Language', $this->formData) ? $this->formData['Language'] : FALSE;
        if (!$field) {
			// The chooseLanguage plugin will write to the database for a logged-in user else it will use setup_Language
			$userId = $this->session->getVar('setup_UserId');
			if ($userId)
			{
				$this->db->formatConditions(['usersId' => $userId]);
				$field = $this->db->selectFirstField("users", "usersLanguage");
				$field = array_key_exists($field, $languages) ? $field : $LanguageNeutralChoice;
			}
			else // i.e. read-only so use a session
			{
				$field = $this->session->getVar("setup_Language", $LanguageNeutralChoice);
				$field = array_key_exists($field, $languages) ? $field : $LanguageNeutralChoice;
			}
		}
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "language"),
            "Language",
            $languages,
            $field
        ));
        
        // Display the user style but change the default selection of the list to the default style when no style is defined or a 
        // style not enabled is selected,
        // this avoid a crash when this option is written without value selected.
        $styles = \LOADSTYLE\loadDir();
        $field = array_key_exists('Style', $this->formData) 
        	? $this->formData['Style'] : GLOBALS::getUserVar("Style", WIKINDX_STYLE_DEFAULT);
        $field = array_key_exists($field, $styles) ? $field : WIKINDX_STYLE_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "style"),
            "Style",
            $styles,
            $field,
            4
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Edit appearance display details
     */
    public function appearanceConfigEdit()
    {
        $this->checkAppearanceInput();
        // If this is a logged on user, write preferences to WKX_user_preferences
        if ($this->session->getVar("setup_UserId")) {
            $user = FACTORY_USER::getInstance();
            $user->writePreferences($this->session->getVar("setup_UserId"));
        }
        $message = rawurlencode($this->success->text("config"));
        $selectItem = $this->vars['selectItem'];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=$selectItem");
        die;
    }
    /**
     * Check appearance display input
     */
    public function checkAppearanceInput()
    {
    	$error = '';
        $required = ["Language", "Template", "TemplateMenu", "Style"];
        foreach ($required as $key) {
            if (!array_key_exists($key, $this->vars) || (!$this->vars[$key] && ($key != 'TemplateMenu'))) {
                $error = $this->errors->text("inputError", "missing", " ($key) ");
            }
            $this->formData[$key] = $this->vars[$key];
        }
        if ($error) {
        	$this->badInputLoad($error, 'appearance');
        } else foreach ($required as $key) {
	        GLOBALS::setUserVar($key, $this->vars[$key]);
	    }
    }
    /**
     * Edit forgotten password details
     */
    public function forgetConfigEdit()
    {
    	$error = '';
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "FORGET.php"]));
        $forget = new FORGET();
        list($success, $message) = $forget->forgetWrite();
        if ($success === FALSE) {
            $error = $message;
        }
        if ($error) {
        	$this->badInputLoad($error, 'forget');
        }
        $message = rawurlencode($this->success->text("forgetUpdate"));
        $selectItem = $this->vars['selectItem'];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=$selectItem");
        die;
    }
    /**
     * Set email notification
     */
    public function notificationConfigEdit()
    {
    	$error = '';
        if (!array_key_exists('Notify', $this->vars) || !$this->vars['Notify']) {
            $error = $this->errors->text("inputError", "missing");
        } else {
        	$this->formData['Notify'] = $this->vars['Notify'];
        }
        if (array_key_exists('NotifyAdd', $this->vars)) {
	        $this->formData['NotifyAdd'] = $this->vars['NotifyAdd'];
	    }
        if (array_key_exists('NotifyEdit', $this->vars)) {
	        $this->formData['NotifyEdit'] = $this->vars['NotifyEdit'];
	    }
        if (array_key_exists('NotifyThreshold', $this->vars)) {
	        $this->formData['NotifyThreshold'] = $this->vars['NotifyThreshold'];
	    }
        if (array_key_exists('DigestThreshold', $this->vars)) {
	        $this->formData['DigestThreshold'] = $this->vars['DigestThreshold'];
	    }
        if ($error) {
        	$this->badInputLoad($error, 'notification');
        }
        $updateArray = ['usersNotify' => $this->vars['Notify']];
        if (array_key_exists('NotifyAdd', $this->vars) && array_key_exists('NotifyEdit', $this->vars)) {
            $updateArray['usersNotifyAddEdit'] = 'A';
        } elseif (array_key_exists('NotifyAdd', $this->vars)) {
            $updateArray['usersNotifyAddEdit'] = 'N';
        } elseif (array_key_exists('NotifyEdit', $this->vars)) {
            $updateArray['usersNotifyAddEdit'] = 'E';
        } else {
            $updateArray['usersNotifyAddEdit'] = 'A';
        }
        if (array_key_exists('NotifyThreshold', $this->vars)) {
            $updateArray['usersNotifyThreshold'] = $this->vars['NotifyThreshold'];
        }
        if (array_key_exists('DigestThreshold', $this->vars)) {
            $input = trim($this->vars['DigestThreshold']) + 0;
            if (is_int($input) && ($input > 0)) {
                $updateArray['usersNotifyDigestThreshold'] = trim($this->vars['DigestThreshold']);
            } else {
                $updateArray['usersNotifyDigestThreshold'] = 100;
            }
        }
        $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
        $this->db->update('users', $updateArray);
        $message = rawurlencode($this->success->text("notify"));
        $selectItem = $this->vars['selectItem'];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=$selectItem");
        die;
    }
    /**
     * Display user groups config options
     *
     * @return string
     */
    public function userGroupsConfigDisplay()
    {
        $pString = $this->messageString;
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
        $pString .= \HTML\p(\FORM\formSubmitButton(
                $this->messages->text("submit", "Proceed"),
                FALSE,
                'onclick="return getMywikindxInputGroups();"'
            ));
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
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "createUserGroup");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $sessVar = $this->session->issetVar("mywikindx_groupTitle") ?
            \HTML\dbToFormTidy($this->session->getVar("mywikindx_groupTitle")) : FALSE;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("user", "groupTitle"),
            "title",
            $sessVar,
            50,
            255
        ));
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
    	$error = '';
        if (array_key_exists('description', $this->vars) && trim($this->vars['description'])) {
            $description = json_decode($this->vars['description']);
            $this->session->setVar("mywikindx_groupDescription", $description);
            $fields[] = 'usergroupsDescription';
            $values[] = $description;
        }
        if (!$title = json_decode($this->vars['title'])) {
            $error = $this->errors->text("inputError", "missing");
        }
        if ($error) {
        	$this->badInputLoad($error, 'userGroups');
        }
        $this->session->setVar("mywikindx_groupTitle", $title);
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
        $this->session->delVar("mywikindx_groupDescription");
        $this->session->delVar("mywikindx_groupTitle");
        $message = rawurlencode($this->success->text("groupAdd"));
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=userGroups");
        die;
    }
    /**
     * Display further user groups settings for editing
     *
     * @param false|string $error
     */
    public function editUserGroupInit($error = FALSE)
    {
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
        if (!array_key_exists('groupId', $this->vars) || !$this->vars['groupId']) {
            $this->badInputPopup($this->errors->text("inputError", "missing"));
        }
        $this->checkValidUserGroup();
        $groupUsers = [];
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "editUserGroup");
        $pString .= \FORM\hidden("groupId", $this->vars['groupId']);
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
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("user", "groupTitle"),
            "title",
            $title,
            50,
            255
        ));
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
    	$error = '';
    	if (!array_key_exists('groupId', $this->vars) || !$this->vars['groupId']) {
            $error = $this->errors->text("inputError", "missing");
        }
        if (!$title = json_decode(trim($this->vars['title']))) {
            $error = $this->errors->text("inputError", "missing");
        }
        if ($error) {
        	$this->badInputLoad($error, 'userGroups');
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
        $this->session->delVar("mywikindx_groupDescription");
        $this->session->delVar("mywikindx_groupTitle");
        $message = rawurlencode($this->success->text("groupEdit"));
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=userGroups");
        die;
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
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
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
    	$error = '';
        if (!array_key_exists('groupId', $this->vars) || !$this->vars['groupId']) {
            $error = $this->errors->text("inputError", "invalid");
        }
        if ($error) {
        	$this->badInputLoad($error, 'userGroups');
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
        $message = rawurlencode($this->success->text("groupDelete"));
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=userGroups");
        die;
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
        $pString = $this->messageString;
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
        $pString .= \HTML\p(\FORM\formSubmitButton(
		   $this->messages->text("submit", "Proceed"),
		   FALSE,
		   'onclick="return getMywikindxInputTags();"'
    	));
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
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "createUserTag");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $sessVar = $this->session->issetVar("mywikindx_tagTitle") ?
            \HTML\dbToFormTidy($this->session->getVar("mywikindx_tagTitle")) : FALSE;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("user", "tagTitle"),
            "title",
            $sessVar,
            50,
            255
        ));
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
	    $error = '';
        if (!$title = json_decode(trim($this->vars['title']))) {
            $error = $this->errors->text("inputError", "missing");
        }
        $this->session->setVar("mywikindx_Title", $title);
        $userTagsObject = FACTORY_USERTAGS::getInstance();
        if ($userTagsObject->checkExists($title)) {
            $error = $this->errors->text('inputError', 'userTagExists');
        }
        if ($error) {
        	$this->badInputLoad($error, 'userTags');
        }
        $fields[] = 'usertagsTag';
        $values[] = $title;
        $fields[] = 'usertagsUserId';
        $values[] = $this->session->getVar("setup_UserId");
        $this->db->insert('user_tags', $fields, $values);
        $this->session->delVar("mywikindx_Title");
        $message = rawurlencode($this->success->text("usertagAdd"));
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=userTags");
        die;
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
        if (!array_key_exists('tagId', $this->vars) || !$this->vars['tagId']) {
            $this->badInputPopup($this->errors->text("inputError", "missing"));
        }
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
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
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("user", "tagTitle"),
            "title",
            $title,
            50,
            255
        ));
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
    	$error = '';
        if (!$title = json_decode(trim($this->vars['title']))) {
            $error = $this->errors->text("inputError", "missing");
        }
        if (!$tagId = json_decode($this->vars['tagId'])) {
            $error = $this->errors->text("inputError", "missing");
        }
        $this->session->setVar("mywikindx_Title", $title);
        $userTagsObject = FACTORY_USERTAGS::getInstance();
        $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['usertagsId' => $tagId]);
        $recordSet = $this->db->select('user_tags', 'usertagsId');
        if (!$this->db->numRows($recordSet)) {
            $error = $this->errors->text('inputError', 'invalid');
        }
        if ($error) {
        	$this->badInputLoad($error, 'userTags');
        }
        $this->db->formatConditions(['usertagsId' => $tagId]);
        $this->db->update('user_tags', ['usertagsTag' => $title]);
        $this->session->delVar("mywikindx_Title");
        $message = rawurlencode($this->success->text("usertagEdit"));
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=userTags");
        die;
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
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
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
    	$error = '';
        if (!$tagId = json_decode($this->vars['tagId'])) {
            $error = $this->errors->text("inputError", "missing");
        }
        $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['usertagsId' => $tagId]);
        $recordSet = $this->db->select('user_tags', 'usertagsId');
        if (!$this->db->numRows($recordSet)) {
            $error = $this->errors->text('inputError', 'invalid');
        }
        if ($error) {
        	$this->badInputLoad($error, 'userTags');
        }
        // Delete usertag
        $this->db->formatConditions(['usertagsId' => $this->vars['tagId']]);
        $this->db->delete('user_tags');
        // Remove user_tag ids from resource_user_tags.TagIds
        $this->db->formatConditions(['resourceusertagsTagId' => $this->vars['tagId']]);
        $this->db->delete('resource_user_tags');
        $message = rawurlencode($this->success->text("usertagDelete"));
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=userTags");
        die;
    }
    /**
     * Display user bibs config options
     */
    public function userBibsConfigDisplay()
    {
        $bibs = $this->getBibs();
        $pString = $this->messageString;
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

		$pString .= \HTML\p(\FORM\formSubmitButton(
			$this->messages->text("submit", "Proceed"),
			FALSE,
			'onclick="return getMywikindxInputBibs();"'
		));
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
        if (!$error) {
            $this->session->delVar("mywikindx_bibTitle");
            $this->session->delVar("mywikindx_bibDescription");
        }
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        $pString .= \FORM\formHeader(FALSE);
        if ($groupBib) {
            $pString .= \FORM\hidden("method", "createGroupBib");
        } else {
            $pString .= \FORM\hidden("method", "createUserBib");
        }
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $sessVar = $this->session->issetVar("mywikindx_bibTitle") ?
            \HTML\dbToFormTidy($this->session->getVar("mywikindx_bibTitle")) : FALSE;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("user", "bibTitle"),
            "title",
            $sessVar,
            50,
            255
        ));
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
    	$error = '';
        if (array_key_exists('description', $this->vars)) {
            $description = json_decode(trim($this->vars['description']));
            if ($description) {
                $this->session->setVar("mywikindx_Description", $description);
                $fields[] = 'userbibliographyDescription';
                $values[] = $description;
            }
        }
        if (!$title = json_decode(trim($this->vars['title']))) {
            $error = $this->errors->text("inputError", "missing");
        }
        if ($error) {
        	$this->badInputLoad($error, 'userBibs');
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
        $this->session->delVar("mywikindx_Description");
        $this->session->delVar("mywikindx_Title");
        $this->session->setVar("setup_Bibliographies", TRUE);
        $message = rawurlencode($this->success->text("bibliographyAdd"));
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=userBibs");
        die;
    }
    /**
     * Display further user/group bibs settings for editing
     *
     * @param mixed $error
     */
    public function editBibInit($error = FALSE)
    {
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
        if (!array_key_exists('bibId', $this->vars) || !$this->vars['bibId']) {
            $this->badInputPopup($this->errors->text("inputError", "missing"));
        }
        $this->checkValidBibliography($this->vars['bibId']);
        $groupUsers = [];
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        $pString .= \FORM\formHeader(FALSE);
        $pString .= \FORM\hidden("method", "editBib");
        $pString .= \FORM\hidden("bibId", $this->vars['bibId']);
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
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("user", "bibTitle"),
            "title",
            $title,
            50,
            255
        ));
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
    	$error = '';
        if (!$bibId = json_decode($this->vars['bibId'])) {
            $error = $this->errors->text("inputError", "missing");
        }
        $this->checkValidBibliography($bibId, 'userBibs');
        if (!$title = json_decode(trim($this->vars['title']))) {
            $error = $this->errors->text("inputError", "missing");
        }
        if ($error) {
        	$this->badInputLoad($error, 'userBibs');
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
        if ($groupId = json_decode(trim($this->vars['groupId']))) { // user group bibliography
            $updateArray['userbibliographyUserGroupId'] = $groupId;
        }
        $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['userbibliographyId' => $bibId]);
        $this->db->update('user_bibliography', $updateArray);
        $this->session->delVar("mywikindx_Description");
        $this->session->delVar("mywikindx_Title");
        $message = rawurlencode($this->success->text("bibliographyEdit"));
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=userBibs");
        die;
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
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
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
    	$error = '';
        if (!$bibId = json_decode($this->vars['bibId'])) {
            $error = $this->errors->text("inputError", "missing");
        }
        if ($error) {
        	$this->badInputLoad($error, 'userBibs');
        }
        $this->checkValidBibliography($bibId, 'userBibs');
        $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['userbibliographyId' => $bibId]);
        $this->db->delete('user_bibliography');
        // Get any bibliographyIds and delete those bibliographies
        $this->db->formatConditions(['userbibliographyresourceBibliographyId' => $bibId]);
        $this->db->delete('user_bibliography_resource');
        if ($bibId == $this->session->getVar("mywikindx_Bibliography_use")) {
            $this->session->delVar("mywikindx_Bibliography_use");
        }
        $bibsU = $this->bib->getUserBibs();
        $bibsUG = $this->bib->getGroupBibs();
        if (empty($bibsU) && empty($bibsUG)) {
            $this->session->delVar("setup_Bibliographies");
        }
        $message = rawurlencode($this->success->text("bibliographyDelete"));
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&message=$message&selectItem=userBibs");
        die;
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
        }
        // Only for logged on users
        if ($this->session->getVar("setup_UserId")) {
            $groups['userTags'] = $this->messages->text("user", "userTags");
            $groups['userBibs'] = $this->messages->text('user', 'bib');
            // Add user group administration only if there is more than one user.
            $resourceId = $this->db->select('users', 'usersId');
            if ($this->db->numRows($resourceId) > 1) {
                $groups['userGroups'] = $this->messages->text("user", "groups");
            }
            $user = [];
            if ($this->session->getVar("setup_UserId") != WIKINDX_RESTRICT_USERID) {
	            $user = ['user' => $this->messages->text('user', 'user')];
            }
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
            if ($this->session->getVar("setup_UserId") == WIKINDX_SUPERADMIN_ID) {
    	        list($formText, $jsString) = $password->createElements($this->usersUsername, TRUE, $this->formData);
    	    } else {
    	        list($formText, $jsString) = $password->createElements(FALSE);
    	    }
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
                $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));

                break;
            case 'resources': // resources display configuration
                $pString .= $this->resourcesConfigDisplay();
                $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));

                break;
            case 'appearance': // appearance configuration
                $pString .= $this->appearanceConfigDisplay();
                $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));

                break;
            case 'forget': // forgotten password configuration
                $pString .= $this->forgetConfigDisplay();
                $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));

                break;
            case 'notification': // email notification configuration
                $pString .= $this->notificationConfigDisplay();
                $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));

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
        $pString = $this->messageString;
        $pString .= \FORM\hidden("method", "userConfigEdit");
        $pString .= \FORM\hidden("uname", $this->usersUsername);
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
        $field = array_key_exists('email', $this->formData) ? $this->formData['email'] : $row["Email"];
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("user", "email"),
            "email",
            $field,
            30
        ));
        $field = array_key_exists('fullname', $this->formData) ? $this->formData['fullname'] : $row["Fullname"];
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "fullname"),
            "fullname",
            $field,
            30
        ));
        $field = FALSE;
        if (!empty($this->formData)) {
        	if (array_key_exists('cookie', $this->formData)) {
	        	 $field = TRUE;
	        } else {
	        	$field = FALSE;
	        }
        }
        elseif ($row["Cookie"] == 'Y') {
        	$field = TRUE;
        }
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("user", "cookie"), "cookie", $field));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display resources display options
     */
    private function resourcesConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \FORM\hidden("method", "resourcesConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        $field = array_key_exists('Paging', $this->formData) ? $this->formData['Paging'] : GLOBALS::getUserVar('Paging');
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "paging"),
            "Paging",
            $field,
            5
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingMaxLinks"));
        $field = array_key_exists('PagingMaxLinks', $this->formData) ? $this->formData['PagingMaxLinks'] : GLOBALS::getUserVar('PagingMaxLinks');
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "maxPaging"),
            "PagingMaxLinks",
            $field,
            5
        ) . BR . \HTML\span($hint, 'hint'));
        if (!GLOBALS::getUserVar("PagingTagCloud")) {
            GLOBALS::setUserVar("PagingTagCloud", WIKINDX_PAGING_TAG_CLOUD_DEFAULT);
        }
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        $field = array_key_exists('PagingTagCloud', $this->formData) ? $this->formData['PagingTagCloud'] : GLOBALS::getUserVar('PagingTagCloud');
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "pagingTagCloud"),
            "PagingTagCloud",
            $field,
            5
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        $field = array_key_exists('StringLimit', $this->formData) ? $this->formData['StringLimit'] : GLOBALS::getUserVar('StringLimit');
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "stringLimit"),
            "StringLimit",
            $field,
            5
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $field = array_key_exists('PagingStyle', $this->formData) ? $this->formData['PagingStyle'] : GLOBALS::getUserVar('PagingStyle');
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "pagingStyle"),
            "PagingStyle",
            ["N" => "Numerical", "A" => "Alphabetical"],
            $field,
            2
        ));
        $field = FALSE; 
        if (array_key_exists('UseWikindxKey', $this->formData)) {
        	$field = 'CHECKED';
        } elseif (GLOBALS::getUserVar("UseWikindxKey")) {
         	$field = 'CHECKED';
		}
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "useWikindxKey"),
            "UseWikindxKey",
            $field
        ));
        $field = FALSE; 
        if (array_key_exists('UseBibtexKey', $this->formData)) {
        	$field = 'CHECKED';
        } elseif (GLOBALS::getUserVar("UseBibtexKey")) {
         	$field = 'CHECKED';
		}
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "useBibtexKey"),
            "UseBibtexKey",
            $field
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
        $field = FALSE; 
        if (array_key_exists('DisplayBibtexLink', $this->formData)) {
        	$field = 'CHECKED';
        } elseif (GLOBALS::getUserVar("DisplayBibtexLink")) {
         	$field = 'CHECKED';
		}
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "displayBibtexLink"),
            "DisplayBibtexLink",
            $field
        ));
        $field = FALSE; 
        if (array_key_exists('DisplayCmsLink', $this->formData)) {
        	$field = 'CHECKED';
        } elseif (GLOBALS::getUserVar("DisplayCmsLink")) {
         	$field = 'CHECKED';
		}
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "displayCmsLink"),
            "DisplayCmsLink",
            $field
        ));
        $field = FALSE; 
        if (array_key_exists('ListLink', $this->formData)) {
        	$field = 'CHECKED';
        } elseif (GLOBALS::getUserVar("ListLink")) {
         	$field = 'CHECKED';
		}
        $pString .= \HTML\td(\FORM\checkbox(
        	$this->messages->text("config", "ListLink"),
        	"ListLink", 
        	$field
        ));
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
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "FORGET.php"]));
        $forget = new FORGET();
        $pString = $this->messageString;
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
        $pString = $this->messageString;
        $pString .= \FORM\hidden("method", "notificationConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        if (empty($this->formData)) {
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $recordset = $this->db->select('users', ['usersNotify', 'usersNotifyAddEdit',
                'usersNotifyThreshold', 'usersNotifyTimestamp', 'usersNotifyDigestThreshold', ]);
            $row = $this->db->fetchRow($recordset);
        }
        $pString .= \HTML\tdStart();
        $checked = $add = $edit = $nThreshold = $dThreshold = FALSE;
        if (array_key_exists('Notify', $this->formData)) {
        	if ($this->formData['Notify'] == 'N') {
				$checked = TRUE;
			}
        } elseif ($row["usersNotify"] == 'N') {
        	 $checked = TRUE;
        }
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "N", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyNone"));
        $checked = FALSE;
        if (!$checked) {
			if (array_key_exists('Notify', $this->formData)) {
				if ($this->formData['Notify'] == 'A') {
					$checked = TRUE;
				}
			} elseif ($row["usersNotify"] == 'A') {
				 $checked = TRUE;
			}
        }
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "A", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyAll"));
        $checked = FALSE;
        if (!$checked) {
			if (array_key_exists('Notify', $this->formData)) {
				if ($this->formData['Notify'] == 'M') {
					$checked = TRUE;
				}
			} elseif ($row["usersNotify"] == 'M') {
				 $checked = TRUE;
			}
        }
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "M", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyMyBib"));
        $checked = FALSE;
        if (!$checked) {
			if (array_key_exists('Notify', $this->formData)) {
				if ($this->formData['Notify'] == 'C') {
					$checked = TRUE;
				}
			} elseif ($row["usersNotify"] == 'C') {
				 $checked = TRUE;
			}
        }
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "C", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyMyCreator"));
        if (empty($this->formData)) {
        	if ($row["usersNotifyAddEdit"] == 'A') {
				 $add = 'CHECKED';
				 $edit = 'CHECKED';
        	}
        	elseif ($row["usersNotifyAddEdit"] == 'N') {
				 $add = 'CHECKED';
        	}
        	elseif ($row["usersNotifyAddEdit"] == 'E') {
				 $edit = 'CHECKED';
        	}
        }
        else {
        	if (array_key_exists('NotifyAdd', $this->formData)) {
        		$add = 'CHECKED';
        	} 
			if (array_key_exists('NotifyEdit', $this->formData)) {
				$edit = 'CHECKED';
			}
		}
        $pString .= \HTML\p($this->messages->text("user", "notifyAdd") . ":&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, "NotifyAdd", $add) . BR .
            $this->messages->text("user", "notifyEdit") . ":&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, "NotifyEdit", $edit));
        $array = [0 => $this->messages->text("user", "notifyImmediate"), 1 => 1, 7 => 7, 14 => 14, 28 => 28];
        if (array_key_exists('NotifyThreshold', $this->formData)) {
        	$nThreshold = $this->formData['NotifyThreshold'];
        } elseif ($row["usersNotifyThreshold"]) {
        	 $nThreshold = $this->session->getVar("mywikindx_NotifyThreshold");
        }
        if ($nThreshold) {
            $pString .= \HTML\p(\FORM\selectedBoxValue(
                $this->messages->text("user", "notifyThreshold"),
                "NotifyThreshold",
                $array,
                $nThreshold,
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
        $dThreshold = array_key_exists('DigestThreshold', $this->formData) ? 
        	$this->formData['DigestThreshold'] : $row["usersNotifyDigestThreshold"];
        $pString .= \HTML\p(\FORM\textInput(
            $this->messages->text("user", "notifyDigestThreshold"),
            "DigestThreshold",
            $dThreshold,
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
