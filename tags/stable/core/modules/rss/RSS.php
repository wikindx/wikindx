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
 * Based upon work by Laure Endrizzi October 2005
 *
 * @package wikindx\core\modules\rss
 */
class RSS
{
    // Constructor
    public function init()
    {
        $db = FACTORY_DB::getInstance();
        if (!WIKINDX_RSS_ALLOW) {
            header('HTTP/1.0 403 Forbidden');
            die("Access forbidden: this feature is disabled.");
        }

        $baseURL = WIKINDX_BASE_URL;

        // set up language
        $messages = FACTORY_MESSAGES::getInstance(WIKINDX_LANGUAGE);

        list($numResults, $item) = $this->queryDb($db, WIKINDX_RSS_LIMIT, WIKINDX_RSS_BIBSTYLE);


        /** declare RSS content type */
        header('Content-type: ' . WIKINDX_MIMETYPE_RSS . '; charset=' . WIKINDX_CHARSET);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-Requested-With");

        /** print the XML/RSS headers */
        echo '<?xml version="1.0" encoding="UTF-8" ?>' . LF;
        echo '<rss version="2.0">' . LF;

        /** print channel data */
        echo TAB . "<channel>" . LF;
        echo TAB . "<link>" . $this->escape_xml($baseURL) . "</link>" . LF;
        echo TAB . TAB . "<title>" . $this->escape_xml(WIKINDX_RSS_TITLE) . "</title>" . LF;
        echo TAB . TAB . "<description>" . $this->escape_xml(WIKINDX_RSS_DESCRIPTION) . "</description>" . LF;
        echo TAB . TAB . "<language>" . $this->escape_xml(WIKINDX_LANGUAGE) . "</language>" . LF;

        // Extract the date of the last updated resource or use the date of the current date
        // for the date of last build of the feed
        if ($numResults > 0) {
            $DateMax = date_create('1854-12-08');

            for ($i = 0; $i < $numResults; $i++) {
                if (mb_strlen($item['timestamp'][$i]) > 0) {
                    $datetime2 = date_create($item['timestamp'][$i]);
                    if ($datetime2 > $DateMax) {
                        $DateMax = $datetime2;
                    }
                }
            }

            $channel['lastBuildDate'] = $DateMax->format(DateTime::RFC822);
        } else {
            $channel['lastBuildDate'] = date(DateTime::RFC822);
        }

        echo TAB . TAB . "<lastBuildDate>" . $this->escape_xml($channel['lastBuildDate']) . "</lastBuildDate>" . LF;

        if ($numResults > 0) {
            for ($i = 0; $i < $numResults; $i++) {
                /**
                 * loop thru the item array
                 * print item data
                 */
                $description = FALSE;
                echo TAB . TAB . "<item>" . LF;

                if (mb_strlen($item['title'][$i]) > 0) {
                    echo TAB . TAB . TAB . "<title>" . $this->escape_xml($item['title'][$i]) . "</title>" . LF;
                }

                if (mb_strlen($item['timestamp'][$i]) > 0) {
                    echo TAB . TAB . TAB . "<pubDate>" . date(DateTime::RFC822, strtotime($item['timestamp'][$i])) . "</pubDate>" . LF;
                }

                if (mb_strlen($item['link'][$i]) > 0) {
                    if (WIKINDX_DENY_READONLY) {
                        $ItemUrl = $baseURL . "/?action=logout";
                    } else {
                        $ItemUrl = $baseURL . "/?method=RSS&amp;action=resource_RESOURCEVIEW_CORE&amp;id=" . $item['link'][$i];
                    }

                    echo TAB . TAB . TAB . "<link>" . $ItemUrl . "</link>" . LF;
                    echo TAB . TAB . TAB . "<guid isPermaLink=\"false\">" . $ItemUrl . "</guid>" . LF;
                }

                if (mb_strlen($item['editUser'][$i]) > 0) {
                    echo TAB . TAB . TAB . "<author>" . $this->escape_xml($item['editUser'][$i]) . "</author>" . LF;
                } elseif (mb_strlen($item['addUser'][$i]) > 0) {
                    echo TAB . TAB . TAB . "<author>" . $this->escape_xml($item['addUser'][$i]) . "</author>" . LF;
                }

                if (mb_strlen($item['description'][$i]) > 0) {
                    echo TAB . TAB . TAB . "<description>" . $this->escape_xml($item['description'][$i]) . "</description>" . LF;
                }

                echo TAB . TAB . "</item>" . LF;
            }
        }

        echo TAB . TAB . "</channel>" . LF;
        echo "</rss>" . LF;

        FACTORY_CLOSERAW::getInstance();
    }

    /**
     * Function to query the database and return formatted entries
     *
     * @param object $db
     * @param int $WIKINDX_RSS_LIMIT
     * @param string $bibstyle
     * @param mixed $limit
     *
     * @return array ($numResults, $item)
     */
    private function queryDb($db, $limit, $bibstyle)
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
            'resourcemiscEditUserIdResource', 'resourcemiscMaturityIndex', 'resourcemiscPeerReviewed', 'resourcemiscQuarantine', ];
        $messages = FACTORY_MESSAGES::getInstance();
        $session = FACTORY_SESSION::getInstance();
        $session->setVar("setup_Style", $bibstyle);
        $bibStyle = FACTORY_BIBSTYLE::getInstance();
        if (!WIKINDX_RSS_DISPLAY) { // display only added resources
            $db->formatConditions($db->formatFields('resourcetimestampTimestampAdd') .
                $db->equal . $db->formatFields('resourcetimestampTimestamp'));
        }
        $db->ascDesc = $db->desc;
        $db->limit($limit, 0);
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
        foreach ($listFields as $field) {
            if ($field == 'resourcetimestampId') {
                $listFields[] = 't2.' . $field;
            } else {
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
        while ($list_results = $db->fetchRow($resultSet)) {
            /** construct a hierarchial array for the item node */
            $item['title'][$x] = $messages->text('resourceType', $list_results['resourceType']) . ': ';
            if ($list_results['resourceNoSort']) {
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
     * Function to grab add/edit full names or, if no full names, the user names.
     *
     * @param object $db
     * @param int $addId
     * @param int $editId
     *
     * @return array ($add, $edit)
     */
    private function getUser($db, $addId, $editId)
    {
        $add = $edit = FALSE;
        if ($addId) {
            $db->formatConditions(['usersId' => $addId]);
            $resultSet = $db->select('users', ['usersUsername', 'usersFullname']);
            $row = $db->fetchRow($resultSet);
            if ($row['usersFullname']) {
                $add = $row['usersFullname'];
            } elseif ($row['usersUsername']) {
                $add = $row['usersUsername'];
            }
        }
        if ($editId) {
            $db->formatConditions(['usersId' => $editId]);
            $resultSet = $db->select('users', ['usersUsername', 'usersFullname']);
            $row = $db->fetchRow($resultSet);
            if ($row['usersFullname']) {
                $edit = $row['usersFullname'];
            } elseif ($row['usersUsername']) {
                $edit = $row['usersUsername'];
            }
        }

        return [$add, $edit];
    }

    /**
     * Function to escape strings in XML tags
     *
     * @param string $s
     *
     * @return string
     */
    private function escape_xml($s)
    {
        return htmlspecialchars($s, ENT_XML1 || ENT_QUOTES);
    }
}
