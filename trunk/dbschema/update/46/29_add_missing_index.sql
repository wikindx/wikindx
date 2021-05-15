-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add a missing index on resourceusertagsResourceId.

CREATE INDEX `resourceusertagsResourceId` ON wkx_resource_user_tags (`resourceusertagsResourceId`);