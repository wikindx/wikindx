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
 * LISTSOMERESOURCES class
 *
 * Lists certain resources from keyword and citation links etc. clicked when viewing a solo resource.
 */
class LISTSOMERESOURCES
{
    private $db;
    private $vars;
    private $stmt;
    private $errors;
    private $messages;
    private $common;
    private $commonBrowse;
    private $session;
    private $badInput;
    private $order = 'creator';
    private $params;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $this->common->browse = TRUE;
        $this->session = FACTORY_SESSION::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "browse", "BROWSECOMMON.php"]));
        $this->commonBrowse = new BROWSECOMMON();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "list"));
        // Turn on the 'add bookmark' menu item
        $this->session->setVar("bookmark_DisplayAdd", TRUE);
        if (!$this->session->getVar("list_Order")) {
            $this->session->setVar("list_Order", "creator");
        }
        $this->session->setVar("sql_LastOrder", $this->session->getVar("list_Order"));
        switch ($this->session->getVar("list_Order")) {
            case 'title':
                break;
            case 'creator':
                break;
            case 'publisher':
                break;
            case 'year':
                break;
            case 'timestamp':
                break;
            default:
                $this->session->setVar("list_Order", "creator");
        }
        $this->params = $this->session->getVar("sql_ListParams"); // temporarily store list parameters for use if reordering
        $this->session->delVar("sql_ListParams");
    }
    /**
     * With a reorder list request, which type of process do we want?
     */
    public function reorder()
    {
    	$this->session->setVar("sql_ListParams", $this->params);
        if (array_key_exists('list_AscDesc', $this->vars)) {
            $this->session->setVar("list_AscDesc", $this->vars['list_AscDesc']);
        }
        if (array_key_exists('list_Order', $this->vars)) {
            $this->session->setVar("list_Order", $this->vars['list_Order']);
            $this->session->setVar("sql_LastOrder", $this->vars['list_Order']);
            $this->order = $this->vars['list_Order'];
        }
        if ($this->session->getVar("list_SomeResources_catId")) {
            $this->vars['catId'] = $this->session->getVar("list_SomeResources_catId");
        }
        if ($this->session->getVar("list_SomeResources_id")) {
            $this->vars['id'] = $this->session->getVar("list_SomeResources_id");
        } elseif ($this->session->getVar("list_SomeResources_department")) {
            $this->vars['department'] = $this->session->getVar("list_SomeResources_department");
        } elseif ($this->session->getVar("list_SomeResources_institution")) {
            $this->vars['institution'] = $this->session->getVar("list_SomeResources_institution");
        }
        if ($this->session->getVar("list_SomeResources") == 'category') {
            $this->categoryProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'subcategory') {
            $this->subcategoryProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'userResource') {
            $this->userResourceProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'userQuote') {
            $this->userQuoteProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'userParaphrase') {
            $this->userParaphraseProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'cite') {
            $this->citeProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'citeCreator') {
            $this->citeProcessCreator();
        } elseif ($this->session->getVar("list_SomeResources") == 'type') {
            $this->typeProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'language') {
            $this->languageProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'keyword') {
            $this->keywordProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'keywordGroup') {
            $this->keywordGroupProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'metaKeyword') {
            $this->metaKeywordProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'metaKeywordGroup') {
            $this->metaKeywordGroupProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'publisher') {
            $this->publisherProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'specialPublisher') {
            $this->specialPublisherProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'collection') {
            $this->collectionProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'creator') {
            $this->creatorProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'year') {
            $this->yearProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'userTag') {
            $this->userTagProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'bibliography') {
            $this->bibliographyProcess();
        } elseif ($this->session->getVar("list_SomeResources") == 'quarantine') {
            $this->quarantineProcess();
        }
        $this->session->saveState(['search', 'sql', 'bookmark', 'list']);
    }
    /**
     * citeProcess - display resources citing this one
     */
    public function citeProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = 'action=list_LISTSOMERESOURCES_CORE&method=citeProcess&id=' . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $resCommon = FACTORY_RESOURCECOMMON::getInstance();
            $this->stmt->conditions[] = $this->db->formatFields('resourceId') . ' ' .
            	$this->db->inClause($resCommon->showCitations($this->vars["id"]));
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'cite');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * citeProcess - display resources citing creators
     */
    public function citeProcessCreator()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = 'action=list_LISTSOMERESOURCES_CORE&method=citeProcessCreator&id=' . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $split = UTF8::mb_explode(',', $this->vars["id"]);
            if (count($split) > 1) {
                foreach ($split as $id) {
                    $array[] = $id;
                }
                $this->stmt->conditionsOneField['resourceId'] = $array;
            } else {
                $this->stmt->conditions[] = ['resourceId' => $this->vars['id']];
            }
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'citeCreator');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * userResourceProcess - display resources input by this user
     */
    public function userResourceProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=userResourceProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = ['resourcemiscAddUserIdResource' => $this->vars['id']];
            $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'userResource');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * userQuoteProcess - display resources with quotes input by this user
     */
    public function userQuoteProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=userQuoteProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = '(' . $this->db->formatFields('resourcemetadataAddUserId') . '=' . $this->db->tidyInput($this->vars["id"]) .
                $this->db->and . $this->db->formatFields('resourcemetadataType') . '=' . $this->db->tidyInput('q') . ')';
            $this->stmt->joins['resource_metadata'] = ['resourcemetadataResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'userQuote');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * userParaphraseProcess - display resources with paraphrases input by this user
     */
    public function userParaphraseProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=userParaphraseProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = '(' . $this->db->formatFields('resourcemetadataAddUserId') . '=' . $this->db->tidyInput($this->vars["id"]) .
                $this->db->and . $this->db->formatFields('resourcemetadataType') . '=' . $this->db->tidyInput('p') . ')';
            $this->stmt->joins['resource_metadata'] = ['resourcemetadataResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'userParaphrase');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        $this->common->display($sql, 'list');
    }
    /**
     * userMusingProcess - display resources with public musings input by this user
     */
    public function userMusingProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=userMusingProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = '(' . $this->db->formatFields('resourcemetadataAddUserId') . '=' . $this->db->tidyInput($this->vars["id"]) .
                $this->db->and . $this->db->formatFields('resourcemetadataPrivate') . '=' . $this->db->tidyInput('N') .
                $this->db->and . $this->db->formatFields('resourcemetadataType') . '=' . $this->db->tidyInput('m') . ')';
            $this->stmt->joins['resource_metadata'] = ['resourcemetadataResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'userMusing');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * languageProcess - display resources with this language
     */
    public function languageProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=languageProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = ['resourcelanguageLanguageId' => $this->vars['id']];
            $this->stmt->joins['resource_language'] = ['resourcelanguageResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'language');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * keywordProcess - display resources with this keyword
     */
    public function keywordProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $catId = array_key_exists('catId', $this->vars) ? $this->vars["catId"] : $this->session->getVar("list_SomeResources_catId");
        if ($catId) { // From Category Tree browsing
            $this->session->setVar("list_SomeResources_catId", $catId);
            $this->stmt->conditions[] = '(' . $this->db->formatFields('resourcekeywordKeywordId') . '=' . $this->db->tidyInput($this->vars["id"]) .
                $this->db->and . $this->db->formatFields('resourcecategoryCategoryId') . '=' . $this->db->tidyInput($catId) . ')';
            $this->stmt->joins['resource_category'] = ['resourcecategoryResourceId', 'resourceId'];
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=keywordProcess&id=" . $this->vars["id"] .
                '&catId=' . $catId;
        } else {
            $this->stmt->conditions[] = ['resourcekeywordKeywordId' => $this->vars['id']];
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=keywordProcess&id=" . $this->vars["id"];
        }
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->joins['resource_keyword'] = ['resourcekeywordResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'keyword');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * keywordGroupProcess - display resources with this keyword group
     */
    public function keywordGroupProcess()
    {
    	if ($this->session->getVar('setup_ReadOnly')) {
        	GLOBALS::addTplVar('content', $this->errors->text('inputError', 'notRegistered'));
    		return;
    	}
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }

		$userId = $this->session->getVar('setup_UserId');
// Get groups this user is a member of
        $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
        $recordset = $this->db->select('user_groups_users', 'usergroupsusersGroupId');
		while ($row = $this->db->fetchRow($recordset)) {
            $groups[] = $row['usergroupsusersGroupId'];
        }
		$this->db->leftJoin('user_kg_keywords', 'userkgkeywordsKeywordGroupId', 'userkeywordgroupsId');
// Set conditions for groups this user is a member of
		if (isset($groups)) {
			$groupCondition = $this->db->formatConditionsOneField(array_values($groups), 
				'userkgusergroupsUserGroupId', FALSE, TRUE, FALSE, FALSE, TRUE);
			$userCondition = $this->db->formatConditions(['userkeywordgroupsUserId' => $userId], '=', TRUE);
			$this->db->formatConditions('(' . $groupCondition . ' ' . $this->db->or . ' ' . $userCondition . ')');
			$this->db->leftJoin('user_kg_usergroups', 'userkgusergroupsKeywordGroupId', 'userkeywordgroupsId');
		}
// Get only keyword groups this user owns
		else {
			$this->db->formatConditions(['userkeywordgroupsUserId' => $userId]);
		}
		$this->db->formatConditions(['userkeywordgroupsId' => $this->vars["id"]]);
		$recordset = $this->db->select('user_keywordgroups', 'userkgkeywordsKeywordId');
		while ($row = $this->db->fetchRow($recordset)) {
            $keywordIds[] = $row['userkgkeywordsKeywordId'];
        }
		if (!isset($keywordIds)) {
			return;
		}
// Get keywords in this keyword group
    	$this->stmt->conditionsOneField['resourcekeywordKeywordId'] = $keywordIds;
    	$queryString = "action=list_LISTSOMERESOURCES_CORE&method=keywordGroupProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->joins['resource_keyword'] = ['resourcekeywordResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'keywordGroup');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * metaKeywordProcess - display resources with metadata having this keyword
     */
    public function metaKeywordProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $typeArray = ['all', 'quotes', 'paraphrases', 'musings', 'ideas', 'notIdeas', 'lastMulti'];
        if (!array_key_exists("type", $this->vars) || $this->vars["type"]) {
        	$type = 'all';
        }
        elseif (array_search($this->vars['type'], $typeArray) === FALSE) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        else {
        	$type = $this->vars['type'];
        }
        $this->stmt->metadataPaging = TRUE;
        $this->common->metadataKeyword = $this->vars["id"];
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=metaKeywordProcess&type=" . $type . "&id=" . $this->vars["id"];
        if (($type == 'all') || ($type == 'ideas'))
        {
			// check for ideas with this keyword
			$this->commonBrowse->setPrivateConditions();
			$this->db->leftJoin('resource_keyword', 'resourcekeywordMetadataId', 'resourcemetadataId');
			$this->db->formatConditions(['resourcekeywordKeywordId' => $this->vars["id"]]);
			$this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
			$this->db->formatConditions(['resourcemetadataType' => 'i']);
			if ($this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId')) && $this->session->getVar("setup_UserId")) {
				$this->common->ideasFound = TRUE;
			}
		}
		if ($type == 'ideas') {
			$this->session->setVar("list_SomeResources", 'metaKeyword');
			$this->session->setVar("list_SomeResources_id", $this->vars['id']);
			// set the lastMulti session variable for quick return to this process.
			$this->session->setVar("sql_LastMulti", $queryString);
			$this->common->display(FALSE, 'list');
			return;
        }
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = $this->db->formatFields('resourcekeywordKeywordId') .
                $this->db->equal . $this->db->tidyInput($this->vars['id']) . $this->db->and .
                $this->db->formatFields('resourcekeywordMetadataId') . ' IS NOT NULL';
            $this->stmt->conditions[] = $this->db->formatFields('resourceId') . ' IS NOT NULL';
            $this->stmt->joins['resource'] = ['resourceId', 'resourcemetadataResourceId'];
            $this->stmt->joins['resource_keyword'] = ['resourcekeywordMetadataId', 'resourcemetadataId'];
            if (($type == 'all') || ($type == 'musings') || ($type == 'notIdeas')) {
	        	$this->commonBrowse->setPrivateConditions();
				if ($type == 'musings') {
					$this->db->formatConditions(['resourcemetadataType' => 'm']);
				}
				else {
	            	$this->db->formatConditions(['resourcemetadataType' => 'i'], TRUE);
            	}
            }
            else if ($type == 'quotes') {
            	$this->db->formatConditions(['resourcemetadataType' => 'q']);
            }
            else if ($type == 'paraphrases') {
            	$this->db->formatConditions(['resourcemetadataType' => 'p']);
            }
            $subStmt = $this->setSubQuery('resource_metadata');
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt, 'resource_metadata');
            $sql = $this->stmt->listList($this->session->getVar("list_Order"), 'resource_metadata');
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'metaKeyword');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * keywordGroupProcess - display metadata with this keyword group
     */
    public function metaKeywordGroupProcess()
    {
    	if ($this->session->getVar('setup_ReadOnly')) {
        	GLOBALS::addTplVar('content', $this->errors->text('inputError', 'notRegistered'));
    		return;
    	}
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $typeArray = ['all', 'quotes', 'paraphrases', 'musings', 'ideas', 'notIdeas', 'lastMulti'];
        if (!array_key_exists("type", $this->vars) || !$this->vars["type"] || (array_search($this->vars['type'], $typeArray) === FALSE)) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        else {
        	$type = $this->vars['type'];
        }
		$userId = $this->session->getVar('setup_UserId');
// Check user is a member of the keyword group or owns it
        $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
        $this->db->formatConditions(['userkgusergroupsKeywordGroupId' => $this->vars["id"]]);
		$this->db->leftJoin('user_kg_usergroups', 'userkgusergroupsUserGroupId', 'usergroupsusersGroupId');
		$this->db->leftJoin('user_keywordgroups', 'userkeywordgroupsId', 'userkgusergroupsKeywordGroupId');
        $recordset = $this->db->select('user_groups_users', ['userkeywordgroupsId']);
		while ($row = $this->db->fetchRow($recordset)) {
            $groups[] = $row['userkeywordgroupsId'];
        }
        $this->db->formatConditions(['userkeywordgroupsUserId' => $userId]);
        $recordset = $this->db->select('user_keywordgroups', ['userkeywordgroupsId']);
		while ($row = $this->db->fetchRow($recordset)) {
            $groups[] = $row['userkeywordgroupsId'];
        }
        if (!isset($groups) || !in_array($this->vars["id"], $groups)) {
            $this->badInput->close($this->errors->text("inputError", "keywordGroupNotMember"));
        }
// Get keywords in this keyword group
		$this->db->leftJoin('user_kg_keywords', 'userkgkeywordsKeywordGroupId', 'userkeywordgroupsId');
		$this->db->formatConditions(['userkeywordgroupsId' => $this->vars["id"]]);
		$recordset = $this->db->select('user_keywordgroups', 'userkgkeywordsKeywordId');
		while ($row = $this->db->fetchRow($recordset)) {
            $this->common->metadataKGKeywords[] = $row['userkgkeywordsKeywordId'];
        }
		if (empty($this->common->metadataKGKeywords)) {
			GLOBALS::addTplVar('content', $this->messages->text("misc", "noKeywords"));
			return;
		}
// If we reach here, we're set to go!
        $this->stmt->metadataPaging = TRUE;
        $this->common->metadataKeyword = $this->vars["id"];
    	$queryString = "action=list_LISTSOMERESOURCES_CORE&method=metaKeywordGroupProcess&type=" . $type . "&id=" . $this->vars["id"];
        if (($type == 'all') || ($type == 'ideas'))
        {
			// check for ideas with this keyword
			$this->commonBrowse->setPrivateConditions();
			$this->db->formatConditionsOneField($this->common->metadataKGKeywords, 'resourcekeywordKeywordId');
			$this->db->leftJoin('resource_keyword', 'resourcekeywordMetadataId', 'resourcemetadataId');
			$this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
			$this->db->formatConditions(['resourcemetadataType' => 'i']);
			if ($this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId')) && $this->session->getVar("setup_UserId")) {
				$this->common->ideasFound = TRUE;
			}
		}
		if ($type == 'ideas') {
			$this->session->setVar("list_SomeResources", 'metaKeywordGroup');
			$this->session->setVar("list_SomeResources_id", $this->vars['id']);
			// set the lastMulti session variable for quick return to this process.
			$this->session->setVar("sql_LastMulti", $queryString);
			$this->common->display(FALSE, 'list');
			return;
        }
    	$this->stmt->conditionsOneField['resourcekeywordKeywordId'] = $this->common->metadataKGKeywords;
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = $this->db->formatFields('resourcekeywordMetadataId') . ' IS NOT NULL';
            $this->stmt->conditions[] = $this->db->formatFields('resourceId') . ' IS NOT NULL';
            $this->stmt->joins['resource'] = ['resourceId', 'resourcemetadataResourceId'];
            $this->stmt->joins['resource_keyword'] = ['resourcekeywordMetadataId', 'resourcemetadataId'];
            if (($type == 'all') || ($type == 'musings') || ($type == 'notIdeas')) {
	        	$this->commonBrowse->setPrivateConditions();
				if ($type == 'musings') {
					$this->db->formatConditions(['resourcemetadataType' => 'm']);
				}
				else {
	            	$this->db->formatConditions(['resourcemetadataType' => 'i'], TRUE);
            	}
            }
            else if ($type == 'quotes') {
            	$this->db->formatConditions(['resourcemetadataType' => 'q']);
            }
            else if ($type == 'paraphrases') {
            	$this->db->formatConditions(['resourcemetadataType' => 'p']);
            }
            $subStmt = $this->setSubQuery('resource_metadata');
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt, 'resource_metadata');
            $sql = $this->stmt->listList($this->session->getVar("list_Order"), 'resource_metadata');
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'metaKeywordGroup');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * yearProcess - display resources in this publication year
     */
    public function yearProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=yearProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = ['resourceyearYear1' => base64_decode($this->vars["id"])];
            $this->stmt->joins['resource_year'] = ['resourceyearId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'year');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * typeProcess - display resources in this resource type
     */
    public function typeProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=typeProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = ['resourceType' => $this->vars["id"]];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'type');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * categoryProcess - display resources in this category
     */
    public function categoryProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=categoryProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = ['resourcecategoryCategoryId' => $this->vars["id"]];
            $this->stmt->joins['resource_category'] = ['resourcecategoryResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'category');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * userTagProcess - display resources in this user tag
     */
    public function userTagProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=userTagProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = ['resourceusertagsTagId' => $this->vars["id"]];
            $this->stmt->joins['resource_user_tags'] = ['resourceusertagsResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'userTag');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * subcategoryProcess - display resources in this category
     */
    public function subcategoryProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=subcategoryProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = ['resourcecategorySubcategoryId' => $this->vars["id"]];
            $this->stmt->joins['resource_category'] = ['resourcecategoryResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'subcategory');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * creatorProcess - display resources by this creator
     */
    public function creatorProcess()
    {
        $found = FALSE;
        if (array_key_exists("id", $this->vars) && $this->vars["id"]) {
            $found = TRUE;
            $type = 'id';
            $this->session->setVar("list_SomeResources_id", $this->vars['id']);
            $this->session->delVar("list_SomeResources_department");
            $this->session->delVar("list_SomeResources_institution");
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&id=" . $this->vars["id"];
            $this->db->formatConditions(['creatorId' => $this->vars["id"]]);
            $sameAsId = $this->db->selectFirstField('creator', 'creatorSameAs');
            // Is creator an alias for another author and a member of that author's group?
            if ($sameAsId) { // Group master ID found so find others in group
                $creatorIds = [$sameAsId];
                $this->db->formatConditions(['creatorSameAs' => $sameAsId]);
                $resultset = $this->db->select('creator', 'creatorId');
                while ($row = $this->db->fetchRow($resultset)) {
                    $creatorIds[] = $row['creatorId'];
                }
                $this->stmt->conditionsOneField['resourcecreatorCreatorId'] = $creatorIds;
            }
            // Perhaps the creator is the group master?
            else {
                $this->stmt->conditions[] = ['resourcecreatorCreatorId' => $this->vars["id"], 'creatorSameAs' => $this->vars["id"]];
            }
        } elseif (array_key_exists("department", $this->vars) && $this->vars["department"]) {
            $found = TRUE;
            $type = 'department';
            $this->session->setVar("list_SomeResources_department", $this->vars['department']);
            $this->session->delVar("list_SomeResources_id");
            $this->session->delVar("list_SomeResources_institution");
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&department=" . $this->vars["department"];
            $this->db->formatConditions(['usersDepartment' => base64_decode($this->vars["department"]),
                'usersIsCreator' => 'IS NOT NULL', ]);
            $resultset = $this->db->select('users', 'usersIsCreator');
            while ($row = $this->db->fetchRow($resultset)) {
                $this->stmt->conditions[] = ['resourcecreatorCreatorId' => $row['usersIsCreator'], 'creatorSameAs' => $row['usersIsCreator']];
            }
        } elseif (array_key_exists("institution", $this->vars) && $this->vars["institution"]) {
            $found = TRUE;
            $type = 'institution';
            $this->session->setVar("list_SomeResources_institution", $this->vars['institution']);
            $this->session->delVar("list_SomeResources_id");
            $this->session->delVar("list_SomeResources_department");
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&institution=" . $this->vars["institution"];
            $this->db->formatConditions(['usersInstitution' => base64_decode($this->vars["institution"]),
                'usersIsCreator' => 'IS NOT NULL', ]);
            $resultset = $this->db->select('users', 'usersIsCreator');
            while ($row = $this->db->fetchRow($resultset)) {
                $this->stmt->conditions[] = ['resourcecreatorCreatorId' => $row['usersIsCreator'], 'creatorSameAs' => $row['usersIsCreator']];
            }
        }
        if (!$found) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->joins['resource_creator'] = ['resourcecreatorResourceId', 'resourceId'];
            $this->stmt->joins['creator'] = ['creatorId', 'resourcecreatorCreatorId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'lastMulti')) {
            $sql = base64_decode($this->session->getVar("sql_ListStmt"));
            // set back to beginning
            $sql = preg_replace('/LIMIT (\d+),/u', "LIMIT 0,", $sql);
            $this->common->display($sql, 'list');
            $this->session->setVar("list_SubQuery", $this->session->getVar("list_SubQueryMulti"));
            $this->session->delVar("list_NextPreviousIds");

            return;
        }
        $this->session->setVar("list_SomeResources", 'creator');
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * Publishers (conference, translated books) where publisher is stored in resourcemiscField1
     */
    public function specialPublisherProcess()
    {
        $this->publisherProcess(TRUE);
    }
    /**
     * publisherProcess - display resources by this publisher
     *
     * @param mixed $miscField1
     */
    public function publisherProcess($miscField1 = FALSE)
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        if ($miscField1) {
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=specialPublisherProcess&id=" . $this->vars["id"];
        } else {
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=publisherProcess&id=" . $this->vars["id"];
        }
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            if ($miscField1) { // conference and translated publisher
                $this->session->setVar("list_SomeResources", 'specialPublisher');
                $this->stmt->conditions[] = $this->db->formatFields('resourcemiscField1') . '=' . $this->db->tidyInput($this->vars["id"]) .
                    $this->db->and . '(' . $this->db->formatFields('resourceType') . '=' . $this->db->tidyInput('proceedings') .
                    $this->db->or . $this->db->formatFields('resourceType') . '=' . $this->db->tidyInput('proceedings_article') .
                    $this->db->or . $this->db->formatFields('resourceType') . '=' . $this->db->tidyInput('book') .
                    $this->db->or . $this->db->formatFields('resourceType') . '=' . $this->db->tidyInput('book_article') .
                    $this->db->or . $this->db->formatFields('resourceType') . '=' . $this->db->tidyInput('book_chapter') . ')';
            } else {
                $this->session->setVar("list_SomeResources", 'publisher');
                $this->stmt->conditions[] = ['resourcemiscPublisher' => $this->vars["id"]];
            }
            $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
            $this->stmt->joins['publisher'] = ['publisherId', 'resourcemiscPublisher'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->common->display($sql, 'list');
    }
    /**
     * collection Process - display resources by this collection
     */
    public function collectionProcess()
    {
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=collectionProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = ['resourcemiscCollection' => $this->vars["id"]];
            $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'collection');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * bibliographyProcess - display resources in this user bibliography
     */
    public function bibliographyProcess()
    {
        $gatekeep = FACTORY_GATEKEEP::getInstance();
        $gatekeep->init(); // No Read-only access allowed
        if (!array_key_exists("id", $this->vars) || !$this->vars["id"]) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=bibliographyProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = ['userbibliographyresourceBibliographyId' => $this->vars["id"]];
            $this->stmt->joins['user_bibliography_resource'] = ['userbibliographyresourceResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'bibliography');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * List quarantined resources
     */
    public function quarantineProcess()
    {
        $gatekeep = FACTORY_GATEKEEP::getInstance();
        $gatekeep->requireSuper = TRUE;
        $gatekeep->init();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "quarantine"));
        $queryString = 'action=list_LISTSOMERESOURCES_CORE&method=quarantineProcess';
        if ($this->lastMulti($queryString)) {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->stmt->conditions[] = ['resourcemiscQuarantine' => 'Y'];
            $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->session->getVar("list_Order"), $queryString, $subStmt);
            $sql = $this->stmt->listList($this->session->getVar("list_Order"));
        } else {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'quarantine');
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->common->display($sql, 'list');
    }
    /**
     * If this is a different resource listing to the previous one, reset the paging counter.
     */
    private function pagingReset()
    {
        if (!array_key_exists('PagingStart', $this->vars)) {
            $this->session->delVar("mywikindx_PagingStart");
            $this->session->delVar("mywikindx_PagingStartAlpha");
            $this->session->delVar("list_AllIds");
            $this->session->delVar("list_PagingAlphaLinks");
            $this->session->delVar("sql_ListStmt");
        }
    }
    /**
     * Quicker querying when paging
     *
     * @param string $queryString
     *
     * @return string
     */
    private function quickQuery($queryString)
    {
        $sql = $this->session->getVar("sql_ListStmt");
        $this->pagingObject = FACTORY_PAGING::getInstance();
        $this->pagingObject->queryString = $queryString;
        $this->pagingObject->getPaging();
        $this->common->pagingObject = $this->pagingObject;
        $sql .= $this->db->limit(GLOBALS::getUserVar('Paging'), $this->pagingObject->start, TRUE); // "LIMIT $limitStart, $limit";
        return $sql;
    }
    /**
     * Check for and display the last list from the Resource menu
     *
     * @param string $queryString
     *
     * @return bool
     */
    private function lastMulti($queryString)
    {
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'lastMulti') && (GLOBALS::getUserVar('PagingStyle') != 'A')) {
            $this->session->delVar("mywikindx_PagingStart");
            $this->pagingObject = FACTORY_PAGING::getInstance();
            $this->pagingObject->queryString = $queryString;
            $this->pagingObject->getPaging();
            $this->common->pagingObject = $this->pagingObject;
            $this->common->lastMulti('list');

            return TRUE;
        }

        return FALSE;
    }
    /**
     * Set the subQuery
     *
     * @param string $queryString
     * @param mixed $table
     */
    private function setSubQuery($table = 'resource')
    {
        $this->db->ascDesc = $this->session->getVar("list_AscDesc");
        switch ($this->session->getVar("list_Order")) {
            case 'title':
                $this->stmt->quarantine(FALSE, 'resourceId');
                if ($this->session->getVar("list_SomeResources") != 'bibliography') {
                    $this->stmt->useBib('resourceId');
                }
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                if (GLOBALS::getUserVar('PagingStyle') == 'A') {
                    return $this->db->selectNoExecute($table, ['resourceTitleSort', ['resourceId' => 'rId']], FALSE, TRUE, TRUE);
                } else {
                    return $this->db->selectNoExecute($table, [['resourceId' => 'rId']], FALSE, TRUE, TRUE);
                }
                    // no break
            case 'creator':
                $this->stmt->joins['resource_creator'] = ['resourcecreatorResourceId', 'resourceId'];
                $this->stmt->joins['creator'] = ['creatorId', 'resourcecreatorCreatorId'];
                $this->stmt->quarantine(FALSE, 'resourcecreatorResourceId');
                if ($this->session->getVar("list_SomeResources") != 'bibliography') {
                    $this->stmt->useBib('resourcecreatorResourceId');
                }
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['resourcecreatorResourceId']);

                return $this->db->selectNoExecute($table, [['resourcecreatorResourceId' => 'rId']], FALSE, TRUE, TRUE);
            case 'publisher':
                $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
                $this->stmt->joins['publisher'] = ['publisherId', 'resourcemiscPublisher'];
                $this->stmt->quarantine(FALSE, 'resourcemiscId', FALSE);
                if ($this->session->getVar("list_SomeResources") != 'bibliography') {
                    $this->stmt->useBib('resourcemiscId');
                }
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);

                return $this->db->selectNoExecute($table, [['resourcemiscId' => 'rId']], FALSE, TRUE, TRUE);
            case 'year':
                $this->stmt->joins['resource_year'] = ['resourceyearId', 'resourceId'];
                $this->stmt->quarantine(FALSE, 'resourceyearId');
                if ($this->session->getVar("list_SomeResources") != 'bibliography') {
                    $this->stmt->useBib('resourceyearId');
                }
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);

                return $this->db->selectNoExecute($table, [['resourceyearId' => 'rId']], FALSE, TRUE, TRUE);
            case 'timestamp':
                $this->stmt->joins['resource_timestamp'] = ['resourcetimestampId', 'resourceId'];
                $this->stmt->quarantine(FALSE, 'resourcetimestampId');
                if ($this->session->getVar("list_SomeResources") != 'bibliography') {
                    $this->stmt->useBib('resourcetimestampId');
                }
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);

                return $this->db->selectNoExecute($table, [['resourcetimestampId' => 'rId']], FALSE, TRUE, TRUE);
            default:
                break;
        }
    }
}
