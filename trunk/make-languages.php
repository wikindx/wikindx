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
include_once("core/file/FILE.php");
include_once("core/locales/LOCALES.php");

///////////////////////////////////////////////////////////////////////
/// Configuration
///////////////////////////////////////////////////////////////////////

$listlocales = \LOCALES\getAllLanguages();

// Never generate a catalog for the source language
unset($listlocales["en"]);
unset($listlocales[WIKINDX_LANGUAGE_DEFAULT]);

$dirroot = __DIR__;
$dirplugins = implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_PLUGINS]);
$dirsrc = implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_LANGUAGES, "src"]);
$dirtra = implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_LANGUAGES]);
$execoutput = [];
$errorcode = 0;
$emailreport = "sirfragalot@users.sourceforge.net";
$excludedir = [
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_CACHE]), // Cache directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_DATA]), // Data directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_DB_SCHEMA]), // Data directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, "docs"]), // Data directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_LANGUAGES]), // component directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_STYLES]), // component directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_TEMPLATES]), // component directory
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_VENDOR]), // Third party lib
    implode(DIRECTORY_SEPARATOR, [__DIR__, WIKINDX_DIR_COMPONENT_PLUGINS]), // Plugins are treated as their own domain
];


///////////////////////////////////////////////////////////////////////
/// MAIN
///////////////////////////////////////////////////////////////////////


echo "Create missing locales folders\n";

if (!file_exists($dirsrc)) {
    mkdir($dirsrc, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
}
if (!file_exists($dirtra)) {
    mkdir($dirtra, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
}

foreach ($listlocales as $locale => $localeName) {
    $dirmo = $dirtra . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . "LC_MESSAGES";
    if (!file_exists($dirmo)) {
        echo " - MKDIR $dirmo\n";
        mkdir($dirmo, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
    }
    
    $dirpo = $dirsrc . DIRECTORY_SEPARATOR . $locale;
    if (!file_exists($dirpo)) {
        echo " - MKDIR $dirpo\n";
        mkdir($dirpo, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
    }
}


echo "\n";
echo "Updating plugin tranlations\n";

$listDirDomain = [
    $dirroot => [WIKINDX_LANGUAGE_DOMAIN_DEFAULT],
    $dirplugins => FILE\dirInDirToArray($dirplugins),
];

foreach ($listDirDomain as $dir => $DirDomain) {
    foreach ($DirDomain as $domain) {
        if ($dir == __DIR__) {
            echo " - Core $domain domain\n";
            $packagename = strtolower($domain);
            $inputdir = $dir;
        } else {
            echo " - Plugin $domain domain\n";
            $packagename = strtolower($domain);
            $inputdir = $dirplugins . DIRECTORY_SEPARATOR . $domain;
        }
        
        $phpfilelist = $dirsrc . DIRECTORY_SEPARATOR . $packagename . ".lst";
        $potfile = $dirsrc . DIRECTORY_SEPARATOR . $packagename . ".pot";
        $potfiletmp = $dirsrc . DIRECTORY_SEPARATOR . $packagename . ".pot.tmp";
        
        echo "   - List all PHP files to $phpfilelist\n";
        
        if ($dir == __DIR__) {
            saveListPHPfilesInDirectory($phpfilelist, $inputdir, $excludedir);
        } else {
            saveListPHPfilesInDirectory($phpfilelist, $inputdir);
        }
            
        // Create missing templates for each domain
        echo "   - Extract all translatable strings in file " . $potfile . "\n";
        $potfile = $dirsrc . DIRECTORY_SEPARATOR . $packagename . ".pot";
        exec("xgettext -L PHP --from-code=UTF-8 -c -n -w 80 --sort-by-file --keyword=local_gettext --msgid-bugs-address=$emailreport --package-name=$packagename -o \"$potfiletmp\" -f \"$phpfilelist\"");

        if (file_exists($potfiletmp)) {
            // Customizing the pot file for the project
            $potcontent = file_get_contents($potfiletmp);

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

            file_put_contents($potfiletmp, $potcontent);

            // Avoid merging a new pot file with translations if it doesn't change more than by its creation date
            if (file_exists($potfile)) {
                $potfilecontent = file_get_contents($potfile);
                $potfiletmpcontent = file_get_contents($potfiletmp);

                $potfilecontent = preg_replace('/"POT-Creation-Date:.+"/um', "", $potfilecontent);
                $potfiletmpcontent = preg_replace('/"POT-Creation-Date:.+"/um', "", $potfiletmpcontent);

                if ($potfilecontent == $potfiletmpcontent) {
                    unlink($potfiletmp);
                } else {
                    unlink($potfile);
                    rename($potfiletmp, $potfile);
                }
            } else {
                rename($potfiletmp, $potfile);
            }
        }
        
        // Cleaning
        unlink($phpfilelist);
        
        // countinue only for domains with translatable strings
        if (file_exists($potfile)) {
            echo "   - Merge and compile translations:\n";
            foreach (FILE\dirInDirToArray($dirtra) as $locale) {
                // Skip folders that do not contain po files
                if (!array_key_exists($locale, $listlocales)) {
                    continue;
                }
                
                echo "     - " . $locale . " : ";
                
                $tmpfile = $dirsrc . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $packagename . ".po.tmp";
                $pofile = $dirsrc . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $packagename . ".po";
                $mofile = $dirtra . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $packagename . ".mo";
                
                if (!file_exists($pofile)) {
                    // Create a translation file and intercept the STDERR of msginit because on Windows,
                    // with a cmd or Powershell console, because msginit emits wrongly an error code 255
                    // when a file is created and this error is not catcheable.
                    $execoutput = [];
                    exec("msginit --no-translator --locale=$locale.UTF-8 -i \"$potfile\" -o \"$pofile\" 2>&1", $execoutput, $errorcode);
                    abortOnError($errorcode);
                }
                
                if (file_exists($pofile)) {
                    // Merge all translatable string changes with previous translations
                    $execoutput = [];
                    exec("msgmerge -q --previous -w 80 --sort-by-file --lang=$locale -o \"$tmpfile\" \"$pofile\" \"$potfile\"", $execoutput, $errorcode);
                    abortOnError($errorcode, $errorcode);
                    copy($tmpfile, $pofile);
                    
                    // Cleaning
                    unlink($tmpfile);
                }
                
                if (file_exists($pofile)) {
                    $pocontent = file_get_contents($pofile);
                    $pocontent = str_replace(
                        "# SOME DESCRIPTIVE TITLE.",
                        "# Wikindx's Translation ressource: " . $listlocales[$locale] . ".",
                        $pocontent
                    );
                    $pocontent = str_replace(
                        "# Copyright (C) YEAR THE PACKAGE'S COPYRIGHT HOLDER",
                        "# For copyright see the component.json file",
                        $pocontent
                    );
                    file_put_contents($pofile, $pocontent);
                }
                
                // Compile gettext catalog
                $execoutput = [];
                exec("msgfmt -v -o \"$mofile\" \"$pofile\"", $execoutput, $errorcode);
                abortOnError($errorcode, $errorcode);
            }
        }
        
        echo "\n";
    }
}


///////////////////////////////////////////////////////////////////////
/// Library
///////////////////////////////////////////////////////////////////////

function saveListPHPfilesInDirectory($filelist, $searchdir, $excludedir = [])
{
    file_put_contents($filelist, implode("\n", recursiveListPHPfilesInDirectory($searchdir, $excludedir)));
}

function recursiveListPHPfilesInDirectory($rootdir, $excludedir = NULL)
{
    $list = [];
    
    foreach (FILE\dirToArray($rootdir) as $p) {
        if (is_dir($rootdir . DIRECTORY_SEPARATOR . $p)) {
            $process = TRUE;
            if (is_array($excludedir)) {
                if (count($excludedir) > 0) {
                    foreach ($excludedir as $ed) {
                        if (mb_substr($rootdir . DIRECTORY_SEPARATOR . $p, 0, mb_strlen($ed)) == $ed) {
                            $process = FALSE;

                            break;
                        }
                    }
                }
            }
            
            if ($process) {
                $tmp = recursiveListPHPfilesInDirectory($rootdir . DIRECTORY_SEPARATOR . $p, $excludedir);
                $list = array_merge($list, $tmp);
            }
        } elseif (matchExtension($p, ".php")) {
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
    if ($errorcode != 0) {
        die("\n" . "The previous process exited with error code " . $errorcode . "\n");
    }
}
