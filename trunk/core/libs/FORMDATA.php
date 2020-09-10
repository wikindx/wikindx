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
class FORMDATA
{
/** array */
private $formData = [];
/** string */
private $defaultErrorRedirect = "index.php"; // Front page
/** string */
private $successRedirect = '';
/** string */
private $errorRedirect = '';
/** string */
private $defaultErrorMessage = '';
/** string */
private $errorMessage = '';
/** string */
private $successMessage = '';

/**
* FORMDATA
*/
	public function __construct()
    {
    	$errors = FACTORY_ERRORS::getInstance();
    	$this->defaultErrorMessage = $errors->text("inputError", "missing");
    }
    /**
     * Put data in the formData container.
     *
     * @param array $array Array of form data
     */
	public function putData($array)
	{
		$this->formData[] = serialize($array);
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
     * Get data from the formData container
     *
     * @return array Array is empty if no data can be found
     */
	public function getData()
	{
		if (!sizeOf($this->formData)) {
			return [];
		}
		return unserialize(array_shift($this->formData));
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