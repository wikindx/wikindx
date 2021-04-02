-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Remove base64 encoding from collectionDefault field in collection table

UPDATE %%WIKINDX_DB_TABLEPREFIX%%collection
SET collectionDefault = FROM_BASE64(collectionDefault)
WHERE collectionDefault IS NOT NULL;
