********************************************************************************
**                               Sound Explorer                               **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5 and up.

Sound Explorer will store searches then, at any time in the future, play a sound if a resource matching 
a past search is found in a current list operation. This works on the principle of a serendipitous conjunction of past searches 
with a current search result, hopefully allowing you to make new creative connections by prodding at the boundaries of your 
current conceptual space.  But it is also useful in a multi-user 
WIKINDX where it functions like a 'tripwire' signal -- the sound will play if a new resource or metadata has been added by another 
user that matches a stored search (if the resource is being displayed in a list). It is currently an experimental 
prototype and has been tested in the Firefox, Safari, and Chrome web browsers. Other browsers are untested.

The module registers itself as an inline plugin and is only for registered WIKINDX users although this can be changed in the plugin's index.php.

Unzip this file (with any directory structure) into plugins/soundexplorer/.
Thus, plugins/soundexplorer/index.php etc.

********************************************************************************

CHANGELOG:

v2.4, 2020
1. Wikindx compatibility version 7.

v2.3, 2020
1. Add documentation.

v2.2, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.
2. Fix CSS/JS includes.

v2.1, 2019
1. Plugin reconfigured to use HTML5 audio.
2. Adaptation for Wikindx 5.9.1.

v1.2
1. Plugin now compatible with WIKINDX v5.x

v1.1
1. Plugin compatible only with WIKINDX v4.2.x

v1.0
1. Initial release.

--
Mark Grimshaw-Aagaard 2019.
