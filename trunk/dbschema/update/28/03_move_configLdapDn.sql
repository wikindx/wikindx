--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Copy the value of configLdapDn to configLdapUserOu option
UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET
    configVarchar = IFNULL((
        SELECT configVarchar
        FROM %%WIKINDX_DB_TABLEPREFIX%%config
        WHERE configName = 'configLdapDn'
    ), configVarchar)
WHERE configName = 'configLdapUserOu';

-- Remove configLdapDn option
DELETE FROM %%WIKINDX_DB_TABLEPREFIX%%config
WHERE configName = 'configLdapDn';
