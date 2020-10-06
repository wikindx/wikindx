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
     * Store data
     *
     * @param class $db
     * @param string $uuid
     * @param array $data
     */
    function store($db, $uuid, $data)
    {
    	$data = serialize($data);
    	$db->insert('temp_storage', ['tempstorageId', 'tempstorageData'], [$uuid, $data]);
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
     * Merge data – assumes unique keys
     *
     * @param class $db
     * @param string $uuid
     * @param array $newData
     */
    function merge($db, $uuid, $newData)
    {
    	$db->formatConditions(['tempstorageId' => $uuid]);
		$oldData = $db->selectFirstField('temp_storage', 'tempstorageData');
		$array = array_merge(unserialize($oldData), $newData);
    	$data = serialize($array);
    	$db->formatConditions(['tempstorageId' => $uuid]);
    	$db->update('temp_storage', ['tempstorageData' => $data]);
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
