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
 * Methods to do with the temp_storage table
 *
 * @package wikindx\core\libs\TEMPSTORAGE
 */
namespace TEMPSTORAGE
{
    /**
     * Get UUID from database
     *
     * @param class $db
     *
     * @return string $uuid
     */
    function getUuid($db)
    {
		return $db->queryFetchFirstField("SELECT UUID()");
    }
    
    /**
     * Store/merge data
     *
     * @param class $db
     * @param string $uuid
     * @param array $data
     */
    function store($db, $uuid, $data)
    {
    // Check for existence
    	$db->formatConditions(['tempstorageId' => $uuid]);
		$oldData = $db->selectFirstField('temp_storage', 'tempstorageData');
    	if (!$oldData) {
    		$data = serialize($data);
	    	$db->insert('temp_storage', ['tempstorageId', 'tempstorageData'], [$uuid, $data]);
	    } else {
			$array = array_merge(unserialize($oldData), $data);
			$newData = serialize($array);
			$db->formatConditions(['tempstorageId' => $uuid]);
			$db->update('temp_storage', ['tempstorageData' => $newData]);
	    }
    }
    
    /**
     * Fetch data
     *
     * @param class $db
     * @param string $uuid
     *
     * @return array $data
     */
    function fetch($db, $uuid)
    {
    	$db->formatConditions(['tempstorageId' => $uuid]);
		$data = $db->selectFirstField('temp_storage', 'tempstorageData');
		return unserialize($data);
    }
    
    /**
     * Delete row
     *
     * @param class $db
     * @param string $uuid
     */
    function delete($db, $uuid)
    {
    	$db->formatConditions(['tempstorageId' => $uuid]);
		$db->delete('temp_storage');
    }
}
