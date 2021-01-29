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
    private $usersEmail = FALSE;
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
        if ($this->session->getVar("setup_UserId"))
        {
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $recordset = $this->db->select('users', ['usersUsername', 'usersEmail']);
            if (!$this->db->numRows($recordset))
            {
                debug_print_backtrace();
                die($this->errors->text("dbError", "read"));
            }
            $row = $this->db->fetchRow($recordset);
            $this->usersUsername = $row['usersUsername'];
            $this->usersEmail = $row['usersEmail'];
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
    	$item = FALSE;
        // Cleanup if necessary
        if (array_key_exists('uuid', $this->vars))
        {
            \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
        }
        if (is_array($message)) {
            $this->messageString = $message[0];
            $item = $message[1];
        } elseif (array_key_exists('success', $this->vars) && $this->vars['success']) {
        	if (strpos($this->vars['success'], '_') !== FALSE) {
        		$split = explode('_', $this->vars['success']);
            	$this->messageString = $this->success->text($split[0], $split[1]);
        	} else {
	            $this->messageString = $this->success->text($this->vars['success']);
	        }
            if (array_key_exists('selectItem', $this->vars)) {
            	$item = $this->vars['selectItem'];
            }
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $this->messageString = $this->errors->text($split[0], $split[1]);
            if (array_key_exists('selectItem', $this->vars)) {
            	$item = $this->vars['selectItem'];
            }
        }
        else
        {
            $this->messageString = $message;
            if (array_key_exists('selectItem', $this->vars)) {
            	$item = $this->vars['selectItem'];
            }
        }
        $configGroups = $this->getConfigGroups();
        if (empty($configGroups))
        {
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
        if ($item)
        {
            $pString .= \HTML\td(\FORM\selectedBoxValue($this->messages->text(
                'config',
                'options'
            ), 'configMenu', $configGroups, $item, count($configGroups), FALSE, $js));
        }
        else
        {
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
            if ((!$email = \UTF8\mb_trim($this->vars['email'])) || !\UTF8\mb_trim($this->vars['usersUsername']) ||
                !\UTF8\mb_trim($this->vars['password']) || !\UTF8\mb_trim($this->vars['passwordConfirm']))
            {
                $error = $this->errors->text("inputError", "missing");
            }
            else
            {
                // Reinject the username after a change otherwise the value is taken from the db before the change
                $this->usersUsername = \UTF8\mb_trim($this->vars['usersUsername']);
            }
            $this->formData['usersUsername'] = \UTF8\mb_trim($this->vars['usersUsername']);
        }
        elseif ((!$email = \UTF8\mb_trim($this->vars['email'])) || !\UTF8\mb_trim($this->vars['password']) ||
            !\UTF8\mb_trim($this->vars['passwordConfirm']))
        {
            $error = $this->errors->text("inputError", "missing");
        }
        elseif (\UTF8\mb_trim($this->vars['password']) != \UTF8\mb_trim($this->vars['passwordConfirm']))
        {
            $error = $this->errors->text("inputError", "invalid");
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE)
        {
            $error = $this->errors->text('inputError', 'invalidMail');
        }
        $this->formData['email'] = $email;
        $this->formData['fullname'] = \UTF8\mb_trim($this->vars['fullname']);
        if (array_key_exists('cookie', $this->vars))
        {
            $this->formData['cookie'] = TRUE;
        }
        if ($error)
        {
            $this->badInputLoad($error, 'user');
        }
        $this->user->writeUser(FALSE); // FALSE = editing user
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
        $emailClass = new EMAIL();
        if (!$emailClass->userEdit())
        {
            $this->badInputLoad($this->errors->text("inputError", "mail", GLOBALS::getError()), 'user');
        }
        $selectItem = $this->vars['selectItem'];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&success=userEdit&selectItem=$selectItem");
        die;
    }
    /**
     * Edit resource display details
     */
    public function resourcesConfigEdit()
    {
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
                if (array_key_exists($key, $this->formData))
                {
                    $value = 1;
                }
                GLOBALS::setUserVar($key, $value);
                $value = is_bool($value) ? var_export($value, TRUE) : $value;
                $updateArray['users' . $key] = $value;
            }
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $this->db->update('users', $updateArray);
        }
        $selectItem = $this->vars['selectItem'];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&success=config&selectItem=$selectItem");
        die;
    }
    /**
     * Check resource display input
     */
    public function checkResourcesInput()
    {
        $error = '';
        $required = ["Paging", "PagingMaxLinks", "StringLimit", "PagingTagCloud"];
        foreach ($required as $key)
        {
            if (!is_numeric($this->vars[$key]) || !is_int($this->vars[$key] + 0))
            { // cast to number
                $error = $this->errors->text("inputError", "nan", " ($key) ");
            }
            else
            {
                $this->formData[$key] = $this->vars[$key];
            }
            if (!array_key_exists($key, $this->vars) || !$this->vars[$key])
            {
                $error = $this->errors->text("inputError", "missing", " ($key) ");
            }
            else
            {
                $this->formData[$key] = $this->vars[$key];
            }
            if (($key == 'PagingMaxLinks') && ($this->vars[$key] < 4))
            {
                $this->formData[$key] = 11;
            }
            elseif ($this->vars[$key] < 0)
            {
                $this->formData[$key] = -1;
            }
        }
        foreach (['PagingStyle', 'UseWikindxKey', 'UseBibtexKey', 'DisplayBibtexLink', 'DisplayCmsLink', 'ListLink'] as $key)
        {
            if (array_key_exists($key, $this->vars))
            {
                $this->formData[$key] = $this->vars[$key];
            }
        }
        if ($error)
        {
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
        if (!$field)
        {
            // The chooseLanguage plugin will write to the database for a logged-in user else it will use setup_Language
            $userId = $this->session->getVar('setup_UserId');
            if ($userId)
            {
                $this->db->formatConditions(['usersId' => $userId]);
                $field = $this->db->selectFirstField("users", "usersLanguage");
                $field = array_key_exists($field, $languages) ? $field : $LanguageNeutralChoice;
            }
            else
            { // i.e. read-only so use a session
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
        if ($this->session->getVar("setup_UserId"))
        {
            $user = FACTORY_USER::getInstance();
            $user->writePreferences($this->session->getVar("setup_UserId"));
        }
        $selectItem = $this->vars['selectItem'];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&success=config&selectItem=$selectItem");
        die;
    }
    /**
     * Check appearance display input
     */
    public function checkAppearanceInput()
    {
        $error = '';
        $required = ["Language", "Template", "TemplateMenu", "Style"];
        foreach ($required as $key)
        {
            if (!array_key_exists($key, $this->vars) || (!$this->vars[$key] && ($key != 'TemplateMenu')))
            {
                $error = $this->errors->text("inputError", "missing", " ($key) ");
            }
            $this->formData[$key] = $this->vars[$key];
        }
        if ($error)
        {
            $this->badInputLoad($error, 'appearance');
        }
        else
        {
            foreach ($required as $key)
            {
                GLOBALS::setUserVar($key, $this->vars[$key]);
            }
        }
    }
    /**
     * Set email notification
     */
    public function notificationConfigEdit()
    {
        $error = '';
        if (!array_key_exists('Notify', $this->vars) || !$this->vars['Notify'])
        {
            $error = $this->errors->text("inputError", "missing");
        }
        else
        {
            $this->formData['Notify'] = $this->vars['Notify'];
        }
        if (array_key_exists('NotifyAdd', $this->vars))
        {
            $this->formData['NotifyAdd'] = $this->vars['NotifyAdd'];
        }
        if (array_key_exists('NotifyEdit', $this->vars))
        {
            $this->formData['NotifyEdit'] = $this->vars['NotifyEdit'];
        }
        if (array_key_exists('NotifyThreshold', $this->vars))
        {
            $this->formData['NotifyThreshold'] = $this->vars['NotifyThreshold'];
        }
        if (array_key_exists('DigestThreshold', $this->vars))
        {
            $this->formData['DigestThreshold'] = $this->vars['DigestThreshold'];
        }
        if ($error)
        {
            $this->badInputLoad($error, 'notification');
        }
        $updateArray = ['usersNotify' => $this->vars['Notify']];
        if (array_key_exists('NotifyAdd', $this->vars) && array_key_exists('NotifyEdit', $this->vars))
        {
            $updateArray['usersNotifyAddEdit'] = 'A';
        }
        elseif (array_key_exists('NotifyAdd', $this->vars))
        {
            $updateArray['usersNotifyAddEdit'] = 'N';
        }
        elseif (array_key_exists('NotifyEdit', $this->vars))
        {
            $updateArray['usersNotifyAddEdit'] = 'E';
        }
        else
        {
            $updateArray['usersNotifyAddEdit'] = 'A';
        }
        if (array_key_exists('NotifyThreshold', $this->vars))
        {
            $updateArray['usersNotifyThreshold'] = $this->vars['NotifyThreshold'];
        }
        if (array_key_exists('DigestThreshold', $this->vars))
        {
            $input = \UTF8\mb_trim($this->vars['DigestThreshold']) + 0;
            if (is_int($input) && ($input > 0))
            {
                $updateArray['usersNotifyDigestThreshold'] = \UTF8\mb_trim($this->vars['DigestThreshold']);
            }
            else
            {
                $updateArray['usersNotifyDigestThreshold'] = 100;
            }
        }
        $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
        $this->db->update('users', $updateArray);
        $selectItem = $this->vars['selectItem'];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&success=notify&selectItem=$selectItem");
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
        if (!$groups = $this->user->listUserGroups())
        {
            $pString .= \HTML\td($this->messages->text("user", "noGroups"));
            $radios = \HTML\p(\FORM\radioButton(FALSE, "method", "createUserGroupInit", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "createGroup"), FALSE, "left");
        }
        else
        {
            $pString .= \HTML\td(\FORM\selectFBoxValue($this->messages->text("user", "groups"), "groupId", $groups, 5));
            $radios = \HTML\p(\FORM\radioButton(FALSE, "method", "createUserGroupInit", TRUE) .
                "&nbsp;&nbsp;" . $this->messages->text("user", "createGroup"), FALSE, "left");
            $radios .= \HTML\p(\FORM\radioButton(FALSE, "method", "editUserGroupInit", FALSE) .
                "&nbsp;&nbsp;" . $this->messages->text("user", "editGroup"), FALSE, "left");
            $radios .= \HTML\p(\FORM\radioButton(FALSE, "method", "deleteUserGroupInit", FALSE) .
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
     * @param mixed $message
     */
    public function createUserGroupInit($message = FALSE)
    {
        $uuid = $title = $description = FALSE;
        $groupUsers = [];
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "createGroup")
        ));
        if (array_key_exists('uuid', $this->vars))
        {
            $uuid = $this->vars['uuid'];
        }
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userGroups"; </script>');
        $pString .= \FORM\formHeader("usersgroups_MYWIKINDX_CORE", "onsubmit=\"selectAll();return true;\"");
        $pString .= \FORM\hidden("method", "createUserGroup");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        if ($uuid)
        { // Back here after editing group so fetch details from temp_storage
            $data = \TEMPSTORAGE\fetch($this->db, $uuid);
            $pString .= \FORM\hidden("uuid", $uuid);
            $title = $data['title'];
            $description = $data['description'];
            if (!empty($data['selectedUsers']))
            {
                $this->db->formatConditionsOneField($data['selectedUsers'], 'usersId');
                $recordset = $this->db->select('users', ['usersId', 'usersUsername', 'usersFullname', 'usersAdmin']);
                while ($row = $this->db->fetchRow($recordset))
                {
                    if ($row['usersId'] == $this->session->getVar("setup_UserId"))
                    {
                        continue;
                    }
                    $groupUsers[$row['usersId']] = \HTML\dbToFormTidy($row['usersUsername']);
                    if ($row['usersFullname'])
                    {
                        $groupUsers[$row['usersId']] .= " (" . \HTML\dbToFormTidy($row['usersFullname']) . ")";
                    }
                    if ($row['usersAdmin'])
                    {
                        $groupUsers[$row['usersId']] .= " ADMIN";
                    }
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
        $potentialUsers = [];
        foreach ($users as $key => $value)
        {
            if ($key == $this->session->getVar("setup_UserId"))
            {
                continue;
            }
            if (array_key_exists($key, $groupUsers))
            {
                continue;
            }
            $potentialUsers[$key] = $value;
        }
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "multiples"));
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple($this->messages->text(
            "user",
            "selectedUsers"
        ), "selectedUsers", $groupUsers, 10) . BR . \HTML\span($hint, 'hint'));
        list($toRightImage, $toLeftImage) = $this->transferArrows();
        $pString .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'padding3px left width5percent');
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("user", "potentialUsers"),
            "potentialUsers",
            $potentialUsers,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
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
        if (!$title = \UTF8\mb_trim($this->vars['title']))
        {
            $error = "inputError_missing";
        }
        if (empty($this->vars['selectedUsers']))
        {
            $error = "inputError_missing";
        }
        if (!$this->checkUserGroupExists($title, FALSE))
        {
            $error = "inputError_groupExists";
        }
        if (array_key_exists('description', $this->vars) && \UTF8\mb_trim($this->vars['description']))
        {
            $description = \UTF8\mb_trim($this->vars['description']);
        }
        if ($error)
        {
            if (array_key_exists('uuid', $this->vars))
            {
                $uuid = $this->vars['uuid'];
                \TEMPSTORAGE\delete($this->db, $uuid);
            }
            else
            {
                $uuid = \TEMPSTORAGE\getUuid($this->db);
            }
            \TEMPSTORAGE\store($this->db, $uuid, ['title' => $title, 'description' => $description,
                'selectedUsers' => $this->vars['selectedUsers'], ]);
            header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=createUserGroupInit&error=$error&uuid=$uuid");
            die;
        }
        $userId = $this->session->getVar("setup_UserId");
        $fields[] = 'usergroupsTitle';
        $values[] = $title;
        $fields[] = 'usergroupsDescription';
        $values[] = $description;
        $fields[] = 'usergroupsAdminId';
        $values[] = $userId;
        $this->db->insert('user_groups', $fields, $values);
        $groupId = $this->db->lastAutoId();
        $userIds[] = $userId;
        foreach ($this->vars['selectedUsers'] as $userId)
        {
            if (!$userId)
            { // IGNORE
                continue;
            }
            $userIds[] = $userId;
        }
        // Insert new users
        foreach ($userIds as $id)
        {
            $this->db->insert('user_groups_users', ['usergroupsusersUserId', 'usergroupsusersGroupId'], [$id, $groupId]);
        }
        $this->formData = [];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=createUserGroupInit&success=groupAdd");
        die;
    }
    /**
     * Display further user groups settings for editing
     *
     * @param false|string $message
     */
    public function editUserGroupInit($message = FALSE)
    {
        $uuid = FALSE;
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "editGroup")
        ));
        if (array_key_exists('uuid', $this->vars))
        {
            $uuid = $this->vars['uuid'];
        }
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userGroups"; </script>');
        $pString .= \FORM\formHeader("usersgroups_MYWIKINDX_CORE", "onsubmit=\"selectAll();return true;\"");
        $pString .= \FORM\hidden("method", "editUserGroup");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        if (!$uuid)
        {
            $groupId = $this->vars['groupId'];
            $this->db->formatConditions(['usergroupsId' => $groupId]);
            $recordset = $this->db->select('user_groups', ['usergroupsTitle', 'usergroupsDescription']);
            $row = $this->db->fetchRow($recordset);
            $title = \HTML\dbToFormTidy($row['usergroupsTitle']);
            $description = \HTML\dbToFormTidy($row['usergroupsDescription']);
            $groupUsers = $this->getGroupUsers($groupId);
            $pString .= \FORM\hidden("groupId", $groupId);
        }
        else
        { // Back here after editing group so fetch details from temp_storage
            $data = \TEMPSTORAGE\fetch($this->db, $uuid);
            $groupId = $data['groupId'];
            $pString .= \FORM\hidden("groupId", $groupId);
            $pString .= \FORM\hidden("uuid", $uuid);
            $title = $data['title'];
            $description = $data['description'];
            $groupUsers = [];
            if (!empty($data['selectedUsers']))
            {
                $this->db->formatConditionsOneField($data['selectedUsers'], 'usersId');
                $recordset = $this->db->select('users', ['usersId', 'usersUsername', 'usersFullname', 'usersAdmin']);
                while ($row = $this->db->fetchRow($recordset))
                {
                    if ($row['usersId'] == $this->session->getVar("setup_UserId"))
                    {
                        continue;
                    }
                    $groupUsers[$row['usersId']] = \HTML\dbToFormTidy($row['usersUsername']);
                    if ($row['usersFullname'])
                    {
                        $groupUsers[$row['usersId']] .= " (" . \HTML\dbToFormTidy($row['usersFullname']) . ")";
                    }
                    if ($row['usersAdmin'])
                    {
                        $groupUsers[$row['usersId']] .= " ADMIN";
                    }
                }
            }
        }
        if (!$this->checkValidUserGroup($groupId))
        {
            $this->catastrophic($this->errors->text("inputError", "invalid"), 'userGroups');
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
        $potentialUsers = [];
        foreach ($users as $key => $value)
        {
            if ($key == $this->session->getVar("setup_UserId"))
            {
                continue;
            }
            if (array_key_exists($key, $groupUsers))
            {
                continue;
            }
            $potentialUsers[$key] = $value;
        }
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "multiples"));
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple($this->messages->text(
            "user",
            "selectedUsers"
        ), "selectedUsers", $groupUsers, 10) . BR . \HTML\span($hint, 'hint'));
        list($toRightImage, $toLeftImage) = $this->transferArrows();
        $pString .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'padding3px left width5percent');
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("user", "potentialUsers"),
            "potentialUsers",
            $potentialUsers,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
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
        $groupId = FALSE;
        if (!array_key_exists('groupId', $this->vars) || (!$groupId = $this->vars['groupId']))
        {
            $error = "inputError_missing";
        }
        if (!$title = \UTF8\mb_trim($this->vars['title']))
        {
            $error = "inputError_missing";
        }
        if (empty($this->vars['selectedUsers']))
        {
            $error = "inputError_missing";
        }
        $description = \UTF8\mb_trim($this->vars['description']);
        if (!$this->checkValidUserGroup($groupId))
        {
            $this->catastrophic($this->errors->text("inputError", "invalid"), 'userGroups');
        }
        if (!$this->checkUserGroupExists($title, $this->vars['groupId']))
        {
            $this->catastrophic($this->errors->text("inputError", "groupExists"), 'userGroups');
        }
        if ($error)
        {
            if (array_key_exists('uuid', $this->vars))
            {
                $uuid = $this->vars['uuid'];
                \TEMPSTORAGE\delete($this->db, $uuid);
            }
            else
            {
                $uuid = \TEMPSTORAGE\getUuid($this->db);
            }
            \TEMPSTORAGE\store($this->db, $uuid, ['title' => $title, 'description' => $description, 'groupId' => $groupId,
                'selectedUsers' => $this->vars['selectedUsers'], ]);
            header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=editUserGroupInit&error=$error&uuid=$uuid");
            die;
        }
        if (!$description)
        {
            $this->db->formatConditions(['usergroupsId' => $this->vars['groupId']]);
            $this->db->updateNull('user_groups', 'usergroupsDescription');
        }
        else
        {
            $updateArray['usergroupsDescription'] = $description;
        }
        $updateArray['usergroupsTitle'] = $title;
        $this->db->formatConditions(['usergroupsId' => $this->vars['groupId']]);
        $this->db->update('user_groups', $updateArray);
        // delete any users before inserting them
        $this->db->formatConditions(['usergroupsusersGroupId' => $this->vars['groupId']]);
        $this->db->delete('user_groups_users');
        // Insert new users
        foreach ($this->vars['selectedUsers'] as $id)
        {
            $this->db->insert(
                'user_groups_users',
                ['usergroupsusersUserId', 'usergroupsusersGroupId'],
                [$id, $this->vars['groupId']]
            );
        }
        if (array_key_exists('uuid', $this->vars))
        {
            \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
        }
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=editUserGroupInit&success=groupEdit&groupId=$groupId");
        die;
    }
    /**
     * Display further user groups settings for deleting
     */
    public function deleteUserGroupInit()
    {
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "deleteGroup")
        ));
        $pString = '';
        $this->db->formatConditions(['usergroupsId' => $this->vars['groupId']]);
        $title = \HTML\strong($this->db->selectFirstField('user_groups', 'usergroupsTitle'));
        $pString .= \HTML\p($this->messages->text("user", "deleteConfirmGroup") . ":&nbsp;&nbsp;" .
            \HTML\nlToHtml($title));
        $pString .= \HTML\p($this->messages->text("user", "deleteGroup2"));
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userGroups"; </script>');
        $pString .= \FORM\formHeader("usersgroups_MYWIKINDX_CORE");
        $pString .= \FORM\hidden("method", "deleteUserGroup");
        $pString .= \FORM\hidden("groupId", $this->vars['groupId']);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Delete")));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
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
        // Get any bibliographyIds and delete those bibliographies
        $this->db->formatConditions(['userbibliographyUserGroupId' => $this->vars['groupId']]);
        $recordset = $this->db->select('user_bibliography', 'userbibliographyId');
        while ($row = $this->db->fetchRow($recordset))
        {
            $bibIds[] = $row['userbibliographyId'];
        }
        if (isset($bibIds))
        {
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
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=deleteUserGroupSuccess&success=groupDelete");
        die;
    }
    /**
     * Display success page when deleting a user group
     *
     * @param mixed $message
     */
    public function deleteUserGroupSuccess($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "deleteGroup")
        ));
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userGroups"; </script>');
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
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
        natcasesort($userTags);
        $pString = $this->messageString;
        $pString .= \FORM\hidden("method", "userTagsConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        if (empty($userTags))
        {
            $pString .= \HTML\td($this->messages->text("user", "noUserTags"));
            $radios = \HTML\p(\FORM\radioButton(FALSE, "method", "createUserTagInit", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "createUserTag"), FALSE, "left");
        }
        else
        {
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
     * @param mixed $message
     */
    public function createUserTagInit($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "createUserTag")
        ));
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
        	if (strpos($this->vars['success'], '_') !== FALSE) {
        		$split = explode('_', $this->vars['success']);
        		$message = $this->success->text($split[0], $split[1]);
        	} else {
	            $message = $this->success->text($this->vars['success']);
	        }
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userTags"; </script>');
        $pString .= \FORM\formHeader("usersgroups_MYWIKINDX_CORE");
        $pString .= \FORM\hidden("method", "createUserTag");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("user", "tagTitle"),
            "title",
            FALSE,
            50,
            255
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"closeAndRedirect();return true;\"");
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
        if (!$title = \UTF8\mb_trim($this->vars['title']))
        {
            $error = $this->errors->text("inputError", "missing");
        }
        $userTagsObject = FACTORY_USERTAGS::getInstance();
        if ($userTagsObject->checkExists($title))
        {
            $error = $this->errors->text('inputError', 'userTagExists');
        }
        if ($error)
        {
            $this->badInputPopup($error, 'createUserTagInit');
        }
        $fields[] = 'usertagsTag';
        $values[] = $title;
        $fields[] = 'usertagsUserId';
        $values[] = $this->session->getVar("setup_UserId");
        $this->db->insert('user_tags', $fields, $values);
        $message = "usertagAdd_$title";
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=createUserTagInit&success=$message");
        die;
    }
    /**
     * Display further user tags settings for editing
     *
     * @param mixed $message
     */
    public function editUserTagInit($message = FALSE)
    {
        $uuid = FALSE;
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "editUserTag")
        ));
        if (array_key_exists('uuid', $this->vars))
        {
            $uuid = $this->vars['uuid'];
        }
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userTags"; </script>');
        $pString .= \FORM\formHeader("usersgroups_MYWIKINDX_CORE");
        $pString .= \FORM\hidden("method", "editUserTag");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        if (!$uuid)
        {
            $this->db->formatConditions(['usertagsId' => $this->vars['tagId']]);
            $recordset = $this->db->select('user_tags', 'usertagsTag');
            $row = $this->db->fetchRow($recordset);
            $title = \HTML\dbToFormTidy($row['usertagsTag']);
            $pString .= \FORM\hidden("tagId", $this->vars['tagId']);
        }
        else
        { // Back here after editing tag so fetch details from temp_storage
            $data = \TEMPSTORAGE\fetch($this->db, $uuid);
            $pString .= \FORM\hidden("tagId", $data['tagId']);
            $pString .= \FORM\hidden("uuid", $uuid);
            $title = $data['title'];
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
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
        $pString .= \FORM\formEnd();
        if ($uuid)
        {
            GLOBALS::addTplVar('scripts', '<script>var uuid = ' . $uuid . '; </script>');
        }
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
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
        if (!$title = \UTF8\mb_trim($this->vars['title']))
        {
            $error = "inputError_missing";
        }
        if (!$tagId = $this->vars['tagId'])
        {
            $error = "inputError_missing";
        }
        $userTagsObject = FACTORY_USERTAGS::getInstance();
        $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['usertagsId' => $tagId]);
        $recordSet = $this->db->select('user_tags', 'usertagsId');
        if (!$this->db->numRows($recordSet))
        {
            $error = "inputError_invalid";
        }
        if ($error)
        {
            if (array_key_exists('uuid', $this->vars))
            {
                $uuid = $this->vars['uuid'];
            }
            else
            {
                $uuid = \TEMPSTORAGE\getUuid($this->db);
                \TEMPSTORAGE\store($this->db, $uuid, ['title' => $title, 'tagId' => $tagId]);
            }
            $message = rawurlencode($error);
            header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=editUserTagInit&error=$error&uuid=$uuid");
            die;
        }
        $this->db->formatConditions(['usertagsId' => $tagId]);
        $this->db->update('user_tags', ['usertagsTag' => $title]);
        if (array_key_exists('uuid', $this->vars))
        {
            \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
        }
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=editUserTagInit&success=usertagEdit&tagId=$tagId");
        die;
    }
    /**
     * Display further user tags settings for deleting
     */
    public function deleteUserTagInit()
    {
        if (!$tagId = $this->vars['tagId'])
        {
            $this->badInputPopup($this->errors->text("inputError", "missing"), 'userTags');
        }
        $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['usertagsId' => $tagId]);
        $recordSet = $this->db->select('user_tags', 'usertagsId');
        if (!$this->db->numRows($recordSet))
        {
            $this->badInputPopup($this->errors->text('inputError', 'invalid'), 'userTags');
        }
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "deleteUserTag")
        ));
        $pString = '';
        $this->db->formatConditions(['usertagsId' => $this->vars['tagId']]);
        $title = \HTML\strong($this->db->selectFirstField('user_tags', 'usertagsTag'));
        $pString .= \HTML\p($this->messages->text("user", "deleteConfirmUserTag") . ":&nbsp;&nbsp;" .
            \HTML\nlToHtml($title));
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userTags"; </script>');
        $pString .= \FORM\formHeader('usersgroups_MYWIKINDX_CORE');
        $pString .= \FORM\hidden("method", "deleteUserTag");
        $pString .= \FORM\hidden("tagId", $this->vars['tagId']);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Delete")));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
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
        // Delete usertag
        $this->db->formatConditions(['usertagsId' => $this->vars['tagId']]);
        $this->db->delete('user_tags');
        // Remove user_tag ids from resource_user_tags.TagIds
        $this->db->formatConditions(['resourceusertagsTagId' => $this->vars['tagId']]);
        $this->db->delete('resource_user_tags');
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=deleteUserTagSuccess&success=usertagDelete");
        die;
    }
    /**
     * Display success page when deleting a user tag
     *
     * @param mixed $message
     */
    public function deleteUserTagSuccess($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "deleteUserTag")
        ));
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userTags"; </script>');
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
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
        if ($this->db->numRows($this->db->select('user_groups', ['usergroupsId', 'usergroupsTitle'])))
        {
            $groups = TRUE;
        }
        if (empty($bibs))
        {
            $pString .= \HTML\td($this->messages->text("user", "noBibs"));
            $radios = \HTML\p(\FORM\radioButton(FALSE, "method", "createUserBibInit", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "createBib"), FALSE, "left");
            if ($groups)
            {
                $radios .= \HTML\p(\FORM\radioButton(FALSE, "method", "createGroupBibInit", FALSE) . "&nbsp;&nbsp;" .
                    $this->messages->text("user", "createGroupBib"), FALSE, "left");
            }
        }
        else
        {
            $pString .= \HTML\td(\FORM\selectFBoxValue($this->messages->text("user", "bib"), "bibId", $bibs, 5));
            $radios = \HTML\p(\FORM\radioButton(FALSE, "method", "createUserBibInit", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("user", "createBib"), FALSE, "left");
            if ($groups)
            {
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
     * @param mixed $message
     * @param mixed $groupBib
     */
    public function createUserBibInit($message = FALSE, $groupBib = FALSE)
    {
        // If creating a group bibliography, this user must own groups
        if ($groupBib)
        {
            GLOBALS::setTplVar('heading', $this->messages->text(
                "heading",
                "myWikindx",
                ": " . $this->messages->text("user", "createGroupBib")
            ));
            $this->db->formatConditions(['usergroupsAdminId' => $this->session->getVar("setup_UserId")]);
            $this->db->orderBy('usergroupsTitle');
            $recordset = $this->db->select('user_groups', ['usergroupsId', 'usergroupsTitle']);
            if (!$this->db->numRows($recordset))
            {
                $this->catastrophic($this->errors->text("inputError", "userHasNoGroups"), 'userBibs');
            }
            while ($row = $this->db->fetchRow($recordset))
            {
                $groups[$row['usergroupsId']] = \HTML\dbToFormTidy($row['usergroupsTitle']);
            }
            $groupString = \HTML\p(\FORM\selectFBoxValue(
                $this->messages->text("user", "addGroupsToBib"),
                "addUsers",
                $groups,
                10
            ));
        }
        else
        {
            $groupString = FALSE;
            GLOBALS::setTplVar('heading', $this->messages->text(
                "heading",
                "myWikindx",
                ": " . $this->messages->text("user", "createBib")
            ));
        }
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
        	if (strpos($this->vars['success'], '_') !== FALSE) {
        		$split = explode('_', $this->vars['success']);
        		$message = $this->success->text($split[0], $split[1]);
        	} else {
	            $message = $this->success->text($this->vars['success']);
	        }
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userBibs"; </script>');
        $pString .= \FORM\formHeader("usersgroups_MYWIKINDX_CORE");
        if ($groupBib)
        {
            $pString .= \FORM\hidden("method", "createGroupBib");
        }
        else
        {
            $pString .= \FORM\hidden("method", "createUserBib");
        }
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("user", "bibTitle"),
            "title",
            FALSE,
            50,
            255
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $description = array_key_exists('description', $this->formData) ? $this->formData['description'] : FALSE;
        $pString .= \HTML\td(\FORM\textAreaInput(
            $this->messages->text("user", "bibDescription"),
            "description",
            $description,
            80,
            10
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= $groupString;
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Display further group bibs settings for creating
     *
     * @param mixed $message
     */
    public function createGroupBibInit($message = FALSE)
    {
        if (array_key_exists('message', $this->vars))
        {
            $message = $this->vars['message'];
        }
        $this->createUserBibInit($message, TRUE);
    }
    // NOT USED?????
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
        if (array_key_exists('description', $this->vars))
        {
            $description = \UTF8\mb_trim($this->vars['description']);
            if ($description)
            {
                $fields[] = 'userbibliographyDescription';
                $values[] = $description;
            }
            $this->formData['description'] = $description;
        }
        if (!$title = \UTF8\mb_trim($this->vars['title']))
        {
            $error = $this->errors->text("inputError", "missing");
        }
        $this->formData['title'] = $title;
        if (!$this->checkBibliographyExists($title))
        {
            $error = $this->errors->text("inputError", "bibExists");
        }
        if ($error)
        {
            if (array_key_exists('addUsers', $this->vars))
            {
                $this->badInputPopup($error, 'createGroupBibInit');
            }
            else
            {
                $this->badInputPopup($error, 'createUserBibInit');
            }
        }
        $fields[] = 'userbibliographyTitle';
        $values[] = $title;
        $fields[] = 'userbibliographyUserId';
        $values[] = $this->session->getVar("setup_UserId");
        if (array_key_exists('addUsers', $this->vars))
        {
            $groupIds = $this->vars['addUsers'];
            if ($groupIds[0])
            { // [0] will be '0' if not a group bibliography
                $groupId = $groupIds[0];
                $fields[] = 'userbibliographyUserGroupId';
                $values[] = $groupId;
            }
        }
        $this->db->insert('user_bibliography', $fields, $values);
        $this->session->setVar("setup_Bibliographies", TRUE);
        $message = "bibliographyAdd_$title";
        $this->formData = [];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=createUserBibInit&success=$message");
        die;
    }
    /**
     * Display further user/group bibs settings for editing
     *
     * @param mixed $message
     */
    public function editBibInit($message = FALSE)
    {
        $uuid = FALSE;
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "editBib")
        ));
        $groupUsers = [];
        if (array_key_exists('uuid', $this->vars))
        {
            $uuid = $this->vars['uuid'];
        }
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
	            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userBibs"; </script>');
        $pString .= \FORM\formHeader("usersgroups_MYWIKINDX_CORE");
        $pString .= \FORM\hidden("method", "editBib");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        if (!$uuid)
        {
            $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar("setup_UserId")]);
            $this->db->formatConditions(['userbibliographyId' => $this->vars['bibId']]);
            $recordset = $this->db->select(
                'user_bibliography',
                ['userbibliographyTitle', 'userbibliographyDescription', 'userbibliographyUserGroupId']
            );
            if (!$this->db->numRows($recordset))
            {
                $this->catastrophic($this->errors->text("inputError", "invalid"), 'userBibs');
            }
            $row = $this->db->fetchRow($recordset);
            $description = \HTML\dbToFormTidy($row['userbibliographyDescription']);
            $title = \HTML\dbToFormTidy($row['userbibliographyTitle']);
            $groupId = $row['userbibliographyUserGroupId'];
            $pString .= \FORM\hidden("bibId", $this->vars['bibId']);
        }
        else
        { // Back here after editing bib so fetch details from temp_storage
            $data = \TEMPSTORAGE\fetch($this->db, $uuid);
            $pString .= \FORM\hidden("bibId", $data['bibId']);
            $pString .= \FORM\hidden("uuid", $uuid);
            $title = $data['title'];
            $description = $data['description'];
            $groupId = array_key_exists('groupId', $data) ? $data['groupId'] : FALSE;
            if ($groupId)
            {
                $pString .= \FORM\hidden("groupId", $groupId);
            }
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
        if ($groupId)
        { // This is a user group bibliography
            $pString .= BR . "&nbsp;" . BR;
            $pString .= \HTML\tableStart();
            $pString .= \HTML\trStart();
            $pString .= \FORM\hidden('groupBib', TRUE);
            $this->db->formatConditions(['usergroupsAdminId' => $this->session->getVar("setup_UserId")]);
            $this->db->orderBy('usergroupsTitle');
            $recordset = $this->db->select('user_groups', ['usergroupsId', 'usergroupsTitle']);
            while ($row = $this->db->fetchRow($recordset))
            {
                $groups[$row['usergroupsId']] = \HTML\dbToFormTidy($row['usergroupsTitle']);
            }
            $pString .= \HTML\p(\FORM\selectedBoxValue(
                $this->messages->text("user", "addGroupsToBib"),
                "groupId",
                $groups,
                $groupId,
                10
            ));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
        }
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
        if ($uuid)
        {
            GLOBALS::addTplVar('scripts', '<script>var uuid = ' . $uuid . '; </script>');
        }
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
        if (!$bibId = $this->vars['bibId'])
        {
            $error = "inputError_missing";
        }
        if (!$this->checkValidBibliography($bibId))
        {
            $error = "inputError_invalid";
        }
        if (!$title = \UTF8\mb_trim($this->vars['title']))
        {
            $error = "inputError_missing";
        }
        if (!$this->checkBibliographyExists($title, $bibId))
        {
            $error = "inputError_bibExists";
        }
        $description = \UTF8\mb_trim($this->vars['description']);
        if (array_key_exists('groupId', $this->vars))
        { // user group bibliography
            $groupId = $this->vars['groupId'];
            $tsArray = ['title' => $title, 'bibId' => $bibId, 'description' => $description, 'groupId' => $groupId];
            $updateArray['userbibliographyUserGroupId'] = $groupId;
        }
        else
        {
            $tsArray = ['title' => $title, 'bibId' => $bibId, 'description' => $description];
        }
        if ($error)
        {
            if (array_key_exists('uuid', $this->vars))
            {
                $uuid = $this->vars['uuid'];
                \TEMPSTORAGE\delete($this->db, $uuid);
            }
            else
            {
                $uuid = \TEMPSTORAGE\getUuid($this->db);
            }
            \TEMPSTORAGE\store($this->db, $uuid, $tsArray);
            $message = rawurlencode($error);
            header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=editBibInit&error=$error&uuid=$uuid");
            die;
        }
        if ($description)
        {
            $updateArray['userbibliographyDescription'] = $description;
        }
        else
        {
            $this->db->formatConditions(['userbibliographyId' => $bibId]);
            $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar("setup_UserId")]);
            $this->db->updateNull('user_bibliography', 'userbibliographyDescription');
        }
        $updateArray['userbibliographyTitle'] = $title;
        $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['userbibliographyId' => $bibId]);
        $this->db->update('user_bibliography', $updateArray);
        if (array_key_exists('uuid', $this->vars))
        {
            \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
        }
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=editBibInit&success=bibliographyEdit&bibId=$bibId");
        die;
    }
    /**
     * Ask for confirmation of delete bibliography
     *
     * @param mixed $message
     */
    public function deleteBibInit($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "deleteBib")
        ));
        if (array_key_exists('message', $this->vars))
        {
            $message = $this->vars['message'];
        }
        $pString = $message;
        $bibId = $this->vars['bibId'];
        $this->db->formatConditions(['userbibliographyId' => $bibId]);
        $title = \HTML\strong($this->db->selectFirstField('user_bibliography', 'userbibliographyTitle'));
        $pString .= \HTML\p($this->messages->text("user", "deleteConfirmBib") . ":&nbsp;&nbsp;" .
            \HTML\nlToHtml($title));
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userBibs"; </script>');
        $pString .= \FORM\formHeader("usersgroups_MYWIKINDX_CORE");
        $pString .= \FORM\hidden("method", "deleteBib");
        $pString .= \FORM\hidden("bibId", $bibId);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Delete")));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
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
        if (!$bibId = $this->vars['bibId'])
        {
            $error = $this->errors->text("inputError", "missing");
        }
        if (!$this->checkValidBibliography($bibId))
        {
            $error = $this->errors->text("inputError", "invalid");
        }
        if ($error)
        {
            $this->catastrophic($error, 'userBibs');
        }
        $this->checkValidBibliography($bibId, 'userBibs');
        $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions(['userbibliographyId' => $bibId]);
        $this->db->delete('user_bibliography');
        // Get any bibliographyIds and delete those bibliographies
        $this->db->formatConditions(['userbibliographyresourceBibliographyId' => $bibId]);
        $this->db->delete('user_bibliography_resource');
        if ($bibId == GLOBALS::getUserVar('BrowseBibliography'))
        {
            $this->db->formatConditions(['usersId' => $this->session->getVar('setup_UserId')]);
            $this->db->update('users', ['usersBrowseBibliography' => 0]);
        }
        $bibsU = $this->bib->getUserBibs();
        $bibsUG = $this->bib->getGroupBibs();
        if (empty($bibsU) && empty($bibsUG))
        {
            $this->session->delVar("setup_Bibliographies");
        }
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=deleteBibSuccess&success=bibliographyDelete");
        die;
    }
    /**
     * Display success for delete bibliography
     *
     * @param mixed $message
     */
    public function deleteBibSuccess($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text(
            "heading",
            "myWikindx",
            ": " . $this->messages->text("user", "deleteBib")
        ));
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
	            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        GLOBALS::addTplVar('scripts', '<script>var selectItem = "userBibs"; </script>');
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Edit forgotten password details
     */
    public function forgetConfigEdit()
    {
        $error = '';
        $array = ["usersPasswordQuestion1", "usersAnswer1", "usersPasswordQuestion2", "usersAnswer2", "usersPasswordQuestion3", "usersAnswer3"];
        foreach ($array as $key)
        {
            if (array_key_exists($key, $this->vars) && \UTF8\mb_trim($this->vars[$key]))
            {
                $this->formData[$key] = \UTF8\mb_trim($this->vars[$key]);
            }
            else
            {
                $this->formData[$key] = FALSE;
            }
        }
        $inputArray = [];
        for ($i = 1; $i < 4; $i++)
        {
            if ($this->formData["usersPasswordQuestion$i"] && !$this->formData["usersAnswer$i"])
            {
                $error = $this->errors->text("inputError", "missing");
            }
            elseif ($this->formData["usersPasswordQuestion$i"] && $this->formData["usersAnswer$i"])
            {
                $inputArray[$this->formData["usersPasswordQuestion$i"]] = sha1(mb_strtolower($this->formData["usersAnswer$i"]));
            }
        }
        if ($error)
        {
            $this->badInputLoad($error, 'forget');
        }
        $index = 1;
        foreach ($inputArray as $q => $a)
        {
            $update["usersPasswordQuestion$index"] = $q;
            $update["usersPasswordAnswer$index"] = $a;
            $index++;
        }
        if (isset($update))
        { // values to update
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $this->db->update('users', $update);
        }
        // Set remaining fields to ''
        while ($index < 4)
        {
            $nulls["usersPasswordQuestion$index"] = '';
            $nulls["usersPasswordAnswer$index"] = '';
            $index++;
        }
        if (isset($nulls))
        {
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $this->db->update('users', $nulls);
        }
        $selectItem = $this->vars['selectItem'];
        header("Location: index.php?action=usersgroups_MYWIKINDX_CORE&method=init&success=forgetUpdate&selectItem=$selectItem");
        die;
    }
    /**
     * Make the select transfer arrows to transfer users between select boxes with onclick
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrows()
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'selectedUsers',
        ];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'potentialUsers',
        ];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);

        return [$toRightImage, $toLeftImage];
    }
    /**
     * Get group users
     *
     * @param int $groupId
     *
     * @return array
     */
    private function getGroupUsers($groupId)
    {
        $groupUsers = [];
        $this->db->formatConditions(['usergroupsusersGroupId' => $groupId]);
        $this->db->leftJoin('users', 'usersId', 'usergroupsusersUserId');
        $recordset = $this->db->select(
            'user_groups_users',
            ['usergroupsusersUserId', 'usersUsername', 'usersFullname', 'usersAdmin']
        );
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$row['usergroupsusersUserId'])
            {
                continue;
            }
            if ($row['usergroupsusersUserId'] == $this->session->getVar("setup_UserId"))
            {
                continue;
            }
            $groupUsers[$row['usergroupsusersUserId']] = \HTML\dbToFormTidy($row['usersUsername']);
            if ($row['usersFullname'])
            {
                $groupUsers[$row['usergroupsusersUserId']] .= " (" . \HTML\dbToFormTidy($row['usersFullname']) . ")";
            }
            if ($row['usersAdmin'])
            {
                $groupUsers[$row['usergroupsusersUserId']] .= " ADMIN";
            }
        }

        return $groupUsers;
    }
    /**
     * create array of config menu items
     */
    private function getConfigGroups()
    {
        $groups = ['resources' => $this->messages->text('config', 'resources'),
            'appearance' => $this->messages->text('config', 'appearance'),
        ];
        if ($this->session->getVar("setup_UserId") != WIKINDX_RESTRICT_USERID)
        {
            if ($this->usersEmail && WIKINDX_MAIL_USE)
            {
                $groups['forget'] = $this->messages->text('config', 'forget');
                $groups['notification'] = $this->messages->text('config', 'notification');
            }
        }
        // Only for logged on users
        if ($this->session->getVar("setup_UserId"))
        {
            $groups['userTags'] = $this->messages->text("user", "userTags");
            $groups['userBibs'] = $this->messages->text('user', 'bib');
            // Add user group administration only if there is more than one user.
            $resourceId = $this->db->select('users', 'usersId');
            if ($this->db->numRows($resourceId) > 1)
            {
                $groups['userGroups'] = $this->messages->text("user", "groups");
            }
            $user = [];
            if ($this->session->getVar("setup_UserId") != WIKINDX_RESTRICT_USERID)
            {
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
        if (array_key_exists('ajaxReturn', $this->vars))
        {
            $item = $this->vars['ajaxReturn'];
        }
        elseif (!$item)
        { // grab the first of the list
            foreach ($groups as $item => $null)
            {
                break;
            }
        }
        if (($item != 'resources') || ($item != 'appearance'))
        {
            $gatekeep = FACTORY_GATEKEEP::getInstance();
            $gatekeep->requireSuper = FALSE;
            $gatekeep->init();
        }
        if ($item == 'user')
        {
            $password = FACTORY_PASSWORD::getInstance();
            if ($this->session->getVar("setup_UserId") == WIKINDX_SUPERADMIN_ID)
            {
                list($formText, $jsString) = $password->createElements($this->usersUsername, TRUE, $this->formData);
            }
            else
            {
                list($formText, $jsString) = $password->createElements(FALSE);
            }
            $pString = \FORM\formHeader("usersgroups_MYWIKINDX_CORE", 'onsubmit="return checkForm(' . $jsString . ');"');
        }
        elseif (($item == 'userGroups'))
        {
            $pString = \FORM\formHeader(FALSE);
            $pString .= \FORM\hidden("method", 'userGroupsConfigDisplay');
        }
        elseif (($item == 'userTags'))
        {
            $pString = \FORM\formHeader(FALSE);
            $pString .= \FORM\hidden("method", 'userTagsConfigDisplay');
        }
        elseif (($item == 'userBibs'))
        {
            $pString = \FORM\formHeader(FALSE);
            $pString .= \FORM\hidden("method", 'userBibsConfigDisplay');
        }
        else
        {
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
        if (!$this->db->numRows($recordset))
        {
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
        if (!empty($this->formData))
        {
            if (array_key_exists('cookie', $this->formData))
            {
                $field = TRUE;
            }
            else
            {
                $field = FALSE;
            }
        }
        elseif ($row["Cookie"] == 'Y')
        {
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
        if (!GLOBALS::getUserVar("PagingTagCloud"))
        {
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
        if (array_key_exists('UseWikindxKey', $this->formData))
        {
            $field = 'CHECKED';
        }
        elseif (GLOBALS::getUserVar("UseWikindxKey"))
        {
            $field = 'CHECKED';
        }
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "useWikindxKey"),
            "UseWikindxKey",
            $field
        ));
        $field = FALSE;
        if (array_key_exists('UseBibtexKey', $this->formData))
        {
            $field = 'CHECKED';
        }
        elseif (GLOBALS::getUserVar("UseBibtexKey"))
        {
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
        if (array_key_exists('DisplayBibtexLink', $this->formData))
        {
            $field = 'CHECKED';
        }
        elseif (GLOBALS::getUserVar("DisplayBibtexLink"))
        {
            $field = 'CHECKED';
        }
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "displayBibtexLink"),
            "DisplayBibtexLink",
            $field
        ));
        $field = FALSE;
        if (array_key_exists('DisplayCmsLink', $this->formData))
        {
            $field = 'CHECKED';
        }
        elseif (GLOBALS::getUserVar("DisplayCmsLink"))
        {
            $field = 'CHECKED';
        }
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "displayCmsLink"),
            "DisplayCmsLink",
            $field
        ));
        $field = FALSE;
        if (array_key_exists('ListLink', $this->formData))
        {
            $field = 'CHECKED';
        }
        elseif (GLOBALS::getUserVar("ListLink"))
        {
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
        if (empty($this->formData))
        {
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $resultSet = $this->db->select('users', ["usersPasswordQuestion1", "usersPasswordQuestion2", "usersPasswordQuestion3"]);
            while ($row = $this->db->fetchRow($resultSet))
            {
                $fields["usersPasswordQuestion1"] = $row["usersPasswordQuestion1"];
                $fields["usersPasswordQuestion2"] = $row["usersPasswordQuestion2"];
                $fields["usersPasswordQuestion3"] = $row["usersPasswordQuestion3"];
                $fields["usersAnser1"] = FALSE;
                $fields["usersAnser2"] = FALSE;
                $fields["usersAnser3"] = FALSE;
            }
        }
        else
        {
            $fields = $this->formData;
        }
        $pString = $this->messageString;
        $pString .= \FORM\hidden("method", "forgetConfigEdit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        $td = $this->messages->text("user", "forget1");
        $td .= \HTML\p($this->messages->text("user", "forget5"));
        $td .= \HTML\p($this->messages->text("user", "forget2"));
        for ($i = 1; $i < 4; $i++)
        {
            $question = array_key_exists("usersPasswordQuestion$i", $fields) ?
                \HTML\dbToFormTidy($fields["usersPasswordQuestion$i"]) : FALSE;
            $answer = array_key_exists("usersAnswer$i", $fields) ?
                \HTML\dbToFormTidy($fields["usersAnswer$i"]) : FALSE;
            $string = \FORM\textInput(
                $this->messages->text("user", "forget3", "&nbsp;" . $i),
                "usersPasswordQuestion$i",
                $question,
                100,
                255
            );
            $string .= BR . \FORM\textInput(
                $this->messages->text("user", "forget4", "&nbsp;" . $i),
                "usersAnswer$i",
                $answer,
                50,
                100
            );
            $td .= \HTML\p($string);
        }
        $pString .= \HTML\td($td);
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
        if (empty($this->formData))
        {
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $recordset = $this->db->select('users', ['usersNotify', 'usersNotifyAddEdit',
                'usersNotifyThreshold', 'usersNotifyTimestamp', 'usersNotifyDigestThreshold', ]);
            $row = $this->db->fetchRow($recordset);
        }
        $pString .= \HTML\tdStart();
        $checked = $add = $edit = $nThreshold = $dThreshold = FALSE;
        if (array_key_exists('Notify', $this->formData))
        {
            if ($this->formData['Notify'] == 'N')
            {
                $checked = TRUE;
            }
        }
        elseif ($row["usersNotify"] == 'N')
        {
            $checked = TRUE;
        }
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "N", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyNone"));
        $checked = FALSE;
        if (!$checked)
        {
            if (array_key_exists('Notify', $this->formData))
            {
                if ($this->formData['Notify'] == 'A')
                {
                    $checked = TRUE;
                }
            }
            elseif ($row["usersNotify"] == 'A')
            {
                $checked = TRUE;
            }
        }
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "A", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyAll"));
        $checked = FALSE;
        if (!$checked)
        {
            if (array_key_exists('Notify', $this->formData))
            {
                if ($this->formData['Notify'] == 'M')
                {
                    $checked = TRUE;
                }
            }
            elseif ($row["usersNotify"] == 'M')
            {
                $checked = TRUE;
            }
        }
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "M", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyMyBib"));
        $checked = FALSE;
        if (!$checked)
        {
            if (array_key_exists('Notify', $this->formData))
            {
                if ($this->formData['Notify'] == 'C')
                {
                    $checked = TRUE;
                }
            }
            elseif ($row["usersNotify"] == 'C')
            {
                $checked = TRUE;
            }
        }
        $pString .= \HTML\p(\FORM\radioButton(FALSE, "Notify", "C", $checked) . "&nbsp;&nbsp;" .
            $this->messages->text("user", "notifyMyCreator"));
        if (empty($this->formData))
        {
            if ($row["usersNotifyAddEdit"] == 'A')
            {
                $add = 'CHECKED';
                $edit = 'CHECKED';
            }
            elseif ($row["usersNotifyAddEdit"] == 'N')
            {
                $add = 'CHECKED';
            }
            elseif ($row["usersNotifyAddEdit"] == 'E')
            {
                $edit = 'CHECKED';
            }
        }
        else
        {
            if (array_key_exists('NotifyAdd', $this->formData))
            {
                $add = 'CHECKED';
            }
            if (array_key_exists('NotifyEdit', $this->formData))
            {
                $edit = 'CHECKED';
            }
        }
        $pString .= \HTML\p($this->messages->text("user", "notifyAdd") . ":&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, "NotifyAdd", $add) . BR .
            $this->messages->text("user", "notifyEdit") . ":&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, "NotifyEdit", $edit));
        $array = [0 => $this->messages->text("user", "notifyImmediate"), 1 => 1, 7 => 7, 14 => 14, 28 => 28];
        if (array_key_exists('NotifyThreshold', $this->formData))
        {
            $nThreshold = $this->formData['NotifyThreshold'];
        }
        elseif ($row["usersNotifyThreshold"])
        {
            $nThreshold = $this->session->getVar("mywikindx_NotifyThreshold");
        }
        if ($nThreshold)
        {
            $pString .= \HTML\p(\FORM\selectedBoxValue(
                $this->messages->text("user", "notifyThreshold"),
                "NotifyThreshold",
                $array,
                $nThreshold,
                5
            ));
        }
        else
        {
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
        if ($this->session->getVar("mywikindx_Bibliographies"))
        {
            $bibsRaw = unserialize($this->session->getVar("mywikindx_Bibliographies"));
            foreach ($bibsRaw as $key => $value)
            {
                $bibsU[$key] = \HTML\dbToFormTidy($value);
            }
        }
        // Get this user's user group bibliographies
        if ($this->session->getVar("mywikindx_Groupbibliographies"))
        {
            $bibsRaw = unserialize($this->session->getVar("mywikindx_Groupbibliographies"));
            foreach ($bibsRaw as $key => $value)
            {
                $bibsUG[$key] = \HTML\dbToFormTidy($value);
            }
        }
        $bibsU = $this->bib->getUserBibs();
        $bibsUG = $this->bib->getGroupBibs();
        if (!empty($bibsU))
        {
            $bibsArray[-1] = $this->messages->text('user', 'userBibs');
            foreach ($bibsU as $key => $value)
            {
                $bibsArray[$key] = $value;
            }
        }
        if (!empty($bibsUG))
        {
            $bibsArray[-2] = $this->messages->text('user', 'userGroupBibs');
            foreach ($bibsUG as $key => $value)
            {
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
     *
     * @return bool
     */
    private function checkUserGroupExists($title, $groupId = FALSE)
    {
        if ($groupId)
        {
            $this->db->formatConditions(['usergroupsId' => $groupId], TRUE); // Not this $groupId
        }
        $recordset = $this->db->select('user_groups', ['usergroupsTitle', 'usergroupsId']);
        while ($row = $this->db->fetchRow($recordset))
        {
            if (mb_strtolower($title) == mb_strtolower($row['usergroupsTitle']))
            {
                return FALSE;
            }
        }

        return TRUE;
    }
    /**
     * check this user can edit and delete bibliographies
     *
     * @param string $bibId
     * @param mixed $uuid
     *
     * @return bool
     */
    private function checkValidBibliography($bibId)
    {
        $this->db->formatConditions(['userbibliographyId' => $bibId]);
        $adminId = $this->db->selectFirstField('user_bibliography', 'userbibliographyUserId');
        if ($this->session->getVar("setup_UserId") != $adminId)
        {
            return FALSE;
        }

        return TRUE;
    }
    /**
     * Does bibliography already exist?
     * If $bibId, we're editing an existing user bibliography.
     *
     * @param string $title
     * @param mixed $bibId
     *
     * @return bool
     */
    private function checkBibliographyExists($title, $bibId = FALSE)
    {
        if ($bibId)
        {
            $this->db->formatConditions(['userbibliographyId' => $bibId], TRUE); // NOT this $bibId
        }
        $recordset = $this->db->select('user_bibliography', ['userbibliographyTitle']);
        while ($row = $this->db->fetchRow($recordset))
        {
            if (mb_strtolower($title) == mb_strtolower($row['userbibliographyTitle']))
            {
                return FALSE;
            }
        }

        return TRUE;
    }
    /**
     * check this user can edit and delete user groups
     *
     * @param int $groupId
     *
     * @return bool
     */
    private function checkValidUserGroup($groupId)
    {
        $this->db->formatConditions(['usergroupsId' => $groupId]);
        $adminId = $this->db->selectFirstField('user_groups', 'usergroupsAdminId');
        if ($this->session->getVar("setup_UserId") != $adminId)
        {
            return FALSE;
        }

        return TRUE;
    }
    /**
     * Error handling
     *
     * @param mixed $error
     * @param mixed $item
     */
    private function badInputLoad($error, $item = FALSE)
    {
        if ($item)
        {
            $this->badInput->close($error, $this, ['init', $item]);
        }
        else
        {
            $this->badInput->close($error, $this, 'init');
        }
    }
    /**
     * Error handling for a popup
     *
     * @param mixed $error
     * @param string $item
     */
    private function badInputPopup($error, $item)
    {
        $this->badInput->closeType = 'closeNoMenu';
        $this->badInput->close($error, $this, $item);
    }
    /**
     * Catastrophic error – bail out immediately!
     *
     * @param string $error
     * @param string $selectItem
     */
    private function catastrophic($error, $selectItem)
    {
        $pString = $error;
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/usersgroups/mywikindx.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        $pString .= '<script>var selectItem = "' . $selectItem . '";</script>';
        $pString .= \FORM\formHeader(FALSE, "onsubmit=\"window.closeAndRedirect();return true;\"");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        $this->badInput->closeType = 'closeNoMenu';
        $this->badInput->close($pString);
    }
}
