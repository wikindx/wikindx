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
    public $filesUrl;
    private $vars;
    private $messages;
    private $config;
    private $bibutilsPath;
    private $outputTypesArray = [];
    private $formData = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = FACTORY_MESSAGES::getInstance();
        if (!$this->bibutilsPath = WIKINDX_BIBUTILS_PATH) {
            $this->bibutilsPath = WIKINDX_BIBUTILS_UNIXPATH_DEFAULT;
        }
        $this->filesDir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]) . DIRECTORY_SEPARATOR;
        $this->filesUrl = implode("/", [WIKINDX_URL_BASE, WIKINDX_URL_DATA_FILES]) . "/";
        $this->vars = GLOBALS::getVars();
        $this->outputTypesArray = $this->outputTypes();
        if (empty($this->outputTypesArray)) {
            $pString .= HTML\p($this->messages->text("importexport", "bibutilsnoPrograms", $this->bibutilsPath), "error", "center");
            die($pString);
        }
    }
    
    /**
     * init
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
		GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibutils"));
    	if (!\FILE\command_exists($this->bibutilsPath . 'bib2xml')) {
			$pString = \HTML\p($this->messages->text("importexport", "bibutilsnoPrograms", $this->bibutilsPath), "error", "center");
			$pString .= \HTML\p($this->messages->text("importexport", "bibutilscredit", \HTML\a(
				"link",
				'Bibutils',
				'https://sourceforge.net/p/bibutils/home/Bibutils/',
				'_blank'
			)));
			GLOBALS::addTplVar('content', $pString);
            FACTORY_CLOSE::getInstance();
        }
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'ajax'))
        {
            $div = $this->createOutputTypes();
            GLOBALS::addTplVar('content', AJAX\encode_jArray(['innerHTML' => $div]));
            FACTORY_CLOSERAW::getInstance();
        }
        $pString = $message ? $message : FALSE;
        $pString .= $this->form();
        GLOBALS::addTplVar('content', $pString);
    }
    
    /**
     * This is the initial form called from the init()
     *
     * @param mixed $error
     *
     * @return string
     */
    private function form($error = FALSE)
    {
        $pString = HTML\p($this->messages->text("importexport", "bibutilscredit", HTML\a(
            "link",
            'Bibutils',
            'https://sourceforge.net/p/bibutils/home/Bibutils/',
            '_blank'
        )));
        if ($error)
        {
            $pString .= HTML\p($error, "error", "center");
        }
        // Conversion options
        $inputTypes = $this->inputTypes();
        if (empty($inputTypes))
        {
            $pString .= HTML\p($this->messages->text("importexport", "bibutilsnoPrograms", $this->bibutilsPath), "error", "center");

            return $pString;
        }
        $options = $this->options();
        $pString .= FORM\formMultiHeader("bibutils_BIBUTILS_CORE");
        $pString .= \FORM\hidden('method', 'startProcess');
        $pString .= HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= HTML\trStart();
        if (!array_key_exists('inputType', $this->formData) || (!$selectedInput = $this->formData['inputType']))
        {
            $selectedInput = "bib2xml";
        }
        $jScript = 'index.php?action=bibutils_BIBUTILS_CORE&method=init&type=ajax';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'inputType',
            'targetDiv' => 'outputType',
        ];
        $js = AJAX\jActionForm('onchange', $jsonArray);
        $pString .= HTML\td(FORM\selectedBoxValue($this->messages->text("importexport", "bibutilsinputType"), 
        	"inputType", $inputTypes, $selectedInput, 9, FALSE, $js));
        $pString .= HTML\td($this->createOutputTypes());
        if (!array_key_exists('options', $this->formData) || (!$selected = $this->formData['options']))
        {
            $pString .= HTML\td(FORM\selectFBoxValueMultiple($this->messages->text("importexport", "bibutilsxmlOptions"), "options", $options, 6, TRUE) .
                BR . HTML\span($this->messages->text('hint', 'multiples'), 'hint'));
        }
        else
        {
            $pString .= HTML\td(FORM\selectedBoxValueMultiple($this->messages->text("importexport", "bibutilsxmlOptions"), "options", $options, $selected, 6, TRUE) .
                BR . HTML\span($this->messages->text('hint', 'multiples'), 'hint'));
        }
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        
        if (ini_get("file_uploads"))
        {
            $pString .= HTML\p(FORM\fileUpload($this->messages->text("importexport", "bibutilsinputFile"), "file", 30, ".bib"));
            $pString .= " (max.&nbsp;" . \FILE\formatSize(\FILE\fileUploadMaxSize()) . ") ";
            $pString .= HTML\p(FORM\formSubmit($this->messages->text("submit", "Submit")));
        }
        else
        {
            $pString .= \HTML\p($this->messages->text("misc", "uploadDisabled"));
        }
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
            $this->messages->text("importexport", "bibutilsoutputType"),
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
    	GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibutils"));
        $inputFile = $this->validateInput();
        $options = '';
        $unicodeOption = FALSE;
        foreach ($this->vars['options'] as $option)
        {
            if (!$option)
            {
                continue;
            }
            if ($option == 1)
            {
                $options .= ' -u';
            }
            elseif ($option == 2)
            {
                $options .= ' -d';
            }
            elseif ($option == 3)
            {
                $options .= ' -nt';
            }
            elseif ($option == 4)
            {
                $options .= ' -nl';
            }
            elseif ($option == 5)
            {
                $unicodeOption = TRUE;
            }
        }
        if ($unicodeOption)
        {
            $options .= ' -i unicode';
        }
        $pString = '';
        $tempFile = $this->filesDir . \UTILS\uuid() . ".mod";
        $baseName = \UTILS\uuid();
        if ($this->vars['outputType'] == 'xml2bib')
        {
            $outputFile = $this->filesDir . $baseName . ".bib";
            $linkFile = $baseName . ".bib";
        }
        elseif ($this->vars['outputType'] == 'xml2ris')
        {
            $outputFile = $this->filesDir . $baseName . ".ris";
            $linkFile = $baseName . ".ris";
        }
        elseif ($this->vars['outputType'] == 'xml2end')
        {
            $outputFile = $this->filesDir . $baseName . ".end";
            $linkFile = $baseName . ".end";
        }
        elseif ($this->vars['outputType'] == 'xml2wordbib')
        {
            $outputFile = $this->filesDir . $baseName . ".xml";
            $linkFile = $baseName . ".xml";
        }
        elseif ($this->vars['outputType'] == 'xml2ads')
        {
            $outputFile = $this->filesDir . $baseName . ".ads";
            $linkFile = $baseName . ".ads";
        }
        elseif ($this->vars['outputType'] == 'mods')
        {
            $outputFile = $this->filesDir . $baseName . ".xml";
            $linkFile = $baseName . ".xml";
        }
        if ($this->vars['inputType'] == 'mods')
        {
            $command1 = $this->vars['outputType'] . " $inputFile > $outputFile 2>&1";
        }
        elseif ($this->vars['outputType'] == 'mods')
        {
            $command1 = $this->vars['inputType'] . " $options $inputFile > $outputFile 2>&1";
        }
        else
        {
            $command1 = $this->vars['inputType'] . " $options $inputFile > $tempFile 2>&1";
            $outputOption = $unicodeOption ? "-o unicode" : FALSE;
            $command2 = $this->vars['outputType'] . " $outputOption $tempFile > $outputFile 2>&1";
        }
        $this->process($command1, $inputFile);
        if (isset($command2))
        {
            $this->process($command2, FALSE);
        }
        @unlink($tempFile);
        @unlink($inputFile);
        if (!isset($outputFile))
        {
            $this->badInput($this->messages->text("importexport", 'bibutilsfailedToConvert'));
        }
        $pString .= HTML\p($this->messages->text("importexport", 'bibutilsSuccess', HTML\a(
            "link",
            $this->messages->text("importexport", 'bibutilsoutputFile'),
            $this->filesUrl . $linkFile,
            "_blank"
        )), 'success');
        $pString .= HTML\hr();
        FILE\tidyFiles();

        $this->init($pString);
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
        if (array_key_exists('ajaxReturn', $this->vars))
        {
            $invalidKey = $this->vars['ajaxReturn'];
            if (array_key_exists($invalidKey, $invalid) && ($invalid[$invalidKey] == $this->formData['outputType']))
            {
                unset($this->formData['outputType']);
            }
        }
        elseif (array_key_exists('inputType', $this->formData) && ($selectedInput = $this->formData['inputType']))
        {
            $invalidKey = $selectedInput;
        }
        else
        {
            $invalidKey = 'bib2xml';
        }
        if (array_key_exists($invalidKey, $invalid))
        {
            unset($this->outputTypesArray[$invalid[$invalidKey]]);
        }
        if (array_key_exists('outputType', $this->formData) && ($selectedOutput = $this->formData['outputType']) &&
            (array_search($selectedOutput, $invalid) !== FALSE))
        {
            $return = $selectedOutput;
        }
        else
        {
            $temp = array_keys($this->outputTypesArray);
            $return = array_shift($temp); // grab first element
        }
        if (!$return)
        {
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
            0 => $this->messages->text('misc', 'ignore'),
            1 => $this->messages->text("importexport", 'bibutilsoption1'),
            2 => $this->messages->text("importexport", 'bibutilsoption2'),
            3 => $this->messages->text("importexport", 'bibutilsoption3'),
            4 => $this->messages->text("importexport", 'bibutilsoption4'),
            5 => $this->messages->text("importexport", 'bibutilsoption5'),
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
        if (FILE\command_exists($this->bibutilsPath . 'biblatex2xml'))
        {
            $array['bib2xml'] = 'BibTeX';
        }
        if (FILE\command_exists($this->bibutilsPath . 'bib2xml'))
        {
            $array['biblatex2xml'] = 'BibTeX LaTeX';
        }
        if (FILE\command_exists($this->bibutilsPath . 'ris2xml'))
        {
            $array['ris2xml'] = 'RIS';
        }
        if (FILE\command_exists($this->bibutilsPath . 'copac2xml'))
        {
            $array['copac2xml'] = 'COPAC';
        }
        if (FILE\command_exists($this->bibutilsPath . 'end2xml'))
        {
            $array['end2xml'] = 'Endnote (Refer Format)';
        }
        if (FILE\command_exists($this->bibutilsPath . 'endx2xml'))
        {
            $array['endx2xml'] = 'Endnote XML';
        }
        if (FILE\command_exists($this->bibutilsPath . 'isi2xml'))
        {
            $array['isi2xml'] = 'ISI';
        }
        if (FILE\command_exists($this->bibutilsPath . 'med2xml'))
        {
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
        if (file_exists($this->bibutilsPath . 'xml2bib'))
        {
            $array['xml2bib'] = 'BibTeX';
        }
        if (file_exists($this->bibutilsPath . 'xml2ris'))
        {
            $array['xml2ris'] = 'RIS';
        }
        if (file_exists($this->bibutilsPath . 'xml2end'))
        {
            $array['xml2end'] = 'Endnote (Refer Format)';
        }
        if (file_exists($this->bibutilsPath . 'xml2wordbib'))
        {
            $array['xml2wordbib'] = 'Word BIB';
        }
        if (file_exists($this->bibutilsPath . 'xml2ads'))
        {
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
        $cmd = $this->bibutilsPath . $command;
        if (\UTILS\OSName() == "windows")
        {
            $this->win_execute($cmd, $inputFile);
        }
        elseif (($result = exec($cmd, $output, $returnVar)) === FALSE)
        {
            @unlink($inputFile);
            $this->badInput($this->messages->text("importexport", 'bibutilsfailedToConvert', $returnVar));
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
        if ($oExec = $WshShell->Run($cmdline, 0, TRUE))
        {
            @unlink($inputFile);
            $this->badInput($this->messages->text("importexport", 'bibutilsfailedToConvert', $oExec));
        }
    }
    /**
     * bad Input function
     *
     * @param mixed $error
     */
    private function badInput($error)
    {
        $this->init(HTML\p($error, 'error'));
        FACTORY_CLOSE::getInstance();
    }
    /**
     * validate input
     *
     * @return string
     */
    private function validateInput()
    {
        if (array_key_exists('options', $this->vars))
        {
            foreach ($this->vars['options'] as $key)
            {
                if (!$key)
                {
                    continue;
                }
                $array[] = $key;
            }
            if (isset($array))
            {
                $this->formData['options'] = $array;
            }
            else
            {
                unset($this->formData['options']);
            }
        }
        else
        {
            unset($this->formData['options']);
        }
        if (!array_key_exists('inputType', $this->vars) || !$this->vars['inputType'])
        {
            $this->badInput($this->messages->text("importexport", 'bibutilsnoInputType'));
        }
        $this->formData['inputType'] = $this->vars['inputType'];
        if (!array_key_exists('outputType', $this->vars) || !$this->vars['outputType'])
        {
            $this->badInput($this->messages->text("importexport", 'bibutilsnoOutputType'));
        }
        $this->formData['outputType'] = $this->vars['outputType'];
        if (!array_key_exists('file', $_FILES))
        {
            if ($file = $this->formData['file'])
            {
                return $this->filesDir . $file;
            }
            $this->badInput($this->messages->text("importexport", 'bibutilsnoFileInput'));
        }
        $fileName = \UTILS\uuid();
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $this->filesDir . $fileName))
        {
            $this->badInput($this->messages->text("importexport", 'bibutilsnoFileInput'));
        }
        $this->formData['file'] = $fileName;

        return $this->filesDir . $fileName;
    }
}
