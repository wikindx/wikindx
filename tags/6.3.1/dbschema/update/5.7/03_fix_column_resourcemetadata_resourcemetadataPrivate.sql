-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Fix a mistake on the length of resourcemetadataPrivate field
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata MODIFY COLUMN `resourcemetadataPrivate` varchar(1) DEFAULT 'N';
