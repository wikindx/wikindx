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
 * Interface for AJAX elements in various pages.
 *
 * @package wikindx\core\libs\AJAX
 */
namespace AJAX
{
    /**
     * AJAX
     *
     * @param mixed $scripts
     */
    /**
     * Load the ajax javascript and the user javascript(s) into the HTML page output.
     *
     * @param mixed $scripts Either an array of .js scripts to load or a single string.  It can be FALSE.
     */
    function loadJavascript($scripts = FALSE)
    {
        $pString = '';
        if (!is_array($scripts)) {
            $scripts = [$scripts];
        }
        foreach ($scripts as $script) {
            $pString .= \HTML\jsInlineExternal($script);
        }
        \GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Create and load the HTML form element insert for the AJAX action and function.
     *
     * @param string $action The javascript action such as 'onclick' etc.
     * @param array $jsonArray The parameter to be passed to gateway() as an array ready to be converted to JSON
     * @param bool $return If TRUE, generated javascript has a return from the gateway() function. Default is FALSE.
     *
     * @return string
     */
    function jActionForm($action, $jsonArray, $return = FALSE)
    {
        $json = \AJAX\encode_jArray($jsonArray, TRUE);
        if ($return) {
            $return = 'return';
        }

        return "$action=\"$return gateway('$json');\"";
    }
    /**
     * Create and load the IMG element insert for the AJAX action and function.  Works with core/libs/LOADICONS.php.
     *
     * @param string $icon The icon to make an image of ('add', 'delete', 'view' etc.)
     * @param string $action The javascript action such as 'onclick' etc.
     * @param array $jsonArray The parameter to be passed to gateway() as an array ready to be converted to JSON
     * @param bool $return If TRUE, generated javascript has a return from the gateway() function. Default is FALSE.
     *
     * @return string The image tag
     */
    function jActionIcon($icon, $action, $jsonArray, $return = FALSE)
    {
        if ($return) {
            $return = "return";
        }
        $icons = \FACTORY_LOADICONS::getInstance($icon);
        $json = \AJAX\encode_jArray($jsonArray, TRUE);

        return \HTML\span($icons->getHTML($icon), "cursorPointer", "", "$action=\"$return gateway('$json')\"");
    }
    /**
     * Convert $jsonArray to JSON string and format any array elements referencing javascript functions.
     *
     * @param array $jsonArray The unformatted JSON array
     * @param bool $quote No encoding of quotation marks (") if set to FALSE. Default is FALSE.
     *
     * @return string The JSON string
     */
    function encode_jArray($jsonArray, $quote = FALSE)
    {
        if ($quote) {
            array_walk_recursive($jsonArray, function (&$value) {
                $value = addslashes($value);
            });
            $json = json_encode($jsonArray);
            \AJAX\_json_error('encode');
            $json = htmlspecialchars($json, ENT_QUOTES);
        } else {
            $json = json_encode($jsonArray);
            \AJAX\_json_error('encode');
        }

        return $json;
    }
    /**
     * Convert JSON-formatted $jsonString to an array or object.
     *
     * @param string $jsonString The JSON string
     *
     * @return mixed Array or object
     */
    function decode_jString($jsonString)
    {
        // Always return an associative array
        $return = json_decode(stripslashes($jsonString), TRUE);
        \AJAX\_json_error('decode');

        return $return;
    }
    /**
     * echo JSON error or return if none.
     *
     * @param string $type
     */
    function _json_error($type)
    {
        if (json_last_error() != JSON_ERROR_NONE) {
            \GLOBALS::addTplVar('content', 'JSON ' . $type . ' error - ' . json_last_error_msg() . ': ');
        }
    }
}
