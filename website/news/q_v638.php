<h2>Wikindx v6.3.8</h2>
<div class="hDetails">August, 2020</div>
<h3>CHANGELOG</h3>

<p><strong>Minor bug fixes, maintenance, and feature enhancements.</strong></p>

<h3>BUGS:</h3>

<ul>
    <li>Correct a path for a file icon.</li></li>
    <li> For the special restricted user, ensure they cannot change their user details (inc. password) in MyWikindx.</li>
    <li>Tidied up display of MyWikindx.</li>
    <li>Fix a duplicate declaration of default value for db users.usersFullname field.</li>
    <li>Update the repairkit schema.</li>
    <li>Prevent a crash when the database is empty and the userId is known by the session.</li>
    <li>Prevent a crash with a negative result when full-text searching attachments (Advanced search).</li>
    <li>When full-text searching attachments (Advanced search), ensure all attachments with the search term are presented for each resource.</li>
    <li>When browsing resources or listing some by various parameters, ensure the displayed list parameters are maintained through reordering operations.</li>
    <li>Fix a missing input message when the last multi list is Metadata:Keyword.</li>
    <li>Fix for web server crash with pasted images: https://sourceforge.net/p/wikindx/v5bugs/239/</li>
    <li>Guard against null resource IDs in the total list when executing some searches.</li>
    <li>Correct 'Resources citing this' link when viewing a single resource.</li>
    <li>When browsing cited creators, ensure cited resources without creators do not cause an error.</li>
    <li>Ensure there is no option to add bookmarks when viewing the front page.</li>
    <li>When deleting resources from within a list (i.e. using the select box to delete set resources), ensure that the new display of the list has the corrected number of resources and the correct paging links. Equally, if all resources in the list are deleted, return to the FRONT page.</li>
    <li>When browsing metadata keywords, clicking on the Reorder button produced and error: fixed.</li>
</ul>

<h3>FEATURE ENHANCEMENTS:</h3>

<ul>
    <li>Remove the MySql persistent connection option (WIKINDX_DB_PERSISTENT). Experienced users who know how to use this tricky option can still use the host's 'p:' syntax.</li>
    <li>Remove unnecessary serialize/base64 combinations when dealing with session arrays.</li>
</ul>

<h3>FEATURE ENHANCEMENTS:</h3>

<ul>
    <li>Added a keyword group feature. Keyword groups allow the individual user to group semantically similar keywords together so the resources or metadata can then be browsed together. To create a keyword group, at least two keywords must be in the database. A keyword group is private to the user who created it but the ability to browse them can be shared with one or more user groups (created in Wikindx|MyWikindx). When creating a keyword group, adding one or more user groups is not required, likewise the keyword group description: the name and at least two keywords, though, are required. Once created, the resources belonging to all of the keywords in the group can be browsed in Search|Browse... and metadata can be browsed in the Metadata menu.</li>
</ul>
