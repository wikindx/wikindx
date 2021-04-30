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
        
        $dirData = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS]);
        
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "attachments", "ATTACHMENTS.php"]));
        $att = new ATTACHMENTS();
        
        $mem = ini_get('memory_limit');
        $maxExecTime = ini_get('max_execution_time');
        ini_set('memory_limit', '-1'); // NB not always possible to set
        ini_set('max_execution_time', '-1'); // NB not always possible to set
        // Turn error display off so that errors from PdfToText don't get written to screen (still written to the cache files)
        $errorDisplay = ini_get('display_errors');
        ini_set('display_errors', FALSE);
        if (array_key_exists('cacheCurl', $vars) && ($vars['cacheCurl'] == 'on') && function_exists('curl_multi_exec'))
        {
            $session->setVar("cache_Curl", TRUE);
            $curlExists = TRUE;
        }
        else
        {
            $session->delVar("cache_Curl");
            $curlExists = FALSE;
        }
        // Attempting to avoid timeouts if max execution time cannot be set. This is done on a trial and error basis.
        $memtmp = \FILE\return_bytes(ini_get('memory_limit'));
        if ($memtmp == -1)
        {
            $maxCount = FALSE;
            $maxSize = FALSE; // unlimited
        }
        elseif ($memtmp >= \FILE\return_bytes("40M"))
        {
            $maxCount = 30;
            $maxSize = 30000000; // 30MB
        }
        elseif ($memtmp >= \FILE\return_bytes("25M"))
        {
            $maxCount = 20;
            $maxSize = 15000000; // 15MB
        }
        else
        {
            $maxCount = 10;
            $maxSize = 5000000; // 5MB
        }
        $input = FALSE;
        if (array_key_exists('cacheLimit', $vars))
        {
            $input = trim($vars['cacheLimit']);
            if (is_numeric($input) && is_int($input + 0))
            { // include cast to number
                $maxCount = $input;
                $session->setVar("cache_Limit", $input);
            }
        }
        if (!$input)
        {
            $session->delVar("cache_Limit");
        }
        
        $count = 0;
        $size = 0;
        
        if ($curlExists)
        {
            $ch = [];
            $mh = curl_multi_init();
        }
        
        $db->formatConditions(["resourceattachmentsText" => 'IS NULL']);
        $resultSet = $db->select('resource_attachments', ['resourceattachmentsHashFilename']);
        while ($row = $db->fetchRow($resultSet))
        {
            $file = $row['resourceattachmentsHashFilename'];
            $pathData = implode(DIRECTORY_SEPARATOR, [$dirData, $file]);
            
            if ($curlExists)
            {
                $curlTarget = WIKINDX_URL_BASE . '/index.php?' .
                'action=attachments_ATTACHMENTS_CORE' .
                '&method=curlRefreshCache' .
                '&filename=' . urlencode($file);
                $ch_x = curl_init($curlTarget);
                $ch[$file] = $ch_x;
                curl_setopt($ch_x, CURLOPT_RETURNTRANSFER, TRUE);
                // Get the headers too
                curl_setopt($ch_x, CURLOPT_HEADER, TRUE);
                curl_setopt($ch_x, CURLOPT_TIMEOUT, ini_get('max_execution_time'));
                curl_multi_add_handle($mh, $ch_x);
            }
            else
            {
                try
                {
                    $att->refreshCache($file, TRUE);
                }
                catch (Exception $e)
                {
                    // Nothing to do
                }
            }
            
            ++$count;
            $size += filesize($pathData);
            
            if ($maxCount)
            {
                if ($count >= $maxCount)
                {
                    break;
                }
            }
            if ($maxSize)
            {
                if ($size >= $maxSize)
                {
                    break;
                }
            }
        }
        
        if ($curlExists)
        {
            $running = NULL;
            do
            {
                curl_multi_exec($mh, $running);
            } while ($running);
            foreach ($ch as $ch_x)
            {
                curl_multi_remove_handle($mh, $ch_x);

                if (version_compare(PHP_VERSION, '8.0.0', '<'))
                {
                    curl_close($ch_x);
                }
            }
            if (version_compare(PHP_VERSION, '8.0.0', '<'))
            {
                curl_multi_close($mh);
            }
        }
        
        list($nbFilesMissing, $nbFilesTotal) = $this->countMissingCacheAttachment();
        $previousRemain = $session->getVar("cache_AttachmentsRemain");
        $session->setVar("cache_AttachmentsRemain", $nbFilesMissing);
        $done = $session->getVar("cache_AttachmentsDone") + ($previousRemain - $nbFilesMissing);
        $session->setVar("cache_AttachmentsDone", $done);
        
        ini_set('display_errors', $errorDisplay);
        ini_set('max_execution_time', $maxExecTime);
        
        if ($nbFilesMissing > 0)
        {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "startup", "HOUSEKEEPING.php"]));
            $hk = new HOUSEKEEPING(FALSE);
        }
        else
        {
            header("Location: index.php");
            die();
        }
    }

    /**
     * convertToText
     *
     * @param string $filename
     * @param string $extension
     *
     * @return string
     */
    public function convertToText($filename, $extension = "")
    {
        // Retrieve the mime type and the extension of the original file
        $mimeType = \FILE\getMimeType($filename);
        if ($extension == "")
        {
            $extension = \FILE\getExtension($filename);
        }
        // Convert to text with a specific function by mimetype and return it
        switch ($mimeType)
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
            case WIKINDX_MIMETYPE_HTML:
            case WIKINDX_MIMETYPE_XHTML:
            case WIKINDX_MIMETYPE_XML_APP:
            case WIKINDX_MIMETYPE_XML_TEXT:
                $text = $this->readHtml($filename);
            break;
            case WIKINDX_MIMETYPE_MHT_APP:
            case WIKINDX_MIMETYPE_MHT_MUL:
            case WIKINDX_MIMETYPE_MHT_RFC:
                $text = $this->readMht($filename);
            break;
            case WIKINDX_MIMETYPE_ODT:
            case WIKINDX_MIMETYPE_OTT:
                $text = $this->readOdt($filename);
            break;
            case WIKINDX_MIMETYPE_PDF:
            case WIKINDX_MIMETYPE_XPDF:
                $text = $this->readPdf($filename);
            break;
            case WIKINDX_MIMETYPE_RTF_APP:
            case WIKINDX_MIMETYPE_RTF_TEXT:
                $text = $this->readRtf($filename);
            break;
            case WIKINDX_MIMETYPE_TXT:
                switch ($extension)
                {
                    case "csv":
                    case "tsv":
                    case "silk":
                        // Type not handled
                        $text = "";
                    break;
                    case "mht":
                    case "mhtml":
                        $text = $this->readMht($filename);
                    break;
                    default:
                        $text = $this->readText($filename);
                    break;
                }
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
        
        // Replace control, format and separator characters by a single space
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
     * readDocx
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
            
            if ($za->open($filename))
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
     * readOdt
     *
     * cf. https://www.oasis-open.org/standards/#opendocumentv1.2
     *
     * @param string $filename
     *
     * @return string
     */
    private function readOdt($filename)
    {
        $striped_content = "";
        $content = "";
        
        // Extract the content file
        $za = new \ZipArchive();
        
        if ($za->open($filename))
        {
            $content = $za->getFromName("content.xml");
            if ($content === FALSE) $content = "";
        }
        
        // Extract the text part of the body and rudimentary formats major blocks with newlines
        // We assume that the document is well formed and that the tags do not intersect
        $pXML = new \XMLReader();
        
        if ($pXML->XML($content))
        {
            $bExtract = FALSE;
            $bExtractElement = FALSE;
            
            while ($pXML->read())
            {
                // Start extracting at the start of the text of the body
                if ($pXML->nodeType == \XMLReader::ELEMENT && $pXML->name == "office:text")
                {
                    $bExtract = TRUE;
                }
                // Stop extracting at the end of the text of the body
                if ($pXML->nodeType == \XMLReader::END_ELEMENT && $pXML->name == "office:text")
                {
                    $bExtract = FALSE;
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
                    $striped_content .= $pXML->value;
                    if (in_array($pXML->name, ["text:h", "text:p", "text:list", "text:note", "text:numbered-paragraph", "text:ruby"]))
                    {
                        $striped_content .= LF.LF;
                    }
                }
            }
        }
        
        unset($pXML);
        
        return $striped_content;
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
        
        if ($za->open($filename))
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
                        $extension = \FILE\getExtension($path);
                        
                        if ($cte == "quoted-printable")
                        {
                            $file = quoted_printable_decode($file);
                        }
                        elseif ($cte == "base64")
                        {
                            $file = base64_decode($file);
                        }
                        
                        if (file_put_contents($path, $file) !== FALSE)
                        {
                            // Go full circle!!!
                            $text = $this->convertToText($path, $extension);
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
                            }
                            $matches = [];
                            if (preg_match("/Content-Transfer-Encoding:(.+)/ui", $headers, $matches) == 1)
                            {
                                $cte = trim($matches[1]);
                            }
                            $matches = [];
                            if (preg_match("/Content-Type:(.+)/ui", $headers, $matches) == 1)
                            {
                                $mime = trim($matches[1]);
                                
                                $v = explode(";", $mime);
                                if (count($v) == 2)
                                {
                                    $mime = trim($v[0]);
                                    $matches = [];
                                    if (preg_match("/charset=\"(.+)\"/ui", $v[1], $matches) == 1)
                                    {
                                        $charset = trim($matches[1]);
                                    }
                                }
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
        
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTMLFile($filename, LIBXML_NOWARNING | LIBXML_NOERROR);
        
        $elements = $doc->getElementsByTagName('body');
        
        if (!is_null($elements))
        {
            foreach ($elements as $element)
            {
                $nodes = $element->childNodes;
                foreach ($nodes as $node)
                {
                    // Skip blacklisted elements and their content
                    // TODO(LkpPo): 2021-04-30, all blacklisted elements are not skipped because the reading is not recursive descendant!!!
                    if (in_array($node->nodeName, ["applet","colgroup","form","head","img","listener","object","script","style"]))
                    {
                        // Do nothing
                    }
                    else
                    {
                        $content .= $node->nodeValue . LF;
                    }
                }
            }
        }
        
        unset($doc);
        
        return $content;
    }
}
