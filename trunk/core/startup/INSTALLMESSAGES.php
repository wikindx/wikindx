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
 * INSTALLMESSAGES class (English)
 *
 * Install and database upgrade messages are kept in English outside the localization system
 */
class INSTALLMESSAGES
{
    private $languageArray = [];
    
    public function __construct()
    {
        $this->languageArray = $this->loadArray();
    }
    /**
     * Grab the message
     *
     * @param string $indexName
     * @param false|string $extra Optional string that replaces '###' in the array element value string. Default is FALSE
     *
     * @return string
     */
    public function text($indexName, $extra = FALSE)
    {
        $message = $this->internaltext($indexName);
        $message = preg_replace("/###/u", trim($extra . ""), $message);
        $message = stripslashes($message);

        return \UTF8\html_uentity_decode(trim($message));
    }
    /**
     * Grab the message
     *
     * @param string $indexName
     *
     * @return string
     */
    private function internaltext($indexName)
    {
        if (!array_key_exists($indexName, $this->languageArray))
        {
            debug_print_backtrace();
            die("<p>Section <strong>$arrayName</strong> not found in translations.</p>");
        }

        return $this->languageArray[$indexName];
    }
    /**
     * English messages
     *
     * @return array
     */
    private function loadArray()
    {
        $array = [
            "continue" => "Continue",
            "username" => "Username",
            "password" => "Password",
            "submit" => "Submit",
			"logonSuperadmin" => "Logon as superAdmin:",
            "install" => "Before being able to use WIKINDX, you must configure it. The minimum initially required are username, password and email. From the Admin menu, after configuration, you can add and edit other settings.",
            // upgradeDB After installing a new version of wikindx, the database may require upgrading
            "installDBHeading" => "Install",
            "installDB1" => "<p>WIKINDX has detected that this is a first installation will proceed with the creation of the database.</p> <p>To report bugs etc., go to: <a href='https://sourceforge.net/p/wikindx/v5bugs/'>https://sourceforge.net/p/wikindx/v5bugs/</a></strong></p>",
            "installDB2" => "<p><strong>Database successfully created</strong></p> <p>The next step you will be asked to create a SuperAdmin account, essential for the maintenance of your new software.</p>",
            "installDB3" => "<p><strong>Successfully configured WIKINDX</strong></p>",
            "upgradeDBHeading" => "Upgrade",
            // upgradeDB To upgrade the database for an upgraded WIKINDX
            "upgradeDBSubmit" => "Upgrade the database",
            // A warning issued if a newly upgraded wikindx requires database upgrading.
            "upgradeDB1" => "
                <p>Minimum version upgradable: <span style='color:red;font-weight:bold'>5.1</span></p>
                <p style='color:red;font-weight:bold'If you have downloaded beta/SVN code, then you are strongly advised not to run it on a production server -- if you wish to test it, either create a new database or make a copy of your existing WIKINDX database and point config.php at it. Wait until all bugs have been dealt with, and the final release version provided, before using the WIKINDX code on a production server.</p> <p>To report bugs etc., go to: <a href='https://sourceforge.net/p/wikindx/v5bugs/'>https://sourceforge.net/p/wikindx/v5bugs/</a></strong></p> WIKINDX has detected that this is the first time a new version is being run and that the database requires upgrading. This upgrading is automatic but only the WIKINDX superAdmin may carry it out.<p> You are <strong>strongly advised</strong> to <strong>back up your old database</strong> first. If you do not do this and you experience the memory errors detailed below when upgrading your only copy of the database, then you will have irrevocably corrupted the database: <em>caveat upgrader</em><p>",
            "upgradeDB2" => "You are not logged on as the superAdmin: please ask that person ### to carry out the upgrade through their web browser.",
            "upgradeDB3" => "The upgrade process may take some time depending upon a number of factors. If your database is large or your server is slow, it is advised to temporarily increase 'max_execution_time' and 'memory_limit' in php.ini and to restart the web server before upgrading. (You can try to increase memory first in config.php -- WIKINDX_MEMORY_LIMIT -- in which case you do not need to restart the server.) During upgrading, PHP error reporting is turned on regardless of your config.php settings.<p> If you get a blank page or an error similar to: 'Fatal error: Allowed memory size of 16777216 bytes exhausted (tried to allocate 38 bytes)', then you must start the upgrade process from the <strong>beginning</strong>:<br> a) Reload your backed-up database or database tables (you have backed up haven't you?);<br> b) Increase PHP memory as per the instructions above (after upgrading, you can set it back to the default 64MB);<br> c) Restart the upgrade process.<p> The upgrade can comprise several stages: as a very rough guide (and depending upon the speed of your server), for pre-v5 databases, the longest stages will take about 25 seconds for a database of 23,000 records and the greatest memory use will be about 50MB. Some of the upgrade stages that are most likely to take the greatest time will safely halt if the scripts notice that 'max_execution_time' is about to be exceeded -- simply keep clicking on the Continue button.<p> WIKINDX v4 and higher ensures that all characters are encoded as UTF-8 (to account for character sets found in many of the world's languages) and the upgrade process does the necessary conversion.  If, however, after the upgrade is complete, you still see characters similar to 'ã¼' or 'ã¶' etc., then you should install and run the repairkit plugin from the Sourceforge.net WIKINDX site.<p> Do not break the process or use browser back or forward buttons.<p> <strong>Before upgrading, you should ensure that all attachments in wikindx3 or wikindx4 have been copied to the new wikindx/data/attachments/ folder -- the upgrade process will remove references to attachments in the database if the attachment files do not exist in the new location.</strong>",
            "upgradeDBSuccess" => "<strong>Database successfully upgraded</strong>",
            "upgradeDBClearConfigBackupFile" => "<strong>Before its migration, a time-stamped backup of your configuration file was copied to the site root directory. You can refer to this file when editing the configuration further through the Admin|Configure interface – otherwise, you can safely delete the file.</strong>",
            // If attachments, sessionData and files folders are in user-defined locations.
            "upgradeDBv5.9" => "<p style='color:red;font-weight:bold'>In WIKINDX 5.9, for security reasons, default file locations have been changed and the option to configure the paths has been removed from config.php. If you had NOT changed the default locations for attachments and files, then these files have been copied to their new locations in data/. Otherwise, you should copy all files in your attachments and files folders to the appropriate folders in the data/ folder. Once you have done this, and if WIKINDX has been unable to do so, you can manually remove the following folders (or equivalents depending on your config.php): attachments/, attachments_cache/, files/, images/, sessionData/.</p>",
            "upgradeDBv6" => "<p style='color:red;font-weight:bold'>In WIKINDX 5.10, component locations have been changed and are installed in components/languages, components/plugins, components/styles, components/templates, and components/vendor folders. If you created custom components or changed the configuration of the official components, the code for the old components remained in place. You have to migrate them manually according to the documentation then delete the languages, plugins, styles, and vendor folders.</p>",
            "upgradeDBv12" => "<p style='color:red;font-weight:bold'>Caution : stage 13 could require you increase the memory limit (\$WIKINDX MEMORY_LIMIT) if you have a lot of statistics entry (you've been using Wikindx for a long time).</p>",
        ];

        return $array;
    }
}
