<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
    private $session;
    private $common;
    private $commonBib;
    private $user;
    private $count = 0;

    public function __construct($method = FALSE)
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $errors = FACTORY_ERRORS::getInstance();
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $badInput = FACTORY_BADINPUT::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "list"));
        if (!$this->common->resourcesExist())
        {
            $badInput->close($this->messages->text("misc", "noResources"));
        }
        //		$sq = $this->session->getVar("list_SubQuery");
// Clear previous list info except AscDesc when paging
		$ascDesc = $this->session->getVar('list_AscDesc');
        $this->session->clearArray('list');
		if (!array_key_exists('list_AscDesc', $this->vars)) // paging
		{
			$this->session->setVar('list_AscDesc', $ascDesc);
		}
        //		$this->session->setVar("list_SubQuery", $sq);
        $this->session->delVar("mywikindx_PagingStart");
        $this->session->delVar("mywikindx_PagingStartAlpha");
        $linksInfo['info'] = $this->commonBib->displayBib();
        GLOBALS::setTplVar('resourceListInfo', $linksInfo);
        unset($linksInfo);
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "list"));
        if (!$method)
        {
            if (!array_key_exists('method', $this->vars))
            {
                $badInput->close($errors->text("inputError", "missing"));
            }
            if (array_key_exists('list_Order', $this->vars))
            {
                $this->session->setVar("list_Order", $this->vars['list_Order']);
            }
            elseif (!array_key_exists('type', $this->vars) || ($this->vars['type'] != 'lastMulti'))
            {
                $badInput->close($errors->text("inputError", "missing"));
            }
            $method = $this->vars['method'];
        }
        $this->session->setVar("sql_LastOrder", $this->session->getVar("list_Order"));
        if (!method_exists($this, $method))
        {
            $badInput->close($errors->text("inputError", "missing"));
        }
        if (($method != 'reorder') && !$this->session->issetVar("list_AscDesc"))
        {print 'here';
            switch ($this->session->getVar("list_Order"))
            {
                case 'creator':
                	$this->session->setVar("list_AscDesc", $this->db->asc);
				break;
                case 'title':
                	$this->session->setVar("list_AscDesc", $this->db->asc);
				break;
                case 'publisher':
                	$this->session->setVar("list_AscDesc", $this->db->asc);
				break;
                case 'year':
                	$this->session->setVar("list_AscDesc", $this->db->desc);
				break;
                case 'timestamp':
                	$this->session->setVar("list_AscDesc", $this->db->desc);
				break;
                case 'viewsIndex':
                	$this->session->setVar("list_AscDesc", $this->db->desc);
				break;
                case 'popularityIndex':
                	$this->session->setVar("list_AscDesc", $this->db->desc);
				break;
                case 'downloadsIndex':
                	$this->session->setVar("list_AscDesc", $this->db->desc);
				break;
                case 'maturityIndex':
                	$this->session->setVar("list_AscDesc", $this->db->desc);
				break;
                default:
                	$this->session->setVar("list_AscDesc", $this->db->asc);
				break;
            }
        }
        if (!$this->session->getVar("list_Order"))
        {
            $this->session->setVar("list_Order", "creator");
        }
        $this->stmt->allIds = TRUE;
        $this->{$method}();
    }
    /**
     * With a reorder list request, which type of process do we want?
     */
    public function reorder()
    {
        if (array_key_exists("list_Order", $this->vars) && $this->vars["list_Order"])
        {
            $this->session->setVar("search_Order", $this->vars["list_Order"]);
            $this->session->setVar("sql_LastOrder", $this->vars["list_Order"]);
        }
        if (array_key_exists('list_AscDesc', $this->vars))
        {
            $this->session->setVar("list_AscDesc", $this->vars['list_AscDesc']);
        }
        $this->processGeneral();
    }
    /**
     * Display titles....
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
        $orders = ['creator', 'title', 'publisher', 'year', 'timestamp', 'popularityIndex', 'viewsIndex', 'downloadsIndex', 'maturityIndex'];
        $order = $this->session->getVar("list_Order");
        if (array_search($order, $orders) === FALSE)
        {
            $errors = FACTORY_ERRORS::getInstance();
            $badInput = FACTORY_BADINPUT::getInstance();
            $badInput->close($errors->text("inputError", "invalid"));
        }
        $queryString = 'action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=' . $order;
// NB. Ordering by popularity index uses temporary tables which must be created for each call (so cannot use the shortcuts for lastMulti)
        if (($order != 'popularityIndex') && $this->lastMulti($queryString))
        {
            return;
        }
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A') || in_array($order, ['popularityIndex', 'downloadsIndex', 'viewsIndex']))
        {
            $subStmt = $this->setSubQuery();
            $this->stmt->listSubQuery($order, $queryString, $subStmt);
            $sql = $this->stmt->listList($order);
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
        $this->session->saveState(['search', 'sql', 'bookmark', 'list']);
        $this->common->display($sql, 'list');
    }
    /**
     * Quicker querying when paging
     *
     * @param string $queryString
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
     * @return boolean
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
        $this->db->ascDesc = $this->session->getVar("list_AscDesc");
        switch ($this->session->getVar("list_Order"))
        {
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
