<h2>Wikindx v6.3.0 Released</h2>
<div class="hDetails">May, 2020</div>
<h3>CHANGELOG</h3>

<p><strong>Focus: Minor bug fixes and feature enhancements and maintenance.</strong></p>

<h3>BUGS:</h3>

<ul>
<li>Fix SQL errors on insert citation.</li>
<li>Fix SQL errors on listing citing resources when viewing a single resource.</li>
<li>Fix display of user bibliography details when viewing a single resource.</li>
<li>Fix SQL error in QUICKSEARCH resulting from a NULL id.</li>
<li>Fix an error of version check in the repairKit.</li>
<li>When loading the basic configuration, only check email settings if email is enabled.</li>
<li>Fix the type of the default value of WIKINDX_MAIL_RETURN_PATH to a string and fix the current config.</li>
<li>Fix the default smtp encryption method (after a migration).</li>
<li>Fix the default smtp backend (after a migration).</li>
<li>Fix the type of the default value of WIKINDX_AUTHGATE_MESSAGE to a string and fix the current config.</li>
<li>Fix accessors to config variables of array type (#149).</li>
<li>Handle a warning in CLI mode about HTTP_HOST.</li>
<li>Fix a warning of smarty when the contactEmail is empty.</li>
<li>Fix the display of the password size requirement.</li>
<li>Bug 214 (https://sourceforge.net/p/wikindx/v5bugs/214/) – allow forward slashes in searches.</li>
<li>Ensure the superadmin can edit all quotes and paraphrases (original behaviour that seems to have dropped away).</li>
<li>Fix user creation in LDAP context.</li>
<li>Fix duplication of username in user creation.</li>
<li>Corrected templates – if read-only users are allowed to view attachments (see Admin|Configure), then display attachments when viewing a single resource.</li>
<li>Remove statistical lists and ordering (by views, downloads, and popularity indices) as these take too long to compile with large databases.</li>
<li>Fix the default value of usersTemplateMenu (#217).</li>
<li>Fix the type mismatch bool/Varchar(N) of WIKINDX_LIST_LINK (#216).</li>
<li>Fix type mismatch in users options (#218).</li>
<li>Fix utf8 encoding of two characters that triggers errors during the manual building.</li>
<li>Fix some errors in user administration/preferences.</li>
<li>Plugged some memory leaks particularly virulent for large databases.</li>
<li>Missing message (https://sourceforge.net/p/wikindx/v5bugs/224/).</li>
<li>Admin|Images – fix a bug that causes images (with spaces in their names) to be displayed as NOT used in metadata when they are.</li>
<li>Fix the setting of language/locale once and for all...</li>
<li>Ensure the selected bibliography is taken account of when doing a Quick list all...</li>
<li>Fix a potential SQL error with the use of single quotes in the search field of QUICKSEARCH and Advanced Search.</li>
<li>Fix some housekeeping statistics generated at the start of each month.</li>
<li>Some fixes to user notifications regarding resources added/edited.</li>
<li>Ensure specific emails (news and resource notifications) are properly decoded for UTF-8.</li>
<li>Fix quicksearch bug (https://sourceforge.net/p/wikindx/v5bugs/226/).</li>
<li>Fix an error encoded URLs when paging ideas.</li>
</ul>

<h3>MAINTENANCE:</h3>

<ul>
<li>Remove the upgrade code before version 5.1.</li>
<li>Remove the PHP mail() function email backend.</li>
<li>Remove the option to send erroneous SQL requests by email.</li>
<li>Change PHP error level to E_ALL in preparation for PHP 8 support (#186).</li>
<li>Rename constants for clarity.</li>
<li>Remove duplicate configuration checks.</li>
<li>Don't force email ReplyTo to a default wrong value. This option is facultative.</li>
<li>Don't force email From to a default wrong value. This option is facultative if the SMTP don't validate the sender.</li>
<li>Restrict the LDAP protocol to authorized values.</li>
<li>Relocate WIKINDX_TRUNK_VERSION option in the database and rename it WIKINDX_IS_TRUNK.</li>
<li>Change the type of the timezone option from TEXT to Varchar.</li>
<li>Add WIKINDX_PATH_AUTO_DETECTION option to config.php to explicitly control auto-detection of the path and base url.</li>
<li>Drop usersChangePasswordTimestamp column from the DB schema.</li>
<li>DB upgrade – transfer data from statistics, resource_misc and resource_attachments tables to new statistics_resource_views and statistics_attachment_downloads tables and drop statistics table and other relevant columns.</li>
<li>Add a UNIQUE constraint on usersUsername column (#219).</li>
<li>Fix the default value of users table.</li>
<li>Disable display of views index, popularity index and downloads index in single resource views and resource lists and disable ability to order by these in Quick list all... operations. On large databases, the compilation of the indices takes too long. At a later stage such indices will be available in the Statistics menu.</li>
<li>Update of PHPMailer to 6.1.5 version.</li>
<li>Remove superAdmin user configuration from Admin|Configure interface. superAdmin details can be edited from My Wikindx and from Admin|Users|Edit.</li>
<li>Update adminer to 4.7.7 (https://github.com/vrana/adminer/releases/tag/v4.7.7).</li>
</ul>

<h3>FEATURE ENHANCEMENTS:</h3>

<ul>
<li>When the admin edits a user, a checkbox allows for the bypassing of the password integrity check, removing the need to enter the password again.</li>
<li>Added the option to override CSS styles globally. This is linked to in components/template/xxxx/display.tpl and allows the overriding of any CSS in the templates' own .css files. Custom template designers should add:
<link rel="stylesheet" href="components/templates/override.css" type="text/css">
in display.tpl following the template.css link and create a components/templates/override.css in which the global CSS is placed.</li>
<li>In the Metadata menu, keyword browsing can now be fine-tuned according to metadata type.</li>
<li>When adding/editing a resource, ensure the title and subtitle textareas are locked to plain text. This stops the insertion of HTML code and images etc.</li>
<li>Add a guard to disable the LDAP auth when the PHP LDAP extension is not enabled.</li>
</ul>
