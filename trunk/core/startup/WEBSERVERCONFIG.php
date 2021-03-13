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
 * WEBSERVERCONFIG
 *
 * CONFIGURE the webserver with default parameters
 * compliant with WIKINDX before applying
 * its adminuser custom configuration.
 *
 * This have to be included at the top of each independant script
 * (index.php, plugins, dialogs, etc).
 *
 * We intend to provide correct default values for anything
 * that is not very interesting for the end user.
 *
 * @package wikindx\core\startup
 */


/**
 * Import CONSTANTS
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "CONSTANTS.php"]));


/**
 * On startup, reports all errors.
 * After that, error reporting is reconfigured
 * by core/startup/LOADCONFIG.php (first called in AUTHORIZE) with user config.
 * This prevent to hide tricky errors.
 *
 * PHP 8 will make E_ALL the default error level,
 * in preparation for its support we also make this level the default
 */
error_reporting(E_ALL);
ini_set('display_startup_errors', TRUE);
ini_set('html_errors', (PHP_SAPI !== 'cli'));
ini_set('display_errors', TRUE);

/**
 * Fix default charset of PHP interpret, PHP libs and protocols
 */

// Default charset
ini_set('default_charset', WIKINDX_CHARSET);
ini_set('mbstring.encoding_translation', WIKINDX_CHARSET);
ini_set('mbstring.detect_order', WIKINDX_CHARSET . ",ISO-8859-15,ISO-8859-1,ASCII");

// HTTP charset (HTTP specification doesn't permit to declare Content-type separetly)
header('Content-type: ' . WIKINDX_HTTP_CONTENT_TYPE_DEFAULT . '; charset=' . WIKINDX_CHARSET);

// make sure that Session output is XHTML conform ('&amp;' instead of '&')
ini_set('arg_separator.output', '&amp;');

// Disable a syntax uncompatible with XML
ini_set('short_open_tag', 'Off');

// Disable PHP arg_v, argc, shebang
ini_set('register_argc_argv', 'Off');
ini_set('cgi.check_shebang_line', 'Off');

//TODO : this option can only be activated when the plugins
//       start the session after the inclusion of this code.
// Disallow access to session cookie by JavaScript
// This setting prevents cookies stolen by JavaScript injection
//ini_set('session.cookie_httponly', 'On');

//TODO : this option can only be activated when the plugins
//       start the session after the inclusion of this code.
// Session module only accepts valid session ID generated by session module
// It rejects session ID supplied by user forgery
//ini_set('session.use_strict_mode', 'On');



// Set the time zone to whatever the default is to avoid 500 errors
// Will default to UTC if it's not set properly in php.ini
date_default_timezone_set(@date_default_timezone_get());


// Check PHP minimum version and above.
if (version_compare(PHP_VERSION, WIKINDX_PHP_VERSION_MIN, '<'))
{
    $PHPVersion = PHP_VERSION;
    $PHPVersionMin = WIKINDX_PHP_VERSION_MIN;
    $SourceFile = __FILE__;
    $CodeLine = __LINE__ - 6;
    $styledir = WIKINDX_URL_COMPONENT_TEMPLATES . "/" . WIKINDX_TEMPLATE_DEFAULT;
    $msg = <<<EOM
<!DOCTYPE html>
<html lang="en">
<head>
	<title>WIKINDX</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="$styledir/template.css" type="text/css">
	<link rel="shortcut icon" type="image/x-icon" href="$styledir/images/favicon.ico">
</head>
<body>

<h1>Configuration error: PHP outdated</h1>

<p>WIKINDX requires PHP <strong>$PHPVersionMin</strong> or greater.  Your PHP version is <em>$PHPVersion</em>.</p>

<p>You can disable this check in file $SourceFile at line $CodeLine for migration purposes,
   but it is definitely not recommended for good functionality of all parts of
   the software and for the security of permanently disabling it.</p>
   
<p>A list of compatibility with PHP is available in the documentation (see docs/README.txt).</p>
   
<p>Please upgrade PHP as soon as possible.</p>

<p>After that, refresh this page (with Ctrl+F5) or <a href="index.php">follow this link</a>.</p>

</body>
EOM;
    die($msg);
}


// Check if mandatories extensions are enabled.
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "libs", "UTILS.php"]));
$MandatoryExtensions = \UTILS\listCoreMandatoryPHPExtensions();
$InstalledExtensions = get_loaded_extensions();
$MissingExtensions = array_diff($MandatoryExtensions, $InstalledExtensions);

if (count($MissingExtensions) > 0)
{
    $EnabledExtensions = array_intersect($MandatoryExtensions, $InstalledExtensions);
    $ListExtensions = '<tr><td>' . implode('</td><td style="color:red">DISABLED</td></tr><tr><td>', $MissingExtensions) . '<td style="color:red">DISABLED</td></tr>';
    $ListExtensions .= '<tr><td>' . implode('</td><td style="color:green">ENABLED</td></tr><tr><td>', $EnabledExtensions) . '<td style="color:green">ENABLED</td></tr>';
    $styledir = WIKINDX_URL_COMPONENT_TEMPLATES . "/" . WIKINDX_TEMPLATE_DEFAULT;
    $msg = <<<EOM
<!DOCTYPE html>
<html lang="en">
<head>
	<title>WIKINDX</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="$styledir/template.css" type="text/css">
	<link rel="shortcut icon" type="image/x-icon" href="$styledir/images/favicon.ico">
</head>
<body>

<h1>Configuration error: missing PHP extensions</h1>

<p>WIKINDX requires the following PHP extensions to work properly :</p>

<table style="border: 1px solid black; width:33%">
<tr style="border: 1px solid black;">
    <th>Extension</th>
    <th>Status</th>
</tr>
    $ListExtensions
</table>

<p>Please install and enable them.</p>

<p>After that, refresh this page (with Ctrl+F5) or <a href="index.php">follow this link</a>.</p>

</body>
EOM;
    die($msg);
}

// Check PHP execution environment (CLI isn't supported)
if (PHP_SAPI === 'cli')
{
    die("WIKINDX doesn't support CLI execution.");
}

// Check for presence of config.php
if (!is_file(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "config.php"])))
{
    $styledir = WIKINDX_URL_COMPONENT_TEMPLATES . "/" . WIKINDX_TEMPLATE_DEFAULT;
    $msg = <<<EOM
<!DOCTYPE html>
<html lang="en">
<head>
	<title>WIKINDX</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="$styledir/template.css" type="text/css">
	<link rel="shortcut icon" type="image/x-icon" href="$styledir/images/favicon.ico">
</head>
<body>

<h1>Configuration error: <em>config.php</em> file missing</h1>

<p><em>config.php</em> file is missing. If this is a new installation,
copy <em>config.php.dist</em> to <em>config.php</em> and edit that file
to ensure the MySQL access protocols match
those you have specified for the WIKINDX database.</p>

<p>Ensure also that the
<em>components/plugins</em>,
<em>components/styles</em>,
<em>components/templates</em>,
and <em>components/vendor</em>
folders and all they contain are writable by the web server user (usually <em>nobody</em> or <em>www-data</em> account).</p>

<p>After that, refresh this page (with F5) or <a href="index.php">follow this link</a>.</p>

</body>
EOM;
    die($msg);
}

// Include the config file and check if the CONFIG class is in place
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "config.php"]));

if (!class_exists("CONFIG"))
{
    $styledir = WIKINDX_URL_COMPONENT_TEMPLATES . "/" . WIKINDX_TEMPLATE_DEFAULT;
    $msg = <<<EOM
<!DOCTYPE html>
<html lang="en">
<head>
	<title>WIKINDX</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="$styledir/template.css" type="text/css">
	<link rel="shortcut icon" type="image/x-icon" href="$styledir/images/favicon.ico">
</head>
<body>

<h1>Configuration error: <strong>CONFIG</strong> class missing in <em>config.php</em> file</h1>

<p><strong>CONFIG</strong> class is missing in <em>config.php</em> file. If this is a new installation,
copy <em>config.php.dist</em> to <em>config.php</em> and edit that file
to ensure the MySQL access protocols match
those you have specified for the WIKINDX database.</p>

<p>Ensure also that the
<em>components/languages</em>,
<em>components/plugins</em>,
<em>components/styles</em>,
<em>components/templates</em>,
and <em>components/vendor</em>
folders and all they contain are writable by the web server user (usually <em>nobody</em> or <em>www-data</em> account).</p>

<p>After that, refresh this page (with F5) or <a href="index.php">follow this link</a>.</p>

</body>
EOM;
    die($msg);
}

// Create components directories
foreach ([WIKINDX_DIR_COMPONENT_PLUGINS, WIKINDX_DIR_COMPONENT_STYLES, WIKINDX_DIR_COMPONENT_TEMPLATES, WIKINDX_DIR_COMPONENT_VENDOR] as $dir)
{
    $dir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, $dir]);
    if (!file_exists($dir))
    {
        // Continue without error, a procedure checks the permissions further.
        if (!@mkdir($dir, WIKINDX_UNIX_PERMS_DEFAULT, TRUE))
        {
            echo "<div>Directory <strong>" . $dir . "</strong> has not been created. Check permissions.</div>\n";
        }
    }
}

// Create data directories
foreach ([WIKINDX_DIR_DATA, WIKINDX_DIR_DATA_ATTACHMENTS, WIKINDX_DIR_DATA_FILES, WIKINDX_DIR_DATA_IMAGES, WIKINDX_DIR_DATA_PLUGINS] as $dir)
{
    $dir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, $dir]);
    if (!file_exists($dir))
    {
        // Continue without error, a procedure checks the permissions further.
        if (!@mkdir($dir, WIKINDX_UNIX_PERMS_DEFAULT, TRUE))
        {
            echo "<div>Directory <strong>" . $dir . "</strong> has not been created. Check permissions.</div>\n";
        }
    }
}

// Create cache directories
foreach ([WIKINDX_DIR_CACHE, WIKINDX_DIR_CACHE_FILES, WIKINDX_DIR_CACHE_ATTACHMENTS, WIKINDX_DIR_CACHE_LANGUAGES, WIKINDX_DIR_CACHE_PLUGINS, WIKINDX_DIR_CACHE_STYLES, WIKINDX_DIR_CACHE_TEMPLATES] as $dir)
{
    $dir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, $dir]);
    if (!file_exists($dir))
    {
        // Continue without error, a procedure checks the permissions further.
        if (!@mkdir($dir, WIKINDX_UNIX_PERMS_DEFAULT, TRUE))
        {
            echo "<div>Directory <strong>" . $dir . "</strong> has not been created. Check permissions.</div>\n";
        }
    }
}

// Create data and cache directories of plugins
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "libs", "FILE.php"]));
foreach (\FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS])) as $dir)
{
    $plugencachedir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_PLUGINS, basename($dir)]);
    if (!file_exists($plugencachedir))
    {
        // Continue without error, a procedure checks the permissions further.
        if (!@mkdir($plugencachedir, WIKINDX_UNIX_PERMS_DEFAULT, TRUE))
        {
            echo "<div>Directory <strong>" . $plugencachedir . "</strong> has not been created. Check permissions.</div>\n";
        }
    }
    $plugendatadir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_PLUGINS, basename($dir)]);
    if (!file_exists($plugendatadir))
    {
        // Continue without error, a procedure checks the permissions further.
        if (!@mkdir($plugendatadir, WIKINDX_UNIX_PERMS_DEFAULT, TRUE))
        {
            echo "<div>Directory <strong>" . $plugendatadir . "</strong> has not been created. Check permissions.</div>\n";
        }
    }
}



// Check folders permissions
\UTILS\checkFoldersPerms();

// Create a cached components list
\UTILS\refreshComponentsListCache();

// Bufferize output
ob_start();

// Begin page execution timer and define globals for rendering by template
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "GLOBALS.php"]));

// Set up the FACTORY objects of commonly used classes and start the timer.
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "FACTORY.php"]));

// Initialize the static config read from config.php file
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "LOADSTATICCONFIG.php"]));

/**
 *	Initialize the system
 *  The static part of the config is loaded.
 */
FACTORY_LOADCONFIG::getInstance();

// Attempt an upgrade only if we are on the main script
if (mb_strripos(WIKINDX_DIR_COMPONENT_PLUGINS . DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_NAME']) === FALSE)
{
    include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "UPDATE.php"]));

    // Do database upgrade check
    if (\UPDATE\needUpdate(FACTORY_DB::getInstance()))
    {
        // Upgrade database
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "UPDATEDATABASE.php"]));
        $update = new UPDATEDATABASE(); // __construct() runs on autopilot
        // We should never reach this point because the uprgade process has its own display
        // and the only way to escape it is finishing all steps
        // and following the final link returning to the front page
        debug_print_backtrace();
        die("Fatal error: upgrade / install had not ended successfully");
    }
}

// Load auth object but diff. login after upgrade stage
// Upgrade will request login to superadmin if needed
$authorize = FACTORY_AUTHORIZE::getInstance();

/**
 *	Initialize the system
 *  The dynamic part of the config is loaded (db).
 */
FACTORY_LOADCONFIG::getInstance()->loadDBConfig();

FACTORY_LOADCONFIG::getInstance()->loadUserVars();

// Locales setting needs to know the language preferred by the user which is now in GLOBALS
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "libs", "LOCALES.php"]));
\LOCALES\load_locales();

$vars = GLOBALS::getVars();
