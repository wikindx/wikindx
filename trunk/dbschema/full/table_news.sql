-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `wkx_news` (
  `newsId` int(11) NOT NULL AUTO_INCREMENT,
  `newsTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `newsNews` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `newsTimestamp` datetime DEFAULT current_timestamp(),
  `newsEmailSent` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  PRIMARY KEY (`newsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
