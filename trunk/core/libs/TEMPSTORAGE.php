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
     *
     * @return bool. FALSE if $uuid not set or $data empty, otherwise TRUE
     */
    function store($db, $uuid, $data)
    {
        if (!$uuid || empty($data))
        {
            return FALSE;
        }
        // Check for existence
        $db->formatConditions(['tempstorageId' => $uuid]);
        $oldData = $db->selectFirstField('temp_storage', 'tempstorageData');
        if (!$oldData)
        {
            $data = serialize($data);
            $db->insert('temp_storage', ['tempstorageId', 'tempstorageData'], [$uuid, $data]);
        }
        else
        {
            $array = array_merge(unserialize($oldData), $data);
            $newData = serialize($array);
            $db->formatConditions(['tempstorageId' => $uuid]);
            $db->update('temp_storage', ['tempstorageData' => $newData]);
            $db->formatConditions(['tempstorageId' => $uuid]);
            $db->updateTimestamp('temp_storage', ['tempStorageTimestamp' => '']); // default is CURRENT_TIMESTAMP
        }

        return TRUE;
    }
    
    /**
     * Check a row exists
     *
     * @param class $db
     * @param string $uuid
     *
     * @return bool
     */
    function exists($db, $uuid)
    {
        $db->formatConditions(['tempstorageId' => $uuid]);
        $row = $db->selectFirstField('temp_storage', 'tempstorageData');
        if (empty($row))
        {
            return FALSE;
        }

        return TRUE;
    }
    
    /**
     * Fetch data array
     *
     * @param class $db
     * @param string $uuid
     *
     * @return array $data
     */
    function fetch($db, $uuid)
    {
        $db->formatConditions(['tempstorageId' => $uuid]);
        $row = $db->selectFirstField('temp_storage', 'tempstorageData');
        $data = unserialize($row);
        if (empty($row))
        {
            return FALSE;
        }

        return $data;
    }
    
    /**
     * Fetch one array element
     *
     * @param class $db
     * @param string $uuid
     * @param string $key
     *
     * @return mixed $data FALSE if $key is not in row
     */
    function fetchOne($db, $uuid, $key)
    {
        $db->formatConditions(['tempstorageId' => $uuid]);
        $row = $db->selectFirstField('temp_storage', 'tempstorageData');
        $data = unserialize($row);
        if (is_bool($data))
        {
            return FALSE;
        }
        if (!array_key_exists($key, $data))
        {
            return FALSE;
        }

        return $data[$key];
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
     
    /**
     * Delete keys from the row data array
     *
     * @param class $db
     * @param string $uuid
     * @param array $deleteKeys
     *
     * @return bool. FALSE if row doesn't exist, otherwise TRUE
     */
    function deleteKeys($db, $uuid, $deleteKeys)
    {
        // Check for existence
        $db->formatConditions(['tempstorageId' => $uuid]);
        $oldData = $db->selectFirstField('temp_storage', 'tempstorageData');
        if (!$oldData)
        {
            return FALSE;
        }
        else
        {
            $array = unserialize($oldData);
            foreach ($deleteKeys as $key)
            {
                unset($array[$key]);
            }
            $newData = serialize($array);
            $db->formatConditions(['tempstorageId' => $uuid]);
            $db->update('temp_storage', ['tempstorageData' => $newData]);
            $db->formatConditions(['tempstorageId' => $uuid]);
            $db->updateTimestamp('temp_storage', ['tempStorageTimestamp' => '']); // default is CURRENT_TIMESTAMP
            // Delete row if empty
            $data = $db->selectFirstField('temp_storage', 'tempstorageData');
            if (empty(unserialize($data)))
            {
                $db->formatConditions(['tempstorageId' => $uuid]);
                $db->delete('temp_storage');
            }
        }

        return TRUE;
    }
}
