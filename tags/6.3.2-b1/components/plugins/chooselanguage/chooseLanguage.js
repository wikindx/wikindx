/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
**********************************************************************************/

/**
* Javascript functions for plugins/chooseLanguage
*/

function chooseLanguageChangeLanguage(language)
{
    window.location='index.php?action=chooselanguage_resetLanguage&language=' + language;
}