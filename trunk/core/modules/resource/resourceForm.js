/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
**********************************************************************************/

/**
* Initialize AJAX for the new resource form
*/
var Creator1_index;
var Creator2_index;
var Creator3_index;
var Creator4_index;
var Creator5_index;
var creatorIndexInitialized_1 = false;
var creatorIndexInitialized_2 = false;
var creatorIndexInitialized_3 = false;
var creatorIndexInitialized_4 = false;
var creatorIndexInitialized_5 = false;


/**
* Start the process of javascript validating RESOURCEFORM input
*/
function validate()
{
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['targetDiv', 'script', 'requiredArray']))
		return false;
// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
	var names = [];
	var values = [];
	var multiNameObj;
	var multiValues = [];
	var count = 0;
	var countMulti = 0;
	var form = coreGetElementById('resourceForm');
	for(var i = 0; i < form.elements.length; i++)
	{
		if(form.elements[i].value != 0)
		{
			if((form.elements[i].name == 'action') || (form.elements[i].name == 'utf8CharTest'))
				continue;
			if(form.elements[i].type == 'select-multiple')
			{
				multiNameObj = form.elements[i];
				for(var x = 0; x < multiNameObj.length; x++)
				{
					if(multiNameObj[x].selected)
					{
						multiValues[countMulti] = multiNameObj[x].value;
						countMulti++;
					}
				}
				if(multiValues.length > 0)
				{
					names[count] = form.elements[i].name;
					values[count] = multiValues.join(',');
					multiValues = [];
					countMulti = 0;
				}
			}
			else
			{
				names[count] = form.elements[i].name;
				values[count] = coreTrim(form.elements[i].value);
			}
		}
		count++;
	}
	if(!validateInput(names, values))
		return false; // stop form submit
// If we get here, validation has been passed
	selectAll(); // Select options in category and subcategory boxes
	return true; // continue form submit (see ajax::gateway())
}
/**
* Check and validate input
*/
function validateInput(names, values)
{
	tinyMCE.triggerSave(); // See http://www.tinymce.com/forum/viewtopic.php?id=28
// Check resource title
// tinyMCE converts returns to <p> tags and encloses more than two spaces with <p> tags
//	coreGetElementById('resourceTitle').value = coreGetElementById('resourceTitle').value.replace(new RegExp("<p>|<\/p>+", "ig"), '');
	coreGetElementById('resourceTitle').value = coreGetElementById('resourceTitle').value.replace(/<(?:.|\n)*?>/gm, '');
//	coreGetElementById('resourceSubtitle').value = coreGetElementById('resourceSubtitle').value.replace(/<(?:.|\n)*?>/gm, '');
	if(!coreRemoveNonPrintable(coreTrim(coreGetElementById('resourceTitle').value)))
	{
		validateError('resourceTitle', 'missing');
		return false;
	}
	if((rtIndex = coreSearchArray(names, 'resourceType')) == -1)
		return false;
	var resourceType = values[rtIndex];
	if((resourceType == 'web_article') || (resourceType == 'web_encyclopedia_article') || 
	(resourceType == 'web_encyclopedia') || (resourceType == 'web_site'))
	{
		if(!coreTrim(coreGetElementById('resourcetextUrl').value) || (coreTrim(coreGetElementById('resourcetextUrl').value) == 'http://') || (coreTrim(coreGetElementById('resourcetextUrl').value) == 'https://'))
		{
			validateError('resourcetextUrl', 'missing');
			return false;
		}
	}
	var requiredArray = A_OBJ[gateway.aobj_index].input.requiredArray;
	requiredArray = JSON.parse(requiredArray.replace(/&quot;/g, "\""));
// Check required inputs
	for(var i in requiredArray)
	{
		if(i != resourceType)
			continue;
		var str = String(requiredArray[i]);
		if((str == 'collectionTitle') && (coreSearchArray(names, 'collectionId') != -1))
			continue;
		else if((str == 'collectionTitle') && (coreSearchArray(names, 'conferenceId') != -1))
			continue;
		if((str == 'publisherpublisherName') && (coreSearchArray(names, 'publisherId') != -1))
			continue;
		if((str == 'publishertransPublisherName') && (coreSearchArray(names, 'transPublisherId') != -1))
			continue;
		var spl = str.split(",");
		for(var id = 0; id < spl.length; id++)
		{
			var nameIndex = coreSearchArray(names, spl[id]);
			if(nameIndex == -1)
			{
				validateError(spl[id], 'missing');
				return false;
			}
		}
	}
	var creatorCount = 0;
	var checkSurname = new Array();
	for(i = 0; i < names.length; i++)
	{
		if(!names[i])
			continue;
// check resourcemiscXXX field values are all integers excepting 'PeerReviewed'
		if(String(names[i]).indexOf('resourcemisc') == 0) // at the start
		{
			if(names[i] == 'resourcemiscPeerReviewed')
				continue;
			if(!coreIsInteger(values[i]))
			{
				validateError(names[i], 'nan');
				return false;
			}
		}
// check creators -- '_surname' must exist if another field (except '_select') has an entry
		if(String(names[i]).indexOf('Creator') == 0) // at the start
		{
			var str = String(names[i]);
			var spl = str.split("_");
			if((spl[2] == 'firstname') || (spl[2] == 'initials') || (spl[2] == 'prefix'))
			{
				checkSurname[creatorCount] = spl[0] + '_' + spl[1] + '_surname'; // store surname field to check
				creatorCount++;
			}
		}
	}
	for(i = 0; i < checkSurname.length; i++)
	{
		cIndex = coreSearchArray(names, checkSurname[i]);
		if((cIndex == -1) || !values[cIndex])
			{
				validateError(checkSurname[i], 'missing');
				return false;
			}
	}
	return true;
}
/**
* Finish the process of javascript validation of RESOURCEFORM input when there is an error
*/
function validateError(field, error)
{
// Set the AJAX object's targetObj property
	var ajaxReturn = '&field=' + field + '&error=' + error;
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	return false; // stop form submit
}
/**
* Add a creator field
*/
function addCreator()
{
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['creatorType', 'script', 'type']))
		return false;
	var jObj = new Object;
	createJObj('increment', jObj); // NB objects passed by reference
// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.creatorType + '_Inner');
//alert(A_OBJ[gateway.aobj_index].targetObj + ': ' + A_OBJ[gateway.aobj_index].input.creatorType);
	var ajaxReturn = '&ajaxReturn=' + JSON.stringify(jObj);
//alert(JSON.stringify(jObj));
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	return true;
}
/**
* Remove a creator field
*/
function removeCreator()
{
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['creatorType', 'script', 'type']))
		return false;
	var jObj = new Object;
	createJObj('decrement', jObj);
// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.creatorType + '_Inner');
//alert(A_OBJ[gateway.aobj_index].targetObj + ': ' + A_OBJ[gateway.aobj_index].input.creatorType);
	var ajaxReturn = '&ajaxReturn=' + JSON.stringify(jObj);
//alert(JSON.stringify(jObj));
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	return true;
}
/**
* Populate the jObj object that, for one creatorType, stores existing creator fields and the field index counter.
* NB jObj is an object and so is passed in by reference
*/
	function createJObj(action, jObj)
	{
		if(A_OBJ[gateway.aobj_index].input.creatorType == 'Creator1')
		{
			if(!creatorIndexInitialized_1)
			{
				if(A_OBJ[gateway.aobj_index].input.index == undefined) // new resource
					Creator1_index = 0;
				else	// editing resource
					Creator1_index = A_OBJ[gateway.aobj_index].input.index;
				creatorIndexInitialized_1 = true;
			}
//alert('Creator1_index: ' + Creator1_index);
			if(action == 'increment')
				jObj.index = ++Creator1_index;
			else if(Creator1_index > 0)
				jObj.index = --Creator1_index;
			else
				jObj.index = 0; // default
		}
		else if(A_OBJ[gateway.aobj_index].input.creatorType == 'Creator2')
		{
			if(!creatorIndexInitialized_2)
			{
				if(A_OBJ[gateway.aobj_index].input.index == undefined)
					Creator2_index = 0;
				else
					Creator2_index = A_OBJ[gateway.aobj_index].input.index;
				creatorIndexInitialized_2 = true;
			}
//alert('Creator2_index: ' + Creator2_index);
			if(action == 'increment')
				jObj.index = ++Creator2_index;
			else if(Creator2_index > 0)
				jObj.index = --Creator2_index;
			else
				jObj.index = 0; // default
		}
		else if(A_OBJ[gateway.aobj_index].input.creatorType == 'Creator3')
		{
			if(!creatorIndexInitialized_3)
			{
				if(A_OBJ[gateway.aobj_index].input.index == undefined)
					Creator3_index = 0;
				else
					Creator3_index = A_OBJ[gateway.aobj_index].input.index;
				creatorIndexInitialized_3 = true;
			}
			if(action == 'increment')
				jObj.index = ++Creator3_index;
			else if(Creator3_index > 0)
				jObj.index = --Creator3_index;
			else
				jObj.index = 0; // default
		}
		else if(A_OBJ[gateway.aobj_index].input.creatorType == 'Creator4')
		{
			if(!creatorIndexInitialized_4)
			{
				if(A_OBJ[gateway.aobj_index].input.index == undefined)
					Creator4_index = 0;
				else
					Creator4_index = A_OBJ[gateway.aobj_index].input.index;
				creatorIndexInitialized_4 = true;
			}
			if(action == 'increment')
				jObj.index = ++Creator4_index;
			else if(Creator4_index > 0)
				jObj.index = --Creator4_index;
			else
				jObj.index = 0; // default
		}
		else if(A_OBJ[gateway.aobj_index].input.creatorType == 'Creator5')
		{
			if(!creatorIndexInitialized_5)
			{
				if(A_OBJ[gateway.aobj_index].input.index == undefined)
					Creator5_index = 0;
				else
					Creator5_index = A_OBJ[gateway.aobj_index].input.index;
				creatorIndexInitialized_5 = true;
			}
			if(action == 'increment')
				jObj.index = ++Creator5_index;
			else if(Creator5_index > 0)
				jObj.index = --Creator5_index;
			else
				jObj.index = 0; // default
		}
		var fieldName;
		jObj.creatorFields = new Object;
		for(var fieldIndex = 0; fieldIndex < jObj.index; fieldIndex++)
		{
			fieldName = A_OBJ[gateway.aobj_index].input.creatorType + '_' + fieldIndex + '_firstname';
			getObjValue(fieldName, jObj);
			fieldName = A_OBJ[gateway.aobj_index].input.creatorType + '_' + fieldIndex + '_initials';
			getObjValue(fieldName, jObj);
			fieldName = A_OBJ[gateway.aobj_index].input.creatorType + '_' + fieldIndex + '_prefix';
			getObjValue(fieldName, jObj);
			fieldName = A_OBJ[gateway.aobj_index].input.creatorType + '_' + fieldIndex + '_surname';
			getObjValue(fieldName, jObj);
			fieldName = A_OBJ[gateway.aobj_index].input.creatorType + '_' + fieldIndex + '_select';
			getObjValue(fieldName, jObj, true);
//alert(fieldName + ': ' + jObj.creatorFields[fieldName]);
		}
	}
/**
* return an object's value
*/
	function getObjValue(fieldName, jObj, selectBox)
	{
		if((fieldObj = coreGetElementById(fieldName)) != null)
		{
			var value;
			if(selectBox == undefined)
			{
				if(value = coreTrim(fieldObj.value))
					jObj.creatorFields[fieldName] = value;
			}
			else // selectBox
			{
				if((value = fieldObj.value) > 0) // ignore IGNORE
					jObj.creatorFields[fieldName] = value;
			}
		}
	}
/**
* sets inner HTML of a DIV
*/
function setDiv()
{
	var targetDivObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
	targetDivObj.innerHTML = A_OBJ[gateway.aobj_index].input.targetContent;
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
function transferKeyword()
{
	coreSelectToTextarea('keywordList', 'resourcekeywordKeywords');
}
function transferUserTag()
{
	coreSelectToTextarea('userTagList', 'resourceusertagsTagId');
}
/**
* Transfer an option from the main categories selectbox to the selected categories selectbox
*/
function selectCategory()
{
	var target = 'resourcecategoryCategories';
	var source = 'availableCategory';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the selected categories selectbox to the main categories selectbox
*/
function discardCategory()
{
	var target = 'availableCategory';
	var source = 'resourcecategoryCategories';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the main subcategories selectbox to the selected subcategories selectbox
*/
function selectSubcategory()
{
	var target = 'resourcecategorySubcategories';
	var source = 'availableSubcategory';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the selected subcategories selectbox to the main subcategories selectbox
*/
function discardSubcategory()
{
	var target = 'availableSubcategory';
	var source = 'resourcecategorySubcategories';
	coreSelectToSelect(target, source);
}
/**
* On submit, select all options in select boxes -- this allows PHP to pick up those options
*/
function selectAll()
{
	selectAllProcess('resourcecategoryCategories');
	selectAllProcess('resourcecategorySubcategories');
}
/**
* Select selected options
*/
function selectAllProcess(box)
{
	var element = box;
	var obj = coreGetElementById(element);
	if(obj == null)
		return;
	for(i = obj.options.length - 1; i >= 0; i--)
		obj.options[i].selected = true;
}
