-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Recreate an empty users_basket table
-- 

CREATE TABLE IF NOT EXISTS `users_basket` (
  	`usersbasketId` int(11) NOT NULL AUTO_INCREMENT,
    `usersbasketUserId` int(11) NOT NULL DEFAULT 0,
    `usersbasketBasket` MEDIUMTEXT COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    PRIMARY KEY (`usersbasketId`),
    KEY (`usersbasketUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
