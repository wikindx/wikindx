<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * SQLSTATEMENTS
 *
 * Some default SQL statements/clauses for LIST/SEARCH operations.
 *
 * @package wikindx\core\sql
 */
class SQLSTATEMENTS
{
    /** string */
    public $totalResourceSubquery;
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
        'resourcetextUrls', 'resourcetextUrlText', 'resourcetextEditUserIdNote', 'resourcetextAddUserIdNote', 'resourcetextEditUserIdAbstract',
        'resourcetextAddUserIdAbstract', 'resourceyearId', 'resourceyearYear1', 'resourceyearYear2', 'resourceyearYear3', 'resourceyearYear4',
        'resourcepageId', 'resourcepagePageStart', 'resourcepagePageEnd', 'resourcesummaryId', 'resourcesummaryQuotes', 'resourcesummaryParaphrases',
        'resourcesummaryMusings', 'resourcetimestampId', 'resourcetimestampTimestamp', 'resourcetimestampTimestampAdd', 'publisherId', 'publisherName',
        'publisherLocation', 'publisherType', 'collectionId', 'collectionTitle', 'collectionTitleShort', 'collectionType', 'usersId', 'usersUsername',
        'usersFullname', 'resourcemiscId', 'resourcemiscCollection', 'resourcemiscPublisher', 'resourcemiscField1', 'resourcemiscField2',
        'resourcemiscField3', 'resourcemiscField4', 'resourcemiscField5', 'resourcemiscField6', 'resourcemiscTag', 'resourcemiscAddUserIdResource',
        'resourcemiscEditUserIdResource', 'resourcemiscMaturityIndex', 'resourcemiscPeerReviewed', 'resourcemiscQuarantine',
        'resourcecreatorCreatorSurname', ];
    /** booolean */
    private $quickListAll = FALSE;

    /**
     * SQLSTATEMENTS
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->common = FACTORY_LISTCOMMON::getInstance();
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

        $this->session->setVar("list_SubQuery", base64_encode($split[0]));
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
        $this->db->leftJoin('resource_summary', 'resourcesummaryId', 't2.resourcetimestampId');
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
        $this->executeCondJoins();
        $this->db->limit($limit, 0);
        $this->db->ascDesc = $this->db->desc;
        $this->db->groupBy(['resourcetimestampId', 'resourcetimestampTimestamp']);
        $this->db->orderBy('resourcetimestampTimestamp', TRUE, FALSE);
        $subQuery = $this->db->subQuery($this->db->queryNoExecute($this->db->selectNoExecute(
            'resource_timestamp',
            [['resourcetimestampId' => ' rId']]
        )), 't1');
        $split = UTF8::mb_explode('(', $subQuery, 2);
        $split = UTF8::mb_explode(')', $split[1]);
        $this->session->setVar("list_SubQuery", base64_encode($split[0]));
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
        $this->db->leftJoin('resource_summary', 'resourcesummaryId', 't2.resourcetimestampId');
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

        return $listQuery;
    }
    /**
     * Produce a SQL statement for exporting bibliographies that is not LIMITed or paged alphabetically -- i.e. all resources in the query are gathered.
     * The SQL statement is base64_encoded and returned to the calling script (usually plugins/importexportbib/EXPORTCOMMON::getSQL())
     * Large numbers of rows can exhaust PHP memory so we return an array of query statements to be executed. The number of statements is set by $totalPossible.
     * The calling script should cycle through these statements and concatenate the results of each.
     * By trial and error, c.1500 rows seems to be the limit with PHP memory of 64M
     *
     * @return string
     */
    public function getExportSql()
    {
        $totalPossible = WIKINDX_MAX_WRITECHUNK;
        if ($this->session->getVar("list_AllIds") == 'all')
        {
            $total = $this->db->selectFirstField('database_summary', 'databasesummaryTotalResources');
        }
        else
        {
            $total = 0;
            $tmp = base64_decode($this->session->getVar("list_AllIds", ""));
            if ($tmp !== FALSE)
            {
                $tmp = unserialize($tmp);
            }
            if ($tmp !== FALSE)
            {
                $total = count($tmp);
            }
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
     * @param string|FALSE $subQ Default is FALSE
     *
     * @return mixed SQL statement ready to be executed. FALSE if there is no statement to be formed
     */
    public function listList($order = 'creator', $table = 'resource', $subQ = FALSE)
    {
        if (!$this->allIds && !$this->session->getVar("list_AllIds"))
        {
            return FALSE; // Perhaps browsing metadata keywords where the keyword is not attached to resources but only to ideas.
        }
        $totalSubQuery = FALSE;
        $this->db->ascDesc = $this->session->getVar($this->listMethodAscDesc);
        $np = [];
        $limit = FALSE;
        if ((GLOBALS::getUserVar('PagingStyle') != 'A') || ((GLOBALS::getUserVar('PagingStyle') == 'A') && 
        	!in_array($order, ['title', 'creator', 'attachments'])))
        {
        	if ($order != 'popularityIndex') // limit is set in the inner statement
        	{
            	$limit = $this->db->limit(GLOBALS::getUserVar('Paging'), $this->common->pagingObject->start, TRUE); // "LIMIT $limitStart, $limit";
            }
        }
        if ($order == 'title')
        {
            if (GLOBALS::getUserVar('PagingStyle') == 'A')
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
                    $this->db->inClause(implode(',', unserialize(base64_decode($this->session->getVar("list_AllIds"))))));
            }
        }
        elseif ($order == 'creator')
        {
            if (GLOBALS::getUserVar('PagingStyle') == 'A')
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
                    $this->db->inClause(implode(',', unserialize(base64_decode($this->session->getVar("list_AllIds"))))));
            }
        }
        elseif (!in_array($order, ['popularityIndex', 'downloadsIndex', 'viewsIndex']) && !$this->allIds)
        { // all other orders
            $this->db->formatConditions($this->db->formatFields('resourceId') .
                $this->db->inClause(implode(',', unserialize(base64_decode($this->session->getVar("list_AllIds"))))));
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
        $this->session->setVar("sql_ListStmt", $listQuery);

        return $listQuery . $limit;
    }
    /**
     * Create subquery initial order SELECT statement with minimal fields.
     * Function specific to core/modules/list/LISTRESOURCES.php (Quick List ALL operations) and core/modules/basket/BASKET.php
     *
     * @param string|FALSE $order Default is FALSE
     * @param string|FALSE $queryString Default is FALSE
     * @param string|FALSE $totalSubQuery Default is FALSE
     * @param string $table default is 'resource'
     * @param string|FALSE $subQ SQL subquery for input to COUNT operations
     *
     * @return bool TRUE if resources found, FALSE otherwise
     */
    public function listSubQuery($order = FALSE, $queryString = FALSE, $totalSubQuery = FALSE, $table = 'resource', $subQ = FALSE)
    {
        $ids = [];
        if ($this->metadataPaging)
        {
            $this->common->metadataPaging = TRUE;
        }
        if (($order == 'popularityIndex') || ($order == 'downloadsIndex') || ($order == 'viewsIndex'))
        {
            $this->totalResourceSubquery = $this->db->subQuery($totalSubQuery, 't1', TRUE, TRUE);
            $this->session->delVar("list_AllIds");
        }
        if (!$this->session->getVar("list_AllIds"))
        {
            if ($this->allIds and !$this->session->getVar("mywikindx_Bibliography_use"))
            {
                $this->session->setVar("setup_PagingTotal", $this->db->selectFirstField('database_summary', 'databasesummaryTotalResources'));
                $this->session->setVar("list_AllIds", 'all');
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
                $this->session->setVar("setup_PagingTotal", count($ids));
                $this->session->setVar("list_AllIds", base64_encode(serialize($ids)));
                $this->session->delVar("sql_CountAlphaStmt");
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
        if ($order == 'popularityIndex')
        {
            $this->db->ascDesc = $this->session->getVar("list_AscDesc");
        	$orderBy = $this->db->orderBy('index', TRUE, FALSE, TRUE);
        	$limit = $this->db->limit(GLOBALS::getUserVar('Paging'), $this->common->pagingObject->start, TRUE);
        	$clause = ' ' . $orderBy . ' ' . $limit;
        	$this->totalResourceSubquery = str_replace('W!K!NDXW!K!NDXW!K!NDX', $clause, $this->totalResourceSubquery);
        }
        if (!$this->session->getVar("list_AllIds"))
        {
            return FALSE;
        }

        return TRUE;
    }
    /**
     * Create subquery initial order SELECT statement with minimal fields.
     * Function specific to core/modules/list/QUICKSEARCH.php
     *
     * @param string|FALSE $queryString Default is FALSE
     * @param string|FALSE $totalSubQuery Default is FALSE
     * @param string|FALSE $subQ SQL subquery for input to COUNT operations
     * @param string|FALSE $type (default FALSE) otherwise 'or', 'and', 'not', 'final'
     *
     * @return bool TRUE if resources found, FALSE otherwise
     */
    public function quicksearchSubQuery($queryString = FALSE, $totalSubQuery = FALSE, $subQ = FALSE, $type = FALSE)
    {
        $ids = [];
        $order = $this->session->getVar("search_Order");
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
			switch ($type)
			{
				case 'or':
					$this->session->setVar("list_AllIds", base64_encode(serialize($ids)));
					return TRUE;
				case 'and':
					if ($this->session->getVar("list_AllIds"))
					{
						$pastIds = unserialize(base64_decode($this->session->getVar("list_AllIds")));
						$ids = array_intersect($ids, $pastIds);
					}
					$this->session->setVar("list_AllIds", base64_encode(serialize($ids)));
					return TRUE;
				case 'not':
					if ($this->session->getVar("list_AllIds"))
					{
						$pastIds = unserialize(base64_decode($this->session->getVar("list_AllIds")));
						$ids = array_diff($pastIds, $ids);
					}
					$this->session->setVar("list_AllIds", base64_encode(serialize($ids)));
					return TRUE;
				default:
					return FALSE;
			}
		}
// If we get here, $quicksearch is 'final'
		$ids = unserialize(base64_decode($this->session->getVar("list_AllIds")));
		if (is_bool($ids) || empty($ids)) // FALSE
		{
			return FALSE;
		}
		$this->session->setVar("setup_PagingTotal", count($ids));
		$this->session->delVar("sql_CountAlphaStmt");
                
        $this->common->pagingStyle(
            $this->countQuery,
            $this->listType,
            $order,
            $queryString,
            $this->conditions,
            $this->joins,
            $this->conditionsOneField,
            'resource',
            $subQ
        );
        if (!$this->session->getVar("list_AllIds"))
        {
            return FALSE;
        }

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
        // Test for web_enclopedia and web_encyclopedia_article
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
            $dateDiff = $this->db->dateDiffRatio('statisticsresourceviewsCount', 'resourcetimestampTimestampAdd');
            $ratio = $this->db->round("$dateDiff / $setSum", 'index');
            $this->joins['statistics_resource_views'] = ['statisticsresourceviewsResourceId', 'resourcemiscId'];
            $this->joins['resource_timestamp'] = ['resourcetimestampId', 'resourcemiscId'];
            $this->db->groupBy(['statisticsresourceviewsResourceId', 'resourcemiscId']);
            $this->quarantine(FALSE, 'resourcemiscId', FALSE);
            $this->useBib('resourcemiscId');
            $this->db->ascDesc = $this->session->getVar("list_AscDesc");
            $this->executeCondJoins();
            $indicesQuery = $this->db->queryNoExecute($this->db->selectNoExecute(
                'resource_misc',
                $this->db->formatFields([['resourcemiscId' => 'rId']]) . ', ' . $ratio,
                FALSE,
                FALSE
            ));
        }
        elseif ($order == 'downloadsIndex')
        {
            if (!$setSum = $stats->getMaxDownloadRatio())
            {
                $setSum = 0.1;
            }
            $dateDiff = $this->db->dateDiffRatio('statisticsattachmentdownloadsCount', 'resourceattachmentsTimestamp', FALSE, 'AVG', FALSE);
//            $ratio = $this->db->round("$dateDiff / $setSum");
            $case = $this->db->round($this->db->caseWhen(
                $this->db->formatFields('resourceattachmentsResourceId'),
                'IS NULL',
                '0',
                "$dateDiff / $setSum",
                FALSE
            ), 'index');
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
			$dateDiffClause = $this->db->dateDiffRatio('count', 'resourcetimestampTimestampAdd', 'accessRatio');
			$sumClause = $this->db->sum('statisticsresourceviewsCount', 'count');
//			$this->db->formatConditions(['resourcetimestampTimestampAdd' => 'IS NOT NULL']);
			$this->db->leftJoin('resource_timestamp', 'resourcetimestampId', 'statisticsresourceviewsResourceId');
			$this->db->groupBy('statisticsresourceviewsResourceId');
			$subQ = $this->db->subQuery(
				$this->db->selectNoExecute('statistics_resource_views', 
				[$sumClause, $this->db->formatFields('resourcetimestampTimestampAdd'), 
					$this->db->formatFields([['statisticsresourceviewsResourceId' => 'rId']])], 
				FALSE, FALSE),
				't',
				TRUE,
				TRUE
			);
			$fields = $this->db->formatFields('rId') . ',' . $dateDiffClause;
			$selectStmt = $this->db->selectNoExecuteFromSubQuery(FALSE, $fields, $subQ, FALSE, FALSE);
            $this->db->createTempTableFromSelect('countsAR', $selectStmt);
// Create temp table for attachment downloads
			$dateDiffClause = $this->db->dateDiffRatio('count', 'resourceattachmentsTimestamp');
			$dateDiffClause = $this->db->ifClause('DATEDIFF(CURRENT_DATE, `resourceattachmentsTimestamp`)', 
				$this->db->equal . 0 . $this->db->or . ' NULL', 0, 
				$dateDiffClause, 'downloadRatio');
			$sumClause = $this->db->sum('statisticsattachmentdownloadsCount', 'count');
			$this->db->formatConditions(['statisticsattachmentdownloadsAttachmentId' => 'IS NOT NULL']);
			$this->db->leftJoin('resource_attachments', 'resourceattachmentsId', 'statisticsattachmentdownloadsAttachmentId');
			$this->db->groupBy('statisticsattachmentdownloadsResourceId');
			$subQ = $this->db->subQuery(
				$this->db->selectNoExecute('statistics_attachment_downloads', 
				[$sumClause, $this->db->formatFields('resourceattachmentsTimestamp'), 
					$this->db->formatFields([['statisticsattachmentdownloadsResourceId' => 'raId']])], 
				FALSE, FALSE),
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
     *	SELECT `userbibliographyresourceResourceId` FROM `WKX_user_bibliography_resource`
     *	WHERE (`userbibliographyresourceBibliographyId` = 1)
     *	)
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
        if ($useBib = $this->session->getVar("mywikindx_Bibliography_use"))
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
     */
    private function listJoins($order)
    {
        $this->db->ascDesc = $this->session->getVar($this->listMethodAscDesc);
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
        if (
        	((GLOBALS::getUserVar('PagingStyle') == 'A') && (($order == 'title') || ($order == 'creator')))
        	|| (in_array($order, ['popularityIndex', 'downloadsIndex', 'viewsIndex']))
        ) {
            $this->db->leftJoin('resource', 'resourceId', 'rId');
        }
        //		if(($order != 'popularityIndex') && ($order != 'downloadsIndex') && ($order != 'viewsIndex'))
        $this->db->leftJoin('resource_misc', 'resourcemiscId', 'resourceId');
        $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'resourceId');
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->leftJoin('resource_timestamp', 'resourcetimestampId', 'resourceId');
        $this->db->leftJoin('resource_year', 'resourceyearId', 'resourceId');
        $this->db->leftJoin('resource_text', 'resourcetextId', 'resourceId');
        $this->db->leftJoin('resource_page', 'resourcepageId', 'resourceId');
        $this->db->leftJoin('resource_summary', 'resourcesummaryId', 'resourceId');
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
                    $conditions[] = $this->db->formatFields('c.resourcecreatorCreatorSurname') . ' IS NULL ';
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
