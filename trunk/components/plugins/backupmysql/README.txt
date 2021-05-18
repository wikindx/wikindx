********************************************************************************
**                           MySQL backup database                            **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5 and up.
Results may be unexpected if used with a lower version.

Backup MySQL database

The module registers itself in the 'Admin' menu.

Dumps are gzipped and placed in plugins/backupmysql/dumps/ which must be
writeable by the web server user.

Unzip this file (with any directory structure) into plugins/backupmysql/.
Thus, plugins/backupmysql/index.php etc.

THIS TOOL DUMPS ONLY MYSQL DATABASE IN A VERY LIMITED WAY.
IT IS LIMITED TO TABLES, THEIR DATAS, AND THEIR INDEX.
IT DOES NOT INCLUDE FOREIGN KEYS, VIEWS, AMD OTHER TYPES
OF OBJECTS IN A DATABASE. IT IS ONLY PROVIDED AS A HELP
TO MEET THE SPECIFIC NEEDS OF WIKINDX 5.

IF YOU NEED TO SAVE AN ENTIRE DATABASE BETTER AND MORE COMPLETELY,
USE ONE OF THESE SOFTWARE:

 - mysqldump (need a command line access, essential for big db):
   https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html
 - phpMyAdmin (often provided by your provider): https://www.phpmyadmin.net/
 - Adminer (single file installable): https://www.adminer.org/

If YOU INTALL BY YOURSELF phpMyAdmin OR Adminer, REMEMBER TO SECURE ACCESS
WITH AN HTTP auth, HTTPS, AND AN IP RESTRICTION IF POSSIBLE.

Uses code adapted from:
http://www.phpclasses.org/package/2779-PHP-Backup-MySQL-databases-to-files-with-SQL-commands.html

********************************************************************************

CHANGELOG:

2021-05-18 : CHG : cleaning.
2021-05-16 : CHG : change of the compatibility version (11) (removal of importexportbib plugin).
2021-04-18 : CHG : change of the compatibility version (10) (removal of the database prefix).
2020-12-21 : CHG : make PHP includes independent of the web server layout (#244).
2020-12-21 : CHG : reformat source code to the prefered if/then/else style.
2020-12-21 : FIX : Send HTTP Error Code 404 for a missing download.
2020-12-21 : CHG : full French translation.
2020-07-11 : CHG : relicencing under ISC License terms.

v1.9, 2020
1. Stored databases can now be renamed.
2. WIKINDX compatibility version 8.

v1.8, 2020
1. WIKINDX compatibility version 7.

v1.7, 2020
1. Add documentation.

v1.6, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.

v1.5 ~ 2019
1. Adaptation for WIKINDX 5.9.1.

v1.4 ~ 10th Decemper 2018
1. Dump only WIKINDX tables if the database is shared with an other software
2. Support of PostgreSQL and SQLite database engines removed (not supported by the core)

v1.3
1. First support of PostgreSQL and SQLite database engines (Warning: highly experimental)

v1.2
1. Plugin now compatible with WIKINDX v5.x
2. Added list and delete functions

v1.1 ~ 14th April 2013
1. Initial release
