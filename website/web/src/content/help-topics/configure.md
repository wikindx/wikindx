+++
title = "Configure WIKINDX"
date = 2021-02-09T21:30:41+01:00
disableToc = true
+++

Most of the configuration options are self-explanatory but bear the following in mind:

* If you add the special string $QUICKSEARCH$ to the front page description, it will be replaced by the Quick Search form.
* To disable registered users from pasting BibTeX entries, set the value to 0.  Administrators can always paste.
* In cases where WIKINDX creates temporary files, such as when exporting bibliographies in various formats, you can define the age of a file in seconds after which the file will be deleted the next time a user logs on.
* If set, statistics will be emailed at the start of each month to registered users who are named creators of resources.
* You can deny read only access.  If read only access is allowed, the login prompt can be bypassed and users will go directly into the WIKINDX.
* Printing PHP errors and SQL statements is for debugging purposes and should not be used on a live production server. Printing SQL statements will interfere with AJAX/javascript operations on pages such as Advanced Search and New/Edit Resource.
* Prior to v6.4.0, data relating to searches and similar were stored in PHP sessions but these are common to all tabs/windows – searches in different tabs would make use of search data (such as search parameters, last multi search etc.) from the most recently conducted search in whatever tab. v6.4.0 makes use of javascript sessionStorage which allows for browser tabs/windows to be uniquely identified allowing search data to be unique to that search. Not all browsers support sessionStorage (a list of compatible browsers can be found here: [_sessionStorage: Browser compatibility_ in developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/API/Window/sessionStorage#Browser_compatibility) or [_sessionStorage_ in caniuse.com](https://caniuse.com/?search=sessionStorage)) so, for this reason, the feature is disabled by default in WIKINDX. Turn it on with the checkbox under Miscellaneous labelled 'BrowserTabID'.

Some of the settings here, such as no. resources to display per page or the bibliographic style, are defaults that users can override in My Wikindx.

You can add system users from the Admin menu.

When adding or editing resources, each resource can belong to multiple categories and subcategories, be assigned custom fields or defined as belonging to a language -- admins can add new categories, subcategories, custom fields and languages from the Admin menu.

**Because user sessions are created only once on login, changes to the configuration will not be registered until a user (logs out and) logs in.**

## LDAP authentication

With LDAP enabled and configured, user authentication bypasses the usual WIKINDX logon protocols and is redirected to a LDAP server instead.

The meaning of each option is given by the tooltips. In addition this help explains how LDAP authentication is done so that you choose the best configuration according to your domain controller.

The LDAP connection test function runs exactly the same code as during user connection. The debugging trace is very comprehensive but the level of detail depends on the loquacity of your server.

The code should work with all LDAP servers. However, it has only been tested with Active Directory and ApacheDS servers. In particular, your user login (or FullName / email) attribute may not be in the proposed list even if the most frequently used are there. You can request it as a feature from the developers.

Wikindx is not able to renew LDAP password on expiration or bulk import users from a group or OU. It only checks for authentication.

When LDAP authentication is enabled, native authentication is not used except for an attempt to connect the Super Admin. If its LDAP authentication fails, the built-in authentication is used instead.

When LDAP authentication is successful, the user is created in Wikindx if it did not already exist. The Username (login), Fullname and Email attributes are acquired from the directory server.

Passwords are not stored in Wikindx database when performing an LDAP authentication. If the LDAP server is offline or any error during the communication with the server occurs, the LDAP authentication will fail.

If the Super Admin subsequently disables LDAP authentication, users created in this way will not be able to log in with builtin authentication without changing their password or asking Super Admin to register one.

The user MUST enter his login WITHOUT domain information. When you have specified the domain name and the 'user' or 'binduser'  binding methods (connection to the server) are set, the domain name will be added automatically to the login according to the configuration.

First, the binding is configured with the chosen protocol. If the binding is encrypted, the certificates will be ignored. Referrals are always ignored. The network connection timeout is set to 10 s. The LDAP search timeout is set to 15 s. The server is contacted and if the connection is successful then WIKINDX searches for the user and verifies its password.

The user is found in five steps:

* The **DN** of all user-type objects `(SAM_NORMAL_USER_ACCOUNT: sAMAccountType = 805306368)` whose login attribute corresponds to the input, which are in one of the configured OUs, is extracted in a first list.
* The **DN** of all member objects of group-type objects configured `(SAM_GROUP_OBJECT: sAMAccountType = 268435456 or SAM_NON_SECURITY_GROUP_OBJECT: sAMAccountType = 268435457)` is extracted in a second list.
  - If the search by **OU** has been configured but not the search by group, the **DNs** of the first list are kept.
  - If the search by group has been configured but not the search by **OU**, the **DNs** in the second list are kept.
  - If both searches are configured and **AND** operator is set, the entries common to both lists are kept.
  - If both searches are configured and **OR** operator is set, the entries of both lists are kept.
  - If no search has been configured, authentication fails.
* The remaining entries are filtered to retain only the user objects whose login is the one entered.
* Finally the password of each remaining **DN** is verified. The first one with a matching password will be authenticated.

A user should not have more than one DN but in the unlikely event that he has two with the same password only the first of the DNs in alphabetical order (regardless of locale) will be authenticated.,
