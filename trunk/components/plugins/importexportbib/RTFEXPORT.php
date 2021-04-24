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
 *	RTF export class
 */
class RTFEXPORT
{
    private $db;
    private $vars;
    private $parentClass;
    private $errors;
    private $session;
    private $coremessages;
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
    private $input;
    private $common;
    private $rtf;
    private $user;
    private $bodyTempFile;
    private $formattedText = [];

    /**
     * Constructor
     *
     * @param mixed $parentClass
     */
    public function __construct($parentClass)
    {
        $this->parentClass = $parentClass;
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->cite = FACTORY_CITE::getInstance('rtf');
        $this->bibStyle = FACTORY_BIBSTYLE::getInstance('rtf');
        $this->styles = LOADSTYLE\loadDir();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "EXPORTCOMMON.php"]));
        $this->common = new EXPORTCOMMON();
        $this->rtf = FACTORY_RICHTEXTFORMAT::getInstance();
        $this->user = FACTORY_USER::getInstance();
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
     * @param resource $fd
     */
    public function closeTempFile($fd)
    {
        return fclose($fd);
    }

    /*
     * write $this->pString to file
     *
     * If file exists, it is written over.
     */
    public function process()
    {
        $this->input = $this->checkInput();
        if (!is_array($this->input))
        {
            $this->failure(HTML\p($this->coremessages->text("importexport", "noList"), 'error'));
        }
        //$this->rtf->fontBlocks = array();
        $sql = $this->common->getSQL();

        if (!$sql)
        {
            $this->failure(HTML\p($this->coremessages->text("importexport", "noList"), 'error'));
        }

        // Prepare fixed fonts for resource's sections
        $this->prepareFontBlocks();

        // The body will be written in memory by a PHP stream
        $this->bodyTempFile = $this->openTempFile();

        $sqlArray = unserialize(base64_decode($sql));
        foreach ($sqlArray as $sql)
        {
            $recordset = $this->db->query($sql);
            $this->getData($recordset);
        }

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

        $pString = HTML\p($this->coremessages->text("importexport", 'exported') . ': ' . $this->common->fileName, 'success');
        $this->common->writeFilenameToSession($this->common->fileName);
        $this->parentClass->listFiles($pString, 'initRtfExport');
    }
    /*
     * get data from database
     *
     * @param object $recordset
     */
    private function getData($recordset)
    {
        if (array_key_exists('link', $this->input))
        {
            global $_SERVER;
            $link = WIKINDX_URL_BASE . $_SERVER['SCRIPT_NAME'] . "?action=resource_RESOURCEVIEW_CORE&id=";
            $this->session->setVar("exportRtf_link", TRUE);
            $wikindxTitle = stripslashes(WIKINDX_TITLE);
        }
        else
        {
            $link = FALSE;
            $this->session->delVar("exportRtf_link");
        }
        $resourceIds = $metadataIds = [];
        $mainArray = $refArray = $abstractArray = $notesArray = [];
        $somethingToPrint = FALSE;
        while ($row = $this->db->fetchRow($recordset))
        {
            if (array_search($row['resourceId'], $resourceIds) === FALSE)
            {
                $resourceIds[] = $row['resourceId'];
                $mainArray[$row['resourceId']] = ''; // needed as placeholder to add other items too if reference itself is not being exported
            }
            else
            {
                continue;
            }
            $returnAfterBib = FALSE;
            $refArray[$row['resourceId']] = '';
            if (array_key_exists('metadataFullCite', $this->input))
            {
                $refArray[$row['resourceId']] .= $this->getFullCite($row['resourceId'], $row['resourceType']);
            }
            if (array_key_exists('bibliography', $this->input))
            {
                $refArray[$row['resourceId']] .= $this->makeBlock('bibliography');
                $refArray[$row['resourceId']] .= $this->rtfParagraphBlock('bibliography');
                $ref = $this->textFormat($this->bibStyle->process($row), FALSE);
                $refArray[$row['resourceId']] .= $this->paragraph($ref);
                if (array_key_exists('bibliographyIsbn', $this->input) && ($row['resourceIsbn']))
                {
                    $refArray[$row['resourceId']] .= '  [' . $row['resourceIsbn'] . ']';
                }
                if ($link)
                {
                    $hyperlink = $link . $row['resourceId'];
                    $refArray[$row['resourceId']] .= '{\field{\fldinst {HYPERLINK "' . $hyperlink . '"}}{\fldrslt {\cs1\ul\cf2 [' . $wikindxTitle . ']}}}';
                }
                $refArray[$row['resourceId']] .= '}' . LF;
                for ($i = 0; $i < $this->rtf->fontBlocks['bibliography']['crFollowing']; $i++)
                {
                    $refArray[$row['resourceId']] .= $this->makeParagraph('bibliography') . LF;
                }
                $returnAfterBib = TRUE;
                $somethingToPrint = TRUE;
            }
            if (array_key_exists('abstract', $this->input) && $row['resourcetextAbstract'])
            {
                $abstractArray[$row['resourceId']] = $this->makeBlock('abstract');
                if ($returnAfterBib)
                {
                    $abstractArray[$row['resourceId']] .= $this->makeParagraph('bibliography') . LF;
                    $returnAfterBib = FALSE;
                }
                if (trim($this->input['abstractTag']))
                {
                    $abstractArray[$row['resourceId']] .= $this->rtfParagraphBlock('abstract');
                    $abstractArray[$row['resourceId']] .= trim($this->input['abstractTag']) . '\par }' . LF;
                }
                $abstractArray[$row['resourceId']] .= $this->rtfParagraphBlock('abstract');
                $abstractArray[$row['resourceId']] .= $this->textFormat(stripslashes($row['resourcetextAbstract']), 'abstract') . '}';
                for ($i = 0; $i < $this->rtf->fontBlocks['abstract']['crFollowing']; $i++)
                {
                    $abstractArray[$row['resourceId']] .= $this->makeParagraph('abstract') . LF;
                }
                $somethingToPrint = TRUE;
            }
            if (array_key_exists('notes', $this->input) && $row['resourcetextNote'])
            {
                $notesArray[$row['resourceId']] = $this->makeBlock('notes');
                if ($returnAfterBib)
                {
                    $notesArray[$row['resourceId']] .= $this->makeParagraph('bibliography') . LF;
                    $returnAfterBib = FALSE;
                }
                if (trim($this->input['notesTag']))
                {
                    $notesArray[$row['resourceId']] .= $this->rtfParagraphBlock('notes');
                    $notesArray[$row['resourceId']] .= trim($this->input['notesTag']) . '\par }' . LF;
                }
                $notesArray[$row['resourceId']] .= $this->rtfParagraphBlock('notes');
                $notesArray[$row['resourceId']] .= $this->textFormat(stripslashes($row['resourcetextNote']), 'notes') . '}';
                for ($i = 0; $i < $this->rtf->fontBlocks['notes']['crFollowing']; $i++)
                {
                    $notesArray[$row['resourceId']] .= $this->makeParagraph('notes') . LF;
                }
                $somethingToPrint = TRUE;
            }
        }
        // resource keywords -- only printed if reference itself is printed
        $keywordArray = [];
        if (array_key_exists('bibliography', $this->input) && array_key_exists('bibliographyKeywords', $this->input))
        {
            $this->db->formatConditionsOneField($resourceIds, 'resourcekeywordResourceId');
            $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
            $this->db->orderBy('keywordKeyword');
            $resultset = $this->db->select('resource_keyword', ['keywordKeyword', 'resourcekeywordResourceId']);
            while ($row = $this->db->fetchRow($resultset))
            {
                $keywordArray[$row['resourcekeywordResourceId']][] = stripslashes($row['keywordKeyword']);
            }
            $somethingToPrint = TRUE;
        }
        // Assemble main components before adding any metadata
        foreach ($mainArray as $id => $null)
        {
            $keywords = '';
            if (array_key_exists($id, $keywordArray))
            {
                $keywords = $this->makeParagraph('bibliography') . LF;
                $keywords .= $this->makeBlock('bibliography');
                $keywords .= $this->rtfParagraphBlock('bibliography');
                $keywords .= $this->paragraph('Keywords:    ' . implode('; ', $keywordArray[$id]));
                $keywords .= '}' . LF;
                $keywords .= $this->makeParagraph('bibliography') . LF;
            }
            $ref = array_key_exists($id, $refArray) ? $refArray[$id] : '';
            $abstract = array_key_exists($id, $abstractArray) ? $abstractArray[$id] : '';
            $notes = array_key_exists($id, $notesArray) ? $notesArray[$id] : '';
            $mainArray[$id] = $ref . $keywords . $abstract . $notes;
        }
        unset($keywordArray);
        unset($abstractArray);
        unset($notesArray);
        // metadata
        if (array_key_exists('musings', $this->input) ||
            array_key_exists('quotes', $this->input) ||
            array_key_exists('paraphrases', $this->input))
        {
            if (array_key_exists('musings', $this->input))
            {
                $metaArray[] = 'm';
                $this->setViewConditions();
            }
            if (array_key_exists('quotes', $this->input))
            {
                $metaArray[] = 'q';
            }
            if (array_key_exists('paraphrases', $this->input))
            {
                $metaArray[] = 'p';
            }
            $this->db->formatConditionsOneField($resourceIds, 'resourcemetadataResourceId');
            $this->db->formatConditionsOneField($metaArray, 'resourcemetadataType');
            $this->db->orderBy($this->db->tidyInputClause('resourcemetadataPageStart') . '+0', FALSE);
            $recordset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataText', 'resourcemetadataResourceId',
                'resourcemetadataPageStart', 'resourcemetadataPageEnd', 'resourcemetadataParagraph', 'resourcemetadataSection',
                'resourcemetadataChapter', 'resourcemetadataPrivate', 'resourcemetadataAddUserId', 'resourcemetadataType', ]);
            $numElements = 0;
            $musings = $quotes = $paraphrases = $quotesMetaIds = $paraphrasesMetaIds = [];
            while ($row = $this->db->fetchRow($recordset))
            {
                if (array_search($row['resourcemetadataId'], $metadataIds) === FALSE)
                {
                    $metadataIds[] = $row['resourcemetadataId'];
                }
                else
                {
                    continue;
                }
                $metaString = '';
                if ($row['resourcemetadataType'] == 'm')
                {
                    $metaType = 'musings';
                }
                elseif ($row['resourcemetadataType'] == 'q')
                {
                    $metaType = 'quotes';
                }
                elseif ($row['resourcemetadataType'] == 'p')
                {
                    $metaType = 'paraphrases';
                }
                $cite = $this->getCiteDetails($row);
                if ($numElements)
                {
                    for ($i = 0; $i < $this->rtf->fontBlocks[$metaType]['crBetween']; $i++)
                    {
                        $metaString .= $this->makeParagraph($metaType) . LF;
                    }
                }
                $metaString .= $this->rtfParagraphBlock($metaType);
                $metaString .= $cite . $this->textFormat(stripslashes($row['resourcemetadataText'])) . '\par }';
                if ($row['resourcemetadataType'] == 'm')
                {
                    $musings[$row['resourcemetadataResourceId']][] = $metaString;
                }
                elseif ($row['resourcemetadataType'] == 'q')
                {
                    $quotes[$row['resourcemetadataResourceId']][$row['resourcemetadataId']] = $metaString;
                    // matches metadataId to resourceId -- used to match metadata comments
                    $quotesMetaIds[$row['resourcemetadataId']] = $row['resourcemetadataId'];
                }
                elseif ($row['resourcemetadataType'] == 'p')
                {
                    $paraphrases[$row['resourcemetadataResourceId']][$row['resourcemetadataId']] = $metaString;
                    // matches metadataId to resourceId -- used to match metadata comments
                    $paraphrasesMetaIds[$row['resourcemetadataId']] = $row['resourcemetadataId'];
                }
                $numElements++;
            }
            // metadata comments
            if ((array_key_exists('quotes', $this->input) || array_key_exists('paraphrases', $this->input)) &&
                (array_key_exists('paraphrasesComments', $this->input) || array_key_exists('paraphrasesComments', $this->input)))
            {
                $commentsArray = [];
                $this->setViewConditions();
                if (!empty($metadataIds))
                {
                    $this->db->formatConditionsOneField($metadataIds, 'resourcemetadataMetadataId');
                }
                $commentsArray = $this->getComments();
            }
            if (!empty($quotes))
            {
                $metaString = $this->makeBlock('quotes');
                if ($returnAfterBib)
                {
                    $metaString .= $this->makeParagraph('bibliography') . LF;
                    $returnAfterBib = FALSE;
                }
                if (trim($this->input['quotesTag']))
                {
                    $metaString .= $this->rtfParagraphBlock('quotes');
                    $metaString .= trim($this->input['quotesTag']) . '\par }' . LF;
                }
                $quotesCopy = $quotes;
                foreach ($quotesCopy as $resourceId => $array)
                {
                    foreach ($array as $metaId => $rawText)
                    {
                        if (array_key_exists($quotesMetaIds[$metaId], $commentsArray))
                        {
                            $quotes[$resourceId][$metaId] .= implode('', $commentsArray[$metaId]);
                        }
                    }
                }
                foreach ($quotes as $id => $text)
                {
                    $mainArray[$id] .= $metaString . implode('', $text);
                    for ($i = 0; $i < $this->rtf->fontBlocks['quotes']['crFollowing']; $i++)
                    {
                        $mainArray[$id] .= $this->makeParagraph('quotes') . LF;
                    }
                }
                $somethingToPrint = TRUE;
            }
            if (!empty($paraphrases))
            {
                $metaString = $this->makeBlock('paraphrases');
                if ($returnAfterBib)
                {
                    $metaString .= $this->makeParagraph('bibliography') . LF;
                    $returnAfterBib = FALSE;
                }
                if (trim($this->input['paraphrasesTag']))
                {
                    $metaString .= $this->rtfParagraphBlock('paraphrases');
                    $metaString .= trim($this->input['paraphrasesTag']) . '\par }' . LF;
                }
                $paraphrasesCopy = $paraphrases;
                foreach ($paraphrasesCopy as $resourceId => $array)
                {
                    foreach ($array as $metaId => $rawText)
                    {
                        if (array_key_exists($paraphrasesMetaIds[$metaId], $commentsArray))
                        {
                            $paraphrases[$resourceId][$metaId] .= implode('', $commentsArray[$metaId]);
                        }
                    }
                }
                foreach ($paraphrases as $id => $text)
                {
                    $mainArray[$id] .= $metaString . implode('', $text);
                    for ($i = 0; $i < $this->rtf->fontBlocks['paraphrases']['crFollowing']; $i++)
                    {
                        $mainArray[$id] .= $this->makeParagraph('paraphrases') . LF;
                    }
                }
                $somethingToPrint = TRUE;
            }
            if (!empty($musings))
            {
                $metaString = $this->makeBlock('musings');
                if ($returnAfterBib)
                {
                    $metaString .= $this->makeParagraph('bibliography') . LF;
                    $returnAfterBib = FALSE;
                }
                if (trim($this->input['musingsTag']))
                {
                    $metaString .= $this->rtfParagraphBlock('musings');
                    $metaString .= trim($this->input['musingsTag']) . '\par }' . LF;
                }
                foreach ($musings as $id => $text)
                {
                    $mainArray[$id] .= $metaString . implode('', $text);
                    for ($i = 0; $i < $this->rtf->fontBlocks['musings']['crFollowing']; $i++)
                    {
                        $mainArray[$id] .= $this->makeParagraph('musings') . LF;
                    }
                }
                $somethingToPrint = TRUE;
            }
        }
        if ($somethingToPrint)
        {
            foreach ($mainArray as $text)
            {
                $fullText = $this->rtf->utf8_2_rtfansicpg1252($this->removeSlashes($text));
                // Do divider
                if (array_key_exists('divider', $this->input) && trim($this->input['divider']))
                {
                    $fullText .= $this->makeBlock('divider');
                    $fullText .= $this->rtfParagraphBlock('divider');
                    $fullText .= trim($this->input['divider']) . '\par }' . LF;
                }
                for ($i = 0; $i < $this->dividerCR[$this->input['dividerCR']]; $i++)
                {
                    $fullText .= $this->makeParagraph('divider') . LF;
                }
                // Cut the string in smaller pieces to isolate hexfile name from other content
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
     * Set SQL conditions for viewing private public etc. musings and comments
     */
    private function setViewConditions()
    {
        $userId = $this->session->getVar("setup_UserId");
        if ($this->input['metadata'])
        { // export only this users quote/paraphrase comments and musings
            $this->db->formatConditions(['resourcemetadataAddUserId' => $userId]);
        }
        else
        {
            $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
            $this->db->formatConditions($this->db->formatFields('usergroupsusersGroupId') . $this->db->equal .
                $this->db->formatFields('resourcemetadataPrivate'));
            $subSql = $this->db->selectNoExecute('user_groups_users', 'usergroupsusersId', FALSE, TRUE, TRUE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('N')
                . $this->db->and .
                $this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('Y');
            $case1 = $this->db->caseWhen($subject, FALSE, $subSql, FALSE, FALSE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('Y');
            $result = $this->db->formatFields('resourcemetadataAddUserId') . $this->db->equal . $this->db->tidyInput($userId);
            $case2 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('N');
            $result = $this->db->tidyInput(1);
            $case3 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $this->db->formatConditions($case1 . $this->db->or . $case2 . $this->db->or . $case3);
        }
    }
    /**
     * Get citation details
     *
     * @param mixed $rowT
     *
     * @return string
     */
    private function getCiteDetails($rowT)
    {
        $citeArray = [];
        if (array_key_exists('metadataFullCite', $this->input) && $this->fullCite)
        {
            $citeArray[] = $this->fullCite;
        }
        $page_start = $rowT['resourcemetadataPageStart'] ? "p." . $rowT['resourcemetadataPageStart'] : FALSE;
        $page_end = $rowT['resourcemetadataPageEnd'] ? "-" . $rowT['resourcemetadataPageEnd'] : FALSE;
        if ($page_start && $page_end)
        {
            $page_start = 'p' . $page_start;
        }
        if ($page_start)
        {
            $citeArray[] = "$page_start$page_end";
        }
        $paragraph = $rowT['resourcemetadataParagraph'] ?
                $this->coremessages->text("resources", "paragraph") . ' ' . $rowT['resourcemetadataParagraph'] : FALSE;
        if ($paragraph)
        {
            $citeArray[] = "$paragraph";
        }
        $chapter = $rowT['resourcemetadataChapter'] ?
                $this->coremessages->text("resources", "chapter") . ' ' . stripslashes($rowT['resourcemetadataChapter']) : FALSE;
        if ($chapter)
        {
            $citeArray[] = "$chapter";
        }
        $section = $rowT['resourcemetadataSection'] ?
                $this->coremessages->text("resources", "section") . ' ' . stripslashes($rowT['resourcemetadataSection']) : FALSE;
        if ($section)
        {
            $citeArray[] = "$section";
        }
        if (empty($citeArray))
        {
            $cite = '-->  '; // resetting
        }
        else
        {
            $cite = '-->  ' . implode(', ', $citeArray) . ':  ';
        }

        return $cite;
    }
    /**
     * Get metadata comments
     *
     * @return array
     */
    private function getComments()
    {
        // check for comments
        $comments = [];
        $this->db->orderBy('resourcemetadataTimestamp');
        $recordset = $this->db->select('resource_metadata', ['resourcemetadataText', 'resourcemetadataTimestamp',
            'resourcemetadataAddUserId', 'resourcemetadataPrivate', 'resourcemetadataMetadataId', 'resourcemetadataId', 'resourcemetadataType', ]);
        while ($row = $this->db->fetchRow($recordset))
        {
            $comments[$row['resourcemetadataMetadataId']][] = $this->formatComments(
                $row['resourcemetadataType'],
                $row['resourcemetadataAddUserId'],
                $row['resourcemetadataText']
            );
        }

        return $comments;
    }
    /**
     * Format comments
     *
     * @param mixed $type
     * @param mixed $addUserCommentId
     * @param mixed $commentField
     *
     * @return string
     */
    private function formatComments($type, $addUserCommentId, $commentField)
    {
        $pString = '';
        $tag = $type == 'qc' ? 'quotesCommentsTag' : 'paraphrasesCommentsTag';
        $user = $this->user->displayUserAddEditPlain($addUserCommentId) . LF;
        if (trim($this->input[$tag]))
        {
            if ($type == 'qc')
            {
                $type = 'quotes';
            }
            else
            {
                $type = 'paraphrases';
            }
            $pString .= LF . $this->rtfParagraphBlock($type);
            $pString .= trim($this->input[$tag]) . '}' . LF;
        }
        $pString .= $this->rtfParagraphBlock($type);
        $pString .= '-->  ' . $this->textFormat(stripslashes($commentField)) . '  (' . $user . ')\par }' . LF;

        return $pString;
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
     * make RTF fontBlocks according to input
     */
    private function prepareFontBlocks()
    {
        //		$sqlSelectReplace = "SELECT ";
        //		$sqlJoinReplace = "FROM " . $this->db->formatTable('resource');

        if (array_key_exists("divider", $this->input))
        {
            $this->rtfFontBlock(
                "divider",
                $this->fonts[$this->input['dividerFont']],
                $this->fontSizes[$this->input['dividerFontSize']],
                FALSE,
                FALSE,
                0
            );
        }
        //		if (array_key_exists("bibliography", $this->input)) // This type is used for other purposes too so always prepare it.
        //		{
        $this->rtfFontBlock(
            "bibliography",
            $this->fonts[$this->input['bibliographyFont']],
            $this->fontSizes[$this->input['bibliographyFontSize']],
            $this->indentTabs[$this->input['bibliographyIndentL']],
            $this->indentTabs[$this->input['bibliographyIndentR']],
            $this->cr[$this->input['bibliographyCrFollowing']],
            FALSE,
            $this->ubiBib[$this->input['bibliographyTextFormat']]
        );
        //		}
        if (array_key_exists("abstract", $this->input))
        {
            $this->rtfFontBlock(
                "abstract",
                $this->fonts[$this->input['abstractFont']],
                $this->fontSizes[$this->input['abstractFontSize']],
                $this->indentTabs[$this->input['abstractIndentL']],
                $this->indentTabs[$this->input['abstractIndentR']],
                $this->cr[$this->input['abstractCrFollowing']],
                FALSE,
                $this->ubi[$this->input['abstractTextFormat']]
            );
        }
        if (array_key_exists("notes", $this->input))
        {
            $this->rtfFontBlock(
                "notes",
                $this->fonts[$this->input['notesFont']],
                $this->fontSizes[$this->input['notesFontSize']],
                $this->indentTabs[$this->input['notesIndentL']],
                $this->indentTabs[$this->input['notesIndentR']],
                $this->cr[$this->input['notesCrFollowing']],
                FALSE,
                $this->ubi[$this->input['notesTextFormat']]
            );
        }
        if (array_key_exists("quotes", $this->input))
        {
            $this->rtfFontBlock(
                "quotes",
                $this->fonts[$this->input['quotesFont']],
                $this->fontSizes[$this->input['quotesFontSize']],
                $this->indentTabs[$this->input['quotesIndentL']],
                $this->indentTabs[$this->input['quotesIndentR']],
                $this->cr[$this->input['quotesCrFollowing']],
                $this->cr[$this->input['quotesCrBetween']],
                $this->ubi[$this->input['quotesTextFormat']]
            );
        }
        if (array_key_exists("paraphrases", $this->input))
        {
            $this->rtfFontBlock(
                "paraphrases",
                $this->fonts[$this->input['paraphrasesFont']],
                $this->fontSizes[$this->input['paraphrasesFontSize']],
                $this->indentTabs[$this->input['paraphrasesIndentL']],
                $this->indentTabs[$this->input['paraphrasesIndentR']],
                $this->cr[$this->input['paraphrasesCrFollowing']],
                $this->cr[$this->input['paraphrasesCrBetween']],
                $this->ubi[$this->input['paraphrasesTextFormat']]
            );
        }
        if (array_key_exists("musings", $this->input))
        {
            $this->rtfFontBlock(
                "musings",
                $this->fonts[$this->input['musingsFont']],
                $this->fontSizes[$this->input['musingsFontSize']],
                $this->indentTabs[$this->input['musingsIndentL']],
                $this->indentTabs[$this->input['musingsIndentR']],
                $this->cr[$this->input['musingsCrFollowing']],
                $this->cr[$this->input['musingsCrBetween']],
                $this->ubi[$this->input['musingsTextFormat']]
            );
        }
    }
    /*
     * RTF header fontBlocks
     *
     * @param string $type
     * @param string $font
     * @param int $fontSize
     * @param int $indentL
     * @param int $indentR
     * @param string $crFollowing
     * @param bool $crBetween
     * @param string $textFormat
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
     * encode paragraphs from HTML
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
     * make RTF block (justification, indents)
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
     * getFullCite
     *
     * If requested, get primary creator surname and publication year for this resource in order to add to the metadata.
     */
    private function getFullCite($resourceId, $resourceType)
    {
        $this->fullCite = '';
        $this->db->formatConditions(['resourcecreatorOrder' => 1]);
        $this->db->formatConditions(['resourcecreatorResourceId' => $resourceId]);
        $this->db->leftJoin('resource_year', 'resourceYearId', 'resourcecreatorResourceId');
        $recordset = $this->db->select(
            'resource_creator',
            ['resourcecreatorCreatorSurname', 'resourceyearYear1', 'resourceyearYear2']
        );
        while ($row = $this->db->fetchRow($recordset))
        {
            $creator = $row['resourcecreatorCreatorSurname'];
            if ($row['resourceyearYear2'] && (($resourceType == 'book') || ($resourceType == 'book_article')))
            {
                $year = $creator ? ' ' . $row['resourceyearYear2'] : $row['resourceyearYear2'];
            }
            elseif ($row['resourceyearYear1'])
            {
                $year = $creator ? ' ' . $row['resourceyearYear1'] : $row['resourceyearYear1'];
            }
            else
            {
                $year = '';
            }
            $this->fullCite = $creator . $year;
        }
    }
    /*
     * validate user input
     *
     * @return mixed
     */
    private function checkInput()
    {
        $this->session->clearArray("exportRtf");
        $this->writeSession();
        // At least one must be on
        if (!array_key_exists("exportRtf_bibliography", $this->vars) &&
            !array_key_exists("exportRtf_abstract", $this->vars) &&
            !array_key_exists("exportRtf_notes", $this->vars) &&
            !array_key_exists("exportRtf_quotes", $this->vars) &&
            !array_key_exists("exportRtf_paraphrases", $this->vars) &&
            !array_key_exists("exportRtf_musings", $this->vars))
        {
            $this->parentClass->initRtfExport($this->errors->text("inputError", "missing"));

            return;
        }

        return $this->session->getArray("exportRtf");
    }
    /*
     * write input to session
     */
    private function writeSession()
    {
        foreach ($this->vars as $key => $value)
        {
            if (preg_match("/^exportRtf_/u", $key))
            {
                $temp[$key] = $value;
            }
        }
        if (isset($temp))
        {
            $temp['exportRtf_done'] = TRUE;
            $this->session->writeArray($temp);
        }
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
