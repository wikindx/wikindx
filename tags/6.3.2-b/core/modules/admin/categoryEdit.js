/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
**********************************************************************************/

/**
* Javascript functions for core/modules/admin/ADMINCATEGORIES.php
*/

function transferCategory()
{
	coreSelectToTextbox('categoryEdit', 'categoryId', 'categoryEditId');
}
function transferSubcategory()
{
	coreSelectToTextbox('subcategoryEdit', 'subcategoryId', 'subcategoryEditId');
}