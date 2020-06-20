<h2>Wikindx v6.3.1 Released</h2>
<div class="hDetails">June, 2020</div>
<h3>CHANGELOG</h3>

<p><strong>Focus: Quick bug fix.</strong></p>

<h3>BUGS:</h3>

<ul>
<li>Fix an error in v6.3.0 upgrade relating to components.json and an incorrect UPDATEDATABASE.php.</li>
<li>Upgrade plugins to wikindx plugin version 8.</li>
<li>Attempt to deal with a memory leak when caching large PDF files on attachment uploads.</li>
<li>As an exception to guarantee access in the event of a misconfiguration or an offline server, if the LDAP authentication of the Super Admin account fails, a second will be attempted with the native method.</li>
<li>Prevent the loading of broken plugins.</li>
<li>Force to refresh the components.json files on upgrade.</li>
<li>Catch more errors of LDAP auth.</li>
</ul>

<h3>MAINTENANCE:</h3>

<ul>
<li>Changes the operation of the component version number so as not to mislead users.</li>
<li>Increase memory prerequisite to 64MB.</li>
</ul>

<h3>SECURITY:</h3>

<ul>
<li>Update PHPMailer to 6.1.6 (CVE-2020-13625).</li>
<li>When LDAP auth is On, prevent the user to login without password.</li>
</ul>
