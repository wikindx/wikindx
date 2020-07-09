---
title : WIKINDX's Translation Guide
subtitle: Internationalisation (i18n) and Localisation (l10n)
author:
 - Stéphane Aulery, <lkppo@users.sourceforge.net>
 - Mark Grimshaw-Aagaard, <sirfragalot@users.sourceforge.net>
 - The WIKINDX Team
lang: en
date: 2020-07-09
---

# Introduction

This document is intended for developers and translators of WIKINDX.

WIKINDX has a translation system for its graphical interface,
bibliographic styles and plugins. It uses the PHP [Gettext] library (no
call to PHP [Intl] library) for the display, PHP 7.0 (or higher) and the
tools of the [GNU Gettext suite] for the maintenance of the
translations.

## Prepare your environment

If you are not only translating but also testing the translation in
WIKINDX, you will need the above programs. We do not explain how to
install the first two because, as a likely WIKINDX user, you already
have an appropriate environment.

To install the tools of the Gettext suite, [here][GettextWinBin] you
will find binaries for Windows, [here][GettextMacBin] for Mac. For Linux
/ BSD, see the packages / ports manager of your system.

After installation make sure that the xgettext program is accessible
from the directory where WIKINDX is installed by typing the _xgettext_
command in a terminal. If you get a _command not found_ message or
something similar, you might need to add the gettext bin directory to
your PATH environment variable.

You might also need a tool like [Poedit] for translation.


# Localisation (l10n)

Translation is, in fact, about [localisation] (l10n) in the broad sense
because other elements can be adapted according to time, place,
language, culture, etc.

For example, the sort order and the date format will change according to
user's preference. Language itself is only one aspect.

In order to be able to replace the original messages (in English) while
running, Gettext needs a __catalog__ of messages translated by
linguistic variant and by domain (see below) called the __PO__ file (a
__POT__ file is a template of __PO__ file).

This structured file associates English messages one by one with their
message in the translation language, sometimes several depending on the
number of plural forms of the target language. Some metadata in the
header provide information about the contents of the file (translation
language, number of plural forms, encoding, last translator ...). Here
is the example the French __PO__ file:

~~~~
# Wikindx's Français Translation ressource.
# Copyright (C) 2019, Mark Grimshaw-Aagaard <sirfragalot@users.sourceforge.net>.
# This file is distributed under the same license as the debugtools package.
#
msgid ""
msgstr ""
"Project-Id-Version: debugtools\n"
"Report-Msgid-Bugs-To: sirfragalot@users.sourceforge.net\n"
"POT-Creation-Date: 2019-11-02 00:20+0100\n"
"PO-Revision-Date: 2019-09-07 15:22+0200\n"
"Last-Translator: Automatically generated\n"
"Language-Team: fr\n"
"Language: fr\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Plural-Forms: nplurals=2; plural=(n > 1);\n"

#: components/plugins/debugtools/debugtoolsMessages.php:20
msgid "Action"
msgstr ""

#: components/plugins/debugtools/debugtoolsMessages.php:21
#, fuzzy
msgid "Backup"
msgstr "Backup"

#: components/plugins/debugtools/debugtoolsMessages.php:22
msgid "Both"
msgstr "Les deux"
~~~~

Unless you are familiar with this format, it is not recommended to edit
it with a text editor. You should not edit the headers either because
they are preconfigured by WIKINDX depending on the language of
translation.

You must also use only UTF-8 encoding because WIKINDX uses it being the
only encoding capable of providing good interoperability.

## Translating

The latest version of the translations is online on [Transifex], a
translation project management system. Before your first contribution,
register for free on this portal.

Then go to the [languages section][TransifexLanguages] of the WIKINDX
project.  Choose the language of translation, for example, [French
(fr)][TransifexFr].

The code in parenthesis is called a locale. It denotes a language (ll)
(without national preference) or a language and a country (ll_CC) or a
language, a country, and an alphabet or a local variant (ll_CC@var).

For example, the translation catalog of the locale _fr_ will be
automatically used for the locales _fr_BE, fr_CA, fr_CH, fr_FR, fr_LU,
fr_MC_ as long as they do not have their own catalog of translations.

If the language you want to translate does not yet exist, make a request
to the WIKINDX developers to add it. A discussion might be needed to
decide the most appropriate code for your translation.

On the language screen you see the catalog files listed by domain
(Transifex calls this a resource): _adminstyle.pot_, _backupmysql.pot_
... __wikindx.pot__ is the main file. It contains the translation of the
core. The other files are the plugin translations (one file per plugin).
Translate __wikindx.pot__ first, because it benefits the largest number
of users.

Click on a catalog, a popup opens. You can see the number of messages to
translate, to review, or that are already translated.

You can either translate online on the Transifex portal or download the
catalog file and translate it with a tool like Poedit, uploading it when
finished to the translated file on the portal.

When you are working alone you can choose one of the above working
methods, but, when several people work at the same time on a
translation, you should not use the file upload method unless you
coordinate with others because, when uploading back to the portal,
messages uploaded by other users are overwritten. For the same reason,
when working with others, it is not a good idea to keep a file outside
of Transifex for a long time (several days or weeks).

Click on the __Translate__ button. Now, you are in the translation
screen.

__On the left__ is a list of messages (without duplication, unless it is
voluntary) sortable, and filterable at will.

__In the center__ is the translation area and additional information
about a message that can help you translate it. You can see from which
file the message is pulled and sometimes a comment left by a developer
to specify the context.

__On the right__ there are translation suggestion tools, a translation
history and so on.

Translate the strings one by one. You can also mark them when you have
proofread them. As long as a message is not translated, its English
version will be displayed instead. This allows WIKINDX to use a
partially translated catalog even if a complete translation is not yet
available for users.

When compiling the translation, other than a finished translation, there
are 6 major scenarios for each message:

- The message is not translated, lacks a suggestion, and its translation
  is different in your language: just translate;
- The message is not translated and its translation is identical in your
  language: copy and paste the English version;
- The message is not translated, has a suggestion, and its translation
  is different in your language: When developers update the model
  catalog, the new strings to translate are automatically added to the
  list. If an original message has changed or looks like another, its
  translation is requested again with a suggestion in the right pane.
  It's up to you to decide whether the translation needs to be rewritten
  completely or if the suggestion is reusable;
- You have been notified of, or you notice, a translation error: find
  the message and correct its translation;
- You think you have found an error in an English message: do not
  translate and report it by email to the developers so that the English
  can be checked. If the error is minor (like a missing plural) and does
  not affect the possible meaning of the translation you can still
  translate with the meaning that you deem correct. If the English is
  corrected later the message will be marked for proofreading;
- If you cannot translate a message or your translation would be
  incomplete, do not translate it at all, leaving the translation empty.

Some messages contain code and variables like `$QUICKSEARCH$` that you
do not have to translate. Others contain placeholders such as
`#currentWikindxVersion#`, `###` (WIKINDX specific syntax), and `%s`,
`%d`, `%1$s`, `%2$s` (see the [printf] function family for a complete
description). You should not change these placeholders because they are
replaced by numbers, dates, and strings (the replacement value should be
described in the comment of the message). You can, though, change their position
in a translated message if there is only one in a message, or if they
have an index like `%1$s`, `%2$s`.

Currently, the messages have not been written with regard to translating them 
in a language that is written from right to left or fully repositioning the 
placeholders.

Unless there are special instructions in the comments you can change the rest
of the message including typography for a better layout. You also have the
capabilities of UTF-8 so do not use an [HTML entity] – rather input the 
character directly.

When you have finished translating the domains, report it so that it is
integrated into the core software as soon as possible. As a general rule,
share your intentions or difficulties with the developers or coordinators
of your language. We can help you.

For translation difficulties and help resources you can contact groups
of translators of your languages as indicated on this page of the
[Translation Project].

## Using or testing a new translation

You have completed the translation and you want to test the result in
the software.

Install the language component __Gettext sources__ from the Admin component panel.

In the `components/languages/src directory` of your WIKINDX installation, add the __locale
code__ for the new language to the __languages.json__ file. The
character case must be the same than in the __getAllLocales()__ function
of __core/locales/LOCALES.php__ file. For example, if adding the __sl__
code for Slovenian.

__languages.json__ before:

~~~~
[
    "de",
    "es",
    "fr",
    "it",
    "ru"
]
~~~~

__languages.json__ after:

~~~~
[
    "de",
    "es",
    "fr",
    "it",
    "ru",
    "sl"
]
~~~~

Open a console, `chdir` to the WIKINDX installation directory and run the
__make-languages.php__ script.

~~~~
$ cd /my/wikindx/directory
$ php make-languages.php
~~~~

If all goes well, this script will create a subdirectory named after the
code of the locales enabled (in lowercase) in the components/languages
directory, and a second subdirectory named after the code of the locales
enabled (in lowercase) in the `components/languages/src` directory.

The first subdirectories (components/languages/<ll_CC>) also contain
an LC_MESSAGES subdirectory that contains Gettext catalogs compiled in a
binary format (__MO__ file).

The second subdirectories (components/languages/src/<ll_CC>) contain
Gettext catalogs in a text source format (__PO__ file).

Example of languages tree:

~~~~
components/languages
 |_ de
 |  |_ component.json
 |  |_ LC_MESSAGES
 |     |_ wikindx.mo
 |     |_ ..
 |_ ..
 |_ sl
 |  |_ component.json
 |  |_ LC_MESSAGES
 |     |_ wikindx.mo
 |     |_ ..
 |_ src
    |_ component.json
    |_ languages.json
    |_ wikindx.pot
    |_ ..
    |_ de
    |  |_ wikindx.po
    |  |_ ..
    |_ sl
       |_ wikindx.po
       |_ ..
~~~~

On transifex, download each catalog of your language and copy them into
that `components/languages/src/<ll_CC>` subdirectory.

Execute again the __make-languages.php__ script.

This time, the script extracts the translations from your __PO__ files
and turns them into __MO__ files. These are in a more suitable format and 
the only one recognized by Gettext when running WIKINDX.

Copy the component.json of an other language in the folder of the new
language and change its fields accordingly. After that go to the components
admin panel for refreshing the cached component list.

Finally, you can choose your language in WIKINDX to see your translation
into action.  (Sometimes it is also necessary to restart the web server
for this to take effect.)

## Packaging and distribution

If you have translated on transifex, your contribution will be
distributed with the next version of the core. If you have translated a
PO file directly and want your work to be distributed, contact the
developers. In any event, let us know your names and surnames or your
nickname, your email and / or website, the license (to be discussed if
it is different from that of the core) for the credits.


# Internationalisation (i18n)

## Operation

_This is a quick introduction, for details read the code._

To update the POT files on Transifex, copy the files to the
`/home/project-web/wikindx/htdocs/transifex/pot` directory on the SF
Wikindx Website FTP folder. Transifex update the resources (twice a day)
from these files.  You can force the update by hand but put the POT file
online before.

Deleting, damaging, or pushning outdated POT files in the
`/home/project-web/wikindx/htdocs/transifex/pot` folder can result in
lost of translations when references strings are removed or modified.
The same can happen when pushing a file by hand.

POT files circulate in the SVN => Transifex direction. And the PO in the
other. Doing otherwise can result in the loss of translators' work. It
is still possible to load PO files in bulk on Transifex but you must
coordinate with the translators to obtain read-only access and
synchronization of work in progress before.

One must be very careful with these maintenance operations.

All core reference strings are stored in two-level PHP arrays, the first
of which is a thematic grouping key and the second of which is a key for
naming the requested string. All these strings are grouped in PHP files
in the `core/messages` folder. The PHP HELP, MESSAGES, SUCCESS,
CONSTANTS, ERRORS classes are used both for storage and as a method of
accessing the strings for the entire application.

They use the official Gettext functions __dgettext__ (singular) and
__dngettext__ (plurial) with a single domain (see
WIKINDX_LANGUAGE_DOMAIN_DEFAULT) for the whole core without taking into
account the sub-key partitioning.

However, messages can be defined in any PHP file as long as they use the
official PHP Gettext functions, and that the PHP files are placed
elsewhere than in the components, cache, and data directories. For the
exact list see make-languages.php. For the moment we have chosen to
group the messages in the `core/messages` folder.

These classes do not load the translation catalogs. It is assumed that
they are already loaded when these classes are used. They read the
translation of the already loaded strings which correspond to the
reference language (WIKINDX_LANGUAGE_DEFAULT) and in the absence of
translation read the reference strings instead, and replace some
patterns such as ### with values before returning them.

The PLUGINMESSAGES class also offers a partial method of access to the
gettext catalogs of plugins but is not required for the proper
functioning of translations of a plugin as long as it meets four
conditions:

- Its domain is the same as its id for all its strings (e.g.
  "adminstyle" domain for "adminstyle" plugin).
- It uses the gettext PHP functions.
- The reference strings are defined in PHP files.
- The PHP strings files are in the source directory of the plugin.

`core/startup/WEBSERVERCONFIG.php` script is responsible for calling the
\LOCALES\load_locales() function which load the translation catalogs
into memory according to the preferences of the user session. Catalogs
of only one language can be loaded at the same time for the same
process. The catalog closest to the desired locale is loaded, if there
is one, examining the variant, then the country, and finally the
language.

WARNING: it sometimes happens that the catalogs are not unloaded between
two scripts call. Restart PHP-FPM and the web server processes in this
case. Likewise the gettext library uses a relative path to the loaded
catalogs. A script that changes directory with chdir() will lose the
translation functionality but if it restores its original working
directory it will be able to recover the translation functionality.

\LOCALES\getAllLocales() provide a prebuilt list of locales (see SVN
`wikindx/tools/i18n/` scripts used to establish it) for macOS, Linux,
OpenBSD and Windows (7), in the hope that it is as complete as possible.
Their names (language + country name) are in the locale language itself
for easy user access. When the locales have non-standard codes on
certain systems, the \LOCALES\getLocaleGettextAliases() function gives
the translation at the time of loading, but for users and catalog
storage, only the standard form is used.

This list is not used directly but it is used to detect the locales
installed on the system. A second list is taken from this detection and
saved in the `cache/locales_system.json` file (refresh at each core
upgrade). Users choose their preferred locale from this second list.

An artificial locale called auto is added to this list. It allows the
user to let Wikindx deduce its locale from the headers sent by its
browser.

The language code returned in the HTML source code of the pages IS NOT a
locale but that which corresponds to it in the [BCP47] encoding.

## Limitations

It is up to the user to choose a locale and not a language to benefit
from all the culture-related behaviors even if there is no translation
for his language. It is not possible for the user at the moment to
choose different subtypes of locale.

The core does not necessarily exploit all of the localized PHP features
to take advantage of user preferences.

Value replacement in translated strings is poor. It should be replaced
by numbered patterns and functions from the printf family.

Plural forms are not yet used in all catalogs.

Notes for translators must be completed, insertions commented.

So far the strings have not been written taking into account the
direction of writing, formats, and other difficulties related to
localization.

There is no distinction between the language desired by the user for the
graphical interface and the language of the content entered in resources
and other data. The data simply has no language at the moment.

The language of bibliographic styles is defined which can contradict a
user preference when formatting.

Substantial work is still necessary.

Some resources on the subject:

- http://www.i18nguy.com/unicode/club-rules.html
- https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Web_Localizability/Creating_localizable_web_applications
- http://www.i18nguy.com/guidelines.html
- http://www.i18nguy.com/
- http://www.xencraft.com/training/webstandards.html


[Gettext]: <https://www.php.net/manual/en/book.gettext.php>
[Intl]: <https://www.php.net/manual/en/book.intl.php>
[GNU Gettext suite]: <https://www.gnu.org/software/gettext/>
[GettextWinBin]: <https://mlocati.github.io/articles/gettext-iconv-windows.html>
[GettextMacBin]: <http://macappstore.org/gettext/>
[Poedit]: <https://poedit.net/>
[localisation]: <https://en.wikipedia.org/wiki/Language_localisation>
[Transifex]: <https://www.transifex.com/saulery/wikindx/dashboard/>
[TransifexLanguages]: <https://www.transifex.com/saulery/wikindx/languages/>
[TransifexFr]: <https://www.transifex.com/saulery/wikindx/language/fr/>
[Translation Project]: <https://translationproject.org/team/index.html>
[printf]: <https://www.php.net/manual/fr/function.printf.php>
[HTML entity]: <https://dev.w3.org/html5/html-author/charref>
[BCP47]: <https://www.w3.org/International/questions/qa-html-language-declarations>
