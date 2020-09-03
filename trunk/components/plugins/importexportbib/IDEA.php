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
 * IDEA class
 *
 * Export ideas
 */
class IDEA
{
    private $db;
    private $session;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $common;
    private $parentClass;

    /**
     * Constructor
     *
     * @param string $parentClass
     */
    public function __construct($parentClass = FALSE)
    {
        $this->parentClass = $parentClass;
        $this->db = FACTORY_DB::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "EXPORTCOMMON.php"]));
        $this->common = new EXPORTCOMMON();
    }
    /**
     * Display options for exporting
     *
     * @return string
     */
    public function exportOptions()
    {
        $pString = \FORM\formHeader("importexportbib_exportIdea");
        $pString .= \FORM\hidden('method', 'process');
        $selectBox = [1 => $this->pluginmessages->text("allIdeas"), 2 => $this->pluginmessages->text("selectedIdeas")];
        $selectBox = \FORM\selectFBoxValue(FALSE, "selectIdea", $selectBox, 2);
        $pString .= \HTML\p($selectBox . '&nbsp;' . \FORM\formSubmit($this->coremessages->text("submit", "Proceed")), FALSE, "right");
        $this->ideaList();

        return $pString;
    }
    /**
     * list available ideas
     */
    private function ideaList()
    {
        $userObj = FACTORY_USER::getInstance();
        $cite = FACTORY_CITE::getInstance();
        $multiUser = WIKINDX_MULTIUSER;
        $ideaList = [];
        $index = 0;
        // now get ideas
        // Check this user is allowed to read the idea.
        $this->db->formatConditions(['resourcemetadataMetadataId' => ' IS NULL']);
        if (!$this->common->setIdeasCondition()) {
            $this->failure(HTML\p($this->pluginmessages->text("noIdeas"), 'error'));
        }
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataTimestamp', 'resourcemetadataTimestampEdited',
            'resourcemetadataMetadataId', 'resourcemetadataText', 'resourcemetadataAddUserId', 'resourcemetadataPrivate', ]);
        while ($row = $this->db->fetchRow($resultset)) {
            $ideaList[$index]['metadata'] = $cite->parseCitations($row['resourcemetadataText'], 'html');
            if ($multiUser) {
                list($user) = $userObj->displayUserAddEdit($row['resourcemetadataAddUserId'], FALSE, 'idea');
                if ($row['resourcemetadataTimestampEdited'] == '0000-00-00 00:00:00') {
                    $ideaList[$index]['user'] = '&nbsp;' . $this->coremessages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']);
                } else {
                    $ideaList[$index]['user'] = '&nbsp;' . $this->coremessages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']) .
                    ',&nbsp;' . $this->coremessages->text('hint', 'editedBy', $user . '&nbsp;' . $row['resourcemetadataTimestampEdited']);
                }
            }
            $ideaList[$index]['links'] = ['&nbsp;' . \FORM\checkbox(FALSE, 'checkbox_' . $row['resourcemetadataId'], FALSE)];
            ++$index;
        }
        if (!$index) {
            $this->failure(HTML\p($this->pluginmessages->text("noIdeas"), 'error'));
        }
        $ideaList[--$index]['links'][] .= \FORM\formEnd();
        GLOBALS::addTplVar('ideaTemplate', TRUE);
        GLOBALS::addTplVar('ideaList', $ideaList);
    }
    /**
     * failure
     *
     * @param string $error
     */
    private function failure($error)
    {
        GLOBALS::addTplVar('content', $error);
        FACTORY_CLOSE::getInstance();
    }
}
