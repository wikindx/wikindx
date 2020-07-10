<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
* CONSTANTS class (English)
*
* Various numbers, days, months etc.
*
*/
class CONSTANTS
{
    public $textEtAl;
    public $possessiveArray;
    public $startQuotation;
    public $endQuotation;
    public $abbreviations;
    public $force_english;

	public function __construct($force_english = false)
	{
	    $this->force_english = $force_english;
// translators: When using the word processor how are extra, unnamed authors indicated? 
// In English this would be 'et al.' as in: 'According to Grimshaw et al., "blah blah blah".
		$this->textEtAl = $this->local_gettext("et al.");
		
// translators:
// You should not translate this string but establish a list separated by ;
// of the singular possessive forms of your language, if they exist.
// THAT END BY A POINT AND ONLY A POINT.
// 
// If your language does not use it or is incompatible with the operation described
// below of the word processor, put a ; in this string empty.
// 
// When using the word processor, how is singular possessive defined.
// This is only for a single creator's surname and, like 'et al.' above, is used
// to detect if a creator name should be removed from the in-text citation
// if that name is in the same sentence as the citation.
// If this value contains an apostrophe (as in the English Grimshaw's),
// then it should be in double quotes (or else the PHP script will fail).
// English has two forms (the second below for names that end in 's').
        $possessive = trim($this->local_gettext("'s;'"));
		$possessive = UTF8::mb_explode(";", $possessive);
		foreach ($possessive as $k => $v)
		{
		    if ($possessive[$k] == "") unset($possessive[$k]);
		}
		$possessive = array_unique($possessive);
		sort($possessive);
// From wikindx v3.4.7 onwards, $this->possessiveArray is used allowing unlimited possessive forms.
// If it exists and is an array, $this->possessive1 and $this->possessive2 will be ignored.
// If there are no possessive forms in your language, $this->possessiveArray should exist and be empty.
// < v3.4.7 commented out.
//		$this->possessive1 = "'s";
//		$this->possessive2 = "'";
// >= v3.4.7 possessive form
		if (count($possessive) > 0)
		    $this->possessiveArray = $possessive;
		else
		    $this->possessiveArray = FALSE;
		
// translators:
// What characters indicate the start of a quotation in the word processor text?
// If your language uses more than one, indicate only the most common.
// N. B. This characters have to match with the end of a quotation.
//       Don't translate the first spaces. It's only a trick to prevent
//       gettext from merging this string with another.
		$this->startQuotation = ltrim($this->local_gettext("  \""));
// translators:
// What characters indicate the end of a quotation in the word processor text?
// If your language uses more than one, indicate only the most common.
// N. B. This characters have to match with the start of a quotation.
//       Don't translate the last spaces. It's only a trick to prevent
//       gettext from merging this string with another.
		$this->endQuotation = rtrim($this->local_gettext("\"  "), " ");
		
// translators:
// You should not translate these abbreviations but establish
// a list separated by ; of the most common abbreviations of your language
// or bibliographic styles used in your language (country)
// THAT END BY A POINT AND ONLY A POINT.
// 
// It is strongly recommended to leave the Latin abbreviations
// which are a common place of bibliographies.
// 
// Remove the English abbreviations unless they are used in your language.
// 
// Abreviations such as U.S.A., U.S.S.R., N.B. are dealt with in the code
// (must be capital English characters and no spaces).
// '...' and multiple dots consisting of two or more dots are dealt with in the code.
// 
// N.B. MATCHING IS CASE SENSITIVE. You must indicate all the possible cases of an abbreviation.
//
// If you have no abbreviations, put a ; in this string.
// 
// When using the word processor, for certain styles the text
// must be split up into sentences -- e.g. APA requires that year
// must follow the author name and page no. follows the citation
// if the citation appears in the same sentence as the author surname.
// Normally sentences are recognized by '. ' (dot followed by at least one space).
// This string handles other groups of characters (commonly abbreviations)
// that may appear in a sentence // but do not indicate a sentence divider.
// If there are no such abbreviations, the string must exist and be empty.
// 
// The Latin abbreviations which are a common place of bibliographies
//  are already included. Do not add them to your list.
// Latin abbreviations in order of apparition are:
//   - ad loc : ad locum
//   - c. : circa
//   - ca. : circa
//   - cf. : confer
//   - e.g. : exempli gratia
//   - etc. : et cetera
//   - id. : idem
//   - i.e. : id est
//   - loc. cit. : loco citato
//   - loc. laud. : loco laudato
//   - N.B. : Nota Bene
//   - N. B. : Nota Bene
//   - op. cit. : opus citatum
//   - sq. : sequiturque
//   - sqq. : sequunturque
//   - v. : versus
//   - viz. : videre licet
//
// English abbreviations in order of apparition are:
//   - short for Brothers (as in Warner Bros.)
//   - company
//   - Figure
//   - figure
//   - number
//   - seconds
//   - milliseconds
//   - doctorate
//   - Manuscript
        $abr = trim($this->local_gettext("Bros.;co.;Fig.;fig.;no.;secs.;msecs.;Ph. D.;MS."));
        $abr .= ";ad loc.;c.;ca.;cf.;e.g.;etc.;i.e.;ibid.;id.;loc. cit.;loc. laud.;N. B.;N.B.;op. cit.;v.;viz.";
		$abr = UTF8::mb_explode(";", $abr);
        // The syntax of the matching rule is that a sentence divider has been found
        // if there is a dot followed by a space where the dot is not preceeded by a member of the array.
        // For example, the 'e.g.' in "Blah blah blah, e.g. more blah, and even more blah" is not seen as
        // a sentence divider if 'e.g' (no final dot) is a member of this array.
        // So remove the last character of each abbreviation if it's a point.
		foreach ($abr as $k => $v)
		{
		    $abr[$k] = trim(rtrim($v, "."));
		    if ($abr[$k] == "") unset($abr[$k]);
		}
		$ab = array_unique($abr);
		sort($abr);
		$this->abbreviations = $abr;
	}
	
	/**
	 * convert cardinal numbers to ordinals
	 *
	 * @param int|string $cardinal
	 * @param string|FALSE $field
	 *
	 * @return string
	 */
	public function cardinalToOrdinal($cardinal, $field = FALSE)
	{
	    // Animate and inanimate genders are not handled. They are too rare.
	    // See. https://en.wikipedia.org/wiki/Grammatical_gender
	    $ordinal = $cardinal;
	    $gender = "masculine";
	    
	    if ($field == "dayMonth")
	        $gender == "daymonth";
	    else if ($field == "edition")
	        $gender == "feminine";
	    else if ($field == "feminine")
	        $gender == "feminine";
	    else if ($field == "neuter")
	        $gender == "neuter";
	    
		if ($gender == "daymonth" && 1 <= $ordinal && $ordinal <= 31)
		{
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[1] = trim($this->local_gettext("1st "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[2] = trim($this->local_gettext("2nd "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[3] = trim($this->local_gettext("3rd "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[4] = trim($this->local_gettext("4th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[5] = trim($this->local_gettext("5th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[6] = trim($this->local_gettext("6th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[7] = trim($this->local_gettext("7th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[8] = trim($this->local_gettext("8th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[9] = trim($this->local_gettext("9th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[10] = trim($this->local_gettext("10th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[11] = trim($this->local_gettext("11th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[12] = trim($this->local_gettext("12th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[13] = trim($this->local_gettext("13th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[14] = trim($this->local_gettext("14th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[15] = trim($this->local_gettext("15th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[16] = trim($this->local_gettext("16th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[17] = trim($this->local_gettext("17th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[18] = trim($this->local_gettext("18th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[19] = trim($this->local_gettext("19th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[20] = trim($this->local_gettext("20th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[21] = trim($this->local_gettext("21st "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[22] = trim($this->local_gettext("22nd "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[23] = trim($this->local_gettext("23rd "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[24] = trim($this->local_gettext("24th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[25] = trim($this->local_gettext("25th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[26] = trim($this->local_gettext("26th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[27] = trim($this->local_gettext("27th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[28] = trim($this->local_gettext("28th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[29] = trim($this->local_gettext("29th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[30] = trim($this->local_gettext("30th "));
    		// translators: abbreviated name of an ordinal day of month
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$dmordinal[31] = trim($this->local_gettext("31st "));
    		
    		$ordinal = $dmordinal[$ordinal];
		}
		else if ($gender == "masculine" && 1 <= $ordinal && $ordinal <= 50)
		{
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[1] = trim($this->local_gettext("1st  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[2] = trim($this->local_gettext("2nd  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[3] = trim($this->local_gettext("3rd  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[4] = trim($this->local_gettext("4th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[5] = trim($this->local_gettext("5th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[6] = trim($this->local_gettext("6th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[7] = trim($this->local_gettext("7th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[8] = trim($this->local_gettext("8th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[9] = trim($this->local_gettext("9th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[10] = trim($this->local_gettext("10th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[11] = trim($this->local_gettext("11th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[12] = trim($this->local_gettext("12th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[13] = trim($this->local_gettext("13th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[14] = trim($this->local_gettext("14th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[15] = trim($this->local_gettext("15th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[16] = trim($this->local_gettext("16th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[17] = trim($this->local_gettext("17th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[18] = trim($this->local_gettext("18th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[19] = trim($this->local_gettext("19th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[20] = trim($this->local_gettext("20th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[21] = trim($this->local_gettext("21st  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[22] = trim($this->local_gettext("22nd  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[23] = trim($this->local_gettext("23rd  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[24] = trim($this->local_gettext("24th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[25] = trim($this->local_gettext("25th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[26] = trim($this->local_gettext("26th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[27] = trim($this->local_gettext("27th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[28] = trim($this->local_gettext("28th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[29] = trim($this->local_gettext("29th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[30] = trim($this->local_gettext("30th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[31] = trim($this->local_gettext("31st  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[32] = trim($this->local_gettext("32nd  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[33] = trim($this->local_gettext("33rd  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[34] = trim($this->local_gettext("34th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[35] = trim($this->local_gettext("35th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[36] = trim($this->local_gettext("36th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[37] = trim($this->local_gettext("37th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[38] = trim($this->local_gettext("38th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[39] = trim($this->local_gettext("39th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[40] = trim($this->local_gettext("40th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[41] = trim($this->local_gettext("41st  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[42] = trim($this->local_gettext("42nd  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[43] = trim($this->local_gettext("43rd  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[44] = trim($this->local_gettext("44th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[45] = trim($this->local_gettext("45th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[46] = trim($this->local_gettext("46th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[47] = trim($this->local_gettext("47th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[48] = trim($this->local_gettext("48th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[49] = trim($this->local_gettext("49th  "));
    		// translators: abbreviated name of an ordinal which gender is masculine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$mordinal[50] = trim($this->local_gettext("50th  "));
    		
    		$ordinal = $mordinal[$ordinal];
		}
		else if ($gender == "feminine" && 1 <= $ordinal && $ordinal <= 50)
		{
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[1] = trim($this->local_gettext("1st   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[2] = trim($this->local_gettext("2nd   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[3] = trim($this->local_gettext("3rd   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[4] = trim($this->local_gettext("4th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[5] = trim($this->local_gettext("5th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[6] = trim($this->local_gettext("6th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[7] = trim($this->local_gettext("7th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[8] = trim($this->local_gettext("8th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[9] = trim($this->local_gettext("9th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[10] = trim($this->local_gettext("10th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[11] = trim($this->local_gettext("11th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[12] = trim($this->local_gettext("12th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[13] = trim($this->local_gettext("13th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[14] = trim($this->local_gettext("14th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[15] = trim($this->local_gettext("15th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[16] = trim($this->local_gettext("16th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[17] = trim($this->local_gettext("17th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[18] = trim($this->local_gettext("18th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[19] = trim($this->local_gettext("19th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[20] = trim($this->local_gettext("20th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[21] = trim($this->local_gettext("21st   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[22] = trim($this->local_gettext("22nd   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[23] = trim($this->local_gettext("23rd   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[24] = trim($this->local_gettext("24th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[25] = trim($this->local_gettext("25th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[26] = trim($this->local_gettext("26th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[27] = trim($this->local_gettext("27th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[28] = trim($this->local_gettext("28th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[29] = trim($this->local_gettext("29th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[30] = trim($this->local_gettext("30th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[31] = trim($this->local_gettext("31st   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[32] = trim($this->local_gettext("32nd   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[33] = trim($this->local_gettext("33rd   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[34] = trim($this->local_gettext("34th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[35] = trim($this->local_gettext("35th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[36] = trim($this->local_gettext("36th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[37] = trim($this->local_gettext("37th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[38] = trim($this->local_gettext("38th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[39] = trim($this->local_gettext("39th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[40] = trim($this->local_gettext("40th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[41] = trim($this->local_gettext("41st   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[42] = trim($this->local_gettext("42nd   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[43] = trim($this->local_gettext("43rd   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[44] = trim($this->local_gettext("44th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[45] = trim($this->local_gettext("45th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[46] = trim($this->local_gettext("46th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[47] = trim($this->local_gettext("47th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[48] = trim($this->local_gettext("48th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[49] = trim($this->local_gettext("49th   "));
    		// translators: abbreviated name of an ordinal which gender is feminine
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$fordinal[50] = trim($this->local_gettext("50th   "));
    		
    		$ordinal = $fordinal[$ordinal];
		    
		}
		else if ($gender == "neuter" && 1 <= $ordinal && $ordinal <= 50)
		{
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[1] = trim($this->local_gettext("1st    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[2] = trim($this->local_gettext("2nd    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[3] = trim($this->local_gettext("3rd    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[4] = trim($this->local_gettext("4th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[5] = trim($this->local_gettext("5th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[6] = trim($this->local_gettext("6th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[7] = trim($this->local_gettext("7th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[8] = trim($this->local_gettext("8th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[9] = trim($this->local_gettext("9th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[10] = trim($this->local_gettext("10th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[11] = trim($this->local_gettext("11th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[12] = trim($this->local_gettext("12th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[13] = trim($this->local_gettext("13th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[14] = trim($this->local_gettext("14th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[15] = trim($this->local_gettext("15th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[16] = trim($this->local_gettext("16th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[17] = trim($this->local_gettext("17th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[18] = trim($this->local_gettext("18th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[19] = trim($this->local_gettext("19th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[20] = trim($this->local_gettext("20th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[21] = trim($this->local_gettext("21st    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[22] = trim($this->local_gettext("22nd    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[23] = trim($this->local_gettext("23rd    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[24] = trim($this->local_gettext("24th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[25] = trim($this->local_gettext("25th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[26] = trim($this->local_gettext("26th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[27] = trim($this->local_gettext("27th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[28] = trim($this->local_gettext("28th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[29] = trim($this->local_gettext("29th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[30] = trim($this->local_gettext("30th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[31] = trim($this->local_gettext("31st    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[32] = trim($this->local_gettext("32nd    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[33] = trim($this->local_gettext("33rd    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[34] = trim($this->local_gettext("34th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[35] = trim($this->local_gettext("35th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[36] = trim($this->local_gettext("36th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[37] = trim($this->local_gettext("37th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[38] = trim($this->local_gettext("38th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[39] = trim($this->local_gettext("39th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[40] = trim($this->local_gettext("40th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[41] = trim($this->local_gettext("41st    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[42] = trim($this->local_gettext("42nd    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[43] = trim($this->local_gettext("43rd    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[44] = trim($this->local_gettext("44th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[45] = trim($this->local_gettext("45th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[46] = trim($this->local_gettext("46th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[47] = trim($this->local_gettext("47th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[48] = trim($this->local_gettext("48th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[49] = trim($this->local_gettext("49th    "));
    		// translators: abbreviated name of an ordinal which gender is neuter
    		// Don't translate the last spaces. It's only a trick to prevent
    		// gettext from merging this string with the string of a single ordinal.
    		$nordinal[50] = trim($this->local_gettext("50th    "));
    		
    		$ordinal = $nordinal[$ordinal];
		}
		
		return $ordinal . "";
	}
	
	/**
	 * convert cardinal numbers
	 *
	 * @param string|FALSE $number
	 * @param string|FALSE $convert
	 *
	 * @return string
	 */
	public function convertNumbers($number = FALSE, $convert = FALSE)
	{
		if(!is_numeric($number))
			return $number;
// month number to long name
		$longMonth = $this->monthToLongName();
// short month
		$shortMonth = $this->monthToShortName();
// cardinal -> ordinal word
		$ordinalWord = $this->cardinalToOrdinalWord();
// arabic -> roman numerals
		$roman = $this->cardinalToRoman();
// arabic -> ordinal (e.g. 3 -> 3rd, 10 -> 10th)
// Usually used for edition numbers in bibliographic styles that require e.g. '10th edition' rather than 'edition 10' - superseded by cardianlToOrdinal() function above
// A maximum of 50 seems a reasonable number to go up to....
// If necessary, you may need to add more loops or individual array elements.
		// translators: abbreviated name of an ordinal number
		$ordinal[1] = $this->local_gettext("1st");
		// translators: abbreviated name of an ordinal number
		$ordinal[2] = $this->local_gettext("2nd");
		// translators: abbreviated name of an ordinal number
		$ordinal[3] = $this->local_gettext("3rd");
		// translators: abbreviated name of an ordinal number
		$ordinal[4] = $this->local_gettext("4th");
		// translators: abbreviated name of an ordinal number
		$ordinal[5] = $this->local_gettext("5th");
		// translators: abbreviated name of an ordinal number
		$ordinal[6] = $this->local_gettext("6th");
		// translators: abbreviated name of an ordinal number
		$ordinal[7] = $this->local_gettext("7th");
		// translators: abbreviated name of an ordinal number
		$ordinal[8] = $this->local_gettext("8th");
		// translators: abbreviated name of an ordinal number
		$ordinal[9] = $this->local_gettext("9th");
		// translators: abbreviated name of an ordinal number
		$ordinal[10] = $this->local_gettext("10th");
		// translators: abbreviated name of an ordinal number
		$ordinal[11] = $this->local_gettext("11th");
		// translators: abbreviated name of an ordinal number
		$ordinal[12] = $this->local_gettext("12th");
		// translators: abbreviated name of an ordinal number
		$ordinal[13] = $this->local_gettext("13th");
		// translators: abbreviated name of an ordinal number
		$ordinal[14] = $this->local_gettext("14th");
		// translators: abbreviated name of an ordinal number
		$ordinal[15] = $this->local_gettext("15th");
		// translators: abbreviated name of an ordinal number
		$ordinal[16] = $this->local_gettext("16th");
		// translators: abbreviated name of an ordinal number
		$ordinal[17] = $this->local_gettext("17th");
		// translators: abbreviated name of an ordinal number
		$ordinal[18] = $this->local_gettext("18th");
		// translators: abbreviated name of an ordinal number
		$ordinal[19] = $this->local_gettext("19th");
		// translators: abbreviated name of an ordinal number
		$ordinal[20] = $this->local_gettext("20th");
		// translators: abbreviated name of an ordinal number
		$ordinal[21] = $this->local_gettext("21st");
		// translators: abbreviated name of an ordinal number
		$ordinal[22] = $this->local_gettext("22nd");
		// translators: abbreviated name of an ordinal number
		$ordinal[23] = $this->local_gettext("23rd");
		// translators: abbreviated name of an ordinal number
		$ordinal[24] = $this->local_gettext("24th");
		// translators: abbreviated name of an ordinal number
		$ordinal[25] = $this->local_gettext("25th");
		// translators: abbreviated name of an ordinal number
		$ordinal[26] = $this->local_gettext("26th");
		// translators: abbreviated name of an ordinal number
		$ordinal[27] = $this->local_gettext("27th");
		// translators: abbreviated name of an ordinal number
		$ordinal[28] = $this->local_gettext("28th");
		// translators: abbreviated name of an ordinal number
		$ordinal[29] = $this->local_gettext("29th");
		// translators: abbreviated name of an ordinal number
		$ordinal[30] = $this->local_gettext("30th");
		// translators: abbreviated name of an ordinal number
		$ordinal[31] = $this->local_gettext("31st");
		// translators: abbreviated name of an ordinal number
		$ordinal[32] = $this->local_gettext("32nd");
		// translators: abbreviated name of an ordinal number
		$ordinal[33] = $this->local_gettext("33rd");
		// translators: abbreviated name of an ordinal number
		$ordinal[34] = $this->local_gettext("34th");
		// translators: abbreviated name of an ordinal number
		$ordinal[35] = $this->local_gettext("35th");
		// translators: abbreviated name of an ordinal number
		$ordinal[36] = $this->local_gettext("36th");
		// translators: abbreviated name of an ordinal number
		$ordinal[37] = $this->local_gettext("37th");
		// translators: abbreviated name of an ordinal number
		$ordinal[38] = $this->local_gettext("38th");
		// translators: abbreviated name of an ordinal number
		$ordinal[39] = $this->local_gettext("39th");
		// translators: abbreviated name of an ordinal number
		$ordinal[40] = $this->local_gettext("40th");
		// translators: abbreviated name of an ordinal number
		$ordinal[41] = $this->local_gettext("41st");
		// translators: abbreviated name of an ordinal number
		$ordinal[42] = $this->local_gettext("42nd");
		// translators: abbreviated name of an ordinal number
		$ordinal[43] = $this->local_gettext("43rd");
		// translators: abbreviated name of an ordinal number
		$ordinal[44] = $this->local_gettext("44th");
		// translators: abbreviated name of an ordinal number
		$ordinal[45] = $this->local_gettext("45th");
		// translators: abbreviated name of an ordinal number
		$ordinal[46] = $this->local_gettext("46th");
		// translators: abbreviated name of an ordinal number
		$ordinal[47] = $this->local_gettext("47th");
		// translators: abbreviated name of an ordinal number
		$ordinal[48] = $this->local_gettext("48th");
		// translators: abbreviated name of an ordinal number
		$ordinal[49] = $this->local_gettext("49th");
		// translators: abbreviated name of an ordinal number
		$ordinal[50] = $this->local_gettext("50th");
		
// arabic -> cardinal (i.e. no change)
		$cardinal = range(0, 50);
// !$number, we are simply loading the arrays for use elsewhere, so return
		if(!$number)
			return;
// get rid of leading '0' if necessary
		if($number < 10)
			$number += 0;
// If that number is not actually in array, we return the number as is
		if(array_key_exists($number, ${$convert}))
			return ${$convert}[$number];
		return $number;
	}
	
	/**
	 * Convert month to long name
	 *
	 * @return array
	 */
	public function monthToLongName()
	{
		return array(
			// translators: name of a month in words
			1 => $this->local_gettext("January"),
			// translators: name of a month in words
			2 => $this->local_gettext("February"),
			// translators: name of a month in words
			3 => $this->local_gettext("March"),
			// translators: name of a month in words
			4 => $this->local_gettext("April"),
			// translators: name of a month in words
			5 => $this->local_gettext("May"),
			// translators: name of a month in words
			6 => $this->local_gettext("June"),
			// translators: name of a month in words
			7 => $this->local_gettext("July"),
			// translators: name of a month in words
			8 => $this->local_gettext("August"),
			// translators: name of a month in words
			9 => $this->local_gettext("September"),
			// translators: name of a month in words
			10 => $this->local_gettext("October"),
			// translators: name of a month in words
			11 => $this->local_gettext("November"),
			// translators: name of a month in words
			12 => $this->local_gettext("December"),
			// translators: name of a month in words
			13 => $this->local_gettext("Spring"),
			// translators: name of a month in words
			14 => $this->local_gettext("Summer"),
			// translators: name of a month in words
			15 => $this->local_gettext("Autumn"),
			// translators: name of a month in words
			16 => $this->local_gettext("Winter"),
			// translators: name of a month in words
			17 => $this->local_gettext("Fall"),
		);
	}
	
	/**
	 * Convert month to short name
	 *
	 * @return array
	 */
	public function monthToShortName()
	{
		return array(
			// translators: abbreviated name of a month
			1 => $this->local_gettext("Jan"),
			// translators: abbreviated name of a month
			2 => $this->local_gettext("Feb"),
			// translators: abbreviated name of a month
			3 => $this->local_gettext("Mar"),
			// translators: abbreviated name of a month
			4 => $this->local_gettext("Apr"),
			// translators: abbreviated name of a month
			5 => $this->local_gettext("May"),
			// translators: abbreviated name of a month
			6 => $this->local_gettext("Jun"),
			// translators: abbreviated name of a month
			7 => $this->local_gettext("Jul"),
			// translators: abbreviated name of a month
			8 => $this->local_gettext("Aug"),
			// translators: abbreviated name of a month
			9 => $this->local_gettext("Sep"),
			// translators: abbreviated name of a month
			10 => $this->local_gettext("Oct"),
			// translators: abbreviated name of a month
			11 => $this->local_gettext("Nov"),
			// translators: abbreviated name of a month
			12 => $this->local_gettext("Dec"),
			// translators: abbreviated name of a month
			13 => $this->local_gettext("Spring"),
			// translators: abbreviated name of a month
			14 => $this->local_gettext("Summer"),
			// translators: abbreviated name of a month
			15 => $this->local_gettext("Autumn"),
			// translators: abbreviated name of a month
			16 => $this->local_gettext("Winter"),
			// translators: abbreviated name of a month
			17 => $this->local_gettext("Fall"),
		);
	}
	
	/**
	 * convert cardinal (arabic) to roman numerals
	 *
	 * @return array
	 */
	public function cardinalToRoman()
	{
        return array(
            1 => "I",
            2 => "II",
            3 => "III",
            4 => "IV",
            5 => "V",
            6 => "VI",
            7 => "VII",
            8 => "VIII",
            9 => "IX",
            10 => "X",
            11 => "XI",
            12 => "XII",
            13 => "XIII",
            14 => "XIV",
            15 => "XV",
            16 => "XVI",
            17 => "XVII",
            18 => "XVIII",
            19 => "XIX",
            20 => "XX",
            21 => "XXI",
            22 => "XXII",
            23 => "XXIII",
            24 => "XXIV",
            25 => "XXV",
            26 => "XXVI",
            27 => "XXVII",
            28 => "XXVIII",
            29 => "XXIX",
            30 => "XXX",
            31 => "XXXI",
            32 => "XXXII",
            33 => "XXXIII",
            34 => "XXXIV",
            35 => "XXXV",
            36 => "XXXVI",
            37 => "XXXVII",
            38 => "XXXVIII",
            39 => "XXXIX",
            40 => "XXXX",
            41 => "XXXXI",
            42 => "XXXXII",
            43 => "XXXXIII",
            44 => "XXXXIV",
            45 => "XXXXV",
            46 => "XXXXVI",
            47 => "XXXXVII",
            48 => "XXXXVIII",
            49 => "XXXXIX",
            50 => "L",
        );
	}
	/**
	 * convert ordinal to word
	 *
	 * @return array
	 */
	public function cardinalToOrdinalWord()
	{
		return array(
// Usually used for edition numbers in bibliographic styles that require words rather than arabic numerals.
// Any numbers not listed here will be returned without change.  e.g. here, 51 will be returned as 51 rather than 'fifty-first'.
// A maximum of 50 seems a reasonable number to go up to....
			// translators: name of an ordinal number in words
			"1" => $this->local_gettext("First"),
			// translators: name of an ordinal number in words
			"2" => $this->local_gettext("Second"),
			// translators: name of an ordinal number in words
			"3" => $this->local_gettext("Third"),
			// translators: name of an ordinal number in words
			"4" => $this->local_gettext("Fourth"),
			// translators: name of an ordinal number in words
			"5" => $this->local_gettext("Fifth"),
			// translators: name of an ordinal number in words
			"6" => $this->local_gettext("Sixth"),
			// translators: name of an ordinal number in words
			"7" => $this->local_gettext("Seventh"),
			// translators: name of an ordinal number in words
			"8" => $this->local_gettext("Eighth"),
			// translators: name of an ordinal number in words
			"9" => $this->local_gettext("Ninth"),
			// translators: name of an ordinal number in words
			"10" => $this->local_gettext("Tenth"),
			// translators: name of an ordinal number in words
			"11" => $this->local_gettext("Eleventh"),
			// translators: name of an ordinal number in words
			"12" => $this->local_gettext("Twelfth"),
			// translators: name of an ordinal number in words
			"13" => $this->local_gettext("Thirteenth"),
			// translators: name of an ordinal number in words
			"14" => $this->local_gettext("Fourteenth"),
			// translators: name of an ordinal number in words
			"15" => $this->local_gettext("Fifteenth"),
			// translators: name of an ordinal number in words
			"16" => $this->local_gettext("Sixteenth"),
			// translators: name of an ordinal number in words
			"17" => $this->local_gettext("Seventeenth"),
			// translators: name of an ordinal number in words
			"18" => $this->local_gettext("Eighteenth"),
			// translators: name of an ordinal number in words
			"19" => $this->local_gettext("Nineteenth"),
			// translators: name of an ordinal number in words
			"20" => $this->local_gettext("Twentieth"),
			// translators: name of an ordinal number in words
			"21" => $this->local_gettext("Twenty-first"),
			// translators: name of an ordinal number in words
			"22" => $this->local_gettext("Twenty-second"),
			// translators: name of an ordinal number in words
			"23" => $this->local_gettext("Twenty-third"),
			// translators: name of an ordinal number in words
			"24" => $this->local_gettext("Twenty-fourth"),
			// translators: name of an ordinal number in words
			"25" => $this->local_gettext("Twenty-fifth"),
			// translators: name of an ordinal number in words
			"26" => $this->local_gettext("Twenty-sixth"),
			// translators: name of an ordinal number in words
			"27" => $this->local_gettext("Twenty-seventh"),
			// translators: name of an ordinal number in words
			"28" => $this->local_gettext("Twenty-eighth"),
			// translators: name of an ordinal number in words
			"29" => $this->local_gettext("Twenty-ninth"),
			// translators: name of an ordinal number in words
			"30" => $this->local_gettext("Thirtieth"),
			// translators: name of an ordinal number in words
			"31" => $this->local_gettext("Thirty-first"),
			// translators: name of an ordinal number in words
			"32" => $this->local_gettext("Thirty-second"),
			// translators: name of an ordinal number in words
			"33" => $this->local_gettext("Thirty-third"),
			// translators: name of an ordinal number in words
			"34" => $this->local_gettext("Thirty-fourth"),
			// translators: name of an ordinal number in words
			"35" => $this->local_gettext("Thirty-fifth"),
			// translators: name of an ordinal number in words
			"36" => $this->local_gettext("Thirty-sixth"),
			// translators: name of an ordinal number in words
			"37" => $this->local_gettext("Thirty-seventh"),
			// translators: name of an ordinal number in words
			"38" => $this->local_gettext("Thirty-eighth"),
			// translators: name of an ordinal number in words
			"39" => $this->local_gettext("Thirty-ninth"),
			// translators: name of an ordinal number in words
			"40" => $this->local_gettext("Fourtieth"),
			// translators: name of an ordinal number in words
			"41" => $this->local_gettext("Fourty-first"),
			// translators: name of an ordinal number in words
			"42" => $this->local_gettext("Fourty-second"),
			// translators: name of an ordinal number in words
			"43" => $this->local_gettext("Fourty-third"),
			// translators: name of an ordinal number in words
			"44" => $this->local_gettext("Fourty-fourth"),
			// translators: name of an ordinal number in words
			"45" => $this->local_gettext("Fourty-fifth"),
			// translators: name of an ordinal number in words
			"46" => $this->local_gettext("Fourty-sixth"),
			// translators: name of an ordinal number in words
			"47" => $this->local_gettext("Fourty-seventh"),
			// translators: name of an ordinal number in words
			"48" => $this->local_gettext("Fourty-eighth"),
			// translators: name of an ordinal number in words
			"49" => $this->local_gettext("Fourty-ninth"),
			// translators: name of an ordinal number in words
			"50" => $this->local_gettext("Fiftieth"),
		);
	}
	
/**
 * Get the translation of a string
 *
 * This function exists only to force the string to stay in english
 * because some code like Bibtex and EndNote need the english version
 * of constants and Gettext is not able to switch the language
 * after a load in a single process.
 *
 * @param bool $english_string If TRUE, bypass gettext and return the string untranslated
 *
 * @return string
 */
	private function local_gettext($english_string)
	{
	    if ($this->force_english)
	        return $english_string;
	    else
	        return dgettext(WIKINDX_LANGUAGE_DOMAIN_DEFAULT, $english_string);
	}
}
