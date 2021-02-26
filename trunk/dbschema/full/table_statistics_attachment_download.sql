-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%statistics_attachment_downloads` (
  `statisticsattachmentdownloadsId` int(11) NOT NULL AUTO_INCREMENT,
  `statisticsattachmentdownloadsResourceId` int(11) NOT NULL,
  `statisticsattachmentdownloadsAttachmentId` int(11) NOT NULL,
  `statisticsattachmentdownloadsCount` int(11) DEFAULT 0,
  `statisticsattachmentdownloadsMonth` int(11) DEFAULT 0,
  PRIMARY KEY (`statisticsattachmentdownloadsId`),
  KEY `statisticsattachmentdownloadsAttachmentId` (`statisticsattachmentdownloadsAttachmentId`),
  KEY `statisticsattachmentdownloadsResourceId` (`statisticsattachmentdownloadsResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
