********************************************************************************
**                                 repairkit                                  **
**                               WIKINDX module                               **
********************************************************************************


Attempt fixes to a number of errors that can occur over time in the database.
You are always asked to confirm repairs before they are carried out and you
should always backup your database first.

1. Fix UTF-8.  When upgrading WIKINDX from v3.8.2 to v4.x, not all UTF8-encoded
database fields are properly dealt with and you may see characters similar to
'ã¼' or 'ã¶' etc.  in the WIKINDX. Additionally, as WIKINDX is a system that can
accept input from many different sources, character encoding can be corrupted
right from the start.

2. Sometimes rows required in other tables are not created when new resources
are added and this can lead to problems in searching etc.  If such missing rows
are identified, you will be asked to add them in with default (usually NULL)
data.

3. Fix totals of resources, quotes, paraphrases and musings in the
database_summary table.

4. Database structure fix. If a user has been using pre-release SVN code on a
production server (despite the warning not to do so), in some cases their
database structure does not match the final release database structure. This
fixes the issue.

The module registers itself in the 'admin' menu.

Unzip this file (with any directory structure) into plugins/repairkit/.
Thus, plugins/repairkit/index.php etc.

********************************************************************************

BUGS AND LIMITATIONS

- Dropping a PRIMARY index from an autoincremented field fails because the
  autoincrement property must be removed before the index, but but the heuristic
  is not fine enough to manage this case which can be considered rare.

- The previous error also implies that any field definition error which requires
  recreating an index on a autoincremented field will also fail.

********************************************************************************

CHANGELOG:

v1.8.4, 2020
1. Fix an error of version check.
2. Remove the code that fix UTF-8 encoding since the default encoding is now UTF-8.

v1.8.3, 2020
1. Add documentation.

v1.8.2, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.
2. Change version handling.

v1.8.1
1. Fix the DB Integrity check for tables defined without a primary key.
2. Fix the DB Integrity check when an index is missing in the current db.
3. Fix the DB Integrity check when an index is supernumerary in the current db.
4. Fix the version check.

v1.8.0
1. Transfer responsibility for creating the schema to an external tool and load the schema
   from a predefined location for all database schemas delivered with the core.
2. Adaptation for Wikindx 5.9.1.

v1.7.1
1. Added creators check: in some cases, 'resourcecreatorCreatorSurname' does not match the id in resourcecreatorCreatorMain.

v1.6.1
1. Corrected a misnamed, case-sensitive path

v1.6
1. Added database structure fix.

v1.5
1. Compliance with PHP 7.2

v1.4
1. Plugin now compatible with WIKINDX v5.x

v1.3
1. Added a fix for total resources, quotes, paraphrases and musings in database_summary table.
2. Plugin compatible only with WIKINDX v4.2.x
3. Added a further option to more stringently fix UTF-8 characters.

v1.2
1. Ensured that non-text fields in the database are skipped when fixing UTF-8.

v1.1
1. Initial release

--
Mark Grimshaw-Aagaard 2019.
