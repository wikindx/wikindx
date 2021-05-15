-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Add column usersDisplayResourceStatistics to users

ALTER TABLE users ADD COLUMN `usersDisplayResourceStatistics` tinyint(1) NOT NULL DEFAULT 0;
