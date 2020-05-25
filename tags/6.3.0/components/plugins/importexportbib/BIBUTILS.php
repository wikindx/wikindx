<?php
/**
 * bibutils class.
 *
 * Convert a range of bibliographic formats to and from bibTeX.
 *
 * Makes use of 'bibutils' from http://sourceforge.net/p/bibutils/home/Bibutils/ written by Chris Putnam.
 *
 * @version 1.4
 */
class BIBUTILS
{
    public $filesDir;
    private $vars;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $session;
    private $config;
    private $parentClass;
    private $outputTypesArray = [];

    /**
     * Constructor.
     *
     * NB, on using this constructor to initialise the menu item(s) (see core/html/MENU.php), $db and $vars will be FALSE.
     * They become available when the user starts to use the module (called from index.php).
     * $menuInit is TRUE if called from MENU.php
     *
     * @param mixed $parentClass
     */
    public function __construct($parentClass)
    {
        $this->parentClass = $parentClass;

        $this->session = FACTORY_SESSION::getInstance();
        include_once("core/messages/PLUGINMESSAGES.php");
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "config.php");
        $this->config = new importexportbib_BIBUTILSCONFIG();
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        if (!$this->config->bibutilsPath) {
            $this->config->bibutilsPath = '/usr/local/bin/'; // default *NIX location
        }

        $this->filesDir = WIKINDX_DIR_DATA_FILES . DIRECTORY_SEPARATOR;
        $this->vars = GLOBALS::getVars();
        $this->outputTypesArray = $this->outputTypes();
        if (empty($this->outputTypesArray)) {
            $pString .= HTML\p($this->pluginmessages->text("bibutilsnoPrograms", $this->config->bibutilsPath), "error", "center");
            die($pString);
        }
    }
    /**
     * This is the initial method called from the menu item
     *
     * @param mixed $error
     *
     * @return string
     */
    public function init($error = FALSE)
    {
        $pString = HTML\p($this->pluginmessages->text("bibutilscredit", HTML\a(
            "link",
            'Bibutils',
            'https://sourceforge.net/p/bibutils/home/Bibutils/',
            '_blank'
        )));
        if ($error) {
            $pString .= HTML\p($error, "error", "center");
        }
        // Conversion options
        $inputTypes = $this->inputTypes();
        if (empty($inputTypes)) {
            $pString .= HTML\p($this->pluginmessages->text("bibutilsnoPrograms", $this->config->bibutilsPath), "error", "center");

            return $pString;
        }
        $options = $this->options();
        $pString .= FORM\formMultiHeader("importexportbib_processBibutils");
        $pString .= HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= HTML\trStart();
        if (!$selectedInput = $this->session->getVar("bibUtils_inputType")) {
            $selectedInput = "bib2xml";
        }
        $jScript = 'index.php?action=importexportbib_initBibutils&method=ajax';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'inputType',
            'targetDiv' => 'outputType',
        ];
        $js = AJAX\jActionForm('onchange', $jsonArray);
        $pString .= HTML\td(FORM\selectedBoxValue($this->pluginmessages->text("bibutilsinputType"), "inputType", $inputTypes, $selectedInput, 9, FALSE, $js));
        $pString .= HTML\td($this->createOutputTypes());
        if (!$selected = $this->session->getVar("bibUtils_options")) {
            $pString .= HTML\td(FORM\selectFBoxValueMultiple($this->pluginmessages->text("bibutilsxmlOptions"), "options", $options, 6, TRUE) .
                BR . HTML\span($this->coremessages->text('hint', 'multiples'), 'hint'));
        } else {
            $selected = unserialize(base64_decode($selected));
            $pString .= HTML\td(FORM\selectedBoxValueMultiple($this->pluginmessages->text("bibutilsxmlOptions"), "options", $options, $selected, 6, TRUE) .
                BR . HTML\span($this->coremessages->text('hint', 'multiples'), 'hint'));
        }
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p(FORM\fileUpload($this->pluginmessages->text("bibutilsinputFile"), "file", 30));
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();
        AJAX\loadJavascript();

        return $pString;
    }
    /**
     * createOutputTypes()
     *
     * @return string
     */
    public function createOutputTypes()
    {
        $selectedOutput = $this->changeOutputTypes();

        return HTML\div('outputType', FORM\selectedBoxValue(
            $this->pluginmessages->text("bibutilsoutputType"),
            "outputType",
            $this->outputTypesArray,
            $selectedOutput,
            6
        ));
    }
    /**
     * Convert input file to output file
     *
     * @return string
     */
    public function startProcess()
    {
        $inputFile = $this->validateInput();
        $options = '';
        $unicodeOption = FALSE;
        foreach ($this->vars['options'] as $option) {
            if (!$option) {
                continue;
            }
            if ($option == 1) {
                $options .= ' -u';
            } elseif ($option == 2) {
                $options .= ' -d';
            } elseif ($option == 3) {
                $options .= ' -nt';
            } elseif ($option == 4) {
                $options .= ' -nl';
            } elseif ($option == 5) {
                $unicodeOption = TRUE;
            }
        }
        if ($unicodeOption) {
            $options .= ' -i unicode';
        }
        $pString = '';
        $tempFile = $this->filesDir . \UTILS\uuid() . ".mod";
        $baseName = \UTILS\uuid();
        if ($this->vars['outputType'] == 'xml2bib') {
            $outputFile = $this->filesDir . $baseName . ".bib";
            $linkFile = $baseName . ".bib";
        } elseif ($this->vars['outputType'] == 'xml2ris') {
            $outputFile = $this->filesDir . $baseName . ".ris";
            $linkFile = $baseName . ".ris";
        } elseif ($this->vars['outputType'] == 'xml2end') {
            $outputFile = $this->filesDir . $baseName . ".end";
            $linkFile = $baseName . ".end";
        } elseif ($this->vars['outputType'] == 'xml2wordbib') {
            $outputFile = $this->filesDir . $baseName . ".xml";
            $linkFile = $baseName . ".xml";
        } elseif ($this->vars['outputType'] == 'xml2ads') {
            $outputFile = $this->filesDir . $baseName . ".ads";
            $linkFile = $baseName . ".ads";
        } elseif ($this->vars['outputType'] == 'mods') {
            $outputFile = $this->filesDir . $baseName . ".xml";
            $linkFile = $baseName . ".xml";
        }
        if ($this->vars['inputType'] == 'mods') {
            $command1 = $this->vars['outputType'] . " $inputFile > $outputFile 2>&1";
        } elseif ($this->vars['outputType'] == 'mods') {
            $command1 = $this->vars['inputType'] . " $options $inputFile > $outputFile 2>&1";
        } else {
            $command1 = $this->vars['inputType'] . " $options $inputFile > $tempFile 2>&1";
            $outputOption = $unicodeOption ? "-o unicode" : FALSE;
            $command2 = $this->vars['outputType'] . " $outputOption $tempFile > $outputFile 2>&1";
        }
        $this->process($command1, $inputFile);
        if (isset($command2)) {
            $this->process($command2, FALSE);
        }
        @unlink($tempFile);
        @unlink($inputFile);
        if (!isset($outputFile)) {
            $this->badInput($this->pluginmessages->text('bibutilsfailedToConvert'));
        }
        $pString .= HTML\p($this->pluginmessages->text('bibutilsSuccess', HTML\a(
            "link",
            $this->pluginmessages->text('bibutilsoutputFile'),
            $this->filesDir . $linkFile,
            "_blank"
        )), 'success');
        $pString .= HTML\hr();
        FILE\tidyFiles();

        return $pString . $this->init();
    }
    /**
     * Change outputTypes array depending upon inputTypes selection
     *
     * @return string
     */
    private function changeOutputTypes()
    {
        $return = FALSE;
        $invalid = [
            'bib2xml' => 'xml2bib',
            'ris2xml' => 'xml2ris',
            'end2xml' => 'xml2end',
            'mods' => 'mods',
        ];
        if (array_key_exists('ajaxReturn', $this->vars)) {
            $invalidKey = $this->vars['ajaxReturn'];
            if (array_key_exists($invalidKey, $invalid) && ($invalid[$invalidKey] == $this->session->getVar("bibUtils_outputType"))) {
                $this->session->delVar("bibUtils_outputType");
            }
        } elseif ($selectedInput = $this->session->getVar("bibUtils_inputType")) {
            $invalidKey = $selectedInput;
        } else {
            $invalidKey = 'bib2xml';
        }
        if (array_key_exists($invalidKey, $invalid)) {
            unset($this->outputTypesArray[$invalid[$invalidKey]]);
        }
        if (($selectedOutput = $this->session->getVar("bibUtils_outputType")) &&
            (array_search($selectedOutput, $invalid) !== FALSE)) {
            $return = $selectedOutput;
        } else {
            $temp = array_keys($this->outputTypesArray);
            $return = array_shift($temp); // grab first element
        }
        if (!$return) {
            $return = 'xml2bib';
        }

        return $return;
    }
    /**
     * Display conversion options
     *
     * @return array
     */
    private function options()
    {
        $array = [
            0 => $this->coremessages->text('misc', 'ignore'),
            1 => $this->pluginmessages->text('bibutilsoption1'),
            2 => $this->pluginmessages->text('bibutilsoption2'),
            3 => $this->pluginmessages->text('bibutilsoption3'),
            4 => $this->pluginmessages->text('bibutilsoption4'),
            5 => $this->pluginmessages->text('bibutilsoption5'),
        ];

        return $array;
    }
    /**
     * Input types
     *
     * @return array
     */
    private function inputTypes()
    {
        $array = [];
        if (FILE\command_exists($this->config->bibutilsPath . 'biblatex2xml')) {
            $array['bib2xml'] = 'BibTeX';
        }
        if (FILE\command_exists($this->config->bibutilsPath . 'bib2xml')) {
            $array['biblatex2xml'] = 'BibTeX LaTeX';
        }
        if (FILE\command_exists($this->config->bibutilsPath . 'ris2xml')) {
            $array['ris2xml'] = 'RIS';
        }
        if (FILE\command_exists($this->config->bibutilsPath . 'copac2xml')) {
            $array['copac2xml'] = 'COPAC';
        }
        if (FILE\command_exists($this->config->bibutilsPath . 'end2xml')) {
            $array['end2xml'] = 'Endnote (Refer Format)';
        }
        if (FILE\command_exists($this->config->bibutilsPath . 'endx2xml')) {
            $array['endx2xml'] = 'Endnote XML';
        }
        if (FILE\command_exists($this->config->bibutilsPath . 'isi2xml')) {
            $array['isi2xml'] = 'ISI';
        }
        if (FILE\command_exists($this->config->bibutilsPath . 'med2xml')) {
            $array['med2xml'] = 'PubMed';
        }
        $array['mods'] = 'MODS';

        return $array;
    }
    /**
     * Output types
     *
     * @return array
     */
    private function outputTypes()
    {
        $array = [];
        if (file_exists($this->config->bibutilsPath . 'xml2bib')) {
            $array['xml2bib'] = 'BibTeX';
        }
        if (file_exists($this->config->bibutilsPath . 'xml2ris')) {
            $array['xml2ris'] = 'RIS';
        }
        if (file_exists($this->config->bibutilsPath . 'xml2end')) {
            $array['xml2end'] = 'Endnote (Refer Format)';
        }
        if (file_exists($this->config->bibutilsPath . 'xml2wordbib')) {
            $array['xml2wordbib'] = 'Word BIB';
        }
        if (file_exists($this->config->bibutilsPath . 'xml2ads')) {
            $array['xml2ads'] = 'ADS';
        }
        $array['mods'] = 'MODS';

        return $array;
    }
    /**
     * run bibutils executables
     *
     * @param mixed $command
     * @param mixed $inputFile
     */
    private function process($command, $inputFile)
    {
        $cmd = $this->config->bibutilsPath . $command;
        if (getenv("OS") == "Windows_NT") {
            $this->win_execute($cmd, $inputFile);
        } elseif (($result = exec($cmd, $output, $returnVar)) === FALSE) {
            @unlink($inputFile);
            $this->badInput($this->pluginmessages->text('bibutilsfailedToConvert', $returnVar));
        }
    }
    /**
     * If Windows server, execute command this way.
     *
     * Thanks to Richard Karnesky of refbase.
     *
     * @param mixed $cmd
     * @param mixed $inputFile
     */
    private function win_execute($cmd, $inputFile)
    {
        $cmdline = "cmd /C " . $cmd;
        // Make a new instance of the COM object
        $WshShell = new COM("WScript.Shell") or die("Failed to instantiate COM");
        // Make the command window but dont show it.
        if ($oExec = $WshShell->Run($cmdline, 0, TRUE)) {
            @unlink($inputFile);
            $this->badInput($this->pluginmessages->text('bibutilsfailedToConvert', $oExec));
        }
    }
    /**
     * bad Input function
     *
     * @param mixed $error
     */
    private function badInput($error)
    {
        $this->parentClass->initBibutils(HTML\p($error, 'error'));
        FACTORY_CLOSE::getInstance();
    }
    /**
     * validate input
     *
     * @return string
     */
    private function validateInput()
    {
        if (array_key_exists('options', $this->vars)) {
            foreach ($this->vars['options'] as $key) {
                if (!$key) {
                    continue;
                }
                $array[] = $key;
            }
            if (isset($array)) {
                $this->session->setVar("bibUtils_options", base64_encode(serialize($array)));
            } else {
                $this->session->delVar("bibUtils_options");
            }
        } else {
            $this->session->delVar("bibUtils_options");
        }
        if (!array_key_exists('inputType', $this->vars) || !$this->vars['inputType']) {
            $this->badInput($this->pluginmessages->text('bibutilsnoInputType'));
        }
        $this->session->setVar("bibUtils_inputType", $this->vars['inputType']);
        if (!array_key_exists('outputType', $this->vars) || !$this->vars['outputType']) {
            $this->badInput($this->pluginmessages->text('bibutilsnoOutputType'));
        }
        $this->session->setVar("bibUtils_outputType", $this->vars['outputType']);
        if (!array_key_exists('file', $_FILES)) {
            if ($file = $this->session->getVar("bibUtils_file")) {
                return $this->filesDir . $file;
            }
            $this->badInput($this->pluginmessages->text('bibutilsnoFileInput'));
        }
        $fileName = \UTILS\uuid();
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $this->filesDir . $fileName)) {
            $this->badInput($this->pluginmessages->text('bibutilsnoFileInput'));
        }
        $this->session->setVar("bibUtils_file", $fileName);

        return $this->filesDir . $fileName;
    }
}
