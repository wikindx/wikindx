********************************************************************************
**                      Localized front page description                      **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5 and up.  Results may be unexpected if used with a lower version.

Store and make available localized versions of the front page description depending on the current language 
localization the user is using.

The module registers itself in the 'Admin' menu.

Unzip this file (with any directory structure) into plugins/localDescription/.
Thus, plugins/localDescription/index.php etc.

********************************************************************************

CHANGELOG:

2020-12-21 : CHG : make PHP includes independent of the web server layout (#244).
2020-12-21 : CHG : handle multiple tabs.
2020-12-21 : CHG : reformat source code to the prefered if/then/else style.
2020-12-21 : CHG : separates the read / write functions of internal version numbers
                   into two Core / plugin families to prevent mishandling in updates.
2020-12-21 : CHG : full French translation.
2020-12-21 : FIX : restore front page description translation functionality (bugs #211 and #228).
2020-12-21 : ADD : internal version number + self-upgrade.
2020-12-21 : ADD : dedicated table plugin_localedescription.
2020-07-11 : CHG : relicencing under ISC License terms.

v1.6, 2020
1. Wikindx compatibility version 7.

v1.5, 2020
1. Add documentation.

v1.4, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.

v1.3, 2019
1. Added a check in case there are no languages installed other than English
2. Adaptation for Wikindx 5.9.1

v1.2
1. Plugin now compatible with WIKINDX v5.x

v1.1 ~ 20th May 2013
1. Initial release
