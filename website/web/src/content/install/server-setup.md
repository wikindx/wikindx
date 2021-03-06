+++
title = "LAMP Server Setup"
date = 2021-01-30T00:08:41+01:00
weight = 2
+++


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
