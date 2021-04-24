<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */
class ENDNOTE
{
    private $coremessages;
    private $session;
    private $importCommon;
    private $parentClass;
    private $vars;
    private $gatekeep;

    /**
     * Constructor
     *
     * @param mixed $parentClass
     */
    public function __construct($parentClass)
    {
        $this->parentClass = $parentClass;
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "modules", "import", "IMPORTCOMMON.php"]));
        $this->importCommon = new IMPORTCOMMON();
        $this->importCommon->importType = 'endnote';
        $this->vars = GLOBALS::getVars();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
    }
    /**
     * dislay options for importing
     *
     * @param mixed $message
     *
     * @return string
     */
    public function displayImport($message = FALSE)
    {
        $this->gatekeep->init();
        $this->importCommon->display($message);
    }
    /**
     * Display options for exporting
     *
     * @return string
     */
    public function displayExport()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "EXPORTCOMMON.php"]));
        $common = new EXPORTCOMMON();
        $sql = $common->getSQL();
        if (!$sql)
        {
            $this->parentClass->initEndnoteExport(HTML\p($this->coremessages->text("noList"), 'error'));

            return;
        }
        $pString = FORM\formHeader("importexportbib_exportEndnote");
        $pString .= FORM\hidden('method', 'process');
        $pString .= HTML\tableStart('left');
        $pString .= HTML\trStart();
        $checked = $this->session->getVar("exportMergeStored") ? 'CHECKED' : FALSE;
        $pString .= HTML\td($this->coremessages->text('misc', "mergeStored") . FORM\checkbox(FALSE, "mergeStored", $checked));
        if ($custom = $common->getCustomFields('endnote'))
        {
            $pString .= HTML\trEnd();
            $pString .= HTML\trStart();
            $pString .= HTML\td($custom);
        }
        // Disabled due to tabbed file bug above
        /*
        $types = array(1 => $this->coremessages->text("importexport", "exportEndnoteTabbed"),
                    2 => $this->coremessages->text("importexport", "exportEndnoteXml"));
        if ($selected = $this->session->getVar("exportEndnoteFileType"))
            $pString .= HTML\td(FORM\selectedBoxValue($this->coremessages->text("importexport", "exportEndnoteFileType"),
                "endnoteFileType", $types, $selected, 2));
        else
            $pString .= HTML\td(FORM\selectFBoxValue($this->coremessages->text("importexport", "exportEndnoteFileType"),
                "endnoteFileType", $types, 2));
        */
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();

        return $pString;
    }
}
