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
 * HOUSEKEEPING
 *
 * Housekeeping tasks on startup
 *
 * @package wikindx\core\startup
 */
class HOUSEKEEPING
{
    /** object */
    private $session;
    /**
     * HOUSEKEEPING
     *
     * @param string $upgradeCompleted
     */
    public function __construct($upgradeCompleted)
    {
        $this->session = FACTORY_SESSION::getInstance();
        $this->statistics();
        if ($this->session->getVar("setup_UserId") == WIKINDX_SUPERADMIN_ID) { // superadmin logging on – caching requires the superadmin to click further
            $this->cacheAttachments($upgradeCompleted);
        }
    }
    /**
     * Check if any attachments need caching
     *
     * @param string $upgradeCompleted
     */
    public function cacheAttachments($upgradeCompleted)
    {
        $db = FACTORY_DB::getInstance();
        $messages = FACTORY_MESSAGES::getInstance();
        $count = 0;
        $attachDir = WIKINDX_DIR_DATA_ATTACHMENTS;
        $cacheDir = WIKINDX_DIR_CACHE_ATTACHMENTS;
        $cacheDirFiles = scandir($cacheDir);
        foreach ($cacheDirFiles as $key => $value) {
            if (strpos($value, '.') === 0) {
                unset($cacheDirFiles[$key]);
            }
        }
        $this->session->setVar("cache_Attachments", count($cacheDirFiles));
        $mimeTypes = [WIKINDX_MIMETYPE_PDF, WIKINDX_MIMETYPE_DOCX, WIKINDX_MIMETYPE_DOC];
        $db->formatConditionsOneField($mimeTypes, 'resourceattachmentsFileType');
        $resultset = $db->select('resource_attachments', ['resourceattachmentsHashFilename']);
        while ($row = $db->fetchRow($resultset)) {
            $f = $row['resourceattachmentsHashFilename'];
            $fileName = $attachDir . DIRECTORY_SEPARATOR . $f;
            $fileNameCache = $cacheDir . DIRECTORY_SEPARATOR . $f;
            if (!file_exists($fileName) || (file_exists($fileNameCache) && filemtime($fileNameCache) >= filemtime($fileName))) {
                continue;
            }
            ++$count;
        }
        if ($count) {
            $pString = \HTML\p($messages->text("misc", "attachmentCache1"));
            $pString .= \HTML\p($messages->text("misc", "attachmentCache2", $count));
            $lastCache = $this->session->getVar("cache_Attachments");
            if ($lastCache) {
                $pString .= \HTML\p($messages->text("misc", "attachmentCache3", $lastCache));
            }
            $pString .= \FORM\formHeader("list_FILETOTEXT_CORE");
            $pString .= \FORM\hidden("method", "checkCache");
            if (function_exists('curl_multi_exec')) {
                if (!$this->session->getVar("cache_Attachments")) { // At beginning
                    $checked = 'CHECKED';
                } elseif ($this->session->getVar("cache_Curl")) {
                    $checked = 'CHECKED';
                } else {
                    $checked = FALSE;
                }
                $pString .= \HTML\p(\FORM\checkbox($messages->text("misc", "attachmentCache4"), "cacheCurl", $checked));
            }
            $value = $this->session->getVar("cache_Limit");
            $pString .= \HTML\p($messages->text("misc", "attachmentCache5", \FORM\textInput(FALSE, "cacheLimit", $value, 3)));
            $pString .= \HTML\p(\FORM\formSubmit($messages->text("submit", "Cache")) . \FORM\formEnd());
            $pString .= \HTML\p(\HTML\a("skip", $messages->text("misc", "attachmentCache6"), htmlentities("index.php?action=skipCaching")));
            GLOBALS::addTplVar('content', $pString);
            FACTORY_CLOSENOMENU::getInstance(); // die
        } else {
            if ($upgradeCompleted == TRUE) {
                include_once('core/startup/INSTALLMESSAGES.php');
                $installMessages = new INSTALLMESSAGES;
                $message = \HTML\p($installMessages->text("upgradeDBSuccess"), "success", "center");
                if (WIKINDX_INTERNAL_VERSION >= 5.3) {
                    $message .= \HTML\p($installMessages->text("upgradeDBv5.3"), "success", "center");
                }
            } else {
                $message = '';
            }
            include_once("core/display/FRONT.php");
            $front = new FRONT($message); // __construct() runs on autopilot
            FACTORY_CLOSE::getInstance();
        }
    }
    /**
     * Check if statistics need compiling and emailing out to registered users.
     */
    private function statistics()
    {
        $stats = FACTORY_STATISTICS::getInstance();
        $stats->runCompile();
    }
}
