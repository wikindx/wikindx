<?php

/**
 *	importPubmed class.
 *
 *	v0.3 (23/Oct/2006 ~ Brian Cade, updated for WIKINDX v4 Dec/2012 Mark Grimshaw-Aagaard)
 *
 *	Searches PubMed by field and imports bibtex data
 *
 *	Adopted Mark Grimshaw-Aagaard's bibutils v1.1 code
 *	Inspired by Dr. Andrew C.R. Martin's BibPubMed from http://www.bioinf.org.uk/pubmed/
 *	Makes use of 'bibutils' from http://sourceforge.net/p/bibutils/home/Bibutils/ written by Chris Putnam.
 */
class PUBMED
{
    private $vars;
    private $coremessages;
    private $errors;
    private $pluginmessages;
    private $session;
    private $importCommon;
    private $tag;
    private $category;
    private $bibConfig;
    private $parentClass;
    private $configImport;
    private $filesDir;
    private $filesUrl;

    /**
     * Constructor
     *
     * @param mixed $parentClass
     */
    public function __construct($parentClass)
    {
        $this->parentClass = $parentClass;
        $this->vars = GLOBALS::getVars();
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_MESSAGES::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $this->configImport = new importexportbib_IMPORTCONFIG();
        if (!$this->configImport->bibutilsPath) {
            $this->configImport->bibutilsPath = '/usr/local/bin/'; // default *NIX location
        }
        $this->session = FACTORY_SESSION::getInstance();
        $this->importCommon = FACTORY_IMPORT::getInstance();
        $this->tag = FACTORY_TAG::getInstance();
        $this->category = FACTORY_CATEGORY::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->bibConfig = FACTORY_BIBTEXCONFIG::getInstance();
        $this->filesDir = WIKINDX_DIR_DATA_FILES . DIRECTORY_SEPARATOR;
        $this->filesUrl = WIKINDX_URL_DATA_FILES . DIRECTORY_SEPARATOR;
    }
    /*
     * dislay options for importing
     *
     * @param string|FALSE $message
     *
     * @return string
     */
    public function displayImport($message = FALSE)
    {
        $this->session->delVar("importLock");

        $pString = $message ? $message : FALSE;
        $pString .= FORM\formHeader("importexportbib_importPubMed");
        $pString .= FORM\hidden('method', 'process');
        $pString .= HTML\p($this->pluginmessages->text('importPubMedIntro'));
        $sessionVars = $this->session->getArray("importpubMed");
        // see http://www.ncbi.nlm.nih.gov/books/bv.fcgi?rid=helppubmed.section.pubmedhelp.Search_Field_Descrip
        // for all possible terms
        $input = array_key_exists('ID', $sessionVars) ? $sessionVars['ID'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedId'), "importpubMed_ID", $input));
        $input = array_key_exists('ALL', $sessionVars) ? $sessionVars['ALL'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedFields'), "importpubMed_ALL", $input));
        $input = array_key_exists('AU', $sessionVars) ? $sessionVars['AU'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedAuthor'), "importpubMed_AU", $input));
        $input = array_key_exists('1AU', $sessionVars) ? $sessionVars['1AU'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedFirstAuthor'), "importpubMed_1AU", $input));
        $input = array_key_exists('LASTAU', $sessionVars) ? $sessionVars['LASTAU'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedLastAuthor'), "importpubMed_LASTAU", $input));
        $input = array_key_exists('TI', $sessionVars) ? $sessionVars['TI'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedTitle'), "importpubMed_TI", $input));
        $input = array_key_exists('TIAB', $sessionVars) ? $sessionVars['TIAB'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedAbstract'), "importpubMed_TIAB", $input));
        $input = array_key_exists('DP', $sessionVars) ? $sessionVars['DP'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedYear'), "importpubMed_DP", $input));
        $input = array_key_exists('TA', $sessionVars) ? $sessionVars['TA'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedJournal'), "importpubMed_TA", $input));
        $input = array_key_exists('VI', $sessionVars) ? $sessionVars['VI'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedVolume'), "importpubMed_VI", $input));
        $input = array_key_exists('IP', $sessionVars) ? $sessionVars['IP'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedIssue'), "importpubMed_IP", $input));
        $input = array_key_exists('LA', $sessionVars) ? $sessionVars['LA'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedLanguage'), "importpubMed_LA", $input));
        $input = array_key_exists('reldate', $sessionVars) ? $sessionVars['reldate'] : '';
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedLimit'), "importpubMed_reldate", $input));
        $input = array_key_exists('MaxResults', $sessionVars) ? $sessionVars['MaxResults'] : 100; // default == 100
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text('importPubMedMaxResults'), "importpubMed_MaxResults", $input, 3, 3));
        if (file_exists($this->configImport->bibutilsPath . 'xml2bib')) {
            $jScript = 'index.php?action=importexportbib_initPubMedImport&method=wikindx';
            $jsonArray[] = [
                'startFunction' => 'toggleVisibilityFromCheckbox',
                'script' => "$jScript",
                'triggerField' => 'importpubMed_Wikindx',
                'targetDiv' => 'wikindxImport',
            ];
            $js = AJAX\jActionForm('onclick', $jsonArray);
            $pString .= HTML\p(FORM\checkbox($this->pluginmessages->text('importPubMedWikindx'), "importpubMed_Wikindx", FALSE, '', $js));
            $pString .= HTML\div('wikindxImport', '&nbsp;');
        }
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();
        $this->session->clearArray("importpubMed");
        // Load at end because .js initialization needs various DIVs to be in the page before they are made invisible
        AJAX\loadJavascript();

        return $pString;
    }
    /*
     * Print form fields for importing into WIKINDX
     *
     * @param bool $display
     *
     * @return string
     */
    public function wikindxImportFields($display = FALSE)
    {
        $categories = $this->category->grabAll();
        $pString = '';
        if (count($categories) > 1) {
            $pString .= HTML\p($this->coremessages->text("import", "categoryPrompt"));
        }
        $pString .= HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= HTML\trStart();
        // Load tags
        $tags = $this->tag->grabAll();
        $tagInput = FORM\textInput($this->coremessages->text("import", "tag"), "import_Tag", $this->session->getVar("import_Tag"), 30, 255);
        if ($tags) {
            // add 0 => IGNORE to tags array
            $temp[0] = $this->coremessages->text("misc", "ignore");
            foreach ($tags as $key => $value) {
                $temp[$key] = $value;
            }
            $tags = $temp;
            $sessionTag = $this->session->issetVar("import_TagId") ?
                $this->session->getVar("import_TagId") : FALSE;
            if ($sessionTag) {
                $element = FORM\selectedBoxValue(FALSE, 'import_TagId', $tags, 5);
            } else {
                $element = FORM\selectFBoxValue(FALSE, 'import_TagId', $tags, 5);
            }
            $pString .= HTML\td($tagInput . '&nbsp;&nbsp;' . $element);
        } else {
            $pString .= HTML\td($tagInput);
        }
        $categoryTd = FALSE;
        if (count($categories) > 1) {
            if ($sessionCategories = $this->session->getVar("import_Categories")) {
                $sCategories = UTF8::mb_explode(",", $sessionCategories);
                $element = FORM\selectedBoxValueMultiple($this->coremessages->text(
                    "import",
                    "category"
                ), 'import_Categories', $categories, $sCategories, 5);
            } else {
                $element = FORM\selectFBoxValueMultiple($this->coremessages->text(
                    "import",
                    "category"
                ), 'import_Categories', $categories, 5);
            }
            $pString .= HTML\td($element . BR .
                HTML\span($this->coremessages->text("hint", "multiples"), 'hint'));
            $categoryTd = TRUE;
        }
        if ($bibs = $this->importCommon->bibliographySelect()) {
            $pString .= HTML\td($bibs . BR .
                HTML\span($this->coremessages->text("hint", "multiples"), 'hint'), FALSE, "left", "bottom");
        }
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\tableStart('generalTable borderStyleSolid');
        $checked = $this->session->issetVar("import_Quarantine") ? TRUE : FALSE;
        $pString .= \HTML\td($this->coremessages->text("import", "quarantine") . "&nbsp;&nbsp;" .
        	\FORM\checkbox(FALSE, "import_Quarantine"));
        $checked = $this->session->issetVar("import_ImportDuplicates") ? TRUE : FALSE;
        $pString .= HTML\td($this->coremessages->text("import", "importDuplicates") . "&nbsp;&nbsp;" .
            FORM\checkbox(FALSE, 'import_ImportDuplicates'), $checked);
        $td = $this->coremessages->text("import", "storeRawBibtex");
        $pString .= HTML\td($td . "&nbsp;&nbsp;" . $this->coremessages->text("import", "storeRawLabel") . "&nbsp;&nbsp;" .
            FORM\checkbox(FALSE, 'import_Raw', $this->session->issetVar("import_Raw")));
        $pString .= HTML\td($this->importCommon->keywordSeparator());
        $pString .= HTML\td($this->importCommon->titleSubtitleSeparator());
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $this->session->clearArray("import");

        return HTML\div('wikindxImport', $pString);
    }
    /*
     * begin import process
     */
    public function processPubMed()
    {
        $this->validateInput();
        if (array_key_exists('importpubMed_Wikindx', $this->vars)) {
            $this->storeImport();
        }
        $pString = $this->fetch();
        if (!array_key_exists('importpubMed_Wikindx', $this->vars)) {
            $this->parentClass->listFiles($pString);
        }
    }
    /*
     * fetch
     *
     * @return string
     */
    private function fetch()
    {
        // Grab via Pubmed IDs - can be multiple
        if (array_key_exists('importpubMed_ID', $this->vars) && (preg_match("/\\d/u", $this->vars['importpubMed_ID']))) {
            $pmid = preg_replace("/\\s+/u", ",", trim($this->vars['importpubMed_ID']));
            $array = $this->pubmed_fetch($pmid);
            if (!empty($array)) {
                $xml_data[] = $array;

                $actualCount = mb_substr_count($pmid, ',') + 1;
            } else {
                $actualCount = 0;
            }
        }
        // Grab multiple results (i.e. not searching by single ID)
        else {
            $term_array = [];
            $modifier = "";
            if (array_key_exists('importpubMed_ALL', $this->vars) && (mb_strlen($this->vars['importpubMed_ALL']) > 0)) {
                $term_array[] = preg_replace("/\\s+/u", "+", trim($this->vars['importpubMed_ALL'])) . "[ALL]";
            }
            if (array_key_exists('importpubMed_AU', $this->vars) && (mb_strlen($this->vars['importpubMed_AU']) > 0)) {
                $term_array[] = preg_replace("/\\s+/u", "+", trim($this->vars['importpubMed_AU'])) . "[AU]";
            }
            if (array_key_exists('importpubMed_1AU', $this->vars) && (mb_strlen($this->vars['importpubMed_1AU']) > 0)) {
                $term_array[] = preg_replace("/\\s+/u", "+", trim($this->vars['importpubMed_1AU'])) . "[1AU]";
            }
            if (array_key_exists('importpubMed_LASTAU', $this->vars) && (mb_strlen($this->vars['importpubMed_LASTAU']) > 0)) {
                $term_array[] = preg_replace("/\\s+/u", "+", trim($this->vars['importpubMed_LASTAU'])) . "[LASTAU]";
            }
            if (array_key_exists('importpubMed_TI', $this->vars) && (mb_strlen($this->vars['importpubMed_TI']) > 0)) {
                $term_array[] = preg_replace("/\\s+/u", "+", trim($this->vars['importpubMed_TI'])) . "[TI]";
            }
            if (array_key_exists('importpubMed_TIAB', $this->vars) && (mb_strlen($this->vars['importpubMed_TIAB']) > 0)) {
                $term_array[] = preg_replace("/\\s+/u", "+", trim($this->vars['importpubMed_TIAB'])) . "[TIAB]";
            }
            if (array_key_exists('importpubMed_DP', $this->vars) && (mb_strlen($this->vars['importpubMed_DP']) > 0)) {
                $term_array[] = preg_replace("/\\s+/u", "+", trim($this->vars['importpubMed_DP'])) . "[DP]";
            }
            if (array_key_exists('importpubMed_TA', $this->vars) && (mb_strlen($this->vars['importpubMed_TA']) > 0)) {
                $term_array[] = preg_replace("/\\s+/u", "+", trim($this->vars['importpubMed_TA'])) . "[TA]";
            }
            if (array_key_exists('importpubMed_VI', $this->vars) && (mb_strlen($this->vars['importpubMed_VI']) > 0)) {
                $term_array[] = preg_replace("/\\s+/u", "+", trim($this->vars['importpubMed_VI'])) . "[VI]";
            }
            if (array_key_exists('importpubMed_IP', $this->vars) && (mb_strlen($this->vars['importpubMed_IP']) > 0)) {
                $term_array[] = preg_replace("/\\s+/u", "+", trim($this->vars['importpubMed_IP'])) . "[IP]";
            }
            if (array_key_exists('importpubMed_LA', $this->vars) && (mb_strlen($this->vars['importpubMed_LA']) > 0)) {
                $term_array[] = preg_replace("/\\s+/u", "+", trim($this->vars['importpubMed_LA'])) . "[LA]";
            }
            if (array_key_exists('importpubMed_reldate', $this->vars) && (is_numeric(trim($this->vars['importpubMed_reldate'])))) {
                $modifier .= "&reldate=" . ($this->vars['importpubMed_reldate']);
            }
            if (array_key_exists('importpubMed_MaxResults', $this->vars)) {
                $this->maxResults = trim($this->vars['importpubMed_MaxResults']);
            } else {
                $this->maxResults = 100;
            }
            // Max 100 returns per NCBI's request- see https://www.ncbi.nlm.nih.gov/books/NBK25501/
            $url = "https://www.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&retmax=" .
                $this->maxResults . $modifier . "&term=";
            $url .= implode("+", $term_array);
            $lines = file($url);
            $ids = [];
            foreach ($lines as $line) {
                preg_match("/<Id>(\\d+)<\\/Id>/ui", $line, $matches);
                if (isset($matches[1])) {
                    $ids[] = $matches[1];
                }
            }
            $xml_data = $this->pubmed_fetch_multiple($ids);
            $actualCount = count($xml_data);
        }
        if ($actualCount == 0) {
            $this->badInput(HTML\p($this->pluginmessages->text('importPubMedNoResults'), 'error'));
        }
        $fileName = \UTILS\uuid();
        $bibFile = $fileName . '.bib';
        $pubmed_bib_path = $this->filesDir . $bibFile;
        $pubmed_pre_xml_path = $this->filesDir . $fileName . ".med";
        if ($pubmed_output_handle = fopen($pubmed_pre_xml_path, "w+")) {
            for ($i = 0; $i < $actualCount; $i++) {
                if (count($xml_data[$i]) > 0) {
                    foreach ($xml_data[$i] as $line) {
                        fwrite($pubmed_output_handle, $line);
                    }
                }
            }

            fclose($pubmed_output_handle);
        }
        $pubmed_xml_path = $this->filesDir . $fileName . ".xml";
        $command1 = "med2xml -u $pubmed_pre_xml_path > $pubmed_xml_path";
        $command2 = "xml2bib $pubmed_xml_path > $pubmed_bib_path";
        $this->process($command1, $pubmed_pre_xml_path);
        $this->process($command2, FALSE);
        @unlink($pubmed_pre_xml_path);
        @unlink($pubmed_xml_path);
        // for downloaded bibtex file
        $result = $actualCount > 1 ? $this->pluginmessages->text('importPubMedResults') : $this->pluginmessages->text('importPubMedResult');
        $pString = $this->pluginmessages->text('importPubMedSuccess', $actualCount) . " $result:&nbsp;&nbsp;";
        // for importing into BIBTEXIMPORT.php
        if (array_key_exists('importpubMed_Wikindx', $this->vars)) {
            GLOBALS::setTplVar('heading', $this->coremessages->text("heading", "bibtexImport"));
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "modules", "import", "IMPORTBIBTEX.php"]));
            $obj = new IMPORTBIBTEX();
            $obj->importFile = $bibFile;
            $obj->type = 'file';
            $this->session->setVar("importLock", FALSE);
            $pString = $obj->stage1(TRUE);
        } else {
            $pString = HTML\p($pString .
                HTML\a("link", $this->pluginmessages->text('importPubMedOutputFile'), $this->filesUrl . $bibFile, "_blank"), 'success');
        }

        return $pString;
    }
    /*
     * run bibutils executables
     *
     * @param string $command
     * @param string $inputFile
     */
    private function process($command, $inputFile)
    {
        $cmd = $this->configImport->bibutilsPath . $command;
        if (getenv("OS") == "Windows_NT") {
            $this->win_execute($cmd, $inputFile);
        } elseif (exec($cmd, $output, $returnVar) === FALSE) {
            @unlink($inputFile);
            $this->badInput(HTML\p($this->pluginmessages->text('importPubMedFailConvert', $returnVar), 'error'));
        }
    }
    /*
     * If Windows server, execute command this way
     *
     * Thanks to Richard Karnesky of refbase.
     *
     * @param string $cmd
     * @param string $inputFile
     */
    
    private function win_execute($cmd, $inputFile)
    {
        $cmdline = "cmd /C " . $cmd;
        // Make a new instance of the COM object
        $WshShell = new COM("WScript.Shell") or die("Failed to instantiate COM");
        // Make the command window but dont show it.
        if ($oExec = $WshShell->Run($cmdline, 0, TRUE)) {
            @unlink($inputFile);
            $this->badInput(HTML\p($this->pluginmessages->text('importPubMedFailConvert', $oExec), 'error'));
        }
    }
    /*
     * Fetch one ID
     *
     * @param int $id
     *
     * @return string
     */
    private function pubmed_fetch($id)
    {
        $pubmed_file = file("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&retmode=xml&id=$id");
        foreach ($pubmed_file as $line) {
            if (mb_strpos($line, "Empty id list - nothing todo") !== FALSE) {
                return [];
            }
        }

        return($pubmed_file);
    }
    /*
     * Fetch multiple IDs
     *
     * @param array $ids
     *
     * @return string
     */
    private function pubmed_fetch_multiple($ids)
    {
        $idString = implode(',', $ids);
        $pubmed_file = file("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&retmode=xml&id=$idString");
        if (count($ids) == 1) { // Only one result possible.
            return($pubmed_file);
        }
        $xml_data = [];
        $startArticle = FALSE;
        foreach ($pubmed_file as $line) {
            if (!$startArticle && (mb_strpos($line, "<PubmedArticle>") !== FALSE)) { // article not yet open -- this marks start
                $array = [];
                $array[] = $line;
                $startArticle = TRUE;
            } elseif ($startArticle && (mb_strpos($line, "</PubmedArticle>") !== FALSE)) { // article opened -- search for closure
                $array[] = $line;
                $xml_data[] = $array;
                $startArticle = FALSE;
            } elseif ($startArticle) { // Article open
                $array[] = $line;
            }
            // else -- discard everything else
        }

        return $xml_data;
    }
    /*
     * store parameters if importing directly to wikindx
     */
    private function storeImport()
    {
        // a multiple select box so handle as array
        if (array_key_exists('import_Categories', $this->vars) && $this->vars['import_Categories']) {
            if (!$this->session->setVar("import_Categories", trim(implode(',', $this->vars['import_Categories'])))) {
                $this->badInput($this->errors->text("sessionError", "write"));
            }
        }
        // a multiple select box so handle as array
        if (array_key_exists('import_BibId', $this->vars) && $this->vars['import_BibId']) {
            if (!$this->session->setVar("import_BibId", trim(implode(',', $this->vars['import_BibId'])))) {
                $this->badInput($this->errors->text("sessionError", "write"));
            }
        }
        if (array_key_exists('import_Raw', $this->vars) && $this->vars['import_Raw']) {
            if (!$this->session->setVar("import_Raw", 1)) {
                $this->badInput($this->errors->text("sessionError", "write"));
            }
        }
        if (!$this->session->setVar("import_KeywordSeparator", $this->vars['import_KeywordSeparator'])) {
            $this->badInput($this->errors->text("sessionError", "write"));
        }
        if (!$this->session->setVar("import_TitleSubtitleSeparator", $this->vars['import_TitleSubtitleSeparator'])) {
            $this->badInput($this->errors->text("sessionError", "write"));
        }
        if (array_key_exists('import_ImportDuplicates', $this->vars) && $this->vars['import_ImportDuplicates']) {
            if (!$this->session->setVar("import_ImportDuplicates", 1)) {
                $this->badInput($this->errors->text("sessionError", "write"));
            }
        }
        // Force to 1 => 'General' category
        if (!$this->session->getVar("import_Categories")) {
            if (!$this->session->setVar("import_Categories", 1)) {
                $this->badInput($this->errors->text("sessionError", "write"));
            }
        }
        if (trim($this->vars['import_Tag'])) {
            if (!$tagId = $this->tag->checkExists(trim($this->vars['import_Tag']))) {
                if (!$this->session->setVar("import_Tag", trim($this->vars['import_Tag']))) {
                    $this->badInput($this->errors->text("sessionError", "write"));
                }
            } else {
                if (!$this->session->setVar("import_TagId", $tagId)) {
                    $this->badInput($this->errors->text("sessionError", "write"));
                }
            }
        } elseif (array_key_exists('import_TagId', $this->vars) && $this->vars['import_TagId']) {
            if (!$this->session->setVar("import_TagId", $this->vars['import_TagId'])) {
                $this->badInput($this->errors->text("sessionError", "write"));
            }
        }
    }
    /*
     * validate and store input in session
     *
     * @return array
     */
    private function validateInput()
    {
        $inputFound = FALSE;
        if (array_key_exists('importpubMed_ID', $this->vars) && preg_match("/\\d/u", $this->vars['importpubMed_ID'])) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_ID", $this->vars['importpubMed_ID']);
        }
        if (array_key_exists('importpubMed_ALL', $this->vars) && (mb_strlen($this->vars['importpubMed_ALL']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_ALL", $this->vars['importpubMed_ALL']);
        }
        if (array_key_exists('importpubMed_AU', $this->vars) && (mb_strlen($this->vars['importpubMed_AU']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_AU", $this->vars['importpubMed_AU']);
        }
        if (array_key_exists('importpubMed_1AU', $this->vars) && (mb_strlen($this->vars['importpubMed_1AU']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_1AU", $this->vars['importpubMed_1AU']);
        }
        if (array_key_exists('importpubMed_LASTAU', $this->vars) && (mb_strlen($this->vars['importpubMed_LASTAU']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_LASTAU", $this->vars['importpubMed_LASTAU']);
        }
        if (array_key_exists('importpubMed_TI', $this->vars) && (mb_strlen($this->vars['importpubMed_TI']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_TI", $this->vars['importpubMed_TI']);
        }
        if (array_key_exists('importpubMed_TIAB', $this->vars) && (mb_strlen($this->vars['importpubMed_TIAB']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_TIAB", $this->vars['importpubMed_TIAB']);
        }
        if (array_key_exists('importpubMed_DP', $this->vars) && (mb_strlen($this->vars['importpubMed_DP']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_DP", $this->vars['importpubMed_DP']);
        }
        if (array_key_exists('importpubMed_TA', $this->vars) && (mb_strlen($this->vars['importpubMed_TA']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_TA", $this->vars['importpubMed_TA']);
        }
        if (array_key_exists('importpubMed_VI', $this->vars) && (mb_strlen($this->vars['importpubMed_VI']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_VI", $this->vars['importpubMed_VI']);
        }
        if (array_key_exists('importpubMed_IP', $this->vars) && (mb_strlen($this->vars['importpubMed_IP']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_IP", $this->vars['importpubMed_IP']);
        }
        if (array_key_exists('importpubMed_LA', $this->vars) && (mb_strlen($this->vars['importpubMed_LA']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_LA", $this->vars['importpubMed_LA']);
        }
        if (array_key_exists('importpubMed_reldate', $this->vars) && (mb_strlen($this->vars['importpubMed_reldate']) !== 0)) {
            $inputFound = TRUE;
            $this->session->setVar("importpubMed_reldate", $this->vars['importpubMed_reldate']);
        }

        if (array_key_exists('importpubMed_MaxResults', $this->vars)) {
            if (!is_numeric(trim($this->vars['importpubMed_MaxResults']))) {
                $this->badInput(HTML\p($this->pluginmessages->text("importPubMedLimitError"), 'error'));
            }
            if ((trim($this->vars['importpubMed_MaxResults']) < 1) ||
            (trim($this->vars['importpubMed_MaxResults']) > 100)) {
                $this->vars['importpubMed_MaxResults'] = 100; // default
            }
            $this->session->setVar("importpubMed_MaxResults", $this->vars['importpubMed_MaxResults']);
        } else {
            $this->session->setVar("importpubMed_MaxResults", 100);
        }
        if (!$inputFound) {
            $this->badInput(HTML\p($this->pluginmessages->text("importPubMedInputError"), 'error'));
        }

        return $this->session->getArray('importpubMed');
    }
    /*
     * bad Input function
     *
     * @param string $error
     */
    private function badInput($error)
    {
        $pString = $this->displayImport($error);
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSE::getInstance();
    }
}
