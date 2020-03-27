-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Drop columns migrated at previous stages
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote      DROP COLUMN resourcequoteKeywords;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase DROP COLUMN resourceparaphraseKeywords;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing     DROP COLUMN resourcemusingKeywords;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography   DROP COLUMN userbibliographyBibliography;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups         DROP COLUMN usergroupsUserIds;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups         DROP COLUMN usergroupsBibliographyIds;
