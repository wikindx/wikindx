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
 * EDITCREATOR class
 */
class EDITCREATOR
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $creator;
    private $gatekeep;
    private $badInput;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->creator = FACTORY_CREATOR::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();

        GLOBALS::setTplVar('heading', $this->messages->text("heading", "edit", " (" . $this->messages->text("resources", "creator") . ")"));
    }
    /**
     * check we are allowed to edit and load appropriate method
     *
     * @param mixed $message
     */
    public function init($message = FALSE)
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        $formData = [];
        if (array_key_exists('message', $this->vars)) {
            $pString = $this->vars['message'];
        }
        elseif (is_array($message)) { // error has occurred so get get form_data to populate form with
            $error = array_shift($message);
            $pString = \HTML\p($error, "error", "center");
            $formData = array_shift($message);
        }
        else {
            $pString = $message;
        }
        $creators = $this->creator->grabAll();
        if (!is_array($creators)) { // i.e. FALSE
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noCreators'));

            return;
        }
        foreach ($creators as $id => $null) {
	        $initialCreatorId = $id;
			break;
		}
        $jsonArray = [];
        $jScript = 'index.php?action=edit_EDITCREATOR_CORE&method=displayName';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'creatorIds',
            'targetDiv' => 'creatorDiv',
        ];
		$js = \AJAX\jActionForm('onchange', $jsonArray);
        $pString .= \FORM\formHeader('edit_EDITCREATOR_CORE');
        $pString .= \FORM\hidden("method", "edit");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        if (array_key_exists('editCreatorId', $formData) && $formData['editCreatorId']) {
        	$pString .= \HTML\td(\FORM\selectedBoxValue(FALSE, "creatorIds", $creators, $formData['editCreatorId'], 20, FALSE, $js));
        }
        else {
	        $pString .= \HTML\td(\FORM\selectFBoxValue(FALSE, "creatorIds", $creators, 20, FALSE, $js));
        }
        $pString .= \HTML\td(\HTML\div('creatorDiv', $this->displayName(TRUE, $initialCreatorId, $formData)));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
        $pString .= \FORM\formEnd();
        \AJAX\loadJavascript();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display interface to edit chose name
     *
     * @param bool $initialDisplay
     * @param int $creatorId
     * @param array $formData
     */
    public function displayName($initialDisplay = FALSE, $creatorId = FALSE, $formData = [])
    {
        $initials = $prefix = $firstname = $surname = FALSE;
		if (array_key_exists('editCreatorId', $formData)) {
			$creatorId = $formData['editCreatorId'];
			$fields = $this->fillFields($formData['firstname'], $formData['initials'], $formData['prefix'], $formData['surname']);
		}
        else {
        	if (!$initialDisplay) {
        		$creatorId = $this->vars['ajaxReturn'];
        	}
            $this->db->formatConditions(['creatorId' => $creatorId]);
            $recordset = $this->db->select('creator', ["creatorSurname", "creatorFirstname", "creatorInitials", "creatorPrefix"]);
			$row = $this->db->fetchRow($recordset);
			if ($row['creatorInitials']) {
				$initials = str_replace(" ", ".", \HTML\dbToFormTidy($row['creatorInitials']) . ".");
			}
			if ($row['creatorFirstname']) {
				$firstname = \HTML\dbToFormTidy($row['creatorFirstname']);
			}
			if ($row['creatorPrefix']) {
				$prefix = \HTML\dbToFormTidy($row['creatorPrefix']);
			}
			$surname = \HTML\dbToFormTidy($row['creatorSurname']);
			$fields = $this->fillFields($firstname, $initials, $prefix, $surname);
        }
        $pString = \FORM\hidden("editCreatorId", $creatorId);
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= $fields;
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        if ($initialDisplay) {
            return $pString;
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $pString]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Fill in form fields
     *
     * @param string $firstname
     * @param string $initials
     * @param string $prefix
     * @param string $surname
     * 
     * @return string
     */
    private function fillFields($firstname, $initials, $prefix, $surname)
    {
    	$pString = \HTML\td(\FORM\textInput(
            $this->messages->text("resources", "firstname"),
            "firstname",
            $firstname,
            30,
            255
        ));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("resources", "initials"),
            "initials",
            $initials,
            6,
            255
        ) . BR .
            \HTML\span($this->messages->text("hint", "initials"), 'hint'));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("resources", "prefix"),
            "prefix",
            $prefix,
            11,
            10
        ));
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("resources", "surname"),
            "surname",
            $surname,
            30,
            255
        ));
        return $pString;
    }
    /**
     * write to the database
     */
    public function edit()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        $this->validateInput();
        $surname = trim($this->vars['surname']);
        $firstname = trim($this->vars['firstname']);
        $initials = $this->creator->formatInitials(trim($this->vars['initials']));
        $prefix = trim($this->vars['prefix']);
        $updateArray['creatorSurname'] = $sortName = $surname;
        if ($creatorExistId = $this->creator->checkExists($surname, $firstname, $initials, $prefix)) {
            if ($creatorExistId != $this->vars['editCreatorId']) {
                return $this->confirmDuplicate($creatorExistId, $sortName);
            }
        }
        if ($firstname) {
            $updateArray['creatorFirstname'] = $firstname;
        } else {
            $nulls[] = 'creatorFirstname';
        }
        if ($initials) {
            $updateArray['creatorInitials'] = $initials;
        } else {
            $nulls[] = 'creatorInitials';
        }
        if ($prefix) {
            $updateArray['creatorPrefix'] = $prefix;
        } else {
            $nulls[] = 'creatorPrefix';
        }
        $this->db->formatConditions(['creatorId' => $this->vars['editCreatorId']]);
        $this->db->update('creator', $updateArray);
        // set to NULL empty firstname, prefix and initials fields
        if (isset($nulls) && !empty($nulls)) {
            $this->db->formatConditions(['creatorId' => $this->vars['editCreatorId']]);
            $this->db->updateNull('creator', $nulls);
        }
        // Update any reference to this creator in resource_creator table
        $this->db->formatConditions(['resourcecreatorCreatorId' => $this->vars['editCreatorId']]);
        // remove all punctuation (keep apostrophe and dash for names such as Grimshaw-Aagaard and D'Eath)
        $this->db->update(
            'resource_creator',
            ['resourcecreatorCreatorSurname' => mb_strtolower(preg_replace('/[^\p{L}\p{N}\s\-\']/u', '', $sortName))]
        );
        // remove cache files for creators
        $this->db->deleteCache('cacheResourceCreators');
        $this->db->deleteCache('cacheMetadataCreators');
        // send back to main script with success message
        $message = rawurlencode($this->success->text("creator"));
        header("Location: index.php?action=edit_EDITCREATOR_CORE&method=init&message=$message");
    }
    /**
     * Validate the form input
     *
     */
    private function validateInput()
    {
// First check for input
		$error = '';
        if (!array_key_exists('editCreatorId', $this->vars) || !$this->vars['editCreatorId']) {
        	$error = $this->errors->text("inputError", "missing");
        }
        $surname = array_key_exists('surname', $this->vars) ? trim($this->vars['surname']) : FALSE;
        if (!$surname) {
            $error = $this->errors->text("inputError", "missing");
        }
// Second, write any input to form_data
// Possible form fields – ensure fields are available whether filled in or not (NB checkbox fields do NOT exist in $this->vars if not checked)
		$fields = ['surname' => $this->vars['surname'], 'editCreatorId' => $this->vars['editCreatorId'], 
			'firstname' => $this->vars['firstname'], 'initials' => $this->vars['initials'], 'prefix' => $this->vars['prefix']];
		if ($error) {
        	$this->badInput->close($error, $this, ['init', $fields]);
		}
    }
    /**
     * write to the database
     */
    public function editConfirm()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        if (!array_key_exists('editCreatorId', $this->vars) || !$this->vars['editCreatorId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        if (!array_key_exists('editCreatorExistId', $this->vars) || !$this->vars['editCreatorExistId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        if (!array_key_exists('editCreatorSurname', $this->vars) || !$this->vars['editCreatorSurname']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $editId = $this->vars['editCreatorId'];
        $existId = $this->vars['editCreatorExistId'];
        $existSurname = $this->vars['editCreatorSurname'];
        // Delete old creator
        $this->db->formatConditions(['creatorId' => $editId]);
        $this->db->delete('creator');
        // remove cache files for creators
        $this->db->deleteCache('cacheResourceCreators');
        $this->db->deleteCache('cacheMetadataCreators');
        // Select all resources referencing this old creator and replace reference with existing creator -- check if main or not.
        // First update where old/new creator is not main creator, then update where old/new creator is main creator (and add newSurname).
        $this->db->formatConditions(['resourcecreatorCreatorMain' => $editId], TRUE);
        $this->db->formatConditions(['resourcecreatorCreatorId' => $editId]);
        $this->db->update('resource_creator', ['resourcecreatorCreatorId' => $existId]);
        $this->db->formatConditions(['resourcecreatorCreatorMain' => $editId]);
        $this->db->formatConditions(['resourcecreatorCreatorId' => $editId]);
        $updateArray['resourcecreatorCreatorId'] = $existId;
        $updateArray['resourcecreatorCreatorMain'] = $existId;
        $this->db->update('resource_creator', $updateArray);
        // Update surname where resourcecreatorCreatorMain is $existId
        $this->db->formatConditions(['resourcecreatorCreatorMain' => $existId]);
        $this->db->update('resource_creator', ['resourcecreatorCreatorSurname' => $existSurname]);
        // send back to main script with success message
        $message = rawurlencode($this->success->text("creator"));
        header("Location: index.php?action=edit_EDITCREATOR_CORE&method=init&message=$message");
    }
    /**
     * The new name equals one already in the database. Confirm that this edited one is to be removed and
     * all references to it replaced by the existing one.
     *
     * @param mixed $creatorExistId
     * @param mixed $surname
     */
    private function confirmDuplicate($creatorExistId, $surname)
    {
        $pString = $this->errors->text("warning", "creatorExists");
        $pString .= \HTML\p($this->messages->text("creators", "creatorExists"));
        $pString .= \FORM\formHeader("edit_EDITCREATOR_CORE");
        $pString .= \FORM\hidden("editCreatorId", $this->vars['editCreatorId']);
        $pString .= \FORM\hidden("editCreatorExistId", $creatorExistId);
        $pString .= \FORM\hidden("editCreatorSurname", $surname);
        $pString .= \FORM\hidden("method", 'editConfirm');
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
}
