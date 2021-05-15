tinyMCEPopup.requireLangPack();

// Bring hidden field 'hdnpaperText' in sync with 'paperText' iFrame and generate the HTML code.
// To be used from the EXPORT pop up where the rte is in the parent/opener window
function wordprocessorExport(savedMessage, notSavedMessage)
{
//set hidden form field value for current editor
	var oHdnField = document.getElementById('hdnpaperText');
	oHdnField.value = tinyMCE.get('paperText').getContent({format : 'text'})
	if (oHdnField.value == null)
		oHdnField.value = "";
//if there is no content (other than formatting) set value to nothing
	if (stripHTML(oHdnField.value.replace("&nbsp;", " ")) == "" &&
		oHdnField.value.toLowerCase().search("<hr") == -1 &&
		oHdnField.value.toLowerCase().search("<img") == -1)
	{
		oHdnField.value = "";
	}
	var title = document.getElementById('title');
	parent.window.opener.paperExported(savedMessage, notSavedMessage, title.value);
// Clear export file hyperlink field in parent window
//	var fileCell = window.opener.document.getElementById("exportFile");
//	fileCell.innerHTML = '';
}
