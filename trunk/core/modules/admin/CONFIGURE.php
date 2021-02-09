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
 *	CONFIGURE WIKINDX class
 */
class CONFIGURE
{
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $user;
    private $db;
    private $vars;
    private $badInput;
    private $gatekeep;
    private $tinymce;
    private $configDbStructure;
    private $messageString = FALSE;
    private $formData = [];
    private $error = FALSE;

    public function __construct($initial = FALSE)
    {
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->tinymce = FACTORY_LOADTINYMCE::getInstance();
        $this->configDbStructure = FACTORY_CONFIGDBSTRUCTURE::getInstance();
        $this->gatekeep->requireSuper = TRUE;
        if (!$initial)
        {
            $this->gatekeep->init();
        }
    }
    /**
     * init
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        if (is_array($message))
        {
            $this->messageString = $message[0];
            $item = $message[1];
        }
        else if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $this->messageString = $this->success->text($this->vars['success']);
            if (array_key_exists('selectItem', $this->vars))
            {
                $item = $this->vars['selectItem'];
            }
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $this->messageString = $this->errors->text($split[0], $split[1]);
            if (array_key_exists('selectItem', $this->vars))
            {
                $item = $this->vars['selectItem'];
            }
        }
        else
        {
            $this->messageString = $message;
            $item = FALSE;
        }
        $configGroups = $this->getConfigGroups();
        if (empty($configGroups))
        {
            return FALSE;
        }
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('configure'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "configure"));
        $jsonArray = [];
        $jScript = 'index.php?action=admin_CONFIGURE_CORE&method=initConfigDiv';
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
            ), 'configMenu', $configGroups, $item, sizeof($configGroups), FALSE, $js));
        }
        else
        {
            $pString .= \HTML\td(\FORM\selectFBoxValue($this->messages->text(
                'config',
                'options'
            ), 'configMenu', $configGroups, sizeof($configGroups), FALSE, $js));
        }
        $pString .= \HTML\td(\HTML\div('configDiv', $this->getConfigDetails($configGroups, $item)), 'left top width80percent');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
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
     * Display front page description in a pop up
     */
    public function displayFrontDescription()
    {
        $this->fromDbToFormdata();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "configure"));
        $pString = $this->session->getVar("configmessage");
        $this->session->delVar("configmessage");
        $pString .= $this->tinymce->loadMinimalTextarea(['configDescription'], TRUE);
        $pString .= \FORM\formHeader("admin_CONFIGURE_CORE");
        $pString .= \FORM\hidden("method", "writeDb");
        $pString .= \FORM\hidden("selectItem", 'frontDescription');
        $input = array_key_exists("configDescription", $this->formData) ? $this->formData["configDescription"] : WIKINDX_DESCRIPTION_DEFAULT;
        $pString .= \FORM\textareaInput($this->messages->text("config", "description"), "configDescription", $input, 75, 20);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader("admin_CONFIGURE_CORE&amp;method=init&amp;selectItem=front", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "front");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Display impressum in a pop up
     */
    public function displayImpressum()
    {
        $this->fromDbToFormdata();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "configure"));
        $pString = $this->session->getVar("configmessage");
        $this->session->delVar("configmessage");
        $pString .= $this->tinymce->loadMinimalTextarea(['configImpressum'], TRUE);
        $pString .= \FORM\formHeader("admin_CONFIGURE_CORE");
        $pString .= \FORM\hidden("method", "writeDb");
        $pString .= \FORM\hidden("selectItem", 'impressum');
        $input = array_key_exists("configImpressum", $this->formData) ? $this->formData["configImpressum"] : WIKINDX_IMPRESSUM_DEFAULT;
        $pString .= \FORM\textareaInput($this->messages->text("config", "impressum"), "configImpressum", $input, 75, 20);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));
        $pString .= \FORM\formEnd();
        $pString .= \FORM\formHeader("admin_CONFIGURE_CORE&amp;method=init&amp;selectItem=front", "onsubmit=\"window.close();return true;\"");
        $pString .= \FORM\hidden("selectItem", "miscellaneous");
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Open popup for ldap transaction report
     */
    public function ldapTester()
    {
        $isLdapExtAvailable = in_array("ldap", get_loaded_extensions());
        
        $log = "";
        $status = FALSE;
        $executionStatus = NULL;
              
        if (
            array_key_exists('configLdapTestUser', $this->vars) && $this->vars['configLdapTestUser']
            && array_key_exists('configLdapTestPassword', $this->vars) && $this->vars['configLdapTestPassword']
        ) {            
            $login = $this->vars['configLdapTestUser'];
            $pwd = $this->vars['configLdapTestPassword'];
            $ldapUserEntry = [];
            
            $user = FACTORY_USER::getInstance();
            $status = $user->checkPasswordLdap($login, $pwd, $ldapUserEntry, $log, $executionStatus);
        }
        
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "ldapTester"));
        $pString = $this->session->getVar("configmessage");
        $this->session->delVar("configmessage");
        $pString .= \HTML\p($this->messages->text("hint", "ldapTest"));
        
        $pString .= \FORM\formHeader("admin_CONFIGURE_CORE");
        $pString .= \FORM\hidden("method", "ldapTester");
        
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        
            $pString .= \HTML\tdStart();
            array_key_exists("configLdapTestUser", $this->vars) ? $input = $this->vars["configLdapTestUser"] : $input = FALSE;
            $pString .= \HTML\p(\FORM\textInput($this->messages->text("config", "ldapTestUsername"), "configLdapTestUser", $input, 30, 255));
            $pString .= \HTML\tdEnd();
            
            $pString .= \HTML\tdStart("width100percent");
            array_key_exists("configLdapTestPassword", $this->vars) ? $input = $this->vars["configLdapTestPassword"] : $input = FALSE;
            $pString .= \HTML\p(\FORM\passwordInput($this->messages->text("config", "ldapTestPassword"), "configLdapTestPassword", $input, 30, 255));
            $pString .= \HTML\tdEnd();
            
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));

        $pString .= \FORM\formEnd();
        
        $close = \FORM\formHeader("admin_CONFIGURE_CORE&amp;method=init&amp;selectItem=front", "onsubmit=\"window.close();return true;\"");
        $close .= \FORM\hidden("selectItem", "email");
        $close .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $close .= \FORM\formEnd();
        $pString .= \HTML\p($close);
            
        if ($executionStatus !== NULL)
        {
            $pString .= "<hr>";
            $pString .= "<h4>Log</h4>";
            if ($executionStatus)
                $pString .= \HTML\p("The test passed", "success", "center");
            else
                $pString .= \HTML\p("The test failed", "error", "center");
            if ($status)
                $pString .= \HTML\p("The user has been authenticated.", "success", "center");
            else
                $pString .= \HTML\p("The user has not been authenticated", "error", "center");
            $pString .= "<pre>" . htmlentities($log) . "</pre>";
        }
        
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Make the resource select transfer arrows to transfer resources between select boxes with onclick
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrows()
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'selectResource',
        ];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'discardResource',
        ];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);

        return [$toRightImage, $toLeftImage];
    }
    /**
     * write values to database table
     */
    public function writeDb()
    {
        $this->checkInput();
        // If we get here, we're cleared to write to the database.  This user is always id = 1
        $updateArray = $nulls = [];
        $possibleVars = $this->getPossibleVars();
        foreach ($possibleVars as $field)
        {
            if ($field == 'configDeactivateResourceTypes')
            {
                if (array_key_exists('configDeactivateResourceTypes', $this->formData))
                {
                    // Ensure we always have at least one resource . . .
                    $resourceMap = FACTORY_RESOURCEMAP::getInstance();
                    $typesRaw = $resourceMap->getTypesRaw();
                    foreach ($typesRaw as $type)
                    {
                        $types[$type] = $this->messages->text("resourceType", $type);
                    }
                    $sizeDefault = count($types);
                    if ($sizeDefault == count($this->formData[$field]))
                    {
                        array_shift($this->formData[$field]);
                    }
                    // so that the select boxes display properly when returning to the DIV
                    $deactivateResourceTypes = [];
                    foreach ($this->formData[$field] as $key => $value)
                    {
                        $deactivateResourceTypes[$key] = $value;
                    }
                    $value = base64_encode(serialize($deactivateResourceTypes));
                }
                else
                {
                    $deactivateResourceTypes = [];
                    $value = base64_encode(serialize([]));
                }
                $this->session->setVar("config_deactivateResourceTypes", $value);
            }
            elseif (WIKINDX_LIST_CONFIG_OPTIONS[$field]["type"] == 'configBoolean')
            {
                if (!array_key_exists($field, $this->formData))
                { // checkboxes not checked
                    $value = FALSE;
                }
                else
                {
                    $value = TRUE;
                }
            }
            elseif ($field != 'configDescription')
            {
                $value = \HTML\removeNl($this->formData[$field]);
            }
            elseif (($field == 'configDescription') && array_key_exists($field, $this->formData))
            {
                $value = \UTF8\mb_trim($this->formData[$field]);
            }
            if (($field == "configTagLowColour") || ($field == "configTagHighColour"))
            {
                $value = str_replace('#', '', $this->formData[$field]);
            }
            if ($field == 'configNoSort')
            {
                $array = [];
                $oldNoSort = base64_encode(serialize(WIKINDX_NO_SORT));
                if ($value)
                {
                    foreach (\UTF8\mb_explode(',', $value) as $word)
                    {
                        $word = mb_strtolower(stripslashes(\UTF8\mb_trim($word)));
                        if ($word && array_search($word, $array) === FALSE)
                        {
                            $array[] = $word;
                        }
                    }
                    $value = base64_encode(serialize($array));
                }
                if (isset($oldNoSort) && $oldNoSort != $value)
                {
                    $this->updateNoSort($oldNoSort);
                    $this->session->setVar("config_noSort", $value);
                }
            }
            elseif ($field == 'configSearchFilter')
            {
                $array = [];
                if ($value)
                {
                    foreach (\UTF8\mb_explode(',', $value) as $word)
                    {
                        $word = mb_strtolower(stripslashes(\UTF8\mb_trim($word)));
                        if ($word && array_search($word, $array) === FALSE)
                        {
                            $array[] = $word;
                        }
                    }
                    $value = base64_encode(serialize($array));
                    $this->session->setVar("config_searchFilter", $value);
                }
            }
            elseif ($field == 'configTimezone')
            {
                $timezones = DateTimeZone::listIdentifiers();
                $value = $timezones[$value];
            }
            elseif ($field == 'configLastChangesType')
            {
                $value = $this->formData['configLastChangesType'] == 1 ? 'number' : 'days'; // 2 == 'days'
            }
            if ($value || ($value === 0) || ($value === FALSE))
            {
                $updateArray[$field] = $value;
            }
            else
            {
                $nulls[] = $field;
            }
        }
        $configFields = $this->configDbStructure->getAllData();
        foreach ($updateArray as $field => $value)
        {
            if ($value === FALSE)
            {
                $value = 0;
            }
            elseif ($value === TRUE)
            {
                $value = 1;
            }
            // create database row if it doesn't exist
            if (array_key_exists($field, $configFields) === FALSE)
            {
                $this->db->insert('config', ['configName', WIKINDX_LIST_CONFIG_OPTIONS[$field]["type"]], [$field, $value]);
            }
            else
            {
                $this->db->formatConditions(['configName' => $field]);
                $this->db->update('config', [WIKINDX_LIST_CONFIG_OPTIONS[$field]["type"] => $value]);
            }
        }
        foreach ($nulls as $field)
        {
            $this->db->formatConditions(['configName' => $field]);
            $this->db->updateNull('config', WIKINDX_LIST_CONFIG_OPTIONS[$field]["type"]);
        }
        // After a change of configuration, reset the template cache
        FACTORY_TEMPLATE::getInstance()->clearAllCache();
        $selectItem = $this->vars['selectItem'];
        header("Location: index.php?action=admin_CONFIGURE_CORE&method=init&success=config&selectItem=$selectItem");
    }
    /**
     * Open popup for mail transaction report
     */
    public function mailTransactionReport()
    {
        $content = \HTML\h("Configuration test email", "", 3);
        $content .= \FORM\formHeader("admin_CONFIGURE_CORE");
        
        $content .= \FORM\hidden("method", "mailTransactionReport");

        array_key_exists("configMailTest", $this->vars) ? $input = $this->vars["configMailTest"] : $input = FALSE;
        
        $content .= \HTML\p(
            \FORM\textInput($this->messages->text("config", "mailTest"), "configMailTest", $input, 30, 255)
            . "&nbsp;" . \FORM\formSubmit($this->messages->text("submit", "Test"))
        );
        $content .= \FORM\formEnd();
        
        if (array_key_exists("configMailTest", $this->vars))
        {
            $mail = new MAIL();
            if (!$mail->sendEmail(
                $this->vars['configMailTest'],
                \HTML\stripHtml(WIKINDX_TITLE),
                $this->messages->text('config', 'mailTestSuccess')
            ))
            {
                $content .= \HTML\p("The test failed", "error", "center");
            }
            else
            {
                $content .= \HTML\p("The test passed", "success", "center");
            }
            $content .= "<pre>" . $mail->TransactionLog . "</pre>";
        }
        $close = \FORM\formHeader("admin_CONFIGURE_CORE&amp;method=init&amp;selectItem=front", "onsubmit=\"window.close();return true;\"");
        $close .= \FORM\hidden("selectItem", "email");
        $close .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Close")));
        $close .= \FORM\formEnd();
        $content .= \HTML\p($close);
        GLOBALS::addTplVar('content', $content);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * create array of config menu items
     *
     * @return array
     */
    private function getConfigGroups()
    {
        $groups = [
            'front' => $this->messages->text('config', 'front'),
            'lists' => $this->messages->text('config', 'lists'),
            'display' => $this->messages->text('config', 'display'),
            'resources' => $this->messages->text('config', 'resources'),
            'users' => $this->messages->text('config', 'users'),
            'authentication' => $this->messages->text('config', 'authentication'),
            'email' => $this->messages->text('config', 'email'),
            'file' => $this->messages->text('config', 'files'),
            'rss' => $this->messages->text('config', 'rss'),
            'cms' => $this->messages->text('config', 'cms'),
            'gs' => $this->messages->text('config', 'gs'),
            'misc' => $this->messages->text('config', 'misc'),
            'debug' => $this->messages->text('config', 'debugging'),
        ];

        return $groups;
    }
    /**
     * Get config details for menu items and put into form elements
     *
     * @param mixed $groups
     * @param mixed $item
     *
     * @return string
     */
    private function getConfigDetails($groups, $item = FALSE)
    {
        if (!$this->error)
        {
            $this->fromDbToFormdata();
        }
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
        $pString = '<script src="' . WIKINDX_URL_BASE . '/core/modules/admin/configure.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        $pString .= \FORM\formHeader("admin_CONFIGURE_CORE", "onsubmit=\"selectAll();return true;\"");
        $pString .= \FORM\hidden("method", "writeDb");
        $pString .= \FORM\hidden("selectItem", $item);
        switch ($item) {
            case 'front': // front page configuration
                $pString .= $this->frontConfigDisplay();

                break;
            case 'frontDescription': // front page description configuration
                $pString .= $this->displayFrontDescription();

                break;
            case 'resources': // resources page configuration
                $pString .= $this->resourcesConfigDisplay();

                break;
            case 'lists': // lists configuration
                $pString .= $this->listConfigDisplay();

                break;
            case 'display': // visual display configuration
                $pString .= $this->displayConfigDisplay();

                break;
            case 'debug': // debugging configuration
                $pString .= $this->debugConfigDisplay();

                break;
            case 'users': // user configuration
                $pString .= $this->usersConfigDisplay();

                break;
            case 'authentication': // user authentication configuration
                $pString .= $this->authenticationConfigDisplay();

                break;
            case 'email': // email configuration
                $pString .= $this->emailConfigDisplay();

                break;
            case 'file': // file/attachment configuration
                $pString .= $this->fileConfigDisplay();

                break;
            case 'misc': // miscellaneous configuration
                $pString .= $this->miscConfigDisplay();

                break;
            case 'impressum': // impressum configuration
                $pString .= $this->displayImpressum();

                break;
            case 'rss': // RSS configuration
                $pString .= $this->rssConfigDisplay();

                break;
            case 'cms': // CMS configuration
                $pString .= $this->cmsConfigDisplay();

                break;
            case 'gs': // Google Scholar configuration
                $pString .= $this->gsConfigDisplay();

                break;
            default:
                $pString .= '';

                break;
        }
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * Display front page options
     *
     * @return string
     */
    private function frontConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        if (array_key_exists("configLastChangesType", $this->formData) && ($this->formData["configLastChangesType"] == 'number'))
        { // set number
            $input = 1;
        }
        else
        { // Set no. days
            $input = 2;
        }
        $array = [
            1 => $this->messages->text("config", "lastChanges2"),
            2 => $this->messages->text("config", "lastChanges3"),
        ];
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "lastChanges"));
        $td = \FORM\selectedBoxValue(
            $this->messages->text("config", "lastChanges1"),
            "configLastChangesType",
            $array,
            $input,
            2,
            80
        ) . BR;
        array_key_exists("configLastChanges", $this->formData) ? $input = $this->formData["configLastChanges"] : $input = WIKINDX_LAST_CHANGES_DEFAULT;
        $td .= \HTML\span('*', 'required') . \FORM\textInput(FALSE, "configLastChanges", $input, 10, 10) . BR .
            \HTML\span($hint, 'hint');
        $pString .= \HTML\td($td);
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "lastChangesDayLimit"));
        array_key_exists("configLastChangesDayLimit", $this->formData) ? $input = $this->formData["configLastChangesDayLimit"] : $input = WIKINDX_LAST_CHANGES_DAY_LIMIT_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "lastChanges4"),
            "configLastChangesDayLimit",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "contactEmail"));
        $input = array_key_exists("configContactEmail", $this->formData) ? $this->formData["configContactEmail"] : WIKINDX_CONTACT_EMAIL_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput($this->messages->text("config", "email"), "configContactEmail", $input, 30) . BR .
            \HTML\span($hint, 'hint'));
        $pString .= \HTML\td(\HTML\a(
            '',
            $this->messages->text("config", "description"),
            "javascript:coreOpenPopup('index.php?action=admin_CONFIGURE_CORE&amp;method=displayFrontDescription" . "', 90)"
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display debugging config options
     *
     * @return string
     */
    private function debugConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \HTML\p($this->messages->text("config", "debug"));
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "forceSmartyCompile"));
        $input = array_key_exists("configBypassSmartyCompile", $this->formData) && ($this->formData['configBypassSmartyCompile']) ? "CHECKED" : WIKINDX_BYPASS_SMARTY_COMPILATION_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "forceSmartyCompile"), "configBypassSmartyCompile", $input) . BR . \HTML\span($hint, 'hint'));
        
        $input = array_key_exists("configErrorReport", $this->formData) && ($this->formData['configErrorReport']) ? "CHECKED" : WIKINDX_DEBUG_ERRORS_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "errorReport"), "configErrorReport", $input));
        
        $input = array_key_exists("configDebugSql", $this->formData) && ($this->formData['configDebugSql']) ? "CHECKED" : WIKINDX_DEBUG_SQL_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "sqlStatements"), "configDebugSql", $input));
        
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "isTrunk"));
        $input = array_key_exists("configIsTrunk", $this->formData) && ($this->formData['configIsTrunk']) ? "CHECKED" : WIKINDX_IS_TRUNK_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "isTrunk"), "configIsTrunk", $input) . BR . \HTML\span($hint, 'hint'));
        
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display user config options
     *
     * @return string
     */
    private function usersConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailServerRequired"));
        $input = array_key_exists("configMultiUser", $this->formData) && ($this->formData['configMultiUser']) ? "CHECKED" : WIKINDX_MULTIUSER_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "multiUser"), "configMultiUser", $input));
        $input = array_key_exists("configUserRegistration", $this->formData) && ($this->formData['configUserRegistration']) ? "CHECKED" : WIKINDX_USER_REGISTRATION_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "userRegistration"),
            "configUserRegistration",
            $input
        ) . BR . \HTML\span($hint, 'hint'));
        $input = array_key_exists("configUserRegistrationModerate", $this->formData) && ($this->formData['configUserRegistrationModerate']) ? "CHECKED" : WIKINDX_USER_REGISTRATION_MODERATE_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "registrationModerate"),
            "configUserRegistrationModerate",
            $input
        ) . BR . \HTML\span($hint, 'hint'));
        $input = array_key_exists("configEmailNewRegistrations", $this->formData) ? $this->formData["configEmailNewRegistrations"] : WIKINDX_EMAIL_NEW_REGISTRATIONS_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "emailNewRegistrations"),
            "configEmailNewRegistrations",
            $input,
            30
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $input = array_key_exists("configDenyReadOnly", $this->formData) && ($this->formData['configDenyReadOnly']) ? "CHECKED" : WIKINDX_DENY_READONLY_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "denyReadOnly"), "configDenyReadOnly", $input));
        $input = array_key_exists("configReadOnlyAccess", $this->formData) && ($this->formData['configReadOnlyAccess']) ? "CHECKED" : FALSE;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "readOnlyAccess"), "configReadOnlyAccess", $input));
        $input = array_key_exists("configOriginatorEditOnly", $this->formData) && ($this->formData['configOriginatorEditOnly']) ? "CHECKED" : WIKINDX_ORIGINATOR_EDIT_ONLY_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "originatorEditOnly"), "configOriginatorEditOnly", $input));
        $input = array_key_exists("configGlobalEdit", $this->formData) && ($this->formData['configGlobalEdit']) ? "CHECKED" : WIKINDX_GLOBAL_EDIT_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "globalEdit"), "configGlobalEdit", $input));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $input = array_key_exists("configImportBib", $this->formData) && ($this->formData['configImportBib']) ? "CHECKED" : WIKINDX_IMPORT_BIB_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "importBib"),
            "configImportBib",
            $input
        ));
        $input = array_key_exists("configMetadataAllow", $this->formData) && ($this->formData['configMetadataAllow']) ? "CHECKED" : FALSE;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "metadataAllow"), "configMetadataAllow", $input));
        $input = array_key_exists("configMetadataUserOnly", $this->formData) && ($this->formData['configMetadataUserOnly']) ? "CHECKED" : WIKINDX_METADATA_USERONLY_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "metadataUserOnly"), "configMetadataUserOnly", $input));
        $input = array_key_exists("configQuarantine", $this->formData) && ($this->formData['configQuarantine']) ? "CHECKED" : WIKINDX_QUARANTINE_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "quarantine"), "configQuarantine", $input));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "displayStatistics"));
        $input = array_key_exists("configDisplayStatistics", $this->formData) && ($this->formData['configDisplayStatistics']) ? "CHECKED" : WIKINDX_DISPLAY_STATISTICS_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "displayStatistics"), "configDisplayStatistics", $input) .
             BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "displayUserStatistics"));
        $input = array_key_exists("configDisplayUserStatistics", $this->formData) && ($this->formData['configDisplayUserStatistics']) ? "CHECKED" : WIKINDX_DISPLAY_USER_STATISTICS_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "displayUserStatistics"), "configDisplayUserStatistics", $input) .
             BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display resource list config options
     *
     * @return string
     */
    private function listConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        array_key_exists("configPaging", $this->formData) ? $input = $this->formData["configPaging"] : $input = WIKINDX_PAGING_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "paging"),
            "configPaging",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingMaxLinks"));
        array_key_exists("configPagingMaxLinks", $this->formData) ? $input = $this->formData["configPagingMaxLinks"] : $input = WIKINDX_PAGING_MAXLINKS_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "maxPaging"),
            "configPagingMaxLinks",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingTagCloud"));
        array_key_exists("configPagingTagCloud", $this->formData) ? $input = $this->formData["configPagingTagCloud"] : $input = WIKINDX_PAGING_TAG_CLOUD_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "pagingTagCloud"),
            "configPagingTagCloud",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $input = array_key_exists("configListLink", $this->formData) && ($this->formData['configListLink']) ? "CHECKED" : WIKINDX_LIST_LINK_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "ListLink"), "configListLink", $input));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "noSort"));
        if ($this->session->getVar("config_noSort"))
        { // After updating the field
            $input = implode(', ', unserialize(base64_decode($this->session->getVar("config_noSort"))));
            $this->session->delVar("config_noSort");
        }
        else
        {
            $input = implode(', ', WIKINDX_NO_SORT);
        }
        $pString .= \HTML\td(\HTML\p(\FORM\textareaInput(
            $this->messages->text("config", "noSort"),
            "configNoSort",
            $input,
            40,
            7
        ) . BR . \HTML\span($hint, 'hint')));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "searchFilter"));
        if ($this->session->getVar("config_searchFilter"))
        { // After updating the field
            $input = implode(', ', unserialize(base64_decode($this->session->getVar("config_searchFilter"))));
            $this->session->delVar("config_searchFilter");
        }
        else
        {
            $input = implode(', ', WIKINDX_SEARCH_FILTER);
        }
        $pString .= \HTML\td(\HTML\p(\FORM\textareaInput(
            $this->messages->text("config", "searchFilter"),
            "configSearchFilter",
            $input,
            40,
            7
        ) . BR . \HTML\span($hint, 'hint')));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display visual display config options
     *
     * @return string
     */
    private function displayConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "title"));
        array_key_exists("configTitle", $this->formData) ? $input = $this->formData["configTitle"] : $input = WIKINDX_TITLE_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput($this->messages->text("config", "title"), "configTitle", $input, 30) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "stringLimit"));
        array_key_exists("configStringLimit", $this->formData) ? $input = $this->formData["configStringLimit"] : $input = WIKINDX_STRING_LIMIT_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "stringLimit"),
            "configStringLimit",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "imgWidthLimit"));
        array_key_exists("configImgWidthLimit", $this->formData) ? $input = $this->formData["configImgWidthLimit"] : $input = WIKINDX_IMG_WIDTH_LIMIT_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "imgWidthLimit"),
            "configImgWidthLimit",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "imgHeightLimit"));
        array_key_exists("configImgHeightLimit", $this->formData) ? $input = $this->formData["configImgHeightLimit"] : $input = WIKINDX_IMG_HEIGHT_LIMIT_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "imgHeightLimit"),
            "configImgHeightLimit",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        
        // Display the global template but change the default selection of the list to the default template when no template is defined or a template not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $templates = FACTORY_TEMPLATE::getInstance()->loadDir();
        array_key_exists("configTemplate", $this->formData) ? $input = $this->formData["configTemplate"] : $input = WIKINDX_TEMPLATE_DEFAULT;
        array_key_exists($input, $templates) ? $input = $input : $input = WIKINDX_TEMPLATE_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "template"),
            "configTemplate",
            $templates,
            $input
        ));
        
        // Display the global language but change the default selection of the list to the default language when no template is defined or a language not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $languages = \LOCALES\getSystemLocales();
        array_key_exists("configLanguage", $this->formData) ? $input = $this->formData["configLanguage"] : $input = WIKINDX_LANGUAGE_DEFAULT;
        array_key_exists($input, $languages) ? $input = $input : $input = WIKINDX_LANGUAGE_DEFAULT;
        
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "language"),
            "configLanguage",
            $languages,
            $input
        ));
        
        // Display the global style but change the default selection of the list to the default style when no style is defined or a style not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $styles = \LOADSTYLE\loadDir();
        array_key_exists("configStyle", $this->formData) ? $input = $this->formData["configStyle"] : $input = WIKINDX_STYLE_DEFAULT;
        array_key_exists($input, $styles) ? $input = $input : $input = WIKINDX_STYLE_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "style"),
            "configStyle",
            $styles,
            $input,
            4
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
        $timezones = DateTimeZone::listIdentifiers();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "timezone"));
        array_key_exists("configTimezone", $this->formData) ? $input = $this->formData["configTimezone"] : $input = WIKINDX_TIMEZONE_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "timezone"),
            "configTimezone",
            $timezones,
            array_search($input, $timezones),
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "imagesAllow"));
        $input = array_key_exists("configImagesAllow", $this->formData) && ($this->formData['configImagesAllow']) ? "CHECKED" : WIKINDX_IMAGES_ALLOW_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "imagesAllow"), "configImagesAllow", $input) .
            BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "imagesMaxSize"));
        array_key_exists("configImagesMaxSize", $this->formData) ? $input = $this->formData["configImagesMaxSize"] : $input = WIKINDX_IMAGES_MAXSIZE_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "imagesMaxSize"),
            "configImagesMaxSize",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "tagCloud"));
        array_key_exists("configTagLowColour", $this->formData) ? $input = '#' . $this->formData["configTagLowColour"] :
            $input = '#' . WIKINDX_TAG_LOW_COLOUR_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\colorInput(
            $this->messages->text("config", "tagLowColour"),
            "configTagLowColour",
            $input
        ) . BR . \HTML\span($hint, 'hint'));
        array_key_exists("configTagHighColour", $this->formData) ? $input = '#' . $this->formData["configTagHighColour"] :
            $input = '#' . WIKINDX_TAG_HIGH_COLOUR_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\colorInput(
            $this->messages->text("config", "tagHighColour"),
            "configTagHighColour",
            $input
        ) . BR . \HTML\span($hint, 'hint'));
        
        // Scale factors of the tags cloud
        $tagfactors = [];
        foreach (range(WIKINDX_TAG_FACTOR_MIN, WIKINDX_TAG_FACTOR_MAX, WIKINDX_TAG_FACTOR_STEP) as $v)
        {
            $tagfactors[$v] = $v;
        }
        
        array_key_exists("configTagLowFactor", $this->formData) ? $input = $this->formData["configTagLowFactor"] : $input = WIKINDX_TAG_LOW_FACTOR_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "tagLowSize"),
            "configTagLowFactor",
            $tagfactors,
            array_search($input, $tagfactors),
            1
        ) . BR . \HTML\span($hint, 'hint'));
        
        array_key_exists("configTagHighFactor", $this->formData) ? $input = $this->formData["configTagHighFactor"] : $input = WIKINDX_TAG_HIGH_FACTOR_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "tagHighSize"),
            "configTagHighFactor",
            $tagfactors,
            array_search($input, $tagfactors),
            1
        ) . BR . \HTML\span($hint, 'hint'));
        
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display RSS config options
     *
     * @return string
     */
    private function rssConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "rssAllow"));
        $input = array_key_exists("configRssAllow", $this->formData) && ($this->formData['configRssAllow']) ? "CHECKED" : WIKINDX_RSS_ALLOW_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "rssAllow"), "configRssAllow", $input) .
            BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "rssDisplay"));
        $input = array_key_exists("configRssDisplay", $this->formData) && ($this->formData['configRssDisplay']) ? "CHECKED" : WIKINDX_RSS_DISPLAY_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "rssDisplay"), "configRssDisplay", $input) .
            BR . \HTML\span($hint, 'hint'));
        // Display the global style but change the default selection of the list to the default style when no style is defined or a style not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $styles = \LOADSTYLE\loadDir();
        array_key_exists("configRssBibstyle", $this->formData) ? $input = $this->formData["configRssBibstyle"] : $input = WIKINDX_STYLE_DEFAULT;
        array_key_exists($input, $styles) ? $input = $input : $input = WIKINDX_STYLE_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "rssBibstyle"),
            "configRssBibstyle",
            $styles,
            $input,
            5
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        array_key_exists("configRssTitle", $this->formData) ? $input = $this->formData["configRssTitle"] : $input = WIKINDX_RSS_TITLE_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "rssTitle"),
            "configRssTitle",
            $input,
            20,
            100
        ));
        array_key_exists("configRssDescription", $this->formData) ? $input = $this->formData["configRssDescription"] : $input = WIKINDX_RSS_DESCRIPTION_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "rssDescription"),
            "configRssDescription",
            $input,
            50,
            255
        ));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "rssLimit"));
        array_key_exists("configRssLimit", $this->formData) ? $input = $this->formData["configRssLimit"] : $input = WIKINDX_RSS_LIMIT_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "rssLimit"),
            "configRssLimit",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display CMS config options
     *
     * @return string
     */
    private function cmsConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "cmsAllow"));
        $input = array_key_exists("configCmsAllow", $this->formData) && ($this->formData['configCmsAllow']) ? "CHECKED" : WIKINDX_CMS_ALLOW_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "cmsAllow"), "configCmsAllow", $input) .
            BR . \HTML\span($hint, 'hint'));
        // Display the global style but change the default selection of the list to the default style when no style is defined or a style not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $styles = \LOADSTYLE\loadDir();
        array_key_exists("configCmsBibstyle", $this->formData) ? $input = $this->formData["configCmsBibstyle"] : $input = WIKINDX_STYLE_DEFAULT;
        array_key_exists($input, $styles) ? $input = $input : $input = WIKINDX_STYLE_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "cmsBibstyle"),
            "configCmsBibstyle",
            $styles,
            $input,
            5
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "cmsSql"));
        $input = array_key_exists("configCmsSql", $this->formData) && ($this->formData['configCmsSql']) ? "CHECKED" : WIKINDX_CMS_SQL_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "cmsSql"), "configCmsSql", $input) .
            BR . \HTML\span($hint, 'hint'));
        array_key_exists("configCmsDbUser", $this->formData) ? $input = $this->formData["configCmsDbUser"] : $input = WIKINDX_CMS_DB_USER_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "cmsDbUser"),
            "configCmsDbUser",
            $input,
            50,
            255
        ));
        array_key_exists("configCmsDbPassword", $this->formData) ? $input = $this->formData["configCmsDbPassword"] : $input = WIKINDX_CMS_DB_PASSWORD_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\passwordInput(
            $this->messages->text("config", "cmsDbPassword"),
            "configCmsDbPassword",
            $input,
            50,
            255
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display Google Scholar config options
     *
     * @return string
     */
    private function gsConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "gsAllow"));
        $input = array_key_exists("configGsAllow", $this->formData) && ($this->formData['configGsAllow']) ? "CHECKED" : WIKINDX_GS_ALLOW_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "gsAllow"), "configGsAllow", $input) .
            BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "gsAttachment"));
        $input = array_key_exists("configGsAttachment", $this->formData) && ($this->formData['configGsAttachment']) ? "CHECKED" : WIKINDX_GS_ATTACHMENT_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "gsAttachment"), "configGsAttachment", $input) .
            BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display user authentication config options
     *
     * @return string
     */
    private function authenticationConfigDisplay()
    {
        $isLdapExtAvailable = in_array("ldap", get_loaded_extensions());
        
        $pString = "";
        $pString .= $this->messageString;
        $pString .= \HTML\h("Built-in auth");
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        array_key_exists("configPasswordSize", $this->formData) ? $input = $this->formData["configPasswordSize"] : $input = WIKINDX_PASSWORD_SIZE_DEFAULT;
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "passwordSize"));
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "passwordSize"),
            "configPasswordSize",
            $input,
            3,
            4
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "passwordStrength"));
        $array = [
            'weak' => $this->messages->text("config", "passwordWeak"),
            'medium' => $this->messages->text("config", "passwordMedium"),
            'strong' => $this->messages->text("config", "passwordStrong"),
        ];
        array_key_exists("configPasswordStrength", $this->formData) ? $input = $this->formData["configPasswordStrength"] :
            $input = WIKINDX_PASSWORD_STRENGTH_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "passwordStrength"),
            "configPasswordStrength",
            $array,
            $input,
            3
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        
        $pString .= \HTML\h("Ldap auth");
        
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $input = array_key_exists("configLdapUse", $this->formData) && ($this->formData['configLdapUse']) ? "CHECKED" : WIKINDX_LDAP_USE_DEFAULT;
        $pString .= \HTML\td(
            \FORM\checkbox($this->messages->text("config", "LdapUse"), "configLdapUse", $input)
            . BR . \HTML\span(
                \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapUse")),
                'hint'
            )
        );
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapServer"));
        array_key_exists("configLdapServer", $this->formData) ? $input = $this->formData["configLdapServer"] : $input = WIKINDX_LDAP_SERVER_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "LdapServer"),
            "configLdapServer",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapPort"));
        array_key_exists("configLdapPort", $this->formData) ? $input = $this->formData["configLdapPort"] : $input = WIKINDX_LDAP_PORT_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "LdapPort"),
            "configLdapPort",
            $input,
            6,
            6
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapServerEncryption"));
        array_key_exists("configLdapServerEncryption", $this->formData) ? $input = $this->formData["configLdapServerEncryption"] : $input = WIKINDX_LDAP_SERVER_ENCRYPTION_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "LdapServerEncryption"),
            "configLdapServerEncryption",
            WIKINDX_LDAP_SERVER_ENCRYPTION_LIST,
            $input,
            count(WIKINDX_LDAP_SERVER_ENCRYPTION_LIST)
        ) . BR . \HTML\span($hint, 'hint'));
        
        $pString .= \HTML\tdStart();
            if (!in_array("ldap", get_loaded_extensions()))
            {
                $pString .= \HTML\p($this->messages->text("hint", "ldapExtDisabled"), "bold");
            }
            else
            {
                $jScript = "javascript:coreOpenPopup('index.php?action=admin_CONFIGURE_CORE&amp;method=ldapTester', 80)";
                $pString .= \HTML\p(\HTML\aBrowse("green", '', $this->messages->text("config", "ldapTester"), $jScript));
            }
            
            $input = array_key_exists("configLdapUserCreate", $this->formData) && ($this->formData['configLdapUserCreate']) ? "CHECKED" : WIKINDX_LDAP_USER_CREATE_DEFAULT;
            $pString .=
                \FORM\checkbox($this->messages->text("config", "LdapUserCreate"), "configLdapUserCreate", $input)
                . BR . \HTML\span(
                    \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapUserCreate")),
                    'hint'
                );
        $pString .= \HTML\tdEnd();
        $pString .= \HTML\trEnd();
        
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapServerBindType"));
        array_key_exists("configLdapServerBindType", $this->formData) ? $input = $this->formData["configLdapServerBindType"] : $input = WIKINDX_LDAP_SERVER_BIND_TYPE_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "LdapServerBindType"),
            "configLdapServerBindType",
            WIKINDX_LDAP_SERVER_BIND_TYPE_LIST,
            $input,
            count(WIKINDX_LDAP_SERVER_BIND_TYPE_LIST)
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapServerBindLogin"));
        array_key_exists("configLdapServerBindLogin", $this->formData) ? $input = $this->formData["configLdapServerBindLogin"] : $input = WIKINDX_LDAP_SERVER_BIND_LOGIN_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "LdapServerBindLogin"),
            "configLdapServerBindLogin",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapServerBindPassword"));
        array_key_exists("configLdapServerBindPassword", $this->formData) ? $input = $this->formData["configLdapServerBindPassword"] : $input = WIKINDX_LDAP_SERVER_BIND_PASSWORD_DEFAULT;
        $pString .= \HTML\td(\FORM\passwordInput(
            $this->messages->text("config", "LdapServerBindPassword"),
            "configLdapServerBindPassword",
            $input,
            20,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapServerBindDomain"));
        array_key_exists("configLdapServerBindDomain", $this->formData) ? $input = $this->formData["configLdapServerBindDomain"] : $input = WIKINDX_LDAP_SERVER_BIND_DOMAIN_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "LdapServerBindDomain"),
            "configLdapServerBindDomain",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapServerBindDomainFormat"));
        array_key_exists("configLdapServerBindDomainFormat", $this->formData) ? $input = $this->formData["configLdapServerBindDomainFormat"] : $input = WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "LdapServerBindDomainFormat"),
            "configLdapServerBindDomainFormat",
            WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT_LIST,
            $input,
            count(WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT_LIST)
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapSearchMethod"));
        array_key_exists("configLdapSearchMethod", $this->formData) ? $input = $this->formData["configLdapSearchMethod"] : $input = WIKINDX_LDAP_SEARCH_METHOD_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "LdapSearchMethod"),
            "configLdapSearchMethod",
            WIKINDX_LDAP_SEARCH_METHOD_LIST,
            $input,
            count(WIKINDX_LDAP_SEARCH_METHOD_LIST)
        ) . BR . \HTML\span($hint, 'hint'));

        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapSearchOperator"));
        array_key_exists("configLdapSearchOperator", $this->formData) ? $input = $this->formData["configLdapSearchOperator"] : $input = WIKINDX_LDAP_SEARCH_OPERATOR_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "LdapSearchOperator"),
            "configLdapSearchOperator",
            WIKINDX_LDAP_SEARCH_OPERATOR_LIST,
            $input,
            count(WIKINDX_LDAP_SEARCH_OPERATOR_LIST)
        ) . BR . \HTML\span($hint, 'hint'));


        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapUserAttributLogin"));
        array_key_exists("configLdapUserAttributLogin", $this->formData) ? $input = $this->formData["configLdapUserAttributLogin"] : $input = WIKINDX_LDAP_USER_ATTRIBUTE_LOGIN_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "LdapUserAttributLogin"),
            "configLdapUserAttributLogin",
            WIKINDX_LDAP_USER_ATTRIBUTE_LOGIN_LIST,
            $input,
            count(WIKINDX_LDAP_USER_ATTRIBUTE_LOGIN_LIST)
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapUserOu"));
        array_key_exists("configLdapUserOu", $this->formData) ? $input = $this->formData["configLdapUserOu"] : $input = WIKINDX_LDAP_USER_OU_DEFAULT;
        $pString .= \HTML\td(\FORM\textareaInput(
            $this->messages->text("config", "LdapUserOu"),
            "configLdapUserOu",
            $input,
            30,
            3
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "LdapGroupDn"));
        array_key_exists("configLdapGroupDn", $this->formData) ? $input = $this->formData["configLdapGroupDn"] : $input = WIKINDX_LDAP_GROUP_DN_DEFAULT;
        $pString .= \HTML\td(\FORM\textareaInput(
            $this->messages->text("config", "LdapGroupDn"),
            "configLdapGroupDn",
            $input,
            30,
            3
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        if ($isLdapExtAvailable)
        {
            // Extra field not in the database used for test purposes only
            /*$hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "ldapTest"));
            array_key_exists("configLdapTestUser", $this->vars) ? $input = $this->vars["configLdapTestUser"] : $input = FALSE;
            $pString .= \HTML\p(\FORM\textInput($this->messages->text("config", "ldapTestUsername"), "configLdapTestUser", $input, 30, 255));
            array_key_exists("configLdapTestPassword", $this->vars) ? $input = $this->vars["configLdapTestPassword"] : $input = FALSE;
            $pString .= \HTML\p(\FORM\passwordInput($this->messages->text("config", "ldapTestPassword"), "configLdapTestPassword", $input, 30, 255) .
                BR . \HTML\span($hint, 'hint'));*/
        }
        else
        {
            $pString .= \HTML\p($this->messages->text("hint", "ldapExtDisabled"), 'redText');
        }
        
        $pString .= \HTML\h("Other options");
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "authGate"));
        $input = array_key_exists("configAuthGate", $this->formData) && ($this->formData['configAuthGate']) ? "CHECKED" : WIKINDX_AUTHGATE_USE_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "authGate"), "configAuthGate", $input) . BR . \HTML\span($hint, 'hint'));
        array_key_exists("configAuthGateMessage", $this->formData) ? $input = $this->formData["configAuthGateMessage"] :
            $input = WIKINDX_AUTHGATE_MESSAGE_DEFAULT;
        $pString .= \HTML\td(\FORM\textareaInputmceNoEditor($this->messages->text("config", "authGateMessage"), "configAuthGateMessage", $input, 80));
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "authGateReset"), "configAuthGateReset", WIKINDX_AUTHGATE_RESET_DEFAULT));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display email config options
     *
     * @return string
     */
    private function emailConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $input = array_key_exists("configMailUse", $this->formData) && ($this->formData['configMailUse']) ? "CHECKED" : WIKINDX_MAIL_USE_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "mailServer"), "configMailUse", $input));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailFrom"));
        array_key_exists("configMailFrom", $this->formData) ? $input = $this->formData["configMailFrom"] : $input = WIKINDX_MAIL_FROM_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "mailFrom"),
            "configMailFrom",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailReplyTo"));
        array_key_exists("configMailReturnPath", $this->formData) ? $input = $this->formData["configMailReplyTo"] : $input = WIKINDX_MAIL_REPLYTO_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "mailReplyTo"),
            "configMailReplyTo",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        array_key_exists("configMailBackend", $this->formData) ? $input = $this->formData["configMailBackend"] : $input = WIKINDX_MAIL_BACKEND_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "mailBackend"),
            "configMailBackend",
            WIKINDX_PHPMAILER_BACKENDS,
            $input,
            count(WIKINDX_PHPMAILER_BACKENDS)
        ));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailReturnPath"));
        array_key_exists("configMailReturnPath", $this->formData) ? $input = $this->formData["configMailReturnPath"] : $input = WIKINDX_MAIL_RETURN_PATH_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "mailReturnPath"),
            "configMailReturnPath",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailSmPath"));
        array_key_exists("configMailSmPath", $this->formData) ? $input = $this->formData["configMailSmPath"] : $input = WIKINDX_MAIL_SENDMAIL_PATH_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "mailSmPath"),
            "configMailSmPath",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailSmtpServer"));
        array_key_exists("configMailSmtpServer", $this->formData) ? $input = $this->formData["configMailSmtpServer"] : $input = WIKINDX_MAIL_SMTP_SERVER_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "mailSmtpServer"),
            "configMailSmtpServer",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        array_key_exists("configMailSmtpPort", $this->formData) ? $input = $this->formData["configMailSmtpPort"] : $input = WIKINDX_MAIL_SMTP_PORT_DEFAULT;
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailSmtpPort"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "mailSmtpPort"),
            "configMailSmtpPort",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        array_key_exists("configMailSmtpEncrypt", $this->formData) ? $input = $this->formData["configMailSmtpEncrypt"] : $input = WIKINDX_MAIL_SMTP_ENCRYPT_DEFAULT;
        $input = (!$input) ? WIKINDX_MAIL_SMTP_ENCRYPT_DEFAULT : $input;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "mailSmtpEncrypt"),
            "configMailSmtpEncrypt",
            WIKINDX_PHPMAILER_SMTP_ENCRYPT,
            $input,
            count(WIKINDX_PHPMAILER_SMTP_ENCRYPT)
        ));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailSmtpPersist"));
        $input = array_key_exists("configMailSmtpPersist", $this->formData) && ($this->formData['configMailSmtpPersist']) ? "CHECKED" : WIKINDX_MAIL_SMTP_PERSIST_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "mailSmtpPersist"), "configMailSmtpPersist", $input) .
        BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailSmtpAuth"));
        $input = array_key_exists("configMailSmtpAuth", $this->formData) && ($this->formData['configMailSmtpAuth']) ? "CHECKED" : WIKINDX_MAIL_SMTP_AUTH_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "mailSmtpAuth"), "configMailSmtpAuth", $input) .
            BR . \HTML\span($hint, 'hint'));
        array_key_exists("configMailSmtpUsername", $this->formData) ? $input = $this->formData["configMailSmtpUsername"] : $input = WIKINDX_MAIL_SMTP_USERNAME_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "mailSmtpUsername"),
            "configMailSmtpUsername",
            $input,
            30,
            255
        ));
        array_key_exists("configMailSmtpPassword", $this->formData) ? $input = $this->formData["configMailSmtpPassword"] : $input = WIKINDX_MAIL_SMTP_PASSWORD_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\passwordInput(
            $this->messages->text("config", "mailSmtpPassword"),
            "configMailSmtpPassword",
            $input,
            30,
            255
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
        $input = array_key_exists("configNotify", $this->formData) && ($this->formData['configNotify']) ? "CHECKED" : WIKINDX_NOTIFY_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "notify"), "configNotify", $input));
        $input = array_key_exists("configEmailStatistics", $this->formData) && ($this->formData['configEmailStatistics']) ? "CHECKED" : WIKINDX_EMAIL_STATISTICS_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "statistics"), "configEmailStatistics", $input));
        $input = array_key_exists("configEmailNews", $this->formData) && ($this->formData['configEmailNews']) ? "CHECKED" : WIKINDX_EMAIL_NEWS_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "emailNews"), "configEmailNews", $input));
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        
        // Extra field not in the database used for test purposes only
        $jScript = "onclick=\"coreOpenPopup('index.php?action=admin_CONFIGURE_CORE&amp;method=mailTransactionReport&amp;configMailTest=' + document.getElementById('configMailTest').value, 80)\"";
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailTest"));
        array_key_exists("configMailTest", $this->vars) ? $input = $this->vars["configMailTest"] : $input = FALSE;
        $pString .= \HTML\p(
            \FORM\textInput($this->messages->text("config", "mailTest"), "configMailTest", $input, 30, 255)
            . "&nbsp;" . \FORM\formSubmitButton($this->messages->text("submit", "Test"), "emailtest", $jScript)
            . BR . \HTML\span($hint, 'hint')
        );

        return $pString;
    }
    /**
     * Display file/attachment config options
     *
     * @return string
     */
    private function fileConfigDisplay()
    {
        $pString = $this->messageString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "deleteSeconds"));
        array_key_exists("configFileDeleteSeconds", $this->formData) ? $input = $this->formData["configFileDeleteSeconds"] : $input = WIKINDX_FILE_DELETE_SECONDS_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "deleteSeconds"),
            "configFileDeleteSeconds",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        $input = array_key_exists("configFileAttach", $this->formData) && ($this->formData['configFileAttach']) ? "CHECKED" : WIKINDX_FILE_ATTACH_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "fileAttach"), "configFileAttach", $input));
        $input = array_key_exists("configFileViewLoggedOnOnly", $this->formData) && ($this->formData['configFileViewLoggedOnOnly']) ? "CHECKED" : WIKINDX_FILE_VIEW_LOGGEDON_ONLY_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "fileViewLoggedOnOnly"),
            "configFileViewLoggedOnOnly",
            $input
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display resources config options
     *
     * @return string
     */
    private function resourcesConfigDisplay()
    {
        $deactivated = [];
        $pString = $this->messageString;
        // need to force reload so display box of active types displays properly after DB write
        $resourceMap = FACTORY_RESOURCEMAP::getFreshInstance();
        $typesRaw = $resourceMap->getTypesRaw();
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", 'deactivateResourceTypes'));
        foreach ($typesRaw as $type)
        {
            $types[$type] = $this->messages->text("resourceType", $type);
        }
        asort($types);
        if ($this->session->getVar("config_deactivateResourceTypes"))
        { // After updating the field
            $array = unserialize(base64_decode($this->session->getVar("config_deactivateResourceTypes")));
            $this->session->delVar("config_deactivateResourceTypes");
        }
        else
        {
            $array = WIKINDX_DEACTIVATE_RESOURCE_TYPES;
        }
        foreach ($array as $type)
        {
            $deactivated[$type] = $this->messages->text("resourceType", $type);
            if (array_key_exists($type, $types))
            {
                unset($types[$type]);
            }
        }
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("config", 'activeResourceTypes'),
            'activeResourceTypes',
            $types,
            10
        ) . BR . \HTML\span($hint, 'hint'), 'padding3px left width18percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows();
        $pString .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'padding3px left width5percent');
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", 'multiples'));
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("config", 'deactivateResourceTypes'),
            'configDeactivateResourceTypes',
            $deactivated,
            10
        ) .
            BR . \HTML\span($hint, 'hint'), 'padding3px left width18percent');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display misc config options
     *
     * @return string
     */
    private function miscConfigDisplay()
    {
        $user = FACTORY_USER::getInstance();
        $users = $user->grabAll();
        unset($users[1]);
        natcasesort($users);
        $users = [0 => $this->messages->text("misc", "ignore")] + $users;
        $pString = $this->messageString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "siteMapAllow"));
        $input = array_key_exists("configSiteMapAllow", $this->formData) && ($this->formData['configSiteMapAllow']) ? "CHECKED" : WIKINDX_SITEMAP_ALLOW_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "siteMapAllow"), "configSiteMapAllow", $input) .
            BR . \HTML\span($hint, 'hint'));
        
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "browserTabID"));
        $input = array_key_exists("configBrowserTabID", $this->formData) && ($this->formData['configBrowserTabID']) ? "CHECKED" : WIKINDX_SITEMAP_ALLOW_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "browserTabID"), "configBrowserTabID", $input) .
            BR . \HTML\span($hint, 'hint'));
        
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "restrictUserId"));
        array_key_exists("configRestrictUserId", $this->formData) ? $input = $this->formData["configRestrictUserId"] : WIKINDX_RESTRICT_USERID_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "restrictUserId"),
            "configRestrictUserId",
            $users,
            $input,
            1
        ) . BR . \HTML\span($hint, 'hint'));
        
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "maxPaste"));
        $input = array_key_exists("configMaxPaste", $this->formData) ? $this->formData["configMaxPaste"] : WIKINDX_MAX_PASTE_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text('config', 'maxPaste'),
            "configMaxPaste",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "urlPrefix"));
        $input = array_key_exists("configResourceUrlPrefix", $this->formData) ?
            $this->formData["configResourceUrlPrefix"] : WIKINDX_MAX_PASTE_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text('config', 'urlPrefix'),
            "configResourceUrlPrefix",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "impressum"));
        $pString .= \HTML\td(\HTML\a(
            '',
            $this->messages->text("config", "impressum"),
            "javascript:coreOpenPopup('index.php?action=admin_CONFIGURE_CORE&amp;method=displayImpressum" . "', 90)"
        ) . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Get possible input vars on form submission
     *
     * @return array
     */
    private function getPossibleVars()
    {
        switch ($this->vars['selectItem']) {
            case 'front': // front page configuration
                $array = [
                    "configContactEmail",
                    "configLastChanges",
                    "configLastChangesDayLimit",
                    "configLastChangesType",
                ];

                break;
            case 'frontDescription': // front page configuration
                $array = [
                    "configDescription",
                ];
                $this->session->setVar("configmessage", $this->success->text("config"));

                break;
            case 'resources': // resources page configuration
                $array = [
                    "configDeactivateResourceTypes",
                ];

                break;
            case 'lists': // lists configuration
                $array = [
                    "configListLink",
                    "configNoSort",
                    "configPaging",
                    "configPagingMaxLinks",
                    "configPagingTagCloud",
                    "configSearchFilter",
                ];

                break;
            case 'display': // visual display configuration
                $array = [
                    "configImagesAllow",
                    "configImagesMaxSize",
                    "configImgHeightLimit",
                    "configImgWidthLimit",
                    "configLanguage",
                    "configStringLimit",
                    "configStyle",
                    "configTagHighColour",
                    "configTagHighFactor",
                    "configTagLowColour",
                    "configTagLowFactor",
                    "configTemplate",
                    "configTimezone",
                    "configTitle",
                ];

                break;
            case 'debug': // debugging configuration
                $array = [
                    "configBypassSmartyCompile",
                    "configDebugSql",
                    "configErrorReport",
                    "configIsTrunk",
                ];

                break;
            case 'users': // user configuration
                $array = [
                    "configDenyReadOnly",
                    "configDisplayStatistics",
                    "configDisplayUserStatistics",
                    "configEmailNewRegistrations",
                    "configGlobalEdit",
                    "configImportBib",
                    "configMetadataAllow",
                    "configMetadataUserOnly",
                    "configMultiUser",
                    "configOriginatorEditOnly",
                    "configQuarantine",
                    "configReadOnlyAccess",
                    "configUserRegistration",
                    "configUserRegistrationModerate",
                ];

                break;
            case 'authentication': // user authentication configuration
                $array = [
                    "configAuthGate",
                    "configAuthGateMessage",
                    "configLdapGroupDn",
                    "configLdapPort",
                    "configLdapSearchMethod",
                    "configLdapSearchOperator",
                    "configLdapServer",
                    "configLdapServerBindDomain",
                    "configLdapServerBindDomainFormat",
                    "configLdapServerBindLogin",
                    "configLdapServerBindPassword",
                    "configLdapServerBindType",
                    "configLdapServerEncryption",
                    "configLdapUse",
                    "configLdapUserAttributLogin",
                    "configLdapUserCreate",
                    "configLdapUserOu",
                    "configPasswordSize",
                    "configPasswordStrength",
                ];

                break;
            case 'email': // email configuration
                $array = [
                    "configEmailNews",
                    "configEmailStatistics",
                    "configMailBackend",
                    "configMailFrom",
                    "configMailReplyTo",
                    "configMailReturnPath",
                    "configMailSmPath",
                    "configMailSmtpAuth",
                    "configMailSmtpEncrypt",
                    "configMailSmtpPassword",
                    "configMailSmtpPersist",
                    "configMailSmtpPort",
                    "configMailSmtpServer",
                    "configMailSmtpUsername",
                    "configMailUse",
                    "configNotify",
                ];

                break;
            case 'file': // file/attachment configuration
                $array = [
                    "configFileAttach",
                    "configFileDeleteSeconds",
                    "configFileViewLoggedOnOnly",
                ];

                break;
            case 'misc': // miscellaneous configuration
                $array = [
                    "configMaxPaste",
                    "configRestrictUserId",
                    "configSiteMapAllow",
                    "configResourceUrlPrefix",
                    "configBrowserTabID",
                ];

                break;
            case 'impressum': // impressum configuration
                $array = [
                    "configImpressum",
                ];
                $this->session->setVar("configmessage", $this->success->text("config"));

                break;
            case 'rss': // RSS configuration
                $array = [
                    "configRssAllow",
                    "configRssBibstyle",
                    "configRssDescription",
                    "configRssDisplay",
                    "configRssLimit",
                    "configRssTitle",
                ];

                break;
            case 'cms': // CMS configuration
                $array = [
                    "configCmsAllow",
                    "configCmsBibstyle",
                    "configCmsDbPassword",
                    "configCmsDbUser",
                    "configCmsSql",
                ];

                break;
            case 'gs': // Google Scholar configuration
                $array = [
                    "configGsAllow",
                    "configGsAttachment",
                ];

                break;
            default:
                $array = [];

                break;
        }

        return $array;
    }
    /**
     * If the NoSort array has changed, need to update the database
     *
     * @param mixed $oldNoSort
     */
    private function updateNoSort($oldNoSort)
    {
        $oldArray = unserialize(base64_decode($oldNoSort));
        $arrayNewNoSort = array_diff(WIKINDX_NO_SORT, $oldArray);
        $arrayRemoveNoSort = array_diff($oldArray, WIKINDX_NO_SORT);
        $newNSPattern = $newNSPatternBrace = FALSE;
        if (!empty($arrayNewNoSort))
        {
            foreach ($arrayNewNoSort as $word)
            {
                $array[] = "^($word)\\s(.*)";
                $arrayBrace[] = "^\\{($word)\\s(.*)";
            }
            $newNSPattern = implode('|', $array);
            $newNSPatternBrace = implode('|', $arrayBrace);
        }
        $resultset = $this->db->select('resource', ['resourceId', 'resourceTitle', 'resourceSubtitle', 'resourceNoSort', 'resourceTitleSort']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $update = [];
            if ($newNSPattern && preg_match("/$newNSPattern/ui", $row['resourceTitle'], $matches))
            {
                $update['resourceTitle'] = $resourceTitleSort = array_pop($matches);
                $update['resourceNoSort'] = array_pop($matches);
                if ($row['resourceSubtitle'])
                {
                    $resourceTitleSort .= ' ' . $row['resourceSubtitle'];
                }
                $update['resourceTitleSort'] = str_replace(['{', '}'], '', $resourceTitleSort);
            }
            elseif ($newNSPatternBrace && preg_match("/$newNSPatternBrace/ui", $row['resourceTitle'], $matches))
            {
                $resourceTitleSort = array_pop($matches);
                $update['resourceTitle'] = '{' . $resourceTitleSort;
                $update['resourceNoSort'] = array_pop($matches);
                if ($row['resourceSubtitle'])
                {
                    $resourceTitleSort .= ' ' . $row['resourceSubtitle'];
                }
                $update['resourceTitleSort'] = str_replace(['{', '}'], '', $resourceTitleSort);
            }
            elseif (array_search(mb_strtolower($row['resourceNoSort']), $arrayRemoveNoSort) !== FALSE)
            {
                $update['resourceTitle'] = $row['resourceNoSort'] . ' ' . $row['resourceTitle'];
                $update['resourceTitleSort'] = $row['resourceNoSort'] . ' ' . $row['resourceTitleSort'];
                $this->db->formatConditions(['resourceId' => $row['resourceId']]);
                $this->db->updateNull('resource', ['resourceNoSort']);
            }
            if (!empty($update))
            {
                $this->db->formatConditions(['resourceId' => $row['resourceId']]);
                $this->db->update('resource', $update);
            }
        }
    }
    /**
     * Check input and store user input in $this->formData
     */
    private function checkInput()
    {
        $error = '';
        // Check for special fields and carry out actions as necessary
        // 1. configAuthGateReset – reset all 'usersAuthGate' fields back to 'N'
        if (array_key_exists('configAuthGateReset', $this->vars) && $this->vars['configAuthGateReset'])
        {
            $this->db->update('users', ['usersGDPR' => 'N']);
        }
        foreach (WIKINDX_LIST_CONFIG_OPTIONS as $key => $unused)
        {
            if (array_key_exists($key, $this->vars))
            {
                if (($key == 'configLastChanges') || ($key == 'configPaging') || ($key == 'configStringLimit') || ($key == 'configPagingTagCloud'))
                {
                    if ($this->vars[$key] < 0)
                    {
                        $this->vars[$key] = -1; // force
                    }
                }
                $this->formData[$key] = $this->vars[$key];
            }
        }
        $requireDefault = [
            "configFileDeleteSeconds" => WIKINDX_FILE_DELETE_SECONDS_DEFAULT,
            "configImagesMaxSize" => WIKINDX_IMAGES_MAXSIZE_DEFAULT,
            "configImgHeightLimit" => WIKINDX_IMG_HEIGHT_LIMIT_DEFAULT,
            "configImgWidthLimit" => WIKINDX_IMG_WIDTH_LIMIT_DEFAULT,
            "configLastChanges" => WIKINDX_LAST_CHANGES_DEFAULT,
            "configLastChangesDayLimit" => WIKINDX_LAST_CHANGES_DAY_LIMIT_DEFAULT,
            "configMaxPaste" => WIKINDX_MAX_PASTE_DEFAULT,
            "configPaging" => WIKINDX_PAGING_DEFAULT,
            "configPagingMaxLinks" => WIKINDX_PAGING_MAXLINKS_DEFAULT,
            "configPagingTagCloud" => WIKINDX_PAGING_TAG_CLOUD_DEFAULT,
            "configPasswordSize" => WIKINDX_PASSWORD_SIZE_DEFAULT,
            "configStringLimit" => WIKINDX_STRING_LIMIT_DEFAULT,
            "configTagHighFactor" => WIKINDX_TAG_HIGH_FACTOR_DEFAULT,
            "configTagLowFactor" => WIKINDX_TAG_LOW_FACTOR_DEFAULT,
            "configTitle" => WIKINDX_TITLE_DEFAULT,
        ];
        foreach ($requireDefault as $value => $default)
        {
            if (array_key_exists($value, $this->vars) && !$this->vars[$value])
            {
            	if (($value == 'configMaxPaste') && ($this->vars[$value] === '0')) {
            		continue;
            	}
                $this->formData[$value] = $default;
            }
        }
        // strings that are required
        $required = [
            "configLanguage",
            "configLastChangesType",
            "configStyle",
            "configTagHighColour",
            "configTagLowColour",
            "configTemplate",
            "configTimezone",
        ];
        foreach ($required as $value)
        {
            if (array_key_exists($value, $this->vars))
            {
                $input = \UTF8\mb_trim($this->vars[$value]);
                if (!$input)
                {
                    $error = $this->errors->text("inputError", 'missing', " ($value) ");
                }
            }
        }
        if (array_key_exists('password', $this->vars) && ($this->vars['password'] != $this->vars['passwordConfirm']))
        {
            $error = $this->errors->text("inputError", 'passwordMismatch');
        }
        $isInt = [
            "configFileDeleteSeconds",
            "configImagesMaxSize",
            "configImgHeightLimit",
            "configImgWidthLimit",
            "configLastChanges",
            "configLastChangesDayLimit",
            "configLdapPort",
            "configMailSmtpPort",
            "configMaxPaste",
            "configPaging",
            "configPagingMaxLinks",
            "configPagingTagCloud",
            "configPasswordSize",
            "configRestrictUserId",
            "configRssLimit",
            "configStringLimit",
            "configTagHighColour",
            "configTagHighFactor",
            "configTagLowColour",
            "configTagLowFactor",
        ];
        $notNegative = [
            "configFileDeleteSeconds",
            "configImagesMaxSize",
            "configImgHeightLimit",
            "configImgWidthLimit",
            "configLdapPort",
            "configLowSize",
            "configMailSmtpPort",
            "configMaxPaste",
            "configPagingMaxLinks",
            "configPasswordSize",
            "configRestrictUserId",
            "configRssLimit",
            "configTagHighFactor",
        ];
        foreach ($isInt as $value)
        {
            if (array_key_exists($value, $this->formData))
            {
                $input = trim($this->formData[$value]);
            }
            else
            {
                continue;
            }
            if (($value == "configTagLowColour") || ($value == "configTagHighColour"))
            {
                $input = hexdec(ltrim($input, '#'));
            }
            // some values cannot be less than 0
            if ((array_search($value, $notNegative) !== FALSE) && ((int)$input < 0))
            {
                $error = $this->errors->text("inputError", 'invalid', " ($value) ");
            }
            // these can be blank, otherwise must be an int
            if (($value == 'configRestrictUserId') || ($value == 'configImagesMaxSize') || ($value == 'configMailSmtpPort'))
            {
                if ($input == '')
                {
                    continue;
                }
                if (!is_numeric($input) || !is_int($input + 0))
                { // cast to number
                    $error = $this->errors->text("inputError", 'notInt', " ($value) ");
                }
            }
            if (!is_numeric($input) || !is_int($input + 0))
            { // cast to number
                $error = $this->errors->text("inputError", 'notInt', " ($value) ");
            }
        }
        // Dependencies
        $this->dependencies('configRssAllow', ['configRssBibstyle', 'configRssTitle', 'configRssDescription', 'configRssLimit']);
        $this->dependencies('configCmsAllow', ['configCmsBibstyle']);
        $this->dependencies('configCmsSql', ['configCmsDbUser', 'configCmsDbPassword']);
        $this->dependencies('configMailUse', ['configMailBackend']);
        $this->dependencies('configLdapUse', ['configLdapServer', 'configLdapPort', 'configLdapServerEncryption', 'configLdapServerBindType', 'configLdapSearchMethod', 'configLdapUserAttributLogin']);
        $this->dependencies('configAuthGate', ['configAuthGateMessage']);
        $this->dependencies('configUserRegistrationModerate', ['configEmailNewRegistrations']);
        if (array_key_exists('configMailUse', $this->formData) && ($this->formData['configMailBackend'] == 'sendmail'))
        {
            $this->dependencies('configMailUse', ['configMailSmPath']);
        }
        elseif (array_key_exists('configMailUse', $this->formData) && ($this->formData['configMailBackend'] == 'smtp'))
        {
            $this->dependencies('configMailUse', ['configMailSmtpServer', 'configMailSmtpPort', 'configMailSmtpEncrypt']);
            $this->dependencies('configMailSmtpAuth', ['configMailSmtpUsername', 'configMailSmtpPassword']);
        }
        // Check size of password is no less than N chars and force it if necessary
        if (array_key_exists('configPasswordSize', $this->formData) && ($this->formData['configPasswordSize'] < WIKINDX_PASSWORD_SIZE_DEFAULT))
        {
            $this->formData['configPasswordSize'] = WIKINDX_PASSWORD_SIZE_DEFAULT;
        }
        // Check email validity
        if (array_key_exists('configEmailNewRegistrations', $this->formData))
        {
            if (filter_var($this->formData['configEmailNewRegistrations'], FILTER_VALIDATE_EMAIL) === FALSE && $this->formData['configEmailNewRegistrations'] != "")
            {
                $error = $this->errors->text('inputError', 'invalidMail');
            }
        }
        if ($error)
        {
            $this->badInputLoad($error, $this->vars['selectItem']);
        }
    }
    /**
     * Check field dependencies – parent is checkbox, childArray of textboxes or selectboxes that must be completed
     *
     * @param string $parent
     * @param array $childArray
     */
    private function dependencies($parent, $childArray)
    {
        if (array_key_exists($parent, $this->formData))
        {
            foreach ($childArray as $value)
            {
                if (array_key_exists($value, $this->formData))
                {
                    $input = \UTF8\mb_trim($this->formData[$value]);
                }
                else
                {
                    $this->badInputLoad($this->errors->text("inputError", 'missing', " ($value) "), $this->vars['selectItem']);
                }
                if (!$input)
                {
                    $this->badInputLoad($this->errors->text("inputError", 'missing', " ($value) "), $this->vars['selectItem']);
                }
            }
        }
    }
    /**
     * fromDbToFormdata
     *
     * Grab config variables from db table and write to $this->formData.
     *
     * @return array
     */
    private function fromDbToFormdata()
    {
        $this->formData = $this->configDbStructure->getAllData();
        // Remove noSort and searchFilter and write the rest to session variables
        unset($this->formData['configNoSort']);
        unset($this->formData['configSearchFilter']);
        // deal with checkboxes
        foreach ($this->formData as $field => $value)
        {
            if ((WIKINDX_LIST_CONFIG_OPTIONS[$field]["type"] == 'configBoolean') && !$value)
            {
                unset($this->formData[$field]);
            }
        }
        // 'lastChanges' can be 0 so may not 'exist'
        if (!array_key_exists('configLastChanges', $this->formData) || !$this->formData['configLastChanges'])
        {
            $this->formData['configLastChanges'] = 0;
        }
        // 'lastChangesDayLimit' can be 0 so may not 'exist'
        if (!array_key_exists('configLastChangesDayLimit', $this->formData) || !$this->formData['configLastChangesDayLimit'])
        {
            $this->formData['configLastChangesDayLimit'] = 0;
        }
        // tidy up the $row elements for presentation to the browser.
        $tidy = [];
        foreach ($this->formData as $key => $value)
        {
            if (!is_array($value))
            {
                $this->formData[$key] = \HTML\dbToFormTidy($value);
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
        $this->error = TRUE;
        if ($item)
        {
            $this->badInput->close($error, $this, ['init', $item]);
        }
        else
        {
            $this->badInput->close($error, $this, 'init');
        }
    }
}
