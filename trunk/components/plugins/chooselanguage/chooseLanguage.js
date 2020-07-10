/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
**********************************************************************************/

/**
* Javascript functions for plugins/chooseLanguage
*/

function chooseLanguageChangeLanguage(language)
{
    window.location='index.php?action=chooselanguage_resetLanguage&language=' + language;
}
