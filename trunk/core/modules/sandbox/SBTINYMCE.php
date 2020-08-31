<?php
/**
 * loadBlankTextarea : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
 * RESOURCEFORM -- Enter or edit a new resource via a form
 */
class SBTINYMCE
{
    private $tinymce;

    public function __construct()
    {
        $this->tinymce = FACTORY_LOADTINYMCE5::getInstance();
    }
    
    /**
     * Start the process of entering a new resource by asking for the choice of resource type and number of authors
     *
     * @param false|string $error
     */
    public function init($error = FALSE)
    {
        $pString = \HTML\h("TinyMCE 5 Sandbox");
        $pString .= \FORM\formHeaderName('sandbox_SBTINYMCE_CORE', 'sbtinymce');
        
        $blankeditor = array_key_exists("blankeditor", $_POST) ? $_POST["blankeditor"] : "";
        $pString .= $this->tinymce->loadBlankTextarea("Blank editor", "blankeditor", $blankeditor);
        
        //$pString .= $this->tinymce->loadBasicTextinput(['resourceTitle', 'resourceSubtitle'], 400);
        //$pString .= $this->tinymce->loadMetadataTextarea(['resourcetextAbstract', 'resourcetextNote']);
        $pString .= \HTML\p(\FORM\formSubmit("Submit"));
        $pString .= \FORM\formEnd();

        GLOBALS::addTplVar('content', $pString);
        // Load at end because .js initialization needs various DIVs to be in the page before they are made invisible
        //\AJAX\loadJavascript(WIKINDX_BASE_URL . '/core/modules/resource/resourceForm.js?ver=' . WIKINDX_PUBLIC_VERSION);
    }
}
