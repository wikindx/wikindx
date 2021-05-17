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
  * xpdftotext_MODULE class.
  *
  * XpdftoText tool
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));


class xpdftotext_MODULE
{
    public $authorize;
    public $menus;
    
    private $pluginmessages;
    private $coremessages;
    private $config;
    private $vars;
    private $session;
    private $formData = [];

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        $this->session = FACTORY_SESSION::getInstance();
        // Conform to admin's configuration
        if ($this->session->getVar("setup_Superadmin"))
        {
            $this->displayItem = TRUE;
        }
        elseif (WIKINDX_METADATA_USERONLY && $this->session->getVar("setup_UserId"))
        {
            $this->displayItem = TRUE;
        }
        elseif (WIKINDX_METADATA_ALLOW)
        {
            $this->displayItem = TRUE;
        }
        if (!$this->displayItem)
        {
            return;
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('xpdftotext', 'xpdftotextMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $this->config = new xpdftotext_CONFIG();
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
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('heading'));
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('xpdftotext'));
        $this->vars = GLOBALS::getVars();
        
        // Make sure the utilities are executable
        $bindir = implode(DIRECTORY_SEPARATOR, [__DIR__, "bin"]);
        // Select a binary for the current OS
        switch (\UTILS\OSName())
        {
            case "windows":
                $binpdftotext = "pdftotext-win.exe";
                $binpdfinfo   = "pdfinfo-win.exe";
            break;
            case "mac":
                $binpdftotext = "pdftotext-mac";
                $binpdfinfo   = "pdfinfo-mac";
            break;
            default:
                $binpdftotext = "pdftotext-lin";
                $binpdfinfo   = "pdfinfo-lin";
            break;
        }
        
        foreach ([$binpdftotext, $binpdfinfo] as $bin)
        {
            $path = implode(DIRECTORY_SEPARATOR, [$bindir, $bin]);
            if (!is_executable($path))
            {
                @chmod($path, 0777);
                clearstatcache(TRUE, $path);
            }
        }
        foreach ([$binpdftotext, $binpdfinfo] as $bin)
        {
            $path = implode(DIRECTORY_SEPARATOR, [$bindir, $bin]);
            if (!is_executable($path))
            {
                die("
                <p>Some binaries of XpdftoText plugin need the executable bit.
                Please add it with chmod. The following commands should do the trick:</p>
                <ul>
                    <li>" . htmlspecialchars('chmod +x "' . realpath($binpdftotext) . '"') . "</li>
                    <li>" . htmlspecialchars('chmod +x "' . realpath($binpdfinfo) . '"') . "</li>
                </ul>
                ");
            }
        }
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
     * @param mixed $message
     */
    public function display()
    {
        GLOBALS::addTplVar('content', "");
    }
}
