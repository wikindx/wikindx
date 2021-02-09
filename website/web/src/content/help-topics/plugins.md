+++
title = "Components"
date = 2021-02-09T21:30:41+01:00
disableToc = true
+++

Components are extras that are not part of the core WIKINDX download and can be plugins, templates, languages, or bibliographic styles. Plugins extend the functionality of WIKINDX beyond its core purpose and can be one of two types:  in-line plugins, where the output of the plugin is displayed in the body of WIKINDX; or menu plugins, where the plugins are accessed via the menus.

Some plugins might not be compatible with this version of WIKINDX, and so they will not be visible to users, because $wikindxVersion in the plugin's config.php is not equal to WIKINDX_COMPONENTS_COMPATIBLE_VERSION["plugin"]. Incompatible plugins will be still be listed in the 'Enabled plugins' select box. Update these plugins in order to use them. **If you manually update wikindxVersion in a plugin's config.php, the plugin is not guaranteed to work and, depending on the plugin, might corrupt your WIKINDX database.**

When checking the update status of plugins, styles, templates, and languages, only those that are enabled will be queried. Two update checks occur:

1. The timestamps of plugins, styles, templates, and languages on the remote server are compared to the timestamps on this WIKINDX
1. Each enabled plugin on this WIKINDX has its $wikindxVersion compared to that on the remote server.

Additionally, the remote server is queried for any new files. If updates are found or new files are available, an appropriate link is supplied (an Internet connection is required).

As an administrator, you can accomplish some management of components via this interface including:

* **Disable plugins** (and templates, styles and languages):  This does not delete the plugin, it merely temporarily disables it until you re-enable it.
* **Position plugins**:  You can reposition plugins in different menu hierarchies.
* **Authorize**: Block types of users from access to the plugins.

Positioning plugins and granting authorization is accomplished by editing the plugin's config.php file (typically only $menus and $authorize need be edited) -- be sure you know what you are doing:

* **$menus**: should be an array of at least one of the following menu elements:
  - wikindx
  - res
  - search
  - text
  - admin
  - plugin1
  - plugin2
  - plugin3
* **$admin**: is only available when logged in as admin, 'metadata' will only show if there are metadata (quotes etc.), and the three **pluginX** menu trees only show if they are populated.
* **$authorize**: should be one of the following:
  - unknown (always unauthorised, menu item not displayed)
  - 0 (menu item displayed for all users, logged in or not)
  - 1 (menu item displayed for users logged in with write access)
  - 2 (menu item displayed only for logged-in admins)

Usually, you will insert a submenu into one of the pluginX menus. As a reference, a typical config.php file will look like this:

```php

    class adminstyle_CONFIG {
        public $menus = array('plugin1');
        public $authorize = 2;
    	public $wikindxVersion = 5.8;
    }
    
```


Inline plugins return output that is displayed in one of four containers that can be positioned anywhere in any of the template .tpl files.  To change the position of a container, you will need to edit the appropriate .tpl file.

At least one template, one bibliographic style and one language must remain enabled.
