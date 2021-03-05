+++
title = "Website"
date = 2021-01-30T00:08:41+01:00
weight = 2
chapter = false
#pre = "<b>1. </b>"
+++

## Setting the build environnement

This website is made with a PHP wrapper script `release/cli-make-web.php` on top of [Hugo](https://gohugo.io/) static generator. The theme is derivated from [Grav Learn theme to Hugo v2.5.0](https://github.com/matcornic/hugo-theme-learn/releases/tag/2.5.0) for Hugo.

Use only the non extended version of [Hugo v0.80](https://github.com/gohugoio/hugo/releases/tag/v0.80.0) for your OS. Other version have not been tested.

After downloading the binary from github, place it in the tools folder and name it __hugo.exe__ for Windows OS or __hugo__ for other OSes. Give 777 permissions so that the wrapper script can run Hugo.

~~~~sh
chmod 777 tools/hugo
~~~~

To test a copy of the website you must modify the __BASE_URL__ constant in the wrapper script to point to a private installation of this site that you have done. For example <http://wikindx.test/>. Be careful not to commit this change.


## Generating and updating the website

The sources of the website are in the `website/web/src` folder. The `website/web/src/content` subfolder contains the markdown files of each page and the `website/web/src/static` subfolder contains some static files copied at the root of the output website. To learn how to write and organize files, read the [online documentation for the Grav learn theme](https://learn.netlify.app/en/). 

To generate the website code, run this command from the cli when your are in the SVN root directory: 

~~~~sh
php release/cli-make-web.php
~~~~

Respond to the question. You must select a version to generate. You only have two choices:

- __trunk__: a version of the current code with a public folder set for the trunk (will give <https://wikindx.sourceforge.io/web/trunk>).
- __X.Y.Z__: a version of the current code with a public folder set with the value of __WIKINDX_PUBLIC_VERSION__ taken from `trunk/core/startup/CONSTANTS.php` (will give <https://wikindx.sourceforge.io/web/X.Y.Z>).

The website code is output in `website/web/trunk` or `website/web/X.Y.Z` folder.

For updating the website, generate the website for each version and upload the content of each output folder inside the `/home/project-web/wikindx/htdocs/web` folder of the SourceForge website FTP. Don't remove the folders of old Wikindx versions.

For the site to be complete you must also generate and upload the API manual for the same versions. 

Regenerating an older version of the website is not supported. To do this, you have to extract an old revision from SVN because the wrapper script use the code of the current Wikindx core, is not able to extract itself the code of the SVN, etc. It's just an adhoc script.

The API manual also need to be generated on each release. See its own page.

## Help Topics

Help Topics are special pages. Wikindx code points to them. So if you want to rename or update them you have to make sure that the names and the content match with the links and behavior provided in the current application of the trunk code. See the `\UTILS\createHelpTopicLink()` function in `trunk/core/libs/UTILS.php`.

## Configuration and changes to the Grav Learn theme

The configuration file is `website/web/src/config.toml`. It follow the documentation of the theme and Hugo with some exceptions. The __pre__ attribut of the `[[menu.shortcuts]]` section have been hacked to set the id of a SVN icon bundled in a custom webfont build with the [icomoon.io](icomoon.io) tool. It's not anymore some HTML code displayed before an entry of the __more__ menu. The file `website/web/src/icomoon.io.wikindx.project.json` is a configuration for `website/web/src/themes/hugo-theme-learn/static/fonts/custom-font-awesome.svg`, build with icomoon.io tool.

Others customization are extensive changes to layout and static files of the theme to optimise the loading time and reduce the size of the generated website. The left column is also entirely customised. 

The three files below are called by `cli-make-web.php` to bundle and minify CSS and JS scripts on the fly during the generation. Only their byproducts are included in the final website. The build script also minify all HTML files after Hugo generation.

~~~~plain
website/web/src/themes/hugo-theme-learn/static/css/minified.css.php
website/web/src/themes/hugo-theme-learn/static/js/minified_header.js.php
website/web/src/themes/hugo-theme-learn/static/js/minified_footer.js.php
~~~~

All custom fonts have been removed and the data of the search engine are loaded only on the first query.

Mermaid JS is not enabled because this JS file is huge but could be used later.

## Version selector

The last customization is a version selector displayed in the left column of the website. The PHP file `website/web/src/static/version-switch.php`, included in the final website, provides a live list in JSON format of all version of the website installed on SF. A JS script included in the left column of each page uses this list of version to allow a visitor to switch the current version instantly, without rebuilding old website versions.
