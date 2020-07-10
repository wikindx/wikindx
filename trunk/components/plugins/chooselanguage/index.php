<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */
 
/**
  * chooseLanguage class.
  *
  * The user can change the language from all pages.
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");


class chooselanguage_MODULE
{
    public $authorize;
    public $menus;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "config.php");
        $localconfig = new chooselanguage_CONFIG();
        $this->authorize = $localconfig->authorize;
        GLOBALS::setTplVar($localconfig->container, $this->display());
        if ($menuInit)
        { // portion of constructor used for menu initialisation
            return; // need do nothing more.
        }
        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
    }
    /**
     * resetLanguage
     */
    public function resetLanguage()
    {
        $vars = GLOBALS::getVars();
	    $session = FACTORY_SESSION::getInstance();
        if (array_key_exists('language', $vars))
        {
            $language = $vars['language'];
        }
        $LanguageNeutralChoice = "auto";
        $languages[$LanguageNeutralChoice] = "Auto";
        $languages = array_merge($languages, \LOCALES\getSystemLocales());
        array_key_exists($language, $languages) ? $language = $language : $language = $LanguageNeutralChoice;
        
        $userId = $session->getVar('setup_UserId');
	    if ($userId)
	    {
        	$db = FACTORY_DB::getInstance();
	    	$db->formatConditions(['usersId' => $userId]);
	    	$db->update('users', ['usersLanguage' => $language]);
	    }
	    else // read-only user
	    {
	        $session->setVar("setup_Language", $language);
	    }
        header("Location: index.php");
    }
    /**
     * display
     */
    private function display()
    {
        $session = FACTORY_SESSION::getInstance();
        $this->config = FACTORY_LOADCONFIG::getInstance();
        $db = FACTORY_DB::getInstance();
        
        // For the graphical interface, add the "auto" value that allows to say that the language is chosen by the browser.
        $LanguageNeutralChoice = "auto";
        $languages[$LanguageNeutralChoice] = "Auto";
        $languages = array_merge($languages, \LOCALES\getSystemLocales());
        
        $userId = $session->getVar('setup_UserId');
        if ($userId)
        {
	        $db->formatConditions(['usersId' => $userId]);
	        $language = $db->selectFirstField("users", "usersLanguage");
	        array_key_exists($language, $languages) ? $language = $language : $language = $LanguageNeutralChoice;
		}
		else // i.e. read-only so use a session
		{
	        $language = $session->getVar("setup_Language", $LanguageNeutralChoice);
	        array_key_exists($language, $languages) ? $language = $language : $language = $LanguageNeutralChoice;
	    }
        
        $display = "";

        if (count($languages) > 1)
        {
            $display .= HTML\jsInlineExternal(WIKINDX_BASE_URL . '/' . str_replace("\\", "/", WIKINDX_DIR_COMPONENT_PLUGINS) . '/' . basename(__DIR__) . '/chooseLanguage.js?ver=' . WIKINDX_PUBLIC_VERSION);
            $js = 'onchange="javascript:chooseLanguageChangeLanguage(this.value);"';
            $display .= FORM\selectedBoxValue(FALSE, "Language", $languages, $language, 1, FALSE, $js);
        }

        return $display;
    }
}
