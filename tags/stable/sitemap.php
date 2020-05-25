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
 * Sitemap
 *
 * Sitemap XML for indexation robots
 * (See http://www.sitemaps.org)
 * (See https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt?hl=en)
 *
 * This is a standard technique to facilitate the indexing of public content
 * by indexation robots visiting the website
 *
 * The search engine of Wikindx is not usable by indexation robots.
 * This page compensate for this lack.
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

// This code had moved to core/modules/sitemap/SITEMAP.php
// to use the current module loading scheme.
// Keep this page only to not break external links
// LkpPo, 20180802
// HTTP/1.0 301 Moved Permanently
header('Location: ' . WIKINDX_SITEMAP_PAGE . preserve_qs(), TRUE, 301);

exit();
