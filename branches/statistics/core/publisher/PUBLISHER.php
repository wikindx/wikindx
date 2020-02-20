<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * PUBLISHER
 *
 * Common routines for dealing with publishers
 *
 * @package wikindx\core\publisher
 */
class PUBLISHER
{
    /** object */
    private $db;
    /** object */
    private $commonBib;
    /** object */
    private $gatekeep; //!< there are some functions requiring write access
    /** object */
    private $map;
    /** object */
    private $messages;

    /**
     *	PUBLISHER
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();

        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->map = FACTORY_PUBLISHERMAP::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
    }
    /**
     * Get publishers from WKX_publisher.  Return associative array of id => type
     *
     * @param string $type Default is FALSE
     * @param int $userBib Default is FALSE
     * @param array $typeArray Default is FALSE
     *
     * @return mixed FALSE|array of publishers
     */
    public function grabAll($type = FALSE, $userBib = FALSE, $typeArray = FALSE)
    {
        if (!$userBib && !$type && !is_array($typeArray))
        {
            if (is_array($publishers = $this->db->readCache('cacheResourcePublishers')))
            {
                return $publishers;
            }
        }
        if ($type)
        {
            $this->db->formatConditionsOneField([$type, ' IS NULL'], 'publisherType');
        }
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
            $this->db->formatConditions(['resourcemiscpublisher' => ' IS NOT NULL']);
            $subQuery = $this->db->subQuery($this->db->selectNoExecute('resource', 'resourcemiscPublisher', TRUE), 't', TRUE, TRUE);
            $this->db->formatConditions(['publisherId' => ' IS NOT NULL']);
            $this->db->leftJoin('publisher', 'publisherId', 'resourcemiscPublisher');
            $userBib = FALSE;
        }
        if ($userBib)
        {
            $this->db->leftJoin('resource_misc', 'resourcemiscPublisher', 'publisherId');
            $this->commonBib->userBibCondition('resourcemiscId');
        }
        $this->db->orderBy('publisherName');
        $this->db->orderBy('publisherLocation');
        if (isset($subQuery))
        {
            $recordset = $this->db->query($this->db->selectNoExecuteFromSubQuery(
                FALSE,
                ['publisherId', 'publisherName', 'publisherLocation'],
                $subQuery
            ));
        }
        else
        {
            $recordset = $this->db->select('publisher', ['publisherId', 'publisherName', 'publisherLocation'], TRUE);
        }
        while ($row = $this->db->fetchRow($recordset))
        {
            if ($row['publisherLocation'])
            {
                $publishers[$row['publisherId']] = \HTML\dbToFormTidy($row['publisherName'] .
                ": " . $row['publisherLocation']);
            }
            else
            {
                $publishers[$row['publisherId']] = \HTML\dbToFormTidy($row['publisherName']);
            }
        }
        if (isset($publishers))
        {
            // (re)create cache
            if (!$userBib && !$type && !is_array($typeArray))
            {
                $this->db->writeCache('cacheResourcePublishers', $publishers);
            }

            return $publishers;
        }

        return FALSE;
    }
    /**
     * Return array of menu items to browse publishers from under the Resource|Browse menu.
     *
     * @return array Empty if no publishers
     */
    public function browseMenuArray()
    {
        $this->db->groupBy('publisherType');
        $recordset = $this->db->select('publisher', 'publisherType');
        if (!$this->db->numRows($recordset))
        {
            return [];
        }
        // Add 'ALL' to array
        $array[$this->messages->text("menu", "browseSubPublisher")] = FALSE;
        $array[$this->messages->text("collection", 'all')] = 'index.php?action=browse_BROWSEPUBLISHER_CORE&method=init&PublisherType=0';
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$row['publisherType'])
            {
                continue;
            }
            $array[$this->messages->text("collection", $row['publisherType'])] =
                'index.php?action=browse_BROWSEPUBLISHER_CORE&method=init&PublisherType=' . $row['publisherType'];
        }

        return $array;
    }
    /**
     * Return array of menu items to edit publishers from under the Resource|Edit menu.
     *
     * @return array Empty if no publishers
     */
    public function editMenuArray()
    {
        $this->db->groupBy('publisherType');
        $recordset = $this->db->select('publisher', 'publisherType');
        if (!$this->db->numRows($recordset))
        {
            return [];
        }
        // Add 'ALL' to array
        $array[$this->messages->text("menu", "browseSubPublisher")] = FALSE;
        $array[$this->messages->text("collection", 'all')] = 'index.php?action=edit_EDITPUBLISHER_CORE&method=init&PublisherType=0';
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$row['publisherType'])
            {
                continue;
            }
            $array[$this->messages->text("collection", $row['publisherType'])] =
                'index.php?action=edit_EDITPUBLISHER_CORE&method=init&PublisherType=' . $row['publisherType'];
        }

        return $array;
    }
    /**
     * Does this publisher already exist in the database?
     *
     * Name must match and location must match if not NULL
     *
     * @param string $name
     * @param string $location
     *
     * @return mixed FALSE|publisher ID
     */
    public function checkExists($name, $location)
    {
        $publisher = str_replace(' ', '', $name . $location);
        $array = [$this->db->formatFields('publisherName'), $this->db->formatFields('publisherLocation')];
        // Concatenate (CONCAT_WS) fields and remove any spaces
        $this->db->formatConditions($this->db->replace(
            $this->db->concat($array, ''),
            ' ',
            '',
            FALSE
        ) . $this->db->equal . $this->db->tidyInput($publisher));
        $resultset = $this->db->select('publisher', 'publisherId');
        if ($this->db->numRows($resultset))
        {
            return $this->db->fetchOne($resultset);
        }

        return FALSE; // not found
    }
    /**
     * Remove resource-less or collection-less publishers
     */
    public function removeHanging()
    {
        $recordset = $this->db->select('publisher', 'publisherId');
        if (!$this->db->numRows($recordset))
        { // nothing to delete
            return;
        }
        while ($row = $this->db->fetchRow($recordset))
        {
            $publisherIds[] = $row['publisherId'];
        }
        // Grab all references to publisher IDs
        $existingPublisherIds = [];
        $this->db->formatConditions(['resourcemiscPublisher' => ' IS NOT NULL']);
        $recordset = $this->db->select('resource_misc', 'resourcemiscPublisher');
        while ($row = $this->db->fetchRow($recordset))
        {
            $existingPublisherIds[] = $row['resourcemiscPublisher'];
        }
        $this->db->leftJoin('resource', 'resourceId', 'resourcemiscId');
        $condition[] = $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('book');
        $condition[] = $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('book_article');
        $condition[] = $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('book_chapter');
        $condition[] = $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('conference_poster');
        $condition[] = $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('conference_paper');
        $condition[] = $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('proceedings_article');
        $condition[] = $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('proceedings');
        $this->db->formatConditions(implode($this->db->or, $condition));
        $this->db->formatConditions(['resourcemiscField1' => ' IS NOT NULL']);
        $recordset = $this->db->select('resource_misc', 'resourcemiscField1');
        while ($row = $this->db->fetchRow($recordset))
        {
            $existingPublisherIds[] = $row['resourcemiscField1'];
        }
        $existingPublisherIds = array_unique($existingPublisherIds);
        if (empty($existingPublisherIds))
        { // no publishers being used any more so delete all
            $this->db->delete('publisher');
            $this->db->deleteCache('cacheResourcePublishers');
            $this->db->deleteCache('cacheMetadataPublishers');
            $this->db->deleteCache('cacheConferenceOrganisers');

            return;
        }
        $deleteIds = array_diff($publisherIds, $existingPublisherIds);
        if (empty($deleteIds))
        {
            return; // nothing to do
        }
        $this->db->formatConditionsOneField($deleteIds, 'publisherId');
        $this->db->delete('publisher');
        $this->db->deleteCache('cacheResourcePublishers');
        $this->db->deleteCache('cacheMetadataPublishers');
        $this->db->deleteCache('cacheConferenceOrganisers');
    }
    /**
     * Inserts a new publisher into the database.
     *
     * Requires write permissions.
     *
     * @param array $v
     * @param bool $alreadyExisted Reference, default is FALSE
     * @param array $errs Reference, default is empty array
     *
     * @return int New publisher ID
     */
    public function insert($v, &$alreadyExisted = FALSE, &$errs = [])
    {
        $this->gatekeep->init();

        if (array_key_exists('name', $v))
        {
            $fields[] = 'publisherName';
            $values[] = trim($v['name']);
        }
        else
        {
            array_push($errs, $this->error->text('inputError', 'missing', 'name'));
        }
        if (array_key_exists('name', $v))
        {
            $fields[] = 'publisherType';
            $values[] = mb_strtolower(trim($v['type']));
        }
        else
        {
            array_push($errs, $this->error->text('inputError', 'missing', 'type'));
        }
        $location = NULL;
        if (array_key_exists('location', $v) && trim($v['location']))
        {
            $fields[] = 'publisherLocation';
            $values[] = $location = trim($v['location']);
        }
        $id = FALSE;
        if (empty($errs))
        {
            if ($id = $this->checkExists($v['name'], $location))
            {
                $alreadyExisted = TRUE;

                return $id;
            }
            $this->db->insert('publisher', $fields, $values);
            $id = $this->db->lastAutoId();
        }

        return $id;
    }
    /**
     * Make the publisher select transfer arrows to transfer publishers between select boxes with onclick
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrows()
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'selectPublisher',
        ];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'discardPublisher',
        ];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);

        return [$toRightImage, $toLeftImage];
    }
}
