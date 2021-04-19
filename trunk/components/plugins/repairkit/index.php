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
 * repairkit class.
 *
 * A number of database repair operations
 */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));

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
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('repairkit', 'repairkitMessages');
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
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
    }
    /**
     * dbIntegrityInit
     */
    public function dbIntegrityInit()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingDbIntegrity'));
        $pString = '';
        if (array_key_exists('uuid', $this->vars)) {
	        $pString = \TEMPSTORAGE\fetchOne($this->db, $this->vars['uuid'], 'repairkitMessages');
            \TEMPSTORAGE\deleteKeys($this->db, $this->vars['uuid'], ['repaikitrMessages']);
		}
        
        $wVersion = WIKINDX_INTERNAL_VERSION;
        $dbVersion = \UPDATE\getCoreInternalVersion($this->db);
        if (floatval($dbVersion) != floatval($wVersion))
        {
            // Shouldn't ever happen if UPDATEDATABASE is functioning correctly . . .
            $pString .= HTML\p($this->pluginmessages->text('dbIntegrityPreamble1a', $dbVersion) . '&nbsp;' .
                $this->pluginmessages->text('dbIntegrityPreamble1b', $wVersion));
            GLOBALS::addTplVar('content', $pString);

            return;
        }
        
        $currentDbSchema = $this->db->createRepairKitDbSchema();
        $correctDbSchema = $this->db->getRepairKitDbSchema(WIKINDX_FILE_REPAIRKIT_DB_SCHEMA);
        
        if ($correctDbSchema === FALSE)
        {
            $pString .= HTML\p($this->pluginmessages->text('fileReadError'), 'error');
            GLOBALS::addTplVar('content', $pString);

            return;
        }
        
        $dbErrors = $this->dbIntegrityCheck($currentDbSchema, $correctDbSchema);
        
        if ($dbErrors["count"] > 0)
        {
            // Database can be fixed
            $pString .= HTML\p($this->pluginmessages->text('dbIntegrityPreamble3', $wVersion));
            $pString .= HTML\p($this->pluginmessages->text('dbIntegrityPreamble4', $wVersion));
            $pString .= FORM\formHeader("repairkit_dbIntegrityFix");
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "OK")));
            $pString .= FORM\formEnd();
            GLOBALS::addTplVar('content', $pString);
            
            $this->dbIntegrityDisplay($dbErrors, $currentDbSchema, $correctDbSchema);
        }
        else
        {
            $pString .= HTML\p($this->pluginmessages->text('dbIntegrityPreamble2'));
            GLOBALS::addTplVar('content', $pString);
        }
    }
    /**
     * dbIntegrityFix
     *
     * Each type of fix is not mixed with an other type of fix
     * because some of them are dependent on a lower or higer level of SQL object.
     * They are done by descending to the lowest SQL object level : db, table, field, index.
     */
    public function dbIntegrityFix()
    {
        $wVersion = WIKINDX_INTERNAL_VERSION;
        $dbVersion = \UPDATE\getCoreInternalVersion($this->db);
        if (floatval($dbVersion) != floatval($wVersion))
        {
            // Abort: we should not be there if the version if not the current!
            // Display the report silently
            $this->dbIntegrityInit();
            die();
        }
        
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingDbIntegrity'));
        
        $currentDbSchema = $this->db->createRepairKitDbSchema();
        $correctDbSchema = $this->db->getRepairKitDbSchema(WIKINDX_FILE_REPAIRKIT_DB_SCHEMA);
        
        if ($correctDbSchema === FALSE)
        {
            $pString = HTML\p($this->pluginmessages->text('fileReadError'), 'error');
            GLOBALS::addTplVar('content', $pString);

            return;
        }
        
        $dbErrors = $this->dbIntegrityCheck($currentDbSchema, $correctDbSchema);
        
        // Errors need to be fixed
        if ($dbErrors["count"] > 0)
        {
            // DATABASE
            if (count($dbErrors["database"]) > 0)
            {
                // Since there are only two easy cases, fix them in a single action
                $this->changeDbCollation($correctDbSchema["database"]);
            }
            // TABLES
            elseif (count($dbErrors["tables"]) > 0)
            {
                $tableArrayCorrect = $correctDbSchema["tables"];
                $tableArrayCurrent = $currentDbSchema["tables"];
                
                foreach ($dbErrors["tables"] as $e)
                {
                    // Search the correct definition
                    $tableCorrect = [];
                    foreach ($tableArrayCorrect as $t)
                    {
                        if ($t["Table"] == $e["Table"])
                        {
                            $tableCorrect = $t;
                            break;
                        }
                    }
                    // Search the current definition
                    $tableCurrent = [];
                    foreach ($tableArrayCurrent as $t)
                    {
                        if (mb_strtolower($t["Table"]) == mb_strtolower($e["Table"]))
                        {
                            $tableCurrent = $t;
                            break;
                        }
                    }
                    
                    if ($e["Code"] == 1)
                    {
                        // NOK
                        // If the table is empty it's easier to recreate it. 
                        if ($this->db->tableIsEmpty($this->db->basicTable($e["Table"])))
                        {
                            $this->dropTable($e["Table"]);
                            $this->createTable($e["Table"]);
                        }
                        else
                        {
                            $this->changeTableCollation($tableCorrect);
                            $this->changeTableEngine($tableCorrect);
                            
                            if (array_key_exists("Table", $tableArrayCurrent) && $tableCorrect["Table"] != $tableCurrent["Table"])
                            {
                                $this->renameTable($tableCurrent["Table"], $tableCorrect["Table"]);
                            }
                        }
                    }
                    elseif ($e["Code"] == 2)
                    {
                        // Missing
                        $this->createTable($e["Table"]);
                    }
                    elseif ($e["Code"] == 3)
                    {
                        // Supernumerary (provided it is empty)
                        if ($this->db->tableIsEmpty($this->db->basicTable($e["Table"])))
                        {
                            $this->dropTable($e["Table"]);
                        }
                    }
                }
            }
            // FIELDS
            elseif (count($dbErrors["fields"]) > 0)
            {
                $fieldArrayCorrect = $correctDbSchema["fields"];
                $fieldArrayCurrent = $currentDbSchema["fields"];
                
                foreach ($dbErrors["fields"] as $e)
                {
                    // Search the correct definition
                    $fieldCorrect = [];
                    foreach ($fieldArrayCorrect as $f)
                    {
                        if (
                            $f["Table"] == $e["Table"]
                            && $f["Field"] == $e["Field"]
                        ) {
                            $fieldCorrect = $f;
                            break;
                        }
                    }
                    // Search the current definition
                    $fieldCurrent = [];
                    foreach ($fieldArrayCurrent as $f)
                    {
                        if (
                            mb_strtolower($f["Table"]) == mb_strtolower($e["Table"])
                            && mb_strtolower($f["Field"]) == mb_strtolower($e["Field"])
                        ) {
                            $fieldCurrent = $f;
                            break;
                        }
                    }
                    
                    if ($e["Code"] == 1)
                    {
                        // Remove indices before others field changes.
                        // A length change on (var)char field can raise an error about
                        // index length limits and prevent all other corrections.
                        foreach ($correctDbSchema["indices"] as $i)
                        {
                            if (
                                mb_strtolower($i["Table"]) == mb_strtolower($e["Table"])
                                && mb_strtolower($i["Column_name"]) == mb_strtolower($e["Field"])
                            ) {
                                $this->dropIndex($i["Table"], $i["Key_name"]);
                            }
                        }
                        
                        // NOK
                        $this->changeField($fieldCurrent, $fieldCorrect);
                    }
                    elseif ($e["Code"] == 2)
                    {
                        // Missing
                        $this->createField($fieldCorrect);
                    }
                    elseif ($e["Code"] == 3)
                    {
                        // Supernumerary (provided it is empty)
                        if ($this->db->tableIsEmpty($this->db->basicTable($e["Table"])))
                        {
                            $this->dropField($fieldCurrent);
                        }
                    }
                }
            }
            // INDICES
            elseif (count($dbErrors["indices"]) > 0)
            {
                $indexArrayCorrect = $correctDbSchema["indices"];
                $indexArrayCurrent = $currentDbSchema["indices"];
                
                foreach ($dbErrors["indices"] as $k => $e)
                {
                    // When an index is multi-field, we must fix it only once,
                    // so we must skip the definition of secondary fields
                    if (
                        $k > 0
                        && $dbErrors["indices"][$k]["Table"] == $e["Table"]
                        && $dbErrors["indices"][$k]["Key_name"] == $e["Key_name"]
                    ) {
                        continue;
                    }
                    
                    // Search the correct definition
                    $indexCorrect = [];
                    foreach ($indexArrayCorrect as $i)
                    {
                        if (
                            mb_strtolower($i["Table"]) == mb_strtolower($e["Table"])
                            && mb_strtolower($i["Key_name"]) == mb_strtolower($e["Key_name"])
                        ) {
                            // Gathers the definitions of all the fields of a multi-field index
                            $indexCorrect[] = $i;
                        }
                    }
                    // Search the current definition
                    $indexCurrent = [];
                    foreach ($indexArrayCurrent as $i)
                    {
                        if (
                            mb_strtolower($i["Table"]) == mb_strtolower($e["Table"])
                            && mb_strtolower($i["Key_name"]) == mb_strtolower($e["Key_name"])
                        ) {
                            // Gathers the definitions of all the fields of a multi-field index
                            $indexCurrent[] = $i;
                        }
                    }
                    
                    if ($e["Code"] == 1)
                    {
                        // NOK
                        $this->dropIndex($indexCurrent[0]["Table"], $indexCurrent[0]["Key_name"]);
                        $this->createIndex($indexCorrect);
                    }
                    elseif ($e["Code"] == 2)
                    {
                        // Missing
                        $this->createIndex($indexCorrect);
                    }
                    elseif ($e["Code"] == 3)
                    {
                        // Supernumerary
                        $this->dropIndex($indexCurrent[0]["Table"], $indexCurrent[0]["Key_name"]);
                    }
                }
            }
        }
        
        $message = HTML\p($this->pluginmessages->text('success'), 'success', 'center');
        $uuid = \TEMPSTORAGE\getUuid($this->db);
    	\TEMPSTORAGE\store($this->db, $uuid, ['repairkitMessages' => $message]);
        header("Location: index.php?action=repairkit_dbIntegrityInit&uuid=$uuid");
        die;
    }
    
    /*
     * Change the collation and character set of the db
     *
     * @param array $dbDef Definition of a db object provided by dbIntegrityCheck
     */
    private function changeDbCollation($dbDef)
    {
        $sql = "ALTER DATABASE " . WIKINDX_DB . " CHARACTER SET " . $dbDef["collation"] . " COLLATE " . $dbDef["character_set"] . ";";
        $this->db->queryNoResult($sql);
    }
    
    /*
     * Create a table
     *
     * @param string $table Fullname of a source table
     */
    private function createTable($table)
    {
        // The db schema is stored in a series of SQL file in the directory /dbschema/full for the core
        // or /plugins/<PluginDirectory>/dbschema/full
        $dbSchemaPath = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DB_SCHEMA, "full"]);
        $sqlfile = "table_" . $this->db->basicTable($table) . ".sql";
        $sql = file_get_contents($dbSchemaPath . DIRECTORY_SEPARATOR . $sqlfile);
        $this->db->queryNoResult($sql);
    }
    
    /*
     * Rename a table
     *
     * @param string $tablesrc Fullname of a source table
     * @param string $tabledst Fullname of a destination table
     */
    private function renameTable($tablesrc, $tabledst)
    {
        $tmpTable = uniqid("wkx_");
        
        // Change the name of all tables to lower case (workaround for mySQL engine on case sensitive files systems)
        $this->db->queryNoResult("ALTER TABLE `" . $tablesrc . "` RENAME AS `" . $tmpTable . "`;");
        $this->db->queryNoResult("ALTER TABLE `" . $tmpTable . "` RENAME AS `" . $tabledst . "`;");
    }
    
    /*
     * Change the collation and character set of a table
     *
     * @param array $tableDef Definition of a table object provided by dbIntegrityCheck
     */
    private function changeTableCollation($tableDef)
    {
        if ($tableDef["Collation"] == "binary")
            $charset = "binary";
        else
            $charset = substr($tableDef["Collation"], 0, strpos($tableDef["Collation"], "_"));
        
        $sql = "ALTER TABLE " . $tableDef["Table"] . " CONVERT TO CHARACTER SET " . $charset . " COLLATE " . $tableDef["Collation"] . ";";
        $this->db->queryNoResult($sql);
    }
    
    /*
     * Change the engine of a table
     *
     * @param array $tableDef Definition of a table object provided by dbIntegrityCheck
     */
    private function changeTableEngine($tableDef)
    {
        $sql = "ALTER TABLE " . $tableDef["Table"] . " ENGINE = " . $tableDef["Engine"] . ";";
        $this->db->queryNoResult($sql);
    }
    
    /*
     * Drop a table
     *
     * @param string $table Fullname of a table
     */
    private function dropTable($table)
    {
        $sql = "DROP TABLE IF EXISTS " . $table . ";";
        $this->db->queryNoResult($sql);
    }
    
    /*
     * Create a field on a table
     *
     * @param array $fieldDef Definition of a field object provided by dbIntegrityCheck
     */
    private function createField($fieldDef)
    {
        // cf. https://dev.mysql.com/doc/refman/5.7/en/create-table.html
        // cf. https://dev.mysql.com/doc/refman/5.7/en/alter-table.html
        
        // tbl_name
        $sql = "ALTER TABLE " . $fieldDef["Table"] . " ";
        
        // alter_option (column_name)
        $sql .= " ADD COLUMN `" . $fieldDef["Field"] . "` ";
        
        // column_definition (data_type)
        $sql .= " " . $fieldDef["Type"] . " ";
        
        // column_definition (nullable?)
        $sql .= $fieldDef["Null"] == "YES" ? " NULL " : " NOT NULL ";
        
        // column_definition (default value)
        if ($fieldDef["Default"] == "current_timestamp()")
            $sql .= " " . $fieldDef["Default"] . " ";
        elseif ($fieldDef["Default"] != NULL)
            $sql .= " '" . $this->db->escapeString($fieldDef["Default"]) . "' ";
        
        // column_definition (extra clause)
        if ($fieldDef["Extra"] == "on update current_timestamp()")
            $sql .= " " . $fieldDef["Default"] . " ";
        elseif ($fieldDef["Extra"] == "auto_increment")
            $sql .= " " . $fieldDef["Default"] . " ";
        
        // column_definition (collation)
        $sql .= $fieldDef["Collation"] == NULL ? "" : " COLLATE " . $fieldDef["Collation"] . " ";
        
        $sql .= ";";
        
        $this->db->queryNoResult($sql);
    }
    
    /*
     * Change a field of a table
     *
     * @param array $fieldDefOld Definition of the old field object provided by dbIntegrityCheck
     * @param array $fieldDefNew Definition of the new field object provided by dbIntegrityCheck
     */
    private function changeField($fieldDefOld, $fieldDefNew)
    {
        // cf. https://dev.mysql.com/doc/refman/5.7/en/create-table.html
        // cf. https://dev.mysql.com/doc/refman/5.7/en/alter-table.html
        
        // tbl_name
        $sql = "ALTER TABLE " . $fieldDefNew["Table"] . " ";
        
        // alter_option (column_name)
        $sql .= " CHANGE COLUMN `" . (count($fieldDefOld) > 0 ? $fieldDefOld["Field"] : $fieldDefNew["Field"]) . "` `" . $fieldDefNew["Field"] . "` ";
        
        // column_definition (data_type)
        $sql .= " " . $fieldDefNew["Type"] . " ";
        
        // column_definition (nullable?)
        $sql .= $fieldDefNew["Null"] == "YES" ? " NULL " : " NOT NULL ";
        
        // column_definition (default value)
        if ($fieldDefNew["Default"] == "current_timestamp()")
            $sql .= " DEFAULT " . $fieldDefNew["Default"] . " ";
        elseif ($fieldDefNew["Default"] != NULL)
            $sql .= " DEFAULT '" . $this->db->escapeString($fieldDefNew["Default"]) . "' ";
        
        // column_definition (extra clause)
        if ($fieldDefNew["Extra"] == "on update current_timestamp()")
            $sql .= " " . $fieldDefNew["Extra"] . " ";
        elseif ($fieldDefNew["Extra"] == "auto_increment")
            $sql .= " " . $fieldDefNew["Extra"] . " ";
        
        // column_definition (collation)
        $sql .= $fieldDefNew["Collation"] == NULL ? "" : " COLLATE " . $fieldDefNew["Collation"] . " ";
        
        $sql .= ";";
        
        $this->db->queryNoResult($sql);
    }
    
    /*
     * Drop a field from a table
     *
     * @param array $fieldDef Definition of a field object provided by dbIntegrityCheck
     */
    private function dropField($fieldDef)
    {
        $sql = "ALTER TABLE " . $fieldDef["Table"] . " DROP COLUMN " . $fieldDef["Field"] . ";";
        $this->db->queryNoResult($sql);
    }
    
    /*
     * Create an index on a table
     *
     * @param array $indicesDef Definition of an index object provided by dbIntegrityCheck
     */
    private function createIndex($indicesDef)
    {
        // cf. https://dev.mysql.com/doc/refman/5.7/en/alter-table.html
        // cf. https://dev.mysql.com/doc/refman/5.7/en/create-index.html
        
        $indexDef = $indicesDef[0];
        
        // tbl_name
        $sql = "ALTER TABLE " . $indexDef["Table"] . " ";
        
        // alter_option
        if ($indexDef["Key_name"] == "PRIMARY")
        {
            // Primary Key
            $sql .= " ADD PRIMARY KEY ";
        }
        elseif ($indexDef["Non_unique"] == "0")
        {
            // unique Key
            $sql .= " ADD UNIQUE KEY `" . $indexDef["Key_name"] . "` ";
        }
        else
        {
            // Others indices
            $sql .= " ADD ";
            if (in_array($indexDef["Index_type"], ["FULLTEXT", "SPATIAL"])) $sql .= " " . $indexDef["Index_type"] . " ";
            $sql .= " INDEX `" . $indexDef["Key_name"] . "` ";
        }
        
        // index_type
        $indexType = [
            "BTREE" => " USING BTREE ",
            "FULLTEXT" => "", // Not inserted at this place
            "HASH" => " USING HASH ",
            "RTREE" => "", // Not used (for MyISAM storage engine only)
        ];
        $sql .= " " . $indexType[$indexDef["Index_type"]] . " ";
        
        $sql .= " ( ";
            foreach($indicesDef as $indexDef)
            {
                // key_part (col_name)
                $sql .= $indexDef["Column_name"];
                
                // key_part (length)
                if ($indexDef["Sub_part"] != NULL) $sql .= "(" . $indexDef["Sub_part"] . ")";
                
                // key_part (sorting)
                $sortType = [
                    "A" => " ASC ",
                    "D" => " DSC ",
                    NULL => "",
                ];
                $sql .= $sortType[$indexDef["Collation"]];
                $sql .= ",";
            }
            $sql = rtrim($sql, ",");
        $sql .= " ) ";
        
        $sql .= ";";
        
        $this->db->queryNoResult($sql);
    }
    
    /*
     * Drop an index from a table
     *
     * Simulate an IF EXISTS clause that is missing in MySQL SQL dialect.
     *
     * @param string $table Fullname of a table
     * @param string $index Name of an index
     */
    private function dropIndex($table, $index)
    {
        // cf. https://dev.mysql.com/doc/refman/5.7/en/alter-table.html
        // cf. https://dev.mysql.com/doc/refman/5.7/en/drop-index.html
        
        if ($this->db->queryFetchFirstField("
                SELECT EXISTS(
                    SELECT 1
                    FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE
            	    	INDEX_SCHEMA LIKE '" . WIKINDX_DB . "'
                        AND LOWER(TABLE_NAME) LIKE LOWER('$table')
                        AND LOWER(INDEX_NAME) = LOWER('$index')
                ) AS IfIndexExists;
            ")
        ) {            
            if ($index == "PRIMARY")
                $sql = "ALTER TABLE " . $table . " DROP PRIMARY KEY;";
            else
                $sql = "ALTER TABLE " . $table . " DROP INDEX " . $index . ";";
            
            $this->db->queryNoResult($sql);
        }
    }
    
    /**
     * creatorsInit
     */
    public function creatorsInit()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingCreators'));
        $pString = '';
        if (array_key_exists('uuid', $this->vars)) {
	        $pString = \TEMPSTORAGE\fetchOne($this->db, $this->vars['uuid'], 'repairkitMessages');
        \TEMPSTORAGE\deleteKeys($this->db, $this->vars['uuid'], ['repaikitrMessages']);
		}
        $pString .= HTML\p($this->pluginmessages->text('preamble1'));
        $pString .= HTML\p($this->pluginmessages->text('preamble2'));
        GLOBALS::addTplVar('content', $pString);
        
        $pString  = HTML\p($this->pluginmessages->text('creatorsPreamble'));
        $pString .= FORM\formHeader("repairkit_creatorsFix");
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    
    /**
     * datetimeInit
     */
    public function datetimesInit()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingDatetimes'));
        $pString = '';
        if (array_key_exists('uuid', $this->vars)) {
	        $pString = \TEMPSTORAGE\fetchOne($this->db, $this->vars['uuid'], 'repairkitMessages');
        \TEMPSTORAGE\deleteKeys($this->db, $this->vars['uuid'], ['repaikitrMessages']);
		}
        $pString .= HTML\p($this->pluginmessages->text('preamble1'));
        $pString .= HTML\p($this->pluginmessages->text('preamble2'));
        GLOBALS::addTplVar('content', $pString);
        
        $pString  = "";
        $pString .= HTML\p($this->pluginmessages->text('datetimesPreamble'));
        
        if ($this->datetimesCheck())
        {
            // Database can be fixed
            $pString .= FORM\formHeader("repairkit_datetimesFix");
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "OK")));
            $pString .= FORM\formEnd();
        }
        else
        {
            $pString .= HTML\p($this->pluginmessages->text('noErrorsFound'), "bold");
        }
        
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * missingrowsInit
     */
    public function missingrowsInit()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingMissingrows'));
        $pString = '';
        if (array_key_exists('uuid', $this->vars)) {
	        $pString = \TEMPSTORAGE\fetchOne($this->db, $this->vars['uuid'], 'repairkitMessages');
        \TEMPSTORAGE\deleteKeys($this->db, $this->vars['uuid'], ['repaikitrMessages']);
		}
        $pString .= HTML\p($this->pluginmessages->text('preamble1'));
        $pString .= HTML\p($this->pluginmessages->text('preamble2'));
        
        GLOBALS::addTplVar('content', $pString);
        
        $pString  = HTML\p($this->pluginmessages->text('missingrowsPreamble'));
        $pString .= FORM\formHeader("repairkit_missingrowsFix");
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    
    /**
     * duplicateUsersInit
     */
    public function duplicateUsersInit()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingDuplicateUsers'));
        $pString = '';
        if (array_key_exists('uuid', $this->vars)) {
	        $pString = \TEMPSTORAGE\fetchOne($this->db, $this->vars['uuid'], 'repairkitMessages');
        \TEMPSTORAGE\deleteKeys($this->db, $this->vars['uuid'], ['repaikitrMessages']);
		}
        $pString .= HTML\p($this->pluginmessages->text('preamble1'));
        $pString .= HTML\p($this->pluginmessages->text('preamble2'));
        GLOBALS::addTplVar('content', $pString);
        
        $pString  = HTML\p($this->pluginmessages->text('duplicateUsersPreamble'));
        $pString  = HTML\p($this->pluginmessages->text('duplicateUsersPreamble2'), "bold");
        
        $rs = $this->db->query("
            SELECT
                usersId,
                usersUsername,
                usersPassword,
                usersFullname,
                usersEmail,
                usersTimestamp
            FROM users
            WHERE LOWER(usersUsername) IN (
                SELECT LOWER(usersUsername)
                FROM users
                GROUP BY LOWER(usersUsername)
                HAVING COUNT(*) > 1
            )
            ORDER BY usersUsername, usersId
        ");
        
        if (is_array($rs))
        {
            $pString .= FORM\formHeader("repairkit_duplicateUsersSelectFields");
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
            
            $pString .= \HTML\tableStart();
            
            $pString .= \HTML\theadStart();
                $pString .= \HTML\trStart();
                    $pString .= \HTML\th("Select user 1");
                    $pString .= \HTML\th("Select user 2");
                    foreach ($rs[0] as $key => $value)
                    {
                        $pString .= \HTML\th($key);
                    }
                $pString .= \HTML\trEnd();
            $pString .= \HTML\theadEnd();
            
            $pString .= \HTML\tbodyStart();
            
            $k = 0;
            foreach ($rs as $row)
            {
                $k++;
                $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                    $pString .= \HTML\td(\FORM\radioButton(FALSE, "usersId1", $row["usersId"]));
                    $pString .= \HTML\td(\FORM\radioButton(FALSE, "usersId2", $row["usersId"]));
                    foreach ($row as $v)
                    {
                        $pString .= \HTML\td($v);
                    }
                $pString .= \HTML\trEnd();
            }
            
            $pString .= \HTML\tbodyEnd();
            
            $pString .= \HTML\tableEnd();
            
            $pString .= FORM\formEnd();
        }
        else
        {
            $pString .= HTML\p($this->pluginmessages->text('noErrorsFound'), "bold");
        }
        
        GLOBALS::addTplVar('content', $pString);
    }
    
    /**
     * duplicateUsersSelectFields
     */
    public function duplicateUsersSelectFields()
    {
        $usersId1 = $this->vars['usersId1'] ?? "";
        $usersId2 = $this->vars['usersId2'] ?? "";
        
        if ($usersId1 == $usersId2 || $usersId1 == "" || $usersId2 == "")
        {
            $this->duplicateUsersInit();
            return;
        }
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingDuplicateUsers'));
        
        $pString = HTML\p($this->pluginmessages->text('preamble1'));
        $pString .= HTML\p($this->pluginmessages->text('preamble2'));
        GLOBALS::addTplVar('content', $pString);
        
        $pString  = HTML\p($this->pluginmessages->text('duplicateUsersPreamble'));
        $pString  = HTML\p($this->pluginmessages->text('duplicateUsersPreamble3'), "bold");
        
        $rs = $this->db->query("
            SELECT *
            FROM users
            WHERE usersId IN ($usersId1, $usersId2)
            ORDER BY usersId
        ");
        
        if (is_array($rs))
        {
            $user1 = $rs[0];
            $user2 = $rs[1];
            
            $pString .= FORM\formHeader("repairkit_duplicateUsersFix");
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
            
            $pString .= \FORM\hidden("user1", $user1["usersId"]);
            $pString .= \FORM\hidden("user2", $user2["usersId"]);
            
            $pString .= \HTML\tableStart();
            
            $pString .= \HTML\theadStart();
                $pString .= \HTML\trStart();
                    $pString .= \HTML\th("Field name");
                    $pString .= \HTML\th("User 1");
                    $pString .= \HTML\th("User 2");
                $pString .= \HTML\trEnd();
            $pString .= \HTML\theadEnd();
            
            $pString .= \HTML\tbodyStart();
            
            $k = 0;
            foreach (array_keys($user1) as $field)
            {
                if ($user1[$field] !== $user2[$field])
                {
                    $k++;
                    $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                        $pString .= \HTML\td($field);
                        $pString .= \HTML\td(
                            \FORM\radioButton(FALSE, $field, $user1["usersId"], TRUE)
                            . $user1[$field]
                        );
                        $pString .= \HTML\td(
                            \FORM\radioButton(FALSE, $field, $user2["usersId"])
                            . $user2[$field]
                        );
                    $pString .= \HTML\trEnd();
                }
            }
            
            $pString .= \HTML\tbodyEnd();
            
            $pString .= \HTML\tableEnd();
            
            $pString .= FORM\formEnd();
        }
        else
        {
            $pString .= HTML\p($this->pluginmessages->text('noErrorsFound'), "bold");
        }
        
        GLOBALS::addTplVar('content', $pString);
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
     * Find and fix missing rows
     */
    public function missingrowsFix()
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
        
        $string = $this->pluginmessages->text('missingRowsCount', $resources);
        $message = HTML\p($this->pluginmessages->text('success', $string), 'success', 'center');
        $uuid = \TEMPSTORAGE\getUuid($this->db);
    	\TEMPSTORAGE\store($this->db, $uuid, ['repairkitMessages' => $message]);
        header("Location: index.php?action=repairkit_missingrowsInit&uuid=$uuid");
        die;
    }
    /**
     * Fix various creator errors
     */
    public function creatorsFix()
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
            if (!array_key_exists($row['resourcecreatorCreatorMain'], $creatorIds))
            {
                continue;
            }
            $rcSurname = mb_strtolower(preg_replace("/[^[:alnum:][:space:]]/u", '', $row['resourcecreatorCreatorSurname']));
            if ($rcSurname != $creatorIds[$row['resourcecreatorCreatorMain']])
            {
                $this->db->formatConditions(['resourcecreatorCreatorMain' => $row['resourcecreatorCreatorMain']]);
                $this->db->update('resource_creator', ['resourcecreatorCreatorSurname' => $creatorIds[$row['resourcecreatorCreatorMain']]]);
            }
        }
        
        $message = HTML\p($this->pluginmessages->text('success'), 'success', 'center');
        $uuid = \TEMPSTORAGE\getUuid($this->db);
    	\TEMPSTORAGE\store($this->db, $uuid, ['repairkitMessages' => $message]);
        header("Location: index.php?action=repairkit_creatorsInit&uuid=$uuid");
        die;
    }
    /**
     * Deduplicate two users
     */
    public function duplicateUsersFix()
    {
        $this->errorsOn();
        
        $selection = $this->vars;
        $newusersId = $selection["usersId"];
        $oldusersId = $selection["usersId"] == $selection["user1"] ? $selection["user2"] : $selection["user1"];
        
        foreach ($selection as $k => $v)
        {
            // Forget data that is not a field selection
            if (!\UTILS\matchPrefix($k, "users"))
            {
                unset($selection[$k]);
            }
            // Forget fields that come from the target user account
            if ($v == $newusersId)
            {
                unset($selection[$k]);
            }
        }
        
        // Merge user account fields
        if (count($selection) > 0)
        {
            $sql = "UPDATE users SET ";
            foreach ($selection as $k => $v)
            {
                $sql .= " " . $k . " = " . $this->db->formatValues($this->db->queryFetchFirstField("SELECT " . $k . " FROM users WHERE usersId = " . $oldusersId)) . ", ";
            }
            $sql = rtrim($sql);
            $sql = rtrim($sql, ",");
            $sql .= " WHERE usersId = $newusersId;" . LF;
            
            $this->db->queryNoResult($sql);
        }
        
        // Remove the old user account
        $sql = "DELETE FROM users WHERE usersId = $oldusersId;";
        $this->db->queryNoResult($sql);
        
        // Merge oldusersId data from other tables
        $this->duplicateUsersMergeData($newusersId, $oldusersId);
        
        $message = HTML\p($this->pluginmessages->text('success'), 'success', 'center');
        $uuid = \TEMPSTORAGE\getUuid($this->db);
    	\TEMPSTORAGE\store($this->db, $uuid, ['repairkitMessages' => $message]);
        header("Location: index.php?action=repairkit_duplicateUsersInit&uuid=$uuid");
        die;
    }
    /**
     * Merge old user's data in various tables to the new user
     *
     * @param int $newusersId
     * @param int $oldusersId
     */
    private function duplicateUsersMergeData($newusersId, $oldusersId)
    {
    	// Merge from user_bibliography
		$updateArray = ['userbibliographyUserId' => $newusersId];
        $this->db->formatConditionsOneField($oldusersId, 'userbibliographyUserId');
        $this->db->update('user_bibliography', $updateArray);
        // Merge from user_groups
		$updateArray = ['usergroupsAdminId' => $newusersId];
        $this->db->formatConditionsOneField($oldusersId, 'usergroupsAdminId');
        $this->db->update('user_groups', $updateArray);
        // Merge from user_groups_users
		$updateArray = ['usergroupsusersUserId' => $newusersId];
        $this->db->formatConditionsOneField($oldusersId, 'usergroupsusersUserId');
        $this->db->update('user_groups_users', $updateArray);
        // Merge from user_keywordgroups
		$updateArray = ['userkeywordgroupsUserId' => $newusersId];
        $this->db->formatConditionsOneField($oldusersId, 'userkeywordgroupsUserId');
        $this->db->update('user_keywordgroups', $updateArray);
    	// Merge from user_tags
		$updateArray = ['usertagsUserId' => $newusersId];
        $this->db->formatConditionsOneField($oldusersId, 'usertagsUserId');
        $this->db->update('user_tags', $updateArray);
    	// Check and merge for any plugin tables with user rows
    	$tables = $this->db->listTables();
    	foreach ($tables as $table) {
    		if (strpos($table, 'plugin_') === 0) {
    			$userField = str_replace('_', '', $table) . 'UserId';
    			$fields = $this->db->listFields($table);
    			if (in_array($userField, $fields)) {
					$updateArray = [$userField => $newusersId];
        			$this->db->formatConditionsOneField($oldusersId, $userField);
        			$this->db->update($table, $updateArray);
    			}
    		}
    	}
    	// Set any resource_custom entries to $newusersId
		$updateArray = ['resourcecustomAddUserIdCustom' => $newusersId];
		$this->db->formatConditionsOneField($oldusersId, 'resourcecustomAddUserIdCustom');
		$this->db->update('resource_custom', $updateArray);
		$updateArray = ['resourcecustomEditUserIdCustom' => $newusersId];
		$this->db->formatConditionsOneField($oldusersId, 'resourcecustomEditUserIdCustom');
		$this->db->update('resource_custom', $updateArray);
    	// Set any resource_misc entries to $newusersId
		$updateArray = ['resourcemiscAddUserIdResource' => $newusersId];
		$this->db->formatConditionsOneField($oldusersId, 'resourcemiscAddUserIdResource');
		$this->db->update('resource_misc', $updateArray);
		$updateArray = ['resourcemiscEditUserIdResource' => $newusersId];
		$this->db->formatConditionsOneField($oldusersId, 'resourcemiscEditUserIdResource');
		$this->db->update('resource_misc', $updateArray);
    	// Set any resource_text entries to $newusersId
		$updateArray = ['resourcetextAddUserIdNote' => $newusersId];
		$this->db->formatConditionsOneField($oldusersId, 'resourcetextAddUserIdNote');
		$this->db->update('resource_text', $updateArray);
		$updateArray = ['resourcetextEditUserIdNote' => $newusersId];
		$this->db->formatConditionsOneField($oldusersId, 'resourcetextEditUserIdNote');
		$this->db->update('resource_text', $updateArray);
		$updateArray = ['resourcetextAddUserIdAbstract' => $newusersId];
		$this->db->formatConditionsOneField($oldusersId, 'resourcetextAddUserIdAbstract');
		$this->db->update('resource_text', $updateArray);
		$updateArray = ['resourcetextEditUserIdAbstract' => $newusersId];
		$this->db->formatConditionsOneField($oldusersId, 'resourcetextEditUserIdAbstract');
		$this->db->update('resource_text', $updateArray);
        // Manage deleted user's metadata
		$updateArray = ['resourcemetadataAddUserId' => $newusersId];
		$this->db->formatConditionsOneField($oldusersId, 'resourcemetadataAddUserId');
		$this->db->update('resource_metadata', $updateArray);
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
        $this->menus[$menuArray[0]]['repairkitpluginSub'][$this->pluginmessages->text('menuMissingrows')] = "missingrowsInit";
        $this->menus[$menuArray[0]]['repairkitpluginSub'][$this->pluginmessages->text('menuCreators')] = "creatorsInit";
        $this->menus[$menuArray[0]]['repairkitpluginSub'][$this->pluginmessages->text('menuDatetimes')] = "datetimesInit";
        $this->menus[$menuArray[0]]['repairkitpluginSub'][$this->pluginmessages->text('menuDuplicateUsers')] = "duplicateUsersInit";
        $this->menus[$menuArray[0]]['repairkitpluginSub'][$this->pluginmessages->text('menuDbIntegrity')] = "dbIntegrityInit";
    }
    /**
     * datetimesCheck
     *
     * @return bool
     */
    private function datetimesCheck()
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
        $this->db->formatConditions(['userregisterTimestamp' => '0000-00-00 00:00:00']);
        if ($this->db->numRows($this->db->select('user_register', 'userregisterId')))
        {
            return TRUE;
        }

        return FALSE;
    }
    /**
     * dbIntegrityCheck
     *
     * Error Codes:
     * - 0, OK db object
     * - 1, NOK db object (declaration mismatch)
     * - 2, Missing db object
     * - 3, Supernumerary db object
     *
     * @param array $currentDbSchema
     * @param array $correctDbSchema
     *
     * @return array
     */
    private function dbIntegrityCheck($currentDbSchema, $correctDbSchema)
    {
        $dbError = [
            "database" => [],
            "tables" => [],
            "fields" => [],
            "indices" => [],
            "count" => 0,
        ];

        // DATABASE
        if ($correctDbSchema["database"]["character_set"] != $currentDbSchema["database"]["character_set"])
        {
            $dbError["database"][] = [
                "Option" => "character_set",
                "Code" => 1,
            ];
            $dbError["count"]++;
        }
        if ($correctDbSchema["database"]["collation"] != $currentDbSchema["database"]["collation"])
        {
            $dbError["database"][] = [
                "Option" => "collation",
                "Code" => 1,
            ];
            $dbError["count"]++;
        }
        
        
        // TABLES
        $tableArrayCorrect = $correctDbSchema["tables"];
        $tableArrayCurrent = $currentDbSchema["tables"];
        
        foreach ($tableArrayCorrect as $tableCorrect)
        {
            // Table missing (by default)
            $match = 2;
            
            foreach ($tableArrayCurrent as $tableCurrent)
            {
                if (mb_strtolower($tableCorrect["Table"]) == mb_strtolower($tableCurrent["Table"]))
                {
                    // Table declaration matching (by default)
                    $match = 0;
                    
                    foreach ($tableCorrect as $key => $value)
                    {
                        if ($tableCorrect[$key] !== $tableCurrent[$key])
                        {
                            // Table declaration mismatching
                            $match = 1;
                            break;
                        }
                    }
                    break;
                }
            }
            
            if ($match > 0)
            {
                $dbError["tables"][] = [
                    "Table" => $tableCorrect["Table"],
                    "Code" => $match,
                ];
                $dbError["count"]++;
            }
        }
        
        foreach ($tableArrayCurrent as $tableCurrent)
        {
            // Field supernumerary (by default)
            $match = 3;
            
            foreach ($tableArrayCorrect as $tableCorrect)
            {
                if (mb_strtolower($tableCurrent["Table"]) == mb_strtolower($tableCorrect["Table"]))
                {
                    // Field present
                    $match = 0;
                    break;
                }
            }
            
            if ($match > 0)
            {
                // Provided the table is empty
                if ($this->db->tableIsEmpty($this->db->basicTable($tableCurrent["Table"])))
                {
                    $dbError["tables"][] = [
                        "Table" => $tableCurrent["Table"],
                        "Code" => $match,
                    ];
                    $dbError["count"]++;
                }
            }
        }
        
        
        // FIELDS
        $fieldArrayCorrect = $correctDbSchema["fields"];
        $fieldArrayCurrent = $currentDbSchema["fields"];
        
        foreach ($fieldArrayCorrect as $fieldCorrect)
        {
            // Field missing (by default)
            $match = 2;
            
            foreach ($fieldArrayCurrent as $fieldCurrent)
            {
                if (
                    mb_strtolower($fieldCorrect["Table"]) == mb_strtolower($fieldCurrent["Table"])
                    && mb_strtolower($fieldCorrect["Field"]) == mb_strtolower($fieldCurrent["Field"])
                ) {
                    // Field declaration matching (by default)
                    $match = 0;
                    
                    foreach ($fieldCorrect as $key => $value)
                    {
                        if ($fieldCorrect[$key] !== $fieldCurrent[$key] && $key != "Table")
                        {
                            if (
                                (mb_strtolower($fieldCorrect["Field"]) == "current_timestamp()" || mb_strtolower($fieldCorrect["Field"]) == "current_timestamp")
                                && (mb_strtolower($fieldCurrent["Field"]) == "current_timestamp()" || mb_strtolower($fieldCurrent["Field"]) == "current_timestamp")
                            ){
                                // current_timestamp() has two syntax (current_timestamp() or CURRENT_TIMESTAMP).
                                // It depends on whether you are on MySQL or MariaDB.
                                continue;
                            }
                            else
                            {
                                // Field declaration mismatching
                                $match = 1;
                                break;
                            }
                        }
                    }
                    
                    break;
                }
            }
            
            if ($match > 0)
            {
                $dbError["fields"][] = [
                    "Table" => $fieldCorrect["Table"],
                    "Field" => $fieldCorrect["Field"],
                    "Code" => $match,
                ];
                $dbError["count"]++;
            }
        }
        
        foreach ($fieldArrayCurrent as $fieldCurrent)
        {
            // Field supernumerary (by default)
            $match = 3;
            
            foreach ($fieldArrayCorrect as $fieldCorrect)
            {
                if (
                    mb_strtolower($fieldCurrent["Table"]) == mb_strtolower($fieldCorrect["Table"])
                    && mb_strtolower($fieldCurrent["Field"]) == mb_strtolower($fieldCorrect["Field"])
                ) {
                    // Field present
                    $match = 0;
                    break;
                }
            }
            
            if ($match > 0)
            {
                // Provided the table is empty
                if ($this->db->tableIsEmpty($this->db->basicTable($fieldCurrent["Table"])))
                {
                    $dbError["fields"][] = [
                        "Table" => $fieldCurrent["Table"],
                        "Field" => $fieldCurrent["Field"],
                        "Code" => $match,
                    ];
                    $dbError["count"]++;
                }
            }
        }
        
        
        // INDICES
        $indexArrayCorrect = $correctDbSchema["indices"];
        $indexArrayCurrent = $currentDbSchema["indices"];
        
        foreach ($indexArrayCorrect as $indexCorrect)
        {
            // Index missing (by default)
            $match = 2;
            
            foreach ($indexArrayCurrent as $indexCurrent)
            {
                if (
                    mb_strtolower($indexCorrect["Table"]) == mb_strtolower($indexCurrent["Table"])
                    && mb_strtolower($indexCorrect["Key_name"]) == mb_strtolower($indexCurrent["Key_name"])
                    && mb_strtolower($indexCorrect["Seq_in_index"]) == mb_strtolower($indexCurrent["Seq_in_index"])
                ) {
                    // Index declaration matching (by default)
                    $match = 0;
                    
                    foreach ($indexCorrect as $key => $value)
                    {
                        if ($indexCorrect[$key] !== $indexCurrent[$key] && $key != "Table")
                        {
                            // Index declaration mismatching
                            $match = 1;
                            break;
                        }
                    }
                    
                    break;
                }
            }
            
            if ($match > 0)
            {
                $dbError["indices"][] = [
                    "Table" => $indexCorrect["Table"],
                    "Key_name" => $indexCorrect["Key_name"],
                    "Seq_in_index" => $indexCorrect["Seq_in_index"],
                    "Code" => $match,
                ];
                $dbError["count"]++;
            }
        }
        
        foreach ($indexArrayCurrent as $indexCurrent)
        {
            // Index supernumerary (by default)
            $match = 3;
            
            foreach ($indexArrayCorrect as $indexCorrect)
            {
                if (
                    mb_strtolower($indexCurrent["Table"]) == mb_strtolower($indexCorrect["Table"])
                    && mb_strtolower($indexCurrent["Key_name"]) == mb_strtolower($indexCorrect["Key_name"])
                ) {
                    // Index declaration matching
                    $match = 0;
                    break;
                }
            }
            
            if ($match > 0)
            {
                $dbError["indices"][] = [
                    "Table" => $indexCurrent["Table"],
                    "Key_name" => $indexCurrent["Key_name"],
                    "Seq_in_index" => $indexCurrent["Seq_in_index"],
                    "Code" => $match,
                ];
                $dbError["count"]++;
            }
        }

        return $dbError;
    }
    
    /**
     * dbIntegrityDisplay
     *
     * @param array $dbErrors
     * @param array $currentDbSchema
     * @param array $correctDbSchema
     *
     * @return bool
     */
    private function dbIntegrityDisplay($dbErrors, $currentDbSchema, $correctDbSchema)
    {
        $nbErrorDb = 0;
        $nbErrorTable = 0;
        $nbErrorField = 0;
        $nbErrorIndex = 0;
        
        $pString = "
            <style>
                .tcaption {
                    font-style: italic;
                }
                .ok {
                    color: green;
                }
                .nok {
                    color: red;
                }
                .missing {
                    color: brown;
                }
                .supernumerary {
                    color: blue;
                }
            </style>";
        GLOBALS::addTplVar('content', $pString);


        // DATABASE
        $pString  = \HTML\tableStart();
        
        $pString .= \HTML\tableCaption($this->pluginmessages->text('dbIntegrityCaptionDb'), "tcaption");
        
        $pString .= \HTML\theadStart();
            $pString .= \HTML\trStart();
            $pString .= \HTML\th("Option");
            $pString .= \HTML\th("Value");
            $pString .= \HTML\th("Integrity");
            $pString .= \HTML\trEnd();
        $pString .= \HTML\theadEnd();
        
        $pString .= \HTML\tbodyStart();
        
        if (count($dbErrors["database"]) > 0)
        {
            $k = 0;
            foreach ($dbErrors["database"] as $e)
            {
                $k++;
                
                if ($e["Code"] == 1)
                {
                    $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                        $pString .= \HTML\td($e["Option"]);
                        $pString .= \HTML\td("'" . $currentDbSchema["database"][$e["Option"]] . "' instead of '" . $correctDbSchema["database"][$e["Option"]] . "'", "nok");
                        $pString .= \HTML\td("Mismatch", "nok");
                    $pString .= \HTML\trEnd();
                }
            }
        }
        else
        {
            $pString .= \HTML\trStart("alternate1 center");
                $pString .= \HTML\td($this->pluginmessages->text('noErrorsFound'), "ok", 3);
            $pString .= \HTML\trEnd();
        }
        
        $pString .= \HTML\tbodyEnd();

        $pString .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
        
        
        // TABLES
        $tableArrayCorrect = $correctDbSchema["tables"];
        $tableArrayCurrent = $currentDbSchema["tables"];
        
        $pString  = \HTML\tableStart();
        
        $pString .= \HTML\tableCaption($this->pluginmessages->text('dbIntegrityCaptionTables'), "tcaption");
        
        $pString .= \HTML\theadStart();
            $pString .= \HTML\trStart();
            foreach ($tableArrayCorrect[0] as $key => $value)
            {
                $pString .= \HTML\th($key);
            }
            $pString .= \HTML\th("Integrity");
            $pString .= \HTML\trEnd();
        $pString .= \HTML\theadEnd();
        
        $pString .= \HTML\tbodyStart();
        
        if (count($dbErrors["tables"]) > 0)
        {
            $k = 0;
            foreach ($dbErrors["tables"] as $e)
            {
                $k++;
                $tableCorrect = [];
                $tableCurrent = [];
                
                foreach($tableArrayCorrect as $t)
                {
                    if ($t["Table"] == $e["Table"])
                    {
                        $tableCorrect = $t;
                    }
                }
                
                foreach($tableArrayCurrent as $t)
                {
                    if (mb_strtolower($t["Table"]) == mb_strtolower($e["Table"]))
                    {
                        $tableCurrent = $t;
                    }
                }
                
                if ($e["Code"] == 1)
                {
                    // Table definition mismatch
                    $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                        foreach ($tableCorrect as $key => $value)
                        {
                            if ($tableCorrect[$key] === $tableCurrent[$key])
                                $pString .= \HTML\td($value, "ok");
                            else
                                $pString .= \HTML\td("'" . $tableCurrent[$key] . "' instead of '" . $value . "'", "nok");
                        }
                        $pString .= \HTML\td("Mismatch", "nok");
                    $pString .= \HTML\trEnd();
                }
                elseif ($e["Code"] == 2)
                {
                    // Table missing
                    $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                        foreach ($tableCorrect as $v)
                        {
                            $pString .= \HTML\td($v, "missing");
                        }
                        $pString .= \HTML\td("Missing", "missing");
                    $pString .= \HTML\trEnd();
                }
                elseif ($e["Code"] == 3)
                {
                    // Table missing
                    $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                        foreach ($tableCurrent as $v)
                        {
                            $pString .= \HTML\td($v, "supernumerary");
                        }
                        $pString .= \HTML\td("Supernumerary", "supernumerary");
                    $pString .= \HTML\trEnd();
                }
            }
        }
        else
        {
            $pString .= \HTML\trStart("alternate1 center");
                $pString .= \HTML\td($this->pluginmessages->text('noErrorsFound'), "ok", count($tableArrayCorrect[0]) + 1);
            $pString .= \HTML\trEnd();
        }
        
        $pString .= \HTML\tbodyEnd();
        
        $pString .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
        
        
        // FIELDS
        $fieldArrayCorrect = $correctDbSchema["fields"];
        $fieldArrayCurrent = $currentDbSchema["fields"];
        
        $pString  = \HTML\tableStart();
        
        $pString .= \HTML\tableCaption($this->pluginmessages->text('dbIntegrityCaptionFields'), "tcaption");
        
        $pString .= \HTML\theadStart();
            $pString .= \HTML\trStart();
            foreach ($fieldArrayCorrect[0] as $key => $value)
            {
                $pString .= \HTML\th($key);
            }
            $pString .= \HTML\th("Integrity");
            $pString .= \HTML\trEnd();
        $pString .= \HTML\theadEnd();
        
        $pString .= \HTML\tbodyStart();
        
        if (count($dbErrors["fields"]) > 0)
        {
            $k = 0;
            foreach ($dbErrors["fields"] as $e)
            {
                $k++;
                $fieldCorrect = [];
                $fieldCurrent = [];
                
                foreach($fieldArrayCorrect as $f)
                {
                    if ($f["Table"] == $e["Table"] && $f["Field"] == $e["Field"])
                    {
                        $fieldCorrect = $f;
                    }
                }
                
                foreach($fieldArrayCurrent as $f)
                {
                    if (
                        mb_strtolower($f["Table"]) == mb_strtolower($e["Table"])
                        && mb_strtolower($f["Field"]) == mb_strtolower($e["Field"])
                    ) {
                        $fieldCurrent = $f;
                    }
                }
                
                if ($e["Code"] == 1)
                {
                    // Field definition mismatch
                    $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                        foreach ($fieldCorrect as $key => $value)
                        {
                            if ($fieldCorrect[$key] === $fieldCurrent[$key])
                                $pString .= \HTML\td($value, "ok");
                            else
                                $pString .= \HTML\td("'" . $fieldCurrent[$key] . "' instead of '" . $value . "'", "nok");
                        }
                        $pString .= \HTML\td("Mismatch", "nok");
                    $pString .= \HTML\trEnd();
                }
                elseif ($e["Code"] == 2)
                {
                    // Field missing
                    $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                        foreach ($fieldCorrect as $v)
                        {
                            $pString .= \HTML\td($v, "missing");
                        }
                        $pString .= \HTML\td("Missing", "missing");
                    $pString .= \HTML\trEnd();
                }
                elseif ($e["Code"] == 3)
                {
                    // Field supernumerary
                    $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                        foreach ($fieldCurrent as $v)
                        {
                            $pString .= \HTML\td($v, "supernumerary");
                        }
                        $pString .= \HTML\td("Supernumerary", "supernumerary");
                    $pString .= \HTML\trEnd();
                }
            }
        }
        else
        {
            $pString .= \HTML\trStart("alternate1 center");
                $pString .= \HTML\td($this->pluginmessages->text('noErrorsFound'), "ok", count($fieldArrayCorrect[0]) + 1);
            $pString .= \HTML\trEnd();
        }

        $pString .= \HTML\tbodyEnd();
        
        $pString .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
        
        
        // INDICES
        $indexArrayCorrect = $correctDbSchema["indices"];
        $indexArrayCurrent = $currentDbSchema["indices"];
        
        $pString  = \HTML\tableStart();
        
        $pString .= \HTML\tableCaption($this->pluginmessages->text('dbIntegrityCaptionFields'), "tcaption");
        
        $pString .= \HTML\theadStart();
            $pString .= \HTML\trStart();
            foreach ($indexArrayCorrect[0] as $key => $value)
            {
                $pString .= \HTML\th($key);
            }
            $pString .= \HTML\th("Integrity");
            $pString .= \HTML\trEnd();
        $pString .= \HTML\theadEnd();
        
        $pString .= \HTML\tbodyStart();
        
        if (count($dbErrors["indices"]) > 0)
        {
            $k = 0;
            foreach ($dbErrors["indices"] as $e)
            {
                $k++;
                $indexCorrect = [];
                $indexCurrent = [];
                
                foreach($indexArrayCorrect as $i)
                {
                    if (
                        $i["Table"] == $e["Table"]
                        && $i["Key_name"] == $e["Key_name"]
                        && $i["Seq_in_index"] == $e["Seq_in_index"]
                    ) {
                        $indexCorrect = $i;
                    }
                }
                
                foreach($indexArrayCurrent as $i)
                {
                    if (
                        mb_strtolower($i["Table"]) == mb_strtolower($e["Table"])
                        && mb_strtolower($i["Key_name"]) == mb_strtolower($e["Key_name"])
                        && mb_strtolower($i["Seq_in_index"]) == mb_strtolower($e["Seq_in_index"])
                    ) {
                        $indexCurrent = $i;
                    }
                }
                
                if ($e["Code"] == 1)
                {
                    // Index definition mismatch
                    $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                        foreach ($indexCorrect as $key => $value)
                        {
                            if ($indexCorrect[$key] === $indexCurrent[$key])
                                $pString .= \HTML\td($value, "ok");
                            else
                                $pString .= \HTML\td("'" . $indexCurrent[$key] . "' instead of '" . $value . "'", "nok");
                        }
                        $pString .= \HTML\td("Mismatch", "nok");
                    $pString .= \HTML\trEnd();
                }
                elseif ($e["Code"] == 2)
                {
                    // Index missing
                    $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                        foreach ($indexCorrect as $v)
                        {
                            $pString .= \HTML\td($v, "missing");
                        }
                        $pString .= \HTML\td("Missing", "missing");
                    $pString .= \HTML\trEnd();
                }
                elseif ($e["Code"] == 3)
                {
                    // Index supernumerary
                    $pString .= \HTML\trStart("alternate" . ($k % 2 ? "1" : "2"));
                        foreach ($indexCurrent as $v)
                        {
                            $pString .= \HTML\td($v, "supernumerary");
                        }
                        $pString .= \HTML\td("Supernumerary", "supernumerary");
                    $pString .= \HTML\trEnd();
                }
            }
        }
        else
        {
            $pString .= \HTML\trStart("alternate1 center");
                $pString .= \HTML\td($this->pluginmessages->text('noErrorsFound'), "ok", count($indexArrayCorrect[0]) + 1);
            $pString .= \HTML\trEnd();
        }
        
        $pString .= \HTML\tbodyEnd();
        
        $pString .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
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
                {
                    // i.e. no indices in table in correct database structure
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
    public function datetimesFix()
    {
        $this->errorsOn();
        
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
        
        $message = HTML\p($this->pluginmessages->text('success'), 'success', 'center');
        $uuid = \TEMPSTORAGE\getUuid($this->db);
    	\TEMPSTORAGE\store($this->db, $uuid, ['repairkitMessages' => $message]);
        header("Location: index.php?action=repairkit_datetimesInit&uuid=$uuid");
        die;
    }
    /**
     * Turn error reporting on
     */
    private function errorsOn()
    {
        ini_set('display_errors', TRUE);
    }
}
