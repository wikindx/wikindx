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
 * LISTRESOURCES class
 *
 * List all database resources.
 */
class LISTRESOURCES
{
    private $db;
    private $vars;
    private $stmt;
    private $messages;
    private $errors;
    private $success;
    private $badInput;
    private $session;
    private $common;
    private $commonBib;
    private $user;
    private $count = 0;
    private $params;
    private $browserTabID = FALSE;
    private $order = 'creator';
    private $ascDesc = FALSE;

    public function __construct($method = FALSE)
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "list"));
        $this->checkInput();
    }
	/**
	 * Initialize the process
	 */
	private function checkInput()
	{
        if (!$this->common->resourcesExist())
        {
            $this->badInput->close($this->messages->text("misc", "noResources"));
        } 
        if (!array_key_exists('method', $this->vars) || (($this->vars['method'] != 'reorder') && !array_key_exists('list_Order', $this->vars)))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $method = $this->vars['method'];
        if (!method_exists($this, $method))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $linksInfo['info'] = $this->commonBib->displayBib();
        GLOBALS::setTplVar('resourceListInfo', $linksInfo);
        unset($linksInfo);
        if ($this->browserTabID)
        {
            // 1. Load any pre-existing search data into GLOBALS $tempStorage
            // 2. Store in and extract data from $tempStorage
            // 3. Finally, put back $tempStorage into temp_storage using $this->common->updateTempStorage();
            GLOBALS::initTempStorage($this->db, $this->browserTabID);
            // As LISTSOMERESOURCES shares some resources with LISTRESOURCES, need to ensure the data are clean . . .
            GLOBALS::unsetTempStorage(["list_SomeResources"]);
        }
        if (array_key_exists('list_Order', $this->vars)) {
			$this->order = $this->vars['list_Order'];
		}
		$orders = ['creator', 'title', 'publisher', 'year', 'timestamp', 'popularityIndex', 'viewsIndex', 'downloadsIndex', 'maturityIndex'];
        if (!in_array($this->order, $orders))
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"));
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
			else if (!$this->ascDesc && in_array($this->order, ['year', 'timestamp', 'viewsIndex', 'popularityIndex', 
				'downloadsIndex', 'maturityIndex'])) {
			   $this->ascDesc = $this->db->desc;
			}
		}
        $this->session->delVar("mywikindx_PagingStart");
        $this->session->delVar("mywikindx_PagingStartAlpha");
        if ($this->browserTabID)
        {
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
        // if browsing on the master bib, setting allIds = TRUE makes the execution marginally quicker for large databases.
        if (!GLOBALS::getUserVar('BrowseBibliography'))
        {
            $this->stmt->allIds = TRUE;
        }
// temporarily store list parameters for use if reordering
		if (!$this->params = GLOBALS::getTempStorage('sql_ListParams')) {
			$this->params = $this->session->getVar("sql_ListParams"); 
		}
        $this->session->delVar("sql_ListParams");
        GLOBALS::unsetTempStorage(['sql_ListParams']);
    }
    /**
     * With a reorder list request, print any message first
     */
    public function reorder()
    {
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
        $this->session->setVar("sql_ListParams", $this->params);
        if ($this->browserTabID) {
        	GLOBALS::setTempStorage(["sqlListParams" => $this->params]);
        }
        $this->processGeneral();
    }
    /**
     * Display resources ....
     */
    public function processGeneral()
    {
        // April 2013: For some reason I haven't figured out yet, this method and all its time/processing overhead is called twice.  This little routine stops that.
        // Still wrong Aug. 2018 . . .
        if ($this->count)
        {
            return;
        }
        ++$this->count;
        // Turn on the 'add bookmark' menu item
        $this->session->setVar("bookmark_DisplayAdd", TRUE);
        $queryString = 'action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=' . $this->order;
        // NB. Ordering by popularity index uses temporary tables which must be created for each call (so cannot use the shortcuts for lastMulti)
        if ((!$this->vars['method'] == 'processGeneral') && ($this->order != 'popularityIndex') && $this->lastMulti($queryString))
        {
            return;
        }
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A') || 
        	in_array($this->order, ['popularityIndex', 'downloadsIndex', 'viewsIndex']))
        {
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($this->order, $queryString, $subStmt);
            $sql = $this->stmt->listList($this->order);
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
        $this->common->display($sql, 'list');
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString]);
        }
        $this->common->updateTempStorage();
    }
    /**
     * Quicker querying when paging
     *
     * @param string $queryString
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
     */
    private function setSubQuery()
    {
    	$this->db->ascDesc = $this->ascDesc;
    	switch ($this->order) {
            case 'title':
                $this->stmt->quarantine(FALSE, 'resourceId');
                $this->stmt->useBib('resourceId');
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
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['resourcecreatorResourceId']);

                return $this->db->selectNoExecute(
                    'resource',
                    [['resourcecreatorResourceId' => 'rId']],
                    FALSE,
                    TRUE,
                    TRUE
                );
            case 'publisher':
                $this->stmt->quarantine(FALSE, 'resourcemiscId', FALSE);
                $this->stmt->useBib('resourcemiscId');
                $this->stmt->joins['publisher'] = ['resourcemiscPublisher', 'publisherId'];
                $this->stmt->executeCondJoins();

                return $this->db->selectNoExecute('resource_misc', [['resourcemiscId' => 'rId']], FALSE, TRUE, TRUE);
            case 'year':
                $this->stmt->quarantine(FALSE, 'resourceyearId');
                $this->stmt->useBib('resourceyearId');
                $this->stmt->joins['resource'] = ['resourceId', 'resourceyearId'];
                $this->stmt->executeCondJoins();

                return $this->db->selectNoExecute('resource_year', [['resourceyearId' => 'rId']], FALSE, TRUE, TRUE);
            case 'timestamp':
                $this->stmt->quarantine(FALSE, 'resourcetimestampId');
                $this->stmt->useBib('resourcetimestampId');
                $this->stmt->executeCondJoins();

                return $this->db->selectNoExecute('resource_timestamp', [['resourcetimestampId' => 'rId']], FALSE, TRUE, TRUE);
            case 'maturityIndex':
                $this->stmt->quarantine(FALSE, 'resourcemiscId', FALSE);
                $this->stmt->useBib('resourcemiscId');
                $this->stmt->executeCondJoins();

                return $this->db->selectNoExecute('resource_misc', [['resourcemiscId' => 'rId']], FALSE, TRUE, TRUE);
            case 'viewsIndex':
                return $this->stmt->indices('viewsIndex');
            case 'downloadsIndex':
                return $this->stmt->indices('downloadsIndex');
            case 'popularityIndex':
                return $this->stmt->indices('popularityIndex');
            default:
                break;
        }
    }
}
