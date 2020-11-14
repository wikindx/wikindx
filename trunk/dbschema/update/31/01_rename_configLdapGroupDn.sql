--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Copy the value of LdapGroupCn to LdapGroupDn option
UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET
    configVarchar = IFNULL((
        SELECT configVarchar
        FROM %%WIKINDX_DB_TABLEPREFIX%%config
        WHERE configName = 'LdapGroupCn'
    ), configVarchar)
WHERE configName = 'LdapGroupDn';

-- Remove LdapGroupCn option
DELETE FROM %%WIKINDX_DB_TABLEPREFIX%%config
WHERE configName = 'LdapGroupCn';
