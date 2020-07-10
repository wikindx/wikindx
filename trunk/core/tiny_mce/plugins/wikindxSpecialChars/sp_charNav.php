<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

if(!function_exists("SetWikindxBasePath")) {
    function SetWikindxBasePath()
    {
        $wikindxBasePath = __DIR__;
        while (!in_array(basename($wikindxBasePath), ["", "components", "core"])) {
            $wikindxBasePath = dirname($wikindxBasePath);
        }
        if (basename($wikindxBasePath) == "") {
            die("
                \$WIKINDX_WIKINDX_PATH in config.php is set incorrectly
                and WIKINDX is unable to set the installation path automatically.
                You should set \$WIKINDX_WIKINDX_PATH in config.php.
            ");
        }
        chdir(dirname($wikindxBasePath));
    }
}

SetWikindxBasePath();

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

	<script src="<?php echo WIKINDX_BASE_URL; ?>/core/tiny_mce/tiny_mce_popup.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>
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

foreach ($tableChars as $anchorNumber => $listDef) {
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
