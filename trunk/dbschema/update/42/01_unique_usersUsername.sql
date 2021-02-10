-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Recreate the usersUsernameUnique index with a BTREE type (bugfix #318)
-- The HASH type seems not well supported by InnoDB

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users DROP INDEX usersUsernameUnique;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users ADD UNIQUE KEY `usersUsernameUnique` ( usersUsername ASC );
