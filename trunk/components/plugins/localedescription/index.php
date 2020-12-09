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
    private $db;
    private $vars;
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
        $this->checkTables();
        $this->vars = GLOBALS::getVars();
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('localedescription', 'localedescriptionMessages');
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $this->config = new localedescription_CONFIG();
        $this->authorize = $this->config->authorize;
        if ($menuInit)
        { // portion of constructor used for menu initialisation
            $this->makeMenu($this->config->menus);

            return; // need do nothing more.
        }

        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
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
     */
    public function display($message = FALSE)
    {
        if (array_key_exists('message', $this->vars))
        {
            $pString = $this->vars['message'];
        }
        else
        {
            $pString = $message;
        }
        if (array_key_exists('language', $this->vars))
        {
            $language = $this->vars['language'];
        }
        else
        {
            $language = FALSE;
        }
        $pString .= FORM\formHeader("localedescription_edit");
        $pString .= HTML\p($this->pluginmessages->text("text1"));
        
        // Get translatables locales minus the main locale
        $languages = \LOCALES\getTranslatableLocales(\LOCALES\determine_locale());
        unset($languages[WIKINDX_LANGUAGE]);
        
        if (count($languages) == 0)
        {
            GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text("noLocaleAvailable", WIKINDX_LANGUAGE), "error", "center"));
        }
        else
        {
            $recordset = $this->db->select('plugin_localedescription', 'pluginlocaledescriptionLocale');
            if ($this->db->numRows($recordset) > 0)
            {
                while ($row = $this->db->fetchRow($recordset))
                {
                    if (array_key_exists($row['pluginlocaledescriptionLocale'], $languages))
                    {
                        $languages[$row['pluginlocaledescriptionLocale']] = "* " . $languages[$row['pluginlocaledescriptionLocale']];
                    }
                }
                
                asort($languages, SORT_LOCALE_STRING);
            }
            
            if (!$language)
            {
                foreach ($languages as $lang => $null)
                {
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
    }
    /**
     * checkTables
     */
    private function checkTables()
    {
        $version = \UPDATE\getPluginInternalVersion($this->db, mb_strtolower(basename(__DIR__)));
        
        if ($version == 0)
        {
            // NB: Windows MySQL lowercases any table name
            // To be sure, it is necessary to lowercase all table elements
            $tables = $this->db->listTables(FALSE);
            foreach ($tables as $k => $v)
            {
                $tables[$k] = mb_strtolower($v);
            }
            
            if (array_search('plugin_localedescription', $tables) === FALSE)
            {
                $this->db->queryNoError("
                    CREATE TABLE `" . WIKINDX_DB_TABLEPREFIX . "plugin_localedescription` (
                        `pluginlocaledescriptionLocale` varchar(16) COLLATE utf8mb4_unicode_520_ci NOT NULL,
                        `pluginlocaledescriptionText` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
                        PRIMARY KEY (`pluginlocaledescriptionLocale`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
    			");
            }
            
            \UPDATE\setPluginInternalVersion($this->db, mb_strtolower(basename(__DIR__)), 1);
        }
    }
    /**
     * edit
     */
    public function edit()
    {
        if (!array_key_exists('language', $this->vars))
        {
            $this->display(HTML\p($this->pluginmessages->text("missingLanguage"), "error", "center"));
            FACTORY_CLOSE::getInstance();
        }
        $this->db->formatConditions(['pluginlocaledescriptionLocale' => $this->vars['language']]);
        if ($input = $this->db->fetchOne($this->db->select('plugin_localedescription', 'pluginlocaledescriptionText')))
        {
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
        $pString .= HTML\p(FORM\textareaInput(HTML\strong($this->vars['language'] . " - " . \Locale::getDisplayName($this->vars['language'], \LOCALES\determine_locale())), "description", $input, 75, 20));
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
        
        $this->db->formatConditions(['pluginlocaledescriptionLocale' => $this->vars['language']]);
        $resultSet = $this->db->select('plugin_localedescription', '*');
        $exists = $this->db->numRows($resultSet);
        
        if (!array_key_exists('description', $this->vars) || !\UTF8\mb_trim($this->vars['description']))
        { // delete row if it exists in table
            if ($exists)
            {
                $this->db->formatConditions(['pluginlocaledescriptionLocale' => $this->vars['language']]);
                $this->db->delete('plugin_localedescription');
            }
            header("Location: index.php?action=localedescription_init&message=$message&language=" . $this->vars['language']);
            die;
        }
        // something to write
        if ($exists)
        {
            $this->db->formatConditions(['pluginlocaledescriptionLocale' => $this->vars['language']]);
            $this->db->update('plugin_localedescription', ['pluginlocaledescriptionText' => \UTF8\mb_trim($this->vars['description'])]);
        }
        else
        {
            $this->db->insert('plugin_localedescription', ['pluginlocaledescriptionLocale', 'pluginlocaledescriptionText'], [$this->vars['language'], \UTF8\mb_trim($this->vars['description'])]);
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
