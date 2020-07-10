-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Fix the code of languages and locales for gettext
-- 
-- https://mathiasbynens.be/notes/mysql-utf8mb4

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configVarchar =
	CASE
		WHEN configVarchar = 'de' THEN 'de_DE'
		WHEN configVarchar = 'en' THEN 'en_GB'
		WHEN configVarchar = 'es' THEN 'es_ES'
		WHEN configVarchar = 'fr' THEN 'fr_FR'
		WHEN configVarchar = 'it' THEN 'it_IT'
		WHEN configVarchar = 'ru' THEN 'ru_RU'
		ELSE configVarchar
	END
WHERE configName = 'configLanguage';

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersLanguage =
	CASE
		WHEN usersLanguage = 'de' THEN 'de_DE'
		WHEN usersLanguage = 'en' THEN 'en_GB'
		WHEN usersLanguage = 'es' THEN 'es_ES'
		WHEN usersLanguage = 'fr' THEN 'fr_FR'
		WHEN usersLanguage = 'it' THEN 'it_IT'
		WHEN usersLanguage = 'ru' THEN 'ru_RU'
		ELSE usersLanguage
	END;
