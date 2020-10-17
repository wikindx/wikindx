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
 *	PREVIEWCITE class for in-text citations.
 */
class adminstyle_previewcite
{
    private $vars;
    private $bibformat;
    private $citeformat;
    private $row;
    private $rowSingle;
    private $text;
    private $errors;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vars = GLOBALS::getVars();

        $this->errors = FACTORY_ERRORS::getInstance();
        $this->bibformat = FACTORY_BIBFORMAT::getInstance();
        $this->bibformat->initialise();
        $this->bibformat->wikindx = TRUE;
        $this->bibformat->preview = TRUE;
        $this->citeformat = FACTORY_CITEFORMAT::getInstance();
        $this->citeformat->initialise();
        $this->citeformat->wikindx = TRUE;
        $this->citeformat->preview = TRUE;
    }
    /**
     * display
     *
     * @return string
     */
    public function display()
    {
        $cite = json_decode(base64_decode($this->vars['cite']), TRUE);
        // Load the test data
        $this->getData();
        if ($this->loadCiteformat($cite) !== TRUE) {
            return FALSE;
        }

        return $this->processCite($this->text);
    }
    /**
     * function loadCiteformat()
     *
     * @param mixed $cite
     *
     * @return bool
     */
    private function loadCiteformat($cite)
    {
        $this->citeformat->wikindx = TRUE;
        if (!$cite['cite_template']) {
            return FALSE;
        }
        $this->citeformat->loadArrays();
        foreach ($cite as $key => $value) {
            // Convert javascript unicode e.g. %u2014 to HTML entities
            $value = preg_replace(
                "/%u(\\d+)/u",
                "&#x$1;",
                str_replace(
                    ['__WIKINDX__SPACE__', '__WIKINDX__LESSTHAN__', '__WIKINDX__GREATERTHAN__'],
                    [' ', '&lt;', '&gt;'],
                    $value
                )
            );
            $this->citeformat->style[str_replace("cite_", "", $key)] = $value;
        }
        $this->citeformat->style['citationStyle'] = FALSE; // Set to in-text citations.
        $this->citeformat->citationToArrayInTextStyle();

        return TRUE;
    }
    /**
     * Create the resource title
     */
    private function createTitleCite()
    {
        $pString = stripslashes($this->rowSingle['title']);
        if (isset($this->rowSingle['subtitle'])) {
            $pString .= $this->citeformat->style['titleSubtitleSeparator'] .
            stripslashes($this->rowSingle['subtitle']);
        }
        // anything enclosed in {...} is to be left as is
        $this->citeformat->formatTitle($pString, "{", "}");
        if ($this->rowSingle['shortTitle']) {
            // anything enclosed in {...} is to be left as is
            $this->citeformat->formatShortTitle($this->rowSingle['shortTitle'], "{", "}");
        }
    }
    /**
     * function processCite()
     *
     * Parse $this->text for citation tags and format accordingly.
     *
     * @param mixed $text
     *
     * @return string
     */
    private function processCite($text)
    {
        // Must be initialised.
        $this->pageStart = $this->pageEnd = $this->preText = $this->postText = $this->citeIds = [];
        // Parse $this->text
        // Capture any text after last [cite]...[/cite] tag
        $explode = \UTF8\mb_explode("]etic/[", \UTF8\mb_strrev($text), 2);
        $this->tailText = \UTF8\mb_strrev($explode[0]);
        $text = \UTF8\mb_strrev("]etic/[" . $explode[1]);
        preg_match_all("/(.*)\\s*\\[cite\\](.*)\\[\\/cite\\]/Uis", $text, $match);
        foreach ($match[1] as $value) {
            $this->matches[1][] = $value;
        }
        $this->citeformat->count = 0;
        foreach ($match[2] as $index => $value) {
            ++$this->citeformat->count;
            if ($id = $this->parseCiteTag($index, $value)) {
                $this->citeIds[] = $id;
            }
        }
        // If empty($this->citeIds), there are no citations to scan for (or user has entered invalid IDs) so return $text unchanged.
        if (empty($this->citeIds)) {
            return $text;
        }
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
        $this->citeformat->count = 0;
        $citeIndex = 0;
        while (!empty($this->matches[1])) {
            $this->citeformat->item = []; // must be reset each time.
            $id = $this->citeIds[$citeIndex];
            ++$citeIndex;
            ++$this->citeformat->count;
            $text = array_shift($this->matches[1]);
            $this->citeformat->items[$this->citeformat->count]['id'] = $id;
            //			$this->createPrePostText(array_shift($this->preText), array_shift($this->postText));
            // For each element of $bibliography, process title, creator names etc.
            if (array_key_exists($id, $this->row)) {
                $this->processCitations($this->row[$id], $id);
            }
            // $this->rowSingle is set in $this->processCitations().
            // 'type' is the type of resource (book, journal article etc.).  In WIKINDX, this is part of the row returned by SQL:  you may
            // need to set this manually if this is not the case for your system.  'type' is used in CITEFORMAT::prependAppend() to add any special strings to the citation within
            // the text (e.g. the XML style file might state that 'Personal communication: ' needs to be appended to any in-text citations for resources of type 'email'.
            // CITEFORMAT::prependAppend() will map 'type' against the $types array in STYLEMAP as used in BIBFORMAT.
            $this->citeformat->items[$this->citeformat->count]['type'] = $this->rowSingle['type'];
            $this->citeformat->items[$this->citeformat->count]['text'] = $text;
        }
        $pString = $this->citeformat->process() . $this->tailText;

        return $pString;
    }
    /**
     * Parse the cite tag by extracting resource ID and any page numbers. Check ID is valid
     * PreText and postText can also be encoded: e.g. (see Grimshaw 2003; Boulanger 2004 for example)
     * [cite]23:34-35|see ` for example[/cite].  For multiple citations, only the first encountered preText and postText will be used to enclose the citations.
     *
     * @param mixed $matchIndex
     * @param mixed $tag
     *
     * @return int
     */
    private function parseCiteTag($matchIndex, $tag)
    {
        // When a user cut's 'n' pastes in HTML design mode, superfluous HTML tags (usually <style lang=xx></span>) are inserted.  Remove anything that looks like HTML
        $tag = preg_replace("/<.*?>/usi", "", $tag);
        $rawCitation = \UTF8\mb_explode("|", $tag);
        $idPart = \UTF8\mb_explode(":", $rawCitation[0]);
        $id = $idPart[0];
        if (array_key_exists('1', $idPart)) {
            $pages = \UTF8\mb_explode("-", $idPart[1]);
            $pageStart = $pages[0];
            $pageEnd = array_key_exists('1', $pages) ? $pages[1] : FALSE;
        } else {
            $pageStart = $pageEnd = FALSE;
        }
        $this->citeformat->formatPages($pageStart, $pageEnd);
        if (array_key_exists('1', $rawCitation)) {
            $text = \UTF8\mb_explode("`", $rawCitation[1]);
            $this->preText[] = $text[0];
            $this->postText[] = array_key_exists('1', $text) ? $text[1] : FALSE;
        } else {
            $this->preText[] = $this->postText[] = FALSE;
        }

        return $id;
    }
    /** Accept a SQL result row of raw bibliographic data and process it.
     * We build up the $citeformat->item array with formatted parts from the raw $row
     *
     * @param mixed $row
     * @param mixed $id
     */
    private function processCitations($row, $id)
    {
        $this->rowSingle = $row;
        unset($row);
        $this->citeformat->formatNames($this->rowSingle['creator1'], $id); // Use 'creator1' array -- primary creators.
        // The title of the resource
        $this->createTitleCite();
        // Publication year of resource.  If no publication year, we create a dummy key entry so that CITEFORMAT can provide a replacement string if required by the style.
        if (!array_key_exists('year1', $this->rowSingle)) {
            $this->rowSingle['year1'] = FALSE;
        }
        $this->citeformat->formatYear(stripslashes($this->rowSingle['year1']));
    }
    /**
     * function getData()
     *
     * Load some test data.
     * $this->row is how BIBFORMAT expects bibliographic input to be formatted and passed to its classes.  It is
     * your responsibility to get your data in a format usable by OSBIB (bibtex-based databases should use STYLEMAPBIBTEX.php) -- see docs/ for details.
     */
    private function getData()
    {
        // First resource
        $this->row[33]['id'] = 33; // Unique ID for this resource
        $this->row[33]['type'] = 'book_article';	// The type of resource which must be one of the types in STYLEMAP.php
        $this->row[33]['title'] = '{WIKINDX}'; // Braces protect uppercase
        $this->row[33]['subtitle'] = 'Bibliographic & Quotations Manager';
        $this->row[33]['collectionTitle'] = 'Guide to Open Source Software';
        $this->row[33]['pageStart'] = '51';
        $this->row[33]['pageEnd'] = '59';
        $this->row[33]['shortTitle'] = 'WIK';
        /*
        * For creators (authors, editors, composers, inventors, performers, translators etc.), the initial key element here must be
        * one of 'creator1', 'creator2', 'creator3', 'creator4' or 'creator5' where 'creator1' is usually the primary creator.  These are mapped
        * to appropriate roles for the resource type in STYLEMAP.php.  Only 'creator1' is used in CITEFORMAT and it must have a unique 'id' key.
        * The second integer key element (keyed from 0) gives the descending order of printing the creator names for the particular creator type.
        */
        // Primary creator, first in list.
        $this->row[33]['creator1'][0]['surname'] = 'Aulery';
        $this->row[33]['creator1'][0]['firstname'] = 'Stéphane';
        $this->row[33]['creator1'][0]['initials'] = ''; // Full stops are added by the bibliographic style if required.
        $this->row[33]['creator1'][0]['prefix'] = ''; // 'de', 'von', 'della' etc.  Array element must be present.
        $this->row[33]['creator1'][0]['id'] = 4; // unique ID for this author (presumably the database table ID for this creator).
// Publication year
        $this->row[33]['year1'] = '2003';
        // Original publication year
        $this->row[33]['year2'] = '1999';
        // Publisher name
        $this->row[33]['publisherName'] = 'Tsetswana Books';
        // Publisher location
        $this->row[33]['publisherLocation'] = 'Gabarone';
        
        $this->row[100]['id'] = 100; // Unique ID for this resource
        $this->row[100]['type'] = 'book';	// The type of resource which must be one of the types in STYLEMAP.php
        $this->row[100]['title'] = 'Blah de Blah'; // Braces protect uppercase
        $this->row[100]['shortTitle'] = 'WIK'; // Braces protect uppercase
// Primary creator, first in list.
        $this->row[100]['creator1'][0]['surname'] = 'Aulery';
        $this->row[100]['creator1'][0]['firstname'] = 'Stéphane';
        $this->row[100]['creator1'][0]['initials'] = ''; // Full stops are added by the bibliographic style if required.
        $this->row[100]['creator1'][0]['prefix'] = ''; // 'de', 'von', 'della' etc.  Array element must be present.
        $this->row[100]['creator1'][0]['id'] = 4; // unique ID for this author (presumably the database table ID for this creator).
// Publication year
        $this->row[100]['year1'] = '2016';
        // Publisher name
        $this->row[100]['publisherName'] = 'Tsetswana Books';
        // Publisher location
        $this->row[100]['publisherLocation'] = 'Gabarone';

        // Second resource
        $this->row[1]['id'] = 1; // Unique ID for this resource
        $this->row[1]['type'] = 'book';	// The type of resource which must be one of the types in STYLEMAP.php
        $this->row[1]['title'] = '{OSBIB}'; // Braces protect uppercase
        $this->row[1]['subtitle'] = 'Open Source Bibliographic Formatting';
        $this->row[1]['edition'] = '3';
        $this->row[1]['shortTitle'] = 'OSB';
        /*
        * For creators (authors, editors, composers, inventors, performers, translators etc.), the initial key element here must be
        * one of 'creator1', 'creator2', 'creator3', 'creator4' or 'creator5' where 'creator1' is usually the primary creator.  These are mapped
        * to appropriate roles for the resource type in STYLEMAP.php.
        * The second integer key element (keyed from 0) gives the descending order of printing the creator names for the particular creator type.
        */
        // Primary creator, first in list.
        $this->row[1]['creator1'][0]['surname'] = 'Grimshaw';
        $this->row[1]['creator1'][0]['firstname'] = 'Mark';
        $this->row[1]['creator1'][0]['initials'] = 'N'; // Full stops are added by the bibliographic style if required.
        $this->row[1]['creator1'][0]['prefix'] = ''; // 'de', 'von', 'della' etc.  Array element must be present.
        $this->row[1]['creator1'][0]['id'] = 4; // unique ID for this author (same author as above)
// Primary creator, second in list.
        $this->row[1]['creator1'][1]['surname'] = 'Aulery';
        $this->row[1]['creator1'][1]['firstname'] = 'Stéphane';
        $this->row[1]['creator1'][1]['initials'] = '';
        $this->row[1]['creator1'][1]['prefix'] = '';
        $this->row[1]['creator1'][1]['id'] = 10; // unique ID for this author
        // Primary creator, third in list.
        $this->row[1]['creator1'][2]['surname'] = 'Gardey';
        $this->row[1]['creator1'][2]['firstname'] = 'Guillaume';
        $this->row[1]['creator1'][2]['initials'] = '';
        $this->row[1]['creator1'][2]['prefix'] = '';
        $this->row[1]['creator1'][2]['id'] = 24; // unique ID for this author
        // Second creator, first in list.  In STYLEMAP, 'creator2' for resource type 'book' is mapped to 'editor'
        $this->row[1]['creator1'][3]['surname'] = 'Boulanger';
        $this->row[1]['creator1'][3]['firstname'] = 'Christian';
        $this->row[1]['creator1'][3]['initials'] = '';
        $this->row[1]['creator1'][3]['prefix'] = '';
        $this->row[1]['creator1'][3]['id'] = 101; // unique ID for this author
        // Publication year
        $this->row[1]['year1'] = '';
        // Original publication year
        $this->row[1]['year2'] = '2004';
        // Publisher name
        $this->row[1]['publisherName'] = 'Botswana Press';
        // Publisher location
        $this->row[1]['publisherLocation'] = 'Selebi Phikwe';

        // Third resource
        $this->row[44]['id'] = 44; // Unique ID for this resource
        $this->row[44]['type'] = 'book';	// The type of resource which must be one of the types in STYLEMAP.php
        $this->row[44]['title'] = 'Bibliophile'; // Braces protect uppercase
        $this->row[44]['shortTitle'] = 'BIBLIO';
        /*
        * For creators (authors, editors, composers, inventors, performers, translators etc.), the initial key element here must be
        * one of 'creator1', 'creator2', 'creator3', 'creator4' or 'creator5' where 'creator1' is usually the primary creator.  These are mapped
        * to appropriate roles for the resource type in STYLEMAP.php.  Only 'creator1' is used in CITEFORMAT and it must have a unique 'id' key.
        * The second integer key element (keyed from 0) gives the descending order of printing the creator names for the particular creator type.
        */
        // Primary creator, first in list.
        $this->row[44]['creator1'][0]['surname'] = 'Grimshaw';
        $this->row[44]['creator1'][0]['firstname'] = 'Mark';
        $this->row[44]['creator1'][0]['initials'] = 'N'; // Full stops are added by the bibliographic style if required.
        $this->row[44]['creator1'][0]['prefix'] = ''; // 'de', 'von', 'della' etc.  Array element must be present.
        $this->row[44]['creator1'][0]['id'] = 4; // unique ID for this author (presumably the database table ID for this creator).
// Publication year
        $this->row[44]['year1'] = '2003';
        // Publisher name
        $this->row[44]['publisherName'] = 'Marula Publishers';
        // Publisher location
        $this->row[44]['publisherLocation'] = 'Gabarone';
        

        // Third resource
        $this->row[45]['id'] = 45; // Unique ID for this resource
        $this->row[45]['type'] = 'book';	// The type of resource which must be one of the types in STYLEMAP.php
        $this->row[45]['title'] = 'My Life as a Nun'; // Braces protect uppercase
        $this->row[45]['shortTitle'] = 'NUN';
        // Primary creator, first in list.
        $this->row[45]['creator1'][0]['surname'] = 'Grimshaw-Aagaard';
        $this->row[45]['creator1'][0]['firstname'] = 'Søsserr';
        $this->row[45]['creator1'][0]['initials'] = ''; // Full stops are added by the bibliographic style if required.
        $this->row[45]['creator1'][0]['prefix'] = ''; // 'de', 'von', 'della' etc.  Array element must be present.
        $this->row[45]['creator1'][0]['id'] = 45; // unique ID for this author (presumably the database table ID for this creator).
// Publication year
        $this->row[45]['year1'] = '2019';
        // Publisher name
        $this->row[45]['publisherName'] = 'Hot off the Press';
        // Publisher location
        $this->row[45]['publisherLocation'] = 'København';

        // Some text input with citation markup relating to the ID in $this->row above.

        $this->text = "It has long been said that \"blah blah blah\" [cite]45:52-53[/cite].";
        $this->text .= "  Aulery also states that \"blah blah blah\" [cite]33:101[/cite].";
        $this->text .= "  A number of writers agree with this [cite]44[/cite][cite]1[/cite].";
        $this->text .= "  However, this same author in the same book later states:  \"Blåh blåh blåh\" [cite]33:171[/cite].";
        $this->text .= "  Such sentiments are further contested when Grimshaw-Aagaard says \"bluh bluh bluh\" [cite]45:302[/cite].";
        $newPara = "But, in this new paragraph, the author referred to above now writes \"Bleh bleh bleh\" [cite]100:666[/cite] and this proves to be a contradiction of his previously oft-quoted sentiment that \"blah blah blah\" [cite]33:52-53[/cite].";
        $this->text .= HTML\p($newPara);
        $this->text = HTML\p($this->text);
    }
}
