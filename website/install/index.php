<p>WIKINDX requires a web server environment comprising an httpd server (typically apache), PHP scripting, and a MySQL or MariaDB server. Most web-hosting providers can provide this configuration. Placing WIKINDX on such a remote web server means that it can be accessed from any machine and/or by multiple people. WIKINDX can also be installed locally for a single user on one desktop or laptop computer. There are a variety of packages that can be downloaded to create the required web server environment including <a href="http://www.wampserver.com/en/" target="_blank">WAMP</a> for Windows and <a href="http://www.apachefriends.org/en/xampp.html" target="_blank">XAMPP</a> for Windows/Linux/MacOS (further instructions on downloading and configuring XAMPP for MacOS are at the bottom of the page).</p>

<h3>In all cases, the following steps need to be taken once the web server environment is up an running.</h3>
 
<p>The details may be slightly different depending on the version of phpMyAdmin that is available or whether running WIKINDX on a hosted web environment or locally but the principles are the same.</p>

<p>1/ If you haven't already, unzip wikindx into the web server environment folder (typically 'www/' or 'httpd/') where it will create the folder 'wikindx6/' – and copy config.php.dist to config.php.</p>
<p>2/ By default, wikindx6/config.php has the following:</p>
<p>// name of the database which these scripts interface with: <br>
  $WIKINDX_DB = &quot;wikindx6&quot;; <br>
  // username and password required to connect to and open the database: <br>
  $WIKINDX_DB_USER = &quot;wikindx&quot;; <br>
  $WIKINDX_DB_PASSWORD = &quot;wikindx&quot;; </p>
<p>Assuming you won't change these, we'll use these values in phpMyAdmin. NB, the password and username are for accessing the database and not for using WIKINDX. When you first launch WIKINDX (see below), you will be asked to enter a username/password which may or may not be the same as the set above. </p>
<p>3/ If running WIKINDX locally, ensure the web server environment (e.g. WAMP or XAMPP) is running both apache and MySQL.</p>
<p>4/ Launch PhpMyAdmin in a web browser. There might be a link to this in your web server control panel or, if running locally, try 'http://localhost/phpmyadmin/' in the web browser address bar.</p>
<p>5/ In the 'Databases' tab of PhpMyAdmin, type in 'wikindx6' as the name of a new database, set 'utf8mb4_unicode_520_ci' as the collation, and click create.</p>
<p>6/ Go back to the 'Databases' tab, click on 'Check privileges' for the new database, and select 'Add user account'.</p>
<p>7/ In the field 'User name', type in 'wikindx' and type 'wikindx' into the two password fields. If running WIKINDX locally, select 'local' for host. Check the checkbox for 'Grant all privileges on database wikindx6' then click on the 'Go' button. </p>
<p>8/ Type in the WIKINDX address in the web browser – if running locally, this will be http://localhost/wikindx6/ – to complete your WIKINDX configuration.</p>
<p>Bon voyage! </p>

<h3>Installing and configuring XAMPP on MacOS</h3>
<p>1/ Download and install XAMPP. If you know what you are doing, install the VM version from <a href="http://www.apachefriends.org/en/xampp.html" target="_blank">XAMPP</a>. Far simpler is the installer version at <a href="https://sourceforge.net/projects/xampp/files/XAMPP%20Mac%20OS%20X/" target="_blank">XAMPP's Sourceforge site</a> – NOT 'xampp-osx-xxxxx-vm.dmg' but 'xampp-osx-xxxxx-installer.dmg'.</p>
<p>2/ Launch the XAMPP control panel and start Apache and MySQL.
<p>3/ Follow the steps above to install and configure WIKINDX.</p>
<p>4/ Each time the computer reboots, you will need to restart apache and MySQL. To avoid this, do the following as root (it's assumed you know how to use a terminal and the appropriate text editors):
<ol>
<li>Store the following text in /Library/LaunchDaemons/apachefriends.xampp.apache.start.plist<br>
<pre><code>
&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd"&gt;
&lt;plist version="1.0"&gt;
&lt;dict&gt;
	&lt;key&gt;Label&lt;/key&gt;
	&lt;string&gt;apachefriends.xampp.apache.start&lt;/string&gt;
	&lt;key&gt;ProgramArguments&lt;/key&gt;
	&lt;array&gt;
		&lt;string&gt;/Applications/XAMPP/xamppfiles/xampp&lt;/string&gt;
		&lt;string&gt;startapache&lt;/string&gt;
	&lt;/array&gt;
	&lt;key&gt;QueueDirectories&lt;/key&gt;
	&lt;array/&gt;
	&lt;key&gt;RunAtLoad&lt;/key&gt;
	&lt;true/&gt;
	&lt;key&gt;WatchPaths&lt;/key&gt;
	&lt;array/&gt;
&lt;/dict&gt;
&lt;/plist&gt;
</code></pre></li>
<li>Store the following text in /Library/LaunchDaemons/apachefriends.xampp.mysql.start.plist<br>
<pre><code>
&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd"&gt;
&lt;plist version="1.0"&gt;
&lt;dict&gt;
	&lt;key&gt;Label&lt;/key&gt;
	&lt;string&gt;apachefriends.xampp.mysql.start&lt;/string&gt;
	&lt;key&gt;Disabled&lt;/key&gt;
	&lt;false/&gt;
	&lt;key&gt;GroupName&lt;/key&gt;
	&lt;string&gt;_mysql&lt;/string&gt;
	&lt;key&gt;KeepAlive&lt;/key&gt;
	&lt;true/&gt;
	&lt;key&gt;Program&lt;/key&gt;
	&lt;string&gt;/Applications/XAMPP/xamppfiles/sbin/mysqld&lt;/string&gt;
	&lt;key&gt;ProgramArguments&lt;/key&gt;
	&lt;array&gt;
		&lt;string&gt;/Applications/XAMPP/xamppfiles/bin/mysqld&lt;/string&gt;
		&lt;string&gt;--user=_mysql&lt;/string&gt;
	&lt;/array&gt;
	&lt;key&gt;QueueDirectories&lt;/key&gt;
	&lt;array/&gt;
	&lt;key&gt;RunAtLoad&lt;/key&gt;
	&lt;true/&gt;
	&lt;key&gt;Umask&lt;/key&gt;
	&lt;integer&gt;7&lt;/integer&gt;
	&lt;key&gt;UserName&lt;/key&gt;
	&lt;string&gt;_mysql&lt;/string&gt;
	&lt;key&gt;WatchPaths&lt;/key&gt;
	&lt;array/&gt;
	&lt;key&gt;WorkingDirectory&lt;/key&gt;
	&lt;string&gt;/Applications/XAMPP/xamppfiles&lt;/string&gt;
&lt;/dict&gt;
&lt;/plist&gt;
</code></pre></li>
<li>Change the owner of both files to root and the group to wheel:
<ul>
<li>chown root:wheel apachefriends.xampp.apache.start.plist</li>
<li>chown root:wheel apachefriends.xampp.mysql.start.plist</li>
</ul></li>
<li>Change the permissions for both files to 0755:
<ul>
<li>chmod 0755 apachefriends.xampp.apache.start.plist</li>
<li>chmod 0755 apachefriends.xampp.mysql.start.plist</li>
</ul></li>
<li>You can test each script with:
<ul>
<li>launchctl load apachefriends.xampp.apache.start.plist</li>
<li>launchctl load apachefriends.xampp.mysql.start.plist</li>
</ul>
After a few seconds, apache and mysql should start up. If you need to adjust paths in the plist files, you can stop the scripts with:
<ul>
<li>launchctl unload apachefriends.xampp.apache.start.plist</li>
<li>launchctl unload apachefriends.xampp.mysql.start.plist</li>
</ul>
then start them again after editing and saving the files.</li>
<li>Assuming all is well, test by rebooting your computer.</li>

</ol>