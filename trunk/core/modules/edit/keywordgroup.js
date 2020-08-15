/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
**********************************************************************************/

/**
* Transfer an option from the main keywords selectbox to the selected keywords selectbox
*/
function selectKeyword()
{
	coreSelectToSelect('SelectedKeyword', 'AvailableKeyword');
}
/**
* Transfer an option from the selected keywords selectbox to the main keywords selectbox
*/
function discardKeyword()
{
	coreSelectToSelect('AvailableKeyword', 'SelectedKeyword');
}
/**
* Transfer an option from the main user groups selectbox to the selected user groups selectbox
*/
function selectUserGroup()
{
	coreSelectToSelect('SelectedUserGroup', 'AvailableUserGroup');
}
/**
* Transfer an option from the selected user groups selectbox to the main user groups selectbox
*/
function discardUserGroup()
{
	coreSelectToSelect('AvailableUserGroup', 'SelectedUserGroup');
}
/**
* On submit, select all options in the new selected keyword box -- this allows PHP to pick up those options.  Extra options for select resources
*/
function selectAllNew()
{
	selectAllProcess('SelectedKeyword');
	selectAllProcess('SelectedUserGroup');
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