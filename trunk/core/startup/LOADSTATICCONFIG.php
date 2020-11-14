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
 *	LOADSTATICCONFIG
 *
 *	Load config variables from config.php
 *
 * @package wikindx\core\startup
 */
loadStaticConfig();

/**
 * Load configuration from config.php
 *
 * @return array Array of error messages
 */
function loadStaticConfig()
{
    $errors = [];

    $config = new \CONFIG();
    
    $dieMsgMissing = 'Missing configuration variable in config.php: ';
    
    if (!property_exists($config, 'WIKINDX_PATH_AUTO_DETECTION'))
    {
        $config->WIKINDX_PATH_AUTO_DETECTION = WIKINDX_PATH_AUTO_DETECTION_DEFAULT;
    }
    elseif (!is_bool($config->WIKINDX_PATH_AUTO_DETECTION))
    {
        $errors[] = 'WIKINDX_PATH_AUTO_DETECTION must be a valid boolean value (switch to ' . WIKINDX_PATH_AUTO_DETECTION_DEFAULT . ' by default)';
        $config->WIKINDX_PATH_AUTO_DETECTION = TRUE;
    }
    
    
    // Set base url (default if needed)
    if ($config->WIKINDX_PATH_AUTO_DETECTION)
    {
        // The fallback of HTTP_HOST is used for a CLI context only
        $config->WIKINDX_URL_BASE = (PHP_SAPI !== 'cli') ? $_SERVER["HTTP_HOST"] : "localhost";
        
        // In case the code is not installed in the root folder of the vhost,
        // deduct the additional subdirectories by difference with the root folder of the vhost.
        $DOCUMENT_ROOT = realpath($_SERVER['DOCUMENT_ROOT']);
        
        if ($_SERVER['DOCUMENT_ROOT'] != WIKINDX_DIR_BASE)
        {
            $wikindxSubPath = mb_substr(WIKINDX_DIR_BASE, mb_strlen($DOCUMENT_ROOT));
            $config->WIKINDX_URL_BASE .= $wikindxSubPath;
        }
    }
    else
    {
        if (!property_exists($config, 'WIKINDX_URL_BASE') || !is_string($config->WIKINDX_URL_BASE))
        {
            $errors[] = 'WIKINDX_URL_BASE must be a valid URL (switch to "" by default)';
            $config->WIKINDX_URL_BASE = "";
        }
    }
    
    // Canonicalize the URL separator
    $config->WIKINDX_URL_BASE = str_replace("\\", "/", $config->WIKINDX_URL_BASE);
    
    // Remove the last slash
    $config->WIKINDX_URL_BASE = trim(rtrim($config->WIKINDX_URL_BASE, "/"));
    
    // Add the protocol requested when not defined
    // or replace it dynamically by the protocol requested by the browser (http or https)
    if (!\UTILS\matchPrefix($config->WIKINDX_URL_BASE, "http://") && !\UTILS\matchPrefix($config->WIKINDX_URL_BASE, "https://"))
    {
        $config->WIKINDX_URL_BASE = \URL\getCurrentProtocole() . '://' . $config->WIKINDX_URL_BASE;
    }
    else
    {
        $config->WIKINDX_URL_BASE = preg_replace('/^https?/u', \URL\getCurrentProtocole(), $config->WIKINDX_URL_BASE);
    }


    // Set database hostname
    if (!property_exists($config, 'WIKINDX_DB_HOST'))
    {
        $errors[] = $dieMsgMissing . 'WIKINDX_DB_HOST';
        $config->WIKINDX_DB_HOST = "localhost";
    }
    elseif (!is_string($config->WIKINDX_DB_HOST))
    {
        $errors[] = 'WIKINDX_DB_HOST must be a string.';
        $config->WIKINDX_DB_HOST = "localhost";
    }

    // Set database name
    if (!property_exists($config, 'WIKINDX_DB'))
    {
        $errors[] = $dieMsgMissing . 'WIKINDX_DB';
        $config->WIKINDX_DB = "";
    }
    elseif (!is_string($config->WIKINDX_DB))
    {
        $errors[] = 'WIKINDX_DB must be a string.';
        $config->WIKINDX_DB = "";
    }

    // Set database user
    if (!property_exists($config, 'WIKINDX_DB_USER'))
    {
        $errors[] = $dieMsgMissing . 'WIKINDX_DB_USER';
        $config->WIKINDX_DB_USER = "";
    }
    elseif (!is_string($config->WIKINDX_DB_USER))
    {
        $errors[] = 'WIKINDX_DB_USER must be a string.';
        $config->WIKINDX_DB_USER = "";
    }

    // Set database user password
    if (!property_exists($config, 'WIKINDX_DB_PASSWORD'))
    {
        $errors[] = $dieMsgMissing . 'WIKINDX_DB_PASSWORD';
        $config->WIKINDX_DB_PASSWORD = "";
    }
    elseif (!is_string($config->WIKINDX_DB_PASSWORD))
    {
        $errors[] = 'WIKINDX_DB_PASSWORD must be a string.';
        $config->WIKINDX_DB_PASSWORD = "";
    }

    // Attempt to set the memory the script uses -- does not work in safe mode
    if (!property_exists($config, 'WIKINDX_MEMORY_LIMIT'))
    {
        $config->WIKINDX_MEMORY_LIMIT = WIKINDX_MEMORY_LIMIT_DEFAULT;
    }
    elseif (is_string($config->WIKINDX_MEMORY_LIMIT))
    {
        if (preg_match('/^\d+[KMG]?$/u', $config->WIKINDX_MEMORY_LIMIT) === FALSE)
        {
            $errors[] = 'Syntax Error in WIKINDX_MEMORY_LIMIT. See https://secure.php.net/manual/fr/faq.using.php#faq.using.shorthandbytes';
            $config->WIKINDX_MEMORY_LIMIT = WIKINDX_MEMORY_LIMIT_DEFAULT;
        }
        elseif (is_int($config->WIKINDX_MEMORY_LIMIT))
        {
            if ($config->WIKINDX_MEMORY_LIMIT < -1)
            {
                $errors[] = 'WIKINDX_MEMORY_LIMIT must be a positive integer.';
                $config->WIKINDX_MEMORY_LIMIT = WIKINDX_MEMORY_LIMIT_DEFAULT;
            }
        }
    }
    ini_set("memory_limit", $config->WIKINDX_MEMORY_LIMIT);

    // Attempt to set the max time the script runs for -- does not work in safe mode
    if (!property_exists($config, 'WIKINDX_MAX_EXECUTION_TIMEOUT'))
    {
        $config->WIKINDX_MAX_EXECUTION_TIMEOUT = WIKINDX_MAX_EXECUTION_TIMEOUT_DEFAULT;
    }
    elseif (is_string($config->WIKINDX_MAX_EXECUTION_TIMEOUT))
    { // v4 config.php required quotes around value
        if (!$config->WIKINDX_MAX_EXECUTION_TIMEOUT = intval($config->WIKINDX_MAX_EXECUTION_TIMEOUT))
        {
            $errors[] = 'WIKINDX_MAX_EXECUTION_TIMEOUT must be a positive integer (or FALSE for default configuration of PHP).';
            $config->WIKINDX_MAX_EXECUTION_TIMEOUT = WIKINDX_MAX_EXECUTION_TIMEOUT_DEFAULT;
        }
    }
    elseif (!is_int($config->WIKINDX_MAX_EXECUTION_TIMEOUT))
    {
        if ($config->WIKINDX_MAX_EXECUTION_TIMEOUT !== FALSE)
        {
            $errors[] = 'WIKINDX_MAX_EXECUTION_TIMEOUT must be a positive integer (or FALSE for default configuration of PHP).';
            $config->WIKINDX_MAX_EXECUTION_TIMEOUT = WIKINDX_MAX_EXECUTION_TIMEOUT_DEFAULT;
        }
        elseif ($config->WIKINDX_MAX_EXECUTION_TIMEOUT < 0)
        {
            $errors[] = 'WIKINDX_MAX_EXECUTION_TIMEOUT must be a positive integer (or FALSE for default configuration of PHP).';
            $config->WIKINDX_MAX_EXECUTION_TIMEOUT = WIKINDX_MAX_EXECUTION_TIMEOUT_DEFAULT;
        }
    }
    // Configure it only if explicitely defined
    if ($config->WIKINDX_MAX_EXECUTION_TIMEOUT !== FALSE)
    {
        ini_set("max_execution_time", $config->WIKINDX_MAX_EXECUTION_TIMEOUT);
    }

    // Set max write chunk for file writing
    if (!property_exists($config, 'WIKINDX_MAX_WRITECHUNK'))
    {
        $config->WIKINDX_MAX_WRITECHUNK = WIKINDX_MAX_WRITECHUNK_DEFAULT;
    }
    elseif (!is_int($config->WIKINDX_MAX_WRITECHUNK))
    {
        if ($config->WIKINDX_MAX_WRITECHUNK !== FALSE)
        {
            $errors[] = 'WIKINDX_MAX_WRITECHUNK must be a positive integer (or FALSE for default configuration).';
        }
        $config->WIKINDX_MAX_WRITECHUNK = WIKINDX_MAX_WRITECHUNK_DEFAULT;
    }
    elseif ($config->WIKINDX_MAX_WRITECHUNK < 1)
    {
        $errors[] = 'WIKINDX_MAX_WRITECHUNK must be a positive integer (or FALSE for default configuration).';
        $config->WIKINDX_MAX_WRITECHUNK = WIKINDX_MAX_WRITECHUNK_DEFAULT;
    }
    
    // Redefine all PHP config as constant for making them pervasive for the whole application
    foreach ([
        "WIKINDX_DB_HOST",
        "WIKINDX_DB",
        "WIKINDX_DB_USER",
        "WIKINDX_DB_PASSWORD",
        "WIKINDX_PATH_AUTO_DETECTION",
        "WIKINDX_URL_BASE",
        "WIKINDX_MEMORY_LIMIT",
        "WIKINDX_MAX_EXECUTION_TIMEOUT",
        "WIKINDX_MAX_WRITECHUNK",
    ] as $unused => $option)
    {
        if (!defined($option) && property_exists($config, $option))
        {
            define($option, $config->{$option});
        }
    }

    return $errors;
}
