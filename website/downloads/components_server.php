<?php
/**
* Return a list of available components for a defined version of WIKINDX
*
* This script is very simple and just returns a precompiled list of components
* for the requested version. It can act as a gateway when there is a change
* in the definition of formats, file locations, or compatibility issues.
*
* This script must be installed at https://wikindx.sourceforge.io/downloads/componentListServer.php
*
* WIKINDX : Bibliographic Management system.
* @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
*
* @version 1
*
* @author The WIKINDX Team
* @copyright 2019 Stéphane Aulery <lkppo@users.sourceforge.net>
* @license https://www.isc.org/licenses/ ISC License
*/

// Config the JSON MIME/Type
header("Content-type: application/json; charset=UTF-8");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");

// Check type of method required
if (!array_key_exists("version", $_GET))
{
    echo emptyJsonString();
	exit(0);
}

$version = $_GET["version"];
$versionFolder = __DIR__ . DIRECTORY_SEPARATOR . $version;
$componentsListFile = $versionFolder . DIRECTORY_SEPARATOR . "components.json";

// Check if the version requested has a directory
if (!file_exists($versionFolder) || !is_dir($versionFolder))
{
    echo emptyJsonString();
	exit(0);
}

// Check if a components list is available for the version requested
if (!file_exists($componentsListFile))
{
    echo emptyJsonString();
	exit(0);
}

// Return the prebuild list
echo file_get_contents($componentsListFile);

////
/// LIB
////////////////////////////////////////////////////////////////////////

function emptyJsonString()
{
    return json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
