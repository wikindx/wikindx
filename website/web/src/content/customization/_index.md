+++
title = "Customization"
date = 2021-01-30T00:08:41+01:00
weight = 5
chapter = true
#pre = "<b>1. </b>"
+++

## Developping components

[TODO]

In addition to creating new bibliographic styles, and languages,
the visual display of WIKINDX (the template) can also be changed.
Furthermore, PHP developers can write plug-in modules for various
database-related tasks. For all matters relating to customization,
please read the documentation found in docs/. Please consider contacting
the WIKINDX team through the sourceforge.net site in order to have your
contribution made available for download.




### component.json format

Each component must have a component.json in [JSON] format that
describes the main information about itself. The file must be structured
as a single object which corresponds to a PHP array once deserialized
with json_decode(). Each key/value pair of the object is an array
key/value entry. The format is under development and may change further.

Description of fields:

Field                 Type    O Description
--------------------- ------- - ------------------------------------------------------------------
component_type        string  Y Name of a type of component. Must be: style, template, or vendor.
component_id          string  Y Component identifier. Must be identical to the name of the root component directory.
component_version     string  Y Component public version number: this number is completely free.
component_builtin     bool    Y If true, the component will be pre-installed/released with the core. Thie field is independent of component_updatable.
component_updatable   bool    Y If true, the component will be released in a separate package that can be updated via the component admin panel.
component_name        string  Y Component short name.
component_description string  Y Component short description (without line break). Use the README.txt file for long description, notes...
component_licence     string  N Component license name or abbreviation (abbreviation recommended).
component_website     string  N A single full URL to the vendor or maintainer website to use as a link.
component_sha256      string  Y Unique signature corresponding to a sha256 hash of the component files. Use cli-sign-components.php to compute it.
component_authors     array   N An array of objects unserialized as a PHP array of PHP arrays. Each object is an author or contributor.
author_name           string  Y Name of the author.
author_role           string  Y Role of the author. Something like: developper, packager, translator, tester, maintainer.
author_copyright      string  Y Contribution period. E.g. the years: 2004-2017.
author_email          string  Y A single email address of the author that could be embedded in a href with mailto.
author_website        string  Y A single full URL to the author website/personnal page to use as a link.


Column O indicates a mandatory field: Y=Yes, N=No.

Any additional fields will be ignored.

For the exact syntax of each data type, consult the official format
specifications [JSON].

When WIKINDX starts or the components admin panel is opened a
components.json file is created in the cache folder, and a
components.json file is created in the data folder.

The file in cache folder, listing all components installed, is an array
of object in [JSON] format. Each object is the content of a
component.json file with additional fields. Original component.json
fields remain inchanged.

Field                 Type    O Description
--------------------- ------- - ------------------------------------------------------------------
component_integrity   integer Y Error code returned when the component is checked. 0 is OK. Not 0 is an error.

The file in data folder, listing persistent data of some components, is
an array of object in [JSON] format. Each object is a short component
description (component_type + component_id) like a component.json file
with additionnal persistent fields. This list can contain data about
components that are no longer installed but that we want to keep for a
future reinstallation. Original component.json fields remain inchanged.

Field                 Type    O Description
--------------------- ------- - ------------------------------------------------------------------
component_status      string  Y Code status value: enabled or disabled. "enabled", if the component is executed on WIKINDX startup.

When the release script release/make.php script is executed it create
also a components.json, listing all components released with the current
core.

As the previous file, it is an array of object in [JSON] format. Each
object is the content of a component.json file with additional fields,
but different. Original component.json fields remain inchanged.

Field                 Type    O Description
--------------------- ------- - ------------------------------------------------------------------
component_packages    array   N Array of object. Each object is the description of a downloadable package of the component.
package_location      string  Y A single full URL to a downloadable package in zip, tar.gz, or tar.bz2 format.
package_sha256        string  Y Unique signature corresponding to a sha256 hash of the package generated by make.php.
package_size          integer Y Size in bytes of the package.


Example of a single component.json file for the smarty vendor component:

~~~~
{
    "component_type": "vendor",
    "component_id": "smarty",
    "component_version": "3.1.34-dev-7",
    "component_builtin": "true",
    "component_updatable": "true",
    "component_name": "PHP Template Engine Smarty",
    "component_description": "Smarty is a template engine for PHP, facilitating the separation of presentation from application logic.",
    "component_licence": "LGPL 3.0",
    "component_website": "https://www.smarty.net/",
    "component_authors": [
        {
            "author_name": "New Digital Group, Inc.",
            "author_role": "Developpers",
            "author_copyright": "2002-2019"
        },
        {
            "author_name": "Mark Grimshaw-Aagaard",
            "author_role": "Packager",
            "author_copyright": "2012-2016",
            "author_email": "sirfragalot@users.sourceforge.net",
            "author_website": "https://vbn.aau.dk/en/persons/126217"
        },
        {
            "author_name": "Stéphane Aulery",
            "author_role": "Packager",
            "author_copyright": "2017-2019",
            "author_email": "lkppo@users.sourceforge.net",
            "author_website": "http://saulery.legtux.org/"
        }
    ],
    "component_sha256": "e4865976c11067e720c45d60769777045b4be94b5ff356a00c81079238ed21d6"
}
~~~~


Example of a data/components.json file:

~~~~
[
    {
        "component_type": "style",
        "component_id": "apa",
        "component_status": "enabled"
    },
    {
        "component_type": "template",
        "component_id": "default",
        "component_status": "enabled"
    },
    {
        "component_type": "vendor",
        "component_id": "jquery",
        "component_status": "enabled"
    }
]
~~~~


Example of a cache/components.json file extract:

~~~~
[
    {
        "component_type": "plugin",
        "component_id": "chooselanguage",
        "component_version": "181",
        "component_builtin": "false",
        "component_updatable": "true",
        "component_name": "Choose Language",
        "component_description": "Display a drop-down menu on all pages to choose the language of the interface. This is useful if your website is hosted on       the web and on public access.",
        "component_licence": "ISC License",
        "component_authors": [
            {
                "author_name": "Stéphane Aulery",
                "author_role": "Translator",
                "author_copyright": "2017-2019",
                "author_email": "lkppo@users.sourceforge.net",
                "author_website": "http://saulery.legtux.org/"
            }
        ],
        "component_sha256": "fff4092a9c602efe4e11bb2d68d1ca2afe6a6e7a40f4881d16b10ba11a12e474",
        "component_integrity": 0
    },
    {
        "component_type": "style",
        "component_id": "apa",
        "component_version": "53",
        "component_builtin": "true",
        "component_updatable": "true",
        "component_name": "APA",
        "component_description": "American Psychological Association (APA) (installed by default). APA is an author/date based style. This means emphasis is placed on the author and the date of a piece of work to uniquely identify it.",
        "component_licence": "ISC License",
        "component_website": "https://apastyle.apa.org",
        "component_authors": [
            {
                "author_name": "Mark Grimshaw-Aagaard",
                "author_role": "Compiler",
                "author_copyright": "2005",
                "author_email": "sirfragalot@users.sourceforge.net",
                "author_website": "https://vbn.aau.dk/en/persons/126217"
            }
        ],
        "component_sha256": "af1ba8aa15c62d0ae9a2ce9dbe598d9001fab81451a7b10cf511d5ba26b6d25d",
        "component_integrity": 0
    }
]
~~~~


Example of a component.json file extract from the update server
generated by the make.php script:

~~~~
[
    {
        "component_version": "181",
        "component_licence": "ISC License",
        "component_description": "Display a drop-down menu on all pages to choose the language of the interface. This is useful if your website is hosted on       the web and on public access.",
        "component_name": "Choose Language",
        "component_sha256": "dc79b8c5e4f303fa242345df5ee734598972c56406191d14e946d81b0ec2bde7",
        "component_id": "chooselanguage",
        "component_builtin": "false",
        "component_type": "plugin",
        "component_updatable": "true",
        "component_authors": [
            {
                "author_name": "Stéphane Aulery",
                "author_role": "Translator",
                "author_copyright": "2017-2019",
                "author_email": "lkppo@users.sourceforge.net",
                "author_website": "http://saulery.legtux.org/"
            }
        ],
        "component_packages": [
            {
                "package_location": "https://sourceforge.net/projects/wikindx/files/archives/5.9.1/components/wikindx_5.9.1_plugin_chooselanguage.zip",
                "package_sha256": "afb5fad76f2c5ce810653988792462352bab0dfe17f8f6065bbefc2f10f983b5",
                "package_size": 47372
            }
        ]
    },
    {
        "component_version": "53",
        "component_sha256": "5b8c8f37c8f46f67ff6d80ae2b1c6901c6c82b599c34ca387b2b31fd27433fb0",
        "component_licence": "ISC License",
        "component_name": "APA",
        "component_description": "American Psychological Association (APA) (installed by default). APA is an author/date based style. This means emphasis      is placed on the author and the date of a piece of work to uniquely identify it.",
        "component_id": "apa",
        "component_builtin": "false",
        "component_type": "style",
        "component_updatable": "true",
        "component_authors": [
            {
                "author_name": "Mark Grimshaw-Aagaard",
                "author_role": "Compiler",
                "author_copyright": "2005",
                "author_email": "sirfragalot@users.sourceforge.net",
                "author_website": "https://vbn.aau.dk/en/persons/126217"
            }
        ],
        "component_packages": [
            {
                "package_location": "https://sourceforge.net/projects/wikindx/files/archives/5.9.1/components/wikindx_5.9.1_style_apa.zip",
                "package_sha256": "f86dde75e51816c8c25c2e6ef34b21a462a5b23273978f5692546e6bf9f8c8d6",
                "package_size": 32241
            }
        ]
    }
]
~~~~