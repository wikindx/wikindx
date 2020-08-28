/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
**********************************************************************************/

/**
* Transfer an option from the creators' selectbox to the group members' selectbox
*/
function toMembers()
{
	coreSelectToSelect('creatorIds', 'creators');
}
/**
* Transfer an option from the group members' selectbox to the creators' selectbox
*/
function fromMembers()
{
	coreSelectToSelect('creators', 'creatorIds');
}
/**
* On submit, select all options in the group members' box -- this allows PHP to pick up those options.
*/
function selectAll()
{
	selectAllProcess('creatorIds');
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