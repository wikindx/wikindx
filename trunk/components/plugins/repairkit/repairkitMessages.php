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
 *	REPAIRKIT plugin -- English messages.
 */
class repairkitMessages
{
    /** array */
    public $text = [];
    
    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            /**
             * Menu items
             */
            "menu" => dgettext($domain, "Repair kit..."),
            "menuMissingrows" => dgettext($domain, "Missing rows"),
            "menuDumpSchema" => dgettext($domain, "Dump DB schema"),
            "menuDbIntegrity" => dgettext($domain, "DB integrity"),
            "menuCreators" => dgettext($domain, "Fix creators"),
            "menuDuplicateUsers" => dgettext($domain, "Deduplicate users"),
            "menuDatetimes" => dgettext($domain, "Fix Datetimes"),
            /**
             * Headings
             */
            "headingMissingrows" => dgettext($domain, "Missing Rows"),
            "headingDumpSchema" => dgettext($domain, "Dump Database Schema"),
            "headingDbIntegrity" => dgettext($domain, "Database Integrity"),
            "headingCreators" => dgettext($domain, "Fix Creators"),
            "headingDuplicateUsers" => dgettext($domain, "Deduplicate users"),
            "headingDatetimes" => dgettext($domain, "Fix Datetimes"),
            /**
             * Text
             */
            "noErrorsFound" => "No errors found",
            "preamble1" => dgettext($domain, "Depending on the size of your database, this operation can be memory intensive. The plugin turns on error reporting so, if you see a memory error, adjust memory in config.php and try again."),
            "preamble2" => dgettext($domain, "It is assumed you have backed up your database."),
            "missingrowsPreamble" => dgettext($domain, "Sometimes rows required in other tables are not created when new resources are added and this can lead to problems in searching etc. If such missing rows are identified, they will be created and filled with default (usually NULL) data."),
            "creatorsPreamble" => dgettext($domain, "Fix various errors regarding creators."),
            "datetimesPreamble" => dgettext($domain, "There are invalid datetime field values ('0000-00-00 00:00:00') that must be fixed."),
            "duplicateUsersPreamble" => dgettext($domain, "The user login must be unique for the authentication to work without error but before version 6.3.0 this constraint did not exist. With this screen you can merge two user accounts by choosing the target account which will be credited with all the data of the second (resources, notes ...)."),
            "duplicateUsersPreamble2" => dgettext($domain, "Please choose two user accounts to merge."),
            "duplicateUsersPreamble3" => dgettext($domain, "For each field, please choose the value to retain in the merged account (only different fields are displayed)."),
            "dbIntegrityPreamble1a" => dgettext($domain, "Before checking the database integrity, you should ensure that your database (currently version ###)"),
            "dbIntegrityPreamble1b" => dgettext($domain, "matches the WIKINDX version (currently version ###)."),
            "dbIntegrityPreamble2" => dgettext($domain, "The database structure is correct and there is nothing to fix."),
            "dbIntegrityPreamble3" => dgettext($domain, "The database structure differs to the final release database structure for version ### (see report below). This could be because you have been updating this database with pre-release SVN code that has upgraded the structure in incremental steps. Click on OK to fix this. Note: this program does NOT fix supernumerary tables or fields when they are not empty, name changes (unless it is only case). Remember, this fix is a last resort for those cases where a production (rather than a test) database has been mistakenly upgraded with SVN code. As always, backup your database before proceeding."),
            "dbIntegrityPreamble4" => dgettext($domain, "Corrections are not all applied at once. They are batch applied from the highest level SQL objects to the lowest level. When one correction depends on another, an SQL object can be redefined and raise a temporary definition error. If a correction crashes, this program may not be complete enough or the database cannot be repaired without human intervention. Open a ticket on SourceForge if you experience a crash."),
            "dbIntegrityCaptionDb" => dgettext($domain, "DB Integrity State"),
            "dbIntegrityCaptionTables" => dgettext($domain, "Tables Integrity State"),
            "dbIntegrityCaptionFields" => dgettext($domain, "Fields Integrity State"),
            "dbIntegrityCaptionIndices" => dgettext($domain, "Indices Integrity State"),
            "success" => dgettext($domain, "Successfully fixed. ###"),
            "missingRowsCount" => dgettext($domain, ": ### resources fixed."),
            "invalidInput" => dgettext($domain, "Invalid or missing input"),
            "fileWriteError" => dgettext($domain, "Unable to write to file."),
            "fileReadError" => dgettext($domain, "Unable to read file."),
        ];
    }
}
