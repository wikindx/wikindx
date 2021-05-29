+++
title = "Release Notes"
weight = 4
disableToc = false
+++

***Focus**: bug fixes, maintenance, feature enhancements*

## Important information

The attachment cache is completely rebuilt to take advantage of new supported formats and fixes.

## Bug fixes

- The default value configBrowserTabID option was wrong in the configuration screen.
- Restore the migration of config options of from 5.3.1 (this code should not change).
- RSS language tag was not compliant with the spec when the country part is added.
- Don’t crash when opening a non Zip Archive.
- Fix the mimetype detection of attachments.

## Feature enhancements

- Option to disable Google Scholar indexing (indexing is enabled by default now).
- Option to disable the RSS Feed (the RSS Feed is enabled by default now).
- Option to disable the sitemap for search engines indexation (the sitemap is enabled by default now).
- Syndication Feed in Atom 1.0 and RSS 2.0 formats [#282].
- Better name and description of syndication options.
- Add the resource abstract to RSS feeds [#393].
- Better parsing of ODT documents for attachment caching [#380].
- Support FODT (Flat ODT) text extraction (attachments) [#380].
- Support ODP/OTP/FODP presentation extraction (attachments) [#383].
- Support STI/SXI presentation extraction (attachments) [#384].
- Support STW/SXW text extraction (attachments) [#385].
- Support POTM/POTX/PPTM/PPTX Powerpoint extraction (attachments) [#386].
- Support FB1/2 FictionBook extraction (attachments) [#387].
- Support XPS text extraction (attachments) [#361].
- Support SLA Scribus text extraction (attachments) [#388].
- Support PostScript text extraction (attachments) [#367].
- Support DjVu text extraction (attachments) [#389].
- Support DVI text extraction (attachments) [#390].
- Support reStructured text extraction (attachment) [#391].
- Support markdown text extraction (attachments) [#356].
- Support AbiWord documents text extraction (attachment) [#392].
- Fix the mimetype of attachments in db and rebuild the cache of attachments with a wrong mimetype [#394].
- Add a switch in Admin|Configure|Users to allow registered users to edit categories and subcategories. This replaces the userwritecategories plugin which is now retired.

## Maintenance

- Better hints for the sitemap option.
- Set a higher limit to the RSS feed (50 resources displayed).
- Remove the custom style of RSS (use the global style now).
- Fix the type of the default SMTP port value.
- Rename option configRssDisplay to configRssDisplayEditedResources.
- Bump component compatibility version of plugins to 12.
