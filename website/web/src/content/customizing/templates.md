+++
title = "Templates"
date = 2021-01-30T00:08:41+01:00
weight = 5
+++


WIKINDX gives the template designer greater control over the visual
design than did previous templates. It uses a combination of CSS and the
Smarty HTML Templating System -- documentation on Smarty can be found at
<https://www.smarty.net/> but you will also find comments and help in the
various .tpl files in `wikindx/components/templates/default/`.

Certain files must be present for the template to be available to the
user in _Wikindx > Preferences_:

 * display.tpl
   * header.tpl (optional if merged with display.tpl)
   * menu.tpl (optional if merged with display.tpl)
   * content.tpl (optional if merged with menu.tpl)
     * content_abstract.tpl (optional if merged with content.tpl)
     * content_attachments.tpl (idem)
     * content_cite_fields.tpl (idem)
     * content_content.tpl (idem)
     * content_custom.tpl (idem)
     * content_file_list.tpl (idem)
     * content_heading_block.tpl (idem)
     * content_ideas.tpl (idem)
     * content_metadata.tpl (idem)
     * content_musings.tpl (idem)
     * content_note.tpl (idem)
     * content_paging.tpl (idem)
     * content_paraphrases.tpl (idem)
     * content_quotes.tpl (idem)
     * content_ressource.tpl (idem)
     * content_ressource_information.tpl (idem)
   * footer.tpl (optional if merged with display.tpl)
   * template.css (stylesheet of the template)
   * tinymce.css (stylesheet for inline html editor TinyMCE)
   * component.json (metadata description of the component)
   * README.txt (optional file that provides more info than component.json)
   * images/* (static images for css/HTML)
   * icons/* (images of icons inserted at runtime in HTML by the core --
     you must follow the same names as default theme)

To create a template, a good starting point is to copy the
`components/templates/default/` files to `components/templates/xxxx/` where xxxx
is a unique folder name for your template.

Edit component.json and change its fields accordingly. After that go to
the components admin panel for refreshing the cached component list.

If the component appears correctly in the panel without error message
you can start its customization.

The "images" folder must contain the images that you want to include
directly by a hardcoded link in a .tpl file. For example:

`{$tplPath}/images/wikindx-logo.png`, where `{$tplPath}` is the
resource location see by the browser to the root dir of the template.

The icons folder contains the images of icons (for buttons for example)
whose name is predefined by the core. You can replace the images with
your own icon set, partial or complete for example to have a larger
size.  If you do not provide an icon or even do not create the "icons"
folder, the missing icons will be taken into the "default" template.
The image formats accepted in this folder are png, jpg, svg.

>>>>>config.txt<<<<<

This comprises 1-2 optional lines. They control how the multi-level
drop-down menus in WIKINDX are displayed.  WIKINDX uses such menus to
efficiently display a large number of options in a limited screen space.
However, such menus can be tricky to use in some circumstances and, in
others such as mobile phone displays, cause more problems than they
solve. Thus, both users and template designers can opt to reduce the
number of menu levels. Users can edit their preferences while template
designers can edit lines 1 and 2 of config.txt.

Line 1:

This can be 0, 1, 2, 0$, 1$, or 2$ where

 - 0 indicates that all menu levels should be used.
 - 1 indicates that the number of levels should be reduced by 1.
 - 2 indicates that the number of levels should be reduced by 2.
 - $ indicates that number of menu levels is mandatory and the user has
     no choice --useful where the template is to be used in special
     circumstances such as on mobile devices. Without a '$', read-only
     users will initially be presented with the number of menu levels set by
     the template designer and this can be overridden in _Wikindx > Preferences_.

If there is only one line in config.txt or the value of the line 1
is wrong, then 0 is assumed for line 1.

Line 2:

This is only used if line 1 is either '1' or '1$'. When menu levels are reduced
by one, the elements of the sub-submenu thus removed appear below the
sub-submenu heading. Adding a string of text to this line will preface each of
these elements with that text. Example config.txt:

config.txt>>>>>
1
-->
<<<<<config.txt

This will display sub-submenu elements as:

 * Some Menu Item
 * Sub-submenu Title...
   --> Sub-submenu Element 1
   --> Sub-submenu Element 2
   --> Sub-submenu Element 3
 * Next Menu Item
 * Next Menu Item



Template files:

Only the general HTML structure and the HTML structure of the main
recordable content (resources, musings ...) can be modified. The other
pages inject their own code into the variables {$heading} and {$content}
in content.tpl and content_content.tpl.

Apart from tinymce which can be personalized using the tinymce.css file,
all the CSS rules for the project are contained in template.css which
gives you great control over the presentation, even on HTML structures
which are not in the templates.

Of course there are implicit rules or presentation choices that it is
not possible to question or circumvent without modifying the core even
if you are a CSS expert. This system was designed to give flexibility
without allowing a design change free of any constraints as could offer
a blog engine.

The most important variables are commented in the code of the .tpl
files.

For an in-depth understanding of the template system, read the PHP files
in the core / display folder, and core/startup/GLOBALS.php.

>>>>>display.tpl<<<<<

This is the first .tpl file to be loaded and it acts as a container for
other .tpl files. The core uses only this model. All the others are
pieces of this first for easy reading and maintenance.

The template's .css file is linked to in display.tpl. You have the option
to override any CSS in the template's own .css file. To do this, create
a components/templates/override.css file and add CSS there. Custom template
designers should add:
<link rel="stylesheet" href="{$tplPath}/../override.css" type="text/css">
in their display.tpl following the template.css link. CSS in override.css acts
as global CSS overwriting any CSS styling in any template.css.

>>>>>header.tpl<<<<<

Fistr level title, hidden in popup windows.


>>>>>menu.tpl<<<<<

Position the display of the drop-down menus.


>>>>>content.tpl<<<<<

This contains the main display logic for the body of the WIKINDX display
between menus and footer. WIKINDX expects all such content to be
displayed in an HTML table element with class 'mainTable'.


>>>>>footer.tpl<<<<<

Secondary information displayed at the bottom of the page.
