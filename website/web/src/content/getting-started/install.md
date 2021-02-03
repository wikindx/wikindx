+++
title = "Install"
date = 2021-01-30T00:08:41+01:00
+++


WIKINDX requires a web server environment comprising a web server (typically apache), PHP scripting, and a MySQL or MariaDB server. Most web-hosting providers can provide this configuration. Placing WIKINDX on such a remote web server means that it can be accessed from any machine and/or by multiple people. WIKINDX can also be installed locally for a single user on one desktop or laptop computer. There are a variety of packages that can be downloaded to create the required web server environment including <a href="http://www.wampserver.com/en/" target="_blank">WAMP</a> for Windows and <a href="http://www.apachefriends.org/en/xampp.html" target="_blank">XAMPP</a> for Windows/Linux/MacOS (further instructions on downloading and configuring XAMPP for MacOS are at the bottom of the page).

## In all cases, the following steps need to be taken once the web server environment is up and running.
 
The details may be slightly different (especially steps 4/ to 7/) depending on the version of phpMyAdmin that is available or whether running WIKINDX on a hosted web environment or locally but the principles are the same.

1. If you haven't already, unzip wikindx into the web server environment folder (typically 'www/' or 'httpd/') where it will create the folder 'wikindx6/' – and copy config.php.dist to config.php. Depending on your Operating System, you might be prompted at stage 8/ below to change the permissions of various folders and files.
2. By default, wikindx6/config.php has the following:

```php
// Name of the database which these scripts interface with:
$WIKINDX_DB = "wikindx6";

// Username and password required to connect to and open the database:
$WIKINDX_DB_USER = "wikindx";
$WIKINDX_DB_PASSWORD = "wikindx";
```

Assuming you won't change these, we'll use these values in phpMyAdmin. NB, the password and username are for accessing the database and not for using WIKINDX. When you first launch WIKINDX (see below), you will be asked to enter a username/password which may or may not be the same as the set above.

3. If running WIKINDX locally, ensure the web server environment (e.g. WAMP or XAMPP) is running both apache and MySQL.
4. Launch PhpMyAdmin in a web browser. There might be a link to this in your web server control panel or, if running locally, try 'http://localhost/phpmyadmin/' in the web browser address bar.
5. In the 'Databases' tab of PhpMyAdmin, type in 'wikindx6' as the name of a new database, set 'utf8mb4_unicode_520_ci' as the collation, and click 'Create'.
6. Go back to the 'Databases' tab, click on 'Check privileges' for the new database, and select 'Add user account'.
7. In the field 'User name', type in 'wikindx' and type 'wikindx' into the two password fields. If running WIKINDX locally, select 'local' for host. Check the checkbox for 'Grant all privileges on database wikindx6' then click on the 'Go' button. 
8. Type in the WIKINDX address in the web browser – if running locally, this will be http://localhost/wikindx6/ – to complete your WIKINDX configuration.

Bon voyage! 

## Installing and configuring XAMPP on MacOS

1/ Download and install XAMPP. If you know what you are doing, install the VM version from <a href="http://www.apachefriends.org/en/xampp.html" target="_blank">XAMPP</a>. Far simpler is the installer version at <a href="https://sourceforge.net/projects/xampp/files/XAMPP%20Mac%20OS%20X/" target="_blank">XAMPP's Sourceforge site</a> – NOT 'xampp-osx-xxxxx-vm.dmg' but 'xampp-osx-xxxxx-installer.dmg'.
2/ Launch the XAMPP control panel and start Apache and MySQL.
3/ Follow the steps above to install and configure WIKINDX.
4/ Each time the computer reboots, you will need to restart apache and MySQL. To avoid this, do the following as root (it's assumed you know how to use a terminal and the appropriate text editors):

* Store the following text in /Library/LaunchDaemons/apachefriends.xampp.apache.start.plist

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>Label</key>
	<string>apachefriends.xampp.apache.start</string>
	<key>ProgramArguments</key>
	<array>
		<string>/Applications/XAMPP/xamppfiles/xampp</string>
		<string>startapache</string>
	</array>
	<key>QueueDirectories</key>
	<array/>
	<key>RunAtLoad</key>
	<true/>
	<key>WatchPaths</key>
	<array/>
</dict>
</plist>
```

* Store the following text in /Library/LaunchDaemons/apachefriends.xampp.mysql.start.plist<br>

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>Label</key>
	<string>apachefriends.xampp.mysql.start</string>
	<key>Disabled</key>
	<false/>
	<key>GroupName</key>
	<string>_mysql</string>
	<key>KeepAlive</key>
	<true/>
	<key>Program</key>
	<string>/Applications/XAMPP/xamppfiles/sbin/mysqld</string>
	<key>ProgramArguments</key>
	<array>
		<string>/Applications/XAMPP/xamppfiles/sobin/mysqld</string>
		<string>--user=_mysql</string>
	</array>
	<key>QueueDirectories</key>
	<array/>
	<key>RunAtLoad</key>
	<true/>
	<key>Umask</key>
	<integer>7</integer>
	<key>UserName</key>
	<string>_mysql</string>
	<key>WatchPaths</key>
	<array/>
	<key>WorkingDirectory</key>
	<string>/Applications/XAMPP/xamppfiles</string>
</dict>
</plist>
```

* Change the owner of both files to root and the group to wheel:

* chown root:wheel apachefriends.xampp.apache.start.plist
* chown root:wheel apachefriends.xampp.mysql.start.plist

* Change the permissions for both files to 0755:

* chmod 0755 apachefriends.xampp.apache.start.plist
* chmod 0755 apachefriends.xampp.mysql.start.plist

* You can test each script with:

* launchctl load apachefriends.xampp.apache.start.plist
* launchctl load apachefriends.xampp.mysql.start.plist

After a few seconds, apache and mysql should start up. If you need to adjust paths in the plist files, you can stop the scripts with:

* launchctl unload apachefriends.xampp.apache.start.plist
* launchctl unload apachefriends.xampp.mysql.start.plist

Then start them again after editing and saving the files.

* Assuming all is well, test by rebooting your computer.


