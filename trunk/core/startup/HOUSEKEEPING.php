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
     *
     * @param string $upgradeCompleted
     */
    public function __construct($upgradeCompleted)
    {
        $this->session = FACTORY_SESSION::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->statistics();
        $this->tempStorage();
        if ($this->session->getVar("setup_UserId") == WIKINDX_SUPERADMIN_ID)
        { // superadmin logging on – caching requires the superadmin to click further
            $this->cacheAttachments($upgradeCompleted);
        }
    }
    /**
     * Check if any attachments need caching and create them one at a time
     *
     * @param string $upgradeCompleted
     */
    public function cacheAttachments($upgradeCompleted)
    {
        $dirData = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS]);
        $dirCache = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_ATTACHMENTS]);
        
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CORE, "modules", "attachments", "ATTACHMENTS.php"]));
        $att = new ATTACHMENTS();
        
        $mem = ini_get('memory_limit');
        ini_set('memory_limit', '-1'); // NB not always possible to set
        if (ini_get('memory_limit') == -1)
        {
            $maxSize = FALSE;
        }
        elseif (ini_get('memory_limit') >= 129)
        {
            $maxSize = 30000000; // 30MB
        }
        elseif (ini_get('memory_limit') >= 65)
        {
            $maxSize = 15000000; // 15MB
        }
        else
        {
            $maxSize = 5000000; // 5MB
        }
        
        $maxExecTime = ini_get('max_execution_time');
        $maxCount = 1;
        
        $count = 0;
        $size = 0;
        
        $listDataFiles = \FILE\fileInDirToArray($dirData);
        $listCacheFiles = \FILE\fileInDirToArray($dirCache);
        
        $listCacheFilesMissing = array_diff($listDataFiles, $listCacheFiles);
        
        foreach($listCacheFilesMissing as $k => $file)
        {
            $att->refreshCache($file);
            
            $count++;
            $size += filesize(implode(DIRECTORY_SEPARATOR, [$dirData, $file]));
            
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
