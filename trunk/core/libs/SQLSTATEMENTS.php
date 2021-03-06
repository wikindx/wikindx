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
 * SQLSTATEMENTS
 *
 * Some default SQL statements/clauses for LIST/SEARCH operations.
 *
 * @package wikindx\core\libs\SQLSTATEMENTS
 */
class SQLSTATEMENTS
{
    /** string */
    public $totalResourceSubquery;
    /** bool */
    public $basket = FALSE;
    /** bool */
    public $exportList = TRUE;
    /** string */
    public $listMethodAscDesc = 'list_AscDesc';
    /** string */
    public $listType = 'list';
    /** string */
    public $listQuarantined = FALSE;
    /** boolean */
    public $metadataPaging = FALSE;
    /** array */
    public $conditions = [];
    /** array */
    public $conditionsOneField = [];
    /** array */
    public $joins = [];
    /** boolean */
    public $allIds = FALSE;
    /** object */
    private $db;
    /** object */
    private $session;
    /** object */
    private $common;
    /** string */
    private $countQuery = FALSE;
    /** array */
    private $listFields = ['resourceId', 'creatorSurname', 'resourceType', 'resourceTitle', 'resourceSubtitle', 'resourceShortTitle',
        'resourceTransTitle', 'resourceTransSubtitle', 'resourceTransShortTitle', 'resourceField1', 'resourceField2', 'resourceField3',
        'resourceField4', 'resourceField5', 'resourceField6', 'resourceField7', 'resourceField8', 'resourceField9', 'resourceNoSort',
        'resourceTransNoSort', 'resourceIsbn', 'resourceBibtexKey', 'resourceDoi', 'resourcetextId', 'resourcetextNote', 'resourcetextAbstract',
        'resourcetextEditUserIdNote', 'resourcetextAddUserIdNote', 'resourcetextEditUserIdAbstract',
        'resourcetextAddUserIdAbstract', 'resourceyearId', 'resourceyearYear1', 'resourceyearYear2', 'resourceyearYear3', 'resourceyearYear4',
        'resourcepageId', 'resourcepagePageStart', 'resourcepagePageEnd', 'resourcetimestampId', 'resourcetimestampTimestamp', 'resourcetimestampTimestampAdd', 'publisherId', 'publisherName',
        'publisherLocation', 'publisherType', 'collectionId', 'collectionTitle', 'collectionTitleShort', 'collectionType', 'usersId', 'usersUsername',
        'usersFullname', 'resourcemiscId', 'resourcemiscCollection', 'resourcemiscPublisher', 'resourcemiscField1', 'resourcemiscField2',
        'resourcemiscField3', 'resourcemiscField4', 'resourcemiscField5', 'resourcemiscField6', 'resourcemiscTag', 'resourcemiscAddUserIdResource',
        'resourcemiscEditUserIdResource', 'resourcemiscMaturityIndex', 'resourcemiscPeerReviewed', 'resourcemiscQuarantine', 'resourcemiscMetadata', 
        'resourcecreatorCreatorSurname', ];
    /** booolean */
    private $quickListAll = FALSE;
    /** string */
    private $browserTabID = FALSE;

    /**
     * SQLSTATEMENTS
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
    }
    /**
     * Select front page resources according to $limit resources from last $days
     *
     * @param int $days
     * @param int $limit
     *
     * @return string SQL statement ready to be executed
     */
    public function frontSetDays($days, $limit)
    {
        $this->quarantine(TRUE);
        if ($this->session->getVar('setup_UserId') && GLOBALS::getUserVar('HomeBib') && GLOBALS::getUserVar('BrowseBibliography'))
        {
            // i.e. ignore if value is ‘0’ (main wikindx bibliography) or user is readOnly
            $this->conditions[] = ['userbibliographyresourceBibliographyId' => GLOBALS::getUserVar('BrowseBibliography')];
            $this->joins['user_bibliography_resource'] = ['userbibliographyresourceResourceId', 'resourcetimestampId'];
        }
        $this->executeCondJoins();
        $this->db->formatConditions("DATE_SUB(CURRENT_DATE, INTERVAL $days DAY)<=" .
            $this->db->formatFields('resourcetimestampTimestamp'));
        $this->db->limit($limit, 0);
        $this->db->ascDesc = $this->db->desc;
        $this->db->groupBy(['resourcetimestampId', 'resourcetimestampTimestamp']);
        $this->db->orderBy('resourcetimestampTimestamp', TRUE, FALSE);
        $subQuery = $this->db->subQuery($this->db->queryNoExecute($this->db->selectNoExecute(
            'resource_timestamp',
            [['resourcetimestampId' => 'rId']]
        )), 't1');

        $split = preg_split('/\s*FROM\s*\(/u', $subQuery);
        $split = preg_split('/\)\s*AS\s+/u', $split[1]);

        $this->session->setVar("list_SubQuery", $split[0]);
        $this->db->ascDesc = $this->db->desc;
        $this->db->orderBy($this->db->formatFields('resourcetimestampTimestamp'), FALSE, FALSE);
        $this->db->orderBy($this->db->ifClause(
            $this->db->formatFields('resourcecreatorCreatorSurname'),
            'IS NOT NULL',
            $this->db->formatFields('resourcecreatorCreatorSurname'),
            $this->db->formatFields('resourceTitleSort')
        ), FALSE, FALSE);
        $this->db->orderBy($this->yearOrder(), FALSE);
        $this->db->orderBy('resourceTitleSort', TRUE, FALSE);
        $this->db->leftJoin([['resource_timestamp' => 't2']], 't2.resourcetimestampId', 't1.rId');
        $this->db->leftJoin('resource_misc', 'resourcemiscId', 't2.resourcetimestampId');
        $this->db->leftJoin('resource', 'resourceId', 't2.resourcetimestampId');
        $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 't2.resourcetimestampId');
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->leftJoin('resource_year', 'resourceyearId', 't2.resourcetimestampId');
        $this->db->leftJoin('resource_text', 'resourcetextId', 't2.resourcetimestampId');
        $this->db->leftJoin('resource_page', 'resourcepageId', 't2.resourcetimestampId');
        $this->db->leftJoin('resource_metadata', 'resourcemetadataResourceId', 't2.resourcetimestampId');
        $this->db->leftJoin('publisher', 'resourcemiscPublisher', 'publisherId');
        $this->db->leftJoin('collection', 'resourcemiscCollection', 'collectionId');
        $this->db->leftJoin('users', 'usersId', $this->db->caseWhen(
            'resourcemiscEditUserIdResource',
            'IS NOT NULL',
            'resourcemiscEditUserIdResource',
            'resourcemiscAddUserIdResource'
        ), FALSE);
        foreach ($this->listFields as $field)
        {
            if ($field == 'resourcetimestampId')
            {
                $listFields[] = 't2.' . $field;
            }
            else
            {
                $listFields[] = $field;
            }
        }
        $listQuery = $this->db->queryNoExecute($this->db->selectNoExecuteFromSubQuery(
            FALSE,
            $this->db->formatFields($listFields),
            $subQuery,
            FALSE,
            FALSE
        ));
        $resultSet = $this->db->query($listQuery);
		while ($row = $this->db->fetchRow($resultSet))
		{
			$ids[] = $row['resourceId'];
		}
		if (empty($ids)) {
			return FALSE;
		}
		$ids = array_filter($ids); // array_filter() to ensure no null ids
		$total = count($ids);
		if ($total > $limit) {
			$total = $limit;
		}
		$this->session->setVar("list_Front", $total);
        return $listQuery;
    }
    /**
     * Select front page resources according to latest $limit resources
     *
     * @param int $limit
     *
     * @return string SQL statement ready to be executed
     */
    public function frontSetNumber($limit)
    {
        $this->quarantine(TRUE);
        if ($this->session->getVar('setup_UserId') && GLOBALS::getUserVar('HomeBib') && GLOBALS::getUserVar('BrowseBibliography'))
        {
            // i.e. ignore if value is ‘0’ (main wikindx bibliography) or user is readOnly
            $this->conditions[] = ['userbibliographyresourceBibliographyId' => GLOBALS::getUserVar('BrowseBibliography')];
            $this->joins['user_bibliography_resource'] = ['userbibliographyresourceResourceId', 'resourcetimestampId'];
        }
        $this->executeCondJoins();
        $this->db->limit($limit, 0);
        $this->db->ascDesc = $this->db->desc;
        $this->db->groupBy(['resourcetimestampId', 'resourcetimestampTimestamp']);
        $this->db->orderBy('resourcetimestampTimestamp', TRUE, FALSE);
        $subQuery = $this->db->subQuery($this->db->queryNoExecute($this->db->selectNoExecute(
            'resource_timestamp',
            [['resourcetimestampId' => ' rId']]
        )), 't1');
        $split = \UTF8\mb_explode('(', $subQuery, 2);
        $split = \UTF8\mb_explode(')', $split[1]);
        $this->session->setVar("list_SubQuery", $split[0]);
        $this->db->ascDesc = $this->db->desc;
        $this->db->orderBy($this->db->formatFields('resourcetimestampTimestamp'), FALSE, FALSE);
        $this->db->orderBy($this->db->ifClause(
            $this->db->formatFields('resourcecreatorCreatorSurname'),
            'IS NOT NULL',
            $this->db->formatFields('resourcecreatorCreatorSurname'),
            $this->db->formatFields('resourceTitleSort')
        ), FALSE, FALSE);
        $this->db->orderBy($this->yearOrder(), FALSE);
        $this->db->orderBy('resourceTitleSort', TRUE, FALSE);
        $this->db->leftJoin([['resource_timestamp' => 't2']], 't2.resourcetimestampId', 't1.rId');
        $this->db->leftJoin('resource_misc', 'resourcemiscId', 't2.resourcetimestampId');
        $this->db->leftJoin('resource', 'resourceId', 't2.resourcetimestampId');
        $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 't2.resourcetimestampId');
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->leftJoin('resource_year', 'resourceyearId', 't2.resourcetimestampId');
        $this->db->leftJoin('resource_text', 'resourcetextId', 't2.resourcetimestampId');
        $this->db->leftJoin('resource_page', 'resourcepageId', 't2.resourcetimestampId');
        $this->db->leftJoin('resource_metadata', 'resourcemetadataResourceId', 't2.resourcetimestampId');
        $this->db->leftJoin('publisher', 'resourcemiscPublisher', 'publisherId');
        $this->db->leftJoin('collection', 'resourcemiscCollection', 'collectionId');
        $this->db->leftJoin('users', 'usersId', $this->db->caseWhen(
            'resourcemiscEditUserIdResource',
            'IS NOT NULL',
            'resourcemiscEditUserIdResource',
            'resourcemiscAddUserIdResource'
        ), FALSE);
        foreach ($this->listFields as $field)
        {
            if ($field == 'resourcetimestampId')
            {
                $listFields[] = 't2.' . $field;
            }
            else
            {
                $listFields[] = $field;
            }
        }
        $listFields[] = 'rId';
        $listQuery = $this->db->queryNoExecute($this->db->selectNoExecuteFromSubQuery(
            FALSE,
            $this->db->formatFields($listFields),
            $subQuery,
            FALSE,
            FALSE
        ));
        $resultSet = $this->db->query($listQuery);
		while ($row = $this->db->fetchRow($resultSet))
		{
			$ids[] = $row['rId'];
		}
		$ids = array_filter($ids); // array_filter() to ensure no null ids
		$total = count($ids);
		if ($total > $limit) {
			$total = $limit;
		}
		$this->session->setVar("list_Front", $total);
        return $listQuery;
    }
    /**
     * Produce a SQL statement for exporting bibliographies that is not LIMITed or paged alphabetically -- i.e. all resources in the query are gathered.
     * The SQL statement is base64_encoded and returned to the calling script
     * Large numbers of rows can exhaust PHP memory so we return an array of query statements to be executed. The number of statements is set by $totalPossible.
     * The calling script should cycle through these statements and concatenate the results of each.
     * By trial and error, c.1500 rows seems to be the limit with PHP memory of 64M
     *
     * @return string
     */
    public function getExportSql()
    {
        $totalPossible = WIKINDX_MAX_WRITECHUNK;
        if (!is_array($this->session->getVar("list_AllIds")) || ($this->session->getVar("list_AllIds") == 'all'))
        {
            $total = $this->db->selectCountOnly("resource", "resourceId");
        }
        else
        {
            $total = count($this->session->getVar("list_AllIds"));
        }
        $stmt = $this->session->getVar("sql_ListStmt");
        // watch out for exhausting PHP memory – we divide into multiple SQL statements
        $start = 0;
        while ($start <= $total)
        {
            $limit = $this->db->limit($totalPossible, $start, TRUE); // "LIMIT $limitStart, $limit";
            $listQuery[] = $stmt . $limit;
            $start += $totalPossible;
        }

        return base64_encode(serialize($listQuery));
    }
    /**
     * Create LIST statement for QUICK LIST ALL queries and basket operations
     *
     * @param string $order Default is 'creator'
     * @param string $table Default is 'resource'
     * @param false|string $subQ Default is FALSE
     *
     * @return mixed SQL statement ready to be executed. FALSE if there is no statement to be formed
     */
    public function listList($order = 'creator', $table = 'resource', $subQ = FALSE)
    {
    	if ($this->basket) {
			$allIdsVar = "list_AllIdsBasket";
		} else {
			$allIdsVar = "list_AllIds";
		}
        if (!$allIds = GLOBALS::getTempStorage($allIdsVar))
        {
            $allIds = $this->session->getVar($allIdsVar);
        }
        if (!$this->allIds && !$allIds)
        {
            return FALSE; // Perhaps browsing metadata keywords where the keyword is not attached to resources but only to ideas.
        }
        $totalSubQuery = FALSE;
        if (!$this->db->ascDesc = GLOBALS::getTempStorage($this->listMethodAscDesc))
        {
            $this->db->ascDesc = $this->session->getVar($this->listMethodAscDesc);
        }
        $np = [];
        $limit = FALSE;
        if ((GLOBALS::getUserVar('PagingStyle') != 'A') || ((GLOBALS::getUserVar('PagingStyle') == 'A') &&
            !in_array($order, ['title', 'creator', 'attachments'])))
        {
            if (($order != 'popularityIndex') && ($order != 'viewsIndex'))
            { // limit is set in the inner statement
                $limit = $this->db->limit(GLOBALS::getUserVar('Paging'), $this->common->pagingObject->start, TRUE); // "LIMIT $limitStart, $limit";
            }
        }
        if ($order == 'title')
        {
            if (!$this->exportList && (GLOBALS::getUserVar('PagingStyle') == 'A'))
            {
                $this->quickListAll = TRUE;
                $this->pagingAlphaCondition($order);
                $this->executeCondJoins();
                if ($subQ)
                {
                    $totalSubQuery =
                        $this->db->subQuery($this->db->selectNoExecuteFromSubQuery(
                            FALSE,
                            [['resourceId' => 'rId']],
                            $this->db->from . ' ' . $subQ,
                            FALSE,
                            TRUE,
                            TRUE
                        ), 't1', TRUE, TRUE);
                }
                else
                {
                    $totalSubQuery = $this->db->subQuery($this->db->selectNoExecute(
                        $table,
                        [['resourceId' => 'rId']],
                        FALSE,
                        TRUE,
                        TRUE
                    ), 't1', TRUE, TRUE);
                }
            }
            elseif (!$this->allIds)
            {
                $this->db->formatConditions($this->db->formatFields('resourceId') .
                    $this->db->inClause(implode(',', $allIds)));
            }
        }
        elseif ($order == 'creator')
        {
            if (!$this->exportList && (GLOBALS::getUserVar('PagingStyle') == 'A'))
            {
                $this->quickListAll = TRUE;
                $this->pagingAlphaCondition($order);
                $this->executeCondJoins();
                if ($subQ)
                {
                    $totalSubQuery =
                        $this->db->subQuery($this->db->selectNoExecuteFromSubQuery(
                            FALSE,
                            [['resourcecreatorResourceId' => 'rId']],
                            $this->db->from . ' ' . $subQ,
                            FALSE,
                            TRUE,
                            TRUE
                        ), 't1', TRUE, TRUE);
                }
                else
                {
                    $totalSubQuery = $this->db->subQuery($this->db->selectNoExecute(
                        $table,
                        [['resourcecreatorResourceId' => 'rId']],
                        FALSE,
                        TRUE,
                        TRUE
                    ), 't1', TRUE, TRUE);
                }
            }
            elseif (!$this->allIds)
            {
                $this->db->formatConditions($this->db->formatFields('resourceId') .
                    $this->db->inClause(implode(',', $allIds)));
            }
        }
        elseif (!in_array($order, ['popularityIndex', 'downloadsIndex', 'viewsIndex']) && !$this->allIds)
        { // all other orders
            $this->db->formatConditions($this->db->formatFields('resourceId') .
                $this->db->inClause(implode(',', $allIds)));
        }
        $this->listJoins($order);
        if (($order == 'popularityIndex') || ($order == 'downloadsIndex') || ($order == 'viewsIndex'))
        {
            $this->listFields[] = 'index';
            $listQuery = $this->db->queryNoExecute($this->db->selectNoExecuteFromSubQuery(
                FALSE,
                $this->db->formatFields($this->listFields),
                $this->totalResourceSubquery,
                FALSE,
                FALSE
            ));
        }
        elseif ($totalSubQuery)
        {
            $listQuery = $this->db->queryNoExecute($this->db->selectNoExecuteFromSubQuery(
                FALSE,
                $this->db->formatFields($this->listFields),
                $totalSubQuery,
                FALSE,
                FALSE
            ));
        }
        else
        {
            $listQuery = $this->db->queryNoExecute($this->db->selectNoExecute('resource', $this->listFields));
        }
        $listStmt = $this->basket ? 'sql_ListStmtBasket' : 'sql_ListStmt';
        $this->session->setVar($listStmt, $listQuery);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage([$listStmt => $listQuery]);
        }
        return $listQuery . $limit;
    }
    /**
     * Create LIST statement for QUICKSEARCH
     *
     * @param string $order Default is 'creator'
     * @param string $table Default is 'resource'
     * @param false|string $subQ Default is FALSE
     *
     * @return mixed SQL statement ready to be executed. FALSE if there is no statement to be formed
     */
    public function listListQS($order = 'creator', $table = 'resource', $subQ = FALSE)
    {
        $this->listJoins = [];
        if (!$allIds = GLOBALS::getTempStorage('list_AllIds'))
        {
            $allIds = $this->session->getVar("list_AllIds");
        }
        if (!$this->allIds && !$allIds)
        {
            return FALSE; // Perhaps browsing metadata keywords where the keyword is not attached to resources but only to ideas.
        }
        $totalSubQuery = FALSE;
        if (!$this->db->ascDesc = GLOBALS::getTempStorage($this->listMethodAscDesc))
        {
            $this->db->ascDesc = $this->session->getVar($this->listMethodAscDesc);
        }
        $np = [];
        $limit = FALSE;
        if ((GLOBALS::getUserVar('PagingStyle') != 'A') || ((GLOBALS::getUserVar('PagingStyle') == 'A') &&
            !in_array($order, ['title', 'creator', 'attachments'])))
        {
            if (($order != 'popularityIndex') && ($order != 'viewsIndex'))
            { // limit is set in the inner statement
                $limit = $this->db->limit(GLOBALS::getUserVar('Paging'), $this->common->pagingObject->start, TRUE); // "LIMIT $limitStart, $limit";
            }
        }
        else
        {
            $this->pagingAlphaCondition($order);
            $this->executeCondJoins();
        }
        $this->db->formatConditions($this->db->formatFields('resourceId') .
                $this->db->inClause(implode(',', $allIds)));
        $this->listJoins($order, TRUE);
        if (($order == 'popularityIndex') || ($order == 'downloadsIndex') || ($order == 'viewsIndex'))
        {
            $this->listFields[] = 'index';
            $listQuery = $this->db->queryNoExecute($this->db->selectNoExecuteFromSubQuery(
                FALSE,
                $this->db->formatFields($this->listFields),
                $this->totalResourceSubquery,
                FALSE,
                FALSE
            ));
        }
        elseif ($totalSubQuery)
        {
            $listQuery = $this->db->queryNoExecute($this->db->selectNoExecuteFromSubQuery(
                FALSE,
                $this->db->formatFields($this->listFields),
                $totalSubQuery,
                FALSE,
                FALSE
            ));
        }
        else
        {
            $listQuery = $this->db->queryNoExecute($this->db->selectNoExecute('resource', $this->listFields));
        }
        $this->session->setVar("sql_ListStmt", $listQuery);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_ListStmt' => $listQuery]);
        }

        return $listQuery . $limit;
    }
    /**
     * Create subquery initial order SELECT statement with minimal fields.
     * Function specific to core/modules/list/LISTRESOURCES.php (Quick List ALL operations), LISTSOMERESOURCES, and core/modules/basket/BASKET.php
     *
     * When ordering by viewsIndex or popularityIndex, each resource is guaranteed to have a vlaue for each of these fields.
     * This means we can use an inner SELECT to order on one of those fields and to limit the results – finer ordering on that field and
     * other fields is accomplished in the outer SELECT. This has a dramatic effect on speed . . .
     *
     * @param false|string $order Default is FALSE
     * @param false|string $queryString Default is FALSE
     * @param false|string $totalSubQuery Default is FALSE
     * @param string $table default is 'resource'
     * @param false|string $subQ SQL subquery for input to COUNT operations
     *
     * @return bool TRUE if resources found, FALSE otherwise
     */
    public function listSubQuery($order = FALSE, $queryString = FALSE, $totalSubQuery = FALSE, $table = 'resource', $subQ = FALSE)
    {
        $ids = [];
		if ($this->basket) {
			$pagingTotalVar = "setup_PagingTotalBasket";
			$allIdsVar = "list_AllIdsBasket";
		} else {
			$pagingTotalVar = "setup_PagingTotal";
			$allIdsVar = "list_AllIds";
		}
        if ($this->metadataPaging)
        {
            $this->common->metadataPaging = TRUE;
        }
        if (($order == 'popularityIndex') || ($order == 'downloadsIndex') || ($order == 'viewsIndex'))
        {
            $this->totalResourceSubquery = $this->db->subQuery($totalSubQuery, 't1', TRUE, TRUE);
            $this->session->delVar($allIdsVar);
			GLOBALS::unsetTempStorage([$allIdsVar]);
        }
		if ($this->allIds and !GLOBALS::getUserVar('BrowseBibliography'))
		{
			$total = $this->db->selectCountOnly("resource", "resourceId");
			$this->session->setVar($pagingTotalVar, $total);
			$this->session->setVar($allIdsVar, 'all');
			if ($this->browserTabID) {
				GLOBALS::setTempStorage([$pagingTotalVar => $total, $allIdsVar => 'all']);
			}
		}
		else
		{
			$resultSet = $this->db->query($totalSubQuery);
			while ($row = $this->db->fetchRow($resultSet))
			{
				$ids[] = $row['rId'];
			}
			if (empty($ids))
			{
				return FALSE;
			}
			$ids = array_filter($ids); // array_filter() to ensure no null ids
			$total = count($ids);
			$this->session->setVar($pagingTotalVar, $total);
			$this->session->setVar($allIdsVar, $ids);
			$this->session->delVar("sql_CountAlphaStmt");
			if ($this->browserTabID)
			{
				GLOBALS::setTempStorage([$pagingTotalVar => $total, $allIdsVar => $ids]);
				GLOBALS::unsetTempStorage(['sql_CountAlphaStmt']);
			}
		}
        $this->common->pagingStyle(
            $this->countQuery,
            $this->listType,
            $order,
            $queryString,
            $this->conditions,
            $this->joins,
            $this->conditionsOneField,
            $table,
            $subQ
        );
        if (($order == 'popularityIndex') || ($order == 'viewsIndex'))
        {
            $this->db->ascDesc = $this->session->getVar("list_AscDesc");
            $orderBy = $this->db->orderBy('index', TRUE, FALSE, TRUE);
            $limit = $this->db->limit(GLOBALS::getUserVar('Paging'), $this->common->pagingObject->start, TRUE);
            $clause = ' ' . $orderBy . ' ' . $limit;
            $this->totalResourceSubquery = str_replace('W!K!NDXW!K!NDXW!K!NDX', $clause, $this->totalResourceSubquery);
        }
        if (!$ids = GLOBALS::getTempStorage($allIdsVar))
        {
            $ids = $this->session->getVar($allIdsVar);
        }
        if (is_bool($ids) || empty($ids))
        { // FALSE
            return FALSE;
        }
        return TRUE;
    }
    /**
     * Create subquery initial order SELECT statement with minimal fields.
     * Function specific to core/modules/list/QUICKSEARCH.php
     *
     * @param false|string $queryString Default is FALSE
     * @param false|string $totalSubQuery Default is FALSE
     * @param false|string $subQ SQL subquery for input to COUNT operations
     * @param false|string $type (default FALSE) otherwise 'or', 'and', 'not', 'final'
     *
     * @return bool TRUE if resources found, FALSE otherwise
     */
    public function quicksearchSubQuery($queryString = FALSE, $totalSubQuery = FALSE, $subQ = FALSE, $type = FALSE)
    {
        $ids = [];
        if (!$order = GLOBALS::getTempStorage('search_Order'))
        {
            $order = $this->session->getVar("search_Order");
        }
        if ($type != 'final')
        {
            $resultSet = $this->db->query($totalSubQuery);
            while ($row = $this->db->fetchRow($resultSet))
            {
                $ids[] = $row['rId'];
            }
            if (empty($ids))
            {
                return FALSE;
            }
            $ids = array_filter($ids); // array_filter() to ensure no null ids
            switch ($type) {
                case 'or':
                    $this->session->setVar("list_AllIds", $ids);
                    GLOBALS::setTempStorage(['list_AllIds' => $ids]);

                    return TRUE;
                case 'and':
                    if (!$pastIds = GLOBALS::getTempStorage('list_AllIds'))
                    {
                        $pastIds = $this->session->getVar("list_AllIds");
                    }
                    if ($pastIds)
                    {
                        $ids = array_intersect($ids, $pastIds);
                    }
                    $this->session->setVar("list_AllIds", $ids);
                    GLOBALS::setTempStorage(['list_AllIds' => $ids]);

                    return TRUE;
                case 'not':
                    if (!$pastIds = GLOBALS::getTempStorage('list_AllIds'))
                    {
                        $pastIds = $this->session->getVar("list_AllIds");
                    }
                    if ($pastIds)
                    {
                        $ids = array_diff($pastIds, $ids);
                    }
                    $this->session->setVar("list_AllIds", $ids);
                    GLOBALS::setTempStorage(['list_AllIds' => $ids]);

                    return TRUE;
                default:
                    return FALSE;
            }
        }
        // If we get here, $quicksearch is 'final'
        if (!$ids = GLOBALS::getTempStorage('list_AllIds'))
        {
            $ids = $this->session->getVar("list_AllIds");
        }
        if (is_bool($ids) || empty($ids))
        { // FALSE
            return FALSE;
        }
        $total = count($ids);
        $this->session->setVar("setup_PagingTotal", $total);
        if ($this->browserTabID) {
	        GLOBALS::setTempStorage(['setup_PagingTotal' => $total]);
	    }
        $this->session->delVar("sql_CountAlphaStmt");
        if ($this->browserTabID)
        {
            GLOBALS::unsetTempStorage(['sql_CountAlphaStmt']);
        }
        $this->common->pagingStyle(
            $this->countQuery,
            $this->listType,
            $order,
            $queryString,
            $this->conditions,
            $this->joins,
            $this->conditionsOneField,
            'resource',
            $subQ,
            TRUE
        );
//        if (!$this->session->getVar("list_AllIds")) {
//            return FALSE;
//        }

        return TRUE;
    }
    /**
     * Create special CASE WHEN statement when ordering by year
     *
     * @return string
     */
    public function yearOrder()
    {
        // Test for book, book_chapter and book_article
        $key = '(' . $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('book') .
            $this->db->or .
            $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('book_chapter') .
            $this->db->or .
            $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('book_article') . ')' .
            $this->db->and .
            $this->db->formatFields('resourceyearYear2') . ' IS NOT NULL ';
        $clauses[$key] = $this->db->formatFields('resourceyearYear2');
        // Test for proceedings and proceedings_article
        $key = '(' . $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('proceedings') .
            $this->db->or .
            $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('proceedings_article') . ')' .
            $this->db->and .
            $this->db->formatFields('resourceyearYear1') . ' IS NULL ';
        $clauses[$key] = $this->db->formatFields('resourceyearYear2');
        // Test for conference_paper and conference_poster
        $key = '(' . $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('conference_paper') .
            $this->db->or .
            $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('conference_poster') . ')';
        $clauses[$key] = $this->db->formatFields('resourceyearYear2');
        // Test for web_encyclopedia and web_encyclopedia_article
        $key = '(' . $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('web_encyclopedia') .
            $this->db->or .
            $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('web_encyclopedia_article') . ')' .
            $this->db->and .
            $this->db->formatFields('resourceyearYear3') . ' IS NOT NULL ';
        $clauses[$key] = $this->db->formatFields('resourceyearYear3');

        return $this->db->caseWhen($clauses, FALSE, FALSE, $this->db->formatFields('resourceyearYear1'), FALSE);
    }
    /**
     * Create SQL statements for various indices bases on stats -- called from LISTRESOURCES.php
     *
     * @param string $order
     *
     * @return string
     */
    public function indices($order)
    {
        $stats = FACTORY_STATISTICS::getInstance();
        if ($order == 'viewsIndex')
        {
            if (!$setSum = $stats->getMaxAccessRatio())
            {
                $setSum = 0.1; // Avoids divide by 0 but still produces a 0 result
            }
            
            $dateDiffClause = $this->db->dateDiffRatio('count', 'resourcetimestampTimestampAdd', 'index', '', 4);
            $sumClause = $this->db->sum('statisticsresourceviewsCount', 'count');
            $this->db->leftJoin('resource_timestamp', 'resourcetimestampId', 'statisticsresourceviewsResourceId');
            $this->db->groupBy('statisticsresourceviewsResourceId');
            $subQ = $this->db->subQuery(
                $this->db->selectNoExecute(
                    'statistics_resource_views',
                    [$sumClause, $this->db->formatFields('resourcetimestampTimestampAdd'),
                        $this->db->formatFields([['statisticsresourceviewsResourceId' => 'rId']]), ],
                    FALSE,
                    FALSE
                ),
                't',
                TRUE,
                TRUE
            );
            $fields = $this->db->formatFields('rId') . ',' . $dateDiffClause;
            $this->quarantine(FALSE, 'rId', FALSE);
            $this->useBib('rId');
            $this->db->ascDesc = $this->session->getVar("list_AscDesc");
            $indicesQuery = $this->db->selectNoExecuteFromSubQuery(FALSE, $fields, $subQ, FALSE, FALSE);
            $indicesQuery .= 'W!K!NDXW!K!NDXW!K!NDX';
        }
        elseif ($order == 'downloadsIndex')
        {
            if (!$setSum = $stats->getMaxDownloadRatio())
            {
                $setSum = 0.1;
            }
            $dateDiff = $this->db->dateDiffRatio('statisticsattachmentdownloadsCount', 'resourceattachmentsTimestamp', FALSE, 'AVG', FALSE);
            $case = $this->db->round($this->db->caseWhen(
                $this->db->formatFields('resourceattachmentsResourceId'),
                'IS NULL',
                '0',
                "$dateDiff / $setSum",
                FALSE
            ), 'index', 4);
            $this->joins['resource_attachments'] = ['resourceattachmentsResourceId', 'resourcemiscId'];
            $this->joins['statistics_attachment_downloads'] = ['statisticsattachmentdownloadsResourceId', 'resourcemiscId'];
            $this->db->groupBy(['statisticsattachmentdownloadsResourceId', 'resourcemiscId']);
            $this->quarantine(FALSE, 'resourcemiscId', FALSE);
            $this->useBib('resourcemiscId');
            $this->db->ascDesc = $this->session->getVar("list_AscDesc");
            $this->executeCondJoins();
            $indicesQuery = $this->db->queryNoExecute($this->db->selectNoExecute(
                'resource_misc',
                $this->db->formatFields([['resourcemiscId' => 'rId']]) . ', ' . $case,
                FALSE,
                FALSE
            ));
        }
        elseif ($order == 'popularityIndex')
        {
            if (!$setSumAccess = $stats->getMaxAccessRatio())
            {
                $setSumAccess = 0.1;
            }
            if (!$setSumDownload = $stats->getMaxDownloadRatio())
            {
                $setSumDownload = 0.1;
            }
            // Create temp table for resource views
            $dateDiffClause = $this->db->dateDiffRatio('count', 'resourcetimestampTimestampAdd', 'accessRatio', '', 4);
            $sumClause = $this->db->sum('statisticsresourceviewsCount', 'count');
            $this->db->leftJoin('resource_timestamp', 'resourcetimestampId', 'statisticsresourceviewsResourceId');
            $this->db->groupBy('statisticsresourceviewsResourceId');
            $subQ = $this->db->subQuery(
                $this->db->selectNoExecute(
                    'statistics_resource_views',
                    [$sumClause, $this->db->formatFields('resourcetimestampTimestampAdd'),
                        $this->db->formatFields([['statisticsresourceviewsResourceId' => 'rId']]), ],
                    FALSE,
                    FALSE
                ),
                't',
                TRUE,
                TRUE
            );
            $fields = $this->db->formatFields('rId') . ',' . $dateDiffClause;
            $selectStmt = $this->db->selectNoExecuteFromSubQuery(FALSE, $fields, $subQ, FALSE, FALSE);
            $this->db->createTempTableFromSelect('countsAR', $selectStmt);
            // Create temp table for attachment downloads
            $dateDiffClause = $this->db->dateDiffRatio('count', 'resourceattachmentsTimestamp', '', '', 4);
            $dateDiffClause = $this->db->ifClause(
                'DATEDIFF(CURRENT_DATE, `resourceattachmentsTimestamp`)',
                $this->db->equal . 0 . $this->db->or . ' NULL',
                0,
                $dateDiffClause,
                'downloadRatio'
            );
            $sumClause = $this->db->sum('statisticsattachmentdownloadsCount', 'count');
            $this->db->formatConditions(['statisticsattachmentdownloadsAttachmentId' => 'IS NOT NULL']);
            $this->db->leftJoin('resource_attachments', 'resourceattachmentsId', 'statisticsattachmentdownloadsAttachmentId');
            $this->db->groupBy('statisticsattachmentdownloadsResourceId');
            $subQ = $this->db->subQuery(
                $this->db->selectNoExecute(
                    'statistics_attachment_downloads',
                    [$sumClause, $this->db->formatFields('resourceattachmentsTimestamp'),
                        $this->db->formatFields([['statisticsattachmentdownloadsResourceId' => 'raId']]), ],
                    FALSE,
                    FALSE
                ),
                't',
                TRUE,
                TRUE
            );
            $fields = $this->db->formatFields('raId') . ',' . $dateDiffClause;
            $selectStmt = $this->db->selectNoExecuteFromSubQuery(FALSE, $fields, $subQ, FALSE, FALSE);
            $this->db->createTempTableFromSelect('countsDR', $selectStmt);

            $accessWeight = WIKINDX_POPULARITY_VIEWS_WEIGHT;
            $ratioAccess = "((" . $this->db->formatFields('accessRatio') . " / $setSumAccess)" . " * $accessWeight)";
            $downloadWeight = WIKINDX_POPULARITY_DOWNLOADS_WEIGHT;
            $ratioDownload = "((" . $this->db->formatFields('downloadRatio') . " / $setSumDownload)" . " * $downloadWeight)";
            $ratioSum = $this->db->round($ratioAccess . ' + ' . $ratioDownload, 'index', 4);
            $this->joins['countsDR'] = ['raId', 'rId'];

            $this->quarantine(FALSE, 'rId', FALSE);
            $this->useBib('rId');
            $this->db->ascDesc = $this->session->getVar("list_AscDesc");
            $this->executeCondJoins();
            $indicesQuery = $this->db->queryNoExecute($this->db->selectNoExecute(
                'countsAr',
                $this->db->formatFields('rId') . ', ' . $ratioSum,
                FALSE,
                FALSE
            ));
            $indicesQuery .= 'W!K!NDXW!K!NDXW!K!NDX';
        }

        return $indicesQuery;
    }
    /**
     * Execute any conditions and joins
     */
    public function executeCondJoins()
    {
        foreach ($this->conditions as $condition)
        {
            if (is_array($condition))
            {
                $this->db->conditionSeparator = $this->db->or;
                $this->db->formatConditions($condition);
                $this->db->conditionSeparator = $this->db->and;
            }
            else
            {
                $this->db->formatConditions($condition);
            }
        }
        foreach ($this->conditionsOneField as $field => $array)
        {
            $this->db->formatConditionsOneField($array, $field);
        }
        foreach ($this->joins as $table => $array)
        {
            if (count($array) == 3)
            { // i.e. table as alias
                $this->db->leftJoin([[$table => $array[2]]], $array[0], $array[1]);
            }
            else
            {
                $this->db->leftJoin($table, $array[0], $array[1]);
            }
        }
    }
    /**
     * Create SQL statement to exclude resources in a list being returned if they exist in a user bibliography. Used from Advanced Search.
     *
     * result is a tidied SQL condition such as:
     * (`matchField` NOT IN (
     *  SELECT `userbibliographyresourceResourceId` FROM `user_bibliography_resource`
     *  WHERE (`userbibliographyresourceBibliographyId` = 1)
     *  )
     *
     * @param string $bibId user bibliography id
     * @param string $matchField databasefield to be searched for Default 'resourceId'
     */
    public function excludeBib($bibId, $matchField = 'resourceId')
    {
        $this->db->formatConditions(['userbibliographyresourceBibliographyId' => $bibId]);
        $stmt = $this->db->selectNoExecute('user_bibliography_resource', 'userbibliographyresourceResourceId', FALSE, TRUE, TRUE);
        $this->conditions[] = $this->db->formatFields($matchField) . ' ' . $this->db->inClause($stmt, TRUE);
    }
    /**
     * Set SQL condition and join clauses if we are browsing a user bibliography to ensure that
     * listed or searched resources come only from that user bibliography.
     *
     * @param int $joinField The resource ID field on which to join the user_bibliography_resource table. Default is FALSE
     */
    public function useBib($joinField = FALSE)
    {
        if ($useBib = GLOBALS::getUserVar('BrowseBibliography'))
        {
            $this->conditions[] = ['userbibliographyresourceBibliographyId' => $useBib];
            if ($joinField)
            {
                $this->joins['user_bibliography_resource'] = ['userbibliographyresourceResourceId', $joinField];
            }
        }
    }
    /**
     * Conditions for quarantined resources
     *
     * @param bool $front Default is FALSE
     * @param string $joinId Default is 'resourceId'
     * @param bool $joinMisc Default is TRUE
     */
    public function quarantine($front = FALSE, $joinId = 'resourceId', $joinMisc = TRUE)
    {
        if (!WIKINDX_QUARANTINE)
        {
            return '';
        }
        if ($this->session->getVar("setup_Superadmin") && !$this->listQuarantined)
        {
            return '';
        }
        elseif ($front)
        {
            $this->db->formatConditions(['resourcemiscQuarantine' => 'N']);
            $this->db->leftJoin('resource_misc', 'resourcemiscId', 'resourcetimestampId');
        }
        else
        {
            $this->conditions[] = ['resourcemiscQuarantine' => 'N'];
            if ($joinMisc && !array_key_exists('resource_misc', $this->joins))
            {
                $this->joins['resource_misc'] = ['resourcemiscId', $joinId];
            }
        }
    }
    /**
     * Create table joins, orders, groups and fields for outer select statement
     *
     * @param string order
     * @param bool $QS. Default is FALSE
     * @param mixed $order
     */
    private function listJoins($order, $QS = FALSE)
    {
        if ($order == 'title')
        {
            $this->db->orderBy('resourceTitleSort', TRUE, FALSE);
            $this->db->orderBy('resourcecreatorCreatorSurname', TRUE, FALSE);
            $this->db->orderBy($this->yearOrder(), FALSE);
        }
        elseif (($order == 'creator') || ($order == 'basketExport'))
        {
            $this->db->orderBy('resourcecreatorCreatorSurname', TRUE, FALSE);
            $this->db->orderBy('resourceTitleSort', TRUE, FALSE);
            $this->db->orderBy($this->yearOrder(), FALSE);
        }
        elseif ($order == 'year')
        {
            $this->db->orderBy($this->yearOrder(), FALSE);
            $this->db->orderBy($this->db->ifClause(
                $this->db->formatFields('resourcecreatorCreatorSurname'),
                'IS NOT NULL',
                $this->db->formatFields('resourcecreatorCreatorSurname'),
                $this->db->formatFields('resourceTitleSort')
            ), FALSE, FALSE);
            $this->db->orderBy('resourceTitleSort', TRUE, FALSE);
        }
        elseif ($order == 'publisher')
        {
            $this->db->orderBy('publisherName');
            $this->db->orderBy($this->db->ifClause(
                $this->db->formatFields('resourcecreatorCreatorSurname'),
                'IS NOT NULL',
                $this->db->formatFields('resourcecreatorCreatorSurname'),
                $this->db->formatFields('resourceTitleSort')
            ), FALSE, FALSE);
            $this->db->orderBy('resourceTitleSort', TRUE, FALSE);
            $this->db->orderBy($this->yearOrder(), FALSE);
        }
        elseif ($order == 'timestamp')
        {
            $this->db->orderBy('resourcetimestampTimestamp', TRUE, FALSE);
            $this->db->orderBy($this->db->ifClause(
                $this->db->formatFields('resourcecreatorCreatorSurname'),
                'IS NOT NULL',
                $this->db->formatFields('resourcecreatorCreatorSurname'),
                $this->db->formatFields('resourceTitleSort')
            ), FALSE, FALSE);
            $this->db->orderBy('resourceTitleSort', TRUE, FALSE);
        }
        elseif ($order == 'attachments')
        {
            $this->db->orderBy('resourceattachmentsFileName', TRUE, FALSE);
        }
        elseif (in_array($order, ['maturityIndex', 'viewsIndex', 'downloadsIndex', 'popularityIndex']))
        {
            if ($order == 'maturityIndex')
            {
                $this->db->orderBy('resourcemiscMaturityIndex', TRUE, FALSE);
                $this->db->orderBy($this->db->ifClause(
                    $this->db->formatFields('resourcecreatorCreatorSurname'),
                    'IS NOT NULL',
                    $this->db->formatFields('resourcecreatorCreatorSurname'),
                    $this->db->formatFields('resourceTitleSort')
                ), FALSE, FALSE);
                $this->db->orderBy('resourceTitleSort', TRUE, FALSE);
                $this->db->orderBy($this->yearOrder(), FALSE);
            }
            else
            {
                $this->db->orderBy('index', TRUE, FALSE);
                $this->db->orderBy($this->db->ifClause(
                    $this->db->formatFields('resourcecreatorCreatorSurname'),
                    'IS NOT NULL',
                    $this->db->formatFields('resourcecreatorCreatorSurname'),
                    $this->db->formatFields('resourceTitleSort')
                ), FALSE, FALSE);
                $this->db->orderBy('resourceTitleSort', TRUE, FALSE);
                $this->db->orderBy($this->yearOrder(), FALSE);
            }
        }
        $this->db->groupBy('resourceId');
        if (!$QS)
        {
            if (!$this->exportList && (GLOBALS::getUserVar('PagingStyle') == 'A') && (($order == 'creator') || ($order == 'title')))
            {
                $this->db->leftJoin('resource', 'resourceId', 'rId');
            }
            $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'resourceId');
            $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        }
        elseif ($QS && (GLOBALS::getUserVar('PagingStyle') != 'A'))
        {
            $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'resourceId');
            $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        }
        elseif ($QS && ($order != 'creator') && ($order != 'title'))
        {
            $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'resourceId');
            $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        }
        $this->db->leftJoin('resource_misc', 'resourcemiscId', 'resourceId');
        $this->db->leftJoin('resource_timestamp', 'resourcetimestampId', 'resourceId');
        $this->db->leftJoin('resource_year', 'resourceyearId', 'resourceId');
        $this->db->leftJoin('resource_text', 'resourcetextId', 'resourceId');
        $this->db->leftJoin('resource_page', 'resourcepageId', 'resourceId');
        $this->db->leftJoin('resource_metadata', 'resourcemetadataResourceId', 'resourceId');
        $this->db->leftJoin('publisher', 'resourcemiscPublisher', 'publisherId');
        $this->db->leftJoin('collection', 'resourcemiscCollection', 'collectionId');
        $this->db->leftJoin('users', 'usersId', $this->db->caseWhen(
            'resourcemiscEditUserIdResource',
            'IS NOT NULL',
            'resourcemiscEditUserIdResource',
            'resourcemiscAddUserIdResource'
        ), FALSE);
        if ($order == 'attachments')
        {
            $this->db->leftJoin('resource_attachments', 'resourceattachmentsResourceId', 'resourceId');
            $this->listFields[] = 'resourceattachmentsId';
            $this->listFields[] = 'resourceattachmentsFileName';
            $this->listFields[] = 'resourceattachmentsHashFilename';
            $this->listFields[] = 'resourceattachmentsResourceId';
        }
    }
    /**
     * Set SQL conditions for title and creator order when using pagingAlpha
     *
     * @param string $order
     */
    private function pagingAlphaCondition($order)
    {
        if ((GLOBALS::getUserVar('PagingStyle') != 'A') || empty($this->common->pagingObject->pagingArray))
        {
            return;
        }
        if ($order == 'title')
        {
            if ($this->common->pagingObject->start >= $this->common->pagingObject->sizeOfPA)
            {
                $this->common->pagingObject->start = 0;
            }
            foreach ($this->common->pagingObject->pagingArray[$this->common->pagingObject->start] as $letter)
            {
                // '??'  force this to 26th element and display all non-alpha titles at the end
                if ($letter == '??')
                {
                    $conditions[] = $this->db->formatFields('resourceTitleSort') . ' ' . $this->db->regexp(FALSE, '^[^A-Z]', FALSE);
                }
                else
                {
                    $conditions[] = $this->db->upper('resourceTitleSort') . ' ' . $this->db->like(FALSE, $letter, '%');
                }
            }
            if (isset($conditions))
            {
                $this->conditions[] = implode($this->db->or, $conditions);
            }
        }
        elseif ($order == 'creator')
        {
            if ($this->common->pagingObject->start >= $this->common->pagingObject->sizeOfPA)
            {
                $this->common->pagingObject->start = 0;
            }
            foreach ($this->common->pagingObject->pagingArray[$this->common->pagingObject->start] as $letter)
            {
                if (($letter == '#') && !$this->quickListAll)
                {
                    $conditions[] = $this->db->formatFields('resourcecreatorCreatorSurname') . ' IS NULL ';
                }
                elseif ($letter == '#')
                {
                    $conditions[] = $this->db->formatFields('resourcecreatorCreatorSurname') . ' IS NULL ';
                }
                elseif (($letter == '??') && !$this->quickListAll)
                {
                    $conditions[] = $this->db->upper('resourcecreatorCreatorSurname', FALSE) . ' ' .
                        $this->db->regexp(FALSE, '^[^A-Z]', FALSE);
                }
                elseif ($letter == '??')
                {
                    $conditions[] = $this->db->upper('resourcecreatorCreatorSurname', FALSE) . ' ' .
                        $this->db->regexp(FALSE, '^[^A-Z]', FALSE);
                }
                elseif (!$this->quickListAll)
                {
                    $conditions[] = $this->db->upper('resourcecreatorCreatorSurname', FALSE) . ' ' .
                        $this->db->like(FALSE, $letter, '%');
                }
                else
                {
                    $conditions[] = $this->db->upper('resourcecreatorCreatorSurname', FALSE) . ' ' .
                        $this->db->like(FALSE, $letter, '%');
                }
            }
            if (isset($conditions))
            {
                $this->conditions[] = implode($this->db->or, $conditions);
            }
        }
        elseif ($order == 'attachments')
        {
            if ($this->common->pagingObject->start >= $this->common->pagingObject->sizeOfPA)
            {
                $this->common->pagingObject->start = 0;
            }
            foreach ($this->common->pagingObject->pagingArray[$this->common->pagingObject->start] as $letter)
            {
                // '??'  force this to 26th element and display all non-alpha filenames at the end
                if ($letter == '??')
                {
                    $conditions[] = $this->db->formatFields('resourceattachmentsFileName') . ' ' . $this->db->regexp(FALSE, '^[^A-Z]', FALSE);
                }
                else
                {
                    $conditions[] = $this->db->upper('resourceattachmentsFileName') . ' ' . $this->db->like(FALSE, $letter, '%');
                }
            }
            if (isset($conditions))
            {
                $this->conditions[] = implode($this->db->or, $conditions);
            }
        }
    }
}
