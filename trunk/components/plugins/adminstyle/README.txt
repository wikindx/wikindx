********************************************************************************
**                                 Adminstyle                                 **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5 and up.

Create and edit bibliographic/citations styles.

The module registers itself in admin and is only for registered WIKINDX users although this can be changed.

Unzip this file (with any directory structure) into plugins/adminstyle/.
Thus, plugins/adminstyle/index.php etc.

********************************************************************************

CHANGELOG:

2021-07-07 : CHG : change of the compatibility version (13) (style editor change for v6 style components).
2021-05-28 : CHG : change of the compatibility version (12) (removal of userwritecategory plugin).
2021-05-16 : CHG : change of the compatibility version (11) (removal of importexportbib plugin).
2021-04-18 : CHG : change of the compatibility version (10) (removal of the database prefix).
2021-04-08 : CHG : Shift resource URLs from the resource_text table to a new resource_url table [#284].
2021-02-10 : CHG : move the help on the website [#294].
2021-01-08 : FIX : restore temporarily the old languages list for style components [#305].
2020-12-21 : CHG : make PHP includes independent of the web server layout [#244].
2020-12-21 : FIX : error reading a bibliographic style for editing.
2020-12-21 : ADD : create the component.json file when creating or copying a style.
2020-12-21 : CHG : enable by default private styles.
2020-12-21 : CHG : convert UTF8 class to a namespace.
2020-12-21 : CHG : reformat source code to the prefered if/then/else style.
2020-12-21 : FIX : styles directory in help.
2020-12-21 : CHG : rewords some messages.
2020-12-21 : CHG : full French translation.
2020-07-11 : CHG : relicencing under ISC License terms.

v1.13, 2020
1. WIKINDX compatibility version 7.

v1.12, 2020
1. Add documentation.

v1.11, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.
2. Fix CSS/JS includes.

v1.10, 2019
1. Removed doubled text on preview citation. This makes it a little clearer to see what is going on.
2. Corrected an error in the bibliography preview whereby it was not possible to remove the publicationYear from the reference.
3. Fix an error previewing the first bibliographic template.
4. In style previews, add a 'RESET' field to the 'Disable fields' select box.
5. Some improvements to the display.
6. Debugged the handling of non-Latin/accented characters during citation and bibliography previews.
7. Corrected a bug in displaying '<' and '>' characters.
8. Adaptation for WIKINDX 5.9.1.

v1.9
1. Fix a fclose() bug.

v1.8
1. Plugin now compatible with WIKINDX v5.x
2. Fix to the preview of bibliographic and footnote citations for books and book articles.
3. Added the option to add custom fields to bibliographic styles.
4. Fix any memory leaks due to an oversight fclose().

v1.7
1. As per WIKINDX v4.2.2, season names (e.g. Spring) can now be added to resources requiring them (e.g. journal article);
   user defined seasons in the style editor have been added to reflect this change.

v1.6
1. Fixed a bug in the packaging of the plugin zip file -- the folder name is now correct.

v1.5
1. Fixed a bug when previewing in-text citations

v1.4
1. Bibliography templates and in-text citations can now be previewed.

v1.3
1. Plugin compatible only with WIKINDX v4.2.x

v1.2
1. Fix to packing of the download file (again).

v1.1
1. Fix to packing of the download file.

v1.0
1. Initial release.
