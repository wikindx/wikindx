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
 *	SEARCH class
 *
 *	Search database from within office programs (Word, Libreoffice etc.)
 */
class SEARCH
{
    public $words = '';
    public $unions = [];
    public $input = [];
    private $ids = [];
    private $db;
    private $stmt;
    private $common;
    private $keyword;
    private $metadata;
    private $parsePhrase;
    private $subQ;
    public $order;
    public $ascDesc;
    /** array */
    private $listFields = ['resourceId', 'creatorSurname', 'resourceType', 'resourceTitle', 'resourceSubtitle', 'resourceShortTitle',
        'resourceTransTitle', 'resourceTransSubtitle', 'resourceTransShortTitle', 'resourceField1', 'resourceField2', 'resourceField3',
        'resourceField4', 'resourceField5', 'resourceField6', 'resourceField7', 'resourceField8', 'resourceField9', 'resourceNoSort',
        'resourceTransNoSort', 'resourceIsbn', 'resourceBibtexKey', 'resourceDoi', 'resourceyearId', 'resourceyearYear1', 'resourceyearYear2', 'resourceyearYear3', 'resourceyearYear4', 'resourcetextUrls', 
        'resourcepageId', 'resourcepagePageStart', 'resourcepagePageEnd', 'resourcetimestampId', 'resourcetimestampTimestamp', 'resourcetimestampTimestampAdd', 'publisherId', 'publisherName',
        'publisherLocation', 'publisherType', 'collectionId', 'collectionTitle', 'collectionTitleShort', 'collectionType', 'resourcemiscId', 'resourcemiscCollection', 'resourcemiscPublisher', 'resourcemiscField1', 'resourcemiscField2',
        'resourcemiscField3', 'resourcemiscField4', 'resourcemiscField5', 'resourcemiscField6', 'resourcemiscTag', 'resourcecreatorCreatorSurname'];

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $this->common->quickSearch = TRUE;
        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->metadata = FACTORY_METADATA::getInstance();
        $this->parsePhrase = FACTORY_PARSEPHRASE::getInstance();
        $this->parsePhrase->quickSearch = TRUE;
    }
    /**
     * Process the search
     *
     */
    public function process()
    {
        $this->db->ascDesc = $this->ascDesc;
        $this->stmt->listType = 'search';
        $this->input['Partial'] = TRUE;
        if (!$this->getIds()) {
            return FALSE;
        }
        $this->db->ascDesc = $this->ascDesc;
        return $this->getFinalSql();
        
    }
    /**
     * create the unions
     *
     * @param mixed $search
     * @param mixed $searchFT
     */
    public function fieldSql($search, $searchFT)
    {
        if ((strpos($searchFT, '-') !== FALSE) && (substr($searchFT, -1) === '*'))
        {
            $searchFT = '"' . substr_replace($searchFT, '"', strlen($searchFT) - 1);
        }
        $searchFT = str_replace("'", "''", $searchFT);
        foreach (['resourcecustomShort', 'resourcecustomLong'] as $field)
        {
            $field = $this->db->formatFields($field);
        }
        // title
        $field = $this->db->concat([$this->db->formatFields('resourceNoSort'), $this->db->formatFields('resourceTitleSort')], ' ');
        $this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', $field, $search));
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource', [['resourceId' => 'rId']]));
        // creatorSurname
        $this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', 'creatorSurname', $search));
        $this->db->formatConditions(['resourcecreatorResourceId' => ' IS NOT NULL ']);
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_creator', [['resourcecreatorResourceId' => 'rId']]));
        // keywordKeyword
        $this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', 'keywordKeyword', $search));
        $this->db->formatConditions(['resourcekeywordResourceId' => ' IS NOT NULL ']);
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_keyword', [['resourcekeywordResourceId' => 'rId']]));
        // resourcemetadataText
        $matchAgainst = $this->db->fulltextSearch('resourcemetadataText', $searchFT);
        $this->metadata->setCondition(FALSE, FALSE, TRUE);
        $this->db->formatConditions($matchAgainst);
        $this->db->formatConditions(['resourcemetadataResourceId' => 'IS NOT NULL']);
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_metadata', [['resourcemetadataResourceId' => 'rId']]));
        // resourcecustomShort
        $this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', 'resourcecustomShort', $search));
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_custom', [['resourcecustomResourceId' => 'rId']]));
        // resourcecustomLong
        $matchAgainst = $this->db->fulltextSearch('resourcecustomLong', $searchFT);
        $this->db->formatConditions($matchAgainst);
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_custom', [['resourcecustomResourceId' => 'rId']]));
        // resourcetextAbstract
        $matchAgainst = $this->db->fulltextSearch('resourcetextAbstract', $searchFT);
        $this->db->formatConditions($matchAgainst);
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_text', [['resourcetextId' => 'rId']]));
        // resourcetextNote
        $matchAgainst = $this->db->fulltextSearch('resourcetextNote', $searchFT);
        $this->db->formatConditions($matchAgainst);
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_text', [['resourcetextId' => 'rId']]));

        $this->unions = $this->db->union($unions);
    }
    /**
     * Get the list of resource ids
     *
     * @param bool $reprocess
     * @param string $queryString
     */
    public function getIds()
    {
		$this->parseWord();
		// Deal with OR strings first
		$ors = implode($this->db->or, $this->parsePhrase->ors);
		$orsFT = implode(' ', $this->parsePhrase->orsFT);
		if ($ors) {
			$this->getInitialIds($ors, $orsFT, 'or');
		}
		// Deal with AND strings next
		foreach ($this->parsePhrase->ands as $and) { // we use array_intersect . . .
			if (empty($this->parsePhrase->andsFT)) {
				$this->parsePhrase->andsFT[] = $this->parsePhrase->firstAnd;
			}
			$this->getInitialIds($and, array_shift($this->parsePhrase->andsFT), 'and');
		}
		// Finally, deal with NOT strings. We match IDs using OR then subtract the found ids from the main ids array
		// If there are no ANDs or ORs, we must first get all existing resource ids
		$nots = implode($this->db->or, $this->parsePhrase->nots);
		$notsFT = implode(' ', $this->parsePhrase->notsFT);
		if (empty($this->parsePhrase->ands) && empty($this->parsePhrase->ors)) {
			$resultSet = $this->db->select('resource', 'resourceId');
			while ($row = $this->db->fetchRow($resultSet)) {
				$allIds[] = $row['resourceId'];
			}
		}
		if ($nots) {
			$this->getInitialIds($nots, $notsFT, 'not');
		}
		// Now finalize
		if (empty($this->ids)) {
			return FALSE;
		}

        return TRUE;
    }
    /**
     * Get the initial IDs from the database
     *
     * @param mixed $search
     * @param mixed $searchFT
     * @param mixed $type
     */
    private function getInitialIds($search, $searchFT, $type)
    {
        $this->fieldSql($search, $searchFT);
        $subStmt = $this->setSubQuery();
        $this->searchSubQuery($subStmt, $type);

        return TRUE;
    }
    /**
     * Get the final SQL statement
     */
    public function getFinalSql()
    {
		$this->db->leftJoin('resource_misc', 'resourcemiscId', 'resourceId');
		$this->db->leftJoin('resource_page', 'resourcepageId', 'resourceId');
		$this->db->leftJoin('resource_year', 'resourceyearId', 'resourceId');
		$this->db->leftJoin('resource_text', 'resourcetextId', 'resourceId');
		$this->db->leftJoin('resource_timestamp', 'resourcetimestampId', 'resourceId');
		$this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'resourceId');
		$this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->leftJoin('publisher', 'publisherId', 'resourcemiscPublisher');
        $this->db->leftJoin('collection', 'collectionId', 'resourcemiscCollection');
        $this->db->groupBy('resourceId');
//		$this->db->limit(10, 0);
		switch ($this->order) {
			case 'creator' :
            	$this->db->orderBy('resourcecreatorCreatorSurname', TRUE, FALSE);
            	$this->db->orderBy('resourceTitleSort', TRUE, FALSE);
            	$this->db->orderBy($this->stmt->yearOrder(), FALSE);
				break;
			case 'title' :
            	$this->db->orderBy('resourceTitleSort', TRUE, FALSE);
            	$this->db->orderBy('resourcecreatorCreatorSurname', TRUE, FALSE);
            	$this->db->orderBy($this->stmt->yearOrder(), FALSE);
				break;
			case 'year' :
            	$this->db->orderBy($this->stmt->yearOrder(), FALSE);
				$this->db->orderBy($this->db->ifClause(
					$this->db->formatFields('resourcecreatorCreatorSurname'),
					'IS NOT NULL',
					$this->db->formatFields('resourcecreatorCreatorSurname'),
					$this->db->formatFields('resourceTitleSort')
				), FALSE, FALSE);
            	$this->db->orderBy('resourceTitleSort', TRUE, FALSE);
				break;
			case 'timestamp' :
            	$this->db->orderBy('resourcetimestampTimestamp', TRUE);
				$this->db->orderBy($this->db->ifClause(
					$this->db->formatFields('resourcecreatorCreatorSurname'),
					'IS NOT NULL',
					$this->db->formatFields('resourcecreatorCreatorSurname'),
					$this->db->formatFields('resourceTitleSort')
				), FALSE, FALSE);
            	$this->db->orderBy('resourceTitleSort', TRUE, FALSE);
				break;
			break;
		}
        $this->db->formatConditions($this->db->formatFields('resourceId') . $this->db->inClause(implode(',', $this->ids)));

    	return $this->db->queryNoExecute($this->db->selectNoExecute('resource', $this->listFields));
    }
    /**
     * parse the search word(s)
     */
    private function parseWord()
    {
        $this->words = $this->parsePhrase->parse($this->input);
        $this->parsePhrase->parse($this->input, FALSE, FALSE, FALSE, TRUE); // Needed to get FULLTEXT searches (abstract, notes etc.)
        if ((is_array($this->words) && empty($this->words)) || !$this->parsePhrase->validSearch)
        {

        }
    }
    /**
     * Set the subQuery
     */
    private function setSubQuery()
    {
        switch ($this->order) {
            case 'title':
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource'] = ['resourceId', 'rId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($this->unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'creator':
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource_creator'] = ['resourcecreatorResourceId', 'rId'];
                $this->stmt->joins['creator'] = ['creatorId', 'resourcecreatorCreatorId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($this->unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'year':
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource'] = ['resourceId', 'rId'];
                $this->stmt->joins['resource_year'] = ['resourceyearId', 'rId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($this->unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'timestamp':
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource_timestamp'] = ['resourcetimestampId', 'rId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($this->unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            default:
                break;
        }
    }
    /**
     * Create subquery initial order SELECT statement with minimal fields.
     *
     * @param false|string $totalSubQuery Default is FALSE
     * @param false|string $type (default FALSE) otherwise 'or', 'and', 'not', 'final'
     *
     * @return bool TRUE if resources found, FALSE otherwise
     */
    public function searchSubQuery($totalSubQuery = FALSE, $type = FALSE)
    {
        $ids = [];
		$resultSet = $this->db->query($totalSubQuery);
		while ($row = $this->db->fetchRow($resultSet)) {
			$ids[] = $row['rId'];
		}
		if (empty($ids)) {
			return FALSE;
		}
		$ids = array_filter($ids); // array_filter() to ensure no null ids
		switch ($type) {
			case 'or':
				$this->ids = $ids;

				return TRUE;
			case 'and':
				if (!empty($this->ids)) {
					$ids = array_intersect($ids, $this->ids);
				}
				$this->ids = $ids;

				return TRUE;
			case 'not':
				if (!empty($this->ids)) {
					$ids = array_diff($this->ids, $ids);
				}
				$this->ids = $ids;

				return TRUE;
			default:
				return FALSE;
		}
    }
}
