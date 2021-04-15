-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `wkx_user_kg_keywords` (
  `userkgkeywordsId` int(11) NOT NULL AUTO_INCREMENT,
  `userkgkeywordsKeywordGroupId` int(11) NOT NULL,
  `userkgkeywordsKeywordId` int(11) NOT NULL,
  PRIMARY KEY (`userkgkeywordsId`),
  KEY `userkgkeywordsKeywordGroupId` (`userkgkeywordsKeywordGroupId`),
  KEY `userkgkeywordsKeywordId` (`userkgkeywordsKeywordId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
