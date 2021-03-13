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
 * cli-make-api-manual.php
 *
 * Script to build the API manual with phpDocumentor (v3.0.0 only).
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
define('DIR_SRC', DIRSRC_TRUNK);

define('BIN_PHPDOC', DIR_ROOT . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phpDocumentor.phar');
define('DIR_PHPDOC_CACHE', DIRPKG_ROOT . DIRECTORY_SEPARATOR . "phpdoc_cache");

include_once(DIRSRC_TRUNK . "/core/startup/CONSTANTS.php");
include_once(DIRSRC_TRUNK . "/core/libs/FILE.php");
include_once(DIRSRC_TRUNK . "/core/libs/UTILS.php");

$VersionsAvailable[] = WIKINDX_PUBLIC_VERSION;
$VersionsAvailable[] = 'trunk';



///////////////////////////////////////////////////////////////////////
/// Configuration (dynamic)
///////////////////////////////////////////////////////////////////////

echo "---[API Manual building]---------------------------------------------------------\n\n";

$VersionPackaged = promptListUser("Which version do you want to pack?", $VersionsAvailable, "trunk");
$VersionPackaged = mb_strtolower($VersionPackaged);
echo "Version selected: $VersionPackaged\n";

define('DIR_DST', implode(DIRECTORY_SEPARATOR, [DIRPKG_ROOT, "..", "website", "api-manual", $VersionPackaged]));

///////////////////////////////////////////////////////////////////////
/// Build the phpdoc manual
///////////////////////////////////////////////////////////////////////

echo "\n";
echo "Build manual\n";
echo " - Source: " . DIR_SRC . "\n";
echo " - Destination: " . DIR_DST . "\n";
echo " - Version: " . $VersionPackaged . "\n";

build_manual(DIR_SRC, DIR_DST, $VersionPackaged, 'WIKINDX API ' . $VersionPackaged);

echo "\n";
echo "Set the current date\n";

\FILE\recurse_ChangeDateOfFiles(DIR_DST, WIKINDX_RELEASE_TIMESTAMP);


echo "\n";
echo "Insert a link to return to the website\n";

$menu = <<<EOT
    <ul class="phpdocumentor-topnav__menu">
        <li class="phpdocumentor-topnav__menu-item">
            <a href="https://wikindx.sourceforge.io/web/{$VersionPackaged}/"><span>Return to the website</span></a>
        </li>
        <li class="phpdocumentor-topnav__menu-item">
            <script>
                function switchWebsiteVersion(currentUrl, version)
                {
                    currentUrl = currentUrl.trim();
            
                    // Remove the last slash
                    if (currentUrl.lastIndexOf('/') + 1 != currentUrl.length)
                        targetUrl = currentUrl;
                    else
                        targetUrl = currentUrl.slice(0, currentUrl.lastIndexOf('/'));
                    
                    // Add the version part
                    targetUrl = targetUrl.slice(0, targetUrl.lastIndexOf('/')) + '/' + version + '/';
                    
                    // Redirect
                    window.location = targetUrl;
                }
            
                // Build the dropdown list
                $.getJSON( 'https:\/\/wikindx.sourceforge.io\/api-manual\/' + 'version-switch.php', function( data ) {
                    // Add other options
                    $.each( data, function( value, text ) {
                        // Skip the trunk version (always pre-included)
                        if (value != 'trunk' && value != '{$VersionPackaged}')
                        {
                            $('#verSwitch').append(new Option(value, text));
                        }
                    });
                });
            </script>
            
            <label for="verSwitch" style="color:white">Version</label>
            <select id="verSwitch" name="verSwitch" onchange="switchWebsiteVersion('https:\/\/wikindx.sourceforge.io\/api-manual\/{$VersionPackaged}\/', this.value);" style="display:inline">
EOT;

if ($VersionPackaged == "trunk")
{
    $menu .= '<option value="trunk" selected>trunk</option>' . "\n";
}
else
{
    $menu .= '<option value="trunk">trunk</option>' . "\n";
    $menu .= '<option value="' . $VersionPackaged . '" selected>' . $VersionPackaged . '</option>' . "\n";
}

$menu .= '</select>' . "\n";
$menu .= '</li>' . "\n";

foreach(\FILE\recurse_fileInDirToArray(DIR_DST) as $v)
{
    if (\UTILS\matchSuffix($v, ".html"))
    {
        $file = DIR_DST . DIRECTORY_SEPARATOR . $v;
        echo $file . "\n";
        $html = file_get_contents($file);
        
        $html = preg_replace("//ui", "", $html);
        $html = str_replace('<ul class="phpdocumentor-topnav__menu">', $menu, $html);
        
        file_put_contents($file, $html);
    }
}


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


function build_manual($dirsrc, $dirdst, $APIVersion, $ManualTitle)
{
    if (file_exists($dirdst))
    {
        echo "Clean previous build\n";
        \FILE\recurse_rmdir($dirdst);
    }

    $cmd = 'php "' . BIN_PHPDOC . '" -vvv --config=' . $dirsrc . '/phpdoc.xml --directory=' . $dirsrc . ' --target=' . $dirdst . ' --cache-folder=' . DIR_PHPDOC_CACHE . ' --title="' . $ManualTitle . '" 2>&1';
    echo $cmd;
    
    $fp = popen($cmd, 'r');
    
    while (!feof($fp))
    {
        $buffer = fgets($fp, 4096);
        echo $buffer;
    }
    
    pclose($fp);
    
    echo "Clear phpDocumentor cache\n";
    \FILE\recurse_rmdir(DIR_PHPDOC_CACHE);
    \FILE\rmfile(getcwd() . DIRECTORY_SEPARATOR . "ast.dump");
}
