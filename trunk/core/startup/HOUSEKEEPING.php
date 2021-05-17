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
        $this->custom_session_gc();
        $this->statistics();
        $this->tempStorage();
        $this->cacheAttachments();
    }
    /**
     * Check if any attachments need caching and provide a screen to built the cache
     */
    public function cacheAttachments()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "list", "FILETOTEXT.php"]));
        $f2t = new FILETOTEXT();
        $f2t->checkCache2();
        return;
        
        // superadmin logging on – caching requires the superadmin to click further
        if ($this->session->getVar("setup_UserId") != WIKINDX_SUPERADMIN_ID || $this->session->getVar("skipCachingAttachments", FALSE)) {
            return;
        }
        
        $dirData = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS]);
        
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "list", "FILETOTEXT.php"]));
        $f2t = new FILETOTEXT();
        list($nbMissingCacheFile, $nbFilesTotal) = $f2t->countMissingCacheAttachment();
        
        if ($nbMissingCacheFile > 0) {
            $messages = FACTORY_MESSAGES::getInstance();
            
            $pString = \HTML\p($messages->text("misc", "attachmentCache1"));
            $pString .= \HTML\p($messages->text("misc", "attachmentCache2", $nbMissingCacheFile));
            $pString .= \HTML\p($messages->text("misc", "attachmentCache3", $nbFilesTotal - $nbMissingCacheFile));
            $pString .= \FORM\formHeader("list_FILETOTEXT_CORE");
            $pString .= \FORM\hidden("method", "checkCache");
            
            $value = $this->session->getVar("cache_Limit");
            $pString .= \HTML\p($messages->text("misc", "attachmentCache5", \FORM\textInput(FALSE, "cacheLimit", $value, 3)));
            $pString .= \HTML\p(\FORM\formSubmit($messages->text("submit", "Cache")) . \FORM\formEnd());
            $pString .= \HTML\p(\HTML\a("skip", $messages->text("misc", "attachmentCache6"), htmlentities(WIKINDX_URL_BASE . "/index.php?action=attachments_ATTACHMENTS_CORE&method=skipCaching")));
            GLOBALS::addTplVar('content', $pString);
            FACTORY_CLOSENOMENU::getInstance(); // die
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
    /**
     * WIKINDX custom Garbage Collector
     *
     * Call the session_gc() routine of PHP at the frequency
     * defined by WIKINDX_SESSION_GC_FREQUENCY instead of the PHP default GC.
     *
     * The custom session handler collects all expired sessions.
     */
    private function custom_session_gc()
    {
        $bExecGC = $this->db->queryFetchFirstField("
            SELECT EXISTS(
                SELECT 1
                FROM config
                WHERE
                    configName = 'configSessionGCLastExecTimestamp'
                    AND DATE_ADD(
                        FROM_UNIXTIME(configInt),
                        INTERVAL " . WIKINDX_SESSION_GC_FREQUENCY . " SECOND
                    ) < UTC_TIMESTAMP()
            );
		");
		
		if ($bExecGC)
		{
		    $this->db->formatConditions(["configName" => "configSessionGCLastExecTimestamp"]);
		    $this->db->update("config", ["configInt"  => WIKINDX_SESSION_GC_LASTEXEC_TIMESTAMP_DEFAULT]);
		    
		    session_gc();
		}
    }
}
