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
 * Convert DOC, DOCX and PDF files to plain text ready for searching.
 *
 *	Code adapted from:
 *		PHP DOC DOCX PDF to Text by Aditya Sarkar at www.phpclasses.org/package/8908-PHP-Convert-DOCX-DOC-PDF-to-plain-text.html
 *		and
 *		https://coderwall.com/p/x_n4tq/how-to-read-doc-using-php
 */
class FILETOTEXT
{
    public function __construct()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR, "pdftotext", "PdfToText.phpclass"]));
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR, "rtftools", "RtfTexter.phpclass"]));
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "RecursiveDOMIterator.php"]));
    }
    
    /**
     * Count the number of [missing] attachments cached
     *
     * @return array [nbMissing, nbTotal]
     */
    public function countMissingCacheAttachment()
    {
        $sql = "
            SELECT COUNT(*) AS nbFilesTotal, COUNT(*) - COUNT(resourceattachmentsText) AS nbFilesMissing
            FROM resource_attachments;
        ";
        
        $db = FACTORY_DB::getInstance();
        $resultSet = $db->query($sql);       
        if (count($resultSet) > 0)
        {
            $row = $db->fetchRow($resultSet);
            return array($row["nbFilesMissing"], $row["nbFilesTotal"]);
        }
        else
        {
            return array(0, 0);
        }
    }

    /**
     * Extract every missing cached text of attachments
     */
    public function checkCache()
    {
        $db = FACTORY_DB::getInstance();
        $vars = GLOBALS::getVars();
        $session = FACTORY_SESSION::getInstance();
        $curl_version_infos = curl_version();
        $curl_ms_timeout_available = version_compare($curl_version_infos["version"], '7.16.2', '>=');
            
        // Don't launch a cache action when we are executing one 
        $action = $vars['action'] ?? "";
        $method = $vars['method'] ?? "";
        if ($action == "attachments_ATTACHMENTS_CORE" && $method == "curlRefreshCache")
        {
            return;
        }
        
        // 20 * 100 ms or 2 * 1 s implies a penality of 2 seconds
        $db->limit($curl_ms_timeout_available ? 20 : 2, 0);
        $db->formatConditions(["resourceattachmentsText" => 'IS NULL']);
        $resultSet = $db->select('resource_attachments', ['resourceattachmentsHashFilename']);
        while ($row = $db->fetchRow($resultSet))
        {
            $curlTarget = WIKINDX_URL_BASE . '/index.php' .
            '?action=attachments_ATTACHMENTS_CORE' .
            '&method=curlRefreshCache' .
            '&filename=' . urlencode($row['resourceattachmentsHashFilename']);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $curlTarget);
            curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            if ($curl_ms_timeout_available)
            {
                // LkpPo(HACK): https://www.php.net/manual/fr/function.curl-setopt.php#104597
                // For OSes that can't handle a timeout below 1 s, we disable signals to achieve queries in quasi async mode
                curl_setopt($ch, CURLOPT_NOSIGNAL, TRUE);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 100); // 100 ms
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); // 100 ms
            }
            else
            {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); // 1 ms
                curl_setopt($ch, CURLOPT_TIMEOUT, 1); // 1 ms
            }
            curl_exec($ch);
        }
    }

    /**
     * convertToText
     *
     * @param string $filename
     * @param string $mimetype Default is "text/plain"
     *
     * @return string
     */
    public function convertToText($filename, $mimetype = "text/plain")
    {
        $extension = \FILE\getExtension($filename);
        
        // Convert to text with a specific function by mimetype and return it
        switch ($mimetype)
        {
            case WIKINDX_MIMETYPE_DOC:
                $text = $this->readWord($filename);
            break;
            case WIKINDX_MIMETYPE_DOCM:
            case WIKINDX_MIMETYPE_DOCX:
            case WIKINDX_MIMETYPE_DOTM:
            case WIKINDX_MIMETYPE_DOTX:
                $text = $this->readDocx($filename);
            break;
            case WIKINDX_MIMETYPE_EPUB:
                $text = $this->readEpub($filename);
            break;
            case WIKINDX_MIMETYPE_FB:
                $text = $this->readFictionBook($filename);
            break;
            case WIKINDX_MIMETYPE_HTML:
            case WIKINDX_MIMETYPE_XHTML:
                $text = $this->readHtml($filename);
            break;
            case WIKINDX_MIMETYPE_MHT_ALT:
            case WIKINDX_MIMETYPE_MHT_APP:
            case WIKINDX_MIMETYPE_MHT_MIX:
            case WIKINDX_MIMETYPE_MHT_MUL:
            case WIKINDX_MIMETYPE_MHT_RFC:
                $text = $this->readMht($filename);
            break;
            case WIKINDX_MIMETYPE_ODP:
            case WIKINDX_MIMETYPE_ODT:
            case WIKINDX_MIMETYPE_OTP:
            case WIKINDX_MIMETYPE_OTT:
            case WIKINDX_MIMETYPE_STI:
            case WIKINDX_MIMETYPE_SXI:
            case WIKINDX_MIMETYPE_SXW:
            case WIKINDX_MIMETYPE_STW:
                $text = $this->readOpenDocument($filename);
            break;
            case WIKINDX_MIMETYPE_POTM:
            case WIKINDX_MIMETYPE_POTX:
            case WIKINDX_MIMETYPE_PPTM:
            case WIKINDX_MIMETYPE_PPTX:
                $text = $this->readPptx($filename);
            break;
            case WIKINDX_MIMETYPE_PDF:
            case WIKINDX_MIMETYPE_XPDF:
                $text = $this->readPdf($filename);
            break;
            case WIKINDX_MIMETYPE_RTF_APP:
            case WIKINDX_MIMETYPE_RTF_TEXT:
                $text = $this->readRtf($filename);
            break;
            case WIKINDX_MIMETYPE_SCRIBUS:
                $text = $this->readScribus($filename);
            break;
            case WIKINDX_MIMETYPE_TXT:
                switch ($extension)
                {
                    // SYLK is a spreadsheet file format
                    case "slk":
                        // Type not handled
                        $text = "";
                    break;
                    default:
                        $text = $this->readText($filename);
                    break;
                }
            break;
            case WIKINDX_MIMETYPE_XML_APP:
            case WIKINDX_MIMETYPE_XML_TEXT:
                $text = $this->readHtml($filename);
            break;
            case WIKINDX_MIMETYPE_XPS:
                $text = $this->readXps($filename);
            break;
            default:
                // Type not handled
                $text = "";
            break;
        }
        
        // If the content returned is not a string, drop it and return an empty text
        if (!is_string($text))
        {
            $text = "";
        }
        
        // Clean up the text a bit to improve the search for exact strings
        
        // Replace by a single space:
        // - Control and format characters (C)
        // - Separator characters (Z)
        $text = preg_replace("/\p{C}|\p{Z}/u", " ", $text);
        // Replace series of spaces with a single space
        $text = preg_replace("/ {2,}/u", " ", $text);
        $text = trim($text);
        
        return $text;
    }
    /**
     * readPdf
     *
     * @param mixed $filename
     *
     * @return string
     */
    private function readPdf($filename)
    {
        static $plugin_xpdftotext_exists = NULL;
        
        // Check xpdftotext plugin availability
        if ($plugin_xpdftotext_exists === NULL)
        {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "startup", "LOADPLUGINS.php"]));
            $loadmodules = new \LOADPLUGINS();
            $moduleList = $loadmodules->readPluginsDirectory();
            $plugin_xpdftotext_exists = in_array("xpdftotext", $moduleList);
        }
        
        // Use the best parser available
        if ($plugin_xpdftotext_exists)
        {
            // 1. Use XpdfReader tools
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, "xpdftotext", "XPDFREADER.php"]));

            $metadata = \XPDFREADER\pdfinfo($filename);

            $text = \XPDFREADER\pdftotext(
                $filename, [
                    "clip"    => "", // Get text hidden by clipping area
                    "nodiag"  => "", // Ignore text that is not on a right angle (remove watermarks)
                ]
            );
            $text = $metadata[1] . " " . $text[1];
        }
        else
        {
            // 2. Use Christian Vigh PdfToText class
            // PDF objects can be large – memory is reset at the next script
            $mem = ini_get('memory_limit');
            ini_set('memory_limit', '-1');
            
            $errorDisplay = ini_get('display_errors');
            ini_set('display_errors', FALSE);
            
            $importPDF = new PdfToText();
            
            // Note:
            // MaxGlobalExecutionTime property and PDFOPT_ENFORCE_GLOBAL_EXECUTION_TIME option are broken
            // use only one instance of the class by file parsed
            
            // PDFOPT_NO_HYPHENATED_WORDS: tries to join back hyphenated words into a single word
            // PDFOPT_ENFORCE_EXECUTION_TIME: throw a PdfToTextTimeout exception if the extraction run more than MaxExecutionTime
            $importPDF->Options = PdfToText::PDFOPT_NO_HYPHENATED_WORDS;
            //$importPDF->Options = PdfToText::PDFOPT_NO_HYPHENATED_WORDS | PdfToText::PDFOPT_ENFORCE_EXECUTION_TIME;
            
            // Will consume all available runtime except 2 seconds (if this point is reached in less than 2 seconds)
            //$importPDF->MaxExecutionTime = ini_get('max_execution_time') - GLOBALS::getPageElapsedTime() - 3;
            
            
            try
            {
                // Return the text extracted
                $text = $importPDF->Load($filename);
            } catch (
                // or catch all PdfToText exceptions and return an empty string
                PdfToTextException
                | PdfToTextCaptureException
                | PdfToTextDecodingException
                | PdfToTextDecryptionException
                | PdfToTextFormException
                | PdfToTextTimeoutException $e
            ) {
                $text = "PdfToTextTimeoutException " . $e->getMessage();
            }
            
            ini_set('display_errors', $errorDisplay);
            ini_set('memory_limit', $mem);
        }
        return $text;
    }
    
    /**
     * readText
     *
     * @param mixed $filename
     *
     * @return string
     */
    private function readText($filename)
    {
        $content = file_get_contents($filename);
        if ($content === FALSE)
            return "";
        else
            return $content;
    }
    
    /**
     * readWord
     *
     * @param string $filename
     *
     * @return string
     */
    private function readWord($filename)
    {
        if (($fh = fopen($filename, 'r')) !== FALSE)
        {
            $headers = fread($fh, 0xA00);

            // 1 = (ord(n)*1) ; Document has from 0 to 255 characters
            $n1 = (ord($headers[0x21C]) - 1);

            // 1 = ((ord(n)-8)*256) ; Document has from 256 to 63743 characters
            $n2 = ((ord($headers[0x21D]) - 8) * 256);

            // 1 = ((ord(n)*256)*256) ; Document has from 63744 to 16775423 characters
            $n3 = ((ord($headers[0x21E]) * 256) * 256);

            // 1 = (((ord(n)*256)*256)*256) ; Document has from 16775424 to 4294965504 characters
            $n4 = (((ord($headers[0x21F]) * 256) * 256) * 256);

            // Total length of text in the document
            $textLength = ($n1 + $n2 + $n3 + $n4);
            if ($textLength <= 0)
            {
                return "";
            }
            $extracted_plaintext = fread($fh, $textLength);
            fclose($fh);

            return utf8_encode($extracted_plaintext);
        }
        else
        {
            return "";
        }
    }
    
    /**
     * readDocx, extract the text content of Word 2007-365 files
     *
     * cf. https://www.ecma-international.org/publications/standards/Ecma-376.htm
     *
     * @param string $filename
     *
     * @return string
     */
    private function readDocx($filename)
    {
        $striped_content = "";
        
        foreach (["word/document.xml", "word/comments.xml", "word/endnotes.xml", "word/footnotes.xml"] as $f)
        {
            $content = "";
            
            // Extract the content parts
            $za = new \ZipArchive();
            
            if ($za->open($filename) === TRUE)
            {
                $content = $za->getFromName($f);
                if ($content === FALSE) $content = "";
            }
            
            if ($content != "")
            {
                // Extract the text part of the body and rudimentary formats major blocks with newlines
                // We assume that the document is well formed and that the tags do not intersect
                $pXML = new \XMLReader();
                
                if ($pXML->XML($content))
                {
                    $bExtract = FALSE;
                    $bExtractElement = FALSE;
                    
                    while ($pXML->read())
                    {
                        // Start extracting at the start of the text of each major part
                        if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["w:body", "w:comments", "w:endnotes", "w:footnotes"]))
                        {
                            $bExtract = TRUE;
                        }
                        // Stop extracting at the end of the text of each major part
                        if ($pXML->nodeType == \XMLReader::END_ELEMENT && in_array($pXML->name, ["w:body", "w:comments", "w:endnotes", "w:footnotes"]))
                        {
                            $bExtract = FALSE;
                        }
                        
                        // Start extracting at the start of the text of a paragraph
                        if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["w:p"]))
                        {
                            $bExtractElement = TRUE;
                        }
                        // Stop extracting at the end of the text of a paragraph
                        if ($pXML->nodeType == \XMLReader::END_ELEMENT && in_array($pXML->name, ["w:p"]))
                        {
                            $bExtractElement = FALSE;
                        }
                        
                        // Extract all node and add new lines on blocks
                        if ($bExtract && $bExtractElement)
                        {
                            $striped_content .= $pXML->value;
                            if (in_array($pXML->name, ["w:p"]))
                            {
                                $striped_content .= LF.LF;
                            }
                        }
                    }
                }
                
                $striped_content .= LF.LF;
                
                unset($pXML);
            }
            
            unset($za);
        }
        
        return $striped_content;
    }
    
    /**
     * readPptx, extract the text content of PowerPoint 2007-365 files
     *
     * cf. https://www.ecma-international.org/publications/standards/Ecma-376.htm
     *
     * @param string $filename
     *
     * @return string
     */
    private function readPptx($filename)
    {
        $content = "";
            
        // Extract the content parts
        $za = new \ZipArchive();
        
        if ($za->open($filename) === TRUE)
        {
            // On macOS extractTo() doesn't work, so we emulate it
            for ($k = 0; $k < $za->numFiles; $k++)
            {
                // Get a stream from the original name
                $filename = $za->getNameIndex($k);
                
                // Skip non slide and comment XML files
                if (!((\UTILS\matchPrefix($filename, "ppt/slides/slide") || \UTILS\matchPrefix($filename, "ppt/comments/comment")) && \UTILS\matchSuffix($filename, ".xml")))
                {
                    continue;
                }
                
                $filecontent = $za->getFromName($filename);

                if ($filecontent !== FALSE && $filecontent != "")
                {
                    // Extract the text part of the body and rudimentary formats major blocks with newlines
                    // We assume that the document is well formed and that the tags do not intersect
                    $pXML = new \XMLReader();
                    
                    if ($pXML->XML($filecontent))
                    {
                        $bExtract = FALSE;
                        
                        while ($pXML->read())
                        {
                            // Start extracting at the start of the text of each major part
                            if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["a:t", "p:text"]))
                            {
                                $bExtract = TRUE;
                            }
                            // Stop extracting at the end of the text of each major part
                            if ($pXML->nodeType == \XMLReader::END_ELEMENT && in_array($pXML->name, ["a:t", "p:text"]))
                            {
                                $bExtract = FALSE;
                            }
                            
                            // Extract all node and add new lines on blocks
                            if ($bExtract)
                            {
                                $content .= $pXML->value;
                                if (in_array($pXML->name, ["a:t", "p:text"]))
                                {
                                    $content .= LF.LF;
                                }
                            }
                        }
                    }
                    
                    $content .= LF.LF;
                    
                    unset($pXML);
                }
            }
        }
        
        unset($za);
        
        return $content;
    }
    
    
    /**
     * readOpenDocument, extract the text content of OpenDocument
     *
     * All version of OpenDocument are supported with a single function
     * because the specification has changed very little
     * when we consider only its structure and text extraction. 
     *
     * Versions supported :
     *
     * - Open Document Format for Office Applications (OpenDocument) Specification v1.3
     * - Open Document Format for Office Applications (OpenDocument) Specification v1.2
     * - Open Document Format for Office Applications (OpenDocument) Specification v1.1
     * - Open Document Format for Office Applications (OpenDocument) Specification v1.0
     * - Flat Open Document (Open Document without container)
     *
     * Type supported:
     *
     * - Document
     * - Presentation
     *
     * cf OpenDocument in https://www.oasis-open.org/standards/
     *
     * @param string $filename
     *
     * @return string
     */
    private function readOpenDocument($filename)
    {
        $content = "";
        $content = "";
        
        // Open the container
        $za = new \ZipArchive();
        $errcode = $za->open($filename);
        
        // Like EPUB, ODT are packaged with OCF container,
        // but since ODT also use fixed paths for XML files we open them directly
        if ($errcode === TRUE)
        {
            // Extract mimetype
            $mimetype = $za->getFromName("mimetype");
            
            // Extract metadata
            $filedata = $za->getFromName("meta.xml");
            if ($filedata !== FALSE)
            {
                $pXML = new \XMLReader();
                
                if ($pXML->XML($filedata))
                {
                    while ($pXML->read())
                    {
                        if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["dc:title", "dc:subject", "dc:description", "meta:keyword", "meta:initial-creator", "meta:creation-date", "dc:creator", "dc:date"]))
                        {
                            $content .= $pXML->readInnerXml() . LF;
                        }
                    }
                }
                
                $content .= LF;
                
                unset($pXML);
            }
            
            // Extract content
            $filecontentdata = $za->getFromName("content.xml");
        }
        elseif ($errcode == \ZipArchive::ER_NOZIP)
        {
            $filecontentdata = file_get_contents($filename);
        }
        else
        {
            $filecontentdata = FALSE;
        }
        
        if ($filecontentdata !== FALSE)
        {
            // Load and normalize the content
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadXML($filecontentdata, LIBXML_NOWARNING | LIBXML_NOERROR);
            
            $xsdpath = new DOMXPath($dom);
            
            foreach([
                "draw:image","draw:image-map",
                "draw:object","draw:object-ole",
                "draw:contour-path","draw:contour-polygon","draw:applet","draw:plugin",
                "office:chart"
            ] as $ndtpath)
            {
                $ndlist = $xsdpath->query("//" . $ndtpath);
                foreach ($ndlist as $nd)
                {
                    $nd->parentNode->removeChild($nd);
                }
            }
            unset($xsdpath);
            
            $filecontentdata = $dom->saveXML();
            echo $filecontentdata;
        
            // Extract the text part of the body and rudimentary formats major blocks with newlines
            // We assume that the document is well formed and that the tags do not intersect
            $pXML = new \XMLReader();
            
            if ($pXML->XML($filecontentdata))
            {
                $bExtract = FALSE;
                $bExtractElement = FALSE;
                
                // OpenDocument v1.0
                if (in_array($mimetype, [WIKINDX_MIMETYPE_STI, WIKINDX_MIMETYPE_STW, WIKINDX_MIMETYPE_SXI, WIKINDX_MIMETYPE_SXW]))
                {
                    $root_element = "office:body";
                }
                // OpenDocument Presentation v1.1, v1.2, and V1.3
                elseif (in_array($mimetype, [WIKINDX_MIMETYPE_ODP, WIKINDX_MIMETYPE_OTP]))
                {
                    $root_element = "office:presentation";
                }
                // OpenDocument Document v1.1, v1.2, and V1.3
                elseif (in_array($mimetype, [WIKINDX_MIMETYPE_ODT, WIKINDX_MIMETYPE_OTT]))
                {
                    $root_element = "office:text";
                }
                // Block the extraction if the mimetype is not supported
                else
                {
                    $root_element = "office:zzz";
                }
                
                while ($pXML->read())
                {
                    // Start extracting at the start of the text of the body
                    if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == $root_element)
                    {
                        $bExtract = TRUE;
                    }
                    // Stop extracting at the end of the text of the body
                    if ($pXML->nodeType == \XMLReader::END_ELEMENT && $pXML->name == $root_element)
                    {
                        $bExtract = FALSE;
                    }
                    
                    /*// Start extracting at the start of the text of the body
                    if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["office:presentation", "office:text"]))
                    {
                        $bExtract = TRUE;
                    }
                    // Stop extracting at the end of the text of the body
                    if ($pXML->nodeType == \XMLReader::END_ELEMENT && in_array($pXML->name, ["office:presentation", "office:text"]))
                    {
                        $bExtract = FALSE;
                    }*/
                    
                    // Transform spaces and tabs to spaces
                    if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["text:s", "text:tab"]))
                    {
                        $content .= "\t";
                    }
                    
                    // Transform spaces and tabs to spaces
                    if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["text:line-break"]))
                    {
                        $content .= LF;
                    }
                    
                    // Start extracting at the start of the text of the body
                    if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["text:h", "text:p", "text:list", "text:note", "text:numbered-paragraph", "text:ruby"]))
                    {
                        $bExtractElement = TRUE;
                    }
                    
                    // Stop extracting at the end of the text of the body
                    if ($pXML->nodeType == \XMLReader::END_ELEMENT && in_array($pXML->name, ["text:h", "text:p", "text:list", "text:note", "text:numbered-paragraph", "text:ruby"]))
                    {
                        $bExtractElement = FALSE;
                    }
                    
                    // Extract all node and add new lines on blocks
                    if ($bExtract && $bExtractElement)
                    {
                        $content .= $pXML->value;
                        if (in_array($pXML->name, ["text:h", "text:p", "text:list", "text:note", "text:numbered-paragraph", "text:ruby"]))
                        {
                            $content .= LF.LF;
                        }
                    }
                }
            }
            
            unset($pXML);
        }
        
        return $content;
    }
    
    
    /**
     * readRtf
     *
     * cf. https://interoperability.blob.core.windows.net/files/Archive_References/%5bMSFT-RTF%5d.pdf
     *
     * @param string $filename
     *
     * @return string
     */
    private function readRtf($filename)
    {
        $striped_content = "";
        $content = "";
        
        // Extract the content
        $content = file_get_contents($filename);
        
        $texter = new RtfStringTexter($content);
        $striped_content = $texter->AsString();
        
        unset($texter);
        
        return $striped_content;
    }

    /**
     * readEpub, extract the text content of EPUB ebooks
     *
     * All version of EPUB are supported with a single function
     * because the specification has changed very little
     * when we consider only its structure and text extraction. 
     *
     * Versions supported :
     *
     * - EPUB 3.2
     * - EPUB 3.1
     * - EPUB 3.0
     * - EPUB 2.0.1
     *
     * cf. EPUB 3.2 Spec., https://www.w3.org/publishing/epub3/epub-spec.html
     * cf. EPUB EPUB Specifications and Projects, http://idpf.org/epub/dir/
     *
     * @param string $filename
     *
     * @return string
     */
    private function readEpub($filename)
    {
        $content = "";
        
        // Open the container
        $za = new \ZipArchive();
        
        if ($za->open($filename) === TRUE)
        {
            $path_container = "META-INF/container.xml"; // Standard location of the top level entry file
            $file_container = $za->getFromName($path_container);
            if ($file_container !== FALSE)
            {
                // Extract the default Package Document path from the OCF Container
                // It's a manifest (map) of content files to render, and metadata
                // Alternatives manifest can be ignored safely (explained in the spec )
                // cf. https://www.w3.org/publishing/epub3/epub-ocf.html#sec-container-abstract
                $pXML = new \XMLReader();
                
                if ($pXML->XML($file_container))
                {
                    while ($pXML->read())
                    {
                        if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "rootfile")
                        {
                            $path_opf = $pXML->getAttribute("full-path");
                            break;
                        }
                    }
                }
                
                unset($pXML);
                
                // Package Document found
                $opf = [];   // List of content files
                $spine = []; // Rendering order of content files
                if ($path_opf !== NULL)
                {
                    $file_opf = $za->getFromName($path_opf);
                    if ($file_opf !== FALSE)
                    {
                        // Extract usefull metadata, the list of XHTML content files, and the spine
                        // There is no reuse of important tag names so we can simplify the parsing
                        // by reading only the elements encountered as we go. 
                        // cf. https://www.w3.org/publishing/epub3/epub-packages.html#sec-package-doc
                        $pXML = new \XMLReader();
                        
                        if ($pXML->XML($file_opf))
                        {
                            while ($pXML->read())
                            {
                                if ($pXML->nodeType == \XMLReader::ELEMENT)
                                {
                                    // Extract the list of XHTML content files (XHTML only, ignore SVG files, images, audio ...)
                                    if ($pXML->name == "item" && $pXML->getAttribute("media-type") == "application/xhtml+xml")
                                    {
                                        $opf[$pXML->getAttribute("id")] = $pXML->getAttribute("href");
                                    }
                                    // Extract the spine
                                    if ($pXML->name == "itemref")
                                    {
                                        $spine[] = $pXML->getAttribute("idref");
                                    }
                                    // Extract metadata (EPUB 2.0.1 only)
                                    elseif (in_array($pXML->name, ["dc:description", "dc:publisher"]))
                                    {
                                        $content .= $pXML->readInnerXml() . LF;
                                    }
                                    // Extract metadata (all EPUB versions)
                                    elseif (in_array($pXML->name, ["dc:contributor", "dc:creator", "dc:creator", "dc:title"]))
                                    {
                                        $content .= $pXML->readInnerXml() . LF;
                                    }
                                }
                            }
                        }
                        
                        unset($pXML);
                    }
                }
                
                // Extract the content from XHTML files following the rendering order of the spine
                // The spine doesn't include the navigation file but we don't need it
                if (count($opf) > 0 && count($spine) > 0)
                {
                    foreach ($spine as $idref)
                    {
                        if (array_key_exists($idref, $opf))
                        {
                            // The path can be absolute or relative to the OPF file directory
                            $path_xhtml = $opf[$idref];
                            if (basename($path_xhtml) == $path_xhtml)
                            {
                                $path_xhtml = implode("/", [dirname($path_opf), $path_xhtml]);
                            }
                            
                            $file_xhtml = $za->getFromName($path_xhtml);
                            if ($file_xhtml !== FALSE)
                            {
                                $path_xhtml_cache = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CACHE, "epub_" . \UTILS\uuid() . ".xhtml"]);
                                if (file_put_contents($path_xhtml_cache, $file_xhtml) !== FALSE)
                                {
                                    // The format is XHTML and not HTML according to the spec
                                    $content .= $this->readHtml($path_xhtml_cache) . LF;
                                    @unlink($path_xhtml_cache);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $content;
    }
    
    /**
     * readMht, extract the text content of an MHT multipart file (RFC2557)
     *
     * cf. https://tools.ietf.org/html/rfc2557
     *
     * @param string $filename
     *
     * @return string
     */
    private function readMht($filename)
    {
        $content = "";
        $boundary = "";
        $nBoundary = -1;
        $isHeader = TRUE;
        $headers = "";
        $file = "";
        $mime = "";
        $location = "";
        $cte = "";
        $charset = "";
        
        $fh = fopen($filename, "rb");
        if ($fh !== FALSE)
        {
            while (!feof($fh))
            {
                $line = fgets($fh);
                
                // Search the bondary token
                if ($boundary == "" && \UTILS\matchPrefix($line, "Content-Type:"))
                {
                    $matches = [];
                    if (preg_match("/boundary=\"(.+)\"/ui", $line, $matches) == 1)
                    {
                        $boundary = "--" . $matches[1] . "\r\n";
                    }
                }
                // Read files at boundaries
                elseif ($line == $boundary)
                {
                    $nBoundary++;
                    $isHeader = TRUE;
                    
                    if ($nBoundary > 0)
                    {
                        // Extract only document files
                        $path = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CACHE, "mht_" . \UTILS\uuid() . ".txt"]);
                        
                        // cf. https://tools.ietf.org/html/rfc2045#section-6
                        if ($cte == "quoted-printable")
                        {
                            $file = quoted_printable_decode($file);
                        }
                        elseif ($cte == "base64")
                        {
                            $file = base64_decode($file);
                        }
                        elseif ($cte == "binary")
                        {
                            // Raw data (do nothing)
                        }
                        elseif ($cte == "8bit")
                        {
                            if ($charset != "utf8" && $charset != "utf-8" && $charset != "us-ascii")
                            {
                                $fileutf8 = iconv($charset, 'UTF-8//TRANSLIT', $file);
                                if ($fileutf8 !== FALSE)
                                {
                                    $file = $fileutf8;
                                    unset($fileutf8);
                                }
                            }
                        }
                        elseif ($cte == "7bit")
                        {
                            // Raw data in ASCII (do nothing)
                        }
                        
                        if (file_put_contents($path, $file) !== FALSE)
                        {
                            // Go full circle!!!
                            $text = $this->convertToText($path, $mime);
                            $content .= $text . LF;
                            
                            @unlink($path);
                        }
                    }
                    
                    // Reset the file
                    $headers = "";
                    $file = "";
                    $location = "";
                    $cte = "";
                    $mime = "";
                    $charset = "";
                }
                else
                {
                    if ($isHeader)
                    {
                        if ($line == "\r\n")
                            $isHeader = FALSE;
                        else
                            $headers .= $line;
                        
                        // Extract headers
                        if (!$isHeader)
                        {
                            $matches = [];
                            if (preg_match("/Content-Location:(.+)/ui", $headers, $matches) == 1)
                            {
                                $location = trim($matches[1]);
                                $location = preg_replace("/\(.+\)/u", "", $location); // Remove comments
                                $location = trim($location);
                            }
                            $matches = [];
                            if (preg_match("/Content-Transfer-Encoding:(.+)/ui", $headers, $matches) == 1)
                            {
                                $cte = mb_strtolower(trim($matches[1]));
                                $cte = preg_replace("/\(.+\)/u", "", $cte); // Remove comments
                                $cte = trim($cte);
                            }
                            $matches = [];
                            if (preg_match("/Content-Type:(.+)/ui", $headers, $matches) == 1)
                            {
                                $mime = trim($matches[1]);
                                
                                $v = explode(";", $mime);
                                if (count($v) == 2)
                                {
                                    $mime = mb_strtolower(trim($v[0]));
                                    $matches = [];
                                    if (preg_match("/charset=(.+)/ui", $v[1], $matches) == 1)
                                    {
                                        $charset = mb_strtolower(trim($matches[1], " \"\n\r\t\v\0"));
                                        $charset = preg_replace("/\(.+\)/u", "", $charset); // Remove comments
                                        $charset = trim($charset);
                                    }
                                }
                                
                                $mime = preg_replace("/\(.+\)/u", "", $mime); // Remove comments
                                $mime = trim($mime);
                                
                            }
                        }
                    }
                    else
                    {
                        $file .= $line;
                    }
                }
            }
            
            fclose($fh);
        }
        
        return $content;
    }

    /**
     * readHtml, extract the text content of an (X)HTML file loosly
     *
     * Widely accepts elements of (X)HTML in all versions.
     * Remove items that are not textual or purely technical items.
     *
     * We assume that the document is well formed and the order is right
     *
     * @param string $filename
     *
     * @return string
     */
    private function readHtml($filename)
    {
        $content = "";
        
        // Load and normalize the content
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTMLFile($filename, LIBXML_NOWARNING | LIBXML_NOERROR);
        $dom->normalizeDocument();
        
        // Initalize the iterator
        $dit = new RecursiveIteratorIterator(
            new RecursiveDOMIterator($dom),
            RecursiveIteratorIterator::SELF_FIRST
        );

        // Extract text
        foreach($dit as $node)
        {
            // Remove blacklisted elements
            if ($node->nodeType === XML_ELEMENT_NODE && in_array($node->nodeName, ["applet","colgroup","form","head","img","link","listener","object","script","style"]))
            {
                $node->parentNode->removeChild($node);
            }
            // Read other elements
            elseif ($node->nodeType === XML_TEXT_NODE)
            {
                $content .= $node->nodeValue;
                $lastchar = mb_substr($node->nodeValue, -1);
                
                // Add an EOL for block elements if it is missing in the stream
                if ($lastchar != "\r" && $lastchar != "\n" && $node->parentNode->lastChild->isSameNode($node) && in_array($node->parentNode->nodeName, ["hr","li","ul","ol","hgroup","dd","div","dt","address","details","blockquote","p","h1","h2","h3","h4","h4","h6","table","tr","nav","main","aside","header","footer","article","section","figure","pre"]))
                {
                    $content .= LF;
                }
            }
        }
        
        return $content;
    }
    
    /**
     * readFictionBook, extract the text content of a FictionBook (v2)
     *
     * cf. http://www.gribuser.ru/xml/fictionbook/index.html.en
     *
     * @param string $filename
     *
     * @return string
     */
    function readFictionBook($filename)
    {
        $content = "";

        $pXML = new \XMLReader();
        
        $filecontent = file_get_contents($filename);
        
        if ($filecontent !== FALSE && $pXML->XML($filecontent))
        {
            $bExtract = FALSE;
            
            while ($pXML->read())
            {
                // Start extracting at the start of the description (headers)
                if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["description"]))
                {
                    $bExtract = TRUE;
                }
                // Stop extracting at the end of the description (headers)
                if ($pXML->nodeType == \XMLReader::END_ELEMENT && in_array($pXML->name, ["description"]))
                {
                    $bExtract = FALSE;
                }
                
                if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["body"]))
                {
                    $body = $pXML->readInnerXml();
                    
                    $path_html_cache = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CACHE, "fb2_" . \UTILS\uuid() . ".html"]);
                    if (file_put_contents($path_html_cache, $body) !== FALSE)
                    {
                        $content .= $this->readHtml($path_html_cache) . LF;
                        $content .= LF.LF;
                        @unlink($path_html_cache);
                    }
                    
                }
                
                // Extract all node and add new lines on blocks
                if ($bExtract)
                {
                    $content .= $pXML->value;
                    if (in_array($pXML->name, ["description"]))
                    {
                        $content .= LF.LF;
                    }
                }
            }
        }
        
        unset($pXML);
        
        return $content;
    }
    

    
    /*
     * readXps, extract the text content of XPS files
     *
     * cf. https://www.ecma-international.org/publications-and-standards/standards/ecma-388/
     *
     * @param string $filename
     *
     * @return string
     */
    private function readXps($filename)
    {
        $content = "";
        $rootmap = [];
        $map = [];
        $structmap = [];
        $string_catalog = [];
        
        // Extract the content parts
        $za = new \ZipArchive();
        
        // Explore the root file of the structure
        if ($za->open($filename) === TRUE)
        {
            $rootmapcontent = $za->getFromName("FixedDocSeq.fdseq");
            if ($rootmapcontent !== FALSE && $rootmapcontent != "")
            {
                $pXML = new \XMLReader();
                
                if ($pXML->XML($rootmapcontent))
                {
                    while ($pXML->read())
                    {
                        if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "DocumentReference")
                        {
                            if ($pXML->getAttribute("Source") != NULL)
                            {
                                echo $pXML->getAttribute("Source") . LF;
                                $rootmap[] = ltrim($pXML->getAttribute("Source"), "/");
                            }
                        }
                    }
                }
            }
            
            if (count($rootmap) > 0)
            {
                foreach($rootmap as $doc)
                {
                    $doccontent = $za->getFromName($doc);
                    if ($doccontent !== FALSE && $doccontent != "")
                    {
                        $pXML = new \XMLReader();
                        
                        if ($pXML->XML($doccontent))
                        {
                            while ($pXML->read())
                            {
                                if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "PageContent")
                                {
                                    if ($pXML->getAttribute("Source") != NULL)
                                    {
                                        $map[] = dirname($doc) . "/" . ltrim($pXML->getAttribute("Source"), "/");
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            if (count($map) > 0)
            {
                natsort($map);
                
                foreach($map as $page)
                {
                    $pagecontent = $za->getFromName($page);
                    if ($pagecontent !== FALSE && $pagecontent != "")
                    {
                        $pagecontent = mb_convert_encoding($pagecontent, "UTF-8", "UTF-16LE");
                        $pXML = new \XMLReader();
                        
                        if ($pXML->XML($pagecontent))
                        {
                            while ($pXML->read())
                            {
                                if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "Glyphs")
                                {
                                    if ($pXML->getAttribute("Name") != NULL && $pXML->getAttribute("UnicodeString") != NULL)
                                    {
                                        $string_catalog[$pXML->getAttribute("Name")] = $pXML->getAttribute("UnicodeString");
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            if (count($string_catalog) > 0)
            {
                // On macOS extractTo() doesn't work, so we emulate it
                for ($k = 0; $k < $za->numFiles; $k++)
                {
                    // Get a stream from the original name
                    $filename = $za->getNameIndex($k);
                    
                    // Skip non structure frag files
                    if (!\UTILS\matchSuffix($filename, ".frag"))
                    {
                        continue;
                    }
                    
                    $structmap[] = $filename;
                }
            }
            
            if (count($structmap) > 0)
            {
                natsort($structmap);
                
                foreach($structmap as $struct)
                {
                    $structcontent = $za->getFromName($struct);
                    if ($structcontent !== FALSE && $structcontent != "")
                    {
                        $pXML = new \XMLReader();
                        
                        if ($pXML->XML($structcontent))
                        {
                            while ($pXML->read())
                            {
                                if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "NamedElement")
                                {
                                    $key = $pXML->getAttribute("NameReference");
                                    if ($key != NULL)
                                    {
                                        if (array_key_exists($key, $string_catalog))
                                        {
                                            $content .= $string_catalog[$key];
                                        }
                                    }
                                }
                                if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "ParagraphStructure")
                                {
                                    $content .= LF;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        unset($za);
        
        return $content;
    }
    
    /*
     * readScribus, extract the text content of Scribus files (SLA)
     *
     * cf. https://wiki.scribus.net/canvas/(FR)_Introdution_au_Format_de_fichier_SLA_pour_Scribus_1.4
     * cf. https://github.com/scribusproject/scribus/tree/master/resources/tests
     *
     * @param string $filename
     *
     * @return string
     */
    function readScribus($filename)
    {
        $content = "";
        
        $pXML = new \XMLReader();
        
        $filecontent = file_get_contents($filename);
        if ($pXML->XML($filecontent))
        {
            while ($pXML->read())
            {
                if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "ITEXT")
                {
                    $content .= htmlspecialchars_decode($pXML->getAttribute("CH"), ENT_HTML5) . LF;
                }
            }
        }
        
        return $content;
    }
}
