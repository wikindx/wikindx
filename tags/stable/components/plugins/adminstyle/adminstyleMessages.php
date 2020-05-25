<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 *	ADMINSTYLE plugin -- English messages.
 */
class adminstyleMessages
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
            "pluginSub" => dgettext($domain, "Styles..."),
            "addStyle" => dgettext($domain, "Create Style"),
            "copyStyle" => dgettext($domain, "Copy Style"),
            "editStyle" => dgettext($domain, "Edit Style"),
            "shortName" => dgettext($domain, "Short Name"),
            "longName" => dgettext($domain, "Long Name"),
            /**
             * General
             */
            "successEdit" => dgettext($domain, "Successfully edited style"),
            "successAdd" => dgettext($domain, "Successfully added style"),
            /**
             * Styles
             */
            "primaryCreatorSep" => dgettext($domain, "Primary creator delimiters"),
            "otherCreatorSep" => dgettext($domain, "Other creator delimiters"),
            "ifOnlyTwoCreators" => dgettext($domain, "If only two creators"),
            "creatorSepBetween" => dgettext($domain, "between"),
            "creatorSepLast" => dgettext($domain, "before last"),
            "sepCreatorsFirst" => dgettext($domain, "Between first two creators"),
            "sepCreatorsNext" => dgettext($domain, "Between following creators"),
            "primaryCreatorStyle" => dgettext($domain, "Primary creator style"),
            "otherCreatorStyle" => dgettext($domain, "Other creator styles"),
            "creatorFirstStyle" => dgettext($domain, "First"),
            "creatorOthers" => dgettext($domain, "Others"),
            "creatorInitials" => dgettext($domain, "Initials"),
            "creatorFirstName" => dgettext($domain, "First name"),
            "creatorFirstNameFull" => dgettext($domain, "Full"),
            "creatorFirstNameInitials" => dgettext($domain, "Initial"),
            "primaryCreatorList" => dgettext($domain, "Primary creator list abbreviation"),
            "otherCreatorList" => dgettext($domain, "Other creator list abbreviation"),
            "creatorListFull" => dgettext($domain, "Full list"),
            "creatorListLimit" => dgettext($domain, "Limit list"),
            // translators: The next 3 surround form text boxes: "If xx or more creators, list the first xx and abbreviate with xx".  For example: "If 4 or more creators, list the first 1 and abbreviate with ,et. al"
            "creatorListIf" => dgettext($domain, "If"),
            "creatorListOrMore" => dgettext($domain, "or more creators, list the first"),
            "creatorListAbbreviation" => dgettext($domain, "and abbreviate with"),
            "titleCapitalization" => dgettext($domain, "Title capitalization"),
            // Title as entered with no changes to capitalization
            "titleAsEntered" => dgettext($domain, "As entered"),
            "availableFields" => dgettext($domain, "Available fields:"),
            "availableFieldsBib" => dgettext($domain, "Available fields (Bibliography)"),
            "availableFieldsFoot" => dgettext($domain, "Available fields (Footnote)"),
            "disableFields" => dgettext($domain, "Disable fields"),
            "resetFields" => dgettext($domain, "RESET"),
            "editionFormat" => dgettext($domain, "Edition format"),
            "monthFormat" => dgettext($domain, "Month format"),
            "dateFormat" => dgettext($domain, "Date format"),
            "dayFormat" => dgettext($domain, "Day format"),
            // Add a leading zero to day if less than 10.
            "dayLeadingZero" => dgettext($domain, "Add leading zero"),
            "pageFormat" => dgettext($domain, "Page format"),
            // Length of film, broadcast etc.
            "runningTimeFormat" => dgettext($domain, "Running time format"),
            // When displaying a book that has no author but has an editor, do we put the editor in the position occupied by the author?
            "editorSwitchHead" => dgettext($domain, "Editor switch"),
            "editorSwitch" => dgettext($domain, "For books with no author but an editor, put editor in author position"),
            "yes" => dgettext($domain, "Yes"),
            "no" => dgettext($domain, "No"),
            "editorSwitchIfYes" => dgettext($domain, "If 'Yes', replace editor field in style definitions with"),
            // Uppercase creator names?
            "uppercaseCreator" => dgettext($domain, "Uppercase all names"),
            // For repeated creator names in next bibliographic item
            "repeatCreators" => dgettext($domain, "For works immediately following by the same creators"),
            "repeatCreators1" => dgettext($domain, "Print the creator list"),
            "repeatCreators2" => dgettext($domain, "Do not print the creator list"),
            "repeatCreators3" => dgettext($domain, "Replace creator list with text below"),
            // Fallback formatting style when a specific resource type has none defined
            "fallback" => dgettext($domain, "Fallback style"),
            "bibFormat" => dgettext($domain, "Bibliography Formatting"),
            "italics" => dgettext($domain, "Italics"),
            // For user specific month naming
            "userMonthSelect" => dgettext($domain, "Use month names defined below"),
            "userMonths" => dgettext($domain, "User-defined month names (all fields must be completed if selected above)"),
            "userSeasons" => dgettext($domain, "User-defined seasons (all fields must be completed if month format above is set to user-defined)"),
            // Date ranges for e.g. conferences
            "dateRange" => dgettext($domain, "Date range"),
            "dateRangeDelimit1" => dgettext($domain, "Delimiter between start and end dates if day and month given"),
            "dateRangeDelimit2" => dgettext($domain, "Delimiter between start and end dates if month only given"),
            "dateRangeSameMonth" => dgettext($domain, "If start and end months are equal"),
            "dateRangeSameMonth1" => dgettext($domain, "Print both months"),
            "dateRangeSameMonth2" => dgettext($domain, "Print start month only"),
            // Different puncutation may be required if a month is given with no day.
            "dateMonthNoDay" => dgettext($domain, "If a date has a month but no day"),
            "dateMonthNoDay1" => dgettext($domain, "Use style definition unchanged"),
            "dateMonthNoDay2" => dgettext($domain, "Replace date field in template with:"),
            // Don't translate 'date'
            "dateMonthNoDayHint" => dgettext($domain, "(Use 'date' as the field)"),
            // Which language localization to use for ordinals and months.
            "language" => dgettext($domain, "Localization"),
            // Re-write creator(s) portion of templates to handle styles such as DIN 1505.
            "rewriteCreator1" => dgettext($domain, "Split creator lists and add strings"),
            "rewriteCreator2" => dgettext($domain, "Add string to first name in list"),
            "rewriteCreator3" => dgettext($domain, "Add string to remaining names"),
            "rewriteCreator4" => dgettext($domain, "Before name:"),
            "rewriteCreator5" => dgettext($domain, "To each name:"),
            "bibTemplate" => dgettext($domain, "Bibliography template"),
            // Don't translate 'pages'
            "footnotePageField" => dgettext($domain, "(Footnote template may also use 'pages')"),
            // Some general help for using templates displayed in the Admin|Style page
            "templateHelp1" => dgettext($domain, "1. The three generic bibliography templates are required and will be used if a displayed resource has no bibliographic template."),
            "templateHelp2" => dgettext($domain, "2. The footnote templates are only required for those styles that use footnotes for citations.  In all cases, the complete bibliography ('works cited') for footnote styles, as well as for endnote and in-text styles, uses the bibliography template."),
            // Don't translate 'citation'
            "templateHelp3" => dgettext($domain, "3. For footnote citations, the 'citation' field above refers to the footnote template or, if that does not exist, to the bibliography template or, if that does not exist, to the fallback style."),
            // Don't translate 'pages'
            "templateHelp4" => dgettext($domain, "4. The 'pages' field in the bibliography template refers to the complete article page range; in the footnote template, it refers to the specific citation page(s)."),
            "templateHelp5" => dgettext($domain, "5. If you define a footnote template for a resource you must also define the bibliography template for that resource."),
            "templateHelp6" => dgettext($domain, "6. If a resource is missing the first field in the bibliography template you may replace that field with the fields in the partial template (this allows a reordering of the initial fields)."),
            "templateHelp7" => dgettext($domain, "7. In the bibliography template for Book Chapter, the field 'title' refers to the chapter number."),
            // For template previewing, allow the use to preview by turning various fields on and off.
            "previewFields" => dgettext($domain, "Preview with the following fields"),
            // Characters separating title and subtitle
            "titleSubtitleSeparator" => dgettext($domain, "Title/subtitle separator"),
            // See "templateHelp6" above
            "partialTemplate" => dgettext($domain, "Partial template"),
            // Use the partial template to replace all of the bibliography template.
            "partialReplace" => dgettext($domain, "Replace all of original template with partial template"),
            "genericBook" => dgettext($domain, "Generic book-type"),
            "genericArticle" => dgettext($domain, "Generic article-type"),
            "genericMisc" => dgettext($domain, "Generic miscellaneous"),
            "previewStyle" => dgettext($domain, "Preview bibliography"),
            "previewCite" => dgettext($domain, "Preview in-text citation"),
            "previewFoot" => dgettext($domain, "Preview footnote"),
            "previewError" => dgettext($domain, "ERROR"),
            /**
             * Citation
             */
            // The displayed hyperlink next to the textarea form input
            "cite" => dgettext($domain, "Cite"),
            "citationFormat" => dgettext($domain, "Citation Formatting"),
            // In-text citation style as opposed to endnote style citations.
            "citationFormatInText" => dgettext($domain, "In-text style"),
            "citationFormatEndnote" => dgettext($domain, "Endnote style"),
            "citationFormatFootnote" => dgettext($domain, "Footnote creators"),
            "creatorList" => dgettext($domain, "Creator list abbreviation"),
            "creatorListSubsequent" => dgettext($domain, "Creator list abbreviation (subsequent appearances)"),
            "creatorSep" => dgettext($domain, "Creator delimiters"),
            "creatorStyle" => dgettext($domain, "Creator style"),
            "lastName" => dgettext($domain, "Last name only"),
            // 'Last name only' is a choice in a select box and should not be translated
            "useInitials" => dgettext($domain, "If 'Last name only', use initials to differentiate between creators with the same surname"),
            // For consecutive citations by the same creator(s)
            "consecutiveCreator" => dgettext($domain, "For consecutive citations by the same creator(s) use the following template:"),
            "consecutiveCreatorSep" => dgettext($domain, "and separate citations with:"),
            // The template is something like '(author|, year)' that the user is asked to enter
            "template" => dgettext($domain, "Template"),
            "consecutiveCitationSep" => dgettext($domain, "Separate consecutive citations with"),
            // Formatting of years
            "yearFormat" => dgettext($domain, "Year format"),
            // Normal, superscript or subscript of citation
            "normal" => dgettext($domain, "Normal text"),
            "superscript" => dgettext($domain, "Superscript"),
            "subscript" => dgettext($domain, "Subscript"),
            "enclosingCharacters" => dgettext($domain, "Parentheses or other characters enclosing the citation"),
            // Ambiguous citations
            "ambiguous" => dgettext($domain, "Ambiguous citations"),
            "ambiguousTitle" => dgettext($domain, "Use the following template"),
            "ambiguousYear" => dgettext($domain, "Add a letter after the year"),
            "ambiguousUnchanged" => dgettext($domain, "Leave citation unchanged"),
            "followCreatorTemplate" => dgettext($domain, "Use template below if a single citation is in the same sentence as the first creator's surname"),
            // This follows on from sentence above....  Split the pages from the main citation placing the main citation immediately after the creator names in the text and the pages immediately following the quote.  e.g. if the citation is in the form: Grimshaw states:  "WIKINDX is wonderful" [cite]123:25[/cite], the result will be Grimshaw (2005) states:  "WIKINDX is wonderful" (p.25) rather than Grimshaw states:  "WIKINDX is wonderful" (2005, p.25).
            "followCreatorPageSplit" => dgettext($domain, "and split the citation placing the main citation after the creator names and the page number after the quote:"),
            // For endnote-style citations
            "footnoteStyleBib" => dgettext($domain, "Format like bibliography"),
            "footnoteStyleInText" => dgettext($domain, "Format like in-text citations"),
            "ibid" => dgettext($domain, "Replace consecutive citations for the same resource and the same page with this template"),
            "idem" => dgettext($domain, "Replace consecutive citations for the same resource but a different page with this template"),
            "opCit" => dgettext($domain, "Replace previously cited resources with this template"),
            "endnoteFormat1" => dgettext($domain, "Format of the citation in the text"),
            "endnoteFormat2" => dgettext($domain, "Format of the citation in the endnotes"),
            "endnoteStyle" => dgettext($domain, "Endnote/Footnote"),
            "endnoteStyle1" => dgettext($domain, "Endnotes: incrementing"),
            "endnoteStyle2" => dgettext($domain, "Endnotes: same ID for same resource"),
            "endnoteStyle3" => dgettext($domain, "Footnotes: incrementing"),
            // Ordering of the appended bibliography for in-text citations and endnote-style citations using the same id number for each cited resource
            "orderBib1" => dgettext($domain, "Bibliography ordering"),
            "orderBib2" => dgettext($domain, "(For in-text citations, endnote-style citations using the same ID number and bibliographies appended to papers using footnotes.)"),
            "orderBib3" => dgettext($domain, "Use this order for endnote-style citations using the same ID number:"),
            "order1" => dgettext($domain, "1st. sort by"),
            "order2" => dgettext($domain, "2nd. sort by"),
            "order3" => dgettext($domain, "3rd. sort by"),
            "ascending" => dgettext($domain, "Ascending"),
            "descending" => dgettext($domain, "Descending"),
            // For a particular resource type (personal communication for example), replace the in-text citation template with another template
            "typeReplace" => dgettext($domain, "For in-text citations, replace the citation template with this template"),
            // Text preceeding and following citations e.g. (see Grimshaw 1999; Boulanger 2004 for example): 'see' is preText and 'for example' is postText
            "preText" => dgettext($domain, "Preliminary text"),
            "postText" => dgettext($domain, "Following text"),
            // Formatting of the id number in the endnotes for endnote-style citations
            "endnoteIDEnclose" => dgettext($domain, "Parentheses or other characters enclosing the ID number"),
            // For subsequent citations from the same resource
            "subsequentCreator" => dgettext($domain, "For subsequent citations from the same resource use the following template:"),
            // This follows on from the text in 'subsequentCreator'
            "subsequentFields" => dgettext($domain, "only if the sentence containing the citation has the creator surname, title or shortTitle in it:"),
            // If no year for in-text citations, replace year field
            "replaceYear" => dgettext($domain, "If no year, replace year field with the following"),
            // When compiling the appended bibliography for in-text citations, certain resources (e.g. APA personal communication) are not added.
            "notInBibliography" => dgettext($domain, "Do not add to the bibliography when cited:"),
            // When using endnote-style citations and defining templates using fields such as 'creator', 'pages', 'year' or 'title'.  Don't translate 'creator', 'pages' or 'citation'.
            "endnoteFieldFormat" => dgettext($domain, "Fields are formatted as defined in in-text citation formatting above unless using footnotes in which case the 'creator' field is defined below and the 'pages' field format is defined in the footnote template in the bibliography section. If the 'citation' field is used, it should be by itself and it refers to the bibliographic/footnote templates below."),
            "footnoteTemplate" => dgettext($domain, "Footnote template"),
            // Set the range of text within which the subsequent creator template (for in-text citations) is used
            "subsequentCreatorRange" => dgettext($domain, "Range within which subsequent citations are searched for"),
            "subsequentCreatorRange1" => dgettext($domain, "Entire text"),
            "subsequentCreatorRange2" => dgettext($domain, "Paragraph"),
            "subsequentCreatorRange3" => dgettext($domain, "Section"),
            "removeTitle" => dgettext($domain, "Remove title and shortTitle fields from the citation if either of those fields is in the same sentence:"),
            "creator" => dgettext($domain, "First Creator"),
            "title" => dgettext($domain, "Title"),
            "year" => dgettext($domain, "Publication Year"),
            /**
             * Hints
             */
            "hint_styleShortName" => dgettext($domain, "(No spaces)"),
            "hint_caseSensitive" => dgettext($domain, "(Fields are case-sensitive)"),
        ];
    }
}
