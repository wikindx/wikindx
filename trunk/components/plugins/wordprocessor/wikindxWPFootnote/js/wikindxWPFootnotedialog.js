tinyMCEPopup.requireLangPack();

function footnoteDialog() 
{
	// Insert the contents from the input into the document
	var footnoteOutput = getFootnoteString();
	if(!footnoteOutput)
		return false;
	tinyMCEPopup.editor.execCommand('mceInsertContent', false, footnoteOutput);
	tinyMCEPopup.close();
}

function getFootnoteString()
{
	var text = document.getElementById('footnote').value;
	text = text.replace(/\s{2}/g, " &nbsp;");
	if(!text)
	{
		alert('Missing input');
		return false;
	}
	var footnote = '[footnote]' + text + '[/footnote]';
	return footnote;
}
