-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Fix default values of users table

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersTemplateMenu = 0
WHERE usersTemplateMenu IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersEmail = ''
WHERE usersEmail IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersTimestamp = CURRENT_TIMESTAMP
WHERE usersTimestamp IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersAdmin = 'N'
WHERE usersAdmin IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersAdmin =
	CASE usersAdmin
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersPaging = 20
WHERE usersPaging IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersPagingMaxLinks = 11
WHERE usersPagingMaxLinks IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersPagingStyle = 'N'
WHERE usersPagingStyle IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersStringLimit = 40
WHERE usersStringLimit IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersLanguage = 'auto'
WHERE usersLanguage IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersStyle = 'apa'
WHERE usersStyle IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersTemplate = 'default'
WHERE usersTemplate IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersNotify = 'N'
WHERE usersNotify IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersNotifyAddEdit = 'A'
WHERE usersNotifyAddEdit IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersNotifyThreshold = 0
WHERE usersNotifyThreshold IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersNotifyTimestamp = CURRENT_TIMESTAMP
WHERE usersNotifyTimestamp IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersNotifyDigestThreshold = 100
WHERE usersNotifyDigestThreshold IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersPagingTagCloud = 100
WHERE usersPagingTagCloud IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersPasswordQuestion1 = ''
WHERE usersPasswordQuestion1 IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersPasswordAnswer1 = ''
WHERE usersPasswordAnswer1 IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersPasswordQuestion2 = ''
WHERE usersPasswordQuestion2 IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersPasswordAnswer2 = ''
WHERE usersPasswordAnswer2 IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersPasswordQuestion3 = ''
WHERE usersPasswordQuestion3 IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersPasswordAnswer3 = ''
WHERE usersPasswordAnswer3 IS NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersUseBibtexKey =
	CASE usersUseBibtexKey
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersUseWikindxKey =
	CASE usersUseWikindxKey
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersDisplayBibtexLink =
	CASE usersDisplayBibtexLink
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersDisplayCmsLink =
	CASE usersDisplayCmsLink
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersListlink =
	CASE usersListlink
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;


ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersEmail varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersTimestamp datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp();
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersAdmin tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersPaging int(11) NOT NULL DEFAULT 20;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersPagingMaxLinks int(11) NOT NULL DEFAULT 11;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersPagingStyle varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersStringLimit int(11) NOT NULL DEFAULT 40;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersLanguage varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'auto';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersStyle varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'apa';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersTemplate varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'default';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersNotify varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersNotifyAddEdit varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'A';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersNotifyThreshold int(2) NOT NULL DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersNotifyTimestamp datetime NOT NULL DEFAULT current_timestamp();
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersNotifyDigestThreshold int(11) NOT NULL DEFAULT 100;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersPagingTagCloud int(11) NOT NULL DEFAULT 100;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersPasswordQuestion1 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersPasswordAnswer1 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersPasswordQuestion2 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersPasswordAnswer2 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersPasswordQuestion3 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersPasswordAnswer3 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersUseBibtexKey tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersUseWikindxKey tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersDisplayBibtexLink tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersDisplayCmsLink tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersListlink tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN usersTemplateMenu int(11) NOT NULL DEFAULT 0;
