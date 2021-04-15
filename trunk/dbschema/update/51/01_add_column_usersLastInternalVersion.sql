-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Add column usersLastInternalVersion to users

ALTER TABLE wkx_users ADD COLUMN usersLastInternalVersion int(11) NOT NULL DEFAULT 0;
