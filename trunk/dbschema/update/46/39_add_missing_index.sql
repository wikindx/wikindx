-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add a missing index on usergroupsAdminId.

CREATE INDEX `usergroupsAdminId` ON %%WIKINDX_DB_TABLEPREFIX%%user_groups (`usergroupsAdminId`);
