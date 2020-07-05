                         --o TEST DATABASE o--

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

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

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

-- 
Mark Grimshaw-Aagaard
The WIKINDX Team 2020
sirfragalot@users.sourceforge.net
