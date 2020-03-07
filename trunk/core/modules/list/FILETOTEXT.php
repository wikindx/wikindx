<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
    /** int */
    public $multibyte = 4; // Use setUnicode(TRUE|FALSE)
    /** string */
    public $convertquotes = ENT_QUOTES; // ENT_COMPAT (double-quotes), ENT_QUOTES (Both), ENT_NOQUOTES (None)
    /** boolean */
    public $showprogress = TRUE; // TRUE if you have problems with time-out
    /** string */
    public $decodedtext = '';
    /** array */
    public $readFiles = [];
    /** string */
    private $fileName;

    public function __construct()
    {
        include_once(WIKINDX_DIR_COMPONENT_VENDOR . '/pdftotext/PdfToText.phpclass');
    }

    /**
     * Check files in WIKINDX_DIR_DATA_ATTACHMENTS have been cached (only PDF, DOC, DOCX)
     */
    public function checkCache()
    {
        $db = FACTORY_DB::getInstance();
        $vars = GLOBALS::getVars();
        $session = FACTORY_SESSION::getInstance();
        $attachDir = WIKINDX_DIR_DATA_ATTACHMENTS;
        $cacheDir = WIKINDX_DIR_CACHE_ATTACHMENTS;
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
        $mimeTypes = [WIKINDX_MIMETYPE_PDF, WIKINDX_MIMETYPE_DOCX, WIKINDX_MIMETYPE_DOC];
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
        include_once("core/startup/HOUSEKEEPING.php");
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
        if ($mimeType == WIKINDX_MIMETYPE_DOC) {
            $text = $this->readWord();
        } elseif ($mimeType == WIKINDX_MIMETYPE_DOCX) {
            $text = $this->read_docx();
        } elseif ($mimeType == WIKINDX_MIMETYPE_PDF) {
            $importPDF = new PdfToText($this->fileName, PdfToText::PDFOPT_NO_HYPHENATED_WORDS);
            if ($importPDF->Text) {
                $this->readFiles[$this->fileName] = $importPDF->Text;

                return $importPDF->Text;
            }
        }
        if ($text) {
            $this->readFiles[$this->fileName] = $text;

            return $text;
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

    /**
     * setFilename
     *
     * @param string $filename
     */
    private function setFilename($filename)
    {
        $this->decodedtext = '';
        $this->fileName = $filename;
    }

    /**
     * output
     *
     * @param bool $echo
     *
     * @return string
     */
    private function output($echo = FALSE)
    {
        if ($echo) {
            echo $this->decodedtext;
        } else {
            return $this->decodedtext;
        }
    }

    /**
     * setUnicode
     *
     * @param bool $input
     */
    private function setUnicode($input)
    {
        if ($input == TRUE) {
            $this->multibyte = 4;
        } else {
            $this->multibyte = 2;
        }
    }

    /**
     * decodePDF
     */
    private function decodePDF()
    {
        $infile = @file_get_contents($this->fileName, FILE_BINARY);
        if (empty($infile)) {
            return "";
        }
        $transformations = [];
        $texts = [];
        preg_match_all("#obj[\n|\r](.*)endobj[\n|\r]#ismuU", $infile . "endobj" . CR, $objects);
        $objects = @$objects[1];
        for ($i = 0; $i < count($objects); $i++) {
            $currentObject = $objects[$i];
            @set_time_limit();
            if ($this->showprogress) {
                flush();
                ob_flush();
            }
            if (preg_match("#stream[\n|\r](.*)endstream[\n|\r]#ismuU", $currentObject . "endstream" . CR, $stream)) {
                $stream = ltrim($stream[1]);
                $options = $this->getObjectOptions($currentObject);
                if (!(empty($options["Length1"]) && empty($options["Type"]) && empty($options["Subtype"]))) {
                    continue;
                }
                unset($options["Length"]);
                $data = $this->getDecodedStream($stream, $options);
                if (mb_strlen($data)) {
                    if (preg_match_all("#BT[\n|\r](.*)ET[\n|\r]#ismuU", $data . "ET" . CR, $textContainers)) {
                        $textContainers = @$textContainers[1];
                        $this->getDirtyTexts($texts, $textContainers);
                    } else {
                        $this->getCharTransformations($transformations, $data);
                    }
                }
            }
        }
        $this->decodedtext = $this->getTextUsingTransformations($texts, $transformations);
    }

    /**
     * decodeAsciiHex
     *
     * @param string $input
     *
     * @return string
     */
    private function decodeAsciiHex($input)
    {
        $output = "";
        $isOdd = TRUE;
        $isComment = FALSE;
        for ($i = 0, $codeHigh = -1; $i < mb_strlen($input) && $input[$i] != '>'; $i++) {
            $c = $input[$i];
            if ($isComment) {
                if ($c == '\r' || $c == '\n') {
                    $isComment = FALSE;
                }

                continue;
            }
            switch ($c) {
            case '\0': case '\t': case '\r': case '\f': case '\n': case ' ': break;
            case '%':
                $isComment = TRUE;

            break;
            default:
                $code = hexdec($c);
                if ($code === 0 && $c != '0') {
                    return "";
                }
                if ($isOdd) {
                    $codeHigh = $code;
                } else {
                    $output .= chr($codeHigh * 16 + $code);
                }
                $isOdd = !$isOdd;

            break;
        }
        }
        if ($input[$i] != '>') {
            return "";
        }
        if ($isOdd) {
            $output .= chr($codeHigh * 16);
        }

        return $output;
    }

    /**
     * decodeAscii85
     *
     * @param string $input
     *
     * @return string
     */
    private function decodeAscii85($input)
    {
        $output = "";
        $isComment = FALSE;
        $ords = [];
        for ($i = 0, $state = 0; $i < mb_strlen($input) && $input[$i] != '~'; $i++) {
            $c = $input[$i];
            if ($isComment) {
                if ($c == '\r' || $c == '\n') {
                    $isComment = FALSE;
                }

                continue;
            }
            if (($c == '\0') || ($c == '\t') || ($c == '\r') || ($c == '\f') || ($c == '\n') || ($c == ' ')) {
                continue;
            }
            if ($c == '%') {
                $isComment = TRUE;

                continue;
            }
            if ($c == 'z' && $state === 0) {
                $output .= str_repeat(chr(0), 4);

                continue;
            }
            if ($c < '!' || $c > 'u') {
                return "";
            }
            $code = ord($input[$i]) & 0xff;
            $ords[$state++] = $code - ord('!');
            if ($state == 5) {
                $state = 0;
                for ($sum = 0, $j = 0; $j < 5; $j++) {
                    $sum = $sum * 85 + $ords[$j];
                }
                for ($j = 3; $j >= 0; $j--) {
                    $output .= chr($sum >> ($j * 8));
                }
            }
        }
        if ($state === 1) {
            return "";
        } elseif ($state > 1) {
            for ($i = 0, $sum = 0; $i < $state; $i++) {
                $sum += ($ords[$i] + ($i == $state - 1)) * pow(85, 4 - $i);
            }
            for ($i = 0; $i < $state - 1; $i++) {
                try {
                    if (FALSE == ($o = chr($sum >> ((3 - $i) * 8)))) {
                        throw new Exception('Error');
                    }
                    $output .= $o;
                } catch (Exception $e) { /*Dont do anything*/
                }
            }
        }

        return $output;
    }

    /**
     * gzuncompress
     *
     * @param mixed $data
     */
    private function decodeFlate($data)
    {
        return @gzuncompress($data);
    }

    /**
     * getObjectOptions
     *
     * @param string $object
     *
     * @return array
     */
    private function getObjectOptions($object)
    {
        $options = [];
        if (preg_match("#<<(.*)>>#ismuU", $object, $options)) {
            $options = UTF8::mb_explode("/", $options[1]);
            @array_shift($options);
            $o = [];
            for ($j = 0; $j < @count($options); $j++) {
                $options[$j] = preg_replace("#\\s+#u", " ", trim($options[$j]));
                if (mb_strpos($options[$j], " ") !== FALSE) {
                    $parts = UTF8::mb_explode(" ", $options[$j]);
                    $o[$parts[0]] = $parts[1];
                } else {
                    $o[$options[$j]] = TRUE;
                }
            }
            $options = $o;
            unset($o);
        }

        return $options;
    }

    /**
     * getDecodedStream
     *
     * @param string $stream
     * @param array $options
     *
     * @return string
     */
    private function getDecodedStream($stream, $options)
    {
        $data = "";
        if (empty($options["Filter"])) {
            $data = $stream;
        } else {
            $length = !empty($options["Length"]) ? $options["Length"] : mb_strlen($stream);
            $_stream = mb_substr($stream, 0, $length);

            foreach ($options as $key => $value) {
                if ($key == "ASCIIHexDecode") {
                    $_stream = $this->decodeAsciiHex($_stream);
                } elseif ($key == "ASCII85Decode") {
                    $_stream = $this->decodeAscii85($_stream);
                } elseif ($key == "FlateDecode") {
                    $_stream = $this->decodeFlate($_stream);
                } elseif ($key == "Crypt") { // TO DO
                }
            }
            $data = $_stream;
        }

        return $data;
    }

    /**
     * getDirtyTexts
     *
     * @param string $texts
     * @param array $textContainers
     */
    private function getDirtyTexts(&$texts, $textContainers)
    {
        for ($j = 0; $j < count($textContainers); $j++) {
            if (preg_match_all("#\\[(.*)\\]\\s*TJ[\n|\r]#ismuU", $textContainers[$j], $parts)) {
                $texts = array_merge($texts, [@implode('', $parts[1])]);
            } elseif (preg_match_all("#T[d|w|m|f]\\s*(\\(.*\\))\\s*Tj[\n|\r]#ismuU", $textContainers[$j], $parts)) {
                $texts = array_merge($texts, [@implode('', $parts[1])]);
            } elseif (preg_match_all("#T[d|w|m|f]\\s*(\\[.*\\])\\s*Tj[\n|\r]#ismuU", $textContainers[$j], $parts)) {
                $texts = array_merge($texts, [@implode('', $parts[1])]);
            }
        }
    }

    /**
     * getCharTransformations
     *
     * @param array $transformations
     * @param string $stream
     */
    private function getCharTransformations(&$transformations, $stream)
    {
        preg_match_all("#([0-9]+)\\s+beginbfchar(.*)endbfchar#ismuU", $stream, $chars, PREG_SET_ORDER);
        preg_match_all("#([0-9]+)\\s+beginbfrange(.*)endbfrange#ismuU", $stream, $ranges, PREG_SET_ORDER);
        for ($j = 0; $j < count($chars); $j++) {
            $count = $chars[$j][1];
            $current = UTF8::mb_explode("\n", trim($chars[$j][2]));
            for ($k = 0; $k < $count && $k < count($current); $k++) {
                if (preg_match("#<([0-9a-f]{2,4})>\\s+<([0-9a-f]{4,512})>#uis", trim($current[$k]), $map)) {
                    $transformations[UTF8::mb_str_pad($map[1], 4, "0")] = $map[2];
                }
            }
        }
        for ($j = 0; $j < count($ranges); $j++) {
            $count = $ranges[$j][1];
            $current = UTF8::mb_explode("\n", trim($ranges[$j][2]));
            for ($k = 0; $k < $count && $k < count($current); $k++) {
                if (preg_match("#<([0-9a-f]{4})>\\s+<([0-9a-f]{4})>\\s+<([0-9a-f]{4})>#uis", trim($current[$k]), $map)) {
                    $from = hexdec($map[1]);
                    $to = hexdec($map[2]);
                    $_from = hexdec($map[3]);
                    for ($m = $from, $n = 0; $m <= $to; $m++, $n++) {
                        $transformations[sprintf("%04X", $m)] = sprintf("%04X", $_from + $n);
                    }
                } elseif (preg_match("#<([0-9a-f]{4})>\\s+<([0-9a-f]{4})>\\s+\\[(.*)\\]#ismuU", trim($current[$k]), $map)) {
                    $from = hexdec($map[1]);
                    $to = hexdec($map[2]);
                    $parts = preg_split("#\\s+#u", trim($map[3]));

                    for ($m = $from, $n = 0; $m <= $to && $n < count($parts); $m++, $n++) {
                        $transformations[sprintf("%04X", $m)] = sprintf("%04X", hexdec($parts[$n]));
                    }
                }
            }
        }
    }

    /**
     * getTextUsingTransformations
     *
     * @param array $texts
     * @param array $transformations
     *
     * @return string
     */
    private function getTextUsingTransformations($texts, $transformations)
    {
        $document = "";
        for ($i = 0; $i < count($texts); $i++) {
            $isHex = FALSE;
            $isPlain = FALSE;
            $hex = "";
            $plain = "";
            for ($j = 0; $j < mb_strlen($texts[$i]); $j++) {
                $c = $texts[$i][$j];
                switch ($c) {
                case "<":
                    $hex = "";
                    $isHex = TRUE;
                    $isPlain = FALSE;

                break;
                case ">":
                    $hexs = str_split($hex, $this->multibyte); // 2 or 4 (UTF8 or ISO)
                    for ($k = 0; $k < count($hexs); $k++) {
                        $chex = UTF8::mb_str_pad($hexs[$k], 4, "0"); // Add tailing zero
                        if (isset($transformations[$chex])) {
                            $chex = $transformations[$chex];
                        }
                        $document .= html_entity_decode("&#x" . $chex . ";");
                    }
                    $isHex = FALSE;

                break;
                case "(":
                    $plain = "";
                    $isPlain = TRUE;
                    $isHex = FALSE;

                break;
                case ")":
                    $document .= $plain;
                    $isPlain = FALSE;

                break;
                case "\\":
                    $c2 = $texts[$i][$j + 1];
                    if (in_array($c2, ["\\", "(", ")"])) {
                        $plain .= $c2;
                    } elseif ($c2 == "n") {
                        $plain .= '\n';
                    } elseif ($c2 == "r") {
                        $plain .= '\r';
                    } elseif ($c2 == "t") {
                        $plain .= '\t';
                    } elseif ($c2 == "b") {
                        $plain .= '\b';
                    } elseif ($c2 == "f") {
                        $plain .= '\f';
                    } elseif ($c2 >= '0' && $c2 <= '9') {
                        $oct = preg_replace("#[^0-9]#u", "", mb_substr($texts[$i], $j + 1, 3));
                        $j += mb_strlen($oct) - 1;
                        $plain .= html_entity_decode("&#" . octdec($oct) . ";", $this->convertquotes);
                    }
                    $j++;

                break;
                default:
                    if ($isHex) {
                        $hex .= $c;
                    } elseif ($isPlain) {
                        $plain .= $c;
                    }

                break;
            }
            }
            $document .= "\n";
        }

        return $document;
    }
}
