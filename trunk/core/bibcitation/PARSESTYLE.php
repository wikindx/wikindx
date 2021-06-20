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
{
    /** array */
    private $creators;
    /** array */
    private $writeSource;
    /** object */
    private $errors;
    /** int */
    private $numTabs = 2;
    /** array */
	private $noTabbingFields = [
								'dependentPre', 'dependentPost', 'dependentPreAlternative', 
								'dependentPostAlternative', 'singular', 'plural', 
    							'pre', 'post', 'ultimate', 'preliminaryText', 
    							'alternatePreSecond', 'alternatePreFirst', 
    							'alternatePostFirst', 'alternatePostSecond', 
    							];
    
	/**
	 * PARSESTYLE
	 */
    public function __construct()
    {
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->creators = ['creator1', 'creator2', 'creator3', 'creator4', 'creator5'];
    }
    /**
     * Transfer source variables to $this->writeSource
     *
     * @param string $source 'vars' = $this->vars, 'session' = $this->session, 'array' = second @param
     * @param array $array Default [];
     * @return mixed TRUE on success or string on error
     */
    public function getSource($source, $array = [])
    {
    	$possible = ['vars', 'session', 'array'];
    	if (!in_array($source, $possible)) {
    		return $this->errors->text("inputError", "invalid");
    	}
    	if ($source == 'vars') {
    		$this->writeSource = GLOBALS::getVars();
    	} else if ($source == 'session') { // TODO if required
    	} else if ($source == 'array') {
    		$this->writeSource = $array;
    	}
    	return TRUE;
    }
    /**
     * write the style to file
     *
     * If !$fileName, we are creating a new style and we create folder/filename immediately before writing to file.
     * If $fileName, we are editing
     *
     * @param false|string $fileName
     * @return mixed TRUE on success or string on error
     */
    public function writeFile($fileName = FALSE)
    {
        $db = FACTORY_DB::getInstance();
        $styleMap = FACTORY_STYLEMAP::getInstance();
        $types = array_keys($styleMap->types);
        // Grab any custom fields
        $customFields = [];
        $recordset = $db->select('custom', ['customId', 'customLabel']);
        while ($row = $db->fetchRow($recordset)) {
            $customFields['custom_' . $row['customId']] = $row['customId'];
        }
        if (!empty($customFields)) {
            foreach ($styleMap as $type => $typeArray) {
                foreach ($customFields as $key => $value) {
                    $styleMap->{$type}[$key] = $key;
                }
            }
        }
        // Start XML
        $fileString = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>" . LF;
        $fileString .= "<style xml:lang=\"en\">" . LF;
        // Main style information
        $fileString .= TAB . "<info>" . LF;
        $fileString .= TAB . TAB . "<name>" . trim(stripslashes($this->writeSource['styleShortName'])) . "</name>" . LF;
        $fileString .= TAB . TAB . "<description>" . htmlspecialchars(trim(stripslashes($this->writeSource['styleLongName'])))
             . "</description>" . LF;
        // Temporary place holder
        $fileString .= TAB . TAB . "<language>en_GB</language>" . LF;
        $fileString .= TAB . TAB . "<osbibVersion>" . WIKINDX_COMPONENTS_COMPATIBLE_VERSION["style"] . "</osbibVersion>" . LF;
        $fileString .= TAB . "</info>" . LF;
        // Start citation definition
        $fileString .= TAB . "<citation>" . LF;
        $inputArray = [
            "cite_creatorStyle", "cite_creatorOtherStyle", "cite_creatorInitials",
            "cite_creatorFirstName", "cite_twoCreatorsSep", "cite_creatorSepFirstBetween",
            "cite_creatorListSubsequentAbbreviation", "cite_creatorSepNextBetween",
            "cite_creatorSepNextLast", "cite_creatorList", "cite_creatorListMore",
            "cite_creatorListLimit", "cite_creatorListAbbreviation", "cite_creatorUppercase",
            "cite_creatorListSubsequentAbbreviationItalic", "cite_creatorListAbbreviationItalic",
            "cite_creatorListSubsequent", "cite_creatorListSubsequentMore",
            "cite_creatorListSubsequentLimit", "cite_consecutiveCreatorSep",
            "cite_useInitials", "cite_consecutiveCitationSep", "cite_yearFormat",
            "cite_pageFormat", "cite_titleCapitalization", "cite_firstChars", "cite_lastChars", "cite_citationStyle", 
            "cite_consecutiveCitationEndnoteInTextSep", "cite_firstCharsEndnoteInText",
            "cite_lastCharsEndnoteInText", "cite_formatEndnoteInText", "cite_endnoteStyle",
            "cite_ambiguous", "cite_order1", "cite_order2", "cite_order3",
            "cite_order1desc", "cite_order2desc", "cite_order3desc", "cite_sameIdOrderBib",
            "cite_firstCharsEndnoteID", "cite_lastCharsEndnoteID", "cite_subsequentCreatorRange",
            "cite_followCreatorPageSplit", "cite_replaceYear",
            "cite_titleSubtitleSeparator", "cite_formatEndnoteID", "cite_removeTitle", "cite_subsequentFields",
        ];
        foreach ($inputArray as $input)
        {
            if (array_key_exists($input, $this->writeSource))
            {
                $split = \UTF8\mb_explode("_", $input, 2);
                $elementName = $split[1];
                $fileString .= TAB . TAB . "<$elementName>" .
                    htmlspecialchars(stripslashes($this->writeSource[$input])) . "</$elementName>" . LF;
            }
        }
        // cite templates
        $inputArray = [
            "cite_consecutiveCreatorTemplate", "cite_template", "cite_followCreatorTemplate", "cite_templateEndnote", 
            "cite_templateEndnoteInText", "cite_ambiguousTemplate", "cite_subsequentCreatorTemplate", 
            "cite_ibid", "cite_idem", "cite_opCit", 
        ];
        foreach ($inputArray as $input)
        {
            if (array_key_exists($input, $this->writeSource))
            {
                $split = \UTF8\mb_explode("_", $input, 2);
                $name = $split[1];
                $fileString .= TAB . TAB . "<$name>";
                if (is_array($this->writeSource[$input])) {
                	$value = $this->arrayToXML($this->writeSource[$input]);
                } else {
                	if ($input == 'cite_templateEndnoteInText') {
                		$type = 'citationEndnoteInText';
                	} else if ($input == 'cite_templateEndnote') {
                		$type = 'citationEndnote';
                	} else {
                		$type = 'citation';
                	}
                	$value = $this->arrayToXML($this->parseStringToArray($type, $this->writeSource[$input], $styleMap));
            	}
                if ($value) {
	            	$fileString .= $value . LF . TAB . TAB . "</$name>" . LF;
	            } else {
	            	$fileString .= "</$name>" . LF;
	            }
            }
        }
        // Resource types replacing citation templates
        foreach ($types as $key)
        {
            $citationStringName = "cite_" . $key . "Template";
            if (array_key_exists($citationStringName, $this->writeSource) &&
            	($string = $this->writeSource[$citationStringName]))
            {
				$name = 'cite_' . $key;
				$fileString .= TAB . TAB . "<" . $key . "Template>";
				if (is_array($string)) {
					$fileString .= $this->arrayToXML($string);
				} else {
					$fileString .= $this->arrayToXML($this->parseStringToArray('citation', $string, $styleMap, FALSE, TRUE));
				}
				$fileString .= LF . TAB . TAB . "</" . $key . "Template>" . LF;
            }
            $field = "cite_" . $key . "_notInBibliography";
            $element = $key . "_notInBibliography";
            if (array_key_exists($field, $this->writeSource))
            {
                $fileString .= TAB . TAB . "<$element>" . $this->writeSource[$field] . "</$element>" . LF;
            }
        }
        $fileString .= TAB . "</citation>" . LF;
        // Footnote creator formatting
        $fileString .= TAB . "<footnote>" . LF;
        $inputArray = [
            // foot note creator formatting
            "footnote_primaryCreatorFirstStyle", "footnote_primaryCreatorOtherStyle",
            "footnote_primaryCreatorList", "footnote_primaryCreatorFirstName",
            "footnote_primaryCreatorListAbbreviationItalic", "footnote_primaryCreatorInitials",
            "footnote_primaryCreatorListMore", "footnote_primaryCreatorListLimit",
            "footnote_primaryCreatorListAbbreviation", "footnote_primaryCreatorUppercase",
            "footnote_primaryCreatorRepeatString", "footnote_primaryCreatorRepeat",
            "footnote_primaryCreatorSepFirstBetween",  "footnote_primaryTwoCreatorsSep",
            "footnote_primaryCreatorSepNextBetween", "footnote_primaryCreatorSepNextLast",
            "footnote_otherCreatorFirstStyle", "footnote_otherCreatorListAbbreviationItalic",
            "footnote_otherCreatorOtherStyle", "footnote_otherCreatorInitials",
            "footnote_otherCreatorFirstName", "footnote_otherCreatorList",
            "footnote_otherCreatorUppercase", "footnote_otherCreatorListMore",
            "footnote_otherCreatorListLimit", "footnote_otherCreatorListAbbreviation",
            "footnote_otherCreatorSepFirstBetween", "footnote_otherCreatorSepNextBetween",
            "footnote_otherCreatorSepNextLast", "footnote_otherTwoCreatorsSep",
        ];
        foreach ($inputArray as $input)
        {
            if (array_key_exists($input, $this->writeSource))
            {
                $split = \UTF8\mb_explode("_", $input, 2);
                $elementName = $split[1];
                $fileString .= TAB . TAB . "<$elementName>" .
                    htmlspecialchars(stripslashes($this->writeSource[$input])) . "</$elementName>" . LF;
            }
        }
        // Footnote templates for each resource type
        foreach ($types as $key)
        {
            $type = 'footnote_' . $key;
            if (array_key_exists($type, $this->writeSource)) {
				if (is_array($this->writeSource[$type])) {
					$value = $this->arrayToXML($this->writeSource[$type]);
				} else {
					$input = trim(stripslashes($this->writeSource[$type]));
					// remove newlines etc.
					$input = preg_replace("/\\R/u", "", $input);
					$value = $this->arrayToXML($this->parseStringToArray($key, $input, $styleMap));
				}
				$fileString .= TAB . TAB . "<resource name=\"$key\">";
				if ($value) {
					$fileString .= $value . LF . TAB . TAB . "</resource>" . LF;
				} else {
					$fileString .= "</resource>" . LF;
				}
	        }
        }
        $fileString .= TAB . "</footnote>" . LF;
        // Start bibliography
        $fileString .= TAB . "<bibliography>" . LF;
        // Common section defining how authors, titles etc. are formatted
        $fileString .= TAB . TAB . "<common>" . LF;
        $inputArray = [
            // style
            "style_titleCapitalization", "style_monthFormat", "style_editionFormat", "style_dateFormat",
            "style_titleSubtitleSeparator",
            "style_primaryCreatorFirstStyle", "style_primaryCreatorOtherStyle", "style_primaryCreatorInitials",
            "style_primaryCreatorFirstName", "style_otherCreatorFirstStyle",
            "style_otherCreatorOtherStyle", "style_otherCreatorInitials",
            "style_otherCreatorFirstName", "style_primaryCreatorList", "style_otherCreatorList",
            "style_primaryCreatorListAbbreviationItalic", "style_otherCreatorListAbbreviationItalic",
            "style_primaryCreatorListMore", "style_primaryCreatorListLimit",
            "style_primaryCreatorListAbbreviation", "style_otherCreatorListMore",
            "style_primaryCreatorRepeatString", "style_primaryCreatorRepeat",
            "style_otherCreatorListLimit", "style_otherCreatorListAbbreviation",
            "style_primaryCreatorUppercase",
            "style_otherCreatorUppercase", "style_primaryCreatorSepFirstBetween",
            "style_primaryCreatorSepNextBetween", "style_primaryCreatorSepNextLast",
            "style_otherCreatorSepFirstBetween", "style_otherCreatorSepNextBetween",
            "style_otherCreatorSepNextLast", "style_primaryTwoCreatorsSep", "style_otherTwoCreatorsSep",
            "style_userMonth_1", "style_userMonth_2", "style_userMonth_3", "style_userMonth_4",
            "style_userMonth_5", "style_userMonth_6", "style_userMonth_7", "style_userMonth_8",
            "style_userMonth_9", "style_userMonth_10", "style_userMonth_11", "style_userMonth_12",
            "style_userMonth_13", "style_userMonth_14", "style_userMonth_15", "style_userMonth_16",
            "style_dateRangeDelimit1", "style_dateRangeDelimit2", "style_dateRangeSameMonth",
            "style_dateMonthNoDay", "style_dayLeadingZero", "style_dayFormat",
            "style_localisation", "style_runningTimeFormat", "style_editorSwitch", "style_pageFormat",
        ];
        foreach ($inputArray as $input)
        {
            if (array_key_exists($input, $this->writeSource))
            {
                $split = \UTF8\mb_explode("_", $input, 2);
                $elementName = $split[1];
                $fileString .= TAB . TAB . TAB . "<$elementName>" .
                    htmlspecialchars(stripslashes($this->writeSource[$input])) . "</$elementName>" . LF;
            }
        }
        $inputArray = ["style_dateMonthNoDayString", "style_editorSwitchIfYes"];
        foreach ($inputArray as $input)
        {
            if (array_key_exists($input, $this->writeSource))
            {
                $split = \UTF8\mb_explode("_", $input, 2);
                $name = $split[1];
                $fileString .= TAB . TAB . TAB . "<$name>";
                if (is_array($this->writeSource[$input])) {
	            	$value = $this->arrayToXML($this->writeSource[$input]);
	            } else {
	            	if ($input == 'style_dateMonthNoDayString') {
		                $value = $this->arrayToXML($this->parseStringToArray('book', $this->writeSource[$input], $styleMap, TRUE));
	            	} else {
		                $value = $this->arrayToXML($this->parseStringToArray('book', $this->writeSource[$input], $styleMap));
		            }
	            }
                if ($value) {
	            	$fileString .= $value . LF . TAB . TAB . TAB . "</$name>" . LF;
	            } else {
	            	$fileString .= "</$name>" . LF;
	            }
            }
        }
        $fileString .= TAB . TAB . "</common>" . LF;
        // Resource types
        foreach ($types as $key)
        {
            $type = 'style_' . $key;
            // Rewrite creator strings
            $attributes = $this->creatorXMLAttributes($type);
            $fileString .= TAB . TAB . "<resource name=\"$key\" $attributes>";
            if (is_array($this->writeSource[$type])) {
            	if (array_key_exists('partial', $this->writeSource[$type])) { // Stype upgrade from v5 –> v6
            		if ($this->writeSource[$type]['partial']) {
            			$partial = $this->parseStringToArray($key, trim(stripslashes($this->writeSource[$type]['partial'])), $styleMap);
            			$this->writeSource[$type]['partial'] = $partial;
            		}
            	}
            	$fileString .= $this->arrayToXML($this->writeSource[$type]);
            } else {
	            $input = trim(stripslashes($this->writeSource[$type]));
	            // remove newlines etc.
	            $input = preg_replace("/\\R/u", "", $input);
	            $fileString .= $this->arrayToXML($this->parseStringToArray($key, $input, $styleMap));
	        }
			if (($key != 'genericBook') && ($key != 'genericArticle') && ($key != 'genericMisc'))
			{
				$name = $type . "_generic";
				if (!array_key_exists($name, $this->writeSource))
				{
					$name = "genericMisc";
				}
				else
				{
					$name = $this->writeSource[$name];
				}
				$fileString .= LF . TAB . TAB . TAB . "<fallbackstyle>$name</fallbackstyle>";
			}
			// Partial templates for each resource type
			$type = 'partial_' . $key;
			if (array_key_exists($type, $this->writeSource)) {
				$fileString .= LF . TAB . TAB . TAB . "<partial>";
				if (is_array($this->writeSource[$type])) {
					$value = $this->arrayToXML($this->writeSource[$type]);
				} else {
					$input = trim(stripslashes($this->writeSource[$type]));
					$input = preg_replace("/\\R/u", "", $input);
					$value = $this->arrayToXML($this->parseStringToArray($key, $input, $styleMap));
				}
				if ($value) {
					$fileString .= $value . LF . TAB . TAB . TAB . "</partial>";
				} else {
					$fileString .= "</partial>" . LF;
				}
			}
			$type = 'partial_' . $key . 'Replace';
			$fileString .= LF . TAB . TAB . TAB . "<partialReplace>";
			if (array_key_exists($type, $this->writeSource))
			{
				$fileString .= '1';
			}
			else
			{
				$fileString .= '0';
			}
			$fileString .= "</partialReplace>" . LF;
            // close resource node
            $fileString .= TAB . TAB . "</resource>" . LF;
        }
        $fileString .= TAB . "</bibliography>" . LF;
        $fileString .= "</style>" . LF;
        if (!$fileName)
        { // adding a new file
            // Create folder with lowercase styleShortName
            $dirName = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES,
            	mb_strtolower(trim($this->writeSource['styleShortName']))]);
            if (!file_exists($dirName))
            {
                if (!mkdir($dirName, WIKINDX_UNIX_PERMS_DEFAULT, TRUE))
                {
                	return $this->errors->text("file", "folder");
                }
            }
            $fileName = implode(DIRECTORY_SEPARATOR, [$dirName, mb_strtolower(trim($this->writeSource['styleShortName'])) . ".xml"]);
        }
        if (!$fp = fopen("$fileName", "w"))
        {
        	return $this->errors->text("file", "write", ": $fileName");
        }
        if (!fwrite($fp, \UTF8\html_uentity_decode($fileString)))
        {
        	return $this->errors->text("file", "write", ": $fileName");
        }
        fclose($fp);
        return TRUE;
    }
    /**
     * parse input into array
     *
     * @param string $type
     * @param string $subject
     * @param bool $map Default is FALSE
     * @param bool $date Default is FALSE
     * @param bool $creator Default is FALSE
     *
     * @return array
     */
    public function parseStringToArray($type, $subject, $map = FALSE, $date = FALSE, $creator = FALSE)
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
        if ($date) {
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
            if ($date && ($fieldName == 'date')) {
 //               $fieldName = $this->map->{$type}['date'];
                $fieldName = 'issueDate';
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
    /**
     * Transform XML nodal array to resource type template strings
     *
     * @param array $temp
     * @return array
     */
    public function arrayToTemplate($temp)
    {
		$alternates = $tempArray = $newArray = $independent = [];
        $ultimate = $preliminary = $partial = $partialReplace = FALSE;
        if (array_key_exists('partial', $temp)) {
			$partial = $temp['partial'];
			unset($temp['partial']);
		}
        if (array_key_exists('partialReplace', $temp)) {
			$partialReplace = $temp['partialReplace'];
			unset($temp['partialReplace']);
		}
		$index = 0;
		foreach ($temp as $key => $value)
		{
			if (!is_array($value))
			{
				if ($key == 'ultimate')
				{
					$ultimate = $value;
				}
				elseif ($key == 'preliminaryText')
				{
					$preliminary = $value;
				}

				continue;
			}
			if (($key == 'independent'))
			{
				$independent = $value;

				continue;
			}
			$string = FALSE;
			if (array_key_exists('alternatePreFirst', $value))
			{
				$alternates[$key]['preFirst'] = $value['alternatePreFirst'];
			}
			if (array_key_exists('alternatePreSecond', $value))
			{
				$alternates[$key]['preSecond'] = $value['alternatePreSecond'];
			}
			if (array_key_exists('alternatePostFirst', $value))
			{
				$alternates[$key]['postFirst'] = $value['alternatePostFirst'];
			}
			if (array_key_exists('alternatePostSecond', $value))
			{
				$alternates[$key]['postSecond'] = $value['alternatePostSecond'];
			}
			if (array_key_exists('pre', $value))
			{
				$string .= $value['pre'];
			}
			$string .= $key;
			if (array_key_exists('post', $value))
			{
				$string .= $value['post'];
			}
			if (array_key_exists('dependentPre', $value))
			{
				$replace = "%" . $value['dependentPre'] . "%";
				if (array_key_exists('dependentPreAlternative', $value))
				{
					$replace .= $value['dependentPreAlternative'] . "%";
				}
				$string = str_replace("__DEPENDENT_ON_PREVIOUS_FIELD__", $replace, $string);
			}
			if (array_key_exists('dependentPost', $value))
			{
				$replace = "%" . $value['dependentPost'] . "%";
				if (array_key_exists('dependentPostAlternative', $value))
				{
					$replace .= $value['dependentPostAlternative'] . "%";
				}
				$string = str_replace("__DEPENDENT_ON_NEXT_FIELD__", $replace, $string);
			}
			if (array_key_exists('singular', $value) && array_key_exists('plural', $value))
			{
				$replace = "^" . $value['singular'] . "^" . $value['plural'] . "^";
				$string = str_replace("__SINGULAR_PLURAL__", $replace, $string);
			}
			$tempArray[$key] = $string;
			$fieldNames[$key] = $index;
			++$index;
		}
		if (!empty($tempArray))
		{
			foreach ($alternates as $field => $altArray)
			{
				$alternateFound = 0;
				if (array_key_exists('preFirst', $altArray) &&
					array_key_exists($altArray['preFirst'], $tempArray))
				{
					$final = '$' . $tempArray[$altArray['preFirst']] . '$';
					unset($tempArray[$altArray['preFirst']]);
					$alternateFound = TRUE;
				}
				else
				{
					$final = '$$';
				}
				if (array_key_exists('preSecond', $altArray) &&
					array_key_exists($altArray['preSecond'], $tempArray))
				{
					$final .= $tempArray[$altArray['preSecond']] . '$';
					unset($tempArray[$altArray['preSecond']]);
					$alternateFound = TRUE;
				}
				else
				{
					$final .= '$';
				}
				if ($alternateFound)
				{
					array_splice($tempArray, $fieldNames[$field] + 1, 0, $final);
				}
				$alternateFound = 0;
				if (array_key_exists('postFirst', $altArray) &&
					array_key_exists($altArray['postFirst'], $tempArray))
				{
					$final = '#' . $tempArray[$altArray['postFirst']] . '#';
					unset($tempArray[$altArray['postFirst']]);
					++$alternateFound;
				}
				else
				{
					$final = '##';
				}
				if (array_key_exists('postSecond', $altArray) &&
					array_key_exists($altArray['postSecond'], $tempArray))
				{
					$final .= $tempArray[$altArray['postSecond']] . '#';
					unset($tempArray[$altArray['postSecond']]);
					++$alternateFound;
				}
				else
				{
					$final .= '#';
				}
				if ($alternateFound)
				{
					array_splice($tempArray, $fieldNames[$field] - $alternateFound, 0, $final);
				}
			}
			$tempArray = array_values($tempArray); // i.e. remove named keys.
		}
		if (!empty($independent))
		{
			$firstOfPair = FALSE;
			foreach ($tempArray as $index => $value)
			{
				if (!$firstOfPair)
				{
					if (array_key_exists($index, $independent))
					{
						$newArray[] = $independent[$index] . '|' . $value;
						$firstOfPair = TRUE;

						continue;
					}
				}
				else
				{
					if (array_key_exists($index, $independent))
					{
						$newArray[] = $value . '|' . $independent[$index];
						$firstOfPair = FALSE;

						continue;
					}
				}
				$newArray[] = $value;
			}
		}
		else
		{
			$newArray = $tempArray;
		}
		$tempString = implode('|', $newArray);
		if ($ultimate && (mb_substr($tempString, -1, 1) != $ultimate))
		{
			$tempString .= '|' . $ultimate;
		}
		if ($preliminary)
		{
			$tempString = $preliminary . '|' . $tempString;
		}
		return [$tempString, $partial, $partialReplace];
    }
    /**
     * create attribute strings for XML <resource> element for creators
     *
     * @param string $type
     *
     * @return string
     */
    private function creatorXMLAttributes($type)
    {
        $attributes = FALSE;
        foreach ($this->creators as $creatorField)
        {
            $basic = $type . "_" . $creatorField;
            $field = $basic . "_firstString";
            $name = $creatorField . "_firstString";
            if (array_key_exists($field, $this->writeSource) && trim($this->writeSource[$field]))
            {
                $attributes .= "$name=\"" . htmlspecialchars(stripslashes($this->writeSource[$field])) . "\" ";
            }
            $field = $basic . "_firstString_before";
            $name = $creatorField . "_firstString_before";
            if (isset($this->writeSource[$field]))
            {
                $attributes .= "$name=\"" . htmlspecialchars(stripslashes($this->writeSource[$field])) . "\" ";
            }
            $field = $basic . "_remainderString";
            $name = $creatorField . "_remainderString";
            if (array_key_exists($field, $this->writeSource) && trim($this->writeSource[$field]))
            {
                $attributes .= "$name=\"" . htmlspecialchars(stripslashes($this->writeSource[$field])) . "\" ";
            }
            $field = $basic . "_remainderString_before";
            $name = $creatorField . "_remainderString_before";
            if (isset($this->writeSource[$field]))
            {
                $attributes .= "$name=\"" . htmlspecialchars(stripslashes($this->writeSource[$field])) . "\" ";
            }
            $field = $basic . "_remainderString_each";
            $name = $creatorField . "_remainderString_each";
            if (isset($this->writeSource[$field]))
            {
                $attributes .= "$name=\"" . htmlspecialchars(stripslashes($this->writeSource[$field])) . "\" ";
            }
        }

        return $attributes;
    }
    /**
     * Parse array to XML
     *
     * @param array $array
     * @param array $type
     *
     * @return string
     */
    private function arrayToXML($array)
    {
        $fileString = '';
        $this->numTabs++;
        $tabs = [];
		for ($i = 0; $i < $this->numTabs; $i++) {
			$tabs[] = TAB;
		}
		$tabString = implode('', $tabs);
        foreach ($array as $key => $value)
        {
        	if (($key == 'fallbackstyle') || ($key == 'partialReplace')) {
        		continue;
        	}
	        $fileString .= LF . $tabString . "<$key>";
            if (is_array($value))
            {
                $fileString .= $this->arrayToXML($value) . LF;
            }
            else
            {
                $fileString .= htmlspecialchars($value);
            }
			if (in_array($key, $this->noTabbingFields) || (strpos($key, 'independent_') !== FALSE)) {
				$fileString .= "</$key>";
			} else {
				$fileString .= $tabString . "</$key>";
			}
        }
		$this->numTabs--;

        return $fileString;
    }
}
