/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
**********************************************************************************/

/**
* ajax.js
*
* This file provides all the AJAX javascript functionality.
* gateway() is the function to be called from the web page action and is set in core/ajax/AJAX.php.
*/

var A_OBJ = new Array(); // array of AJAX objects
var noTarget = false;
var visible = false;

/********************************************* FUNCTIONS *****************************************/

/**
* Gateway to AJAX javascript functions
* @param input - JSON string representing an array of sets of instructions to be processed in numerical order. At the very
* least a startFunction() method should be present in each set.
*/
function gateway(input)
{
	var vars = '';
	var parsedInput = JSON.parse(input);
	var inputLen = parsedInput.length;
	if(gateway.aobj_index == undefined)
		gateway.aobj_index = -1;
	gateway.aobj_index++;
	for(var i = 0; i < inputLen; i++)
	{
		if(parsedInput[i].startFunction != undefined)
		{
			if(parsedInput[i].startFunctionVars != undefined)
				vars = parsedInput[i].startFunctionVars;
			A_OBJ[gateway.aobj_index] = new AJAXOBJECT();
			A_OBJ[gateway.aobj_index].input = parsedInput[i];
			A_OBJ[gateway.aobj_index].evalReturn = eval('(' + A_OBJ[gateway.aobj_index].input.startFunction + '(' + vars + '))');
			if(A_OBJ[gateway.aobj_index].evalReturn == false)
			{
				return false;
			}
		}
		else
		{
			alert('No startFunction() defined');
			return false;
		}
		gateway.aobj_index++;
	}
	return true;
}

/**
* The object used for independent AJAX instances
*/
function AJAXOBJECT()
{
//properties
	this.input = null;
	this.processedScript = null;
	this.phpResponse = null;
	this.targetObj = null;
	this.evalReturn = null;
// methods
	this.checkInput = checkInput;
	this.doXmlHttp = doXmlHttp;
}

/**
* Check required input parameters are present in input
* @param input array
* @return false|true
*/
function checkInput(inputArray)
{
	var len = inputArray.length;
	for(var i = 0; i < len; i++)
	{
		if(this.input[inputArray[i]] == undefined)
		{
			alert('required input parameter is missing: ' + inputArray[i]);
			return false;
		}
	}
	return true;
}

/**
* Create the xmlHttp object
* @return xmlHttp object
*/	
function createXmlHttpObject()
{
	var xmlHttp = false;
//Check if we are using IE.
	try
	{
//If the javascript version is greater than 5.
		xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
	} 
	catch (e)
	{
//If not, then use the older active x object.
		try
		{
//If we are using IE.
			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
		} 
		catch (E)
		{
//Else we must be using a non-IE browser.
			xmlHttp = false;
		}
	}
//If we are using a non-IE browser, create a JavaScript instance of the object.
	if (!xmlHttp && typeof XMLHttpRequest != undefined)
	{
		xmlHttp = new XMLHttpRequest();
	}
	return xmlHttp;
}

/**
* Execute the xmlHTTP object
*
* @return true|false
*/
function doXmlHttp()
{
	if(this.targetObj == null)
	{
		alert('No AJAXOBJECT targetObj property set');
		return false;
	}
	if(this.processedScript == null)
	{
		alert('No AJAXOBJECT processedScript property set');
		return false;
	}
	var targetObj = this.targetObj;
	var xmlHttp = createXmlHttpObject();
	xmlHttp.open("GET", this.processedScript, true);
//alert(this.processedScript);
	xmlHttp.onreadystatechange = function()
	{
		if((xmlHttp.readyState == 4) && (xmlHttp.status == 200))
		{
			try
			{
				this.phpResponse = JSON.parse(xmlHttp.responseText);
			}
			catch(err)
			{
				alert('HTTP status: ' + xmlHttp.status + '. ' + err.message + ' ERROR. PHP says:\n' + xmlHttp.responseText);
				return false;
			}
			if(this.phpResponse.ERROR)
			{
				alert('PHP ERROR:\n' + this.phpResponse.ERROR);
				return false;
			}
			if(this.phpResponse.innerHTML)
				targetObj.innerHTML = this.phpResponse.innerHTML;
//alert(this.phpResponse.next);
// Further action to be taken?
			if(this.phpResponse.next != undefined)
				gateway(JSON.stringify([this.phpResponse]));
		}
	}
	xmlHttp.send(null);
	A_OBJ[gateway.aobj_index].xmlHttp = xmlHttp;
	return true;
}

/**
* Do something AJAXy in the 'targetDiv' based upon input from a multiple select box form element
*
* @return boolean
*/
function triggerFromMultiSelect()
{
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['triggerField', 'targetDiv', 'script']))
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
		if(triggerObj.options[i].selected && (triggerObj.options[i].value != 0)) // don't include 'IGNORE'
			triggerOptionValues.push(triggerObj.options[i].value);
	}
	if(triggerOptionValues.length)
		ajaxReturn = '&ajaxReturn=' + triggerOptionValues.join();
// Gather the current selected OPTIONS of the targetDiv select box
	if(A_OBJ[gateway.aobj_index].input.restoreField != undefined)
	{
		var restoreObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.restoreField);
		if(restoreObj)
		{
			len = restoreObj.options.length;
			var restoreOptionValues = new Array();
			for(var i = 0; i < len; i++)
			{
				if(restoreObj.options[i].selected && (restoreObj.options[i].value != 0)) // don't include 'IGNORE'
					restoreOptionValues.push(restoreObj.options[i].value);
			}
//alert(A_OBJ[gateway.aobj_index].input.targetDiv+ ' ' + restoreOptionValues.length);
			if(restoreOptionValues.length)
				ajaxReturn = ajaxReturn + '&ajaxReturn2=' + restoreOptionValues.join();
		}
	}
//alert(A_OBJ[gateway.aobj_index].input.targetDiv + ': ' + ajaxReturn);
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
function triggerFromSelect()
{
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['triggerField', 'targetDiv', 'script']))
		return false;
// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
// Gather the selected OPTIONS of the triggerField select box
	var triggerObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.triggerField);
	var ajaxReturn = '';
	ajaxReturn = '&ajaxReturn=' + triggerObj.value;
//alert(A_OBJ[gateway.aobj_index].input.targetDiv + ': ' + ajaxReturn);
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	return true;
}

/**
* Do something AJAXy in the 'targetDiv' based upon input from a single checkbox form element
*/
function triggerFromCheckbox()
{
// Check we have the required input
	if(!A_OBJ[gateway.aobj_index].checkInput(['triggerField', 'targetDiv', 'script']))
		return false;
// Set the AJAX object's targetObj property
	A_OBJ[gateway.aobj_index].targetObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
// Gather the selected OPTIONS of the triggerField select box
	var triggerObj = coreGetElementById(A_OBJ[gateway.aobj_index].input.triggerField);
	var ajaxReturn = '';
	if(!visible)
	{
		A_OBJ[gateway.aobj_index].targetObj.style.display = 'block';
		visible = true;
	}
	else
	{
		A_OBJ[gateway.aobj_index].targetObj.style.display = 'none';
		visible = false;
	}
//alert(A_OBJ[gateway.aobj_index].input.targetDiv + ': ' + ajaxReturn);
	A_OBJ[gateway.aobj_index].processedScript = A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
// Execute the AJAX
	if(!A_OBJ[gateway.aobj_index].doXmlHttp())
		return false;
	return true;
}
