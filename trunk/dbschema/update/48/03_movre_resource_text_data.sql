-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Move the data from the old table to resource_text

INSERT INTO wkx_resource_text (
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
    FROM wkx_resource_text_48;
