<?php

header("Content-type: application/javascript", true);

$files_js = [
    "jquery-3.3.1.min.js",
    "lunr.min.js",
    "auto-complete.js",
];

foreach ($files_js as $f)
{
    echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $f) . "\n";
}
