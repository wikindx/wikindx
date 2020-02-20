<h2>Wikindx v6.2.1 Released</h2>
<div class="hDetails">February, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: Minor bug fixes.</strong></p>

<h3>BUGS:</h3>
<ul>
    <li>Fix the config include of dbadminer plugin.</li>
    <li>Add missing logos of templates.</li>
    <li>Fix a config error of the tag high size.</li>
    <li>Fix the type of configImagesMaxSize option in the configuration panel.</li>
    <li>Fix the type of configMailSmtpPort option in the configuration panel.</li>
    <li>Respect the font size limits of the tags cloud.</li>
    <li>Fix a saving error of the config by converting the font sizes of the tags cloud to a scalling factor.</li>
    <li>Drop the float type of the config table.</li>
</ul>


<h2>Wikindx v6.2.0 Released</h2>
<div class="hDetails">February, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: maintenance.</strong></p>

<p><strong>This version is a maintenance version only to facilitate the transition
from wikindx v5 to v6. This version remove the upgrade support of v3.8
and v4.x database (v5.1 db minimum supported).</strong></p>

<h3>MAINTENANCE:</h3>
<ul>
    <li>Drop upgrade support before v5.1.</li>
</ul>


<h2>Wikindx v6.1.0 Released</h2>
<div class="hDetails">February, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: maintenance.</strong></p>

<p><strong>This version is a maintenance version only to facilitate the transition
from php 5.6 to php 7.0. Its code is strictly identical to version 6.0.8
minus support for php 5.6, plus support for php 7.4. This version is the last
supporting php 7 and an upgrade from wikindx 3.8 to 6.</strong></p>

<h3>MAINTENANCE:</h3>
<ul>
    <li>Drop PHP 5.6 support.</li>
    <li>Add  PHP 7.4 support.</li>
</ul>


<h2>Wikindx v6.0.8 Released</h2>
<div class="hDetails">February, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: Minor bug fixes.</strong></p>

<p><strong>This version is the last supporting php 5.6 and an upgrade from wikindx 3.8 to 6.</strong></p>

<h3>BUGS:</h3>
<ul>
    <li>Fix a syntaxic error in importexportbib plugin.</li>
    <li>Fix memory leak when email resource change notifications (https://sourceforge.net/p/wikindx/v5bugs/207/).</li>
    <li>Fix memory leak when upgrading database and compiling the statistics.</li>
    <li>Fix the initialisation of configUserRegistrationModerate config variable.</li>
    <li>Fix some test error in the global configuration screen.</li>
    <li>Add a missing initialisation of configLastChangesDayLimit config variable.</li>
    <li>Add a missing initialisation of configPagingTagCloud config variable.</li>
    <li>Remove config options added by mistake (configMaxWriteChunk, configCaptchaPublicKey, configCaptchaPrivateKey, configRegistrationModerate).</li>
    <li>Fix the configRestrictUserId default value 0 to instead of FALSE.</li>
    <li>Fix the configLdapDn default value "" to instead of FALSE.</li>
    <li>Fix a character case error for option configListLink.</li>
    <li>Fix the name of the default values of global options.</li>
    <li>Fix a global option name (configSqlEmail => configDebugEmail).</li>
    <li>Fix the LDAP Server validity check.</li>
    <li>Fix a crash on a first install when the statistics are compiled without resources available.</li>
    <li>Fix the name of the default constant value of WIKINDX_CONTACT_EMAIL.</li>
    <li>Add a missing default value for WIKINDX_DEACTIVATE_RESOURCE_TYPES (configDeactivateResourceTypes).</li>
    <li>Fix the name of the default constant value of WIKINDX_EMAIL_CONTACT.</li>
    <li>Fix the name of the default constant value of WIKINDX_EMAIL_NEWREGISTRATIONS.</li>
    <li>Fix the name of the default constant value of WIKINDX_EMAIL_NEWS.</li>
    <li>Fix the name of the default constant value of WIKINDX_EMAIL_STATISTICS.</li>
    <li>Fix the name of the default constant value of WIKINDX_FILE_DELETESECONDS.</li>
    <li>Fix the name of the default constant value of WIKINDX_IMG_WIDTHLIMIT.</li>
    <li>Fix the name of the default constant value of WIKINDX_IMG_HEIGHTLIMIT.</li>
    <li>Fix the name of the default constant value of WIKINDX_METADATA_ALLOW.</li>
    <li>Fix the name of the default constant value of WIKINDX_PAGING_MAXLINKS.</li>
    <li>Fix the name of the default constant value of WIKINDX_DEBUG_SQLERROROUTPUT.</li>
    <li>Prevent errors when the config table is not yet initialized during the installation.</li>
    <li>Move the initialisation of the config table in LOADCONFIG table: this prevent misconfigured options.</li>
    <li>Logos without version number.</li>
    <li>Add missing default values for WIKINDX_BASE_URL and WIKINDX_TRUNK_VERSION options.</li>
    <li>Fix the language display and setup.</li>
    <li>Prevent a 404 HTTP error about favicon.ico when a template is not used.</li>
</ul>

<h3>FEATURE ENHANCEMENTS:</h3>
<ul>
    <li>Implement FULLTEXT searches on some database fields. In QUICKSEARCH and ADVANCED SEARCH, certain fields (abstract, notes, long custom, 
and metadata such as quotes, comments etc.) are searched on with MySQL's BOOLEAN FULLTEXT methods. This gives a significant speed gain.</li>
    <li>Always display a trace and die when a SQL query fails otherwise a debug is very hard when the debug mode is not on or during an upgrade/installation.</li>
    <li>Add an option to enable/disable the SiteMap.</li>
</ul>


<h2>Wikindx v6.0.7 Released</h2>
<div class="hDetails">January, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: Minor bug fixes.</strong></p>

<h3>BUGS:</h3>
<ul>
    <li>Fix some heredoc string opening syntax.</li>
    <li>Fix display of Register User in the Wikindx menu – under the right conditions (Admin|Configure interface), the menu item is now displayed.</li>
    <li>Fix RSS.</li>
    <li>Fix text replacement in Help files.</li>
    <li>Fix an crash during translation in the link dialog of the custom TinyMCE dialog.</li>
    <li>Remove useless instances of ENVIRONMENT class.</li>
    <li>Fix a warning in JS debugger of the browser about a missing JS map.</li>
    <li>Disable the CSS of the TinyMCE dialog in o2k7 skin because this interacts poorly with the templates CSS.</li>
    <li>Fix the encoding of TinyMCE html files.</li>
    <li>Use an absolute path for tinyMCE js if possible (unbreak templates CSS in some TinyMCE dialogs).</li>
    <li>Call LOADCONFIG class in WEBSERVERCONFIG.php which fix a hidden bug in RESOURCEMAP.php during an RTF export.</li>
    <li>Add a cache directory for common files.</li>
    <li>Fix RTF export of images (#206).</li>
</ul>


<h2>Wikindx v6.0.6 Released</h2>
<div class="hDetails">January, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: Minor bug fixes.</strong></p>

<h3>BUGS:</h3>
<ul>
    <li>Fix a non-disruptive warning when upgrading from v3.8.</li>
    <li>Fix an unnecessary GROUP BY statement in QUICKSEARCH that greatly slows down the search.</li>
    <li>Fix the migration of 5.8.2 styles when foreign files are present in style folders.</li>
    <li>Fix the migration of image links in papers.</li>
    <li>Fix the migration of image links in resources.</li>
    <li>Partial fix for the escaping of a LIKE SQL clause.</li>
    <li>Change the default language in the user config and try to fix a crash of the chooseLanguage plugin.</li>
    <li>Fix the sort order of the list of resource types in the configuration.</li>
</ul>

<h2>Wikindx v6.0.5 Released</h2>
<div class="hDetails">January, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: Minor bug fixes.</strong></p>

<h3>BUGS:</h3>
<ul>
    <li>Fix the character case of style and template options in db.</li>
</ul>


<h2>Wikindx v6.0.4 Released</h2>
<div class="hDetails">January, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: Minor bug fixes.</strong></p>

<h3>BUGS:</h3>
<ul>
    <li>If QUICKSEARCH is called by the special string $QUICKSEARCH$ on the front page, the help icon is already in use – don't 
override it with search help.</li>
    <li>Fix the character case of all style options in db.</li>
    <li>Fix the style options to lowercase to avoid empty formatting of resources when a session has a bad character style after a migration from a pre-5.9.1 version or a non-installed style.</li>
    <li>Prevent a crash when a style already defined in an option is not yet enabled in the new component system.</li>
    <li>Prevent a crash when a template already defined in an option is not yet enabled in the new component system.</li>
    <li>Prevent a crash when a language already defined in an option is not yet enabled in the new component system.</li>
    <li>Repair the image library (JS libs packaged in vendor components).</li>
    <li>Fix the encoding of the special chars dialog.</li>
    <li>Fix the special chars dialog (JS libs packaged in vendor components).</li>
</ul>


<h2>Wikindx v6.0.3 Released</h2>
<div class="hDetails">January, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: Minor bug fixes.</strong></p>

<h3>BUGS:</h3>
<ul>
    <li>When a user selects menu reduced level 2 from MyWikindx, there were fatal errors when menus with submenus were selected. Fixed.</li>
    <li>Add a missing string in English messages of collections.</li>
</ul>


<h2>Wikindx v6.0.2 Released</h2>
<div class="hDetails">January, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: Minor bug fixes and maintenance.</strong></p>

<h3>BUGS:</h3>
<ul>
    <li>Fix the return value of displayUserAddEditPlain().</li>
    <li>Add some missing messages.</li>
    <li>Fix missing variable configIsCreator in Configure.</li>
    <li>When listing resources containing resources with multiple creators, the number of resources returned as per the paging value was 
incorrect. An erroneous GROUP BY statement has been corrected.</li>
    <li>Ensure read only users have access to some configuration options (Wikindx|Preferences menu).</li>
    <li>Correct a syntax error preventing code execution under PHP 5.6 and 7.0.</li>
</ul>

<h3>MAINTENANCE:</h3>
<ul>
    <li>Remove dead code/comments.</li>
    <li>Removes the class FACTORY_GENERIC which has never been used in practice which eliminates the need for the PHP Reflection extension.</li>
    <li>Check limits of MySQL max_allowed_packet variable.</li>
    <li>Add a lot of missing function prototypes in manual.</li>
</ul>


<h2>Wikindx v6.0.1 Released</h2>
<div class="hDetails">January, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: Minor bug fixes and maintenance.</strong></p>

<h3>BUGS:</h3>
<ul>
    <li>Fix bug https://sourceforge.net/p/wikindx/v5bugs/202/ – unable to read temporary config.php when editing plugin configurations – and ensure the temporary file has a secure name.</li>
    <li>Fix JS includes of the word processor.</li>
</ul>

<h3>SECURITY:</h3>
<ul>
    <li>No longer use session_id() as a random string.</li>
</ul>

<h3>MAINTENANCE:</h3>
<ul>
    <li>Switch the project to license CC-BY-NC-SA 4.0.</li>
    <li>Add an internal version number that trigger the upgrade process.</li>
    <li>Fix a warning in the components signature script.</li>
</ul>



<h2>Wikindx v6 Released</h2>
<div class="hDetails">January, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: Minor bug fixes for 5.9.1.</strong></p>
<p>An existing database is upgraded with this release – backup the database before running the code.</p>

<h3>BUGS:</h3>
<ul>
    <li>Fix the default db value of the usersLanguage field (auto instead of en_GB) for the RepairKit.</li>
    <li>Fix some bug in the detection of index mismatchs in the RepairKit.</li>
    <li>Fix a string access error in BibTex import/export (PHP 7.4 support).</li>
    <li>Fix an error in the folder check of a component.</li>
    <li>Fix an error in the version check during an upgrade.</li>
    <li>Correction of the loading of certain configuration variables defined in the database and which have been moved from the config.php file.</li>
    <li>Fix the migration of the word processor papers.</li>
    <li>Move all component folders to a "components" sub-folder.</li>
    <li>Fix the loading of styles.</li>
    <li>Fix escaping of values in multiUpdate() function.</li>
    <li>Fix the values updated by the  multiUpdate() function.</li>
    <li>Fix a wrong array access in upgrade of 5.8.1.</li>
    <li>Fix the style loading during a setup or when there are only the default style available after an upgrade.</li>
    <li>Fix the query of contact email during the upgrade.</li>
</ul>

<h3>MAINTENANCE:</h3>
<ul>
    <li>Enable the check of plugins version.</li>
    <li>Change the databasesummaryDbVersion field to databasesummarySoftwareVersion and the notion of minor/major upgrade/version.</li>
</ul>



<h2>Wikindx v5.9.1 Released</h2>
<div class="hDetails">January, 2020</div>
<h3>CHANGELOG</h3>
<p><strong>Focus: Minor bug and security fixes and major feature enhancements.</strong></p>
<p>An existing database is upgraded with this release – backup the database before running the code.</p>

<h3>DEPRECATED:</h3>
<ul>
<li>The database table prefix (wkx_) is kept but its configuration for users
through the $WIKINDX_DB_TABLEPREFIX variable will be removed at the next major
delivery. People who have changed the prefix should rename the tables with the
default prefix and correct their configuration. It will no longer be possible to
install two WIKINDXs in the same database. If you are in this rare case contact us.</li>
<li>PHP 5.6 support is deprecated and will be dropped in favor of PHP 7.0.
WIKINDX 5.9.1 is the last version supporting it.</li>
</ul>

<h3>BUGS:</h3>
<ul>
<li>Fix for https://sourceforge.net/p/wikindx/v5bugs/169/ (warning on missing array when viewing a resource).</li>
<li>Fix some warnings related to sessions [https://sourceforge.net/p/wikindx/v5bugs/170/].</li>
<li>When exporting a basket with the importExport plugin (to bibTeX, RTF etc.), sometimes the last multi view was exported instead.</li>
<li>When browsing metadata keywords, a fatal error occurred if a keyword was not attached to resources but only attached to ideas.</li>
<li>Fix resource edit warning noted in https://sourceforge.net/p/wikindx/v5bugs/166/.</li>
<li>In user Preferences, ensure the value of 'Default no. paging links to display/screen' is a minimum of 4.</li>
<li>In user Preferences, ensure the language is get from the database when the chooseLanguage plugin is enabled.</li>
<li>Disable enchant on Windows because pspell is not installable and restore spell checking for others OS [https://sourceforge.net/p/wikindx/v5bugs/24/].</li>
<li>Ensure a basket of resources can comprise just one resource.</li>
<li>Fixed inability of new users to register before session data had been loaded.</li>
</ul>

<h3>FEATURE ENHANCEMENTS:</h3>
<ul>
<li>In Admin|Components, a remote server may now be queried in order to get new plugins or to update existing plugins,
styles, templates, and languages (only those that are enabled in the Admin|Components interface will be updated). An Internet connection
is required. When updating existing plugins, user edits to the config.php file  are maintained (e.g. the plugin's menu placement) and
those plugins that are disabled will not be updated. This system requires that the variable $wikindxVersion of each plugin's config.php
file be set to 5.9. This assumes that $wikindxVersion was previously 3 and that the plugins are in fact the latest from the Sourceforge
server (https://sourceforge.net/projects/wikindx/files/plugins%20wikindx%20v5.x/). This is a one-off requirement when upgrading to
WIKINDX 5.9.1 in order to get the plugin versions in sync. If $wikindxVersion is not 5.8 for any plugin after installing WIKINDX 5.8.3,
then that plugin will not be visible to users even if enabled in the Admin|Components interface – you will need to update your plugins.
Following the official release of WIKINDX 5.9.1, the latest plugins, styles, templates, and languages will only be available through
the Admin|Configure interface. In order to download and install components, the following directories and subfolders and files must be
writeable by the web user:
plugins/, templates/, styles/, and languages/ [https://sourceforge.net/p/wikindx/v5bugs/172].</li>
<li>Increase the minimum PHP version needed for the core to version 5.6.0. This version will be EOL at the end of 2020.</li>
<li>Migration of the localisation system to gettext [https://sourceforge.net/p/wikindx/v5bugs/139/].</li>
<li>Partial support of PHP 7.4 (some warnings can appear if you use the debug mode, dbAdminer and Visualize plugins, RTF export or PDF
to Text features) [https://sourceforge.net/p/wikindx/v5bugs/173].</li>
<li>Be able to use the language sent by the browser [https://sourceforge.net/p/wikindx/v5bugs/80/].</li>
<li>Improved interface for Wikindx|Bibliographies.</li>
<li>Configure the user's preferred locale in addition to the language (without separating these two notions for the moment).</li>
<li>Remove the need of disabling ONLY_FULL_GROUP_BY sql mode and don't force it anymore (ONLY_FULL_GROUP_BY is the default since MySQL 5.7)
[https://sourceforge.net/p/wikindx/v5bugs/68/].</li>
<li>Merge and improve the Wikindx|Preferences and Wikindx|My Wikindx interfaces so that the interface now matches Admin|Configure.</li>
<li>Cache, data, and third-party software folders are separated into "cache", "data", and "vendor" folders for easy maintenance. On upgrading
the database, copying of existing files is carried out automatically (the admin is informed of this after the upgrade).
- The "cache" folder contains data that can be deleted and will be recreated by the application.
- The "data" folder contains additional information such as attachments, images, and user data that must be saved in the same way as the database.
- The "vendor" folder contains the third-party software libraries used by the WIKINDX core to facilitate their update.
[https://sourceforge.net/p/wikindx/v5bugs/171/].</li>
<li>With the new folder structure, remove the options in config.php to configure attachments and files locations.</li>
</ul>

<h3>SECURITY:</h3>
<ul>
<li>Migrate the Wikindx official website to HTTPS.</li>
<li>Remove options to set a session location ($WIKINDX_SESSION_PATH_CLEAR, WIKINDX_SESSION_PATH).</li>
</ul>

<h3>MAINTENANCE:</h3>
<ul>
<li>Update progressbar.js from 1.0.1 to 1.1.0.</li>
<li>Update of PHPMailer to 6.1.4 version.</li>
<li>Update Smarty from 3.1.23 to 3.1.34-dev-7 : PHP 7.3 and 7.4 compatibility.</li>
<li>Remove template.js from templates. Popups are not handled like that anymore and the browser detection of this file is not reliable.</li>
<li>Remove aside.txt option from templates.</li>
</ul>

<p><a href="https://sourceforge.net/p/wikindx/code/HEAD/tree/wikindx/tags/5.9.1/CHANGELOG.txt" target="_new">FULL CHANGELOG</a></p>