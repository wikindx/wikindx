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
 *	LOADSTATICCONFIG
 *
 *	Load config variables from config.php
 *
 * @package wikindx\core\startup
 */
loadStaticConfig();

/**
 * Load configuration from config.php
 */
function loadStaticConfig()
{
    $config = new CONFIG();
    
    $dieMsgMissing = 'Missing configuration variable in config.php: ';
    
    if (!property_exists($config, 'WIKINDX_PATH_AUTO_DETECTION')) {
        $config->WIKINDX_PATH_AUTO_DETECTION = WIKINDX_PATH_AUTO_DETECTION_DEFAULT;
    } elseif (!is_bool($config->WIKINDX_PATH_AUTO_DETECTION)) {
        die('WIKINDX_PATH_AUTO_DETECTION must be a valid boolean value');
    }
    
    // Set the current working directory -- useful for ensuring TinyMCE plug-ins can find the wikindx base path for include() commands.
    // Not all OSs allow getcwd() or sometimes the wikindx installation is in a directory that is not searchable.
    if ($config->WIKINDX_PATH_AUTO_DETECTION) {
        $config->WIKINDX_WIKINDX_PATH = realpath(\URL\getWikindxBasePath());
    } else {
        if (property_exists($config, 'WIKINDX_WIKINDX_PATH') && is_string($config->WIKINDX_WIKINDX_PATH)) {
            $config->WIKINDX_WIKINDX_PATH = realpath(trim($config->WIKINDX_WIKINDX_PATH));
        } else {
            die('WIKINDX_WIKINDX_PATH must be a valid absolute path');
        }
    }
    
    
    // Remove the last slash
    $config->WIKINDX_WIKINDX_PATH = trim(rtrim($config->WIKINDX_WIKINDX_PATH, "/"));
    
    // Test path is correct
    if (!is_file($config->WIKINDX_WIKINDX_PATH . '/core/startup/' . basename(__FILE__))) {
        if ($config->WIKINDX_PATH_AUTO_DETECTION) {
            die("
                WIKINDX is unable to set the installation path automatically.
                You should set \$WIKINDX_PATH_AUTO_DETECTION to FALSE
                and \$WIKINDX_WIKINDX_PATH in config.php.
            ");
        } else {
            die("
                The path \$WIKINDX_WIKINDX_PATH in config.php is set incorrectly.
                You should set it to a right value.
            ");
        }
    }

    
    // Set base url (default if needed)
    if ($config->WIKINDX_PATH_AUTO_DETECTION) {
        // The fallback of HTTP_HOST is used for a CLI context only
        $config->WIKINDX_BASE_URL = (PHP_SAPI !== 'cli') ? $_SERVER["HTTP_HOST"] : "localhost";
        
        // In case the code is not installed in the root folder of the vhost,
        // deduct the additional subdirectories by difference with the root folder of the vhost.
        $DOCUMENT_ROOT = realpath($_SERVER['DOCUMENT_ROOT']);
        $wikindxBasePath = realpath($config->WIKINDX_WIKINDX_PATH);
        
        if ($_SERVER['DOCUMENT_ROOT'] != $wikindxBasePath) {
            $wikindxSubPath = mb_substr($wikindxBasePath, mb_strlen($DOCUMENT_ROOT));
            $config->WIKINDX_BASE_URL .= $wikindxSubPath;
        }
    } else {
        if (!property_exists($config, 'WIKINDX_BASE_URL') || !is_string($config->WIKINDX_BASE_URL)) {
            die('WIKINDX_BASE_URL must be a valid URL');
        }
    }
    
    // Canonicalize the URL separator
    $config->WIKINDX_BASE_URL = str_replace("\\", "/", $config->WIKINDX_BASE_URL);
    
    // Remove the last slash
    $config->WIKINDX_BASE_URL = trim(rtrim($config->WIKINDX_BASE_URL, "/"));
    
    // Add the protocol requested when not defined
    // or replace it dynamically by the protocol requested by the browser (http or https)
    if (!\UTILS\matchPrefix($config->WIKINDX_BASE_URL, "http://") && !\UTILS\matchPrefix($config->WIKINDX_BASE_URL, "https://")) {
        $config->WIKINDX_BASE_URL = \URL\getCurrentProtocole() . '://' . $config->WIKINDX_BASE_URL;
    } else {
        $config->WIKINDX_BASE_URL = preg_replace('/^https?/u', \URL\getCurrentProtocole(), $config->WIKINDX_BASE_URL);
    }


    // Set database hostname
    if (!property_exists($config, 'WIKINDX_DB_HOST')) {
        die($dieMsgMissing . 'WIKINDX_DB_HOST');
    } elseif (!is_string($config->WIKINDX_DB_HOST)) {
        die('WIKINDX_DB_HOST must be a string.');
    }

    // Set database name
    if (!property_exists($config, 'WIKINDX_DB')) {
        die($dieMsgMissing . 'WIKINDX_DB');
    } elseif (!is_string($config->WIKINDX_DB)) {
        die('WIKINDX_DB must be a string.');
    }

    // Set database user
    if (!property_exists($config, 'WIKINDX_DB_USER')) {
        die($dieMsgMissing . 'WIKINDX_DB_USER');
    } elseif (!is_string($config->WIKINDX_DB_USER)) {
        die('WIKINDX_DB_USER must be a string.');
    }

    // Set database user password
    if (!property_exists($config, 'WIKINDX_DB_PASSWORD')) {
        die($dieMsgMissing . 'WIKINDX_DB_PASSWORD');
    } elseif (!is_string($config->WIKINDX_DB_PASSWORD)) {
        die('WIKINDX_DB_PASSWORD must be a string.');
    }

    // Set database table prefix
    if (!property_exists($config, 'WIKINDX_DB_TABLEPREFIX')) {
        die($dieMsgMissing . 'WIKINDX_DB_TABLEPREFIX');
    } elseif (!is_string($config->WIKINDX_DB_TABLEPREFIX)) {
        die('WIKINDX_DB_TABLEPREFIX must be a string.');
    }
    // Use always a lowercase prefix to prevent problem with case sensitive database
    $config->WIKINDX_DB_TABLEPREFIX = mb_strtolower($config->WIKINDX_DB_TABLEPREFIX);
    
    // This option is deprecated from version 5.9.1
    if ($config->WIKINDX_DB_TABLEPREFIX != WIKINDX_DB_TABLEPREFIX_DEFAULT) {
        if (defined('WIKINDX_DEBUG_ERRORS') && WIKINDX_DEBUG_ERRORS) {
            trigger_error(
                "\$WIKINDX_DB_TABLEPREFIX configuration option is deprecated since version 5.9.1
    			and will be removed in the next release. People who have changed the prefix should
    			rename the tables with the default prefix (" . WIKINDX_DB_TABLEPREFIX_DEFAULT . ")
    			and correct their configuration. It will no longer be possible to install two WIKINDXs
    			in the same database. If you are in this rare case contact us.",
                E_USER_DEPRECATED
            );
        }
    }

    // Set database persistent mode
    if (!property_exists($config, 'WIKINDX_DB_PERSISTENT')) {
        die($dieMsgMissing . 'WIKINDX_DB_PERSISTENT');
    } elseif (!is_bool($config->WIKINDX_DB_PERSISTENT)) {
        die('WIKINDX_DB_PERSISTENT must be a boolean (TRUE / FALSE).');
    }

    // Attempt to set the memory the script uses -- does not work in safe mode
    if (!property_exists($config, 'WIKINDX_MEMORY_LIMIT')) {
        $config->WIKINDX_MEMORY_LIMIT = WIKINDX_MEMORY_LIMIT_DEFAULT;
    } elseif (is_string($config->WIKINDX_MEMORY_LIMIT)) {
        if (preg_match('/^\d+[KMG]?$/u', $config->WIKINDX_MEMORY_LIMIT) === FALSE) {
            die('Syntax Error in WIKINDX_MEMORY_LIMIT. See https://secure.php.net/manual/fr/faq.using.php#faq.using.shorthandbytes');
        } elseif (is_int($config->WIKINDX_MEMORY_LIMIT)) {
            if ($config->WIKINDX_MEMORY_LIMIT < -1) {
                die('WIKINDX_MEMORY_LIMIT must be a positive integer.');
            }
        }
    }
    ini_set("memory_limit", $config->WIKINDX_MEMORY_LIMIT);

    // Attempt to set the max time the script runs for -- does not work in safe mode
    if (!property_exists($config, 'WIKINDX_MAX_EXECUTION_TIMEOUT')) {
        $config->WIKINDX_MAX_EXECUTION_TIMEOUT = WIKINDX_MAX_EXECUTION_TIMEOUT_DEFAULT;
    } elseif (is_string($config->WIKINDX_MAX_EXECUTION_TIMEOUT)) { // v4 config.php required quotes around value
        if (!$config->WIKINDX_MAX_EXECUTION_TIMEOUT = intval($config->WIKINDX_MAX_EXECUTION_TIMEOUT)) {
            die('WIKINDX_MAX_EXECUTION_TIMEOUT must be a positive integer (or FALSE for default configuration of PHP).');
        }
    } elseif (!is_int($config->WIKINDX_MAX_EXECUTION_TIMEOUT)) {
        if ($config->WIKINDX_MAX_EXECUTION_TIMEOUT !== FALSE) {
            die('WIKINDX_MAX_EXECUTION_TIMEOUT must be a positive integer (or FALSE for default configuration of PHP).');
        } elseif ($config->WIKINDX_MAX_EXECUTION_TIMEOUT < 0) {
            die('WIKINDX_MAX_EXECUTION_TIMEOUT must be a positive integer (or FALSE for default configuration of PHP).');
        }
    }
    // Configure it only if explicitely defined
    if ($config->WIKINDX_MAX_EXECUTION_TIMEOUT !== FALSE) {
        ini_set("max_execution_time", $config->WIKINDX_MAX_EXECUTION_TIMEOUT);
    }

    // Set max write chunk for file writing
    if (!property_exists($config, 'WIKINDX_MAX_WRITECHUNK')) {
        $config->WIKINDX_MAX_WRITECHUNK = WIKINDX_MAX_WRITECHUNK_DEFAULT;
    } elseif (!is_int($config->WIKINDX_MAX_WRITECHUNK)) {
        if ($config->WIKINDX_MAX_WRITECHUNK !== FALSE) {
            die('WIKINDX_MAX_WRITECHUNK must be a positive integer (or FALSE for default configuration).');
        } else {
            $config->WIKINDX_MAX_WRITECHUNK = WIKINDX_MAX_WRITECHUNK_DEFAULT;
        }
    } elseif ($config->WIKINDX_MAX_WRITECHUNK < 1) {
        die('WIKINDX_MAX_WRITECHUNK must be a positive integer (or FALSE for default configuration).');
    }
    
    // Redefine all PHP config as constant for making them pervasive for the whole application
    define("WIKINDX_DB_HOST", $config->WIKINDX_DB_HOST);
    define("WIKINDX_DB", $config->WIKINDX_DB);
    define("WIKINDX_DB_USER", $config->WIKINDX_DB_USER);
    define("WIKINDX_DB_PASSWORD", $config->WIKINDX_DB_PASSWORD);
    define("WIKINDX_DB_TABLEPREFIX", $config->WIKINDX_DB_TABLEPREFIX);
    define("WIKINDX_DB_PERSISTENT", $config->WIKINDX_DB_PERSISTENT);
    define("WIKINDX_PATH_AUTO_DETECTION", $config->WIKINDX_PATH_AUTO_DETECTION);
    define("WIKINDX_BASE_URL", $config->WIKINDX_BASE_URL);
    define("WIKINDX_WIKINDX_PATH", $config->WIKINDX_WIKINDX_PATH);
    define("WIKINDX_MEMORY_LIMIT", $config->WIKINDX_MEMORY_LIMIT);
    define("WIKINDX_MAX_EXECUTION_TIMEOUT", $config->WIKINDX_MAX_EXECUTION_TIMEOUT);
    define("WIKINDX_MAX_WRITECHUNK", $config->WIKINDX_MAX_WRITECHUNK);
}
