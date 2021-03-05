+++
title = "Customizing"
date = 2021-01-30T00:08:41+01:00
weight = 4
chapter = true
+++

# Customizing

Wikindx can be customized through components of four types: plugin, style, template, vendor.


Disclaimer : _currently the interface between the components and the core is not mature and we have not set up an infrastructure that would allow a developer to distribute an official component without also being an active developer of the core. The interface is quite unstable from one version to another, however you can develop a component for your private use if you are not put off by the code changes with each version._

Each type of component has its own structure described on its page. The only common point is a component.json file describing the component and a unique component identifier by type which must also be the name of the folder that contains the component. 

To prevent access problems between different operating systems and file systems, the component identifier must respect the following constraints:

- ASCII characters allowed: a to z, 0 to 9, - (hyphen), or _ (underscore).
- All lowercase.
- The `<id>` folder name of `install/folder/components/<type>/<id>` = `component_id` field of the its component.json file.

A component of type __plugin__ is a set of PHP code files that add new functionality.
It must respect an interface with the core code.

A component of type __style__ is an XML file defining bibliographic style rules.

A component of type __template__ is a collection of Smarty templates, images, CSS and Javascript that customizes the general appearance and major pages.

A component of type __vendor__ is reserved for kernel developers.
It is used to integrate third-party libraries such as Smarty.
You shouldn't have to create components of this type.
