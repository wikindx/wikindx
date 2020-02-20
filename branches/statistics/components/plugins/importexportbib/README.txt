********************************************************************************
**                               importexportbib                              **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5 and up. Versions below 1.6 will work with WIKINDX 4.2.1.

Administrator interface for the importing and exporting of bibliographies.

Default settings are that the module registers itself in the 'plugin1' menu, export options are available 
to registered users and import is only for WIKINDX administrators.  These settings can be changed by 
editing config.php.

Unzip this file (with any directory structure) into plugins/importexportbib/.
Thus, plugins/importexportbib/index.php etc.

Ensure that you are using the latest WIKINDX version before use.

PubMed Import and bibutils (see below) make use of 'bibutils' from http://sourceforge.net/p/bibutils/home/Bibutils/ and written by Chris Putnam.

You will need to download and install the appropriate binaries from the bibutils website above.  By default, the *NIX install path is 
/usr/local/bin/ -- if you install elsewhere, edit both $bibutilsPath variables in plugins/importexportbib/config.php.

************************************************************************
CHANGELOG:

v1.14, 2020
1. When exporting Endnote XML, custom fields can now be exported.

v1.13, 2020
1. Fix heredoc syntax.

v1.12, 2020
1. Fix a bug in exporting Endnote XML – no resources were exported if the merge fields checkbox was ticked.

v1.11, 2020
1. Add documentation.

v1.10, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.
2. No longer use session_id() for temporary file names.

v1.9 ~ 2019
1. Adaptation for Wikindx 5.9.1.

v1.8 ~ June 2017
1. Plugin now compatible with WIKINDX v5.x
2. Improved handling of date field in Endnote imports
3. In Endnote import, unrecognized fields can be mapped to custom fields.
4. Fix any memory leaks due to an oversight fclose().
5. Imagemagick no longer used.

v1.7 ~ Sept. 2014
A packaging error in the download files stopped wikindx working if v1.6 of this plugin was installed.
--------------------
v1.6 ~ Aug. 2014
1. In bibtex, custom fields can now be exported.
--------------------
v1.5 ~ Jan. 2014
1. Creator initials now correctly added to Endnote exports.
--------------------
v1.4 ~ April 2013
1. Plugin compatible only with WIKINDX v4.2.x
2. Users can now choose to export either the basket (if it exists) or the last multiview.
3. Added the option to ignore keywords when importing Endnote and PubMed resources.
--------------------
v1.3 ~ 21st Feb. 2013
1.  Misnaming of file paths in some of the modules caused a file not found error.
--------------------
v1.2 ~ 25th January 2013
1. Correction to download packaging that stops the plugin working properly.
--------------------
v1.1 - 23/Jan/2013
Bug fixes to permissions
--------------------
v1.0, 2016
Initial release.

--
Mark Grimshaw-Aagaard 2016

***********************************
***********************************
PUBMED IMPORT

Search for and download PubMed data in bibTeX format (based on bibUtils v1.1).  The bibTeX file may be optionally immediately imported into WIKINDX.

If 'safe_mode' is 'On' in php.ini then you will need to install the bibutils binaries in the directory specified 
by 'safe_mode_exec_dir'.

Brian Cade 2006-2007
bcade@users.sourceforge.net

(Additional work by Mark Grimshaw-Aagaard 2006, 2008, 2012 and 2015
Mainly the bibTeX to WIKINDX import, optimization for Windows and upgrade to WIKINDX v4 and v5.
sirfragalot@users.sourceforge.net)

***********************************
***********************************
BIBUTILS

Convert a range of bibliographic formats.

NB. this module is compatible with WIKINDX v5 and up.  Results may be unexpected if used with a lower version.  

If 'safe_mode' is 'On' in php.ini then you will need to install the bibutils binaries in the directory specified 
by 'safe_mode_exec_dir'.

v1.2 -- Dec/2012.  Upgraded plugin for WIKINDX v4

--
Mark Grimshaw-Aagaard 2012-2019.
