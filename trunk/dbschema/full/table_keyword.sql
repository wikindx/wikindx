-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `keyword` (
  `keywordId` int(11) NOT NULL AUTO_INCREMENT,
  `keywordKeyword` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `keywordGlossary` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`keywordId`),
  KEY `keywordKeyword` (`keywordKeyword`(768))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
