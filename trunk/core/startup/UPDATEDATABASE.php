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
 * UPDATEDATABASE
 *
 * Update the database as required
 *
 * @package wikindx\core\startup
 */
class UPDATEDATABASE
{
    /** object */
    private $session;
    /** object */
    private $db;
    /** array */
    private $vars;
    /** object */
    private $messages;
    /** object */
    private $installMessages;
    /** object */
    private $errors;
    /**  int */
    private $oldTime;
    /** string */
    private $interruptStepMessage = FALSE;
    /** string */
    private $endStepMessage = FALSE;
    /** float */
    private $targetVersion = NULL;
    /**
     * UPDATEDATABASE
     */
    public function __construct()
    {
        // Turn on error reporting
        // Since this class is instancied only when a db upgrade
        // is needed we don't need to switch off at the end.
        ini_set('display_errors', TRUE);
        
        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "INSTALLMESSAGES.php"]));
        $this->installMessages = new INSTALLMESSAGES;
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->oldTime = time();
        
        // Force the update of the components.json files in case WIKINDX_COMPONENTS_COMPATIBLE_VERSION["plugin"] changes
        \UTILS\refreshComponentsListCache(TRUE);
        
        $this->startDisplay();
        
        $this->checkDatabase();
        
        $this->session->clearSessionData();
        
        if (GLOBALS::tplVarExists('content'))
        {
            $this->endDisplay();
        }
    }
    /**
     * Create the database structure with the definitions of the dbschema store
     *
     * @param string $pluginPath is the path to the root directory of a plugin. Default is the constant DIRECTORY_SEPARATOR for the core
     */
    public function createDbSchema($pluginPath = DIRECTORY_SEPARATOR)
    {
        // The db schema is stored in a series of SQL file in the directory /dbschema/full for the core
        // or /plugins/<PluginDirectory>/dbschema/full
        $dbSchemaPath =
            WIKINDX_DIR_BASE
            . $pluginPath . WIKINDX_DIR_DB_SCHEMA
            . DIRECTORY_SEPARATOR . 'full';
        foreach (FILE\fileInDirToArray($dbSchemaPath) as $sqlfile)
        {
            $sql = file_get_contents($dbSchemaPath . DIRECTORY_SEPARATOR . $sqlfile);
            $sql = str_replace('%%WIKINDX_DB_TABLEPREFIX%%', WIKINDX_DB_TABLEPREFIX, $sql);
            $this->db->queryNoError($sql);
        }
    }
    /**
     * Update the database structure with the definitions of the dbschema store for a specific version
     *
     * If $wkxVersion is NULL, the version number used is. $this->targetVersion
     *
     * @param string $wkxVersion Version number of Wikindx (Default is NULL)
     * @param string $pluginPath is the path to the root directory of a plugin. Default is the constant DIRECTORY_SEPARATOR for the core
     */
    public function updateDbSchema($wkxVersion = NULL, $pluginPath = DIRECTORY_SEPARATOR)
    {
        if ($wkxVersion == NULL)
            $wkxVersion = (string)$this->targetVersion;
        else
            $wkxVersion = (string)$wkxVersion;
        
        // The db schema is stored in a serie of SQL file in the directory /dbschema/update/<$wkxVersion> for the core
        // or /plugins/<PluginDirectory>/dbschema/update/<$wkxVersion>
        $dbSchemaPath =
            WIKINDX_DIR_BASE
            . $pluginPath . WIKINDX_DIR_DB_SCHEMA
            . DIRECTORY_SEPARATOR . 'update'
            . DIRECTORY_SEPARATOR . $wkxVersion;
        if (file_exists($dbSchemaPath) && is_dir($dbSchemaPath))
        {
            foreach (FILE\fileInDirToArray($dbSchemaPath) as $sqlfile)
            {
                $sql = file_get_contents($dbSchemaPath . DIRECTORY_SEPARATOR . $sqlfile);
                $sql = str_replace('%%WIKINDX_DB_TABLEPREFIX%%', WIKINDX_DB_TABLEPREFIX, $sql);
                $this->db->queryNoError($sql);
            }
        }
        else
        {
            GLOBALS::addTplVar('content', "Fatal error: upgrade not possible. " . $dbSchemaPath . " is not a directory or doesn't exist.");
            $this->endDisplay();
        }
    }
    /**
     * We know we have a database as, if we've reached this stage, we're able to connect to it.
     *
     * Here,
     * 1/ we check we have tables, if not, populate the database with tables and set defaults in
     * database_summary and category table and
     * 2/ populate the config table displaying configuration interface if necessary
     * If no admins yet exist, ask for admin configuration to force the input of at least one admin username/password.
     *
     * @param mixed $error
     */
    private function checkDatabase($error = FALSE)
    {
        // Some users may have one database shared for several different purposes so check for presence of config table
        // 1/
        if (!$this->db->tableExists('config'))
        {
            $this->createDbSchema();
            // NB: The config table is initialized with default values by the LOADCONFIG class that know the name and type of each option
            $this->updateSoftwareVersion(WIKINDX_INTERNAL_VERSION);
            $this->session->setVar("setup_Superadmin", TRUE); // required for gatekeep function in CONFIG.php
        }
        if (array_key_exists('action', $this->vars) && $this->session->getVar("setup_Superadmin") &&
            (($this->vars['action'] == 'continueExecution') || ($this->vars['action'] == 'upgradeDB')))
        {
            $confirm = TRUE;
        }
        else
        {
            $confirm = FALSE;
        }
        if ($error)
        {
            $confirm = FALSE;
        }
        if (!$this->updateDatabase($confirm, $error))
        {
            GLOBALS::addTplVar('content', $this->error->text("dbError", "updateMySQL"));
        }
        // 2/
        $this->checkUsersTable();
    }
    /**
     * Update the database if required based on the currrent version
     *
     * @param bool $confirm
     * @param mixed $error
     *
     * @return bool
     */
    private function updateDatabase($confirm, $error = FALSE)
    {
        $dbVersion = \UPDATE\getDatabaseVersion($this->db);
        
        if ($error)
        {
            GLOBALS::addTplVar("content", "<font color=\"red\">$error</font>");
        }
        
        if (!$confirm)
        {
            $this->confirmUpdateDisplay($dbVersion);

            return FALSE;
        }
        
        // These versions are too old to be upgradable
        if ($dbVersion < WIKINDX_INTERNAL_VERSION_UPGRADE_MIN)
        {
            GLOBALS::addTplVar("content", "
                Your WIKINDX database version is $dbVersion. WIKINDX requires that you first upgrade
                to WIKINDX v" . WIKINDX_INTERNAL_VERSION_UPGRADE_MIN . " or later before attempting to upgrade to the latest version.
                v6.0.8 is recommended as a transition version if you need yet PHP 5.6 support.
                v6.1.0 is recommended as a transition version if you don't need PHP 5.6 support (PHP 7.0 minimum).
            ");
            $this->endDisplay();
        }
        // Something's wrong, we shouldn't be on a future release!
        elseif ($dbVersion > WIKINDX_INTERNAL_VERSION)
        {
            GLOBALS::addTplVar("content", "
                Your WIKINDX database version is $dbVersion.
                This version of the application (" . WIKINDX_PUBLIC_VERSION . ") is not compatible with a version of the database greater than " . WIKINDX_INTERNAL_VERSION . ".
                Please upgrade the application or restore a previous database.
            ");
            $this->endDisplay();
        }
        // Hey, we're already on the right version!
        elseif ($dbVersion == WIKINDX_INTERNAL_VERSION)
        {
            // We answer TRUE and the calling code will create the super administrator account
            return TRUE;
        }
        elseif ($dbVersion < WIKINDX_INTERNAL_VERSION)
        {            
            // Operations always carried out at the start of the last upgrade
            if ($dbVersion + 1 == WIKINDX_INTERNAL_VERSION)
            {
                // Refresh the locales list
                \LOCALES\refreshSystemLocalesCache(TRUE);
                
                // Clear the Smarty cache
                $this->session->setVar("setup_Template", WIKINDX_TEMPLATE_DEFAULT);
                FACTORY_TEMPLATE::getInstance()->loadTemplate(TRUE);
                FACTORY_TEMPLATE::getInstance()->clearAllCache();
            }
            
            // Set the target version of the current version
            // Before 6.0 the internal version is a float incremented by 1/10
            if ($dbVersion < 6.0)
            {
                // 5.2 = upgrade v5.1 to 5.2.2
                // 5.3 = upgrade v5.2.2 to v5.2.2
                // 5.4 = upgrade v5.2.2 to 5.4
                // 5.5 = upgrade v5.4 to 5.5
                // 5.6 = upgrade v5.5 to 5.6
                // 5.7 = upgrade v5.6 to 5.7
                // 5.8 = upgrade v5.7 to 5.8
                // 5.9 = upgrade v5.8 to 5.9
                // 6.0 = upgrade v5.9 to 6
                $this->targetVersion = $dbVersion + 0.1;
            }
            // From 6.0 the internal version is an integer incremented by 1
            else
            {
                $this->targetVersion = $dbVersion + 1;
            }
            
            // Set the upgrade function from the target version
            $func_upgrade = "upgradeTo" . str_replace(".", "_", strval($this->targetVersion));
            
            // Execute the upgrade code OR die if the code is missing
            if (!method_exists($this, $func_upgrade))
            {    
                GLOBALS::addTplVar("content", "Fatal error: upgrade function {$func_upgrade}() is not yet implemented!");
                $this->endDisplay();
            }
            
            // Announces the start of the next step to the superadmin
            $this->checkStatus();
            
            // Disable temporarily all SQL mode to update old databases
            $this->db->setSqlMode('');
            
            // Finally we upgrade!
            $this->$func_upgrade();
            
            // Announces the end of the next step to the superadmin
            $this->pauseExecution();
        }
        // Impossible case except error during delivery or development
        // Constants can be wrong
        else
        {
            GLOBALS::addTplVar("content", "
                <p>Your WIKINDX database version is $dbVersion.</p>
                <p>You encountered an unexpected error. Probably a problem with the configuration of the internal versions. Please check the constants:</p>
                <ul>
                    <li>WIKINDX_PUBLIC_VERSION = " . WIKINDX_PUBLIC_VERSION . "</li>
                    <li>WIKINDX_INTERNAL_VERSION = " . WIKINDX_INTERNAL_VERSION . "/li>
                    <li>WIKINDX_INTERNAL_VERSION_UPGRADE_MIN = " . WIKINDX_INTERNAL_VERSION_UPGRADE_MIN . "/li>
                </ul>
            ");
            $this->endDisplay();
        }
    }
    /**
     * Intercept for initial configuration by admin and, if necessary, display admin configuration interface (new installation means users table is empty).
     */
    private function checkUsersTable()
    {
        if ($this->db->tableIsEmpty('users'))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "modules", "usersgroups", "INITSUPERADMIN.php"]));
            $config = new INITSUPERADMIN(TRUE);
            if (array_key_exists('action', $this->vars) && ($this->vars['action'] == 'usersgroups_INITSUPERADMIN_CORE'))
            {
                GLOBALS::addTplVar('content', $config->writeDb());
            }
            else
            {
                // write preliminary stringLimit, write and superadmin to session and display super configuration screen
                $this->session->setVar("setup_StringLimit", WIKINDX_STRING_LIMIT_DEFAULT);
                $this->session->setVar("setup_Write", TRUE);
                $this->session->setVar("setup_Superadmin", TRUE);
                // superadmin userId is always WIKINDX_SUPERADMIN_ID
                $this->session->setVar("setup_UserId", WIKINDX_SUPERADMIN_ID);
                GLOBALS::addTplVar('content', $config->init(\HTML\p($this->installMessages->text("install"), "error", "center")));
                $this->endDisplay();
            }
        }
    }
    /**
     * Check and print status of update scripts
     */
    private function checkStatus()
    {
        $pString  = \HTML\p("Do <b>not</b> click until each script has finished.");
        $pString .= "<ul>";
        $pString .= "<li>INTERMEDIATE INTERNAL VERSION: <b>" . $this->targetVersion . "</b></li>";
        $pString .= "<li>FINAL INTERNAL VERSION: <b>" . WIKINDX_INTERNAL_VERSION . "</b></li>";
        $pString .= "<li>MAX EXECUTION TIME: " . ini_get("max_execution_time") . " secs.</li>";
        $pString .= "<li>MEMORY LIMIT: " . ini_get("memory_limit") . "</li>";
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * End an upgrade step by displaying a message and a form
     *
     * Can be a pause during a too long operation or the end of a step
     *
     * - For an interrupt, fill in the $interruptStepMessage member before calling the function.
     * - For a normal end of step, fill in the endStepMessage member before calling the function.
     */
    private function pauseExecution()
    {
        // Display the memory used and the tim elapsed after the upgrade step
        $pString  = "<li>ELAPSED TIME: " . (time() - $this->oldTime) . " secs.</li>";
        $pString .= "<li>MEMORY USED: " . memory_get_peak_usage() / 1000000 . " MB</li>";
        $pString .= "<li>DATABASE QUERIES: " . GLOBALS::getDbQueries() . "</li>";
        $pString .= "</ul>";
        if ($this->interruptStepMessage)
        {
            $pString .= \HTML\p($this->interruptStepMessage);
        }
        else
        {
            if ($this->endStepMessage)
            {
                $pString .= \HTML\p($this->endStepMessage);
            }
            if ($this->targetVersion == WIKINDX_INTERNAL_VERSION && \UPDATE\getDatabaseVersion($this->db) == WIKINDX_INTERNAL_VERSION)
            {
                $pString .= \HTML\p("<b>Upgrade finished.</b>");
                $pString .= \HTML\p("Please click on the button to return to the home page.");
            }
            else
            {
                $pString .= \HTML\p("Upgrade to internal version <b>" . $this->targetVersion . "</b> finished.");
                $pString .= \HTML\p("Please click on the button to continue the upgrade.");
            }
        }
        $pString .= \FORM\formHeader('continueExecution');
        $pString .= \HTML\p(\FORM\formSubmit($this->installMessages->text("continue")) . \FORM\formEnd());
        GLOBALS::addTplVar('content', $pString);
        $this->endDisplay();
    }
    /**
     * Continue execution of upgrade after a pause
     */
    private function continueExecution()
    {
        // Nothing to do
    }
    /**
     * Only the superadmin may update the database -- ask for login
     *
     * @param string $currentdbVersion
     */
    private function confirmUpdateDisplay($currentdbVersion)
    {
        
        $pString = "";

        $vars = GLOBALS::getVars();
        $vars['usersUsername'] = isset($vars['usersUsername']) ? $vars['usersUsername'] : '';
        $vars['password'] = isset($vars['password']) ? $vars['password'] : '';
        if (\UPDATE\logonCheckUpgradeDB($this->db, $vars['usersUsername'], $vars['password'], $currentdbVersion))
        {
            $this->session->clearSessionData();
            $this->session->setVar("setup_Superadmin", TRUE);
            $this->session->setVar("setup_Write", TRUE);
            
            $pString .= \HTML\p(
                'CURRENT MAX EXECUTION TIME: ' . ini_get("max_execution_time") . ' secs' . BR
                . 'CURRENT PHP MEMORY LIMIT: ' . ini_get("memory_limit")
            );
            $pString .= \HTML\p($this->installMessages->text("upgradeDB1"));
            $pString .= \HTML\p($this->installMessages->text("upgradeDB3"));
            $pString .= \FORM\formHeader("upgradeDB");
            $pString .= \HTML\p(\FORM\formSubmit($this->installMessages->text("upgradeDBSubmit")), FALSE, 'right');
            $pString .= \FORM\formEnd();
        }
        else
        {
            $email = \UPDATE\getConfigContactEmail($this->db);
            $email = $email ? "(" . $email . ")" : "";
            $pString .= \HTML\p($this->installMessages->text("upgradeDB2", $email));
            
            $pString .= \HTML\p($this->installMessages->text("logonSuperadmin"));
            
            $pString .= \FORM\formHeader("upgradeDBLogon");
            $pString .= \HTML\tableStart('left width50percent');
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->installMessages->text("username") . ":&nbsp;&nbsp;");
            $pString .= \HTML\td(\FORM\textInput(FALSE, "usersUsername"));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->installMessages->text("password") . ":&nbsp;&nbsp;");
            $pString .= \HTML\td(\FORM\passwordInput(FALSE, "password"));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td("&nbsp;");
            $pString .= \HTML\td(\FORM\formSubmit($this->installMessages->text("submit")), 'right');
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \FORM\formEnd();
        }
        GLOBALS::addTplVar('content', $pString);
        
        $this->endDisplay();
    }
    /**
     * Start to emit the HTML page of the upgrade process
     *
     * The upgrade process needs a separate display function so that it does not depend on
     * the template system while the configuration is incomplete.
     */
    private function startDisplay()
    {
        $heading = $this->installMessages->text("upgradeDBHeading");
        $apptilte = WIKINDX_TITLE_DEFAULT;
        
        $string = <<<END
<!DOCTYPE html>
<html>
<head>
    <title>$apptilte $heading</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <style>
        body {
            width: 96%;
            margin: auto;
            padding: 0;
            background-color: #FBF5EF;
            font-family: arial, helvetica, serif;
            font-size: 0.9em;
        }
        
        h1, h2, h3, h4 {
            color: #F90;
            margin: 0 0 0.5em 0;
        }
        
        h1 {
            height:38px;
            margin:0.5em 0 1em 0;
            padding:0;
        }
        
        input {
            background-color: #FFF;
            border: 1px sold black;
            padding: 2px;
        }
        
        input[type=submit] {
            background-color: #FFF;
            border: 1px sold black;
            padding: 0.3em;
        }
    </style>
</head>
<body>
    <h1>$apptilte - $heading</h1>
END;
        echo $string;
        ob_flush();
    }
    /**
     * End to emit the HTML page of the upgrade process
     *
     * The upgrade process needs a separate display function so that it does not depend on
     * the template system while the configuration is incomplete.
     */
    private function endDisplay()
    {
        $content = "";
        foreach (GLOBALS::getTplVar('content') as $h)
        { 
            $content .= $h;
        }
        $apptilte = WIKINDX_TITLE_DEFAULT;
        $version = WIKINDX_PUBLIC_VERSION;
        $string = <<<END
    $content
    <hr>
    <p style='text-align:right'>$apptilte $version</p>
</body>
</html>
END;
        echo $string;
        ob_end_flush();
        die;
    }
    /**
     * Write the internal version in the database
     *
     * If $version is NULL, the version number used it $this->targetVersion.
     *
     * @param string $version (Default is NULL)
     */
    private function updateSoftwareVersion($version = NULL)
    {
        if ($version == NULL)
            $version = (string)$this->targetVersion;
        else
            $version = (string)$version;
        
        $version = str_replace(",", ".", $version);
        if ($version <= 5.9)
        {
            $field = "databasesummaryDbVersion";
        }
        if ($version >= 6.0)
        {
            $field = "databasesummarySoftwareVersion";
        }
        $this->db->update('database_summary', [$field => $version]);
    }
    /**
     * Performs the most common kind of upgrade
     *
     * Upgrade to the targetVersion with the most common operations:
     *
     *  - Simple DB upgrade
     *  - Bump version
     *  - Display upgrade report
     *
     * This function should not be called directly.
     * You must define an upgradeToN function to manage the upgrade of version N.
     * This function is just a helper. This is preferable to avoid performing
     * operations by mistake which corrupt the database.
     */
    private function upgradeToTargetVersion()
    {
        if ($this->targetVersion != NULL)
        {
            $this->updateDbSchema();
            $this->updateSoftwareVersion();
        }
        else
        {
            GLOBALS::addTplVar('content', \HTML\p("Fatal error: upgrade target version undefined. Inconsistent state!"));
            $this->endDisplay();
        }
    }
    /**
     * Upgrade database schema to version 5.2
     *
     * Use MySQL utf8 encode and collation utf8_unicode_520_ci
     * Lowercase all table names
     * Use InnoDB for all tables
     */
    private function upgradeTo5_2()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 5.3
     *
     * 5.3 is a missing internal version, just bump the number
     */
    private function upgradeTo5_3()
    {
        $this->updateSoftwareVersion();
    }
    /**
     * Upgrade database schema to version 5.4.
     *
     * Reconfiguration of config table and shifting many variables to it from config.php
     */
    private function upgradeTo5_4()
    {
        // NB: At this location a migration of the config.php configuration file was necessary
        // but subsequent migrations without changing the name of the variables concerned made it useless.
        // The deleted code can be recovered in SVN at revision 116, in core/startup/UPDATEDATABASE.php file,
        // function writeConfigFile5_4().

        $this->updateDbSchema('5.4-begin');

        if (!$this->db->tableExists('configtemp'))
        {
            // Update db summary no. in case we have attempted to upgrade a database less than 5.3 (we've been through the previous stages successfully)
            $this->updateSoftwareVersion();
            GLOBALS::addTplVar('content', "Fatal error: unable to create 'configtemp' table in the database. Check the database permissions.");
            $this->endDisplay();
        }
        // Read old config and transfer values to temp table
        $row = $this->db->queryFetchFirstRow($this->db->selectNoExecute('config', '*'));
        foreach ($row as $key => $value)
        {
            // In all cases, if a config parameter has an empty value we only keep its name
            if (!$value)
            {
                $this->db->insert('configtemp', ['configName'], [$key]);

                continue; // Jump to the next parameter
            }

            // Paramters no longer used
            if (
                ($key == 'configErrorReport') ||
                ($key == 'configDebugSql')
            ) {
                continue;
            }
            // Deal with varchar(255) type
            elseif (
                ($key == 'configTitle') ||
                ($key == 'configContactEmail') ||
                ($key == 'configLanguage') ||
                ($key == 'configStyle') ||
                ($key == 'configTemplate') ||
                ($key == 'configEmailNewRegistrations'))
            {
                $this->db->insert('configtemp', ['configName', 'configVarchar'], [$key, $value]);
            }
            // Deal with text type
            elseif (
                ($key == 'configDescription') ||
                ($key == 'configNoSort') ||
                ($key == 'configSearchFilter')
            ) {
                $this->db->insert('configtemp', ['configName', 'configText'], [$key, $value]);
            }
            // Deal with varChar(1) type. These are not converted to boolean
            elseif (
                ($key == 'configLastChangesType')
            ) {
                // Can be 'D' for 'last days' or 'N' for set number of resources Рconvert to varChar row
                $tValue = ($value == 'D') ? 'days' : 'number';
                $this->db->insert('configtemp', ['configName', 'configVarchar'], [$key, $tValue]);
            }
            // Deal with varChar(1) type. These are converted to boolean
            elseif (
                ($key == 'configMultiUser') ||
                ($key == 'configUserRegistration') ||
                ($key == 'configUserRegistrationModerate') ||
                ($key == 'configNotify') ||
                ($key == 'configFileAttach') ||
                ($key == 'configFileViewLoggedOnOnly') ||
                ($key == 'configImportBib') ||
                ($key == 'configEmailNews') ||
                ($key == 'configQuarantine') ||
                ($key == 'configListLink') ||
                ($key == 'configEmailStatistics') ||
                ($key == 'configMetadataAllow') ||
                ($key == 'configMetadataUserOnly') ||
                ($key == 'configDenyReadOnly') ||
                ($key == 'configReadOnlyAccess') ||
                ($key == 'configOriginatorEditOnly') ||
                ($key == 'configGlobalEdit')
            ) {
                // 'N' or 'Y' Рconvert to 0 and 1 respectively
                $bValue = ($value == 'N') ? 0 : 1;
                $this->db->insert('configtemp', ['configName', 'configBoolean'], [$key, $bValue]);
            }
            // Deal with int(11) type
            elseif (
                ($key == 'configFileDeleteSeconds') ||
                ($key == 'configPaging') ||
                ($key == 'configPagingMaxLinks') ||
                ($key == 'configStringLimit') ||
                ($key == 'configImgWidthLimit') ||
                ($key == 'configImgHeightLimit') ||
                ($key == 'configMaxPaste') ||
                ($key == 'configLastChanges') ||
                ($key == 'configLastChangesDayLimit') ||
                ($key == 'configPagingTagCloud')
            ) {
                $this->db->insert('configtemp', ['configName', 'configInt'], [$key, $value]);
            }
            // Deal with datetime type
            elseif ($key == 'configStatisticsCompiled')
            {
                $this->db->insert('configtemp', ['configName', 'configDatetime'], [$key, $value]);
            }
        }
        
        // Load a separate config class that containts original constant names
        $tmpconfig = new CONFIG();
        // fv = Name of the field where the option value is stored
        // fn = Name of the field where the option name is stored
        // dv = Default value of the option
        // NB The name of the constants used for the default values could change in the future
        //    but the name of option must remain the same because the next upgrade stage assume
        //    them unchanged and the name of properties in the config class are the original name.
        $cnfFields = [
            ["fv" => "configBoolean", "fn" => "configBypassSmartyCompile",    "cn" => "WIKINDX_BYPASS_SMARTYCOMPILE",      "dv" => WIKINDX_BYPASS_SMARTY_COMPILATION_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configCmsAllow",               "cn" => "WIKINDX_CMS_ALLOW",                 "dv" => WIKINDX_CMS_ALLOW_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configCmsSql",                 "cn" => "WIKINDX_CMS_SQL",                   "dv" => WIKINDX_CMS_SQL_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configDisplayStatistics",      "cn" => "WIKINDX_DISPLAY_STATISTICS",        "dv" => WIKINDX_DISPLAY_STATISTICS_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configDisplayUserStatistics",  "cn" => "WIKINDX_DISPLAY_USER_STATISTICS",   "dv" => WIKINDX_DISPLAY_USER_STATISTICS_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configErrorReport",            "cn" => "WIKINDX_DEBUG_ERRORS",              "dv" => WIKINDX_DEBUG_ERRORS_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configGsAllow",                "cn" => "WIKINDX_GS_ALLOW",                  "dv" => WIKINDX_GS_ALLOW_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configGsAttachment",           "cn" => "WIKINDX_GS_ATTACHMENT",             "dv" => WIKINDX_GS_ATTACHMENT_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configImagesAllow",            "cn" => "WIKINDX_IMAGES_ALLOW",              "dv" => WIKINDX_IMAGES_ALLOW_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configMailServer",             "cn" => "WIKINDX_MAIL_SERVER",               "dv" => WIKINDX_MAIL_USE_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configMailSmtpAuth",           "cn" => "WIKINDX_MAIL_SMTPAUTH",             "dv" => WIKINDX_MAIL_SMTP_AUTH_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configMailSmtpPersist",        "cn" => "WIKINDX_MAIL_SMTPPERSIST",          "dv" => WIKINDX_MAIL_SMTP_PERSIST_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configDebugSql",               "cn" => "WIKINDX_DEBUG_SQL",                 "dv" => WIKINDX_DEBUG_SQL_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configRssAllow",               "cn" => "WIKINDX_RSS_ALLOW",                 "dv" => WIKINDX_RSS_ALLOW_DEFAULT],
            ["fv" => "configBoolean", "fn" => "configRssDisplay",             "cn" => "WIKINDX_RSS_DISPLAY",               "dv" => WIKINDX_RSS_DISPLAY_DEFAULT],
            ["fv" => "configFloat",   "fn" => "configTagHighSize",            "cn" => "WIKINDX_TAG_HIGH_SIZE",             "dv" => WIKINDX_TAG_HIGH_SIZE_DEFAULT],
            ["fv" => "configFloat",   "fn" => "configTagLowSize",             "cn" => "WIKINDX_TAG_LOW_SIZE",              "dv" => WIKINDX_TAG_LOW_SIZE_DEFAULT],
            ["fv" => "configInt",     "fn" => "configImagesMaxSize",          "cn" => "WIKINDX_IMAGES_MAXSIZE",            "dv" => WIKINDX_IMAGES_MAXSIZE_DEFAULT],
            ["fv" => "configInt",     "fn" => "configMailSmtpPort",           "cn" => "WIKINDX_MAIL_SMTPPORT",             "dv" => WIKINDX_MAIL_SMTP_PORT_DEFAULT],
            ["fv" => "configInt",     "fn" => "configRestrictUserId",         "cn" => "WIKINDX_RESTRICT_USERID",           "dv" => WIKINDX_RESTRICT_USERID_DEFAULT],
            ["fv" => "configInt",     "fn" => "configRssLimit",               "cn" => "WIKINDX_RSS_LIMIT",                 "dv" => WIKINDX_RSS_LIMIT_DEFAULT],
            ["fv" => "configText",    "fn" => "configDeactivateResourceTypes","cn" => "WIKINDX_DEACTIVATE_RESOURCE_TYPES", "dv" => WIKINDX_DEACTIVATE_RESOURCE_TYPES_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configTimezone",               "cn" => "WIKINDX_TIMEZONE",                  "dv" => WIKINDX_TIMEZONE_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configCmsBibstyle",            "cn" => "WIKINDX_CMS_BIBSTYLE",              "dv" => WIKINDX_CMS_BIBSTYLE_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configCmsDbPassword",          "cn" => "WIKINDX_CMS_DB_PASSWORD",           "dv" => WIKINDX_CMS_DB_PASSWORD_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configCmsDbUser",              "cn" => "WIKINDX_CMS_DB_USER",               "dv" => WIKINDX_CMS_DB_USER_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configMailBackend",            "cn" => "WIKINDX_MAIL_BACKEND",              "dv" => WIKINDX_MAIL_BACKEND_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configMailFrom",               "cn" => "WIKINDX_MAIL_FROM",                 "dv" => WIKINDX_MAIL_FROM_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configMailReplyTo",            "cn" => "WIKINDX_MAIL_REPLYTO",              "dv" => WIKINDX_MAIL_REPLYTO_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configMailReturnPath",         "cn" => "WIKINDX_MAIL_RETURN_PATH",          "dv" => WIKINDX_MAIL_RETURN_PATH_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configMailSmPath",             "cn" => "WIKINDX_MAIL_SMPATH",               "dv" => WIKINDX_MAIL_SENDMAIL_PATH_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configMailSmtpEncrypt",        "cn" => "WIKINDX_MAIL_SMTPENCRYPT",          "dv" => WIKINDX_MAIL_SMTP_ENCRYPT_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configMailSmtpPassword",       "cn" => "WIKINDX_MAIL_SMTPPASSWORD",         "dv" => WIKINDX_MAIL_SMTP_PASSWORD_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configMailSmtpServer",         "cn" => "WIKINDX_MAIL_SMTPSERVER",           "dv" => WIKINDX_MAIL_SMTPSERVER],
            ["fv" => "configVarchar", "fn" => "configMailSmtpUsername",       "cn" => "WIKINDX_MAIL_SMTPUSERNAME",         "dv" => WIKINDX_MAIL_SMTP_USERNAME_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configRssBibstyle",            "cn" => "WIKINDX_RSS_BIBSTYLE",              "dv" => WIKINDX_RSS_BIBSTYLE_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configRssDescription",         "cn" => "WIKINDX_RSS_DESCRIPTION",           "dv" => WIKINDX_RSS_DESCRIPTION_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configRssTitle",               "cn" => "WIKINDX_RSS_TITLE",                 "dv" => WIKINDX_RSS_TITLE_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configTagHighColour",          "cn" => "WIKINDX_TAG_HIGH_COLOUR",           "dv" => WIKINDX_TAG_HIGH_COLOUR_DEFAULT],
            ["fv" => "configVarchar", "fn" => "configTagLowColour",           "cn" => "WIKINDX_TAG_LOW_COLOUR",            "dv" => WIKINDX_TAG_LOW_COLOUR_DEFAULT],
        ];
        
        // Now copy across selected config.php variables
        foreach ($cnfFields as $cnfField)
        {
            $value = $cnfField["dv"];
            if (
                property_exists($tmpconfig, $cnfField["cn"])
                && isset($tmpconfig->{$cnfField["cn"]})
                && $tmpconfig->{$cnfField["cn"]}
            ) {
                if ($cnfField["fn"] == "configDeactivateResourceTypes" && is_array($tmpconfig->{$cnfField["cn"]}) && !empty($tmpconfig->{$cnfField["cn"]}))
                {
                    $value = base64_encode(serialize($tmpconfig->{$cnfField["cn"]}));
                }
                else
                {
                    $value = $tmpconfig->{$cnfField["cn"]};
                }
            }
            
            $this->db->insert('configtemp', ['configName', $cnfField["fv"]], [$cnfField["fn"], $value]);
        }

        $this->updateDbSchema('5.4-end');

        $this->session->setVar("setup_UserId", WIKINDX_SUPERADMIN_ID);
        $user = FACTORY_USER::getInstance();
        $user->writeSessionPreferences(FALSE);
        
        $this->updateSoftwareVersion();
    }
    /**
     * Upgrade database schema to version 5.5.
     *
     * Addition of new fields to users table for auth security and GDPR
     */
    private function upgradeTo5_5()
    {
        $this->updateDbSchema('5.5');
        $this->updatePluginTables();
        
        $this->updateSoftwareVersion();
    }
    /**
     * Upgrade database schema to version 5.6.
     *
     * Convert the database to utf8 charset and utf8_unicode_ci collation
     */
    private function upgradeTo5_6()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 5.7.
     *
     * Convert the database to utf8mb4 charset and utf8mb4_unicode_520_ci collation
     * Fix resource_metadata.resourcemetadataPrivate size to 1 character
     */
    private function upgradeTo5_7()
    {
        $this->correctIndices();
        $this->updateDbSchema('5.7');
        $this->correctDatetimeFields();
        
        $this->updateSoftwareVersion();
    }
    /**
     * Upgrade database schema to version 5.8. There are no changes to DB structure so no call to updateDbSchema('5.8').
     *
     * Check resource totals are correct
     * Check creator correlations are correct
     */
    private function upgradeTo5_8()
    {
        $this->correctTotals();
        $this->correctCreators();
        
        $this->updateSoftwareVersion();
    }
    /**
     * Upgrade database schema to version 5.9
     *
     * Move directories attachments, attachments_cache, images, and files
     * Change configuration
     * Update images links
     */
    private function upgradeTo5_9()
    {
        // Copy files in various old directories to their new directories
        // Order is important – ned to know if files or attachments returns FALSE
        $return = $this->copyFolderContents('attachments_cache', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_ATTACHMENTS]));
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        $return = $this->copyFolderContents('images', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES]));
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        $return = $this->copyFolderContents('files', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]));
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        $return = $this->copyFolderContents('attachments', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS]));
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        
        // NB: At this location a migration of the config.php configuration file was necessary
        // but subsequent migrations without changing the name of the variables concerned made it useless.
        // The deleted code can be recovered in SVN at revision 116, in core/startup/UPDATEDATABASE.php file,
        // function writeConfigFile5_9().
        
        $this->updateDbSchema('5.9');
        $this->updateImageLinks();
        
        $this->updateSoftwareVersion();
        
        $this->endStepMessage = $this->installMessages->text("upgradeDBv5.9");
    }
    /**
     * Upgrade database schema to version 6
     *
     * Move word processor papers and styles custom styles components
     */
    private function upgradeTo6()
    {
        // Copy files in various old directories to their new directories
        // Order is important – ned to know if files or attachments returns FALSE
        $return = $this->copyWpContents();
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        $return = $this->copyBibContents();
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        $this->updateDbSchema('6');
        
        $this->updateSoftwareVersion();
        
        $this->endStepMessage = $this->installMessages->text("upgradeDBv6");
    }
    /**
     * Upgrade database schema to version 7 (6.0.4)
     */
    private function upgradeTo7()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 8 (6.0.5)
     */
    private function upgradeTo8()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 9 (6.0.6)
     */
    private function upgradeTo9()
    {
        $this->updateImageLinks();
        $return = $this->copyWpContents();
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        
        $this->updateSoftwareVersion();
    }
    /**
     * Upgrade database schema to version 10 (6.0.8)
     *
     * Add FULLTEXT indices
     */
    private function upgradeTo10()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 11 (6.2.1)
     *
     * Convert tag sizes to scale factors
     */
    private function upgradeTo11()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 12 (6.2.2 - part A)
     *
     * Convert tag sizes to scale factors
     */
    private function upgradeTo12()
    {
        $this->updateDbSchema('12');
        
        $this->updateSoftwareVersion();
        
        $this->endStepMessage = $this->installMessages->text("upgradeDBv12");
    }
    /**
     * Upgrade database schema to version 13 (6.2.2 - part B)
     */
    private function upgradeTo13()
    {
        $this->updateDbSchema('13');
        
        $this->transferStatistics();
        
        $this->db->queryNoError("DROP TABLE IF EXISTS " . WIKINDX_DB_TABLEPREFIX . "statistics;");
        $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "resource_misc DROP COLUMN resourcemiscAccesses");
        $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "resource_misc DROP COLUMN resourcemiscAccessesPeriod");
        $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "resource_attachments DROP COLUMN resourceattachmentsDownloads");
        $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "resource_attachments DROP COLUMN resourceattachmentsDownloadsPeriod");
        
        // For a period mid-2018 to mid-2019, resourceattachmentsTimestamp was not written – set these NULL values to current timestamp
        $this->db->formatConditions(['resourceattachmentsTimestamp' => 'IS NULL']);
        $resultSet = $this->db->select('resource_attachments', ['resourceattachmentsId']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            $this->db->formatConditions(['resourceattachmentsId' => $row['resourceattachmentsId']]);
            $this->db->updateTimestamp('resource_attachments', ['resourceattachmentsTimestamp' => '']); // default is CURRENT_TIMESTAMP
        }
        
        $this->updateSoftwareVersion();
    }
    /**
     * Upgrade database schema to version 14 (6.2.2 - part C)
     *
     * Convert tag sizes to scale factors
     */
    private function upgradeTo14()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 15 (6.3.8)
     *
     * Convert tag sizes to scale factors
     */
    private function upgradeTo15()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 16 (6.3.8)
     *
     * This stage was doing a config change that have been moved to stage 24
     */
    private function upgradeTo16()
    {
        $this->updateSoftwareVersion();
    }
    /**
     * Upgrade database schema to version 17 (6.3.8)
     *
     * Convert tag sizes to scale factors
     */
    private function upgradeTo17()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 18 (6.3.8)
     */
    private function upgradeTo18()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 19 (6.3.8)
     *
     * Following change to storage of session arrays, reset the session state variable to NULL
     */
    private function upgradeTo19()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 20 (6.3.8)
     *
     * Create new keyword groups table
     */
    private function upgradeTo20()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 21 (6.3.8)
     *
     * Correct default value in user_kg_usergroups
     */
    private function upgradeTo21()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 22 (6.3.8)
     *
     * Correct default value for usersFullname to '';
     */
    private function upgradeTo22()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 23 (6.4.0)
     *
     * Correct default value for usersFullname to '';
     */
    private function upgradeTo23()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 24 (6.4.0)
     *
     * This code removed fields from the configuration file.
     * It has been moved to version 32 which removes others.
     */
    private function upgradeTo24()
    {
        $this->updateSoftwareVersion();
    }
    /**
     * Upgrade database schema to version 25 (6.4.0)
     */
    private function upgradeTo25()
    {
        // Strip HTML from bibliograpy descriptions
        $updateArray = [];
        $this->db->formatConditions(['userbibliographyDescription' => 'IS NOT NULL']);
        $resultSet = $this->db->select('user_bibliography', ['userbibliographyId', 'userbibliographyDescription']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            $desc = trim(\HTML\stripHtml($row['userbibliographyDescription']));
            if ($desc != $row['userbibliographyDescription'])
            {
                $updateArray[$row['userbibliographyId']] = ($desc != '' ? $desc : 'NULL');
            }
        }
        
        if (count($updateArray) > 0)
        {
            $this->db->multiUpdate('user_bibliography', 'userbibliographyDescription', 'userbibliographyId', $updateArray);
        }
        
        // Strip HTML from attachment descriptions
        $updateArray = [];
        $this->db->formatConditions(['resourceattachmentsDescription' => 'IS NOT NULL']);
        $resultSet = $this->db->select('resource_attachments', ['resourceattachmentsId', 'resourceattachmentsDescription']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            $desc = trim(\HTML\stripHtml($row['resourceattachmentsDescription']));
            if ($desc != $row['resourceattachmentsDescription'])
            {
                $updateArray[$row['resourceattachmentsId']] = ($desc != '' ? $desc : 'NULL');
            }
        }
        
        if (count($updateArray) > 0)
        {
            $this->db->multiUpdate('resource_attachments', 'resourceattachmentsDescription', 'resourceattachmentsId', $updateArray);
        }
        
        $this->updateSoftwareVersion();
    }
    /**
     * Upgrade database schema to version 26 (6.4.0)
     *
     * Drop form_data and create temp_storage table
     */
    private function upgradeTo26()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 27 (6.4.0)
     *
     * Drop form_data and create temp_storage table
     */
    private function upgradeTo27()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 28 (6.4.0)
     *
     * Drop form_data and create temp_storage table
     */
    private function upgradeTo28()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Remove unwanted rows in user_bibliography_resource (6.4.0)
     */
    private function upgradeTo29()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Remove mistakenly named configBrowserTagID from config table (6.4.0)
     *
     * Rename option configBrowserTagID to configBrowserTabID
     */
    private function upgradeTo30()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 31 (6.4.0)
     *
     * Rename option LdapGroupCn to LdapGroupDn
     */
    private function upgradeTo31()
    {
        $this->upgradeToTargetVersion();
    }
    /**
     * Upgrade database schema to version 32 (6.4.0)
     *
     * Remove option WIKINDX_DB_TABLEPREFIX that is now hardcoded
     * Clear missing attachments
     */
    private function upgradeTo32()
    {
        // Die if not possible
        $this->rewriteConfigFile6_4_0();
        
        // Clear attachments
        $attachment = FACTORY_ATTACHMENT::getInstance();
        $attachment->checkAttachmentRows();
        
        $this->updateSoftwareVersion();
    }

    /**
     * Transfer statistics data to new tables then drop old table
     *
     * A fault in the previous statistics compilation means that each month's statistics needs to be backdated one month ...
     */
    private function transferStatistics()
    {
        $resourceInsertFields = ['statisticsresourceviewsResourceId', 'statisticsresourceviewsMonth', 'statisticsresourceviewsCount'];
        $attachmentInsertFields = ['statisticsattachmentdownloadsAttachmentId', 'statisticsattachmentdownloadsMonth', 'statisticsattachmentdownloadsCount', 'statisticsattachmentdownloadsResourceId'];
        
        $countTransfered = 0;
        $insertResourceValues = [];
        $insertAttachmentValues = [];
        $deleteStatisticsAttachment = [];
        $deleteStatisticsResource = [];
        
        // 1. Past statistics from statistics table
        $resultSet = $this->db->select('statistics', ['statisticsId', 'statisticsResourceId', 'statisticsAttachmentId', 'statisticsStatistics']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            $id = $row['statisticsAttachmentId'] ? $row['statisticsAttachmentId'] : $row['statisticsResourceId'];
            
            if ($row['statisticsAttachmentId'])
            {
                $deleteStatisticsAttachment[] = $row['statisticsId'];
            }
            else
            {
                $deleteStatisticsResource[] = $row['statisticsId'];
            }
            
            $statsArray = unserialize(base64_decode($row['statisticsStatistics']));
            if ($statsArray === FALSE)
            {
                continue;
            }
            
            foreach ($statsArray as $month => $count)
            {
                if (!$count)
                { // Ensure there is a valid INSERT value here ...
                    $count = 0;
                }
                
                // If the month (period) is too short or long (YYYYMM format expected), skip this stat
                $month = trim($month . "");
                if (strlen($month) != 6)
                {
                    continue;
                }
                
                $month = intval($month);
                
                // If the month is not in the range 01..12, skip this stat
                if ($month % 100 > 12)
                {
                    continue;
                }
                
                // Shift of one month back
                $month = $month - 1;
                // Month 0 doesn't exist, so shift one year back on december
                $month = ($month % 100 == 0) ? $month - 100 + 12 : $month;
                
                $insertValues = [$id, $month, $count];
                
                if ($row['statisticsAttachmentId'])
                {
                    $deleteStatisticsAttachment[] = $row['statisticsId'];
                    $insertValues[] = $row['statisticsResourceId'];
                    $insertAttachmentValues[] = '(' . implode(',', $insertValues) . ')';
                    
                    if (count($insertAttachmentValues) % 1000 == 0)
                    {
                        $this->db->multiInsert('statistics_attachment_downloads', $attachmentInsertFields, implode(', ', $insertAttachmentValues));
                        $countTransfered += count($insertAttachmentValues);
                        $insertAttachmentValues = [];
                        
                        $this->db->formatConditionsOneField($deleteStatisticsAttachment, 'statisticsId');
                        $this->db->delete('statistics');
                        $deleteStatisticsAttachment = [];
                    }
                }
                else
                {
                    $deleteStatisticsResource[] = $row['statisticsId'];
                    $insertResourceValues[] = '(' . implode(',', $insertValues) . ')';
                    
                    if (count($insertResourceValues) % 1000 == 0)
                    {
                        $this->db->multiInsert('statistics_resource_views', $resourceInsertFields, implode(', ', $insertResourceValues));
                        $countTransfered += count($insertResourceValues);
                        $insertResourceValues = [];
                        
                        $this->db->formatConditionsOneField($deleteStatisticsResource, 'statisticsId');
                        $this->db->delete('statistics');
                        $deleteStatisticsResource = [];
                    }
                }
            }
            
            // Check we have more than 6 seconds buffer before max_execution_time times out.
            if (((time() - $this->oldTime) >= (ini_get("max_execution_time") - 6)) || $countTransfered >= 200000)
            {
                $this->interruptStepMessage  = "<span style='color:red;font-weight:bold'>Caution : stage 13 could require you increase the memory limit (\$WIKINDX MEMORY_LIMIT) if you have a lot of statistics entry (you've been using Wikindx for a long time).</span>";
                $this->interruptStepMessage .= "<br>stage13 continuing: $countTransfered statistics records created this pass.&nbsp;&nbsp;";
                $this->pauseExecution();
            }
        }
        // Remaining past statistics
        if (count($insertAttachmentValues) > 0)
        {
            $this->db->multiInsert('statistics_attachment_downloads', $attachmentInsertFields, implode(', ', $insertAttachmentValues));
        }
        if (count($insertResourceValues) > 0)
        {
            $this->db->multiInsert('statistics_resource_views', $resourceInsertFields, implode(', ', $insertResourceValues));
        }
        if (count($deleteStatisticsAttachment) > 0)
        {
            $this->db->formatConditionsOneField($deleteStatisticsAttachment, 'statisticsId');
            $this->db->delete('statistics');
        }
        if (count($deleteStatisticsResource) > 0)
        {
            $this->db->formatConditionsOneField($deleteStatisticsResource, 'statisticsId');
            $this->db->delete('statistics');
        }
        
        // Check we have more than 6 seconds buffer before max_execution_time times out.
        if (((time() - $this->oldTime) >= (ini_get("max_execution_time") - 6)))
        {
            $this->interruptStepMessage  = "<span style='color:red;font-weight:bold'>Caution : stage 13 could require you increase the memory limit (\$WIKINDX MEMORY_LIMIT) if you have a lot of statistics entry (you've been using Wikindx for a long time).</span>";
            $this->interruptStepMessage .= "<br>stage13 continuing: $countTransfered statistics records created this pass.&nbsp;&nbsp;";
            $this->pauseExecution();
        }
    
        // 2. Current statistics for views
        $month = date('Ym');
        $insertResourceValues = [];
        $resultSet = $this->db->select('resource_misc', ['resourcemiscId', 'resourcemiscAccessesPeriod']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            if (!$row['resourcemiscAccessesPeriod'])
            { // Ensure there is a valid INSERT value here ...
                $count = 1;
            }
            else
            {
                $count = $row['resourcemiscAccessesPeriod'];
            }
            $insertResourceValues[] = '(' . implode(',', [$row['resourcemiscId'], $month, $count]) . ')';
            
            if (count($insertResourceValues) % 5000 == 0)
            {
                $this->db->multiInsert(
                    'statistics_resource_views',
                    ['statisticsresourceviewsResourceId',
                        'statisticsresourceviewsMonth',
                        'statisticsresourceviewsCount', ],
                    implode(', ', $insertResourceValues)
                );
                $insertResourceValues = [];
            }
        }
        if (count($insertResourceValues) > 0)
        {
            $this->db->multiInsert(
                'statistics_resource_views',
                ['statisticsresourceviewsResourceId',
                    'statisticsresourceviewsMonth',
                    'statisticsresourceviewsCount', ],
                implode(', ', $insertResourceValues)
            );
        }
    
        // 3. Current statistics for downloads
        $insertAttachmentValues = [];
        $resultSet = $this->db->select('resource_attachments', ['resourceattachmentsId', 'resourceattachmentsResourceId', 'resourceattachmentsDownloadsPeriod']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            if (!$row['resourceattachmentsDownloadsPeriod'])
            { // Ensure there is a valid INSERT value here ...
                $count = 1;
            }
            else
            {
                $count = $row['resourceattachmentsDownloadsPeriod'];
            }
            $insertAttachmentValues[] = '(' . implode(',', [$row['resourceattachmentsResourceId'], $row['resourceattachmentsId'], $month, $count]) . ')';
            
            if (count($insertAttachmentValues) % 5000 == 0)
            {
                $this->db->multiInsert(
                    'statistics_attachment_downloads',
                    ['statisticsattachmentdownloadsResourceId',
                        'statisticsattachmentdownloadsAttachmentId',
                        'statisticsattachmentdownloadsMonth',
                        'statisticsattachmentdownloadsCount', ],
                    implode(', ', $insertAttachmentValues)
                );
                $insertAttachmentValues = [];
            }
        }
        if (count($insertAttachmentValues) > 0)
        {
            $this->db->multiInsert(
                'statistics_attachment_downloads',
                ['statisticsattachmentdownloadsResourceId',
                    'statisticsattachmentdownloadsAttachmentId',
                    'statisticsattachmentdownloadsMonth',
                    'statisticsattachmentdownloadsCount', ],
                implode(', ', $insertAttachmentValues)
            );
        }
    }
    /**
     * Copy non-official bibliographic styles (if they exist)
     */
    private function copyBibContents()
    {
        $oldDir = 'styles' . DIRECTORY_SEPARATOR . 'bibliography';
        foreach (\FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, $oldDir])) as $dir)
        {
            $dirLower = mb_strtolower($dir);
            
            // Not styles released as part of 5.8.2
            if (array_search($dir, ['abnt', 'apa', 'britishmedicaljournal', 'cfb', 'chicago', 'harvard', 'ieee', 'mla',
                'turabian', 'wikindx', ]) !== FALSE)
            {
                continue;
            }
            
            $oldDirStyle = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, $oldDir, $dir]);
            $newDirStyle = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES, $dirLower]);
            @mkdir($newDirStyle, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
            
            foreach (\FILE\fileInDirToArray($oldDirStyle) as $file)
            {
                if (!copy(
                    $oldDirStyle . DIRECTORY_SEPARATOR . $file,
                    $newDirStyle . DIRECTORY_SEPARATOR . $file
                ))
                {
                    return "Cannot write " . $newDirStyle . DIRECTORY_SEPARATOR . $file;
                }
                else
                {
                    \UTILS\createComponentMetadataFile("style", $dirLower);
                }
            }
        }

        return TRUE;
    }
    /**
     * Copy and update papers in the word processor plugin (if it/they exist)
     */
    private function copyWpContents()
    {
        // Move papers to the new dir
        $newDir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_PLUGINS, 'wordprocessor']);
        foreach (['wordProcessor', 'wordprocessor'] as $plugindir)
        {
            $oldDir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, 'plugins', $plugindir, 'papers']);
            foreach (\FILE\fileInDirToArray($oldDir) as $file)
            {
                if ($file == 'PAPERS.txt')
                {
                    continue;
                }
                $oldFile = $oldDir . DIRECTORY_SEPARATOR . $file;
                $newFile = $newDir . DIRECTORY_SEPARATOR . $file;
                if (!copy($oldFile, $newFile))
                {
                    return "Cannot copy " . $newFile;
                }
                else
                {
                    @unlink($oldFile);
                }
            }
            if (file_exists($oldDir))
            {
                @unlink($oldDir);
            }
        }
        
        // Change image links to the new image dir
        foreach (\FILE\fileInDirToArray($newDir) as $file)
        {
            $file = $newDir . DIRECTORY_SEPARATOR . $file;
            
            $doc = file_get_contents($file);
            $doc = str_replace('<img src="images/', '<img src="' . WIKINDX_URL_DATA_IMAGES . '/', $doc);
        
            // Fix in 6.0.6 a previous error during the migration of images links in papers introduced in 5.9
            // The folder separator must be / and not \ otherwise the image display is broken
            $doc = str_replace('<img src="' . WIKINDX_DIR_DATA . '\\', '<img src="' . WIKINDX_URL_DATA . '/', $doc);
            
            if (!file_put_contents($file, $doc))
            {
                return "Cannot write " . $file;
            }
        }

        return TRUE;
    }
    /**
     * Update 'images' links in metadata to new images folder location
     */
    private function updateImageLinks()
    {
        $this->db->formatConditions($this->db->formatFields('resourcemetadataText') . ' IS NOT NULL');
        $this->db->formatConditions($this->db->formatFields('resourcemetadataText') . $this->db->like('%', '<img src="images/', '%'));
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataText']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $text = $row['resourcemetadataText'];
            $text = str_replace('<img src="images/', '<img src="' . WIKINDX_URL_DATA_IMAGES . '/', $text);
            $this->db->formatConditions(['resourcemetadataId' => $row['resourcemetadataId']]);
            $this->db->update('resource_metadata', ['resourcemetadataText' => $text]);
        }
        
        // Fix in 6.0.6 a previous error during the migration of images links in resources introduced in 5.9
        // The folder separator must be / and not \ otherwise the image display is broken
        $this->db->formatConditions($this->db->formatFields('resourcemetadataText') . ' IS NOT NULL');
        $this->db->formatConditions($this->db->formatFields('resourcemetadataText') . $this->db->like('%', '<img src="' . WIKINDX_DIR_DATA . '\\', '%'));
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataText']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $text = $row['resourcemetadataText'];
            $text = str_replace('<img src="' . WIKINDX_DIR_DATA . '\\', '<img src="' . WIKINDX_DIR_DATA . '/', $text);
            $this->db->formatConditions(['resourcemetadataId' => $row['resourcemetadataId']]);
            $this->db->update('resource_metadata', ['resourcemetadataText' => $text]);
        }
    }
    /**
     * Copy folder contents
     * Code modified from: https://stackoverflow.com/questions/2082138/move-all-files-in-a-folder-to-another
     *
     * @param mixed $old source folder
     * @param mixed $new destination folder
     */
    private function copyFolderContents($old, $new)
    {
        if (!file_exists($old))
        {
            return TRUE; // nothing to do . . .
        }
        $delete = [];
        // Get array of all source files
        $files = scandir($old);
        // Identify directories
        $source = $old . DIRECTORY_SEPARATOR;
        $destination = $new . DIRECTORY_SEPARATOR;
        // Cycle through all source files
        foreach ($files as $file)
        {
            if (in_array($file, [".",".."]))
            {
                continue;
            }
            // If we copied this successfully, mark it for deletion
            if (copy($source . $file, $destination . $file))
            {
                $delete[] = $source . $file;
            }
        }
        // Delete all successfully-copied files
        foreach ($delete as $file)
        {
            unlink($file);
        }
        // Remove old directory
        @unlink($old);

        return TRUE;
    }
    /**
     * Correct resource totals.
     */
    private function correctTotals()
    {
        $num = $this->db->numRows($this->db->select('resource', 'resourceId'));
        $this->db->update('database_summary', ['databasesummaryTotalResources' => $num]);
        $this->db->formatConditions(['resourcemetadataType' => 'q']);
        $num = $this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId'));
        $this->db->update('database_summary', ['databasesummaryTotalQuotes' => $num]);
        $this->db->formatConditions(['resourcemetadataType' => 'p']);
        $num = $this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId'));
        $this->db->update('database_summary', ['databasesummaryTotalParaphrases' => $num]);
        $this->db->formatConditions(['resourcemetadataType' => 'm']);
        $num = $this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId'));
        $this->db->update('database_summary', ['databasesummaryTotalMusings' => $num]);
    }
    /**
     * Fix creator errors
     * In some cases, 'resourcecreatorCreatorSurname' does not match the id in resourcecreatorCreatorMain
     */
    private function correctCreators()
    {
        $max_execution_time = ini_get("max_execution_time");
        $stage5_8_correctCreators_resourcecreatorId = $this->session->getVar("stage5_8_correctCreators_resourcecreatorId", -1);
        $creatorIds = [];
        $updateArray = [];
        $count = 0;
        $this->db->formatConditions(['resourcecreatorCreatorMain' => 'IS NOT NULL']);
        $this->db->orderBy('resourcecreatorId');
        $resultSet1 = $this->db->select('resource_creator', ['resourcecreatorId', 'resourcecreatorCreatorMain', 'resourcecreatorCreatorSurname']);
        $resultSet2 = $this->db->select('creator', ['creatorId', 'creatorSurname']);
        while ($row = $this->db->fetchRow($resultSet2))
        {
            $creatorIds[$row['creatorId']] = mb_strtolower(preg_replace("/[^[:alnum:][:space:]]/u", '', $row['creatorSurname']));
        }
        
        if ($stage5_8_correctCreators_resourcecreatorId != -1)
        {
            while ($row = $this->db->fetchRow($resultSet1))
            {
                if ($row['resourcecreatorId'] == $stage5_8_correctCreators_resourcecreatorId)
                {
                    break;
                }
            }
        }
        while ($row = $this->db->fetchRow($resultSet1))
        {
            $count++;
            
            if (!array_key_exists($row['resourcecreatorCreatorMain'], $creatorIds) || $row['resourcecreatorCreatorMain'] == NULL)
            {
                $this->db->formatConditions(['resourcecreatorCreatorMain' => $row['resourcecreatorCreatorMain']]);
                $this->db->update('resource_creator', ['resourcecreatorCreatorSurname' => "NULL", "resourcecreatorCreatorMain" => "NULL"]);
            }
            else
            {
                $updateArray[$row['resourcecreatorCreatorMain']] = $creatorIds[$row['resourcecreatorCreatorMain']];
            }
        }
        
        if (count($updateArray) > 0)
        {
            $this->db->multiUpdate('resource_creator', 'resourcecreatorCreatorSurname', 'resourcecreatorCreatorMain', $updateArray);
        }
    }
    /**
     * Correct parameters of indices that are varchars by ensuring they have a limited prefix of (100)
     *
     * There is no DROP INDEX IF EXISTS . . . syntax so another way must be found to check if the index exists before trying to drop it
     */
    private function correctIndices()
    {
        $db = WIKINDX_DB;
        foreach (['category', 'collection', 'config', 'creator', 'keyword', 'publisher', 'resource', 'resource_creator',
            'resource_metadata', 'resource_year', 'user_bibliography', ] as $table)
        {
            $table = WIKINDX_DB_TABLEPREFIX . $table;
            $resultSet = $this->db->query("SHOW INDEX FROM `$table` FROM `$db`");
            while ($row = $this->db->fetchRow($resultSet))
            {
                if ($row['Key_name'] == 'categoryCategory')
                {
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
                elseif ($row['Key_name'] == 'collectionTitle')
                {
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
                elseif ($row['Key_name'] == 'configName')
                {
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
                elseif ($row['Key_name'] == 'creatorSurname')
                {
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
                elseif ($row['Key_name'] == 'keywordKeyword')
                {
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
                elseif ($row['Key_name'] == 'publisherName')
                {
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
                elseif ($row['Key_name'] == 'resourceType')
                {
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
                elseif ($row['Key_name'] == 'resourceTitleSort')
                { // NB not to be added again
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
                elseif ($row['Key_name'] == 'resourcecreatorCreatorSurname')
                {
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
                elseif ($row['Key_name'] == 'resourcemetadataResourceId')
                { // NB not a varchar index . . .
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
                elseif ($row['Key_name'] == 'resourceyearYear1')
                {
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
                elseif ($row['Key_name'] == 'userbibliographyTitle')
                {
                    $this->db->query("DROP INDEX `" . $row['Key_name'] . "` ON `$table`");
                }
            }
        }
    }
    /**
     * Correct anomalies in the datetime fields Рthere should be no occurrence of '0000-00-00 00:00:00' as a value.
     *
     * The strategy is:
     * 1. If default is NULL, set all incorrect values to that. Otherwise,
     * 2. Find the minimum value in the table then set all incorrect fields to that. Otherwise,
     * 3. If all values are incorrect, then set all values to default.
     */
    private function correctDatetimeFields()
    {
        // user_register
        $this->db->formatConditions($this->db->formatFields('userregisterTimestamp'));
        $minArray = $this->db->selectMin('user_register', 'userregisterTimestamp');
        $min = $minArray[0]['userregisterTimestamp'];
        $this->db->formatConditions(['userregisterTimestamp' => '0000-00-00 00:00:00']);
        if ($min)
        {
            $this->db->updateTimestamp('user_register', ['userregisterTimestamp' => $this->db->tidyInput($min)]);
        }
        else
        {
            $this->db->updateTimestamp('user_register', ['userregisterTimestamp' => '']); // default is CURRENT_TIMESTAMP
        }
        // users
        $this->db->formatConditions($this->db->formatFields('usersTimestamp'));
        $minArray = $this->db->selectMin('users', 'usersTimestamp');
        $min = $minArray[0]['usersTimestamp'];
        $this->db->formatConditions(['usersTimestamp' => '0000-00-00 00:00:00']);
        if ($min)
        {
            $this->db->updateTimestamp('users', ['usersTimestamp' => $this->db->tidyInput($min)]);
        }
        else
        {
            $this->db->updateTimestamp('users', ['usersTimestamp' => '']); // default is CURRENT_TIMESTAMP
        }
        $this->db->formatConditions($this->db->formatFields('usersNotifyTimestamp'));
        $minArray = $this->db->selectMin('users', 'usersNotifyTimestamp');
        $min = $minArray[0]['usersNotifyTimestamp'];
        $this->db->formatConditions(['usersNotifyTimestamp' => '0000-00-00 00:00:00']);
        if ($min)
        {
            $this->db->updateTimestamp('users', ['usersNotifyTimestamp' => $this->db->tidyInput($min)]);
        }
        else
        {
            $this->db->updateTimestamp('users', ['usersNotifyTimestamp' => '']); // default is CURRENT_TIMESTAMP
        }
        // resource_timestamp
        $this->db->formatConditions($this->db->formatFields('resourcetimestampTimestampAdd'));
        $minArray = $this->db->selectMin('resource_timestamp', 'resourcetimestampTimestampAdd');
        $min = $minArray[0]['resourcetimestampTimestampAdd'];
        $this->db->formatConditions(['resourcetimestampTimestampAdd' => '0000-00-00 00:00:00']);
        if ($min)
        {
            $this->db->updateTimestamp('resource_timestamp', ['resourcetimestampTimestampAdd' => $this->db->tidyInput($min)]);
        }
        else
        {
            $this->db->updateTimestamp('resource_timestamp', ['resourcetimestampTimestampAdd' => '']); // default is CURRENT_TIMESTAMP
        }
        $this->db->formatConditions($this->db->formatFields('resourcetimestampTimestamp'));
        $minArray = $this->db->selectMin('resource_timestamp', 'resourcetimestampTimestamp');
        $min = $minArray[0]['resourcetimestampTimestamp'];
        $this->db->formatConditions(['resourcetimestampTimestamp' => '0000-00-00 00:00:00']);
        if ($min)
        {
            $this->db->updateTimestamp('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->tidyInput($min)]);
        }
        else
        {
            $this->db->updateTimestamp('resource_timestamp', ['resourcetimestampTimestamp' => '']); // default is CURRENT_TIMESTAMP
        }
        // resource_attachments
        $this->db->formatConditions($this->db->formatFields('resourceattachmentsEmbargoUntil'));
        $minArray = $this->db->selectMin('resource_attachments', 'resourceattachmentsEmbargoUntil');
        $min = $minArray[0]['resourceattachmentsEmbargoUntil'];
        $this->db->formatConditions(['resourceattachmentsEmbargoUntil' => '0000-00-00 00:00:00']);
        if ($min)
        {
            $this->db->updateTimestamp('resource_attachments', ['resourceattachmentsEmbargoUntil' => $this->db->tidyInput($min)]);
        }
        else
        {
            $this->db->updateTimestamp('resource_attachments', ['resourceattachmentsEmbargoUntil' => '']); // default is CURRENT_TIMESTAMP
        }
        $this->db->formatConditions($this->db->formatFields('resourceattachmentsTimestamp'));
        $minArray = $this->db->selectMin('resource_attachments', 'resourceattachmentsTimestamp');
        $min = $minArray[0]['resourceattachmentsTimestamp'];
        $this->db->formatConditions(['resourceattachmentsTimestamp' => '0000-00-00 00:00:00']);
        if ($min)
        {
            $this->db->updateTimestamp('resource_attachments', ['resourceattachmentsTimestamp' => $this->db->tidyInput($min)]);
        }
        else
        {
            $this->db->updateTimestamp('resource_attachments', ['resourceattachmentsTimestamp' => '']); // default is CURRENT_TIMESTAMP
        }
        // news
        $this->db->formatConditions($this->db->formatFields('newsTimestamp'));
        $minArray = $this->db->selectMin('news', 'newsTimestamp');
        $min = $minArray[0]['newsTimestamp'];
        $this->db->formatConditions(['newsTimestamp' => '0000-00-00 00:00:00']);
        if ($min)
        {
            $this->db->updateTimestamp('news', ['newsTimestamp' => $this->db->tidyInput($min)]);
        }
        else
        {
            $this->db->updateTimestamp('news', ['newsTimestamp' => '']); // default is CURRENT_TIMESTAMP
        }
        // resource_metadata
        $this->db->formatConditions($this->db->formatFields('resourcemetadataTimestamp'));
        $minArray = $this->db->selectMin('resource_metadata', 'resourcemetadataTimestamp');
        $min = $minArray[0]['resourcemetadataTimestamp'];
        $this->db->formatConditions(['resourcemetadataTimestamp' => '0000-00-00 00:00:00']);
        if ($min)
        {
            $this->db->updateTimestamp('resource_metadata', ['resourcemetadataTimestamp' => $this->db->tidyInput($min)]);
        }
        else
        {
            $this->db->updateTimestamp('resource_metadata', ['resourcemetadataTimestamp' => '']);
        }
        $this->db->formatConditions(['resourcemetadataTimestampEdited' => '0000-00-00 00:00:00']);
        $this->db->updateNull('resource_metadata', 'resourcemetadataTimestampEdited'); // default is NULL
    }
    /**
     * Check permissions on config.php
     *
     * 6.4.0 (internal version 32) is the last version to modify the config file
     */
    private function checkConfigFile()
    {
        $message = HTML\p("
            Part of the upgrade process for a WIKINDX that is younger than v6.2.2 is the transfer of many settings in config.php to the database (from where they can be configured via the Admin|Configure menu).
            In order to accomplish this, config.php must be writeable by the web server user and the upgrade will not proceed until this is the case.
            Equally, some settings are removed from config.php by 6.4.0.
            Once the upgrade has completed, you can then return the file permissions on config.php to read only.
        ");
        $fic_config = implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "config.php"]);
        if (!file_exists($fic_config))
        {
            $message .= HTML\p("Fatal error: {$fic_config} does not exist.");
            GLOBALS::addTplVar("content", $message);
            $this->endDisplay();
        }
        elseif (!is_writable($fic_config))
        {
            $permissions = mb_substr(sprintf('%o', fileperms($fic_config)), -4);
            $message .= HTML\p("The permissions on config.php are currently: " . $permissions . ". The upgrade requires the file to be writeable.");
            
            GLOBALS::addTplVar("content", $message);
            $this->endDisplay();
        }
    }
    /**
     * Write new config.php with upgrade to >= WIKINDX v6.3.10
     */
    private function rewriteConfigFile6_4_0()
    {
        // As WIKINDX v5.3, v5.9, v6.2.2 and v6.4.0 (DB version 12.0) transfers config.php variables to the database, config.php must be writeable before we can proceed
        // Previously, each of these versions modified the configuration, but since they are backward compatible, only the last one is kept.
        $this->checkConfigFile(); // die if not writeable or file does not exist.
        
        // Load a separate config class that containts original constant names
        $tmpconfig = new CONFIG();
        
        $string = <<<END
<?php
/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
**********************************************************************************/
/**
*
* WIKINDX CONFIGURATION FILE
*
* NB. BEFORE YOU MAKE CHANGES TO THIS FILE, BACK IT UP!
* NB. BEFORE YOU MAKE CHANGES TO THIS FILE, BACK IT UP!
* NB. BEFORE YOU MAKE CHANGES TO THIS FILE, BACK IT UP!
*
* If you make changes, backup the edited file as future upgrades of WIKINDX might overwrite this file - no questions asked!
*/

/**********************************************************************************/

class CONFIG
{
/*****
* START DATABASE CONFIGURATION
*****/
// NB:
// wikindx supports only MySQL with mysqli PHP driver (WIKINDX_DB_TYPE parameter is deprecated).
//
// The database and permissions for accessing it must be created using your RDBMS client. Wikindx
// will NOT do this for you.  If unsure how to do this, contact your server admin. After you have
// set up an empty database with the correct permissions (GRANT ALL), the first running of Wikindx
// will create the necessary database tables.
//
// WIKINDX uses caching in the database _cache table for lists of creators, keywords etc.  If you have a large
// database, you may get SQL errors as WIKINDX attempts to write these cache data.  You will need to increase
// max allowed packet in my.cnf and restart the MySQL server.
//
// Host on which the relational db management system (i.e. the MySQL server) is running (usually localhost if
// the web files are on the same server as the RDBMS although some web hosting services may specify something like
// localhost:/tmp/mysql5.sock)
// If your DB server is on a non-standard socket (i.e. not port 3306), then you should set something like localhost:xxxx
// where 'xxxx' is the non-standard socket.

END;
        $string .= 'public $WIKINDX_DB_HOST = "' . $tmpconfig->WIKINDX_DB_HOST . '";' . "\n";
        $string .= '// name of the database which these scripts interface with (case-sensitive):' . "\n" .
                   'public $WIKINDX_DB = "' . $tmpconfig->WIKINDX_DB . '";' . "\n";
        $string .= '// username and password required to connect to and open the database' . "\n" .
                   '// (it is strongly recommended that you change these default values):' . "\n" .
                   'public $WIKINDX_DB_USER = "' . $tmpconfig->WIKINDX_DB_USER . '";' . "\n" .
                   'public $WIKINDX_DB_PASSWORD = "' . $tmpconfig->WIKINDX_DB_PASSWORD . '";' . "\n";
        $string .= <<<END
/*****
* END DATABASE CONFIGURATION
*****/

/**********************************************************************************/

/*****
* START PATHS CONFIGURATION
*****/
// The auto-detection of the path installation and the base url is an experimental feature
// which you can disable by changing this parameter to FALSE.
// If you deactivate auto-detection you must fill in the option WIKINDX_URL_BASE.
// If you don't define this option, auto-detection is enabled by default.

END;
        $string .= 'public $WIKINDX_PATH_AUTO_DETECTION = TRUE;' . "\n";

        $string .= <<<END
// If option auto-detection is disabled you must define the base URL for the WIKINDX installation.
// You have to indicate protocol HTTP / HTTPS and remove the terminal /.
// e.g. if wikindx's index.php file is in /wikindx/ under the httpd/ (or similar)
// folder on the www.myserver.com, then set the variable
// to http://www.myserver.com/wikindx
// Otherwise, leave as "".

END;
        if (property_exists($tmpconfig, 'WIKINDX_BASE_URL'))
        {
            $string .= 'public $WIKINDX_URL_BASE = "' . $tmpconfig->WIKINDX_BASE_URL . '";' . "\n";
        }
        elseif (property_exists($tmpconfig, 'WIKINDX_URL_BASE'))
        {
            $string .= 'public $WIKINDX_URL_BASE = "' . $tmpconfig->WIKINDX_URL_BASE . '";' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_URL_BASE = "";' . "\n";
        }
        
        $string .= <<<END
/*****
* END PATHS CONFIGURATION
*****/

/**********************************************************************************/

/*****
* START PHP MEMORY AND EXECUTION CONFIGURATION
*****/
// WIKINDX usually runs with the standard PHP memory_limit of 64MB.
// With some PHP configurations, however, this is not enough -- a mysterious blank page is often the result.
// If you are unable to update php.ini's memory_limit yourself, WIKINDX_MEMORY_LIMIT may be set (an integer such as 64 or 128 followed by 'M').
// Despite the PHP manual stating that this may not be set outside of php.ini, it seems to work most of the time.
// It is not, however, guaranteed to do so and editing php.ini is the preferred method particularly if your PHP is in 'safe' mode.
// Use double quotes around the value.

END;
        if (property_exists($tmpconfig, 'WIKINDX_MEMORY_LIMIT') && ($tmpconfig->WIKINDX_MEMORY_LIMIT !== FALSE))
        {
            $string .= 'public $WIKINDX_MEMORY_LIMIT = "' . $tmpconfig->WIKINDX_MEMORY_LIMIT . '";' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_MEMORY_LIMIT = FALSE;' . "\n";
        }
        $string .= <<<END
// WIKINDX should run fine with the PHP standard execution timeouts (typically 30 seconds) but,
// in some cases such as database upgrading of a large database on a slow server, you will need to increase the timeout figure.
// If this is FALSE, the value set in php.ini is used.
// Despite the PHP manual stating that this may not be set outside of php.ini, it seems to work most of the time.
// It is not, however, guaranteed to do so and editing php.ini is the preferred method particularly if your PHP is in 'safe' mode.
// The value is in seconds.
// Do NOT use quotes around the value.

END;
        if (property_exists($tmpconfig, 'WIKINDX_MAX_EXECUTION_TIMEOUT') && ($tmpconfig->WIKINDX_MAX_EXECUTION_TIMEOUT !== FALSE))
        {
            $string .= 'public $WIKINDX_MAX_EXECUTION_TIMEOUT = ' . $tmpconfig->WIKINDX_MAX_EXECUTION_TIMEOUT . ';' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_MAX_EXECUTION_TIMEOUT = FALSE;' . "\n";
        }
        $string .= <<<END
// WIKINDX_MAX_WRITECHUNK concerns how many resources are exported and written to file in one go.
// If your WIKINDX contains several thousands of resources and you wish to export them all (e.g. to bibTeX or Endnote),
// then you may run into memory problems which will manifest as either
// a blank page when you attempt to export or an error report (if you have error reporting turned on).
// WIKINDX_MAX_WRITECHUNK breaks down the SQL querying of resources and subsequent writing of resources to file into manageable chunks.
// As a rough guide, with a WIKINDX_MEMORY_LIMIT of 64MB, WIKINDX_MAX_WRITECHUNK of 700 should work fine and with 64M, 1500 works fine.
// If WIKINDX_MAX_WRITECHUNK is FALSE, the chunk is set to 10,000.
// This can be a tricky figure to set as setting the figure too low increases SQL and PHP execution times significantly.
// Do NOT use quotes around the value.

END;
        if (property_exists($tmpconfig, 'WIKINDX_MAX_WRITECHUNK') && ($tmpconfig->WIKINDX_MAX_WRITECHUNK !== FALSE))
        {
            $string .= 'public $WIKINDX_MAX_WRITECHUNK = ' . $tmpconfig->WIKINDX_MAX_WRITECHUNK . ';' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_MAX_WRITECHUNK = FALSE;' . "\n";
        }
        $string .= <<<END
/*****
* END PHP MEMORY AND EXECUTION CONFIGURATION
*****/
}
END;
        $string .= "\n";


        // Save the old config file before writing it
        // Something could go wrong and configuration lost otherwise
        $cf = 'config.php';
        $bf = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES, $cf . '.' . date('YmdHis')]);
        if (copy($cf, $bf))
        {
            if (is_writable($cf))
            {
                if (file_put_contents($cf, $string) === FALSE)
                {
                    GLOBALS::addTplVar('content', "Fatal error: an error occurred when writing to $cf");
                    $this->endDisplay();
                }
            }
            else
            {
                GLOBALS::addTplVar('content', "Fatal error: $cf is not writable");
                $this->endDisplay();
            }
        }
        else
        {
            GLOBALS::addTplVar('content', "Fatal error: could not backup $cf to $bf");
            $this->endDisplay();
        }
    }
    /**
     * Copy papers table (word processor) to new format if it exists then drop it. Upgrade the soundExplorer table
     */
    private function updatePluginTables()
    {
        // NB: Windows MySQL lowercases any table name
        // To be sure, it is necessary to lowercase all table elements
        $tables = $this->db->listTables(FALSE);
        foreach ($tables as $k => $v)
        {
            $tables[$k] = mb_strtolower($v);
        }
        // If there is an existing papers table, copy fields across and drop table
        if (array_search('papers', $tables) !== FALSE)
        {
            if (array_search('plugin_wordprocessor', $tables) === FALSE)
            {
                $this->db->queryNoError("
                    CREATE TABLE `" . WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor` (
                        `pluginwordprocessorId` int(11) NOT NULL AUTO_INCREMENT,
                        `pluginwordprocessorHashFilename` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                        `pluginwordprocessorFilename` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                        `pluginwordprocessorUserId` int(11) NOT NULL,
                        `pluginwordprocessorTimestamp` datetime NOT NULL,
                        PRIMARY KEY (`pluginwordprocessorId`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
                ");
            }
            $resultset = $this->db->select('papers', ['papersId', 'papersHashFilename', 'papersUserId', 'papersFilename', 'papersTimestamp']);
            while ($row = $this->db->fetchRow($resultset))
            {
                $fields = $values = [];
                $fields[] = 'pluginwordprocessorId';
                $values[] = $row['papersId'];
                $fields[] = 'pluginwordprocessorHashFilename';
                $values[] = $row['papersHashFilename'];
                $fields[] = 'pluginwordprocessorUserId';
                $values[] = $row['papersUserId'];
                $fields[] = 'pluginwordprocessorFilename';
                $values[] = $row['papersFilename'];
                $fields[] = 'pluginwordprocessorTimestamp';
                $values[] = $row['papersTimestamp'];
                $this->db->insert('plugin_wordprocessor', $fields, $values);
            }
            $this->db->queryNoError("DROP TABLE IF EXISTS " . WIKINDX_DB_TABLEPREFIX . "papers;");
        }
        elseif (array_search('plugin_wordprocessor', $tables) !== FALSE)
        {
            $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor ENGINE=InnoDB;");
            $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;");
            $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor MODIFY COLUMN `pluginwordprocessorHashFilename` varchar(1020) DEFAULT NULL;");
            $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor MODIFY COLUMN `pluginwordprocessorFilename` varchar(1020) DEFAULT NULL;");
        }
        if (array_search('plugin_soundexplorer', $tables) !== FALSE)
        {
            $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer RENAME `" . WIKINDX_DB_TABLEPREFIX . "4fc387ba1ae34ac28e6dee712679d7b5`");
            $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "4fc387ba1ae34ac28e6dee712679d7b5 RENAME `" . WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer`");
            $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer ENGINE=InnoDB;");
            $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;");
            $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer MODIFY COLUMN `pluginsoundexplorerLabel` varchar(1020) DEFAULT NOT NULL;");
            $this->db->queryNoError("ALTER TABLE " . WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer MODIFY COLUMN `pluginsoundexplorerArray` text DEFAULT NOT NULL;");
        }
    }
}
