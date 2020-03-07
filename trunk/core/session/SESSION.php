<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * SESSION
 *
 * Common session functions
 *
 * @package wikindx\core\session
 */
class SESSION
{
    /**
     * SESSION
     */
    public function __construct()
    {
        if (!isset($_SESSION)) {
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
        if (!isset($key) || !isset($value)) {
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
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
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
        foreach ($_SESSION as $key => $value) {
            if (mb_substr($key, 0, $prefixLength) == $prefix) {
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
        if ($prefix != '') {
            $prefix .= '_';
        }
        foreach ($array as $key => $value) {
            if (!$keepZero && !$value) {
                $value = FALSE;
            }
            if (!$this->setVar($prefix . $key, $value)) {
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
        $prefix .= '_';
        $prefixLength = mb_strlen($prefix);
        foreach ($_SESSION as $key => $value) {
            if (mb_substr($key, 0, $prefixLength) == $prefix) {
                $this->delVar($key);
            }
        }
    }
    /**
     * Clear all user sessions (other than setup_) in case this user has been logging on and off as different users
     */
    public function clearSessionData()
    {
        $prefix = "setup_";
        $prefixLength = mb_strlen($prefix);
        foreach ($_SESSION as $key => $value) {
            if (mb_substr($key, 0, $prefixLength) != $prefix) {
                $this->delVar($key);
            }
        }
    }
    /**
     * Save the session state
     *
     * Save the session variables to the database for logged on users
     *
     * @param string $sessionKey
     */
    public function saveState($sessionKey)
    {
        if ($userId = $this->getVar("setup_UserId")) {
            $db = FACTORY_DB::getInstance();
            // Get existing state
            $db->formatConditions(['usersId' => $userId]);
            $stateIn = $db->selectFirstField('users', 'usersUserSession');
            if ($stateIn) {
                $sessionData = unserialize(base64_decode($stateIn));
            } else {
                $sessionData = [];
            }
            // Sync current state
            if (!is_array($sessionKey)) {
                $sessionKeyList = [$sessionKey];
            } else {
                $sessionKeyList = $sessionKey;
            }
            // At upgrade time, there are no session in db
            $stateOut = '';
            foreach ($sessionKeyList as $sessionKey) {
                $value = $this->getArray($sessionKey);
                //if($sessionKey = 'list')
                //	print "$sessionKey: "; print_r($value); print '<p>';
                if (count($value) > 0) {
                    $sessionData[$sessionKey] = base64_encode(serialize($value));
                } else {
                    unset($sessionData[$sessionKey]);
                }
            }
            $stateOut = base64_encode(serialize($sessionData));
            // Save current state, if changed
            if ($stateIn != $stateOut) {
                // $stateOut can be large . . . Check MySQL max packet length and try to set (possible if DB not shared) otherwise, do not write stateOut to database.
                // See: https://stackoverflow.com/questions/5688403/how-to-check-and-set-max-allowed-packet-mysql-variable
                // Third param of $db->update() bypasses any errors in the sql UPDATE so we continue regardless of whether the state can be saved or not.
                $oldMaxPacket = $db->getMaxPacket();
                $size = strlen($stateOut);
                if ($size >= ($oldMaxPacket - 1024)) {
                    $size += 5 * 1024; // must be multiples of 1024 – give it some headroom
                    $db->setMaxPacket($size);
                    $newMaxPacket = $db->getMaxPacket();
                    if ($newMaxPacket > $oldMaxPacket) { // i.e. successfully set
                        $db->formatConditions(['usersId' => $userId]);
                        $db->update('users', ['usersUserSession' => $stateOut], FALSE); // else, do not update . . .
                        $db->setMaxPacket($oldMaxPacket);
                    }
                } else {
                    $db->formatConditions(['usersId' => $userId]);
                    $db->update('users', ['usersUserSession' => $stateOut], FALSE);
                }
            }
        }
    }
}
