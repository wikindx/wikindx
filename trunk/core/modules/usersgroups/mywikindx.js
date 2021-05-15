/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
**********************************************************************************/

/**
* Javascript functions for core/modules/usergroups/MYWIKINDX.php
*/

function getMywikindxInputGroups()
{
	var i;
	var selectItem = coreGetElementById('groupId');
	if(selectItem != null)
	{
		var i;
		for(i = 0, length = selectItem.length; i < length; i++)
		{
			if(selectItem.options[i].selected)
			{
				var groupId = selectItem.options[i].value;
				break;
			}
		}
	}
	else // no groups currently extant
		var groupId = 0;
	var radioItem = coreGetElementByName('method');
	for(i = 0, length = radioItem.length; i < length; i++)
	{
		if(radioItem[i].checked)
		{
			var method = radioItem[i].value;
			break;
		}
	}
	var options = '&method=' + method + '&groupId=' + groupId;
	coreOpenPopup('index.php?action=usersgroups_MYWIKINDX_CORE' + options, 90)
}
function getMywikindxInputTags()
{
	var i;
	var selectItem = coreGetElementById('tagId');
	if(selectItem != null)
	{
		var i;
		for(i = 0, length = selectItem.length; i < length; i++)
		{
			if(selectItem.options[i].selected)
			{
				var tagId = selectItem.options[i].value;
				break;
			}
		}
	}
	else // no tags currently extant
		var tagId = 0;
	var radioItem = coreGetElementByName('method');
	for(i = 0, length = radioItem.length; i < length; i++)
	{
		if(radioItem[i].checked)
		{
			var method = radioItem[i].value;
			break;
		}
	}
	var options = '&method=' + method + '&tagId=' + tagId;
	coreOpenPopup('index.php?action=usersgroups_MYWIKINDX_CORE' + options, 90)
}

function getMywikindxInputBibs()
{
	var i;
	var selectItem = coreGetElementById('bibId');
	if(selectItem != null)
	{
		var i;
		for(i = 0, length = selectItem.length; i < length; i++)
		{
			if(selectItem.options[i].selected)
			{
				var bibId = selectItem.options[i].value;
				break;
			}
		}
	}
	else // no bibs currently extant
		var bibId = 0;
	var radioItem = coreGetElementByName('method');
	for(i = 0, length = radioItem.length; i < length; i++)
	{
		if(radioItem[i].checked)
		{
			var method = radioItem[i].value;
			break;
		}
	}
	var options = '&method=' + method + '&bibId=' + bibId;
	coreOpenPopup('index.php?action=usersgroups_MYWIKINDX_CORE' + options, 90)
}

function closeAndRedirect()
{
	selectItem = '&selectItem=' + selectItem;
	if (typeof uuid !== 'undefined') {
		uuid = '&uuid=' + uuid.value;
		var url = 'index.php?action=usersgroups_MYWIKINDX_CORE&method=init' + selectItem + uuid;
	} else {
		var url = 'index.php?action=usersgroups_MYWIKINDX_CORE&method=init' + selectItem;
	}
	window.opener.location.href=url;
	window.close();
}
/**
* Transfer an option from the group users selectbox to the potential users selectbox
*/
function selectedUsers()
{
	var target = 'potentialUsers';
	var source = 'selectedUsers';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the potential users selectbox to the selected users selectbox
*/
function potentialUsers()
{
	var target = 'selectedUsers';
	var source = 'potentialUsers';
	coreSelectToSelect(target, source);
}
/**
* Select selected options
*/
function selectAll()
{
	var obj = coreGetElementById('selectedUsers');
	if(obj == null)
		return;
	for(i = obj.options.length - 1; i >= 0; i--)
		obj.options[i].selected = true;
}
