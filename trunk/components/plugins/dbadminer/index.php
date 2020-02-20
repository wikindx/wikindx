<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
 
/**
  * dbAdminer class.
  *
  * Wikindx custom wrapper for adminer
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");


class dbadminer_MODULE
{
    public $authorize;
    public $menus;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $config;
    private $session;
    private $vars;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        include_once("core/messages/PLUGINMESSAGES.php");
        $this->pluginmessages = new PLUGINMESSAGES('dbadminer', 'dbadminerMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "config.php");
        $this->config = new dbadminer_CONFIG();
        $this->session = FACTORY_SESSION::getInstance();
        $this->authorize = $this->config->authorize;
        if ($menuInit)
        {
            $this->makeMenu($this->config->menus);

            return; // Need do nothing more as this is simply menu initialisation.
        }

        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }

        $this->vars = GLOBALS::getVars();
    }
    
    /**
     * This is the initial method called from the menu item
     */
    public function init()
    {
        return $this->display();
    }
    
    /**
     * display
     * 
     * @param string|FALSE $message
     */
    public function display($message = FALSE)
    {
        GLOBALS::setTplVar("heading", $this->pluginmessages->text("heading"));
        
        GLOBALS::addTplVar(
            "content",
            HTML\p(HTML\a(
                "link",
                $this->pluginmessages->text("openlink"),
                WIKINDX_DIR_COMPONENT_PLUGINS . "/" . basename(__DIR__) . "/adminer.php",
                "_blank"
            ))
        );
    }

    /**
     * Make the menu
     * 
     * @param array $menuArray
     */
    private function makeMenu($menuArray)
    {
        $this->menus = [
            $menuArray[0] => [$this->pluginmessages->text('menu') => "init"],
        ];
    }
}
