<p>Users wanting to run WIKINDX as a single user on their desktop or laptop may be interested in <a href="http://www.wampserver.com/en/" target="_blank">WAMP</a> or <a href="http://www.apachefriends.org/en/xampp.html" target="_blank">XAMPP</a>. </p>

<h3>Simple instuctions on configuring WAMP to work with WIKINDX. (XAMPP installation is much the same.)</h3>
 
<p>The details may be slightly different depending on the version of phpMyAdmin that comes with the WAMP version you have but the principles are the same.</p>

<p>1/ If you haven't already, unzip wikindx into the wamp/www/ folder – where it will create the folder 'wikindx5/' – and copy config.php.dist to config.php.</p>
<p>2/ By default, wikindx5/config.php has the following:</p>
<p>// name of the database which these scripts interface with: <br>
  $WIKINDX_DB = &quot;wikindx5&quot;; <br>
  // username and password required to connect to and open the database: <br>
  $WIKINDX_DB_USER = &quot;wikindx&quot;; <br>
  $WIKINDX_DB_PASSWORD = &quot;wikindx&quot;; </p>
<p>Assuming you won't change these, we'll use these values in phpMyAdmin. NB, the password and username are for accessing the database and not for using WIKINDX. When you first launch WIKINDX (see below), you will be asked to enter a username/password which may or may not be the same as the set above. </p>
<p>3/ Start WAMP from the start menu and launch PhpMyAdmin from the WAMP services icon. </p>
<p>4/ On the front page, type in 'wikindx5' as the name of a new database and hit create. </p>
<p>5/ Don't do anything on the next page except click on the link at the top called 'localhost' then click on the privileges link and select 'add a new user'. 'localhost' is the name of the local (i.e. not remote) host that WAMP runs using the Apache web server. </p>
<p>6/ In the field 'User Name', type in 'wikindx' and type 'wikindx' into the two password fields and select 'local' for host. Don't change anything else then click on the 'Go' button. </p>
<p>7/ On the next page, find the section named database-specific privileges and select the database 'wikindx5'. </p>
<p>8/ On the next page, click on the 'Check All' link to select all the boxes then click on the 'Go' button. </p>
<p>9/ Start a web browser and go to: <br>
  http://localhost/wikindx5/ <br>
  to complete your WIKINDX configuration. <br>
</p>
<p>Bon voyage! </p>