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
 *	CONFIGDBSTRUCTURE
 *
 *	Map out the structure of the config table
 *
 * @package wikindx\core\startup
 */
class CONFIGDBSTRUCTURE
{
    /** object */
    private $db;

    /**
     *	CONFIGDBSTRUCTURE
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
    }
    /**
     * Get and return one value from the config table.
     *
     * Requested value must be a configName value that is in WIKINDX_LIST_CONFIG_OPTIONS. For other values, use standard $db functions.
     *
     * Result is returned as a number if the value is stored in configInt columns else the return result is a string or a boolean
     *
     * @param string $field – the table column to match the condition.
     *
     * @return bool|float|string
     */
    public function getOne(string $field)
    {
        if (!array_key_exists($field, WIKINDX_LIST_CONFIG_OPTIONS)) {
            die('CONFIGDBSTRUCTURE->getOne(): bad config option name requested:' . $field);
        }
        $column = WIKINDX_LIST_CONFIG_OPTIONS[$field]["type"];
        $this->db->formatConditions(['configName' => $field]);
        $value = $this->db->fetchOne($this->db->select('config', $column));

        return $this->convertVarDB2PHP($column, $value);
    }
    /**
     * Get all data from the config table and return an array of ($field => 'value')
     *
     * @return array
     */
    public function getAllData()
    {
        return $this->getData(array_keys(WIKINDX_LIST_CONFIG_OPTIONS));
    }
    /**
     * Get data from the config table for specific variables and return an array of ($field => 'value')
     *
     * @param mixed $match is the name of a variable or an array or variable names : array('var1, 'var2', ...).
     *
     * @return array
     */
    public function getData($match)
    {
        if (!is_array($match)) {
            $match = [$match];
        }
        
        foreach ($match as $configName) {
	        if (!array_key_exists($configName, WIKINDX_LIST_CONFIG_OPTIONS)) {
	            die('CONFIGDBSTRUCTURE->getData(): bad config option name requested:' . $configName);
	        }
        }
        
        $row = [];
        $this->db->formatConditionsOneField($match, 'configName');
        $resultSet = $this->db->select('config', '*');
        while ($coRow = $this->db->fetchRow($resultSet)) {
            // NB we grab only basic configuration variables – extra rows are added e.g. by localeDescription plugin
            $configName = $coRow['configName'];
            
            // Retrieving known options only
            if (array_key_exists($configName, WIKINDX_LIST_CONFIG_OPTIONS)) {
            	$constName = WIKINDX_LIST_CONFIG_OPTIONS[$configName]["constname"];
            	$configType = WIKINDX_LIST_CONFIG_OPTIONS[$configName]["type"];
            	$configValue = $this->convertVarDB2PHP($configType, $coRow[$configType]);
                
                // Unserialize the value for some options
                if (in_array($configName, ['configNoSort', 'configSearchFilter', 'configDeactivateResourceTypes'])) {
                    $configValue = unserialize(base64_decode($configValue));
                    if (!is_array($configValue)) {
                        $configValue = unserialize(base64_decode(constant($constName . "_DEFAULT")));
                    }
                }
                
                $row[$configName] = $configValue;
            }
        }
        
        // During and installation the config table is not initialized before this function is called,
        // so return default values in that case
        if (count($row) < count($match)) {
            foreach ($match as $configName) {
                $constName = WIKINDX_LIST_CONFIG_OPTIONS[$configName]["constname"];
                $value = constant($constName . "_DEFAULT");
                
                // Unserialize some options
                if (in_array($configName, ['configNoSort', 'configSearchFilter', 'configDeactivateResourceTypes'])) {
                    $value = unserialize(base64_decode($value));
                }
                
                $row[$configName] = $value;
            }
        }

        return $row;
    }
    /**
     * Update one value in the config table.
     *
     * @param string $name in the 'configName' column (i.e. which configuration variable to update)
     * @param mixed $value to set
     */
    public function updateOne(string $name, $value)
    {
        if (!array_key_exists($name, WIKINDX_LIST_CONFIG_OPTIONS)) {
            die('CONFIGDBSTRUCTURE->updateOne(): bad config option name updated:' . $name);
        }
        
        // Serialize some options
        if (in_array($name, ['configNoSort', 'configSearchFilter', 'configDeactivateResourceTypes'])) {
            $value = base64_encode(serialize($value));
        }
        
        $value = $this->convertVarPHP2DB(WIKINDX_LIST_CONFIG_OPTIONS[$name]["type"], $value);
        $this->db->formatConditions(['configName' => $name]);
        $this->db->update('config', [WIKINDX_LIST_CONFIG_OPTIONS[$name]["type"] => $value]);
    }
    /**
     * Convert a value from Wikindx database format to PHP format
     *
     * @param string $configType can be: configVarchar, configInt, configBoolean, configDatetime, or configText
     * @param mixed $value to convert
     *
     * @return mixed The value converted
     */
    private function convertVarDB2PHP(string $configType, $value)
    {
        switch ($configType) {
            // Cast to integer number
            case 'configInt':
                $value = (int)$value;

            break;
            // return boolean (stored as 0 or 1 in the db table)
            case 'configBoolean':
                $value = $value ? TRUE : FALSE;

            break;
        }

        return $value;
    }
    /**
     * Convert a value from PHP format to Wikindx database format
     *
     * @param string $configType can be: configVarchar, configInt, configBoolean, configDatetime, or configText
     * @param mixed $value to convert
     *
     * @return mixed The value converted
     */
    private function convertVarPHP2DB(string $configType, $value)
    {
        switch ($configType) {
            // Cast to integer number
            case 'configInt':
                $value = (string)$value;

            break;
            // return boolean (stored as 0 or 1 in the db table)
            case 'configBoolean':
                $value = $value ? 1 : 0;

            break;
        }

        return $value;
    }
}
