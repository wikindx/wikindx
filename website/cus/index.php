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
 * Return a list of available components for a defined version of WIKINDX
 *
 * This script is very simple and just returns a list of components that match
 * components compatible versions of the core version requested.. It can act as
 * a gateway when there is a change in the definition of formats, file locations,
 * or compatibility issues.
 *
 * This script must be installed at https://wikindx.sourceforge.io/cus/index.php
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
$componentFolder = __DIR__ . DIRECTORY_SEPARATOR . "components";
$coreFolder = __DIR__ . DIRECTORY_SEPARATOR . "core";
$components_compatible_version_file = $coreFolder . DIRECTORY_SEPARATOR . $version . ".json";


// Check if the components directory exists
if (!file_exists($componentFolder) || !is_dir($componentFolder))
{
    echo emptyJsonString();
	exit(0);
}

// Check if the core directory exists
if (!file_exists($coreFolder) || !is_dir($coreFolder))
{
    echo emptyJsonString();
	exit(0);
}

// Check if a components compatible version file is available for the version requested
if (!file_exists($components_compatible_version_file))
{
    echo emptyJsonString();
	exit(0);
}

$components_compatible_version_file = json_decode(file_get_contents($components_compatible_version_file));
$componentsList = [];

foreach($components_compatible_version_file as $type => $cv)
{
    $dir = $componentFolder . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $cv;
    foreach(fileInDirToArray($dir) as $f)
    {
        if (substr($f, 0 - strlen(".json")) == ".json")
        {
            $cmp = json_decode(file_get_contents($dir . DIRECTORY_SEPARATOR . $f), TRUE);
            
            $matched = FALSE;
            foreach($componentsList as $k => $c)
            {
                // Keep the component with the highest version number
                if (
                    $cmp["component_id"] == $c["component_id"] &&
                    $cmp["component_type"] == $c["component_type"]
                ){
                    $matched = TRUE;
                    if (intval($cmp["component_version"]) > intval($c["component_version"])){
                        $componentsList[$k] = $cmp;
                        break;
                    }
                }
            }
            if (!$matched) {
                $componentsList[] = $cmp;
            }
        }
    }
}

// Return the prebuild list
echo json_encode($componentsList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

////
/// LIB
////////////////////////////////////////////////////////////////////////

function emptyJsonString()
{
    return json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Enumerate files of a directory except . and .. subdirectories
 *
 * @param string $dir A directory to explore
 *
 * @return array An array of filenames
 */
function fileInDirToArray($dir)
{
    $result = [];

    $cdir = dirToArray($dir);

    if (count($cdir) > 0) {
        foreach ($cdir as $v) {
            if (is_file($dir . DIRECTORY_SEPARATOR . $v)) {
                $result[] = $v;
            }
        }
    }

    unset($cdir);

    return $result;
}

/**
 * Enumerate files and subdirectories of a directory except . and .. subdirectories
 *
 * @param string $dir A directory to explore
 *
 * @return array An array of file and subdirectory names
 */
function dirToArray($dir)
{
    $result = [];

    if (file_exists($dir)) {
        $cdir = scandir($dir);

        if ($cdir !== FALSE) {
            foreach ($cdir as $v) {
                // Without hidden files
                if (!in_array($v, ['.', '..'])) {
                    $result[] = $v;
                }
            }
        }

        unset($cdir);
    }

    return $result;
}
