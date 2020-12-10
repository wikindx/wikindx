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
 * Common methods for collections
 *
 *	A collection is a journal, edited book, CD etc. - something which has
 *	multiple resources within it.
 *
 * @package wikindx\core\collection
 */
class COLLECTION
{
    /** object */
    private $db;
    /** object */
    private $commonBib;
    /** object */
    private $messages;

    /**
     * COLLECTION
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();

        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
    }
    /**
     * Get collection from WKX_collection.  Return associative array of id => collection
     *
     * @param string $type Default is FALSE
     * @param bool $userBib Default is FALSE
     * @param mixed $typeArray Default is FALSE
     *
     * @return mixed
     */
    public function grabAll($type = FALSE, $userBib = FALSE, $typeArray = FALSE)
    {
        if (!$userBib && !$type && !is_array($typeArray))
        {
            if (is_array($collections = $this->db->readCache('cacheResourceCollections')))
            {
                return $collections;
            }
        }
        unset($collections);
        if ($type)
        {
            $this->db->formatConditions(['collectionType' => $type]);
        }
        $thisType = $type;
        if (is_array($typeArray) && !empty($typeArray))
        {
            $this->db->leftJoin('resource_misc', 'resourcemiscId', 'resourceId');
            if ($userBib)
            {
                $this->commonBib->userBibCondition('resourcemiscId');
            }
            foreach ($typeArray as $type)
            {
                $conditions[] = $type;
            }
            $this->db->formatConditionsOneField($conditions, 'resourceType');
            $this->db->formatConditions(['resourcemiscCollection' => ' IS NOT NULL']);
            $subQuery = $this->db->subQuery($this->db->selectNoExecute('resource', 'resourcemiscCollection', TRUE), 't', TRUE, TRUE);
            $this->db->formatConditions(['collectionId' => ' IS NOT NULL']);
            $this->db->leftJoin('collection', 'collectionId', 'resourcemiscCollection');
            $userBib = FALSE;
        }
        if ($userBib)
        {
            $this->db->leftJoin('resource_misc', 'resourcemiscCollection', 'collectionId');
            $this->commonBib->userBibCondition('resourcemiscId');
        }
        $this->db->orderBy('collectionTitle');
        if (isset($subQuery))
        {
            $recordset = $this->db->query($this->db->selectNoExecuteFromSubQuery(
                FALSE,
                ['collectionId', 'collectionType', 'collectionTitle', 'collectionTitleShort'],
                $subQuery
            ));
        }
        else
        {
            $recordset = $this->db->select(
                'collection',
                ['collectionId', 'collectionType', 'collectionTitle', 'collectionTitleShort'],
                TRUE
            );
        }
        while ($row = $this->db->fetchRow($recordset))
        {
            $short = $row['collectionTitleShort'] ?
                " [" . $row['collectionTitleShort'] . ']' : FALSE;
            $title = $row['collectionTitle'] . $short;
            if (!$type)
            {
                $thisType = $row['collectionType'];
            }
            $collections[$row['collectionId']] = \HTML\dbToFormTidy($title, TRUE);
        }
        if (isset($collections))
        {
            // (re)create cache
            if (!$userBib && !$type && !is_array($typeArray))
            {
                $this->db->writeCache('cacheMetadataCollections', $collections);
            }

            return $collections;
        }

        return FALSE;
    }
    /**
     * Return array of menu items to browse collections from under the Resource|Browse menu.
     *
     * @return array Array is empty if no collections
     */
    public function browseMenuArray()
    {
        $this->db->groupBy('collectionType');
        $recordset = $this->db->select('collection', 'collectionType');
        if (!$this->db->numRows($recordset))
        {
            return [];
        }
        // Add 'ALL' to array
        $array[$this->messages->text("menu", "browseSubCollection")] = FALSE;
        $array[$this->messages->text("collection", 'all')] = 'index.php?action=browse_BROWSECOLLECTION_CORE&method=display&collectionType=0';
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$row['collectionType'])
            {
                continue;
            }
            $array[$this->messages->text("collection", $row['collectionType'])] =
                'index.php?action=browse_BROWSECOLLECTION_CORE&method=display&collectionType=' . $row['collectionType'];
        }

        return $array;
    }
    /**
     * Return array of menu items to edit collections from under the Resource|BEdit menu.
     *
     * @return array Array is empty if no collections
     */
    public function editMenuArray()
    {
        $this->db->groupBy('collectionType');
        $recordset = $this->db->select('collection', 'collectionType');
        if (!$this->db->numRows($recordset))
        {
            return [];
        }
        // Add 'ALL' to array
        $array[$this->messages->text("menu", "editSubCollection")] = FALSE;
        $array[$this->messages->text("collection", 'all')] =
            'index.php?action=edit_EDITCOLLECTION_CORE&method=editChooseCollection&edit_collectionType=0';
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$row['collectionType'])
            {
                continue;
            }
            $array[$this->messages->text("collection", $row['collectionType'])] =
                'index.php?action=edit_EDITCOLLECTION_CORE&method=editChooseCollection&edit_collectionType=' .
                $row['collectionType'];
        }

        return $array;
    }
    /**
     * check to see if collection title already exists
     *
     * @param int $id Collection ID. Default is FALSE
     * @param string $title
     * @param string $titleShort
     * @param string $type
     *
     * @return mixed FALSE or ID
     */
    public function checkExists($id = FALSE, $title, $titleShort, $type)
    {
        if ($id && !$type)
        {
            $this->db->formatConditions(['collectionId' => $id]);
            $type = $this->db->selectFirstField('collection', 'collectionType');
        }
        $collection = str_replace(' ', '', $title . $titleShort . $type);
        $array = [$this->db->formatFields('collectionTitle'), $this->db->formatFields('collectionTitleShort'),
            $this->db->formatFields('collectionType'), ];
        // Concatenate (CONCAT_WS) fields and remove any spaces
        $this->db->formatConditions($this->db->replace(
            $this->db->concat($array, ''),
            ' ',
            '',
            FALSE
        ) . $this->db->equal . $this->db->tidyInput($collection));
        //		if ($id)
        //			$this->db->formatConditions(array('collectionId' => $id));
        $resultset = $this->db->select('collection', 'collectionId');
        while ($row = $this->db->fetchRow($resultset))
        {
            if ($id && ($row['collectionId'] == $id))
            { // the collection being edited
                continue;
            }

            return $row['collectionId'];
        }

        return FALSE;
    }
    /**
     * Remove resource-less collections from collection list
     */
    public function removeHanging()
    {
        $deleteIds = [];
        $this->db->formatConditions(['resourcemiscCollection' => ' IS NOT NULL']);
        $subStmt = $this->db->subQuery($this->db->selectNoExecute('resource_misc', 'resourcemiscCollection'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('collectionId') . $this->db->inClause($subStmt, TRUE));
        $recordset = $this->db->select('collection', 'collectionId');
        while ($row = $this->db->fetchRow($recordset))
        {
            $deleteIds[] = $row['collectionId'];
        }
        if (empty($deleteIds))
        {
            return; // nothing to do
        }
        if (!empty($deleteIds))
        {
            $this->db->formatConditionsOneField($deleteIds, 'collectionId');
            $this->db->delete("collection");
            // remove cache files for collections
            $this->db->deleteCache('cacheResourceCollections');
            $this->db->deleteCache('cacheMetadataCollections');
            $this->db->deleteCache('cacheResourceCollectionTitles');
            $this->db->deleteCache('cacheResourceCollectionShorts');
        }
    }
    /**
     * Make the collection select transfer arrows to transfer collections between select boxes with onclick
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrows()
    {
        $jsonArray = [];
        $jsonArray[] = ['startFunction' => 'selectCollection'];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = ['startFunction' => 'discardCollection'];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);

        return [$toRightImage, $toLeftImage];
    }
}
