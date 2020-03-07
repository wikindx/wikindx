-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Add missing indices and correct some indices (varchar indices needing a prefix to ensure the index is not oversize).
-- Indices that ahve their prefix changed are dropped in UPDATEDATABASE:correctIndices()
-- 
-- https://dev.mysql.com/doc/refman/5.7/en/char.html
-- https://dev.mysql.com/doc/refman/5.7/en/blob.html
-- https://dev.mysql.com/doc/refman/5.7/en/storage-requirements.html#data-types-storage-reqs-strings

CREATE UNIQUE INDEX `usersUsernameUnique` ON %%WIKINDX_DB_TABLEPREFIX%%users (`usersUsername`) USING HASH;
