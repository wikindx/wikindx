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
private $session;
private $gatekeep;
private $badInput;
private $languages;

	public function __construct()
	{
		$this->db = FACTORY_DB::getInstance();
		$this->vars = GLOBALS::getVars();
		$this->errors = FACTORY_ERRORS::getInstance();
		$this->messages = FACTORY_MESSAGES::getInstance();
		$this->success = FACTORY_SUCCESS::getInstance();
		$this->session = FACTORY_SESSION::getInstance();


		$this->gatekeep = FACTORY_GATEKEEP::getInstance();
		$this->badInput = FACTORY_BADINPUT::getInstance();

		$this->gatekeep->requireSuper = TRUE;
		$this->gatekeep->init();
		$this->session->clearArray('edit');
		$this->languages = $this->grabAll();
	}
// display options
	public function init($message = FALSE)
	{
		GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminLanguage"));
		$pString = $message ? \HTML\p($message, "error", "center") : FALSE;
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
		if(!empty($this->languages))
		{
// Edit
// If preferences reduce long language titles, we want to transfer the original rather than the condensed version.
// Store the base64-encoded value for retrieval in the javascript.
			foreach($this->languages as $key => $value)
			{
				$key = $key . '_' . base64_encode($value);
				$languages[$key] = $value;
			}
			$td = \FORM\formHeader("admin_ADMINLANGUAGES_CORE");
			$td .= \FORM\hidden("method", "editLanguage");
			$td .= \FORM\selectFBoxValue($this->messages->text("misc", "languageEdit"),
				'languageId', $languages, 10);
			$td .= \HTML\p($this->transferArrow('transferLanguage'));
			$td .= \HTML\p(\FORM\textInput(FALSE, "languageEdit", FALSE, 30, 255));
			$td .= \FORM\hidden('languageEditId', FALSE);
			$td .= \HTML\p(\FORM\formSubmit('Edit'));
			$td .= \FORM\formEnd();
			$pString .= \HTML\td($td);
// Delete
			$td = \FORM\formHeader("admin_ADMINLANGUAGES_CORE");
			$td .= \FORM\hidden("method", "deleteConfirm");
			$td .= \FORM\selectFBoxValueMultiple($this->messages->text("misc", "languageDelete"),
				'languageIds', $this->languages, 10) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint');
			$td .= \HTML\p(\FORM\formSubmit('Delete'));
			$td .= \FORM\formEnd();
			$pString .= \HTML\td($td);
		}
		$pString .= \HTML\trEnd();
		$pString .= \HTML\tableEnd();
		\AJAX\loadJavascript(array(WIKINDX_URL_BASE . '/' . 'core/modules/admin/languageEdit.js'));
		GLOBALS::addTplVar('content', $pString);
	}
	private function transferArrow($function)
	{
		$jsonArray = array();
		$jsonArray[] = array(
			'startFunction' => $function,
			);
		$image = \AJAX\jActionIcon('toBottom', 'onclick', $jsonArray);
		return $image;
	}
// Add a language
	public function addLanguage()
	{
		if(!$input = $this->validateInput('add'))
			$this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
		$pString = $this->success->text("languageAdd");
// If language already exists quietly return without error.
		foreach($this->languages as $language)
		{
			if(mb_strtolower($input) == mb_strtolower($language))
				return $this->addInit($pString);
		}
		$this->db->insert('language', array('languageLanguage'), array($input));
		$this->languages = $this->grabAll();
		return $this->init($pString);
	}
// Delete languages display.
	public function deleteInit($message = FALSE)
	{
		GLOBALS::setTplVar('heading', $this->messages->text("heading", "delete2", " (" .
			$this->messages->text("resources", "languages") . ")"));
		$pString = '';
		if($message)
			$pString .= \HTML\p($message, "error", "center");
		if(empty($this->languages))
		{
			GLOBALS::addTplVar('content', $pString . $this->messages->text("misc", "noLanguages"));
			return;
		}
		$pString .= \FORM\formHeader("admin_ADMINLANGUAGES_CORE");
		$pString .= \FORM\hidden("method", "deleteConfirm");
		foreach($this->languages as $key => $value)
			$pString .= \FORM\checkbox(FALSE, "languageDelete_" . $key) . " $value" . BR;
		$pString .= BR . \FORM\formSubmit('Delete');
		$pString .= \FORM\formEnd();
		GLOBALS::addTplVar('content', $pString);
	}
// Ask for confirmation of delete languages
	public function deleteConfirm()
	{
		if(!$this->validateInput('delete'))
			$this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
		GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminLanguage"));
		$pString = \FORM\formHeader("admin_ADMINLANGUAGES_CORE");
		$pString .= \FORM\hidden("method", "deleteLanguage");
		foreach($this->vars['languageIds'] as $id)
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
		$input = $this->validateInput('deleteConfirm');
		if(empty($input))
			$this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
		foreach($input as $id)
		{
			if(array_key_exists($id, $this->languages))
			{
				$this->db->formatConditions(array('resourcelanguageLanguageId' => $id));
				$this->db->delete('resource_language');
				$this->db->formatConditions(array('languageId' => $id));
				$this->db->delete('language');
			}
		}
		$pString = $this->success->text("languageDelete");
		$this->languages = $this->grabAll();
		return $this->init($pString);
	}
// Edit languages
	public function editLanguage()
	{
		if(!$this->validateInput('edit'))
			$this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
		$this->db->formatConditions(array('languageId' => $this->vars['languageEditId']));
		$this->db->update('language', array('languageLanguage' => trim($this->vars['languageEdit'])));
		$this->languages = $this->grabAll();
		$pString = $this->success->text("languageEdit");
		return $this->init($pString);
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
		if($type == 'add')
		{
			if(!$input = trim($this->vars['languageAdd']))
				return FALSE;
		}
		else if($type == 'delete')
		{
			return (!empty($this->vars['languageIds']) && array_key_exists('languageIds', $this->vars));
		}
		else if($type == 'deleteConfirm')
		{
			$input = array();
			foreach($this->vars as $key => $value)
			{
				if(!preg_match("/delete_(.*)/u", $key, $match))
					continue;
				$input[] = $match[1];
			}
		}
		else if($type == 'edit')
		{
			return (trim($this->vars['languageEdit']) && array_key_exists('languageEdit', $this->vars) && array_key_exists('languageEditId', $this->vars));
		}
		return $input;
	}
}
