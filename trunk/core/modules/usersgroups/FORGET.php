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
 * FORGET class for forgotten passwords
 */
class FORGET
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
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
        $this->badInput = FACTORY_BADINPUT::getInstance();
    }
    /**
     * Recovery of forgotten password stage1
     *
     * @param false|string $error
     */
    public function forgetInitStage1($error = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "forget"));
        $pString = \FORM\formHeader('usersgroups_FORGET_CORE');
        $pString .= \FORM\hidden('method', 'forgetInitStage2');
        if ($error) {
            $pString .= $error;
        }
        $pString .= \HTML\p($this->messages->text("user", "forget7"));
        $string = \FORM\textInput(
            $this->messages->text("user", "username"),
            "usersUsername",
            FALSE,
            50,
            255
        );
        $string .= BR . \FORM\textInput(
            $this->messages->text("user", "email"),
            "email",
            FALSE,
            50,
            255
        );
        $pString .= \HTML\p($string);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Recovery of forgotten password stage2.
     *
     * Input may be either username or email -- if both, use username
     *
     * @param false|string $error
     */
    public function forgetInitStage2($error = FALSE)
    {
        $this->badInput->closeType = 'closeNoMenu';
        if (array_key_exists('usersUsername', $this->vars) && ($usersUsername = trim($this->vars['usersUsername']))) {
            $this->db->formatConditions(['usersUsername' => $usersUsername]);
        } elseif (array_key_exists('email', $this->vars) && ($email = trim($this->vars['email']))) {
            $this->db->formatConditions(['usersEmail' => $email]);
        } else {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'forgetInitStage1');
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "forget"));
        $pString = \FORM\formHeader('usersgroups_FORGET_CORE');
        $pString .= \FORM\hidden('method', 'forgetProcess');
        if ($error) {
            $pString .= $error;
        }
        $pString .= \HTML\p($this->messages->text("user", "forget8"));
        for ($i = 1; $i < 4; $i++) {
            $userArray[] = "usersPasswordQuestion$i";
            $userArray[] = "usersPasswordAnswer$i";
        }
        $userArray[] = "usersUsername";
        $userArray[] = "usersEmail";
        $recordSet = $this->db->select('users', $userArray);
        if ($this->db->numRows($recordSet) > 1) {
            $this->badInput->close($this->errors->text("warning", "forget2"), $this, 'forgetInitStage1');
        }
        $row = $this->db->fetchRow($recordSet);
        $pString .= \FORM\hidden("usersUsername", $row['usersUsername']);
        $pString .= \FORM\hidden("email", $row['usersEmail']);
        $questionFound = FALSE;
        for ($i = 1; $i < 4; $i++) {
            if (!$row["usersPasswordQuestion$i"]) {
                continue;
            }
            $answer = array_key_exists("answer$i", $this->formData) ? $this->formData["answer$i"] : FALSE;
            $string = $this->messages->text("user", "forget3", "&nbsp;" . $i) .
                ":&nbsp;&nbsp;" . \HTML\nlToHtml($row["usersPasswordQuestion$i"]);
            $string .= BR . \FORM\textInput(FALSE, "answer$i", $answer, 50, 100);
            $pString .= \HTML\p($string);
            $questionFound = TRUE;
        }
        if (!$questionFound) {
            $email = WIKINDX_CONTACT_EMAIL;
            if ($email) {
                $email = \HTML\nlToHtml($email);
                $email = \HTML\a("link", $email, "mailto:$email");
                $contact = "&nbsp;($email).";
            } else {
                $contact = ".";
            }
            $this->badInput->close($this->errors->text("warning", "forget1", $contact));
        }
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Process forgotten password request
     */
    public function forgetProcess()
    {
    	$error = '';
        $this->badInput->closeType = 'closeNoMenu';
        $usersUsername = trim($this->vars['usersUsername']);
        $this->db->formatConditions(['usersUsername' => $usersUsername]);
        for ($i = 1; $i < 4; $i++) {
            $userArray[] = "usersPasswordQuestion$i";
            $userArray[] = "usersPasswordAnswer$i";
        }
        $row = $this->db->selectFirstRow('users', $userArray);
        for ($i = 1; $i < 4; $i++) {
            if (!array_key_exists("answer$i", $this->vars)) {
                continue;
            }
            $this->formData["answer$i"] = trim($this->vars["answer$i"]);
            $answer = sha1(mb_strtolower($this->formData["answer$i"]));
            if ($answer != $row["usersPasswordAnswer$i"]) {
            	$error = $this->errors->text("inputError", "incorrect");
            }
        }
        if ($error) {
            $this->badInput->close($error, $this, 'forgetInitStage2');
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
        $emailClass = new EMAIL();
        $password = time();
        if (!$emailClass->forgetProcess($usersUsername, $password)) {
            $email = WIKINDX_CONTACT_EMAIL;
            if ($email) {
                $email = \HTML\nlToHtml($email);
                $email = \HTML\a("link", $email, "mailto:$email");
                $contact = "&nbsp;($email).";
            } else {
                $contact = ".";
            }
            $this->badInput->close($this->errors->text("warning", "forget3", $contact), $this, 'forgetInitStage1');
        }
        // If we get here, all questions have been correctly answered so write temp pass word to database.
        // NB  This is done after sending out email. If email fails, we don't want to change the user's password.  
        // However, the risk is that a password may be sent and then the update code below will fail. 
        // This is judged to be the lesser of two evils.
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "forget"));
        $cryptPassword = crypt($password, UTF8::mb_strrev(time()));
        $this->db->formatConditions(['usersUsername' => $usersUsername]);
        $this->db->updateSingle('users', $this->db->formatFields('usersPassword') . $this->db->equal . $this->db->tidyInput($cryptPassword));
        header("Location: index.php?action=usersgroups_FORGET_CORE&method=success");
        die;
    }
    /**
     * Successfully written temporary password
     *
     */
    public function success()
    {
        $pString = $this->messages->text("user", "forget10");
        $link = "index.php?action=initLogon";
        $pString .= \HTML\p(\HTML\a("link", $this->messages->text("user", "forget11"), $link), FALSE, "right");
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
}
