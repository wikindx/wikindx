-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Change the name of all tables to lower case (workaround for mySQL engine on case sensitive files systems)
-- Be careful to keep the quotes as they are for the renaming to work : no quotes for the initial name, with quotes for the target name
-- It's also important to do it in two stage, otherwish the name don't change on windows
--  
-- NB: Windows MySQL lowercases any table name and Linux is case sensitive
-- To be sure, it is necessary to lowercase all table elements
-- 

ALTER TABLE wkx_bibtex_string                    RENAME `wkx_96e6bdf40a134c97809d40b1d5ffb700`;
ALTER TABLE wkx_cache                            RENAME `wkx_1f5610ddf465433c8adbb9740dfb755f`;
ALTER TABLE wkx_category                         RENAME `wkx_48eda791e04241adbf01f20828acf510`;
ALTER TABLE wkx_collection                       RENAME `wkx_9c9994d44d344928a4dd7f4a5477cf46`;
ALTER TABLE wkx_config                           RENAME `wkx_e6d131c5ee6644d18f8af7f8f7294b6f`;
ALTER TABLE wkx_creator                          RENAME `wkx_0e4c2f8bc23f42959a1a0b1839e05867`;
ALTER TABLE wkx_custom                           RENAME `wkx_e4054f20a5f3471d81654daa425a1a07`;
ALTER TABLE wkx_database_summary                 RENAME `wkx_b10b54238c8d4eb08a44e52fccb45bdc`;
ALTER TABLE wkx_import_raw                       RENAME `wkx_22def90cbfe24d4d94138c9478f84775`;
ALTER TABLE wkx_keyword                          RENAME `wkx_6e9ba07058134fc8815d9947a97a0852`;
ALTER TABLE wkx_language                         RENAME `wkx_c407e449e6a94b86815971f3a0e0e0b4`;
ALTER TABLE wkx_news                             RENAME `wkx_86e88c587c4b48aab014fa50c9c775e2`;
-- ALTER TABLE wkx_plugin_soundexplorer             RENAME `wkx_4fc387ba1ae34ac28e6dee712679d7b5`;
ALTER TABLE wkx_publisher                        RENAME `wkx_9bf06154cf5b41f6847c353f18ec8f67`;
ALTER TABLE wkx_resource                         RENAME `wkx_de9ec4380c724fc2934b24f283465be7`;
ALTER TABLE wkx_resource_attachments             RENAME `wkx_59177497fcde4729bd01b90b220012ae`;
ALTER TABLE wkx_resource_category                RENAME `wkx_e8c2f4279de647dc89367406d70b325b`;
ALTER TABLE wkx_resource_creator                 RENAME `wkx_bd5d6598e912463fa07dbdf25a5e6a88`;
ALTER TABLE wkx_resource_custom                  RENAME `wkx_354da1f09b8e4ad5a4af39f8f7688d92`;
ALTER TABLE wkx_resource_keyword                 RENAME `wkx_2e1057454e234a3aa255722d6b3b778f`;
ALTER TABLE wkx_resource_language                RENAME `wkx_f30115696d13452aa4f3526b25c157ce`;
ALTER TABLE wkx_resource_metadata                RENAME `wkx_b55d4f5de9944f9e826e676c52d3ecff`;
ALTER TABLE wkx_resource_misc                    RENAME `wkx_44db90345b8d44da84abfb9388b2a8a4`;
ALTER TABLE wkx_resource_page                    RENAME `wkx_ae8d10bfe8aa43a49a9b15be5d17fd7b`;
ALTER TABLE wkx_resource_summary                 RENAME `wkx_32635f292702449980a0936294321d04`;
ALTER TABLE wkx_resource_text                    RENAME `wkx_7b94473afaf7436e89c164061731b75d`;
ALTER TABLE wkx_resource_timestamp               RENAME `wkx_197d47085aac42f48adf09da10a04e68`;
ALTER TABLE wkx_resource_user_tags               RENAME `wkx_5b8d59cd555548888cd117998e852d51`;
ALTER TABLE wkx_resource_year                    RENAME `wkx_3416e12ac0c744519bc2f6656a43c11f`;
ALTER TABLE wkx_statistics                       RENAME `wkx_04a6e883b2af4ee7ac203e32e747c575`;
ALTER TABLE wkx_subcategory                      RENAME `wkx_0a3c535b9e974dd29adc1c7db3041647`;
ALTER TABLE wkx_tag                              RENAME `wkx_b33e1fa74fc049bbbb20722854a60f6d`;
ALTER TABLE wkx_users                            RENAME `wkx_0ff24f0e8e244131ad1299770a12dfc4`;
ALTER TABLE wkx_user_bibliography                RENAME `wkx_b95c1e9b483a481da3d374000ab26a0a`;
ALTER TABLE wkx_user_bibliography_resource       RENAME `wkx_7dfda1dc0b7f487ba26bae2e7ee94f74`;
ALTER TABLE wkx_user_groups                      RENAME `wkx_21919c05adda4f6d97081630ac0b6e89`;
ALTER TABLE wkx_user_groups_users                RENAME `wkx_2d3c86f204334d6c9a52b269b30ac9cb`;
ALTER TABLE wkx_user_register                    RENAME `wkx_91bae96a5ee54a1a87be2b352fc7d27d`;
ALTER TABLE wkx_user_tags                        RENAME `wkx_fa98a5d65e2949759b844da472d16f4b`;


ALTER TABLE wkx_96e6bdf40a134c97809d40b1d5ffb700 RENAME `wkx_bibtex_string`;
ALTER TABLE wkx_1f5610ddf465433c8adbb9740dfb755f RENAME `wkx_cache`;
ALTER TABLE wkx_48eda791e04241adbf01f20828acf510 RENAME `wkx_category`;
ALTER TABLE wkx_9c9994d44d344928a4dd7f4a5477cf46 RENAME `wkx_collection`;
ALTER TABLE wkx_e6d131c5ee6644d18f8af7f8f7294b6f RENAME `wkx_config`;
ALTER TABLE wkx_0e4c2f8bc23f42959a1a0b1839e05867 RENAME `wkx_creator`;
ALTER TABLE wkx_e4054f20a5f3471d81654daa425a1a07 RENAME `wkx_custom`;
ALTER TABLE wkx_b10b54238c8d4eb08a44e52fccb45bdc RENAME `wkx_database_summary`;
ALTER TABLE wkx_22def90cbfe24d4d94138c9478f84775 RENAME `wkx_import_raw`;
ALTER TABLE wkx_6e9ba07058134fc8815d9947a97a0852 RENAME `wkx_keyword`;
ALTER TABLE wkx_c407e449e6a94b86815971f3a0e0e0b4 RENAME `wkx_language`;
ALTER TABLE wkx_86e88c587c4b48aab014fa50c9c775e2 RENAME `wkx_news`;
-- ALTER TABLE wkx_4fc387ba1ae34ac28e6dee712679d7b5 RENAME `wkx_plugin_soundexplorer`;
ALTER TABLE wkx_9bf06154cf5b41f6847c353f18ec8f67 RENAME `wkx_publisher`;
ALTER TABLE wkx_de9ec4380c724fc2934b24f283465be7 RENAME `wkx_resource`;
ALTER TABLE wkx_59177497fcde4729bd01b90b220012ae RENAME `wkx_resource_attachments`;
ALTER TABLE wkx_e8c2f4279de647dc89367406d70b325b RENAME `wkx_resource_category`;
ALTER TABLE wkx_bd5d6598e912463fa07dbdf25a5e6a88 RENAME `wkx_resource_creator`;
ALTER TABLE wkx_354da1f09b8e4ad5a4af39f8f7688d92 RENAME `wkx_resource_custom`;
ALTER TABLE wkx_2e1057454e234a3aa255722d6b3b778f RENAME `wkx_resource_keyword`;
ALTER TABLE wkx_f30115696d13452aa4f3526b25c157ce RENAME `wkx_resource_language`;
ALTER TABLE wkx_b55d4f5de9944f9e826e676c52d3ecff RENAME `wkx_resource_metadata`;
ALTER TABLE wkx_44db90345b8d44da84abfb9388b2a8a4 RENAME `wkx_resource_misc`;
ALTER TABLE wkx_ae8d10bfe8aa43a49a9b15be5d17fd7b RENAME `wkx_resource_page`;
ALTER TABLE wkx_32635f292702449980a0936294321d04 RENAME `wkx_resource_summary`;
ALTER TABLE wkx_7b94473afaf7436e89c164061731b75d RENAME `wkx_resource_text`;
ALTER TABLE wkx_197d47085aac42f48adf09da10a04e68 RENAME `wkx_resource_timestamp`;
ALTER TABLE wkx_5b8d59cd555548888cd117998e852d51 RENAME `wkx_resource_user_tags`;
ALTER TABLE wkx_3416e12ac0c744519bc2f6656a43c11f RENAME `wkx_resource_year`;
ALTER TABLE wkx_04a6e883b2af4ee7ac203e32e747c575 RENAME `wkx_statistics`;
ALTER TABLE wkx_0a3c535b9e974dd29adc1c7db3041647 RENAME `wkx_subcategory`;
ALTER TABLE wkx_b33e1fa74fc049bbbb20722854a60f6d RENAME `wkx_tag`;
ALTER TABLE wkx_0ff24f0e8e244131ad1299770a12dfc4 RENAME `wkx_users`;
ALTER TABLE wkx_b95c1e9b483a481da3d374000ab26a0a RENAME `wkx_user_bibliography`;
ALTER TABLE wkx_7dfda1dc0b7f487ba26bae2e7ee94f74 RENAME `wkx_user_bibliography_resource`;
ALTER TABLE wkx_21919c05adda4f6d97081630ac0b6e89 RENAME `wkx_user_groups`;
ALTER TABLE wkx_2d3c86f204334d6c9a52b269b30ac9cb RENAME `wkx_user_groups_users`;
ALTER TABLE wkx_91bae96a5ee54a1a87be2b352fc7d27d RENAME `wkx_user_register`;
ALTER TABLE wkx_fa98a5d65e2949759b844da472d16f4b RENAME `wkx_user_tags`;
