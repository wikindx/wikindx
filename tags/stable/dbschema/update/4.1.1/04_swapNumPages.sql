-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Switches the numPages data for books and theses from resourceField6 to resourcemiscField6
-- 

UPDATE %%WIKINDX_DB_TABLEPREFIX%%resource AS r
INNER JOIN %%WIKINDX_DB_TABLEPREFIX%%resource_misc AS m ON r.id = m.id
SET m.miscField6 = r.field6
WHERE
	r.field6 IS NOT NULL
	AND r.type IN ('book', 'thesis');

UPDATE %%WIKINDX_DB_TABLEPREFIX%%resource
SET r.field6 = NULL
WHERE
	r.field6 IS NOT NULL
	AND r.type IN ('book', 'thesis');
