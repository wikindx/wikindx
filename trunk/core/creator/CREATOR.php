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
 * Common functions for creators
 *
 * @package wikindx\core\creator
 */
class CREATOR
{
    /** object */
    private $db;
    /** array */
    private $names;
    /** object */
    private $commonBib;
    /** object */
    private $gatekeep; //!< there are some functions requiring write access
    /** object */
    private $errors;

    /**
     *	CREATOR class.
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();

        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->names = ["resourcecreatorCreator1", "resourcecreatorCreator2",
            "resourcecreatorCreator3", "resourcecreatorCreator4", "resourcecreatorCreator5", ];
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
    }
    /**
     * Get creators from creators table.
     *
     * @param int $userBib ID of user bibliography to use. Default is FALSE
     * @param mixed $typeArray array of resource types within which to select creators. Default is FALSE
     * @param mixed $metadata metadata type or metadata array ('quote', 'paraphrase' etc) -- gather only creators for resources which have metadata Default is FALSE
     * @param bool $group group database creators together into the same person if TRUE. Default is FALSE
     *
     * @return array of id => creator
     */
    public function grabAll($userBib = FALSE, $typeArray = FALSE, $metadata = FALSE, $group = FALSE)
    {
        if (!$userBib && !is_array($typeArray) && !$group) {
            if (!is_array($metadata) && !$metadata && is_array($creators = $this->db->readCache('cacheResourceCreators'))) {
                return $creators;
            }

            unset($creators);
        }

        $recordset = FALSE;
        if (is_array($typeArray) && !empty($typeArray)) {
            if (isset($subSubQuery)) {
                $this->db->formatConditionsOneField($typeArray, 'resourceType');
                $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'rId');
                $this->db->leftJoin('resource', 'resourceId', 'rId');
                $this->db->formatConditions($this->db->formatFields('creatorId') .
                    $this->db->inClause($this->db->subQueryFields('resourcecreatorCreatorId', $subSubQuery, 't', TRUE, TRUE)));
            } else {
                $this->db->formatConditionsOneField($typeArray, 'resourceType');
                $this->db->formatConditions(['creatorSameAs' => ' IS NULL']);
                $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'resourceId');
                $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
                if ($userBib) {
                    $this->commonBib->userBibCondition('resourcecreatorResourceId');
                }
                $u[] = $this->db->selectNoExecute('resource', 'resourcecreatorCreatorId', TRUE, TRUE, TRUE);
                $this->db->formatConditionsOneField($typeArray, 'resourceType');
                $this->db->formatConditions(['creatorSameAs' => ' IS NOT NULL']);
                $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'resourceId');
                $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
                if ($userBib) {
                    $this->commonBib->userBibCondition('resourcecreatorResourceId');
                }
                $u[] = $this->db->selectNoExecute('resource', 'creatorSameAs', TRUE, TRUE, TRUE);
                $subQuery = $this->db->subQuery($this->db->union($u), 't');
                $this->db->formatConditions(['creatorId' => ' IS NOT NULL']);
                $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
                $this->db->orderBy('creatorSurname');
                $recordset = $this->db->query($this->db->selectNoExecuteFromSubQuery(
                    FALSE,
                    ['creatorId', "creatorSurname", "creatorInitials", "creatorFirstname", "creatorPrefix"],
                    $subQuery,
                    FALSE,
                    TRUE,
                    TRUE
                ));
            }
            $group = $userBib = FALSE;
        } elseif (is_array($metadata) && !empty($metadata)) {
            foreach ($metadata as $mType) {
                if (($mType == 'quote') || ($mType == 'quoteComment')) {
                    $unions[] = $this->db->selectNoExecute('resource_quote', [['resourcequoteResourceId' => 'rId']], TRUE);
                } elseif (($mType == 'paraphrase') || ($mType == 'paraphraseComment')) {
                    $unions[] = $this->db->selectNoExecute('resource_paraphrase', [['resourceparaphraseResourceId' => 'rId']], TRUE);
                } elseif ($mType == 'musing') {
                    $unions[] = $this->db->selectNoExecute('resource_musing', [['resourcemusingResourceId' => 'rId']], TRUE);
                }
            }
            $union = $this->db->union($unions);
            $subSubQuery = $this->db->subQuery($union, 'u', TRUE, TRUE);
            $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'rId');
            if ($userBib) {
                $this->commonBib->userBibCondition('resourcecreatorResourceId');
            }
            $subQuery = $this->db->subQueryFields('resourcecreatorCreatorId', $subSubQuery, 't', TRUE, TRUE);
            $this->db->formatConditions($this->db->formatFields('creatorId') . $this->db->equal .
                $this->db->formatFields('resourcecreatorCreatorId'));
            $this->db->orderBy('creatorSurname');
            $recordset = $this->db->query($this->db->selectNoExecuteFromSubQuery(
                'creator',
                ['creatorId', "creatorSurname", "creatorInitials", "creatorFirstname", "creatorPrefix"],
                $subQuery
            ));
        }
        if (!$recordset) {
            if ($group) {
                $this->db->formatConditions(['creatorSameAs' => ' IS NULL']);
            }
            if ($userBib) {
                $this->db->leftJoin('resource_creator', 'resourcecreatorCreatorId', 'creatorId');
                $this->commonBib->userBibCondition('resourcecreatorResourceId');
            }
            $this->db->orderBy('creatorSurname');
            if (isset($subQuery)) {
                $recordset = $this->db->query($this->db->selectNoExecuteFromSubQuery(
                    FALSE,
                    ['creatorId', "creatorSurname", "creatorInitials", "creatorFirstname", "creatorPrefix"],
                    $subQuery
                ));
            } else {
                $recordset = $this->db->select(
                    'creator',
                    ['creatorId', "creatorSurname", "creatorInitials", "creatorFirstname", "creatorPrefix"]
                );
            }
        }

        $creators = [];

        while ($row = $this->db->fetchRow($recordset)) {
            $this->formatNames($row, $creators);
        }

        if (!empty($creators)) {
            // (re)create cache if getting entire list
            if (!$userBib && !is_array($typeArray) && !$group) {
                $this->db->writeCache($metadata ? 'cacheMetadataCreators' : 'cacheResourceCreators', $creators);
            }

            return $creators;
        } else {
            return FALSE;
        }
    }
    /**
     * Gather creators who are group masters
     *
     * @return mixed FALSE|array of creators
     */
    public function grabGroupMasters()
    {
        $this->db->formatConditions(['creatorSameAs' => ' IS NOT NULL']);
        $subStmt = $this->db->subQuery($this->db->selectNoExecute('creator', 'creatorSameAs'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('creatorId') . $this->db->inClause($subStmt));
        $this->db->orderBy('creatorSurname');
        $recordset = $this->db->select(
            'creator',
            ['creatorId', "creatorSurname", "creatorInitials", "creatorFirstname", "creatorPrefix"]
        );
        $creators = [];
        while ($row = $this->db->fetchRow($recordset)) {
            $this->formatNames($row, $creators);
        }
        if (!empty($creators)) {
            return $creators;
        } else {
            return FALSE;
        }
    }
    /**
     * Gather creators who are not group masters
     *
     * @param bool $notExists
     *
     * @return mixed FALSE|array of creators
     */
/*    public function grabGroupAvailableMembers($notExists = FALSE)
    {
        $this->db->formatConditions($this->db->formatFields('creatorId') . $this->db->equal . $this->db->formatFields('t1.creatorId'));
        $this->db->formatConditions(['creatorSameAs' => ' IS NOT NULL']);
        //		$subStmt = $this->db->subQuery($this->db->selectNoExecute('creator', 'creatorSameAs'), FALSE, FALSE, TRUE);
        //		$this->db->formatConditions($this->db->formatFields('creatorId') . $this->db->inClause($subStmt, TRUE));
        $subStmt = $this->db->selectNoExecute('creator', $this->db->tidyInput('1'), FALSE, FALSE, TRUE, 't1');
        if ($notExists) {
            $this->db->formatConditions($this->db->existsClause($subStmt, TRUE));
        } else {
            $this->db->formatConditions($this->db->existsClause($subStmt));
        }
        $this->db->orderBy('creatorSurname');
        $recordset = $this->db->select(
            'creator',
            ['creatorId', "creatorSurname", "creatorInitials", "creatorFirstname", "creatorPrefix"]
        );
        $creators = [];
        while ($row = $this->db->fetchRow($recordset)) {
            $this->formatNames($row, $creators);
        }
        if (!empty($creators)) {
            return $creators;
        } else {
            return FALSE;
        }
    }
*/
    /**
     * Gather creators who are group members of one group
     *
     * @param int $masterId
     *
     * @return mixed FALSE|array of creators
     */
    public function grabGroupMembers($masterId)
    {
        $this->db->formatConditions(['creatorSameAs' => $masterId]);
        $this->db->orderBy('creatorSurname');
        $recordset = $this->db->select(
            'creator',
            ['creatorId', "creatorSurname", "creatorInitials", "creatorFirstname", "creatorPrefix"]
        );
        $creators = [];
        while ($row = $this->db->fetchRow($recordset)) {
            $this->formatNames($row, $creators);
        }
        if (!empty($creators)) {
            return $creators;
        } else {
            return FALSE;
        }
    }
    /**
     * Gather creators who are not group members
     *
     * @return mixed FALSE|array of creators
     */
    public function grabGroupAvailableMembers()
    {
        $this->db->formatConditions(['creatorSameAs' => ' IS NULL']);
        $this->db->orderBy('creatorSurname');
        $recordset = $this->db->select(
            'creator',
            ['creatorId', "creatorSurname", "creatorInitials", "creatorFirstname", "creatorPrefix"]
        );
        $creators = [];
        while ($row = $this->db->fetchRow($recordset)) {
            $this->formatNames($row, $creators);
        }
        if (!empty($creators)) {
            return $creators;
        } else {
            return FALSE;
        }
    }
    /**
     * Does this creator already exist in the database?
     *
     * @param string $surname
     * @param string $firstname
     * @param string $initials
     * @param string $prefix
     *
     * @return int creator ID if exists, FALSE otherwise
     */
    public function checkExists($surname, $firstname, $initials, $prefix)
    {
        $name = str_replace(' ', '', $surname . $firstname . $initials . $prefix);
        $array = [$this->db->formatFields('creatorSurname'), $this->db->formatFields('creatorFirstname'),
            $this->db->formatFields('creatorInitials'), $this->db->formatFields('creatorPrefix'), ];
        // Concatenate (CONCAT_WS) fields and remove any spaces
        $this->db->formatConditions($this->db->replace(
            $this->db->concat($array, ''),
            ' ',
            '',
            FALSE
        ) . $this->db->equal . $this->db->tidyInput($name));
        $resultset = $this->db->select('creator', 'creatorId');
        if (!$this->db->numRows($resultset)) {
            return FALSE;
        } else {
            return $this->db->fetchOne($resultset);
        }
    }
    /**
     * Remove resource-less and collection-less creators
     *
     * @param bool $collectionDefaults If TRUE, remove references to deleted creators in collection defaults fields. Default = TRUE
     */
    public function removeHanging($collectionDefaults = TRUE)
    {
        $deleteIds = [];
        $this->db->formatConditions($this->db->formatFields('resourcecreatorCreatorId') . ' IS NOT NULL');
        $subStmt = $this->db->subQuery($this->db->selectNoExecute('resource_creator', 'resourcecreatorCreatorId'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('creatorId') . $this->db->inClause($subStmt, TRUE));
        $recordset = $this->db->select('creator', 'creatorId');
        while ($row = $this->db->fetchRow($recordset)) {
            $deleteIds[] = $row['creatorId'];
        }
        if (empty($deleteIds)) {
            return; // nothing to do
        }
        $this->db->formatConditionsOneField($deleteIds, 'creatorId');
        $this->db->delete('creator');
        // remove references to this creator as group master
        $this->db->formatConditionsOneField($deleteIds, 'creatorSameAs');
        $this->db->updateNull('creator', 'creatorSameAs');
        // remove cache files for creators
        $this->db->deleteCache('cacheResourceCreators');
        $this->db->deleteCache('cacheMetadataCreators');
        // remove references to deleted creators from collection defaults. Not used when deleting creators after editing collections (rather than resources)
        if ($collectionDefaults) {
            $this->collectionDefaults($deleteIds);
        }
    }
    /**
     * Inserts a new creator in the database.
     *
     * Requires write permissions.
     *
     * @param array $v Array of surname, firstname, initials, prefix
     *
     * @return int Inserted ID
     */
    public function insert($v)
    {
        $this->gatekeep->init();
        $surname = $v['surname'];
        $firstname = $initials = $prefix = NULL;
        if (array_key_exists('firstname', $v)) {
            $firstname = $v['firstname'];
        }
        if (array_key_exists('initials', $v)) {
            $initials = $v['initials'];
        }
        if (array_key_exists('prefix', $v)) {
            $prefix = $v['prefix'];
        }
        $fields[] = 'creatorSurname';
        $values[] = trim($surname);
        if (trim($firstname)) {
            $fields[] = 'creatorFirstname';
            $values[] = trim($firstname);
        }
        if (trim($initials)) {
            $fields[] = 'creatorInitials';
            $values[] = $this->formatInitials(trim($initials));
        }
        if (trim($prefix)) {
            $fields[] = 'creatorPrefix';
            $values[] = $this->formatInitials(trim($prefix));
        }
        if ($id = $this->checkExists($surname, $firstname, $initials, $prefix)) {
            return $id;
        }
        $this->db->insert('creator', $fields, $values);
        $return = $this->db->lastAutoId();
        // remove cache files for creators -- whenever anything changes in the lsit of creators, delete the cache.  No cache is the
        // trigger to recreate the cache the next time the list of creators is requested.
        $this->db->deleteCache('cacheResourceCreators');
        $this->db->deleteCache('cacheMetadataCreators');

        return $return;
    }
    /**
     * Format initials prior to storing in database
     *
     * Initials are stored unpunctuated and space-delimited.
     * In the final result, they can only consist of A-Z (and UTF-8) and space characters
     *
     * @param string $raw
     *
     * @return string
     */
    public function formatInitials($raw)
    {
        $initials = preg_split("/\\.\\s*/u", $raw);
        // If there's an empty array element at the end, remove it.
        if (!$initials[count($initials) - 1]) {
            array_pop($initials);
        }

        return implode(' ', $initials);
    }
    /**
     * Make the creator select transfer arrows to transfer creators between select boxes with onclick
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrows()
    {
        $jsonArray = [];
        $jsonArray[] = ['startFunction' => 'selectCreator'];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = ['startFunction' => 'discardCreator'];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);

        return [$toRightImage, $toLeftImage];
    }
    /**
     * Remove references to deleted creators in collection defaults fields.
     *
     * @param array $deleteIds Array of creator IDs that have been deleted
     */
    private function collectionDefaults($deleteIds)
    {
        $recordset = $this->db->select('collection', ['collectionId', 'collectionDefault']);
        while ($row = $this->db->fetchRow($recordset)) {
            $defaults = unserialize(base64_decode($row['collectionDefault']));
            if (!is_array($defaults) || !array_key_exists('creators', $defaults) || empty(array_intersect($deleteIds, $defaults['creators']))) {
                continue;
            }
            $defaults['creators'] = $this->reOrderCreator($defaults['creators'], $deleteIds);
            $updateArray['collectionDefault'] = base64_encode(serialize($defaults));
            $this->db->formatConditions(['collectionId' => $row['collectionId']]);
            $this->db->update('collection', $updateArray);
        }
    }
    /**
     * Reorder creator order for any creator role when a creator has been deleted
     *
     * @param array $creators Array of creators in 'creators' position of collection::collectionDefault field of database
     * @param array $deleteIds Array of creator IDs that have been deleted
     *
     * @return array
     */
    private function reOrderCreator($creators, $deleteIds)
    {
        $allCreators = $newCreators = [];
        $temp = $creators;
        foreach ($temp as $key => $creatorId) {
            if (array_search($creatorId, $deleteIds) !== FALSE) {
                unset($creators[$key]);
            }
        }
        foreach ($creators as $creator => $creatorId) {
            $split = UTF8::mb_explode('_', $creator);
            $newKey = $split[0] . '_' . $split[1];
            $allCreators[$split[0]]['order'][$newKey] = $split[1];
            $allCreators[$split[0]]['creators'][$newKey]['select'] = $creatorId;
        }
        foreach ($allCreators as $role => $array) {
            $index = 0;
            asort($array['order']);
            foreach ($array['order'] as $key => $value) {
                $newKey = $role . '_' . $index . '_select';
                $newCreators[$newKey] = $allCreators[$role]['creators'][$key]['select'];
                ++$index;
            }
        }

        return $newCreators;
    }
    /**
     * Format names returned from database
     *
     * @param array $row
     * @param array $creators Array reference to be written to
     */
    private function formatNames(&$row, &$creators)
    {
        if ($row['creatorId']) {
            $name = '';

            if ($row['creatorPrefix']) {
                $name .= $row['creatorPrefix'] . ' ';
            }

            $name .= $row['creatorSurname'];

            if ($row['creatorFirstname']) {
                $name .= ', ' . $row['creatorFirstname'] . ' ';
                if ($row['creatorInitials']) {
                    $name .= ' ' . str_replace(' ', '.', $row['creatorInitials']) . '.';
                }
            } elseif ($row['creatorInitials']) {
                $name .= ', ' . str_replace(' ', '.', $row['creatorInitials']) . '.';
            }
			
			$name = \HTML\dbToFormTidy($name);
			// Collapse all whitespaces in one space
			$name = preg_replace("/\\s+/u", " ", trim($name));
            $creators[$row['creatorId']] = $name;
        }
    }
}
