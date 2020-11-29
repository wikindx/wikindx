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
 * SESSION
 *
 * Common session functions
 *
 * @package wikindx\core\libs\SESSION
 */
class SESSION
{
    /**
     * SESSION
     */
    public function __construct()
    {
        if (!isset($_SESSION))
        {
            $_SESSION = [];
        }
        ksort($_SESSION, SORT_LOCALE_STRING);
    }
    /**
     * Set a session variable
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function setVar($key, $value)
    {
        if (!isset($key) || !isset($value))
        {
            return FALSE;
        }
        $_SESSION[$key] = $value;

        return isset($_SESSION[$key]);
    }
    /**
     * Get a session variable
     *
     * @param string $key
     * @param mixed $defaultValue Value returned if the variable session is not set, default is FALSE (keep compatibility with old WKX ver. <= 5)
     *
     * @return mixed The value of the key, if exists, or the value of $defaultValue
     */
    public function getVar($key, $defaultValue = FALSE)
    {
        if (isset($_SESSION[$key]))
        {
            return $_SESSION[$key];
        }
        else
        {
            return $defaultValue;
        }
    }
    /**
     * Delete a session variable
     *
     * @param string $key
     */
    public function delVar($key)
    {
        //debug_print_backtrace();
        unset($_SESSION[$key]);
    }
    /**
     * Is a session variable set?
     *
     * @param string $key
     *
     * @return bool
     */
    public function issetVar($key)
    {
        return isset($_SESSION[$key]);
    }
    /**
     * Destroy the whole session
     */
    public function destroy()
    {
        //debug_print_backtrace();
        $_SESSION = [];
    }
    /**
     * Return an associative array of all session variables starting with $prefix_.
     *
     * key in returned array is minus the prefix to aid in matching database table fields.
     *
     * @param string $prefix
     *
     * @return array
     */
    public function getArray($prefix)
    {
        $array = [];
        $prefix .= '_';
        $prefixLength = mb_strlen($prefix);
        foreach ($_SESSION as $key => $value)
        {
            if (mb_substr($key, 0, $prefixLength) == $prefix)
            {
                $key = mb_substr($key, $prefixLength, mb_strlen($key) - $prefixLength);
                $array[$key] = $value;
            }
        }

        return $array;
    }
    /**
     * Write to session variables named with $prefix_ the given associative array.
     *
     * @param array $array
     * @param string $prefix Default is ''
     * @param bool $keepZero If TRUE, array values that are 0 will be stored as 0 rather than FALSE. Default is FALSE
     *
     * @return bool
     */
    public function writeArray($array, $prefix = '', $keepZero = FALSE)
    {
        if ($prefix != '')
        {
            $prefix .= '_';
        }
        foreach ($array as $key => $value)
        {
            if (!$keepZero && !$value)
            {
                $value = FALSE;
            }
            if (!$this->setVar($prefix . $key, $value))
            {
                return FALSE;
            }
        }

        return TRUE;
    }
    /**
     * Clear session variables named with $prefix
     *
     * @param string $prefix
     */
    public function clearArray($prefix)
    {
        //debug_print_backtrace();
        $prefix .= '_';
        $prefixLength = mb_strlen($prefix);
        foreach ($_SESSION as $key => $value)
        {
            if (mb_substr($key, 0, $prefixLength) == $prefix)
            {
                $this->delVar($key);
            }
        }
    }
    /**
     * Clear all user sessions (other than setup_) in case this user has been logging on and off as different users
     */
    public function clearSessionData()
    {
        //debug_print_backtrace();
        $prefix = "setup_";
        $prefixLength = mb_strlen($prefix);
        foreach ($_SESSION as $key => $value)
        {
            if (mb_substr($key, 0, $prefixLength) != $prefix)
            {
                $this->delVar($key);
            }
        }
    }
}
