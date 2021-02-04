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

echo "---[Wikindx Website building]---------------------------------------------------\n\n";

$VersionPackaged = promptListUser("Which version do you want to pack?", $VersionsAvailable, "trunk");
$VersionPackaged = mb_strtolower($VersionPackaged);
echo "Version selected: $VersionPackaged\n";

define('DIR_DST', implode(DIRECTORY_SEPARATOR, [DIRPKG_ROOT, "..", "website", "web", $VersionPackaged]));
define('BASE_URL', "https://wikindx.sourceforge.io/web/" . $VersionPackaged . "/");


///////////////////////////////////////////////////////////////////////
/// Build the phpdoc manual
///////////////////////////////////////////////////////////////////////

echo "\n";
echo "Build manual\n";
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
    $cmd = '"' . BIN_HUGO . '" -v --baseURL "' . BASE_URL . '" --cleanDestinationDir --source "' . $dirsrc . '" --destination "' . $dirdst . '" 2>&1';
    echo $cmd;
    
    $fp = popen($cmd, 'r');
    
    while (!feof($fp))
    {
        $buffer = fgets($fp, 4096);
        echo $buffer;
    }
    
    pclose($fp);
}
