-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Initially set all users to default settings
-- Same for read-only users in config table
-- 

SET NAMES latin1;
SET CHARACTER SET latin1;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET
	usersStyle = 'APA',
	usersLanguage = 'en',
	usersTemplate = 'default',
	usersCookie = 'N';

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET
	configStyle = 'APA',
	configLanguage = 'en',
	configTemplate = 'default';
