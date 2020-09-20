<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @copyright 2017 Mark Grimshaw <sirfragalot@users.sourceforge.net>
 * @license https://creativecommons.org/licenses/by-nc-sa/2.0/legalcode CC-BY-NC-SA 2.0
 */

/*****
*	ADMINLANGUAGES class.
*
*	ADMINLANGUAGES of resource languages
*****/
class ADMINLANGUAGES
{
private $db;
private $vars;
private $errors;
private $messages;
private $success;
private $gatekeep;
private $badInput;
private $languages;
private $formData = [];

	public function __construct()
	{
		$this->db = FACTORY_DB::getInstance();
		$this->vars = GLOBALS::getVars();
		$this->errors = FACTORY_ERRORS::getInstance();
		$this->messages = FACTORY_MESSAGES::getInstance();
		$this->success = FACTORY_SUCCESS::getInstance();
		$this->gatekeep = FACTORY_GATEKEEP::getInstance();
		$this->badInput = FACTORY_BADINPUT::getInstance();
		$this->gatekeep->requireSuper = TRUE;
		$this->gatekeep->init();
	}
// display options
	public function init($message = FALSE)
	{
		$languages = $this->grabAll();
		GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminLanguage"));
    	if (array_key_exists('message', $this->vars)) {
    		$message = $this->vars['message'];
    	}
		$pString = $message;
		$pString .= \HTML\p($this->messages->text("misc", "language"));
		$pString .= \HTML\tableStart('generalTable borderStyleSolid left');
		$pString .= \HTML\trStart();
// Add
		$td = \FORM\formHeader("admin_ADMINLANGUAGES_CORE");
		$td .= \FORM\hidden("method", "addLanguage");
		$td .= \FORM\textInput($this->messages->text("misc", "languageAdd"), "languageAdd", FALSE, 30, 255);
		$td .= \HTML\p(\FORM\formSubmit('Add'));
		$td .= \FORM\formEnd();
		$pString .= \HTML\td($td);
		if(!empty($languages))
		{
// Edit
// If preferences reduce long language titles, we want to transfer the original rather than the condensed version.
// Store the base64-encoded value for retrieval in the javascript.
			foreach($languages as $key => $value)
			{
				$key = $key . '_' . base64_encode($value);
				$fields[$key] = $value;
			}
			if (array_key_exists('id', $this->formData)) { // thus missing name so get the original
				$name = base64_encode($languages[$this->formData['id']]);
				$id = $this->formData['id'] . '_' . $name;
				$hiddenId = $this->formData['id'];
				$initialLanguage = $languages[$this->formData['id']];
			}
			else {
	            foreach ($languages as $id => $initialLanguage) {
    	        	break;
        	    }
        	    $hiddenId = $id;
        	}
            $jsonArray = [];
			$jsonArray[] = [
				'startFunction' => 'transferLanguage',
			];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
			$td = \FORM\formHeader("admin_ADMINLANGUAGES_CORE");
			$td .= \FORM\hidden("method", "editLanguage");
			$td .= \FORM\selectedBoxValue($this->messages->text("misc", "languageEdit"),
				'languageId', $fields, $id, 10, FALSE, $js);
			$td .= \HTML\p(\FORM\textInput(FALSE, "languageEdit", $initialLanguage, 30, 255));
			$td .= \FORM\hidden('languageEditId', $hiddenId);
			$td .= \HTML\p(\FORM\formSubmit('Edit'));
			$td .= \FORM\formEnd();
			$pString .= \HTML\td($td);
// Delete
			$td = \FORM\formHeader("admin_ADMINLANGUAGES_CORE");
			$td .= \FORM\hidden("method", "deleteConfirm");
			$td .= \FORM\selectFBoxValueMultiple($this->messages->text("misc", "languageDelete"),
				'languageIds', $languages, 10) . BR . \HTML\span(\HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", 
            	$this->messages->text("hint", "multiples")), 'hint');
			$td .= \HTML\p(\FORM\formSubmit('Delete'));
			$td .= \FORM\formEnd();
			$pString .= \HTML\td($td);
		}
		$pString .= \HTML\trEnd();
		$pString .= \HTML\tableEnd();
		\AJAX\loadJavascript(array(WIKINDX_URL_BASE . '/' . 'core/modules/admin/languageEdit.js'));
		GLOBALS::addTplVar('content', $pString);
	}
// Add a language
	public function addLanguage()
	{
		$this->validateInput('add');
// If language already exists quietly return without error.
		$languages = $this->grabAll();
		foreach($languages as $language)
		{
			if(mb_strtolower($input) == mb_strtolower($language))
				return $this->init($pString);
		}
		$this->db->insert('language', ['languageLanguage'], [$this->formData['languageAdd']]);
        $message = rawurlencode($this->success->text("languageAdd"));
        header("Location: index.php?action=admin_ADMINLANGUAGES_CORE&method=init&message=$message");
        die;
	}
// Ask for confirmation of delete languages
	public function deleteConfirm()
	{
		$this->validateInput('delete');
		GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminLanguage"));
		$pString = \FORM\formHeader("admin_ADMINLANGUAGES_CORE");
		$pString .= \FORM\hidden("method", "deleteLanguage");
		foreach($this->formData['ids'] as $id)
		{
			$pString .= \FORM\hidden("delete_" . $id, $id);
			$ids[] = $this->db->tidyInput($id);
		}
		$this->db->formatConditions($this->db->formatFields('languageId') . $this->db->equal .
			join($this->db->or . $this->db->formatFields('languageId') . $this->db->equal, $ids));
		$recordset = $this->db->select('language', 'languageLanguage');
		while($row = $this->db->fetchRow($recordset))
			$fieldValues[] = "'" . $row['languageLanguage'] . "'";
		$pString .= \HTML\p($this->messages->text("misc", "confirmDeleteLanguage") . ": " . join(", ", $fieldValues));
		$pString .= \HTML\p(\FORM\formSubmit('Confirm'));
		$pString .= \FORM\formEnd();
		GLOBALS::addTplVar('content', $pString);
	}
// Delete languages
	public function deleteLanguage()
	{
		$this->validateInput('deleteConfirm');
        $this->db->formatConditionsOneField($this->formData['ids'], 'languageId');
        $this->db->delete('language');
        $this->db->formatConditionsOneField($this->formData['ids'], 'resourcelanguageLanguageId');
        $this->db->delete('resource_language');
        $message = rawurlencode($this->success->text("languageDelete"));
        header("Location: index.php?action=admin_ADMINLANGUAGES_CORE&method=init&message=$message");
        die;
	}
// Edit languages
	public function editLanguage()
	{
		$this->validateInput('edit');
		$this->db->formatConditions(array('languageId' => $this->vars['languageEditId']));
		$this->db->update('language', array('languageLanguage' => trim($this->vars['languageEdit'])));
        $message = rawurlencode($this->success->text("languageEdit"));
        header("Location: index.php?action=admin_ADMINLANGUAGES_CORE&method=init&message=$message");
        die;
	}
// Grab any languages from config table
	private function grabAll()
	{
		$array = array();
		$this->db->orderBy('languageLanguage');
		$resultset = $this->db->select('language', array('languageId', 'languageLanguage'));
		while($row = $this->db->fetchRow($resultset))
			$array[$row['languageId']] = $row['languageLanguage'];
		return $array;
	}
// validate input
	private function validateInput($type)
	{
		$error = '';
		if ($type == 'add')
		{
			if (!trim($this->vars['languageAdd'])) {
        		$error = $this->errors->text("inputError", "missing");
        	}
        	else {
            	$this->db->formatConditions($this->db->lower('languageLanguage') . 
            		$this->db->like(FALSE, mb_strtolower(trim($this->vars['languageAdd'])), FALSE));
				$recordset = $this->db->select('language', ['languageLanguage']);
				if ($this->db->numRows($recordset)) {
					$error = $this->errors->text("inputError", "languageExists");
				}
			}
            $this->formData['languageAdd'] = trim($this->vars['languageAdd']);
		}
		elseif ($type == 'delete')
		{
			if (!array_key_exists('languageIds', $this->vars) || empty($this->vars['languageIds'])) {
        		$error = $this->errors->text("inputError", "missing");
        	} else {
	            $this->formData['ids'] = $this->vars['languageIds'];
	        }
		}
		elseif ($type == 'deleteConfirm')
		{
			foreach($this->vars as $key => $value) {
				if(!preg_match("/delete_(.*)/u", $key))
				{
					continue;
				}
				$this->formData['ids'][] = $value;
			}
            if (empty($this->formData['ids'])) {
                $error = $this->errors->text("inputError", "missing");
            }
		}
		elseif ($type == 'edit')
		{
        	if (!array_key_exists('languageEdit', $this->vars) || !trim($this->vars['languageEdit'])) {
        		$error = $this->errors->text("inputError", "missing");
        	}
        	else {
            	$this->db->formatConditions($this->db->lower('languageLanguage') . 
            		$this->db->like(FALSE, mb_strtolower(trim($this->vars['languageEdit'])), FALSE));
				$recordset = $this->db->select('language', ['languageId', 'languageLanguage']);
				if ($this->db->numRows($recordset)) {
					$row = $this->db->fetchRow($recordset);
					if ($row['languageId'] != $this->vars['languageEditId']) {
						$error = $this->errors->text("inputError", "languageExists");
					}
				}
			}
			$this->formData['language'] = trim($this->vars['languageEdit']);
			$this->formData['id'] = $this->vars['languageEditId'];
		}
        if ($error) {
        	$this->badInput->close($error, $this, 'init');
        }
	}
}
