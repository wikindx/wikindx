+++
title = "Release Notes"
weight = 4
disableToc = false
+++

***Focus**: bug fixes, maintenance*

## Bug fixes

- Correct display of resource icons in list view – 4 possibilities: resource only, resource with metadata, resource with attachment, resource with metadata and attachment. Update the latter icon.
- Fix missing message when authGate is enabled.
- Fix RSS/ATOM feed display.
- Fix an issue paging through a basket.
- Do not restore memory_limit configuration to work around PHP bug #81070 (in PHP 7.4 and 8.0).

## Feature enhancements

- QUICKSEARCH: Add a check box to toggle searching of attachments.

## Maintenance

- The default db name become 'wikindx'.
- Some rationalization of bibliographic style code moving a lot into the core from the adminstyle plugin.
- Upgrading of bibliographic styles.
- Split Chicago bibliographic style into two styles, one for footnote version and one for author-date version.
- Bump component compatibility version of plugins to 13 (style editor change for v6 style components).

## Security

- Update PHPMailer (v6.5.0, CVE-2021-3603).
