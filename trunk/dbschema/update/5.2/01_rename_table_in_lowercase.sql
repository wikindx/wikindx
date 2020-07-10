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

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%bibtex_string                    RENAME `%%WIKINDX_DB_TABLEPREFIX%%96e6bdf40a134c97809d40b1d5ffb700`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache                            RENAME `%%WIKINDX_DB_TABLEPREFIX%%1f5610ddf465433c8adbb9740dfb755f`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%category                         RENAME `%%WIKINDX_DB_TABLEPREFIX%%48eda791e04241adbf01f20828acf510`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection                       RENAME `%%WIKINDX_DB_TABLEPREFIX%%9c9994d44d344928a4dd7f4a5477cf46`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config                           RENAME `%%WIKINDX_DB_TABLEPREFIX%%e6d131c5ee6644d18f8af7f8f7294b6f`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator                          RENAME `%%WIKINDX_DB_TABLEPREFIX%%0e4c2f8bc23f42959a1a0b1839e05867`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%custom                           RENAME `%%WIKINDX_DB_TABLEPREFIX%%e4054f20a5f3471d81654daa425a1a07`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary                 RENAME `%%WIKINDX_DB_TABLEPREFIX%%b10b54238c8d4eb08a44e52fccb45bdc`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%import_raw                       RENAME `%%WIKINDX_DB_TABLEPREFIX%%22def90cbfe24d4d94138c9478f84775`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%keyword                          RENAME `%%WIKINDX_DB_TABLEPREFIX%%6e9ba07058134fc8815d9947a97a0852`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%language                         RENAME `%%WIKINDX_DB_TABLEPREFIX%%c407e449e6a94b86815971f3a0e0e0b4`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%news                             RENAME `%%WIKINDX_DB_TABLEPREFIX%%86e88c587c4b48aab014fa50c9c775e2`;
-- ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%plugin_soundexplorer             RENAME `%%WIKINDX_DB_TABLEPREFIX%%4fc387ba1ae34ac28e6dee712679d7b5`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%publisher                        RENAME `%%WIKINDX_DB_TABLEPREFIX%%9bf06154cf5b41f6847c353f18ec8f67`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource                         RENAME `%%WIKINDX_DB_TABLEPREFIX%%de9ec4380c724fc2934b24f283465be7`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments             RENAME `%%WIKINDX_DB_TABLEPREFIX%%59177497fcde4729bd01b90b220012ae`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_category                RENAME `%%WIKINDX_DB_TABLEPREFIX%%e8c2f4279de647dc89367406d70b325b`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_creator                 RENAME `%%WIKINDX_DB_TABLEPREFIX%%bd5d6598e912463fa07dbdf25a5e6a88`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom                  RENAME `%%WIKINDX_DB_TABLEPREFIX%%354da1f09b8e4ad5a4af39f8f7688d92`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_keyword                 RENAME `%%WIKINDX_DB_TABLEPREFIX%%2e1057454e234a3aa255722d6b3b778f`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_language                RENAME `%%WIKINDX_DB_TABLEPREFIX%%f30115696d13452aa4f3526b25c157ce`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata                RENAME `%%WIKINDX_DB_TABLEPREFIX%%b55d4f5de9944f9e826e676c52d3ecff`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc                    RENAME `%%WIKINDX_DB_TABLEPREFIX%%44db90345b8d44da84abfb9388b2a8a4`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_page                    RENAME `%%WIKINDX_DB_TABLEPREFIX%%ae8d10bfe8aa43a49a9b15be5d17fd7b`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_summary                 RENAME `%%WIKINDX_DB_TABLEPREFIX%%32635f292702449980a0936294321d04`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text                    RENAME `%%WIKINDX_DB_TABLEPREFIX%%7b94473afaf7436e89c164061731b75d`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_timestamp               RENAME `%%WIKINDX_DB_TABLEPREFIX%%197d47085aac42f48adf09da10a04e68`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_user_tags               RENAME `%%WIKINDX_DB_TABLEPREFIX%%5b8d59cd555548888cd117998e852d51`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year                    RENAME `%%WIKINDX_DB_TABLEPREFIX%%3416e12ac0c744519bc2f6656a43c11f`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%statistics                       RENAME `%%WIKINDX_DB_TABLEPREFIX%%04a6e883b2af4ee7ac203e32e747c575`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%subcategory                      RENAME `%%WIKINDX_DB_TABLEPREFIX%%0a3c535b9e974dd29adc1c7db3041647`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%tag                              RENAME `%%WIKINDX_DB_TABLEPREFIX%%b33e1fa74fc049bbbb20722854a60f6d`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users                            RENAME `%%WIKINDX_DB_TABLEPREFIX%%0ff24f0e8e244131ad1299770a12dfc4`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography                RENAME `%%WIKINDX_DB_TABLEPREFIX%%b95c1e9b483a481da3d374000ab26a0a`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography_resource       RENAME `%%WIKINDX_DB_TABLEPREFIX%%7dfda1dc0b7f487ba26bae2e7ee94f74`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups                      RENAME `%%WIKINDX_DB_TABLEPREFIX%%21919c05adda4f6d97081630ac0b6e89`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups_users                RENAME `%%WIKINDX_DB_TABLEPREFIX%%2d3c86f204334d6c9a52b269b30ac9cb`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register                    RENAME `%%WIKINDX_DB_TABLEPREFIX%%91bae96a5ee54a1a87be2b352fc7d27d`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_tags                        RENAME `%%WIKINDX_DB_TABLEPREFIX%%fa98a5d65e2949759b844da472d16f4b`;


ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%96e6bdf40a134c97809d40b1d5ffb700 RENAME `%%WIKINDX_DB_TABLEPREFIX%%bibtex_string`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%1f5610ddf465433c8adbb9740dfb755f RENAME `%%WIKINDX_DB_TABLEPREFIX%%cache`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%48eda791e04241adbf01f20828acf510 RENAME `%%WIKINDX_DB_TABLEPREFIX%%category`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%9c9994d44d344928a4dd7f4a5477cf46 RENAME `%%WIKINDX_DB_TABLEPREFIX%%collection`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%e6d131c5ee6644d18f8af7f8f7294b6f RENAME `%%WIKINDX_DB_TABLEPREFIX%%config`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%0e4c2f8bc23f42959a1a0b1839e05867 RENAME `%%WIKINDX_DB_TABLEPREFIX%%creator`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%e4054f20a5f3471d81654daa425a1a07 RENAME `%%WIKINDX_DB_TABLEPREFIX%%custom`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%b10b54238c8d4eb08a44e52fccb45bdc RENAME `%%WIKINDX_DB_TABLEPREFIX%%database_summary`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%22def90cbfe24d4d94138c9478f84775 RENAME `%%WIKINDX_DB_TABLEPREFIX%%import_raw`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%6e9ba07058134fc8815d9947a97a0852 RENAME `%%WIKINDX_DB_TABLEPREFIX%%keyword`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%c407e449e6a94b86815971f3a0e0e0b4 RENAME `%%WIKINDX_DB_TABLEPREFIX%%language`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%86e88c587c4b48aab014fa50c9c775e2 RENAME `%%WIKINDX_DB_TABLEPREFIX%%news`;
-- ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%4fc387ba1ae34ac28e6dee712679d7b5 RENAME `%%WIKINDX_DB_TABLEPREFIX%%plugin_soundexplorer`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%9bf06154cf5b41f6847c353f18ec8f67 RENAME `%%WIKINDX_DB_TABLEPREFIX%%publisher`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%de9ec4380c724fc2934b24f283465be7 RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%59177497fcde4729bd01b90b220012ae RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_attachments`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%e8c2f4279de647dc89367406d70b325b RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_category`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%bd5d6598e912463fa07dbdf25a5e6a88 RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_creator`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%354da1f09b8e4ad5a4af39f8f7688d92 RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_custom`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%2e1057454e234a3aa255722d6b3b778f RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_keyword`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%f30115696d13452aa4f3526b25c157ce RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_language`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%b55d4f5de9944f9e826e676c52d3ecff RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_metadata`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%44db90345b8d44da84abfb9388b2a8a4 RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_misc`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%ae8d10bfe8aa43a49a9b15be5d17fd7b RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_page`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%32635f292702449980a0936294321d04 RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_summary`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%7b94473afaf7436e89c164061731b75d RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_text`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%197d47085aac42f48adf09da10a04e68 RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_timestamp`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%5b8d59cd555548888cd117998e852d51 RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_user_tags`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%3416e12ac0c744519bc2f6656a43c11f RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_year`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%04a6e883b2af4ee7ac203e32e747c575 RENAME `%%WIKINDX_DB_TABLEPREFIX%%statistics`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%0a3c535b9e974dd29adc1c7db3041647 RENAME `%%WIKINDX_DB_TABLEPREFIX%%subcategory`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%b33e1fa74fc049bbbb20722854a60f6d RENAME `%%WIKINDX_DB_TABLEPREFIX%%tag`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%0ff24f0e8e244131ad1299770a12dfc4 RENAME `%%WIKINDX_DB_TABLEPREFIX%%users`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%b95c1e9b483a481da3d374000ab26a0a RENAME `%%WIKINDX_DB_TABLEPREFIX%%user_bibliography`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%7dfda1dc0b7f487ba26bae2e7ee94f74 RENAME `%%WIKINDX_DB_TABLEPREFIX%%user_bibliography_resource`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%21919c05adda4f6d97081630ac0b6e89 RENAME `%%WIKINDX_DB_TABLEPREFIX%%user_groups`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%2d3c86f204334d6c9a52b269b30ac9cb RENAME `%%WIKINDX_DB_TABLEPREFIX%%user_groups_users`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%91bae96a5ee54a1a87be2b352fc7d27d RENAME `%%WIKINDX_DB_TABLEPREFIX%%user_register`;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%fa98a5d65e2949759b844da472d16f4b RENAME `%%WIKINDX_DB_TABLEPREFIX%%user_tags`;
