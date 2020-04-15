<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * AUTHORIZE
 *
 * Logging on and system authorisation class.
 *
 * @package wikindx\core\startup
 */
class AUTHORIZE
{
    /** object */
    private $db;
    /** object */
    private $session;
    /** object */
    private $config;
    /** object */
    private $loadConfig;
    /** array */
    private $vars;
    /** object */
    private $configDbStructure;

    /**
     *	AUTHORIZE
     */
    public function __construct()
    {
        $this->session = FACTORY_SESSION::getInstance();
        $this->config = FACTORY_CONFIG::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->loadConfig = FACTORY_LOADCONFIG::getInstance();
        $this->configDbStructure = FACTORY_CONFIGDBSTRUCTURE::getInstance();
        if (!$this->session->getVar("setup_Language"))
        {
            $fields = $this->db->listFields('config');
            // set the default language prior to displaying the login prompt
            if (in_array('language', $fields) !== FALSE)
            { // perhaps this is a first install or upgrade to >= v4 (v3 has config.language not config.configLanguage)
                $this->session->setVar('setup_Language', "auto");
            }
            elseif (array_search('configLanguage', $fields) !== FALSE)
            {
                if ($this->db->numRows($this->db->select('config', '*')) == 1)
                { // Prior to v5.3
                    $this->session->setVar('setup_Language', "auto");
                }
                else
                { // post v5.3
                    $user = FACTORY_USER::getInstance();
                    $row = $this->configDbStructure->getData('configLanguage');
                    if (empty($row))
                    { // perhaps this is a first install
                        $this->session->setVar('setup_Language', "auto");
                    }
                    else
                    {
                        // populate session with default values from config
                        $user->writeSessionPreferences(FALSE, 'config');
                    }
                }
            }
        }
    }
    /**
     * gatekeeper to the system.
     *
     * Order is of checking is important!
     *
     * @return bool
     */
    public function gatekeep()
    {
        $this->loadConfig->load();
        if (array_key_exists("action", $this->vars))
        {
            // Logged-on user clicked on 'OK' when asked to confirm GDPR or privacy statement
            if ($this->vars["action"] == 'authGate')
            {
                $this->db->formatConditions(['usersId' => $this->session->getVar('setup_UserId')]);
                $this->db->update('users', ['usersGDPR' => 'Y']);
                // FALSE means go to front of WIKINDX
                return FALSE;
            }
            // logging out
            if ($this->vars["action"] == 'logout')
            {
                $this->logout();
            }
            // User logging in from readOnly mode
            elseif ($this->vars["action"] == 'initLogon')
            {
                // First delete any pre-existing session in case this user has been logging on and off as different users --
                // keep template and language etc.
                $language = $this->config->WIKINDX_LANGUAGE;
                $template = $this->config->WIKINDX_TEMPLATE;
                $userReg = $this->config->WIKINDX_USERREGISTRATION;
                $multiUser = $this->config->WIKINDX_MULTIUSER;
                $this->session->setVar('setup_Language', $language);
                $this->session->setVar('setup_Template', $template);
                $this->session->setVar('setup_UserRegistration', $userReg);
                $this->session->setVar('setup_MultiUser', $multiUser);
                $this->initLogon(); // login prompt
                FACTORY_CLOSENOMENU::getInstance();
            }
            if (array_key_exists('method', $this->vars) &&
            (($this->vars["method"] == 'forgetInitStage1') ||
            ($this->vars["method"] == 'forgetInitStage2') ||
            ($this->vars["method"] == 'forgetProcess')))
            {
                return TRUE;
            }
            // User supplying username and password to logon to WIKINDX.
            // $auth->logonCheck() dies after printing logon screen if bad comparison.
            elseif (($this->vars["action"] == 'logon') &&
                array_key_exists("password", $this->vars) && array_key_exists("username", $this->vars))
            {
                $this->logonCheck($this->vars['username'], $this->vars['password']);
                // tidy up old files
                FILE\tidyFiles();
                // FALSE means go to front of WIKINDX
                return FALSE;
            }
            // superAdmin already logged in after upgrade so just set up the environment
            elseif ($this->vars["action"] == 'upgradeDB')
            {
                $user = FACTORY_USER::getInstance();
                $this->session->clearSessionData();
                $this->session->setVar("setup_UserId", '1'); // superAdmin always id = '1'
                $this->session->setVar("setup_Write", TRUE);
                $this->session->delVar("setup_ReadOnly");
                $user->writeSessionPreferences(FALSE, 'config');
                // restore some session variables if stored from last logout
                $this->restoreEnvironment();
                //				$this->loadConfig->load();
                return FALSE;
            }
            // User requesting readOnly access - clear previous sessions
            elseif ($this->vars["action"] == 'readOnly')
            {
                if ($this->config->WIKINDX_DENY_READONLY)
                {
                    $this->initLogon(); // login prompt
                    FACTORY_CLOSENOMENU::getInstance();
                }
                // First delete any pre-existing session in case this user has been logging on and off as different users
                // session array 'setup' is deleted at logout()
                $this->session->destroy();
                $this->session->setVar("setup_ReadOnly", TRUE);
                // tidy up old files
                FILE\tidyFiles();
                // populate session with default values from config
                $user = FACTORY_USER::getInstance();
                $user->writeSessionPreferences(FALSE, 'config');
                $this->clearEmbargoes();
                $this->checkNews();
                // FALSE means go to front of WIKINDX
                return FALSE;
            }
            // User registration
            elseif ($this->config->WIKINDX_MULTIUSER && $this->config->WIKINDX_USERREGISTRATION && ($this->config->WIKINDX_MAIL_SERVER))
            {
                include_once("core/modules/usersgroups/REGISTER.php");
                $obj = new REGISTER();
                if ($this->vars["action"] == 'initRegisterUser')
                {
                    $obj->initRegister();
                    if (!$this->session->getVar("setup_ReadOnly"))
                    {
                        FACTORY_CLOSENOMENU::getInstance();
                    }
                    else
                    {
                        FACTORY_CLOSE::getInstance();
                    }
                }
                elseif (array_key_exists('method', $this->vars) && $this->vars["method"] == 'registerConfirm')
                {
                    GLOBALS::addTplVar('content', $obj->registerConfirm());
                    if (!$this->session->getVar("setup_ReadOnly"))
                    {
                        FACTORY_CLOSENOMENU::getInstance();
                    }
                    else
                    {
                        FACTORY_CLOSE::getInstance();
                    }
                }
                elseif (array_key_exists('method', $this->vars) && $this->vars["method"] == 'registerUser')
                {
                    $obj->registerUser();
                    if (!$this->session->getVar("setup_ReadOnly"))
                    {
                        FACTORY_CLOSENOMENU::getInstance();
                    }
                    else
                    {
                        FACTORY_CLOSE::getInstance();
                    }
                }
                elseif (array_key_exists('method', $this->vars) && $this->vars["method"] == 'registerUserAdd')
                {
                    GLOBALS::addTplVar('content', $obj->registerUserAdd());
                    FACTORY_CLOSE::getInstance();
                }
                elseif (array_key_exists('method', $this->vars) && $this->vars["method"] == 'registerRequest')
                {
                    GLOBALS::addTplVar('content', $obj->registerRequest());
                    FACTORY_CLOSE::getInstance();
                }
            }
        }
        if (isset($this->vars["method"]) && ($this->vars['method'] == 'RSS') && !$this->config->WIKINDX_DENY_READONLY)
        {
            $this->session->setVar("setup_ReadOnly", TRUE);

            return TRUE;
        }
        // access already granted
        if ($this->session->getVar('setup_Write'))
        {
            return TRUE;
        }
        // access already granted
        if ($this->session->getVar("setup_ReadOnly"))
        {
            // populate session with default values from config
            //			$user = FACTORY_USER::getInstance();
            //			$user->writeSessionPreferences(FALSE, 'config');
            return TRUE;
        }
        if ((!array_key_exists('action', $this->vars) || $this->vars['action'] != 'upgradeDBLogon'))
        {
            //			$this->loadConfig->load();
            $cookie = FACTORY_COOKIE::getInstance();
            // grabCookie() returns TRUE if valid cookie - otherwise, proceed to manual logon
            if ($cookie->grabCookie())
            {
                // Success - so restore some session variables if stored from last logout
                $this->restoreEnvironment();

                return TRUE;
            }
        }
        if (!$this->session->getVar("setup_Write") && !$this->session->getVar('setup_ReadOnly'))
        {
            // Default == read only access.
            if ($this->config->WIKINDX_READONLYACCESS && !$this->config->WIKINDX_DENY_READONLY)
            {
                $this->session->setVar("setup_ReadOnly", TRUE);
                // populate session with default values from config
                $user = FACTORY_USER::getInstance();
                $user->writeSessionPreferences(FALSE, 'config');
                $this->checkNews();

                return TRUE;
            }
            $this->initLogon(); // login prompt
            FACTORY_CLOSENOMENU::getInstance();
        }
        // FALSE indicates that index.php will print the front page of WIKINDX
        return FALSE;
    }
    /**
     * Display the empty form for logging on
     *
     * @param string $error Default is FALSE
     */
    public function initLogon($error = FALSE)
    {
        $this->session->delVar("setup_ReadOnly");
        $messages = FACTORY_MESSAGES::getFreshInstance();
        GLOBALS::setTplVar('heading', $messages->text("heading", "logon"));
        $pString = $error;
        $row = $this->configDbStructure->getData(['configMultiUser', 'configUserRegistration']);
        if (!$row['configMultiUser'])
        {
            $errors = FACTORY_ERRORS::getFreshInstance();
            $pString .= \HTML\p($errors->text("warning", "superadminOnly"));
        }
        $pString .= \HTML\p($messages->text("authorize", "writeLogon"));
        $link1 = "index.php?action=readOnly";
        $link2 = "index.php?action=usersgroups_FORGET_CORE&method=forgetInitStage1";
        $link3 = "index.php?action=initRegisterUser";
        $links = FALSE;
        /**
         * For a test user (see index.php)
         */
        if ($this->config->WIKINDX_RESTRICT_USERID != WIKINDX_RESTRICT_USERID_DEFAULT)
        {
            $pString .= \HTML\p("For test drive purposes, " .
                \HTML\strong($messages->text("user", "username") . ":&nbsp;&nbsp;") . "wikindx, " .
                \HTML\strong($messages->text("user", "password") . ":&nbsp;&nbsp;") . "wikindx");
        }
        $forgot = $this->config->WIKINDX_MAIL_SERVER ? \HTML\a("link", $messages->text("user", "forget6"), $link2) : FALSE;
        $pString .= $this->printLogonTable();
        // Give user the option to bypass logging in simply to read.
        if (!$this->config->WIKINDX_DENY_READONLY)
        {
            $links = \HTML\a("link", $messages->text("authorize", "readOnly") .
            BR . $forgot, $link1);
        }
        else
        {
            $links = $forgot;
        }
        if (($row['configUserRegistration']) && ($row['configMultiUser']) && ($this->config->WIKINDX_MAIL_SERVER))
        {
            $links .= BR . \HTML\a("link", $messages->text("menu", "register"), $link3);
        }
        $pString .= \HTML\p($links, FALSE, 'right');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Initial logon to the system.
     *
     * Public for use with API.
     * If cleared, user environment is set and embargoed resources checked for clearance, otherwise, user is presented with logon prompt again
     *
     * @param string $username
     * @param string $password
     */
    public function logonCheck($username, $password)
    {
        $user = FACTORY_USER::getInstance();
        // If checkPassword is successful, it also sets up some session variables to allow access without reauthentication.
        if (!$user->checkPassword($username, $password))
        {
            $this->failure();
        }
        // Success - so restore some session variables if stored from last logout
        $this->restoreEnvironment();
        $this->clearEmbargoes();
        // Run AuthGate (e.g. GDPR and/or privacy notification if required)
        $this->authGate();
    }
    /**
     * isPluginExecutionAuthorised
     *
     * Check if the level of auth matchs the level requested by the calling plugin.
     * If $promptForLogin is TRUE, displays the login page instead of returning FALSE,
     * when the plugin is not authorised
     *
     * @param int $pluginAuthLevelRequested Default is 0.
     *                                      Auth level:
     *                                      unknow => always unauthorised, menu item not displayed
     *                                      0      => menu item displayed for all users (logged or not)
     *                                      1      => menu item displayed for users logged (with write access)
     *                                      2      => menu item displayed only for logged admins
     * @param bool $promptForLogin Default is FALSE.
     *
     * @return bool
     */
    public function isPluginExecutionAuthorised($pluginAuthLevelRequested = 0, $promptForLogin = FALSE)
    {
        $isAuthorised = FALSE;

        switch ($pluginAuthLevelRequested)
        {
            case 0:
                $isAuthorised = TRUE;

            break;
            case 1:
                if ($this->session->getVar("setup_Write"))
                {
                    $isAuthorised = TRUE;
                }
                elseif ($promptForLogin == TRUE)
                {
                    $this->initLogon();
                }

            break;
            case 2:
                if ($this->session->getVar("setup_Superadmin"))
                {
                    $isAuthorised = TRUE;
                }
                elseif ($promptForLogin == TRUE)
                {
                    $this->initLogon();
                }

            break;
        }

        return $isAuthorised;
    }
    /**
     * print username/password text boxes
     *
     * @return string
     */
    private function printLogonTable()
    {
        $messages = FACTORY_MESSAGES::getFreshInstance();
        $pString = \FORM\formHeader("logon");
        $pString .= \HTML\tableStart('left width50percent');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($messages->text("user", "username") . ":&nbsp;&nbsp;");
        $pString .= \HTML\td(\FORM\textInput(FALSE, "username"));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($messages->text("user", "password") . ":&nbsp;&nbsp;");
        $pString .= \HTML\td(\FORM\passwordInput(FALSE, "password"));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td("&nbsp;");
        $pString .= \HTML\td(\FORM\formSubmit($messages->text("submit", "Submit")), 'right');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * GDPR and/or privacy notification if required
     */
    private function authGate()
    {
        if (($this->config->WIKINDX_AUTHGATE_USE === TRUE) && ($this->session->getVar('setup_UserId') != 1))
        {
            $this->db->formatConditions(['usersId' => $this->session->getVar('setup_UserId')]);
            $recordset = $this->db->select('users', 'usersGDPR');
            if ($this->db->fetchOne($recordset) == 'N')
            {
                $pString = \HTML\p($this->config->WIKINDX_AUTHGATE_MESSAGE);
                $pString .= \FORM\formHeader("authGate");
                $pString .= \HTML\td(\FORM\formSubmit($this->messages->text("submit", "OK")));
                $pString .= \FORM\formEnd();
                GLOBALS::addTplVar('content', $pString);
                FACTORY_CLOSENOMENU::getInstance();
            }
        }
    }
    /**
     * Clear any expired embargoes
     */
    private function clearEmbargoes()
    {
        $this->db->formatConditions('resourceattachmentsEmbargoUntil' . $this->db->less . 'CURRENT_TIMESTAMP');
        $this->db->update('resource_attachments', ['resourceattachmentsEmbargo' => 'N']);
    }
    /**
     * Successful registered user logon so restore some session variables if they've been stored
     */
    private function restoreEnvironment()
    {
        $this->db->formatConditions(['usersId' => $this->session->getVar('setup_UserId')]);
        $state = $this->db->selectFirstField('users', 'usersUserSession');
        if ($state)
        {
            $sessionData = unserialize(base64_decode($state));
            foreach ($sessionData as $key => $array)
            {
                $array = unserialize(base64_decode($array));
                if (!is_array($array))
                {
                    continue;
                }
                foreach ($array as $subKey => $value)
                {
                    $this->session->setVar($key . '_' . $subKey, $value);
                }
            }
        }
        $this->checkNews();
    }
    /**
     * Check for any news items in the database
     */
    private function checkNews()
    {
        $resultset = $this->db->select('news', 'newsId');
        if ($this->db->numRows($resultset))
        {
            $this->session->setVar('setup_News', TRUE);
        }
        else
        {
            $this->session->delVar('setup_News');
        }
    }
    /**
     * log out user
     *
     * Various bits of garbage disposal, session is destroyed, cookie is deleted and user is presented with logon prompt
     */
    private function logout()
    {
        // Garbage disposal
        // remove this session's files
        $dir = WIKINDX_DIR_DATA_FILES;
        if ($sessVar = $this->session->getVar('FileExports'))
        {
            $sessArray = unserialize($sessVar);
            foreach (\FILE\fileInDirToArray($dir) as $f)
            {
                if (array_search($f, $sessArray) === FALSE)
                {
                    continue;
                }
                $file = $dir . DIRECTORY_SEPARATOR . $f;
                unlink($file);
            }
            //			$this->session->delVar('fileExports');
        }
        if ($sessVar = $this->session->getVar('PaperExports'))
        {
            $sessArray = unserialize($sessVar);
            foreach (\FILE\fileInDirToArray($dir) as $f)
            {
                if (!array_key_exists($f, $sessArray))
                {
                    continue;
                }
                $file = $dir . DIRECTORY_SEPARATOR . $f;
                unlink($file);
            }
            //			$this->session->delVar('paperExports');
        }
        $this->session->destroy();
        // set the default language prior to displaying the login prompt
        $row = $this->configDbStructure->getData('configLanguage');
        if (empty($row))
        { // perhaps this is a first install or upgrade to >= v4 (v3 has config.language not config.configLanguage)
            $this->session->setVar('setup_Language', "auto");
        }
        else
        {
            $user = FACTORY_USER::getInstance();
            // populate session with default values from config
            $user->writeSessionPreferences(FALSE, 'config');
        }
        // remove any wikindx cookie that has been set
        $cookie = FACTORY_COOKIE::getInstance();
        $cookie->deleteCookie();
        if ($this->config->WIKINDX_DENY_READONLY)
        {
            $this->initLogon(); // login prompt
            FACTORY_CLOSENOMENU::getInstance();
        }
        else
        { // send back to front page
            $this->session->setVar('setup_ReadOnly', TRUE);
            include_once("core/display/FRONT.php");
            $front = new FRONT('');
            unset($front);
            FACTORY_CLOSE::getInstance();
        }
    }
    /**
     * failure
     *
     * In case of failure, Exit back to logon prompt with optional error message
     *
     * @param mixed $error Default is FALSE
     */
    private function failure($error = FALSE)
    {
        if (!$error && ($sessionError = $this->session->getVar('misc_ErrorMessage')))
        {
            $error = $sessionError;
            $this->session->delVar('misc_ErrorMessage');
        }
        // Exit back to logon prompt
        FACTORY_CLOSENOMENU::getInstance($this->initLogon($error));
    }
}