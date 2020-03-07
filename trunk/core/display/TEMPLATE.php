<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * Load the WIKINDX display template system
 *
 * @package wikindx\core\display
 */
class TEMPLATE
{
    /** object */
    public $tpl;
    /** object */
    private $errors;
    /** object */
    private $session;
    /** string */
    private $compileDir;
    /** string */
    private $cacheDir;
    /** string */
    private $name;
    /** string */
    private $path;
    /** array */
    private $tplList = FALSE;

    /**
     * TEMPLATE
     */
    public function __construct()
    {
        include_once(WIKINDX_DIR_COMPONENT_VENDOR . "/smarty/libs/Smarty.class.php");
        $this->tpl = new Smarty();

        // PHP 8 will make E_ALL the default error level,
        // in preparation for its support we also make this level the default
        $this->tpl->error_reporting = E_ALL;

        // We never execute untrusted code, so we can disable this feature of Smarty
        $this->tpl->disableSecurity();
    }

    /**
     * Return the directory of the loaded template
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->path;
    }

    /**
     * Clear all caches of Smarty
     */
    public function clearAllCache()
    {
        $this->clearCache();
        $this->clearCompileCache();
    }

    /**
     * Clear the entire template cache of Smarty
     */
    public function clearCache()
    {
        $this->tpl->clearAllCache();
    }

    /**
     * Clear all compilation directories of Smarty
     */
    public function clearCompileCache()
    {
        $this->tpl->clearCompiledTemplate();

        foreach (FILE\fileInDirToArray($this->compileDir) as $ftpl) {
            @unlink($this->compileDir . DIRECTORY_SEPARATOR . $ftpl);
        }
    }

    /**
     * Called from CLOSE, this sets up the right template to use before the script is exited
     *
     * @param mixed $setupMode
     */
    public function loadTemplate($setupMode = FALSE)
    {
        $this->session = FACTORY_SESSION::getInstance();
        $tplArray = $this->loadDir();
        $this->name = GLOBALS::getUserVar('Template');
        // Special case: during installation there is no template sets.
        if (!is_string($this->name) || $setupMode) {
            $this->name = WIKINDX_TEMPLATE_DEFAULT;
        }

        if (!array_key_exists($this->name, $tplArray)) {

            // At first install of a blank database
            if (!$this->name) {
                $this->name = WIKINDX_TEMPLATE_DEFAULT;
            }

            if (!array_key_exists($this->name, $tplArray)) {
                $this->name = WIKINDX_TEMPLATE_DEFAULT;
            }

            if (!$this->session->getVar("setup_ReadOnly")) {
                $this->session->setVar("setup_Template", $this->name);
            }
        }

        // template may have been disabled by admin
        if (!is_file(WIKINDX_DIR_COMPONENT_TEMPLATES . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR . 'component.json')) {
            // During the upgrade / update process the database is not always available to query the configuration
            if (!$setupMode) {
                $co = FACTORY_CONFIGDBSTRUCTURE::getInstance();
                $this->name = $co->getOne('configTemplate');
            }
        }
        
        // cache directories
        $this->compileDir = WIKINDX_DIR_CACHE_TEMPLATES . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR . "compile";
        $this->cacheDir = WIKINDX_DIR_CACHE_TEMPLATES . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR . "cache";

        // Configure (main) template path of Smarty
        // with the name of the template instead of using indice 0
        $this->path = WIKINDX_DIR_COMPONENT_TEMPLATES . DIRECTORY_SEPARATOR . $this->name;
        $this->tpl->setTemplateDir([$this->name => $this->path]);

        // Register "menu" function of SmartyMenu plugin
        $this->tpl->registerDefaultPluginHandler([$this, 'plugin_SmartyMenu_handler']);

        // Configure compilation options of Smarty
        $this->createDirectory($this->compileDir);
        $this->tpl->setCompileDir($this->compileDir);

        // Force compilation on setup mode, or apply the current config policy
        $this->tpl->setForceCompile($setupMode || (defined("WIKINDX_BYPASS_SMARTY_COMPILATION") ? WIKINDX_BYPASS_SMARTY_COMPILATION : WIKINDX_BYPASS_SMARTY_COMPILATION_DEFAULT));

        // Configure cache options of Smarty
        // We use dynamic pages so certainly don't want caching!
        // However it is not impossible to cache the menu that now we have features to clear the cache.
        $this->createDirectory($this->cacheDir); // Don't create it for the moment
        $this->tpl->setCacheDir($this->cacheDir);
        $this->tpl->setCaching(FALSE);
        
        // Default is 0
        // Used when config.txt file is missing or something go wrong
        $ReduceMenuLevelOption = "0";
        $ReduceMenuLevelPretextOption = "";
        
        // Retrieve the configuration
        $configfile = $this->path . DIRECTORY_SEPARATOR . 'config.txt';
        if (file_exists($configfile)) {
            if ($fh = fopen($configfile, "r")) {
                $index = 1;
                while (($line = fgets($fh)) !== FALSE) {
                    if ($index == 1) {
                        $ReduceMenuLevelOption = $line;
                        ++$index;
                    } elseif ($index == 2) {
                        $ReduceMenuLevelPretextOption = $line;

                        break;
                    }
                }
    
                fclose($fh);
            }
        }
        
        // Configure smarty menu level reduction
        // The first char is a digit that indicates the level of reduction (0, 1, 2)
        // If the second char is $, the user config of setup_TemplateMenu will be ignored
        // In case of error, the level is 0
        $level = trim($ReduceMenuLevelOption);
        $level = substr($ReduceMenuLevelOption, 0, 1);
        $level = $level !== FALSE ? $level : "0";
        $level = ctype_digit($level) ? $level : "0";
        $ignoreUserConfig = substr($ReduceMenuLevelOption, 1, 1);
        $ignoreUserConfig = ($ignoreUserConfig === "$");
        
        if (!$ignoreUserConfig && GLOBALS::getUserVar('TemplateMenu')) {
            $this->session->setVar("setup_ReduceMenuLevel", GLOBALS::getUserVar('TemplateMenu'));
        } else {
            $this->session->setVar("setup_ReduceMenuLevel", $level);
        }
        
        // Configure pre text for menu items that were in a subSubmenu
        // Only valid for reduceMenuLevel == 1
        if ($ReduceMenuLevelPretextOption != "") {
            $this->session->setVar("setup_ReduceMenuLevelPretext", $ReduceMenuLevelPretextOption);
        }
    }

    /**
     * read WIKINDX_DIR_COMPONENT_TEMPLATES directory for template preferences and check we have a sane environment
     *
     * @return array
     */
    public function loadDir()
    {
        // Use an internal array to "memoize" the list of installed templates
        // This function is called multiple times while its result is almost static but expensive due to disk access.
        if ($this->tplList === FALSE) {
            $array = [];
            
            $componentsInstalled = \UTILS\readComponentsList();
            
            foreach ($componentsInstalled as $cmp) {
                if ($cmp["component_type"] == "template" && $cmp["component_status"] == "enabled") {
                    $array[$cmp["component_id"]] = $cmp["component_name"];
                }
            }
            
            ksort($array);
            
            $this->tplList = $array;
        }

        return $this->tplList;
    }

    /**
     * SmartyMenu Plugin Handler
     *
     * called when Smarty encounters an undefined tag during compilation
     *
     * @param string $name name of the undefined tag
     * @param string $type tag type (e.g. Smarty::PLUGIN_FUNCTION, Smarty::PLUGIN_BLOCK, Smarty::PLUGIN_COMPILER, Smarty::PLUGIN_MODIFIER, Smarty::PLUGIN_MODIFIERCOMPILER)
     * @param Smarty_Internal_Template $template template object
     * @param string &$callback returned function name
     * @param string &$script optional returned script filepath if function is external
     * @param bool &$cacheable true by default, set to false if plugin is not cachable (Smarty >= 3.1.8)
     *
     * @return bool true if successful
     */
    public function plugin_SmartyMenu_handler($name, $type, $template, &$callback, &$script, &$cacheable)
    {
        switch ($type) {
            case Smarty::PLUGIN_FUNCTION:
                switch ($name) {
                    case 'menu':
                        $script = WIKINDX_DIR_COMPONENT_VENDOR . '/smarty/SmartyMenu/function.menu.php';
                        $callback = 'smarty_function_menu';

                        return TRUE;
                }
                // no break
            case Smarty::PLUGIN_COMPILER:
                switch ($name) {
                    case 'menu':
                    $script = WIKINDX_DIR_COMPONENT_VENDOR . '/smarty/SmartyMenu/function.menu.php';
                    $callback = 'smarty_function_menu';

                    return TRUE;
                default:
                    return FALSE;
                }
                // no break
            case Smarty::PLUGIN_BLOCK:
                switch ($name) {
                    case 'menu':
                    $script = WIKINDX_DIR_COMPONENT_VENDOR . '/smarty/SmartyMenu/function.menu.php';
                    $callback = 'smarty_function_menu';

                    return TRUE;
                default:
                    return FALSE;
                }
                // no break
            default:
                return FALSE;
        }
    }


    /**
     * Clear all compilation directories of Smarty
     *
     * @param string $dir
     */
    private function createDirectory($dir)
    {
        $this->errors = FACTORY_ERRORS::getInstance();

        if (!is_dir($dir)) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, WIKINDX_UNIX_PERMS_DEFAULT, TRUE)) {
                    echo $this->errors->text("file", "folder") . " " . $dir;
                }
            }
        }
    }
}
