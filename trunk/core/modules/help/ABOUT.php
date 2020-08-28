<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
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
        $licenseLink = \HTML\a(
            "link",
            "ISC License",
            "https://www.isc.org/licenses/",
            "_blank"
        );
        $tinyMceLink = \HTML\a("link", "TinyMCE", "https://www.tiny.cloud", "_blank");
        $pString = \HTML\p('The ' . \HTML\strong('WIKINDX Virtual Research Environment') .
            ' is brought to you by the following: ');
        $pString .= \HTML\p(\HTML\strong('Mark Grimshaw-Aagaard:') . ' [ADMINSTRATOR/LEAD PROGRAMMER] (UK/New Zealand/Denmark)');
        $pString .= \HTML\p('Co-programmers:');
        $list = \HTML\li(\HTML\strong('St&eacute;phane Aulery:') . ' (France) ~ ' . \HTML\em('Senior programmer'));
        $list .= \HTML\li(\HTML\strong('Dimitri Joukoff:') . ' (Australia)');
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
        $pString .= \HTML\p("This program is Free Software distributed under the terms of the $licenseLink.");
        $pString .= \HTML\p("Copyright &copy; 2003-" . date("Y") . " WIKINDX Team.");
        GLOBALS::addTplVar('content', $pString);
    }
}
