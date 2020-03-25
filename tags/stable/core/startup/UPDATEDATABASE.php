<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
    /** boolean */
    public $upgradeCompleted = FALSE;
    /** object */
    private $session;
    /** object */
    private $config;
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
    private $stageInterruptMessage = FALSE;
    /** string */
    private $statusString;
    /** integer */
    private $numStages;
    /**
     * UPDATEDATABASE
     */
    public function __construct()
    {
        // Turn on error reporting:
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');

        $this->db = FACTORY_DB::getInstance();
        $this->config = FACTORY_CONFIG::getInstance();
        $this->session = FACTORY_SESSION::getInstance();

        $this->messages = FACTORY_MESSAGES::getInstance();
        include_once('core/startup/INSTALLMESSAGES.php');
        $this->installMessages = new INSTALLMESSAGES;
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->oldTime = time();

        // Before upgrade process, clear all template cache
        $this->config->WIKINDX_BYPASS_SMARTYCOMPILE = TRUE;
        // Use the default template
        $this->session->setVar("setup_Template", WIKINDX_TEMPLATE_DEFAULT);
        // need to do this so the correct cache folder is set for the smarty cacheDir
        FACTORY_TEMPLATE::getInstance()->loadTemplate(TRUE);
        FACTORY_TEMPLATE::getInstance()->clearAllCache();

        $this->checkDatabase();

        // Restore defaut error reporting level
        $this->session->clearSessionData();

        if (GLOBALS::tplVarExists('content'))
        {
            FACTORY_CLOSE::getInstance(); // die;
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
            $this->configDefaults();
            $this->session->setVar("setup_Superadmin", TRUE); // required for gatekeep function in CONFIG.php
        }
        if (array_key_exists('action', $this->vars) && $this->session->getVar('setup_Superadmin') &&
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
     * Update the database if required
     *
     * @param bool $confirm
     * @param mixed $error
     *
     * @return bool
     */
    private function updateDatabase($confirm, $error = FALSE)
    {
        if ($error)
        {
            echo HTML\p("<font color=\"red\">$error</font>");
        }
        $dbVersion = \UPDATE\getDatabaseVersion($this->db);

        // WIKINDX 4 cleared out a lot of database upgrade code....
        if ($dbVersion < 5.1)
        {
            GLOBALS::addTplVar("content", "
                Your WIKINDX database version is $dbVersion. WIKINDX requires that you first upgrade
                to WIKINDX v5.1 or later before attempting to upgrade to the latest version.
                v6.0.8 is recommended as a transition version if you need yet PHP 5.6 support.
                v6.1.0 is recommended as a transition version if you don't need PHP 5.6 support (PHP 7.0 minimum).
            ");
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        
        if ($dbVersion < WIKINDX_INTERNAL_VERSION)
        {
            // v3.8 and beyond
            // As WIKINDX v5.3 (DB version 5.4) transfers config.php variables to the database, config.php must be writeable before we can proceed
            if ($dbVersion < 5.4)
            {
                $this->checkConfigFile(); // dies if not writeable or file does not exist.
            }
            if (!$confirm)
            {
                $this->confirmUpdateDisplay($dbVersion);

                return FALSE;
            }

            // Disable temporarily all SQL mode to update old databases
            $this->db->setSqlMode('');

            if ($dbVersion < 4.0)
            {      // upgrade v3.8.2 to v4.1
                $this->numStages = 16;
                $this->upgrade41();
            }
            elseif ($dbVersion < 4.2)
            { // upgrade v4.1 to v4.2
                $this->numStages = 1;
                $this->stage4_2__1();
            }
            elseif ($dbVersion < 5.1)
            { // upgrade v4.2 to v5.1
                $this->numStages = 1;
                $this->stage5_1__1();
            }
            elseif ($dbVersion < 5.2)
            { // upgrade v5.1 to 5.2.2
                $this->numStages = 1;
                $this->stage5_2__1();
            }
            elseif ($dbVersion < 5.4)
            { // upgrade v5.2.2 to 5.4
                $this->numStages = 1;
                $this->stage5_4__1();
            }
            elseif ($dbVersion < 5.5)
            { // upgrade v5.4 to 5.5
                $this->numStages = 1;
                $this->stage5_5__1();
            }
            elseif ($dbVersion < 5.6)
            { // upgrade v5.5 to 5.6
                $this->numStages = 1;
                $this->stage5_6__1();
            }
            elseif ($dbVersion < 5.7)
            { // upgrade v5.6 to 5.7
                $this->numStages = 1;
                $this->stage5_7__1();
            }
            elseif ($dbVersion < 5.8)
            { // upgrade v5.7 to 5.8
                $this->numStages = 1;
                $this->stage5_8__1();
            }
            elseif ($dbVersion < 5.9)
            { // upgrade v5.8 to 5.9
                $this->numStages = 1;
                $this->stage5_9__1();
            }
            elseif ($dbVersion < 6.0)
            { // upgrade v5.9 to 6
                $this->numStages = 1;
                $this->stage6();
            }
            elseif ($dbVersion < 7.0)
            { // upgrade v6 to 6.0.4
                $this->numStages = 1;
                $this->stage7();
            }
            elseif ($dbVersion < 8.0)
            { // upgrade v6.0.4 to 6.0.5
                $this->numStages = 1;
                $this->stage8();
            }
            elseif ($dbVersion < 9.0)
            { // upgrade v6.0.5 to 6.0.6
                $this->numStages = 1;
                $this->stage9();
            }
            elseif ($dbVersion < 10.0)
            { // upgrade v6.0.6 to 6.2.0
                $this->numStages = 1;
                $this->stage10();
            }
            elseif ($dbVersion < 11.0)
            { // upgrade v6.2.0 to 6.2.1
                $this->numStages = 1;
                $this->stage11();
            }
            $attachment = FACTORY_ATTACHMENT::getInstance();
            $attachment->checkAttachmentRows();
            // Refresh the locales list
            \LOCALES\refreshSystemLocalesCache(TRUE);
            $this->updateSoftwareVersion(WIKINDX_INTERNAL_VERSION);
            $this->session->delVar('upgrade_function');
            $this->upgradeCompleted = TRUE;
        }

        return TRUE;
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
            $this->session->getVar('wikindxBasePath')
            . $pluginPath . WIKINDX_DIR_DB_SCHEMA
            . DIRECTORY_SEPARATOR . 'full';
        foreach (FILE\fileInDirToArray($dbSchemaPath) as $sqlfile)
        {
            $sql = file_get_contents($dbSchemaPath . DIRECTORY_SEPARATOR . $sqlfile);
            $sql = str_replace('%%WIKINDX_DB_TABLEPREFIX%%', $this->config->WIKINDX_DB_TABLEPREFIX, $sql);
            $this->db->queryNoError($sql);
        }
    }
    /**
     * Update the database structure with the definitions of the dbschema store for a specific version
     *
     * @param string $wkxVersion Version number of Wikindx
     * @param string $pluginPath is the path to the root directory of a plugin. Default is the constant DIRECTORY_SEPARATOR for the core
     */
    public function updateDbSchema($wkxVersion, $pluginPath = DIRECTORY_SEPARATOR)
    {
        // The db schema is stored in a serie of SQL file in the directory /dbschema/update/<$wkxVersion> for the core
        // or /plugins/<PluginDirectory>/dbschema/update/<$wkxVersion>
        $dbSchemaPath =
            $this->session->getVar('wikindxBasePath')
            . $pluginPath . WIKINDX_DIR_DB_SCHEMA
            . DIRECTORY_SEPARATOR . 'update'
            . DIRECTORY_SEPARATOR . $wkxVersion;
        if (is_dir($dbSchemaPath))
        {
            foreach (FILE\fileInDirToArray($dbSchemaPath) as $sqlfile)
            {
                $sql = file_get_contents($dbSchemaPath . DIRECTORY_SEPARATOR . $sqlfile);
                $sql = str_replace('%%WIKINDX_DB_TABLEPREFIX%%', $this->config->WIKINDX_DB_TABLEPREFIX, $sql);
                $this->db->queryNoError($sql);
            }
        }
    }
    /**
     * Fill new config table (>= WIKINDX v5.3) with some default configuration values
     *
     * NB: The config table is initialized with default values by the LOADCONFIG class that know the name and type of each option
     */
    public function configDefaults()
    {
        $this->db->insert('category', 'categoryCategory', 'General');
        
        $fields = [
            'databasesummaryTotalResources',
            'databasesummaryTotalQuotes',
            'databasesummaryTotalParaphrases',
            'databasesummaryTotalMusings',
        ];
        $values = ['0', '0', '0', '0'];
        $this->db->insert('database_summary', $fields, $values);
        
        $this->updateSoftwareVersion(WIKINDX_INTERNAL_VERSION);
    }
    /**
     * Intercept for initial configuration by admin and, if necessary, display admin configuration interface (new installation means users table is empty).
     */
    private function checkUsersTable()
    {
        if ($this->db->tableIsEmpty('users'))
        {
            include_once("core/modules/admin/CONFIGURE.php");
            $config = new CONFIGURE(TRUE);
            // force super initialization in CONFIGURE
            $config->insert = TRUE;
            if (isset($this->vars['action']) && $this->vars['action'] == 'admin_CONFIGURE_CORE')
            {
                GLOBALS::addTplVar('content', $config->writeDb());
            }
            else
            {
                // write preliminary stringLimit, write and superadmin to session and display super configuration screen
                $this->session->setVar("setup_StringLimit", 40);
                $this->session->setVar("setup_Write", TRUE);
                $this->session->setVar("setup_Superadmin", TRUE);
                // superadmin userId is always 1
                $this->session->setVar("setup_UserId", 1);
                GLOBALS::addTplVar('content', $config->init([\HTML\p($this->installMessages->text("install"), "error", "center"), 'super']));
                FACTORY_CLOSENOMENU::getInstance();
            }
        }
    }
    /**
     * Check and print status of update scripts
     *
     * @param string $stage
     */
    private function checkStatus($stage)
    {
        $pString = "MAX EXECUTION TIME: " . ini_get("max_execution_time") . ' secs' . BR;
        $pString .= "ELAPSED TIME ($stage): ";
        $pString .= time() - $this->oldTime . " secs.";
        $pString .= BR;
        $pString .= "Database queries: " . GLOBALS::getDbQueries() . BR;
        $pString .= 'MEMORY LIMIT: ' . ini_get("memory_limit") . BR;
        $pString .= 'MEMORY USED: ' . memory_get_peak_usage() / 1000000 . 'MB';
        $this->statusString = $pString;
    }
    /**
     * Write the internal version in the database
     *
     * @param string $version
     */
    private function updateSoftwareVersion($version)
    {
        $version = (string)$version;
        $version = str_replace(",", ".", $version);
        if ($version <= 3.8) $field = "dbVersion";
        if ($version <= 5.9) $field = "databasesummaryDbVersion";
        if ($version >= 6.0) $field = "databasesummarySoftwareVersion";
        $this->db->update('database_summary', [$field => $version]);
    }
    /**
     * Check permissions on config.php
     */
    private function checkConfigFile()
    {
        $message = HTML\p("Part of the upgrade process for a WIKINDX that is younger than v5.3 is the transfer of many settings in config.php to the database (from where they can be configured via the Admin|Configure menu). In order to accomplish this, config.php must be writeable by the web server user and the upgrade will not proceed until this is the case. Equally, some settings are removed from config.php where the WIKINDX is 5.3 and older but younger than 5.9 Once the upgrade has completed, you can then return the file permissions on config.php to read only.");
        if (!file_exists('config.php'))
        {
            die("Fatal error: config.php does not exist.");
        }
        elseif (!is_writable('config.php'))
        {
            $permissions = mb_substr(sprintf('%o', fileperms('config.php')), -4);
            $message .= HTML\p("The permissions on config.php are currently: " . $permissions . ". The upgrade requires the file to be writeable.");
            die($message);
        }
    }
    /**
     * Upgrade database to v4.1.
     * Start with stage1();
     */
    private function upgrade41()
    {
        if (array_key_exists('action', $this->vars) && ($this->vars['action'] == 'continueExecution'))
        {
            $function = $this->session->getVar('upgrade_function');
            $this->{$function}();

            return;
        }
        $this->stage4_1__1();
    }
    /**
     * Stage 1
     */
    private function stage4_1__1()
    {
        $this->session->delVar('stage3UpgradeContinueView');
        $this->session->delVar('stage3UpgradeContinueAttach');

        $this->updateDbSchema('4.1.1');

        $value = base64_encode(serialize([
            'an', 'a', 'the', 'der', 'die', 'das',
            'ein', 'eine', 'einer', 'eines', 'le', 'la', 'las',
            'il', 'les', 'une', 'un', 'una', 'uno', 'lo', 'los',
            'i', 'gli', 'de', 'het', 'um', 'uma', 'o', 'os', 'as',
        ]));
        $this->db->update('config', ['noSort' => $value]);

        $value = base64_encode(serialize(['an', 'a', 'the', 'and', 'to']));
        $this->db->update('config', ['searchFilter' => $value]);

        $this->upgrade40Nulls();

        $this->checkStatus('stage4_1__1');
        $this->pauseExecution('stage4_1__1', 'stage4_1__2');
    }
    /**
     * Stage 2
     */
    private function stage4_1__2()
    {
        $this->updateDbSchema('4.1.2');

        // v3.3 corrected additional backslashes that had crept in but forgot to deal with the resource_note table
        $resultset = $this->db->select('resource_note', ['id', 'text']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $update = [];
            $update['text'] = stripslashes(stripslashes($row['text']));
            $this->db->formatConditions(['id' => $row['id']]);
            $this->db->update('resource_note', $update);
        }
        unset($update);


        // Transfer resource_note and resource_abstract fields to it
        $ids = [];
        // Transfer URLs (now stores multiple URLs so base_64 encode and serialize before storing)
        $recordSet = $this->db->select('resource', ['id', 'url']);
        while ($row = $this->db->fetchRow($recordSet))
        {
            if ($row['url'])
            {
                $fields = ['id', 'urls'];
                $values = [$row['id'], base64_encode(serialize([$row['url']]))];
                $this->db->insert('resource_text', $fields, $values);
                $ids[$row['id']] = FALSE;
            }
        }
        // Transfer notes
        $recordSet = $this->db->select('resource_note', ['id', 'text', 'editUserIdNote', 'addUserIdNote']);
        while ($row = $this->db->fetchRow($recordSet))
        {
            if ($row['text'] && array_key_exists($row['id'], $ids))
            { // update row
                $updateArray = ['note' => UTF8::smartUtf8_decode(\HTML\nlToHtml($row['text'], TRUE))];
                if ($row['editUserIdNote'])
                {
                    $updateArray['editUserIdNote'] = $row['editUserIdNote'];
                }
                if ($row['addUserIdNote'])
                {
                    $updateArray['addUserIdNote'] = $row['addUserIdNote'];
                }
                $this->db->formatConditions(['id' => $row['id']]);
                $this->db->update('resource_text', $updateArray);
            }
            elseif ($row['text'])
            { // insert
                $fields = ['id', 'note'];
                $values = [$row['id'], UTF8::smartUtf8_decode(\HTML\nlToHtml($row['text'], TRUE))];
                if ($row['editUserIdNote'])
                {
                    $fields[] = 'editUserIdNote';
                    $values[] = $row['editUserIdNote'];
                }
                if ($row['addUserIdNote'])
                {
                    $fields[] = 'addUserIdNote';
                    $values[] = $row['addUserIdNote'];
                }
                $this->db->insert('resource_text', $fields, $values);
                $ids[$row['id']] = FALSE;
            }
        }
        // Transfer abstracts
        $recordSet = $this->db->select('resource_abstract', ['id', 'abstract', 'editUserIdAbstract', 'addUserIdAbstract']);
        while ($row = $this->db->fetchRow($recordSet))
        {
            if ($row['abstract'] && array_key_exists($row['id'], $ids))
            { // update row
                $updateArray = ['abstract' => UTF8::smartUtf8_decode(\HTML\nlToHtml($row['abstract'], TRUE))];
                if ($row['editUserIdAbstract'])
                {
                    $updateArray['editUserIdAbstract'] = $row['editUserIdAbstract'];
                }
                if ($row['addUserIdAbstract'])
                {
                    $updateArray['addUserIdAbstract'] = $row['addUserIdAbstract'];
                }
                $this->db->formatConditions(['id' => $row['id']]);
                $this->db->update('resource_text', $updateArray);
            }
            elseif ($row['abstract'])
            { // insert
                $fields = ['id', 'abstract'];
                $values = [$row['id'], UTF8::smartUtf8_decode(\HTML\nlToHtml($row['abstract'], TRUE))];
                if ($row['editUserIdAbstract'])
                {
                    $fields[] = 'editUserIdAbstract';
                    $values[] = $row['editUserIdAbstract'];
                }
                if ($row['addUserIdAbstract'])
                {
                    $fields[] = 'addUserIdAbstract';
                    $values[] = $row['addUserIdAbstract'];
                }
                $this->db->insert('resource_text', $fields, $values);
            }
        }

        $this->checkStatus('stage4_1__2');
        $this->pauseExecution('stage4_1__2', 'stage4_1__3');
    }
    /**
     * Stage 3
     */
    private function stage4_1__3()
    {
        $this->updateDbSchema('4.1.3');

        // Transfer attachments fields to resource_attachments.
//
        // Delete attachment rows if attachments don't exist.

        // Add resource view and attachment download data to statistics table
        // First insert statistics rows by averaging out total resource views over months resource has been available.
        // If we've paused execution, ensure we only select those that have not yet been done.
        $this->db->formatConditions(['attachmentId' => ' IS NULL']);
        $subStmt = $this->db->subQuery($this->db->selectNoExecute('statistics', 'resourceId'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('resource_misc.id') . $this->db->inClause($subStmt, TRUE));
        $recordset = $this->db->query('SELECT `' .
            $this->config->WIKINDX_DB_TABLEPREFIX . "resource_misc`.`id` AS `id`,
			`accesses`,
			`timestampAdd`,
			date_format(`timestampAdd`, '%Y%m') AS `date`,
			period_diff(date_format(CURRENT_TIMESTAMP, '%Y%m'), date_format(`timestampAdd`, '%Y%m')) AS `months` FROM `" .
            $this->config->WIKINDX_DB_TABLEPREFIX . 'resource_misc` LEFT JOIN `' .
            $this->config->WIKINDX_DB_TABLEPREFIX . 'resource_timestamp` ON `' .
                $this->config->WIKINDX_DB_TABLEPREFIX . 'resource_timestamp`.`id` = `' .
                $this->config->WIKINDX_DB_TABLEPREFIX . 'resource_misc`.`id`');
        $count = 0;
        while ($row = $this->db->fetchRow($recordset))
        {
            $array = [];
            $year = mb_substr($row['date'], 0, 4);
            $month = mb_substr($row['date'], 4, 2);
            if (!$row['months'])
            {
                $array[$row['date']] = $row['accesses'];
            }
            else
            {
                $average = ceil($row['accesses'] / $row['months']);
                for ($i = 0; $i < $row['months']; $i++)
                {
                    $addYears = floor($i / 12);
                    $addMonths = $i % 12;
                    $monthPart = $month + $addMonths;
                    if ($monthPart > 12)
                    {
                        ++$addYears;
                        $monthPart = $monthPart - 12;
                    }
                    $monthPart = $monthPart < 10 ? '0' . $monthPart : (string)$monthPart;
                    $yearPart = $year + $addYears;
                    $date = (string)$yearPart . $monthPart;
                    $array[$date] = $average;
                }
            }
            $this->db->insert(
                'statistics',
                ['resourceId', 'statistics'],
                [$row['id'], base64_encode(serialize($array))]
            );
            $this->db->formatConditions(['id' => $row['id']]);
            $this->db->update('resource_misc', ['accessesPeriod' => $average]);
            $count++;
            // Check we have more than 3 seconds buffer before max_execution_time times out.
            if ((time() - $this->oldTime) >= (ini_get("max_execution_time") - 3))
            {
                $this->session->setVar('stage3UpgradeContinueView', TRUE);
                $this->checkStatus('stage4_1__3');
                $this->stageInterruptMessage = "stage3 continuing: $count view statistics created this pass.&nbsp;&nbsp;";
                $this->pauseExecution('stage4_1__3', 'stage4_1__3');
            }
        }
        if (!$this->session->getVar('stage3UpgradeContinueAttach') && array_key_exists('WIKINDX_ATTACHMENTS_DIR', $this->config))
        { // First time through and no continue execution
            $dirName = $this->config->WIKINDX_ATTACHMENTS_DIR;
            $recordSet = $this->db->select('resource_attachments', ['id', 'hashFilename']);
            $deletes = [];
            while ($row = $this->db->fetchRow($recordSet))
            {
                if (!file_exists("$dirName/" . $row['hashFilename']))
                {
                    $deletes[] = $row['id'];
                }
            }
            if (!empty($deletes))
            { // discard these entries as there is no attachment
                $this->db->formatConditionsOneField($deletes, 'id');
                $this->db->delete('resource_attachments');
            }
            $this->db->leftJoin('resource_timestamp', 'resource_timestamp.id', 'resource_misc.id');
            $recordset = $this->db->select('resource_misc', [['resource_misc.id' => 'id'], 'attachDownloads', 'timestampAdd',]);
            while ($row = $this->db->fetchRow($recordset))
            {
                $this->db->formatConditions(['resourceId' => $row['id']]);
                $this->db->update('resource_attachments', ['downloads' => $row['attachDownloads'], 'timestamp' => $row['timestampAdd']]);
            }
        }
        // Insert statistics rows by averaging out total attachment downloads over months attachment has been available
        // If we've paused execution, ensure we only select those that have not yet been done.
        $this->db->formatConditions(['attachmentId' => ' IS NOT NULL']);
        $subStmt = $this->db->subQuery($this->db->selectNoExecute('statistics', 'resourceId'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('resourceId') . $this->db->inClause($subStmt, TRUE));
        $recordset = $this->db->query("SELECT `id`,
			`resourceId`,
			`downloads`,
			`timestamp`,
			date_format(`timestamp`, '%Y%m') AS `date`,
			period_diff(date_format(CURRENT_TIMESTAMP, '%Y%m'), date_format(`timestamp`, '%Y%m')) AS `months` FROM `" .
            $this->config->WIKINDX_DB_TABLEPREFIX . 'resource_attachments`');
        $count = 0;
        while ($row = $this->db->fetchRow($recordset))
        {
            $array = [];
            $year = mb_substr($row['date'], 0, 4);
            $month = mb_substr($row['date'], 4, 2);
            if (!$row['months'])
            {
                $array[$row['date']] = $row['downloads'];
            }
            else
            {
                $average = ceil($row['downloads'] / $row['months']);
                for ($i = 0; $i < $row['months']; $i++)
                {
                    $addYears = floor($i / 12);
                    $addMonths = $i % 12;
                    $monthPart = $month + $addMonths;
                    if ($monthPart > 12)
                    {
                        ++$addYears;
                        $monthPart = $monthPart - 12;
                    }
                    $monthPart = $monthPart < 10 ? '0' . $monthPart : (string)$monthPart;
                    $yearPart = $year + $addYears;
                    $date = (string)$yearPart . $monthPart;
                    $array[$date] = $average;
                }
            }
            $this->db->insert(
                'statistics',
                ['resourceId', 'attachmentId', 'statistics'],
                [$row['resourceId'], $row['id'], base64_encode(serialize($array))]
            );
            $this->db->formatConditions(['id' => $row['id']]);
            $this->db->update('resource_attachments', ['downloadsPeriod' => $average]);
            $count++;
            // Check we have more than 3 seconds buffer before max_execution_time times out.
            if ((time() - $this->oldTime) >= (ini_get("max_execution_time") - 3))
            {
                $this->session->setVar('stage3UpgradeContinueAttach', TRUE);
                $this->checkStatus('stage4_1__3');
                $this->stageInterruptMessage = "stage3 continuing: $count attachment statistics created this pass.&nbsp;&nbsp;";
                $this->pauseExecution('stage4_1__3', 'stage4_1__3');
            }
        }

        $this->checkStatus('stage4_1__3');
        $this->pauseExecution('stage4_1__3', 'stage4_1__4');
    }
    /**
     * stage 4
     */
    private function stage4_1__4()
    {
        // Set for Latin1
        $this->db->query('SET NAMES latin1');
        $this->db->query('SET CHARACTER SET latin1');

        // Pre v4.0 versions have a bug whereby creators may be duplicated in the database -- find these and fix.
        $resultset = $this->db->query("
		    SELECT `a`.`id` AS `aId`, `b`.`id` AS `bId`
			FROM `" . $this->config->WIKINDX_DB_TABLEPREFIX . "creator` AS a
			INNER JOIN `" . $this->config->WIKINDX_DB_TABLEPREFIX . "creator` AS b
			ON  (`a`.`firstname` = `b`.`firstname` OR (`a`.`firstname` IS NULL AND `b`.`firstname` IS NULL))
			AND (`a`.`initials`  = `b`.`initials`  OR (`a`.`initials` IS NULL  AND `b`.`initials` IS NULL))
			AND (`a`.`prefix`    = `b`.`prefix`    OR (`a`.`prefix` IS NULL    AND `b`.`prefix` IS NULL))
			AND `a`.`surname`    = `b`.`surname`
			AND `a`.`id` != `b`.`id`
			");
        $dupCreators = $delCreators = [];
        $count = 0;
        while ($row = $this->db->fetchRow($resultset))
        {
            if (array_search($row['aId'], $delCreators) !== FALSE)
            {
                continue;
            }
            if (array_search($row['aId'], $dupCreators) === FALSE)
            {
                $dupCreators[$row['aId']] = $row['bId'];
                $resultset2 = $this->db->query(
                    "
				    SELECT `id`, `creator1`, `creator2`, `creator3`, `creator4`, `creator5`
					FROM `" . $this->config->WIKINDX_DB_TABLEPREFIX . "resource_creator`
					WHERE
					   FIND_IN_SET('" . $row['bId'] . "', `creator1`)
					OR FIND_IN_SET('" . $row['bId'] . "', `creator2`)
					OR FIND_IN_SET('" . $row['bId'] . "', `creator3`)
					OR FIND_IN_SET('" . $row['bId'] . "', `creator4`)
					OR FIND_IN_SET('" . $row['bId'] . "', `creator5`)"
                );
                while ($row2 = $this->db->fetchRow($resultset2))
                {
                    $updateArray = [];
                    foreach (['creator1', 'creator2', 'creator3', 'creator4', 'creator5'] as $creator)
                    {
                        // remove bId creator id and replace with aId if aId not already in set
                        if ($row2[$creator])
                        {
                            $oldArray = UTF8::mb_explode(",", $row2[$creator]);
                            // If $row['aId'] already in db field, simply remove $row['bId']
                            if ((array_search($row['aId'], $oldArray) !== FALSE) &&
                                (($editIndex = array_search($row['bId'], $oldArray)) !== FALSE))
                            {
                                unset($oldArray[$editIndex]);
                                $updateArray[$creator] = implode(",", $oldArray);
                            }
                            // else, if this creator field has $row['bId'] in it, replace it with $row['aId']
                            elseif (($editIndex = array_search($row['bId'], $oldArray)) !== FALSE)
                            {
                                $oldArray[$editIndex] = $row['aId'];
                                $updateArray[$creator] = implode(",", $oldArray);
                            }
                        }
                    }
                    if (!empty($updateArray))
                    {
                        $this->db->formatConditions(['id' => $row2['id']]);
                        $this->db->update('resource_creator', $updateArray);
                    }
                }
                if (array_search($row['bId'], $delCreators) === FALSE)
                {
                    ++$count;
                    $delCreators[] = $row['bId'];
                    // delete bId
                    $this->db->formatConditions(['id' => $row['bId']]);
                    $this->db->delete('creator');
                }
            }
            // Check we have more than 3 seconds buffer before max_execution_time times out.
            if ((time() - $this->oldTime) >= (ini_get("max_execution_time") - 3))
            {
                $this->checkStatus('stage4_1__4');
                $this->stageInterruptMessage = "stage4 continuing: $count duplicate creators corrected this pass.&nbsp;&nbsp;";
                $this->pauseExecution('stage4_1__4', 'stage4_1__4');
            }
        }

        $this->checkStatus('stage4_1__4');
        $this->pauseExecution('stage4_1__4', 'stage4_1__5');
    }
    /**
     * stage 5
     */
    private function stage4_1__5()
    {
        // Set for Latin1
        $this->db->query('SET NAMES latin1');
        $this->db->query('SET CHARACTER SET latin1');

        // Remove duplicate collections
        $resultset = $this->db->query("
		    SELECT `a`.`id` AS `aId`, `b`.`id` AS `bId`
			FROM `" . $this->config->WIKINDX_DB_TABLEPREFIX . "collection` AS a
			INNER JOIN `" . $this->config->WIKINDX_DB_TABLEPREFIX . "collection` AS b
			ON  (`a`.`collectionTitle`      = `b`.`collectionTitle`)
			AND (`a`.`collectionTitleShort` = `b`.`collectionTitleShort` OR (`a`.`collectionTitleShort` IS NULL AND `b`.`collectionTitleShort` IS NULL))
			AND (`a`.`collectionType`       = `b`.`collectionType`)
			AND `a`.`id` != `b`.`id`
			");
        $dupCollections = $delCollections = [];
        while ($row = $this->db->fetchRow($resultset))
        {
            if (array_search($row['aId'], $delCollections) === FALSE)
            {
                $dupCollections[$row['aId']][] = $row['bId'];
                $delCollections[] = $row['bId'];
            }
        }
        $updateArray = [];
        foreach ($dupCollections as $aId => $bIds)
        {
            foreach ($bIds as $bId)
            {
                $resultset2 = $this->db->query("
				    SELECT `id`, `collection`
					FROM `" . $this->config->WIKINDX_DB_TABLEPREFIX . "resource_misc`
					WHERE `collection` = '" . $bId . "'");
                while ($row2 = $this->db->fetchRow($resultset2))
                {
                    // remove bId collection id and replace with aId
                    if ($row2['collection'] && ($row2['collection'] == $bId))
                    { // duplicate check?
                        $updateArray[$bId] = $aId;
                    }
                }
            }
            // Check we have more than 5 seconds buffer before max_execution_time times out.
            if ((time() - $this->oldTime) >= (ini_get("max_execution_time") - 5))
            {
                // Do updates so far
                $this->db->multiUpdate('resource_misc', 'collection', 'collection', $updateArray);
                // delete bIds thus far
                $this->db->formatConditionsOneField($delCollections, 'id');
                $this->db->delete('collection');
                $count = count($delCollections);
                $this->checkStatus('stage4_1__5');
                $this->stageInterruptMessage = "stage5 continuing: $count duplicate collections corrected this pass.&nbsp;&nbsp;";
                $this->pauseExecution('stage4_1__5', 'stage4_1__5');
            }
        }
        $this->db->multiUpdate('resource_misc', 'collection', 'collection', $updateArray);
        $this->db->formatConditionsOneField($delCollections, 'id');
        $this->db->delete('collection');

        $this->checkStatus('stage4_1__5');
        $this->pauseExecution('stage4_1__5', 'stage4_1__6');
    }
    /**
     * stage 6
     */
    private function stage4_1__6()
    {
        // Rename fields in tables for v4.0 upgrade
        $this->updateDbSchema('4.1.6');

        $this->upgrade40charToBin();
        $this->checkStatus('stage4_1__6');
        $this->pauseExecution('stage4_1__6', 'stage4_1__7');
    }
    /**
     * stage 7
     */
    private function stage4_1__7()
    {
        $this->updateDbSchema('4.1.7');

        $this->upgrade40Tables();

        $this->checkStatus('stage4_1__7');
        $this->pauseExecution('stage4_1__7', 'stage4_1__8');
    }
    /**
     * stage 8
     */
    private function stage4_1__8()
    {
        /**
         * Rewrite resource_creator tables
         *
         * In v4, we no longer use MySQL's FIND_IN_SET and therefore no longer want comma-delimited field values such as creator1, creator2 etc. in
         * the resource_creator table.  Write a new resource_creator table that has a row comprising:
         * resourceId references resource table), creatorId (references creator table), role (creator1, creator2 etc.) and order (1,2, 3 etc.
         * for first author, second author, third author etc.)
         * resourcecreatorCreatorMain is the creatorId that is the main creator for that resource used when ordering lists.
         */
        $this->updateDbSchema('4.1.8');

        $creatorFields = [
            'resourcecreatorCreator1',
            'resourcecreatorCreator2',
            'resourcecreatorCreator3',
            'resourcecreatorCreator4',
            'resourcecreatorCreator5',
        ];

        $this->db->formatConditions(['creatorSurname' => ' IS NOT NULL']);
        $recordSetCreator = $this->db->select('creator', ['creatorId', 'creatorSurname']);

        $recordSet = $this->db->select(
            'resource_creator',
            ['resourcecreatorId',
                'resourcecreatorCreator1',
                'resourcecreatorCreator2',
                'resourcecreatorCreator3',
                'resourcecreatorCreator4',
                'resourcecreatorCreator5',
            ]
        );
        while ($row = $this->db->fetchRow($recordSet))
        {
            $creatorMain = FALSE;
            foreach ($creatorFields as $creatorField)
            {
                if (!$row[$creatorField])
                {
                    continue;
                }

                $order = 1;

                $creatorIds = UTF8::mb_explode(',', $row[$creatorField]);

                foreach ($creatorIds as $creatorId)
                {
                    $fields = [];
                    $values = [];

                    $fields[] = 'resourcecreatorResourceId';
                    $values[] = $row['resourcecreatorId'];

                    $fields[] = 'resourcecreatorCreatorId';
                    $values[] = $creatorId;

                    $fields[] = 'resourcecreatorOrder';
                    $values[] = $order;

                    if ($creatorField != 'resourcecreatorId')
                    {
                        $fields[] = 'resourcecreatorRole';
                        $values[] = mb_substr($creatorField, -1);

                        if (!$creatorMain)
                        {
                            $creatorMain = $creatorId;
                        }

                        $fields[] = 'resourcecreatorCreatorMain';
                        $values[] = $creatorMain;
                    }

                    $this->db->goToRow($recordSetCreator, 1);
                    while ($rowCreator = $this->db->fetchRow($recordSetCreator))
                    {
                        if ($rowCreator['creatorId'] == $creatorMain)
                        {
                            $fields[] = 'resourcecreatorCreatorSurname';
                            $values[] = $rowCreator['creatorSurname'];

                            break;
                        }
                    }

                    $this->db->insert('temp_resource_creator', $fields, $values);
                    ++$order;
                }
            }
        }
        // Select, and insert into temp_resource_creator, those resourceIds without a creator
        $this->db->formatConditions($this->db->formatFields('resourceId') . ' NOT IN ' .
            $this->db->subQuery($this->db->selectNoExecute('resource_creator', 'resourcecreatorId'), FALSE, FALSE));
        $recordSet = $this->db->select('resource', 'resourceId');

        while ($row = $this->db->fetchRow($recordSet))
        {
            $fields = $values = [];
            $fields[] = 'resourcecreatorResourceId';
            $values[] = $row['resourceId'];
            $this->db->insert('temp_resource_creator', $fields, $values);
        }

        $this->checkStatus('stage4_1__8');
        $this->pauseExecution('stage4_1__8', 'stage4_1__9');
    }
    /**
     * stage 9
     */
    private function stage4_1__9()
    {
        // Rewrite resource_summary
        // Fix quote, paraphrase, musing counts per resource.
        $ids = [];
        // quotes
        $this->db->groupBy('resourcequoteResourceId');
        $resultset = $this->db->query('SELECT `resourcequoteResourceId`, COUNT(`resourcequoteResourceId`) AS `count` FROM ' .
            $this->config->WIKINDX_DB_TABLEPREFIX . 'resource_quote');
        while ($row = $this->db->fetchRow($resultset))
        {
            $ids[$row['resourcequoteResourceId']]['resourcesummaryQuotes'] = $row['count'];
        }
        // paraphrases
        $this->db->groupBy('resourceparaphraseResourceId');
        $resultset = $this->db->query('SELECT `resourceparaphraseResourceId`, COUNT(`resourceparaphraseResourceId`) AS `count` FROM ' .
            $this->config->WIKINDX_DB_TABLEPREFIX . 'resource_paraphrase');
        while ($row = $this->db->fetchRow($resultset))
        {
            $ids[$row['resourceparaphraseResourceId']]['resourcesummaryParaphrases'] = $row['count'];
        }
        // musings
        $this->db->groupBy('resourcemusingResourceId');
        $resultset = $this->db->query('SELECT `resourcemusingResourceId`, COUNT(`resourcemusingResourceId`) AS `count` FROM ' .
            $this->config->WIKINDX_DB_TABLEPREFIX . 'resource_musing');
        while ($row = $this->db->fetchRow($resultset))
        {
            $ids[$row['resourcemusingResourceId']]['resourcesummaryMusings'] = $row['count'];
        }
        foreach ($ids as $id => $fieldArray)
        {
            $update = $nulls = [];
            foreach ($fieldArray as $field => $count)
            {
                $update[$field] = $count;
            }
            if (!array_key_exists('resourcesummaryQuotes', $fieldArray))
            {
                $nulls[] = 'resourcesummaryQuotes';
            }
            if (!array_key_exists('resourcesummaryParaphrases', $fieldArray))
            {
                $nulls[] = 'resourcesummaryParaphrases';
            }
            if (!array_key_exists('resourcesummaryMusings', $fieldArray))
            {
                $nulls[] = 'resourcesummaryMusings';
            }

            $this->db->formatConditions(['resourcesummaryId' => $id]);
            $this->db->update('resource_summary', $update);
            if (!empty($nulls))
            {
                $this->db->formatConditions(['resourcesummaryId' => $id]);
                $this->db->updateNull('resource_summary', $nulls);
            }
        }

        $this->checkStatus('stage4_1__9');
        $this->pauseExecution('stage4_1__9', 'stage4_1__10');
    }
    /**
     * stage 10
     */
    private function stage4_1__10()
    {
        $this->updateDbSchema('4.1.10');

        // Rewrite resource_keyword table
        $this->db->formatConditions($this->db->formatFields('resourcekeywordKeywords') . ' IS NOT NULL');
        $recordSet = $this->db->select('resource_keyword', ['resourcekeywordId', 'resourcekeywordKeywords']);
        while ($row = $this->db->fetchRow($recordSet))
        {
            foreach (UTF8::mb_explode(',', $row['resourcekeywordKeywords']) as $kId)
            {
                $this->db->insert(
                    'temp_resource_keyword',
                    ['resourcekeywordResourceId', 'resourcekeywordKeywordId'],
                    [$row['resourcekeywordId'], $kId]
                );
            }
        }
        $this->db->formatConditions($this->db->formatFields('resourcequoteKeywords') . ' IS NOT NULL');
        $recordSet = $this->db->select('resource_quote', ['resourcequoteId', 'resourcequoteKeywords']);
        while ($row = $this->db->fetchRow($recordSet))
        {
            foreach (UTF8::mb_explode(',', $row['resourcequoteKeywords']) as $kId)
            {
                $this->db->insert(
                    'temp_resource_keyword',
                    ['resourcekeywordQuoteId', 'resourcekeywordKeywordId'],
                    [$row['resourcequoteId'], $kId]
                );
            }
        }
        $this->db->formatConditions($this->db->formatFields('resourceparaphraseKeywords') . ' IS NOT NULL');
        $recordSet = $this->db->select('resource_paraphrase', ['resourceparaphraseId', 'resourceparaphraseKeywords']);
        while ($row = $this->db->fetchRow($recordSet))
        {
            foreach (UTF8::mb_explode(',', $row['resourceparaphraseKeywords']) as $kId)
            {
                $this->db->insert(
                    'temp_resource_keyword',
                    ['resourcekeywordParaphraseId', 'resourcekeywordKeywordId'],
                    [$row['resourceparaphraseId'], $kId]
                );
            }
        }
        $this->db->formatConditions($this->db->formatFields('resourcemusingKeywords') . ' IS NOT NULL');
        $recordSet = $this->db->select('resource_musing', ['resourcemusingId', 'resourcemusingKeywords']);
        while ($row = $this->db->fetchRow($recordSet))
        {
            foreach (UTF8::mb_explode(',', $row['resourcemusingKeywords']) as $kId)
            {
                $this->db->insert(
                    'temp_resource_keyword',
                    ['resourcekeywordMusingId', 'resourcekeywordKeywordId'],
                    [$row['resourcemusingId'], $kId]
                );
            }
        }

        $this->checkStatus('stage4_1__10');
        $this->pauseExecution('stage4_1__10', 'stage4_1__11');
    }
    /**
     * stage 11
     */
    private function stage4_1__11()
    {
        $this->updateDbSchema('4.1.11');

        // Rewrite resource_category table
        $recordSet = $this->db->select('resource_category', ['resourcecategoryId', 'resourcecategoryCategories']);
        while ($row = $this->db->fetchRow($recordSet))
        {
            foreach (UTF8::mb_explode(',', $row['resourcecategoryCategories']) as $cId)
            {
                $this->db->insert(
                    'temp_resource_category',
                    ['resourcecategoryResourceId', 'resourcecategoryCategoryId'],
                    [$row['resourcecategoryId'], $cId]
                );
            }
        }

        $this->checkStatus('stage4_1__11');
        $this->pauseExecution('stage4_1__11', 'stage4_1__12');
    }
    /**
     * stage 12
     */
    private function stage4_1__12()
    {
        $this->updateDbSchema('4.1.12');

        // Rewrite bibliographies groups
        $recordSet = $this->db->select('user_bibliography', '*');
        while ($row = $this->db->fetchRow($recordSet))
        {
            if ($row['userbibliographyBibliography'])
            {
                foreach (UTF8::mb_explode(',', $row['userbibliographyBibliography']) as $rId)
                {
                    $this->db->insert(
                        'user_bibliography_resource',
                        ['userbibliographyresourceBibliographyId', 'userbibliographyresourceResourceId'],
                        [$row['userbibliographyId'], $rId]
                    );
                }
            }
        }
        $recordSet = $this->db->select('user_groups', ['usergroupsId', 'usergroupsUserIds', 'usergroupsBibliographyIds']);
        while ($row = $this->db->fetchRow($recordSet))
        {
            foreach (UTF8::mb_explode(',', $row['usergroupsUserIds']) as $uId)
            {
                $this->db->insert(
                    'user_groups_users',
                    ['usergroupsusersGroupId', 'usergroupsusersUserId'],
                    [$row['usergroupsId'], $uId]
                );
            }
        }

        $this->checkStatus('stage4_1__12');
        $this->pauseExecution('stage4_1__12', 'stage4_1__13');
    }
    /**
     * stage 13
     *
     * convert bbcode to html for use with tinymce and correct transTitle errors
     */
    private function stage4_1__13()
    {
        include_once("core/display/BBCODE.php");
        $string = $this->db->selectFirstField('config', 'configDescription');
        $string = BBCODE::bbCodeToHtml($string);
        $this->db->update('config', ['configDescription' => $string]);
        // correct transTitle errors
        $resultset = $this->db->select('resource', ['resourceId', 'resourceTransTitle', 'resourceTransSubtitle']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $nulls = [];
            if (($row['resourceTransTitle'] == '') || ($row['resourceTransTitle'] == '(no title)'))
            {
                $nulls[] = 'resourceTransTitle';
            }
            if ($row['resourceTransSubtitle'] == '')
            {
                $nulls[] = 'resourceTransSubtitle';
            }
            if (!empty($nulls))
            {
                $this->db->formatConditions(['resourceId' => $row['resourceId']]);
                $this->db->updateNull('resource', $nulls);
            }
        }
        // titles and subtitles
        $this->db->formatConditions($this->db->formatFields('resourceTitle') . $this->db->like('%', '[', '%') .
            $this->db->or . $this->db->formatFields('resourceSubtitle') . $this->db->like('%', '[', '%'));
        $resultset = $this->db->select('resource', ['resourceId', 'resourceTitle', 'resourceSubtitle',
            'resourceTransTitle', 'resourceTransSubtitle', ]);
        while ($row = $this->db->fetchRow($resultset))
        {
            $updateArray = [];
            if ($row['resourceTitle'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourceTitle']);
                $updateArray['resourceTitle'] = $string;
            }
            if ($row['resourceSubtitle'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourceSubtitle']);
                $updateArray['resourceSubtitle'] = $string;
            }
            if ($row['resourceTransTitle'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourceTransTitle']);
                $updateArray['resourceTransTitle'] = $string;
            }
            if ($row['resourceTransSubtitle'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourceTransSubtitle']);
                $updateArray['resourceTransSubtitle'] = $string;
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['resourceId' => $row['resourceId']]);
                $this->db->update('resource', $updateArray);
            }
        }
        // Collection titles
        $this->db->formatConditions($this->db->formatFields('collectionTitle') . $this->db->like('%', '[', '%'));
        $resultset = $this->db->select('collection', ['collectionId', 'collectionTitle']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $updateArray = [];
            if ($row['collectionTitle'])
            {
                $string = BBCODE::bbCodeToHtml($row['collectionTitle']);
                $updateArray['collectionTitle'] = $string;
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['collectionId' => $row['collectionId']]);
                $this->db->update('collection', $updateArray);
            }
        }
        // notes and abstracts
        $this->db->formatConditions($this->db->formatFields('resourcetextNote') . $this->db->like('%', '[', '%') .
            $this->db->or . $this->db->formatFields('resourcetextAbstract') . $this->db->like('%', '[', '%'));
        $resultset = $this->db->select('resource_text', ['resourcetextId', 'resourcetextNote', 'resourcetextAbstract']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $updateArray = [];
            if ($row['resourcetextNote'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourcetextNote']);
                $updateArray['resourcetextNote'] = $string;
            }
            if ($row['resourcetextAbstract'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourcetextAbstract']);
                $updateArray['resourcetextAbstract'] = $string;
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['resourcetextId' => $row['resourcetextId']]);
                $this->db->update('resource_text', $updateArray);
            }
        }
        // Custom large fields -- don't filter results because all fields need newlines converting to HTML
        //		$this->db->formatConditions($this->db->formatFields('resourcecustomLong') . $this->db->like('%', '[', '%'));
        $resultset = $this->db->select('resource_custom', ['resourcecustomId', 'resourcecustomLong']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $updateArray = [];
            if ($row['resourcecustomLong'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourcecustomLong']);
                $updateArray['resourcecustomLong'] = \HTML\nlToHtml($string, TRUE);
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['resourcecustomId' => $row['resourcecustomId']]);
                $this->db->update('resource_custom', $updateArray);
            }
        }
        // User bibliography
        //		$this->db->formatConditions($this->db->formatFields('userbibliographyDescription') . $this->db->like('%', '[', '%'));
        $resultset = $this->db->select('user_bibliography', ['userbibliographyId', 'userbibliographyDescription']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $updateArray = [];
            if ($row['userbibliographyDescription'])
            {
                $string = BBCODE::bbCodeToHtml($row['userbibliographyDescription']);
                $updateArray['userbibliographyDescription'] = \HTML\nlToHtml($string, TRUE);
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['userbibliographyId' => $row['userbibliographyId']]);
                $this->db->update('user_bibliography', $updateArray);
            }
        }
        // News
        //		$this->db->formatConditions($this->db->formatFields('newsNews') . $this->db->like('%', '[', '%'));
        $resultset = $this->db->select('news', ['newsId', 'newsNews']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $updateArray = [];
            if ($row['newsNews'])
            {
                $string = BBCODE::bbCodeToHtml($row['newsNews']);
                $updateArray['newsNews'] = \HTML\nlToHtml($string, TRUE);
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['newsId' => $row['newsId']]);
                $this->db->update('news', $updateArray);
            }
        }
        // Musings
        //		$this->db->formatConditions($this->db->formatFields('resourcemusingtextText') . $this->db->like('%', '[', '%'));
        $resultset = $this->db->select('resource_musing_text', ['resourcemusingtextId', 'resourcemusingtextText']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $updateArray = [];
            if ($row['resourcemusingtextText'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourcemusingtextText']);
                $updateArray['resourcemusingtextText'] = \HTML\nlToHtml($string, TRUE);
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['resourcemusingtextId' => $row['resourcemusingtextId']]);
                $this->db->update('resource_musing_text', $updateArray);
            }
        }
        // Quotes
        //		$this->db->formatConditions($this->db->formatFields('resourcequotetextText') . $this->db->like('%', '[', '%'));
        $resultset = $this->db->select('resource_quote_text', ['resourcequotetextId', 'resourcequotetextText']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $updateArray = [];
            if ($row['resourcequotetextText'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourcequotetextText']);
                $updateArray['resourcequotetextText'] = \HTML\nlToHtml($string, TRUE);
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['resourcequotetextId' => $row['resourcequotetextId']]);
                $this->db->update('resource_quote_text', $updateArray);
            }
        }
        // Quote comments
        //		$this->db->formatConditions($this->db->formatFields('resourcequotecommentComment') . $this->db->like('%', '[', '%'));
        $resultset = $this->db->select('resource_quote_comment', ['resourcequotecommentId', 'resourcequotecommentComment']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $updateArray = [];
            if ($row['resourcequotecommentComment'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourcequotecommentComment']);
                $updateArray['resourcequotecommentComment'] = \HTML\nlToHtml($string, TRUE);
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['resourcequotecommentId' => $row['resourcequotecommentId']]);
                $this->db->update('resource_quote_comment', $updateArray);
            }
        }
        // Paraphrases
        //		$this->db->formatConditions($this->db->formatFields('resourceparaphrasetextText') . $this->db->like('%', '[', '%'));
        $resultset = $this->db->select('resource_paraphrase_text', ['resourceparaphrasetextId', 'resourceparaphrasetextText']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $updateArray = [];
            if ($row['resourceparaphrasetextText'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourceparaphrasetextText']);
                $updateArray['resourceparaphrasetextText'] = \HTML\nlToHtml($string, TRUE);
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['resourceparaphrasetextId' => $row['resourceparaphrasetextId']]);
                $this->db->update('resource_paraphrase_text', $updateArray);
            }
        }
        // Paraphrase comments
        //		$this->db->formatConditions($this->db->formatFields('resourceparaphrasecommentComment') . $this->db->like('%', '[', '%'));
        $resultset = $this->db->select('resource_paraphrase_comment', ['resourceparaphrasecommentId', 'resourceparaphrasecommentComment']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $updateArray = [];
            if ($row['resourceparaphrasecommentComment'])
            {
                $string = BBCODE::bbCodeToHtml($row['resourceparaphrasecommentComment']);
                $updateArray['resourceparaphrasecommentComment'] = \HTML\nlToHtml($string, TRUE);
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['resourceparaphrasecommentId' => $row['resourceparaphrasecommentId']]);
                $this->db->update('resource_paraphrase_comment', $updateArray);
            }
        }

        $this->checkStatus('stage4_1__13');
        $this->pauseExecution('stage4_1__13', 'stage4_1__14');
    }
    /**
     * stage 14
     */
    private function stage4_1__14()
    {
        $this->addMissingRows();
        // Session setup required in penultimate stage Рremoved for v5.3 upgrade
        //		$this->session->setVar("setup_UserId", 1);
        //		$user = FACTORY_USER::getInstance();
        //		$user->writeSessionPreferences(FALSE, 'config', TRUE);
        $this->checkStatus('stage4_1__14');
        $this->pauseExecution('stage4_1__14', 'stage4_1__15');
    }
    /**
     * stage 15
     */
    private function stage4_1__15()
    {
        $this->updateDbSchema('4.1.15');

        // Create a new column on the resource table that holds the title-subtitle without {}, ' or " in order to facilitate fast sorting
        $resultset = $this->db->query('SELECT ' . $this->db->replace($this->db->replace($this->db->replace($this->db->replace(
            'resourceTitle',
            '{',
            ''
        ), '}', '', FALSE), '"', '', FALSE), '\\\'', '', FALSE) . " AS 't', " .
            $this->db->replace($this->db->replace($this->db->replace($this->db->replace(
                'resourceSubtitle',
                '{',
                ''
            ), '}', '', FALSE), '"', '', FALSE), '\\\'', '', FALSE) . " AS 's', `resourceId` FROM " .
            $this->config->WIKINDX_DB_TABLEPREFIX . 'resource');
        while ($row = $this->db->fetchRow($resultset))
        {
            $title = '';
            if ($row['s'])
            {
                $title = $row['t'] . ' ' . $row['s'];
            }
            else
            {
                $title = $row['t'];
            }
            $this->db->formatConditions(['resourceId' => $row['resourceId']]);
            $this->db->update('resource', ['resourceTitleSort' => $title]);
        }

        //		$this->recreate40Cache();
        ini_set('memory_limit', $this->config->WIKINDX_MEMORY_LIMIT);
        if (array_key_exists('fixUTF8', $this->vars))
        {
            $this->fixUTF8();
        }
        $this->updateSoftwareVersion(4.1);
        $this->checkStatus('stage4_1__15');
        $this->pauseExecution('stage4_1__15');
    }
    /**
     * Upgrade 4.1 to 4.2 -- Stage 1
     */
    private function stage4_2__1()
    {
        $this->updateDbSchema('4.2.1');

        // v4.x code before 4.2 incorrectly named statistics field
        $fields = $this->db->listFields('statistics');
        if (array_search('statistics', $fields) !== FALSE)
        {
            $this->db->query('ALTER TABLE `' . $this->config->WIKINDX_DB_TABLEPREFIX . 'statistics` CHANGE `statistics` `statisticsStatistics` TEXT DEFAULT NULL');
        }
        $this->updateSoftwareVersion(4.2);
        $this->checkStatus('stage4_2__1');
        $this->pauseExecution('stage4_2__1');
    }
    /**
     * Upgrade 4.2 to 5.1 -- Stage 1
     *
     * Transfer all metadata tables to new unified resource_metadata table
     */
    private function stage5_1__1()
    {
        $this->updateDbSchema('5.1.1-begin');

        $maxPacket = $this->db->getMaxPacket();
        // For each 1MB max_allowed_packet (1048576 bytes), 600 updates in one go seems fine as a value for $maxCounts (based on trial and error)
        $maxCounts = floor(600 * ($maxPacket / 1048576));
        // Transfer quotes
        $updateArray = [];
        $count = 0;
        $insertArray = [];
        $countI = 0;
        $fields_c[] = 'resourcemetadataMetadataId';
        $fields_c[] = 'resourcemetadataText';
        $fields_c[] = 'resourcemetadataAddUserId';
        $fields_c[] = 'resourcemetadataTimestamp';
        $fields_c[] = 'resourcemetadataPrivate';
        $fields_c[] = 'resourcemetadataType';
        $this->db->leftJoin('resource_quote_text', 'resourcequotetextId', 'resourcequoteId');
        $resultset = $this->db->select('resource_quote', ['resourcequoteId', 'resourcequoteResourceId', 'resourcequotePageStart', 'resourcequotePageEnd',
            'resourcequoteParagraph', 'resourcequoteSection', 'resourcequoteChapter', 'resourcequotetextText', 'resourcequotetextAddUserIdQuote', ]);
        while ($row = $this->db->fetchRow($resultset))
        {
            ++$count;
            $fields = [];
            $values = [];
            $fields[] = 'resourcemetadataResourceId';
            $values[] = $row['resourcequoteResourceId'];
            if ($row['resourcequotePageStart'])
            {
                $fields[] = 'resourcemetadataPageStart';
                $values[] = $row['resourcequotePageStart'];
            }
            if ($row['resourcequotePageEnd'])
            {
                $fields[] = 'resourcemetadataPageEnd';
                $values[] = $row['resourcequotePageEnd'];
            }
            if ($row['resourcequoteParagraph'])
            {
                $fields[] = 'resourcemetadataParagraph';
                $values[] = $row['resourcequoteParagraph'];
            }
            if ($row['resourcequoteSection'])
            {
                $fields[] = 'resourcemetadataSection';
                $values[] = $row['resourcequoteSection'];
            }
            if ($row['resourcequoteChapter'])
            {
                $fields[] = 'resourcemetadataChapter';
                $values[] = $row['resourcequoteChapter'];
            }
            $fields[] = 'resourcemetadataType';
            $values[] = 'q';
            $fields[] = 'resourcemetadataText';
            $values[] = $row['resourcequotetextText'];
            $fields[] = 'resourcemetadataAddUserId';
            $values[] = $row['resourcequotetextAddUserIdQuote'];
            $this->db->insert('resource_metadata', $fields, $values);
            $id = $this->db->lastAutoID();
            $updateArray[$row['resourcequoteId']] = $id;
            if ($count >= $maxCounts)
            {
                $this->db->multiUpdate('resource_keyword', 'resourcekeywordMetadataId', 'resourcekeywordQuoteId', $updateArray);
                $updateArray = [];
                $count = 0;
            }
            // insert quote comments
            $this->db->formatConditions(['resourcequotecommentQuoteId' => $row['resourcequoteId']]);
            $resultset1 = $this->db->select(
                'resource_quote_comment',
                ['resourcequotecommentComment', 'resourcequotecommentAddUserIdQuote', 'resourcequotecommentTimestamp', 'resourcequotecommentPrivate']
            );
            while ($row1 = $this->db->fetchRow($resultset1))
            {
                ++$countI;
                $values_c = [];
                $values_c[] = $this->db->tidyInput($id);
                $values_c[] = $this->db->tidyInput($row1['resourcequotecommentComment']);
                $values_c[] = $this->db->tidyInput($row1['resourcequotecommentAddUserIdQuote']);
                $values_c[] = $this->db->tidyInput($row1['resourcequotecommentTimestamp']);
                $values_c[] = $this->db->tidyInput($row1['resourcequotecommentPrivate']);
                $values_c[] = $this->db->tidyInput('qc');
                $insertArray[] = '(' . implode(',', $values_c) . ')';
            }
            if ($countI >= $maxCounts)
            {
                $this->db->multiInsert('resource_metadata', $fields_c, implode(',', $insertArray));
                $insertArray = [];
                $countI = 0;
            }
        }
        if (!empty($insertArray))
        { // do the remainder
            $this->db->multiInsert('resource_metadata', $fields_c, implode(',', $insertArray));
        }
        if (!empty($updateArray))
        { // do the remainder
            $this->db->multiUpdate('resource_keyword', 'resourcekeywordMetadataId', 'resourcekeywordQuoteId', $updateArray);
        }
        $updateArray = [];
        $count = 0;
        $insertArray = [];
        $countI = 0;
        // Transfer paraphrases
        $this->db->leftJoin('resource_paraphrase_text', 'resourceparaphrasetextId', 'resourceparaphraseId');
        $resultset = $this->db->select('resource_paraphrase', ['resourceparaphraseId', 'resourceparaphraseResourceId', 'resourceparaphrasePageStart',
            'resourceparaphrasePageEnd', 'resourceparaphraseParagraph', 'resourceparaphraseSection', 'resourceparaphraseChapter',
            'resourceparaphrasetextText', 'resourceparaphrasetextAddUserIdParaphrase', ]);
        while ($row = $this->db->fetchRow($resultset))
        {
            ++$count;
            $fields = [];
            $values = [];
            $fields[] = 'resourcemetadataResourceId';
            $values[] = $row['resourceparaphraseResourceId'];
            if ($row['resourceparaphrasePageStart'])
            {
                $fields[] = 'resourcemetadataPageStart';
                $values[] = $row['resourceparaphrasePageStart'];
            }
            if ($row['resourceparaphrasePageEnd'])
            {
                $fields[] = 'resourcemetadataPageEnd';
                $values[] = $row['resourceparaphrasePageEnd'];
            }
            if ($row['resourceparaphraseParagraph'])
            {
                $fields[] = 'resourcemetadataParagraph';
                $values[] = $row['resourceparaphraseParagraph'];
            }
            if ($row['resourceparaphraseSection'])
            {
                $fields[] = 'resourcemetadataSection';
                $values[] = $row['resourceparaphraseSection'];
            }
            if ($row['resourceparaphraseChapter'])
            {
                $fields[] = 'resourcemetadataChapter';
                $values[] = $row['resourceparaphraseChapter'];
            }
            $fields[] = 'resourcemetadataType';
            $values[] = 'p';
            $fields[] = 'resourcemetadataText';
            $values[] = $row['resourceparaphrasetextText'];
            $fields[] = 'resourcemetadataAddUserId';
            $values[] = $row['resourceparaphrasetextAddUserIdParaphrase'];
            $this->db->insert('resource_metadata', $fields, $values);
            $id = $this->db->lastAutoID();
            $updateArray[$row['resourceparaphraseId']] = $id;
            if ($count >= $maxCounts)
            {
                $this->db->multiUpdate('resource_keyword', 'resourcekeywordMetadataId', 'resourcekeywordParaphraseId', $updateArray);
                $updateArray = [];
                $count = 0;
            }
            // insert paraphrase comments
            $this->db->formatConditions(['resourceparaphrasecommentParaphraseId' => $row['resourceparaphraseId']]);
            $resultset1 = $this->db->select('resource_paraphrase_comment', ['resourceparaphrasecommentComment',
                'resourceparaphrasecommentAddUserIdParaphrase', 'resourceparaphrasecommentTimestamp', 'resourceparaphrasecommentPrivate', ]);
            while ($row1 = $this->db->fetchRow($resultset1))
            {
                ++$countI;
                $values_c = [];
                $values_c[] = $this->db->tidyInput($id);
                $values_c[] = $this->db->tidyInput($row1['resourceparaphrasecommentComment']);
                $values_c[] = $this->db->tidyInput($row1['resourceparaphrasecommentAddUserIdParaphrase']);
                $values_c[] = $this->db->tidyInput($row1['resourceparaphrasecommentTimestamp']);
                $values_c[] = $this->db->tidyInput($row1['resourceparaphrasecommentPrivate']);
                $values_c[] = $this->db->tidyInput('pc');
                $insertArray[] = '(' . implode(',', $values_c) . ')';
            }
            if ($countI >= $maxCounts)
            {
                $this->db->multiInsert('resource_metadata', $fields_c, implode(',', $insertArray));
                $insertArray = [];
                $countI = 0;
            }
        }
        if (!empty($insertArray))
        { // do the remainder
            $this->db->multiInsert('resource_metadata', $fields_c, implode(',', $insertArray));
        }
        if (!empty($updateArray))
        { // do the remainder
            $this->db->multiUpdate('resource_keyword', 'resourcekeywordMetadataId', 'resourcekeywordParaphraseId', $updateArray);
        }
        // Transfer musings
        $updateArray = [];
        $count = 0;
        $resultset = $this->db->select('resource_musing', '*');
        while ($row = $this->db->fetchRow($resultset))
        {
            ++$count;
            $fields = [];
            $values = [];
            $fields[] = 'resourcemetadataResourceId';
            $values[] = $row['resourcemusingResourceId'];
            if ($row['resourcemusingPageStart'])
            {
                $fields[] = 'resourcemetadataPageStart';
                $values[] = $row['resourcemusingPageStart'];
            }
            if ($row['resourcemusingPageEnd'])
            {
                $fields[] = 'resourcemetadataPageEnd';
                $values[] = $row['resourcemusingPageEnd'];
            }
            if ($row['resourcemusingParagraph'])
            {
                $fields[] = 'resourcemetadataParagraph';
                $values[] = $row['resourcemusingParagraph'];
            }
            if ($row['resourcemusingSection'])
            {
                $fields[] = 'resourcemetadataSection';
                $values[] = $row['resourcemusingSection'];
            }
            if ($row['resourcemusingChapter'])
            {
                $fields[] = 'resourcemetadataChapter';
                $values[] = $row['resourcemusingChapter'];
            }
            $fields[] = 'resourcemetadataType';
            $values[] = 'm';
            $this->db->formatConditions(['resourcemusingtextId' => $row['resourcemusingId']]);
            $row1 = $this->db->selectFirstRow(
                'resource_musing_text',
                ['resourcemusingtextText', 'resourcemusingtextAddUserIdMusing', 'resourcemusingtextTimestamp', 'resourcemusingtextPrivate']
            );
            $fields[] = 'resourcemetadataText';
            $values[] = $row1['resourcemusingtextText'];
            $fields[] = 'resourcemetadataAddUserId';
            $values[] = $row1['resourcemusingtextAddUserIdMusing'];
            $fields[] = 'resourcemetadataTimestamp';
            $values[] = $row1['resourcemusingtextTimestamp'];
            $fields[] = 'resourcemetadataPrivate';
            $values[] = $row1['resourcemusingtextPrivate'];
            $this->db->insert('resource_metadata', $fields, $values);
            $id = $this->db->lastAutoID();
            $updateArray[$row['resourcemusingId']] = $id;
            if ($count >= $maxCounts)
            {
                $this->db->multiUpdate('resource_keyword', 'resourcekeywordMetadataId', 'resourcekeywordMusingId', $updateArray);
                $updateArray = [];
                $count = 0;
            }
        }
        if (!empty($updateArray))
        { // do the remainder
            $this->db->multiUpdate('resource_keyword', 'resourcekeywordMetadataId', 'resourcekeywordMusingId', $updateArray);
        }
        /**
         * Do some corrections
         */
        // Correct incorrect database summary figures for metadata
        $this->db->formatConditions(['resourcekeywordKeywordId' => ' IS NULL']);
        $this->db->delete('resource_keyword');
        $mIds = [];
        $resultSet = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataText']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            if (!$row['resourcemetadataText'])
            {
                $mIds[] = $row['resourcemetadataId'];
            }
        }
        if (!empty($mIds))
        {
            $this->db->formatConditionsOneField($mIds, 'resourcemetadataId');
            $this->db->delete('resource_metadata');
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
        // Correct empty year column error
        $this->db->formatConditions(['resourceyearYear2' => ' IS NOT NULL']);
        $this->db->formatConditions(['resourceyearYear2' => '']);
        $resultSet = $this->db->select('resource_year', ['resourceyearId']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            $yIds[] = $row['resourceyearId'];
        }
        if (!empty($yIds))
        {
            $this->db->formatConditionsOneField($yIds, 'resourceyearId');
            $this->db->updateNull('resource_year', ['resourceyearYear2']);
        }
        $yIds = [];
        $this->db->formatConditions(['resourceyearYear3' => ' IS NOT NULL']);
        $this->db->formatConditions(['resourceyearYear3' => '']);
        $resultSet = $this->db->select('resource_year', ['resourceyearId']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            $yIds[] = $row['resourceyearId'];
        }
        if (!empty($yIds))
        {
            $this->db->formatConditionsOneField($yIds, 'resourceyearId');
            $this->db->updateNull('resource_year', ['resourceyearYear2']);
        }
        // Correct inclusion of HTML in resource::resourceTitleSort and remove all symbols
        $updateArray = [];
        $count = 0;
        $resultSet = $this->db->select('resource', ['resourceId', 'resourceTitleSort']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            $count++;
            $string = \HTML\stripHtml($row['resourceTitleSort']);
            // remove all punctuation (keep apostrophe and dash for names such as Grimshaw-Aagaard and D'Eath)
            $updateArray[$row['resourceId']] = preg_replace('/[^\p{L}\p{N}\s\-\']/u', '', $string . "");
            if ($count >= $maxCounts)
            {
                $this->db->multiUpdate('resource', 'resourceTitleSort', 'resourceId', $updateArray);
                $updateArray = [];
                $count = 0;
            }
        }
        // Sessionstate fields for all users should be set to NULL as they may contain (SQL) code that relates to old formats and structures
        $this->db->updateNull('users', 'usersUserSession');
        // set collectionDefault column in the collections table
        $this->collectionDefaults();
        // Strip bbCode from users table
        $this->stripBBCode($maxCounts);
        // Alter resourcecreator::creatorSurname field to allow for more accurate ordering
        $this->alterCreatorSurname($maxCounts);

        $this->updateDbSchema('5.1.1-end');
        
        $this->updateSoftwareVersion(5.1);
        $this->checkStatus('stage5_1__1');
        $this->pauseExecution('stage5_1__1');
    }
    /**
     * Upgrade database schema to version 5.2
     * Use MySQL utf8 encode and collation utf8_unicode_520_ci
     * Lowercase all table names
     * Use InnoDB for all tables
     */
    private function stage5_2__1()
    {
        $this->updateDbSchema('5.2');
        
        $this->updateSoftwareVersion(5.2);
        $this->checkStatus('stage5_2__1');
        $this->pauseExecution('stage5_2__1');
    }
    /**
     * Upgrade database schema to version 5.4.
     * Reconfiguration of config table and shifting many variables to it from config.php
     */
    private function stage5_4__1()
    {
        $this->writeConfigFile5_4(); // dies if not possible
        $pf = $this->config->WIKINDX_DB_TABLEPREFIX;

        $this->updateDbSchema('5.4-begin');

        if (!$this->db->tableExists('configtemp'))
        {
            // Update db summary no. in case we have attempted to upgrade a database less than 5.3 (we've been through the previous stages successfully)
            $this->updateSoftwareVersion(5.3);
            die("Unable to create 'configtemp' table in the database. Check the database permissions.");
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
                ($key == 'configPrintSql')
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
        // Now copy across selected config.php variables
        if (isset($this->config->WIKINDX_TIMEZONE) && $this->config->WIKINDX_TIMEZONE)
        {
            $this->db->insert('configtemp', ['configName', 'configText'], ['configTimezone', $this->config->WIKINDX_TIMEZONE]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configText'], ['configTimezone', WIKINDX_TIMEZONE_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_RESTRICT_USERID) && $this->config->WIKINDX_RESTRICT_USERID)
        {
            $this->db->insert('configtemp', ['configName', 'configInt'], ['configRestrictUserId', $this->config->WIKINDX_RESTRICT_USERID]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configInt'], ['configRestrictUserId', WIKINDX_RESTRICT_USERID_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_DEACTIVATE_RESOURCE_TYPES) &&
            is_array($this->config->WIKINDX_DEACTIVATE_RESOURCE_TYPES) && !empty($this->config->WIKINDX_DEACTIVATE_RESOURCE_TYPES))
        {
            $this->db->insert('configtemp', ['configName', 'configText'], ['configDeactivateResourceTypes',
                base64_encode(serialize($this->config->WIKINDX_DEACTIVATE_RESOURCE_TYPES)), ]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configText'], ['configDeactivateResourceTypes', base64_encode(serialize([]))]);
        }
        if (isset($this->config->WIKINDX_RSS_ALLOW) && $this->config->WIKINDX_RSS_ALLOW)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configRssAllow', $this->config->WIKINDX_RSS_ALLOW]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configRssAllow', WIKINDX_RSS_ALLOW_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_RSS_BIBSTYLE) && $this->config->WIKINDX_RSS_BIBSTYLE)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configRssBibstyle', $this->config->WIKINDX_RSS_BIBSTYLE]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configRssBibstyle', WIKINDX_RSS_BIBSTYLE_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_RSS_LIMIT) && $this->config->WIKINDX_RSS_LIMIT)
        {
            $this->db->insert('configtemp', ['configName', 'configInt'], ['configRssLimit', $this->config->WIKINDX_RSS_LIMIT]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configInt'], ['configRssLimit', WIKINDX_RSS_LIMIT_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_RSS_DISPLAY) && $this->config->WIKINDX_RSS_DISPLAY)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configRssDisplay', $this->config->WIKINDX_RSS_DISPLAY]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configRssDisplay', WIKINDX_RSS_DISPLAY_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_RSS_TITLE) && $this->config->WIKINDX_RSS_TITLE)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configRssTitle', $this->config->WIKINDX_RSS_TITLE]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configRssTitle', WIKINDX_RSS_TITLE_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_RSS_DESCRIPTION) && $this->config->WIKINDX_RSS_DESCRIPTION)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configRssDescription', $this->config->WIKINDX_RSS_DESCRIPTION]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configRssDescription', WIKINDX_RSS_DESCRIPTION_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_SERVER) && $this->config->WIKINDX_MAIL_SERVER)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configMailServer', $this->config->WIKINDX_MAIL_SERVER]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configMailServer', WIKINDX_MAIL_SERVER_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_FROM) && $this->config->WIKINDX_MAIL_FROM)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailFrom', $this->config->WIKINDX_MAIL_FROM]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailFrom', WIKINDX_MAIL_FROM_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_REPLYTO) && $this->config->WIKINDX_MAIL_REPLYTO)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailReplyTo', $this->config->WIKINDX_MAIL_REPLYTO]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailReplyTo', WIKINDX_MAIL_REPLYTO_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_RETURN_PATH) && $this->config->WIKINDX_MAIL_RETURN_PATH)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailReturnPath', $this->config->WIKINDX_MAIL_RETURN_PATH]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailReturnPath', WIKINDX_MAIL_RETURN_PATH_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_BACKEND) && $this->config->WIKINDX_MAIL_BACKEND)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailBackend', $this->config->WIKINDX_MAIL_BACKEND]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailBackend', WIKINDX_MAIL_BACKEND_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_SMPATH) && $this->config->WIKINDX_MAIL_SMPATH)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailSmPath', $this->config->WIKINDX_MAIL_SMPATH]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailSmPath', WIKINDX_MAIL_SMPATH_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_SMTPSERVER) && $this->config->WIKINDX_MAIL_SMTPSERVER)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailSmtpServer', $this->config->WIKINDX_MAIL_SMTPSERVER]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailSmtpServer', WIKINDX_MAIL_SMTPSERVER_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_SMTPPORT) && $this->config->WIKINDX_MAIL_SMTPPORT)
        {
            $this->db->insert('configtemp', ['configName', 'configInt'], ['configMailSmtpPort', $this->config->WIKINDX_MAIL_SMTPPORT]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configInt'], ['configMailSmtpPort', WIKINDX_MAIL_SMTPPORT_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_SMTPENCRYPT) && $this->config->WIKINDX_MAIL_SMTPENCRYPT)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailSmtpEncrypt', $this->config->WIKINDX_MAIL_SMTPENCRYPT]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailSmtpEncrypt', WIKINDX_MAIL_SMTPENCRYPT_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_SMTPPERSIST) && $this->config->WIKINDX_MAIL_SMTPPERSIST)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configMailSmtpPersist', $this->config->WIKINDX_MAIL_SMTPPERSIST]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configMailSmtpPersist', WIKINDX_MAIL_SMTPPERSIST_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_SMTPAUTH) && $this->config->WIKINDX_MAIL_SMTPAUTH)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configMailSmtpAuth', $this->config->WIKINDX_MAIL_SMTPAUTH]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configMailSmtpAuth', WIKINDX_MAIL_SMTPAUTH_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_SMTPUSERNAME) && $this->config->WIKINDX_MAIL_SMTPUSERNAME)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailSmtpUsername', $this->config->WIKINDX_MAIL_SMTPUSERNAME]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailSmtpUsername', WIKINDX_MAIL_SMTPUSERNAME_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_MAIL_SMTPPASSWORD) && $this->config->WIKINDX_MAIL_SMTPPASSWORD)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailSmtpPassword', $this->config->WIKINDX_MAIL_SMTPPASSWORD]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configMailSmtpPassword', WIKINDX_MAIL_SMTPPASSWORD_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_GS_ALLOW) && $this->config->WIKINDX_GS_ALLOW)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configGsAllow', $this->config->WIKINDX_GS_ALLOW]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configGsAllow', WIKINDX_GS_ALLOW_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_GS_ATTACHMENT) && $this->config->WIKINDX_GS_ATTACHMENT)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configGsAttachment', $this->config->WIKINDX_GS_ATTACHMENT]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configGsAttachment', WIKINDX_GS_ATTACHMENT_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_CMS_ALLOW) && $this->config->WIKINDX_CMS_ALLOW)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configCmsAllow', $this->config->WIKINDX_CMS_ALLOW]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configCmsAllow', WIKINDX_CMS_ALLOW_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_CMS_BIBSTYLE) && $this->config->WIKINDX_CMS_BIBSTYLE)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configCmsBibstyle', $this->config->WIKINDX_CMS_BIBSTYLE]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configCmsBibstyle', WIKINDX_CMS_BIBSTYLE_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_CMS_SQL) && $this->config->WIKINDX_CMS_SQL)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configCmsSql', $this->config->WIKINDX_CMS_SQL]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configCmsSql', WIKINDX_CMS_SQL_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_CMS_DB_USER) && $this->config->WIKINDX_CMS_DB_USER)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configCmsDbUser', $this->config->WIKINDX_CMS_DB_USER]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configCmsDbUser', WIKINDX_CMS_DB_USER_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_CMS_DB_PASSWORD) && $this->config->WIKINDX_CMS_DB_PASSWORD)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configCmsDbPassword', $this->config->WIKINDX_CMS_DB_PASSWORD]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configCmsDbPassword', WIKINDX_CMS_DB_PASSWORD_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_TAG_LOW_COLOUR) && $this->config->WIKINDX_TAG_LOW_COLOUR)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configTagLowColour', $this->config->WIKINDX_TAG_LOW_COLOUR]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configTagLowColour', WIKINDX_TAG_LOW_COLOUR_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_TAG_HIGH_COLOUR) && $this->config->WIKINDX_TAG_HIGH_COLOUR)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configTagHighColour', $this->config->WIKINDX_TAG_HIGH_COLOUR]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configTagHighColour', WIKINDX_TAG_HIGH_COLOUR_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_TAG_LOW_SIZE) && $this->config->WIKINDX_TAG_LOW_SIZE)
        {
            $this->db->insert('configtemp', ['configName', 'configFloat'], ['configTagLowSize', $this->config->WIKINDX_TAG_LOW_SIZE]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configFloat'], ['configTagLowSize', WIKINDX_TAG_LOW_SIZE_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_TAG_HIGH_SIZE) && $this->config->WIKINDX_TAG_HIGH_SIZE)
        {
            $this->db->insert('configtemp', ['configName', 'configFloat'], ['configTagHighSize', $this->config->WIKINDX_TAG_HIGH_SIZE]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configFloat'], ['configTagHighSize', WIKINDX_TAG_HIGH_SIZE_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_IMAGES_ALLOW) && $this->config->WIKINDX_IMAGES_ALLOW)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configImagesAllow', $this->config->WIKINDX_IMAGES_ALLOW]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configImagesAllow', WIKINDX_IMAGES_ALLOW_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_IMAGES_MAXSIZE) && $this->config->WIKINDX_IMAGES_MAXSIZE)
        {
            $this->db->insert('configtemp', ['configName', 'configInt'], ['configImagesMaxSize', $this->config->WIKINDX_IMAGES_MAXSIZE]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configInt'], ['configImagesMaxSize', WIKINDX_IMAGES_MAXSIZE_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_DEBUG_ERRORS) && $this->config->WIKINDX_DEBUG_ERRORS)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configErrorReport', $this->config->WIKINDX_DEBUG_ERRORS]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configErrorReport', WIKINDX_DEBUG_ERRORS_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_DEBUG_EMAIL) && $this->config->WIKINDX_DEBUG_EMAIL)
        {
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configDebugEmail', $this->config->WIKINDX_DEBUG_EMAIL]);
        }
        else
        { // NB database name change for this field!
            $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configDebugEmail', WIKINDX_DEBUG_EMAIL_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_DEBUG_SQL) && $this->config->WIKINDX_DEBUG_SQL)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configPrintSql', $this->config->WIKINDX_DEBUG_SQL]);
        }
        else
        { // NB database name change for this field!
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configPrintSql', WIKINDX_DEBUG_SQL_DEFAULT]);
        }
        // Add extra fields
        $this->db->insert('configtemp', ['configName', 'configVarchar'], ['configSqlErrorOutput', 'printSql']);
        if (isset($this->config->WIKINDX_BYPASS_SMARTYCOMPILE) && $this->config->WIKINDX_BYPASS_SMARTYCOMPILE)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configBypassSmartyCompile',
                $this->config->WIKINDX_BYPASS_SMARTYCOMPILE, ]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configBypassSmartyCompile', WIKINDX_BYPASS_SMARTYCOMPILE_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_DISPLAY_STATISTICS) && $this->config->WIKINDX_DISPLAY_STATISTICS)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configDisplayStatistics', $this->config->WIKINDX_DISPLAY_STATISTICS]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configDisplayStatistics', WIKINDX_DISPLAY_STATISTICS_DEFAULT]);
        }
        if (isset($this->config->WIKINDX_DISPLAY_USER_STATISTICS) && $this->config->WIKINDX_DISPLAY_USER_STATISTICS)
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configDisplayUserStatistics', $this->config->WIKINDX_DISPLAY_USER_STATISTICS]);
        }
        else
        {
            $this->db->insert('configtemp', ['configName', 'configBoolean'], ['configDisplayUserStatistics', WIKINDX_DISPLAY_USER_STATISTICS_DEFAULT]);
        }

        $this->updateDbSchema('5.4-end');

        $this->session->setVar("setup_UserId", 1);
        $user = FACTORY_USER::getInstance();
        $user->writeSessionPreferences(FALSE, 'config', TRUE);
        
        $this->updateSoftwareVersion(5.4);
        $this->checkStatus('stage5_4__1');
        $this->pauseExecution('stage5_4__1');
    }
    /**
     * Upgrade database schema to version 5.5.
     * Addition of new fields to users table for auth security and GDPR
     */
    private function stage5_5__1()
    {
        $this->updateDbSchema('5.5');
        $this->updatePluginTables();
        
        $this->updateSoftwareVersion(5.5);
        $this->checkStatus('stage5_5__1');
        $this->pauseExecution('stage5_5__1');
    }
    /**
     * Upgrade database schema to version 5.6.
     * Convert the database to utf8 charset and utf8_unicode_ci collation
     */
    private function stage5_6__1()
    {
        $this->updateDbSchema('5.6');
        
        $this->updateSoftwareVersion(5.6);
        $this->checkStatus('stage5_6__1');
        $this->pauseExecution('stage5_6__1');
    }
    /**
     * Upgrade database schema to version 5.7.
     * Convert the database to utf8mb4 charset and utf8mb4_unicode_520_ci collation
     * Fix resource_metadata.resourcemetadataPrivate size to 1 character
     */
    private function stage5_7__1()
    {
        $this->correctIndices();
        $this->updateDbSchema('5.7');
        $this->correctDatetimeFields();
        
        $this->updateSoftwareVersion(5.7);
        $this->checkStatus('stage5_7__1');
        $this->pauseExecution('stage5_7__1');
    }
    /**
     * Upgrade database schema to version 5.8. There are no changes to DB structure so no call to updateDbSchema('5.8').
     * Check resource totals are correct
     * Check creator correlations are correct
     */
    private function stage5_8__1()
    {
        $this->correctTotals();
        $this->correctCreators();
        
        $this->updateSoftwareVersion(5.8);
        $this->checkStatus('stage5_8__1');
        $this->pauseExecution('stage5_8__1');
    }
    /**
     * Upgrade database schema to version 5.9
     *
     * Move directories attachments, attachments_cache, images, and files
     * Change configuration
     * Update images links
     */
    private function stage5_9__1()
    {
        // Copy files in various old directories to their new directories
        // Order is important – ned to know if files or attachments returns FALSE
        $return = $this->copyFolderContents('attachments_cache', WIKINDX_DIR_CACHE_ATTACHMENTS);
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        $return = $this->copyFolderContents('images', WIKINDX_DIR_DATA_IMAGES);
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        $return = $this->copyFolderContents('files', WIKINDX_DIR_DATA_FILES);
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        $return = $this->copyFolderContents('attachments', WIKINDX_DIR_DATA_ATTACHMENTS);
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        $this->writeConfigFile5_9(); // dies if not possible
        $this->updateDbSchema('5.9');
        $this->updateImageLinks();
        
        $this->updateSoftwareVersion(5.9);
        
        echo $this->installMessages->text("upgradeDBv5.9");
        $this->checkStatus('stage5_9__1');
        $this->pauseExecution('stage5_9__1');
    }
    /**
     * Upgrade database schema to version 6
     *
     * Move word processor papers and styles custom styles components
     */
    private function stage6()
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
        
        $this->updateSoftwareVersion(6);
        
        echo $this->installMessages->text("upgradeDBv6");
        $this->checkStatus('stage6');
        $this->pauseExecution('stage6');
    }
    /**
     * Upgrade database schema to version 7 (6.0.4)
     */
    private function stage7()
    {
        $this->updateDbSchema('7');
        $this->updateSoftwareVersion(7);
        $this->checkStatus('stage7');
        $this->pauseExecution('stage7');
    }
    /**
     * Upgrade database schema to version 8 (6.0.5)
     */
    private function stage8()
    {
        $this->updateDbSchema('8');
        $this->updateSoftwareVersion(8);
        $this->checkStatus('stage8');
        $this->pauseExecution('stage8');
    }
    /**
     * Upgrade database schema to version 9 (6.0.6)
     */
    private function stage9()
    {
        $this->updateImageLinks();
        $return = $this->copyWpContents();
        if ($return !== TRUE)
        {
            $this->checkDatabase($return);
        }
        
        $this->updateSoftwareVersion(9);
        $this->checkStatus('stage9');
        $this->pauseExecution('stage9');
    }
    /**
     * Upgrade database schema to version 10 (6.0.8)
     */
    private function stage10()
    {
        // Add FULLTEXT indices
        $this->updateDbSchema('10');
        
        $this->updateSoftwareVersion(10);
        $this->checkStatus('stage10');
        $this->pauseExecution('stage10');
    }
    /**
     * Upgrade database schema to version 10 (6.2.1)
     */
    private function stage11()
    {
        // Convert tag sizes to scale factors
        $this->updateDbSchema('11');
        
        $this->updateSoftwareVersion(11);
        $this->checkStatus('stage11');
        $this->pauseExecution('stage11');
    }
    /**
     * Copy non-official bibliographic styles (if they exist)
     */
    private function copyBibContents()
    {
    	$oldDir = 'styles' . DIRECTORY_SEPARATOR . 'bibliography';
        foreach (\FILE\dirInDirToArray($oldDir) as $dir)
        {
            $dirLower = mb_strtolower($dir);
            
            // Not styles released as part of 5.8.2
            if (array_search($dir, ['abnt', 'apa', 'britishmedicaljournal', 'cfb', 'chicago', 'harvard', 'ieee', 'mla',
                'turabian', 'wikindx', ]) !== FALSE)
            {
                continue;
            }
            
            $oldDirStyle = $oldDir . DIRECTORY_SEPARATOR . $dir;
            $newDirStyle = WIKINDX_DIR_COMPONENT_STYLES . DIRECTORY_SEPARATOR . $dirLower;
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
        $newDir = WIKINDX_DIR_DATA_PLUGINS . DIRECTORY_SEPARATOR . 'wordprocessor';
        foreach (['wordProcessor', 'wordprocessor'] as $plugindir)
        {
            $oldDir = 'plugins' . DIRECTORY_SEPARATOR . $plugindir . DIRECTORY_SEPARATOR . 'papers';
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
            $doc = str_replace('<img src="images/', '<img src="' . str_replace("\\", "/", WIKINDX_DIR_DATA_IMAGES) . '/', $doc);
        
            // Fix in 6.0.6 a previous error during the migration of images links in papers introduced in 5.9
            // The folder separator must be / and not \ otherwise the image display is broken
            $doc = str_replace('<img src="' . WIKINDX_DIR_DATA . '\\', '<img src="' . WIKINDX_DIR_DATA . '/', $doc);
            
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
            $text = str_replace('<img src="images/', '<img src="' . str_replace("\\", "/", WIKINDX_DIR_DATA_IMAGES) . '/', $text);
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
        $stage5_8__1_correctCreators_resourcecreatorId = $this->session->getVar('stage5_8__1_correctCreators_resourcecreatorId', -1);
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
        
        if ($stage5_8__1_correctCreators_resourcecreatorId != -1)
        {
            while ($row = $this->db->fetchRow($resultSet1))
            {
                if ($row['resourcecreatorId'] == $stage5_8__1_correctCreators_resourcecreatorId)
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
        $db = $this->config->WIKINDX_DB;
        foreach (['category', 'collection', 'config', 'creator', 'keyword', 'publisher', 'resource', 'resource_creator',
            'resource_metadata', 'resource_year', 'user_bibliography', ] as $table)
        {
            $table = $this->config->WIKINDX_DB_TABLEPREFIX . $table;
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
        $this->db->formatConditions($this->db->formatFields('usersChangePasswordTimestamp'));
        $minArray = $this->db->selectMin('users', 'usersChangePasswordTimestamp');
        $min = $minArray[0]['usersChangePasswordTimestamp'];
        $this->db->formatConditions(['usersChangePasswordTimestamp' => '0000-00-00 00:00:00']);
        if ($min)
        {
            $this->db->updateTimestamp('users', ['usersChangePasswordTimestamp' => $this->db->tidyInput($min)]);
        }
        else
        {
            $this->db->updateTimestamp('users', ['usersChangePasswordTimestamp' => '']); // default is CURRENT_TIMESTAMP
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
     * Write new config.php with upgrade to >= WIKINDX v5.3
     */
    private function writeConfigFile5_4()
    {
        $string = <<<END
<?php
/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
        $string .= 'public $WIKINDX_DB_HOST = "' . $this->config->WIKINDX_DB_HOST . '";' . "\n";
        $string .= '// name of the database which these scripts interface with (case-sensitive):' . "\n" .
                   'public $WIKINDX_DB = "' . $this->config->WIKINDX_DB . '";' . "\n";
        $string .= '// username and password required to connect to and open the database' . "\n" .
                   '// (it is strongly recommended that you change these default values):' . "\n" .
                   'public $WIKINDX_DB_USER = "' . $this->config->WIKINDX_DB_USER . '";' . "\n" .
                   'public $WIKINDX_DB_PASSWORD = "' . $this->config->WIKINDX_DB_PASSWORD . '";' . "\n";
        $string .= '// If using WIKINDX on a shared database, set the WIKINDX table prefix here (lowercase only)' . "\n" .
                   '// (do not change after running WIKINDX and creating the tables!):' . "\n" .
                   'public $WIKINDX_DB_TABLEPREFIX = "' . $this->config->WIKINDX_DB_TABLEPREFIX . '";' . "\n";
        $string .= '// WIKINDX uses MySQL persistent connections by default.' . "\n" .
                   '// Some hosting services are not configured for this: if you have problems' . "\n" .
                   "// connecting to your MySQL server and/or receive error messages about 'too many connections'," . "\n" .
                   '// set $WIKINDX_DB_PERSISTENT to FALSE' . "\n";
        if ($this->config->WIKINDX_DB_PERSISTENT === TRUE)
        {
            $string .= 'public $WIKINDX_DB_PERSISTENT = TRUE;' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_DB_PERSISTENT = FALSE;' . "\n";
        }
        $string .= <<<END
/*****
* END DATABASE CONFIGURATION
*****/

/**********************************************************************************/

/*****
* START PATHS CONFIGURATION
*****/
// You must define the base URL for the WIKINDX installation.
// You have to indicate protocol HTTP / HTTPS and remove the terminal /.
// e.g. if wikindx's index.php file is in /wikindx/ under the httpd/ (or similar)
// folder on the www.myserver.com, then set the variable
// to http://www.myserver.com/wikindx

END;
        // We set it because we know best ;)
        $this->config->WIKINDX_BASE_URL = FACTORY_URL::getInstance()->getBaseUrl();
        $string .= 'public $WIKINDX_BASE_URL = "' . $this->config->WIKINDX_BASE_URL . '";' . "\n";

        $string .= <<<END
// The TinyMCE editor needs the WIKINDX server installation path.
// WIKINDX tries to get this through getcwd() but this is not always possible.
// In this case, you will receive an error message and WIKINDX will die and you should then set that path here.
// The path should be the full path from the root folder to your wikindx folder with no trailing '/'.
// On Apple OSX running XAMPP, for example, the case-sensitive path is:
// '/Applications/XAMPP/xamppfiles/htdocs/wikindx'.
// The script will continue to die until it has a valid installation path.
// If you get no error message and WIKINDX runs fine, then you can leave this value as FALSE.

END;
        if (property_exists($this->config, 'WIKINDX_WIKINDX_PATH') && ($this->config->WIKINDX_WIKINDX_PATH !== FALSE))
        {
            $string .= 'public $WIKINDX_WIKINDX_PATH = "' . $this->config->WIKINDX_WIKINDX_PATH . '";' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_WIKINDX_PATH = FALSE;' . "\n";
        }
        $string .= <<<END
// Alternate locations for storing attachments and exported files.
// If these are FALSE, the default locations at the top level of wikindx/ will be used.
// It is the administrator's responsibility to ensure that these directories are web-server user readable and writeable.
// There should be no trailing '\\' or '/'.
// For example, for a windows system, WIKINDX_ATTACHMENTS_DIR might be "D:\\attachments"
// For example, for a *NIX system, WIKINDX_FILE_PATH might be "files"

END;
        if (property_exists($this->config, 'WIKINDX_ATTACHMENTS_PATH') && (WIKINDX_DIR_DATA_ATTACHMENTS !== FALSE))
        {
            $string .= 'public $WIKINDX_ATTACHMENTS_PATH = "' . WIKINDX_DIR_DATA_ATTACHMENTS . '";' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_ATTACHMENTS_PATH = FALSE;' . "\n";
        }
        if (property_exists($this->config, 'WIKINDX_FILE_PATH') && (WIKINDX_DIR_DATA_FILES !== FALSE))
        {
            $string .= 'public $WIKINDX_FILE_PATH = "' . WIKINDX_DIR_DATA_FILES . '";' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_FILE_PATH = FALSE;' . "\n";
        }
        $string .= <<<END
/*****
* END PATH CONFIGURATION
*****/

/**********************************************************************************/

/*****
* START PHP MEMORY AND EXECUTION CONFIGURATION
*****/
// WIKINDX usually runs with the standard PHP memory_limit of 32MB.
// With some PHP configurations, however, this is not enough -- a mysterious blank page is often the result.
// If you are unable to update php.ini's memory_limit yourself, WIKINDX_MEMORY_LIMIT may be set (an integer such as 64 or 128 followed by 'M').
// Despite the PHP manual stating that this may not be set outside of php.ini, it seems to work most of the time.
// It is not, however, guaranteed to do so and editing php.ini is the preferred method particularly if your PHP is in 'safe' mode.
// Use double quotes around the value.

END;
        if (property_exists($this->config, 'WIKINDX_MEMORY_LIMIT') && ($this->config->WIKINDX_MEMORY_LIMIT !== FALSE))
        {
            $string .= 'public $WIKINDX_MEMORY_LIMIT = "' . $this->config->WIKINDX_MEMORY_LIMIT . '";' . "\n";
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
        if (property_exists($this->config, 'WIKINDX_MAX_EXECUTION_TIMEOUT') && ($this->config->WIKINDX_MAX_EXECUTION_TIMEOUT !== FALSE))
        {
            $string .= 'public $WIKINDX_MAX_EXECUTION_TIMEOUT = ' . $this->config->WIKINDX_MAX_EXECUTION_TIMEOUT . ';' . "\n";
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
// As a rough guide, with a WIKINDX_MEMORY_LIMIT of 32M, WIKINDX_MAX_WRITECHUNK of 700 should work fine and with 64M, 1500 works fine.
// If WIKINDX_MAX_WRITECHUNK is FALSE, the chunk is set to 10,000.
// This can be a tricky figure to set as setting the figure too low increases SQL and PHP execution times significantly.
// Do NOT use quotes around the value.

END;
        if (property_exists($this->config, 'WIKINDX_MAX_WRITECHUNK') && ($this->config->WIKINDX_MAX_WRITECHUNK !== FALSE))
        {
            $string .= 'public $WIKINDX_MAX_WRITECHUNK = ' . $this->config->WIKINDX_MAX_WRITECHUNK . ';' . "\n";
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
        $string .= "\n" . '?>';


        // Save the old config file before writing it
        // Something could go wrong and configuration lost otherwise
        $cf = 'config.php';
        $bf = WIKINDX_DIR_DATA_FILES . DIRECTORY_SEPARATOR . $cf . '.' . date('YmdHis');
        if (copy($cf, $bf))
        {
            if (is_writable($cf))
            {
                if (file_put_contents($cf, $string) === FALSE)
                {
                    die("Fatal error: an error occurred when writing to $cf");
                }
            }
            else
            {
                die("Fatal error: $cf is not writable");
            }
        }
        else
        {
            die("Fatal error: could not backup $cf to $bf");
        }
    }
    /**
     * Write new config.php with upgrade to >= WIKINDX v5.9
     */
    private function writeConfigFile5_9()
    {
        $string = <<<END
<?php
/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
        $string .= 'public $WIKINDX_DB_HOST = "' . $this->config->WIKINDX_DB_HOST . '";' . "\n";
        $string .= '// name of the database which these scripts interface with (case-sensitive):' . "\n" .
                   'public $WIKINDX_DB = "' . $this->config->WIKINDX_DB . '";' . "\n";
        $string .= '// username and password required to connect to and open the database' . "\n" .
                   '// (it is strongly recommended that you change these default values):' . "\n" .
                   'public $WIKINDX_DB_USER = "' . $this->config->WIKINDX_DB_USER . '";' . "\n" .
                   'public $WIKINDX_DB_PASSWORD = "' . $this->config->WIKINDX_DB_PASSWORD . '";' . "\n";
        $string .= '// If using WIKINDX on a shared database, set the WIKINDX table prefix here (lowercase only)' . "\n" .
                   '// (do not change after running WIKINDX and creating the tables!).' . "\n" .
                   '// This option is deprecated since version 5.9.1 and will be removed in the next release.' . "\n" .
                   '// People who have changed the prefix should rename the tables with the default prefix (wkx_)' . "\n" .
                   '// and correct their configuration. It will no longer be possible to install two WIKINDXs' . "\n" .
                   '// in the same database. If you are in this rare case contact us.' . "\n" .
                   'public $WIKINDX_DB_TABLEPREFIX = "' . $this->config->WIKINDX_DB_TABLEPREFIX . '";' . "\n";
        $string .= '// WIKINDX uses MySQL persistent connections by default.' . "\n" .
                   '// Some hosting services are not configured for this: if you have problems' . "\n" .
                   "// connecting to your MySQL server and/or receive error messages about 'too many connections'," . "\n" .
                   '// set $WIKINDX_DB_PERSISTENT to FALSE' . "\n";
        '// see https://www.php.net/manual/en/mysqli.persistconns.php' . "\n";
        if ($this->config->WIKINDX_DB_PERSISTENT === TRUE)
        {
            $string .= 'public $WIKINDX_DB_PERSISTENT = TRUE;' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_DB_PERSISTENT = FALSE;' . "\n";
        }
        $string .= <<<END
/*****
* END DATABASE CONFIGURATION
*****/

/**********************************************************************************/

/*****
* START PATHS CONFIGURATION
*****/
// You must define the base URL for the WIKINDX installation.
// You have to indicate protocol HTTP / HTTPS and remove the terminal /.
// e.g. if wikindx's index.php file is in /wikindx/ under the httpd/ (or similar)
// folder on the www.myserver.com, then set the variable
// to http://www.myserver.com/wikindx

END;
        // We set it because we know best ;)
        $this->config->WIKINDX_BASE_URL = FACTORY_URL::getInstance()->getBaseUrl();
        $string .= 'public $WIKINDX_BASE_URL = "' . $this->config->WIKINDX_BASE_URL . '";' . "\n";

        $string .= <<<END
// The TinyMCE editor needs the WIKINDX server installation path.
// WIKINDX tries to get this through getcwd() but this is not always possible.
// In this case, you will receive an error message and WIKINDX will die and you should then set that path here.
// The path should be the full path from the root folder to your wikindx folder with no trailing '/'.
// On Apple OSX running XAMPP, for example, the case-sensitive path is:
// '/Applications/XAMPP/xamppfiles/htdocs/wikindx'.
// The script will continue to die until it has a valid installation path.
// If you get no error message and WIKINDX runs fine, then you can leave this value as FALSE.

END;
        if (property_exists($this->config, 'WIKINDX_WIKINDX_PATH') && ($this->config->WIKINDX_WIKINDX_PATH !== FALSE))
        {
            $string .= 'public $WIKINDX_WIKINDX_PATH = "' . $this->config->WIKINDX_WIKINDX_PATH . '";' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_WIKINDX_PATH = FALSE;' . "\n";
        }
        $string .= <<<END
/*****
* END PATHS CONFIGURATION
*****/

/**********************************************************************************/

/*****
* START PHP MEMORY AND EXECUTION CONFIGURATION
*****/
// WIKINDX usually runs with the standard PHP memory_limit of 32MB.
// With some PHP configurations, however, this is not enough -- a mysterious blank page is often the result.
// If you are unable to update php.ini's memory_limit yourself, WIKINDX_MEMORY_LIMIT may be set (an integer such as 64 or 128 followed by 'M').
// Despite the PHP manual stating that this may not be set outside of php.ini, it seems to work most of the time.
// It is not, however, guaranteed to do so and editing php.ini is the preferred method particularly if your PHP is in 'safe' mode.
// Use double quotes around the value.

END;
        if (property_exists($this->config, 'WIKINDX_MEMORY_LIMIT') && ($this->config->WIKINDX_MEMORY_LIMIT !== FALSE))
        {
            $string .= 'public $WIKINDX_MEMORY_LIMIT = "' . $this->config->WIKINDX_MEMORY_LIMIT . '";' . "\n";
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
        if (property_exists($this->config, 'WIKINDX_MAX_EXECUTION_TIMEOUT') && ($this->config->WIKINDX_MAX_EXECUTION_TIMEOUT !== FALSE))
        {
            $string .= 'public $WIKINDX_MAX_EXECUTION_TIMEOUT = ' . $this->config->WIKINDX_MAX_EXECUTION_TIMEOUT . ';' . "\n";
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
// As a rough guide, with a WIKINDX_MEMORY_LIMIT of 32M, WIKINDX_MAX_WRITECHUNK of 700 should work fine and with 64M, 1500 works fine.
// If WIKINDX_MAX_WRITECHUNK is FALSE, the chunk is set to 10,000.
// This can be a tricky figure to set as setting the figure too low increases SQL and PHP execution times significantly.
// Do NOT use quotes around the value.

END;
        if (property_exists($this->config, 'WIKINDX_MAX_WRITECHUNK') && ($this->config->WIKINDX_MAX_WRITECHUNK !== FALSE))
        {
            $string .= 'public $WIKINDX_MAX_WRITECHUNK = ' . $this->config->WIKINDX_MAX_WRITECHUNK . ';' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_MAX_WRITECHUNK = FALSE;' . "\n";
        }
        $string .= <<<END
// WIKINDX_TRUNK_VERSION boolean activates experimental features of the trunk version, development tools,
// and changes the link of the update server to use the components of this version in perpetual development.
// DO NOT ACTIVATE THIS OPTION IF YOU ARE NOT A CORE DEVELOPER. If you need to debug your installation,
// you will find suitable options in the administration screen.

END;
        if (property_exists($this->config, 'WIKINDX_TRUNK_VERSION') && ($this->config->WIKINDX_TRUNK_VERSION === TRUE))
        {
            $string .= 'public $WIKINDX_TRUNK_VERSION = TRUE;' . "\n";
        }
        else
        {
            $string .= 'public $WIKINDX_TRUNK_VERSION = FALSE;' . "\n";
        }
        $string .= <<<END
/*****
* END PHP MEMORY AND EXECUTION CONFIGURATION
*****/
}
END;
        $string .= "\n" . '?>';


        // Save the old config file before writing it
        // Something could go wrong and configuration lost otherwise
        $cf = 'config.php';
        $bf = WIKINDX_DIR_DATA_FILES . DIRECTORY_SEPARATOR . $cf . '.' . date('YmdHis');
        if (copy($cf, $bf))
        {
            if (is_writable($cf))
            {
                if (file_put_contents($cf, $string) === FALSE)
                {
                    die("Fatal error: an error occurred when writing to $cf");
                }
            }
            else
            {
                die("Fatal error: $cf is not writable");
            }
        }
        else
        {
            die("Fatal error: could not backup $cf to $bf");
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
        // If there is an existing papers table (from wikindx v3.8.x), copy fields across and drop table
        if (array_search('papers', $tables) !== FALSE)
        {
            if (array_search('plugin_wordprocessor', $tables) === FALSE)
            {
                $this->db->queryNoError("
					CREATE TABLE `" . $this->config->WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor` (
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
            $this->db->queryNoError("DROP TABLE IF EXISTS " . $this->config->WIKINDX_DB_TABLEPREFIX . "papers;");
        }
        elseif (array_search('plugin_wordprocessor', $tables) !== FALSE)
        {
            $this->db->queryNoError("ALTER TABLE " . $this->config->WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor ENGINE=InnoDB;");
            $this->db->queryNoError("ALTER TABLE " . $this->config->WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;");
            $this->db->queryNoError("ALTER TABLE " . $this->config->WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor MODIFY COLUMN `pluginwordprocessorHashFilename` varchar(1020) DEFAULT NULL;");
            $this->db->queryNoError("ALTER TABLE " . $this->config->WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor MODIFY COLUMN `pluginwordprocessorFilename` varchar(1020) DEFAULT NULL;");
        }
        if (array_search('plugin_soundexplorer', $tables) !== FALSE)
        {
            $this->db->queryNoError("ALTER TABLE " . $this->config->WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer RENAME `" . $this->config->WIKINDX_DB_TABLEPREFIX . "4fc387ba1ae34ac28e6dee712679d7b5`");
            $this->db->queryNoError("ALTER TABLE " . $this->config->WIKINDX_DB_TABLEPREFIX . "4fc387ba1ae34ac28e6dee712679d7b5 RENAME `" . $this->config->WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer`");
            $this->db->queryNoError("ALTER TABLE " . $this->config->WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer ENGINE=InnoDB;");
            $this->db->queryNoError("ALTER TABLE " . $this->config->WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;");
            $this->db->queryNoError("ALTER TABLE " . $this->config->WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer MODIFY COLUMN `pluginsoundexplorerLabel` varchar(1020) DEFAULT NOT NULL;");
            $this->db->queryNoError("ALTER TABLE " . $this->config->WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer MODIFY COLUMN `pluginsoundexplorerArray` text DEFAULT NOT NULL;");
        }
    }
    /**
     * Alter resourcecreator::creatorSurname field to allow for more accurate ordering
     *
     * @param int $maxCounts
     */
    private function alterCreatorSurname($maxCounts)
    {
        $count = 0;
        $updateArray = [];
        $this->db->formatConditions($this->db->formatFields('resourcecreatorCreatorSurname') . ' IS NOT NULL');
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorMain');
        $resultset = $this->db->select('resource_creator', ['creatorSurname', 'resourcecreatorId']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $count++;
            // remove all punctuation (keep apostrophe and dash for names such as Grimshaw-Aagaard and D'Eath)
            $updateArray[$row['resourcecreatorId']] = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s\-\']/u', '', $row['creatorSurname']));

            if ($count >= $maxCounts)
            {
                $this->db->multiUpdate('resource_creator', 'resourcecreatorCreatorSurname', 'resourcecreatorId', $updateArray);
                $updateArray = [];
                $count = 0;
            }
        }
    }
    /**
     * Strip BBCode from users table
     *
     * @param int $maxCounts
     */
    private function stripBBCode($maxCounts)
    {
        include_once("core/display/BBCODE.php");
        $fields = ['usersFullname', 'usersDepartment', 'usersInstitution'];
        foreach ($fields as $field)
        {
            $count = 0;
            $updateArray = [];
            $this->db->formatConditions($this->db->formatFields($field) . ' IS NOT NULL');
            $this->db->formatConditions($this->db->formatFields($field) . $this->db->like('%', '\\[', '%'));
            $resultset = $this->db->select('users', ['usersId', $field]);
            while ($row = $this->db->fetchRow($resultset))
            {
                $count++;
                $updateArray[$row['usersId']] = BBCODE::stripBBCode($row[$field]);

                if ($count >= $maxCounts)
                {
                    $this->db->multiUpdate('users', 'usersId', $field, $updateArray);
                    $updateArray = [];
                    $count = 0;
                }
            }
        }
    }
    /**
     * Set collectionDefault column in the collections table
     */
    private function collectionDefaults()
    {
        include_once("core/collection/COLLECTIONDEFAULTMAP.php");
        $defaultMap = new COLLECTIONDEFAULTMAP();
        $typesArray = array_unique(array_values($defaultMap->collectionTypes));
        $collectionArray = [];
        $maxPacket = $this->db->getMaxPacket();
        // For each 1MB max_allowed_packet (1048576 bytes), 600 updates in one go seems fine as a value for $maxCounts (based on trial and error)
        $maxCounts = floor(600 * ($maxPacket / 1048576));
        foreach ($typesArray as $type)
        {
            $fieldNames = [];
            foreach ($defaultMap->{$type} as $typeKey => $typeKeyArray)
            {
                $typeKey = str_replace('_', '', $typeKey);
                if (($typeKey == 'resource') && !empty($typeKeyArray))
                {
                    $this->db->leftJoin('resource', 'resourceId', 'resourcemiscId');
                    foreach ($typeKeyArray as $key => $value)
                    {
                        $fieldName = $typeKey . $key;
                        $fieldNames[] = $fieldName;
                    }
                }
                elseif (($typeKey == 'resourcemisc') && !empty($typeKeyArray))
                {
                    foreach ($typeKeyArray as $key => $value)
                    {
                        $fieldName = $typeKey . $key;
                        $fieldNames[] = $fieldName;
                    }
                }
                elseif (($typeKey == 'resourceyear') && !empty($typeKeyArray))
                {
                    $this->db->leftJoin('resource_year', 'resourceyearId', 'resourcemiscId');
                    foreach ($typeKeyArray as $key => $value)
                    {
                        $fieldName = $typeKey . $key;
                        $fieldNames[] = $fieldName;
                    }
                }
            }
            if (empty($fieldNames))
            {
                continue;
            }
            $fieldNames[] = 'collectionId';
            $fieldNames[] = 'resourcemiscId';
            $this->db->formatConditions(['resourcemiscCollection' => ' IS NOT NULL']);
            $this->db->formatConditions(['collectionType' => $type]);
            $this->db->leftJoin('collection', 'collectionId', 'resourcemiscCollection');
            $resultset = $this->db->select('resource_misc', $fieldNames, TRUE);
            while ($row = $this->db->fetchRow($resultset))
            {
                foreach ($fieldNames as $fieldName)
                {
                    if (($fieldName == 'collectionId') || ($fieldName == 'resourcemiscId'))
                    {
                        continue;
                    }
                    if (
                        !array_key_exists($row['collectionId'], $collectionArray)
                        ||
                        (
                            array_key_exists($row['collectionId'], $collectionArray)
                            && (!array_key_exists($fieldName, $collectionArray[$row['collectionId']]))
                        )
                    ) {
                        if ($row[$fieldName])
                        {
                            $collectionArray[$row['collectionId']][$fieldName] = $row[$fieldName];
                        }
                    }
                }
                if (array_key_exists('resource_creator', $defaultMap->{$type}) && !empty($defaultMap->{$type}['resource_creator']))
                {
                    $creators = [];
                    $roles = array_keys($defaultMap->{$type}['resource_creator']);
                    $this->db->formatConditions(['resourcecreatorResourceId' => $row['resourcemiscId']]);
                    $this->db->formatConditionsOneField($roles, 'resourcecreatorRole');
                    $this->db->orderBy('resourcecreatorOrder', TRUE, FALSE);
                    $resultsetC = $this->db->select('resource_creator', ['resourcecreatorCreatorId', 'resourcecreatorRole', 'resourcecreatorOrder']);
                    while ($rowC = $this->db->fetchRow($resultsetC))
                    {
                        $order = $rowC['resourcecreatorOrder'] - 1;
                        $creators['Creator' . $rowC['resourcecreatorRole'] . '_' . $order . '_select'] = $rowC['resourcecreatorCreatorId'];
                    }
                    if (!empty($creators))
                    {
                        $collectionArray[$row['collectionId']]['creators'] = $creators;
                    }
                }
            }
            if (!empty($collectionArray))
            {
                $count = 0;
                $updateArray = [];
                foreach ($collectionArray as $collectionId => $array)
                {
                    ++$count;
                    $updateArray[$collectionId] = base64_encode(serialize($array));
                    if ($count >= $maxCounts)
                    {
                        $this->db->multiUpdate('collection', 'collectiondefault', 'collectionId', $updateArray);
                        $updateArray = [];
                        $count = 0;
                    }
                }
                if (!empty($updateArray))
                { // do the remainder
                    $this->db->multiUpdate('collection', 'collectiondefault', 'collectionId', $updateArray);
                }
            }
        }
    }
    /**
     * Fix bad UTF8
     */
    private function fixUTF8()
    {
        include_once("core/utf8/encoding.php");

        // The following only have timestamps, numeric values or preset values such as 'N', 'Y' etc.
        $tableFilter = [
            'cache',
            'database_summary',
            'resource_category',
            'resource_keyword',
            'resource_language',
            'resource_misc',
            'resource_summary',
            'resource_timestamp',
            'statistics',
            'user_bibliography_resource',
            'user_groups_users',
        ];
        $textFieldFilter = ['char', 'longtext', 'mediumtext', 'text', 'tinytext', 'varchar'];

        $tables = $this->db->listTables(FALSE);
        $tables = array_intersect($tables, $tableFilter);
        foreach ($tables as $table)
        {
            $selectedFields = [];

            $convType = 'lightFixutf8';
            // Need to select text fields for conversion
            $fInfo = $this->db->getFieldsProperties($table);
            foreach ($fInfo as $val)
            {
                if (in_array($val['DATA_TYPE'], $textFieldFilter))
                {
                    $selectedFields[] = $val['COLUMN_NAME'];
                }
            }

            // Never convert the password of users
            if ($table == 'users')
            {
                $selectedFields = array_diff($selectedFields, ['usersPassword']);
            }

            if (count($selectedFields) > 0)
            {
                $resultset = $this->db->select($table, $selectedFields);
                while ($row = $this->db->fetchRow($resultset))
                {
                    $id = str_replace('_', '', $table) . 'Id';
                    $updateArray = [];
                    foreach ($row as $field => $value)
                    {
                        if (!$value || is_numeric($value))
                        {
                            continue;
                        }
                        $value = stripslashes($value);
                        $original = $value;
                        if ($convType == 'lightFixutf8')
                        {
                            $value = Encoding::toUTF8($value);
                            if ($original != $value)
                            {
                                $updateArray[$field] = $value;
                            }
                        }
                        elseif ($convType == 'toughFixutf8')
                        {
                            $value = Encoding::fixUTF8($value);
                            if ($original != $value)
                            {
                                $updateArray[$field] = $value;
                            }
                        }
                    }
                    if (empty($updateArray))
                    {
                        continue;
                    }
                    if (($table != 'config'))
                    {
                        $this->db->formatConditions([$id => $row[$id]]);
                    }
                    $this->db->update($table, $updateArray);
                }
            }
        }
        $this->db->updateNull('cache', 'cacheResourceCreators');
        $this->db->updateNull('cache', 'cacheMetadataCreators');
        $this->db->updateNull('cache', 'cacheKeywords');
        $this->db->updateNull('cache', 'cacheResourceKeywords');
        $this->db->updateNull('cache', 'cacheMetadataKeywords');
        $this->db->updateNull('cache', 'cacheQuoteKeywords');
        $this->db->updateNull('cache', 'cacheParaphraseKeywords');
        $this->db->updateNull('cache', 'cacheMusingKeywords');
        $this->db->updateNull('cache', 'cacheResourcePublishers');
        $this->db->updateNull('cache', 'cacheMetadataPublishers');
        $this->db->updateNull('cache', 'cacheConferenceOrganisers');
        $this->db->updateNull('cache', 'cacheResourceCollections');
        $this->db->updateNull('cache', 'cacheMetadataCollections');
        $this->db->updateNull('cache', 'cacheResourceCollectionTitles');
        $this->db->updateNull('cache', 'cacheResourceCollectionShorts');
    }
    /**
     * To get total counts right in list operations, certain tables, having their ID columns the same as resource.resourceId,
     * must have the same number of rows as the resource table.
     */
    private function addMissingRows()
    {
        // resource_year
        $resultset = $this->db->query('SELECT `resourceId` FROM `' . $this->config->WIKINDX_DB_TABLEPREFIX . 'resource`
			WHERE `resourceId` NOT IN (SELECT `resourceyearId`
			FROM `' . $this->config->WIKINDX_DB_TABLEPREFIX . 'resource_year`)');
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->insert('resource_year', 'resourceyearId', $row['resourceId']);
        }
        // resource_misc
        $resultset = $this->db->query('SELECT `resourceId` FROM `' . $this->config->WIKINDX_DB_TABLEPREFIX . 'resource`
			WHERE `resourceId` NOT IN (SELECT `resourcemiscId`
			FROM `' . $this->config->WIKINDX_DB_TABLEPREFIX . 'resource_misc`)');
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->insert('resource_misc', 'resourcemiscId', $row['resourceId']);
        }
        // resource_timestamp
        $resultset = $this->db->query('SELECT `resourceId` FROM `' . $this->config->WIKINDX_DB_TABLEPREFIX . 'resource`
			WHERE `resourceId` NOT IN (SELECT `resourcetimestampId`
			FROM `' . $this->config->WIKINDX_DB_TABLEPREFIX . 'resource_timestamp`)');
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->insert('resource_timestamp', 'resourcetimestampId', $row['resourceId']);
        }
    }
    /**
     * If required to pause execution, store current position and any $tableArray arrays in session and present continuation form to user
     *
     * @param string $finished
     * @param string $function Default is ""
     * @param string $table Default is FALSE
     */
    private function pauseExecution($finished, $function = "", $table = FALSE)
    {
        // Store data

        if ($function != "")
        {
            $this->session->setVar('upgrade_function', $function);
        }
        if ($table)
        {
            $this->session->setVar('upgrade_table', $table);
        }
        $tables = $this->db->listTables(TRUE);
        foreach ($tables as $table)
        {
            $table .= 'Array';
            if (isset($this->{$table}))
            {
                $tableArrays[$table] = $this->{$table};
            }
        }
        if (isset($tableArrays))
        {
            $this->session->setVar('upgrade_tableArrays', base64_encode(serialize($tableArrays)));
        }
        // Print form and die
        //		$pString = "php.ini's max_execution time (" . ini_get("max_execution_time") . " seconds) was about
        //			to be exceeded.  Please click on the button to continue the upgrade.&nbsp;&nbsp;Do <strong>not</strong> click
        //			until each script has finished.";
        $pString = \HTML\p(\HTML\strong($this->installMessages->text("upgradeDBHeading")));
        $pString .= \HTML\p($this->statusString);
        if ($this->stageInterruptMessage)
        {
            $pString .= \HTML\p($this->stageInterruptMessage);
        }
        else
        {
            $pString .= \HTML\p("`$finished` of " . $this->numStages .
            " finished.  Please click on the button to continue the upgrade.&nbsp;&nbsp;Do <b>not</b> click
			until each script has finished.");
        }
        $pString .= \FORM\formHeader('continueExecution');
        if ($function == 'stage4_1__15')
        {
            $pString .= \HTML\p($this->installMessages->text("upgradeFixUTF8-1"));
            $pString .= \HTML\p($this->installMessages->text("upgradeFixUTF8-2") . '&nbsp;&nbsp;' . \FORM\checkBox(FALSE, 'fixUTF8'));
        }
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Continue")) . \FORM\formEnd());
        $this->close($pString);
    }
    /**
     * Continue execution of upgrade after a pause
     */
    private function continueExecution()
    {
        $function = $this->session->getVar('upgrade_function');
        $tableArrays = $this->session->getVar('upgrade_tableArrays');
        if ($tableArrays)
        {
            $tableArrays = unserialize(base64_decode($tableArrays));
            foreach ($tableArrays as $table => $array)
            {
                $this->{$table} = $array;
            }
        }
        $table = $this->session->getVar('upgrade_table');
        $this->session->clearArray('upgrade');
        if ($function == 'upgrade40charToBin')
        {
            if ($table)
            {
                $this->upgrade40charToBin($table);
            }
            else
            {
                $this->upgrade40charToBin();
            }
            $this->upgrade40Nulls();
            $this->upgrade40Tables();
        }
        elseif ($function == 'upgrade40Tables')
        {
            if ($table)
            {
                $this->upgrade40Tables($table);
            }
            else
            {
                $this->upgrade40Tables();
            }
        }
    }
    /**
     * Update VARCHARS to account for increased size of UTF8 fields (3* space required) and then temp and change char types to binary types on some tables
     *
     * @param string $table Default is FALSE
     */
    private function upgrade40charToBin($table = FALSE)
    {
        $tables = $this->db->listTables(FALSE);
        foreach ($tables as $basicTable)
        {
            $change = $varchar = [];
            $table = "`" . $this->config->WIKINDX_DB_TABLEPREFIX . $basicTable . "`";
            $fields = [];
            $idField = "null";
            $recordset = $this->db->query("SHOW COLUMNS FROM $table");
            while ($row = $this->db->fetchRow($recordset))
            {
                if (($row['Type'] == 'varchar(10)') || $row['Type'] == 'varchar(30)')
                {
                    $fields[] = $row['Field'];
                    $varchar[] = '`' . $row['Field'] . '` `' . $row['Field'] . '` varchar(255)';
                }
                elseif ($row['Type'] == 'varchar(255)')
                {
                    $fields[] = $row['Field'];
                }
                elseif ($row['Type'] == 'mediumtext')
                {
                    $fields[] = $row['Field'];
                    $varchar[] = '`' . $row['Field'] . '` `' . $row['Field'] . '` text';
                }
                elseif ($row['Type'] == 'text')
                {
                    $fields[] = $row['Field'];
                }
                
                if ($row['Type'] == 'int(11)' && $row['Extra'] == 'auto_increment')
                {
                    $idField = $row['Field'];
                }
            }
            if (!empty($fields))
            {
                $this->upgrade40CheckForValidUtf8($basicTable, $fields, $idField);
            }
            // reset database conditions, join etc (upgrade40CheckForValidUtf8) may not have executed SQL query but condition still set
            $this->db->resetSubs();
            if (!empty($varchar))
            {
                foreach ($varchar as $new)
                {
                    $change[] = ' CHANGE ' . $new;
                }
                $this->db->query('ALTER TABLE ' . $table . implode(',', $change));
            }
        }
        $tables = ['resource_attachments', 'category', 'collection', 'config', 'creator',
            'custom', 'keyword', 'news', 'papers', 'publisher', 'resource',
            'resource_custom', 'resource_musing', 'resource_musing_text', 'resource_text', 'resource_page',
            'resource_paraphrase', 'resource_paraphrase_comment', 'resource_paraphrase_text',
            'resource_quote', 'resource_quote_comment', 'resource_quote_text', 'resource_year', 'tag',
            'users', 'user_bibliography', 'user_groups', 'user_register',
            'user_tags', 'resource_user_tags', 'subcategory', 'statistics', ];
        foreach ($tables as $basicTable)
        {
            $change = $varchar = [];
            $table = '`' . $this->config->WIKINDX_DB_TABLEPREFIX . "$basicTable`";
            $recordset = $this->db->query("SHOW COLUMNS FROM $table");
            while ($row = $this->db->fetchRow($recordset))
            {
                if (($row['Type'] == 'varchar(255)'))
                {
                    $varchar[] = '`' . $row['Field'] . '` `' . $row['Field'] . '` varbinary(255)';
                }
                elseif ($row['Type'] == 'text')
                {
                    $varchar[] = '`' . $row['Field'] . '` `' . $row['Field'] . '` blob';
                }
            }
            if (!empty($varchar))
            {
                foreach ($varchar as $new)
                {
                    $change[] = ' CHANGE ' . $new;
                }
            }
            $this->db->query('ALTER TABLE ' . $table . implode(',', $change));
        }
    }
    /**
     * Check for valid UTF-8.
     *
     * If invalid code found, store value in array for updating to database after UTF8 upgrade
     *
     * @param string $basicTable
     * @param array $fields
     * @param string $idField
     */
    private function upgrade40CheckForValidUtf8($basicTable, $fields, $idField = "null")
    {
        if (($basicTable == 'cache') || ($basicTable == 'database_summary') ||
            ($basicTable == 'resource_keyword') || ($basicTable == 'resource_category') ||
            ($basicTable == 'resource_creator')
        ) {
            return;
        }
        $resultset = $this->db->select($basicTable, $fields);
        $tableArray = $basicTable . 'Array';
        $this->{$tableArray}["idField"] = $idField;
        while ($row = $this->db->fetchRow($resultset))
        {
            foreach ($fields as $field)
            {
                if (($basicTable != 'config') && ($field == $idField))
                {
                    continue;
                }
                $encoding = mb_detect_encoding($row[$field]);
                if (($encoding != 'ASCII') && ($encoding != 'UTF-8'))
                {
                    if (!$encoding)
                    { // no idea what this is, so store for re-writing after upgrade later
                        $value = $row[$field];
                    }
                    else
                    {
                        if (!$value = iconv($encoding, 'utf-8', $row[$field]))
                        {
                            $value = $row[$field];
                        }
                    }
                    if ($idField == 'null')
                    {
                        $this->{$tableArray}[][$field] = $value;
                    }
                    else
                    {
                        $this->{$tableArray}[$row[$idField]][$field] = $value;
                    }
                }
            }
        }
    }
    /**
     * Remove user's sessions and caches
     */
    private function upgrade40Nulls()
    {
        // Remove any existing user session
        $this->db->updateNull('users', 'userSession');
        // Remove cache fields so that v4's UTF8 handling and ordering of creators, keywords etc. is handled properly
        $this->db->updateNull('cache', 'resourceCreators');
        $this->db->updateNull('cache', 'metadataCreators');
        $this->db->updateNull('cache', 'resourceKeywords');
        $this->db->updateNull('cache', 'metadataKeywords');
        $this->db->updateNull('cache', 'quoteKeywords');
        $this->db->updateNull('cache', 'paraphraseKeywords');
        $this->db->updateNull('cache', 'musingKeywords');
        $this->db->updateNull('cache', 'resourcePublishers');
        $this->db->updateNull('cache', 'metadataPublishers');
        $this->db->updateNull('cache', 'conferenceOrganisers');
        $this->db->updateNull('cache', 'resourceCollections');
        $this->db->updateNull('cache', 'metadataCollections');
        $this->db->updateNull('cache', 'resourceCollectionTitles');
        $this->db->updateNull('cache', 'resourceCollectionShorts');
    }
    /**
     * recreate the 4.0 cache
     */
    private function recreate40Cache()
    {
        $keyword = FACTORY_KEYWORD::getInstance();
        $creator = FACTORY_CREATOR::getInstance();
        $publisher = FACTORY_PUBLISHER::getInstance();
        $collection = FACTORY_COLLECTION::getInstance();
        $creator->grabAll();
        $creator->grabAll(FALSE, FALSE, TRUE);  // creators in resources with metadata
        $keyword->grabAll(); // default resource_keyword table
        $keyword->grabAll(FALSE, 'resource');
        $keyword->grabAll(FALSE, 'quote');
        $keyword->grabAll(FALSE, 'paraphrase');
        $keyword->grabAll(FALSE, 'musing');
        $publisher->grabAll();
        $publisher->grabAll(FALSE, FALSE, FALSE, TRUE); // publishers in resources with metadata
        $collection->grabAll();
        $collection->grabAll(FALSE, FALSE, FALSE, TRUE); // collections in resources with metadata
    }
    /**
     * Upgrade tables to UTF-8 then reverse field type change for some tables from binary back to var
     *
     * @param string $tableContinue Default is FALSE
     */
    private function upgrade40Tables($tableContinue = FALSE)
    {
        $tables = $this->db->listTables(TRUE);
        foreach ($tables as $table)
        {
            $this->db->query('ALTER TABLE `' . $table . '` CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci');
        }
        $tables = [
            'resource_attachments', 'category', 'collection', 'config', 'creator',
            'custom', 'keyword', 'news', 'papers', 'publisher', 'resource',
            'resource_custom', 'resource_musing', 'resource_musing_text', 'resource_text', 'resource_page',
            'resource_paraphrase', 'resource_paraphrase_comment', 'resource_paraphrase_text',
            'resource_quote', 'resource_quote_comment', 'resource_quote_text', 'resource_year', 'tag', 'statistics',
            'users', 'user_bibliography', 'user_groups', 'user_register', 'user_tags', 'resource_user_tags', 'subcategory',
        ];
        foreach ($tables as $basicTable)
        {
            $change = $varchar = [];
            $table = '`' . $this->config->WIKINDX_DB_TABLEPREFIX . "$basicTable`";
            $recordset = $this->db->query("SHOW COLUMNS FROM $table");
            while ($row = $this->db->fetchRow($recordset))
            {
                if (($row['Type'] == 'varbinary(255)'))
                {
                    $varchar[] = '`' . $row['Field'] . '` `' . $row['Field'] . '` varchar(255)';
                }
                elseif ($row['Type'] == 'blob')
                {
                    $varchar[] = '`' . $row['Field'] . '` `' . $row['Field'] . '` text';
                }
            }
            if (!empty($varchar))
            {
                foreach ($varchar as $new)
                {
                    $change[] = ' CHANGE ' . $new;
                }
            }
            $this->db->query('ALTER TABLE ' . $table . implode(',', $change));
            $tableArray = $basicTable . 'Array';
            if (isset($this->{$tableArray}))
            {
                foreach ($this->{$tableArray} as $id => $array)
                {
                    $updateArray = [];
                    if ($this->{$tableArray}["idField"] != 'null')
                    {
                        $this->db->formatConditions([$this->{$tableArray}["idField"] => $id]);
                    }
                    foreach ($array as $key => $basicTable)
                    {
                        $updateArray[$key] = $basicTable;
                    }
                    if (!empty($updateArray))
                    {
                        $this->db->update($basicTable, $updateArray);
                    }
                }
            }
        }
    }
    /**
     * Only the superadmin may update the database -- ask for login
     *
     * @param string $currentdbVersion
     */
    private function confirmUpdateDisplay($currentdbVersion)
    {
        $pString = \HTML\p(\HTML\strong($this->installMessages->text("upgradeDBHeading")));

        $vars = GLOBALS::getVars();
        $vars['username'] = isset($vars['username']) ? $vars['username'] : '';
        $vars['password'] = isset($vars['password']) ? $vars['password'] : '';
        if (\UPDATE\logonCheckUpgradeDB($this->db, $vars['username'], $vars['password'], $currentdbVersion))
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
            
            $pString .= \HTML\p($this->messages->text("authorize", "logonSuperadmin"));
            
            $pString .= \FORM\formHeader("upgradeDBLogon");
            $pString .= \HTML\tableStart('left width50percent');
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->messages->text("user", "username") . ":&nbsp;&nbsp;");
            $pString .= \HTML\td(\FORM\textInput(FALSE, "username"));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->messages->text("user", "password") . ":&nbsp;&nbsp;");
            $pString .= \HTML\td(\FORM\passwordInput(FALSE, "password"));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td("&nbsp;");
            $pString .= \HTML\td(\FORM\formSubmit($this->messages->text("submit", "Submit")), 'right');
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \FORM\formEnd();
        }
        $this->close($pString);
    }
    /**
     * Special CLOSE function for pre v4.0 databases
     *
     * @param string $pString
     */
    private function close($pString)
    {
        $styledir = str_replace("\\", "/", WIKINDX_DIR_COMPONENT_TEMPLATES) . "/" . WIKINDX_TEMPLATE_DEFAULT;
        $string = <<<END
<!DOCTYPE html>
<html>
<head>
	<title>WIKINDX Upgrade</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="$styledir/template.css" type="text/css">
	<link rel="shortcut icon" type="image/x-icon" href="$styledir/images/favicon.ico">
</head>
<body>
	$pString
</body>
</html>
END;
        echo $string;
        ob_end_flush();
        die;
    }
}
