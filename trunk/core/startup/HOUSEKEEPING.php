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
    /** object */
    private $db;
    /**
     * HOUSEKEEPING
     */
    public function __construct()
    {
        $this->session = FACTORY_SESSION::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->statistics();
        $this->tempStorage();
        $this->cacheAttachments();
    }
    /**
     * Check if any attachments need caching and provide a scrren to built the cache
     */
    public function cacheAttachments()
    {
        // superadmin logging on – caching requires the superadmin to click further
        if ($this->session->getVar("setup_UserId") != WIKINDX_SUPERADMIN_ID || $this->session->getVar("skipCachingAttachments", FALSE)) {
            return;
        }
        
        $dirData = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS]);
        
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "list", "FILETOTEXT.php"]));
        $f2t = new FILETOTEXT();
        list($nbMissingCacheFile, $nbFilesTotal) = $f2t->countMissingCacheAttachment();
        
        if ($nbMissingCacheFile > 0) {
            $this->session->setVar("cache_AttachmentsRemain", $nbMissingCacheFile);
            
            $messages = FACTORY_MESSAGES::getInstance();
            
			if (!$doneCache = $this->session->getVar("cache_AttachmentsDone")) {
				$doneCache = 0;
				$this->session->setVar("cache_AttachmentsDone", 0);
			}
            $pString = \HTML\p($messages->text("misc", "attachmentCache1"));
            $pString .= \HTML\p($messages->text("misc", "attachmentCache2", $nbMissingCacheFile));
            $pString .= \HTML\p($messages->text("misc", "attachmentCache3", $doneCache));
            $pString .= \FORM\formHeader("list_FILETOTEXT_CORE");
            $pString .= \FORM\hidden("method", "checkCache");
            if (function_exists('curl_multi_exec')) {
                if (!$this->session->getVar("cache_AttachmentsRemain")) { // At beginning
                    $checked = 'CHECKED';
                }
                elseif ($this->session->getVar("cache_Curl")) {
                    $checked = 'CHECKED';
                }
                else {
                    $checked = FALSE;
                }
                $pString .= \HTML\p(\FORM\checkbox($messages->text("misc", "attachmentCache4"), "cacheCurl", $checked));
            }
            $value = $this->session->getVar("cache_Limit");
            $pString .= \HTML\p($messages->text("misc", "attachmentCache5", \FORM\textInput(FALSE, "cacheLimit", $value, 3)));
            $pString .= \HTML\p(\FORM\formSubmit($messages->text("submit", "Cache")) . \FORM\formEnd());
            $pString .= \HTML\p(\HTML\a("skip", $messages->text("misc", "attachmentCache6"), htmlentities(WIKINDX_URL_BASE . "/index.php?action=attachments_ATTACHMENTS_CORE&method=skipCaching")));
            GLOBALS::addTplVar('content', $pString);
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        else {
            $this->session->delVar("cache_AttachmentsRemain");
            $this->session->delVar("cache_AttachmentsDone");
        }
    }
    /**
     * Housekeeping: remove any rows in temp_storage older than 3 days
     */
    private function tempStorage()
    {
        $this->db->formatConditions($this->db->dateIntervalCondition(3) . $this->db->greater .
            $this->db->formatFields('tempstorageTimestamp'));
        $this->db->delete('temp_storage');
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
