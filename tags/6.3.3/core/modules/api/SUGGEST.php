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
 * Provides interface for auto-suggestions from external programs and for Wikindx AJAX.
 */
class SUGGEST
{
    private $db;
    private $vars;
    private $stmt;
    private $errors;
    private $messages;
    private $session;
    private $user;
    private $badInput;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "resources"));
    }
    /**
     * keywords
     */
    public function keywords()
    {
        if (!array_key_exists('param1', $this->vars) || !$this->vars['param1']) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }

        $param1 = $this->vars['param1'];

        // the following is a hack, should be changed:
        $this->db->condition[] = "keywordKeyword LIKE " . $this->db->tidyInput("$param1%");
        $resultset = $this->db->select('keyword', 'keywordKeyword');

        if (!$this->db->numRows($resultset)) {
            $this->badInput->close($this->messages->text("resources", "noResult"));
        }

        $quoted = [];
        while ($row = $this->db->fetchRow($resultset)) {
            $quoted[] = "\"" . $row['keywordKeyword'] . "\"";
        }
        self::displayRaw($param1, $quoted);
    }
    /**
     * authors
     */
    public function authors()
    {
        if (!array_key_exists('param1', $this->vars) || !$this->vars['param1']) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }

        $param1 = $this->vars['param1'];

        // the following is a hack, should be changed:
        $this->db->condition[] = "( creatorSurname LIKE " . $this->db->tidyInput("$param1%") .
                    " OR creatorFirstname LIKE " . $this->db->tidyInput("$param1%") . ')';
        $resultset = $this->db->select('creator', ['creatorFirstname','creatorSurname']);

        if (!$this->db->numRows($resultset)) {
            $this->badInput->close($this->messages->text("resources", "noResult"));
        }

        $quoted = [];
        while ($row = $this->db->fetchRow($resultset)) {
            $quoted[] = "\"" . $row['creatorSurname'] . ', ' . $row['creatorFirstname'] . "\"";
        }
        self::displayRaw($param1, $quoted);
    }
    /**
     * collections
     */
    public function collections()
    {
        if (!array_key_exists('param1', $this->vars) || !$this->vars['param1']) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }

        $param1 = $this->vars['param1'];

        // the following is a hack, should be changed:
        $this->db->condition[] = "( collectionTitle LIKE " . $this->db->tidyInput("$param1%") .
                    " OR collectionTitleShort LIKE " . $this->db->tidyInput("$param1%") . ')';
        $resultset = $this->db->select('collection', ['collectionTitle']);

        if (!$this->db->numRows($resultset)) {
            $this->badInput->close($this->messages->text("resources", "noResult"));
        }

        $quoted = [];
        while ($row = $this->db->fetchRow($resultset)) {
            $quoted[] = "\"" . $row['collectionTitle'] . "\"";
        }
        self::displayRaw($param1, $quoted);
    }
    /**
     * displayRaw
     *
     * @param mixed $param1
     * @param mixed $quoted
     */
    private function displayRaw($param1, $quoted)
    {
        $body = "[\"$param1\",[";
        $body .= implode(",", $quoted);
        $body .= ']]';

        echo $body;
        FACTORY_CLOSERAW::getInstance();
    }
}
