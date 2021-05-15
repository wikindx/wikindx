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
 * GLOBALS
 *
 * Rather than using the PHP $GLOBALS array for common variables, we store, set and access them from here.
 *
 * @package wikindx\core\startup
 */
class GLOBALS
{
    /**
     * user input as either form data or querystring is all stored in the $vars variable
     */
    private static $vars = [];
    /** array */
    private static $dirtyVars = [];
    /**
     * user variables are stored here
     */
    private static $userVars = [];
    /**
     * temp_storage table variables are stored here
     */
    private static $tempStorage = [];
    /**
     *  The db queries counter
     */
    private static $WIKINDX_DB_QUERIES = 0;
    /**
     *  The db queries time elapsed
     */
    private static $WIKINDX_DB_TIME_CHRONO = 0;
    /**
     * Store all variables in an array that we will give to our template system to render at the end of the script execution
     * Each variable is stored with a key name identical to it's variable name defined in templates.
     */
    private static $WIKINDX_TEMPLATE_VARIABLE_STORE = [];
    /**
     * The starting time of the entire page
     */
    private static $WIKINDX_PAGE_STARTING_TIME_CHRONO = NULL;
    /**
     * The ending time of the entire page
     */
    private static $WIKINDX_PAGE_ENDING_TIME_CHRONO = NULL;
    /**
     * Error messages. A convenient place to store a single error message
     */
    private static $WIKINDX_ERROR = '';
    /**
     * The unique browser tab ID
     */
    private static $WIKINDX_BROWSER_TAB_ID = NULL;

    /**
     *	GLOBALS
     */
    public function __construct()
    {
        $this->startPageTimer();
    }
    /**
     * Set $vars
     *
     * $cleanInput has been stripped of HTML etc.
     *
     * @param array $cleanInput
     * @param array $dirtyInput
     */
    public static function setVars($cleanInput, $dirtyInput)
    {
        if (empty(self::$vars))
        {
            self::$vars = $cleanInput;
        }
        if (empty(self::$dirtyVars))
        {
            self::$dirtyVars = $dirtyInput;
        }
    }
    /**
     * Get $vars
     *
     * @return array
     */
    public static function getVars()
    {
        return self::$vars;
    }
    /**
     * Get $dirtyVars
     *
     * @return array
     */
    public static function getDirtyVars()
    {
        return self::$dirtyVars;
    }
    /**
     * Initaliaze the tempStorage array
     *
     * @param object $db
     * @param string $uuid
     */
    public static function initTempStorage($db, $uuid)
    {
        $db->formatConditions(['tempstorageId' => $uuid]);
        $row = $db->selectFirstField('temp_storage', 'tempstorageData');
        if (is_bool($row))
        {
            self::$tempStorage = [];

            return;
        }
        $data = unserialize($row);
        if (empty($data))
        {
            self::$tempStorage = [];

            return;
        }
        self::$tempStorage = $data;
    }
    /**
     * Set tempStorage variables
     *
     * @param array $array (assoc. array)
     */
    public static function setTempStorage($array)
    {
        foreach ($array as $key => $value)
        {
            self::$tempStorage[$key] = $value;
        }
    }
    /**
     * Unset tempStorage variables
     *
     * @param array $array (keys to unset)
     */
    public static function unsetTempStorage($array)
    {
        foreach ($array as $key)
        {
            unset(self::$tempStorage[$key]);
        }
    }
    /**
     * Get $tempStorage either in whole or in part
     *
     * @param string $key default is FALSE
     *
     * @return mixed Array ($key == FALSE), FALSE if $key not found, else string
     */
    public static function getTempStorage($key = FALSE)
    {
        if ($key && !is_array($key))
        {
            if (!array_key_exists($key, self::$tempStorage))
            {
                return FALSE;
            }

            return self::$tempStorage[$key];
        }

        return self::$tempStorage;
    }
    /**
     * Set browserTabID
     *
     * @param $browserTabID
     */
    public static function setBrowserTabID($browserTabID)
    {
        self::$WIKINDX_BROWSER_TAB_ID = $browserTabID;
    }
    /**
     * Get browserTabID
     *
     * @return string GLOBALS::WIKINDX_BROWSER_TAB_ID
     */
    public static function getBrowserTabID()
    {
        return self::$WIKINDX_BROWSER_TAB_ID;
    }
    /**
     * Set a user variable
     *
     * @param string $key
     * @param mixed $value
     */
    public static function setUserVar($key, $value)
    {
        self::$userVars[$key] = $value;
    }
    /**
     * Get a user variable
     *
     * @param $default Default return value if set value does not exist.
     * @param mixed $key
     *
     * @return string
     */
    public static function getUserVar($key, $default = FALSE)
    {
        // Added the check for FALSE because, in some cases (Read Only from the WIKINDX menu after being logged in),
        // self::$userVars[$key] exists but is FALSE. e.g. a warning is produced in PARSEXML line 139 because $style is a bool.
        // TODO: Check loading of user vars for read only –– here is a temporary fix only.
        if (!array_key_exists($key, self::$userVars) || (self::$userVars[$key] === FALSE))
        {
            return $default;
        }

        return self::$userVars[$key];
    }
    /**
     * Get user variable array
     *
     * @return array
     */
    public static function getUserVarsArray()
    {
        return self::$userVars;
    }
    /**
     * Empty user variable array – used in /office.php to clear any existing user variables ready for a read-only access from Word add-in etc.
     *
     * @return array
     */
    public static function deleteUserVarsArray()
    {
    	self::$userVars = [];
        return self::$userVars;
    }
    /**
     * Increment the DB query counter
     */
    public static function incrementDbQueries()
    {
        self::$WIKINDX_DB_QUERIES++;
    }
    /**
     * Get the number of DB queries this script run
     *
     * @return int
     */
    public static function getDbQueries()
    {
        return self::$WIKINDX_DB_QUERIES;
    }
    /**
     * Increment the DB time elapsed
     *
     * @param float $appendTime
     */
    public static function incrementDbTimeElapsed($appendTime)
    {
        self::$WIKINDX_DB_TIME_CHRONO += $appendTime;
    }
    /**
     * Get the time elapsed during DB queries
     *
     * @return float
     */
    public static function getDbTimeElapsed()
    {
        return round(self::$WIKINDX_DB_TIME_CHRONO, 5);
    }
    /**
     * Clear data stored of a template variable
     *
     * @param string $variableName
     */
    public static function clearTplVar($variableName)
    {
        unset(self::$WIKINDX_TEMPLATE_VARIABLE_STORE[$variableName]);
    }
    /**
     * See if some data are defined for a template variable
     *
     * @param string $variableName
     *
     * @return bool
     */
    public static function tplVarExists($variableName)
    {
        return array_key_exists($variableName, self::$WIKINDX_TEMPLATE_VARIABLE_STORE);
    }
    /**
     * Get a concatenated string of the data of a template variable (involve the are only strings)
     *
     * @param string $variableName
     * @param string $glueString
     *
     * @return string
     */
    public static function buildTplVarString($variableName, $glueString = '')
    {
        return implode($glueString, self::getTplVar($variableName));
    }
    /**
     * Add a data to a template variable
     *
     * @param string $variableName
     * @param string $variableValue
     */
    public static function addTplVar($variableName, $variableValue)
    {
        self::$WIKINDX_TEMPLATE_VARIABLE_STORE[$variableName][] = $variableValue;
    }
    /**
     * Set the data of a template variable after clearing its current data
     *
     * @param string $variableName
     * @param string $variableValue
     */
    public static function setTplVar($variableName, $variableValue)
    {
        self::clearTplVar($variableName);

        self::$WIKINDX_TEMPLATE_VARIABLE_STORE[$variableName][] = $variableValue;
    }
    /**
     * Get the data of a template variable
     *
     * @param string $variableName
     *
     * @return array
     */
    public static function getTplVar($variableName)
    {
        if (self::tplVarExists($variableName))
        {
            return self::$WIKINDX_TEMPLATE_VARIABLE_STORE[$variableName];
        }
        else
        {
            return [];
        }
    }
    /**
     * Get the list of all template variables name which have data stored
     *
     * @return array
     */
    public static function getTplVarKeys()
    {
        return array_keys(self::$WIKINDX_TEMPLATE_VARIABLE_STORE);
    }
    /**
     * Start the global page timer with the current UNIX timestamp
     */
    public static function startPageTimer()
    {
        // Don't launch again start timer if we include this file twice
        if (self::$WIKINDX_PAGE_STARTING_TIME_CHRONO == NULL)
        {
            self::$WIKINDX_PAGE_STARTING_TIME_CHRONO = microtime(TRUE);
        }
    }
    /**
     * Stop the global page timer with the current UNIX timestamp
     */
    public static function stopPageTimer()
    {
        self::$WIKINDX_PAGE_ENDING_TIME_CHRONO = microtime(TRUE);
    }
    /**
     * Get elapsed time of the page
     *
     * @return float
     */
    public static function getPageElapsedTime()
    {
        $beginTimer = self::$WIKINDX_PAGE_STARTING_TIME_CHRONO;
        $endTimer = self::$WIKINDX_PAGE_ENDING_TIME_CHRONO ?? microtime(TRUE);
        return round($endTimer - $beginTimer, 5);
    }
    /**
     * set error message
     *
     * @param string $error
     */
    public static function setError($error)
    {
        self::$WIKINDX_ERROR = $error;
    }
    /**
     * get error message
     *
     * @return string GLOBALS::WIKINDX_ERROR
     */
    public static function getError()
    {
        return self::$WIKINDX_ERROR;
    }
}

// Always start global execution timer when we load GLOBALS static class
GLOBALS::startPageTimer();
