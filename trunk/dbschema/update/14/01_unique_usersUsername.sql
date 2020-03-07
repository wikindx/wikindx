-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Add a unique constraint on usersUsername column

CREATE UNIQUE INDEX `usersUsernameUnique` ON %%WIKINDX_DB_TABLEPREFIX%%users (`usersUsername`) USING HASH;
