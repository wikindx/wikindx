<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * COOKIE
 *
 * Common cookie functions
 *
 * @package wikindx\core\session
 */
class COOKIE
{
    /** string */
    private $cookieName = "wikindx_" . WIKINDX_DB;
    /** array */
    private $cookieVars;

    /**
     * COOKIE
     */
    public function __construct()
    {
        // whatever array cookie variables are in, we grab 'em.
        if (isset($_COOKIE))
        {
            $this->cookieVars = &$_COOKIE;
        }
    }
    /**
     * Set a cookie if user requests through mywikindx 'remember me' checkbox
     *
     * store only username and password so user does not have to go through initial authentication
     *
     * @param string $username
     */
    public function storeCookie($username)
    {
        $valueArray = ['username' => $username];
        $value = base64_encode(serialize($valueArray));
        $expire = 60 * 60 * 24 * 100; // 100 days
        $path = "/";
        // we don't fail if this cookie can't be set.
        setcookie($this->cookieName, $value, time() + $expire, $path);
    }
    /**
     * remove cookie
     */
    public function deleteCookie()
    {
        setcookie($this->cookieName, "", time() - 3600, "/");
    }
    /**
     * Get cookie
     *
     * @return bool TRUE if cookie set
     */
    public function grabCookie()
    {
        if (!array_key_exists($this->cookieName, $this->cookieVars))
        {
            return FALSE; // no cookie set
        }

        $cookieArray = unserialize(base64_decode($this->cookieVars[$this->cookieName]));

        if (!array_key_exists('username', $cookieArray))
        {
            return FALSE; // invalid cookie
        }

        if (!$cookieArray['username'])
        {
            return FALSE; // invalid cookie
        }

        // Cookie set so check for valid username
        $db = FACTORY_DB::getInstance();
        $recordSet = $db->select('users', ['usersUsername', 'usersId', 'usersAdmin'], ['usersUsername' => $cookieArray['username']]);
        if (!$db->numRows($recordSet))
        {
            return FALSE;
        }

        $user = FACTORY_USER::getInstance();
        $user->environment($db->fetchRow($recordSet));

        return TRUE;
    }
}
