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
 * USERTAGS
 *
 * Common methods for handling user tags.
 *
 * @package wikindx\core\usergroups
 */
class USERTAGS
{
    /** object */
    private $db;
    /** object */
    private $session;
    /** object */
    private $commonBib;

    /**
     *	USERTAGS
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();

        $this->session = FACTORY_SESSION::getInstance();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
    }
    /**
     * grab all current usertags for this user as assoc. array
     *
     * @param bool $userBib Default is FALSE
     * @param array $typeArray Default is FALSE
     * @param bool $populated Default is FALSE
     *
     * @return array
     */
    public function grabAll($userBib = FALSE, $typeArray = FALSE, $populated = FALSE)
    {
        $this->db->leftJoin('resource_user_tags', 'resourceusertagsTagId', 'usertagsId');
        if ($userBib) {
            $this->commonBib->userBibCondition('resourceusertagsResourceId');
        }
        if (is_array($typeArray) && !empty($typeArray)) {
            $this->db->leftJoin('resource', 'resourceId', 'resourceusertagsResourceId');
            foreach ($typeArray as $type) {
                $conditions[] = $type;
            }
            $this->db->formatConditionsOneField($conditions, 'resourceType');
        }
        $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
        if ($populated) {
            $this->db->groupBy(['usertagsId', 'usertagsTag', 'resourceusertagsTagId'], TRUE, $this->db->count('resourceusertagsTagId') . '>0');
            $resultset = $this->db->selectCounts('user_tags', 'resourceusertagsTagId', ['usertagsId', 'usertagsTag'], FALSE, FALSE);
        } else {
            $resultset = $this->db->select('user_tags', ['usertagsId', 'usertagsTag']);
        }
        $array = [];
        while ($row = $this->db->fetchRow($resultset)) {
            $array[$row['usertagsId']] = \HTML\dbToFormTidy($row['usertagsTag']);
        }

        return $array;
    }
    /**
     * Does this usertag already exist in the database?
     *
     * @param string $usertag
     *
     * @return mixed FALSE|usertag ID
     */
    public function checkExists($usertag)
    {
        $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions($this->db->formatFields('usertagsTag') . $this->db->like(FALSE, $usertag, FALSE));
        $resultset = $this->db->select('user_tags', 'usertagsId');
        if ($this->db->numRows($resultset)) {
            return $this->db->fetchOne($resultset);
        } else {
            return FALSE; // not found
        }
    }
    /**
     * Make the usertag select transfer arrows to transfer usertags between select boxes with onclick
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrows()
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'selectUserTag',
        ];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'discardUserTag',
        ];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);

        return [$toRightImage, $toLeftImage];
    }
}
