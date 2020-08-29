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

// Include the config file and check if the CONFIG class is in place
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));

include_once("core/startup/CONSTANTS.php");
include_once("core/libs/FILE.php");
include_once("core/libs/LOCALES.php");
include_once("core/libs/UTILS.php");

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
