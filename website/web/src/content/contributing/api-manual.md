+++
title = "API Manual"
date = 2021-01-30T00:08:41+01:00
weight = 4
+++


The API Manual is a documentation of all PHP classes, functions, namespaces, etc provided by Wikindx. It is useful for core and component developers. 


## Setting the build environment

This API Manual is made with a PHP wrapper script `release/cli-make-api-manual.php` on top of [phpDocumentor 3.0.0](https://phpdoc.org/) static generator (fixed version). The theme is the default.

As phpDocumentor is written in PHP and is OS independent, a version is stored in SVN at `tools/phpDocumentor.phar`. It requires PHP 7.3 or 7.4. Others files `tools/phpDocumentor.phar.asc` and `tools/phpDocumentor.phar.pubkey` are needed by phpDocumentor to run.


## Generation and update

To learn how to write PHP documentation, read the [online documentation for phpDocumentor](https://docs.phpdoc.org/3.0/). 

To generate the API Manual, run this command from the cli when your are in the SVN root directory: 

~~~~sh
php release/cli-make-api-manual.php
~~~~

Respond to the question of the script. You must select a version to generate from the two choices:

- __trunk__: a version of the current code with a public folder set for the trunk (will give <https://wikindx.sourceforge.io/api-manual/trunk>).
- __X.Y.Z__: a version of the current code with a public folder set with the value of __WIKINDX_PUBLIC_VERSION__ taken from `trunk/core/startup/CONSTANTS.php` (will give <https://wikindx.sourceforge.io/api-manual/X.Y.Z>).

The code is generated in `website/api-manual/trunk` or `website/api-manual/X.Y.Z` folder.

For updating the API Manual, generate the code for each version and upload the content of each output folder inside the `/home/project-web/wikindx/htdocs/api-manual` folder of the SourceForge website FTP. Don't remove the folders of old versions.

Regenerating an older version is not supported. To do this, you have to extract an old revision from SVN because the wrapper script uses the code of the current Wikindx core and is not able to extract itself the code of the SVN, etc. It's just an ad hoc script that could change from version to version.

For the online website to be complete you also need to generate and upload the website for the same versions. See its own page.


## Configuration

The configuration file is `trunk/phpdoc.xml`. It is used to define the root folder of Wikindx sources and ignored source files. Basically, the sources of cli tools, third party libraries and components are ignored. Files and directories containing non PHP files are also ignored as possible.


## Version selector

The build script injects some JS and HTML code inside all pages of the manual to create a dynamic version selector displayed at the top. The PHP file `website/api-manual/version-switch.php`, stored at `https://wikindx.sourceforge.io/web/version-switch.php`, provides a live list in JSON format of all version of the API Manual installed on SF. The JS script included in the API Manual uses this list of versions to allow a visitor to switch the current version instantly, without rebuilding old versions.
