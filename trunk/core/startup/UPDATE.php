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
 * Functions used for the upgrade process
 *
 * @package wikindx\core\update
 */
namespace UPDATE
{
    /**
     * Check if the current WIKINDX data (db and files) need an upgrade
     *
     * @param object $dbo An SQL object
     *
     * @return bool
     */
    function needUpdate($dbo)
    {
        // NB: existsTableVersion must be the first operation because
        // it reads the db system catalog and can't fail if the db exists
        
        // Check if 'database_summary' table doesn't exist
        if (!existsTableVersion($dbo))
        {
            return TRUE;
        }
        // Check if 'users' table has not been filled with the superadmin account
        elseif (!existsSuperadminAccount($dbo))
        {
            return TRUE;
        }
        // Check if the database version number is not the same as source code version number
        elseif (getCoreInternalVersion($dbo) != WIKINDX_INTERNAL_VERSION)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
     * Check if 'version' table that stores the version number of the db schema exists
     *
     * Check also the old names of the version table.
     *
     * This function is used only during the upgrade process.
     *
     * @param object $dbo An SQL object
     *
     * @return bool
     */
    function existsTableVersion($dbo)
    {
        return $dbo->tableExists('version') ||  $dbo->tableExists('wkx_version') || $dbo->tableExists('wkx_database_summary');
    }
    
    /**
     * Return the internal version stored in the database of the core
     *
     * This function is used only during the upgrade process, and the value should be looked up
     * in the field regardless of the version.
     *
     * @param object $dbo An SQL object
     *
     * @return float
     */
    function getCoreInternalVersion($dbo)
    {
        $version = 0.0;
        $ComponentType = "core";
        $ComponentId = "core";
        
        $dbo->formatConditions(["versionComponentType" => $ComponentType]);
        $dbo->formatConditions(['versionComponentId' => $ComponentId]);
        $recordset = $dbo->queryNoError($dbo->selectNoExecute('version', 'versionInternalVersion'));
        // From version 57 (6.4.7)
        if ($recordset !== FALSE)
        {
            $row = $dbo->fetchRow($recordset);
            $version = (float) $row['versionInternalVersion'];
        }
        // Up to version 34 (6.4.0)
        else
        {
            $dbo->formatConditions(["versionComponentType" => $ComponentType]);
            $dbo->formatConditions(['versionComponentId' => $ComponentId]);
            $recordset = $dbo->queryNoError($dbo->selectNoExecute('wkx_version', 'versionInternalVersion'));
            if ($recordset !== FALSE)
            {
                $row = $dbo->fetchRow($recordset);
                $version = (float) $row['versionInternalVersion'];
            }
            // Up to version 33 (6.4.0)
            else
            {
                $recordset = $dbo->queryNoError($dbo->selectNoExecute('wkx_database_summary', '*'));
                if ($recordset !== FALSE)
                {
                    $row = $dbo->fetchRow($recordset);
                    // Up to version 33 (6.4.0)
                    if (array_key_exists('databasesummarySoftwareVersion', $row))
                    {
                        $field = "databasesummarySoftwareVersion";
                    }
                    // Up to version 5.9 (5.9.1)
                    if (array_key_exists('databasesummaryDbVersion', $row))
                    {
                        $field = "databasesummaryDbVersion";
                    }
                    $version = floatval($row[$field]);
                }
            }
        }
        
        return $version;
    }
    
    /**
     * Write an internal version in the database of a component or the core
     *
     * This function is used only during the upgrade process, and the value should be written
     * in the field regardless of the version.
     *
     * If $version is NULL, the version number used it 0.0.
     *
     * @param object $dbo An SQL object
     * @param string $version (Default is NULL)
     */
    function setCoreInternalVersion($dbo, $version = NULL)
    {
        if ($version == NULL)
            $version = (string) 0.0;
        else
            $version = (string) $version;
        
        $version = str_replace(",", ".", $version);
        
        $ComponentType = "core";
        $ComponentId = "core";
        
        // Up to version 5.9 (5.9.1)
        if ($version <= 5.9 && $ComponentId == "core")
        {
            $dbo->update("wkx_database_summary", ["databasesummaryDbVersion" => $version]);
        }
        // Up to version 33 (6.4.0)
        if ($version <= 33.0 && $ComponentId == "core")
        {
            $dbo->update("wkx_database_summary", ["databasesummarySoftwareVersion" => $version]);
        }
        // From version 34 (6.4.0) to 56 (6.4.6)
        if ($version <= 56.0)
        {
            $dbo->formatConditions(["versionComponentType" => $ComponentType]);
            $dbo->formatConditions(["versionComponentId" => $ComponentId]);
            if ($dbo->selectCountOnly("wkx_version", "versionComponentId") == 0)
            {
                $dbo->insert("wkx_version", ["versionComponentType", "versionComponentId"], [$ComponentType, $ComponentId]);
            }
            $dbo->formatConditions(["versionComponentType" => $ComponentType]);
            $dbo->formatConditions(["versionComponentId" => $ComponentId]);
            $dbo->update("wkx_version", ["versionInternalVersion" => $version]);
        }
        // From version 57 (6.4.7)
        if ($version >= 57.0)
        {
            $dbo->formatConditions(["versionComponentType" => $ComponentType]);
            $dbo->formatConditions(["versionComponentId" => $ComponentId]);
            if ($dbo->selectCountOnly("version", "versionComponentId") == 0)
            {
                $dbo->insert("version", ["versionComponentType", "versionComponentId"], [$ComponentType, $ComponentId]);
            }
            $dbo->formatConditions(["versionComponentType" => $ComponentType]);
            $dbo->formatConditions(["versionComponentId" => $ComponentId]);
            $dbo->update("version", ["versionInternalVersion" => $version]);
        }
    }
    
    /**
     * Return the internal version stored in the database of a plugin
     *
     * This function is used only during the upgrade process, and the value should be looked up
     * in the field regardless of the version.
     *
     * @param object $dbo An SQL object
     * @param string $ComponentId Id of a component or 'core' for the WIKINDX core
     *
     * @return float
     */
    function getPluginInternalVersion($dbo, $ComponentId)
    {
        $version = 0.0;
        $ComponentType = "plugin";
        
        $dbo->formatConditions(['versionComponentType' => $ComponentType]);
        $dbo->formatConditions(['versionComponentId' => $ComponentId]);
        $recordset = $dbo->queryNoError($dbo->selectNoExecute('version', 'versionInternalVersion'));
        if ($recordset !== FALSE)
        {
            $row = $dbo->fetchRow($recordset);
            $version = (float) $row['versionInternalVersion'];
        }
        
        return $version;
    }
    
    /**
     * Write an internal version in the database of a plugin
     *
     * This function is used only during the upgrade process, and the value should be written
     * in the field regardless of the version.
     *
     * If $version is NULL, the version number used it 0.0.
     *
     * @param object $dbo An SQL object
     * @param string $ComponentId Id of a component or 'core' for the WIKINDX core
     * @param string $version (Default is NULL)
     */
    function setPluginInternalVersion($dbo, $ComponentId, $version = NULL)
    {
        if ($version == NULL)
            $version = (string) 0.0;
        else
            $version = (string) $version;
        
        $version = str_replace(",", ".", $version);
        
        $ComponentType = "plugin";
        
        $dbo->formatConditions(["versionComponentType" => $ComponentType]);
        $dbo->formatConditions(["versionComponentId" => $ComponentId]);
        if ($dbo->selectCountOnly("version", "versionComponentId") == 0)
        {
            $dbo->insert("version", ["versionComponentType", "versionComponentId"], [$ComponentType, $ComponentId]);
        }
        $dbo->formatConditions(["versionComponentType" => $ComponentType]);
        $dbo->formatConditions(["versionComponentId" => $ComponentId]);
        $dbo->update("version", ["versionInternalVersion" => $version]);
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
        $recordset = $dbo->queryNoError($dbo->selectNoExecute('users', 'usersId'));
        if ($recordset === FALSE)
        {
            $dbo->formatConditionsOneField(WIKINDX_SUPERADMIN_ID, 'usersId');
            $recordset = $dbo->queryNoError($dbo->selectNoExecute('wkx_users', 'usersId'));
        }

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
        if ($recordset === FALSE)
        {
            $recordset = $dbo->queryNoError($dbo->selectNoExecute("wkx_config", "*"));
        }
        if ($recordset !== FALSE)
        {
            $field = "";
            $row = $dbo->fetchRow($recordset);
            // Up to version 5.3
            if (array_key_exists("configContactEmail", $row))
            {
                $field = "configContactEmail";
            }
            // From version 5.4
            if (array_key_exists("configName", $row) && array_key_exists("configVarchar", $row))
            {
                $field = "configVarchar";
                
                // Search the recond
                do
                {
                    if ($row["configName"] == "configContactEmail")
                    {
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
     *
     * @return bool TRUE if able to authenticate
     */
    function logonCheckUpgradeDB($dbo, $username, $password)
    {
        // superAdmin is id '1'
        $dbo->formatConditions(['usersUsername' => $username, 'usersId' => WIKINDX_SUPERADMIN_ID]);
        $recordset = $dbo->queryNoError($dbo->selectNoExecute('users', ['usersId']));
        if ($recordset === FALSE)
        {
            $dbo->formatConditions(['usersUsername' => $username, 'usersId' => WIKINDX_SUPERADMIN_ID]);
            $recordset = $dbo->queryNoError($dbo->selectNoExecute('wkx_users', ['usersId']));
        }
        if ($dbo->numRows($recordset) == 1)
        {
            // verify the password
            $row = $dbo->fetchRow($recordset);
            if (\UTILS\verifyUserPassword($dbo, $row['usersId'], $password))
            {
                return TRUE;
            }
        }

        return FALSE;
    }
}
