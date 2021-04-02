-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Move the data from the old table to resource_text

INSERT INTO %%WIKINDX_DB_TABLEPREFIX%%resource_text (
    resourcetextId,
    resourcetextAddUserIdNote,
    resourcetextEditUserIdNote,
    resourcetextAddUserIdAbstract,
    resourcetextEditUserIdAbstract,
    resourcetextNote,
    resourcetextAbstract
)
    SELECT
        resourcetextId,
        resourcetextAddUserIdNote,
        resourcetextEditUserIdNote,
        resourcetextAddUserIdAbstract,
        resourcetextEditUserIdAbstract,
        resourcetextNote,
        resourcetextAbstract
    FROM %%WIKINDX_DB_TABLEPREFIX%%resource_text_48;
