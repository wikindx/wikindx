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
 * ADMINKEYWORD class
 */
class ADMINKEYWORD
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
        $this->gatekeep->init();
        $this->session->clearArray('edit');
    }
    /**
     * editInit
     *
     * @param false|string $message
     */
    public function editInit($message = FALSE)
    {
        // Edit operations use functions from core/modules/edit/EDITKEYWORD.php
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "edit", " (" . $this->messages->text("resources", "keyword") . ")"));
        include_once('core/modules/edit/EDITKEYWORD.php');
        $keyword = new EDITKEYWORD();
        $keyword->init();
    }
    /**
     * mergeInit
     *
     * @param false|string $message
     */
    public function mergeInit($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminKeywords"));
        $keywords = $this->keyword->grabAll();
        $pString = \HTML\p($this->messages->text("misc", "keywordMerge"));
        if ($message) {
            $pString .= \HTML\p($message);
        }        
        if (is_array($keywords) && !empty($keywords)) {
            $pString .= \FORM\formHeader('admin_ADMINKEYWORD_CORE');
            $pString .= \FORM\hidden("method", "merge");
            $pString .= \HTML\tableStart('left');
            $pString .= \HTML\trStart();
            $td = \FORM\selectFBoxValueMultiple(FALSE, "keywordIds", $keywords, 20) .
                BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint');
            $pString .= \HTML\td($td);
            $td = \FORM\textInput(
                $this->messages->text("misc", "keywordMergeTarget"),
                "keywordText",
                FALSE,
                50
            );
            $td .= BR;
            $td .= BR;
            $td .= \FORM\formSubmit($this->messages->text("submit", "Proceed"));
            $pString .= \HTML\td($td);
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \FORM\formEnd();
        } else {
            $pString .= \HTML\p($this->messages->text("misc", "noKeywords"));
        }
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * start merging process
     */
    public function merge()
    {
        if (!array_key_exists("keywordIds", $this->vars)) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'mergeInit');
        }
        if (!array_key_exists("keywordText", $this->vars) || !trim($this->vars['keywordText'])) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'mergeInit');
        }
        if (array_key_exists("glossaries", $this->vars)) {
            $keywordIds = unserialize(base64_decode($this->vars['keywordIds']));
        } else {
            $keywordIds = $this->vars['keywordIds'];
        }
        $newKeyword = trim($this->vars['keywordText']);
        $newKeywordId = $this->insertKeyword($newKeyword);
// Convert keyword IDs in keyword groups
        $this->db->formatConditionsOneField($keywordIds, 'userkgkeywordsKeywordId');
        $this->db->update('user_kg_keywords', ['userkgkeywordsKeywordId' => $newKeywordId]);
// If a keyword group now has multiple entries in user_kg_keywords with the same keyword ID, remove excess rows
		$kgs = [];
		$this->db->formatConditions(['userkgkeywordsKeywordId' => $newKeywordId]);
		$resultset = $this->db->select('user_kg_keywords', ['userkgkeywordsId', 'userkgkeywordsKeywordGroupId']);
		while ($row = $this->db->fetchRow($resultset)) {
			if (!in_array($row['userkgkeywordsKeywordGroupId'], $kgs)) {
				$kgs[] = $row['userkgkeywordsKeywordGroupId'];
			}
			else {
				$this->db->formatConditions(['userkgkeywordsId' => $row['userkgkeywordsId']]);
				$this->db->delete('user_kg_keywords');
			}
		}
        if (($index = array_search($newKeywordId, $keywordIds)) !== FALSE) {
            unset($keywordIds[$index]);
        }
        if (empty($keywordIds)) { // basically, we're renaming the keyword and that's all
            $this->db->formatConditions(['keywordId' => $newKeywordId]);
            $this->db->update('keyword', ['keywordKeyword' => $newKeyword]);
        } else {
            // Check for glossary entries
            if (!array_key_exists("glossaries", $this->vars)) {
                $this->db->formatConditionsOneField($keywordIds, 'keywordId');
                $resultset = $this->db->select('keyword', ['keywordId', 'keywordKeyword', 'keywordGlossary']);
                $glossaryString = '';
                while ($row = $this->db->fetchRow($resultset)) {
                if ($row['keywordGlossary']) {
	                    $glossaryString .= \HTML\p(\HTML\strong($row['keywordKeyword']) . ":&nbsp;&nbsp;" . $row['keywordGlossary']);
	                }
                }
                if ($glossaryString) {
                    $pString = \HTML\p($this->messages->text("resources", "glossaryMerge"));
                    $pString .= \FORM\formHeader('admin_ADMINKEYWORD_CORE');
                    $pString .= \FORM\hidden("method", "merge");
                    $pString .= \FORM\hidden("glossaries", TRUE);
                    $pString .= \FORM\hidden("keywordIds", base64_encode(serialize($keywordIds)));
                    $pString .= \FORM\hidden("keywordText", $newKeyword);
                    $pString .= \HTML\p($this->messages->text('resources', 'glossary') . BR .
                        \FORM\textareaInput(FALSE, "glossary", FALSE, 50, 10));
                    $pString .= \FORM\formSubmit($this->messages->text("submit", "Proceed"));
                    $pString .= \FORM\formEnd();
                    $pString .= $glossaryString;
                    GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminKeywords"));
                    GLOBALS::addTplVar('content', $pString);

                    return; // break out here
                }
            }
            // Remove old keywords
            $this->db->formatConditionsOneField($keywordIds, 'keywordId');
            $this->db->delete('keyword');
            // Add or edit glossary
            if (array_key_exists("glossary", $this->vars)) {
                $glossary = trim($this->vars['glossary']);
                $this->db->formatConditions(['keywordId' => $newKeywordId]);
                if ($glossary) {
                    $this->db->update('keyword', ['keywordGlossary' => $glossary]);
                } else {
                    $this->db->updateNull('keyword', 'keywordGlossary');
                }
            }
            // update references to keyword ID
            $this->db->formatConditionsOneField($keywordIds, 'resourcekeywordKeywordId');
            $this->db->update('resource_keyword', ['resourcekeywordKeywordId' => $newKeywordId]);
            // If we are merging, say, 2 keywords that a particular resource has, need to ensure we do not duplicate keyword entries in
            // resource_keyword for that resource
            $deleteIds = $rIds = [];
            $resultset = $this->db->select('resource_keyword', ['resourcekeywordId', 'resourcekeywordResourceId',
                'resourcekeywordMetadataId', 'resourcekeywordKeywordId', ]);
            while ($row = $this->db->fetchRow($resultset)) {
                if (!array_key_exists($row['resourcekeywordId'], $deleteIds) &&
                    $row['resourcekeywordResourceId'] && array_key_exists($row['resourcekeywordResourceId'], $rIds)
                    && ($rIds[$row['resourcekeywordResourceId']] == $row['resourcekeywordKeywordId'])) {
                    $deleteIds[] = $row['resourcekeywordId'];
                } elseif ($row['resourcekeywordResourceId']) {
                    $rIds[$row['resourcekeywordResourceId']] = $row['resourcekeywordKeywordId'];
                } elseif (!array_key_exists($row['resourcekeywordId'], $deleteIds) &&
                    $row['resourcekeywordMetadataId'] && array_key_exists($row['resourcekeywordMetadataId'], $rIds)
                    && ($rIds[$row['resourcekeywordMetadataId']] == $row['resourcekeywordKeywordId'])) {
                    $deleteIds[] = $row['resourcekeywordId'];
                } elseif ($row['resourcekeywordMetadataId']) {
                    $rIds[$row['resourcekeywordMetadataId']] = $row['resourcekeywordKeywordId'];
                }
            }
            if (!empty($deleteIds)) {
                $this->db->formatConditionsOneField($deleteIds, 'resourcekeywordId');
                $this->db->delete('resource_keyword');
            }
        }
        // remove cache files for keywords
        $this->db->deleteCache('cacheKeywords');
        $this->db->deleteCache('cacheResourceKeywords');
        $this->db->deleteCache('cacheMetadataKeywords');
        $this->db->deleteCache('cacheQuoteKeywords');
        $this->db->deleteCache('cacheParaphraseKeywords');
        $this->db->deleteCache('cacheMusingKeywords');
        $this->keyword->checkKeywordGroups();
        $this->mergeInit($this->success->text("keywordMerge"));
    }
    /**
     * deleteInit
     *
     * @param false|string $message
     */
    public function deleteInit($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "delete2", " (" .
            $this->messages->text("resources", "keyword") . ")"));
        $keywords = $this->keyword->grabAll();
        if (!$keywords) {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noKeywords'));

            return;
        }
        $pString = $message;
        $pString .= \HTML\tableStart('left');
        $pString .= \HTML\trStart();
        $td = \FORM\formHeader('admin_ADMINKEYWORD_CORE');
        $td .= \FORM\hidden("method", "deleteConfirm");
        $td .= \FORM\selectFBoxValueMultiple(FALSE, "delete_KeywordId", $keywords, 20) .
            BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint');
        $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $td .= \FORM\formEnd();
        $pString .= \HTML\td($td);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Confirm deletes
     */
    public function deleteConfirm()
    {
        $this->session->delVar("editLock");
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "delete2", " (" .
            $this->messages->text("resources", "keyword") . ")"));
        $input = array_values($this->vars['delete_KeywordId']);
        $keywords = $this->keyword->grabAll();
        $keywordInput = "'" . implode("', '", array_keys(array_intersect(array_flip($keywords), $input))) . "'";
        $keywordInput = html_entity_decode($keywordInput);
        $pString = \HTML\p($this->messages->text("resources", "deleteConfirmKeywords") . ":&nbsp;&nbsp;$keywordInput");
        $pString .= \FORM\formHeader("admin_ADMINKEYWORD_CORE");
        $pString .= \FORM\hidden("delete_KeywordId", base64_encode(serialize($this->vars['delete_KeywordId'])));
        $pString .= \FORM\hidden("method", 'delete');
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * write to the database
     */
    public function delete()
    {
        if ($this->session->getVar("editLock")) {
            $this->badInput->close($this->errors->text("done", "keywordDelete"), $this, 'deleteInit');
        }
        if (!array_key_exists('delete_KeywordId', $this->vars) || !$this->vars['delete_KeywordId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'deleteInit');
        }
        $deleteIds = unserialize(base64_decode($this->vars['delete_KeywordId']));
        foreach ($deleteIds as $deleteId) {
            // Delete old keyword
            $this->db->formatConditions(['keywordId' => $deleteId]);
            $this->db->delete('keyword');
            // remove cache files for keywords
            $this->db->deleteCache('cacheKeywords');
            $this->db->deleteCache('cacheResourceKeywords');
            $this->db->deleteCache('cacheMetadataKeywords');
            $this->db->deleteCache('cacheQuoteKeywords');
            $this->db->deleteCache('cacheParaphraseKeywords');
            $this->db->deleteCache('cacheMusingKeywords');
            // Select all resources and metadata referencing this old keyword and remove keyword from list
            $this->db->formatConditions(['resourcekeywordKeywordId' => $deleteId]);
            $this->db->delete('resource_keyword');
        }
        $this->keyword->checkKeywordGroups();
        // lock reload
        $this->session->setVar("editLock", TRUE);
        // Clear session
        $this->session->clearArray("edit");
        // send back to deleteDisplay with success message
        $this->deleteInit($this->success->text("keywordDelete"));
    }
    /**
     * When merging, insert new keyword or return ID if already exists
     *
     * @param string $keyword
     *
     * @return int
     */
    private function insertKeyword($keyword)
    {
        $this->keywordExists = TRUE;
        if ($id = $this->keyword->checkExists($keyword)) {
            return $id;
        }
        $this->keywordExists = FALSE;
        $fields[] = "keywordKeyword";
        $values[0] = $keyword;
        // given keyword doesn't exist so now write to db
        $this->db->insert('keyword', $fields, $values);

        return $this->db->lastAutoId();
    }
}
