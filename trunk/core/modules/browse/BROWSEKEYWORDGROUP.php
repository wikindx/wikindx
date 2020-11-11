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
 * BROWSEKEYWORDGROUP class
 *
 * Browse keyword groups as a 'tag cloud'
 */
class BROWSEKEYWORDGROUP
{
    public $keywordGroup;
    public $description = [];
    public $sum;
    private $resourceCounts = [];
    private $keywordMeta;
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $userId;
    private $metadataType = FALSE;
    private $metadata = FALSE;
    private $groups;
    private $kgKeywords;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "browse", "BROWSECOMMON.php"]));
        $this->common = new BROWSECOMMON();

        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseKeywordGroup"));
        $this->userId = $this->session->getVar('setup_UserId');
    }
    /**
     * display keyword groups
     */
    public function init()
    {
        if ($this->session->getVar('setup_ReadOnly'))
        {
            $errors = FACTORY_ERRORS::getInstance();
            GLOBALS::addTplVar('content', $errors->text('inputError', 'notRegistered'));

            return;
        }
        $this->sum = $this->sumTemp = $this->keywordGroup = $this->description = $this->keywordMeta = $this->kgKeywords = $this->groups = [];
        if (array_key_exists('metadata', $this->vars))
        {
            $this->metadata = TRUE;
            if (!$this->getMetaKeywordGroups())
            {
                GLOBALS::addTplVar('content', $this->messages->text("misc", "noKeywordGroups"));

                return;
            }
        }
        else
        {
            $this->getKeywordGroups();
            if (empty($this->keywordGroup) || empty($this->sum))
            {
                GLOBALS::addTplVar('content', $this->messages->text("misc", "noKeywordGroups"));

                return;
            }
        }
        natcasesort($this->sum);
        natcasesort($this->keywordGroup);
        $this->keywordGroup = $this->common->paging($this->keywordGroup);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        $this->common->pagingLinks('action=browse_BROWSEKEYWORDGROUP_CORE');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get resource keyword groups from db
     */
    public function getKeywordGroups()
    {
        // Get only keyword groups this user owns
        $this->setUGConditions();
        $this->db->leftJoin('user_kg_keywords', 'userkgkeywordsKeywordGroupId', 'userkeywordgroupsId');
        $this->db->orderBy('userkeywordgroupsName');
        $recordset = $this->db->select(
            'user_keywordgroups',
            ['userkeywordgroupsId', 'userkeywordgroupsName', 'userkeywordgroupsDescription', 'userkgkeywordsKeywordId']
        );
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!array_key_exists($row['userkeywordgroupsId'], $this->keywordGroup))
            {
                $this->keywordGroup[$row['userkeywordgroupsId']] = preg_replace(
                    "/{(.*)}/Uu",
                    "$1",
                    \HTML\nlToHtml($row['userkeywordgroupsName'])
                );
                if ($row['userkeywordgroupsDescription'])
                {
                    $this->description[$row['userkeywordgroupsId']] = \HTML\dbToHtmlPopupTidy($row['userkeywordgroupsDescription']);
                }
            }
            $this->collate($row);
        }
    }
    /**
     * Add keyword groups to array and sum totals
     *
     * @param array $row
     */
    public function collate($row)
    {
        // In each keyword group, if there are two keywords that appear in a single resource, the count of each resource is potentially doubled.
        // This must be checked for.
        $this->common->userBibCondition('resourcekeywordResourceId');
        $this->db->formatConditions($this->db->formatFields('resourcekeywordResourceId') . ' IS NOT NULL');
        $this->db->formatConditions(['resourcekeywordKeywordId' => $row['userkgkeywordsKeywordId']]);
        $recordset = $this->db->select('resource_keyword', ['resourcekeywordResourceId']);
        while ($row2 = $this->db->fetchRow($recordset))
        {
            if (!array_key_exists($row['userkeywordgroupsId'], $this->resourceCounts))
            {
                $this->resourceCounts[$row['userkeywordgroupsId']][] = $row2['resourcekeywordResourceId'];
                $this->sum[$row['userkeywordgroupsId']] = 1;
            }
            elseif (!in_array($row2['resourcekeywordResourceId'], $this->resourceCounts[$row['userkeywordgroupsId']]))
            {
                $this->resourceCounts[$row['userkeywordgroupsId']][] = $row2['resourcekeywordResourceId'];
                ++$this->sum[$row['userkeywordgroupsId']];
            }
        }
    }
    /**
     * Add keyword groups to array and sum totals for metadata
     *
     * @param array $row
     */
    public function collateMeta($row)
    {
        if (!array_key_exists($row['userkeywordgroupsId'], $this->sum))
        {
            $this->sum[$row['userkeywordgroupsId']] = $this->keywordMeta[$row['userkgkeywordsKeywordId']];
        }
        else
        {
            $this->sum[$row['userkeywordgroupsId']] += $this->keywordMeta[$row['userkgkeywordsKeywordId']];
        }
    }
    /**
     * Get keyword groupss for metadata from db
     */
    private function getMetaKeywordGroups()
    {
        if (array_key_exists('type', $this->vars))
        {
            $this->type = $this->vars['type'];
        }
        if (($this->type != 'all') && ($this->type != 'quotes') && ($this->type != 'paraphrases') && ($this->type != 'musings') &&
            ($this->type != 'ideas') && ($this->type != 'notIdeas'))
        {
            $this->type = 'all';
        }
        // Limit to only those keyword IDs that belong to the user's keyword groups
        $this->db->formatConditionsOneField(array_values($this->groups), 'userkgkeywordsKeywordGroupId');
        $recordset = $this->db->select('user_kg_keywords', ['userkgkeywordsKeywordId']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->kgKeywords[] = $row['userkgkeywordsKeywordId'];
        }
        if (empty($this->kgKeywords))
        {
            return FALSE; // no keywords for this user
        }
        // Get only keywords for metadata available to this user and count them/keyword
        if (!$this->getMetadataKeywordCount())
        {
            return FALSE;
        }
        // Get only keyword groups this user owns
        $this->setUGConditions();
        $this->db->formatConditionsOneField(array_keys($this->keywordMeta), 'userkgkeywordsKeywordId');
        $this->db->leftJoin('user_kg_keywords', 'userkgkeywordsKeywordGroupId', 'userkeywordgroupsId');
        $this->db->orderBy('userkeywordgroupsName');
        $recordset = $this->db->select(
            'user_keywordgroups',
            ['userkeywordgroupsId', 'userkeywordgroupsName', 'userkeywordgroupsDescription', 'userkgkeywordsKeywordId']
        );
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!array_key_exists($row['userkeywordgroupsId'], $this->keywordGroup))
            {
                $this->keywordGroup[$row['userkeywordgroupsId']] = preg_replace(
                    "/{(.*)}/Uu",
                    "$1",
                    \HTML\nlToHtml($row['userkeywordgroupsName'])
                );
                if ($row['userkeywordgroupsDescription'])
                {
                    $this->description[$row['userkeywordgroupsId']] = \HTML\dbToHtmlPopupTidy($row['userkeywordgroupsDescription']);
                }
            }
            $this->collateMeta($row);
        }

        return TRUE;
    }
    /** Set conditions for groups this user is a member of
     *
     */
    private function setUGConditions()
    {
        // First, get groups this user is a member of
        $this->db->formatConditions(['usergroupsusersUserId' => $this->userId]);
        $recordset = $this->db->select('user_groups_users', 'usergroupsusersGroupId');
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->groups[] = $row['usergroupsusersGroupId'];
        }
        // Then, get only keyword groups this user owns
        if (!empty($this->groups))
        {
            $groupCondition = $this->db->formatConditionsOneField(
                array_values($this->groups),
                'userkgusergroupsUserGroupId',
                FALSE,
                TRUE,
                FALSE,
                FALSE,
                TRUE
            );
            $userCondition = $this->db->formatConditions(['userkeywordgroupsUserId' => $this->userId], '=', TRUE);
            $this->db->formatConditions('(' . $groupCondition . ' ' . $this->db->or . ' ' . $userCondition . ')');
            $this->db->leftJoin('user_kg_usergroups', 'userkgusergroupsKeywordGroupId', 'userkeywordgroupsId');
        }
        else
        {
            $this->db->formatConditions(['userkeywordgroupsUserId' => $this->userId]);
        }
    }
    /**
     * Set conditions for metadata
     *
     * @return bool
     */
    private function getMetadataKeywordCount()
    {
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
            // Limit to only those keyword IDs that belong to the user's keyword groups
            $this->db->formatConditionsOneField($this->kgKeywords, 'resourcekeywordKeywordId');
            $this->db->orderBy('keywordKeyword');
            $recordset = $this->db->selectCounts(
                'resource_keyword',
                'resourcekeywordKeywordId',
                ['keywordKeyword', 'resourcemetadataPrivate']
            );
            while ($row = $this->db->fetchRow($recordset))
            {
                $this->keywordMeta[$row['resourcekeywordKeywordId']] = $row['count'];
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
            // Limit to only those keyword IDs that belong to the user's keyword groups
            $this->db->formatConditionsOneField($this->kgKeywords, 'resourcekeywordKeywordId');
            $this->db->orderBy('keywordKeyword');
            $recordset = $this->db->selectCounts(
                'resource_keyword',
                'resourcekeywordKeywordId',
                ['keywordKeyword', 'resourcemetadataPrivate']
            );
            while ($row = $this->db->fetchRow($recordset))
            {
                if (($row['resourcemetadataPrivate'] != 'N') && ($row['resourcemetadataPrivate'] != 'Y'))
                { // musing is part of user group
                    $this->db->formatConditions(['usergroupsusersUserId' => $this->userId]);
                    $this->db->formatConditions(['usergroupsusersGroupId' => $row['resourcemetadataPrivate']]);
                    $resultset = $this->db->select('user_groups_users', 'usergroupsusersId');
                    if ($this->db->numRows($resultset))
                    {
                        $this->keywordMeta[$row['resourcekeywordKeywordId']] = $row['count'];
                    }
                }
                else
                {
                    $this->keywordMeta[$row['resourcekeywordKeywordId']] = $row['count'];
                }
            }
        }
        if (empty($this->keywordMeta))
        {
            return FALSE;
        }

        return TRUE;
    }
    /**
     * Process and display results
     */
    private function process()
    {
        $lowestSum = current($this->sum);
        $highestSum = end($this->sum);
        foreach ($this->keywordGroup as $id => $name)
        {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            if (array_key_exists($id, $this->description))
            {
                $description = $this->description[$id];
            }
            else
            {
                $description = "";
            }
            if (array_key_exists('metadata', $this->vars))
            {
                if ($this->type)
                {
                    $type = '&type=' . $this->type;
                }
                $links[] = \HTML\aBrowse(
                    $colour,
                    $size,
                    $name,
                    "index.php?action=list_LISTSOMERESOURCES_CORE&method=metaKeywordGroupProcess" . $type .
                    htmlentities("&id=" . $id),
                    "",
                    $description
                ) . "&nbsp;[" . $this->sum[$id] . "]";
            }
            else
            {
                $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                    htmlentities('action=list_LISTSOMERESOURCES_CORE&method=keywordGroupProcess&id=' . $id), "", $description) .
                "&nbsp;[" . $this->sum[$id] . "]";
            }
        }

        return implode("&nbsp; ", $links);
    }
}
