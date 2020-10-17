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
 *	REGISTER class.
 *
 *	Registration of users
 */
class REGISTER
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $user;
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
    }
    /**
     * display options for user registration
     *
     * @param false|string $error
     */
    public function initRegister($error = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        $pString = $error;
        $registrationModerate = WIKINDX_USER_REGISTRATION_MODERATE;
        if ($registrationModerate) {
            $pString .= \HTML\p($this->messages->text('config', 'registrationRequest1'));
            $pString .= \FORM\formHeader('usersgroups_REGISTER_CORE');
            $pString .= \FORM\hidden('method', 'registerRequest');
        } else {
            $pString .= \FORM\formHeader('usersgroups_REGISTER_CORE');
            $pString .= \FORM\hidden('method', 'registerUser');
        }
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $field = array_key_exists('email', $this->formData) ? $this->formData['email'] : FALSE;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput($this->messages->text("user", "email"), "email", $field, 30, 255)
              . (!$registrationModerate ? BR . $this->messages->text("hint", "registerEmail") . \HTML\p('&nbsp;') : FALSE));
        if ($registrationModerate) {
            $pString .= \HTML\td('&nbsp;');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        if ($registrationModerate) {
        	$field = array_key_exists('registerRequest', $this->formData) ? $this->formData['registerRequest'] : FALSE;
            $pString .= \HTML\td(BR . \HTML\span('*', 'required') . 
            \FORM\textareaInput($this->messages->text("config", "registrationRequest2"), "registerRequest", $field, 75, 15));
        }
        if (!$registrationModerate) {
            $pString .= \HTML\p('&nbsp;');
        }
        $pString .= $this->createCaptcha();
        if ($registrationModerate) {
            $pString .= \HTML\td('&nbsp;');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Write registration details and send confirmation email
     */
    public function registerUser()
    {
    	$this->badInput->closeType = 'closenomenu';
    	$error = '';
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        if (!$email = \UTF8\mb_trim($this->vars['email'])) {
        	$error = $this->errors->text('inputError', 'missing');
        }
        if (!$this->captchaCheck()) {
        	$error = $this->errors->text('inputError', 'captcha');
        }
        $this->session->delVar("captcha");
        $this->formData['email'] = \UTF8\mb_trim($this->vars['email']);
        if ($error) {
            $this->badInput->close($error, $this, 'initRegister');
        }
        // time() should be unique enough
        $hashKey = md5(time());
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
        $emailClass = new EMAIL();
        if (!$emailClass->register($hashKey, $email)) {
            $error = $this->errors->text("inputError", "mail", GLOBALS::getError());
        }
        if ($error) {
            $this->badInput->close($error, $this, 'initRegister');
        }
        // END email
        // Now write details to WKX_user_register database
        $fields[] = 'userregisterHashKey';
        $values[] = $hashKey;
        $fields[] = 'userregisterEmail';
        $values[] = $email;
        $fields[] = 'userregisterTimestamp';
        $values[] = $this->db->formatTimestamp();
        $this->db->insert("user_register", $fields, $values);
        // do some housekeeping by removing dead registrations older than 20 days
        $this->db->formatConditions($this->db->dateIntervalCondition(20) . $this->db->greater .
            $this->db->formatFields('userregisterTimestamp'));
        $this->db->delete('user_register');
		$message = rawurlencode($this->success->text("registerEmail"));
		header("location: index.php?message=$message");
		die;
    }
    /**
     * user has entered hashkey for confirmation
     *
     * @param false|string $error
     */
    public function registerConfirm($error = FALSE)
    {
        $pString = $error;
    	$this->badInput->closeType = 'closenomenu';
    	$error = '';
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        if (!array_key_exists('hashKey', $this->vars)) {
        	$error = $this->errors->text('inputError', 'missing');
			$this->badInput->close($error, $this, 'initRegister');
        }
        // $this->vars['hashKey'] -> check it exists in user_register
        $hashKey = array_key_exists('hashKey', $this->formData) ? $this->formData['hashKey'] : $this->vars['hashKey'];
        $this->db->formatConditions(['userregisterHashkey' => $hashKey]);
        $recordset = $this->db->select('user_register', ['userregisterId', 'userregisterEmail']);
        if (!$this->db->numRows($recordset)) {
        	$error = $this->errors->text('inputError', 'noHashKey');
			$this->badInput->close($error, $this, 'initRegister');
        }
        $row = $this->db->fetchRow($recordset);
        $id = $row['userregisterId'];
        $email = $row['userregisterEmail'];
        $password = FACTORY_PASSWORD::getInstance();
        list($formText, $jsString) = $password->createElements(TRUE, FALSE, $this->formData);
        $pString .= \FORM\formHeader('usersgroups_REGISTER_CORE', 'onsubmit="return checkForm(' . $jsString . ');"');
        $pString .= \FORM\hidden('method', 'registerUserAdd');
        $pString .= \FORM\hidden('id', $id);
        $pString .= \FORM\hidden('hashKey', $hashKey);
        $pString .= \FORM\hidden('email', $email);
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= $formText;
        $fullname = array_key_exists('fullname', $this->formData) ? $this->formData['fullname'] : FALSe;
        $pString .= \HTML\td(\FORM\textInput($this->messages->text("user", "fullname"), "fullname", $fullname, 30, 255));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p($this->createCaptcha());
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Add a user (self registration)
     */
    public function registerUserAdd()
    {
    	$this->badInput->closeType = 'closenomenu';
    	$error = '';
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        if (!array_key_exists('usersUsername', $this->vars) || !array_key_exists('password', $this->vars) ||
            !array_key_exists('passwordConfirm', $this->vars) || !array_key_exists('email', $this->vars) || 
            !array_key_exists('hashKey', $this->vars)) {print_r($this->vars);
        	$error = $this->errors->text('inputError', 'missing');
        }
        if (!\UTF8\mb_trim($this->vars['usersUsername']) || !\UTF8\mb_trim($this->vars['password']) ||
            !\UTF8\mb_trim($this->vars['passwordConfirm']) || !$this->vars['email'] || !$this->vars['hashKey']) {
        	$error = $this->errors->text('inputError', 'missing');
        }
        if (\UTF8\mb_trim($this->vars['password']) != \UTF8\mb_trim($this->vars['passwordConfirm'])) {
        	$error = $this->errors->text('inputError', 'missing');
        }
        $this->formData['usersUsername'] = \UTF8\mb_trim($this->vars['usersUsername']);
        $this->formData['fullname'] = \UTF8\mb_trim($this->vars['fullname']);
        $this->formData['id'] = $this->vars['id'];
        $this->formData['hashKey'] = \UTF8\mb_trim($this->vars['hashKey']);
        $this->formData['email'] = $this->vars['email'];
        if ($error) {
            $this->badInput->close($error, $this, 'registerConfirm');
        }
        if (!$this->captchaCheck()) {
        	$error = $this->errors->text('inputError', 'captcha');
        }
        $this->session->delVar("captcha");
        if ($error) {
            $this->badInput->close($error, $this, 'registerConfirm');
        }
        // NB - writeUser returns FALSE on success!
        if ($this->user->writeUser(TRUE, 0)) {
        	$error = $this->errors->text('inputError', 'userExists');
        }
        if ($error) {
            $this->badInput->close($error, $this, 'registerConfirm');
        }
        // remove id from user_register
        $this->db->formatConditions(['userregisterId' => $this->vars['id']]);
        $this->db->delete('user_register');
        // grab id and write initial session details to grant write access
        $this->db->formatConditions(['usersUsername' => $this->vars['usersUsername']]);
        $userId = $this->db->selectFirstField('users', 'usersId');
        $this->session->setVar("setup_UserId", $userId);
        $this->session->setVar("setup_Write", TRUE);
        // Write default preferences (TRUE == insert)
        $this->user->writePreferences($userId);
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
        $emailClass = new EMAIL();
        if (!$emailClass->registerUserAdd()) {
            $error = $this->errors->text("inputError", "mail", GLOBALS::getError());
        }
        if ($error) {
            $this->badInput->close($error, $this, 'registerConfirm');
        }
		$message = rawurlencode($this->success->text("userAdd"));
		header("location: index.php?message=$message");
		die;
    }
    /**
     * Store request for registration
     */
    public function registerRequest()
    {
    	$this->badInput->closeType = 'closenomenu';
    	$error = '';
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        if ((!$email = \UTF8\mb_trim($this->vars['email'])) || !$request = \UTF8\mb_trim($this->vars['registerRequest'])) {
        	$error = $this->errors->text('inputError', 'missing');
        }
        elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
        	$error = $this->errors->text('inputError', 'invalidMail');
        }
        elseif (!$this->captchaCheck()) {
        	$error = $this->errors->text('inputError', 'captcha');
        }
        $this->session->delVar("captcha");
        $this->formData['registerRequest'] = \UTF8\mb_trim($this->vars['registerRequest']);
        $this->formData['email'] = \UTF8\mb_trim($this->vars['email']);
        if ($error) {
            $this->badInput->close($error, $this, 'initRegister');
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
        $emailClass = new EMAIL();
        if (!$emailClass->registerRequest($email)) {
        	$error = $this->errors->text("inputError", "mail", GLOBALS::getError());
        }
        if ($error) {
            $this->badInput->close($error, $this, 'initRegister');
        }
        // Now write details to WKX_user_register database
        $fields[] = 'userregisterHashKey';
        $values[] = md5(time());
        $fields[] = 'userregisterEmail';
        $values[] = trim(\HTML\removeNl($email));
        $fields[] = 'userregisterRequest';
        $values[] = trim(\HTML\removeNl($request));
        $fields[] = 'userregisterTimestamp';
        $values[] = $this->db->formatTimestamp();
        $this->db->insert('user_register', $fields, $values);
		$message = rawurlencode($this->success->text("registerRequest"));
		header("location: index.php?message=$message");
		die;
    }
    /**
     * Create PNG of a simple maths question as anti-bot device
     *
     * @return string
     */
    private function createCaptcha()
    {
        // compute a math challenge
        $left = [0, 1, 2, 3, 4, 5, 6, 7];
        $right = [0, 1, 2, 3, 4, 5, 6, 7];
        $lterm = $left[rand(0, count($left) - 1)];
        $rterm = $right[rand(0, count($right) - 1)];
        $opArray = ['+', '-', 'x'];
        $opIndex = rand(0, count($opArray) - 1);
        $operator = $opArray[$opIndex];
        if ($opIndex == 0) {
            $answer = $lterm + $rterm;
        } elseif ($opIndex == 1) {
            $answer = $lterm - $rterm;
        } elseif ($opIndex == 2) {
            $answer = $lterm * $rterm;
        } else {
            die("Cannot Create image");
        }
        $challengeText = $lterm . ' ' . $operator . ' ' . $rterm . ' =';
        // prepare an image for the challenge
        $CaptchaWidth = 80;
        $CaptchaHeight = 25;
        $handle = imagecreate($CaptchaWidth, 25) or die("Cannot Create image");
        $bg_color = imagecolorallocatealpha($handle, 255, 255, 255, 127); // Transparent background
        $txt_color = imagecolorallocate($handle, 0, 0, 0); // Black text
        imagestring($handle, 5, 5, 5, $challengeText, $txt_color);
        ob_start();
        imagepng($handle);
        $data = ob_get_clean();
        imagedestroy($handle);
        // write answer to session
        $this->session->setVar("captcha", $answer);
        // display the captcha
        $pString = \HTML\td(\HTML\span('*', 'required') . 
            $this->messages->text('config', 'captcha1') . BR .
            '<img src="data:image/png;base64,' . base64_encode($data) . '" title="captcha" alt="captcha"' .
            ' width="' . $CaptchaWidth . '" height="' . $CaptchaHeight . '" style="vertical-align:middle;">' .
            \FORM\textInput(FALSE, "answer", FALSE, 30, 30)
        );

        return $pString;
    }
    /**
     * Check for CAPTCHA input
     *
     * @return bool
     */
    private function captchaCheck()
    {
        $user_answer = $this->vars['answer'];
        $correct_answer = $this->session->getVar("captcha");

        return ($user_answer == $correct_answer);
    }
}
