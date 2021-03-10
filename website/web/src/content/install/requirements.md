+++
title = "Requirements"
date = 2021-01-30T00:08:41+01:00
weight = 1
+++


## Hosting

WIKINDX requires a web server environment comprising a web server (typically [Apache](https://apache.org/), [Nginx](https://www.nginx.com/), or [Microsoft IIS](https://www.iis.net/)), [PHP](https://www.php.net/) scripting, and a [MariaDB](https://mariadb.org/) (or [MySQL](https://www.mysql.com)) server.

Most web-hosting providers can provide this configuration at a low cost. Even small VPS of a few bucks per month are more than enough.  If you don't have any system administration skills or don't have the time to deal with it this is definitely your best option. Placing WIKINDX on such a remote web server means that it can be accessed from any machine and people on the web. The constraint of this solution is security. Be sure to update Wikindx regularly to benefit from our security fixes

WIKINDX can also be installed on a private network within a company, institution or an organization or locally for a single user on one desktop or laptop computer.

Inside a private network you probably need to contact your administrator who will provide the infrastructure support necessary for your installation.

For a single installation there are a variety of [LAMP software bundle](https://en.wikipedia.org/wiki/LAMP_(software_bundle)) that can be downloaded to create the required web server environment. Users reported to setup successfully:

* [Caddy2](https://caddyserver.com/v2) (Web server only, OS versatile, Go based, for advanced users only)
* [WAMP](https://www.wampserver.com/) (Windows only, Apache based)
* [WinNMP](https://winnmp.wtriple.com/) (Windows only, Nginx based)
* [MAMP](https://www.mamp.info/en/mamp/) (Windows/MacOS, Apache based)
* [XAMPP](http://www.apachefriends.org/en/xampp.html) (Windows, Linux, MacOS, Apache based)
* [Homebrew](https://brew.sh/) (package manager for MacOS)

If you have a BSD or Linux server then choose the softwares available from the package manager of your distribution / OS. They are usually very well endowed. Favor Apache over Nginx which is much easier to configure. As a general rule we follow the [software versions](https://distrowatch.com/table.php?distribution=debian) to be compatible with the latest [stable version of Debian](https://www.debian.org/releases/).

If a third party is configuring for hosting, provide them with the information on this page.


## MariaDB & MySQL versions

Wikindx does not use advanced features of MariaDB / MySQL database engines which should
allow to install it without problems on a recent version. However, there
is no guarantee that MySQL will change its default options to stricter
uses as in the past. We are finding that more and more [MariaDB and MySQL
diverge](https://mariadb.com/kb/en/mariadb-vs-mysql-compatibility/) which
may lead to the removal of MySQL support in the future.
If you encounter a problem let us know it.

Minimal versions are strictly understood for decent support of UTF-8 and
runtime-checked. The setup blocks the installation if the db engine does not correspond to the minimum required.

Wikindx use the following options and sets them on db connection:

* Charset: [utf8mb4](https://mariadb.com/kb/en/supported-character-sets-and-collations/)
* Collation: [utf8mb4_unicode_520_ci](https://mariadb.com/kb/en/setting-character-sets-and-collations/) (please use this collation on db creation)
* GROUP_CONCAT limit: [group_concat_max_len](https://mariadb.com/kb/en/group_concat/) = 200000
* Mode: [TRADITIONAL](https://mariadb.com/kb/en/sql-mode/#traditional) (this stricter mode allows us to prevent errors)

__Disclaimer: *Wikindx is not supposed to share its database with any other software. This widespread practice involves serious security breaches and possible data loss in the event of software bugs. Dedicate a database to Wikindx!*__

Support for another database engine is not considered and is
limited to the [mysqli](https://www.php.net/manual/fr/book.mysqli.php) PHP driver.

|Wikindx / MariaDB | 10.2 | 10.3 | 10.4 | 10.5
|------------------|------|------|------|------
|All versions      |   X  |   X  |   X  |   X

|Wikindx / MySQL   | 5.7.5 | 5.8 | 8.0
|------------------|------|------|------
|All versions      |   X  |   X  |   X


## PHP versions

PHP support is tested for the core, components, and third party software
included. For security purpose we recommend to use an [officialy
supported PHP version](https://www.php.net/supported-versions.php)
of the PHP Team. mod_php, CGI, and and FastCGI are compliant with PHP and Wikindx.

Wikindx can support older versions to facilitate the migration
of an installation but are not intended to support a large number of versions.

All current versions of PHP have a good support of UTF-8.

PHP version | Min Wikindx Version | Max Wikindx Version
------------|---------------------|--------------------
8.0         | 6.4.2 (partial)     | trunk (partial)
7.4         | 6.1.0               | trunk
7.3         | 5.7.2               | trunk
7.2         | 5.7.0               | 6.3.10
7.1         | 5.3.1               | 6.3.10
7.0         | 5.3.1               | 6.3.10
5.6         | 5.7.0               | 6.0.8
5.5         | 5.2.1               | 5.3.2
5.1         | 4.0.0               | ???
4.3         | 3.8.1               | ???


## PHP extensions


### Core requirements

The version numbers in this section are those of Wikindx, not those of a
PHP extension or a library used by a PHP extension. When the version is
not specified the need is valid for all versions of Wikindx.


#### Mandatory extensions

In summary, from version 5.2.0 all the extensions mentioned above are required
for proper functioning, which should be the case for almost all installations. 

| Extensions | Wikindx version
|------------|-----------------------
| core       |
| date       |
| fileinfo   |
| filter     | >= 5.2.0
| gd         | >= 4.0.0
| gettext    | >= 4.2.1
| hash       | >= 5.2.0
| iconv      | >= 4.0.3
| intl       | >= 6.3.9
| json       | >= 4.0.3
| libxml     |
| mbstring   | >= 5.2.0
| mysqli     |
| pcre       |
| Reflection | >= 5.2.0 and <= 6.0.1
| session    |
| SimpleXML  | >= 4.2.0
| xml        |
| xmlreader  | >= 5.2.0


#### Optional extensions and configuration

| Extensions       | Wikindx version | Note
|------------------|-----------------|-----------------
| curl             | >= 5.2.0        | Needed to extract texts of a PDF.
| enchant / pspell | >= 5.2.0        | Used by the spell checker of [TinyMCE](https://www.tiny.cloud/). It is always disabled under Windows because the pspell is uninstallable in practice on this OS.
| ldap             | >= 6.4.0        | Needed only for LDAP authentification mode.
| openssl          | >= 5.2.0        | Needed if you intend to send emails with secure protocols (starttls, tls, ssl) in SMTP mode and encryption (See [PHPMailer](https://github.com/PHPMailer/PHPMailer)).
| sockets          | >= 5.2.0        | Needed if you intend to send emails with the SMTP protocol.
| zip              |                 | Without this extension attachments will not be exported in a Zip archive. Also used by the release process.
| curl + zip       | >= 5.9.1        | if disabled, the update feature of Wikindx components will be disabled.
| Phar             |                 | Used by the release process.
| zlib             |                 | Used by the release process.

If **allow_url_fopen** is disabled Wikindx will be not be able to embed an external image in an RTF export.


#### Official plugins requirements

This list only indicates the need of extensions when it is more
important than those of the core:

 * backupMySQL: __zlib__ (optional)
 * dbAdminer: __bzi2__ (optional), __openssl__ (optional), __zip__ (optional), __zlib__ (optional)
 * wordProcessor: __enchant__ (optional), __socket__ (optional)


## Disk space

The code's disk space consumption is modest (around 40 MB for the code). You should plan a minimum of __150MB for the code__, downloading updates and one db backup. To this must be added the space to be allocated for attachments, images, attachment cache files ... It depends greatly on your use case.

Database disk space can vary greatly. The initial size is around 10 MB (50 KB for its backup). For a large base of 23,000 resources, 12,000 authors and 10 years of statistics, we have observed a size of 460MB (110 MB for its backup). 


## Memory consumption

In normal use Wikindx consumes less than 20 MB of RAM by process. It is recommended to limit to 64MB for the proper functioning of updating, searching, and extracting texts from PDF. 


## Execution time

Generally a script responds in less than 1 s but searches can go up to 5 or 15 s depending on the size of the database and the complexity of the request. This can be more during a database upgrade, extracting texts from PDF, or importing data. It is recommended to limit the execution time of scripts to 120 s to allow the upgrade and imports.


## Browser compatibility

The  client  accesses  the  database via  a  standard  (graphical)  web
browser. Wikindx  makes use of the [TinyMCE](https://www.tiny.cloud/) editor  (v2.0.4) and this
could limit the web browsers that can be used. A migration project to
TinyMCE 5 is underway to address these limitations. Your browser should
[support HTML5](https://caniuse.com/?search=html5)
and [CSS level 3](https://caniuse.com/?search=css3) well.

Generally, though, if you use the following, you should be fine:

 * [Internet Explorer](https://www.microsoft.com/en-us/download/internet-explorer.aspx) >= v9 (strongly discouraged since Microsoft doesn't support it anymore)
 * [Edge](https://www.microsoft.com/en-us/edge) >= v12
 * [Firefox](https://www.mozilla.org/fr/firefox/) >= v21
 * [Chrome](https://www.google.com/chrome/index.html) >= v26
 * [Safari](https://www.apple.com/safari/) >= v10
 * [Opera](https://www.opera.com) >= v15
 * [Vivaldi](https://vivaldi.com)
 * [WebKit based browsers](https://en.wikipedia.org/wiki/List_of_web_browsers#WebKit-based)

You must enable JavaScript execution. Without JavaScript Wikindx will not work properly.
