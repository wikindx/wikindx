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
 * Miscellaneous HTML elements except forms
 *
 * @package wikindx\core\libs\HTML
 */
namespace HTML
{
    /**
     * HTML
     *
     * @param mixed $name
     * @param mixed $value
     */

    /**
     * Build a string for insertion of an HTML tag attribute.
     *
     * Ensures that the attribute value is never empty (incorrect syntax)
     *
     * @param string $name Default is ''
     * @param string $value Default is ''
     *
     * @return string
     */
    function _inlineHtmlAttribute($name = '', $value = '')
    {
        return ' ' . rtrim($name) . '="' . str_replace('"', "&quot;", $value) . '"';
    }
/// HTML BLOCK TAGS (memento)
/// * HTML strict: address, blockquote, dl, div, fieldset, form, h(1-6), hr, noscript, ol, p, pre, table, ul
/// * HTML transitional only: center, dir, menu, noframes, isindex

    /**
     * <Hx> heading element
     *
     * @param string $data Default is ''
     * @param string $class Default is ''
     * @param int $level Default is 4
     *
     * @return string
     */
    function h($data = '', $class = '', $level = 4)
    {
        // this tag must have a value
        $data = trim($data);
        if ($data == '')
        {
            $data = "&nbsp;";
        }

        return '<h' . $level . ' ' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . $data . '</h' . $level . '>';
    }
    /**
     * <DIV> element
     *
     * If no $data, then this is probably used in conjunction with AJAX to hide or unhide a page element
     *
     * @param int $id
     * @param string $data Default is ''
     * @param string $class Default is ''
     *
     * @return string
     */
    function div($id, $data = '', $class = '')
    {
        // this tag must have a value
        $data = trim($data);

        // TODO : debug advanced search which break with <div>&nbsp;</div>
        //if ($data == '')
        //$data = "&nbsp;";

        return '<div'
            . \HTML\_inlineHtmlAttribute('class', $class)
            . \HTML\_inlineHtmlAttribute('id', $id)
            . '>' . $data . '</div>';
    }
    /**
     * <IFRAME> element
     *
     * If no $data, then this is probably used in conjunction with AJAX to hide or unhide a page element
     *
     * @param int $id
     * @param string $data Default is ''
     * @param string $class Default is ''
     *
     * @return string
     */
    function iframe($id, $data = '', $class = '')
    {
        // this tag must have a value
        $data = trim($data);
        if ($data == '')
        {
            $data = '&nbsp;';
        }

        return '<iframe'
            . \HTML\_inlineHtmlAttribute('class', $class)
            . \HTML\_inlineHtmlAttribute('id', $id)
            . \HTML\_inlineHtmlAttribute('name', $id)
            . '>' . $data . '</iframe>';
    }
    /**
     * <P> element
     *
     * @param string $data Default is ''
     * @param string $class Default is ''
     * @param string $align Default is 'left'
     *
     * @return string
     */
    function p($data = '', $class = '', $align = 'left')
    {
        // this tag must have a value
        $data = trim($data);
        if ($data == '')
        {
            $data = '&nbsp;';
        }

        return '<p' . \HTML\_inlineHtmlAttribute('class', $class . ' ' . $align) . '>' . $data . '</p>';
    }
    /**
     * <P> element
     *
     * <P> for browsing creators, collections etc. (tag colour) where a background colour needs to be specified
     *
     * @param string $data Default is ''
     * @param string $class Default is ''
     *
     * @return string
     */
    function pBrowse($data = '', $class = '')
    {
        return \HTML\p($data, "browseParagraph $class");
    }
    /**
     * <UL> element
     *
     * @param string $data Default is ''
     * @param string $class Default is ''
     *
     * @return string
     */
    function ul($data = '', $class = '')
    {
        // this tag must have a value
        $data = trim($data);
        if ($data == '')
        {
            $data = '&nbsp;';
        }

        return '<ul' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . $data . '</ul>';
    }
    /**
     * <OL> element
     *
     * @param string $data Default is ''
     * @param string $class Default is ''
     *
     * @return string
     */
    function ol($data = '', $class = '')
    {
        // this tag must have a value
        $data = trim($data);
        if ($data == '')
        {
            $data = '&nbsp;';
        }

        return '<ol' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . $data . '</ol>';
    }
    /**
     * <LI> element
     *
     * @param string $data Default is ''
     * @param string $class Default is ''
     *
     * @return string
     */
    function li($data = '', $class = '')
    {
        // this tag must have a value
        $data = trim($data);
        if ($data == '')
        {
            $data = '&nbsp;';
        }

        return '<li' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . $data . '</li>';
    }
    /**
     * <HR> element
     *
     * @param string $class Default is ''
     *
     * @return string
     */
    function hr($class = '')
    {
        return '<hr' . \HTML\_inlineHtmlAttribute('class', $class) . '>';
    }


/// HTML INLINE TAGS (memento)
/// * HTML strict: a, abbr, acronym, bdo, big, br, button, cite, code, dfn,
///                em, img, input, iframe, kbd, label, map, object, q, samp,
///                script, select, small, span, strong, sub, sup, textarea,
///                tt, var
/// * HTML transitional only: applet, basefont, font, iframe, u, s, strike
/// These elements should not be followed by newline without reason
/// because it is included in the course of the text.


    /**
     * <SPAN> element
     *
     * @param string $data Default is ''
     * @param string $class Default is ''
     * @param string $title Default is ''
     * @param string $js Default is ''
     *
     * @return string
     */
    function span($data = '', $class = '', $title = '', $js = '')
    {
        // this tag can be empty sometimes
        $string = '<span'
            . \HTML\_inlineHtmlAttribute('class', $class)
            . \HTML\_inlineHtmlAttribute('title', $title)
            . ' ' . $js . '>' . $data . '</span>';

        return $string;
    }
    /**
     * <SPAN color> element
     *
     * @param string $data Default is ''
     * @param string $class Default is 'blackText'
     *
     * @return string
     */
    function color($data = '', $class = 'blackText')
    {
        // this tag must have a value
        $data = trim($data);
        if ($data == '')
        {
            return '';
        }
        else
        {
            return '<span' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . $data . '</span>';
        }
    }
    /**
     * <STRONG> element (Semantic equivalent of <B>)
     *
     * @param string $data Default is ''
     * @param string $class Default is ''
     *
     * @return string
     */
    function strong($data = '', $class = '')
    {
        // this tag must have a value
        $data = trim($data);
        if ($data == '')
        {
            return '';
        }
        else
        {
            return '<strong' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . $data . '</strong>';
        }
    }
    /**
     * <EM> element (Semantic equivalent of <I>)
     *
     * @param string $data Default is ''
     * @param string $class Default is ''
     *
     * @return string
     */
    function em($data = '', $class = '')
    {
        // this tag must have a value
        $data = trim($data);
        if ($data == '')
        {
            return '';
        }
        else
        {
            return '<em' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . $data . '</em>';
        }
    }
    /**
     * <U> element
     *
     * @param string $data
     * @param string $class Default is ''
     *
     * @return string
     */
    function u($data, $class = '')
    {
        return \HTML\span($data, "u $class");
    }
    /**
     * <IMG> element
     *
     * @param string $src
     * @param int $width
     * @param int $height
     * @param string $title Default is ''
     * @param string $alt Default is ''
     * @param string $js Default is ''
     *
     * @return string
     */
    function img($src, $width, $height, $title = '', $alt = '', $js = '')
    {
        $string = '<img'
            . \HTML\_inlineHtmlAttribute('src', $src)
            . \HTML\_inlineHtmlAttribute('width', $width)
            . \HTML\_inlineHtmlAttribute('height', $height)
            . \HTML\_inlineHtmlAttribute('title', $title)
            . \HTML\_inlineHtmlAttribute('alt', $alt)
            . ' ' . $js . '>';

        return $string;
    }
    /**
     * <A> element used as internal anchor
     *
     * @param string $name Default is ''
     * @param string $data Default is ''
     * @param string $title Default is ''
     *
     * @return string
     */
    function aName($name = '', $data = '', $title = '')
    {
        $string = '<a'
            . \HTML\_inlineHtmlAttribute('id', $name)
            . \HTML\_inlineHtmlAttribute('name', $name)
            . \HTML\_inlineHtmlAttribute('title', $title)
            . '>' . $data . '</a>';

        return $string;
    }
    /**
     * <A> element used as hyperlink
     *
     * @param string $class
     * @param string $label
     * @param string $link
     * @param string $target Default is ''
     * @param string $title Default is ''
     *
     * @return string
     */
    function a($class, $label, $link, $target = '', $title = '')
    {
        $string = '<a'
            . \HTML\_inlineHtmlAttribute('class', $class)
            . \HTML\_inlineHtmlAttribute('href', $link)
            . \HTML\_inlineHtmlAttribute('target', $target)
            . \HTML\_inlineHtmlAttribute('title', $title)
            . '>' . $label . '</a>';

        return $string;
    }
    /**
     * <A> element used as hyperlink
     *
     * Hyperlinks for browsing creators, collections etc. (tag clouds) where the text colour and size is provided by the scripts to indicate frequency
     *
     * @param string $color Default is '#000'
     * @param string $size Default is '1em'
     * @param string $label Default is ''
     * @param string $link Default is ''
     * @param string $target Default is ''
     * @param string $title Default is ''
     * @param string $js Default is ''
     *
     * @return string
     */
    function aBrowse($color = '#000', $size = '1em', $label = '', $link ='', $target = '', $title = '', $js = '')
    {
        $string = '<a'
            . \HTML\_inlineHtmlAttribute('class', 'browseLink')
            . \HTML\_inlineHtmlAttribute('style', "color: $color; font-size: $size;")
            . \HTML\_inlineHtmlAttribute('href', $link)
            . \HTML\_inlineHtmlAttribute('target', $target)
            . \HTML\_inlineHtmlAttribute('title', $title)
            . ' ' . $js . '>' . $label . '</a>';

        return $string;
    }


    /**
     * Start a <TABLE> tag
     *
     * @param string $class Default is ''
     *
     * @return string
     */
    function tableStart($class = '')
    {
        return '<table' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . LF;
    }
    /**
     * Close a <TABLE> tag
     *
     * @return string
     */
    function tableEnd()
    {
        return '</table>' . LF;
    }
    /**
     * provide a table <caption>
     *
     * @param string $caption Default is ''
     * @param string $class Default is ''
     *
     * @return string
     */
    function tableCaption($caption = '', $class = '')
    {
        $caption = trim($caption);
        if ($caption == '')
        {
            return '';
        }
        else
        {
            return '<caption' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . $caption . '</caption>' . LF;
        }
    }
    /**
     * Provide a <tbody> tag
     *
     * @param string $class Default is ''
     *
     * @return string
     */
    function tbodyStart($class = '')
    {
        return '<tbody' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . LF;
    }
    /**
     * Provide a </tbody> tag
     *
     * @return string
     */
    function tbodyEnd()
    {
        return '</tbody>' . LF;
    }
    /**
     * Provde a <thead> tag
     *
     * @param string $class Default is ''
     *
     * @return string
     */
    function theadStart($class = '')
    {
        return '<thead' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . LF;
    }
    /**
     * Provide a </thead> tag
     *
     * @return string
     */
    function theadEnd()
    {
        return '</thead>' . LF;
    }
    /**
     * Provide a <tfoot> tag
     *
     * @param string $class Default is ''
     *
     * @return string
     */
    function tfootStart($class = '')
    {
        return '<tfoot' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . LF;
    }
    /**
     * Provide a </tfoot> tag
     *
     * @return string
     */
    function tfootEnd()
    {
        return '</tfoot>' . LF;
    }
    /**
     * Provide a <tr> tag
     *
     * @param string $class Default is ''
     *
     * @return string
     */
    function trStart($class = '')
    {
        return '<tr' . \HTML\_inlineHtmlAttribute('class', $class) . '>' . LF;
    }
    /**
     * Provide a </tr> tag
     *
     * @return string
     */
    function trEnd()
    {
        return '</tr>' . LF;
    }
    /**
     * Provide a <td> tag without closing it or encapsulating data
     *
     * @param string $class Default is ''
     * @param string $colspan Default is ''
     *
     * @return string
     */
    function tdStart($class = '', $colspan = '')
    {
        return '<td'
            . \HTML\_inlineHtmlAttribute('class', $class)
            . \HTML\_inlineHtmlAttribute('colspan', $colspan)
            . '>';
    }
    /**
     * Provide a </td> tag
     *
     * @return string
     */
    function tdEnd()
    {
        return '</td>' . LF;
    }
    /**
     * Provide a <td>...</td> tag
     *
     * @param string $data Default is ''
     * @param string $class Default is ''
     * @param string $colspan Default is ''
     *
     * @return string
     */
    function td($data = '', $class = '', $colspan = '')
    {
        // this tag must have a value
        $data = trim($data);
        if ($data == '')
        {
            $data = '&nbsp;';
        }

        return '<td'
            . \HTML\_inlineHtmlAttribute('class', $class)
            . \HTML\_inlineHtmlAttribute('colspan', $colspan)
            . '>' . $data . '</td>' . LF;
    }
    /**
     * Provide a <th> tag without closing it or encapsulating data
     *
     * @param string $class Default is ''
     * @param string $colspan Default is ''
     *
     * @return string
     */
    function thStart($class = '', $colspan = '')
    {
        return '<th'
            . \HTML\_inlineHtmlAttribute('class', $class)
            . \HTML\_inlineHtmlAttribute('colspan', $colspan)
            . '>';
    }
    /**
     * Provide a </th> tag
     *
     * @return string
     */
    function thEnd()
    {
        return '</th>' . LF;
    }
    /**
     * Provide a <th>...</th> tag
     *
     * @param string $data Default is ''
     * @param string $class Default is ''
     * @param string $colspan Default is ''
     *
     * @return string
     */
    function th($data = '', $class = '', $colspan = '')
    {
        // this tag must have a value
        $data = trim($data);
        if ($data == '')
        {
            $data = '&nbsp;';
        }

        return '<th'
            . \HTML\_inlineHtmlAttribute('class', $class)
            . \HTML\_inlineHtmlAttribute('colspan', $colspan)
            . '>' . $data . '</th>' . LF;
    }

    /**
     * Inlining JavaScript code
     *
     * @param string $function Default is ''
     *
     * @return string
     */
    function jsInline($function = '')
    {
        $function = trim($function);

        if ($function == '')
        {
            return '';
        }
        else
        {
            return '<script>' . LF . $function . LF . '</script>';
        }
    }
    /**
     * Insert a call to an external javascript
     *
     * @param string $src Default is ''
     *
     * @return string
     */
    function jsInlineExternal($src = '')
    {
        $src = trim($src);

        if ($src == '')
        {
            return '';
        }
        else
        {
            return '<script'
                . \HTML\_inlineHtmlAttribute('src', $src)
                . '></script>';
        }
    }


/// METHODS FOR CLEANING UP HTML CODE

    /**
     * replace newlines and carriage returns with appropriate HTML code.
     *
     * first multiples then singles.
     * Used for display back from DB table
     *
     * @param string $string
     *
     * @return string
     */
    function nlToHtml($string)
    {
        $string = preg_replace("/(\015?\012){2,}/u", BR . BR, $string);
        $string = preg_replace("/(\\\\r?\\\\n){2,}/u", BR . BR, $string);
        $string = preg_replace("/(\\\\r?\\\\n){1,}/u", BR . LF, $string);

        return $string;
    }
    /**
     * replace HTML newlines and carriage returns with appropriate ANSI code.
     *
     * first multiples then singles.
     * Used for display back from DB table
     *
     * @param string $string
     *
     * @return string
     */
    function htmlToNl($string)
    {
        $string = preg_replace("#(.*)<br>#ui", "$1\r" . LF, $string);
        //		$string = preg_replace("#<p.*>(.*)</p>#ui", "$1\r\n\r". LF, $string);
        return preg_replace("#<p>(.*)</p>#ui", "$1\r\n\r" . LF, $string);
    }
    /**
     * remove all newlines.
     *
     * For cases when user cut 'n' pastes multiple lines into single-line text box
     * Used before writing to DB table
     *
     * @param string $string
     *
     * @return string
     */
    function removeNl($string)
    {
        $string = preg_replace("/(\015?\012){2,}/u", " ", $string);

        return preg_replace("/\r|\n/u", " ", $string);
    }
    /**
     * Format text grabbed from database for printing to form elements.
     *
     * @param string $string
     * @param bool $stripHtml Default is FALSE
     *
     * @return string
     */
    function dbToFormTidy($string, $stripHtml = FALSE)
    {
        if ($stripHtml)
        {
            return \HTML\stripHtml($string);
        }
        else
        {
            return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5);
        }
    }
    /**
     * Format text grabbed from database for printing to HTML (should be just where the original input was from text input elements).
     *
     * @param string $string
     *
     * @return string
     */
    function dbToHtmlTidy($string)
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5);
    }
    /**
     * Format text grabbed from database for printing to HTML popups.
     *
     * @param string $string
     *
     * @return string
     */
    function dbToHtmlPopupTidy($string)
    {
        return htmlspecialchars(\HTML\stripHtml($string), ENT_QUOTES | ENT_HTML5);
    }
    /**
     * Strip HTML from string
     *
     * @param string $string
     *
     * @return string
     */
    function stripHtml($string)
    {
        $search = ['@<script[^>]*?>.*?</script>@siu',  // Strip out javascript
            '@<style[^>]*?>.*?</style>@siuU',    // Strip style tags properly
            '@<[\/\!]*?[^<>]*?>@siu',            // Strip out HTML tags
            '@<![\s\S]*?--[ \t\n\r]*>@u',         // Strip multi-line comments including CDATA
        ];

        return preg_replace($search, '', $string);
    }
    /**
     * Format text grabbed from database for printing to tinyMCE form elements.
     *
     * @param string $string
     *
     * @return string
     */
    function dbToTinyMCE($string)
    {
        return stripslashes(str_replace('"', '&quot;', $string));
    }
}
