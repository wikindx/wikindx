--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Remove unwanted rows in user_bibliography_resource

DELETE FROM %%WIKINDX_DB_TABLEPREFIX%%user_bibliography_resource
WHERE userbibliographyresourceBibliographyId IN (-1, -2);
