tinyMCEPopup.requireLangPack();

function tableDialog() 
{
	// Insert the contents from the input into the document
	var tableOutput = getTableData();
	if(!tableOutput)
		return false;
	tinyMCEPopup.editor.execCommand('mceInsertContent', false, tableOutput);
	tinyMCEPopup.close();
}

function getTableData()
{
	var cols = document.getElementById('columns').value;
	var rows = document.getElementById('rows').value;
//	if((cols < 1) || (rows < 1))
	if(!isInteger(cols) || !isInteger(rows) || (cols < 1) || (rows < 1))
	{
		alert('Invalid input: ' + cols + ' ' + rows);
		return false;
	}
	var html = '<table border="1" width="100%">\n';
	for (var r = 0; r < rows; r++)
	{
		html += "<tr>\n";
		for (c = 0; c < cols; c++)
			html += "<td>&nbsp;</td>\n";
		html+= "</tr>\n";
	}
// Add line break so that it's possible to continue adding text following the table.
	html += "</table><br>\n";
	return html;
}
function isInteger(n)
{
    return (/^-?\d+$/.test(n+''));
}
