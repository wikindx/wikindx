---
title: Wikindx Readme
author:
 - Stéphane Aulery, <lkppo@users.sourceforge.net>
 - Mark Grimshaw-Aagaard, <sirfragalot@users.sourceforge.net>
 - The WIKINDX Team
date: 2020-07-09
lang: en
---

# Preamble (Mark Grimshaw-Aagaard)

I originally started developing this as a means to help organise my PhD
research  by catalogueing  bibliographic notations,  references, quotes
and thoughts on a computer via a program that was not tied to a single
operating system (like similar and expensively commercial software) and
that could be accessed from anywhere on the web (unlike other systems).
Additionally, I wanted a quick way to search, for example, for all
quotes and thoughts referencing a keyword or to be able to automatically
reformat bibliographies to different style guides.

As this is  a system designed to  run on any web server,  I thought its
use could be expanded to groups of researchers who could all contribute
to  and  read  the index. This  concept is  very  similar to  a Wiki
Encyclopaedia  where anyone can add or edit entries in an on-line
encyclopaedia.

Since the original ideas, various others have been implemented such as a
wide variety of import and export options and, importantly, the
incorportation of a WYSIWYG word processor that can insert citations and
(re)format them with a few clicks of the mouse.  This was powerful
enough for me to write my entire PhD thesis in. (v4 removed this feature
to a plug-in rather than being a core feature.)

Developed under the Creative Commons [CC-BY-NC-SA 4.0] license (since
v5.1, [GPL 2.0] before that), the project homepage can be found at:
<https://sourceforge.net/projects/wikindx/> and the required
files/updates and a variety of components are freely available there.


# Support, requirements, and compatibility

Wikindx is a web application written in PHP tested on:

 * Linux (Debian), Mac, Windows (and is intended to be OS independent)
 * [Apache] >= 2.x or [nginx] >= 1.11 (or any web Server able to run PHP
   scripts)

Support is provided through SourceForge's project tracking tools: [forum],
[bug tracker], [mailing list].

The development style is [Trunk-Based], but sometimes an important
development is the subject of an ephemeral branch.

The version system is not a branch system with long term support for
each one.  Only the trunk gets new features, security and bug fixes that
are not backported.  These developments are made available to the public
at each release of a new version.

The versions are numbered for the history and semi-automatic update
system of the data and the database (each change is applied between your
version and the target version).  However, always read the UPGRADE.txt
file for the steps to be done by hand when upgrading.

So we recommend that you regularly update to the latest version from the
tarball version available in the [SourceForge File] section, especially
if your wikindx is hosted on the web.

If you prefer an installation from a source management client, __we
strongly recommand__ that you use one of the __point release__ described
in the README.txt file at the root of SVN with the __trunk__ branch on a
__production__ server.

The __trunk__ branch (for developers and testers) can be broken at any
(and for a long) time and damage your database.


## Components compatibility

Wikindx, the core application, and officials components are developped
together: templates (themes), styles (bibliographic styles), languages
(gettext locales) and PHP plugins. The additional vendor component type
contains third-party software.

Wikindx comes with "default" template, "fr" language, "APA" style and
main vendor components pre-installed. No plugins are pre-installed.

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

Wikindx         [PHP]             [MySQL]  [MariaDB]
--------------- ----------------- -------- ---------
Later           >= 7.2 and <= 7.4 >= 5.7.5 >= 10.2
6.1.0 to 6.3.3  >= 7.0 and <= 7.4 >= 5.7.5 >= 10.2
5.7.2 to 6.0.8  >= 5.6 and <= 7.3 >= 5.7.5 >= 10.2
5.7.0 to 5.7.1  >= 5.6 and <= 7.2 >= 5.7.5 >= 10.2
5.3.1 to 5.3.2  >= 5.5 and <= 7.1 >= 5.7.5 >= 10.2
5.2.1 to 5.2.2  >= 5.5 and <= 5.6 >= 5.7.5 >= 10.2
5.2.0           >= 5.1 and <= 5.6 >= 5.7.5 >= 10.2
4.2.0 to 4.2.2  >= 5.1 and <= ??? >= 4.1   >= 10.1
4.0.0 to 4.0.5  >= 5.1 and <= ??? ???      >= 10.1
3.8.1           >= 4.3 and <= ??? ???      --

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
   json (>= 4.0.3), mbstring (>= 5.2.0), libxml, mysqli, pcre, Reflection
   (>= 5.2.0 and <= 6.0.1), session, SimpleXML (>= 4.2.0), xml, xmlreader
   (>= 5.2.0).

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

   * intl (>= 5.2.0): if disabled, PHPMailer will silently fail to send
     messages with a domain containing non ascii characters (see
     [Punycode]).

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
CKEditor is underway to address these limitations.

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


# Contributing

We are happy to welcome new contributors.  You can join us to contribute
to the creation of official components or the development of Wikindx.

There is a lot to do, not just development. Contact us!


## Localisation (l10n)

We are looking for translators. If you want to see Wikindx in your
native language do not hesitate to join us on [Transifex].

If you only want to report an error without committing yourself to fixing it, 
post it on the [forum] or the wikindx-developers [mailing list].

A _Translation Guide_ is available in the software documentation.

## Developping components

[TODO]

### component.json format

Each component must have a component.json in [JSON] format that
describes the main information about itself. The file must be structured
as a single object which corresponds to a PHP array once deserialized
with json_decode(). Each key/value pair of the object is an array
key/value entry. The format is under development and may change further.

Description of fields:

Field                 Type    O Description
--------------------- ------- - ------------------------------------------------------------------
component_type        string  Y Name of a type of component. Must be: language, style, template, or vendor.
component_id          string  Y Component identifier. Must be identical to the name of the root component directory.
component_version     string  Y Component public version number: this number is completely free.
component_builtin     bool    Y If true, the component will be pre-installed/released with the core. Thie field is independent of component_updatable.
component_updatable   bool    Y If true, the component will be released in a separate package that can be updated via the component admin panel.
component_name        string  Y Component short name.
component_description string  Y Component short description (without line break). Use the README.txt file for long description, notes...
component_licence     string  N Component license name or abbreviation (abbreviation recommended).
component_website     string  N A single full URL to the vendor or maintainer website to use as a link.
component_sha256      string  Y Unique signature corresponding to a sha256 hash of the component files. Use sign-components.php to compute it.
component_authors     array   N An array of objects unserialized as a PHP array of PHP arrays. Each object is an author or contributor.
author_name           string  Y Name of the author.
author_role           string  Y Role of the author. Something like: developper, packager, translator, tester, maintainer.
author_copyright      string  Y Contribution period. E.g. the years: 2004-2017.
author_email          string  Y A single email address of the author that could be embedded in a href with mailto.
author_website        string  Y A single full URL to the author website/personnal page to use as a link.


Column O indicates a mandatory field: Y=Yes, N=No.

Any additional fields will be ignored.

For the exact syntax of each data type, consult the official format
specifications [JSON].

When WIKINDX starts or the components admin panel is opened a
components.json file is created in the cache folder, and a
components.json file is created in the data folder.

The file in cache folder, listing all components installed, is an array
of object in [JSON] format. Each object is the content of a
component.json file with additional fields. Original component.json
fields remain inchanged.

Field                 Type    O Description
--------------------- ------- - ------------------------------------------------------------------
component_integrity   integer Y Error code returned when the component is checked. 0 is OK. Not 0 is an error.

The file in data folder, listing persistent data of some components, is
an array of object in [JSON] format. Each object is a short component
description (component_type + component_id) like a component.json file
with additionnal persistent fields. This list can contain data about
components that are no longer installed but that we want to keep for a
future reinstallation. Original component.json fields remain inchanged.

Field                 Type    O Description
--------------------- ------- - ------------------------------------------------------------------
component_status      string  Y Code status value: enabled or disabled. "enabled", if the component is executed on WIKINDX startup.

When the release script release/make.php script is executed it create
also a components.json, listing all components released with the current
core.

As the previous file, it is an array of object in [JSON] format. Each
object is the content of a component.json file with additional fields,
but different. Original component.json fields remain inchanged.

Field                 Type    O Description
--------------------- ------- - ------------------------------------------------------------------
component_packages    array   N Array of object. Each object is the description of a downloadable package of the component.
package_location      string  Y A single full URL to a downloadable package in zip, tar.gz, or tar.bz2 format.
package_sha256        string  Y Unique signature corresponding to a sha256 hash of the package generated by make.php.
package_size          integer Y Size in bytes of the package.


Example of a single component.json file for the smarty vendor component:

~~~~
{
    "component_type": "vendor",
    "component_id": "smarty",
    "component_version": "3.1.34-dev-7",
    "component_builtin": "true",
    "component_updatable": "true",
    "component_name": "PHP Template Engine Smarty",
    "component_description": "Smarty is a template engine for PHP, facilitating the separation of presentation from application logic.",
    "component_licence": "LGPL 3.0",
    "component_website": "https://www.smarty.net/",
    "component_authors": [
        {
            "author_name": "New Digital Group, Inc.",
            "author_role": "Developpers",
            "author_copyright": "2002-2019"
        },
        {
            "author_name": "Mark Grimshaw-Aagaard",
            "author_role": "Packager",
            "author_copyright": "2012-2016",
            "author_email": "sirfragalot@users.sourceforge.net",
            "author_website": "https://vbn.aau.dk/en/persons/126217"
        },
        {
            "author_name": "Stéphane Aulery",
            "author_role": "Packager",
            "author_copyright": "2017-2019",
            "author_email": "lkppo@users.sourceforge.net",
            "author_website": "http://saulery.legtux.org/"
        }
    ],
    "component_sha256": "e4865976c11067e720c45d60769777045b4be94b5ff356a00c81079238ed21d6"
}
~~~~


Example of a data/components.json file:

~~~~
[
    {
        "component_type": "language",
        "component_id": "fr",
        "component_status": "enabled"
    },
    {
        "component_type": "style",
        "component_id": "apa",
        "component_status": "enabled"
    },
    {
        "component_type": "template",
        "component_id": "default",
        "component_status": "enabled"
    },
    {
        "component_type": "vendor",
        "component_id": "jquery",
        "component_status": "enabled"
    }
]
~~~~


Example of a cache/components.json file extract:

~~~~
[
    {
        "component_type": "language",
        "component_id": "fr",
        "component_version": "1.0",
        "component_builtin": "true",
        "component_updatable": "true",
        "component_name": "French",
        "component_description": "Traduction française",
        "component_licence": "CC-BY-NC-SA 4.0",
        "component_authors": [
            {
                "author_name": "Simon Côté-Lapointe",
                "author_role": "Translator",
                "author_copyright": "2017",
                "author_email": "simonoliviercotelapointe@hotmail.com",
                "author_website": "http://simoncotelapointe.com/"
            },
            {
                "author_name": "Stéphane Aulery",
                "author_role": "Translator",
                "author_copyright": "2017-2019",
                "author_email": "lkppo@users.sourceforge.net",
                "author_website": "http://saulery.legtux.org/"
            }
        ],
        "component_sha256": "fff4092a9c602efe4e11bb2d68d1ca2afe6a6e7a40f4881d16b10ba11a12e474",
        "component_integrity": 0
    },
    {
        "component_type": "style",
        "component_id": "apa",
        "component_version": "1.0",
        "component_builtin": "true",
        "component_updatable": "true",
        "component_name": "APA",
        "component_description": "American Psychological Association (APA) (installed by default). APA is an author/date based style. This means emphasis is placed on the author and the date of a piece of work to uniquely identify it.",
        "component_licence": "CC-BY-NC-SA 4.0",
        "component_website": "https://apastyle.apa.org",
        "component_authors": [
            {
                "author_name": "Mark Grimshaw-Aagaard",
                "author_role": "Compiler",
                "author_copyright": "2005",
                "author_email": "sirfragalot@users.sourceforge.net",
                "author_website": "https://vbn.aau.dk/en/persons/126217"
            }
        ],
        "component_sha256": "af1ba8aa15c62d0ae9a2ce9dbe598d9001fab81451a7b10cf511d5ba26b6d25d",
        "component_integrity": 0
    }
]
~~~~


Example of a component.json file extract from the update server
generated by the make.php script:

~~~~
[
    {
        "component_version": "1.0",
        "component_licence": "CC-BY-NC-SA 4.0",
        "component_description": "Deutsch Sprachanpassun",
        "component_name": "German",
        "component_sha256": "dc79b8c5e4f303fa242345df5ee734598972c56406191d14e946d81b0ec2bde7",
        "component_id": "de",
        "component_builtin": "false",
        "component_type": "language",
        "component_updatable": "true",
        "component_authors": [
            {
                "author_name": "Stephan Matthiesen",
                "author_role": "Translator",
                "author_copyright": "2013",
                "author_email": "info@stephan-matthiesen.de",
                "author_website": "https://www.stephan-matthiesen.de/en/about-me.html"
            }
        ],
        "component_packages": [
            {
                "package_location": "https://sourceforge.net/projects/wikindx/files/archives/5.9.1/components/wikindx_5.9.1_language_de.zip",
                "package_sha256": "afb5fad76f2c5ce810653988792462352bab0dfe17f8f6065bbefc2f10f983b5",
                "package_size": 47372
            }
        ]
    },
    {
        "component_version": "1.0",
        "component_sha256": "5b8c8f37c8f46f67ff6d80ae2b1c6901c6c82b599c34ca387b2b31fd27433fb0",
        "component_licence": "CC-BY-NC-SA 4.0",
        "component_name": "Spanish",
        "component_description": "Traducción al español",
        "component_id": "es",
        "component_builtin": "false",
        "component_type": "language",
        "component_updatable": "true",
        "component_authors": [
            {
                "author_name": "Anonymous",
                "author_role": "Translator",
                "author_copyright": "2017"
            }
        ],
        "component_packages": [
            {
                "package_location": "https://sourceforge.net/projects/wikindx/files/archives/5.9.1/components/wikindx_5.9.1_language_es.zip",
                "package_sha256": "f86dde75e51816c8c25c2e6ef34b21a462a5b23273978f5692546e6bf9f8c8d6",
                "package_size": 32241
            }
        ]
    }
]
~~~~

# Credits

Wikindx includes and uses several free creations. We thank the authors
for their work:

 * [FatCow], Free Icon Set ([CC BY 3.0])
 * [TinyMCE], JavaScript WYSIWYG HTML Editor ([LGPL 2.1])
 * [TinyMCE Compressor PHP] ([LGPL 3.0])
 * [Smarty], PHP templating system ([LGPL 3.0])
 * [SmartyMenu], menu plugin for Smarty ([LGPL 2.1])
 * [PdfToText], PHP library to extract data from a PDF ([LGPL 3.0])
 * [PHPMailer], PHP email sending library ([LGPL 2.1])
 * [CKEditor], JavaScript WYSIWYG HTML Editor ([LGPL 2.1])
 * [json2.js], JSON in JavaScript (Public Domain)
 * [jpGraph], Graph creating library ([QPL 1.0])
 * [progressbar.js], ProgressBar.js ([MIT])

# Appendix

 * [PHP in Debian].
 * [phpversions.info]: a website about PHP versions supported by some OS.
 * [Distrowatch search]: finding a PHP version in major Open Source OS.
 * [Title Logo Generator]: title on image generator.


[CC BY 3.0]: <https://creativecommons.org/licenses/by/3.0/deed.en>
[CC-BY-NC-SA 4.0]: <https://creativecommons.org/licenses/by-nc-sa/4.0/>
[GPL 2.0]: <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
[GPL 3.0]: <https://www.gnu.org/licenses/gpl-3.0.en.html>
[LGPL 2.1]: <https://www.gnu.org/licenses/old-licenses/lgpl-2.1.en.html>
[LGPL 3.0]: <https://www.gnu.org/licenses/lgpl-3.0.en.html>
[MIT]: <https://opensource.org/licenses/MIT>
[QPL 1.0]: <https://opensource.org/licenses/QPL-1.0>

[PHP]: <https://secure.php.net/>
[MySQL]: <https://www.mysql.com/fr/>
[MariaDB]: <https://mariadb.org/>
[mysqli]: <https://www.php.net/manual/fr/book.mysqli.php>
[Apache]: <https://httpd.apache.org/>
[nginx]: <https://www.nginx.com/>
[Punycode]: <https://en.wikipedia.org/wiki/Punycode>

[Trunk-Based]: <https://trunkbaseddevelopment.com/>
[SourceForge File]: <https://sourceforge.net/projects/wikindx/files/>
[FatCow]: <https://www.fatcow.com>
[TinyMCE]: <https://www.tiny.cloud/>
[TinyMCE Compressor PHP]: <https://github.com/hakjoon/hak_tinymce/tree/master/tinymce_compressor_php>
[Smarty]: <https://www.smarty.net>
[SmartyMenu]: <http://www.phpinsider.com/php/code/SmartyMenu>
[PdfToText]: <https://github.com/christian-vigh-phpclasses/PdfToText>
[PHPMailer]: <https://github.com/PHPMailer/PHPMailer>
[CKEditor]: <https://ckeditor.com/>
[json2.js]: <https://github.com/douglascrockford/JSON-js>
[JSON]: <https://www.json.org/json-en.html>
[jpGraph]: <https://jpgraph.net/>
[progressbar.js]: <https://kimmobrunfeldt.github.io/progressbar.js>

[WebKit based browsers]: <https://en.wikipedia.org/wiki/List_of_web_browsers#WebKit-based>
[Transifex]: <https://www.transifex.com/saulery/wikindx/dashboard/>

[forum]: <https://sourceforge.net/p/wikindx/discussion/>
[bug tracker]: <https://sourceforge.net/p/wikindx/support-requests/>
[mailing list]: <https://sourceforge.net/p/wikindx/mailman/>

[officialy supported PHP version]: <https://www.php.net/supported-versions.php>
[phpversions.info]: <http://phpversions.info/operating-systems/>
[PHP in Debian]: <https://wiki.debian.org/PHP>
[Distrowatch search]: <https://www.distrowatch.com/search.php>
[Title Logo Generator]: <http://www.webestools.com/web20-title-generator-logo-title-maker-online-web20-effect-reflect-free-photoshop.html>
