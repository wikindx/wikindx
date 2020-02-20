<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once("core/browse/BROWSECOMMON.php");
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
            $this->common->pagingLinks('action=browse_BROWSEKEYWORD_CORE&metadata=1');
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
                \HTML\dbToHtmlTidy($row['keywordKeyword'])
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
        $this->session->delVar('list_SomeResources_catId');
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
                $links[] = \HTML\aBrowse(
                    $colour,
                    $size,
                    $name,
                    "index.php?action=list_LISTSOMERESOURCES_CORE&method=metaKeywordProcess" .
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
        // quotes and paraphrases
        $this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
        $this->db->formatConditions($this->db->formatFields('keywordKeyword') . ' IS NOT NULL');
        $this->db->formatConditions(['resourcemetadataType' => 'm'], TRUE);
        $this->db->formatConditions(['resourcemetadataType' => 'i'], TRUE);
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
        // musings and ideas
        if ($this->session->getVar('setup_ReadOnly'))
        {
            $this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
        }
        elseif ($userId = $this->session->getVar('setup_UserId'))
        {
            $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
            $this->db->formatConditions($this->db->formatFields('usergroupsusersGroupId') . $this->db->equal .
                $this->db->formatFields('resourcemetadataPrivate'));
            $subSql = $this->db->selectNoExecute('user_groups_users', 'usergroupsusersId', FALSE, TRUE, TRUE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('N')
                . $this->db->and .
                $this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('Y');
            $case1 = $this->db->caseWhen($subject, FALSE, $subSql, FALSE, FALSE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('Y');
            $result = $this->db->formatFields('resourcemetadataAddUserId') . $this->db->equal . $this->db->tidyInput($userId);
            $case2 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('N');
            $result = $this->db->tidyInput(1);
            $case3 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $this->db->formatConditions($case1 . $this->db->or . $case2 . $this->db->or . $case3);
        }
        $this->db->formatConditionsOneField(['m', 'i'], 'resourcemetadataType');
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
            if (($userId = $this->session->getVar('setup_UserId')) && ($row['resourcemetadataPrivate'] != 'N') &&
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
