<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * RESOURCE TYPE
 *
 * Handle resource types
 *
 * @package wikindx\core\type
 */
class TYPE
{
    /** object */
    private $db;
    /** object */
    private $commonBib;
    /** object */
    private $messages;

    /**
     *	RESOURCE TYPE
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
    }
    /**
     * Get resource types for which resources exist in database
     *
     * @param bool $userBib FALSE (default) or TRUE (return only resource types within current user bibliography)
     * @param mixed $metadata FALSE or array of metadata fields ('quote', 'quoteComment' etc.) which returned resource types must have. Default is []
     *
     * @return mixed FALSE|array
     */
    public function grabAll($userBib = FALSE, $metadata = [])
    {
        $subQuery = FALSE;
        if (is_array($metadata) && !empty($metadata)) {
            $unions = [];
            foreach ($metadata as $mType) {
                if ($mType == 'quote') {
                    $this->db->formatConditions(['resourcemetadataType' => 'q']);
                    $unions[] = $this->db->selectNoExecute(
                        'resource_metadata',
                        [['resourcemetadataResourceId' => 'id']],
                        TRUE,
                        TRUE,
                        TRUE
                    );
                } elseif ($mType == 'quoteComment') {
                    $this->db->formatConditions(['resourcemetadataType' => 'q']);
                    $unions[] = $this->db->selectNoExecute(
                        'resource_metadata',
                        [['resourcemetadataResourceId' => 'id']],
                        TRUE,
                        TRUE,
                        TRUE
                    );
                } elseif ($mType == 'paraphrase') {
                    $this->db->formatConditions(['resourcemetadataType' => 'p']);
                    $unions[] = $this->db->selectNoExecute(
                        'resource_metadata',
                        [['resourcemetadataResourceId' => 'id']],
                        TRUE,
                        TRUE,
                        TRUE
                    );
                } elseif ($mType == 'paraphraseComment') {
                    $this->db->formatConditions(['resourcemetadataType' => 'p']);
                    $unions[] = $this->db->selectNoExecute(
                        'resource_metadata',
                        [['resourcemetadataResourceId' => 'id']],
                        TRUE,
                        TRUE,
                        TRUE
                    );
                }
                if ($mType == 'musing') {
                    $this->db->formatConditions(['resourcemetadataType' => 'm']);
                    $unions[] = $this->db->selectNoExecute(
                        'resource_metadata',
                        [['resourcemetadataResourceId' => 'id']],
                        TRUE,
                        TRUE,
                        TRUE
                    );
                }
            }
            if (!empty($unions)) {
                $subQuery = $this->db->subQuery($this->db->union($unions), 't');
            }
        }
        if ($userBib) {
            if ($subQuery) {
                $this->commonBib->userBibCondition('id');
            } else {
                $this->commonBib->userBibCondition('resourceId');
            }
        }
        $this->db->groupBy('resourceType');
        $this->db->orderBy('resourceType');
        if ($subQuery) {
            $this->db->leftJoin('resource', 'resourceId', 'id');
            $recordset = $this->db->selectFromSubQuery(FALSE, 'resourceType', $subQuery);
        } else {
            $recordset = $this->db->select('resource', 'resourceType');
        }
        while ($row = $this->db->fetchRow($recordset)) {
            if (!$row['resourceType']) {
                continue;
            }
            $types[$row['resourceType']] = $this->messages->text("resourceType", $row['resourceType']);
        }
        if (isset($types)) {
            return $types;
        } else {
            return FALSE;
        }
    }
}
