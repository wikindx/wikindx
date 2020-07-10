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
 * WORDPROCESSOR plugin -- messages.
 */
class wordprocessorMessages
{
    /** array */
    public $text = [];
    
    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            /**
             * Menu items
             */
            "wpSub" => dgettext($domain, "Word Processor..."),
            "wpOpen" => dgettext($domain, "Open Paper"),
            "wpList" => dgettext($domain, "List Papers"),
            "wpDelete" => dgettext($domain, "Delete Paper"),
            "wpImport" => dgettext($domain, "Import Paper"),
            "wpNew" => dgettext($domain, "New Paper"),
            /**
             * Other messages
             */
            "headingPaper" => dgettext($domain, "Word Processor"),
            "headingImport" => dgettext($domain, "Import Paper"),
            "headingOpen" => dgettext($domain, "Open Paper"),
            "headingDelete" => dgettext($domain, "Delete Paper"),
            "headingListPapers" => dgettext($domain, "List Papers"),
            "headingSavePaper" => dgettext($domain, "Save Paper"),
            "headingExportPaper" => dgettext($domain, "Export & Save Paper"),
            "headingAddFootnote" => dgettext($domain, "Add Footnote"),
            "headingAddTable" => dgettext($domain, "Add Table"),
            "headingAddImage" => dgettext($domain, "Add Image"),
            "headingAddLink" => dgettext($domain, "Add Link"),
            "headingAddCitation" => dgettext($domain, "Add Citation"),
            "paperTitle" => dgettext($domain, "Title of Paper"),
            "revert" => dgettext($domain, "Revert to saved version"),
            "revertConfirm" => dgettext($domain, "Are you sure you wish to revert to the saved version?"),
            "new" => dgettext($domain, "New paper"),
            "open" => dgettext($domain, "Open paper"),
            "delete" => dgettext($domain, "Delete paper"),
            "deleteConfirm" => dgettext($domain, "Delete paper(s): "),
            "deletePaper" => dgettext($domain, "Successfully deleted paper(s)"),
            "list" => dgettext($domain, "List papers"),
            "backup" => dgettext($domain, "You can download and backup papers if you wish. The newest files are displayed first."),
            "noPapers" => dgettext($domain, "You have no papers"),
            "import" => dgettext($domain, "Import paper"),
            "import2" => dgettext($domain, "You can import a paper here which should be a plain file as backed up from WIKINDX"),
            "import3" => dgettext($domain, "Filename"),
            /// browserTry When WIKINDX detects what is probably an incompatible browser for the SUWP, issue a warning and give the option to give it a try anyway
            "browserTry" => dgettext($domain, "Try to use the SUWP anyway"),
            /// noAutoSave Warning that there is no autosave or prompt to save when user accesses another menu item without saving first!
            "noAutosave" => dgettext($domain, "There is no autosave or save prompt"),
            /// menuRestore The SUWP menu is hidden for safety reasons when an edit has been made to the paper.  This is a hyperlink that restores it.
            "menuRestore" => dgettext($domain, "Restore menu"),
            "appendPaper" => dgettext($domain, "Paper has now been loaded ready for appending"),
            /// addSection Add a section break before appending the paper
            "addSection" => dgettext($domain, "Add section break before appended paper:"),
            /// citeTagOnly When searching and inserting metadata in the SUWP, insert only the cite tag (and any pages)
            "citeTagOnly" => dgettext($domain, "Insert only the cite tag"),
            "paperExists" => dgettext($domain, "The paper you are trying to import already exists in this wikindx"),
            "savePaper" => dgettext($domain, "Successfully saved paper"),
            "deletePaper" => dgettext($domain, "Successfully deleted paper(s)"),
            "importPaper" => dgettext($domain, "Successfully imported paper"),
            /// loadAppendPaper Load a paper ready for appending one paper to another in the WP
            "loadAppendPaper" => dgettext($domain, "Load paper"),
            "alreadyDeleted" => dgettext($domain, "You have already deleted that paper"),
            "savedStatus" => dgettext($domain, "Paper saved"),
            "notSavedStatus" => dgettext($domain, "Paper not yet saved"),
            "saveFailure" => dgettext($domain, "Paper not saved: write error"),
            "invalidTitle" => dgettext($domain, "Paper not saved: invalid title"),
            "saveAsNewVersion" => dgettext($domain, "Save new version"),
            /// Export functions
            "export" => dgettext($domain, "Export paper to"),
            "exportRtf" => dgettext($domain, "Rich Text Format (RTF)"),
            /// lineSpacePaper Line spacing for paper exporting to RTF etc.
            "lineSpacePaper" => dgettext($domain, "Paper line space"),
            "lineSpaceBib" => dgettext($domain, "Bibliography line space"),
            "singleSpace" => dgettext($domain, "Single"),
            "oneHalfSpace" => dgettext($domain, "1.5 lines"),
            "doubleSpace" => dgettext($domain, "Double"),
            /// indentBib Bibliography indentation for exporting to RTF etc.
            "indentBib" => dgettext($domain, "Bibliography indentation"),
            "indentNone" => dgettext($domain, "None"),
            "indentAll" => dgettext($domain, "All"),
            "indentFL" => dgettext($domain, "First line"),
            "indentNotFL" => dgettext($domain, "All but first line"),
            /// indentFt Spacing, font size and indentation for footnotes
            "indentFt" => dgettext($domain, "Footnote indentation"),
            "lineSpaceFt" => dgettext($domain, "Footnote line space"),
            "fontSizeFt" => dgettext($domain, "Footnote font size"),
            /// pageNumber Page numbering for RTF exports
            "pageNumber" => dgettext($domain, "Page numbering"),
            "pageNumberFooter" => dgettext($domain, "Footer"),
            "pageNumberHeader" => dgettext($domain, "Header"),
            "pageNumberNone" => dgettext($domain, "None"),
            /// pageNumberalign Page number alignment for RTF exports
            "pageNumberAlign" => dgettext($domain, "Page number alignment"),
            "pageNumberAlignCentre" => dgettext($domain, "Centre"),
            "pageNumberAlignLeft" => dgettext($domain, "Left"),
            "pageNumberAlignRight" => dgettext($domain, "Right"),
            /// indentQuoteWords Indentation options for large quotations
            "indentQuoteWords" => dgettext($domain, "Indent quotations with at least this number of words"),
            "lineSpaceIndentQ" => dgettext($domain, "Quotation line space"),
            "indentQuoteFontSize" => dgettext($domain, "Font size"),
            "indentQuoteMarks" => dgettext($domain, "Keep quotation marks"),
            "exportAndSave" => dgettext($domain, "Export and Save"),
            /// sectionFtRestart If a section has been inserted in the paper, any footnotes are renumbered from 1 at each section
            "sectionFtRestart" => dgettext($domain, "Restart footnotes at each section"),
            /// paperSize Size of exported paper (letter, A4 etc.)
            "paperSize" => dgettext($domain, "Paper size"),
            "tableColumns" => dgettext($domain, "Columns"),
            "tableRows" => dgettext($domain, "Rows"),
            "imagePath" => dgettext($domain, "Image URL"),
            "linkPath" => dgettext($domain, "URL"),
        ];
    }
}
