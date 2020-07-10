-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Drop usersChangePasswordTimestamp column

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users
DROP COLUMN usersChangePasswordTimestamp;
