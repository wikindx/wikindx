-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Create new statistics tables

CREATE TABLE IF NOT EXISTS `wkx_statistics_attachment_downloads` (
  `statisticsattachmentdownloadsId` int(11) NOT NULL AUTO_INCREMENT,
  `statisticsattachmentdownloadsResourceId` int(11) NOT NULL,
  `statisticsattachmentdownloadsAttachmentId` int(11) NOT NULL,
  `statisticsattachmentdownloadsCount` int(11) DEFAULT 0,
  `statisticsattachmentdownloadsMonth` int(11) DEFAULT 0,
  PRIMARY KEY (`statisticsattachmentdownloadsId`),
  KEY `statisticsattachmentdownloadsAttachmentId` (`statisticsattachmentdownloadsAttachmentId`),
  KEY `statisticsattachmentdownloadsResourceId` (`statisticsattachmentdownloadsResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE IF NOT EXISTS `wkx_statistics_resource_views` (
  `statisticsresourceviewsId` int(11) NOT NULL AUTO_INCREMENT,
  `statisticsresourceviewsResourceId` int(11) NOT NULL,
  `statisticsresourceviewsCount` int(11) DEFAULT 0,
  `statisticsresourceviewsMonth` int(11) DEFAULT 0,
  PRIMARY KEY (`statisticsresourceviewsId`),
  KEY `statisticsresourceviewsResourceId` (`statisticsresourceviewsResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
