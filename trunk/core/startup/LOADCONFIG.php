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
    public $config;

    /**
     *	LOADCONFIG
     */
    public function __construct()
    {
        $this->config = FACTORY_CONFIG::getInstance();
        $this->loadStaticConfig();
        $this->getVars();
        
        $vars = GLOBALS::getVars();
        if (
            !empty($vars)
            && array_key_exists('cookie', $vars)
            && $vars['cookie']
            && array_key_exists('uname', $vars)
            && trim($vars['uname'])
        ) {
            // set the cookie if requested
            $cookie = FACTORY_COOKIE::getInstance();
            $cookie->storeCookie($vars['uname']);
            unset($cookie);
        }
        
        // Start the session
        $this->startSession();
    }

    /**
     * start the SESSION
     */
    function startSession()
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
     * Load user vars from users table and store them in GLOBALS::userVars array
     */
    public function loadUserVars()
    {
        $session = FACTORY_SESSION::getInstance();
        $db = FACTORY_DB::getInstance();
    	if ($session->getVar('setup_UserId')) // logged on user so setup from users table
    	{
			$basic = [
				"CmsTag",
				"DisplayBibtexLink",
				"DisplayCmsLink",
				"Language",
				"Listlink",
				"Paging",
				"PagingMaxLinks",
				"PagingStyle",
				"PagingTagCloud",
				"StringLimit",
				"Style",
				"Template",
				"TemplateMenu",
				"UseBibtexKey",
				"UseWikindxKey",
			];
			$table = 'users';
			$preferences = $db->prependTableToField($table, $basic);
			$db->formatConditions([$table . 'Id' => $session->getVar('setup_UserId')]);
			$resultSet = $db->select($table, $preferences);
			$row = $db->fetchRow($resultSet);
		}
		else // read only user – read default settings from config table
		{
			$basic = [
				"Language",
				"Listlink",
				"Paging",
				"PagingMaxLinks",
				"PagingStyle",
				"PagingTagCloud",
				"StringLimit",
				"Style", 
				"Template",
				"TemplateMenu",
			];
        	$co = FACTORY_CONFIGDBSTRUCTURE::getInstance();
			$table = 'config';
            $preferences = $db->prependTableToField($table, $basic);
			$row = $co->getData($preferences);
// read only user – load session variable where it exists and overwrite default config setting set above.
			foreach ($basic as $key)
			{
				$rowKey = $table . $key;
				if ($session->issetVar('setup_' . $key))
				{
					$row[$rowKey] = $session->getVar('setup_' . $key);
				}
				elseif ($key == 'Listlink')
				{
					$row[$rowKey] = FALSE;
				}
			}
		}
        foreach ($basic as $key)
        {
        	$rowKey = $table . $key;
        	if (array_key_exists($rowKey, $row))
        	{
				if (($key == 'PagingStyle') || ($key == 'UseWikindxKey') || ($key == 'UseBibtexKey')
					 || ($key == 'DisplayBibtexLink') || ($key == 'DisplayCmsLink') || ($key == 'Listlink'))
				{
					if (!$row[$rowKey] || ($row[$rowKey] === 'N'))
					{
						GLOBALS::setUserVar($key, FALSE);
					}
					else
					{
						GLOBALS::setUserVar($key, $row[$rowKey]);
					}
                }
				elseif ($key == 'CmsTag')
				{
					if (!$row[$rowKey])
					{
						GLOBALS::setUserVar('CmsTagStart', FALSE);
						GLOBALS::setUserVar('CmsTagEnd', FALSE);
					}
					else
					{
						$cms = unserialize(base64_decode($row[$rowKey]));
						GLOBALS::setUserVar('CmsTagStart', $cms[0]);
						GLOBALS::setUserVar('CmsTagEnd', $cms[1]);
					}
				}
        		elseif (!$row[$rowKey])
        		{
	        		GLOBALS::setUserVar($key, FALSE);
        		}
        		else
        		{
	        		GLOBALS::setUserVar($key, $row[$rowKey]);
	        	}
        	}
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
    private function loadStaticConfig()
    {
        $dieMsgMissing = 'Missing configuration variable in config.php: ';
        
        // Set the current working directory -- useful for ensuring TinyMCE plug-ins can find the wikindx base path for include() commands.
        // Not all OSs allow getcwd() or sometimes the wikindx installation is in a directory that is not searchable.
        if (!property_exists($this->config, 'WIKINDX_WIKINDX_PATH'))
        {
        	die('WIKINDX_WIKINDX_PATH must be a valid absolute path or the boolean value FALSE');
        }
        elseif ($this->config->WIKINDX_WIKINDX_PATH === FALSE)
        {
	        $this->config->WIKINDX_WIKINDX_PATH = realpath($this->getWikindxBasePath());
        }
        elseif (is_string($this->config->WIKINDX_WIKINDX_PATH))
        {
	        $this->config->WIKINDX_WIKINDX_PATH = realpath(trim($this->config->WIKINDX_WIKINDX_PATH));
        }
        else
        {
        	die('WIKINDX_WIKINDX_PATH must be a valid absolute path or the boolean value FALSE');
        }
        
        // Remove the last slash
        $this->config->WIKINDX_WIKINDX_PATH = trim(rtrim($this->config->WIKINDX_WIKINDX_PATH, "/"));
	    
        // Test path is correct
        if (!is_file($this->config->WIKINDX_WIKINDX_PATH . '/core/startup/' . basename(__FILE__)))
        {
            die("
                \$WIKINDX_WIKINDX_PATH in config.php is set incorrectly
                and WIKINDX is unable to set the installation path automatically.
                You should set \$WIKINDX_WIKINDX_PATH in config.php.
            ");
        }

        
        // Set base url (default if needed)
        if (!property_exists($this->config, 'WIKINDX_BASE_URL'))
        {
        	die('WIKINDX_BASE_URL must be a valid URL or the boolean value FALSE');
        }
        elseif ($this->config->WIKINDX_BASE_URL === FALSE)
        {
            $this->config->WIKINDX_BASE_URL = $_SERVER['HTTP_HOST'];
            
            // In case the code is not installed in the root folder of the vhost,
            // deduct the additional subdirectories by difference with the root folder of the vhost.
            $DOCUMENT_ROOT = realpath($_SERVER['DOCUMENT_ROOT']);
            $wikindxBasePath = realpath($this->config->WIKINDX_WIKINDX_PATH);
            
            if ($_SERVER['DOCUMENT_ROOT'] != $wikindxBasePath)
            {
                $wikindxSubPath = mb_substr($wikindxBasePath, mb_strlen($DOCUMENT_ROOT));
                $this->config->WIKINDX_BASE_URL .= $wikindxSubPath;
            }
        }
        elseif (is_string($this->config->WIKINDX_BASE_URL))
        {
	        $this->config->WIKINDX_BASE_URL = $this->config->WIKINDX_BASE_URL;
        }
        else
        {
        	die('WIKINDX_BASE_URL must be a valid URL or the boolean value FALSE');
        }
        
        // Canonicalize the URL separator
        $this->config->WIKINDX_BASE_URL = str_replace("\\", "/", $this->config->WIKINDX_BASE_URL);
        
        // Remove the last slash
        $this->config->WIKINDX_BASE_URL = trim(rtrim($this->config->WIKINDX_BASE_URL, "/"));
        
        // Add the protocol requested when not defined
        // or replace it dynamically by the protocol requested by the browser (http or https)
        if (!\UTILS\matchPrefix($this->config->WIKINDX_BASE_URL, "http://") && !\UTILS\matchPrefix($this->config->WIKINDX_BASE_URL, "https://"))
        {
            $this->config->WIKINDX_BASE_URL = FACTORY_URL::getInstance()->getCurrentProtocole() . '://' . $this->config->WIKINDX_BASE_URL;
        }
        else
        {
            $this->config->WIKINDX_BASE_URL = preg_replace('/^https?/u', FACTORY_URL::getInstance()->getCurrentProtocole(), $this->config->WIKINDX_BASE_URL);
        }


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
    }
    private function getWikindxBasePath()
    {
        $wikindxBasePath = __DIR__;
        while (!in_array(basename($wikindxBasePath), ["", "core"]))
        {
            $wikindxBasePath = dirname($wikindxBasePath);
        }
        if (basename($wikindxBasePath) == "")
        {
            die("
                \$WIKINDX_WIKINDX_PATH in config.php is set incorrectly
                and WIKINDX is unable to set the installation path automatically.
                You should set \$WIKINDX_WIKINDX_PATH in config.php.
            ");
        }
        return dirname($wikindxBasePath);
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
     * Load various arrays into $this->config object as well as initialize user variables in GLOBALS
     */
    public function loadDBConfig()
    {
        $db = FACTORY_DB::getInstance();
        $co = FACTORY_CONFIGDBSTRUCTURE::getInstance();
        $tmp_config = [];
        
        // Load the configuration from the db and destroy unused config options
        $resultSet = $db->select('config', '*');
        while ($row = $db->fetchRow($resultSet))
        {
            if (array_key_exists($row['configName'], $co->configToConstant))
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
                $db->formatConditions(['configName' => $row['configName']]);
                $db->delete('config');
            }
        }
        
        // If an option is missing in the db create it
        // and use its default value
        foreach ($co->configToConstant as $configName => $unused)
        {
            if (!array_key_exists($configName, $tmp_config))
            {
                // Retrieve the default value
                $constName = $co->configToConstant[$configName];
                if (!array_key_exists($configName, $co->dbStructure))
                {
                    die("The type of $configName option is not defined.");
                }
                $configType = $co->dbStructure[$configName];
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
                $db->insert('config', ['configName', $configType], [$configName, $defaultvalue]);
                
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
            $constName = $co->configToConstant[$configName];
            $configType = $co->dbStructure[$configName];
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
                if (!is_array($value))
                {
                    $value = unserialize(base64_decode(constant($constName . "_DEFAULT")));
                }
            }
            
            // Create the constant config member 
            $this->config->{$constName} = $value;
        }
        
        $this->checkConfigValidity();
        $this->configureErrorReporting();
    }

    public function configureErrorReporting()
    {
        ini_set('display_errors', $this->config->WIKINDX_DEBUG_ERRORS);
    }
    /**
     * Check validity of configuration in database and amend where necessary (dying if required).
     */
    private function checkConfigValidity()
    {
        ////////////////////////
        // As a precaution it is better not to check the variables below before an upgrade to version = 5.3 because the config.php file has changed.
        ////////////////////////
        if (WIKINDX_INTERNAL_VERSION < 5.3)
        {
            return;
        }
        
        $dieMsgMissing = 'Missing configuration variable: ';

        date_default_timezone_set($this->config->WIKINDX_TIMEZONE);

        // Set resource type
        if (!property_exists($this->config, 'WIKINDX_DEACTIVATE_RESOURCE_TYPES'))
        {
            $this->config->WIKINDX_DEACTIVATE_RESOURCE_TYPES = [];
        }
    }
}
