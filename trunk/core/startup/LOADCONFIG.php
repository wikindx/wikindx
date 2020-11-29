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
     * Load user vars from users table and store them in GLOBALS::userVars array
     */
    public function loadUserVars()
    {
        $session = FACTORY_SESSION::getInstance();
        $db = FACTORY_DB::getInstance();
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
            "HomeBib",
            "BrowseBibliography",
            "StringLimit",
            "Style",
            "Template",
            "TemplateMenu",
            "UseBibtexKey",
            "UseWikindxKey",
        ];
        if ($session->getVar("setup_UserId", 0) > 0)
        { // logged on user so setup from users table
            $table = 'users';
            $preferences = $db->prependTableToField($table, $basic);
            $db->formatConditions([$table . 'Id' => $session->getVar("setup_UserId")]);
            $resultSet = $db->select($table, $preferences);
            $row = $db->fetchRow($resultSet);
        }
        else
        { // read only user – read default settings from config table
            $table = 'config';
            $row = [];
            foreach ($basic as $key)
            {
                $rowKey = $table . $key;
                if ($session->issetVar("setup_" . $key))
                {
                    // Options inherited from the global config
                    $row[$rowKey] = $session->getVar("setup_" . $key);
                }
                elseif ($key == "Language")
                {
                    // Language should be inherited but it needs a special default
                    // which allows the browser to control the preferred language first
                    $row[$rowKey] = "auto";
                }
                elseif (array_key_exists($rowKey, WIKINDX_LIST_CONFIG_OPTIONS))
                {
                    $constName = WIKINDX_LIST_CONFIG_OPTIONS[$rowKey]["constname"];
                    $row[$rowKey] = constant($constName);
                }
                // Options unique to users
                elseif ($key == "DisplayBibtexLink")
                {
                    $row[$rowKey] = WIKINDX_DISPLAY_BIBTEX_LINK_DEFAULT;
                }
                elseif ($key == "DisplayCmsLink")
                {
                    $row[$rowKey] = WIKINDX_DISPLAY_CMS_LINK_DEFAULT;
                }
                elseif ($key == "PagingStyle")
                {
                    $row[$rowKey] = WIKINDX_USER_PAGING_STYLE_DEFAULT;
                }
                elseif ($key == "TemplateMenu")
                {
                    $row[$rowKey] = WIKINDX_TEMPLATE_MENU_DEFAULT;
                }
                elseif ($key == "UseBibtexKey")
                {
                    $row[$rowKey] = WIKINDX_USE_BIBTEX_KEY_DEFAULT;
                }
                elseif ($key == "UseWikindxKey")
                {
                    $row[$rowKey] = WIKINDX_USE_WIKINDX_KEY_DEFAULT;
                }
                elseif ($key == "BrowseBibliography")
                {
                    $row[$rowKey] = WIKINDX_BROWSEBIBLIOGRAPHY_DEFAULT;
                }
                elseif ($key == "HomeBib")
                {
                    $row[$rowKey] = WIKINDX_HOMEBIB_DEFAULT;
                }
                elseif ($key == "CmsTag")
                {
                    $row[$rowKey] = WIKINDX_CMS_TAG_DEFAULT;
                }
                else
                {
                    debug_print_backtrace();
                    die("Fatal error: missing default value for '{$key}' user config on loading");
                }
            }
        }
        foreach ($basic as $key)
        {
            $rowKey = $table . $key;
            if (array_key_exists($rowKey, $row))
            {
                if ($key == 'CmsTag')
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
     * Load various arrays into global constants as well as initialize user variables in GLOBALS
     */
    public function loadDBConfig()
    {
        $db = FACTORY_DB::getInstance();
        $tmp_config = [];
        
        // Load the configuration from the db and destroy unused config options
        $resultSet = $db->select('config', '*');
        while ($row = $db->fetchRow($resultSet))
        {
            if (array_key_exists($row['configName'], WIKINDX_LIST_CONFIG_OPTIONS))
            {
                // Load
                $tmp_config[$row['configName']] = [
                    "configBoolean" => $row['configBoolean'],
                    "configDatetime" => $row['configDatetime'],
                    "configInt" => $row['configInt'],
                    "configText" => $row['configText'],
                    "configVarchar" => $row['configVarchar'],
                ];
            }/* else {
                // destroy
                $db->formatConditions(['configName' => $row['configName']]);
                $db->delete('config');
            }*/
        }
        // If an option is missing in the db create it
        // and use its default value
        foreach (WIKINDX_LIST_CONFIG_OPTIONS as $configName => $unused)
        {
            if (array_key_exists($configName, $tmp_config) === FALSE)
            {
                // Retrieve the default value
                $constName = WIKINDX_LIST_CONFIG_OPTIONS[$configName]["constname"];
                $configType = WIKINDX_LIST_CONFIG_OPTIONS[$configName]["type"];
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
            $constName = WIKINDX_LIST_CONFIG_OPTIONS[$configName]["constname"];
            $configType = WIKINDX_LIST_CONFIG_OPTIONS[$configName]["type"];
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
                // at some point in the past, incorrect values have crept in – remove them
                if ($configName == 'configDeactivateResourceTypes')
                {
                    $tempValue = [];
                    foreach ($value as $key => $index)
                    {
                        if (!is_numeric($index))
                        {
                            $tempValue[$key] = $index;
                        }
                    }
                    $value = $tempValue;
                }
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
}
