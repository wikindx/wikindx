<?php

/**
 * MySQL database backup class, version 1.0.0
 * Written by Vagharshak Tozalakyan <vagh@armdex.com>
 * Released under GNU Public license
 *
 * Adaptations for mysqli, to handle NULLs etc. by Mark Grimshaw-Aagaard April 2013
 * Change mysql drivers form mysql to mysqli (PHP >= 5.5.x compliance) by S. Aulery 2016
 *
 * NB (8 june 2016): It seems that this class is no longer maintained by the original author
 */
define('MSB_VERSION', '1.5');

define('MSB_STRING', 0); // Return SQL commands as a single output string
define('MSB_DOWNLOAD', 1); // Download backup file to the user's computer
define('MSB_SAVE', 2); // Create the backup file on the server

class MySQL_Backup
{
    public $server = 'localhost';
    // 3306 is the default port of MySQL
    public $port = 3306;
    public $username = 'root';
    public $password = '';
    public $database = '';
    public $link_id = -1;
    public $connected = FALSE;
    public $tables = [];
    public $drop_tables = TRUE;
    public $struct_only = FALSE;
    public $comments = TRUE;
    public $backup_dir = '';
    public $fname_format = 'd_m_y__H_i_s';
    public $error = '';
    public $errno = 0;

    private $db;
    private $vars;
    private $prefix;


    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->server = WIKINDX_DB_HOST;
        $this->username = WIKINDX_DB_USER;
        $this->password = WIKINDX_DB_PASSWORD;
        $this->database = WIKINDX_DB;
        $this->prefix = WIKINDX_DB_TABLEPREFIX;
    }


    public function Execute($task = MSB_STRING, $fname = '', $compress = FALSE)
    {
        if (!($sql = $this->_Retrieve()))
        {
            return FALSE;
        }

        if ($task == MSB_SAVE)
        {
            if (empty($fname))
            {
                $fname = $this->backup_dir . DIRECTORY_SEPARATOR;
                $fname .= date($this->fname_format);
                $fname .= ($compress ? '.sql.gz' : '.sql');
            }

            return $this->_SaveToFile($fname, $sql, $compress);
        }
        elseif ($task == MSB_DOWNLOAD)
        {
            if (empty($fname))
            {
                $fname = date($this->fname_format);
                $fname .= ($compress ? '.sql.gz' : '.sql');
            }

            return $this->_DownloadFile($fname, $sql, $compress);
        }
        else
        {
            return $sql;
        }
    }


    public function _Query($sql)
    {
        $result = $this->db->query($sql);

        $this->error = $this->db->error;
        $this->errno = $this->db->errno;

        return $result;
    }


    public function _GetTables()
    {
        $value = [];
        $tables = $this->db->listTables(TRUE);

        foreach ($tables as $table)
        {
            if (empty($this->tables) || in_array($table, $this->tables))
            {
                // Process only tables of Wikindx
                if (substr($table, 0, strlen($this->prefix)) == $this->prefix)
                {
                    $value[] = $table;
                }
            }
        }

        if (!count($value))
        {
            $this->error = 'No tables found in database.';
            $this->errno = $this->db->errno;

            return FALSE;
        }

        return $value;
    }


    public function _DumpTable($table, $nulls)
    {
        $value = '';

        if ($this->comments)
        {
            $value .= '#' . "\n";
            $value .= '# Table structure for table `' . $table . '`' . "\n";
            $value .= '#' . "\n\n";
        }

        if ($this->drop_tables)
        {
            $value .= 'DROP TABLE IF EXISTS `' . $table . '`;' . "\n";
        }

        // Lock the table
        // Generetable a CREATE script for the table
        // Unlock the table
        $sql = "SHOW CREATE TABLE `$table`;";

        // Try to generate the CREATE script
        if (!($result = $this->_Query($sql)))
        {
            return FALSE;
        }

        $row = $this->db->fetchRow($result);
        $value .= str_replace("\n", "\n", stripslashes($row['Create Table'])) . ';';
        $value .= "\n" . "\n";

        if (!$this->struct_only)
        {
            if ($this->comments)
            {
                $value .= '#' . "\n";
                $value .= '# Dumping data for table `' . $table . '`' . "\n";
                $value .= '#' . "\n\n";
            }

            $value .= $this->_GetInserts($table, $nulls);
        }

        $value .= "\n" . "\n";

        return $value;
    }


    public function _GetInserts($table, $nulls)
    {
        $value = '';
        $prefix = $this->prefix;

        // Lock the table
        $this->_Query("LOCK TABLES `$table` WRITE;");

        // Select all rows of the table
        $sql = $this->db->selectNoExecute(preg_replace("/$prefix/ui", '', $table), '*') . ";\n";

        if (($result = $this->db->query($sql)))
        {
            $row = $this->db->fetchRow($result);

            if (is_array($row))
            {
                $fields = [];
                foreach ($row as $field => $data)
                {
                    $fields[] = "`" . $field . "`";
                }

                $listOfFields = '(' . implode(',', $fields) . ')';
                $listValues = '';

                do
                {
                    $array = [];
                    foreach ($row as $field => $data)
                    {
                        if (!$data && (array_search($field, $nulls) !== FALSE))
                        {
                            $array[] = 'NULL';
                        }
                        elseif (ctype_digit($data))
                        {
                            $array[] = $data;
                        }
                        else
                        {
                            $array[] = "'" . $this->db->escapeString($data) . "'";
                        }
                    }

                    $listValues .= "\n" . '(' . implode(',', $array) . '),';
                } while ($row = $this->db->fetchRow($result));

                $listValues = mb_substr($listValues, 0, -1);

                $value = "INSERT INTO `$table` $listOfFields VALUES $listValues;" . "\n";
            }
        }

        // Unlock the table
        $this->_Query("UNLOCK TABLES;");

        return $value;
    }


    public function _Retrieve()
    {
        $value = '';

        if ($this->comments)
        {
            $value .= '#' . "\n";
            $value .= '# Database dump' . "\n";
            $value .= '# Created by MySQL_Backup class, ver. ' . MSB_VERSION . "\n";
            $value .= '#' . "\n";
            $value .= '# Host: ' . $this->server . "\n";
            $value .= '# Generated: ' . date('M j, Y') . ' at ' . date('H:i') . "\n";
            $value .= '# MySQL version: ' . $this->db->queryFetchFirstField("SELECT version() AS EngineVersion;") . "\n";
            $value .= '# PHP version: ' . phpversion() . "\n";
            $value .= '#' . "\n";
            $value .= '# Database: `' . $this->database . '`' . "\n";

            $value .= '#' . "\n\n\n";
        }

        if (!($tables = $this->_GetTables()))
        {
            return FALSE;
        }

        foreach ($tables as $table)
        {
            // For ANSI behavior (MySQL, PG at least)
            // We must always use TABLE_SCHEMA in the WHERE clause
            // and the raw value of TABLE_SCHEMA otherwise MySQL scans
            // the disk for db names and slow down the server
            // https://dev.mysql.com/doc/refman/5.7/en/information-schema-optimization.html
            $sql = "
			    SELECT COLUMN_NAME, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
			    WHERE
			        TABLE_NAME = '$table'
			        AND TABLE_SCHEMA = '" . $this->database . "';";

            $result = $this->_Query($sql);

            $nulls = [];
            while ($row = $this->db->fetchRow($result))
            {
                if ($row['IS_NULLABLE'] == 'YES')
                {
                    $nulls[] = $row['COLUMN_NAME'];
                }
            }

            if (!($table_dump = $this->_DumpTable($table, $nulls)))
            {
                $this->error = $this->db->error;
                $this->errno = $this->db->errno;

                return FALSE;
            }

            $value .= $table_dump;
        }

        return $value;
    }


    public function _SaveToFile($fname, $sql, $compress)
    {
        if ($compress)
        {
            if (!($zf = gzopen($fname, 'w9')))
            {
                $e = error_get_last();
                $this->error = $e['message'];
                $this->errno = $e['type'];

                return FALSE;
            }
            else
            {
                gzwrite($zf, $sql);
                gzclose($zf);
            }
        }
        else
        {
            if ($f = fopen($fname, 'w'))
            {
                fwrite($f, $sql);
                fclose($f);
            }
            else
            {
                $e = error_get_last();
                $this->error = $e['message'];
                $this->errno = $e['type'];

                return FALSE;
            }
        }

        return TRUE;
    }


    public function _DownloadFile($fname, $sql, $compress)
    {
        header('Content-disposition: filename="' . $fname . '";');
        header('Content-type: application/octetstream');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo($compress ? gzencode($sql) : $sql);

        return TRUE;
    }
}
