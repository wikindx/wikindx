-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Drop columns some fields of config table and notify table
-- 

SET NAMES latin1;
SET CHARACTER SET latin1;

DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%notify;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config DROP COLUMN kwBibliography;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users  DROP COLUMN kwBibliography;