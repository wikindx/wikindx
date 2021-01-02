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
 * cli-make-manual.php
 *
 * Script to build the API manual with phpDocumentor.
 *
 * @package wikindx\release\manual
 */
include_once("core/startup/CONSTANTS.php");
include_once("core/libs/FILE.php");

///////////////////////////////////////////////////////////////////////
/// MAIN
///////////////////////////////////////////////////////////////////////

echo "\n";
echo "Build API manual with phpDocumentor\n";

build_manual(__DIR__, 'WIKINDX API ' . WIKINDX_PUBLIC_VERSION);

///////////////////////////////////////////////////////////////////////
/// Library
///////////////////////////////////////////////////////////////////////

function build_manual($Appdir, $ManualTitle)
{
    // Go to the package directory (required by PHPDOCUMENTOR to run smoothly)
    echo "\n";
    echo "CD " . $Appdir . "\n";
    $oldCurrentDir = getcwd();
    chdir($Appdir);
    
    $bin_phpdoc = "";
    foreach (
        [
            implode(DIRECTORY_SEPARATOR, [$Appdir, "phpDocumentor.phar"]), // Could be here if copied by hand
            implode(DIRECTORY_SEPARATOR, [$Appdir, "tools", "phpDocumentor.phar"]), // Should be here if copied by hand
            implode(DIRECTORY_SEPARATOR, [$Appdir, "..", "tools", "phpDocumentor.phar"]), // Should be here if called from a trunk synced from the whole SVN repository
            implode(DIRECTORY_SEPARATOR, [$Appdir, "..", "..", "tools", "phpDocumentor.phar"]),
            implode(DIRECTORY_SEPARATOR, [$Appdir, "..", "..", "..", "tools", "phpDocumentor.phar"]), // Should be here if called from make.php
        ] as $f
    ) {
        echo "CD " . $f . "\n";
        
        if (file_exists($f))
        {
            $bin_phpdoc = $f;
            break;
        }
    }
    
    if ($bin_phpdoc == "")
    {
        die("
You need a copy of phpDocumentor.phar 3.0.0 to use this script.
You will find a copy in the tools folder of the Wikindx SVN repository.
Copy the tools folder and its contents to the same directory as this script
and run the script again.
        ");
    }
    else
    {
        $cmd = 'php "' . $bin_phpdoc . '" -vvv -c phpdoc.xml --cache-folder ..' . DIRECTORY_SEPARATOR . 'phpdoc_cache --title="' . $ManualTitle . '" 2>&1';
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
    }
    
    // Restores current directory
    echo "CD " . $oldCurrentDir . "\n";
    chdir($oldCurrentDir);
}
