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
    private $errors;
    /** object */
    private $success;
    /** array */
    private $vars;

    /**
     *	AUTHORIZE
     */
    public function __construct()
    {
        $this->session = FACTORY_SESSION::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        if (!GLOBALS::getUserVar("Language"))
        {
            $configDbStructure = FACTORY_CONFIGDBSTRUCTURE::getInstance();
            $cnf = $configDbStructure->getData('configLanguage');
            GLOBALS::setUserVar('Language', $cnf['configLanguage']);
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
        if (array_key_exists("action", $this->vars))
        {
            // Logged-on user clicked on 'OK' when asked to confirm GDPR or privacy statement
            if ($this->vars["action"] == 'authGate')
            {
                $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
                $this->db->update('users', ['usersGDPR' => 'Y']);
                // FALSE means go to front of WIKINDX
                return FALSE;
            }
            // User requesting readOnly access - clear previous sessions
            if ($this->vars["action"] == 'readOnly')
            {
                $this->readOnly();
            }
            // User requesting logout - clear previous sessions
            elseif ($this->vars["action"] == 'logout')
            {
                // First delete any pre-existing session in case this user has been logging on and off as different users --
                // keep template and language etc.
                $this->logout(); // login prompt
                FACTORY_CLOSENOMENU::getInstance();
            }
            // User logging in from readOnly mode
            elseif ($this->vars["action"] == 'initLogon')
            {
                // First delete any pre-existing session in case this user has been logging on and off as different users --
                // keep template and language etc.
                $this->initLogon(); // login prompt
                FACTORY_CLOSENOMENU::getInstance();
            }
            if (
                array_key_exists('method', $this->vars) &&
                (($this->vars["method"] == 'forgetInitStage1') ||
                ($this->vars["method"] == 'forgetInitStage2') ||
                ($this->vars["method"] == 'forgetProcess'))
            ) {
                return TRUE;
            }
            // User supplying username and password to logon to WIKINDX.
            // $auth->logonCheck() dies after printing logon screen if bad comparison.
            elseif (
                ($this->vars["action"] == 'logon')
                && array_key_exists("password", $this->vars)
                && array_key_exists("usersUsername", $this->vars)
            ) {
                $this->logonCheck($this->vars['usersUsername'], $this->vars['password']);
                // tidy up old files
                FILE\tidyFiles();
// TODO: Delete at some point in the future . . .
                $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
				$resultSet2 = $this->db->select('users', 'usersLastInternalVersion');
				if ($this->db->fetchOne($resultSet2) < 51) { // See UPDATEDATABASE::upgradeTo51()
					$this->session->clearArray("sql");
					$this->session->clearArray("bookmark");
				}
                $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
	        	$this->db->update('users', ['usersLastInternalVersion' => WIKINDX_INTERNAL_VERSION]);
                // FALSE means go to front of WIKINDX
                return FALSE;
            }
            // superAdmin already logged in after upgrade so just set up the environment
            elseif ($this->vars["action"] == 'upgradeDB')
            {
                $user = FACTORY_USER::getInstance();
                $this->session->clearSessionData();
                $this->session->setVar("setup_UserId", WIKINDX_SUPERADMIN_ID); // superAdmin always id = WIKINDX_SUPERADMIN_ID
                $this->session->setVar("setup_Write", TRUE);
                $this->session->delVar("setup_ReadOnly");
                $user->writeSessionPreferences(FALSE);

                return FALSE;
            }
            // User registration
            elseif (WIKINDX_MULTIUSER && WIKINDX_USER_REGISTRATION && WIKINDX_MAIL_USE)
            {
                include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "modules", "usersgroups", "REGISTER.php"]));
                $obj = new REGISTER();
                if ($this->vars["action"] == 'initRegisterUser')
                {
                    $obj->initRegister();
                    FACTORY_CLOSENOMENU::getInstance();
                }
                elseif (array_key_exists('method', $this->vars) && $this->vars["method"] == 'registerConfirm')
                {
                    GLOBALS::addTplVar('content', $obj->registerConfirm());
                    FACTORY_CLOSENOMENU::getInstance();
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
                    $obj->registerRequest();
                }
            }
        }
        if (isset($this->vars["method"]) && ($this->vars['method'] == 'RSS') && !WIKINDX_DENY_READONLY)
        {
            $this->session->setVar("setup_ReadOnly", TRUE);

            return TRUE;
        }
        // access already granted
        if ($this->session->getVar("setup_Write"))
        {
            return TRUE;
        }
        // access already granted
        if ($this->session->getVar("setup_ReadOnly"))
        {
            return TRUE;
        }
        if ((!array_key_exists('action', $this->vars) || $this->vars['action'] != 'upgradeDBLogon'))
        {
            $cookie = FACTORY_COOKIE::getInstance();
            // grabCookie() returns TRUE if valid cookie - otherwise, proceed to manual logon
            if ($cookie->grabCookie())
            {
                return TRUE;
            }
        }
        if (!$this->session->getVar("setup_Write") && !$this->session->getVar("setup_ReadOnly"))
        {
            // Default == read only access.
            if (WIKINDX_READ_ONLY_ACCESS && !WIKINDX_DENY_READONLY)
            {
                $this->session->setVar("setup_ReadOnly", TRUE);
                // populate session with default values from config
                $user = FACTORY_USER::getInstance();
                $user->writeSessionPreferences(FALSE);
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
     * @param string $message Default is FALSE
     */
    public function initLogon($message = FALSE)
    {
        $this->session->delVar("setup_ReadOnly");
        $messages = FACTORY_MESSAGES::getFreshInstance();
        GLOBALS::setTplVar('heading', $messages->text("heading", "logon"));
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        if (!WIKINDX_MULTIUSER)
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
        if (WIKINDX_RESTRICT_USERID != WIKINDX_RESTRICT_USERID_DEFAULT)
        {
            $pString .= \HTML\p("For test drive purposes, " .
                \HTML\strong($messages->text("user", "username") . ":&nbsp;&nbsp;") . "wikindx, " .
                \HTML\strong($messages->text("user", "password") . ":&nbsp;&nbsp;") . "Wikindx");
        }
        $forgot = WIKINDX_MAIL_USE ? \HTML\a("link", $messages->text("user", "forget6"), $link2) : FALSE;
        $pString .= $this->printLogonTable();
        // Give user the option to bypass logging in simply to read.
        if (!WIKINDX_DENY_READONLY)
        {
            $links = \HTML\a("link", $messages->text("authorize", "readOnly") . BR . $forgot, $link1);
        }
        else
        {
            $links = $forgot;
        }
        if (WIKINDX_USER_REGISTRATION && WIKINDX_MULTIUSER && WIKINDX_MAIL_USE)
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
            $error = FACTORY_ERRORS::getInstance();
            $this->failure("inputError_invalid");
        }
        // Success
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

        switch ($pluginAuthLevelRequested) {
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
        $pString .= \HTML\td(\FORM\textInput(FALSE, "usersUsername"));
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
        if ((WIKINDX_AUTHGATE_USE === TRUE) && ($this->session->getVar("setup_UserId") != WIKINDX_SUPERADMIN_ID))
        {
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $recordset = $this->db->select('users', 'usersGDPR');
            if ($this->db->fetchOne($recordset) == 'N')
            {
                $pString = \HTML\p(WIKINDX_AUTHGATE_MESSAGE);
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
     * Check for any news items in the database
     */
    private function checkNews()
    {
        $resultset = $this->db->select('news', 'newsId');
        if ($this->db->numRows($resultset))
        {
            $this->session->setVar("setup_News", TRUE);
        }
        else
        {
            $this->session->delVar("setup_News");
        }
    }
    /**
     * log out user
     *
     * Various bits of garbage disposal, session is destroyed, cookie is deleted and user is presented with logon prompt
     */
    private function readOnly()
    {
        // Garbage disposal
        // remove this session's files
        $dir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]);
        if ($sessArray = $this->session->getVar("fileExports"))
        {
            foreach (\FILE\fileInDirToArray($dir) as $f)
            {
                if (array_search($f, $sessArray) === FALSE)
                {
                    continue;
                }
                $file = $dir . DIRECTORY_SEPARATOR . $f;
                unlink($file);
            }
            //			$this->session->delVar("fileExports");
        }
        if ($sessArray = $this->session->getVar("wp_PaperExports"))
        {
            foreach (\FILE\fileInDirToArray($dir) as $f)
            {
                if (!array_key_exists($f, $sessArray))
                {
                    continue;
                }
                $file = $dir . DIRECTORY_SEPARATOR . $f;
                unlink($file);
            }
            //			$this->session->delVar("paperExports");
        }
        // Store this previous user settings for use below if necessary
        $keys = ["Paging", "PagingMaxLinks", "StringLimit", "Language", "Style", "Template", "PagingTagCloud", "ListLink"];
        foreach ($keys as $key)
        {
            $sessArray[$key] = GLOBALS::getUserVar($key);
        }
        $this->session->destroy();
        // set the default language prior to displaying the login prompt
        $user = FACTORY_USER::getInstance();
        // remove any WIKINDX cookie that has been set
        $cookie = FACTORY_COOKIE::getInstance();
        $cookie->deleteCookie();
        // send back to front page
        // Restore this user's previous user settings (e.g. so language and appearance does not suddenly change to the default from config)
        foreach ($keys as $key)
        {
            $this->session->setVar("setup_" . $key, $sessArray[$key]);
        }
        $this->session->setVar("setup_ReadOnly", TRUE);
        header("Location: index.php");
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
        $dir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]);
        if ($sessArray = $this->session->getVar("fileExports"))
        {
            foreach (\FILE\fileInDirToArray($dir) as $f)
            {
                if (array_search($f, $sessArray) === FALSE)
                {
                    continue;
                }
                $file = $dir . DIRECTORY_SEPARATOR . $f;
                unlink($file);
            }
            //			$this->session->delVar("fileExports");
        }
        if ($sessArray = $this->session->getVar("wp_PaperExports"))
        {
            foreach (\FILE\fileInDirToArray($dir) as $f)
            {
                if (!array_key_exists($f, $sessArray))
                {
                    continue;
                }
                $file = $dir . DIRECTORY_SEPARATOR . $f;
                unlink($file);
            }
            //			$this->session->delVar("paperExports");
        }
        // Store this previous user settings for use below if necessary
        $keys = ["Paging", "PagingMaxLinks", "StringLimit", "Language", "Style", "Template", "PagingTagCloud", "ListLink"];
        foreach ($keys as $key)
        {
            $sessArray[$key] = GLOBALS::getUserVar($key);
        }
        $this->session->destroy();
        // set the default language prior to displaying the login prompt
        $user = FACTORY_USER::getInstance();
        // remove any WIKINDX cookie that has been set
        $cookie = FACTORY_COOKIE::getInstance();
        $cookie->deleteCookie();
        // send back to front page
        // Restore this user's previous user settings (e.g. so language and appearance does not suddenly change to the default from config)
        foreach ($keys as $key)
        {
            $this->session->setVar("setup_" . $key, $sessArray[$key]);
        }
        $this->session->delVar("setup_ReadOnly");
        header("Location: index.php");
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
		// Exit back to logon prompt
        header("Location: index.php?action=initLogon&error=$error");
        die;
    }
}
