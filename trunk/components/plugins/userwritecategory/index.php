<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * userwritecategory class.
 *
 * User administration of categories - non-admin users can add/edit/delete categories (they must be logged in).
 */

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");


class userwritecategory_MODULE
{
    public $authorize;
    public $menus;
    private $pluginmessages;
    private $acObject;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        if (FACTORY_SESSION::getInstance()->getVar("setup_Superadmin")) {
            return;
        }
        include_once("core" . DIRECTORY_SEPARATOR . "messages" . DIRECTORY_SEPARATOR . "PLUGINMESSAGES.php");
        $this->pluginmessages = new PLUGINMESSAGES('userwritecategory', 'userwritecategoryMessages');
        include_once("plugins" . DIRECTORY_SEPARATOR . "userwritecategory" . DIRECTORY_SEPARATOR . "config.php");
        $config = new userwritecategory_CONFIG();
        include_once("core" . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'ADMINCATEGORIES.php');
        $this->authorize = $config->authorize;
        if ($menuInit) { // portion of constructor used for menu initialisation
            $this->makeMenu($config->menus);

            return; // Need do nothing more as this is simply menu initialisation.
        }
        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize)) { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        $this->acObject = new ADMINCATEGORIES();
    }
    /**
     * Call category init page
     */
    public function catInit()
    {
        $this->acObject->catInit();
    }
    /**
     * Call category init page
     */
    public function subInit()
    {
        $this->acObject->subInit();
    }
    /**
     * Make the menus
     *
     * @param array $menuArray
     */
    private function makeMenu($menuArray)
    {
        $this->menus = [
            $menuArray[0] => ['uwcpluginSub' => [
                $this->pluginmessages->text('uwcSub') => FALSE,
                $this->pluginmessages->text('uwcCategories') => "catInit",
                $this->pluginmessages->text('uwcSubcategories') => "subInit",
            ],
            ],
        ];
    }
}
