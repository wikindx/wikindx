********************************************************************************
**                               Choose Language                              **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5 and up.

A small select box for users to quickly and efficiently change language 
localization without the need to edit their preferences.  Any change 
is not permanent and will be lost when the user logs out.

If the WIKINDX does not have two or more languages available to users, the 
plugin will not display.

The module registers itself as an inline plugin.

Unzip this file (with any directory structure) into plugins/chooselanguage/.
Thus, plugins/chooselanguage/index.php etc.

********************************************************************************

CHANGELOG:

2021-07-07 : CHG : change of the compatibility version (13) (style editor change for v6 style components).
2021-05-28 : CHG : change of the compatibility version (12) (removal of userwritecategory plugin).
2021-05-16 : CHG : change of the compatibility version (11) (removal of importexportbib plugin).
2021-04-18 : CHG : change of the compatibility version (10) (removal of the database prefix).
2020-12-21 : CHG : make PHP includes independent of the web server layout (#244).
2020-12-21 : CHG : reformat source code to the prefered if/then/else style.
2020-07-11 : CHG : relicencing under ISC License terms.


v1.7, 2020
1. Fix a letter case issue stopping the plugin working on some systems.

v1.6, 2020
1. Fix heredoc syntax.
2. Fix some errors on the refreshing of the language.

v1.5, 2020
1. Add documentation.

v1.4, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.
2. Fix CSS/JS includes.

v1.3
1. Adaptation for WIKINDX 5.9.1.

v1.2
1. Plugin now compatible with WIKINDX v5.x

v1.1
1. Plugin compatible only with WIKINDX v4.2.x

v1.0, 2015
1. Initial release.
