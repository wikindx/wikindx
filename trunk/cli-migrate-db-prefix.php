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
 * cli-migrate-db-prefix.php
 *
 * Script to migrate a custom db prefix to the db default prefix 'wkx_'
 *
 * For details, see https://wikindx.sourceforge.io/web/6.4.6/install/release-notes/
 *
 * @package wikindx\release\repairkit
 */
 
// Include the config file and check if the CONFIG class is in place
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));

define("DEFAULT_PREFIX", "wkx_");

echo "************************************************************************\n";
echo "*                      DB PREFIX MIGRATION SCRIPT                      *\n";
echo "************************************************************************\n";
echo "\n";

$cnf = new CONFIG();

$db = mysqli_connect($cnf->WIKINDX_DB_HOST, $cnf->WIKINDX_DB_USER, $cnf->WIKINDX_DB_PASSWORD, $cnf->WIKINDX_DB);

if (mysqli_connect_errno() == 0)
{
    // Extract the list of tables
    $tables = dbListAllTables($db, $cnf->WIKINDX_DB);
        
    // Extract currents prefixes
    $AvailablePrefix = [];
    $AvailablePrefix[] = DEFAULT_PREFIX;
    foreach ($tables as $k => $table)
    {
        if (strstr($table, "-", TRUE) !== FALSE)
        {
            $AvailablePrefix[] = strstr($table, "-", TRUE) . "-";
        }
        if (strstr($table, "_", TRUE) !== FALSE)
        {
            $AvailablePrefix[] = strstr($table, "_", TRUE) . "_";
        }
    }
    $AvailablePrefix = array_unique($AvailablePrefix);
    
    $oldprefix = promptListUser("What is the custom prefix to replace (case sensitive)?", $AvailablePrefix, DEFAULT_PREFIX);
    echo "\n";
    
    $oldprefix = trim($oldprefix);
    
    if (strlen($oldprefix) == "")
    {
        echo "FATAL ERROR.\n";
        echo "\n";
        echo "The old prefix is empty.\n";
    }
    elseif ($oldprefix == DEFAULT_PREFIX)
    {
        echo "FATAL ERROR.\n";
        echo "\n";
        echo "The old prefix is the same as the default (" . DEFAULT_PREFIX . ").\n";
    }
    else
    {
        echo "\n";
        echo "OLD PREFIX: " . $oldprefix . "\n";
        echo "NEX PREFIX: " . DEFAULT_PREFIX . "\n";
        echo "\n";
        
        // Find foreign tables
        $tables_errors = [];
        foreach ($tables as $k => $table)
        {
            if ($table == dbbasicTable($table, $oldprefix) && $table == dbbasicTable($table, DEFAULT_PREFIX))
            {
                $tables_errors[] = $table;
            }
        }
        
        if (count($tables_errors) == 0)
        {
            // No errors: migrate
            echo "Renaming...\n";
            echo "\n";
            foreach ($tables as $k => $tablesrc)
            {
                if ($tablesrc != dbbasicTable($tablesrc, $oldprefix))
                {
                    $tabledst = preg_replace("/^" . preg_quote($oldprefix, "/") . "/ui", DEFAULT_PREFIX, $tablesrc);
                    echo $tablesrc . " => " . $tabledst . "\n";
                    dbRenameTable($db, $tablesrc, $tabledst);
                }
            }
            
            echo "\n";
            echo "END WITH SUCCESS\n";
            echo "\n";
        }
        else
        {
            // Errors: abort
            echo "FATAL ERROR.\n";
            echo "\n";
            
            echo "The next version of WIKINDX will remove the prefix from tables.\n";
            echo "\n";
            
            echo "This functionality is only useful for several programs sharing the same\n";
            echo "database with the same table names or several WIKINDX installations in\n";
            echo "the same database. These two practices are to be avoided because they\n";
            echo "are a good way to lose your data. Each software should be isolated in\n";
            echo "its own database for privacy, security, bug resistance and ease of\n";
            echo "maintenance. We believe that very few installs use this feature.\n";
            echo "\n";
            
            echo "'6.4.0' harcoded the 'wkx_' prefix but still offered the possibility\n";
            echo "of 'cheating' with the constant 'WIKINDX_DB_TABLEPREFIX'.\n";
            echo "\n";
            
            echo "This version ('6.4.6') removes the 'cheat mode' and checks that you don't\n";
            echo "have a mix of tables with another app because the next version will rename\n";
            echo "all tables without the prefix. Otherwise, collisions could occur. If you are\n";
            echo "affected by this change please contact us for help with the transition.\n";
            echo "\n";
            
            echo "You must apply one or more of these corrections before you can continue.\n";
            echo "\n";
            
            echo "- If you have written your own plugin with your own tables they should\n";
            echo "  use the default prefix 'wkx_' to be portable.\n";
            echo "- If you have installed another application in the same database, move the\n";
            echo "  tables from WIKINDX, or the database objects from the other application,\n";
            echo "  to its own database.\n";
            echo "- If you have other tables in the database for various reasons,\n";
            echo "  please drop them or move them to another database.\n";
            echo "\n";
            
            echo "The update is blocked because it found suspicious tables:\n";
            echo "\n";
            
            foreach ($tables_errors as $k => $table)
            {
                echo "- " . $table . "\n";
            }
            echo "\n";
            
            echo "The official tables for version '6.4.6' are:\n";
            echo "\n";
            
            $tables_officials = [
                "wkx_bibtex_string",
                "wkx_cache",
                "wkx_category",
                "wkx_collection",
                "wkx_config",
                "wkx_creator",
                "wkx_custom",
                "wkx_import_raw",
                "wkx_keyword",
                "wkx_language",
                "wkx_news",
                "wkx_plugin_localedescription",
                "wkx_plugin_soundexplorer",
                "wkx_plugin_wordprocessor",
                "wkx_publisher",
                "wkx_resource",
                "wkx_resource_attachments",
                "wkx_resource_category",
                "wkx_resource_creator",
                "wkx_resource_custom",
                "wkx_resource_keyword",
                "wkx_resource_language",
                "wkx_resource_metadata",
                "wkx_resource_misc",
                "wkx_resource_page",
                "wkx_resource_text",
                "wkx_resource_timestamp",
                "wkx_resource_url",
                "wkx_resource_user_tags",
                "wkx_resource_year",
                "wkx_session",
                "wkx_statistics_attachment_downloads",
                "wkx_statistics_resource_views",
                "wkx_subcategory",
                "wkx_tag",
                "wkx_temp_storage",
                "wkx_user_bibliography",
                "wkx_user_bibliography_resource",
                "wkx_user_groups",
                "wkx_user_groups_users",
                "wkx_user_keywordgroups",
                "wkx_user_kg_keywords",
                "wkx_user_kg_usergroups",
                "wkx_user_register",
                "wkx_user_tags",
                "wkx_users",
                "wkx_version",
            ];
            foreach ($tables_officials as $k => $table)
            {
                echo "- " . $table . "\n";
            }
            echo "\n";
        }
    }
    
    mysqli_close($db);
}
else
{
    echo "FATAL ERROR.\n";
    echo "\n";
    echo mysqli_connect_error();
    echo "\n";
}



///////////////////////////////////////////////////////////////////////
/// Library
///////////////////////////////////////////////////////////////////////

function promptListUser($promptStr, $AvailableValues, $defaultVal = NULL)
{
    // PRINT => Do you like snails? [default=Y]:
    $promptMsg = "";
    $promptMsg .= $promptStr;
    if($defaultVal) $promptMsg .= ' [' . implode(', ', $AvailableValues) . ';default='. $defaultVal. ']';
    $promptMsg .= ": ";
    
    // Read input and remove CRLF
    do
    {
        echo $promptMsg;
        $CapturedValue = trim(fgets(STDIN));
    }
    while(!in_array($CapturedValue, $AvailableValues) && !($defaultVal != NULL && $CapturedValue == ''));
    
    // Return user input or the default value
    return empty($CapturedValue) ? $defaultVal : $CapturedValue;
}

/**
 * Return a table name without prefix
 *
 * @param string $table A prefixed table name
 * @param string $prefix
 *
 * @return string
 */
function dbbasicTable($table, $prefix)
{
    return preg_replace("/^" . preg_quote($prefix, "/") . "/ui", "", $table);
}
    
/*
 * Rename a table
 *
 * @param int $db
 * @param string $tablesrc Fullname of a source table
 * @param string $tabledst Fullname of a destination table
 */
function dbRenameTable($db, $tablesrc, $tabledst)
{
    $tmpTable = uniqid(DEFAULT_PREFIX);
    
    // Change the name of all tables to lower case (workaround for mySQL engine on case sensitive files systems)
    dbquery($db, "ALTER TABLE `" . $tablesrc . "` RENAME AS `" . $tmpTable . "`;");
    dbquery($db, "ALTER TABLE `" . $tmpTable . "` RENAME AS `" . $tabledst . "`;");
}
 
/**
 * show all tables in db
 *
 * @param int $db
 *
 * @return array
 */
function dbListAllTables($db, $dbname)
{
    $tables = [];

    // For ANSI behavior (MySQL, PG at least)
    // We must always use TABLE_SCHEMA in the WHERE clause
    // and the raw value of TABLE_SCHEMA otherwise MySQL scans
    // the disk for db names and slow down the server
    // https://dev.mysql.com/doc/refman/5.7/en/information-schema-optimization.html
    $recordset = dbquery($db, "
	    SELECT TABLE_NAME
	    FROM INFORMATION_SCHEMA.TABLES
	    WHERE
	        TABLE_TYPE = 'BASE TABLE'
	        AND TABLE_SCHEMA = '" . $dbname . "';
	");

    if ($recordset !== FALSE)
    {
        while ($table = dbfetchRow($recordset))
        {
            $tables[] = $table['TABLE_NAME'];
        }
    }

    return $tables;
}

/**
 * Fetch one row from a recordset
 *
 * @param array $recordset
 *
 * @return array
 */
function dbfetchRow(&$recordset)
{
    if (is_array($recordset))
    {
        $row = current($recordset);
        next($recordset);
    }
    else
    {
        $row = FALSE;
    }

    return $row;
}

/**
 * Execute queries and return recordset
 *
 * If WIKINDX_DEBUG_SQL is TRUE then the script always die on db error.
 *
 * @param int $db
 * @param string $sql
 *
 * @return mixed An array, or a boolean if there are no data to return. Only the first result set is returned
 */
function dbquery($db, $sql)
{
    $execOk = mysqli_multi_query($db, $sql);
    $errno = mysqli_errno($db);
    $error = mysqli_error($db);

    $recordset = mysqli_store_result($db);

    $aRecordset = FALSE;
    if (is_object($recordset))
    {
        /*foreach (range(0, mysqli_num_fields($recordset) - 1) as $k) {
            var_dump(mysqli_fetch_field_direct($recordset, $k));
        }*/
        while ($row = mysqli_fetch_assoc($recordset))
        {
            $aRecordset[] = $row;
        }
        // Never forget to free the driver result,
        // otherwith the next mysqli_multi_query() call will fail
        mysqli_free_result($recordset);
    }
    else
    {
        $aRecordset = $execOk;
    }

    // Drop all subsequent results
    // If there are needed we can add a way to store them in this class
    // with a method to retrieve them as array
    do
    {
    } while (mysqli_more_results($db) && mysqli_next_result($db));

    if (!$execOk)
    {
        echo "ERROR: " . $error . "\n";
    }

    return $aRecordset;
}
