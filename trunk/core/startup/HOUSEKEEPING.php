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
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "attachments", "ATTACHMENTS.php"]));
        $att = new ATTACHMENTS();
        $att->checkCache();
        return;
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
