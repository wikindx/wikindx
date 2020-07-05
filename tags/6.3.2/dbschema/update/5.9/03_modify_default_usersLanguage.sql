-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Change the default language of a new user to "auto"
-- 
-- https://mathiasbynens.be/notes/mysql-utf8mb4

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersLanguage` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT 'auto';
