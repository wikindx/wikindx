tinyMCEPopup.requireLangPack();

// function to add image by linking to URL
function imageDialogUrl() 
{
	var imagePath = getImagePath('imagePath');	
	if(imagePath)
	{
// Image must be a URL on a valid web server.  If only a path is given, prepend http://localhost
		if(imagePath.match(/^http/) == false)
		{
			if(imagePath.match(/^\//) == true)
				imagePath = location.protocol + '//localhost' + imagePath;
			else
				imagePath = location.protocol + '//localhost/' + imagePath;
		}
		var html = "<img src=\"" + imagePath + "\" />";
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
	}
	tinyMCEPopup.close();
}

// function to add image from selected file in image/ folder -- imagePath var name needs changing to match the selected file
function imageDialogBrowse(path, width, height) 
{
	if(width && height)
		var html = "<img src=\"" + path + "\" width=\"" + width + "\" height=\"" + height + "\">";
    else if(width)
        var html = "<img src=\"" + path + "\" width=\"" + width + "\">";
    else if(height)
        var html = "<img src=\"" + path + "\" height=\"" + height + "\">";
    else
		var html = "<img src=\"" + path + "\">";
	tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
	tinyMCEPopup.close();
}

function getImagePath(path)
{
	var imagePath = document.getElementById(path).value;
	if(!imagePath)
	{
		alert('Invalid input');
		return false;
	}
	return imagePath;
}
