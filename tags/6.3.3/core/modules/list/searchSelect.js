/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
**********************************************************************************/

/**
Initialize AJAX
*/
var Element_index = 1;
var elementIndexInitialized = false;

var type = 'select_';
if(coreGetElementById('search_Type') !== null)
	type = 'search_';
/**
* sets inner HTML of a DIV and restores the selected options in the select box
*/
function setDiv()
{
	var targetDivObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
	var targetOptionValues = new Array();
	var targetBoxObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetBox);
// store selected options for later restoration
	if(targetBoxObj)
	{
		var len = targetBoxObj.options.length;
		for(var i = 0; i < len; i++)
		{
			if(targetBoxObj.options[i].selected && (targetBoxObj.options[i].value != 0)) // don't include 'IGNORE'
				targetOptionValues.push(targetBoxObj.options[i].value);
		}
	}
	targetDivObj.innerHTML = A_OBJ[gateway.aobj_index].input.targetContent;
// restore selected options
	targetBoxObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetBox);
	if(targetBoxObj && targetOptionValues.length)
	{
		len = targetBoxObj.length
		var found = false;
		for(var i = 0; i < len; i++)
		{
			for(var index in targetOptionValues)
			{
				if(targetOptionValues[index] == targetBoxObj.options[i].value)
				{
					targetBoxObj.options[i].selected = true;
					found = true;
				}
			}
		}
	}
	if(found) // deselect IGNORE
		targetBoxObj.options[0].selected = false;
}
/**
* sets DIV as visible or not
*/
function divVisibility()
{
	var targetDivObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
//alert(A_OBJ[gateway.aobj_index].input.targetDiv + ': ' + A_OBJ[gateway.aobj_index].input.targetContent);
	if(A_OBJ[gateway.aobj_index].input.targetState == 'visible')
		targetDivObj.style.display = 'block';
	else
		targetDivObj.style.display = 'none';
//alert(A_OBJ[gateway.aobj_index].input.targetDiv + ': ' + targetDivObj.style.display);
}
/**
* Transfer an option from the main keywords selectbox to the selected keywords selectbox
*/
function selectKeyword()
{
	var target = type + 'Keyword';
	var source = type + 'AvailableKeyword';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the selected keywords selectbox to the main keywords selectbox
*/
function discardKeyword()
{
	var target = type + 'AvailableKeyword';
	var source = type + 'Keyword';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the main usertags selectbox to the selected usertags selectbox
*/
function selectUserTag()
{
	var target = type + 'UserTag';
	var source = type + 'AvailableUserTag';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the selected usertags selectbox to the main usertags selectbox
*/
function discardUserTag()
{
	var target = type + 'AvailableUserTag';
	var source = type + 'UserTag';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the main categories selectbox to the selected categories selectbox
*/
function selectCategory()
{
	var target = type + 'Category';
	var source = type + 'AvailableCategory';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the selected categories selectbox to the main categories selectbox
*/
function discardCategory()
{
	var target = type + 'AvailableCategory';
	var source = type + 'Category';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the main subcategories selectbox to the selected subcategories selectbox
*/
function selectSubcategory()
{
	var target = type + 'Subcategory';
	var source = type + 'AvailableSubcategory';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the selected subcategories selectbox to the main subcategories selectbox
*/
function discardSubcategory()
{
	var target = type + 'AvailableSubcategory';
	var source = type + 'Subcategory';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the main collections selectbox to the selected collections selectbox
*/
function selectCollection()
{
	var target = type + 'Collection';
	var source = type + 'AvailableCollection';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the selected collections selectbox to the main collections selectbox
*/
function discardCollection()
{
	var target = type + 'AvailableCollection';
	var source = type + 'Collection';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the main creator selectbox to the selected creators selectbox
*/
function selectCreator()
{
	var target = type + 'Creator';
	var source = type + 'AvailableCreator';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the selected creators selectbox to the main creators selectbox
*/
function discardCreator()
{
	var target = type + 'AvailableCreator';
	var source = type + 'Creator';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the main publishers selectbox to the selected publishers selectbox
*/
function selectPublisher()
{
	var target = type + 'Publisher';
	var source = type + 'AvailablePublisher';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the selected publishers selectbox to the main publishers selectbox
*/
function discardPublisher()
{
	var target = type + 'AvailablePublisher';
	var source = type + 'Publisher';
	coreSelectToSelect(target, source);
}
/**
* On submit, select all options in target select boxes -- this allows PHP to pick up those options
*/
function selectAll()
{
	for(i = 1; i <= 50; i++)
	{
		var element = 'advancedSearch_Select_' + i;
		var obj = coreGetElementById(element);
		if(obj == null)
			continue;
		if(obj.name.match(/^\/exclude\/1/))
			continue;
		for(index = obj.options.length - 1; index >= 0; index--)
			obj.options[index].selected = true;
	}
}
/*function selectAll()
{
	selectAllProcess('Keyword');
	selectAllProcess('UserTag');
	selectAllProcess('Category');
	selectAllProcess('Subcategory');
}
*/
/**
* On submit, select all options in select boxes -- this allows PHP to pick up those options.  Extra options for select resources
*/
function selectAllSelect()
{
	selectAllProcess('Collection');
	selectAllProcess('Publisher');
	selectAllProcess('Creator');
}
/**
* Select selected options
*/
function selectAllProcess(box)
{
	var element = type + box;
	var obj = coreGetElementById(element);
	if(obj == null)
		return;
	for(i = obj.options.length - 1; i >= 0; i--)
		obj.options[i].selected = true;
}
/**
* Add a search form element
*/
function addElement()
{
	if(Element_index == 50)
	{
		alert('Exceeded allowed number of search fields');
		return false;
	}
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['script', 'elementIndex', 'updateJSElementIndex']))
		return false;
	if(A_OBJ[gateway.aobj_index].input.updateJSElementIndex == 1)
		Element_index = A_OBJ[gateway.aobj_index].input.elementIndex;
	var jObj = new Object;
	createJObj(jObj); // NB objects passed by reference
// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById('searchElement_' + Element_index);
//alert(A_OBJ[gateway.aobj_index].targetObj + ': ' + A_OBJ[gateway.aobj_index].input.elementIndex);
	var ajaxReturn = '&ajaxReturn=' + JSON.stringify(jObj);
//alert(JSON.stringify(jObj));
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	return true;
}
/**
* Remove a search form element
*/
function removeElement()
{
//alert(A_OBJ[gateway.aobj_index].input.elementIndex);
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['script', 'elementIndex']))
		return false;
	var jObj = new Object;
	jObj.elementIndex = A_OBJ[gateway.aobj_index].input.elementIndex;
// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById('searchElement_' + jObj.elementIndex);
//alert(A_OBJ[gateway.aobj_index].targetObj + ': ' + A_OBJ[gateway.aobj_index].input.elementIndex);
	var ajaxReturn = '&ajaxReturn=' + JSON.stringify(jObj);
//alert(JSON.stringify(jObj));
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	return true;
}
/**
* Adjust the display options depending on what attachment options are selected
*/
function attachmentOptions()
{
	if((fieldObj = coreGetElementById('advancedSearch_Options')) == null)
		return false;
	for(i = fieldObj.options.length - 1; i > 0; i--)
	{
		if(fieldObj.options[i].selected == true)
		{
			fieldObj.options[0].selected = false; // 'ignore'
			break;
		}
	}
	if(fieldObj.options[1].selected == true) // 'noAttachment'
	{
		fieldObj.options[2].selected = false; // 'withAttachment'
		fieldObj.options[3].selected = false; // 'displayOnlyAttachment'
		fieldObj.options[4].selected = false; // 'zipAttachment'
	}
	if(fieldObj.options[4].selected == true) // 'zipAttachment'
		fieldObj.options[3].selected = true; // 'displayOnlyAttachment'
	if(fieldObj.options[3].selected == true) // 'displayOnlyAttachment'
		fieldObj.options[2].selected = true; // 'withAttachment'
	return true;
}
/**
* Add a new text input for a second value for comparison
*/
function addComparisonValue()
{
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['script', 'elementIndex', 'targetDiv']))
		return false;
	var jObj = new Object;
	jObj.elementIndex = A_OBJ[gateway.aobj_index].input.elementIndex;
	if((fieldObj = coreGetElementById('advancedSearch_Comparison_' + jObj.elementIndex)) == null)
		return false;
	if(fieldObj.value == 6) // '<...<'
		jObj.execute = true;
	else
		jObj.execute = false;
// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
//alert(A_OBJ[gateway.aobj_index].targetObj + ': ' + A_OBJ[gateway.aobj_index].input.targetDiv);
	var ajaxReturn = '&ajaxReturn=' + encodeURIComponent(JSON.stringify(jObj));
//alert(JSON.stringify(jObj));
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	return true;
}
/**
* Do something AJAXy in the 'targetDiv' based upon input from a single select box form element
*
* @return boolean
*/
function triggerSearchParameter()
{
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['triggerField', 'targetDiv', 'script']))
		return false;
	var jObj = new Object;
	jObj.elementIndex = A_OBJ[gateway.aobj_index].input.elementIndex;
// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
// Gather the selected OPTIONS of the triggerField select box
	var triggerObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.triggerField);
	len = triggerObj.options.length;
	for(i = 0; i < len; i++)
	{
		if(triggerObj.options[i].selected)
		{
			split = triggerObj.options[i].value.split('_');
			jObj.field = coreEntityDecode(triggerObj.options[i].value);
			break;
		}
	}
	var ajaxReturn = '&ajaxReturn=' + encodeURIComponent(JSON.stringify(jObj));
//alert(JSON.stringify(jObj));
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	return true;
}
/**
* Transfer an option from the main categories selectbox to the selected categories selectbox
*/
function search_Transfer()
{
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['source', 'target']))
		return false;
	coreSelectToSelect(A_OBJ[gateway.aobj_index].input.target, A_OBJ[gateway.aobj_index].input.source);
}
/**
* Return object of search input for input to the test/natural language div
*/
function test()
{
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['targetDiv', 'script']))
		return false;
	selectAll();
	var jObj = new Object;
// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
	var fieldName;
	jObj.elementFields = new Object;
// get search elements
	for(index = 1; index < 51; index++)
	{
		fieldName = 'advancedSearch_Field_' + index;
		getObjValue(fieldName, jObj);
		fieldName = 'advancedSearch_Word_' + index;
		getObjValue(fieldName, jObj);
		fieldName = 'advancedSearch_Partial_' + index;
		getObjValue(fieldName, jObj, 'checkbox');
		fieldName = 'advancedSearch_Button1_' + index;
		getObjValue(fieldName, jObj, 'radio');
		fieldName = 'advancedSearch_Button2_' + index;
		getObjValue(fieldName, jObj, 'radio');
		fieldName = 'advancedSearch_Select_' + index;
		getObjValue(fieldName, jObj, 'select');
		fieldName = 'advancedSearch_Comparison_' + index;
		getObjValue(fieldName, jObj);
		fieldName = 'advancedSearch_Value1_' + index;
		getObjValue(fieldName, jObj);
		fieldName = 'advancedSearch_Value2_' + index;
		getObjValue(fieldName, jObj);
	}
// get bibliography and options
	fieldName = 'advancedSearch_BibId';
	getObjValue(fieldName, jObj, 'selectChecked');
	fieldName = 'advancedSearch_Options';
	getObjValue(fieldName, jObj, 'selectChecked');
	var ajaxReturn = '&ajaxReturn=' + encodeURIComponent(JSON.stringify(jObj));
//alert(JSON.stringify(jObj));
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	return true;
}
/**
* Populate the jObj object that stores existing search fields and the field index counter.
* NB jObj is an object and so is passed in by reference
*/
function createJObj(jObj)
{
	jObj.elementIndex = ++Element_index;
	var fieldName;
	jObj.elementFields = new Object;
	fieldName = 'advancedSearch_Field_' + Element_index;
	getObjValue(fieldName, jObj);
	fieldName = 'advancedSearch_Word_' + Element_index;
	getObjValue(fieldName, jObj);
}
/**
* return an object's value
*/
function getObjValue(fieldName, jObj, type)
{
	if((fieldObj = coreGetElementById(fieldName)) != null)
	{
		var value;
		var i;
		if(type == undefined) // textbox or single selectbox
		{
			if(value = coreTrim(fieldObj.value))
			{
				value = value.replace(/['"]+/g, '\\"');
				jObj.elementFields[fieldName] = value;
			}
		}
		else if(type == 'checkbox')
		{
			if(fieldObj.checked == 1)
				jObj.elementFields[fieldName] = 1;
		}
		else if(type == 'radio')
		{
			fieldObj = coreGetElementByName(fieldName);
			for(i = 0, length = fieldObj.length; i < length; i++)
			{
				if(fieldObj[i].checked)
				{
					jObj.elementFields[fieldName] = fieldObj[i].value;
					break;
				}
			}
		}
		else if(type == 'select') // multiple select box -- all options
		{
			var array = new Array();
			var string = '';
			for(i = fieldObj.options.length - 1; i >= 0; i--)
				array[i] = fieldObj[i].value;
			string = array.join();
			jObj.elementFields[fieldName] = string;
		}
		else if(type == 'selectChecked') // multiple select box -- checked options only
		{
			var array = new Array();
			var string = '';
			for(i = fieldObj.options.length - 1; i >= 0; i--)
			{
				if(fieldObj.options[i].selected)
					array[i] = fieldObj[i].value;
			}
			string = array.join();
			jObj.elementFields[fieldName] = string;
		}
	}
}

