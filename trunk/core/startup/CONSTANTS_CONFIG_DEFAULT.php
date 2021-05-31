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

define('WIKINDX_DB_DEFAULT', 'wikindx5');
define('WIKINDX_DB_HOST_DEFAULT', 'localhost');
define('WIKINDX_DB_PASSWORD_DEFAULT', 'wikindx');
define('WIKINDX_DB_USER_DEFAULT', 'wikindx');
define('WIKINDX_MAX_EXECUTION_TIMEOUT_DEFAULT', FALSE);
define('WIKINDX_MAX_WRITECHUNK_DEFAULT', 10000);
define('WIKINDX_MEMORY_LIMIT_DEFAULT', FALSE);
define('WIKINDX_PATH_AUTO_DETECTION_DEFAULT', TRUE);
define('WIKINDX_URL_BASE_DEFAULT', FALSE);
