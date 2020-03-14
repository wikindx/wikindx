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
 *	ADMINUSER class.
 *
 *	Administration of users
 */
class ADMINUSER
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $user;
    private $gatekeep;
    private $badInput;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->gatekeep->requireSuper = TRUE;
        $this->gatekeep->init();
    }
    /**
     * init
     *
     * @param string $method
     */
    public function init($method)
    {
        $this->$method();
    }
    /**
     * Add a user - display options
     *
     * @param false|string $error
     */
    public function addInit($error = FALSE)
    {
        $creator = FACTORY_CREATOR::getInstance();
        $password = FACTORY_PASSWORD::getInstance();
        $creators = $creator->grabAll(FALSE, FALSE, FALSE, TRUE);
        if (is_array($creators)) {
            // add 0 => IGNORE to creators array
            $temp[0] = $this->messages->text("misc", "ignore");
            foreach ($creators as $key => $value) {
                $temp[$key] = $value;
            }
            $creators = $temp;
            unset($temp);
        }
        list($formText, $jsString) = $password->createElements(TRUE);
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userAdd"));
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= \FORM\formHeader('admin_ADMINUSER_CORE', 'onsubmit="return checkForm(' . $jsString . ');"');
        $pString .= \FORM\hidden('method', 'add');
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= $formText;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "email"),
            "email",
            FALSE,
            30,
            255
        ) . " " . \HTML\span('*', 'required'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\textInput($this->messages->text("user", "fullname"), "fullname", FALSE, 30, 255));
        // Department
        $td = \FORM\textInput($this->messages->text("user", "department"), "department", FALSE, 30, 255);
        $resultset = $this->db->select('users', 'usersDepartment', TRUE);
        $initial = FALSE;
        while ($row = $this->db->fetchRow($resultset)) {
            $department = \HTML\dbToFormTidy($row['usersDepartment']);
            if (!$department) {
                continue;
            }
            $departments[$department] = $department;
        }
        if (isset($departments)) {
            // add 0 => IGNORE to departments array
            $temp[0] = $this->messages->text("misc", "ignore");
            foreach ($departments as $key => $value) {
                $temp[$key] = $value;
            }
            $departments = $temp;
            unset($temp);
            $td .= BR . \FORM\selectFBoxValue(FALSE, "departmentId", $departments, 1);
        }
        $pString .= \HTML\td($td);
        // Institution
        $td = \FORM\textInput($this->messages->text("user", "institution"), "institution", FALSE, 30, 255);
        $resultset = $this->db->select('users', 'usersInstitution', TRUE);
        $initial = FALSE;
        while ($row = $this->db->fetchRow($resultset)) {
            $institution = \HTML\dbToFormTidy($row['usersInstitution']);
            if (!$institution) {
                continue;
            }
            $institutions[$institution] = $institution;
        }
        if (isset($institutions)) {
            // add 0 => IGNORE to insitutions array
            $temp[0] = $this->messages->text("misc", "ignore");
            foreach ($institutions as $key => $value) {
                $temp[$key] = $value;
            }
            $institutions = $temp;
            unset($temp);
            $td .= BR . \FORM\selectFBoxValue(FALSE, "institutionId", $institutions, 1);
        }
        $pString .= \HTML\td($td);
        if (is_array($creators)) {
            $pString .= \HTML\td(\FORM\selectFBoxValue(
                $this->messages->text("user", "isCreator"),
                "creatorId",
                $creators,
                1
            ));
        }
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("user", "admin"), "admin", FALSE));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")), FALSE, "left");
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     *Add a user
     */
    public function add()
    {
        if (!$this->validateInput('adminAdd')) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, "addInit");
        }
        // NB - writeUser returns FALSE on success!
        if ($this->user->writeUser(TRUE, 2)) {
            $this->badInput->close($this->errors->text("inputError", "userExists"), $this, "addInit");
        }
        include_once("core/modules/email/EMAIL.php");
        $emailClass = new EMAIL();
        if (!$emailClass->registerUserAdd(TRUE)) { // Display password in email to new user
            $this->badInput->close($this->success->text("userAdd") .
            $this->errors->text("inputError", "mail", GLOBALS::getError()), $this, 'addInit');
        }
        GLOBALS::addTplVar('content', $this->success->text("userAdd"));

        return $this->init('addInit');
    }
    /**
     * Delete users display
     *
     * @param false|string $error
     */
    public function deleteInit($error = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userDelete"));
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $this->grabUsers();
        if (empty($this->users)) {
            $this->badInput->close($this->messages->text("resources", "noUsers"), $this, "addInit");
        }
        $pString .= \FORM\formHeader('admin_ADMINUSER_CORE');
        $pString .= \FORM\hidden('method', 'deleteConfirm');
        $pString .= \FORM\selectFBoxValueMultiple(FALSE, "userDelete", $this->users, 20);
        $pString .= BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint')
            . BR;
        $pString .= BR . \FORM\formSubmit($this->messages->text("submit", "Delete"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Ask for confirmation of delete users
     */
    public function deleteConfirm()
    {
        if (!$input = $this->validateInput('delete')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'deleteInit');
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userDelete"));
        $this->grabUsers();
        if (empty($this->users)) {
            $this->badInput->close($this->messages->text("resources", "noUsers"), $this, "addInit");
        }
        $users = "'" . implode("', '", array_keys(array_intersect(
            array_flip($this->users),
            $this->vars['userDelete']
        ))) . "'";
        $pString = \HTML\p($this->messages->text("user", "deleteConfirm", $users));
        $pString .= \FORM\formHeader('admin_ADMINUSER_CORE');
        $pString .= \FORM\hidden('method', 'delete');
        foreach ($this->vars['userDelete'] as $id) {
            $pString .= \FORM\hidden("userDelete_" . $id, $id);
        }
        $array = [0 => $this->messages->text("user", "deleteUserMetadata2"),
            1 => $this->messages->text("user", "deleteUserMetadata3"),
            2 => $this->messages->text("user", "deleteUserMetadata4"), ];
        $pString .= \FORM\selectFBoxValue(
            $this->messages->text("user", "deleteUserMetadata1"),
            "userMetadata",
            $array,
            3
        );
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Confirm")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete user(s)
     */
    public function delete()
    {
        if (!$input = $this->validateInput('deleteConfirm')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'deleteInit');
        }
        // $input is an array of user IDs
        if (!$this->user->deleteSql($input)) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'deleteInit');
        }

        return $this->addInit($this->success->text("userDelete"));
    }
    /**
     * Block users display
     *
     * @param false|string $error
     */
    public function blockInit($error = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userBlock"));
        $pString = '<script type="text/javascript" src="' . \WIKINDX_BASE_URL .
            '/core/modules/admin/adminUser.js"></script>';
        $pString .= $error ? \HTML\p($error, "error", "center") : '';
        $this->grabUsers();
        if (empty($this->users)) {
            $this->badInput->close($this->messages->text("resources", "noUsers"), $this, "addInit");
        }
        $authUsers = $this->grabAuthUsers(TRUE);
        // don't allow this user to operate on self or superadmin (id == 1)
        $this->selfId = $this->session->getVar("setup_UserId");
        if (array_key_exists($this->selfId, $authUsers)) {
            unset($authUsers[$this->selfId]);
        }
        if (array_key_exists(1, $authUsers)) {
            unset($authUsers[1]);
        }
        $blockedUsers = $this->grabAuthUsers(FALSE);
        $pString .= \FORM\formHeader('admin_ADMINUSER_CORE', "onsubmit=\"selectAll();return true;\"");
        $pString .= \FORM\hidden('method', 'block');
        $pString .= \HTML\tableStart('generalTable', 'borderStyleSolid', 0, "left");
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", 'multiples'));
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("user", 'authorizedUsers'),
            'authorizedUsers',
            $authUsers,
            10
        ) . BR . \HTML\span($hint, 'hint'), 'padding3px left width18percent');
        list($toRightImage, $toLeftImage) = $this->transferArrows();
        $pString .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'padding3px left width5percent');
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", 'multiples'));
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text("user", 'blockedUsers'),
            'blockedUsers',
            $blockedUsers,
            10
        ) . BR . \HTML\span($hint, 'hint'), 'padding3px left width18percent');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= BR . \FORM\formSubmit($this->messages->text("submit", "Proceed"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Actually block or unblock users
     */
    public function block()
    {
        if (array_key_exists('authorizedUsers', $this->vars) && count($this->vars['authorizedUsers'])) {
            $this->db->formatConditionsOneField($this->vars['authorizedUsers'], 'usersId');
            $this->db->update('users', ['usersBlock' => 'N']);
        }
        if (array_key_exists('blockedUsers', $this->vars) && count($this->vars['blockedUsers'])) {
            $this->db->formatConditionsOneField($this->vars['blockedUsers'], 'usersId');
            $this->db->update('users', ['usersBlock' => 'Y']);
        }
        $pString = $this->success->text("userBlock");

        return $this->blockInit($pString);
    }
    /**
     * display users for editing
     *
     * @param false|string $error
     */
    public function editInit($error = FALSE)
    {
        $this->session->clearArray('mywikindx');
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userEdit"));
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= \FORM\formHeader('admin_ADMINUSER_CORE');
        $pString .= \FORM\hidden('method', 'editDisplay');
        $this->grabUsers();
        if (empty($this->users)) {
            $this->badInput->close($this->messages->text("resources", "noUsers"), $this, "addInit");
        }
        $pString .= \FORM\selectFBoxValue(FALSE, "editId", $this->users, 20) . BR .
            \FORM\formSubmit($this->messages->text("submit", "Edit"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * display one user for editing
     *
     * We temporarily use the session array 'mywikindx_' for storage so we can use the USER class methods.
     *
     * @param false|string $error
     */
    public function editDisplay($error = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userEdit"));
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $this->userId = FALSE;
        if (array_key_exists("editId", $this->vars)) {
            $this->userId = trim($this->vars["editId"]);
        } elseif (array_key_exists("userId", $this->vars)) {
            $this->userId = trim($this->vars["userId"]);
        }
        if (!$this->validateInput('editId')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'editInit');
        }
        $this->user->loadSession($this->userId);
        $pString .= $this->user->displayUserDetails('admin_ADMINUSER_CORE', 'edit', $this->userId);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Edit user
     */
    public function edit()
    {
        if (!$this->validateInput('edit')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'editDisplay');
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userEdit"));
        // writeUser() returns FALSE for success
        if ($this->user->writeUser(FALSE, 2)) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'editDisplay');
        }
        $pString = $this->success->text("userEdit");

        return $this->editInit($pString);
    }
    /**
     * Show users requesting registration
     *
     * @param false|string $error
     */
    public function registrationInit($error = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        $pString = $error ? \HTML\p($error, "error", "center") : FALSE;
        $this->db->formatConditions(['userregisterConfirmed' => 'N']);
        $this->db->formatConditions(['userregisterRequest' => ' IS NOT NULL']);
        $resultset = $this->db->select('user_register', ['userregisterId', 'userregisterRequest',
            'userregisterEmail', 'userregisterTimestamp', ]);
        if (!$this->db->numRows($resultset)) {
            $pString .= $this->messages->text('user', 'noUsers');
        } else {
            $pString .= \HTML\p($this->messages->text('user', 'pendingRegistration1'));
            $pString .= \HTML\p($this->messages->text('user', 'pendingRegistration2')) . BR;
            $pString .= \FORM\formHeader('admin_ADMINUSER_CORE');
            $pString .= \FORM\hidden('method', 'register');
            $pString .= \HTML\tableStart('generalTable borderStyleSolid');
            while ($row = $this->db->fetchRow($resultset)) {
                $pString .= \HTML\trStart();
                $pString .= \HTML\td($row['userregisterEmail']);
                $pString .= \HTML\td(\HTML\nlToHtml($row['userregisterRequest']));
                $pString .= \HTML\td($row['userregisterTimestamp']);
                $pString .= \HTML\td($this->messages->text('user', 'registrationAccept') . "&nbsp:&nbsp;" .
                    \FORM\radioButton(FALSE, 'registerUser_' . $row['userregisterId'], 'accept', TRUE) .
                    BR . $this->messages->text('user', 'registrationDecline') . "&nbsp:&nbsp;" .
                    \FORM\radioButton(FALSE, 'registerUser_' . $row['userregisterId'], 'decline'));
                $pString .= \HTML\trEnd();
            }
            $pString .= \HTML\tableEnd();
            $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")), FALSE, "left");
            $pString .= \FORM\formEnd();
        }
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Mangage requests for registration
     */
    public function register()
    {
        foreach ($this->vars as $key => $value) {
            if (mb_strpos($key, "registerUser_") !== FALSE) {
                $split = UTF8::mb_explode("_", $key);
                $registerIds[$split[1]] = $value;
            }
        }
        if (!isset($registerIds)) {
            return $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'registrationInit');
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        include_once("core/modules/email/EMAIL.php");
        $emailClass = new EMAIL();
        if (!$emailClass->registerRequestManage($registerIds)) {
            $this->badInput->close($this->errors->text("inputError", "mail2", GLOBALS::getError()), $this, 'registrationInit');
        }
        // Delete those declined and confirm those accepted
        foreach ($registerIds as $id => $value) {
            $this->db->formatConditions(['userregisterId' => $id]);
            if ($value == 'accept') {
                $this->db->update('user_register', ['userregisterConfirmed' => 'Y']);
            } else {
                $this->db->delete('user_register');
            }
        }
        GLOBALS::addTplVar('content', $this->success->text('registerRequestManage'));
    }
    /**
     * Grab users according to whether they are blocked or not
     *
     * @param bool $auth Default TRUE (grab authorized users) else FALSE (grab blocked users)
     *
     * @return array
     */
    private function grabAuthUsers($auth = TRUE)
    {
        $users = [];
        $this->db->orderBy('usersUsername');
        if ($auth) {
            $this->db->formatConditions(['usersBlock' => 'N']);
        } else {
            $this->db->formatConditions(['usersBlock' => 'Y']);
        }
        $recordset = $this->db->select('users', ["usersId", "usersUsername", "usersFullname", "usersAdmin"], TRUE);
        while ($row = $this->db->fetchRow($recordset)) {
            $userName = $row['usersUsername'];
            if ($row['usersFullname']) {
                $userName .= " (" . $row['usersFullname'] . ")";
            }
            if ($row['usersAdmin']) {
                $userName .= " ADMIN";
            }
            $users[$row['usersId']] = \HTML\dbToFormTidy($userName);
        }

        return $users;
    }
    /**
     * Make the user block/unblock select transfer arrows to transfer resources between select boxes with onclick
     *
     * @return array (toRightImage, toLeftImage)
     */
    private function transferArrows()
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'blockUser',
        ];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'authUser',
        ];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);

        return [$toRightImage, $toLeftImage];
    }
    /**
     * validate input
     *
     * @param string $type
     *
     * @return bool|int
     */
    private function validateInput($type)
    {
        if ($type == 'adminAdd') {
            if (!trim($this->vars['username']) || !trim($this->vars['password']) ||
                !trim($this->vars['passwordConfirm']) || !trim($this->vars['email'])) {
                return FALSE;
            }
            if (trim($this->vars['password']) != trim($this->vars['passwordConfirm'])) {
                return FALSE;
            }
        } elseif ($type == 'userAdd') {
            if (!trim($this->vars['username']) || !trim($this->vars['password']) ||
                !trim($this->vars['passwordConfirm']) || !$this->vars['email'] || !$this->vars['hashKey']) {
                return FALSE;
            }
            if (trim($this->vars['password']) != trim($this->vars['passwordConfirm'])) {
                return FALSE;
            }
        } elseif ($type == 'register') {
            if (!trim($this->vars['email'])) {
                return FALSE;
            }
        } elseif ($type == 'delete') {
            if (!array_key_exists('userDelete', $this->vars) || empty($this->vars['userDelete'])) {
                return FALSE;
            }
        } elseif ($type == 'deleteConfirm') {
            $input = [];
            // Ensure we don't delete superadmin with id of 1
            foreach ($this->vars as $key => $value) {
                if (!$value || ($value == 1)) {
                    continue;
                }
                if (!preg_match("/userDelete_/u", $key)) {
                    continue;
                }
                $input[] = $value;
            }
            if (empty($input)) {
                return FALSE;
            }
        } elseif ($type == 'editId') {
            if (!$this->userId) {
                return FALSE;
            }
        } elseif ($type == 'edit') {
            if (!trim($this->vars['password']) || !trim($this->vars['email']) || !trim($this->vars['userId'])) {
                return FALSE;
            }
            if (trim($this->vars['password']) != trim($this->vars['passwordConfirm'])) {
                return FALSE;
            }
        }
        if (isset($input)) {
            return $input;
        }

        return TRUE;
    }
    /**
     * grab users from database
     */
    private function grabUsers()
    {
        $this->users = $this->user->grabAll(TRUE);
        // don't allow non-superAdmin admins to operate on superadmin (id == WIKINDX_SUPERADMIN_ID)
        $this->selfId = $this->session->getVar("setup_UserId");
        if (array_key_exists(WIKINDX_SUPERADMIN_ID, $this->users) && ($this->selfId != WIKINDX_SUPERADMIN_ID)) {
            unset($this->users[WIKINDX_SUPERADMIN_ID]);
        }
    }
}
