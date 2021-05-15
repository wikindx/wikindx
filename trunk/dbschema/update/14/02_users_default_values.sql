-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Fix default values of users table

UPDATE wkx_users
SET usersTemplateMenu = 0
WHERE usersTemplateMenu IS NULL;

UPDATE wkx_users
SET usersEmail = ''
WHERE usersEmail IS NULL;

UPDATE wkx_users
SET usersTimestamp = CURRENT_TIMESTAMP
WHERE usersTimestamp IS NULL;

UPDATE wkx_users
SET usersAdmin = 'N'
WHERE usersAdmin IS NULL;

UPDATE wkx_users
SET usersAdmin =
	CASE usersAdmin
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;

UPDATE wkx_users
SET usersPaging = 20
WHERE usersPaging IS NULL;

UPDATE wkx_users
SET usersPagingMaxLinks = 11
WHERE usersPagingMaxLinks IS NULL;

UPDATE wkx_users
SET usersPagingStyle = 'N'
WHERE usersPagingStyle IS NULL;

UPDATE wkx_users
SET usersStringLimit = 40
WHERE usersStringLimit IS NULL;

UPDATE wkx_users
SET usersLanguage = 'auto'
WHERE usersLanguage IS NULL;

UPDATE wkx_users
SET usersStyle = 'apa'
WHERE usersStyle IS NULL;

UPDATE wkx_users
SET usersTemplate = 'default'
WHERE usersTemplate IS NULL;

UPDATE wkx_users
SET usersNotify = 'N'
WHERE usersNotify IS NULL;

UPDATE wkx_users
SET usersNotifyAddEdit = 'A'
WHERE usersNotifyAddEdit IS NULL;

UPDATE wkx_users
SET usersNotifyThreshold = 0
WHERE usersNotifyThreshold IS NULL;

UPDATE wkx_users
SET usersNotifyTimestamp = CURRENT_TIMESTAMP
WHERE usersNotifyTimestamp IS NULL;

UPDATE wkx_users
SET usersNotifyDigestThreshold = 100
WHERE usersNotifyDigestThreshold IS NULL;

UPDATE wkx_users
SET usersPagingTagCloud = 100
WHERE usersPagingTagCloud IS NULL;

UPDATE wkx_users
SET usersPasswordQuestion1 = ''
WHERE usersPasswordQuestion1 IS NULL;

UPDATE wkx_users
SET usersPasswordAnswer1 = ''
WHERE usersPasswordAnswer1 IS NULL;

UPDATE wkx_users
SET usersPasswordQuestion2 = ''
WHERE usersPasswordQuestion2 IS NULL;

UPDATE wkx_users
SET usersPasswordAnswer2 = ''
WHERE usersPasswordAnswer2 IS NULL;

UPDATE wkx_users
SET usersPasswordQuestion3 = ''
WHERE usersPasswordQuestion3 IS NULL;

UPDATE wkx_users
SET usersPasswordAnswer3 = ''
WHERE usersPasswordAnswer3 IS NULL;

UPDATE wkx_users
SET usersUseBibtexKey =
	CASE usersUseBibtexKey
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;

UPDATE wkx_users
SET usersUseWikindxKey =
	CASE usersUseWikindxKey
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;

UPDATE wkx_users
SET usersDisplayBibtexLink =
	CASE usersDisplayBibtexLink
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;

UPDATE wkx_users
SET usersDisplayCmsLink =
	CASE usersDisplayCmsLink
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;

UPDATE wkx_users
SET usersListlink =
	CASE usersListlink
		WHEN 'N' THEN '0'
		WHEN 'Y' THEN '1'
		ELSE '0'
	END;


ALTER TABLE wkx_users MODIFY COLUMN usersEmail varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE wkx_users MODIFY COLUMN usersTimestamp datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp();
ALTER TABLE wkx_users MODIFY COLUMN usersAdmin tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE wkx_users MODIFY COLUMN usersPaging int(11) NOT NULL DEFAULT 20;
ALTER TABLE wkx_users MODIFY COLUMN usersPagingMaxLinks int(11) NOT NULL DEFAULT 11;
ALTER TABLE wkx_users MODIFY COLUMN usersPagingStyle varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N';
ALTER TABLE wkx_users MODIFY COLUMN usersStringLimit int(11) NOT NULL DEFAULT 40;
ALTER TABLE wkx_users MODIFY COLUMN usersLanguage varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'auto';
ALTER TABLE wkx_users MODIFY COLUMN usersStyle varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'apa';
ALTER TABLE wkx_users MODIFY COLUMN usersTemplate varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'default';
ALTER TABLE wkx_users MODIFY COLUMN usersNotify varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N';
ALTER TABLE wkx_users MODIFY COLUMN usersNotifyAddEdit varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'A';
ALTER TABLE wkx_users MODIFY COLUMN usersNotifyThreshold int(2) NOT NULL DEFAULT 0;
ALTER TABLE wkx_users MODIFY COLUMN usersNotifyTimestamp datetime NOT NULL DEFAULT current_timestamp();
ALTER TABLE wkx_users MODIFY COLUMN usersNotifyDigestThreshold int(11) NOT NULL DEFAULT 100;
ALTER TABLE wkx_users MODIFY COLUMN usersPagingTagCloud int(11) NOT NULL DEFAULT 100;
ALTER TABLE wkx_users MODIFY COLUMN usersPasswordQuestion1 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE wkx_users MODIFY COLUMN usersPasswordAnswer1 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE wkx_users MODIFY COLUMN usersPasswordQuestion2 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE wkx_users MODIFY COLUMN usersPasswordAnswer2 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE wkx_users MODIFY COLUMN usersPasswordQuestion3 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE wkx_users MODIFY COLUMN usersPasswordAnswer3 varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
ALTER TABLE wkx_users MODIFY COLUMN usersUseBibtexKey tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE wkx_users MODIFY COLUMN usersUseWikindxKey tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE wkx_users MODIFY COLUMN usersDisplayBibtexLink tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE wkx_users MODIFY COLUMN usersDisplayCmsLink tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE wkx_users MODIFY COLUMN usersListlink tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE wkx_users MODIFY COLUMN usersTemplateMenu int(11) NOT NULL DEFAULT 0;
