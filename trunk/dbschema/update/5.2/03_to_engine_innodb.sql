-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Convert all tables to innodb engine
-- 

ALTER TABLE wkx_bibtex_string              ENGINE=InnoDB;
ALTER TABLE wkx_cache                      ENGINE=InnoDB;
ALTER TABLE wkx_category                   ENGINE=InnoDB;
ALTER TABLE wkx_collection                 ENGINE=InnoDB;
ALTER TABLE wkx_config                     ENGINE=InnoDB;
ALTER TABLE wkx_creator                    ENGINE=InnoDB;
ALTER TABLE wkx_custom                     ENGINE=InnoDB;
ALTER TABLE wkx_database_summary           ENGINE=InnoDB;
ALTER TABLE wkx_import_raw                 ENGINE=InnoDB;
ALTER TABLE wkx_keyword                    ENGINE=InnoDB;
ALTER TABLE wkx_language                   ENGINE=InnoDB;
ALTER TABLE wkx_news                       ENGINE=InnoDB;
ALTER TABLE wkx_publisher                  ENGINE=InnoDB;
ALTER TABLE wkx_resource                   ENGINE=InnoDB;
ALTER TABLE wkx_resource_attachments       ENGINE=InnoDB;
ALTER TABLE wkx_resource_category          ENGINE=InnoDB;
ALTER TABLE wkx_resource_creator           ENGINE=InnoDB;
ALTER TABLE wkx_resource_custom            ENGINE=InnoDB;
ALTER TABLE wkx_resource_keyword           ENGINE=InnoDB;
ALTER TABLE wkx_resource_language          ENGINE=InnoDB;
ALTER TABLE wkx_resource_metadata          ENGINE=InnoDB;
ALTER TABLE wkx_resource_misc              ENGINE=InnoDB;
ALTER TABLE wkx_resource_page              ENGINE=InnoDB;
ALTER TABLE wkx_resource_summary           ENGINE=InnoDB;
ALTER TABLE wkx_resource_text              ENGINE=InnoDB;
ALTER TABLE wkx_resource_timestamp         ENGINE=InnoDB;
ALTER TABLE wkx_resource_user_tags         ENGINE=InnoDB;
ALTER TABLE wkx_resource_year              ENGINE=InnoDB;
ALTER TABLE wkx_statistics                 ENGINE=InnoDB;
ALTER TABLE wkx_subcategory                ENGINE=InnoDB;
ALTER TABLE wkx_tag                        ENGINE=InnoDB;
ALTER TABLE wkx_user_bibliography          ENGINE=InnoDB;
ALTER TABLE wkx_user_bibliography_resource ENGINE=InnoDB;
ALTER TABLE wkx_user_groups                ENGINE=InnoDB;
ALTER TABLE wkx_user_groups_users          ENGINE=InnoDB;
ALTER TABLE wkx_user_register              ENGINE=InnoDB;
ALTER TABLE wkx_user_tags                  ENGINE=InnoDB;
ALTER TABLE wkx_users                      ENGINE=InnoDB;
