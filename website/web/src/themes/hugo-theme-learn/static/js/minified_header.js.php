<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/*
 * Make a bundle of JS files for the header and destroy source files.
 *
 * Called by release/cli-make-web.php
 */

$code = "";

$files = [
    "jquery-3.3.1.min.js",
    "lunr.min.js",
    "auto-complete.js",
];

foreach ($files as $f)
{
    $code .= file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $f) . "\n";
    unlink(__DIR__ . DIRECTORY_SEPARATOR . $f);
}

$file_bundle = __DIR__ . DIRECTORY_SEPARATOR . "minified_header.js";

file_put_contents($file_bundle, $code);

unlink(__FILE__);
