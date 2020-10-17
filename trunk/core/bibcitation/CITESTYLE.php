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
 * CITATION STYLE class. Format citations.
 *
 * For non-WIKINDX users, you must have set up BIBFORMAT before using this (see documentation for BIBFORMAT()).
 *
 * @package wikindx\core\bibcitation
 */
class CITESTYLE
{
    /** string */
    public $output = 'html';
    /** boolean */
    public $rtfBibExport = FALSE;
    /** boolean */
    public $dir = FALSE;
    /** object */
    public $citeFormat;
    /** object */
    private $db;
    /** object */
    private $res;
    /** object */
    private $session;
    /** string */
    private $setupStyle;
    /** string */
    private $pageStart;
    /** string */
    private $pageEnd;
    /** string */
    private $preText;
    /** string */
    private $postText;
    /** array */
    private $citeIds;

    /**
     * CITESTYLE
     *
     * @param string $output 'html', plain', 'rtf'. Default is 'html'
     */
    public function __construct($output = 'html')
    {
        $this->output = $output;
        $this->db = FACTORY_DB::getInstance();
        $this->res = FACTORY_RESOURCECOMMON::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        // BIBSTYLE.php is used by WIKINDX to set up the system for BIBFORMAT.php which is the bibliographic formatting engine.  Although CITESTYLE initialises
        // CITEFORMAT (the OSBib citation engine), BIBFORMAT is required for the appending of bibliographies to the text containing citations.
        // Your system will need something similar to BIBSTYLE (see documentation for BIBFORMAT()).
        $this->citeFormat = FACTORY_CITEFORMAT::getInstance($this->output);
    }
    /**
     * Start the whole process off by finding [cite]...[/cite] tags in input text.
     *
     * WIKINDX uses [cite]34[/cite] or [cite]34:23[/cite] or [cite]34:23-24[/cite] where '34' is the resource's unique ID, '23' is a single page for the citation and
     * '23-24' is a page range for the citation.  If your system uses something else, you will need to make changes here and in $this->parseCiteTag().
     * PreText and postText can also be encoded: e.g. (see Grimshaw 2003; Boulanger 2004 for example)
     * [cite]23:34-35|see ` for example[/cite]
     *
     * @param string $text
     * @param bool $citeLink
     *
     * @return string
     */
    public function start($text, $citeLink)
    {
        $this->init();
        // Turn on hyperlinking for html output of the citation references within the text.
        // The unique resource ID in the database is appended to this string.  The default in $this->citeFormat is FALSE meaning no hyperlinking.
        if ($citeLink) {
            $this->citeFormat->hyperlinkBase = "index.php?action=resource_RESOURCEVIEW_CORE&id=";
        }
        // Capture any text after last [cite]...[/cite] tag
        $explode = \UTF8\mb_explode("]etic/[", \UTF8\mb_strrev($text), 2);
        $this->tailText = \UTF8\mb_strrev($explode[0]);
        $text = \UTF8\mb_strrev("]etic/[" . $explode[1]);
        preg_match_all("/(.*)\\s*\\[cite\\](.*)\\[\\/cite\\]/Uuis", $text, $match);
        foreach ($match[1] as $value) {
            if (($this->output == 'html') || ($this->output == 'htmlNoBib')) { // WIKINDX metadata stored in db with <br>
                $this->matches[1][] = $value;
            } else {
                // Wikindx v4 runs in HTML 4.01 but TinyMCE was configured for XHTML in v3.8.
                // For the precaution, treating the two notations which can be mixed after migrating to v4.
                $this->matches[1][] = rtrim(str_ireplace(['<br />', '<br/>', '<br>'], '', $value));
            }
        }
        $this->citeFormat->count = 0;
        foreach ($match[2] as $index => $value) {
            // Wikindx v4 runs in HTML 4.01 but TinyMCE was configured for XHTML in v3.8.
            // For the precaution, treating the two notations which can be mixed after migrating to v4.
            if ($id = $this->parseCiteTag($index, str_ireplace(['<br />', '<br/>', '<br>'], '', $value))) {
                $this->citeIds[] = $id;
            }
        }
        // If empty($this->citeIds), there are no citations to scan for (or user has entered invalid IDs) so return $text unchanged.
        if (empty($this->citeIds)) {
            return $text;
        }
        // Get appended bibliographies.  $bibliography is a multiple array of raw bibliographic data from the database suitable for passing to BIBFORMAT.php.
        $bibliography = $this->bibliographyProcess();
        /*
        * $matches[1]is an array of $1 above
        * $matches[2] is an array of $2 (the citation references)
        * e.g.
        * [1] => Array ( [0] => First [1] => [2] => [3] => [4] => blah blah see ) [2] => Array ( [0] => 1 [1] => 2 [2] => 3 [3] => 4 [4] => 2 )
        * might represent:
        * First [cite]1[/cite] [cite]2[/cite] [cite]3[/cite]
        * [cite]1[/cite] blah blah see[cite]2[/cite]
        *
        * Note that having both [1][0] and [2][0] populated means that the citation reference [2][0] _follows_ the text in [1][0].
        * Any unpopulated elements of matches[1] indicates multiple citations at that point.  e.g., in the example above,
        * there are multiple citations (references 1, 2, 3 and 4) following the text 'First' and preceeding the text 'blah blah see'.
        *
        * N.B. the preg_match_all() above does not capture any text after the final citation so this must be handled manually and appended to any final output -
        * this is $this->tailText above.
        */
        $this->row = [];
        $this->citeFormat->count = 0;
        $citeIndex = 0;
        while (!empty($this->matches[1])) {
            $this->citeFormat->item = []; // must be reset each time.
            $id = $this->citeIds[$citeIndex];
            ++$citeIndex;
            ++$this->citeFormat->count;
            $text = array_shift($this->matches[1]);
            $this->citeFormat->items[$this->citeFormat->count]['id'] = $id;
            //			$this->createPages(array_shift($this->pageStart), array_shift($this->pageEnd));
            $this->createPrePostText(array_shift($this->preText), array_shift($this->postText));
            // For each element of $bibliography, process title, creator names etc.
            if (array_key_exists($id, $bibliography)) {
                $this->process($bibliography[$id], $id);
            }
            // $this->rowSingle is set in $this->process().  'type' is the type of resource (book, journal article etc.).  In WIKINDX, this is part of the row returned by SQL:  you may
            // need to set this manually if this is not the case for your system.  'type' is used in CITEFORMAT::prependAppend() to add any special strings to the citation within
            // the text (e.g. the XML style file might state that 'Personal communication: ' needs to be appended to any in-text citations for resources of type 'email'.
            // CITEFORMAT::prependAppend() will map 'type' against the $types array in STYLEMAP as used in BIBFORMAT.
            $this->citeFormat->items[$this->citeFormat->count]['type'] = $this->rowSingle['resourceType'];
            $this->citeFormat->items[$this->citeFormat->count]['text'] = $text;
        }
        $pString = $this->citeFormat->process() . $this->tailText;
        // bibTeX ordinals such as 5$^{th}$
        $pString = preg_replace_callback("/(\\d+)\\$\\^\\{(.*)\\}\\$/u", [$this, "ordinals"], $pString);
        // Endnote-style citations so add the endnotes bibliography
        if ($this->citeFormat->style['citationStyle']) {
            // Turn off hyperlinking for the appended bibliography
            $this->citeFormat->hyperlinkBase = FALSE;
            $pString = $this->citeFormat->printEndnoteBibliography($pString);
            if ($this->citeFormat->style['endnoteStyle'] != 2) { // Not footnotes.
                return $pString;
            }
        }
        if ($this->output == 'htmlNoBib') {
            return $pString;
        }
        // In-text citations and footnotes - output the appended bibliography
        $bib = $this->printBibliography($bibliography);
        if ($this->output == 'rtf') {
            // WIKINDX-specific:  Indentation of appended bibliography
            if ($this->session->getVar("exportPaper_indentBib") == 'indentAll') {
                $bib = "\\li720\n$bib";
            } elseif ($this->session->getVar("exportPaper_indentBib") == 'indentFL') {
                $bib = "\\fi720\n$bib";
            } elseif ($this->session->getVar("exportPaper_indentBib") == 'indentNotFL') {
                $bib = "\\li720\\fi-720\n$bib";
            } else {
                $bib = "\\li1\\fi1\n$bib";
            }
            // WIKINDX-specific:  Line spacing of appended bibliography
            if ($this->session->getVar("exportPaper_spaceBib") == 'oneHalfSpace') {
                $bib = "\\pard\\plain \\sl360\\slmult1\n$bib";
            } elseif ($this->session->getVar("exportPaper_spaceBib") == 'doubleSpace') {
                $bib = "\\pard\\plain \\sl480\\slmult1\n$bib";
            } else {
                $bib = "\\pard\\plain $bib";
            }
            $bib = "\\par\n\n$bib";
        }

        return $pString . $bib;
    }
    /**
     * Gather bibliography of citations.
     *
     * The order is important:
     * 1. for in-text citations as it controls disambiguation where a letter is added after the year.
     * 2. for endnote-style citations, if your bibliographic style says that a resource following another by the same creator(s) should have the creator(s) replaced by
     * something like '_______', order is again important.
     * 3. for endnote-style citations having the same id no. for the same resource where the id no. in the text follows the bibliography order.
     *
     * @return array
     */
    public function bibliographyProcess()
    {
        foreach (array_unique($this->citeIds) as $id) {
            $ids[] = $id;
        }
        // 1st order
        if ($this->citeFormat->style['order1desc']) { // descending (default 0 = ascending)
            $this->db->ascDesc = $this->db->desc;
        }
        $orderYear = $this->db->caseWhen(
            '(' . $this->db->formatFields('resourceType'),
            $this->db->equal .
            $this->db->tidyInput('book') . $this->db->or .
            $this->db->formatFields('resourceType') . $this->db->equal . $this->db->tidyInput('book_article') . ')' .
            $this->db->and . $this->db->formatFields('resourceyearYear1') . ' IS NOT NULL ',
            $this->db->tidyInputClause('resourceyearYear2'),
            $this->db->tidyInputClause('resourceyearYear1'),
            FALSE
        );
        $orderTitle = $this->db->formatFields('resourceTitleSort');
        $orderCreator = $this->db->ifClause(
            $this->db->formatFields('creatorSurname'),
            'IS NOT NULL',
            $this->db->formatFields('creatorSurname'),
            $this->db->formatFields('resourceTitleSort')
        );
        if ($this->citeFormat->style['order1'] == 1) { // publication year
            $order1 = $orderYear;
        } elseif ($this->citeFormat->style['order1'] == 2) { // title
            $order1 = $orderTitle;
        } else { // default: by creator
            $order1 = $orderCreator;
        }
        // 2nd order
        if ($this->citeFormat->style['order2desc']) { // descending (default 0 = ascending)
            $this->db->ascDesc = $this->db->desc;
        }
        if ($this->citeFormat->style['order2'] == 1) { // publication year
            $order2 = $orderYear;
        } elseif ($this->citeFormat->style['order2'] == 2) { // title
            $order2 = $orderTitle;
        } else { // default: by creator
            $order2 = $orderCreator;
        }
        // 3rd order
        if ($this->citeFormat->style['order3desc']) { // descending (default 0 = ascending)
            $this->db->ascDesc = $this->db->desc;
        }
        if ($this->citeFormat->style['order3'] == 1) { // publication year
            $order3 = $orderYear;
        } elseif ($this->citeFormat->style['order3'] == 2) { // title
            $order3 = $orderTitle;
        } else { // default: by creator
            $order3 = $orderCreator;
        }
        $this->db->conditionSeparator = $this->db->or;
        $this->db->formatConditionsOneField($ids, 'resourceId');
        $this->db->conditionSeparator = $this->db->and; // reset
        $resultset = $this->res->getResource(FALSE, $order1, $order2, $order3);
        while ($row = $this->db->fetchRow($resultset)) {
            $row = array_map([$this, "removeSlashes"], $row);
            // collect multiple array for passing to $this->citeFormat->processEndnoteBibliography.  Must be keyed by unique resource identifier.
            $rows[$row['resourceId']] = $row;
            // Set the placeholder to deal with ambiguous in-text citations.  Must be keyed by unique resource identifier.
            $this->citeFormat->bibliographyIds[$row['resourceId']] = FALSE;
        }
        $this->citeFormat->processEndnoteBibliography($rows, $this->citeIds);

        return $rows;
    }
    /**
     * Process bibliography array into string for output -- used for in-text citations and appended bibliographies for footnotes
     *
     * @param array $bibliography
     *
     * @return string
     */
    public function printBibliography($bibliography)
    {
        foreach ($bibliography as $row) {
            // Do not add if cited resource type shouldn't be in the appended bibliography
            if (array_key_exists($row['resourceType'] . "_notInBibliography", $this->citeFormat->style)) {
                continue;
            }
            // If we're disambiguating citations by adding a letter after the year, we need to insert the yearLetter into $row before formatting the bibliography.
            if (isset($this->citeFormat->yearsDisambiguated) && $this->citeFormat->style['ambiguous'] &&
                array_key_exists($row['resourceId'], $this->citeFormat->yearsDisambiguated)) {
                // For WIKINDX, if type == book or book article and there exists both 'year1' and 'year2' in $row (entered as
                // publication year and reprint year respectively), need to make sure we have the later publication year
                $yearField = 'resourceyearYear1';
                if (($row['resourceType'] == 'book') || ($row['resourceType'] == 'book_article')) {
                    $year2 = $row['resourceyearYear2'];
                    if ($year2 && !$row['resourceyearYear1']) {
                        $yearField = 'resourceyearYear2';
                    } elseif ($year2 && $row['resourceyearYear1']) {
                        $yearField = 'resourceyearYear2';
                    }
                }
                $row[$yearField] = $this->citeFormat->yearsDisambiguated[$row['resourceId']];
            }
            $this->citeFormat->processIntextBibliography($row);
        }

        return $this->citeFormat->collateIntextBibliography();
    }
    /**
     * Parse the cite tag by extracting resource ID and any page numbers. Check ID is valid.
     *
     * PreText and postText can also be encoded: e.g. (see Grimshaw 2003; Boulanger 2004 for example)
     * [cite]23:34-35|see ` for example[/cite].  For multiple citations, only the first encountered preText and postText will be used to enclose the citations.
     *
     * @param int $matchIndex
     * @param string $tag
     *
     * @return int
     */
    public function parseCiteTag($matchIndex, $tag)
    {
        // When a user cut's 'n' pastes in HTML design mode, superfluous HTML tags (usually <style lang=xx></span>) are inserted.  Remove anything that looks like HTML
        $tag = preg_replace("/<.*?>/usi", "", $tag);
        $rawCitation = \UTF8\mb_explode("|", $tag);
        $idPart = \UTF8\mb_explode(":", $rawCitation[0]);
        $id = $idPart[0];
        $this->db->formatConditions(['resourceId' => $id]);
        $resultset = $this->db->select('resource', 'resourceId');
        if (!$this->db->numRows($resultset)) {
            // For an invalid citation ID, deal with any text that precedes it by either prepending to the next cite tag capture or prepending to $this->tailText.
            if (array_key_exists($matchIndex + 1, $this->matches[1]) && array_key_exists($matchIndex, $this->matches[1])) {
                $this->matches[1][$matchIndex + 1] =
                $this->matches[1][$matchIndex] . $this->matches[1][$matchIndex + 1];
            } elseif (array_key_exists($matchIndex, $this->matches[1])) {
                $this->tailText = $this->matches[1][$matchIndex] . $this->tailText;
            }
            // Ensure we don't pass this invalid ID in the citation engine.
            unset($this->matches[1][$matchIndex]);

            return FALSE;
        }
        ++$this->citeFormat->count;
        if (array_key_exists('1', $idPart)) {
            $pages = \UTF8\mb_explode("-", $idPart[1]);
            $pageStart = $pages[0];
            $pageEnd = array_key_exists('1', $pages) ? $pages[1] : FALSE;
        } else {
            $pageStart = $pageEnd = FALSE;
        }
        $this->citeFormat->formatPages($pageStart, $pageEnd);
        if (array_key_exists('1', $rawCitation)) {
            $text = \UTF8\mb_explode("`", $rawCitation[1]);
            $this->preText[] = $text[0];
            $this->postText[] = array_key_exists('1', $text) ? $text[1] : FALSE;
        } else {
            $this->preText[] = $this->postText[] = FALSE;
        }

        return $id;
    }
    /**
     * Accept a SQL result row of raw bibliographic data and process it.
     *
     * We build up the $citeFormat->item array with formatted parts from the raw $row
     *
     * @param array $row
     * @param int $id
     */
    public function process($row, $id)
    {
        // For WIKINDX, if type == book or book article and there exists both 'year1' and 'year2' in $row (entered as
        // publication year and reprint year respectively), then switch these around as 'year1' is
        // entered in the style template as 'originalPublicationYear' and 'year2' should be 'publicationYear'.
        if (($row['resourceType'] == 'book') || ($row['resourceType'] == 'book_article')) {
            $year2 = $row['resourceyearYear2'];
            if ($year2 && !$row['resourceyearYear1']) {
                $row['resourceyearYear1'] = $year2;
                unset($row['resourceyearYear2']);
            } elseif ($year2 && $row['resourceyearYear1']) {
                $row['resourceyearYear2'] = $row['resourceyearYear1'];
                $row['resourceyearYear1'] = $year2;
            }
        }
        $this->rowSingle = $row;
        unset($row);
        // Get creator names for resource
        $this->grabNames($id);
        // The title of the resource
        $this->createTitle();
        // URL of resource
        $this->createUrl();
        // Publication year of resource.  If no publication year, we create a dummy key entry so that CITEFORMAT can provide a replacement string if required by the style.
        if (!array_key_exists('resourceyearYear1', $this->rowSingle)) {
            $this->rowSingle['resourceyearYear1'] = FALSE;
        }
        $this->citeFormat->formatYear($this->rowSingle['resourceyearYear1']);
    }
    /**
     * callback for ordinals
     *
     * @param array $matches
     *
     * @return string
     */
    public function ordinals($matches)
    {
        if ($this->output == 'html') {
            return $matches[1] . "<sup>" . $matches[2] . "</sup>";
        } elseif ($this->output == 'rtf') {
            return $matches[1] . "{{\\up5 " . $matches[2] . "}}";
        } else {
            return $matches[1] . $matches[2];
        }
    }
    /**
     * Create the resource title
     */
    public function createTitle()
    {
        $pString = $this->rowSingle['resourceNoSort'] . ' ' . $this->rowSingle['resourceTitle'];
        if ($this->rowSingle['resourceSubtitle']) {
            $pString .= $this->citeFormat->style['titleSubtitleSeparator'] .
            $this->rowSingle['resourceSubtitle'];
        }
        // anything enclosed in {...} is to be left as is
        $this->citeFormat->formatTitle($pString, "{", "}");
        if ($this->rowSingle['resourceShortTitle']) {
            // anything enclosed in {...} is to be left as is
            $this->citeFormat->formatShortTitle($this->rowSingle['resourceShortTitle'], "{", "}");
        }
    }
    /**
     * Create preText and postText
     *
     * @param string $preText
     * @param string $postText
     */
    public function createPrePostText($preText, $postText)
    {
        if (!$preText && !$postText) { // empty field
            return;
        }
        $this->citeFormat->formatPrePostText($preText, $postText);
    }
    /**
     * get names from database for creator, editor, translator etc.
     *
     * @param int $citationId
     *
     * @return bool
     */
    public function grabNames($citationId)
    {
        $creators = [];
        $this->db->formatConditions(['resourcecreatorResourceId' => $citationId]);
        $this->db->formatConditionsOneField([1, 2], 'resourcecreatorRole');
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourcecreatorRole', TRUE, FALSE);
        $this->db->orderBy('resourcecreatorOrder', TRUE, FALSE);
        $resultSet = $this->db->select('resource_creator', ['resourcecreatorCreatorId', 'resourcecreatorRole']);
        while ($row = $this->db->fetchRow($resultSet)) {
            $creators[$row['resourcecreatorRole']][] = $row['resourcecreatorCreatorId'];
        }
        if (empty($creators)) {
            return FALSE;
        }
        if (array_key_exists(1, $creators)) { // edited book?
            $nameIds = $creators[1];
        } else {
            $nameIds = $creators[2];
        }
        foreach ($nameIds as $nameId) {
            $ids[] = $nameId;
        }
        $this->db->conditionSeparator = $this->db->or;
        $this->db->formatConditionsOneField($ids, 'creatorId');
        $this->db->conditionSeparator = $this->db->and; // reset
        $recordset = $this->db->select('creator', [['creatorSurname' => 'surname'],
            ['creatorFirstname' => 'firstname'],
            ['creatorInitials' => 'initials'], ['creatorPrefix' => 'prefix'], 'creatorId', ]);
        // Reorder $row so that creator order is correct and not that returned by SQL
        while ($row = $this->db->fetchRow($recordset)) {
            $rowSql[$row['creatorId']] = array_map([$this, "removeSlashes"], $row);
        }
        if (!isset($rowSql)) {
            return FALSE;
        }
        foreach ($nameIds as $id) {
            $rowTemp[] = $rowSql[$id];
        }
        $this->citeFormat->formatNames($rowTemp, $citationId);
    }
    /**
     * Create the URL
     *
     * @return bool
     */
    public function createUrl()
    {
        if (!$this->rowSingle['resourcetextUrls']) {
            return FALSE;
        }
        $urls = \URL\getUrls($this->rowSingle['resourcetextUrls']);
        // In $urls array, [0] index is primary URL
        $url = ($this->output == 'html') ? htmlspecialchars($urls[0]) : $urls[0];
        if ($this->output == 'html') {
            $url = \URL\reduceUrl($url, 80);
        }
        $this->citeFormat->formatUrl($url);
    }
    /**
     * Remove slashes
     *
     * @param string $element
     *
     * @return string
     */
    public function removeSlashes($element)
    {
        if ($this->output == 'rtf') {
            return str_replace('\\', '\\\\', stripslashes($element));
        } else {
            return stripslashes($element);
        }
    }
    /**
     * Initialize the CITESTYLE system
     */
    private function init()
    {
        // Must be initialised.
        $this->pageStart = $this->pageEnd = $this->preText = $this->postText = $this->citeIds = [];
        $this->citeFormat->wikindx = TRUE;
        // Get the bibliographic style.  These session variables are set in WIKINDX's BIBSTYLE.php - other systems will need similar code to load an XML style file.
        if ($this->rtfBibExport) {
            $this->setupStyle = $this->session->getVar("exportRtf_Style");
        } elseif ($this->output == 'rtf') {
            $this->setupStyle = $this->session->getVar("exportPaper_Style");
        } else {
            $this->setupStyle = GLOBALS::getUserVar("Style");
        }
        // Initialize citeFormat properties
        $this->citeFormat->output = $this->output;
        $this->citeFormat->rtfBibExport = $this->rtfBibExport;
        $this->citeFormat->initialise();
    }
}
