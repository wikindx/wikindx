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
    private $metadataType = FALSE;
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
        if (array_key_exists('metadata', $this->vars)) {
            $this->getMetaKeywords();
        } else {
            $this->getKeywordGroups();
        }
        if (empty($this->keywordGroup)) {
            if (array_key_exists('metadata', $this->vars)) {
                GLOBALS::addTplVar('content', $this->messages->text("misc", "noMetaKeywords"));
            } else {
                GLOBALS::addTplVar('content', $this->messages->text("misc", "noKeywords"));
            }

            return;
        }
        natcasesort($this->sum);
        natcasesort($this->keywordGroup);
        $this->keywordGroup = $this->common->paging($this->keywordGroup);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        if (array_key_exists('metadata', $this->vars)) 
        {
        	if ($this->type) {
        		$type = '&type=' . $this->type;
        	}
            $this->common->pagingLinks('action=browse_BROWSEKEYWORD_CORE&metadata=1' . $type);
        } else {
            $this->common->pagingLinks('action=browse_BROWSEKEYWORD_CORE');
        }
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
            if (array_key_exists('metadata', $this->vars)) {
				if ($this->type) {
					$type = '&type=' . $this->type;
				}
                $links[] = \HTML\aBrowse(
                    $colour,
                    $size,
                    $name,
                    "index.php?action=list_LISTSOMERESOURCES_CORE&method=metaKeywordProcess" . $type . 
                    htmlentities("&id=" . $id),
                    "",
                    $description
                ) . "&nbsp;[" . $this->sum[$id] . "]";
            } else {
                $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=keywordGroupProcess&id=' . $id), "", $description) .
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
    	if (array_key_exists('type', $this->vars)) {
    		$this->type = $this->vars['type'];
    	}
    	if (($this->type != 'all') && ($this->type != 'quotes') && ($this->type != 'paraphrases') && ($this->type != 'musings') && 
    		($this->type != 'ideas') && ($this->type != 'notIdeas')) {
    			$this->type = 'all';
    		}
    	if (($this->type == 'all') || ($this->type == 'quotes') || ($this->type == 'paraphrases') || ($this->type == 'notIdeas'))
    	{
			// quotes and paraphrases
			$this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
			$this->db->formatConditions($this->db->formatFields('keywordKeyword') . ' IS NOT NULL');
			if ($this->type == 'quotes') {
				$this->db->formatConditions(['resourcemetadataType' => 'q']);
			}
			if ($this->type == 'paraphrases') {
				$this->db->formatConditions(['resourcemetadataType' => 'p']);
			}
			else {
				$this->db->formatConditions(['resourcemetadataType' => 'm'], TRUE);
				$this->db->formatConditions(['resourcemetadataType' => 'i'], TRUE);
			}
			$this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
			$this->db->leftJoin('resource_metadata', 'resourcemetadataId', 'resourcekeywordMetadataId');
			$this->common->userBibCondition('resourcemetadataResourceId', FALSE);
			$this->db->groupBy('resourcekeywordKeywordId');
			$this->db->orderBy('keywordKeyword');
			$recordset = $this->db->selectCounts('resource_keyword', 'resourcekeywordKeywordId', ['keywordKeyword', 'keywordGlossary']);
			while ($row = $this->db->fetchRow($recordset)) {
				$this->collate($row);
			}
		}
		if (($this->type == 'musings') || ($this->type == 'ideas') || ($this->type == 'all') || ($this->type == 'notIdeas'))
    	{
			// musings and ideas
			if ($this->session->getVar("setup_ReadOnly")) {
				$this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
			} elseif ($userId = $this->session->getVar("setup_UserId")) {
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
			if ($this->type == 'all') {
				$this->db->formatConditionsOneField(['m', 'i'], 'resourcemetadataType');
			}
			else if (($this->type == 'musings') || ($this->type == 'notIdeas')) {
				$this->db->formatConditions(['resourcemetadataType' => 'm']);
			}
			else if ($this->type == 'ideas') {
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
			while ($row = $this->db->fetchRow($recordset)) {
				if (($userId = $this->session->getVar("setup_UserId")) && ($row['resourcemetadataPrivate'] != 'N') &&
					($row['resourcemetadataPrivate'] != 'Y')) { // musing is part of user group
					$this->db->formatConditions(['usergroupsusersUserId' => $userId]);
					$this->db->formatConditions(['usergroupsusersGroupId' => $row['resourcemetadataPrivate']]);
					$resultset = $this->db->select('user_groups_users', 'usergroupsusersId');
					if ($this->db->numRows($resultset)) {
						$this->collate($row);
					}
				} else {
					$this->collate($row);
				}
			}
		}
    }
}
