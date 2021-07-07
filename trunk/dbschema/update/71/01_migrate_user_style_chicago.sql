--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Change user style preference chicago to chicago-ft (rename, no feature change)
UPDATE users
SET usersStyle = 'chicago-ft'
WHERE usersStyle = 'chicago';
