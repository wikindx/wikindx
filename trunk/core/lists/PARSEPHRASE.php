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
 * PARSEPHRASE -- break up a search phrase into component parts such as 'exact phrase', AND, OR and NOT
 *
 * @package wikindx\core\lists
 */
class PARSEPHRASE
{
    /** boolean */
    public $idea = FALSE;
    /** boolean */
    public $validSearch = TRUE;
    /** array */
    public $ors = [];
    /** array */
    public $ands = [];
    /** array */
    public $nots = [];
    /** array */
    public $orsFT = [];
    /** array */
    public $andsFT = [];
    /** array */
    public $notsFT = [];
    /** object */
    private $db;
    /** object */
    private $session;
    /** object */
    private $errors;
    /** array */
    private $stringArray;
    /** array */
    private $NOTfragments;
    /** booolean */
    private $attachmentSearch;
    /** array */
    private $useRegex;

    /**
     * PARSEPHRASE
     */
    public function __construct()
    {
        $this->session = FACTORY_SESSION::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
    }
    /**
     * Parse input and create SQL condition
     *
     * @param array $input $input['Word'] is the input string from the search form
     * @param bool $test if TRUE formats the string for the natural language section of SEARCH.php. Default is FALSE
     * @param bool $stringSearch if TRUE formats the string for searching attachments in SEARCH.php. Default is FALSE
     * @param bool $attachmentSearch if TRUE manages the use of wildcards for attachment searches
     * @param bool $fulltext Default is FALSE. If TRUE, return a string formatted for FULLTEXT, else a string formatted for LIKE/REGEXP.
     *
     * @return mixed FALSE|string
     */
    public function parse($input, $test = FALSE, $stringSearch = FALSE, $attachmentSearch = FALSE, $fulltext = FALSE)
    {
        $this->stringArray = [];
        $this->NOTfragments = [];
        $this->useRegex = [];
        $this->attachmentSearch = $attachmentSearch;
        // Check for malformed or empty $phrase
        if (!array_key_exists('Word', $input)) {
            $this->validSearch = FALSE;

            return ' 1 = 0 ';
        }
        // check for equal no. double quotes
        if (substr_count($input['Word'], '"') % 2) { // i.e. odd number
            $this->validSearch = FALSE;

            return \HTML\color($this->errors->text("inputError", "invalid"), 'redText');
        }
        // check for valid use of wildcards
        if (substr_count($input['Word'], '**') || substr_count($input['Word'], '??')) {
            $this->validSearch = FALSE;

            return \HTML\color($this->errors->text("inputError", "invalid"), 'redText');
        }
        $phrase = str_replace('"', 'WIKINDXDOUBLEQUOTEWIKINDX', $input['Word']);
        $phrase = preg_quote(stripslashes(trim($phrase)), '/');
        if (!$phrase || !$this->malformedString($phrase)) {
            $this->validSearch = FALSE;

            return \HTML\color($this->errors->text("inputError", "invalid"), 'redText');
        }
        // remove all punctuation (keep wildcard characters, apostrophe and dash for names such as Grimshaw-Aagaard and D'Eath)
        $phrase = preg_replace('/[^\p{L}\p{N}\s\*\?\-\'\/]/u', '', $phrase);
        $phrase = str_replace('WIKINDXDOUBLEQUOTEWIKINDX', '"', $phrase);
        if (!$phrase = $this->tidySearch($phrase)) {
            $this->validSearch = FALSE;

            return \HTML\color($this->errors->text("inputError", "invalid"), 'redText');
        }
        // split up search string on single spaces -- NB, multiple spaces will result in empty elements
        $fragments = $this->splitSpaces(trim($phrase));
        // loop through $fragments sorting into 'exact phrase', AND fragments, NOT fragments and OR fragments
        $this->sortFragments($fragments);
//        $this->removeNot_and_FilterWords(); // probably not needed
        if (empty($this->stringArray)) {
            $this->validSearch = FALSE;

            return \HTML\color($this->errors->text("inputError", "invalid"), 'redText');
        }
        if ($fulltext && !$test) {
            $partial = array_key_exists('Partial', $input) ? TRUE : FALSE;

            return $this->createConditionFT($input['Word'], $partial);
        }
        if (is_bool($stringSearch) && $stringSearch) {
            return $this->stringArray;
        }

        return $this->createCondition($input, $test);
    }
    /**
     * Create the condition clause for FULL TEXT and store search highlighting in the session
     *
     * @param string $input
     * @param bool $partial
     *
     * @return string
     */
    private function createConditionFT($input, $partial)
    {
        $searchHighlight = [];
        // Remove leading control words AND and OR
        if (strpos($input, 'AND ') === 0) {
            $input = substr_replace($input, '', 0, 4);
        } elseif (strpos($input, 'OR ') === 0) {
            $input = substr_replace($input, '', 0, 3);
        }
        $input = preg_quote($input, '/');
        $split = preg_split('/(AND|NOT|OR)/', $input, NULL, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $count = 0;
        $singleFirst = $notFirst = FALSE;
        $phrase = '';
        $lastControl = FALSE;
        foreach ($split as $fragment) {
            $fragment = stripslashes(trim($fragment));
            if (!$fragment || ($fragment == 'OR')) {
                $lastControl = FALSE;

                continue;
            } elseif ($fragment == 'NOT') {
                if ($count > 0) {
                    if (($count == 2) && $singleFirst && !$notFirst) {
                        $phrase = '+' . $phrase;
                    }
                } else {
                    $notFirst = TRUE;
                }
                $phrase .= '-';
                $lastControl = 'NOT';

                continue;
            } elseif ($fragment == 'AND') {
                if ($count > 0) {
                    if (($count == 2) && $singleFirst && !$notFirst) {
                        $phrase = '+' . $phrase;
                    }
                    $phrase .= '+';
                }
                $lastControl = 'AND';

                continue;
            }
            $searchHighlight[] = $fragment;
            // From now on, dealing with words
            if (strpos($fragment, ' ')) { // multiple words
                $frags = [];
                $openExact = FALSE;
                foreach (explode(' ', $fragment) as $frag) {
                    if (!$frag) {
                        continue;
                    }
                    // Remove user's wildcards if not in an exact phrase
                    if (strpos($frag, '"') === 0) {
                        $openExact = TRUE;
                    }
                    if (!$openExact) {
                        $frag = str_replace('?', '', $frag);
                        $frag = str_replace('*', '', $frag);
                        if ($partial) {
                            $frag .= '*';
                        }
                    }
                    if (strpos(strrev($frag), '"') === 0) {
                        $openExact = FALSE;
                    }
                    $frags[] = $frag;
                }
                if ((strpos($frag, '"') === FALSE) && ((!$count && $notFirst) || $count)) {
                    $string = '(' . implode(' ', $frags) . ') ';
                    $phrase .= $string;
                } else {
                    $string = '(' . implode(' ', $frags) . ') ';
                    $phrase .= $string;
                }
            } else {
                if (strpos($fragment, '"') === FALSE) {
                    $fragment = str_replace('?', '', $fragment);
                    $fragment = str_replace('*', '', $fragment);
                    if ($partial) {
                        $fragment = trim($fragment) . '*';
                    }
                }
                if (!$count) {
                    $singleFirst = TRUE;
                }
                $string = $fragment . ' ';
                $phrase .= $string;
            }
            switch ($lastControl) {
                case 'AND':
                    $this->andsFT[] = '+' . trim($string);

                    break;
                case 'NOT':
                    $this->notsFT[] = '+' . trim($string); // '+' is correct – in QUICKSEARCH an array of IDs comes from doing an AND on these words
                    break;
                default:
                        $this->orsFT[] = trim($string);

                    break;
            }
            ++$count;
        }
        if (!empty($this->orsFT)) {
            if (((strpos($this->orsFT[0], '"') === 0) && (strpos(strrev($this->orsFT[0]), '"') === 0)) ||
                (count(explode(' ', $this->orsFT[0])) == 1)) { // single OR so add to $this->andsFT
                array_unshift($this->andsFT, array_pop($this->orsFT));
            }
        }
        if (!empty($this->andsFT) && (strpos($this->andsFT[0], '+') === FALSE)) {
            $this->andsFT[0] = '+' . $this->andsFT[0];
        }
        if (!$notFirst && (sizeof($this->andsFT) == 1) && (strpos($this->andsFT[0], '+') === 0)) {
            $this->orsFT[] = ltrim(array_pop($this->andsFT), '+');
        }
        if (!$this->idea) {
            $storedHighlight = UTF8::mb_explode(',', $this->session->getVar("search_Highlight"));
            $searchHighlight = array_unique(array_merge($storedHighlight, $searchHighlight));
            $this->session->setVar("search_Highlight", implode(",", $searchHighlight));
        } else {
            $storedHighlight = UTF8::mb_explode(',', $this->session->getVar("search_HighlightIdea"));
            $searchHighlight = array_unique(array_merge($storedHighlight, $searchHighlight));
            $this->session->setVar("search_HighlightIdea", implode(",", $searchHighlight));
        }

        return trim($phrase);
    }
    /**
     * Create the condition clause formatted for LIKE/REGEXP and store search highlighting in the session
     *
     * @param array $input
     * @param bool $test If TRUE, we are just testing the parsing
     * @param bool $fulltext If TRUE, return a string formatted for FULLTEXT, else a string formatted for LIKE/REGEXP.
     *
     * @return string
     */
    private function createCondition($input, $test)
    {
        $count = 0;
        $lastType = FALSE;
        $conditions = $searchHighlight = $conditionsJoin = $clauseOr = [];
        foreach ($this->stringArray as $wordArray) {
            // temporarily prepend '!WIKINDXFIELDWIKINDX!' which will later be replaced by the actual field name to search on.
            // Each type that equals AND means we must bracket until the next AND or the end of the search -- thus including any
            // exact phrases and OR statements in the bracket.  NOT statements are treated as AND statements.
            // We always start the search with an AND clause -- parsePhrase()
            // does not allow the search word phrase to start with OR or NOT.
            $string = '';
            $type = array_shift($wordArray);
            $highlightSearch = $search = array_shift($wordArray);
            $search = addslashes($search);
            // As of v4.2.2 (Sept 2013), 'Partial' is set TRUE in QUICKSEARCH
            if ((array_key_exists('Partial', $input) && array_key_exists($count, $this->useRegex) && !$this->useRegex[$count])
            &&
                (($type == 'AND') || ($type == 'OR') || ($type == 'NOT'))) {
                if ($type == 'NOT') {
                    if (!$test) {
                        $conditions[] = '!WIKINDXFIELDWIKINDX! ' . $this->db->like('%', $search, '%', TRUE);
                    } else {
                        $search = \HTML\color('?' . $search . '?', 'redtext');
                        $conditions[] = '!WIKINDXFIELDWIKINDX! ' . 'DOES NOT CONTAIN ' . $search;
                    }
                    $lastType = 'NOT';
                    $conditionsJoin[] = 'AND';
                } elseif ($type == 'OR') {
                    if ($lastType == 'NOT') {
                        if (!$test) {
                            $clauseOr[] = '!WIKINDXFIELDWIKINDX! ' . $this->db->like('%', $search, '%', TRUE);
                        } else {
                            $search = \HTML\color('?' . $search . '?', 'redtext');
                            $clauseOr[] = '!WIKINDXFIELDWIKINDX! ' . 'DOES NOT CONTAIN ' . $search;
                        }
                    } else {
                        if (!$test) {
                            $clauseOr[] = '!WIKINDXFIELDWIKINDX! ' . $this->db->like('%', $search, '%');
                        } else {
                            $search = \HTML\color('?' . $search . '?', 'redtext');
                            $clauseOr[] = '!WIKINDXFIELDWIKINDX! ' . 'CONTAINS ' . $search;
                        }
                    }
                } elseif ($type == 'AND') {
                    if (!$test) {
                        $conditions[] = '!WIKINDXFIELDWIKINDX! ' . $this->db->like('%', $search, '%');
                    } else {
                        $search = \HTML\color('?' . $search . '?', 'redtext');
                        $conditions[] = '!WIKINDXFIELDWIKINDX! ' . 'CONTAINS ' . $search;
                    }
                    $lastType = 'AND';
                    $conditionsJoin[] = 'AND';
                }
            } else {
                if (($type == 'NOT') || ($type == 'exactNOT')) {
                    if (!$test) {
                        $conditions[] = '!WIKINDXFIELDWIKINDX! ' . $this->db->regexp('[[:<:]]', $search, '[[:>:]]', TRUE);
                    } else {
                        if ($type == 'exactNOT') {
                            $search = \HTML\color('"' . $search . '"', 'redtext');
                            $conditions[] = '!WIKINDXFIELDWIKINDX! ' . 'DOES NOT CONTAIN ' . $search;
                        } else {
                            $conditions[] = '!WIKINDXFIELDWIKINDX! ' . 'DOES NOT CONTAIN ' . \HTML\color($search, 'redtext');
                        }
                    }
                    $lastType = 'NOT';
                    $conditionsJoin[] = 'AND';
                } elseif (($type == 'OR') || ($type == 'exactOR')) {
                    if ($lastType == 'NOT') {
                        if (!$test) {
                            $clauseOr[] = '!WIKINDXFIELDWIKINDX! ' . $this->db->regexp('[[:<:]]', $search, '[[:>:]]', TRUE);
                        } else {
                            if ($type == 'exactOR') {
                                $search = \HTML\color('"' . $search . '"', 'redtext');
                                $clauseOr[] = '!WIKINDXFIELDWIKINDX! ' . 'DOES NOT CONTAIN ' . $search;
                            } else {
                                $clauseOr[] = '!WIKINDXFIELDWIKINDX! ' . 'DOES NOT CONTAIN ' . \HTML\color($search, 'redtext');
                            }
                        }
                    } else {
                        if (!$test) {
                            $clauseOr[] = '!WIKINDXFIELDWIKINDX! ' . $this->db->regexp('[[:<:]]', $search, '[[:>:]]');
                        } else {
                            if ($type == 'exactOR') {
                                $search = \HTML\color('"' . $search . '"', 'redtext');
                                $clauseOr[] = '!WIKINDXFIELDWIKINDX! ' . 'CONTAINS ' . $search;
                            } else {
                                $clauseOr[] = '!WIKINDXFIELDWIKINDX! ' . 'CONTAINS ' . \HTML\color($search, 'redtext');
                            }
                        }
                    }
                } elseif (($type == 'AND') || ($type == 'exactAND')) {
                    if (!$test) {
                        $conditions[] = '!WIKINDXFIELDWIKINDX! ' . $this->db->regexp('[[:<:]]', $search, '[[:>:]]');
                    } else {
                        if ($type == 'exactAND') {
                            $search = \HTML\color('"' . $search . '"', 'redtext');
                            $conditions[] = '!WIKINDXFIELDWIKINDX! ' . 'CONTAINS ' . $search;
                        } else {
                            $conditions[] = '!WIKINDXFIELDWIKINDX! ' . 'CONTAINS ' . \HTML\color($search, 'redtext');
                        }
                    }
                    $lastType = 'AND';
                    $conditionsJoin[] = 'AND';
                }
            }
            if (!empty($clauseOr)) {
                if (!empty($conditions)) {
                    $conditions[] = $this->db->delimit(array_pop($conditions) . $this->db->or . implode($this->db->or, $clauseOr), 'parentheses');
                } else {
                    $conditions[] = $this->db->delimit(implode($this->db->or, $clauseOr), 'parentheses');
                }
                $clauseOr = [];
            }
            ++$count;
            if ($lastType != 'NOT') {
                $searchHighlight[] = $highlightSearch;
            }
        }
        if (!$this->idea) {
            $storedHighlight = UTF8::mb_explode(',', $this->session->getVar("search_Highlight"));
            $searchHighlight = array_unique(array_merge($storedHighlight, $searchHighlight));
            $this->session->setVar("search_Highlight", implode(",", $searchHighlight));
        } else {
            $storedHighlight = UTF8::mb_explode(',', $this->session->getVar("search_HighlightIdea"));
            $searchHighlight = array_unique(array_merge($storedHighlight, $searchHighlight));
            $this->session->setVar("search_HighlightIdea", implode(",", $searchHighlight));
        }
        $sizeof = count($conditions);
        $count = 0;
        $multipleORs = FALSE;
        foreach ($conditions as $condition) {
            $sizeof--;
            if (!empty($conditionsJoin)) {
                if (array_shift($conditionsJoin) == 'AND') {
                    if ($sizeof == 0) { // last loop through
                        $stringArray[] = $condition;
                    } else {
                        $stringArray[] = $condition . ' ' . $this->db->and;
                    }
                }
            } else {
                $stringArray[] = $condition;
            }
            if (!$test) {
                if (strpos($condition, ' NOT ') !== FALSE) {
                    if (!$multipleORs && !empty($this->ors)) {
                        $this->ands[] = array_pop($this->ors);
                    }
                    $this->nots[] = str_replace(' NOT ', ' ', $condition);
                } elseif (strpos($condition, ' AND ') !== FALSE) {
                    $this->ands[] = str_replace(' AND ', ' ', $condition);
                } elseif ($count) { // AND
                    if (!$multipleORs && !empty($this->ors)) {
                        $this->ands[] = array_pop($this->ors);
                    }
                    $this->ands[] = $condition;
                } else { // OR
                    if (strpos($condition, ' OR ') !== FALSE) {
                        $multipleORs = TRUE;
                    }
                    $this->ors[] = $condition;
                }
            }
            ++$count;
        }

        return implode(' ', $stringArray);
    }
    /**
     * loop through $fragments sorting into 'exact phrase', AND fragments, NOT fragments and OR fragments
     *
     * @param array $fragments
     */
    private function sortFragments($fragments)
    {
        $searchArrayIndex = 0;
        while (!empty($fragments)) {
            $fragment = array_shift($fragments);
            // one word in quotes?
            if ((mb_strpos($fragment, '"') === 0) && (mb_strpos($fragment, '"', 1) === mb_strlen($fragment) - 1)) {
                $this->stringArray[$searchArrayIndex]['type'] = 'exactOR'; // treat this word as an OR clause
                $this->stringArray[$searchArrayIndex]['string'] = trim($fragment, '"');
            }
            // AND keyword
            elseif (($fragment === 'AND') && (count($fragments))) {
                $this->stringArray[$searchArrayIndex]['type'] = 'AND';
                $this->tidyFragment($fragment, $fragments, $searchArrayIndex, 'AND');
            }
            // OR keyword
            elseif (($fragment === 'OR') && (count($fragments))) {
                $this->stringArray[$searchArrayIndex]['type'] = 'OR';
                $this->tidyFragment($fragment, $fragments, $searchArrayIndex, 'OR');
            }
            // NOT keyword
            elseif (($fragment === 'NOT') && (count($fragments))) {
                $this->stringArray[$searchArrayIndex]['type'] = 'NOT';
                $this->tidyFragment($fragment, $fragments, $searchArrayIndex, 'NOT');
            }
            // start of exact phrase search?
            elseif (mb_strpos($fragment, '"') === 0) {
                $this->stringArray[$searchArrayIndex]['type'] = 'exactOR';
                $this->extractExactPhrase($fragment, $fragments, $searchArrayIndex);
            }
            // everthing else, treat as OR <word> -- default behaviour
            else {
                $this->stringArray[$searchArrayIndex]['type'] = 'OR';
                $this->tidyFragment($fragment, $fragments, $searchArrayIndex, 'noKeyword');
            }
            if (!array_key_exists('string', $this->stringArray[$searchArrayIndex]) || !$this->stringArray[$searchArrayIndex]['string']) {
                unset($this->stringArray[$searchArrayIndex]);
            }
            ++$searchArrayIndex;
        }
        // first phrase must be an AND phrase
        if ($this->stringArray[0]['type'] == 'OR') {
            $this->stringArray[0]['type'] = 'AND';
        }
    }
    /**
     * For all AND, OR or NOT fragments, tidy the input so we have something usable
     *
     * @param string $fragment
     * @param array $fragments Reference to array
     * @param int $searchArrayIndex
     * @param string $type Default is FALSE
     */
    private function tidyFragment($fragment, &$fragments, $searchArrayIndex, $type = FALSE)
    {
        if (count($fragments) && ($type != 'noKeyword')) {
            do {
                $fragment = array_shift($fragments);
            } while (!$fragment);
        }
        // one word in quotes?
        if ((mb_strpos($fragment, '"') === 0) && (mb_strpos($fragment, '"', 1) === mb_strlen($fragment) - 1)) {
            if ($type == 'noKeyword') {
                $type = 'AND';
            }
            $this->stringArray[$searchArrayIndex]['type'] = 'exact' . $type;
            $this->stringArray[$searchArrayIndex]['string'] = trim($fragment, '"');
        } elseif (mb_strpos($fragment, '"') === 0) { // keyword is followed by exact phrase
            if ($type == 'noKeyword') {
                $type = 'AND';
            }
            $this->stringArray[$searchArrayIndex]['type'] = 'exact' . $type;
            $this->extractExactPhrase($fragment, $fragments, $searchArrayIndex);
        } else {
            if ($fragment = preg_replace("/[^\\pL\\pN\\p{Zs}'-][!\\*\\?]/u", '', $fragment)) { // remove useless characters (leave wildcards)
                // convert to MySQL wildcards: ? = '_', * = '%' if required
                if (!$this->attachmentSearch) { //leave '? and '*' for attachment searches
                    // NB. For wildcards, we always use regex
                    $this->stringArray[$searchArrayIndex]['string'] = preg_replace(
                        ["/\\*/u", "/\\?/u"],
                        ["[[:alnum:]]*", "[[:alnum:]]?"],
                        $fragment,
                        -1,
                        $count
                    );
                    if ($count) {
                        $this->useRegex[$searchArrayIndex] = TRUE;
                    } else {
                        $this->useRegex[$searchArrayIndex] = FALSE;
                    }
                } else {
                    $this->stringArray[$searchArrayIndex]['string'] = $fragment;
                }
            }
            if ($type == 'NOT') {
                $this->NOTfragments[] = $fragment;
            }
        }
    }
    /**
     * extract all fragment elements until end of exact phrase is found
     *
     * @param string $fragment
     * @param array $fragments Reference to array
     * @param int $searchArrayIndex
     */
    private function extractExactPhrase($fragment, &$fragments, $searchArrayIndex)
    {
        $this->stringArray[$searchArrayIndex]['string'] = ltrim($fragment, '"'); // start of exact phrase
        $extracted = FALSE;
        while (!$extracted) {
            $fragment = array_shift($fragments);
            if (!$fragment) { // extra space -- must keep for exact phrase
                $this->stringArray[$searchArrayIndex]['string'] .= ' ' . $fragment;
            }
            if (mb_strpos($fragment, '"') === mb_strlen($fragment) - 1) { // end of exact phrase
                $this->stringArray[$searchArrayIndex]['string'] .= ' ' . rtrim($fragment, '"');
                $extracted = TRUE;
            } else {
                $this->stringArray[$searchArrayIndex]['string'] .= ' ' . $fragment;
            }
        }
    }
    /**
     * split up search string on single spaces
     *
     * @param string $phrase
     *
     * @return string
     */
    private function splitSpaces($phrase)
    {
        return UTF8::mb_explode(' ', $phrase);
    }
    /**
     * check for malformed search strings -- i.e. odd no. of " chars
     *
     * @param string $phrase
     *
     * @return bool
     */
    private function malformedString($phrase)
    {
        // odd no. of " chars
        if (mb_substr_count($phrase, '"') % 2) {
            return FALSE;
        }
        // Reserved word
        if (mb_strpos($phrase, '!WIKINDXFIELDWIKINDX!') !== FALSE) {
            return FALSE;
        }
        // discard empty exact phrases and phrases where the final quote is not followed by a space
        elseif (mb_substr_count($phrase, '"')) {
            preg_match_all('/"/u', $phrase, $matches, PREG_OFFSET_CAPTURE);
            $count = 0;
            foreach ($matches as $array) {
                foreach ($array as $key => $null) {
                    $offset = $array[$key][1] + 1;
                    if ($offset == mb_strlen($phrase)) { // reached end of search phrase
                        return TRUE;
                    }
                    // 2nd of a pair of quotes not at the end of the phrase and not followed by space
                    if (($count % 2) && (mb_substr($phrase, $offset, 1) != ' ')) {
                        return FALSE;
                    }
                    ++$count;
                }
            }
        }

        return TRUE;
    }
    /**
     * Return words governed by AND, OR or NOT
     *
     * @param string $phrase
     *
     * @return string
     */
    private function tidySearch($phrase)
    {
        if (preg_match('/^AND(.*)|^OR(.*)|(.*)AND$|(.*)OR$|(.*)NOT$/u', $phrase, $matches)) {
            return array_pop($matches);
        }

        return $phrase;
    }
    /**
     * Remove any NOT words that are listed as AND or OR and any words to be filtered
     */
    private function removeNot_and_FilterWords()
    {
        if (is_array($this->stringArray)) {
            if (count($this->stringArray) > 0) {
                $delete = [];
                foreach ($this->stringArray as $index => $array) {
                    if ((($array['type'] == 'AND') || ($array['type'] == 'OR') || ($array['type'] == 'NOT'))
                        &&
                        (array_search(mb_strtolower($array['string']), WIKINDX_SEARCH_FILTER) !== FALSE)) {
                        $delete[] = $index;

                        continue;
                    }
                    foreach ($this->NOTfragments as $not) {
                        if ((($array['type'] == 'AND') || ($array['type'] == 'OR')) &&
                            (mb_strtolower($array['string']) == mb_strtolower($not))) {
                            $delete[] = $index;
                        }
                    }
                }
                foreach ($delete as $index) {
                    unset($this->stringArray[$index]);
                }
            }
        }
    }
    /**
     * Neatly print the elements of the quoteFragments array -- for debugging purposes only
     *
     * @param array $fragments
     */
    private function printFragments($fragments)
    {
        foreach ($fragments as $key => $value) {
            if (!$value) {
                $value = 'EXTRASPACE';
            }
            echo "$key: $value" . BR;
        }
    }
}
