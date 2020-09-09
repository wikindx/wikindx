<?php
/*
ISC License

Copyright (c) 2019-2020, Stéphane Aulery, <lkppo@users.sourceforge.net>

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted, provided that the above
copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
*/

///////////////////////////////////////////////////////////////////////
/// Configuration
///////////////////////////////////////////////////////////////////////

include_once("core/startup/CONSTANTS.php");
include_once("core/libs/FILE.php");
include_once("core/libs/UTILS.php");

define('DIR_BUILD', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE, 'build']));
if (!file_exists(DIR_BUILD)) {
    mkdir(DIR_BUILD, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
}
if (!file_exists(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA]))) {
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

foreach ($componentlist as $k => $cmp) {
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
