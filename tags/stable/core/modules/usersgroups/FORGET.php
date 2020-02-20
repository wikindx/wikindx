<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
     * @param string|FALSE $error
     */
    public function forgetInitStage1($error = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "forget"));
        $pString = \FORM\formHeader('usersgroups_FORGET_CORE');
        $pString .= \FORM\hidden('method', 'forgetInitStage2');
        if ($error)
        {
            $pString .= $error;
        }
        $pString .= \HTML\p($this->messages->text("user", "forget7"));
        $string = \FORM\textInput(
            $this->messages->text("user", "username"),
            "username",
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
     * @param string|FALSE $error
     */
    public function forgetInitStage2($error = FALSE)
    {
        $this->badInput->closeType = 'closeNoMenu';
        if (array_key_exists('username', $this->vars) && ($username = trim($this->vars['username'])))
        {
            $this->db->formatConditions(['usersUsername' => $username]);
        }
        elseif (array_key_exists('email', $this->vars) && ($email = trim($this->vars['email'])))
        {
            $this->db->formatConditions(['usersEmail' => $email]);
        }
        else
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'forgetInitStage1');
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "forget"));
        $pString = \FORM\formHeader('usersgroups_FORGET_CORE');
        $pString .= \FORM\hidden('method', 'forgetProcess');
        if ($error)
        {
            $pString .= $error;
        }
        $pString .= \HTML\p($this->messages->text("user", "forget8"));
        for ($i = 1; $i < 4; $i++)
        {
            $userArray[] = "usersPasswordQuestion$i";
            $userArray[] = "usersPasswordAnswer$i";
        }
        $userArray[] = "usersUsername";
        $userArray[] = "usersEmail";
        $recordSet = $this->db->select('users', $userArray);
        if ($this->db->numRows($recordSet) > 1)
        {
            $this->badInput->close($this->errors->text("warning", "forget2"), $this, 'forgetInitStage1');
        }
        $row = $this->db->fetchRow($recordSet);
        $pString .= \FORM\hidden("username", $row['usersUsername']);
        $pString .= \FORM\hidden("email", $row['usersEmail']);
        $questionFound = FALSE;
        for ($i = 1; $i < 4; $i++)
        {
            if (!$row["usersPasswordQuestion$i"])
            {
                continue;
            }
            $string = $this->messages->text("user", "forget3", "&nbsp;" . $i) .
                ":&nbsp;&nbsp;" . \HTML\dbToHtmlTidy($row["usersPasswordQuestion$i"]);
            $string .= BR . \FORM\textInput(FALSE, "answer$i", FALSE, 50, 100);
            $pString .= \HTML\p($string);
            $questionFound = TRUE;
        }
        if (!$questionFound)
        {
            $co = FACTORY_CONFIGDBSTRUCTURE::getInstance();
            $email = $co->getOne('configContactEmail');
            if ($email)
            {
                $email = \HTML\dbToHtmlTidy($email);
                $email = \HTML\a("link", $email, "mailto:$email");
                $contact = "&nbsp;($email).";
            }
            else
            {
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
        $this->badInput->closeType = 'closeNoMenu';
        $username = trim($this->vars['username']);
        $this->db->formatConditions(['usersUsername' => $username]);
        for ($i = 1; $i < 4; $i++)
        {
            $userArray[] = "usersPasswordQuestion$i";
            $userArray[] = "usersPasswordAnswer$i";
        }
        $row = $this->db->selectFirstRow('users', $userArray);
        for ($i = 1; $i < 4; $i++)
        {
            if (!array_key_exists("answer$i", $this->vars))
            {
                continue;
            }
            $answer = sha1(mb_strtolower(trim($this->vars["answer$i"])));
            if ($answer != $row["usersPasswordAnswer$i"])
            {
                $this->badInput->close($this->errors->text("inputError", "incorrect"), $this, 'forgetInitStage2');
            }
        }
        include_once("core/modules/email/EMAIL.php");
        $emailClass = new EMAIL();
        $password = time();
        if (!$emailClass->forgetProcess($username, $password))
        {
            $co = FACTORY_CONFIGDBSTRUCTURE::getInstance();
            $email = $co->getOne('configContactEmail');
            if ($email)
            {
                $email = \HTML\dbToHtmlTidy($email);
                $email = \HTML\a("link", $email, "mailto:$email");
                $contact = "&nbsp;($email).";
            }
            else
            {
                $contact = ".";
            }
            $this->badInput->close($this->errors->text("warning", "forget3", $contact), $this, 'forgetInitStage1');
        }
        // If we get here, all questions have been correctly answered so write temp pass word to database and send out email.
        // NB  This is done after sending out email.  If email fails, we don't want to change the user's password.  However, the risk is that a password may be sent
        // and then the update code below will fail.  This is judged to be the lesser of two evils.
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "forget"));
        $cryptPassword = crypt($password, UTF8::mb_strrev(time()));
        $this->db->formatConditions(['usersUsername' => $username]);
        $this->db->updateSingle('users', $this->db->formatFields('usersPassword') . $this->db->equal .
            $this->db->tidyInput($cryptPassword));
        $pString = $this->messages->text("user", "forget10");
        $link = "index.php?action=initLogon";
        $pString .= \HTML\p(\HTML\a("link", $this->messages->text("user", "forget11"), $link), FALSE, "right");
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * display form for entering questions and answers for a forgotten password scenario
     *
     * @return string
     */
    public function forgetSet()
    {
        $pString = \HTML\p($this->messages->text("user", "forget1"));
        $pString .= \HTML\p($this->messages->text("user", "forget5"));
        $pString .= \HTML\p($this->messages->text("user", "forget2"));
        //		print_r($this->session->getArray('mywikindx'));
        for ($i = 1; $i < 4; $i++)
        {
            $question = $this->session->issetVar("mywikindx_usersPasswordQuestion$i") ?
                \HTML\dbToFormTidy($this->session->getVar("mywikindx_usersPasswordQuestion$i")) : FALSE;
            $answer = $this->session->issetVar("mywikindx_usersAnswer$i") ?
                \HTML\dbToFormTidy($this->session->getVar("mywikindx_usersAnswer$i")) : FALSE;
            $string = \FORM\textInput(
                $this->messages->text("user", "forget3", "&nbsp;" . $i),
                "usersPasswordQuestion$i",
                $question,
                100,
                255
            );
            $string .= BR . \FORM\textInput(
                $this->messages->text("user", "forget4", "&nbsp;" . $i),
                "usersAnswer$i",
                $answer,
                50,
                100
            );
            $pString .= \HTML\p($string);
        }

        return $pString;
    }
    /**
     * Set the forget password system's questions and answers
     *
     * @param array Array of two values
     */
    public function forgetWrite()
    {
        include_once('core/modules/usersgroups/MYWIKINDX.php');
        $mywikindx = new MYWIKINDX();
        $array = ["usersPasswordQuestion1", "usersAnswer1", "usersPasswordQuestion2", "usersAnswer2", "usersPasswordQuestion3", "usersAnswer3"];
        foreach ($array as $key)
        {
            if (array_key_exists($key, $this->vars) && $this->vars[$key])
            {
                if (($key == "usersPasswordQuestion1") || ($key == "usersPasswordQuestion2") || ($key == "usersPasswordQuestion3"))
                {
                    $this->session->setVar("mywikindx_" . $key, $this->vars[$key]);
                }
            }
        }
        $inputArray = [];
        for ($i = 1; $i < 4; $i++)
        {
            $question = trim($this->vars["usersPasswordQuestion$i"]);
            if (!$question)
            {
                $this->session->delVar("mywikindx_usersPasswordQuestion$i");
                $this->session->delVar("mywikindx_usersAnswer$i");
            }
            $answer = trim($this->vars["usersAnswer$i"]);
            if ($question && !$answer)
            {
                return [FALSE, $this->errors->text("inputError", "missing")];
            }
            elseif ($question && $answer)
            {
                $inputArray[$question] = sha1(mb_strtolower($answer));
            }
        }
        $index = 1;
        foreach ($inputArray as $q => $a)
        {
            $update["usersPasswordQuestion$index"] = $q;
            $update["usersPasswordAnswer$index"] = $a;
            $index++;
        }
        if (isset($update))
        { // values to update
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $this->db->update('users', $update);
        }
        // Set remaining fields to NULL
        while ($index < 4)
        {
            $nulls[] = "usersPasswordQuestion$index";
            $nulls[] = "usersPasswordAnswer$index";
            $index++;
        }
        if (isset($nulls))
        {
            $this->db->formatConditions(['usersId' => $this->session->getVar("setup_UserId")]);
            $this->db->updateNull('users', $nulls);
        }

        return [TRUE, FALSE];
    }
}
