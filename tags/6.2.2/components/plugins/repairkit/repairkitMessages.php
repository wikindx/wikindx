<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
            "menuFixchars" => dgettext($domain, "Fix chars"),
            "menuMissingrows" => dgettext($domain, "Missing rows"),
            "menuTotals" => dgettext($domain, "Fix totals"),
            "menuDumpSchema" => dgettext($domain, "Dump DB schema"),
            "menuDbIntegrity" => dgettext($domain, "DB integrity"),
            "menuCreators" => dgettext($domain, "Fix creators"),
            /**
             * Headings
             */
            "headingFixchars" => dgettext($domain, "Fix Chars"),
            "headingMissingrows" => dgettext($domain, "Missing Rows"),
            "headingTotals" => dgettext($domain, "Fix Totals"),
            "headingDumpSchema" => dgettext($domain, "Dump Database Schema"),
            "headingDbIntegrity" => dgettext($domain, "Database Integrity"),
            "headingCreators" => dgettext($domain, "Fix Creators"),
            /**
             * Text
             */
            "preamble1" => dgettext($domain, "Depending on the size of your database, this operation can be memory intensive.  The
				plugin turns on error reporting so, if you see a memory error, adjust memory in config.php and
				try again."),
            "preamble2" => dgettext($domain, "It is assumed you have backed up your database."),
            "fixutf8Preamble1" => dgettext($domain, "When upgrading WIKINDX from v3.8.2 to v4.x, not all UTF-8-encoded database
				fields are properly dealt with and you may see characters similar to 'ã¼' or 'ã¶' etc.
				in the WIKINDX. Additionally, as WIKINDX is a system that can accept input from many different
				sources, character encoding can be corrupted right from the start. This plugin will attempt to correct such anomalies.  These
				operations are irreversible so ensure that you have backed up your database. You will get an opportunity to confirm the fixing of data
				when you click on Submit."),
            "missingrowsPreamble" => dgettext($domain, "Sometimes rows required in other tables are not created when new resources are added and this
			can lead to problems in searching etc.  If such missing rows are identified, they will be created and filled with
			default (usually NULL) data."),
            "totalsPreamble" => dgettext($domain, "Fix totals of resources, quotes, paraphrases and musings in the database_summary table."),
            "creatorsPreamble" => dgettext($domain, "Fix various errors regarding creators."),
            "dbIntegrityPreamble1a" => dgettext($domain, "Before checking the database integrity, you should ensure that your database (currently version ###)"),
            "dbIntegrityPreamble1b" => dgettext($domain, "matches the WIKINDX version (currently version ###)."),
            "dbIntegrityPreamble2" => dgettext($domain, "The database structure is correct and there is nothing to fix."),
            "dbIntegrityPreamble3" => dgettext($domain, "The database structure differs to the final release database structure for version ### (see report below). This could be because you have been updating this database with pre-release SVN code that has upgraded the structure in incremental steps. Click on OK to fix this. Note: this fix does NOT fix missing tables or missing fields – it will only fix variances in engine, collation, indices, field types, and null and default values. Remember, this fix is a last resort for those cases where a production (rather than a test) database has been mistakenly upgraded with SVN code. As always, backup your database before proceeding."),
            "success" => dgettext($domain, "Successfully fixed. ###"),
            "missingRowsCount" => dgettext($domain, ": ### resources fixed."),
            "lightFixutf8" => dgettext($domain, "Light UTF-8 fix"),
            "toughFixutf8" => dgettext($domain, "Tough UTF-8 fix"),
            "lightFixutf8Message" => dgettext($domain, "Encode non-UTF-8 characters as UTF-8. This will fix most UTF-8 problems and should be the fix you try first."),
            "toughFixutf8Message" => dgettext($domain, "Encode non-UTF-8 characters as UTF-8. If the light fix does not work, you should try this as a last resort."),
            "fixutf8Preamble2" => dgettext($domain, "The following items show the possibly fixed text that you can now write to the database."),
            "fixUtf8NotFound" => dgettext($domain, "Nothing found to fix."),
            "fixUtf8All" => dgettext($domain, "Fix all"),
            "fixUtf8Selected" => dgettext($domain, "Fix selected"),
            "fixUtf8NotSelected" => dgettext($domain, "Fix not selected"),
            "invalidInput" => dgettext($domain, "Invalid or missing input"),
            "fileWriteError" => dgettext($domain, "Unable to write to file."),
            "fileReadError" => dgettext($domain, "Unable to read file."),
            "dbIntegrityMissingTables" => dgettext($domain, "The database is missing the following tables and cannot be fixed:"),
            "dbIntegrityMissingFields" => dgettext($domain, "The database is missing the following fields and cannot be fixed:"),
            "dbIntegrityInvalidDatetime" => dgettext($domain, "There are invalid datetime field values ('0000-00-00 00:00:00') that must be fixed."),
        ];
    }
}
