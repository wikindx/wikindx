---
title: "WIKINDX overview"
---


# WIKINDX overview

Reference management, bibliography management, citations and a whole lot more.

Designed by academics for academics, under continuous development since 2003, and used by both individuals and major research institutions worldwide, WIKINDX is a single or multi-user Virtual Research Environment (an enhanced on-line bibliography manager) storing searchable references, notes, files, citations, ideas, and more. Plugins include a citation style editor, import/export of bibliographies (BibTeX, Endnote, RIS etc.), and a WYSIWYG word processor exporting formatted articles to RTF. WIKINDX supports multiple attachments with each reference, multiple language localizations, and uses a template system to allow users to visually integrate WIKINDX into their sites.

WIKINDX runs on a web server giving you and your research group ownership and global access from any web-enabled device. You manage your database, you own your data.

WIKINDX is widely used around the world including at NASA and a range of universities and research institutions. Some WIKINDX users:


* [Game Audio](https://gameaudio.wikindx.com)
* [Institut National de Recherche Pédagogique](http://wikindx.inrp.fr/biblio_vst/index.php), Bibliographies du service Veille et Analyses
* [Laboratorio Bio Acyl Corp](https://site.bioacyl.com/wikindx/)
* [Malvern Radar and Technology History Society](https://www.reports.mraths.org.uk)
* [The Fraunhofer Institute](https://www.ttcn.de//bibliography/), TTCN-3
* [University of Bonn](http://www.comicforschung.uni-bonn.de/index.php), Online-Bibliographie zur Comicforschung


## Features

Listed below are the core features available by default. Functionality can be extended with plug-ins and other add-ins.


### User

* 41 resource types.
* Translations included for major languages (English, French, German, Spanish, Italian, Russian).
* Multi-user mode – create and manage your own bibliographies drawn from the WIKINDX master bibliography and browse other users' bibliographies. (Must be enabled by the administrator.)
* Create user groups and user group bibliographies. (Must be enabled by the administrator).
* Save your own preferences.
* Enter/edit bibliographic resources.
* Add unlimited file attachments to each resource. (Must be enabled by the administrator.)
* Catalogue resources by categories, sub-categories and keyword(s).
* Enter/edit a general note about the whole resource.
* Enter/edit quotes and paraphrases from those resources.
* Enter/edit thoughts or musings on various aspects of a resource (can be private or public).
* Enter/edit ideas that are independent of resources.
* Add keywords to resource metadata such as quotes, paraphrases and musings.
* Cross-reference other WIKINDX resources from within quotes, paraphrases, musings, ideas, notes and abstracts.
* Edit keywords, creators, publishers and journals.
* Comprehensive search across all the above with highlighting of search terms using either Quick Search or the flexible Advanced Search.
* Reorder bibliographic lists by first creator, title, resource type, publisher, year of publication or timestamp.
* Browse all creators, publishers, collections, categories and keywords with font colour and size indicating frequency of occurrence.
* Unlimited primary creators, editors, translators and revisers, composers, agents, performers etc.
* Export bibliographic lists (optionally annotated) with a range of formatting options to Rich Text Format [RTF] files for easy insert into word processors.
* Cut 'n' Paste BibTeX entries to the database (amount limited by the administrator).
* View and export in various bibliographic styles including Chicago, MLA, APA, Harvard, Turabian, British Medical Journal and IEEE.
* Run WIKINDX in core English or other languages (depending on administrator-installed language plug-ins).
* User-defined paging of long bibliographic lists.
* View all resources, quotes, paraphrases and musings or a single random one.
* One-click return to last bibliographic list or single view.
* Store up to 20 bookmarks for quick return to single views and resource lists.
* Add resources to a temporary basket for viewing and exporting.
* Select a visual style.
* Import WIKINDX resources into Zotero.
* And much, much more ...


### Administrator

* Builtin and LDAP auth.
* Design your own CSS templates for web browser display and integrate WIKINDX into your existing web site.
* Enable/disable multi-user mode, add users or allow user self-registration.
* Several levels of write and read only access.
* Delete resources.
* Add custom text fields to resources.
* Manage users and resource categories and sub-categories.
* Merge keywords into one keyword.
* Merge and group creators into one creator.
* Importation of BibTeX (.bib) bibliographies.
* Manage plug-ins, visual templates and localizations.
* Add general news items and optionally email the item to registered users.
* Optionally receive email notification of user registrations.
* Install non-English language localizations.
* Provide a RSS feed of latest additions and edits.
* WIKINDX resources can be optionally indexed by Google Scholar.
* WIKINDX has an architecture enabling the easy writing of plug-ins to expand its features.
* User registration requests implement anti-spam/bot measures.
* Attachments can be embargoed (blocked from public view) until a specified date – the embargo is automatically lifted on or after the specified date. Until that time, only admins can view embargoed attachments.
* And much, much more...



## Components

WIKINDX uses a components system to extend or alter its capabilities. All offical components are available from our Components Update Server and are configurable from the WIKINDX interface. You could also write your own components and contribute to the community of users.


### Some Plugins

* The WYSIWYG word processor – write an article, from draft through to publication with automatic citation formatting, entirely within the one software
* IdeaGen – randomly selects and displays two items of metadata (quotes, paraphrases, musings, or ideas) in the hope that the new juxtaposition might lead to serendipity
* Bibutils – convert between a number of bibliographic formats in preparation for an import into WIKINDX
* ImportExportBib – import bibliographies into WIKINDX and export bibliographies – Endnote, RIS, BibTeX, RTF, HTML, PubMed
* SoundExplorer – experimental aural notification of selected resource additions
* ChooseLanguage – quickly select a new language
* Localization – easily create a new localization
* AdminStyle – create and edit bibliographic/citation styles
* LocalDescription – change the front-page description depending on the user-selected localization
* UserWriteCategory – user administration of categories
* Visualize – create various graphical visualizations from WIKINDX data with the JpGraph PHP library.X
* RepairKit – fix corrupted character codes in imported data, missing rows, database integrity, and other fixes. The Swiss Army Knife for WIKINDX . . .
* BackupMysql – back up the WIKINDX
* DbAdminer – embed a db editor for debug/admin
* DebugTools – get usefull debugging info


### Bibliographic styles

There are a variety of bibliographic and citation styles available from the Components Update Server like:

* American Psychological Association (APA)
* Associação Brasileira de Normas Técnicas (ABNT)
* British Medical Journal (BMJ)
* Chicago
* Comicforschung-Bibliographie Stil (CFB)
* Harvard
* Institute of Electrical and Electronics Engineers (IEEE)
* Modern Language Association (MLA)
* Turabian
* Wikindx

Create your own styles and contribute to extend our public repository of bibliographic styles!


### Templates

We provide basic themes. Fork one of them and build your own.
