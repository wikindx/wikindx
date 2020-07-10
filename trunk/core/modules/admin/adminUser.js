/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
**********************************************************************************/

/**
* Javascript functions for core/modules/admin/ADMINUSER.php
*/

/**
* Transfer an option from the authorized users selectbox to the blocked users selectbox
*/
function blockUser()
{
	var target = 'blockedUsers';
	var source = 'authorizedUsers';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the blocked users selectbox to the authorized users selectbox
*/
function authUser()
{
	var target = 'authorizedUsers';
	var source = 'blockedUsers';
	coreSelectToSelect(target, source);
}
/**
* On submit, select all options in select boxes -- this allows PHP to pick up those options
*/
function selectAll()
{
	selectAllProcess('authorizedUsers');
	selectAllProcess('blockedUsers');
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
