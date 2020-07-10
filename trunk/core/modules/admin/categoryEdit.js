/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
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
