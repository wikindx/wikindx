********************************************************************************
**                                 db Adminer                                 **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5 and up.

Wikindx custom wrapper for Adminer with a selection of tested plugins for Adminer.

Unzip this file (with any directory structure) into plugins/dbadminer/.
Thus, plugins/dbadminer/index.php etc.

NB: the FillLoginForm class is customized to always fill the login form with the db configuration of Wikindx.

Adminer website: https://www.adminer.org/

********************************************************************************

CHANGELOG:

2020-06-24 : SEC : prevent direct access to subparts of adminer. Only the index.php
                   and adminer.php scripts can be called and it includes others.

v1.7, 2020
1. Update adminer to 4.7.7 (https://github.com/vrana/adminer/releases/tag/v4.7.7).

v1.6, 2020
1. Wikindx compatibility version 7.

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

--
Stéphane Aulery 2019
