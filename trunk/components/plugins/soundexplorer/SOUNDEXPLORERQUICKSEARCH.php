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
 *	SOUND EXPLORER SEARCH class
 *
 *	Quickly search database for use with Sound Explorer plugin
 */
class SOUNDEXPLORERQUICKSEARCH
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $common;
    private $session;
    private $keyword;
    private $keywords;
    private $input;
    private $parsePhrase;
    private $words = '';
    private $typeArray;
    private $joinResourceId;
    private $tAlias = 1;
    private $execCond = [];
    private $execJoin = [];
    private $orderedJoins = [];
    private $foundResources = [];

    public function __construct()
    {
        include_once("core/messages/PLUGINMESSAGES.php");
        
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->pluginmessages = new PLUGINMESSAGES('soundexplorer', 'soundexplorerMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->common = FACTORY_LISTCOMMON::getInstance();
        $this->session = FACTORY_SESSION::getInstance();


        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->parsePhrase = FACTORY_PARSEPHRASE::getInstance();
    }
    /**
     * display form options
     *
     * @param false|int $id
     *
     * @return string
     */
    public function display($id = FALSE)
    {
        ///First check, do we have resources?
        if (!$this->common->resourcesExist()) {
            return;
        }
        $pString = FORM\formHeader("soundexplorer_seStoreSearch");
        $pString .= FORM\hidden("method", "process");
        if ($id) {
            $pString .= FORM\hidden('sepluginId', $id);
        }
        $pString .= HTML\tableStart();
        $pString .= HTML\trStart();
        $label = $this->session->issetVar("seplugin_Label") ? htmlspecialchars(stripslashes($this->session->getVar("seplugin_Label")), ENT_QUOTES | ENT_HTML5) : FALSE;
        $pString .= HTML\td(FORM\textInput(
            $this->pluginmessages->text("seLabel"),
            "seplugin_Label",
            $label,
            20
        ));
        $fields = $this->searchFields();
        $this->makeRadioButtons('Field');
        $pString .= HTML\td($this->makeFormMultiple($fields));
        $this->radioButtons = FALSE;
        $word = $this->session->issetVar("seplugin_Word") ?
            htmlspecialchars(stripslashes($this->session->getVar("seplugin_Word")), ENT_QUOTES | ENT_HTML5) : FALSE;
        $hint = BR . HTML\span($this->coremessages->text("hint", "wordLogic"), 'hint');
        $pString .= HTML\td(FORM\textInput(
            $this->coremessages->text("search", "word"),
            "seplugin_Word",
            $word,
            40
        ) . $hint);
        $selectedArray = ['sine' => 'sine', 'square' => 'square', 'triangle' => 'triangle'];
        $sound = $this->session->issetVar("seplugin_Sound") ? $this->session->getVar("seplugin_Sound") : 'sine';
        $js = 'onClick="seTestSound()"';
        $pString .= HTML\td(FORM\selectedBoxValue($this->pluginmessages->text("seSound"), "seplugin_Sound", $selectedArray, $sound, 1, FALSE, $js));
        $selectedArray = ['enabled' => $this->pluginmessages->text("seEnabled"), 'disabled' => $this->pluginmessages->text("seDisabled")];
        if (!$id) {
            $status = 'enabled';
        } else {
            $status = $this->session->getVar("seplugin_SearchStatus") == 'enabled' ? 'enabled' : 'disabled';
        }
        $pString .= HTML\td(FORM\selectedBoxValue($this->pluginmessages->text("seSearchStatus"), "seplugin_SearchStatus", $selectedArray, $status, 1));
        if ($id) {
            $pString .= HTML\td(FORM\checkBox($this->pluginmessages->text("seSearchDelete"), "seplugin_SearchDelete"));
        }
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\tableStart();
        $pString .= HTML\trStart();
        $note = $this->session->issetVar("seplugin_SearchNote") ?
            htmlspecialchars(stripslashes($this->session->getVar("seplugin_SearchNote")), ENT_QUOTES | ENT_HTML5) : FALSE;
        $pString .= HTML\td(FORM\textareaInput($this->pluginmessages->text("seSearchNote"), "seplugin_SearchNote", $note, 60) .
            HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit"))));
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= FORM\formEnd();

        return $pString;
    }
    /**
     * process
     *
     * @return array|false
     */
    public function process()
    {
        $session = FACTORY_SESSION::getInstance();
        $this->db->formatConditions(['pluginsoundexplorerUserId' => $session->getVar("setup_UserId")]);
        $resultset = $this->db->select('plugin_soundexplorer', ['pluginsoundexplorerLabel', 'pluginsoundexplorerArray']);
        $sounds = [];
        while ($row = $this->db->fetchRow($resultset)) {
            $this->input = unserialize(base64_decode($row['pluginsoundexplorerArray']));
            if ($this->input['SearchStatus'] == 'disabled') {
                continue;
            }
            if ($this->runSearch($row['pluginsoundexplorerLabel'])) {
                if (array_search($this->input['Sound'], $sounds) === FALSE) {
                    $sounds[] = $this->input['Sound'];
                }
            }
        }
        if (!empty($this->foundResources)) {
            $session->setVar("seplugin_FoundResources", $this->foundResources);
        } else {
            $session->delVar("seplugin_FoundResources");
        }
        if (!empty($sounds)) {
            return $sounds;
        } else {
            return FALSE;
        }
    }
    /**
     * validate user input - method, word and field are required
     *
     * Input comes either from form input or, when paging, from the session.
     *
     * @return false|string
     */
    public function checkInput()
    {
        $this->writeSession();
        $type = FALSE;
        if (array_key_exists("seplugin_Field", $this->vars) && $this->vars["seplugin_Field"]) {
            $type = $this->vars["seplugin_Field"];
        } elseif ($this->session->issetVar("seplugin_Field")) {
            $type = $this->session->getVar("seplugin_Field");
        }
        if (!$type) {
            $this->session->setVar("seplugin_Field", "title"); // force to default title search
        }
        if ((array_key_exists("seplugin_Label", $this->vars) && !trim($this->vars["seplugin_Label"]))
        || !$this->session->getVar("seplugin_Label")) {
            return $this->errors->text("inputError", "missing");
        }
        if ((array_key_exists("seplugin_Word", $this->vars) && !trim($this->vars["seplugin_Word"]))
        || !$this->session->getVar("seplugin_Word")) {
            return $this->errors->text("inputError", "missing");
        } else {
            return FALSE;
        }
    }
    /* Return array of database fields to perform the search on
     *
     * @return array
     */
    private function searchFields()
    {
        $fields = ["title" => $this->coremessages->text("search", "title")];
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->selectCount('resource_creator', 'resourcecreatorId'))) {
            $fields['creator'] = $this->coremessages->text("search", "creator");
        }
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->selectCount('resource_text', 'resourcetextId'))) {
            $fields['note'] = $this->coremessages->text("search", "note");
        }
        if ($this->db->fetchOne($this->db->selectCount('resource_text', 'resourcetextId'))) {
            $fields['abstract'] = $this->coremessages->text("search", "abstract");
        }
        $this->db->formatConditions(['resourcemetadataType' => 'q']);
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->selectCount('resource_metadata', 'resourcemetadataId'))) {
            $fields['quote'] = $this->coremessages->text("search", "quote");
        }
        $this->db->formatConditions(['resourcemetadataType' => 'p']);
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->selectCount('resource_metadata', 'resourcemetadataId'))) {
            $fields['paraphrase'] = $this->coremessages->text("search", "paraphrase");
        }
        $this->db->formatConditions(['resourcemetadataType' => 'm']);
        $this->db->limit(1, 0); // Keep memory usage down for large databases
        if ($this->db->fetchOne($this->db->selectCount('resource_metadata', 'resourcemetadataId'))) {
            $fields['musing'] = $this->coremessages->text("search", "musing");
        }
        $this->grabKeywords();
        if (is_array($this->keywords)) {
            $fields['keyword'] = $this->coremessages->text("resources", "keyword");
        }
        // Add any used custom fields
        $subQ = $this->db->subQuery($this->db->selectNoExecute('resource_custom', 'resourcecustomCustomId'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('customId') . $this->db->inClause($subQ));
        $recordset = $this->db->select('custom', ['customId', 'customLabel', 'customSize']);
        while ($row = $this->db->fetchRow($recordset)) {
            if ($row['customSize'] == 'S') {
                $fields['Custom_S_' . $row['customId']] = HTML\dbToFormTidy($row['customLabel']);
            } else {
                $fields['Custom_L_' . $row['customId']] = HTML\dbToFormTidy($row['customLabel']);
            }
        }

        return $fields;
    }
    /**
     * make a multiple select box
     *
     * @param array $array
     *
     * @return string
     */
    private function makeFormMultiple($array)
    {
        $temp = $array;
        if ($selected = $this->session->getVar("seplugin_Field")) {
            $selectedArray = UTF8::mb_explode(",", $selected);
            $pString = FORM\selectedBoxValueMultiple($this->coremessages->text("search", 'field'), "seplugin_Field", $temp, $selectedArray, 2);
        } else {
            // If $type == 'field', select all fields as default
            $pString = FORM\selectFBoxValueMultiple($this->coremessages->text("search", 'field'), "seplugin_Field", $temp, 2);
        }
        $pString .= BR . HTML\span($this->coremessages->text("hint", "multiples"), 'hint') .
            BR;
        $pString .= $this->radioButtons . BR;

        return $pString;
    }
    /**
     * get keywords from database
     */
    private function grabKeywords()
    {
        $this->keywords = $this->keyword->grabAll(
            $this->session->getVar("mywikindx_Bibliography_use"),
            'resource',
            $this->typeArray
        );
    }
    /**
     * Create radio buttons for AND and OR
     *
     * @param string $type
     */
    private function makeRadioButtons($type)
    {
        $type = 'seplugin_' . $type . 'Method';
        if ($this->session->getVar($type) == 'AND') {
            $pString = HTML\span(FORM\radioButton(FALSE, $type, 'OR') . " OR", "small") . BR;
            $pString .= HTML\span(FORM\radioButton(FALSE, $type, 'AND', TRUE) . " AND", "small");
        }
        // Default
        else {
            $pString = HTML\span(FORM\radioButton(FALSE, $type, 'OR', TRUE) . " OR", "small") .
                BR;
            $pString .= HTML\span(FORM\radioButton(FALSE, $type, 'AND') . " AND", "small");
        }
        $this->radioButtons = $pString;
    }
    /**
     * parse the search word(s)
     *
     * @return bool
     */
    private function parseWord()
    {
        $this->words = $this->parsePhrase->parse($this->input);
        if (!$this->words) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    /**
     * add to SQL conditional statement and add fields to fieldArray for database fields
     */
    private function fieldSql()
    {
        if (!array_key_exists('Field', $this->input)) {
            return;
        }
        $conditionArray = $metaCond = [];
        $fields = UTF8::mb_explode(",", $this->input['Field']);
        $this->joinResourceId = 'resourceId';
        $metadata = FALSE;
        foreach ($fields as $field) {
            if (mb_strpos($field, 'Custom_') === 0) {
                $split = UTF8::mb_explode('_', $field);
                if ($split[1] == 'S') {
                    $searchField = 'resourcecustomShort';
                } else {
                    $searchField = 'resourcecustomLong';
                }
                $cId = $split[2];
                $wc = str_replace(
                    '!WIKINDXFIELDWIKINDX!',
                    $this->db->formatFields($this->tAlias . '.' . $searchField),
                    $this->words
                );
                $this->execCond[] = "($wc)";
                $this->execCond[] = [$this->tAlias . '.' . 'resourcecustomCustomId' => $cId];
                $this->execJoin[$this->tAlias . '.resource_custom']['intField'] = $this->tAlias . '.resourcecustomResourceId';
                $this->execJoin[$this->tAlias . '.resource_custom']['extField'] = $this->joinResourceId;
                ++$this->tAlias;
            } elseif ($field == 'title') {
                $wc = str_replace('!WIKINDXFIELDWIKINDX!', $this->db->formatFields('resourceTitleSort'), $this->words);
                $conditionArray[] = $wc;
            } elseif ($field == 'note') {
                $wc = str_replace('!WIKINDXFIELDWIKINDX!', $this->db->formatFields('resourcetextNote'), $this->words);
                $conditionArray[] = $wc;
                $this->execJoin['resource_text']['intField'] = 'resourcetextId';
                $this->execJoin['resource_text']['extField'] = $this->joinResourceId;
            } elseif ($field == 'abstract') {
                $wc = str_replace('!WIKINDXFIELDWIKINDX!', $this->db->formatFields('resourcetextAbstract'), $this->words);
                $conditionArray[] = $wc;
                $this->execJoin['resource_text']['intField'] = 'resourcetextId';
                $this->execJoin['resource_text']['extField'] = $this->joinResourceId;
            } elseif ($field == 'quote') {
                if (!$metadata) {
                    $wc = str_replace('!WIKINDXFIELDWIKINDX!', $this->db->formatFields('resourcemetadataText'), $this->words);
                    $conditionArray[] = $wc;
                    $metadata = TRUE;
                }
                $metaCond[] = $this->db->formatFields('resourcemetadataType') . $this->db->equal . $this->db->tidyInput('q');
                $this->execJoin['resource_metadata']['intField'] = 'resourcemetadataResourceId';
                $this->execJoin['resource_metadata']['extField'] = $this->joinResourceId;
            } elseif ($field == 'paraphrase') {
                if (!$metadata) {
                    $wc = str_replace('!WIKINDXFIELDWIKINDX!', $this->db->formatFields('resourcemetadataText'), $this->words);
                    $conditionArray[] = $wc;
                    $metadata = TRUE;
                }
                $metaCond[] = $this->db->formatFields('resourcemetadataType') . $this->db->equal . $this->db->tidyInput('p');
                $this->execJoin['resource_metadata']['intField'] = 'resourcemetadataResourceId';
                $this->execJoin['resource_metadata']['extField'] = $this->joinResourceId;
            } elseif ($field == 'musing') {
                if (!$metadata) {
                    $wc = str_replace('!WIKINDXFIELDWIKINDX!', $this->db->formatFields('resourcemetadataText'), $this->words);
                    $conditionArray[] = $wc;
                    $metadata = TRUE;
                }
                $metaCond[] = $this->db->formatFields('resourcemetadataType') . $this->db->equal . $this->db->tidyInput('m');
                $this->execJoin['resource_metadata']['intField'] = 'resourcemetadataResourceId';
                $this->execJoin['resource_metadata']['extField'] = $this->joinResourceId;
            } elseif ($field == 'keyword') {
                $wc = str_replace('!WIKINDXFIELDWIKINDX!', $this->db->formatFields('keywordKeyword'), $this->words);
                $conditionArray[] = $wc;
                $this->execCond[] = $this->db->formatFields('resourcekeywordResourceId') . ' IS NOT NULL';
                $this->execJoin['resource_keyword']['intField'] = 'resourcekeywordResourceId';
                $this->execJoin['resource_keyword']['extField'] = $this->joinResourceId;
                $this->execJoin['keyword']['intField'] = 'keywordId';
                $this->execJoin['keyword']['extField'] = 'resourcekeywordKeywordId';
            } elseif ($field == 'creator') {
                $wc = str_replace('!WIKINDXFIELDWIKINDX!', $this->db->formatFields('creatorSurname'), $this->words);
                $creatorsCond = UTF8::mb_explode(' AND ', $wc);
                if (sizeof($creatorsCond) > 1) {
                    $creatorStmts = $creatorAlias = [];
                    foreach ($creatorsCond as $creatorCond) {
                        $this->db->formatConditions("$creatorCond");
                        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
                        $creatorStmts[] = $this->db->subQuery(
                            $this->db->selectNoExecute(
                                'resource_creator',
                                [['resourcecreatorResourceId' => 'cId' . $this->tAlias]],
                                FALSE,
                                TRUE,
                                TRUE
                            ),
                            $this->tAlias,
                            FALSE
                        );
                        $creatorAlias[] = 'cId' . $this->tAlias;
                        ++$this->tAlias;
                    }
                    $masterCreatorId = $this->db->formatFields(array_shift($creatorAlias));
                    foreach ($creatorAlias as $cAlias) {
                        $creatorCondArray[] = $this->db->equal . $this->db->formatFields($cAlias);
                    }
                    $this->db->formatConditions($masterCreatorId . implode($this->db->and . $masterCreatorId, $creatorCondArray));
                    $creatStmt = $this->db->selectNoExecuteFromSubQuery(
                        FALSE,
                        $masterCreatorId,
                        $this->db->from . implode(', ', $creatorStmts),
                        FALSE,
                        FALSE,
                        TRUE
                    );
                    $this->execCond[] = $this->db->formatFields('resourceId') . $this->db->inClause($creatStmt);
                } else {
                    $conditionArray[] = $wc;
                    $this->execJoin['c.resource_creator']['intField'] = 'c.resourcecreatorResourceId';
                    $this->execJoin['c.resource_creator']['extField'] = $this->joinResourceId;
                    $this->execJoin['creator']['intField'] = 'creatorId';
                    $this->execJoin['creator']['extField'] = 'c.resourcecreatorCreatorId';
                }
            } elseif ($field == 'userTag') {
                $wc = str_replace('!WIKINDXFIELDWIKINDX!', $this->db->formatFields('usertagsTag'), $this->words);
                $conditionArray[] = $wc;
                $this->execCond[] = ['usertagsUserId' => $this->session->getVar("setup_UserId")];
                $this->execJoin['resource_user_tags']['intField'] = 'resourceusertagsResourceId';
                $this->execJoin['resource_user_tags']['extField'] = $this->joinResourceId;
                $this->execJoin['user_tags']['intField'] = 'usertagsId';
                $this->execJoin['user_tags']['extField'] = 'resourceusertagsTagId';
            }
        }
        if (!empty($conditionArray)) {
            $conditionJoin = $this->input['FieldMethod'] == 'OR' ? $this->db->or : $this->db->and;
            $this->execCond[] = ('(' . implode($conditionJoin, array_map([$this, 'addBrackets'], $conditionArray)) . ')');
        }
        if (!empty($metaCond)) {
            $conditionJoin = $this->input['FieldMethod'] == 'OR' ? $this->db->or : $this->db->and;
            $this->execCond[] = ('(' . implode($conditionJoin, array_map([$this, 'addBrackets'], $metaCond)) . ')');
        }
        $this->executeCondJoin();
    }
    /**
     * addBrackets
     *
     * @param mixed $string
     *
     * @return string
     */
    private function addBrackets($string)
    {
        return '(' . $string . ')';
    }
    /**
     * process the conditions and joins for search and select that are defined elsewhere.  We can precisely control the order of joins here
     */
    private function executeCondJoin()
    {
        $this->orderJoins();
        foreach ($this->execCond as $cond) {
            $this->db->formatConditions($cond);
        }
        foreach ($this->orderedJoins as $table => $array) {
            if (array_key_exists('alias', $array)) {
                $this->db->leftJoin([[$array['table'] => $array['alias']]], $array['intField'], $array['extField']);
            } else {
                $this->db->leftJoin($table, $array['intField'], $array['extField']);
            }
        }
        // reset arrays
        $this->orderedJoins = [];
        $this->execCond = [];
        $this->execJoin = [];
    }
    /**
     * Order the joins
     */
    private function orderJoins()
    {
        $tables = ['resource_misc', 'resource_attachments', 'resource', 'resource_creator', 'creator',
            'resource_text', 'resource_category', 'resource_keyword', 'keyword', 'resource_timestamp', 'resource_year',
            'resource_metadata', 'resource_user_tags', 'user_tags', 'resource_custom',
            'user_bibliography_resource', 'publisher', 'resource_language', ];
        foreach ($tables as $tableOrder) {
            foreach ($this->execJoin as $table => $array) {
                $split = UTF8::mb_explode('.', $table);
                if ((sizeof($split) == 2) && ($split[1] == $tableOrder)) {
                    $array['alias'] = $split[0];
                    $array['table'] = $split[1];
                    $this->orderedJoins[$table] = $array;
                } elseif ($table == $tableOrder) {
                    $this->orderedJoins[$table] = $array;
                }
            }
        }
    }
    /**
     * runSearch
     *
     * @param mixed $label
     *
     * @return bool
     */
    private function runSearch($label)
    {
        if (!$this->session->getVar("list_AllIds")) {
            return FALSE;
        }
        if (array_key_exists('order', $this->input)) {
            $order = $this->input['order'];
        } else {
            $order = 'creator';
        }
        if (!$this->parseWord()) {
            return FALSE;
        }
        $this->fieldSql();
        $this->db->formatConditionsOneField(unserialize(base64_decode($this->session->getVar("list_AllIds"))), 'resourceId');
        $resultset = $this->db->select('resource', 'resourceId');
        $found = FALSE;
        while ($row = $this->db->fetchRow($resultset)) {
            $found = TRUE;
            $this->foundResources[$label][] = $row['resourceId'];
        }

        return $found;
    }
    /**
     * write input to session
     */
    private function writeSession()
    {
        // First, write all input with 'search_' prefix to session
        foreach ($this->vars as $key => $value) {
            if (preg_match("/^seplugin_/u", $key)) {
                $key = str_replace('seplugin_', '', $key);
                // Is this a multiple select box input?  If so, multiple choices are written to session as
                // comma-delimited string (no spaces).
                // Don't write any FALSE or '0' values.
                if (is_array($value)) {
                    if (!$value[0] || ($value[0] == $this->coremessages->text("misc", "ignore"))) {
                        unset($value[0]);
                    }
                    $value = implode(",", $value);
                }
                if (!trim($value)) {
                    continue;
                }
                $temp[$key] = trim($value);
            }
        }
        // temp store plugin status (on/off) and plugin database status
        $status = $this->session->getVar("seplugin_On");
        $dbStatus = $this->session->getVar("seplugin_DatabaseCreated");
        $this->session->clearArray("seplugin");
        $this->session->writeArray($temp, 'seplugin');
        $this->session->setVar("seplugin_On", $status);
        $this->session->setVar("seplugin_DatabaseCreated", $dbStatus);
    }
}
