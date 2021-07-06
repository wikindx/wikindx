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
 * Convert files of various types to text ready for searching.
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
     * Convert files of various types to text ready for searching and return it
     *
     * This function dispatches the conversion to functions specialized by mime-type.
     *
     * The dispatching is done according to the mime-type AND file extension.
     * So you MUST pass a file with an appropriate extension.
     *
     * @param string $filepath An absolute or relative file path
     * @param string $mimetype A mime-type. Default is "text/plain"
     *
     * @return string Text extracted
     */
    public function convertToText($filepath, $mimetype = "text/plain")
    {
        $extension = \FILE\getExtension($filepath);
        
        switch ($mimetype)
        {
            case WIKINDX_MIMETYPE_ABW:
                // AbiWord
                $text = $this->readAbiWord($filepath);
            break;
            case WIKINDX_MIMETYPE_DJV:
                // DjVu
                $text = $this->readDjVu($filepath);
            break;
            case WIKINDX_MIMETYPE_DOC:
                // Microsoft Office Word (before 2007)
                $text = $this->readWordBinary($filepath);
            break;
            case WIKINDX_MIMETYPE_DOCM:
            case WIKINDX_MIMETYPE_DOCX:
            case WIKINDX_MIMETYPE_DOTM:
            case WIKINDX_MIMETYPE_DOTX:
                // Microsoft Office Word (2007 and higher)
                $text = $this->readWordXML($filepath);
            break;
            case WIKINDX_MIMETYPE_DVI:
                // DVI
                $text = $this->readDVI($filepath);
            break;
            case WIKINDX_MIMETYPE_EPUB:
                // EPUB
                $text = $this->readEPUB($filepath);
            break;
            case WIKINDX_MIMETYPE_FB:
                // Fiction Book
                $text = $this->readFictionBook($filepath);
            break;
            case WIKINDX_MIMETYPE_HTML:
            case WIKINDX_MIMETYPE_XHTML:
                // (X)HTML
                $text = $this->readHTML($filepath);
            break;
            case WIKINDX_MIMETYPE_MHT_ALT:
            case WIKINDX_MIMETYPE_MHT_APP:
            case WIKINDX_MIMETYPE_MHT_MIX:
            case WIKINDX_MIMETYPE_MHT_MUL:
            case WIKINDX_MIMETYPE_MHT_RFC:
                // Multipart file (RFC2557)
                $text = $this->readMultipart($filepath);
            break;
            case WIKINDX_MIMETYPE_ODP:
            case WIKINDX_MIMETYPE_ODT:
            case WIKINDX_MIMETYPE_OTP:
            case WIKINDX_MIMETYPE_OTT:
            case WIKINDX_MIMETYPE_STI:
            case WIKINDX_MIMETYPE_SXI:
            case WIKINDX_MIMETYPE_SXW:
            case WIKINDX_MIMETYPE_STW:
                // LibreOffice/OpenDocument/SunOffice Document and Presentation
                $text = $this->readOpenDocument($filepath);
            break;
            case WIKINDX_MIMETYPE_POTM:
            case WIKINDX_MIMETYPE_POTX:
            case WIKINDX_MIMETYPE_PPTM:
            case WIKINDX_MIMETYPE_PPTX:
                // Microsoft Office PowerPoint (2007 and higher)
                $text = $this->readPowerPointXML($filepath);
            break;
            case WIKINDX_MIMETYPE_PDF:
            case WIKINDX_MIMETYPE_XPDF:
                // PDF
                $text = $this->readPDF($filepath);
            break;
            case WIKINDX_MIMETYPE_PS:
                // PostScript
                $text = $this->readPostScript($filepath);
            break;
            case WIKINDX_MIMETYPE_RTF_APP:
            case WIKINDX_MIMETYPE_RTF_TEXT:
                // Rtf
                $text = $this->readRTF($filepath);
            break;
            case WIKINDX_MIMETYPE_SCRIBUS:
                // Scribus
                $text = $this->readScribus($filepath);
            break;
            case WIKINDX_MIMETYPE_MD:
            case WIKINDX_MIMETYPE_TXT:
                // Various texts
                switch ($extension)
                {
                    // SYLK is a spreadsheet file format
                    case "slk":
                        // Type not handled
                        $text = "";
                    break;
                    default:
                        $text = $this->readText($filepath);
                    break;
                }
            break;
            case WIKINDX_MIMETYPE_XML_APP:
            case WIKINDX_MIMETYPE_XML_TEXT:
                // Untyped XML
                $text = $this->readHTML($filepath);
            break;
            case WIKINDX_MIMETYPE_XPS:
                // XPS
                $text = $this->readXPS($filepath);
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
        
        // Replace Unicode characters classes by a single space:
        // - Control and format characters (C)
        // - Separator characters (Z)
        $text = preg_replace("/\p{C}|\p{Z}/u", " ", $text);
        // Replace series of spaces with a single space
        $text = preg_replace("/ {2,}/u", " ", $text);
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * Extract the text content of PDF files (PDF)
     *
     * Adobe Portable Document Format extracted with PdfToText. PHP class of Christian Vigh or XPdf utilities.
     *
     * The supported extensions of the format are not well defined.
     * In the case of xpdf the support is supposed to be cutting edge. 
     *
     * @see https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/pdf_reference_archives/PDFReference.pdf Adobe Portable Document Format v1.4 
     * @see https://www.xpdfreader.com/pdftotext-man.html pdftotext manpage
     * @see https://www.xpdfreader.com/pdfinfo-man.html pdfinfo manpage
     *
     * @param mixed $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    private function readPDF($filepath)
    {
        static $plugin_xpdftotext_exists = NULL;
        
        // Check xpdftotext plugin availability
        if ($plugin_xpdftotext_exists === NULL)
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "startup", "LOADPLUGINS.php"]));
            $loadmodules = new \LOADPLUGINS();
            $moduleList = $loadmodules->readPluginsDirectory();
            $plugin_xpdftotext_exists = in_array("xpdftotext", $moduleList);
        }
        
        // Use the best parser available
        if ($plugin_xpdftotext_exists)
        {
            // 1. Use XpdfReader tools
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, "xpdftotext", "XPDFREADER.php"]));

            $metadata = \XPDFREADER\pdfinfo($filepath);

            $text = \XPDFREADER\pdftotext(
                $filepath, [
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
                $text = $importPDF->Load($filepath);
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
     * Extract the text content of plain text files
     *
     * Markdown, reStructured text are supported as plain text.
     *
     * @param mixed $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    private function readText($filepath)
    {
        $content = file_get_contents($filepath);
        if ($content === FALSE)
            return "";
        else
            return $content;
    }
    
    /**
     * Extract the text content of Microsoft Office Word files (before 2007) (DOC, DOT)
     *
     * Binary format of Microsoft office Suite. Templates and plain documents are supported,
     * with and without macros. All versions before Word 2007 are supported.
     *
     * @see https://coderwall.com/p/x_n4tq/how-to-read-doc-using-php How to read .doc using PHP
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    private function readWordBinary($filepath)
    {
        $content = "";

        if (($fh = fopen($filepath, 'r')) !== FALSE)
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
            if ($textLength > 0)
            {
                $content = fread($fh, $textLength);
                if ($content === FALSE) $content = "";
                $content = utf8_encode($content);
            }

            fclose($fh);
        }

        return $content;
    }
    
    /**
     * Extract the text content of Microsoft office Word files (2007 and higher) (DOCX, DOCM...)
     *
     * XML format of Microsoft office Suite. Templates and plain documents are supported,
     * with and without macros. All versions starting with Word 2007 are supported.
     *
     * @see https://www.ecma-international.org/publications/standards/Ecma-376.htm ECMA-376 - Office Open XML file formats
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    private function readWordXML($filepath)
    {
        $content = "";
        
        // Open the OCF container
        $za = new \ZipArchive();
        
        if ($za->open($filepath) === TRUE)
        {
            // There are in defined locations, so we don't need to parse the Package Document Map
            foreach (["word/document.xml", "word/comments.xml", "word/endnotes.xml", "word/footnotes.xml"] as $f)
            {
                $filecontent = $za->getFromName($f);
                if ($filecontent !== FALSE && $filecontent != "")
                {
                    // Extract the text part of the body and rudimentary formats major blocks with newlines
                    // We assume that the document is well formed and that the tags do not intersect
                    $pXML = new \XMLReader();
                    
                    if ($pXML->XML($filecontent))
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
                            
                            // Extract and add new lines on blocks
                            if ($bExtract && $bExtractElement)
                            {
                                $content .= $pXML->value;
                                if (in_array($pXML->name, ["w:p"]))
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
     * Extract the text content of Microsoft Office PowerPoint files (2007 and higher)
     *
     * XML format of Microsoft office Suite. Templates and plain documents are supported,
     * with and without macros. All versions starting with PowerPoint 2007 are supported.
     *
     * @see https://www.ecma-international.org/publications/standards/Ecma-376.htm ECMA-376 - Office Open XML file formats
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    private function readPowerPointXML($filepath)
    {
        $content = "";
            
        // Open the OCF container
        $za = new \ZipArchive();
        
        if ($za->open($filepath) === TRUE)
        {
            // On macOS extractTo() doesn't work, so we emulate it
            for ($k = 0; $k < $za->numFiles; $k++)
            {
                // Get a stream from the original name
                $filepath = $za->getNameIndex($k);
                
                // Keep only the content of slide and comment XML files
                // They are in defined locations, so we don't need to parse the Package Document Map
                if (!((\UTILS\matchPrefix($filepath, "ppt/slides/slide") || \UTILS\matchPrefix($filepath, "ppt/comments/comment")) && \UTILS\matchSuffix($filepath, ".xml")))
                {
                    continue;
                }
                
                $filecontent = $za->getFromName($filepath);

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
     * Extract the text content of LibreOffice/OpenDocument/SunOffice Document and Presentation files (ODP, ODT...)
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
     * @see https://www.oasis-open.org/standards/ Open Document Format for Office Applications (OpenDocument) Version 1.X
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    private function readOpenDocument($filepath)
    {
        $content = "";
        $content = "";
        
        // Open the OCF container
        $za = new \ZipArchive();
        $errcode = $za->open($filepath);
        
        // Like EPUB, ODT are packaged with OCF container,
        // but since ODT also use fixed paths for XML files we open them without loading the Package Document Map
        if ($errcode === TRUE)
        {
            // Extract mimetype
            $mimetype = $za->getFromName("mimetype");
            
            // Extract metadata
            $filedata = $za->getFromName("meta.xml");
            if ($filedata !== FALSE && $filedata != "")
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
            $filecontentdata = file_get_contents($filepath);
        }
        else
        {
            $filecontentdata = FALSE;
        }
        
        if ($filecontentdata !== FALSE && $filecontentdata != "")
        {
            // Load and normalize the content
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadXML($filecontentdata, LIBXML_NOWARNING | LIBXML_NOERROR);
            
            $xsdpath = new DOMXPath($dom);
            
            // Remove base64 encoded content before parsing
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
            
            // Sniff the mime-type of flat OpenDocument documents
            $mimetype = $mimetype ?? "";
            if ($mimetype == "")
            {
                // Extract the text part of the body and rudimentary formats major blocks with newlines
                // We assume that the document is well formed and that the tags do not intersect
                $pXML = new \XMLReader();
                
                if ($pXML->XML($filecontentdata))
                {
                    while ($pXML->read())
                    {
                        // "office:mimetype" attribut of "office:document" elements
                        if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "office:document")
                        {
                            if ($pXML->getAttribute("office:mimetype") != NULL)
                            {
                                $mimetype = $pXML->getAttribute("office:mimetype");
                                break;
                            }
                        }
                    }
                }
                
                unset($pXML);
            }
        
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
                    // Start extracting at the start of the root element of the current format
                    if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == $root_element)
                    {
                        $bExtract = TRUE;
                    }
                    // Stop extracting at the end of the root element of the current format
                    if ($pXML->nodeType == \XMLReader::END_ELEMENT && $pXML->name == $root_element)
                    {
                        $bExtract = FALSE;
                    }
                    
                    // Transform spaces and tabs to spaces
                    if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["text:s", "text:tab"]))
                    {
                        $content .= "\t";
                    }
                    
                    // Extract new lines
                    if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["text:line-break"]))
                    {
                        $content .= LF;
                    }
                    
                    // Start extracting at the start of text elements (ruby is for asian languages)
                    if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["text:h", "text:p", "text:list", "text:note", "text:numbered-paragraph", "text:ruby"]))
                    {
                        $bExtractElement = TRUE;
                    }
                    
                    // Stop extracting at the end of text elements
                    if ($pXML->nodeType == \XMLReader::END_ELEMENT && in_array($pXML->name, ["text:h", "text:p", "text:list", "text:note", "text:numbered-paragraph", "text:ruby"]))
                    {
                        $bExtractElement = FALSE;
                    }
                    
                    // Extract and add new lines on blocks
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
     * Extract the text content of Rich Text Format (RTF) files
     *
     * Extracted with RTF classes of Christian Vigh. All version of RTF are supported.
     *
     * @see https://interoperability.blob.core.windows.net/files/Archive_References/%5bMSFT-RTF%5d.pdf Rich Text Format Specification
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    private function readRTF($filepath)
    {
        $striped_content = "";
        $content = "";
        
        // Extract the content
        $content = file_get_contents($filepath);
        if ($content === FALSE) $content = "";
        
        $texter = new RtfStringTexter($content);
        $striped_content = $texter->AsString();
        
        unset($texter);
        
        return $striped_content;
    }

    /**
     * Extract the text content of EPUB ebooks (EPUB)
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
     * @see https://www.w3.org/publishing/epub3/epub-spec.html EPUB 3.2 Spec.
     * @see http://idpf.org/epub/dir/ EPUB Specifications and Projects
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    private function readEPUB($filepath)
    {
        $content = "";
        
        // Open the OCF container
        $za = new \ZipArchive();
        
        if ($za->open($filepath) === TRUE)
        {
            $path_container = "META-INF/container.xml"; // Standard location of the top level entry file
            $file_container = $za->getFromName($path_container);
            if ($file_container !== FALSE && $file_container != "")
            {
                // Extract the default Package Document path from the OCF Container
                // It's a manifest (map) of content files to render, and metadata
                // Alternatives manifest can be ignored safely (explained in the spec)
                // cf. https://www.w3.org/publishing/epub3/epub-ocf.html#sec-container-abstract
                $pXML = new \XMLReader();
                
                if ($pXML->XML($file_container))
                {
                    while ($pXML->read())
                    {
                        // The Package Document path is stored on the full-path attribut of the rootfile node
                        if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "rootfile")
                        {
                            $path_opf = $pXML->getAttribute("full-path");
                            break;
                        }
                    }
                }
                
                unset($pXML);
                
                // Package Document parsing
                $opf = [];   // List of content files
                $spine = []; // Rendering order of content files
                if ($path_opf !== NULL)
                {
                    $file_opf = $za->getFromName($path_opf);
                    if ($file_opf !== FALSE && $file_opf != "")
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
                            // The path can be absolute or relative to the OCF file directory
                            $path_xhtml = $opf[$idref];
                            if (basename($path_xhtml) == $path_xhtml)
                            {
                                $path_xhtml = implode("/", [dirname($path_opf), $path_xhtml]);
                            }
                            
                            $file_xhtml = $za->getFromName($path_xhtml);
                            if ($file_xhtml !== FALSE && $file_xhtml != "")
                            {
                                $path_xhtml_cache = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE, "epub_" . \UTILS\uuid() . ".xhtml"]);
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
     * Extract the text content of multipart files (RFC2557) (EML, MHT)
     *
     * This format is a container for any number of files of arbitrary mime-type,
     * separated by text boundaries. It's is used for packed HTML and email storage.
     *
     * Each file is extracted, reencoded in UTF-8 (with transliteration) if possible, and parsed with convertToText().
     *
     * @see https://tools.ietf.org/html/rfc2557 RFC2557 - MIME Encapsulation of Aggregate Documents, such as HTML (MHTML)
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    private function readMultipart($filepath)
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
        
        $fh = fopen($filepath, "rb");
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
                        $path = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE, "mht_" . \UTILS\uuid() . ".txt"]);
                        
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
     * Extract the text content of (X)HTML files (loosly)
     *
     * Widely accepts elements of (X)HTML in all versions.
     * Remove items that are not textual or purely technical items.
     *
     * We assume that the document is malformed (normalization is performed)
     * and can be parsed in reading order.
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    private function readHtml($filepath)
    {
        $content = "";
        
        // Load and normalize the content
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTMLFile($filepath, LIBXML_NOWARNING | LIBXML_NOERROR);
        $dom->normalizeDocument();
        
        // Initalize the recursive iterator
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
     * Extract the text content of FictionBook ebooks (FB1, FB2)
     *
     * Russian XML ebook format.
     *
     * Versions supported :
     *
     * - v1 (no documentation found but that should word)
     * - v2
     *
     * @see http://www.gribuser.ru/xml/fictionbook/index.html.en FictionBook 2.0 Specification
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    function readFictionBook($filepath)
    {
        $content = "";

        $pXML = new \XMLReader();
        
        $filecontent = file_get_contents($filepath);
        
        if ($filecontent !== FALSE && $filecontent != "" && $pXML->XML($filecontent))
        {
            $bExtract = FALSE;
            
            while ($pXML->read())
            {
                // Start extracting at the start of description elements (metadata)
                if ($pXML->nodeType == \XMLReader::ELEMENT && in_array($pXML->name, ["description"]))
                {
                    $bExtract = TRUE;
                }
                // Stop extracting at the end of description elements (metadata)
                if ($pXML->nodeType == \XMLReader::END_ELEMENT && in_array($pXML->name, ["description"]))
                {
                    $bExtract = FALSE;
                }
                
                // Extract metadata
                if ($bExtract)
                {
                    $content .= $pXML->value;
                    if ($pXML->name == "description")
                    {
                        $content .= LF.LF;
                    }
                }
                
                // Extract the body part (HTML document without headers inside the body element only)
                if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "body")
                {
                    $body = $pXML->readInnerXml();
                    
                    $path_html_cache = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE, "fb_" . \UTILS\uuid() . ".html"]);
                    if (file_put_contents($path_html_cache, $body) !== FALSE)
                    {
                        $content .= $this->readHtml($path_html_cache) . LF;
                        $content .= LF.LF;
                        @unlink($path_html_cache);
                    }
                    
                }
            }
        }
        
        unset($pXML);
        
        return $content;
    }
    

    
    /*
     * Extract the text content of Open XML Paper Specification files (XPS, OXPS)
     *
     * All versions are supported.
     *
     * @see https://www.ecma-international.org/publications-and-standards/standards/ecma-388/ ECMA-388 - Open XML paper specification (OpenXPS)
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    private function readXPS($filepath)
    {
        $content = "";
        $rootmap = [];
        $map = [];
        $structmap = [];
        $string_catalog = [];
        
        // Open the OCF container
        $za = new \ZipArchive();
        
        if ($za->open($filepath) === TRUE)
        {
            // Explode the root file of the structure and find the map file of the page source files
            $rootmapcontent = $za->getFromName("FixedDocSeq.fdseq");
            if ($rootmapcontent !== FALSE && $rootmapcontent != "")
            {
                $pXML = new \XMLReader();
                
                if ($pXML->XML($rootmapcontent))
                {
                    while ($pXML->read())
                    {
                        // "Source" attribut of "DocumentReference" elements
                        if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "DocumentReference")
                        {
                            if ($pXML->getAttribute("Source") != NULL)
                            {
                                // Remove "/" from the beginning because getFromName() dislikes it
                                $rootmap[] = ltrim($pXML->getAttribute("Source"), "/");
                            }
                        }
                    }
                }
            }
            
            // Extract the list of all page source files form the map file
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
                                // "Source" attribut of "PageContent" elements
                                if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "PageContent")
                                {
                                    if ($pXML->getAttribute("Source") != NULL)
                                    {
                                        // Remove "/" from the beginning because getFromName() dislikes it
                                        // Build a relative path from the container folder
                                        $map[] = dirname($doc) . "/" . ltrim($pXML->getAttribute("Source"), "/");
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Extract the text content of page source files and reencode it to UTF-8 (from UTF-16 LE)
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
                                // "Name" attribut of "Glyphs" elements = ID of the content
                                // "UnicodeString" attribut of "Glyphs" elements = CONTENT
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
            
            // Extract the list of all frag structure files for each page source file
            if (count($string_catalog) > 0)
            {
                // On macOS extractTo() doesn't work, so we emulate it
                for ($k = 0; $k < $za->numFiles; $k++)
                {
                    // Get a stream from the original name
                    $filepath = $za->getNameIndex($k);
                    
                    // Skip non structure frag files
                    if (!\UTILS\matchSuffix($filepath, ".frag"))
                    {
                        continue;
                    }
                    
                    $structmap[] = $filepath;
                }
            }
            
            // Build the XPS content but concatenating the content of each page source following the order described in the frag structures files
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
                                // Concat "NameReference" attribut of "NamedElement" elements
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
                                // Add a new line at the beginning of each paragraph
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
     * Extract the text content of Scribus files (SLA)
     *
     * This XML format is not documented but it seems the text
     * is always enclosed inside the "CH" attribut of "ITEXT" elements.
     *
     * @see https://wiki.scribus.net/canvas/(FR)_Introdution_au_Format_de_fichier_SLA_pour_Scribus_1.4 Scribus File Format
     * @see https://github.com/scribusproject/scribus/tree/master/resources/tests Scribus file samples
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    function readScribus($filepath)
    {
        $content = "";
        
        $pXML = new \XMLReader();
        
        $filecontent = file_get_contents($filepath);
        if ($filecontent !== FALSE && $filecontent != "")
        {
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
        }
        
        return $content;
    }
    
    /**
     * Extract the text content of AbiWord files (ABW, AWT, ZABW)
     *
     * This XML format is not documented but it seems the text
     * is always enclosed inside "p" elements.
     *
     * @see http://www.abisource.com/wiki/AbiWord AbiWord Format
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    function readAbiWord($filepath)
    {
        $content = "";
        
        $filecontent = file_get_contents($filepath);
        
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
                    // Start extracting at the start of the p element
                    if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "p")
                    {
                        $bExtract = TRUE;
                    }
                    // Stop extracting at the end of the p element
                    if ($pXML->nodeType == \XMLReader::END_ELEMENT && $pXML->name == "p")
                    {
                        $bExtract = FALSE;
                    }
                    
                    // Extract and add new lines before each "p" element
                    if ($bExtract)
                    {
                        $content .= $pXML->value;
                        if ($pXML->name == "p")
                        {
                            $content .= LF;
                        }
                    }
                }
            }
            
            unset($pXML);
        }
        
        return $content;
    }
    
    /*
     * Extract the text content of DjVu files (DJV, DJVU) with djvutxt utility
     *
     * This format is used for archiving and contains text if an OCR have been used.
     *
     * djvutxt utility is included in DjVuLibre toolbox.
     *
     * @see http://djvu.sourceforge.net/doc/man/djvutxt.html djvutxt manpage
     * @see http://djvu.sourceforge.net DjVuLibre website
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    function readDjVu($filepath)
    {
        $content = "";
        
        // Utility config
        $txtfile = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CACHE, "djvu_" . \UTILS\uuid() . ".txt"]);
        $bin = "djvutxt";
        if (WIKINDX_BIN_FOLDER_DJVUTXT != "")
        {
            $bin = implode(DIRECTORY_SEPARATOR, [WIKINDX_BIN_FOLDER_DJVUTXT, $bin]);
        }
        $cmd = '"' . $bin . '" "' . $filepath . '" "' . $txtfile . '"';
        
        // Extract
        $execerrno = 0;
        $execoutput = [];
        exec($cmd, $execoutput, $execerrno);
        
        // Read and remove the result file
        if (file_exists($txtfile))
        {
            $content = file_get_contents($txtfile);
            if ($content === FALSE) $content = "";
            @unlink($txtfile);
        }
        
        return $content;
    }
    
    /*
     * Extract the text content of DeVice Independent files (DVI) with catdvi utility
     *
     * This format is a byproduct of a TeX compilation.
     *
     * catdvi utility is included in most TeX distributions like TeX Live.
     *
     * @see http://catdvi.sourceforge.net/ catdvi website
     * @see https://tug.org/texlive/ Tex Live website
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    function readDVI($filepath)
    {
        $content = "";
        
        // Utility config
        $txtfile = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CACHE, "dvi_" . \UTILS\uuid() . ".txt"]);
        $bin = "catdvi";
        if (WIKINDX_BIN_FOLDER_CATDVI != "")
        {
            $bin = implode(DIRECTORY_SEPARATOR, [WIKINDX_BIN_FOLDER_CATDVI, $bin]);
        }
        // "sequential" option allows to read a multicolumn document in human reading order
        $cmd = '"' . $bin . '" --output-encoding=UTF-8 --sequential "' . $filepath . '" > "' . $txtfile . '"';
        
        // Extract
        $execerrno = 0;
        $execoutput = [];
        exec($cmd, $execoutput, $execerrno);
        
        // Read and remove the result file
        if (file_exists($txtfile))
        {
            $content = file_get_contents($txtfile);
            if ($content === FALSE) $content = "";
            @unlink($txtfile);
        }
        
        return $content;
    }
    
    /*
     * Extract the text content of PostScript files (PS, EPS) with ps2pdf utility
     *
     * This Adobe format is a scripted document that need GhostScript to be interpreted.
     *
     * ps2pdf utility is included in GhostScript.
     *
     * @see http://web.mit.edu/ghostscript/www/Ps2pdf.htm ps2pdf manpage
     * @see https://www.ghostscript.com/ Ghostscript website
     *
     * @param string $filepath An absolute or relative file path
     *
     * @return string Text extracted
     */
    function readPostScript($filepath)
    {
        $content = "";
        
        // Utility config
        $pdffile = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE, "ps_" . \UTILS\uuid() . ".pdf"]);
        $bin = "ps2pdf";
        if (WIKINDX_BIN_FOLDER_PS2PDF != "")
        {
            $bin = implode(DIRECTORY_SEPARATOR, [WIKINDX_BIN_FOLDER_PS2PDF, $bin]);
        }
        $cmd = '"' . $bin . '" "' . $filepath . '" "' . $pdffile . '"';
        
        // Extract
        $execerrno = 0;
        $execoutput = [];
        exec($cmd, $execoutput, $execerrno);
        
        // Read and remove the result file
        if (file_exists($pdffile))
        {
            $content = $this->readPDF($pdffile);
            @unlink($pdf);
        }
        
        return $content;
    }
}
