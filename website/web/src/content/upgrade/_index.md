+++
title = "Upgrade"
date = 2021-01-30T00:08:41+01:00
weight = 5
chapter = true
#pre = "<b>1. </b>"
+++

                           --o UPGRADE o--

    ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

////////////////////////////////////////////////////////////////////////////
For installing a fresh WIKINDX with a blank database, read docs/INSTALL.txt.
////////////////////////////////////////////////////////////////////////////

If you are upgrading, read this document carefully.

Upgrading v3.8.x or v4.x database is not supported anymore by this version.
For this case you need to use one of the two stable transition versions and
follow their instructions. After going through these intermediate versions
you can update a second time to a more recent version:

 - v6.0.8 which supports PHP 5.6 minimum.
 - v6.1.0 which supports PHP 7.0 minimum.
 - v6.4.0 which supports PHP 7.3 minimum.

>>>>>>> A note on UTF-8 characters <<<<<<<
Databases used since v5.1 might exhibit errors in the conversion to UTF-8
characters that came in with v5.1. The repairkit plugin attempts to fix these
issues but, because the UTF-8 correction code was removed in this plugin from
v6.2.1, databases that have been upgraded since before 5.1 but have not had
their UTF-8 repaired with the plugin, should follow this procedure:
1. Download and install WIKINDX 6.0.8 or 6.1.0 according to your PHP version.
2. Run WIKINDX and follow the database upgrade.
3. Install the repairkit plugin for the relevant wikindx version and run the
plugin’s ‘Fix chars’ code.
4. If you do not have PHP 7.0 or greater, you cannot go further than using
WIKINDX v6.0.8. If you have PHP 7.0 or greater, then you can now safely download
and install the highest version of WIKINDX available.

The above results from a code clean-out and the understanding that there are
very few (if any) current users of WIKINDX with pre-v5.1 databases.
>>>>>>> <<<<<<<

If you are upgrading a previous installation of WIKINDX 5.x to 6.x,
simply overwrite the existing installation. Optionally, you can install
the software files in a different folder but see steps 7–9 below
(especially step 9). You should not use the existing config.php file but
should copy config.php.dist to a new config.php. v5.4 moved several
settings to the database: you can copy existing settings from the old
config.php file but should also edit the new settings.  The first
running of WIKINDX will upgrade the database in a single step. You
should also reinstall v6 plugins from the components server as WIKINDX
introduced a components compatibility check and a components manager.

The CURL PHP extension is not mandatory in WIKINDX but is used in some
circumstances. For example, from v5.9.1, components such as plugins and
languages can be managed from the Admin|Components interface if CURL is
installed – without CURL, components must be installed manually by
downloading them from the Sourceforge server. It's not recommend to
disable CURL and manage components by hand.

///////////////////

BACK UP YOUR DATABASE BEFORE ANY UPGRADING!!!!!!!
BACK UP YOUR DATABASE BEFORE ANY UPGRADING!!!!!!!
BACK UP YOUR DATABASE BEFORE ANY UPGRADING!!!!!!!
BACK UP YOUR DATABASE BEFORE ANY UPGRADING!!!!!!!
BACK UP YOUR DATABASE BEFORE ANY UPGRADING!!!!!!!

BACK UP YOUR DATA FILES BEFORE ANY UPGRADING!!!!!!!
BACK UP YOUR DATA FILES BEFORE ANY UPGRADING!!!!!!!
BACK UP YOUR DATA FILES BEFORE ANY UPGRADING!!!!!!!
BACK UP YOUR DATA FILES BEFORE ANY UPGRADING!!!!!!!
BACK UP YOUR DATA FILES BEFORE ANY UPGRADING!!!!!!!


IMPORTANT <<

1/ v6.2.0 or later will automatically upgrade an existing v5.1 or later
WIKINDX database.

2/ If your WIKINDX installation is less than v5.1, you should first
upgrade to v6.0.8 or 6.1.0, convert the database by running one of these
version, then upgrade further. It's assumed that there are very few pre
v5.1 users left if any so this is a code cleanout.  See the section
below.

3/  WIKINDX uses caching in the database _cache table for lists of
creators, keywords etc. For large WIKINDX databases, if you receive
messages such as 'MySQL server has gone away', try increasing
max_allowed_packet in the MySQL server.

4/ If your database is over 1500 resources and you expect to export
(with the importexport plugin) lists of resources of at least this
length, then you should set public $WIKINDX_MEMORY_LIMIT = "64M"; in
config.php in order to avoid memory allocation errors.

5/ In what follows, 'wikindx/' refers to the top folder of your wikindx
installation.

6/ If you prefer to back up your existing wikindx/ installation, copy
all files in the folder elsewhere and back up the database before proceeding.
Should you need to restore, a) replace the wikindx database with the backed-up
one, b) remove files and folders in wikindx/, and c) re-place the backed-up
wikindx/ contents.

7/ Do not attempt to run WIKINDX before all upgrade steps have been completed.

>> IMPORTANT


UPGRADING <<

8/ Unzip WIKINDX into a folder on your computer -- this will create a wikindx/
folder.

9/ Copy the files and folders from the unzipped wikindx/ folder to the wikindx/
folder in the web server hierarchy.

10/ If you are upgrading a version less than 5.9.1, move attachments in the
old wikindx/attachments/ folder to wikindx/data/attachments/ and then delete
the old wikindx/attachments/ folder. Likewise, files in wikindx/images/ must be
moved to wikindx/data/images/ and the wikindx/images/ folder deleted.

11/ Now you are ready to run WIKINDX. On first running, WIKINDX will check that
several folders are writeable including:
'cache/',
'data/',
'components/languages/',
'components/plugins/',
'components/templates/',
'components/styles/',
'components/vendor/'

If anything is not writeable, you will be prompted to correct this.

12/ Run WIKINDX through your web browser (http://<my.wikindx.domain.example>/index.php).
If the database needs upgrading, you will be prompted. In order to account for
potential PHP timeouts when used on a large database, the database
upgrade takes place over numerous stages.

13/ Finally update official components (plugins, templates, bibliographic
styles, and languages) from the admin components panel of your WIKINDX
installation. If you have created custom components you must adapt
them to keep them working. Particularly in version 5.9.1, the
component.json file is added to describe the component.

   a/ Administrators who have written their own bibliographic styles for
   v3.x should open, edit, and save them in the plug-in adminstyle editor
   (download the plug-in from the WIKINDX Sourceforge page) prior to
   their use as this will add additions and amendments to those styles
   that are required in v4.x and up.

   b/ Language localizations from v3.8.x will still work in WIKINDX
   (before 5.9.1) but will be missing some messages (replaced
   automatically by English messages). v3.8.x localization files can be
   upgraded to v5 (before 5.9.1) by running them through the
   localization plug-in that can be downloaded from the WIKINDX
   Sourceforge site. From version 5.9.1 the translation system is
   replaced by Gettext. Previous catalogs will no longer work and we do
   not provide a migration script. We encourage you to join the common
   translation project on Transifex.

   c/ Administrators who have designed their own CSS/templates will need
   to do some editing. The templating subsystem has greatly changed in
   order to give template designers a lot more control over the visual
   design of WIKINDX. See wikindx/components/templates/default/ for examples.

   d/ To date, each version greatly modifies the interface between the
   core and the plugins. You will probably have to rewrite some of your
   private plugins. We encourage you to contact us to integrate them
   into the official distribution or at least to make yourself known so
   that we have an idea of the impact of the interface modifications.

   e/ If you want an embedded spell checker, you need to enable enchant
   extension in your php.ini or .htaccess file. See your webserver
   manual.

PS: However, it may happen that the rewriting of certain resource and
paper links to these images fails when the src attribute of the HTML img
tag does not appear first.

>> UPGRADING

    ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

--
Mark Grimshaw-Aagaard
The WIKINDX Team 2020
sirfragalot@users.sourceforge.net
