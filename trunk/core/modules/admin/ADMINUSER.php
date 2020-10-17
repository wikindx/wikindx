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
    private $formData = [];

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
     * @param false|string $message
     */
    public function addInit($message = FALSE)
    {
        $creator = FACTORY_CREATOR::getInstance();
        $password = FACTORY_PASSWORD::getInstance();
        list($formText, $jsString) = $password->createElements(TRUE, FALSE, $this->formData);
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userAdd"));
    	if (array_key_exists('message', $this->vars)) {
    		$message = $this->vars['message'];
    	}
        $pString = $message;
        $pString .= \FORM\formHeader('admin_ADMINUSER_CORE', 'onsubmit="return checkForm(' . $jsString . ');"');
        $pString .= \FORM\hidden('method', 'add');
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= $formText;
        $field = array_key_exists('email', $this->formData) ? $this->formData['email'] : FALSE;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("user", "email"),
            "email",
            $field,
            30,
            255
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $field = array_key_exists('fullname', $this->formData) ? $this->formData['fullname'] : FALSE;
        $pString .= \HTML\td(\FORM\textInput($this->messages->text("user", "fullname"), "fullname", $field, 30, 255));
        // Department
        $field = array_key_exists('department', $this->formData) ? $this->formData['department'] : FALSE;
        $td = \FORM\textInput($this->messages->text("user", "department"), "department", $field, 30, 255);
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
        	$field = array_key_exists('departmentId', $this->formData) ? 
        		htmlspecialchars(trim($this->formData['departmentId']), ENT_QUOTES | ENT_HTML5) : FALSE;
        	if ($field) {
	            $td .= BR . \FORM\selectedBoxValue(FALSE, "departmentId", $departments, $field, 1);
        	} else {
	            $td .= BR . \FORM\selectFBoxValue(FALSE, "departmentId", $departments, 1);
	        }
        }
        $pString .= \HTML\td($td);
        // Institution
        $field = array_key_exists('institution', $this->formData) ? $this->formData['institution'] : FALSE;
        $td = \FORM\textInput($this->messages->text("user", "institution"), "institution", $field, 30, 255);
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
        	$field = array_key_exists('institutionId', $this->formData) ? 
        		htmlspecialchars(trim($this->formData['institutionId']), ENT_QUOTES | ENT_HTML5) : FALSE;
        	if ($field) {
	            $td .= BR . \FORM\selectedBoxValue(FALSE, "institutionId", $institutions, $field, 1);
	        } else {
	            $td .= BR . \FORM\selectFBoxValue(FALSE, "institutionId", $institutions, 1);
	        }
        }
        $pString .= \HTML\td($td);
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
        if (is_array($creators)) {
        	$field = array_key_exists('creatorId', $this->formData) ? $this->formData['creatorId'] : 0;
            $pString .= \HTML\td(\FORM\selectedBoxValue(
                $this->messages->text("user", "isCreator"),
                "creatorId",
                $creators,
                $field,
                1
            ));
        }
        $field = array_key_exists('admin', $this->formData) ? TRUE : FALSE;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("user", "admin"), "admin", $field));
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
        $this->validateInput('adminAdd');
        // NB - writeUser returns FALSE on success!
        if ($this->user->writeUser(TRUE, 2)) {
            $this->badInput->close($this->errors->text("inputError", "userExists"), $this, "addInit");
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
        $emailClass = new EMAIL();
        if (!$emailClass->registerUserAdd(TRUE)) { // Display password in email to new user
            $this->badInput->close($this->success->text("userAdd") .
            $this->errors->text("inputError", "mail", GLOBALS::getError()), $this, 'addInit');
        }
        $message = rawurlencode($this->success->text("userAdd"));
        header("Location: index.php?action=admin_ADMINUSER_CORE&method=addInit&message=$message");
        die;
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
        // don't allow this user to operate on self or superadmin (id == 1)
        $selfId = $this->session->getVar("setup_UserId");
        if (array_key_exists($selfId, $this->users)) {
            unset($this->users[$selfId]);
        }
        if (array_key_exists(1, $this->users)) {
            unset($this->users[1]);
        }
        $pString .= \FORM\formHeader('admin_ADMINUSER_CORE');
        $pString .= \FORM\hidden('method', 'deleteConfirm');
        $pString .= \FORM\selectFBoxValueMultiple(FALSE, "userDelete", $this->users, 20);
        $pString .= BR . \HTML\span(\HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", 
            	$this->messages->text("hint", "multiples")), 'hint') . BR;
        $pString .= BR . \FORM\formSubmit($this->messages->text("submit", "Delete"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Ask for confirmation of delete users
     */
    public function deleteConfirm()
    {
        $this->validateInput('delete');
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userDelete"));
        $this->grabUsers();
        if (empty($this->users)) { // should never happen at this point
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
        $this->validateInput('deleteConfirm');
        // $this->formData is an array of user IDs
        if (!$this->user->deleteSql($this->formData)) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'deleteInit');
        }
        $message = rawurlencode($this->success->text("userDelete"));
        header("Location: index.php?action=admin_ADMINUSER_CORE&method=addInit&message=$message");
        die;
    }
    /**
     * Block users display
     *
     * @param false|string $error
     */
    public function blockInit($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userBlock"));
        $pString = '<script src="' . WIKINDX_URL_BASE .
            '/core/modules/admin/adminUser.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
    	if (array_key_exists('message', $this->vars)) {
    		$message = $this->vars['message'];
    	}
        $pString .= $message;
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
        $message = rawurlencode($this->success->text("userBlock"));
        header("Location: index.php?action=admin_ADMINUSER_CORE&method=blockInit&message=$message");
        die;
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
        // don't allow this user to operate on self or superadmin (id == 1)
        $selfId = $this->session->getVar("setup_UserId");
        if (array_key_exists($selfId, $this->users)) {
            unset($this->users[$selfId]);
        }
        if (array_key_exists(1, $this->users)) {
            unset($this->users[1]);
        }
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
     * @param false|string $message
     */
    public function editDisplay($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userEdit"));
    	if (array_key_exists('message', $this->vars)) {
    		$message = $this->vars['message'];
    	}
        $pString = $message;
        $this->userId = FALSE;
        if (array_key_exists("editId", $this->vars)) {
            $this->userId = $this->vars["editId"];
        } elseif (array_key_exists("userId", $this->vars)) {
            $this->userId = $this->vars["userId"];
        }
        $this->validateInput('editId');
        if (empty($this->formData)) { // i.e. initial load
	        $this->formData = $this->user->loadFormData($this->userId);
	    }
        $pString .= $this->user->displayUserDetails('admin_ADMINUSER_CORE', 'edit', $this->userId, $this->formData);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Edit user
     */
    public function edit()
    {
        $this->validateInput('edit');
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userEdit"));
        // writeUser() returns FALSE for success
        if ($this->user->writeUser(FALSE, 2)) { // TRUE on error!
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'editDisplay');
        }
        $message = rawurlencode($this->success->text("userEdit"));
        $userId = $this->formData["userId"];
        header("Location: index.php?action=admin_ADMINUSER_CORE&method=editDisplay&message=$message&userId=$userId");
        die;
    }
    /**
     * Show users requesting registration
     *
     * @param false|string $message
     */
    public function registrationInit($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
    	if (array_key_exists('message', $this->vars)) {
    		$message = $this->vars['message'];
    	}
        $pString = $message;
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
    	$this->validateInput('register');
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
        $emailClass = new EMAIL();
        if (!$emailClass->registerRequestManage($this->formData)) {
            $this->badInput->close($this->errors->text("inputError", "mail2", GLOBALS::getError()), $this, 'registrationInit');
        }
        // Delete those declined and confirm those accepted
        foreach ($this->formData as $id => $value) {
            $this->db->formatConditions(['userregisterId' => $id]);
            if ($value == 'accept') {
                $this->db->update('user_register', ['userregisterConfirmed' => 'Y']);
            } else {
                $this->db->delete('user_register');
            }
        }
        $message = rawurlencode($this->success->text("registerRequestManage"));
        header("Location: index.php?action=admin_ADMINUSER_CORE&method=registrationInit&message=$message");
        die;
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
            $usersUsername = $row['usersUsername'];
            if ($row['usersFullname']) {
                $usersUsername .= " (" . $row['usersFullname'] . ")";
            }
            if ($row['usersAdmin']) {
                $usersUsername .= " ADMIN";
            }
            $users[$row['usersId']] = \HTML\dbToFormTidy($usersUsername);
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
    	$error = $email = '';
        if ($type == 'adminAdd') {
            $function = 'addInit';
            if (!\UTF8\mb_trim($this->vars['usersUsername']) || (!$email = \UTF8\mb_trim($this->vars['email'])) || 
            	!\UTF8\mb_trim($this->vars['password']) || !\UTF8\mb_trim($this->vars['passwordConfirm'])) {
                $error = $this->errors->text("inputError", "missing");
            }
       		elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
        		$error = $this->errors->text('inputError', 'invalidMail');
        	}
            if (\UTF8\mb_trim($this->vars['password']) != \UTF8\mb_trim($this->vars['passwordConfirm'])) {
                $error = $this->errors->text("inputError", "missing");
            }
        // Don't store passwords . . .
            $this->formData['usersUsername'] = \UTF8\mb_trim($this->vars['usersUsername']);
            $this->formData['email'] = $email;
            $this->formData['fullname'] = \UTF8\mb_trim($this->vars['fullname']);
            $this->formData['department'] = \UTF8\mb_trim($this->vars['department']);
            $this->formData['departmentId'] = $this->vars['departmentId'];
            $this->formData['institution'] = \UTF8\mb_trim($this->vars['institution']);
            $this->formData['institutionId'] = $this->vars['institutionId'];
            if (array_key_exists('admin', $this->vars)) {
            	$this->formData['admin'] = TRUE;
            }
            $this->formData['creatorId'] = \UTF8\mb_trim($this->vars['creatorId']);
        } 
// Appears not to be used . . . .
/*        elseif ($type == 'userAdd') {
            if (!\UTF8\mb_trim($this->vars['usersUsername']) || !\UTF8\mb_trim($this->vars['password']) ||
                !\UTF8\mb_trim($this->vars['passwordConfirm']) || !$this->vars['email'] || !$this->vars['hashKey']) {
                return FALSE;
            }
            if (\UTF8\mb_trim($this->vars['password']) != \UTF8\mb_trim($this->vars['passwordConfirm'])) {
                return FALSE;
            }
        } 
*/        elseif ($type == 'register') {
            $function = 'registrationInit';
			foreach ($this->vars as $key => $value) {
				if (mb_strpos($key, "registerUser_") !== FALSE) {
					$split = \UTF8\mb_explode("_", $key);
					$this->formData[$split[1]] = $value;
				}
			}
			if (empty($this->formData)) {
                $error = $this->errors->text("inputError", "missing");
			}
        } elseif ($type == 'delete') {
            if (!array_key_exists('userDelete', $this->vars) || empty($this->vars['userDelete'])) {
                $error = $this->errors->text("inputError", "missing");
            }
        } elseif ($type == 'deleteConfirm') {
            // Ensure we don't delete superadmin with id of 1
            foreach ($this->vars as $key => $value) {
                if (!$value || ($value == 1)) {
                    continue;
                }
                if (!preg_match("/userDelete_/u", $key)) {
                    continue;
                }
                $this->formData[] = $value;
            }
            if (empty($this->formData)) {
                $error = $this->errors->text("inputError", "missing");
            }
        } elseif ($type == 'editId') {
            $function = 'editInit';
            if (!$this->userId) {
                $error = $this->errors->text("inputError", "missing");
            }
        } elseif ($type == 'edit') {
            $function = 'editDisplay';
        	if (!array_key_exists('bypassPasswordCheck', $this->vars)) {
				if (!\UTF8\mb_trim($this->vars['password']) || !\UTF8\mb_trim($this->vars['passwordConfirm'])) {
					$error = $this->errors->text("inputError", "missing");
				}
			}
            if ((!$email = \UTF8\mb_trim($this->vars['email'])) || !$this->vars['userId']) {
                $error = $this->errors->text("inputError", "missing");
            }
       		elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
        		$error = $this->errors->text('inputError', 'invalidMail');
        	}
            $this->formData['usersUsername'] = \UTF8\mb_trim($this->vars['usersUsername']);
            $this->formData['userId'] = $this->vars['userId'];
            $this->formData['email'] = $email;
            $this->formData['fullname'] = \UTF8\mb_trim($this->vars['fullname']);
            $this->formData['department'] = \UTF8\mb_trim($this->vars['department']);
            $this->formData['departmentId'] = $this->vars['departmentId'];
            $this->formData['institution'] = \UTF8\mb_trim($this->vars['institution']);
            $this->formData['institutionId'] = $this->vars['institutionId'];
            if (array_key_exists('admin', $this->vars)) {
            	$this->formData['admin'] = TRUE;
            }
            if (array_key_exists('bypassPasswordCheck', $this->vars)) {
            	$this->formData['bypassPasswordCheck'] = TRUE;
            }
            $this->formData['creatorId'] = $this->vars['creatorId'];
        }
        if ($error) {
	        $this->badInput->close($error, $this, $function);
    	}
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
