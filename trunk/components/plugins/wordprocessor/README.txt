********************************************************************************
**                               wordProcessor                                **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5 and up.  Results may be unexpected (at least) if used with a 
lower version.

Logged in users can use a WYSIWYG word processor for the writing of articles.  WIKINDX resources can be cited,  
bibliographic and citation styles can be applied to the article and the article exported in Rich Text Format 
(.rtf) which can be opened in most common word processors including OpenOffice.org and Word. 

The module registers itself in the 'plugin1' menu but this can be changed from the WIKINDX Admin|Plugins 
interface.

Unzip this file (with any directory structure) into plugins/wordProcessor/. Thus, 
plugins/wordProcessor/index.php
etc.

Papers can be imported from earlier versions by using the 'Import paper' function.

********************************************************************************

CHANGELOG:

v1.10, 2020
1. Fix RTF export of images.
2. Fix a call to RESOURCEMAP.php.
3. Fix all JS includes.

v1.9, 2020
1. Add documentation.

v1.8, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.
2. Fix CSS/JS includes.

v1.7
1. Remove a the old unused "papers" folder.

v1.6
1. Adaptation for Wikindx 5.9.1.

v1.5
1. Plugin now compatible with WIKINDX v5.x
2. Some minor debugging for listing/opening/deleting files.
3. Imagemagick no longer used.

v1.4
1. On some server environments, filepaths were incorrect for some tinyMCE functionality (core wikindx must be updated to at least v4.2.2).

v1.3
1. Plugin compatible only with WIKINDX v4.2.x

v1.2
1. Added a check on startup for deleted wikindx users -- if found, delete entries from db table and papers/ folder.

--
Mark Grimshaw-Aagaard 2017-2019.