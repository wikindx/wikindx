--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Copy the value of configLdapDn to configLdapUserOu option
UPDATE %%WIKINDX_DB_TABLEPREFIX%%config AS t1
    INNER JOIN %%WIKINDX_DB_TABLEPREFIX%%config AS t2
        ON t1.configName = 'configLdapDn'
            AND t2.configName = 'configLdapUserOu'
SET t2.configVarchar = t1.configVarchar;

-- Remove configLdapDn option
DELETE FROM %%WIKINDX_DB_TABLEPREFIX%%config
WHERE configName = 'configLdapDn';

