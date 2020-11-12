--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Remove LDAP protocol version option

DELETE FROM %%WIKINDX_DB_TABLEPREFIX%%config
WHERE configName = 'configLdapProtocolVersion'