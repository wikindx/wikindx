<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 *	QUICKSEARCH class
 *
 *	Quickly search database
 */
class QUICKSEARCH
{
    public $words = '';
    public $wordsFT = '';
    public $unions = [];
    public $insertCitation = FALSE; // TRUE if being called from INSERTCITATION
    private $db;
    private $vars;
    private $stmt;
    private $errors;
    private $messages;
    private $common;
    private $metadata;
    private $session;
    private $keyword;
    private $input = [];
    private $badInput;
    private $parsePhrase;
    private $commonBib;
    private $subQ;

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
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        switch ($this->session->getVar("search_Order"))
        {
            case 'title':
                break;
            case 'creator':
                break;
            case 'publisher':
                break;
            case 'year':
                break;
            case 'timestamp':
                break;
            default:
                $this->session->setVar("search_Order", "creator");
        }
        // Turn on the 'add bookmark' menu item
        $this->session->setVar("bookmark_DisplayAdd", TRUE);
    }
    /**
     * display form options. $word comes from modules/cite/INSERTCITATION.php
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
        ///First check, do we have resources?
        if (!$this->common->resourcesExist())
        {
            return;
        }
// If QUICKSEARCH is called by the special string $QUICKSEARCH$ on the front page, there is already a help icon being used.
        if (!GLOBALS::getTplVar('help'))
        {
	        include_once("core/modules/help/HELPMESSAGES.php");
    	    $help = new HELPMESSAGES();
        	GLOBALS::setTplVar('help', $help->createLink('search'));
        }
        if (!$returnString)
        {
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "search"));
        }
        $this->session->delVar("mywikindx_PagingStart");
        $this->session->delVar("mywikindx_PagingStartAlpha");
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
            $word = $this->session->issetVar("search_Word") ?
                htmlspecialchars(stripslashes($this->session->getVar("search_Word")), ENT_QUOTES | ENT_HTML5) : FALSE;
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
     * Reset the form and clear the session
     */
    public function reset()
    {
        $this->session->clearArray('search');
        $this->init();
    }
    /**
     * create the unions
     */
    public function fieldSql($ors, $orsFT)
    {
    	foreach (['resourcecustomShort', 'resourcecustomLong'] as $field)
    	{
    		$field = $this->db->formatFields($field);
    	}
// title
    	$field = $this->db->concat([$this->db->formatFields('resourceNoSort'), $this->db->formatFields('resourceTitleSort')], ' ');
    	$this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', $field, $ors));
    	$unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource', [['resourceId' => 'rId']]));
// creatorSurname
    	$this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', 'creatorSurname', $ors));
    	$this->db->formatConditions(['resourcecreatorResourceId' => ' IS NOT NULL ']);
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
    	$unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_creator', [['resourcecreatorResourceId' => 'rId']]));
// keywordKeyword
    	$this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', 'keywordKeyword', $ors));
    	$this->db->formatConditions(['resourcekeywordResourceId' => ' IS NOT NULL ']);
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
    	$unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_keyword', [['resourcekeywordResourceId' => 'rId']]));
// resourcemetadataText
    	$matchAgainst = $this->db->fulltextSearch('resourcemetadataText', $orsFT);
    	$this->metadata->setCondition(FALSE, FALSE, TRUE);
    	$this->db->formatConditions($matchAgainst);
    	$this->db->formatConditions(['resourcemetadataResourceId' => 'IS NOT NULL']);
    	$unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_metadata', [['resourcemetadataResourceId' => 'rId']]));
// usertagsTag
    	$this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', 'usertagsTag', $ors));
        $result = $this->db->formatFields('usertagsUserId') . $this->db->equal . '1';
        $userCond = $this->db->caseWhen('usertagsId', 'IS NOT NULL', $result, FALSE, FALSE);
    	$this->db->formatConditions($userCond);
        $this->db->leftJoin('user_tags', 'resourceusertagsTagId', 'usertagsId');
    	$unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_user_tags', [['resourceusertagsResourceId' => 'rId']]));
// resourcecustomShort
    	$this->db->formatConditions(str_replace('!WIKINDXFIELDWIKINDX!', 'resourcecustomShort', $ors));
    	$unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_custom', [['resourcecustomResourceId' => 'rId']]));
// resourcecustomLong
    	$matchAgainst = $this->db->fulltextSearch('resourcecustomLong', $orsFT);
    	$this->db->formatConditions($matchAgainst);
    	$unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_custom', [['resourcecustomResourceId' => 'rId']]));
// resourcetextAbstract
    	$matchAgainst = $this->db->fulltextSearch('resourcetextAbstract', $orsFT);
    	$this->db->formatConditions($matchAgainst);
    	$unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_text', [['resourcetextId' => 'rId']]));
// resourcetextNote
    	$matchAgainst = $this->db->fulltextSearch('resourcetextNote', $orsFT);
    	$this->db->formatConditions($matchAgainst);
    	$unions[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource_text', [['resourcetextId' => 'rId']]));

    	$this->unions = $this->db->union($unions);
    }
    /**
     * For re-ordering or paging
     */
    public function reprocess()
    {
        $reprocess = TRUE;
        $this->input = $this->session->getArray("search");
        if (array_key_exists("search_Order", $this->vars) && $this->vars["search_Order"])
        {
            if (($this->session->getVar("search_Order") != $this->vars["search_Order"]) ||
                ($this->session->getVar("search_AscDesc") != $this->vars['search_AscDesc']))
            {
                $reprocess = FALSE;
            }
            $this->input['order'] = $this->vars["search_Order"];
            $this->session->setVar("search_Order", $this->input['order']);
            $this->session->setVar("sql_LastOrder", $this->input['order']);
            $this->session->setVar("search_AscDesc", $this->vars['search_AscDesc']);
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
        }
        if (!$reprocess || ($this->session->getVar("setup_PagingStyle") == 'A'))
        {
            $this->session->delVar("sql_ListStmt");
            $this->session->delVar("advancedSearch_listParams");
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "search"));
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
        }
        else
        {
            $this->session->setVar("sql_LastOrder", $this->input['Order']);
        }
        $this->input['Partial'] = TRUE;
        GLOBALS::setTplVar('resourceListSearchForm', $this->init(FALSE, TRUE, TRUE));
        if (!$reprocess || ($this->session->getVar("setup_PagingStyle") == 'A'))
        {
        	$masterIds = $andIds = $notIds = [];
            $this->parseWord();
            $resourcesFound = FALSE;
// Deal with OR strings first
			$ors = join($this->db->or, $this->parsePhrase->ors); // shouldn't be necessary as there should only be one element
			$orsFT = join(' ', $this->parsePhrase->orsFT);
			if ($ors && $this->getInitialIds($ors, $orsFT, 'or'))
			{
				$resourcesFound = TRUE;
			}
// Deal with AND strings next
			$ands = join($this->db->and, $this->parsePhrase->ands); // shouldn't be necessary as there should only be one element
			$andsFT = join(' ', $this->parsePhrase->andsFT);
			if ($ands && $this->getInitialIds($ands, $andsFT, 'and'))
			{
				$resourcesFound = TRUE;
			}
			unset($andIds);
// Finally, deal with NOT strings. We match IDs using OR then subtract the found ids from the main ids array
			$nots = join($this->db->or, $this->parsePhrase->nots); // shouldn't be necessary as there should only be one element
			$notsFT = join(' ', $this->parsePhrase->notsFT);
			if ($nots && $this->getInitialIds($nots, $notsFT, 'not'))
			{
				$resourcesFound = TRUE;
			}
			unset($notIds);
			if (!$resourcesFound)
			{
                $this->common->noResources('search');
				return FALSE;
			}
// Now finalize
			if (!$this->stmt->quicksearchSubQuery($queryString, FALSE, $this->subQ, 'final'))
			{
                $this->common->noResources('search');
				return FALSE;
			}
        }
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'lastMulti') && ($this->session->getVar("setup_PagingStyle") != 'A'))
        {
            $this->pagingObject = FACTORY_PAGING::getInstance();
            $this->pagingObject->queryString = $queryString;
            $this->pagingObject->getPaging();
            $this->common->pagingObject = $this->pagingObject;
            $this->common->lastMulti('search');

            return;
        }
        $searchTerms = UTF8::mb_explode(",", $this->session->getVar("search_Highlight"));
        $patterns = [];
        foreach ($searchTerms as $term)
        {
            if (trim($term))
            {
                $term = preg_quote($term);
                $patterns[] = "/($term)(?=[^>]*(<|$))/ui";
            }
        }
        $this->common->patterns = $patterns;
        $this->session->setVar("search_Patterns", base64_encode(serialize($patterns)));
        $this->common->keepHighlight = TRUE;
        if (!$reprocess || ($this->session->getVar("setup_PagingStyle") == 'A'))
        {
            $sql = $this->stmt->listList($this->session->getVar("search_Order"), FALSE, $this->subQ);
        }
        else
        {
            $sql = $this->session->getVar("sql_ListStmt");
            $this->pagingObject = FACTORY_PAGING::getInstance();
            $this->pagingObject->queryString = $queryString;
            $this->pagingObject->getPaging();
            $this->common->pagingObject = $this->pagingObject;
            $limit = $this->db->limit($this->session->getVar("setup_Paging"), $this->pagingObject->start, TRUE); // "LIMIT $limitStart, $limit";
            $sql .= $limit;
        }
        $this->common->display($sql, 'search');
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
        $this->session->saveState(['search', 'sql', 'setup', 'bookmark', 'list']);
        $this->session->delVar("bookmarkRead");
    }
    /**
     * Get the initial IDs from the database
     */
     private function getInitialIds($searchArray, $searchArrayFT, $type)
     {
		$this->fieldSql($searchArray, $searchArrayFT);
		$subStmt = $this->setSubQuery();
		$resourcesFound = $this->stmt->quicksearchSubQuery(FALSE, $subStmt, FALSE, $type);
		if (!$resourcesFound)
		{
			$this->common->noResources('search');

			return FALSE;
		}
		return TRUE;
    }
    /**
     * parse the search word(s)
     */
    private function parseWord()
    {
        $this->words = $this->parsePhrase->parse($this->input);
        $this->wordsFT = $this->parsePhrase->parse($this->input, FALSE, FALSE, FALSE, TRUE);
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
     * validate user input - method, word and field are required
     *
     * Input comes either from form input or, when paging, from the session.
     *
     * @return array
     */
    private function checkInput()
    {
        $this->writeSession();
        if ((array_key_exists("search_Word", $this->vars) && !trim($this->vars["search_Word"]))
        || !$this->session->getVar("search_Word"))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }

        return $this->session->getArray("search");
    }
    /**
     * Set the subQuery
     *
     */
    private function setSubQuery()
    {
        $this->db->ascDesc = $this->session->getVar("search_AscDesc");
        switch ($this->session->getVar("search_Order"))
        {
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
