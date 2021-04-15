-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add a missing index on resourcemiscPeerReviewed.

CREATE INDEX `resourcemiscPeerReviewed` ON wkx_resource_misc (`resourcemiscPeerReviewed`);
