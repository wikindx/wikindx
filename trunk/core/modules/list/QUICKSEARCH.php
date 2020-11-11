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
 *	QUICKSEARCH class
 *
 *	Quickly search database
 */
class QUICKSEARCH
{
    public $words = '';
    public $unions = [];
    public $insertCitation = FALSE; // TRUE if being called from INSERTCITATION
    public $input = [];
    private $db;
    private $vars;
    private $stmt;
    private $errors;
    private $messages;
    private $common;
    private $metadata;
    private $session;
    private $keyword;
    private $badInput;
    private $parsePhrase;
    private $commonBib;
    private $subQ;
    private $browserTabID = FALSE;

    public function __construct()
    {
        $this->session = FACTORY_SESSION::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $this->metadata = FACTORY_METADATA::getInstance();
        $this->common->quickSearch = TRUE;
        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->parsePhrase = FACTORY_PARSEPHRASE::getInstance();
        $this->parsePhrase->quickSearch = TRUE;
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
        if ($this->browserTabID)
        {
            // 1. Load any pre-existing search data into GLOBALS $tempStorage
            // 2. Store in and extract data from $tempStorage
            // 3. Finally, put back $tempStorage into temp_storage using $this->common->updateTempStorage();
            GLOBALS::initTempStorage($this->db, $this->browserTabID);
            $order = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'search_Order');
            $order = GLOBALS::getTempStorage('search_Order');
            if (!$order)
            {
                $order = 'creator';
            }
        }
        else
        {
            $order = $this->session->getVar("search_Order");
            if (!in_array($order, ['title', 'creator', 'publisher', 'year', 'timestamp']))
            { // set default
                $this->session->setVar("search_Order", "creator");
            }
        }
        // Turn on the 'add bookmark' menu item
        $this->session->setVar("bookmark_DisplayAdd", TRUE);
    }
    /**
     * display form options. $word comes from modules/cite/INSERTCITATION.php if being used.
     *
     * @param mixed $error
     * @param mixed $tableBorder
     * @param mixed $returnString
     * @param mixed $word
     *
     * @return string
     */
    public function init($error = FALSE, $tableBorder = FALSE, $returnString = FALSE, $word = FALSE)
    {
        // First check, do we have resources?
        if (!$this->common->resourcesExist())
        {
            return;
        }
        // If QUICKSEARCH is called by the special string $QUICKSEARCH$ on the front page, there is already a help icon being used.
        if (!GLOBALS::getTplVar('help'))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "help", "HELPMESSAGES.php"]));
            $help = new HELPMESSAGES();
            GLOBALS::setTplVar('help', $help->createLink('search'));
        }
        if (!$returnString)
        {
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "search"));
        }
        $this->session->delVar("mywikindx_PagingStart");
        $this->session->delVar("mywikindx_PagingStartAlpha");
        if ($this->browserTabID)
        {
            GLOBALS::unsetTempStorage(['mywikindx_PagingStart', 'mywikindx_PagingStartAlpha']);
        }
        $pString = $error ? $error : FALSE;
        if (!$this->insertCitation)
        {
            $pString .= \FORM\formHeader("list_QUICKSEARCH_CORE");
        }
        else
        {
            $pString .= \FORM\formHeaderVisibleAction("dialog.php", "searchInsertCitation");
        }
        $pString .= \FORM\hidden("method", "process");
        $pString .= \FORM\hidden("browserTabID", $this->browserTabID);
        if ($tableBorder)
        {
            $pString .= \HTML\tableStart('generalTable borderStyleSolid');
        }
        else
        {
            $pString .= \HTML\tableStart('width50percent');
        }
        $pString .= \HTML\trStart();
        $this->radioButtons = FALSE;
        if (!$word)
        {
            if ($this->browserTabID && ($word = GLOBALS::getTempStorage('search_Word')))
            {
                $word = htmlspecialchars(stripslashes($word), ENT_QUOTES | ENT_HTML5);
            }
            elseif ($this->session->issetVar("search_Word"))
            {
                $word = htmlspecialchars(stripslashes($this->session->getVar("search_Word")), ENT_QUOTES | ENT_HTML5);
            }
            else
            {
                $word = FALSE;
            }
        }
        $hint = BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "wordLogic")
        ), 'hint') . BR;
        if (!$this->insertCitation)
        {
            $pString .= \HTML\td(\FORM\textInput(
                $this->messages->text("search", "word"),
                "search_Word",
                $word,
                40
            ) . $hint . \FORM\formSubmit($this->messages->text("submit", "Search")), $tableBorder ? 'padding4px' : '');
        }
        else
        {
            $pString .= \HTML\td(\FORM\textInput(
                $this->messages->text("search", "word"),
                "search_Word",
                $word,
                60
            ) . $hint . \FORM\formSubmit($this->messages->text("submit", "Search")), $tableBorder ? 'padding4px' : '');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \FORM\formEnd();
        if ($returnString)
        {
            return $pString; // cf FRONT.php or process() below.
        }
        else
        {
            GLOBALS::addTplVar('content', $pString);
        }
    }
    /**
     * create the unions
     *
     * @param mixed $ors
     * @param mixed $orsFT
     * @param mixed $search
     * @param mixed $searchFT
     */
    public function fieldSql($search, $searchFT)
    {
        if ((strpos($searchFT, '-') !== FALSE) && (substr($searchFT, -1) === '*'))
        {
            $searchFT = '"' . substr_replace($searchFT, '"', strlen($searchFT) - 1);
        }
        $searchFT = str_replace("'", "''", $searchFT);
        foreach (['resourcecustomShort', 'resourcecustomLong'] as $field)
        {
            $field = $this->db->formatFields($field);
        }
        // title
        $field = $this->db->concat([$this->db->formatFields('resourceNoSort'), $this->db->formatFields('resourceTitleSort')], ' ');
        $this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', $field, $search));
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource', [['resourceId' => 'rId']]));
        // creatorSurname
        $this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', 'creatorSurname', $search));
        $this->db->formatConditions(['resourcecreatorResourceId' => ' IS NOT NULL ']);
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_creator', [['resourcecreatorResourceId' => 'rId']]));
        // keywordKeyword
        $this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', 'keywordKeyword', $search));
        $this->db->formatConditions(['resourcekeywordResourceId' => ' IS NOT NULL ']);
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_keyword', [['resourcekeywordResourceId' => 'rId']]));
        // resourcemetadataText
        $matchAgainst = $this->db->fulltextSearch('resourcemetadataText', $searchFT);
        $this->metadata->setCondition(FALSE, FALSE, TRUE);
        $this->db->formatConditions($matchAgainst);
        $this->db->formatConditions(['resourcemetadataResourceId' => 'IS NOT NULL']);
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_metadata', [['resourcemetadataResourceId' => 'rId']]));
        // usertagsTag
        $this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', 'usertagsTag', $search));
        $result = $this->db->formatFields('usertagsUserId') . $this->db->equal . '1';
        $userCond = $this->db->caseWhen('usertagsId', 'IS NOT NULL', $result, FALSE, FALSE);
        $this->db->formatConditions($userCond);
        $this->db->leftJoin('user_tags', 'resourceusertagsTagId', 'usertagsId');
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_user_tags', [['resourceusertagsResourceId' => 'rId']]));
        // resourcecustomShort
        $this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', 'resourcecustomShort', $search));
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_custom', [['resourcecustomResourceId' => 'rId']]));
        // resourcecustomLong
        $matchAgainst = $this->db->fulltextSearch('resourcecustomLong', $searchFT);
        $this->db->formatConditions($matchAgainst);
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_custom', [['resourcecustomResourceId' => 'rId']]));
        // resourcetextAbstract
        $matchAgainst = $this->db->fulltextSearch('resourcetextAbstract', $searchFT);
        $this->db->formatConditions($matchAgainst);
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_text', [['resourcetextId' => 'rId']]));
        // resourcetextNote
        $matchAgainst = $this->db->fulltextSearch('resourcetextNote', $searchFT);
        $this->db->formatConditions($matchAgainst);
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_text', [['resourcetextId' => 'rId']]));

        $this->unions = $this->db->union($unions);
    }
    /**
     * For re-ordering or paging
     */
    public function reprocess()
    {
        if ($this->browserTabID && array_key_exists('type', $this->vars) &&
            ($this->vars['type'] == 'lastMulti') && !\TEMPSTORAGE\exists($this->db, $this->browserTabID))
        { // Opened Last Multi in a new tab
            $this->init();

            return;
        }
        if ($this->browserTabID && array_key_exists('method', $this->vars) &&
            ($this->vars['method'] == 'reprocess') && !\TEMPSTORAGE\exists($this->db, $this->browserTabID))
        { // Opened paging link in a new tab
            $this->init();

            return;
        }
        if (array_key_exists('message', $this->vars))
        {
            GLOBALS::addTplVar('content', $this->vars['message']);
        }
        if (array_key_exists('quickSearch', $this->vars))
        {
            if ($this->vars['quickSearch'])
            {
                $this->common->quickSearch = TRUE;
            }
            else
            {
                $this->common->quickSearch = FALSE;
            }
        }
        if (array_key_exists('keepHighlight', $this->vars))
        {
            if ($this->vars['keepHighlight'])
            {
                $this->common->keepHighlight = TRUE;
            }
            else
            {
                $this->common->keepHighlight = FALSE;
            }
        }
        if ($this->browserTabID)
        {
            $this->common->keepHighlight = TRUE;
            $this->common->patterns = GLOBALS::getTempStorage('search_Patterns');
        }
        elseif (array_key_exists('patterns', $this->vars))
        {
            $this->common->patterns = unserialize(base64_decode($this->vars['patterns']));
        }
        $reprocess = TRUE;
        if (array_key_exists("search_Order", $this->vars) && $this->vars["search_Order"])
        {
            if (($this->session->getVar("search_Order") != $this->vars["search_Order"]) ||
                ($this->session->getVar("search_AscDesc") != $this->vars['search_AscDesc']))
            {
                $reprocess = FALSE;
            }
            $this->input['Order'] = $this->vars["search_Order"];
            $this->input['AscDesc'] = $this->vars["search_AscDesc"];
            $this->session->setVar("search_Order", $this->vars["search_Order"]);
            $this->session->setVar("sql_LastOrder", $this->vars["search_Order"]);
            $this->session->setVar("search_AscDesc", $this->vars['search_AscDesc']);
            if ($this->browserTabID)
            {
                GLOBALS::setTempStorage(['search_Order' => $this->vars["search_Order"]]);
                GLOBALS::setTempStorage(['sql_LastOrder' => $this->vars["search_Order"]]);
                GLOBALS::setTempStorage(['search_AscDesc' => $this->vars['search_AscDesc']]);
                $this->input['Word'] = GLOBALS::getTempStorage('search_Word');
            }
            else
            {
                $this->input['Word'] = $this->session->getVar("search_Word");
            }
        }
        elseif ($this->browserTabID)
        {
            $this->input['Word'] = GLOBALS::getTempStorage('search_Word');
            $this->input['Order'] = GLOBALS::getTempStorage('search_Order');
            $this->input['AscDesc'] = GLOBALS::getTempStorage('search_AscDesc');
        }
        else
        {
            $this->input = $this->session->getArray("search");
        }
        if (array_key_exists("navigate", $this->vars))
        {
            $reprocess = FALSE;
        }
        $this->process($reprocess);
    }
    /**
     * process
     *
     * @param bool $reprocess
     */
    public function process($reprocess = FALSE)
    {
        if (!$reprocess)
        {
            $this->session->delVar("list_AllIds");
            $this->session->delVar("list_PagingAlphaLinks");
            if ($this->browserTabID)
            {
                GLOBALS::unsetTempStorage(['list_AllIds', 'list_PagingAlphaLinks']);
            }
        }
        if (!$reprocess || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->session->delVar("sql_ListStmt");
            $this->session->delVar("advancedSearch_listParams");
            if ($this->browserTabID)
            {
                GLOBALS::unsetTempStorage(['sql_ListStmt', 'advancedSearch_listParams']);
            }
        }
        $this->stmt->listMethodAscDesc = 'search_AscDesc';
        $this->stmt->listType = 'search';
        $queryString = 'action=list_QUICKSEARCH_CORE&method=reprocess';
        if (empty($this->input))
        {
            $this->input = $this->checkInput();
        }
        if (!array_key_exists('order', $this->input) && !array_key_exists('Order', $this->input))
        {
            $this->session->setVar("search_Order", 'creator');
            $this->session->setVar("sql_LastOrder", 'creator');
            $this->session->setVar("search_AscDesc", $this->db->asc);
            if ($this->browserTabID)
            {
                GLOBALS::setTempStorage(['search_Order' => 'creator']);
                GLOBALS::setTempStorage(['sql_LastOrder' => 'creator']);
                GLOBALS::setTempStorage(['search_AscDesc' => $this->db->asc]);
            }
        }
        else
        {
            $this->session->setVar("sql_LastOrder", $this->input['Order']);
            if ($this->browserTabID)
            {
                GLOBALS::setTempStorage(['sql_LastOrder' => $this->input['Order']]);
            }
        }
        $this->input['Partial'] = TRUE;
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['search_Partial' => $this->input['Partial']]);
        }
        GLOBALS::setTplVar('resourceListSearchForm', $this->init(FALSE, TRUE, TRUE));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "search"));
        if (!$this->getIds($reprocess, $queryString))
        {
            return FALSE;
        }

        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'lastMulti') && (GLOBALS::getUserVar('PagingStyle') != 'A'))
        {
            $this->pagingObject = FACTORY_PAGING::getInstance();
            $this->pagingObject->queryString = $queryString;
            $this->pagingObject->getPaging();
            $this->common->pagingObject = $this->pagingObject;
            $this->common->lastMulti('search');
            $this->common->updateTempStorage();

            return;
        }
        if ($this->browserTabID)
        {
            $searchTerms = GLOBALS::getTempStorage('search_Highlight');
        }
        else
        {
            $searchTerms = \UTF8\mb_explode(",", $this->session->getVar("search_Highlight"));
        }
        $patterns = [];
        foreach ($searchTerms as $term)
        {
            if (trim($term))
            {
                $term = preg_quote($term, '/');
                $patterns[] = "/($term)(?=[^>]*(<|$))/ui";
            }
        }
        $this->common->patterns = $patterns;
        $this->session->setVar("search_Patterns", $patterns);
        if ($this->browserTabID)
        {
            //	        GLOBALS::setTempStorage(['search_AscDesc' => $this->db->asc]);
            GLOBALS::setTempStorage(['search_Patterns' => $patterns]);
        }
        $this->common->keepHighlight = TRUE;
        $sql = $this->getFinalSql($reprocess, $queryString);
        $this->common->display($sql, 'search');
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['sql_LastMulti' => $queryString]);
        }
        $this->session->saveState(['search', 'sql', 'setup', 'bookmark', 'list']);
        $this->session->delVar("bookmarkRead");
        $this->common->updateTempStorage();
    }
    /**
     * Get the list of resource ids
     *
     * @param bool $reprocess
     * @param string $queryString
     */
    public function getIds($reprocess, $queryString)
    {
        if (!$reprocess || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->parseWord();
            // Deal with OR strings first
            $ors = implode($this->db->or, $this->parsePhrase->ors);
            $orsFT = implode(' ', $this->parsePhrase->orsFT);
            if ($ors)
            {
                $this->getInitialIds($ors, $orsFT, 'or');
            }
            // Deal with AND strings next
            foreach ($this->parsePhrase->ands as $and)
            { // we use array_intersect . . .
                $this->getInitialIds($and, array_shift($this->parsePhrase->andsFT), 'and');
            }
            // Finally, deal with NOT strings. We match IDs using OR then subtract the found ids from the main ids array
            // If there are no ANDs or ORs, we must first get all existing resource ids
            $nots = implode($this->db->or, $this->parsePhrase->nots);
            $notsFT = implode(' ', $this->parsePhrase->notsFT);
            if (empty($this->parsePhrase->ands) && empty($this->parsePhrase->ors))
            {
                $resultSet = $this->db->select('resource', 'resourceId');
                while ($row = $this->db->fetchRow($resultSet))
                {
                    $allIds[] = $row['resourceId'];
                }
                $this->session->setVar("list_AllIds", $allIds);
                if ($this->browserTabID)
                {
                    GLOBALS::setTempStorage(['list_AllIds' => $allIds]);
                }
                unset($allIds);
            }
            if ($nots)
            {
                $this->getInitialIds($nots, $notsFT, 'not');
            }
            // Now finalize
            if (!$this->stmt->quicksearchSubQuery($queryString, FALSE, $this->subQ, 'final'))
            {
                $this->common->noResources('search');

                return FALSE;
            }
        }

        return TRUE;
    }
    /**
     * Get the final SQL statement to send to $this->common->display or back to INSERTCITATION
     *
     * @param bool $reprocess
     * @param string $queryString
     */
    public function getFinalSql($reprocess, $queryString)
    {
        if (!$reprocess || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->stmt->joins = [];
            $this->stmt->joins['resource_creator'] = ['resourcecreatorResourceId', 'resourceId'];
            $this->stmt->joins['creator'] = ['creatorId', 'resourcecreatorCreatorId'];
            if (!$order = GLOBALS::getTempStorage('search_Order'))
            {
                $order = $this->session->getVar("search_Order");
            }
            $sql = $this->stmt->listListQS($order, FALSE, $this->subQ);
        }
        else
        {
            if (!$sql = GLOBALS::getTempStorage('sql_ListStmt'))
            {
                $sql = $this->session->getVar("sql_ListStmt");
            }
            $this->pagingObject = FACTORY_PAGING::getInstance();
            $this->pagingObject->queryString = $queryString;
            $this->pagingObject->getPaging();
            $this->common->pagingObject = $this->pagingObject;
            $limit = $this->db->limit(GLOBALS::getUserVar('Paging'), $this->pagingObject->start, TRUE); // "LIMIT $limitStart, $limit";
            $sql .= $limit;
        }

        return $sql;
    }
    /**
     * Get the initial IDs from the database
     *
     * @param mixed $search
     * @param mixed $searchFT
     * @param mixed $type
     */
    private function getInitialIds($search, $searchFT, $type)
    {
        $this->fieldSql($search, $searchFT);
        $subStmt = $this->setSubQuery();
        $this->stmt->quicksearchSubQuery(FALSE, $subStmt, FALSE, $type);

        return TRUE;
    }
    /**
     * parse the search word(s)
     */
    private function parseWord()
    {
        $this->words = $this->parsePhrase->parse($this->input);
        $this->parsePhrase->parse($this->input, FALSE, FALSE, FALSE, TRUE);
        if ((is_array($this->words) && empty($this->words)) || !$this->parsePhrase->validSearch)
        {
            GLOBALS::setTplVar('resourceListSearchForm', FALSE);
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
        }
    }
    /**
     * write input to session
     */
    private function writeSession()
    {
        $this->session->clearArray("search");
        if (!array_key_exists('search_Word', $this->vars))
        {
            $this->vars['search_Word'] = $this->session->getVar('tempTab_search_Word');
            $this->session->clearArray('tempTab');
        }
        $word = \UTF8\mb_trim($this->vars['search_Word']);
        $this->session->setVar('search_Word', $word);
        if ($this->browserTabID)
        {
            GLOBALS::setTempStorage(['search_Word' => $word]);
        }
        if (array_key_exists('search_Order', $this->vars))
        {
            $this->session->setVar('search_Order', $this->vars['search_Order']);
            if ($this->browserTabID)
            {
                GLOBALS::setTempStorage(['search_Order' => $this->vars['search_Order']]);
            }
        }
    }
    /**
     * validate user input - method, word and field are required
     *
     * Input comes either from form input or, when paging, from the session.
     *
     * @return array
     */
    private function checkInput()
    {
        $this->writeSession();
        if (!$this->browserTabID)
        {
            if ((array_key_exists("search_Word", $this->vars) && !\UTF8\mb_trim($this->vars["search_Word"]))
            || !$this->session->getVar("search_Word"))
            {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
            }

            return $this->session->getArray("search");
        }
        else
        {
            if ((array_key_exists("search_Word", $this->vars) && !\UTF8\mb_trim($this->vars["search_Word"]))
            || !GLOBALS::getTempStorage('search_Word'))
            {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
            }
            foreach (GLOBALS::getTempStorage() as $key => $value)
            {
                if (preg_match("/^search_/u", $key))
                {
                    $key = str_replace('search_', '', $key);
                    $array[$key] = $value;
                }
            }

            return $array;
        }
    }
    /**
     * Set the subQuery
     */
    private function setSubQuery()
    {
        if (!$this->session->getVar("search_Order"))
        { // from INSERTCITE
            $this->session->setVar("search_Order", 'creator');
            if ($this->browserTabID)
            {
                GLOBALS::setTempStorage(['search_Order' => 'creator']);
            }
        }
        if (!$this->session->getVar("search_AscDesc"))
        { // from INSERTCITE
            $this->session->setVar("search_AscDesc", $this->db->asc);
            if ($this->browserTabID)
            {
                GLOBALS::setTempStorage(['search_AscDesc' => $this->db->asc]);
            }
        }
        $this->db->ascDesc = $this->session->getVar("search_AscDesc");
        switch ($this->session->getVar("search_Order")) {
            case 'title':
                $this->stmt->useBib('rId');
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource'] = ['resourceId', 'rId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($this->unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'creator':
                $this->stmt->useBib('rId');
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource_creator'] = ['resourcecreatorResourceId', 'rId'];
                $this->stmt->joins['creator'] = ['creatorId', 'resourcecreatorCreatorId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($this->unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'publisher':
                $this->stmt->useBib('rId');
                $this->stmt->quarantine(FALSE, 'rId', FALSE);
                $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'rId'];
                $this->stmt->joins['publisher'] = ['publisherId', 'resourcemiscPublisher'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($this->unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'year':
                $this->stmt->useBib('rId');
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource'] = ['resourceId', 'rId'];
                $this->stmt->joins['resource_year'] = ['resourceyearId', 'rId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($this->unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'timestamp':
                $this->stmt->useBib('rId');
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource_timestamp'] = ['resourcetimestampId', 'rId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($this->unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            default:
                break;
        }
    }
}
