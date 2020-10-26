--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- 1) Introduce a toggle allowing Home Page filtering by currently selected bibliography.
-- 0   -  produce the normal Home Page
-- 1   -  produce Home Page based on currently selected bibliography

-- 2) Switch from using session based ('mywikindx_Bibliography_use')storage of currently selected bibliography to database based storage ('usersBrowseBibliography').


ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users ADD COLUMN usersHomeBib tinyint(1) DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users ADD COLUMN usersBrowseBibliography int(11) default -1;
