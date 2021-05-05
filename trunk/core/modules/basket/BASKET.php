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
 * BASKET class
 *
 * Management of resource baskets
 */
class BASKET
{
    private $db;
    private $vars;
    private $messages;
    private $success;
    private $session;
    private $stmt;
    private $userId;
    private $browserTabID = FALSE;
    public $useDB = FALSE; // accessed elsewhere
    public $useDBnorow = FALSE; // accessed elsewhere

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
        $this->userId = $this->session->getVar("setup_UserId");
        if ($this->browserTabID)
        {
            // 1. Load any pre-existing search data into GLOBALS $tempStorage
            // 2. Store in and extract data from $tempStorage
            // 3. Finally, put back $tempStorage into temp_storage using $this->common->updateTempStorage();
            GLOBALS::initTempStorage($this->db, $this->browserTabID);
        }
        if (!$order = GLOBALS::getTempStorage('list_Order')) {
        	$order = $this->session->getVar("list_Order");
        }
        if (!in_array($order, ['title', 'creator', 'publisher', 'year', 'timestamp'])) {
        	$this->session->setVar("list_Order", 'creator');
        	if ($this->browserTabID)
        	{
        		GLOBALS::getTempStorage(['list_Order' => 'creator']);
        	}
        }
    }
    /** Get basket from one of three possible locations
     *
     * @param $userId Default: $this->userId
     *
     * @return array
     */
    public function getBasket($userId = FALSE)
    {
    	if (!$userId) {
    		$userId = $this->userId;
    	}
    	$basketList = [];
    	$this->db->formatConditions(['usersbasketUserId' => $userId]);
        $resultSet = $this->db->select('users_basket', ['usersbasketBasket']);
        $row = $this->db->fetchRow($resultSet);
        if ($row) { // a row exists for this user
        	$basketList = unserialize($row['usersbasketBasket']);
        	$this->useDB = TRUE;
        } else if ($userId) { // write access but no row yet – function checking this will insert rather than update (LISTADDTO.php and init() here)
        	$this->useDBnorow = TRUE;
        } else if ($this->browserTabID) {
        	$basketList = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'basket_List');
        } else {
        	$basketList = $this->session->getVar("basket_List");
        }
    // Check that resource still exists so that menu item at least can be dealt with (another user might have deleted the resource)
    	if (!empty($basketList) && !$this->useDB) { // From session or TEMPSTORAGE. DELETERESOURCE already checks all users_basket rows
    		$this->db->formatConditionsOneField($basketList, 'resourceId');
    		$resultSet = $this->db->select('resource', 'resourceId');
    		if (!$this->db->numRows($resultSet)) {
    			$this->session->delVar("basket_List");
    			if ($this->browserTabID) {
        			\TEMPSTORAGE\deleteKeys($this->db, $this->browserTabID, ['basket_List']);
        		}
    			$basketList = [];
    		}
    	}
        if (!is_array($basketList)) {
        	return [];
        }
        return $basketList;
    } 
    /**
     * Add resource to basket
     */
    public function init()
    {
        $basket = $this->getBasket();
        if (array_key_exists('resourceId', $this->vars))
        {
            $resourceId = $this->vars['resourceId'];
            if (array_search($resourceId, $basket) === FALSE)
            {
                $basket[] = $resourceId;
            }
        }
        // Ensure array is unique
        array_unique($basket);
        if ($this->useDB) {
    		$this->db->formatConditions(['usersbasketUserId' => $this->userId]);
    		$this->db->update('users_basket', ['usersbasketBasket' => serialize($basket)]);
        } else if ($this->useDBnorow) {
        	$this->db->insert('users_basket', ['usersbasketUserId', 'usersbasketBasket'], [$this->userId, serialize($basket)]);
        } else {
			$this->session->setVar("basket_List", $basket);
			if ($this->browserTabID) {
				GLOBALS::setTempStorage(['basket_List' => $basket]);
				\TEMPSTORAGE\store($this->db, $this->browserTabID, GLOBALS::getTempStorage());
			}
		}
        // send back to view this resource with success message
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "resource", "RESOURCEVIEW.php"]));
        $resource = new RESOURCEVIEW();
        if (!$solo = GLOBALS::getTempStorage('sql_LastSolo')) {
        	$solo = $this->session->getVar("sql_LastSolo");
        }
        $resource->init($solo);
        GLOBALS::addTplVar('content', $this->success->text("basketAdd"));
    }
    /**
     * Remove single resource from basket
     */
    public function remove()
    {
        $basket = $this->getBasket();
        $resourceId = $this->vars['resourceId'];
        if (($key = array_search($resourceId, $basket)) !== FALSE)
        {
            unset($basket[$key]);
        }
        if ($this->useDB) {
    		$this->db->formatConditions(['usersbasketUserId' => $this->userId]);
    		$this->db->update('users_basket', ['usersbasketBasket' => serialize($basket)]);
        } else {
			$this->session->setVar("basket_List", $basket);
			if ($this->browserTabID) {
				GLOBALS::setTempStorage(['basket_List' => $basket]);
				\TEMPSTORAGE\store($this->db, $this->browserTabID, GLOBALS::getTempStorage());
			}
		}
        // send back to view this resource with success message
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "resource", "RESOURCEVIEW.php"]));
        $resource = new RESOURCEVIEW();
        if (!$solo = GLOBALS::getTempStorage('sql_LastSolo')) {
        	$solo = $this->session->getVar("sql_LastSolo");
        }
        $resource->init($solo);
        GLOBALS::addTplVar('content', $this->success->text("basketRemove"));
    }
    /**
     * Reorder the view with list_AscDesc session variable
     */
    public function reorder()
    {
        $this->session->setVar("list_AscDesc", $this->vars['list_AscDesc']);
        if ($this->browserTabID) {
        	GLOBALS::setTempStorage(['list_AscDesc' => $this->vars['list_AscDesc']]);
        }
        $this->view();
    }
    /**
     * View the basket
     */
    public function view()
    {
        $basket = $this->getBasket();
        if (empty($basket)) {
		    include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "libs", "FRONT.php"]));
        	new FRONT(); // __construct() runs on autopilot
    		return;
    	}
    	$message = FALSE;
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        if ($message) {
            GLOBALS::addTplVar('content', $message);
        }
        $sql = FALSE;
        $this->session->delVar('sql_ListParams');
		if ($this->browserTabID)
		{
			GLOBALS::unsetTempStorage(['sql_ListParams']);
		}
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $queryString = 'action=basket_BASKET_CORE&method=view';
        $sizeOfbasket = is_array($basket) ? count($basket) : 0;
        $this->session->setVar("setup_PagingTotal", $sizeOfbasket);
		if ($this->browserTabID)
		{
			GLOBALS::setTempStorage(['setup_PagingTotal' => $sizeOfbasket]);
		}
        $this->pagingObject = FACTORY_PAGING::getInstance();
        $this->pagingObject->queryString = $queryString;
        $this->pagingObject->getPaging();
        $this->common->pagingObject = $this->pagingObject;
        GLOBALS::setTplVar('heading', $this->messages->text('heading', 'basket'));
        if (array_key_exists('list_Order', $this->vars))
        {
            $this->session->setVar("list_Order", $this->vars['list_Order']);
            GLOBALS::setTempStorage(['list_Order' => $this->session->getVar("list_Order")]);
        }
        $this->session->delVar("mywikindx_PagingStart");
        $this->session->delVar("mywikindx_PagingStartAlpha");
		if ($this->browserTabID)
		{
			GLOBALS::unsetTempStorage(['mywikindx_PagingStart', 'mywikindx_PagingStartAlpha']);
			$this->common->updateTempStorage();
		}
        if ($this->lastMulti($queryString))
        {
            return;
        }
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->session->delVar("list_PagingAlphaLinks");
            $this->session->delVar("list_AllIds");
			if ($this->browserTabID)
			{
				GLOBALS::unsetTempStorage(['list_PagingAlphaLinks', 'list_AllIds']);
				GLOBALS::setTempStorage(['list_AllIds' => $this->session->getVar("basket_List")]);
			}
            $sql = $this->returnBasketSql($queryString);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
        if (!$sql)
        {
            $errors = FACTORY_ERRORS::getInstance();
            $badInput = FACTORY_BADINPUT::getInstance();
            $badInput->close($errors->text("inputError", "invalid"));
        }
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString]);
        }
        // Turn on the 'add bookmark' menu item
        $this->session->setVar("bookmark_DisplayAdd", TRUE);
        $this->common->display($sql, 'basket');
        $this->common->updateTempStorage();
    }
    /**
     * Get basket SQL statement.
     * Given as a separate fuction because it is also used when exporting the basket to various formats with the importExport plugin
     *
     * @param false|string $queryString Default FALSE
     * @param bool $order Default FALSE
     *
     * @return string
     */
    public function returnBasketSql($queryString = FALSE, $order = FALSE)
    {
        if (!$order) {
			if (!$order = GLOBALS::getTempStorage('list_Order')) {
				$order = $this->session->getVar("list_Order");
			}
        }
        $basket = $this->getBasket();
        $this->session->setVar("list_AllIds", $basket);
        if ($this->browserTabID) {
        	\TEMPSTORAGE\store($this->db, $this->browserTabID, ['basket_List' => $basket]);
        }
        $subStmt = $this->setSubQuery($basket);
        $this->stmt->listSubQuery($order, $queryString, $subStmt);

        return $this->stmt->listList($order);
    }
    /**
     * Delete the basket
     *
     * @return string
     */
    public function delete()
    {
        $basket = $this->getBasket();
        if (empty($basket)) {
		    include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "libs", "FRONT.php"]));
        	new FRONT(); // __construct() runs on autopilot
    		return;
    	}
        // Ask for confirmation first
        GLOBALS::setTplVar('heading', $this->messages->text('heading', 'basketDelete'));

        $pString = \FORM\formHeader('basket_BASKET_CORE');
        $pString .= \FORM\hidden('method', 'deleteConfirm');
        if ($this->useDB) {
        	$pString .= \FORM\hidden('useDB', 1);
        }
        $pString .= \FORM\formSubmit($this->messages->text("submit", "Confirm")) . \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Confirm screen for the delete action
     */
    public function deleteConfirm()
    {
        if (array_key_exists('useDB', $this->vars)) {
    		$this->db->formatConditions(['usersbasketUserId' => $this->userId]);
    		$this->db->delete('users_basket');
    	} else if ($this->browserTabID) {
        	\TEMPSTORAGE\deleteKeys($this->db, $this->browserTabID, ['basket_List']);
        }
        $this->session->clearArray('basket');
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "libs", "FRONT.php"]));
        new FRONT($this->success->text("basketDelete")); // __construct() runs on autopilot
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
            $this->pagingObject = FACTORY_PAGING::getInstance();
            $this->pagingObject->queryString = $queryString;
            $this->pagingObject->getPaging();
            $this->common->pagingObject = $this->pagingObject;
            $this->common->lastMulti('basket');

            return TRUE;
        }

        return FALSE;
    }
    /**
     * Set the subQuery
     *
     * @param mixed $ids
     *
     * @return object Record object
     */
    private function setSubQuery($ids)
    {
        if (!$order = GLOBALS::getTempStorage('list_Order')) {
        	$order = $this->session->getVar("list_Order");
        }
        if (!$this->db->ascDesc = GLOBALS::getTempStorage('list_AscDesc')) {
        	$this->db->ascDesc = $this->session->getVar("list_AscDesc");
        }
        switch ($order) {
            case 'title':
                $this->stmt->quarantine(FALSE, 'resourceId');
                $this->stmt->useBib('resourceId');
                $this->stmt->conditionsOneField['resourceId'] = $ids;
                $this->stmt->executeCondJoins();
                if (GLOBALS::getUserVar('PagingStyle') == 'A')
                {
                    return $this->db->selectNoExecute('resource', ['resourceTitleSort', ['resourceId' => 'rId']], FALSE, TRUE, TRUE);
                }
                else
                {
                    return $this->db->selectNoExecute('resource', [['resourceId' => 'rId']], FALSE, TRUE, TRUE);
                }
                    // no break
            case 'creator':
                $this->stmt->joins['resource_creator'] = ['resourcecreatorResourceId', 'resourceId'];
                $this->stmt->quarantine(FALSE, 'resourcecreatorResourceId');
                $this->stmt->useBib('resourcecreatorResourceId');
                $this->stmt->conditionsOneField['resourcecreatorResourceId'] = $ids;
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['resourcecreatorResourceId', 'resourcecreatorCreatorSurname']);

                return $this->db->selectNoExecute(
                    'resource',
                    [['resourcecreatorResourceId' => 'rId']],
                    FALSE,
                    TRUE,
                    TRUE
                );
            case 'publisher':
                $this->stmt->quarantine(FALSE, 'resourcemiscId');
                $this->stmt->useBib('resourcemiscId');
                $this->stmt->conditionsOneField['resourcemiscId'] = $ids;
                $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
                $this->stmt->joins['publisher'] = ['resourcemiscPublisher', 'publisherId'];
                $this->stmt->executeCondJoins();

                return $this->db->selectNoExecute('resource', [['resourcemiscId' => 'rId']], FALSE, TRUE, TRUE);
            case 'year':
                $this->stmt->quarantine(FALSE, 'resourceyearId');
                $this->stmt->useBib('resourceyearId');
                $this->stmt->conditionsOneField['resourceyearId'] = $ids;
                $this->stmt->joins['resource'] = ['resourceId', 'resourceyearId'];
                $this->stmt->executeCondJoins();

                return $this->db->selectNoExecute('resource_year', [['resourceyearId' => 'rId']], FALSE, TRUE, TRUE);
            case 'timestamp':
                $this->stmt->quarantine(FALSE, 'resourcetimestampId');
                $this->stmt->useBib('resourcetimestampId');
                $this->stmt->conditionsOneField['resourcetimestampId'] = $ids;
                $this->stmt->executeCondJoins();

                return $this->db->selectNoExecute('resource_timestamp', [['resourcetimestampId' => 'rId']], FALSE, TRUE, TRUE);
            default:
                break;
        }
    }
}
