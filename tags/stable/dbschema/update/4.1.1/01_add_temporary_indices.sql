-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Add temporary indices to speed up the upgrade
-- 

SET NAMES latin1;
SET CHARACTER SET latin1;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection ADD INDEX `collectionTitle` (collectionTitle);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection ADD INDEX `collectionTitleShort` (collectionTitleShort);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection ADD INDEX `collectionType` (collectionType);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator    ADD INDEX `firstname` (firstname);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator    ADD INDEX `surname` (surname);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator    ADD INDEX `initials` (initials);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator    ADD INDEX `creator` (creator);
