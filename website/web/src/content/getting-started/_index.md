+++
title = "Getting started"
date = 2021-01-30T00:08:41+01:00
weight = 5
chapter = true
#pre = "<b>1. </b>"
+++



# Preamble (Mark Grimshaw-Aagaard)

I originally started developing this as a means to help organise my PhD
research  by cataloguing  bibliographic notations,  references, quotes
and thoughts on a computer via a program that was not tied to a single
operating system (like similar and expensively commercial software) and
that could be accessed from anywhere on the web (unlike other systems).
Additionally, I wanted a quick way to search, for example, for all
quotes and thoughts referencing a keyword or to be able to automatically
reformat bibliographies to different style guides.

As this is  a system designed to  run on any web server,  I thought its
use could be expanded to groups of researchers who could all contribute
to  and  read  the index. This  concept is  very  similar to  a Wiki
Encyclopaedia  where anyone can add or edit entries in an on-line
encyclopaedia.

Since the original ideas, various others have been implemented such as a
wide variety of import and export options and, importantly, the
incorporation of a WYSIWYG word processor that can insert citations and
(re)format them with a few clicks of the mouse.  This was powerful
enough for me to write my entire PhD thesis in. (v4 removed this feature
to a plug-in rather than being a core feature.)

Developed under the [ISC License] (since v6.3.5) (Creative Commons
[CC-BY-NC-SA 4.0] license for v6.0.1 to v6.3.4; [GPL 2.0] before that),
the project homepage can be found at:
<https://sourceforge.net/projects/wikindx/> and the required
files/updates and a variety of components are freely available there.







A test database (wikindxX_Y_testDatabase.sql, with X_Y the major version
targeted by the schema of this base) is provided in docs/.

Use PhpMyAdmin (or similar) to create a database and
add a username/password to it then import the file
wikindxX_Y_testDatabase.sql. Add the database name and
username/password to wikindx/config.php and then run WIKINDX.

Three users (username/password):

 * Administrator -- super/super
 * user1/user1
 * user2/user2

There are 83 resources entered with categories, keywords, abstracts,
notes and metadata (quotations, paraphrases, musings) and at least two
resources per resource type.

user2 has a private bibliography. There is a user group which has two
members (super and user1) and which has a private group bibliography
(superBibliography).

Some maturity indices have been set and there are some popularity
ratings/number of views.

No attachments have been added to any resource.
