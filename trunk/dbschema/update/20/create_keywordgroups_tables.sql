-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Create new keyword groups tables

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_keywordgroups` (
  `userkeywordgroupsId` int(11) NOT NULL AUTO_INCREMENT,
  `userkeywordgroupsUserId` int(11) NOT NULL,
  `userkeywordgroupsName` varchar(1020) NOT NULL,
  `userkeywordgroupsDescription` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`userkeywordgroupsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_kg_keywords` (
  `userkgkeywordsId` int(11) NOT NULL AUTO_INCREMENT,
  `userkgkeywordsKeywordGroupId` int(11) NOT NULL,
  `userkgkeywordsKeywordId` int(11) NOT NULL,
  PRIMARY KEY (`userkgkeywordsId`),
  KEY `userkgkeywordsKeywordId` (`userkgkeywordsKeywordId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_kg_usergroups` (
  `userkgusergroupsId` int(11) NOT NULL AUTO_INCREMENT,
  `userkgusergroupsKeywordGroupId` int(11) NOT NULL,
  `userkgusergroupsUserGroupId` int(11) NOT NULL,
  PRIMARY KEY (`userkgusergroupsId`),
  KEY `userkgusergroupsUserGroupId` (`userkgusergroupsUserGroupId`),
  KEY `userkgusergroupsKeywordGroupId` (`userkgusergroupsKeywordGroupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
