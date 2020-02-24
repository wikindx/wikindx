<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 *	PASSWORD class.
 *
 *	Functions for passwords
 */
class PASSWORD
{
    /** object */
    private $db;
    /** object */
    private $errors;
    /** object */
    private $messages;
    /** int */
    private $noChars;
    /** string */
    private $regexp;
    /** string */
    private $invalidPassword;


    /**
     * PASSWORD
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->init();
    }
    /**
     * Create and return an array of two strings for the username/password form
     *
     * @param string|bool $username TRUE (default) if username should be part of the form
     * @param string|bool $super FALSE (default) if this is for superadmin in the configure form
     *
     * @return array Array of 2 strings: 1. form element string; 2. javascript for form header
     */
    public function createElements($username = TRUE, $super = FALSE)
    {
        $missing = $this->errors->text('inputError', 'missing', FALSE, FALSE);
        $misMatch = $this->errors->text('inputError', 'passwordMismatch', FALSE, FALSE);
        $jsString = "this, " . $this->regexp . ", " . $this->noChars . ", '" . $missing . "', '" . $misMatch . "', '" . $this->invalidPassword . "'";
        $hintArray = [
            "weak" => "password1",
            "medium" => "password2",
            "strong" => "password3",
        ];
        $hint = \HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", $hintArray[defined("WIKINDX_PASSWORD_STRENGTH") ? WIKINDX_PASSWORD_STRENGTH : WIKINDX_PASSWORD_STRENGTH_DEFAULT], $this->noChars) .
            '     ' . $this->messages->text("hint", 'password4')
        );
        $formText = '';
        if (!$super)
        {
            if ($username)
            {
                $formText .= \HTML\td(\FORM\textInput(
                    $this->messages->text("user", "username"),
                    "username",
                    FALSE,
                    20,
                    255
                ) . " " . \HTML\span('*', 'required'));
            }
            $formText .= \HTML\td(\FORM\passwordInput(
                $this->messages->text("user", "password"),
                "password",
                FALSE,
                15,
                255
            ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
            $formText .= \HTML\td(\FORM\passwordInput(
                $this->messages->text("user", "passwordConfirm"),
                "passwordConfirm",
                FALSE,
                15,
                255
            ) . " " . \HTML\span('*', 'required'));
        }
        else
        { // superadmin from Configure menu
            $formText .= \HTML\td(\FORM\textInput(
                $this->messages->text("config", "superUsername"),
                "configUsername",
                $username,
                20
            ) . " " . \HTML\span('*', 'required'));
            $formText .= \HTML\td(\FORM\passwordInput(
                $this->messages->text("config", "superPassword"),
                "password",
                FALSE,
                20
            ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
            $formText .= \HTML\td(\FORM\passwordInput(
                $this->messages->text("user", "passwordConfirm"),
                "passwordConfirm",
                FALSE,
                20
            ) . " " . \HTML\span('*', 'required'));
        }

        return [$formText, $jsString];
    }
    /**
     * Initialize password parameters
     */
    private function init()
    {
        $pwdSize = defined('WIKINDX_PASSWORD_SIZE') ? WIKINDX_PASSWORD_SIZE : WIKINDX_PASSWORD_SIZE_DEFAULT;
        $pwdStrengh = defined('WIKINDX_PASSWORD_STRENGTH') ? WIKINDX_PASSWORD_STRENGTH : WIKINDX_PASSWORD_STRENGTH_DEFAULT;
        
        $errorArray = [
            "weak" => "invalidPassword1",
            "medium" => "invalidPassword2",
            "strong" => "invalidPassword3",
        ];
        $this->invalidPassword = $this->errors->text('inputError', $errorArray[$pwdStrengh], $pwdSize, FALSE);
        $regexpArray = [
            'weak' => "/^(?=.*[a-z])(?=.*[A-Z])\\S+$/", // UPPER/lower Latin, no spaces
            'medium' => "/^(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])\\S+$/", // UPPER/lower Latin and numbers, no spaces
            'strong' => "/^(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[$@#!%*?&€])\\S+$/", // UPPER/lower Latin, numbers, and special chars, no spaces
        ];
        $this->regexp = $regexpArray[$pwdStrengh];
    }
}
