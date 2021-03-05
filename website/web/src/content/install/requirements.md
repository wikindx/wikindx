+++
title = "Requirements"
date = 2021-01-30T00:08:41+01:00
weight = 1
+++


## Components compatibility

Wikindx, the core application, and officials components are developped
together: templates (themes), styles (bibliographic styles) and PHP
plugins. The additional vendor component type contains third-party
software.

Wikindx comes with "default" template, "APA" style and main vendor
components pre-installed. No plugins are pre-installed.

All components are available on [SourceForge File] section for a manual
installation or via the component update system embeded in Wikindx.

Each official extension is released with a new version of the
application and only for the last version.

For reasons of immaturity of the system of components it is recommended
to contribute to the official development team so that the components
are always compatible with the latest version. If you create your own
components it is not guaranteed that they will work on a later version
of Wikindx.



## PHP & MySQL versions

|Wikindx        | [PHP]            | [MySQL] | [MariaDB]
|---------------| -----------------| --------| ---------
|6.4.0 and later| >= 7.3 and <= 7.4| >= 5.7.5| >= 10.2
|6.1.0 to 6.3.10| >= 7.0 and <= 7.4| >= 5.7.5| >= 10.2
|5.7.2 to 6.0.8 | >= 5.6 and <= 7.3| >= 5.7.5| >= 10.2
|5.7.0 to 5.7.1 | >= 5.6 and <= 7.2| >= 5.7.5| >= 10.2
|5.3.1 to 5.3.2 | >= 5.5 and <= 7.1| >= 5.7.5| >= 10.2
|5.2.1 to 5.2.2 | >= 5.5 and <= 5.6| >= 5.7.5| >= 10.2
|5.2.0          | >= 5.1 and <= 5.6| >= 5.7.5| >= 10.2

PHP support is tested for the core, extentions, and third party software
included. For security purpose we recommend you use an [officialy
supported PHP version] of the PHP Team. Wikindx can support older
versions to facilitate the migration of an installation but are not
intended to support a large number of versions.

Wikindx does not use advanced features of MySQL / MariaDB which should
allow to install it without problems on a recent version. However, there
is no guarantee that MySQL will change its default options to stricter
uses as in the past. If you encounter a problem let us know it.

Support for another database engine is not considered, and support is
limited to the [mysqli] driver.

Minimal versions are strictly understood for decent support of UTF-8 and
runtime-checked.


## PHP extensions

### Core requirements

The version numbers in this section are those of Wikindx, not those of a
PHP extension or a library used by a PHP extension. When the version is
not specified the need is valid for all versions of Wikindx.

 * Mandatory extensions: Core, date, fileinfo, filter (>= 5.2.0), gd
   (>= 4.0.0), gettext (>= 4.2.1), hash (>= 5.2.0), iconv (>= 4.0.3),
   intl (>= 6.3.9), json (>= 4.0.3), mbstring (>= 5.2.0), libxml, mysqli,
   pcre, Reflection (>= 5.2.0 and <= 6.0.1), session, SimpleXML (>= 4.2.0),
   xml, xmlreader (>= 5.2.0).

In summary, from version 5.2.0 all the above mentioned extensions are
necessary for a good functioning, which should be the case of almost all
the installations.

 * Optional extensions:

   * curl (>= 5.2.0): if allow_url_fopen and curl are disabled Wikindx
     will be not be able to efficiently extract texts of a PDF and embed an
     external image in an RTF export.

   * enchant (>= 5.2.0): if disabled, the spell checker incorporated into
     TinyMCE will be disabled. It is always disabled under Windows
     because the pspell is uninstallable in practice on this OS.

   * openssl (>= 5.2.0): if disabled, PHPMailer will not be able to use
     secure protocols (starttls, tls, ssl) in SMTP mode and encryption.

   * pspell (>= 5.2.0): if disabled, the spell checker incorporated into
     TinyMCE will be disabled. It is always disabled under Windows
     because the pspell is uninstallable in practice on this OS.

   * sockets (>= 5.2.0): if disabled, PHPMailer will not be able to use
     the SMTP protocol.

   * zip: if disabled, Wikindx will not be able to export attachments in
     a Zip archive.

   * curl + zip (>= 5.9.1): if disabled, the update feature of Wikindx
     components will be disabled.

### Official plugins requirements

This list only indicates the need of extensions when it is more
important than those of the core:

 * backupMySQL: zlib (optional)
 * dbAdminer: bz2 (optional), openssl (optional), zlib (optional),
   zip (optional)
 * visualize: bz2 (optional), zlib (optional)
 * wordProcessor: enchant (optional), socket (optional)


## Browser compatibility

The  client  accesses  the  database via  a  standard  (graphical)  web
browser. Wikindx  makes use of the tinyMCE editor  (v2.0.4) and this
could limit the web browsers that can be used. A migration project to
TinyMCE 5 is underway to address these limitations.

Generally, though, if you use the following, you should be OK:

 * Internet Explorer >= v9
 * Edge >= v10
 * Firefox >= v12
 * Chrome >= v4
 * Safari >= v10
 * Opera >= v12
 * [WebKit based browsers]

You must enable JavaScript execution, otherwise important features such
as searching or creating resources will not work.
