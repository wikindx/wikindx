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
    $optionsDefinition = [
        "WIKINDX_BASE_URL" => [],
        "WIKINDX_DB" => [],
        "WIKINDX_DB_HOST" => [],
        "WIKINDX_DB_PASSWORD" => [],
        "WIKINDX_DB_PERSISTENT" => [],
        "WIKINDX_DB_TABLEPREFIX" => [],
        "WIKINDX_DB_USER" => [],
        "WIKINDX_MAX_EXECUTION_TIMEOUT" => [],
        "WIKINDX_MAX_WRITECHUNK" => [],
        "WIKINDX_MEMORY_LIMIT" => [],
        "WIKINDX_PATH_AUTO_DETECTION" => [],
        "WIKINDX_WIKINDX_PATH" => [],
    ];

    /**
     * Check if the minimum PHP version is compatible
     *
     * @return bool
     */
    function isPhpVersionMinCompatible()
    {
        return version_compare(PHP_VERSION, WIKINDX_PHP_VERSION_MIN, '>=');
    }
    
    /**
     * Check if the maximum PHP version is compatible
     *
     * @return bool
     */
    function isPhpVersionMaxCompatible()
    {
        return version_compare(PHP_VERSION, WIKINDX_PHP_VERSION_MAX, '<=');
    }
    
    /**
     * Check if all mandatory PHP extensions are available
     *
     * @return bool
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
     *
     * @return bool
     */
    function isDBEngineVersionMinCompatible($dbo)
    {
        $EngineVersionRaw = $dbo->getStringEngineVersion();
        $EngineVersion = strtolower($EngineVersionRaw);

        if (strstr($EngineVersion, "mariadb")) {
            $VersionMin = WIKINDX_MARIADB_VERSION_MIN; // Check MariaDB version
        } else {
            $VersionMin = WIKINDX_MYSQL_VERSION_MIN; // Check MySql or unknow engine version
        }
     
        // If the current engine version is lower than the minimum needed
        return (strcmp($EngineVersion, $VersionMin) >= 0);
    }
    
    /**
     * Check if the Config class is available
     *
     * @return bool
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
     * @return bool
     */
    function isConfigValid()
    {
        if (isConfigSet())
        {
            include_once(__DIR__ . "/../core/startup/LOADSTATICCONFIG.php");
            return (count(\loadStaticConfig()) == 0);
        }
        
        return FALSE;
    }
    
    /**
     * Check if the Configuration class is up to date (has all it's members)
     *
     * This function checks the existence of all members of the class and not their validity.
     *
     * @return bool
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
                //echo $option . "\n";
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
     * Check if a database connection can be established
     *
     * @return bool
     */
    function isDBConnectionCorrectlyConfigured()
    {
        $configfile = __DIR__ . "/../config.php";
        include_once($configfile);
        $config = new \CONFIG();

        $dbpers = $config->WIKINDX_DB_PERSISTENT;
        $dbhost = $config->WIKINDX_DB_HOST;  
        $dbname = $config->WIKINDX_DB;
        $dbuser = $config->WIKINDX_DB_USER;
        $dbpwd  = $config->WIKINDX_DB_PASSWORD;

        $dbo = new \SQL(FALSE);
        $res = $dbo->testConnection($dbhost, $dbname, $dbuser, $dbpwd, $dbpers);
        foreach($res as $key => $unused) {
            return $key;
        }
    }

    /**
     * Check if the current Wikindx data (db and files) need an upgrade
     *
     * @return bool
     */
    function needInstall()
    {
        if (!\SETUP\isPhpVersionMinCompatible()) {
            echo "I1";
            return TRUE;
        }
        if (!\SETUP\isPhpVersionMaxCompatible()) {
            echo "I2";
            return TRUE;
        }
        if (!\SETUP\areMandatoryPhpExtensionsAvailable()) {
            echo "I3";
            return TRUE;
        }
        if (!\SETUP\isConfigSet()) {
            echo "I4";
            return TRUE;
        }
        if (!\SETUP\isConfigValid()) {
            echo "I5";
            return TRUE;
        }
        if (!\SETUP\isDBConnectionCorrectlyConfigured()) {
            echo "I6";
            return TRUE;
        }
        
        $dbo = new \SQL();
        if (!\SETUP\isDBEngineVersionMinCompatible($dbo)) {
            echo "I7";
            return TRUE;
        }

        // NB: existsTableDatabaseVersion must be the first operation because
        // it reads the db system catalog and can't fail if the db exists
        
        // Check if 'database_summary' table doesn't exist
        if (!existsTableDatabaseVersion($dbo)) {
            echo "I8";
            return TRUE;
        }
        // Check if 'users' table has not been filled with the superadmin account
        if (!existsSuperadminAccount($dbo)) {
            echo "I9";
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Check if the current Wikindx data (db and files) need an upgrade
     *
     * @return bool
     */
    function needUpgrade()
    {
        if (!\SETUP\isConfigUptodate()) {
            echo "U1";
            return TRUE;
        }
        // Check if the database version number is not the same as source code version number
        $dbo = new \SQL();
        if (!\SETUP\isDatabaseVersionUptodate($dbo)) {
            echo "U2";
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Check if 'database_summary' table that stores the version number of the db schema exists
     *
     * This function is used only during the upgrade process.
     *
     * @param object $dbo An SQL object
     *
     * @return bool
     */
    function existsTableDatabaseVersion($dbo)
    {
        return $dbo->tableExists('database_summary');
    }
    
    /**
     * Check if 'database_summary' table that stores the version number of the db schema exists
     *
     * This function is used only during the upgrade process.
     *
     * @param object $dbo An SQL object
     *
     * @return bool
     */
    function isDatabaseVersionUptodate($dbo)
    {
        return (getDatabaseVersion($dbo) == WIKINDX_INTERNAL_VERSION);
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
     * Check if 'users' table has not been filled with the superadmin account
     *
     * This function is used only during the upgrade process.
     *
     * @param object $dbo An SQL object
     *
     * @return bool
     */
    function existsSuperadminAccount($dbo)
    {
        $dbo->formatConditionsOneField(WIKINDX_SUPERADMIN_ID, 'usersId');
        $recordset = $dbo->queryNoError($dbo->selectNoExecute('users', '*'));
        return ($recordset !== FALSE);
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
}

