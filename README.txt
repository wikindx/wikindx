            --o Organization of the source repository o--

---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

If you prefer an installation from a source management client, WE STRONGLY
RECOMMAND that you use the STABLE branch on a PRODUCTION server.

The TRUNK branch can be broken at any (and for a long) time and damage your
database, while the STABLE branch has been debugged and validated.

https://github.com/wikindx/wikindx is a mirror of the SVN repository.

---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

- Main wikindx sources are in folders:

     - trunk/        contains the development sources
     - branches/     contains temporary code derived from trunk for development


- Other folders:

     - tools/        tools used to develop or release Wikindx
     - release/      is used to build the release packages


- Other files:

     - release/make.php is the main release script
     - release/README.txt describes the release process

     - website/downloads/componentListServer.php is used to serve the list of
       components from an update server

     - website/downloads/ stores the components list of each version released in
       a subfolder named after their version number


- Components sources are stored in components/ subfolders of each wikindx version.
- Internal documentation is stored in the docs/ folder of each wikindx version.
- The API manual is not stored in SVN but build at the release time.

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

Version notes and instructions to upgrade or install Wikindx are provided with
each release in docs/ folder.

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

SVN History

This repository archives wikindx sources from version 6.2.1 at revision 3959.

The previous versions can be viewed in trunk folder at:

https://sourceforge.net/p/wikindxold/svn/HEAD/tree/

 Version    Release Date   Revision
 ---------  ------------   --------
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

--
The WIKINDX Team
sirfragalot@users.sourceforge.net
