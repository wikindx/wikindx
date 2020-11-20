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
    private $ascDesc;
    private $params;
    private $browserTabID = FALSE;

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
        $this->browserTabID = GLOBALS::getBrowserTabID();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "list"));
        $this->checkInput();
    }
    /**
	 * Initialize the process
	 */
	private function checkInput()
	{
        if (!$this->common->resourcesExist()) {
            $this->badInput->close($this->messages->text("misc", "noResources"));
        } 
        if (!array_key_exists('method', $this->vars)) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $method = $this->vars['method'];
        if (!method_exists($this, $method)) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        if (!in_array($method, ['creator', 'quarantine', 'reorder']) && (!array_key_exists('id', $this->vars) || !$this->vars['id'])) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        if ($this->browserTabID)
        {
            // 1. Load any pre-existing search data into GLOBALS $tempStorage
            // 2. Store in and extract data from $tempStorage
            // 3. Finally, put back $tempStorage into temp_storage using $this->common->updateTempStorage();
            GLOBALS::initTempStorage($this->db, $this->browserTabID);
        }
        if (array_key_exists('list_Order', $this->vars)) {
			$this->order = $this->vars['list_Order'];
		}
		else if (!$this->order = GLOBALS::getTempStorage('list_Order')) {
			$this->order = $this->session->getVar("list_Order");
		}
		$orders = ['creator', 'title', 'publisher', 'year', 'timestamp'];
        if (!$this->order || !in_array($this->order, $orders)) {
			$this->order = 'creator';
        }
		if (array_key_exists('list_AscDesc', $this->vars)) {
			$this->ascDesc = $this->vars['list_AscDesc'];
		}
		else {
			if ($method == 'reorder') {
				if (!$this->ascDesc = GLOBALS::getTempStorage('list_AscDesc')) {
					$this->ascDesc = $this->session->getVar("list_AscDesc");
				}
			}
			if (!$this->ascDesc && in_array($this->order, ['creator', 'title', 'publisher'])) {
			   $this->ascDesc = $this->db->asc;
			}
			else if (!$this->ascDesc && in_array($this->order, ['year', 'timestamp'])) {
			   $this->ascDesc = $this->db->desc;
			}
		}
        $this->session->delVar("mywikindx_PagingStart");
        $this->session->delVar("mywikindx_PagingStartAlpha");
        if ($this->browserTabID) {
            GLOBALS::unsetTempStorage(['mywikindx_PagingStart', 'mywikindx_PagingStartAlpha']);
			GLOBALS::setTempStorage(['list_Order' => $this->order]);
			GLOBALS::setTempStorage(['sql_LastOrder' => $this->order]);
			GLOBALS::setTempStorage(['list_AscDesc' => $this->ascDesc]);
        }
    	else {
    		$this->session->setVar("list_Order", $this->order);
    		$this->session->setVar("sql_LastOrder", $this->order);
    		$this->session->setVar("list_AscDesc", $this->ascDesc);
    	}
// temporarily store list parameters for use if reordering
		if (!$this->params = GLOBALS::getTempStorage('sql_ListParams')) {
			$this->params = $this->session->getVar("sql_ListParams"); 
		}
        $this->session->delVar("sql_ListParams");
        GLOBALS::unsetTempStorage(['sql_ListParams']);
        // Turn on the 'add bookmark' menu item
        $this->session->setVar("bookmark_DisplayAdd", TRUE);
    }
    /**
     * With a reorder list request, which type of process do we want?
     */
    public function reorder()
    {
        if (array_key_exists('message', $this->vars))
        {
            GLOBALS::addTplVar('content', $this->vars['message']);
        }
        $this->session->setVar("sql_ListParams", $this->params);
        if ($this->browserTabID) {
        	GLOBALS::setTempStorage(["sql_ListParams" => $this->params]);
        }
        
        if (!$this->vars['catId'] = GLOBALS::getTempStorage("list_SomeResources_catId"))
        {
            $this->vars['catId'] = $this->session->getVar("list_SomeResources_catId");
        }
        if (!$this->vars['id'] = GLOBALS::getTempStorage("list_SomeResources_id"))
        {
            $this->vars['id'] = $this->session->getVar("list_SomeResources_id");
        }
        elseif (!$this->vars['department'] = GLOBALS::getTempStorage("list_SomeResources_department"))
        {
            $this->vars['department'] = $this->session->getVar("list_SomeResources_department");
        }
        elseif (!$this->vars['institution'] = GLOBALS::getTempStorage("list_SomeResources_institution"))
        {
            $this->vars['institution'] = $this->session->getVar("list_SomeResources_institution");
        }
        if (!$process = GLOBALS::getTempStorage("list_SomeResources")) {
        	$process = $this->session->getVar("list_SomeResources");
        }
        if ($process == 'category')
        {
            $this->categoryProcess();
        }
        elseif ($process == 'subcategory')
        {
            $this->subcategoryProcess();
        }
        elseif ($process == 'userResource')
        {
            $this->userResourceProcess();
        }
        elseif ($process == 'userQuote')
        {
            $this->userQuoteProcess();
        }
        elseif ($process == 'userParaphrase')
        {
            $this->userParaphraseProcess();
        }
        elseif ($process == 'cite')
        {
            $this->citeProcess();
        }
        elseif ($process == 'citeCreator')
        {
            $this->citeProcessCreator();
        }
        elseif ($process == 'type')
        {
            $this->typeProcess();
        }
        elseif ($process == 'language')
        {
            $this->languageProcess();
        }
        elseif ($process == 'keyword')
        {
            $this->keywordProcess();
        }
        elseif ($process == 'keywordGroup')
        {
            $this->keywordGroupProcess();
        }
        elseif ($process == 'metaKeyword')
        {
            $this->metaKeywordProcess();
        }
        elseif ($process == 'metaKeywordGroup')
        {
            $this->metaKeywordGroupProcess();
        }
        elseif ($process == 'publisher')
        {
            $this->publisherProcess();
        }
        elseif ($process == 'specialPublisher')
        {
            $this->specialPublisherProcess();
        }
        elseif ($process == 'collection')
        {
            $this->collectionProcess();
        }
        elseif ($process == 'creator')
        {
            $this->creatorProcess();
        }
        elseif ($process == 'year')
        {
            $this->yearProcess();
        }
        elseif ($process == 'userTag')
        {
            $this->userTagProcess();
        }
        elseif ($process == 'bibliography')
        {
            $this->bibliographyProcess();
        }
        elseif ($process == 'quarantine')
        {
            $this->quarantineProcess();
        }
        $this->session->saveState(['search', 'sql', 'bookmark', 'list']);
    }
    /**
     * citeProcess - display resources citing this one
     */
    public function citeProcess()
    {
        $queryString = 'action=list_LISTSOMERESOURCES_CORE&method=citeProcess&id=' . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $resCommon = FACTORY_RESOURCECOMMON::getInstance();
            $this->stmt->conditions[] = $this->db->formatFields('resourceId') . ' ' .
                $this->db->inClause($resCommon->showCitations($this->vars["id"]));
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'cite');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'cite', 'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * citeProcess - display resources citing creators
     */
    public function citeProcessCreator()
    {
        $queryString = 'action=list_LISTSOMERESOURCES_CORE&method=citeProcessCreator&id=' . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $split = \UTF8\mb_explode(',', $this->vars["id"]);
            if (count($split) > 1)
            {
                foreach ($split as $id)
                {
                    $array[] = $id;
                }
                $this->stmt->conditionsOneField['resourceId'] = $array;
            }
            else
            {
                $this->stmt->conditions[] = ['resourceId' => $this->vars['id']];
            }
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'citeCreator');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'citeCreator', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * userResourceProcess - display resources input by this user
     */
    public function userResourceProcess()
    {
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=userResourceProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = ['resourcemiscAddUserIdResource' => $this->vars['id']];
            $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'userResource');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'userResource', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * userQuoteProcess - display resources with quotes input by this user
     */
    public function userQuoteProcess()
    {
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=userQuoteProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = '(' . $this->db->formatFields('resourcemetadataAddUserId') . '=' . $this->db->tidyInput($this->vars["id"]) .
                $this->db->and . $this->db->formatFields('resourcemetadataType') . '=' . $this->db->tidyInput('q') . ')';
            $this->stmt->joins['resource_metadata'] = ['resourcemetadataResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'userQuote');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'userQuote', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * userParaphraseProcess - display resources with paraphrases input by this user
     */
    public function userParaphraseProcess()
    {
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=userParaphraseProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = '(' . $this->db->formatFields('resourcemetadataAddUserId') . '=' . $this->db->tidyInput($this->vars["id"]) .
                $this->db->and . $this->db->formatFields('resourcemetadataType') . '=' . $this->db->tidyInput('p') . ')';
            $this->stmt->joins['resource_metadata'] = ['resourcemetadataResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'userParaphrase');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'userParaphrase', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * userMusingProcess - display resources with public musings input by this user
     */
    public function userMusingProcess()
    {
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=userMusingProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = '(' . $this->db->formatFields('resourcemetadataAddUserId') . '=' . $this->db->tidyInput($this->vars["id"]) .
                $this->db->and . $this->db->formatFields('resourcemetadataPrivate') . '=' . $this->db->tidyInput('N') .
                $this->db->and . $this->db->formatFields('resourcemetadataType') . '=' . $this->db->tidyInput('m') . ')';
            $this->stmt->joins['resource_metadata'] = ['resourcemetadataResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'userMusing');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'userMusing', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * languageProcess - display resources with this language
     */
    public function languageProcess()
    {
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=languageProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = ['resourcelanguageLanguageId' => $this->vars['id']];
            $this->stmt->joins['resource_language'] = ['resourcelanguageResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'language');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'language', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * keywordProcess - display resources with this keyword
     */
    public function keywordProcess()
    {
        $catId = array_key_exists('catId', $this->vars) ? $this->vars["catId"] : $this->session->getVar("list_SomeResources_catId");
        if ($catId)
        { // From Category Tree browsing
            $this->session->setVar("list_SomeResources_catId", $catId);
            $this->stmt->conditions[] = '(' . $this->db->formatFields('resourcekeywordKeywordId') . '=' . $this->db->tidyInput($this->vars["id"]) .
                $this->db->and . $this->db->formatFields('resourcecategoryCategoryId') . '=' . $this->db->tidyInput($catId) . ')';
            $this->stmt->joins['resource_category'] = ['resourcecategoryResourceId', 'resourceId'];
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=keywordProcess&id=" . $this->vars["id"] .
                '&catId=' . $catId;
        }
        else
        {
            $this->stmt->conditions[] = ['resourcekeywordKeywordId' => $this->vars['id']];
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=keywordProcess&id=" . $this->vars["id"];
        }
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->joins['resource_keyword'] = ['resourcekeywordResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'keyword');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'keyword', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * keywordGroupProcess - display resources with this keyword group
     */
    public function keywordGroupProcess()
    {
        if ($this->session->getVar('setup_ReadOnly'))
        {
            GLOBALS::addTplVar('content', $this->errors->text('inputError', 'notRegistered'));

            return;
        }
        $userId = $this->session->getVar('setup_UserId');
        // Get groups this user is a member of
        $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
        $recordset = $this->db->select('user_groups_users', 'usergroupsusersGroupId');
        while ($row = $this->db->fetchRow($recordset))
        {
            $groups[] = $row['usergroupsusersGroupId'];
        }
        $this->db->leftJoin('user_kg_keywords', 'userkgkeywordsKeywordGroupId', 'userkeywordgroupsId');
        // Set conditions for groups this user is a member of
        if (isset($groups))
        {
            $groupCondition = $this->db->formatConditionsOneField(
                array_values($groups),
                'userkgusergroupsUserGroupId',
                FALSE,
                TRUE,
                FALSE,
                FALSE,
                TRUE
            );
            $userCondition = $this->db->formatConditions(['userkeywordgroupsUserId' => $userId], '=', TRUE);
            $this->db->formatConditions('(' . $groupCondition . ' ' . $this->db->or . ' ' . $userCondition . ')');
            $this->db->leftJoin('user_kg_usergroups', 'userkgusergroupsKeywordGroupId', 'userkeywordgroupsId');
        }
        // Get only keyword groups this user owns
        else
        {
            $this->db->formatConditions(['userkeywordgroupsUserId' => $userId]);
        }
        $this->db->formatConditions(['userkeywordgroupsId' => $this->vars["id"]]);
        $recordset = $this->db->select('user_keywordgroups', 'userkgkeywordsKeywordId');
        while ($row = $this->db->fetchRow($recordset))
        {
            $keywordIds[] = $row['userkgkeywordsKeywordId'];
        }
        if (!isset($keywordIds))
        {
            return;
        }
        // Get keywords in this keyword group
        $this->stmt->conditionsOneField['resourcekeywordKeywordId'] = $keywordIds;
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=keywordGroupProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->joins['resource_keyword'] = ['resourcekeywordResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'keywordGroup');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'keywordGroup', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * metaKeywordProcess - display resources with metadata having this keyword
     */
    public function metaKeywordProcess()
    {
        $typeArray = ['all', 'quotes', 'paraphrases', 'musings', 'ideas', 'notIdeas', 'lastMulti'];
        if (!array_key_exists("mType", $this->vars) || $this->vars["mType"])
        {
            $type = 'all';
        }
        elseif (array_search($this->vars['mType'], $typeArray) === FALSE)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        else
        {
            $type = $this->vars['mType'];
        }
        $this->stmt->metadataPaging = TRUE;
        $this->common->metadataKeyword = $this->vars["id"];
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=metaKeywordProcess&mType=" . $type . "&id=" . $this->vars["id"];
        if (($type == 'all') || ($type == 'ideas'))
        {
            // check for ideas with this keyword
            $this->commonBrowse->setPrivateConditions();
            $this->db->leftJoin('resource_keyword', 'resourcekeywordMetadataId', 'resourcemetadataId');
            $this->db->formatConditions(['resourcekeywordKeywordId' => $this->vars["id"]]);
            $this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
            $this->db->formatConditions(['resourcemetadataType' => 'i']);
            if ($this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId')) && $this->session->getVar("setup_UserId"))
            {
                $this->common->ideasFound = TRUE;
            }
        }
        if ($type == 'ideas')
        {
            $this->session->setVar("list_SomeResources", 'metaKeyword');
            $this->session->setVar("list_SomeResources_id", $this->vars['id']);
            // set the lastMulti session variable for quick return to this process.
            $this->session->setVar("sql_LastMulti", $queryString);
			if ($this->browserTabID)
			{
            	GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'metaKeyword', 
            		'list_SomeResources_id' => $this->vars['id']]);
			}
            $this->common->display(FALSE, 'list');
        	$this->common->updateTempStorage();

            return;
        }
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = $this->db->formatFields('resourcekeywordKeywordId') .
                $this->db->equal . $this->db->tidyInput($this->vars['id']) . $this->db->and .
                $this->db->formatFields('resourcekeywordMetadataId') . ' IS NOT NULL';
            $this->stmt->conditions[] = $this->db->formatFields('resourceId') . ' IS NOT NULL';
            $this->stmt->joins['resource'] = ['resourceId', 'resourcemetadataResourceId'];
            $this->stmt->joins['resource_keyword'] = ['resourcekeywordMetadataId', 'resourcemetadataId'];
            if (($type == 'all') || ($type == 'musings') || ($type == 'notIdeas'))
            {
                $this->commonBrowse->setPrivateConditions();
                if ($type == 'musings')
                {
                    $this->db->formatConditions(['resourcemetadataType' => 'm']);
                }
                else
                {
                    $this->db->formatConditions(['resourcemetadataType' => 'i'], TRUE);
                }
            }
            elseif ($type == 'quotes')
            {
                $this->db->formatConditions(['resourcemetadataType' => 'q']);
            }
            elseif ($type == 'paraphrases')
            {
                $this->db->formatConditions(['resourcemetadataType' => 'p']);
            }
            $subStmt = $this->setSubQuery('resource_metadata');
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt, 'resource_metadata');
            $sql = $this->stmt->listList($this->order, 'resource_metadata');
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'metaKeyword');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'metaKeyword', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * keywordGroupProcess - display metadata with this keyword group
     */
    public function metaKeywordGroupProcess()
    {
        if ($this->session->getVar('setup_ReadOnly'))
        {
            GLOBALS::addTplVar('content', $this->errors->text('inputError', 'notRegistered'));

            return;
        }
        $typeArray = ['all', 'quotes', 'paraphrases', 'musings', 'ideas', 'notIdeas', 'lastMulti'];
        if (!array_key_exists("mType", $this->vars) || !$this->vars["mType"] || (array_search($this->vars['mType'], $typeArray) === FALSE))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        else
        {
            $type = $this->vars['mType'];
        }
        $userId = $this->session->getVar('setup_UserId');
        // Check user is a member of the keyword group or owns it
        $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
        $this->db->formatConditions(['userkgusergroupsKeywordGroupId' => $this->vars["id"]]);
        $this->db->leftJoin('user_kg_usergroups', 'userkgusergroupsUserGroupId', 'usergroupsusersGroupId');
        $this->db->leftJoin('user_keywordgroups', 'userkeywordgroupsId', 'userkgusergroupsKeywordGroupId');
        $recordset = $this->db->select('user_groups_users', ['userkeywordgroupsId']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $groups[] = $row['userkeywordgroupsId'];
        }
        $this->db->formatConditions(['userkeywordgroupsUserId' => $userId]);
        $recordset = $this->db->select('user_keywordgroups', ['userkeywordgroupsId']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $groups[] = $row['userkeywordgroupsId'];
        }
        if (!isset($groups) || !in_array($this->vars["id"], $groups))
        {
            $this->badInput->close($this->errors->text("inputError", "keywordGroupNotMember"));
        }
        // Get keywords in this keyword group
        $this->db->leftJoin('user_kg_keywords', 'userkgkeywordsKeywordGroupId', 'userkeywordgroupsId');
        $this->db->formatConditions(['userkeywordgroupsId' => $this->vars["id"]]);
        $recordset = $this->db->select('user_keywordgroups', 'userkgkeywordsKeywordId');
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->common->metadataKGKeywords[] = $row['userkgkeywordsKeywordId'];
        }
        if (empty($this->common->metadataKGKeywords))
        {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noKeywords"));

            return;
        }
        // If we reach here, we're set to go!
        $this->stmt->metadataPaging = TRUE;
        $this->common->metadataKeyword = $this->vars["id"];
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=metaKeywordGroupProcess&mType=" . $type . "&id=" . $this->vars["id"];
        if (($type == 'all') || ($type == 'ideas'))
        {
            // check for ideas with this keyword
            $this->commonBrowse->setPrivateConditions();
            $this->db->formatConditionsOneField($this->common->metadataKGKeywords, 'resourcekeywordKeywordId');
            $this->db->leftJoin('resource_keyword', 'resourcekeywordMetadataId', 'resourcemetadataId');
            $this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
            $this->db->formatConditions(['resourcemetadataType' => 'i']);
            if ($this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId')) && $this->session->getVar("setup_UserId"))
            {
                $this->common->ideasFound = TRUE;
            }
        }
        if ($type == 'ideas')
        {
            $this->session->setVar("list_SomeResources", 'metaKeywordGroup');
            $this->session->setVar("list_SomeResources_id", $this->vars['id']);
            // set the lastMulti session variable for quick return to this process.
            $this->session->setVar("sql_LastMulti", $queryString);
			if ($this->browserTabID)
			{
            	GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'metaKeywordGroup', 
            		'list_SomeResources_id' => $this->vars['id']]);
			}
            $this->common->display(FALSE, 'list');
        	$this->common->updateTempStorage();

            return;
        }
        $this->stmt->conditionsOneField['resourcekeywordKeywordId'] = $this->common->metadataKGKeywords;
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = $this->db->formatFields('resourcekeywordMetadataId') . ' IS NOT NULL';
            $this->stmt->conditions[] = $this->db->formatFields('resourceId') . ' IS NOT NULL';
            $this->stmt->joins['resource'] = ['resourceId', 'resourcemetadataResourceId'];
            $this->stmt->joins['resource_keyword'] = ['resourcekeywordMetadataId', 'resourcemetadataId'];
            if (($type == 'all') || ($type == 'musings') || ($type == 'notIdeas'))
            {
                $this->commonBrowse->setPrivateConditions();
                if ($type == 'musings')
                {
                    $this->db->formatConditions(['resourcemetadataType' => 'm']);
                }
                else
                {
                    $this->db->formatConditions(['resourcemetadataType' => 'i'], TRUE);
                }
            }
            elseif ($type == 'quotes')
            {
                $this->db->formatConditions(['resourcemetadataType' => 'q']);
            }
            elseif ($type == 'paraphrases')
            {
                $this->db->formatConditions(['resourcemetadataType' => 'p']);
            }
            $subStmt = $this->setSubQuery('resource_metadata');
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt, 'resource_metadata');
            $sql = $this->stmt->listList($this->order, 'resource_metadata');
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'metaKeywordGroup');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'metaKeywordGroup', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * yearProcess - display resources in this publication year
     */
    public function yearProcess()
    {
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=yearProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = ['resourceyearYear1' => base64_decode($this->vars["id"])];
            $this->stmt->joins['resource_year'] = ['resourceyearId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'year');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'year', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * typeProcess - display resources in this resource type
     */
    public function typeProcess()
    {
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=typeProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = ['resourceType' => $this->vars["id"]];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'type');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'type', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * categoryProcess - display resources in this category
     */
    public function categoryProcess()
    {
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=categoryProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = ['resourcecategoryCategoryId' => $this->vars["id"]];
            $this->stmt->joins['resource_category'] = ['resourcecategoryResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'category');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'category', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * userTagProcess - display resources in this user tag
     */
    public function userTagProcess()
    {
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=userTagProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = ['resourceusertagsTagId' => $this->vars["id"]];
            $this->stmt->joins['resource_user_tags'] = ['resourceusertagsResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'userTag');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'userTag', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * subcategoryProcess - display resources in this category
     */
    public function subcategoryProcess()
    {
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=subcategoryProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = ['resourcecategorySubcategoryId' => $this->vars["id"]];
            $this->stmt->joins['resource_category'] = ['resourcecategoryResourceId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'subcategory');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'subcategory', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * creatorProcess - display resources by this creator
     */
    public function creatorProcess()
    {
        $found = FALSE;
        if (array_key_exists("id", $this->vars) && $this->vars["id"])
        {
            $found = TRUE;
            $type = 'id';
            $this->session->setVar("list_SomeResources_id", $this->vars['id']);
            $this->session->delVar("list_SomeResources_department");
            $this->session->delVar("list_SomeResources_institution");
            if ($this->browserTabID) {
	            GLOBALS::setTempStorage(['list_SomeResources_id' => $this->vars['id']]);
	            GLOBALS::unsetTempStorage(['list_SomeResources_department', 'list_SomeResources_institution']);
	        }
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&id=" . $this->vars["id"];
            $this->db->formatConditions(['creatorId' => $this->vars["id"]]);
            $sameAsId = $this->db->selectFirstField('creator', 'creatorSameAs');
            // Is creator an alias for another author and a member of that author's group?
            if ($sameAsId)
            { // Group master ID found so find others in group
                $creatorIds = [$sameAsId];
                $this->db->formatConditions(['creatorSameAs' => $sameAsId]);
                $resultset = $this->db->select('creator', 'creatorId');
                while ($row = $this->db->fetchRow($resultset))
                {
                    $creatorIds[] = $row['creatorId'];
                }
                $this->stmt->conditionsOneField['resourcecreatorCreatorId'] = $creatorIds;
            }
            // Perhaps the creator is the group master?
            else
            {
                $this->stmt->conditions[] = ['resourcecreatorCreatorId' => $this->vars["id"], 'creatorSameAs' => $this->vars["id"]];
            }
        }
        elseif (array_key_exists("department", $this->vars) && $this->vars["department"])
        {
            $found = TRUE;
            $type = 'department';
            $this->session->setVar("list_SomeResources_department", $this->vars['department']);
            $this->session->delVar("list_SomeResources_id");
            $this->session->delVar("list_SomeResources_institution");
            if ($this->browserTabID) {
	            GLOBALS::setTempStorage(['list_SomeResources_department' => $this->vars['department']]);
	            GLOBALS::unsetTempStorage(['list_SomeResources_id', 'list_SomeResources_institution']);
	        }
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&department=" . $this->vars["department"];
            $this->db->formatConditions(['usersDepartment' => base64_decode($this->vars["department"]),
                'usersIsCreator' => 'IS NOT NULL', ]);
            $resultset = $this->db->select('users', 'usersIsCreator');
            while ($row = $this->db->fetchRow($resultset))
            {
                $this->stmt->conditions[] = ['resourcecreatorCreatorId' => $row['usersIsCreator'], 'creatorSameAs' => $row['usersIsCreator']];
            }
        }
        elseif (array_key_exists("institution", $this->vars) && $this->vars["institution"])
        {
            $found = TRUE;
            $type = 'institution';
            $this->session->setVar("list_SomeResources_institution", $this->vars['institution']);
            $this->session->delVar("list_SomeResources_id");
            $this->session->delVar("list_SomeResources_department");
            if ($this->browserTabID) {
	            GLOBALS::setTempStorage(['list_SomeResources_institution' => $this->vars['institution']]);
	            GLOBALS::unsetTempStorage(['list_SomeResources_id', 'list_SomeResources_department']);
	        }
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&institution=" . $this->vars["institution"];
            $this->db->formatConditions(['usersInstitution' => base64_decode($this->vars["institution"]),
                'usersIsCreator' => 'IS NOT NULL', ]);
            $resultset = $this->db->select('users', 'usersIsCreator');
            while ($row = $this->db->fetchRow($resultset))
            {
                $this->stmt->conditions[] = ['resourcecreatorCreatorId' => $row['usersIsCreator'], 'creatorSameAs' => $row['usersIsCreator']];
            }
        }
        if (!$found)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->joins['resource_creator'] = ['resourcecreatorResourceId', 'resourceId'];
            $this->stmt->joins['creator'] = ['creatorId', 'resourcecreatorCreatorId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'lastMulti'))
        {
            $sql = base64_decode($this->session->getVar("sql_ListStmt"));
            // set back to beginning
            $sql = preg_replace('/LIMIT (\d+),/u', "LIMIT 0,", $sql);
            $this->common->display($sql, 'list');
            $this->session->setVar("list_SubQuery", $this->session->getVar("list_SubQueryMulti"));
            $this->session->delVar("list_NextPreviousIds");
            if ($this->browserTabID) {
	            GLOBALS::setTempStorage(['list_SubQuery' => $this->session->getVar("list_SubQueryMulti")]);
	            GLOBALS::unsetTempStorage(['list_NextPreviousIds']);
	        }
        	$this->common->updateTempStorage();

            return;
        }
        $this->session->setVar("list_SomeResources", 'creator');
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'creator']);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
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
        if ($miscField1)
        {
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=specialPublisherProcess&id=" . $this->vars["id"];
        }
        else
        {
            $queryString = "action=list_LISTSOMERESOURCES_CORE&method=publisherProcess&id=" . $this->vars["id"];
        }
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            if ($miscField1)
            { // conference and translated publisher
                $this->session->setVar("list_SomeResources", 'specialPublisher');
				if ($this->browserTabID)
				{
					GLOBALS::setTempStorage(['list_SomeResources' => 'specialPublisher']);
				}
                $this->stmt->conditions[] = $this->db->formatFields('resourcemiscField1') . '=' . $this->db->tidyInput($this->vars["id"]) .
                    $this->db->and . '(' . $this->db->formatFields('resourceType') . '=' . $this->db->tidyInput('proceedings') .
                    $this->db->or . $this->db->formatFields('resourceType') . '=' . $this->db->tidyInput('proceedings_article') .
                    $this->db->or . $this->db->formatFields('resourceType') . '=' . $this->db->tidyInput('book') .
                    $this->db->or . $this->db->formatFields('resourceType') . '=' . $this->db->tidyInput('book_article') .
                    $this->db->or . $this->db->formatFields('resourceType') . '=' . $this->db->tidyInput('book_chapter') . ')';
            }
            else
            {
                $this->session->setVar("list_SomeResources", 'publisher');
        		if ($this->browserTabID)
        		{
                	GLOBALS::setTempStorage(['list_SomeResources' => 'publisher']);
                }
                $this->stmt->conditions[] = ['resourcemiscPublisher' => $this->vars["id"]];
            }
            $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
            $this->stmt->joins['publisher'] = ['publisherId', 'resourcemiscPublisher'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * collection Process - display resources by this collection
     */
    public function collectionProcess()
    {
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=collectionProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = ['resourcemiscCollection' => $this->vars["id"]];
            $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'collection');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'collection', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * bibliographyProcess - display resources in this user bibliography
     */
    public function bibliographyProcess()
    {
        $gatekeep = FACTORY_GATEKEEP::getInstance();
        $gatekeep->init(); // No Read-only access allowed
        $queryString = "action=list_LISTSOMERESOURCES_CORE&method=bibliographyProcess&id=" . $this->vars["id"];
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
        	$this->stmt->joins['user_bibliography_resource'] = ['userbibliographyresourceResourceId', 'resourceId'];
            $this->stmt->conditions[] = ['userbibliographyresourceBibliographyId' => $this->vars["id"]];
            $subStmt = $this->setSubQuery('resource', TRUE);
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'bibliography');
        $this->session->setVar("list_SomeResources_id", $this->vars['id']);
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'bibliography', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
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
        if ($this->lastMulti($queryString))
        {
            return;
        }
        $this->pagingReset();
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->conditions[] = ['resourcemiscQuarantine' => 'Y'];
            $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        $this->session->setVar("list_SomeResources", 'quarantine');
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString, 'list_SomeResources' => 'quarantine', 
            	'list_SomeResources_id' => $this->vars['id']]);
        }
        $this->common->quarantineList = TRUE;
        $this->common->display($sql, 'list');
        $this->common->updateTempStorage();
    }
    /**
     * If this is a different resource listing to the previous one, reset the paging counter.
     */
    private function pagingReset()
    {
        if (!array_key_exists('PagingStart', $this->vars))
        {
            $this->session->delVar("mywikindx_PagingStart");
            $this->session->delVar("mywikindx_PagingStartAlpha");
            $this->session->delVar("list_AllIds");
            $this->session->delVar("list_PagingAlphaLinks");
            $this->session->delVar("sql_ListStmt");
            GLOBALS::unsetTempStorage(['mywikindx_PagingStart', 'mywikindx_PagingStartAlpha', 'list_AllIds', 
            	'list_PagingAlphaLinks', 'sql_ListStmt']);
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
    	if (!$sql = GLOBALS::getTempStorage('sql_ListStmt')) {
	        $sql = $this->session->getVar("sql_ListStmt");
	    }
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
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'lastMulti') && (GLOBALS::getUserVar('PagingStyle') != 'A'))
        {
            $this->session->delVar("mywikindx_PagingStart");
            GLOBALS::unsetTempStorage(['mywikindx_PagingStart']);
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
     * @param mixed $table
     * @param bool $bib
     */
    private function setSubQuery($table = 'resource', $bib = FALSE)
    {
        $this->db->ascDesc = $this->ascDesc;
        switch ($this->order) {
            case 'title':
                $this->stmt->quarantine(FALSE, 'resourceId');
                if (!$bib) {
	                $this->stmt->useBib('resourceId');
	            }
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                if (GLOBALS::getUserVar('PagingStyle') == 'A')
                {
                    return $this->db->selectNoExecute($table, ['resourceTitleSort', ['resourceId' => 'rId']], FALSE, TRUE, TRUE);
                }
                else
                {
                    return $this->db->selectNoExecute($table, [['resourceId' => 'rId']], FALSE, TRUE, TRUE);
                }
                    // no break
            case 'creator':
                $this->stmt->joins['resource_creator'] = ['resourcecreatorResourceId', 'resourceId'];
                $this->stmt->joins['creator'] = ['creatorId', 'resourcecreatorCreatorId'];
                $this->stmt->quarantine(FALSE, 'resourcecreatorResourceId');
                if (!$bib) {
                	$this->stmt->useBib('resourcecreatorResourceId');
                }
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['resourcecreatorResourceId']);

                return $this->db->selectNoExecute($table, [['resourcecreatorResourceId' => 'rId']], FALSE, TRUE, TRUE);
            case 'publisher':
                $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
                $this->stmt->joins['publisher'] = ['publisherId', 'resourcemiscPublisher'];
                $this->stmt->quarantine(FALSE, 'resourcemiscId', FALSE);
                if (!$bib) {
	                $this->stmt->useBib('resourcemiscId');
	            }
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);

                return $this->db->selectNoExecute($table, [['resourcemiscId' => 'rId']], FALSE, TRUE, TRUE);
            case 'year':
                $this->stmt->joins['resource_year'] = ['resourceyearId', 'resourceId'];
                $this->stmt->quarantine(FALSE, 'resourceyearId');
                if (!$bib) {
	                $this->stmt->useBib('resourceyearId');
	            }
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);

                return $this->db->selectNoExecute($table, [['resourceyearId' => 'rId']], FALSE, TRUE, TRUE);
            case 'timestamp':
                $this->stmt->joins['resource_timestamp'] = ['resourcetimestampId', 'resourceId'];
                $this->stmt->quarantine(FALSE, 'resourcetimestampId');
                if (!$bib) {
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
