+++
title = "Install"
date = 2021-01-30T00:08:41+01:00
weight = 3
+++

## Preamble

The versions are numbered for the history and the semi-automatic
data and database update system (each change is applied between
the installed version and the target version). However, always
read the Upgrade chapter for instructions on upgrading.

So we recommend that you regularly update to the latest version from the
tarball version available in the [SourceForge File](https://sourceforge.net/projects/wikindx/files/) section,
especially if your wikindx is hosted on the web.

If you prefer an installation from a [Version Control Systems](https://en.wikipedia.org/wiki/Version_control) (VCS),
__we strongly recommend__ that you use one of the __point release__ described
in the README.txt file at the [root of SF SVN](https://sourceforge.net/p/wikindx/svn/HEAD/tree/)
with the __trunk__ branch on a __production__ server.

The __trunk__ branch (for developers and testers) can be broken at any
(and for a long) time and damage your database.


## Components compatibility

Wikindx, the core application, and officials components are developed
together: templates (themes), styles (bibliographic styles) and PHP
plugins. The additional vendor component type contains third-party
software.

Wikindx comes with __default__ template, __APA__ style and main vendor
components pre-installed. No plugins are pre-installed. Translations are
included in the core or plugins.

All components are available on [SourceForge File](https://sourceforge.net/projects/wikindx/files/) section for a manual
installation or via the component update system embedded in Wikindx.

Each official vendor component is released with a new version of the
application and only for the last version. Others components could
cover many Wikindx versions.

For reasons of immaturity of the system of components it is recommended
to contribute to the official development team so that the components
are always compatible with the latest version. If you create your own
components it is not guaranteed that they will work on a later version
of Wikindx.

## Installation

In all cases, the following steps need to be taken once the web server
is up and running, and the database have been created.

The details may be slightly different (especially steps 4 to 7) depending
on your MySQL client or whether running WIKINDX on a hosted web environment
or locally but the principles are the same. Assume we are running
a <http://wikindx.test> website installed in a `/var/www/wikindx` folder
of the web server.


1. Download the source code of the core from the [SourceForge Files](https://sourceforge.net/projects/wikindx/files/) section.

2. Uncompress the source code into a folder on your computer -- this will create a `wikindx` folder.

3. Copy the files and folders from the uncompressed `wikindx` folder
   to `/var/www/wikindx` that you setup on previous chapter.

4. Copy the `config.php.dist` file to `config.php`.

5. Edit the `config.php` file and set the RDBMS connection parameters
   (more if needed). Each setting is documented in the file.

```php
// See https://www.php.net/manual/en/mysqli.construct.php
public $WIKINDX_DB_HOST = "localhost";
public $WIKINDX_DB = "wikindx6";
public $WIKINDX_DB_USER = "wikindx";
public $WIKINDX_DB_PASSWORD = "wikindx";
```

6. Optionally, ensure the web server environment is running
   (with ps, top or an other process monitoring software).

7. Type in the WIKINDX address in the web browser – if running locally,
   this will be <http://wikindx.test/> – to complete your WIKINDX configuration. You will go through the following steps:

    - If the PHP version is wrong, you will be prompted to correct this.
    - If mandatory PHP extensions are missing, you will be prompted to correct this.
    - If the `config.php` file is missing, you will be prompted to correct this.
    - Missing folders will be created (cache and data).
    - If anything is not writeable, you will be prompted to correct this.
    - If the MySQL version is wrong, you will be prompted to correct this.
    - The database will be populated with tables.
    - Setup of the Super Administrator account.

8. Go to __Admin > Configure__ menu and set global preferences.

9. Go to __Wikindx > My Wikindx__ menu and set the Super Administrator preferences.

10. Finally install and enable components from the __Components Manager__  (__Admin > Components__ menu).

You single user install of Wikindx is ready. Have fun!


## Permissions

If you are running WIKINDX locally on Windows (using something like
XAMPP), you can skip the perms step as the folders will be writable by
default.

If you are running a Unix machine WIKINDX will not function correctly
if various folders and files within them are not writeable for the web server user.

The install process will show with current Unix permissions. You should made folders
and files readable and writeable (along with their contents) for the web server user.
The web server user can be the owner and/or the group of those folders. So you have to modify,
the owner, the group and the permission bits according to the particular configuration
of your web server (usually web servers use 'nobody', 'www-data', or 'daemon' users),
PHP and file transfer software.

You may also be required to add the execution bit in certain cases.
The same rights apply to files in these folders, but this script does not
check them for performance reasons. See the chmod, web server and PHP manuals
for details. r = readable; w = writable ; x = executable.

The following commands should give a good result in the general case
where the web server user should be "user" or "group" (760, 670 or 770
should work):

~~~~sh
cd /var/www/wikindx
chown -R www-data:www-data *
chmod -R 777 *
~~~~

As a last resort you can use the 777 mode but it is a major security
flaw.  You don't have to get to this end if the owners are set up
correctly.

When your web site code is a clone of the SVN repository (not
recommended) the owner of the root SVN folder, .svn folders, and all
files and folders under .svn need to be readable, writable and
executable (7).

Wikindx create folders with 777 permissions so that it works in all
cases, notably the installation of the core and components, for managing
caches and data. You can correct it later if you want the best possible
security.

__WARNING: *if you started to install without taking into account the
permissions you may find yourself blocked with a blank page because of
the template system which creates incomplete cached files. Each time you
configure permissions, delete all the files and folders found under the
`cache/templates/` tree.*__


### Note

The `data/files/` directory is used for the temporary storage of RTF,
RIS, Endnote, BibTeX and other files for the user to download. The
scripts within WIKINDX will mark these files for deletion after so many
seconds have passed since their last modification (you can configure
this through the web browser) . This doesn't necessarily mean that they
will be immediately deleted: they will be deleted the next time someone
exports a file.
