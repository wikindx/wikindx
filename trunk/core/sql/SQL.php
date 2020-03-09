<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * SQL
 *
 * SQL abstraction layer for using MySQL.
 *
 * @package wikindx\core\sql
 */
class SQL
{
    /** string */
    public $error = ""; // Error message returned by db drivers or Wikindx
    /** integer */
    public $errno = 0; // Error code returned by db drivers
    /** string */
    public $conditionSeparator;
    /** string */
    public $multiConditionSeparator;
    /** string */
    public $ascDesc;

    // Define some ANSI SQL keywords
    /** string */
    public $or = ' OR ';
    /** string */
    public $and = ' AND ';
    /** string */
    public $asc = ' ASC';
    /** string */
    public $desc = ' DESC';
    /** string */
    public $equal = ' = ';
    /** string */
    public $notEqual = ' <> ';
    /** string */
    public $greater = ' > ';
    /** string */
    public $less = ' < ';
    /** string */
    public $greaterEqual = ' >= ';
    /** string */
    public $lessEqual = ' <= ';
    /** string */
    public $plus = ' + ';
    /** string */
    public $minus = ' - ';
    /** string */
    public $alias = ' AS ';
    /** string */
    public $from = ' FROM ';

    /** array */
    public $condition = [];
    /** array */
    public $join = [];
    /** array */
    public $order = [];
    /** string */
    public $group = FALSE;
    /** string */
    public $limit = FALSE;
    /** array */
    private $joinUpdate = [];
    /** object */
    private $errors;
    /** object */
    private $handle = NULL;
    /** mixed */
    private $startTimer;
    /** mixed */
    private $endTimer;

    /**
     * SQL
     */
    public function __construct()
    {
        $this->errors = FACTORY_ERRORS::getInstance();

        $this->open();

        $this->conditionSeparator = $this->multiConditionSeparator = $this->and;
        $this->ascDesc = $this->asc;
    }
    /**
     * Get database engine version as number
     *
     * @return int
     */
    public function getNumberEngineVersion()
    {
        return mysqli_get_server_version($this->handle);
    }
    /**
     * Get database engine version as string
     *
     * @return string
     */
    public function getStringEngineVersion()
    {
        $EngineVersion = $this->queryFetchFirstRow("SELECT version() AS EngineVersion;");
        if (array_key_exists("EngineVersion", $EngineVersion)) {
            return $EngineVersion["EngineVersion"];
        } else {
            return "";
        }
    }
    /**
     * Close SQL database
     *
     * @return bool
     */
    public function close()
    {
        return mysqli_close($this->handle);
    }

    /**
     * Add / enable an SQL mode of MySQL engine
     *
     * @param string $SqlMode
     */
    public function enableSqlMode(string $SqlMode)
    {
        $this->queryNoResult("SET @@sql_mode = CONCAT(@@sql_mode, '," . $this->escapeString($SqlMode) . "');");
    }

    /**
     * Remove / disable an SQL mode of MySQL engine
     *
     * @param string $SqlMode
     */
    public function disableSqlMode(string $SqlMode)
    {
        $this->queryNoResult("SET sql_mode = (SELECT REPLACE(@@sql_mode, '" . $this->escapeString($SqlMode) . "', ''));");
    }

    /**
     * Set an SQL mode of MySQL engine
     *
     * @param string $SqlMode
     */
    public function setSqlMode(string $SqlMode)
    {
        $this->queryNoResult("SET sql_mode = '" . $this->escapeString($SqlMode) . "';");
    }
    /**
     * Fetch MySQL server max_allowed_packet variable
     *
     * @return int
     */
    public function getMaxPacket()
    {
        $value = 0;

        $row = $this->queryFetchFirstRow("SHOW VARIABLES LIKE 'max_allowed_packet';");

        if (is_array($row)) {
            $value = $row['Value'];
        }

        unset($row);

        return $value;
    }
    /**
     * Set MySQL server max_allowed_packet variable
     *
     * Corrects the value according to the constraints described in the MySQL documentation.
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_max_allowed_packet
     *
     * @param int $size Packet size in bytes
     */
    public function setMaxPacket(int $size)
    {
        // Must be multiples of 1024
        $mul = $size % 1024;
        $mul = ($mul * 1024 < $size) ? $mul + 1 : $mul;
        $size = $mul * 1024;
        
        // Correct size within authorized limits
        $size = $size < 1024 ? 1024 : $size;
        $size = $size > 1073741824 ? 1073741824 : $size;
        
        $this->queryNoError("SET @@global.max_allowed_packet = $size");
    }
    /**
     * create the entire querystring but do not execute
     *
     * @param string $querystring
     *
     * @return string
     */
    public function queryNoExecute(string $querystring)
    {
        $querystring .= $this->subClause();

        $this->printSQLDebug($querystring, 'queryNoExecute');

        $this->resetSubs();

        return $querystring;
    }
    /**
     * execute queries and return recordset
     *
     * @param string $querystring
     * @param bool $saveSession Default is FALSE
     *
     * @return mixed An array, or a boolean if there are no data to return. Only the first result set is returned
     */
    public function query(string $querystring, bool $saveSession = FALSE)
    {
        return $this->internalQuery($querystring, FALSE);
    }
    /**
     * Execute queries and return recordset
     *
     * Ignore error warnings
     *
     * @param string $querystring
     *
     * @return mixed An array, or a boolean if there are no data to return. Only the first result set is returned
     */
    public function queryNoError(string $querystring)
    {
        return $this->internalQuery($querystring, TRUE);
    }
    /**
     * Execute queries and return TRUE for success, FALSE if the query failed
     *
     * @param string $querystring
     * @param bool $saveSession Default is FALSE
     *
     * @return bool
     */
    public function queryNoResult(string $querystring, bool $saveSession = FALSE)
    {
        return ($this->query($querystring, $saveSession) !== FALSE);
    }
    /**
     * Execute queries, fetch only the first row of the result and return it
     *
     * @param string $querystring
     * @param bool $saveSession Default is FALSE
     *
     * @return array
     */
    public function queryFetchFirstRow(string $querystring, bool $saveSession = FALSE)
    {
        $recordset = $this->query($querystring, $saveSession);

        return $this->fetchRow($recordset);
    }
    /**
     * Execute queries, fetch only the first field of the first row of the result and return it
     *
     * @param string $querystring
     * @param bool $saveSession Default is FALSE
     *
     * @return mixed
     */
    public function queryFetchFirstField(string $querystring, bool $saveSession = FALSE)
    {
        $recordset = $this->query($querystring, $saveSession);

        return $this->fetchOne($recordset);
    }
    /**
     * reset various strings and arrays used in subclauses
     */
    public function resetSubs()
    {
        $this->join = [];
        $this->multiConditionSeparator = $this->and;
        $this->conditionSeparator = $this->and;
        $this->condition = [];
        $this->group = FALSE;
        $this->order = [];
        $this->ascDesc = $this->asc;
        $this->limit = FALSE;
    }
    /**
     * Create a db schema in an array for the Repair Kit plugin from the current database
     *
     * @return array Schema formated in an array
     */
    public function createRepairKitDbSchema()
    {
        $schema = [];
        
        $tables = $this->listTables();
        foreach ($tables as $table) {
            $basicTable = preg_replace("/^" . preg_quote(WIKINDX_DB_TABLEPREFIX, "/") . "/ui", '', $table);
            
            if (strpos($basicTable, 'plugin_') === 0) {
                continue; // ignore plugin tables
            }
            
            // Extract fields schema
            $result = $this->query("DESCRIBE " . $table);
            $result = (array)$result;
            $schema[$basicTable]['fields'][] = $result;
            
            // Extract tables schema
            $result = $this->query("
                SELECT *
                FROM INFORMATION_SCHEMA.TABLES
    			WHERE
		        	TABLE_TYPE = 'BASE TABLE'
	    			AND TABLE_SCHEMA LIKE '" . WIKINDX_DB . "'
	    			AND LOWER(TABLE_NAME) LIKE LOWER('$table')
            ");
            $result = (array)$result;
            $schema[$basicTable]['schema'][] = $result;
            
            // Extract indices schema
            $result = $this->query("SHOW INDEX FROM $table FROM " . WIKINDX_DB);
            $result = (array)$result;
            $schema[$basicTable]['indices'][] = $result;
        }
        
        return $schema;
    }
    /**
     * Read a db schema formated for the Repair Kit plugin in an array
     *
     * @param string $fileName Destination filename (absolute or relative)
     *
     * @return mixed Schema formated in an array, or FALSE on error
     */
    public function getRepairKitDbSchema(string $filename)
    {
        $dbSchema = FALSE;
        
        $data = file_get_contents($filename);
        if ($data !== FALSE) {
            $dbSchema = unserialize($data);
        }
        
        return $dbSchema;
    }
    /**
     * Write an array of db schema formated for the Repair Kit plugin to a file
     *
     * @param array $dbSchema Schema formated in an array
     * @param string $filename Destination filename (absolute or relative)
     *
     * @return bool TRUE on success, FALSE on error
     */
    public function writeRepairKitDbSchema(array $dbSchema, string $filename)
    {
        return (file_put_contents($filename, serialize($dbSchema)) !== FALSE);
    }
    /**
     * List fields in a database table
     *
     * @see SQL::listFields()
     *
     * @param string $table Name of a table (without prefix)
     *
     * @return array
     */
    public function listFields(string $table)
    {
        $fields = [];
        
        // For ANSI behavior (MySQL, PG at least)
        // We must always use TABLE_SCHEMA in the WHERE clause
        // and the raw value of TABLE_SCHEMA otherwise MySQL scans
        // the disk for db names and slow down the server
        // https://dev.mysql.com/doc/refman/5.7/en/information-schema-optimization.html
        $recordset = $this->query("
		    SELECT COLUMN_NAME
		    FROM INFORMATION_SCHEMA.COLUMNS
		    WHERE
		        TABLE_SCHEMA = '" . WIKINDX_DB . "'
		        AND LOWER(TABLE_NAME) = LOWER('" . WIKINDX_DB_TABLEPREFIX . $table . "');
		");

        if ($recordset !== FALSE) {
            while ($field = $this->fetchRow($recordset)) {
                $fields[] = $field['COLUMN_NAME'];
                unset($field);
            }
        }

        return $fields;
    }
    /**
     * show all tables in db
     *
     * @param bool $withPrefix Keep the prefix of tables. Default is TRUE.
     *
     * @return array
     */
    public function listTables(bool $withPrefix = TRUE)
    {
        $tables = [];

        // For ANSI behavior (MySQL, PG at least)
        // We must always use TABLE_SCHEMA in the WHERE clause
        // and the raw value of TABLE_SCHEMA otherwise MySQL scans
        // the disk for db names and slow down the server
        // https://dev.mysql.com/doc/refman/5.7/en/information-schema-optimization.html
        $recordset = $this->query("
		    SELECT TABLE_NAME
		    FROM INFORMATION_SCHEMA.TABLES
		    WHERE
		        TABLE_TYPE = 'BASE TABLE'
		        AND TABLE_SCHEMA = '" . WIKINDX_DB . "'
		        AND LOWER(TABLE_NAME) LIKE CONCAT(LOWER('" . WIKINDX_DB_TABLEPREFIX . "'), '%');
		");

        if ($recordset !== FALSE) {
            while ($table = $this->fetchRow($recordset)) {
                $t = $table['TABLE_NAME'];
                if (!$withPrefix) {
                    $t = preg_replace("/^" . preg_quote(WIKINDX_DB_TABLEPREFIX, "/") . "/ui", '', $t);
                }
                $tables[] = $t;
                unset($table);
            }
        }

        return $tables;
    }
    /**
     * Check if a table exists in the current database
     *
     * @param string $table
     *
     * @return array
     */
    public function tableExists(string $table)
    {
        // We must always use TABLE_SCHEMA in the WHERE clause
        // and the raw value of TABLE_SCHEMA otherwise MySQL scans
        // the disk for db names and slow down the server
        // https://dev.mysql.com/doc/refman/5.7/en/information-schema-optimization.html
        return $this->queryFetchFirstField("
			SELECT EXISTS(
				SELECT 1
				FROM INFORMATION_SCHEMA.TABLES
				WHERE
					TABLE_TYPE = 'BASE TABLE'
					AND TABLE_SCHEMA = '" . WIKINDX_DB . "'
					AND LOWER(TABLE_NAME) = LOWER('" . WIKINDX_DB_TABLEPREFIX . $table . "')
			);
		");
    }

    /**
     * Is a table Empty?
     *
     * @param string $table
     *
     * @return bool
     */
    public function tableIsEmpty(string $table)
    {
        return $this->queryFetchFirstField('SELECT NOT EXISTS(SELECT 1 FROM ' . $this->formatTables($table) . ') AS IsEmpty;'); // ANSI SQL
    }
    /**
     * Create a table
     *
     * @param string $newTable
     * @param array $fieldsArray
     * @param bool $tempTable
     */
    public function createTable(string $newTable, array $fieldsArray, bool $tempTable = FALSE)
    {
        $newTable = WIKINDX_DB_TABLEPREFIX . $newTable;
        $sql = '(' . implode(', ', $fieldsArray) . ')';
        $sql .= 'ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci';
        if ($tempTable) {
            $this->queryNoResult("CREATE TEMPORARY TABLE `$newTable` $sql");
        } else {
            $this->queryNoResult("CREATE TABLE `$newTable` $sql");
        }
    }
    /**
     * Create a temporary table from a SELECT statement
     *
     * @param string $newTable
     * @param string $selectStmt
     */
    public function createTempTableFromSelect(string $newTable, string $selectStmt)
    {
        $newTable = WIKINDX_DB_TABLEPREFIX . $newTable;
        $sql = ' AS (' . $selectStmt . ')';
        $this->queryNoResult("CREATE TEMPORARY TABLE `$newTable` $sql");
    }
    /**
     * return numRows from recordset
     *
     * @param array $recordset
     *
     * @return int
     */
    public function numRows($recordset)
    {
        return is_array($recordset) ? count($recordset) : 0;
    }
    /**
     * Adjusts the result pointer to an arbitrary row in the resultset
     *
     * @param array $recordset
     * @param int $rowNumber
     */
    public function goToRow(&$recordset, int $rowNumber)
    {
        if (is_array($recordset)) {
            // Move to the first element
            reset($recordset);

            for ($k = 1; $k < $rowNumber; $k++) {
                next($recordset);
            }
        }
    }
    /**
     * Fetch one row from the database
     *
     * @param array $recordset
     *
     * @return array
     */
    public function fetchRow(&$recordset)
    {
        if (is_array($recordset)) {
            $row = current($recordset);
            next($recordset);
        } else {
            $row = FALSE;
        }

        return $row;
    }
    /**
     * Fetch one field value from the database
     *
     * @param array $recordset
     *
     * @return string
     */
    public function fetchOne($recordset)
    {
        $value = NULL;

        if (is_array($recordset)) {
            $row = current($recordset);
            if (is_array($row)) {
                foreach ($row as $v) {
                    $value = $v;
                }
            }
        }

        return $value;
    }
    /**
     * Fetch fields properties of a recordset
     *
     * @param string $table Name of a table (without prefix)
     *
     * @return array
     */
    public function getFieldsProperties(string $table)
    {
        // For ANSI behavior (MySQL, PG at least)
        // We must always use TABLE_SCHEMA in the WHERE clause
        // and the raw value of TABLE_SCHEMA otherwise MySQL scans
        // the disk for db names and slow down the server
        // https://dev.mysql.com/doc/refman/5.7/en/information-schema-optimization.html
        $recordset = $this->query("
        	SELECT *
        	FROM INFORMATION_SCHEMA.COLUMNS
        	WHERE
	        	TABLE_SCHEMA = '" . WIKINDX_DB . "'
	        	AND LOWER(TABLE_NAME) = LOWER('" . WIKINDX_DB_TABLEPREFIX . $table . "');
        ");

        return $recordset;
    }
    /**
     * Create a UNION sub query -- MySQL 4.1 and above.
     *
     * @param mixed $stmt string or array select statement(s) to be unionized
     * @param bool $all Default FALSE.  Set to TRUE to have 'UNION ALL'
     *
     * @return string
     */
    public function union($stmt, bool $all = FALSE)
    {
        $all = $all ? 'ALL' : '';

        if (is_array($stmt)) {
            return implode(" UNION $all ", $stmt);
        } else {
            return " UNION $all $stmt";
        }
    }
    /**
     * Create a subquery from a SQL statement
     *
     * @param string $stmt Pre-defined SQL stmt (which may be a subquery itself)
     * @param false|string $alias Boolean table alias sometimes required for subquery SELECT statements.  Default is FALSE
     * @param bool $from TRUE If FALSE, don't add the initial 'FROM'
     * @param bool $clause Default is FALSE. If TRUE, add all conditions, joins, groupBy, orderBy etc. clauses
     *
     * @return string
     */
    public function subQuery($stmt, $alias = FALSE, bool $from = TRUE, bool $clause = FALSE)
    {
        if (!$stmt) {
            $this->error = $this->errors->text("dbError", "subQuery");
        }
        if ($clause) {
            $stmt .= $this->subClause();

            $this->resetSubs();
        }

        $from = $from ? 'FROM ' : '';
        if ($alias) {
            $alias = ' AS ' . $this->formatTables($alias);
        }

        return "$from($stmt) $alias";
    }
    /**
     * Create a subquery from SQL fields
     *
     * If $alias is FALSE: 'FROM (SELECT $distinct $field $subquery $clause)'
     * If $alias is TRUE: 'FROM (SELECT $distinct $field $subquery $clause) AS '
     *
     * @param array $fields
     * @param string $subquery Formatted subquery string
     * @param false|string $alias Boolean table alias sometimes required for subquery SELECT statements.  Default is FALSE
     * @param bool $clause Default is FALSE. If TRUE, add all conditions, joins, groupBy, orderBy etc. clauses
     * @param bool $distinct Default is FALSE
     * @param bool $tidy Format fields for SQL queries. Default is TRUE
     *
     * @return string
     */
    public function subQueryFields($fields, $subquery, $alias = FALSE, bool $clause = FALSE, bool $distinct = FALSE, bool $tidy = TRUE)
    {
        if ($clause) {
            $clause = $this->subClause();

            $this->resetSubs();
        }

        $distinct = $distinct ? 'DISTINCT' : '';
        if ($alias) {
            $alias = ' AS ' . $this->formatTables($alias);
        }
        if ($tidy) {
            $fields = $this->formatFields($fields);
        }

        return "FROM (SELECT $distinct $fields $subquery $clause) $alias";
    }
    /**
     * Execute SELECT statement
     *
     * @param array $tables
     * @param mixed $fields Array of fields or can be '*'
     * @param bool $distinct Default is FALSE
     * @param bool $tidyFields Format fields for SQL. Default is TRUE
     * @param false|string $alias Default is FALSE
     * @param string $clause Default is FALSE
     *
     * @return object recordset
     */
    public function select($tables, $fields, $distinct = FALSE, $tidyFields = TRUE, $alias = FALSE, $clause = FALSE)
    {
        return $this->query($this->selectNoExecute($tables, $fields, $distinct, $tidyFields, $clause, $alias));
    }
    /**
     * Execute SELECT statement and return the first row
     *
     * @param array $tables
     * @param mixed $fields Array of fields or can be '*'
     * @param bool $distinct Default is FALSE
     * @param bool $tidyFields Format fields for SQL. Default is TRUE
     * @param bool $alias Default is FALSE
     * @param string $clause Default is FALSE
     *
     * @return array
     */
    public function selectFirstRow($tables, $fields, $distinct = FALSE, $tidyFields = TRUE, $alias = FALSE, $clause = FALSE)
    {
        return $this->queryFetchFirstRow($this->selectNoExecute($tables, $fields, $distinct, $tidyFields, $clause, $alias));
    }
    /**
     * Execute SELECT statement and return the first field of the first row
     *
     * @param array $tables
     * @param mixed $fields Array of fields or can be '*'
     * @param bool $distinct Default is FALSE
     * @param bool $tidyFields Format fields for SQL. Default is TRUE
     * @param false|string $alias Default is FALSE
     * @param string $clause Default is FALSE
     *
     * @return mixed
     */
    public function selectFirstField($tables, $fields, $distinct = FALSE, $tidyFields = TRUE, $alias = FALSE, $clause = FALSE)
    {
        return $this->queryFetchFirstField($this->selectNoExecute($tables, $fields, $distinct, $tidyFields, $clause, $alias));
    }
    /**
     * Create a SELECT statement without executing
     *
     * Either: "SELECT DISTINCT $field FROM $table $alias $clause" or "SELECT $field FROM $table $alias $clause"
     *
     * @param array $tables
     * @param mixed $fields Array of fields or '*'
     * @param bool $distinct Default is FALSE
     * @param bool $tidyFields Format fields. Default is TRUE
     * @param string $clause Default is FALSE
     * @param false|string $alias Default is FALSE
     *
     * @return string
     */
    public function selectNoExecute($tables, $fields, $distinct = FALSE, $tidyFields = TRUE, $clause = FALSE, $alias = FALSE)
    {
        $table = $this->formatTables($tables);

        if ($tidyFields) {
            if (!is_array($fields) && $fields == '*') {
                $field = '*';
            } else {
                $field = $this->formatFields($fields);
            }
        } else {
            if (is_array($fields)) {
                $field = implode(', ', $fields);
            } else {
                $field = $fields;
            }
        }
        $this->error = $this->errors->text("dbError", "read");

        if ($clause) {
            $clause = $this->subClause();
            $this->resetSubs();
        }

        if ($alias) {
            $alias = ' AS ' . $this->formatTables($alias);
        }

        $distinct = $distinct ? 'DISTINCT' : '';

        return "SELECT $distinct $field FROM $table $alias $clause";
    }
    /**
     * Execute SELECT MAX() statement
     *
     * @param string $table
     * @param string $maxField
     * @param false|string $alias Default is FALSE
     * @param array $otherFields Other fields to add to the query. Default is FALSE
     * @param string $subQuery Default is FALSE
     *
     * @return object recordset
     */
    public function selectMax($table, $maxField, $alias = FALSE, $otherFields = FALSE, $subQuery = FALSE)
    {
        if ($table) {
            $table = 'FROM ' . $this->formatTables($table);
        }
        if (!$alias) {
            $alias = $this->formatFields($maxField);
        } else {
            $alias = $this->formatFields($alias);
        }
        if ($otherFields) {
            $otherFields = ', ' . $this->formatFields($otherFields);
        }
        $this->error = $this->errors->text("dbError", "read");

        return $this->queryFetchFirstRow('SELECT MAX(' . $this->formatFields($maxField) . ") AS $alias $otherFields $table $subQuery");
    }
    /**
     * Execute a SELECT MIN() statement
     *
     * @param string $table
     * @param string $minField
     *
     * @return object recordset
     */
    public function selectMin($table, $minField)
    {
        $table = $this->formatTables($table);
        $this->error = $this->errors->text("dbError", "read");

        return $this->query('SELECT MIN(' . $this->formatFields($minField) . ") AS $minField FROM $table");
    }
    /**
     * Execute a "SELECT FROM_UNIXTIME(AVG(UNIX_TIMESTAMP($field))) AS $field FROM $table" statement
     *
     * @param string $table
     * @param string $field
     *
     * @return object recordset
     */
    public function selectAverageDate($table, $field)
    {
        $table = $this->formatTables($table);
        $field = $this->formatFields($field);
        $this->error = $this->errors->text("dbError", "read");

        return $this->queryFetchFirstField("SELECT FROM_UNIXTIME(AVG(UNIX_TIMESTAMP($field))) AS $field FROM $table");
    }
    /**
     * Execute a "SELECT COUNT(*) AS count, $field FROM $table" statement
     *
     * NB 'count' field in recordset
     *
     * @param string $table
     * @param string $field
     *
     * @return object recordset
     */
    public function selectCount($table, $field)
    {
        $table = $this->formatTables($table);
        $this->groupBy($field);
        $field = $this->formatFields($field);
        $this->error = $this->errors->text("dbError", "read");

        return $this->query("SELECT COUNT(*) AS count, $field FROM $table");
    }
    /**
     * Execute a "SELECT COUNT(*) AS count, $field FROM $table" statement
     *
     * NB 'count' field in recordset.
     * MAX is achieved by grouping and ordering on $field
     *
     * @param string $table
     * @param string $field
     *
     * @return object recordset
     */
    public function selectCountMax($table, $field)
    {
        $table = $this->formatTables($table);
        $this->groupBy($field);
        $field = $this->formatFields($field);
        $this->ascDesc = $this->desc;
        $this->orderBy('count', FALSE, FALSE);
        $this->error = $this->errors->text("dbError", "read");

        return $this->query("SELECT COUNT(*) AS count, $field FROM $table");
    }
    /**
     * Execute a "SELECT $field, COUNT($field) AS count $otherFields $table $subQuery" statement
     *
     * @param string $table
     * @param string $field
     * @param array $otherFields Other fields to add to the query. Default is FALSE
     * @param string $subQuery Default is FALSE
     * @param bool $group Default is TRUE
     * @param string $clause Default is FALSE
     * @param string $distinct Default is FALSE
     *
     * @return object recordset
     */
    public function selectCounts($table, $field, $otherFields = FALSE, $subQuery = FALSE, $group = TRUE, $clause = FALSE, $distinct = FALSE)
    {
        return $this->query($this->selectCountsNoExecute($table, $field, $otherFields, $subQuery, $group, $clause, $distinct));
    }
    /**
     * Create a "SELECT $field, COUNT($field) AS count $otherFields $table $subQuery $clause" statement without executing
     *
     * @param string $table
     * @param string $field
     * @param array $otherFields Other fields to add to the query. Default is FALSE
     * @param string $subQuery Default is FALSE
     * @param bool $group Default is TRUE
     * @param string $clause Default is FALSE
     * @param string $distinct Default is FALSE
     *
     * @return string
     */
    public function selectCountsNoExecute($table, $field, $otherFields = FALSE, $subQuery = FALSE, $group = TRUE, $clause = FALSE, $distinct = FALSE)
    {
        // NB NULL value rows are not gathered
        if ($table) {
            $table = 'FROM ' . $this->formatTables($table);
        }
        if ($subQuery && mb_strpos(ltrim($subQuery), 'FROM') !== 0) {
            $subQuery = 'FROM ' . $subQuery;
        }
        if ($otherFields) {
            $otherFields = ', ' . $this->formatFields($otherFields);
        }
        if ($group) {
            $this->groupBy($field);
            $this->group .= $otherFields;
        }
        $field = $this->formatFields($field);
        $this->error = $this->errors->text("dbError", "read");
        if ($clause) {
            $clause = $this->subClause();

            $this->resetSubs();
        }
        $distinct = $distinct ? "DISTINCT " : '';
        $subQuery ? "$subQuery" : '';

        return "SELECT $field, COUNT($distinct$field) AS count $otherFields $table $subQuery $clause";
    }
    /**
     * Execute a "SELECT COUNT(DISTINCT $field) AS count $table $subQuery" statement
     *
     * NB 'count' field in the recordset
     *
     * @param string $table
     * @param string $field
     * @param string $subQuery Default is FALSE
     * @param string $clause Default is FALSE
     *
     * @return object recordset
     */
    public function selectCountDistinctField($table, $field, $subQuery = FALSE, $clause = FALSE)
    {
        return $this->query($this->selectCountDistinctFieldNoExecute($table, $field, $subQuery, $clause));
    }
    /**
     * Create a "SELECT COUNT(DISTINCT $field) AS count $table $subQuery $clause" statement without executing
     *
     * NB 'count' field in the recordset
     *
     * @param string $table
     * @param string $field
     * @param string $subQuery Default is FALSE
     * @param string $clause Default is FALSE
     *
     * @return string
     */
    public function selectCountDistinctFieldNoExecute($table, $field, $subQuery = FALSE, $clause = FALSE)
    {
        if ($table) {
            $table = 'FROM ' . $this->formatTables($table);
        }
        if ($subQuery && mb_strpos(ltrim($subQuery), 'FROM') !== 0) {
            $subQuery = 'FROM ' . $subQuery;
        }
        $field = $this->formatFields($field);
        $this->error = $this->errors->text("dbError", "read");
        if ($clause) {
            $clause = $this->subClause();

            $this->resetSubs();
        }

        return "SELECT COUNT(DISTINCT $field) AS count $table $subQuery $clause";
    }
    /**
     * Execute a "SELECT COUNT(DISTINCT $field) AS count $subQuery" statement
     *
     * @param string $field
     * @param string $subQuery
     * @param string $clause Default is FALSE
     *
     * @return object recordset
     */
    public function selectCountFromSubquery($field, $subQuery, $clause = FALSE)
    {
        return $this->query($this->selectCountFromSubqueryNoExecute($field, $subQuery, $clause));
    }
    /**
     * Create a "SELECT COUNT(DISTINCT $field) AS count $subQuery $clause" statment without executing
     *
     * @param string $field
     * @param string $subQuery
     * @param string $clause Default is FALSE
     *
     * @return string
     */
    public function selectCountFromSubqueryNoExecute($field, $subQuery, $clause = FALSE)
    {
        $field = $this->formatFields($field);
        $this->error = $this->errors->text("dbError", "read");
        if ($clause) {
            $clause = $this->subClause();

            $this->resetSubs();
        }

        return "SELECT COUNT(DISTINCT $field) AS count $subQuery $clause";
    }
    /**
     * Execute a SELECT statement
     *
     * @see SQL::select()
     *
     * @param array $tables
     * @param mixed $fields Array of fields or '*'
     * @param bool $distinct Default is FALSE
     *
     * @return object recordset
     */
    public function selectWithExceptions($tables, $fields, $distinct = FALSE)
    {
        return $this->query($this->selectNoExecuteWithExceptions($tables, $fields, $distinct));
    }
    /**
     * Create a SELECT statement without executing
     *
     * @see SQL::select()
     *
     * @param array $tables
     * @param mixed $fields Array of fields or '*'
     * @param bool $distinct Default is FALSE
     *
     * @return string
     */
    public function selectNoExecuteWithExceptions($tables, $fields, $distinct = FALSE)
    {
        if (!is_array($fields) && $fields == '*') {
            $field = '*';
        } else {
            $field = $this->formatFields($fields, TRUE);
        }
        $table = $this->formatTables($tables);
        $this->error = $this->errors->text("dbError", "read");
        $distinct = $distinct ? 'DISTINCT' : '';

        return "SELECT $distinct $field FROM $table";
    }
    /**
     * Execute a SELECT statement with a subquery
     *
     * If $tables is FALSE, statement is "SELECT $field $subQuery" else it is "SELECT $field $subQuery $tables"
     *
     * @param array $tables
     * @param mixed $fields Array of fields or '*'
     * @param string $subQuery
     * @param bool $distinct Default is FALSE
     * @param bool $tidy Format fields. Default is TRUE
     * @param string $clause Default is FALSE
     *
     * @return object recordset
     */
    public function selectFromSubQuery($tables, $fields, $subQuery, $distinct = FALSE, $tidy = TRUE, $clause = FALSE)
    {
        return $this->query($this->selectNoExecuteFromSubQuery($tables, $fields, $subQuery, $distinct, $tidy, $clause));
    }
    /**
     * Create a SELECT statement with a subquery without executing
     *
     * If $tables is FALSE, statement is "SELECT $field $subQuery $clause" else it is "SELECT $field $subQuery $tables $clause"
     *
     * @param array $tables
     * @param mixed $fields Array of fields or '*'
     * @param string $subQuery
     * @param bool $distinct Default is FALSE
     * @param bool $tidy Format fields. Default is TRUE
     * @param bool $clause Default is FALSE
     *
     * @return object recordset
     */
    public function selectNoExecuteFromSubQuery($tables, $fields, $subQuery, $distinct = FALSE, $tidy = TRUE, $clause = FALSE)
    {
        if (!is_array($fields) && $fields == '*') {
            $field = '*';
        } else {
            $field = $fields;
            if ($tidy) {
                $field = $this->formatFields($field);
            }
        }
        if ($tables) {
            $table = ', ' . $this->formatTables($tables);
        }
        $this->error = $this->errors->text("dbError", "read");
        if ($clause) {
            $clause = $this->subClause();

            $this->resetSubs();
        }

        $distinct = $distinct ? 'DISTINCT' : '';

        if ($tables) {
            $myquery = "SELECT $distinct $field $subQuery $table $clause"; // 'FROM' is already part of subqQuery -- FROM($subQuery)
        } else {
            $myquery = "SELECT $distinct $field $subQuery $clause";
        }

        return $myquery;
    }
    /**
     * Execute an INSERT statement
     *
     * @param string $table
     * @param array $fields
     * @param array $values – can be multi-dimensional array
     */
    public function insert($table, $fields, $values)
    {
        $this->error = $this->errors->text("dbError", "write");
        $field = $this->formatFields($fields);
        $table = $this->formatTables($table);
        if (is_array($values)) {
            if (is_array($values[0])) {
                $valueArray = [];
                foreach ($values as $element) {
                    $valueArray[] = $this->formatValues($element);
                }
                $value = implode('), (', $valueArray);
                $this->queryNoResult("INSERT INTO $table ($field) VALUES ($value);");
            } else {
                $value = $this->formatValues($values);
                $this->queryNoResult("INSERT INTO $table ($field) VALUES ($value);");
            }
        } else {
            $value = $this->tidyInput($values);
            $this->queryNoResult("INSERT INTO $table ($field) VALUES ($value);");
        }
    }
    /**
     * Execute an multiple INSERT statement
     *
     * @param string $table
     * @param array $fields
     * @param string $values Must be formatted as "('1', '2', '3' ...), ('4', '5', '6' ...)"
     */
    public function multiInsert($table, $fields, $values)
    {
        $field = $this->formatFields($fields);
        $table = $this->formatTables($table);
        $this->error = $this->errors->text("dbError", "write");
        $this->queryNoResult("INSERT INTO $table ($field) VALUES $values");
    }
    /**
     * Execute an UPDATE statement for an array of fields
     *
     * @see SQL::update()
     *
     * @param string $table
     * @param array $updateArray
     * @param bool $failOnError Optional abort script on error (default TRUE)
     */
    public function update($table, $updateArray, $failOnError = TRUE)
    {
        $set = $this->formatUpdate($updateArray);
        $table = $this->formatTables($table);
        $this->error = $this->errors->text("dbError", "write");
        $join = FALSE;
        if (!empty($this->join)) {
            $join = implode(' ', $this->join);
            $this->joinUpdate = $this->join;
            $this->join = [];
        }
        if ($failOnError) {
            $this->queryNoResult("UPDATE $table $join $set");
        } else {
            $this->queryNoError("UPDATE $table $join $set");
        }
    }
    /**
     * Execute an UPDATE statement for an array of fields, setting the timestamp of a field
     *
     * If there is no value for a $updateArray key, the timestamp is set to CURRENT_TIMESTAMP
     *
     * @see SQL::updateTimestamp
     *
     * @param string $table
     * @param array $updateArray
     */
    public function updateTimestamp($table, $updateArray)
    {
        foreach ($updateArray as $field => $value) {
            if (!$value) {
                $value = 'CURRENT_TIMESTAMP';
            }
            $fieldArray[] = "`$field` = $value";
        }
        $set = "SET " . implode(", ", $fieldArray);

        $table = $this->formatTables($table);
        $this->error = $this->errors->text("dbError", "write");
        $this->queryNoResult("UPDATE $table $set");
    }
    /**
     * Execute an UPDATE statement for a single field
     *
     * @param string $table
     * @param string $set Set statement
     */
    public function updateSingle($table, $set)
    {
        $table = $this->formatTables($table);
        $this->error = $this->errors->text("dbError", "write");
        $this->queryNoResult("UPDATE $table SET $set");
    }
    /**
     * Execute an UPDATE statement setting the fields to NULL
     *
     * @see SQL::updateNull()
     *
     * @param string $table
     * @param array $nulls Array of fields to set to NULL
     */
    public function updateNull($table, $nulls)
    {
        $table = $this->formatTables($table);
        $this->error = $this->errors->text("dbError", "write");
        $join = FALSE;

        if (is_array($nulls)) {
            foreach ($nulls as $null) {
                $sqlArray[] = "`$null` = NULL";
            }
            $set = implode(", ", $sqlArray);
        } else {
            $set = "`$nulls` = NULL";
        }
        if (!empty($this->join)) {
            $join = implode(' ', $this->join);
            $this->joinUpdate = $this->join;
            $this->join = [];
        }
        $this->queryNoResult("UPDATE $table $join SET $set");
    }
    /**
     * Create and execute a multiple update on one table
     *
     * This executes something like:
     * UPDATE $table
     *    SET $setField = CASE $conditionField
     *        WHEN 1 THEN a
     *        WHEN 2 THEN b
     *        WHEN 3 THEN c
     *    END
     * WHERE $conditionField IN (1,2,3)
     *
     * where $updateArray is (1 => a, 2 => b, 3 => c)
     *
     * @param string $table
     * @param string $setField
     * @param string $conditionField
     * @param array $updateArray
     * @param array $extraConditions Optional array of formatted conditions joined with an AND to the IN clause
     */
    public function multiUpdate($table, $setField, $conditionField, $updateArray, $extraConditions = FALSE)
    {
        $table = $this->formatTables($table);
        $setField = $this->formatFields($setField);
        $conditionField = $this->formatFields($conditionField);
        $condition = ' WHERE ' . $conditionField . $this->inClause(implode(',', array_keys($updateArray)));
        if (is_array($extraConditions)) {
            $condition .= ' AND (' . implode(' ', $extraConditions) . ')';
        }

        $caseArray = [];
        foreach ($updateArray as $key => $value) {
            $value = $this->formatValues($value);
            if (mb_strlen($value) == 0) {
                $value = "''";
            }
            $caseArray[] = "WHEN " . $this->formatValues($key) . " THEN " . $value;
        }

        $caseString = implode(' ', $caseArray);

        $string = 'UPDATE ' . $table
            . ' SET ' . $setField
            . ' = CASE ' . $conditionField . ' ' . $caseString . ' END'
            . ' ' . $condition . '';
        $this->queryNoResult($string);
    }
    /**
     * Execute a DELETE statement
     *
     * NB Unless you want to delete all rows from a table, set the condition first!
     *
     * @param string $table
     */
    public function delete($table)
    {
        $table = $this->formatTables($table);
        $this->error = $this->errors->text("dbError", "write");
        $this->queryNoResult("DELETE FROM $table");
    }
    /**
     * Return last auto_increment ID
     *
     * @return int
     */
    public function lastAutoID()
    {
        $this->sqlTimerOn();

        $autoId = mysqli_insert_id($this->handle);

        $this->sqlTimerOff();

        return $autoId;
    }
    /**
     * Format fields for database type
     *
     * @param mixed $fields array or string
     * @param bool $withExceptions Default is FALSE
     * @param bool $tidyLeft Default is FALSE
     *
     * @return string
     */
    public function formatFields($fields, $withExceptions = FALSE, $tidyLeft = TRUE)
    {
        if (!is_array($fields)) {
            if (count($split = UTF8::mb_explode('.', $fields)) > 1) {
                return $this->formatTables($split[0]) . ".`$split[1]`";
            } else {
                return "`$fields`";
            }
        }
        if (empty($fields)) {
            return NULL;
        }
        foreach ($fields as $field) {
            if (is_array($field)) {
                if ($withExceptions) {
                    $array[] = $this->formatAliasWithExceptions($field, $tidyLeft);
                } else {
                    $array[] = $this->formatAlias($field, FALSE, $tidyLeft);
                }
            } else {
                if (count($split = UTF8::mb_explode('.', $field)) > 1) {
                    $array[] = $this->formatTables($split[0]) . ".`$split[1]`";
                } else {
                    $array[] = "`$field`";
                }
            }
        }

        return implode(', ', $array);
    }
    /**
     * Add delimiters to statements
     *
     * @param string $stmt
     * @param string $delimiter 'backtick', 'singleQuote', 'doubleQuote', 'parentheses' (based on MySQL)
     *
     * @return string delimited statement
     */
    public function delimit($stmt, $delimiter)
    {
        $stringDelimited = '';

        switch ($delimiter) {
            case 'backtick':
                $stringDelimited = '`' . $stmt . '`';

                break;

            case 'singleQuote':
                $stringDelimited = "'" . $stmt . "'";

                break;

            case 'doubleQuote':
                $stringDelimited = '"' . $stmt . '"';

                break;

            case 'parentheses':
                $stringDelimited = '(' . $stmt . ')';

                break;

            case 'brackets':
                $stringDelimited = '[' . $stmt . ']';

                break;

            // The default case is used as a trick for not quoting a statement
            // according to a condition of the caller,
            // or if the caller is faulty
            default:
                $stringDelimited = $stmt;

                break;
        }

        return $stringDelimited;
    }
    /**
     * Format tables for database type
     *
     * @param mixed $tables Array of tables or single table
     * @param bool $brackets Default is FALSE
     *
     * @return string
     */
    public function formatTables($tables, $brackets = FALSE)
    {
        if (!$tables) {
            return FALSE;
        } else {
            if (!is_array($tables)) {
                $tableListe = WIKINDX_DB_TABLEPREFIX . $tables;
            } else {
                foreach ($tables as $table) {
                    if (is_array($table)) {
                        $array[] = $this->formatAlias($table, TRUE);
                    } else {
                        $array[] = WIKINDX_DB_TABLEPREFIX . $table;
                    }
                }

                $tableListe = implode(', ', $array);
            }

            $brackets = $brackets ? 'parentheses' : '';

            return $this->delimit($tableListe, $brackets);
        }
    }
    /**
     * Create a WHERE() statement
     *
     * @param mixed $conditions
     * @param string $join Default is ''
     *
     * @return string
     */
    public function whereStmt($conditions, $join = '')
    {
        $array = [];
        if (is_array($conditions)) {
            foreach ($conditions as $field => $value) {
                $array[] = $field . $value;
            }

            return 'WHERE (' . implode($join, $array) . ')';
        } else {
            return 'WHERE (' . $conditions . ')';
        }
    }
    /**
     * Set up the SQL conditions for the next query.
     *
     * Conditions should be set before almost every SQL query. After the query is executed, the conditions are deleted automatically.
     * Multiple conditions are joined with $this->conditionSeparator which by default is set to $this->and (it could be $this->or).
     * $this->conditionSeparator is reset automatically after each query back to $this->and.
     *
     * @param mixed $condition Array of field => condition conditions or formatted condition string
     * @param string $notEqual Default is '='
     * @param bool $returnString Default is FALSE. If TRUE, don't set the condition but return a formatted condition string instead
     * @param bool $doubleParentheses Default is FALSE
     *
     * @return string Optional return
     */
    public function formatConditions($condition, $notEqual = '=', bool $returnString = FALSE, bool $doubleParentheses = FALSE)
    {
        if (!is_array($condition)) {
            if ($returnString) {
                return $doubleParentheses ? $this->conditionSeparator . '((' . $condition . '))' : $this->conditionSeparator . '(' . $condition . ')';
            } else {
                $this->condition[] = $doubleParentheses ? '((' . $condition . '))' : '(' . $condition . ')';
            }

            return;
        }

        if ($notEqual === ">") {
            $equal = $this->greater;
        } elseif ($notEqual === "<") {
            $equal = $this->less;
        } elseif ($notEqual === ">=") {
            $equal = $this->greaterEqual;
        } elseif ($notEqual === "<=") {
            $equal = $this->lessEqual;
        } elseif ($notEqual === "=") {
            $equal = $this->equal;
        } elseif ($notEqual === "!=") {
            $equal = $this->notEqual;
        } else {
            $equal = $this->notEqual;
        }

        foreach ($condition as $field => $value) {
            /**
             * Check for conditions such as 'IS NULL' or 'IS NOT NULL'
             */
            if (trim($value) === 'IS NULL' || trim($value) === 'IS NOT NULL') {
                $array[] = $this->formatFields($field) . $value;
            } elseif (trim($value) === 'NULL') {
                if ($equal == $this->equal) {
                    $array[] = $this->formatFields($field) . 'IS NULL';
                } else {
                    $array[] = $this->formatFields($field) . 'IS NOT NULL';
                }
            } else {
                $array[] = $this->formatFields($field) . $equal . $this->tidyInput($value);
            }
        }

        $conditions = '(' . implode($this->conditionSeparator, $array) . ')';
        if ($doubleParentheses) {
            '(' . $conditions . ')';
        }

        if ($returnString) {
            return $conditions;
        } else {
            $this->condition[] = $conditions;
        }
    }
    /**
     * Format multiple conditions for one field using ' OR '
     *
     * Conditions should be set before almost every SQL query. After the query is executed, the conditions are reset automatically.
     * Multiple conditions are joined with $this->or ($this->conditionSeparator is ignored).
     *
     * @param mixed $condition Array of conditions or formatted condition string
     * @param string $field
     * @param bool $notEqual Default is FALSE
     * @param bool $tidy Format the field for the database type. Default is TRUE.
     * @param bool $doubleParentheses Place double, rather than single, parentheses around the condition. Default is FALSE.
     * @param bool $alias $field is an alias in SQL so should have quotes instead of backticks. Default is FALSE.
     * @param bool $returnString Default is FALSE. If TRUE, don't set the condition but return a formatted condition string instead
     */
    public function formatConditionsOneField(
        $condition,
        $field,
        $notEqual = FALSE,
        $tidy = TRUE,
        $doubleParentheses = FALSE,
        $alias = FALSE,
        $returnString = FALSE
    ) {
        if ($notEqual) {
            if ($notEqual === ">") {
                $equal = $this->greater;
            } elseif ($notEqual === "<") {
                $equal = $this->less;
            } elseif ($notEqual === ">=") {
                $equal = $this->greaterEqual;
            } elseif ($notEqual === "<=") {
                $equal = $this->lessEqual;
            } elseif ($notEqual === "=") {
                $equal = $this->equal;
            } elseif ($notEqual === "!=") {
                $equal = $this->notEqual;
            } else {
                $equal = $this->notEqual;
            }
        } else {
            $equal = $this->equal;
        }

        $field = $alias ? $this->tidyInput($field) : $this->formatFields($field);

        // When the condition is unique, turn it into multiple condition not to double the code.
        if (!is_array($condition)) {
            $condition = [$condition];
        }

        // When cond operator is = or != we can use instead IN or NOT IN operators
        $opIN = $equal == $this->equal || $equal == $this->notEqual;

        $array = $arrayIN = [];

        if (!$tidy) {
            foreach ($condition as $value) {
                $array[] = $field . $value;
            }
        } else {
            foreach ($condition as $value) {
                /**
                 * Check for conditions such as 'IS NULL' or 'IS NOT NULL'
                 */
                if (trim($value) === 'IS NULL' || trim($value) === 'IS NOT NULL') {
                    $array[] = $field . $value;
                } elseif (trim($value) === 'NULL') {
                    if ($equal == $this->equal) {
                        $array[] = $field . 'IS NULL';
                    } else {
                        $array[] = $field . 'IS NOT NULL';
                    }
                } elseif ($opIN && $value) { // If IN or NOT IN operator can be used, puts current cond. value in an separate array
                    $arrayIN[] = $this->tidyInput($value);
                } elseif ($value) {
                    $array[] = $field . $equal . $this->tidyInput($value);
                }
            }
        }

        // If possible merge all cond. value with IN / NOT IN and puts this clause with others
        if ($opIN && !empty($arrayIN)) {
            $array[] = $field . ' ' . ($equal == $this->equal ? 'IN' : 'NOT IN') . ' (' . implode(', ', $arrayIN) . ')';
        }

        $conditionsOneField = implode($this->or, $array);

        if ($conditionsOneField != '') {
            $conditionsOneField = '(' . $conditionsOneField . ')';
            if ($doubleParentheses) {
                $conditionsOneField = '(' . $conditionsOneField . ')';
            }

            if ($returnString) {
                return $conditionsOneField;
            } else {
                $this->condition[] = $conditionsOneField;
            }
        } else {
            if ($returnString) {
                return $conditionsOneField;
            }
        }
    }
    /**
     * Format a timestamp value as "Y-m-d H:i:s"
     *
     * @param string $time UNIX epoch time. Default is FALSE (in which case time() is used)
     *
     * @return string
     */
    public function formatTimestamp($time = FALSE)
    {
        if (!$time) {
            $time = time();
        }

        return date("Y-m-d H:i:s", $time);
    }
    /**
     * Format field values for database type
     *
     * Fields are trimmed
     *
     * @param string $string
     *
     * @return string
     */
    public function tidyInput($string)
    {
        $string = trim($string);
        // Check if STRING is a number and reject scientific notation
        // (used sometimes as page numbers in journals)
        if (is_numeric($string) && strpos($string, 'e') === FALSE) {
            return $string;
        } else {
            return "'" . $this->escapeString($string) . "'";
        }
    }
    /**
     * Format field values for database type
     *
     * Fields are not trimmed
     *
     * @param string $string
     *
     * @return string
     */
    public function tidyInputNoTrim($string)
    {
        // Check if STRING is a number and reject scientific notation
        // (used sometimes as page numbers in journals)
        if (is_numeric($string) && strpos($string, 'e') === FALSE) {
            return $string;
        } else {
            return "'" . $this->escapeString($string) . "'";
        }
    }
    /**
     * Format field values for database type
     *
     * For use with ORDER or GROUP by clauses
     *
     * @param string $string
     *
     * @return string
     */
    public function tidyInputClause($string)
    {
        return "`" . $string . "`";
    }
    /**
     * Escape a string according to db type
     *
     * @param string $string
     *
     * @return string
     */
    public function escapeString($string)
    {
        return preg_replace('/[\x00\x0A\x0D\x1A\x22\x27\x5C]/u', '\\\$0', $string);
    }
    /**
     * Escape a string for the LIKE statement according to db type
     *
     * @param string $string
     *
     * @return string
     */
    public function escapeLikeString($string)
    {
        return preg_replace('~[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]~u', '\\\$0', $string);
    }
    /**
     * Write a WIKINDX database cache
     *
     * @param string $field
     * @param array $array
     */
    public function writeCache($field, $array)
    {
        $this->updateSingle('cache', $this->formatFields($field) . "=" . $this->tidyInput(base64_encode(serialize($array))));
    }
    /**
     * Read a WIKINDX database cache
     *
     * @param string $field
     *
     * @return array
     */
    public function readCache($field)
    {
        $result = FALSE;

        $recordset = $this->select('cache', $field);

        if ($this->numRows($recordset) > 0) {
            $result = unserialize(base64_decode($this->fetchOne($recordset)));
        }

        return $result;
    }
    /**
     * Delete a WIKINDX database cache
     *
     * @param string $field
     */
    public function deleteCache($field)
    {
        $this->updateNull('cache', $field);
    }
    /**
     * prepend the configured table name to the field names
     *
     * @param string $table
     * @param mixed $fields Array of field names or single field name
     *
     * @return mixed
     */
    public function prependTableToField($table, $fields)
    {
        $table = str_replace('_', '', $table);
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $changed[] = $table . $field;
            }
        } else {
            $changed = $table . $fields;
        }

        return $changed;
    }
    /**
     * Return a ratio alias of $field / number days since e.g. resource added.
     *
     * @param string $field (e.g. 'statisticsresourceviewsCount', or 'statisticsattachmentdownloadsCount')
     * @param string $denominator (e.g. 'resourcetimestampTimestampAdd')
     * @param bool $alias Default is FALSE
     * @param string $aggregateFunction Default is ''. If <> '', insert an Aggregate Function of the same name of multiple $fields
     * @param int $round Default is 3
     * @param mixed $otherFields FALSE (default) or comma-delimited list of database fields to include in the GROUP BY
     * @param bool $group FALSE (default) or GROUP BY $field and $otherFields
     *
     * @return int
     */
    public function dateDiffRatio($field, $denominator, $alias = FALSE, $aggregateFunction = '', $round = 3, $otherFields = FALSE, $group = FALSE)
    {
        if ($otherFields) {
            $otherFields = ', ' . $this->formatFields($otherFields);
        }
        if ($group) {
            $this->groupBy($field);
            $this->group .= $otherFields;
        }

        if ($alias) {
            $alias = ' AS ' . $this->formatFields($alias);
        }
        $field = $this->formatFields($field);
        $denominator = $this->formatFields($denominator);

        if ($aggregateFunction != '') {
            $avgBegin = $aggregateFunction . '(';
            $avgEnd = ')';
        } else {
            $avgBegin = '';
            $avgEnd = '';
        }
        if (!$round) {
            return "$avgBegin $field / DATEDIFF(CURRENT_DATE, $denominator)$avgEnd $alias";
        } else {
            return "ROUND($avgBegin $field / DATEDIFF(CURRENT_DATE, $denominator)$avgEnd, $round)$alias";
        }
    }
    /**
     * Return number months difference between two database timestamps
     *
     * @param string $date1 Timestamp value from database
     * @param string $date2 Default is FALSE. If FALSE, CURRENT_TIMESTAMP is assumed
     *
     * @return int
     */
    public function monthDiff($date1, $date2 = FALSE)
    {
        if ($date2) {
            $date2 = "'$date2'";
        } else {
            $date2 = 'CURRENT_TIMESTAMP';
        }

        $date1 = "'$date1'";

        return $this->queryFetchFirstField("SELECT PERIOD_DIFF(DATE_FORMAT($date2, '%Y%m'), DATE_FORMAT($date1, '%Y%m'))");
    }
    /**
     * Return SQL code to retrieve the first day of the current year
     *
     * @return string
     */
    public function firstDayOfCurrentYear()
    {
        return 'DATE_SUB(CURRENT_DATE, INTERVAL DAYOFYEAR(CURRENT_DATE)-1 DAY)';
    }
    /**
     * Return SQL code to retrieve the first day of the current mounth
     *
     * @return string
     */
    public function firstDayOfCurrentMonth()
    {
        return 'DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY)';
    }
    /**
     * Create a ROUND() clause
     *
     * @param string $clause
     * @param bool $alias Default is FALSE
     * @param int $round Default is 3
     *
     * @return string
     */
    public function round($clause, $alias = FALSE, $round = 3)
    {
        if ($alias) {
            $alias = ' AS ' . $this->formatFields($alias);
        }

        return "ROUND($clause, $round)$alias";
    }
    /**
     * Return a AVG() clause
     *
     * @param string $clause
     *
     * @return string
     */
    public function avg($clause)
    {
        return "AVG($clause)";
    }
    /**
     * Create a condition clause for a time interval: "DATE_SUB($fromTime, INTERVAL $limit $timescale)"
     *
     * @param string $limit
     * @param string $timescale Default is 'day'
     * @param string $fromTime Default is 'now'
     *
     * @return string
     */
    public function dateIntervalCondition($limit, $timescale = 'day', $fromTime = 'now')
    {
        if ($fromTime == 'now') {
            $fromTime = 'CURRENT_DATE';
        }
        if ($timescale == 'day') {
            $timescale = 'DAY';
        }

        return "DATE_SUB($fromTime, INTERVAL $limit $timescale)";
    }
    /**
     * Create a CONCAT clause
     *
     * @param array $array
     * @param string $separator Default is FALSE. If !FALSE, CONCAT_WS() is used, else CONCAT().
     *
     * @return string
     */
    public function concat($array, $separator = FALSE)
    {
        if ($separator !== FALSE) {
            return "CONCAT_WS('$separator', " . implode(', ', $array) . ")";
        } else {
            return "CONCAT(" . implode(', ', $array) . ")";
        }
    }
    /**
     * Create a GROUP_CONCAT clause
     *
     * @param string $field
     * @param string $separator Default is FALSE.
     * @param bool $distinct Default is FALSE
     *
     * @return string
     */
    public function groupConcat($field, $separator = FALSE, $distinct = FALSE)
    {
        $distinct === TRUE ? $distinct = 'DISTINCT ' : $distinct = '';
        if ($separator !== FALSE) {
            return "GROUP_CONCAT($distinct$field SEPARATOR '$separator')";
        } else {
            return "GROUP_CONCAT($distinct$field)";
        }
    }
    /**
     * Create a CASE WHEN() THEN clause
     *
     * $subject can be an array. This allows multiple WHEN $subject:key THEN $subject:value to be part of the CASE statement. If $subject is an array,
     * $test and $result are ignored ($tidy is still tested regarding formatting or not of $default) and the keys and values of $subject should
     * already be formatted and tidied for SQL:
     * e.g. subject['subject = test'] => result;
     *
     * @param mixed $subject (string or array)
     * @param string $test
     * @param string $result
     * @param string $default Default is FALSE
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     * @param string $alias Default is FALSE
     *
     * @return string
     */
    public function caseWhen($subject, $test, $result, $default = FALSE, $tidy = TRUE, $alias = FALSE)
    {
        if ($tidy && $default) {
            $defaultClause = $this->formatFields($default);
        } else {
            $defaultClause = $default;
        }
        if ($default) {
            $default = ' ELSE (' . $defaultClause . ')';
        }
        if ($alias) {
            $alias = ' AS ' . $this->formatFields($alias);
        }
        if (is_array($subject)) {
            foreach ($subject as $key => $value) {
                $clauses[] = "WHEN ($key) THEN $value";
            }
            $multipleClause = implode(' ', $clauses);
            $final = "CASE $multipleClause $default END$alias";
        } elseif ($tidy) {
            $subject = $this->formatFields($subject);
            $result = $this->formatFields($result);
            $final = "CASE WHEN ($subject $test) THEN ($result) $default END$alias";
        } else {
            $final = "CASE WHEN ($subject $test) THEN ($result) $default END$alias";
        }

        return $final;
    }
    /**
     * Create an IF clause: "IF($field $test, $result, $default)"
     *
     * @param string $field
     * @param string $test
     * @param string $result
     * @param string $default
     * @param string $alias – default is FALSE
     *
     * @return string
     */
    public function ifClause($field, $test, $result, $default, $alias = FALSE)
    {
        if ($alias) {
            $alias = ' AS ' . $this->formatFields($alias);
        }

        return "IF($field $test, $result, $default)$alias";
    }
    /**
     * Create a INNER JOIN clause on a table
     *
     * Clauses are stored in $this->join array for use at the next query after which the array is emptied.
     * You should set up your join statements before each query.
     *
     * @param string $table
     * @param string $left
     * @param string $right Default is FALSE
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     */
    public function innerJoin($table, $left, $right = FALSE, $tidy = TRUE)
    {
        $this->innerJoinGeneric($this->formatTables($table, FALSE), $left, $right, $tidy);
    }
    /**
     * Create a INNER JOIN clause on a subquery
     *
     * Clauses are stored in $this->join array for use at the next query after which the array is emptied.
     * You should set up your join statements before each query.
     *
     * @param string $subQuery
     * @param string $left
     * @param string $right Default is FALSE
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     */
    public function innerJoinSubQuery($subQuery, $left, $right = FALSE, $tidy = TRUE)
    {
        $this->innerJoinGeneric("($subQuery)", $left, $right, $tidy);
    }
    /**
     * Create a INNER JOIN clause (generic)
     *
     * Clauses are stored in $this->join array for use at the next query after which the array is emptied.
     * You should set up your join statements before each query.
     *
     * @param string $joinedMember (Name of a table, Name of a view, subquery...)
     * @param string $left
     * @param string $right Default is FALSE
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     */
    public function innerJoinGeneric($joinedMember, $left, $right = FALSE, $tidy = TRUE)
    {
        if ($tidy && $right) {
            $left = $this->formatFields($left);
            $right = $this->equal . $this->formatFields($right);
        } elseif ($right) {
            $right = $this->equal . $right;
        }
        $this->join[] = 'INNER JOIN ' . $joinedMember . ' ON ' . $left . $right;
    }
    /**
     * Create a LEFT JOIN clause on a table
     *
     * Clauses are stored in $this->join array for use at the next query after which the array is emptied.
     * You should set up your join statements before each query.
     *
     * @param string $table
     * @param string $left
     * @param string $right Default is FALSE
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     */
    public function leftJoin($table, $left, $right = FALSE, $tidy = TRUE)
    {
        $this->leftJoinGeneric($this->formatTables($table, FALSE), $left, $right, $tidy);
    }
    /**
     * Create a LEFT JOIN clause on a subquery
     *
     * Clauses are stored in $this->join array for use at the next query after which the array is emptied.
     * You should set up your join statements before each query.
     *
     * @param string $subQuery
     * @param string $left
     * @param string $right Default is FALSE
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     */
    public function leftJoinSubQuery($subQuery, $left, $right = FALSE, $tidy = TRUE)
    {
        $this->leftJoinGeneric("($subQuery)", $left, $right, $tidy);
    }
    /**
     * Create a LEFT JOIN clause with additional condition string
     *
     * Clauses are stored in $this->join array for use at the next query after which the array is emptied.
     * You should set up your join statements before each query.
     *
     * @param string $table
     * @param string $left
     * @param string $right Default is FALSE
     * @param string $condition Default is FALSE
     * @param bool $and Default is TRUE. If TRUE, prefix SQL 'AND' to $condition
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     */
    public function leftJoinCondition($table, $left, $right = FALSE, $condition = FALSE, $and = TRUE, $tidy = TRUE)
    {
        if ($tidy && $right) {
            $left = $this->formatFields($left);
            $right = $this->equal . $this->formatFields($right);
        }
        $and ? $and = $this->and : '';
        if ($condition) {
            $right = $right . $and . $condition;
        }
        $this->leftJoinGeneric($this->formatTables($table, FALSE), $left, $right, FALSE);
    }
    /**
     * Create a LEFT JOIN clause (generic)
     *
     * Clauses are stored in $this->join array for use at the next query after which the array is emptied.
     * You should set up your join statements before each query.
     *
     * @param string $joinedMember (Name of a table, Name of a view, subquery...)
     * @param string $left
     * @param string $right Default is FALSE
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     */
    public function leftJoinGeneric($joinedMember, $left, $right = FALSE, $tidy = TRUE)
    {
        if ($tidy && $right) {
            $left = $this->formatFields($left);
            $right = $this->equal . $this->formatFields($right);
        } elseif ($right) {
            $right = $this->equal . $right;
        }
        $this->join[] = 'LEFT JOIN ' . $joinedMember . ' ON ' . $left . $right;
    }
    /**
     * Create an ORDER BY clause
     *
     * Clauses are stored in $this->order array for use at the next query after which the array is emptied.
     * You should set up your order statements before each query.
     *
     * @param string $field
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     * @param bool $removeBraces Default is TRUE. If TRUE, remove {...} braces
     * @param bool $returnString Default is FALSE. If TRUE, return the ORDER BY clause as a string
     */
    public function orderBy($field, $tidy = TRUE, $removeBraces = TRUE, $returnString = FALSE)
    {
        if ($tidy) {
            $field = $this->formatFields($field);
        }

        if ($removeBraces) {
            $field = $this->replace($this->replace($field, '{', '', FALSE), '}', '', FALSE);
        }
        if ($returnString) {
            return ' ORDER BY ' . $field . ' ' . $this->ascDesc;
        }
        $this->order[] = $field . ' ' . $this->ascDesc;

        $this->collateSet = FALSE; // reset
    }
    /**
     * Create an ORDER BY RAND() clause
     *
     * Clauses are stored in $this->order array for use at the next query after which the array is emptied.
     * You should set up your order statements before each query.
     */
    public function orderByRandom()
    {
        $this->order[] = ' RAND()';
        $this->collateSet = FALSE; // reset
    }
    /**
     * Create an ORDER BY clause with additional COLLATION for UTF8
     *
     * Clauses are stored in $this->order array for use at the next query after which the array is emptied.
     * You should set up your order statements before each query.
     *
     * @param string $field
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     */
    public function orderByCollate($field, $tidy = TRUE)
    {
        if ($tidy) {
            $field = $this->formatFields($field);
        }

        $this->order[] = $field . ' COLLATE utf8mb4_unicode_520_ci' . $this->ascDesc;

        $this->collateSet = FALSE; // reset
    }
    /**
     * Create a GROUP BY clause
     *
     * Clauses are stored in $this->group array for use at the next query after which the array is emptied.
     * You should set up your group statements before each query.
     *
     * @param string $field
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     * @param string $having Default is FALSE. If TRUE, group by clause has ' HAVING $having' appended to it.
     */
    public function groupBy($field, $tidy = TRUE, $having = FALSE)
    {
        if ($tidy) {
            $field = $this->formatFields($field);
        }

        $this->group = "GROUP BY $field";

        if ($having) {
            $this->group .= " HAVING $having";
        }
    }
    /**
     * Create a LIMIT clause
     *
     * Clauses are stored in the $this->limit string for use at the next query after which the string is reset.
     * You should set up your limit statement before each query.
     *
     * @param int $limit
     * @param int $offset
     * @param bool $return If TRUE, return the limit statement rather than setting it. Default is FALSE
     */
    public function limit($limit, $offset, $return = FALSE)
    {
        if ($limit < 1) {
            return; // if limit is set to -1, we don't want a limit
        }
        $limit = " LIMIT $offset, $limit";
        if ($return) {
            return $limit;
        } else {
            $this->limit = $limit;
        }
    }
    /**
     * Create a REPLACE clause: "REPLACE(' . $field . ", '$find', '$replace')"
     *
     * @param string $field
     * @param string $find
     * @param string $replace
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     *
     * @return string
     */
    public function replace($field, $find, $replace, $tidy = TRUE)
    {
        if ($tidy) {
            $field = $this->formatFields($field);
        }

        return " REPLACE($field, '$find', '$replace')";
    }
    /**
     * Create a COALESCE clause: "COALESCE($fields) $alias"
     *
     * @param mixed $fields Array of field names or single field name
     * @param string $alias
     *
     * @return string
     */
    public function coalesce($fields, $alias = FALSE)
    {
        if (is_array($fields)) {
            $coalesce = [];

            foreach ($fields as $field) {
                $coalesce[] = $this->formatFields($field);
            }

            $fields = implode(', ', $coalesce);
        }

        if ($alias) {
            $alias = ' AS ' . $this->formatFields($alias);
        }

        return " COALESCE($fields) $alias";
    }
    /**
     * Create an UPPER clause
     *
     * @param string $field
     * @param bool $tidy Default is TRUE. If TRUE, format fields for database type
     *
     * @return string
     */
    public function upper($field, $tidy = TRUE)
    {
        if ($tidy) {
            $field = $this->formatFields($field);
        }

        return " UPPER($field)";
    }
    /**
     * Create a REGEXP clause
     *
     * @param string $first
     * @param string $test
     * @param string $last
     * @param bool $not Default is FALSE
     *
     * @return string
     */
    public function regexp($first, $test, $last, $not = FALSE)
    {
        $not = $not ? 'NOT' : '';

        return " $not REGEXP " . $this->tidyInput($first . $test . $last);
    }
    /**
     * Create a LIKE clause
     *
     * @param string $first
     * @param string $test
     * @param string $last
     * @param bool $not Default is FALSE
     *
     * @return string
     */
    public function like($first, $test, $last, $not = FALSE)
    {
        $not = $not ? 'NOT' : '';

        return " $not LIKE '" . $first . $this->escapeLikeString($test) . $last . "' COLLATE utf8mb4_unicode_520_ci";
    }
    /**
     * Create a FULLTEXT search clause: MATCH($field) AGAINST('$searchTerm' $type)
     *
     * @param mixed $field Field or array of fields to search on
     * @param string $searchTerm String formatted for boolean or natural language search
     * @param bool $boolean Default is TRUE and gives a boolean search, FALSE is natural language search
     *
     * @return string
     */
    public function fulltextSearch($field, $searchTerm, $boolean = TRUE)
    {
        $fields = $this->formatFields($field);
        $type = $boolean ? 'IN BOOLEAN MODE' : 'IN NATURAL LANGUAGE MODE';
        // Need to protect double quotes
        $searchTerm = str_replace('"', '!WIKINDXQUOTESWIKINDX!', $searchTerm);
        $this->escapeLikeString($searchTerm);
        $searchEscaped = str_replace('!WIKINDXQUOTESWIKINDX!', '"', $searchTerm);

        return " MATCH($fields) AGAINST('" . $searchEscaped . "' $type)";
    }
    /**
     * Create a COUNT() clause
     *
     * @param string $field Database field to count
     * @param string $operator Optional =, !=, >, <. Default is FALSE
     * @param string $comparison Comparison following $operator. Default is FALSE
     * @param bool $distinct TRUE/FALSE (default).  COUNT(DISTINCT `field`)
     * @param string $alias COUNT(`field`) AS $alias. Default is FALSE
     *
     * @return string
     */
    public function count($field, $operator = FALSE, $comparison = FALSE, $distinct = FALSE, $alias = FALSE)
    {
        if ($operator) {
            if ($operator === ">") {
                $selop = $this->greater;
            } elseif ($operator === "<") {
                $selop = $this->less;
            } elseif ($operator === ">=") {
                $selop = $this->greaterEqual;
            } elseif ($operator === "<=") {
                $selop = $this->lessEqual;
            } elseif ($operator === '!=') {
                $selop = $this->notEqual;
            } elseif ($operator === '=') {
                $selop = $this->equal;
            } else {
                $selop = $this->equal;
            }

            $comparison = ' ' . $selop . ' ' . $this->tidyInput($comparison);
        }

        $field = $this->formatFields($field);
        $distinct = $distinct ? 'DISTINCT' : '';
        if ($alias) {
            $alias = ' AS ' . $this->formatFields($alias);
        }

        return "COUNT($distinct $field)$alias $comparison";
    }
    /**
     * Clause for using an index in a SQL query
     *
     * @param string $field
     * @param string $type Default is 'FORCE'
     */
    public function indexHint($field, $type = 'FORCE')
    {
        $field = $this->formatFields($field);

        return " $type INDEX($field) ";
    }
    /**
     * Create an IN() clause
     *
     * @param string $stmt IN ($stmt)
     * @param bool $not Default is FALSE
     *
     * @return string
     */
    public function inClause($stmt, $not = FALSE)
    {
        $not = $not ? 'NOT' : '';

        return " $not IN ($stmt)";
    }
    /**
     * Create an EXISTS() clause
     *
     * @param string $stmt IN ($stmt)
     * @param bool $not Default is FALSE
     *
     * @return string
     */
    public function existsClause($stmt, $not = FALSE)
    {
        $not = $not ? 'NOT' : '';

        return " $not EXISTS ($stmt)";
    }
    /**
     * Create a SUM() clause
     *
     * @param string $field
     * @param string $alias Default is FALSE
     *
     * @return string
     */
    public function sum($field, $alias = FALSE)
    {
        $field = $this->formatFields($field);
        if ($alias) {
            $alias = ' AS ' . $this->formatFields($alias);
        }

        return "SUM($field)$alias";
    }
    /**
     * Create the SQL SELECT statement for counting resources/initial character of creator or title when using alphabetic paging.
     * A-Z for Latin characters, '??' for all other characters and '#' for NULL resourcecreatorCreatorSurname fields
     *
     * @param string $order ('creator' or 'title')
     * @param string $subQuery Optional subquery to be added to this statement
     * @param array $conditions Array of conditions to SQL
     * @param array $joins Array of table joins to SQL (array(table => array(rightField, leftField))
     * @param array $conditionsOneField Array of conditions to SQL (formatConditionsOneField)
     * @param string $table default is 'resource'
     * @param string $tableJoin default is 'resourceId'
     *
     * @return string
     *
     * @todo Use code to create the statements rather than strings
     */
    public function countAlpha(
        $order,
        $subQuery = FALSE,
        $conditions = [],
        $joins = [],
        $conditionsOneField = [],
        $table = 'resource',
        $tableJoin = 'resourceId'
    ) {
        $condition = $join = FALSE;
        foreach ($conditions as $condition) {
            if (is_array($condition)) {
                $this->conditionSeparator = $this->or;
                $conditionArray[] = $this->formatConditions($condition, '=', TRUE);
                $this->conditionSeparator = $this->and;
            } else {
                $conditionArray[] = $condition; // $condition has already passed through formatConditions()
            }
        }
        foreach ($conditionsOneField as $field => $array) {
            $field = $this->formatFields($field);
            $conditionOneField = [];
            foreach ($array as $cond) {
                $conditionOneField[] = $field . ' ' . $this->equal . ' ' . $this->tidyInput($cond);
            }
            $conditionArray[] = '(' . implode($this->or, $conditionOneField) . ')';
        }
        foreach ($joins as $key => $array) {
            $joinStmts[] = "LEFT OUTER JOIN " . $this->formatTables($key) . " ON " .
                $this->formatFields($array[0]) . ' ' . $this->equal . ' ' . $this->formatFields($array[1]);
        }
        if (isset($joinStmts)) {
            $join = implode(' ', $joinStmts);
        }
        if ($table) {
            $table = $this->formatTables($table);
            $tableJoin = $this->formatFields($tableJoin);
            $initialJoin = "LEFT OUTER JOIN $table ON $tableJoin = `rId`";
        } else {
            $initialJoin = FALSE;
        }
        if (isset($conditionArray)) {
            $condition = $this->whereStmt(implode($this->and, $conditionArray));
        }
        if ($subQuery) {
            if ($order == 'title') {
                return "SELECT page, COUNT(id) AS count
				FROM (
					SELECT resourceId AS id ,
					CASE WHEN ORD(UPPER(SUBSTRING(resourceTitleSort, 1, 1))) BETWEEN 65 AND 90
						THEN UPPER(SUBSTRING(resourceTitleSort, 1, 1))
						ELSE '??' END
					AS page
					FROM $subQuery
					$initialJoin
					$join
					$condition
					GROUP BY id, page
				) AS t_page
				GROUP BY page";
            } elseif ($order == 'attachments') { // Only from advanced search
                $jrTable = $this->formatTables('resource');
                $jrField = $this->formatFields('resourceId');
                $joinResource = "LEFT OUTER JOIN $jrTable ON $jrField = rId";

                return "SELECT page, COUNT(id) AS count
				FROM (
					SELECT resourceId AS id ,
					CASE WHEN ORD(UPPER(SUBSTRING(resourceattachmentsFileName, 1, 1))) BETWEEN 65 AND 90
						THEN UPPER(SUBSTRING(resourceattachmentsFileName, 1, 1))
						ELSE '??' END
					AS page
					FROM $subQuery
					$joinResource
					$initialJoin
					$join
					$condition
					GROUP BY id, page
				) AS t_page
				GROUP BY page";
            } else { // default is 'creator'
                return "SELECT page, COUNT(id) AS count
				FROM (
					SELECT resourcecreatorResourceId AS id ,
					CASE WHEN ORD(UPPER(SUBSTRING(REPLACE(REPLACE(resourcecreatorCreatorSurname, '{', ''), '}', ''), 1, 1))) BETWEEN 65 AND 90
						THEN UPPER(SUBSTRING(REPLACE(REPLACE(resourcecreatorCreatorSurname, '{', ''), '}', ''), 1, 1))
						WHEN resourcecreatorCreatorSurname IS NULL THEN '#'
						ELSE '??' END
					AS page
					FROM $subQuery
					$initialJoin
					$join
					$condition
					GROUP BY id, page
				) AS t_page
				GROUP BY page";
            }
        } else {
            if ($order == 'title') {
                return "SELECT page, COUNT(id) AS count
				FROM (
					SELECT resourceId AS id ,
					CASE WHEN ORD(UPPER(SUBSTRING(resourceTitleSort, 1, 1))) BETWEEN 65 AND 90
						THEN UPPER(SUBSTRING(resourceTitleSort, 1, 1))
						ELSE '??' END
					AS page
					FROM $table
					$join
					$condition
					GROUP BY resourceId, page
				) AS t_page
				GROUP BY page";
            } else { // default is 'creator'
                return "SELECT page, COUNT(id) AS count
				FROM (
					SELECT resourcecreatorResourceId AS id ,
					CASE WHEN ORD(UPPER(SUBSTRING(REPLACE(REPLACE(resourcecreatorCreatorSurname, '{', ''), '}', ''), 1, 1))) BETWEEN 65 AND 90
						THEN UPPER(SUBSTRING(REPLACE(REPLACE(resourcecreatorCreatorSurname, '{', ''), '}', ''), 1, 1))
						WHEN resourcecreatorCreatorSurname IS NULL THEN '#'
						ELSE '??' END
					AS page
					FROM $table
					$join
					$condition
					GROUP BY resourcecreatorResourceId, page
				) AS t_page
				GROUP BY page";
            }
        }
    }

    /**
     * Format display of SQL query in debug mode
     *
     * @param string $querystring SQL of a query to display
     * @param string $executionType Description of execution type (EXEC, NOEXEC...)
     */
    public function printSQLDebug($querystring = '', $executionType = 'SQL')
    {
        $beautified = FALSE;
        if (!defined("WIKINDX_DEBUG_SQL") || WIKINDX_DEBUG_SQL) {
            $beautified = $this->beautify($querystring, $executionType);
            GLOBALS::addTplVar('logsql', $beautified);
        }

        return $beautified;
    }

    /**
     * Return the time elapsed betwen two UNIX timestamp with microseconds
     *
     * @return int
     */
    private function elapsedTime()
    {
        $startTimer = $this->startTimer;
        $endTimer = $this->endTimer;

        // Stop the timer, if not done
        if (empty($endTimer)) {
            $endTimer = microtime();
        }

        $tmp = UTF8::mb_explode(" ", $startTimer);
        $startTimer = $tmp[0] + $tmp[1];
        $tmp = UTF8::mb_explode(" ", $endTimer);
        $endTimer = $tmp[0] + $tmp[1];

        return $endTimer - $startTimer;
    }
    /**
     * Turn SQL timer ON
     */
    private function sqlTimerOn()
    {
        $this->startTimer = microtime();
    }
    /**
     * Turn SQL timer OFF
     */
    private function sqlTimerOff()
    {
        $this->endTimer = microtime();
        GLOBALS::incrementDbTimeElapsed($this->elapsedTime());
    }
    /**
     * Get error information from db drivers at execution
     */
    private function getConnectionError()
    {
        $this->errno = mysqli_connect_errno();
        $this->error = mysqli_connect_error();
    }

    /**
     * Get error information from db drivers at connection
     */
    private function getExecutionError()
    {
        $this->errno = mysqli_errno($this->handle);
        $this->error = mysqli_error($this->handle);
    }
    /**
     * Open SQL database
     *
     * @return bool
     */
    private function open()
    {
        $this->sqlTimerOn();

        $dbpers = WIKINDX_DB_PERSISTENT;
        $dbhost = WIKINDX_DB_HOST;
        $dbname = WIKINDX_DB;
        $dbuser = WIKINDX_DB_USER;
        $dbpwd = WIKINDX_DB_PASSWORD;

        $dbhost = $dbpers === TRUE ? 'p:' . $dbhost : $dbhost;
        $this->handle = mysqli_connect($dbhost, $dbuser, $dbpwd, $dbname);

        $this->getConnectionError();

        if ($this->errno) {
            $this->sqlDie($this->errors->text("dbError", "open"));
        }

        $this->sqlTimerOff();
        
        $this->CheckEngineVersion();
        
        // Set for UTF8 client, results, connection
        $this->queryNoResult("SET NAMES utf8mb4 COLLATE 'utf8mb4_unicode_520_ci';");
        
        // To avoid CONCAT etc. truncating long fields during search operations. '200000' is a rough figure arrived at after some experimentation
        $this->queryNoResult("SET SESSION group_concat_max_len=200000");

        // Set the strict mode
        $this->setSqlMode('TRADITIONAL');

        return TRUE;
    }

    /**
     * Check if the MySql/MariaDB engine version is right
     * and emit a warning, in debug mode only
     */
    private function CheckEngineVersion()
    {
        if (!defined("WIKINDX_DEBUG_SQL") || WIKINDX_DEBUG_SQL) {
            $this->sqlTimerOn();
            $EngineVersion = $this->getStringEngineVersion();
            $this->sqlTimerOff();
            
            $EngineVersion = strtolower($EngineVersion);
            
            if (strstr($EngineVersion, "mariadb")) {
                $VersionMin = WIKINDX_MARIADB_VERSION_MIN; // Check MariaDB version
            } else {
                $VersionMin = WIKINDX_MYSQL_VERSION_MIN; // Check MySql or unknow engine version
            }
            
            // If the current engine version is lower than the minimum needed
            if (strcmp($EngineVersion, $VersionMin) < 0) {
                $errorMessage = "In order to support UTF-8 character sets, WIKINDX requires MySQL " . WIKINDX_MYSQL_VERSION_MIN . " or greater,
                                 or MariaDB " . WIKINDX_MARIADB_VERSION_MIN . " or greater. Your MySQL version is {" . $this->getStringEngineVersion() . "}.
                                 Please upgrade MySQL or use WIKINDX v4.2.0 which supports MySQL v4.1 and above.";
                GLOBALS::addTplVar('logsql', "<p style='font-weight:bold;color:red;'>" . $errorMessage . "</p>");
            }
        }
    }
    /**
     * execute queries and return recordset
     *
     * @param string $querystring
     * @param bool $bNoError Default is FALSE
     *
     * @return mixed An array, or a boolean if there are no data to return. Only the first result set is returned
     */
    private function internalQuery($querystring, $bNoError)
    {
        $querystring .= $this->subClause();
        $beautified = $this->printSQLDebug($querystring, 'query');

        $this->sqlTimerOn();

        $execOk = mysqli_multi_query($this->handle, $querystring);
        $this->getExecutionError();

        $recordset = mysqli_store_result($this->handle);

        $aRecordset = FALSE;
        if (is_object($recordset)) {
            while ($row = mysqli_fetch_assoc($recordset)) {
                $aRecordset[] = $row;
            }
            // Never forget to free the driver result,
            // otherwith the next mysqli_multi_query() call will fail
            mysqli_free_result($recordset);
        } else {
            $aRecordset = $execOk;
        }

        // Drop all subsequent results
        // If there are needed we can add a way to store them in this class
        // with a method to retrieve them as array
        do {
        } while (mysqli_more_results($this->handle) && mysqli_next_result($this->handle));

        $this->sqlTimerOff();

        $this->printSQLDebugTime();

        if (!$execOk && !$bNoError) {
            $this->printSQLDebug($querystring, "EXEC ERROR");
            $this->sqlDie($this->error, $beautified);
        }

        GLOBALS::incrementDbQueries();

        $this->resetSubs();

        return $aRecordset;
    }
    /**
     * Formulate subclause after main query
     *
     * @return string
     */
    private function subClause()
    {
        $clause = '';

        if (!empty($this->join)) {
            $clause .= ' ' . implode(' ', $this->join);
        }
        if (!empty($this->condition)) {
            $clause .= ' WHERE ' . implode($this->multiConditionSeparator, $this->condition);
        }
        if ($this->group) {
            $clause .= ' ' . $this->group;
        }
        if (!empty($this->order)) {
            $clause .= ' ORDER BY ' . implode(', ', $this->order);
        }
        if ($this->limit) {
            $clause .= ' ' . $this->limit;
        }

        if (!empty($this->joinUpdate)) { // To allow for restore() in case $this->join has been used in update()
            $this->join = $this->joinUpdate;
        }

        return $clause;
    }
    /**
     * Format field values for database type
     *
     * @param mixed $values Array of values or single value
     *
     * @return string
     */
    private function formatValues($values)
    {
        $array = [];

        if (!empty($values)) {
            if (!is_array($values)) {
                $values = [$values];
            }

            foreach ($values as $value) {
                $array[] = $this->tidyInput($value);
            }
        }

        return implode(', ', $array);
    }
    /**
     * Format fields for an UPDATE statement
     *
     * @see ADOSQL::update
     *
     * @param array $array
     *
     * @return string
     */
    private function formatUpdate($array)
    {
        foreach ($array as $field => $value) {
            $fieldArray[] = "`$field` = " . $this->tidyInput($value);
        }

        return 'SET ' . implode(', ', $fieldArray);
    }
    /**
     * Format fields as aliases
     *
     * @param array $array Field => Alias
     * @param string $table Default is FALSE. If TRUE, prepend table name to alias
     * @param string $tidyLeft Default is TRUE. If FALSE, do not quote the left hand side of the alias
     *
     * @return string
     */
    private function formatAlias($array, $table = FALSE, $tidyLeft = TRUE)
    {
        $key = key($array);
        $value = $array[$key];

        if ($table) {
            if ($tidyLeft) {
                return '`' . WIKINDX_DB_TABLEPREFIX . "$key` AS " . WIKINDX_DB_TABLEPREFIX . $value;
            } else {
                return       WIKINDX_DB_TABLEPREFIX . "$key AS " . WIKINDX_DB_TABLEPREFIX . $value;
            }
        }
        if (count($split = UTF8::mb_explode('.', $key)) > 1) {
            if ($tidyLeft) {
                return $this->formatTables($split[0]) . ".`$split[1]` AS $value";
            } else {
                return $this->formatTables($split[0]) . ".$split[1] AS $value";
            }
        } else {
            if ($tidyLeft) {
                return "`$key` AS $value";
            } else {
                return "$key AS $value";
            }
        }
    }
    /**
     * Format fields as aliases
     *
     * Array keys are checked for UNIX_TIMESTAMP or DATE_FORMAT
     *
     * @param array $array Field => Alias
     * @param string $tidyLeft Default is TRUE. If FALSE, do not quote the left hand side of the alias
     *
     * @return string
     */
    private function formatAliasWithExceptions($array, $tidyLeft)
    {
        $key = key($array);
        $value = $array[$key];
        /**
         * For something like DATE_FORMAT(timestamp,'%d/%b/%Y'), we don't want backticks.
         * Add other exceptions here...
         */
        if (preg_match("/^DATE_FORMAT/u", $key)) {
            return $key . " AS $value";
        }
        if (preg_match("/^UNIX_TIMESTAMP/u", $key)) {
            return $key . " AS $value";
        }
        if (count($split = UTF8::mb_explode('.', $key)) > 1) {
            return $this->formatTables($split[0]) . ".`$split[1]` AS $value";
        } else {
            if ($tidyLeft) {
                return "`$key` AS $value";
            } else {
                return "$key AS $value";
            }
        }
    }

    /**
     * Format display of SQL timer in debug mode
     */
    private function printSQLDebugTime()
    {
        if (!defined("WIKINDX_DEBUG_SQL") || WIKINDX_DEBUG_SQL) {
            GLOBALS::addTplVar('logsql', '<hr><div>Elapsed time: ' . sprintf('%.3f', round($this->elapsedTime(), 3)) . ' s</div>');
        }
    }
    /**
     * Die or throw an exception depending on the configuration
     *
     * @param string $errorMessage
     * @param string $beautified Offending SQL statement
     */
    private function sqlDie($errorMessage, $beautified = "")
    {
        echo "<!DOCTYPE html>";
        echo "<html lang=\"en\">";
        echo "<head>";
        echo "<title>WIKINDX - SQL Error</title>";
        echo "<meta charset=\"UTF-8\">";
        echo "</head>";
        echo "<body>";
        echo "<pre>";
        debug_print_backtrace();
        echo "</pre>";
        echo (trim($beautified) != "") ? "<p>" . $beautified . "</p>\n" : "";
        echo $errorMessage;
        echo "</body>";
        die();
    }
    /**
     * Beautify very briefly a SQL statement to facilitate debugging.
     *
     * Return Sql instruction packaged in a nice HTML
     *
     * @param string $sqlStatement Default is ""
     * @param string $executionType
     *
     * @return string
     */
    private function beautify($sqlStatement = '', $executionType)
    {
        $keyWords = [
            'ANALYZE ',
            'SELECT ',
            'DISTINCT ',
            'UPDATE ',
            'EXECUTE ',
            'INSERT ',
            'DELETE ',
            'SET ',
            'UNION ',
            'SHOW ',
            'ALTER ',
            'CREATE ',

            'FROM ',
            'NATURAL JOIN ',
            'INNER JOIN ',
            'CROSS JOIN ',
            'LEFT JOIN ',
            'RIGHT JOIN ',
            'LEFT JOIN ',
            'RIGHT JOIN ',
            'STRAIGHT_JOIN ',
            'WHERE ',
            'HAVING ',
            'GROUP BY ',
            'ORDER BY ',
            'CASE',
            'END',
            ' AS ',
            ' ASC',
            ' DESC',

            ' ON ',
            ' AND ',
            ' OR ',
            ' XOR ',
            ' WHEN ',
            ' THEN ',
            ' ELSE ',
            ' BETWEEN ',

            '(',
            ')',
            '[',
            ']',
            ' = ',
            '<>',
            ' IN ',
            ' IS ',
            ' NOT',
            ' LIKE ',
            ' LIMIT ',
            'NULL',
        ];
        $prettyKeyWords = [
            '<span style="font-weight:bold;color:red;">ANALYZE </span>',
            '<span style="font-weight:bold;color:red;">SELECT </span>',
            '<span style="font-weight:bold;color:red;">DISTINCT </span>',
            '<span style="font-weight:bold;color:red;">UPDATE </span>',
            '<span style="font-weight:bold;color:red;">EXECUTE </span>',
            '<span style="font-weight:bold;color:red;">INSERT </span>',
            '<span style="font-weight:bold;color:red;">DELETE </span>',
            '<span style="font-weight:bold;color:red;">SET </span>',
            '<span style="font-weight:bold;color:red;">UNION </span>',
            '<span style="font-weight:bold;color:red;">SHOW </span>',
            '<span style="font-weight:bold;color:red;">ALTER </span>',
            '<span style="font-weight:bold;color:red;">CREATE </span>',

            '<span style="font-weight:bold;color:blue;">FROM </span>',
            '<span style="font-weight:bold;color:blue;">NATURAL JOIN </span>',
            '<span style="font-weight:bold;color:blue;">INNER JOIN </span>',
            '<span style="font-weight:bold;color:blue;">CROSS JOIN </span>',
            '<span style="font-weight:bold;color:blue;">LEFT JOIN </span>',
            '<span style="font-weight:bold;color:blue;">RIGHT JOIN </span>',
            '<span style="font-weight:bold;color:blue;">LEFT OUTER JOIN </span>',
            '<span style="font-weight:bold;color:blue;">RIGHT OUTER JOIN </span>',
            '<span style="font-weight:bold;color:blue;">STRAIGHT_JOIN </span>',
            '<span style="font-weight:bold;color:blue;">WHERE </span>',
            '<span style="font-weight:bold;color:blue;">HAVING </span>',
            '<span style="font-weight:bold;color:blue;">GROUP BY </span>',
            '<span style="font-weight:bold;color:blue;">ORDER BY </span>',
            '<span style="font-weight:bold;color:blue;">CASE</span>',
            '<span style="font-weight:bold;color:blue;">END</span>',
            '<span style="font-weight:bold;color:blue;"> AS </span>',
            '<span style="font-weight:bold;color:blue;"> ASC</span>',
            '<span style="font-weight:bold;color:blue;"> DESC</span>',

            '<span style="font-weight:bold;color:green;"> ON </span>',
            '<span style="font-weight:bold;color:green;"> AND </span>',
            '<span style="font-weight:bold;color:green;"> OR </span>',
            '<span style="font-weight:bold;color:green;"> XOR </span>',
            '<span style="font-weight:bold;color:green;"> WHEN </span>',
            '<span style="font-weight:bold;color:green;"> THEN </span>',
            '<span style="font-weight:bold;color:green;"> ELSE </span>',
            '<span style="font-weight:bold;color:green;"> BETWEEN </span>',

            '<span style="font-weight:bold;color:black;">(</span>',
            '<span style="font-weight:bold;color:black;">)</span>',
            '<span style="font-weight:bold;color:black;">[</span>',
            '<span style="font-weight:bold;color:black;">]</span>',
            '<span style="font-weight:bold;color:black;"> = </span>',
            '<span style="font-weight:bold;color:black;">&lt;&gt;</span>',
            '<span style="font-weight:bold;color:black;"> IN </span>',
            '<span style="font-weight:bold;color:black;"> IS </span>',
            '<span style="font-weight:bold;color:black;"> NOT</span>',
            '<span style="font-weight:bold;color:black;"> LIKE </span>',
            '<span style="font-weight:bold;color:black;"> LIMIT </span>',
            '<span style="font-weight:bold;color:black;">NULL</span>',
        ];

        return "<hr><div><strong>[$executionType]</strong> " . str_ireplace($keyWords, $prettyKeyWords, $sqlStatement) . "</div>\n";
    }
}
