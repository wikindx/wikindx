<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "startup", "WEBSERVERCONFIG.php"]));

include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "modules", "cite", "INSERTCITATION.php"]));

GLOBALS::addTplVar('scripts', '<link href="' . WIKINDX_URL_BASE . '/core/tiny_mce/plugins/' . basename(__DIR__) . '/css/wikindxCite.css" rel="stylesheet" type="text/css">');

$cite = new INSERTCITATION();
$cite->init();
