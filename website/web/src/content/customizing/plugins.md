+++
title = "Plugins"
date = 2021-01-30T00:08:41+01:00
weight = 5
+++

There are two types of plugin, menu plugins and in-line plugins. Menu
plugins are accessed via the WIKINDX menu system while in-line plugins
appear in the main body of the WIKINDX.

To get your plugins working, a few conditions are required:

 * All modules must go in a folder within the `wikindx/components/plugins/`
   directory.

 * This module folder must have a main PHP file called `index.php`, a
   `config.php` file and a `plugintype.txt` file -- you add further files
   as you like.

 * If the folder is called 'test', the class name in
   `wikindx/components/plugins/test/index.php` must be called test_MODULE.

 * `plugintype.txt` should have one line which comprises either of the
   case-sensitive words 'menu' or 'inline'.

 * `index.php` and `config.php` must be writeable by the web server
   user (this allows administrators to manage plugins via the WIKINDX
   interface rather than having to edit the files directly).

 * `config.php` should have the public variable `$wikindxVersion`.  e.g.  public
   `$wikindxVersion = 8;` From WIKINDX 5.8, compatible plugins are required to
   explicitly state their compatibility with the constant
   __WIKINDX_COMPONENTS_COMPATIBLE_VERSION["plugin"]__. This is a value that matches
   an internal core version that could be validated against more than one public version.
   In the plugin's `config.php`, set `$wikindxVersion` to match
   __WIKINDX_COMPONENTS_COMPATIBLE_VERSION["plugin"]__ in order to state compatibility.
   Incompatible plugins will not load. They will still be present
   and editable in the __Admin > Components__ interface.

The plugin directory may optionally have a file called `description.txt`
which may be used as a README providing instructions and credits. The
first line of this file must be the title of the plugin (and is used to
display available plugins when administering plugins in WIKINDX) but
the remaining lines can be whatever you wish.

If you have your message texts in a separate file, then users can opt to
change language localization as for other parts of WIKINDX.  In the example
given below, both the file and the class are called 'testMessages_en'.  The
'_xx' suffix of a new file and class for another language should follow the
name of a language folder in `wikindx/components/languages/`. New localization
 files should go into the same plugin folder as the `xxx_en` file.

For creating or upgrading the db structure of the objects used by your plugin,
create a folder `dbschema` (lowercase) in your plugin directory. In `dbschema`
create one directory for each database driver supported (lowercase) (currently
mysqli only). In each driver directory, create one directory "full" (lowercase)
and one directory "update" (lowercase).

Full and update directories may contain one or more SQL files to run on the first
execution of WIKINDX or on upgrade stage, in alphabetical order. If you do create
a database table that stores user IDs, then that field should be named
'pluginxxxxUserId' (when a user is deleted from the WIKINDX, the delete routines
check plugin database tables for fields that follow that convention).

To run the SQL code for a creation call the function
`createDbSchema(<plugindirname>)` of the UPDATEDATABASE class.

To run the SQL code for an upgrade call the function
`updateDbSchema(<plugindirname>, <versionnumberstring>)` of the UPDATEDATABASE class.


## Menu plugins

Menu plugins have one or more menu items inserted in the WIKINDX menu
specified in the plugin. These types of plugins are intended for cases
where the main display table of the WIKINDX is completely dedicated to
the plugin.

* The constructor of a menu plugin must appear as:

~~~~php
public function __construct($menuInit = FALSE)
{
    if ($menuInit)
    {
        // set $this->menus
        // set $this->authorize
        return;
    }
    // Do something else
}
~~~~

* The constructor must set `$this->menus`. This is a multi-dimensional
  public array where you define which menu(s) the module should insert
  itself into, what is the label for the menu item and which method
  within our class do we run when the menu option is selected.

* The constructor must set the public parameter `$this->authorize` as
  either 0 (public readonly access), 1 (login required) or 2 (admin only).
  This should come from your config.php file (see below).

* Both `$this-Menus` and `$this->authorize` should only be set when
  `$menuInit` is TRUE in which case the constructor should return after
  they have been set without doing anything else (`$menuInit` is set
  automatically to TRUE by the MENU class in `wikindx/core/navigation/MENU.php`).

You should also have a `config.php` file (see the example below) which
defines the access level for the plugin and in which menu(s) the plugin
is displayed. These values can be altered through the WIKINDX __Admin > Components__
interface and so you must have a public `$menus` array and a public
`$authorize` variable.

Unless using the basic `GLOBALS::buildOutputString()` method, you must
use the templating system that WIKINDX uses and return the template
string to the calling process. Furthermore, in the interests of
compatibility and future WIKINDX upgrades, you should use the WIKINDX
functions where possible.

Constructor parameters could be `$db` and `$vars` that are the database object (see
the SQL class in `wikindx/core/libs/SQL.php`) and an array of all input values from
the web browser form or querystring.

`$this->authorize` controls the display of the module item in the menu
system according to user permissions.

An example of a menu plugin is given below.


## In-line plugins

WIKINDX provides four in-line plugin containers -- these are inserted
as per the template design and so are dependent for positioning, and
indeed appearance, upon the template designer.

The four containers are:

 * inline1
 * inline2
 * inline3
 * inline4

An example of an in-line plugin is given below.

~~~~php
<?php
/*
**********************
*
* MENU plugin example
*
**********************
*/

/**
* EXAMPLE: wikindx/components/plugins/test/config.php
*/

class test_CONFIG
{
	public $menus = array('plugin1');
    /**
    * $authorize
    *
    * 0 (public readonly access)
    * 1 (login required)
    * 2 (admin only)
    */
	public $authorize = 1;
	public $wikindxVersion = 5.8;
}
~~~~

~~~~php
<?php
/*****
*	TEST plugin -- English messages.
*
* '###' is dynamic text to be inserted. e.g. $this->messages->text('helpMePlease', 'method') will replace '###' with 'method' in $this->text['helpMePlease'].
*
*****/

class testMessages
{
    /** array */
    public $text = [];

    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
/**
* Menu items
*/
            "testSub" => dgettext($domain, "Test plugin..."),
            "menu1" => dgettext($domain, "Test Command 1"),
            "menu2" => dgettext($domain, "Test Command 2"),
            "menuHelp" => dgettext($domain, "Test plugin Help"),
            
/**
* Other messages
*/
            "heading" => dgettext($domain, "Test Plugin Example"),
            "command1" => dgettext($domain, "This is command 1"),
            "command2" => dgettext($domain, "This is command 2"),
            "noMethod" => dgettext($domain, "No method was input. Try the WIKINDX menu instead"),
            "thisModule" => dgettext($domain, "this module"),
            "noHelp" => dgettext($domain, "No help here. Try ### instead."),
            "helpMePlease" => dgettext($domain, "The method is ### and here I am.  Sadly, I cannot help."),
        ];
    }
}
~~~~

~~~~php
<?php
/**
* EXAMPLE: wikindx/components/plugins/test/index.php
*/

class test_MODULE
{
    private $db;
    private $vars;
    private $html;
    private $messages;
    public $authorize;
    public $menus;

// constructor
	public function __construct($menuInit = FALSE)
	{
		include_once(__DIR__ . DIRECTORY_SEPARATOR . "config.php");
		$config = new test_CONFIG();
		include_once("core/messages/PLUGINMESSAGES.php");
// plugin folder name and generic message filename
		$this->messages = new PLUGINMESSAGES('test', 'testMessages');
		$this->session = FACTORY_SESSION::getInstance();
		$this->authorize = $config->authorize;
		if($menuInit)
		{
			$this->makeMenu($config->menus);
			return; // Need do nothing more as this is simply menu initialisation.
		}

		$authorize = FACTORY_AUTHORIZE::getInstance();
		if(!$authorize->isPluginExecutionAuthorised($this->authorize)) // not authorised
			FACTORY_CLOSENOMENU::getInstance(); // die

		$this->db = FACTORY_DB::getInstance();
		$this->vars = GLOBALS::getVars();
	}
/**
* Make the menu items
*
* $this->menus must exist and be public and can be in two forms:
* 1. a simple menu array without hierarchy under one of the available WIKINDX menu headings
* or
* 2. a menu of one submenu or more.
*
* Top level menu labels are: "wikindx", "res", "search", "text", "admin", "plugin1", "plugin2", "plugin3".
* "admin" is only available when logged in as admin, "text" will only show if there are metadata (quotes etc.)
* and the three 'pluginX' menu trees only show if they are populated.
* Typically, you will insert a submenu into one of the pluginX menus.
*
* The command to be executed when the user selects that option is the value of each member of the sub array and must be a public method (see below).
* In order to see the menu command when building and testing your menus, you must have declared the command's public method.
* Only in the case of a submenu (see $menu2 below) must the first element of the submenu tree have a FALSE value.
*/
	private function makeMenu($menuArray)
	{
// This first example shows 3 commands appearing simply in two menus -- solely for demonstration purposes
		$menus1 = array(
			$menuArray[1] => // top level menu label
				array(
				$this->messages->text('menu1') 		=>	"command1", // menu item label and command
				$this->messages->text('menu2')		=>	"command2"
				 ),
			$menuArray[0] 	=> // top level menu label
				array($this->messages->text('menuHelp')	=>	"helpMe"),
		);
// This second example shows 3 commands appearing under one menu as a submenu -- the example uses this configuration
// 'xxxpluginSub' must be a unique name otherwise you risk overwriting other plugins
		$menus2 = array(
			$menuArray[0] =>
				array('testpluginSub' => array(
				$this->messages->text('testSub') => FALSE,
				$this->messages->text('menu1')		=>	"command1",
				$this->messages->text('menu2')		=>	"command2",
				$this->messages->text('menuHelp')	=>	"helpMe"
				),
			),
		);
		$this->menus = $menus1;
	}
/**
* The name of this public function must be the same as one of the array values in a sub array of $this->menu
*/
	public function command1()
	{
		GLOBALS::setTplVar('heading', $this->messages->text('heading'));
		$pString = \HTML\p($this->messages->text('command1'));
		if(array_key_exists('method', $this->vars))
			$pString .= $this->{$this->vars['method']}();
		else
			$pString .= $this->messages->text('noMethod');
		GLOBALS::setTplVar('content', $pString);
	}
/**
* The name of this public function must be the same as one of the array values in a sub array of $this->menu
*/
	public function command2()
	{
		GLOBALS::setTplVar('heading', $this->messages->text('heading'));
		GLOBALS::setTplVar('content', $pString);
	}
/**
* The name of this public function must be the same as one of the array values in a sub array of $this->menu
*/
	public function helpMe()
	{
		GLOBALS::setTplVar('heading', $this->messages->text('heading'));
		$link = \HTML\a("link", $this->messages->text('thisModule'),
			htmlentities("index.php?action=test_command1&method=helpMePlease"));
		$pString = $this->messages->text('noHelp', $link);
		GLOBALS::setTplVar('content', $pString);
	}
/**
* A private method not referenced in the menus
*/
	private function helpMePlease()
	{
		return $this->messages->text('helpMePlease', \HTML\em($this->vars['method']));
	}
}
~~~~

~~~~php
<?php
/*
**********************
*
* IN-LINE plugin example
*
**********************
*/

/*
* Example of an in-line plugin.  This inserts the phrase 'Hello sailor!' wherever the template
* designer has placed the 'inline1' container. In the default template that comes with WIKINDX,
* 'inline1' appears in content.tpl in the paging display and will print 'Hello sailor!' in bold in front
* of paging links when displaying lists.
*/
class inline1_MODULE
{
// constructor
	public function __construct()
	{
		GLOBALS::setTplVar('inline1', \HTML\strong('Hello sailor!'));
	}
}
~~~~
