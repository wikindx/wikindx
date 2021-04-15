-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Fix a mistake on the length of resourcemetadataPrivate field
-- 

ALTER TABLE wkx_resource_metadata MODIFY COLUMN `resourcemetadataPrivate` varchar(1) DEFAULT 'N';
