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
        
        $errorDisplay = ini_get('display_errors');
        ini_set('display_errors', FALSE);
        
        $importPDF = new PdfToText();
        
        // Note:
        // MaxGlobalExecutionTime property and PDFOPT_ENFORCE_GLOBAL_EXECUTION_TIME option are broken
        // use only one instance of the class by file parsed
        
        // PDFOPT_NO_HYPHENATED_WORDS: tries to join back hyphenated words into a single word
        // PDFOPT_ENFORCE_EXECUTION_TIME: throw a PdfToTextTimeout exception if the extraction run more than MaxExecutionTime
        $importPDF->Options = PdfToText::PDFOPT_NO_HYPHENATED_WORDS | PdfToText::PDFOPT_ENFORCE_EXECUTION_TIME;
        
        // Will consume all available runtime except 2 seconds (if this point is reached in less than 2 seconds)
        $importPDF->MaxExecutionTime = ini_get('max_execution_time') - GLOBALS::getPageElapsedTime() - 3;
        
        
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
    private function readDocx2($filename)
    {
        $striped_content = "";
        $content = "";
        
        $za = new \ZipArchive();
        
        if ($za->open($filename, \ZipArchive::RDONLY))
        {
            $content = $za->getFromName("word/document.xml");
            if ($content === FALSE) $content = "";
        }
        
        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', LF, $content);
        $striped_content = strip_tags($content);
        $striped_content = html_entity_decode($striped_content, ENT_QUOTES | ENT_XML1, 'UTF-8');
        
        return $striped_content;
    }
}
