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
 * INSERTCITATION class.
 *
 * Insert a citation into a tinyMCE textarea
 *
 * This is the main file to handle inserting citations.
 *
 * For users with javascript turned off, the hyperlinks to this page should not display at all.
 */
class INSERTCITATION
{
    /** object */
    private $db;
    /** array */
    private $vars = [];
    /** object */
    private $stmt;
    /** object */
    private $errors;
    /** object */
    private $messages;
    /** object */
    private $common;
    /** object */
    private $session;
    /** object */
    private $badInput;
    /** object */
    private $parsePhrase;
    /** bool */
    private $reprocess = FALSE;
    
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        if (!empty($_POST))
        {
            $this->vars = $_POST;
        }
        elseif (!empty($_GET))
        {
            $this->vars = $_GET;
        }
        GLOBALS::setVars($this->vars, $this->vars);
        GLOBALS::addTplVar('content', '<script src="' . WIKINDX_URL_BASE . '/core/tiny_mce/tiny_mce_popup.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>');
        GLOBALS::addTplVar('content', '<script src="js/wikindxCitedialog.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>');
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->badInput->closeType = 'closePopup';
        $this->parsePhrase = FACTORY_PARSEPHRASE::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "list", "QUICKSEARCH.php"]));
        $this->search = new QUICKSEARCH();
        $this->search->insertCitation = TRUE;
    }
    /**
     * all methods pass through here
     *
     * @param mixed $error
     */
    public function init($error = FALSE)
    {
        //First check, do we have resources?
        if (!$this->db->selectFirstField('database_summary', 'databaseSummaryTotalResources'))
        {
            $pString = $this->messages->text('misc', 'noResources');
            GLOBALS::addTplVar('content', $pString);
            FACTORY_CLOSEPOPUP::getInstance();
        }
        if (!array_key_exists('PagingStart', $this->vars))
        { // reset paging counter and clear session
            $this->session->delVar("mywikindx_PagingStart");
            $this->session->delVar("mywikindx_PagingStartAlpha");
        }
        $pString = $error ? $error : '';
        $pString .= \HTML\h($this->messages->text("heading", "addCitation"), FALSE, 3);
        if (!array_key_exists('method', $this->vars))
        {
            $pString .= $this->search->init(FALSE, FALSE, TRUE, $this->session->getVar("search_Word"));
            $this->session->delVar("list_AllIds");
            $this->session->delVar("list_PagingAlphaLinks");
        }
        else
        {
            if ($this->vars['method'] == 'reprocess')
            {
                $this->reprocess = TRUE;
                $this->search->input = $this->session->getArray("search");
            }
            else
            {
                $this->search->input = $this->checkInput();
            }
            $pString .= $this->search->init(FALSE, FALSE, TRUE, $this->session->getVar("search_Word"));
            $pString .= \HTML\hr();
            $pString .= $this->process();
        }
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
    /**
     * Display results of search
     *
     * @return string
     */
    private function process()
    {
        $this->stmt->listMethodAscDesc = 'search_AscDesc';
        $this->stmt->listType = 'search';
        $this->search->input['Partial'] = TRUE;
        $queryString = 'dialog.php?method=reprocess';
        if (!$this->search->getIds($this->reprocess, $queryString))
        {
            return FALSE;
        }
        $sql = $this->search->getFinalSql($this->reprocess, $queryString);
        $found = $this->common->display($sql, 'cite');
        if ($found)
        {
            $citeFields['formheader'] = \FORM\formHeaderName('', 'citeForm');
            $citeFields['pageStart'] = $this->messages->text("cite", "pages") . "&nbsp;&nbsp;" .
                \FORM\textInput(FALSE, 'pageStart', FALSE, 6, 5);
            $citeFields['pageEnd'] = \FORM\textInput(FALSE, 'pageEnd', FALSE, 6, 5);
            // preText and postText for the citation (usually appears within citation parentheses but will be ignored for endnote-style citations)
            $citeFields['preText'] = $this->messages->text("cite", "preText") . "&nbsp;&nbsp;" .
                \FORM\textInput(FALSE, 'preText', FALSE, 12);
            $citeFields['postText'] = $this->messages->text("cite", "postText") . "&nbsp;&nbsp;" .
                \FORM\textInput(FALSE, 'postText', FALSE, 12);
            $citeFields['cite'] = \FORM\formSubmitButton($this->messages->text("submit", "Cite"), "insert", "onclick=\"citedialog.insert();\"");
            $citeFields['formfooter'] = \FORM\formEnd();
            GLOBALS::addTplVar('citeFields', $citeFields);

            return FALSE;
        }
        else
        {
            return \HTML\p($this->messages->text("resources", "noResult"));
        }
    }
    /**
     * write input to session
     */
    private function writeSession()
    {
        // First, write all input with 'search_' prefix to session
        foreach ($this->vars as $key => $value)
        {
            if (preg_match("/^search_/u", $key))
            {
                $key = str_replace('search_', '', $key);
                // Is this a multiple select box input?  If so, multiple choices are written to session as
                // comma-delimited string (no spaces).
                // Don't write any FALSE or '0' values.
                if (is_array($value))
                {
                    if (!$value[0] || ($value[0] == $this->messages->text("misc", "ignore")))
                    {
                        unset($value[0]);
                    }
                    $value = implode(",", $value);
                }
                if (!trim($value))
                {
                    continue;
                }
                $temp[$key] = trim($value);
            }
        }
        $this->session->clearArray("search");
        if (!empty($temp))
        {
            $this->session->writeArray($temp, 'search');
        }
    }
    /**
     * validate user input
     *
     * Must have at least one of creator, keyword, userTag, searchWord.
     *
     * @return mixed
     */
    private function checkInput()
    {
        $this->writeSession();
        if ((array_key_exists("search_Word", $this->vars) && !\UTF8\mb_trim($this->vars["search_Word"]))
            || !$this->session->getVar("search_Word"))
        {
            $pString = $this->errors->text("inputError", "missing");
            $pString .= \HTML\h($this->messages->text("heading", "addCitation"), FALSE, 3);
            $pString .= $this->search->init(FALSE, FALSE, TRUE);
            GLOBALS::addTplVar('content', $pString);
            FACTORY_CLOSEPOPUP::getInstance();
            die; // needed
        }

        return $this->session->getArray("search");
    }
}
