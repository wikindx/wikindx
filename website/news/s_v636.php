<h2>Wikindx v6.3.6</h2>
<div class="hDetails">July, 2020</div>
<h3>CHANGELOG</h3>

<p><strong>Minor bug fixes and maintenance.</strong></p>

<p>The major change in this version is an improvement in the update server which delivers components shared between versions of the software and minimizes their maintenance. Each type of component now has a version of compatibility with the core to satisfy. Unofficial components will need to be updated.</p>

<h3>BUGS:</h3>

<ul>
    <li>When adding a plain/txt attachment to a resource, ensure it is copied to the cache directory so it can be searched from Advanced search.</li>
    <li>In Advanced search, correct a path when zipping attachments.</li>
    <li>Minor correction APA style for unpublished works.</li>
</ul>

<h3>MAINTENANCE:</h3>

<ul>
    <li>Update PHPMailer to 6.1.7.</li>
    <li>Add a component compatible version for each type of component.</li>
</ul>
