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
 * EDITKEYWORD class
 */
class EDITKEYWORD
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $keyword;
    private $gatekeep;
    private $badInput;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();

        $this->keyword = FACTORY_KEYWORD::getInstance();

        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();

        GLOBALS::setTplVar('heading', $this->messages->text("heading", "edit", " (" .
            $this->messages->text("resources", "keyword") . ")"));
    }
    /**
     * check we are allowed to edit and load appropriate method
     *
     * @param false|mixed $message
     */
    public function init($message = FALSE)
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        $error = '';
        $formData = [];
        if (is_array($message)) { // error has occurred so get get form_data to populate form with
        	$error = array_shift($message);
        	$pString = \HTML\p($error, "error", "center");
        	$uuid = array_shift($message);
        	$formData = \FORMDATA\getData($this->db, $uuid);
        }
        else {
        	$pString = $message;
        }
        $keywords = $this->keyword->grabAll();
        if (!$keywords) {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noKeywords'));

            return;
        }
        foreach ($keywords AS $id => $value) {
	        $initialKeywordId = $id;
	        break;
	    }
        $jsonArray = [];
        $jScript = 'index.php?action=edit_EDITKEYWORD_CORE&method=displayKeyword';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'keywordIds',
            'targetDiv' => 'keywordDiv',
        ];
        $jScript = 'index.php?action=edit_EDITKEYWORD_CORE&method=displayGlossary';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'keywordIds',
            'targetDiv' => 'glossaryDiv',
        ];
		$js = \AJAX\jActionForm('onchange', $jsonArray);
        $pString .= \FORM\formHeader('edit_EDITKEYWORD_CORE');
        $pString .= \FORM\hidden("method", "edit");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        if (array_key_exists('keywordIds', $formData) && $formData['keywordIds']) {
        	$pString .= \HTML\td(\FORM\selectedBoxValue(FALSE, "keywordIds", $keywords, $formData['keywordIds'], 20, FALSE, $js));
        }
        else {
	        $pString .= \HTML\td(\FORM\selectFBoxValue(FALSE, "keywordIds", $keywords, 20, FALSE, $js));
        }
        $td = \HTML\tableStart();
        $td .= \HTML\trStart();
        $td .= \HTML\td(\HTML\div('keywordDiv', $this->displayKeyword(TRUE, $initialKeywordId, $formData)));
        $td .= \HTML\trEnd();
        // Div and TD for glossary preceded by blank space
        $td .= \HTML\trStart();
        $td .= \HTML\td('&nbsp;');
        $td .= \HTML\trEnd();
        $td .= \HTML\trStart();
        $td .= \HTML\td($this->messages->text('resources', 'glossary') . BR . \HTML\div('glossaryDiv',
        	 $this->displayGlossary(TRUE, $initialKeywordId, $formData)));
        $td .= \HTML\trEnd();
        $td .= \HTML\tableEnd();
        $pString .= \HTML\td($td);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
        $pString .= \FORM\formEnd();
        \AJAX\loadJavascript();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display interface to edit keyword
     *
     * @param bool $initialDisplay
     * @param int $keywordId
     * @param array $formData
     */
    public function displayKeyword($initialDisplay = FALSE, $keywordId = FALSE, $formData = [])
    {
        $keyword = FALSE;
        if ($initialDisplay) {
        	if (array_key_exists('keywordIds', $formData)) {
        		$keywordId = $formData['keywordIds'];
        	}
            $this->db->formatConditions(['keywordId' => $keywordId]);
        }
        else {
            $this->db->formatConditions(['keywordId' => $this->vars['ajaxReturn']]);
            $keywordId = $this->vars['ajaxReturn'];
        }
		$recordset = $this->db->select('keyword', 'keywordKeyword');
		$row = $this->db->fetchRow($recordset);
		$keyword = \HTML\dbToFormTidy($row['keywordKeyword']);
        $pString = \FORM\hidden("editKeywordId", $keywordId);
        $pString .= \FORM\textInput(
            $this->messages->text('resources', 'keyword') . ' ' . \HTML\span('*', 'required'),
            'keyword',
            $keyword,
            30,
            255
        );
        if ($initialDisplay) {
            return $pString;
        }
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$pString"]));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Display the glossary textarea
     *
     * @param bool $initialDisplay
     * @param int $keywordId
     * @param array $formData
     */
    public function displayGlossary($initialDisplay = FALSE, $keywordId = FALSE, $formData = [])
    {
        if ($initialDisplay) {
        	if (array_key_exists('text', $formData)) {
	            return \FORM\textareaInput(FALSE, "text", $formData['text'], 50, 10);
        	}
        	else {
        		$this->db->formatConditions(['keywordId' => $keywordId]);
	        }
        }
        else {
        	$this->db->formatConditions(['keywordId' => $this->vars['ajaxReturn']]);
        }
        $recordset = $this->db->select('keyword', 'keywordGlossary');
        $row = $this->db->fetchRow($recordset);
        $glossary = \HTML\dbToFormTidy($row['keywordGlossary']);
        $pString = \FORM\textareaInput(FALSE, "text", $glossary, 50, 10);
        if ($initialDisplay) {
        	return $pString;
        }
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $pString]));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * write to the database
     */
    public function edit()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        $uuid = $this->validateInput();
        $keyword = trim($this->vars['keyword']);
        if ($keywordExistId = $this->keyword->checkExists($keyword)) {
            if ($keywordExistId != $this->vars['editKeywordId']) {
                return $this->confirmDuplicate($keywordExistId);
            }
        }
        $updateArray['keywordKeyword'] = $keyword;
        $glossary = trim($this->vars['text']);
        if ($glossary) {
            $updateArray['keywordGlossary'] = $glossary;
        } else {
            $this->db->formatConditions(['keywordId' => $this->vars['editKeywordId']]);
            $this->db->updateNull('keyword', 'keywordGlossary');
        }
        $this->db->formatConditions(['keywordId' => $this->vars['editKeywordId']]);
        $this->db->update('keyword', $updateArray);
        // remove cache files for keywords
        $this->db->deleteCache('cacheKeywords');
        $this->db->deleteCache('cacheResourceKeywords');
        $this->db->deleteCache('cacheMetadataKeywords');
        $this->db->deleteCache('cacheQuoteKeywords');
        $this->db->deleteCache('cacheParaphraseKeywords');
        $this->db->deleteCache('cacheMusingKeywords');
        $this->keyword->removeHanging();
        $this->keyword->checkKeywordGroups();
        // send back to editDisplay with success message
        $this->init($this->success->text("keyword"));
    }
    /**
     * write to the database
     */
    public function editConfirm()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        if (!array_key_exists('editKeywordId', $this->vars) || !$this->vars['editKeywordId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        if (!array_key_exists('editKeywordExistId', $this->vars) || !$this->vars['editKeywordExistId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $editId = $this->vars['editKeywordId'];
        $existId = $this->vars['editKeywordExistId'];
        // Delete old keyword
        $this->db->formatConditions(['keywordId' => $editId]);
        $this->db->delete('keyword');
        // Update references to keyword
        $this->db->formatConditions(['resourcekeywordKeywordId' => $editId]);
        $this->db->update('resource_keyword', ['resourcekeywordKeywordId' => $existId]);
        // remove cache files for keywords
        $this->db->deleteCache('cacheKeywords');
        $this->db->deleteCache('cacheResourceKeywords');
        $this->db->deleteCache('cacheMetadataKeywords');
        $this->db->deleteCache('cacheQuoteKeywords');
        $this->db->deleteCache('cacheParaphraseKeywords');
        $this->db->deleteCache('cacheMusingKeywords');
        $this->keyword->removeHanging();
        $this->keyword->checkKeywordGroups();
        // send back to editDisplay with success message
        $this->init($this->success->text("keyword"));
    }
    /**
     * The new keyword equals one already in the database. Confirm that this edited one is to be removed and
     * all references to it replaced by the existing one.
     *
     * @param mixed $keywordExistId
     */
    private function confirmDuplicate($keywordExistId)
    {
        $pString = $this->errors->text("warning", "keywordExists");
        $pString .= \HTML\p($this->messages->text("misc", "keywordExists"));
        $pString .= \FORM\formHeader("edit_EDITKEYWORD_CORE");
        $pString .= \FORM\hidden("editKeywordId", $this->vars['editKeywordId']);
        $pString .= \FORM\hidden("text", $this->vars['text']);
        $pString .= \FORM\hidden("editKeywordExistId", $keywordExistId);
        $pString .= \FORM\hidden("method", 'editConfirm');
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")), FALSE, "right");
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Validate the form input and write to form_data
     *
     * @return string $uuid
     */
    private function validateInput()
    {
// First check for input
		$error = '';
        if (!array_key_exists('editKeywordId', $this->vars) || !$this->vars['editKeywordId']) {die;
            $error = $this->errors->text("inputError", "missing");
        }
        if (!array_key_exists('keywordIds', $this->vars) || !$this->vars['keywordIds']) {
            $error = $this->errors->text("inputError", "missing");
        }
        $keyword = array_key_exists('keyword', $this->vars) ? trim($this->vars['keyword']) : FALSE;
        if (!$keyword) {
            $error = $this->errors->text("inputError", "missing");
        }
// Second, write any input to form_data
// Possible form fields – ensure fields are available whether filled in or not (NB checkbox fields do NOT exist in $this->vars if not checked)
		$fields = ['keyword' => $this->vars['keyword'], 'keywordIds' => $this->vars['keywordIds'], 'text' => $this->vars['text']];
		$uuid = FALSE;
		if ($error) { // Only store form data if there is an error
			$uuid = \FORMDATA\putData($this->db, $fields);
			if (!$uuid) {
				$error = $this->errors->text("dbError", "formData");
			}
		}
		if ($error) {
            $this->badInput->close($error, $this, ['init', $uuid]);
        }
        return $uuid;
    }
}
