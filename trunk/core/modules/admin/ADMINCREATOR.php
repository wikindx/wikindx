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
    private $potentialMasters;

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
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "help", "HELPMESSAGES.php"]));
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('creatorGroups'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "groupCreators"));
        $pString = $message;
        $this->potentialMasters = $this->creator->grabGroupAvailableMembers();
        if (is_array($this->potentialMasters) && !empty($this->potentialMasters)) {
            foreach ($this->potentialMasters as $id => $name) { // array_shift() breaks ids!
                break;
            }
            reset($this->potentialMasters);

            $pString .= \FORM\formHeader('admin_ADMINCREATOR_CORE', "onsubmit=\"selectAll();return true;\"");
            $pString .= \FORM\hidden("method", "groupProcess");
            $pString .= \HTML\tableStart();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td(\HTML\div('masterDiv', $this->masterDiv(TRUE)));
            $pString .= \HTML\td(\HTML\div('memberDiv', $this->memberDiv($id)));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
            $pString .= \FORM\formEnd();
            GLOBALS::addTplVar('content', $pString);
        } else {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noCreators'));
        }
        \AJAX\loadJavascript(WIKINDX_BASE_URL . '/core/modules/admin/admincreator.js?ver=' . WIKINDX_PUBLIC_VERSION);
    }
    /**
     * Show group masters' DIV
     * 
     * @param bool $initialize Default FALSE
     * @return string
     */
    public function masterDiv($initialize = FALSE)
    {
// Potential master list
		$jScript = 'index.php?action=admin_ADMINCREATOR_CORE&method=memberDiv';
		$jsonArray[] = [
			'startFunction' => 'triggerFromMultiSelect',
			'script' => "$jScript",
			'triggerField' => 'creatorMaster',
			'targetDiv' => 'memberDiv',
		];
		$js1 = \AJAX\jActionForm('onchange', $jsonArray);
		$jsonArray = [];
		$jScript = 'index.php?action=admin_ADMINCREATOR_CORE&method=masterDiv';
		$jsonArray[] = [
			'startFunction' => 'triggerFromCheckbox',
			'script' => "$jScript",
			'triggerField' => 'onlyMasters',
			'targetDiv' => 'masterDiv',
		];
		$jScript = 'index.php?action=admin_ADMINCREATOR_CORE&method=memberDiv';
		$jsonArray[] = [
			'startFunction' => 'triggerFromCheckbox',
			'script' => "$jScript",
			'triggerField' => 'onlyMasters',
			'targetDiv' => 'memberDiv',
		];
		$js2 = \AJAX\jActionForm('onchange', $jsonArray);
		if ($initialize) {
			return \FORM\selectFBoxValue(
				\HTML\strong($this->messages->text("misc", "creatorGroupMaster")),
				"creatorMaster",
				$this->potentialMasters,
				20, 
				FALSE,
				$js1
				) . \HTML\p($this->messages->text("misc", "creatorOnlyMasters") . ':&nbsp;' . 
					\FORM\checkbox(FALSE, "onlyMasters", FALSE, FALSE, $js2)
			);
		}
		else {
			if ($this->vars['ajaxReturn'] == 'on') {
				$checked = TRUE;
				$masters = $this->creator->grabGroupMasters();
			}
			else {
				$checked = FALSE;
				$masters = $this->creator->grabGroupAvailableMembers();
			}
			$pString = \FORM\selectFBoxValue(
				\HTML\strong($this->messages->text("misc", "creatorGroupMaster")),
				"creatorMaster",
				$masters,
				20, 
				FALSE,
				$js1
				) . \HTML\p($this->messages->text("misc", "creatorOnlyMasters") . ':&nbsp;' . 
					\FORM\checkbox(FALSE, "onlyMasters", $checked, FALSE, $js2)
			);
			GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $pString]));
			FACTORY_CLOSERAW::getInstance();
		}
    }
    /**
     * AJAX driven select box for creator group members
     *
     * @param int $id Default = FALSE
     */
    public function memberDiv($id = FALSE)
    {
// If 'ajaxReturn' is a number, it is the ID of the master creator.
// If 'ajaxReturn is 'on', we are displaying only the current masters and so we must get the first ID of tht list.
// If 'ajaxReturn is 'off', we are displaying all potential masters and so we must get the first ID of tht list.
    	$initial = FALSE;
    	if ($id) { // Initial loading of page so check for existing members for first-listed potential master
    		$initial = TRUE;
    	}
    	elseif ($this->vars['ajaxReturn'] == 'on') {
			$masters = $this->creator->grabGroupMasters();
			foreach ($masters as $id => $name) { // array_shift() breaks ids!
				break;
			}
    	}
    	elseif ($this->vars['ajaxReturn'] == 'off') {
        		$this->potentialMasters = $this->creator->grabGroupAvailableMembers();
				foreach ($this->potentialMasters as $id => $name) { // array_shift() breaks ids!
					break;
				}
  		}
  		else { // 'ajaxReturn' is a number
  			$id = $this->vars['ajaxReturn'];
  		}
    	$pString = $this->memberDivBoxes($id);
		if ($initial) { // Initial loading of page
			return $pString;
		}
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $pString]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Create select boxes for group members
     * 
     * @param int $id of group master
     * @return string
     */
    private function memberDivBoxes($id)
    {
        $existingMembers = $this->creator->grabGroupMembers($id);
    	$potentialMembers = $this->creator->grabGroupAvailableMembers();
		$masters = $this->creator->grabGroupMasters();
// a potential group member cannot also be a master
		$potentialMembers = array_diff_key($potentialMembers, $masters);
    	unset($potentialMembers[$id]);
		if (!is_array($potentialMembers)) {
			$potentialMembers = [];
		}
		$pString = \HTML\tableStart();
		$pString .= \HTML\trStart();
		$pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
			\HTML\strong($this->messages->text("misc", "creatorGroupAvailable")),
			"creators",
			$potentialMembers,
			20
		) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint'));
// Transfer arrows
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'toMembers',
        ];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'fromMembers',
        ];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);
        $pString .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'padding3px left width5percent');
		if (!is_array($existingMembers)) {
			$pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
				\HTML\strong($this->messages->text("misc", "creatorGroupMember")),
				"creatorIds",
				[],
				20
			) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint'));
		} else {
			$pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
				\HTML\strong($this->messages->text("misc", "creatorGroupMember")),
				"creatorIds",
				$existingMembers,
				20
			) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint'));
		}
		$pString .= \HTML\trEnd();
		$pString .= \HTML\tableEnd();
		return $pString;
	}
    /**
     * start grouping process
     */
    public function groupProcess()
    {
        $this->validateInput('group');
// Are we removing all creatorIds thus removing the group?
		if (!array_key_exists("creatorIds", $this->vars) || empty($this->vars['creatorIds'])) {
			$this->db->formatConditions(['creatorSameAs' => $this->vars['creatorMaster']]);
            $this->db->updateNull('creator', 'creatorSameAs');
        	return $this->groupInit($this->success->text("creatorUngroup"));
		}
// Otherwise creating or editing a group
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
            if (!array_key_exists("creatorMaster", $this->vars)) {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'groupInit');
            }
            if (array_key_exists("creatorIds", $this->vars)) {
				if ((count($this->vars['creatorIds']) == 1) && $this->vars['creatorIds'][0] == $this->vars['creatorMaster']) {
					$this->badInput->close($this->errors->text("inputError", "missing"), $this, 'groupInit');
				}
				if ((count($this->vars['creatorIds']) == 1) && $this->vars['creatorIds'][0] == 0) {
					$this->badInput->close($this->errors->text("inputError", "missing"), $this, 'groupInit');
				}
			}
        }
    }
}
