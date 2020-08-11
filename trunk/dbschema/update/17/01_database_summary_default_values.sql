-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Setup sane defaults values for database_summary table

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary MODIFY COLUMN `databasesummaryTotalResources` int(11) DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary MODIFY COLUMN `databasesummaryTotalQuotes` int(11) DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary MODIFY COLUMN `databasesummaryTotalParaphrases` int(11) DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary MODIFY COLUMN `databasesummaryTotalMusings` int(11) DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary MODIFY COLUMN `databasesummarySoftwareVersion` varchar(16) DEFAULT '0';
