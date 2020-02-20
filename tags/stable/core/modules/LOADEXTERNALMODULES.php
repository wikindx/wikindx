<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * LOADEXTERNALMODULES class for third-party plug-in modules in the WIKINDX_DIR_COMPONENT_PLUGINS directory
 */
class LOADEXTERNALMODULES
{
    public static $moduleList = [];

    /**
     * Return a list of available and enabled plugins
     *
     * Read root WIKINDX_DIR_COMPONENT_PLUGINS directory for any available and validate modules
     * and return a list of them.
     *
     * @return string[]
     */
    public function readPluginsDirectory()
    {
        if (count(self::$moduleList) == 0)
        {
            foreach (\UTILS\readComponentsList() as $cmp)
            {
                if ($cmp["component_type"] == "plugin" && $cmp["component_status"] == "enabled")
                {
                    self::$moduleList[] = $cmp["component_id"];
                }
            }
        }

        return self::$moduleList;
    }

    /**
     * Check version compatiblility of a plugin
     *
     * @return bool
     */
    public function checkVersion($dir)
    {
        include_once(WIKINDX_DIR_COMPONENT_PLUGINS . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'config.php');
        $class = $dir . "_CONFIG";
        if (!class_exists($class))
        {
            return FALSE;
        }
        $config = new $class();
        if (isset($config->wikindxVersion) && ($config->wikindxVersion == WIKINDX_PLUGIN_VERSION))
        {
            return TRUE;
        }

        return FALSE;
    }
}
