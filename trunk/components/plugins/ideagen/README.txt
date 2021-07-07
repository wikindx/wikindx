********************************************************************************
**                           	 Idea Generator                            	  **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v6 and up.
Results may be unexpected if used with a lower version.

Idea Generator

Idea Generator randomly selects and displays two items of metadata (quotes, 
paraphrases, musings, or ideas) in the hope that the chance juxtaposition might be
serendipitous. If so, then a new idea can be stored.

The module registers itself in the 'Metadata' menu and requires three or more items 
of metadata to be available to the user.

Unzip this file (with any directory structure) into components/plugins/ideagen/.
Thus, components/plugins/ideagen/index.php etc.

********************************************************************************

CHANGELOG:

2021-07-07 : CHG : change of the compatibility version (13) (style editor change for v6 style components).
2021-05-28 : CHG : change of the compatibility version (12) (removal of userwritecategory plugin).
2021-05-22 : CHG : add options to fine tune the two selections.
2021-05-16 : CHG : change of the compatibility version (11) (removal of importexportbib plugin).
2021-04-18 : CHG : change of the compatibility version (10) (removal of the database prefix).
2021-04-16 : CHG : re-enable the insertion of plug-ins into the metadata menu.
                   Where the plugin config.php file inserted into 'text', this should now be 'metadata'.
2020-12-21 : CHG : make PHP includes independent of the web server layout (#244).
2020-12-21 : CHG : converted all instances of trim() for form input to UTF8::mb_trim().
2020-12-21 : CHG : handle multiple tabs.
2020-12-21 : CHG : if browsing a user bibliography, use it also for the front page
                   (which otherwise uses the master bibliography) – set in WIKINDX|Bibliographies.
2020-12-21 : FIX : minor debugging of the ideaGen plugin.
2020-12-21 : CHG : improved the options displayed in the Metadata menu.
2020-12-21 : CHG : reformat source code to the prefered if/then/else style.
2020-12-21 : CHG : full French translation.
2020-07-11 : CHG : relicencing under ISC License terms.

v1.1, 2020
1. Some debugging to ensure metadata are returned at each generation.

v1.0, 2020
1. Initial release June 2020.

--
Mark Grimshaw-Aagaard 2020.