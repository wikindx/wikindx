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

function sendMywikindxInput()
{
	var type = coreGetElementById('method');
	if((type.value == 'createUserGroup') || (type.value == 'editUserGroup') || (type.value == 'createUserBib') || 
		(type.value == 'createGroupBib') || (type.value == 'editBib'))
	{
		var i;
		var select = coreGetElementById('addUsers');
		if(select != null)
		{
			var addUsers = new Array();
			for(i = 0, length = select.length; i < length; i++)
			{
				if(select.options[i].selected)
				{
					if(select.options[i].value != null)
						addUsers[i] = select.options[i].value;
				}
			}
		}
		else
			var addUsers = [0];
		addUsers = JSON.stringify(addUsers);
		var obj = coreGetElementById('title');
		var title = JSON.stringify(coreTrim(obj.value));
		var description = coreTrim(tinymce.EditorManager.get('description').getContent());
		description = description.replace(/^<p>/, '');
		description = description.replace(/<\/p>/, '');
		description = JSON.stringify(description);
		if((type.value == 'editUserGroup') || (type.value == 'editBib'))
		{
			select = coreGetElementById('deleteUsers');
			if(select != null)
			{
				var deleteUsers = new Array();
				for(i = 0, length = select.length; i < length; i++)
				{
					if(select.options[i].selected)
					{
						if(select.options[i].value != null)
							deleteUsers[i] = select.options[i].value;
					}
				}
			}
			else
				var deleteUsers = [0];
			deleteUsers = JSON.stringify(deleteUsers);
			if(type.value == 'editUserGroup')
			{
				var groupId = coreGetElementById('groupId').value;
				var options = '&title=' + title + '&description=' + description + 
					'&addUsers=' + addUsers + '&deleteUsers=' + deleteUsers + '&groupId=' + groupId;
			}
			else
			{
				var bibId = coreGetElementById('bibId').value;
				var groupId = coreGetElementById('groupId').value;
				var options = '&title=' + title + '&description=' + description + '&groupId=' + groupId + '&bibId=' + bibId;
			}
		}
		else
			var options = '&title=' + title + '&description=' + description + '&addUsers=' + addUsers;
	}
	else if(type.value == 'deleteUserGroup')
	{
		var groupId = coreGetElementById('groupId').value;
		var options = '&groupId=' + groupId;
	}
	else if((type.value == 'createUserTag') || (type.value == 'editUserTag'))
	{
		var obj = coreGetElementById('title');
		var title = JSON.stringify(coreTrim(obj.value));
		if(type.value == 'editUserTag')
		{
			var tagId = coreGetElementById('tagId').value;
			var options = '&title=' + title + '&tagId=' + tagId;
		}
		else
			var options = '&title=' + title;
	}
	else if(type.value == 'deleteUserTag')
	{
		var tagId = coreGetElementById('tagId').value;
		var options = '&tagId=' + tagId;
	}
	else if(type.value == 'deleteBib')
	{
		var bibId = coreGetElementById('bibId').value;
		var options = '&bibId=' + bibId;
	}
	else
	{
		alert('Missing type');
		return;
	}
	var method = type.value;
	var url = 'index.php?action=usersgroups_MYWIKINDX_CORE&method=' + method + options;
	window.opener.location.href=url;
	window.close();
}
