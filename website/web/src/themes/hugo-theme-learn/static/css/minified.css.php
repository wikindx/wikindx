<?php

/*
 * Make a bundle of CSS files
 */

$code = "";

$files = [
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

foreach ($files as $f)
{
    $css = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $f);
    
    $css = str_replace("\n", "", $css);
    
    do {
        $len = mb_strlen($css);
        $css = str_replace("  ", " ", $css);
        $css = str_replace(" {", "{", $css);
        $css = str_replace("{ ", "{", $css);
        $css = str_replace(" }", "}", $css);
        $css = str_replace("} ", "}", $css);
        $css = str_replace("; ", ";", $css);
        $css = str_replace(": ", ":", $css);
    } while ($len > mb_strlen($css));
    
    $code .= $css . "\n";
    unlink(__DIR__ . DIRECTORY_SEPARATOR . $f);
}

$file_bundle = __DIR__ . DIRECTORY_SEPARATOR . "minified.css";

file_put_contents($file_bundle, $code);

unlink(__FILE__);
