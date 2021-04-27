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
 *	FILES export class
 */
class FILES
{
    private $db;
    private $vars;
    private $errors;
    private $messages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
    }
    /**
     * listFiles
     *
     * @param false|string $message
     *
     * @return string
     */
    public function listFiles($message = FALSE)
    {
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
            else
            {
                GLOBALS::addTplVar('content', $this->errors->text('file', "read"));
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
                $pString .= HTML\a("link", $key, "index.php?action=export_FILES_CORE&method=downloadFile" .
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
            switch (\FILE\getExtension($filepath)) {
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
}