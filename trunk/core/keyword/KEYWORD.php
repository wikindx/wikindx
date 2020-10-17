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
 * KEYWORD common routines
 *
 * @package wikindx\core\keyword
 */
class KEYWORD
{
    /** object */
    private $db;
    /** object */
    private $commonBib;

    /**
     *	KEYWORD class
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
    }
    /**
     * Get keyword names from WKX_keyword.
     *
     * @param bool $userBib FALSE (default) or TRUE (return only resource types within current user bibliography)
     * @param mixed $kType FALSE (default) or metadata type or metadata array ('quote', 'paraphrase' etc). Gather only keywords attached to metadata
     * @param $typeArray FALSE (default) or limits keywords to those belonging to these types (only when table == 'resource_keyword')
     *
     * @return array|false id => keyword
     */
    public function grabAll($userBib = FALSE, $kType = FALSE, $typeArray = FALSE)
    {
        $subQuery = FALSE;
        if (is_array($kType) && !empty($kType)) {
            $unions = [];
            foreach ($kType as $mType) {
                if (($mType == 'quote') || ($mType == 'quoteComment')) {
                    $this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
                    $this->db->formatConditions(['resourcemetadataType' => 'q']);
                    $this->db->leftJoin('resource_metadata', 'resourcemetadataId', 'resourcekeywordMetadataId');
                    $unions[] = $this->db->selectNoExecute(
                        'resource_keyword',
                        [['resourcekeywordKeywordId' => 'rkId']],
                        TRUE,
                        TRUE,
                        TRUE
                    );
                } elseif (($mType == 'paraphrase') || ($mType == 'paraphraseComment')) {
                    $this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
                    $this->db->formatConditions(['resourcemetadataType' => 'p']);
                    $this->db->leftJoin('resource_metadata', 'resourcemetadataId', 'resourcekeywordMetadataId');
                    $unions[] = $this->db->selectNoExecute(
                        'resource_keyword',
                        [['resourcekeywordKeywordId' => 'rkId']],
                        TRUE,
                        TRUE,
                        TRUE
                    );
                } elseif ($mType == 'musing') {
                    $this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
                    $this->db->formatConditions(['resourcemetadataType' => 'm']);
                    $this->db->leftJoin('resource_metadata', 'resourcemetadataId', 'resourcekeywordMetadataId');
                    $unions[] = $this->db->selectNoExecute(
                        'resource_keyword',
                        [['resourcekeywordKeywordId' => 'rkId']],
                        TRUE,
                        TRUE,
                        TRUE
                    );
                } elseif ($mType == 'idea') {
                    $this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
                    $this->db->formatConditions(['resourcemetadataType' => 'i']);
                    $this->db->leftJoin('resource_metadata', 'resourcemetadataId', 'resourcekeywordMetadataId');
                    $unions[] = $this->db->selectNoExecute(
                        'resource_keyword',
                        [['resourcekeywordKeywordId' => 'rkId']],
                        TRUE,
                        TRUE,
                        TRUE
                    );
                }
            }
            if (!empty($unions)) {
                $subQuery = $this->db->subQuery($this->db->union($unions), 't');
            }
            $id = 'resourcekeywordResourceId';
        } elseif (!is_array($kType)) {
            if ($kType == 'quote') {
                if (!$userBib && is_array($keywords = $this->db->readCache('cacheQuoteKeywords'))) {
                    return $keywords;
                }
                $this->db->formatConditions($this->db->formatFields('resourcekeywordQuoteId') . ' IS NOT NULL');
                $id = 'resourceId';
            } elseif ($kType == 'paraphrase') {
                if (!$userBib && is_array($keywords = $this->db->readCache('cacheParaphraseKeywords'))) {
                    return $keywords;
                }
                $this->db->formatConditions($this->db->formatFields('resourcekeywordParaphraseId') . ' IS NOT NULL');
                $id = 'resourceId';
            } elseif ($kType == 'musing') {
                if (!$userBib && is_array($keywords = $this->db->readCache('cacheMusingKeywords'))) {
                    return $keywords;
                }
                $this->db->formatConditions($this->db->formatFields('resourcekeywordMusingId') . ' IS NOT NULL');
                $id = 'resourceId';
            } elseif ($kType == 'resource') {
                if (!$userBib && !is_array($typeArray) && is_array($keywords = $this->db->readCache('cacheResourceKeywords'))) {
                    return $keywords;
                }
                $this->db->formatConditions($this->db->formatFields('resourcekeywordResourceId') . ' IS NOT NULL');
                $id = 'resourcekeywordResourceId';
            } else { // all keywords
                if (!$userBib && !is_array($typeArray) && is_array($keywords = $this->db->readCache('cacheKeywords'))) {
                    return $keywords;
                }
                $id = 'resourcekeywordResourceId';
            }
        } else { // all keywords
            if (!$userBib && !is_array($typeArray) && is_array($keywords = $this->db->readCache('cacheKeywords'))) {
                return $keywords;
            }
            $id = 'resourcekeywordResourceId';
        }
        if (is_array($typeArray) && !empty($typeArray) && $kType == 'resource') {
            $this->db->formatConditionsOneField($typeArray, 'resourceType');
        }
        if (!is_array($kType) && $kType) {
            $this->db->leftJoin('resource_keyword', 'resourcekeywordKeywordId', 'keywordId');
            $this->db->leftJoin('resource', 'resourceId', 'resourcekeywordResourceId');
        }
        $this->db->orderBy('keywordKeyword');
        if ($subQuery) {
            $this->db->leftJoin('keyword', 'keywordId', 'rkId');
            $this->db->leftJoin('resource_keyword', 'resourcekeywordKeywordId', 'keywordId');
            if ($userBib) {
                $this->commonBib->userBibCondition($id);
            }
            $recordset = $this->db->selectFromSubQuery(FALSE, ['keywordId', 'keywordKeyword', 'rkId'], $subQuery);
        } else {
            if ($userBib) {
                $this->commonBib->userBibCondition($id);
            }
            $recordset = $this->db->select('keyword', ['keywordId', 'keywordKeyword'], TRUE);
        }
        while ($row = $this->db->fetchRow($recordset)) {
            $keywords[$row['keywordId']] = \HTML\dbToFormTidy($row['keywordKeyword']);
        }
        if (isset($keywords)) {
            // (re)create cache if listing for all types and no user bibliography
            if (!$userBib && !is_array($typeArray) && !is_array($kType)) {
                if ($kType == 'quote') {
                    $this->db->writeCache('cacheQuoteKeywords', $keywords);
                } elseif ($kType == 'paraphrase') {
                    $this->db->writeCache('cacheParaphraseKeywords', $keywords);
                } elseif ($kType == 'musing') {
                    $this->db->writeCache('cacheMusingKeywords', $keywords);
                } elseif ($kType == 'resource') {
                    $this->db->writeCache('cacheResourceKeywords', $keywords);
                } else {
                    $this->db->writeCache('cacheKeywords', $keywords);
                }
            }

            return $keywords;
        }

        return FALSE;
    }
    /**
     * Does this keyword already exist in the database?
     *
     * @param string $keyword
     *
     * @return false|int Existing keyword ID
     */
    public function checkExists($keyword)
    {
        $this->db->formatConditions($this->db->formatFields('keywordKeyword') . $this->db->like(FALSE, $keyword, FALSE));
        $resultset = $this->db->select('keyword', 'keywordId');
        if ($this->db->numRows($resultset)) {
            return $this->db->fetchOne($resultset);
        }

        return FALSE; // not found
    }
    /**
     * Remove resource-less keywords from keyword list and keyword groups having the keyword
     */
    public function removeHanging()
    {
        $deleteIds = [];
        $this->db->formatConditions(['resourcekeywordKeywordId' => ' IS NOT NULL']);
        $subStmt = $this->db->subQuery($this->db->selectNoExecute('resource_keyword', 'resourcekeywordKeywordId'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('keywordId') . $this->db->inClause($subStmt, TRUE));
        $recordset = $this->db->select('keyword', 'keywordId');
        while ($row = $this->db->fetchRow($recordset)) {
            $deleteIds[] = $row['keywordId'];
        }
        if (!empty($deleteIds)) {
            $this->db->formatConditionsOneField($deleteIds, 'keywordId');
            $this->db->delete("keyword");
            // remove cache files for keywords
            $this->db->deleteCache('cacheResourceKeywords');
            $this->db->deleteCache('cacheMetadataKeywords');
            $this->db->deleteCache('cacheQuoteKeywords');
            $this->db->deleteCache('cacheParaphraseKeywords');
            $this->db->deleteCache('cacheMusingKeywords');
            $this->db->deleteCache('cacheKeywords');
        }
        $this->checkKeywordGroups();
    }
    /**
     * When deleting or merging keywords or deleting resources or metadata, check the consistency of keyword groups.
	 * A KG must have at least two keywords or it should be deleted.
     *
     */
    public function checkKeywordGroups()
    {
        $deleteIds = [];
		$subQ = $this->db->selectNoExecute('resource_keyword', 'resourcekeywordKeywordId');
		$this->db->formatConditions('userkgkeywordsKeywordId' . $this->db->inClause($subQ, TRUE));
		$recordset = $this->db->select('user_kg_keywords', 'userkgkeywordsKeywordId');
		while ($row = $this->db->fetchRow($recordset)) {
            $deleteIds[] = $row['userkgkeywordsKeywordId'];
        }
        if (!empty($deleteIds)) {
            $this->db->formatConditionsOneField($deleteIds, 'userkgkeywordsKeywordId');
            $this->db->delete('user_kg_keywords');
        }
        $deleteIds = [];
        $this->db->groupBy('userkgkeywordsKeywordGroupId');
        $recordset = $this->db->selectCounts('user_kg_keywords', 'userkgkeywordsKeywordId', ['userkgkeywordsKeywordGroupId'], FALSE, FALSE);
		while ($row = $this->db->fetchRow($recordset)) {
			if ($row['count'] < 2) {
	            $deleteIds[] = $row['userkgkeywordsKeywordGroupId'];
	        }
        }
        if (!empty($deleteIds)) {
            $this->db->formatConditionsOneField($deleteIds, 'userkgusergroupsKeywordGroupId');
            $this->db->delete('user_kg_usergroups');
            $this->db->formatConditionsOneField($deleteIds, 'userkgkeywordsKeywordGroupId');
            $this->db->delete('user_kg_keywords');
            $this->db->formatConditionsOneField($deleteIds, 'userkeywordgroupsId');
            $this->db->delete('user_keywordgroups');
        }
    }
    /**
     * Write names to keyword table and return keyword id array ready for adding to resource_keyword.resourcekeywordKeywordId fields.
     *
     * @param array $inputArray
     *
     * @return false|int Array of keyword IDs
     */
    public function writeKeywordTable($inputArray)
    {
        $fields[] = "keywordKeyword";
        $addedKeyword = FALSE;
        $ids = $keywords = [];
        if (array_key_exists('keywords', $inputArray)) {
            $keywords = \UTF8\mb_explode(',', $inputArray['keywords']);
        }
        foreach ($keywords as $keyword) {
            if (!$keyword = \UTF8\mb_trim($keyword)) {
                continue;
            }
            $values[0] = $keyword;
            if ($id = $this->checkExists($keyword)) {
                $ids[] = $id;
                foreach ($fields as $field) {
                    $updateArray[$field] = array_shift($values);
                }
                $this->db->formatConditions(['keywordId' => $id]);
                $this->db->update('keyword', $updateArray);
            }
            // given keyword doesn't exist so now write to db
            else {
                $this->db->insert('keyword', $fields, $values);
                $ids[] = $this->db->lastAutoId();
                $addedKeyword = TRUE;
            }
        }
        // merge two arrays to remove duplicate ids.
        if (array_key_exists('keyword_ids', $inputArray) && $inputArray['keyword_ids']) {
            $ids = array_unique(array_merge($ids, \UTF8\mb_explode(',', $inputArray['keyword_ids'])));
        }
        if (empty($ids)) {
            return FALSE;
        }
        if ($addedKeyword) {
            // remove cache files for keywords
            $this->db->deleteCache('cacheresourceKeywords');
            $this->db->deleteCache('cachemetadataKeywords');
            $this->db->deleteCache('cachequoteKeywords');
            $this->db->deleteCache('cacheparaphraseKeywords');
            $this->db->deleteCache('cachemusingKeywords');
            $this->db->deleteCache('cacheKeywords');
        }
        // In case array key 0 ('IGNORE') is there, remove it
        if (array_search(0, $ids) === 0) {
            unset($ids[0]);
        }

        return $ids;
    }
    /**
     * Make the keyword select transfer arrows to transfer keywords between select boxes with onclick
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrows()
    {
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', ['startFunction' => 'selectKeyword']);
        $toLeftImage  = \AJAX\jActionIcon('toLeft',  'onclick', ['startFunction' => 'discardKeyword']);
        return [$toRightImage, $toLeftImage];
    }
}
