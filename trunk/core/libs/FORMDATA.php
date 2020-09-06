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
 * Form data functions
 *
 * @package wikindx\core\libs\FORMDATA
 */
namespace FORMDATA
{
    /**
     * Write data to the form_data table.
     *
     * @param object $dbo DB object
     * @param array $array Array of form data
     *
     * @return mixed FALSE on error else $uuid
     */
	function putData($dbo, $array)
	{
		$index = 1;
		do {
			$uuid = getUuid($dbo);
			if ($index > 100) { // some sanity
				return FALSE;
			}
			++$index;
		} while ($uuid === FALSE);
		$data = serialize($array);
		$keys[] = 'formdataId';
		$values[] = $uuid;
		$keys[] = 'formdataData';
		$values[] = $data;
		$dbo->insert('form_data', $keys, $values);
		return $uuid;
	}
	
    /**
     * Ensure we have unique UUID (probably not needed but extra check)
     *
     * @param object $dbo DB object
     *
     * @return mixed FALSE on error or valid uuid
     */
	function getUuid($dbo)
	{
		$uuid = \UTILS\uuid();
		$dbo->formatConditions(['formdataId' => $uuid]);
		if ($dbo->numRows($dbo->select('form_data', ['formdataId']))) {
			return FALSE;
		}
		return $uuid;
	}
	
    /**
     * Get data from the form_data table then remove the row
     *
     * @param object $dbo DB object
     * @param string $formdataId
     *
     * @return array Array is empty if no data can be found
     */
	function getData($dbo, $formdataId)
	{
		$dbo->formatConditions(['formdataId' => $formdataId]);
		$data = $dbo->fetchOne($dbo->select('form_data', ['formdataData']));
		if ($data) {
			$data = array_map('\FORMDATA\tidy', unserialize($data));
		}
		else {
			$data = [];
		}
// Clean up 
		deleteData($dbo, $formdataId);
		return $data;
	}
	
    /**
     * Delete a row from the form_data table
     *
     * @param object $dbo DB object
     * @param string $formdataId
     */
	function deleteData($dbo, $formdataId)
	{
		$dbo->formatConditions(['formdataId' => $formdataId]);
		$dbo->delete('form_data');
	}
	/**
	 * Tidy form_data array for the form
	 *
	 * @param string $data
	 */
	function tidy($data)
	{
		return \HTML\dbToFormTidy($data);
	}
}