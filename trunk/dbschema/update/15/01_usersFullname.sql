-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Reduce the size of usersUsername column (limited index size)
-- Add a unique constraint on usersUsername column

UPDATE wkx_users
SET usersFullname = ''
WHERE usersFullname IS NULL;

ALTER TABLE wkx_users MODIFY COLUMN `usersFullname` varchar(1020) NOT NULL;
