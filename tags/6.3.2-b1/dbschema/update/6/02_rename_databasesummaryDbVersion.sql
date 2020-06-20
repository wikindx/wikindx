-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Rename databasesummaryDbVersion field
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary CHANGE `databasesummaryDbVersion` `databasesummarySoftwareVersion` varchar(16);
