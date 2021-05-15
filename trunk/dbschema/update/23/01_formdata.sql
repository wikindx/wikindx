-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Add new table for form data

CREATE TABLE IF NOT EXISTS `wkx_form_data` (
  `formdataId` varchar(128) NOT NULL,
  `formdataData` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `formdataTimestamp` datetime DEFAULT current_timestamp() NOT NULL,
  PRIMARY KEY (`formdataId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
