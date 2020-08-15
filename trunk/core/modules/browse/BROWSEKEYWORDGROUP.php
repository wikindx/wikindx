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
    private $counted = [];
    private $keyword;
    public $description = [];
    public $sum;
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $userId;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once("core/browse/BROWSECOMMON.php");
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
    	if ($this->session->getVar('setup_ReadOnly')) {
        	$errors = FACTORY_ERRORS::getInstance();
        	GLOBALS::addTplVar('content', $errors->text('inputError', 'notRegistered'));
    		return;
    	}
        $this->sum = $this->sumTemp = $this->keywordGroup = $this->description = $this->keyword = [];
        $this->getKeywordGroups();
        if (empty($this->keywordGroup)) {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noKeywords"));

            return;
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
// First, get keywords used and their resource count
        $this->common->userBibCondition('resourcekeywordResourceId');
        $this->db->formatConditions($this->db->formatFields('resourcekeywordResourceId') . ' IS NOT NULL');
        $this->db->groupBy('resourcekeywordKeywordId');
        $recordset = $this->db->selectCounts('resource_keyword', 'resourcekeywordKeywordId');
        while ($row = $this->db->fetchRow($recordset)) {
            $this->keyword[$row['resourcekeywordKeywordId']] = $row['count'];
        }
// Get groups this user is a member of
        $this->db->formatConditions(['usergroupsusersUserId' => $this->userId]);
        $recordset = $this->db->select('user_groups_users', 'usergroupsusersGroupId');
		while ($row = $this->db->fetchRow($recordset)) {
            $groups[] = $row['usergroupsusersGroupId'];
        }
		$this->db->formatConditionsOneField(array_keys($this->keyword), 'userkgkeywordsKeywordId');
		$this->db->leftJoin('user_kg_keywords', 'userkgkeywordsKeywordGroupId', 'userkeywordgroupsId');
// Set conditions for groups this user is a member of
		if (isset($groups)) {
			$groupCondition = $this->db->formatConditionsOneField(array_values($groups), 
				'userkgusergroupsUserGroupId', FALSE, TRUE, FALSE, FALSE, TRUE);
			$userCondition = $this->db->formatConditions(['userkeywordgroupsUserId' => $this->userId], '=', TRUE);
			$this->db->formatConditions('(' . $groupCondition . ' ' . $this->db->or . ' ' . $userCondition . ')');
			$this->db->leftJoin('user_kg_usergroups', 'userkgusergroupsKeywordGroupId', 'userkeywordgroupsId');
		}
// Get only keyword groups this user owns
		else {
			$this->db->formatConditions(['userkeywordgroupsUserId' => $this->userId]);
		}
		$this->db->orderBy('userkeywordgroupsName');
		$recordset = $this->db->select('user_keywordgroups', 
			['userkeywordgroupsId', 'userkeywordgroupsName', 'userkeywordgroupsDescription', 'userkgkeywordsKeywordId']);
		while ($row = $this->db->fetchRow($recordset)) {
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
        if (!array_key_exists($row['userkeywordgroupsId'], $this->keywordGroup)) {
            $this->keywordGroup[$row['userkeywordgroupsId']] = preg_replace(
                "/{(.*)}/Uu",
                "$1",
                \HTML\nlToHtml($row['userkeywordgroupsName'])
            );
            if ($row['userkeywordgroupsDescription']) {
                $this->description[$row['userkeywordgroupsId']] = \HTML\dbToHtmlPopupTidy($row['userkeywordgroupsDescription']);
            }
        }
        if (!array_key_exists($row['userkeywordgroupsId'], $this->sum)) {
        	if (array_key_exists($row['userkgkeywordsKeywordId'], $this->keyword)) {
            	$this->sum[$row['userkeywordgroupsId']] = $this->keyword[$row['userkgkeywordsKeywordId']];
            	$this->counted[$row['userkeywordgroupsId']][] = $row['userkgkeywordsKeywordId'];
        	}
        } else {
        	if (array_key_exists($row['userkgkeywordsKeywordId'], $this->keyword)) {
        		if (!in_array($row['userkgkeywordsKeywordId'], $this->counted[$row['userkeywordgroupsId']])) {
        			$this->counted[$row['userkeywordgroupsId']][] = $row['userkgkeywordsKeywordId'];
	            	$this->sum[$row['userkeywordgroupsId']] += $this->keyword[$row['userkgkeywordsKeywordId']];
        		}
        	}
        }
    }
    /**
     * Process and display results
     */
    private function process()
    {
        $lowestSum = current($this->sum);
        $highestSum = end($this->sum);
        foreach ($this->keywordGroup as $id => $name) {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            if (array_key_exists($id, $this->description)) {
                $description = $this->description[$id];
            } else {
                $description = "";
            }
            $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=keywordGroupProcess&id=' . $id), "", $description) .
                "&nbsp;[" . $this->sum[$id] . "]";
        }

        return implode("&nbsp; ", $links);
    }
}
