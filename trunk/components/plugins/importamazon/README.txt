********************************************************************************
**                                importamazon                                **
**                               WIKINDX module                               **
********************************************************************************


NB. this module is compatible with WIKINDX v5 and up.  Results may be unexpected if used with a lower version.

Import a resource from Amazon using Amazon's web services (only imports books).

You will need both an Amazon access key and a secret access key from:
https://affiliate-program.amazon.com/gp/flex/associates/apply-login.html

1. you will need to enter the access key into the variable $this->accessKey in plugins/importamazon/config.php
2. you will need to enter the secret access key into the variable $this->secretAccessKey in plugins/importamazon/config.php
3. you will need to enter the associate tag into the variable $this->associateTag in plugins/importamazon/config.php

Write access to the WIKINDX is required to use this.

The module registers itself in the 'Resource' menu.

Unzip this file (with any directory structure) into plugins/importamazon/.
Thus, plugins/importamazon/index.php etc.
	
Uses PHP code freely adapted from Wolfgang Plaschg's BibWiki:
http://wolfgang.plaschg.net/bibwiki/

********************************************************************************

CHANGELOG:

v1.12, 2020
1. Wikindx compatibility version 7.

v1.11, 2020
1. Add documentation.

v1.10, 2020
1. Relicencing under CC-BY-NC-SA 4.0 terms.

v1.9, 2019
1. Bug fix: resourceTitleSort field in the resource table was not completed.
2. Adaptation for Wikindx 5.9.1.

v1.8
1. The Product Advertising API End point is now configurable.
2. You can use an url of any Amazon website to import

v1.7
1. Plugin now compatible with WIKINDX v5.x

v1.6
1. Ensured that non-English characters and UTF-8 code in Amazon data is imported correctly.

v1.5
1. Plugin compatible only with WIKINDX v4.2.x

v1.4 ~ 25th January 2013
1. Correction to download packaging that stops the plugin working properly.

v1.3 ~ 22nd January 2013
1. Updated Amazon sign-up URL.
2. Creators not being written to database.

v1.2 ~ 24th February 2012
1. Updated for WIKINDX v4.

v1.1 ~ 4th November 2009
1. As per Amazon.com requirements, the module now requires and uses both sets of keys from Amazon Web Services.

--
Mark Grimshaw-Aagaard 2017.
