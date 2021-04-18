-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `creator` (
  `creatorId` int(11) NOT NULL AUTO_INCREMENT,
  `creatorSurname` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorFirstname` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorInitials` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorPrefix` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorSameAs` int(11) DEFAULT NULL,
  PRIMARY KEY (`creatorId`),
  KEY `creatorSurname` (`creatorSurname`(100)),
  KEY `creatorSameAs` (`creatorSameAs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
