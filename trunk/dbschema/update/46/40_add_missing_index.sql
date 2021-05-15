-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add a missing index on usergroupsusersGroupId.

CREATE INDEX `usergroupsusersGroupId` ON wkx_user_groups_users (`usergroupsusersGroupId`);
