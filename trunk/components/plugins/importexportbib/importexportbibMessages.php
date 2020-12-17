<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */
class importexportbibMessages
{
    /** array */
    public $text = [];
    
    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            "menu" => dgettext($domain, "Import & Export..."),
            "menuEndnoteImport" => dgettext($domain, "Import Endnote"),
            "headerEndnoteImport" => dgettext($domain, "Import Endnote XML Bibliography"),
            "menuPubMedImport" => dgettext($domain, "Import PubMed"),
            "headerPubMedImport" => dgettext($domain, "Import PubMed"),
            "menuBibutils" => dgettext($domain, "Bibutils"),
            "menuRtfExport" => dgettext($domain, "Exp. RTF..."),
            "menuBibtexExport" => dgettext($domain, "Exp. BibTeX..."),
            "menuHtmlExport" => dgettext($domain, "Exp. HTML..."),
            "menuRisExport" => dgettext($domain, "Exp. RIS..."),
            "menuEndnoteExport" => dgettext($domain, "Exp. Endnote..."),
            "menuExportBasket" => dgettext($domain, "Basket"),
            "menuExportList" => dgettext($domain, "Last List"),
            "menuListFiles" => dgettext($domain, "Exported Files"),
            "menuIdeaExport" => dgettext($domain, "Export Ideas"),
            "headerRtfExport" => dgettext($domain, "Export Rich Text Format"),
            "headerBibtexExport" => dgettext($domain, "Export BibTeX"),
            "headerHtmlExport" => dgettext($domain, "Export HTML"),
            "headerRisExport" => dgettext($domain, "Export RIS"),
            "headerEndnoteExport" => dgettext($domain, "Export Endnote"),
            "headerIdeaExport" => dgettext($domain, "Export Ideas"),
            "headerListFiles" => dgettext($domain, "Exported Files"),
            "headerBibutils" => dgettext($domain, "Bibutils Conversion"),
            "bibutilsinputType" => dgettext($domain, "Input Type"),
            "bibutilsoutputType" => dgettext($domain, "Output Type"),
            "bibutilsinputFile" => dgettext($domain, "Input File"),
            "bibutilsoutputFile" => dgettext($domain, "Output File"),
            "bibutilscredit" => dgettext($domain, "The Bibutils plugin uses C binaries written by Chris Putnam at ###. It allows for the format conversion of many different types of bibliographies so that there is usually a way to import your bibliography into WIKINDX. Most conversions use the MODS XML intermediate format."),
            "bibutilsnoPrograms" => dgettext($domain, "No Bibutils programs found in ###. Either Bibutils is not installed or the path is incorrect (edit the plugin's config.php) or \$PATH"),
            "bibutilsnoInputType" => dgettext($domain, "No input type specified"),
            "bibutilsnoOutputType" => dgettext($domain, "No output type specified"),
            "bibutilsnoFileInput" => dgettext($domain, "Missing input file"),
            "bibutilsfailedToConvert" => dgettext($domain, "FAILED to convert (###)"),
            "bibutilsxmlOptions" => dgettext($domain, "Conversion options for intermediate MODS XML"),
            "bibutilsignore" => dgettext($domain, "IGNORE"),
            "bibutilsoption1" => dgettext($domain, "Encode unicode characters directly in the file rather than as XML entities"),
            "bibutilsoption2" => dgettext($domain, "Don't put citation key in the MODS id field"),
            "bibutilsoption3" => dgettext($domain, "Don't split titles into TITLE/SUBTITLE pairs"),
            "bibutilsoption4" => dgettext($domain, "Don't covert latex character combinations"),
            "bibutilsoption5" => dgettext($domain, "Unicode in, unicode out"),
            "bibutilsSuccess" => dgettext($domain, "Successfully converted ###"),
            "introEndnoteImport" => dgettext($domain, "You may import Endnote XML bibliographies (.xml files) here. Large files may take some time so if
				WIKINDX senses that php.ini's 'max_execution_time' variable is about to be exceeded, it will start importing the bibliography in chunks.
				If there is a date field, the format should be either dd/mm/yyyy or yyyy/mm/dd and yyyy will override any year field in the record.
				If you have custom fields in your import file, create custom fields first in the WIKINDX database (the Admin menu) so that you can then map
				the import custom fields."),
            "introRtfExport" => dgettext($domain, "You may export to Rich Text Format your most recent bibliography list. Large numbers of resources might
				take some time and memory so you might need to adjust php.ini."),
            "category" => dgettext($domain, "Category"),
            "categoryPrompt" => dgettext($domain, "All WIKINDX resources belong to at least one category which you chose here.  The category(s) a resource belongs to can always be edited later."),
            // importDuplicates For file imports, allow duplicates?
            "importDuplicates" => dgettext($domain, "Import duplicates:"),
            "importKeywordIgnore" => dgettext($domain, "Ignore keywords:"),
            "storeRawEndnoteImport" => dgettext($domain, "You may store Endnote fields that WIKINDX does not use so that any resources later exported to Endnote can include this original unchanged data. Store unused fields:"),
            "empty" => dgettext($domain, "File is empty"),
            "added" => dgettext($domain, "No. resources added: ###"),
            "discarded" => dgettext($domain, "No. resources discarded (duplicates or no titles): ###"),
            // invalidField1 If non-standard import fields are found in the input file, invite the user to map these fields to wikindx fields
            "invalidField1" => dgettext($domain, "Unknown fields have been found. You may map these fields to WIKINDX fields -- no duplicate mapping is allowed."),
            "invalidField2" => dgettext($domain, "Where an unknown field is mapped to a WIKINDX field that would normally be automatically mapped to a standard input field, the unknown field mapping takes precedence."),
            "file" => dgettext($domain, "Import File"),
            "tag" => dgettext($domain, "Tag this import so you can do a mass select or delete later"),
            // executionTimeExceeded With large imports that would go over php.ini's max_execution time, WIKINDX splits the imports into chunks
            "executionTimeExceeded" => dgettext($domain, "'max_execution_time' (### seconds) in php.ini was about to be exceeded.  WIKINDX is importing the bibliography in chunks."),
            "addedChunk" => dgettext($domain, "No. resources added this chunk: ###"),
            "fileImport" => dgettext($domain, "You have already imported that file"),
            "empty" => dgettext($domain, "Import bibliography is empty"),
            "upload" => dgettext($domain, "File upload error"),
            "noList" => dgettext($domain, "You must create a list from the Search menu first"),
            "noIdeas" => dgettext($domain, "You have no ideas"),
            "allIdeas" => dgettext($domain, "Export all ideas"),
            "selectedIdeas" => dgettext($domain, "Export selected ideas"),
            "exported" => dgettext($domain, "Data successfully exported"),
            "imported" => dgettext($domain, "Data successfully imported"),
            "contents" => dgettext($domain, "Contents of your temporary folder (newest first):"),
            "noContents" => dgettext($domain, "Directory is empty"),
            "warning" => dgettext($domain, "These files will be available for ### minutes or while you keep your browser open (whichever is the shorter) so download and save them elsewhere immediately"),
            "bibliography" => dgettext($domain, "Export bibliographic entries:"),
            "fontSize" => dgettext($domain, "Font size"),
            // Font type e.g. 'Times New Roman', 'Courier'
            "font" => dgettext($domain, "Font"),
            "abstract" => dgettext($domain, "Export abstract:"),
            "notes" => dgettext($domain, "Export notes:"),
            "quotes" => dgettext($domain, "Export quotes:"),
            "paraphrases" => dgettext($domain, "Export paraphrases:"),
            "musings" => dgettext($domain, "Export musings:"),
            "comments" => dgettext($domain, "Export comments:"),
            // Number of indents (tabulation - TAB)
            "indentL" => dgettext($domain, "Left indent"),
            "indentR" => dgettext($domain, "Right indent"),
            // 'CR' = carriage return (newlines)
            "crFollowing" => dgettext($domain, "CR following"),
            "crBetween" => dgettext($domain, "CR between"),
            // bold, italics, underline
            "textFormat" => dgettext($domain, "Text format"),
            // 'tag' = label given to each section (abstract, quotes, quote comments etc.)
            "tag" => dgettext($domain, "Tag"),
            // Some text or characters to visually divide resources in the RT output
            "divider" => dgettext($domain, "Divider between entries"),
            "dividerCR" => dgettext($domain, "CR after entries"),
            "isbn" => dgettext($domain, "Include ID no. (ISBN etc.)"),
            // Metadata export options
            "metadata" => dgettext($domain, "If exporting quote and paraphrase comments or musings"),
            // These two are in a select box and follow on from the string above
            "metadataUser" => dgettext($domain, "Export only mine"),
            "metadataAll" => dgettext($domain, "Export my data and all public data"),
            "metadataFullCite" => dgettext($domain, "Add resource's primary creator and publication year to metadata"),
            // Default text for labelling metadata in the RTF export
            "quotesTag" => dgettext($domain, "QUOTES:"),
            "paraphrasesTag" => dgettext($domain, "PARAPHRASES:"),
            "quotesCommentTag" => dgettext($domain, "COMMENTS:"),
            "paraphrasesCommentTag" => dgettext($domain, "COMMENTS:"),
            "musingsTag" => dgettext($domain, "MUSINGS:"),
            "abstractTag" => dgettext($domain, "ABSTRACT:"),
            "notesTag" => dgettext($domain, "NOTES:"),
            "keywords" => dgettext($domain, "Include resource keywords"),
            "exportHyperlink" => dgettext($domain, "Include a hyperlink to the resource in this WIKINDX:"),
            "exportEndnoteFileType" => dgettext($domain, "Endnote file type"),
            "exportEndnoteXml" => dgettext($domain, "Endnote XML"),
            "exportEndnoteTabbed" => dgettext($domain, "Endnote tabbed"),
            "importPubMedIntro" => dgettext($domain, "Search PubMed: please enter one or more fields – if the ID field is 
            	completed, only one result will be returned if found. Multiple items within a field
				should be separated by a space. Per NCBI policy, a maximum of 100 search results can be obtained."),
            "importPubMedNoBibutils" => dgettext($domain, "PubMed imports require Bibutils to be installed."),
            "importPubMedId" => dgettext($domain, "PubMed ID"),
            "importPubMedFields" => dgettext($domain, "All Fields"),
            "importPubMedAuthor" => dgettext($domain, "Author"),
            "importPubMedFirstAuthor" => dgettext($domain, "First Author"),
            "importPubMedLastAuthor" => dgettext($domain, "Last Author"),
            "importPubMedTitle" => dgettext($domain, "Title"),
            "importPubMedAbstract" => dgettext($domain, "Abstract"),
            "importPubMedYear" => dgettext($domain, "Year"),
            "importPubMedJournal" => dgettext($domain, "Journal"),
            "importPubMedVolume" => dgettext($domain, "Volume"),
            "importPubMedIssue" => dgettext($domain, "Issue"),
            "importPubMedLanguage" => dgettext($domain, "Language"),
            "importPubMedLimit" => dgettext($domain, "Limit search to the last n days"),
            "importPubMedMaxResults" => dgettext($domain, "Max. Results"),
            "importPubMedWikindx" => dgettext($domain, "Import the PubMed import directly into WIKINDX"),
            "importPubMedLimitError" => dgettext($domain, "Please enter an integer up to 100 for Max. Results"),
            "importPubMedInputError" => dgettext($domain, "Please enter one or more fields"),
            "importPubMedResult" => dngettext($domain, "result", "results", 1),
            "importPubMedResults" => dngettext($domain, "result", "results", 2),
            "importPubMedSuccess" => dgettext($domain, "Successfully converted ###"),
            "importPubMedOutputFile" => dgettext($domain, "Output File"),
            "importPubMedNoResults" => dgettext($domain, "No Results Found"),
            "importPubMedFailConvert" => dgettext($domain, "FAILED to convert (###)"),
            "importEndnoteNotv8" => dgettext($domain, "XML file is not compatible with Endnote v8 which is required for this import script."),
            "importEndnoteSuccess" => dgettext($domain, "Successfully imported Endnote file."),
        ];
    }
}
