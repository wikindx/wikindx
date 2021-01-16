-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_attachments` (
  `resourceattachmentsId` int(11) NOT NULL AUTO_INCREMENT,
  `resourceattachmentsResourceId` int(11) DEFAULT NULL,
  `resourceattachmentsHashFilename` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsFileName` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsFileType` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsFileSize` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsPrimary` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourceattachmentsTimestamp` datetime DEFAULT current_timestamp(),
  `resourceattachmentsEmbargo` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourceattachmentsEmbargoUntil` datetime DEFAULT current_timestamp(),
  `resourceattachmentsDescription` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourceattachmentsId`),
  KEY `resourceattachmentsResourceId` (`resourceattachmentsResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

