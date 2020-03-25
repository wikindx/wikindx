<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 *	RTF export class
 */
class RTF
{
    private $pluginmessages;
    private $session;
    private $coremessages;
    private $indentTabs;
    private $dividerCR;
    private $cr;
    private $cite;
    private $styles;
    private $parentClass;

    /**
     * Constructor
     *
     * @param mixed $parentClass
     */
    public function __construct($parentClass = FALSE)
    {
        $this->parentClass = $parentClass;
        include_once("core/messages/PLUGINMESSAGES.php");
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        $this->session = FACTORY_SESSION::getInstance();
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->cite = FACTORY_CITE::getInstance('rtf');
        $this->styles = LOADSTYLE\loadDir();
        $this->pString = '';
        $this->fontSizes = [
            1 => 8, 2 => 10, 3 => 12, 4 => 14, 5 => 16, 6 => 18, 7 => 20, 8 => 22,
        ];
        $this->fonts = [
            1 => "Arial",
            2 => "Courier",
            3 => "Georgia",
            4 => "Helvetica",
            5 => "MS Sans Serif",
            6 => "MS Serif",
            7 => "Palatino",
            8 => "Tahoma",
            9 => "Trebuchet MS",
            10 => "Times New Roman",
            11 => "Verdana",
        ];
        $this->ubi = [
            1 => "Normal",
            2 => "Italics",
            3 => "Bold",
            4 => "Underline",
        ];
        $this->ubiBib = [
            1 => "Normal",
            2 => "Bold",
        ];
        $this->indentTabs = [1 => 0, 2 => 1, 3 => 2, 4 => 3];
        $this->cr = [1 => 1, 2 => 2, 3 => 3, 4 => 4];
        $this->dividerCR = [1 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 4];
        // Perform some system admin
        FILE\tidyFiles();
    }
    /*
     * Display RTF export and format options
     *
     * @param string
     */
    public function display()
    {
// Check we have a list to export
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "EXPORTCOMMON.php");
        $common = new EXPORTCOMMON();
        $sql = $common->getSQL();
        if (!$sql)
        {
            return HTML\p($this->pluginmessages->text("noList"), 'error');
        }
        // load the session
        $sessionVars = $this->session->getArray("exportRtf");
        $pString = HTML\p($this->pluginmessages->text('introRtfExport'));
        $pString .= FORM\formHeader("importexportbib_exportRtf");
        $pString .= FORM\hidden('method', 'process');
        if ($this->session->issetVar("exportRtf_bibliography"))
        {
            $checked = $this->session->getVar("exportRtf_bibliography") ? 'CHECKED' : FALSE;
        }
        else
        {
            $checked = $this->session->issetVar("exportRtf_done") ? FALSE : 'CHECKED';
        }
        $pString .= $this->makeForm($checked, "bibliography", "bibliography");
        $pString .= BR;
        if ($this->session->issetVar("exportRtf_abstract"))
        {
            $checked = $this->session->getVar("exportRtf_abstract") ? 'CHECKED' : FALSE;
        }
        else
        {
            $checked = $this->session->issetVar("exportRtf_done") ? FALSE : 'CHECKED';
        }
        $pString .= $this->makeForm($checked, "abstract", "abstract");
        $pString .= BR;
        if ($this->session->issetVar("exportRtf_notes"))
        {
            $checked = $this->session->getVar("exportRtf_notes") ? 'CHECKED' : FALSE;
        }
        else
        {
            $checked = $this->session->issetVar("exportRtf_done") ? FALSE : 'CHECKED';
        }
        $pString .= $this->makeForm($checked, "notes", "notes");
        $pString .= BR;
        $pString .= HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= HTML\trStart();
        $selected = $this->session->getVar("exportRtf_metadata");
        $metadata = [0 => $this->pluginmessages->text("metadataAll"),
            1 => $this->pluginmessages->text("metadataUser"), ];
        if ($selected)
        {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("metadata"),
                "exportRtf_metadata",
                $metadata,
                $selected,
                2
            ));
        }
        else
        {
            $pString .= HTML\td(FORM\selectFBoxValue(
                $this->pluginmessages->text("metadata"),
                "exportRtf_metadata",
                $metadata,
                2
            ));
        }
        $selected = $this->session->getVar("exportRtf_metadataFullCite") ? TRUE : FALSE;
        $pString .= HTML\td(FORM\checkbox(
            $this->pluginmessages->text("metadataFullCite"),
            "exportRtf_metadataFullCite",
            $selected
        ));
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR;
        if ($this->session->issetVar("exportRtf_quotes"))
        {
            $checked = $this->session->getVar("exportRtf_quotes") ? 'CHECKED' : FALSE;
        }
        else
        {
            $checked = $this->session->issetVar("exportRtf_done") ? FALSE : 'CHECKED';
        }
        $pString .= $this->makeForm($checked, "quotes", "quotes", TRUE);
        $pString .= BR;
        if ($this->session->issetVar("exportRtf_paraphrases"))
        {
            $checked = $this->session->getVar("exportRtf_paraphrases") ? 'CHECKED' : FALSE;
        }
        else
        {
            $checked = $this->session->issetVar("exportRtf_done") ? FALSE : 'CHECKED';
        }
        $pString .= $this->makeForm($checked, "paraphrases", "paraphrases", TRUE);
        $pString .= BR;
        if ($this->session->issetVar("exportRtf_musings"))
        {
            $checked = $this->session->getVar("exportRtf_musings") ? 'CHECKED' : FALSE;
        }
        else
        {
            $checked = $this->session->issetVar("exportRtf_done") ? FALSE : 'CHECKED';
        }
        $pString .= $this->makeForm($checked, "musings", "musings");
        $pString .= BR;
        // Entry divider
        $pString .= HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= HTML\trStart();
        if (!$this->session->getVar("exportRtf_done"))
        {
            $selected = '______________________________________________________________';
        }
        else
        {
            $selected = trim($this->session->getVar("exportRtf_divider"));
        }
        $pString .= HTML\td(FORM\textInput(
            $this->pluginmessages->text("divider"),
            "exportRtf_divider",
            $selected,
            75
        ));
        // Divider Font
        if (!$selected = $this->session->getVar("exportRtf_dividerFont"))
        {
            $selected = 3; // Georgia
        }
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text("font"),
            "exportRtf_dividerFont",
            $this->fonts,
            $selected,
            3
        ));
        // Divider Font Size
        if (!$selected = $this->session->getVar("exportRtf_dividerFontSize"))
        {
            $selected = 2; // 10
        }
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text("fontSize"),
            "exportRtf_dividerFontSize",
            $this->fontSizes,
            $selected,
            3
        ));
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= HTML\trStart();
        // Carriage returns after each entry
        if (!$selected = $this->session->getVar("exportRtf_dividerCR"))
        {
            $selected = 4;
        }
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text("dividerCR"),
            "exportRtf_dividerCR",
            $this->dividerCR,
            $selected,
            3
        ));
        // preferences
        if ($selected = $this->session->getVar("exportRtf_style"))
        {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->coremessages->text("config", "style"),
                "exportRtf_style",
                $this->styles,
                $this->session->getVar("exportRtf_style"),
                4
            ));
        }
        else
        {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->coremessages->text("config", "style"),
                "exportRtf_style",
                $this->styles,
                $this->session->getVar("setup_Style"),
                4
            ));
        }
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR;
        if (count($sessionVars) > 1)
        { // by default, array has a single 'style' key (see above)
            $this->session->clearArray("exportRtf");
            $this->session->setVar('exportRtf_style', $this->session->getVar("setup_Style"));
            $reset =
                FORM\formHeader("importexportbib_initRtfExport") .
                FORM\formSubmit($this->coremessages->text("submit", "Reset")) .
                FORM\formEnd();
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Proceed")) . FORM\formEnd() . $reset);
        }
        else
        {
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Proceed")) .
            BR . BR . FORM\formReset($this->coremessages->text('submit', 'Reset')) . FORM\formEnd());
        }

        return $pString;
    }
    /*
     * make a form subset for setup()
     *
     * @param mixed $checked
     * @param mixed $session
     * @param string $text
     * @param mixed $comments
     *
     * @param string
     */
    public function makeForm($checked, $session, $text, $comments = FALSE)
    {
        $pString = HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= HTML\trStart();
        $temp = HTML\strong($this->pluginmessages->text($text)) . "&nbsp;" .
            FORM\checkbox(FALSE, "exportRtf_" . $text, $checked);
        if ($session != 'bibliography')
        {
            if (!$this->session->getVar("exportRtf_done"))
            {
                $selected = $this->pluginmessages->text($session . 'Tag');
            }
            else
            {
                $selected = trim($this->session->getVar("exportRtf_" . $session . "Tag"));
            }
            $temp .= BR . FORM\textInput(
                $this->pluginmessages->text("tag"),
                "exportRtf_" . $session . "Tag",
                $selected,
                15
            );
        }
        $pString .= HTML\td($temp);
        if ($comments)
        {
            if ($this->session->issetVar("exportRtf_" . $session . "Comments"))
            {
                $checkedC = $this->session->getVar("exportRtf_" . $session . "Comments") ? 'CHECKED' : FALSE;
            }
            else
            {
                $checkedC = $this->session->issetVar("exportRtf_done") ? FALSE : 'CHECKED';
            }
            $temp = $this->pluginmessages->text("comments") . "&nbsp;" .
                FORM\checkbox(FALSE, "exportRtf_" . $text . "Comments", $checkedC);
            if (!$selected = trim($this->session->getVar("exportRtf_" . $session . "CommentsTag")))
            {
                $selected = $this->pluginmessages->text($session . 'CommentTag');
            }
            $temp .= BR . FORM\textInput(
                $this->pluginmessages->text("tag"),
                "exportRtf_" . $session . "CommentsTag",
                $selected,
                15
            );
            $pString .= HTML\td($temp);
        }
        // Font
        if (!$selected = $this->session->getVar("exportRtf_" . $session . "Font"))
        {
            $selected = 3; // Georgia
        }
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text("font"),
            "exportRtf_" . $session . "Font",
            $this->fonts,
            $selected,
            3
        ));
        // Font Size
        if (!$selected = $this->session->getVar("exportRtf_" . $session . "FontSize"))
        {
            if ($session == 'bibliography')
            {
                $selected = 1; // 8
            }
            else
            {
                $selected = 2; // 10
            }
        }
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text("fontSize"),
            "exportRtf_" . $session . "FontSize",
            $this->fontSizes,
            $selected,
            3
        ));
        // Underline, Italics, Bold, Normal
        if ($session != 'bibliography')
        {
            if ($selected = $this->session->getVar("exportRtf_" . $session . "TextFormat"))
            {
                $pString .= HTML\td(FORM\selectedBoxValue(
                    $this->pluginmessages->text("textFormat"),
                    "exportRtf_" . $session . "TextFormat",
                    $this->ubi,
                    $selected,
                    3
                ));
            }
            else
            {
                $pString .= HTML\td(FORM\selectFBoxValue(
                    $this->pluginmessages->text("textFormat"),
                    "exportRtf_" . $session . "TextFormat",
                    $this->ubi,
                    3
                ));
            }
        }
        else
        {
            if ($selected = $this->session->getVar("exportRtf_bibliographyTextFormat"))
            {
                $pString .= HTML\td(FORM\selectedBoxValue(
                    $this->pluginmessages->text("textFormat"),
                    "exportRtf_bibliographyTextFormat",
                    $this->ubiBib,
                    $selected,
                    2
                ));
            }
            else
            {
                $pString .= HTML\td(FORM\selectFBoxValue(
                    $this->pluginmessages->text("textFormat"),
                    "exportRtf_bibliographyTextFormat",
                    $this->ubiBib,
                    2
                ));
            }
        }
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= HTML\trStart();
        // Left Indent
        if (!$selected = $this->session->getVar("exportRtf_" . $session . "IndentL"))
        {
            if ($session != 'bibliography')
            {
                $selected = 2; // 1
            }
            else
            {
                $selected = 1; // 0
            }
        }
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text("indentL"),
            "exportRtf_" . $session . "IndentL",
            $this->indentTabs,
            $selected,
            3
        ));
        // Right Indent
        if (!$selected = $this->session->getVar("exportRtf_" . $session . "IndentR"))
        {
            if (($session == 'abstract') || ($session == 'notes'))
            {
                $selected = 2; // 1
            }
            elseif ($session != 'bibliography')
            {
                $selected = 1; // 2
            }
            else
            {
                $selected = 1; // 0
            }
        }
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text("indentR"),
            "exportRtf_" . $session . "IndentR",
            $this->indentTabs,
            $selected,
            3
        ));
        if (($session == 'musings') || ($session == 'quotes') || ($session == 'paraphrases'))
        {
            // Carriage Returns between parts of element
            if (!$selected = $this->session->getVar("exportRtf_" . $session . "CrBetween"))
            {
                $selected = 2; // 1
            }
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("crBetween"),
                "exportRtf_" . $session . "CrBetween",
                $this->cr,
                $selected,
                3
            ));
        }
        // Carriage Returns after element
        if (!$selected = $this->session->getVar("exportRtf_" . $session . "CrFollowing"))
        {
            $selected = 2; // 1
        }
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text("crFollowing"),
            "exportRtf_" . $session . "CrFollowing",
            $this->cr,
            $selected,
            3
        ));
        // ISBN and Keywords and hyperlink
        if ($session == 'bibliography')
        {
            $checkedC = $this->session->issetVar("exportRtf_bibliographyIsbn") ? 'CHECKED' : FALSE;
            $pString .= HTML\td($this->pluginmessages->text("isbn") . "&nbsp;" .
                FORM\checkbox(FALSE, "exportRtf_bibliographyIsbn", $checkedC));
            $checkedC = $this->session->issetVar("exportRtf_bibliographyKeywords") ? 'CHECKED' : FALSE;
            $pString .= HTML\td($this->pluginmessages->text("keywords") . "&nbsp;" .
                FORM\checkbox(FALSE, "exportRtf_bibliographyKeywords", $checkedC));
            $checkedC = $this->session->issetVar("exportRtf_link") ? 'CHECKED' : FALSE;
            $pString .= HTML\td($this->pluginmessages->text("exportHyperlink") . "&nbsp;" .
                FORM\checkbox(FALSE, "exportRtf_link", $checkedC));
        }
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();

        return $pString;
    }
}
