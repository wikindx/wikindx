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
    /** string */
    public $decodedtext = '';
    /** array */
    public $readFiles = [];
    /** string */
    private $fileName;

    public function __construct()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR, "pdftotext", "PdfToText.phpclass"]));
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
        $maxExecTime = ini_get('max_execution_time');
        ini_set('memory_limit', '-1'); // NB not always possible to set
        ini_set('max_execution_time', '-1'); // NB not always possible to set
        // Turn error display off so that errors from PdfToText don't get written to screen (still written to the cache files)
        $errorDisplay = ini_get('display_errors');
        ini_set('display_errors', FALSE);
        if (array_key_exists('cacheCurl', $vars) && ($vars['cacheCurl'] == 'on')) {
            $session->setVar("cache_Curl", TRUE);
            if (function_exists('curl_multi_exec')) {
                $ch = [];
                $mh = curl_multi_init();
                $script = $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
                $curlExists = TRUE;
            } else {
                $curlExists = FALSE;
            }
        } else {
            $session->delVar("cache_Curl");
            $curlExists = FALSE;
        }
        // Attempting to avoid timeouts if max execution time cannot be set. This is done on a trial and error basis.
        if (ini_get('memory_limit') == -1) { // unlimited
            $maxCount = FALSE;
            $maxSize = FALSE;
        } elseif (ini_get('memory_limit') >= 129) {
            $maxCount = 30;
            $maxSize = 30000000; // 30MB
        } elseif (ini_get('memory_limit') >= 65) {
            $maxCount = 20;
            $maxSize = 15000000; // 15MB
        } else {
            $maxCount = 10;
            $maxSize = 5000000; // 5MB
        }
        $input = FALSE;
        if (array_key_exists('cacheLimit', $vars)) {
            $input = trim($vars['cacheLimit']);
            if (is_numeric($input) && is_int($input + 0)) { // include cast to number
                $maxCount = $input;
                $session->setVar("cache_Limit", $input);
            }
        }
        if (!$input) {
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
        while ($row = $db->fetchRow($resultset)) {
            $f = $row['resourceattachmentsHashFilename'];
            $fileName = $attachDir . DIRECTORY_SEPARATOR . $f;
            $fileNameCache = $cacheDir . DIRECTORY_SEPARATOR . $f;
            if (!file_exists($fileName) || (file_exists($fileNameCache) && filemtime($fileNameCache) > filemtime($fileName))) {
                continue; // already cached
            } elseif ($curlExists) {
                $curlTarget = $script . '?' .
                'action=curl_CURL_CORE' .
                '&method=attachmentCache' .
                '&file=' . urlencode($fileName) .
                '&fileType=' . urlencode($row['resourceattachmentsFileType']) .
                '&id=' . $f;
                $ch_x = curl_init($curlTarget);
                $ch[$row['resourceattachmentsHashFilename']] = $ch_x;
                curl_setopt($ch_x, CURLOPT_RETURNTRANSFER, TRUE);
                // Get the headers too
                curl_setopt($ch_x, CURLOPT_HEADER, TRUE);
                curl_setopt($ch_x, CURLOPT_TIMEOUT, ini_get('max_execution_time'));
                curl_multi_add_handle($mh, $ch_x);
            } else {
                try {
                    file_put_contents($fileNameCache, $this->convertToText($fileName, $row['resourceattachmentsFileType']));
                } catch (Exception $e) {
                    file_put_contents($fileNameCache, '');
                }
            }
            ++$count;
            $size += $row['resourceattachmentsFileSize'];
            if ($maxCount) {
                if ($count >= $maxCount) {
                    break;
                }
            }
            if ($maxSize) {
                if ($size >= $maxSize) {
                    break;
                }
            }
        }
        if ($curlExists) {
            $running = NULL;
            do {
                curl_multi_exec($mh, $running);
            } while ($running);
            foreach ($ch as $ch_x) {
                $return = curl_multi_getcontent($ch_x);
                curl_multi_remove_handle($mh, $ch_x);
                curl_close($ch_x);

                // Identify the file parsed with its custom header 'resourceattachmentsHashFilename'
                // This is mandatory because the output of PdfToText could be altered at byte level
                $split = UTF8::mb_explode("\r\n\r\n", $return, 2);
                if (count($split) == 2) {
                    $headers = $split[0];
                    $body = $split[1];

                    // Split headers / body
                    $headers = UTF8::mb_explode("\r\n", $headers);
                    foreach ($headers as $h) {
                        // Split each header in key / value
                        $h = UTF8::mb_explode(":", $h);
                        if (count($split) == 2) {
                            // Identify the file parsed
                            if ($h[0] == 'resourceattachmentsHashFilename') {
                                $texts[trim($h[1])] = trim($body);

                                try {
                                    file_put_contents($cacheDir . DIRECTORY_SEPARATOR . trim($h[1]), $texts[trim($h[1])]);
                                } catch (Exception $e) {
                                    file_put_contents($cacheDir . DIRECTORY_SEPARATOR . trim($h[1]), '');
                                }
                            }
                        }
                    }
                }
            }
            curl_multi_close($mh);
        }
        $cacheDirFiles = scandir($cacheDir);
        foreach ($cacheDirFiles as $key => $value) {
            if (strpos($value, '.') === 0) {
                unset($cacheDirFiles[$key]);
            }
        }
        $session->setVar("cache_Attachments", count($cacheDirFiles));
        ini_set('display_errors', $errorDisplay);
        ini_set('memory_limit', $mem);
        ini_set('max_execution_time', $maxExecTime);
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "startup", "HOUSEKEEPING.php"]));
        $hk = new HOUSEKEEPING(FALSE);
    }

    /**
     * convertToText
     *
     * @param mixed $filename
     * @param mixed $mimeType
     *
     * @return false|string
     */
    public function convertToText($filename, $mimeType)
    {
        $this->fileName = $filename;
        if (isset($this->fileName) && !file_exists($this->fileName)) {
            return FALSE;
        }
        if (array_key_exists($this->fileName, $this->readFiles)) {
            return $this->readFiles[$this->fileName];
        }
        if ($mimeType == WIKINDX_MIMETYPE_TXT) {
            $text = $this->readText();
        } elseif ($mimeType == WIKINDX_MIMETYPE_DOC) {
            $text = $this->readWord();
        } elseif ($mimeType == WIKINDX_MIMETYPE_DOCX) {
            $text = $this->read_docx();
        } elseif ($mimeType == WIKINDX_MIMETYPE_PDF) {
        	ini_set('memory_limit', '-1'); // PDF objects can be large – memory is reset at the next script
            $importPDF = new PdfToText($this->fileName, PdfToText::PDFOPT_NO_HYPHENATED_WORDS);
            if ($importPDF->Text) {
                $this->readFiles[$this->fileName] = $importPDF->Text;

                return $importPDF->Text;
            }
            $text = FALSE;
        }
        if ($text) {
            $this->readFiles[$this->fileName] = $text;

            return $text;
        } else {
            return FALSE;
        }
    }
    /**
     * readText
     *
     * @return false|string
     */
    private function readText()
    {
        if (file_exists($this->fileName)) {
            if (($text = file_get_contents($this->fileName)) !== FALSE) {
                return $text;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
    /**
     * readWord
     *
     * @return false|string
     */
    private function readWord()
    {
        if (file_exists($this->fileName)) {
            if (($fh = fopen($this->fileName, 'r')) !== FALSE) {
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
                if ($textLength <= 0) {
                    return FALSE;
                }
                $extracted_plaintext = fread($fh, $textLength);
                fclose($fh);

                return utf8_encode($extracted_plaintext);
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
    /**
     * read_docx
     *
     * @return false|string
     */
    private function read_docx()
    {
        $striped_content = '';
        $content = '';
        $zip = zip_open($this->fileName);
        if (!$zip || is_numeric($zip)) {
            return FALSE;
        }
        while ($zip_entry = zip_read($zip)) {
            if (zip_entry_open($zip, $zip_entry) == FALSE) {
                continue;
            }
            if (zip_entry_name($zip_entry) != "word/document.xml") {
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
