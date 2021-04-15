-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Change the default language of a new user to "auto"
-- 
-- https://mathiasbynens.be/notes/mysql-utf8mb4

ALTER TABLE wkx_users MODIFY COLUMN `usersLanguage` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT 'auto';
