+++
title = "First try"
date = 2021-01-30T00:08:41+01:00
weight = 5
+++

For testing purpose we provide a test database at ([`demo_wikindx_database.sql`](../demo_wikindx_database.sql)).

To test it follow the instructions in the Install section. But instead of starting the installation by calling the index.php script the first time, use a MySQL client such as phpMyAdmin to import the `demo_wikindx_database.sql` file in your database.
 
If you missed this last point destroy all tables of the database before the import. Once the test is complete destroy all tables of the database and call the index.php page to start the install setup.

The test database has three users:

|User          | Username | Password
|--------------|----------|-------------
|Administrator | super    | superW!k1ndx
|User 1        | user1    | user1W!k1ndx
|User 2        | user2    | user2W!k1ndx


There are 83 resources entered with categories, keywords, abstracts,
notes and metadata (quotations, paraphrases, musings) and at least two
resources per resource type.

User 2 has a private bibliography. There is a user group which has two
members (Administrator and User 1) and which has a private group bibliography
(superBibliography).

Some maturity indices have been set and there are some popularity
ratings/number of views.

No attachments have been added to any resource to easy this setup.

No particular configuration is defined. 
