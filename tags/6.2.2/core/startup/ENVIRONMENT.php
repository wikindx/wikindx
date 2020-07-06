<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * ENVIRONMENT
 *
 * Set up the WIKINDX server environment
 *
 * @package wikindx\core\startup
 */
class ENVIRONMENT
{
    /** object */
    public $config;

    /**
     * ENVIRONMENT
     */
    public function __construct()
    {
        $this->config = FACTORY_CONFIG::getInstance();
        $this->runConfig();
        $this->checkFolders();
        $this->getVars();
    }

    /**
     * start the SESSION
     */
    public function startSession()
    {
        // Protect from a session already launched by an other page but not well loaded (plugins)
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            session_write_close();
        }
        if (session_status() === PHP_SESSION_NONE)
        {
            ini_set('session.gc_probability', 0);
            // start session
            session_start();
        }
    }
    
    /**
     * Filter function for method GET parameter
     *
     * Method should have the format of an array key of a MESSAGES.php catalog
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function filterMethod($value)
    {
        return preg_replace("/[^A-Za-z0-9]/u", "", $value);
    }
    /**
     * Make sure we get HTTP VARS in whatever format they come in
     *
     * Use $vars = GLOBALS::getVars(); to get querystrings and form elements
     */
    private function getVars()
    {
        if (!empty($_POST))
        {
            $vars = $_POST;
        }
        elseif (!empty($_GET))
        {
            $vars = $_GET;
        }
        else
        {
            return FALSE;
        }

        $dirtyVars = $vars;

        $cleanVars = array_map([$this, "stripHtmlTags"], $dirtyVars);
        
        if (array_key_exists('action', $cleanVars) && ($cleanVars['action'] == 'noMenu' || $cleanVars['action'] == 'noSubMenu'))
        {
            if (array_key_exists('method', $cleanVars))
            {
                $method = trim($cleanVars['method']);
                $method = filter_var($method, FILTER_CALLBACK, ['options' => [$this, "filterMethod"]]);
                $cleanVars['method'] = $method;
            }
        }
        
        // Store globally
        GLOBALS::setVars($cleanVars, $dirtyVars);
    }
    /**
     * Check and load configuration from config.php
     * From WIKINDX v5.3 on, we have transferred many variables from config.php to the database.
     * After checking for basic MYSQL and PATH variables that remain in config.php, we decide whether to check
     * further variables in this function (i.e. we are in the process of upgrading to >= v5.3) or to return.
     * If we return, the checks below are carried out in LOADCONFIG::checkConfigValidity().
     */
    private function runConfig()
    {
        $dieMsgMissing = 'Missing configuration variable in config.php: ';
        // Set base url (default if needed)
        if (!property_exists($this->config, 'WIKINDX_BASE_URL') || !$this->config->WIKINDX_BASE_URL)
        {
            $this->config->WIKINDX_BASE_URL = FACTORY_URL::getInstance()->getBaseUrl();
        }
        elseif (!is_string($this->config->WIKINDX_BASE_URL))
        {
            die('WIKINDX_BASE_URL must be a valid URL');
        }
        elseif (trim($this->config->WIKINDX_BASE_URL) == '')
        {
            die('WIKINDX_BASE_URL must be a valid URL');
        }
        // Replace dynamically the protocol defined by config.php by the protocol requested (http or https)
        $this->config->WIKINDX_BASE_URL = preg_replace('/^https?/u', FACTORY_URL::getInstance()->getCurrentProtocole(), $this->config->WIKINDX_BASE_URL);

        // Set database hostname
        if (!property_exists($this->config, 'WIKINDX_DB_HOST'))
        {
            die($dieMsgMissing . 'WIKINDX_DB_HOST');
        }
        elseif (!is_string($this->config->WIKINDX_DB_HOST))
        {
            die('WIKINDX_DB_HOST must be a string.');
        }

        // Set database name
        if (!property_exists($this->config, 'WIKINDX_DB'))
        {
            die($dieMsgMissing . 'WIKINDX_DB');
        }
        elseif (!is_string($this->config->WIKINDX_DB))
        {
            die('WIKINDX_DB must be a string.');
        }

        // Set database user
        if (!property_exists($this->config, 'WIKINDX_DB_USER'))
        {
            die($dieMsgMissing . 'WIKINDX_DB_USER');
        }
        elseif (!is_string($this->config->WIKINDX_DB_USER))
        {
            die('WIKINDX_DB_USER must be a string.');
        }

        // Set database user password
        if (!property_exists($this->config, 'WIKINDX_DB_PASSWORD'))
        {
            die($dieMsgMissing . 'WIKINDX_DB_PASSWORD');
        }
        elseif (!is_string($this->config->WIKINDX_DB_PASSWORD))
        {
            die('WIKINDX_DB_PASSWORD must be a string.');
        }

        // Set database table prefix
        if (!property_exists($this->config, 'WIKINDX_DB_TABLEPREFIX'))
        {
            die($dieMsgMissing . 'WIKINDX_DB_TABLEPREFIX');
        }
        elseif (!is_string($this->config->WIKINDX_DB_TABLEPREFIX))
        {
            die('WIKINDX_DB_TABLEPREFIX must be a string.');
        }
        // Use always a lowercase prefix to prevent problem with case sensitive database
        $this->config->WIKINDX_DB_TABLEPREFIX = mb_strtolower($this->config->WIKINDX_DB_TABLEPREFIX);
        
        // This option is deprecated from version 5.9.1
        if ($this->config->WIKINDX_DB_TABLEPREFIX != WIKINDX_DB_TABLEPREFIX_DEFAULT)
        {
            if (property_exists($this->config, 'WIKINDX_DEBUG_ERRORS') && $this->config->WIKINDX_DEBUG_ERRORS)
            {
                trigger_error(
                    "\$WIKINDX_DB_TABLEPREFIX configuration option is deprecated since version 5.9.1
        			and will be removed in the next release. People who have changed the prefix should
        			rename the tables with the default prefix (" . WIKINDX_DB_TABLEPREFIX_DEFAULT . ")
        			and correct their configuration. It will no longer be possible to install two WIKINDXs
        			in the same database. If you are in this rare case contact us.",
                    E_USER_DEPRECATED
                );
            }
        }
        
        // Force WIKINDX_DB_TABLEPREFIX to always be equal to WIKINDX_DB_TABLEPREFIX_DEFAULT (wkx_) after v6.3.3
        $this->config->WIKINDX_DB_TABLEPREFIX = WIKINDX_DB_TABLEPREFIX_DEFAULT;

        // Set database persistent mode
        if (!property_exists($this->config, 'WIKINDX_DB_PERSISTENT'))
        {
            die($dieMsgMissing . 'WIKINDX_DB_PERSISTENT');
        }
        elseif (!is_bool($this->config->WIKINDX_DB_PERSISTENT))
        {
            die('WIKINDX_DB_PERSISTENT must be a boolean (TRUE / FALSE).');
        }

        // Attempt to set the memory the script uses -- does not work in safe mode
        if (!property_exists($this->config, 'WIKINDX_MEMORY_LIMIT'))
        {
            $this->config->WIKINDX_MEMORY_LIMIT = WIKINDX_MEMORY_LIMIT_DEFAULT;
        }
        elseif (is_string($this->config->WIKINDX_MEMORY_LIMIT))
        {
            if (preg_match('/^\d+[KMG]?$/u', $this->config->WIKINDX_MEMORY_LIMIT) === FALSE)
            {
                die('Syntax Error in WIKINDX_MEMORY_LIMIT. See https://secure.php.net/manual/fr/faq.using.php#faq.using.shorthandbytes');
            }
            elseif (is_int($this->config->WIKINDX_MEMORY_LIMIT))
            {
                if ($this->config->WIKINDX_MEMORY_LIMIT < -1)
                {
                    die('WIKINDX_MEMORY_LIMIT must be a positive integer.');
                }
            }
        }
        ini_set("memory_limit", $this->config->WIKINDX_MEMORY_LIMIT);

        // Attempt to set the max time the script runs for -- does not work in safe mode
        if (!property_exists($this->config, 'WIKINDX_MAX_EXECUTION_TIMEOUT'))
        {
            $this->config->WIKINDX_MAX_EXECUTION_TIMEOUT = WIKINDX_MAX_EXECUTION_TIMEOUT_DEFAULT;
        }
        elseif (is_string($this->config->WIKINDX_MAX_EXECUTION_TIMEOUT))
        { // v4 config.php required quotes around value
            if (!$this->config->WIKINDX_MAX_EXECUTION_TIMEOUT = intval($this->config->WIKINDX_MAX_EXECUTION_TIMEOUT))
            {
                die('WIKINDX_MAX_EXECUTION_TIMEOUT must be a positive integer (or FALSE for default configuration of PHP).');
            }
        }
        elseif (!is_int($this->config->WIKINDX_MAX_EXECUTION_TIMEOUT))
        {
            if ($this->config->WIKINDX_MAX_EXECUTION_TIMEOUT !== FALSE)
            {
                die('WIKINDX_MAX_EXECUTION_TIMEOUT must be a positive integer (or FALSE for default configuration of PHP).');
            }
            elseif ($this->config->WIKINDX_MAX_EXECUTION_TIMEOUT < 0)
            {
                die('WIKINDX_MAX_EXECUTION_TIMEOUT must be a positive integer (or FALSE for default configuration of PHP).');
            }
        }
        // Configure it only if explicitely defined
        if ($this->config->WIKINDX_MAX_EXECUTION_TIMEOUT !== FALSE)
        {
            ini_set("max_execution_time", $this->config->WIKINDX_MAX_EXECUTION_TIMEOUT);
        }

        // Set max write chunk for file writing
        if (!property_exists($this->config, 'WIKINDX_MAX_WRITECHUNK'))
        {
            $this->config->WIKINDX_MAX_WRITECHUNK = WIKINDX_MAX_WRITECHUNK_DEFAULT;
        }
        elseif (!is_int($this->config->WIKINDX_MAX_WRITECHUNK))
        {
            if ($this->config->WIKINDX_MAX_WRITECHUNK !== FALSE)
            {
                die('WIKINDX_MAX_WRITECHUNK must be a positive integer (or FALSE for default configuration).');
            }
            else
            {
                $this->config->WIKINDX_MAX_WRITECHUNK = WIKINDX_MAX_WRITECHUNK_DEFAULT;
            }
        }
        elseif ($this->config->WIKINDX_MAX_WRITECHUNK < 1)
        {
            die('WIKINDX_MAX_WRITECHUNK must be a positive integer (or FALSE for default configuration).');
        }

        // Set a boolean flag to activate debugging or experimental features of the trunk
        if (!property_exists($this->config, 'WIKINDX_TRUNK_VERSION'))
        {
            $this->config->WIKINDX_TRUNK_VERSION = WIKINDX_TRUNK_VERSION_DEFAULT;
        }
        elseif (!is_bool($this->config->WIKINDX_TRUNK_VERSION))
        {
            die('WIKINDX_TRUNK_VERSION must be a boolean (TRUE / FALSE).');
        }

        ////////////////////////
        // The above are the basic variables in config.php with WIKINDX >= v5.3
        // We now check if we are indeed >= v5.3
        ////////////////////////
        if (WIKINDX_INTERNAL_VERSION >= 5.3)
        {
            return;
        }
        ////////////////////////
        // Everything else here is only used when upgrading to >= v5.3. After upgrading, the checks below are carried out in LOADCONFIG::checkConfigValidity().
        ////////////////////////

        // Set timezone (default if needed)
        if (!property_exists($this->config, 'WIKINDX_TIMEZONE'))
        {
            $this->config->WIKINDX_TIMEZONE = WIKINDX_TIMEZONE_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_TIMEZONE))
        {
            die('WIKINDX_TIMEZONE must be a valid PHP time zone. See https://secure.php.net/manual/fr/timezones.php');
        }
        elseif (trim($this->config->WIKINDX_TIMEZONE) == '')
        {
            die('WIKINDX_TIMEZONE must be a valid PHP time zone. See https://secure.php.net/manual/fr/timezones.php');
        }

        date_default_timezone_set($this->config->WIKINDX_TIMEZONE);

        // Special userId (FALSE or userID from database). Used on the test wikindx to stop this write-enabled user changing login details
        if (!property_exists($this->config, 'WIKINDX_RESTRICT_USERID'))
        {
            $this->config->WIKINDX_RESTRICT_USERID = WIKINDX_RESTRICT_USERID_DEFAULT;
        }

        // Set resource type
        if (!property_exists($this->config, 'WIKINDX_DEACTIVATE_RESOURCE_TYPES'))
        {
            $this->config->WIKINDX_DEACTIVATE_RESOURCE_TYPES = [];
        }
        elseif (!is_array($this->config->WIKINDX_DEACTIVATE_RESOURCE_TYPES))
        {
            die('WIKINDX_DEACTIVATE_RESOURCE_TYPES must be an array.');
        }

        // Set number of items in RSS feed
        if (!property_exists($this->config, 'WIKINDX_RSS_LIMIT'))
        {
            $this->config->WIKINDX_RSS_LIMIT = WIKINDX_RSS_LIMIT_DEFAULT;
        }
        elseif (!is_int($this->config->WIKINDX_RSS_LIMIT))
        {
            die('WIKINDX_RSS_LIMIT must be > 0.');
        }
        elseif ($this->config->WIKINDX_RSS_ALLOW && $this->config->WIKINDX_RSS_LIMIT < 1)
        {
            die('WIKINDX_RSS_LIMIT must be > 0.');
        }

        // Set bibliographic style of RSS feed
        if (!property_exists($this->config, 'WIKINDX_RSS_BIBSTYLE'))
        {
            $this->config->WIKINDX_RSS_BIBSTYLE = WIKINDX_RSS_BIBSTYLE_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_RSS_BIBSTYLE))
        {
            die('WIKINDX_RSS_BIBSTYLE must be an existing bibliographic style name.');
        }

        // Set RSS feed title
        if (!property_exists($this->config, 'WIKINDX_RSS_TITLE'))
        {
            $this->config->WIKINDX_RSS_TITLE = WIKINDX_RSS_TITLE_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_RSS_TITLE))
        {
            die('WIKINDX_RSS_TITLE must be a string.');
        }

        // Set RSS feed description
        if (!property_exists($this->config, 'WIKINDX_RSS_DESCRIPTION'))
        {
            $this->config->WIKINDX_RSS_DESCRIPTION = WIKINDX_RSS_DESCRIPTION_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_RSS_DESCRIPTION))
        {
            die('WIKINDX_RSS_DESCRIPTION must be a string.');
        }

        // Set RSS feed language
        if (!property_exists($this->config, 'WIKINDX_RSS_LANGUAGE'))
        {
            $this->config->WIKINDX_RSS_LANGUAGE = WIKINDX_LANGUAGE_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_RSS_LANGUAGE))
        {
            die('WIKINDX_RSS_LANGUAGE must be a legal language code (en_GB, fr_FR, de_DE...).');
        }

        // Set mailer configuration
        if (!property_exists($this->config, 'WIKINDX_MAIL_SERVER'))
        {
            $this->config->WIKINDX_MAIL_SERVER = WIKINDX_MAIL_SERVER_DEFAULT;
        }
        elseif (!is_bool($this->config->WIKINDX_MAIL_SERVER))
        {
            die('WIKINDX_MAIL_SERVER must be a boolean (TRUE / FALSE).');
        }

        // Set email from header
        if (!property_exists($this->config, 'WIKINDX_MAIL_FROM'))
        {
            $this->config->WIKINDX_MAIL_FROM = WIKINDX_MAIL_FROM_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_MAIL_FROM))
        {
            if ($this->config->WIKINDX_MAIL_FROM !== FALSE)
            {
                die('WIKINDX_MAIL_FROM must be a string or FALSE.');
            }
            elseif (trim($this->config->WIKINDX_MAIL_FROM == ''))
            {
                $this->config->WIKINDX_MAIL_FROM = WIKINDX_MAIL_FROM_DEFAULT;
            }
        }

        // Set email reply-to header
        if (!property_exists($this->config, 'WIKINDX_MAIL_REPLYTO'))
        {
            $this->config->WIKINDX_MAIL_REPLYTO = WIKINDX_MAIL_REPLYTO_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_MAIL_REPLYTO))
        {
            if ($this->config->WIKINDX_MAIL_REPLYTO !== FALSE)
            {
                die('WIKINDX_MAIL_REPLYTO must be a string or FALSE.');
            }
            elseif (trim($this->config->WIKINDX_MAIL_REPLYTO == ''))
            {
                $this->config->WIKINDX_MAIL_REPLYTO = WIKINDX_MAIL_REPLYTO_DEFAULT;
            }
        }

        // Set email path return header
        if (!property_exists($this->config, 'WIKINDX_MAIL_RETURN_PATH'))
        {
            $this->config->WIKINDX_MAIL_RETURN_PATH = WIKINDX_MAIL_RETURN_PATH_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_MAIL_RETURN_PATH))
        {
            if ($this->config->WIKINDX_MAIL_RETURN_PATH !== FALSE)
            {
                die('WIKINDX_MAIL_RETURN_PATH must be a string or FALSE.');
            }
            elseif (trim($this->config->WIKINDX_MAIL_RETURN_PATH == ''))
            {
                $this->config->WIKINDX_MAIL_RETURN_PATH = WIKINDX_MAIL_RETURN_PATH_DEFAULT;
            }
        }

        // Set MAIL backend
        if (!property_exists($this->config, 'WIKINDX_MAIL_BACKEND'))
        {
            $this->config->WIKINDX_MAIL_BACKEND = WIKINDX_MAIL_BACKEND_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_MAIL_BACKEND))
        {
            die('WIKINDX_MAIL_BACKEND must be of this value: ' . implode(', ', ['smtp', 'sendmail', 'mail']));
        }
        elseif (!in_array($this->config->WIKINDX_MAIL_BACKEND, ['smtp', 'sendmail', 'mail']))
        {
            die('WIKINDX_MAIL_BACKEND must be of this value: ' . implode(', ', ['smtp', 'sendmail', 'mail']));
        }
        elseif ($this->config->WIKINDX_MAIL_BACKEND == 'mail' && !in_array('mail', explode(',', ini_get('disable_functions'))))
        {
            if (property_exists($this->config, 'WIKINDX_DEBUG_ERRORS') && $this->config->WIKINDX_DEBUG_ERRORS)
            {
                trigger_error(
                    "Mail backend unavailable [configMailBackend/WIKINDX_MAIL_BACKEND] : mail() function is disabled in the configuration of PHP.",
                    E_USER_NOTICE
                );
            }
        }

        // Set sendmail path
        if (!property_exists($this->config, 'WIKINDX_MAIL_SMPATH'))
        {
            $this->config->WIKINDX_MAIL_SMPATH = WIKINDX_MAIL_SMPATH_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_MAIL_SMPATH))
        {
            die('WIKINDX_MAIL_SMPATH must be a string.');
        }

        // Set sendmail optional parameters
        if (!property_exists($this->config, 'WIKINDX_MAIL_SMARGS'))
        {
            $this->config->WIKINDX_MAIL_SMARGS = WIKINDX_MAIL_SMARGS_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_MAIL_SMARGS))
        {
            die('WIKINDX_MAIL_SMARGS must be a string.');
        }

        // Set smtp hostname
        if (!property_exists($this->config, 'WIKINDX_MAIL_SMTPSERVER'))
        {
            $this->config->WIKINDX_MAIL_SMTPSERVER = WIKINDX_MAIL_SMTPSERVER_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_MAIL_SMTPSERVER))
        {
            die('WIKINDX_MAIL_SMTPSERVER must be a string.');
        }

        // Set smtp port
        if (!property_exists($this->config, 'WIKINDX_MAIL_SMTPPORT'))
        {
            $this->config->WIKINDX_MAIL_SMTPPORT = WIKINDX_MAIL_SMTPPORT_DEFAULT;
        }
        elseif (is_int($this->config->WIKINDX_MAIL_SMTPPORT))
        {
            if ($this->config->WIKINDX_MAIL_SMTPPORT < 0)
            {
                die('WIKINDX_MAIL_SMTPPORT must be a positive integer.');
            }
            elseif (!is_string($this->config->WIKINDX_MAIL_SMTPPORT))
            {
                die('WIKINDX_MAIL_SMTPPORT must be a positive integer.');
            }
            elseif (!preg_match('/^\d+$/u', $this->config->WIKINDX_MAIL_SMTPPORT))
            {
                die('WIKINDX_MAIL_SMTPPORT must be a positive integer.');
            }
        }

        // Set smtp encryption
        if (!property_exists($this->config, 'WIKINDX_MAIL_SMTPENCRYPT'))
        {
            $this->config->WIKINDX_MAIL_SMTPENCRYPT = WIKINDX_MAIL_SMTPENCRYPT_DEFAULT;
        }
        // No encryption stored in the database as 'none' Рensure this is an empty string
        elseif ($this->config->WIKINDX_MAIL_SMTPENCRYPT == 'none')
        {
            $this->config->WIKINDX_MAIL_SMTPENCRYPT = '';
        }
        elseif (!is_string($this->config->WIKINDX_MAIL_SMTPENCRYPT))
        {
            die('WIKINDX_MAIL_SMTPENCRYPT must be of this value: ' . implode(', ', ['tls', 'ssl', 'or an empty string']));
        }
        elseif (!in_array($this->config->WIKINDX_MAIL_SMTPENCRYPT, ['', 'tls', 'ssl']))
        {
            die('WIKINDX_MAIL_SMTPENCRYPT must be of this value: ' . implode(', ', ['tls', 'ssl', 'or an empty string']));
        }
        elseif ($this->config->WIKINDX_MAIL_SMTPENCRYPT == '')
        {
            $this->config->WIKINDX_MAIL_SMTPENCRYPT == FALSE;
        }

        // Set smtp persist
        if (!property_exists($this->config, 'WIKINDX_MAIL_SMTPPERSIST'))
        {
            $this->config->WIKINDX_MAIL_SMTPPERSIST = WIKINDX_MAIL_SMTPPERSIST_DEFAULT;
        }
        elseif (!is_bool($this->config->WIKINDX_MAIL_SMTPPERSIST))
        {
            die('WIKINDX_MAIL_SMTPPERSIST must be a boolean (TRUE / FALSE).');
        }

        // Set smtp user
        if (!property_exists($this->config, 'WIKINDX_MAIL_SMTPUSERNAME'))
        {
            $this->config->WIKINDX_MAIL_SMTPUSERNAME = WIKINDX_MAIL_SMTPUSERNAME_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_MAIL_SMTPUSERNAME))
        {
            die('WIKINDX_MAIL_SMTPUSERNAME must be a string.');
        }

        // Set smtp password
        if (!property_exists($this->config, 'WIKINDX_MAIL_SMTPPASSWORD'))
        {
            $this->config->WIKINDX_MAIL_SMTPPASSWORD = WIKINDX_MAIL_SMTPPASSWORD_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_MAIL_SMTPPASSWORD))
        {
            die('WIKINDX_MAIL_SMTPPASSWORD must be a string.');
        }

        // Enable / disable GS attachments
        if (!property_exists($this->config, 'WIKINDX_GS_ATTACHMENT'))
        {
            $this->config->WIKINDX_GS_ATTACHMENT = WIKINDX_GS_ATTACHMENT_DEFAULT;
        }
        elseif (!is_bool($this->config->WIKINDX_GS_ATTACHMENT))
        {
            die('WIKINDX_GS_ATTACHMENT must be a boolean (TRUE / FALSE).');
        }

        // Enable / disable CMS
        if (!property_exists($this->config, 'WIKINDX_CMS_ALLOW'))
        {
            $this->config->WIKINDX_CMS_ALLOW = WIKINDX_CMS_ALLOW_DEFAULT;
        }
        elseif (!is_bool($this->config->WIKINDX_CMS_ALLOW))
        {
            die('WIKINDX_CMS_ALLOW must be a boolean (TRUE / FALSE).');
        }

        // Set bibliographic style of CMS
        if (!property_exists($this->config, 'WIKINDX_CMS_BIBSTYLE'))
        {
            if ($this->config->WIKINDX_CMS_ALLOW)
            {
                die($dieMsgMissing . 'WIKINDX_CMS_BIBSTYLE');
            }
            else
            {
                $this->config->WIKINDX_CMS_BIBSTYLE = WIKINDX_CMS_BIBSTYLE_DEFAULT;
            }
        }
        elseif (!is_string($this->config->WIKINDX_CMS_BIBSTYLE))
        {
            die('WIKINDX_CMS_BIBSTYLE must an existing bibliographic style name.');
        }

        // Enable / disable CMS database access
        if (!property_exists($this->config, 'WIKINDX_CMS_SQL'))
        {
            if ($this->config->WIKINDX_CMS_ALLOW)
            {
                die($dieMsgMissing . 'WIKINDX_CMS_SQL');
            }
            else
            {
                $this->config->WIKINDX_CMS_SQL = WIKINDX_CMS_SQL_DEFAULT;
            }
        }
        elseif (!is_bool($this->config->WIKINDX_CMS_SQL))
        {
            die('WIKINDX_CMS_SQL must be a boolean (TRUE / FALSE).');
        }

        // Set CMS database user
        if (!property_exists($this->config, 'WIKINDX_CMS_DB_USER'))
        {
            if ($this->config->WIKINDX_CMS_SQL)
            {
                die($dieMsgMissing . 'WIKINDX_CMS_DB_USER');
            }
            else
            {
                $this->config->WIKINDX_CMS_DB_USER = WIKINDX_CMS_DB_USER_DEFAULT;
            }
        }
        elseif (!is_string($this->config->WIKINDX_CMS_DB_USER))
        {
            die('WIKINDX_CMS_DB_USER must be a string.');
        }

        // Set CMS database password
        if (!property_exists($this->config, 'WIKINDX_CMS_DB_PASSWORD'))
        {
            if ($this->config->WIKINDX_CMS_SQL)
            {
                die($dieMsgMissing . 'WIKINDX_CMS_DB_PASSWORD');
            }
            else
            {
                $this->config->WIKINDX_CMS_DB_PASSWORD = WIKINDX_CMS_DB_PASSWORD_DEFAULT;
            }
        }
        elseif (!is_string($this->config->WIKINDX_CMS_DB_PASSWORD))
        {
            die('WIKINDX_CMS_DB_PASSWORD must be a string.');
        }

        // Set Tag cloud low frequency color
        if (!property_exists($this->config, 'WIKINDX_TAG_LOW_COLOUR'))
        {
            $this->config->WIKINDX_TAG_LOW_COLOUR = WIKINDX_TAG_LOW_COLOUR_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_TAG_LOW_COLOUR))
        {
            die('WIKINDX_TAG_LOW_COLOUR must be a valid HTML color (e.g. CCCCCC, gray).');
        }

        // Set Tag cloud high frequency color
        if (!property_exists($this->config, 'WIKINDX_TAG_HIGH_COLOUR'))
        {
            $this->config->WIKINDX_TAG_HIGH_COLOUR = WIKINDX_TAG_HIGH_COLOUR_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_TAG_HIGH_COLOUR))
        {
            die('WIKINDX_TAG_HIGH_COLOUR must be a valid HTML color (e.g. FF0000, red).');
        }

        // Set Tag cloud low frequency size
        if (!property_exists($this->config, 'WIKINDX_TAG_LOW_FACTOR'))
        {
            $this->config->WIKINDX_TAG_LOW_FACTOR = WIKINDX_TAG_LOW_FACTOR_DEFAULT;
        }
        elseif (!is_float($this->config->WIKINDX_TAG_LOW_FACTOR) && !is_int($this->config->WIKINDX_TAG_LOW_FACTOR))
        {
            die('WIKINDX_TAG_LOW_FACTOR must be a number.');
        }

        // Set Tag cloud low frequency size
        if (!property_exists($this->config, 'WIKINDX_TAG_HIGH_FACTOR'))
        {
            $this->config->WIKINDX_TAG_HIGH_FACTOR = WIKINDX_TAG_HIGH_FACTOR_DEFAULT;
        }
        elseif (!is_float($this->config->WIKINDX_TAG_HIGH_FACTOR) && !is_int($this->config->WIKINDX_TAG_HIGH_FACTOR))
        {
            die('WIKINDX_TAG_HIGH_FACTOR must be a number.');
        }

        // Set image max size
        if (!property_exists($this->config, 'WIKINDX_IMAGES_MAXSIZE'))
        {
            $this->config->WIKINDX_IMAGES_MAXSIZE = WIKINDX_IMAGES_MAXSIZE_DEFAULT;
        }
        elseif (!is_int($this->config->WIKINDX_IMAGES_MAXSIZE))
        {
            die('WIKINDX_IMAGES_MAXSIZE must be a positive integer (in MB).');
        }
    }
    /**
     * Strip some HTML tags from string.
     *
     * Since adding tiny_mce, we only strip javascript and the enclosing <p> tag tinymce adds
     *
     * @param string $element
     *
     * @return string
     */
    private function stripHtmlTags($element)
    {
        if (is_array($element))
        {
            return array_map([$this, "stripHtmlTags"], $element);
        }
        else
        {
            /*
            $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
               '@<style[^>]*?>.*?</style>@siU',                // Strip style tags properly
               '@<[\/\!]*?[^<>]*?>@si',                        // Strip out HTML tags
               '@<![\s\S]*?--[ \t\n\r]*>@'                     // Strip multi-line comments including CDATA
            );
            */
            $search = [
                '@<script[^>]*?>.*?</script>@usi',  // Strip out javascript
                '@<![\s\S]*?--[ \t\n\r]*>@u',       // Strip multi-line comments including CDATA
            ];
            $element = preg_replace($search, '', $element);
            if (mb_strpos($element, '<p>') === 0)
            {
                return preg_replace('@<p>(.*)</p>@u', '$1', $element, 1);
            }
            else
            {
                return $element;
            }
        }
    }
    /**
     * Check the permissions of various folders and files which must be writable
     */
    private function checkFolders()
    {
        // No verification on Windows which does not have an Unix permissions system
        // because IIS would be the only one to use the native permissions of this system
        // and it is not officially supported
        if (\UTILS\OSName() == "windows")
        {
            return;
        }
        
        $aErrorPerms = [];
        
        $folderstocheck = [
            WIKINDX_DIR_DATA => \FILE\dirInDirToArray(WIKINDX_DIR_DATA),
            WIKINDX_DIR_CACHE => \FILE\dirInDirToArray(WIKINDX_DIR_CACHE),
            
            WIKINDX_DIR_COMPONENT_LANGUAGES => \FILE\dirInDirToArray(WIKINDX_DIR_COMPONENT_LANGUAGES),
            WIKINDX_DIR_COMPONENT_PLUGINS => \FILE\dirInDirToArray(WIKINDX_DIR_COMPONENT_PLUGINS),
            WIKINDX_DIR_COMPONENT_STYLES => \FILE\dirInDirToArray(WIKINDX_DIR_COMPONENT_STYLES),
            WIKINDX_DIR_COMPONENT_TEMPLATES => \FILE\dirInDirToArray(WIKINDX_DIR_COMPONENT_TEMPLATES),
            WIKINDX_DIR_COMPONENT_VENDOR => \FILE\dirInDirToArray(WIKINDX_DIR_COMPONENT_VENDOR),
        ];
        
        foreach ($folderstocheck as $root => $paths)
        {
            foreach ($paths as $path)
            {
                $dir = $root . DIRECTORY_SEPARATOR . $path;
                
                if (!is_readable($dir))
                {
                    $aErrorPerms[$dir] = "r";
                }
                if (!is_writable($dir))
                {
                    if (array_key_exists($dir, $aErrorPerms))
                    {
                        $aErrorPerms[$dir] .= "w";
                    }
                    else
                    {
                        $aErrorPerms[$dir] = "w";
                    }
                }
            }
        }
        
        if (count($aErrorPerms) > 0)
        {
            $string = "<table>";
            $string .= "<tr>";
            $string .= "<th>Folder</th>";
            $string .= "<th>Current Unix perms</th>";
            $string .= "<th>Missing perms</th>";
            $string .= "</tr>";
            foreach ($aErrorPerms as $name => $perm)
            {
                $string .= "<tr>";
                $string .= "<td>" . $name . "</td>";
                $string .= "<td>" . substr(sprintf('%o', fileperms($name)), -4) . "</td>";
                $string .= "<td>" . $perm . "</td>";
                $string .= "</tr>";
            }
            $string .= "</table>";
            
            die("WIKINDX will not function correctly if various folders and files within them are not writeable for the web server user.
            The following folders, shown with their current Unix permissions, should be made readable and writeable (along with their contents) for 
            the web server user. The web server user can be the owner and/or the group of those folders. So you have to modify, the owner, the group and the permission bits according to the particular configuration of your web server, PHP and file transfer software. You may also be required to add the execution bit in certain cases. The same rights apply to files in these folders, but this script does not check them for performance reasons. See the chmod, web server and PHP manuals, and docs/INSTALL.txt for details.<p><p>r = readable; w = writable ; x = executable</p>" . $string);
        }
    }
}
