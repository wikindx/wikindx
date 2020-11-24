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
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CORE, "modules", "attachments", "ATTACHMENTS.php"]));
    }

    /**
     * Check files in WIKINDX_DIR_DATA_ATTACHMENTS have been cached (only PDF, DOC, DOCX)
     */
    public function checkCache()
    {
        $db = FACTORY_DB::getInstance();
        $vars = GLOBALS::getVars();
        $session = FACTORY_SESSION::getInstance();
        $attachDir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS]);
        $cacheDir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_ATTACHMENTS]);
        $mem = ini_get('memory_limit');
        ini_set('memory_limit', '-1'); // NB not always possible to set
        $maxExecTime = ini_get('max_execution_time');
        // Turn error display off so that errors from PdfToText don't get written to screen (still written to the cache files)
        $errorDisplay = ini_get('display_errors');
        ini_set('display_errors', FALSE);
        
        // Attempting to avoid timeouts if max execution time cannot be set. This is done on a trial and error basis.
        if (ini_get('memory_limit') == -1)
        { // unlimited
            $maxCount = FALSE;
            $maxSize = FALSE;
        }
        elseif (ini_get('memory_limit') >= 129)
        {
            $maxCount = 30;
            $maxSize = 30000000; // 30MB
        }
        elseif (ini_get('memory_limit') >= 65)
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
        $mimeTypes = [WIKINDX_MIMETYPE_PDF, WIKINDX_MIMETYPE_DOCX, WIKINDX_MIMETYPE_DOC, WIKINDX_MIMETYPE_TXT];
        $db->formatConditionsOneField($mimeTypes, 'resourceattachmentsFileType');
        $resultset = $db->select(
            'resource_attachments',
            ['resourceattachmentsResourceId', 'resourceattachmentsHashFilename', 'resourceattachmentsFileType', 'resourceattachmentsFileSize']
        );
        while ($row = $db->fetchRow($resultset))
        {
            $att = new ATTACHMENTS();
            $att->refreshCache($row['resourceattachmentsHashFilename'], $row['resourceattachmentsHashFilename']);
            
            ++$count;
            $size += $row['resourceattachmentsFileSize'];
            
            // Stop if there is less than a second left
            if ($maxExecTime - GLOBALS::getPageElapsedTime() <= 1)
            {
                break;
            }
            
            // Stop if the maximum number of attachments has been reached
            if ($maxCount)
            {
                if ($count >= $maxCount)
                {
                    break;
                }
            }
            
            // Stop if all allocated memory has been consumed
            if ($maxSize)
            {
                if ($size >= $maxSize)
                {
                    break;
                }
            }
        }
        
        $cacheDirFiles = scandir($cacheDir);
        foreach ($cacheDirFiles as $key => $value)
        {
            if (strpos($value, '.') === 0)
            {
                unset($cacheDirFiles[$key]);
            }
        }
        $session->setVar("cache_Attachments", count($cacheDirFiles));
        ini_set('display_errors', $errorDisplay);
        ini_set('memory_limit', $mem);
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "startup", "HOUSEKEEPING.php"]));
        $hk = new HOUSEKEEPING(FALSE);
    }

    /**
     * convertToText
     *
     * @param string $filename
     *
     * @return string
     */
    public function convertToText($filename)
    {
        // Retrieve the mime type of the original file
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filename);
        
        // Convert to text with a specific function by mimetype and return it
        switch ($mimeType) {
            case WIKINDX_MIMETYPE_DOC:
                $text = $this->readWord($filename);
            break;
            case WIKINDX_MIMETYPE_DOCX:
                $text = $this->readDocx($filename);
            break;
            case WIKINDX_MIMETYPE_PDF:
                $text = $this->readPdf($filename);
            break;
            case WIKINDX_MIMETYPE_TXT:
                $text = $this->readText($filename);
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
        // PDF objects can be large – memory is reset at the next script
        ini_set('memory_limit', '-1');
        $importPDF = new PdfToText();
        
        // Note:
        // MaxGlobalExecutionTime property and PDFOPT_ENFORCE_GLOBAL_EXECUTION_TIME option are broken
        // use only one instance of the class by file parsed
        
        // PDFOPT_NO_HYPHENATED_WORDS: tries to join back hyphenated words into a single word
        // PDFOPT_ENFORCE_EXECUTION_TIME: throw a PdfToTextTimeout exception if the extraction run more than MaxExecutionTime
        $importPDF->Options = PdfToText::PDFOPT_NO_HYPHENATED_WORDS | PdfToText::PDFOPT_ENFORCE_EXECUTION_TIME;
        
        // Will consume all available runtime except 2 seconds (if this point is reached in less than 2 seconds)
        $importPDF->MaxExecutionTime = ini_get('max_execution_time') - GLOBALS::getPageElapsedTime() - 2;
        
        
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
     * @param string $filename
     *
     * @return string
     */
    private function readDocx($filename)
    {
        $striped_content = '';
        $content = '';
        $zip = zip_open($filename);
        if (!$zip || is_numeric($zip))
        {
            return FALSE;
        }
        while ($zip_entry = zip_read($zip))
        {
            if (zip_entry_open($zip, $zip_entry) == FALSE)
            {
                continue;
            }
            if (zip_entry_name($zip_entry) != "word/document.xml")
            {
                continue;
            }
            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
            zip_entry_close($zip_entry);
        }
        zip_close($zip);
        unset($zip);
        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', CR . LF, $content);
        $striped_content = strip_tags($content);

        return $striped_content;
    }
}
