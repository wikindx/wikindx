<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */


/**
 * Miscellaneous BBCode elements
 *
 * @package wikindx\core\display
 */
class BBCODE
{
    /**
     * Strip all BBCode tags
     *
     * @param string $string
     *
     * @return string
     */
    public static function stripBBCode($string)
    {
        return "" . preg_replace("/\\[.*\\]|\\[\\/.*\\]/Uu", '', $string);
    }
    /**
     * replace [x]...[/x] BBcode with HTML code
     *
     * Used for display back from DB table
     *
     * @param string $string
     *
     * @return string
     */
    public static function bbCodeToHtml($string)
    {
        // First things first: If there aren't any "[*" or hyperlinks strings in the message, we don't
        // need to process it at all.
        if (!preg_match("/\\[.*|www\\.|ftp\\./u", $string))
        {
            return $string;
        }
        // code - this must be parsed first so that other BBCode within [code]...[/code] can be safely removed.
        $string = preg_replace_callback(
            "/\\[code\\](.*)\\[\\/code\\]/isu",
            ['self', 'codeCallback'],
            $string
        );
        $pattern = [
            "/\\[nl\\]/siu",
            "/\\[b\\](.*?)\\[\\/b\\]/siu",
            "/\\[u\\](.*?)\\[\\/u\\]/siu",
            "/\\[i\\](.*?)\\[\\/i\\]/siu",
            "/\\[sup\\](.*?)\\[\\/sup\\]/siu",
            "/\\[sub\\](.*?)\\[\\/sub\\]/siu",
            "/\\[size=(.*?)\\](.*?)\\[\\/size\\]/isu",
            "/\\[color=(.*?)\\](.*?)\\[\\/color\\]/isu",
            "/\\[float=(.*?)\\](.*?)\\[\\/float\\]/isu",
            //							"@(www([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@isu",
            //							"@(ftp([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@isu",
            //							"@((https?|ftp)://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@isu",
            "/\\[url\\](.*?)\\[\\/url\\]/isu",
            "/\\[url=(.*?)\\](.*?)\\[\\/url\\]/isu",
            "/\\[email\\](.*?)\\[\\/email\\]/isu",
            "/\\[img\\](.*?)\\[\\/img\\]/isu",
            "/\\[align=(.*?)\\](.*?)\\[\\/align\\]/isu",
        ];
        $change = [
            "<br>",
            "<strong>$1</strong>",
            "<span style=\"text-decoration: underline;\">$1</span>",
            "<em>$1</em>",
            "<sup>$1</sup>",
            "<sub>$1</sub>",
            "<span style=\"font-size: $1px;\">$2</span>",
            "<span style=\"color: $1;\">$2</span>",
            "<span style=\"float: $1; margin: 0.25em\" display: inline>$2</span>",
            //							"<a class=\"link\" href=\"http://\\1\" target=\"_blank\">\\1</a>",
            //							"<a class=\"link\" href=\"ftp://\\1\" target=\"_blank\">\\1</a>",
            "<a class=\"link\" href=\"$1\" target=\"_blank\">$1</a>",
            "<a class=\"link\" href=\"$1\" target=\"_blank\">$2</a>",
            "<a class=\"link\" href=\"mailto:$1\">$1</a>",
            "<img src=\"$1\" border=\"0\" alt=\"\">",
            "<div align=\"$1\">$2</div>",
        ];
        $string = preg_replace($pattern, $change, $string);
        // image + dimensions

        $imgWidthLimit = FACTORY_SESSION::getInstance()->getVar("config_configImgWidthLimit");
        $imgHeightLimit = FACTORY_SESSION::getInstance()->getVar("config_configImgHeightLimit");

        $string = preg_replace_callback(
            "/\\[img=(.*)\\*(.*)\\](.*?)\\[\\/img\\]/isu",
            function ($matches) use ($imgWidthLimit, $imgHeightLimit) {
                $width = ($matches[1] > $imgWidthLimit) ? $imgWidthLimit : $matches[1];
                $height = ($matches[2] > $imgHeightLimit) ? $imgHeightLimit : $matches[2];

                return "<img src=\"$matches[3]\" width=\"$width\" height=\"$height\" alt=\"\">";
            },
            $string
        );
        // bbencode_list requires initial ' ' - remove it after processing.
        return mb_substr(self::bbencode_list(' ' . $string), 1);
    }
    /**
     * Callback function for [code]...[/code] which cannot have other BBCode within it
     *
     * @param array $matches
     *
     * @return string
     */
    public static function codeCallback($matches)
    {
        return "<code>" . preg_replace("/\\[.*\\]+(.+?)\\[\\/.*\\]*/su", "$1", $matches[1]) . "</code>";
    }
    /**
     * Decode [list]...[/list] and [list=xx]...[/list]
     *
     * This has been shamelessly pinched from PHP Bulletin Board code with a little debugging....
     * Nathan Codding - Jan. 12, 2001.
     * Performs [list][/list] and [list=?][/list] bbencoding on the given string, and returns the results.
     * Any unmatched "[list]" or "[/list]" token will just be left alone.
     * This works fine with both having more than one list in a message, and with nested lists.
     * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
     *
     * Note: This function assumes the first character of $message is a space, which is added by
     * bbencode().
     *
     * @param string $message
     *
     * @return string
     */
    public static function bbencode_list($message)
    {
        $start_length = [];
        $start_length['ordered'] = 8;
        $start_length['unordered'] = 6;

        // First things first: If there aren't any "[list" strings in the message, we don't
        // need to process it at all.

        if (mb_strpos(mb_strtolower($message), "[list") === FALSE)
        {
            return $message;
        }
        $stack = [];
        $curr_pos = 1;
        while ($curr_pos && ($curr_pos < mb_strlen($message)))
        {
            $curr_pos = mb_strpos($message, "[", $curr_pos);

            // If not found, $curr_pos will be 0, and the loop will end.
            if ($curr_pos)
            {
                // We found a [. It starts at $curr_pos.
                // check if it's a starting or ending list tag.
                $possible_ordered_start = mb_substr($message, $curr_pos, $start_length['ordered']);
                $possible_unordered_start = mb_substr($message, $curr_pos, $start_length['unordered']);
                $possible_end = mb_substr($message, $curr_pos, 7);
                if (UTF8::mb_strcasecmp("[list]", $possible_unordered_start) == 0)
                {
                    // We have a starting unordered list tag.
                    // Push its position on to the stack, and then keep going to the right.
                    array_push($stack, [$curr_pos, '']);
                    ++$curr_pos;
                }
                elseif (preg_match("/\\[list=([a1i])\\]/siu", $possible_ordered_start, $matches))
                {
                    // We have a starting ordered list tag.
                    // Push its position on to the stack, and the starting char onto the start
                    // char stack, the keep going to the right.
                    array_push($stack, [$curr_pos, $matches[1]]);
                    ++$curr_pos;
                }
                elseif (UTF8::mb_strcasecmp("[/list]", $possible_end) == 0)
                {
                    // We have an ending list tag.
                    // Check if we've already found a matching starting tag.
                    if (count($stack) > 0)
                    {
                        // There exists a starting tag.
                        // We need to do 2 replacements now.
                        $start = array_pop($stack);
                        $start_index = $start[0];
                        $start_char = $start[1];
                        $is_ordered = ($start_char != '');
                        $start_tag_length = ($is_ordered) ? $start_length['ordered'] : $start_length['unordered'];
                        // everything before the [list] tag.
                        $before_start_tag = mb_substr($message, 0, $start_index);
                        // everything after the [list] tag, but before the [/list] tag.
                        $between_tags = mb_substr(
                            $message,
                            $start_index + $start_tag_length,
                            $curr_pos - $start_index - $start_tag_length
                        );
                        // Need to replace [*] with <LI> inside the list.
                        $between_tags = str_replace('[*]', '<li>', $between_tags);
                        // everything after the [/list] tag.
                        $after_end_tag = mb_substr($message, $curr_pos + 7);

                        if ($is_ordered)
                        {
                            $message = $before_start_tag . "<ol type=\"" . $start_char . "\">";
                            $message .= $between_tags . "</ol>";
                        }
                        else
                        {
                            $message = $before_start_tag . "<ul>";
                            $message .= $between_tags . "</ul>";
                        }
                        $message .= $after_end_tag;
                        // Now.. we've screwed up the indices by changing the length of the string.
                        // So, if there's anything in the stack, we want to resume searching just after it.
                        // otherwise, we go back to the start.
                        if (count($stack) > 0)
                        {
                            $a = array_pop($stack);
                            $curr_pos = $a[0];
                            array_push($stack, $a);
                            ++$curr_pos;
                        }
                        else
                        {
                            $curr_pos = 1;
                        }
                    }
                    else
                    {
                        // No matching start tag found. Increment pos, keep going.
                        ++$curr_pos;
                    }
                }
                else
                {
                    // No starting tag or ending tag.. Increment pos, keep looping.
                    ++$curr_pos;
                }
            }
        } // while
        return $message;
    }
}
