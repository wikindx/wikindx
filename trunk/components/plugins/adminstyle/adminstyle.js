/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
**********************************************************************************/

/**
* adminstyle.js
*/

var templatePrefix = 'style_';
var templateSuffix = '';


/**
* Preview an in-text citation by calling previewcite.php with parameters
* 
* @Author Mark Grimshaw-Aagaard with a lot of help from Christian Boulanger
*/
function previewCite()
{
	templatePrefix = 'cite_';
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['targetDiv', 'script']))
		return false;
// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
	ajaxReturn = "&cite=" + coreEncode_base64(citeConfig2json());
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	else
		return true;
}

/**
* Preview a bibliography or a footnote by calling previewstyle.php with parameters
* 
* @Author Mark Grimshaw-Aagaard with a lot of help from Christian Boulanger
*/
function previewBibliographyOrFootnote(templateName, footnote = false)
{
	if(footnote && coreGetElementById('footnote_' + templateName + 'Template').value)
	{
		templatePrefix = 'footnote_';
		templateSuffix = 'Template';
	}
	else
	{
		templatePrefix = 'style_';
		templateSuffix = '';
	}
	// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['targetDiv', 'triggerField', 'script']))
		return false;
	// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
	// Gather the selected OPTIONS of the triggerField select box
	var triggerObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.triggerField);
	var len = triggerObj.options.length;
	var ajaxReturn = '';
	var triggerOptionValues = new Array();
	for(var i = 0; i < len; i++)
	{
		if(!triggerObj.options[i].selected)
			triggerOptionValues.push(triggerObj.options[i].value);
	}
	if(triggerOptionValues.length)
	ajaxReturn = '&ajaxReturn=' + coreEncode_base64(triggerOptionValues.join());
	
	var url = "";
	url += "&style="          + coreEncode_base64(bibstyleConfig2json(templateName));
	url += "&rewriteCreator=" + coreEncode_base64(rewriteCreator2json(templateName));
	
	var templateString = coreGetElementById(templatePrefix + templateName + templateSuffix).value; 
	if(!templateString)
	{
		templateName = coreGetElementById('style_' + templateName + '_generic').value;
		var fallbackString = coreGetElementById(templatePrefix + templateName + templateSuffix).value;
		fallbackString = fallbackString
			.replace(/</g, '__WIKINDX__LESSTHAN__')
			.replace(/>/g, '__WIKINDX__GREATERTHAN__');
	}
	else
	{	
		templateString = templateString
			.replace(/</g, '__WIKINDX__LESSTHAN__')
			.replace(/>/g, '__WIKINDX__GREATERTHAN__');
		var fallbackString = '';
	}
	ajaxReturn += url;
	ajaxReturn += "&templateName="   + coreEncode_base64(encode_utf8(templateName));
	ajaxReturn += "&templateString=" + coreEncode_base64(encode_utf8(templateString));
	ajaxReturn += "&fallbackString=" + coreEncode_base64(encode_utf8(fallbackString));
	
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
	// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	else
		return true;
}


/*********************************************************************************/

/**
* Format a json object of all options for rewriting creators
* taken from the current state of the adminStyle form
* 
* @Author Mark Grimshaw-Aagaard with a lot of help from Christian Boulanger
*/
function rewriteCreator2json(templateName)
{
	var creatorArray = new Array();
	var currFormField;
	var str;
	var fieldName;
	var arrayKey;
	templateName = 'style_' + templateName;
	
	// Add rewrite creator strings for the resource type
	var creators = new Array("creator1", "creator2", "creator3", "creator4", "creator5");
	for (index = 0; index < creators.length; index++)
	{
		arrayKey = creators[index] + "_firstString";
		fieldName = templateName + "_" + creators[index] + "_firstString";
		currFormField = document.forms[0][fieldName];
		if(typeof(currFormField) != 'undefined')
			creatorArray[arrayKey] = currFormField.value; // input and textarea
		
		arrayKey = creators[index] + "_firstString_before";
		fieldName = templateName + "_" + creators[index] + "_firstString_before";
		currFormField = document.forms[0][fieldName];
		if ((typeof(currFormField) != 'undefined') && currFormField.checked) // checkbox
			creatorArray[arrayKey] = "on";
		
		arrayKey = creators[index] + "_remainderString";
		fieldName = templateName + "_" + creators[index] + "_remainderString";
		currFormField = document.forms[0][fieldName];
		if(typeof(currFormField) != 'undefined')
			creatorArray[arrayKey] = currFormField.value; // input and textarea
		
		arrayKey = creators[index] + "_remainderString_before";
		fieldName = templateName + "_" + creators[index] + "_remainderString_before";
		currFormField = document.forms[0][fieldName];
		if ((typeof(currFormField) != 'undefined') && currFormField.checked) // checkbox
			creatorArray[arrayKey] = "on";
		
		arrayKey = creators[index] + "_remainderString_each";
		fieldName = templateName + "_" + creators[index] + "_remainderString_each";
		currFormField = document.forms[0][fieldName];
		if ((typeof(currFormField) != 'undefined') && currFormField.checked) // checkbox
			creatorArray[arrayKey] = "on";
	}
	
	// style definition array
	var creatorArray2 = {};
	for (var key in creatorArray)
	{
		str = creatorArray[key]
			.replace(/ /g, '__WIKINDX__SPACE__')
			.replace(/</g, '__WIKINDX__LESSTHAN__')
			.replace(/>/g, '__WIKINDX__GREATERTHAN__');
		creatorArray2[encode_utf8(key)] = encode_utf8(str);
	}
	
	return JSON.stringify(creatorArray2);
}

/**
* Format a json object of all options for styling a bibliography or a footnote
* taken from the current state of the adminStyle form
* 
* @Author Mark Grimshaw-Aagaard with a lot of help from Christian Boulanger
*/
function bibstyleConfig2json(templateName)
{
	var fieldArray = new Array (
		"style_dateFormat", 
		"style_dateMonthNoDay",
		"style_dateMonthNoDayString",
		"style_dateRangeDelimit1",
		"style_dateRangeDelimit2",
		"style_dateRangeSameMonth", 
		"style_dayFormat", 
		"style_dayLeadingZero",
		"style_editionFormat",
		"style_editorSwitch",
		"style_editorSwitchIfYes",
		"style_localisation", 
		"style_monthFormat",
		"style_otherCreatorFirstName",
		"style_otherCreatorFirstStyle", 
		"style_otherCreatorInitials",
		"style_otherCreatorList",
		"style_otherCreatorListAbbreviation",
		"style_otherCreatorListAbbreviationItalic", 
		"style_otherCreatorListLimit",
		"style_otherCreatorListMore", 
		"style_otherCreatorOtherStyle",
		"style_otherCreatorSepFirstBetween",
		"style_otherCreatorSepNextBetween", 
		"style_otherCreatorSepNextLast",
		"style_otherCreatorUppercase",
		"style_otherTwoCreatorsSep", 
		"style_pageFormat", 
		"style_primaryCreatorFirstName",
		"style_primaryCreatorFirstStyle", 
		"style_primaryCreatorInitials", 
		"style_primaryCreatorList",
		"style_primaryCreatorListAbbreviation",
		"style_primaryCreatorListAbbreviationItalic",
		"style_primaryCreatorListLimit",
		"style_primaryCreatorListMore", 
		"style_primaryCreatorOtherStyle",
		"style_primaryCreatorRepeat", 
		"style_primaryCreatorRepeatString",
		"style_primaryCreatorSepFirstBetween", 
		"style_primaryCreatorSepNextBetween",
		"style_primaryCreatorSepNextLast", 
		"style_primaryCreatorUppercase", 
		"style_primaryTwoCreatorsSep",
		"style_runningTimeFormat",
		"style_titleCapitalization",
		"style_titleSubtitleSeparator",
		"style_userMonth_1",
		"style_userMonth_10",
		"style_userMonth_11",
		"style_userMonth_12", 
		"style_userMonth_13",
		"style_userMonth_14",
		"style_userMonth_15",
		"style_userMonth_16", 
		"style_userMonth_2",
		"style_userMonth_3",
		"style_userMonth_4", 
		"style_userMonth_5",
		"style_userMonth_6",
		"style_userMonth_7",
		"style_userMonth_8", 
		"style_userMonth_9"
	);
		
	var currFormField;
	var str;
	var styleArray = new Array ();
	for (index = 0; index < fieldArray.length; index++)
	{
		currFormField = document.forms[0][fieldArray[index]];
		if ((currFormField.type == "checkbox") && currFormField.checked)
			styleArray[fieldArray[index]] = "on"; // checkbox
		else if (currFormField.type != "checkbox")
			styleArray[fieldArray[index]] = currFormField.value; // input and textarea
	}
	
	// style definition array
	var styleArray2 = {};
	for (var key in styleArray)
	{
		str = styleArray[key]
			.replace(/ /g, '__WIKINDX__SPACE__')
			.replace(/</g, '__WIKINDX__LESSTHAN__')
			.replace(/>/g, '__WIKINDX__GREATERTHAN__');
		styleArray2[encode_utf8(key)] = encode_utf8(str);
	}
	
	return JSON.stringify(styleArray2);
}

/**
* Format a json object of all options for styling an in-text citation
* taken from the current state of the adminStyle form
* 
* @Author Mark Grimshaw-Aagaard with a lot of help from Christian Boulanger
*/
function citeConfig2json()
{
	var fieldArray = new Array (
		"cite_ambiguous",
		"cite_ambiguousTemplate",
		"cite_consecutiveCitationSep",
		"cite_consecutiveCreatorSep",
		"cite_consecutiveCreatorTemplate",
		"cite_creatorFirstName",
		"cite_creatorInitials",
		"cite_creatorList",
		"cite_creatorListAbbreviation",
		"cite_creatorListAbbreviationItalic",
		"cite_creatorListLimit",
		"cite_creatorListMore",
		"cite_creatorListSubsequent",
		"cite_creatorListSubsequentAbbreviation",
		"cite_creatorListSubsequentLimit",
		"cite_creatorListSubsequentMore",
		"cite_creatorOtherStyle",
		"cite_creatorSepFirstBetween",
		"cite_creatorSepNextBetween",
		"cite_creatorSepNextLast",
		"cite_creatorStyle",
		"cite_creatorUppercase",
		"cite_firstChars",
		"cite_followCreatorPageSplit",
		"cite_followCreatorTemplate",
		"cite_lastChars",
		"cite_pageFormat",
		"cite_removeTitle",
		"cite_replaceYear",
		"cite_subsequentCreatorRange",
		"cite_subsequentCreatorTemplate",
		"cite_subsequentFields",
		"cite_template",
		"cite_titleCapitalization",
		"cite_titleSubtitleSeparator",
		"cite_twoCreatorsSep",
		"cite_useInitials",
		"cite_yearFormat"
	);
	
	var currFormField;
	var str;
	var citeArray = new Array ();
	for (index = 0; index < fieldArray.length; index++)
	{
		currFormField = document.forms[0][fieldArray[index]];
		if ((currFormField.type == "checkbox") && currFormField.checked)
		citeArray[fieldArray[index]] = "on"; // checkbox
		else if (currFormField.type != "checkbox")
		citeArray[fieldArray[index]] = currFormField.value; // input and textarea
	}
	
	// style definition array
	var citeArray2 = {};
	for (var key in citeArray)
	{
		str = citeArray[key]
			.replace(/ /g, '__WIKINDX__SPACE__')
			.replace(/</g, '__WIKINDX__LESSTHAN__')
			.replace(/>/g, '__WIKINDX__GREATERTHAN__');
		citeArray2[encode_utf8(key)] = encode_utf8(str);
	}
	
	return JSON.stringify(citeArray2);
}


/*********************************************************************************/

function transferField(textArea, select)
{
	selectToTextarea(textArea, select);
}

/**
* Transfer elements from source select box to target textarea if selected element's text does not exist in target area
*
* @param string Target object
* @param string Source object
*/
function selectToTextarea(target, source)
{
	var selection;
	var targetObj = coreGetElementById(target);
	var sourceObj = coreGetElementById(source);
	len = sourceObj.options.length;
	for(i = 0; i < len; i++)
	{
		selection = coreEntityDecode(coreUtf8.decode(coreDecode_base64(sourceObj.options[i].value)));
		if(sourceObj.options[i].selected)
			insertTextAtCaret(targetObj, selection);
	}
}

function getSelectionBoundary(el, start)
{
	var property = start ? "selectionStart" : "selectionEnd";
	var originalValue, textInputRange, precedingRange, pos, bookmark;
	
	if (typeof el[property] == "number")
	{
		return el[property];
	}
	else if (document.selection && document.selection.createRange)
	{
		el.focus();
		
		var range = document.selection.createRange();
		if (range)
		{
			// Collapse the selected range if the selection is not a caret
			if (document.selection.type == "Text")
			{
				range.collapse(!!start);
			}
			
			originalValue = el.value;
			if(typeof(el.createTextRange) === 'function')
			{
				textInputRange = el.createTextRange();
				precedingRange = el.createTextRange();
			}
			pos = 0;
			
			bookmark = range.getBookmark();
			textInputRange.moveToBookmark(bookmark);
			
			if (originalValue.indexOf("\r\n") > -1)
			{
				// Trickier case where input value contains line breaks
				// Insert a character in the text input range and use that
				// as a marker
				textInputRange.text = " ";
				precedingRange.setEndPoint("EndToStart", textInputRange);
				pos = precedingRange.text.length - 1;
				
				// Executing an undo command deletes the character inserted
				// and prevents adding to the undo stack.
				document.execCommand("undo");
			}
			else
			{
				// Easier case where input value contains no line breaks
				precedingRange.setEndPoint("EndToStart", textInputRange);
				pos = precedingRange.text.length;
			}
			return pos;
		}
	}
	return 0;
}

function offsetToRangeCharacterMove(el, offset)
{
	return offset - (el.value.slice(0, offset).split("\r\n").length - 1);
}

function setSelection(el, startOffset, endOffset)
{
	if(typeof(el.createTextRange) === 'function')
		var range = el.createTextRange();
	else
		var range = el.createRange();
	
	var startCharMove = offsetToRangeCharacterMove(el, startOffset);
	range.collapse(true);
	if (startOffset == endOffset)
	{
		range.move("character", startCharMove);
	}
	else
	{
		range.moveEnd("character", offsetToRangeCharacterMove(el, endOffset));
		range.moveStart("character", startCharMove);
	}
	range.select();
}

function insertTextAtCaret(el, text)
{
	var pos = getSelectionBoundary(el, false);
	var newPos = pos + text.length;
	var val = el.value;
	el.value = val.slice(0, pos) + text + val.slice(pos);
	setSelection(el, newPos, newPos);
}

//------------------------------------------------------

// cf. https://stackoverflow.com/questions/13356493/decode-utf-8-with-javascript
// cf. https://developer.mozilla.org/en-US/docs/Web/API/WindowOrWorkerGlobalScope/btoa#Unicode_strings
function encode_utf8(s) {
	return unescape(encodeURIComponent(s));
}

function decode_utf8(s) {
	return decodeURIComponent(escape(s));
}
