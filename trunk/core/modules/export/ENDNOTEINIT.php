<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */
class ENDNOTEINIT
{
    private $messages;
    private $session;
    private $vars;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->vars = GLOBALS::getVars();
    }
    /**
     * initEndnoteExportB
     */
    public function initEndnoteExportB()
    {
        $this->session->setVar("exportBasket", TRUE);
        $this->initEndnoteExport();
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * initEndnoteExportL
     */
    public function initEndnoteExportL()
    {
        $this->session->delVar("exportBasket", TRUE);
        $this->initEndnoteExport();
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * initEndnoteExport
     *
     */
    public function initEndnoteExport()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "endnoteExport"));
        $pString = $this->displayExport();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display options for exporting
     *
     * @return string
     */
    private function displayExport()
    {
        $common = FACTORY_EXPORTCOMMON::getInstance('endnote');
        $sql = $common->getSQL();
        if (!$sql)
        {
            return HTML\p($this->messages->text("importexport", "noList"), 'error');
        }
        $pString = FORM\formHeader("export_ENDNOTEEXPORT_CORE");
        $pString .= FORM\hidden('method', 'process');
        $pString .= HTML\tableStart('left');
        $pString .= HTML\trStart();
        $checked = $this->session->getVar("exportMergeStored") ? 'CHECKED' : FALSE;
        $pString .= HTML\td($this->messages->text('misc', "mergeStored") . FORM\checkbox(FALSE, "mergeStored", $checked));
        if ($custom = $common->getCustomFields('endnote'))
        {
            $pString .= HTML\trEnd();
            $pString .= HTML\trStart();
            $pString .= HTML\td($custom);
        }
        // Disabled due to tabbed file bug above
        /*
        $types = array(1 => $this->messages->text("importexport", "exportEndnoteTabbed"),
                    2 => $this->coremessages->text("importexport", "exportEndnoteXml"));
        if ($selected = $this->session->getVar("exportEndnoteFileType"))
            $pString .= HTML\td(FORM\selectedBoxValue($this->messages->text("importexport", "exportEndnoteFileType"),
                "endnoteFileType", $types, $selected, 2));
        else
            $pString .= HTML\td(FORM\selectFBoxValue($this->messages->text("importexport", "exportEndnoteFileType"),
                "endnoteFileType", $types, 2));
        */
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p(FORM\formSubmit($this->messages->text("submit", "Submit")));
        $pString .= FORM\formEnd();

        return $pString;
    }
}
