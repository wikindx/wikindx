<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * ABOUT class
 *
 * About page for WIKINDX
 */
class ABOUT
{
    public function __construct()
    {
        GLOBALS::setTplVar('heading', 'About WIKINDX');
    }
    /**
     * Print the about page
     */
    public function init()
    {
        $linkSmarty = \HTML\a("link", "Smarty", "https://www.smarty.net/", "_blank");
        $linkSfWikindx = \HTML\a(
            "link",
            "WIKINDX Sourceforge Project",
            "https://sourceforge.net/projects/wikindx/",
            "_blank"
        );
        $linkSf = \HTML\a("link", "Sourceforge", "https://sourceforge.net", "_blank");
        $gnuLink = \HTML\a(
            "link",
            "Creative Commons Attribution-Non Commercial-ShareAlike 4.0 License",
            "https://creativecommons.org/licenses/by-nc-sa/4.0/",
            "_blank"
        );
        $tinyMceLink = \HTML\a("link", "TinyMCE", "https://www.tiny.cloud", "_blank");
        $pString = \HTML\p('The ' . \HTML\strong('WIKINDX Virtual Research Environment') .
            ' is brought to you by the following: ');
        $pString .= \HTML\p(\HTML\strong('Mark Grimshaw-Aagaard:') . ' [ADMINSTRATOR/LEAD PROGRAMMER] (UK/New Zealand/Denmark)');
        $pString .= \HTML\p('Co-programmers:');
        $list = \HTML\li(\HTML\strong('St&eacute;phane Aulery:') . ' (France) ~ ' . \HTML\em('Senior programmer'));
        $pString .= \HTML\ul($list);
        $pString .= \HTML\p('Beta-testers and de-buggers:');
        $list = \HTML\li(\HTML\strong('Joachim Trinkwitz:') . ' (University of Bonn, Germany)');
        $list .= \HTML\li(\HTML\strong('Mathis Bicker:') . ' (University of Bonn, Germany)');
        $list .= \HTML\li(\HTML\strong('Allen Wilkinson:') . ' (NASA Glen Research Center, USA)');
        $list .= \HTML\li(\HTML\strong('Phil Abel:') . ' (NASA Glen Research Center, USA)');
        $pString .= \HTML\ul($list);
        $pString .= \HTML\p('Contributors to earlier versions include:');
        $list = \HTML\li('H&eacute;lio Alvarenga Nunes');
        $list .= \HTML\li('Amaury de la Pinsonnais');
        $list .= \HTML\li('Geoffrey Rowland');
        $list .= \HTML\li('Fabrizio Tallarita');
        $list .= \HTML\li('Notis Toufexis');
        $list .= \HTML\li('Pierre Nault');
        $list .= \HTML\li('Stephan Matthiesen');
        $list .= \HTML\li('Andreas G. Nie');
        $list .= \HTML\li('Jess Collicott');
        $list .= \HTML\li('Mark Tsikanovski');
        $list .= \HTML\li('John Weare');
        $list .= \HTML\li('Tim Richter');
        $list .= \HTML\li('Pascal Kockaert');
        $list .= \HTML\li('Frank Goergen');
        $list .= \HTML\li('Brian Koontz');
        $list .= \HTML\li('Benoit Beraud');
        $list .= \HTML\li('Simon C&ocirct&eacute-Lapointe');
        $list .= HTML\li('Jean-Saul Gendron');
        $pString .= \HTML\ul($list);
        $pString .= \HTML\p("WIKINDX makes use of the $linkSmarty HTML templating system and the $tinyMceLink WYSIWYG editor.");
        $pString .= \HTML\p("All updates, bug reports, forums, news etc. can be found at the $linkSfWikindx");
        $pString .= \HTML\p("Thanks to $linkSf for hosting the project.");
        $pString .= \HTML\hr();
        $pString .= \HTML\p("This program is free software; you can redistribute it and/or modify it
			under the terms of the Creative Commons Attribution-Non Commercial-ShareAlike 2.0 License.");
        $pString .= \HTML\p("If you do redistribute or modify the program, you must retain the copyright
		information and WIKINDX contact details as found in each file.");
        $pString .= \HTML\p("This program is distributed in the hope that it will be useful, but WITHOUT
		ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
		See the $gnuLink for more details.");
        $pString .= \HTML\p("The WIKINDX Team ~ Copyright (C) " . WIKINDX_COPYRIGHT_YEAR, FALSE, 'right');
        GLOBALS::addTplVar('content', $pString);
    }
}
