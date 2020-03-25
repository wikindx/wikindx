<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 *	LOADCONFIG
 *
 *	Load config variables from the database
 *
 * @package wikindx\core\startup
 */
class LOADCONFIG
{
    /** object */
    private $db;
    /** object */
    private $config;
    /** object */
    private $configDbStructure;

    /**
     *	LOADCONFIG
     */
    public function __construct()
    {
        $this->config = FACTORY_CONFIG::getInstance();
        $this->configDbStructure = FACTORY_CONFIGDBSTRUCTURE::getInstance();
        $this->db = FACTORY_DB::getInstance();
    }
    /**
     * Load various arrays into $this->config object
     */
    public function load()
    {
        $tmp_config = [];
        
        // Load the configuration from the db and destroy unused config options
        $resultSet = $this->db->select('config', '*');
        while ($row = $this->db->fetchRow($resultSet))
        {
            if (array_key_exists($row['configName'], $this->configDbStructure->configToConstant))
            {
                // Load
                $tmp_config[$row['configName']] = [
                    "configBoolean" => $row['configBoolean'],
                    "configDatetime" => $row['configDatetime'],
                    "configInt" => $row['configInt'],
                    "configText" => $row['configText'],
                    "configVarchar" => $row['configVarchar'],
                ];
            }
            else
            {
                // destroy
                $this->db->formatConditions(['configName' => $row['configName']]);
                $this->db->delete('config');
            }
        }
        
        // If an option is missing in the db create it
        // and use its default value
        foreach ($this->configDbStructure->configToConstant as $configName => $unused)
        {
            if (!array_key_exists($configName, $tmp_config))
            {
                // Retrieve the default value
                $constName = $this->configDbStructure->configToConstant[$configName];
                if (!array_key_exists($configName, $this->configDbStructure->dbStructure))
                {
                    die("The type of $configName option is not defined.");
                }
                $configType = $this->configDbStructure->dbStructure[$configName];
                if (!defined($constName . "_DEFAULT"))
                {
                    die("A default constant value for $constName option is missing (" . $constName . "_DEFAULT expected).");
                }
                
                // Create the option in the db
                $defaultvalue = constant($constName . "_DEFAULT");
                if ($configType == 'configBoolean')
                {
                    $defaultvalue = $defaultvalue === FALSE ? 0 : 1;
                }
                $this->db->insert('config', ['configName', $configType], [$configName, $defaultvalue]);
                
                // Create the option in the temp array
                $tmp_config[$configName] = [
                    "configBoolean" => NULL,
                    "configDatetime" => NULL,
                    "configInt" => NULL,
                    "configText" => NULL,
                    "configVarchar" => NULL,
                ];
                $tmp_config[$configName][$configType] = constant($constName . "_DEFAULT");
            }
        }
        
        // Cast the value retrieved from the db and create a constant config member for each global option
        foreach ($tmp_config as $configName => $configValues)
        {
            $constName = $this->configDbStructure->configToConstant[$configName];
            $configType = $this->configDbStructure->dbStructure[$configName];
            $value = $configValues[$configType];
            
            if ($configType == 'configBoolean')
            {
                $value = $value == 1 ? TRUE : FALSE; // Cast to bool
            }
            elseif ($configType == 'configDatetime')
            {
                // Keep the value unchanged
            }
            elseif ($configType == 'configInt')
            {
                $value = $value + 0; // cast to number from database string
            }
            elseif ($configType == 'configText')
            {
                // Keep the value unchanged
            }
            elseif ($configType == 'configVarchar')
            {
                // Keep the value unchanged
            }
            else
            {
                die("db config type unsupported: $configType");
            }
            
            // Unserialize some options
            if (in_array($configName, ['configNoSort', 'configSearchFilter', 'configDeactivateResourceTypes']))
            {
                $value = unserialize(base64_decode($value));
            }
            
            // Create the constant config member 
            $this->config->{$constName} = $value;
        }
        
        $this->checkConfigValidity();
        $this->configureErrorReporting();
    }
    public function configureErrorReporting()
    {
        if ($this->config->WIKINDX_DEBUG_ERRORS)
        {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        }
        else
        {
            // Disable all errors reports
            error_reporting(0);
            ini_set('display_errors', 'Off');
        }
    }
    /**
     * Check validity of configuration in database and amend where necessary (dying if required).
     */
    private function checkConfigValidity()
    {
        $dieMsgMissing = 'Missing configuration variable: ';
        if (!property_exists($this->config, 'WIKINDX_TITLE') || !$this->config->WIKINDX_TITLE)
        {
            $this->config->WIKINDX_TITLE = WIKINDX_NAME;
        }
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

        // Set LDAP configuration
        if (!property_exists($this->config, 'WIKINDX_LDAP_USE'))
        {
            $this->config->WIKINDX_LDAP_USE = WIKINDX_LDAP_USE_DEFAULT;
        }
        elseif (!is_bool($this->config->WIKINDX_LDAP_USE))
        {
            die('WIKINDX_LDAP_USE must be a boolean (TRUE / FALSE).');
        }

        // Set LDAP SERVER
        if (!property_exists($this->config, 'WIKINDX_LDAP_SERVER'))
        {
            $this->config->WIKINDX_LDAP_SERVER = WIKINDX_LDAP_SERVER_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_LDAP_SERVER))
        {
            die('WIKINDX_LDAP_SERVER must be a string.');
        }

        // Set LDAP DN
        if (!property_exists($this->config, 'WIKINDX_LDAP_DN'))
        {
            $this->config->WIKINDX_LDAP_DN = WIKINDX_LDAP_DN_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_LDAP_DN))
        {
            if ($this->config->WIKINDX_LDAP_DN !== FALSE)
            {
                $this->config->WIKINDX_LDAP_DN = WIKINDX_LDAP_DN_DEFAULT;
            }
            elseif (trim($this->config->WIKINDX_LDAP_DN == ''))
            {
                $this->config->WIKINDX_LDAP_DN = WIKINDX_LDAP_DN_DEFAULT;
            }
        }

        // Set LDAP port
        if (!property_exists($this->config, 'WIKINDX_LDAP_PORT'))
        {
            $this->config->WIKINDX_LDAP_PORT = WIKINDX_LDAP_PORT_DEFAULT;
        }
        elseif (is_int($this->config->WIKINDX_LDAP_PORT))
        {
            if ($this->config->WIKINDX_LDAP_PORT < 0)
            {
                die('WIKINDX_LDAP_PORT must be a positive integer.');
            }
        }

        // Set LDAP Opt Protocol Version
        if (!property_exists($this->config, 'WIKINDX_LDAP_PROTOCOL_VERSION'))
        {
            $this->config->WIKINDX_LDAP_PROTOCOL_VERSION = WIKINDX_LDAP_PROTOCOL_VERSION_DEFAULT;
        }
        elseif (is_int($this->config->WIKINDX_LDAP_PROTOCOL_VERSION))
        {
            if ($this->config->WIKINDX_LDAP_PROTOCOL_VERSION < 0)
            {
                die('WIKINDX_LDAP_PROTOCOL_VERSION must be a positive integer.');
            }
        }

        // Set AUTHGATE configuration (or similar authentication gate)
        if (!property_exists($this->config, 'WIKINDX_AUTHGATE_USE'))
        {
            $this->config->WIKINDX_AUTHGATE_USE = WIKINDX_AUTHGATE_USE_DEFAULT;
        }
        elseif (!is_bool($this->config->WIKINDX_AUTHGATE_USE))
        {
            die('WIKINDX_AUTHGATE_USE must be a boolean (TRUE / FALSE).');
        }

        // Set AUTHGATE message
        if (!property_exists($this->config, 'WIKINDX_AUTHGATE_MESSAGE'))
        {
            $this->config->WIKINDX_AUTHGATE_MESSAGE = WIKINDX_AUTHGATE_MESSAGE_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_AUTHGATE_MESSAGE))
        {
            if ($this->config->WIKINDX_AUTHGATE_MESSAGE !== FALSE)
            {
                $this->config->WIKINDX_AUTHGATE_MESSAGE = WIKINDX_AUTHGATE_MESSAGE_DEFAULT;
            }
            elseif (trim($this->config->WIKINDX_AUTHGATE_MESSAGE == ''))
            {
                $this->config->WIKINDX_AUTHGATE_MESSAGE = WIKINDX_AUTHGATE_MESSAGE_DEFAULT;
            }
        }

        // Set PASSWORD size
        if (!property_exists($this->config, 'WIKINDX_PASSWORDSIZE'))
        {
            $this->config->WIKINDX_PASSWORDSIZE = WIKINDX_PASSWORDSIZE_DEFAULT;
        }
        elseif (!is_int($this->config->WIKINDX_PASSWORDSIZE))
        {
            if ($this->config->WIKINDX_PASSWORDSIZE < 0)
            {
                die('WIKINDX_PASSWORDSIZE must be a positive integer.');
            }
        }

        // Set PASSWORD strength
        if (!property_exists($this->config, 'WIKINDX_PASSWORDSTRENGTH'))
        {
            $this->config->WIKINDX_PASSWORDSTRENGTH = WIKINDX_PASSWORDSTRENGTH_DEFAULT;
        }
        elseif (!is_string($this->config->WIKINDX_PASSWORDSTRENGTH))
        {
            if ($this->config->WIKINDX_PASSWORDSTRENGTH !== FALSE)
            {
                $this->config->WIKINDX_PASSWORDSTRENGTH = WIKINDX_PASSWORDSTRENGTH_DEFAULT;
            }
            elseif (trim($this->config->WIKINDX_PASSWORDSTRENGTH == ''))
            {
                $this->config->WIKINDX_PASSWORDSTRENGTH = WIKINDX_PASSWORDSTRENGTH_DEFAULT;
            }
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
                $this->config->WIKINDX_MAIL_FROM = WIKINDX_MAIL_FROM_DEFAULT;
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
                $this->config->WIKINDX_MAIL_REPLYTO = WIKINDX_MAIL_REPLYTO_DEFAULT;
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
                $this->config->WIKINDX_MAIL_RETURN_PATH = WIKINDX_MAIL_RETURN_PATH_DEFAULT;
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
        }

        // Set smtp encryption
        if (!property_exists($this->config, 'WIKINDX_MAIL_SMTPENCRYPT'))
        {
            $this->config->WIKINDX_MAIL_SMTPENCRYPT = WIKINDX_MAIL_SMTPENCRYPT_DEFAULT;
        }
        elseif (!$this->config->WIKINDX_MAIL_SMTPENCRYPT)
        {
            $this->config->WIKINDX_MAIL_SMTPENCRYPT = '';
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
        elseif (!$this->config->WIKINDX_MAIL_SMTPUSERNAME)
        {
            $this->config->WIKINDX_MAIL_SMTPUSERNAME = '';
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
        elseif (!$this->config->WIKINDX_MAIL_SMTPPASSWORD)
        {
            $this->config->WIKINDX_MAIL_SMTPPASSWORD = '';
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
        elseif (!$this->config->WIKINDX_CMS_DB_USER)
        {
            $this->config->WIKINDX_CMS_DB_USER = '';
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
        elseif (!$this->config->WIKINDX_CMS_DB_PASSWORD)
        {
            $this->config->WIKINDX_CMS_DB_PASSWORD = '';
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
}
