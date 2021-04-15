-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `wkx_resource_attachments` (
  `resourceattachmentsId` int(11) NOT NULL AUTO_INCREMENT,
  `resourceattachmentsResourceId` int(11) DEFAULT NULL,
  `resourceattachmentsHashFilename` varchar(40) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsFileName` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsFileType` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsFileSize` int(11) NOT NULL DEFAULT 0,
  `resourceattachmentsPrimary` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourceattachmentsTimestamp` datetime DEFAULT current_timestamp(),
  `resourceattachmentsEmbargo` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourceattachmentsEmbargoUntil` datetime DEFAULT current_timestamp(),
  `resourceattachmentsDescription` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourceattachmentsId`),
  KEY `resourceattachmentsResourceId` (`resourceattachmentsResourceId`),
  KEY `resourceattachmentsHashFilename` (`resourceattachmentsHashFilename`),
  KEY `resourceattachmentsPrimary` (`resourceattachmentsPrimary`),
  KEY `resourceattachmentsEmbargo` (`resourceattachmentsEmbargo`),
  KEY `resourceattachmentsTimestamp` (`resourceattachmentsTimestamp`),
  KEY `resourceattachmentsEmbargoUntil` (`resourceattachmentsEmbargoUntil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
