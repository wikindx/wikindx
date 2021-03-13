+++
title = "Release Notes"
date = 2021-01-29T00:08:41+01:00
weight = 4
disableToc = false
+++

***Focus**: bug fixes, maintenance*

## Important information

**From this version the API documentation and manual are integrated into the website. The manual will no longer be packaged. **


### Bugs fixes

* When adding a new resource form, if a duplicate title is detected, the form is now re-presented with the filled-in form data intact [#313].
* Fix SQL formatting.
* Rearrange the order of some default function fields in readiness for PHP 8 [#312].
* In some cases, QUICKSEARCH searched on an empty field in FULLTEXT mode (abstracts, notes, etc.) – fixed.
* Recreate the usersUsernameUnique index with a BTREE type [#318].
* Index_Type option is unreliable for InnoDB due to functionality of [bug #22632](https://bugs.mysql.com/bug.php?id=22632).
* Edit collection did not update the collection's resources [#319].
* Drop old form_data table.
* Add a missing index on resourceattachmentsResourceId (previous upgrade code missing).
* Add a missing index on resourcecustomCustomId (previous upgrade code missing).
* Add a missing index on resourcelanguageLanguageId (previous upgrade code missing).
* Add a missing index on resourcelanguageResourceId (previous upgrade code missing).
1* Add a missing index on resourcemiscCollection (previous upgrade code missing).
* Add a missing index on resourcemiscPublisher (previous upgrade code missing).
* Add a missing index on resourcetimestampTimestamp (previous upgrade code missing).
* Add a missing index on resourcetimestampTimestamp (previous upgrade code missing).
* Add a missing index on resourcetimestampTimestampAdd (previous upgrade code missing).
* Fix indices categoryCategory, keywordKeyword, and resourceTitle.
* Workaround a limitation of MySQL (no self update of a table).
* Allow the two syntax of current_timestamp() for MySQL and MariaDB.


### Feature enhancement

* Added the ability to add an impressum/legal notice at the footer of each page – see Admin|Configure|Miscellaneous.
* Added code to interface with a Word add-in if the WIKINDX allows read-only access, can be reached through https:// and has 
in-text citation styles (such as APA, Harvard, MLA etc.). The add-in allows the importation of references and citations (quotations/paraphrases) direct from one or more wikindices. The word document can be 'finalized' in the add-in and this (re)formats the inserted references to the 
chosen style and appends a bibliography ordered as required.
* Allow to use the CN as a LDAP login.


### Maintenance

* Blocks install/upgrade if the db engine does not meet the requirements [#311].
* Move technical details in debug mode [#301].
* Move the documentation on the website [#294].
* Update Smarty (v3.1.39).
* Update PHPMailer (v6.3.0).
* Add a lot of missing indices.
* Add missing FULLTEXT indices on resources.
* When the WIKINDX_DEBUG_SQL is TRUE, always die on db error [#289].
* Embed icons in HTML with base64 encoding [#321].
* Remove dead code validating components ot type language.
* New rule (code 34) about the characters set of the component_id field of the component.json file.
* Note the ldap PHP extension as optional.
* WIKINDX_MEMORY_LIMIT: follow the default official value of PHP memory_limit (128MB).
* WIKINDX_MAX_EXECUTION_TIMEOUT: follow the default official value of PHP max_execution_time (30 seconds).
* Remove the deprecated syntax of WIKINDX_MAX_EXECUTION_TIMEOUT. The value cannot be a string now.
* Use a better system to rewrite the config file.
* Ensure utf8mb4_unicode_520_ci is always the default collation of the database.
* Standardize the name of the application to WIKINDX.
