-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Reduce the size of usersUsername column (limited index size)
-- Add a unique constraint on usersUsername column

ALTER TABLE wkx_users MODIFY COLUMN `usersUsername` varchar(188) NOT NULL;

CREATE UNIQUE INDEX `usersUsernameUnique` ON wkx_users (`usersUsername`) USING HASH;

