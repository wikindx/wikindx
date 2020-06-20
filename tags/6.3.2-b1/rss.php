<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * RSS
 *
 * RSS feed
 *
 * @package wikindx
 */
include_once("core/startup/CONSTANTS.php");

function preserve_qs()
{
    if (empty($_SERVER['QUERY_STRING']) && mb_strpos($_SERVER['REQUEST_URI'], '?') === FALSE) {
        return '';
    }

    return '&' . $_SERVER['QUERY_STRING'];
}

// This code had moved to core/modules/rss/RSS.php
// to use the current module loading scheme.
// Keep this page only to not break external links
// LkpPo, 20180802
// HTTP/1.0 301 Moved Permanently
header('Location: ' . WIKINDX_RSS_PAGE . preserve_qs(), TRUE, 301);

exit();
