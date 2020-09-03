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
        if (count(self::$moduleList) == 0) {
            foreach (\UTILS\readComponentsList() as $cmp) {
                // Load only:
                if (
                       $cmp["component_type"] == "plugin" // Components of plugin type
                    && $cmp["component_status"] == "enabled" // Enabled components
                    && $cmp["component_integrity"] == 0 // Sane components
                ) {
                    self::$moduleList[] = $cmp["component_id"];
                }
            }
        }

        return self::$moduleList;
    }

    /**
     * Check version compatiblility of a plugin
     *
     * @param mixed $dir
     *
     * @return bool
     */
    public function checkVersion($dir)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_COMPONENT_PLUGINS, $dir, "config.php"]));
        $class = $dir . "_CONFIG";
        if (class_exists($class)) {
            $config = new $class();
            if (property_exists($config, "wikindxVersion") && ($config->wikindxVersion == WIKINDX_COMPONENTS_COMPATIBLE_VERSION["plugin"])) {
                return TRUE;
            }
        }

        return FALSE;
    }
}
