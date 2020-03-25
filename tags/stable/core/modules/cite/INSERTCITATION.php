<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
    private $config;
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
	/** array */
    private $input;
    /** bool */
    private $reprocess = FALSE;
    
    public function __construct()
    {
        $this->config = FACTORY_CONFIG::getInstance();
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
        GLOBALS::addTplVar('content', '<script src="' . $this->config->WIKINDX_BASE_URL . '/core/tiny_mce/tiny_mce_popup.js"></script>');
        GLOBALS::addTplVar('content', '<script src="js/wikindxCitedialog.js"></script>');
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->badInput->closeType = 'closePopup';
        $this->parsePhrase = FACTORY_PARSEPHRASE::getInstance();
        include_once("core/modules/list/QUICKSEARCH.php");
        $this->search = new QUICKSEARCH();
        $this->search->insertCitation = TRUE;
    }
    /** 
     * all methods pass through here
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
            $this->session->delVar('mywikindx_PagingStart');
            $this->session->delVar('mywikindx_PagingStartAlpha');
        }
        $pString = $error ? $error : '';
        $pString .= \HTML\h($this->messages->text("heading", "addCitation"), FALSE, 3);
        $word = $this->session->issetVar('setup_CiteWord') ? $this->session->getVar('setup_CiteWord') : FALSE;
        if (!array_key_exists('method', $this->vars))
        {
            $pString .= $this->search->init(FALSE, FALSE, TRUE, $word);
        }
        elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'process'))
        {
            $this->session->setVar('setup_BackupWord', $this->session->getVar('search_Word'));
            $this->input = $this->checkInput();
            $this->session->setVar('list_BackupAllIds', $this->session->getVar('list_AllIds'));
            $this->session->delVar('list_AllIds');
            $this->session->setVar('sql_BackupListStmt', $this->session->getVar('sql_ListStmt'));
            $this->session->delVar('sql_ListStmt');
            $this->session->setVar('setup_BackupPagingTotal', $this->session->getVar('setup_PagingTotal'));
            $this->session->delVar('setup_PagingTotal');
            $this->session->setVar('list_BackupPagingAlphaLinks', $this->session->getVar('list_PagingAlphaLinks'));
            $this->session->delVar('list_PagingAlphaLinks');
            $pString .= $this->search->init(FALSE, FALSE, TRUE);
            $pString .= \HTML\hr();
            $pString .= $this->process();
            $this->session->setVar('sql_CiteListStmt', $this->session->getVar('sql_ListStmt')); // Ready for reprocessing
            $this->session->setVar('setup_CitePagingTotal', $this->session->getVar('setup_PagingTotal')); // Ready for reprocessing
            $this->session->setVar('setup_CiteWord', $this->session->getVar('search_Word')); // Ready for reprocessing
            if ($this->session->getVar('list_BackupAllIds'))
            {
                $this->session->setVar('list_AllIds', $this->session->getVar('list_BackupAllIds'));
            }
            if ($this->session->getVar('sql_BackupListStmt'))
            {
                $this->session->setVar('sql_ListStmt', $this->session->getVar('sql_BackupListStmt'));
            }
            if ($this->session->getVar('setup_BackupPagingTotal'))
            {
                $this->session->setVar('setup_PagingTotal', $this->session->getVar('setup_BackupPagingTotal'));
            }
            $this->session->setVar('search_Word', $this->session->getVar('setup_BackupWord'));
        }
        elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'reprocess'))
        {
            $this->reprocess = TRUE;
            $this->session->setVar('setup_Word', $this->session->getVar('search_CiteWord'));
            $this->input = $this->session->getArray("search");
            $pString .= $this->search->init(FALSE, FALSE, TRUE);
            $pString .= \HTML\hr();
            $pString .= $this->process();
            if ($this->session->getVar('list_BackupAllIds'))
            {
                $this->session->setVar('list_AllIds', $this->session->getVar('list_BackupAllIds'));
            }
            if ($this->session->getVar('sql_BackupListStmt'))
            {
                $this->session->setVar('sql_ListStmt', $this->session->getVar('sql_BackupListStmt'));
            }
            if ($this->session->getVar('setup_BackupPagingTotal'))
            {
                $this->session->setVar('setup_PagingTotal', $this->session->getVar('setup_BackupPagingTotal'));
            }
            if ($this->session->getVar('setup_BackupWord'))
            {
                $this->session->setVar('search_Word', $this->session->getVar('setup_BackupWord'));
            }
        }
        $this->session->saveState(['search', 'sql', 'setup', 'list']);
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
        $this->input['Partial'] = TRUE;
        $queryString = 'dialog.php?method=reprocess';
        if (!$this->reprocess || ($this->session->getVar('setup_PagingStyle') == 'A'))
        {
            $this->parseWord();
            $this->search->fieldSql();
            $subStmt = $this->setSubQuery();
            $resourcesFound = $this->stmt->listSubQuery('creator', $queryString, $subStmt, FALSE, $this->subQ);
            if (!$resourcesFound)
            {
                $this->common->noResources('search');

                return;
            }
            $sql = $this->stmt->listList('creator', FALSE, $this->subQ);
        }
        else
        {
            $sql = $this->quickQuery($queryString);
        }
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
     * Quicker querying when paging
     *
     * @param string $queryString
     *
     * @return string
     */
    private function quickQuery($queryString)
    {
        $sql = $this->session->getVar('sql_CiteListStmt');
        $this->session->setVar('setup_PagingTotal', $this->session->getVar('setup_CitePagingTotal'));
        $this->pagingObject = FACTORY_PAGING::getInstance();
        $this->pagingObject->queryString = $queryString;
        $this->pagingObject->getPaging();
        $this->common->pagingObject = $this->pagingObject;
        $sql .= $this->db->limit($this->session->getVar('setup_Paging'), $this->pagingObject->start, TRUE); // "LIMIT $limitStart, $limit";
        return $sql;
    }
    /**
     * parse the search word(s)
     */
    private function parseWord()
    {
        $this->search->words = $this->parsePhrase->parse($this->input);
        if (!$this->search->words)
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
        }
        $this->search->words = str_replace('!WIKINDXFIELDWIKINDX!', $this->db->formatFields('concatText'), $this->search->words);
    }
    /**
     * create the subquery
     *
     * @return string
     */
    private function setSubQuery()
    {
        $this->db->ascDesc = $this->session->getVar('search_AscDesc');
        $this->stmt->quarantine(FALSE, 'rId');
        $this->stmt->useBib('rId');
        $this->stmt->conditions[] = $this->search->words;
        $this->stmt->joins['resource_creator'] = ['resourcecreatorResourceId', 'rId'];
        $this->stmt->joins['creator'] = ['creatorId', 'resourcecreatorCreatorId'];
        $this->stmt->executeCondJoins();
        $this->db->groupBy(['rId', 'resourcecreatorCreatorSurname']);
        $this->subQ = $this->db->subQuery($this->search->unions, 'u', FALSE);
        $subQuery = $this->db->from . ' ' . $this->subQ;

        return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
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
        if ((array_key_exists("search_Word", $this->vars) && !trim($this->vars["search_Word"]))
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
