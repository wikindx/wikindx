+++
title = "Website"
date = 2021-01-30T00:08:41+01:00
weight = 5
+++


## Setting the build environment

This website is made with a PHP wrapper script `release/cli-make-web.php` on top of [Hugo](https://gohugo.io/) static generator (fixed version). The theme is derivated from [Grav Learn theme to Hugo v2.5.0](https://github.com/matcornic/hugo-theme-learn/releases/tag/2.5.0) for Hugo.

Use only the non extended version of [Hugo v0.80](https://github.com/gohugoio/hugo/releases/tag/v0.80.0) for your OS. Other versions have not been tested.

After downloading the binary from github, place it in the tools folder and name it __hugo.exe__ for Windows OS or __hugo__ for other OSes. Give 777 permissions so that the wrapper script can run Hugo.

~~~~sh
chmod 777 tools/hugo
~~~~

To test a copy of the website you must modify the __BASE_URL__ constant in the wrapper script to point to a private installation of this site that you have done. For example <http://wikindx.test/>. Be careful not to commit this change.


## Generation and update

The sources of the website are in the `website/web/src` folder. The `website/web/src/content` subfolder contains the markdown files of each page and the `website/web/src/static` subfolder contains some static files copied at the root of the output website. To learn how to write and organize files, read the [online documentation for the Grav learn theme](https://learn.netlify.app/en/). 

To generate the website code, run this command from the cli when your are in the SVN root directory: 

~~~~sh
php release/cli-make-web.php
~~~~

Respond to the question of the script. You must select a version to generate from the two choices:

- __trunk__: a version of the current code with a public folder set for the trunk (will give <https://wikindx.sourceforge.io/web/trunk>).
- __X.Y.Z__: a version of the current code with a public folder set with the value of __WIKINDX_PUBLIC_VERSION__ taken from `trunk/core/startup/CONSTANTS.php` (will give <https://wikindx.sourceforge.io/web/X.Y.Z>).

The code is generated in `website/web/trunk` or `website/web/X.Y.Z` folder.

For updating the website, generate the code for each version and upload the content of each output folder inside the `/home/project-web/wikindx/htdocs/web` folder of the SourceForge website FTP. Don't remove the folders of old versions.

Regenerating an older version is not supported. To do this, you have to extract an old revision from SVN because the wrapper script uses the code of the current WIKINDX core and is not able to extract itself the code of the SVN, etc. It's just an ad hoc script that could change from version to version.

For the online website to be complete you also need to generate and upload the API manual for the same versions. See its own page.


## Help Topics

Help Topics are special pages. WIKINDX code points to them. So if you want to rename or update them you have to make sure that the names and the content match with the links and behavior provided in the current application of the trunk code. See the `\UTILS\createHelpTopicLink()` function in `trunk/core/libs/UTILS.php`.


## Configuration and changes to the Grav Learn theme

The configuration file is `website/web/src/config.toml`. It follow the documentation of the theme and Hugo with some exceptions. The __pre__ attribute of the `[[menu.shortcuts]]` section have been hacked to set the id of a SVN icon bundled in a custom webfont build with the [icomoon.io](icomoon.io) tool. It's no longer HTML code displayed before an entry of the __more__ menu. The file `website/web/src/icomoon.io.wikindx.project.json` is a configuration for `website/web/src/themes/hugo-theme-learn/static/fonts/custom-font-awesome.svg`, and is built with the icomoon.io tool.

Others customization are extensive changes to layout and static files of the theme to optimise the loading time and reduce the size of the generated website. The left column is also entirely customised. 

The three files below are called by `cli-make-web.php` to bundle and minify CSS and JS scripts on the fly during the generation. Only their byproducts are included in the final website. The build script also minifies all HTML files after Hugo generation.

~~~~plain
website/web/src/themes/hugo-theme-learn/static/css/minified.css.php
website/web/src/themes/hugo-theme-learn/static/js/minified_header.js.php
website/web/src/themes/hugo-theme-learn/static/js/minified_footer.js.php
~~~~

All custom fonts have been removed and the data of the search engine are loaded only on the first query.

Chapter titles are displayed in the body.

Mermaid JS is not enabled because this JS file is huge but could be used later.


## Version selector

The last customization is a version selector displayed in the left column of the website. The PHP file `website/web/version-switch.php`, included in the final website, provides a live list in JSON format of all versions of the website installed on SF. A JS script included in the left column of each page uses this list of versions to allow a visitor to switch the current version instantly, without rebuilding old versions.
