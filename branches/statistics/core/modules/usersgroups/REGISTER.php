<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
     * @param string|FALSE $error
     */
    public function initRegister($error = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        $pString = $error ? \HTML\p($error, "error", "center") : FALSE;
        $registrationModerate = WIKINDX_USER_REGISTRATION_MODERATE;
        if ($registrationModerate)
        {
            $pString .= \HTML\p($this->messages->text('config', 'registrationRequest1'));
            $pString .= \FORM\formHeader('usersgroups_REGISTER_CORE');
            $pString .= \FORM\hidden('method', 'registerRequest');
        }
        else
        {
            $pString .= \FORM\formHeader('usersgroups_REGISTER_CORE');
            $pString .= \FORM\hidden('method', 'registerUser');
        }
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\textInput($this->messages->text("user", "email"), "email", FALSE, 30, 255)
             . " " . \HTML\span('*', 'required') .
             (!$registrationModerate ? BR . $this->messages->text("hint", "registerEmail") . \HTML\p('&nbsp;') : FALSE));
        if ($registrationModerate)
        {
            $pString .= \HTML\td('&nbsp;');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        if ($registrationModerate)
        {
            $pString .= \HTML\td(BR .
            \FORM\textareaInput($this->messages->text("config", "registrationRequest2"), "registerRequest", FALSE, 75, 15));
        }
        if (!$registrationModerate)
        {
            $pString .= \HTML\p('&nbsp;');
        }
        $pString .= $this->createCaptcha();

        if ($registrationModerate)
        {
            $pString .= \HTML\td('&nbsp;');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")), FALSE, "right");
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Write registration details and send confirmation email
     */
    public function registerUser()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        // check we're not reloading
        if ($this->session->getVar("register_lock"))
        {
            $this->badInput->close($this->errors->text("done", "register"), $this, 'initRegister');
        }
        if (!$email = trim($this->vars['email']))
        {
            $this->badInput->close($this->errors->text('inputError', 'missing'), $this, 'initRegister');
        }
        if (!$this->captchaCheck())
        {
            $this->badInput->close($this->errors->text('inputError', 'captcha'), $this, "initRegister");
        }
        // time() should be unique enough
        $hashKey = md5(time());
        include_once("core/modules/email/EMAIL.php");
        $emailClass = new EMAIL();
        if (!$emailClass->register($hashKey, $email))
        {
            $this->badInput->close($this->errors->text("inputError", "mail", GLOBALS::getError()), $this, 'initRegister');
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
        // Lock to prevent re-registration
        $this->session->setVar("register_lock", TRUE);
        GLOBALS::addTplVar('content', $this->success->text("registerEmail"));
    }
    /**
     * user has entered hashkey for confirmation
     *
     * @param string|FALSE $error
     */
    public function registerConfirm($error = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        if (!array_key_exists('hashKey', $this->vars))
        {
            $this->badInput->close($this->errors->text('inputError', 'missing'));
        }
        // $this->vars['hashKey'] -> check it exists in user_register
        $this->db->formatConditions(['userregisterHashkey' => $this->vars['hashKey']]);
        $recordset = $this->db->select('user_register', ['userregisterId', 'userregisterEmail']);
        if (!$this->db->numRows($recordset))
        {
            $this->badInput->close($this->errors->text('inputError', 'noHashKey'));
        }
        $row = $this->db->fetchRow($recordset);
        $id = $row['userregisterId'];
        $email = $row['userregisterEmail'];
        $pString = $error ? \HTML\p($error, "error", "center") : FALSE;
        $password = FACTORY_PASSWORD::getInstance();
        list($formText, $jsString) = $password->createElements();
        $pString .= \FORM\formHeader('usersgroups_REGISTER_CORE', 'onsubmit="return checkForm(' . $jsString . ');"');
        $pString .= \FORM\hidden('method', 'registerUserAdd');
        $pString .= \FORM\hidden('id', $id);
        $pString .= \FORM\hidden('hashKey', $this->vars['hashKey']);
        $pString .= \FORM\hidden('email', $email);
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= $formText;
        $pString .= \HTML\td(\FORM\textInput($this->messages->text("user", "fullname"), "fullname", FALSE, 30, 255));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        $pString .= \HTML\p($this->createCaptcha());
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")), FALSE, "right");
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Add a user (self registration)
     */
    public function registerUserAdd()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        if (!trim($this->vars['username']) || !trim($this->vars['password']) ||
            !trim($this->vars['passwordConfirm']) || !$this->vars['email'] || !$this->vars['hashKey'])
        {
            $this->badInput->close($this->errors->text('inputError', 'missing'), $this, 'registerConfirm');
        }
        if (trim($this->vars['password']) != trim($this->vars['passwordConfirm']))
        {
            $this->badInput->close($this->errors->text('inputError', 'missing'), $this, 'registerConfirm');
        }
        if (!$this->captchaCheck())
        {
            $this->badInput->close($this->errors->text('inputError', 'captcha'), $this, "registerConfirm");
        }
        // NB - writeUser returns FALSE on success!
        if ($this->user->writeUser(TRUE, 0))
        {
            $this->badInput->close($this->errors->text('inputError', 'userExists'), $this, 'registerConfirm');
        }
        // remove id from user_register
        $this->db->formatConditions(['userregisterId' => $this->vars['id']]);
        $this->db->delete('user_register');
        // grab id and write initial session details to grant write access
        $this->db->formatConditions(['usersUsername' => $this->vars['username']]);
        $userId = $this->db->selectFirstField('users', 'usersId');
        $this->session->setVar("setup_UserId", $userId);
        $this->session->setVar("setup_Write", TRUE);
        // Write default preferences (TRUE == insert)
        $this->user->writePreferences($userId);
        include_once("core/modules/email/EMAIL.php");
        $emailClass = new EMAIL();
        if (!$emailClass->registerUserAdd())
        {
            $this->badInput->close($this->errors->text("inputError", "mail", GLOBALS::getError()), $this, 'registerConfirm');
        }
        GLOBALS::addTplVar('content', $this->success->text("userAdd"));
    }
    /**
     * Store request for registration
     */
    public function registerRequest()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "register"));
        // check we're not reloading
        if ($this->session->getVar("register_lock"))
        {
            $this->badInput->close($this->errors->text("done", "register"), $this, 'initRegister');
        }
        if ((!$email = trim($this->vars['email'])) || !$request = trim($this->vars['registerRequest']))
        {
            $this->badInput->close($this->errors->text('inputError', 'missing'), $this, 'initRegister');
        }
        if (!$this->captchaCheck())
        {
            $this->badInput->close($this->errors->text('inputError', 'captcha'), $this, "initRegister");
        }
        include_once("core/modules/email/EMAIL.php");
        $emailClass = new EMAIL();
        if (!$emailClass->registerRequest($email))
        {
            $this->badInput->close($this->errors->text("inputError", "mail", GLOBALS::getError()), $this, 'initRegister');
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
        // Lock to prevent re-registration
        $this->session->setVar("register_lock", TRUE);
        GLOBALS::addTplVar('content', $this->success->text("registerRequest"));
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
        if ($opIndex == 0)
        {
            $answer = $lterm + $rterm;
        }
        elseif ($opIndex == 1)
        {
            $answer = $lterm - $rterm;
        }
        elseif ($opIndex == 2)
        {
            $answer = $lterm * $rterm;
        }
        else
        {
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
        $session = FACTORY_SESSION::getInstance();
        $session->setVar('captcha', $answer);
        // display the captcha
        $pString = \HTML\td(
            $this->messages->text('config', 'captcha1') . BR .
            '<img src="data:image/png;base64,' . base64_encode($data) . '" title="captcha" alt="captcha"' .
            ' width="' . $CaptchaWidth . '" height="' . $CaptchaHeight . '" style="vertical-align:middle;">' .
            \FORM\textInput(FALSE, "answer", FALSE, 30, 30) . " " . \HTML\span('*', 'required')
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
        $session = FACTORY_SESSION::getInstance();
        $user_answer = $this->vars['answer'];
        $correct_answer = $session->getVar('captcha');
        if ($user_answer == $correct_answer)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
}
