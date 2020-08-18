-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Correct default value for user_kg_usergroups table

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_kg_usergroups MODIFY COLUMN `userkgusergroupsUserGroupId` int(11) DEFAULT NULL;
