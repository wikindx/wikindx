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
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "startup", "WEBSERVERCONFIG.php"]));

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Special Characters</title>

	<style type="text/css">
	body {
		background-color:#EEE;
	}

	#spCharTable {
		border: 1px solid black;
		margin-right: auto;
		margin-left: auto;
		border-spacing:0;
		border-collapse:collapse;
		width: 100%;
	}

	#spCharTable td {
		text-align: center;
		border: 1px solid gray;
		padding : 2px;
		font-size : 16px;
		cursor:default;
	}

	#spCharTable td:hover {
		background-color: red;
		color: #FFF;
		cursor:pointer;
	}

	#spCharTable td.charsetHeading {
		background-color: #AAA;
		font-weight: bold;
		border-top: 2px solid gray;
		border-bottom: 2px solid gray;
	}

	#spCharTable td.charsetHeading:hover {
		background-color: #999;
		color: #000;
		cursor:default;
	}

	#spCharTable td.emptyCell:hover {
		background-color: #FFF;
		color: #000;
		cursor:default;
	}

	.links {
		float : left;
		padding : 0 10px 0 0;
	}
	</style>
        <script src="<?php echo WIKINDX_BASE_URL . "/" . WIKINDX_URL_COMPONENT_VENDOR; ?>/jquery/jquery.min.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>
	<script language="JavaScript" type="text/javascript">
        // SET CURSOR POSITION
        $.fn.setCursorPosition = function(pos) {
                this.each(function(index, elem) {
                if (elem.setSelectionRange) {
                        elem.setSelectionRange(pos, pos);
                } else if (elem.createTextRange) {
                        var range = elem.createTextRange();
                        range.collapse(true);
                        range.moveEnd('character', pos);
                        range.moveStart('character', pos);
                        range.select();
                }
                });
                return this;
        };

	function selectSpChar(idChar)
	{
	        // Extract the visual version of the character
	        var htmlChar = parent.main.document.getElementById("ul" + idChar).innerHTML;

	        // Get an handle on the textearea and the position of the cursor
                var t = $("#textBox", parent.nav.document);
                var cursorPos = t.prop('selectionStart');

                // Insert the character in the textarea
                var v = t.val();
                var textBefore = v.substring(0,  cursorPos);
                var textAfter  = v.substring(cursorPos, v.length);
                t.val(textBefore + htmlChar + textAfter);

                // Put the cursor after the character we have inserted
                t.setCursorPosition(cursorPos + 1);
	}
	</script>
</head>
<body>
<table id="spCharTable">
<?php


// Create unicode tables.  Which characters are available is modelled on what is available in my OO.org 2 beta....
function bodyCharsetTable($charsetArray, $name, $title, $diacritic)
{
    $numColumns = 20;

    $lString = "";
    $lString .= "<tr>\n\t<td class=\"charsetHeading\" colspan=\"$numColumns\"><a name=\"$name\">$title";
    if ($diacritic) {
        $lString .= " <span style=\"color:red;\">*</span>";
    }
    $lString .= "</td>\n</tr>\n";

    $charPreview = $diacritic ? 'o' : '';

    foreach (array_chunk($charsetArray, $numColumns) as $lineArray) {
        $lString .= "<tr>\n";

        foreach ($lineArray as $char) {
            $lString .= "\t<td id=\"ul$char\" onclick=\"selectSpChar($char);\">&#$char;$charPreview</td>\n";
        }

        $index = count($lineArray);
        if ($index < $numColumns) {
            $lString .= "\t<td class=\"emptyCell\" colspan=\"" . ($numColumns - $index) . "\">&nbsp;</td>\n";
        }

        $lString .= "</tr>\n";
    }

    return $lString;
}

include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "sp_charTableDef.php"]));

if (isset($_GET['ul'])) {
    $ul = $_GET['ul'];
    $ul = intval(str_replace('ul', '', $ul));
} else {
    $ul = 0;
}

$fcp = $tableChars[$ul]['fcp'];
$lcp = $tableChars[$ul]['lcp'];
$id = $tableChars[$ul]['id'];
$diac = $tableChars[$ul]['diac'];

$charsetArray = [];
for ($char = base_convert($fcp, 16, 10); $char <= base_convert($lcp, 16, 10); $char++) {
    $charsetArray[] = $char;
}

echo bodyCharsetTable($charsetArray, 'ul' . $ul, $id, $diac);


?>
</tr>
</table>
</body>
</html>
