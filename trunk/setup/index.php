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
 * Setup script
 *
 * This script helps the administrator to install or upgrade the core.
 *
 * @package wikindx
 */
 

/// GENERAL CONFIGURATION
///////////////////////////////////////////////////////////////////////////////

// Import tools
include_once(__DIR__ . "/../core/startup/CONSTANTS.php");
include_once(__DIR__ . "/../core/startup/CONSTANTS_CONFIG_DEFAULT.php");
include_once(__DIR__ . "/../core/utils/UTILS.php");
include_once(__DIR__ . "/../core/startup/GLOBALS.php");
include_once(__DIR__ . "/../core/startup/FACTORY.php");
include_once(__DIR__ . "/../core/sql/SQL.php");
include_once(__DIR__ . "/../core/urls/URL.php");
include_once(__DIR__ . "/../core/utf8/UTF8.php");
include_once(__DIR__ . "/setup.php");
include_once(__DIR__ . "/setup-steps.php");

// Debug config
error_reporting(E_ALL);
ini_set('display_startup_errors', TRUE);
ini_set('html_errors', TRUE);
ini_set('display_errors', TRUE);


// Default charset
ini_set('default_charset', WIKINDX_CHARSET);
ini_set('mbstring.encoding_translation', WIKINDX_CHARSET);
ini_set('mbstring.detect_order', WIKINDX_CHARSET);

// HTTP charset (HTTP specification doesn't permit to declare Content-type separetly)
header('Content-type: ' . WIKINDX_HTTP_CONTENT_TYPE_DEFAULT . '; charset=' . WIKINDX_CHARSET);

// Protect from a session already launched by an other page but not well loaded (plugins)
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
if (session_status() === PHP_SESSION_NONE) {
    // start session
    session_start();
}

// Check PHP execution environnement (CLI isn't supported)
if (PHP_SAPI === 'cli') {
    die("WIKINDX doesn't support CLI execution.");
}


/// SETUP/UPGARDE CONFIGURATION
///////////////////////////////////////////////////////////////////////////////

unset($_SESSION["setup-in-progress"]);

// If the session variable setup-in-progress is not set,
// we determine the steps to perform before defining and entering the installation process
if (!array_key_exists("setup-in-progress", $_SESSION))
{
    $_SESSION["setup-steps"] = [];

    // Test if an install or an upgrade is needed
    if (\SETUP\needInstall()) {
        $_SESSION["setup-steps"][] = "step_install_start";
        $_SESSION["setup-steps"][] = "step_php_min_version";
        $_SESSION["setup-steps"][] = "step_php_max_version";
        $_SESSION["setup-steps"][] = "step_php_mandatory_extensions";
        $_SESSION["setup-steps"][] = "step_install_config";
        $_SESSION["setup-steps"][] = "step_validate_config";
        $_SESSION["setup-steps"][] = "step_validate_db_connection";
        $_SESSION["setup-steps"][] = "step_db_min_version";
        $_SESSION["setup-steps"][] = "step_create_database";
        $_SESSION["setup-steps"][] = "step_create_superadmin";
        $_SESSION["setup-steps"][] = "step_install_end";
    } elseif(\SETUP\needUpgrade()) {
        $_SESSION["setup-steps"][] = "step_upgrade_start";
        $_SESSION["setup-steps"][] = "step_login_superadmin";
        if (!\SETUP\isConfigUptodate()) {
            $_SESSION["setup-steps"][] = "step_upgrade_config";
        }
        $dbo = new \SQL();
        if (!\SETUP\isDatabaseVersionUptodate($dbo)) {
            //$_SESSION["setup-steps"][] = "step_upgrade_config";
        }
        $_SESSION["setup-steps"][] = "step_upgrade_end";
    } else {
        die("ERROR");
    }
    
    // Do database upgrade check
    /*if (\SETUP\needUpdate(FACTORY_DB::getInstance())) {
        // Upgrade database
        include_once("core/startup/UPDATEDATABASE.php");
        $update = new UPDATEDATABASE(); // __construct() runs on autopilot
        $upgradeCompleted = $update->upgradeCompleted;
        unset($update);
    }*/
    
    if (count($_SESSION["setup-steps"]) == 0) {
        $_SESSION["setup-steps"][] = "step_install_end";
    }
    
    // Key of the first step
    $_SESSION["setup-current_step"] = 0;
    
    $_SESSION["setup-in-progress"] = TRUE;
    $_SESSION["setup-title"] = WIKINDX_TITLE_DEFAULT . " Setup";
    $_SESSION["setup-nav"] = "Step " . ($_SESSION["setup-current_step"] + 1) . "/" . count($_SESSION["setup-steps"]);
}


/// SETUP/UPGARDE EXECUTION
///////////////////////////////////////////////////////////////////////////////

// Execute the current step
// and display it's output
$screen = "";

if (
    array_key_exists("setup-steps", $_SESSION) &&
    array_key_exists("setup-current_step", $_SESSION)
) {
    $step_function = $_SESSION["setup-steps"][$_SESSION["setup-current_step"]];
    $screen = call_user_func("\SETUP\\STEPS\\" . $step_function);
}


/// DISPLAY
///////////////////////////////////////////////////////////////////////////////

include_once(__DIR__ . "/header.php");

// Print the screen of the current step
echo "\n";
echo $screen;
echo "\n";

// Debug output at the end of the page
echo "<pre>\n";
echo "\$_SERVER:\n";
//echo print_r($_SERVER, TRUE);
echo "\n";
echo "\n";

echo "\$_SESSION:\n";
echo print_r($_SESSION, TRUE);

echo "</pre>\n";

include_once(__DIR__ . "/footer.php");

