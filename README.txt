            --o Organization of the source repository o--

---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

The TRUNK can be broken at any (and for a long) time and damage your
database, while a RELEASED revision has been debugged and validated.

For an installation or an update of the core preferably use the FILES
section of SourceForge.

If you prefer an installation from a source management client, WE STRONGLY
RECOMMAND that you use a RELEASED revision on a PRODUCTION server. To find
the revision corresponding to the desired version, see “Release History" section.

After some code loss, we gave up using BRANCHES and TAGS.

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

Version notes and instructions to upgrade or install Wikindx from a tarball
is included in docs/ folder of each release tarball. Go to SourceForge FILES
section to download them.

---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

To install from a SVN clone, go to the parent folder of your website sources
folder and use one of these commands to clone the source code in a sub-folder:

   svn checkout https://svn.code.sf.net/p/wikindx/svn/trunk <website-directory>
or
   svn checkout svn://svn.code.sf.net/p/wikindx/svn/trunk <website-directory>

<website-directory> must not exists before you clone. 


Keep in mind that SVN does not use the same permissions as your web server
or PHP FPM. Therefore you will have to modify the permissions with each update,
torture the groups of SVN and your web server or configure very permissive
rights so that the web server, PHP and SVN work at the same time.

Each of these solutions involve SECURITY CONCERNS, which is why
we do not recommend this way outside of a TEST or DEVELOPMENT server.


E.g., to clone in /var/www/wikindx folder

   $ cd /var/www
   $ svn checkout svn://svn.code.sf.net/p/wikindx/svn/trunk wikindx


To update your installation from an SVN clone, got to <website-directory> and use the command:

   svn update -r <rev>

E.g. to retrieve the source code of 6.3.10, use:

   $ cd /var/www
   $ svn update -r 632

---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

Repository locations

- Active repository:      https://sourceforge.net/p/wikindx/svn/HEAD/tree/

- Archived repository:    https://sourceforge.net/p/wikindxold/svn/HEAD/tree/

- Github readonly mirror: https://github.com/wikindx/wikindx

---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

Repository layout

- Sources are in folders:

     - trunk/               contains the sources of the project

     - trunk/components/    sources of officials components

     - trunk/docs/          public and internal documentation sources of Wikindx

     - website/             contains the sources of the project website


- Tools scripts

     - tools/               tools used to develop or release Wikindx

     - release/make.php     is the used to build the release packages

     - release/README.txt   describes the release process


- Other folders:

     - website/cus/index.php is used to serve the list of
       components from the update server configured in Wikindx 6.3.6 and later

     - website/cus/components stores descriptions in json format of each component
       version released since Wikindx version 6.3.6. The format of its subfolders
       must respect the scheme <component_type>/<compatibility_version>/.

     - website/cus/core stores the definition in json format of the component
       compatibility versions for each core version since Wikindx version 6.3.6


     - website/downloads/components_server.php is used to serve the list of
       components from the update server configured in Wikindx 5.9.1 to 6.3.5

     - website/downloads/ stores the components list of each version released in
       a subfolder named after their version number for Wikindx 5.9.1 to 6.3.5

     - The API manual is not stored in SVN but build at the release time.

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

Release History (Active repository)

 Version    Release Date   Revision
 ---------  ------------   --------
  6.4.0      2020-21-21        1115
  6.3.10     2020-08-30         632
  6.3.9      2020-08-29         621
  6.3.8      2020-08-19         593
  6.3.7      2020-07-21         532
  6.3.6      2020-07-21         525
  6.3.5      2020-07-14         503
  6.3.4      2020-07-09         480
  6.3.3      2020-07-06         447
  6.3.2      2020-07-05         442
  6.3.2-b2   2020-07-03         423
  6.3.2-b1   2020-06-20         404
  6.3.1      2020-06-11         383
  6.3.0      2020-05-25         356
  6.2.2      2020-03-27         283

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

Release History (Archived repository)

Revision 1 of active repository is the same as revision 3959 of the archived
repository.

The sources of wikindx were not versioned before version 4.0.0.

The source code for major version 3.8.1 was also imported because it can help
track migration errors from v3 to v4 whithout unpacking an archive, but importing
afterwards does not allow to use all of the SVN tools because the order of the revision
does not follow the chronological order. Older versions will not be imported.

 Version   Release Date   Revision
 -------   ------------   --------
  6.2.1     2020-02-19        3947
  6.2.0     2020-02-11        3924
  6.1.0     2020-02-10        3920
  6.0.8     2020-02-10        3915
  6.0.7     2020-02-01        3788 (+ 3800 + 3813)
  6.0.6     2020-01-30        3750
  6.0.5     2020-01-28        3729
  6.0.4     2020-01-28        3722
  6.0.3     2020-01-26        3698
  6.0.2     2020-01-25        3676
  6.0.1     2020-01-20        3641
  6         2020-01-12        3613
  5.9.1     2020-01-08        3546
  5.8.2     2019-08-20        2802
  5.8.1     2019-07-05        2788
  5.7.3     2019-06-05        2770
  5.7.2     2019-05-31        2743
  5.7.1     2019-04-30        2695
  5.7.0     2019-03-25        2659
  5.3.2     2018-09-16        2438
  5.3.1     2018-11-06        2339
  5.2.2     2018-03-14        2154
  5.2.1     2017-12-17        2091
  5.2.0     2017-12-13        2068
  4.2.2     2014-09-27        1042
  4.2.1     2013-05-13         992
  4.2.0     2013-02-24         874
  4.0.5     2012-02-05         469
  4.0.3     2012-01-17         451
  4.0.0     2008-07-22           1
  3.8.1     2007-12-07        2760
  3.7.1     2007-08-19           -
  3.7.0     2007-07-29           -
  3.6.5     2007-06-03           -
  3.6.4     2007-05-13           -
  3.6.3     2007-05-02           -
  3.6.2     2007-04-28           -
  3.6.1     2007-04-19           -
  3.6.0     2007-04-16           -
  3.5.0     2007-01-03           -
  3.4.7     2006-11-12           -
  3.4.6     2006-10-31           -
  3.4.5     2006-10-24           -
  3.4.4     2006-10-09           -
  3.4.3     2006-09-19           -
  3.4.2     2006-09-10           -
  3.4.1     2006-08-22           -
  3.4.0     2006-08-08           -
  3.3.2     2006-07-10           -
  3.3.1     2006-05-19           -
  3.3.0     2006-05-15           -
  3.2.4     2006-02-08           -
  3.2.3     2006-02-07           -
  3.2.2     2005-12-20           -
  3.2.1     2005-12-02           -
  3.2.0     2005-11-22           -
  3.1.1     2005-07-19           -
  3.1.0     2005-06-29           -
  3.0.6     2005-05-31           -
  3.0.5     2005-05-22           -
  3.0.4     2005-05-20           -
  3.0.3     2005-05-13           -
  3.0.2     2005-05-13           -
  3.0.1     2005-04-18           -
  3.0.0     2005-04-13           -
  2.3.6     2005-02-03           -
  2.3.5     2005-01-19           -
  2.3.4     2005-01-14           -
  2.3.3     2005-01-12           -
  2.3.2     2005-01-11           -
  2.3.1     2004-12-18           -
  2.3.0     2004-12-18           -
  2.2.0     2004-12-05           -
  2.1.1     2004-11-22           -
  2.1.0     2004-11-20           -
  2.0.0     2004-09-02           -
  1.5.1.1   2005-06-01           -
  1.1.0     2004-05-04           -
  1.0.0     2004-05-03           -
  0.9.9i    2004-04-29           -
  0.9.9h    2004-04-25           -
  0.9.9g    2004-04-15           -
  0.9.9f    2004-04-09           -
  0.9.9e    2004-04-03           -
  0.9.9d    2004-03-28           -
  0.9.9c    2004-03-23           -
  0.9.9b    2004-03-14           -
  0.9.9     2004-03-04           -
  0.9.8     2004-02-26           -
  0.9.7     2004-02-24           -
  0.9.6     2004-02-23           -
  0.9.5b    2004-02-19           -
  0.9.4b    2004-02-17           -
  0.9.3     2004-02-14           -
  0.9.2     2004-02-11           -
  0.9.1     2004-02-09           -

--
The WIKINDX Team
sirfragalot@users.sourceforge.net
