********************************************************************************
**                                 db Adminer                                 **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5 and up.

WIKINDX custom wrapper for Adminer with a selection of tested plugins for Adminer.

Unzip this file (with any directory structure) into plugins/dbadminer/.
Thus, plugins/dbadminer/index.php etc.

NB: the FillLoginForm class is customized to always fill the login form with the db configuration of WIKINDX.

Adminer website: https://www.adminer.org/

********************************************************************************

CHANGELOG:

2021-05-16 : CHG : change of the compatibility version (11) (removal of importexportbib plugin).
2021-04-18 : CHG : change of the compatibility version (10) (removal of the database prefix).
2021-02-10 : CHG : Update adminer to 4.8.0 (https://github.com/vrana/adminer/releases/tag/v4.8.0).
2021-02-07 : CHG : Update adminer to 4.7.9 (https://github.com/vrana/adminer/releases/tag/v4.7.9).
2020-12-21 : CHG : make PHP includes independent of the web server layout (#244).
2020-12-21 : CHG : reformat source code to the prefered if/then/else style.
2020-12-21 : CHG : update Adminer to version 4.7.8 (PHP 8.0 support).
2020-12-21 : CHG : full French translation.
2020-07-11 : CHG : relicencing under ISC License terms.
2020-06-24 : SEC : prevent direct access to subparts of adminer. Only the index.php
                   and adminer.php scripts can be called and it includes others.

v1.7, 2020
1. Update adminer to 4.7.7 (https://github.com/vrana/adminer/releases/tag/v4.7.7).

v1.6, 2020
1. WIKINDX compatibility version 7.

v1.5, 2020
1. Fix the config include.

v1.4, 2020
1. Update adminer to 4.7.6 (https://github.com/vrana/adminer/releases/tag/v4.7.6).

v1.3, 2020
1. Add documentation.

v1.2, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.

v1.1, 2019
1. Update of Adminer to 4.7.5

v1.0, 2019
1. Initial release.
