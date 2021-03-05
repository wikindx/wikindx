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
 * This script redirect pages of the old website to the new website.
 *
 * This script must be installed at https://wikindx.sourceforge.io/web/index.php
 */

$page = empty($_GET['page']) ? "index" : mb_strtolower(trim($_GET['page']));

$redirections = [
    "404" => "404.html",
    "about" => "index.html",
    "extensions" => "index.html",
    "faq" => "getting-started/faq/",
    "features" => "index.html",
    "index" => "index.html",
    "install" => "getting-started/install/",
    "news" => "news",
    "screenshots" => "screenshots/",
    "users" => "index.html",
];

if (array_key_exists($page, $redirections))
// Redirect the addresses of the old site to the saddles of the new one.
    $newpage = $redirections[$page];
else
// If the page is unknown, go to the root of the new site.
    $newpage = "index.html";

header("Location: https://wikindx.sourceforge.io/web/trunk/" . $newpage, TRUE, 301);
