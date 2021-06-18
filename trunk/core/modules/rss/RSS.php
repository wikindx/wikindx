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
 * RSS
 *
 * Syndication feed (RSS 2.0/Atom 1.0)
 *
 * @package wikindx\core\modules\rss
 */
class RSS
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // The syndication is disallowed on feature level (valid for all formats)
        if (WIKINDX_RSS_DISALLOW)
        {
            header('HTTP/1.0 403 Forbidden');
            die("Access forbidden: this feature is disabled.");
        }
    }
    
    /**
     * Default init method
     *
     * Catch the requests send by a client without a specific format or via an old URL
     * Try to send a feed formatted with the best format accepted by the client
     */
    public function init()
    {
        $accept = $_SERVER["HTTP_ACCEPT"] ?? "";
        if (stripos($accept, WIKINDX_MIMETYPE_ATOM) !== FALSE)
            $this->atom10();
        else
            $this->rss20();
    }
    
    /*
     * Output the syndication feed in RSS 2.0 format
     *
     * @see https://validator.w3.org/feed/docs/rss2.html
     */
    public function rss20()
    {
        $item = $this->queryDb();
        $numResults = count($item["title"] ?? []);

        // The tag language of RSS use an hyphen
        // cf. https://datatracker.ietf.org/doc/html/rfc3066
        $lang = str_replace("_", "-", WIKINDX_LANGUAGE);

        /** declare RSS content type */
        header('Content-type: ' . WIKINDX_MIMETYPE_RSS . '; charset=' . WIKINDX_CHARSET);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-Requested-With");

        /** print the XML/RSS headers */
        echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>' . LF;
        // xmlns:atom is a non standard extension recommended by the W3C RSS Validator <https://validator.w3.org/feed/>
        echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . LF;

        /** print channel data */
        echo TAB . "<channel>" . LF;
        // <link rel='self' ... /> is a non standard extension recommended by the W3C RSS Validator <https://validator.w3.org/feed/>
        echo TAB . TAB . "<link rel='self' type='" . WIKINDX_MIMETYPE_RSS . "' href='" . $this->escape_xml(WIKINDX_URL_BASE . ($_SERVER["REQUEST_URI"] ?? WIKINDX_RSS_PAGE)) . "' />" . LF;
        echo TAB . TAB . "<link>" . $this->escape_xml(WIKINDX_URL_BASE) . "</link>" . LF;
        echo TAB . TAB . "<title>" . $this->escape_xml(WIKINDX_RSS_TITLE) . "</title>" . LF;
        echo TAB . TAB . "<description>" . $this->escape_xml(WIKINDX_RSS_DESCRIPTION) . "</description>" . LF;
        echo TAB . TAB . "<language>" . $this->escape_xml($lang) . "</language>" . LF;

        // Extract the date of the last updated resource or use the date of the current date
        // for the date of last build of the feed
        if ($numResults > 0)
        {
            $DateMax = date_create('1854-12-08');

            for ($i = 0; $i < $numResults; $i++)
            {
                if (mb_strlen($item['timestampEdit'][$i]) > 0)
                {
                    $datetime2 = date_create($item['timestampEdit'][$i]);
                    if ($datetime2 > $DateMax)
                    {
                        $DateMax = $datetime2;
                    }
                }
            }

            $channel['lastBuildDate'] = $DateMax->format(DateTime::RSS);
        }
        else
        {
            $channel['lastBuildDate'] = date(DateTime::RSS);
        }

        echo TAB . TAB . "<lastBuildDate>" . $this->escape_xml($channel['lastBuildDate']) . "</lastBuildDate>" . LF;

        if ($numResults > 0)
        {
            for ($i = 0; $i < $numResults; $i++)
            {
                /**
                 * loop through the array of resources
                 * print resource data
                 */
                $description = FALSE;
                echo TAB . TAB . "<item>" . LF;

                if (mb_strlen($item['title'][$i]) > 0)
                {
                    echo TAB . TAB . TAB . "<title>" . $this->escape_xml($item['title'][$i]) . "</title>" . LF;
                }

                if (mb_strlen($item['timestampEdit'][$i]) > 0)
                {
                    echo TAB . TAB . TAB . "<pubDate>" . date(DateTime::RSS, strtotime($item['timestampEdit'][$i])) . "</pubDate>" . LF;
                }

                if (mb_strlen($item['link'][$i]) > 0)
                {
                    if (WIKINDX_DENY_READONLY)
                    {
                        $ItemUrl = WIKINDX_URL_BASE . "/?action=logout";
                    }
                    else
                    {
                        $ItemUrl = WIKINDX_URL_BASE . "/?method=RSS&amp;action=resource_RESOURCEVIEW_CORE&amp;id=" . $item['link'][$i];
                    }

                    echo TAB . TAB . TAB . "<link>" . $ItemUrl . "</link>" . LF;
                    echo TAB . TAB . TAB . "<guid isPermaLink=\"false\">" . $ItemUrl . "</guid>" . LF;
                }

                // NB: In RSS spec, the author MUST be an email and a name in parenthesis but we don't want to expose users emails
                if (mb_strlen($item['editUser'][$i]) > 0)
                {
                    echo TAB . TAB . TAB . "<author>" . $this->escape_xml($item['editUser'][$i]) . "</author>" . LF;
                }
                elseif (mb_strlen($item['addUser'][$i]) > 0)
                {
                    echo TAB . TAB . TAB . "<author>" . $this->escape_xml($item['addUser'][$i]) . "</author>" . LF;
                }

                if (mb_strlen($item['description'][$i]) > 0)
                {
                    echo TAB . TAB . TAB . "<description>" . $this->escape_xml($item['description'][$i]) . "</description>" . LF;
                }

                echo TAB . TAB . "</item>" . LF;
            }
        }

        echo TAB . "</channel>" . LF;
        echo "</rss>" . LF;

        FACTORY_CLOSERAW::getInstance();
    }
    
    /*
     * Output the syndication feed in Atom 1.0 format
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4287
     */
    public function atom10()
    {
        $item = $this->queryDb();
        $numResults = count($item["title"] ?? []);

        // The tag language of Atom use an hyphen
        // cf. https://datatracker.ietf.org/doc/html/rfc3066
        $lang = str_replace("_", "-", WIKINDX_LANGUAGE);

        /** declare Atom content type */
        header('Content-type: ' . WIKINDX_MIMETYPE_ATOM . '; charset=' . WIKINDX_CHARSET);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-Requested-With");

        /** print the XML/RSS headers */
        echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>' . LF;
        echo '<feed xmlns="http://www.w3.org/2005/Atom">' . LF;

        /** print feed metadata */
        // The id is an artificial unique identifier (IRI) that MUST never change
        echo TAB . "<id>" . $this->escape_xml(WIKINDX_URL_BASE) . "/general" . "</id>" . LF;
        echo TAB . "<link rel='self' type='" . WIKINDX_MIMETYPE_ATOM . "' href='" . $this->escape_xml(WIKINDX_URL_BASE . ($_SERVER["REQUEST_URI"] ?? WIKINDX_ATOM_PAGE)) . "' />" . LF;
        echo TAB . "<title type='text'>" . $this->escape_xml(WIKINDX_RSS_TITLE) . "</title>" . LF;
        echo TAB . "<subtitle type='text'>" . $this->escape_xml(WIKINDX_RSS_DESCRIPTION) . "</subtitle>" . LF;
        
        // Extract the date of the last updated resource or use the date of the current date
        // for the date of last build of the feed
        if ($numResults > 0)
        {
            $DateMax = date_create('1854-12-08');

            for ($i = 0; $i < $numResults; $i++)
            {
                if (mb_strlen($item['timestampEdit'][$i]) > 0)
                {
                    $datetime2 = date_create($item['timestampEdit'][$i]);
                    if ($datetime2 > $DateMax)
                    {
                        $DateMax = $datetime2;
                    }
                }
            }

            $channel['lastBuildDate'] = $DateMax->format(DateTime::ATOM);
        }
        else
        {
            $channel['lastBuildDate'] = date(DateTime::ATOM);
        }

        echo TAB . "<updated>" . $this->escape_xml($channel['lastBuildDate']) . "</updated>" . LF;

        if ($numResults > 0)
        {
            for ($i = 0; $i < $numResults; $i++)
            {
                /**
                 * loop through the array of resources
                 * print resource data
                 */
                $description = FALSE;
                echo TAB . "<entry>" . LF;
                

                if (mb_strlen($item['title'][$i]) > 0)
                {
                    echo TAB . TAB . "<title type='html'>" . $this->escape_xml($item['title'][$i]) . "</title>" . LF;
                }

                if (mb_strlen($item['timestampEdit'][$i]) > 0)
                {
                    echo TAB . TAB . "<updated>" . date(DateTime::ATOM, strtotime($item['timestampEdit'][$i])) . "</updated>" . LF;
                }

                if (mb_strlen($item['timestampAdd'][$i]) > 0)
                {
                    echo TAB . TAB . "<published>" . date(DateTime::ATOM, strtotime($item['timestampAdd'][$i])) . "</published>" . LF;
                }

                if (mb_strlen($item['link'][$i]) > 0)
                {
                    if (WIKINDX_DENY_READONLY)
                    {
                        $ItemUrl = WIKINDX_URL_BASE . "/?action=logout";
                    }
                    else
                    {
                        $ItemUrl = WIKINDX_URL_BASE . "/?method=RSS&amp;action=resource_RESOURCEVIEW_CORE&amp;id=" . $item['link'][$i];
                    }

                    echo TAB . TAB . "<link rel='alternate' type='text/html' href='" . $ItemUrl . "' />" . LF;
                    // The id is an artificial unique identifier (IRI) that MUST never change
                    echo TAB . TAB . "<id>" . $this->escape_xml(WIKINDX_URL_BASE) . "/resource/" . $item['link'][$i] . "</id>" . LF;
                }

                if (mb_strlen($item['editUser'][$i]) > 0)
                {
                    echo TAB . TAB . "<author><name>" . $this->escape_xml($item['editUser'][$i]) . "</name></author>" . LF;
                }
                elseif (mb_strlen($item['addUser'][$i]) > 0)
                {
                    echo TAB . TAB . "<author><name>" . $this->escape_xml($item['addUser'][$i]) . "</name></author>" . LF;
                }

                if (mb_strlen($item['description'][$i]) > 0)
                {
                    echo TAB . TAB . "<content type='html' xml:lang='" . $lang . "'>" . $this->escape_xml($item['description'][$i]) . "</content>" . LF;
                }

                echo TAB . "</entry>" . LF;
            }
        }

        echo "</feed>" . LF;

        FACTORY_CLOSERAW::getInstance();
    }

    /**
     * Function to query the database and return formatted resources
     *
     * @return array
     */
    private function queryDb()
    {
        $db = FACTORY_DB::getInstance();
        $messages = FACTORY_MESSAGES::getInstance();
        $session = FACTORY_SESSION::getInstance();
        $bibStyle = FACTORY_BIBSTYLE::getInstance();
        
        $listFields = ['resourceId', 'creatorSurname', 'resourceType', 'resourceTitle', 'resourceSubtitle', 'resourceShortTitle',
            'resourceTransTitle', 'resourceTransSubtitle', 'resourceTransShortTitle', 'resourceField1', 'resourceField2', 'resourceField3',
            'resourceField4', 'resourceField5', 'resourceField6', 'resourceField7', 'resourceField8', 'resourceField9', 'resourceNoSort',
            'resourceTransNoSort', 'resourceIsbn', 'resourceBibtexKey', 'resourceDoi', 'resourcetextId', 'resourcetextNote', 'resourcetextAbstract',
            'resourcetextEditUserIdNote', 'resourcetextAddUserIdNote', 'resourcetextEditUserIdAbstract',
            'resourcetextAddUserIdAbstract', 'resourceyearId', 'resourceyearYear1', 'resourceyearYear2', 'resourceyearYear3', 'resourceyearYear4',
            'resourcepageId', 'resourcepagePageStart', 'resourcepagePageEnd', 'resourcetimestampId',
            'resourcetimestampTimestamp', 'resourcetimestampTimestampAdd', 'publisherId', 'publisherName',
            'publisherLocation', 'publisherType', 'collectionId', 'collectionTitle', 'collectionTitleShort', 'collectionType', 'usersId', 'usersUsername',
            'usersFullname', 'resourcemiscId', 'resourcemiscCollection', 'resourcemiscPublisher', 'resourcemiscField1', 'resourcemiscField2',
            'resourcemiscField3', 'resourcemiscField4', 'resourcemiscField5', 'resourcemiscField6', 'resourcemiscTag', 'resourcemiscMetadata', 'resourcemiscAddUserIdResource',
            'resourcemiscEditUserIdResource', 'resourcemiscMaturityIndex', 'resourcemiscPeerReviewed', 'resourcemiscQuarantine', ];
        
        $db->ascDesc = $db->desc;
        $db->limit(WIKINDX_RSS_LIMIT, 0);
        $db->groupBy(['resourcetimestampId', 'resourcetimestampTimestamp']);
        $db->orderBy(WIKINDX_RSS_DISPLAY_EDITED_RESOURCES ? 'resourcetimestampTimestamp' : 'resourcetimestampTimestampAdd', TRUE, FALSE);
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

            $item['timestampEdit'][$x] = $list_results['resourcetimestampTimestamp'];

            $item['timestampAdd'][$x] = $list_results['resourcetimestampTimestampAdd'];

            list($item['addUser'][$x], $item['editUser'][$x]) =
                $this->getUser($db, $list_results['resourcemiscAddUserIdResource'], $list_results['resourcemiscEditUserIdResource']);

            $item['link'][$x] = $list_results['resourceId'];
            $item['description'][$x] = "<p>" . $bibStyle->process($list_results) . "</p>";
            
            $list_results['resourcetextAbstract'] = trim($list_results['resourcetextAbstract']);
            if ($list_results['resourcetextAbstract'] != "")
            {
                $item['description'][$x] .= LF.LF;
                $item['description'][$x] .= "<p><strong>Abstract</strong></p>";
                $item['description'][$x] .= LF.LF;
                if (!\UTILS\matchPrefix($list_results['resourcetextAbstract'], "<p>"))
                {
                    $list_results['resourcetextAbstract'] = "<p>" . $list_results['resourcetextAbstract'] . "</p>";
                }
                $item['description'][$x] .= $list_results['resourcetextAbstract'];
            }

            $x++;
        }

        return $item;
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

        $db->formatConditionsOneField([$addId, $editId], 'usersId');
        $resultSet = $db->select('users', ['usersId', 'usersUsername', 'usersFullname']);
        while ($row = $db->fetchRow($resultSet))
        {
            $name = $row['usersFullname'] ? $row['usersFullname'] : $row['usersUsername'];
            if ($row['usersId'] == $addId)
            {
                $add = $name;
            }
            if ($row['usersId'] == $editId)
            {
                $edit = $name;
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
