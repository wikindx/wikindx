<?php

/*
ISC License

Copyright (c) 2017, Stéphane Aulery, <lkppo@users.sourceforge.net>

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

/*
    This script redirect to the new website.
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
