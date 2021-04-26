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
 *	IDEA export class
 */
class IDEAEXPORT
{
    private $db;
    private $vars;
    private $errors;
    private $session;
    private $messages;
    private $cite;
    private $bibStyle;
    private $styles;
    private $pString;
    private $ubi;
    private $ubiBib;
    private $fonts;
    private $fontSizes;
    private $indentTabs;
    private $dividerCR;
    private $cr;
    private $fontBlocks;
    private $common;
    private $rtf;
    private $userObj;
    private $bodyTempFile;
    private $formattedText = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->cite = FACTORY_CITE::getInstance('rtf');
        $this->bibStyle = FACTORY_BIBSTYLE::getInstance('rtf');
        $this->styles = LOADSTYLE\loadDir();
        $this->common = FACTORY_EXPORTCOMMON::getInstance();
        $this->rtf = FACTORY_RICHTEXTFORMAT::getInstance();
        $this->userObj = FACTORY_USER::getInstance();
        $this->fontSizes = [
            1 => 8, 2 => 10, 3 => 12, 4 => 14, 5 => 16, 6 => 18, 7 => 20, 8 => 22,
        ];
        $this->fonts = [
            1 => "Arial",
            2 => "Courier",
            3 => "Georgia",
            4 => "Helvetica",
            5 => "MS Sans Serif",
            6 => "MS Serif",
            7 => "Palatino",
            8 => "Tahoma",
            9 => "Trebuchet MS",
            10 => "Times New Roman",
            11 => "Verdana",
        ];
        $this->ubi = [
            1 => "Normal",
            2 => "Italics",
            3 => "Bold",
            4 => "Underline",
        ];
        $this->ubiBib = [
            1 => "Normal",
            2 => "Bold",
        ];
        $this->indentTabs = [1 => 0, 2 => 1, 3 => 2, 4 => 3];
        $this->cr = [1 => 1, 2 => 2, 3 => 3, 4 => 4];
        $this->dividerCR = [1 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 4];
    }

    /**
     * initIdeaExport
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "ideaExport"));
        $pString = $message ? $message : FALSE;
        $pString .= $this->exportOptions();
        GLOBALS::clearTplVar('pagingList');
        GLOBALS::addTplVar('content', $pString);
    }    /**
     * Display options for exporting
     *
     * @return string
     */
    private function exportOptions()
    {
        $pString = \FORM\formHeader("ideas_IDEAEXPORT_CORE");
        $pString .= \FORM\hidden('method', 'process');
        $selectBox = [1 => $this->messages->text("importexport", "allIdeas"), 2 => $this->messages->text("importexport", "selectedIdeas")];
        $selectBox = \FORM\selectFBoxValue(FALSE, "selectIdea", $selectBox, 2);
        $pString .= \HTML\p($selectBox . '&nbsp;' . \FORM\formSubmit($this->messages->text("submit", "Proceed")), FALSE, "right");
        $this->ideaList();

        return $pString;
    }
    /**
     * list available ideas
     */
    private function ideaList()
    {
        $userObj = FACTORY_USER::getInstance();
        $cite = FACTORY_CITE::getInstance();
        $multiUser = WIKINDX_MULTIUSER;
        $ideaList = [];
        $index = 0;
        // now get ideas
        // Check this user is allowed to read the idea.
        $this->db->formatConditions(['resourcemetadataMetadataId' => ' IS NULL']);
        if (!$this->common->setIdeasCondition())
        {
            $this->failure(HTML\p($this->messages->text("importexport", "noIdeas"), 'error'));
        }
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataTimestamp', 'resourcemetadataTimestampEdited',
            'resourcemetadataMetadataId', 'resourcemetadataText', 'resourcemetadataAddUserId', 'resourcemetadataPrivate', ]);
        while ($row = $this->db->fetchRow($resultset))
        {
            $ideaList[$index]['metadata'] = $cite->parseCitations($row['resourcemetadataText'], 'html');
            if ($multiUser)
            {
                list($user) = $userObj->displayUserAddEdit($row['resourcemetadataAddUserId'], FALSE, 'idea');
                if ($row['resourcemetadataTimestampEdited'] == '0000-00-00 00:00:00')
                {
                    $ideaList[$index]['user'] = '&nbsp;' . $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']);
                }
                else
                {
                    $ideaList[$index]['user'] = '&nbsp;' . $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']) .
                    ',&nbsp;' . $this->messages->text('hint', 'editedBy', $user . '&nbsp;' . $row['resourcemetadataTimestampEdited']);
                }
            }
            $ideaList[$index]['links'] = ['&nbsp;' . \FORM\checkbox(FALSE, 'checkbox_' . $row['resourcemetadataId'], FALSE)];
            ++$index;
        }
        if (!$index)
        {
            $this->failure(HTML\p($this->messages->text("importexport", "noIdeas"), 'error'));
        }
        $ideaList[--$index]['links'][] .= \FORM\formEnd();
        GLOBALS::addTplVar('ideaTemplate', TRUE);
        GLOBALS::addTplVar('ideaList', $ideaList);
    }
    /**
     * listFiles
     *
     * @param false|string $message
     * @param false|string $errorMethod
     *
     * @return string
     */
    public function listFiles($message = FALSE, $errorMethod = FALSE)
    {
        $errors = FACTORY_ERRORS::getInstance();
        // Perform some system admin
        FILE\tidyFiles();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "listFiles"));
        list($dirName, $deletePeriod, $fileArray) = FILE\listFiles();

        if (!$dirName)
        {
            if (!$fileArray)
            {
                GLOBALS::addTplVar('content', HTML\p($this->messages->text("importexport", "noContents"), 'error'));
            }
            elseif (!$errorMethod)
            {
                GLOBALS::addTplVar('content', $errors->text('file', "read"));
            }
            else
            {
                $this->{$errorMethod}($errors->text("file", "read"));
            }

            return;
        }
        if (array_key_exists('uuid', $this->vars))
        {
            $data = \TEMPSTORAGE\fetch($this->db, $this->vars['uuid']);
            if (is_array($data))
            { // FALSE if no longer there (reloading page e.g.)
                \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
                $message = $data['message'];
            }
        }
        $pString = $message;
        $filesDir = TRUE;
        $pString .= HTML\p($this->messages->text("importexport", "contents"));
        $minutes = $deletePeriod / 60;
        if (!empty($fileArray))
        {
            foreach ($fileArray as $key => $value)
            {
                $pString .= date(DateTime::W3C, filemtime($dirName . DIRECTORY_SEPARATOR . $key)) . ': ';
                $pString .= HTML\a("link", $key, "index.php?action=ideas_IDEAEXPORT_CORE&method=downloadFile" .
                htmlentities("&filename=" . $key), "_blank") . BR . LF;
            }
        }
        $pString .= HTML\hr();
        $pString .= HTML\p($this->messages->text("importexport", "warning", " $minutes "));
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * /**
     * downloadFile
     */
    public function downloadFile()
    {
        $filepath = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES, $this->vars['filename']]);
        if (file_exists($filepath))
        {
            switch (pathinfo($filepath)['extension']) {
                case 'bib':
                    $type = WIKINDX_MIMETYPE_BIB;
                    $charset = 'UTF-8';

                break;
                case 'html':
                    $type = WIKINDX_MIMETYPE_HTML;
                    $charset = 'UTF-8';

                break;
                case 'ris':
                    $type = WIKINDX_MIMETYPE_RIS;
                    $charset = 'UTF-8';

                break;
                case 'rtf':
                    $type = WIKINDX_MIMETYPE_RTF;
                    $charset = 'Windows-1252';

                break;
                case 'xml':
                    $type = WIKINDX_MIMETYPE_ENDNOTE;
                    $charset = 'UTF-8';

                break;
            }
            $size = filesize($filepath);
            $lastmodified = date(DateTime::RFC1123, filemtime($filepath));
            FILE\setHeaders($type, $size, basename($filepath), $lastmodified, $charset);
            FILE\readfile_chunked($filepath);
        }
        else
        {
            header('HTTP/1.0 404 Not Found');
            $this->badInput->closeType = 'closePopup';
            $this->badInput->close($this->errors->text("file", "missing"));
        }
        die;
    }
    
    /*
     * Open a memory stream file
     *
     * @return resource
     */
    public function openTempFile()
    {
        $fd = fopen("php://memory", 'r+');

        return $fd;
    }

    /*
     * Close a temporary file
     *
     * @param resourceb $fd
     */
    public function closeTempFile($fd)
    {
        return fclose($fd);
    }

    /*
     * write $this->pString to file.  If file exists, it is written over
     */
    public function process()
    {
        $this->prepareFontBlocks();
        // The body will be written in memory by a PHP stream
        $this->bodyTempFile = $this->openTempFile();
        $this->db->formatConditions(['resourcemetadataMetadataId' => ' IS NULL']); //main ideas only
        if (!$this->common->setIdeasCondition())
        {
            $this->failure(HTML\p($this->messages->text("importexport", "noIdeas"), 'error'));
        }
        if (array_key_exists('selectIdea', $this->vars) && ($this->vars['selectIdea'] == 2))
        {
            $ids = [];
            foreach ($this->vars as $key => $var)
            {
                $split = explode('checkbox_', $key);
                if (count($split) == 2)
                {
                    $ids[] = $split[1];
                }
            }
            if (!empty($ids))
            { // else, default to all ideas
                $this->db->formatConditionsOneField($ids, 'resourcemetadataId');
            }
        }
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataTimestamp',
            'resourcemetadataTimestampEdited',
            'resourcemetadataMetadataId', 'resourcemetadataText', 'resourcemetadataAddUserId', 'resourcemetadataPrivate', ]);
        $this->getData($resultset);
        if (!$this->common->openFile('.rtf', 'a'))
        {
            $this->failure($this->errors->text('file', 'write', ': ' . $this->common->fileName));
        }
        // Headers are built after body because we have to extract
        // color and font declarations before from the body
        $header = $this->rtfHeader();
        if (!fwrite($this->common->fp, $header))
        {
            $this->failure($this->errors->text('file', 'write', ': ' . $this->common->fileName));
        }
        // Go to the head of the body stream,
        // read it by chunk and write it to the RTF file
        rewind($this->bodyTempFile);
        do
        {
            $data = fgets($this->bodyTempFile, 1024);
            if ($data !== FALSE)
            {
                if (!fwrite($this->common->fp, $data))
                {
                    $this->failure($this->errors->text('file', 'write', ': ' . $this->common->fileName));
                }
            }
        } while ($data !== FALSE);
        $this->closeTempFile($this->bodyTempFile);
        $footer = $this->rtfFooter();
        if (!fwrite($this->common->fp, $footer))
        {
            $this->failure($this->errors->text('file', 'write', ': ' . $this->common->fileName));
        }
        $this->common->closeFile();
        $pString = HTML\p($this->messages->text("importexport", 'exported') . ': ' . $this->common->fileName, 'success');
        $this->common->writeFilenameToSession($this->common->fileName);
        $this->listFiles($pString, 'initRtfExport');
    }
    /*
     * get data from database
     *
     * @param object $recordset
     */
    private function getData($recordset)
    {
        $mainArray = [];
        $multiUser = WIKINDX_MULTIUSER;
        while ($row = $this->db->fetchRow($recordset))
        {
            $string = $this->textFormat($row['resourcemetadataText']);
            if ($multiUser)
            {
                $string .= "__WIKINDX__NEWLINEPAR__";
                list($user) = $this->userObj->displayUserAddEdit($row['resourcemetadataAddUserId'], FALSE, 'idea');
                if ($row['resourcemetadataTimestampEdited'] == '0000-00-00 00:00:00')
                {
                    $string .= $this->messages->text('hint', 'addedBy', $user . ' ' . $row['resourcemetadataTimestamp']);
                }
                else
                {
                    $string .= $this->messages->text('hint', 'addedBy', $user . ' ' . $row['resourcemetadataTimestamp']) .
                    ', ' . $this->messages->text('hint', 'editedBy', $user . ' ' . $row['resourcemetadataTimestampEdited']);
                }
                $string = str_replace("__WIKINDX__NEWLINEPAR__", "\\par\n", $string);
            }
            $mainArray[] = $string;
        }
        foreach ($mainArray as $text)
        {
            $fullText = $this->rtf->utf8_2_rtfansicpg1252($this->removeSlashes($text));
            // Do divider
            $fullText .= $this->makeBlock('divider');
            $fullText .= $this->rtfParagraphBlock('divider');
            $fullText .= '\par__________________________________________' . '\par }' . LF;
            for ($i = 0; $i < $this->dividerCR[3]; $i++)
            {
                $fullText .= $this->makeParagraph('divider') . LF;
            }
            // Cut the string in smaller pieces to isolate hexfile name for other content
            $tString = preg_split('/(##' . preg_quote(WIKINDX_URL_CACHE_FILES, "/") . '\/hex[0-9a-zA-Z]+\.txt##)/u', $fullText, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            // Write the ressource in the tempfile by chunk
            $k = 0;
            for ($k = 0; $k < count($tString); $k++)
            {
                $c = $tString[$k];
                // Is an image: replace hexfile names by the content of these files
                if (\UTILS\matchPrefix($c, '##' . WIKINDX_URL_CACHE_FILES . '/hex'))
                {
                    $c = str_replace('#', '', $c);
                    $this->writeImageRTF($this->bodyTempFile, str_replace(["\\", "/"], DIRECTORY_SEPARATOR, $c));
                    @unlink($c);
                }
                // Not an image
                else
                {
                    if (!fwrite($this->bodyTempFile, $c))
                    {
                        $this->failure($this->errors->text('file', 'write', ': ' . $this->common->fileName));
                    }
                }
            }
        }
    }
    /**
     * Write an image encoded for RTF to a stream
     *
     * @param handle &$fdOutputFile
     * @param string $imageFileName
     */
    private function writeImageRTF(&$fdOutputFile, $imageFileName)
    {
        $BUFFER_SIZE = 1024;

        if (file_exists($imageFileName))
        {
            $fdImage = fopen($imageFileName, 'rb');

            if ($fdImage !== FALSE)
            {
                do
                {
                    $data = fgets($fdImage, $BUFFER_SIZE);
                    if ($data !== FALSE)
                    {
                        if (!fwrite($fdOutputFile, $data))
                        {
                            $this->failure($this->errors->text('file', 'write', ': ' . $this->common->fileName));
                        }
                    }
                } while ($data !== FALSE);

                fclose($fdImage);
            }
        }
        else
        {
            if (!fwrite($fdOutputFile, $imageFileName))
            {
                $this->failure($this->errors->text('file', 'write', ': ' . $this->common->fileName));
            }
        }
    }
    /**
     * Remove extra slashes
     *
     * @param mixed $text
     *
     * @return string
     */
    private function removeSlashes($text)
    {
        $pattern = [
            "/\\\\{/u",
            "/\\\\}/u",
            "/\\\\\\\\/u",
        ];
        $change = [
            '{',
            '}',
            "\\",
        ];

        return preg_replace($pattern, $change, $text);
    }
    /*
     * RTF header
     *
     * @return string
     */
    private function rtfHeader()
    {
        $pString = '';
        $pString .= '{'; // Open RTF document
        $pString .= '\rtf1'; // Declare RTF document in plain text
        $pString .= '\ansi'; // Document encoded with ANSI
        $pString .= '\ansicpg1252'; // Document encoded with Windows-1252 charset
        $pString .= '\deff0'; // Default font is \f0 in fonts table
        $pString .= LF . LF;

        $pString .= $this->makeFontTable();
        $pString .= $this->rtfcolorTable();
        $pString .= $this->rtfstylesheetTable();

        $pString .= LF;
        $pString .= '\aftnnar' . LF . LF; // Endnote numbering?Arabic numbering
        $pString .= '\fet1' . LF . LF; // Footnote type : endnotes only

        $this->rtf->closeListTable();
        $pString .= $this->rtf->listTable;

        return $pString;
    }
    /*
     * RTF Footer
     *
     * @return string
     */
    private function rtfFooter()
    {
        $pString = '';
        $pString .= '\par '; // Close last paragraph
        $pString .= '}'; // Close document

        return $pString;
    }
    /*
     * RTF Stylesheet table
     *
     * @return string
     */
    private function rtfstylesheetTable()
    {
        $pString = '{\stylesheet' . LF;
        $pString .= '{\*\cs1 Hyperlink;}' . LF;
        $pString .= '{\*\cs1 Bullet Symbols;}' . LF;
        $pString .= '{\*\cs1 Numbering Symbols;}' . LF;
        $pString .= '{\*\cs16 \additive \sbasedon10 endnote reference;}' . LF;
        $pString .= '}';

        return $pString;
    }
    /*
     * RTF Color table
     *
     * @return string
     */
    private function rtfcolorTable()
    {
        $colourTable = '{\colortbl;';

        foreach ($this->rtf->colourArray as $colour)
        {
            $colourTable .= $colour . ';';
        }

        $colourTable .= '}' . LF . LF;

        return $colourTable;
    }
    /*
     * RTF Paragraph block
     *
     * @param string $type
     *
     * @return string
     */
    private function rtfParagraphBlock($type)
    {
        $pString = '{';
        $pString .= $this->rtf->fontBlocks[$type]['fontBlock'] . $this->rtf->fontBlocks[$type]['fontSize'];
        if (array_key_exists('textFormat', $this->rtf->fontBlocks[$type]))
        {
            $DefEmphase = [
                'Italics' => '\i',
                'Underline' => '\ul',
                'Bold' => '\b',
            ];
            $pString .= $DefEmphase[$this->rtf->fontBlocks[$type]['textFormat']];
        }
        $pString .= ' ';

        return $pString;
    }
    /*
     * RTF header fontBlockPlain
     *
     * @return string
     */
    private function makeFontTable()
    {
        $pString = '{\fonttbl' . LF;

        foreach ($this->rtf->fonttbl as $index => $font)
        {
            $pString .= '{\f' . $index . '\fcharset0 ' . $font . ';}' . LF;
        }

        $pString .= '}' . LF . LF;

        return $pString;
    }
    /*
     * Encode paragraphs from HTML
     *
     * @param string $string
     *
     * @return string
     */
    private function paragraph($string)
    {
        return preg_replace("/<\\/ br>/ui", "__WIKINDX__NEWLINEPAR__", $string);
    }
    /*
     * Make a RTF paragraph
     *
     * @param string $type
     *
     * @return string
     */
    private function makeParagraph($type)
    {
        return '{' . $this->rtf->fontBlocks[$type]['fontBlock'] . $this->rtf->fontBlocks[$type]['fontSize'] . '\par }';
    }
    /*
     * Make RTF block (justification, indents)
     *
     * @param string $type
     *
     * @return string
     */
    private function makeBlock($type)
    {
        if ($type == 'divider')
        {
            $pString = '\qc'; // Center
        }
        else
        {
            $pString = '\qj'; // Justify
        }

        $pString .= $this->rtf->fontBlocks[$type]['indentL']; // Left indentation
        $pString .= $this->rtf->fontBlocks[$type]['indentR']; // Right indentation

        return $pString . LF;
    }
    /*
     * make RTF fontBlocks according to input
     */
    private function prepareFontBlocks()
    {
        $this->rtfFontBlock(
            "divider",
            $this->fonts[10],
            $this->fontSizes[3],
            FALSE,
            FALSE,
            0
        );
    }
    /*
     * RTF header fontBlocks
     *
     * @param string $type
     * @param string $font
     * @param int $fontSize
     * @param int $indentL
     * @param int $tindentRype
     * @param string $crFollowing
     * @bool string $crBetween
     * @param string $textFormat
     *
     * @return string
     */
    private function rtfFontBlock($type, $font, $fontSize, $indentL, $indentR, $crFollowing, $crBetween = FALSE, $textFormat = 'Normal')
    {
        $fontIndex = $this->rtf->setFontBlock($font);

        $this->rtf->fontBlocks[$type]['fontBlock'] = '\f' . $fontIndex;
        // Actual font size seems to be half this value and rounded up to an even number
        $this->rtf->fontBlocks[$type]['fontSize'] = '\fs' . $fontSize * 2;

        // Each TAB approximately = 720 (no idea what the units are)
        $indentL = $indentL ? $indentL : 0;
        $indentR = $indentR ? $indentR : 0;

        $this->rtf->fontBlocks[$type]['indentL'] = '\li' . ($indentL * 720);
        $this->rtf->fontBlocks[$type]['indentR'] = '\ri' . ($indentR * 720);

        $this->rtf->fontBlocks[$type]['crFollowing'] = $crFollowing;
        if ($crBetween)
        {
            $this->rtf->fontBlocks[$type]['crBetween'] = $crBetween;
        }
        if ($textFormat != 'Normal')
        {
            $this->rtf->fontBlocks[$type]['textFormat'] = $textFormat;
        }
    }
    /*
     * format text with bold, italics, underline, convert newlines etc
     *
     * @param string $input
     * @param bool $protectCurlyBracket
     *
     * @return string
     */
    private function textFormat($input, $protectCurlyBracket = TRUE)
    {
        $input = $this->rtf->formatText($input, $protectCurlyBracket);
        $input = $this->rtf->parseSpan($input, [$this->rtf, "styleCallback"]);
        $input = $this->rtf->parseLists($input, [$this->rtf, "callbackUnorderedList"], [$this->rtf, "callbackOrderedList"]);
        // Handle images
        $input = preg_replace_callback("/<img.*[>]+/Uusi", [$this->rtf, "imageCallback"], $input);
        $input = $this->rtf->createFancyUrl($input);
        // convert citations (FALSE for no hyperlink)
        $input = $this->cite->parseCitations($input, 'rtf', FALSE, TRUE);
        // Replace temporary newlines
        $input = str_replace("__WIKINDX__NEWLINEPAR__", "\\par\n", $input);
        $input = str_replace("__WIKINDX__NEWLINE__", "\n", $input);

        return $input;
    }
    /*
     * failure
     *
     * @param string $error
     */
    private function failure($error)
    {
        GLOBALS::addTplVar('content', $error);
        FACTORY_CLOSE::getInstance();
    }
}
