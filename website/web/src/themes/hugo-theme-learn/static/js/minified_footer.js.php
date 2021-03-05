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
 * Make a bundle of JS files for the footer and destroy source files.
 *
 * Called by release/cli-make-web.php
 */

$code = "";

$files = [
    "clipboard.min.js",
    "perfect-scrollbar.min.js",
    "perfect-scrollbar.jquery.min.js",
    "jquery.sticky.js",
    "featherlight.min.js",
    "highlight.pack.js",
    "modernizr.custom-3.6.0.js",
    "learn.js",
    "hugo-learn.js",
    /*"mermaid.js",*/
];

foreach ($files as $f)
{
    $code .= file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $f) . "\n";
    unlink(__DIR__ . DIRECTORY_SEPARATOR . $f);
}

$file_bundle = __DIR__ . DIRECTORY_SEPARATOR . "minified_footer.js";

file_put_contents($file_bundle, $code);

unlink(__FILE__);
