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
 *	SEARCH class
 *
 *	Search database
 */
class SEARCH
{
    private $db;
    private $vars;
    private $stmt;
    private $errors;
    private $success;
    private $messages;
    private $common;
    private $session;
    private $keyword;
    private $type;
    private $category;
    private $creator;
    private $userTag;
    private $publisher;
    private $collection;
    private $tag;
    private $user;
    private $input = [];
    private $badInput;
    private $parsePhrase;
    private $languages = [];
    private $commonBib;
    private $metadata;
    private $displayUserTags = FALSE;
    private $displayQCs = FALSE;
    private $displayPCs = FALSE;
    private $displayMusings = FALSE;
    private $displayIdeas = FALSE;
    private $displayMKs = FALSE;
    private $dbFields;
    private $unionFragments = [];
    private $ideas = [];
    private $matchIds = [];
    private $partials = [];
    private $attachmentSearches = [];
    private $validSearch = TRUE;
    private $lastUnionResourceId;
    private $unionResourceIds = [];
    private $subQ;
    private $backupPT;
    private $browserTabID = FALSE;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $this->common->quickSearch = FALSE;
        $this->session = FACTORY_SESSION::getInstance();
        $this->type = FACTORY_TYPE::getInstance();
        $this->category = FACTORY_CATEGORY::getInstance();
        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->userTag = FACTORY_USERTAGS::getInstance();
        $this->creator = FACTORY_CREATOR::getInstance();
        $this->publisher = FACTORY_PUBLISHER::getInstance();
        $this->collection = FACTORY_COLLECTION::getInstance();
        $this->user = FACTORY_USER::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "miscellaneous", "TAG.php"]));
        $this->tag = new TAG();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->parsePhrase = FACTORY_PARSEPHRASE::getInstance();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->metadata = FACTORY_METADATA::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
        if ($this->browserTabID)
        {
            // 1. Load any pre-existing search data into GLOBALS $tempStorage
            // 2. Store in and extract data from $tempStorage
            // 3. Finally, put back $tempStorage into temp_storage using $this->common->updateTempStorage();
            GLOBALS::initTempStorage($this->db, $this->browserTabID);
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
        $this->session->setVar("sql_LastOrder", $this->session->getVar("search_Order"));
        if ($this->browserTabID) {
            GLOBALS::setTempStorage(['sql_LastOrder' => $this->session->getVar("search_Order")]);
        }
    }
    /**
     * display form options
     *
     * @param mixed $error
     * @param false|string $returnString
     *
     * @return string
     */
    public function init($error = FALSE, $returnString = FALSE)
    {
        ///First check, do we have resources?
        if (!$this->common->resourcesExist())
        {
            return;
        }
        if ($this->browserTabID) {
        	$this->input = GLOBALS::getTempStorage('advancedSearch');
        	if (!is_array($this->input)) {
        		$this->input = [];
        	}
        } else {
	        $this->input = $this->session->getArray('advancedSearch');
	    }
        $this->checkAvailableFields();
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('search'));
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
        $this->session->setVar("advancedSearch_elementIndex", 1);
        if ($this->browserTabID) {
            GLOBALS::setTempStorage(['advancedSearch_elementIndex' => 1]);
        }

        $pString = '';
        $pString .= $error;
        $pString .= \HTML\p(\FORM\formHeader("list_SEARCH_CORE") . 
        	\FORM\hidden("method", "reset") . 
        	\FORM\hidden("browserTabID", $this->browserTabID) . 
        	\FORM\formSubmit($this->messages->text("submit", "Reset")) . 
        	\FORM\formEnd());
        $pString .= \FORM\formHeader("list_SEARCH_CORE", "onsubmit=\"selectAll();return true;\"");
        $pString .= \FORM\hidden("method", "process");
        $pString .= \FORM\hidden("browserTabID", $this->browserTabID);
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($this->firstDisplay());
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        $updateJSElementIndex = FALSE;
        if ((!WIKINDX_METADATA_ALLOW))
        {
            if ((WIKINDX_METADATA_USERONLY) && $this->session->getVar("setup_UserId"))
            {
                $wordFields = ['title', 'note', 'abstract', 'quote', 'quoteComment', 'paraphrase', 'paraphraseComment', 'musing', 'idea'];
            }
            else
            {
                $wordFields = ['title', 'note', 'abstract'];
            }
        }
        else
        {
            $wordFields = ['title', 'note', 'abstract', 'quote', 'quoteComment', 'paraphrase', 'paraphraseComment', 'musing', 'idea'];
        }
        if ((!WIKINDX_FILE_VIEW_LOGGEDON_ONLY || $this->session->getVar("setup_UserId")) &&
            ($this->db->tableIsEmpty('resource_attachments') == 0))
        { // 0 means table is NOT empty
            array_splice($wordFields, 3, 0, "attachments");
        }
        $subQ = $this->db->subQuery($this->db->selectNoExecute('resource_custom', 'resourcecustomCustomId'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('customId') . $this->db->inClause($subQ));
        $recordset = $this->db->select('custom', ['customId', 'customLabel', 'customSize']);

        while ($row = $this->db->fetchRow($recordset))
        {
            if ($row['customSize'] == 'S')
            {
                $wordFields[] = 'Custom_S_' . $row['customId'];
            }
            else
            {
                $wordFields[] = 'Custom_L_' . $row['customId'];
            }
        }

        // A possibility to add up to 50 search fields should be enough...
        for ($i = 2; $i < 51; $i++)
        {
            if (array_key_exists("Field_$i", $this->input))
            {
                $noWords = FALSE;

                foreach ($wordFields as $value)
                {
                    if ($value == $this->input["Field_$i"])
                    {
                        if (!array_key_exists("Word_$i", $this->input))
                        {
                            $noWords = TRUE;
                        }

                        break;
                    }
                }

                if ($noWords)
                {
                    $pString .= \HTML\div("searchElement_$i", '');

                    continue;
                }

                if (in_array($this->input["Field_$i"], ['type', 'category', 'subcategory', 'creator', 'keyword', 'metaKeyword', 'userTag', 'language', 'publisher', 'collection', 'tag', 'addedBy', 'editedBy'])
                    && !array_key_exists("Select_$i", $this->input))
                {
                    $pString .= \HTML\div("searchElement_$i", '');

                    continue;
                }
                elseif (in_array($this->input["Field_$i"], ['publicationYear', 'access', 'maturityIndex'])
                    && !array_key_exists("Value1_$i", $this->input))
                {
                    $pString .= \HTML\div("searchElement_$i", '');

                    continue;
                }

                $div = \HTML\tableStart();
                $div .= \HTML\trStart();
                $div .= \HTML\td($this->addRemoveIcon($i, FALSE)); // add remove element icon
                if ($this->input["Field_$i"] == 'idea')
                {
                    $buttons = \HTML\span('OR', 'small') . \FORM\hidden("advancedSearch_Button1_$i", 'OR');
                }
                else
                {
                    $buttons = $this->makeRadioButtons1("advancedSearch_Button1_$i");
                }
                $div .= \HTML\td(\HTML\div("searchElementButtons_$i", $buttons), 'left width5percent');
                $fields = $this->searchFields($i);
                $div .= \HTML\td($fields, 'left width15percent');
                $div .= \HTML\td(\HTML\div("searchElementContainer_$i", $this->createDivs($this->input["Field_$i"], $i)));
                $div .= \HTML\trEnd();
                $div .= \HTML\tableEnd();
                $pString .= \HTML\p(\HTML\div("searchElement_$i", $div));
                $this->session->setVar("advancedSearch_elementIndex", $i);
				if ($this->browserTabID) {
					GLOBALS::setTempStorage(['advancedSearch_elementIndex' => $i]);
				}
                $updateJSElementIndex = TRUE;
            }
            else
            {
                $pString .= \HTML\div("searchElement_$i", '');
            }
        }
		if (!$elementIndex = GLOBALS::getTempStorage('advancedSearch_elementIndex')) {
			$this->session->getVar("advancedSearch_elementIndex");
		}
        $pString .= \HTML\p(\HTML\div(
            'searchElement_addIcon',
            $this->addRemoveIcon($elementIndex, TRUE, $updateJSElementIndex)
        ));
        $pString .= \HTML\p($this->options());

        $pString .= \FORM\formEnd();

        if ($returnString)
        {
            return $pString; // cf FRONT.php or process() below.
        }
        else
        {
            GLOBALS::addTplVar('content', $pString);
        }

        // Load at end because .js initialization needs various DIVs to be in the page before they are made invisible
        \AJAX\loadJavascript(WIKINDX_URL_BASE . '/core/modules/list/searchSelect.js?ver=' . WIKINDX_PUBLIC_VERSION);
    }
    /**
     * Reset the form and clear the session
     */
    public function reset()
    {
        $this->session->clearArray('advancedSearch');
		if ($this->browserTabID) {
			GLOBALS::unsetTempStorage(['advancedSearch']);
		}
        $this->init();
    }
    /**
     * Test search by displaying natural language search
     *
     * @param mixed $search
     * @param array $input
     * @param array $bibIdArray
     * @param array $optionsArray
     */
    public function test($search = FALSE, $input = [], $bibIdArray = [], $optionsArray = [])
    {
        $testArray = $bibIdArray = $optionsArray = $array = $tempIdeas = $ideas = [];
        if ($search)
        { // Doing an actual search rather than test
            $longSpace = '    ';
            $shortSpace = '  ';
            $newLine = CR . LF;
            $array = $input;
            foreach ($input as $key1 => $inputArray)
            {
                foreach ($inputArray as $key2 => $value)
                {
                    if ($key2 == 'OriginalField')
                    {
                        $array[$key1]['Field'] = $value;
                    }
                    elseif ($key2 == 'Select')
                    {
                        $array[$key1]['Select'] = implode(',', $value);
                    }
                }
            }
        }
        else
        {
            $longSpace = '&nbsp;&nbsp;&nbsp;&nbsp;';
            $shortSpace = '&nbsp;&nbsp;';
            $newLine = BR;
            $vars = GLOBALS::getVars();
            $jArray = \AJAX\decode_jString($vars['ajaxReturn']);
            if (array_key_exists('advancedSearch_BibId', $jArray['elementFields']))
            {
                $bibIdArray = \UTF8\mb_explode(',', $jArray['elementFields']['advancedSearch_BibId']);
                $optionsArray = \UTF8\mb_explode(',', $jArray['elementFields']['advancedSearch_Options']);
            }
            unset($jArray['elementFields']['advancedSearch_BibId']);
            unset($jArray['elementFields']['advancedSearch_Options']);
            foreach ($jArray['elementFields'] as $key => $value)
            {
                $split = \UTF8\mb_explode('_', $key);
                $array[$split[2]][$split[1]] = $value;
            }
        }
        $arrayString = \HTML\em($this->messages->text("search", "naturalLanguage")) . $newLine;
        $temp = $bibIdArray;
        foreach ($temp as $key => $value)
        {
            if (!trim($value))
            {
                unset($bibIdArray[$key]);
            }
        }
        $temp = $optionsArray;
        foreach ($temp as $key => $value)
        {
            if (!trim($value))
            {
                unset($optionsArray[$key]);
            }
        }
        $tempArray = $array;
        foreach ($array as $key1 => $value1)
        {
            foreach ($value1 as $key2 => $value2)
            {
                if (($key2 == 'Field') && ($value2 == 'idea'))
                {
                    $this->parsePhrase->idea = TRUE;
                    if (($word = $this->parsePhrase->parse($value1, TRUE)) && array_key_exists('Word', $array[$key1]))
                    {
                        $tempIdeas[$key1]['String'] = $word;
                    }
                    else
                    {
                        unset($tempArray[$key1]);

                        continue;
                    }
                    $tempIdeas[$key1]['Field'] = 'idea';
                    $tempIdeas[$key1]['Button1'] = 'OR';
                    unset($tempArray[$key1]);
                }
            }
        }
        $this->parsePhrase->idea = FALSE;
        $array = $tempArray;
        $ideas = $tempIdeas;
        foreach ($tempIdeas as $key => $value)
        {
            $ideas[$key] = '(' . str_replace('!WIKINDXFIELDWIKINDX!', \HTML\color($value['Field'], 'greenText'), $value['String']) . ')';
        }
        foreach ($array as $key1 => $value1)
        {
            foreach ($value1 as $key2 => $value2)
            {
                if ($key2 == 'Word')
                {
                    if ($word = $this->parsePhrase->parse($value1, TRUE))
                    {
                        $testArray[$key1]['String'] = $word;
                    }
                }
                elseif ($key2 == 'Select')
                {
                    $testArray[$key1]['Select'] = \UTF8\mb_explode(',', $value2);
                }
                elseif ($key2 == 'Field')
                {
                    $split = \UTF8\mb_explode('_', $value2);
                    if (count($split) == 3)
                    {
                        if (mb_strpos($split[0], 'Custom') !== FALSE)
                        {
                            $this->db->formatConditions(['customId' => $split[2]]);
                            $customName = $this->db->selectFirstField('custom', 'customLabel');
                            $testArray[$key1]['Field'] = \HTML\nlToHtml($customName);
                        }
                    }
                    else
                    {
                        $testArray[$key1]['Field'] = $value2;
                    }
                }
                elseif ($key2 == 'Button1')
                {
                    $testArray[$key1]['Button1'] = $value2;
                }
                elseif ($key2 == 'Button2')
                {
                    $testArray[$key1]['Button2'] = $value2;
                }
                elseif ($key2 == 'Comparison')
                {
                    $testArray[$key1]['Comparison'] = $value2;
                }
                elseif ($key2 == 'Value1')
                {
                    $testArray[$key1]['Value1'] = trim($value2);
                }
                elseif ($key2 == 'Value2')
                {
                    $testArray[$key1]['Value2'] = trim($value2);
                }
            }
        }
        if (!empty($testArray) &&
            !array_key_exists('String', $testArray[$key1]) &&
            !array_key_exists('Select', $testArray[$key1]) &&
            !array_key_exists('Comparison', $testArray[$key1])
            ) {
            unset($testArray[$key1]);
        }
        elseif (!empty($testArray) && array_key_exists('Comparison', $testArray[$key1]))
        {
            if ($testArray[$key1]['Comparison'] == 6)
            { // i.e. <...<
                if (!array_key_exists('Value2', $testArray[$key1]))
                {
                    unset($testArray[$key1]);
                }
                elseif (!is_numeric($testArray[$key1]['Value1']) || !is_numeric($testArray[$key1]['Value2']))
                {
                    unset($testArray[$key1]);
                }
                elseif (!$testArray[$key1]['Value1'] || !$testArray[$key1]['Value2'])
                {
                    unset($testArray[$key1]);
                }
                elseif ($testArray[$key1]['Value1'] >= $testArray[$key1]['Value2'])
                {
                    unset($testArray[$key1]);
                }
            }
            elseif (!array_key_exists('Value1', $testArray[$key1]) || !$testArray[$key1]['Value1'] || !is_numeric($testArray[$key1]['Value1']))
            {
                unset($testArray[$key1]);
            }
        }
        $temp = $buttons = $final = [];
        foreach ($testArray as $index => $value)
        {
            if (array_key_exists('Field', $value) && (count($value) == 1))
            {
                continue;
            }
            if (array_key_exists('String', $value))
            {
                $temp[$index] = '(' . str_replace('!WIKINDXFIELDWIKINDX!', \HTML\color($value['Field'], 'greenText'), $value['String']) . ')';
            }
            elseif (array_key_exists('Select', $value))
            {
                if (!array_key_exists('Button2', $value))
                { // e.g. publisher, collection etc. where each resource can have only one
                    $value['Button2'] = 'OR';
                }
                if (array_key_exists(0, $value['Select']) && !$value['Select'][0])
                {
                    unset($value['Select'][0]);
                }
                if (empty($value['Select']))
                {
                    continue;
                }
                $this->formatElement($value);
                $value['Select'] = array_map([$this, "colorID"], $value['Select']);
                $sizeof = count($value['Select']);
                if ($sizeof > 1)
                {
                    if ($value['Button2'] == 'OR')
                    {
                        $temp[$index] = '(' . \HTML\color($value['Field'], 'greenText') .
                            ' IS ' . implode(' ' . $value['Button2'] . ' ', $value['Select']) . ')';
                    }
                    else
                    { // AND
                        $temp[$index] = '(' . \HTML\color($value['Field'], 'greenText') .
                            ' INCLUDES ' . implode(' ' . $value['Button2'] . ' ', $value['Select']) . ')';
                    }
                }
                else
                {
                    if ($shift = array_shift($value['Select']))
                    {
                        $temp[$index] = '(' . \HTML\color($value['Field'], 'greenText') . ' IS ' . $shift . ')';
                    }
                }
            }
            elseif (array_key_exists('Comparison', $value))
            {
                if (!array_key_exists('Value1', $value))
                {
                    continue;
                }
                if ($value['Comparison'] == 0)
                {
                    $temp[$index] = '(' . \HTML\color($value['Field'], 'greenText') . ' IS EQUAL TO ' .
                        \HTML\color($value['Value1'], 'redText') . ')';
                }
                elseif ($value['Comparison'] == 1)
                {
                    $temp[$index] = '(' . \HTML\color($value['Field'], 'greenText') . ' IS NOT EQUAL TO ' .
                        \HTML\color($value['Value1'], 'redText') . ')';
                }
                elseif ($value['Comparison'] == 2)
                {
                    $temp[$index] = '(' . \HTML\color($value['Field'], 'greenText') . ' IS LESS THAN ' .
                        \HTML\color($value['Value1'], 'redText') . ')';
                }
                elseif ($value['Comparison'] == 3)
                {
                    $temp[$index] = '(' . \HTML\color($value['Field'], 'greenText') . ' IS GREATER THAN ' .
                        \HTML\color($value['Value1'], 'redText') . ')';
                }
                if ($value['Comparison'] == 4)
                {
                    $temp[$index] = '(' . \HTML\color($value['Field'], 'greenText') . ' IS LESS THAN OR EQUAL TO ' .
                        \HTML\color($value['Value1'], 'redText') . ')';
                }
                elseif ($value['Comparison'] == 5)
                {
                    $temp[$index] = '(' . \HTML\color($value['Field'], 'greenText') . ' IS GREATER THAN OR EQUAL TO ' .
                        \HTML\color($value['Value1'], 'redText') . ')';
                }
                elseif ($value['Comparison'] == 6)
                {
                    if (!array_key_exists('Value2', $value))
                    {
                        continue;
                    }
                    $temp[$index] = '(' . \HTML\color($value['Field'], 'greenText') . ' IS MORE THAN ' .
                        \HTML\color($value['Value1'], 'redText') . ' AND LESS THAN ' .
                        \HTML\color($value['Value2'], 'redText') . ')';
                }
            }
            else
            {
                continue;
            }
            $buttons[$index] = array_key_exists('Button1', $value) ? $value['Button1'] : NULL;
        }
        $error = TRUE;
        if (!empty($temp))
        {
            ksort($temp);
            ksort($buttons);
            // Each time an element begins with 'OR', close and begin new parentheses and use newlines if it is not the first element
            $count = 0;
            foreach ($buttons as $index => $button)
            {
                if (!$count)
                { // First element
                    $final[$index] = '(' . $newLine . $longSpace . array_shift($temp);
                }
                elseif ($button == 'OR')
                {
                    $final[$index] = $newLine . ')' . $newLine . 'OR' . $newLine . '(' . $newLine .
                    $longSpace . array_shift($temp);
                }
                elseif ($button == 'AND')
                {
                    $final[$index] = $newLine . $shortSpace . 'AND' . $newLine . $longSpace . array_shift($temp);
                }
                elseif ($button == 'NOT')
                {
                    $final[$index] = $newLine . $shortSpace . 'NOT' . $newLine . $longSpace . array_shift($temp);
                }
                $count++;
            }
            $final[$index] .= $newLine . ')';
            // Now add any bibliography etc. options
            foreach ($optionsArray as $value)
            {
                if (($value == 'ignore') || ($value == 'displayOnlyAttachment') || ($value == 'zipAttachment'))
                {
                    continue;
                }
                $final[++$index] = $newLine . 'AND ' . \HTML\color($value, 'greenText');
            }
            foreach ($bibIdArray as $value)
            {
                if (!$value)
                {
                    continue;
                }
                $this->db->formatConditions(['userbibliographyId' => $value]);
                $row = $this->db->selectFirstRow('user_bibliography', 'userbibliographyTitle');
                $final[++$index] = $newLine . 'AND NOT IN ' .
                    \HTML\color(\HTML\nlToHtml($row['userbibliographyTitle']), 'greenText') . ' bibliography';
            }
            $arrayString .= implode(' ', $final);
            if ($search)
            {
                if (!empty($ideas))
                {
                    if (!empty($final))
                    {
                        $or = $newLine . 'OR';
                    }
                    else
                    {
                        $or = FALSE;
                    }
                    while ($ideas)
                    {
                        $finalIdeas[] = $or . $newLine . ' (' . $newLine . $longSpace . array_shift($ideas) . $newLine . ')';
                    }
                    $arrayString .= implode(' ', $finalIdeas);
                }
                $this->session->setVar("advancedSearch_listParams", $arrayString);

                return;
            }
            $div = \HTML\p($arrayString);
            $error = FALSE;
        }
        if (!empty($ideas))
        {
            if (!empty($final))
            {
                $or = $newLine . 'OR';
            }
            else
            {
                $or = FALSE;
            }
            while ($ideas)
            {
                $finalIdeas[] = $or . $newLine . ' (' . $newLine . $longSpace . array_shift($ideas) . $newLine . ')';
            }
            $arrayString .= implode(' ', $finalIdeas);
            if ($search)
            {
                $this->session->setVar("advancedSearch_listParams", $arrayString);

                return;
            }
            $div = \HTML\p($arrayString);
            $error = FALSE;
        }
        if ($error && !empty($this->attachmentSearches))
        {
            return;
        }
        elseif ($error)
        {
            $this->testError();
        }
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            if (!isset($div))
            {
                $div = '';
            }
            $jsonResponseArray = ['innerHTML' => "$div"];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * add form element
     */
    public function addElement()
    {
        $vars = GLOBALS::getVars();
        $this->checkAvailableFields();
        $jArray = \AJAX\decode_jString($vars['ajaxReturn']);
        $div = \HTML\p($this->wordSearch($jArray['elementIndex'], TRUE));
        $this->session->setVar("advancedSearch_elementIndex", $jArray['elementIndex']);
		if ($this->browserTabID) {
			GLOBALS::setTempStorage(['advancedSearch_elementIndex' => $jArray['elementIndex']]);
		}
        $jsonResponseArray = [
            'innerHTML' => "$div",
        ];
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * remove form element
     */
    public function removeElement()
    {
        $vars = GLOBALS::getVars();
        $jArray = \AJAX\decode_jString($vars['ajaxReturn']);
        $this->session->delVar("advancedSearch_Field_" . $jArray['elementIndex']);
        $this->session->delVar("advancedSearch_Word_" . $jArray['elementIndex']);
        $this->session->delVar("advancedSearch_Partial_" . $jArray['elementIndex']);
        $this->session->delVar("advancedSearch_Button1_" . $jArray['elementIndex']);
        $this->session->delVar("advancedSearch_Button2_" . $jArray['elementIndex']);
        $this->session->delVar("advancedSearch_Select_" . $jArray['elementIndex']);
        $this->session->delVar("advancedSearch_Comparison_" . $jArray['elementIndex']);
        $this->session->delVar("advancedSearch_Value1_" . $jArray['elementIndex']);
        $this->session->delVar("advancedSearch_Value2_" . $jArray['elementIndex']);
		if ($this->browserTabID) {
			GLOBALS::unsetTempStorage([
				"advancedSearch_Field_" . $jArray['elementIndex'], 
				"advancedSearch_Word_" . $jArray['elementIndex'],
				"advancedSearch_Partial_" . $jArray['elementIndex'],
				"advancedSearch_Button1_" . $jArray['elementIndex'],
				"advancedSearch_Button2_" . $jArray['elementIndex'],
				"advancedSearch_Select_" . $jArray['elementIndex'],
				"advancedSearch_Comparison_" . $jArray['elementIndex'],
				"advancedSearch_Value1_" . $jArray['elementIndex'],
				"advancedSearch_Value2_" . $jArray['elementIndex']
				]);
		}
        $div = ' ';
        $jsonResponseArray = [
            'innerHTML' => $div,
        ];
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Add a new text input for a second value for comparison
     */
    public function addComparisonValue()
    {
        $vars = GLOBALS::getVars();
        $jArray = \AJAX\decode_jString($vars['ajaxReturn']);
        $index = $jArray['elementIndex'];
        if ($jArray['execute'] === FALSE)
        { // set to TRUE in searchSelect.js addComparisonValue() if option 6 of select box is selected
            $div = ' ';
        }
        else
        {
            $div = \FORM\textInput(FALSE, "advancedSearch_Value2_$index");
        }
        $jsonResponseArray = [
            'innerHTML' => "$div",
        ];
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Make the transfer arrows to transfer fields between select boxes with onclick
     *
     * @param mixed $source
     * @param mixed $target
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrows($source, $target)
    {
        $jsonArrayS = $jsonArrayD = [];
        $jsonArrayS[] = [
            'startFunction' => 'search_Transfer',
            'source' => "$source",
            'target' => "$target",
        ];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArrayS);
        $jsonArrayD[] = [
            'startFunction' => 'search_Transfer',
            'source' => "$target",
            'target' => "$source",
        ];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArrayD);

        return [$toRightImage, $toLeftImage];
    }
    /**
     * Change OR/AND buttons for fields to unselectable OR if field == idea
     */
    public function switchButtons()
    {
        $vars = GLOBALS::getVars();
        $jArray = \AJAX\decode_jString($vars['ajaxReturn']);
        if ($jArray['elementIndex'] == 1)
        {
            FACTORY_CLOSERAW::getInstance();
            die;
        }
        $i = $jArray['elementIndex'];
        if ($jArray['field'] == 'idea')
        {
            $div = \HTML\span('OR', 'small') . \FORM\hidden("advancedSearch_Button1_$i", 'OR');
        }
        else
        {
            $div = $this->makeRadioButtons1("advancedSearch_Button1_$i");
        }
        $jsonResponseArray = [
            'innerHTML' => "$div",
        ];
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Switch container fields depending on type of parameter selected in first field select box
     */
    public function switchField()
    {
        $vars = GLOBALS::getVars();
        $jArray = \AJAX\decode_jString($vars['ajaxReturn']);
        $div = $this->createDivs($jArray['field'], $jArray['elementIndex']);
        $jsonResponseArray = [
            'innerHTML' => "$div",
        ];
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * For re-ordering or paging
     */
    public function reprocess()
    {
    	$message = FALSE;
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        if ($message) {
            GLOBALS::addTplVar('content', $message);
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
        if (array_key_exists('ideasFound', $this->vars))
        {
            if ($this->vars['ideasFound'])
            {
                $this->common->ideasFound = TRUE;
            }
            else
            {
                $this->common->ideasFound = FALSE;
            }
        }
        if (array_key_exists('patterns', $this->vars))
        {
            $this->common->patterns = unserialize(base64_decode($this->vars['patterns']));
        }
		if ($this->browserTabID) {
			$this->input = GLOBALS::getTempStorage('advancedSearch');
			if (!is_array($this->input)) {
				$this->input = [];
			}
		} else {
	        $this->input = $this->session->getArray("advancedSearch");
	    }
        if (array_key_exists("search_Order", $this->vars) && $this->vars["search_Order"])
        {
            $this->input['Order'] = $this->vars["search_Order"];
            $this->session->setVar("search_Order", $this->input['Order']);
            $this->session->setVar("advancedSearch_Order", $this->input['Order']);
            $this->session->setVar("sql_LastOrder", $this->input['Order']);
            $this->session->setVar("search_AscDesc", $this->vars['search_AscDesc']);
            $this->session->setVar("advancedSearch_AscDesc", $this->vars['search_AscDesc']);
			if ($this->browserTabID) {
				GLOBALS::setTempStorage([
					"search_Order" => $this->input['Order'],
					"advancedSearch_Order" => $this->input['Order'],
					"sql_LastOrder" => $this->input['Order'],
					"search_AscDesc" => $this->vars['search_AscDesc'],
					"advancedSearch_AscDesc" => $this->vars['search_AscDesc']
					]);
			}
        }
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'displayIdeas'))
        {
            // Store paging total for resources and restore after calculating paging for ideas
            // only (so paging for resources is correctly displayed with lastmulti
            if (!$this->backupPT = GLOBALS::getTempStorage('setup_PagingTotal')) {
	            $this->backupPT = $this->session->getVar("setup_PagingTotal");
	        }
        }
        $this->process(TRUE);
    }
    /**
     * process
     *
     * @param bool $reprocess
     */
    public function process($reprocess = FALSE)
    {
        if (!array_key_exists('PagingStart', $this->vars))
        {
            if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'displayIdeas'))
            {
                // Store paging total for resources and restore after calculating paging for ideas
                // only (so paging for resources is correctly displayed with lastmulti
                if (!$this->backupPT = GLOBALS::getTempStorage('setup_PagingTotal')) {
	            	$this->backupPT = $this->session->getVar("setup_PagingTotal");
	        	}
                $this->session->delVar("setup_PagingTotal");
				if ($this->browserTabID) {
					GLOBALS::unsetTempStorage(['setup_PagingTotal']);
				}
            }
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "search"));
        if (!$reprocess)
        {
            $this->session->delVar("list_AllIds");
            $this->session->delVar("list_PagingAlphaLinks");
            if ($this->browserTabID) {
				GLOBALS::unsetTempStorage(['list_AllIds', 'list_PagingAlphaLinks']);
			}
        }
        if (!$reprocess || (GLOBALS::getUserVar('PagingStyle') == 'A'))
        {
            $this->session->delVar("sql_ListStmt");
            $this->session->delVar("advancedSearch_listParams");
            if ($this->browserTabID) {
				GLOBALS::unsetTempStorage(['sql_ListStmt', 'advancedSearch_listParams']);
			}
        }
        $this->session->delVar("search_Highlight");
        $this->session->delVar("search_HighlightIdea");
		if ($this->browserTabID) {
			GLOBALS::unsetTempStorage(['search_Highlight', 'search_HighlightIdea']);
		}
        $this->stmt->listMethodAscDesc = 'advancedSearch_AscDesc';
        $this->stmt->listType = 'search';
        $queryString = 'action=list_SEARCH_CORE&method=reprocess';
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'lastMulti') && (GLOBALS::getUserVar('PagingStyle') != 'A'))
        {
            $this->session->delVar("mywikindx_PagingStart");
			if ($this->browserTabID) {
				GLOBALS::unsetTempStorage(['mywikindx_PagingStart']);
			}
            $this->pagingObject = FACTORY_PAGING::getInstance();
            $this->pagingObject->queryString = $queryString;
            $this->pagingObject->getPaging();
            $this->common->pagingObject = $this->pagingObject;
            if (GLOBALS::getTempStorage('sql_LastIdeaSearch')) {
            	$this->common->ideasFound = TRUE;
            }
            else if ($this->session->getVar('sql_LastIdeaSearch')) {
                $this->common->ideasFound = TRUE;
            }
            else {
            	$this->common->ideasFound = FALSE;
            }
            $this->common->lastMulti('search');

            return;
        }
        if (!$reprocess)
        {
            $this->checkInput();
        }
        $this->populateDbFields();
        $array1 = $array2 = [];
        $options = unserialize(base64_decode($this->input['Options']));
        foreach ($this->input as $key => $value)
        {
            $split = \UTF8\mb_explode('_', $key);
            if (count($split) != 2)
            {
                continue;
            }
            if ($split[0] == 'Field')
            {
/*                if (($value == 'attachments'))
                {
                    $field = 'Word_' . $split[1];
                    $this->attachmentSearches[$split[1]]['Word'] = $this->input[$field];
                    $this->attachmentSearches[$split[1]]['Field'] = 'attachment';
                    $this->attachmentSearches[$split[1]]['OriginalField'] = 'attachment';
                    $field = 'Partial_' . $split[1];
                    if (array_key_exists($field, $this->input))
                    {
                        $this->attachmentSearches[$split[1]]['Partial'] = $this->input[$field];
                    }
                    $field = 'Button1_' . $split[1];
                    if (array_key_exists($field, $this->input))
                    {
                        $this->attachmentSearches[$split[1]]['Button'] = $this->attachmentSearches[$split[1]]['Button1'] = $this->input[$field];
                    }

                    continue;
                }
*/                $array1[$split[1]]['Field'] = $this->dbFields[$value][0];
                $array1[$split[1]]['OriginalField'] = $value;
                if (mb_strpos($value, 'Custom_') === 0)
                {
                    $array1[$split[1]]['Custom'] = TRUE;
                }
            }
            else
            {
                $array1[$split[1]][$split[0]] = $value;
            }
        }
        foreach ($this->attachmentSearches as $key => $value)
        {
            if (array_key_exists($key, $array1))
            {
                unset($array1[$key]);
            }
        }
        // do attachment searches
        if (!empty($this->attachmentSearches) && (array_search('noAttachment', $options) === FALSE))
        {
            list($this->matchIds, $this->excludeIds) = $this->searchAttachments();
        }
        foreach ($array1 as $key1 => $value1)
        {
            foreach ($value1 as $key2 => $value2)
            {
                if ($key2 == 'Word')
                {
                    // some words (cf admin|configure menu) are filtered so this function may return FALSE if there is nothing to search on
                    if ($array2[$key1]['OriginalField'] == 'idea')
                    {
                        $this->parsePhrase->idea = TRUE;
                    }
                    else
                    {
                        $this->parsePhrase->idea = FALSE;
                    }
                    // Check for FULLTEXT search fields
                    if ($this->parsePhrase->idea
                        ||
                        ($value1['OriginalField'] == 'note')
                        ||
                        ($value1['OriginalField'] == 'abstract')
                        ||
                        ($value1['Field'] == 'resourcecustomLong')
                        ||
                        ($value1['Field'] == 'resourcemetadataText')
                        ||
                        ($value1['Field'] == 'resourceattachmentsText')
                    ) {
                        if (($word = $this->parsePhrase->parse($value1, FALSE, FALSE, FALSE, TRUE)) && $this->parsePhrase->validSearch)
                        {
                            $array2[$key1]['String'] = $word;
                        }
                        else
                        {
                            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
                        }
                    }
                    elseif (($word = $this->parsePhrase->parse($value1)) && $this->parsePhrase->validSearch)
                    {
                        $array2[$key1]['String'] = $word;
                    }
                    else
                    {
                        $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
                    }
                    $array2[$key1]['Word'] = $value2;
                }
                elseif ($key2 == 'Select')
                {
                    $array2[$key1]['Select'] = unserialize(base64_decode($value2));
                }
                elseif ($key2 == 'OriginalField')
                {
                    $array2[$key1]['OriginalField'] = $value2;
                }
                elseif ($key2 == 'Field')
                {
                    $array2[$key1]['Field'] = $value2;
                    if ((mb_strpos($value2, 'Custom') !== FALSE) && (!array_key_exists($value2, $this->dbFields)))
                    {
                        $this->dbFields[$value2] = ['resourcecustomCustomId', 'resource_custom', 'resourcecustomResourceId'];
                    }
                }
                elseif ($key2 == 'Button1')
                {
                    $array2[$key1]['Button1'] = $value2;
                }
                elseif ($key2 == 'Button2')
                {
                    $array2[$key1]['Button2'] = $value2;
                }
                elseif ($key2 == 'Comparison')
                {
                    $array2[$key1]['Comparison'] = $value2;
                }
                elseif ($key2 == 'Value1')
                {
                    $array2[$key1]['Value1'] = trim($value2);
                }
                elseif ($key2 == 'Value2')
                {
                    $array2[$key1]['Value2'] = trim($value2);
                }
                elseif ($key2 == 'Custom')
                {
                    $array2[$key1]['Custom'] = $value2;
                }
            }
            if (!array_key_exists('String', $array2[$key1]) &&
                !array_key_exists('Select', $array2[$key1]) &&
                !array_key_exists('Comparison', $array2[$key1])
                ) {
                unset($array2[$key1]);
            }
            elseif (array_key_exists('Comparison', $array2[$key1]))
            {
                if ($array2[$key1]['Comparison'] == 6)
                { // i.e. <...<
                    if (!array_key_exists('Value2', $array2[$key1]))
                    {
                        unset($array2[$key1]);
                    }
                    elseif (!is_numeric($array2[$key1]['Value1']) || !is_numeric($array2[$key1]['Value2']))
                    {
                        unset($array2[$key1]);
                    }
                    elseif (!$array2[$key1]['Value1'] || !$array2[$key1]['Value2'])
                    {
                        unset($array2[$key1]);
                    }
                    elseif ($array2[$key1]['Value1'] >= $array2[$key1]['Value2'])
                    {
                        unset($array2[$key1]);
                    }
                }
                elseif (!$array2[$key1]['Value1'] || !is_numeric($array2[$key1]['Value1']))
                {
                    unset($array2[$key1]);
                }
            }
        }
        if (empty($array2) && empty($this->attachmentSearches))
        {
            GLOBALS::setTplVar('resourceListSearchForm', FALSE);
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
        }
        foreach ($array2 as $key3 => $value3)
        {
            // not for FULTTEXT fields
            if (($value3['Field'] != 'resourcetextNote') && ($value3['Field'] != 'resourcetextAbstract'))
            {
                if (array_key_exists('String', $value3))
                {
                    if ($value3['Field'] == 'resourceTitleSort')
                    {
						$replace = $this->db->replace($this->db->replace(
								$this->db->concat([
								$this->db->formatFields('resourceNoSort'), 
								$this->db->formatFields('resourceTitle'),
								$this->db->formatFields('resourceSubtitle')
								], ' '), 
							'{', '', FALSE), '}', '', FALSE);
                    }
                    else
                    {
                        $replace = $this->db->formatFields($value3['Field']);
                    }
                    $value3['String'] = str_replace('!WIKINDXFIELDWIKINDX!', $replace, $value3['String']);
                    $array2[$key3] = $value3;
                }
            }
        }
        if ($this->createSqlFragments($array2))
        {
            $sqlDummy = FALSE;
        }
        else
        { // create a dummy SQL query that will return no results in listcommon->display()
            $this->db->formatConditions(['resourceId' => 'IS NULL']);
            $this->db->leftJoin('resource', 'resourceId', 'resourcemiscId');
            $sqlDummy = $this->db->queryNoExecute($this->db->selectNoExecute('resource_misc', ['resourceId', 'resourcemiscId']));
        }
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'displayIdeas'))
        { // from existing list, clicked on 'Ideas have been found'
            $this->unionFragments = [];
        }
        if (!empty($this->ideas) && !empty($this->unionFragments))
        {
            // Check this user is allowed to read the idea.
            foreach ($this->ideas as $valueArray)
            {
                $matchAgainst = $this->db->fulltextSearch($valueArray['Field'], str_replace("'", "''", $valueArray['String']));
                $conditions[] = $matchAgainst;
            }
            $this->metadata->setCondition('i');
            $this->db->formatConditions(implode($this->db->or, $conditions));
            $resultset = $this->db->select('resource_metadata', 'resourcemetadataId');
            if ($this->db->numRows($resultset))
            {
                $this->common->ideasFound = TRUE;
                $this->session->setVar("sql_LastIdeaSearch", "index.php?action=list_SEARCH_CORE&method=reprocess&type=displayIdeas");
                if ($this->browserTabID) {
                	GLOBALS::setTempStorage(['sql_LastIdeaSearch' => 'index.php?action=list_SEARCH_CORE&method=reprocess&type=displayIdeas']);
                }
            }
            else
            {
                $this->session->delVar("sql_LastIdeaSearch");
                if ($this->browserTabID) {
                	GLOBALS::unsetTempStorage(['sql_LastIdeaSearch']);
                }
            }
        }
        elseif (!empty($this->ideas))
        {
            $this->searchIdeas();

            return;
        }
        else
        {
            $this->session->delVar("sql_LastIdeaSearch");
			if ($this->browserTabID) {
				GLOBALS::unsetTempStorage(['sql_LastIdeaSearch']);
			}
        }
        $bibId = FALSE;
        if (!$bibId = GLOBALS::getTempStorage('advancedSearch_BibId')) {
        	$bibId = $this->session->getVar("advancedSearch_BibId");
        }
        if ($bibId) {
            $bibIdArray[] = $bibId;
            $this->stmt->excludeBib($bibId, 'rId');
        }
        else
        {
            $bibIdArray = [];
        }
        foreach ($this->attachmentSearches as $key1 => $value1)
        {
            foreach ($value1 as $key2 => $value2)
            {
                if ($key2 == 'Word')
                {
                    $array2[$key1]['Word'] = $value2;
                }
                elseif ($key2 == 'OriginalField')
                {
                    $array2[$key1]['OriginalField'] = $value2;
                }
                elseif ($key2 == 'Field')
                {
                    $array2[$key1]['Field'] = $value2;
                    if ((mb_strpos($value2, 'Custom') !== FALSE) && (!array_key_exists($value2, $this->dbFields)))
                    {
                        $this->dbFields[$value2] = ['resourcecustomCustomId', 'resource_custom', 'resourcecustomResourceId'];
                    }
                }
                elseif ($key2 == 'Button1')
                {
                    $array2[$key1]['Button1'] = $value2;
                }
            }
        }
        $this->test(TRUE, $array2, $bibIdArray, $options);
        $attach = FALSE;
        /**
         * zipAttachment is dependent upon displayOnlyAttachment which is dependent upon withAttachment
         *
         * Therefore, we can cascade . . .
         */
        $this->session->delVar("search_DisplayAttachment");
        $this->session->delVar("search_DisplayAttachmentZip");
		if ($this->browserTabID) {
			GLOBALS::unsetTempStorage(['search_DisplayAttachment', 'search_DisplayAttachmentZip']);
		}
        if (array_search('noAttachment', $options) !== FALSE)
        {
            $attach = 'noAttachment';
        }
        elseif (array_search('withAttachment', $options) !== FALSE)
        {
            $attach = 'withAttachment';
        }
        $order = (array_search('displayOnlyAttachment', $options) !== FALSE) ? 'attachments' : $this->input['Order'];
        if ($attach)
        {
            if ($order == 'attachments')
            { // displaying attachments only
                $this->session->setVar("search_DisplayAttachment", TRUE);
				if ($this->browserTabID) {
					GLOBALS::setTempStorage(['search_DisplayAttachment' => TRUE]);
				}
                if (array_search('zipAttachment', $options) !== FALSE)
                {
                    $this->session->setVar("search_DisplayAttachmentZip", TRUE);
					if ($this->browserTabID) {
						GLOBALS::setTempStorage(['search_DisplayAttachmentZip' => TRUE]);
					}
                }
            }
        }
        if (!$sqlDummy)
        {
            if (!array_key_exists('order', $this->input) && !array_key_exists('Order', $this->input))
            {
                $this->session->setVar("search_Order", 'creator');
                $this->session->setVar("sql_LastOrder", 'creator');
                $this->session->setVar("search_AscDesc", $this->db->asc);
				if ($this->browserTabID) {
					GLOBALS::setTempStorage(['search_Order' => 'creator', 'sql_LastOrder' => 'creator', 'search_AscDesc' => $this->db->asc]);
				}
            }
            else
            {
                $this->session->setVar("sql_LastOrder", $this->input['Order']);
				if ($this->browserTabID) {
					GLOBALS::setTempStorage(['sql_LastOrder' => $this->input['Order']]);
				}
            }
            // Turn on the 'add bookmark' menu item
            $this->session->setVar("bookmark_DisplayAdd", TRUE);
            $this->session->setVar("search_Order", $order);
			if ($this->browserTabID) {
				GLOBALS::setTempStorage(['sql_Order' => $order]);
			}
            $subStmt = $this->setSubQuery($attach);
            if (!$order = GLOBALS::getTempStorage('search_Order')) {
            	$order = $this->session->getVar("search_Order");
            }
            $resourcesFound = $this->stmt->listSubQuery($order, $queryString, $subStmt, FALSE, $this->subQ);
            if (!$resourcesFound)
            {
                $this->common->noResources('search');

                return;
            }
        }
		if (!$searchTerms = GLOBALS::getTempStorage('search_Highlight')) {
			$searchTerms = \UTF8\mb_explode(",", $this->session->getVar("search_Highlight"));
		}
        foreach ($searchTerms as $term)
        {
            if (trim($term))
            {
                $term = preg_quote($term, '/');
                $patterns[] = "/($term)(?=[^>]*(<|$))/ui";
            }
        }
        if (!isset($patterns))
        {
            $this->session->setVar("search_Patterns", []);
			if ($this->browserTabID) {
				GLOBALS::setTempStorage(['search_Patterns' => []]);
			}
            $this->common->patterns = FALSE;
        }
        else
        {
            $this->session->setVar("search_Patterns", $patterns);
			if ($this->browserTabID) {
				GLOBALS::setTempStorage(['search_Patterns' => []]);
			}
            $this->common->patterns = $patterns;
        }
        $this->common->keepHighlight = TRUE;
        if ($sqlDummy)
        {
            $sql = $sqlDummy;
        }
        else
        {
            if (!$order = GLOBALS::getTempStorage('search_Order')) {
            	$order = $this->session->getVar("search_Order");
            }
            $sql = $this->stmt->listList($order, FALSE, $this->subQ);
        }
        $this->common->display($sql, "search");
        // set the lastMulti session variable for quick return to this process.
        $this->session->setVar("sql_LastMulti", $queryString);
		if ($this->browserTabID) {
			GLOBALS::setTempStorage(['sql_LastMulti' => $queryString]);
		}
        $this->common->updateTempStorage();
    }
    /**
     * Search ideas for search words and display
     */
    public function searchIdeas()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "search"));
        $icons = FACTORY_LOADICONS::getInstance();
        $cite = FACTORY_CITE::getInstance();
        $userObj = FACTORY_USER::getInstance();
        $multiUser = WIKINDX_MULTIUSER;
        $ideaList = [];
        $index = 0;
        // get count statement and set queryString
        $pagingObject = FACTORY_PAGING::getInstance();
        if ((!array_key_exists('PagingStart', $this->vars) || !$this->vars['PagingStart']))
        {
            $this->session->delVar("mywikindx_PagingStart"); // might be set from last multi resource list display
			if ($this->browserTabID) {
				GLOBALS::unsetTempStorage(['mywikindx_PagingStart']);
			}
        }
        $queryString = "index.php?action=list_SEARCH_CORE&method=reprocess&type=displayIdeas";
        // Check this user is allowed to read the idea.
        $this->metadata->setCondition('i');
        foreach ($this->ideas as $valueArray)
        {
            $conditions[] = $this->db->fulltextSearch($valueArray['Field'], str_replace("'", "''", $valueArray['String']));
        }
        $this->db->formatConditions(implode($this->db->or, $conditions));
        $countQuery = $this->db->selectCountDistinctField('resource_metadata', 'resourcemetadataId');
        $this->session->setVar("setup_PagingTotal", $countQuery[0]['count']);
		if ($this->browserTabID) {
			GLOBALS::setTempStorage(['setup_PagingTotal' => $countQuery[0]['count']]);
		}
        $pagingObject->queryString = $queryString;
        $pagingObject->getPaging();
        if (!$searchTerms = GLOBALS::getTempStorage('search_HighlightIdea')) {
			$searchTerms = \UTF8\mb_explode(",", $this->session->getVar("search_HighlightIdea"));
		}
        foreach ($searchTerms as $term)
        {
            if (trim($term))
            {
                $patterns[] = "/($term)(?=[^>]*(<|$))/ui";
            }
        }
        // now get ideas
        // Check this user is allowed to read the idea.
        $this->metadata->setCondition('i');
        $this->db->formatConditions(implode($this->db->or, $conditions));
        $this->db->limit(GLOBALS::getUserVar('Paging'), $pagingObject->start);
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataTimestamp', 'resourcemetadataTimestampEdited',
            'resourcemetadataMetadataId', 'resourcemetadataText', 'resourcemetadataAddUserId', 'resourcemetadataPrivate', ]);
        if (!$this->db->numRows($resultset))
        {
            $this->common->noIdeas();

            return;
        }
        while ($row = $this->db->fetchRow($resultset))
        {
            if ($multiUser)
            {
                list($user) = $userObj->displayUserAddEdit($row['resourcemetadataAddUserId'], FALSE, 'idea');
                if (!$row['resourcemetadataTimestampEdited'])
                {
                    $ideaList[$index]['user'] = $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']);
                }
                else
                {
                    $ideaList[$index]['user'] = $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']) .
                    ',&nbsp;' . $this->messages->text('hint', 'editedBy', $user . '&nbsp;' . $row['resourcemetadataTimestampEdited']);
                }
                GLOBALS::addTplVar('multiUser', TRUE);
            }
            $ideaList[$index]['links'] = $this->metadata->createLinks($row, TRUE);
            $data = preg_replace($patterns, \HTML\span("$1", "highlight"), $row['resourcemetadataText']);
            $ideaList[$index]['metadata'] = $cite->parseCitations(\HTML\nlToHtml($data), 'html');
            ++$index;
        }
        $this->session->setVar("sql_LastIdeaSearch", $queryString);
		if ($this->browserTabID) {
			GLOBALS::setTempStorage(['sql_LastIdeaSearch' => $queryString]);
		}
        GLOBALS::addTplVar('ideaTemplate', TRUE);
        GLOBALS::addTplVar('ideaList', $ideaList);
        $this->common->pagingStyle($countQuery, FALSE, FALSE, $queryString);
        $this->session->setVar("setup_PagingTotal", $this->backupPT);
		if ($this->browserTabID) {
			GLOBALS::setTempStorage(['setup_PagingTotal' => $this->backupPT]);
		}
    }
    /**
     * Display the first field of the search form
     *
     * @return string
     */
    private function firstDisplay()
    {
        return \HTML\div('searchElement_1', $this->wordSearch());
    }
    /**
     * Create form fields for word searches on fields
     *
     * @param int $index
     * @param bool $remove
     *
     * @return string
     */
    private function wordSearch($index = 1, $remove = FALSE)
    {
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        if ($remove)
        {
            $pString .= \HTML\td($this->addRemoveIcon($index, FALSE)); // add remove element icon
        }
        // Add radio buttons
        if ($index > 1)
        {
            $buttons = $this->makeRadioButtons1("advancedSearch_Button1_$index");
            $pString .= \HTML\td(\HTML\div("searchElementButtons_$index", $buttons), 'left width5percent');
        }
        $fields = $this->searchFields($index);
        $pString .= \HTML\td($fields, 'left width15percent');
        if (($index == 1) && array_key_exists("Field_1", $this->input))
        {
            $pString .= \HTML\td(\HTML\div("searchElementContainer_1", $this->createDivs($this->input["Field_1"], 1)));
        }
        else
        {
            $pString .= \HTML\td(\HTML\div("searchElementContainer_$index", $this->wordDiv($index)));
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Create radio buttons for AND and OR and NOT
     *
     * @param string $type
     *
     * @return string
     */
    private function makeRadioButtons1($type)
    {
        if ($this->session->getVar($type) == 'AND')
        {
            $pString = \HTML\span(\FORM\radioButton(FALSE, $type, 'OR') . " OR", "small") . BR;
            $pString .= \HTML\span(\FORM\radioButton(FALSE, $type, 'AND', TRUE) . " AND", "small") . BR;
            $pString .= \HTML\span(\FORM\radioButton(FALSE, $type, 'NOT') . " NOT", "small");
        }
        elseif ($this->session->getVar($type) == 'NOT')
        {
            $pString = \HTML\span(\FORM\radioButton(FALSE, $type, 'OR') . " OR", "small") . BR;
            $pString .= \HTML\span(\FORM\radioButton(FALSE, $type, 'AND') . " AND", "small") . BR;
            $pString .= \HTML\span(\FORM\radioButton(FALSE, $type, 'NOT', TRUE) . " NOT", "small");
        }
        // Default
        else
        {
            $pString = \HTML\span(\FORM\radioButton(FALSE, $type, 'OR', TRUE) . " OR", "small") . BR;
            $pString .= \HTML\span(\FORM\radioButton(FALSE, $type, 'AND') . " AND", "small") . BR;
            $pString .= \HTML\span(\FORM\radioButton(FALSE, $type, 'NOT') . " NOT", "small");
        }

        return $pString;
    }
    /**
     * Create radio buttons for AND and OR
     *
     * @param string $type
     *
     * @return string
     */
    private function makeRadioButtons2($type)
    {
        if ($this->session->getVar($type) == 'AND')
        {
            $pString = \HTML\span(\FORM\radioButton(FALSE, $type, 'OR') . " OR", "small") . BR;
            $pString .= \HTML\span(\FORM\radioButton(FALSE, $type, 'AND', TRUE) . " AND", "small");
        }
        // Default
        else
        {
            $pString = \HTML\span(\FORM\radioButton(FALSE, $type, 'OR', TRUE) . " OR", "small") . BR;
            $pString .= \HTML\span(\FORM\radioButton(FALSE, $type, 'AND') . " AND", "small");
        }

        return $pString;
    }
    /**
     * Search options
     *
     * @return string
     */
    private function options()
    {
        $pString = \HTML\tableStart('generalTable borderStyleSolid');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($this->userBibs(), 'width10percent');
        $pString .= \HTML\td($this->displayOptions(), 'width10percent');
        $pString .= \HTML\td($this->common->displayOrder('advancedSearch'), 'width10percent');
        $pString .= \HTML\td(\FORM\formSubmit($this->messages->text("submit", "Search")), 'center top width5percent');
        $pString .= \HTML\td($this->displayTest(), 'width55percent');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Display test option
     *
     * @return string
     */
    private function displayTest()
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => "test",
            'script' => "index.php?action=list_SEARCH_CORE&method=test",
            'targetDiv' => "advancedSearch_Test",
        ];
        $testIcon = \AJAX\jActionIcon('view', 'onclick', $jsonArray);

        return $this->messages->text("search", "test") . ':&nbsp;&nbsp;' . $testIcon . \HTML\p(\HTML\div('advancedSearch_Test', ' '));
    }
    /**
     * Pull out real values behind IDs
     *
     * @param mixed $value
     */
    private function formatElement(&$value)
    {
        if ($value['Field'] == 'creator')
        {
            foreach ($value['Select'] as $select)
            {
                $temp[] = $this->formatCreators($select);
            }
        }
        elseif ($value['Field'] == 'publisher')
        {
            foreach ($value['Select'] as $select)
            {
                $temp[] = $this->formatPublishers($select);
            }
        }
        elseif ($value['Field'] == 'collection')
        {
            foreach ($value['Select'] as $select)
            {
                $temp[] = $this->formatCollections($select);
            }
        }
        elseif ($value['Field'] == 'type')
        {
            return;
        }
        elseif ($value['Field'] == 'subcategory')
        {
            foreach ($value['Select'] as $select)
            {
                $temp[] = $this->formatSubcategories($select);
            }
        }
        elseif ($value['Field'] == 'category')
        {
            foreach ($value['Select'] as $select)
            {
                $temp[] = $this->formatCategories($select);
            }
        }
        elseif (($value['Field'] == 'keyword') or ($value['Field'] == 'metaKeyword'))
        {
            foreach ($value['Select'] as $select)
            {
                $temp[] = $this->formatKeywords($select);
            }
        }
        elseif ($value['Field'] == 'userTag')
        {
            foreach ($value['Select'] as $select)
            {
                $temp[] = $this->formatUsertags($select);
            }
        }
        elseif ($value['Field'] == 'language')
        {
            foreach ($value['Select'] as $select)
            {
                $temp[] = $this->formatLanguages($select);
            }
        }
        elseif ($value['Field'] == 'tag')
        {
            foreach ($value['Select'] as $select)
            {
                $temp[] = $this->formatTags($select);
            }
        }
        elseif (($value['Field'] == 'addedBy') or ($value['Field'] == 'editedBy'))
        {
            foreach ($value['Select'] as $select)
            {
                $temp[] = $this->formatUsernames($select);
            }
        }
        $value['Select'] = $temp;
    }
    /**
     * Test error
     */
    private function testError()
    {
        $div = $this->errors->text("inputError", "missing");
        $jsonResponseArray = [
            'innerHTML' => "$div",
        ];
        GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Callback function to color red each array element of a select box
     *
     * @param mixed $element
     *
     * @return string
     */
    private function colorID($element)
    {
        if (!trim($element))
        {
            return '';
        }

        return \HTML\color($element, 'redText');
    }
    /**
     * Display attachment options
     *
     * @return string
     */
    private function displayOptions()
    {
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        if ($this->session->getVar("setup_ReadOnly") && WIKINDX_FILE_VIEW_LOGGEDON_ONLY)
        {
            $options = [];
        }
        elseif ($this->db->tableIsEmpty('resource_attachments') == 1)
        {
            $options = [];
        }
        else
        {
            $options = [
                'ignore' => $this->messages->text("misc", "ignore"),
                'noAttachment' => $this->messages->text('select', 'noAttachment'),
                'withAttachment' => $this->messages->text('select', 'attachment'),
                'displayOnlyAttachment' => $this->messages->text('select', 'displayAttachment'),
                'zipAttachment' => $this->messages->text('select', 'displayAttachmentZip'),
            ];
        }
        if (empty($options))
        {
            $options['ignore'] = $this->messages->text("misc", "ignore");
        }
        $options['withUrl'] = $this->messages->text('select', 'url');
        $options['withDoi'] = $this->messages->text('select', 'doi');
        $options['peerReviewed'] = $this->messages->text('select', 'displayPeerReviewed');
        $jsonArray[] = [
            'startFunction' => "attachmentOptions",
        ];
        $js = \AJAX\jActionForm('onchange', $jsonArray);
        $selected = FALSE;
        if (!$selected = GLOBALS::getTempStorage('advancedSearch_Options')) {
        	$selected = $this->session->getVar("advancedSearch_Options");
        }
        if ($selected)
        {
            $selected = unserialize(base64_decode($selected));
            if (($key = array_search('ignore', $selected)) !== FALSE)
            {
                unset($selected[$key]);
            }
            if (!empty($selected))
            {
                $selectBox = \FORM\selectedBoxValueMultiple(
                    $this->messages->text('select', 'option'),
                    "advancedSearch_Options",
                    $options,
                    $selected,
                    5,
                    FALSE,
                    $js
                );
            }
            else
            {
                $selectBox = \FORM\selectFBoxValueMultiple(
                    $this->messages->text('select', 'option'),
                    "advancedSearch_Options",
                    $options,
                    5,
                    FALSE,
                    $js
                );
            }
        }
        else
        {
            $selectBox = \FORM\selectFBoxValueMultiple(
                $this->messages->text('select', 'option'),
                "advancedSearch_Options",
                $options,
                5,
                FALSE,
                $js
            );
        }
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Return select box of user bibliographies for this user if browsing the master bibliography
     *
     * @return string
     */
    private function userBibs()
    {
        $bibs = $this->commonBib->getUserBibs() + $this->commonBib->getGroupBibs();
        if ($userBib = GLOBALS::getUserVar('BrowseBibliography'))
        {
            unset($bibs[$userBib]);
        }
        if (empty($bibs))
        {
            return "&nbsp;";
        }
        // add 0 => IGNORE to $array
        $temp[0] = $this->messages->text("misc", "ignore");
        foreach ($bibs as $key => $value)
        {
            $temp[$key] = $value;
        }
        $selected = FALSE;
        if (!$selected = GLOBALS::getTempStorage('advancedSearch_BibId')) {
        	$selected = $this->session->getVar("advancedSearch_BibId");
        }
        if ($selected && array_key_exists($selected, $temp))
        {
            $pString = \FORM\selectedBoxValue(
                $this->messages->text("select", "notInUserBib"),
                "advancedSearch_BibId",
                $temp,
                $selected,
                5
            );
        }
        else
        {
            $pString = \FORM\selectFBoxValue($this->messages->text("select", "notInUserBib"), "advancedSearch_BibId", $temp, 5);
        }

        return $pString;
    }
    /**
     * Word search div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function wordDiv($index)
    {
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $word = array_key_exists("Word_$index", $this->input) ?
            htmlspecialchars(stripslashes($this->input["Word_$index"]), ENT_QUOTES | ENT_HTML5) : FALSE;
        $wordHint = BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "wordLogic")
        ), 'hint');
        $checked = array_key_exists("Partial_$index", $this->input) ? 'CHECKED' : FALSE;
        $checked = BR . $this->messages->text("search", "partial") . ':&nbsp;&nbsp;' .
            \FORM\checkbox(FALSE, "advancedSearch_Partial_$index", $checked);
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("search", "word"),
            "advancedSearch_Word_$index",
            $word,
            40
        ) . $wordHint . $checked);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Add or remove form element icon
     *
     * @param int $index
     * @param bool $add
     * @param int $updateJSElementIndex
     *
     * @return string
     */
    private function addRemoveIcon($index, $add = TRUE, $updateJSElementIndex = FALSE)
    {
        $jsonArray = [];
        if ($add)
        {
            $jScript = "index.php?action=list_SEARCH_CORE&method=addElement";
            $startFunction = 'addElement';
            $elementIndex = $this->session->getVar("advancedSearch_elementIndex");
        }
        else
        {
            $jScript = "index.php?action=list_SEARCH_CORE&method=removeElement";
            $startFunction = 'removeElement';
            $elementIndex = $index;
        }
        $jsonArray[] = [
            'startFunction' => "$startFunction",
            'script' => "$jScript",
            'elementIndex' => "$elementIndex",
            'updateJSElementIndex' => "$updateJSElementIndex",
        ];
        if ($add)
        {
            return \AJAX\jActionIcon('add', 'onclick', $jsonArray);
        }
        else
        {
            return \AJAX\jActionIcon('remove', 'onclick', $jsonArray);
        }
    }
    /**
     * Make div selected box
     *
     * @param mixed $array
     * @param mixed $type
     * @param mixed $index
     *
     * @return string
     */
    private function makeSelectBox(&$array, $type, $index)
    {
        if (array_key_exists("Select_$index", $this->input))
        {
            $selected = [];
            $temp = unserialize(base64_decode($this->input["Select_$index"]));
            foreach ($temp as $value)
            {
                if (!is_array($array) || !array_key_exists($value, $array))
                { // could be the case bibliography used for browsing has changed
                    continue;
                }
                $selected[$value] = $array[$value];
                unset($array[$value]);
            }
            if (!empty($selected))
            {
                $selectBox = \FORM\selectFBoxValueMultiple(FALSE, "advancedSearch_Select_$index", $selected, 5);
            }
            else
            {
                $selectBox = \FORM\selectFBoxValueMultiple(FALSE, "advancedSearch_Select_$index", [], 5);
            }
        }
        else
        {
            $selectBox = \FORM\selectFBoxValueMultiple(FALSE, "advancedSearch_Select_$index", [], 5);
        }
        if (($type == 'type') || ($type == 'publisher') || ($type == 'collection') || ($type == 'tag') || ($type == 'addedBy') || ($type == 'editedBy'))
        {
            $buttons = \HTML\span('&nbsp;OR', 'small') . \FORM\hidden("advancedSearch_Button2_$index", 'OR');
        }
        else
        {
            $buttons = $this->makeRadioButtons2("advancedSearch_Button2_$index");
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($this->messages->text("select", $type), '', 3);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($selectBox, 'left width5percent');
        $pString .= \HTML\td($buttons, 'left');
        $pString .= \HTML\td('&nbsp;', 'width100percent'); // Filler to ensure buttons are flush against select box
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Resource type div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function typeDiv($index)
    {
        $types = $this->type->grabAll(GLOBALS::getUserVar('BrowseBibliography'), TRUE);
        $selectBox = $this->makeSelectBox($types, 'type', $index);
        if (is_array($types))
        {
            natcasesort($types);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableType"),
            "types_$index",
            $types,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("types_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Category div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function categoryDiv($index)
    {
        $categories = $this->category->grabAll(GLOBALS::getUserVar('BrowseBibliography'), TRUE);
        $selectBox = $this->makeSelectBox($categories, 'category', $index);
        if (is_array($categories))
        {
            natcasesort($categories);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableCategory"),
            "categories_$index",
            $categories,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("categories_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Subcategory div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function subcategoryDiv($index)
    {
        $subcategories = $this->category->grabSubAll(TRUE, GLOBALS::getUserVar('BrowseBibliography'), FALSE, TRUE);
        $selectBox = $this->makeSelectBox($subcategories, 'subcategory', $index);
        if (is_array($subcategories))
        {
            natcasesort($subcategories);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableSubcategory"),
            "subcategories_$index",
            $subcategories,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("subcategories_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Creator div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function creatorDiv($index)
    {
        $creators = $this->creator->grabAll(GLOBALS::getUserVar('BrowseBibliography'));
        $selectBox = $this->makeSelectBox($creators, 'creator', $index);
        if (is_array($creators))
        {
            natcasesort($creators);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableCreator"),
            "creators_$index",
            $creators,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("creators_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Keyword div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function keywordDiv($index)
    {
        $keywords = $this->keyword->grabAll(GLOBALS::getUserVar('BrowseBibliography'), 'resource');
        $selectBox = $this->makeSelectBox($keywords, 'keyword', $index);
        if (is_array($keywords))
        {
            natcasesort($keywords);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableKeyword"),
            "keywords_$index",
            $keywords,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("keywords_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Metadata Keyword div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function metaKeywordDiv($index)
    {
        $metakeywords = $this->keyword->grabAll(TRUE, ['quote', 'paraphrase', 'quoteComment', 'paraphraseComment', 'musing', 'idea']);
        $selectBox = $this->makeSelectBox($metakeywords, 'metaKeyword', $index);
        if (is_array($metakeywords))
        {
            natcasesort($metakeywords);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableMetaKeyword"),
            "metakeywords_$index",
            $metakeywords,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("metakeywords_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Language div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function languageDiv($index)
    {
        $this->grabLanguages();
        $selectBox = $this->makeSelectBox($this->languages, 'language', $index);
        if (is_array($this->languages))
        {
            natcasesort($this->languages);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableLanguage"),
            "languages_$index",
            $this->languages,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("languages_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Publisher div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function publisherDiv($index)
    {
        $publishers = $this->publisher->grabAll(FALSE, GLOBALS::getUserVar('BrowseBibliography'));
        $selectBox = $this->makeSelectBox($publishers, 'publisher', $index);
        if (is_array($publishers))
        {
            natcasesort($publishers);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availablePublisher"),
            "publishers_$index",
            $publishers,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("publishers_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Collection div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function collectionDiv($index)
    {
        $collections = $this->collection->grabAll(FALSE, GLOBALS::getUserVar('BrowseBibliography'));
        $selectBox = $this->makeSelectBox($collections, 'collection', $index);
        if (is_array($collections))
        {
            natcasesort($collections);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableCollection"),
            "collections_$index",
            $collections,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("collections_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * UserTag div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function userTagDiv($index)
    {
        $userTags = $this->userTag->grabAll(GLOBALS::getUserVar('BrowseBibliography'), FALSE, TRUE);
        $selectBox = $this->makeSelectBox($userTags, 'userTag', $index);
        if (is_array($userTags))
        {
            natcasesort($userTags);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableUserTag"),
            "userTags_$index",
            $userTags,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("userTags_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Import Tag div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function tagDiv($index)
    {
        $tags = $this->tag->grabAll();
        $selectBox = $this->makeSelectBox($tags, 'tag', $index);
        if (is_array($tags))
        {
            natcasesort($tags);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableTag"),
            "tags_$index",
            $tags,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("tags_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * AddedBy div
     *
     * @param mixed $index
     *
     * @return string
     */
    private function addedByDiv($index)
    {
        $users = $this->grabUsers('add');
        $selectBox = $this->makeSelectBox($users, 'addedBy', $index);
        if (is_array($users))
        {
            natcasesort($users);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableAddedBy"),
            "addedBy_$index",
            $users,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("addedBy_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * EditedBy div
     *
     * @param string $index
     *
     * @return string
     */
    private function editedByDiv($index)
    {
        $users = $this->grabUsers('edit');
        $selectBox = $this->makeSelectBox($users, 'editedBy', $index);
        if (is_array($users))
        {
            natcasesort($users);
        }
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("select", "availableEditedBy"),
            "editedBy_$index",
            $users,
            5
        ), 'left width10percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows("editedBy_$index", "advancedSearch_Select_$index");
        $pString .= \HTML\td(\HTML\p('&nbsp;' . $toRightImage) . \HTML\p('&nbsp;' . $toLeftImage), 'left width10percent');
        $pString .= \HTML\td($selectBox);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Value div for publicationYear, access, maturityIndex
     *
     * @param mixed $index
     *
     * @return string
     */
    private function valueDiv($index)
    {
        $comps = ['=', '!=', '<', '>', '<=', '>=', '<...<'];
        $jsonArray = [];
        $jScript = "index.php?action=list_SEARCH_CORE&method=addComparisonValue";
        $jsonArray[] = [
            'startFunction' => "addComparisonValue",
            'script' => "$jScript",
            'elementIndex' => "$index",
            'targetDiv' => "value2Container_$index",
        ];
        $js = \AJAX\jActionForm('onclick', $jsonArray);
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        if (array_key_exists("Comparison_$index", $this->input))
        {
            $selectBox = \FORM\selectedBoxValue(FALSE, "advancedSearch_Comparison_$index", $comps, $this->input["Comparison_$index"], 7, FALSE, $js);
        }
        else
        {
            $selectBox = \FORM\selectFBoxValue(FALSE, "advancedSearch_Comparison_$index", $comps, 7, FALSE, $js);
        }
        $pString .= \HTML\td($selectBox, 'left width10percent');
        if (array_key_exists("Value1_$index", $this->input))
        {
            $textInput = \FORM\textInput(FALSE, "advancedSearch_Value1_$index", $this->input["Value1_$index"]);
        }
        else
        {
            $textInput = \FORM\textInput(FALSE, "advancedSearch_Value1_$index");
        }
        $pString .= \HTML\td($textInput, 'left width10percent');
        if (array_key_exists("Value2_$index", $this->input))
        {
            $pString .= \HTML\td(\HTML\div(
                "value2Container_$index",
                \FORM\textInput(FALSE, "advancedSearch_Value2_$index", $this->input["Value2_$index"])
            ), 'left width10percent');
        }
        else
        {
            $pString .= \HTML\td(\HTML\div("value2Container_$index", ' '), 'left width10percent'); // needed for '<...<'
        }
        $pString .= \HTML\td('&nbsp;', 'width100percent'); // Filler to ensure buttons are flush against select box
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * get languages from database
     */
    private function grabLanguages()
    {
        $userBib = GLOBALS::getUserVar('BrowseBibliography');
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourceLanguageResourceId');
        }
        $this->db->orderBy('languageLanguage');
        $this->db->leftJoin('language', 'languageId', 'resourcelanguageLanguageId');
        $resultset = $this->db->select('resource_language', ['resourcelanguageLanguageId', 'languageLanguage'], TRUE);
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->languages[$row['resourcelanguageLanguageId']] = $row['languageLanguage'];
        }
    }
    /**
     * grab users
     *
     * @param string $addEdit
     *
     * @return string
     */
    private function grabUsers($addEdit = 'add')
    {
        return $this->user->grabAll(TRUE, GLOBALS::getUserVar('BrowseBibliography'), $addEdit);
    }
    // Check what search fields are available for this user in the database
    /*
    For comments, in the database resourcemetadataMetadataId references the resourcemetadataId field of the parent quote or paraphrase. So,
    for searches within a bibliography, the SQL should be something like (here, pulling paraphrase comments against resources in bibliography ID 86
    and with user ID 1):
    SELECT COUNT(*) AS `count`, `resourcemetadataId`
    FROM `resource_metadata`
    LEFT JOIN `user_bibliography_resource` ON `userbibliographyresourceResourceId` = `resourcemetadataResourceId`
    WHERE
    (
        `resourcemetadataId` IN
        (
            SELECT `resourcemetadataMetadataId`
            FROM `resource_metadata`
            WHERE
                (CASE
                    WHEN (`resourcemetadataPrivate` != 'N' AND `resourcemetadataPrivate` != 'Y' )
                    THEN
                    (
                        SELECT `usergroupsusersId`
                        FROM `user_groups_users`
                        WHERE (`usergroupsusersUserId` = '1')
                        AND (`usergroupsusersGroupId` = `resourcemetadataPrivate`)
                    )
                    END
                    OR
                    CASE WHEN (`resourcemetadataPrivate` = 'Y' )
                    THEN
                        (`resourcemetadataAddUserId` = '1')
                    END
                    OR
                    CASE WHEN (`resourcemetadataPrivate` = 'N' )
                    THEN
                        ('1')
                    END)
                AND
                (`resourceMetadataType` = 'pc')
        )
    )
    AND (`userbibliographyresourceBibliographyId` = '86')
    GROUP BY `resourcemetadataId`
    */
    /**
     * checkAvailableFields
     */
    private function checkAvailableFields()
    {
        $userId = $this->session->getVar("setup_UserId");
        // userTags
        $this->db->formatConditions(['userTagsUserId' => $userId]);
        if (!empty($this->userTag->grabAll(GLOBALS::getUserVar('BrowseBibliography'), FALSE, TRUE)))
        {
            $this->displayUserTags = TRUE;
        }
        if ((!WIKINDX_METADATA_ALLOW))
        {
            if ((!WIKINDX_METADATA_USERONLY))
            {
                $this->displayIdeas = $this->displayQCs = $this->displayPCs = $this->displayMusings = $this->displayMKs = FALSE;

                return;
            }
        }
        $userBib = GLOBALS::getUserVar('BrowseBibliography');
        // for everything here, the user must be logged on
        if (!$userId)
        {
            return;
        }
        // ideas (which are independent of resources). setCondition() returns FALSE if user is not logged on. ReadOnly users never see musings, ideas, or comments.
        if (!$this->metadata->setCondition('i'))
        {
            return;
        }
        $resultSet = $this->db->selectCount('resource_metadata', 'resourcemetadataId');
        if ($this->db->fetchOne($resultSet))
        {
            $this->displayIdeas = TRUE;
        }
        // quote comments
        if (!$this->metadata->setCondition('qc'))
        {
            return;
        }
        $subQ = $this->db->subQuery($this->db->selectNoExecute('resource_metadata', 'resourcemetadataMetadataId'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('resourcemetadataId') . $this->db->inClause($subQ));
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcemetadataResourceId');
        }
        $resultSet = $this->db->selectCount('resource_metadata', 'resourcemetadataId');
        if ($this->db->fetchOne($resultSet))
        {
            $this->displayQCs = TRUE;
        }
        // paraphrase comments
        if (!$this->metadata->setCondition('pc'))
        {
            return;
        }
        $subQ = $this->db->subQuery($this->db->selectNoExecute('resource_metadata', 'resourcemetadataMetadataId'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('resourcemetadataId') . $this->db->inClause($subQ));
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcemetadataResourceId');
        }
        $resultSet = $this->db->selectCount('resource_metadata', 'resourcemetadataId');
        if ($this->db->fetchOne($resultSet))
        {
            $this->displayPCs = TRUE;
        }
        // musings
        if (!$this->metadata->setCondition('m'))
        {
            return;
        }
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcemetadataResourceId');
        }
        $resultSet = $this->db->selectCount('resource_metadata', 'resourcemetadataId');
        if ($this->db->fetchOne($resultSet))
        {
            $this->displayMusings = TRUE;
        }
        // metadatakeywords (user restrictions if ideas and musings)
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcekeywordResourceId');
        }
        $this->db->formatConditions(['resourcekeywordMetadataId' => 'IS NOT NULL']);
        $resultSet = $this->db->selectCount('resource_keyword', 'resourcekeywordMetadataId');
        if ($this->db->fetchOne($resultSet))
        {
            $this->displayMKs = TRUE;
        }
        if (!$this->displayMKs)
        {
            if (!$this->metadata->setCondition('m'))
            {
                return;
            }
            $subQ = $this->db->subQuery($this->db->selectNoExecute('resource_keyword', 'resourcekeywordMetadataId'), FALSE, FALSE, TRUE);
            $this->db->formatConditions($this->db->formatFields('resourcemetadataId') . $this->db->inClause($subQ));
            if ($userBib)
            {
                $this->commonBib->userBibCondition('resourcemetadataResourceId');
            }
            $resultSet = $this->db->selectCount('resource_metadata', 'resourcemetadataId');
            if ($this->db->fetchOne($resultSet))
            {
                $this->displayMKs = TRUE;
            }
        }
        if (!$this->displayMKs)
        {
            if (!$this->metadata->setCondition('i'))
            {
                return;
            }
            $subQ = $this->db->subQuery($this->db->selectNoExecute('resource_keyword', 'resourcekeywordMetadataId'), FALSE, FALSE, TRUE);
            $this->db->formatConditions($this->db->formatFields('resourcemetadataId') . $this->db->inClause($subQ));
            if ($userBib)
            {
                $this->commonBib->userBibCondition('resourcemetadataResourceId');
            }
            $resultSet = $this->db->selectCount('resource_metadata', 'resourcemetadataId');
            if ($this->db->fetchOne($resultSet))
            {
                $this->displayMKs = TRUE;
            }
        }
    }
    /**
     * Return array of database fields to perform the search on. This populates the first select box
     *
     * @param int $index
     *
     * @return string
     */
    private function searchFields($index = 0)
    {
        $userBib = GLOBALS::getUserVar('BrowseBibliography');
        $fields = ["title" => $this->messages->text("search", "title")];
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcetextId');
        }
        $this->db->formatConditions(['resourcetextNote' => 'IS NOT NULL']);
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        $resultSet = $this->db->select('resource_text', 'resourcetextId');
        if ($this->db->fetchOne($resultSet))
        {
            $fields['note'] = $this->messages->text("search", "note");
        }
        $this->db->formatConditions(['resourcetextAbstract' => 'IS NOT NULL']);
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        $resultSet = $this->db->select('resource_text', 'resourcetextId');
        if ($this->db->fetchOne($resultSet))
        {
            $fields['abstract'] = $this->messages->text("search", "abstract");
        }
        if ((!WIKINDX_FILE_VIEW_LOGGEDON_ONLY || $this->session->getVar("setup_UserId")) &&
            ($this->db->tableIsEmpty('resource_attachments') == 0))
        {
            $fields['attachments'] = $this->messages->text("search", "attachments");
        }
        if ((!WIKINDX_METADATA_ALLOW))
        {
            if ((WIKINDX_METADATA_USERONLY) && $this->session->getVar("setup_UserId"))
            {
                if ($userBib)
                {
                    $this->commonBib->userBibCondition('resourcemetadataResourceId');
                }
                $this->db->formatConditions(['resourcemetadataType' => 'q']);
                $this->db->limit(1, 0); // Keep memory usage down for large databases
                $resultSet = $this->db->select('resource_metadata', 'resourcemetadataId');
                if ($this->db->fetchOne($resultSet))
                {
                    $fields['quote'] = $this->messages->text("search", "quote");
                }
            }
        }
        else
        {
            if ($userBib)
            {
                $this->commonBib->userBibCondition('resourcemetadataResourceId');
            }
            $this->db->formatConditions(['resourcemetadataType' => 'q']);
            $this->db->limit(1, 0); // Keep memory usage down for large databases
            $resultSet = $this->db->select('resource_metadata', 'resourcemetadataId');
            if ($this->db->fetchOne($resultSet))
            {
                $fields['quote'] = $this->messages->text("search", "quote");
            }
        }
        if ($this->displayQCs)
        {
            $fields['quoteComment'] = $this->messages->text("search", "quoteComment");
        }
        if ((!WIKINDX_METADATA_ALLOW))
        {
            if ((WIKINDX_METADATA_USERONLY) && $this->session->getVar("setup_UserId"))
            {
                if ($userBib)
                {
                    $this->commonBib->userBibCondition('resourcemetadataResourceId');
                }
                $this->db->formatConditions(['resourcemetadataType' => 'p']);
                $this->db->limit(1, 0); // Keep memory usage down for large databases
                $resultSet = $this->db->select('resource_metadata', 'resourcemetadataId');
                if ($this->db->fetchOne($resultSet))
                {
                    $fields['paraphrase'] = $this->messages->text("search", "paraphrase");
                }
            }
        }
        else
        {
            if ($userBib)
            {
                $this->commonBib->userBibCondition('resourcemetadataResourceId');
            }
            $this->db->formatConditions(['resourcemetadataType' => 'p']);
            $this->db->limit(1, 0); // Keep memory usage down for large databases
            $resultSet = $this->db->select('resource_metadata', 'resourcemetadataId');
            if ($this->db->fetchOne($resultSet))
            {
                $fields['paraphrase'] = $this->messages->text("search", "paraphrase");
            }
        }
        if ($this->displayPCs)
        {
            $fields['paraphraseComment'] = $this->messages->text("search", "paraphraseComment");
        }
        if ($this->displayMusings)
        {
            $fields['musing'] = $this->messages->text("search", "musing");
        }
        if ($this->displayIdeas)
        {
            $fields['idea'] = $this->messages->text("search", "idea");
        }
        // Add any used custom fields
        $subQ = $this->db->subQuery($this->db->selectNoExecute('resource_custom', 'resourcecustomCustomId'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('customId') . $this->db->inClause($subQ));
        $recordset = $this->db->select('custom', ['customId', 'customLabel', 'customSize']);
        while ($row = $this->db->fetchRow($recordset))
        {
            if ($row['customSize'] == 'S')
            {
                $fields['Custom_S_' . $row['customId']] =
                    $this->messages->text('custom', 'customField') . ':&nbsp;' . \HTML\dbToFormTidy($row['customLabel']);
            }
            else
            {
                $fields['Custom_L_' . $row['customId']] =
                    $this->messages->text('custom', 'customField') . ':&nbsp;' . \HTML\dbToFormTidy($row['customLabel']);
            }
        }
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcecreatorId');
        }
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->select('resource_creator', 'resourcecreatorId')))
        {
            $fields['creator'] = $this->messages->text("search", "creator");
        }
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcemiscPublisher');
        }
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->select('resource_misc', 'resourcemiscPublisher')))
        {
            $fields['publisher'] = $this->messages->text("search", "publisher");
        }
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcemiscCollection');
        }
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->select('resource_misc', 'resourcemiscCollection')))
        {
            $fields['collection'] = $this->messages->text("search", "collection");
        }
        $fields['type'] = $this->messages->text("search", "type");
        $fields['category'] = $this->messages->text("search", "category"); // All resources always belong to at least one category
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcecategoryResourceId');
        }
        $this->db->formatConditions(['resourcecategorySubcategoryId' => 'IS NOT NULL']);
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->select('resource_category', 'resourcecategorySubcategoryId')))
        {
            $fields['subcategory'] = $this->messages->text("search", "subcategory");
        }
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcekeywordResourceId');
        }
        $this->db->formatConditions(['resourcekeywordResourceId' => 'IS NOT NULL']);
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->select('resource_keyword', 'resourcekeywordId')))
        {
            $fields['keyword'] = $this->messages->text("search", "keyword");
        }
        if ($this->displayMKs)
        {
            $fields['metaKeyword'] = $this->messages->text("search", "metaKeyword");
        }
        if ($this->displayUserTags)
        {
            $fields['userTag'] = $this->messages->text("search", "usertag");
        }
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcelanguageResourceId');
        }
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->select('resource_language', 'resourcelanguageId')))
        {
            $fields['language'] = $this->messages->text("search", "language");
        }
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->selectCount('tag', 'tagId')))
        {
            $fields['tag'] = $this->messages->text("search", "tag");
        }
        // If logged on and multiuser, display addedBy and editedBy options
        if ($this->session->getVar("setup_UserId") && (WIKINDX_MULTIUSER))
        {
            if ($userBib)
            {
                $this->commonBib->userBibCondition('resourcemiscId');
            }
            $this->db->formatConditions(['resourcemiscAddUserIdResource' => 'IS NOT NULL']);
            $this->db->limit(1, 0); // Keep memory usage down for large databases
            if ($this->db->fetchOne($this->db->select('resource_misc', 'resourcemiscAddUserIdResource')))
            {
                $fields['addedBy'] = $this->messages->text("search", "addedBy");
            }
            if ($userBib)
            {
                $this->commonBib->userBibCondition('resourcemiscId');
            }
            $this->db->formatConditions(['resourcemiscEditUserIdResource' => 'IS NOT NULL']);
            $this->db->limit(1, 0); // Keep memory usage down for large databases
            if ($this->db->fetchOne($this->db->select('resource_misc', 'resourcemiscEditUserIdResource')))
            {
                $fields['editedBy'] = $this->messages->text("search", "editedBy");
            }
        }
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourceyearId');
        }
        $this->db->formatConditions(['resourceyearYear1' => 'IS NOT NULL']);
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->select('resource_year', 'resourceyearYear1')))
        {
            $fields['publicationYear'] = $this->messages->text("search", "publicationYear");
        }
        // Temporarily remove searching on resource views as the resultant SQL is complicated by the need to do a SUM().
        /*        $this->db->formatConditions(['statisticsresourceviewsCount' => 'IS NOT NULL']);
                $this->db->limit(1, 0); // Keep memory usage down for large databases
                if ($this->db->fetchOne($this->db->select('statistics_resource_views', 'statisticsresourceviewsCount'))) {
                    $fields['access'] = $this->messages->text("search", "access");
                }
        */
        if ($userBib)
        {
            $this->commonBib->userBibCondition('resourcemiscId');
        }
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->select('resource_misc', 'resourcemiscMaturityIndex')))
        {
            $fields['maturityIndex'] = $this->messages->text("search", "maturityIndex");
        }
        $jsonArray = [];
        $jScript = "index.php?action=list_SEARCH_CORE&method=switchField";
        $triggerField = 'advancedSearch_Field_' . $index;
        $targetDiv = 'searchElementContainer_' . $index;
        $jsonArray[] = [
            'startFunction' => 'triggerSearchParameter',
            'script' => "$jScript",
            'triggerField' => "$triggerField",
            'targetDiv' => "$targetDiv",
            'elementIndex' => "$index",
        ];
        if ($index > 1)
        {
            $jScript = "index.php?action=list_SEARCH_CORE&method=switchButtons";
            $triggerField = 'advancedSearch_Field_' . $index;
            $targetDiv = 'searchElementButtons_' . $index;
            $jsonArray[] = [
                'startFunction' => 'triggerSearchParameter',
                'script' => "$jScript",
                'triggerField' => "$triggerField",
                'targetDiv' => "$targetDiv",
                'elementIndex' => "$index",
            ];
        }
        $js = \AJAX\jActionForm('onchange', $jsonArray);
        if (array_key_exists("Field_$index", $this->input) && array_key_exists($this->input["Field_$index"], $fields))
        {
            return \FORM\selectedBoxValue(
                $this->messages->text("search", "searchSelect"),
                "advancedSearch_Field_$index",
                $fields,
                $this->input["Field_$index"],
                5,
                FALSE,
                $js
            );
        }
        else
        {
            $this->input["Field_$index"] = 'title'; // As long as 'title' is the first element . . .
            return \FORM\selectFBoxValue($this->messages->text("search", "searchSelect"), "advancedSearch_Field_$index", $fields, 5, FALSE, $js);
        }
    }
    /**
     * Create divs
     *
     * @param mixed $field
     * @param mixed $index
     *
     * @return string
     */
    private function createDivs($field, $index)
    {
        switch ($field) {
            case 'title':
            case 'note':
            case 'abstract':
            case 'attachments':
            case 'quote':
            case 'quoteComment':
            case 'paraphrase':
            case 'paraphraseComment':
            case 'idea':
                $div = $this->wordDiv($index);

                break;
            case 'category':
                $div = $this->categoryDiv($index);

                break;
            case 'type':
                $div = $this->typeDiv($index);

                break;
            case 'subcategory':
                $div = $this->subcategoryDiv($index);

                break;
            case 'creator':
                $div = $this->creatorDiv($index);

                break;
            case 'keyword':
                $div = $this->keywordDiv($index);

                break;
            case 'metaKeyword':
                $div = $this->metaKeywordDiv($index);

                break;
            case 'userTag':
                $div = $this->userTagDiv($index);

                break;
            case 'language':
                $div = $this->languageDiv($index);

                break;
            case 'publisher':
                $div = $this->publisherDiv($index);

                break;
            case 'collection':
                $div = $this->collectionDiv($index);

                break;
            case 'tag':
                $div = $this->tagDiv($index);

                break;
            case 'addedBy':
                $div = $this->addedByDiv($index);

                break;
            case 'editedBy':
                $div = $this->editedByDiv($index);

                break;
            case 'publicationYear':
            case 'access':
            case 'maturityIndex':
                $div = $this->valueDiv($index);

                break;
            default: // any custom tags
                $div = $this->wordDiv($index);

                break;
        }

        return $div;
    }
    /**
     * relationships of search fields to database fields and tables (used when search is submitted)
     */
    private function populateDbFields()
    {
        $this->dbFields = [
            'type' => ['resourceType', 'resource', 'resourceId'],
            'title' => ['resourceTitleSort', 'resource', 'resourceId'],
            'note' => ['resourcetextNote', 'resource_text', 'resourcetextId'],
            'abstract' => ['resourcetextAbstract', 'resource_text', 'resourcetextId'],
            'quote' => ['resourcemetadataText', 'resource_metadata', 'resourcemetadataResourceId', 'q'],
            'paraphrase' => ['resourcemetadataText', 'resource_metadata', 'resourcemetadataResourceId', 'p'],
            'quoteComment' => ['resourcemetadataText', 'resource_metadata', 'resourcemetadataResourceId', 'qc'],
            'paraphraseComment' => ['resourcemetadataText', 'resource_metadata', 'resourcemetadataResourceId', 'pc'],
            'musing' => ['resourcemetadataText', 'resource_metadata', 'resourcemetadataResourceId', 'm'],
            'idea' => ['resourcemetadataText', 'resource_metadata'],
            'creator' => ['resourcecreatorCreatorId', 'resource_creator', 'resourcecreatorResourceId'],
            'publisher' => ['resourcemiscPublisher', 'resource_misc', 'resourcemiscId'],
            'collection' => ['resourcemiscCollection', 'resource_misc', 'resourcemiscId'],
            'category' => ['resourcecategoryCategoryId', 'resource_category', 'resourcecategoryResourceId'],
            'subcategory' => ['resourcecategorySubcategoryId', 'resource_category', 'resourcecategoryResourceId'],
            'keyword' => ['resourcekeywordKeywordId', 'resource_keyword', 'resourcekeywordResourceId'],
            'metaKeyword' => ['resourcekeywordKeywordId', 'resource_keyword', 'resourcemetadataResourceId'],
            'userTag' => ['resourceusertagsTagId', 'resource_user_tags', 'resourceusertagsResourceId'],
            'language' => ['resourcelanguageLanguageId', 'resource_language', 'resourcelanguageResourceId'],
            'tag' => ['resourcemiscTag', 'resource_misc', 'resourcemiscId'],
            'addedBy' => ['resourcemiscAddUserIdResource', 'resource_misc', 'resourcemiscId'],
            'editedBy' => ['resourcemiscEditUserIdResource', 'resource_misc', 'resourcemiscId'],
            'publicationYear' => ['resourceyearYear1', 'resource_year', 'resourceyearId'],
            'access' => ['statisticsresourceviewsCount', 'statistics_resource_views', 'statisticsresourceviewsResourceId'],
            'maturityIndex' => ['resourcemiscMaturityIndex', 'resource_misc', 'resourcemiscId'],
            'attachments' => ['resourceattachmentsText', 'resource_attachments', 'resourceattachmentsResourceId'],
        ];
        // Add any used custom fields
        $subQ = $this->db->subQuery($this->db->selectNoExecute('resource_custom', 'resourcecustomCustomId'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('customId') . $this->db->inClause($subQ));
        $recordset = $this->db->select('custom', ['customId', 'customLabel', 'customSize']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $fieldPrefix = $row['customSize'] == 'S' ? 'S' : 'L';
            if ($fieldPrefix == 'S')
            {
                $this->dbFields['Custom_' . $fieldPrefix . '_' . $row['customId']] =
                    ['resourcecustomShort', 'resource_custom', 'resourcecustomResourceId', $row['customId']];
            }
            else
            {
                $this->dbFields['Custom_' . $fieldPrefix . '_' . $row['customId']] =
                    ['resourcecustomLong', 'resource_custom', 'resourcecustomResourceId', $row['customId']];
            }
        }
    }
    /**
     * Create the SQL fragments from the search fields that will make up the final SQL search statement
     *
     * @param mixed $array
     *
     * @return bool
     */
    private function createSqlFragments($array)
    {
        ksort($array);
        $index = 0;
        $excludedIds = [];
        foreach ($array as $key => $valueArray)
        {
            if (($valueArray['OriginalField'] != 'idea') && array_key_exists('Button1', $valueArray) && !$index)
            {
                unset($valueArray['Button1']); // i.e. 'idea' was the first field searched on so there should be no button
            }
            if (array_key_exists('String', $valueArray) && ($valueArray['OriginalField'] == 'idea'))
            {
                $this->ideas[$key] = $valueArray;
            }
            elseif (array_key_exists('String', $valueArray))
            {
                // 'String' already with field and formatted for SQL
                $this->createStringCondition($key, $valueArray);
            }
            elseif (array_key_exists('Select', $valueArray))
            {
                // 'Select' from a select box
                $this->createSelectCondition($key, $valueArray);
            }
            elseif (array_key_exists('Comparison', $valueArray))
            {
                // 'Comparison' of a value or two values
                $this->createComparisonCondition($key, $valueArray);
            }
            if (!$this->validSearch)
            {
                unset($this->unionFragments[$key]);
            }
            $this->validSearch = TRUE;
            ++$index;
        }
        // check for attachment searches
        /*
        $matchedIds = array();
        $temp = $this->matchIds;
        foreach($this->matchIds as $key1 => $array)
        {
            foreach($array as $key2 => $id)
            {
                if (!array_search($id, $matchedIds))
                    unset($temp[$key1][$key2]);
                else
                    $matchedIds[] = $id;
            }
        }
        $this->matchIds = $temp;
        */
        if (!empty($this->unionFragments) && !empty($this->excludeIds))
        {
            $index = 0;
            foreach ($this->unionFragments as $key => $uf)
            {
                // append excluded resourceId conditions for attachment searches to unionFragments elements
                foreach ($this->excludeIds as $array)
                {
                    foreach ($array as $id)
                    {
                        if (array_search($id, $excludedIds) !== FALSE)
                        {
                            continue;
                        }
                        $condition = $this->db->and . $this->db->formatConditions([$this->unionResourceIds[$index] => $id], TRUE, TRUE);
                        $uf .= $condition;
                        $excludedIds[] = $id;
                    }
                }
                $this->unionFragments[$key] = $uf;
                ++$index;
            }
        }
        $matchSearches = [];
        foreach ($this->matchIds as $key1 => $array)
        {
            if (empty($array))
            {
                continue;
            }
            if (!array_key_exists('Button', $this->attachmentSearches[$key1]) || ($this->attachmentSearches[$key1]['Button'] == 'OR'))
            {
                $this->db->formatConditionsOneField($array, 'resourceId');
                $matchSearches[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource', [['resourceId' => 'rId']]));
            }
            elseif ($this->attachmentSearches[$key1]['Button'] == 'AND')
            {
                if (empty($this->unionFragments))
                {
                    $this->db->formatConditionsOneField($array, 'resourceId');
                    $matchSearches[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource', [['resourceId' => 'rId']]));
                }
                else
                {
                    $index = 0;
                    foreach ($this->unionFragments as $key2 => $uf)
                    {
                        $condition = $this->db->and . $this->db->formatConditionsOneField(
                            $array,
                            $this->unionResourceIds[$index],
                            FALSE,
                            TRUE,
                            FALSE,
                            FALSE,
                            TRUE
                        );
                        $uf .= $condition;
                        $this->unionFragments[$key2] = $uf;
                        ++$index;
                    }
                }
            }
            elseif ($this->attachmentSearches[$key1]['Button'] == 'NOT')
            {
                $this->db->formatConditionsOneField($array, 'resourceId');
                $matchSearches[] = $this->db->queryNoExecute($this->db->selectNoExecute('resource', [['resourceId' => 'rId']]));
            }
        }
        foreach ($matchSearches as $search)
        {
            $this->unionFragments[] = $search;
        }
        if (!empty($this->unionFragments))
        {
            $this->stmt->unions = $this->db->union($this->unionFragments);

            return TRUE;
        }

        return FALSE;
    }
    /**
     * Create the SQL fragment for string queries
     *
     * @param mixed $key
     * @param mixed $valueArray
     */
    private function createStringCondition($key, $valueArray)
    {
        $commentSubQ = FALSE;
        if (array_key_exists('Custom', $valueArray))
        {
            $this->db->formatConditions(['resourcecustomCustomId' => $this->dbFields[$valueArray['OriginalField']][3]]);
        }
        elseif (($valueArray['OriginalField'] == 'quote') || ($valueArray['OriginalField'] == 'paraphrase'))
        {
            $this->db->formatConditions(['resourcemetadataType' => $this->dbFields[$valueArray['OriginalField']][3]]);
        }
        elseif (($valueArray['OriginalField'] == 'musing') || ($valueArray['OriginalField'] == 'quoteComment') ||
            ($valueArray['OriginalField'] == 'paraphraseComment'))
        {
            if (!$this->metadata->setCondition($this->dbFields[$valueArray['OriginalField']][3]))
            {
                $this->validSearch = FALSE;
            }
        }
        // check for FULLTEXT fields
        if ($this->validSearch &&
            (($valueArray['OriginalField'] == 'note') ||
            ($valueArray['OriginalField'] == 'abstract') ||
            ($valueArray['Field'] == 'resourcecustomLong') ||
            ($valueArray['Field'] == 'resourceattachmentsText') ||
            ($valueArray['Field'] == 'resourcemetadataText')))
        {
            $matchAgainst = $this->db->fulltextSearch($valueArray['Field'], str_replace("'", "''", $valueArray['String']));
            $this->db->formatConditions($matchAgainst);
        }
        else
        {
            $this->db->formatConditions($valueArray['String']);
        }
        if ($this->validSearch && (($valueArray['OriginalField'] == 'quoteComment') || ($valueArray['OriginalField'] == 'paraphraseComment')))
        {
            $subQ = $this->db->queryNoExecute($this->db->selectNoExecute(
                $this->dbFields[$valueArray['OriginalField']][1],
                'resourcemetadataMetadataId'
            ));
            $this->db->formatConditions($this->db->formatFields('resourcemetadataId') . $this->db->inClause($subQ));
            $this->createOptionConditions($valueArray, 'resourcemetadataResourceId');
            $commentSubQ = $this->db->queryNoExecute($this->db->selectNoExecute(
                $this->dbFields[$valueArray['OriginalField']][1],
                [[$this->dbFields[$valueArray['OriginalField']][2] => 'rId']]
            ));
        }
        if (!array_key_exists('Button1', $valueArray) or ($valueArray['Button1'] == 'OR'))
        { // Start a new unionFragments element
            $this->unionResourceIds[] = $this->lastUnionResourceId = $this->dbFields[$valueArray['OriginalField']][2];
            // must let query be created in order to clear any conditions -- then, if necessary, we can remove from the unionFragments array
            if ($commentSubQ)
            {
                $this->unionFragments[$key] = $commentSubQ;
            }
            else
            {
                $this->createOptionConditions($valueArray);
                $this->unionFragments[$key] = $this->db->queryNoExecute($this->db->selectNoExecute(
                    $this->dbFields[$valueArray['OriginalField']][1],
                    [[$this->dbFields[$valueArray['OriginalField']][2] => 'rId']]
                ));
            }
        }
        // Button1 == 'AND' or 'NOT' so use inClause() as a condition rather than union() and append to previous unionFragments array element
        elseif (($valueArray['Button1'] == 'AND') || ($valueArray['Button1'] == 'NOT'))
        {
            $subQ = $this->db->queryNoExecute($this->db->selectNoExecute(
                $this->dbFields[$valueArray['OriginalField']][1],
                $this->dbFields[$valueArray['OriginalField']][2]
            ));
            $lastUFKey = key(array_slice($this->unionFragments, -1, 1, TRUE));
            if ($commentSubQ)
            {
                if ($valueArray['Button1'] == 'AND')
                {
                    $fc = $this->db->formatConditions($this->db->formatFields($this->lastUnionResourceId) .
                        $this->db->inClause($commentSubQ), FALSE, TRUE);
                }
                else
                {
                    $fc = $this->db->formatConditions($this->db->formatFields($this->lastUnionResourceId) .
                        $this->db->inClause($commentSubQ, TRUE), FALSE, TRUE);
                }
            }
            else
            {
                if ($valueArray['Button1'] == 'AND')
                {
                    $fc = $this->db->formatConditions($this->db->formatFields($this->lastUnionResourceId) . 
                    	$this->db->inClause($subQ), FALSE, TRUE);
                }
                else
                {
                    $fc = $this->db->formatConditions($this->db->formatFields($this->lastUnionResourceId) . 
                    	$this->db->inClause($subQ, TRUE), FALSE, TRUE);
                }
            }
            $this->unionFragments[$lastUFKey] .= $fc;
            $this->validSearch = TRUE;
        }
    }
    /**
     * Create the SQL fragment for select queries
     *
     * @param mixed $key
     * @param mixed $valueArray
     */
    private function createSelectCondition($key, $valueArray)
    {
        $commentMK = FALSE;
        // 'metaKeyword' => array('resourcekeywordKeywordId', 'resource_keyword', 'resourcemetadataResourceId'),
        if ($valueArray['OriginalField'] == 'metaKeyword')
        {
            if ($valueArray['Button2'] == 'OR')
            {
                $this->db->formatConditionsOneField($valueArray['Select'], $this->dbFields[$valueArray['OriginalField']][0]);
            }
            else
            {
                foreach ($valueArray['Select'] as $select)
                {
                    $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][0] => $select]);
                    $subQArray[] = $this->db->queryNoExecute($this->db->selectNoExecute(
                        $this->dbFields[$valueArray['OriginalField']][1],
                        'resourcekeywordMetadataId'
                    ));
                }
                foreach ($subQArray as $subQ)
                {
                    $this->db->formatConditions($this->db->formatFields('resourcekeywordMetadataId') . $this->db->inClause($subQ));
                }
            }
            $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][2] => 'IS NOT NULL']);
            $subQ = $this->db->queryNoExecute($this->db->selectNoExecute(
                $this->dbFields[$valueArray['OriginalField']][1],
                'resourcekeywordMetadataId'
            ));
            $this->db->formatConditions($this->db->formatFields('resourcemetadataId') . $this->db->inClause($subQ));
            $this->createOptionConditions($valueArray, 'resourcemetadataResourceId');
            $commentMK = $this->db->queryNoExecute($this->db->selectNoExecute(
                'resource_metadata',
                [[$this->dbFields[$valueArray['OriginalField']][2] => 'rId']]
            ));
        }
        else
        {
            if ($valueArray['Button2'] == 'OR')
            {
                $this->db->formatConditionsOneField($valueArray['Select'], $this->dbFields[$valueArray['OriginalField']][0], FALSE, TRUE, TRUE);
            }
            else
            {
                foreach ($valueArray['Select'] as $select)
                {
                    $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][0] => $select]);
                    $subQArray[] = $this->db->queryNoExecute($this->db->selectNoExecute(
                        $this->dbFields[$valueArray['OriginalField']][1],
                        $this->dbFields[$valueArray['OriginalField']][2]
                    ));
                }
                foreach ($subQArray as $subQ)
                {
                    $fcArray[] = $this->db->formatFields($this->dbFields[$valueArray['OriginalField']][2]) . $this->db->inClause($subQ);
                }
                $this->db->formatConditions(implode(' ' . $this->db->and . ' ', $fcArray));
            }
        }
        if (!array_key_exists('Button1', $valueArray) or ($valueArray['Button1'] == 'OR'))
        { // Start a new unionFragments element
            $this->unionResourceIds[] = $this->lastUnionResourceId = $this->dbFields[$valueArray['OriginalField']][2];
            // must let query be created in order to clear any conditions -- then, if necessary, we can remove from the unionFragments array
            if ($commentMK)
            {
                $this->unionFragments[$key] = $commentMK;
            }
            else
            {
                $this->createOptionConditions($valueArray);
                $this->unionFragments[$key] = $this->db->queryNoExecute($this->db->selectNoExecute(
                    $this->dbFields[$valueArray['OriginalField']][1],
                    [[$this->dbFields[$valueArray['OriginalField']][2] => 'rId']]
                ));
            }
        }
        // Button1 == 'AND' or 'NOT' so use inClause() as a condition rather than union() and append to previous unionFragments array element
        elseif (($valueArray['Button1'] == 'AND') || ($valueArray['Button1'] == 'NOT'))
        {
            if ($valueArray['Button1'] == 'NOT')
            {
                $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][2] => 'IS NOT NULL']);
            }
            $subQ = $this->db->queryNoExecute($this->db->selectNoExecute(
                $this->dbFields[$valueArray['OriginalField']][1],
                $this->dbFields[$valueArray['OriginalField']][2]
            ));
            $lastUFKey = key(array_slice($this->unionFragments, -1, 1, TRUE));
            if ($commentMK)
            {
                if ($valueArray['Button1'] == 'AND')
                {
                    $fc = $this->db->formatConditions($this->db->formatFields($this->lastUnionResourceId) .
                        $this->db->inClause($commentMK), FALSE, TRUE);
                }
                else
                {
                    $fc = $this->db->formatConditions($this->db->formatFields($this->lastUnionResourceId) .
                        $this->db->inClause($commentMK, TRUE), FALSE, TRUE);
                }
            }
            else
            {
                if ($valueArray['Button1'] == 'AND')
                {
                    $fc = $this->db->formatConditions($this->db->formatFields($this->lastUnionResourceId) . 
                    	$this->db->inClause($subQ), FALSE, TRUE);
                }
                else
                {
                    $fc = $this->db->formatConditions($this->db->formatFields($this->lastUnionResourceId) . 
                    	$this->db->inClause($subQ, TRUE), FALSE, TRUE);
                }
            }
            $this->unionFragments[$lastUFKey] .= $fc;
        }
    }
    /**
     * Format comparisons for SQL conditions
     *
     * @param mixed $key
     * @param mixed $valueArray
     */
    private function createComparisonCondition($key, $valueArray)
    {
        switch ($valueArray['Comparison']) {
            case 0: // '='
                $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][0] => $valueArray['Value1']], '=');

                break;
            case 1: // '!='
                $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][0] => $valueArray['Value1']], '!=');

                break;
            case 2: // '<'
                $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][0] => $valueArray['Value1']], '<');

                break;
            case 3: // '>'
                $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][0] => $valueArray['Value1']], '>');

                break;
            case 4: // '<='
                $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][0] => $valueArray['Value1']], '<=');

                break;
            case 5: // '>='
                $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][0] => $valueArray['Value1']], '>=');

                break;
            case 6: // '<...<'
                $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][0] => $valueArray['Value1']], '>');
                $this->db->formatConditions([$this->dbFields[$valueArray['OriginalField']][0] => $valueArray['Value2']], '<');

                break;
            default: // shouldn't ever get here
                break;
        }
        if (!array_key_exists('Button1', $valueArray) or ($valueArray['Button1'] == 'OR'))
        { // Start a new unionFragments element
            $this->unionResourceIds[] = $this->lastUnionResourceId = $this->dbFields[$valueArray['OriginalField']][2];
            $this->createOptionConditions($valueArray);
            // must let query be created in order to clear any conditions -- then, if necessary, we can remove from the unionFragments array
            $this->unionFragments[$key] = $this->db->queryNoExecute($this->db->selectNoExecute(
                $this->dbFields[$valueArray['OriginalField']][1],
                [[$this->dbFields[$valueArray['OriginalField']][2] => 'rId']]
            ));
        }
        // Button1 == 'AND' or 'NOT' so use inClause() as a condition rather than union() and append to previous unionFragments array element
        elseif (($valueArray['Button1'] == 'AND') || ($valueArray['Button1'] == 'NOT'))
        {
            $subQ = $this->db->queryNoExecute($this->db->selectNoExecute(
                $this->dbFields[$valueArray['OriginalField']][1],
                $this->dbFields[$valueArray['OriginalField']][2]
            ));
            $lastUFKey = key(array_slice($this->unionFragments, -1, 1, TRUE));
            if ($valueArray['Button1'] == 'AND')
            {
                $fc = $this->db->formatConditions($this->db->formatFields($this->lastUnionResourceId) . 
                	$this->db->inClause($subQ), FALSE, TRUE);
            }
            else
            {
                $fc = $this->db->formatConditions($this->db->formatFields($this->lastUnionResourceId) . 
                	$this->db->inClause($subQ, TRUE), FALSE, TRUE);
            }
            $this->unionFragments[$lastUFKey] .= $fc;
        }
    }
    /**
     * add other conditions such DOI, URL etc.
     *
     * @param mixed $valueArray
     * @param mixed $resourceId
     */
    private function createOptionConditions($valueArray, $resourceId = FALSE)
    {
        $rId = $resourceId ? $resourceId : $this->lastUnionResourceId;
        $options = unserialize(base64_decode($this->session->getVar("advancedSearch_Options")));
        if (array_search('withDoi', $options) !== FALSE)
        {
            $this->db->formatConditions(['resourceDoi' => ' IS NOT NULL']);
            if ((!array_key_exists('Button1', $valueArray) or ($valueArray['Button1'] == 'OR'))
                && ($valueArray['OriginalField'] != 'title') && ($valueArray['OriginalField'] != 'type'))
            {
                $this->db->leftJoin('resource', 'resourceId', $rId);
            }
        }
        if (array_search('peerReviewed', $options) !== FALSE)
        {
            $this->db->formatConditions(['resourcemiscPeerReviewed' => 'Y']);
            if ((!array_key_exists('Button1', $valueArray) or ($valueArray['Button1'] == 'OR'))
                && ($valueArray['OriginalField'] != 'addedBy')
                && ($valueArray['OriginalField'] != 'editedBy')
                && ($valueArray['OriginalField'] != 'access')
                && ($valueArray['OriginalField'] != 'maturityIndex'))
            {
                $this->db->leftJoin('resource_misc', 'resourcemiscId', $rId);
            }
        }
        if (array_search('withUrl', $options) !== FALSE)
        {
            $this->db->formatConditions(['resourceurlUrl' => ' IS NOT NULL']);
            if ((!array_key_exists('Button1', $valueArray) or ($valueArray['Button1'] == 'OR'))
                && ($valueArray['OriginalField'] != 'note') && ($valueArray['OriginalField'] != 'abstract'))
            {
                $this->db->leftJoin('resource_url', 'resourceurlResourceId', $rId);
            }
        }
    }
    /**
     * Set the subQuery
     *
     * @param mixed $attach
     */
    private function setSubQuery($attach)
    {
        $unions = $this->db->union($this->unionFragments);
        if (!$this->db->ascDesc = GLOBALS::getTempStorage('search_AscDesc')) {
	        $this->db->ascDesc = $this->session->getVar("search_AscDesc");
	    }
        if (!$order = GLOBALS::getTempStorage('search_Order')) {
	        $order = $this->session->getVar("search_Order");
	    }
        if ($attach == 'noAttachment')
        {
            $this->stmt->conditions[] = ['resourceattachmentsId' => ' IS NULL'];
            $this->stmt->joins['resource_attachments'] = ['resourceattachmentsResourceId', 'rId'];
        }
        elseif ($attach == 'withAttachment')
        {
            $this->stmt->conditions[] = (['resourceattachmentsId' => ' IS NOT NULL']);
            $this->stmt->joins['resource_attachments'] = ['resourceattachmentsResourceId', 'rId'];
        }
        switch ($order) {
            case 'title':
                $this->stmt->useBib('rId');
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource'] = ['resourceId', 'rId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'creator':
                $this->stmt->useBib('rId');
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource_creator'] = ['resourcecreatorResourceId', 'rId'];
                $this->stmt->joins['creator'] = ['creatorId', 'resourcecreatorCreatorId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'publisher':
                $this->stmt->useBib('rId');
                $this->stmt->quarantine(FALSE, 'rId', FALSE);
                $this->stmt->joins['resource_misc'] = ['resourcemiscId', 'rId'];
                $this->stmt->joins['publisher'] = ['publisherId', 'resourcemiscPublisher'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'year':
                $this->stmt->useBib('rId');
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource'] = ['resourceId', 'rId'];
                $this->stmt->joins['resource_year'] = ['resourceyearId', 'rId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'timestamp':
                $this->stmt->useBib('rId');
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->joins['resource_timestamp'] = ['resourcetimestampId', 'rId'];
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            case 'attachments':
                $this->stmt->useBib('rId');
                $this->stmt->quarantine(FALSE, 'rId');
                $this->stmt->executeCondJoins();
                $this->db->groupBy(['rId']);
                $this->subQ = $this->db->subQuery($unions, 'u', FALSE);
                $subQuery = $this->db->from . ' ' . $this->subQ;

                return $this->db->selectNoExecuteFromSubQuery(FALSE, ['rId'], $subQuery, FALSE, TRUE, TRUE);
            default:
                break;
        }
    }
    /**
     * Search attachments for search words
     */
    private function searchAttachments()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "list", "FILETOTEXT.php"]));
        $ftt = new FILETOTEXT();
        $excludeIds = $matchIds = [];
        foreach ($this->attachmentSearches as $key => $array)
        {
            $searchArray[$key] = $this->parsePhrase->parse($array, FALSE, TRUE, TRUE);
            if (array_key_exists('Partial', $array))
            {
                $searchArray[$key]['Partial'] = 'on';
            }
        }
        foreach ($searchArray as $key => $arrays)
        {
            foreach ($arrays as $wordKey => $wordArray)
            {
                if (is_array($wordArray))
                {
                    $types[$key][$wordKey] = array_shift($wordArray);
                    $phrases[$key][$wordKey] = array_shift($wordArray);
                }
            }
            if (array_key_exists('Partial', $arrays))
            {
                $this->partials[$key] = TRUE;
            }
            else
            {
                $this->partials[$key] = FALSE;
            }
        }
        $this->db->formatConditions('resourceattachmentsText', ['IS NOT NULL']);
        $resultset = $this->db->select(
            'resource_attachments',
            ['resourceattachmentsId', 'resourceattachmentsResourceId', 'resourceattachmentsHashFilename',
                'resourceattachmentsText']
        );
        $attachments = $texts = [];
        while ($row = $this->db->fetchRow($resultset))
        {
            $texts[$row['resourceattachmentsHashFilename']] = $row['resourceattachmentsText'];
        }
        foreach ($attachments as $row)
        {
            // NB Converting files to text takes time -- searches below are fast.
            if (!array_key_exists($row['resourceattachmentsHashFilename'], $texts) || $texts[$row['resourceattachmentsHashFilename']] == '')
            {
                continue;
            }
            // For each search, we search on NOT and EXACTNOT first. If the search is true, then there is no need to do the other searches.
            foreach ($types as $key1 => $typeArray)
            {
                foreach ($typeArray as $key2 => $type)
                {
                    if (($type == 'NOT') || ($type == 'exactNOT') ||
                        (array_key_exists('Button', $this->attachmentSearches[$key1]) && ($this->attachmentSearches[$key1]['Button'] == 'NOT')))
                    {
                        $phrase = $phrases[$key1][$key2];
                        if ($this->partials[$key1])
                        {
                            // Escape the user input only for EXACT phrases
                            // and unescape the jokers used by the advanced search syntax
                            // 0 or more (lazy) - asterisk (*)
                            // 1 character - question mark (?)
                            if ($type == 'exactNOT')
                            {
                                $pattern = \UTF8\mb_explode("*", $phrase);
                                foreach ($pattern as $k1 => $p1)
                                {
                                    $p1 = \UTF8\mb_explode("?", $p1);

                                    foreach ($p1 as $k2 => $p2)
                                    {
                                        $p2 = preg_quote($p2, '/');
                                    }
                                    $pattern[$k1] = implode(".?", $p1);
                                }
                                $pattern = implode(".*", $pattern);
                            }
                            else
                            {
                                $pattern = preg_replace(["/\\*/", "/\\?/"], [".*", "."], $phrase);
                            }
                            if (preg_match("/$pattern/iu", $texts[$row['resourceattachmentsHashFilename']]) === 1)
                            {
                                $excludeIds[$key1][] = $row['resourceattachmentsResourceId'];
                            }
                        }
                        else
                        {
                            // Escape the user input
                            if ($type == 'exactNOT')
                            {
                                $pattern = preg_quote($phrase, '/');
                            }
                            else
                            {
                                $pattern = preg_replace(["/\\*/", "/\\?/"], [".*", "."], $phrase);
                            }
                            if (preg_match("/\\b$pattern\\b/iu", $texts[$row['resourceattachmentsHashFilename']]) === 1)
                            {
                                $excludeIds[$key1][] = $row['resourceattachmentsResourceId'];
                            }
                        }
                    }
                }
                foreach ($typeArray as $key2 => $type)
                { // for now, we ignore AND and OR etc. and their position. If a match is found, break the loop
                    if (($type == 'NOT') || ($type == 'exactNOT'))
                    {
                        continue;
                    }
                    $phrase = $phrases[$key1][$key2];
                    if ($this->partials[$key1])
                    {
                        // Escape the user input only for EXACT phrases
                        // and unescape the jokers used by the advanced search syntax
                        // 0 or more (lazy) - asterisk (*)
                        // 1 character - question mark (?)
                        if (($type == 'exactAND') || ($type == 'exactOR'))
                        {
                            $pattern = \UTF8\mb_explode("*", $phrase);
                            foreach ($pattern as $k1 => $p1)
                            {
                                $p1 = \UTF8\mb_explode("?", $p1);

                                foreach ($p1 as $k2 => $p2)
                                {
                                    $p2 = preg_quote($p2, '/');
                                }
                                $pattern[$k1] = implode(".?", $p1);
                            }
                            $pattern = implode(".*", $pattern);
                        }
                        else
                        {
                            $pattern = preg_replace(["/\\*/", "/\\?/"], [".*", "."], $phrase);
                        }
                        if (preg_match("/$pattern/iu", $texts[$row['resourceattachmentsHashFilename']]) === 1)
                        {
                            $matchIds[$key1][] = $row['resourceattachmentsResourceId'];
                            $this->common->attachmentHashnames[] = $row['resourceattachmentsId'];
                        }
                    }
                    else
                    {
                        // Escape the user input if EXACT phrase
                        if (($type == 'exactAND') || ($type == 'exactOR'))
                        {
                            $pattern = preg_quote($phrase, '/');
                        }
                        else
                        { // use as wildcards
                            $pattern = preg_replace(["/\\*/", "/\\?/"], [".*", "."], $phrase);
                        }
                        if (preg_match("/\\b$pattern\\b/iu", $texts[$row['resourceattachmentsHashFilename']]) === 1)
                        {
                            $matchIds[$key1][] = $row['resourceattachmentsResourceId'];
                            $this->common->attachmentHashnames[] = $row['resourceattachmentsId'];
                        }
                    }
                }
            }
        }
        foreach ($matchIds as $key => $array)
        {
            $matchIds[$key] = array_unique($array);
        }
        foreach ($excludeIds as $key => $array)
        {
            $excludeIds[$key] = array_unique($array);
        }
        // Remove duplicates
        $matchIds = $this->removeArrayDuplicates($matchIds);
        $excludeIds = $this->removeArrayDuplicates($excludeIds);
        // An ID in $excludeIds cannot be anywhere in $matchIds
        $excludes = [];
        if (!empty($matchIds) && !empty($excludeIds))
        {
            foreach ($excludeIds as $array)
            {
                foreach ($array as $id)
                {
                    $excludes[] = $id;
                }
            }
            $excludes = array_unique($excludes);
            $temp = $matchIds;
            foreach ($temp as $key1 => $array)
            {
                foreach ($array as $id)
                {
                    if (($key2 = array_search($id, $excludes)) !== FALSE)
                    {
                        unset($matchIds[$key1][$key2]);
                        if (empty($matchIds[$key1]))
                        {
                            unset($matchIds[$key1]);
                        }
                    }
                }
            }
        }

        return [$matchIds, $excludeIds, $this->partials];
    }
    /**
     * Remove multidimensional array duplicates
     *
     * @param mixed $input
     *
     * @return mixed
     */
    private function removeArrayDuplicates($input)
    {
        $temp1 = $input;
        $temp2 = [];
        foreach ($temp1 as $key1 => $array)
        {
            foreach ($array as $key2 => $id)
            {
                if (array_search($id, $temp2) !== FALSE)
                {
                    unset($input[$key1][$key2]);
                    if (empty($input[$key1]))
                    {
                        unset($input[$key1]);
                    }
                }
                else
                {
                    $temp2[] = $id;
                }
            }
        }

        return $input;
    }
    /**
     * write input to session
     */
    private function writeSession()
    {
        // First, write all input with 'advancedSearch_' prefix to session
        foreach ($this->vars as $key => $value)
        {
            if (preg_match("/^advancedSearch_Word_/u", $key))
            {
                if (!trim($value))
                {
                    continue;
                }
                $key = str_replace('advancedSearch_', '', $key);
                $temp[$key] = trim($value);
            }
            elseif (preg_match("/^advancedSearch_Field_/u", $key))
            {
                $key = str_replace('advancedSearch_', '', $key);
                $temp[$key] = $value;
            }
            elseif (preg_match("/^advancedSearch_Select_/u", $key))
            {
                $key = str_replace('advancedSearch_', '', $key);
                $temp[$key] = base64_encode(serialize($value));
            }
            elseif (preg_match("/^advancedSearch_Comparison_/u", $key))
            {
                $key = str_replace('advancedSearch_', '', $key);
                $temp[$key] = $value;
            }
            elseif (preg_match("/^advancedSearch_Value1_/u", $key))
            {
                $key = str_replace('advancedSearch_', '', $key);
                $temp[$key] = $value;
            }
            elseif (preg_match("/^advancedSearch_Value2_/u", $key))
            {
                $key = str_replace('advancedSearch_', '', $key);
                $temp[$key] = $value;
            }
            elseif (preg_match("/^advancedSearch_Button1_/u", $key))
            {
                $key = str_replace('advancedSearch_', '', $key);
                $temp[$key] = $value;
            }
            elseif (preg_match("/^advancedSearch_Button2_/u", $key))
            {
                $key = str_replace('advancedSearch_', '', $key);
                $temp[$key] = $value;
            }
            elseif (preg_match("/^advancedSearch_Partial_/u", $key))
            {
                $key = str_replace('advancedSearch_', '', $key);
                $temp[$key] = $value;
            }
        }
        if (array_key_exists('advancedSearch_BibId', $this->vars) && $this->vars['advancedSearch_BibId'])
        {
            $temp['BibId'] = $this->vars['advancedSearch_BibId'];
        }
        if (array_key_exists('advancedSearch_Options', $this->vars))
        {
            $temp['Options'] = base64_encode(serialize($this->vars['advancedSearch_Options']));
        }
        if (array_key_exists('advancedSearch_Order', $this->vars) && $this->vars['advancedSearch_Order'])
        {
            $temp['Order'] = $this->vars['advancedSearch_Order'];
        }
        if (array_key_exists('advancedSearch_AscDesc', $this->vars) && $this->vars['advancedSearch_AscDesc'])
        {
            $temp['AscDesc'] = $this->vars['advancedSearch_AscDesc'];
        }
        $this->session->clearArray("advancedSearch");
        if ($this->browserTabID) {
        	GLOBALS::unsetTempStorage(['advancedSearch']);
        }
        if (!empty($temp))
        {
            $this->session->writeArray($temp, 'advancedSearch', TRUE);
            $this->session->setVar("search_Order", $temp['Order']);
            $this->session->setVar("search_AscDesc", $temp['AscDesc']);
			if ($this->browserTabID) {
				GLOBALS::setTempStorage(['advancedSearch' => $temp, 'search_Order' => $temp['Order'], 'search_AscDesc' => $temp['AscDesc']]);
			}
        }
    }
    /**
     * validate user input - method, word and field are required.
     *
     * Input comes either from form input or, when paging, from the session.
     */
    private function checkInput()
    {
        $this->writeSession();
        if ($this->browserTabID) {
	        $this->input = GLOBALS::getTempStorage('advancedSearch');
	        if (!is_array($this->input)) {
	        	$this->input = [];
	        }
	    }
        else {
	        $this->input = $this->session->getArray("advancedSearch");
	    }
        for ($i = 1; $i <= 50; $i++)
        {
            if (!array_key_exists("Field_$i", $this->input))
            {
                continue;
            }
            if (array_key_exists("Comparison_$i", $this->input) && ($i == 1))
            {
                if ($this->input["Comparison_$i"] == 6)
                { // '<...<'
                    if (array_key_exists("Value1_$i", $this->input) && trim($this->input["Value1_$i"])
                        && array_key_exists("Value2_$i", $this->input) && trim($this->input["Value2_$i"]))
                    {
                        continue;
                    }
                    else
                    {
                        $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
                    }
                }
                else
                {
                    if (array_key_exists("Value1_$i", $this->input) && trim($this->input["Value1_$i"]))
                    {
                        continue;
                    }
                    else
                    {
                        $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
                    }
                }

                continue;
            }
            if (array_key_exists("Word_$i", $this->input) && trim($this->input["Word_$i"]))
            {
                continue;
            }
            elseif (array_key_exists("Select_$i", $this->input) && trim($this->input["Select_$i"]))
            {
                continue;
            }
            elseif ($i == 1)
            {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
            }
        }
        //$this->badInput->close(\HTML\p('still working on it . . .', 'error'), $this, 'init');
    }
    /**
     * Format creators returned from database
     *
     * @param int $id
     *
     * @return string
     */
    private function formatCreators($id)
    {
        $this->db->formatConditions(['creatorId' => $id]);
        $row = $this->db->selectFirstRow('creator', ["creatorSurname", "creatorInitials", "creatorFirstname", "creatorPrefix"]);
        if ($row['creatorPrefix'])
        {
            $name = $row['creatorPrefix'] . ' ' . $row['creatorSurname'];
        }
        else
        {
            $name = $row['creatorSurname'];
        }
        if ($row['creatorFirstname'])
        {
            $name .= ', ' . $row['creatorFirstname'] . ' ';
            if ($row['creatorInitials'])
            {
                $name .= ' ' . str_replace(' ', '.', $row['creatorInitials']) . '.';
            }
        }
        elseif ($row['creatorInitials'])
        {
            $name .= ', ' . str_replace(' ', '.', $row['creatorInitials']) . '.';
        }

        return \HTML\nlToHtml(trim($name));
    }
    /**
     * Format publishers returned from database
     *
     * @param int $id
     *
     * @return string
     */
    private function formatPublishers($id)
    {
        $this->db->formatConditions(['publisherId' => $id]);
        $row = $this->db->selectFirstRow('publisher', ["publisherName", "publisherLocation"]);
        if ($row['publisherLocation'])
        {
            return \HTML\nlToHtml($row['publisherName'] .
            ": " . $row['publisherLocation']);
        }
        else
        {
            return \HTML\nlToHtml($row['publisherName']);
        }
    }
    /**
     * Format collections returned from database
     *
     * @param int $id
     *
     * @return string
     */
    private function formatCollections($id)
    {
        $this->db->formatConditions(['collectionId' => $id]);
        $row = $this->db->selectFirstRow('collection', ["collectionTitle"]);

        return \HTML\nlToHtml($row['collectionTitle']);
    }
    /**
     * Format subcategories returned from database
     *
     * @param int $id
     *
     * @return string
     */
    private function formatSubcategories($id)
    {
        $this->db->formatConditions(['subcategoryId' => $id]);
        $row = $this->db->selectFirstRow('subcategory', ["subcategorySubcategory"]);

        return \HTML\nlToHtml($row['subcategorySubcategory']);
    }
    /**
     * Format categories returned from database
     *
     * @param int $id
     *
     * @return string
     */
    private function formatCategories($id)
    {
        $this->db->formatConditions(['categoryId' => $id]);
        $row = $this->db->selectFirstRow('category', ["categoryCategory"]);

        return \HTML\nlToHtml($row['categoryCategory']);
    }
    /**
     * Format keywords returned from database
     *
     * @param int $id
     *
     * @return string
     */
    private function formatKeywords($id)
    {
        $this->db->formatConditions(['keywordId' => $id]);
        $row = $this->db->selectFirstRow('keyword', ["keywordKeyword"]);

        return \HTML\nlToHtml($row['keywordKeyword']);
    }
    /**
     * Format usertags returned from database
     *
     * @param int $id
     *
     * @return string
     */
    private function formatUsertags($id)
    {
        $this->db->formatConditions(['usertagsId' => $id]);
        $row = $this->db->selectFirstRow('user_tags', ["usertagsTag"]);

        return \HTML\nlToHtml($row['usertagsTag']);
    }
    /**
     * Format languages returned from database
     *
     * @param int $id
     *
     * @return string
     */
    private function formatLanguages($id)
    {
        $this->db->formatConditions(['languageId' => $id]);
        $row = $this->db->selectFirstRow('language', ["languageLanguage"]);

        return \HTML\nlToHtml($row['languageLanguage']);
    }
    /**
     * Format tags returned from database
     *
     * @param int $id
     *
     * @return string
     */
    private function formatTags($id)
    {
        $this->db->formatConditions(['tagId' => $id]);
        $row = $this->db->selectFirstRow('tag', ["tagTag"]);

        return \HTML\nlToHtml($row['tagTag']);
    }
    /**
     * Format usernames returned from database
     *
     * @param int $id
     *
     * @return string
     */
    private function formatUsernames($id)
    {
        $this->db->formatConditions(['usersId' => $id]);
        $row = $this->db->selectFirstRow('users', ["usersUsername", "usersFullname"]);
        if ($row['usersFullname'])
        {
            return \HTML\nlToHtml($row['usersUsername'] .
            ' [' . $row['usersFullname'] . ']');
        }
        else
        {
            return \HTML\nlToHtml($row['usersUsername']);
        }
    }
}
