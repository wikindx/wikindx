<?php
/*
Copyright Stéphane Aulery, 2019

<lkppo@users.sourceforge.net>

Ce logiciel est un programme informatique servant à préparer le code de
wikindx pour sa publication officiel.

This software is a computer program whose purpose is to dump the db schema
of the Wikindx's RepairKit plugin.

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

// Include the config file and check if the CONFIG class is in place
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));

include_once("core/startup/CONSTANTS.php");
include_once("core/file/FILE.php");
include_once("core/locales/LOCALES.php");
include_once("core/utils/UTILS.php");

// Begin page execution timer and define globals for rendering by template
include_once("core/startup/GLOBALS.php");

// Set up the FACTORY objects of commonly used classes and start the timer.
include_once("core/startup/FACTORY.php");

// Initialize the static config read from config.php file
include_once("core/startup/LOADSTATICCONFIG.php");

$db = FACTORY_DB::getInstance();

$dbSchema = $db->createRepairKitDbSchema();

echo "Remove previous RepairKit schema\n";

if (file_exists(WIKINDX_FILE_REPAIRKIT_DB_SCHEMA)) {
    unlink(WIKINDX_FILE_REPAIRKIT_DB_SCHEMA);
}

if ($db->writeRepairKitDbSchema($dbSchema, WIKINDX_FILE_REPAIRKIT_DB_SCHEMA)) {
    echo "RepairKit Schema dump successfull\n";
} else {
    echo "RepairKit Schema dump failed\n";
}
