********************************************************************************
**                              userwritecategory                             **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5 and up.  Results may be unexpected if used with a lower version.

User administration of categories - non-admin users can add/edit/delete categories (they must be logged in). 

The module registers itself in the 'Edit' menu.

Unzip this file (with any directory structure) into plugins/userwritecategory/.
Thus, plugins/userwritecategory/index.php etc.

********************************************************************************

CHANGELOG:

2021-04-18 : CHG : change of the compatibility version (10) (removal of the database prefix).
2020-12-21 : CHG : make PHP includes independent of the web server layout (#244).
2020-12-21 : CHG : handle multiple tabs.
2020-12-21 : CHG : full French translation.
2020-12-21 : CHG : reformat source code to the prefered if/then/else style.
2020-07-11 : CHG : relicencing under ISC License terms.

v1.9, 2020
1. Add documentation.

v1.8, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.

v.1.7
1. If user is an admin, categories and subcategories can already be edited so remove this plugin from the menu system.
2. Adaptation for WIKINDX 5.9.1.

v1.6
1. Plugin now uses core WIKINDX code and so is simply a gateway to that.

v1.5
1. Plugin now compatible with WIKINDX v5.x

v1.4
1. Plugin compatible only with WIKINDX v4.2.x

v1.3 
1. Authorization bug fix.

v1.2
1. Updated for WIKINDX v4

v1.1
1. Added $this->authorize to control display of menu item for users with at least write access.
