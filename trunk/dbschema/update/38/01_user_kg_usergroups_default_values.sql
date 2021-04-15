-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Correct default value for user_kg_usergroups table
-- Fix the wrong definition of this field in 6.4.0 (36) for a fisrt installation

ALTER TABLE wkx_user_kg_usergroups MODIFY COLUMN `userkgusergroupsUserGroupId` int(11) DEFAULT NULL;
