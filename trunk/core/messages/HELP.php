<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/*****
* HELP class (English)
*
* NOTE TO TRANSLATORS:
*           1/  Both the class name and the constructor name should be changed to match the (case-sensitive) name of
*				the folder your language files are in.  For example, if you are supplying a Klingon translation and
*				your languages/ folder is languages/kn/, the class and constructor name for the file SUCCESS.php
*				must both be SUCCESS_kn.
*			2/  Do not change the key (the first part) of an array element.
*			3/  Ensure that each array element value is enclosed in double quotes "..." and is followed by a comma "...",
*			4/  The class name should be changed to match the (case-sensitive) name of
*				the folder your language files are in.  For example, if you are supplying a Klingon translation and
*				your languages/ folder is languages/kn/, the class name for the file SUCCESS.php
*				must both be SUCCESS_kn.
*
* NOTE TO DEVELOPERS:
*           1/  Any comments not preceeded by '///' at the start of the line will be ignored by the localization module.
*			2/  All comments should be on one line (no line breaks) and must start at the beginning of the line for the localization module to work.
*			3/  Comments must be of the form '/// dbError_ This array does this' where 'dbError' is the array that the comment refers to
*				or may be of the form '/// dbError_open This is the comment'
*				where 'dbError_open' is the exact array and array key that the comment refers to.
*****/
class HELP
{
    private $config;
    private $session;
    private $languageArray = [];

	public function __construct()
	{
		$this->config = FACTORY_CONFIG::getInstance();
		$this->session = FACTORY_SESSION::getInstance();
	    $this->languageArray = $this->loadArrays();
	}
/**
* Grab the message
*
*
* Override class function as /languages/xx/HELP.php has only one array
* @param string $indexName
* @param string $extra Optional string that replaces '###' in the array element value string. Default is FALSE
* @param boolean $html Optional boolean for HTML printing (TRUE/default) or plain text (FALSE)
*
* @return string
*/
	public function text($indexName, $extra = FALSE, $html = TRUE)
	{
		$message = $this->internaltext($indexName);
		//$message = preg_replace_callback('/#([^#]*)#/Uu', array($this, 'textCallback'), $message);
		$message = preg_replace("/###/u", str_replace("\\", "\\\\", trim($extra . "")), $message);
		$message = stripslashes($message);
		
		if($html)
			return UTF8::html_uentity_decode(trim($message));
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
	private function internaltext($indexName)
	{
	    $arrayName = "help";
	    
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
	    $wikindxVersion = WIKINDX_PLUGIN_VERSION;
	    
        $tmpLanguageArray = array(
/// search_ Here, and elsewhere, do not edit #search# or similar words enclosed with '#', or words prefaced with '$'.
"search" => dgettext($domain, "<h3>Search</h3>")
. dgettext($domain, "<p>There are two types of search available:</p>")
. dgettext($domain, "<ul>
<li><strong>Quick Search:</strong></li>
<ul>
<li>A set number of database fields are searched: title; note; abstract; quote; quote comment; paraphrase; paraphrase comment; musing; creator surname; resource keyword; user tag; and any custom fields</li>
<li>Partial word searches are the default unless the search term is an exact phrase</li>
<li>You can use control words as noted below</li>
<li>The restrictions noted below on searches using the abstract and note fields pertain here</li>
</ul>
</li>
<li><strong>Advanced Search:</strong></li>
</li>
<ul>
<li>Complex composite search and select operations may be constructed by adding new fields. Some of these fields can be searched for words or phrases, some can be selected within, and some make use of numerical comparison</li>
<li>The abstract and note fields are searched on in FULLTEXT BOOLEAN mode. This is a quick and efficient method for searching over potentially large text fields but will not be able to correctly search where the search term and text comprise Chinese or Japanese characters. In order to do this, your MySQL server must have the requisite parser: see <a href='https://dev.mysql.com/doc/refman/8.0/en/fulltext-restrictions.html'>Full-Text Restrictions</a> in the MySQL documentation. Wildcard characters will be ignored for these two fields</li>
<li>Document searches can be performed on resource attachments if they are of type PDF, DOC or DOCX – searches are carried out on the cached versions of attachments. If you use the first select field to search on attachments any 'NOT' in the search field will be ignored. Attachment searches are not filtered for the list of ignored words (see below)</li>
<li>The 'OR', 'AND' and 'NOT' radio buttons logically link that set of search parameters to the previous set. For example, five search elements that are sequentially 1 OR 2 AND 3 NOT 4 OR 5 will be grouped as 1 OR (2 AND 3 NOT 4) OR 5</li>
<li>The structure and logic of the operation may be viewed before searching by clicking on the 'View natural language' icon</li>
<li>Multiple selections may be made through various combinations of holding (on Windows and Linux) the Control and Shift keys while clicking (on Apple, the Command and Shift keys). Use the arrows to transfer select options between the select box listing those available to use and the select box listing those that will be used</li>
<li>The select boxes of selected options make use of the radio buttons 'OR' and 'AND'. For example (selecting just the Keyword field to search on), with two or more keywords selected and 'OR' set, each of the returned resources must contain at least one of those keywords.  With two or more keywords selected and 'AND' set, each of the returned resources must contain all those keywords</li>
<li>Ideas can also be searched but are displayed separately as they are not part of a resource</li>
</ul>
</li>
</ul>")
. dgettext($domain, "<p>In both types of search, the following rules hold for the word search phrase:</p>")
// translators: don't translate the placeholder #search#
. dgettext($domain, "<ul>
<li>You can use the control words <strong>AND</strong>, <strong>OR</strong> and <strong>NOT</strong> and can group words into exact phrases using double quote marks: <strong>\"</strong>search term<strong>\"</strong></li>
<li><strong>AND</strong>, <strong>OR</strong> and <strong>NOT</strong> are case-sensitive and function as control words only outside exact phrases</li>
<li>The wildcard characters '?' (zero or one character) and '*' (zero or multiple characters) can be used. In an exact phrase, these characters will treated literally</li>
<li>The use of wildcard characters disables partial word matching</li>
<li>The wildcard '?' will not match a single UTF-8 character due to the multibyte nature of UTF-8. Use '*' instead</li>
<li>Searches are case-insensitive</li>
<li>A space not in an exact phrase will be treated as <strong>OR</strong></li>
<li>All non-alphanumeric characters not in an exact phrase will be ignored unless the character is a wildcard</li>
<li><em>OR</em> words following <strong>AND</strong> or <strong>NOT</strong> will be grouped. You might choose, therefore, to have a string of <em>OR</em> words at the start of the phrase. Some examples: 
<ul>
<li>'word1 AND word2 OR word3 OR word4 NOT word5 word6' => 'word1 AND (word2 OR word3 OR word4) NOT (word5 OR word6)'</li>
<li>'word1 word2 OR word3 word4 NOT word5 word6 AND word7' => 'word1 OR word2 OR word3 OR word4 NOT (word5 OR word6) AND word7'</li>
<li>'NOT word1 word2 OR word3 OR word4 NOT word5 word6' => 'NOT (word1 word2 OR word3 OR word4) NOT (word5 OR word6)'</li>
</ul>
</li>
<li>The administrator has defined the following words which, if not in an exact phrase, will be ignored: <em>#search#</em></li>
</ul>"),

"resource" => dgettext($domain, "<h3>Resource</h3>")
. dgettext($domain, "<p>Any user can:</p>")
. dgettext($domain, "<ul>
<li>Store the resource in a basket which operates as a temporary collection of resources which you can view at any point from the Resource menu.</li>
<li>Store the resource as a bookmark which can be accessed from the Resource menu. Bookmarks are stored across sessions for registered users.</li>
</ul>")
. dgettext($domain, "<p>If you are a registered user you can:</p>")
. dgettext($domain, "<ul>
<li>Edit a number of elements on this page. Certain options, such as editing and adding attachments, need to be enabled by the administrator.</li>
<li>Add new elements such as quotations, paraphrases and musings. Comments (on quotations and paraphrases) and musings can be set to be to private, public or available to any user groups you have defined in My Wikindx.</li>
</ul>")
. dgettext($domain, "<p>If an element such as a keyword or category is hyperlinked, clicking on the link will display other resources belonging to that keyword or category. Any quotations, paraphrases and available musings can optionally have hyperlinked keywords linking them to other metadata.</p>")
. dgettext($domain, "<p>Depending on your user permissions, you will see various statistics on the page:</p>")
. dgettext($domain, "<ul>
<li>Views: Number of views this month / total number of views.</li>
<li>Views Index: An indication of the number of times this resource has been viewed compared to other resources weighted according to the amount of time the resource has been available. The higher the percentage, the greater the number of weighted views.</li>
<li>Downloads: A resource can have any number of attachments and each has a display giving downloads this month / total number of downloads.</li>
<li>Downloads Index: As with the Views Index, a weighted percentage of total downloads.</li>
<li>Popularity Index: A combination of Views Index and Downloads Index indicating the popularity of this resource.</li> <li>Maturity: A figure out of 10 assigned by the administrator indicating the subjective 'maturity' of the resource.</li>
</ul>"),

"collection" => dgettext($domain, "<h3>Editing Collections</h3>")
. dgettext($domain, "<p>Here, you can globally edit default values for all collections:</p>")
. dgettext($domain, "<ul>
<li>New collections are automatically created when adding or editing a resource that belongs to a previously non-existent collection.</li>
<li>The default values are the collection values displayed when adding or editing a new resource that belongs to a collection such as an anthology or a journal. When adding or editing a resource, the default values can be overridden for that resource but the default values for the collection can only be edited here.</li>
<li>A value in a 'Publisher name' field will override any selection in the 'Publisher' select box.</li>
<li>A value in a creator 'Last name' field will override any selection in the appropriate 'Creator' select box.</li>
<li>Editing the default values of a collection will update the information for resources within that collection.</li>
</ul>"),

"categoryTree" => dgettext($domain, "<h3>Category Tree</h3>")
. dgettext($domain, "<p>The Category Tree displays all categories used in the WIKINDX and, under each category, the category's subcategories and keywords. The number in brackets is the number of resources in each category, each category's subcategory or each category's keyword.</p>"),

"pasteBibtex" => dgettext($domain, "<h3>Paste BibTeX</h3>")
// translators: don't translate the placeholder #pasteBibtex#
. dgettext($domain, "<p>If you have a BibTeX bibliography, you can import selected entries, including @string values, by copying and pasting the entries from your BibTeX file into the textarea.  If you have non-standard fields in your BibTeX entries, WIKINDX will provide you with the option to map them to WIKINDX fields: if you are the administrator, you might like to create custom fields before pasting.  An administrator can post unlimited numbers of entries in one go, other users can post <strong>#pasteBibtex#</strong> entries at a time.</p>"),

"configure" => dgettext($domain, "<h3>Configure WIKINDX</h3>")
. dgettext($domain, "<p>Most of the configuration options are self-explanatory but bear the following in mind:</p>")

// translators: do not edit words prefaced with '$'
. dgettext($domain, "<ul>
<li>If you add the special string \$QUICKSEARCH\$ to the front page description, it will be replaced by the Quick Search form.</li>
<li>To disable registered users from pasting BibTeX entries, set the value to 0.  Administrators can always paste.</li>
<li>In cases where WIKINDX creates temporary files, such as when exporting bibliographies in various formats, you can define the age of a file in seconds after which the file will be deleted the next time a user logs on.</li>
<li>If set, statistics will be emailed at the start of each month to registered users who are named creators of resources.</li>
<li>You can deny read only access.  If read only access is allowed, the login prompt can be bypassed and users will go directly into the WIKINDX.</li>
<li>Printing PHP errors and SQL statements is for debugging purposes and should not be used on a live production server. Printing SQL statements will interfere with AJAX/javascript operations on pages such as Advanced Search and New/Edit Resource.</li>
</ul>")
. dgettext($domain, "<p>Some of the settings here, such as no. resources to display per page or the bibliographic style, are defaults that users can override in My Wikindx.</p>")
. dgettext($domain, "<p>You can add system users from the Admin menu.</p>")
. dgettext($domain, "<p>When adding or editing resources, each resource can belong to multiple categories and subcategories, be assigned custom fields or defined as belonging to a language -- admins can add new categories, subcategories, custom fields and languages from the Admin menu.</p>")
. dgettext($domain, "<p><strong>Because user sessions are created only once on login, changes to the configuration will not be registered until a user (logs out and) logs in.</strong></p>"),

"front" => "<h3>WIKINDX</h3>"
. dgettext($domain, "<p><strong>General tips</strong></p>")
. dgettext($domain, "<ul>
<li>Your web browser must accept cookies.</li>
<li>As WIKINDX functions as a program within a program, you should try not to use the web browser's back and forward navigation buttons but should, instead, use the navigation within WIKINDX.</li>
<li>WIKINDX uses sessions to temporarily store data and sessions are unique to a web browser instance and the type of web browser.  You may experience unexpected results if you use WIKINDX with more than one web browser window or tab although you can safely do this if the web browsers are different.</li>
<li>A <em>resource</em> in WIKINDX is a collection of data that comprises information forming the bibliographic record, metadata (index card-like information about or taken from the record such as quotations or musings -- the administrator may have disabled this feature), categories, subcategories and keywords. There may also be further information such as notes, abstract, attachments and external URLs in addition to statistical data.</li>
<li>If you are a registered user, you may create your own user tags and apply them to resources.</li>
<li>As a registered user, you can also create your own bibliographies drawn from the WIKINDX Master Bibliography.</li>
<li>Bibliographic formatting is applied in WIKINDX 'on-the-fly' using bibliographic styles compiled and defined by the administrator.  As with any bibliographic style, not all bibliographic data need be displayed. If, for example, you were to search for resources having M. Mouse as a creator, then resources might be returned where M. Mouse is, for instance, a series editor but, due to the requirements of the bibliographic style, is not displayed as such.</li>
</ul>")
. dgettext($domain, "<p><strong>Personal settings</strong></p>")
. dgettext($domain, "<p>The administrator will have defined default settings but, under the Wikindx|My Wikindx or Wikindx|Preferences menu, you can alter a number of parameters that change the way WIKINDX performs or displays its data. As a registered user, under the My Wikindx menu, these include:</p>") 
. dgettext($domain, "<ul>
<li>manage your personal details and email notification</li>
<li>set up user groups and bibliographies</li>
<li>define personal user tags.</li>
</ul>"),

"myWikindx" => dgettext($domain, "<h3>My Wikindx</h3>")
. dgettext($domain, "<p>As a registered user, settings here will be saved for the next time you use WIKINDX. Most settings are self-explanatory but some require further explanation:</p>")
. dgettext($domain, "<ul>
<li>Language/Locale: The default interface language of WIKINDX is English (United Kingdom). Other languages can be added as 3rd-party translators provide them and can be downloaded from <a href='https://sourceforge.net/projects/wikindx/'>the WIKINDX Sourceforge website</a>. Even if a language is not installed, the user can still set the environment locale (for the display of numbers, dates, currencies etc.). In all cases, missing messages in the selected language will be replaced by the default English. If the language option is set to 'Auto,' the set language will be determined by the locale of the server.</li>
<li>Paging links: If a resource list operation returns a high number of resources, this can take time to display. You can, however, define a subset of resources to display in the web browser then use paging links to move onto the next subset.</li>
<li>Tag clouds: Resources can be browsed (under the Search menu) as 'tag clouds' where the size and colour of the font represent relative frequency of the data being browsed.</li>
<li>Character limiting: Sometimes the text displayed in a form select box is long enough to cause problems with the web browser display. This is often the case with journal titles or publisher names. This field allows the user to limit the amount of text with any dropped text being replaced by ' ... '.</li>
<li>BibTeX:  BibTeX is a bibliographic format familiar to engineers and scientists.</li>
<li>CMS: If the administrator allows it, bibliographic data from WIKINDX may be displayed in Content Management Systems such as MediaWiki, Moodle and WordPress. To aid in this, you can display a 'cms' link that provides the data required.</li>
<li>Hyperlink view resource: when viewing a list of resources, the entire resource may be viewed by clicking on the view icon.  Additionally, you may set the entire bibliographic record to be the hyperlink to the resource. In this case, if the bibliographic record contains an external URL (the record is a web page, for example), then the external URL would no longer be hyperlinked.</li>
<li>Menu level: To use the screen space efficiently, WIKINDX makes use of multi-level menus.  These can, however, be difficult to use so you can opt to reduce the number of menu levels. In some cases, the template designer will mandate a certain number of menu levels in which case, attempting to change the number of menu levels for that template will make no difference.</li>
<li>User groups: In a multi-user WIKINDX, registered users can define user groups.  Potentially private information, such as comments on quotations or resource musings, can then be assigned to be viewed only by members of a user group.  Additionally, user groups may collaborate in building a user group bibliography.</li>
<li>User bibliographies: These are drawn from the WIKINDX Master Bibliography and may be personal or managed by a user group. Operations on a user bibliography (such as removing resources from it) have no effect on the WIKINDX Master Bibliography.</li>
</ul>"),

"preferences" => dgettext($domain, "<h3>Preferences</h3>")
. dgettext($domain, "<p>Most settings are self-explanatory but some require further explanation:</p>")
. dgettext($domain, "<ul>
<li>Language/Locale: The default interface language of WIKINDX is English (United Kingdom). Other languages can be added as 3rd-party translators provide them and can be downloaded from <a href='https://sourceforge.net/projects/wikindx/'>the WIKINDX Sourceforge website</a>. Even if a language is not installed, the user can still set the environment locale (for the display of numbers, dates, currencies etc.). In all cases, missing messages in the selected language will be replaced by the default English. If the language option is set to 'Auto,' the set language will be determined by the computer's locale.</li>
<li>Paging links: If a resource list operation returns a high number of resources, this can take time to display. You can, however, define a subset of resources to display in the web browser then use paging links to move onto the next subset.</li>
<li>Tag clouds: Resources can be browsed (under the Search menu) as 'tag clouds' where the size and colour of the font represent relative frequency of the data being browsed.</li>
<li>Character limiting: Sometimes the text displayed in a form select box is long enough to cause problems with the web browser display. This is often the case with journal titles or publisher names. This field allows the user to limit the amount of text with any dropped text being replaced by ' ... '.</li>
<li>Hyperlink view resource: when viewing a list of resources, the entire resource may be viewed by clicking on the view icon.  Additionally, you may set the entire bibliographic record to be the hyperlink to the resource. In this case, if the bibliographic record contains an external URL (the record is a web page, for example), then the external URL would no longer be hyperlinked.</li>
<li>Menu level: To use the screen space efficiently, WIKINDX makes use of multi-level menus.  These can, however, be difficult to use so you can opt to reduce the number of menu levels. In some cases, the template designer will mandate a certain number of menu levels in which case, attempting to change the number of menu levels for that template will make no difference.</li>
</ul>"),

"ideas" => dgettext($domain, "<h3>Ideas</h3>")
. dgettext($domain, "<p>Ideas are independent of resources and are a means to store thoughts related to the subject matter of the database.</p>")
. dgettext($domain, "<ul>
<li>Ideas are threaded -- new sub-ideas can be added to them.</li>
<li>The originator of the first idea in the thread is the owner of the thread.</li>
<li>The owner can set the privacy of the thread (public, private, or group) and changing this will change the privacy settings of the entire thread.</li>
<li>If threads are public or shared with a group, other registered users can add sub-ideas.</li>
<li>Only the owner of a sub-idea can edit and delete that sub-idea.</li>
<li>If the original idea is deleted, then the entire thread is deleted (you will be asked to confirm this).</li>
</ul>"),

"plugins" => dgettext($domain, "<h3>Components</h3>")
. dgettext($domain, "<p>Components are extras that are not part of the core WIKINDX download and can be plugins, templates, languages, or bibliographic styles. Plugins extend the functionality of WIKINDX beyond its core purpose and can be one of two types:  in-line plugins, where the output of the plugin is displayed in the body of WIKINDX; or menu plugins, where the plugins are accessed via the menus.</p>")
// translators: do not edit words prefaced with '$'
. dgettext($domain, "<p>Some plugins might not be compatible with this version of WIKINDX, and so they will not be visible to users, because \$wikindxVersion in the plugin's config.php is not equal to WIKINDX_PLUGIN_VERSION which is currently <strong>$wikindxVersion</strong>. Incompatible plugins will be still be listed in the 'Enabled plugins' select box. Update these plugins in order to use them. <font color='red'>If you manually update \$wikindxVersion in a plugin's config.php, the plugin is not guaranteed to work and, depending on the plugin, might corrupt your WIKINDX database.</font></p>")

. dgettext($domain, "<p>When checking the update status of plugins, styles, templates, and languages, only those that are enabled will be queried. Two update checks occur:</p>")

// translators: do not edit words prefaced with '$'
. dgettext($domain, "<ol>
<li>The timestamps of plugins, styles, templates, and languages on the remote server are compared to the timestamps on this WIKINDX</li>
<li>Each enabled plugin on this WIKINDX has its \$wikindxVersion compared to that on the remote server.</li>
</ol>")

. dgettext($domain, "<p>Additionally, the remote server is queried for any new files. If updates are found or new files are available, an appropriate link is supplied (an Internet connection is required).</p>")

. dgettext($domain, "<p>As an administrator, you can accomplish some management of components via this interface including:</p>")
. dgettext($domain, "<ul>
<li>Disable plugins (and templates, styles and languages):  This does not delete the plugin, it merely temporarily disables it until you re-enable it.</li>
<li>Position plugins:  You can reposition plugins in different menu hierachies.</li>
<li>Authorize: Block types of users from access to the plugins.</li>
</ul>")

// translators: do not edit words prefaced with '$'
. dgettext($domain, "<p>Positioning plugins and granting authorization is accomplished by editing the plugin's config.php file (typically only \$menus and \$authorize need be edited) -- be sure you know what you are doing:</p>")
. "<ul><li>"
. dgettext($domain, "\$menus should be an array of at least one of the following menu elements:")
// This code should not be translated
. "<ul>
        <li>'wikindx'</li>
        <li>'res'</li>
        <li>'search'</li>
        <li>'text'</li>
        <li>'admin'</li>
        <li>'plugin1'</li>
        <li>'plugin2'</li>
        <li>'plugin3'</li>
    </ul>
</li>
<li>" . dgettext($domain, "'admin' is only available when logged in as admin, 'text' will only show if there are metadata (quotes etc.), and the three 'pluginX' menu trees only show if they are populated.") . "</li>
<li>"
// translators: do not edit words prefaced with '$'
 . dgettext($domain, "\$authorize should be one of the following numerals:") . "
    <ul>
        <li>unknown " . dgettext($domain, "(always unauthorised, menu item not displayed)") . "</li>
        <li>0 " . dgettext($domain, "(menu item displayed for all users, logged in or not)") . "</li>
        <li>1 " . dgettext($domain, "(menu item displayed for users logged in with write access)") . "</li>
        <li>2 " . dgettext($domain, "(menu item displayed only for logged-in admins)") . "</li>
    </ul>
</li>
</ul>"

. dgettext($domain, "<p>Usually, you will insert a submenu into one of the pluginX menus. As a reference, a typical config.php file will look like this:</p>")
// This code should not be translated
. "<p>
<pre>
class adminstyle_CONFIG {
    public \$menus = array('plugin1');
    public \$authorize = 2;
	public $wikindxVersion = 5.8;
}
</pre>
</p>"

. dgettext($domain, "<p>Inline plugins return output that is displayed in one of four containers that can optionally be positioned in any of the template .tpl files.  To change the position of a container, you will need to edit the appropriate .tpl file.</p>")

. dgettext($domain, "<p>At least one template, one bibliographic style and one language must remain enabled. WIKINDX expects that the English language pack is available on the server (i.e. that you do not physically remove it from the wikindx/languages/ folder) whether it has been disabled or not.  This is because the English language pack is used to supply any messages that might be missing from other language packs.</p>"),
		);
		
		
		if(!isset($this->config->WIKINDX_SEARCH_FILTER)) // i.e. at first install of a blank database
			$search = "an, a, the, and, to";
		else
			$search = implode(", ", $this->config->WIKINDX_SEARCH_FILTER);
		
		$pasteBibtex = $this->session->getVar("setup_MaxPaste");
		
		$tempArray = array();
		foreach($tmpLanguageArray as $k => $v)
		{
		    $string = preg_replace("/" . preg_quote("#currentWikindxVersion#", "/") . "/", WIKINDX_PLUGIN_VERSION, $v);
		    $string = preg_replace("/" . preg_quote("#pasteBibtex#", "/") . "/", $pasteBibtex, $string);
		    $string = preg_replace("/" . preg_quote("#search#", "/") . "/", $search, $string);
		    $tempArray[$k] = $string;
		}
		
		return array("help" => $tempArray);
	}
}
