--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Change global style preference chicago to chicago-ft (rename, no feature change)
UPDATE config
SET configVarchar = 'chicago-ft'
WHERE
    configName = 'configStyle'
    AND configVarchar = 'chicago';
