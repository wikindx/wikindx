+++
title = "Coding"
date = 2021-01-30T00:08:41+01:00
weight = 2
+++


The development style is [Trunk-Based](https://trunkbaseddevelopment.com/),
but sometimes an important development is the subject of an ephemeral branch.

The version system is not a branch system with long term support for
each one.  Only the trunk gets new features, security and bug fixes that
are not backported.  These developments are made available to the public
at each release of a new version.

The versions are numbered for the history and semi-automatic update
system of the data and the database (each change is applied between the installed
version and the target version).

The [README.txt file](https://sourceforge.net/p/wikindx/svn/HEAD/tree/) at the root of SVN describes how to get the source code.

The __trunk__ branch (for developers and testers) can be broken at any
(and for a long) time and damage your database.
