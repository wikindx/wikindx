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
 *	EMAIL class.
 */
class EMAIL
{
    private $db;
    private $vars;
    private $smtp;
    private $messages;
    private $errors;
    private $success;
    private $session;
    private $badInput;
    private $title;
    private $bibStyle;
    private $user;
    private $stmt;
    private $res;
    private $usersThreshold = [];
    private $titles = [];
    private $allAddedIds = [];
    private $allEditedIds = [];

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->smtp = FACTORY_MAIL::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
    }
    /**
     * Start functions for emailing users their preferences or 'my wikindx' details.
     *
     * @return bool
     */
    public function userEdit()
    {
        // do nothing if email is not turned on
        if (!WIKINDX_MAIL_USE) {
            return TRUE;
        }
        if (array_key_exists('email', $this->vars)) {
            $email = $this->vars['email'];
            $subject = "WIKINDX Registration Confirmation";
            $message = $this->messages->text("user", "emailText3") . "\n\nWIKINDX:\t\t" . WIKINDX_BASE_URL . "\n\nUSERNAME:\t\t" .
                trim($this->vars['uname']) . "\n" . LF;
            $this->smtp->sendEmail($email, $subject, $message);

            return TRUE;
        }

        return FALSE;
    }
    /**
     * Email a user details of initial self-registration
     *
     * @param mixed $hashKey
     * @param mixed $email
     *
     * @return bool
     */
    public function register($hashKey, $email)
    {
        // do nothing if email is not turned on
        if (!WIKINDX_MAIL_USE) {
            return TRUE;
        }
        $subject = "WIKINDX Registration";
        $link = WIKINDX_BASE_URL . "/index.php?action=usersgroups_REGISTER_CORE&method=registerConfirm&hashKey=$hashKey";
        $message = $this->messages->text("user", "emailText") . LF . LF . $link . LF . LF;

        return ($this->smtp->sendEmail($email, $subject, $message));
    }
    /**
     * Complete user self-registration
     *
     * @param mixed $passwordShow
     *
     * @return bool
     */
    public function registerUserAdd($passwordShow = FALSE)
    {
        // do nothing if email is not turned on
        if (!WIKINDX_MAIL_USE) {
            return TRUE;
        }
        $email = $this->vars['email'];
        $subject = "WIKINDX Registration Confirmation";
        if ($passwordShow) {
            $message = $this->messages->text("user", "emailText2") . "\n\nWIKINDX:\t\t" . WIKINDX_BASE_URL . "\n\nUSERNAME:\t\t" .
                trim($this->vars['usersUsername']) . "\n\nPASSWORD:\t\t" . trim($this->vars['password']) . "\n" . LF;
        } else {
            $message = $this->messages->text("user", "emailText2") . "\n\nWIKINDX:\t\t" . WIKINDX_BASE_URL . "\n\nUSERNAME:\t\t" .
                trim($this->vars['usersUsername']) . "\n" . LF;
        }
        if (!$this->smtp->sendEmail($email, $subject, $message)) {
            return FALSE;
        }
        // If needed, email admin about new user
        $email = WIKINDX_EMAIL_NEW_REGISTRATIONS;
        if ($email && !$this->session->getVar("setup_Superadmin")) {
            $message = "A new user has registered for" . "\n\nWIKINDX:\t\t" . WIKINDX_BASE_URL . "\n\nUSERNAME:\t\t" .
                trim($this->vars['usersUsername']) . "\n" . LF;
            if ($this->vars['fullname']) {
                $message .= "FULLNAME:\t\t" . trim($this->vars['fullname']) . "\n" . LF;
            }
            if (!$this->smtp->sendEmail($email, $subject, $message)) {
                return FALSE;
            }
        }

        return TRUE;
    }
    /**
     * registerRequest
     *
     * @return bool
     */
    public function registerRequest()
    {
        // do nothing if email is not turned on
        if (!WIKINDX_MAIL_USE) {
            return TRUE;
        }
        $adminEmail = WIKINDX_EMAIL_NEW_REGISTRATIONS;
        if (!$adminEmail) {
            return FALSE;
        }
        $subject = "WIKINDX Registration Request";
        $message = $this->messages->text("user", "emailText4") . "\n" . LF;

        return ($this->smtp->sendEmail($adminEmail, $subject, $message));
    }
    /**
     * Admin accepting/declining registration requests
     *
     * @param mixed $registerIds
     *
     * @return bool
     */
    public function registerRequestManage($registerIds)
    {
        foreach ($registerIds as $id => $value) {
            $this->db->formatConditions(['userregisterId' => $id]);
            $this->db->formatConditions(['userregisterConfirmed' => 'N']);
            $resultset = $this->db->select('user_register', ['userregisterEmail', 'userregisterHashKey']);
            $row = $this->db->fetchRow($resultset);
            // Email user
            $email = $row['userregisterEmail'];
            $subject = "WIKINDX Registration Confirmation";
            if ($value == 'accept') {
                $link = WIKINDX_BASE_URL . "/index.php?action=usersgroups_REGISTER_CORE&method=registerConfirm&hashKey=" . $row['userregisterHashKey'];
                $message = $this->messages->text("user", "emailText") . LF . LF . $link . LF . LF;
            } else {
                $message = $this->messages->text("user", "emailText5", " " . WIKINDX_BASE_URL) . LF . LF;
            }
            // do nothing if email is not turned on
            if (WIKINDX_MAIL_USE) {
                if (!$this->smtp->sendEmail($email, $subject, $message)) {
                    return FALSE;
                }
            }
            // Delete those declined and confirm those accepted
            $this->db->formatConditions(['userregisterId' => $id]);
            if ($value == 'accept') {
                $this->db->update('user_register', ['userregisterConfirmed' => 'Y']);
            } else {
                $this->db->delete('user_register');
            }
        }

        return TRUE;
    }
    /**
     * Display form in pop-up for emailing a link to a friend
     *
     * START functions for emailing a resource to a friend
     * Called from index.php
     *
     * @param mixed $error
     */
    public function emailFriendDisplay($error = FALSE)
    {
        if (array_key_exists('id', $this->vars)) {
            $hyperlink = WIKINDX_BASE_URL . "/index.php?action=resource_RESOURCEVIEW_CORE&id=" . $this->vars['id'];
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "emailFriend"));
        $pString = $error ? \HTML\p($error, "error", "center") : FALSE;
        $pString .= \FORM\formHeader("email_EMAIL_CORE");
        $pString .= \FORM\hidden('method', 'emailFriend');
        $address = $this->session->getVar("emailFriend_Address");
        $subject = $this->session->getVar("emailFriend_Subject");
        if (isset($hyperlink)) {
            $text = "\n\n$hyperlink";
        } else {
            $text = $this->session->getVar("emailFriend_Text");
        }
        $pString .= \HTML\p(\FORM\textInput(
            $this->messages->text("misc", "emailFriendAddress"),
            "emailFriend_address",
            $address,
            80
        ) . BR .
            \HTML\span($this->messages->text('hint', 'emailFriendAddress'), 'hint'));
        $pString .= \HTML\p(\FORM\textInput(
            $this->messages->text("misc", "emailFriendSubject"),
            "emailFriend_subject",
            $subject,
            80
        ));
        $pString .= \HTML\p(\FORM\textareaInput(
            $this->messages->text("misc", "emailFriendText"),
            "emailFriend_text",
            $text,
            80,
            10
        ));
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Email")), FALSE, "right");
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Send email to friend
     */
    public function emailFriend()
    {
        // do nothing if email is not turned on
        if (!WIKINDX_MAIL_USE) {
            return TRUE;
        }
        list($addresses, $subject, $text) = $this->checkFriendInput();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "emailFriend"));
        if (!$this->smtp->sendEmail($addresses, $subject, $text)) {
            $this->badInput->close($this->errors->text('inputError', 'mail2'), $this, 'emailFriendDisplay');
        }
        $this->session->clearArray('emailFriend');
        GLOBALS::addTplVar('content', $this->success->text('emailFriend'));
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Emailing username::password to forgetful user
     *
     * @param mixed $usersUsername
     * @param mixed $password
     *
     * @return bool
     */
    public function forgetProcess($usersUsername, $password)
    {
        // do nothing if email is not turned on
        if (!WIKINDX_MAIL_USE) {
            return TRUE;
        }
        $password = time();
        $email = trim($this->vars['email']);
        $subject = "Password Reset";
        $message = $this->messages->text("user", "forget9");
        $message .= LF . LF;
        $message .= $this->title . ":" . str_repeat(TAB, 4) . WIKINDX_BASE_URL;
        $message .= LF . LF;
        $message .= "USERNAME:" . str_repeat(TAB, 4) . $usersUsername;
        $message .= LF . LF;
        $message .= "TEMPORARY PASSWORD:" . str_repeat(TAB, 2) . $password;
        $message .= LF . LF;

        return ($this->smtp->sendEmail($email, $subject, $message));
    }
    /**
     * Email news items to users
     *
     * @param mixed $title
     * @param mixed $news
     *
     * @return bool
     */
    public function news($title, $news)
    {
        // do nothing if email is not turned on
        if (!WIKINDX_MAIL_USE) {
            return TRUE;
        }
        $news = preg_replace('/\<br(\s*)?\/?\>/ui', CR . LF, $news);
        $news = preg_replace('/\<p(\s*)?\/?\>/ui', "\r\n\r" . LF, $news);
        $news = html_entity_decode(\HTML\stripHtml(stripslashes($news)));
        $wikindxTitle = \HTML\stripHtml(WIKINDX_TITLE);
        $subject = "$wikindxTitle News";
        $message = "\n\n$title\n" . LF;
        $message .= "$news\n" . LF;
        $recordset = $this->db->select('users', 'usersEmail');
        while ($row = $this->db->fetchRow($recordset)) {
            if (!$row['usersEmail']) { // This should only happen if superadmin has not entered email
                continue;
            }
            $addresses[] = $row['usersEmail'];
        }
        if (!isset($addresses)) {
            return TRUE;
        }

        return ($this->smtp->sendEmail(array_unique($addresses), $subject, $message));
    }
    /**
     * Notify users of resource additions and edits
     *
     * @param mixed $resourceId
     * @param mixed $newResource
     *
     * @return bool
     */
    public function notify($resourceId, $newResource = FALSE)
    {
        // do nothing if admin does not allow notification or email is not turned on
        if (!WIKINDX_MAIL_USE || !WIKINDX_NOTIFY) {
            return TRUE;
        }
        $this->bibStyle = FACTORY_BIBSTYLE::getInstance();
        $this->bibStyle->output = 'plain';
        $this->user = FACTORY_USER::getInstance();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->res = FACTORY_RESOURCECOMMON::getInstance();
        $subject = "Resource Notification";
        if (!$this->emailImmediate($resourceId, $newResource, $subject)) {
            return FALSE;
        }
        // Now deal with users with a set email threshold
        if (count($this->usersThreshold) > 0) {
            if (!$this->emailThreshold($resourceId, $subject)) {
                return FALSE;
            }
        }

        return TRUE; // success
    }
    /**
     * checkFriendInput
     *
     * @return array
     */
    private function checkFriendInput()
    {
        $address = $subject = $text = FALSE;
        if (array_key_exists('emailFriend_address', $this->vars)) {
            $address = trim($this->vars['emailFriend_address']);
        }
        if (array_key_exists('emailFriend_subject', $this->vars)) {
            $subject = trim($this->vars['emailFriend_subject']);
        }
        if (array_key_exists('emailFriend_text', $this->vars)) {
            $text = trim($this->vars['emailFriend_text']);
        }
        $this->session->setVar("emailFriend_Address", $address);
        $this->session->setVar("emailFriend_Subject", $subject);
        $this->session->setVar("emailFriend_Text", $text);
        if (!$address || !$subject || !$text) {
            $this->badInput->close($this->errors->text('inputError', 'missing'), $this, 'emailFriendDisplay');
        }

        return [$address, $subject, $text];
    }
    /**
     * Email those with a notification threshold set
     *
     * @param mixed $resourceId
     * @param mixed $subject
     *
     * @return bool
     */
    private function emailThreshold($resourceId, $subject)
    {
        $userId = $this->session->getVar("setup_UserId");
        $this->db->formatConditions(['usersId' => $userId]);
        $digestThreshold = $this->db->selectFirstField('users', 'usersNotifyDigestThreshold');
        $this->grabResources($digestThreshold);
        if (empty($this->allAddedIds) && empty($this->allEditedIds)) {
            return TRUE; // nothing to do
        }
        foreach ($this->usersThreshold as $userId => $userArray) 
        {
            if (!$userArray['email']) { // This should only happen if superadmin has not entered email
                continue;
            }
			$message = $this->messages->text("user", "notifyMass4") . "\n\n\n" . LF; // reset each time
			$notifyArray = []; // reset each time
// User wants notification only on new resources
			if (!empty($this->allAddedIds) && ($userArray['notifyAddEdit'] == 'N')) {
				$notifyArray = $this->grabTitlesThreshold($userArray, $this->allAddedIds);
			}
			// User wants notification only on edited resources
			elseif (!empty($this->allEditedIds) && ($userArray['notifyAddEdit'] == 'E')) {
				$notifyArray = $this->grabTitlesThreshold($userArray, $this->allEditedIds);
			}
			if (empty($notifyArray) && $userArray['notify'] == 'A') { // notify on all resources
				// NB, if resource has not been edited, editedTimestamp is same as addedTimestamp
				$notifyArray = $this->grabTitlesThreshold($userArray, $this->allEditedIds);
			}
			// notify on resources in a user's bibliography
			elseif (($userArray['notify'] == 'M')) {
				if (empty($notifyArray) && !empty($this->allEditedIds)) {
					$newArray = $this->allEditedIds;
				} elseif (!empty($notifyArray)) {
					$newArray = $notifyArray;
				} else {
					continue;
				}
				$remainIds = [];
				$this->db->formatConditions(['userbibliographyId' => $userId]);
				$this->db->formatConditions(['userbibliographyresourceResourceId' => $resourceId]);
				$this->db->leftJoin('user_bibliography_resource', 'userbibliographyresourceBibliographyId', 'userbibliographyId');
				$recordset = $this->db->select('user_bibliography', 'userbibliographyId');
				if (!$this->db->numRows($recordset)) { // This resource not in user's bibliography
					continue;
				}
				while ($row = $this->db->fetchRow($recordset)) {
					$bibs = UTF8::mb_explode(',', $row['bibliography']);
					foreach ($newArray as $id => $field) {
						if (array_search($id, $bibs) !== FALSE) {
							$remainIds[$id] = $field;
						}
					}
				}
				if (empty($notifyArray)) { // $field is not a formatted title but is unixTimestamp from $this->allEditedIds
					$notifyArray = $this->grabTitlesThreshold($userArray, $remainIds);
				} else {
					$notifyArray = $remainIds;
				}
			}
			// notify if user is a creator of this resource and resourceId is not FALSE (e.g. notify from a mass bibliography import)
			elseif ($resourceId && ($userArray['notify'] == 'C')) {
				if (empty($notifyArray) && !empty($this->allEditedIds)) {
					$newArray = $this->allEditedIds;
				} elseif (!empty($notifyArray)) {
					$newArray = $notifyArray;
				} else {
					continue;
				}
				$remainIds = [];
				$this->db->formatConditions(['resourcecreatorResourceId' => $resourceId]);
				$this->db->leftJoin(
					'creator',
					$this->db->formatFields('creatorSameAs'),
					$this->db->tidyInput($userArray['creatorId']),
					FALSE
				);
				$recordset = $this->db->select('resource_creator', 'resourcecreatorResourceId', TRUE);
				if (!$this->db->numRows($recordset)) { // This resource not in user's bibliography
					continue;
				}
				while ($row = $this->db->fetchRow($recordset)) {
					foreach ($newArray as $id => $field) {
						if ($id = $row['resourcecreatorResourceId']) {
							$remainIds[$id] = $field;
						}
					}
				}
				if (empty($notifyArray)) { // $field is not a formatted title but is unixTimestamp from $this->allEditedIds
					$notifyArray = $this->grabTitlesThreshold($userArray, $remainIds);
				} else {
					$notifyArray = $remainIds;
				}
			}
			if (empty($notifyArray)) {
				continue;
			}
			// If more than xxx added resources, simply grab the number of added resources
			$size = count($notifyArray);
            if ($size > $digestThreshold) {
                $message = $this->messages->text("user", "notifyMass3", $size);
            } else {
                if (!isset($notifyArray)) {
                    continue;
                }
                $message .= implode("\n" . LF, $notifyArray);
            }
            if (!$this->smtp->sendEmail($userArray['email'], $subject, $message)) {
                return FALSE;
            }
            // set this user's users.notifyTimestamp to current date
            $this->db->formatConditions(['usersId' => $userId]);
            $this->db->updateTimestamp('users', ['usersNotifyTimestamp' => 'CURRENT_TIMESTAMP']);
        }

        return TRUE;
    }
    /**
     * Deal with those requiring immediate notification
     *
     * @param mixed $resourceId
     * @param mixed $newResource
     * @param mixed $subject
     *
     * @return bool
     */
    private function emailImmediate($resourceId, $newResource, $subject)
    {
        $this->earliestUserUnixTimestamp = $this->earliestNotifyUnixTimestamp = $this->greatestThreshold = FALSE;
        $userId = $this->session->getVar("setup_UserId");
        $this->db->formatConditions(['usersId' => $userId]);
        $digestThreshold = $this->db->selectFirstField('users', 'usersNotifyDigestThreshold');
        // Are there any users wanting notification?
        $this->db->formatConditions(['usersId' => $userId], TRUE); // 'TRUE' is NOT EQUAL
        $this->db->formatConditions(['usersNotify' => 'N'], TRUE); // 'TRUE' is NOT EQUAL
        $recordset = $this->db->selectWithExceptions('users', ['usersId', 'usersEmail', 'usersNotify',
            'usersNotifyAddEdit', 'usersNotifyThreshold', 'usersIsCreator',
            ["UNIX_TIMESTAMP(usersNotifyTimestamp)" => 'usersUnixNotifyTimestamp'],
            ["UNIX_TIMESTAMP(usersTimestamp)" => 'usersUnixTimestamp'], ]);
        if (!$this->db->numRows($recordset)) { // nothing to do
            return TRUE;
        }
        while ($row = $this->db->fetchRow($recordset)) {
            if ($row['usersNotifyThreshold'] > 0) {
                // Store greatest user notification threshold
                if (!$this->greatestThreshold || ($row['usersNotifyThreshold'] > $this->greatestThreshold)) {
                    $this->greatestThreshold = $row['usersNotifyThreshold'];
                }
                // Store earliest user notification timestamp
                if (!$this->earliestNotifyUnixTimestamp || ($row['usersUnixNotifyTimestamp']
                    < $this->earliestNotifyUnixTimestamp)) {
                    $this->earliestNotifyUnixTimestamp = $row['usersUnixNotifyTimestamp'];
                }
                // Store earliest user notification timestamp
                if (!$this->earliestUserUnixTimestamp || ($row['usersUnixTimestamp'] < $this->earliestUserUnixTimestamp)) {
                    $this->earliestUserUnixTimestamp = $row['usersUnixTimestamp'];
                }
                $this->usersThreshold[$row['usersId']] = [
                    'email' => $row['usersEmail'],
                    'notify' => $row['usersNotify'],
                    'notifyAddEdit' => $row['usersNotifyAddEdit'],
                    'notifyThreshold' => $row['usersNotifyThreshold'],
                    'unixNotifyTimestamp' => $row['usersUnixNotifyTimestamp'],
                    'unixTimestamp' => $row['usersUnixTimestamp'],
                    'creatorId' => $row['usersIsCreator'],
                ];
            } else {
                $users[$row['usersId']] = [
                    'email' => $row['usersEmail'],
                    'notify' => $row['usersNotify'],
                    'notifyAddEdit' => $row['usersNotifyAddEdit'],
                    'creatorId' => $row['usersIsCreator'],
                ];
            }
        }
        if (!isset($users)) { // nothing to do
            return TRUE;
        }
        // Get this user's name (the user adding/editing a resource)
        $userAddEdit = $this->user->displayUserAddEditPlain($userId);
        // Grab resource details if single
        if ($resourceId) {
            list($title, $notifyMessage) = $this->formatTitle($resourceId, $userAddEdit, $digestThreshold);
            $message = $notifyMessage . "\n\n$title\n" . LF;
        } else { // mass import from bibliography
            $message = $this->messages->text("user", "notifyMass1", $userAddEdit);
            $message .= ' ' . $this->messages->text("user", "notifyMass2");
        }
        $addresses = [];
        foreach ($users as $userId => $user) {
            // User wants notification only on new resources
            if (($user['notifyAddEdit'] == 'N') && !$newResource) {
                continue;
            }
            // User wants notification only on edited resources
            if (($user['notifyAddEdit'] == 'E') && $newResource) {
                continue;
            }
            if (!$user['email']) { // This should only happen if superadmin has not entered email
                continue;
            }
            // notify on resources in a user's bibliography (works for only a single resourceId)
            if ($resourceId && ($user['notify'] == 'M') && !is_array($resourceId)) {
                $this->db->formatConditions(['userbibliographyId' => $userId]);
                $this->db->formatConditions(['userbibliographyresourceResourceId' => $resourceId]);
                $this->db->leftJoin('user_bibliography_resource', 'userbibliographyresourceBibliographyId', 'userbibliographyId');
                $recordset2 = $this->db->select('user_bibliography', 'userbibliographyId');
                if (!$this->db->numRows($recordset2)) { // This resource not in user's bibliography
                    continue;
                }
            }
            // notify if user is a creator of this resource
            elseif ($resourceId && ($user['notify'] == 'C')) {
                $this->db->formatConditions(['resourcecreatorResourceId' => $resourceId]);
                $this->db->leftJoin('creator', $this->db->formatFields('creatorSameAs'), $this->db->tidyInput($user['creatorId']), FALSE);
                $recordset2 = $this->db->select('resource_creator', 'resourcecreatorResourceId', TRUE);
                if (!$this->db->numRows($recordset2)) { // This resource not in user's bibliography
                    continue;
                }
            }
            $addresses[] = $user['email']; // add user to email recipients
        }
        if (empty($addresses)) {
            return TRUE;
        }

        return $this->smtp->sendEmail($addresses, $subject, $message);
    }
    /**
     * grab titles from main list only if user threshold has been passed and resources have been added/edited since the user's last notification
     *
     * @param mixed $userArray
     * @param mixed $idArray
     *
     * @return array
     */
    private function grabTitlesThreshold($userArray, $idArray)
    {
        // no. seconds in 1 day
        $day1secs = 86400;
        $userThreshold = $userArray['notifyThreshold'] * $day1secs;
        $now = time();
        $passed = (($now - $userArray['unixNotifyTimestamp']) > $userThreshold) ? TRUE : FALSE;
        $notifyArray = [];
        foreach ($idArray as $id => $resourceTimestamp) {
            if (time()) {
                if (($resourceTimestamp > $userArray['unixNotifyTimestamp']) && ($passed)) {
                    $notifyArray[$id] = $this->titles[$id];
                }
            }
        }

        return $notifyArray;
    }
    /**
     * Grab resources at greatest threshold following editing or addition.
     * Resources must have NOW() - timestamp  that is less than the greatest notification threshold
     *
     * @param mixed $digestThreshold
     *
     * @return int
     */
    private function grabResources($digestThreshold)
    {
        $size = 0;
        $this->db->formatConditions('UNIX_TIMESTAMP(' . $this->db->formatFields('resourcetimestampTimestampAdd') . ')>' .
            $this->db->tidyInput($this->earliestNotifyUnixTimestamp));
        $this->db->leftJoin('resource_timestamp', "resourcetimestampId", 'resourceId');
        $this->res->withUnixTimestamp = TRUE;
        $this->res->groupByResourceId = FALSE;
        $this->res->limit = $digestThreshold + 1;
        $recordset = $this->res->getResource();
        $this->res->withUnixTimestamp = FALSE;
        $this->res->groupByResourceId = TRUE;
        if (($size = $this->db->numRows($recordset)) > $digestThreshold) {
            return $size;
        }
        while ($row = $this->db->fetchRow($recordset)) {
            if (!array_key_exists($row['resourceId'], $this->titles)) {
                $this->formatTitleThreshold($row);
            }
            $this->allAddedIds[$row['resourceId']] = $row['addUnixTimestamp'];
        }
        // grab greatest possible number of edited resources
        $this->db->formatConditions('UNIX_TIMESTAMP(' . $this->db->formatFields('resourcetimestampTimestamp') . ')>' .
            $this->db->tidyInput($this->earliestNotifyUnixTimestamp));
        $this->db->leftJoin('resource_timestamp', "resourcetimestampId", 'resourceId');
        $this->res->withUnixTimestamp = TRUE;
        $this->res->groupByResourceId = FALSE;
        $recordset = $this->res->getResource();
        $this->res->withUnixTimestamp = FALSE;
        $this->res->groupByResourceId = TRUE;
        if (($size = $this->db->numRows($recordset)) > $digestThreshold) {
            return $size;
        }
        while ($row = $this->db->fetchRow($recordset)) {
            if (!array_key_exists($row['resourceId'], $this->titles)) {
                $this->formatTitleThreshold($row);
            }
            $this->allEditedIds[$row['resourceId']] = $row['editUnixTimestamp'];
        }

        return $size;
    }
    /**
     * Format the resource entry from the SQL row (threshold)
     *
     * @param mixed $row
     */
    private function formatTitleThreshold($row)
    {
        // Data stored in database as UTF-8
        $title = html_entity_decode(\HTML\stripHtml($this->bibStyle->process($row)));
        $this->titles[$row['resourceId']] = $title;
    }
    /**
     * Format the resource entry from the given resource ID.  $resourceIds is either a single ID or an array of IDs for mass imports
     *
     * @param mixed $resourceId
     * @param mixed $userAddEdit
     * @param mixed $digestThreshold
     *
     * @return array
     */
    private function formatTitle($resourceId, $userAddEdit, $digestThreshold)
    {
        $title = '';
        $this->res->limit = $digestThreshold + 1;
        if (is_array($resourceId)) { // always an array coming from emailThreshold()
            // If more than xxx added resources, simply grab the number of added resources
            $size = count($resourceId);
            if ($size > $digestThreshold) {
                $notifyMessage = $this->messages->text("user", "notifyMass1", $userAddEdit);
                $notifyMessage .= ' ' . $this->messages->text("user", "notifyMass2", $size);
                $title = FALSE;
            } else {
                $recordset = $this->res->getResource($resourceId);
                while ($row = $this->db->fetchRow($recordset)) {
                    $this->titles[$row['resourceId']] = html_entity_decode(\HTML\stripHtml($this->bibStyle->process($row)));
                }
                $title = implode("\n" . LF, $this->titles);
                $notifyMessage = $this->messages->text("user", "notifyMass1", $userAddEdit);
            }
        } else {
            $recordset = $this->res->getResource($resourceId);
            $row = $this->db->fetchRow($recordset);
            $title = html_entity_decode(\HTML\stripHtml($this->bibStyle->process($row)));
            $notifyMessage = $this->messages->text("user", "notify", $userAddEdit);
        }

        return [$title, $notifyMessage];
    }
}
