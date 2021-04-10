-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%session` (
    -- Max length of a session id is 256 characters
    -- cf. https://www.php.net/manual/en/session.configuration.php#ini.session.sid-length
    `sessionId` VARCHAR(256) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    -- Auto update the last access timestamp on update or creation success
    `sessionLastAccessTimestamp` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    -- Session data serialized with serialize()
    `sessionData` LONGTEXT COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    PRIMARY KEY (`sessionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
