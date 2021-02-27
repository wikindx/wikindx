-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add a FULLTEXT index on resourcecustomShort.
-- Add a FULLTEXT index on resourceNoSort.
-- Add a FULLTEXT index on resourceShortTitle.
-- Add a FULLTEXT index on resourceSubtitle.
-- Add a FULLTEXT index on resourceTitle.
-- Add a FULLTEXT index on resourceTitleSort.
-- Add a FULLTEXT index on resourceTransNoSort.
-- Add a FULLTEXT index on resourceTransShortTitle.
-- Add a FULLTEXT index on resourceTransSubtitle.
-- Add a FULLTEXT index on resourceTransTitle.
-- Add a FULLTEXT index on usertagsTag.

CREATE FULLTEXT INDEX `resourcecustomShort` ON %%WIKINDX_DB_TABLEPREFIX%%resource_custom (`resourcecustomShort`);
CREATE FULLTEXT INDEX `resourceNoSort` ON %%WIKINDX_DB_TABLEPREFIX%%resource (`resourceNoSort`);
CREATE FULLTEXT INDEX `resourceSubtitle` ON %%WIKINDX_DB_TABLEPREFIX%%resource (`resourceSubtitle`);
CREATE FULLTEXT INDEX `resourceTitle` ON %%WIKINDX_DB_TABLEPREFIX%%resource (`resourceTitle`);
CREATE FULLTEXT INDEX `resourceTitleSort` ON %%WIKINDX_DB_TABLEPREFIX%%resource (`resourceTitleSort`);
CREATE FULLTEXT INDEX `resourceTransNoSort` ON %%WIKINDX_DB_TABLEPREFIX%%resource (`resourceTransNoSort`);
CREATE FULLTEXT INDEX `resourceTransSubtitle` ON %%WIKINDX_DB_TABLEPREFIX%%resource (`resourceTransSubtitle`);
CREATE FULLTEXT INDEX `resourceTransTitle` ON %%WIKINDX_DB_TABLEPREFIX%%resource (`resourceTransTitle`);
CREATE FULLTEXT INDEX `usertagsTag` ON %%WIKINDX_DB_TABLEPREFIX%%user_tags (`usertagsTag`);
