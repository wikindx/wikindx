-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Convert all tables to innodb engine
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%bibtex_string              ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache                      ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%category                   ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection                 ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config                     ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator                    ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%custom                     ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary           ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%import_raw                 ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%keyword                    ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%language                   ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%news                       ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%publisher                  ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource                   ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments       ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_category          ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_creator           ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom            ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_keyword           ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_language          ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata          ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc              ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_page              ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_summary           ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text              ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_timestamp         ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_user_tags         ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year              ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%statistics                 ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%subcategory                ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%tag                        ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography          ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography_resource ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups                ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups_users          ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register              ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_tags                  ENGINE=InnoDB;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users                      ENGINE=InnoDB;
