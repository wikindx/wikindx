            --o Organization of the source repository o--

---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

If you prefer an installation from a source management client,
WE STRONGLY RECOMMAND that you use the STABLE branch on a PRODUCTION server.

The TRUNK branch can be broken at any (and for a long) time and damage your database,
while the STABLE branch has been debugged and validated.

---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

- Main wikindx code is in wikindx/ and is divided in folders :

     - tags/    archives the past releases
     - stable/  contains the last release (recommended for production)
     - trunk/   contains the code in development (for developers and testers)

- Plugins are in wikindx/.../plugins/

- Some maintenance scripts are in scripts/

- A test database is provided in wikindx/.../docs/. Read docs/README_TESTDATABASE.txt.

- Internal documentation can be found in wikindx/.../docs/

- The source code of wikindx.sourceforge.net web site is in website/

- tools/, release/ and make.php are used to release Wikindx

- componentListServer.php is used to serve the list of components for update from a server

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

 SVN History

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

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

Version notes and instructions to upgrade or install Wikindx
are provided with each release in wikindx/.../docs/ folder.

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

--
The WIKINDX Team
sirfragalot@users.sourceforge.net
