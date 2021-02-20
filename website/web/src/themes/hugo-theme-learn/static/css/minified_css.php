<?php

header("Content-type: text/css", true);

$files_css = [
    "nucleus.css",
    "custom-font-awesome.css",
    "hybrid.css",
    "featherlight.min.css",
    "perfect-scrollbar.min.css",
    "auto-complete.css",
    "atom-one-dark-reasonable.css",
    "tags.css",
    "theme.css",
    "hugo-theme.css",
    "theme-blue.css",
    "mermaid.css",
];

foreach ($files_css as $f)
{
    echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $f) . "\n";
}
