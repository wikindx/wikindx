<?php

header("Content-type: text/javascript", true);

$files_css = [
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

foreach ($files_css as $f)
{
    echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $f) . "\n";
}
