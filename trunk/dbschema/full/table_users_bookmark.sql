-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Recreate an empty users_bookmarks table
-- 

CREATE TABLE IF NOT EXISTS `users_bookmarks` (
  	`usersbookmarksId` int(11) NOT NULL AUTO_INCREMENT,
    `usersbookmarksUserId` int(11) NOT NULL DEFAULT 0,
    `usersbookmarksBookmarks` LONGTEXT COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    PRIMARY KEY (`usersbookmarksId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
