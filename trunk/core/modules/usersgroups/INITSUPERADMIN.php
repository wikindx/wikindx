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
 *	INITSUPERADMIN WIKINDX class
 *
 * Initial superadmin configuration when installing a new WIKINDX database
 */
class INITSUPERADMIN
{
    private $errors;
    private $messages;
    private $success;
    private $db;
    private $vars;
    private $badInput;
    private $messageString = FALSE;
    private $error = FALSE;
    private $formData = [];

    public function __construct()
    {
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->badInput = FACTORY_BADINPUT::getInstance();
    }
    /**
     * init
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        $this->messageString = $message;
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "configure"));
        $pString = \HTML\tableStart('');
        $pString .= \HTML\trStart();
        $pString .= $this->superConfigDisplay();
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        if ($this->error)
        {
            GLOBALS::addTplVar('content', $pString);
            FACTORY_CLOSENOMENU::getInstance();
        }

        return $pString;
    }
    /**
     * write values to database table
     */
    public function writeDb()
    {
        $this->checkInput();
        // If we get here, we're cleared to write to the database.
        $usersFieldArray = $usersValueArray = [];
        // if inserting after initial install, write superadmin's preferences to users table and create user session
        $usersFieldArray[] = 'usersUsername';
        $usersValueArray[] = $this->vars['usersUsername'];
        $usersFieldArray[] = 'usersPassword';
        $usersValueArray[] = \UTILS\password_hash($this->vars['password']);
        $usersFieldArray[] = 'usersEmail';
        $usersValueArray[] = $this->vars['usersEmail'];
        $usersFieldArray[] = 'usersAdmin';
        $usersValueArray[] = TRUE;
        if (array_key_exists('usersFullname', $this->vars))
        {
            $usersFieldArray[] = 'usersFullname';
            $usersValueArray[] = $this->vars['usersFullname'];
        }
        if (array_key_exists('usersIsCreator', $this->vars) && $this->vars['usersIsCreator'])
        {
            $usersValueArray[] = $this->vars['usersIsCreator'];
            $usersFieldArray[] = 'usersIsCreator';
        }
        if (array_key_exists('usersCookie', $this->vars) && $this->vars['usersCookie'])
        {
            $cookie = FACTORY_COOKIE::getInstance();
            $cookie->storeCookie($this->vars['usersUsername']);
            $usersFieldArray[] = 'usersCookie';
            $usersValueArray[] = 'Y';
        }
        $this->db->insert('users', $usersFieldArray, $usersValueArray);
        $message = rawurlencode($this->success->text("config"));
        header("Location: index.php?action=admin_CONFIGURE_CORE&method=init&message=$message&selectItem=front");
    }
    /**
     * Display super config options
     *
     * @return string
     */
    private function superConfigDisplay()
    {
        $password = FACTORY_PASSWORD::getInstance();
        $pString = $this->messageString;
        list($formText, $jsString) = $password->createElements(FALSE, TRUE, $this->formData);
        $pString .= '<script src="' . WIKINDX_URL_BASE . '/core/modules/admin/configure.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        $pString .= \FORM\formHeader("usersgroups_INITSUPERADMIN_CORE", 'onsubmit="return checkForm(' . $jsString . ');"');
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        $pString .= $formText;
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $field = array_key_exists("usersEmail", $this->formData) ? $this->formData["usersEmail"] : FALSE;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("user", "email"),
            "usersEmail",
            $field,
            30
        ));
        $field = array_key_exists("usersFullname", $this->formData) ? $this->formData["usersFullname"] : FALSE;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("user", "fullname"),
            "usersFullname",
            $field,
            30
        ));
        $field = FALSE;
        if (!empty($this->formData))
        {
            if (array_key_exists('usersCookie', $this->formData))
            {
                $field = TRUE;
            }
            else
            {
                $field = FALSE;
            }
        }
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("user", "cookie"), "usersCookie", $field));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * Check input
     */
    private function checkInput()
    {
        $error = '';
        if (array_key_exists('usersUsername', $this->vars))
        {
            $this->formData['usersUsername'] = \UTF8\mb_trim($this->vars['usersUsername']);
        }
        if (array_key_exists('usersFullname', $this->vars))
        {
            $this->formData['usersFullname'] = \UTF8\mb_trim($this->vars['usersFullname']);
        }
        if (array_key_exists('usersEmail', $this->vars))
        {
            $this->formData['usersEmail'] = \UTF8\mb_trim($this->vars['usersEmail']);
            if (filter_var($this->formData['usersEmail'], FILTER_VALIDATE_EMAIL) === FALSE)
            {
                $error = $this->errors->text('inputError', 'invalidMail');
            }
        }
        if (array_key_exists('usersCookie', $this->vars))
        {
            $this->formData['usersCookie'] = TRUE;
        }
        // strings that are required
        $required = [
            "password",
            "passwordConfirm",
            "usersEmail",
            "usersUsername",
        ];
        foreach ($required as $value)
        {
            if (array_key_exists($value, $this->vars))
            {
                $input = \UTF8\mb_trim($this->vars[$value]);
                if ($value == 'usersUsername')
                {
                    if (array_search(mb_strtolower($input), ['sa', 'admin', 'super', 'superadmin']))
                    {
                        $error = $this->errors->text("inputError", 'badUsername', " ($value) ");
                    }
                }
            }
            else
            {
                continue;
            }
            if (!$input)
            {
                $error = $this->errors->text("inputError", 'missing', " ($value) ");
            }
        }
        if (array_key_exists('password', $this->vars) && ($this->vars['password'] != $this->vars['passwordConfirm']))
        {
            $error = $this->errors->text("inputError", 'passwordMismatch');
        }
        if ($error)
        {
            $this->badInputLoad($error);
        }
        // Check size of password is no less than N chars
        if (array_key_exists('configPasswordSize', $this->vars) && ($this->vars['configPasswordSize'] < WIKINDX_PASSWORD_SIZE_DEFAULT))
        {
            $this->vars['configPasswordSize'] = WIKINDX_PASSWORD_SIZE_DEFAULT;
        }
    }
    /**
     * Error handling
     *
     * @param mixed $error
     */
    private function badInputLoad($error)
    {
        $this->badInput->closeType = 'closeNoMenu';
        $this->error = TRUE;
        $this->badInput->close($error, $this, 'init');
    }
}
