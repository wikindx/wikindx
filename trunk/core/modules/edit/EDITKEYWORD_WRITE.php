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
 * EDITKEYWORD_WRITE class
 */
class EDITKEYWORD_WRITE
{
    private $db;
    private $vars;
    private $success;
    private $keyword;
    private $gatekeep;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
    }
    /**
     * check we are allowed to edit and then edit
     *
     * @param string $method Either 'edit' or 'editConfirm'
     *
     */
    public function init($method)
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        $this->{$method}();
        $this->tidy();
        // send back to main script with success message
        $message = rawurlencode($this->success->text("keyword"));
        header("Location: index.php?action=edit_EDITKEYWORD_CORE&method=init&message=$message");
    }
    /**
     * write to the database
     */
    private function edit()
    {
        $updateArray['keywordKeyword'] = trim($this->vars['keyword']);
        $glossary = trim($this->vars['text']);
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
    private function editConfirm()
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
