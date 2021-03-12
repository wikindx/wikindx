+++
title = "Release Checklist"
date = 2021-01-30T00:08:41+01:00
weight = 7
+++

This does not take into account the merge of temporary branches to
gather the code to be published in a single branch or the tests to
ensure the maturity of the code. This step is part of the development
and is exceptional.

As the development is Trunk-based, the release is also based on trunk
for simplicity.

__X.Y.Z__ is the number of the released version defined by the constant
**WIKINDX_PUBLIC_VERSION**. trunk is not a valid name.


## Code preparation and checks

1. In the event of a change in structure or the addition of a
   third-party library in a plugin, update:

   - robots.txt
   - phpdoc.xml
   - The list of ignored files.
   - All php scripts that are used for a release

2. Build the manual of the trunk with the release script `release/cli-make-api-manual.php`.
   Is everything that has changed documented? Does the manual build
   without crashing? Does the release script execute without errors? Fix
   all issues.

~~~~sh
   $ php release/cli-make-api-manual.php
~~~~

3. Check the release number in **WIKINDX_PUBLIC_VERSION** and the changelog.

4. Change **WIKINDX_RELEASE_DATE** to the current date
   and **WIKINDX_COPYRIGHT_YEAR** to the current year.

5. Check the internal version number in **WIKINDX_INTERNAL_VERSION**.
   Increment it by one (and one only) if a core upgrade is needed.

6. Check that **WIKINDX_COMPONENTS_COMPATIBLE_VERSION["plugin"]**
   and $wikindxVersion in plugins have the same value.

7. Check that the core upgrade stage for this version is complete and
   functional: database, constants, variables, files, folders, messages,
   version numbers.

8. Check that the plugins upgrade for this version is complete and
   functional: database, constants, variables, files, folders, messages,
   version numbers.

9. Check if a code change has changed compatibility. Update
   **WIKINDX_PHP_VERSION_MIN**,
   **WIKINDX_PHP_VERSION_MAX**,
   **WIKINDX_MYSQL_VERSION_MIN**,
   and **WIKINDX_MARIADB_VERSION_MIN**.

10. Check if a code change has changed PHP extensions compatibility.
   Update `\UTILS\listCoreMandatoryPHPExtensions()` and
   `\UTILS\listCoreOptionalPHPExtensions()` functions.

11. Check that the changelog of the core is complete.

12. Check that the changelog of each component is complete.

13. Update component.json file of components and check their integrity
    in the admin components panel.

14. Update the db schema of the Repair Kit.

~~~~sh
    $ php trunk/cli-dump-repairkit-schema.php
~~~~

15. Update translations in SVN and push POT files on Transifex via the
    `/home/project-web/wikindx/htdocs/transifex/pot` directory on the SF
    Wikindx Website FTP. Wait for Transifex to update the resources
    (twice a day). You can force the update by hand but put the POT
    file online before. Announce on Transifex a deadline for updating
    translations before the release.

~~~~sh
    $ php trunk/cli-make-languages.php
~~~~

16. On the translation deadline, copy the PO files from Transifex to SVN
    and compile the MO files for Gettext.

~~~~sh
    $ php trunk/cli-make-languages.php
~~~~

17. Sign components. If the hash of a component changes (use "svn diff"
    for checking it) then its code has changed. Check that its version
    number has been incremented and that the changelog is up to date,
    the component.json file is up to date. Sign them again (the hash
    doesn't change if the code doesn't change).

~~~~sh
    $ php trunk/cli-sign-components.php
~~~~

18. When the components are up to date, build their packages from the
    trunk with the packaging script. Answer questions from the script (version=trunk).

~~~~sh
    $ php release/cli-make-package.php
~~~~

19. Upload the content of `release/trunk/files` in the
    `/home/pfs/project/wikindx/archives` directory of the SourceForge Wikindx
    Project FTP.

20. Upload the content of `release/trunk/cus` in the
    `/home/project-web/wikindx/htdocs/cus` directory of the SourceForge
    Wikindx Project FTP.

21. Try the update server. Don't forget to switch the Trunk Version flag
    in debug configuration.

22. When the components are ready commit them to SVN and don't change
    them anymore because their signature must be definitively fixed.


## API Manual Release

Generate the manual for the trunk and the current version and upload them on SF.
The __Contributing  > API Manual__ give details about that.

~~~~sh
   $ php release/cli-make-api-manual.php
~~~~


## Website Release

1. If a recurring request has appeared since the last release, add an
   entry to the website FAQ on this subject.

2. Update the requirements in __Install & Upgrade > Requirements__ section.

3. Mirror the content of `CHANGELOG.txt` file in __Install & Upgrade > Release Notes__ section.

4. Check the __Help Topics__ section is up to date and any topic is missing or misnamed.

5. Check all the pages and correct what no longer matches the new version.

6. Check the main SourceForge page of the project and correct what no
   longer matches the new version.

7. Generate the website for the trunk and the current version and upload them on SF.
  The __Contributing  > Website__ give details about that.

~~~~sh
    $ php release/cli-make-web.php
~~~~


## SVN Release

During this step it is preferable that the release manager is the only
one to modify SVN.

1. Update your working copy.

~~~~sh
   $ svn update
~~~~

2. Check that you don't have uncommited changes. Go back to step 1 of __Code preparation and checks__ if
   you forgot something.

~~~~sh
   $ svn status
~~~~

3. Add an entry for the release in the SVN History section of the
   `README.txt` file at the root of the repository. The revision number
   indicated is the revision of the last commit included in the release.

At this point the version is officially released in SVN and should no
longer be changed so that the packaged code is identical.  If a last
minute bug is discovered in the code for example for a bug which crashes
the application and must at all costs be corrected, fix the bug in trunk
and start again at step 1 of __Code preparation and checks__.

If the correction is very fast (a few tens of minutes) you can keep the
same version number because no one will have the opportunity to
install from SVN.

If the correction takes too long then it is necessary to abandon the
publication of the current release, to increment the version number and
to make again a complete release. In the SVN history replace the Release
Date of this version with the mention "unreleased". To have absolute safety
of the published code you should only use this method.


## Public Release

In case you need to release and old version, use svn checkout xxx before
switching on the last commit of this release. Don't forget to switch again
to HEAD after the release!

1. Execute the packaging script from the CLI.
   Answer questions from the script (version=X.Y.Z).

~~~~sh
   $ php release/cli-make-package.php
~~~~

   The layout created by this script matchs exactly the layout of FTP.

2. Without overwriting files already online, upload the content of
    `release/X.Y.Z/files` in the `/home/pfs/project/wikindx/` directory
    of the SourceForge Wikindx Project FTP.

3. Without overwriting files already online, upload the content of
    `release/X.Y.Z/cus` in the `/home/project-web/wikindx/htdocs/cus` directory
    of the SourceForge Wikindx Project FTP.

4. Update the WIKINDX TEST DRIVE website and check if nothing bad append before
   publishing to directories that are not archives. If things go wrong, correct
   and redo the release from the begining.

5. Remove all files and folders of the current_release FTP directory
   of the SourceForge Wikindx Project FTP and upload the content of
   `release/X.Y.Z/files/X.Y.Z` instead.

6. On the Files section of the Wikindx SourceForge pages, go in
    directory X.Y.Z, display details of the wikindx_x.y.z.tar.bz2 file, select
    it as the default download for all systems (Link: Select all) and save.

7. If an old A.B.C version should no longer be highlighted, remove its A.B.C
    folder of the SourceForge Wikindx Project FTP. The automatic update for
    the A.B.C version will continue to work because it uses the copies of the
    `archives/` folders, which have not been deleted.


## Announce

1. Announce the release on wikindx-news SourceForge mailing list.

2. Post about the release on the SourceForge News section.


## BugTracker update

1. Go in "Bugs and feature requests" section of the SourceForge Bugtracker

2. Click on "Edit Searches" menu

3. Click on "Field Management" menu

4. Update or create a "Found in" milestone entry for the release X.Y.Z
   and fill it with the same infos than the SVN History (put the revision
   number in the Description field). Check the "Complete" column for older
   versions that are no longer supported.

5. Update or create a "Target" milestone entry for the release X.Y.Z
   and fill it with the same infos than the SVN History (put the revision
   number in the Description field). Check the "Complete" column of the
   current release.

6. Save these changes.

7. If this is not done, assign all the tickets processed in this release
   and close them. Reassign unprocessed tickets if necessary.
