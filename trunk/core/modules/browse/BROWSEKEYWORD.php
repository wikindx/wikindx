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
 * BROWSEKEYWORD class
 *
 * Browse keywords as a 'tag cloud'
 */
class BROWSEKEYWORD
{
    public $keyword;
    public $glossary = [];
    public $sum;
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $metadataType = FALSE;
    private $type = 'all';

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "browse", "BROWSECOMMON.php"]));
        $this->common = new BROWSECOMMON();

        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseKeyword"));
    }
    /**
     * display keywords
     */
    public function init()
    {
        $this->sum = $this->sumTemp = $this->keyword = $this->glossary = [];
        if (array_key_exists('metadata', $this->vars))
        {
            $this->getMetaKeywords();
        }
        else
        {
            $this->getKeywords();
        }
        if (empty($this->keyword))
        {
            if (array_key_exists('metadata', $this->vars))
            {
                GLOBALS::addTplVar('content', $this->messages->text("misc", "noMetaKeywords"));
            }
            else
            {
                GLOBALS::addTplVar('content', $this->messages->text("misc", "noKeywords"));
            }

            return;
        }
        natcasesort($this->sum);
        natcasesort($this->keyword);
        $this->keyword = $this->common->paging($this->keyword);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        if (array_key_exists('metadata', $this->vars))
        {
            if ($this->type)
            {
                $type = '&mType=' . $this->type;
            }
            $this->common->pagingLinks('action=browse_BROWSEKEYWORD_CORE&metadata=1' . $type);
        }
        else
        {
            $this->common->pagingLinks('action=browse_BROWSEKEYWORD_CORE');
        }
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get resource keywords from db
     */
    public function getKeywords()
    {
        $this->common->userBibCondition('resourcekeywordResourceId');
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
        $this->db->formatConditions($this->db->formatFields('resourcekeywordResourceId') . ' IS NOT NULL');
        $this->db->formatConditions($this->db->formatFields('keywordKeyword') . ' IS NOT NULL');
        $this->db->groupBy('resourcekeywordKeywordId');
        $this->db->orderBy('keywordKeyword');
        $recordset = $this->db->selectCounts('resource_keyword', 'resourcekeywordKeywordId', ['keywordKeyword', 'keywordGlossary']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->collate($row);
        }
    }
    /**
     * Add keywords to array and sum totals
     *
     * @param array $row
     */
    public function collate($row)
    {
        if (!array_key_exists($row['resourcekeywordKeywordId'], $this->keyword))
        {
            $this->keyword[$row['resourcekeywordKeywordId']] = preg_replace(
                "/{(.*)}/Uu",
                "$1",
                \HTML\nlToHtml($row['keywordKeyword'])
            );
            if ($row['keywordGlossary'])
            {
                $this->glossary[$row['resourcekeywordKeywordId']] = \HTML\dbToHtmlPopupTidy($row['keywordGlossary']);
            }
        }
        if (!array_key_exists($row['resourcekeywordKeywordId'], $this->sum))
        {
            $this->sum[$row['resourcekeywordKeywordId']] = $row['count'];
        }
        else
        {
            $this->sum[$row['resourcekeywordKeywordId']] += $row['count'];
        }
    }
    /**
     * Process and display results
     */
    private function process()
    {
        $this->session->delVar("list_SomeResources_catId");
        $lowestSum = current($this->sum);
        $highestSum = end($this->sum);
        foreach ($this->keyword as $id => $name)
        {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            if (array_key_exists($id, $this->glossary))
            {
                $glossary = $this->glossary[$id];
            }
            else
            {
                $glossary = "";
            }
            if (array_key_exists('metadata', $this->vars))
            {
                if ($this->type)
                {
                    $type = '&mType=' . $this->type;
                }
                $links[] = \HTML\aBrowse(
                    $colour,
                    $size,
                    $name,
                    "index.php?action=list_LISTSOMERESOURCES_CORE&method=metaKeywordProcess" . $type .
                    htmlentities("&id=" . $id),
                    "",
                    $glossary
                ) . "&nbsp;[" . $this->sum[$id] . "]";
            }
            else
            {
                $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=keywordProcess&id=' . $id), "", $glossary) .
                "&nbsp;[" . $this->sum[$id] . "]";
            }
        }

        return implode("&nbsp; ", $links);
    }
    /**
     * Get keywords for metadata from db
     */
    private function getMetaKeywords()
    {
        if (array_key_exists('mType', $this->vars))
        {
            $this->type = $this->vars['mType'];
        }
        if (($this->type != 'all') && ($this->type != 'quotes') && ($this->type != 'paraphrases') && ($this->type != 'musings') &&
            ($this->type != 'ideas') && ($this->type != 'notIdeas'))
        {
            $this->type = 'all';
        }
        if (($this->type == 'all') || ($this->type == 'quotes') || ($this->type == 'paraphrases') || ($this->type == 'notIdeas'))
        {
            // quotes and paraphrases
            $this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
            $this->db->formatConditions($this->db->formatFields('keywordKeyword') . ' IS NOT NULL');
            if ($this->type == 'quotes')
            {
                $this->db->formatConditions(['resourcemetadataType' => 'q']);
            }
            if ($this->type == 'paraphrases')
            {
                $this->db->formatConditions(['resourcemetadataType' => 'p']);
            }
            else
            {
                $this->db->formatConditions(['resourcemetadataType' => 'm'], TRUE);
                $this->db->formatConditions(['resourcemetadataType' => 'i'], TRUE);
            }
            $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
            $this->db->leftJoin('resource_metadata', 'resourcemetadataId', 'resourcekeywordMetadataId');
            $this->common->userBibCondition('resourcemetadataResourceId', FALSE);
            $this->db->groupBy('resourcekeywordKeywordId');
            $this->db->orderBy('keywordKeyword');
            $recordset = $this->db->selectCounts('resource_keyword', 'resourcekeywordKeywordId', ['keywordKeyword', 'keywordGlossary']);
            while ($row = $this->db->fetchRow($recordset))
            {
                $this->collate($row);
            }
        }
        if (($this->type == 'musings') || ($this->type == 'ideas') || ($this->type == 'all') || ($this->type == 'notIdeas'))
        {
            // musings and ideas
            $this->common->setPrivateConditions();
            if ($this->type == 'all')
            {
                $this->db->formatConditionsOneField(['m', 'i'], 'resourcemetadataType');
            }
            elseif (($this->type == 'musings') || ($this->type == 'notIdeas'))
            {
                $this->db->formatConditions(['resourcemetadataType' => 'm']);
            }
            elseif ($this->type == 'ideas')
            {
                $this->db->formatConditions(['resourcemetadataType' => 'i']);
            }
            $this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
            $this->db->formatConditions($this->db->formatFields('keywordKeyword') . ' IS NOT NULL');
            $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
            $this->db->leftJoin('resource_metadata', 'resourcemetadataId', 'resourcekeywordMetadataId');
            $this->common->userBibCondition('resourcemetadataResourceId', FALSE);
            $this->db->groupBy('resourcekeywordKeywordId');
            $this->db->orderBy('keywordKeyword');
            $recordset = $this->db->selectCounts(
                'resource_keyword',
                'resourcekeywordKeywordId',
                ['keywordKeyword', 'resourcemetadataPrivate', 'keywordGlossary']
            );
            while ($row = $this->db->fetchRow($recordset))
            {
                if (($userId = $this->session->getVar("setup_UserId")) && ($row['resourcemetadataPrivate'] != 'N') &&
                    ($row['resourcemetadataPrivate'] != 'Y'))
                { // musing is part of user group
                    $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
                    $this->db->formatConditions(['usergroupsusersGroupId' => $row['resourcemetadataPrivate']]);
                    $resultset = $this->db->select('user_groups_users', 'usergroupsusersId');
                    if ($this->db->numRows($resultset))
                    {
                        $this->collate($row);
                    }
                }
                else
                {
                    $this->collate($row);
                }
            }
        }
    }
}
