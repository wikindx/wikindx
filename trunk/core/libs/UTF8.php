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
 * UTF-8 routines
 *
 * @package wikindx\core\libs\UTF8
 */
namespace UTF8
{
    /**
     * Encode a string in UTF-8 if not already UTF-8
     *
     * Tools for validing a UTF-8 string is well formed.
     * The Original Code is Mozilla Communicator client code.
     * The Initial Developer of the Original Code is
     * Netscape Communications Corporation.
     * Portions created by the Initial Developer are Copyright (C) 1998
     * the Initial Developer. All Rights Reserved.
     * Ported to PHP by Henri Sivonen (http://hsivonen.iki.fi)
     * Slight modifications to fit with phputf8 library by Harry Fuecks (hfuecks gmail com)
     *
     * Tests a string as to whether it's valid UTF-8 and supported by the
     * Unicode standard
     *
     * @see http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUTF8ToUnicode.cpp
     * @see http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUnicodeToUTF8.cpp
     * @see http://hsivonen.iki.fi/php-utf8/
     * @see utf8_compliant
     *
     * @author <hsivonen@iki.fi>
     *
     * @param string $str UTF-8 encoded string
     *
     * @return string
     */
    function smartUtf8_encode($str)
    {
        $mState = 0;    // cached expected number of octets after the current octet
                        // until the beginning of the next UTF8 character sequence
        $mUcs4 = 0;     // cached Unicode character
        $mBytes = 1;    // cached expected number of octets in the current sequence
    
        $len = strlen($str);

        for ($i = 0; $i < $len; $i++) {
            $in = ord($str[$i]);
            if ($mState == 0) {
                // When mState is zero we expect either a US-ASCII character or a
                // multi-octet sequence.
                if (0 == (0x80 & ($in))) {
                    // US-ASCII, pass straight through.
                    $mBytes = 1;
                } elseif (0xC0 == (0xE0 & ($in))) {
                    // First octet of 2 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x1F) << 6;
                    $mState = 1;
                    $mBytes = 2;
                } elseif (0xE0 == (0xF0 & ($in))) {
                    // First octet of 3 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x0F) << 12;
                    $mState = 2;
                    $mBytes = 3;
                } elseif (0xF0 == (0xF8 & ($in))) {
                    // First octet of 4 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x07) << 18;
                    $mState = 3;
                    $mBytes = 4;
                } elseif (0xF8 == (0xFC & ($in))) {
                    /* First octet of 5 octet sequence.
                    *
                    * This is illegal because the encoded codepoint must be either
                    * (a) not the shortest form or
                    * (b) outside the Unicode range of 0-0x10FFFF.
                    * Rather than trying to resynchronize, we will carry on until the end
                    * of the sequence and let the later error handling code catch it.
                    */
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x03) << 24;
                    $mState = 4;
                    $mBytes = 5;
                } elseif (0xFC == (0xFE & ($in))) {
                    // First octet of 6 octet sequence, see comments for 5 octet sequence.
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 1) << 30;
                    $mState = 5;
                    $mBytes = 6;
                } else {
                    /* Current octet is neither in the US-ASCII range nor a legal first
                    * octet of a multi-octet sequence.
                     */
                    return utf8_encode($str);
                }
            } else {
                // When mState is non-zero, we expect a continuation of the multi-octet
                // sequence
                if (0x80 == (0xC0 & ($in))) {
                    // Legal continuation.
                    $shift = ($mState - 1) * 6;
                    $tmp = $in;
                    $tmp = ($tmp & 0x0000003F) << $shift;
                    $mUcs4 |= $tmp;
                    /**
                     * End of the multi-octet sequence. mUcs4 now contains the final
                     * Unicode codepoint to be output
                     */
                    if (0 == --$mState) {
                        /*
                         * Check for illegal sequences and codepoints.
                         */
                        // From Unicode 3.1, non-shortest form is illegal
                        if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
                        ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
                        ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
                        (4 < $mBytes) ||
                        // From Unicode 3.2, surrogate characters are illegal
                        (($mUcs4 & 0xFFFFF800) == 0xD800) ||
                        // Codepoints outside the Unicode range are illegal
                        ($mUcs4 > 0x10FFFF)) {
                            return utf8_encode($str);
                        }
                        //initialize UTF8 cache
                        $mState = 0;
                        $mUcs4 = 0;
                        $mBytes = 1;
                    }
                } else {
                    /**
                     *((0xC0 & (*in) != 0x80) && (mState != 0))
                     * Incomplete multi-octet sequence.
                     */
                    return utf8_encode($str);
                }
            }
        }

        return $str; // $str is UTF-8
    }
    /**
     * Decode UTF-8 ONLY if the input has been UTF-8-encoded.
     *
     * Adapted from 'nospam' in the user contributions at:
     * http://www.php.net/manual/en/function.utf8-decode.php
     *
     * @param string $inStr
     *
     * @return string
     */
    function smartUtf8_decode($inStr)
    {
        // Replace ? with a unique string
        $newStr = str_replace("?", "w0i0k0i0n0d0x", $inStr);
        // Try the utf8_decode
        $newStr = decodeUtf8($newStr);
        // if it contains ? marks
        if (strpos($newStr, "?") !== FALSE) {
            // Something went wrong, set newStr to the original string.
            $newStr = $inStr;
        } else {
            // If not then all is well, put the ?-marks back where is belongs
            $newStr = str_replace("w0i0k0i0n0d0x", "?", $newStr);
        }

        return $newStr;
    }
    /**
     * UTF-8 encoding - PROPERLY decode UTF-8 as PHP's utf8_decode can't hack it.
     *
     * Freely borrowed from morris_hirsch at http://www.php.net/manual/en/function.utf8-decode.php
     * bytes bits representation
     * 1  7  0bbbbbbb
     * 2  11  110bbbbb 10bbbbbb
     * 3  16  1110bbbb 10bbbbbb 10bbbbbb
     * 4  21  11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
     * Each b represents a bit that can be used to store character data.
     *
     * input CANNOT have single byte upper half extended ascii codes
     *
     * @param string $utf8_string
     *
     * @return string
     */
    function decodeUtf8($utf8_string)
    {
        $out = "";
        $ns = strlen($utf8_string);
        for ($nn = 0; $nn < $ns; $nn++) {
            $ch = $utf8_string[$nn];
            $ii = ord($ch);

            //1 7 0bbbbbbb (127)

            if ($ii < 128) {
                $out .= $ch;
            }

            //2 11 110bbbbb 10bbbbbb (2047)

            elseif ($ii >> 5 == 6) {
                $b1 = ($ii & 31);

                $nn++;
                $ch = $utf8_string[$nn];
                $ii = ord($ch);
                $b2 = ($ii & 63);

                $ii = ($b1 * 64) + $b2;

                $ent = sprintf("&#%d;", $ii);
                $out .= $ent;
            }

            //3 16 1110bbbb 10bbbbbb 10bbbbbb

            elseif ($ii >> 4 == 14) {
                $b1 = ($ii & 31);

                $nn++;
                $ch = $utf8_string[$nn];
                $ii = ord($ch);
                $b2 = ($ii & 63);

                $nn++;
                $ch = $utf8_string[$nn];
                $ii = ord($ch);
                $b3 = ($ii & 63);

                $ii = ((($b1 * 64) + $b2) * 64) + $b3;

                $ent = sprintf("&#%d;", $ii);
                $out .= $ent;
            }

            //4 21 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb

            elseif ($ii >> 3 == 30) {
                $b1 = ($ii & 31);

                $nn++;
                $ch = $utf8_string[$nn];
                $ii = ord($ch);
                $b2 = ($ii & 63);

                $nn++;
                $ch = $utf8_string[$nn];
                $ii = ord($ch);
                $b3 = ($ii & 63);

                $nn++;
                $ch = $utf8_string[$nn];
                $ii = ord($ch);
                $b4 = ($ii & 63);

                $ii = ((((($b1 * 64) + $b2) * 64) + $b3) * 64) + $b4;

                $ent = sprintf("&#%d;", $ii);
                $out .= $ent;
            }
        }

        return $out;
    }
    /**
     * Encode UTF-8 from unicode characters
     *
     * @param string $str
     *
     * @return string
     */
    function html_uentity_decode($str)
    {
        preg_match_all("/&#([0-9]*?);/u", $str, $unicode);
        foreach ($unicode[0] as $key => $value) {
            $str = "" . preg_replace("/" . $value . "/u", code2utf8($unicode[1][$key]), $str);
        }

        return $str;
    }
    /**
     * A unicode aware replacement for ucfirst()
     *
     * @author Andrea Rossato <arossato@istitutocolli.org>
     *
     * @see ucfirst()
     *
     * @param string $str
     *
     * @return string
     */
    function mb_ucfirst($str)
    {
        $fc = mb_substr($str, 0, 1);

        return mb_strtoupper($fc) . mb_substr($str, 1, mb_strlen($str));
    }

    /**
     * This simple utf-8 word count function (it only counts)
     * is a bit faster then the one with preg_match_all
     * about 10x slower then the built-in str_word_count
     *
     * If you need the hyphen or other code points as word-characters
     * just put them into the [brackets] like [^\p{L}\p{N}\'\-]
     * If the pattern contains utf-8, utf8_encode() the pattern,
     * as it is expected to be valid utf-8 (using the u modifier).
     *
     * @param mixed $str
     * @param mixed $format
     * @param mixed $charlist
     */

    /**
     * count UTF-8 words in a string
     *
     * @see https://www.php.net/manual/en/function.str-word-count.php
     *
     * @param string $str
     * @param string $format
     * @param string $charlist
     *
     * @return int|string[]
     */
    function mb_str_word_count($str, $format = 0, $charlist = "")
    {
        $preg_split_flags = ($format == 2) ? PREG_SPLIT_OFFSET_CAPTURE : 0;
        $res = preg_split('~[^\p{L}\p{N}' . preg_quote($charlist) . '\']+~u', $str, -1, $preg_split_flags);
    
        if ($format == 0) {
            return ($res !== FALSE) ? count($res) : 0;
        } elseif ($format == 1) {
            return ($res !== FALSE) ? $res : [];
        } elseif ($format == 2) {
            $res2 = [];
            foreach ($res as $m) {
                $res2[$m[1]] = $m[0];
            }
        } else {
            return 0;
        }
    }

    /**
     * Simulate chr() for multibytes strings
     *
     * @param string $dec
     *
     * @return string
     */
    function mb_chr($dec)
    {
        if (function_exists("mb_chr")) {
            $utf = mb_chr($dec);
        } else {
            if ($dec < 0x80) {
                $utf = chr($dec);
            } elseif ($dec < 0x0800) {
                $utf = chr(0xC0 + ($dec >> 6));
                $utf .= chr(0x80 + ($dec & 0x3f));
            } elseif ($dec < 0x010000) {
                $utf = chr(0xE0 + ($dec >> 12));
                $utf .= chr(0x80 + (($dec >> 6) & 0x3f));
                $utf .= chr(0x80 + ($dec & 0x3f));
            } elseif ($dec < 0x200000) {
                $utf = chr(0xF0 + ($dec >> 18));
                $utf .= chr(0x80 + (($dec >> 12) & 0x3f));
                $utf .= chr(0x80 + (($dec >> 6) & 0x3f));
                $utf .= chr(0x80 + ($dec & 0x3f));
            } else {
                // UTF-8 character size can't use more than 4 bytes!
                $utf = '';
            }
        }

        return $utf;
    }

    /**
     * Simulate explode() for multibytes strings (as documented for PHP 7.0)
     *
     * @param string $delimiter
     * @param string $string
     * @param int $limit Default is PHP_INT_MAX.
     *
     * @return string
     */
    function mb_explode($delimiter, $string, $limit = PHP_INT_MAX)
    {
        if ($delimiter == '') {
            return FALSE;
        }

        if ($limit === NULL) {
            PHP_INT_MAX;
        }
        if ($limit == 0) {
            $limit = 1;
        }

        $pattern = '/' . preg_quote($delimiter, '/') . '/u';

        $aString = preg_split($pattern, $string, $limit);

        if ($limit < 0 && count($aString) == 1) {
            return [];
        } elseif ($limit < 0 && count($aString) > 1) {
            $length = count($aString) - abs($limit);
            if ($length <= 0) {
                return [];
            } else {
                return array_slice($aString, 0, $length, TRUE);
            }
        } else {
            return $aString;
        }
    }

    /**
     * Simulate str_pad() for multibytes strings
     *
     * @param string $str
     * @param int $pad_len
     * @param string $pad_str Default is ' '.
     * @param string $dir Default is STR_PAD_RIGHT.
     * @param string $encoding Default is NULL.
     *
     * @return string
     */
    function mb_str_pad($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT, $encoding = NULL)
    {
        $encoding = $encoding === NULL ? mb_internal_encoding() : $encoding;
        $padBefore = $dir === STR_PAD_BOTH || $dir === STR_PAD_LEFT;
        $padAfter = $dir === STR_PAD_BOTH || $dir === STR_PAD_RIGHT;
        $pad_len -= mb_strlen($str, $encoding);
        $targetLen = $padBefore && $padAfter ? $pad_len / 2 : $pad_len;
        $strToRepeatLen = mb_strlen($pad_str, $encoding);
        $repeatTimes = ceil($targetLen / $strToRepeatLen);
        $repeatedString = str_repeat($pad_str, max(0, $repeatTimes)); // safe if used with valid utf-8 strings
        $before = $padBefore ? mb_substr($repeatedString, 0, floor($targetLen), $encoding) : '';
        $after = $padAfter ? mb_substr($repeatedString, 0, ceil($targetLen), $encoding) : '';

        return $before . $str . $after;
    }

    /**
     * Simulate strcasecmp() for multibytes strings
     *
     * A simple multibyte-safe case-insensitive string comparison
     *
     * @param string $str1
     * @param string $str2
     * @param string $encoding Default is NULL.
     *
     * @return string
     */
    function mb_strcasecmp($str1, $str2, $encoding = NULL)
    {
        if (NULL === $encoding) {
            $encoding = mb_internal_encoding();
        }

        return strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding));
    }

    /**
     * Simulate strrev() for multibytes strings
     *
     * @param string $str
     *
     * @return string
     */
    function mb_strrev($str)
    {
        preg_match_all('/./us', $str, $ar);

        return implode('', array_reverse($ar[0]));
    }

    /**
     * Simulate substr_replace() for multibytes strings
     *
     * @param string $string
     * @param string $replacement
     * @param int $start
     * @param int $length Default is NULL.
     * @param string $encoding Default is NULL.
     *
     * @return string
     */
    function mb_substr_replace($string, $replacement, $start, $length = NULL, $encoding = NULL)
    {
        if (extension_loaded('mbstring') === TRUE) {
            $string_length = (is_null($encoding) === TRUE) ? mb_strlen($string) : mb_strlen($string, $encoding);

            if ($start < 0) {
                $start = max(0, $string_length + $start);
            } elseif ($start > $string_length) {
                $start = $string_length;
            }

            if ($length < 0) {
                $length = max(0, $string_length - $start + $length);
            } elseif ((is_null($length) === TRUE) || ($length > $string_length)) {
                $length = $string_length;
            }

            if (($start + $length) > $string_length) {
                $length = $string_length - $start;
            }

            if (is_null($encoding) === TRUE) {
                return mb_substr($string, 0, $start) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length);
            }

            return mb_substr($string, 0, $start, $encoding) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length, $encoding);
        }

        return (is_null($length) === TRUE) ? substr_replace($string, $replacement, $start) : substr_replace($string, $replacement, $start, $length);
    }

    /**
     * Simulate ord() for UTF8 strings (not arbitrary multibytes strings)
     *
     * @param string $string
     *
     * @return string
     */
    function mb_ord($string)
    {
        if (function_exists("mb_ord")) {
            if ($string != "") {
                $utf = mb_ord($string);
            } else {
                $utf = 0;
            }
        } else {
            $offset = 0;
            while ($offset >= 0) {
                $code = ord(substr($string, $offset, 1));
                if ($code >= 128) {        //otherwise 0xxxxxxx
                    if ($code < 224) {
                        $bytesnumber = 2;
                    }                //110xxxxx
                    elseif ($code < 240) {
                        $bytesnumber = 3;
                    }        //1110xxxx
                    elseif ($code < 248) {
                        $bytesnumber = 4;
                    }    //11110xxx
                    $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
                    for ($i = 2; $i <= $bytesnumber; $i++) {
                        $offset++;
                        $code2 = ord(substr($string, $offset, 1)) - 128;        //10xxxxxx
                        $codetemp = $codetemp * 64 + $code2;
                    }
                    $code = $codetemp;
                }
                $offset += 1;
                if ($offset >= strlen($string)) {
                    $offset = -1;
                }
            }
            $utf = $code;
        }

        return $utf;
    }
    /**
     * Code by Ben XO at https://www.php.net/manual/en/ref.mbstring.php
     *
     * Trim characters from either (or both) ends of a string in a way that is
     * multibyte-friendly.
     *
     * Mostly, this behaves exactly like trim() would: for example supplying 'abc' as
     * the charlist will trim all 'a', 'b' and 'c' chars from the string, with, of
     * course, the added bonus that you can put unicode characters in the charlist.
     *
     * We are using a PCRE character-class to do the trimming in a unicode-aware
     * way, so we must escape ^, \, - and ] which have special meanings here.
     * As you would expect, a single \ in the charlist is interpretted as
     * "trim backslashes" (and duly escaped into a double-\ ). Under most circumstances
     * you can ignore this detail.
     *
     * As a bonus, however, we also allow PCRE special character-classes (such as '\s')
     * because they can be extremely useful when dealing with UCS. '\pZ', for example,
     * matches every 'separator' character defined in Unicode, including non-breaking
     * and zero-width spaces.
     *
     * It doesn't make sense to have two or more of the same character in a character
     * class, therefore we interpret a double \ in the character list to mean a
     * single \ in the regex, allowing you to safely mix normal characters with PCRE
     * special classes.
     *
     * *Be careful* when using this bonus feature, as PHP also interprets backslashes
     * as escape characters before they are even seen by the regex. Therefore, to
     * specify '\\s' in the regex (which will be converted to the special character
     * class '\s' for trimming), you will usually have to put *4* backslashes in the
     * PHP code - as you can see from the default value of $charlist.
     *
     * @param string
     * @param charlist list of characters to remove from the ends of this string.
     * @param boolean trim the left?
     * @param boolean trim the right?
     * @return String
     */ 
    function mb_trim($string, $charlist='\\\\s', $ltrim=true, $rtrim=true)
    {
        $both_ends = $ltrim && $rtrim;

        $char_class_inner = preg_replace(
            array( '/[\^\-\]\\\]/S', '/\\\{4}/S' ),
            array( '\\\\\\0', '\\' ),
            $charlist
        );

        $work_horse = '[' . $char_class_inner . ']+';
        $ltrim && $left_pattern = '^' . $work_horse;
        $rtrim && $right_pattern = $work_horse . '$';

        if($both_ends)
        {
            $pattern_middle = $left_pattern . '|' . $right_pattern;
        }
        elseif($ltrim)
        {
            $pattern_middle = $left_pattern;
        }
        else
        {
            $pattern_middle = $right_pattern;
        }

        return preg_replace("/$pattern_middle/usSD", '', $string);
    } 
    /**
     * convert an integer to its chr() representation
     *
     * @param int $num
     *
     * @return string
     */
    function code2utf8($num)
    {
        if ($num < 128) {
            return chr($num);
        }
        if ($num < 2048) {
            return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
        }
        if ($num < 65536) {
            return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        }
        if ($num < 2097152) {
            return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        }

        return '';
    }
}
