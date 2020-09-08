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
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));

include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));

$script = '<script src="' . WIKINDX_URL_BASE . '/core/tiny_mce/tiny_mce_popup.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
$script .= '<script src="' . WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_PLUGINS . '/wordprocessor/wikindxWPcommon.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
$script .= '<script src="' . WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_PLUGINS . '/wordprocessor/' . basename(__DIR__) . '/js/wikindxWPExportdialog.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
GLOBALS::addTplVar('scripts', $script);

$class = new WPExportDialog();

class WPExportDialog
{
    private $coremessages;
    private $pluginmessages;
    private $errors;
    private $session;
    private $vars;
    private $dirFilesName;
    private $common;

    public function __construct()
    {
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->pluginmessages = new PLUGINMESSAGES('wordprocessor', 'wordprocessorMessages');
        
        $this->session = FACTORY_SESSION::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->dirFilesName = WIKINDX_DIR_DATA_FILES;
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] = 'save')) {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "WPCOMMON.php"]));
            $this->common = new WPCOMMON();
            $this->common->save();
            $this->wpExport();
            FACTORY_CLOSEPOPUP::getInstance();
        } else {
            $this->exportDialogue();
        }
    }
    /**
     * Export the file
     */
    private function wpExport()
    {
        $this->wpExportWriteSession();
        $hashFileName = $this->session->getVar("wp_HashFilename");
        $title = base64_decode($this->session->getVar("wp_Title"));
        // Write to files/ folder
        $fullFileName = $this->dirFilesName . DIRECTORY_SEPARATOR . $hashFileName . '.rtf';
        if ($fp = fopen("$fullFileName", "w")) {
            $text = $this->formatText();

            if (!fwrite($fp, $text)) {
                $this->common->failure($this->errors->text("file", "write", ": $hashFileName"), $title);
            }

            fclose($fp);
        } else {
            $this->common->failure($this->errors->text("file", "write", ": $hashFileName"), $title);
        }

        // Write exported hashed filename to session
        if ($sessVar = $this->session->getVar("wp_PaperExports")) {
            $sessArray = $sessVar;
        } else {
            $sessArray = [];
        }
        if (array_search($hashFileName, $sessArray) === FALSE) {
            $sessArray[$hashFileName] = $title . '.rtf';
            $this->session->setVar("wp_PaperExports", $sessArray);
        }
        // Perform some system admin
        FILE\tidyFiles();
        // Create download link and return it
        $titleUrl = rawurlencode($title . '.rtf');
        $exportLink = HTML\a("link", $title . '.rtf', "index.php?action=wordprocessor_wpExportDownload" .
            htmlentities("&hash=$hashFileName&filename=$titleUrl&extension=rtf"), "_new");
        $exportLink = str_replace('"', "'", $exportLink);
        echo "<script type=\"text/javascript\">var fileCell=window.opener.document.getElementById('wpExportFile');fileCell.innerHTML=\"$exportLink\";window.close();</script>";
    }
    /**
     * Format WP text as required
     *
     * @return string
     */
    private function formatText()
    {
        $text = stripslashes(trim($this->vars['hdnpaperText']));
        if ($this->session->getVar("wp_ExportFormat") == 'rtf') {
            $rtf = FACTORY_RICHTEXTFORMAT::getInstance();
            $output = $rtf->header();
            $text = $rtf->parse($text);
            if ($rtf->fontBlock) {
                $output .= $rtf->fontBlock;
            }
            if ($rtf->colourTable) {
                $output .= $rtf->colourTable;
            }
            $output .= $rtf->listTable;
            //			$output .= $this->styleSheet;
            // Set default font and size and black colour and left justification
            $output .= "{\\f0\\fs24\n\\s1\\cf1\\ql\\li1\\fi1" . $rtf->lineSpacing . "" . LF;
            $output .= ltrim($text);
            $output .= "\n\\par\n}";
            $output .= $rtf->footer();
        }

        return $output;
    }
    /**
     * Save wpExport session
     *
     * @return bool
     */
    private function wpExportWriteSession()
    {
        if (!array_key_exists('exportFormat', $this->vars)) {
            return FALSE;
        } else {
            $this->session->setVar("wp_ExportFormat", $this->vars['exportFormat']);
        }
        if ($this->vars['exportFormat'] == 'noExport') {
            return FALSE;
        }
        if (array_key_exists('exportStyle', $this->vars)) {
            $this->session->setVar("wp_ExportStyle", $this->vars['exportStyle']);
        }
        if (array_key_exists('exportPaperSize', $this->vars)) {
            $this->session->setVar("wp_ExportPaperSize", $this->vars['exportPaperSize']);
        } else {
            $this->session->setVar("wp_ExportPaperSize", 'A4');
        }
        if (array_key_exists('exportPaperSpace', $this->vars)) {
            $this->session->setVar("wp_ExportPaperSpace", $this->vars['exportPaperSpace']);
        } else {
            $this->session->setVar("wp_ExportPaperSpace", 'singleSpace');
        }
        if (array_key_exists('exportSectionFtRestart', $this->vars)) {
            $this->session->setVar("wp_ExportSectionFtRestart", $this->vars['exportSectionFtRestart']);
        } else {
            $this->session->setVar("wp_ExportSectionFtRestart", FALSE); // default no restart
        }
        if (array_key_exists('exportSpaceBib', $this->vars)) {
            $this->session->setVar("wp_ExportSpaceBib", $this->vars['exportSpaceBib']);
        } else {
            $this->session->setVar("wp_ExportSpaceBib", 'singleSpace');
        }
        if (array_key_exists('exportIndentBib', $this->vars)) {
            $this->session->setVar("wp_ExportIindentBib", $this->vars['exportIndentBib']);
        } else {
            $this->session->setVar("wp_ExportIndentBib", 'none');
        }
        if (array_key_exists('exportSpaceFt', $this->vars)) {
            $this->session->setVar("wp_ExportSpaceFt", $this->vars['exportSpaceFt']);
        } else {
            $this->session->setVar("wp_ExportSpaceFt", 'singleSpace');
        }
        if (array_key_exists('exportIndentFt', $this->vars)) {
            $this->session->setVar("wp_ExportIndentFt", $this->vars['exportIndentFt']);
        } else {
            $this->session->setVar("wp_ExportIndentFt", 'none');
        }
        if (array_key_exists('exportFontSizeFt', $this->vars)) {
            $this->session->setVar("wp_ExportFontSizeFt", $this->vars['exportFontSizeFt']);
        } else {
            $this->session->setVar("wp_ExportFontSizeFt", 1);
        }
        if (array_key_exists('exportPageNumber', $this->vars)) {
            $this->session->setVar("wp_ExportPageNumber", $this->vars['exportPageNumber']);
        } else {
            $this->session->setVar("wp_ExportPageNumber", 1);
        }
        if (array_key_exists('exportPageNumberAlign', $this->vars)) {
            $this->session->setVar("wp_ExportPageNumberAlign", $this->vars['exportPageNumberAlign']);
        } else {
            $this->session->setVar("wp_ExportPageNumberAlign", 0);
        }
        if (array_key_exists('exportIndentQuoteWords', $this->vars)) {
            $this->session->setVar("wp_ExportIndentQuoteWords", $this->vars['exportIndentQuoteWords']);
        } else {
            $this->session->delVar("wp_ExportIndentQuoteWords");
        }
        if (array_key_exists('exportSpaceIndentQ', $this->vars)) {
            $this->session->setVar("wp_ExportSpaceIndentQ", $this->vars['exportSpaceIndentQ']);
        } else {
            $this->session->setVar("wp_ExportSpaceIndentQ", 'singleSpace');
        }
        if (array_key_exists('exportIndentQuoteFontSize', $this->vars)) {
            $this->session->setVar("wp_ExportIndentQuoteFontSize", $this->vars['exportIndentQuoteFontSize']);
        } else {
            $this->session->setVar("wp_ExportIndentQuoteFontSize", 1);
        }
        if (array_key_exists('exportIndentQuoteMarks', $this->vars)) {
            $this->session->setVar("wp_ExportIndentQuoteMarks", $this->vars['exportIndentQuoteMarks']);
        } else {
            $this->session->delVar("wp_ExportIndentQuoteMarks");
        }
    }

    /**
     * Export paper dialogue
     */
    private function exportDialogue()
    {
        $savedMessage = "<span class=\\'success\\'>" . $this->pluginmessages->text("savedStatus") . "</span>";
        $notSavedMessage = "<span class=\\'error\\'>" . $this->pluginmessages->text("notSavedStatus") . "</span>";
        $pString = HTML\h($this->pluginmessages->text("headingExportPaper"), FALSE, 3);
        $js = "onsubmit=\"return wordprocessorExport('$savedMessage', '$notSavedMessage');\"";
        $pString .= FORM\formHeaderVisibleAction("dialog.php", "wpExport", $js);
        $pString .= FORM\hidden("method", "save");
        if ($hashFilename = $this->session->getVar("wp_HashFilename")) {
            $pString .= FORM\hidden("hashFilename", $hashFilename);
        }
        if ($id = $this->session->getVar("wp_Id")) {
            $pString .= FORM\hidden("id", $id);
        }
        $pString .= "<input type=\"hidden\" id=\"hdnpaperText\" name=\"hdnpaperText\" value=\"\">";
        if ($this->session->getVar("wp_Title")) {
            $title = base64_decode($this->session->getVar("wp_Title"));
        } else {
            $title = '';
        }
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text("paperTitle"), "title", $title, 40) . BR .
            $this->pluginmessages->text("saveAsNewVersion") . ':&nbsp;&nbsp;' . FORM\checkbox(FALSE, 'saveAsNewVersion'));
        $pString .= $this->exportOptions();
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Save"), 'insert'));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
    /**
     * Print paper export options
     *
     * @return string
     */
    private function exportOptions()
    {
        $formats = [
            'rtf' => $this->pluginmessages->text("exportRtf"),
        ];
        $pString = HTML\hr();
        $pString .= HTML\tableStart('borderStyleSolid');
        $pString .= HTML\trStart();
        if ($format = $this->session->getVar("wp_ExportFormat")) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("export"),
                "exportFormat",
                $formats,
                $format,
                1
            ));
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("export"),
                "exportFormat",
                $formats,
                'rtf',
                1
            ));
        }
        // Paper size
        $sizes = [
            'letter' => "Letter",
            'A4' => "A4",
            'A5' => "A5",
            'legal' => 'Legal',
            'executive' => 'Executive',
        ];
        if ($size = $this->session->getVar("wp_ExportPaperSize")) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("paperSize"),
                "exportPaperSize",
                $sizes,
                $size,
                3
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("paperSize"),
                "exportPaperSize",
                $sizes,
                'A4',
                3
            ), 'bottom');
        }
        // Line spacing for paper body
        $spaces = [
            'singleSpace' => $this->pluginmessages->text("singleSpace"),
            'oneHalfSpace' => $this->pluginmessages->text("oneHalfSpace"),
            'doubleSpace' => $this->pluginmessages->text("doubleSpace"),
        ];
        $fontSizeArray = [8 => 8, 10 => 10, 12 => 12, 14 => 14, 18 => 18, 24 => 24, 36 => 36];
        if ($space = $this->session->getVar("wp_ExportPaperSpace")) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("lineSpacePaper"),
                "exportPaperSpace",
                $spaces,
                $space,
                3
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("lineSpacePaper"),
                "exportPaperSpace",
                $spaces,
                'singleSpace',
                3
            ), 'bottom');
        }
        // page numbering
        $pns = [
            1 => $this->pluginmessages->text("pageNumberFooter"),
            2 => $this->pluginmessages->text("pageNumberHeader"),
            0 => $this->pluginmessages->text("pageNumberNone"),
        ];
        if (($pn = $this->session->getVar("wp_ExportPageNumber")) !== FALSE) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("pageNumber"),
                "exportPageNumber",
                $pns,
                $pn,
                3
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("pageNumber"),
                "exportPageNumber",
                $pns,
                1,
                3
            ), 'bottom');
        }
        // page numbering alignment
        $pns = [
            0 => $this->pluginmessages->text("pageNumberAlignCentre"),
            1 => $this->pluginmessages->text("pageNumberAlignLeft"),
            2 => $this->pluginmessages->text("pageNumberAlignRight"),
        ];
        if (($pn = $this->session->getVar("wp_ExportPageNumberAlign")) !== FALSE) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("pageNumberAlign"),
                "exportPageNumberAlign",
                $pns,
                $pn,
                3
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("pageNumberAlign"),
                "exportPageNumberAlign",
                $pns,
                0,
                3
            ), 'bottom');
        }
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\tdEnd() . HTML\trEnd() . HTML\trStart() . HTML\tdStart();
        $pString .= BR;
        $pString .= HTML\tableStart('borderStyleSolid');
        $pString .= HTML\trStart();
        // bibliographic/citation export style
        $styles = LOADSTYLE\loadDir();
        if ($style = $this->session->getVar("wp_ExportStyle")) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->coremessages->text("config", "style"),
                "exportStyle",
                $styles,
                $style,
                4
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->coremessages->text("config", "style"),
                "exportStyle",
                $styles,
                GLOBALS::getUserVar("Style"),
                4
            ), 'bottom');
        }
        // Line spacing for appended bibliography
        if ($space = $this->session->getVar("wp_ExportSpaceBib")) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("lineSpaceBib"),
                "exportSpaceBib",
                $spaces,
                $space,
                3
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("lineSpaceBib"),
                "exportSpaceBib",
                $spaces,
                'singleSpace',
                3
            ), 'bottom');
        }
        // Indentation for appended bibliography
        $indents = [
            'indentNone' => $this->pluginmessages->text("indentNone"),
            'indentAll' => $this->pluginmessages->text("indentAll"),
            'indentFL' => $this->pluginmessages->text("indentFL"),
            'indentNotFL' => $this->pluginmessages->text("indentNotFL"),
        ];
        if ($indent = $this->session->getVar("wp_ExportIndentBib")) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("indentBib"),
                "exportIndentBib",
                $indents,
                $indent,
                4
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("indentBib"),
                "exportIndentBib",
                $indents,
                'indentNone',
                4
            ), 'bottom');
        }
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\tdEnd() . HTML\trEnd() . HTML\trStart() . HTML\tdStart();
        $pString .= BR;
        $pString .= HTML\tableStart('borderStyleSolid');
        $pString .= HTML\trStart();
        // Line spacing for footnotes
        if ($space = $this->session->getVar("wp_ExportSpaceFt")) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("lineSpaceFt"),
                "exportSpaceFt",
                $spaces,
                $space,
                3
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("lineSpaceFt"),
                "exportSpaceFt",
                $spaces,
                'singleSpace',
                3
            ), 'bottom');
        }
        // Indentation for footnotes
        $indents = [
            'indentNone' => $this->pluginmessages->text("indentNone"),
            'indentAll' => $this->pluginmessages->text("indentAll"),
            'indentFL' => $this->pluginmessages->text("indentFL"),
            'indentNotFL' => $this->pluginmessages->text("indentNotFL"),
        ];
        if ($indent = $this->session->getVar("wp_ExportIndentFt")) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("indentFt"),
                "exportIndentFt",
                $indents,
                $indent,
                4
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("indentFt"),
                "exportIndentFt",
                $indents,
                'indentNone',
                4
            ), 'bottom');
        }
        // footnote font size
        if (($fontSize = $this->session->getVar("wp_ExportFontSizeFt")) !== FALSE) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("fontSizeFt"),
                "exportFontSizeFt",
                $fontSizeArray,
                $fontSize,
                3
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("fontSizeFt"),
                "exportFontSizeFt",
                $fontSizeArray,
                8,
                3
            ), 'bottom'); // default 8pt
        }
        $sectionFtRestart = $this->session->getVar("wp_ExportSectionFtRestart") ? TRUE : FALSE;
        $pString .= HTML\td($this->pluginmessages->text("sectionFtRestart") . ":&nbsp;&nbsp;" .
            FORM\checkbox(FALSE, "exportSectionFtRestart", $sectionFtRestart));
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\tdEnd() . HTML\trEnd() . HTML\trStart() . HTML\tdStart();
        $pString .= BR;
        $pString .= HTML\tableStart('borderStyleSolid');
        $pString .= HTML\trStart();
        // Indent large quotations
        $indentQuoteWords = $this->session->getVar("wp_ExportIndentQuoteWords");
        $pString .= HTML\td(FORM\textInput(
            $this->pluginmessages->text("indentQuoteWords"),
            "exportIndentQuoteWords",
            $indentQuoteWords,
            4
        ));
        // Line spacing for indented quotations
        if ($space = $this->session->getVar("wp_ExportSpaceIndentQ")) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("lineSpaceIndentQ"),
                "exportSpaceIndentQ",
                $spaces,
                $space,
                3
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("lineSpaceIndentQ"),
                "exportSpaceIndentQ",
                $spaces,
                'singleSpace',
                3
            ), 'bottom');
        }
        if (($fontSize = $this->session->getVar("wp_ExportIndentQuoteFontSize")) !== FALSE) {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("indentQuoteFontSize"),
                "exportIndentQuoteFontSize",
                $fontSizeArray,
                $fontSize,
                3
            ), 'bottom');
        } else {
            $pString .= HTML\td(FORM\selectedBoxValue(
                $this->pluginmessages->text("indentQuoteFontSize"),
                "exportIndentQuoteFontSize",
                $fontSizeArray,
                8,
                3
            ), 'bottom'); // default 8pt
        }
        $indentQuoteMarks = $this->session->getVar("wp_ExportIndentQuoteMarks") ? TRUE : FALSE;
        $pString .= HTML\td($this->pluginmessages->text("indentQuoteMarks") . ":&nbsp;&nbsp;" .
            FORM\checkbox(FALSE, "exportIndentQuoteMarks", $indentQuoteMarks));
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();

        return $pString;
    }
}
