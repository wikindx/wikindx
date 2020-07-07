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
 * UTF-8 routines
 *
 * @package wikindx\core\utf8
 */
class UTF8
{
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
    public static function decodeUtf8($utf8_string)
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
    public static function html_uentity_decode($str)
    {
        preg_match_all("/&#([0-9]*?);/u", $str, $unicode);
        foreach ($unicode[0] as $key => $value) {
            $str = "" . preg_replace("/" . $value . "/u", self::code2utf8($unicode[1][$key]), $str);
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
    public static function mb_ucfirst($str)
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
    public static function mb_str_word_count($str, $format = 0, $charlist = "")
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
     * Simulate explode() for multibytes strings (as documented for PHP 7.0)
     *
     * @param string $delimiter
     * @param string $string
     * @param int $limit Default is PHP_INT_MAX.
     *
     * @return string
     */
    public static function mb_explode($delimiter, $string, $limit = PHP_INT_MAX)
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
    public static function mb_str_pad($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT, $encoding = NULL)
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
    public static function mb_strcasecmp($str1, $str2, $encoding = NULL)
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
    public static function mb_strrev($str)
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
    public static function mb_substr_replace($string, $replacement, $start, $length = NULL, $encoding = NULL)
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
     * convert an integer to its chr() representation
     *
     * @param int $num
     *
     * @return string
     */
    private static function code2utf8($num)
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
