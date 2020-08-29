<?php
/*
ISC License

Copyright (c) 2018-2020, Stéphane Aulery <lkppo@users.sourceforge.net>
Copyright (c) 2018, Mark Grimshaw-Aagaard <sirfragalot@users.sourceforge.net>

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

$Start = microtime();

///////////////////////////////////////////////////////////////////////
/// Configuration (static)
///////////////////////////////////////////////////////////////////////

define('MAX_LINE_LENGHT', 80);
define('DATE_RELEASE', date('YmdHis'));
define('APP_NAME', 'wikindx');
define('DIR_ROOT', __DIR__ . DIRECTORY_SEPARATOR . "..");
define('DIRPKG_ROOT', __DIR__);
define('SF_RELEASE_SERVER', "https://sourceforge.net/projects/wikindx/files/archives");

define('DIRSRC_ROOT', DIR_ROOT);
define('DIRSRC_TAGS', DIRSRC_ROOT . DIRECTORY_SEPARATOR . 'tags');
define('DIRSRC_TRUNK', DIRSRC_ROOT . DIRECTORY_SEPARATOR . 'trunk');

define('BIN_PHPDOC', DIR_ROOT . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phpDocumentor.phar');

include_once(DIRSRC_TRUNK . "/core/startup/CONSTANTS.php");
include_once(DIRSRC_TRUNK . "/core/libs/FILE.php");
include_once(DIRSRC_TRUNK . "/core/libs/UTILS.php");

$VersionsAvailable[] = WIKINDX_PUBLIC_VERSION;
$VersionsAvailable[] = 'trunk';



///////////////////////////////////////////////////////////////////////
/// Configuration (dynamic)
///////////////////////////////////////////////////////////////////////

echo "---[Wikindx Packaging]---------------------------------------------------------\n\n";

$VersionPackaged = promptListUser("Which version do you want to pack?", $VersionsAvailable, "trunk");
$VersionPackaged = mb_strtolower($VersionPackaged);
echo "Version selected: $VersionPackaged\n";

$ManualRebuildingFlag = promptListUser("Rebuild the manual?", ["N", "Y"], "Y");
$ManualRebuildingFlag = mb_strtoupper($ManualRebuildingFlag);
echo "Manual rebuilding: $ManualRebuildingFlag\n";
$ManualRebuildingFlag = ($ManualRebuildingFlag == "Y");


switch ($VersionPackaged)
{
    case 'trunk' :
        define('DIR_SRC', DIRSRC_TRUNK);
        define('DIR_DST', DIRPKG_ROOT . DIRECTORY_SEPARATOR . $VersionPackaged);
        define('DIR_DST_SRC', DIR_DST . DIRECTORY_SEPARATOR . 'source');
        define('DIR_DST_PKG', DIR_DST . DIRECTORY_SEPARATOR . 'files');
        define('DIR_DST_COR', DIR_DST_PKG . DIRECTORY_SEPARATOR . $VersionPackaged);
        define('DIR_DST_COR_ARC', DIR_DST_PKG . DIRECTORY_SEPARATOR . 'archives' . DIRECTORY_SEPARATOR . $VersionPackaged);
        define('DIR_DST_CMP', DIR_DST_PKG . DIRECTORY_SEPARATOR . $VersionPackaged . DIRECTORY_SEPARATOR . 'components');
        define('DIR_DST_CMP_ARC', [
            'plugin' => DIR_DST_PKG . DIRECTORY_SEPARATOR . 'archives' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR . 'trunk',
            'style' => DIR_DST_PKG . DIRECTORY_SEPARATOR . 'archives' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR . 'trunk',
            'template' => DIR_DST_PKG . DIRECTORY_SEPARATOR . 'archives' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'trunk',
            'vendor' => DIR_DST_PKG . DIRECTORY_SEPARATOR . 'archives' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'trunk',
        ]);
        define('DIR_DST_COR_CUS',  DIR_DST . DIRECTORY_SEPARATOR . 'cus' . DIRECTORY_SEPARATOR . 'core');
        define('DIR_DST_CMP_CUS', [
            'plugin' => DIR_DST . DIRECTORY_SEPARATOR . 'cus' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR . 'trunk',
            'style' => DIR_DST . DIRECTORY_SEPARATOR . 'cus' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR . 'trunk',
            'template' => DIR_DST . DIRECTORY_SEPARATOR . 'cus' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'trunk',
            'vendor' => DIR_DST . DIRECTORY_SEPARATOR . 'cus' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'trunk',
        ]);
        define('DIR_URL_CMP', [
            'plugin' => SF_RELEASE_SERVER . "/components/plugin/trunk",
            'style' => SF_RELEASE_SERVER . "/components/style/trunk",
            'template' => SF_RELEASE_SERVER . "/components/template/trunk",
            'vendor' => SF_RELEASE_SERVER . "/components/vendor/trunk",
        ]);
    break;
    default:
        define('DIR_SRC', DIRSRC_TRUNK);
        define('DIR_DST', DIRPKG_ROOT . DIRECTORY_SEPARATOR . $VersionPackaged);
        define('DIR_DST_SRC', DIR_DST . DIRECTORY_SEPARATOR . 'source');
        define('DIR_DST_PKG', DIR_DST . DIRECTORY_SEPARATOR . 'files');
        define('DIR_DST_COR', DIR_DST_PKG . DIRECTORY_SEPARATOR . $VersionPackaged);
        define('DIR_DST_COR_ARC', DIR_DST_PKG . DIRECTORY_SEPARATOR . 'archives' . DIRECTORY_SEPARATOR . $VersionPackaged);
        define('DIR_DST_CMP', DIR_DST_PKG . DIRECTORY_SEPARATOR . $VersionPackaged . DIRECTORY_SEPARATOR . 'components');
        define('DIR_DST_CMP_ARC', [
            'plugin' => DIR_DST_PKG . DIRECTORY_SEPARATOR . 'archives' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['plugin'],
            'style' => DIR_DST_PKG . DIRECTORY_SEPARATOR . 'archives' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['style'],
            'template' => DIR_DST_PKG . DIRECTORY_SEPARATOR . 'archives' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['template'],
            'vendor' => DIR_DST_PKG . DIRECTORY_SEPARATOR . 'archives' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['vendor'],
        ]);
        define('DIR_DST_COR_CUS',  DIR_DST . DIRECTORY_SEPARATOR . 'cus' . DIRECTORY_SEPARATOR . 'core');
        define('DIR_DST_CMP_CUS', [
            'plugin' => DIR_DST . DIRECTORY_SEPARATOR . 'cus' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['plugin'],
            'style' => DIR_DST . DIRECTORY_SEPARATOR . 'cus' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['style'],
            'template' => DIR_DST . DIRECTORY_SEPARATOR . 'cus' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['template'],
            'vendor' => DIR_DST . DIRECTORY_SEPARATOR . 'cus' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['vendor'],
        ]);
        define('DIR_URL_CMP', [
            'plugin' => SF_RELEASE_SERVER . "/components/plugin/" . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['plugin'],
            'style' => SF_RELEASE_SERVER . "/components/style/" . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['style'],
            'template' => SF_RELEASE_SERVER . "/components/template/" . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['template'],
            'vendor' => SF_RELEASE_SERVER . "/components/vendor/" . WIKINDX_COMPONENTS_COMPATIBLE_VERSION['vendor'],
        ]);
    break;
}

define("APP_PKG_PREFIX", APP_NAME . "_" . $VersionPackaged);



///////////////////////////////////////////////////////////////////////
/// Retrieve the source code
///////////////////////////////////////////////////////////////////////
if (!file_exists(DIRPKG_ROOT))
{
    echo "MKDIR " . DIRPKG_ROOT . "\n";
    mkdir(DIRPKG_ROOT);
    echo "\n";
}

if (file_exists(DIR_DST))
{
    echo "Clean previous package\n";
    \FILE\recurse_rmdir(DIR_DST);
}

echo "MKDIR " . DIR_DST . "\n";
mkdir(DIR_DST);
echo "\n";
echo "MKDIR " . DIR_DST_PKG . "\n";
mkdir(DIR_DST_PKG);
echo "\n";
echo "MKDIR " . DIR_DST_COR . "\n";
mkdir(DIR_DST_COR, 0777, TRUE);
echo "\n";
echo "MKDIR " . DIR_DST_COR_ARC . "\n";
mkdir(DIR_DST_COR_ARC, 0777, TRUE);
echo "\n";
echo "MKDIR " . DIR_DST_CMP . "\n";
mkdir(DIR_DST_CMP, 0777, TRUE);
echo "\n";
foreach (DIR_DST_CMP_ARC as $type => $path)
{
    echo "MKDIR " . $path . "\n";
    mkdir($path, 0777, TRUE);
    echo "\n";
}
echo "MKDIR " . DIR_DST_COR_CUS . "\n";
mkdir(DIR_DST_COR_CUS, 0777, TRUE);
echo "\n";
foreach (DIR_DST_CMP_CUS as $type => $path)
{
    echo "MKDIR " . $path . "\n";
    mkdir($path, 0777, TRUE);
    echo "\n";
}

echo "Replicate source code of $VersionPackaged\n";
\FILE\recurse_dir_copy(DIR_SRC, DIR_DST_SRC);


$signatures = "algo;hash;file\n";

///////////////////////////////////////////////////////////////////////
/// Build the phpdoc manual
///////////////////////////////////////////////////////////////////////
echo "\n";
echo "Build manual package\n";
$pkg = APP_PKG_PREFIX . '_api_manual';
echo "Package " . $pkg . "\n";

// Create a fake file
$FakeContent  = "Fake file to prevent the make process to hang";
$FakeContent .= "if the manual is not built for any reason.";
file_put_contents(DIR_DST_SRC . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'manual' . DIRECTORY_SEPARATOR . 'fake.txt', $FakeContent);

if ($ManualRebuildingFlag) build_manual(DIR_DST_SRC, 'WIKINDX Documentation ' . $VersionPackaged);

foreach (["BZIP2", "GZ", "ZIP"] as $archformat)
{                
    $pkgarch = \FILE\createComponentPackage(DIR_DST_SRC . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'manual', DIR_DST_COR_ARC, $pkg, $archformat);
    copy($pkgarch, DIR_DST_COR . DIRECTORY_SEPARATOR . basename($pkgarch));
    echo " - $archformat arch: " . $pkgarch . "\n";
    
    $pkghash = \UTILS\hash_path($pkgarch, WIKINDX_PACKAGE_HASH_ALGO);
    echo " - $archformat hash: " . $pkghash . "\n";
    
    $signatures .= WIKINDX_PACKAGE_HASH_ALGO . ";" . $pkghash . ";" . basename($pkgarch) . "\n";
}

// Remove the manual from sources after packaging it
\FILE\recurse_rmdir(DIR_DST_SRC . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "manual");



///////////////////////////////////////////////////////////////////////
/// Clear the code
///////////////////////////////////////////////////////////////////////
echo "\n";
echo "Clear source code\n";
// Security if the script is not called against a fresh repository

// Delete temporary directories
\FILE\recurse_rmdir(DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_CACHE);
\FILE\recurse_rmdir(DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_DATA);
\FILE\recurse_rmdir(DIR_DST_SRC . DIRECTORY_SEPARATOR . 'session');
\FILE\recurse_rmdir(DIR_DST_SRC . DIRECTORY_SEPARATOR . 'sessionData');

// Delete temporary files
\FILE\rmfile(DIR_DST_SRC . DIRECTORY_SEPARATOR . 'config copy.php');
\FILE\rmfile(DIR_DST_SRC . DIRECTORY_SEPARATOR . 'config.php');


// Create placeholder files in empty (cache or not) directories to prevent TAR from deleting them
foreach(\FILE\recurse_fileInDirToArray(DIR_DST_SRC) as $d)
{
    if (\UTILS\matchSuffix($d, DIRECTORY_SEPARATOR) && $d != DIRECTORY_SEPARATOR)
    {
        $d = mb_substr($d, 0, mb_strlen($d) - mb_strlen(DIRECTORY_SEPARATOR));
        $placeholderFile = mb_strtoupper(basename($d));
        
        // Pick an existing placeholder file or create one
        if (
            file_exists(DIR_SRC . DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR . $placeholderFile)
            && is_file(DIR_SRC . DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR . $placeholderFile)
        )
        {
            copy(
                DIR_SRC . DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR . $placeholderFile,
                DIR_DST_SRC . DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR . $placeholderFile
            );
        }
        else
        {
            file_put_contents(
                DIR_DST_SRC . DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR . $placeholderFile,
                "This file prevents TAR from deleting the empty directory that contains it during the build process. It can be deleted safely."
            );
        }
    }
}



///////////////////////////////////////////////////////////////////////
/// Build packages
///////////////////////////////////////////////////////////////////////
echo "\n";
echo "Build component packages\n";
$componentlist = [];

$componentPath = [
    DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_PLUGINS => \FILE\dirInDirToArray(DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_PLUGINS),
    DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_STYLES => \FILE\dirInDirToArray(DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_STYLES),
    DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_TEMPLATES => \FILE\dirInDirToArray(DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_TEMPLATES),
    DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_VENDOR => \FILE\dirInDirToArray(DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_VENDOR),
];

$readmecmp  = str_pad("--o " . mb_strtoupper("WIKINDX " . $VersionPackaged . " COMPONENTS") . " o--", MAX_LINE_LENGHT, " ", STR_PAD_BOTH) . "\n";
$readmecmp .= "\n";
$readmecmp .= str_pad("---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---", MAX_LINE_LENGHT, " ", STR_PAD_BOTH) . "\n";
$readmecmp .= "\n";
$readmecmp .= str_pad("List of components available for Wikindx", MAX_LINE_LENGHT, " ", STR_PAD_BOTH) . "\n";
$readmecmp .= "\n";

foreach ($componentPath as $rootpath => $paths)
{
    foreach ($paths as $path)
    {
        $componentDir = $rootpath . DIRECTORY_SEPARATOR . basename($path);
        $componentConfig = \FILE\read_json_file($componentDir . DIRECTORY_SEPARATOR . 'component.json');
        if ($componentConfig === NULL)
        {
            echo "Parsing the " . $componentDir . DIRECTORY_SEPARATOR . "component.json file returned an error.\n";
            echo "As long as this error will not be corrected, this component will not be packaged.\n";
            continue;
        }
        asort($componentConfig);
        
        if (
            array_key_exists("component_updatable", $componentConfig)
            && array_key_exists("component_builtin", $componentConfig)
            && array_key_exists("component_type", $componentConfig)
            && array_key_exists("component_id", $componentConfig)
        )
        {
            if ($componentConfig["component_updatable"] == "true")
            {
                $pkg = APP_NAME . "_" . $componentConfig["component_type"] . "_" . $componentConfig["component_id"] . "_" . $componentConfig["component_version"];
                echo "Package " . $pkg . "\n";
                
                $readmecmp .= str_repeat("-", MAX_LINE_LENGHT) . "\n";
                $readmecmp .= formatParagraph($componentConfig["component_description"], MAX_LINE_LENGHT - 1, 1) . "\n";
                $readmecmp .= "\n";
                $readmecmp .= " Name: " . $componentConfig["component_name"] . "\n";
                $readmecmp .= " Type: " . $componentConfig["component_type"] . "\n";
                $readmecmp .= " Version: " . $componentConfig["component_version"] . "\n";
                $readmecmp .= " Licence: " . $componentConfig["component_licence"] . "\n";
                if (array_key_exists("component_website", $componentConfig))
                {
                	$readmecmp .= " Website: " . $componentConfig["component_website"] . "\n";
                }
                
                if (array_key_exists("component_authors", $componentConfig))
                {
                	$readmecmp .= " Authors:\n";
                	foreach($componentConfig["component_authors"] as $author)
                	{
                		$line = "";
                		
	                    if (array_key_exists("author_name", $author))
	                    {
	                    	$line .= $author["author_name"];
	                    }
	                    if (array_key_exists("author_role", $author))
	                    {
	                    	$line .= " (" . $author["author_role"] . ")";
	                    }
	                    if (array_key_exists("author_copyright", $author))
	                    {
	                    	$line .= ", " . $author["author_copyright"];
	                    }
                		
                		$readmecmp .= "    - " . $line . "\n";
                	}
                }
                
                $readmecmp .= "\n";
                $readmecmp .= " Hash: " . $componentConfig["component_" . WIKINDX_PACKAGE_HASH_ALGO] . " (" . WIKINDX_PACKAGE_HASH_ALGO . ")\n";
                
                $readmecmp .= "\n";
                $readmecmp .= " Packages:\n";
                
                $PkgList = [];
                //foreach (["ZIP"] as $archformat)
                foreach (["BZIP2", "GZ", "ZIP"] as $archformat)
                {                
                    $pkgarch = \FILE\createComponentPackage($componentDir, DIR_DST_CMP_ARC[$componentConfig["component_type"]], $pkg, $archformat);
                    copy($pkgarch, DIR_DST_CMP . DIRECTORY_SEPARATOR . basename($pkgarch));
                    echo " - $archformat arch: " . $pkgarch . "\n";
                    
                    $pkghash = \UTILS\hash_path($pkgarch, WIKINDX_PACKAGE_HASH_ALGO);
                    echo " - $archformat hash: " . $pkghash . "\n";
    				
					//$signatures .= WIKINDX_PACKAGE_HASH_ALGO . ";" . $pkghash . ";" . basename($pkgarch) . "\n";
                    
                    // The update server use only the ZIP format because the decompression of .tar.gz and .tar.bz2 is broken on macOS
                    // BZIP2 and GZ are kept for manual update
                    if ($archformat == "ZIP")
                    {
	                    $PkgList[] = [
	                    	"package_location" => DIR_URL_CMP[$componentConfig["component_type"]] . "/" . basename($pkgarch),
	                    	"package_" . WIKINDX_PACKAGE_HASH_ALGO => $pkghash, "package_size" => filesize($pkgarch)
	                    ];
                    }
                    
                    $readmecmp .= "    - " . basename($pkgarch) . "\n";
                    $readmecmp .= "      " . $pkghash . " (" . WIKINDX_PACKAGE_HASH_ALGO . ")\n";
                }
                
                $componentConfig["component_packages"] = $PkgList;
                $componentlist[] = $componentConfig;
                
                // Create the config file of the component for the update server
                echo " - " . DIR_DST_CMP_CUS[$componentConfig["component_type"]] . DIRECTORY_SEPARATOR . $pkg . ".json" . "\n";
                file_put_contents(
                    DIR_DST_CMP_CUS[$componentConfig["component_type"]] . DIRECTORY_SEPARATOR . $pkg . ".json",
                    json_encode($componentConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                );
            }
            // Clear files for the core build that follows
            if ($componentConfig["component_builtin"] == "false")
            {
                \FILE\recurse_rmdir($componentDir);
            }
        }
        else
        {
        	echo "\n";
        	echo "ERROR - a component.json file is damaged or incomplete:";
        	echo print_r($componentConfig);
        	echo "\n";
        }
    }
}

$readmecmp .= "\n";
$readmecmp .= "--\n";
$readmecmp .= "The WIKINDX Team " . date("Y") . "\n";
$readmecmp .= "sirfragalot@users.sourceforge.net\n";

echo "Component README.txt\n";
file_put_contents(DIR_DST_CMP . DIRECTORY_SEPARATOR . "README.txt", $readmecmp);


echo "\n";
echo "Save a list of component compatible version of the core for the update server\n";
if ($VersionPackaged == "trunk")
    file_put_contents(DIR_DST_COR_CUS . DIRECTORY_SEPARATOR . $VersionPackaged . ".json", json_encode(["plugin" => "trunk","style" => "trunk","template" => "trunk","vendor" => "trunk",], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
else
    file_put_contents(DIR_DST_COR_CUS . DIRECTORY_SEPARATOR . $VersionPackaged . ".json", json_encode(WIKINDX_COMPONENTS_COMPATIBLE_VERSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));


echo "\n";
echo "Build core package\n";
$pkg = APP_PKG_PREFIX;
echo "Package " . $pkg . "\n";

foreach (["BZIP2", "GZ", "ZIP"] as $archformat)
{                
    $pkgarch = \FILE\createComponentPackage(DIR_DST_SRC, DIR_DST_COR_ARC, $pkg, $archformat);
    copy($pkgarch, DIR_DST_COR . DIRECTORY_SEPARATOR . basename($pkgarch));
    echo " - $archformat arch: " . $pkgarch . "\n";
    
    $pkghash = \UTILS\hash_path($pkgarch, WIKINDX_PACKAGE_HASH_ALGO);
    echo " - $archformat hash: " . $pkghash . "\n";
    				
	$signatures .= WIKINDX_PACKAGE_HASH_ALGO . ";" . $pkghash . ";" . basename($pkgarch) . "\n";
}


// GENERAL README
echo "General README.txt\n";
$readmeglobal  = str_pad("--o " . mb_strtoupper("Wikindx " . $VersionPackaged . " installation files") . " o--", MAX_LINE_LENGHT, " ", STR_PAD_BOTH) . "\n";
$readmeglobal .= "\n";
$readmeglobal .= str_pad("---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---", MAX_LINE_LENGHT, " ", STR_PAD_BOTH) . "\n";
$readmeglobal .= "\n";
if ($VersionPackaged == "trunk")
{
	$readmeglobal .= str_pad("THIS VERSION IS ONLY INTENDED FOR DEVELOPERS AND TESTERS.", MAX_LINE_LENGHT, " ", STR_PAD_BOTH) . "\n";
	$readmeglobal .= "\n";
}
$readmeglobal .= formatParagraph("For a new installation or an update of the core, download the main package, uncompress it and follow the instructions in the documentation. A good part of the process is automatic but some manual actions specific to a version are often necessary (Internet not required).", MAX_LINE_LENGHT, 0) . "\n";
$readmeglobal .= "\n";
$readmeglobal .= formatParagraph("\"components\" folder contains the component packages available for this version of the core. They can be installed or updated from the administration of the components of your Wikindx installation (Internet required, does not work with a proxy).", MAX_LINE_LENGHT, 0) . "\n";
$readmeglobal .= "\n";
$readmeglobal .= formatParagraph("For an offline installation of components, you must download the desired component packages from a station that has internet access, and install individually from the same administration screen. It is therefore recommended to recopy the hash of the downloaded file to check its integrity.", MAX_LINE_LENGHT, 0) . "\n";
$readmeglobal .= "\n";
$readmeglobal .= formatParagraph("An additional package contains documentation of internal APIs that are of interest to core and plugin developers.", MAX_LINE_LENGHT, 0) . "\n";
$readmeglobal .= "\n";
$readmeglobal .= "--\n";
$readmeglobal .= "The WIKINDX Team " . date("Y") . "\n";
$readmeglobal .= "sirfragalot@users.sourceforge.net\n";

file_put_contents(DIR_DST_COR_ARC . DIRECTORY_SEPARATOR . "README.txt", $readmeglobal);
file_put_contents(DIR_DST_COR . DIRECTORY_SEPARATOR . "README.txt", $readmeglobal);

echo "Signatures file\n";
file_put_contents(DIR_DST_COR_ARC . DIRECTORY_SEPARATOR . APP_NAME . "_" . $VersionPackaged . "_signatures.txt", $signatures);
file_put_contents(DIR_DST_COR . DIRECTORY_SEPARATOR . APP_NAME . "_" . $VersionPackaged . "_signatures.txt", $signatures);


///////////////////////////////////////////////////////////////////////
/// END OF THE RELEASE PROCESS
///////////////////////////////////////////////////////////////////////

// Display stats
$End = microtime();

$tmp = explode(' ', $Start);
$beginTimer = $tmp[0] + $tmp[1];

$tmp = explode(' ', $End);
$endTimer = $tmp[0] + $tmp[1];

echo "\n";
echo "Time elapsed: " . sprintf('%0.5f', round($endTimer - $beginTimer, 5)) . " s\n";



///////////////////////////////////////////////////////////////////////
/// Library
///////////////////////////////////////////////////////////////////////

function promptListUser($promptStr, $AvailableValues, $defaultVal = NULL)
{
    // PRINT => Do you like snails? [default=Y]:
    $promptMsg = "";
    $promptMsg .= $promptStr;
    if($defaultVal) $promptMsg .= ' [' . implode(', ', $AvailableValues) . ';default='. $defaultVal. ']';
    $promptMsg .= ": ";
    
    $AvailableValues = array_map("mb_strtoupper", $AvailableValues);
    
    // Read input and remove CRLF
    do
    {
        echo $promptMsg;
        $CapturedValue = mb_strtoupper(trim(fgets(STDIN)));
    }
    while(!in_array($CapturedValue, $AvailableValues) && !($defaultVal != NULL && $CapturedValue == ''));
    
    // Return user input or the default value
    return empty($CapturedValue) ? $defaultVal : $CapturedValue;
}

function build_manual($Appdir, $ManualTitle)
{
    // Go to the package directory (required by PHPDOCUMENTOR to run smoothly)
    echo "\n";
    echo "CD " . $Appdir . "\n";
    $oldCurrentDir = getcwd();
    chdir($Appdir);
    
    echo "Buiding manual with phpDocumentor\n";
    $cmd = 'php "' . BIN_PHPDOC . '" -vvv -c phpdoc.xml --cache-folder ..' . DIRECTORY_SEPARATOR . 'phpdoc_cache --title="' . $ManualTitle . '" 2>&1';
    echo $cmd;
    
    $fp = popen($cmd, 'r');
    
    while (!feof($fp))
    {
        $buffer = fgets($fp, 4096);
        echo $buffer;
    }
    
    pclose($fp);
    
    echo "Clear phpDocumentor cache\n";
    $ManualDir = $Appdir . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'manual';
    foreach(\FILE\dirInDirToArray($ManualDir) as $dir)
    {
        if (mb_substr($dir , 0, mb_strlen('phpdoc-cache-')) == 'phpdoc-cache-')
        {
            \FILE\recurse_rmdir($ManualDir . DIRECTORY_SEPARATOR . $dir);
        }
    }
    
    \FILE\recurse_rmdir($Appdir . DIRECTORY_SEPARATOR . "build");
    \FILE\recurse_rmdir($Appdir . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "phpdoc_cache");
    \FILE\rmfile($Appdir . DIRECTORY_SEPARATOR . "ast.dump");
    
    // Restores current directory
    echo "CD " . $oldCurrentDir . "\n";
    chdir($oldCurrentDir);
}

function formatParagraph($str, $lenght, $padlenght)
{
	$res = "";
	$line = "";
	
	foreach(preg_split("/\s+/u", $str) as $word)
	{
		if (mb_strlen($line) + (mb_strlen($line) > 0 ? 1 : 0) + mb_strlen($word) > $lenght)
		{
			$res .= str_repeat(" ", $padlenght) . $line . "\n";
			$line = "";
		}
		$line = $line . (mb_strlen($line) > 0 ? " " : "") . $word;
	}
	
	if (mb_strlen($line) > 0)
	{
		$res .= str_repeat(" ", $padlenght) . $line;
	}
	
	return $res;
}
