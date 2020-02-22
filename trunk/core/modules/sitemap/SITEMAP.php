<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
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
 * @package wikindx\core\modules\sitemap
 */
class SITEMAP
{
    // Constructor
    public function init()
    {
        $config = FACTORY_CONFIG::getInstance();
        $db = FACTORY_DB::getInstance();
        if (!$config->WIKINDX_SITEMAP_ALLOW)
        {
            header('HTTP/1.0 403 Forbidden');
            die("Access forbidden: this feature is disabled.");
        }

        $baseURL = FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL;

        // set up language
        $messages = FACTORY_MESSAGES::getInstance($config->WIKINDX_LANGUAGE);

        // Get newspage flag: N = 0 = OFF, N > 0 = ON
        $newspage = isset($_GET['newspage']) ? $_GET['newspage'] : 0;
        if (!is_numeric($newspage))
        {
            $newspage = 0;
        }
        $newspage = intval($newspage);

        // Get resourcepage flag: N is a page number or 0 if we don't want to see a specific resource page
        $resourcepage = isset($_GET['resourcepage']) ? $_GET['resourcepage'] : 0;
        if (!is_numeric($resourcepage))
        {
            $resourcepage = 0;
        }
        $resourcepage = intval($resourcepage);


        // Declare text/xml as the header content-type
        header('Content-type: ' . WIKINDX_MIMETYPE_XML . '; charset=' . WIKINDX_CHARSET);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-Requested-With");

        $output = '<?xml version="1.0" encoding="UTF-8"?>';


        // For simplicity and because these queries are very fast,
        // it gets the list of all the information to list

        // List of resources of the website
        $rsResource = $db->query("
        	SELECT `resourcetimestampId`, `resourcetimestampTimestamp`
        	FROM `" . $config->WIKINDX_DB_TABLEPREFIX . "resource_timestamp`
        	ORDER BY `resourcetimestampTimestamp` DESC, `resourcetimestampTimestampAdd` DESC, `resourcetimestampId` DESC
        ");

        $nbResource = $db->numRows($rsResource);

        // List of news of the website
        $rsNews = $db->query("
        	SELECT `newsId`, `newsTimestamp`
        	FROM `" . $config->WIKINDX_DB_TABLEPREFIX . "news`
        	ORDER BY `newsTimestamp` DESC, `newsId` DESC
        ");

        $nbNews = $db->numRows($rsNews);

        // If no information are available, return anly the XML header
        // Otherwise return a sitemapindex or a specific sitemap according to the sitemap flags in parameters
        if ($nbResource + $nbNews > 0)
        {

            // Return an index of Sitemaps if no flags have been through
            if ($resourcepage == 0 && $newspage == 0)
            {
                $output .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

                // Add sitemaps for resources if any
                if ($nbResource > 0)
                {
                    $nbResourcePage = 0;

                    // Add one page if there are a rest for the euclidean division of the total number of url
                    if ($nbResource % WIKINDX_SITEMAP_MAXSIZE > 0)
                    {
                        $nbResourcePage = 1;
                        $numResults = $nbResource - ($nbResource % WIKINDX_SITEMAP_MAXSIZE);
                    }

                    // Add one page for each block of WIKINDX_SITEMAP_MAXSIZE urls
                    $nbResourcePage += $nbResource / WIKINDX_SITEMAP_MAXSIZE;

                    for ($p = 1; $p <= $nbResourcePage; $p++)
                    {
                        // Get the first resource of the block for its date of modification
                        $firstUrl = $this->firstResourcePageEntry($p);
                        $db->goToRow($rsResource, $firstUrl);
                        $resource = $db->fetchRow($rsResource);

                        $output .= '<sitemap>';
                        $output .= '<loc>' . $baseURL . WIKINDX_SITEMAP_PAGE . '&amp;resourcepage=' . $p . '</loc>';
                        $output .= '<lastmod>' . date('c', strtotime($resource['resourcetimestampTimestamp'])) . '</lastmod>';
                        $output .= '</sitemap>';
                    }
                }

                // Add a sitemap for news if any
                if ($nbNews > 0)
                {
                    // Get the first news for its date of modification
                    $news = $db->fetchRow($rsNews);

                    $output .= '<sitemap>';
                    $output .= '<loc>' . $baseURL . WIKINDX_SITEMAP_PAGE . '&amp;newspage=1</loc>';
                    $output .= '<lastmod>' . date('c', strtotime($news['newsTimestamp'])) . '</lastmod>';
                    $output .= '</sitemap>';
                }

                $output .= '</sitemapindex>';
            }

            // Return a sitemap of website news if newspage flag is on
            elseif ($newspage > 0)
            {
                $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

                while ($news = $db->fetchRow($rsNews))
                {
                    $output .= '<url>';
                    $output .= '<loc>' . $baseURL . '/index.php?action=news_NEWS_CORE&amp;method=viewNewsItem&amp;id=' . $news['newsId'] . '</loc>';
                    $output .= '<lastmod>' . date('c', strtotime($news['newsTimestamp'])) . '</lastmod>';
                    $output .= '<priority>0.9</priority>';
                    $output .= '</url>';
                }

                $output .= '</urlset>';
            }

            // Return a sitemap according to its resource page number
            elseif ($resourcepage > 0)
            {
                $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

                $firstUrl = $this->firstResourcePageEntry($resourcepage);
                $lastUrl = $this->lastResourcePageEntry($resourcepage);

                // Go to the first URL of the page and decrement r to begin on firstUrl limit
                $db->goToRow($rsResource, $firstUrl);
                $r = $firstUrl - 1;

                while ($resource = $db->fetchRow($rsResource))
                {
                    $r++;

                    if ($r > $lastUrl)
                    {
                        break;
                    }

                    $output .= '<url>';
                    $output .= '<loc>' . $baseURL . '/index.php?action=resource_RESOURCEVIEW_CORE&amp;id=' . $resource['resourcetimestampId'] . '</loc>';
                    $output .= '<lastmod>' . date('c', strtotime($resource['resourcetimestampTimestamp'])) . '</lastmod>';
                    $output .= '<priority>1.0</priority>';
                    $output .= '</url>';
                }

                $output .= '</urlset>';
            }
        }

        GLOBALS::addTplVar('content', $output);

        FACTORY_CLOSERAW::getInstance();
    }

    /**
     * Function to query the database and return formatted entries
     *
     * @param object $db
     * @param int $WIKINDX_RSS_LIMIT
     * @param string $WIKINDX_RSS_BIBSTYLE
     *
     * @return array ($numResults, $item)
     */
    private function queryDb($db, $WIKINDX_RSS_LIMIT, $WIKINDX_RSS_BIBSTYLE)
    {
        $listFields = ['resourceId', 'creatorSurname', 'resourceType', 'resourceTitle', 'resourceSubtitle', 'resourceShortTitle',
            'resourceTransTitle', 'resourceTransSubtitle', 'resourceTransShortTitle', 'resourceField1', 'resourceField2', 'resourceField3',
            'resourceField4', 'resourceField5', 'resourceField6', 'resourceField7', 'resourceField8', 'resourceField9', 'resourceNoSort',
            'resourceTransNoSort', 'resourceIsbn', 'resourceBibtexKey', 'resourceDoi', 'resourcetextId', 'resourcetextNote', 'resourcetextAbstract',
            'resourcetextUrls', 'resourcetextUrlText', 'resourcetextEditUserIdNote', 'resourcetextAddUserIdNote', 'resourcetextEditUserIdAbstract',
            'resourcetextAddUserIdAbstract', 'resourceyearId', 'resourceyearYear1', 'resourceyearYear2', 'resourceyearYear3', 'resourceyearYear4',
            'resourcepageId', 'resourcepagePageStart', 'resourcepagePageEnd', 'resourcesummaryId', 'resourcetimestampId',
            'resourcetimestampTimestamp', 'resourcetimestampTimestampAdd', 'publisherId', 'publisherName',
            'publisherLocation', 'publisherType', 'collectionId', 'collectionTitle', 'collectionTitleShort', 'collectionType', 'usersId', 'usersUsername',
            'usersFullname', 'resourcemiscId', 'resourcemiscCollection', 'resourcemiscPublisher', 'resourcemiscField1', 'resourcemiscField2',
            'resourcemiscField3', 'resourcemiscField4', 'resourcemiscField5', 'resourcemiscField6', 'resourcemiscTag', 'resourcemiscAddUserIdResource',
            'resourcemiscEditUserIdResource', 'resourcemiscAccesses', 'resourcemiscMaturityIndex', 'resourcemiscPeerReviewed', 'resourcemiscQuarantine',
            'resourcemiscAccessesPeriod', ];
        $messages = FACTORY_MESSAGES::getInstance();
        $session = FACTORY_SESSION::getInstance();
        $session->setVar('setup_Style', $WIKINDX_RSS_BIBSTYLE);
        $bibStyle = FACTORY_BIBSTYLE::getInstance();
        $db->ascDesc = $db->desc;
        $db->limit($WIKINDX_RSS_LIMIT, 0);
        $db->groupBy(['resourcetimestampId', 'resourcetimestampTimestamp']);
        $db->orderBy('resourcetimestampTimestamp', TRUE, FALSE);
        $subQuery = $db->subQuery($db->queryNoExecute($db->selectNoExecute(
            'resource_timestamp',
            [['resourcetimestampId' => 'rId']]
        )), 't1');
        $db->ascDesc = $db->desc;
        $db->orderBy($db->formatFields('resourcetimestampTimestamp'), FALSE, FALSE);
        $db->orderBy($db->ifClause(
            $db->formatFields('creatorSurname'),
            'IS NOT NULL',
            $db->formatFields('creatorSurname'),
            $db->formatFields('resourceTitleSort')
        ), FALSE);
        $db->orderBy($db->caseWhen(
            '(' . $db->formatFields('resourceType'),
            $db->equal .
            $db->tidyInput('book') . $db->or .
            $db->formatFields('resourceType') . $db->equal . $db->tidyInput('book_article') . ')' .
            $db->and . $db->formatFields('resourceyearYear2') . ' IS NOT NULL ',
            $db->formatFields('resourceyearYear2'),
            $db->formatFields('resourceyearYear1'),
            FALSE
        ), FALSE);
        $db->orderBy('resourceTitleSort', TRUE, FALSE);
        $db->leftJoin([['resource_timestamp' => 't2']], 't2.resourcetimestampId', 't1.rId');
        $db->leftJoin('resource_misc', 'resourcemiscId', 't2.resourcetimestampId');
        $db->leftJoin('resource', 'resourceId', 't2.resourcetimestampId');
        $db->leftJoin('resource_creator', 'resourcecreatorResourceId', 't2.resourcetimestampId');
        $db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $db->leftJoin('resource_year', 'resourceyearId', 't2.resourcetimestampId');
        $db->leftJoin('resource_text', 'resourcetextId', 't2.resourcetimestampId');
        $db->leftJoin('resource_page', 'resourcepageId', 't2.resourcetimestampId');
        $db->leftJoin('resource_summary', 'resourcesummaryId', 't2.resourcetimestampId');
        $db->leftJoin('publisher', 'resourcemiscPublisher', 'publisherId');
        $db->leftJoin('collection', 'resourcemiscCollection', 'collectionId');
        $db->leftJoin('users', 'usersId', $db->caseWhen(
            'resourcemiscEditUserIdResource',
            'IS NOT NULL',
            'resourcemiscEditUserIdResource',
            'resourcemiscAddUserIdResource'
        ), FALSE);
        foreach ($listFields as $field)
        {
            if ($field == 'resourcetimestampId')
            {
                $listFields[] = 't2.' . $field;
            }
            else
            {
                $listFields[] = $field;
            }
        }
        $resultSet = $db->query($db->selectNoExecuteFromSubQuery(
            FALSE,
            $db->formatFields($listFields),
            $subQuery,
            FALSE,
            FALSE
        ));


        $numResults = $db->numRows($resultSet);
        $x = 0;
        $item = [];
        while ($list_results = $db->fetchRow($resultSet))
        {
            /** construct a hierarchial array for the item node */
            $item['title'][$x] = $messages->text('resourceType', $list_results['resourceType']) . ': ';
            if ($list_results['resourceNoSort'])
            {
                $item['title'][$x] .= $list_results['resourceNoSort'] . ' ';
            }
            $item['title'][$x] .= $list_results['resourceTitle'];

            $item['timestamp'][$x] = $list_results['resourcetimestampTimestamp'];

            list($item['addUser'][$x], $item['editUser'][$x]) =
                $this->getUser($db, $list_results['resourcemiscAddUserIdResource'], $list_results['resourcemiscEditUserIdResource']);

            $item['link'][$x] = $list_results['resourceId'];
            $item['description'][$x] = $bibStyle->process($list_results);

            $x++;
        }

        return [$numResults, $item];
    }

    /**
     * Return the first number in recordset of a page of resource URLs
     *
     * @param int $numPage
     */
    private function firstResourcePageEntry($numPage)
    {
        return $numPage * WIKINDX_SITEMAP_MAXSIZE - WIKINDX_SITEMAP_MAXSIZE;
    }

    /**
     * Return the last number in recordset of a page of resource URLs
     *
     * @param int $numPage
     */
    private function lastResourcePageEntry($numPage)
    {
        return $numPage * WIKINDX_SITEMAP_MAXSIZE - 1;
    }
}
