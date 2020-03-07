<?php
/*
Copyright Stéphane Aulery, 2019-2020

<lkppo@users.sourceforge.net>

This software is a computer program whose purpose is to sign wikindx's
components.

This software is governed by the CeCILL-C license under French law and
abiding by the rules of distribution of free software.  You can  use,
modify and/ or redistribute the software under the terms of the CeCILL-C
license as circulated by CEA, CNRS and INRIA at the following URL
"http://www.cecill.info".

As a counterpart to the access to the source code and  rights to copy,
modify and redistribute granted by the license, users are provided only
with a limited warranty  and the software's author,  the holder of the
economic rights,  and the successive licensors  have only  limited
liability.

In this respect, the user's attention is drawn to the risks associated
with loading,  using,  modifying and/or developing or reproducing the
software by the user in light of its specific status of free software,
that may mean  that it is complicated to manipulate,  and  that  also
therefore means  that it is reserved for developers  and  experienced
professionals having in-depth computer knowledge. Users are therefore
encouraged to load and test the software's suitability as regards their
requirements in conditions enabling the security of their systems and/or
data to be ensured and,  more generally, to use and operate it in the
same conditions as regards security.

The fact that you are presently reading this means that you have had
knowledge of the CeCILL-C license and that you accept its terms.
*/

///////////////////////////////////////////////////////////////////////
/// Configuration
///////////////////////////////////////////////////////////////////////

include_once("core/startup/CONSTANTS.php");
include_once("core/file/FILE.php");
include_once("core/utils/UTILS.php");

define('DIR_BUILD', __DIR__ . DIRECTORY_SEPARATOR . WIKINDX_DIR_CACHE . DIRECTORY_SEPARATOR . 'build');
if (!file_exists(DIR_BUILD)) {
    mkdir(DIR_BUILD, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
}
if (!file_exists(WIKINDX_DIR_DATA)) {
    mkdir(WIKINDX_DIR_DATA, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
}

$component_types = [
    "language" => __DIR__ . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_LANGUAGES,
    "plugin" => __DIR__ . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_PLUGINS,
    "style" => __DIR__ . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_STYLES,
    "template" => __DIR__ . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_TEMPLATES,
    "vendor" => __DIR__ . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_VENDOR,
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
    unset($componentMetadata["component_" . WIKINDX_PACKAGE_HASH_ALGO]);
    unset($componentMetadata["component_integrity"]);
    unset($componentMetadata["component_status"]);
    \FILE\write_json_file($pkgmetadata, $componentMetadata);
    
    // Hashing
    $hashcmp = \UTILS\hash_path($dircmpdst);
    echo " - hash: " . $hashcmp . "\n";
    
    // Signature
    $componentMetadata = $cmp;
    $componentMetadata["component_" . WIKINDX_PACKAGE_HASH_ALGO] = $hashcmp;
    unset($componentMetadata["component_integrity"]);
    unset($componentMetadata["component_status"]);
    \FILE\write_json_file($dircmpsrc . DIRECTORY_SEPARATOR . "component.json", $componentMetadata);
    
    echo " - signing [OK]\n";
    
    echo "\n";
}


// Cleaning the build directory
\FILE\recurse_rmdir(DIR_BUILD);
