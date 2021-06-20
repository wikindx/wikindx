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
 * Format a bibliographic resource for output.
 *
 * @package wikindx\core\bibcitation
 */
class BIBFORMAT
{
    /** Preview. */
    public $preview = FALSE;
    /**
     * Some styles require different templates and formatting of creator names for a citation in a footnote as opposed to a full bibliography.
     *
     * Setting this to TRUE (set
     * externally in CITEFORMAT) loads a different set of templates and settings for footnotes.  The default FALSE is for full bibliography.
     */
    public $footnotePages = FALSE;
    /** boolean */
    public $footnoteStyle = FALSE;
    /** boolean */
    public $citationFootnote = FALSE;
    /**
     * Output medium: 'html', 'rtf', or 'plain'
     */
    public $output = 'html';
    /** boolean */
    public $wikindx = FALSE;
    /** string */
    public $type;
    /**
     * Highlight preg pattern and CSS class for HTML display
     */
    public $patterns = FALSE;
    /** boolean */
    public $patternHighlight = FALSE;
    /** array */
    public $styleMap;
    /** array */
    public $customTypes = [];
    /** Path */
    private $dir = '';
    /** Bibtex */
    private $bibtex = FALSE;
    /** Path */
    private $bibtexParsePath;
    /** Previous creator */
    private $previousCreator = '';
    /**
     * Switch editor and author positions in the style definition for a book in which there are only editors
     */
    private $editorSwitch = FALSE;
    /** Boolean */
    private $dateMonthNoDay = FALSE;
    /** boolean */
    private $footnoteType = FALSE;
    /** boolean */
    private $partialDone = FALSE;
    /** int */
    private $countRecords = 0;
    /** array */
    private $customFields = [];

    /**
     * BIBFORMAT
     *
     * @param string $output 'html', plain', 'rtf'. Default is 'html'
     */
    public function __construct($output = 'html')
    {
        $this->output = $output;
        if (!$this->preview)
        { // Not javascript preview
            if (!$this->dir)
            {
                $this->dir = realpath(__DIR__) . DIRECTORY_SEPARATOR;
            }
            else
            {
                if (mb_substr($this->dir, mb_strlen($this->dir) - 1, 1) != DIRECTORY_SEPARATOR)
                {
                    $this->dir .= DIRECTORY_SEPARATOR;
                }
            }
            $this->bibtexParsePath = $this->dir . "bibtexParse";
        }
        else
        { // preview
            $this->dir = '';
        }
        if ($this->bibtex)
        {
            include_once($this->dir . "STYLEMAPBIBTEX.php");
            $this->styleMap = new STYLEMAPBIBTEX();
        }
        else
        {
            $db = FACTORY_DB::getInstance();
            include_once($this->dir . "STYLEMAP.php");
            $this->styleMap = new STYLEMAP();
            // Grab any custom fields
            $recordset = $db->select('custom', ['customId', 'customLabel']);
            while ($row = $db->fetchRow($recordset))
            {
                $this->customFields['custom_' . $row['customId']] = $row['customId'];
            }
            if (!empty($this->customFields))
            {
                foreach ($this->styleMap as $type => $typeArray)
                {
                    foreach ($this->customFields as $key => $value)
                    {
                        $this->styleMap->{$type}[$key] = $value;
                    }
                }
            }
        }
    }
    /**
     * Initialise bibformat system
     */
    public function initialise()
    {
        include_once($this->dir . "EXPORTFILTER.php");
        $this->export = new EXPORTFILTER($this, $this->output);
    }
    /**
     * Transform the raw data from the XML file into usable arrays and load some data arrays
     *
     * @param array $common Array of global formatting data
     * @param array $types Array of style definitions for each resource type
     * @param array $footnote Array of style definitions for footnote creators
     */
    public function getStyle($common, $types, $footnote)
    {
        $this->style = $common;
        $this->fallback = $types['fallback'];
        unset($types['fallback']);
        $this->backup = $types;
        foreach ($types as $name => $value)
        {
            if (!array_key_exists($name, $this->customTypes))
            {
                foreach ($this->customFields as $cKey => $cValue)
                {
                    if (array_key_exists($cKey, $value))
                    {
                        $this->customTypes[$name] = TRUE;
                    }
                }
            }
            if (array_key_exists($name, $footnote) && $footnote[$name])
            {
                $footnoteType = 'footnote_' . $name;
                foreach ($footnote[$name] as $rName => $rValue)
                {
                    if (($rName == 'independent') && is_array($rValue))
                    {
                        $array = [];
                        foreach ($rValue as $iName => $iValue)
                        {
                            $split = \UTF8\mb_explode('_', $iName);
                            $array[$split[1]] = $iValue;
                        }
                        $this->{$footnoteType}['independent'] = $array;
                    }
                    else
                    {
                        $this->{$footnoteType}[$rName] = $rValue;
                    }
                }
            }
            $this->$name = $value;
            $typeTemplateSet = $name . 'TemplateSet';
            if (!empty($this->$name) && (count($this->$name) > 3))
            {
                $this->$typeTemplateSet = TRUE;
            }
            else
            {
                $this->$typeTemplateSet = FALSE;
            }
        }
        // Set fallbacks
        foreach ($types as $name => $value)
        {
            $typeTemplateSet = $name . 'TemplateSet';
            if (array_key_exists($name, $this->fallback))
            {
                $footnoteType = 'footnote_' . $name;
                if (array_key_exists($name, $footnote) && !$footnote[$name])
                {
                    $this->{$footnoteType} = $footnote[$this->fallback[$name]];
                }
            }
            if (array_key_exists('fallbackstyle', $value) && $this->$typeTemplateSet)
            {
                $this->fallback[$name] = $value['fallbackstyle'];
            }
        }
        /**
         * Load localizations etc.
         */
        $this->loadArrays();
    }
    /**
     * Perform pre-processing on the raw SQL array
     *
     * @param string $type The resource type
     * @param array $row Associate array of raw SQL data
     *
     * @return array Processed row of raw SQL data
     */
    public function preProcess($type, $row)
    {
        /**
         * Ensure that $this->item is empty for each resource!!!!!!!!!!
         */
        $this->item = $this->footnoteTypeArray = [];
        // Map this system's resource type to OSBib's resource type
        $this->type = array_search($type, $this->styleMap->types);
        $typeTemplateSet = $type . 'TemplateSet';
        if ($this->bibtex)
        {
            if (array_key_exists('author', $row))
            {
                $row['creator1'] = $row['author'];
                unset($row['author']);
            }
            if (array_key_exists('editor', $row))
            {
                $row['creator2'] = $row['editor'];
                unset($row['editor']);
            }
        }
        /**
         * Set any author/editor re-ordering for book and book_article type.
         */
        if (!$this->preview && (($type == 'book') || ($type == 'book_article')) &&
            $row['creator2'] && !$row['creator1'] && $this->style['editorSwitch'] &&
            array_key_exists('author', $this->$type))
        {
            $row['creator1'] = $row['creator2'];
            $row['creator2'] = FALSE;
            $editorArray = $this->style['editorSwitchIfYes'];
            if (!empty($editorArray) && array_key_exists('editor', $editorArray))
            {
                $this->{$type}['author'] = $editorArray['editor'];
                unset($this->{$type}['editor']);
                $this->editorSwitch = TRUE;
            }
        }
        if ($this->style['dateMonthNoDay'] && array_key_exists('date', $this->styleMap->$type) &&
            array_key_exists('dateMonthNoDayString', $this->style) && $this->style['dateMonthNoDayString'])
        {
            $this->dateArray = $this->style['dateMonthNoDayString'];
            $this->dateMonthNoDay = TRUE;
        }
        /**
         * If $row comes in in BibTeX format, process and add items to $this->item
         */
        if ($this->bibtex)
        {
            list($type, $row) = $this->preProcessBibtex($row, $this->type);
        }
        /**
         * Set to catch-all generic style.  For all keys except named database fields, creator1 and year1,
         * we only print if the value in $this->styleMap matches the value in
         * $this->styleMap->generic for each key.
         */
        if ($this->citationFootnote)
        { // using footnote template
            $footnoteType = 'footnote_' . $type;
            if (isset($this->$footnoteType))
            { // footnote template for this resource exists
                $this->footnoteType = $footnoteType;
                $this->footnoteTypeArray[$type] = $footnoteType;
            }
            else
            {
                $footnoteType = 'footnote_' . $this->fallback[$type];
                if (isset($this->$footnoteType))
                { // fallback footnote template exists
                    $this->footnoteType = $footnoteType;
                    $this->footnoteTypeArray[$type] = $footnoteType;
                }
                elseif (!$this->$typeTemplateSet)
                { // use fallback bibliography template
                    $fallback = $this->fallback[$type];
                    $this->footnoteTypeArray[$type] = $fallback;
                    $type = $fallback;
                }
                // else, we're using the bibliography template for this resource type
                else
                {
                    $this->footnoteTypeArray[$type] = $type;
                }
            }
        }
        else
        {
            if (!$this->{$typeTemplateSet})
            {
                $fallback = $this->fallback[$type];
                $type = $fallback;
            }
        }
        $this->type = $type;
        /**
         * Add BibTeX entry to $this->item
         */
        if ($this->bibtex)
        {
            $this->addAllOtherItems($row);
        }
        $this->countRecords++;

        return $row;
    }
    /**
     * Map the $item array against the style array ($this->$type) for this resource type and produce a string ready to be
     * formatted for bold, italics etc.
     *
     * @param mixed $template If called from CITEFORMAT, this is the array of template elements. Default is FALSE.
     *
     * @return string For printing to the output medium.
     */
    public function map($template = FALSE)
    {
        $type = $pluralType = $this->type;
        // Check for partial templates
        if (!$this->footnoteType)
        {
            $this->checkPartial($type);
        }
        else
        {
            $type = $this->footnoteType;
            $this->footnoteType = FALSE;
        }
        $this->findAlternates($this->$type);
        $ultimate = $preliminary = '';
        $index = 0;
        $previousFieldExists = $nextFieldExists = TRUE;
        if (array_key_exists('independent', $this->$type))
        {
            $independent = $this->{$type}['independent'];
        }
        /**
         * For dependency on next field, we must grab array keys of $this->$type, shift the first element then, in the loop,
         * check each element exists in $item.  If it doesn't, $nextFieldExists is set to FALSE
         */
        $checkPost = array_keys($this->$type);
        array_shift($checkPost);
        $lastFieldKey = FALSE;
        // Add or replace pages field if this process is called from CITEFORMAT for footnotes where $this->footnotePages are the formatted citation pages.
        if ($this->footnotePages)
        {
            $this->item['pages'] = $this->footnotePages;
        }

        $arrayType = $this->$type;

        foreach ($arrayType as $key => $value)
        {
            if (($key == 'partial') || ($key == 'partialReplace'))
            {
                continue;
            }
            if ($key == 'ultimate')
            {
                $ultimate = $value;

                continue;
            }
            if ($key == 'preliminaryText')
            {
                $preliminary = $value;

                continue;
            }
            if (!array_key_exists($key, $this->item) || !$this->item[$key])
            {
                $index++;
                array_shift($checkPost);
                $previousFieldExists = FALSE;

                continue;
            }
            $checkPostShift = array_shift($checkPost);
            if (!array_key_exists($checkPostShift, $this->item) || !$this->item[$checkPostShift])
            {
                $nextFieldExists = FALSE;
            }
            $pre = array_key_exists('pre', $value) ? $value['pre'] : '';
            $post = array_key_exists('post', $value) ? $value['post'] : '';
            /**
             * Deal with __DEPENDENT_ON_PREVIOUS_FIELD__ for characters dependent on previous field's existence and
             * __DEPENDENT_ON_NEXT_FIELD__ for characters dependent on the next field's existence
             */
            if ($previousFieldExists && array_key_exists('dependentPre', $value))
            {
                $pre = preg_replace("/__DEPENDENT_ON_PREVIOUS_FIELD__/u", $value['dependentPre'], $pre);
            }
            elseif (array_key_exists('dependentPreAlternative', $value))
            {
                $pre = preg_replace("/__DEPENDENT_ON_PREVIOUS_FIELD__/u", $value['dependentPreAlternative'], $pre);
            }
            else
            {
                $pre = preg_replace("/__DEPENDENT_ON_PREVIOUS_FIELD__/u", '', $pre);
            }

            if ($nextFieldExists && array_key_exists('dependentPost', $value))
            {
                $post = str_replace("__DEPENDENT_ON_NEXT_FIELD__", $value['dependentPost'], $post);
            }
            elseif (array_key_exists('dependentPostAlternative', $value))
            {
                $post = preg_replace("/__DEPENDENT_ON_NEXT_FIELD__/u", $value['dependentPostAlternative'], $post);
            }
            else
            {
                $post = preg_replace("/__DEPENDENT_ON_NEXT_FIELD__/u", '', $post);
            }
            /**
             * Deal with __SINGULAR_PLURAL__ for creator lists and pages
             */
            if ($styleKey = array_search($key, $this->styleMap->$pluralType))
            {
                $pluralKey = $styleKey . "_plural";
            }
            // For use with generic footnote templates which uses generic 'creator' field
            else
            {
                $pluralKey = "creator_plural";
            }
            if (isset($this->$pluralKey) && $this->$pluralKey)
            { // plural alternative for this key
                if (array_key_exists('plural', $value))
                {
                    $pre = preg_replace("/__SINGULAR_PLURAL__/u", $value['plural'], $pre);
                    $post = preg_replace("/__SINGULAR_PLURAL__/u", $value['plural'], $post);
                }
            }
            elseif (isset($this->$pluralKey))
            { // singular alternative for this key
                if (array_key_exists('singular', $value))
                {
                    $pre = preg_replace("/__SINGULAR_PLURAL__/u", $value['singular'], $pre);
                    $post = preg_replace("/__SINGULAR_PLURAL__/u", $value['singular'], $post);
                }
            }
            // Deal with en dash characters in pages
            if ($key == 'pages')
            {
                $this->item[$key] = $this->export->format($this->item[$key]);
            }
            /**
             * Strip backticks used in template
             */
            $pre = str_replace("`", '', $pre);
            $post = str_replace("`", '', $post);
            /**
             * Make sure we don't have multiple punctuation characters after a field
             */
            $lastPre = mb_substr($post, -1);
            $firstItem = mb_substr($this->item[$key], 0, 1);
            if ($firstItem === $lastPre)
            {
                $this->item[$key] = mb_substr($this->item[$key], 1);
            }
            // Match last character of this field with $post
            if ($post && preg_match("/[.,;:?!]$/u", $this->item[$key]) &&
                preg_match("/^(\\[.*?[\\]]+)*([.,;:?!])|^([.,;:?!])/u", $post, $capture, PREG_OFFSET_CAPTURE))
            {
                // There is punctuation in post either immediately following BBCode formatting or at the start of the string.
                // The offset for the punctuation character in $post is given at $capture[2][1]
                $post = \UTF8\mb_substr_replace($post, '', $capture[2][1], 1);
            }
            // Match $itemArray[$lastFieldKey] with $pre
            if (($lastFieldKey !== FALSE) && $pre && preg_match("/^[.,;:?!]/u", $pre) &&
                preg_match(
                    "/([.,;:?!])(\\[.*?[\\]]+)*$|([.,;:?!])$/u",
                    $itemArray[$lastFieldKey],
                    $capture,
                    PREG_OFFSET_CAPTURE
                ))
            {
                // There is punctuation in post either immediately following BBCode formatting or at the start of the string.
                $pre = \UTF8\mb_substr_replace($pre, '', 0, 1);
            }
            if ($this->item[$key])
            {
                $itemArray[$index] = $pre . $this->item[$key] . $post;
                $lastFieldKey = $index;
            }
            $previousFieldExists = $nextFieldExists = TRUE;
            $index++;
        }

        unset($arrayType);
        /**
         * Check for independent characters.  These (should) come in pairs.
         */
        if (isset($independent))
        {
            $independentKeys = array_keys($independent);
            while ($independent)
            {
                $preAlternative = $postAlternative = FALSE;
                $startFound = $endFound = FALSE;
                $pre = array_shift($independent);
                $post = array_shift($independent);
                if (preg_match("/%(.*)%(.*)%|%(.*)%/Uu", $pre, $dependent))
                {
                    if (count($dependent) == 4)
                    {
                        $pre = $dependent[3];
                    }
                    else
                    {
                        $pre = $dependent[1];
                        $preAlternative = $dependent[2];
                    }
                }
                if (preg_match("/%(.*)%(.*)%|%(.*)%/Uu", $post, $dependent))
                {
                    if (count($dependent) == 4)
                    {
                        $post = $dependent[3];
                    }
                    else
                    {
                        $post = $dependent[1];
                        $postAlternative = $dependent[2];
                    }
                }
                /**
                 * Strip backticks used in template
                 */
                $preAlternative = str_replace("`", '', $preAlternative);
                $postAlternative = str_replace("`", '', $postAlternative);
                $firstKey = str_replace('independent_', '', array_shift($independentKeys));
                $secondKey = str_replace('independent_', '', array_shift($independentKeys));
                for ($index = $firstKey; $index <= $secondKey; $index++)
                {
                    if (array_key_exists($index, $itemArray))
                    {
                        $startFound = $index;

                        break;
                    }
                }
                for ($index = $secondKey; $index >= $firstKey; $index--)
                {
                    if (array_key_exists($index, $itemArray))
                    {
                        $endFound = $index;

                        break;
                    }
                }
                if (($startFound !== FALSE) && ($endFound !== FALSE))
                { // intervening fields found
                    $itemArray[$startFound] = $pre . $itemArray[$startFound];
                    $itemArray[$endFound] = $itemArray[$endFound] . $post;
                }
                else
                { // intervening fields not found - do we have an alternative?
                    if (array_key_exists(intval($firstKey) - 1, $itemArray) && $preAlternative)
                    {
                        $itemArray[intval($firstKey) - 1] .= $preAlternative;
                    }
                    if (array_key_exists(intval($secondKey) + 1, $itemArray) && $postAlternative)
                    {
                        $itemArray[intval($secondKey) + 1] = $postAlternative . $itemArray[intval($secondKey) + 1];
                    }
                }
            }
        }

        // Empty titles should not occur but, in case, this catches errors
        if (!isset($itemArray))
        {
            $itemArray = [];
        }
        $pString = implode('', $itemArray);
        /**
         * if last character is punctuation (which it may be with missing fields etc.), and $ultimate is also
         * punctuation, set $ultimate to empty string.
         */
        if (isset($ultimate) && $ultimate)
        {
            $pString = trim($pString);
            /**
             * Don't do ';' in case last element is URL with &gt; ...!
             */
            if (preg_match("/^[.,:?!]/u", $ultimate) &&
                preg_match("/([.,:?!])(\\[.*?[\\]]+)*$|([.,:?!])$/u", $pString))
            {
                $ultimate = '';
            }
        }
        // If $this->editorSwitch or $this->dateMonthNoDay, we have altered $this->$bibformat->$type so need to reload styles
        if (!$this->preview && ($this->editorSwitch || $this->dateMonthNoDay || $this->partialDone))
        {
            $this->restoreTypes();
            $this->editorSwitch = $this->dateMonthNoDay = FALSE;
        }

        return $this->export->format($preliminary . trim($pString) . $ultimate);
    }
    /**
     * Format creator name lists (authors, editors, etc.)
     *
     * @param array $creators Multi-associative array of creator names e.g. this array might be of
     *                        the primary authors:
     *                        <pre>
     *                        array([0] => array(['surname'] => 'Grimshaw', ['firstname'] => Mark, ['initials'] => 'N', ['prefix'] => ),
     *                        [1] => array(['surname'] => 'Witt', ['firstname'] => Jan, ['initials'] => , ['prefix'] => 'de'))
     *                        </pre>
     * @param string $nameType 'creator1', 'creator2' etc.
     * @param bool $shortFootnote If TRUE, this is being used for just the primary creator names in a footnote style citation using Ibid, Idem, op cit. etc.
     *                            Default is FALSE.
     *
     * @return string Optional if $nameType == 'citation': formatted string of all creator names in the input array.
     */
    public function formatNames($creators, $nameType, $shortFootnote = FALSE)
    {
        //		$style = $this->citationFootnote ? $this->footnoteStyle : $this->style;
        $style = $this->style;
        $first = TRUE;
        /**
         * Citation creators
         */
        if ($nameType == 'citation')
        {
            $limit = 'creatorListLimit';
            $moreThan = 'creatorListMore';
            $abbreviation = 'creatorListAbbreviation';
            $initialsStyle = 'creatorInitials';
            $firstNameInitial = 'creatorFirstName';
            $delimitTwo = 'twoCreatorsSep';
            $delimitFirstBetween = 'creatorSepFirstBetween';
            $delimitNextBetween = 'creatorSepNextBetween';
            $delimitLast = 'creatorSepNextLast';
            $uppercase = 'creatorUppercase';
            $italics = 'creatorListAbbreviationItalic';
        }
        /**
         * Primary creator
         */
        elseif ($nameType == 'creator1')
        {
            $limit = 'primaryCreatorListLimit';
            $moreThan = 'primaryCreatorListMore';
            $abbreviation = 'primaryCreatorListAbbreviation';
            $initialsStyle = 'primaryCreatorInitials';
            $firstNameInitial = 'primaryCreatorFirstName';
            $delimitTwo = 'primaryTwoCreatorsSep';
            $delimitFirstBetween = 'primaryCreatorSepFirstBetween';
            $delimitNextBetween = 'primaryCreatorSepNextBetween';
            $delimitLast = 'primaryCreatorSepNextLast';
            $uppercase = 'primaryCreatorUppercase';
            $italics = 'primaryCreatorListAbbreviationItalic';
        }
        else
        {
            $limit = 'otherCreatorListLimit';
            $moreThan = 'otherCreatorListMore';
            $abbreviation = 'otherCreatorListAbbreviation';
            $initialsStyle = 'otherCreatorInitials';
            $firstNameInitial = 'otherCreatorFirstName';
            $delimitTwo = 'otherTwoCreatorsSep';
            $delimitFirstBetween = 'otherCreatorSepFirstBetween';
            $delimitNextBetween = 'otherCreatorSepNextBetween';
            $delimitLast = 'otherCreatorSepNextLast';
            $uppercase = 'otherCreatorUppercase';
            $italics = 'otherCreatorListAbbreviationItalic';
        }
        $type = $this->type;
        /**
         * Set default plural behaviour for creator lists
         */
        // For use with generic footnote templates which uses generic 'creator' field
        if ($this->citationFootnote && ($nameType == 'creator1') &&
        ($this->styleMap->{$type}[$nameType] != 'creator'))
        {
            $pluralKey = "creator_plural";
        }
        else
        {
            $pluralKey = $nameType . "_plural";
        }
        $this->$pluralKey = FALSE;
        $firstInList = TRUE;
        $rewriteCreatorBeforeDone = $rewriteCreatorFinal = FALSE;

        foreach ($creators as $creator)
        {
            if ($first)
            {
                if ($nameType == 'citation')
                {
                    $nameStyle = 'creatorStyle';
                }
                elseif ($nameType == 'creator1')
                {
                    $nameStyle = 'primaryCreatorFirstStyle';
                }
                else
                {
                    $nameStyle = 'otherCreatorFirstStyle';
                }
            }
            else
            {
                if ($nameType == 'citation')
                {
                    $nameStyle = 'creatorOtherStyle';
                }
                elseif ($nameType == 'creator1')
                {
                    $nameStyle = 'primaryCreatorOtherStyle';
                }
                else
                {
                    $nameStyle = 'otherCreatorOtherStyle';
                }
            }

            if (array_key_exists('creatorId', $creator))
            {
                $creatorIds[] = $creator['creatorId'];
            }
            $firstName = trim($this->checkInitials(
                $creator,
                $style[$initialsStyle],
                $style[$firstNameInitial]
            ));
            $prefix = $creator['prefix'] ? trim($creator['prefix']) . ' ' : '';

            switch ($style[$nameStyle]) {
                case 0: // Joe Bloggs
                    $nameString = $firstName . ' ' .
                        $prefix .
                        $creator['surname'];

                break;
                case 1: // Bloggs, Joe
                    $prefixDelimit = $firstName ? ', ' : '';
                    $nameString =
                        $creator['prefix'] . ' ' .
                        $creator['surname'] . $prefixDelimit .
                        $firstName;

                break;
                case 2: // Bloggs Joe
                    $nameString =
                        $creator['prefix'] . ' ' .
                        $creator['surname'] . ' ' .
                        $firstName;

                break;
                default: // Last name only
                    $nameString =
                        $creator['prefix'] . ' ' .
                        $creator['surname'];

                break;
            }

            if (isset($style[$uppercase]))
            {
                $nameString = mb_strtoupper($nameString);
            }
            $nameString = trim($nameString);
            if ($firstInList)
            {
                $rewriteCreatorField = $nameType . "_firstString";
                $rewriteCreatorFieldBefore = $nameType . "_firstString_before";
            }
            else
            {
                $rewriteCreatorField = $nameType . "_remainderString";
                $rewriteCreatorFieldBefore = $nameType . "_remainderString_before";
                $rewriteCreatorFieldEach = $nameType . "_remainderString_each";
            }
            if (array_key_exists($rewriteCreatorField, $this->$type))
            {
                if ($firstInList)
                {
                    if (array_key_exists($rewriteCreatorFieldBefore, $this->$type))
                    {
                        $nameString = $this->{$type}[$rewriteCreatorField] . $nameString;
                    }
                    else
                    {
                        $nameString .= $this->{$type}[$rewriteCreatorField];
                    }
                    $firstInList = FALSE;
                }
                elseif (array_key_exists($rewriteCreatorFieldEach, $this->$type))
                {
                    if (array_key_exists($rewriteCreatorFieldBefore, $this->$type))
                    {
                        $nameString = $this->{$type}[$rewriteCreatorField] . $nameString;
                    }
                    else
                    {
                        $nameString .= $this->{$type}[$rewriteCreatorField];
                    }
                }
                elseif (!$rewriteCreatorBeforeDone)
                {
                    if (array_key_exists($rewriteCreatorFieldBefore, $this->$type))
                    {
                        $nameString = $this->{$type}[$rewriteCreatorField] . $nameString;
                        $rewriteCreatorBeforeDone = TRUE;
                    }
                    elseif (!array_key_exists($rewriteCreatorFieldEach, $this->$type))
                    {
                        $rewriteCreatorFinal = $this->{$type}[$rewriteCreatorField];
                    }
                }
            }
            $cArray[] = $nameString;
            $first = FALSE;
        }

        unset($creators);
        /**
         * Keep only some elements in array if we've exceeded $moreThan
         */
        $etAl = FALSE;
        if ($style[$limit] && (count($cArray) >= $style[$moreThan]))
        {
            array_splice($cArray, $style[$limit]);
            if (isset($style[$italics]))
            {
                $etAl = "[i]" . $style[$abbreviation] . "[/i]";
            }
            else
            {
                $etAl = $style[$abbreviation];
            }
        }
        /**
         * add delimiters
         */
        if (count($cArray) > 1)
        {
            if (count($cArray) == 2)
            {
                $cArray[0] .= $style[$delimitTwo];
            }
            else
            {
                for ($index = 0; $index < (count($cArray) - 2); $index++)
                {
                    if (!$index)
                    {
                        $cArray[$index] .= $style[$delimitFirstBetween];
                    }
                    else
                    {
                        $cArray[$index] .= $style[$delimitNextBetween];
                    }
                }
                $cArray[count($cArray) - 2] .= $style[$delimitLast];
            }
        }
        /**
         * If sizeof of $cArray > 1 or $etAl != FALSE, set this $nameType_plural to TRUE
         */
        if ((count($cArray) > 1) || $etAl)
        {
            //			$pluralKey = $nameType . "_plural";
            $this->$pluralKey = TRUE;
        }
        /**
         * Finally flatten array
         */
        if ($etAl)
        {
            $pString = implode('', $cArray) . $etAl;
        }
        else
        {
            $pString = implode('', $cArray);
        }
        if ($rewriteCreatorFinal)
        {
            $pString .= $rewriteCreatorFinal;
        }
        /**
         * Check for repeating primary creator list in subsequent bibliographic item.
         */
        if (($nameType == 'creator1'))
        {
            $tempString = $pString;
            if (($style['primaryCreatorRepeat'] == 2) && ($this->previousCreator == $pString))
            {
                $pString = $style['primaryCreatorRepeatString'];
            }
            elseif (($style['primaryCreatorRepeat'] == 1) && ($this->previousCreator == $pString))
            {
                $pString = ''; // don't print creator list
            }
            $this->previousCreator = $tempString;
        }
        if ($shortFootnote)
        {
            return [$pString, $creatorIds];
        }
        // For use with generic footnote templates, we must also place 'creator1' string (if not called 'creator') into the 'creator' slot
        if (($nameType == 'creator1') && ($this->styleMap->{$type}[$nameType] != 'creator'))
        {
            $this->item['creator'] = $pString;
        }
        $this->item[$this->styleMap->{$type}[$nameType]] = $pString;
    }
    /**
     * Handle initials.
     *
     * @see formatNames()
     *
     * @param array $creator Associative array of creator name e.g.
     *                       <pre>
     *                       array(['surname'] => 'Grimshaw', ['firstname'] => Mark, ['initials'] => 'M N G', ['prefix'] => ))
     *                       </pre>
     *                       Initials must be space-delimited.
     * @param int $initialsStyle
     * @param bool $firstNameInitial
     *
     * @return string Formatted string of initials.
     */
    public function checkInitials(&$creator, $initialsStyle, $firstNameInitial)
    {
        /**
         * Format firstname
         */
        if ($creator['firstname'] && !$firstNameInitial)
        { // Full name
            $firstName = $creator['firstname'];
        }
        elseif ($creator['firstname'])
        { // Initial only of first name.  'firstname' field may actually have several 'firstnames'
            $fn = \UTF8\mb_explode(" ", $creator['firstname']);
            $firstTime = TRUE;
            foreach ($fn as $name)
            {
                // May be the first name is a hyphenated name
                // We separate each part of the name separated by a -
                $fn2 = \UTF8\mb_explode("-", trim($name));
                if ($firstTime)
                {
                    if (count($fn2) == 1)
                    { // no hyphen
                        $firstNameInitialMake =
                            mb_strtoupper(mb_substr($fn2[0], 0, 1));
                        $firstTime = FALSE;

                        continue;
                    }
                    $firstNameInitialMake = "";
                    foreach ($fn2 as $nameparts)
                    {
                        if ($firstNameInitialMake && ($initialsStyle <= 1))
                        { // needs '.'
                            $firstNameInitialMake .= ".-";
                        }
                        elseif ($firstNameInitialMake)
                        {
                            $firstNameInitialMake .= "-";
                        }
                        $firstNameInitialMake .=
                            mb_strtoupper(mb_substr($nameparts, 0, 1));
                        $firstTime = FALSE;
                    }
                }
                else
                {
                    if (count($fn2) == 1)
                    { // no hyphen
                        $initials[] = mb_strtoupper(mb_substr($fn2[0], 0, 1));

                        continue;
                    }
                    $fn3 = '';
                    foreach ($fn2 as $nameparts)
                    {
                        if ($fn3)
                        {
                            $fn3 .= "-";
                            $initials[] = $fn3 . mb_strtoupper(mb_substr(trim($nameparts), 0, 1));
                        }
                        else
                        {
                            $fn3 = mb_strtoupper(mb_substr(trim($nameparts), 0, 1));
                        }
                    }
                }
            }
            if (isset($initials))
            {
                if ($creator['initials'])
                {
                    $creator['initials'] = implode(" ", $initials) . ' ' . $creator['initials'];
                }
                else
                {
                    $creator['initials'] = implode(" ", $initials);
                }
            }
        }
        /**
         * Initials are stored as space-delimited characters.
         * If no initials, return just the firstname or its initial in the correct format.
         */
        if (!$creator['initials'])
        {
            if (isset($firstName))
            {	// full first name only
                return $firstName;
            }
            if (isset($firstNameInitialMake) && $initialsStyle > 1)
            { // First name initial with no '.'
                return $firstNameInitialMake;
            }
            if (isset($firstNameInitialMake))
            { // First name initial with  '.'
                // replace hyphen with '.-' so we get 'J.-F' for example
                str_replace('-', '.-', $firstNameInitialMake);

                return $firstNameInitialMake . '.';
            }

            return ''; // nothing here
        }
        $initialsArray = \UTF8\mb_explode(' ', $creator['initials']);
        if ($initialsStyle <= 1)
        { // needs '.' before any hyphen
            foreach ($initialsArray as $key => $i)
            {
                $initialsArray[$key] = str_replace('-', '.-', $i);
            }
        }
        /**
         * If firstname is initial only, prepend to array
         */
        if (isset($firstNameInitialMake))
        {
            array_unshift($initialsArray, $firstNameInitialMake);
        }
        if ($initialsStyle == 0)
        { // 'T. U. '
            $initials = implode('. ', $initialsArray) . '.';
        }
        elseif ($initialsStyle == 1)
        { // 'T.U.'
            $initials = implode('.', $initialsArray) . '.';
        }
        elseif ($initialsStyle == 2)
        { // 'T U '
            $initials = implode(' ', $initialsArray);
        }
        else
        { // 'TU '
            $initials = implode('', $initialsArray);
        }
        /**
         * If we have a full first name, prepend it to $initials.
         */
        if (isset($firstName))
        {
            return ($firstName . ' ' . $initials);
        }

        return $initials;
    }
    /**
     * Add an item to $this->item array
     *
     * @param string $item The item to be added.
     * @param string $fieldName The database fieldName of the item to be added
     */
    public function addItem($item, $fieldName)
    {
        $type = $this->type;
        if ($item !== FALSE)
        {
            $key = $this->styleMap->{$type}[$fieldName];
            /**
             * This item may already exist (e.g. edition field for WIKINDX)
             */
            if (!(isset($this->item) && array_key_exists($key, $this->item)))
            {
                $this->item[$key] = $item;
            }
        }
    }
    /**
     * Add all remaining items to $this->item array
     *
     * @param array $row The items to be added.
     */
    public function addAllOtherItems($row)
    {
        $type = $this->type;
        $typeStyleMap = $this->styleMap->$type;

        $row = array_intersect_key($row, $typeStyleMap);

        foreach ($row as $field => $value)
        {
            $this->addItem($value, $field);
        }

        unset($row);
    }
    /**
     * Format a title.  Anything enclosed in $delimitLeft...$delimitRight is to be left unchanged
     *
     * @param string $pString Raw title string.
     * @param string $delimitLeft Default is FALSE.
     * @param string $delimitRight Default is FALSE.
     *
     * @return void
     */
    public function formatTitle($pString, $delimitLeft = FALSE, $delimitRight = FALSE)
    {
        $type = $this->type;
        $key = array_search('title', $this->styleMap->$type);
        $subKey = $this->styleMap->{$type}[$key];

        if ($key !== FALSE)
        {
            if (array_key_exists($subKey, $this->item))
            { // i.e. we're now dealing with subtitle
                $this->item[$subKey] .= BIBFORMAT::titleCapitalization($pString, $delimitLeft, $delimitRight, $this->style['titleCapitalization']);
            }
            else
            {
                $this->item[$subKey] = BIBFORMAT::titleCapitalization($pString, $delimitLeft, $delimitRight, $this->style['titleCapitalization']);
            }
        }
        else
        {
            $this->item[$subKey] = '';
        }
    }
    /**
     * Format a shortTitle.  Anything enclosed in $delimitLeft...$delimitRight is to be left unchanged
     *
     * @param string $pString Raw title string.
     * @param string $delimitLeft Default is FALSE.
     * @param string $delimitRight Default is FALSE.
     *
     * @return void
     */
    public function formatShortTitle($pString, $delimitLeft = FALSE, $delimitRight = FALSE)
    {
        $type = $this->type;
        $key = array_search('shortTitle', $this->styleMap->$type);

        if ($key !== FALSE)
        {
            $this->item[$this->styleMap->{$type}[$key]] = BIBFORMAT::titleCapitalization($pString, $delimitLeft, $delimitRight, $this->style['titleCapitalization']);
        }
    }
    /**
     * Format a title of the original work from which a translation has been made.  Anything enclosed in $delimitLeft...$delimitRight is to be left unchanged
     *
     * @param string $pString Raw title string.
     * @param string $delimitLeft Default is FALSE.
     * @param string $delimitRight Default is FALSE.
     *
     * @return void
     */
    public function formatTransTitle($pString, $delimitLeft = FALSE, $delimitRight = FALSE)
    {
        $type = $this->type;
        $key = array_search('transTitle', $this->styleMap->$type);

        if ($key !== FALSE)
        {
            $subKey = $this->styleMap->{$type}[$key];
            if (array_key_exists($subKey, $this->item))
            { // i.e. we're now dealing with subtitle
                $this->item[$subKey] .= BIBFORMAT::titleCapitalization($pString, $delimitLeft, $delimitRight, $this->style['titleCapitalization']);
            }
            else
            {
                $this->item[$subKey] = BIBFORMAT::titleCapitalization($pString, $delimitLeft, $delimitRight, $this->style['titleCapitalization']);
            }
        }
    }
    /**
     * Do title capitalization
     *
     * @param string $pString Raw title string.
     * @param string $delimitLeft
     * @param string $delimitRight
     * @param bool $CondCapitalization Default is TRUE.
     *
     * @return string Formatted title string.
     */
    public static function titleCapitalization($pString, $delimitLeft, $delimitRight, $CondCapitalization = TRUE)
    {
        /**
         * '0' == 'Osbib Bibliographic Formatting'
         * '1' == 'Osbib bibliographic formatting'
         */
        $pString = ltrim($pString);

        // Empty string
        if (mb_strlen($pString) == 0)
        {
            return $pString;
        }
        // Error: delimiters not balanced
        elseif ((mb_strlen($delimitLeft . $delimitRight) > 0) && (mb_strlen($delimitLeft) == 0 || mb_strlen($delimitRight) == 0))
        {
            return $pString;
        }
        // Use defaults delimiters
        elseif (mb_strlen($delimitLeft) == 0 && mb_strlen($delimitRight) == 0)
        {
            $delimitLeft = '{';
            $delimitRight = '}';
        }

        $firstChar = '';

        $sizeDelimitLeft = mb_strlen($delimitLeft);
        $sizeDelimitRight = mb_strlen($delimitRight);

        // Remove all closing delimiter at the head of the string (malformed string)
        // to find the real first character to capitalize
        while ((mb_substr($pString, 0, $sizeDelimitRight) == $delimitRight) && (mb_strlen($pString) >= $sizeDelimitRight))
        {
            $pString = ltrim(mb_substr($pString, $sizeDelimitRight));
        }

        // If the first character isn't an opening delimiter, extract and capitalize it
        if ((mb_substr($pString, 0, $sizeDelimitLeft) != $delimitLeft) && $CondCapitalization)
        {
            $firstChar = mb_strtoupper(mb_substr($pString, 0, 1));
            $pString = mb_substr($pString, 1);
        }

        $nbDelimOpen = 0;
        $tmp = '';
        $sReturn = '';

        // Lowercase text not enclosed by delims
        for ($p = 0; $p < mb_strlen($pString); $p++)
        {
            $tmp .= mb_substr($pString, $p, 1);
            $sTmp = mb_strlen($tmp);

            if ($sTmp == $sizeDelimitLeft || $sTmp == $sizeDelimitRight)
            {
                // Opening
                if ($tmp == $delimitLeft)
                {
                    $tmp = '';
                    $nbDelimOpen++;
                }
                // Closing
                elseif ($tmp == $delimitRight)
                {
                    $tmp = '';
                    // max() is used to not decrement below 0
                    // if we encounter a closing delim not preceded by an opening delim.
                    $nbDelimOpen = max($nbDelimOpen - 1, 0);
                }
                // Delimiter not matched, it's just ordinary text to transform
                else
                {
                    $sReturn .= ($nbDelimOpen == 0 && $CondCapitalization) ? mb_strtolower($tmp) : $tmp;
                    $tmp = '';
                }
            }
        }

        // If any text remains, transform it
        if ($tmp != '')
        {
            $sReturn .= ($nbDelimOpen == 0 && $CondCapitalization) ? mb_strtolower($tmp) : $tmp;
            $tmp = '';
        }

        return $firstChar . $sReturn;
    }
    /**
     * Format pages.
     *
     * $this->style['pageFormat']:
     * 0 == 132-9
     * 1 == 132-39
     * 2 == 132-139
     *
     * @param string $start Page start.
     * @param string $end Page end. Default is FALSE.
     * @param mixed $citation If called from CITEFORMAT, this is the array of citation stylings. Default is FALSE.
     *
     * @return string Pages.
     */
    public function formatPages($start, $end = FALSE, $citation = FALSE)
    {
        $type = $this->type;
        $style = $citation ? $citation : $this->style;
        /**
         * Set default plural behaviour for pages
         */
        $this->pages_plural = FALSE;
        /**
         * If no page end, return just $start;
         */
        if (!$end)
        {
            $this->item[$this->styleMap->{$type}['pages']] = $start;

            return;
        }
        /**
         * Pages may be in roman numeral format etc.  Return unchanged
         */
        if (!is_numeric($start))
        {
            $this->item[$this->styleMap->{$type}['pages']] = $start . 'WIKINDX_NDASH' . $end;

            return;
        }
        /**
         * We have multiple pages...
         */
        $this->pages_plural = TRUE;
        /**
         * They've done something wrong so give them back exactly what they entered
         */
        if (($end <= $start) || (mb_strlen($end) < mb_strlen($start)))
        {
            $this->item[$this->styleMap->{$type}['pages']] = $start . 'WIKINDX_NDASH' . $end;

            return;
        }
        elseif ($style['pageFormat'] == 2)
        {
            $this->item[$this->styleMap->{$type}['pages']] = $start . 'WIKINDX_NDASH' . $end;

            return;
        }
        else
        {
            /**
             * We assume page numbers are not into the 10,000 range - if so, return the complete pages
             */
            if (mb_strlen($start) <= 4)
            {
                $startArray = preg_split('//u', $start);
                array_shift($startArray); // always an empty element at start?
                array_pop($startArray); // always an empty array element at end?
                if ($style['pageFormat'] == 0)
                {
                    array_pop($startArray);
                    $endPage = mb_substr($end, -1);
                    $index = -2;
                }
                else
                {
                    array_pop($startArray);
                    array_pop($startArray);
                    $endPage = mb_substr($end, -2);
                    $index = -3;
                }
                while (!empty($startArray))
                {
                    $startPop = array_pop($startArray);
                    $endSub = mb_substr($end, $index--, 1);
                    if ($endSub == $startPop)
                    {
                        $this->item[$this->styleMap->{$type}['pages']]
                            = $start . '-' . $endPage;

                        return;
                    }
                    if ($endSub > $startPop)
                    {
                        $endPage = $endSub . $endPage;
                    }
                }
            }
            else
            {
                $this->item[$this->styleMap->{$type}['pages']] = $start . 'WIKINDX_NDASH' . $end;

                return;
            }
        }
        /**
         * We should never reach here - in case we do, give back complete range so that something at least is printed
         */
        $this->item[$this->styleMap->{$type}['pages']] = $start . 'WIKINDX_NDASH' . $end;
    }
    /**
     * Format runningTime for film/broadcast
     *
     * @param int $minutes
     * @param int $hours
     */
    public function formatRunningTime($minutes, $hours)
    {
        $type = $this->type;
        if ($this->style['runningTimeFormat'] == 0)
        { // 1'45"
            if (isset($minutes) && $minutes)
            {
                if ($minutes < 10)
                {
                    $minutes = '0' . $minutes;
                }
                $runningTime = $hours . "'" . $minutes . "\"";
            }
            else
            {
                $runningTime = $hours . "'00\"";
            }
        }
        elseif ($this->style['runningTimeFormat'] == 1)
        { // 1:45
            if (isset($minutes) && $minutes)
            {
                if ($minutes < 10)
                {
                    $minutes = '0' . $minutes;
                }
                $runningTime = $hours . ":" . $minutes;
            }
            else
            {
                $runningTime = $hours . ":00";
            }
        }
        elseif ($this->style['runningTimeFormat'] == 2)
        { // 1,45
            if (isset($minutes) && $minutes)
            {
                if ($minutes < 10)
                {
                    $minutes = '0' . $minutes;
                }
                $runningTime = $hours . "," . $minutes;
            }
            else
            {
                $runningTime = $hours . ",00";
            }
        }
        elseif ($this->style['runningTimeFormat'] == 3)
        { // 1 hours, 45 minutes
            $hours = ($hours == 1) ? $hours . " hour" : $hours . " hours";
            if (isset($minutes) && $minutes)
            {
                $minutes = ($minutes == 1) ? $minutes . " minute" : $minutes . " minutes";
                $runningTime = $hours . ", " . $minutes;
            }
            else
            {
                $runningTime = $hours;
            }
        }
        elseif ($this->style['runningTimeFormat'] == 4)
        { // 1 hours and 45 minutes
            $hours = ($hours == 1) ? $hours . " hour" : $hours . " hours";
            if (isset($minutes) && $minutes)
            {
                $minutes = ($minutes == 1) ? $minutes . " minute" : $minutes . " minutes";
                $runningTime = $hours . " and " . $minutes;
            }
            else
            {
                $runningTime = $hours;
            }
        }
        elseif ($this->style['runningTimeFormat'] == 5)
        { // 105 minutes
            $time = 0;
            if (isset($hours))
            {
                $time = $hours * 60;
            }
            if (isset($minutes) && $minutes)
            {
                $time += $minutes;
            }
            $runningTime = ($time == 1) ? $time . " minute" : $time . " minutes";
        }
        elseif ($this->style['runningTimeFormat'] == 6)
        { // 105 mins
            $time = 0;
            if (isset($hours))
            {
                $time = $hours * 60;
            }
            if (isset($minutes) && $minutes)
            {
                $time += $minutes;
            }
            $runningTime = ($time == 1) ? $time . " min" : $time . " mins";
        }
        $this->item[$this->styleMap->{$type}['runningTime']] = $runningTime;
    }
    /**
     * Format date
     *
     * @param int $startDay
     * @param int $startMonth
     * @param int $endDay
     * @param int $endMonth
     * @param bool $webArticle Default is FALSE.
     *
     * for 'web_article', $startDay and $startMonth represent the publication date
     */
    public function formatDate($startDay, $startMonth, $endDay, $endMonth, $webArticle = FALSE)
    {
        $type = $this->type;
        $oldStartDay = $startDay;
        $oldEndDay = $endDay;
        if ($this->dateMonthNoDay && !$startDay && !$endDay)
        {
            $this->{$type}[$this->styleMap->{$type}['date']] =
            $this->dateArray[$this->styleMap->{$type}['date']];
        }
        if ($startDay !== FALSE)
        {
            if ($this->style['dayFormat'] == 1)
            { // e.g. 10.
                $startDay .= '.';
            }
            elseif ($this->style['dayFormat'] == 2)
            { // e.g. 10th
                $startDay = $this->cardinalToOrdinal($startDay, 'dayMonth');
            }
            if (array_key_exists('dayLeadingZero', $this->style) && $oldStartDay < 10)
            {
                $startDay = '0' . $startDay;
            }
        }
        if ($endDay !== FALSE)
        {
            if ($this->style['dayFormat'] == 1)
            { // e.g. 10.
                $endDay .= '.';
            }
            elseif ($this->style['dayFormat'] == 2)
            { // e.g. 10th
                $endDay = $this->cardinalToOrdinal($endDay, 'dayMonth');
            }
            if (array_key_exists('dayLeadingZero', $this->style) && $oldEndDay < 10)
            {
                $endDay = '0' . $endDay;
            }
        }
        if ($this->style['monthFormat'] == 1)
        { // Full month name
            $monthArray = $this->longMonth;
        }
        elseif ($this->style['monthFormat'] == 2)
        { // User-defined
            for ($i = 1; $i <= 16; $i++)
            {
                $monthArray[$i] = $this->style["userMonth_$i"];
            }
        }
        else
        { // Short month name
            $monthArray = $this->shortMonth;
        }
        if ($startMonth !== FALSE)
        {
            if (!array_key_exists($startMonth, $monthArray))
            {
                $startMonth = '';
            }
            else
            {
                $startMonth = $monthArray[$startMonth];
            }
        }
        if ($endMonth !== FALSE)
        {
            if (!array_key_exists($endMonth, $monthArray))
            {
                $endMonth = '';
            }
            else
            {
                $endMonth = $monthArray[$endMonth];
            }
        }
        if (!$endMonth)
        {
            if ($this->style['dateFormat'])
            { // Order == Month Day
                $startDay = ($startDay === FALSE) ? '' : ' ' . $startDay;
                $date = $startMonth . $startDay;
            }
            else
            { // Order == Day Month
                $startDay = ($startDay === FALSE) ? '' : $startDay . ' ';
                $date = $startDay . $startMonth;
            }
        }
        else
        { // date range
            if (!$startDay)
            {
                $delimit = $this->style['dateRangeDelimit2'];
            }
            else
            {
                $delimit = $this->style['dateRangeDelimit1'];
            }
            if (($endMonth !== FALSE) && ($startMonth == $endMonth) && ($this->style['dateRangeSameMonth'] == 1))
            {
                $endMonth = FALSE;
                if (!$endDay)
                {
                    $delimit = FALSE;
                }
            }
            if ($this->style['dateFormat'])
            { // Order == Month Day
                $startDay = ($startDay === FALSE) ? '' : ' ' . $startDay;
                $startDate = $startMonth . $startDay;
                if ($endMonth)
                {
                    $endDate = $endMonth . $endDay = ($endDay === FALSE) ? '' : ' ' . $endDay;
                }
                else
                {
                    $endDate = $endDay;
                }
            }
            else
            { // Order == Day Month
                if ($endMonth)
                {
                    $startDate = $startDay . ' ' . $startMonth;
                    $endDate = $endDay = ($endDay === FALSE) ? '' : $endDay . ' ';
                    $endDate .= $endMonth;
                }
                else
                {
                    $startDate = $startDay;
                    $endDate = ($endDay === FALSE) ? ' ' : $endDay . ' ';
                    $endDate .= $startMonth;
                }
            }
            $date = $startDate . $delimit . $endDate;
        }
        if ($webArticle)
        {
            if (array_key_exists('publicationDate', $this->styleMap->{$type}))
            {
                $this->item[$this->styleMap->{$type}['publicationDate']] = $date;
            }
        }
        else
        {
            $this->item[$this->styleMap->{$type}['date']] = $date;
        }
    }
    /**
     * Format edition
     *
     * @param int $edition
     */
    public function formatEdition($edition)
    {
        $type = $this->type;
        if (!is_numeric($edition))
        {
            $edition = $edition;
        }
        elseif ($this->style['editionFormat'] == 1)
        { // 10.
            $edition .= '.';
        }
        elseif ($this->style['editionFormat'] == 2)
        { // 10th
            $edition = $this->cardinalToOrdinal($edition, 'edition');
        }
        $this->item[$this->styleMap->{$type}[array_search('edition', $this->styleMap->$type)]] = $edition;
    }
    /**
     * Set-up localisations etc.
     */
    public function loadArrays()
    {
        // WIKINDX-specific
        if ($this->wikindx)
        {
            $this->wikindxLanguageClass = FACTORY_CONSTANTS::getInstance();
            if (method_exists($this->wikindxLanguageClass, "monthToLongName")
                && method_exists($this->wikindxLanguageClass, "monthToShortName"))
            {
                $this->longMonth = $this->wikindxLanguageClass->monthToLongName();
                $this->shortMonth = $this->wikindxLanguageClass->monthToShortName();

                return;
            }
        }
        // Defaults
        $this->longMonth = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
        $this->shortMonth = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
        ];
        $this->titleSubtitleSeparator = ": ";
    }
    /**
     * Restore each $this->type array from $this->backup
     */
    private function restoreTypes()
    {
        foreach ($this->backup as $type => $array)
        {
            $this->$type = $array;
        }
    }
    /**
     * Preprocess BibTeX-type entries
     *
     * @param array $row assoc. array of elements for one bibtex entry
     * @param string $type resource type
     *
     * @return array|string resource type|resource assoc. array of elements for one bibtex entry
     */
    private function preProcessBibtex(&$row, $type)
    {
        //05/05/2005 G.GARDEY: change bibtexParse name.
        /**
         * This set of includes is for the OSBib public release and should be uncommented for that and
         * the WIKINDX-specific includes below commented out!
         */
        // WIKINDX naming of above files
        include_once($this->bibtexParsePath . "/BIBTEXCREATORPARSE.php");
        $parseCreator = new BIBTEXCREATORPARSE();
        include_once($this->bibtexParsePath . "/BIBTEXMONTHPARSE.php");
        $parseDate = new BIBTEXMONTHPARSE();
        include_once($this->bibtexParsePath . "/BIBTEXPAGEPARSE.php");
        $parsePages = new BIBTEXPAGEPARSE();
        /**
         * Bibtex-specific types not defined in STYLEMAPBIBTEX
         */
        if (!$this->type)
        {
            if ($type == 'mastersthesis')
            {
                $type = 'thesis';
                $row['type'] = "Master's Dissertation";
            }
            if ($type == 'phdthesis')
            {
                $type = 'thesis';
                $row['type'] = "PhD Thesis";
            }
            elseif ($type == 'booklet')
            {
                $type = 'miscellaneous';
            }
            elseif ($type == 'conference')
            {
                $type = 'proceedings_article';
            }
            elseif ($type == 'incollection')
            {
                $type = 'book_article';
            }
            elseif ($type == 'manual')
            {
                $type = 'report';
            }
        }
        /**
         * 'article' could be journal, newspaper or magazine article
         */
        elseif ($type == 'article')
        {
            if (array_key_exists('month', $row) && array_key_exists('date', $this->styleMap->$type))
            {
                list($month, $day) = $parseDate->init($row['month']);
                if ($day)
                {
                    $type = 'newspaper_article';
                }
                elseif ($month)
                {
                    $type = 'magazine_article';
                }
                $this->formatDate($day, $month);
            }
            else
            {
                $type = 'journal_article';
            }
        }
        /**
         * Is this a web article?
         */
        elseif (($type == 'misc') && array_key_exists('howpublished', $row))
        {
            if (preg_match("#^\\\\url{(.*://.*)}#u", $row['howpublished'], $match))
            {
                $row['URL'] = $match[1];
                $type = 'web_article';
            }
        }
        $this->type = $type;
        if (array_key_exists('creator1', $row) && $row['creator1'] &&
            array_key_exists('creator1', $this->styleMap->$type))
        {
            $creators = $parseCreator->parse($row['creator1']);
            foreach ($creators as $cArray)
            {
                $temp[] = [
                    'surname' => trim($cArray[2]),
                    'firstname' => trim($cArray[0]),
                    'initials' => trim($cArray[1]),
                    'prefix' => trim($cArray[3]),
                ];
            }
            $this->formatNames($temp, 'creator1');
            unset($temp);
        }
        if (array_key_exists('creator2', $row) && $row['creator2'] &&
            array_key_exists('creator2', $this->styleMap->$type))
        {
            $creators = $parseCreator->parse($row['creator2']);
            foreach ($creators as $cArray)
            {
                $temp[] = [
                    'surname' => trim($cArray[2]),
                    'firstname' => trim($cArray[0]),
                    'initials' => trim($cArray[1]),
                    'prefix' => trim($cArray[3]),
                ];
            }
            $this->formatNames($temp, 'creator2');
        }
        if (array_key_exists('pages', $row) && array_key_exists('pages', $this->styleMap->$type))
        {
            list($start, $end) = $parsePages->init($row['pages']);
            $this->formatPages(trim($start), trim($end));
        }
        $this->formatTitle($row['title'], "{", "}");

        return [$type, $row];
    }
    /**
     * Find alternate fields dependent on previous or following fields and deal with accordingly.
     *
     * @param array $typeArray
     */
    private function findAlternates($typeArray)
    {
        $typeArray = array_intersect_key($typeArray, $this->item);

        foreach ($typeArray as $field => $array)
        {
            if (is_array($array))
            {
                if (array_key_exists('alternatePreFirst', $array) ||
                    array_key_exists('alternatePreSecond', $array))
                {
                    if (!$this->item[$field])
                    {
                        unset($this->item[$array['alternatePreFirst']]);
                    }
                    else
                    {
                        unset($this->item[$array['alternatePreSecond']]);
                    }
                }

                if (array_key_exists('alternatePostFirst', $array) ||
                    array_key_exists('alternatePostSecond', $array))
                {
                    if (!$this->item[$field])
                    {
                        unset($this->item[$array['alternatePostFirst']]);
                    }
                    else
                    {
                        unset($this->item[$array['alternatePostSecond']]);
                    }
                }
            }
        }
    }
    /**
     * Check that the first field of $this->$type exists in $this->item and has a value.
     *
     * If it does not and $this->$type has a partial template array, rearrange $this->$type
     *
     * @param string $type Resource type
     */
    private function checkPartial($type)
    {
        $this->partialDone = FALSE;
        if (!array_key_exists('partial', $this->$type) || !$this->{$type}['partial'])
        {
            return; // nothing to do
        }
        $typeKeys = array_keys($this->$type);
        if (array_key_exists($typeKeys[0], $this->item) && $this->item[$typeKeys[0]])
        {
            return;
        }	// item key exists -- nothing to do
        // Replace the whole template with the partial template?
        if ($this->{$type}['partialReplace'])
        {
            $partial = $this->{$type}['partial'];
            $this->$type = $partial;
            // If independent keys exists, because we have removed the first key from $this->$type, we must decrement the keys in independent
            if (array_key_exists('independent', $this->$type) && !empty($this->{$type}['independent']))
            {
                foreach ($this->{$type}['independent'] as $key => $value)
                {
                    if (is_numeric($key))
                    {
                        $tempInd[$key - 1] = $value;
                    }
                    else
                    {
                        $tempInd[$key] = $value;
                    }
                }
                $this->{$type}['independent'] = $tempInd;
            }

            return;
        }
        // At this point need to rearrange
        $oldType = $this->$type;
        // Remove missing first item
        array_shift($oldType);
        foreach ($this->{$type}['partial'] as $key => $value)
        {
            unset($oldType[$key]);
        }
        $this->$type = array_merge($this->{$type}['partial'], $oldType);
        // If independent keys exists, because we have removed the first key from $this->$type, we must decrement the keys in independent
        if (array_key_exists('independent', $this->$type) && !empty($this->{$type}['independent']))
        {
            foreach ($this->{$type}['independent'] as $key => $value)
            {
                $tempInd[intval($key) - 1] = $value;
            }
            $this->{$type}['independent'] = $tempInd;
        }
        $this->partialDone = TRUE;
    }
    /**
     * Create ordinal number from cardinal
     *
     * @param string $cardinal Input number.
     * @param string $field Default is FALSE.
     *
     * @return string
     */
    private function cardinalToOrdinal($cardinal, $field = FALSE)
    {
        // WIKINDX-specific
        if ($this->wikindx && method_exists($this->wikindxLanguageClass, "cardinalToOrdinal"))
        {
            return $this->wikindxLanguageClass->cardinalToOrdinal($cardinal, $field);
        }
        $modulo = $cardinal % 100;
        if (($modulo == 11) || ($modulo == 12) || ($modulo == 13))
        {
            return $cardinal . 'th';
        }
        $modulo = $cardinal % 10;
        if (($modulo >= 4) || !$modulo)
        {
            return $cardinal . 'th';
        }
        if ($modulo == 1)
        {
            return $cardinal . 'st';
        }
        if ($modulo == 2)
        {
            return $cardinal . 'nd';
        }
        if ($modulo == 3)
        {
            return $cardinal . 'rd';
        }
    }
}
