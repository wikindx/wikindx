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

        $this->keyword = FACTORY_KEYWORD::getInstance();

        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();

        GLOBALS::setTplVar('heading', $this->messages->text("heading", "edit", " (" .
            $this->messages->text("resources", "keyword") . ")"));
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
        $initialKeywordId = FALSE;
        if (array_key_exists('message', $this->vars)) {
            $pString = $this->vars['message'];
			if (array_key_exists('id', $this->vars)) {
				$initialKeywordId = $this->vars['id'];
			}
        }
        elseif (is_array($message)) { // error has occurred so get get form_data to populate form with
            $error = array_shift($message);
            $pString = \HTML\p($error, "error", "center");
            $formData = array_shift($message);
        }
        else {
            $pString = $message;
        }
        $keywords = $this->keyword->grabAll();
        if (!$keywords) {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noKeywords'));

            return;
        }
        if (!$initialKeywordId) {
			foreach ($keywords AS $id => $value) {
				$initialKeywordId = $id;
				break;
			}
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
	        $pString .= \HTML\td(\FORM\selectedBoxValue(FALSE, "keywordIds", $keywords, $initialKeywordId, 20, FALSE, $js));
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
		$keyword = $row['keywordKeyword'];
        $pString = \FORM\hidden("editKeywordId", $keywordId);
        $pString .= \FORM\textInput(
            \HTML\span('*', 'required') . $this->messages->text('resources', 'keyword'),
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
        $glossary = $row['keywordGlossary'];
        $pString = \FORM\textareaInput(FALSE, "text", $glossary, 50, 10) . 
        	BR . 
        	\HTML\span(\HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "glossary")), 'hint');
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
        $this->validateInput();
        $keyword = \UTF8\mb_trim($this->vars['keyword']);
        if ($existId = $this->keyword->checkExists($keyword)) {
            if ($existId != $this->vars['editKeywordId']) {
            	$editId = $this->vars['editKeywordId'];
            	$text = \UTF8\mb_trim($this->vars['text']) ? rawurlencode(\UTF8\mb_trim($this->vars['text'])) : FALSE;
				header("Location: index.php?action=edit_EDITKEYWORD_CORE&method=confirmDuplicate&editId=$editId&existId=$existId&text=$text");
				die;
            }
        }
        // At this point, we're cleared to write
        $this->editWrite();
        $this->tidy();
        // send back to main script with success message
        $message = rawurlencode($this->success->text("keyword"));
        header("Location: index.php?action=edit_EDITKEYWORD_CORE&method=init&message=$message&id=" . $this->vars['editKeywordId']);
        die;
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
        // At this point, we're cleared to write
        $this->editConfirmWrite();
        $this->tidy();
        // send back to form with success message
        $message = rawurlencode($this->success->text("keyword"));
        header("Location: index.php?action=edit_EDITKEYWORD_CORE&method=init&message=$message");
        die;
    }
    /**
     * The new keyword equals one already in the database. Confirm that this edited one is to be removed and
     * all references to it replaced by the existing one.
     *
     */
    public function confirmDuplicate()
    {
        $pString = $this->errors->text("warning", "keywordExists");
        $pString .= \HTML\p($this->messages->text("misc", "keywordExists"));
        $pString .= \FORM\formHeader("edit_EDITKEYWORD_CORE");
        $pString .= \FORM\hidden("editKeywordId", $this->vars['editId']);
        $pString .= \FORM\hidden("text", $this->vars['text']);
        $pString .= \FORM\hidden("editKeywordExistId", $this->vars['existId']);
        $pString .= \FORM\hidden("method", 'editConfirm');
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")), FALSE, "right");
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * write to the database
     */
    private function editWrite()
    {
        $updateArray['keywordKeyword'] = \UTF8\mb_trim($this->vars['keyword']);
        $glossary = \UTF8\mb_trim($this->vars['text']);
        if ($glossary) {
            $updateArray['keywordGlossary'] = $glossary;
        } else {
            $this->db->formatConditions(['keywordId' => $this->vars['editKeywordId']]);
            $this->db->updateNull('keyword', 'keywordGlossary');
        }
        $this->db->formatConditions(['keywordId' => $this->vars['editKeywordId']]);
        $this->db->update('keyword', $updateArray);
    }
    /**
     * write to the database
     */
    private function editConfirmWrite()
    {
        $editId = $this->vars['editKeywordId'];
        $existId = $this->vars['editKeywordExistId'];
        // Delete old keyword
        $this->db->formatConditions(['keywordId' => $editId]);
        $this->db->delete('keyword');
        // Update references to keyword
        $this->db->formatConditions(['resourcekeywordKeywordId' => $editId]);
        $this->db->update('resource_keyword', ['resourcekeywordKeywordId' => $existId]);
    }
    /**
     * Validate the form input
     *
     */
    private function validateInput()
    {
// First check for input
		$error = '';
        if (!array_key_exists('editKeywordId', $this->vars) || !$this->vars['editKeywordId']) {
            $error = $this->errors->text("inputError", "missing");
        }
        if (!array_key_exists('keywordIds', $this->vars) || !$this->vars['keywordIds']) {
            $error = $this->errors->text("inputError", "missing");
        }
        $keyword = array_key_exists('keyword', $this->vars) ? \UTF8\mb_trim($this->vars['keyword']) : FALSE;
        if (!$keyword) {
            $error = $this->errors->text("inputError", "missing");
        }
// Possible form fields – ensure fields are available whether filled in or not (NB checkbox fields do NOT exist in $this->vars if not checked)
		$fields = ['keyword' => $this->vars['keyword'], 'keywordIds' => $this->vars['keywordIds'], 'text' => $this->vars['text']];
		if ($error) {
        	$this->badInput->close($error, $this, ['init', $fields]);
		}
    }
    /**
     * Tidy up
     */
    private function tidy()
    {
        // remove cache files for keywords
        $this->db->deleteCache('cacheKeywords');
        $this->db->deleteCache('cacheResourceKeywords');
        $this->db->deleteCache('cacheMetadataKeywords');
        $this->db->deleteCache('cacheQuoteKeywords');
        $this->db->deleteCache('cacheParaphraseKeywords');
        $this->db->deleteCache('cacheMusingKeywords');
        $this->keyword->removeHanging();
        $this->keyword->checkKeywordGroups();
    }
}
