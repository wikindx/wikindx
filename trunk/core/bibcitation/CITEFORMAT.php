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
 * Description of class CITEFORMAT. Format citations.
 *
 * @package wikindx\core\bibcitation
 */
class CITEFORMAT
{
    /** boolean */
    public $suwpExport;
    /** boolean */
    public $hyperlinkBase;
    /** boolean */
    public $wikindx;
    /** boolean */
    public $preview;
    /** boolean */
    public $output;
    /** boolean */
    public $rtfBibExport = FALSE;
    /** $dir is the path to STYLEMAP.php etc. */
    public $dir;
    /** array */
    public $items;
    /** $bibStyle is the object that handles bibliography formatting of appended bibliographies. */
    private $bibStyle;
    /** object */
    private $styleMap;
    /** object */
    private $export;
    /** array */
    private $citationIds;
    /** array */
    private $creatorIds;
    /** array */
    private $consecutiveCreatorSep;
    /** array */
    private $endnoteStringArray;
    /** array */
    private $creators;
    /** array */
    private $endnoteCitations;
    /** array */
    private $footnoteOffsetIds;
    /** array */
    private $endnoteSameIdsArray;
    /** array */
    private $inTextDoneIds;
    /** array */
    private $endnotes;
    /** array */
    private $opCit;
    /** array */
    private $rtfDoneIds;
    /** array */
    private $intextBibliography;
    /** array */
    private $creatorSurnames;
    /** array */
    private $endnoteSameIds;
    /** boolean */
    private $styleSheet;
    /** boolean */
    private $endnoteString;
    /** boolean */
    private $matchNameSplit;
    /** boolean */
    private $matchNameSplitEtAl;
    /** boolean */
    private $citationInSameSentence;
    /** boolean */
    private $nameInSameSentence;
    /** boolean */
    private $previousNameInSameSentenceId;
    /** boolean */
    private $multipleCitations;
    /** boolean */
    private $pageSplitDone;
    /** boolean */
    private $newParagraph;
    /** boolean */
    private $newSection;
    /** array */
    private $storeOriginalTemplate;
    /** array */
    private $template;

    /**
     * CITEFORMAT
     *
     * @param string $output Allowed values: 'html', plain', 'rtf'. Default is 'html'
     */
    public function __construct($output = 'html')
    {
        $this->output = $output;
        $this->bibStyle = FACTORY_BIBSTYLE::getInstance($this->output);
        $this->bibStyle->bibformat->patternHighlight = "highlight";
        if (!$this->dir)
        {
            $this->dir = dirname(__FILE__) . "/";
        }
        else
        {
            if (mb_substr($this->dir, mb_strlen($this->dir) - 1, 1) != DIRECTORY_SEPARATOR)
            {
                $this->dir .= DIRECTORY_SEPARATOR;
            }
        }
        $this->styleMap = $this->bibStyle->bibformat->styleMap;
        $this->bibStyleProcess = 'process';

        $this->parsexml = FACTORY_PARSEXML::getInstance();
        $this->getStyle();
    }
    /**
     * Some system initialisation
     */
    public function initialise()
    {
        $this->citationIds = $this->creatorIds = $this->consecutiveCreatorSep = $this->endnoteStringArray =
            $this->creators = $this->endnoteCitations = $this->endnoteSameIdsArray = $this->inTextDoneIds =
            $this->endnotes = $this->opCit = $this->rtfDoneIds = $this->intextBibliography =
            $this->creatorSurnames = $this->items = $this->footnoteOffsetIds = [];
        $this->endnoteSameIds = 0;
        $this->styleSheet = FALSE; // For RTF
        $this->hyperlinkBase = FALSE; // no hyperlinking of cited resources (i.e. for $this->output other than 'html')
        $this->endnoteString = FALSE;
        $this->matchNameSplit = $this->matchNameSplitEtAl = FALSE; // split page from main citation (in-text only)
        $this->citationInSameSentence = $this->nameInSameSentence = FALSE;
        $this->previousNameInSameSentenceId = FALSE;
        $this->multipleCitations = FALSE;
        $this->pageSplitDone = FALSE;
        $this->newParagraph = TRUE; // Indication of new paragraph
        $this->newSection = TRUE; // Indication of new section
        if (isset($this->storeOriginalTemplate))
        {
            $this->template = $this->storeOriginalTemplate;
        }
        $this->patterns = ''; // not needed here but must be set for EXPORTFILTER
        include_once($this->dir . "EXPORTFILTER.php");
        $this->export = new EXPORTFILTER($this, $this->output);
        // WIKINDX-specific
        $this->wikindx = FALSE;
        $this->preview = FALSE;
        //		$this->rtfBibExport = FALSE;
        $this->suwpExport = FALSE;
    }
    /**
     * Reformat the array representation of citation into a more useable format - Endnote style citations
     */
    public function citationToArrayEndnoteStyle()
    {
        // Ensure we have only valid fields.
        foreach ($this->style['templateEndnoteInText'] as $field => $value) {
            if (array_key_exists($field, $this->styleMap->citationEndnoteInText) || ($field == 'independent') ||
                ($field == 'ultimate') || ($field == 'preliminaryText')) {
                $this->templateEndnoteInText[$field] = $value;
            }
        }
        if (isset($this->templateEndnoteInText)) {
            $this->parseIndependent($this->templateEndnoteInText);
        }
        if (array_key_exists('templateEndnote', $this->style) && is_array($this->style['templateEndnote'])) {
			// Ensure we have only valid fields.
			foreach ($this->style['templateEndnote'] as $field => $value)
			{
				if (array_key_exists($field, $this->styleMap->citationEndnote) || ($field == 'independent') ||
					($field == 'ultimate') || ($field == 'preliminaryText'))
				{
					$this->templateEndnote[$field] = $value;
				}
			}
			if (isset($this->templateEndnote))
			{
				$this->parseIndependent($this->templateEndnote);
			}
		}
        if (array_key_exists('ibid', $this->style) && is_array($this->style['ibid'])) {
			// Ensure we have only valid fields.
			foreach ($this->style['ibid'] as $field => $value)
			{
				if (array_key_exists($field, $this->styleMap->citationEndnote) || ($field == 'independent') ||
					($field == 'ultimate') || ($field == 'preliminaryText'))
				{
					$this->templateIbid[$field] = $value;
				}
			}
			if (isset($this->templateIbid))
			{
				$this->parseIndependent($this->templateIbid);
			}
		}
        if (array_key_exists('idem', $this->style) && is_array($this->style['idem'])) {
			// Ensure we have only valid fields.
			foreach ($this->style['idem'] as $field => $value)
			{
				if (array_key_exists($field, $this->styleMap->citationEndnote) || ($field == 'independent') ||
					($field == 'ultimate') || ($field == 'preliminaryText'))
				{
					$this->templateIdem[$field] = $value;
				}
			}
			if (isset($this->templateIdem))
			{
				$this->parseIndependent($this->templateIdem);
			}
		}
        if (array_key_exists('opCit', $this->style) && is_array($this->style['opCit'])) {
			// Ensure we have only valid fields.
			foreach ($this->style['opCit'] as $field => $value)
			{
				if (array_key_exists($field, $this->styleMap->citationEndnote) || ($field == 'independent') ||
					($field == 'ultimate') || ($field == 'preliminaryText'))
				{
					$this->templateOpCit[$field] = $value;
				}
			}
			if (isset($this->templateOpCit))
			{
				$this->parseIndependent($this->templateOpCit);
			}
		}
    }
    /**
     * Reformat the array representation of citation into a more useable format - In-text style citations
     */
    public function citationToArrayInTextStyle()
    {
        // Ensure we have only valid fields.
        foreach ($this->style['template'] as $field => $value)
        {
            if (array_key_exists($field, $this->styleMap->citation) || ($field == 'independent') ||
                ($field == 'ultimate') || ($field == 'preliminaryText'))
            {
                $this->template[$field] = $value;
            }
        }
        if (isset($this->template))
        {
            $this->parseIndependent($this->template);
            $this->storeOriginalTemplate = $this->template;
        }
        if (array_key_exists('followCreatorTemplate', $this->style) && is_array($this->style['followCreatorTemplate']))
        {
            foreach ($this->style['followCreatorTemplate'] as $field => $value)
            {
                if (array_key_exists($field, $this->styleMap->citation) || ($field == 'independent') ||
                    ($field == 'ultimate') || ($field == 'preliminaryText'))
                {
                    $this->followCreatorTemplate[$field] = $value;
                }
            }
            $this->parseIndependent($this->followCreatorTemplate);
        }
        if (array_key_exists('consecutiveCreatorTemplate', $this->style) && is_array($this->style['consecutiveCreatorTemplate']))
        {
            foreach ($this->style['consecutiveCreatorTemplate'] as $field => $value)
            {
                if (array_key_exists($field, $this->styleMap->citation) || ($field == 'independent') ||
                    ($field == 'ultimate') || ($field == 'preliminaryText'))
                {
                    $this->consecutiveCreatorTemplate[$field] = $value;
                }
            }
            $this->parseIndependent($this->consecutiveCreatorTemplate);
        }
        if (array_key_exists('subsequentCreatorTemplate', $this->style) && is_array($this->style['subsequentCreatorTemplate']))
        {
            foreach ($this->style['subsequentCreatorTemplate'] as $field => $value)
            {
                if (array_key_exists($field, $this->styleMap->citation) || ($field == 'independent') ||
                    ($field == 'ultimate') || ($field == 'preliminaryText'))
                {
                    $this->subsequentCreatorTemplate[$field] = $value;
                }
            }
            $this->parseIndependent($this->subsequentCreatorTemplate);
        }
        if (array_key_exists('ambiguousTemplate', $this->style) && is_array($this->style['ambiguousTemplate']))
        {
            // Ensure we have only valid fields.
            foreach ($this->style['ambiguousTemplate'] as $field => $value)
            {
                if (array_key_exists($field, $this->styleMap->citation) || ($field == 'independent') ||
                    ($field == 'ultimate') || ($field == 'preliminaryText'))
                {
                    $this->ambiguousTemplate[$field] = $value;
                }
            }print_r($this->ambiguousTemplate); die;
            $this->parseIndependent($this->ambiguousTemplate);
        }
        // replacement citation templates for particular resource types
        foreach ($this->styleMap->types as $type => $value)
        {
            $key = $type . "Template";
            if (array_key_exists($key, $this->style) && is_array($this->style[$key]))
            {
                foreach ($this->style[$key] as $field => $value)
                {
                    if (array_key_exists($field, $this->styleMap->citation) || ($field == 'independent') ||
                        ($field == 'ultimate') || ($field == 'preliminaryText'))
                    {
                        $this->{$key}[$field] = $value;
                    }
                }
                $this->parseIndependent($this->$key);
            }
        }
    }
    /**
     * Parse independent strings of templates
     *
     * @param $array
     */
    public function parseIndependent(&$array)
    {
        if (array_key_exists('independent', $array))
        {
            $ind1 = $array['independent'];
            foreach ($ind1 as $key => $value)
            {
                $split = \UTF8\mb_explode('_', $key);
                $ind2[$split[1]] = $value;
            }
            if (isset($ind2))
            {
                $array['independent'] = $ind2;
            }
        }
    }
    /**
     * Loop through $this->items
     *
     * @return string Complete string ready for printing to the output medium.
     */
    public function process()
    {
        if (!isset($this->output))
        {
            $this->output = 'html';
        }
        $this->export = new EXPORTFILTER($this, $this->output);
        if ($this->style['citationStyle'])
        { // Endnote style citations
            return $this->endnoteStyle();
        }
        else
        { // In-text tyle citations
            return $this->inTextStyle();
        }
    }
    /**
     * Endnote style citations
     *
     * @return string Complete string ready for printing to the output medium.
     */
    public function endnoteStyle()
    {
        $pString = '';
        $multiples = $textAtoms = [];
        $this->multipleCitations = FALSE;
        foreach ($this->items as $count => $this->item)
        {
            $this->id = $count;
            $this->ids[$count] = $this->item['id'];
            $text = '';
            // If $this->items[$count + 1]['text'] is empty, this is the start or continuation of a multiple citation.
            // If $this->items[$count + 1]['text'] is not empty, this is the start of a new citation with 'text' preceeding the citation
            if (array_key_exists($count + 1, $this->items) && !$this->items[$count + 1]['text'])
            { // multiples
                // Grab the first citation of the multiple
                $textAtoms[] = $this->item['text'];
                $citation = $this->map($this->templateEndnoteInText);
                $multiples[$count] = $citation;
                $this->multipleCitations = TRUE;

                continue;
            }
            elseif ($this->multipleCitations)
            { // last of multiple
                $citation = $this->map($this->templateEndnoteInText);
                $multiples[$count] = $citation;
            }
            else
            { // not multiple
                $text = $this->item['text'];
                $citation = $this->map($this->templateEndnoteInText);
            }
            $this->multipleCitations = FALSE;
            if (!empty($multiples))
            {
                $textAtom = implode('', $textAtoms);
                $citation = $this->multiple($multiples);
                $multiples = $textAtoms = [];
                $text .= $textAtom;
            }
            if ($this->style['formatEndnoteInText'] == 1)
            { // superscript
                $citation = '[sup]' . $this->style['firstCharsEndnoteInText'] . $citation .
                $this->style['lastCharsEndnoteInText'] . '[/sup]';
            }
            elseif ($this->style['formatEndnoteInText'] == 2)
            { // subscript
                $citation = '[sub]' . $this->style['firstCharsEndnoteInText'] . $citation .
                $this->style['lastCharsEndnoteInText'] . '[/sub]';
            }
            else
            {
                $citation = $this->style['firstCharsEndnoteInText'] . $citation .
                $this->style['lastCharsEndnoteInText'];
            }
            $pString .= $text . $this->export->format($citation);
        }

        return $pString;
    }
    /**
     * In-text style citations
     *
     * @return string Complete string ready for printing to the output medium.
     */
    public function inTextStyle()
    {
        // Create RTF stylesheet
        if ($this->wikindx && !$this->styleSheet && ($this->output == 'rtf'))
        {
            $session = new SESSION();
            $this->styleSheet = "{\\stylesheet\n
{\\*\\cs1 Hyperlink;}\n
{\\*\\cs1 Bullet Symbols;}\n
{\\*\\cs1 Numbering Symbols;}\n
{\\cs2 \\additive \\super \\sbasedon10 footnote reference;}" . LF;
            // WIKINDX-specific: RTF page numbering
            if ($session->getVar("exportPaper_pageNumber"))
            { // 0 == no. 1 == footer, 2 == header
                $this->styleSheet .= "{\\cs17 \\additive \\sbasedon10 page number;}\n}\n" . LF;
                $this->styleSheet .= $this->rtfPageNumbers($session);
            }
            if ($session->getVar("exportPaper_sectionFtRestart"))
            {
                $this->styleSheet .= "\\ftnrestart\\aftnnar\\fet1" . LF;
            }
            else
            {
                $this->styleSheet .= "\\aftnnar\\fet1" . LF;
            }
        }
        $pString = '';
        $multiples = $textAtoms = $creatorIdStrings = [];
        $this->multipleCitations = FALSE;
        $this->disambiguate();
        $preText = $postText = $this->previousNameInSameSentenceId = $this->citationInSameSentence = FALSE;
        foreach ($this->items as $count => $this->item)
        {
            $this->matchNameSplit = $this->matchNameSplitEtAl = $this->nameInSameSentence = FALSE;
            // If this is a single citation or the start of a multiple set, get any preText and postText
            if (!$this->multipleCitations && array_key_exists('preText', $this->item))
            {
                $preText = $this->item['preText'];
                if (array_key_exists('postText', $this->item))
                {
                    $postText = $this->item['postText'];
                }
            }
            $usingReplacementType = FALSE;
            if (isset($tempTemplate))
            {
                $this->template = $tempTemplate;
                unset($tempTemplate);
            }
            $this->ids[$count] = $this->item['id'];
            if ($this->style['subsequentCreatorRange'] == 1)
            { // paragraph
                if ($this->newParagraph)
                {
                    $this->newParagraph = FALSE;
                }

                // WIKINDX v4 runs in HTML 4.01 but TinyMCE was configured for XHTML in v3.8.
                // For the precaution, treating the two notations which can be mixed after migrating to v4.
                if ((($this->output == 'rtf') &&
                    (mb_strpos($this->item['text'], "__WIKINDX__NEWLINEPAR____WIKINDX__NEWLINEPAR__")
                    !== FALSE))
                    ||
                    ((($this->output == 'html') || ($this->output == 'htmlNoBib')) && (mb_strpos($this->item['text'], BR . BR) !== FALSE))
                    ||
                    ((($this->output == 'html') || ($this->output == 'htmlNoBib')) && (mb_strpos($this->item['text'], BR . BR) !== FALSE))
                    ) {
                    $creatorIdStrings = [];
                    $this->newParagraph = TRUE;
                }
            }
            elseif (($this->output == 'rtf') && ($this->style['subsequentCreatorRange'] == 2))
            { // section
                if ($this->newSection)
                {
                    $this->newSection = FALSE;
                }
                if (mb_strpos($this->item['text'], "__WIKINDX__SECTION__") !== FALSE)
                {
                    $creatorIdStrings = [];
                    $this->newSection = TRUE;
                }
            }
            $text = '';
            $this->followCreatorTemplateUse = FALSE;
            if (array_key_exists('ambiguousTemplate', $this->item))
            {
                if ($this->checkTemplateFields($this->ambiguousTemplate))
                {
                    $tempTemplate = $this->template;
                    $this->template = $this->ambiguousTemplate;
                    // Debugging
//$templateName = "AMBIGUOUS";
//
                }
            }
            elseif ($this->multipleCitations && array_key_exists($count, $this->creatorIds) &&
                isset($this->consecutiveCreatorTemplate) && array_key_exists($count - 1, $this->creatorIds) &&
                ($this->creatorIds[$count] == $this->creatorIds[$count - 1]))
            {
                $this->consecutiveCreatorSep[] = $count;
                if ($this->checkTemplateFields($this->consecutiveCreatorTemplate))
                {
                    $tempTemplate = $this->template;
                    $this->template = $this->consecutiveCreatorTemplate;
                }
            }
            // Replacement templates for particular resource types.  Need to match this template to other replacement templates by removing fields if necessary
            $type = $this->item['type'] . "Template";
            if (isset($this->$type))
            {
                $tempTypeTemplate = $this->$type;
                $usingReplacementType = TRUE;
                if (isset($tempTemplate))
                { // i.e. $this->template has already been replaced
                    foreach ($this->$type as $key => $value)
                    {
                        if (($key == 'ultimate') || ($key == 'preliminaryText'))
                        {
                            continue;
                        }
                        if (!array_key_exists($key, $this->template))
                        {
                            unset($this->{$type}[$key]);
                        }
                    }
                    if ($this->checkTemplateFields($this->$type))
                    {
                        $this->template = $this->$type;
                        // Debugging
//$templateName = "$type";
//
                    }
                }
                else
                {
                    if ($this->checkTemplateFields($this->$type))
                    {
                        $tempTemplate = $this->template;
                        $this->template = $this->$type;
                        // Debugging
//$templateName = "$type";
//
                    }
                }
            }
            // Debugging
            // If $this->items[$count + 1]['text'] is empty, this is the start or continuation of a multiple citation.
            // If $this->items[$count + 1]['text'] is not empty, this is the start of a new citation with 'text' preceeding the citation
            if (array_key_exists($count + 1, $this->items) && !$this->items[$count + 1]['text'])
            { // multiples
                // Grab the first citation of the multiple
                $textAtoms[] = $this->item['text'];
                $citation = $this->map($this->template);
                // If $citation is empty, we want to return something so return the title
                if (($citation == '') && array_key_exists('title', $this->item))
                {
                    $citation = $this->item['title'];
                }
                $multiples[$count] = $citation;
                $this->multipleCitations = TRUE;

                continue;
            }
            elseif ($this->multipleCitations)
            { // last of multiple
                $citation = $this->map($this->template);
                // If $citation is empty, we want to return something so return the title
                if (($citation == '') && array_key_exists('title', $this->item))
                {
                    $citation = $this->item['title'];
                }
                $multiples[$count] = $citation;
            }
            else
            { // not multiple
                $text = $this->item['text'];
                // If single citation is in the same sentence as first creator surname, use followCreatorTemplate if specified.
                // Don't use this if there is preText or postText in the citation.
                if (isset($this->item['firstCreatorSurname']) && !$preText && !$postText)
                {
                    $this->sameSentence($text);
                }
                if (isset($this->followCreatorTemplate) && $this->nameInSameSentence)
                {
                    if (!$usingReplacementType)
                    {
                        if ($this->checkTemplateFields($this->followCreatorTemplate))
                        {
                            $tempTemplate = $this->template;
                            $this->template = $this->followCreatorTemplate;
                            // Debugging
//$templateName = "FOLLOWCREATOR";
//
                        }
                    }
                    else
                    {
                        foreach ($this->$type as $key => $value)
                        {
                            if (($key == 'ultimate') || ($key == 'preliminaryText'))
                            {
                                continue;
                            }
                            if (!array_key_exists($key, $this->followCreatorTemplate))
                            {
                                unset($this->{$type}[$key]);
                            }
                        }
                        if ($this->checkTemplateFields($this->$type))
                        {
                            $this->template = $this->$type; // $tempTemplate already stored
// Debugging
//$templateName = "$type";
//
                        }
                    }
                    unset($split);
                }
                $trySubsequent = FALSE;
                if (!empty($creatorIdStrings) && array_search($this->item['id'], $this->inTextDoneIds) !== FALSE)
                {
                    $trySubsequent = TRUE;
                }
                // If an intervening but different work by the same creators is in $this->inTextDoneIds, we don't use the subsequent template.
                // Commented out as not needed.
                /*				if (array_key_exists('creatorIds', $this->item))
                                {
                                    foreach($creatorIdStrings as $CISarray)
                                    {
                                        $CIScitationID = $CISarray[0];
                                        $CISstring = $CISarray[1];
                                        if ($CISstring == $this->item['creatorIds'])
                                        {
                                            if (($this->item['id'] == $CIScitationID))
                                                $trySubsequent = TRUE;
                                            else
                                                $trySubsequent = FALSE;
                                            break;
                                        }
                                    }
                                }
                */
                // If single subsequent citation later in the text, use subsequentCitationTemplate
                //				if (!$this->matchNameSplit && !$this->nameInSameSentence &&
                //					array_search($this->item['id'], $this->inTextDoneIds) !== FALSE)
                //				if ($trySubsequent && (array_search($this->item['id'], $this->inTextDoneIds) !== FALSE))
                if ($trySubsequent)
                {
                    if (isset($this->subsequentCreatorTemplate) &&
                        $this->checkTemplateFields($this->subsequentCreatorTemplate) &&
                        $this->subsequentSentenceCheck())
                    {
                        if (!isset($tempTemplate))
                        {
                            $tempTemplate = $this->template;
                        }
                        $this->template = $this->subsequentCreatorTemplate;
                        // Debugging
//$templateName = "SUBSEQUENTCREATOR";
//
                    }
                }
                // Remove title and shortTitle fields from template if either of those fields is in same sentence as citation
                $matchArray = [];
                if (array_key_exists('title', $this->item))
                {
                    $title = preg_quote(trim($this->item['mainTitle']));
                    $matchArray[] = "(&nbsp;){1}$title|\\s{1}$title";
                }
                if (array_key_exists('shortTitle', $this->item))
                {
                    $shortTitle = preg_quote(trim($this->item['shortTitle']));
                    $matchArray[] = "(&nbsp;){1}$shortTitle|\\s{1}$shortTitle";
                }
                if (!empty($matchArray))
                {
                    $match = preg_quote(implode('|', $matchArray), '/');
                    if (preg_match("/$match/iuU", $text))
                    {
                        if (array_key_exists('title', $this->template))
                        {
                            unset($this->template['title']);
                        }
                        if (array_key_exists('shortTitle', $this->template))
                        {
                            unset($this->template['shortTitle']);
                        }
                    }
                }
                $citation = $this->map($this->template);
                if ($citation == '')
                {
                    if ($this->pageSplitDone === FALSE)
                    {
                        $this->matchNameSplit = FALSE;
                    }
                }
            }
            $this->multipleCitations = FALSE;
            if (!empty($multiples))
            {
                $textAtom = implode('', $textAtoms);
                $citation = $this->multiple($multiples);
                $multiples = $textAtoms = [];
                $text .= $textAtom;
            }
            // APA-style split page number(s) from main citation.  $text may be several sentences and name to search for may appear several times.  We want to use the
            // last occurrence.
            // If $this->pageSplitDone === TRUE a citation for this resource has already been added in the same sentence.
            if ($this->pageSplitDone && ($this->pageSplitDone !== TRUE))
            {
                if ($this->matchNameSplitEtAl)
                {
                    $pattern = "/(" . preg_quote($this->matchNameSplit) . ".*" .
                    preg_quote($this->matchNameSplitEtAl) . ")/u";
                }
                else
                {
                    $pattern = "/(" . preg_quote($this->matchNameSplit) . ")/u";
                }
                if ($this->suwpExport || ($this->output == 'rtf'))
                {
                    $input = htmlspecialchars($text, ENT_QUOTES);
                }
                else
                {
                    $input = $text;
                }
                $split = preg_split($pattern, $input, -1, PREG_SPLIT_DELIM_CAPTURE);
                $lastText = array_pop($split);
                $lastText = array_pop($split) . ' ' . $this->pageSplitDone . $lastText;
                if (!$this->suwpExport && ($this->output != 'rtf'))
                {
                    $text = html_entity_decode(implode('', $split) . $lastText);
                }
            }
            if ($citation == '')
            {
                $pString .= $text;
            }
            else
            {
                $pString .= $text . ' ' .
                    $this->export->format($this->style['firstChars'] .
                    $preText . $citation . $postText . $this->export->format($this->style['lastChars']));
            }
            // reset
            $preText = $postText = FALSE;
            if (array_search($this->item['id'], $this->inTextDoneIds) === FALSE)
            {
                $this->inTextDoneIds[] = $this->item['id'];
            }
            if (array_key_exists('creatorIds', $this->item))
            {
                $creatorIdStrings[] = [$this->item['id'], $this->item['creatorIds']];
            }
            // Reset temporary replcement type template
            if (isset($this->$type))
            {
                $this->$type = $tempTypeTemplate;
            }
        }

        return $pString;
    }    /**
     * In-text style citations – Special start function for ooxml processing from e.g. a Word add-in
     *
     * @return string Complete string ready for printing to the output medium.
     */
    public function inTextStyleOoxml()
    {
        $pString = '';
        foreach ($this->items as $count => $this->item)
        {
            if (isset($tempTemplate))
            {
                $this->template = $tempTemplate;
                unset($tempTemplate);
            }
            $text = '';

            // Replacement templates for particular resource types.  Need to match this template to other replacement templates by removing fields if necessary
            $type = $this->item['type'] . "Template";
            if (isset($this->$type))
            {
                $tempTypeTemplate = $this->$type;
				if ($this->checkTemplateFields($this->$type))
				{
					$tempTemplate = $this->template;
					$this->template = $this->$type;
				}
            }
			// Remove title and shortTitle fields from template if either of those fields is in same sentence as citation
			$matchArray = [];
			if (array_key_exists('title', $this->item))
			{
				$title = preg_quote(trim($this->item['mainTitle']));
				$matchArray[] = "(&nbsp;){1}$title|\\s{1}$title";
			}
			if (array_key_exists('shortTitle', $this->item))
			{
				$shortTitle = preg_quote(trim($this->item['shortTitle']));
				$matchArray[] = "(&nbsp;){1}$shortTitle|\\s{1}$shortTitle";
			}
			if (!empty($matchArray))
			{
				$match = preg_quote(implode('|', $matchArray), '/');
				if (preg_match("/$match/iuU", $text))
				{
					if (array_key_exists('title', $this->template))
					{
						unset($this->template['title']);
					}
					if (array_key_exists('shortTitle', $this->template))
					{
						unset($this->template['shortTitle']);
					}
				}
			}
			$citation = $this->map($this->template);
			$pString .= $text . ' ' . $this->export->format($this->style['firstChars'] .
                $citation . $this->export->format($this->style['lastChars']));
            // Reset temporary replcement type template
            if (isset($this->$type))
            {
                $this->$type = $tempTypeTemplate;
            }
        }
        return $pString;
    }
    /**
     * Use subsequentTemplate only if creator surname, title or shortTitle exists in same sentence as citation
     *
     * @return bool
     */
    public function subsequentSentenceCheck()
    {
        if (!array_key_exists('subsequentFields', $this->style) || !$this->style['subsequentFields'])
        {
            return TRUE; // Use subsequentTemplate
        }
        if ($this->nameInSameSentence)
        {
            return TRUE; // Use subsequentTemplate
        }
        if (array_key_exists('mainTitle', $this->item))
        {
            $title = str_replace(['{', '}'], '', trim($this->item['mainTitle']));
            $title = preg_quote($title);
            //			$matchArray[] = "\.\s*(&nbsp;)*.*$title|\.(&nbsp;)*\s*.*$title";
            //			$matchArray[] = "\.{1}.*\s*(&nbsp;)*$title|\.{1}.*(&nbsp;)*\s*$title";
            //			$matchArray[] = "__WIKINDX__NEWLINEPAR____WIKINDX__NEWLINEPAR__.*\s*(&nbsp;)*$title";
            //			$matchArray[] = "(__WIKINDX__NEWLINEPAR____WIKINDX__NEWLINEPAR__).*(&nbsp;)*\s*$title";
            $matchArray[] = "$title(?!.*\\.|.*\\?|.*\\!|.*¿|.*¡)\\s*(&nbsp;)*";
            $matchArray[] = "$title(?!.*\\.|.*\\?|.*\\!|.*¿|.*¡)(&nbsp;)*\\s*";
        }
        if (array_key_exists('shortTitle', $this->item))
        {
            $shortTitle = str_replace(['{', '}'], '', trim($this->item['shortTitle']));
            $shortTitle = preg_quote($shortTitle);
            //			$shortTitle = preg_quote($this->item['shortTitle']);
            //			$matchArray[] = "\..*\s*(&nbsp;)*.*$shortTitle|\..*(&nbsp;)*\s*.*$shortTitle";
            $matchArray[] = "$shortTitle(?!.*\\.|.*\\?|.*\\!|.*¿|.*¡)\\s*(&nbsp;)*";
            $matchArray[] = "$shortTitle(?!.*\\.|.*\\?|.*\\!|.*¿|.*¡)(&nbsp;)*\\s*";
        }
        if (!empty($matchArray))
        {
            $match = implode('|', $matchArray);
            if (preg_match("/$match/iuUs", $this->item['text']))
            {
                return TRUE; // Use subsequentTemplate
            }
        }

        return FALSE; // Don't use subsequentTemplate
    }
    /**
     * Discover if creator name(s) is in same sentence and split citation if requested.
     *
     * @param string $text
     */
    public function sameSentence($text)
    {
        // Is this citation in the same sentence as the previous citation and for the same resource?
        if (($this->item['id'] == $this->previousNameInSameSentenceId) &&
            !preg_match("/^\\s*(&nbsp;)*\\.|(&nbsp;)*\\s*\\./Uu", $text))
        {
            $this->citationInSameSentence = $this->nameInSameSentence = $this->matchNameSplit = TRUE;

            return;
        }
        $possessive1 = $this->citationInSameSentence = FALSE;
        $storedSurname = $this->item['firstCreatorSurname'];
        $text = str_replace("&nbsp;", " ", $text);
        // strip BBCode, HTML and anything in quotation marks temporarily while splitting for the sentence
        $split = preg_split(
            $this->sentenceSplit,
            preg_replace($this->quotationMarker, "", preg_replace("/\\[.*\\]|\\[\\/.*\\]|<.*[>]+/Uus", "", $text))
        );
        //		$split = \UTF8\mb_explode('. ',
        //			preg_replace("/\[.*\]|\[\/.*\]|<.*[>]+/Uus", "", $text)); // strip BBCode and HTML temporarily
        $lastSplit = $split[count($split) - 1];
        // Perhaps we've split on the dot in 'et al.' or equivalent
        if ((mb_substr($this->textEtAl, -1) == '.') &&
            array_key_exists(count($split) - 2, $split) &&
            (mb_substr($split[count($split) - 2], -(mb_strlen($this->textEtAl) - 1)) ==
            preg_replace("/[.]$/u", '', $this->textEtAl)))
        {
            $this->matchNameSplitEtAl = ' ' . $this->textEtAl;
            $lastSplit = $split[count($split) - 2] . ' ' . $lastSplit;
        }
        // Citation tag may immediately follow 'creatorName et al.'
        elseif ((mb_substr($split[count($split) - 1], -(mb_strlen($this->textEtAl))) == $this->textEtAl))
        {
            $this->matchNameSplitEtAl = ' ' . $this->textEtAl;
            $patternsEnd[] = '(' . $storedSurname . $this->matchNameSplitEtAl . ')$';
        }
        elseif (!$this->matchNameSplitEtAl)
        {
            $patterns[] = "(" . $storedSurname . ' ' . $this->textEtAl . ")";
        }
        $lastSplit = trim($lastSplit);
        foreach ($this->possessiveArray as $poss)
        {
            $patterns[] = $possessive1 = '(' . $storedSurname . $poss . ')';
            $patternsEnd[] = $possessive1 . '$';
        }
        $surnamePattern = FALSE;
        $sizeSurname = count($this->creatorSurnames[$this->item['id']]);
        if ($sizeSurname > 1)
        {
            $surnamePattern = "(" .
                $this->creatorSurnames[$this->item['id']][0] . ".*?" .
                $this->creatorSurnames[$this->item['id']][--$sizeSurname];
            foreach ($this->possessiveArray as $poss)
            {
                $patterns[] = $poss = $surnamePattern . $poss . ")";
                $patternsEnd[] = $poss . '$';
            }
            $patterns[] = $poss = $surnamePattern . ")";
            $patternsEnd[] = $poss . '$';
        }
        $patterns[] = '(' . $storedSurname . ')';
        $pattern = implode("|", $patterns);
        $patternsEnd[] = $storedSurname . '$';
        $patternEnd = implode("|", $patternsEnd);
        if ($this->suwpExport || ($this->output == 'rtf'))
        {
            $input1 = $input2 = htmlspecialchars($lastSplit, ENT_QUOTES);
        }
        else
        {
            $input1 = $input2 = $lastSplit;
        }
        //$pattern = "(Grimshaw et al.)|(Grimshaw's)|(Grimshaw')|(Grimshaw)";
        if (preg_match("/\\b$pattern\\b/u", $input1, $matchName))
        {
            if (array_key_exists('followCreatorPageSplit', $this->style) &&
            !preg_match("/$patternEnd/u", $input2))
            {
                $this->matchNameSplit = $matchName[0];
            }
            $this->nameInSameSentence = TRUE;
            $this->previousNameInSameSentenceId = $this->item['id'];
        }
        else
        {
            $this->previousNameInSameSentenceId = FALSE;
        }
    }
    /**
     * For any replacement templates used for in-text citations, check we have fields to populate it with
     *
     * If not, return FALSE to indicate that we use original $this->template
     *
     * @param array $template
     *
     * @return bool
     */
    public function checkTemplateFields($template)
    {
        foreach ($template as $key => $value)
        {
            if (array_key_exists($key, $this->item) || ($key == 'preliminaryText'))
            {
                return TRUE; // use replacement template
            }
        }

        return FALSE; // use original template
    }
    /**
     * Disambiguate any ambiguous citations
     */
    public function disambiguate()
    {
        if (!$this->style['ambiguous'])
        { // do nothing
            return;
        }
        $ambiguousTitles = $this->yearsDisambiguated = $this->bibliographyIds = [];
        foreach ($this->items as $count => $item)
        {
            if (($this->style['ambiguous'] == 1) &&
            array_key_exists('title', $this->template) && array_key_exists('title', $item) &&
            array_key_exists('year', $this->template) && array_key_exists('year', $item) &&
            array_key_exists('creatorIds', $item))
            {
                foreach ($this->titles as $titleIndex => $title)
                {
                    if (($title != $item['title']) // not the same title
                    && array_key_exists($titleIndex, $this->creatorIds)
                    && ($this->creatorIds[$titleIndex] == $item['creatorIds']) // same creators
                    && ($this->years[$titleIndex] == $item['year']) // same year
                    && ($this->citationIds[$titleIndex] != $item['id']) // not the same citation ID
                    ) {
                        $identifier = str_replace(' ', '', $item['year'] . $item['creatorIds']);
                        if (!array_key_exists($identifier, $ambiguousTitles))
                        {
                            $ambiguousTitles[$identifier] = range('a', 'z'); // Start a new letter set
                        }
                        $this->bibliographyIds[$item['id']] = $identifier;

                        break;
                    }
                }
            }
            elseif (array_key_exists('year', $this->template) && array_key_exists('year', $item))
            {
                foreach ($this->years as $yearIndex => $year)
                {
                    if (array_key_exists('creatorIds', $item)
                    && array_key_exists($yearIndex, $this->creatorIds)
                    && ($year == $item['year']) // same year
                    && ($this->creatorIds[$yearIndex] == $item['creatorIds']) // same creators
                    && ($this->citationIds[$yearIndex] != $item['id']))
                    { // not the same citation ID
                        if ($this->style['ambiguous'] == 1)
                        { // add letter after year
                            $identifier = str_replace(' ', '', $year . $this->creatorIds[$yearIndex]);
                            if (!array_key_exists($identifier, $ambiguousTitles))
                            {
                                $ambiguousTitles[$identifier] = range('a', 'z'); // Start a new letter set
                            }
                            $this->bibliographyIds[$item['id']] = $identifier;
                        }
                        elseif ($this->style['ambiguous'] == 2)
                        { // add title and new template
                            $this->items[$count]['ambiguousTemplate'] = TRUE;
                        }

                        break;
                    }
                }
            }
        }
        foreach ($this->bibliographyIds as $id => $identifier)
        {
            if (!$identifier)
            {
                continue;
            }
            else
            {
                foreach ($this->items as $count => $item)
                {
                    if ($item['id'] == $id)
                    {
                        if (!array_key_exists($id, $this->yearsDisambiguated))
                        {
                            $letter = array_shift($ambiguousTitles[$identifier]);
                        }
                        $this->items[$count]['year'] .= $letter;
                        $this->yearsDisambiguated[$id] = $this->items[$count]['year'];
                    }
                }
            }
        }
        unset($this->titles);
        unset($this->pages);
        unset($this->years);
    }
    /**
     * Find alternate fields dependent on previous or following fields and deal with accordingly.
     *
     * @param array $typeArray
     */
    public function findAlternates($typeArray)
    {
        foreach ($typeArray as $field => $array)
        {
            if (is_array($array) && array_key_exists($field, $this->item))
            {
                if (array_key_exists('alternatePreFirst', $array) ||
                    array_key_exists('alternatePreSecond', $array))
                {
                    if (!$this->item[$field])
                    {
                        if (array_key_exists($array['alternatePreFirst'], $this->item))
                        {
                            unset($this->item[$array['alternatePreFirst']]);
                        }
                    }
                    else
                    {
                        if (array_key_exists($array['alternatePreSecond'], $this->item))
                        {
                            unset($this->item[$array['alternatePreSecond']]);
                        }
                    }
                }
                if (array_key_exists('alternatePostFirst', $array) ||
                    array_key_exists('alternatePostSecond', $array))
                {
                    if (!$this->item[$field])
                    {
                        if (array_key_exists($array['alternatePostFirst'], $this->item))
                        {
                            unset($this->item[$array['alternatePostFirst']]);
                        }
                    }
                    else
                    {
                        if (array_key_exists($array['alternatePostSecond'], $this->item))
                        {
                            unset($this->item[$array['alternatePostSecond']]);
                        }
                    }
                }
            }
        }
    }
    /**
     * Map the $item array against the style array and produce a string ready to be formatted for bold, italics etc.
     *
     * @param array $template
     *
     * @return string ready for printing to the output medium.
     */
    public function map($template)
    {
        $itemArray = [];
        $index = 0;
        $this->findAlternates($template);
        $ultimate = $preliminaryText = '';
        $previousFieldExists = $nextFieldExists = TRUE;
        $pageSplit = $nonPageFieldFound = $pageFieldFound = FALSE;
        if (array_key_exists('independent', $template))
        {
            $independent = $template['independent'];
        }
        /**
         * For dependency on next field, we must grab array keys of citation template, shift the first element then, in the loop,
         * check each element exists in $item.  If it doesn't, $nextFieldExists is set to FALSE
         */
        $checkPost = array_keys($template);
        array_shift($checkPost);
        foreach ($template as $key => $value)
        {
            if ($key == 'ultimate')
            {
                $ultimate = $value;

                continue;
            }
            if ($key == 'preliminaryText')
            {
                $preliminaryText = $value;

                continue;
            }
            if (!array_key_exists($key, $this->item) || !$this->item[$key])
            {
                $index++;
                array_shift($checkPost);
                $previousFieldExists = FALSE;

                continue;
            }
            if ($key != 'pages')
            {
                $nonPageFieldFound = TRUE;
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
            if ($this->matchNameSplit && ($key == 'pages'))
            { // If pages split has occurred, remove dependencies for pages.
                $pre = str_replace("__DEPENDENT_ON_PREVIOUS_FIELD__", '', $pre);
                $post = str_replace("__DEPENDENT_ON_PREVIOUS_FIELD__", '', $post);
            }
            else
            {
                if ($previousFieldExists && array_key_exists('dependentPre', $value))
                {
                    $pre = preg_replace(
                        "/__DEPENDENT_ON_PREVIOUS_FIELD__/u",
                        $value['dependentPre'],
                        $pre
                    );
                }
                elseif (array_key_exists('dependentPreAlternative', $value))
                {
                    $pre = preg_replace(
                        "/__DEPENDENT_ON_PREVIOUS_FIELD__/u",
                        $value['dependentPreAlternative'],
                        $pre
                    );
                }
                else
                {
                    $pre = preg_replace("/__DEPENDENT_ON_PREVIOUS_FIELD__/u", '', $pre);
                }
                if ($nextFieldExists && array_key_exists('dependentPost', $value))
                {
                    $post = str_replace(
                        "__DEPENDENT_ON_NEXT_FIELD__",
                        $value['dependentPost'],
                        $post
                    );
                }
                elseif (array_key_exists('dependentPostAlternative', $value))
                {
                    $post = preg_replace(
                        "/__DEPENDENT_ON_NEXT_FIELD__/u",
                        $value['dependentPostAlternative'],
                        $post
                    );
                }
                else
                {
                    $post = preg_replace("/__DEPENDENT_ON_NEXT_FIELD__/u", '', $post);
                }
            }
            /**
             * Deal with __SINGULAR_PLURAL__ for pages
             */
            if ($key == 'pages')
            {
                $pageFieldFound = $index;
                if (array_key_exists('pluralPagesExist', $this->item))
                { // plural alternative for this key
                    $pre = array_key_exists('plural', $value) ?
                        preg_replace("/__SINGULAR_PLURAL__/u", $value['plural'], $pre) : $pre;
                    $post = array_key_exists('plural', $value) ?
                        preg_replace("/__SINGULAR_PLURAL__/u", $value['plural'], $post) : $post;
                }
                else
                { // singular alternative for this key
                    $pre = array_key_exists('singular', $value) ?
                        preg_replace("/__SINGULAR_PLURAL__/u", $value['singular'], $pre) : $pre;
                    $post = array_key_exists('singular', $value) ?
                        preg_replace("/__SINGULAR_PLURAL__/u", $value['singular'], $post) : $post;
                }
                // Deal with en dash characters
                $this->item[$key] = $this->export->format($this->item[$key]);
            }
            /**
             * Make sure we don't have duplicate punctuation characters
             */
            $lastPre = mb_substr($post, -1);
            $firstItem = mb_substr($this->item[$key], 0, 1);
            if ($firstItem === $lastPre)
            {
                $this->item[$key] = mb_substr($this->item[$key], 1);
            }
            $firstPost = mb_substr($post, 0, 1);
            $lastItem = mb_substr($this->item[$key], -1);
            if (preg_match("/[.,;:?!]/u", $lastItem) && ($firstPost == $lastItem))
            {
                $post = mb_substr($post, 1); // take a guess at removing first character of $post
            }
            /**
             * Strip backticks used in template
             */
            $pre = str_replace("`", '', $pre);
            $post = str_replace("`", '', $post);
            if ($this->item[$key])
            {
                // Endnote style citations
                if (($key == 'id') && $this->style['citationStyle'])
                {
                    $itemArray[$index] = $this->formatCitationId($pre, $post);
                }
                else
                { // in-text citations
                    if ($this->matchNameSplit)
                    {
                        $pageSplit = $index;
                    }
                    $itemArray[$index] = $pre . $this->item[$key] . $post;
                }
            }
            $previousFieldExists = $nextFieldExists = TRUE;
            $index++;
        }
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
                $firstKey = array_shift($independentKeys);
                $secondKey = array_shift($independentKeys);
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
                    if (array_key_exists($firstKey - 1, $itemArray) && $preAlternative)
                    {
                        $itemArray[$firstKey - 1] .= $preAlternative;
                    }
                    if (array_key_exists($secondKey + 1, $itemArray) && $postAlternative)
                    {
                        $itemArray[$secondKey + 1] = $postAlternative .
                        $itemArray[$secondKey + 1];
                    }
                }
            }
        }
        $this->pageSplitDone = FALSE;
        // if (($pageSplit !== FALSE) && (count($itemArray) > 1))
        if (($pageSplit !== FALSE))
        {
            $index = $pageSplit;
            $pageSplit = $itemArray[$pageSplit];
            unset($itemArray[$index]);
            $this->pageSplitDone = TRUE;
            // Second citation from same resource in same sentence.
            if ($this->citationInSameSentence)
            {
                return trim($pageSplit);
            }
        }
        $pString = implode('', $itemArray);
        /**
         * if last character is punctuation (which it may be with missing fields etc.), and $ultimate is also
         * punctuation, remove last character.
         */
        if ($ultimate)
        {
            $last = mb_substr(trim($pString), -1);
            /**
             * Don't do ';' in case last element is URL with &gt; ...!
             */
            if (preg_match("/^[.,:?!]/u", $ultimate) && preg_match("/[.,:?!]/u", $last))
            {
                $pString = mb_substr(trim($pString), 0, -1);
            }
        }
        if ($this->pageSplitDone)
        {
            if ($pString)
            {
                $this->pageSplitDone = trim(trim($pString) . $ultimate);
                if (preg_match("/[.,;:?!]$/u", $this->pageSplitDone))
                {
                    $this->pageSplitDone = mb_substr($this->pageSplitDone, 0, -1);
                }
                $this->pageSplitDone = $this->export->format($this->style['firstChars'] .
                    $preliminaryText . $this->hyperLink($this->pageSplitDone) .
                    $this->style['lastChars']);
            }
            elseif ($pageFieldFound === FALSE)
            {
                $this->pageSplitDone = trim(trim($pageSplit) . $ultimate);
                if (preg_match("/[.,;:?!]$/u", $this->pageSplitDone))
                {
                    $this->pageSplitDone = mb_substr($this->pageSplitDone, 0, -1);
                }
                $this->pageSplitDone = $this->export->format($this->style['firstChars'] .
                    $preliminaryText . $this->hyperLink($this->pageSplitDone) .
                    $this->style['lastChars']);
                $pageSplit = '';
            }
            else
            {
                $this->pageSplitDone = '';
            }

            return trim($pageSplit);
        }

        return $this->hyperLink($preliminaryText . trim($pString) . $ultimate);
    }
    /**
     * Format the citation ID for endnote-style citations
     *
     * @param string $pre pre-characters
     * @param string $post post-characters
     *
     * @return string
     */
    public function formatCitationId($pre, $post)
    {
        if ($this->style['endnoteStyle'] == 1)
        { // Endnotes, same ids
            $id = $this->endnoteSameIdsArray[$this->item['id']];
        }
        else
        { // different incrementing ids (endnotes or footnotes)
            $id = $this->id;
        }
        if ($this->output != 'rtf')
        {
            return $pre . $id . $post;
        }
        // First create the RTF stylesheet if not already done
        if (!$this->styleSheet)
        {
            $this->styleSheet = $this->createStyleSheet();
        }
        // Now formatting for RTF output
        $preId = $this->style['firstCharsEndnoteID'];
        $postId = $this->style['lastCharsEndnoteID'];
        // Format of ID in endnotes/footnotes
        if ($this->style['formatEndnoteID'] == 1)
        { // superscript
            $preIDFormat = "{\\super";
            $postIDFormat = '}';
        }
        elseif ($this->style['formatEndnoteID'] == 2)
        { // subscript
            $preIDFormat = "{\\sub";
            $postIDFormat = '}';
        }
        else
        {
            $preIDFormat = '';
            $postIDFormat = '';
        }
        // RTF output.
        // NB - Word and OO.org will print endnotes in the order in which they are presented in the text.  This is fine for different incrementing ids but bad when the ids
        // follow a specified bibliography order; in this case, they are likely not to be in incrementing order in the text.  If, in the text, endnote ids are in the order 4, 6, 1, 3, 2, 5
        // for example, they will print out in the endnotes in that order if we use RTF's default endnote formatting.  If this is the case, we need to provide fake endnotes as a
        // plain string to be appended to the final RTF output.
        if ($this->wikindx)
        {
            $session = new SESSION();
            // WIKINDX-specific:  Indentation of appended bibliography
            if ($session->getVar("exportPaper_indentBib") == 'indentAll')
            {
                $bf = "\\li720 ";
            }
            elseif ($session->getVar("exportPaper_indentBib") == 'indentFL')
            {
                $bf = "\\fi720 ";
            }
            elseif ($session->getVar("exportPaper_indentBib") == 'indentNotFL')
            {
                $bf = "\\li720\\fi-720 ";
            }
            else
            {
                $bf = "\\li1\\fi1 ";
            }
            // WIKINDX-specific:  Line spacing of appended bibliography
            if ($session->getVar("exportPaper_spaceBib") == 'oneHalfSpace')
            {
                $bf = "\\pard\\plain $bf\\sl360\\slmult1 ";
            }
            elseif ($session->getVar("exportPaper_spaceBib") == 'doubleSpace')
            {
                $bf = "\\pard\\plain $bf\\sl480\\slmult1 ";
            }
            else
            {
                $bf = "\\pard\\plain$bf";
            }
            // WIKINDX-specific:  Indentation of footnotes
            if ($session->getVar("exportPaper_indentFt") == 'indentAll')
            {
                $ftf = "\\li720 ";
            }
            elseif ($session->getVar("exportPaper_indentFt") == 'indentFL')
            {
                $ftf = "\\fi720 ";
            }
            elseif ($session->getVar("exportPaper_indentFt") == 'indentNotFL')
            {
                $ftf = "\\li720\\fi-720 ";
            }
            else
            {
                $ftf = "\\li1\\fi1 ";
            }
            // WIKINDX-specific:  Line spacing of footnotes
            if ($session->getVar("exportPaper_spaceFt") == 'oneHalfSpace')
            {
                $ftf = "\\pard\\plain $ftf\\sl360\\slmult1 ";
            }
            elseif ($session->getVar("exportPaper_spaceFt") == 'doubleSpace')
            {
                $ftf = "\\pard\\plain $ftf\\sl480\\slmult1 ";
            }
            else
            {
                $ftf = "\\pard\\plain$ftf";
            }
        }
        else
        {
            $bf = $ftf = "\\pard\\plain ";
        }
        // END WIKINDX-specific
        if ($this->style['endnoteStyle'] == 0)
        { // Endnotes, incrementing ids
            $citation = "{\\cs2 $preId\\chftn $postId}$postIDFormat{__OSBIB__ENDNOTE__$id}";
            $endnoteString = "{\\footnote\\ftnalt$bf\\s2\\ql$preIDFormat" . $citation
                . "}}__WIKINDX__NEWLINE__";

            return "{\\cs2 \\chftn" . $endnoteString;
        }
        elseif ($this->style['endnoteStyle'] == 1)
        { // Endnotes, same ids
            if (array_search($id, $this->rtfDoneIds) === FALSE)
            {
                $this->rtfDoneIds[] = $id;
                if (array_key_exists('sameIdOrderBib', $this->style))
                { // provide fake endnotes
                    $citation = "$preIDFormat{\\cs2 $preId$id$postId}$postIDFormat{__OSBIB__ENDNOTE__$id}";
                    $this->endnoteStringArray[$id] = $bf . $citation;
                    if ($this->style['formatEndnoteInText'] == 1)
                    {
                        return "{\\cs2\\super $id}__WIKINDX__NEWLINE__";
                    }
                    elseif ($this->style['formatEndnoteInText'] == 2)
                    {
                        return "{\\cs2\\sub $id}__WIKINDX__NEWLINE__";
                    }
                    else
                    {
                        return "{\\cs2\\plain $id}__WIKINDX__NEWLINE__";
                    }
                }
                else
                { // Not following bibliography order
                    $citation = "{\\cs2 $preId$id$postId}$postIDFormat{__OSBIB__ENDNOTE__$id}";
                    $endnoteString = "{\\footnote\\ftnalt$bf\\s2\\ql$preIDFormat" . $citation .
                        "}}__WIKINDX__NEWLINE__";

                    return "{\\cs2 $id" . $endnoteString;
                }
            }
            else
            {
                $citation = "{\\cs2 $preId$id$postId}{__OSBIB__ENDNOTE__$id}";
                $endnoteString = "{\\footnote\\ftnalt$bf\\s2\\ql " . $citation . "}}__WIKINDX__NEWLINE__";

                return "{\\cs2 $id}";
            }
        }
        elseif ($this->style['endnoteStyle'] == 2)
        { // Footnotes, incrementing ids
            $citation = "{\\cs2 $preId\\chftn $postId}{__OSBIB__ENDNOTE__$id}";
            $endnoteString = "{\\footnote$ftf\\s2\\ql " . $citation . "}}__WIKINDX__NEWLINE__";

            return "{\\cs2 \\chftn" . $endnoteString;
        }
    }
    /**
     * Create RTF stylesheet
     *
     * @return string
     */
    public function createStyleSheet()
    {
        // Stylesheet for hyperlinks, list bullets and endnotes
        // \s16 for endnote text (in the body of the paper)
        // \cs2 .. \cs18 for ordered endnotes
        // \cs19 .. \cs21 for unordered endnotes
        // \s22 for footnote text (in the body of the paper)
        // \cs22 .. \cs24 for footnotes
        if (!$this->wikindx)
        {
            return FALSE;
        }
        $session = new SESSION();
        $styleSheet = "{\\stylesheet\n
{\\*\\cs1 Hyperlink;}\n
{\\*\\cs1 Bullet Symbols;}\n
{\\*\\cs1 Numbering Symbols;}" . LF;
        // WIKINDX-specific: RTF page numbering
        if ($session->getVar("exportPaper_pageNumber"))
        { // 0 == no. 1 == footer, 2 == header
            $styleSheet .= "{\\cs17 \\additive \\sbasedon10 page number;}" . LF;
        }
        // Set up RTF settings for endnotes and footnotes
        if ($this->style['formatEndnoteInText'] == 1)
        {
            $format = "\\super";
            if ($this->style['endnoteStyle'] == 2)
            { // footnotes
                $styleSheet .= "{\\cs10\\super Footnote anchor;}" . LF;
            }
            else
            {
                $styleSheet .= "{\\cs10\\super Endnote anchor;}" . LF;
            }
        }
        elseif ($this->style['formatEndnoteInText'] == 2)
        {
            $format = "\\sub";
            if ($this->style['endnoteStyle'] == 2)
            { // footnotes
                $styleSheet .= "{\\cs10\\sub Footnote anchor;}" . LF;
            }
            else
            {
                $styleSheet .= "{\\cs10\\sub Endnote anchor;}" . LF;
            }
        }
        else
        {
            $format = "\\plain";
            if ($this->style['endnoteStyle'] == 2)
            { // footnotes
                $styleSheet .= "{\\cs10 Footnote anchor;}" . LF;
            }
            else
            {
                $styleSheet .= "{\\cs10 Endnote anchor;}" . LF;
            }
        }
        /* RTF notes:
        /fet0 produces lowercase roman numerals
        /fet1 produces arabic numerals
        */
        if ($session->getVar("exportPaper_sectionFtRestart"))
        {
            $ft = "\\ftnrestart";
        }
        else
        {
            $ft = "";
        }
        if ($this->style['endnoteStyle'] == 0)
        { // Incrementing endnotes
            $styleSheet .= "{\\s2\\ql \\sbasedon0 endnote text;}" . LF;
            $styleSheet .= "{\\cs2 \\additive $format \\sbasedon10 endnote reference;}" . LF;
            $styleSheet .= "}\n\n$ft\\aftnnar\\fet1\n" . LF;
        }
        elseif ($this->style['endnoteStyle'] == 1)
        { // Endnotes, same ids
            $styleSheet .= "{\\s2\\ql \\sbasedon0 endnote text;}" . LF;
            $styleSheet .= "{\\cs2 $format \\sbasedon10 endnote reference;}" . LF;
            $styleSheet .= "}\n\n$ft\\aftnnar\\fet1\n" . LF;
        }
        elseif ($this->style['endnoteStyle'] == 2)
        { // Incrementing footnotes
            $styleSheet .= "{\\s2\\ql \\sbasedon0 footnote text;}" . LF;
            $styleSheet .= "{\\cs2 \\additive $format \\sbasedon10 footnote reference;}" . LF;
            $styleSheet .= "}\n\n$ft\\aftnnar\\ftnbj\\fet1\n" . LF;
        }
        elseif (!$this->style['citationStyle'])
        { // in-text citations
            $styleSheet .= "{\\cs2 \\additive \\super \\sbasedon10 endnote reference;}" . LF;
        }
        $styleSheet .= $this->rtfPageNumbers($session);

        return $styleSheet;
    }
    /**
     * Format RTF Page numbers
     *
     * @param object $session
     *
     * @return false|string
     */
    public function rtfPageNumbers($session)
    {
        // WIKINDX-specific: Page numbering
        if ($pn = $session->getVar("exportPaper_pageNumber"))
        { // 0 == no, 1 == footer, 2 == header
            $pna = $session->getVar("exportPaper_pageNumberAlign"); // 0 == centre, 1 == left, 2 == right
            if ($pna == 1)
            {
                $pna = "\\ql";
            }
            elseif ($pna == 2)
            {
                $pna = "\\qr";
            }
            else
            {
                $pna = "\\qc";
            }
            if ($pn == 1)
            {
                return "{\\footer $pna{\\field{\\fldinst {\\cs17  PAGE }}}{\\par }}\n" . LF;
            }
            else
            {
                return "{\\header $pna{\\field{\\fldinst {\\cs17  PAGE }}}{\\par }}\n" . LF;
            }
        }

        return FALSE;
    }
    /**
     * Format hyperlinks and clean up citation
     *
     * @param string $citation
     *
     * @return string ready for printing to the output medium.
     */
    public function hyperLink($citation)
    {
        // Ensure we have no preliminary punctuation left over
        $citation = trim(preg_replace("/^\\s*[.,;:]\\s*/Uu", "", $citation));
        if (!$citation)
        {
            return $citation;
        }
        if ($this->hyperlinkBase)
        {
            $citation = \HTML\a(
                "link",
                $this->export->format(trim($citation)),
                $this->hyperlinkBase . $this->item['id']
            );
        }

        return $citation;
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
     * @param int $citationId
     */
    public function formatNames($creators, $citationId)
    {
        if ($this->bibStyle->bibformat->citationFootnote)
        { // footnotes
            list($pString, $creatorIds) = $this->bibStyle->bibformat->formatNames($creators, 'creator1', TRUE);
            $this->citationIds[$this->count] = $citationId;
            $this->creatorIds[$this->count] = implode(',', $creatorIds);
            $this->items[$this->count]['creator'] = $this->creators[$this->count] = $pString;
            $this->items[$this->count]['creatorIds'] = $this->creatorIds[$this->count];

            return;
        }
        $style = $this->style;
        $first = TRUE;
        /**
         * Set default plural behaviour for creator lists
         */
        $pluralKey = "creator_plural";
        $this->$pluralKey = FALSE;
        $initialsStyle = 'creatorInitials';
        $firstNameInitial = 'creatorFirstName';
        $delimitTwo = 'twoCreatorsSep';
        $delimitFirstBetween = 'creatorSepFirstBetween';
        $delimitNextBetween = 'creatorSepNextBetween';
        $delimitLast = 'creatorSepNextLast';
        $uppercase = 'creatorUppercase';
        if (array_search($citationId, $this->citationIds) !== FALSE)
        {
            $list = 'creatorListSubsequent';
            $limit = 'creatorListSubsequentLimit';
            $moreThan = 'creatorListSubsequentMore';
            $abbreviation = 'creatorListSubsequentAbbreviation';
            $italics = 'creatorListSubsequentAbbreviationItalic';
        }
        else
        {
            $list = 'creatorList';
            $limit = 'creatorListLimit';
            $moreThan = 'creatorListMore';
            $abbreviation = 'creatorListAbbreviation';
            $italics = 'creatorListAbbreviationItalic';
        }
        // cache surnames
        foreach ($creators as $creatorIndex => $creator)
        {
            $surnames[$creatorIndex] = $creator['surname'];
        }
        if (!$this->style['citationStyle'] && // in-text style
            !array_key_exists($citationId, $this->creatorSurnames))
        {
            foreach ($creators as $creatorIndex => $creator)
            {
                $this->creatorSurnames[$citationId][] = $creator['surname'];
            }
        }
        foreach ($creators as $creatorIndex => $creator)
        {
            if (!$this->preview)
            {
                $creatorIds[] = $creator['creatorId'];
            }
            if ($first)
            {
                $nameStyle = 'creatorStyle';
                $this->items[$this->count]['firstCreatorSurname'] = $creator['surname'];
                $first = FALSE;
            }
            else
            {
                $nameStyle = 'creatorOtherStyle';
            }
            $firstName = trim($this->checkInitials(
                $creator,
                $style[$initialsStyle],
                $style[$firstNameInitial]
            ));
            $prefix = $creator['prefix'] ? trim($creator['prefix']) . ' ' : '';
            if ($style[$nameStyle] == 0)
            { // Joe Bloggs
                $nameString = $firstName . ' ' . $prefix . $creator['surname'];
            }
            elseif ($style[$nameStyle] == 1)
            { // Bloggs, Joe
                $prefixDelimit = $firstName ? ', ' : '';
                $nameString = $creator['prefix'] . ' ' . $creator['surname'] . $prefixDelimit . $firstName;
            }
            elseif ($style[$nameStyle] == 2)
            { // Bloggs Joe
                $nameString = $creator['prefix'] . ' ' . $creator['surname'] . ' ' . $firstName;
            }
            else
            { // Last name only
                // Distinguish between creators of the same surname within the same citation?
                $useInitials = FALSE;
                if (array_key_exists('useInitials', $this->style))
                {
                    foreach ($surnames as $surnameIndex => $surname)
                    {
                        if (($creator['surname'] == $surname) && ($surnameIndex != $creatorIndex))
                        {
                            if ($style[$list] && $style[$limit]
                            && ($surnameIndex < $style[$moreThan])
                            && ($creatorIndex < $style[$moreThan]))
                            {
                                $useInitials = TRUE;

                                break;
                            }
                        }
                    }
                }
                if ($useInitials)
                {
                    $nameString = $creator['prefix'] . ' ' . $creator['surname'] . ' ' . $firstName;
                }
                else
                {
                    $nameString = $creator['prefix'] . ' ' . $creator['surname'];
                }
            }
            if (isset($style[$uppercase]))
            {
                $nameString = mb_strtoupper($nameString);
            }
            $cArray[] = trim($nameString);
        }
        /**
         * Keep only some elements in array if we've exceeded $moreThan
         */
        $etAl = FALSE;
        if ($style[$list] && $style[$limit] && (count($cArray) > $style[$moreThan]))
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
            $pluralKey = "creator_plural";
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
        // Cache citation IDs
        $this->citationIds[$this->count] = $citationId;
        $this->items[$this->count]['creator'] = $this->creators[$this->count] = $pString;
        if (!$this->preview)
        {
            $this->creatorIds[$this->count] = implode(',', $creatorIds);
            $this->items[$this->count]['creatorIds'] = $this->creatorIds[$this->count];
        }
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
        return $this->bibStyle->bibformat->checkInitials($creator, $initialsStyle, $firstNameInitial);
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
                if ($firstTime)
                {
                    $firstNameInitialMake = mb_strtoupper(mb_substr(trim($name), 0, 1));
                    $firstTime = FALSE;
                }
                else
                {
                    $initials[] = mb_strtoupper(mb_substr(trim($name), 0, 1));
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
                return $firstNameInitialMake . '.';
            }

            return ''; // nothing here
        }
        $initialsArray = \UTF8\mb_explode(' ', $creator['initials']);
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
     * Format a shortTitle.  Anything enclosed in $delimitLeft...$delimitRight is to be left unchanged
     *
     * @param string $pString Raw title string.
     * @param string $delimitLeft Default FALSE
     * @param string $delimitRight Default FALSE
     */
    public function formatShortTitle($pString, $delimitLeft = FALSE, $delimitRight = FALSE)
    {
        $title = BIBFORMAT::titleCapitalization($pString, $delimitLeft, $delimitRight, $this->style['titleCapitalization']);
        $title = preg_replace("/{(.*)}/Uu", "$1", $title);

        $this->items[$this->count]['shortTitle'] = $title;
        $this->titles[$this->count] = $this->items[$this->count]['shortTitle'];
    }
    /**
     * Format a title.  Anything enclosed in $delimitLeft...$delimitRight is to be left unchanged
     *
     * @param string $pString Raw title string.
     * @param string $delimitLeft Default FALSE
     * @param string $delimitRight Default FALSE
     */
    public function formatTitle($pString, $delimitLeft = FALSE, $delimitRight = FALSE)
    {
        $split = \UTF8\mb_explode($this->style['titleSubtitleSeparator'], $pString);
        if (is_array($split)) {
	        $this->items[$this->count]['mainTitle'] = $split[0];
	    } else {
	    	$this->items[$this->count]['mainTitle'] = $pString;
	    }

        $title = BIBFORMAT::titleCapitalization($pString, $delimitLeft, $delimitRight, $this->style['titleCapitalization']);
        $title = preg_replace("/{(.*)}/Uu", "$1", $title);

        $this->items[$this->count]['title'] = $title;
        $this->titles[$this->count] = $this->items[$this->count]['title'];
    }
    /**
     * Format URL.
     *
     * @param string $url
     */
    public function formatUrl($url)
    {
        $this->items[$this->count]['URL'] = $url;
    }
    /**
     * Format preText and postText.
     * [cite]23:34-35|see ` for example[/cite] (as used by WIKINDX)
     *
     * @param string $preText
     * @param string $postText
     */
    public function formatPrePostText($preText, $postText)
    {
        $this->items[$this->count]['preText'] = $preText;
        $this->items[$this->count]['postText'] = $postText;
    }
    /**
     * Format pages.
     * $this->style['pageFormat']:
     * 0 == 132-9
     * 1 == 132-39
     * 2 == 132-139
     *
     * @param string $start Page start.
     * @param string $end Page end.
     */
    public function formatPages($start, $end = FALSE)
    {
        $style = $this->style;
        /**
         * If no page end, return just $start;
         */
        if (!$end)
        {
            $this->items[$this->count]['pages'] = $start;
            $this->pages[$this->count] = $this->items[$this->count]['pages'];

            return;
        }
        /**
         * Pages may be in roman numeral format etc.  Return unchanged
         */
        if (!is_numeric($start))
        {
            $this->items[$this->count]['pages'] = $start . 'WIKINDX_NDASH' . $end;
            $this->pages[$this->count] = $this->items[$this->count]['pages'];

            return;
        }
        /**
         * We have multiple pages...
         */
        $this->items[$this->count]['pluralPagesExist'] = TRUE;
        /**
         * They've done something wrong so give them back exactly what they entered
         */
        if (($end <= $start) || (mb_strlen($end) < mb_strlen($start)))
        {
            $this->items[$this->count]['pages'] = $start . 'WIKINDX_NDASH' . $end;
            $this->pages[$this->count] = $this->items[$this->count]['pages'];

            return;
        }
        elseif ($style['pageFormat'] == 2)
        {
            $this->items[$this->count]['pages'] = $start . 'WIKINDX_NDASH' . $end;
            $this->pages[$this->count] = $this->items[$this->count]['pages'];

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
                        $this->items[$this->count]['pages'] = $start . 'WIKINDX_NDASH' . $endPage;
                        $this->pages[$this->count] = $this->items[$this->count]['pages'];

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
                $this->items[$this->count]['pages'] = $start . 'WIKINDX_NDASH' . $end;
                $this->pages[$this->count] = $this->items[$this->count]['pages'];

                return;
            }
        }
        /**
         * We should never reach here - in case we do, give back complete range so that something at least is printed
         */
        $this->items[$this->count]['pages'] = $start . 'WIKINDX_NDASH' . $end;
        $this->pages[$this->count] = $this->items[$this->count]['pages'];
    }
    /**
     * Format publication year.
     * $this->style['yearFormat']:
     * 0 == 1998
     * 1 == '98
     * 2 == 98
     *
     * @param string $year
     */
    public function formatYear($year)
    {
        if (!$year)
        {
            $this->items[$this->count]['year'] = $this->years[$this->count] = $this->style['replaceYear'];

            return;
        }
        if (!$this->style['yearFormat'])
        { // 1998
            $this->items[$this->count]['year'] = $year;
        }
        elseif ($this->style['yearFormat'] == 1)
        { // '98
            $this->items[$this->count]['year'] = "'" . mb_substr($year, -2, 2);
        }
        elseif ($this->style['yearFormat'] == 2)
        { // 98
            $this->items[$this->count]['year'] = mb_substr($year, -2, 2);
        }
        $this->years[$this->count] = $this->items[$this->count]['year'];
    }
    /**
     * Format multiple citations
     *
     * @param array $multiples Citations
     *
     * @return string
     */
    public function multiple($multiples)
    {
        $first = TRUE;
        foreach ($multiples as $index => $citation)
        {
            if ($first)
            {
                $text = $citation;
                $first = FALSE;

                continue;
            }
            if ($this->style['citationStyle'])
            { // Endote-style citations
                $separator = $this->style['consecutiveCitationEndnoteInTextSep'];
            }
            else
            {
                if (!$first && array_search($index, $this->consecutiveCreatorSep) !== FALSE)
                {
                    $separator = $this->style['consecutiveCreatorSep'];
                }
                else
                {
                    $separator = $this->style['consecutiveCitationSep'];
                }
            }
            $text .= $separator . $citation;
        }

        return $text;
    }
    /**
     * Collate and format the bibliography for endnote-style citations.  Must be processed in the same order as $ids.
     * Where the id nos. are the same for each resource (endnote-style citations), store the bibliographic id order with an incrementing citation id no.
     *
     * @param array $rows multiple array of raw bibliographic data to be processed by $this->bibStyle
     * @param array $ids resource ids giving order of processing bibliography
     */
    public function processEndnoteBibliography($rows, $ids)
    {
        $this->export = new EXPORTFILTER($this, $this->output);
        $process = $this->bibStyleProcess;
        if (isset($this->pages))
        {
            $pages = $this->pages;
        }
        if ($this->style['citationStyle'])
        { // endnote-style
            // id numbers follow bibliography order for same ids
            if (($this->style['endnoteStyle'] == 1) && array_key_exists('sameIdOrderBib', $this->style))
            {
                $index = 1;
                foreach ($rows as $id => $row)
                {
                    $this->endnoteCitations[$id] = $this->endnoteRemovePunc($this->bibStyle->$process($row));
                    $this->endnoteSameIdsArray[$id] = $index;
                    ++$index;
                }
                // WIKINDX specific - create ID numbers for footnotes in the SUWP
                if ($this->wikindx && isset($this->footnoteOffsets) && !empty($this->footnoteOffsets))
                {
                    foreach ($this->footnoteOffsets as $null)
                    {
                        $this->footnoteOffsetIds[] = $index;
                        ++$index;
                    }
                }
            }
            else
            {
                $endnoteSameIds = 1;
                if ($this->style['endnoteStyle'] == 1)
                { // Endnotes, same ids
                    // WIKINDX specific - create ID numbers for footnotes in the SUWP
                    if ($this->wikindx && isset($this->footnoteOffsets) &&
                        !empty($this->citeOffsets) && !empty($this->footnoteOffsets))
                    {
                        $citeOffsets = $idsDone = [];
                        foreach ($ids as $id)
                        {
                            if (array_search($id, $idsDone) === FALSE)
                            {
                                $idsDone[] = $id;
                                $citeOffsets[] = array_shift($this->citeOffsets);
                            }
                            else
                            { // discard this offset
                                array_shift($this->citeOffsets);
                            }
                        }
                    }
                    foreach (array_unique($ids) as $id)
                    {
                        if (!array_key_exists($id, $this->endnoteCitations))
                        { // don't have this one
                            $this->endnoteCitations[$id] =
                            $this->endnoteRemovePunc($this->bibStyle->$process($rows[$id]));
                        }
                        // WIKINDX specific - create ID numbers for footnotes in the SUWP
                        if ($this->wikindx && isset($this->footnoteOffsets) &&
                            !empty($citeOffsets) && !empty($this->footnoteOffsets))
                        {
                            if ($this->footnoteOffsets[0][1] < $citeOffsets[0][1])
                            {
                                $this->footnoteOffsetIds[] = $endnoteSameIds;
                                array_shift($this->footnoteOffsets);
                                ++$endnoteSameIds;
                            }
                            array_shift($citeOffsets);
                        }
                        if (!array_key_exists($id, $this->endnoteSameIdsArray))
                        {
                            $this->endnoteSameIdsArray[$id] = $endnoteSameIds;
                            ++$endnoteSameIds;
                        }
                    }
                    // WIKINDX specific - create ID numbers for footnotes in the SUWP
                    if ($this->wikindx && isset($this->footnoteOffsets) && !empty($this->footnoteOffsets))
                    {
                        foreach ($this->footnoteOffsets as $null)
                        {
                            $this->footnoteOffsetIds[] = $endnoteSameIds;
                            ++$endnoteSameIds;
                        }
                    }
                }
                else
                {
                    $index = 1;
                    foreach ($ids as $id)
                    {
                        if (isset($pages) && array_key_exists($index, $this->pages) &&
                        ($this->style['endnoteStyle'] == 2))
                        { // footnotes
                            $this->bibStyle->bibformat->footnotePages = $this->export->format($pages[$index]);
                        }
                        $this->endnoteCitations[] =
                            $this->endnoteRemovePunc($this->bibStyle->$process($rows[$id]));
                        if (!array_key_exists($id, $this->endnoteSameIdsArray))
                        {
                            $this->endnoteSameIdsArray[$id] = $endnoteSameIds;
                            ++$endnoteSameIds;
                        }
                        $this->bibStyle->bibformat->footnotePages = FALSE;
                        ++$index;
                    }
                }
            }
        }
    }
    /**
     * Removing trailing spaces and punctuation for endnote-style bibliographic entries.
     *
     * @param string $entry
     *
     * @return string
     */
    public function endnoteRemovePunc($entry)
    {
        // probably don't want to remove trailing punctuation so currently just trim
        return trim($entry);

        return preg_replace("/[.,;:?!]$/u", '', trim($entry));
    }
    /**
     * Format the bibliography for in-text-style citations.
     *
     * @param array $row array of raw bibliographic data for one resource to be processed by $this->bibStyle
     */
    public function processIntextBibliography($row)
    {
        $process = $this->bibStyleProcess;
        if ($this->output == 'html')
        {
            $this->intextBibliography[] = str_replace("&nbsp;", ' ', $this->bibStyle->$process($row));
        }
        else
        {
            $this->intextBibliography[] = $this->bibStyle->$process($row);
        }
    }
    /**
     * Collate the bibliography array for in-text-style citations.
     *
     * @return string
     */
    public function collateIntextBibliography()
    {
        if ($this->rtfBibExport)
        {
            $pString = implode($this->export->newLine, $this->intextBibliography);
        }
        else
        {
            $pString = $this->export->newLine . $this->export->newLine .
                implode($this->export->newLine, $this->intextBibliography);
        }
        if (($this->output == 'rtf') && !$this->rtfBibExport)
        { // add a page break if not exporting to a bibliography
            return "\n\\page\n$pString";
        }
        elseif (($this->output == 'rtf'))
        { // add a paragraph after bibliography
            return "\n\\par $pString\n\\par" . LF;
        }

        return $pString;
    }
    /**
     * Print the bibliography for endnote-style citations.
     *
     * @param string $pString
     *
     * @return string
     */
    public function printEndnoteBibliography($pString)
    {
        $this->endnoteProcess();
        if ($this->output == 'html')
        {
            $pre = $post = FALSE;
            $pre .= $this->export->format($this->style['firstCharsEndnoteID']);
            $post = $this->export->format($this->style['lastCharsEndnoteID']) . $post;
            $endnotes = '';
            foreach ($this->endnotes as $index => $string)
            {
                $endnotes .= $pre . $index . $post . $string . $this->export->newLine;
            }
            $pString .= $this->export->newLine . $this->export->newLine . $endnotes;
        }
        elseif ($this->output == 'rtf')
        {
            if (!empty($this->endnoteStringArray))
            {
                ksort($this->endnoteStringArray);
                foreach ($this->endnoteStringArray as $index => $string)
                {
                    $match = "__OSBIB__ENDNOTE__$index}";
                    $this->endnoteString .= str_replace($match, $this->endnotes[$index], $string) . "}\\par";
                }
                $pString .= "\\par\\par\\par\\par" . LF . $this->endnoteString;
            }
            else
            {
                foreach ($this->endnotes as $index => $string)
                {
                    $match = "__OSBIB__ENDNOTE__$index}";
                    $pString = str_replace($match, $string . "}", $pString);
                }
            }
        }
        else
        {
            $endnotes = '';
            foreach ($this->endnotes as $index => $string)
            {
                $endnotes .= $index . '. ' . $string . $this->export->newLine;
            }
            $pString .= $this->export->newLine . $this->export->newLine . $endnotes;
        }
        // Turn off footnote templating in bibformat
        $this->bibStyle->bibformat->citationFootnote = FALSE;
        $this->bibStyle->bibformat->output = $this->output;

        return $pString;
    }
    /**
     * Format the endnotes for endnote-style citations
     *
     * @return string
     */
    public function endnoteProcess()
    {
        if (!isset($this->ids))
        {
            return;
        }
        $endnoteSameIdsArray = array_flip($this->endnoteSameIdsArray);
        $doneIds = [];
        $citationIndex = 1;
        foreach ($this->ids as $index => $id)
        {
            $this->item = [];
            // We're using the same ID number for citations from the same resource
            if ($this->style['endnoteStyle'] == 1)
            {
                if (array_key_exists($id, $doneIds))
                {
                    continue;
                }
                else
                {
                    $doneIds[$id] = TRUE;
                }
                // Use specified bibliographic order?
                if (array_key_exists('sameIdOrderBib', $this->style))
                {
                    $id = array_shift($endnoteSameIdsArray);
                    $this->item['id'] = $id;
                    $this->item['citation'] = $this->endnoteCitations[$id];
                }
                else
                {
                    $this->item['id'] = $id;
                    $this->item['citation'] = $this->endnoteCitations[$id];
                    if (array_search($citationIndex, $this->footnoteOffsetIds) !== FALSE)
                    {
                        ++$citationIndex; // index already taken by textual footnote
                    }
                }
                $this->endnotes[$citationIndex] = $this->export->format($this->map($this->templateEndnote), TRUE);
                ++$citationIndex;
            }
            else
            {
                $tempTemplate = [];
                $size = count($this->opCit);
                $this->item['id'] = $this->id = $index;
                if (array_key_exists('pluralPagesExist', $this->items[$index]))
                {
                    $this->item['pluralPagesExist'] = $this->items[$index]['pluralPagesExist'];
                }
                if (array_key_exists('pages', $this->items[$index]))
                {
                    $this->item['pages'] = $thesePages = $this->items[$index]['pages'];
                    $this->item['pages'] = $this->export->format($this->item['pages']);
                }
                else
                {
                    $thesePages = FALSE;
                }
                if ($this->style['idem'] && $size && ($this->opCit[$size - 1] == $id) &&
                    isset($lastPages) && ($thesePages == $lastPages) && !empty($this->templateIbid))
                {
                    if (array_key_exists('citation', $this->templateIbid))
                    {
                        $this->item['citation'] = array_shift($this->endnoteCitations);
                    }
                    else
                    {
                        if (array_key_exists('creator', $this->templateIbid))
                        {
                            $this->item['creator'] = $this->creators[$index];
                        }
                        if (array_key_exists('year', $this->templateIbid))
                        {
                            $this->item['year'] = $this->years[$index];
                        }
                        if (array_key_exists('title', $this->templateIbid))
                        {
                            $this->item['title'] = $this->titles[$index];
                        }
                        array_shift($this->endnoteCitations);
                    }
                    $tempTemplate = $this->templateEndnote;
                    $this->templateEndnote = $this->templateIbid;
                }
                elseif ($this->style['idem'] && $size && ($this->opCit[$size - 1] == $id) &&
                    isset($lastPages) && ($thesePages != $lastPages) && !empty($this->templateIdem))
                {
                    if (array_key_exists('citation', $this->templateIdem))
                    {
                        $this->item['citation'] = array_shift($this->endnoteCitations);
                    }
                    else
                    {
                        if (array_key_exists('creator', $this->templateIdem))
                        {
                            $this->item['creator'] = $this->creators[$index];
                        }
                        if (array_key_exists('year', $this->templateIdem))
                        {
                            $this->item['year'] = $this->years[$index];
                        }
                        if (array_key_exists('title', $this->templateIdem))
                        {
                            $this->item['title'] = $this->titles[$index];
                        }
                        array_shift($this->endnoteCitations);
                    }
                    $tempTemplate = $this->templateEndnote;
                    $this->templateEndnote = $this->templateIdem;
                }
                elseif ($this->style['opCit'] && $size && (array_search($id, $this->opCit) !== FALSE) &&
                 !empty($this->templateOpCit))
                {
                    if (array_key_exists('citation', $this->templateOpCit))
                    {
                        $this->item['citation'] = array_shift($this->endnoteCitations);
                    }
                    else
                    {
                        if (array_key_exists('creator', $this->templateOpCit))
                        {
                            $this->item['creator'] = $this->creators[$index];
                        }
                        if (array_key_exists('year', $this->templateOpCit))
                        {
                            $this->item['year'] = $this->years[$index];
                        }
                        if (array_key_exists('title', $this->templateOpCit))
                        {
                            $this->item['title'] = $this->titles[$index];
                        }
                        array_shift($this->endnoteCitations);
                    }
                    $tempTemplate = $this->templateEndnote;
                    $this->templateEndnote = $this->templateOpCit;
                }
                else
                {
                    if (array_key_exists('citation', $this->templateEndnote))
                    {
                        $this->item['citation'] = array_shift($this->endnoteCitations);
                    }
                    else
                    {
                        if (array_key_exists('creator', $this->templateEndnote))
                        {
                            $this->item['creator'] = $this->creators[$index];
                        }
                        if (array_key_exists('year', $this->templateEndnote))
                        {
                            $this->item['year'] = $this->years[$index];
                        }
                        if (array_key_exists('title', $this->templateEndnote))
                        {
                            $this->item['title'] = $this->titles[$index];
                        }
                    }
                }
                $lastPages = $thesePages;
                // If footnotes, uses 'pages' formatting from footnote template
                if ($this->style['citationStyle'] && ($this->style['endnoteStyle'] == 2) &&
                array_key_exists($this->items[$index]['type'], $this->bibStyle->bibformat->footnoteTypeArray))
                {
                    $type = $this->bibStyle->bibformat->footnoteTypeArray[$this->items[$index]['type']];
                    if (isset($this->bibStyle->bibformat->{$type})
                    && array_key_exists('pages', $this->bibStyle->bibformat->{$type})
                    && array_key_exists('pages', $this->templateEndnote))
                    {
                        $this->templateEndnote['pages'] = $this->bibStyle->bibformat->{$type}['pages'];
                    }
                }
                $this->endnotes[$index] = $this->export->format($this->map($this->templateEndnote), TRUE);
                $this->opCit[] = $id;
                if (!empty($tempTemplate))
                {
                    $this->templateEndnote = $tempTemplate;
                }
            }
        }

        return $this->export->newLine . $this->export->newLine . implode($this->export->newLine, $this->endnotes);
    }
    /**
     * Localisations etc.
     */
    public function loadArrays()
    {
        // Defaults
        $this->sentenceSplit = "/(?<!\\.|[ .][A-Z])\\. /u";
        if ($this->wikindx)
        {
            $this->quotationMarker = "/\".*\"|__WIKINDX__QUOTEINDENTDONE__.*__WIKINDX__QUOTEINDENTDONE__/us";
        }
        else
        {
            $this->quotationMarker = "/\".*\"/us";
        }
        // Scan for occurrences of creator name(s) followed by 'et al.' when checking if surname(s) is in same sentence as citation.
        // e.g. Grimshaw et al. state "blah blah blah" [cite]123:45-46[/cite].
        // Similarly, check for possessive form of a single creator name in the same sentence.  English has two forms (the second below for names that end in 's').
        // If there is no equivalent possessive form in another language, set these to FALSE.
        // From WIKINDX v3.4.7 onwards, $this->possessiveArray is used allowing unlimited possessive forms.  If it exists and is an array, possessive1 and possessive2
        // will be ignored.
        $this->possessiveArray[] = "'s";
        $this->possessiveArray[] = "'";
        $this->textEtAl = 'et al.';
        $styleLoc = FALSE;
        // WIKINDX-specific.  Months depend on the localisation set in the bibliographic style file.  'et al.' depends on the user's wikindx localisation.
        if ($this->wikindx)
        {
            $styleLoc = TRUE;
            // User localisation
            $this->wikindxLanguageClass = FACTORY_CONSTANTS::getInstance();
            if ($styleLoc)
            {
                if (isset($this->wikindxLanguageClass->textEtAl))
                {
                    $this->textEtAl = $this->wikindxLanguageClass->textEtAl;
                }
                if (isset($this->wikindxLanguageClass->possessiveArray) &&
                    is_array($this->wikindxLanguageClass->possessiveArray))
                {
                    $this->possessiveArray = $this->wikindxLanguageClass->possessiveArray;
                }
                else
                {
                    if (isset($this->wikindxLanguageClass->possessive1))
                    {
                        $possessiveArray[] = $this->wikindxLanguageClass->possessive1;
                    }
                    if (isset($this->wikindxLanguageClass->possessive2))
                    {
                        $possessiveArray[] = $this->wikindxLanguageClass->possessive2;
                    }
                    if (isset($possessiveArray))
                    {
                        $this->possessiveArray = $possessiveArray;
                        unset($possessiveArray);
                    }
                    // else use defaults above.
                }
                if (isset($this->wikindxLanguageClass->startQuotation) &&
                    isset($this->wikindxLanguageClass->endQuotation))
                {
                    $this->quotationMarker =
                        "/" . preg_quote($this->wikindxLanguageClass->startQuotation) . ".*" .
                        preg_quote($this->wikindxLanguageClass->endQuotation) .
                        "|__WIKINDX__QUOTEINDENTDONE__.*__WIKINDX__QUOTEINDENTDONE__/s";
                }
                if (isset($this->wikindxLanguageClass->abbreviations))
                {
                    foreach ($this->wikindxLanguageClass->abbreviations as $abbrev)
                    {
                        $temp[] = '\s' . preg_quote($abbrev);
                    }
                    if (isset($temp))
                    {
                        $abbreviations = "(?<!" . implode('|', $temp) . "|\\.|[ .][A-Z])";
                        $this->sentenceSplit = "/$abbreviations\\. /";
                    }
                }
            }

            return;
        }
        // Defaults.  Any localisation in external files as above should follow this format.
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
    }
    /**
     * Transform the raw data from the XML file into usable arrays
     */
    private function getStyle()
    {
        $this->style = $this->bibStyle->parsexml->citation;
        $this->footnoteStyle = $this->bibStyle->parsexml->footnote;
        if ($this->style['citationStyle'])
        { // Endnote style citations
            $this->citationToArrayEndnoteStyle();
        }
        else
        { // In-text style citations
            $this->citationToArrayInTextStyle();
        }
        // If endnote-style citations, need to ensure we get endnote references with BBCode intact and not parsed by bibformat()
        if ($this->style['citationStyle'])
        {
            $this->bibStyle->bibformat->output = 'noScan';
        }
        if ($this->style['citationStyle'] && ($this->style['endnoteStyle'] == 2))
        { // footnotes
            $this->bibStyle->bibformat->citationFootnote = TRUE;
        }
        /**
         * Load localisations etc.
         */
        $this->loadArrays();
    }
}
