-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Finish the transfert of data between 
-- Drop column resource.url
-- Drop table resource_note after the transfert to resource_text
-- Drop table resource_abstract after the transfert to resource_text
-- 

SET NAMES latin1;
SET CHARACTER SET latin1;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource DROP COLUMN url;

DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_note;
DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_abstract;
