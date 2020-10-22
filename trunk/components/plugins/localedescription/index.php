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
    private $db;
    private $vars;
    public $authorize;
    public $menus;
    private $pluginmessages;
    private $coremessages;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('localedescription', 'localedescriptionMessages');
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $this->config = new localedescription_CONFIG();
        $this->authorize = $this->config->authorize;
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
     * display select box to choose localization
     *
     * @param mixed $message
     *
     */
    public function display($message = FALSE)
    {
        if (array_key_exists('message', $this->vars)) {
            $pString = $this->vars['message'];
        } else {
        	$pString = $message;
        }
        if (array_key_exists('language', $this->vars)) {
            $language = $this->vars['language'];
        } else {
        	$language = FALSE;
        }
        $pString .= FORM\formHeader("localedescription_edit");
        $pString .= HTML\p($this->pluginmessages->text("text1"));
        $languages = \LOCALES\getSystemLocales();
        if ((count($languages) == 1) && array_key_exists(WIKINDX_LANGUAGE_DEFAULT, $languages)) {
            GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text("onlyEnglish"), "error", "center"));
            FACTORY_CLOSE::getInstance();
        }
        unset($languages[WIKINDX_LANGUAGE_DEFAULT]);
        if (!$language) {
        	foreach ($languages as $lang => $null) {
        		$language = $lang;
        		break;
        	}
        }
        $size = count($languages) > 5 ? 5 : count($languages);
        $pString .= HTML\p(FORM\selectedBoxValue(
            $this->pluginmessages->text("choose"),
            "language",
            $languages,
            $language,
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
        if (!array_key_exists('language', $this->vars)) {
            $this->display(HTML\p($this->pluginmessages->text("missingLanguage"), "error", "center"));
            FACTORY_CLOSE::getInstance();
        }
        $field = 'configDescription_' . $this->vars['language'];
        $this->db->formatConditions(['configName' => $field]);
        if ($input = $this->db->fetchOne($this->db->select('config', 'configText'))) {
            $input = HTML\nlToHtml($input);
        }
        $original = HTML\nlToHtml(WIKINDX_DESCRIPTION);
        $pString = HTML\p(HTML\strong($this->pluginmessages->text('original')));
        $pString .= HTML\p($original);
        $pString .= HTML\hr();
        $pString .= HTML\p($this->pluginmessages->text("text2"));
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $pString .= FORM\formHeader("localedescription_write");
        $pString .= FORM\hidden('language', $this->vars['language']);
        $pString .= $tinymce->loadMinimalTextarea(['description'], TRUE);
        $pString .= HTML\p(FORM\textareaInput(HTML\strong($this->vars['language']), "description", $input, 75, 20));
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * write
     */
    public function write()
    {
        $message = rawurlencode(HTML\p($this->pluginmessages->text("success", $this->vars['language']), 'success', 'center'));
        $field = 'configDescription_' . $this->vars['language'];
        $this->db->formatConditions(['configName' => $field]);
        $resultSet = $this->db->select('config', '*');
        $exists = $this->db->numRows($resultSet);
        if (!array_key_exists('description', $this->vars) || !\UTF8\mb_trim($this->vars['description'])) { // delete row if it exists in table
            if ($exists) {
                $this->db->formatConditions(['configName' => $field]);
                $this->db->delete('config');
            }
            header("Location: index.php?action=localedescription_init&message=$message&language=" . $this->vars['language']);
        	die;
        }
        // something to write
        if ($exists) {
            $this->db->formatConditions(['configName' => $field]);
            $this->db->update('config', ['configText' => \UTF8\mb_trim($this->vars['description'])]);
        } else {
            $this->db->insert('config', ['configName', 'configText'], [$field, \UTF8\mb_trim($this->vars['description'])]);
        }
        header("Location: index.php?action=localedescription_init&message=$message&language=" . $this->vars['language']);
        die;
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
