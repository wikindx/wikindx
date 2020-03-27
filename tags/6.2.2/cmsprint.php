<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * CMS
 *
 * Content Management System hooks.
 *
 * @package wikindx
 */
include_once("core/startup/CONSTANTS.php");

function preserve_qs()
{
    if (empty($_SERVER['QUERY_STRING']) && mb_strpos($_SERVER['REQUEST_URI'], '?') === FALSE)
    {
        return '';
    }

    return '&' . $_SERVER['QUERY_STRING'];
}

// This code had moved to core/modules/sitemap/SITEMAP.php
// to use the current module loading scheme.
// Keep this page only to not break external links
// LkpPo, 20180802
// HTTP/1.0 301 Moved Permanently

// Check if request is to parse text for [cite]...[/cite] tags
if (array_key_exists('action', $_GET) && ($_GET['action'] == 'parseText' || $_GET['action'] == 'parseSql'))
{
    header('Location: ' . WIKINDX_CMS_PAGE . str_replace('action=', 'method=', preserve_qs()), TRUE, 301);
}
else
{
    header('Location: ' . WIKINDX_CMS_PAGE . str_replace('action=', 'type=', preserve_qs()) . ((!array_key_exists('method', $_GET)) ? '&method=queryDb' : ''), TRUE, 301);
}

exit();
