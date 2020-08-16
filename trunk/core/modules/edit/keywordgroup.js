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
function availableKeyword()
{
	coreSelectToSelect('AvailableKeyword', 'SelectedKeyword');
}
/**
* Transfer an option from the main keywords selectbox to the selected keywords selectbox
*/
function edit_selectKeyword()
{
	coreSelectToSelect('editSelectedKeyword', 'editAvailableKeyword');
}
/**
* Transfer an option from the selected keywords selectbox to the main keywords selectbox
*/
function edit_availableKeyword()
{
	coreSelectToSelect('editAvailableKeyword', 'editSelectedKeyword');
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
function availableUserGroup()
{
	coreSelectToSelect('AvailableUserGroup', 'SelectedUserGroup');
}
/**
* Transfer an option from the main user groups selectbox to the selected user groups selectbox
*/
function edit_selectUserGroup()
{
	coreSelectToSelect('editSelectedUserGroup', 'editAvailableUserGroup');
}
/**
* Transfer an option from the selected user groups selectbox to the main user groups selectbox
*/
function edit_availableUserGroup()
{
	coreSelectToSelect('editAvailableUserGroup', 'editSelectedUserGroup');
}
/**
* On submit, select all options in the new select boxes -- this allows PHP to pick up those options.
*/
function selectAllNew()
{
	selectAllProcess('SelectedKeyword');
	selectAllProcess('SelectedUserGroup');
}
/**
* On submit, select all options in the edit select boxes -- this allows PHP to pick up those options.
*/
function selectAllEdit()
{
	selectAllProcess('editSelectedKeyword');
	selectAllProcess('editSelectedUserGroup');
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