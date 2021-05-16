+++
title = "Release Notes"
weight = 4
disableToc = false
+++

***Focus**: bug fixes & feature enhancements*

## Important information

Three extensions become mandatory: curl, dom (libxml) and zip.

This allows:

- More attachment formats covered by search
- Components update more stable

## Bug fixes

- Fixed a rare error (missing URL array) when adding/editing resources.
- Replace some call to strstr by mb_strstr (UTF-8 support).
- Set max_allowed_packet to 1073741824 (allow to store LONGTEXT type in one statement, MySQL).
- Fix an error in paging ideas following Advanced Search when ideas are the only field searched on.
- Matching with matchPrefix() and matchSuffix() was partly insensitive.
- Prevent issues when a plugin is removed but not disabled safely by the admin.
- Fix a crash on creation of resource_attachments table.
- Fix a crash during a fresh install (error on table counting).
- Fix a bug in search highlighting that remembered a previous search instead of removing it.
- Fix a bug in resource view for the delete icon for a user's own resource if they are not the superadmin.
- Fix bug #314 (warning if not enough creators available for creator grouping).

## Feature enhancements

- Add a switch in MyWikindx|Resources to toggle the display of certain statistics when viewing a single resource. The display of views index and popularity index greatly slows down the display of the resource so these can be turned off (the default on upgrade) and only number of views, number of downloads, and maturity index will be viewed. Popularity and view indices will appear in a later version in the Statistics menu.
- Set the timezone of the db engine.
- New plugin XpdfReader PdftoText: a second option to convert PDF attachments.
- Support EPUB text extraction (attachments) [#355].
- Support TXT text extraction (attachments) [#360].
- Support (X)HTML text extraction (attachments) [#359].
- Support MHT/HTML text extraction (attachments) [#357].
- QUICKSEARCH now also searches on cached resource attachments.
- Moved the functions of the importexportbib plugin to the core code (Under Resources menu, Resources|Basket menu, and Metadata menu).
- Custom session garbage collector (see Admin|Configure|Users).

## Maintenance

- Align group_concat_max_len value with max_allowed_packet (MySQL).
- Document MySQL/MariaDB settings.
- Store the content of attachment cache files in the database [#352].
- Add missing mime-types of parsable attachments.
- Fix return_bytes for negative values (e.g. memory_limit) [#351].
- curl PHP extension is mandatory now.
- dom PHP extension is mandatory now.
- zip PHP extension is mandatory now.
- Keep the userId of users who have been logged in at least once.
- Transfer registered users' baskets and bookmarks to separate tables – these are maintained permanently regardless of what happens with FEATURE ENHANCEMENT 10 above.
- Be more reliable when a component is removed by hand [#373].
- Bump component compatibility version of plugins to 11.

## Security

- Update PHPMailer (v6.4.1, CVE-2020-36326, a regression of CVE-2018-19296 object injection introduced in 6.1.8).
- Prevent session fixing by using a custom session name.
- Prevent session fixing by regenerating the session id before a major change of status.
- Reduce cookie lifetime to 1 month.
- Hide the value of WIKINDX_DB_PASSWORD in the debugtool.
- Just in case while a user is logged on and is deleted by the superadmin, ensure their next action returns them to the logon prompt.
- Update Adminer to version 4.8.1 (fix an XSS in doc_link + PHP 8.0 support).
