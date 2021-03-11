+++
title = "Upgrade"
date = 2021-01-30T00:08:41+01:00
weight = 4
+++

## Preliminary

If you are upgrading, read this document carefully. Follow the instructions
below depending on the starting version.

Instead, you can also install again Wikindx in a different folder
from a copy of the `components` and `data` folders and a backup of the database.

__BACK UP YOUR DATABASE BEFORE ANY UPGRADING!!!!!!!__

__BACK UP YOUR DATA FILES BEFORE ANY UPGRADING!!!!!!!__

__BACK UP YOUR SOURCE CODE BEFORE ANY UPGRADING!!!!!!!__

Backup doesn't cost more, is quick and prevents any definitive lost of data.

Use our BackupMysql plugin which make a backup with PHP or the [mysqldump](https://mariadb.com/kb/en/mysqldump/) tool from the cli. mysqldump is the best choice for large databases because it has no execution time limit. The following command should work for ordinary MySQL configurations. Replace values quoted by `<>`. On Windows don't replace the `--result-file` option by the pipe syntax (>) because the ends of line and the encoding can be corrupted.

Example:

~~~~sh
mysqldump --user=<dbusername> --password=<dbpwd> --result-file=<wikindx_backup_xyz_yyyymmdd.sql> <dbname>
~~~~

~~~~sh
mysqldump --user=wkxuser --password=4hv563UFM9Hnte5J --result-file=wikindx_backup_640_20210310.sql wikindxdb
~~~~


## Upgrading components

From v5.9.1, components are updated and managed from the __Components Manager__ (Menu entry: _Admin > Components_). Update them after the core is fully upgraded.

The cURL and Zip PHP extensions are not mandatory but used by the __Components Manager__. We recommend to enable these extensions and use the __Components Manager__.

Without cURL, components cannot be downloaded by Wikindx but you can still manage to install
a component with the top form of the __Components Manager__ from a local copy downloaded from Sourceforge.

Without Zip, you cannot install components via the __Components Manager__ and
have to do it by hand.

If you are upgrading a components by hand, follow these steps:

1. Download the component source code from the [SourceForge Files](https://sourceforge.net/projects/wikindx/files/) section. Until v6.3.5 each version has a `archives/X.y.Z/components` folder on SF where components are stored. After v6.3.5 components are stored in `components/<type>/<components_compatible_version>/` folder on SF. <__components_compatible_version__> is a number specific to the core of the version installed. Find this number in **WIKINDX_COMPONENTS_COMPATIBLE_VERSION** constant in `core/startup/CONSTANTS.php` file.

2. Uncompress the source code into a folder on your computer -- this will create a folder named after its __component id__.

3. Copy the uncompressed folder inside the `components/<type>` folder of the current installation (<__type__> is the folder name of the type of the component).

4. Enable the component if the core upgrade disabled it.


## Upgrading from v6.x serie

You no longer need to update the `config.php` file by hand as in previous versions.
This step is automatic. If `config.php` changes, the previous file will be
saved with the current date and time in the website root directory.

WIKINDX uses caching in the database _cache table for lists of
creators, keywords etc. For large WIKINDX databases, if you receive
messages such as 'MySQL server has gone away', try increasing
**max_allowed_packet** in the MySQL server.
4/ If your database is over 1500 resources and you expect to export
(with the importexport plugin) lists of resources of at least this
length, then you should set `public $WIKINDX_MEMORY_LIMIT = "64M"` (64MB minimum); in
`config.php` in order to avoid memory allocation errors.

PS: However, it may happen that the rewriting of certain resource and
paper links to these images fails when the src attribute of the HTML img
tag does not appear first.

If you are upgrading a previous installation of WIKINDX 6.x, simply:

1. Back up the database with mysqldump or the BackupMysql plugin.

2. Back up the `data` folder of the current installation.

3. Back up the source code (all but `data` folder) of the current installation.

4. Transfer the backups to a safe place (not on your server or the folder tree of the current installation...).

2. Download the source code of the core from the [SourceForge Files](https://sourceforge.net/projects/wikindx/files/) section.

3. Uncompress the source code into a folder on your computer -- this will create a `wikindx`
folder.

4. In the web server hierarchy, optionally remove the `core` and `dbschema` folders.
You can do this from time to time for cleaning up old code deleted from previous versions.

4. Copy the files and folders from the unzipped `wikindx` folder to the root
folder of your website in the web server hierarchy. This will overwrite
the source code of the existing installation.

5. If you are upgrading a version less than 5.9.1, move attachments in the
old `wikindx/attachments/` folder to `wikindx/data/attachments/` and then delete
the old `wikindx/attachments/` folder. Likewise, files in `wikindx/images/` must be
moved to `wikindx/data/images/` and the `wikindx/images/` folder deleted.

6. Now you are ready to run WIKINDX. On first running, WIKINDX will check PHP/MySQl versions and that
several folders are writeable including:

    - `cache/'`
    - `data/'`
    - `components/languages/'`
    - `components/plugins/'`
    - `components/templates/'`
    - `components/styles/'`
    - `components/vendor/`

6. Run Wikindx through your web browser and follow the instructions on screen. You will go through the following steps:

    - If anything is not writeable, you will be prompted to correct this.
    - If the PHP version is wrong, you will be prompted to correct this.
    - If the MySQL version is wrong, you will be prompted to correct this.
    - If the database needs upgrading, you will be prompted.
    - Logon as Super Administrator (simple administrators cannot upgrade).
    - Upgrade of the database schema step by step. In order to account for
      potential PHP timeouts when used on a large database, the database
      upgrade takes place over numerous stages.
    - Data migration (optional step).
    - `config.php` upgrade (optional step).
    - Cache refresh (check component compatibility and clear template caches).

7. Finally update the official components from the admin __Components Manager__. If a component is not compatible with the new core, it will be disabled automatically. After their update, enable them again.

8. If you have created custom components you must adapt
them to keep them working. Particularly in version 5.9.1, the
`component.json` file is added to describe the component.

   a) Administrators who have written their own bibliographic styles for
   v3.x should open, edit, and save them in the plug-in adminstyle editor
   prior to their use as this will add additions and amendments to those
   styles that are required in v4.x and up.

   b) Administrators who have designed their own CSS/templates will need
   to do some editing. The templating subsystem has greatly changed in
   order to give template designers a lot more control over the visual
   design of WIKINDX. See `wikindx/components/templates/default/` for examples.

   c) To date, each version greatly modifies the interface between the
   core and the plugins. You will probably have to rewrite some of your
   private plugins. We encourage you to contact us to integrate them
   into the official distribution or at least to make yourself known so
   that we have an idea of the impact of the interface modifications.


## UTF-8 migration (for 5.x to 6.2.0 users)

__Note: *Do not attempt a conversion if you do not see corrupted character display,
you may get the opposite effect. If in doubt ask for assistance on the forum.*__

Databases used since v5.1 might exhibit errors in the conversion to UTF-8
characters that came in with v5.1. The RepairKit plugin attempts to fix these
issues but, because the UTF-8 correction code was removed in this plugin from
v6.2.1, databases that have been upgraded since before 5.1 but have not had
their UTF-8 repaired with the plugin, should follow this procedure:

1. Download and install WIKINDX 6.0.8 or 6.1.0 according to your PHP version.
2. Run WIKINDX and follow the database upgrade.
3. Install the RepairKit plugin for the relevant wikindx version and run the
   plugin’s __Fix chars__ code.
4. If you do not have PHP 7.0 or greater, you cannot go further than using
   WIKINDX v6.0.8. If you have PHP 7.0 or greater, then you can now safely download
   and install the highest version of WIKINDX available.

The above results from a code clean-out and the understanding that there are
very few (if any) current users of WIKINDX with pre-v5.1 databases.


## Upgrading from v5.x serie

Upgrading v5.x database is supported by this version. If you use an old version
of PHP the oldest versions supporting an upgrade form v5.x serie are (see `docs/UPGRADE.txt`):

- v6.2.0 which supports PHP 7.0 to 7.4.
- v6.4.0 which supports PHP 7.3 to 7.4.
- trunk which supports PHP 7.3 to 8.0 (partial).

After going through these intermediate versions
you can update a second time to a more recent version.


## Upgrading from v3.8.x or v4.x series

Upgrading v3.8.x or v4.x database is not supported anymore by this version.
For this case you need to use one of the two stable transition versions and
follow their instructions (see `docs/UPGRADE.txt`):

- v6.0.8 which supports PHP 5.6 to 7.3.
- v6.1.0 which supports PHP 7.0 to 7.4.

After going through these intermediate versions
you can update a second time to a more recent version.
