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
 * USER
 *
 * Common methods for handling users.
 *
 * @package wikindx\core\usergroups
 */
class USER
{
    /** object */
    private $db;
    /** array */
    private $vars;
    /** object */
    private $errors;
    /** object */
    private $messages;
    /** object */
    private $session;

    /**
     * USER
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
    }
    /**
     * Write user details to WKX_users
     *
     * If $admin = 0, this is a standard user.
     * If $admin = 1, this is the initial configuration/reconfiguration and we need to insert superadmin details into the database.
     * If $admin = 2, this is an admin editing or adding a user.
     * a) the user may not change the username from this screen.
     * When adding a user, we need to check there is not already $usersUsername (caseless) in the database.
     * NB - returns FALSE in case of success, an error message in case of failure.
     *
     * @param bool $add Default is TRUE
     * @param int $admin Default is 0
     *
     * @return mixed
     */
    public function writeUser($add = TRUE, int $admin = 0)
    {
        $userId = $this->session->getVar("setup_UserId", 0);
        $usersUsername = FALSE;
        
        if (($add || ($userId == WIKINDX_SUPERADMIN_ID)) && !array_key_exists('usersUsername', $this->vars))
        {
            return "username field missing";
        }
        elseif ($add || ($userId == WIKINDX_SUPERADMIN_ID))
        {
            $usersUsername = \HTML\removeNl($this->vars['usersUsername']);
        }
        if (!array_key_exists('password', $this->vars))
        {
            return "password field missing";
        }
        else
        {
            $password = \HTML\removeNl($this->vars['password']);
        }
        // check for existing usersUsername (remove current user from search if already logged in with setup_userId)
        if ($add)
        {
            $this->db->formatConditions(['usersUsername' => $usersUsername]);
            $this->db->formatConditions(['usersId' => $userId], TRUE); // Not equal to
            // existing user with that usersUsername found (not this user)
            $recordset = $this->db->select('users', 'usersId');
            if ($this->db->numRows($recordset))
            {
                return $this->errors->text("inputError", "userExists");
            }
        }
        
        if (!$add)
        { // update
            if ($admin == 0)
            { // user editing own details
                $cookie = FACTORY_COOKIE::getInstance();
                if (array_key_exists('cookie', $this->vars) && $this->vars['cookie'])
                {
                    $cookie->storeCookie($usersUsername);
                    $update['usersCookie'] = 'Y';
                }
                else
                {
                    // remove any wikindx cookie that has been set
                    $cookie->deleteCookie();
                    $update['usersCookie'] = 'N';
                }
            }
            elseif ($admin == 1)
            { // superadmin configuration - userId always WIKINDX_SUPERADMIN_ID for superadmin
                $userId = WIKINDX_SUPERADMIN_ID;
            }
            elseif ($admin == 2)
            { // admin editing user
                $userId = $this->vars['userId'];
                if (array_key_exists('admin', $this->vars) && $this->vars['admin'])
                {
                    $update['usersAdmin'] = 1;
                }
                else
                {
                    $update['usersAdmin'] = 0;
                }
                if (array_key_exists('department', $this->vars) && ($dept = \UTF8\mb_trim($this->vars['department'])))
                {
                    $update['usersDepartment'] = $dept;
                }
                elseif (array_key_exists('departmentId', $this->vars) && ($dept = $this->vars['departmentId']))
                {
                    $update['usersDepartment'] = $dept;
                }
                else
                {
                    $nulls[] = 'usersDepartment';
                }
                if (array_key_exists('institution', $this->vars) && ($inst = \UTF8\mb_trim($this->vars['institution'])))
                {
                    $update['usersInstitution'] = $inst;
                }
                elseif (array_key_exists('institutionId', $this->vars) && ($inst = $this->vars['institutionId']))
                {
                    $update['usersInstitution'] = $inst;
                }
                else
                {
                    $nulls[] = 'usersInstitution';
                }
                if (array_key_exists('creatorId', $this->vars) && $this->vars['creatorId'])
                {
                    $update['usersIsCreator'] = $this->vars['creatorId'];
                }
                else
                {
                    $nulls[] = 'usersIsCreator';
                }
            }
            else
            {
                die("admin param value unknown: " . $admin);
            }
            if (isset($nulls))
            {
                $this->db->formatConditions(['usersId' => $userId]);
                $this->db->updateNull('users', $nulls);
            }
            if (array_key_exists('bypassPasswordCheck', $this->vars) === FALSE)
            {
                $this->db->formatConditions(['usersId' => $userId]);
                $recordset = $this->db->select('users', 'usersPassword');
                // The encrypted password is displayed on the browser screen.  Need to check if it's unchanged as we don't
                // want to encrypt the encrypted password!
                if ($this->db->numRows($recordset))
                {
                    $this->pwdInputEncrypted = FALSE;
                    $pwd = $this->db->fetchOne($recordset);
                    if ($password != $pwd)
                    {
                        $password = \UTILS\password_hash($password);
                    }
                    else
                    {
                        $this->pwdInputEncrypted = TRUE;
                    }
                }
                $update['usersPassword'] = $password;
            }
            if (array_key_exists('fullname', $this->vars) &&
                $fullname = $this->db->tidyInput(\HTML\removeNl($this->vars['fullname'])))
            {
                $update['usersFullname'] = \HTML\removeNl($this->vars['fullname']);
            }
            // Ensure it's set to NULL
            else
            {
                $this->db->formatConditions(['usersId' => $userId]);
                $this->db->updateNull('users', 'usersFullname');
            }
            if ($admin == 1)
            { // superadmin configuration
                $update['usersUsername'] = $usersUsername;
            }
            else
            {
                $update['usersEmail'] = $this->vars['email'];
            }
            if ($usersUsername)
            {
                $update['usersUsername'] = $usersUsername;
            }
            $this->db->formatConditions(['usersId' => $userId]);
            $this->db->update('users', $update);
        }
        else
        { // insert new user
            $password = \UTILS\password_hash($password);
            $field[] = 'usersUsername';
            $value[] = $usersUsername;
            $field[] = 'usersPassword';
            $value[] = $password;
            if (array_key_exists('email', $this->vars) &&
                $fullname = \HTML\removeNl($this->vars['email']))
            {
                $field[] = 'usersEmail';
                $value[] = $this->vars['email'];
            }
            if ($admin == 1)
            { // if == 0, default db field value is 0
                $field[] = 'usersAdmin';
                $value[] = 1;
                $field[] = 'usersFullname';
                $value[] = 'superAdmin';
            }
            elseif ($admin == 2)
            { // admin editing a user
                if (array_key_exists('admin', $this->vars) && $this->vars['admin'])
                {
                    $field[] = 'usersAdmin';
                    $value[] = 1;
                }
                if (array_key_exists('department', $this->vars) && ($dept = \UTF8\mb_trim($this->vars['department'])))
                {
                    $field[] = 'usersDepartment';
                    $value[] = $dept;
                }
                elseif (array_key_exists('departmentId', $this->vars) && ($dept = $this->vars['departmentId']))
                {
                    $field[] = 'usersDepartment';
                    $value[] = $dept;
                }
                if (array_key_exists('institution', $this->vars) && ($inst = \UTF8\mb_trim($this->vars['institution'])))
                {
                    $field[] = 'usersInstitution';
                    $value[] = $inst;
                }
                elseif (array_key_exists('institutionId', $this->vars) && ($inst = $this->vars['institutionId']))
                {
                    $field[] = 'usersInstitution';
                    $value[] = $inst;
                }
                if (array_key_exists('creatorId', $this->vars) && $this->vars['creatorId'])
                {
                    $field[] = 'usersIsCreator';
                    $value[] = $this->vars['creatorId'];
                }
            }
            if (array_key_exists('fullname', $this->vars) &&
                $fullname = \HTML\removeNl($this->vars['fullname']))
            {
                $field[] = 'usersFullname';
                $value[] = $fullname;
            }
            
            $this->db->insert('users', $field, $value);
            $userId = $this->db->lastAutoId();
            // insert preferences to table
            $this->writePreferences($userId, TRUE);
        }

        return FALSE; // success!
    }
    
    /**
     * Compare encrypted passwords.
     *
     * Decide if this is standard WIKINDX logon or LDAP logon
     *
     * @param string $usersUsername
     * @param string $pwdInput
     *
     * @return bool
     */
    public function checkPassword($usersUsername, $pwdInput)
    {
        $ldapUserEntry = [];
            
        // Abort if the user is blocked
        if ($this->checkBlocked($usersUsername))
        {
            return FALSE;
        }
        // Abort if the user is not the superadmin in single mode
        if ($this->checkDisallowedInSingleMode($usersUsername))
        {
            return FALSE;
        }
        // Auth with LDAP
        if (WIKINDX_LDAP_USE && in_array("ldap", get_loaded_extensions()))
        {
            // Don't catch traces in regular auth
            if (!$this->checkPasswordLdap($usersUsername, $pwdInput, $ldapUserEntry))
            {
                // When LDAP fails, fallback on builtin ONLY for the superadmin
                if (!$this->checkPasswordBuiltin($usersUsername, $pwdInput, TRUE))
                {
                    return FALSE;
                }
            }
        }
        elseif (!$this->checkPasswordBuiltin($usersUsername, $pwdInput))
        {
            return FALSE;
        }
        
        // At this point the user is authenticated
        
        // Create the user from ldap data
        if (WIKINDX_LDAP_USER_CREATE && count($ldapUserEntry) > 0)
        {
            $this->writeLDAPUser($usersUsername, $ldapUserEntry);
        }
        
        // Retrieve user data
        $this->db->formatConditions(['usersUsername' => $usersUsername]);
        $recordset = $this->db->select('users', ['usersId', 'usersAdmin', 'usersCookie']);
        
        // BUT if the auth was not builtin and the user is not create by this method,
        // or failed to create it, then the auth can STILL be rejected
        if ($this->db->numRows($recordset) == 0)
        {
            return FALSE;
        }
        
        // And now set up his environment
        $row = $this->db->fetchRow($recordset);
        $this->environment($row, $usersUsername);
        
        // Success
        return TRUE;
    }
    
    /**
     * Compare encrypted passwords on LDAP
     *
     * Return FALSE for password not found or password doesn't match.
     * If LDAP user does not exist in WIKINDX, write user details to WIKINDX
     * Superadmin is always id = 1
     *
     * LDAP functions adapted from work by Fabrice Boyrie
     *
     * Limitations:
     *
     * - LDAP_OPT_PROTOCOL_VERSION: v3 is stable since 1997 so we don't provide an option to choice the version (v3 is the default in php_ldap).
     * - LDAP_OPT_DEREF: dereferencing seems an arcane option and could be avoided by setting the main domain name so we keep the default (never deref)
     * - LDAP_OPT_REFERRALS: referrals (forests shared between servers) are very rare and AD don't provide them so we do not support them
     *
     * @param string $usersUsername
     * @param string $pwdInput
     * @param array $ldapUserEntry User ldap info
     * @param string $trace
     *
     * @return bool
     */
    public function checkPasswordLdap($usersUsername, $pwdInput, &$ldapUserEntry = [], &$trace = "")
    {
        $auth = FALSE; // Reject unless the password match and all goes well
        $fail = FALSE; // Allow to skip operations if a step has failed
        
        // Interception of the traces of the library
        $trace = "";
        ob_start();
        
        // WARNING ----------------------------------------------
        // The empty password is never allowed because the ldap_bind() function allows unconditional access in this case.
        // See parameter bind_password of ldap_bind()
        // cf. https://www.php.net/manual/en/function.ldap-bind.php
        // cf. https://tools.ietf.org/html/rfc2251#section-4.2.2
        if ($pwdInput == "")
        {
            $fail = TRUE;
            $trace .= "Empty password!" . LF;
        }
        
        // The ldap extension is not available
        if (!$fail && !in_array("ldap", get_loaded_extensions()))
        {
            $fail = TRUE;
            $trace .= $this->messages->text("hint", "ldapExtDisabled") . LF;
        }
        
        // Turn on debugging
        $trace .= "LDAP_OPT_DEBUG_LEVEL=" . WIKINDX_LDAP_DEBUG_LEVEL . LF;
        if (!$fail && ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, WIKINDX_LDAP_DEBUG_LEVEL) === FALSE)
        {
            $fail = TRUE;
            $trace .= $this->errors->text("inputError", "ldapSetOption") . LF;
        }
        
        // Choice the encryption mode and connect
        $ds = FALSE;
        if (!$fail)
        {
            $ldap_server_uri = \UTILS\array_value_select(
                [
                    // Start a non encrypted connection (insecure)
                    "none" => "ldap://" . WIKINDX_LDAP_SERVER,
                    // Start a non encrypted connection and upgrades the connection later with TLS  (less secure than SSL)
                    "starttls" => "ldap://" . WIKINDX_LDAP_SERVER,
                    // SSL encryption from the start (most secure)
                    "ssl" => "ldaps://" . WIKINDX_LDAP_SERVER, ],
                WIKINDX_LDAP_SERVER_ENCRYPTION,
                WIKINDX_LDAP_SERVER_ENCRYPTION_DEFAULT
            );
            
            $trace .= "SERVER_ENCRYPTION=" . WIKINDX_LDAP_SERVER_ENCRYPTION . LF;
            $trace .= "SERVER_URI=" . $ldap_server_uri . LF;
            $trace .= "SERVER_PORT=" . WIKINDX_LDAP_PORT . LF;
            
            $ds = ldap_connect($ldap_server_uri, WIKINDX_LDAP_PORT);
            if ($ds === FALSE)
            {
                $fail = TRUE;
                $trace .= $this->errors->text("inputError", "ldapConnect") . LF;
            }
        }
        
        // Don't follow referrals
        // cf. https://www.ibm.com/support/knowledgecenter/en/SSVJJU_6.3.1/com.ibm.IBMDS.doc_6.3.1/reference/r_pg_opt_referrals_in_ldap_get_init.html
        $trace .= "LDAP_OPT_REFERRALS=" . WIKINDX_LDAP_USE_REFERRALS . LF;
        if (!$fail && ldap_set_option($ds, LDAP_OPT_REFERRALS, WIKINDX_LDAP_USE_REFERRALS) === FALSE)
        {
            $fail = TRUE;
            $trace .= $this->errors->text("inputError", "ldapSetOption") . LF;
        }
        
        // Set a network timeout in seconds
        $trace .= "LDAP_OPT_NETWORK_TIMEOUT=" . WIKINDX_LDAP_SERVER_NETWORK_TIMEOUT . LF;
        if (!$fail && ldap_set_option($ds, LDAP_OPT_NETWORK_TIMEOUT, WIKINDX_LDAP_SERVER_NETWORK_TIMEOUT) === FALSE)
        {
            $fail = TRUE;
            $trace .= $this->errors->text("inputError", "ldapSetOption") . LF;
        }
        
        // Set a response timeout in seconds
        // cf. https://www.ibm.com/support/knowledgecenter/en/SSVJJU_6.3.1/com.ibm.IBMDS.doc_6.3.1/reference/r_pg_opt_timelimit_in_ldap_get_init.html
        $trace .= "LDAP_OPT_TIMELIMIT=" . WIKINDX_LDAP_SERVER_RESPONSE_TIMEOUT . LF;
        if (!$fail && ldap_set_option($ds, LDAP_OPT_TIMELIMIT, WIKINDX_LDAP_SERVER_RESPONSE_TIMEOUT) === FALSE)
        {
            $fail = TRUE;
            $trace .= $this->errors->text("inputError", "ldapSetOption") . LF;
        }
        
        // Don't verify the certificate in TLS mode (and SSL?)
        if (!$fail)
        {
            $trace .= "LDAPTLS_REQCERT=never" . LF;
            putenv('LDAPTLS_REQCERT=never');
        }
        $trace .= "LDAP_OPT_X_TLS_REQUIRE_CERT=" . 0 . LF;
        if (!$fail && ldap_set_option($ds, LDAP_OPT_X_TLS_REQUIRE_CERT, 0) === FALSE)
        {
            $fail = TRUE;
            $trace .= $this->errors->text("inputError", "ldapSetOption") . LF;
        }
        
        // Start TLS over a non encrypted connection
        if (!$fail && WIKINDX_LDAP_SERVER_ENCRYPTION == "startls")
        {
            $trace .= "Starting TLS";
            if (ldap_start_tls($ds) === FALSE)
            {
                $fail = TRUE;
                $trace .= " failed" . LF;
            }
            else
            {
                $trace .= " succeed" . LF;
            }
        }
        
        // Bind to the server
        // NB: according to PHP doc an empty password performs an anonymous binding,
        // but we do it explicitly, and reject empty passwords for an explicit proxy or binding user.
        $ldapbind = FALSE;
        if (!$fail)
        {
            $trace .= "SERVER_BIND_TYPE=" . WIKINDX_LDAP_SERVER_BIND_TYPE . LF;
            switch (WIKINDX_LDAP_SERVER_BIND_TYPE) {
                case "proxyuser":
                    $ldapbind_pwd = WIKINDX_LDAP_SERVER_BIND_PASSWORD;
                    if ($ldapbind_pwd == "")
                    {
                        $fail = TRUE;
                        $trace .= "Empty binding password!" . LF;
                    }
                    
                    $ldapbind_login = $this->formatLdapLogin(WIKINDX_LDAP_SERVER_BIND_LOGIN);
                    $trace .= "SERVER_BIND_USER=" . $ldapbind_login . LF;
                    $ldapbind = ldap_bind($ds, $ldapbind_login, $ldapbind_pwd);

                break;
                case "user":
                    $ldapbind_pwd = $pwdInput;
                    if ($ldapbind_pwd == "")
                    {
                        $fail = TRUE;
                        $trace .= "Empty binding password!" . LF;
                    }
                    
                    $ldapbind_login = $this->formatLdapLogin($usersUsername);
                    $trace .= "SERVER_BIND_USER=" . $ldapbind_login . LF;
                    $ldapbind = ldap_bind($ds, $ldapbind_login, $ldapbind_pwd);

                break;
                case "anonymous":
                default:
                    $ldapbind_login = "";
                    $ldapbind_pwd = "";
                    $ldapbind = ldap_bind($ds);

                break;
            }
            if ($ldapbind === FALSE)
            {
                $fail = TRUE;
                $trace .= $this->errors->text("inputError", "ldapBind") . LF;
            }
        }
        
        // Extract all users OU
        $UserOU = \UTF8\mb_explode("\n", WIKINDX_LDAP_USER_OU);
        foreach ($UserOU as $k => $v)
        {
            $UserOU[$k] = trim($v);
        }
        $UserOU = array_diff($UserOU, [""]);
        
        // Search the user in the tree under an Organizational Unit (OU) or a Domain Controller (DC)
        $UsersInDn = [];
        if (!$fail && count($UserOU) > 0)
        {
            $sr = FALSE;
            if (!$fail)
            {
                $ldap_search_func = \UTILS\array_value_select(
                    ["tree" => "ldap_search", "list" => "ldap_list"],
                    WIKINDX_LDAP_SEARCH_METHOD,
                    WIKINDX_LDAP_SEARCH_METHOD_DEFAULT
                );
                
                // filter on user type AND user login
                $user_filter = "(&" . WIKINDX_LDAP_USER_TYPE_FILTER . "(" . WIKINDX_LDAP_USER_ATTRIBUTE_LOGIN . "=" . ldap_escape($usersUsername, "", LDAP_ESCAPE_FILTER) . "))";
                
                // Attributs retrived
                $user_filter_attributs = ["dn"];
                
                $trace .= "USER_OU=" . LF;
                $trace .= implode(LF, $UserOU) . LF;
                $trace .= "LDAP_SEARCH_FUNCTION=" . $ldap_search_func . LF;
                $trace .= "USER_FILTER=" . $user_filter . LF;
                $trace .= "USER_FILTER_ATTRIBUTES=" . implode(", ", $user_filter_attributs) . LF;
                
                // The search needs one connection by OU
                $aDS = [];
                foreach($UserOU As $ou)
                {
                    $aDS[] = $ds;
                }
                
                $aSR = $ldap_search_func($aDS, $UserOU, $user_filter, $user_filter_attributs);
                if ($aSR === FALSE || count($aSR) == 0)
                {
                    $fail = TRUE;
                    $trace .= $this->errors->text("inputError", "ldapSearch") . LF;
                }
            }
            
            // Retrieve user DN from the previous search
            if (!$fail)
            {
                foreach ($aSR as $sr)
                {
                    $entries = FALSE;
                    $entries = ldap_get_entries($ds, $sr);
                    if ($entries === FALSE)
                    {
                        $fail = TRUE;
                        $trace .= $this->errors->text("inputError", "ldapGetEntries") . LF;
                    }
                    else
                    {
                        $trace .= "USERS=" . print_r($entries, TRUE) . LF;
                        for ($k = 0; $k < $entries["count"]; $k++)
                        {
                            $UsersInDn[] = $entries[$k]["dn"];
                        }
                    }
                }
            }
        }
        
        // Extract all groups CN
        $GroupCN = \UTF8\mb_explode("\n", WIKINDX_LDAP_GROUP_CN);
        foreach ($GroupCN as $k => $v)
        {
            $GroupCN[$k] = trim($v);
        }
        $GroupCN = array_diff($GroupCN, [""]);
        
        // Search the users in a groups CN
        $UsersInGroup = [];
        if (!$fail && count($GroupCN) > 0)
        {
            $sr = FALSE;
            if (!$fail)
            {
                // filter on group type only
                $group_filter = WIKINDX_LDAP_GROUP_TYPE_FILTER;
                
                // Attributs retrived
                $group_filter_attributs = ["dn", "member"];
                
                $trace .= "GROUP_CN=" . LF;
                $trace .= implode(LF, $GroupCN) . LF;
                $trace .= "GROUP_FILTER=" . WIKINDX_LDAP_GROUP_TYPE_FILTER . LF;
                $trace .= "GROUP_ATTRIBUTES=dn,member" . LF;
                $trace .= "GROUP_FILTER_ATTRIBUTES=" . implode(", ", $group_filter_attributs) . LF;
                
                // The search needs one connection by CN
                $aDS = [];
                foreach($GroupCN As $ou)
                {
                    $aDS[] = $ds;
                }
                $aSR = ldap_read($ds, $GroupCN, $group_filter, $group_filter_attributs);
                if ($aSR === FALSE || count($aSR) == 0)
                {
                    $fail = TRUE;
                    $trace .= $this->errors->text("inputError", "ldapSearch") . LF;
                }
            }
            
            // Retrieve user DN from the previous search
            if (!$fail)
            {
                foreach ($aSR as $sr)
                {
                    $entries = FALSE;
                    $entries = ldap_get_entries($ds, $sr);
                    if ($entries === FALSE)
                    {
                        $fail = TRUE;
                        $trace .= $this->errors->text("inputError", "ldapGetEntries") . LF;
                    }
                    else
                    {
                        $trace .= "USERS=" . print_r($entries, TRUE) . LF;
                        for ($k = 0; $k < $entries["count"]; $k++)
                        {
                            for ($p = 0; $p < $entries[$k]["member"]["count"]; $p++)
                            {
                                $UsersInGroup[] = $entries[$k]["member"][$p];
                            }
                        }
                    }
                }
            }
        }
        
        // Calculate the intersection of the two user lists
        if (count($UserOU) > 0 && count($GroupCN) > 0)
        {
            $Users = array_intersect($UsersInDn, $UsersInGroup);
        }
        elseif (count($UserOU) > 0)
        {
            $Users = $UsersInDn;
        }
        elseif (count($GroupCN) > 0)
        {
            $Users = $UsersInGroup;
        }
        else
        {
            $Users = [];
        }
        
        // Stop if the list is empty
        if (!$fail && count($Users) == 0)
        {
            $fail = TRUE;
        }
        
        // Find the user whose login attribute matches the input login and checks his password
        if (!$fail && count($Users) > 0)
        {
            // filter on user type only
            $user_filter = "(&" . WIKINDX_LDAP_USER_TYPE_FILTER . "(" . WIKINDX_LDAP_USER_ATTRIBUTE_LOGIN . "=" . ldap_escape($usersUsername, "", LDAP_ESCAPE_FILTER) . "))";
            
            // Attributs retrived, when defined
            $user_filter_attributs = array_diff(
                [
                    "dn",
                    WIKINDX_LDAP_USER_ATTRIBUTE_LOGIN,
                    WIKINDX_LDAP_USER_ATTRIBUTE_FULLNAME,
                    WIKINDX_LDAP_USER_ATTRIBUTE_EMAIL,
                ],
                [""]
            );
            
            $trace .= "USER_FILTER=" . WIKINDX_LDAP_USER_TYPE_FILTER . LF;
            $trace .= "USER_FILTER_ATTRIBUTES=" . implode(", ", $user_filter_attributs) . LF;
            
            foreach ($Users as $dn)
            {
                $trace .= "USER_DN=" . $dn . LF;
                
                $sr = ldap_read($ds, $dn, $user_filter, $user_filter_attributs);
                if ($sr === FALSE)
                {
                    $fail = TRUE;
                    $trace .= $this->errors->text("inputError", "ldapSearch") . LF;

                    break;
                }
                else
                {
                    // Retrieve user data from the previous search
                    $entries = ldap_get_entries($ds, $sr);
                    if ($entries === FALSE)
                    {
                        $fail = TRUE;
                        $trace .= $this->errors->text("inputError", "ldapGetEntries") . LF;

                        break;
                    }
                    else
                    {
                        $entries = array_change_key_case($entries, CASE_LOWER);
                        $login_attribut = mb_strtolower(WIKINDX_LDAP_USER_ATTRIBUTE_LOGIN);
                        $trace .= "USERS_VALUES=" . print_r($entries, TRUE) . LF;
                        
                        for ($k = 0; $k < $entries["count"]; $k++)
                        {
                            for ($p = 0; $p < $entries[$k][$login_attribut]["count"]; $p++)
                            {
                                if ($entries[$k][$login_attribut][$p] == $usersUsername)
                                {
                                    // Check the connection with the exact user credentials (DN = Distinguished Names) of the first user only
                                    $trace .= "VERIFY_USER_PASSWORD=" . $dn . LF;
                                    $auth = ldap_bind($ds, $dn, $pwdInput);
                                    $trace .= "AUTH=" . $auth ? "OK" : "NOK" . LF;
                                    if ($auth)
                                    {
                                        $ldapUserEntry = $entries[0];

                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Disconnect
        if ($ds !== FALSE)
        {
            ldap_unbind($ds);
        }
        
        // Add the debug trace of the library
        $trace .= LF;
        $trace .= trim(ob_get_clean());
        
        return ($auth === TRUE) && ($fail === FALSE);
    }
    
    /**
     * set up user environment on first logon
     *
     * @param array $row A user record
     * @param string $usersUsername Default is FALSE
     */
    public function environment($row, $usersUsername = FALSE)
    {
        // First delete any pre-existing session
        $this->session->clearSessionData();
        if (array_key_exists('usersAdmin', $row) && $row['usersAdmin'])
        {
            $this->session->setVar("setup_Superadmin", TRUE);
        }
        $this->session->setVar("setup_UserId", $row['usersId']);
        $this->session->setVar("setup_Write", TRUE);
        $this->session->delVar("setup_ReadOnly");
        $this->writeSessionPreferences($row['usersId']);
        // Reload message files
        $this->messages = FACTORY_MESSAGES::getFreshInstance();
        $this->errors = FACTORY_ERRORS::getFreshInstance();
        //		$this->bib->grabBibliographies();
        // If $usersUsername, we are logging in without using a cookie.  Check if we require a cookie to be set.
        if (array_key_exists('usersCookie', $row) && ($row['usersCookie'] == 'Y'))
        {
            $cookie = FACTORY_COOKIE::getInstance();
            $cookie->storeCookie($usersUsername);
        }
    }
    /**
     * create 'added by..., edited by... details for displaying resources
     *
     * @param array $row
     * @param bool $both Default is FALSE
     * @param string $type Default is 'resource'
     *
     * @return array ($userAdd, $userEdit)
     */
    public function displayUserAddEdit($row, $both = FALSE, $type = 'resource')
    {
        if (!WIKINDX_MULTIUSER)
        {
            if ($both)
            {
                return ["", FALSE];
            }
            else
            {
                return FALSE;
            }
        }
        if ($type == 'resource')
        {
            $add = $row['resourcemiscAddUserIdResource'];
            $edit = $row['resourcemiscEditUserIdResource'];
        }
        elseif ($type == 'abstract')
        {
            $add = $row['resourcetextAddUserIdAbstract'];
            $edit = $row['resourcetextEditUserIdAbstract'];
        }
        elseif ($type == 'note')
        {
            $add = $row['resourcetextAddUserIdNote'];
            $edit = $row['resourcetextEditUserIdNote'];
        }
        elseif ($type == 'custom')
        {
            $add = $row['resourcecustomAddUserIdCustom'];
            $edit = $row['resourcecustomEditUserIdCustom'];
        }
        elseif (($type == 'musing') || ($type == 'quote') || ($type == 'paraphrase') || ($type == 'comment') || ($type == 'idea'))
        {
            // NB in this case, $row is not an array but the user ID
            $this->db->formatConditions(['usersId' => $row]);
            $resultset = $this->db->select('users', ['usersUsername', 'usersFullname']);
            if (!$this->db->numRows($resultset))
            {
                if ($both)
                {
                    return [$this->messages->text("user", "unknown"), FALSE];
                }
                else
                {
                    return FALSE;
                }
            }
            $row = $this->db->fetchRow($resultset);
            if ($row['usersFullname'])
            {
                if ($type == 'idea')
                {
                    $user = stripslashes($row['usersFullname']);
                }
                else
                {
                    $user = $this->messages->text("hint", "addedBy", stripslashes($row['usersFullname']));
                }
            }
            elseif ($row['usersUsername'])
            {
                if ($type == 'idea')
                {
                    $user = stripslashes($row['usersUsername']);
                }
                else
                {
                    $user = $this->messages->text("hint", "addedBy", stripslashes($row['usersUsername']));
                }
            }
            else
            {
                if ($type == 'idea')
                {
                    $user = $this->messages->text("user", "unknown");
                }
                else
                {
                    $user = $this->messages->text("hint", "addedBy", $this->messages->text("user", "unknown"));
                }
            }

            return [$user, FALSE];
        }
        if ($edit)
        {
            if (is_array($row) && array_key_exists('usersFullname', $row) && $row['usersFullname'])
            {
                $userEdit = $this->messages->text("hint", "editedBy", stripslashes($row['usersFullname']));
            }
            elseif (is_array($row) && array_key_exists('usersUsername', $row) && $row['usersUsername'])
            {
                $userEdit = $this->messages->text("hint", "editedBy", stripslashes($row['usersUsername']));
            }
            else
            {
                $userEdit = $this->messages->text("hint", "editedBy", $this->messages->text("user", "unknown"));
            }
            if (!$both)
            {
                return $userEdit;
            }
            $this->db->formatConditions(['usersId' => $add]);
            $resultset = $this->db->select('users', ['usersUsername', 'usersFullname']);
            $row = $this->db->fetchRow($resultset);
            if (is_array($row) && array_key_exists('usersFullname', $row) && $row['usersFullname'])
            {
                $userAdd = $this->messages->text("hint", "addedBy", stripslashes($row['usersFullname']));
            }
            elseif (is_array($row) && array_key_exists('usersUsername', $row) && $row['usersUsername'])
            {
                $userAdd = $this->messages->text("hint", "addedBy", stripslashes($row['usersUsername']));
            }
            else
            {
                $userAdd = $this->messages->text("hint", "addedBy", $this->messages->text("user", "unknown"));
            }

            return [$userAdd, $userEdit];
        }
        if ($add)
        {
            if (is_array($row) && array_key_exists('usersFullname', $row) && $row['usersFullname'])
            {
                $userAdd = $this->messages->text("hint", "addedBy", stripslashes($row['usersFullname']));
            }
            elseif (is_array($row) && array_key_exists('usersUsername', $row) && $row['usersUsername'])
            {
                $userAdd = $this->messages->text("hint", "addedBy", stripslashes($row['usersUsername']));
            }
            else
            {
                $userAdd = $this->messages->text("hint", "addedBy", $this->messages->text("user", "unknown"));
            }
            /*			if(!$both) // usually for list views where we display only either added by or edited by
                        {
                            if(!$edit
                                &&
                                array_key_exists('resourcetimestampTimestamp', $row) // i.e. edited
                                &&
                                ($row['resourcetimestampTimestamp'] != $row['resourcetimestampTimestampAdd']) // i.e. edited at a later date
                                )
                            {
                                $userEdit = $this->messages->text("hint", "editedBy", $this->messages->text("user", "unknown"));
                                return $userEdit;
                            }
                            else
                                return $userAdd;
                        }
                        $this->db->formatConditions(array('usersId' => $edit));
                        $resultset = $this->db->select('users', array('usersUsername', 'usersFullname'));
                        if(!$this->db->numRows($resultset))
                        {
                            if(!$edit
                                &&
                                array_key_exists('resourcetimestampTimestamp', $row) // i.e. edited
                                &&
                                ($row['resourcetimestampTimestamp'] != $row['resourcetimestampTimestampAdd']) // i.e. edited at a later date
                                )
                                $userEdit = $this->messages->text("hint", "editedBy", $this->messages->text("user", "unknown"));
                            else
                                $userEdit = FALSE;
                            return array($userAdd, $userEdit);
                        }
                        $row = $this->db->fetchRow($resultset);
                        if(is_array($row) && array_key_exists('usersFullname', $row) && $row['usersFullname'])
                            $userEdit = $this->messages->text("hint", "editedBy", stripslashes($row['usersFullname']));
                        else if(is_array($row) && array_key_exists('usersUsername', $row) && $row['usersUsername'])
                            $userEdit = $this->messages->text("hint", "editedBy", stripslashes($row['usersUsername']));
                        else
                            $userEdit = $this->messages->text("hint", "editedBy", $this->messages->text("user", "unknown"));
            */
            if (!$both)
            {
                return $userAdd;
            }

            return [$userAdd, FALSE];
        }
    }
    /**
     * create username
     *
     * Plain version of displayUserAddEdit() used in core/modules/email/EMAIL.php for notify emails
     *
     * @param int $addId
     *
     * @return string
     */
    public function displayUserAddEditPlain($addId)
    {
        $usersUsername = "";
        if ($addId)
        {
            $this->db->formatConditions(['usersId' => $addId]);
            $recordset = $this->db->select('users', ['usersUsername', 'usersFullname']);
            $row2 = $this->db->fetchRow($recordset);
            if ($row2['usersFullname'])
            {
                $usersUsername = $row2['usersFullname'];
            }
            elseif ($row2['usersUsername'])
            {
                $usersUsername = $row2['usersUsername'];
            }
        }

        return $usersUsername;
    }
    /**
     * Store user preferences in the session setup_ array
     *
     * Can come from users or config (default values for readOnly user in which case $userId = FALSE)
     *
     * @param int $userId
     */
    public function writeSessionPreferences($userId)
    {
        if ($userId)
        {
            $this->session->setVar("setup_UserId", $userId);
            $bib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
            $bibs = $bib->getUserBibs();
            if (empty($bibs))
            {
                $bibs = $bib->getGroupBibs();
            }
            if (!empty($bibs))
            {
                $this->session->setVar("setup_Bibliographies", TRUE);
            }
        }
        else
        {
            $this->session->setVar("setup_Bibliographies", FALSE);
        }
    }
    /**
     * Load user details into an array
     *
     * @param int $id Default is FALSE
     */
    public function loadFormData($id = FALSE)
    {
        $formData = $departments = $institutions = [];
        if (!$id)
        {
            $id = $this->session->getVar("setup_UserId");
        }
        $userArray = [
            "usersUsername" => "usersUsername",
            "email" => "usersEmail",
            "fullname" => "usersFullname",
            "admin" => "usersAdmin",
            "cookie" => "usersCookie",
            "department" => "usersDepartment",
            "institution" => "usersInstitution",
            "creatorId" => "usersIsCreator",
        ];
        for ($i = 1; $i < 4; $i++)
        {
            $userArray[] = "usersPasswordQuestion$i";
        }
        $this->db->formatConditions(['usersId' => $id]);
        $recordset = $this->db->select('users', array_values($userArray));
        $row = $this->db->fetchRow($recordset);
        foreach ($userArray as $key => $dbField)
        {
            if ($key == 'cookie')
            {
                if ($row[$dbField] == 'Y')
                {
                    $formData[$key] = TRUE;
                }
            }
            elseif ($key == 'admin')
            {
                if ($row[$dbField] == 1)
                {
                    $formData[$key] = TRUE;
                }
            }
            elseif ($row[$dbField])
            {
                $formData[$key] = $row[$dbField];
            }
        }
        if (array_key_exists('department', $formData))
        {
            $formData['departmentId'] = $formData['department'];
            unset($formData['department']);
        }
        if (array_key_exists('institution', $formData))
        {
            $formData['institutionId'] = $formData['institution'];
            unset($formData['institution']);
        }

        return $formData;
    }
    /**
     * display user details from users table
     *
     * If $admin != FALSE, it is the user id that an admin is editing
     *
     * @param string $form
     * @param string $hidden
     * @param int $admin Default is FALSE
     * @param array $formData populated if validation failed
     *
     * @return string
     */
    public function displayUserDetails($form, $hidden, $admin = FALSE, $formData = [])
    {
        $password = FACTORY_PASSWORD::getInstance();
        list($formText, $jsString) = $password->createElements(FALSE);
        $pString = \FORM\formHeader($form, 'onsubmit="return checkForm(' . $jsString . ');"');
        $pString .= \FORM\hidden('method', $hidden);
        $field = array_key_exists('usersUsername', $formData) ? $formData['usersUsername'] : FALSE;
        $pString .= \FORM\hidden("usersUsername", $field);
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\HTML\strong($this->messages->text("user", "username")) . ":&nbsp;&nbsp;$field" .
            BR . "&nbsp;");
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= $formText;
        $field = array_key_exists('email', $formData) ? $formData['email'] : FALSE;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput($this->messages->text("user", "email"), "email", $field, 20, 255));
        $field = array_key_exists('fullname', $formData) ? $formData['fullname'] : FALSE;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "fullname"),
            "fullname",
            $field,
            20,
            255
        ));
        if ($admin)
        {
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \FORM\hidden("userId", $admin);
            $pString .= BR . "&nbsp;" . BR;
            $pString .= \HTML\tableStart();
            $pString .= \HTML\trStart();
            // Department
            $field = array_key_exists('department', $formData) ? $formData['department'] : FALSE;
            $td = \FORM\textInput($this->messages->text("user", "department"), "department", $field, 30, 255);
            $resultset = $this->db->select('users', 'usersDepartment', TRUE);
            while ($row = $this->db->fetchRow($resultset))
            {
                $department = \HTML\dbToFormTidy($row['usersDepartment']);
                if (!$department)
                {
                    continue;
                }
                $departments[$department] = $department;
            }
            if (isset($departments))
            {
                // add 0 => IGNORE to departments array
                $temp[0] = $this->messages->text("misc", "ignore");
                foreach ($departments as $key => $value)
                {
                    $temp[$key] = $value;
                }
                $departments = $temp;
                unset($temp);
                $field = array_key_exists('departmentId', $formData) ?
                    htmlspecialchars(trim($formData['departmentId']), ENT_QUOTES | ENT_HTML5) : FALSE;
                if ($field)
                {
                    $td .= BR . \FORM\selectedBoxValue('', "departmentId", $departments, $field, 1);
                }
                else
                {
                    $td .= BR . \FORM\selectFBoxValue('', "departmentId", $departments, 1);
                }
            }
            $pString .= \HTML\td($td);
            // Institution
            $field = array_key_exists('institution', $formData) ? $formData['institution'] : FALSE;
            $td = \FORM\textInput($this->messages->text("user", "institution"), "institution", $field, 30, 255);
            $resultset = $this->db->select('users', 'usersInstitution', TRUE);
            while ($row = $this->db->fetchRow($resultset))
            {
                $institution = \HTML\dbToFormTidy($row['usersInstitution']);
                if (!$institution)
                {
                    continue;
                }
                $institutions[$institution] = $institution;
            }
            if (isset($institutions))
            {
                // add 0 => IGNORE to insitutions array
                $temp[0] = $this->messages->text("misc", "ignore");
                foreach ($institutions as $key => $value)
                {
                    $temp[$key] = $value;
                }
                $institutions = $temp;
                unset($temp);
                $field = array_key_exists('institutionId', $formData) ?
                    htmlspecialchars(trim($formData['institutionId']), ENT_QUOTES | ENT_HTML5) : FALSE;
                if ($field)
                {
                    $td .= BR . \FORM\selectedBoxValue('', "institutionId", $institutions, $field, 1);
                }
                else
                {
                    $td .= BR . \FORM\selectFBoxValue('', "institutionId", $institutions, 1);
                }
            }
            $pString .= \HTML\td($td);
            // User is creator
            $creator = FACTORY_CREATOR::getInstance();
            $creators = $creator->grabAll(FALSE, FALSE, FALSE, TRUE);
            if (is_array($creators))
            {
                // add 0 => IGNORE to creators array
                $temp[0] = $this->messages->text("misc", "ignore");
                foreach ($creators as $key => $value)
                {
                    $temp[$key] = $value;
                }
                $creators = $temp;
                unset($temp);
                $field = array_key_exists('creatorId', $formData) ? $formData['creatorId'] : 0;
                $pString .= \HTML\td(\FORM\selectedBoxValue(
                    $this->messages->text("user", "isCreator"),
                    "creatorId",
                    $creators,
                    $field,
                    1
                ));
            }
            $field = array_key_exists('admin', $formData) ? TRUE : FALSE;
            $pString .= \HTML\td(\FORM\checkbox($this->messages->text("user", "admin"), "admin", $field));
        }
        else
        {
            $field = array_key_exists('cookie', $formData) ? TRUE : FALSE;
            $pString .= \HTML\td(\FORM\checkbox($this->messages->text("user", "cookie"), "cookie", $field));
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $field = array_key_exists('bypassPasswordCheck', $formData) ? TRUE : FALSE;
        $pString .= \HTML\p(\FORM\checkbox($this->messages->text("user", "bypassPasswordCheck"), "bypassPasswordCheck", $field));
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")), FALSE, "left");
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * insert/update user preferences
     *
     * @param int $userId
     * @param bool $newUser
     */
    public function writePreferences($userId, $newUser = FALSE)
    {
        // Set paging_start back to 0
        $this->session->setVar("mywikindx_PagingStart", 0);
        $preferences = [];
        // Options inherited from the global config
        if (WIKINDX_LIST_LINK)
        {
            $preferences["ListLink"] = 1;
        }
        else
        {
            $preferences["ListLink"] = 0;
        }
        $preferences["Paging"] = WIKINDX_PAGING;
        $preferences["PagingMaxLinks"] = WIKINDX_PAGING_MAXLINKS;
        $preferences["PagingTagCloud"] = WIKINDX_PAGING_TAG_CLOUD;
        $preferences["StringLimit"] = WIKINDX_STRING_LIMIT;
        $preferences["Style"] = WIKINDX_STYLE;
        $preferences["Template"] = WIKINDX_TEMPLATE;
        // Language should be inherited but it needs a special default
        // which allows the browser to control the preferred language first
        $preferences["Language"] = WIKINDX_USER_LANGUAGE_DEFAULT;
        
        // Options unique to users
        if (WIKINDX_DISPLAY_BIBTEX_LINK_DEFAULT)
        {
            $preferences["DisplayBibtexLink"] = 1;
        }
        else
        {
            $preferences["DisplayBibtexLink"] = 0;
        }
        if (WIKINDX_DISPLAY_CMS_LINK_DEFAULT)
        {
            $preferences["DisplayCmsLink"] = 1;
        }
        else
        {
            $preferences["DisplayCmsLink"] = 0;
        }
        $preferences["PagingStyle"] = WIKINDX_USER_PAGING_STYLE_DEFAULT;
        $preferences["TemplateMenu"] = WIKINDX_TEMPLATE_MENU_DEFAULT;
        if (WIKINDX_USE_BIBTEX_KEY_DEFAULT)
        {
            $preferences["UseBibtexKey"] = 1;
        }
        else
        {
            $preferences["UseBibtexKey"] = 0;
        }
        if (WIKINDX_USE_WIKINDX_KEY_DEFAULT)
        {
            $preferences["UseWikindxKey"] = 1;
        }
        else
        {
            $preferences["UseWikindxKey"] = 0;
        }

        foreach ($preferences as $pref => $default)
        {
            if ($newUser)
            {
                $value = $default;
            }
            elseif ($value = GLOBALS::getUserVar($pref))
            {
                // Value already assigned in the condition
            }
            else
            {
                $value = $default;
            }
            
            // Grr! PHP doesn't cast FALSE to "0" but the empty string
            $value = is_bool($value) ? var_export($value, TRUE) : $value;
            $updateArray['users' . $pref] = $value;
        }
        $this->db->formatConditions(['usersId' => $userId]);
        $this->db->update('users', $updateArray);

        FACTORY_TEMPLATE::getInstance()->clearAllCache();
    }
    /**
     * List user's user groups
     *
     * @return mixed FALSE|array
     */
    public function listUserGroups()
    {
        $this->db->formatConditions(['usergroupsAdminId' => $this->session->getVar("setup_UserId")]);
        $this->db->orderBy('usergroupsTitle');
        $recordset = $this->db->select(['user_groups'], ['usergroupsId', 'usergroupsTitle']);
        if (!$this->db->numRows($recordset))
        {
            return FALSE;
        }
        else
        {
            while ($row = $this->db->fetchRow($recordset))
            {
                $groups[$row['usergroupsId']] = $row['usergroupsTitle'];
            }

            return $groups;
        }
    }
    /**
     * Get users from users.
     *
     * Return associative array of id => group of FALSE if no users.
     *
     * @param bool $full Default is FALSE
     * @param bool $useBib Default is FALSE
     * @param string $addEdit Default is 'add'
     *
     * @return mixed FALSE|array
     */
    public function grabAll($full = FALSE, $useBib = FALSE, $addEdit = 'add')
    {
        if ($useBib)
        {
            $field = $addEdit == 'add' ? 'resourcemiscAddUserIdResource' : 'resourcemiscEditUserIdResource';
            $this->db->formatConditions(['userbibliographyresourceBibliographyId' => $useBib]);
            $this->db->leftJoin('resource_misc', 'resourcemiscId', 'userbibliographyresourceResourceId');
            $sq = $this->db->subQuery($this->db->selectNoExecute('user_bibliography_resource', $field, TRUE), 't', TRUE, TRUE);
            $this->db->formatConditions($this->db->formatFields('usersId') . $this->db->equal . $this->db->formatFields($field));
        }
        $this->db->orderBy('usersUsername');

        if ($full)
        {
            $listOfFields = ["usersId", "usersUsername", "usersFullname", "usersAdmin"];
        }
        else
        {
            $listOfFields = ["usersId", "usersUsername"];
        }

        if ($useBib)
        {
            $recordset = $this->db->selectFromSubQuery('users', $listOfFields, $sq);
        }
        else
        {
            $recordset = $this->db->select('users', $listOfFields, TRUE);
        }

        while ($row = $this->db->fetchRow($recordset))
        {
            $usersUsername = $row['usersUsername'];

            if ($full)
            {
                if ($row['usersFullname'])
                {
                    $usersUsername .= " (" . $row['usersFullname'] . ")";
                }
                if ($row['usersAdmin'] == 1)
                {
                    $usersUsername .= " ADMIN";
                }
            }

            $users[$row['usersId']] = \HTML\dbToFormTidy($usersUsername);
        }
        if (isset($users))
        {
            return $users;
        }
        else
        {
            return FALSE; // shouldn't ever happen but just in case...
        }
    }
    /**
     * Delete user(s) from user table and handle users' metadata
     *
     * @param array $input Array of ids in WKX_users.
     *
     * @return bool
     */
    public function deleteSql($input)
    {
        // Delete from users
        $this->db->formatConditionsOneField($input, 'usersId');
        $this->db->delete('users');
        // Delete from user_bibliography
        $this->db->formatConditionsOneField($input, 'userbibliographyUserId');
        $this->db->delete('user_bibliography');
        // Delete from user_groups_users
        $this->db->formatConditionsOneField($input, 'usergroupsusersUserId');
        $this->db->delete('user_groups_users');
        // Manage deleted user's metadata
        // $this->vars['userMetadata']:
        // 0 -- do nothing except set to public
        // 1 -- transfer to superadmin and set to public
        // 2 -- delete
        if (!array_key_exists('userMetadata', $this->vars))
        {
            return TRUE;
        }
        if (!$this->vars['userMetadata'])
        { // update comments etc. to public
            $updateArray = ['resourcemetadataPrivate' => 'N'];
            $this->db->formatConditionsOneField($input, 'resourcemetadataAddUserId');
            $this->db->update('resource_metadata', $updateArray);
        }
        elseif ($this->vars['userMetadata'] == 1)
        { // to superadmin and set to public
            $updateArray = ['resourcemetadataAddUserId' => 1, 'resourcemetadataPrivate' => 'N'];
            $this->db->formatConditionsOneField($input, 'resourcemetadataAddUserId');
            $this->db->update('resource_metadata', $updateArray);
        }
        elseif ($this->vars['userMetadata'] == 2)
        { // delete
            foreach ($input as $userId)
            {
                $this->db->formatConditions(['resourcemetadataAddUserId' => $userId]);
                $this->db->delete('resource_metadata');
            }
            $this->db->formatConditions(['resourcemetadataType' => 'q']);
            $num = $this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId'));
            $this->db->update('database_summary', ['databasesummaryTotalQuotes' => $num]);
            $this->db->formatConditions(['resourcemetadataType' => 'p']);
            $num = $this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId'));
            $this->db->update('database_summary', ['databasesummaryTotalParaphrases' => $num]);
            $this->db->formatConditions(['resourcemetadataType' => 'm']);
            $num = $this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId'));
            $this->db->update('database_summary', ['databasesummaryTotalMusings' => $num]);
            // remove any keywords associated with metadata
            $subStmt = $this->db->subQuery($this->db->selectNoExecute('resource_metadata', 'resourcemetadataId'), FALSE, FALSE, TRUE);
            $this->db->formatConditions($this->db->formatFields('resourcekeywordMetadataId') . $this->db->inClause($subStmt, TRUE));
            $recordset = $this->db->select('resource_keyword', 'resourcekeywordMetadataId');
            while ($row = $this->db->fetchRow($recordset))
            {
                $deleteIds[] = $row['resourcekeywordMetadataId'];
            }
            if (empty($deleteIds))
            {
                return TRUE; // nothing to do
            }
            $this->db->formatConditionsOneField($deleteIds, 'resourcekeywordMetadataId');
            $this->db->delete("resource_keyword");
            $keyword = FACTORY_KEYWORD::getInstance();
            $keyword->removeHanging();
        }

        return TRUE;
    }
    
    /**
     * Write ldap user details to WKX_users
     *
     * We don't store the LDAP password just 'LDAP' to indicate a LDAP user
     *
     * @param string $usersUsername Username
     * @param array $ldapUserEntry User ldap info get from checkPasswordLdap()
     */
    private function writeLdapUser($usersUsername, $ldapUserEntry)
    {
        // The characters case described by the standard in not reliable
        // Go for a lowercase everywhere
        // cf. https://docs.bmc.com/docs/fpsc121/ldap-attributes-and-associated-fields-495323340.html
        $ldapUserEntry = array_change_key_case($ldapUserEntry, CASE_LOWER);
        
        // Login
        $field[] = 'usersUsername';
        $value[] = $usersUsername;
        $updateArray['usersUsername'] = $usersUsername;
        
        // Fake password
        $field[] = 'usersPassword';
        $value[] = 'LDAP';
        $updateArray['usersPassword'] = 'LDAP';
        
        // Mail (the first non empty)
        $field[] = 'usersEmail';
        $usersEmail = "";
        if (WIKINDX_LDAP_USER_ATTRIBUTE_EMAIL != "")
        {
            $field_search = mb_strtolower(WIKINDX_LDAP_USER_ATTRIBUTE_EMAIL);
            
            if (array_key_exists($field_search, $ldapUserEntry))
            {
                for ($k = 0; $k < $ldapUserEntry["count"]; $k++)
                {
                    $usersEmail = $ldapUserEntry[$field_search][$k];
                    if ($usersEmail != "")
                    {
                        break;
                    }
                }
            }
        }
        $value[] = $usersEmail;
        $updateArray['usersEmail'] = $usersEmail;
        
        // Fullname (the first non empty)
        // Search the real Display Name
        $field[] = 'usersFullname';
        $usersFullname = "";
        if (WIKINDX_LDAP_USER_ATTRIBUTE_FULLNAME != "")
        {
            $field_search = mb_strtolower(WIKINDX_LDAP_USER_ATTRIBUTE_FULLNAME);
            
            if (array_key_exists($field_search, $ldapUserEntry))
            {
                // displayName = Display Name
                for ($k = 0; $k < $ldapUserEntry["count"]; $k++)
                {
                    $usersFullname = $ldapUserEntry[$field_search][$k];
                    if ($usersFullname != "")
                    {
                        break;
                    }
                }
            }
        }
        // Or use the user login as a Display Name
        if ($usersFullname == "")
        {
            $usersFullname = $usersUsername;
        }
        $value[] = $usersFullname;
        $updateArray['usersFullname'] = $usersFullname;
        
        // Retrieve user data
        $this->db->formatConditions(['usersUsername' => $usersUsername]);
        $recordset = $this->db->select('users', ['usersId']);
        
        // Write user data
        if ($this->db->numRows($recordset) == 1)
        {
            // Update the user table
            $row = $this->db->fetchRow($recordset);
            $this->db->formatConditions(['usersId' => $row['usersId']]);
            // Prevents superadmin password from being overwritten in the event of a bug
            $this->db->formatConditions(['usersId' => WIKINDX_SUPERADMIN_ID], "!=");
            $this->db->update('users', $updateArray);
        }
        else
        {
            // Insert into the user table
            $this->db->insert('users', $field, $value);
            $userId = $this->db->lastAutoId();
            $this->writePreferences($userId, TRUE);
        }
    }
    
    /**
     * Compare encrypted passwords on WIKINDX
     *
     * Return FALSE for password not found or password doesn't match.
     * Superadmin is always id = 1
     *
     * @param string $usersUsername
     * @param string $pwdInput
     * @param bool $bSuperAdmin If TRUE, restrict verification to superadmin account
     *
     * @return bool
     */
    private function checkPasswordBuiltin($usersUsername, $pwdInput, $bSuperAdmin = FALSE)
    {
        $this->db->formatConditions(['usersUsername' => $usersUsername], '=');
        if ($bSuperAdmin)
        {
            $this->db->formatConditions(['usersId' => WIKINDX_SUPERADMIN_ID], '=');
        }
        $recordset = $this->db->select('users', ['usersId', 'usersPassword']);
        
        if ($this->db->numRows($recordset) == 1)
        {
            $row = $this->db->fetchRow($recordset);

            return \UTILS\password_verify($pwdInput, $row['usersPassword']);
        }
        
        return FALSE;
    }
    
    /**
     * Check if the user has been blocked by an admin
     *
     * @param string $login
     *
     * @return bool
     */
    private function checkBlocked($login)
    {
        // Return one row if the user is blocked and not the superadmin
        $this->db->formatConditions(['usersUsername' => $login], "=");
        $this->db->formatConditions(['usersBlock' => 'Y'], "=");
        $this->db->formatConditions(['usersId' => WIKINDX_SUPERADMIN_ID], "!=");
        $recordset = $this->db->select('users', ['usersId']);

        return ($this->db->numRows($recordset) == 1);
    }
    
    /**
     * Check if the user is disallowed to auth in single mode
     *
     * Only the superadmin can be logged in if the multiuser mode is not enable.
     *
     * @param string $login
     *
     * @return bool
     */
    private function checkDisallowedInSingleMode($login)
    {
        $this->db->formatConditions(['usersUsername' => $usersUsername], '=');
        $this->db->formatConditions(['usersId' => WIKINDX_SUPERADMIN_ID], '=');
        $recordset = $this->db->select('users', ['usersId']);

        return (!WIKINDX_MULTIUSER) && ($this->db->numRows($recordset) != 1);
    }
    
    /**
     * Format a login for ldap with the domain configured
     *
     * Use it only for the binding user.
     *
     * @param string $login
     *
     * @return string
     */
    private function formatLdapLogin($login)
    {
        $format = \UTILS\array_value_select(
            WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT_LIST,
            WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT,
            WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT_DEFAULT
        );
        
        if (WIKINDX_LDAP_SERVER_BIND_DOMAIN == "")
        {
            $login_formated = $login; // No format needed
        }
        elseif (WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT == "upn")
        {
            $login_formated = $login . "@" . WIKINDX_LDAP_SERVER_BIND_DOMAIN; // user@domain.example.com
        }
        elseif (WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT == "sam")
        {
            $login_formated = WIKINDX_LDAP_SERVER_BIND_DOMAIN . "\\" . $login; // DOMAIN\user
        }
        elseif (WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT == "none")
        {
            $login_formated = $login; // No format requested
        }
        
        return $login_formated;
    }
}
