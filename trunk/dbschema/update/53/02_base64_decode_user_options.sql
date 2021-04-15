-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Remove base64 encoding from global options

UPDATE wkx_users
SET usersCmsTag = FROM_BASE64(usersCmsTag)
WHERE usersCmsTag IS NOT NULL;
