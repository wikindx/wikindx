/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
**********************************************************************************/

/**
* Javascript functions for core/modules/admin/CONFIGURE.php
*/

/**
* Transfer an option from the available resources selectbox to the discarded resources selectbox
*/
function selectResource()
{
	var target = 'configDeactivateResourceTypes';
	var source = 'activeResourceTypes';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the disabled resources selectbox to the available resources selectbox
*/
function discardResource()
{
	var target = 'activeResourceTypes';
	var source = 'configDeactivateResourceTypes';
	coreSelectToSelect(target, source);
}
/**
* On submit, select all options in select boxes -- this allows PHP to pick up those options
*/
function selectAll()
{
	selectAllProcess('configDeactivateResourceTypes');
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
