<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * repairkit class.
 *
 * A number of database repair operations
 */

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");

class repairkit_MODULE
{
    public $authorize;
    public $menus;
    private $db;
    private $vars;
    private $pluginmessages;
    private $dbInconsistenciesReport = [];
    private $dbFieldInconsistenciesFix = [];
    private $dbTableInconsistenciesFix = [];
    private $dbKeyInconsistenciesFix = [];
    private $dbIndexInconsistenciesFix = [];
    private $dbMissingTables = [];
    private $dbMissingRows = [];
    private $dbCurrentIndexPrefix = [];
    private $dbInvalidDatetime = FALSE;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        include_once("core/messages/PLUGINMESSAGES.php");
        $this->pluginmessages = new PLUGINMESSAGES('repairkit', 'repairkitMessages');
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "config.php");
        $config = new repairkit_CONFIG();
        $this->authorize = $config->authorize;
        if ($menuInit)
        { // portion of constructor used for menu initialisation
            $this->makeMenu($config->menus);

            return; // Need do nothing more as this is simply menu initialisation.
        }
        $this->session = FACTORY_SESSION::getInstance();
        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        $this->vars = GLOBALS::getVars();
        $this->db = FACTORY_DB::getInstance();
        include_once("core/utf8/encoding.php");
    }
    /**
     * dbIntegrityInit
     */
    public function dbIntegrityInit()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingDbIntegrity'));
        $wVersion = WIKINDX_INTERNAL_VERSION;
        $dbVersion = $this->db->selectFirstField('database_summary', 'databasesummaryDbVersion');
        if (floatval($dbVersion) != floatval($wVersion))
        { // Shouldn't ever happen if UPDATEDATABASE is functioning correctly . . .
            $pString = HTML\p($this->pluginmessages->text('dbIntegrityPreamble1a', $dbVersion) . '&nbsp;' . $this->pluginmessages->text('dbIntegrityPreamble1b', $wVersion));
            GLOBALS::addTplVar('content', $pString);

            return;
        }
        $currentDbSchema = $this->db->createRepairKitDbSchema();
        $correctDbSchema = $this->db->getRepairKitDbSchema(WIKINDX_FILE_REPAIRKIT_DB_SCHEMA);
        if ($correctDbSchema === FALSE)
        {
            $pString = HTML\p($this->pluginmessages->text('fileReadError'), 'error');
            GLOBALS::addTplVar('content', $pString);

            return;
        }
        if ($this->checkDatetime())
        {
            $this->dbInvalidDatetime = TRUE;
        }
        if (($this->dbIntegrityReport($currentDbSchema, $correctDbSchema) === TRUE) && !$this->dbInvalidDatetime)
        {
            $pString = HTML\p($this->pluginmessages->text('dbIntegrityPreamble2'));
            GLOBALS::addTplVar('content', $pString);

            return;
        }
        else
        { // Structure needs fixing – can it be?
            if (!empty($this->dbMissingTables))
            { // Cannot be fixed
                $pString = HTML\p($this->pluginmessages->text('dbIntegrityMissingTables') . BR . implode(BR, $this->dbMissingTables));
                GLOBALS::addTplVar('content', $pString);

                return;
            }
            if (!empty($this->dbMissingFields))
            { // Cannot be fixed
                $missingFields = '';
                foreach ($this->dbMissingFields as $table)
                { // [0] == table, [1] == field
                    $missingFields .= "TABLE " . $table[0] . ": " . $table[1] . BR;
                }
                $pString = HTML\p($this->pluginmessages->text('dbIntegrityMissingFields') . BR . $missingFields);
                GLOBALS::addTplVar('content', $pString);

                return;
            }
            // Database can be fixed
            $pString = HTML\p($this->pluginmessages->text('dbIntegrityPreamble3', $wVersion));
            $pString .= FORM\formHeader("repairkit_dbIntegrityFix");
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "OK")));
            $pString .= FORM\formEnd();
            $pString .= HTML\hr();
            if ($this->dbInvalidDatetime)
            {
                $pString .= HTML\p($this->pluginmessages->text('dbIntegrityInvalidDatetime'));
            }
            foreach ($this->dbInconsistenciesReport as $table)
            {
                $pString .= HTML\p(implode('', $table));
            }
        }
        GLOBALS::addTplVar('content', $pString);

        return;
    }
    /**
     * dbIntegrityFix
     */
    public function dbIntegrityFix()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingDbIntegrity'));
        $config = FACTORY_CONFIG::getInstance();
        $currentDbSchema = $this->db->createRepairKitDbSchema();
        $correctDbSchema = $this->db->getRepairKitDbSchema(WIKINDX_FILE_REPAIRKIT_DB_SCHEMA);
        if ($correctDbSchema === FALSE)
        {
            $pString = HTML\p($this->pluginmessages->text('fileReadError'), 'error');
            GLOBALS::addTplVar('content', $pString);

            return;
        }
        foreach ($correctDbSchema as $table => $tableArray)
        {
            if (!array_key_exists($table, $currentDbSchema))
            {	// skip missing tables
                continue;
            }
            $this->getDbInconsistencies($currentDbSchema, $tableArray, $table);
        }
        if (empty($this->dbTableInconsistenciesFix) && empty($this->dbFieldInconsistenciesFix) &&
            empty($this->dbKeyInconsistenciesFix) && empty($this->dbIndexInconsistenciesFix))
        {
            $pString = HTML\p($this->pluginmessages->text('dbIntegrityPreamble2')); // nothing to fix
            GLOBALS::addTplVar('content', $pString);

            return;
        }
        if ($this->checkDatetime())
        { // fix invalid datetime fields first or else the charset/collation might fail.
            $this->fixDatetimeFields();
        }

        // Remove wrong indices before others field changes.
        // A length change on (var)char field can raise an error about
        // index length limits and prevent all other corrections.
        foreach ($this->dbKeyInconsistenciesFix as $table => $fields)
        {
            if (array_key_exists($table, $correctDbSchema))
            {
                foreach ($correctDbSchema[$table]['fields'][0] as $correctField)
                {
                    if (array_search($correctField['Field'], $fields) !== FALSE)
                    {
                        $fieldName = $correctField['Field'];
                        if (!$correctField['Key'])
                        {
                            $this->db->query("DROP INDEX `$fieldName`" . " ON `" . $config->WIKINDX_DB_TABLEPREFIX . "$table`");
                        }
                    }
                }
            }
        }
        foreach ($this->dbIndexInconsistenciesFix as $table => $fields)
        {
            foreach ($fields as $parts)
            {
                $keyName = $parts['Key_name'];
                //				$columnName = $parts['Column_name'];
                //				$subPart = $parts['Sub_part'] ? '(' . $parts['Sub_part'] . ')' : FALSE;
                // quietly drop any existing indices before adding them anew with the correct configuration
                $this->db->queryNoError("DROP INDEX `$keyName`" . " ON `" . $config->WIKINDX_DB_TABLEPREFIX . "$table`");
            }
        }
        foreach ($this->dbTableInconsistenciesFix as $index => $tables)
        {
            if ($index == 'collation')
            {
                foreach ($tables as $table)
                {
                    $this->db->query("ALTER TABLE `" . $config->WIKINDX_DB_TABLEPREFIX . "$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci");
                }
            }
            elseif ($index == 'engine')
            {
                foreach ($tables as $table)
                {
                    $this->db->query("ALTER TABLE `" . $config->WIKINDX_DB_TABLEPREFIX . "$table` ENGINE=InnoDB");
                }
            }
        }
        foreach ($this->dbFieldInconsistenciesFix as $table => $fields)
        {
            if (array_key_exists($table, $correctDbSchema))
            {
                foreach ($correctDbSchema[$table]['fields'][0] as $correctField)
                {
                    if (array_search($correctField['Field'], $fields) !== FALSE)
                    {
                        $fieldName = $correctField['Field'];
                        $type = $correctField['Type'];
                        if (!$correctField['Default'])
                        {
                            $default = 'DEFAULT';
                        }
                        else
                        {
                            if ($type == 'datetime')
                            {
                                $default = "DEFAULT " . $correctField['Default'];
                            }
                            else
                            {
                                $default = "DEFAULT '" . $correctField['Default'] . "'";
                            }
                        }
                        if ($correctField['Null'] == 'NO')
                        {
                            $this->db->query("ALTER TABLE `" . $config->WIKINDX_DB_TABLEPREFIX . "$table` MODIFY COLUMN `$fieldName` $type NOT NULL");
                        }
                        else
                        {
                            $this->db->query("ALTER TABLE `" . $config->WIKINDX_DB_TABLEPREFIX . "$table` MODIFY COLUMN `$fieldName` $type $default NULL");
                        }
                    }
                }
            }
        }
        // Recreate right indices
        foreach ($this->dbKeyInconsistenciesFix as $table => $fields)
        {
            if (array_key_exists($table, $correctDbSchema))
            {
                foreach ($correctDbSchema[$table]['fields'][0] as $correctField)
                {
                    if (array_search($correctField['Field'], $fields) !== FALSE)
                    {
                        $fieldName = $correctField['Field'];
                        if (!$correctField['Key'])
                        {
                            //Don't drop indices twice
                            continue;
                        }
                        elseif ($correctField['Key'] == 'MUL')
                        {
                            $this->db->query("CREATE INDEX `$fieldName`" . " ON `" . $config->WIKINDX_DB_TABLEPREFIX . "$table` (`$fieldName`)");
                        }
                        elseif ($correctField['Key'] == 'PRI')
                        { // Primary key
                            $this->db->query("ALTER TABLE `" . $config->WIKINDX_DB_TABLEPREFIX . "$table` MODIFY `$fieldName` INT(11) PRIMARY KEY AUTO_INCREMENT");
                        }
                    }
                }
            }
        }
        foreach ($this->dbIndexInconsistenciesFix as $table => $fields)
        {
            foreach ($fields as $parts)
            {
                $keyName = $parts['Key_name'];
                $columnName = $parts['Column_name'];
                $subPart = $parts['Sub_part'] ? '(' . $parts['Sub_part'] . ')' : FALSE;
                $this->db->query("CREATE INDEX `$keyName`" . " ON `" . $config->WIKINDX_DB_TABLEPREFIX . "$table` (`$columnName`$subPart)");
            }
        }
        $pString = HTML\p($this->pluginmessages->text('success'));
        GLOBALS::addTplVar('content', $pString);

        return;
    }
    /**
     * creatorsInit
     */
    public function creatorsInit()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingCreators'));
        $pString = HTML\p($this->pluginmessages->text('preamble1'));
        $pString .= HTML\p($this->pluginmessages->text('preamble2'));
        GLOBALS::addTplVar('content', $pString);
        $this->creatorsDisplay();

        return;
    }
    /**
     * fixcharsInit
     */
    public function fixcharsInit()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingFixchars'));
        $pString = HTML\p($this->pluginmessages->text('preamble1'));
        $pString .= HTML\p($this->pluginmessages->text('preamble2'));
        GLOBALS::addTplVar('content', $pString);
        $this->fixcharsDisplay();

        return;
    }
    /**
     * missingrowsInit
     */
    public function missingrowsInit()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingMissingrows'));
        $pString = HTML\p($this->pluginmessages->text('preamble1'));
        $pString .= HTML\p($this->pluginmessages->text('preamble2'));
        GLOBALS::addTplVar('content', $pString);

        return $this->missingrowsDisplay();
    }
    /**
     * totalsInit
     */
    public function totalsInit()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingTotals'));
        $pString = HTML\p($this->pluginmessages->text('preamble1'));
        $pString .= HTML\p($this->pluginmessages->text('preamble2'));
        GLOBALS::addTplVar('content', $pString);

        return $this->totalsDisplay();
    }
    /**
     * AJAX-based DIV content creator
     */
    public function getFixMessageAjax()
    {
        if (array_key_exists('ajaxReturn', $this->vars))
        {
            $message = $this->vars['ajaxReturn'];
        }
        $message .= 'Message';
        $div = HTML\div('divMess', HTML\color($this->pluginmessages->text($message), 'redText'));
        GLOBALS::addTplVar('content', AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Fix the characters
     */
    public function fixchars()
    {
        $this->errorsOn();
        if (array_key_exists('confirm', $this->vars) && array_key_exists('convType', $this->vars) &&
            (($this->vars['confirm'] == 'all') || ($this->vars['confirm'] == 'selected') ||
            ($this->vars['confirm'] == 'notSelected')))
        { // actually fix
            $this->fixcharsConfirm();
        }
        elseif (array_key_exists('convType', $this->vars) &&
            (($this->vars['convType'] == 'lightFixutf8') || ($this->vars['convType'] == 'toughFixutf8')))
        {
            $this->fixcharsParse(); // first run through to find problems
            $this->errorsOff();
        }
        else
        { // failure
            $errors = FACTORY_ERRORS::getInstance();
            GLOBALS::addTplVar('content', HTML\p($errors->text('inputError', 'missing')));
            $this->errorsOff();
            $this->fixcharsInit();
            FACTORY_CLOSE::getInstance();
        }
    }
    /**
     * Fix selected problem characters
     */
    public function fixcharsConfirm()
    {
        if ($this->vars['confirm'] == 'all')
        {
            $this->fixcharsParse(TRUE);
        }
        elseif ($this->vars['confirm'] == 'selected')
        {
            $count = 0;
            foreach ($this->vars as $key => $input)
            {
                if (mb_strpos($key, 'confirm_') === 0)
                {
                    $array = unserialize(base64_decode(str_replace('confirm_', '', $key)));
                    $table = array_shift($array);
                    $tableId = str_replace('_', '', $table) . 'Id';
                    $id = array_shift($array);
                    $field = array_shift($array);
                    $value = array_shift($array);
                    if (($table != 'config'))
                    {
                        $this->db->formatConditions([$tableId => $id]);
                    }
                    $this->db->update($table, [$field => $value]);
                    ++$count;
                }
            }
            if (!$count)
            {
                GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text('invalidInput'), 'error', 'center'));
                $this->errorsOff();
                $this->fixcharsInit();

                return;
            }
        }
        elseif ($this->vars['confirm'] == 'notSelected')
        {
            $count = 0;
            $invalids = [];
            foreach ($this->vars as $key => $input)
            {
                if (mb_strpos($key, 'confirm_') === 0)
                {
                    $array = str_replace('confirm_', '', $key);
                    $invalids[] = $array;
                    ++$count;
                }
            }
            if (!$count)
            {
                GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text('invalidInput'), 'error', 'center'));
                $this->errorsOff();
                $this->fixcharsInit();

                return;
            }
            $this->fixcharsParse(TRUE, $invalids);
        }
        // Delete caches so they can be recreated
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
        GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text('success'), 'success', 'center'));
        $this->errorsOff();
        $this->fixcharsInit();
    }
    /**
     * Find and fix missing rows
     */
    public function missingrows()
    {
        $this->errorsOn();
        $resIds = [];
        $resources = 0;
        $this->db->formatConditions($this->db->formatFields('resourcecreatorResourceId') . $this->db->equal . $this->db->formatFields('resourceId'));
        $stmt = $this->db->selectNoExecute('resource_creator', '*', FALSE, TRUE, TRUE);
        $stmt = $this->db->existsClause($stmt, TRUE);
        $this->db->formatConditions($stmt);
        $resultset = $this->db->select('resource', 'resourceId');
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->insert('resource_creator', 'resourcecreatorResourceId', $row['resourceId']);
            if (array_search($row['resourceId'], $resIds) === FALSE)
            {
                ++$resources;
                $resIds[] = $row['resourceId'];
            }
        }
        $this->db->formatConditions($this->db->formatFields('resourcecategoryResourceId') . $this->db->equal . $this->db->formatFields('resourceId'));
        $stmt = $this->db->selectNoExecute('resource_category', '*', FALSE, TRUE, TRUE);
        $stmt = $this->db->existsClause($stmt, TRUE);
        $this->db->formatConditions($stmt);
        $resultset = $this->db->select('resource', 'resourceId');
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->insert('resource_category', 'resourcecategoryResourceId', $row['resourceId']);
            if (array_search($row['resourceId'], $resIds) === FALSE)
            {
                ++$resources;
                $resIds[] = $row['resourceId'];
            }
        }
        $this->db->formatConditions($this->db->formatFields('resourcetimestampId') . $this->db->equal . $this->db->formatFields('resourceId'));
        $stmt = $this->db->selectNoExecute('resource_timestamp', '*', FALSE, TRUE, TRUE);
        $stmt = $this->db->existsClause($stmt, TRUE);
        $this->db->formatConditions($stmt);
        $resultset = $this->db->select('resource', 'resourceId');
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->insert(
                'resource_timestamp',
                ['resourcetimestampId', 'resourcetimestampTimestamp', 'resourcetimestampTimestampAdd'],
                [$row['resourceId'], '2012-01-01 01:01:01', '2012-01-01 01:01:01']
            );
            // update to NOW()
            $this->db->formatConditions(['resourcetimestampId' => $row['resourceId']]);
            $this->db->updateTimestamp('resource_timestamp', ['resourcetimestampTimestamp' => 'CURRENT_TIMESTAMP', 'resourcetimestampTimestampAdd' => 'CURRENT_TIMESTAMP']);
            if (array_search($row['resourceId'], $resIds) === FALSE)
            {
                ++$resources;
                $resIds[] = $row['resourceId'];
            }
        }
        $this->db->formatConditions($this->db->formatFields('resourcemiscId') . $this->db->equal . $this->db->formatFields('resourceId'));
        $stmt = $this->db->selectNoExecute('resource_misc', '*', FALSE, TRUE, TRUE);
        $stmt = $this->db->existsClause($stmt, TRUE);
        $this->db->formatConditions($stmt);
        $resultset = $this->db->select('resource', 'resourceId');
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->insert('resource_misc', 'resourcemiscId', $row['resourceId']);
            if (array_search($row['resourceId'], $resIds) === FALSE)
            {
                ++$resources;
                $resIds[] = $row['resourceId'];
            }
        }
        $this->db->formatConditions($this->db->formatFields('resourceyearId') . $this->db->equal . $this->db->formatFields('resourceId'));
        $stmt = $this->db->selectNoExecute('resource_year', '*', FALSE, TRUE, TRUE);
        $stmt = $this->db->existsClause($stmt, TRUE);
        $this->db->formatConditions($stmt);
        $resultset = $this->db->select('resource', 'resourceId');
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->insert('resource_year', 'resourceyearId', $row['resourceId']);
            if (array_search($row['resourceId'], $resIds) === FALSE)
            {
                ++$resources;
                $resIds[] = $row['resourceId'];
            }
        }
        $this->db->formatConditions($this->db->formatFields('statisticsResourceId') . $this->db->equal . $this->db->formatFields('resourceId'));
        $stmt = $this->db->selectNoExecute('statistics', '*', FALSE, TRUE, TRUE);
        $stmt = $this->db->existsClause($stmt, TRUE);
        $this->db->formatConditions($stmt);
        $resultset = $this->db->select('resource', 'resourceId');
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->insert('statistics', 'statisticsResourceId', $row['resourceId']);
            if (array_search($row['resourceId'], $resIds) === FALSE)
            {
                ++$resources;
                $resIds[] = $row['resourceId'];
            }
        }
        $this->errorsOff();
        $string = $this->pluginmessages->text('missingRowsCount', $resources);
        GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text('success', $string), 'success', 'center'));
        $this->missingrowsInit();
    }
    /**
     * Fix totals in database_summary table
     */
    public function totals()
    {
        $this->errorsOn();
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
        $this->errorsOff();
        $string = $this->pluginmessages->text('success');
        GLOBALS::addTplVar('content', HTML\p($string, 'success', 'center'));
        $this->totalsInit();
    }
    /**
     * Fix various creator errors
     */
    public function creators()
    {
        $this->errorsOn();
        $creatorIds = [];
        // In some cases, 'resourcecreatorCreatorSurname' does not match the id in resourcecreatorCreatorMain
        $this->db->formatConditions(['resourcecreatorCreatorMain' => 'IS NOT NULL']);
        $resultSet1 = $this->db->select('resource_creator', ['resourcecreatorCreatorMain', 'resourcecreatorCreatorSurname']);
        $resultSet2 = $this->db->select('creator', ['creatorId', 'creatorSurname']);
        while ($row = $this->db->fetchRow($resultSet2))
        {
            $creatorIds[$row['creatorId']] = mb_strtolower(preg_replace("/[^[:alnum:][:space:]]/u", '', $row['creatorSurname']));
        }
        while ($row = $this->db->fetchRow($resultSet1))
        {
            $rcSurname = mb_strtolower(preg_replace("/[^[:alnum:][:space:]]/u", '', $row['resourcecreatorCreatorSurname']));
            if ($rcSurname != $creatorIds[$row['resourcecreatorCreatorMain']])
            {
                $this->db->formatConditions(['resourcecreatorCreatorMain' => $row['resourcecreatorCreatorMain']]);
                $this->db->update('resource_creator', ['resourcecreatorCreatorSurname' => $creatorIds[$row['resourcecreatorCreatorMain']]]);
            }
        }
        $this->errorsOff();
        $string = $this->pluginmessages->text('success');
        GLOBALS::addTplVar('content', HTML\p($string, 'success', 'center'));
        $this->creatorsInit();
    }
    /**
     * Make the menus
     *
     * @param array $menuArray
     */
    private function makeMenu($menuArray)
    {
        $this->menus = [
            $menuArray[0] => ['repairkitpluginSub' => [
                $this->pluginmessages->text('menu') => FALSE,
            ],
            ],
        ];
        $this->menus[$menuArray[0]]['repairkitpluginSub'][$this->pluginmessages->text('menuFixchars')] = "fixcharsInit";
        $this->menus[$menuArray[0]]['repairkitpluginSub'][$this->pluginmessages->text('menuMissingrows')] = "missingrowsInit";
        $this->menus[$menuArray[0]]['repairkitpluginSub'][$this->pluginmessages->text('menuTotals')] = "totalsInit";
        $this->menus[$menuArray[0]]['repairkitpluginSub'][$this->pluginmessages->text('menuCreators')] = "creatorsInit";
        $this->menus[$menuArray[0]]['repairkitpluginSub'][$this->pluginmessages->text('menuDbIntegrity')] = "dbIntegrityInit";
    }
    /**
     * checkDatetime
     *
     * @return bool
     */
    private function checkDatetime()
    {
        $this->db->formatConditions(['resourcemetadataTimestamp' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataTimestamp')))
        {
            return TRUE;
        }
        $this->db->formatConditions(['resourcemetadataTimestampEdited' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId')))
        {
            return TRUE;
        }
        $this->db->formatConditions(['newsTimestamp' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('news', 'newsId')))
        {
            return TRUE;
        }
        $this->db->formatConditions(['resourceattachmentsTimestamp' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('resource_attachments', 'resourceattachmentsId')))
        {
            return TRUE;
        }
        $this->db->formatConditions(['resourceattachmentsEmbargoUntil' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('resource_attachments', 'resourceattachmentsId')))
        {
            return TRUE;
        }
        $this->db->formatConditions(['resourcetimestampTimestamp' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('resource_timestamp', 'resourcetimestampId')))
        {
            return TRUE;
        }
        $this->db->formatConditions(['resourcetimestampTimestampAdd' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('resource_timestamp', 'resourcetimestampId')))
        {
            return TRUE;
        }
        $this->db->formatConditions(['usersTimestamp' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('users', 'usersId')))
        {
            return TRUE;
        }
        $this->db->formatConditions(['usersNotifyTimestamp' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('users', 'usersId')))
        {
            return TRUE;
        }
        $this->db->formatConditions(['usersChangePasswordTimestamp' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('users', 'usersId')))
        {
            return TRUE;
        }
        $this->db->formatConditions(['userregisterTimestamp' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('user_register', 'userregisterId')))
        {
            return TRUE;
        }

        return FALSE;
    }
    /**
     * dbIntegrityReport
     *
     * @param array $currentDbSchema
     * @param array $correctDbSchema
     *
     * @return bool
     */
    private function dbIntegrityReport($currentDbSchema, $correctDbSchema)
    {
        foreach ($correctDbSchema as $table => $tableArray)
        {
            if (!array_key_exists($table, $currentDbSchema))
            {	// skip missing tables
                $this->dbMissingTables[] = $table;

                continue;
            }
            $this->getDbInconsistencies($currentDbSchema, $tableArray, $table, TRUE);
        }
        if (!empty($this->dbMissingTables) || !empty($this->dbMissingFields) || !empty($this->dbInconsistenciesReport))
        {
            return FALSE;
        }

        return TRUE;
    }
    /**
     * getDbInconsistencies
     *
     * @param array $currentDbSchema
     * @param array $tableArray
     * @param string $table
     * @param bool $report
     *
     * @return bool
     */
    private function getDbInconsistencies($currentDbSchema, $tableArray, $table, $report = FALSE)
    {
        foreach ($tableArray['fields'] as $field => $fieldArrays)
        {
            foreach ($fieldArrays as $index => $fieldArray)
            {
                // Correct field to check
                $fieldName = $fieldArray['Field'];

                // Search the correct field in the current table fields for the checked table
                $FieldExists = FALSE;
                foreach ($currentDbSchema[$table]['fields'] as $cf => $cfas)
                {
                    foreach ($cfas as $ci => $cfa)
                    {
                        if ($cfa['Field'] == $fieldName)
                        {
                            $currentDbField = $cf;
                            $currentDbIndex = $ci;
                            $FieldExists = TRUE;
                        }
                    }
                }

                // Skip checks if the field is missing and report it as missing
                if (!$FieldExists)
                {
                    $this->dbMissingFields[] = [$table, $fieldName];

                    continue;
                }

                // Check the field type
                $checkedType = $currentDbSchema[$table]['fields'][$currentDbField][$currentDbIndex]['Type'];
                if ($checkedType != $fieldArray['Type'])
                {
                    $currentValue = !$checkedType ? htmlentities('<empty>') : $checkedType;
                    $correctValue = !$fieldArray['Type'] ? htmlentities('<empty>') : $fieldArray['Type'];
                    if ($report)
                    {
                        $this->dbInconsistenciesReport[$table][] =
                            "TABLE $table: " . $fieldName . "['Type'] is " . $currentValue . "."
                            . " It should be: " . $correctValue . BR;
                    }
                    else
                    {
                        $this->dbFieldInconsistenciesFix[$table][] = $fieldName;
                    }
                }

                // Check the default field value
                $checkedDefault = $currentDbSchema[$table]['fields'][$currentDbField][$currentDbIndex]['Default'];

                // current_timestamp() and CURRENT_TIMESTAMP are the same definition (the first is MySQL jargon, the second is standard)
                $checkedDefault = str_replace('current_timestamp()', 'CURRENT_TIMESTAMP', $checkedDefault);
                $fieldArray['Default'] = str_replace('current_timestamp()', 'CURRENT_TIMESTAMP', $fieldArray['Default']);

                if ($checkedDefault != $fieldArray['Default'])
                {
                    $currentValue = !$checkedDefault ? htmlentities('<empty>') : $checkedDefault;
                    $correctValue = !$fieldArray['Default'] ? htmlentities('<empty>') : $fieldArray['Default'];
                    if ($report)
                    {
                        $this->dbInconsistenciesReport[$table][] =
                            "TABLE $table: " . $fieldName . "[Default] is " . $currentValue . "."
                            . " It should be: " . $correctValue . BR;
                    }
                    else
                    {
                        $this->dbFieldInconsistenciesFix[$table][] = $fieldName;
                    }
                }

                // Check the field nullability
                $checkedNull = $currentDbSchema[$table]['fields'][$currentDbField][$currentDbIndex]['Null'];
                if ($checkedNull != $fieldArray['Null'])
                {
                    $currentValue = !$checkedNull ? htmlentities('<empty>') : $checkedNull;
                    $correctValue = !$fieldArray['Null'] ? htmlentities('<empty>') : $fieldArray['Null'];
                    if ($report)
                    {
                        $this->dbInconsistenciesReport[$table][] =
                            "TABLE $table: " . $fieldName . "[Null] is " . $currentValue . "."
                            . " It should be: " . $correctValue . BR;
                    }
                    else
                    {
                        $this->dbFieldInconsistenciesFix[$table][] = $fieldName;
                    }
                }

                // Check the field key
                $checkedKey = $currentDbSchema[$table]['fields'][$currentDbField][$currentDbIndex]['Key'];
                if ($checkedKey != $fieldArray['Key'])
                {
                    $currentValue = !$checkedKey ? htmlentities('<empty>') : $checkedKey;
                    $correctValue = !$fieldArray['Key'] ? htmlentities('<empty>') : $fieldArray['Key'];
                    if ($report)
                    {
                        $this->dbInconsistenciesReport[$table][] =
                            "TABLE $table: " . $fieldName . "[Key] is " . $currentValue . "."
                            . " It should be: " . $correctValue . BR;
                    }
                    else
                    {
                        $this->dbKeyInconsistenciesFix[$table][] = $fieldName;
                    }
                }
            }
        }

        foreach ($tableArray['schema'] as $field => $fieldArrays)
        {
            foreach ($fieldArrays as $index => $fieldArray)
            {
                // Search the correct values for the checked table
                $checkedEngine = '';
                $checkedCollation = '';
                foreach ($currentDbSchema[$table]['schema'] as $cf => $cfas)
                {
                    foreach ($cfas as $ci => $cfa)
                    {
                        $checkedEngine = $cfa['ENGINE'];
                        $checkedCollation = $cfa['TABLE_COLLATION'];

                        break;
                    }
                }
                // Check the engine
                if ($checkedEngine != $fieldArray['ENGINE'])
                {
                    if ($report)
                    {
                        $this->dbInconsistenciesReport[$table][] =
                            "TABLE $table: " . "['ENGINE'] is " . $checkedEngine . "."
                            . " It should be: " . $fieldArray['ENGINE'] . BR;
                    }
                    else
                    {
                        $this->dbTableInconsistenciesFix['engine'][] = $table;
                    }
                }
                // Check the collation
                if ($checkedCollation != $fieldArray['TABLE_COLLATION'])
                {
                    if ($report)
                    {
                        $this->dbInconsistenciesReport[$table][] =
                            "TABLE $table: " . "['TABLE_COLLATION'] is " . $checkedCollation . "."
                            . " It should be: " . $fieldArray['TABLE_COLLATION'] . BR;
                    }
                    else
                    {
                        $this->dbTableInconsistenciesFix['collation'][] = $table;
                    }
                }
            }
        }
        foreach ($tableArray['indices'] as $field => $fieldArrays)
        {
            foreach ($fieldArrays as $index => $fieldArray)
            {
                $foundMatchingKeyName = FALSE;
                $subPart = NULL;
                if ($fieldArray === FALSE)
                { // i.e. no indices in table in correct database structure
                    $correctKeyName = $correctColumnName = $correctSubPart = htmlentities('<empty>');
                }
                else
                {
                    $correctKeyName = $fieldArray['Key_name'] ? $fieldArray['Key_name'] : htmlentities('<empty>');
                    $correctColumnName = $fieldArray['Column_name'] ? $fieldArray['Column_name'] : htmlentities('<empty>');
                    $correctSubPart = $fieldArray['Sub_part'] ? $fieldArray['Sub_part'] : htmlentities('<empty>');
                    if ($fieldArray['Sub_part'])
                    {
                        $subPart = $fieldArray['Sub_part'];
                    }
                }
                foreach ($currentDbSchema[$table]['indices'][$field] as $currentFieldArray)
                {
                    // No index in the current db and no index in the schema is OK
                    if ($currentFieldArray === FALSE && $fieldArray === FALSE)
                    {
                        break;
                    }
                    // When an index is missing in current database
                    if ($currentFieldArray === FALSE && is_array($fieldArray))
                    {
                        $currentKeyName = $currentColumnName = $currentSubPart = htmlentities('<empty>');
                        $foundMatchingKeyName = TRUE;

                        break;
                    }
                    // Supernumerary index in the current database
                    if (is_array($currentFieldArray) && $fieldArray === FALSE)
                    {
                        $currentKeyName = $currentFieldArray['Key_name'] ? $currentFieldArray['Key_name'] : htmlentities('<empty>');
                        $currentColumnName = $currentFieldArray['Column_name'] ? $currentFieldArray['Column_name'] : htmlentities('<empty>');
                        $currentSubPart = $currentFieldArray['Sub_part'] ? $currentFieldArray['Sub_part'] : htmlentities('<empty>');
                        $foundMatchingKeyName = TRUE;

                        break;
                    }
                    // Index present with an inconsistent definition
                    if ($currentFieldArray['Key_name'] == $fieldArray['Key_name'])
                    {
                        $currentKeyName = $currentFieldArray['Key_name'] ? $currentFieldArray['Key_name'] : htmlentities('<empty>');
                        $currentColumnName = $currentFieldArray['Column_name'] ? $currentFieldArray['Column_name'] : htmlentities('<empty>');
                        $currentSubPart = $currentFieldArray['Sub_part'] ? $currentFieldArray['Sub_part'] : htmlentities('<empty>');
                        $foundMatchingKeyName = TRUE;

                        break;
                    }
                }
                if (
                    $foundMatchingKeyName
                    && (($currentKeyName != $correctKeyName) || ($currentColumnName != $correctColumnName) || ($currentSubPart != $correctSubPart))
                ) {
                    if ($report)
                    {
                        $this->dbInconsistenciesReport[$table][] =
                            "TABLE $table: INDEX mismatch. Key_name: $currentKeyName, Column_name: $currentColumnName, Sub_part: $currentSubPart
							 should be: Key_name: $correctKeyName, Column_name: $correctColumnName, Sub_part: $correctSubPart." . BR;
                    }
                    else
                    {
                        $this->dbIndexInconsistenciesFix[$table][] =
                            ['Key_name' => $correctKeyName, 'Column_name' => $correctColumnName, 'Sub_part' => $subPart];
                    }
                }
            }
        }
    }
    /**
     * Correct anomalies in the datetime fields – there should be no occurrence of '0000-00-00 00:00:00' as a value.
     *
     * The strategy is:
     * 1. If default is NULL, set all incorrect values to that. Otherwise,
     * 2. Find the minimum value in the table then set all incorrect fields to that. Otherwise,
     * 3. If all values are incorrect, then set all values to default.
     */
    private function fixDatetimeFields()
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
     * fixcharsDisplay
     */
    private function fixcharsDisplay()
    {
        $pString = HTML\p($this->pluginmessages->text('fixutf8Preamble1'));
        $pString .= FORM\formHeader("repairkit_fixchars");
        $pString .= HTML\tableStart();
        $pString .= HTML\trStart();
        $jsonArray = [];
        $jScript = 'index.php?action=repairkit_getFixMessageAjax';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'convType',
            'targetDiv' => 'divMess',
        ];
        $js = AJAX\jActionForm('onclick', $jsonArray);
        $pString .= HTML\td(FORM\selectFBoxValue(
            FALSE,
            'convType',
            ['lightFixutf8' => $this->pluginmessages->text('lightFixutf8'),
                'toughFixutf8' => $this->pluginmessages->text('toughFixutf8'), ],
            2,
            FALSE,
            $js
        ));
        $pString .= HTML\td(HTML\div('divMess', HTML\color($this->pluginmessages->text('lightFixutf8Message'), 'redText')));
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();
        AJAX\loadJavascript();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * missingrowsDisplay
     */
    private function missingrowsDisplay()
    {
        $pString = HTML\p($this->pluginmessages->text('missingrowsPreamble'));
        $pString .= FORM\formHeader("repairkit_missingrows");
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * creatorsDisplay
     */
    private function creatorsDisplay()
    {
        $pString = HTML\p($this->pluginmessages->text('creatorsPreamble'));
        $pString .= FORM\formHeader("repairkit_creators");
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * totalsDisplay
     */
    private function totalsDisplay()
    {
        $pString = HTML\p($this->pluginmessages->text('totalsPreamble'));
        $pString .= FORM\formHeader("repairkit_totals");
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * find problem characters
     *
     * @param bool $fix
     * @param array $invalids
     */
    private function fixcharsParse($fix = FALSE, $invalids = [])
    {
        // Save memory limit configuration
        $memory_limit = ini_get('memory_limit');
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Doesn't always work
        ini_set('memory_limit', '-1');
        if (!$fix)
        {
            $fixFound = FALSE;
            $init_pString = FORM\formHeader("repairkit_fixcharsConfirm");
            $init_pString .= FORM\hidden('convType', $this->vars['convType']);
            $init_pString .= HTML\p($this->pluginmessages->text('fixutf8Preamble2'));
            $init_pString .= HTML\p(FORM\selectFBoxValue(
                FALSE,
                'confirm',
                ['all' => $this->pluginmessages->text('fixUtf8All'),
                    'selected' => $this->pluginmessages->text('fixUtf8Selected'),
                    'notSelected' => $this->pluginmessages->text('fixUtf8NotSelected'), ],
                3
            ));
            $init_pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Confirm")));
            $init_pString .= HTML\tableStart('generalTable borderStyleSolid');
        }
        $convType = $this->vars['convType'];

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
                $resultset = $this->db->select($table, '*');
                while ($row = $this->db->fetchRow($resultset))
                {
                    $id = str_replace('_', '', $table) . 'Id';
                    foreach ($row as $field => $value)
                    {
                        if (!$value || is_numeric($value))
                        {
                            continue;
                        }
                        $updateArray = [];
                        $value = stripslashes($value);
                        $original = $value;
                        if ($convType == 'lightFixutf8')
                        {
                            $value = Encoding::toUTF8($value);
                            if ($original != $value)
                            {
                                $updateArray[$field] = $value;
                                if (!$fix)
                                {
                                    if (!$fixFound)
                                    {
                                        GLOBALS::addTplVar('content', $init_pString);
                                        $fixFound = TRUE;
                                    }
                                    if (($table == 'config'))
                                    {
                                        $key = base64_encode(serialize([$table, NULL, $field, $value]));
                                    }
                                    // $fixes[base64_encode(serialize(array($table, null, $field, $value)))] = $value;
                                    else
                                    {
                                        $key = base64_encode(serialize([$table, $row[$id], $field, $value]));
                                    }
                                    // $fixes[base64_encode(serialize(array($table, $row[$id], $field, $value)))] = $value;
                                    $pString = HTML\trStart();
                                    $pString .= HTML\td(FORM\checkbox(FALSE, 'confirm_' . $key), 'padding2px');
                                    $pString .= HTML\td($value, 'padding2px');
                                    $pString .= HTML\trEnd();
                                    GLOBALS::addTplVar('content', $pString);
                                }
                            }
                        }
                        elseif ($convType == 'toughFixutf8')
                        {
                            $value = Encoding::fixUTF8($value);
                            if ($original != $value)
                            {
                                $updateArray[$field] = $value;
                                if (!$fix)
                                {
                                    if (!$fixFound)
                                    {
                                        GLOBALS::addTplVar('content', $init_pString);
                                        $fixFound = TRUE;
                                    }
                                    if (($table == 'config'))
                                    {
                                        $key = base64_encode(serialize([$table, NULL, $field, $value]));
                                    }
                                    // $fixes[base64_encode(serialize(array($table, null, $field, $value)))] = $value;
                                    else
                                    {
                                        $key = base64_encode(serialize([$table, $row[$id], $field, $value]));
                                    }
                                    // $fixes[base64_encode(serialize(array($table, $row[$id], $field, $value)))] = $value;
                                    $pString = HTML\trStart();
                                    $pString .= HTML\td(FORM\checkbox(FALSE, 'confirm_' . $key), 'padding2px');
                                    $pString .= HTML\td($value, 'padding2px');
                                    $pString .= HTML\trEnd();
                                    GLOBALS::addTplVar('content', $pString);
                                }
                            }
                        }
                        unset($pString);
                        if ($fix && !empty($updateArray))
                        {
                            if (($table == 'config'))
                            {
                                $check = base64_encode(serialize([$table, NULL, $field, $value]));
                            }
                            else
                            {
                                $check = base64_encode(serialize([$table, $row[$id], $field, $value]));
                            }
                            if (array_search($check, $invalids) === FALSE)
                            {
                                if (($table != 'config'))
                                {
                                    $this->db->formatConditions([$id => $row[$id]]);
                                }
                                $this->db->update($table, $updateArray);
                            }
                            else
                            {
                                echo 'NOT INVALID<br>';
                            }
                            unset($updateArray);
                        }
                    }
                }
            }
        }
        if (!$fix)
        {
            GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingFixchars'));
            if (!$fixFound)
            {
                GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text('fixUtf8NotFound'), 'success', 'center'));
                $this->fixcharsInit();
            }
            else
            {
                /*
                foreach($fixes as $key => $value)
                {
                    $pString = HTML\trStart();
                    $pString .= HTML\td(FORM\checkbox(FALSE, 'confirm_' . $key), 'padding2px');
                    $pString .= HTML\td($value, 'padding2px');
                    $pString .= HTML\trEnd();
                    GLOBALS::addTplVar('content', $pString);
                }
                */
                $pString = HTML\tableEnd();
                $pString .= FORM\formEnd();
                GLOBALS::addTplVar('content', $pString);
            }
        }
        // Restore memory limit configuration
        ini_set('memory_limit', $memory_limit);
    }
    /**
     * Turn error reporting on
     */
    private function errorsOn()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
    }
    /**
     * Restore defaut error reporting level
     */
    private function errorsOff()
    {
        FACTORY_LOADCONFIG::getInstance()->configureErrorReporting();
    }
}