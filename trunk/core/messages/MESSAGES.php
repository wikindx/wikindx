<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
* MESSAGES class (English)
*
* NOTE TO TRANSLATORS:
*           1/  '###' appearing anywhere in an array value will be replaced by text supplied by the core WIKINDX code.
*				Do not remove it.
*			2/  Do not change the key (the first part) of an array element.
*			3/  Ensure that each array element value is enclosed in double quotes "..." and is followed by a comma "...",
*			4/  The class name should be changed to match the (case-sensitive) name of
*				the folder your language files are in.  For example, if you are supplying a Klingon translation and
*				your languages/ folder is languages/kn/, the class name for the file SUCCESS.php
*				must be SUCCESS_kn.
*
* NOTE TO DEVELOPERS:
*           1/  Any comments not preceeded by '///' at the start of the line will be ignored by the localization module.
*			2/  All comments should be on one line (no line breaks) and must start at the beginning of the line for the localization module to work.
*			3/  Comments must be of the form '/// dbError_ This array does this' where 'dbError' is the array that the comment refers to or may be of the form
*				'/// dbError_open This is the comment' where 'dbError_open' is the exact array and array key that the comment refers to.
*/
class MESSAGES
{
    private $languageArray = [];
    
	function __construct()
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
			return \UTF8\html_uentity_decode(trim($message));
		else
			return trim($message);
	}
/**
* Grab the message
*
* @param string $arrayName
* @param string $indexName.
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
			die("<p>Message <strong>$indexName</strong> not found in section <strong>$arrayName</strong> of " . __CLASS__ . " class.</p>");
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
/// heading_ Page headings
		    "heading" => array(
				"configure" => dgettext($domain, "Configure Wikindx"),
				"logon" => dgettext($domain, "Logon"),
				"list" => dgettext($domain, "List Resources"),
				"search" => dgettext($domain, "Search Resources"),
				"select" => dgettext($domain, "Select Resources"),
				"addToBib" => dgettext($domain, "Add selected to bibliography"),
				"addToCategory" => dgettext($domain, "Add selected to categories"),
				"addToSubcategory" => dgettext($domain, "Add selected to subcategories"),
				"addToKeyword" => dgettext($domain, "Add selected to keywords"),
				"addToUserTag" => dgettext($domain, "Add selected to user tags"),
				"addToLanguage" => dgettext($domain, "Add selected to languages"),
				"newResource" => dgettext($domain, "New Resource"),
				"editResource" => dgettext($domain, "Edit Resource"),
				"editCategory" => dgettext($domain, "Categories"),
				"editSubcategory" => dgettext($domain, "Subcategories"),
				"edit" => dgettext($domain, "Edit WIKINDX Resources ###"),
				"add" => dgettext($domain, "Add WIKINDX Resources ###"),
				"delete2" => dgettext($domain, "Delete WIKINDX Resources ###"),
				"browseCreator" => dgettext($domain, "Browse Creators"),
				"browseCited" => dgettext($domain, "Browse Cited Creators"),
				"browseKeyword" => dgettext($domain, "Browse Keywords"),
				"browseKeywordGroup" => dgettext($domain, "Browse Keyword Groups"),
				"browseCollection" => dgettext($domain, "Browse Collections"),
				"browseCategory" => dgettext($domain, "Browse Categories"),
				"browseSubcategory" => dgettext($domain, "Browse Subcategories"),
				"browseLanguage" => dgettext($domain, "Languages"),
				"browsePublisher" => dgettext($domain, "Browse Publishers"),
				"browseType" => dgettext($domain, "Browse Resource Types"),
				"browseYear" => dgettext($domain, "Browse Publication Year"),
				"browseBibliography" => dgettext($domain, "Browse User Bibliographies"),
				"browseUserTags" => dgettext($domain, "User Tags"),
				"browseUser" => dgettext($domain, "Browse System Users"),
				"browseDept" => dgettext($domain, "Browse Departments"),
				"browseInst" => dgettext($domain, "Browse Institutions"),
/// heading_categoryTree A category tree is a list of browsable categories with their associated keywords
				"categoryTree" => dgettext($domain, "Category Tree"),
				"statistics" => dgettext($domain, "Statistics"),
				"bibs" => dgettext($domain, "Bibliographies"),
				"preferences" => dgettext($domain, "User Preferences"),
				"resources" => dgettext($domain, "WIKINDX Resources"),
				"bookmark" => dgettext($domain, "Bookmarks"),
				"userAdd" => dgettext($domain, "Add User"),
				"userEdit" => dgettext($domain, "Edit User"),
				"userDelete" => dgettext($domain, "Delete User"),
				"userBlock" => dgettext($domain, "Block and Unblock Users"),
/// heading_basket Resource basket is a temporary collection of resources while the user is logged on
				"basket" => dgettext($domain, "Resource basket"),
				"basketDelete" => dgettext($domain, "Delete Basket"),
				"attach" => dgettext($domain, "File Attachments ###"),
				"url" => dgettext($domain, "File URLs ###"),
/// heading_myWikindx In a multi-user WIKINDX, settings for a user's bibliographies and personal details
				"myWikindx" => dgettext($domain, "My Wikindx ###"),
				"forget" => dgettext($domain, "Forgotten Password"),
				"delete" => dgettext($domain, "Delete Resource"),
				"adminKeywords" => dgettext($domain, "Administer Keywords"),
				"abstract" => dgettext($domain, "Resource Abstract"),
				"notes" => dgettext($domain, "Resource Notes"),
				"quotes" => dgettext($domain, "Resource Quotes"),
				"paraphrases" => dgettext($domain, "Resource Paraphrases"),
/// heading_musings Musings are thoughts about a resource
				"musings" => dgettext($domain, "Resource Musings"),
/// heading_ideas Ideas are general thoughts
				"ideas" => dgettext($domain, "Ideas"),
/// heading_ideaThread Ideas can be threaded as main idea and sub-ideas
				"ideaThread" => dgettext($domain, "Idea Thread"),
				"abstractDelete" => dgettext($domain, "Delete Resource Abstract"),
				"notesDelete" => dgettext($domain, "Delete Resource Notes"),
				"commentDelete" => dgettext($domain, "Delete Comment"),
				"quoteDelete" => dgettext($domain, "Delete Resource Quote"),
				"paraphraseDelete" => dgettext($domain, "Delete Resource Paraphrase"),
				"musingDelete" => dgettext($domain, "Delete Resource Musing"),
				"resourceEdit" => dgettext($domain, "Edit Resource"),
				"mergeCreators" => dgettext($domain, "Merge Creators"),
				"groupCreators" => dgettext($domain, "Group Creators"),
				"ungroupCreators" => dgettext($domain, "Ungroup Creators"),
				"adminCustom" => dgettext($domain, "Administer Custom Fields"),
				"adminLanguage" => dgettext($domain, "Administer Languages"),
				"adminImages" => dgettext($domain, "Delete Images"),
				"userEditField" => dgettext($domain, "Resource Custom Field"),
				"quarantine" => dgettext($domain, "Quarantined Resources"),
				"randomMetadata" => dgettext($domain, "Random Metadata"),
				"cms" => dgettext($domain, "Content Management System Gateway"),
				"news" => dgettext($domain, "News"),
				"newsAdd" => dgettext($domain, "Add News"),
				"newsEdit" => dgettext($domain, "Edit News"),
				"newsDelete" => dgettext($domain, "Delete News"),
				"register" => dgettext($domain, "Register User"),
/// heading_emailFriend Email a single resource link to a friend
				"emailFriend" => dgettext($domain, "Email resource to friend"),
				"bibtexImport" => dgettext($domain, "Import BibTeX Bibliography"),
				"bibtexPaste" => dgettext($domain, "Paste BibTeX Bibliography"),
				"exportBibtex" => dgettext($domain, "Export BibTeX"),
				"adminComponents" => dgettext($domain, "Administer Components"),
				"addCitation" => dgettext($domain, "Add Citation"),
/// heading_exportCoins COinS is a bibliographic format used by, for instance, Zotero. Don't translate 'COinS'
				"exportCoins" => dgettext($domain, "Export COinS"),
				"ldapTester" => dgettext($domain, "LDAP Tester"),
			),
/// authorize_ User authorization
		    "authorize" => array(
				"writeLogon" => dgettext($domain, "Enter your username and password to logon to the system. Your browser preferences must allow cookies for this domain."),
				"superLogon" => dgettext($domain, "Enter your superadmin username and password to logon"),
				"readOnly" => dgettext($domain, "READ-ONLY access"),
			),
/// config_ WIKINDX administrator/default configuration
		    "config" => array(
/// config_title This WIKINDX's title
				"title" => dgettext($domain, "Title displayed on each page"),
				"description" => dgettext($domain, "Front Page Description"),
				"superUsername" => dgettext($domain, "Superadmin username"),
				"superPassword" => dgettext($domain, "Superadmin password"),
				"deleteSeconds" => dgettext($domain, "Seconds before export file is marked for deletion"),
				"paging" => dgettext($domain, "Default no. resources to display/screen (paging)"),
/// config_pagingTagCloud Tag clouds are the display of creators, keywords, collections etc. found under the resources menu.
				"pagingTagCloud" => dgettext($domain, "Default no. tag cloud items to display/screen (paging)"),
				"maxPaging" => dgettext($domain, "Default no. paging links to display/screen"),
				"language" => dgettext($domain, "Language/locale"),
				"template" => dgettext($domain, "Template"),
				"timezone" => dgettext($domain, "Timezone"),
				"imagesAllow" => dgettext($domain, "Allow images"),
				"imagesMaxSize" => dgettext($domain, "Images max. size"),
				"attachmentsMaxSize" => dgettext($domain, "Attachments max. size"),
				"tagLowColour" => dgettext($domain, "Tag cloud, low colour"),
				"tagHighColour" => dgettext($domain, "Tag cloud, high colour"),
				"tagLowSize" => dgettext($domain, "Tag cloud, low size"),
				"tagHighSize" => dgettext($domain, "Tag cloud, high size"),
				"deactivateResourceTypes" => dgettext($domain, "Deactivated resource types"),
				"activeResourceTypes" => dgettext($domain, "Active resource types"),
				"LdapUse" => dgettext($domain, "Enable LDAP authentication"),
				"LdapServer" => dgettext($domain, "Server"),
				"LdapPort" => dgettext($domain, "Port"),
				"LdapServerEncryption" => dgettext($domain, "Encryption"),
				"LdapServerBindType" => dgettext($domain, "Bind type"),
				"LdapServerBindLogin" => dgettext($domain, "Bind user login"),
				"LdapServerBindPassword" => dgettext($domain, "Bind user password"),
				"LdapServerBindDomain" => dgettext($domain, "Bind domain"),
				"LdapServerBindDomainFormat" => dgettext($domain, "Bind domain format"),
				"LdapSearchMethod" => dgettext($domain, "Search method"),
				"LdapSearchOperator" => dgettext($domain, "Search operator"),
				"LdapUserAttributLogin" => dgettext($domain, "Login attribute"),
				"LdapUserOu" => dgettext($domain, "Organizational Units (OU)"),
				"LdapGroupDn" => dgettext($domain, "Group DNs"),
				"LdapUserCreate" => dgettext($domain, "Create user"),
				"ldapTestUsername" => dgettext($domain, "Login"),
				"ldapTestPassword" => dgettext($domain, "Password"),
				"ldapTester" => dgettext($domain, "Open LDAP Tester"),
				"ldapTestSuccess" => dgettext($domain, "LDAP successfully configured"),
/// config_authGate If checked, user must click on OK after login in order to proceed. Used for situations such as notification about privacy policies such as that mandated by the EU's GDPR
				"authGate" => dgettext($domain, "Authentication gate"),
				"authGateMessage" => dgettext($domain, "Message for user"),
				"authGateReset" => dgettext($domain, "Reset authentication gate flag for all users"),
				"passwordSize" => dgettext($domain, "No. chars in password"),
				"passwordStrength" => dgettext($domain, "Password strength"),
				"passwordWeak" => dgettext($domain, "Weak"),
				"passwordMedium" => dgettext($domain, "Medium"),
				"passwordStrong" => dgettext($domain, "Strong"),
				"mailServer" => dgettext($domain, "Enable mail operations"),
				"mailFrom" => dgettext($domain, "From address"),
				"mailReplyTo" => dgettext($domain, "Reply-to address"),
				"mailBackend" => dgettext($domain, "Mail backend"),
				"mailReturnPath" => dgettext($domain, "Sendmail return path"),
				"mailSmPath" => dgettext($domain, "Sendmail path"),
				"mailSmtpServer" => dgettext($domain, "SMTP server"),
				"mailSmtpPort" => dgettext($domain, "SMTP port"),
				"mailSmtpEncrypt" => dgettext($domain, "SMTP encryption"),
				"mailSmtpPersist" => dgettext($domain, "SMTP persist"),
				"mailSmtpAuth" => dgettext($domain, "SMTP authorization"),
				"mailSmtpUsername" => dgettext($domain, "SMTP username"),
				"mailSmtpPassword" => dgettext($domain, "SMTP password"),
				"mailTest" => dgettext($domain, "Test email address"),
				"mailTestSuccess" => dgettext($domain, "If you are reading this email, you have correctly configured email for WIKINDX"),
				"mailTransactionReport" => dgettext($domain, "Mail Test Transaction Report"),
				"siteMapAllow" => dgettext($domain, "Enable SiteMap"),
				"browserTabID" => dgettext($domain, "Enable BrowserTabID"),
				"rssAllow" => dgettext($domain, "Enable RSS"),
				"rssDisplay" => dgettext($domain, "Display"),
				"rssLanguage" => dgettext($domain, "Language"),
				"rssBibstyle" => dgettext($domain, "Bibliographic style"),
				"rssTitle" => dgettext($domain, "Title"),
				"rssDescription" => dgettext($domain, "Description"),
				"rssLimit" => dgettext($domain, "Display limit"),
				"cmsAllow" => dgettext($domain, "Enable CMS"),
				"cmsDisplay" => dgettext($domain, "Display"),
				"cmsLanguage" => dgettext($domain, "Language"),
				"cmsBibstyle" => dgettext($domain, "Bibliographic style"),
				"cmsSql" => dgettext($domain, "Enable SQL queries"),
				"cmsDbUser" => dgettext($domain, "Database username"),
				"cmsDbPassword" => dgettext($domain, "Database password"),
				"gsAllow" => dgettext($domain, "Enable Google Scholar"),
				"gsAttachment" => dgettext($domain, "Only attachments"),
				"restrictUserId" => dgettext($domain, "Restricted user"),
				"options" => dgettext($domain, "Configuration options"),
				"superAdmin" => dgettext($domain, "Super admin"),
				"front" => dgettext($domain, "Front page"),
				"lists" => dgettext($domain, "Resource lists"),
				"display" => dgettext($domain, "General display"),
				"appearance" => dgettext($domain, "Appearance"),
				"forget" => dgettext($domain, "Forgotten password"),
				"notification" => dgettext($domain, "Email notification"),
				"resources" => dgettext($domain, "Resources"),
				"users" => dgettext($domain, "Users"),
				"authentication" => dgettext($domain, "Authentication"),
				"email" => dgettext($domain, "Email"),
				"files" => dgettext($domain, "Files/Attachments"),
				"rss" => dgettext($domain, "RSS"),
				"cms" => dgettext($domain, "CMS"),
				"gs" => dgettext($domain, "Google Scholar"),
				"misc" => dgettext($domain, "Miscellaneous"),
				"debugging" => dgettext($domain, "Debugging"),
/// config_templateMenu Template menus can have their levels reduced from sub-submenus to submenus to menus
				"templateMenu" => dgettext($domain, "Template menu"),
				"templateMenu1" => dgettext($domain, "All menu levels"),
				"templateMenu2" => dgettext($domain, "Reduce by one level"),
				"templateMenu3" => dgettext($domain, "Reduce by two levels"),
				"style" => dgettext($domain, "Bibliographic style"),
				"stringLimit" => dgettext($domain, "Limit no. characters in select box text"),
				"userRegistration" => dgettext($domain, "Allow user registration"),
/// config_registrationModerate Request emails be sent to the admin to approve or deny user registration requests
				"registrationModerate" => dgettext($domain, "Moderate registration requests (you must provide a valid email address)"),
/// config_registrationRequest1 Inform the user that his/her registration request requires approval by the admin
				"registrationRequest1" => dgettext($domain, "Your request will require approval by the WIKINDX administrator"),
				"registrationRequest2" => dgettext($domain, "Please give the reasons for your registration request (this will be emailed to the administrator). At the very least you should give your name"),
/// config_captcha1 User registration can use CAPTCHA technology
				"captcha1" => dgettext($domain, "Answer the question if you are not a bot"),
				"multiUser" => dgettext($domain, "Multi user mode"),
				"notify" => dgettext($domain, "Email notification to registered users of resource additions and amendments"),
				"statistics" => dgettext($domain, "Email view and download statistics to users"),
				"imgWidthLimit" => dgettext($domain, "Max. pixel width for images"),
				"imgHeightLimit" => dgettext($domain, "Max. pixel height for images"),
// v3.0 - File attachments (uploading) to resources
				"fileAttach" => dgettext($domain, "Allow file attachments"),
				"fileViewLoggedOnOnly" => dgettext($domain, "Allow only registered users to view file attachments"),
				"debug" => dgettext($domain, "All these should be disabled on a production server"),
				"errorReport" => dgettext($domain, "Print PHP errors and warnings to the screen"),
				"sqlStatements" => dgettext($domain, "Display SQL statements and die on db error"),
				"isTrunk" => dgettext($domain, "Trunk version"),
				"printSql" => dgettext($domain, "Print SQL statements to the screen"),
				"forceSmartyCompile" => dgettext($domain, "Force Smarty templates compilation"),
				"maxPaste" => dgettext($domain, "Max. number of bibTeX entries a user can paste"),
				"urlPrefix" => dgettext($domain, "URL prefix"),
				"displayStatistics" => dgettext($domain, "Display statistics"),
				"displayUserStatistics" => dgettext($domain, "Display user statistics"),
				"kwBibliography" => dgettext($domain, "When browsing a user bibliography, limit the keyword list to keywords found in that bibliography"),
				"importBib" => dgettext($domain, "Allow registered users to import BibTeX bibliographies"),
/// config_lastChanges1 lastChanges2 and lastChanges3 are in a select box and syntactically follow on from lastChanges1 - * is a wildcard referring to the number entered by the user
				"lastChanges1" => dgettext($domain, "Display on the front page"),
				"lastChanges2" => dgettext($domain, "Up to * recently added or edited resources"),
				"lastChanges3" => dgettext($domain, "Resources added or edited in the last * days"),
/// config_lastChanges4 '...show up to a maximum of' is followed by a number
				"lastChanges4" => dgettext($domain, "If showing resources added or edited in the last number of days, show up to a maximum of"),
/// config_useWikindxKey When displaying single resources or exporting a list to bibTeX,  (probably not necessary to translate 'ID' -- the database resource ID)
				"useWikindxKey" => dgettext($domain, "Use the WIKINDX-generated bibTeX key (author.ID) in preference to the default authorYear bibTeX key"),
				"useBibtexKey" => dgettext($domain, "Display the bibTeX key from the original bibliographic import (if available) in preference to either the WIKINDX-generated bibTeX key or the authorYear bibTeX key"),
				"emailNews" => dgettext($domain, "Email news items to registered users"),
				"emailNewRegistrations" => dgettext($domain, "When a new user registers, notify the administrator at this email address"),
				"metadataAllow" => dgettext($domain, "Enable the metadata subsystem (quotes, paraphrases, comments etc.) for all users"),
				"metadataUserOnly" => dgettext($domain, "If disabled, allow registered users to still use the metadata subsystem"),
				"displayBibtexLink" => dgettext($domain, "When viewing lists of resources, display an icon to view the BibTeX version of each resource"),
				"displayCmsLink" => dgettext($domain, "When viewing lists of resources, display a hyperlink to a pop-up window to generate a CMS (Content Management System) 'replacement tag' for each resource"),
				"pagingStyle" => dgettext($domain, "When viewing lists ordered by creator or title, replace the numerical paging links with an alphabetical list"),
				"quarantine" => dgettext($domain, "Allow new resources to be quarantined from non-admin view until approved"),
				"ListLink" => dgettext($domain, "For resource lists, make each resource hyperlinked to viewing that resource"),
				"noSort" => dgettext($domain, "Ignore list (sorting)"),
				"searchFilter" => dgettext($domain, "Ignore list (searching)"),
				"denyReadOnly" => dgettext($domain, "Deny read only access"),
				"readOnlyAccess" => dgettext($domain, "If read only access is allowed, bypass the login prompt"),
				"originatorEditOnly" => dgettext($domain, "Only admins and the resource originator can edit the resource"),
				"globalEdit" => dgettext($domain, "Registered users can globally edit creators, collections, publishers and keywords"),
				"impressum" => dgettext($domain, "Impressum"),
			),
/// components_ Admin components
		    "components" => array(
				"manualComponent" => dgettext($domain, "Manually install a component: "),
				"packageFile" => dgettext($domain, "Package file: "),
				"hashFile" => dgettext($domain, "Hash file: "),
				"checkUpdates" => dgettext($domain, "Check for updates"),
				"lastUpdate" => dgettext($domain, "Last updated"),
				"defaultQuery" => dgettext($domain, "Users have a favorite template or style that is no longer available (disabled or uninstalled). Do you want to replace this preference with the default values?"),
				"defaultInstall" => dgettext($domain, "Fix users preferences:"),
				"browseByType" => dgettext($domain, "Browse by type"),
				"description" => dgettext($domain, "Description"),
				"credits" => dgettext($domain, "Credits"),
				"version" => dgettext($domain, "Version"),
				"licence" => dgettext($domain, "Licence"),
				"package" => dgettext($domain, "Package"),
				"action" => dgettext($domain, "Action"),
				"installed" => dgettext($domain, "Installed: "),
				"latest" => dgettext($domain, "Latest: "),
				"enable" => dgettext($domain, "Enable"),
				"disable" => dgettext($domain, "Disable"),
				"install" => dgettext($domain, "Install"),
				"uninstall" => dgettext($domain, "Uninstall"),
				"reinstall" => dgettext($domain, "Reinstall"),
				"update" => dgettext($domain, "Update"),
				"configure" => dgettext($domain, "Configure"),
				"installError" => dgettext($domain, "an error occurred while installing the package: ###."),
				"uninstallError" => dgettext($domain, "the package ### cannot be uninstalled."),
				"vendorUninstallError" => dgettext($domain, "a vendor component cannot be uninstalled"),
/// components_downloadSignature is printed as one message with corruptDownload and corruptHash messages
				"downloadSignature" => dgettext($domain, "the signature of the downloaded resource ### differs from that online."),
				"corruptDownload" => dgettext($domain, "&nbsp;The component has not been downloaded completely or is corrupted (online hash: ###,"),
				"computedHash" => dgettext($domain, "&nbsp;computed hash: ###)."),
				"downloadError" => dgettext($domain, "an error occurred while downloading the resource: ###."),
				"missingCurl" => dgettext($domain, "without the <strong>curl</strong> PHP extension it is impossible to download the update information."),
				"missingCompression" => dgettext($domain, "without a PHP compression extension it is impossible to retrieve a package from the update server. Please install PHP Zip extension (.zip file)."),
				"nothingToDo" => dgettext($domain, "Nothing to do or the upload failed."),
				"installSuccess" => dgettext($domain, "Package ### installed."),
				"wrongParameters" => dgettext($domain, "Wrong parameters"),
			),
/// viewResource_ Viewing a single resource
		    "viewResource" => array(
/// viewResource_viewIndex Total views / no. days available
				"viewIndex" => dgettext($domain, "Views index: ###%"),
/// viewResource_download Total downloads / no. days available
				"download" => dgettext($domain, "Downloads index: ###%"),
				"popIndex" => dgettext($domain, "Popularity index: ###%"),
				"numDownloads" => dgettext($domain, "Downloads: ###"),
/// viewResource_maturityIndex The maturity index is another index input by the administrator based on the popularity, num. downloads, number of metadata and user reviews.
				"maturityIndex" => dgettext($domain, "Maturity index: ###"),
				"numAccesses" => dgettext($domain, "Views: ###"),
				"type" => dgettext($domain, "Resource type"),
				"notes" => dgettext($domain, "Notes"),
				"quotes" => dgettext($domain, "Quotes"),
				"paraphrases" => dgettext($domain, "Paraphrases"),
				"musings" => dgettext($domain, "Musings"),
				"attachments" => dgettext($domain, "Attachments"),
				"urls" => dgettext($domain, "URLs"),
				"language" => dgettext($domain, "Language"),
				"viewDetails" => dgettext($domain, "View all bibliographic details"),
			),
/// metadata_ Metadata are quotes, paraphrases, musings and comments
/// metadata_ Metadata are quotes, paraphrases, musings and comments
		    "metadata" => array(
				"quotes" => dgettext($domain, "Quotes"),
				"paraphrases" => dgettext($domain, "Paraphrases"),
				"musings" => dgettext($domain, "Musings"),
				"ideas" => dgettext($domain, "Ideas"),
				"idea" => dgettext($domain, "Idea"),
				"subIdea" => dgettext($domain, "Sub-idea"),
				"quoteComments" => dgettext($domain, "Quote comments"),
				"paraphraseComments" => dgettext($domain, "Paraphrase comments"),
			),
/// resources_ Messages for resources
		    "resources" => array(
				"new" => dgettext($domain, "Do not put any punctuation at the end of text fields: WIKINDX will do this automatically depending on the bibliographic style. You are responsible for correct capitalization of titles and proper names. Fields marked with a### are required unless you have selected from the relevant select box. Text in a text field will override the selected option for any field. Generally, all numbers should be given as arabic cardinal numbers (1, 2, 3...) as the bibliographic style handles any reformatting they require. Pages can be roman numerals and years can be entered as a range (e.g. 2001-2004) or with a modifier (e.g. 'BC', 'BCE' etc.) – where there is a date field with a drop-down calendar, a valid entry here will override corresponding day, month, and year fields. Due to the automatic completion of some fields (e.g. with resources belonging to a collection or a conference, such as a journal or proceedings article), it is best to complete the resource details from the top of the form down."),
				"titleLabel" => dgettext($domain, "Enter the resource titles. If the resource is a book chapter, the title should be an integer (the chapter number) and subtitle and short title will be ignored."),
/// resources_type book, article in book, web resource, thesis etc.
				"type" => dgettext($domain, "Resource type and title"),
				"title" => dgettext($domain, "Title"),
				"subTitle" => dgettext($domain, "Subtitle"),
/// resources_shortTitle Short title of a resource
				"shortTitle" => dgettext($domain, "Short title"),
				"shortJournal" => dgettext($domain, "Short title"),
				"shortBook" => dgettext($domain, "Short title"),
				"shortConference" => dgettext($domain, "Short title"),
				"shortMagazine" => dgettext($domain, "Short title"),
				"bookTitle" => dgettext($domain, "Title of book"),
/// resources_numContributors 'Contributors' are creators, authors, editors, translators etc.
				"numContributors" => dgettext($domain, "Select the number of contributors to this resource"),
/// resources_next Hyperlinks for displaying next resource and previous resource
				"next" => dgettext($domain, "next resource"),
				"previous" => dgettext($domain, "previous resource"),
				"lastChanges" => dgettext($domain, "Recent additions or edits"),
/// resources_pagingStart Paging system (number of resources to display/browser page.
				"pagingStart" => dgettext($domain, "Start"),
				"pagingEnd" => dgettext($domain, "End"),
				"noResult" => dgettext($domain, "No resources found matching your selection"),
/// resources_withChecked For adding or deleting resource lists or selected resources in the list to categories, keywords or user bibliographies.  This is followed by a select box of options as 'With checked: add to categories'
				"withChecked" => dgettext($domain, "With checked"),
				"addToBib" => dgettext($domain, "Add to user bibliography"),
/// resources_deleteFromBib Remove selected resources from the user bibliography currently being browsed
				"deleteFromBib" => dgettext($domain, "Remove from this user bibliography"),
				"deleteResource" => dgettext($domain, "Delete"),
				"addToCategory" => dgettext($domain, "Add to categories"),
				"addToSubcategory" => dgettext($domain, "Add to subcategories"),
				"addToKeyword" => dgettext($domain, "Add to keywords"),
				"addToLanguage" => dgettext($domain, "Add to languages"),
				"addToUserTag" => dgettext($domain, "Add to user tags"),
/// resource_exportCoins1 COinS is the bibliographic format that is used, for instance, by zotero
				"exportCoins1" => dgettext($domain, "Export to COinS"),
				"exportCoins2" => dgettext($domain, "This page contains hidden code in COinS format of the selected resources from the list. This allows you to import the resources into bibliographic software that recognizes the COinS format (e.g. Zotero)"),
				"selectAll" => dgettext($domain, "Use all in list:"),
				"selectDisplay" => dgettext($domain, "Use all displayed:"),
				"selectCheck" => dgettext($domain, "Use all checked:"),
/// resources_organize This is displayed in a selectbox when viewing a list so that users can add/remove the list to/from categories, keywords etc.
				"organize" => dgettext($domain, "Organize"),
				"unquarantineResource" => dgettext($domain, "Approve resource"),
/// resources_general 'General' is the catch-all default category
				"general" => dgettext($domain, "General"),
				"keyword" => dgettext($domain, "Keyword"),
				"glossary" => dgettext($domain, "Glossary"),
				"glossaryMerge" => dgettext($domain, "One or more of the merged keywords has a glossary entry. You can add or edit the target keyword's glossary."),
				"keywordGroup" => dgettext($domain, "Keyword Groups"),
				"keywordGroupNew" => dgettext($domain, "New Keyword Group"),
				"keywordGroupEdit" => dgettext($domain, "Edit Keyword Group"),
				"keywordGroupName" => dgettext($domain, "Keyword Group"),
				"kgDescription" => dgettext($domain, "Description"),
				"creator" => dgettext($domain, "Creator ###"),
				"firstname" => dgettext($domain, "Firstnames"),
				"initials" => dgettext($domain, "Initials"),
				"surname" => dgettext($domain, "Last name"),
/// resources_prefix Prefix for surname - e.g. de Witt, della Croce, von Neumann
				"prefix" => dgettext($domain, "Prefix"),
				"collection" => dgettext($domain, "Collection"),
/// resources_collectionShort Abbreviated titles for journals etc.
				"collectionShort" => dgettext($domain, "Short title"),
				"publisher" => dgettext($domain, "Publisher"),
				"languages" => dgettext($domain, "Languages"),
				"languagesAdd" => dgettext($domain, "Each resource can be defined as being available in one or more languages which are defined here"),
				"languageDefault" => dgettext($domain, "Default language"),
				"categories" => dgettext($domain, "Categories"),
				"subcategories" => dgettext($domain, "Subcategories"),
				"userTags" => dgettext($domain, "User tags"),
				"subcategoryPart" => dgettext($domain, "Subcategory is part of category"),
				"subcategoryKeepCat" => dgettext($domain, "Keep resources in original category"),
				"keywords" => dgettext($domain, "Keywords"),
/// resources_publisherName Publisher name
				"publisherName" => dgettext($domain, "Publisher name"),
/// resources_publisherLocation Publisher location
				"publisherLocation" => dgettext($domain, "Publisher location"),
				"institutionName" => dgettext($domain, "Institution name"),
				"institutionLocation" => dgettext($domain, "Institution location"),
				"publicationYear" => dgettext($domain, "Publication year"),
				"resourceyearYear" => dgettext($domain, "Publication year"),
				"startPublicationYear" => dgettext($domain, "Start publication year"),
				"endPublicationYear" => dgettext($domain, "End publication year"),
				"accessYear" => dgettext($domain, "Year of access"),
				"issueYear" => dgettext($domain, "Year of issue"),
				"date" => dgettext($domain, "Date"),
				"year" => dgettext($domain, "Year"),
				"startYear" => dgettext($domain, "Start Year"),
				"endYear" => dgettext($domain, "End Year"),
				"day" => dgettext($domain, "Day"),
				"startDay" => dgettext($domain, "Start day"),
				"endDay" => dgettext($domain, "End day"),
				"month" => dgettext($domain, "Month"),
				"startMonth" => dgettext($domain, "Start month"),
				"endMonth" => dgettext($domain, "End month"),
				"bibliographies" => dgettext($domain, "Bibliographies"),
				"quote" => dgettext($domain, "Quote"),
				"paraphrase" => dgettext($domain, "Paraphrase"),
				"musing" => dgettext($domain, "Musing"),
/// resources_commentPrivate For public/private comments and musings (first value is above next three which are in a select box)
				"commentPrivate" => dgettext($domain, "Comment is"),
				"musingPrivate" => dgettext($domain, "Musing is"),
				"ideaPrivate" => dgettext($domain, "Idea is"),
				"public" => dgettext($domain, "Public"),
				"private" => dgettext($domain, "Private"),
				"availableToGroups" => dgettext($domain, "Available to User Group: ###"),
/// resources_isbn International Standard Book Number
				"isbn" => dgettext($domain, "ID no. (ISBN etc.)"),
/// resources_doi Digital Object Identifier (http://doi.org).  Don't translate this.
				"doi" => dgettext($domain, "DOI"),
				"abstract" => dgettext($domain, "Abstract"),
				"note" => dgettext($domain, "Note"),
/// resources_citedResources Other resources citing this resource where '###' is the name of the user bibliography or 'WIKINDX Master Bibliography'
				"citedResources" => dgettext($domain, "Resources citing this ###"),
				"noUsers" => dgettext($domain, "There are no users in the database"),
/// resources_paragraph Paragraph and section in resources
				"paragraph" => dgettext($domain, "Paragraph"),
				"section" => dgettext($domain, "Section"),
/// resources_chapter A numeric chapter in a book
				"chapter" => dgettext($domain, "Chapter"),
				"comment" => dgettext($domain, "Comments"),
				"fileName" => dgettext($domain, "Filename"),
/// resources_primaryAttachment User can specify one attachment from the resources attachments to be displayed first
				"primaryAttachment" => dgettext($domain, "Display this attachment first"),
/// resources_attachmentDescription Description of the attachment
				"attachmentDescription" => dgettext($domain, "Attachment description"),
/// resources_attachmentReadMe Open the attachment description.  Keep it short
				"attachmentReadMe" => dgettext($domain, "(Desc.)"),
				"fileAttachments" => dgettext($domain, "There are three ways to attach files to resources."),
/// resources_fileAttach For file attachments/uploading to resources
				"fileAttach" => dgettext($domain, "Attach a single file to this resource"),
/// resources_fileAttachMultiple For file attachments/uploading to resources
				"fileAttachMultiple" => dgettext($domain, "Attach one or more files to this resource"),
/// resources_fileAttachDragAndDrop For file attachments/uploading to resources
				"fileAttachDragAndDrop" => dgettext($domain, "Drag and drop files here to attach them to this resource"),
/// resources_fileAttachFallback Displayed when drag and drop is not avilable in the browser
				"fileAttachFallback" => dgettext($domain, "Drag and drop is not available in this browser"),
/// resources_attachEmbargo Store the attachment but keep it from public view for a period of time
				"attachEmbargo" => dgettext($domain, "Embargo this attachment until the specified date:"),
				"attachEmbargoMultiple" => dgettext($domain, "Embargo these attachments until the specified date:"),
				"embargoed" => dgettext($domain, "(One or more attachments embargoed)"),
				"fileName" => dgettext($domain, "Filename"),
				"deleteConfirmAttach" => dgettext($domain, "Delete attachment(s) ###"),
				"deleteConfirmKeywords" => dgettext($domain, "Delete keyword(s) ###"),
				"deleteConfirmKeywordGroups" => dgettext($domain, "Delete keyword group(s) ###"),
				"currentAttachments" => dgettext($domain, "Current attachments"),
/// resources_url For adding URLs to resources
				"url" => dgettext($domain, "URL"),
				"urlLabel" => dgettext($domain, "URL label"),
/// resources_primaryUrl User can specify one URL from the resources URLs to be displayed first
				"primaryUrl" => dgettext($domain, "Display this URL first"),
				"deleteConfirmUrl" => dgettext($domain, "Delete URL(s) ###"),
				"usertags" => dgettext($domain, "User tags"),
/// resources_warningOrganize1 A warning shown when organizing a list of resources (search, select etc.) into categories, keywords etc.
				"warningOrganize1" => dgettext($domain, "If you select nothing for item labels that are displayed, opt to replace the existing set and then save, you are removing those item labels from all selected resources. A resource must always belong to a category; if you attempt to save with categories removed, the resources will be placed in category 'General'. Uncheck the checkboxes to disable editing for that group."),
				"warningOrganize2" => dgettext($domain, "You can only edit categories, keywords, and languages for those resources you own or where the superadmin has given global permissions."),
/// resources_availableKeywords Existing keywords stored in the database
				"availableKeywords" => dgettext($domain, "Available keywords"),
/// resources_availableUserTags Existing user tags stored in the database
				"availableUserTags" => dgettext($domain, "Available user tags"),
/// resources_translatedFrom  For details of original publication of a translated book
				"translatedFrom" => dgettext($domain, "Original details of a translated book"),
				"transPublicationYear" => dgettext($domain, "Original publication year"),
				"transTitle" => dgettext($domain, "Original title"),
				"transSubtitle" => dgettext($domain, "Original subtitle"),
				"originalTitle" => dgettext($domain, "Original title"),
				"miscellaneous" => dgettext($domain, "Miscellaneous details"),
				"commonDetails" => dgettext($domain, "Common details"),
				"customFields" => dgettext($domain, "Custom fields"),
/// resources_addNewResourceToBib When adding a new resource, user can add the resource to their user bibliographies
				"addNewResourceToBib" => dgettext($domain, "Add to user bibliographies"),
				"series" => dgettext($domain, "Series"),
				"seriesTitle" => dgettext($domain, "Series title"),
				"seriesNumber" => dgettext($domain, "Series number"),
				"volume" => dgettext($domain, "Volume"),
				"issue" => dgettext($domain, "Issue"),
				"ISSN" => dgettext($domain, "ISSN"),
				"ID"	=>	dgettext($domain, "ID"),
				"numberOfVolumes" => dgettext($domain, "Number of volumes in set"),
				"bookVolumeNumber" => dgettext($domain, "This volume number"),
				"volumeYear" => dgettext($domain, "Volume set publication year"),
				"numPages" => dgettext($domain, "Number of pages"),
				"reprintYear" => dgettext($domain, "Reprint year"),
				"revisionYear" => dgettext($domain, "Revision year"),
				"edition" => dgettext($domain, "Edition"),
				"pageStart" => dgettext($domain, "Page start"),
				"pageEnd" => dgettext($domain, "Page end"),
/// resources_peerReviewed Resource has been peer reviewed
				"peerReviewed" => dgettext($domain, "Peer reviewed"),
				"journalVolumeNumber" => dgettext($domain, "Journal volume number"),
				"journalIssueNumber" => dgettext($domain, "Journal issue number"),
				"journal" => dgettext($domain, "Journal"),
				"book" => dgettext($domain, "Book"),
				"newspaper" => dgettext($domain, "Newspaper"),
				"shortNewspaper" => dgettext($domain, "Short title"),
				"proceedings" => dgettext($domain, "Proceedings"),
				"encyclopedia" => dgettext($domain, "Encyclopedia"),
				"encyclopediaShort" => dgettext($domain, "Short title"),
				"section" => dgettext($domain, "Section"),
				"city" => dgettext($domain, "City"),
				"proceedingsVolumeNumber" => dgettext($domain, "Proceedings volume number"),
				"publicationDay" => dgettext($domain, "Publication day"),
				"publicationMonth" => dgettext($domain, "Publication month"),
/// resources_department Department reports and documentation were issued from
				"department" => dgettext($domain, "Department"),
				"thesis" => dgettext($domain, "Thesis/Dissertation"),
				"thesisType" => dgettext($domain, "Degree level"),
				"thesisLabel" => dgettext($domain, "Thesis label"),
				"label" => dgettext($domain, "Thesis label"),
				"awardYear" => dgettext($domain, "Year of award"),
				"thesisThesis" => dgettext($domain, "thesis"),
				"thesisDissertation" => dgettext($domain, "dissertation"),
				"institution" => dgettext($domain, "Institution"),
				"thesisYear" => dgettext($domain, "Year of awarding"),
/// resources_distributor Film distributor/studio
				"distributor" => dgettext($domain, "Distributor"),
				"country" => dgettext($domain, "Country"),
				"hours" => dgettext($domain, "Hours"),
				"minutes" => dgettext($domain, "Minutes"),
/// resources_channel TV or radio channel
				"channel" => dgettext($domain, "Broadcast channel"),
				"channelLocation" => dgettext($domain, "Channel location"),
/// resources_version Computer program
				"version" => dgettext($domain, "Version"),
				"typeOfSoftware" => dgettext($domain, "Type of Software"),
/// resources_medium Medium = 'oil on canvas', 'marble sculpture', 'multimedia show' etc. usually for art works
				"medium" => dgettext($domain, "Medium"),
/// resources_reporter For legal cases
				"reporter" => dgettext($domain, "Reporter"),
				"caseYear" => dgettext($domain, "Year"),
				"statuteYear" => dgettext($domain, "Year"),
				"hearingYear" => dgettext($domain, "Year"),
				"court" => dgettext($domain, "Court"),
				"reporterVolume" => dgettext($domain, "Reporter Volume"),
/// resources_bill Parliamentary bills/laws
				"bill" => dgettext($domain, "Bill"),
				"code" => dgettext($domain, "Code"),
				"codeEditionYear" => dgettext($domain, "Year"),
				"codeSection" => dgettext($domain, "Section"),
				"legislativeBody" => dgettext($domain, "Legislative Body"),
				"legislativeLocation" => dgettext($domain, "Location"),
/// resources_session Parliamentary session
				"session" => dgettext($domain, "Session"),
				"codeVolume" => dgettext($domain, "Code Volume"),
				"billNumber" => dgettext($domain, "Bill Number"),
				"sessionYear" => dgettext($domain, "Session Year"),
				"conference" => dgettext($domain, "Conference"),
				"conferenceOrganiser" => dgettext($domain, "Conference organizer"),
				"conferenceLocation" => dgettext($domain, "Conference location"),
				"conferenceYear" => dgettext($domain, "Conference year"),
				"organiser" => dgettext($domain, "Organizer"),
/// resources_ruleType Legal Rulings/Regulations
				"ruleType" => dgettext($domain, "Type of Ruling"),
				"ruleNumber" => dgettext($domain, "Rule Number"),
/// resources_issueNumber report issue number
				"issueNumber" => dgettext($domain, "Issue Number"),
				"typeOfReport" => dgettext($domain, "Type of Report"),
				"reportYear" => dgettext($domain, "Year"),
/// resources_committee Government/legal hearings
				"committee" => dgettext($domain, "Committee"),
				"documentNumber" => dgettext($domain, "Document Number"),
				"magazine" => dgettext($domain, "Magazine"),
				"hearing" => dgettext($domain, "Hearing"),
/// resources_typeOfArticle Type of article (interview, review, advert etc.) in magazines
				"typeOfArticle" => dgettext($domain, "Type of Article"),
/// resources_typeOfManuscript Manuscripts
				"typeOfManuscript" => dgettext($domain, "Type of Manuscript"),
				"manuscriptNumber" => dgettext($domain, "Manuscript Number"),
/// resources_map Maps
				"map" => dgettext($domain, "Map"),
				"typeOfMap" => dgettext($domain, "Type of Map"),
/// resources_nameOfFile Charts/images
				"nameOfFile" => dgettext($domain, "Name of File"),
/// resources_imageProgram Software used to display image
				"imageProgram" => dgettext($domain, "Image Program"),
				"imageSize" => dgettext($domain, "Image Size"),
				"imageType" => dgettext($domain, "Image Type"),
				"number" => dgettext($domain, "Number"),
/// resources_publicLawNumber Legal Statutes
				"publicLawNumber" => dgettext($domain, "Public Law Number"),
				"codeNumber" => dgettext($domain, "Code Number"),
/// resources_publishedSource Patents
				"publishedSource" => dgettext($domain, "Published Source"),
				"patentVersionNumber" => dgettext($domain, "Patent Version Number"),
				"patentNumber" => dgettext($domain, "Patent Number"),
				"applicationNumber" => dgettext($domain, "Application Number"),
				"patentType" => dgettext($domain, "Patent Type"),
				"intPatentNumber" => dgettext($domain, "International Patent Number"),
				"intPatentClassification" => dgettext($domain, "International Patent Classification"),
				"intPatentTitle" => dgettext($domain, "International Patent Title"),
				"legalStatus" => dgettext($domain, "Legal Status"),
/// resources_assignee Who is the patent assigned to?
				"assignee" => dgettext($domain, "Assignee"),
				"assigneeLocation" => dgettext($domain, "Assignee Location"),
/// resources_typeOfCommunication Personal Communication
				"typeOfCommunication" => dgettext($domain, "Type of Communication"),
/// resources_typeOfWork Unpublished work
				"typeOfWork" => dgettext($domain, "Type of Work"),
/// resources_recordLabel Music
				"recordLabel" => dgettext($domain, "Record Label"),
				"album" => dgettext($domain, "Album"),
/// resources_duplicate Add a new resource with the same title as an existing one (used when adding/editing a resource)
				"duplicate" => dgettext($domain, "Allow the duplicate resource"),
				"page" => dgettext($domain, "Page"),
				"basketAdd" => dgettext($domain, "Add to basket"),
				"basketRemove" => dgettext($domain, "Remove from basket"),
				"putInQuarantine" => dgettext($domain, "Quarantine resource"),
				"removeFromQuarantine" => dgettext($domain, "Approve resource"),
				"replaceExisting" => dgettext($domain, "Replace existing set"),
			),
/// hint_ Hint messages.  Helpful tips usually displayed in smaller text
		    "hint" => array(
				"hint" => dgettext($domain, "Hint"),
				"homeBib" => dgettext($domain, "If browsing a user bibliography, use it also for the front page (which otherwise uses the master bibliography)."),
				"addedBy" => dgettext($domain, "Added by: ###"),
				"editedBy" => dgettext($domain, "Last edited by: ###"),
				"stringLimit" => dgettext($domain, "-1 is unlimited. Default is 40"),
				"lastChanges" => dgettext($domain, "-1 is unlimited. Default is 10"),
				"lastChangesDayLimit" => dgettext($domain, "-1 is unlimited. Default is 10"),
				"pagingLimit" => dgettext($domain, "-1 is unlimited. Default is 20"),
				"pagingMaxLinks" => dgettext($domain, "Minimum is 4. Default is 11"),
				"pagingTagCloud" => dgettext($domain, "-1 is unlimited. Default is 100"),
				"title" => dgettext($domain, "Default is 'WIKINDX'"),
				"contactEmail" => dgettext($domain, "Contact email displayed on the front page"),
/// hint_pagingInfo Paging system e.g. 'Displaying 24-32 ...'
				"pagingInfo" => dgettext($domain, "Displaying ### "),
/// hint_pagingInfoOf Follows hint_pagingInfo: '... of 345'
				"pagingInfoOf" => dgettext($domain, "of ###"),
				"mailServerRequired" => dgettext($domain, "Requires a mail server to be enabled and configured"),
				"registerEmail" => dgettext($domain, "An email will be sent with further instructions"),
				"multiples" => dgettext($domain, "Multiples can be chosen"),
				"imgWidthLimit" => dgettext($domain, "Default is 400"),
				"imgHeightLimit" => dgettext($domain, "Default is 400"),
				"deleteSeconds" => dgettext($domain, "Default is 3600"),
				"timezone" => dgettext($domain, "The default timezone. This should be set to the timezone of your server installation"),
				"imagesAllow" => dgettext($domain, "Images can be inserted into abstracts, notes, and other metadata as well as the word processor if that plugin is being used. These images can be a link to an external URL or an image selected from a directory that registered users can upload to: the data/images/ directory at the top level of wikindx/. Checking the checkbox will allow the uploading of images"),
				"imagesMaxSize" => dgettext($domain, "Maximum filesize of uploaded images in megabytes (an integer). Default is 5"),
				"attachmentsMaxSize" => dgettext($domain, "Maximum filesize of uploaded attachments in megabytes (an integer). Default is 5"),
				"tagCloud" => dgettext($domain, "When browsing tag clouds (for example, Browse Keywords in the Resources menu), you can indicate the frequency of the data by a range of colours and font sizes from low to high. Font sizes are given in a scaling factor of the base font (e.g. 75% = 0.75em and 200% = 2em). NB the background colour and the way the hyperlinks are displayed are given in the template's CSS file as the classes 'browseLink' and 'browseParagraph'."),
				"deactivateResourceTypes" => dgettext($domain, "The importing and exporting of bibliographies and entry of new resources will ignore resource types that are deactivated. Deactivated resource types that are already in the database will not have their display affected. At least one type must remain active. Multiples can be chosen"),
				"LdapUse" => dgettext($domain, "This option enables authentication from an LDAP / Active Directory server of all users. As an exception to guarantee access in the event of a misconfiguration or an offline server, if the LDAP authentication of the Super Admin account fails, a second will be attempted with the built-in method."),
				"LdapServer" => dgettext($domain, "Server name or IP without mention of protocol. For example: 'server152.university.lan'"),
				"LdapPort" => dgettext($domain, "LDAP standard ports are 389 (no encryption) and 636 (ssl)"),
				"LdapServerEncryption" => dgettext($domain, "Encryption options (strongest to weakest):\n - ssl: use LDAPS protocol (no certificate verification).\n - starttls: uses the LDAP protocol and when the connection is established starts TLS (no certificate verification).\n - none: use LDAP protocol without encryption."),
				"LdapServerBindType" => dgettext($domain, "Binding/Server connection types:\n - anonymous: does not use credentials.\n - binduser: use credentials of a user reserved for this with read access to the server, the login / password of which can be entered in this screen.\n - user: use the credentials of the user trying to connect to Wikindx."),
				"LdapServerBindLogin" => dgettext($domain, "Login of the binding user"),
				"LdapServerBindPassword" => dgettext($domain, "Password of the binding user"),
				"LdapServerBindDomain" => dgettext($domain, "Domain added to the login of the user used for the binding. If the domain is missing, the login is used alone. For example: 'cahors.edu.fr', 'CAHORS'"),
				"LdapServerBindDomainFormat" => dgettext($domain, "Format options:\n - none: the domain is not added to the login\n - sam: <domain>\\\\<login>\n - upn: <login>@<domain>"),
				"LdapSearchMethod" => dgettext($domain, "Search methods:\n - list:  searches for the user in the specified OUs\n - tree: searches for the user in the specified OUs and their sub-OUs"),
				"LdapSearchOperator" => dgettext($domain, "Search operator:\n - and:  searches for the user in the specified OUs AND groups\n - or: searches for the user in the specified OUs OR groups"),
				"LdapUserAttributLogin" => dgettext($domain, "Attribute where is stored the user login to check. This depends on your directory configuration. The most frequent attributes are proposed."),
				"LdapUserOu" => dgettext($domain, "One or more OU separated by ';' where authenticated users DNs are stored.\n Note: if a component of the OU contains special characters for the ldap protocol, they must be escaped by a \\\\ character preceding them. The special characters are: leading or trailing spaces and \\\\#+<>,;\"=.\n For example:\n DC=cahors,DC=edu,DC=fr\n OU=Physics,DC=cahors,DC=edu,DC=fr\n OU=Chemistry\\\\+Physics,DC=cahors,DC=edu,DC=fr"),
				"LdapGroupDn" => dgettext($domain, "One or more group DNs to which the users to be authenticated belong, separated by ';'.\n Note: if a component of the DN contains special characters for the ldap protocol, they must be escaped by a \\\\ character preceding them. The special characters are: leading or trailing spaces and \\\\#+<>,;\"=.\n For example:\n CN=teachers,OU=interns,DC=cahors,DC=edu,DC=fr\n CN=students,OU=interns,DC=cahors,DC=edu,DC=fr\n CN=lecture\\\\+amphitheater,DC=cahors,DC=edu,DC=fr"),
				"LdapUserCreate" => dgettext($domain, "This option activates the creation of the user account if it does not already exist from the attributes of the ldap directory, during its first successful authentication. The following times the attributes are not synchronized. The copied attributes (in brackets) are: login (the one configured), full name (displayName), email (mail)."),
				"ldapExtDisabled" => dgettext($domain, "The PHP LDAP extension is disabled or not installed. As long as it is not available the LDAP auth mode will be ignored."),
				"ldapTest" => dgettext($domain, "Enter a valid LDAP username/password here to test the LDAP configuration when you click on Proceed."),
				"authGate" => dgettext($domain, "If checked, user must click on OK after login in order to proceed. Used for situations such as notification about privacy policies such as that mandated by the EU's GDPR. Once the user has clicked on OK, they are not required to do so again until the authentication gate flag is reset."),
				"passwordSize" => dgettext($domain, "Minimum of 6 characters."),
				"passwordStrength" => dgettext($domain, "Weak: Password must  be a mix of UPPER/lowercase. Spaces are not allowed. Medium: Password must be a mix of UPPER/lowercase and numbers. Spaces are not allowed. Strong: Password must be a mix of UPPER/lowercase, numbers, and non-alphanumeric characters ($ @ # ! % * ? & €). Spaces are not allowed."),
				"mailFrom" => dgettext($domain, "Email address for the 'From: ' field. Default is the WIKINDX title. However, some email hosts will not accept email without a valid email address here"),
				"mailReplyTo" => dgettext($domain, "Email address for the 'Reply-to: ' field. Default is 'noreply@noreply.org'"),
				"mailReturnPath" => dgettext($domain, "If required, enter the 5th mail field here (the return path – possibly something like '-f postmaster@domain.dom' including single-quotes). Otherwise, or if unsure, leave blank. Setting this field will override the mail.force_extra_parameters parameter in php.ini"),
				"mailSmPath" => dgettext($domain, "The default sendmail path is '/usr/sbin/sendmail'"),
				"mailSmtpServer" => dgettext($domain, "e.g. smtp.mydomain.org. Default is 'localhost'"),
				"mailSmtpPersist" => dgettext($domain, "Check to keep the SMTP port open for multiple calls"),
				"mailSmtpPort" => dgettext($domain, "Default is 25"),
				"mailSmtpAuth" => dgettext($domain, "If checked, set the username and password"),
				"mailTest" => dgettext($domain, "Save the configuration by clicking on 'Proceed'. After that, enter a valid email address here to test the mail configuration when you click on 'Test'"),
				"maxPaste" => dgettext($domain, "To disable registered users from pasting BibTeX entries, set the value to 0. Default is 10"),
				"siteMapAllow" => dgettext($domain, "This is only required if you wish to run a Sitemap. Sitemaps are an easy way for webmasters to inform search engines about pages on their sites that are available for crawling. For details, see <a href='https://www.sitemaps.org/'>sitemaps.org</a>."),
				"browserTabID" => dgettext($domain, "Enable independent searching and listing across browser tabs/windows. This is experimental – see the help for further details."),
				"rssAllow" => dgettext($domain, "This is only required if you wish to run a RSS news feed for the latest additions to your WIKINDX. See README_RSS. If you do do not check this, WIKINDX will block RSS access and will not display any RSS icon in the Firefox location bar. If checked, to allow RSS feed users to then click on the RSS link and access WIKINDX, you must also enable read only access"),
				"rssDisplay" => dgettext($domain, "Uncheck to display only recently added resources or check to display recently added AND edited resources"),
				"rssLimit" => dgettext($domain, "Number of recent items to send to the feed. Default is 10"),
				"cmsAllow" => dgettext($domain, "This is only required if you wish to use WIKINDX to print individual resources or lists directly to a Content Management System. See README_CMS. If checked, any user, regardless of the general access settings may use the WIKINDX CMS hooks as shown in README_CMS to display WIKINDX content in their CMS. If unchecked, WIKINDX will block access"),
				"cmsSql" => dgettext($domain, "If checked (and CMS access is enabled), users can send a SQL string to the database for more complex queries. If this is the case, and users (in their WIKINDX preferences) have set CMS to display, they will have a 'cms' link provided for resource lists that will give them a base64-encoded text string to use in their CMS as they wish. If you enable this option, WIKINDX will access the WIKINDX database using the username::password combination supplied here. It is your responsibility to ensure this user _only_ has SELECT privileges on the database otherwise you are at risk of users sending INSERT, UPDATE, DROP, EXECUTE etc. SQL commands!"),
				"gsAllow" => dgettext($domain, "Allow Google Scholar to index resources"),
				"gsAttachment" => dgettext($domain, "If checked, Google Scholar indexing will only occur where the resource has an attachment (if multiple attachments, only the primary is used). Additionally, to enable this, 'Allow only registered users to view file attachments' must be checked"),
				"restrictUserId" => dgettext($domain, "Stop this write-enabled user changing login details. Typically this is used for a guest/test user (as on the WIKINDX testdrive database)"),
				"forceSmartyCompile" => dgettext($domain, "If checked, Smarty templates are re-compiled for each web page load"),
				"isTrunk" => dgettext($domain, "Activates experimental features of the trunk version, development tools, and changes the link of the update server to use the components of this version in perpetual development. DO NOT ACTIVATE THIS OPTION IF YOU ARE NOT A CORE DEVELOPER. If you need to debug your installation, you will find other suitable options in this screen."),
				"displayStatistics" => dgettext($domain, "Display statistics to read-only users"),
				"displayUserStatistics" => dgettext($domain, "If displaying statistics to read-only users, also display user statistics"),
/// hint_initials For initials input in creator names (keep it short)
				"initials" => dgettext($domain, "e.g. M.N. or Th.V."),
/// hint_maturityIndex For setting the maturity index for a resource, the range of allowable input is from 0 (inc. decimal points) to 10.
				"maturityIndex" => dgettext($domain, "0.0 to 10"),
				"keywordList" => dgettext($domain, "comma-separated list"),
				"categories" => dgettext($domain, "Each resource must belong to at least one category"),
				"keywords" => dgettext($domain, "Select keywords from the list and/or enter them as a comma-separated list"),
				"keywordsAlt" => dgettext($domain, "Enter keywords as a comma-separated list"),
				"userTags" => dgettext($domain, "Select user tags from the list and/or enter them as a comma-separated list"),
				"keywordsUserTags" => dgettext($domain, "Select keywords and user tags from the lists and/or enter them as comma-separated lists"),
/// hint_availableKeywords When editing the keywords after viewing a single resource, users can copy and paste keywords from one box to the other
				"availableKeywords" => dgettext($domain, "Copy and paste from this list to the first list"),
				"capitals" => dgettext($domain, "Braces e.g. {BibTeX Specs.} maintain the case whatever the requirements of the bibliographic style"),
				"publicationYear" => dgettext($domain, "Year of original publication"),
				"revisionYear" => dgettext($domain, "Year of last substantial revision"),
				"url" => dgettext($domain, "http://..."),
				"doi" => dgettext($domain, "e.g. 10.1234/56789"),
				"arabicNumeral1" => dgettext($domain, "Arabic numeral"),
				"arabicNumeral2" => dgettext($domain, "All numbers should be Arabic numerals."),
/// hint_dateAccessed Date of accessing a web-based resource
				"dateAccessed" => dgettext($domain, "Date accessed"),
				"thesisAbstract" => dgettext($domain, "If the thesis has had an abstract of it published in a journal, enter those details here"),
				"thesisType" => dgettext($domain, "e.g. PhD, masters..."),
				"thesisLabel" => dgettext($domain, "e.g. dissertation, thesis..."),
/// hint_runningTime length of audiovisual material (film etc.)
				"runningTime" => dgettext($domain, "Running time"),
				"broadcastDate" => dgettext($domain, "Broadcast Date"),
/// hint_dateDecided Date legal case decided
				"dateDecided" => dgettext($domain, "Date Case Decided"),
				"conferenceDate" => dgettext($domain, "Conference date"),
				"hearingDate" => dgettext($domain, "Date of Hearing"),
				"dateEnacted" => dgettext($domain, "Date Enacted"),
				"issueDate" => dgettext($domain, "Issuing Date"),
				"collection" => dgettext($domain, "If adding a resource to an existing collection or conference, after selecting the resource type, select the collection or conference from the select box first to automatically fill in other fields"),
				"quote" => dgettext($domain, "To allow for free-form quoting such as: it is claimed that WIKINDX is 'amongst the best' resources available, you are expected to distinguish the actual quote from surrounding text yourself."),
				"emailFriendAddress" => dgettext($domain, "Separate multiple addresses with commas or semicolons"),
				"noSort" => dgettext($domain, "When ordering resources by title, ignore the following list of case-insensitive words if they are the first word of the title. Comma-separated list"),
				"searchFilter" => dgettext($domain, "When searching resources or metadata, ignore the following list of case-insensitive words if they are not part of an exact phrase. Comma-separated list"),
/// hint_wordLogic Optional control words and formatting to use with search strings.  Don't change uppercase.
				"wordLogic" => dgettext($domain, "You can use combinations of: AND, OR, NOT and ''exact phrase'' as well as the wildcards '?' and '*'."),
/// hint_deleteConfirmBib When deleting a user bibliography, assurance that doing so does not delete any resources
				"deleteConfirmBib" => dgettext($domain, "(this does not delete resources)"),
				"password1" => dgettext($domain, "Password must contain at least ### characters and be a mix of UPPER/lowercase. Spaces are not allowed."),
				"password2" => dgettext($domain, "Password must contain at least ### characters and be a mix of UPPER/lowercase and numbers. Spaces are not allowed."),
				"password3" => dgettext($domain, "Password must contain at least ### characters and be a mix of UPPER/lowercase, numbers, and non-alphanumeric characters ($ @ # ! % * ? & €). Spaces are not allowed."),
				"password4"	=> dgettext($domain, "If you are logged in, the password fields will be empty for security reasons – your password is stored but, if you click on the submit button, you must fill in the password fields again."),
				"hashFile" => dgettext($domain, "The hash file is optional – use it to check the integrity of the uploaded package."),
				"glossary" => dgettext($domain, "The glossary appears when hovering over a keyword."),
/// hint_storeRawBibtex Do not translate '@string'
				"storeRawBibtex" => dgettext($domain, "You may store BibTeX fields that WIKINDX does not use so that any resources later exported to BibTeX can include this original unchanged data.  Doing this, also stores the BibTeX key and any @string strings that are in the imported BibTeX file."),
				"replaceExisting" => dgettext($domain, "When adding items, you may either append the selections above to an existing set for each resource or replace the existing set with the selections."),
				"tagImport" => dgettext($domain, "Tag this import so you can do a mass select or delete later."),
				"splitImport" => dgettext($domain, "Split the title and subtitle in the source bibliography on the first occurrence of the chosen character(s)."),
				"keywordImport" => dgettext($domain, "Keywords are separated in the source bibliography by the chosen character(s)."),
				"urlPrefix" => dgettext($domain, "A default URL prefix displayed when adding URLs to resources."),
				"impressum" => dgettext($domain, "Append an impressum/legal notice to the footer of each page."),
			),
/// menu_ Menu subsystem.  The trick here is to use short terms that don't cause problems with overflowing the CSS drop-down boxes - some browsers may happily handle this, others won't. Up to 15-16 characters (depending on character width) is a good guide - but check! NB!!!!!  For this array, the values should be unique where the keys form part of the same menu item.  For example, in the File menu, the 'file' key and the 'show' key should not have the same value of, for example, 'Files'.
		    "menu" => array(
/// menu_home Wikindx menu starts here
				"home" => dgettext($domain, "Home"),
				"importSub" => dgettext($domain, "Import..."),
				"news" => dgettext($domain, "News"),
				"prefs" => dgettext($domain, "Preferences"),
				"register" => dgettext($domain, "Register"),
				"myWikindx" => dgettext($domain, "My Wikindx"),
				"bibs" => dgettext($domain, "Bibliographies"),
				"userLogon" => dgettext($domain, "User Logon"),
				"logout" => dgettext($domain, "Logout"),
				"readOnly" => dgettext($domain, "Read Only"),
				"statistics" => dgettext($domain, "Statistics"),
				"statisticsSub" => dgettext($domain, "Statistics..."),
				"statisticsTotals" => dgettext($domain, "Totals"),
				"statisticsUsers" => dgettext($domain, "Users"),
				"statisticsKeywords" => dgettext($domain, "Keywords"),
				"statisticsYears" => dgettext($domain, "Publication years"),
				"statisticsAllCreators" => dgettext($domain, "All creators"),
				"statisticsMainCreators" => dgettext($domain, "Main creators"), // i.e. authors not editors
				"statisticsPublishers" => dgettext($domain, "Publishers"),
				"statisticsCollections" => dgettext($domain, "Collections"),
				"toggleHelpOn" => dgettext($domain, "Turn Help on"),
				"toggleHelpOff" => dgettext($domain, "Turn Help off"),
				"about" => dgettext($domain, "About Wikindx"),
/// menu_res Resources menu starts here
				"res" => dgettext($domain, "Resources"), // top menu item
				"new" => dgettext($domain, "New"),
				"files" => dgettext($domain, "List Exported Files"),
/// menu_bookmarkSub Bookmarks submenu
				"bookmarkSub" => dgettext($domain, "Bookmarks..."),
				"bookmarkAdd" => dgettext($domain, "Add bookmark"),
				"bookmarkDelete" => dgettext($domain, "Delete bookmarks"),
/// menu_editSub edit submenu
				"editSub" => dgettext($domain, "Edit..."),
				"editSubCollection" => dgettext($domain, "Collections..."),
				"creator" => dgettext($domain, "Creator"),
				"collection" => dgettext($domain, "Collection"),
				"publisher" => dgettext($domain, "Publisher"),
				"keyword" => dgettext($domain, "Keyword"),
				"keywordGroup" => dgettext($domain, "Keyword Groups"),
				"lastMulti" => dgettext($domain, "Last Multi View"),
				"lastSolo" => dgettext($domain, "Last Solo View"),
				"randomResource" => dgettext($domain, "Random Resource"),
				"basketSub" => dgettext($domain, "Basket..."),
/// menu_basketView Resource basket is a temporary collection of resources while the user is logged on
				"basketView" => dgettext($domain, "View Basket"),
				"basketDelete" => dgettext($domain, "Delete Basket"),
/// menu_pasteBibtex Ordinary user can paste a BibTeX file into a textarea for importing
				"pasteBibtex" => dgettext($domain, "BibTeX (Copy/Paste)"),
				"importBibtex" => dgettext($domain, "BibTeX (.bib file)"),
/// menu_search Search menu starts here
				"advancedSearch" => dgettext($domain, "Advanced Search"),
				"search" => dgettext($domain, "Search"),
				"quickSearch" => dgettext($domain, "Quick Search"),
				"selectResource" => dgettext($domain, "Select Resources"),
/// menu_listSub Quick list submenu
				"listSub" => dgettext($domain, "Quick List All..."),
/// menu_listCreator 'asc.' and 'desc.' mean 'ascending' and 'descending' and refers to the list ordering
				"listCreator" => dgettext($domain, "Creator asc."),
				"listTitle" => dgettext($domain, "Title asc."),
				"listPublisher" => dgettext($domain, "Publisher asc."),
				"listYear" => dgettext($domain, "Year desc."),
				"listTimestamp" => dgettext($domain, "Timestamp desc."),
				"listViews" => dgettext($domain, "Views desc."),
				"listDownloads" => dgettext($domain, "Downloads desc."),
				"listPopularity" => dgettext($domain, "Popularity desc."),
				"listMaturity" => dgettext($domain, "Maturity desc."),
				"searchResource" => dgettext($domain, "Search Resources"),
				"searchMeta" => dgettext($domain, "Search Metadata"),
				"selectMeta" => dgettext($domain, "Select Metadata"),
/// menu_browseSub browse submenu
				"browseSub" => dgettext($domain, "Browse..."),
				"browseSubCollection" => dgettext($domain, "Collections..."),
				"browseSubPublisher" => dgettext($domain, "Publishers..."),
				"browseCreator" => dgettext($domain, "Creators"),
				"browseCited" => dgettext($domain, "Cited"),
				"browseKeyword" => dgettext($domain, "Keywords"),
				"browseKeywordGroup" => dgettext($domain, "Keyword Groups"),
				"browseKeywordSub" => dgettext($domain, "Keywords..."),
				"browseKeywordGroupSub" => dgettext($domain, "Keyword Groups..."),
				"browseKeywordAll" => dgettext($domain, "All"),
				"browseKeywordQuotes" => dgettext($domain, "Quotes"),
				"browseKeywordParaphrases" => dgettext($domain, "Paraphrases"),
				"browseKeywordMusings" => dgettext($domain, "Musings"),
				"browseKeywordIdeas" => dgettext($domain, "Ideas"),
				"browseKeywordNotIdeas" => dgettext($domain, "Not Ideas"),
				"browseCollection" => dgettext($domain, "Collections"),
				"browseCategory" => dgettext($domain, "Categories"),
				"browseSubcategory" => dgettext($domain, "Subcategories"),
				"browseLanguage" => dgettext($domain, "Languages"),
				"browsePublisher" => dgettext($domain, "Publishers"),
				"browseType" => dgettext($domain, "Types"),
				"browseYear" => dgettext($domain, "Years"),
				"browseBibliography" => dgettext($domain, "Bibliographies"),
				"browseUserTags" => dgettext($domain, "User Tags"),
				"browseUser" => dgettext($domain, "System Users"),
				"browseDept" => dgettext($domain, "Departments"),
				"browseInst" => dgettext($domain, "Institutions"),
/// menu_categoryTree A category tree is a list of browsable categories with their associated keywords
				"categoryTree" => dgettext($domain, "Category Tree"),
/// menu_text Metadata menu starts here -- Metadata are ideas, quotes, paraphrases and musings
				"text" => dgettext($domain, "Metadata"),
				"addIdea" => dgettext($domain, "Add Idea"),
				"lastIdea" => dgettext($domain, "Last Idea Thread"),
				"listIdeas" => dgettext($domain, "List Ideas"),
				"lastIdeaSearch" => dgettext($domain, "Last Idea Search"),
/// menu_randomSub random submenu
				"randomSub" => dgettext($domain, "Random..."),
				"randomParaphrases" => dgettext($domain, "Paraphrase"),
				"randomQuotes" => dgettext($domain, "Quote"),
				"randomMusings" => dgettext($domain, "Musing"),
				"randomIdeas" => dgettext($domain, "Idea"),
/// menu_lastMultiMeta Last list of metadata
				"lastMultiMeta" => dgettext($domain, "Last Multi View"),
/// menu_plugin1 menu system for plugins (1 of 3)
				"plugin1" => dgettext($domain, "Plugins (1)"),
				"plugin2" => dgettext($domain, "Plugins (2)"),
				"plugin3" => dgettext($domain, "Plugins (3)"),
/// menu_admin Administrator menu starts here
				"admin" => dgettext($domain, "Admin"), // top menu item
				"conf" => dgettext($domain, "Configure"),
				"newsSub" => dgettext($domain, "News..."),
				"newsAdd" => dgettext($domain, "Add"),
				"newsDelete" => dgettext($domain, "Delete"),
				"newsEdit" => dgettext($domain, "Edit"),
				"importhelp" => dgettext($domain, "Import Help"),
				"confighelp" => dgettext($domain, "Config Help"),
				"categories" => dgettext($domain, "Categories"),
				"subcategories" => dgettext($domain, "Subcategories"),
				"language" => dgettext($domain, "Languages"),
				"images" => dgettext($domain, "Images"),
				"keywordSub" => dgettext($domain, "Keywords..."),
				"keywordEdit" => dgettext($domain, "Edit"),
				"keywordMerge" => dgettext($domain, "Merge"),
				"keywordDelete" => dgettext($domain, "Delete"),
				"creatorSub" => dgettext($domain, "Creators..."),
				"creatorEdit" => dgettext($domain, "Edit"),
				"creatorMerge" => dgettext($domain, "Merge"),
				"creatorGroup" => dgettext($domain, "Group"),
				"delete" => dgettext($domain, "Delete Resource"),
				"userSub" => dgettext($domain, "Users..."),
				"userAdd" => dgettext($domain, "Add"),
				"userEdit" => dgettext($domain, "Edit"),
				"userDelete" => dgettext($domain, "Delete"),
				"userBlock" => dgettext($domain, "Block/Unblock"),
				"userRegistration" => dgettext($domain, "Registrations"),
				"quarantine" => dgettext($domain, "Quarantine"),
				"components" => dgettext($domain, "Components"),
/// menu_customSub The admin can administer custom database fields for resources
				"custom" => dgettext($domain, "Custom Fields"),
				"creators" => dgettext($domain, "Creators"),
			),
/// menuReduced_ Headings for menu items when the user has reduced menus by 2 levels. Used only for menu items that have submenus.
			"menuReduced" => array(
				"Statistics" => dgettext($domain, "Statistics..."),
				"Edit" => dgettext($domain, "Edit..."),
				"QuickListAll" => dgettext($domain, "Quick list all..."),
				"Browse" => dgettext($domain, "Browse..."),
				"Random" => dgettext($domain, "Random..."),
				"Users" => dgettext($domain, "Users..."),
				"Keywords" => dgettext($domain, "Keywords..."),
				"Creators" => dgettext($domain, "Creators..."),
			),
/// misc_ Miscellaneous items that don't fit anywhere else
		    "misc" => array(
				"file" => dgettext($domain, "file"),
/// misc_toLeft text for an arrow to shift items from a box on the right to the left
				"toLeft" => dgettext($domain, "Shift item from right to left"),
/// misc_toRight text for an arrow to shift items from a box on the left to the right
				"toRight" => dgettext($domain, "Shift item from left to right"),
/// misc_toBottom text for an arrow to shift items from a box on the top to the bottom
				"toBottom" => dgettext($domain, "Shift item from top to bottom"),
				"toTop" => dgettext($domain, "Shift item from bottom to top"),
				"add" => dgettext($domain, "add"),
				"remove" => dgettext($domain, "remove"),
				"edit" => dgettext($domain, "edit"),
				"delete" => dgettext($domain, "delete"),
				"bibtex" => dgettext($domain, "BibTeX"),
				"view" => dgettext($domain, "view"),
				"viewAttach" => dgettext($domain, "view"),
				"viewmeta" => dgettext($domain, "view"),
				"viewmetaAttach" => dgettext($domain, "view"),
				"download" => dgettext($domain, "download"),
				"quarantine" => dgettext($domain, "Quarantined"),
				"noResources" => dgettext($domain, "There are no resources in the database"),
				"noMetadata" => dgettext($domain, "There are no metadata in the database"),
				"noQuotes" => dgettext($domain, "There are no quotes in the database"),
				"noParaphrases" => dgettext($domain, "There are no paraphrases in the database"),
				"noMusings" => dgettext($domain, "There are no musings in the database"),
				"noIdeas" => dgettext($domain, "There are no ideas in the database"),
				"noResourcesBib" => dgettext($domain, "There are no resources in that user bibliography"),
				"noCategories" => dgettext($domain, "There are no categories in the database"),
				"noSubcategories" => dgettext($domain, "There are no subcategories in the database"),
				"noBibliographies" => dgettext($domain, "There are no populated user bibliographies in the database"),
				"noLanguages" => dgettext($domain, "There are no languages in the database"),
				"noUsertags" => dgettext($domain, "There are no resources with user tags in the database"),
				"noImages" => dgettext($domain, "There are no images available"),
				"usedImages" => dgettext($domain, "Used images"),
				"unusedImages" => dgettext($domain, "Unused images"),
/// misc_popIndex When viewing resources on the front page of the WIKINDX, display the popularity index.  This should be a short abbreviation
				"popIndex" => dgettext($domain, "Pop. ###%"),
/// misc_downIndex When viewing resources on the front page of the WIKINDX, display the download index.  This should be a short abbreviation
				"downIndex" => dgettext($domain, "Down. ###%"),
/// misc_matIndex When viewing resources on the front page of the WIKINDX, display the maturity index.  This should be a short abbreviation
				"matIndex" => dgettext($domain, "Mat. ###"),
/// misc_ignore In select boxes - when it is not necessary to choose an existing selection.  WIKINDX will skip over this one. Could be '---'
				"ignore" => dgettext($domain, "IGNORE"),
				"noCreators" => dgettext($domain, "There are no creators in the database"),
				"noGroupMasterCreators" => dgettext($domain, "There are no group master creators in the database"),
				"noCollections" => dgettext($domain, "There are no collections in the database"),
				"noPublishers" => dgettext($domain, "There are no publishers in the database"),
				"noKeywords" => dgettext($domain, "There are no keywords in the database"),
				"noKeywordGroups" => dgettext($domain, "There are no keyword groups in the database"),
				"tooFewKeywords" => dgettext($domain, "There must be at least two keywords in the database in order to create a keyword group"),
				"noMetaKeywords" => dgettext($domain, "There are no metadata keywords in the database"),
				"noKeywordGroups" => dgettext($domain, "There are no used keywords in available keyword groups"),
				"noCitations" => dgettext($domain, "There are no cited creators in the database"),
				"noUsers" => dgettext($domain, "There are no registered users with resources in the database"),
/// misc_categoryTreeKeywords When browsing the category tree, display keywords with resources in each category
				"categoryTreeKeywords" => dgettext($domain, "Resource keywords in this category:"),
/// misc_categoryTreeSubcategories When browsing the category tree, display subcategories with resources in each category
				"categoryTreeSubcategories" => dgettext($domain, "Resource subcategories in this category:"),
/// misc_set Used in SUCCESS.php when a user chooses a user bibliography to browse.  The message is "Successfully set Bibliography".
				"set" => dgettext($domain, "set"),
/// misc_keywordExists Advice on what to do when editing a keyword name and the new name already exists in the database.
				"keywordExists" => dgettext($domain, "If you proceed, this edited keyword will be deleted and all references in the database to it will be replaced by references to the pre-existing keyword."),
/// misc_collectionExists Advice on what to do when editing a collection name and the new name already exists in the database.
				"collectionExists" => dgettext($domain, "If you proceed, this edited collection will be deleted and all references in the database to it will be replaced by references to the pre-existing collection."),
/// misc_publisherExists Advice on what to do when editing a publisher name and the new name already exists in the database.
				"publisherExists" => dgettext($domain, "If you proceed, this edited publisher will be deleted and all references in the database to it will be replaced by references to the pre-existing publisher."),
/// misc_emailToFriend Email a single resource link to a friend.
				"emailToFriend" => dgettext($domain, "Email resource to friend"),
/// misc_bibtexKey When viewing a single resource, display the BibTeX citation key (such as 'aarseth.321')
				"bibtexKey" => dgettext($domain, "BibTeX citation key"),
/// misc_bookmarks User bookmarks
				"bookmark" => dgettext($domain, "You may store up to 20 bookmarks for returning to a single or multi-resource view."),
				"bookmarkDelete" => dgettext($domain, "As you already have 20 bookmarks, you must choose one to be replaced by this new addition"),
				"bookmarkDeleteInit" => dgettext($domain, "Select the bookmarks to be deleted"),
				"bookmarkName" => dgettext($domain, "Enter a short name for your bookmark"),
				"edited" => dgettext($domain, "edited"),
				"added" => dgettext($domain, "added"),
				"deleted" => dgettext($domain, "deleted"),
/// misc_confirmDelete Ask for confirmation deleting a large number of resources
				"confirmDelete" => dgettext($domain, "Delete ### resources?"),
				"confirmDeleteLanguage" => dgettext($domain, "Delete language(s)?"),
/// misc_keywordMerge Admins can merge multiple keywords into one keyword
				"keywordMerge" => dgettext($domain, "Select and merge multiple keywords into one keyword (the merged keywords will be deleted)"),
				"keywordMergeTarget" => dgettext($domain, "New or target keyword"),
/// misc_creatorMerge Admins can merge multiple creators into one creator
				"creatorMerge" => dgettext($domain, "Select and merge multiple creators into one creator (the merged creators will be deleted).  A selection in the target select box overrides any text input. At least two original creators must be selected."),
				"creatorMergeOriginal" => dgettext($domain, "Original creators"),
				"creatorMergeTarget" => dgettext($domain, "Target creator"),
				"creatorGroupMember" => dgettext($domain, "Group members"),
				"creatorGroupAvailable" => dgettext($domain, "Available creators"),
				"creatorGroupMaster" => dgettext($domain, "Group master"),
				"creatorOnlyMasters" => dgettext($domain, "Show only group masters"),
/// misc_tag Tag - when importing BibTeX resources, the administrator can give a label to each import which makes it possible to later delete in one go resources that were imported under this label.
				"tag" => dgettext($domain, "Delete resources by import tag"),
/// misc_emailToFriend Email a single resource link to a friend.
				"emailToFriend" => dgettext($domain, "Email resource to friend"),
				"emailFriendAddress" => dgettext($domain, "Email address"),
				"emailFriendSubject" => dgettext($domain, "Email subject"),
				"emailFriendText" => dgettext($domain, "Email text"),
/// misc_keywordImport1 When importing bibliographies from external sources, these give the user the option to specify whether keywords, in the source bibliography, are separated by commas or semicolons
				"keywordImport" => dgettext($domain, "Keyword separation"),
				"keywordImport1" => dgettext($domain, "Commas"),
				"keywordImport2" => dgettext($domain, "Semicolons"),
				"keywordImport3" => dgettext($domain, "Commas or Semicolons"),
				"keywordImport4" => dgettext($domain, "SPACES"),
				"keywordIgnore" => dgettext($domain, "Ignore keywords"),
/// misc_titleSubtitleSeparator When importing BibTeX and endnote bibliographies, split title and subtitle on specified characters
				"titleSubtitleSeparator" => dgettext($domain, "Title/subtitle split"),
				"titleSubtitleSeparator1" => dgettext($domain, "NO SPLIT"),
				"titleSubtitleSeparator2" => dgettext($domain, ": (colon)"),
				"titleSubtitleSeparator3" => dgettext($domain, "; (semicolon)"),
				"titleSubtitleSeparator4" => dgettext($domain, ". or ! or ?"),
				"titleSubtitleSeparator5" => dgettext($domain, "-- (dash dash)"),
				"titleSubtitleSeparator6" => dgettext($domain, "- (dash)"),
/// misc_mergeStored When importing a bibliography (e.g. BibTeX), the admin has the choice of storing fields that wikindx does not recognise. Later, when exporting a bibliography, the user will have the choice of merging these unrecognised fields into the wikindx export. Additionally, any stored citation keys from the original import can be used in preference to a WIKINDX-generated one.
				"mergeStored" => dgettext($domain, "If any fields that WIKINDX does not recognise are stored from an original import, merge these fields into this export:"),
				"useOriginalCitation" => dgettext($domain, "Use the citation keys from the original import (where available) in preference to WIKINDX generated keys:"),
/// misc_shortString When exporting to BibTeX.  Don't translate @STRING.
				"shortString" => dgettext($domain, "Use short titles for any @STRING values:"),
/// misc_bibExportQuotes When exporting to BibTeX, use either double quotes or braces to enclose field values. \"...\" must be given exactly as it is here
				"bibExportQuotes" => dgettext($domain, "Use double quotes \"...\" to enclose field values"),
				"bibExportBraces" => dgettext($domain, "Use braces {...} to enclose field values"),
				"bibExportCharacterSetUTF" => dgettext($domain, "Use UTF-8 character encoding"),
				"bibExportCharacterSetTex" => dgettext($domain, "Use ISO-8859-1 and TeX character encoding"),
				"bibExportKeywordSeparatorSemicolon" => dgettext($domain, "Use semicolons ';' to separate keywords"),
				"bibExportKeywordSeparatorComma" => dgettext($domain, "Use commas ',' to separate keywords"),
/// misc_exportMetadata1 User has option of exporting metadata to BibTeX when exporting a bibliography
				"exportMetadata1" => dgettext($domain, "Export metadata to BibTeX."),
				"exportMetadata2" => dgettext($domain, "Enter a unique field name for each metadata field you wish to export:"),
/// misc_customFieldMap When exporting a bibliography to BibTeX or endnote, if any custom fields exist in the SQL set, the user can map these to specified BibTeX or endnote custom fields
				"customFieldMap" => dgettext($domain, "Map WIKINDX custom fields to export fields."),
				"customFieldMap2" => dgettext($domain, "Enter a unique field name for each custom field you wish to export:"),
				"help" => dgettext($domain, "Help"),
				"pluginConfig" => dgettext($domain, "Plugin Config.php File"),
				"pluginConfigHelp" => dgettext($domain, "There are two variables common to all plugins: \$wikindxVersion and \$authorize. In-line plugins additionally have the \$container variable while menu plugins have the \$menus plugin. Normally, you should not change \$wikindxVersion and changing it might cause the plugin to stop working. \$authorize is one of: 0 (public readonly access); 1 (login required); or 2 (admin only). \$menus is an array with at least one of: 'wikindx'; 'res'; 'search'; 'metadata'; 'admin'; 'plugin1'; 'plugin2'; or 'plugin3' (if you wish the plugin to appear in several menus, list them in the array separated by commas). The 'admin' menu is only available when logged in as admin. The 'text' menu will only show if there are metadata (quotes etc.) and the three 'pluginX' menus only show if they are populated. \$container should be one of: 'inline1'; 'inline2'; 'inline3'; or 'inline4' and their placement depends on the display template."),
				"openReadme" => dgettext($domain, "Open README file"),
				"language" => dgettext($domain, "Resources can be labelled with a language when adding or editing resources"),
				"languageAdd" => dgettext($domain, "Add Language"),
				"languageEdit" => dgettext($domain, "Edit Language"),
				"languageDelete" => dgettext($domain, "Delete Language"),
				"newsAdd" => dgettext($domain, "Add News"),
				"newsEdit" => dgettext($domain, "Edit News"),
				"newsDelete" => dgettext($domain, "Delete News"),
				"keywordGroupDelete" => dgettext($domain, "Delete Keyword Group"),
				"closePopup" => dgettext($domain, "Close"),
				"attachmentCache1" => dgettext($domain, "Before proceeding, attachments need to be converted to text files and cached in order to speed up full-text searches. This process might take some time if you have a large number of attachments and/or large files so leave the script running until activity stops. Keep on eye on activity in the cache/attachments/ folder. WIKINDX tries to deal with max execution timeouts but if you find that caching is not proceeding, you might need to adjust PHP's max_execution_time. If you get a blank page, it means a timeout has occurred. If CURL is part of the PHP installation and you select this option, WIKINDX will attempt to convert files in multiple, simultaneous instances and, if a timeout occurs, the error message will be written to the cached file: you should either increase max_execution_time or turn off the use of CURL. In the latter case, conversion proceeds sequentially and slowly and, unless the file to be converted is far too large and WIKINDX does not have enough time to convert the one file, the converted files are guaranteed to be cached. The option of limiting the number of files cached at any one time is also provided. At any time, you can skip over caching of files until the next time you login or go to the front page."),
				"attachmentCache2" => dgettext($domain, "### attachments remain to be cached."),
				"attachmentCache3" => dgettext($domain, "### attachments cached thus far."),
/// misc_attachmentCache4 Don't translate 'CURL'
				"attachmentCache4" => dgettext($domain, "Use CURL"),
				"attachmentCache5" => dgettext($domain, "Attempt to only cache this number of files at a time: ###"),
				"attachmentCache6" => dgettext($domain, "Skip caching until the next login."),
				"fileAttachDeleteAll" => dgettext($domain, "Delete all attachments for this resource"),
				"uploadDisabled" => dgettext($domain, "The upload is disabled by the administrator."),
			),
/// collection_ Messages relating to collections and publisher types
		    "collection" => array(
				"chooseTypeBrowse" => dgettext($domain, "Choose the type of collection you wish to browse"),
				"book" => dgettext($domain, "Book"),
				"journal" => dgettext($domain, "Journal"),
				"web" => dgettext($domain, "Web Site"),
				"proceedings" => dgettext($domain, "Proceedings"),
				"newspaper" => dgettext($domain, "Newspaper"),
				"magazine" => dgettext($domain, "Magazine"),
				"thesis" => dgettext($domain, "Thesis Abstracts"),
				"music" => dgettext($domain, "Music Recording"),
				"manuscript" => dgettext($domain, "Manuscript"),
				"miscellaneous" => dgettext($domain, "Miscellaneous"),
				"institution" => dgettext($domain, "Institution"),
				"conference" => dgettext($domain, "Conference"),
				"legal" => dgettext($domain, "Legal"),
				"music" => dgettext($domain, "Music"),
				"distributor" => dgettext($domain, "Distributor"),
				"chart" => dgettext($domain, "Chart"),
				"patent" => dgettext($domain, "Patent"),
/// collection_all When browsing collections, the user may browse ALL collections or by collection type.  'ALL' is is displayed at the top of a select box so should not be many words
				"all" => dgettext($domain, "ALL"),
			),
/// list_ Plain listing of resources
		    "list" => array(
				"listBy" => dgettext($domain, "List all by"),
				"creator" => dgettext($domain, "First Creator"),
				"title" => dgettext($domain, "Title"),
				"publisher" => dgettext($domain, "Publisher"),
				"year" => dgettext($domain, "Publication Year"),
				"timestamp" => dgettext($domain, "Timestamp"),
				"order" => dgettext($domain, "Order by"),
				"views" => dgettext($domain, "Views Index"),
				"downloads" => dgettext($domain, "Downloads Index"),
				"popularity" => dgettext($domain, "Popularity Index"),
				"maturity" => dgettext($domain, "Maturity Index"),
				"ascending" => dgettext($domain, "Ascending"),
				"descending" => dgettext($domain, "Descending"),
				"addUserIdResource" => dgettext($domain, "Resource added by"),
				"editUserIdResource" => dgettext($domain, "Resource last edited by"),
			),
/// select_ Select resources by...
		    "select" => array(
				"selectBy" => dgettext($domain, "Select by ###"),
				"notInUserBib" => dgettext($domain, "NOT in bibliography"),
				"metadata" => dgettext($domain, "Metadata type"),
				"option" => dgettext($domain, "Display options"),
				"noAttachment" => dgettext($domain, "With no attachments"),
				"attachment" => dgettext($domain, "With at least one attachment"),
				"displayAttachment" => dgettext($domain, "Display only attachments"),
				"url" => dgettext($domain, "With at least one external URL"),
				"doi" => dgettext($domain, "With DOI"),
/// select_displayAttachmentZip 'Tar' is the UNIX function tar so should not be translated.  'Compress' is as in file compression.  This statement follows on from the previous one
				"displayAttachmentZip" => dgettext($domain, "Tar and compress attachments"),
				"displayPeerReviewed" => dgettext($domain, "If peer reviewed"),
				"addedBy" => dgettext($domain, "Resource added by"),
				"editedBy" => dgettext($domain, "Resource last edited by"),
/// select_field Database field such as 'title', 'abstract', 'quotes' etc.
				"field" => dgettext($domain, "Search on Fields"),
				"availableType" => dgettext($domain, "Types"),
				"type" => dgettext($domain, "Selected Types"),
				"availableKeyword" => dgettext($domain, "Keywords"),
				"keyword" => dgettext($domain, "Selected Keywords"),
				"availableUserGroup" => dgettext($domain, "User Groups"),
				"userGroup" => dgettext($domain, "Selected User Groups"),
				"availableMetaKeyword" => dgettext($domain, "Metadata Keywords"),
				"metaKeyword" => dgettext($domain, "Selected Metadata Keywords"),
				"availableUserTag" => dgettext($domain, "User Tags"),
				"userTag" => dgettext($domain, "Selected User Tags"),
				"availableCategory" => dgettext($domain, "Categories"),
				"category" => dgettext($domain, "Selected Categories"),
				"availableSubcategory" => dgettext($domain, "Subcategories"),
				"subcategory" => dgettext($domain, "Selected Subcategories"),
				"language" => dgettext($domain, "Selected Languages"),
				"availableLanguage" => dgettext($domain, "Languages"),
				"publisher" => dgettext($domain, "Selected Publishers"),
				"availablePublisher" => dgettext($domain, "Publishers"),
				"creator" => dgettext($domain, "Selected Creators"),
				"availableCreator" => dgettext($domain, "Creators"),
				"collection" => dgettext($domain, "Selected Collections"),
				"availableCollection" => dgettext($domain, "Collections"),
				"availableTag" => dgettext($domain, "Import Tags"),
				"tag" => dgettext($domain, "Selected tags"),
				"availableAddedBy" => dgettext($domain, "Added By"),
				"addedBy" => dgettext($domain, "Selected Users"),
				"availableEditedBy" => dgettext($domain, "Edited By"),
				"editedBy" => dgettext($domain, "Selected Users"),
				"noIdeas" => dgettext($domain, "No ideas found matching your search"),
			),
/// search_ Search the database
		    "search" => array(
				"method" => dgettext($domain, "Method"),
				"word" => dgettext($domain, "Search word(s)"),
/// search_partial Match part of a word or search term must equal whole word?
				"partial" => dgettext($domain, "Partial word match"),
				"exact" => dgettext($domain, "Exact phrase"),
/// search_type Type = book, journal article, thesis etc.
				"type" => dgettext($domain, "Type"),
				"language" => dgettext($domain, "Language"),
				"publicationYear" => dgettext($domain, "Publication Year"),
				"access" => dgettext($domain, "Number of Views"),
				"maturityIndex" => dgettext($domain, "Maturity Index"),
				"title" => dgettext($domain, "Title"),
				"note" => dgettext($domain, "Notes"),
				"abstract" => dgettext($domain, "Abstract"),
				"quote" => dgettext($domain, "Quote"),
				"paraphrase" => dgettext($domain, "Paraphrase"),
				"quoteComment" => dgettext($domain, "Quote comment"),
				"paraphraseComment" => dgettext($domain, "Paraphrase comment"),
				"musing" => dgettext($domain, "Musing"),
				"attachments" => dgettext($domain, "Attachments"),
				"idea" => dgettext($domain, "Idea"),
				"creator" => dgettext($domain, "Creator"),
				"ideasFound" => dgettext($domain, "Ideas have been found"),
/// search_field Database field such as 'title', 'abstract', 'quotes' etc.
				"field" => dgettext($domain, "Search on Fields"),
				"metadata" => dgettext($domain, "Metadata Type"),
				"availableKeyword" => dgettext($domain, "Keywords"),
				"availableMetaKeyword" => dgettext($domain, "Metadata Keywords"),
				"keyword" => dgettext($domain, "Selected Keywords"),
				"availableUserTag" => dgettext($domain, "User Tags"),
				"userTag" => dgettext($domain, "Selected User Tags"),
				"availableCategory" => dgettext($domain, "Categories"),
				"category" => dgettext($domain, "Selected Categories"),
				"availableSubcategory" => dgettext($domain, "Subcategories"),
				"subcategory" => dgettext($domain, "Subcategory"),
				"category" => dgettext($domain, "Category"),
				"keyword" => dgettext($domain, "Keyword"),
				"metaKeyword" => dgettext($domain, "Metadata keyword"),
				"usertag" => dgettext($domain, "User tag"),
				"publisher" => dgettext($domain, "Publisher"),
				"collection" => dgettext($domain, "Collection"),
				"tag" => dgettext($domain, "Import tag"),
				"addedBy" => dgettext($domain, "Resource added by"),
				"editedBy" => dgettext($domain, "Resource edited by"),
				"searchSelect" => dgettext($domain, "Search/Select"),
				"test" => dgettext($domain, "View natural language"),
				"naturalLanguage" => dgettext($domain, "Results where"),
			),
/// listParams_ When displaying the results of a list, select or search, display the selection parameters
		    "listParams" => array(
				"word" => dgettext($domain, "Search word(s)"),
				"partial" => dgettext($domain, "Partial word match"),
				"field" => dgettext($domain, "Field"),
				"language" => dgettext($domain, "Language"),
				"type" => dgettext($domain, "Type"),
				"tag" => dgettext($domain, "Import Tag"),
				"attachment" => dgettext($domain, "With at least one attachment"),
				"collection" => dgettext($domain, "Collection"),
				"category" => dgettext($domain, "Category"),
				"subcategory" => dgettext($domain, "Subcategory"),
				"userTag" => dgettext($domain, "User tag"),
				"notInUserBib" => dgettext($domain, "NOT in user bibliography"),
				"publisher" => dgettext($domain, "Publisher"),
				"keyword" => dgettext($domain, "Keyword"),
				"keywordGroup" => dgettext($domain, "Keyword Group"),
				"creator" => dgettext($domain, "Creator"),
				"list" => dgettext($domain, "List (###)"),
				"addedBy" => dgettext($domain, "Resource added by ###"),
				"editedBy" => dgettext($domain, "Resource last edited by ###"),
/// listParams_listParams List, search or select parameters when displaying a list
				"listParams" => dgettext($domain, "Parameters"),
/// listParams_listParamMultiple When displaying search, select or list parameters, this message is displayed if there are too many to reasonably display
				"listParamMultiple" => dgettext($domain, "Multiple"),
				"cited" => dgettext($domain, "Cited"),
				"year" => dgettext($domain, "Year"),
				"bibliography" => dgettext($domain, "Bibliography"),
				"listAll" => dgettext($domain, "List all"),
				"department" => dgettext($domain, "Department"),
				"institution" => dgettext($domain, "Institution"),
			),
/// resourceType_ Mapping WKX_resource.type to description.
		    "resourceType" => array(
				"book" => dgettext($domain, "Book"),
/// resourceType_book_article Titled chapter in book (i.e. chapter has title not number)
				"book_article" => dgettext($domain, "Book Article"),
/// resourceType_book_chapter Numeric chapter in book
				"book_chapter" => dgettext($domain, "Book Chapter Number"),
				"web_article" => dgettext($domain, "Web Article"),
				"web_site" => dgettext($domain, "Web Site"),
				"web_encyclopedia" => dgettext($domain, "Web Encyclopedia"),
				"web_encyclopedia_article" => dgettext($domain, "Web Encyclopedia Article"),
				"journal_article" => dgettext($domain, "Journal Article"),
				"newspaper_article" => dgettext($domain, "Newspaper Article"),
				"thesis" => dgettext($domain, "Thesis/Dissertation"),
				"proceedings_article" => dgettext($domain, "Proceedings Article"),
/// resourceType_broadcast TV or Radio broadcast
				"broadcast" => dgettext($domain, "Broadcast"),
				"film" => dgettext($domain, "Film"),
/// resourceType_legal_ruling Legal Ruling or Regulation
				"legal_ruling" => dgettext($domain, "Legal Rule/Regulation"),
/// resourceType_software Computer software
				"software" => dgettext($domain, "Software"),
/// resourceType_artWork Art etc.
				"artwork" => dgettext($domain, "Artwork"),
/// resourceType_audiovisual Audiovisual material
				"audiovisual" => dgettext($domain, "Audiovisual"),
/// resourceType_case Legal cases
				"case" => dgettext($domain, "Legal Case"),
/// resourceType_bill Parliamentary bill (law)
				"bill" => dgettext($domain, "Bill"),
/// resourceType_classical Classical (historical) work
				"classical" => dgettext($domain, "Classical Work"),
				"conference_paper" => dgettext($domain, "Conference Paper"),
				"conference_poster" => dgettext($domain, "Conference Poster"),
/// resourceType_report Reports or documentation
				"report" => dgettext($domain, "Report/Documentation"),
/// resourceType_government_report Government report or documentation
				"government_report" => dgettext($domain, "Government Report/Documentation"),
/// resourceType_hearing Legal/Government Hearing
				"hearing" => dgettext($domain, "Hearing"),
/// resourceType_database Online databases
				"database" => dgettext($domain, "Online Database"),
				"magazine_article" => dgettext($domain, "Magazine Article"),
				"manuscript" => dgettext($domain, "Manuscript"),
/// resourceType_map Maps
				"map" => dgettext($domain, "Map"),
/// resourceType_chart Charts/images
				"chart" => dgettext($domain, "Chart/Image"),
/// resourceType_statute Statute
				"statute" => dgettext($domain, "Statute"),
/// resourceType_patent Patents
				"patent" => dgettext($domain, "Patent"),
/// resourceType_brochure Company brochure
				"brochure" => dgettext($domain, "Brochure"),
/// resourceType_personal Personal Communication
				"personal" => dgettext($domain, "Personal Communication"),
/// resourceType_unpublished Unpublished work
				"unpublished" => dgettext($domain, "Unpublished Work"),
/// resourceType_proceedings Conference proceedings (complete set)
				"proceedings" => dgettext($domain, "Proceedings"),
/// resourceType_music_album Recorded music
				"music_album" => dgettext($domain, "Recorded Music Album"),
				"music_track" => dgettext($domain, "Recorded Music Track"),
/// resourceType_music_score Sheet music
				"music_score" => dgettext($domain, "Music Score"),
/// resourceType_miscellaneous For anything else that does not fit into the above categories.
				"miscellaneous" => dgettext($domain, "Miscellaneous"),
/// resourceType_miscellaneous_section Similar to miscellaneous but a part of something else
				"miscellaneous_section" => dgettext($domain, "Miscellaneous Section"),
/// resourceType_genericBook Generic resource types used when creating bibliographic styles.
				"genericBook" => dgettext($domain, "Generic book-type"),
				"genericArticle" => dgettext($domain, "Generic article-type"),
				"genericMisc" => dgettext($domain, "Generic miscellaneous"),
			),
/// category_ Administration of categories
		    "category" => array(
				"addCategory" => dgettext($domain, "Add category"),
				"addSubcategory" => dgettext($domain, "Add subcategory"),
				"editCategory" => dgettext($domain, "Edit category"),
				"editSubcategory" => dgettext($domain, "Edit subcategory"),
				"deleteCategory" => dgettext($domain, "Delete category"),
				"deleteSubcategory" => dgettext($domain, "Delete subcategory"),
				"deleteCatConfirm" => dgettext($domain, "Delete category(s) ###"),
				"deleteSubConfirm" => dgettext($domain, "Delete subcategory(s) ###"),
				"deleteWarning" => dgettext($domain, "Any resource belonging to the category(s) you are deleting that does not belong to another category will be placed in the 'General' category"),
			),
/// submit_ Form submit button text
		    "submit" => array(
				"Submit" => dgettext($domain, "Submit"),
				"List" => dgettext($domain, "List"),
				"Search" => dgettext($domain, "Search"),
				"Select" => dgettext($domain, "Select"),
				"Proceed" => dgettext($domain, "Proceed"),
/// submit_Reset Reset button for forms
				"Reset" => dgettext($domain, "Reset"),
				"Continue" => dgettext($domain, "Continue"),
				"Delete" => dgettext($domain, "Delete"),
				"Confirm" => dgettext($domain, "Confirm"),
				"Edit" => dgettext($domain, "Edit"),
				"basketAdd" => dgettext($domain, "Add to basket"),
				"basketRemove" => dgettext($domain, "Remove from basket"),
				"Add" => dgettext($domain, "Add"),
				"ApproveResource" => dgettext($domain, "Approve resource"),
				"QuarantineResource" => dgettext($domain, "Quarantine resource"),
				"Remove" => dgettext($domain, "Remove"),
				"Email" => dgettext($domain, "Email"),
/// submit_Cite Add citation
				"Cite" => dgettext($domain, "Cite"),
				"Save" => dgettext($domain, "Save"),
				"Test" => dgettext($domain, "Test"),
/// submit_Cache Convert and cache attachments
				"Cache" => dgettext($domain, "Convert attachments"),
				"OK" => dgettext($domain, "OK"),
				"Close" => dgettext($domain, "Close"),
/// submit_Return Not really a submit button but ALT text on the 'Return' icon used for navigation
				"return" => dgettext($domain, "Return"),
			),
/// import_ Bibliography import messages
		    "import" => array(
		    	"bibtexImport" => dgettext($domain, "You may import BibTeX bibliographies (.bib files) here. Large files may take some time so if WIKINDX senses that php.ini's 'max_execution_time' variable is about to be exceeded, it will start importing the bibliography in chunks. If you have custom fields in your import file, create custom fields first in the WIKINDX database (the Admin menu) so that you can then map the import custom fields."),
				"category" => dgettext($domain, "Category"),
				"categoryPrompt" => dgettext($domain, "All WIKINDX resources belong to at least one category which you chose here.  The category(s) a resource belongs to can always be edited later."),
/// import_pasteBibtex An ordinary user may cut 'n' paste BibTeX entries into a textarea box for importing into the bibliography. '###' is the maximum number that the admin allows. Don't translate '@string'
				"pasteBibtex" => dgettext($domain, "You may paste up to ### BibTeX entries here in addition to @string types."),
				"pasteBibtex2" => dgettext($domain, "Paste the BibTeX entries here"),
/// import_importDuplicates For file imports, allow duplicates?
				"importDuplicates" => dgettext($domain, "Import duplicates:"),
				"storeRawLabel" => dgettext($domain, "Store unused fields:"),
				"empty" => dgettext($domain, "File is empty"),
				"added" => dgettext($domain, "No. resources added: ###"),
				"discarded" => dgettext($domain, "No. resources discarded (duplicates, no titles, or in the deactivated resource type list): ###"),
/// import_invalidField1 If non-standard BibTeX fields are found in the input file, invite the user to map these fields to wikindx fields
				"invalidField1" => dgettext($domain, "Unknown fields have been found. You may map these fields to WIKINDX fields -- no duplicate mapping is allowed."),
				"invalidField2" => dgettext($domain, "Where an unknown field is mapped to a WIKINDX field that would normally be automatically mapped to a standard input field, the unknown field mapping takes precedence"),
				"invalidField3" => dgettext($domain, "Unknown fields have been found. You may map these fields to custom fields -- no duplicate mapping is allowed."),
				"file" => dgettext($domain, "Import File"),
				"tag" => dgettext($domain, "Tag"),
/// import_executionTimeExceeded With large imports that would go over php.ini's max_execution time, WIKINDX splits the imports into chunks
				"executionTimeExceeded" => dgettext($domain, "'max_execution_time' (### seconds) in php.ini was about to be exceeded.  WIKINDX is importing the bibliography in chunks."),
				"addedChunk" => dgettext($domain, "No. resources added this chunk: ###"),
				"quarantine" => dgettext($domain, "Quarantine from public view:"),
			),
/// user_ Users in a multi user WIKINDX
		    "user" => array(
			    "user" => dgettext($domain, "User"),
				"passwordConfirm" => dgettext($domain, "Confirm password"),
				"username" => dgettext($domain, "Username"),
				"deleteConfirm" => dgettext($domain, "Delete user(s): ###"),
				"fullname" => dgettext($domain, "Full name"),
				"department" => dgettext($domain, "Department"),
				"institution" => dgettext($domain, "Institution"),
				"isCreator" => dgettext($domain, "User is creator"),
				"password" => dgettext($domain, "Password"),
				"email" => dgettext($domain, "Email address"),
/// user_emailText Do not use any TAB or CR in this one or the message formatting will be messed up in the email client.
				"emailText" => dgettext($domain, "You recently registered to use our WIKINDX. If this was not you, please ignore this email.  Otherwise, to complete the registration process, please go to the following address and follow the instructions there:"),
/// user_emailText2 Do not use any TAB or CR in this one or the message formatting will be messed up in the email client.
				"emailText2" => dgettext($domain, "Thank you for registering to use our WIKINDX.  Please keep this email for reference:"),
/// user_emailText3 Do not use any TAB or CR in this one or the message formatting will be messed up in the email client.
				"emailText3" => dgettext($domain, "You recently updated your details on our WIKINDX.  Please keep this email for reference:"),
/// user_cookie For optional cookies
				"cookie" => dgettext($domain, "Remember me"),
/// user_forget1 If the user forgets a password, they can answer a series of questions they have earlier supplied to get a new temporary password emailed to them
				"forget1" => dgettext($domain, "If you forget your password at some point in the future, you may have a temporary password emailed to you by correctly answering up to three questions. You should ensure that the email address stored by WIKINDX is always up to date."),
				"forget2" => dgettext($domain, "Enter up to three short questions and answers:"),
				"forget3" => dgettext($domain, "Question ###"),
				"forget4" => dgettext($domain, "Answer ###"),
				"forget5" => dgettext($domain, "If you have just loaded this page and a question is displayed here but no answer, then the answer is already stored encrypted in the database. You must, however, supply answers when clicking on the 'Edit' button."),
				"forget6" => dgettext($domain, "Forgotten your password?"),
				"forget7" => dgettext($domain, "Enter either your username or email as stored in the WIKINDX."),
				"forget8" => dgettext($domain, "Answer the following questions:"),
				"forget9" => dgettext($domain, "You recently requested a reset password for your WIKINDX account. A temporary password is given below. You should log into this WIKINDX as soon as possible and change this password in your user settings."),
				"forget10" => dgettext($domain, "Thank you for correctly answering the questions. An email has been sent to your address with a temporary password which you should change the next time you log into WIKINDX."),
				"forget11" => dgettext($domain, "Return to log in prompt"),
				"masterBib" => dgettext($domain, "WIKINDX Master Bibliography"),
				"bibliography" => dgettext($domain, "Bibliography"),
				"homeBib" => dgettext($domain, "Front page"),
				"deleteConfirmBib" => dgettext($domain, "Delete bibliography ###"),
/// user_unknown When a user has been deleted but her input remains, display this when viewing a resource and its associated text
				"unknown" => dgettext($domain, "Deleted user"),
				"deleteUserMetadata1" => dgettext($domain, "How do you wish to deal with the deleted users' quotes, paraphrases, musings, ideas, and comments (metadata)? Metadata left unchanged will be shown as added by 'Deleted user'. If you choose to delete users' metadata, other users' comments relating to those metadata will also be deleted. A deleted user's resources are not deleted. If you choose not to delete metadata, all private and group musings, comments, and ideas will be made public"),
				"deleteUserMetadata2" => dgettext($domain, "Leave unchanged"),
				"deleteUserMetadata3" => dgettext($domain, "Transfer to superadmin"),
				"deleteUserMetadata4" => dgettext($domain, "Delete"),
/// user_bib This user's bibliographies
				"bib" => dgettext($domain, "User bibliographies"),
				"useBib" => dgettext($domain, "Use this bibliography for browsing"),
				"displayBib" => dgettext($domain, "Display bibliography details"),
				"bibTitle" => dgettext($domain, "Title"),
				"bibDescription" => dgettext($domain, "Description"),
				"noBibs" => dgettext($domain, "You do not yet have any bibliographies"),
				"createBib" => dgettext($domain, "Create a new user bibliography"),
				"createGroupBib" => dgettext($domain, "Create a new group bibliography"),
				"deleteBib" => dgettext($domain, "Delete bibliography"),
				"editBib" => dgettext($domain, "Edit bibliography details"),
				"bibUserAdd" => dgettext($domain, "Add users to this bibliography"),
				"deleteFromBib" => dgettext($domain, "Delete from bibliography"),
/// user_otherBibs displayed in select box - other users' bibliographies
				"otherBibs" => dgettext($domain, "______OTHER USERS______"),
/// user_userBibs displayed in select box
				"userBibs" => dgettext($domain, "___MY BIBLIOGRAPHIES___"),
/// user_userGroupBibs displayed in select box
				"userGroupBibs" => dgettext($domain, "___GROUP BIBLIOGRAPHIES___"),
				"numResources" => dgettext($domain, "Number of resources"),
				"admin" => dgettext($domain, "Administrator"),
				"bypassPasswordCheck" => dgettext($domain, "Bypass the password check"),
/// user_notification Email notification of resource additions/edits etc.
				"notification" => dgettext($domain, "Email notification"),
				"notifyNone" => dgettext($domain, "Never"),
				"notifyAll" => dgettext($domain, "When any resource or its text is added/edited by other users"),
				"notifyMyBib" => dgettext($domain, "When any resource from my bibliographies or its text is added/edited by other users"),
				"notifyMyCreator" => dgettext($domain, "When any resource or its text for which I am a creator is added/edited by other users"),
				"notifyAdd" => dgettext($domain, "When a resource has been added"),
				"notifyEdit" => dgettext($domain, "When a resource or its text has been edited"),
				"notifyThreshold" => dgettext($domain, "Provide a digest of additions/edits after x number of days since the last notification"),
				"notifyImmediate" => dgettext($domain, "Immediately"),
				"notifyDigestThreshold" => dgettext($domain, "At or below this value, receive a list of all added or edited resources; above this value, receive just the number of added or edited resources"),
				"notify" => dgettext($domain, "### has added or edited the following resource or its text"),
/// user_notifyMass1 Email notification of new resources when there has been a mass import of more than 10.  This will produce something like: "Mark has added 29 new resources"
				"notifyMass1" => dgettext($domain, "### has added"),
				"notifyMass2" => dgettext($domain, "### new resources"),
				"notifyMass3" => dgettext($domain, "### resources have been added or edited since your last notification"),
				"notifyMass4" => dgettext($domain, "The following resources have been added or edited since your last notification"),
/// user_addGroupsToBib When creating or editing a user group bibliography, the group admin can add user groups with access to this bibliography
				"addGroupsToBib" => dgettext($domain, "Give a user group write access to this bibliography"),
				"noGroups" => dgettext($domain, "You are not yet the administrator of any user groups"),
				"createGroup" => dgettext($domain, "Create a new user group"),
				"deleteGroup" => dgettext($domain, "Delete user group"),
				"deleteGroup2" => dgettext($domain, "(This will also delete any user group bibliographies for this group)"),
				"deleteConfirmGroup" => dgettext($domain, "Delete user group###"),
				"editGroup" => dgettext($domain, "Edit user group"),
				"groups" => dgettext($domain, "User groups"),
				"group" => dgettext($domain, "User group"),
				"groupTitle" => dgettext($domain, "Title"),
				"groupDescription" => dgettext($domain, "Description"),
				"potentialUsers" => dgettext($domain, "Potential users"),
				"selectedUsers" => dgettext($domain, "Users in this group"),
				"userTags" => dgettext($domain, "User tags"),
				"noUserTags" => dgettext($domain, "You do not yet have any user tags"),
				"tagTitle" => dgettext($domain, "Title"),
				"createUserTag" => dgettext($domain, "Create a new user tag"),
				"deleteUserTag" => dgettext($domain, "Delete user tag"),
				"editUserTag" => dgettext($domain, "Edit user tag"),
				"deleteConfirmUserTag" => dgettext($domain, "Delete user tag ###"),
/// user_emailText4 Text that is emailed to the WIKINDX admin advising of a request for user registration.  'Admin' and 'Users' should be the same translations as in the menu array.
				"emailText4" => dgettext($domain, "There has been a request for WIKINDX registration.  In order to manage this request, please log on and use the 'Admin|Users' menu."),
/// user_emailText5 A request for user registration has been declined.  '###' is the URL of the WIKINDX
				"emailText5" => dgettext($domain, "You recently requested registration at: ###. Unfortunately, the WIKINDX administrator has declined your request."),
/// user_pendingRegistration1 The Administrator has registration requests to manage
				"pendingRegistration1" => dgettext($domain, "Pending registration requests"),
				"pendingRegistration2" => dgettext($domain, "Potential users will be emailed your decision with those accepted being invited to complete the registration process."),
				"registrationAccept" => dgettext($domain, "Accept registration"),
				"registrationDecline" => dgettext($domain, "Decline registration"),
				"noUsers" => dgettext($domain, "There are no users requesting registration"),
				"authorizedUsers" => dgettext($domain, "Authorized users"),
				"blockedUsers" => dgettext($domain, "Blocked users"),
			),
/// cite_ Messages for adding citations to quotes, notes, musings , comments etc. and for administration of citation templates within bibliographic style creation/editing
		    "cite" => array(
				"cite" => dgettext($domain, "Cite"),
/// cite_preText Text preceeding and following citations e.g. (see Grimshaw 1999; Boulanger 2004 for example): 'see' is preText and 'for example' is postText
				"preText" => dgettext($domain, "Preliminary text"),
				"postText" => dgettext($domain, "Following text"),
				"pages" => dgettext($domain, "Pages"),
			),
/// creators_ Various types of creators
		    "creators" => array(
				"author" => dgettext($domain, "Authors"),
				"editor" => dgettext($domain, "Editors"),
				"translator" => dgettext($domain, "Translators"),
				"reviser" => dgettext($domain, "Revisers"),
				"seriesEditor" => dgettext($domain, "Series Editors"),
/// creators_director For films etc.
				"director" => dgettext($domain, "Director"),
				"producer" => dgettext($domain, "Producer"),
				"company" => dgettext($domain, "Company"),
/// creators_artist For artwork
				"artist" => dgettext($domain, "Artist"),
				"performer" => dgettext($domain, "Performer"),
/// creators_counsel For legal cases
				"counsel" => dgettext($domain, "Counsel"),
				"judge" => dgettext($domain, "Judge"),
/// creators_attributedTo For classical works of doubtful provenance
				"attributedTo" => dgettext($domain, "Attributed to"),
/// creators_cartographer Map makers
				"cartographer" => dgettext($domain, "Cartographer"),
/// creators_creator Charts/images
				"creator" => dgettext($domain, "Creator"),
/// creators_inventor For patents
				"inventor" => dgettext($domain, "Inventor"),
				"issuingOrganisation" => dgettext($domain, "Issuing Organisation"),
				"agent" => dgettext($domain, "Agent/Attorney"),
/// creators_intAuthor International patent author
				"intAuthor" => dgettext($domain, "International Author"),
/// creators_recipient Personal Communication
				"recipient" => dgettext($domain, "Recipient"),
/// creators_composer For Musical works
				"composer" => dgettext($domain, "Composer"),
				"conductor" => dgettext($domain, "Conductor"),
/// creators_supervisor for theses
				"supervisor" => dgettext($domain, "Supervisors"),
/// creators_creatorExists Advice on what to do when editing a creator name and the new name already exists in the database.
				"creatorExists" => dgettext($domain, "If you proceed, this edited creator will be deleted and all references in the database to it will be replaced by references to the pre-existing creator."),
				"creators" => dgettext($domain, "Creators"),
/// creators_alias Some creators might have multiple names . . .
				"alias" => dgettext($domain, "Alias: ###"),
			),
/// custom_ For managing custom database fields
		    "custom" => array(
/// custom_label The label given to the field
				"label" => dgettext($domain, "Label"),
/// custom_size The field storage space can be small or large
				"size" => dgettext($domain, "The database size allocation for the field can be small (max. 255 characters) or large"),
				"small" => dgettext($domain, "Small"),
				"large" => dgettext($domain, "Large"),
				"warning" => dgettext($domain, "Deleting these fields will also remove any resource data belonging to the field."),
				"addLabel" => dgettext($domain, "Add a field"),
				"deleteLabel" => dgettext($domain, "Delete fields"),
				"editLabel" => dgettext($domain, "Edit fields"),
				"deleteConfirm" => dgettext($domain, "Delete fields(s) ###"),
				"customFields" => dgettext($domain, "Custom fields"),
				"customField" => dgettext($domain, "Custom field"),
			),
/// statistics_ Messages for the administrator statistics section
		    "statistics" => array(
				"maxAccesses" => dgettext($domain, "Highest number of views for any resource:"),
				"minAccesses" => dgettext($domain, "Lowest number of views for any resource:"),
				"firstAdded" => dgettext($domain, "Date the first resource was added:"),
				"lastAdded" => dgettext($domain, "Date the last resource was added:"),
/// statistics_meanAddedResource 'mean' is a synonym for 'average'
				"meanAddedResource" => dgettext($domain, "Mean date of added resources:"),
				"totalResources" => dgettext($domain, "Total resources:"),
				"totalQuotes" => dgettext($domain, "Total quotes:"),
				"totalParaphrases" => dgettext($domain, "Total paraphrases:"),
				"totalMusings" => dgettext($domain, "Total musings:"),
				"userResourceTotal" => dgettext($domain, "Greatest number of resources input by any one user:"),
				"userQuoteTotal" => dgettext($domain, "Greatest number of quotes input by any one user:"),
				"userParaphraseTotal" => dgettext($domain, "Greatest number of paraphrases input by any one user:"),
				"userMusingTotal" => dgettext($domain, "Greatest number of public musings input by any one user:"),
				"resourceTypes" => dgettext($domain, "Number of Resource Types:"),
/// statistics_emailSubject Subject for email of statistics to users
				"emailSubject" => dgettext($domain, "### (Usage statistics)"),
				"emailIntro" => dgettext($domain, "Your monthly statistics for ###. The indices are an indication of ranking weighted according to how long the resource has been available.  The closer to 100% the index is, the higher the ranking.  The downloads index is calculated across all attachments a resource has while the popularity index is a combination of views index and downloads index."),
				"emailViewsMonth" => dgettext($domain, "### views in the last month"),
				"emailViewsTotal" => dgettext($domain, "(### views in total)"),
				"emailDownloadsMonth" => dgettext($domain, "### attachment downloads in the last month"),
				"emailDownloadsTotal" => dgettext($domain, "(### attachment downloads in total)"),
				"userStats" => dgettext($domain, "Statistics are shown for each user as: user/total (% of total)"),
				"noUserStats" => dgettext($domain, "No user statistics are available"),
				"userResources" => dgettext($domain, "Resources"),
				"userQuotes" => dgettext($domain, "Quotes"),
				"userParaphrases" => dgettext($domain, "Paraphrases"),
				"userMusings" => dgettext($domain, "Public musings"),
			),
/// footer_ Messages for display in the WIKINDX footer for each page
		    "footer" => array(
				"resources" => dgettext($domain, "Total resources:"),
				"queries" => dgettext($domain, "Database queries:"),
				"execution" => dgettext($domain, "Script execution:"),
				"dbtime" => dgettext($domain, "DB execution:"),
				"user" => dgettext($domain, "Username:"),
				"style" => dgettext($domain, "Style:"),
				"bib" => dgettext($domain, "Bibliography:"),
			),
/// cms_ Messages for handling CMS (Content Management System) code
		    "cms" => array(
/// cms_introduction1 Do not translate the README_CMS link
				"introduction1" => dgettext($domain, "If you wish to display elements of your WIKINDX in a Content Management System (CMS) you may generate CMS 'replacement' tags here and also interrogate the database for WIKINDX ID numbers.  WIKINDX only generates the CMS 'replacement' tag for you to paste into your CMS source -- the coding and parsing of that tag in your CMS is a task for you to do.  Refer to <a href=\"docs/README_CMS.txt\">README_CMS</a>."),
				"introduction2" => dgettext($domain, "If you wish to display a list from your WIKINDX in a Content Management System (CMS), copy the text below to a text file on your CMS system. Refer to <a href=\"docs/README_CMS.txt\">README_CMS</a>."),
				"displayIds" => dgettext($domain, "Display WIKINDX ID numbers"),
/// cms_generateCmsTag Generate a CMS (Content Management System) 'replacement' tag
				"generateCmsTag" => dgettext($domain, "Generate a CMS tag"),
/// cms_cmsTagStart The initial section of a CMS (Content Management System) 'replacement' tag
				"cmsTagStart" => dgettext($domain, "CMS tag starts with"),
/// cms_cmsTagEnd The closing section of a CMS (Content Management System) 'replacement' tag
				"cmsTagEnd" => dgettext($domain, "CMS tag ends with"),
				"pageStart" => dgettext($domain, "Page start"),
				"pageEnd" => dgettext($domain, "Page end"),
				"tag" => dgettext($domain, "Generated tag"),
/// cms_preText Text preceeding and following citations e.g. (see Grimshaw 1999; Boulanger 2004 for example): 'see' is preText and 'for example' is postText
				"preText" => dgettext($domain, "Preliminary text"),
				"postText" => dgettext($domain, "Following text"),
			),
/// news_ Administering and display of news items
		    "news" => array(
				"deleteConfirm" => dgettext($domain, "Delete news ###"),
				"title" => dgettext($domain, "Title"),
				"body" => dgettext($domain, "Main Text"),
				"emailNews" => dgettext($domain, "Email the edited news to registered users"),
			),
/// tinymce_ Mesages for dialog tiny mce boxes
		    "tinymce" => array(
    			"headingAddTable" => dgettext($domain, "Add Table"),
    			"headingAddImage" => dgettext($domain, "Add Image"),
    			"headingAddLink" => dgettext($domain, "Add Link"),
    			"tableColumns" => dgettext($domain, "Columns"),
    			"tableRows" => dgettext($domain, "Rows"),
    			"imagePath" => dgettext($domain, "Image URL"),
    			"linkPath" => dgettext($domain, "URL"),
    			"fileName" => dgettext($domain, "File name"),
    			"size" => dgettext($domain, "Size"),
    			"lastUpdated" => dgettext($domain, "Last updated"),
    			"upload" => dgettext($domain, "Upload file"),
		    ),
		);
	}
}
