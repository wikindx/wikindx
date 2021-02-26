
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Redefine in FULLTEXT type three indices not well upgraded (categoryCategory, keywordKeyword, resourceTitle).

CREATE FULLTEXT INDEX `keywordKeyword` ON %%WIKINDX_DB_TABLEPREFIX%%keyword (`keywordKeyword`);
