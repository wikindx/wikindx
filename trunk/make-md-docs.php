<?php
/*
Copyright Stéphane Aulery, 2019

<lkppo@users.sourceforge.net>

This software is a computer program used to build wikindx's API manual.

This software is a computer program whose purpose is to [describe
functionalities and technical features of your software].

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

include_once("core/startup/CONSTANTS.php");
include_once("core/file/FILE.php");

///////////////////////////////////////////////////////////////////////
/// Configuration
///////////////////////////////////////////////////////////////////////

$dirroot = __DIR__;
$dirsrc = implode(DIRECTORY_SEPARATOR, [__DIR__, "docs"]);

///////////////////////////////////////////////////////////////////////
/// MAIN
///////////////////////////////////////////////////////////////////////

echo "\n";
echo "Building markdown documentation\n";

$listDocFile = [
    $dirsrc => FILE\fileInDirToArray($dirsrc),
];

foreach ($listDocFile as $dir => $aFile) {
    foreach ($aFile as $file) {
        $fsrc = $dir . DIRECTORY_SEPARATOR . $file;
        $fdst = $dir . DIRECTORY_SEPARATOR . basename($file, ".md") . ".htm";
        chdir($dir);

        if (is_file($fsrc) && matchExtension($file, ".md")) {
            echo " - $file\n";
            exec("pandoc --verbose --self-contained --number-sections --toc --data-dir=\"$dir\" --from=markdown --to=html5 --output=\"$fdst\" \"$fsrc\"");
        }
    }
}


///////////////////////////////////////////////////////////////////////
/// Library
///////////////////////////////////////////////////////////////////////

function matchExtension($filename, $ext)
{
    return (mb_strtolower(mb_substr($filename, -mb_strlen($ext))) == $ext);
}
