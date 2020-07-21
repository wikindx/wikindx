                      --o RELEASE CHECKLIST o--

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

This does not take into account the merge of temporary branches to
gather the code to be published in a single branch or the tests to
ensure the maturity of the code. This step is part of the development.

As the development is Trunk-based, the release is also based on trunk
for simplicity.

X.Y.Z is the number of the released version defined by the constant
WIKINDX_PUBLIC_VERSION. trunk is not a valid name.

I. Code preparation and checks

1. In the event of a change in structure or the addition of a
   third-party library in a plugin, update:

   - robots.txt
   - phpdoc.xml
   - The list of ignored files.
   - All php scripts that are used for a release

2. Build the manual of the trunk with the release script release/make.php.
   Is everything that has changed documented? Does the manual build
   without crashing? Does the release script execute without errors? Fix
   all issues.

   $ cd release
   $ php make.php

3. Check the release number in WIKINDX_PUBLIC_VERSION and the changelog.

4. Check the internal version number in WIKINDX_INTERNAL_VERSION.
   Increment it by one (and one only) if a core upgrade is needed.

5. Check that WIKINDX_PLUGIN_VERSION and $wikindxVersion in plugins have
   the same value.

6. Check that the core upgrade stage for this version is complete and
   functional: database, constants, variables, files, folders, messages,
   version numbers.

7. Check that the plugins upgrade for this version is complete and
   functional: database, constants, variables, files, folders, messages,
   version numbers.

8. Check if a code change has changed compatibility. Update
   WIKINDX_PHP_VERSION_MIN, WIKINDX_MYSQL_VERSION_MIN, and
   WIKINDX_MARIADB_VERSION_MIN.

9. Check if a code change has changed PHP extensions compatibility.
   Update \UTILS\listCoreMandatoryPHPExtensions() and
   \UTILS\listCoreOptionalPHPExtensions() functions.

10. Update "PHP & MySQL versions", "PHP extensions",
   and "Browser compatibility" sections in docs/README.md.

11. Update browser compatibility in docs/README.md.

12. Check that the changelog of the core is complete.

13. Check that the changelog of each component is complete.

14. Update WIKINDX_COPYRIGHT_YEAR.

15. Update the phpdoc fields @author, @copyright and @version of each
    file modified during the developement.

16. Update component.json file of components and check their integrity
    in the admin components panel.

17. Update the db schema of the Repair Kit.

    $ php dump-repairkit-schema.php

18. Update translations in SVN and push POT files on Transifex via the
    /home/project-web/wikindx/htdocs/transifex/pot directory on the SF
    Wikindx Website FTP. Wait for Transifex to update the resources
    (twice a day). You can force the update by hand but put the PORT
    file online before. Announce on Transifex a deadline for updating
    translations before the release.

    $ php make-languages.php

19. On the translation deadline, copy the PO files from Transifex to SVN
    and compile the MO files for Gettext.

    $ php make-languages.php

20. Update the changelog and component.json file of language components.

21. Sign components. If the hash of a component changes (use "svn diff"
    for checking it) then its code has changed. Check that its version
    number has been incremented and that the changelog is up to date,
    the component.json file is up to date. Sign them again (the hash
    doesn't change if the code doesn't change).

    $ php sign-components.php

22. When the components are up to date, build their packages from the
    trunk with the release script release/make.php.

23. Upload the content of release/trunk/files in the
    /home/pfs/project/wikindx/ directory of the SourceForge Wikindx
    Project FTP.

24. Upload the content of release/trunk/cus in the
    /home/project-web/wikindx/htdocs/cus directory of the SourceForge
    Wikindx Project FTP.

25. Try the update server.
    Don't forget to switch $WIKINDX_TRUNK_VERSION = TRUE;

26. When the components are ready commit them to SVN and don't change
    them anymore because their signature must be definitively fixed.


II. Website preparation

1. If a recurring request has appeared since the last release, add an
   entry to the website FAQ on this subject.

2. Create an entry in the website news folder for the changelog of this
   release.

3. Check all the pages of the website and correct what no longer matches
   the new version.

4. Check the main SourceForge page of the project and correct what no
   longer matches the new version.


III. SVN Release

During this step it is preferable that the release manager is the only
one to modify SVN.

1. Update your working copy.

   $ svn update

2. Check that you don't have uncommited changes. Go back to step I if
   you forgot something.

   $ svn status

3. Add an entry for the release in the SVN History section of the
   README.txt file at the root of the repository. The revision number
   indicated is the revision of the last commit included in the release.

At this point the version is officially released in SVN and should no
longer be changed so that the packaged code is identical.  If a last
minute bug is discovered in the code for example for a bug which crashes
the application and must at all costs be corrected, fix the bug in trunk
and start again at step I.

If the correction is very fast (a few tens of minutes) you can keep the
same version number because no one will have had the opportunity to
install from SVN.

If the correction takes too long then it is necessary to abandon the
publication of the current release, to increment the version number and
to make a new complete release. In the SVN history replace the Release
Date of this version with the mention "unreleased". To have absolute safety
of the published code you should only use this method.


IV. Public Release

*. In case you need to release and old version, use svn checkout xxx before
   switching on the last commit of this release. Don't forget to switch again
   to HEAD after the release!

1. cd in the release directory and execute the packaging script make.php
   from the CLI. Answer questions from the script (version=X.Y.Z and
   manual building=Y).

   $ cd release
   $ php make.php

   The layout created by this scripit matchs exactly the layout of FTP.

2. Without overwriting files already online, upload the content of
    release/X.Y.Z/files in the /home/pfs/project/wikindx/ directory
    of the SourceForge Wikindx Project FTP.

3. Without overwriting files already online, upload the content of
    release/X.Y.Z/cus in the /home/project-web/wikindx/htdocs/cus directory
    of the SourceForge Wikindx Project FTP.

4. Update the WIKINDX TEST DRIVE website and check if nothing bad append before
   publishing to directories that are not archives. If things go wrong, correct
   and redo the release from the begining.

5. Remove all files and folders of the current_release FTP directory
   of the SourceForge Wikindx Project FTP and upload the content of
   release/X.Y.Z/files/X.Y.Z instead.

6. On the Files section of the Wikindx SourceForge pages, go in
    directory X.Y.Z, display details of the wikindx_x.y.z.tar.bz2 file, select
    it as the default download for all systems (Link: Select all) and save.

7. If an old A.B.C version should no longer be highlighted, remove its A.B.C
    folder of the SourceForge Wikindx Project FTP. The automatic update for
    the A.B.C version will continue to work because it uses the copies of the
    archives/ folders, which have not been deleted.


V. Announce

1. Upload to the SourceForge Wikindx Website FTP all pages prepared for
   the release.

2. Announce the release on wikindx-news SourceForge mailing list.

3. Post about the release on the SourceForge News section.


VI. BugTracker update

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


--
The WIKINDX Team 2020
sirfragalot@users.sourceforge.net

