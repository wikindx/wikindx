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
    /**
     *	LOADCONFIG
     */
    public function __construct()
    {
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
    	if ($session->getVar("setup_UserId")) // logged on user so setup from users table
    	{
			$basic = [
				"CmsTag",
				"DisplayBibtexLink",
				"DisplayCmsLink",
				"Language",
				"ListLink",
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
			$db->formatConditions([$table . 'Id' => $session->getVar("setup_UserId")]);
			$resultSet = $db->select($table, $preferences);
			$row = $db->fetchRow($resultSet);
		}
		else // read only user – read default settings from config table
		{
			$basic = [
				"Language",
				"ListLink",
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
				if ($session->issetVar("setup_" . $key))
				{
					$row[$rowKey] = $session->getVar("setup_" . $key);
				}
				elseif ($key == 'ListLink')
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
				if (in_array($key, ['PagingStyle', 'UseWikindxKey', 'UseBibtexKey', 'DisplayBibtexLink', 'DisplayCmsLink', 'ListLink']))
				{
					if ($key == 'PagingStyle' && $row[$rowKey] === 'N')
					{
						GLOBALS::setUserVar($key, "N");
					}
					elseif (!$row[$rowKey] || ($row[$rowKey] === 'N'))
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
                $element = preg_replace('@<p>(.*)</p>@u', '$1', $element, 1);
            }
            return $element;
        }
    }
    
    /**
     * Load various arrays into global constans as well as initialize user variables in GLOBALS
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
            
            // Create the global constant 
            if (!defined($constName))
            {
                define($constName, $value);
            }
        }
        
        date_default_timezone_set(WIKINDX_TIMEZONE);
        ini_set('display_errors', WIKINDX_DEBUG_ERRORS);
    }
}
