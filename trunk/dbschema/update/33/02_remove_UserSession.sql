-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Remove the UserSession column of the users table

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users
DROP COLUMN usersUserSession;
