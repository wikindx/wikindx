+++
title = "Release Notes"
date = 2021-01-29T00:08:41+01:00
weight = 4
disableToc = false
+++

***Focus**: bug fixes, maintenance, feature enhancements*


## Bugs fixes

* Add missing indices, again. 
* In the distribution, rename override.css to override.css.dist. This stops any required override.css being overwritten at upgrade. To make use of override.css.dist for CSS control, rename override.css.dist to override.css.
* Fix search on titles with hyphens [#324].
* Align textboxes on “File URLs edit” screen.
* Search on title fields of resources failed where the title/subtitle made use of {...} to protect against changes in bibliographic capitalization [#336].
* Corrected added/edited message on resource add/edit form return.

## Maintenance

* Shift resource URLs from the resource_text table to a new resource_url table [#284].
* Remove base64 encoding/decoding from the cache table.
* Drop the resource_summary table, create a new resourcemiscMetadata column in resource_misc, and set it to 1 if the resource has metadata.
* Update PHPMailer (v6.4.0).
* As part of the upgrade process for 1., this version deletes all registered users sessions related to SQL including bookmarks, last multi view etc.
* Remove base64 encoding on collection defaults [#331].
* Protects the upgrade against SQL files with incorrect permissions.
* Protects the upgrade against rogue files (.DS_Store, thumb...): execute only file with a .sql extension.
* Remove the base64 encoding of config / constants [#329].

## Feature enhancement

* Registered users can add comments to others' quotes and paraphrases – added ability to delete those comments.
