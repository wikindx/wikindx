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
 * Parse the bibliographic style
 *
 * @package wikindx\core\bibcitation
 */
class PARSESTYLE
{    /**
     * PARSESTYLE
     */
    public function __construct()
    {
    }
    /**
     * parse input into array
     *
     * @param string $type
     * @param string $subject
     * @param bool $map Default is FALSE
     * @param bool $date Default is FALSE
     *
     * @return array
     */
    public function parseStringToArray($type, $subject, $map = FALSE, $date = FALSE)
    {
        if (!$subject)
        {
            return [];
        }
        if ($map)
        {
            $this->map = $map;
        }
        $search = implode('|', $this->map->$type);
        if ($date)
        {
            $search .= '|' . 'date';
        }
        $subjectArray = \UTF8\mb_explode('|', $subject);
        list($subjectArray, $alternates) = $this->findAlternateFields($subjectArray, $search);
        $sizeSubject = count($subjectArray);
        // Loop each field string
        $index = 0;
        $subjectIndex = 0;
        foreach ($subjectArray as $subject)
        {
            ++$subjectIndex;
            $dependentPre = $dependentPost = $dependentPreAlternative =
                $dependentPostAlternative = $singular = $plural = FALSE;
            // First grab fieldNames from the input string.
            preg_match("/(.*)(?<!`|[a-zA-Z])($search)(?!`|[a-zA-Z])(.*)/u", $subject, $array);
            if (empty($array))
            {
                if (!$index)
                {
                    $possiblePreliminaryText = $subject;

                    continue;
                }
                if (isset($independent) && ($subjectIndex == $sizeSubject) &&
                    array_key_exists('independent_' . $index, $independent))
                {
                    $ultimate = $subject;
                }
                else
                {
                    if (isset($independent) && (count($independent) % 2))
                    {
                        $independent['independent_' . ($index - 1)] = $subject;
                    }
                    else
                    {
                        $independent['independent_' . $index] = $subject;
                    }
                }

                continue;
            }
            // At this stage, [2] is the fieldName, [1] is what comes before and [3] is what comes after.
            $pre = $array[1];
            $fieldName = $array[2];
            if ($date && ($fieldName == 'date'))
            {
                $fieldName = $this->map->{$type}['date'];
            }
            $post = $array[3];
            // Anything in $pre enclosed in '%' characters is only to be printed if the resource has something in the
            // previous field -- replace with unique string for later preg_replace().
            if (preg_match("/%(.*)%(.*)%|%(.*)%/Uu", $pre, $dependent))
            {
                // if sizeof == 4, we have simply %*% with the significant character in [3].
                // if sizeof == 3, we have %*%*% with dependent in [1] and alternative in [2].
                $pre = str_replace($dependent[0], "__DEPENDENT_ON_PREVIOUS_FIELD__", $pre);
                if (count($dependent) == 4)
                {
                    $dependentPre = $dependent[3];
                    $dependentPreAlternative = '';
                }
                else
                {
                    $dependentPre = $dependent[1];
                    $dependentPreAlternative = $dependent[2];
                }
            }
            // Anything in $post enclosed in '%' characters is only to be printed if the resource has something in the
            // next field -- replace with unique string for later preg_replace().
            if (preg_match("/%(.*)%(.*)%|%(.*)%/Uu", $post, $dependent))
            {
                $post = str_replace($dependent[0], "__DEPENDENT_ON_NEXT_FIELD__", $post);
                if (count($dependent) == 4)
                {
                    $dependentPost = $dependent[3];
                    $dependentPostAlternative = '';
                }
                else
                {
                    $dependentPost = $dependent[1];
                    $dependentPostAlternative = $dependent[2];
                }
            }
            // find singular/plural alternatives in $pre and $post and replace with unique string for later preg_replace().
            if (preg_match("/\\^(.*)\\^(.*)\\^/Uu", $pre, $matchCarat))
            {
                $pre = str_replace($matchCarat[0], "__SINGULAR_PLURAL__", $pre);
                $singular = $matchCarat[1];
                $plural = $matchCarat[2];
            }
            elseif (preg_match("/\\^(.*)\\^(.*)\\^/Uu", $post, $matchCarat))
            {
                $post = str_replace($matchCarat[0], "__SINGULAR_PLURAL__", $post);
                $singular = $matchCarat[1];
                $plural = $matchCarat[2];
            }
            // Now dump into $final[$fieldName] stripping any backticks
            if ($dependentPre)
            {
                $final[$fieldName]['dependentPre'] = $dependentPre;
            }
            else
            {
                $final[$fieldName]['dependentPre'] = '';
            }
            if ($dependentPost)
            {
                $final[$fieldName]['dependentPost'] = $dependentPost;
            }
            else
            {
                $final[$fieldName]['dependentPost'] = '';
            }
            if ($dependentPreAlternative)
            {
                $final[$fieldName]['dependentPreAlternative'] = $dependentPreAlternative;
            }
            else
            {
                $final[$fieldName]['dependentPreAlternative'] = '';
            }
            if ($dependentPostAlternative)
            {
                $final[$fieldName]['dependentPostAlternative'] = $dependentPostAlternative;
            }
            else
            {
                $final[$fieldName]['dependentPostAlternative'] = '';
            }
            if ($singular)
            {
                $final[$fieldName]['singular'] = $singular;
            }
            else
            {
                $final[$fieldName]['singular'] = '';
            }
            if ($plural)
            {
                $final[$fieldName]['plural'] = $plural;
            }
            else
            {
                $final[$fieldName]['plural'] = '';
            }
            $final[$fieldName]['pre'] = str_replace('`', '', $pre);
            $final[$fieldName]['post'] = str_replace('`', '', $post);
            // add any alternates (which are indexed from 1 to match $subjectIndex)
            if (array_key_exists(0, $alternates))
            {
                if (array_key_exists($subjectIndex, $alternates[0]))
                {
                    if ($alternates[0][$subjectIndex]['position'] == 'pre')
                    {
                        foreach ($alternates[0][$subjectIndex] as $field => $position)
                        {
                            if ($position == 'first')
                            {
                                $final[$fieldName]['alternatePreFirst'] = $field;
                            }
                            elseif ($position == 'second')
                            {
                                $final[$fieldName]['alternatePreSecond'] = $field;
                            }
                        }
                        // Write empty XML fields if required
                        if (!array_key_exists('alternatePreFirst', $final[$fieldName]))
                        {
                            $final[$fieldName]['alternatePreFirst'] = '';
                        }
                        if (!array_key_exists('alternatePreSecond', $final[$fieldName]))
                        {
                            $final[$fieldName]['alternatePreSecond'] = '';
                        }
                    }
                    else
                    {
                        foreach ($alternates[0][$subjectIndex] as $field => $position)
                        {
                            if ($position == 'first')
                            {
                                $final[$fieldName]['alternatePostFirst'] = $field;
                            }
                            elseif ($position == 'second')
                            {
                                $final[$fieldName]['alternatePostSecond'] = $field;
                            }
                        }
                        // Write empty XML fields if required
                        if (!array_key_exists('alternatePostFirst', $final[$fieldName]))
                        {
                            $final[$fieldName]['alternatePostFirst'] = '';
                        }
                        if (!array_key_exists('alternatePostSecond', $final[$fieldName]))
                        {
                            $final[$fieldName]['alternatePostSecond'] = '';
                        }
                    }
                }
            }
            if (array_key_exists(1, $alternates))
            {
                if (array_key_exists($subjectIndex, $alternates[1]))
                {
                    if ($alternates[1][$subjectIndex]['position'] == 'pre')
                    {
                        foreach ($alternates[1][$subjectIndex] as $field => $position)
                        {
                            if ($position == 'first')
                            {
                                $final[$fieldName]['alternatePreFirst'] = $field;
                            }
                            elseif ($position == 'second')
                            {
                                $final[$fieldName]['alternatePreSecond'] = $field;
                            }
                        }
                        // Write empty XML fields if required
                        if (!array_key_exists('alternatePreFirst', $final[$fieldName]))
                        {
                            $final[$fieldName]['alternatePreFirst'] = '';
                        }
                        if (!array_key_exists('alternatePreSecond', $final[$fieldName]))
                        {
                            $final[$fieldName]['alternatePreSecond'] = '';
                        }
                    }
                    else
                    {
                        foreach ($alternates[1][$subjectIndex] as $field => $position)
                        {
                            if ($position == 'first')
                            {
                                $final[$fieldName]['alternatePostFirst'] = $field;
                            }
                            elseif ($position == 'second')
                            {
                                $final[$fieldName]['alternatePostSecond'] = $field;
                            }
                        }
                        // Write empty XML fields if required
                        if (!array_key_exists('alternatePostFirst', $final[$fieldName]))
                        {
                            $final[$fieldName]['alternatePostFirst'] = '';
                        }
                        if (!array_key_exists('alternatePostSecond', $final[$fieldName]))
                        {
                            $final[$fieldName]['alternatePostSecond'] = '';
                        }
                    }
                }
            }
            $index++;
        }
        if (isset($possiblePreliminaryText))
        {
            if (isset($independent))
            {
                $independent = ['independent_0' => $possiblePreliminaryText] + $independent;
            }
            else
            {
                $final['preliminaryText'] = $possiblePreliminaryText;
            }
        }
        if (!isset($final))
        { // presumably no field names... so assume $subject is standalone text and return
            $final['preliminaryText'] = $subject;

            return $final;
        }
        if (isset($independent))
        {
            $size = count($independent);
            // If $size == 3 and exists 'independent_0', this is preliminaryText
            // If $size == 3 and exists 'independent_' . $index, this is ultimate
            // If $size % 2 == 0 and exists 'independent_0' and 'independent_' . $index, these are preliminaryText and ultimate
            if (($size == 3) && array_key_exists('independent_0', $independent))
            {
                $final['preliminaryText'] = array_shift($independent);
            }
            elseif (($size == 3) && array_key_exists('independent_' . $index, $independent))
            {
                $final['ultimate'] = array_pop($independent);
            }
            elseif (!($size % 2) && array_key_exists('independent_0', $independent)
            && array_key_exists('independent_' . $index, $independent))
            {
                $final['preliminaryText'] = array_shift($independent);
                $final['ultimate'] = array_pop($independent);
            }
            $size = count($independent);
            // last element of odd number is actually ultimate punctuation or first element is preliminary if exists 'independent_0'
            if ($size % 2)
            {
                if (array_key_exists('independent_0', $independent))
                {
                    $final['preliminaryText'] = array_shift($independent);
                }
                else
                {
                    $final['ultimate'] = array_pop($independent);
                }
            }
            if ($size == 1)
            {
                if (array_key_exists('independent_0', $independent))
                {
                    $final['preliminaryText'] = array_shift($independent);
                }
                if (array_key_exists('independent_' . $index, $independent))
                {
                    $final['ultimate'] = array_shift($independent);
                }
            }
            if (isset($ultimate) && !array_key_exists('ultimate', $final))
            {
                $final['ultimate'] = $ultimate;
            }
            if (isset($preliminaryText) && !array_key_exists('preliminaryText', $final))
            {
                $final['preliminaryText'] = $preliminaryText;
            }
            if (!empty($independent))
            {
                $final['independent'] = $independent;
            }
        }

        return $final;
    }
    /**
     * Find alternate fields
     *
     * @param array $subjectArray
     * @param string $search
     *
     * @return array
     */
    private function findAlternateFields($subjectArray, $search)
    {
        $index = 1;
        $lastIndex = count($subjectArray) - 1;
        $alternates = [];
        foreach ($subjectArray as $subject)
        {
            $subjectFieldIndex = $index;
            // this pair depend on the preceding field
            if (($index > 1) && (mb_substr_count($subject, "$") == 3) && (mb_strpos($subject, "$") === 0))
            {
                $dollarSplit = \UTF8\mb_explode("$", trim($subject));
                $temp = [];
                $elements = 0;
                if ($dollarSplit[1])
                {
                    preg_match("/(.*)(?<!`|[a-zA-Z])($search)(?!`|[a-zA-Z])(.*)/u", $dollarSplit[1], $match);
                    if (!empty($match))
                    {
                        $newSubjectArray[$index] = $dollarSplit[1];
                        $temp[$match[2]] = 'first';
                        ++$index;
                        ++$lastIndex;
                        ++$elements;
                        $temp['position'] = 'pre';
                    }
                    else
                    {
                        $newSubjectArray[$index] = $subject;
                        ++$index;
                    }
                }
                if ($dollarSplit[2])
                {
                    preg_match("/(.*)(?<!`|[a-zA-Z])($search)(?!`|[a-zA-Z])(.*)/u", $dollarSplit[2], $match);
                    if (!empty($match))
                    {
                        $newSubjectArray[$index] = $dollarSplit[2];
                        $temp[$match[2]] = 'second';
                        ++$index;
                        ++$lastIndex;
                        ++$elements;
                        $temp['position'] = 'pre';
                    }
                    else
                    {
                        $newSubjectArray[$index] = $subject;
                        ++$index;
                    }
                }
                if ($elements)
                {
                    $alternates[][$subjectFieldIndex - 1] = $temp;
                }
            }
            // this pair depend on the following field
            elseif ((mb_substr_count($subject, "#") == 3) && (mb_strpos($subject, "#") === 0))
            {
                $hashSplit = \UTF8\mb_explode("#", trim($subject));
                $temp = [];
                $elements = $subjectFieldIndex;
                if ($hashSplit[1])
                {
                    preg_match("/(.*)(?<!`|[a-zA-Z])($search)(?!`|[a-zA-Z])(.*)/u", $hashSplit[1], $match);
                    if (!empty($match))
                    {
                        $newSubjectArray[$index] = $hashSplit[1];
                        $temp[$match[2]] = 'first';
                        ++$index;
                        ++$lastIndex;
                        ++$elements;
                        $temp['position'] = 'post';
                    }
                    else
                    {
                        $newSubjectArray[$index] = $subject;
                        ++$index;
                    }
                }
                if ($hashSplit[2])
                {
                    preg_match("/(.*)(?<!`|[a-zA-Z])($search)(?!`|[a-zA-Z])(.*)/u", $hashSplit[2], $match);
                    if (!empty($match))
                    {
                        $newSubjectArray[$index] = $hashSplit[2];
                        $temp[$match[2]] = 'second';
                        ++$index;
                        ++$lastIndex;
                        ++$elements;
                        $temp['position'] = 'post';
                    }
                    else
                    {
                        $newSubjectArray[$index] = $subject;
                        ++$index;
                    }
                }
                if ($elements > $subjectFieldIndex)
                {
                    $alternates[][$subjectFieldIndex + 1] = $temp;
                }
            }
            else
            {
                $newSubjectArray[$index] = $subject;
                ++$index;
            }
        }

        return [$newSubjectArray, $alternates];
    }
}
