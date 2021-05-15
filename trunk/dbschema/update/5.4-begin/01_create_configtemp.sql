-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Create statistics table
-- Rename table attachments => resource_attachments
-- Redefine resource_attachments.filename size
-- 

CREATE TABLE IF NOT EXISTS `wkx_configtemp` (
	`configId` int(11) NOT NULL AUTO_INCREMENT,
	`configName` varchar(255) NOT NULL,
	`configInt` int(11) DEFAULT NULL,
	`configFloat` double DEFAULT NULL,
	`configVarchar` varchar(255) DEFAULT NULL,
	`configText` text DEFAULT NULL,
	`configBoolean` tinyint(1) DEFAULT NULL,
	`configDatetime` datetime DEFAULT NULL,
	PRIMARY KEY (`configId`)
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_unicode_ci;
