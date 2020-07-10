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
 * Common methods for user bibliographies
 *
 * @package wikindx\core\bibliographies
 */
class BIBLIOGRAPHYCOMMON
{
    /** boolean */
    public $bailOut = FALSE;
    /** object */
    private $db;
    /** array */
    private $vars;
    /** object */
    private $session;
    /** object */
    private $messages;

    /**
     * BIBLIOGRAPHYCOMMON
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
    }
    /**
     * Get an array of all available bibliographies inc. the MASTER bibliography
     *
     * @return array
     */
    public function getBibsArray()
    {
        $otherBibs = $bibsU = $bibsUG = $bibsArray = [];
        // Get this user's bibliographies and group bibliographies user belongs to
        $bibsU = $this->getUserBibs();
        $bibsUG = $this->getGroupBibs();
        // add main wikindx bibliography to array with id of 0
        $bibsArray[0] = $this->messages->text("user", "masterBib");
        if (!empty($bibsU)) {
            $bibsArray[-1] = $this->messages->text('user', 'userBibs');
            foreach ($bibsU as $key => $value) {
                $bibsArray[$key] = $value;
            }
        }
        if (!empty($bibsUG)) {
            $bibsArray[-2] = $this->messages->text('user', 'userGroupBibs');
            foreach ($bibsUG as $key => $value) {
                $bibsArray[$key] = $value;
            }
        }
        if (!empty($otherBibs)) {
            $bibsArray[-3] = $this->messages->text('user', 'otherBibs');
            foreach ($otherBibs as $key => $value) {
                $bibsArray[$key] = $value;
            }
        }
        if (count($bibsArray) == 1) { // only the master bib
            $bibsArray = [];
            $this->session->setVar("setup_Bibliographies", FALSE);
        }

        return $bibsArray;
    }
    /**
     * Display bibliography being browsed
     *
     * @param bool $hint Default is FALSE
     *
     * @return string
     */
    public function displayBib($hint = FALSE)
    {
        $userBib = $this->session->getVar("mywikindx_Bibliography_use");
        if ($userBib) {
            $this->db->formatConditions(['userbibliographyId' => $userBib]);
            $recordset = $this->db->select('user_bibliography', 'userbibliographyTitle');
            $row = $this->db->fetchRow($recordset);
            $bib = $row['userbibliographyTitle'];
        } elseif (WIKINDX_MULTIUSER) {
            $bib = $this->messages->text("user", "masterBib");
        } else {
            return '';
        }
        if ($hint) {
            return \HTML\span(' (' . $this->messages->text("user", "bibliography") . ": " . $bib . ')', 'hint');
        }

        return ' (' . $this->messages->text("user", "bibliography") . ": " . $bib . ')'; // else
    }
    /**
     * Get user bibliographies
     *
     * @return array
     */
    public function getUserBibs()
    {
        if (!$this->session->getVar("setup_UserId")) {
            return [];
        }
        // Get this user's bibliographies
        $tempU = [];
        $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->formatConditions($this->db->formatFields('userbibliographyUserGroupId') . ' IS NULL');
        $this->db->orderBy('userbibliographyTitle');
        $recordset = $this->db->select('user_bibliography', ['userbibliographyId', 'userbibliographyTitle']);
        while ($row = $this->db->fetchRow($recordset)) {
            $tempU[$row['userbibliographyId']] = \HTML\dbToFormTidy($row['userbibliographyTitle']);
        }

        return $tempU;
    }
    /**
     * Get group bibliographies
     *
     * @return array
     */
    public function getGroupBibs()
    {
        if (!$this->session->getVar("setup_UserId")) {
            return [];
        }
        $tempUG = [];
        // Get group bibliographies user belongs to
        $this->db->formatConditions(['usergroupsusersUserId' => $this->session->getVar("setup_UserId")]);
        $subQ = $this->db->subQuery(
            $this->db->selectNoExecute('user_groups_users', 'usergroupsusersGroupId', TRUE),
            't',
            TRUE,
            TRUE
        );
        $this->db->formatConditions($this->db->formatFields('userbibliographyUserGroupId') . $this->db->equal .
            $this->db->formatFields('usergroupsusersGroupId'));
        $this->db->orderBy('userbibliographyTitle');
        $recordset = $this->db->selectFromSubQuery(
            'user_bibliography',
            ['userbibliographyId', 'userbibliographyUserGroupId', 'userbibliographyTitle'],
            $subQ
        );
        while ($row = $this->db->fetchRow($recordset)) {
            $tempUG[$row['userbibliographyId']] = \HTML\dbToFormTidy($row['userbibliographyTitle']);
        }

        return $tempUG;
    }
    /**
     * Set a SQL condition clause if we are browsing a user bibliography to ensure that
     * listed, selected or searched resources come only from that user bibliography.
     *
     * @param int $joinField The resource ID field on which to join the user_bibliography_resource table. Default is FALSE
     *
     * @return bool TRUE if a bibliography condition was set
     */
    public function userBibCondition($joinField = FALSE)
    {
        if ($this->bailOut) {
            return FALSE;
        }
        if ($useBib = $this->session->getVar("mywikindx_Bibliography_use")) {
            $this->db->formatConditions(['userbibliographyresourceBibliographyId' => $useBib]);
            if ($joinField) {
                $this->db->leftJoin('user_bibliography_resource', 'userbibliographyresourceResourceId', $joinField);
            }

            return TRUE;
        }

        return FALSE;
    }
}
