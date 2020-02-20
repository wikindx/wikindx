<?php

$locales = [
    "fr_FR.utf8",
    "fr_FR.utf-8",
    "fr_FR.UTF-8",
    "fr_FR.UTF8",
    "fr_FR",
    "fr-FR.utf8",
    "fr-FR.utf-8",
    "fr-FR.UTF-8",
    "fr-FR.UTF8",
    "fr-FR",
];

foreach ($locales as $locale)
{
    echo $locale . ":" . setlocale(LC_ALL, $locale) . "\n";
}
