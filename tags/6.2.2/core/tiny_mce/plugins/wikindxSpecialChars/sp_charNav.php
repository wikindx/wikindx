<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

session_start();
if (isset($_SESSION) && array_key_exists('wikindxBasePath', $_SESSION) && $_SESSION['wikindxBasePath'])
{
    chdir($_SESSION['wikindxBasePath']); // tinyMCE changes the phpbasepath
}
else
{
    $oldPath = dirname(__FILE__);
    $split = preg_split('/' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/u', $oldPath);
    array_splice($split, -4); // get back to trunk
    $newPath = implode(DIRECTORY_SEPARATOR, $split);
    chdir($newPath);
}

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Special Characters</title>

	<style type="text/css">
	.textTable {
		text-align: left;
		border: 1px dotted red;
		padding : 1px;
		font-size : 18px;
	}
	</style>

	<script src="<?php echo FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL; ?>/core/tiny_mce/tiny_mce_popup.js"></script>
	<script>
	tinyMCEPopup.requireLangPack();

	function goto_URL(object)
	{
		if(object.options[object.selectedIndex].value)
			parent.main.location.href = object.options[object.selectedIndex].value;
	}

	function insertAndClose()
	{
		var spChar = parent.nav.document.getElementById("textBox").value
		if(spChar != '') tinyMCEPopup.execCommand('mceInsertContent', false, spChar);

		// Refocus in window and close
		if (tinyMCEPopup.isWindow) {
			window.focus();
			window.parent.close();
		}
	}

	function clearInsert()
	{
		parent.nav.document.getElementById("textBox").value = '';
	}
	</script>
</head>
<body>
<form onsubmit="return false;">
<p>
	<select name="selectName" onchange="goto_URL(this.form.selectName)">
<?php

include_once('sp_charTableDef.php');

?><option value="sp_char.php?ul=0">Select a character set:</option><?php

foreach ($tableChars as $anchorNumber => $listDef)
{
    ?><option value="sp_char.php?ul=<?php echo('ul' . $anchorNumber); ?>"><?php echo($listDef['id']); ?></option><?php
}

?>
	</select>
</p>
<p><textarea cols="33" rows="3" name="textBox" id="textBox" vwrap></textarea></p>
<p>
	<input type="button" value="Insert" name="insert" id="insert" title="Insert" onClick="insertAndClose()">
	<input type="button" class = "button" value="Clear" name="clear" title="Clear" onClick="clearInsert()">
</p>

<p>
	<span style="color:red;">*</span> Combining diacritical marks are a special case, because they can not be represented alone.
	The letter "o", accompanying them, represents any character.
	Click on a character followed by a combining diacritic to accentuate it.
</p>

<p>
	<span style="color:red;">**</span> Some unicode points are unused or the current font have no glyph to display them. In that case they are replaced by the following symbol: &#1114111;. This could change in the future if the Unicode support of your system is better.
</p>
<p>
    <a href="https://www.unicode.org/charts/beta/nameslist/" target="_blank" title="Unicode Code Charts">UCD: 11.0.0</a> (2018-06-05)
</p>
</form>
</body>
</html>