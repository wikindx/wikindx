<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/*****
* ERRORS class (English)
*
* NOTE TO TRANSLATORS:
*           1/  '###' appearing anywhere in an array value will be replaced by text supplied by the core WIKINDX code.
*				Do not remove it.
*			2/  Do not change the key (the first part) of an array element.
*			3/  Ensure that each array element value is enclosed in double quotes "..." and is followed by a comma "...",
*			4/  Both the class name and the constructor name should be changed to match the (case-sensitive) name of
*				the folder your language files are in.  For example, if you are supplying a Klingon translation and
*				your languages/ folder is languages/kn/, the class and constructor name for the file SUCCESS.php
*				must both be SUCCESS_kn.
*
* NOTE TO DEVELOPERS:
*           1/  Any comments not preceeded by '///' at the start of the line will be ignored by the localization module.
*			2/  All comments should be on one line (no line breaks) and must start at the beginning of the line for the localization module to work.
*			3/  Comments must be of the form '/// dbError_ This array does this' where 'dbError' is the array that the comment refers to or may be of the form
*				'/// dbError_open This is the comment' where 'dbError_open' is the exact array and array key that the comment refers to.
*
*****/
class ERRORS
{
    private $languageArray = [];
    
	public function __construct()
	{
	    $this->languageArray = $this->loadArrays();
	}
/**
* Grab the message
*
* @param string $arrayName
* @param string $indexName
* @param string $extra Optional string that replaces '###' in the array element value string. Default is FALSE
* @param boolean $html Optional boolean for HTML printing (TRUE/default) or plain text (FALSE)
*
* @return string
*/
	public function text($arrayName, $indexName, $extra = FALSE, $html = TRUE)
	{
		$message = $this->internaltext($arrayName, $indexName);
		$message = preg_replace("/###/u", str_replace("\\", "\\\\", trim($extra . "")), $message);
		$message = stripslashes($message);
		
		if($html)
			return \HTML\p(\UTF8\html_uentity_decode(trim($message)), "error", "center");
		else
			return trim($message);
	}
/**
* Grab the message
*
* @param string $arrayName
* @param string $indexName
*
* @return string
*/
	private function internaltext($arrayName, $indexName)
	{
		if(!array_key_exists($arrayName, $this->languageArray))
		{
		    debug_print_backtrace();
			die("<p>Section <strong>$arrayName</strong> not found in translations.</p>");
		}
		if(!array_key_exists($indexName, $this->languageArray[$arrayName]))
		{
		    debug_print_backtrace();
			die("<p>Message <strong>$indexName</strong> not found in section $arrayName of " . __CLASS__ . " class.</p>");
		}
		return $this->languageArray[$arrayName][$indexName];
	}
/**
 * Return English messages
 *
 * @return array
 */
	private function loadArrays()
	{
	    $domain = WIKINDX_LANGUAGE_DOMAIN_DEFAULT;
	    
		return array(
/// dbError_ General database errors
			"dbError" => array(
				"read" => dgettext($domain, "Unable to read database."),
			),
/// sessionError_ PHP Session errors
			"sessionError" => array(
				"write" => dgettext($domain, "Unable to write to session."),
			),
/// inputError_ General user input errors
			"inputError" => array(
				"nan" => dgettext($domain, "Input is not a number ###"),
				"notInt" => dgettext($domain, "Input is not an integer ###"),
				"notFloat" => dgettext($domain, "Input is not a float ###"),
				"missing" => dgettext($domain, "Missing input ###"),
				"invalid" => dgettext($domain, "Invalid input ###"),
				"badUsername" => dgettext($domain, "Insecure username ###"),
				"userExists" => dgettext($domain, "That username already exists"),
				"groupExists" => dgettext($domain, "That group already exists"),
				"bibExists" => dgettext($domain, "That bibliography already exists"),
				"labelExists" => dgettext($domain, "That label already exists"),
				"languageExists" => dgettext($domain, "That language already exists"),
				"mail" => dgettext($domain, "Unable to use email - please contact the WIKINDX administrator (ERROR: ###)"),
				"invalidMail" => dgettext($domain, "Invalid email address"),
/// inputError_noHashKey User registration - the hashKey supplied by email to a user has been deleted from the database as they did not confirm their registration within 10 days.
				"noHashKey" => dgettext($domain, "That key no longer exists. Please re-register"),
				"styleExists" => dgettext($domain, "That style already exists"),
/// inputError_duplicateCustomMap When exporting bibliographies to bibtex or endnote and custom fields are mapped, duplicate field names are not allowed.
				"duplicateCustomMap" => dgettext($domain, "Duplicate export field names"),
/// inputError_incorrect Response given to an incorrect answer in the forgotten password system
				"incorrect" => dgettext($domain, "A question has been answered incorrectly.  Please try again."),
/// inputError_duplicateFieldNames When exporting metadata to bibtex, the user has entered a 'unique' field name that already exists
				"duplicateFieldNames" => dgettext($domain, "Duplicate field names"),
/// inputError_chapterNotNumeric book_chapter resource type:  chapter must be a number
				"chapterNotNumeric" => dgettext($domain, "Chapter must be numeric"),
				"userHasNoGroups" => dgettext($domain, "You do not own any user groups. You must first create a user group before you can create user group bibliographies"),
				"mail2" => dgettext($domain, "Unable to complete the operation due to a mail server error (ERROR: ###)"),
/// inputError_passwordMismatch typed passwords do not match
				"passwordMismatch" => dgettext($domain, "Passwords do not match."),
				"invalidPassword0" => dgettext($domain, "Password must contain at least ### characters."),
				"invalidPassword1" => dgettext($domain, "Password must contain at least ### characters and be a mix of UPPER/lowercase. Spaces are not allowed."),
				"invalidPassword2" => dgettext($domain, "Password must contain at least ### characters and be a mix of UPPER/lowercase and numbers. Spaces are not allowed."),
				"invalidPassword3" => dgettext($domain, "Password must contain at least ### characters and be a mix of UPPER/lowercase, numbers, and non-alphanumeric characters ($ @ # ! % * ? & €). Spaces are not allowed."),
				"userTagExists" => dgettext($domain, "That user tag already exists"),
/// inputError_captcha CAPTCHA words entered incorrect
				"captcha" => dgettext($domain, "Answer incorrect"),
/// inputError_maxInputVars Too many input vars for PHP to handle
				"maxInputVars" => dgettext($domain, "Too many input variables selected for PHP to handle (input > ###). You can adjust this increasing max_input_vars in php.ini."),
				"ldapConnect" => dgettext($domain, "Cannot connect to LDAP server. Server and/or port are not plausible"),
				"ldapSetOption" => dgettext($domain, "Error while setting LDAP options"),
				"ldapBind" => dgettext($domain, "Cannot connect to LDAP server. Unable to bind"),
				"ldapEmptyBindCredentials" => dgettext($domain, "Cannot connect to LDAP server. Unable to bind"),
				"ldapSearch" => dgettext($domain, "Error while searching the LDAP catalog"),
				"ldapGetEntries" => dgettext($domain, "Error reading LDAP entries"),
				"notRegistered"  => dgettext($domain, "You must be a registered user to access this"),
				"tooFewKeywordGroups" => dgettext($domain, "A keyword group requires at least two keywords in it"),
				"keywordGroupNotMember" => dgettext($domain, "You neither own nor are a member of that keyword group"),
			),
/// file_ File operations (import/export)
			"file" => array(
				"write" => dgettext($domain, "Unable to write to file ###"),
				"noSql" => dgettext($domain, "You must first list or select resources"),
				"read" => dgettext($domain, "Unable to read directory or file ###"),
				"empty" => dgettext($domain, "You have not yet exported any files"),
				"upload" => dgettext($domain, "File upload error"),
				"folder" => dgettext($domain, "Unable to create directory"),
				"fieldMap" => dgettext($domain, "You may not map more than one unknown field to the same WIKINDX field"),
/// file_fileSize Don't translate 'post_max_size', 'upload_max_filesize' and 'php.ini'
				"fileSize" => dgettext($domain, "Attachment exceeds maximum post_max_size or upload_max_filesize in php.ini (> ### bytes)"),
				"attachmentExists" => dgettext($domain, "Attachment already exists for this resource"),
				"missing" => dgettext($domain, "File not found"),
				"imageExists" => dgettext($domain, "Image already exists as ###"),
				"uploadType" => dgettext($domain, "File type not allowed (only JPEG, GIF, PNG, and WEBP)"),
				"imageSize" => dgettext($domain, "Image above maximum allowed size of ###MB"),
				"uploadSize" => dgettext($domain, "One or more attachments exceed the maximum size (>### bytes). If you are the admin, try editing both 'post_max_size' and 'upload_max_filesize' in php.ini."),
			),
/// done_ Following input, editing operations etc., these messages are printed when a user attempts to redo the operation (usually by clicking on browser's 'reload' button)
			"done" => array(
				"resource" => dgettext($domain, "You have already created/edited/deleted that resource"),
				"note" => dgettext($domain, "You have already created/edited/deleted that note"),
				"quote" => dgettext($domain, "You have already created/edited/deleted that quote"),
				"paraphrase" => dgettext($domain, "You have already created/edited/deleted that paraphrase"),
				"musing" => dgettext($domain, "You have already created/edited/deleted that musing"),
				"idea" => dgettext($domain, "You have already created/edited/deleted that idea"),
				"abstract" => dgettext($domain, "You have already created/edited/deleted that abstract"),
				"creator" => dgettext($domain, "You have already created/edited/deleted that creator"),
				"publisher" => dgettext($domain, "You have already created/edited/deleted that publisher"),
				"collection" => dgettext($domain, "You have already created/edited/deleted that collection"),
				"fileImport" => dgettext($domain, "You have already imported that file"),
				"keyword" => dgettext($domain, "You have already edited that keyword"),
				"keywordDelete" => dgettext($domain, "You have already deleted that keyword"),
				"register" => dgettext($domain, "You have already registered"),
				"bibliography" => dgettext($domain, "You have already created/edited/deleted that bibliography"),
				"custom" => dgettext($domain, "You have already edited/deleted that custom field"),
				"attachAdd" => dgettext($domain, "You have already attached that file"),
				"attachDelete" => dgettext($domain, "You have already deleted those files"),
				"group" => dgettext($domain, "You have already created/edited/deleted that user group"),
				"urlAdd" => dgettext($domain, "You have already added that URL"),
				"userTag" => dgettext($domain, "You have already created/edited/deleted that user tag"),
				"news" => dgettext($domain, "You have already created/edited/deleted that news item"),
				"keywordGroupDelete" => dgettext($domain, "You have already deleted that keyword"),
			),
/// warning_ Warning type messages
			"warning" => array(
/// warning_resourceExists When inputting a new resource
				"resourceExists" => dgettext($domain, "At least one resource of that type with that title already exists"),
/// warning_superadminOnly WIKINDX not in multi user mode - this is displayed at the logon screen
				"superadminOnly" => dgettext($domain, "Multi user has not been enabled - only the superadmin may log on"),
/// warning_creatorExists When editing a creator, the new one may be the same as an existing one.
				"creatorExists" => dgettext($domain, "A creator of that name already exists in the database"),
/// warning_publisherExists When editing a publisher, the new one may be the same as an existing one.
				"publisherExists" => dgettext($domain, "A publisher of that name and location already exists in the database"),
/// warning_collectionExists When editing a collection, the new one may be the same as an existing one.
				"collectionExists" => dgettext($domain, "A collection with those details already exists in the database"),
/// warning_keywordExists When editing a keyword, the new one may be the same as an existing one.
				"keywordExists" => dgettext($domain, "A keyword of that name already exists in the database"),
/// warning_forget1 For the forgotten password system: '###' is the admin's email address which may or may not be available.  If it is, the '###' may be something like '(me@blah.com).' otherwise it will be '.'.
				"forget1" => dgettext($domain, "Unfortunately you have not stored any question/answer pairs or you have entered your details incorrectly so WIKINDX is unable to help you.  Please contact the WIKINDX administrator ###"),
				"forget2" => dgettext($domain, "There is more than one user with that email address so WIKINDX is unable to help you. Please contact the WIKINDX administrator for further help or enter your username."),
/// warning_forget3 Don't translate 'SMTP'.  In this case, '###' is the admin's email address which may or may not be available.  If it is, the '###' may be something like '(me@blah.com).' otherwise it will be '.'.
				"forget3" => dgettext($domain, "WIKINDX is unable to email you because the SMTP server does not appear to be available.  Please contact the WIKINDX administrator ###"),
				"quarantine" => dgettext($domain, "Resource has been quarantined until an ADMIN approves it"),
				"pluginVersion1" => dgettext($domain, "Incompatible plugins: ###"),
				"ideaDelete" => dgettext($domain, "If you delete the main idea, the entire thread will be deleted"),
				"blocked" => dgettext($domain, "The administrator of this wikindx has denied you access. Please contact the administrator for further details."),
				"noBibliographies" => dgettext($domain, "There are no user bibliographies in the database"),
			),
/// import bibliography error messages
			"import" => array(
				"empty" => dgettext($domain, "No valid entries found in the file"),
			),
/// configure components messages
			"components" => array(
/// components_adminFailed This is used as preamble text. e.g. Admin action failed: No file selected.
				"adminFailed" => dgettext($domain, "Admin action failed: ###"),
				"list" => dgettext($domain, "An error occurred while downloading the component list from the update server. Please try again later."),
				"parse" => dgettext($domain, "An error occurred while parsing the component list from the update server. Please check for updates again."),
				"unknown" => dgettext($domain, "Unknown component."),
				"pluginConflict" => dgettext($domain, "Two or more inline plugins have been assigned the same container: ###. The configuration has not been updated."),
				"invalidInline" => dgettext($domain, "The value of \$container is invalid. The configuration has not been updated."),
				"invalidMenu" => dgettext($domain, "The value of \$menus is invalid. The configuration has not been updated."),
				"invalidConfigLoading" => dgettext($domain, "Loading failed. The configuration has not been updated."),
				"invalidConfigClassName" => dgettext($domain, "The  CONFIG class is incorrectly named. The configuration has not been updated."),
				"missingConfigClassMember" => dgettext($domain, "Member ### of the CONFIG class is missing. The configuration has not been updated."),
				"listDownloadFail" => dgettext($domain, "The component list has not yet been downloaded."),
			),
		);
	}
}
