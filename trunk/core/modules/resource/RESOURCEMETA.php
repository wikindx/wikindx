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
 * RESOURCEMETA class
 *
 * Deal with resource's quotes, paraphrases and musings
 */
class RESOURCEMETA
{
    private $db;
    private $vars;
    private $session;
    private $messages;
    private $user;
    private $icons;
    private $common;
    private $cite;
    private $quote = [];
    private $paraphrase = [];
    private $musing = [];
    private $userId;
    private $browserTabID = FALSE;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->common = FACTORY_RESOURCECOMMON::getInstance();
        $this->cite = FACTORY_CITE::getInstance();
        $this->userId = $this->session->getVar("setup_UserId");
        $this->browserTabID = GLOBALS::getBrowserTabID();
    }
    /**
     * Display resource's quotes
     *
     * @param array $row
     *
     * @return array
     */
    public function viewQuotes($row)
    {
        $write = $this->session->getVar("setup_Write") ? TRUE : FALSE;
        $this->db->formatConditions(['resourcemetadataResourceId' => $row['resourceId']]);
        $this->db->formatConditions($this->db->formatFields('resourcemetadataType') . $this->db->equal . $this->db->tidyInput('q'));
        $this->db->orderBy($this->db->tidyInputClause('resourcemetadataPageStart') . '+0', FALSE, FALSE);
        $recordset = $this->db->select(
            'resource_metadata',
            ['resourcemetadataId', 'resourcemetadataPageStart', 'resourcemetadataPageEnd',
                'resourcemetadataParagraph', 'resourcemetadataSection', 'resourcemetadataChapter',
                'resourcemetadataText', 'resourcemetadataAddUserId', ]
        );
        $numRows = $this->db->numRows($recordset);
        if (!$numRows && !$write)
        {
            return [];
        }
        if ($write)
        {
            $this->quote['editLink'] = \HTML\a(
                $this->icons->getClass("add"),
                $this->icons->getHTML("add"),
                "index.php?action=resource_RESOURCEQUOTE_CORE&method=quoteEdit" . htmlentities("&resourceId=" . $row['resourceId']
                 . '&browserTabID=' . $this->browserTabID)
            );
        }
        $this->view($row['resourceId'], $recordset, 'quote');

        return $this->quote;
    }
    /**
     * Display resource's paraphrases
     *
     * @param array $row
     *
     * @return array
     */
    public function viewParaphrases($row)
    {
        $write = $this->session->getVar("setup_Write") ? TRUE : FALSE;
        $this->db->formatConditions(['resourcemetadataResourceId' => $row['resourceId']]);
        $this->db->formatConditions($this->db->formatFields('resourcemetadataType') . $this->db->equal . $this->db->tidyInput('p'));
        $this->db->orderBy($this->db->tidyInputClause('resourcemetadataPageStart') . '+0', FALSE, FALSE);
        $recordset = $this->db->select(
            'resource_metadata',
            ['resourcemetadataId', 'resourcemetadataPageStart', 'resourcemetadataPageEnd',
                'resourcemetadataParagraph', 'resourcemetadataSection', 'resourcemetadataChapter',
                'resourcemetadataText', 'resourcemetadataAddUserId', ]
        );
        $numRows = $this->db->numRows($recordset);
        if (!$numRows && !$write)
        {
            return [];
        }
        if ($write)
        {
            $this->paraphrase['editLink'] = \HTML\a(
                $this->icons->getClass("add"),
                $this->icons->getHTML("add"),
                "index.php?action=resource_RESOURCEPARAPHRASE_CORE&method=paraphraseEdit" . htmlentities("&resourceId=" . $row['resourceId']
                 . '&browserTabID=' . $this->browserTabID)
            );
        }
        $this->view($row['resourceId'], $recordset, 'paraphrase');

        return $this->paraphrase;
    }
    /**
     * Display resource's musings
     *
     * @param array $row
     *
     * @return array
     */
    public function viewMusings($row)
    {
        $resourceId = $row['resourceId'];
        $write = $this->session->getVar("setup_Write") ? TRUE : FALSE;
        $this->db->formatConditions(['resourcemetadataResourceId' => $row['resourceId']]);
        $this->db->formatConditions($this->db->formatFields('resourcemetadataType') . $this->db->equal . $this->db->tidyInput('m'));
        $this->db->orderBy($this->db->tidyInputClause('resourcemetadataPageStart') . '+0', FALSE, FALSE);
        $recordset = $this->db->select(
            'resource_metadata',
            ['resourcemetadataId', 'resourcemetadataPageStart', 'resourcemetadataPageEnd',
                'resourcemetadataParagraph', 'resourcemetadataSection', 'resourcemetadataChapter', 'resourcemetadataPrivate',
                'resourcemetadataText', 'resourcemetadataAddUserId', 'resourcemetadataTimestamp', ]
        );
        $numRows = $this->db->numRows($recordset);
        if (!$numRows && !$write)
        {
            return [];
        }
        if ($write)
        {
            $this->musing['editLink'] = \HTML\a(
                $this->icons->getClass("add"),
                $this->icons->getHTML("add"),
                "index.php?action=resource_RESOURCEMUSING_CORE&method=musingEdit" . htmlentities("&resourceId=" . $row['resourceId']
                 . '&browserTabID=' . $this->browserTabID)
            );
        }
        $patterns = FALSE;
        $write = $this->session->getVar("setup_Write") ? TRUE : FALSE;
        $index = 0;
        if (array_key_exists("search", $this->vars) && $this->vars["search"] = 'highlight')
        {
            $searchTerms = \UTF8\mb_explode(",", $this->session->getVar("search_Highlight"));
            foreach ($searchTerms as $term)
            {
                $patterns[] = "/($term)(?!\\S*\" \\S*>)/i";
            }
        }
        $index = 0;
        while ($row = $this->db->fetchRow($recordset))
        {
            if (($row['resourcemetadataPrivate'] == 'Y') && ($this->userId != $row['resourcemetadataAddUserId']))
            {
                continue;
            }
            // If numeric, this comment may be viewed by members of user groups of which $row[$addUserId] is a member
            elseif (is_numeric($row['resourcemetadataPrivate']))
            {
                $this->db->formatConditions(['usergroupsusersUserId' => $this->userId]);
                $this->db->formatConditions(['usergroupsusersGroupId' => $row['resourcemetadataPrivate']]);
                $recordset2 = $this->db->select('user_groups_users', 'usergroupsusersId');
                if (!$this->db->numRows($recordset2))
                {
                    continue;
                }
            }
            $this->musing[$index]['details'] = $this->getDetails($row);
            $text = $this->cite->parseCitations(\HTML\nlToHtml($row['resourcemetadataText']), 'html');
            $this->musing[$index]['musing'] = $this->common->doHighlight($text);
            $this->musing[$index]['timestamp'] = $row['resourcemetadataTimestamp'];
            $this->db->formatConditions(['resourcekeywordMetadataId' => $row['resourcemetadataId']]);
            $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
            $recordset2 = $this->db->select('resource_keyword', ['keywordId', 'keywordKeyword', 'keywordGlossary']);
            while ($row2 = $this->db->fetchRow($recordset2))
            {
                $this->musing[$index]['keywordTitle'] = $this->messages->text("resources", "keywords");
                $this->musing[$index]['keywords'][] = \HTML\a(
                    "link",
                    \HTML\nlToHtml($row2['keywordKeyword']),
                    "index.php?action=list_LISTSOMERESOURCES_CORE" .
                    '&method=metaKeywordProcess' . htmlentities("&id=" . $row2['keywordId']),
                    "",
                    \HTML\dbToHtmlPopupTidy($row2['keywordGlossary'])
                );
            }
            $users = $this->user->displayUserAddEdit($row['resourcemetadataAddUserId'], TRUE, 'musing');
            $this->musing[$index]['userAdd'] = $users[0];
            if ($write && ($row['resourcemetadataAddUserId'] == $this->userId))
            {
                $this->musing[$index]['editLink'] = \HTML\a(
                    $this->icons->getClass("edit"),
                    $this->icons->getHTML("edit"),
                    "index.php?action=resource_RESOURCEMUSING_CORE&method=musingEdit" . htmlentities("&resourceId=" . $resourceId .
                	'&resourcemetadataId=' . $row['resourcemetadataId'] . '&browserTabID=' . $this->browserTabID)
                );
                
                $this->musing[$index]['editLink'] .= '&nbsp;' . \HTML\a(
                    $this->icons->getClass("delete"),
                    $this->icons->getHTML("delete"),
                    "index.php?action=resource_RESOURCEMUSING_CORE&method=deleteInit" .
                	htmlentities("&resourceId=" . $resourceId . "&resourcemetadataId=" . $row['resourcemetadataId'] . 
                	'&browserTabID=' . $this->browserTabID)
                );
            }
            $index++;
        }
        if (!empty($this->musing))
        {
            $this->musing['title'] = $this->messages->text("viewResource", "musings");
        }

        return $this->musing;
    }
    /**
     * Display quotes, paraphrases etc
     *
     * @param int $resourceId
     * @param array $recordset
     * @param string $type
     */
    private function view($resourceId, $recordset, $type)
    {
        if ($type == 'quote')
        {
            $this->quote['title'] = $this->messages->text("viewResource", "quotes");
        }
        else
        { // 'paraphrase'
            $this->paraphrase['title'] = $this->messages->text("viewResource", "paraphrases");
        }
        $patterns = FALSE;
        $action = $type == 'quote' ? 'quoteEdit' : 'paraphraseEdit';
        $phpFile = $type == 'quote' ? 'resource_RESOURCEQUOTE_CORE' : 'resource_RESOURCEPARAPHRASE_CORE';
        $write = $this->session->getVar("setup_Write") ? TRUE : FALSE;
        $index = 0;
        $thisUserId = $this->session->getVar("setup_UserId");
        if (array_key_exists("search", $this->vars) && $this->vars["search"] = 'highlight')
        {
            $searchTerms = \UTF8\mb_explode(",", $this->session->getVar("search_Highlight"));
            foreach ($searchTerms as $term)
            {
                $patterns[] = "/($term)(?!\\S*\" \\S*>)/i";
            }
        }
        $index = 0;
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->{$type}[$index]['metaId'] = $row['resourcemetadataId'];
            $this->{$type}[$index]['details'] = $this->getDetails($row);
            $this->{$type}[$index]['commentTitle'] = $this->messages->text("resources", "comment");
            $text = $this->cite->parseCitations(\HTML\nlToHtml($row['resourcemetadataText']), 'html');
            $this->{$type}[$index][$type] = $this->common->doHighlight($text);
            $this->db->formatConditions(['resourcekeywordMetadataId' => $row['resourcemetadataId']]);
            $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
            $recordset2 = $this->db->select('resource_keyword', ['keywordId', 'keywordKeyword', 'keywordGlossary']);
            while ($row2 = $this->db->fetchRow($recordset2))
            {
                $this->{$type}[$index]['keywordTitle'] = $this->messages->text("resources", "keywords");
                $this->{$type}[$index]['keywords'][] = \HTML\a(
                    "link",
                    \HTML\nlToHtml($row2['keywordKeyword']),
                    "index.php?action=list_LISTSOMERESOURCES_CORE" .
                    '&method=metaKeywordProcess' . htmlentities("&id=" . $row2['keywordId']). '&browserTabID=' . $this->browserTabID,
                    "",
                    \HTML\dbToHtmlPopupTidy($row2['keywordGlossary'])
                );
            }
            $users = $this->user->displayUserAddEdit($row['resourcemetadataAddUserId'], TRUE, $type);
            $this->{$type}[$index]['userAdd'] = $users[0];
            // check for comments
            $this->db->formatConditions(['resourcemetadataMetadataId' => $row['resourcemetadataId']]);
            if ($type == 'quote')
            {
                $this->db->formatConditions(['resourcemetadataType' => 'qc']);
            }
            else
            {
                $this->db->formatConditions(['resourcemetadataType' => 'pc']);
            }
            $this->db->orderBy('resourcemetadataTimestamp', TRUE, FALSE);
            $recordset2 = $this->db->select('resource_metadata', ['resourcemetadataText', 'resourcemetadataTimestamp',
                'resourcemetadataAddUserId', 'resourcemetadataPrivate', ]);
            if ($this->db->numRows($recordset2))
            {
                $index2 = 0;
                while ($rowComment = $this->db->fetchRow($recordset2))
                {
                    // Read only access
                    if ($this->session->getVar("setup_ReadOnly") &&
                        (($rowComment['resourcemetadataPrivate'] == 'Y') || ($rowComment['resourcemetadataPrivate'] == 'G')))
                    {
                        continue;
                    }
                    elseif (($rowComment['resourcemetadataPrivate'] == 'Y') &&
                        ($thisUserId != $rowComment['resourcemetadataAddUserId']))
                    {
                        continue;
                    }
                    // If 'G' or numeric, this comment may be viewed by members of user groups of which $row[$addUserId] is a member
                    elseif (is_numeric($rowComment['resourcemetadataPrivate']))
                    {
                        $this->db->formatConditions(['usergroupsusersUserId' => $this->userId]);
                        $this->db->formatConditions(['usergroupsusersGroupId' => $rowComment['resourcemetadataPrivate']]);
                        $recordset3 = $this->db->select('user_groups_users', 'usergroupsusersId');
                        if (!$this->db->numRows($recordset3))
                        {
                            continue;
                        }
                    }
                    // Else, comment is public
                    $text = $this->cite->parseCitations(\HTML\nlToHtml($rowComment['resourcemetadataText']), 'html');
                    $users = $this->user->displayUserAddEdit($rowComment['resourcemetadataAddUserId'], TRUE, 'comment');
                    $this->{$type}[$index]['comments'][$index2]['userAdd'] = $users[0];
                    $this->{$type}[$index]['comments'][$index2]['comment'] = $this->common->doHighlight($text);
                    $this->{$type}[$index]['comments'][$index2]['timestamp'] = $rowComment['resourcemetadataTimestamp'];
                    $index2++;
                }
            }
            if ($write)
            {
                $this->{$type}[$index]['editLink'] = \HTML\a(
                    $this->icons->getClass("edit"),
                    $this->icons->getHTML("edit"),
                    "index.php?action=$phpFile&method=$action" .
                htmlentities("&resourceId=" . $resourceId . "&resourcemetadataId=" . $row['resourcemetadataId'] . 
                	'&browserTabID=' . $this->browserTabID)
                );
                
                $this->{$type}[$index]['editLink'] .= '&nbsp;' . \HTML\a(
                    $this->icons->getClass("delete"),
                    $this->icons->getHTML("delete"),
                    "index.php?action=$phpFile&method=deleteInit" .
                	htmlentities("&resourceId=" . $resourceId . "&resourcemetadataId=" . $row['resourcemetadataId'] . 
                	'&browserTabID=' . $this->browserTabID)
                );
            }
            $index++;
        }
    }
    /**
     * Get metadata details such as pages, section etc.
     *
     * @param array $row
     *
     * @return false|string
     */
    private function getDetails($row)
    {
        $page_start = $row['resourcemetadataPageStart'] ? $row['resourcemetadataPageStart'] : FALSE;
        $page_end = $row['resourcemetadataPageEnd'] ? "-" . $row['resourcemetadataPageEnd'] : FALSE;
        if ($page_start && $page_end)
        {
            $page_start = 'pp.' . $page_start;
        }
        elseif ($page_start)
        {
            $page_start = 'p.' . $page_start;
        }
        $page = $page_start ? $page_start . $page_end : FALSE;
        if ($page)
        {
            $details[] = $page;
        }
        $paragraph = $row['resourcemetadataParagraph'] ? $row['resourcemetadataParagraph'] : FALSE;
        if ($paragraph)
        {
            $details[] = $this->messages->text("resources", "paragraph") . "&nbsp;" . $paragraph;
        }
        $section = $row['resourcemetadataSection'] ? $row['resourcemetadataSection'] : FALSE;
        if ($section)
        {
            $details[] = $this->messages->text("resources", "section") . "&nbsp;" . $section;
        }
        $chapter = $row['resourcemetadataChapter'] ? $row['resourcemetadataChapter'] : FALSE;
        if ($chapter)
        {
            $details[] = $this->messages->text("resources", "chapter") . "&nbsp;" . $chapter;
        }

        return isset($details) ? implode(",&nbsp;", $details) : FALSE;
    }
}
