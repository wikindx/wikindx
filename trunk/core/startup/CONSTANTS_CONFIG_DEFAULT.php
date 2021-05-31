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
 * CONSTANTS_CONFIG_DEFAULT
 *
 * Declare the default values of the properties of the CONFIG class defined in config.php.
 *
 * Each default value is a constant where the name is the name of the corresponding property and the suffix "_DEFAULT".
 *
 * @package wikindx\core\startup
 */

/**
 * Default name of the MariaDB/MySQL database (case-sensitive)
 *
 * cf. https://dev.mysql.com/doc/mysql-reslimits-excerpt/5.6/en/identifier-length.html
 *
 * @global string WIKINDX_DB_DEFAULT
 */
define('WIKINDX_DB_DEFAULT', 'wikindx');
/**
 * Default name of the MariaDB/MySQL host server (case-insensitive)
 *
 * @global string WIKINDX_DB_HOST_DEFAULT
 */
define('WIKINDX_DB_HOST_DEFAULT', 'localhost');
/**
 * Default password of the user required to connect to and open the database (case-sensitive)
 *
 * @global string WIKINDX_DB_PASSWORD_DEFAULT
 */
define('WIKINDX_DB_PASSWORD_DEFAULT', 'wikindx');
/**
 * Default username required to connect to and open the database (case-sensitive)
 *
 * @global string WIKINDX_DB_USER_DEFAULT
 */
define('WIKINDX_DB_USER_DEFAULT', 'wikindx');
/**
 * Default max execution time of a script/page (in seconds, or FALSE)
 *
 * @global bool|int WIKINDX_MAX_EXECUTION_TIMEOUT_DEFAULT
 */
define('WIKINDX_MAX_EXECUTION_TIMEOUT_DEFAULT', FALSE);
/**
 * Default number of resources processed during a file operation (positive integer) (boolean)
 *
 * @global int WIKINDX_MAX_WRITECHUNK_DEFAULT
 */
define('WIKINDX_MAX_WRITECHUNK_DEFAULT', 10000);
/**
 * Default custom value for PHP memory_limit option (interger, string, or FALSE)
 *
 * @global bool|int WIKINDX_MEMORY_LIMIT_DEFAULT
 */
define('WIKINDX_MEMORY_LIMIT_DEFAULT', FALSE);
/**
 * Default value of path auto detection feature (for Virtual Host Apache config)
 *
 * @global bool WIKINDX_PATH_AUTO_DETECTION_DEFAULT
 */
define('WIKINDX_PATH_AUTO_DETECTION_DEFAULT', TRUE);
/**
 * Default base URL of WIKINDX (string, or FALSE)
 *
 * @global bool|string WIKINDX_URL_BASE_DEFAULT
 */
define('WIKINDX_URL_BASE_DEFAULT', FALSE);
