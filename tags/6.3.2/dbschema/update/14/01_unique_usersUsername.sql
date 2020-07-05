-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
--
-- Reduce the size of usersUsername column (limited index size)
-- Add a unique constraint on usersUsername column

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersUsername` varchar(188) NOT NULL;

CREATE UNIQUE INDEX `usersUsernameUnique` ON %%WIKINDX_DB_TABLEPREFIX%%users (`usersUsername`) USING HASH;

