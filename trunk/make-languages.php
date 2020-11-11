<?php
/*
ISC License

Copyright (c) 2019, Stéphane Aulery, <lkppo@users.sourceforge.net>

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

include_once("core/startup/CONSTANTS.php");
include_once("core/libs/FILE.php");
include_once("core/libs/LOCALES.php");

///////////////////////////////////////////////////////////////////////
/// Configuration
///////////////////////////////////////////////////////////////////////

$dirroot = __DIR__;
$dirplugins = implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_PLUGINS]);
$execoutput = [];
$errorcode = 0;
$emailreport = "sirfragalot@users.sourceforge.net";
$excludedir = [
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_CACHE]), // Cache directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_DATA]), // Data directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_DB_SCHEMA]), // Data directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_DB_DOCS]), // Data directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_STYLES]), // component directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_TEMPLATES]), // component directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_VENDOR]), // Third party lib
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_PLUGINS]), // Plugins are treated as their own domain
];


///////////////////////////////////////////////////////////////////////
/// MAIN
///////////////////////////////////////////////////////////////////////

echo "\n";
echo "Updating plugin tranlations\n";

$listDirDomain = [
    $dirroot => [WIKINDX_LANGUAGE_DOMAIN_DEFAULT],
    $dirplugins => FILE\dirInDirToArray($dirplugins),
];

foreach ($listDirDomain as $dir => $DirDomain)
{
    foreach ($DirDomain as $domain)
    {
        $packagename = mb_strtolower($domain);

        if ($dir == __DIR__)
        {
            echo " - Core $domain domain\n";
            $inputdir = $dir;
            $dirsrc = implode(DIRECTORY_SEPARATOR, [$inputdir, WIKINDX_DIR_CORE_LANGUAGES, "src"]);
            $dirtra = implode(DIRECTORY_SEPARATOR, [$inputdir, WIKINDX_DIR_CORE_LANGUAGES]);
        }
        else
        {
            echo " - Plugin $domain domain\n";
            $inputdir = $dirplugins . DIRECTORY_SEPARATOR . $domain;
            $dirsrc = implode(DIRECTORY_SEPARATOR, [$inputdir, "languages", "src"]);
            $dirtra = implode(DIRECTORY_SEPARATOR, [$inputdir, "languages"]);
        }
        
        echo "Create missing locales folders\n";

        if (!file_exists($dirsrc))
        {
            mkdir($dirsrc, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
        }
        if (!file_exists($dirtra))
        {
            mkdir($dirtra, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
        }
        
        $phpfilelist = $dirsrc . DIRECTORY_SEPARATOR . $packagename . ".lst";
        $potfile = $dirsrc . DIRECTORY_SEPARATOR . $packagename . ".pot";
        $potfiletmp = $dirsrc . DIRECTORY_SEPARATOR . $packagename . ".pot.tmp";
        
        echo "   - List all PHP files to $phpfilelist\n";
        
        if ($dir == __DIR__)
        {
            saveListPHPfilesInDirectory($phpfilelist, $inputdir, $excludedir);
        }
        else
        {
            saveListPHPfilesInDirectory($phpfilelist, $inputdir);
        }
            
        // Create missing templates for each domain
        echo "   - Extract all translatable strings in file " . $potfile . "\n";
        $potfile = $dirsrc . DIRECTORY_SEPARATOR . $packagename . ".pot";
        extractPotFile($potfiletmp, $phpfilelist, $packagename, $emailreport);
        unlink($phpfilelist);

        // When the best file is the new, overwrite the previous with the new file
        if ($potfiletmp == bestPotFile($potfile, $potfiletmp))
        {
            rename($potfiletmp, $potfile);
        }
        else
        {
            if (file_exists($potfiletmp))
            {
                unlink($potfiletmp);
            }
        }
        
        // countinue only for domains with translatable strings
        echo "   - Merge and compile translations:\n";
        foreach (FILE\dirInDirToArray($dirtra) as $locale)
        {
            // Skip folders that do not contain po files
            if (!checkLocaleCode($locale))
            {
                continue;
            }
            
            $dirmo = $dirtra . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . "LC_MESSAGES";
            if (!file_exists($dirmo))
            {
                echo " - MKDIR $dirmo\n";
                mkdir($dirmo, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
            }
            $dirpo = $dirsrc . DIRECTORY_SEPARATOR . $locale;
            if (!file_exists($dirpo))
            {
                echo " - MKDIR $dirpo\n";
                mkdir($dirpo, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
            }
                
            echo "     - " . $locale . ": ";
                
            $tmpfile = $dirsrc . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $packagename . ".po.tmp";
            $pofile = $dirsrc . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $packagename . ".po";
            $mofile = $dirtra . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $packagename . ".mo";
                
            initPoFile($potfile, $pofile, $locale);
            mergePoFile($pofile, $potfile, $tmpfile, $locale);
            if (file_exists($tmpfile))
            {
                rename($tmpfile, $pofile);
            }
            compileMoFile($pofile, $mofile);
        }
        
        echo "\n";
    }
}


///////////////////////////////////////////////////////////////////////
/// Library
///////////////////////////////////////////////////////////////////////

function checkLocaleCode($locale)
{
    $LocList = \LOCALES\getAllLocales($display_code_only = FALSE);
    
    // Never generate a catalog for the source language
    unset($LocList["en"]);
    unset($LocList[WIKINDX_LANGUAGE_DEFAULT]);
    
    foreach ($LocList as $code => $name)
    {
        $locale_variant = $code; // ll[_CC][@variant] (unchanged)
        $locale_country = strpos($code, "@") !== FALSE ? mb_substr($code, 0, strpos($code, "@")) : $code; // ll[_CC]
        $locale_language = strpos($code, "_") !== FALSE ? mb_substr($code, 0, strpos($code, "_")) : $code; // ll
        
        if (
            mb_strtolower($locale_variant) == mb_strtolower($locale) ||
            mb_strtolower($locale_country) == mb_strtolower($locale) ||
            mb_strtolower($locale_language) == mb_strtolower($locale)
        ) {
            return TRUE;
        }
    }

    return FALSE;
}
// Return the name of the POT to keep
//
// The new POT file if it has significative changes, otherwise the old
function bestPotFile($potFileOld, $potFileNew)
{
    // Avoid merging a new pot file with translations if it doesn't change more than by its creation date
    if (file_exists($potFileOld) && file_exists($potFileNew))
    {
        $potfilecontentold = file_get_contents($potFileOld);
        $potfilecontentnew = file_get_contents($potFileNew);

        $potfilecontentold = preg_replace('/"POT-Creation-Date:.+"/um', "", $potfilecontentold);
        $potfilecontentnew = preg_replace('/"POT-Creation-Date:.+"/um', "", $potfilecontentnew);

        if ($potfilecontentold == $potfilecontentnew)
        {
            return $potFileOld;
        }
        else
        {
            return $potFileNew;
        }
    }
    elseif (file_exists($potFileOld))
    {
        return $potFileOld;
    }
    elseif (file_exists($potFileNew))
    {
        return $potFileNew;
    }
    else
    {
        return "";
    }
}

// Extract reference string to translate in a gettext POT file
function extractPotFile($potFile, $aFileList, $packagename, $emailreport)
{
    exec("xgettext -L PHP --from-code=UTF-8 -c -n -w 80 --sort-by-file --keyword=local_gettext --msgid-bugs-address=$emailreport --package-name=$packagename -o \"$potFile\" -f \"$aFileList\"");

    if (file_exists($potFile))
    {
        // Customizing the pot file for the project
        $potcontent = file_get_contents($potFile);

        // Change the charset for UTF-8
        $potcontent = str_replace(
            "Content-Type: text/plain; charset=CHARSET",
            "Content-Type: text/plain; charset=UTF-8",
            $potcontent
        );

        // Remove the absolute path of source files to be able to perform a diff on the next update
        $potcontent = str_replace(
            "#: " . __DIR__ . DIRECTORY_SEPARATOR,
            "#: ",
            $potcontent
        );

        // Normalize the path separator to be able to perform a diff on the next update
        $potcontent = preg_replace_callback(
            "/^#: .+:\\d+$/um",
            function ($matches) {
                return mb_strtolower(str_replace("\\", "/", $matches[0]));
            },
            $potcontent
        );

        file_put_contents($potFile, $potcontent);
    }
}

// Init a gettext translation file from a pot template
function initPoFile($potFile, $poFile, $locale)
{
    if (!file_exists($potFile) || file_exists($poFile))
    {
        return;
    }

    // Create a translation file and intercept the STDERR of msginit because on Windows,
    // with a cmd or Powershell console, because msginit emits wrongly an error code 255
    // when a file is created and this error is not catcheable.
    $errorcode = 0;
    $execoutput = [];
    exec("msginit --no-translator --locale=$locale.UTF-8 -i \"$potFile\" -o \"$poFile\" 2>&1", $execoutput, $errorcode);
    abortOnError($errorcode);
}

// Merge the 1st and 2d po file to 3rd po file
// 1st po file must be the previous po file
function mergePoFile($poFile1, $poFile2, $poFile3, $locale)
{
    if (!file_exists($poFile1) || !file_exists($poFile2))
    {
        return;
    }

    // Merge all translatable string changes with previous translations
    $errorcode = 0;
    $execoutput = [];
    exec("msgmerge -q --previous -w 80 --sort-by-file --lang=$locale -o \"$poFile3\" \"$poFile1\" \"$poFile2\"", $execoutput, $errorcode);
    abortOnError($errorcode, $errorcode);

    if (file_exists($poFile3))
    {
        $pocontent = file_get_contents($poFile3);
        $pocontent = str_replace(
            "# SOME DESCRIPTIVE TITLE.",
            "# Wikindx's Translation ressource: " . \LOCALES\codeISO639a1toName($locale) . ".",
            $pocontent
        );
        $pocontent = str_replace(
            "# Copyright (C) YEAR THE PACKAGE'S COPYRIGHT HOLDER",
            "# For copyright see the component.json file",
            $pocontent
        );
        file_put_contents($poFile3, $pocontent);
    }
}

// Compile a PO gettext catalog to binary format (MO file)
function compileMoFile($poFile, $moFile)
{
    if (!file_exists($poFile))
    {
        return;
    }

    $errorcode = 0;
    $execoutput = [];
    exec("msgfmt -v -o \"$moFile\" \"$poFile\"", $execoutput, $errorcode);
    abortOnError($errorcode, $errorcode);
}

function saveListPHPfilesInDirectory($filelist, $searchdir, $excludedir = [])
{
    file_put_contents($filelist, implode("\n", recursiveListPHPfilesInDirectory($searchdir, $excludedir)));
}

function recursiveListPHPfilesInDirectory($rootdir, $excludedir = NULL)
{
    $list = [];
    
    foreach (FILE\dirToArray($rootdir) as $p)
    {
        if (is_dir($rootdir . DIRECTORY_SEPARATOR . $p))
        {
            $process = TRUE;
            if (is_array($excludedir))
            {
                if (count($excludedir) > 0)
                {
                    foreach ($excludedir as $ed)
                    {
                        if (mb_substr($rootdir . DIRECTORY_SEPARATOR . $p, 0, mb_strlen($ed)) == $ed)
                        {
                            $process = FALSE;

                            break;
                        }
                    }
                }
            }
            
            if ($process)
            {
                $tmp = recursiveListPHPfilesInDirectory($rootdir . DIRECTORY_SEPARATOR . $p, $excludedir);
                $list = array_merge($list, $tmp);
            }
        }
        elseif (matchExtension($p, ".php"))
        {
            $list[] = $rootdir . DIRECTORY_SEPARATOR . $p;
        }
    }
    
    return $list;
}

function matchExtension($filename, $ext)
{
    return (mb_strtolower(mb_substr($filename, -mb_strlen($ext))) == $ext);
}

function abortOnError($errorcode)
{
    if ($errorcode != 0)
    {
        die("\n" . "The previous process exited with error code " . $errorcode . "\n");
    }
}
