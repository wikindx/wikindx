-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

SET NAMES utf8mb4 COLLATE 'utf8mb4_unicode_520_ci';
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO %%WIKINDX_DB_TABLEPREFIX%%category (
	categoryId,
	categoryCategory
) VALUES (
	1,
	'General'
);

INSERT INTO %%WIKINDX_DB_TABLEPREFIX%%version (
	versionComponentId,
	versionInternalVersion
) VALUES (
    'core',
	0
);
