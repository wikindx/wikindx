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
 *	PASSWORD class.
 *
 *	Functions for passwords
 */
class PASSWORD
{
    /**
     * Create and return an array of two strings for the username/password form
     *
     * @param bool|string $username TRUE (default) if username should be part of the form
     * @param bool|string $super FALSE (default) if this is for superadmin in the configure form
     *
     * @return array Array of 2 strings: 1. form element string; 2. javascript for form header
     */
    public function createElements($username = TRUE, $super = FALSE)
    {
        $db = FACTORY_DB::getInstance();
        $errors = FACTORY_ERRORS::getInstance();
        $messages = FACTORY_MESSAGES::getInstance();
        
        $pwdSize = defined('WIKINDX_PASSWORD_SIZE') ? WIKINDX_PASSWORD_SIZE : WIKINDX_PASSWORD_SIZE_DEFAULT;
        $pwdStrengh = defined('WIKINDX_PASSWORD_STRENGTH') ? WIKINDX_PASSWORD_STRENGTH : WIKINDX_PASSWORD_STRENGTH_DEFAULT;
        
        $errorArray = [
            "weak" => "invalidPassword1",
            "medium" => "invalidPassword2",
            "strong" => "invalidPassword3",
        ];
        $regexpArray = [
            'weak' => "/^(?=.*[a-z])(?=.*[A-Z])\\S+$/", // UPPER/lower Latin, no spaces
            'medium' => "/^(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])\\S+$/", // UPPER/lower Latin and numbers, no spaces
            'strong' => "/^(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[$@#!%*?&€])\\S+$/", // UPPER/lower Latin, numbers, and special chars, no spaces
        ];
        $regexp = $regexpArray[$pwdStrengh];
        
        $invalidPassword = $errors->text('inputError', $errorArray[$pwdStrengh], $pwdSize, FALSE);
        $missing = $errors->text('inputError', 'missing', FALSE, FALSE);
        $misMatch = $errors->text('inputError', 'passwordMismatch', FALSE, FALSE);
        
        $jsString = "this, " . $regexp . ", " . $pwdSize . ", '" . $missing . "', '" . $misMatch . "', '" . $invalidPassword . "'";
        
        $hintArray = [
            "weak" => "password1",
            "medium" => "password2",
            "strong" => "password3",
        ];
        $hint = \HTML\aBrowse(
            'green',
            '',
            $messages->text("hint", "hint"),
            '#',
            "",
            $messages->text("hint", $hintArray[$pwdStrengh], $pwdSize) .
            '     ' . $messages->text("hint", 'password4')
        );
        $formText = '';
        if (!$super) {
            if ($username) {
                $formText .= \HTML\td(\FORM\textInput(
                    $messages->text("user", "username"),
                    "usersUsername",
                    FALSE,
                    20,
                    255
                ) . " " . \HTML\span('*', 'required'));
            }
            $formText .= \HTML\td(\FORM\passwordInput(
                $messages->text("user", "password"),
                "password",
                FALSE,
                15,
                255
            ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
            $formText .= \HTML\td(\FORM\passwordInput(
                $messages->text("user", "passwordConfirm"),
                "passwordConfirm",
                FALSE,
                15,
                255
            ) . " " . \HTML\span('*', 'required'));
        } else {
            // superadmin from Configure menu
            $formText .= \HTML\td(\FORM\textInput(
                $messages->text("config", "superUsername"),
                "usersUsername",
                $username,
                20
            ) . " " . \HTML\span('*', 'required'));
            $formText .= \HTML\td(\FORM\passwordInput(
                $messages->text("config", "superPassword"),
                "password",
                FALSE,
                20
            ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
            $formText .= \HTML\td(\FORM\passwordInput(
                $messages->text("user", "passwordConfirm"),
                "passwordConfirm",
                FALSE,
                20
            ) . " " . \HTML\span('*', 'required'));
        }

        return [$formText, $jsString];
    }
}
