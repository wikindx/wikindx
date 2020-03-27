<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * VIEWBIBTEX View single resource as BibTeX
 *
 * NB Some variables and methods are public for use with the importexportbib plugin -- DO NOT CHANGE!
 */
class VIEWBIBTEX
{
    public $vars; // public for use with importexportbib plugin
    /** array */
	public $rawStringArray = []; // public for use with importexportbib plugin
	public $resourceId; // public for use with importexportbib plugin
	public $customFieldString = FALSE;
    private $db;
    private $errors;
    private $messages;
    private $session;
    private $badInput;
    private $res;
    private $bibConfig;
    private $map;
    private $user;
    private $constants;
    private $startField;
    private $endField;
    /** array */
    private $metadataFields = [];
    /** array */
    private $customIds = [];
    /** array */
    private $titleString = [];
    /** array */
    private $rawString = [];
    /** array */
    private $stringIdArray = [];
    /** array */
    private $strValues = [];
    /** array */
    private $strKeys = [];
    /** array */
    private $rawEntries = [];
    private $userId;
    /** array */
    private $rawCitations = [];
    /** array */
    private $storedBibtexKey = [];
    private $spChPlain;
    private $editionArray;
    private $monthArray;
    /** array */
    private $cite = [];
    private $convertTex = FALSE;
    private $keywordSeparator = ',';
    private $resourceTitle;

    // Constructor
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->config = FACTORY_CONFIG::getInstance();

        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->res = FACTORY_RESOURCECOMMON::getInstance();
        $this->bibConfig = FACTORY_BIBTEXCONFIG::getInstance();
        $this->map = FACTORY_BIBTEXMAP::getInstance();
        $this->user = FACTORY_USER::getInstance();
        // need to use English constants for BibTeX
        $this->constants = FACTORY_CONSTANTS::getFreshInstance(TRUE);
        $this->startField = $this->endField = "\""; // default field enclosures
        $this->userId = $this->session->getVar("setup_UserId");
        // Load bibConfig arrays
        $this->bibConfig->bibtex();
        // load default arrays
        $this->loadArrays();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "exportBibtex"));
    }
    /**
     * display pop-up for a single resource
     */
    public function display()
    {
        if (!array_key_exists('id', $this->vars))
        {
            $this->badInput->closeType = 'closeNoMenu';
            $this->badInput->close($this->messages->text('inputError', 'missing'));
        }
        // START set up environment
        if (!array_key_exists('resubmit', $this->vars))
        {
            // custom field mapping
            foreach ($this->session->getArray("export") as $key => $value)
            {
                if (mb_strpos($key, 'Map_') == 0)
                {
                    $this->vars[$key] = $value;
                }
            }
        }
        $metadataFields = ["Quotation", "QuotationComment", "Paraphrase", "ParaphraseComment", "Musing"];
        foreach ($metadataFields as $field)
        {
            // first time so few values in $this->vars -- check session
            if (!array_key_exists('resubmit', $this->vars) && ($value = $this->session->getVar("export_$field")))
            {
                $this->vars[$field] = $value;
            }
            elseif (!array_key_exists('resubmit', $this->vars))
            {
                $this->vars[$field] = '';
            }
        }
        if (!array_key_exists('resubmit', $this->vars))
        {
            if ($this->session->getVar('export_MergeStored'))
            {
                $this->vars['MergeStored'] = TRUE;
            }
            if ($this->session->getVar('export_UseOriginalCitation'))
            {
                $this->vars['UseOriginalCitation'] = TRUE;
            }
            if ($this->session->getVar('export_ShortString'))
            {
                $this->vars['ShortString'] = TRUE;
            }
            if ($this->session->getVar('export_EncloseField'))
            { // braces
                $this->vars['EncloseField'] = 'B';
            }
            else
            { // double quotes
                $this->vars['EncloseField'] = 'Q';
            }
            if ($this->session->getVar('export_CharacterSet'))
            { // Latin + TeX
                $this->vars['CharacterSet'] = 'T';
            }
            else
            { // UTF-8
                $this->vars['CharacterSet'] = 'U';
            }
            if ($this->session->getVar('export_KeywordSeparator'))
            { // ;
                $this->vars['KeywordSeparator'] = ';';
            }
            else
            { // ,
                $this->vars['KeywordSeparator'] = ',';
            }
        }
        $pString = $this->process();
        $pString = str_replace(LF, BR, $pString);
        $pString .= \HTML\hr();
        // These form elements kept here so the importexportbib plugin can use functions in this class such as options()
        $pString .= \FORM\formHeader("resource_VIEWBIBTEX_CORE");
        $pString .= \FORM\hidden('method', 'display');
        $pString .= \FORM\hidden("id", $this->vars['id']);
        $pString .= $this->options();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Display options
     *
     * @param mixed $cString
     *
     * @return string
     */
    public function options($cString = FALSE)
    {
        $pString = \FORM\hidden("resubmit", TRUE);
        $checked = $this->session->getVar("export_MergeStored") ? TRUE : FALSE;
        $pString .= \HTML\p($this->messages->text("misc", "mergeStored") . "&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, "MergeStored", $checked));
        $checked = $this->session->getVar("export_UseOriginalCitation") ? TRUE : FALSE;
        $pString .= \HTML\p($this->messages->text("misc", "useOriginalCitation") . "&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, "UseOriginalCitation", $checked));
        $checked = $this->session->getVar("export_ShortString") ? TRUE : FALSE;
        $pString .= \HTML\p($this->messages->text("misc", "shortString") . "&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, "ShortString", $checked));
        $pString .= \HTML\tableStart('generalTable borderStyleSolid');
        $pString .= \HTML\trStart();
        if ($this->session->getVar("export_EncloseField"))
        { // TRUE == {...}
            $string = \FORM\radioButton(FALSE, "EncloseField", "Q", FALSE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportQuotes");
            $string .= BR . \FORM\radioButton(FALSE, "EncloseField", "B", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportBraces");
            $pString .= \HTML\td($string);
        }
        else
        { // FALSE == "..."
            $string = \FORM\radioButton(FALSE, "EncloseField", "Q", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportQuotes");
            $string .= BR . \FORM\radioButton(FALSE, "EncloseField", "B", FALSE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportBraces");
            $pString .= \HTML\td($string);
        }
        if ($this->session->getVar("export_CharacterSet"))
        { // TRUE == TeX
            $string = \FORM\radioButton(FALSE, "CharacterSet", "U", FALSE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportCharacterSetUTF");
            $string .= BR . \FORM\radioButton(FALSE, "CharacterSet", "T", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportCharacterSetTex");
            $pString .= \HTML\td($string);
        }
        else
        { // FALSE == UTF-8
            $string = \FORM\radioButton(FALSE, "CharacterSet", "U", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportCharacterSetUTF");
            $string .= BR . \FORM\radioButton(FALSE, "CharacterSet", "T", FALSE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportCharacterSetTex");
            $pString .= \HTML\td($string);
        }
        if ($this->session->getVar("export_KeywordSeparator"))
        { // TRUE == ';'
            $string = \FORM\radioButton(FALSE, "KeywordSeparator", ",", FALSE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportKeywordSeparatorComma");
            $string .= BR . \FORM\radioButton(FALSE, "KeywordSeparator", ";", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportKeywordSeparatorSemicolon");
            $pString .= \HTML\td($string);
        }
        else
        { // FALSE == ';'
            $string = \FORM\radioButton(FALSE, "KeywordSeparator", ",", TRUE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportKeywordSeparatorComma");
            $string .= BR . \FORM\radioButton(FALSE, "KeywordSeparator", ";", FALSE) . "&nbsp;&nbsp;" .
                $this->messages->text("misc", "bibExportKeywordSeparatorSemicolon");
            $pString .= \HTML\td($string);
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        if (!$cString)
        {
            if (!$this->customFieldString)
            { // cf importexportbib plugin
                $pString .= $this->getCustomFields();
            }
            elseif (($this->customFieldString != -1))
            {
                $pString .= $this->customFieldString;
            }
        }
        else
        {
            $pString .= $cString; // comes from importexport bibtex plugin
        }
        // Export metadata fields?
        $pString .= \HTML\p(\HTML\strong($this->messages->text("misc", "exportMetadata1")) . "&nbsp;&nbsp;" .
            $this->messages->text("misc", "exportMetadata2"));
        $pString .= \HTML\tableStart('generalTable borderStyleSolid') . \HTML\trStart();
        $label = $this->messages->text("metadata", "quotes");
        $text = $this->session->getVar("export_Quotation");
        $pString .= \HTML\td(\FORM\textInput($label, "Quotation", $text, 10));
        $label = $this->messages->text("metadata", "quoteComments");
        $text = $this->session->getVar("export_QuotationComment");
        $pString .= \HTML\td(\FORM\textInput($label, "QuotationComment", $text, 10));
        $label = $this->messages->text("metadata", "paraphrases");
        $text = $this->session->getVar("export_Paraphrase");
        $pString .= \HTML\td(\FORM\textInput($label, "Paraphrase", $text, 10));
        $label = $this->messages->text("metadata", "paraphraseComments");
        $text = $this->session->getVar("export_ParaphraseComment");
        $pString .= \HTML\td(\FORM\textInput($label, "ParaphraseComment", $text, 10));
        $label = $this->messages->text("metadata", "musings");
        $text = $this->session->getVar("export_Musing");
        $pString .= \HTML\td(\FORM\textInput($label, "Musing", $text, 10));
        $pString .= \HTML\trEnd() . \HTML\tableEnd();
        $pString .= BR . \FORM\formSubmit($this->messages->text("submit", "Submit"));
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * write session
     */
    public function writeSession()
    {
        if (isset($this->vars['MergeStored']))
        {
            $this->session->setVar('export_MergeStored', $this->vars['MergeStored']);
        }
        else
        {
            $this->session->delVar('export_MergeStored');
        }
        if (isset($this->vars['UseOriginalCitation']))
        {
            $this->session->setVar('export_UseOriginalCitation', $this->vars['UseOriginalCitation']);
        }
        else
        {
            $this->session->delVar('export_UseOriginalCitation');
        }
        if (isset($this->vars['ShortString']))
        {
            $this->session->setVar('export_ShortString', $this->vars['ShortString']);
        }
        else
        {
            $this->session->delVar('export_ShortString');
        }
        if (array_key_exists('EncloseField', $this->vars) && $this->vars['EncloseField'] == 'Q')
        {
            $this->session->setVar('export_EncloseField', FALSE);
            $this->startField = "\"";
            $this->endField = "\"";
        }
        else
        {
            $this->session->setVar('export_EncloseField', TRUE);
            $this->startField = "{";
            $this->endField = "}";
        }
        if (array_key_exists('CharacterSet', $this->vars) && $this->vars['CharacterSet'] == 'U')
        {
            $this->session->setVar('export_CharacterSet', FALSE);
        }
        else
        {
            $this->session->setVar('export_CharacterSet', TRUE);
            $this->convertTex = TRUE;
        }
        if (array_key_exists('KeywordSeparator', $this->vars) && $this->vars['KeywordSeparator'] == ',')
        {
            $this->session->setVar('export_KeywordSeparator', FALSE);
        }
        else
        {
            $this->session->setVar('export_KeywordSeparator', TRUE);
            $this->keywordSeparator = ';';
        }
        $checkDuplicates = [];
        $duplicateMapping = FALSE;
        foreach ($this->vars as $key => $value)
        {
            $split = UTF8::mb_explode("Map_", $key); // custom fields mapping
            if (count($split) == 2)
            {
                $value = trim($value);
                if ($value)
                {
                    $this->session->setVar("export_$key", $value);
                    if (!$duplicateMapping && (array_search(mb_strtolower($value), $checkDuplicates) !== FALSE))
                    {
                        $duplicateMapping = TRUE;
                    }
                    $this->customIds[$split[1]] = $value;
                    $checkDuplicates[] = mb_strtolower($value);
                }
                else
                {
                    $this->session->delVar("export_$key");
                }
            }
        }
        // Check for metadata input and write to session
        $metadataFields = ["Quotation", "QuotationComment", "Paraphrase", "ParaphraseComment", "Musing"];
        foreach ($metadataFields as $field)
        {
            $input = trim($this->vars[$field]);
            if ($input)
            {
                if (($field == 'QuotationComment') || ($field == 'ParaphraseComment'))
                { // may be duplicated
                    $this->session->setVar("export_$field", $input);
                    $this->metadataFields[$field] = $input;
                }
                elseif (array_search(mb_strtolower($input), $checkDuplicates) === FALSE)
                {
                    $this->session->setVar("export_$field", $input);
                    $this->metadataFields[$field] = $input;
                    $checkDuplicates[] = mb_strtolower($input);
                }
                else
                {
                    $duplicateMapping = TRUE;
                }
            }
            else
            {
                $this->session->delVar("export_$field");
            }
        }
        // if duplicate custom mapping found, fail
        if ($duplicateMapping)
        {
            $this->badInput->close($this->errors->text("inputError", "duplicateFieldNames"));
        }
    }
    /**
     * format @STRING strings
     *
     * @return bool
     */
    public function formatRawString()
    {
        if (!empty($this->rawString))
        {
            foreach ($this->titleString as $key => $value)
            {
                if (!array_key_exists($key, $this->rawString))
                {
                    $this->rawString[$key] = $value;
                }
            }
            foreach ($this->rawString as $key => $value)
            {
                $key = preg_replace("/[^a-zA-Z0-9]/u", '', $key);
                if (is_numeric(mb_substr($key, 0)))
                {
                    $key = "string$key";
                }
                $rawString = "@STRING" . '{' . "$key = $value" . '}';
                if (array_search($rawString, $this->rawStringArray) === FALSE)
                {
                    $this->rawStringArray[] = $rawString;
                }
            }

            return TRUE;
        }
        elseif (!empty($this->titleString))
        {
            foreach ($this->titleString as $key => $value)
            {
                if ($this->session->getVar("export_shortString"))
                {
                    $value = $key;
                }
                $key = preg_replace("/[^a-zA-Z0-9]/u", '', $key);
                if (is_numeric(mb_substr($key, 0)))
                {
                    $key = "string$key";
                }
                $rawString = "@STRING" . '{' . "$key = $value" . '}';
                if (array_search($rawString, $this->rawStringArray) === FALSE)
                {
                    $this->rawStringArray[] = $rawString;
                }
            }

            return TRUE;
        }

        return FALSE;
    }
    /**
     * get data from database
     *
     * @param mixed $resultset
     * @param mixed $fp
     *
     * @return string|FALSE
     */
    public function getData($resultset = FALSE, $fp = FALSE)
    {
        $pluginExport = TRUE;
        if (!$resultset)
        {
            $this->resourceId = $this->vars['id'];
            $resultset = $this->res->getResource($this->resourceId);
            $pluginExport = FALSE;
        }
        if (!$this->db->numRows($resultset))
        {
            $this->badInput->close($this->messages->text("resources", "noResult"));
        }
        $resourceIds = $rowTypes = $entryArray = $entryKeys = $howPublishedDone = $howPublished = $dates = $miscField1 = $miscPublishers = [];

        while ($row = $this->db->fetchRow($resultset))
        {
            // will be the case if ideas have been found through a keyword
            if (!$row['resourceId'])
            {
                continue;
            }
            if (array_search($row['resourceId'], $resourceIds))
            {
                continue;
            }

            $resourceIds[] = $row['resourceId'];

            if ($pluginExport)
            {
                $this->resourceId = $row['resourceId'];
            }
            if (!$this->resourceId)
            {
                continue;
            }

            $rowTypes[$this->resourceId]['resourceType'] = $row['resourceType'];
            $rowTypes[$this->resourceId]['resourceField1'] = $row['resourceField1'];
            if ($row['resourcemiscField1'] && (($row['resourceType'] == 'proceedings_article') || ($row['resourceType'] == 'proceedings')))
            {
                $miscField1[$this->resourceId]['resourcemiscField1'] = $row['resourcemiscField1'];
            }
            elseif ($row['resourcemiscPublisher'])
            {
                $miscPublishers[$this->resourceId]['resourcemiscPublisher'] = $row['resourcemiscPublisher'];
            }
            if (array_key_exists('howpublished', $this->map->{$row['resourceType']}) && ($this->map->types[$row['resourceType']] == 'misc'))
            {
                if (array_key_exists($this->map->{$row['resourceType']}['howpublished'], $row))
                {
                    $howPublished[$this->resourceId] = $row[$this->map->{$row['resourceType']}['howpublished']];
                }
                else
                {
                    $howPublished[$this->resourceId] = $this->map->{$row['resourceType']}['howpublished'];
                }
            }
            $this->storedBibtexKey[$this->resourceId] = $row['resourceBibtexKey'];
            $entryArray[$this->resourceId][] = "title = " . $this->convertCharacter($this->formatTitle($row));
            // For book_chapter, 'title' is bibtex 'chapter' and 'collectionTitle' is bibtex 'title'
            if ($row['resourceType'] == 'book_chapter')
            {
                $entryArray[$this->resourceId][] = "chapter = " . $this->startField . $row['resourceTitle'] . $this->endField;
            }

            foreach ($this->map->{$row['resourceType']} as $table => $tableArray)
            {
                if (($table == 'resource_creator') || ($table == 'howpublished') || ($table == 'resource_publisher'))
                {
                    continue;
                }
                foreach ($tableArray as $wkField => $bibField)
                {
                    if (isset($row[$wkField]) && $row[$wkField])
                    {
                        if (($wkField == 'collectionTitle') && isset($row['collectionTitleShort']) && $row['collectionTitleShort'])
                        {
                            $short = $row['collectionTitleShort'];
                            $title = $row['collectionTitle'];
                            $long = preg_quote($title);
                            // preg_quote doesn't escape '/'
                            $long = "/^" . str_replace('/', '\/', $long) . "$/u";
                            if (!array_key_exists($short, $this->titleString))
                            {
                                $this->titleString[$short] = '"' . $title . '"';
                            }
                            $entryArray[$this->resourceId][] = "$bibField = " . $this->convertCharacter2($title, $short, $long);
                        }
                        elseif ($wkField == 'resourcetextUrls')
                        {
                            $urls = unserialize(base64_decode($row[$wkField]));
                            $entryArray[$this->resourceId][] = "$bibField = " . $this->startField . array_shift($urls) . $this->endField;
                        }
                        else
                        {
                            $entryArray[$this->resourceId][] = "$bibField = " . $this->convertCharacter($row[$wkField]);
                        }
                    }
                }
            }
            if ($item = $this->pageFormat($row))
            {
                $entryArray[$this->resourceId][] = "pages = " . $this->convertCharacter($item);
            }
            // Deal with month/day
            if ($row['resourcemiscField3'] && (($row['resourceType'] == 'web_article') || ($row['resourceType'] == 'web_site') ||
                ($row['resourceType'] == 'web_encyclopedia') || ($row['resourceType'] == 'web_encyclopedia_article')))
            {
                $dates[$this->resourceId]['resourcemiscField3'] = $row['resourcemiscField3'];
                if (array_key_exists('resourcemiscField2', $row) && $row['resourcemiscField2'])
                {
                    $dates[$this->resourceId]['resourcemiscField2'] = $row['resourcemiscField2'];
                }
                if (array_key_exists('resourcemiscField5', $row) && $row['resourcemiscField5'])
                {
                    $dates[$this->resourceId]['resourcemiscField5'] = $row['resourcemiscField5'];
                }
                if (array_key_exists('resourcemiscField6', $row) && $row['resourcemiscField6'])
                {
                    $dates[$this->resourceId]['resourcemiscField6'] = $row['resourcemiscField6'];
                }
            }
            if ($this->session->getVar("export_MergeStored") && !empty($this->rawEntries))
            {
                foreach ($this->rawEntries as $key => $value)
                {
                    $entryArray[$this->resourceId][] = $key . " = " . $value;
                }
            }
            unset($row);
        }

        // Get raw data (must be done before getting the key
        if ($this->session->getVar("export_MergeStored"))
        {
            $this->raw($entryArray, $resourceIds);
        }
        // Get creators
        $this->grabNames($rowTypes, $entryArray, $resourceIds);
        // Get notes and abstracts and URLs
        $this->grabNoteAbstractUrl($rowTypes, $entryArray, $howPublishedDone, $resourceIds);
        // Get publisher
        $this->grabPublisher($rowTypes, $entryArray, $miscField1, $miscPublishers);
        unset($miscField1);
        unset($miscPublishers);
        // 'howpublished'
        $this->grabHowPublished($entryArray, $howPublishedDone, $howPublished);
        unset($howPublishedDone);
        unset($howPublished);
        // attachments
        $this->grabAttachments($entryArray, $resourceIds);
        // keywords
        $this->grabKeywords($entryArray, $resourceIds);
        // custom fields
        $this->grabCustomFields($entryArray, $resourceIds);
        // dates
        $this->grabDates($entryArray, $dates);
        unset($dates);
        // Deal with metadata
        $this->grabMetadata($entryArray, $resourceIds);
        // Get key
        $this->citeFormat($rowTypes, $entryKeys, $resourceIds);
        unset($rowTypes);
        unset($resourceIds);

        // Write entries to file or concatenate a string
        if ($fp)
        {
            foreach ($entryArray as $rId => $array)
            {
                $success = (FALSE !== fwrite($fp, $entryKeys[$rId] . implode(",\n\t", $array) . "\n}\n" . LF));
            }

            return $success;
        }
        else
        {
            $pString = '';

            foreach ($entryArray as $rId => $array)
            {
                $pString .= $entryKeys[$rId] . implode(",\n\t", $array) . "\n}\n" . LF;
            }
        }

        unset($entryKeys);

        if (!empty($pString))
        {
            return $pString;
        }
        else
        {
            return TRUE;
        }
    }
    /**
     * Check if there any custom fields in the SQL set and provide options to map these to bibtex fields
     *
     * @return string|FALSE
     */
    private function getCustomFields()
    {
        if (!$this->db->numRows($this->db->select('custom', 'customId')))
        {
            return FALSE;
        }
        $this->db->leftJoin('resource_custom', 'resourcecustomCustomId', 'customId');
        $this->db->formatConditions(['resourcecustomResourceId' => $this->resourceId]);
        $recordset = $this->db->select('custom', ['resourcecustomCustomId', 'customLabel', 'customSize', 'resourcecustomShort',
            'resourcecustomLong', 'resourcecustomAddUserIdCustom', 'resourcecustomEditUserIdCustom', ]);
        while ($row = $this->db->fetchRow($recordset))
        {
            if ($row['resourcecustomCustomId'])
            {
                $customLabels[$row['resourcecustomCustomId']] = stripslashes($row['customLabel']);
            }
        }
        if (!isset($customLabels) || empty($customLabels))
        {
            return FALSE;
        }
        $customLabels = array_unique($customLabels);
        $pString = \HTML\p(\HTML\strong($this->messages->text("misc", "customFieldMap")) .
            ' ' . $this->messages->text("misc", "customFieldMap2"));
        foreach ($customLabels as $id => $label)
        {
            $text = $this->session->getVar("export_Map_$id");
            $pString .= \HTML\p(\FORM\textInput($label, "Map_$id", $text));
        }

        return $pString;
    }
    /**
     * Process and display
     *
     * @return string
     */
    private function process()
    {
        $this->writeSession();
        $pString = $this->getData();
        if ($this->formatRawString())
        {
            return implode(LF, $this->rawStringArray) . LF . LF . $pString;
        }

        return $pString;
    }
    /**
     * Deal with any custom field mappings
     *
     * @param mixed $entryArray
     * @param mixed $rIds
     */
    private function grabCustomFields(&$entryArray, $rIds)
    {
        if (!empty($this->customIds))
        {
            $this->db->formatConditionsOneField(array_keys($this->customIds), 'resourcecustomCustomId');
            $this->db->formatConditionsOneField($rIds, 'resourcecustomResourceId');
            $recordSet = $this->db->select(
                'resource_custom',
                ['resourcecustomResourceId', 'resourcecustomCustomId', 'resourcecustomLong', 'resourcecustomShort']
            );
            while ($row = $this->db->fetchRow($recordSet))
            {
                if ($row['resourcecustomShort'])
                {
                    $entryArray[$row['resourcecustomResourceId']][] = $this->customIds[$row['resourcecustomCustomId']] . " = " .
                        $this->convertCharacter($row['resourcecustomShort']);
                }
                if ($row['resourcecustomLong'])
                {
                    $entryArray[$row['resourcecustomResourceId']][] = $this->customIds[$row['resourcecustomCustomId']] . " = " .
                        $this->convertCharacter(\HTML\stripHtml($row['resourcecustomLong']));
                }
            }
        }
    }
    /**
     * Grab how published field
     */
    private function grabHowPublished(&$entryArray, &$howPublishedDone, &$howPublished)
    {
        foreach ($howPublished as $rId => $hp)
        {
            if (!array_key_exists($rId, $howPublishedDone))
            {
                $entryArray[$rId][] = "howpublished = " . $this->convertCharacter(ucfirst($hp));
            }
        }
    }
    /**
     * grab attachments
     *
     * @param mixed $entryArray
     * @param mixed $rIds
     */
    private function grabAttachments(&$entryArray, $rIds)
    {
        $path = $this->config->WIKINDX_BASE_URL . str_replace('index.php', WIKINDX_DIR_DATA_ATTACHMENTS . '/', $_SERVER['PHP_SELF']);
        $files = [];
        $this->db->formatConditionsOneField($rIds, 'resourceattachmentsResourceId');
        $resultset = $this->db->select(
            'resource_attachments',
            ['resourceattachmentsResourceId', 'resourceattachmentsHashFilename', 'resourceattachmentsFileType', 'resourceattachmentsFileName']
        );
        while ($row = $this->db->fetchRow($resultset))
        {
            $array = [];
            $array[] = preg_replace('/[^\da-z]/iu', '', $this->resourceTitle) . '-' . $row['resourceattachmentsFileName'];
            $array[] = $path . $row['resourceattachmentsHashFilename'];
            $split = UTF8::mb_explode('/', $row['resourceattachmentsFileType']);
            $array[] = $split[1];
            $files[$row['resourceattachmentsResourceId']][] = implode(':', $array);
            unset($row);
        }
        foreach ($files as $rId => $fileArray)
        {
            $entryArray[$rId][] = "file = " . $this->startField . implode(';', $fileArray) . $this->endField;
        }
    }
    /**
     * grab any stored data and @strings for this resource from import_raw
     *
     * @param mixed $entryArray
     * @param mixed $rIds
     */
    private function raw(&$entryArray, $rIds)
    {
        $this->db->formatConditionsOneField($rIds, 'importrawId');
        $this->db->formatConditions(['importrawImportType' => 'bibtex']);
        $recordset = $this->db->select('import_raw', ['importrawId', 'importrawText', 'importrawStringId', 'importrawImportType']);
        if (!$this->db->numRows($recordset))
        {
            return;
        }
        $stringIdFound = FALSE;
        $rawEntries = [];
        while ($row = $this->db->fetchRow($recordset))
        {
            $rawEntries[$row['importrawId']] = unserialize(base64_decode($row['importrawText']));
            foreach ($this->stringIdArray as $stringId)
            {
                if ($row['importrawStringId'] == $stringId)
                {
                    $stringIdFound = TRUE;

                    break;
                }
            }
            if ($stringIdFound)
            {
                break;
            }
            if (array_search($row['importrawStringId'], $this->stringIdArray) === FALSE)
            {
                $this->db->formatConditions(['bibtexstringId' => $row['importrawStringId']]);
                $rawString = unserialize(base64_decode($this->db->selectFirstField('bibtex_string', 'bibtexstringText'))) . "\n" . LF;
                if ($rawString)
                {
                    preg_match_all("/@STRING{(.*)}/u", $rawString, $strings);
                }
                foreach ($strings[1] as $string)
                {
                    $split = UTF8::mb_explode("=", $string, 2);
                    $key = trim($split[0]);
                    if (array_search($key, $this->rawString) === FALSE)
                    {
                        $this->rawString[$key] = trim($split[1]);
                    }
                }
                $this->orderArrayByKeyLength();
                foreach ($this->rawString as $key => $value)
                {
                    if (preg_match("/^[\"{](.*)[\"}]$/u", trim($value), $matches))
                    {
                        $value = $matches[1];
                    }
                    $value = "/^" . preg_quote($value, "/") . "$/u";
                    if (!array_search($value, $this->strValues))
                    {
                        $this->strValues[] = $value;
                    }
                    if (!array_search($key, $this->strKeys))
                    {
                        $this->strKeys[] = $key;
                    }
                }
                $this->stringIdArray[] = $row['importrawStringId'];

                break;
            }
            unset($row);
        }
        $this->strValues = array_unique($this->strValues);
        $this->strKeys = array_unique($this->strKeys);
        foreach ($rawEntries as $rId => $rawEntry)
        {
            $rawEntry = UTF8::mb_explode(LF, $rawEntry);
            array_pop($rawEntry); // always an empty array at end so get rid of it.
            foreach ($rawEntry as $entries)
            {
                $entry = UTF8::mb_explode("=", $entries, 2);
                if (!trim($entry[1]))
                {
                    continue;
                }
                if (trim($entry[0]) == 'citation')
                {
                    $this->rawCitations[$rId] = trim($entry[1]);
                }
                else
                {
                    $key = trim($entry[0]);
                    $value = trim($entry[1]);
                    if ($this->startField == "{")
                    {
                        $match = "/^(\")(.*)(\")$/u";
                        if (preg_match($match, $value, $matches))
                        {
                            $value = $this->startField . $matches[2] . $this->endField;
                        }
                    }
                    elseif ($this->startField == "\"")
                    {
                        $match = "/^(\\{)(.*)(\\})$/u";
                        if (preg_match($match, $value, $matches))
                        {
                            $value = $this->startField . $matches[2] . $this->endField;
                        }
                    }
                    if (($key == 'month') && (mb_substr($value, 0, 1) == '"'))
                    {
                        $value = mb_substr($value, 1);
                        $value = mb_substr($value, 0, -1);
                    }
                    $this->rawEntries[$key] = $value;
                }
            }
        }
    }
    /**
     * load default arrays
     */
    private function loadArrays()
    {
        $temp = $this->bibConfig->bibtexSpCh;
        // We need to remove first two elements of array as we don't want to convert '{' and '}'. Because the keys of the original
        // array are hexadecimal characters, this means array_shift will treat them as integer keys and will modify the array
        // keys - we don't want this!
        $index = 0;
        foreach ($temp as $key => $value)
        {
            if ($index >= 2)
            {
                $this->spCh[$key] = $value;
            }
            $index++;
        }
        $this->spChPlain = $this->bibConfig->bibtexSpChPlain;
        $this->constants->convertNumbers();
        $this->editionArray = $this->constants->cardinalToOrdinalWord();
        $this->monthArray = $this->constants->monthToLongName();
    }
    /**
     * store surname of first author for BibTeX citation purposes.
     *
     * $this->cite[] holds existing citations which must not be repeated.
     * Ensure $cite has no spaces, commas etc.
     *
     * @param array $rowTypes
     * @param array $entryKeys
     * @param int $rIds
     */
    private function citeFormat(&$rowTypes, &$entryKeys, $rIds)
    {
        if ($this->session->getVar("export_UseOriginalCitation") || !$this->session->getVar("setup_UseWikindxKey"))
        {
            foreach ($rIds as $rId)
            {
                if (($this->session->getVar("export_UseOriginalCitation")) &&
                    array_key_exists($rId, $this->rawCitation) && (array_search($this->rawCitation[$rId], $this->cite) === FALSE))
                {
                    $this->cite[] = trim($this->rawCitation[$rId]);
                    $cite = trim($this->rawCitation[$rId]);
                }
                elseif (!$this->session->getVar("setup_UseWikindxKey"))
                {
                    $cite = $this->storedBibtexKey[$rId];
                }
                else
                { // just in case
                    $cite = $rId;
                }
                if (($rowTypes[$rId]['resourceType'] == 'thesis') && (($rowTypes[$rId]['resourceField1'] == 'doctoral') ||
                    ($rowTypes[$rId]['resourceField1'] == 'PhD') || ($rowTypes[$rId]['resourceField1'] == 'EdD')))
                {
                    $entryKeys[$rId] = "@" . 'phdthesis' . "{" . "$cite,\n\t";
                }
                elseif ($rowTypes[$rId]['resourceType'] == 'thesis')
                { // masters + unknown
                    $entryKeys[$rId] = "@" . 'mastersthesis' . "{" . "$cite,\n\t";
                }
                else
                {
                    $entryKeys[$rId] = "@" . $this->map->types[$rowTypes[$rId]['resourceType']] . "{" . "$cite,\n\t";
                }
            }
        }
        // Use wikindx-generated keys
        else
        {
            $this->db->formatConditionsOneField($rIds, 'resourcecreatorResourceId');
            $recordset = $this->db->select('resource_creator', ['resourcecreatorResourceId', 'resourcecreatorCreatorSurname'], TRUE);
            while ($row = $this->db->fetchRow($recordset))
            {
                if (!$row['resourcecreatorCreatorSurname'])
                {
                    $cite = 'anon';
                }
                else
                {
                    $cite = preg_replace("/\\W/u", '', $this->convertCharacter($row['resourcecreatorCreatorSurname'], 'plain'));
                }
                $cite .= '.' . $row['resourcecreatorResourceId'];
                if (($rowTypes[$row['resourcecreatorResourceId']]['resourceType'] == 'thesis') &&
                    (($rowTypes[$row['resourcecreatorResourceId']]['resourceField1'] == 'doctoral') ||
                    ($rowTypes[$row['resourcecreatorResourceId']]['resourceField1'] == 'PhD') ||
                    ($rowTypes[$row['resourcecreatorResourceId']]['resourceField1'] == 'EdD')))
                {
                    $entryKeys[$row['resourcecreatorResourceId']] = "@" . 'phdthesis' . "{" . "$cite,\n\t";
                }
                elseif ($rowTypes[$row['resourcecreatorResourceId']]['resourceType'] == 'thesis')
                { // masters + unknown
                    $entryKeys[$row['resourcecreatorResourceId']] = "@" . 'mastersthesis' . "{" . "$cite,\n\t";
                }
                else
                {
                    $entryKeys[$row['resourcecreatorResourceId']] =
                        "@" . $this->map->types[$rowTypes[$row['resourcecreatorResourceId']]['resourceType']] . "{" . "$cite,\n\t";
                }
                unset($row);
            }
        }
    }
    /**
     * Generate wikindx citation key for one resource
     *
     * @param int $id
     *
     * @return string
     */
    private function wikindxCiteKey($id)
    {
        $this->db->formatConditions(['resourcecreatorResourceId' => $id]);
        $recordset = $this->db->select('resource_creator', 'resourcecreatorCreatorSurname', TRUE);
        if (!$this->db->numRows($recordset))
        { // shouldn't happen, but just in case
            $cite = 'anon';
        }
        else
        {
            $name = $this->db->fetchOne($recordset);
            if (!$name)
            {
                $cite = 'anon';
            }
            else
            {
                $cite = $this->convertCharacter($name, 'plain');
                $cite = preg_replace("/\\W/u", '', $cite);
            }
        }

        return $cite;
    }
    /**
     * convert string from raw database string fields
     *
     * @param string $string
     * @param string|FALSE $type
     *
     * @return string
     */
    private function convertCharacter($string, $type = FALSE)
    {
        $string = html_entity_decode(stripslashes($string), ENT_QUOTES, 'UTF-8');
        $string = $this->convertStringTex($string, $type, $this->convertTex ? 'ISO-8859-1' : 'UTF8');
        // Do @string substitutions
        if (!empty($this->strValues))
        {
            $replace = preg_replace($this->strValues, $this->strKeys, $string);
            // If a replacement has occurred, $replace != $c so return without quotes
            if ($replace != $string)
            {
                $replace = preg_replace("/[^a-zA-Z0-9]/u", '', $replace);
                if (is_numeric(mb_substr($replace, 0)))
                {
                    $replace = "string$replace";
                }

                return $replace;
            }
        }

        return $this->startField . $string . $this->endField;
    }
    /**
     * convert string from WIKINDX stored fields
     *
     * @param string $string
     * @param string $replace
     * @param string $pattern
     * @param string|FALSE $type
     *
     * @return string
     */
    private function convertCharacter2($string, $replace, $pattern, $type = FALSE)
    {
        $string = $this->convertStringTex($string, $type, $this->convertTex ? 'ISO-8859-1' : 'UTF8');
        // Do @string substitutions
        $replace = preg_replace($pattern, $replace, $string);
        // If a replacement has occurred, $replace != $c so return without quotes
        if ($replace != $string)
        {
            $replace = preg_replace("/[^a-zA-Z0-9]/u", '', $replace);
            if (is_numeric(mb_substr($replace, 0)))
            {
                $replace = "string$replace";
            }

            return $replace;
        }

        return $this->startField . $string . $this->endField;
    }
    /**
     * convert special characters to TeX codes
     *
     * @param string $string
     * @param string|FALSE $type
     * @param string $encoding Default is 'ISO-8859-1'
     *
     * @return string
     */
    private function convertStringTex($string, $type = FALSE, $encoding = 'ISO-8859-1')
    {
        $c = $string;

        if ($encoding == 'ISO-8859-1')
        {
            if ($type == 'plain')
            {
                foreach ($this->spChPlain as $key => $value)
                {
                    $char = preg_quote(UTF8::mb_chr($key), '/');
                    $c = preg_replace("/$char/u", $value, $c);
                }
            }
            else
            {
                // '\' and '$' are special cases and must be treated separately.  Former MUST be treated first!
                $char = preg_quote("\\" . UTF8::mb_chr(0x005C), '/');	// '\'
                $rep = "\\textbackslash";
                $c = preg_replace("/$char/u", $rep, $c);
                foreach ($this->spCh as $key => $value)
                {
                    $match[] = "/" . preg_quote(UTF8::mb_chr($key), '/') . "/u";
                    $replace[] = $value;
                }
                $c = preg_replace($match, $replace, $c);
            }
        }
        else
        {
            $c = preg_replace('/"/u', '{"}', $c);
        }

        // Convert some BBCode and any citeKeys to TeX and strip the rest
        $bbCode = [
            '/<strong>(.*?)<\/strong>/usi',
            '/<span style="text-decoration: underline;">(.*?)<\/span>/usi',
            '/<em>(.*?)<\/em>/usi',
            '/<sup>(.*?)<\/sup>/usi',
            '/<sub>(.*?)<\/sub>/usi',
            '/WIKINDXCITEKEYSTART(.*?)WIKINDXCITEKEYEND/usi',
        ];
        $tex = [
            '\\textbf{\\1}',
            '\\underline{\\1}',
            '\\textit{\\1}',
            '^{\\1}',
            '_{\\1}',
            '\\cite{\\1}',
        ];

        $c = preg_replace($bbCode, $tex, $c);

        // As web browser encoding is set to UTF-8, all input in the db is stored as UTF-8 - convert back to ISO-8859-1
        if ($encoding == 'ISO-8859-1')
        {
            $c = utf8_decode($c);
        }

        return $c;
    }
    /**
     * grab and format names
     *
     * @param array $rowTypes
     * @param array $entryArray
     * @param int $rIds
     */
    private function grabNames(&$rowTypes, &$entryArray, $rIds)
    {
        $mapName = [];

        $this->db->formatConditionsOneField($rIds, 'resourcecreatorResourceId');
        $this->db->formatConditionsOneField([1, 2], 'resourcecreatorRole'); // bibtex only recognizes author and editor
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->orderBy('resourcecreatorResourceId', TRUE, FALSE);
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourcecreatorRole', TRUE, FALSE);
        $this->db->orderBy('resourcecreatorOrder', TRUE, FALSE);
        $resultSet = $this->db->select('resource_creator', ['resourcecreatorResourceId', 'creatorSurname',
            'creatorFirstname', 'creatorInitials', 'creatorPrefix', 'resourcecreatorRole', ]);
        while ($row = $this->db->fetchRow($resultSet))
        {
            if (!array_key_exists($row['resourcecreatorRole'], $this->map->{$rowTypes[$row['resourcecreatorResourceId']]['resourceType']}['resource_creator']))
            {
                continue;
            }
            $name = $this->formatName($row);
            if ($name)
            {
                $mapName[$row['resourcecreatorResourceId']][$row['resourcecreatorRole']][] = $name;
            }
            unset($row);
        }
        foreach ($rIds as $rId)
        {
            if (array_key_exists($rId, $mapName))
            {
                foreach ($this->map->{$rowTypes[$rId]['resourceType']}['resource_creator'] as $wkField => $bibField)
                {
                    if (array_key_exists($wkField, $mapName[$rId]))
                    {
                        $name = $this->convertCharacter(implode(' and ', $mapName[$rId][$wkField]));
                        $entryArray[$rId][] = $bibField . " = " . $name;
                    }
                }
            }
        }
    }
    /**
     * orderArrayByKeyLength
     */
    private function orderArrayByKeyLength()
    {
        uksort($this->rawString, [$this, "cmp"]);
    }
    /**
     * user function for uksort above
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private function cmp($a, $b)
    {
        if (preg_match_all("/./u", $a, $throwAway) > preg_match_all("/./u", $b, $throwAway2))
        {
            return 0;
        }

        return preg_match_all("/./u", $a, $throwAway) > preg_match_all("/./u", $b, $throwAway2) ? -1 : 1;
    }
    /**
     * formatName
     *
     * @param array $row
     *
     * @return string
     */
    private function formatName($row)
    {
        $surname = $firstname = $initials = '';
        // WIKINDX stores Jr., IV etc. at end of surname...
        if ($row['creatorSurname'])
        {
            if ($row['creatorPrefix'])
            {
                $surname = stripslashes($row['creatorPrefix']) . " " .
                stripslashes($row['creatorSurname']);
            }
            else
            {
                $surname = stripslashes($row['creatorSurname']);
            }
        }
        if ($row['creatorFirstname'])
        {
            $firstname = stripslashes($row['creatorFirstname']);
        }
        if ($row['creatorInitials'])
        {
            $initials = implode('. ', UTF8::mb_explode(' ', stripslashes($row['creatorInitials']))) . ".";
        }
        if (preg_match("/(.*)(Sr\\.|jr\\.)/ui", $surname, $matches))
        {
            $surname = trim($matches[1]) . ", " . trim($matches[2]);
        }
        if (preg_match("/(.*)\\s(I|II|III|IV|V|VI|VII|VIII|IX|X)$/u", $surname, $matches))
        {
            $surname = trim($matches[1]) . ", " . trim($matches[2]);
        }
        if ($firstname && $initials)
        {
            return $surname . ", " . $firstname . ' ' . $initials;
        }
        elseif ($firstname)
        {
            return $surname . ", " . $firstname;
        }
        elseif ($initials)
        {
            return $surname . ", " . $initials;
        }

        return $surname; // if all else fails
    }
    /**
     * formatTitle
     *
     * @param array $row
     *
     * @return string
     */
    private function formatTitle($row)
    {
        // For book_chapter, 'title' is bibtex 'chapter' and 'collectionTitle' is bibtex 'title'
        if ($row['resourceType'] == 'book_chapter')
        {
            return $row['collectionTitle'];
        }
        $noSort = $row['resourceNoSort'] ? $row['resourceNoSort'] . ' ' : FALSE;
        if ($row['resourceSubtitle'])
        {
            $string = $noSort . $row['resourceTitle'] . ": " . $row['resourceSubtitle'];
        }
        else
        {
            $string = $noSort . $row['resourceTitle'];
        }
        $this->resourceTitle = $string;

        return \HTML\stripHtml($string);
    }
    /**
     * grabPublisher
     *
     * @param array $rowTypes
     * @param array $entryArray
     * @param array $miscField1
     * @param array $miscPublishers
     */
    private function grabPublisher(&$rowTypes, &$entryArray, &$miscField1, &$miscPublishers)
    {
        if (!empty($miscField1))
        {
            $this->db->formatConditionsOneField(array_keys($miscField1), 'resourcemiscid');
            $this->db->leftJoin('publisher', 'publisherId', 'resourcemiscField1');
            $resultSet = $this->db->select('resource_misc', ['resourcemiscId', 'publisherName','publisherLocation']);
            while ($row = $this->db->fetchRow($resultSet))
            {
                if (!array_key_exists('resource_publisher', $this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}) ||
                    empty($this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}['resource_publisher']))
                {
                    continue;
                }
                $publisher = $address = FALSE;
                if (array_key_exists('publisherName', $this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}['resource_publisher']))
                {
                    $bibtexPubField = $this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}['resource_publisher']['publisherName'];
                }
                if (array_key_exists('publisherLocation', $this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}['resource_publisher']))
                {
                    $bibtexAddField = $this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}['resource_publisher']['publisherLocation'];
                }
                if ($row['publisherName'])
                {
                    $entryArray[$row['resourcemiscId']][] = "$bibtexPubField = " . $this->convertCharacter($row['publisherName']);
                    if ($row['publisherLocation'])
                    {
                        $entryArray[$row['resourcemiscId']][] = "$bibtexAddField = " . $this->convertCharacter($row['publisherLocation']);
                    }
                }
                unset($row);
            }
        }
        if (!empty($miscPublishers))
        {
            $this->db->formatConditionsOneField(array_keys($miscPublishers), 'resourcemiscId');
            $this->db->leftJoin('publisher', 'publisherId', 'resourcemiscPublisher');
            $resultSet = $this->db->select('resource_misc', ['resourcemiscId', 'publisherName','publisherLocation']);
            while ($row = $this->db->fetchRow($resultSet))
            {
                if (!array_key_exists('resource_publisher', $this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}) ||
                    empty($this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}['resource_publisher']))
                {
                    continue;
                }
                $publisher = $address = FALSE;
                if (array_key_exists('publisherName', $this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}['resource_publisher']))
                {
                    $bibtexPubField = $this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}['resource_publisher']['publisherName'];
                }
                if (array_key_exists('publisherLocation', $this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}['resource_publisher']))
                {
                    $bibtexAddField = $this->map->{$rowTypes[$row['resourcemiscId']]['resourceType']}['resource_publisher']['publisherLocation'];
                }
                if ($row['publisherName'])
                {
                    $entryArray[$row['resourcemiscId']][] = "$bibtexPubField = " . $this->convertCharacter($row['publisherName']);
                    if ($row['publisherLocation'])
                    {
                        $entryArray[$row['resourcemiscId']][] = "$bibtexAddField = " . $this->convertCharacter($row['publisherLocation']);
                    }
                }
                unset($row);
            }
        }
    }
    /**
     * pageFormat
     *
     * @param array $row
     *
     * @param string|FALSE
     */
    private function pageFormat($row)
    {
        $page = FALSE;
        if ($row['resourcepagePageStart'])
        {
            $page = stripslashes($row['resourcepagePageStart']);
        }
        if ($row['resourcepagePageEnd'])
        {
            $page .= '--' . stripslashes($row['resourcepagePageEnd']);
        }

        return $page;
    }
    /**
     * grabKeywords
     *
     * @param array $entryArray
     * @param array $rIds
     */
    private function grabKeywords(&$entryArray, $rIds)
    {
        $kws = [];
        $this->db->formatConditionsOneField($rIds, 'resourcekeywordResourceId');
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
        $recordset = $this->db->select('resource_keyword', ['resourcekeywordResourceId', 'keywordKeyword']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $kws[$row['resourcekeywordResourceId']][] = $row['keywordKeyword'];
        }
        foreach ($kws as $rId => $kwArray)
        {
            $entryArray[$rId][] = "keywords = " . $this->convertCharacter(implode($this->keywordSeparator, $kwArray));
        }
    }
    /**
     * grabNoteAbstractUrl
     *
     * @param array $rowTypes
     * @param array $entryArray
     * @param array $howPublishedDone
     * @param array $rIds
     */
    private function grabNoteAbstractUrl(&$rowTypes, &$entryArray, &$howPublishedDone, $rIds)
    {
        $this->db->formatConditionsOneField($rIds, 'resourcetextId');
        $resultSet = $this->db->select('resource_text', ['resourcetextId', 'resourcetextNote', 'resourcetextAbstract', 'resourcetextUrls']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            // Ensure first letter is capitalized
            if ($row['resourcetextNote'])
            {
                $note = UTF8::mb_ucfirst(\HTML\stripHtml($row['resourcetextNote']));
                $entryArray[$row['resourcetextId']][] = "note = " . $this->convertCharacter($this->parseCitation($note));
            }
            if ($row['resourcetextAbstract'])
            {
                $abstract = UTF8::mb_ucfirst(\HTML\stripHtml($row['resourcetextAbstract']));
                $entryArray[$row['resourcetextId']][] = "abstract = " . $this->convertCharacter($this->parseCitation($abstract));
            }
            if ($row['resourcetextUrls'] &&
                (($rowTypes[$row['resourcetextId']]['resourceType'] == 'web_article') ||
                ($rowTypes[$row['resourcetextId']]['resourceType'] == 'database')))
            { // 'misc' types
                // Only take the first URL for this field
                $tmp = base64_decode($row['resourcetextUrls']);
                $tmp = unserialize($tmp);
                $url = array_shift($tmp);
                $entryArray[$row['resourcetextId']][] = "howpublished = " . $this->startField . "\\url{" . $url . "}" . $this->endField;
                $howPublishedDone[$row['resourcetextId']] = TRUE;
            }
            unset($row);
        }
    }
    /**
     * For bibtex exports, return bibtex key instead of formatted citation for citations within the body of text
     *
     * @param string $text
     *
     * @return string
     */
    private function parseCitation($text)
    {
        // If no citations, return doing nothing
        if (mb_strpos(mb_strtolower($text), "[cite]") === FALSE)
        {
            return $text;
        }
        // Capture any text after last [cite]...[/cite] tag
        $explode = UTF8::mb_explode("]etic/[", UTF8::mb_strrev($text), 2);
        $tailText = UTF8::mb_strrev($explode[0]);
        $text = UTF8::mb_strrev("]etic/[" . $explode[1]);

        return preg_replace_callback("/(\\[cite\\])(.*)(\\[\\/cite\\])/Uuis", [$this, "citeCallback"], $text . $tailText);
    }
    /**
     * Callback for citations
     *
     * @param mixed $matches
     *
     * @return mixed
     */
    private function citeCallback($matches)
    {
        array_shift($matches);

        return $this->parseCiteTag($matches[1]);
    }
    /**
     * Parse the cite tag by extracting resource ID and any page numbers. Check ID is valid
     * PreText and postText can also be encoded: e.g. (see Grimshaw 2003; Boulanger 2004 for example)
     * [cite]23:34-35|see ` for example[/cite].  For multiple citations, only the first encountered preText
     * and postText will be used to enclose the citations.
     *
     * @param mixed $cite
     *
     * @return string
     */
    private function parseCiteTag($cite)
    {
        $citeKey = FALSE;
        $rawCitation = UTF8::mb_explode("|", $cite);
        $idPart = UTF8::mb_explode(":", $rawCitation[0]);
        $id = $idPart[0];
        $this->db->formatConditions(['resourceId' => $id]);
        $resultset = $this->db->select('resource', ['resourceId']);
        // Presumably an invalid citation ID
        if (!$this->db->numRows($resultset))
        {
            return "INVALID CITATION";
        }
        if ($this->session->getVar("export_UseOriginalCitation"))
        {
            $this->db->formatConditions(['importrawId' => $id]);
            $rawEntries = unserialize(base64_decode($this->db->selectFirstField('import_raw', 'importrawText')));
            if ($rawEntries)
            {
                $rawEntries = UTF8::mb_explode(LF, $rawEntries);
                array_pop($rawEntries); // always an empty array at end so get rid of it.
                foreach ($rawEntries as $entries)
                {
                    $entry = UTF8::mb_explode("=", $entries, 2);
                    if (!trim($entry[1]))
                    {
                        continue;
                    }
                    if (trim($entry[0]) == 'citation')
                    {
                        $citeKey = trim($entry[1]);

                        break;
                    }
                }
            }
        }
        elseif (!$this->session->getVar("setup_UseWikindxKey"))
        {
            $this->db->formatConditions(['resourceId' => $id]);
            $citeKey = $this->db->selectFirstField('resource', 'resourceBibtexKey');
        }
        if (!$citeKey)
        {
            $citeKey = $this->wikindxCiteKey($id); // Use wikindx=generated keys from this point on
        }
        
        $citeKey = "WIKINDXCITEKEYSTART" . $citeKey . ".$id" . "WIKINDXCITEKEYEND";
        if (array_key_exists('1', $idPart))
        {
            $pages = UTF8::mb_explode("-", $idPart[1]);
            $pageStart = $pages[0];
            $pageEnd = array_key_exists('1', $pages) ? $pages[1] : FALSE;
            if ($pageEnd)
            {
                $pages = " pp.$pageStart-$pageEnd";
            }
            else
            {
                $pages = " p.$pageStart";
            }
        }
        else
        {
            $pages = FALSE;
        }
        if (array_key_exists('1', $rawCitation))
        {
            $text = UTF8::mb_explode("`", $rawCitation[1]);
            $preText = $text[0];
            $postText = array_key_exists('1', $text) ? $text[1] : FALSE;
        }
        else
        {
            $preText = $postText = FALSE;
        }

        return "$preText$citeKey$pages$postText";
    }
    /**
     * Deal with month/day
     *
     * @param array $entryArray
     * @param array $dates
     */
    private function grabDates(&$entryArray, &$dates)
    {
        // NB 3-letter month abbreviation must not be quoted
        foreach ($dates as $rId => $dateArray)
        {
            $startMonth = $this->monthArray[$dateArray['resourcemiscField3']]; // we check this array element exists above
            // if there's a day, append it
            if (array_key_exists('resourcemiscField2', $dateArray))
            {
                $startDay = $this->startField . " " . $dateArray['resourcemiscField2'] . $this->endField;
            }
            if (isset($startDay) && array_key_exists('resourcemiscField5', $dateArray) && array_key_exists('resourcemiscField6', $dateArray) &&
                ($dateArray['resourcemiscField3'] == $dateArray['resourcemiscField6']))
            {
                $date = $this->startField . $startMonth . "~" . $this->endField . " # "
                    . $this->startField . $dateArray['resourcemiscField2']
                    . "--" . $dateArray['resourcemiscField5'] . $this->endField;
            }
            elseif (isset($startDay) && array_key_exists('resourcemiscField5', $dateArray) && array_key_exists('resourcemiscField6', $dateArray))
            {
                $date = $this->startField . $startMonth . "~" . $this->endField . " # "
                 . "~" . $this->startField . $dateArray['resourcemiscField2'] . "--" .
                $this->endField . " # " . $this->startField . $this->monthArray[$dateArray['resourcemiscField6']]
                . $this->endField . " # " . $this->startField . $dateArray['resourcemiscField5'] . $this->endField;
            }
            elseif (array_key_exists('resourcemiscField6', $dateArray))
            {
                $date = $this->startField . $startMonth . "~" . $this->endField . " # "
                . $this->startField . "/" . $this->endField .
                " # " . $this->startField . $this->monthArray[$dateArray['resourcemiscField6']] . "~" . $this->endField;
            }
            elseif (isset($startDay))
            {
                $date = $this->startField . $startMonth . "~" . $this->endField . " # "
                . $this->startField . $startDay . $this->endField;
            }
            else
            {
                $date = $this->startField . $startMonth . $this->endField;
            }
            // No double quotes for month
            $entryArray[$rId][] = "month = $date";
        }
    }
    /**
     * Grab any requested metadata
     *
     * @param array $entryArray
     * @param array $resourceIds
     */
    private function grabMetadata(&$entryArray, $resourceIds)
    {
        $conditions = $conditionsC = $unions = [];
        if (array_key_exists('Quotation', $this->metadataFields))
        {
            $conditions[] = 'q';
        }
        if (array_key_exists('QuotationComment', $this->metadataFields))
        {
            $conditionsC[] = 'qc';
        }
        if (array_key_exists('Paraphrase', $this->metadataFields))
        {
            $conditions[] = 'p';
        }
        if (array_key_exists('ParaphraseComment', $this->metadataFields))
        {
            $conditionsC[] = 'pc';
        }
        if (array_key_exists('Musing', $this->metadataFields))
        {
            $this->setPrivateCondition();
            $conditions[] = 'm';
        }
        // subquery for quotes, paraphrases and musings
        if (!empty($conditions))
        {
            $this->db->formatConditionsOneField($resourceIds, 'resourcemetadataResourceId');
            $this->db->formatConditionsOneField($conditions, 'resourcemetadataType');
            $unions[] = $this->db->selectNoExecute(
                'resource_metadata',
                ['resourcemetadataResourceId', 'resourcemetadataId', 'resourcemetadataMetadataId', 'resourcemetadataType',
                    'resourcemetadataPageStart', 'resourcemetadataPageEnd', 'resourcemetadataText', ],
                FALSE,
                TRUE,
                TRUE
            );
        }
        // subquery for comments
        if (!empty($conditionsC))
        {
            $this->setPrivateCondition('t2');
            $ijCondition = $this->db->formatFields('t1.resourcemetadataId') . $this->db->equal . $this->db->formatFields('t2.resourcemetadataMetadataId')
                . $this->db->and .
                $this->db->formatConditionsOneField($resourceIds, 't1.resourcemetadataResourceId', FALSE, TRUE, FALSE, FALSE, TRUE);
            $this->db->innerJoin([['resource_metadata' => 't2']], $ijCondition, FALSE, FALSE);
            $this->db->formatConditionsOneField($conditionsC, 't2.resourcemetadataType');
            $unions[] = $this->db->selectNoExecute(
                'resource_metadata',
                ['t2.resourcemetadataResourceId', 't2.resourcemetadataId', 't2.resourcemetadataMetadataId', 't2.resourcemetadataType',
                    't2.resourcemetadataPageStart', 't2.resourcemetadataPageEnd', 't2.resourcemetadataText', ],
                FALSE,
                TRUE,
                TRUE,
                't1'
            );
        }
        if (empty($unions))
        {
            return; // nothing to do
        }
        $subQ = $this->db->subQuery($this->db->union($unions), 't');
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourcemetadataResourceId', TRUE, FALSE);
        $this->db->orderBy('resourcemetadataMetadataId', TRUE, FALSE);
        $this->db->orderBy('resourcemetadataType', TRUE, FALSE);
        $this->db->orderBy($this->db->tidyInputClause('resourcemetadataPageStart') . '+0', FALSE, FALSE);
        $recordset = $this->db->selectFromSubQuery(
            FALSE,
            ['resourcemetadataResourceId', 'resourcemetadataId', 'resourcemetadataMetadataId', 'resourcemetadataType',
                'resourcemetadataPageStart', 'resourcemetadataPageEnd', 'resourcemetadataText', ],
            $subQ
        );
        $comments = $commentTypes = $cFields = $mFields = $mIds = [];
        $qIndex = $pIndex = $mIndex = 1;
        $rId = 0;
        $lastType = FALSE;
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$row['resourcemetadataResourceId'])
            {
                if ($row['resourcemetadataType'] == 'qc')
                {
                    list($field, $text) = $this->formatMetadata($row, $qIndex);
                    $commentTypes[$row['resourcemetadataId']] = $this->metadataFields['QuotationComment'];
                }
                elseif ($row['resourcemetadataType'] == 'pc')
                {
                    list($field, $text) = $this->formatMetadata($row, $pIndex);
                    $commentTypes[$row['resourcemetadataId']] = $this->metadataFields['ParaphraseComment'];
                }
                $comments[$row['resourcemetadataId']][$row['resourcemetadataMetadataId']] = $text;
                $cFields[$row['resourcemetadataId']] = $field;
            }
            else
            {
                if ($rId === $row['resourcemetadataResourceId'])
                {
                    if (($row['resourcemetadataType'] == 'q') && ($lastType == 'q'))
                    {
                        ++$qIndex;
                    }
                    elseif (($row['resourcemetadataType'] == 'p') && ($lastType == 'p'))
                    {
                        ++$pIndex;
                    }
                    elseif (($row['resourcemetadataType'] == 'm') && ($lastType == 'm'))
                    {
                        ++$mIndex;
                    }
                }
                else
                {
                    $rId = $row['resourcemetadataResourceId'];
                    $qIndex = $pIndex = $mIndex = 1;
                }
                if ($row['resourcemetadataType'] == 'q')
                {
                    list($field, $text) = $this->formatMetadata($row, $qIndex);
                    $lastType = 'q';
                }
                elseif ($row['resourcemetadataType'] == 'p')
                {
                    list($field, $text) = $this->formatMetadata($row, $pIndex);
                    $lastType = 'p';
                }
                elseif ($row['resourcemetadataType'] == 'm')
                {
                    list($field, $text) = $this->formatMetadata($row, $mIndex);
                    $lastType = 'm';
                }
                $entryArray[$row['resourcemetadataResourceId']][] = $field . ' = ' . $text;
                $mFields[$row['resourcemetadataId']] = $field;
                $mIds[$row['resourcemetadataId']] = $row['resourcemetadataResourceId'];
            }
            unset($row);
        }
        $index = 1;
        foreach ($comments as $mId => $textArray)
        {
            foreach ($textArray as $mmId => $text)
            {
                $entryArray[$mIds[$mmId]][] = $commentTypes[$mId] . '_' . $index . '_' . $mFields[$mmId] . ' = ' . $text;
                ++$index;
            }
        }
    }
    /**
     * Format the metadata row
     *
     * @param array $row
     * @param mixed $index
     *
     * @return array
     */
    private function formatMetadata($row, $index = FALSE)
    {
        if ($row['resourcemetadataType'] == 'q')
        {
            $type = 'Quotation';
        }
        elseif ($row['resourcemetadataType'] == 'p')
        {
            $type = 'Paraphrase';
        }
        elseif ($row['resourcemetadataType'] == 'qc')
        {
            $type = 'QuotationComment';
        }
        elseif ($row['resourcemetadataType'] == 'pc')
        {
            $type = 'ParaphraseComment';
        }
        elseif ($row['resourcemetadataType'] == 'm')
        {
            $type = 'Musing';
        }
        if ($row['resourcemetadataPageStart'])
        {
            $pages = $row['resourcemetadataPageStart'];
            if ($row['resourcemetadataPageEnd'])
            {
                $pages = '(pp.' . $pages . '-' . $row['resourcemetadataPageEnd'] . ') ';
            }
            else
            {
                $pages = "(p.$pages) ";
            }
        }
        else
        {
            $pages = FALSE;
        }
        $text = $pages . $row['resourcemetadataText'];
        if ($index)
        {
            $fieldName = $this->metadataFields[$type] . "_$index";
        }
        else
        {
            $fieldName = $this->metadataFields[$type];
        }

        return [$fieldName, $this->convertCharacter($this->parseCitation(\HTML\stripHtml($text)))];
    }
    /**
     * Set conditions for musings and comments according to private flag
     *
     * @param mixed $alias
     */
    private function setPrivateCondition($alias = FALSE)
    {
        if ($alias)
        {
            $alias = "$alias.";
        }
        if ($userId = $this->session->getVar('setup_UserId'))
        {
            $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
            $this->db->formatConditions($this->db->formatFields('usergroupsusersGroupId') . $this->db->equal
                . $this->db->formatFields($alias . 'resourcemetadataPrivate'));
            $subSql = $this->db->selectNoExecute('user_groups_users', 'usergroupsusersId', FALSE, TRUE, TRUE);
            $subject = $this->db->formatFields($alias . 'resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('N')
                . $this->db->and
                . $this->db->formatFields($alias . 'resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('Y');
            $case1 = $this->db->caseWhen($subject, FALSE, $subSql, FALSE, FALSE);
            $subject = $this->db->formatFields($alias . 'resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('Y');
            $result = $this->db->formatFields($alias . 'resourcemetadataAddUserId') . $this->db->equal . $this->db->tidyInput($userId);
            $case2 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $subject = $this->db->formatFields($alias . 'resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('N');
            $result = $this->db->tidyInput(1);
            $case3 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $this->db->formatConditions($case1 . $this->db->or . $case2 . $this->db->or . $case3);
        }
        else
        {
            $this->db->formatConditions([$alias . 'resourcemetadataPrivate' => 'N']);
        }
    }
}
