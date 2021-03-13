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
 * cli-make-web.php
 *
 * Script to build the website with Hugo (v0.80.0 only).
 *
 * @package wikindx\release\api-manual
 */
$Start = microtime();

///////////////////////////////////////////////////////////////////////
/// Configuration (static)
///////////////////////////////////////////////////////////////////////

define('DATE_RELEASE', date('YmdHis'));
define('DIR_ROOT', __DIR__ . DIRECTORY_SEPARATOR . "..");
define('DIRPKG_ROOT', __DIR__);

define('DIRSRC_ROOT', DIR_ROOT);
define('DIRSRC_TRUNK', DIRSRC_ROOT . DIRECTORY_SEPARATOR . 'trunk');
define('DIR_SRC', implode(DIRECTORY_SEPARATOR, [DIRPKG_ROOT, "..", "website", "web", "src"]));

include_once(DIRSRC_TRUNK . "/core/startup/CONSTANTS.php");
include_once(DIRSRC_TRUNK . "/core/libs/FILE.php");
include_once(DIRSRC_TRUNK . "/core/libs/UTILS.php");

define('BIN_HUGO', DIR_ROOT . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'hugo' . (\UTILS\OSName() == "windows" ? ".exe" : ""));


$VersionsAvailable[] = WIKINDX_PUBLIC_VERSION;
$VersionsAvailable[] = 'trunk';



///////////////////////////////////////////////////////////////////////
/// Configuration (dynamic)
///////////////////////////////////////////////////////////////////////

echo "---[Website building]------------------------------------------------------------\n\n";

$VersionPackaged = promptListUser("Which version do you want to pack?", $VersionsAvailable, "trunk");
$VersionPackaged = mb_strtolower($VersionPackaged);
echo "Version selected: $VersionPackaged\n";

define('DIR_DST', implode(DIRECTORY_SEPARATOR, [DIRPKG_ROOT, "..", "website", "web", $VersionPackaged]));
define('BASE_URL', "https://wikindx.sourceforge.io/web/" . $VersionPackaged . "/");
//define('BASE_URL', "http://wikindx.test/" . $VersionPackaged . "/");


///////////////////////////////////////////////////////////////////////
/// Build the phpdoc manual
///////////////////////////////////////////////////////////////////////

echo "\n";
echo "Build website\n";
echo " - Source: " . DIR_SRC . "\n";
echo " - Destination: " . DIR_DST . "\n";
echo " - Version: " . $VersionPackaged . "\n";

build_web(DIR_SRC, DIR_DST, $VersionPackaged, 'WIKINDX API ' . $VersionPackaged);

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


function build_web($dirsrc, $dirdst, $APIVersion, $ManualTitle)
{
    // Patch the config file with the target version number
    $fileconf_src = $dirsrc . DIRECTORY_SEPARATOR . "config.toml";
    $fileconf_dst = __DIR__ . DIRECTORY_SEPARATOR . "hugo_config_" . $APIVersion . ".toml";
    
    $conf = file_get_contents($fileconf_src);
    $conf = str_replace("trunk", $APIVersion, $conf);
    file_put_contents($fileconf_dst, $conf);
    
    $cmd = '"' . BIN_HUGO . '" -v --baseURL "' . BASE_URL . '" --cleanDestinationDir --environment ' . $APIVersion . ' --config "' . $fileconf_dst . '" --source "' . $dirsrc . '" --destination "' . $dirdst . '" 2>&1';
    echo $cmd;
    
    $fp = popen($cmd, 'r');
    
    while (!feof($fp))
    {
        $buffer = fgets($fp, 4096);
        echo $buffer;
    }
    
    pclose($fp);
    
    unlink($fileconf_dst);
    
    echo "\n";
    echo "CSS Minification\n";
    include($dirdst . DIRECTORY_SEPARATOR . "css/minified.css.php");
    
    echo "\n";
    echo "JS Minification\n";
    include($dirdst . DIRECTORY_SEPARATOR . "js/minified_header.js.php");
    include($dirdst . DIRECTORY_SEPARATOR . "js/minified_footer.js.php");
    
    echo "\n";
    echo "HTML Minification\n";
    foreach (\FILE\recurse_fileInDirToArray($dirdst) as $f)
    {
        $file = $dirdst . DIRECTORY_SEPARATOR . $f;
        
        if (\UTILS\matchSuffix($f, ".html") == ".html")
        {
            echo $file . "\n";
            $code = "";
            $code .= file_get_contents($file);
            
            do {
                $len = mb_strlen($code);
                //$code = str_replace("\t", " ", $code); // Destroy code in pre
                //$code = str_replace("  ", " ", $code); // Destroy code in pre
                $code = str_replace(">\n <", ">\n<", $code);
                $code = str_replace(" \n", "\n", $code);
                $code = str_replace("\n\n", "\n", $code);
            } while ($len > mb_strlen($code));
            
            file_put_contents($file, $code);
        }
    }
}
