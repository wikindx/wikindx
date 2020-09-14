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
    public $insert = FALSE;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $user;
    private $db;
    private $vars;
    private $values;
    private $badInput;
    private $gatekeep;
    private $tinymce;
    private $configDbStructure;
    private $errorString = FALSE;

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
        if (!$initial) {
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
        // Anything in the session takes precedence
        if (($messageIn = $this->session->getVar("configmessage")) && ($itemIn = $this->session->getVar("configitem"))) {
            $messageString = $messageIn;
            $item = $itemIn;
        	$this->session->delVar("configmessage");
        	$this->session->delVar("configitem");
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
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "help", "HELPMESSAGES.php"]));
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('configure'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "configure"));
        if (!$this->insert) {
            $jsonArray = [];
            $jScript = 'index.php?action=admin_CONFIGURE_CORE&method=initConfigDiv';
            $jsonArray[] = [
                'startFunction' => 'triggerFromMultiSelect',
                'script' => "$jScript",
                'triggerField' => 'configMenu',
                'targetDiv' => 'configDiv',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
        }
        $pString = \HTML\tableStart('');
        $pString .= \HTML\trStart();
        if (!$this->insert) {
            if ($item) {
                $pString .= \HTML\td(\FORM\selectedBoxValue($this->messages->text(
                    'config',
                    'options'
                ), 'configMenu', $configGroups, $item, sizeof($configGroups), FALSE, $js));
            } else {
                $pString .= \HTML\td(\FORM\selectFBoxValue($this->messages->text(
                    'config',
                    'options'
                ), 'configMenu', $configGroups, sizeof($configGroups), FALSE, $js));
            }
            $pString .= \HTML\td(\HTML\div('configDiv', $this->getConfigDetails($configGroups, $item)), 'left top width80percent');
        } else {
            $pString .= \HTML\td($this->getConfigDetails($configGroups, $item));
        }
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
        $this->values = $this->fromDbToSession();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "configure"));
        $pString = $this->session->getVar("configmessage");
        $this->session->delVar("configmessage");
        $pString .= $this->tinymce->loadMinimalTextarea(['configDescription'], TRUE);
        $pString .= \FORM\formHeader("admin_CONFIGURE_CORE", "onsubmit=\"selectAll();return true;\"");
        $pString .= \FORM\hidden("method", "writeDb");
        $pString .= \FORM\hidden("selectItem", 'frontDescription');
        $input = array_key_exists("configDescription", $this->values) ? $this->values["configDescription"] : WIKINDX_DESCRIPTION_DEFAULT;
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
     * Open popup for ldap transaction report
     */
    public function ldapTransactionReport()
    {
        $pString = $this->session->getVar("ldapTransactionLog");
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
        if (!$this->insert) {
            $updateArray = $nulls = [];
            $possibleVars = $this->getPossibleVars();
            foreach ($possibleVars as $field) {
                if ($field == 'configDeactivateResourceTypes') {
                    if (array_key_exists('configDeactivateResourceTypes', $this->vars)) {
                        // Ensure we always have at least one resource . . .
                        $resourceMap = FACTORY_RESOURCEMAP::getInstance();
                        $typesRaw = $resourceMap->getTypesRaw();
                        foreach ($typesRaw as $type) {
                            $types[$type] = $this->messages->text("resourceType", $type);
                        }
                        $sizeDefault = count($types);
                        if ($sizeDefault == count($this->vars[$field])) {
                            array_shift($this->vars[$field]);
                        }
                        // so that the select boxes display properly when returning to the DIV
                        $deactivateResourceTypes = [];
                        foreach ($this->vars[$field] as $key => $value) {
                            $deactivateResourceTypes[$key] = $value;
                        }
                        $value = base64_encode(serialize($deactivateResourceTypes));
                    } else {
                        $deactivateResourceTypes = [];
                        $value = base64_encode(serialize([]));
                    }
                    $this->session->setVar("config_deactivateResourceTypes", $value);
                } elseif (WIKINDX_LIST_CONFIG_OPTIONS[$field]["type"] == 'configBoolean') {
                    if (!array_key_exists($field, $this->vars)) { // checkboxes not checked
                        $value = FALSE;
                    } else {
                        $value = TRUE;
                    }
                } elseif ($field != 'configDescription') {
                    $value = \HTML\removeNl($this->vars[$field]);
                } elseif (($field == 'configDescription') && array_key_exists($field, $this->vars)) {
                    $value = trim($this->vars[$field]);
                }
                if (($field == "configTagLowColour") || ($field == "configTagHighColour")) {
                    $value = str_replace('#', '', $this->vars[$field]);
                }
                if ($field == 'configNoSort') {
                    $array = [];
                    $oldNoSort = base64_encode(serialize(WIKINDX_NO_SORT));
                    if ($value) {
                        foreach (UTF8::mb_explode(',', $value) as $word) {
                            $word = mb_strtolower(stripslashes(trim($word)));
                            if ($word && array_search($word, $array) === FALSE) {
                                $array[] = $word;
                            }
                        }
                        $value = base64_encode(serialize($array));
                    }
                    if (isset($oldNoSort) && $oldNoSort != $value) {
                        $this->updateNoSort($oldNoSort);
                        $this->session->setVar("config_noSort", $value);
                    }
                } elseif ($field == 'configSearchFilter') {
                    $array = [];
                    if ($value) {
                        foreach (UTF8::mb_explode(',', $value) as $word) {
                            $word = mb_strtolower(stripslashes(trim($word)));
                            if ($word && array_search($word, $array) === FALSE) {
                                $array[] = $word;
                            }
                        }
                        $value = base64_encode(serialize($array));
                        $this->session->setVar("config_searchFilter", $value);
                    }
                } elseif ($field == 'configTimezone') {
                    $timezones = DateTimeZone::listIdentifiers();
                    $value = $timezones[$value];
                } elseif ($field == 'configLastChangesType') {
                    $value = $this->vars['configLastChangesType'] == 1 ? 'number' : 'days'; // 2 == 'days'
                }
                if ($value || ($value === 0) || ($value === FALSE)) {
                    $updateArray[$field] = $value;
                } else {
                    $nulls[] = $field;
                }
            }
        }
        $headerRedirect = FALSE;
        if ($this->insert) {
            $this->insert = FALSE;
        } else {
            $configFields = $this->configDbStructure->getAllData();
            foreach ($updateArray as $field => $value) {
                if ($field == 'configDebugSql') { // debugging configuration – see header() below
                    $headerRedirect = TRUE;
                }
                if ($value === FALSE) {
                    $value = 0;
                } elseif ($value === TRUE) {
                    $value = 1;
                }
                // create database row if it doesn't exist
                if (array_key_exists($field, $configFields) === FALSE) {
                    $this->db->insert('config', ['configName', WIKINDX_LIST_CONFIG_OPTIONS[$field]["type"]], [$field, $value]);
                } else {
                    $this->db->formatConditions(['configName' => $field]);
                    $this->db->update('config', [WIKINDX_LIST_CONFIG_OPTIONS[$field]["type"] => $value]);
                }
            }
            foreach ($nulls as $field) {
                $this->db->formatConditions(['configName' => $field]);
                $this->db->updateNull('config', WIKINDX_LIST_CONFIG_OPTIONS[$field]["type"]);
            }
        }
        // need to use header() to ensure any change in appearance is immediately picked up.
        if ($headerRedirect) {
            $this->session->setVar("configmessage", $this->success->text("config"));
            $this->session->setVar("configitem", $this->vars['selectItem']);
            header("Location: index.php?action=admin_CONFIGURE_CORE&method=init");
        } else {
            $this->init([$this->success->text("config"), $this->vars['selectItem']]);
        }
        // After a change of configuration, reset the template cache
        FACTORY_TEMPLATE::getInstance()->clearAllCache();
    }
    /**
     * Open popup for mail transaction report
     */
    public function mailTransactionReport()
    {
        $content = \HTML\h("Configuration test email", "", 3);
        $content .= \FORM\formHeader("admin_CONFIGURE_CORE");
        
        $content .= \FORM\hidden("method", "mailTransactionReport");
        
        $content .= \HTML\p($this->messages->text("hint", "mailTest"));
        array_key_exists("configMailTest", $this->vars) ? $input = $this->vars["configMailTest"] : $input = FALSE;
        
        $content .= \HTML\p(
            \FORM\textInput($this->messages->text("config", "mailTest"), "configMailTest", $input, 30, 255)
            . "&nbsp;" . \FORM\formSubmit($this->messages->text("submit", "Test"))
        );
        $content .= \FORM\formEnd();
        
        if (array_key_exists("configMailTest", $this->vars)) {
            $mail = new MAIL();
            if (!$mail->sendEmail($this->vars['configMailTest'], \HTML\stripHtml(WIKINDX_TITLE), $this->messages->text('config', 'mailTestSuccess'))) {
                $content .= \HTML\p("The test fails", "error", "center");
            } else {
                $content .= \HTML\p("The test passed", "success", "center");
            }
            $content .= "<pre>" . $mail->TransactionLog . "</pre>";
        }
        
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
        if (!$this->insert) {
            $this->values = $this->fromDbToSession();
        } else { // initial install
            $this->values = $this->session->getArray("config");
        }
        if (array_key_exists('ajaxReturn', $this->vars)) {
            $item = $this->vars['ajaxReturn'];
        } elseif (!$item) { // grab the first of the list
            foreach ($groups as $item => $null) {
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
        $pString = $this->errorString;
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        if (array_key_exists("configLastChangesType", $this->values) && ($this->values["configLastChangesType"] == 'number')) { // set number
            $input = 1;
        } else { // Set no. days
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
        array_key_exists("configLastChanges", $this->values) ? $input = $this->values["configLastChanges"] : $input = WIKINDX_LAST_CHANGES_DEFAULT;
        $td .= \FORM\textInput(FALSE, "configLastChanges", $input, 10, 10) . \HTML\span('*', 'required') . BR .
            \HTML\span($hint, 'hint');
        $pString .= \HTML\td($td);
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "lastChangesDayLimit"));
        array_key_exists("configLastChangesDayLimit", $this->values) ? $input = $this->values["configLastChangesDayLimit"] : $input = WIKINDX_LAST_CHANGES_DAY_LIMIT_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "lastChanges4"),
            "configLastChangesDayLimit",
            $input,
            10,
            10
        ) . \HTML\span('*', 'required') . BR .
            \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "contactEmail"));
        $input = array_key_exists("configContactEmail", $this->values) ? $this->values["configContactEmail"] : WIKINDX_CONTACT_EMAIL_DEFAULT;
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
        $pString = $this->errorString;
        $pString .= \HTML\p($this->messages->text("config", "debug"));
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "forceSmartyCompile"));
        $input = array_key_exists("configBypassSmartyCompile", $this->values) && ($this->values['configBypassSmartyCompile']) ? "CHECKED" : WIKINDX_BYPASS_SMARTY_COMPILATION_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "forceSmartyCompile"), "configBypassSmartyCompile", $input) . BR . \HTML\span($hint, 'hint'));
        
        $input = array_key_exists("configErrorReport", $this->values) && ($this->values['configErrorReport']) ? "CHECKED" : WIKINDX_DEBUG_ERRORS_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "errorReport"), "configErrorReport", $input));
        
        $input = array_key_exists("configDebugSql", $this->values) && ($this->values['configDebugSql']) ? "CHECKED" : WIKINDX_DEBUG_SQL_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "sqlStatements"), "configDebugSql", $input));
        
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "isTrunk"));
        $input = array_key_exists("configIsTrunk", $this->values) && ($this->values['configIsTrunk']) ? "CHECKED" : WIKINDX_IS_TRUNK_DEFAULT;
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
        $pString = $this->errorString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailServerRequired"));
        $input = array_key_exists("configMultiUser", $this->values) && ($this->values['configMultiUser']) ? "CHECKED" : WIKINDX_MULTIUSER_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "multiUser"), "configMultiUser", $input));
        $input = array_key_exists("configUserRegistration", $this->values) && ($this->values['configUserRegistration']) ? "CHECKED" : WIKINDX_USER_REGISTRATION_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "userRegistration"),
            "configUserRegistration",
            $input
        ) . BR . \HTML\span($hint, 'hint'));
        $input = array_key_exists("configUserRegistrationModerate", $this->values) && ($this->values['configUserRegistrationModerate']) ? "CHECKED" : WIKINDX_USER_REGISTRATION_MODERATE_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "registrationModerate"),
            "configUserRegistrationModerate",
            $input
        ) . BR . \HTML\span($hint, 'hint'));
        $input = array_key_exists("configEmailNewRegistrations", $this->values) ? $this->values["configEmailNewRegistrations"] : WIKINDX_EMAIL_NEW_REGISTRATIONS_DEFAULT;
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
        $input = array_key_exists("configDenyReadOnly", $this->values) && ($this->values['configDenyReadOnly']) ? "CHECKED" : WIKINDX_DENY_READONLY_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "denyReadOnly"), "configDenyReadOnly", $input));
        if ($this->insert) {
            $input = "CHECKED"; // NB checked by default
        } else {
            $input = array_key_exists("configReadOnlyAccess", $this->values) && ($this->values['configReadOnlyAccess']) ? "CHECKED" : FALSE;
        }
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "readOnlyAccess"), "configReadOnlyAccess", $input));
        $input = array_key_exists("configOriginatorEditOnly", $this->values) && ($this->values['configOriginatorEditOnly']) ? "CHECKED" : WIKINDX_ORIGINATOR_EDIT_ONLY_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "originatorEditOnly"), "configOriginatorEditOnly", $input));
        $input = array_key_exists("configGlobalEdit", $this->values) && ($this->values['configGlobalEdit']) ? "CHECKED" : WIKINDX_GLOBAL_EDIT_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "globalEdit"), "configGlobalEdit", $input));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $input = array_key_exists("configImportBib", $this->values) && ($this->values['configImportBib']) ? "CHECKED" : WIKINDX_IMPORT_BIB_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox(
            $this->messages->text("config", "importBib"),
            "configImportBib",
            $input
        ));
        if ($this->insert) {
            $input = "CHECKED"; // NB checked by default
        } else {
            $input = array_key_exists("configMetadataAllow", $this->values) && ($this->values['configMetadataAllow']) ? "CHECKED" : FALSE;
        }
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "metadataAllow"), "configMetadataAllow", $input));
        $input = array_key_exists("configMetadataUserOnly", $this->values) && ($this->values['configMetadataUserOnly']) ? "CHECKED" : WIKINDX_METADATA_USERONLY_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "metadataUserOnly"), "configMetadataUserOnly", $input));
        $input = array_key_exists("configQuarantine", $this->values) && ($this->values['configQuarantine']) ? "CHECKED" : WIKINDX_QUARANTINE_DEFAULT;
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
        $input = array_key_exists("configDisplayStatistics", $this->values) && ($this->values['configDisplayStatistics']) ? "CHECKED" : WIKINDX_DISPLAY_STATISTICS_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "displayStatistics"), "configDisplayStatistics", $input) .
             BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "displayUserStatistics"));
        $input = array_key_exists("configDisplayUserStatistics", $this->values) && ($this->values['configDisplayUserStatistics']) ? "CHECKED" : WIKINDX_DISPLAY_USER_STATISTICS_DEFAULT;
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
        $pString = $this->errorString;
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        array_key_exists("configPaging", $this->values) ? $input = $this->values["configPaging"] : $input = WIKINDX_PAGING_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput($this->messages->text("config", "paging"), "configPaging", $input, 10, 10) .
            " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingMaxLinks"));
        array_key_exists("configPagingMaxLinks", $this->values) ? $input = $this->values["configPagingMaxLinks"] : $input = WIKINDX_PAGING_MAXLINKS_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "maxPaging"),
            "configPagingMaxLinks",
            $input,
            10,
            10
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingTagCloud"));
        array_key_exists("configPagingTagCloud", $this->values) ? $input = $this->values["configPagingTagCloud"] : $input = WIKINDX_PAGING_TAG_CLOUD_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "pagingTagCloud"),
            "configPagingTagCloud",
            $input,
            10,
            10
        ) . " " . \HTML\span('*', 'required') . BR .
        \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $input = array_key_exists("configListLink", $this->values) && ($this->values['configListLink']) ? "CHECKED" : WIKINDX_LIST_LINK_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "ListLink"), "configListLink", $input));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "noSort"));
        if ($this->insert) {
            $input = implode(', ', unserialize(base64_decode(WIKINDX_NO_SORT_DEFAULT))); // default at first install
        } elseif ($this->session->getVar("config_noSort")) { // After updating the field
            $input = implode(', ', unserialize(base64_decode($this->session->getVar("config_noSort"))));
            $this->session->delVar("config_noSort");
        } else {
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
        if ($this->insert) {
            $input = implode(', ', unserialize(base64_decode(WIKINDX_SEARCH_FILTER_DEFAULT))); // default at first install
        } elseif ($this->session->getVar("config_searchFilter")) { // After updating the field
            $input = implode(', ', unserialize(base64_decode($this->session->getVar("config_searchFilter"))));
            $this->session->delVar("config_searchFilter");
        } else {
            $input = implode(', ', WIKINDX_SEARCH_FILTER);
        }
        $pString .= \HTML\td(\HTML\p(\FORM\textareaInput(
            $this->messages->text("config", "searchFilter"),
            "configSearchFilter",
            $input,
            40,
            7
        ) .
                BR . \HTML\span($hint, 'hint')));
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
        $pString = $this->errorString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "title"));
        array_key_exists("configTitle", $this->values) ? $input = $this->values["configTitle"] : $input = WIKINDX_TITLE_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput($this->messages->text("config", "title"), "configTitle", $input, 30) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "stringLimit"));
        array_key_exists("configStringLimit", $this->values) ? $input = $this->values["configStringLimit"] : $input = WIKINDX_STRING_LIMIT_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "stringLimit"),
            "configStringLimit",
            $input,
            10,
            10
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "imgWidthLimit"));
        array_key_exists("configImgWidthLimit", $this->values) ? $input = $this->values["configImgWidthLimit"] : $input = WIKINDX_IMG_WIDTH_LIMIT_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "imgWidthLimit"),
            "configImgWidthLimit",
            $input,
            10,
            10
        ) . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "imgHeightLimit"));
        array_key_exists("configImgHeightLimit", $this->values) ? $input = $this->values["configImgHeightLimit"] : $input = WIKINDX_IMG_HEIGHT_LIMIT_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "imgHeightLimit"),
            "configImgHeightLimit",
            $input,
            10,
            10
        ) . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
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
        array_key_exists("configTemplate", $this->values) ? $input = $this->values["configTemplate"] : $input = WIKINDX_TEMPLATE_DEFAULT;
        array_key_exists($input, $templates) ? $input = $input : $input = WIKINDX_TEMPLATE_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "template"),
            "configTemplate",
            $templates,
            $input
        ) . " " . \HTML\span('*', 'required'));
        
        // Display the global language but change the default selection of the list to the default language when no template is defined or a language not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $languages = \LOCALES\getSystemLocales();
        array_key_exists("configLanguage", $this->values) ? $input = $this->values["configLanguage"] : $input = WIKINDX_LANGUAGE_DEFAULT;
        array_key_exists($input, $languages) ? $input = $input : $input = WIKINDX_LANGUAGE_DEFAULT;
        
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "language"),
            "configLanguage",
            $languages,
            $input
        ) . " " . \HTML\span('*', 'required'));
        
        // Display the global style but change the default selection of the list to the default style when no style is defined or a style not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $styles = \LOADSTYLE\loadDir();
        array_key_exists("configStyle", $this->values) ? $input = $this->values["configStyle"] : $input = WIKINDX_STYLE_DEFAULT;
        array_key_exists($input, $styles) ? $input = $input : $input = WIKINDX_STYLE_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "style"),
            "configStyle",
            $styles,
            $input,
            4
        ) . " " . \HTML\span('*', 'required'));
        
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
        array_key_exists("configTimezone", $this->values) ? $input = $this->values["configTimezone"] : $input = WIKINDX_TIMEZONE_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "timezone"),
            "configTimezone",
            $timezones,
            array_search($input, $timezones),
            10
        ) . " " . \HTML\span('*', 'required') .
            BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "imagesAllow"));
        $input = array_key_exists("configImagesAllow", $this->values) && ($this->values['configImagesAllow']) ? "CHECKED" : WIKINDX_IMAGES_ALLOW_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "imagesAllow"), "configImagesAllow", $input) .
            BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "imagesMaxSize"));
        array_key_exists("configImagesMaxSize", $this->values) ? $input = $this->values["configImagesMaxSize"] : $input = WIKINDX_IMAGES_MAXSIZE_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "imagesMaxSize"),
            "configImagesMaxSize",
            $input,
            10,
            10
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
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
        array_key_exists("configTagLowColour", $this->values) ? $input = '#' . $this->values["configTagLowColour"] :
            $input = '#' . WIKINDX_TAG_LOW_COLOUR_DEFAULT;
        $pString .= \HTML\td(\FORM\colorInput(
            $this->messages->text("config", "tagLowColour"),
            "configTagLowColour",
            $input
        ) . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        array_key_exists("configTagHighColour", $this->values) ? $input = '#' . $this->values["configTagHighColour"] :
            $input = '#' . WIKINDX_TAG_HIGH_COLOUR_DEFAULT;
        $pString .= \HTML\td(\FORM\colorInput(
            $this->messages->text("config", "tagHighColour"),
            "configTagHighColour",
            $input
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        
        // Scale factors of the tags cloud
        $tagfactors = [];
        foreach (range(WIKINDX_TAG_FACTOR_MIN, WIKINDX_TAG_FACTOR_MAX, WIKINDX_TAG_FACTOR_STEP) as $v) {
            $tagfactors[$v] = $v;
        }
        
        array_key_exists("configTagLowFactor", $this->values) ? $input = $this->values["configTagLowFactor"] : $input = WIKINDX_TAG_LOW_FACTOR_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "tagLowSize"),
            "configTagLowFactor",
            $tagfactors,
            array_search($input, $tagfactors),
            1
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        
        array_key_exists("configTagHighFactor", $this->values) ? $input = $this->values["configTagHighFactor"] : $input = WIKINDX_TAG_HIGH_FACTOR_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "tagHighSize"),
            "configTagHighFactor",
            $tagfactors,
            array_search($input, $tagfactors),
            1
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        
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
        $pString = $this->errorString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "rssAllow"));
        $input = array_key_exists("configRssAllow", $this->values) && ($this->values['configRssAllow']) ? "CHECKED" : WIKINDX_RSS_ALLOW_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "rssAllow"), "configRssAllow", $input) .
            BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "rssDisplay"));
        $input = array_key_exists("configRssDisplay", $this->values) && ($this->values['configRssDisplay']) ? "CHECKED" : WIKINDX_RSS_DISPLAY_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "rssDisplay"), "configRssDisplay", $input) .
            BR . \HTML\span($hint, 'hint'));
        // Display the global style but change the default selection of the list to the default style when no style is defined or a style not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $styles = \LOADSTYLE\loadDir();
        array_key_exists("configRssBibstyle", $this->values) ? $input = $this->values["configRssBibstyle"] : $input = WIKINDX_STYLE_DEFAULT;
        array_key_exists($input, $styles) ? $input = $input : $input = WIKINDX_STYLE_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "rssBibstyle"),
            "configRssBibstyle",
            $styles,
            $input,
            5
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        array_key_exists("configRssTitle", $this->values) ? $input = $this->values["configRssTitle"] : $input = WIKINDX_RSS_TITLE_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "rssTitle"),
            "configRssTitle",
            $input,
            20,
            100
        ) . " " . \HTML\span('*', 'required'));
        array_key_exists("configRssDescription", $this->values) ? $input = $this->values["configRssDescription"] : $input = WIKINDX_RSS_DESCRIPTION_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "rssDescription"),
            "configRssDescription",
            $input,
            50,
            255
        ) . " " . \HTML\span('*', 'required'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "rssLimit"));
        array_key_exists("configRssLimit", $this->values) ? $input = $this->values["configRssLimit"] : $input = WIKINDX_RSS_LIMIT_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "rssLimit"),
            "configRssLimit",
            $input,
            10,
            10
        ) . " " . \HTML\span('*', 'required') .
            BR . \HTML\span($hint, 'hint'));
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
        $pString = $this->errorString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "cmsAllow"));
        $input = array_key_exists("configCmsAllow", $this->values) && ($this->values['configCmsAllow']) ? "CHECKED" : WIKINDX_CMS_ALLOW_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "cmsAllow"), "configCmsAllow", $input) .
            BR . \HTML\span($hint, 'hint'));
        // Display the global style but change the default selection of the list to the default style when no style is defined or a style not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $styles = \LOADSTYLE\loadDir();
        array_key_exists("configCmsBibstyle", $this->values) ? $input = $this->values["configCmsBibstyle"] : $input = WIKINDX_STYLE_DEFAULT;
        array_key_exists($input, $styles) ? $input = $input : $input = WIKINDX_STYLE_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "cmsBibstyle"),
            "configCmsBibstyle",
            $styles,
            $input,
            5
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "cmsSql"));
        $input = array_key_exists("configCmsSql", $this->values) && ($this->values['configCmsSql']) ? "CHECKED" : WIKINDX_CMS_SQL_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "cmsSql"), "configCmsSql", $input) .
            BR . \HTML\span($hint, 'hint'));
        array_key_exists("configCmsDbUser", $this->values) ? $input = $this->values["configCmsDbUser"] : $input = WIKINDX_CMS_DB_USER_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "cmsDbUser"),
            "configCmsDbUser",
            $input,
            50,
            255
        ) . " " . \HTML\span('*', 'required'));
        array_key_exists("configCmsDbPassword", $this->values) ? $input = $this->values["configCmsDbPassword"] : $input = WIKINDX_CMS_DB_PASSWORD_DEFAULT;
        $pString .= \HTML\td(\FORM\passwordInput(
            $this->messages->text("config", "cmsDbPassword"),
            "configCmsDbPassword",
            $input,
            50,
            255
        ) . " " . \HTML\span('*', 'required'));
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
        $pString = $this->errorString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "gsAllow"));
        $input = array_key_exists("configGsAllow", $this->values) && ($this->values['configGsAllow']) ? "CHECKED" : WIKINDX_GS_ALLOW_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "gsAllow"), "configGsAllow", $input) .
            BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "gsAttachment"));
        $input = array_key_exists("configGsAttachment", $this->values) && ($this->values['configGsAttachment']) ? "CHECKED" : WIKINDX_GS_ATTACHMENT_DEFAULT;
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
        $mailMessage = FALSE;

        if ($isLdapExtAvailable) {
            if (array_key_exists("configLdapUse", $this->values) && $this->values['configLdapUse']) {
                if (array_key_exists('configLdapTestUser', $this->vars) && $this->vars['configLdapTestUser']) {
                    $this->testLdap();
                    $jScript = "javascript:coreOpenPopup('index.php?action=admin_CONFIGURE_CORE&amp;method=ldapTransactionReport', 80)";
                    $colour = $this->session->getVar("ldapTransactionLogStatus") == 'success' ? 'green' : 'red';
                    $mailMessage = \HTML\p(\HTML\aBrowse($colour, '', $this->messages->text("config", "ldapTransactionReport"), $jScript));
                }
            }
        }
        $pString = $this->errorString . $mailMessage;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        array_key_exists("configPasswordSize", $this->values) ? $input = $this->values["configPasswordSize"] : $input = WIKINDX_PASSWORD_SIZE_DEFAULT;
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "passwordSize"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "passwordSize"),
            "configPasswordSize",
            $input,
            3,
            4
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "passwordStrength"));
        $array = [
            'weak' => $this->messages->text("config", "passwordWeak"),
            'medium' => $this->messages->text("config", "passwordMedium"),
            'strong' => $this->messages->text("config", "passwordStrong"),
        ];
        array_key_exists("configPasswordStrength", $this->values) ? $input = $this->values["configPasswordStrength"] :
            $input = WIKINDX_PASSWORD_STRENGTH_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "passwordStrength"),
            "configPasswordStrength",
            $array,
            $input,
            3
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $input = array_key_exists("configLdapUse", $this->values) && ($this->values['configLdapUse']) ? "CHECKED" : WIKINDX_LDAP_USE_DEFAULT;
        $pString .= \HTML\td(
            \FORM\checkbox($this->messages->text("config", "ldapUse"), "configLdapUse", $input)
            .  BR . \HTML\span(\HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "ldapUse")))
        );
        array_key_exists("configLdapProtocolVersion", $this->values) ? $input = $this->values["configLdapProtocolVersion"] :
            $input = WIKINDX_LDAP_PROTOCOL_VERSION_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "ldapProtocolVersion"),
            "configLdapProtocolVersion",
            WIKINDX_LDAP_PROTOCOLE_VERSIONS,
            $input,
            1
        ));
        array_key_exists("configLdapServer", $this->values) ? $input = $this->values["configLdapServer"] : $input = WIKINDX_LDAP_SERVER_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "ldapServer"),
            "configLdapServer",
            $input,
            30,
            255
        ));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "ldapDn"));
        array_key_exists("configLdapDn", $this->values) ? $input = $this->values["configLdapDn"] : $input = WIKINDX_LDAP_DN_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "ldapDn"),
            "configLdapDn",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        array_key_exists("configLdapPort", $this->values) ? $input = $this->values["configLdapPort"] : $input = WIKINDX_LDAP_PORT_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "ldapPort"),
            "configLdapPort",
            $input,
            6,
            6
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "authGate"));
        $input = array_key_exists("configAuthGate", $this->values) && ($this->values['configAuthGate']) ? "CHECKED" : WIKINDX_AUTHGATE_USE_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "authGate"), "configAuthGate", $input) . BR . \HTML\span($hint, 'hint'));
        array_key_exists("configAuthGateMessage", $this->values) ? $input = $this->values["configAuthGateMessage"] :
            $input = WIKINDX_AUTHGATE_MESSAGE_DEFAULT;
        $pString .= \HTML\td(\FORM\textareaInputmceNoEditor($this->messages->text("config", "authGateMessage"), "configAuthGateMessage", $input, 80));
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "authGateReset"), "configAuthGateReset", WIKINDX_AUTHGATE_RESET_DEFAULT));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        if ($isLdapExtAvailable) {
        // Extra field not in the database used for test purposes only
            $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "ldapTest"));
                array_key_exists("configLdapTestUser", $this->vars) ? $input = $this->vars["configLdapTestUser"] : $input = FALSE;
            $pString .= \HTML\p(\FORM\textInput($this->messages->text("config", "ldapTestUsername"), "configLdapTestUser", $input, 30, 255));
                array_key_exists("configLdapTestPassword", $this->vars) ? $input = $this->vars["configLdapTestPassword"] : $input = FALSE;
            $pString .= \HTML\p(\FORM\passwordInput($this->messages->text("config", "ldapTestPassword"), "configLdapTestPassword", $input, 30, 255) .
                BR . \HTML\span($hint, 'hint'));
        } else {
            $pString .= \HTML\p($this->messages->text("hint", "ldapExtDisabled"), 'redText');
        }

        return $pString;
    }
    /**
     * Display email config options
     *
     * @return string
     */
    private function emailConfigDisplay()
    {
        $pString = $this->errorString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $input = array_key_exists("configMailUse", $this->values) && ($this->values['configMailUse']) ? "CHECKED" : WIKINDX_MAIL_USE_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "mailServer"), "configMailUse", $input));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailFrom"));
        array_key_exists("configMailFrom", $this->values) ? $input = $this->values["configMailFrom"] : $input = WIKINDX_MAIL_FROM_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "mailFrom"),
            "configMailFrom",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailReplyTo"));
        array_key_exists("configMailReturnPath", $this->values) ? $input = $this->values["configMailReplyTo"] : $input = WIKINDX_MAIL_REPLYTO_DEFAULT;
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
        array_key_exists("configMailBackend", $this->values) ? $input = $this->values["configMailBackend"] : $input = WIKINDX_MAIL_BACKEND_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "mailBackend"),
            "configMailBackend",
            WIKINDX_PHPMAILER_BACKENDS,
            $input,
            count(WIKINDX_PHPMAILER_BACKENDS)
        ) . " " . \HTML\span('*', 'required'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailReturnPath"));
        array_key_exists("configMailReturnPath", $this->values) ? $input = $this->values["configMailReturnPath"] : $input = WIKINDX_MAIL_RETURN_PATH_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "mailReturnPath"),
            "configMailReturnPath",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailSmPath"));
        array_key_exists("configMailSmPath", $this->values) ? $input = $this->values["configMailSmPath"] : $input = WIKINDX_MAIL_SENDMAIL_PATH_DEFAULT;
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
        array_key_exists("configMailSmtpServer", $this->values) ? $input = $this->values["configMailSmtpServer"] : $input = WIKINDX_MAIL_SMTP_SERVER_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "mailSmtpServer"),
            "configMailSmtpServer",
            $input,
            30,
            255
        ) . BR . \HTML\span($hint, 'hint'));
        array_key_exists("configMailSmtpPort", $this->values) ? $input = $this->values["configMailSmtpPort"] : $input = WIKINDX_MAIL_SMTP_PORT_DEFAULT;
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailSmtpPort"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "mailSmtpPort"),
            "configMailSmtpPort",
            $input,
            10,
            10
        ) . BR . \HTML\span($hint, 'hint'));
        array_key_exists("configMailSmtpEncrypt", $this->values) ? $input = $this->values["configMailSmtpEncrypt"] : $input = WIKINDX_MAIL_SMTP_ENCRYPT_DEFAULT;
        $input = (!$input) ? WIKINDX_MAIL_SMTP_ENCRYPT_DEFAULT : $input;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "mailSmtpEncrypt"),
            "configMailSmtpEncrypt",
            WIKINDX_PHPMAILER_SMTP_ENCRYPT,
            $input,
            count(WIKINDX_PHPMAILER_SMTP_ENCRYPT)
        ));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "mailSmtpPersist"));
        $input = array_key_exists("configMailSmtpPersist", $this->values) && ($this->values['configMailSmtpPersist']) ? "CHECKED" : WIKINDX_MAIL_SMTP_PERSIST_DEFAULT;
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
        $input = array_key_exists("configMailSmtpAuth", $this->values) && ($this->values['configMailSmtpAuth']) ? "CHECKED" : WIKINDX_MAIL_SMTP_AUTH_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "mailSmtpAuth"), "configMailSmtpAuth", $input) .
            BR . \HTML\span($hint, 'hint'));
        array_key_exists("configMailSmtpUsername", $this->values) ? $input = $this->values["configMailSmtpUsername"] : $input = WIKINDX_MAIL_SMTP_USERNAME_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "mailSmtpUsername"),
            "configMailSmtpUsername",
            $input,
            30,
            255
        ) . " " . \HTML\span('*', 'required'));
        array_key_exists("configMailSmtpPassword", $this->values) ? $input = $this->values["configMailSmtpPassword"] : $input = WIKINDX_MAIL_SMTP_PASSWORD_DEFAULT;
        $pString .= \HTML\td(\FORM\passwordInput(
            $this->messages->text("config", "mailSmtpPassword"),
            "configMailSmtpPassword",
            $input,
            30,
            255
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $input = array_key_exists("configNotify", $this->values) && ($this->values['configNotify']) ? "CHECKED" : WIKINDX_NOTIFY_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "notify"), "configNotify", $input));
        $input = array_key_exists("configEmailStatistics", $this->values) && ($this->values['configEmailStatistics']) ? "CHECKED" : WIKINDX_EMAIL_STATISTICS_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "statistics"), "configEmailStatistics", $input));
        $input = array_key_exists("configEmailNews", $this->values) && ($this->values['configEmailNews']) ? "CHECKED" : WIKINDX_EMAIL_NEWS_DEFAULT;
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
            . BR . $hint
        );

        return $pString;
    }
    /**
     * Test the ldap configuration
     *
     * @return bool
     */
    private function testLdap()
    {
        if (!in_array("ldap", get_loaded_extensions())) {
            return FALSE;
        }
        if (($ds = @ldap_connect(WIKINDX_LDAP_SERVER, WIKINDX_LDAP_PORT)) === FALSE) {
            if (ldap_errno($ds)) {
                $error = \HTML\p(ldap_err2str(ldap_errno($ds)));
                $this->session->setVar("ldapTransactionLog", $error);
                $this->session->setVar("ldapTransactionLogStatus", "failure");

                return FALSE;
            }
        }
        @ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, WIKINDX_LDAP_PROTOCOL_VERSION);
        if (($ldapbind = @ldap_bind($ds)) === FALSE) {
            if (ldap_errno($ds)) {
                $error = \HTML\p(ldap_err2str(ldap_errno($ds)));
                ldap_get_option($ds, 0x32, $err);
                $error .= \HTML\p($err);
                $this->session->setVar("ldapTransactionLog", $error);
                $this->session->setVar("ldapTransactionLogStatus", "failure");

                return FALSE;
            }
        }
        $sr = @ldap_search($ds, WIKINDX_LDAP_DN, '(uid=' . $this->vars['configLdapTestUser'] . ')');
        $info = @ldap_get_entries($ds, $sr);
        if ($info['count'] > 1) {
            if (ldap_errno($ds)) {
                $error = \HTML\p(ldap_err2str(ldap_errno($ds)));
                ldap_get_option($ds, 0x32, $err);
                $error .= \HTML\p($err);
                $this->session->setVar("ldapTransactionLog", $error);
                $this->session->setVar("ldapTransactionLogStatus", "failure");

                return FALSE;
            }
        }
        if ($info['count'] == 1) {
            $ldaprdn = $info[0]['dn'];
        } else {
            $ldaprdn = "cn=" . $this->vars['configLdapTestUser'] . "," . WIKINDX_LDAP_DN;
        }
        // Connexion au serveur LDAP
        $ldapbind = @ldap_bind($ds, $ldaprdn, $this->vars['configLdapTestPassword']);
        if (ldap_errno($ds)) {
            $error = \HTML\p(ldap_err2str(ldap_errno($ds)));
            ldap_get_option($ds, 0x32, $err);
            $error .= \HTML\p($err);
            $this->session->setVar("ldapTransactionLog", $error);
            $this->session->setVar("ldapTransactionLogStatus", "failure");

            return FALSE;
        } else { // success
            $this->session->setVar("ldapTransactionLog", $this->messages->text('config', 'ldapTestSuccess'));
            $this->session->setVar("ldapTransactionLogStatus", "success");
        }
    }
    /**
     * Display file/attachment config options
     *
     * @return string
     */
    private function fileConfigDisplay()
    {
        $pString = $this->errorString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "deleteSeconds"));
        array_key_exists("configFileDeleteSeconds", $this->values) ? $input = $this->values["configFileDeleteSeconds"] : $input = WIKINDX_FILE_DELETE_SECONDS_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "deleteSeconds"),
            "configFileDeleteSeconds",
            $input,
            10,
            10
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $input = array_key_exists("configFileAttach", $this->values) && ($this->values['configFileAttach']) ? "CHECKED" : WIKINDX_FILE_ATTACH_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "fileAttach"), "configFileAttach", $input));
        $input = array_key_exists("configFileViewLoggedOnOnly", $this->values) && ($this->values['configFileViewLoggedOnOnly']) ? "CHECKED" : WIKINDX_FILE_VIEW_LOGGEDON_ONLY_DEFAULT;
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
        $pString = $this->errorString;
        // need to force reload so display box of active types displays properly after DB write
        $resourceMap = FACTORY_RESOURCEMAP::getFreshInstance();
        $typesRaw = $resourceMap->getTypesRaw();
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", 'deactivateResourceTypes'));
        foreach ($typesRaw as $type) {
            $types[$type] = $this->messages->text("resourceType", $type);
        }
        asort($types);
        if ($this->session->getVar("config_deactivateResourceTypes")) { // After updating the field
            $array = unserialize(base64_decode($this->session->getVar("config_deactivateResourceTypes")));
            $this->session->delVar("config_deactivateResourceTypes");
        } else {
            $array = WIKINDX_DEACTIVATE_RESOURCE_TYPES;
        }
        foreach ($array as $type) {
            $deactivated[$type] = $this->messages->text("resourceType", $type);
            if (array_key_exists($type, $types)) {
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
        $pString = $this->errorString;
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "siteMapAllow"));
        $input = array_key_exists("configSiteMapAllow", $this->values) && ($this->values['configSiteMapAllow']) ? "CHECKED" : WIKINDX_SITEMAP_ALLOW_DEFAULT;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "siteMapAllow"), "configSiteMapAllow", $input) .
            BR . \HTML\span($hint, 'hint'));
        
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "restrictUserId"));
        array_key_exists("configRestrictUserId", $this->values) ? $input = $this->values["configRestrictUserId"] : WIKINDX_RESTRICT_USERID_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "restrictUserId"),
            "configRestrictUserId",
            $users,
            $input,
            1
        ) . BR . \HTML\span($hint, 'hint'));
        
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "maxPaste"));
        $input = array_key_exists("configMaxPaste", $this->values) ? $this->values["configMaxPaste"] : WIKINDX_MAX_PASTE_DEFAULT;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text('config', 'maxPaste'),
            "configMaxPaste",
            $input,
            10,
            10
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
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
                    "configLdapDn",
                    "configLdapPort",
                    "configLdapProtocolVersion",
                    "configLdapServer",
                    "configLdapUse",
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
                ];

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
        if (!empty($arrayNewNoSort)) {
            foreach ($arrayNewNoSort as $word) {
                $array[] = "^($word)\\s(.*)";
                $arrayBrace[] = "^\\{($word)\\s(.*)";
            }
            $newNSPattern = implode('|', $array);
            $newNSPatternBrace = implode('|', $arrayBrace);
        }
        $resultset = $this->db->select('resource', ['resourceId', 'resourceTitle', 'resourceSubtitle', 'resourceNoSort', 'resourceTitleSort']);
        while ($row = $this->db->fetchRow($resultset)) {
            $update = [];
            if ($newNSPattern && preg_match("/$newNSPattern/ui", $row['resourceTitle'], $matches)) {
                $update['resourceTitle'] = $resourceTitleSort = array_pop($matches);
                $update['resourceNoSort'] = array_pop($matches);
                if ($row['resourceSubtitle']) {
                    $resourceTitleSort .= ' ' . $row['resourceSubtitle'];
                }
                $update['resourceTitleSort'] = str_replace(['{', '}'], '', $resourceTitleSort);
            } elseif ($newNSPatternBrace && preg_match("/$newNSPatternBrace/ui", $row['resourceTitle'], $matches)) {
                $resourceTitleSort = array_pop($matches);
                $update['resourceTitle'] = '{' . $resourceTitleSort;
                $update['resourceNoSort'] = array_pop($matches);
                if ($row['resourceSubtitle']) {
                    $resourceTitleSort .= ' ' . $row['resourceSubtitle'];
                }
                $update['resourceTitleSort'] = str_replace(['{', '}'], '', $resourceTitleSort);
            } elseif (array_search(mb_strtolower($row['resourceNoSort']), $arrayRemoveNoSort) !== FALSE) {
                $update['resourceTitle'] = $row['resourceNoSort'] . ' ' . $row['resourceTitle'];
                $update['resourceTitleSort'] = $row['resourceNoSort'] . ' ' . $row['resourceTitleSort'];
                $this->db->formatConditions(['resourceId' => $row['resourceId']]);
                $this->db->updateNull('resource', ['resourceNoSort']);
            }
            if (!empty($update)) {
                $this->db->formatConditions(['resourceId' => $row['resourceId']]);
                $this->db->update('resource', $update);
            }
        }
    }
    /**
     * Check input
     */
    private function checkInput()
    {
        // Check for special fields and carry out actions as necessary
        // 1. configAuthGateReset – reset all 'usersAuthGate' fields back to 'N'
        if (array_key_exists('configAuthGateReset', $this->vars) && $this->vars['configAuthGateReset']) {
            $this->db->update('users', ['usersGDPR' => 'N']);
        }
        $array = $required = [];
        // Store in session first and remove unrequired session variables
        foreach (WIKINDX_LIST_CONFIG_OPTIONS as $key => $unused) {
            if (array_key_exists($key, $this->vars)) {
                if (($key == 'configLastChanges') || ($key == 'configPaging') || ($key == 'configStringLimit') || ($key == 'configPagingTagCloud')) {
                    if ($this->vars[$key] < 0) {
                        $this->vars[$key] = -1;
                    }
                }
                $array[$key] = $this->vars[$key];
            } elseif (
                // checkboxes
                in_array($key, [
                    "configAuthGate",
                    "configBypassSmartyCompile",
                    "configCmsAllow",
                    "configCmsSql",
                    "configDebugSql",
                    "configDenyReadOnly",
                    "configDisplayStatistics",
                    "configDisplayUserStatistics",
                    "configEmailNews",
                    "configEmailStatistics",
                    "configErrorReport",
                    "configFileAttach",
                    "configFileViewLoggedOnOnly",
                    "configGlobalEdit",
                    "configGsAllow",
                    "configGsAttachment",
                    "configImagesAllow",
                    "configImportBib",
                    "configIsTrunk",
                    "configLdapUse",
                    "configListLink",
                    "configMailSmtpAuth",
                    "configMailSmtpPersist",
                    "configMailUse",
                    "configMetadataAllow",
                    "configMetadataUserOnly",
                    "configMultiUser",
                    "configNotify",
                    "configNotify",
                    "configOriginatorEditOnly",
                    "configQuarantine",
                    "configReadOnlyAccess",
                    "configRssAllow",
                    "configRssDisplay",
                    "configSiteMapAllow",
                    "configUserRegistration",
                    "configUserRegistrationModerate",
                ])
            ) {
                $this->session->delVar("config_" . $key);
                $this->session->delVar("setup_" . str_replace('config', 'setup', $key));
            }
        }
        if (!empty($array)) {
            $this->session->writeArray($array, "config");
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
        foreach ($requireDefault as $value => $default) {
            if (array_key_exists($value, $this->vars) && !$this->vars[$value]) {
                $this->vars[$value] = $default;
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
        foreach ($required as $value) {
            if (array_key_exists($value, $this->vars)) {
                $input = trim($this->vars[$value]);
                if (!$input) {
                    $this->badInputLoad($this->errors->text("inputError", 'missing', " ($value) "), $this->vars['selectItem']);
                }
            }
        }
        if (array_key_exists('password', $this->vars) && ($this->vars['password'] != $this->vars['passwordConfirm'])) {
            $this->badInputLoad($this->errors->text("inputError", 'passwordMismatch'), $this->vars['selectItem']);
        }
        $isInt = [
            "configFileDeleteSeconds",
            "configImagesMaxSize",
            "configImgHeightLimit",
            "configImgWidthLimit",
            "configLastChanges",
            "configLastChangesDayLimit",
            "configLdapPort",
            "configLdapProtocolVersion",
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
        foreach ($isInt as $value) {
            if (array_key_exists($value, $this->vars)) {
                $input = trim($this->vars[$value]);
            } else {
                continue;
            }
            if (($value == "configTagLowColour") || ($value == "configTagHighColour")) {
                $input = hexdec($input);
            }
            // some values cannot be less than 0
            if ((array_search($value, $notNegative) !== FALSE) && ((int)$input < 0)) {
                $this->badInputLoad($this->errors->text("inputError", 'invalid', " ($value) "), $this->vars['selectItem']);
            }
            // these can be blank, otherwise must be an int
            if (($value == 'configRestrictUserId') || ($value == 'configImagesMaxSize') || ($value == 'configMailSmtpPort')) {
                if ($input == '') {
                    continue;
                }
                if (!is_numeric($input) || !is_int($input + 0)) { // cast to number
                    $this->badInputLoad($this->errors->text("inputError", 'notInt', " ($value) "), $this->vars['selectItem']);
                }
            }
            if (!is_numeric($input) || !is_int($input + 0)) { // cast to number
                $this->badInputLoad($this->errors->text("inputError", 'notInt', " ($value) "), $this->vars['selectItem']);
            }
        }
        // Dependencies
        $this->dependencies('configRssAllow', ['configRssBibstyle', 'configRssTitle', 'configRssDescription', 'configRssLimit']);
        $this->dependencies('configCmsAllow', ['configCmsBibstyle']);
        $this->dependencies('configCmsSql', ['configCmsDbUser', 'configCmsDbPassword']);
        $this->dependencies('configMailUse', ['configMailBackend']);
        $this->dependencies('configLdapUse', ['configLdapServer', 'configLdapPort', 'configLdapProtocolVersion', 'configLdapDn']);
        $this->dependencies('configAuthGate', ['configAuthGateMessage']);
        if (array_key_exists('configMailUse', $this->vars) && ($this->vars['configMailBackend'] == 'sendmail')) {
            $this->dependencies('configMailUse', ['configMailSmPath']);
        } elseif (array_key_exists('configMailUse', $this->vars) && ($this->vars['configMailBackend'] == 'smtp')) {
            $this->dependencies('configMailUse', ['configMailSmtpServer', 'configMailSmtpPort', 'configMailSmtpEncrypt']);
            $this->dependencies('configMailSmtpAuth', ['configMailSmtpUsername', 'configMailSmtpPassword']);
        }
        // Check size of password is no less than N chars
        if (array_key_exists('configPasswordSize', $this->vars) && ($this->vars['configPasswordSize'] < WIKINDX_PASSWORD_SIZE_DEFAULT)) {
            $this->vars['configPasswordSize'] = WIKINDX_PASSWORD_SIZE_DEFAULT;
        }
    }
    /**
     * Check field dependencies – parent is checkbox, childArray of textboxes or selectboxes
     *
     * @param string $parent
     * @param array $childArray
     */
    private function dependencies($parent, $childArray)
    {
        if (array_key_exists($parent, $this->vars)) {
            foreach ($childArray as $value) {
                if (array_key_exists($value, $this->vars)) {
                    $input = trim($this->vars[$value]);
                } else {
                    $this->badInputLoad($this->errors->text("inputError", 'missing', " ($value) "), $this->vars['selectItem']);
                }
                if (!$input) {
                    $this->badInputLoad($this->errors->text("inputError", 'missing', " ($value) "), $this->vars['selectItem']);
                }
            }
        }
    }
    /**
     * fromDbToSession
     *
     * When updating, if the config variables are not in a session, grab from db table and write to session.
     * Admin configuration session variables have prefix of 'config_'.
     *
     * @return array
     */
    private function fromDbToSession()
    {
        $row = $this->configDbStructure->getAllData();
        // Remove noSort and searchFilter and write the rest to session variables
        unset($row['configNoSort']);
        unset($row['configSearchFilter']);
        if (!$this->session->writeArray($row, "config")) {
            echo $this->errors->text("sessionError", "write");
        }
        // deal with checkboxes
        foreach ($row as $field => $value) {
            if ((WIKINDX_LIST_CONFIG_OPTIONS[$field]["type"] == 'configBoolean') && !$value) {
                unset($row[$field]);
            }
        }
        // 'lastChanges' can be 0 so may not exist if called from the session
        if (!array_key_exists('configLastChanges', $row) || !$row['configLastChanges']) {
            $row['configLastChanges'] = 0;
        }
        // 'lastChangesDayLimit' can be 0 so may not exist if called from the session
        if (!array_key_exists('configLastChangesDayLimit', $row) || !$row['configLastChangesDayLimit']) {
            $row['configLastChangesDayLimit'] = 0;
        }
        // tidy up the $row elements for presentation to the browser.
        $tidy = [];
        foreach ($row as $key => $value) {
            if (!is_array($value)) {
                $tidy[$key] = \HTML\dbToFormTidy($value);
            }
        }

        return $tidy;
    }
    /**
     * Error handling
     *
     * @param mixed $error
     * @param mixed $item
     */
    private function badInputLoad($error, $item = FALSE)
    {
        if ($this->insert) { // new install
            $this->badInput->closeType = 'closeNoMenu';
        }
        if ($item) {
            $this->badInput->close($error, $this, ['init', $item]);
        } else {
            $this->badInput->close($error, $this, 'init');
        }
    }
}
