-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Fix default values of users table

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersTemplateMenu = 0
WHEN usersTemplateMenu Is NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersTemplateMenu` int(11) NOT NULL DEFAULT 0;
