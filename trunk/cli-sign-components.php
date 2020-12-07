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
 * cli-sign-components.php
 *
 * Script to sign and fix the version of components.
 *
 * This script must be systematically launched before tagging a release,
 * but after the last modification of a component because it is the change
 * of signature which controls the update of the packages.
 *
 * The signature is a hash stored in the component.json file
 * that uniquely identifies the final code for each component.
 *
 * The version number is used to publish several versions of the same component
 * for compatible cores. The update server is responsible for publishing only
 * the latest available version of each combination of core and component.
 * To avoid downgrades and errors, the version number is calculated in days
 * elapsed since 2020-01-12 (start day of the component update server).
 *
 * Limits: it is possible to publish twice on the same day (publicly on the update server)
 * but this is not recommended because users who have already downloaded the list
 * from the server will see the hash changed for the same version.
 * This prevents updating until they update their list again.
 *
 * @package wikindx\release\components
 */
 
///////////////////////////////////////////////////////////////////////
/// Configuration
///////////////////////////////////////////////////////////////////////

include_once("core/startup/CONSTANTS.php");
include_once("core/libs/FILE.php");
include_once("core/libs/UTILS.php");

define('DIR_BUILD', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE, 'build']));
if (!file_exists(DIR_BUILD))
{
    mkdir(DIR_BUILD, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
}
if (!file_exists(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA])))
{
    mkdir(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA]), WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
}

$component_types = [
    "plugin" => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS]),
    "style" => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES]),
    "template" => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES]),
    "vendor" => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR]),
];

///////////////////////////////////////////////////////////////////////
/// Signing
///////////////////////////////////////////////////////////////////////


echo "\n";
echo "Components signature script\n";
echo "\n";
$componentlist = \UTILS\readComponentsList(TRUE);

foreach ($componentlist as $k => $cmp)
{
    $dircmpsrc = $component_types[$cmp["component_type"]] . DIRECTORY_SEPARATOR . $cmp["component_id"];
    $dircmpdst = DIR_BUILD . DIRECTORY_SEPARATOR . $cmp["component_type"] . "_" . $cmp["component_id"];
    $pkgcmp = DIR_BUILD . DIRECTORY_SEPARATOR . $cmp["component_id"] . ".zip";
    
    echo $dircmpsrc . "\n";
    
    // Make a separate copy of the component where the hash is removed from the component.json file,
    // otherwise the calculated hash is different at each execution although the comporent has not changed.
    echo " - copy\n";
    \FILE\recurse_dir_copy($dircmpsrc, $dircmpdst);
    
    $pkgmetadata = $dircmpdst . DIRECTORY_SEPARATOR . "component.json";
    $componentMetadata = \FILE\read_json_file($pkgmetadata);
    
    $old_version = $componentMetadata["component_version"] ?? "";
    $old_hash = $componentMetadata["component_" . WIKINDX_PACKAGE_HASH_ALGO] ?? "";
    
    unset($componentMetadata["component_" . WIKINDX_PACKAGE_HASH_ALGO]);
    unset($componentMetadata["component_integrity"]);
    unset($componentMetadata["component_status"]);
    unset($componentMetadata["component_version"]);
    
    \FILE\write_json_file($pkgmetadata, $componentMetadata);
    
    // Hashing
    $new_hash = \UTILS\hash_path($dircmpdst);
    echo " - hash: " . $new_hash . "\n";
    
    // Signature
    $componentMetadata = $cmp;
    unset($componentMetadata["component_integrity"]);
    unset($componentMetadata["component_status"]);
    
    $componentMetadata["component_" . WIKINDX_PACKAGE_HASH_ALGO] = $new_hash;
    
    if ($new_hash != $old_hash || $old_hash == "" || $old_version == "")
    {
        // The version number is the number of days elapsed since the launch of the system of components (v6 on 2020-01-12)
        $datetime1 = new DateTime("2020-01-12");
        $datetime2 = new DateTime("");
        $interval = $datetime1->diff($datetime2);
        $componentMetadata["component_version"] = $interval->format('%a');
    }
    else
    {
        $componentMetadata["component_version"] = $old_version;
    }
    \FILE\write_json_file($dircmpsrc . DIRECTORY_SEPARATOR . "component.json", $componentMetadata);
    
    echo " - signing [OK]\n";
    
    echo "\n";
}


// Cleaning the build directory
\FILE\recurse_rmdir(DIR_BUILD);
