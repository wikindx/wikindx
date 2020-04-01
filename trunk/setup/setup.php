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
 * Functions used for the upgrade process
 *
 * @package wikindx\core\update
 */
namespace SETUP
{
    /**
     * Check if the minimum PHP version is compatible
     */
    function isPhpVersionMinCompatible()
    {
        return version_compare(PHP_VERSION, WIKINDX_PHP_VERSION_MIN, '>=');
    }
    
    /**
     * Check if the maximum PHP version is compatible
     */
    function isPhpVersionMaxCompatible()
    {
        return version_compare(PHP_VERSION, WIKINDX_PHP_VERSION_MAX, '<=');
    }
    
    /**
     * Check if all mandatory PHP extensions are available
     */
    function areMandatoryPhpExtensionsAvailable()
    {
        $MandatoryExtensions = \UTILS\listCoreMandatoryPHPExtensions();
        $InstalledExtensions = get_loaded_extensions();
        $MissingExtensions = array_diff($MandatoryExtensions, $InstalledExtensions);
        
        return (count($MissingExtensions) == 0);
    }
    
    /**
     * Check if the minimum MariaDB/MySQL engine version is compatible
     */
    function isDBEngineVersionMinCompatible()
    {
        return TRUE;
    }
    
    /**
     * Check if the Config class is available
     *
     */
    function isConfigSet()
    {
        $configfile = __DIR__ . "/../config.php";
        if (is_readable($configfile) && is_writable($configfile))
        {
            include_once($configfile);
            return class_exists("\CONFIG");
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
     * Check if the Configuration class is up to date (has all it's members)
     *
     * This function checks the existence of all members of the class and not their validity.
     *
     */
    function isConfigUptodate()
    {
        if (isConfigSet())
        {
            $configfile = __DIR__ . "/../config.php";
            include_once($configfile);
            global $optionsDefinition;
            $config = new \CONFIG();
            
            // Check if an option is missing
            foreach ($optionsDefinition as $option => $def)
            {
                if (!property_exists($config, $option))
                {
                    return FALSE;
                    break;
                }
            }
            
            // Check if an option is not used
            foreach (get_class_vars("\CONFIG") as $option => $value)
            {
                if (!array_key_exists($option, $optionsDefinition))
                {
                    return FALSE;
                    break;
                }
            }
        }
        
        return TRUE;
    }
    
    /**
     * Check if the current Wikindx data (db and files) need an upgrade
     *
     * @param object $dbo An SQL object
     *
     * @return bool
     */
    function needUpdate($dbo)
    {
        // NB: tableConfigExists must be the first operation because
        // it reads the db system catalog and can't fail if the db exists
        
        // Check if 'config' table doesn't exist
        if (!$dbo->tableExists('config')) {
            return TRUE;
        }
        // Check if 'config' table has not been filled with a configuration
        elseif ($dbo->tableIsEmpty('config')) {
            return TRUE;
        }
        // Check if the database version number is not the same as source code version number
        elseif (\UPDATE\getDatabaseVersion($dbo) != WIKINDX_INTERNAL_VERSION) {
            return TRUE;
        }
        // Check if 'users' table has not been filled with a configuration
        elseif ($dbo->tableIsEmpty('users')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * Initial logon to the system when upgrading the database.
     *
     * @param object $dbo An SQL object
     * @param string $username
     * @param string $password
     * @param float $dbVersion default = WIKINDX_INTERNAL_VERSION
     *
     * @return bool TRUE if able to authenticate
     */
    function logonCheckUpgradeDB($dbo, $username, $password, $dbVersion = WIKINDX_INTERNAL_VERSION)
    {
        // superAdmin is id '1'
        $dbo->formatConditions(['usersUsername' => $username, 'usersId' => WIKINDX_SUPERADMIN_ID]);
        $recordset = $dbo->select('users', ['usersId', 'usersPassword']);
        if ($dbo->numRows($recordset) == 1) {
            // verify the password
            $row = $dbo->fetchRow($recordset);
            if (crypt($password, $row['usersPassword']) == $row['usersPassword']) {
                return TRUE;
            }
        }

        return FALSE;
    }
    
    /**
     * Return the version number stored in the database, depending on the software version
     *
     * This function is used only during the upgrade process, and the value should be looked up
     * in the field regardless of the version.
     *
     * @param object $dbo An SQL object
     *
     * @return float
     */
    function getDatabaseVersion($dbo)
    {
        $dbVersion = 0.0;
        $recordset = $dbo->queryNoError($dbo->selectNoExecute('database_summary', '*'));
        if ($recordset !== FALSE) {
            $row = $dbo->fetchRow($recordset);
            // From version 6
            if (array_key_exists('databasesummarySoftwareVersion', $row)) {
                $field = "databasesummarySoftwareVersion";
            }
            // Up to version 5.9.1
            if (array_key_exists('databasesummaryDbVersion', $row)) {
                $field = "databasesummaryDbVersion";
            }
            $dbVersion = floatval($row[$field]);
            unset($row);
        }

        return $dbVersion;
    }
    
    /**
     * Return the configContactEmail depending on the software version
     *
     * This function is used only during the upgrade process, and the value should be looked up
     * in the field regardless of the version.
     *
     * @param object $dbo An SQL object
     *
     * @return float
     */
    function getConfigContactEmail($dbo)
    {
        $email = WIKINDX_CONTACT_EMAIL_DEFAULT;
        $recordset = $dbo->queryNoError($dbo->selectNoExecute("config", "*"));
        if ($recordset !== FALSE) {
            $field = "";
            $row = $dbo->fetchRow($recordset);
            // Up to version 5.3
            if (array_key_exists("configContactEmail", $row)) {
                $field = "configContactEmail";
            }
            // From version 5.4
            if (array_key_exists("configName", $row) && array_key_exists("configVarchar", $row)) {
                $field = "configVarchar";
                
                // Search the recond
                do {
                    if ($row["configName"] == "configContactEmail") {
                        break;
                    }
                } while ($row = $dbo->fetchRow($recordset));
            }
            
            $email = $row[$field];
        }

        return $email;
    }
}
