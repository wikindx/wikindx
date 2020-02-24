<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
    /** object */

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
     * When adding a user, we need to check there is not already $username (caseless) in the database.
     * NB - returns FALSE in case of success, an error message in case of failure.
     *
     * @param bool $add Default is TRUE
     * @param int $admin Default is 0
     *
     * @return mixed
     */
    public function writeUser($add = TRUE, $admin = 0)
    {
        if (array_key_exists('username', $this->vars))
        {
            $username = \HTML\removeNl($this->vars['username']);
            // check for existing usernames (remove current user from search if already logged in with setup_userId)
            $this->db->formatConditions(['usersUsername' => $username]);
            if ($userId = $this->session->getVar('setup_UserId') && !$add)
            {
                $this->db->formatConditions(['usersId' => $userId], TRUE); // Not equal to
            }
            // existing user with that username found (not this user)
            $recordset = $this->db->select('users', 'usersId');
            if ($this->db->numRows($recordset))
            {
                return $this->errors->text("inputError", "userExists");
            }
        }
        $password = \HTML\removeNl($this->vars['password']);
        if (!$add)
        { // update
            if (!$admin)
            { // user editing own details
                $userId = $this->session->getVar('setup_UserId');
                $cookie = FACTORY_COOKIE::getInstance();
                if (array_key_exists('cookie', $this->vars) && $this->vars['cookie'])
                {
                    $cookie->storeCookie($this->vars['uname']);
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
            { // superadmin configuration - userId always 1 for superadmin
                $userId = 1;
            }
            elseif ($admin == 2)
            { // admin editing user
                $userId = $this->vars['userId'];
                if (array_key_exists('admin', $this->vars) && $this->vars['admin'])
                {
                    $update['usersAdmin'] = 'Y';
                }
                else
                {
                    $update['usersAdmin'] = 'N';
                }
                if (array_key_exists('department', $this->vars) && ($dept = trim($this->vars['department'])))
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
                if (array_key_exists('institution', $this->vars) && ($inst = trim($this->vars['institution'])))
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
            if (isset($nulls))
            {
                $this->db->formatConditions(['usersId' => $userId]);
                $this->db->updateNull('users', $nulls);
            }
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
                    $password = crypt($password, UTF8::mb_strrev(time()));
                }
                else
                {
                    $this->pwdInputEncrypted = TRUE;
                }
            }
            $update['usersPassword'] = $password;
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
                $update['usersUsername'] = $username;
            }
            else
            {
                $update['usersEmail'] = $this->vars['email'];
            }
            $this->db->formatConditions(['usersId' => $userId]);
            $this->db->update('users', $update);
        }
        else
        { // insert new user
            $password = crypt($password, UTF8::mb_strrev(time()));
            $field[] = 'usersUsername';
            $value[] = $username;
            $field[] = 'usersPassword';
            $value[] = $password;
            if (array_key_exists('email', $this->vars) &&
                $fullname = \HTML\removeNl($this->vars['email']))
            {
                $field[] = 'usersEmail';
                $value[] = $this->vars['email'];
            }
            if ($admin == 1)
            { // if == 0, default db field value is 'N'
                $field[] = 'usersAdmin';
                $value[] = 'Y';
                $field[] = 'usersFullname';
                $value[] = 'superAdmin';
            }
            elseif ($admin == 2)
            { // admin editing a user
                if (array_key_exists('admin', $this->vars) && $this->vars['admin'])
                {
                    $field[] = 'usersAdmin';
                    $value[] = 'Y';
                }
                if (array_key_exists('department', $this->vars) && ($dept = trim($this->vars['department'])))
                {
                    $field[] = 'usersDepartment';
                    $value[] = $dept;
                }
                elseif (array_key_exists('departmentId', $this->vars) && ($dept = $this->vars['departmentId']))
                {
                    $field[] = 'usersDepartment';
                    $value[] = $dept;
                }
                if (array_key_exists('institution', $this->vars) && ($inst = trim($this->vars['institution'])))
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
            $field[] = 'usersTimestamp';
            $value[] = '2012-01-01 01:01:01';
            $field[] = 'usersNotifyTimestamp';
            $value[] = '2012-01-01 01:01:01';
            $this->db->insert('users', $field, $value);
            $userId = $this->db->lastAutoId();
            // set the users.notifyTimestamp and users.timestamp to current date
            $this->db->formatConditions(['usersId' => $userId]);
            $this->db->updateTimestamp('users', ['usersNotifyTimestamp' => 'CURRENT_TIMESTAMP', 'usersTimestamp' => 'CURRENT_TIMESTAMP']);
            // write userId to session if not adding a new user
            if (!$add)
            {
                $this->session->setVar('setup_UserId', $userId);
            }
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
     * @param string $username
     * @param string $pwdInput
     *
     * @return bool
     */
    public function checkPassword($username, $pwdInput)
    {
        if (WIKINDX_LDAP_USE !== FALSE)
        {
            return $this->ldapCheckPassword($username, $pwdInput);
        }
        else
        {
            return $this->wikindxCheckPassword($username, $pwdInput);
        }
    }
    /**
     * Write ldap user details to WKX_users
     *
     * We don't store the LDAP password just 'LDAP' to indicate a LDAP user
     *
     * @param array $info ldap info
     * @param string $username username
     *
     * @return int
     */
    public function writeLdapUser($info, $username)
    {
        $field[] = 'usersUsername';
        $value[] = $username;
        $field[] = 'usersPassword';
        $value[] = 'LDAP';
        $field[] = 'usersEmail';
        $value[] = $info[0]['mail'][0];
        $field[] = 'usersFullname';
        $value[] = $info[0]['cn'][0];
        $field[] = 'usersTimestamp';
        $value[] = '2012-01-01 01:01:01';
        $field[] = 'usersNotifyTimestamp';
        $value[] = '2012-01-01 01:01:01';
        $this->db->insert('users', $field, $value);
        $userId = $this->db->lastAutoId();
        // set the users.notifyTimestamp and users.timestamp to current date
        $this->db->formatConditions(['usersId' => $userId]);
        $this->db->updateTimestamp('users', ['usersNotifyTimestamp' => 'CURRENT_TIMESTAMP', 'usersTimestamp' => 'CURRENT_TIMESTAMP']);
        // insert preferences to table
        $this->writePreferences($userId);

        return $userId; // success!
    }
    /**
     * set up user environment on first logon
     *
     * @param array $row
     * @param string $username Default is FALSE
     */
    public function environment($row, $username = FALSE)
    {
        // First delete any pre-existing session
        $this->session->clearSessionData();
        if ($row['usersAdmin'] == 'Y')
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
        // If $username, we are logging in without using a cookie.  Check if we require a cookie to be set.
        if (array_key_exists('usersCookie', $row) && ($row['usersCookie'] == 'Y'))
        {
            $cookie = FACTORY_COOKIE::getInstance();
            $cookie->storeCookie($username);
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
        if (!$this->session->getVar('setup_MultiUser'))
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
        $userName = "";
        if ($addId)
        {
            $this->db->formatConditions(['usersId' => $addId]);
            $recordset = $this->db->select('users', ['usersUsername', 'usersFullname']);
            $row2 = $this->db->fetchRow($recordset);
            if ($row2['usersFullname'])
            {
                $userName = $row2['usersFullname'];
            }
            elseif ($row2['usersUsername'])
            {
                $userName = $row2['usersUsername'];
            }
        }
        return $userName;
    }
    /**
     * Store user preferences in the session setup_ array
     *
     * Can come from users or config (default values for readOnly user in which case $userId = FALSE)
     *
     * @param int $userId
     * @param string $table
     * @param bool $upgrade Default is FALSE
     *
     * @return bool
     */
    public function writeSessionPreferences($userId, $table = 'users', $upgrade = FALSE)
    {
        $co = FACTORY_CONFIGDBSTRUCTURE::getInstance();
        $bib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
/*        if ($table == 'users')
        {
            $basic = ["Paging", "PagingMaxLinks", "StringLimit",
                "Language", "Style", "Template", "PagingStyle", "PagingTagCloud", "UseBibtexKey",
                "UseWikindxKey", "DisplayBibtexLink", "DisplayCmsLink", "CmsTag", "ListLink", "TemplateMenu", ];
            $preferences = $this->db->prependTableToField($table, $basic);
            if ($userId)
            {
                $this->db->formatConditions(['usersId' => $userId]);
            }
            $recordset = $this->db->select($table, $preferences);
            if (!$this->db->numRows($recordset))
            {
                return FALSE;
            }
            $row = $this->db->fetchRow($recordset);
        }
        else
        { // config table
            $table = 'config';
            $basic = ["Paging", "PagingMaxLinks", "StringLimit", "Language", "Style", "Template", "PagingTagCloud", "ListLink"];
            $preferences = $this->db->prependTableToField($table, $basic);
            $row = $co->getData($preferences);
        }
*/        /**
         * Check requested style plug-in has not been deleted.  If so, return first in list so that something is safely
         * displayed when listing bibliographies.  Not required after upgrading to v4
         */
/*        if (!$upgrade)
        {
            $styles = \LOADSTYLE\loadDir();
            if (!array_key_exists($row[$table . 'Style'], $styles))
            {
                $styleKeys = array_keys($styles);
                $row['style'] = array_shift($styleKeys);
            }
        }
        $table = str_replace('_', '', $table);
        foreach ($basic as $pref)
        {
            if (($pref == 'PagingStyle') || ($pref == 'UseWikindxKey') || ($pref == 'UseBibtexKey')
                 || ($pref == 'DisplayBibtexLink') || ($pref == 'DisplayCmsLink') || ($pref == 'ListLink'))
            {
                if (array_key_exists($table . $pref, $row))
                {
                    if ($row[$table . $pref] == 'N')
                    {
                        $this->session->delVar("setup_$pref");
                    }
                    elseif ($pref == 'PagingStyle')
                    {
                        $this->session->setVar('setup_PagingStyle', $row[$table . $pref]);
                    }
                    else
                    {
                        $this->session->setVar("setup_$pref", $row[$table . $pref]);
                    }
                }
            }
            elseif ($pref == $table . 'CmsTag')
            {
                if ($row[$pref])
                {
                    $cms = unserialize(base64_decode($row[$pref]));
                    $this->session->setVar("setup_CmsTagStart", $cms[0]);
                    $this->session->setVar("setup_CmsTagEnd", $cms[1]);
                }
                else
                {
                    $this->session->delVar("setup_CmsTagStart");
                    $this->session->delVar("setup_CmsTagEnd");
                }
            }
            else
            {
                $this->session->setVar("setup_" . $pref, $row[$table . $pref]);
            }
        }
*/        // The system also requires userRegistration, notify, multiUser, maxPaste fileAttach and fileViewLoggedOnOnly etc. from
        // WKX_config
        $fields = $this->db->prependTableToField('config', ['UserRegistration', 'MultiUser', 'Notify',
            'FileAttach', 'FileViewLoggedOnOnly', 'MaxPaste', 'LastChanges', 'LastChangesType', 'ImportBib',
            'LastChangesDayLimit', 'Quarantine', 'ListLink', 'MetadataAllow', 'MetadataUserOnly', 'ImgWidthLimit', 'ImgHeightLimit', ]);
        $row = $co->getData($fields);
        if (array_key_exists('configUserRegistration', $row) && $row['configUserRegistration'])
        {
            $this->session->setVar("setup_UserRegistration", TRUE);
        }
        $this->session->setVar("setup_MultiUser", $row['configMultiUser']);
        if (array_key_exists('configNotify', $row) && $row['configNotify'])
        {
            $this->session->setVar("setup_Notify", $row['configNotify']);
        }
        if (array_key_exists('configFileAttach', $row) && $row['configFileAttach'])
        {
            $this->session->setVar("setup_FileAttach", TRUE);
        }
        if (array_key_exists('configFileViewLoggedOnOnly', $row) && $row['configFileViewLoggedOnOnly'])
        {
            $this->session->setVar("setup_FileViewLoggedOnOnly", TRUE);
        }
        if (array_key_exists('configImportBib', $row) && $row['configImportBib'])
        {
            $this->session->setVar("setup_ImportBib", TRUE);
        }
        $this->session->setVar("setup_MaxPaste", $row['configMaxPaste']);
        $this->session->setVar("setup_LastChanges", $row['configLastChanges']);
        $this->session->setVar("setup_LastChangesDayLimit", $row['configLastChangesDayLimit']);
        $this->session->setVar("setup_LastChangesType", $row['configLastChangesType']);
        $this->session->setVar("setup_Quarantine", $row['configQuarantine']);
        $this->session->setVar("setup_MetadataAllow", $row['configMetadataAllow']);
        $this->session->setVar("setup_MetadataUserOnly", $row['configMetadataUserOnly']);
        $this->session->setVar("config_configImgWidthLimit", $row['configImgWidthLimit']);
        $this->session->setVar("config_configImgHeightLimit", $row['configImgHeightLimit']);
        if ($userId)
        {
            $this->session->setVar("setup_UserId", $userId);
        }
        elseif (array_key_exists('configListLink', $row) && $row['configListLink'])
        {
            $this->session->setVar("setup_ListLink", $row['configListLink']);
        }
        $bibs = $bib->getUserBibs();
        if (empty($bibs))
        {
            $bibs = $bib->getGroupBibs();
        }
        if (!empty($bibs))
        {
            $this->session->setVar("setup_Bibliographies", TRUE);
        }

        return TRUE;
    }
    /**
     * Load user details into session mywikindx_ array
     *
     * @param int $id Default is FALSE
     */
    public function loadSession($id = FALSE)
    {
        if (!$id)
        {
            $id = $this->session->getVar("setup_UserId");
        }
        $userArray = ["usersUsername", "usersPassword", "usersEmail", "usersFullname", "usersAdmin", "usersCookie",
            "usersDepartment", "usersInstitution", "usersIsCreator", ];
        for ($i = 1; $i < 4; $i++)
        {
            $userArray[] = "usersPasswordQuestion$i";
        }
        $this->db->formatConditions(['usersId' => $id]);
        $recordset = $this->db->select('users', $userArray);
        $row = $this->db->fetchRow($recordset);
        foreach ($userArray as $key)
        {
            $varName = "mywikindx_" . str_replace('users', '', $key);

            if (($key == 'admin') || ($key == 'cookie'))
            {
                if ($row[$key] == 'Y')
                {
                    $this->session->setVar($varName, TRUE);
                }
                else
                {
                    $this->session->delVar($varName);
                }
            }
            elseif ($row[$key])
            {
                $this->session->setVar($varName, $row[$key]);
            }
        }
        //		$this->bib->grabBibliographies($id);
    }
    /**
     * display user details from users table
     *
     * If $admin != FALSE, it is the user id that an admin is editing
     *
     * @param string $form
     * @param string $hidden
     * @param int $admin Default is FALSE
     *
     * @return string
     */
    public function displayUserDetails($form, $hidden, $admin = FALSE)
    {
        $password = FACTORY_PASSWORD::getInstance();
        list($formText, $jsString) = $password->createElements(FALSE);
        $pString = \FORM\formHeader($form, 'onsubmit="return checkForm(' . $jsString . ');"');
        $pString .= \FORM\hidden('method', $hidden);
        $sessVar = \HTML\dbToFormTidy($this->session->getVar("mywikindx_Username"));
        $pString .= \FORM\hidden("uname", $sessVar);
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\HTML\strong($this->messages->text("user", "username")) . ":&nbsp;&nbsp;$sessVar" .
            BR . "&nbsp;");
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= $formText;
        $sessVar = \HTML\dbToFormTidy($this->session->getVar("mywikindx_Email"));
        $pString .= \HTML\td(\FORM\textInput($this->messages->text("user", "email"), "email", $sessVar, 20, 255)
             . " " . \HTML\span('*', 'required'));
        $sessVar = \HTML\dbToFormTidy($this->session->getVar("mywikindx_Fullname"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "fullname"),
            "fullname",
            $sessVar,
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
            $td = \FORM\textInput($this->messages->text("user", "department"), "department", FALSE, 30, 255);
            $resultset = $this->db->select('users', 'usersDepartment', TRUE);
            $initial = FALSE;
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
                $sessVar = \HTML\dbToFormTidy($this->session->getVar("mywikindx_Department"));
                $td .= BR . \FORM\selectedBoxValue('', "departmentId", $departments, $sessVar, 1);
            }
            $pString .= \HTML\td($td);
            // Institution
            $td = \FORM\textInput($this->messages->text("user", "institution"), "institution", FALSE, 30, 255);
            $resultset = $this->db->select('users', 'usersInstitution', TRUE);
            $initial = FALSE;
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
                $sessVar = \HTML\dbToFormTidy($this->session->getVar("mywikindx_Institution"));
                $td .= BR . \FORM\selectedBoxValue('', "institutionId", $institutions, $sessVar, 1);
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
                $sessVar = \HTML\dbToFormTidy($this->session->getVar("mywikindx_IsCreator"));
                $pString .= \HTML\td(\FORM\selectedBoxValue(
                    $this->messages->text("user", "isCreator"),
                    "creatorId",
                    $creators,
                    $sessVar,
                    1
                ));
            }
            $sessVar = $this->session->getVar("mywikindx_Admin") == 'Y' ? 'CHECKED' : FALSE;
            $pString .= \HTML\td(\FORM\checkbox($this->messages->text("user", "admin"), "admin", $sessVar));
        }
        else
        {
            $sessVar = $this->session->getVar("mywikindx_Cookie") == 'Y' ? 'CHECKED' : FALSE;
            $pString .= \HTML\td(\FORM\checkbox($this->messages->text("user", "cookie"), "cookie", $sessVar));
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
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
        $this->session->setVar('mywikindx_PagingStart', 0);
        $preferences = [
            "Paging" => WIKINDX_PAGING_DEFAULT,
            "PagingMaxLinks" => WIKINDX_PAGING_MAXLINKS_DEFAULT,
            "StringLimit" => WIKINDX_STRING_LIMIT_DEFAULT,
            "Language" => "auto",
            "Style" => WIKINDX_STYLE_DEFAULT,
            "Template" => WIKINDX_TEMPLATE_DEFAULT,
            "PagingStyle" => WIKINDX_PAGINGSTYLE_DEFAULT,
            "PagingTagCloud" => WIKINDX_PAGING_TAG_CLOUD_DEFAULT,
            "UseBibtexKey" => WIKINDX_USEBIBTEXKEY_DEFAULT,
            "UseWikindxKey" => WIKINDX_USEWIKINDXKEY_DEFAULT,
            "DisplayBibtexLink" => WIKINDX_DISPLAYBIBTEXLINK_DEFAULT,
            "DisplayCmsLink" => WIKINDX_DISPLAYCMSLINK_DEFAULT,
            "TemplateMenu" => WIKINDX_TEMPLATEMENU_DEFAULT,
            "ListLink" => "N",
        ];
        foreach ($preferences as $pref => $default)
        {
/*            if ($pref == 'TemplateMenu')
            {
                if ($newUser)
                {
                    $updateArray['users' . $pref] = $default;
                }
                elseif (!GLOBALS::getUserVar('TemplateMenu'))
                { // no level reduction
                    $updateArray['users' . $pref] = WIKINDX_TEMPLATEMENU_DEFAULT;
                }
                else
                {
                    $updateArray['users' . $pref] = GLOBALS::getUserVar('TemplateMenu');
                }

                continue;
            }
*/            if ($newUser)
            {
                $updateArray['users' . $pref] = $default;
            }
            elseif ($value = GLOBALS::getUserVar($pref))
            {
                $updateArray['users' . $pref] = $value;
            }
            else
            {
                $updateArray['users' . $pref] = $default;
            }
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
        $this->db->formatConditions(['usergroupsAdminId' => $this->session->getVar('setup_UserId')]);
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
            $userName = $row['usersUsername'];

            if ($full)
            {
                if ($row['usersFullname'])
                {
                    $userName .= " (" . $row['usersFullname'] . ")";
                }
                if ($row['usersAdmin'] == 'Y')
                {
                    $userName .= " ADMIN";
                }
            }

            $users[$row['usersId']] = \HTML\dbToFormTidy($userName);
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
     * Compare encrypted passwords on WIKINDX
     *
     * Return FALSE for password not found or password doesn't match.
     * Superadmin is always id = 1
     *
     * @param string $username
     * @param string $pwdInput
     *
     * @return bool
     */
    private function wikindxCheckPassword($username, $pwdInput)
    {
        $fields = $this->db->prependTableToField('users', ["Id", "Password", "Admin", "Cookie", "Block"]);
        $this->db->formatConditions(['usersUsername' => $username]);
        $recordset = $this->db->select('users', $fields);
        if (!$this->db->numRows($recordset))
        {
            return FALSE;
        }
        $row = $this->db->fetchRow($recordset);
        // only the superadmin may log on when multi user is not enabled
        if (!WIKINDX_MULTIUSER && ($row['usersId'] != 1))
        {
            return FALSE;
        }
        if (crypt($pwdInput, $row['usersPassword']) != $row['usersPassword'])
        {
            return FALSE;
        }
        // Logged in, check user is not blocked
        if (!$this->checkBlock($row))
        {
            return FALSE;
        }
        // Logged in, now set up environment
        $this->environment($row, $username);

        return TRUE;
    }
    /**
     * Check if user has been blocked by the admin
     *
     * @param array $row
     *
     * @return bool
     */
    private function checkBlock($row)
    {
        if ($row['usersBlock'] == 'Y')
        {
            $this->session->setVar("misc_ErrorMessage", $this->errors->text("warning", "blocked"));

            return FALSE;
        }

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
     * @param string $username
     * @param string $pwdInput
     *
     * @return bool
     */
    private function ldapCheckPassword($username, $pwdInput)
    {
        if (($ds = ldap_connect(WIKINDX_LDAP_SERVER, WIKINDX_LDAP_PORT)) === FALSE)
        {
            $this->session->setVar("misc_ErrorMessage", $this->errors->text("inputError", "ldapConnect"));

            return FALSE;
        }
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, WIKINDX_LDAP_PROTOCOL_VERSION);
        if (($ldapbind = @ldap_bind($ds)) === FALSE)
        {
            $this->session->setVar("misc_ErrorMessage", $this->errors->text("inputError", "ldapBind"));

            return FALSE;
        }
        $sr = @ldap_search($ds, WIKINDX_LDAP_DN, '(uid=' . $username . ')');
        $info = @ldap_get_entries($ds, $sr);
        if ($info['count'] > 1)
        {
            $this->session->setVar("misc_ErrorMessage", $this->errors->text("inputError", "ldapTooManyUsers"));

            return FALSE;
        }
        if ($info['count'] == 1)
        {
            $ldaprdn = $info[0]['dn'];
        }
        else
        {
            $ldaprdn = "cn=" . $username . "," . WIKINDX_LDAP_DN;
        }
        // Connexion au serveur LDAP
        $ldappass = $pwdInput;
        $ldapbind = @ldap_bind($ds, $ldaprdn, $ldappass);
        if ($ldapbind)
        {
            // L'utilisateur est authentifié
            $fields = $this->db->prependTableToField('users', ["Id", "Password", "Admin", "Cookie", "Block"]);
            $this->db->formatConditions(['usersUsername' => $username]);
            $this->db->formatConditions(['usersPassword' => 'LDAP']);
            $recordset = $this->db->select('users', $fields);
            if (!$this->db->numRows($recordset))
            {
                // L'utilisateur n'existe pas on le crée
                $userId = $this->writeLDAPUser($info[0], $username);
                $this->db->formatConditions(['usersId' => $userId]);
                $recordset = $this->db->select('users', $fields);
            }
            $row = $this->db->fetchRow($recordset);
            // only the superadmin may log on when multi user is not enabled
            if (!WIKINDX_MULTIUSER && ($row['usersId'] != 1))
            {
                return FALSE;
            }
            // Logged in, check user is not blocked
            if (!$this->checkBlock($row))
            {
                return FALSE;
            }
            // Logged in, now set up environment
            $this->environment($row, $username);

            return TRUE; // this is our ultimate goal
        }
        else
        {
            $this->session->setVar("misc_ErrorMessage", $this->errors->text("inputError", "ldapBind"));

            return FALSE;
        }
    }
}
