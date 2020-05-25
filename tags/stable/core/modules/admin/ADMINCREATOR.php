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
 *	ADMINCREATOR class.
 *
 *	Administration of creators
 */
class ADMINCREATOR
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $creator;
    private $gatekeep;
    private $badInput;
    private $newCreatorId;
    private $newName;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();

        $this->creator = FACTORY_CREATOR::getInstance();

        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->gatekeep->init();
        $this->session->clearArray('edit');
    }
    /**
     * display options for creator merging
     *
     * @param false|string $message
     */
    public function mergeInit($message = FALSE)
    {
        $creators = $this->creator->grabAll();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "mergeCreators"));
        $pString = $message;
        if (is_array($creators) && !empty($creators)) {
            $pString .= \HTML\p($this->messages->text("misc", "creatorMerge"));
            $pString .= \FORM\formHeader('admin_ADMINCREATOR_CORE');
            $pString .= \FORM\hidden("method", "mergeProcess");
            $pString .= \HTML\tableStart();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
                \HTML\strong($this->messages->text("misc", "creatorMergeOriginal")),
                "creatorIds",
                $creators,
                20
            ) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint'));
            $pString .= \HTML\tdStart();
            $pString .= \HTML\tableStart('left');
            $pString .= \HTML\trStart();
            // add 0 => IGNORE to creators array
            $temp[0] = $this->messages->text("misc", "ignore");
            foreach ($creators as $key => $value) {
                $temp[$key] = $value;
            }
            $creators = $temp;
            unset($temp);
            $pString .= \HTML\td('&nbsp;');
            $pString .= \HTML\td(\FORM\selectFBoxValue(
                \HTML\strong($this->messages->text("misc", "creatorMergeTarget")),
                "creatorIdsOutput",
                $creators,
                20
            ));
            $pString .= \HTML\td(\FORM\textInput(
                $this->messages->text("resources", "firstname"),
                "firstname",
                FALSE,
                20,
                255
            ));
            $pString .= \HTML\td(\FORM\textInput(
                $this->messages->text("resources", "initials"),
                "initials",
                FALSE,
                6,
                255
            ) . BR .
                \HTML\span($this->messages->text("hint", "initials"), 'hint'));
            $pString .= \HTML\td(\FORM\textInput(
                $this->messages->text("resources", "prefix"),
                "prefix",
                FALSE,
                11,
                10
            ));
            $pString .= \HTML\td(\FORM\textInput(
                $this->messages->text("resources", "surname"),
                "surname",
                FALSE,
                20,
                255
            ) . " " . \HTML\span('*', 'required'));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \HTML\tdEnd();
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
            $pString .= \FORM\formEnd();
            GLOBALS::addTplVar('content', $pString);
        } else {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noCreators'));
        }
    }
    /**
     * start merging process
     */
    public function mergeProcess()
    {
        $this->validateInput('merge');
        $creatorIds = $this->vars['creatorIds'];
        $this->newCreatorId = $this->insertCreator();
        $this->db->formatConditions(['creatorId' => $this->newCreatorId]);
        $row = $this->db->fetchRow($this->db->select('creator', ['creatorSurname', 'creatorFirstname', 'creatorInitials']));
        $this->newName = $row['creatorSurname'];
        foreach ($creatorIds as $oldId) {
            // Remove old creators
            if ($oldId != $this->newCreatorId) {
                $this->db->formatConditions(['creatorId' => $oldId]);
                $this->db->delete('creator');
            }
            $this->updateTableMerge($oldId);
        }
        // remove cache files for creators
        $this->db->deleteCache('cacheResourceCreators');
        $this->db->deleteCache('cacheMetadataCreators');

        return $this->mergeInit($this->success->text("creatorMerge"));
    }
    /**
     * Insert new creator or return ID if already exists
     *
     * @return int
     */
    public function insertCreator()
    {
        if ($this->vars['creatorIdsOutput']) {
            return $this->vars['creatorIdsOutput'];
        }

        return $this->creator->insert(['surname' => $this->vars['surname'], 'initials' => $this->vars['initials'],
            'firstname' => $this->vars['firstname'], 'prefix' => $this->vars['prefix'], ]);
    }
    /**
     * display options for creator grouping
     *
     * @param false|string $message
     */
    public function groupInit($message = FALSE)
    {
        \AJAX\loadJavascript([WIKINDX_BASE_URL . '/core/modules/list/searchSelect.js']);
        $potentialMasters = $this->creator->grabGroupAvailableMasters();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "groupCreators"));
        $pString = $message;
        if (is_array($potentialMasters) && !empty($potentialMasters)) {
            $potentialMembers = $this->creator->grabGroupAvailableMembers();
            if (!is_array($potentialMembers)) {
                $potentialMembers = $this->creator->grabGroupAvailableMembers(TRUE);
            }
            foreach ($potentialMasters as $id => $name) { // array_shift() breaks ids!
                break;
            }
            reset($potentialMasters);
            $existingMembers = $this->creator->grabGroupMembers($id);
            // add 0 => IGNORE to potentialMembers array
            $temp[0] = $this->messages->text("misc", "ignore");
            foreach ($potentialMembers as $key => $value) {
                $temp[$key] = $value;
            }
            $potentialMembers = $temp;
            unset($temp);
            $pString .= \HTML\p($this->messages->text("misc", "creatorGroup"));
            $pString .= \FORM\formHeader('admin_ADMINCREATOR_CORE');
            $pString .= \FORM\hidden("method", "groupProcess");
            $pString .= \HTML\tableStart();
            $pString .= \HTML\trStart();
            $jScript = 'index.php?action=admin_ADMINCREATOR_CORE&method=groupDiv';
            $jsonArray[] = [
                'startFunction' => 'triggerFromMultiSelect',
                'script' => "$jScript",
                'triggerField' => 'creatorMaster',
                'targetDiv' => 'creatorIds',
            ];
            $js = \AJAX\jActionForm('onclick', $jsonArray);
            if (!is_array($existingMembers)) {
                $td = \HTML\div('creatorIds', \FORM\selectFBoxValueMultiple(
                    \HTML\strong($this->messages->text("misc", "creatorGroupMember")),
                    "creatorIds",
                    $potentialMembers,
                    20,
                    FALSE,
                    $js
                ) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint') .
                    \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed"))));
            } else {
                $existingMembers = array_keys($existingMembers);
                $td = \HTML\div('creatorIds', \FORM\selectedBoxValueMultiple(
                    \HTML\strong($this->messages->text("misc", "creatorGroupMember")),
                    "creatorIds",
                    $potentialMembers,
                    $existingMembers,
                    20,
                    FALSE,
                    $js
                ) .
                    BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint') .
                    \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed"))));
            }
            $pString .= \HTML\td($td);
            $pString .= \HTML\td(\FORM\selectFBoxValue(
                \HTML\strong($this->messages->text("misc", "creatorGroupMaster")),
                "creatorMaster",
                $potentialMasters,
                20
            ));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \FORM\formEnd();
            GLOBALS::addTplVar('content', $pString);
        } else {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noCreators'));
        }
    }
    /**
     * AJAX driven select box for creator group members
     */
    public function groupDiv()
    {
        $potentialMembers = $this->creator->grabGroupAvailableMembers();
        if (!is_array($potentialMembers)) {
            $potentialMembers = $this->creator->grabGroupAvailableMembers(TRUE);
        }
        $existingMembers = $this->creator->grabGroupMembers($this->vars['ajaxReturn']);
        // add 0 => IGNORE to potentialMembers array
        $temp[0] = $this->messages->text("misc", "ignore");
        foreach ($potentialMembers as $key => $value) {
            $temp[$key] = $value;
        }
        $potentialMembers = $temp;
        unset($temp);
        if (!is_array($existingMembers)) {
            unset($potentialMembers[$this->vars['ajaxReturn']]);
            $div = \HTML\div('creatorIdsOutput', \FORM\selectFBoxValueMultiple(
                \HTML\strong($this->messages->text("misc", "creatorGroupMember")),
                "creatorIds",
                $potentialMembers,
                20
            ) . BR .
                \HTML\span($this->messages->text("hint", "multiples"), 'hint') .
                \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed"))));
        } else {
            unset($potentialMembers[$this->vars['ajaxReturn']]);
            $existingMembers = array_keys($existingMembers);
            $div = \HTML\div('creatorIdsOutput', \FORM\selectedBoxValueMultiple(
                \HTML\strong($this->messages->text("misc", "creatorGroupMember")),
                "creatorIds",
                $potentialMembers,
                $existingMembers,
                20
            ) . BR .
                \HTML\span($this->messages->text("hint", "multiples"), 'hint') .
                \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed"))));
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * start grouping process
     */
    public function groupProcess()
    {
        $this->validateInput('group');
        $creatorIds = $this->vars['creatorIds'];
        $targetCreatorId = $this->vars['creatorMaster'];
        if (($index = array_search($targetCreatorId, $creatorIds)) !== FALSE) {
            unset($creatorIds[$index]);
        }
        // First, remove references to this creator as group master
        $this->db->formatConditions(['creatorSameAs' => $targetCreatorId]);
        $this->db->updateNull('creator', 'creatorSameAs');
        $this->db->formatConditionsOneField($creatorIds, 'creatorId');
        $this->db->update('creator', ['creatorSameAs' => $targetCreatorId]);

        return $this->groupInit($this->success->text("creatorGroup"));
    }
    /**
     * display options for creator ungrouping
     *
     * @param false|string $message
     */
    public function ungroupInit($message = FALSE)
    {
        \AJAX\loadJavascript([WIKINDX_BASE_URL . '/core/modules/list/searchSelect.js']);
        $mastersCopy = $masters = $this->creator->grabGroupMasters();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "ungroupCreators"));
        $pString = $message;
        if (is_array($masters) && !empty($masters)) {
            foreach ($mastersCopy as $id => $name) {
                $initialMasterId = $id;

                break;
            }
            $creators = $this->creator->grabGroupMembers($initialMasterId);
            $pString .= \FORM\formHeader('admin_ADMINCREATOR_CORE');
            $pString .= \FORM\hidden("method", "ungroupProcess");
            $pString .= \HTML\tableStart();
            $pString .= \HTML\trStart();
            $jScript = 'index.php?action=admin_ADMINCREATOR_CORE&method=ungroupDiv';
            $jsonArray[] = [
                'startFunction' => 'triggerFromMultiSelect',
                'script' => "$jScript",
                'triggerField' => 'creatorMaster',
                'targetDiv' => 'creatorIds',
            ];
            $js = \AJAX\jActionForm('onclick', $jsonArray);
            $pString .= \HTML\td(\FORM\selectFBoxValue(
                $this->messages->text("misc", "creatorGroupMaster"),
                "creatorMaster",
                $masters,
                20,
                FALSE,
                $js
            ));
            $td = \HTML\div('creatorIds', \FORM\selectFBoxValueMultiple(
                $this->messages->text("misc", "creatorUngroup"),
                "creatorIds",
                $creators,
                20
            ) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint') .
                \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Remove"))));
            $pString .= \HTML\td($td);
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \FORM\formEnd();
            GLOBALS::addTplVar('content', $pString);
        } else {
            GLOBALS::addTplVar('content', $pString);
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noGroupMasterCreators'));
        }
    }
    /**
     * AJAX driven select box for creator group members
     */
    public function ungroupDiv()
    {
        $creators = $this->creator->grabGroupMembers($this->vars['ajaxReturn']);
        $div = \FORM\selectFBoxValueMultiple(
            $this->messages->text("misc", "creatorUngroup"),
            "creatorIds",
            $creators,
            20
        ) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint') .
            \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * ungroupProcess
     */
    public function ungroupProcess()
    {
        $this->validateInput('ungroup');
        $creatorIds = $this->vars['creatorIds'];
        foreach ($creatorIds as $oldId) {
            $this->db->formatConditionsOneField($creatorIds, 'creatorId');
            $this->db->updateNull('creator', 'creatorSameAs');
        }

        return $this->ungroupInit($this->success->text("creatorUngroup"));
    }
    /**
     * Remove old creator references from resource_creator and add new creator reference.
     *
     * @param int $oldId
     */
    private function updateTableMerge($oldId)
    {
        // Select all resources referencing this old creator and replace reference with existing creator -- check if main or not.
        $this->db->formatConditions(['resourcecreatorCreatorId' => $oldId]);
        $this->db->update('resource_creator', ['resourcecreatorCreatorId' => $this->newCreatorId]);
        // Next, update all rows where $oldId = creatorMain
        $this->db->formatConditions(['resourcecreatorCreatorMain' => $oldId]);
        $updateArray['resourcecreatorCreatorMain'] = $this->newCreatorId;
        // remove all punctuation (keep apostrophe and dash for names such as Grimshaw-Aagaard and D'Eath)
        $updateArray['resourcecreatorCreatorSurname'] = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s\-\']/u', '', $this->newName));
        $this->db->update('resource_creator', $updateArray);
    }
    /**
     * validate input
     *
     * @param string $process
     */
    private function validateInput($process)
    {
        if ($process == 'merge') {
            if (!array_key_exists("creatorIds", $this->vars) || empty($this->vars['creatorIds'])
                 || (count($this->vars['creatorIds']) == 1)) {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'mergeInit');
            }
            if (!array_key_exists("creatorIdsOutput", $this->vars) || empty($this->vars['creatorIdsOutput'])) {
                if (!array_key_exists("surname", $this->vars) || !trim($this->vars['surname'])) {
                    $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'mergeInit');
                }
            } elseif ((!array_key_exists("surname", $this->vars) || !trim($this->vars['surname'])) &&
                (count($this->vars['creatorIds']) == 1) && $this->vars['creatorIds'][0] == $this->vars['creatorIdsOutput']) {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'mergeInit');
            }
        } elseif ($process == 'group') {
            if (!array_key_exists("creatorIds", $this->vars) || empty($this->vars['creatorIds'])) {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'groupInit');
            }
            if (!array_key_exists("creatorMaster", $this->vars)) {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'groupInit');
            }
            if ((count($this->vars['creatorIds']) == 1) && $this->vars['creatorIds'][0] == $this->vars['creatorMaster']) {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'groupInit');
            }
            if ((count($this->vars['creatorIds']) == 1) && $this->vars['creatorIds'][0] == 0) {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'groupInit');
            }
        } elseif ($process == 'ungroup') {
            if (!array_key_exists("creatorIds", $this->vars) || empty($this->vars['creatorIds'])) {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'ungroupInit');
            }
        }
    }
}
