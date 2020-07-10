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
 * TAG
 *
 * An import may be tagged so that the resources in it may be tracked in later list and delete operations.
 *
 * @package wikindx\core\miscellaneous
 */
class TAG
{
    /** object */
    private $db;

    /**
     *	TAG class
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
    }
    /**
     * Get tags from tag table.
     *
     * @return array|false [id => group]
     */
    public function grabAll()
    {
        $this->db->orderBy('tagTag');
        $recordset = $this->db->select('tag', ['tagId', 'tagTag']);
        while ($row = $this->db->fetchRow($recordset)) {
            $tags[$row['tagId']] = \HTML\dbToFormTidy($row['tagTag']);
        }
        if (isset($tags)) {
            return $tags;
        } else {
            return FALSE;
        }
    }
    /**
     * Does this tag already exist in the database?
     *
     * @param string $tag
     *
     * @return false|int
     */
    public function checkExists($tag)
    {
        $this->db->formatConditions(['tagTag' => $tag]);
        $resultset = $this->db->select('tag', 'tagId');
        if ($this->db->numRows($resultset)) {
            return $this->db->fetchOne($resultset);
        } else {
            return FALSE; // not found
        }
    }
}
