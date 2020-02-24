<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * MENU
 *
 * Configure, create and print menus
 *
 * @package wikindx\core\navigation
 */
class MENU
{
    /** array */
    public $menus;
    /** object */
    private $smartyMenu;
    /** object */
    private $db;
    /** object */
    private $session;
    /** array */
    private $topLevel = [];
    /** array */
    private $menuSub = [];
    /** array */
    private $wikindx;
    /** array */
    private $res;
    /** array */
    private $search;
    /** array */
    private $text;
    /** array */
    private $admin;
    /** array */
    private $plugin1;
    /** array */
    private $plugin2;
    /** array */
    private $plugin3;
    /** string */
    private $superAdmin;
    /** string */
    private $write;
    /** int */
    private $userId;
    /** string */
    private $multiUser;
    /** string */
    private $userRegistration;
    /** array */
    private $bibliographies;
    /** array */
    private $bookmarkArray;
    /** string */
    private $bookmarkAdd;
    /** string */
    private $lastSolo;
    /** string */
    private $lastThread;
    /** string */
    private $stmt;
    /** string */
    private $lastMulti;
    /** string */
    private $lastMultiMeta;
    /** string */
    private $lastIdeaSearch;
    /** array */
    private $basketList;
    /** string */
    private $importBib;
    /** array */
    private $outputArray = [];
    /** boolean */
    private $resourcesExist = FALSE;
    /** boolean */
    private $metadataExist = FALSE;
    /** boolean */
    private $ideasExist = FALSE;
    /** int */
    private $reduceMenuLevel;
    /** string */
    private $reduceMenuLevelPretext = '';

    /**
     * MENU class
     */
    public function __construct()
    {
        // Keep here the responsibility to including SmartyMenu pulgin because
        include_once(WIKINDX_DIR_COMPONENT_VENDOR . "/smarty/SmartyMenu/SmartyMenu.class.php");

        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->superAdmin = $this->session->getVar("setup_Superadmin");
        $this->smartyMenu = new SmartyMenu();
        $this->write = $this->session->getVar("setup_Write");
        $this->userId = $this->session->getVar("setup_UserId");
        $this->userRegistration = $this->session->getVar("setup_UserRegistration");
        $this->reduceMenuLevel = $this->session->getVar("setup_ReduceMenuLevel");
        if ($this->session->issetVar("setup_ReduceMenuLevelPretext"))
        {
            $this->reduceMenuLevelPretext = $this->session->getVar("setup_ReduceMenuLevelPretext");
        }
        $row = $this->db->selectFirstRow('database_summary', ['databaseSummaryTotalResources',
            'databaseSummaryTotalQuotes', 'databaseSummaryTotalParaphrases', 'databaseSummaryTotalMusings', ]);
        if ($row['databaseSummaryTotalResources'])
        {
            $this->resourcesExist = TRUE;
        }
        // Admin may have turned off metadata subsystem. Default for $this->metadataExist is FALSE in the class constructor
        if ($this->session->getVar('setup_Superadmin')
            &&
            ($row['databaseSummaryTotalQuotes'] || $row['databaseSummaryTotalParaphrases'] || $row['databaseSummaryTotalMusings']))
        {
            $this->metadataExist = TRUE;
            if ($this->setIdeasCondition())
            {
                if ($this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId')))
                {
                    $this->ideasExist = TRUE;
                }
            }
        }
        elseif ($this->session->getVar('setup_Superadmin'))
        {
            if ($this->setIdeasCondition())
            {
                if ($this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId')))
                {
                    $this->ideasExist = TRUE;
                }
            }
        }
        elseif ((!WIKINDX_METADATA_ALLOW))
        {
            if ((WIKINDX_METADATA_USERONLY) && $this->session->getVar('setup_UserId'))
            {
                if ($row['databaseSummaryTotalQuotes'] || $row['databaseSummaryTotalParaphrases'] || $row['databaseSummaryTotalMusings'])
                {
                    $this->metadataExist = TRUE;
                }
                if ($this->setIdeasCondition())
                {
                    if ($this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId')))
                    {
                        $this->ideasExist = TRUE;
                    }
                }
            }
        }
        elseif (WIKINDX_METADATA_ALLOW)
        {
            if ($row['databaseSummaryTotalQuotes'] || $row['databaseSummaryTotalParaphrases'] || $row['databaseSummaryTotalMusings'])
            {
                $this->metadataExist = TRUE;
            }
            if ($this->setIdeasCondition())
            {
                if ($this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId')))
                {
                    $this->ideasExist = TRUE;
                }
            }
        }
        $this->bookmarkArray = $this->session->getArray('bookmark');
        if (array_key_exists('DisplayAdd', $this->bookmarkArray))
        {
            $stateArray[] = $this->bookmarkAdd = $this->bookmarkArray['DisplayAdd'];
        }
        if (array_key_exists('View', $this->bookmarkArray))
        {
            $stateArray[] = $this->bookmarkView = $this->bookmarkArray['View'];
        }
        $stateArray[] = $this->multiUser = WIKINDX_MULTIUSER;
        $stateArray[] = $this->bibliographies = $this->session->getVar("setup_Bibliographies");
        $stateArray[] = $this->lastSolo = $this->session->getVar("sql_LastSolo");
        $stateArray[] = $this->stmt = $this->session->getVar("sql_ListStmt");
        $stateArray[] = $this->lastMulti = $this->session->getVar("sql_LastMulti");
        $stateArray[] = $this->lastThread = $this->session->getVar("sql_LastThread");
        $stateArray[] = $this->lastMultiMeta = $this->session->getVar("sql_LastMultiMeta");
        $stateArray[] = $this->lastIdeaSearch = $this->session->getVar("sql_LastIdeaSearch");
        $stateArray[] = $this->basketList = $this->session->getVar("basket_List");
        $stateArray[] = $this->importBib = $this->session->getVar("setup_ImportBib");
        $state = base64_encode(serialize($stateArray));
        if (($state == $this->session->getVar('menu_state')) && ($menu = $this->session->getVar('menu_menu', FALSE) !== FALSE))
        {
            GLOBALS::setTplVar('menu', $menu);
        }
        else
        {
            $this->session->setVar('menu_state', $state);
        }
    }
    /**
     * print menus
     *
     * submenus have to be created before the menu item is
     */
    public function menus()
    {
        $this->configure();
        $this->smartyMenu->initMenu($subMenu);
        // Check for plug-in modules
        $this->menuInsert();
        $this->createMenuArray($this->wikindx, 'wikindx', $wikindxSub);
        array_push($this->menuSub, $wikindxSub);
        unset($wikindxSub);
        if (isset($this->bookmark))
        {
            $this->createMenuArray($this->bookmark, 'bookmark', $bookmarkSub);
            array_push($this->menuSub, $bookmarkSub);
        }
        unset($bookmarkSub);
        if ($this->resourcesExist || $this->write)
        {
            $this->createMenuArray($this->res, 'resource', $resourceSub);
            array_push($this->menuSub, $resourceSub);
        }
        unset($resourceSub);
        if ($this->resourcesExist)
        {
            $this->createMenuArray($this->search, 'search', $searchSub);
            array_push($this->menuSub, $searchSub);
        }
        unset($searchSub);
        if ($this->resourcesExist && $this->metadataExist)
        {
            $this->createMenuArray($this->text, 'metadata', $metadataSub);
            array_push($this->menuSub, $metadataSub);
        }
        unset($metadataSub);
        // If not admin, don't display admin menu
        if ($this->superAdmin)
        {
            $this->createMenuArray($this->admin, 'admin', $adminSub);
            array_push($this->menuSub, $adminSub);
        }
        if (count($this->plugin1) > 1)
        {
            $this->createMenuArray($this->plugin1, 'plugin1', $plugin1Sub);
            array_push($this->menuSub, $plugin1Sub);
        }
        unset($plugin1Sub);
        if (count($this->plugin2) > 1)
        {
            $this->createMenuArray($this->plugin2, 'plugin2', $plugin2Sub);
            array_push($this->menuSub, $plugin2Sub);
        }
        unset($plugin2Sub);
        if (count($this->plugin3) > 1)
        {
            $this->createMenuArray($this->plugin3, 'plugin3', $plugin3Sub);
            array_push($this->menuSub, $plugin3Sub);
        }
        unset($plugin3Sub);
        // Top level of above subMenus
        $this->smartyMenu->initMenu($menu);
        foreach ($this->topLevel as $menuArray)
        {
            $this->smartyMenu->initItem($item);
            $this->smartyMenu->setItemText($item, $menuArray['key']);
            $this->smartyMenu->setItemLink($item, $menuArray['value']);
            // Add in subMenu
            $menuSub = array_shift($this->menuSub);
            $this->smartyMenu->setItemSubmenu($item, $menuSub);
            $this->smartyMenu->addMenuItem($menu, $item);
        }
        GLOBALS::setTplVar('menu', $menu);
        $this->session->setVar('menu_menu', $menu);
        /** $this->menus is public and available to, for example, the admin to remove messages etc.
         */
        foreach (['wikindx', 'res', 'search', 'text', 'admin', 'plugin1', 'plugin2', 'plugin3'] as $menuItem)
        {
            if (is_array($this->{$menuItem}) and (count($this->{$menuItem}) > 1))
            {
                $this->menus[$menuItem] = $this->{$menuItem};
            }
        }
    }
    /**
     * no drop-down menu system
     *
     * Produce standard hyperlinks in body of page
     */
    public function noMenu()
    {
        $vars = GLOBALS::getVars();
        
        $type = "";
        if (array_key_exists('method', $vars))
        {
            $type = trim($vars['method']);
        }
        $type = ($type == "") ? "wikindx" : $type;
        
        $messages = FACTORY_MESSAGES::getInstance();
        $this->configure();
        
        // Check for plug-in modules
        $this->menuInsert();
        if ($type == 'wikindx')
        {
            GLOBALS::setTplVar('heading', 'Wikindx');
        }
        else
        {
            $content = $messages->text("menu", $type);
            $content = ($content == $type) ? "" : $content;
            GLOBALS::setTplVar('heading', $content);
        }
            
        if (property_exists($this, $type))
        {
            array_shift($this->$type);
            foreach ($this->$type as $key => $value)
            {
                $this->outputArray[] = $this->subNoMenu($key, $value);
            }
                
            GLOBALS::addTplVar('content', implode(BR, $this->outputArray));
        }
    }

    /**
     * no drop-down submenu system
     *
     * Produce standard hyperlinks in body of page
     */
    public function noSubMenu()
    {
        $vars = GLOBALS::getVars();
        $array = unserialize(base64_decode($vars['array']));
        //		$this->configure();
        // Check for plug-in modules
        //		$this->menuInsert();
        $messages = FACTORY_MESSAGES::getInstance();
        $content = $messages->text("menuReduced", $vars['method']);
        $content = ($content == $vars['method']) ? "" : $content;
        GLOBALS::setTplVar('heading', $content);
        foreach ($array as $key => $value)
        {
            $this->outputArray[] = $this->subNoMenu($key, $value);
        }
        GLOBALS::addTplVar('content', implode(BR, $this->outputArray));
    }
    /**
     * configure menus
     */
    private function configure()
    {
        $messages = FACTORY_MESSAGES::getInstance();
        // Build dummy plugin array
        $this->plugin1 = [$messages->text("menu", "plugin1") => 'index.php?action=noMenu&method=plugin1'];
        $this->plugin2 = [$messages->text("menu", "plugin2") => 'index.php?action=noMenu&method=plugin2'];
        $this->plugin3 = [$messages->text("menu", "plugin3") => 'index.php?action=noMenu&method=plugin3'];
        // Build arrays of menus items.  First element of array name is main menu item name, subsequent elements are the names
        // of the submenu with its
        // hyperlink.  This provides easy access for both building CSS menus and for displaying links for non-CSS drop-down
        // web browsers.
        $this->wikindx = [
            'Wikindx' => 'index.php?action=noMenu&method=wikindx',
            $messages->text("menu", "home") => 'index.php',
        ];
        if ($this->session->getVar('setup_News'))
        {
            $this->wikindx[$messages->text("menu", "news")] = 'index.php?action=news_NEWS_CORE&method=viewNews';
        }
        if ($this->bibliographies && $this->write)
        {
            $this->wikindx[$messages->text("menu", "bibs")] = 'index.php?action=bibliography_CHOOSEBIB_CORE';
        }
        if ($this->write)
        {
	        $this->wikindx[$messages->text("menu", "myWikindx")] = 'index.php?action=usersgroups_MYWIKINDX_CORE';
	    }
	    else
	    {
	        $this->wikindx[$messages->text("menu", "prefs")] = 'index.php?action=usersgroups_PREFERENCES_CORE';
	    }
        if ($this->userId)
        {
            $this->wikindx['statisticsSub'] = [
                $messages->text("menu", "statisticsSub") => FALSE,
                $messages->text("menu", "statisticsTotals") => 'index.php?action=statistics_STATS_CORE&method=totals',
                $messages->text("menu", "statisticsKeywords") => 'index.php?action=statistics_STATS_CORE&method=keywords',
                $messages->text("menu", "statisticsYears") => 'index.php?action=statistics_STATS_CORE&method=years',
                $messages->text("menu", "statisticsAllCreators") => 'index.php?action=statistics_STATS_CORE&method=allCreators',
                $messages->text("menu", "statisticsMainCreators") => 'index.php?action=statistics_STATS_CORE&method=mainCreators',
                $messages->text("menu", "statisticsPublishers") => 'index.php?action=statistics_STATS_CORE&method=publishers',
                $messages->text("menu", "statisticsCollections") => 'index.php?action=statistics_STATS_CORE&method=collections',
                $messages->text("menu", "statisticsUsers") => 'index.php?action=statistics_STATS_CORE&method=users',
            ];
        }
        elseif (WIKINDX_DISPLAY_STATISTICS)
        {
            $this->wikindx['statisticsSub'] = [
                $messages->text("menu", "statisticsSub") => FALSE,
                $messages->text("menu", "statisticsTotals") => 'index.php?action=statistics_STATS_CORE&method=totals',
                $messages->text("menu", "statisticsKeywords") => 'index.php?action=statistics_STATS_CORE&method=keywords',
                $messages->text("menu", "statisticsYears") => 'index.php?action=statistics_STATS_CORE&method=years',
                $messages->text("menu", "statisticsAllCreators") => 'index.php?action=statistics_STATS_CORE&method=allCreators',
                $messages->text("menu", "statisticsMainCreators") => 'index.php?action=statistics_STATS_CORE&method=mainCreators',
                $messages->text("menu", "statisticsPublishers") => 'index.php?action=statistics_STATS_CORE&method=publishers',
                $messages->text("menu", "statisticsCollections") => 'index.php?action=statistics_STATS_CORE&method=collections',
            ];
            if (WIKINDX_DISPLAY_USER_STATISTICS)
            {
                $this->wikindx['statisticsSub'][$messages->text("menu", "statisticsUsers")] = 'index.php?action=statistics_STATS_CORE&method=users';
            }
        }
        elseif ($this->multiUser && $this->userRegistration && !$this->userId)
        {
            $this->wikindx[$messages->text("menu", "statistics")] = 'index.php?action=statistics_STATS_CORE&method=totals';
        }
        elseif ($this->multiUser && !$this->userId)
        {
            $this->wikindx[$messages->text("menu", "statistics")] = 'index.php?action=statistics_STATS_CORE&method=totals';
        }
        if ($this->write)
        {
            // On the first run after a fresh install this screen is displayed immediatly
            // and these two options are not yet set, so we avoid to check the READONLY mode strictly.
        	if (defined('WIKINDX_DENY_READONLY') && defined('WIKINDX_READ_ONLY_ACCESS'))
        	{
            	if (!WIKINDX_DENY_READONLY && WIKINDX_READ_ONLY_ACCESS) 
            	{
    	            $this->wikindx[$messages->text("menu", "readOnly")] = 'index.php?action=readOnly';
    	        }
	        }
            $this->wikindx[$messages->text("menu", "logout")] = 'index.php?action=initLogon';
        }
        else
        {
            $this->wikindx[$messages->text("menu", "userLogon")] = 'index.php?action=initLogon';
        }
    	if ($this->multiUser && $this->userRegistration && WIKINDX_MAIL_USE && !$this->userId)
        {
            $this->wikindx[$messages->text("menu", "register")] = 'index.php?action=initRegisterUser';
        }
        $this->wikindx[$messages->text("menu", "about")] = 'index.php?action=help_ABOUT_CORE';
        list($collBrowseSub, $collEditSub) = $this->collectionArray();
        list($pubBrowseSub, $pubEditSub) = $this->publisherArray();
        if ($this->write)
        {
            $this->res = [
                $messages->text("menu", "res") => 'index.php?action=noMenu&method=res',
                $messages->text("menu", "new") => 'index.php?action=resource_RESOURCEFORM_CORE',
                'editSub' => [
                    $messages->text("menu", "editSub") => FALSE,
                    $messages->text("menu", "creator") => 'index.php?action=edit_EDITCREATOR_CORE',
                    $collEditSub,
                    $pubEditSub,
                    $messages->text("menu", "keyword") => 'index.php?action=edit_EDITKEYWORD_CORE',
                ],
                'bookmarkSub' => [
                    $messages->text("menu", "bookmarkSub") => FALSE,
                ],
                $messages->text("menu", "randomResource") => 'index.php?action=resource_RESOURCEVIEW_CORE&method=random',
            ];
            if (empty($collEditSub))
            {
                unset($this->res['editSub'][0]);
            }
        }
        else
        {
            $this->res = [
                $messages->text("menu", "res") => 'index.php?action=noMenu&method=res',
                'bookmarkSub' => [
                    $messages->text("menu", "bookmarkSub") => FALSE,
                ],
                $messages->text("menu", "randomResource") => 'index.php?action=resource_RESOURCEVIEW_CORE&method=random',
            ];
        }
        if (empty($collBrowseSub))
        {
            unset($this->res['browseSub'][0]);
        }
        if (empty($pubBrowseSub))
        {
            unset($this->res['browseSub'][1]);
        }
        $requireBookmark = FALSE;
        if ($this->bookmarkAdd)
        {
            $found = FALSE;
            for ($i = 1; $i <= 6; $i++)
            {
                if (array_key_exists($i . "_name", $this->bookmarkArray) && array_key_exists($i . "_id", $this->bookmarkArray)
                    && $this->bookmarkArray[$i . "_id"] == $this->lastSolo && ($this->bookmarkView == 'solo'))
                {
                    $found = TRUE;

                    break;
                }
                elseif (array_key_exists($i . "_name", $this->bookmarkArray) &&
                    array_key_exists($i . "_multi", $this->bookmarkArray) && ($this->bookmarkView == 'multi'))
                {
                    $encodedSql = base64_encode($this->stmt);
                    $bk = UTF8::mb_explode('|', $this->bookmarkArray[$i . "_multi"]); // statement, multi, listParams
                    if ($bk[0] == $encodedSql)
                    {
                        $found = TRUE;

                        break;
                    }
                }
            }
            if (!$found)
            {
                $this->res['bookmarkSub'][$messages->text("menu", "bookmarkAdd")] = 'index.php?action=bookmarks_BOOKMARK_CORE';
                $requireBookmark = TRUE;
            }
        }
        if (count($this->bookmarkArray) > 2)
        {
            $this->res['bookmarkSub'][$messages->text("menu", "bookmarkDelete")] =
                'index.php?action=bookmarks_BOOKMARK_CORE&method=deleteInit';
            for ($i = 1; $i <= 20; $i++)
            {
                if (array_key_exists($i . "_name", $this->bookmarkArray) &&
                    array_key_exists($i . "_id", $this->bookmarkArray))
                {
                    $this->res['bookmarkSub'][stripslashes($this->bookmarkArray[$i . "_name"])] =
                    "index.php?action=resource_RESOURCEVIEW_CORE&id=" . $this->bookmarkArray[$i . "_id"];
                }
                elseif (array_key_exists($i . "_name", $this->bookmarkArray) &&
                    array_key_exists($i . "_multi", $this->bookmarkArray))
                {
                    $this->res['bookmarkSub'][stripslashes($this->bookmarkArray[$i . "_name"])] =
                    'index.php?action=bookmarks_BOOKMARK_CORE&method=multiView&id=' . $i;
                }
            }
            $requireBookmark = TRUE;
        }
        if (!$requireBookmark)
        {
            unset($this->res['bookmarkSub']);
        }
        if ($this->write)
        {
            $this->search = [
                $messages->text("menu", "search") => 'index.php?action=noMenu&method=search',
                $messages->text("menu", "quickSearch") => 'index.php?action=list_QUICKSEARCH_CORE',
                $messages->text("menu", "advancedSearch") => 'index.php?action=list_SEARCH_CORE',
                'listSub' => [
                    $messages->text("menu", "listSub") => FALSE,
                    $messages->text("menu", "listCreator") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=creator',
                    $messages->text("menu", "listTitle") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=title',
                    $messages->text("menu", "listPublisher") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=publisher',
                    $messages->text("menu", "listYear") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=year',
                    $messages->text("menu", "listTimestamp") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=timestamp',
                    $messages->text("menu", "listPopularity") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=popularityIndex',
                    $messages->text("menu", "listViews") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=viewsIndex',
                    $messages->text("menu", "listDownloads") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=downloadsIndex',
                    $messages->text("menu", "listMaturity") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=maturityIndex',
                ],
                'browseSub' => [
                    $messages->text("menu", "browseSub") => FALSE,
                    $messages->text("menu", "browseType") => 'index.php?action=browse_BROWSETYPE_CORE',
                    $messages->text("menu", "browseCreator") => 'index.php?action=browse_BROWSECREATOR_CORE',
                    $messages->text("menu", "browseCited") => 'index.php?action=browse_BROWSECITED_CORE',
                    $collBrowseSub,
                    $pubBrowseSub,
                    $messages->text("menu", "browseYear") => 'index.php?action=browse_BROWSEYEAR_CORE',
                    $messages->text("menu", "browseKeyword") => 'index.php?action=browse_BROWSEKEYWORD_CORE',
                    $messages->text("menu", "browseCategory") => 'index.php?action=browse_BROWSECATEGORY_CORE',
                    $messages->text("menu", "browseSubcategory") => 'index.php?action=browse_BROWSESUBCATEGORY_CORE',
                    $messages->text("menu", "browseLanguage") => 'index.php?action=browse_BROWSELANGUAGE_CORE',
                    $messages->text("menu", "browseBibliography") => 'index.php?action=browse_BROWSEBIBLIOGRAPHY_CORE',
                    $messages->text("menu", "browseUser") => 'index.php?action=browse_BROWSEUSER_CORE&method=user',
                    $messages->text("menu", "browseDept") => 'index.php?action=browse_BROWSEUSER_CORE&method=department',
                    $messages->text("menu", "browseInst") => 'index.php?action=browse_BROWSEUSER_CORE&method=institution',
                ],
                $messages->text("menu", "categoryTree") => 'index.php?action=browse_CATEGORYTREE_CORE',
            ];
            $userTagsObject = FACTORY_USERTAGS::getInstance();
            $userTags = $userTagsObject->grabAll($this->session->getVar('mywikindx_Bibliography_use'));
            if (!empty($userTags))
            {
                $this->search['browseSub'][$messages->text("menu", "browseUserTags")] = 'index.php?action=browse_BROWSEUSERTAGS_CORE';
            }
        }
        else
        {
            $this->search = [
                $messages->text("menu", "search") => 'index.php?action=noMenu&method=search',
                $messages->text("menu", "quickSearch") => 'index.php?action=list_QUICKSEARCH_CORE',
                $messages->text("menu", "advancedSearch") => 'index.php?action=list_SEARCH_CORE',
                'listSub' => [
                    $messages->text("menu", "listSub") => FALSE,
                    $messages->text("menu", "listCreator") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=creator',
                    $messages->text("menu", "listTitle") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=title',
                    $messages->text("menu", "listPublisher") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=publisher',
                    $messages->text("menu", "listYear") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=year',
                    $messages->text("menu", "listTimestamp") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=timestamp',
                    $messages->text("menu", "listPopularity") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=popularityIndex',
                    $messages->text("menu", "listViews") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=viewsIndex',
                    $messages->text("menu", "listDownloads") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=downloadsIndex',
                    $messages->text("menu", "listMaturity") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=maturityIndex',
                ],
                'browseSub' => [
                    $messages->text("menu", "browseSub") => FALSE,
                    $messages->text("menu", "browseType") => 'index.php?action=browse_BROWSETYPE_CORE',
                    $messages->text("menu", "browseCreator") => 'index.php?action=browse_BROWSECREATOR_CORE',
                    $messages->text("menu", "browseCited") => 'index.php?action=browse_BROWSECITED_CORE',
                    $collBrowseSub,
                    $pubBrowseSub,
                    $messages->text("menu", "browseYear") => 'index.php?action=browse_BROWSEYEAR_CORE',
                    $messages->text("menu", "browseKeyword") => 'index.php?action=browse_BROWSEKEYWORD_CORE',
                    $messages->text("menu", "browseCategory") => 'index.php?action=browse_BROWSECATEGORY_CORE',
                    $messages->text("menu", "browseSubcategory") => 'index.php?action=browse_BROWSESUBCATEGORY_CORE',
                    $messages->text("menu", "browseLanguage") => 'index.php?action=browse_BROWSELANGUAGE_CORE',
                    $messages->text("menu", "browseUser") => 'index.php?action=browse_BROWSEUSER_CORE&method=user',
                    $messages->text("menu", "browseDept") => 'index.php?action=browse_BROWSEUSER_CORE&method=department',
                    $messages->text("menu", "browseInst") => 'index.php?action=browse_BROWSEUSER_CORE&method=institution',
                ],
                $messages->text("menu", "categoryTree") => 'index.php?action=browse_CATEGORYTREE_CORE',
            ];
        }
        // There is no collection, an empty menu entry can be injected.
        // We remove it before rendering.
        foreach ($this->search['browseSub'] as $k => $v)
        {
            if ($v === NULL)
            {
                unset($this->search['browseSub'][$k]);
            }
        }
        if (!$this->metadataExist)
        {
            unset($this->search[$messages->text("menu", "selectMeta")]);
            unset($this->search[$messages->text("menu", "searchMeta")]);
        }
        $this->text = [
            $messages->text("menu", "text") => 'index.php?action=noMenu&method=text',
            'randomSub' => [
                $messages->text("menu", "randomSub") => FALSE,
                $messages->text("menu", "randomQuotes") => 'index.php?action=metadata_RANDOMMETADATA_CORE&method=randomQuote',
                $messages->text("menu", "randomParaphrases") => 'index.php?action=metadata_RANDOMMETADATA_CORE&method=randomParaphrase',
                $messages->text("menu", "randomMusings") => 'index.php?action=metadata_RANDOMMETADATA_CORE&method=randomMusing',
                $messages->text("menu", "randomIdeas") => 'index.php?action=metadata_RANDOMMETADATA_CORE&method=randomIdea',
            ],
            $messages->text("menu", "addIdea") => 'index.php?action=ideas_IDEAS_CORE&method=ideaEdit',
            $messages->text("menu", "listIdeas") => 'index.php?action=ideas_IDEAS_CORE&method=ideaList',
            $messages->text("menu", "browseKeyword") => 'index.php?action=browse_BROWSEKEYWORD_CORE&metadata=1',
        ];
        if (!$this->ideasExist)
        {
            unset($this->text[$messages->text("menu", "listIdeas")]);
            unset($this->text['randomSub'][$messages->text("menu", "randomIdeas")]);
        }
        // readOnly user
        if (!$this->write)
        {
            unset($this->text['randomSub'][$messages->text("menu", "randomMusings")]);
            unset($this->text['randomSub'][$messages->text("menu", "randomIdeas")]);
            unset($this->text[$messages->text("menu", "addIdea")]);
            unset($this->text[$messages->text("menu", "listIdeas")]);
        }
        if ($this->lastThread && $this->ideasExist)
        {
            $this->text[$messages->text("menu", "lastIdea")] = 'index.php?action=ideas_IDEAS_CORE&method=threadView&resourcemetadataId=' . $this->lastThread;
        }
        if ($this->lastSolo)
        {
            $this->res[$messages->text("menu", "lastSolo")] = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->lastSolo;
        }
        if ($this->lastMulti)
        {
            $this->res[$messages->text("menu", "lastMulti")] = 'index.php?' . $this->lastMulti . '&type=lastMulti';
        }
        $basket = unserialize($this->basketList);
        if ($this->basketList && !empty($basket))
        {
            $this->res['basketSub'] = [
                $messages->text("menu", "basketSub") => FALSE,
                $messages->text("menu", "basketView") => 'index.php?action=basket_BASKET_CORE&method=view',
                $messages->text("menu", "basketDelete") => 'index.php?action=basket_BASKET_CORE&method=delete',
            ];
        }
        if ($this->lastMultiMeta && $this->metadataExist)
        {
            $this->text[$messages->text("menu", "lastMultiMeta")] = 'index.php?action=lastMultiMeta';
            $this->text[$messages->text("menu", "rtfexp")] = 'index.php?action=metaExportRtf';
        }
        if ($this->lastIdeaSearch && $this->metadataExist)
        {
            $this->text[$messages->text("menu", "lastIdeaSearch")] = "index.php?action=list_SEARCH_CORE&method=reprocess&type=displayIdeas";
        }
        if ($this->write)
        {
            if (WIKINDX_MAX_PASTE || $this->superAdmin)
            {
                $this->res[$messages->text("menu", "pasteBibtex")] = 'index.php?action=import_PASTEBIBTEX_CORE';
            }
            if ($this->importBib && !$this->superAdmin)
            {
                $this->res[$messages->text("menu", "importBibtex")] = 'index.php?action=import_BIBTEXFILE_CORE';
            }
        }
        else
        { // Read Only
            unset($this->res['browseSub'][$messages->text("menu", "browseBibliography")]);
        }
        $this->admin = [
            $messages->text("menu", "admin") => 'index.php?action=noMenu&method=admin',
            $messages->text("menu", "conf") => 'index.php?action=admin_CONFIGURE_CORE',
            $messages->text("menu", "news") => 'index.php?action=news_NEWS_CORE&method=init',
            $messages->text("menu", "categories") => 'index.php?action=admin_ADMINCATEGORIES_CORE&method=catInit',
            $messages->text("menu", "subcategories") => 'index.php?action=admin_ADMINCATEGORIES_CORE&method=subInit',
            $messages->text("menu", "custom") => 'index.php?action=admin_ADMINCUSTOM_CORE&method=init',
            'userSub' => [
                $messages->text("menu", "userSub") => FALSE,
                $messages->text("menu", "userAdd") => 'index.php?action=admin_ADMINUSER_CORE&method=addInit',
                $messages->text("menu", "userEdit") => 'index.php?action=admin_ADMINUSER_CORE&method=editInit',
                $messages->text("menu", "userDelete") => 'index.php?action=admin_ADMINUSER_CORE&method=deleteInit',
                $messages->text("menu", "userBlock") => 'index.php?action=admin_ADMINUSER_CORE&method=blockInit',
                $messages->text("menu", "userRegistration") => 'index.php?action=admin_ADMINUSER_CORE&method=registrationInit',
            ],
            'keywordSub' => [
                $messages->text("menu", "keywordSub") => FALSE,
                $messages->text("menu", "keywordEdit") => 'index.php?action=admin_ADMINKEYWORD_CORE&method=editInit',
                $messages->text("menu", "keywordMerge") => 'index.php?action=admin_ADMINKEYWORD_CORE&method=mergeInit',
                $messages->text("menu", "keywordDelete") => 'index.php?action=admin_ADMINKEYWORD_CORE&method=deleteInit',
            ],
            'creatorSub' => [
                $messages->text("menu", "creatorSub") => FALSE,
                $messages->text("menu", "creatorMerge") => 'index.php?action=admin_ADMINCREATOR_CORE&method=mergeInit',
                $messages->text("menu", "creatorGroup") => 'index.php?action=admin_ADMINCREATOR_CORE&method=groupInit',
                $messages->text("menu", "creatorUngroup") => 'index.php?action=admin_ADMINCREATOR_CORE&method=ungroupInit',
            ],
            $messages->text("menu", "delete") => 'index.php?action=admin_DELETERESOURCE_CORE',
            $messages->text("menu", "importBibtex") => 'index.php?action=import_BIBTEXFILE_CORE',
        ];
        if ($this->superAdmin)
        {
            $this->admin[$messages->text("menu", "components")] = 'index.php?action=admin_ADMINCOMPONENTS_CORE';
        }
        $imagesExists = FALSE;
        if (file_exists(WIKINDX_DIR_DATA_IMAGES))
        {
            $open_dir = opendir('.' . DIRECTORY_SEPARATOR . WIKINDX_DIR_DATA_IMAGES . DIRECTORY_SEPARATOR);
            while ($object = readdir($open_dir))
            {
                if ($object != "." && $object != "..")
                {
                    $ext = mb_strtolower(pathinfo('.' . DIRECTORY_SEPARATOR . WIKINDX_DIR_DATA_IMAGES . DIRECTORY_SEPARATOR . $object, PATHINFO_EXTENSION));
                    if (($ext == 'jpeg') || ($ext == 'jpg') || ($ext == 'gif') || ($ext == 'png'))
                    {
                        $imagesExists = TRUE;

                        break;
                    }
                }
            }
            closedir($open_dir);
            if ($imagesExists)
            {
                $this->admin[$messages->text("menu", "images")] = 'index.php?action=admin_DELETEIMAGES_CORE';
            }
        }
        if ((WIKINDX_QUARANTINE) && $this->checkQuarantine())
        {
            $this->admin[$messages->text("menu", "quarantine")] = 'index.php?action=list_LISTSOMERESOURCES_CORE&method=quarantineProcess';
        }
        if (!$this->multiUser)
        {
            unset($this->admin['userSub']);
        }
        // Disable menu items if there are not yet resources
        if (!$this->resourcesExist)
        {
            unset($this->admin[$messages->text("menu", "delete")]);
            unset($this->admin['keywordSub']);
            unset($this->admin['creatorSub']);
            unset($this->res['searchSub']);
            if ($this->write)
            {
                unset($this->res['editSub']);
            }
            unset($this->res['browseSub']);
            unset($this->res[$messages->text("menu", "categoryTree")]);
            unset($this->res[$messages->text("menu", "randomResource")]);
            unset($this->wikindx[$messages->text("menu", "statistics")]);
        }
        // Remove 'edit' array from resource array if non-admins not allowed to edit
        if ($this->resourcesExist && $this->write)
        { // if no resources, editSub does not exist anyway
            if (!WIKINDX_GLOBAL_EDIT && !$this->superAdmin)
            {
                array_splice($this->res, array_search('editSub', array_keys($this->res)), 1);
            }
        }
    }
    /**
     * Insert available modules into menu system
     */
    private function menuInsert()
    {
        $menuHeadings = ["wikindx", "res", "search", "text", "admin", "plugin1", "plugin2", "plugin3"];
        
        include_once("core/modules/LOADEXTERNALMODULES.php");
        $loadmodules = new LOADEXTERNALMODULES();
        $moduleList = $loadmodules->readPluginsDirectory();
        
        foreach ($moduleList as $dirName)
        {
            include_once(WIKINDX_DIR_COMPONENT_PLUGINS . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR . 'index.php');
            // class name must be in the form $dirName . MODULE
            $module = $dirName . "_MODULE";
            if (!class_exists($module))
            {
                continue;
            }
            $class = new $module(TRUE);
            // Check if the plugin permission and the auth level permits to display its menu
            // Read AUTHORIZE.isPluginExecutionAuthorised for level descriptions
            if (!isset($class->authorize))
            {
                continue; // don't write to menu
            }
            if (($class->authorize == 2) && !$this->superAdmin)
            {
                continue; // don't write to menu
            }
            if (($class->authorize == 1) && (!$this->superAdmin || !$this->write))
            {
                continue; // don't write to menu
            }
            // Check we have valid menu plugins, if so, insert into menu
            if (isset($class->menus))
            {
                foreach ($class->menus as $menu => $array)
                {
                    if ((array_search($menu, $menuHeadings) !== FALSE) && is_array($array))
                    {
                        foreach ($array as $entry => $action)
                        {
                            if ($entry && is_array($action))
                            {
                                $subMenu = [];
                                foreach ($action as $key => $value)
                                {
                                    if ($value === FALSE)
                                    {
                                        $subMenu[$key] = $value;
                                    }
                                    elseif (is_array($value))
                                    {
                                        foreach ($value as $subKey => $subValue)
                                        {
                                            if ($subValue === FALSE)
                                            {
                                                $subMenu[$key][$subKey] = $subValue;
                                            }
                                            elseif (method_exists($class, $subValue))
                                            {
                                                $subMenu[$key][$subKey] = 'index.php?action=' . $dirName . '_' . $subValue;
                                            }
                                        }
                                    }
                                    elseif (method_exists($class, $value))
                                    {
                                        $subMenu[$key] = 'index.php?action=' . $dirName . '_' . $value;
                                    }
                                }
                                if (!empty($subMenu))
                                {
                                    $this->{$menu}[$entry] = $subMenu;
                                }
                            }
                            elseif ($entry && $action && method_exists($class, $action))
                            {
                                $this->{$menu}[$entry] = 'index.php?action=' . $dirName . '_' . $action;
                            }
                        }
                    }
                }
            }
        }
    }
    /**
     * Create a sub-sub menu
     *
     * @param array $link
     * @param string $menuSub Reference to
     */
    private function doSubSubMenu($link, &$menuSub)
    {
        $k = 1;
        foreach ($link as $subText => $subLink)
        {
            if ($k == 1)
            {
                $this->smartyMenu->setItemText($menuSub, $subText);
                $this->smartyMenu->setItemLink($menuSub, $subLink);
                $this->smartyMenu->initItem($itemSubSub);
                $this->smartyMenu->initMenu($subSub);
            }
            else
            {
                $this->smartyMenu->setItemText($itemSubSub, $subText);
                $this->smartyMenu->setItemLink($itemSubSub, $subLink);
                $this->smartyMenu->addMenuItem($subSub, $itemSubSub);
            }

            $k++;
        }
        if (isset($subSub))
        {
            $this->smartyMenu->setItemSubmenu($menuSub, $subSub);
        }
    }
    /**
     * Create a sub menu
     *
     * @param array $link
     * @param string $menu Reference to
     */
    private function doSubMenu($link, &$menu)
    {
        $k = 1;
        foreach ($link as $subText => $subLink)
        {
            if ($k == 1)
            {
                $this->smartyMenu->setItemText($item, $subText);
                $this->smartyMenu->setItemLink($item, $subLink);
                $this->smartyMenu->initItem($itemSub);
                $this->smartyMenu->initMenu($sub);
            }
            else
            {
                if (is_array($subLink))
                {
                    if ($this->reduceMenuLevel == 1)
                    { // remove one submenu level
                        $index = 0;
                        foreach ($subLink as $subText1 => $subLink1)
                        {
                            if ($index)
                            {
                                $this->smartyMenu->setItemText($itemSub, $this->reduceMenuLevelPretext . $subText1);
                            }
                            else
                            {
                                $this->smartyMenu->setItemText($itemSub, $subText1);
                            }
                            $this->smartyMenu->setItemLink($itemSub, $subLink1);
                            $this->smartyMenu->addMenuItem($sub, $itemSub);
                            ++$index;
                        }
                    }
                    else
                    { // keep all submenus
                        $this->doSubSubMenu($subLink, $itemSub);
                        $this->smartyMenu->addMenuItem($sub, $itemSub);
                    }
                }
                else
                {
                    $this->smartyMenu->setItemText($itemSub, $subText);
                    $this->smartyMenu->setItemLink($itemSub, $subLink);
                    $this->smartyMenu->addMenuItem($sub, $itemSub);
                }
                unset($itemSub);
            }

            $k++;
        }
        $this->smartyMenu->setItemSubmenu($item, $sub);
        $this->smartyMenu->addMenuItem($menu, $item);
    }
    /**
     * Create a menu array
     *
     * @param array $array
     * @param string $label
     * @param string $menu Reference to
     */
    private function createMenuArray($array, $label, &$menu)
    {
        $k = 1;
        foreach ($array as $text => $link)
        {
            if ($k == 1)
            {
                // Remove top level of menu array in readiness for Top Level of menu below
                $this->topLevel[$label] = ['key' => $text, 'value' => $link];
                $this->smartyMenu->initItem($item);
            }
            else
            {
                if (is_array($link))
                {
                    if ($this->reduceMenuLevel == 2)
                    { // remove all submenu levels
                        $index = 0;
                        $subArray = [];
                        foreach ($link as $subText => $subLink)
                        {
                            if (!$index)
                            {
                                $this->smartyMenu->setItemText($item, $subText);
                                $title = $subText;
                                ++$index;

                                continue;
                            }
                            $subArray[$subText] = $subLink;
                            ++$index;
                        }
                        if (!empty($subArray))
                        {
                            $serial = base64_encode(serialize($subArray));
                            $this->smartyMenu->setItemLink($item, "index.php?action=noSubMenu&method=$title&array=$serial");
                            $this->smartyMenu->addMenuItem($menu, $item);
                        }
                    }
                    else
                    {
                        $this->doSubMenu($link, $menu);
                    }
                }
                else
                {
                    $this->smartyMenu->setItemText($item, $text);
                    $this->smartyMenu->setItemLink($item, $link);
                    $this->smartyMenu->addMenuItem($menu, $item);
                }
            }

            $k++;
        }
    }
    /**
     * Elements of noMenu display -- with subMenus and subSubMenus
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    private function subNoMenu($key, $value)
    {
        $spacing = '&nbsp;&nbsp;&nbsp;&nbsp;';
        $pString = '';
        if (is_array($value))
        { // sub menu
            $index = 0;
            foreach ($value as $subKey => $subValue)
            {
                if (!$index)
                {
                    if (is_array($subValue))
                    {
                        $subIndex = 0;
                        foreach ($subValue as $subSubKey => $subSubValue)
                        {
                            if (!$subIndex)
                            {
                                $pString .= BR . $spacing . $subSubKey;
                                ++$subIndex;

                                continue;
                            }
                            $pString .= \HTML\span(\HTML\a("link", $subSubKey, $subSubValue)) . $spacing;
                        }
                        $pString .= BR . $spacing;
                    }
                    else
                    {
                        $pString .= $subKey . $spacing;
                    }
                    $index++;

                    continue;
                }
                if (is_array($subValue))
                { // sub submenu
                    $subIndex = 0;
                    foreach ($subValue as $subSubKey => $subSubValue)
                    {
                        if (!$subIndex)
                        {
                            $pString .= BR . $spacing . $subSubKey;
                            ++$subIndex;

                            continue;
                        }
                        $pString .= \HTML\span(\HTML\a("link", $subSubKey, $subSubValue)) . $spacing;
                    }
                    $pString .= BR . $spacing;
                }
                else
                {
                    $pString .= \HTML\span(\HTML\a("link", $subKey, $subValue)) . $spacing;
                }
            }
        }
        else
        {
            $pString .= \HTML\span(\HTML\a("link", $key, $value));
        }

        return $pString;
    }
    /**
     * Return array of menu items to browse collections from under the Resource|Browse menu.
     *
     * @return array Array is empty if no collections
     */
    private function collectionArray()
    {
        $messages = FACTORY_MESSAGES::getInstance();
        $this->db->groupBy('collectionType');
        $recordset = $this->db->select('collection', 'collectionType');
        if (!$this->db->numRows($recordset))
        {
            return [[], []];
        }
        // Add 'ALL' to array
        $browseArray[$messages->text("menu", "browseSubCollection")] = FALSE;
        $browseArray[$messages->text("collection", 'all')] = 'index.php?action=browse_BROWSECOLLECTION_CORE&method=display&collectionType=0';
        $editArray[$messages->text("menu", "editSubCollection")] = FALSE;
        $editArray[$messages->text("collection", 'all')] =
            'index.php?action=edit_EDITCOLLECTION_CORE&method=editChooseCollection&edit_collectionType=0';
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$row['collectionType'])
            {
                continue;
            }
            $browseArray[$messages->text("collection", $row['collectionType'])] =
                'index.php?action=browse_BROWSECOLLECTION_CORE&method=display&collectionType=' . $row['collectionType'];
            $editArray[$messages->text("collection", $row['collectionType'])] =
                'index.php?action=edit_EDITCOLLECTION_CORE&method=editChooseCollection&edit_collectionType=' .
                $row['collectionType'];
        }

        return [$browseArray, $editArray];
    }
    /**
     * Return array of menu items to edit publishers from under the Resource|Edit menu.
     *
     * Array is empty if no publishers
     *
     * @return array
     */
    private function publisherArray()
    {
        $messages = FACTORY_MESSAGES::getInstance();
        $this->db->groupBy('publisherType');
        $recordset = $this->db->select('publisher', 'publisherType');
        if (!$this->db->numRows($recordset))
        {
            return [[], []];
        }
        // Add 'ALL' to array
        $browseArray[$messages->text("menu", "browseSubPublisher")] = FALSE;
        $browseArray[$messages->text("collection", 'all')] = 'index.php?action=browse_BROWSEPUBLISHER_CORE&method=init&PublisherType=0';
        $editArray[$messages->text("menu", "browseSubPublisher")] = FALSE;
        $editArray[$messages->text("collection", 'all')] = 'index.php?action=edit_EDITPUBLISHER_CORE&method=init&PublisherType=0';
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$row['publisherType'])
            {
                continue;
            }
            $browseArray[$messages->text("collection", $row['publisherType'])] =
                'index.php?action=browse_BROWSEPUBLISHER_CORE&method=init&PublisherType=' . $row['publisherType'];
            $editArray[$messages->text("collection", $row['publisherType'])] =
                'index.php?action=edit_EDITPUBLISHER_CORE&method=init&PublisherType=' . $row['publisherType'];
        }

        return [$browseArray, $editArray];
    }
    /**
     * Quickly check if there are any quarantined resources
     */
    private function checkQuarantine()
    {
        $this->db->formatConditions(['resourcemiscQuarantine' => 'Y']);
        $resultset = $this->db->select('resource_misc', 'resourcemiscId');
        $nbQuarantined = $this->db->numRows($resultset);

        return ($nbQuarantined > 0);
    }
    /** set user/group ID conditions
     *
     * @return bool
     */
    private function setIdeasCondition()
    {
        if ($userId = $this->session->getVar('setup_UserId'))
        {
            $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
            $this->db->formatConditions($this->db->formatFields('usergroupsusersGroupId') . $this->db->equal .
                $this->db->formatFields('resourcemetadataPrivate'));
            $subSql = $this->db->selectNoExecute('user_groups_users', 'usergroupsusersId', FALSE, TRUE, TRUE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('N')
                . $this->db->and .
                $this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('Y');
            $case1 = $this->db->caseWhen($subject, FALSE, $subSql, FALSE, FALSE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('Y');
            $result = $this->db->formatFields('resourcemetadataAddUserId') . $this->db->equal . $this->db->tidyInput($userId);
            $case2 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('N');
            $result = $this->db->tidyInput(1);
            $case3 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $this->db->formatConditions($case1 . $this->db->or . $case2 . $this->db->or . $case3);
            $this->db->formatConditions(['resourcemetadataType' => 'i']);

            return TRUE;
        }

        return FALSE;
    }
}
