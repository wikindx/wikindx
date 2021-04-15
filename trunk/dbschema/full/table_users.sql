-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `wkx_users` (
  `usersId` int(11) NOT NULL AUTO_INCREMENT,
  `usersUsername` varchar(188) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `usersPassword` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `usersFullname` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `usersEmail` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `usersDepartment` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersInstitution` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersTimestamp` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usersAdmin` tinyint(1) NOT NULL DEFAULT 0,
  `usersCookie` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersPaging` int(11) NOT NULL DEFAULT 20,
  `usersPagingMaxLinks` int(11) NOT NULL DEFAULT 11,
  `usersPagingStyle` varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N',
  `usersStringLimit` int(11) NOT NULL DEFAULT 40,
  `usersLanguage` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'auto',
  `usersStyle` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'apa',
  `usersTemplate` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'default',
  `usersNotify` varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N',
  `usersNotifyAddEdit` varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'A',
  `usersNotifyThreshold` int(2) NOT NULL DEFAULT 0,
  `usersNotifyTimestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `usersNotifyDigestThreshold` int(11) NOT NULL DEFAULT 100,
  `usersPagingTagCloud` int(11) NOT NULL DEFAULT 100,
  `usersPasswordQuestion1` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `usersPasswordAnswer1` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `usersPasswordQuestion2` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `usersPasswordAnswer2` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `usersPasswordQuestion3` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `usersPasswordAnswer3` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `usersUseBibtexKey` tinyint(1) NOT NULL DEFAULT 0,
  `usersUseWikindxKey` tinyint(1) NOT NULL DEFAULT 0,
  `usersDisplayBibtexLink` tinyint(1) NOT NULL DEFAULT 0,
  `usersDisplayCmsLink` tinyint(1) NOT NULL DEFAULT 0,
  `usersCmsTag` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersIsCreator` int(11) DEFAULT NULL,
  `usersListlink` tinyint(1) NOT NULL DEFAULT 0,
  `usersTemplateMenu` int(11) NOT NULL DEFAULT 0,
  `usersGDPR` varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N',
  `usersBlock` varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N',
  `usersHomeBib` tinyint(1) NOT NULL DEFAULT 0,
  `usersBrowseBibliography` int(11) NOT NULL DEFAULT 0,
  `usersLastInternalVersion` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usersId`),
  UNIQUE KEY `usersUsernameUnique` (`usersUsername`),
  KEY `usersBlock` (`usersBlock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
