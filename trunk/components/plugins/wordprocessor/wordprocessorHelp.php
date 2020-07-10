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
 * wordprocessor class -- help file
 */
class wordprocessorHelp
{
    /** array */
    public $text = [];
    
    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text['help'] = dgettext($domain, "
<h3>Word Processor</h3>
<p>The WYSISWYG Word Processor allows you to write your articles and papers entirely within WIKINDX and then export them to an external word processor such as OpenOffice or Word for final polishing if necessary.  When exporting, you can choose the citation style and any citations you have inserted will be formatted to that citation style with appended bibliography.  The Word Processor can handle footnote and endnote styles as well as in-text citation that is context sensitive.</p>
<ul>
<li>If you have existing papers from a WIKINDX v3.x installation, you must first import them before they can be used in this plugin. With a new WIKINDX v4 install, the conversion process from WIKINDX v3.8.2 will keep the record of papers in the v4 database but the actual papers will still be in the wikindx3/papers/ folder. You can manually copy them or use the Import Paper function.</li>
<li>Papers can later be backed up to external sources and imported back into the word processor using the Import Paper function.</li>
<li>If you save an existing paper with a new title, the default behaviour is that previous versions with the old title will be deleted unless you check the 'save new version' checkbox.</li>
<li>The title of a paper can only comprise uppercase/lowercase a-z letters, arabic numerals, spaces and underscores.</li>
<li>The wikindx4/plugins/wordprocessor/papers/ folder must be writeable by the web user.</li>
<li>If you have Image Magick installed on your server, the Export Paper function will attempt to convert images for use in a word processor. The Image Magick bin/ path can be set in the Word Processor's config.php file.</li>
<li>A horizontal line inserted in the editor will become a new section when exported (to Rich Text Format, for example). Some of the WIKINDX citation styles will format elements according to sections.</li>
<li><strong>Be careful</strong>: there is currently no autosave and no warning if you leave the editor without first saving.</li>
</ul>
		");
    }
}
