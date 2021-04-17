+++
title = "Release Notes"
date = 2021-04-17T17:30:00+01:00
weight = 4
disableToc = false
+++

***Focus**: bug fixes, features, maintenance*

##Important information

The next version of WIKINDX will remove the prefix from tables.

This functionality is only useful for several programs sharing the same database with the same table names or several WIKINDX installations in the same database. These two practices are to be avoided because they are a good way to lose your data. Each software should be isolated in its own database for privacy, security, bug resistance and ease of maintenance. We believe that very few installs use this feature.

6.4.0 harcoded the __wkx___ prefix but still offered the possibility of cheating with the constant __WIKINDX_DB_TABLEPREFIX__.

This version (6.4.6) removes the _cheat mode_ and checks that you don't have a mix of tables with another app because the next version will rename all tables without the prefix. Otherwise, collisions could occur. If you are affected by this change please contact us for help with the transition.

IT WOULD BE A GOOD IDEA TO BACK UP YOUR DATABASE FIRST BEFORE PROCEEDING WITH THE UPGRADE.

You must apply one or more of these corrections before you can continue.

If you have written your own plugin with your own tables they should use the default prefix __wkx___ to be portable.

If you customized the prefix, use the cli-migrate-db-prefix.php script to replace it with the default prefix __wkx___.

If you have installed another application in the same database, move the tables from WIKINDX, or the database objects from the other application, to its own database.

If you have other tables in the database for various reasons, please drop them or move them to another database.

The official tables for version 6.4.6 are:

wkx_bibtex_string, wkx_cache, wkx_category, wkx_collection, wkx_config,
wkx_creator, wkx_custom, wkx_import_raw, wkx_keyword, wkx_language, wkx_news,
wkx_plugin_localedescription, wkx_plugin_soundexplorer, wkx_plugin_wordprocessor,
wkx_publisher, wkx_resource, wkx_resource_attachments, wkx_resource_category,
wkx_resource_creator, wkx_resource_custom, wkx_resource_keyword,
wkx_resource_language, wkx_resource_metadata, wkx_resource_misc, wkx_resource_page,
wkx_resource_text, wkx_resource_timestamp, wkx_resource_url, wkx_resource_user_tags,
wkx_resource_year, wkx_session, wkx_statistics_attachment_downloads, 
wkx_statistics_resource_views, wkx_subcategory, wkx_tag, wkx_temp_storage,
wkx_user_bibliography, wkx_user_bibliography_resource, wkx_user_groups,
wkx_user_groups_users, wkx_user_keywordgroups, wkx_user_kg_keywords,
wkx_user_kg_usergroups, wkx_user_register, wkx_user_tags, wkx_users, wkx_version


## Bug fixes

1. Remove dead code about Etag HTTP headers and fix a wrong image mimetype in images dialog [#333].
2. Localize dates with the current locale.
3. Fix a syntaxic error in upgrade step 34.
4. Re-enable the insertion of plug-ins into the metadata menu. Where the plugin config.php file inserted into 'text', this should now be 'metadata'.

## Feature enhancement

1. Localize the dates in the images dialog.
2. Accept the webp image format because since September 2020 all browsers support it (TinyMCE included).
3. Distinct image, attachment and file upload max file size [#278].
4. Display max file sizes on upload forms [#278].
5. Restrict the uploadable file type when possible.
6. Disable uploads if PHP file_uploads option is Off [#345].
7. Display the limit of the number of files to upload from PHP (max_file_uploads) [#344].

## Maintenance

1. Rename configImagesMaxSize option to configImgUploadMaxSize for consistency.
2. Rename configImagesAllow option to configImgAllow for consistency.
3. Rename configFileAttach option to configFileAttachAllow for consistency.
4. Hardcode the db prefix in SQL files [#346].
5. Throw a fatal error if the database contains non WIKINDX tables [#346].
