<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */
 
/**
  * localDescription class.
  *
  * Store and make available localized versions of the front page description depending on the current language
  * localization the user is using.
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));


class localedescription_MODULE
{
    public $authorize;
    public $menus;
    private $pluginmessages;
    private $coremessages;
    private $session;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('localedescription', 'localedescriptionMessages');
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $this->config = new localedescription_CONFIG();
        $this->authorize = $this->config->authorize;
        $this->session = FACTORY_SESSION::getInstance();
        if ($menuInit) { // portion of constructor used for menu initialisation
            $this->makeMenu($this->config->menus);

            return; // need do nothing more.
        }

        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize)) { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }

        GLOBALS::setTplVar('heading', $this->pluginmessages->text('heading'));
    }
    /**
     * This is the initial method called from the menu item.
     */
    public function init()
    {
        return $this->display();
    }
    /**
     * display
     *
     * @param mixed $message
     */
    public function display($message = FALSE)
    {
        if ($message) {
            $pString = $message;
        } else {
            $pString = '';
        }
        $pString .= FORM\formHeader("localedescription_edit");
        $pString .= HTML\p($this->pluginmessages->text("text1"));
        $languages = \LOCALES\getSystemLocales();
        if ((count($languages) == 1) && array_key_exists(WIKINDX_LANGUAGE_DEFAULT, $languages)) {
            GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text("onlyEnglish"), "error", "center"));
            FACTORY_CLOSE::getInstance();
        }
        unset($languages[WIKINDX_LANGUAGE_DEFAULT]);
        $size = count($languages) > 5 ? 5 : count($languages);
        $pString .= HTML\p(FORM\selectFBoxValue(
            $this->pluginmessages->text("choose"),
            "language",
            $languages,
            $size
        ));
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Proceed")));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * edit
     */
    public function edit()
    {
        $db = FACTORY_DB::getInstance();
        $vars = GLOBALS::getVars();
        if (!array_key_exists('language', $vars)) {
            $this->display(HTML\p($this->pluginmessages->text("missingLanguage"), "error", "center"));
            FACTORY_CLOSE::getInstance();
        }
        $field = 'configDescription_' . $vars['language'];
        $db->formatConditions(['configName' => $field]);
        if ($input = $db->fetchOne($db->select('config', 'configText'))) {
            $input = HTML\nlToHtml($input);
        }
        $original = HTML\nlToHtml(WIKINDX_DESCRIPTION);
        $pString = HTML\p(HTML\strong($this->pluginmessages->text('original')));
        $pString .= HTML\p($original);
        $pString .= HTML\hr();
        $pString .= HTML\p($this->pluginmessages->text("text2"));
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $pString .= FORM\formHeader("localedescription_write");
        $pString .= FORM\hidden('language', $vars['language']);
        $pString .= $tinymce->loadMinimalTextarea(['description'], TRUE);
        $pString .= HTML\p(FORM\textareaInput(HTML\strong($vars['language']), "description", $input, 75, 20));
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * write
     */
    public function write()
    {
        $db = FACTORY_DB::getInstance();
        $vars = GLOBALS::getVars();
        $field = 'configDescription_' . $vars['language'];
        $db->formatConditions(['configName' => $field]);
        $resultSet = $db->select('config', '*');
        $exists = $db->numRows($resultSet);
        if (!array_key_exists('description', $vars) || !trim($vars['description'])) { // delete row if it exists in table
            if ($exists) {
                $db->formatConditions(['configName' => $field]);
                $db->delete('config');
            }
            $this->display(HTML\p($this->pluginmessages->text("success", $vars['language']), "success", "center"));
            FACTORY_CLOSE::getInstance();
        }
        // something to write
        if ($exists) {
            $db->formatConditions(['configName' => $field]);
            $db->update('config', ['configText' => trim($vars['description'])]);
        } else {
            $db->insert('config', ['configName', 'configText'], [$field, trim($vars['description'])]);
        }
        $this->display(HTML\p($this->pluginmessages->text("success", $vars['language']), "success", "center"));
    }
    /**
     * Make the menus
     *
     * @param mixed $menuArray
     */
    private function makeMenu($menuArray)
    {
        $this->menus = [$menuArray[0] => [$this->pluginmessages->text('menu') => "init"]];
    }
}
