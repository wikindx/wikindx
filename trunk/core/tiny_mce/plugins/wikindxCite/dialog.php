<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
function SetWikindxBasePath()
{
    $wikindxBasePath = __DIR__;
    while (!in_array(basename($wikindxBasePath), ["", "core"])) {
        $wikindxBasePath = dirname($wikindxBasePath);
    }
    if (basename($wikindxBasePath) == "") {
        die("
            \$WIKINDX_WIKINDX_PATH in config.php is set incorrectly
            and WIKINDX is unable to set the installation path automatically.
            You should set \$WIKINDX_WIKINDX_PATH in config.php.
        ");
    }
    chdir(dirname($wikindxBasePath));
}

SetWikindxBasePath();

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");

include_once("core/modules/cite/INSERTCITATION.php");

GLOBALS::addTplVar('scripts', '<link href="' . WIKINDX_BASE_URL . '/core/tiny_mce/plugins/' . basename(__DIR__) . '/css/wikindxCite.css" rel="stylesheet" type="text/css">');

$cite = new INSERTCITATION();
$cite->init();
