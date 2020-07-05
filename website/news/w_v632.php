<h2>Wikindx v6.3.2</h2>
<div class="hDetails">July, 2020</div>
<h3>CHANGELOG</h3>

<p><strong>Maintenance and minor feature enhancement.</strong></p>

<h3>BUGS:</h3>

<ul>
    <li>Fix an error that prevent the upgrade of components.</li>
    <li>Fix some notices when a resource without creator is displayed.</li>
    <li>Fix the BCP 47 code of to_TO locale.</li>
    <li>Temporary fix to legacy issue (to be fixed permanently on next database upgrade) – disabled resource types can be stored incorrectly leading to errors when disabling the types from Admin|Configure.</li>
    <li>Fix mismatch between search results in numerical and alphabetical mode.</li>
    <li>Add missing message about 'shortNewspaper'.</li>
    <li>Fix display of deactivated resource types in Admin|Configure.</li>
</ul>

<h3>MAINTENANCE:</h3>

<ul>
    <li>Changes the operation of the component version number so as not to mislead users.</li>
    <li>Increase memory prerequisite to 64MB.</li>
    <li>Add a notice about memory comsumption of upgrade stage 13.</li>
    <li>Add compatibility functions for higher versions of PHP (polyfill-php.php).</li>
    <li>Update jQuery from v3.3.1 to v3.5.1.</li>
    <li>Force browsers to reload JS and CSS files when a new version is installed.</li>
</ul>

<h3>FEATURE ENHANCEMENTS:</h3>

<ul>
    <li>Add possibility to quarantine resources on import.</li>
</ul>
