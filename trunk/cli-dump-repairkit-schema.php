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
 * cli-dump-repairkit-schema.php
 *
 * Script to extract a copy of the db schema for the repairkit
 * in dbschema/repairkit.schema file.
 *
 * Use this script with a fresh install of a pre-version of a release
 * otherwise you could export changes to your development schema
 * that are not in the release.
 *
 * Save the exported file in SVN before tagging the release.
 *
 * @package wikindx\release\repairkit
 */
 
// Include the config file and check if the CONFIG class is in place
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));

include_once("core/startup/CONSTANTS.php");
include_once("core/libs/FILE.php");
include_once("core/libs/LOCALES.php");
include_once("core/libs/UTILS.php");

// Begin page execution timer and define globals for rendering by template
include_once("core/startup/GLOBALS.php");

// Set up the FACTORY objects of commonly used classes and start the timer.
include_once("core/startup/FACTORY.php");

// Initialize the static config read from config.php file
include_once("core/startup/LOADSTATICCONFIG.php");

include_once("core/startup/LOADCONFIG.php");

FACTORY_LOADCONFIG::getInstance()->loadDBConfig();

FACTORY_LOADCONFIG::getInstance()->loadUserVars();

$db = FACTORY_DB::getInstance();

$dbSchema = $db->createRepairKitDbSchema();

echo "Remove previous RepairKit schema\n";

if (file_exists(WIKINDX_FILE_REPAIRKIT_DB_SCHEMA))
{
    unlink(WIKINDX_FILE_REPAIRKIT_DB_SCHEMA);
}

if ($db->writeRepairKitDbSchema($dbSchema, WIKINDX_FILE_REPAIRKIT_DB_SCHEMA))
{
    echo "RepairKit Schema dump successfull\n";
}
else
{
    echo "RepairKit Schema dump failed\n";
}
