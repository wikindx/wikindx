/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
**********************************************************************************/

/**
* Javascript functions for core/modules/resource/RESOURCECATEGORYEDIT.php and TEXTQP.php
*/

function transferKeyword()
{
	coreSelectToTextarea('keywords', 'fromKeywords');
}
function transferUserTag()
{
	coreSelectToTextarea('userTags', 'fromUserTags');
}
/**
* Transfer an option from the main categories selectbox to the selected categories selectbox
*/
function selectCategory()
{
	var target = 'categoryIds';
	var source = 'availableCategory';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the selected categories selectbox to the main categories selectbox
*/
function discardCategory()
{
	var target = 'availableCategory';
	var source = 'categoryIds';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the main subcategories selectbox to the selected subcategories selectbox
*/
function selectSubcategory()
{
	var target = 'subcategoryIds';
	var source = 'availableSubcategory';
	coreSelectToSelect(target, source);
}
/**
* Transfer an option from the selected subcategories selectbox to the main subcategories selectbox
*/
function discardSubcategory()
{
	var target = 'availableSubcategory';
	var source = 'subcategoryIds';
	coreSelectToSelect(target, source);
}
/**
* On submit, select all options in select boxes -- this allows PHP to pick up those options
*/
function selectAll()
{
	selectAllProcess('categoryIds');
	selectAllProcess('subcategoryIds');
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