-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Migrate MySQL db schema to utf8mb4 and utf8mb4_unicode_520_ci collation
-- 
-- https://mathiasbynens.be/notes/mysql-utf8mb4

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%bibtex_string              CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache                      CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%category                   CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection                 CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config                     CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator                    CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%custom                     CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary           CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%import_raw                 CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%keyword                    CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%language                   CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%news                       CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%publisher                  CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource                   CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments       CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_category          CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_creator           CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom            CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_keyword           CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_language          CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata          CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc              CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_page              CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_summary           CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text              CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_timestamp         CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_user_tags         CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year              CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%statistics                 CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%subcategory                CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%tag                        CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography          CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography_resource CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups                CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups_users          CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register              CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_tags                  CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users                      CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;