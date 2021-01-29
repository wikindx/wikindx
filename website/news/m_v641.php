<h2>Wikindx v6.4.1</h2>
<div class="hDetails">January, 2021</div>
<h3>CHANGELOG</h3>

<p><strong>Focus: minor bug fixes, maintenance, security</strong></p>

<h3>IMPORTANT INFORMATION</h3>

<p>The security vulnerability is critical. We advise to update quickly.</p>


<h3>BUG FIXES</h3>

<ul>
    <li>Typo in changelog.
    <li>Field userkgusergroupsUserGroupId in table user_kg_usergroups should not accept NULL on first install.</li>
    <li>Fix [#304] (missing input when clicking on Quarantine in Admin menu).</li>
    <li>Syntax error in TinyMCE spellChecker code.</li>
    <li>Syntax errors in API doc.</li>
    <li>Remove some leftover print_r() statements.</li>
    <li>Fix a missing icon when deleting a resource.</li>
    <li>Correct totals when paging through the front page list.</li>
    <li>Fix a missing input error in bibliographic/citation styles (bugs [#249] & [#305]).</li>
    <li>Fix a crash on upgrade of step 34 for version 6.4.0. MariaDB and/or MySQL engines don’t support to fill, drop, and create a table in a single transaction.</li>
    <li>Vendor components cannot be enabled/disabled.</li>
    <li>Ensure that user database rows are universally deleted when deleting a user.</li>
    <li>Ensure that logged-in users can attach resources to user tags.</li>
    <li>Ensure that logged-in users can only edit a resource's categories, keywords, and languages if they own the resource or allowed to by the superadmin.</li>
    <li>Add a default value to the users.usersFullname field [#316].</li>
</ul>


<h3>MAINTENANCE</h3>

<ul>
    <li>The curl_close() function no longer has an effect (PHP 8.0 support) [#265].</li>
    <li>Update future TinyMCE (5.6.2).</li>
    <li>Add browserTabID functionality to the home page.</li>
    <li>Update Smarty (v3.1.38).</li>
    <li>Update style xml files for locales [#308].</li>
    <li>Bump component compatibility version of styles to 5.</li>
    <li>Fix some typos [#310].</li>
    <li>Simplify MySQL/MariaDB version query.</li>
    <li>Full Ukranian translation (thanks to Yuri Chornoivan).</li>
    <li>Set utf8mb4_unicode_520_ci as the default collation of the database.</li>
    <li>Improve browserTabID functionality for single resource views.</li>
    <li>Credits of translators.</li>
</ul>


<h3>SECURITY</h3>

<ul>
    <li>A cross-site scripting (XSS) vulnerability in many forms of version 6.4.0 allows remote attackers to inject arbitrary web script or HTML via the 'message’ parameter (CVE-2021-3340, thanks to jppuetz).</li>
</ul>
