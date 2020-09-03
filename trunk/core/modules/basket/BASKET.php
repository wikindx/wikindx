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

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
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
                $this->session->setVar("list_Order", 'creator');
        }
    }
    /**
     * Add resource to basket
     */
    public function init()
    {
        $basket = $this->session->getVar("basket_List", []);
        if (array_key_exists('resourceId', $this->vars)) {
            $resourceId = $this->vars['resourceId'];
            if (array_search($resourceId, $basket) === FALSE) {
                $basket[] = $resourceId;
            }
        }
        // Ensure array is unique
        array_unique($basket);
        $this->session->setVar("basket_List", $basket);
        $this->session->saveState('basket');
        // send back to view this resource with success message
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "resource", "RESOURCEVIEW.php"]));
        $resource = new RESOURCEVIEW();
        $resource->init($this->session->getVar("sql_LastSolo"));
        GLOBALS::addTplVar('content', $this->success->text("basketAdd"));
    }
    /**
     * Remove resource from basket
     */
    public function remove()
    {
        $basket = $this->session->getVar("basket_List", []);
        $resourceId = $this->vars['resourceId'];
        if (($key = array_search($resourceId, $basket)) !== FALSE) {
            unset($basket[$key]);
        }
        $this->session->setVar("basket_List", $basket);
        $this->session->saveState('basket');
        // send back to view this resource with success message
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "resource", "RESOURCEVIEW.php"]));
        $resource = new RESOURCEVIEW();
        $resource->init($this->session->getVar("sql_LastSolo"));
        GLOBALS::addTplVar('content', $this->success->text("basketRemove"));
    }
    /**
     * Reorder the view with list_AscDesc session variable
     */
    public function reorder()
    {
        $this->session->setVar("list_AscDesc", $this->vars['list_AscDesc']);
        $this->view();
    }
    /**
     * View the basket
     */
    public function view()
    {
        $sql = FALSE;
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $queryString = 'action=basket_BASKET_CORE&method=view';
        $bl = $this->session->getVar("basket_List");
        $sizeOfbl = is_array($bl) ? count($bl) : 0;
        $this->session->setVar("setup_PagingTotal", $sizeOfbl);
        $this->pagingObject = FACTORY_PAGING::getInstance();
        $this->pagingObject->queryString = $queryString;
        $this->pagingObject->getPaging();
        $this->common->pagingObject = $this->pagingObject;
        GLOBALS::setTplVar('heading', $this->messages->text('heading', 'basket'));
        if (array_key_exists('list_Order', $this->vars)) {
            $this->session->setVar("list_Order", $this->vars['list_Order']);
        }
        $this->session->delVar("mywikindx_PagingStart");
        $this->session->delVar("mywikindx_PagingStartAlpha");
        if ($this->lastMulti($queryString)) {
            return;
        }
        if (!array_key_exists('PagingStart', $this->vars) || (GLOBALS::getUserVar('PagingStyle') == 'A')) {
            $this->session->delVar("list_PagingAlphaLinks");
            $this->session->delVar("list_AllIds");
            $this->session->setVar("list_AllIds", $this->session->getVar("basket_List"));
            $sql = $this->returnBasketSql($queryString);
        } else {
            $sql = $this->quickQuery($queryString);
        }
        if (!$sql) {
            $errors = FACTORY_ERRORS::getInstance();
            $badInput = FACTORY_BADINPUT::getInstance();
            $badInput->close($errors->text("inputError", "invalid"));
        }
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        // Turn on the 'add bookmark' menu item
        $this->session->setVar("bookmark_DisplayAdd", TRUE);
        $this->session->saveState(['list', 'basket', 'bookmark']);
        $this->common->display($sql, 'basket');
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
            $order = $this->session->getVar("list_Order");
        }
        $subStmt = $this->setSubQuery($this->session->getVar("basket_List"));
        $this->stmt->listSubQuery($order, $queryString, $subStmt);

        return $this->stmt->listList($this->session->getVar("list_Order"));
    }
    /**
     * Delete the basket
     *
     * @param bool $confirm Default FALSE
     *
     * @return string
     */
    public function delete($confirm = FALSE)
    {
        if ($confirm) {
            $this->session->clearArray('basket');
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "libs", "FRONT.php"]));
            $obj = new FRONT($this->db);
            $obj->message = $this->success->text("basketDelete");

            return $obj->display();
        }
        // Ask for confirmation first
        GLOBALS::setTplVar('heading', $this->messages->text('heading', 'basketDelete'));

        $pString = \FORM\formHeader('basket_BASKET_CORE');
        $pString .= \FORM\hidden('method', 'deleteConfirm');
        $pString .= \FORM\formSubmit($this->messages->text("submit", "Confirm")) . \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Confirm screen for the delete action
     */
    public function deleteConfirm()
    {
        $this->session->clearArray('basket');
        $front = new FRONT($this->success->text("basketDelete")); // __construct() runs on autopilot
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
        if (!$this->session->getVar("list_Order")) {
            $this->session->setVar("list_Order", "creator");
        }
        $this->db->ascDesc = $this->session->getVar("list_AscDesc");
        switch ($this->session->getVar("list_Order")) {
            case 'title':
                $this->stmt->quarantine(FALSE, 'resourceId');
                $this->stmt->useBib('resourceId');
                $this->stmt->conditionsOneField['resourceId'] = $ids;
                $this->stmt->executeCondJoins();
                if (GLOBALS::getUserVar('PagingStyle') == 'A') {
                    return $this->db->selectNoExecute('resource', ['resourceTitleSort', ['resourceId' => 'rId']], FALSE, TRUE, TRUE);
                } else {
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
