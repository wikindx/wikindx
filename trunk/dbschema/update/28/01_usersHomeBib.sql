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


ALTER TABLE wkx_users ADD COLUMN usersHomeBib tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE wkx_users ADD COLUMN usersBrowseBibliography int(11) NOT NULL DEFAULT 0;
