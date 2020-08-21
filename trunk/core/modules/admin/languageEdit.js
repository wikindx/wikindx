/**********************************************************************************
WIKINDX: Bibliographic Management system.
Copyright (C)

Creative Commons
Creative Commons Legal Code
Attribution-NonCommercial-ShareAlike 2.0
THE WORK IS PROVIDED UNDER THE TERMS OF THIS CREATIVE COMMONS PUBLIC LICENSE ("CCPL" OR "LICENSE”) — docs/LICENSE.txt. 
THE WORK IS PROTECTED BY COPYRIGHT AND/OR OTHER APPLICABLE LAW. ANY USE OF THE WORK OTHER THAN AS AUTHORIZED UNDER 
THIS LICENSE OR COPYRIGHT LAW IS PROHIBITED.

BY EXERCISING ANY RIGHTS TO THE WORK PROVIDED HERE, YOU ACCEPT AND AGREE TO BE BOUND BY THE TERMS OF THIS LICENSE. 
THE LICENSOR GRANTS YOU THE RIGHTS CONTAINED HERE IN CONSIDERATION OF YOUR ACCEPTANCE OF SUCH TERMS AND CONDITIONS.

The WIKINDX Team 2016
sirfragalot@users.sourceforge.net

**********************************************************************************/

/**
* Javascript functions for core/modules/admin/ADMINLANGUAGES.php
*
* @version 1.1
* @date March 2013
* @author Mark Grimshaw
*/

function transferLanguage()
{
	coreSelectToTextbox('languageEdit', 'languageId', 'languageEditId');
}