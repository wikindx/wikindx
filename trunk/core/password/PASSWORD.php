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
 *	PASSWORD class.
 *
 *	Functions for passwords
 */
class PASSWORD
{
    /**
     * Create and return an array of two strings for the username/password form
     *
     * @param bool|string $usersUsername TRUE (default) if usersUsername should be part of the form
     * @param bool|string $super FALSE (default) if this is for superadmin in the configure form
     * @param array $formData if reprinting form on validation error (passwords are not stored!)
     *
     * @return array Array of 2 strings: 1. form element string; 2. javascript for form header
     */
    public function createElements($usersUsername = TRUE, $super = FALSE, $formData = [])
    {
        $errors = FACTORY_ERRORS::getInstance();
        $messages = FACTORY_MESSAGES::getInstance();
        
        $pwdSize = defined('WIKINDX_PASSWORD_SIZE') ? WIKINDX_PASSWORD_SIZE : WIKINDX_PASSWORD_SIZE_DEFAULT;
        $pwdStrength = defined('WIKINDX_PASSWORD_STRENGTH') ? WIKINDX_PASSWORD_STRENGTH : WIKINDX_PASSWORD_STRENGTH_DEFAULT;
        
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
        $regexp = $regexpArray[$pwdStrength];
        
        $invalidPassword = $errors->text('inputError', $errorArray[$pwdStrength], $pwdSize, FALSE);
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
            $messages->text("hint", $hintArray[$pwdStrength], $pwdSize) .
            '     ' . $messages->text("hint", 'password4')
        );
        $formText = '';
        if (!$super) {
            if ($usersUsername) {
            	if (array_key_exists('usersUsername', $formData)) {
            		$usersUsername = $formData['usersUsername'];
            	} else {
            		$usersUsername = FALSE; // initial display
            	}
                $formText .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
                    $messages->text("user", "username"),
                    "usersUsername",
                    $usersUsername,
                    20,
                    255
                ));
            }
            $formText .= \HTML\td(\HTML\span('*', 'required') . \FORM\passwordInput(
                $messages->text("user", "password"),
                "password",
                FALSE,
                15,
                255
            ) . BR . \HTML\span($hint, 'hint'));
            $formText .= \HTML\td(\HTML\span('*', 'required') . \FORM\passwordInput(
                $messages->text("user", "passwordConfirm"),
                "passwordConfirm",
                FALSE,
                15,
                255
            ));
        } else {
            // superadmin from Configure menu
            $formText .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
                $messages->text("config", "superUsername"),
                "usersUsername",
                $usersUsername,
                20
            ));
            $formText .= \HTML\td(\HTML\span('*', 'required') . \FORM\passwordInput(
                $messages->text("config", "superPassword"),
                "password",
                FALSE,
                20
            ) . BR . \HTML\span($hint, 'hint'));
            $formText .= \HTML\td(\HTML\span('*', 'required') . \FORM\passwordInput(
                $messages->text("user", "passwordConfirm"),
                "passwordConfirm",
                FALSE,
                20
            ));
        }

        return [$formText, $jsString];
    }
}
