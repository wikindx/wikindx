<h2>Wikindx v6.4.0</h2>
<div class="hDetails">December, 2020</div>
<h3>CHANGELOG</h3>

<p><strong>Focus: minor bug fixes, major feature enhancement, and maintenance</strong></p>

<h3>IMPORTANT INFORMATION</h3>

<p>The attachment cache is cleared because their creation has been greatly improved and search results will be better.</p>

<p>This version supports php 7.3 and 7.4 only. php 8.0 support is a work in progress.</p>

<p>It is no longer possible to modify the prefix of the tables in the database. This functionality is only useful for several programs sharing the same database with the same table names or several wikindx installations in the same database. These two practices are to be avoided because they are a good way to lose your data. Each software should be isolated in its own database for privacy, security, bug resistance and ease of maintenance. Firstly, the "wkx_" prefix will be kept for some versions and cannot be changed. Secondly, it will be deleted. We believe that very few installs use this feature and we will handle it on a case-by-case basis. If you are affected by this change please contact us for help with the transition.</p>

<h3>BUGS</h3>

<ul>
    <li>When merging or deleting keywords, ensure integrity of keyword_groups is maintained.</li>
    <li>Properly handle resource attachment upload errors in all three forms.</li>
    <li>Fix a missing input error message when clicking on a metadata keyword.</li>
    <li>Fix the editing/setting of embargo dates for existing resource attachments.</li>
    <li>Fix the bibliography description edition.</li>
    <li>Fix a bug in editing keyword groups when there are no user groups.</li>
    <li>Component admin wrote empty Plugin config.php files.</li>
    <li>Display a message on failure when a component is disabled.</li>
    <li>Display a message on failure when a component is uninstalled.</li>
    <li>When copying a bibliographic style in the adminstyle plugin, ensure the appropriate component.json file is written too.</li>
    <li>Unbreak the plugin configuration.</li>
    <li>Fix a regression preventing ldap auth.</li>
    <li>When importing bibTeX or EndNote bibliographies, ensure certain fields are integers if required by the database structure.</li>
    <li>Update the locales list on each last step of an upgrade.</li>
    <li>Session was lost when visiting the login page.</li>
    <li>Properly decode the HTML entities of texts extracted from a DOCX file.</li>
    <li>Crash in news edition.</li>
    <li>Restore front page description translation functionality (bugs #211 and #228).</li>
    <li>Bibtex export : encode HTML before regular characters.</li>
    <li>Bibtex export : use file links that return appropriate HTTP code with the correct mime type.</li>
    <li>Migrate option configEmailnewRegistration to configEmailnewRegistration with several versions of delay.</li>
    <li>Ensure the session is actually destroyed on logout.</li>
    <li>Fix PHP escaping of strings in config.php migration script (#286).</li>
</ul>


<h3>FEATURE ENHANCEMENT</h3>

<ul>
    <li>When editing a form, use internal representations to repopulate the form in case of error and lessen the load on sessions. Also, restructure form management by splitting form presentation from DB data writing and use redirects to mitigate errors caused when attempting to reload a submitted form.</li>
    <li>Improve the user interface for several forms.</li>
    <li>Create the component.json file when creating or copying a style.</li>
    <li>In single resource view, added icons to toggle between quarantining/approving the resource (quarantining must be enabled in Admin|Configure|Users).</li>
    <li>When viewing a list of quarantined resources, admins can now approve resources en masse via the organize select box.</li>
    <li>Retrieve better info from an ldap user account.</li>
    <li>When adding URLs to a resource, a default URL prefix can now be defined (see Admin|Configure|Miscellaneous).</li>
    <li>All edit and add functions when viewing a single resource now have a 'Return' link to that resource.</li>
    <li>If browsing a user bibliography, use it also for the front page (which otherwise uses the master bibliography) – set in Wikindx|Bibliographies.</li>
    <li>Added the possibility to conduct various operations independently across different browser tabs/windows. Previously, data relating to searches and similar were stored in PHP sessions but these are common to all tabs/windows – searches in different tabs would make use of search data (such as search parameters, last multi search etc.) from the most recently conducted search in whatever tab. v6.4.0 makes use of javascript sessionStorage which allows for browser tabs/windows to be uniquely identified allowing search data to be unique to that search. Not all browsers support sessionStorage (a list of compatible browsers can be found here: https://developer.mozilla.org/en-US/docs/Web/API/Window/sessionStorage#Browser_compatibility or https://caniuse.com/?search=sessionStorage) so, for this reason, the feature is disabled by default in WIKINDX. To turn it on, go to Admin|Configure|Miscellaneous.</li>
    <li>Complete reimplementation of LDAP authentication. (https://sourceforge.net/p/wikindx/v5bugs/254/).</li>
    <li>Accept the webp image format because since September 2020 all browsers support it. However TinyMCE does not support it yet.</li>
    <li>Better content extractor for DOCX files.</li>
    <li>New format supported for document extractor: OpenDocument Writer (ODT).</li>
    <li>New format supported for document extractor: Rich Text Format (RTF).</li>
    <li>Send HTTP Error Code 404 for a missing download.</li>
    <li>Ignore empty sub-menus.</li>
</ul>


<h3>MAINTENANCE</h3>

<ul>
    <li>Remove the need of WIKINDX_WIKINDX_PATH and use automatically WIKINDX_DIR_BASE instead.</li>
    <li>Replace WIKINDX_BASE_URL by  WIKINDX_URL_BASE.</li>
    <li>Make PHP includes independent of the web server layout: https://sourceforge.net/p/wikindx/v5bugs/244/</li>
    <li>Bump WIKINDX_COMPONENTS_COMPATIBLE_VERSION of plugins to 9 (for includes and WIKINDX_URL_BASE).</li>
    <li>Remove the check of title and subtitle length in TinyMCE editor.</li>
    <li>Remove the type attribute of scripts elements previously mandatory in XHTML.</li>
    <li>Convert bibliography descriptions to plain text.</li>
    <li>Convert the  attachment descriptions to plain text.</li>
    <li>Always disable the SQL debug output if WIKINDX_DEBUG_SQL is not yet defined (on install).</li>
    <li>Gives the CLOSE class alone the responsibility of displaying SQL traces.</li>
    <li>Isolate some functions for enabling/disabling a component.</li>
    <li>Enable by default private bibliographic styles.</li>
    <li>No more controls over manually configuring a plugin's configuration.</li>
    <li>Uses a consistent name for the plugin load class.</li>
    <li>Delete the Amazon import plugin. This imported one resource (only books) at a time from Amazon but it is too much bother keeping up with Amazon's constant changes to the API and connection protocols.</li>
    <li>Make packaging reproducible.</li>
    <li>Drop PHP 7.0 support.</li>
    <li>Drop PHP 7.1 support.</li>
    <li>Drop PHP 7.2 support.</li>
    <li>Update PHPMailer to version 6.2.0 (PHP 8.0 support).</li>
    <li>Remove WIKINDX_DB_TABLEPREFIX: It is no longer possible to modify the prefix of the tables in the database. This functionality is only useful for several programs sharing the same database with the same table names or several wikindx installations in the same database. These two practices are to be avoided because they are a good way to lost your data. Each software should be isolated in its own database for privacy, security, bug resistance and ease of maintenance. Firstly, the "wkx_" prefix will be kept for some versions and cannot be changed. Secondly, it will be deleted.</li>
    <li>Update jpGraph from 4.2.10 to 4.3.4.</li>
    <li>Remove multistage in upgrade.</li>
    <li>enchant_broker_free() and enchant_broker_free_dict() are deprecated; unset the object instead (PHP 8.0 support).</li>
    <li>The curl_close() function no longer has an effect (PHP 8.0 support).</li>
    <li>Stop storing a copy of the session state in db and use only PHP plain session.</li>
    <li>Support PHP 8.0 for zip files.</li>
    <li>New vendor component RtfTools for RTF format handling.</li>
    <li>Keep internal version numbers in a dedicated table (#268).</li>
    <li>Update Adminer to version 4.7.8 (PHP 8.0 support).</li>
    <li>Remove table database_summary and count resources on the fly.</li>
    <li>Use an internal version for plugins.</li>
    <li>Assume the bibUtils are in the system PATH.</li>
    <li>Updated French translation.</li>
</ul>


<h3>SECURITY</h3>

<ul>
    <li>Replace crypt/hash_equals() by password_hash/password_verify() which is stronger by default.</li>
    <li>Disallow access to backups of config.php.</li>
</ul>
