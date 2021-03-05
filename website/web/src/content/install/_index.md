+++
title = "Install & Upgrade"
date = 2021-01-30T00:08:41+01:00
weight = 1
chapter = true
+++


Wikindx is a web application written in PHP and is intended to be OS independent.

This chapter will explain how to install and upgrade it, and what are the requirements for proper operation.

Support is provided through SourceForge's project tracking tools:
[forum](https://sourceforge.net/p/wikindx/discussion/),
[bug tracker](https://sourceforge.net/p/wikindx/v5bugs/),
[mailing list](https://sourceforge.net/p/wikindx/mailman/).


A test database ([`demo_wikindx_database.sql`](../demo_wikindx_database.sql)) is provided.

Use PhpMyAdmin (or similar) to create a database and
add a username/password to it then import the file
demo_wikindx_database.sql. Add the database name and
username/password to wikindx/config.php and then run WIKINDX.

Three users (username/password):

 * Administrator -- super/superW!k1ndx
 * user1/user1W!k1ndx
 * user2/user2W!k1ndx

There are 83 resources entered with categories, keywords, abstracts,
notes and metadata (quotations, paraphrases, musings) and at least two
resources per resource type.

user2 has a private bibliography. There is a user group which has two
members (super and user1) and which has a private group bibliography
(superBibliography).

Some maturity indices have been set and there are some popularity
ratings/number of views.

No attachments have been added to any resource.














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


    ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

                           --o INSTALL o--

    ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

/////////////////////////////////////////////////////////////////////////////
For upgrading from a previous installation of WIKINDX, read docs/UPGRADE.txt.
/////////////////////////////////////////////////////////////////////////////

Simple installation instructions (help for WIKINDX use is included in
the system and is accessed through the web browser).

REQUIREMENTS:

1/ You must have the ability to create a database and grant permissions
(GRANT ALL) on a MySQL server.

2/ Read/Write access to your web server's documents folder.

3/ (It is assumed you have a working MySQL database server and
PHP-enabled web server.)

4/ Any operating system capable of running the above.

5/ Windows users wishing to run WIKINDX as a single user on a desktop
machine, may be interested in the XAMPP or WTServer servers which are a
one-step install of Apache/PHP/MySQL. Instructions can be found at
https://wikindx.sourceforge.io/.

NB:

1/ WIKINDX will neither create the database nor grant appropriate
permissions (GRANT ALL) - you must do this manually and save a copy of
config.php.dist as config.php and edit it with the MySQL access data.
PHPMyAdmin users (for Windows users, this utility for managing MySQL
databases comes with the server) can find instructions for this at
https://wikindx.sourceforge.io/.

2/ Some PHP distributions (notably on Linux Mandriva) come without PHP
extensions that are standard on other distributions. Importantly, the
GD, mbstring, and XML extensions must be enabled on Mandriva (and
possibly other Linux distributions).

3/ The CURL PHP extension is not mandatory in WIKINDX but is used in
some circumstances. For example, from v5.9.1, components such as plugins
and styles can be managed from the _Admin > Components_ interface if CURL
is installed – without CURL, components must be installed manually by
downloading them from the Sourceforge server. It's not recommend to
disable CURL and manage components by hand.

4/ The standard PHP/Apache installation is typically sufficient to
run WIKINDX. However, there are some instances (PHP/Apache installations
with particular configurations or extras, for example) where this memory
limit may need to be increased. An indication of this is typically an
unexpected blank page following a WIKINDX operation or, if error
reporting is turned on, an error message detailing a lack of memory. If
either of these symptoms occur, increase php.ini's memory_limit in steps
of 4 Megabytes until it is working again.

5/ If your database is over 1500 resources and you expect to export
(with the importexport plugin) lists of resources of at least this
length, then you should set public $WIKINDX_MEMORY_LIMIT = "64M"; in
config.php in order to avoid memory allocation errors.

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

INSTALLATION:

1/ Unzip WIKINDX into a new folder on your web server -- it will create
a wikindx/ folder.

2/ Create a new MySQL database (e.g. 'wikindx5') and GRANT ALL
permissions on it to your wikindx user.

3/ Copy wikindx/config.php.dist to wikindx/config.php and edit the
latter (a lot of configuration options are accessible via the web
browser interface once installation is complete but, as a minimum, you
will need to set the MySQL database access protocols).

4/ Ensure that
'cache/',
'data/',
'components/plugins/',
'components/templates/',
'components/styles/',
'components/vendor/'

and the files and folders within these folders are readable and writable
(and in some case execution for PHP) by the web server user (usually
'nobody', 'www-data', or 'daemon').

So you have to modify, the owner, the group and the permission bits
according to the particular configuration of your web server, PHP and
file transfer software.  You may also be required to add the execution
bit in certain cases. The same rights apply to files in these folders.
See the chmod, web server and PHP manuals for details.

If you are running WIKINDX locally on Windows (using something like
XAMPP), you can skip this step as the folders will be writable by
default.

IIS has not been tested and uses the Windows permissions model.  No
control is done in this case.

The following commands should give a good result in the general case
where the webserver user should be "user" or "group" (760, 670 or 770
should work):

  cd my_website_root_folder
  chown -R user:group *
  chmod -R 770 *

As a last resort you can use the 777 mode but it is a major security
flaw.  You don't have to get to this end if the owners are set up
correctly.

When your web site code is a clone of the SVN repository (not
recommended) the owner of the root SVN folder, .svn folders, and all
files and folders under .svn need to be readable, writable and
executable (7).

Wikindx create folders with 777 permissions so that it works in all
cases, notably the installation of the core and components, for managing
caches and data. You can correct it later if you want the best possible
security.

WARNING: if you started to install without taking into account the
persmissions you may find yourself blocked with a blank page because of
the template system which creates incomplete cached files. Each time you
configure permissions, delete all the files and folders found under the
cache/templates/tree.

5/ If you want an embeded spell checker, you need to enable enchant
extension in your php.ini or .htaccess file. See your webserver manual.

6/ Finally, run v6 through your web browser (http://<server>/wikindx)
and follow the instructions there to complete the installation.

7/ Have fun!

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

NOTE:

1/ The data/files/ directory is used for the temporary storage of RTF,
RIS, Endnote, BibTeX and other files for the user to download. The
scripts within WIKINDX will mark these files for deletion after so many
seconds have passed since their last modification (you can configure
this through the web browser) . This doesn't necessarily mean that they
will be immediately deleted: they will be deleted the next time someone
exports a file.

2/ Since v6.3.6 language packs are builtin.

3/ Bibliographic style packs are available as separate downloads from
the sourceforge site. They act like plug-ins so simply extract them
(with their directory structure) to the components/styles/ directory to
make them instantly available. NB - to be able to edit them, ensure the
XML files are writeable by the web server user.

4/ Other plug-in modules, extending the functionality of WIKINDX, are
available from the sourceforge site. Unzip these with their directory
structure to components/plugins/. v3.x plug-ins will not work with
WIKINDX v5.  To allow plug-ins to be administered from within WIKINDX,
ensure that each plugin's config.php and index.php are writeable by the
web server user.

5/ There is an optional RSS feed and WIKINDX content may be accessed via
Content Management Systems such as Moodle, WordPress, etc. See
docs/README_RSS and docs/README_CMS for details.

    ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

--
Mark Grimshaw-Aagaard
The WIKINDX Team 2021
sirfragalot@users.sourceforge.net
