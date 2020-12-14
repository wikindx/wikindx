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
<li>Papers can be backed up to external sources and imported back into the word processor using the Import Paper function.</li>
<li>If you save an existing paper with a new title, the default behaviour is that previous versions with the old title will be deleted unless you check the 'save new version' checkbox.</li>
<li>A horizontal line inserted in the editor will become a new section when exported (to Rich Text Format, for example). Some of the WIKINDX citation styles will format elements according to sections.</li>
<li><strong>Be careful</strong>: there is currently no autosave and no warning if you leave the editor without first saving.</li>
</ul>
		");
    }
}
