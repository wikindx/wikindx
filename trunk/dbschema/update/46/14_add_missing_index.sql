-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add a missing index on resourcelanguageResourceId (previous upgrade code missing).

CREATE INDEX `resourcelanguageResourceId` ON wkx_resource_language (`resourcelanguageResourceId`);
