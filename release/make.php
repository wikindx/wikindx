<?php
/*
Copyright Stéphane Aulery, 2018-2020
Copyright Mark Grimshaw-Aagaard, 2018

<lkppo@users.sourceforge.net>

This software is a computer program used to prepare the wikindx code
for its official publication.

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

define('DIRSRC_ROOT', DIR_ROOT . DIRECTORY_SEPARATOR . 'wikindx');
define('DIRSRC_TAGS', DIRSRC_ROOT . DIRECTORY_SEPARATOR . 'tags');
define('DIRSRC_TRUNK', DIRSRC_ROOT . DIRECTORY_SEPARATOR . 'trunk');

define('BIN_PHPDOC', DIR_ROOT . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phpDocumentor.phar');

include_once(DIR_ROOT . "/wikindx/trunk/core/startup/CONSTANTS.php");
include_once(DIR_ROOT . "/wikindx/trunk/core/file/FILE.php");
include_once(DIR_ROOT . "/wikindx/trunk/core/utils/UTILS.php");

$VersionsAvailable = \FILE\dirInDirToArray(DIRSRC_TAGS);
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
        define('DIR_DST_SRC', DIR_DST . DIRECTORY_SEPARATOR . 'wikindx');
        define('DIR_DST_PKG', DIR_DST . DIRECTORY_SEPARATOR . 'package');
        define('DIR_DST_CMP', DIR_DST_PKG . DIRECTORY_SEPARATOR . 'components');
    break;
    default:
        define('DIR_SRC', DIRSRC_TAGS . DIRECTORY_SEPARATOR . $VersionPackaged);
        define('DIR_DST', DIRPKG_ROOT . DIRECTORY_SEPARATOR . $VersionPackaged);
        define('DIR_DST_SRC', DIR_DST . DIRECTORY_SEPARATOR . 'wikindx');
        define('DIR_DST_PKG', DIR_DST . DIRECTORY_SEPARATOR . 'package');
        define('DIR_DST_CMP', DIR_DST_PKG . DIRECTORY_SEPARATOR . 'components');
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
echo "MKDIR " . DIR_DST_CMP . "\n";
mkdir(DIR_DST_CMP);
echo "\n";

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
    $pkgarch = \FILE\createComponentPackage(DIR_DST_SRC . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'manual', DIR_DST_PKG, $pkg, $archformat);
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
\FILE\rmfile(DIR_DST_SRC . DIRECTORY_SEPARATOR . '.php_cs.cache');
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
    DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_LANGUAGES => \FILE\dirInDirToArray(DIR_DST_SRC . DIRECTORY_SEPARATOR . WIKINDX_DIR_COMPONENT_LANGUAGES),
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
                $pkg = APP_PKG_PREFIX . "_" . $componentConfig["component_type"] . "_" . $componentConfig["component_id"];
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
                    $pkgarch = \FILE\createComponentPackage($componentDir, DIR_DST_CMP, $pkg, $archformat);
                    echo " - $archformat arch: " . $pkgarch . "\n";
                    
                    $pkghash = \UTILS\hash_path($pkgarch, WIKINDX_PACKAGE_HASH_ALGO);
                    echo " - $archformat hash: " . $pkghash . "\n";
    				
					$signatures .= WIKINDX_PACKAGE_HASH_ALGO . ";" . $pkghash . ";" . basename($pkgarch) . "\n";
                    
                    // The update server use only the ZIP format because the decompression of .tar.gz and .tar.bz2 is broken on macOS
                    // BZIP2 and GZ are kept for manual update
                    if ($archformat == "ZIP")
                    {
	                    $PkgList[] = [
	                    	"package_location" => SF_RELEASE_SERVER . "/" . $VersionPackaged . "/components/" . basename($pkgarch),
	                    	"package_" . WIKINDX_PACKAGE_HASH_ALGO => $pkghash, "package_size" => filesize($pkgarch)
	                    ];
                    }
                    
                    $readmecmp .= "    - " . basename($pkgarch) . "\n";
                    $readmecmp .= "      " . $pkghash . " (" . WIKINDX_PACKAGE_HASH_ALGO . ")\n";
                }
                
                $componentConfig["component_packages"] = $PkgList;
                $componentlist[] = $componentConfig;
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
echo "Build component packages list for the update server\n";
file_put_contents(DIR_DST_CMP . DIRECTORY_SEPARATOR . "components.json", json_encode($componentlist, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));


echo "\n";
echo "Build core package\n";
$pkg = APP_PKG_PREFIX;
echo "Package " . $pkg . "\n";

foreach (["BZIP2", "GZ", "ZIP"] as $archformat)
{                
    $pkgarch = \FILE\createComponentPackage(DIR_DST_SRC, DIR_DST_PKG, $pkg, $archformat);
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

file_put_contents(DIR_DST_PKG . DIRECTORY_SEPARATOR . "README.txt", $readmeglobal);

echo "Signatures file\n";
file_put_contents(DIR_DST_PKG . DIRECTORY_SEPARATOR . APP_NAME . "_" . $VersionPackaged . "_signatures.txt", $signatures);


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
