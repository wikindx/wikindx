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
    /** array */
    private $bibliographies;
    /** array */
    private $bookmarkArray;
    /** string */
    private $bookmarkAdd;
    /** string */
    private $lastSolo = FALSE;
    /** string */
    private $lastThread;
    /** string */
    private $stmt = FALSE;
    /** string */
    private $lastMulti = FALSE;
    /** string */
    private $lastMultiMeta;
    /** string */
    private $lastIdeaSearch;
    /** array */
    private $basketList;
    /** array */
    private $outputArray = [];
    /** boolean */
    private $resourcesExist = FALSE;
    /** boolean */
    private $metadataExist = FALSE;
    /** boolean */
    private $ideasExist = FALSE;
    /** boolean */
    private $enableMetadataMenu = FALSE;
    /** int */
    private $reduceMenuLevel;
    /** string */
    private $reduceMenuLevelPretext = '';
    /** string */
    private $browserTabID = FALSE;

    /**
     * MENU class
     */
    public function __construct()
    {
        // Keep here the responsibility to including SmartyMenu pulgin because
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR, "smarty", "SmartyMenu", "SmartyMenu.class.php"]));

        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
        $this->superAdmin = $this->session->getVar("setup_Superadmin");
        $this->smartyMenu = new SmartyMenu();
        $this->write = $this->session->getVar("setup_Write");
        $this->userId = $this->session->getVar("setup_UserId");
        $this->reduceMenuLevel = $this->session->getVar("setup_ReduceMenuLevel");
        if ($this->session->issetVar("setup_ReduceMenuLevelPretext"))
        {
            $this->reduceMenuLevelPretext = $this->session->getVar("setup_ReduceMenuLevelPretext");
        }
        
        $totR = $this->db->selectCountOnly("resource", "resourceId");
        
        $this->db->formatConditions(['resourcemetadataType' => 'q']);
        $totQ = $this->db->selectCountOnly("resource_metadata", "resourcemetadataId");
        
        $this->db->formatConditions(['resourcemetadataType' => 'p']);
        $totP = $this->db->selectCountOnly("resource_metadata", "resourcemetadataId");
        
        $this->db->formatConditions(['resourcemetadataType' => 'm']);
        $totM = $this->db->selectCountOnly("resource_metadata", "resourcemetadataId");
        
        if ($totR > 0)
        {
            $this->resourcesExist = TRUE;
        }
        if ($this->session->getVar("setup_Superadmin"))
        {
            $this->enableMetadataMenu = TRUE;
        }
        elseif (WIKINDX_METADATA_USERONLY && $this->session->getVar("setup_UserId"))
        {
            $this->enableMetadataMenu = TRUE;
        }
        elseif (WIKINDX_METADATA_ALLOW)
        {
            $this->enableMetadataMenu = TRUE;
        }
        // Admin may have turned off metadata subsystem. Default for $this->metadataExist is FALSE in the class constructor
        if ($this->session->getVar("setup_Superadmin") && ($totQ || $totP || $totM))
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
        elseif ($this->session->getVar("setup_Superadmin"))
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
            if ((WIKINDX_METADATA_USERONLY) && $this->session->getVar("setup_UserId"))
            {
                if ($totQ || $totP || $totM)
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
            if ($totQ || $totP || $totM)
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
        $stateArray[] = WIKINDX_MULTIUSER;
        $stateArray[] = $this->bibliographies = $this->session->getVar("setup_Bibliographies");
        if ($this->browserTabID)
        {
            $lastSolo = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'sql_LastSolo');
        }
        else
        {
            $lastSolo = $this->session->getVar("sql_LastSolo");
        }
        $stateArray[] = $this->lastSolo = $lastSolo;
        if ($this->browserTabID)
        {
            $stmt = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'sql_ListStmt');
        }
        else
        {
            $stmt = $this->session->getVar("sql_ListStmt");
        }
        $stateArray[] = $this->stmt = $stmt;
        if ($stmt)
        { // Don't display for 0 results
            if (!$lastMulti = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'sql_LastMulti')) {
           		$lastMulti = $this->session->getVar("sql_LastMulti");
            }
            $stateArray[] = $this->lastMulti = $lastMulti;
        }
        $stateArray[] = $this->lastThread = $this->session->getVar("sql_LastThread");
        $stateArray[] = $this->lastMultiMeta = $this->session->getVar("sql_LastMultiMeta");
        $stateArray[] = $this->lastIdeaSearch = $this->session->getVar("sql_LastIdeaSearch");
        
        if ($this->browserTabID) {
        	$this->basketList = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'basket_List');
        } else {
        	$this->basketList = $this->session->getVar("basket_List");
        }
        $stateArray[] = $this->basketList;
        $stateArray[] = WIKINDX_IMPORT_BIB;
        $state = base64_encode(serialize($stateArray));
        if (($state == $this->session->getVar("menu_state")) && ($menu = $this->session->getVar("menu_menu", FALSE) !== FALSE))
        {
            GLOBALS::setTplVar('menu', $menu);
        }
        else
        {
            $this->session->setVar("menu_state", $state);
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
            unset($bookmarkSub);
        }
        if ($this->resourcesExist || $this->write)
        {
            $this->createMenuArray($this->res, 'resource', $resourceSub);
            array_push($this->menuSub, $resourceSub);
            unset($resourceSub);
        }
        if ($this->resourcesExist)
        {
            $this->createMenuArray($this->search, 'search', $searchSub);
            array_push($this->menuSub, $searchSub);
            unset($searchSub);
        }
        if ($this->resourcesExist && !empty($this->metadata))
        {
            $this->createMenuArray($this->metadata, 'metadata', $metadataSub);
            array_push($this->menuSub, $metadataSub);
            unset($metadataSub);
        }
        // If not admin, don't display admin menu
        if ($this->superAdmin)
        {
            $this->createMenuArray($this->admin, 'admin', $adminSub);
            array_push($this->menuSub, $adminSub);
            unset($adminSub);
        }
        if (count($this->plugin1) > 1)
        {
            $this->createMenuArray($this->plugin1, 'plugin1', $plugin1Sub);
            array_push($this->menuSub, $plugin1Sub);
            unset($plugin1Sub);
        }
        if (count($this->plugin2) > 1)
        {
            $this->createMenuArray($this->plugin2, 'plugin2', $plugin2Sub);
            array_push($this->menuSub, $plugin2Sub);
            unset($plugin2Sub);
        }
        if (count($this->plugin3) > 1)
        {
            $this->createMenuArray($this->plugin3, 'plugin3', $plugin3Sub);
            array_push($this->menuSub, $plugin3Sub);
            unset($plugin3Sub);
        }
        $browserTabID = GLOBALS::getBrowserTabID();
        if ($browserTabID)
        {
            $browserTabID = '&browserTabID=' . $browserTabID;
        }
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
        $this->session->setVar("menu_menu", $menu);
        // $this->menus is public and available to, for example, the admin to remove messages etc.
        foreach (['wikindx', 'bookmark', 'res', 'search', 'metadata', 'admin', 'plugin1', 'plugin2', 'plugin3'] as $menuItem)
        {
            if (property_exists($this, $menuItem)  and is_array($this->{$menuItem}) and (count($this->{$menuItem}) > 1))
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
            GLOBALS::setTplVar('heading', 'WIKINDX');
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
        // $this->configure();
        // Check for plug-in modules
        // $this->menuInsert();
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
        // Build arrays of menus items.  First element of array name is main menu item name, subsequent elements are the names
        // of the submenu with its
        // hyperlink.  This provides easy access for both building CSS menus and for displaying links for non-CSS drop-down
        // web browsers.
        
        // WIKINDX MENU
        $this->wikindx = [
            'Wikindx' => 'index.php?action=noMenu&method=wikindx',
            $messages->text("menu", "home") => 'index.php' . "?browserTabID=" . $this->browserTabID,
        ];
        if ($this->session->getVar("setup_News"))
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
        
        $this->wikindx['statisticsSub'] = [
            $messages->text("menu", "statisticsSub") => FALSE,
        ];
        
        if ($this->userId || WIKINDX_DISPLAY_STATISTICS)
        {
            $this->wikindx['statisticsSub'][$messages->text("menu", "statisticsSub")] = FALSE;
            $this->wikindx['statisticsSub'][$messages->text("menu", "statisticsTotals")] = 'index.php?action=statistics_STATS_CORE&method=totals';
            $this->wikindx['statisticsSub'][$messages->text("menu", "statisticsKeywords")] = 'index.php?action=statistics_STATS_CORE&method=keywords';
            $this->wikindx['statisticsSub'][$messages->text("menu", "statisticsYears")] = 'index.php?action=statistics_STATS_CORE&method=years';
            $this->wikindx['statisticsSub'][$messages->text("menu", "statisticsAllCreators")] = 'index.php?action=statistics_STATS_CORE&method=allCreators';
            $this->wikindx['statisticsSub'][$messages->text("menu", "statisticsMainCreators")] = 'index.php?action=statistics_STATS_CORE&method=mainCreators';
            $this->wikindx['statisticsSub'][$messages->text("menu", "statisticsPublishers")] = 'index.php?action=statistics_STATS_CORE&method=publishers';
            $this->wikindx['statisticsSub'][$messages->text("menu", "statisticsCollections")] = 'index.php?action=statistics_STATS_CORE&method=collections';
        }
        
        if ($this->userId || WIKINDX_DISPLAY_USER_STATISTICS)
        {
            $this->wikindx['statisticsSub'][$messages->text("menu", "statisticsUsers")] = 'index.php?action=statistics_STATS_CORE&method=users';
            // Disabled temporarily for some later dates when statistics can be calculated in the database code.
            // $this->wikindx['statisticsSub'][$messages->text("menu", "listDownloads")] = 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=downloadsIndex';
            // $this->wikindx['statisticsSub'][$messages->text("menu", "listPopularity")] = 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=popularityIndex';
        }
        
        if ($this->write)
        {
            // On the first run after a fresh install this screen is displayed immediately
            // and these two options are not yet set, so we avoid to check the READONLY mode strictly.
            if (!WIKINDX_DENY_READONLY && WIKINDX_READ_ONLY_ACCESS)
            {
                $this->wikindx[$messages->text("menu", "readOnly")] = 'index.php?action=readOnly';
            }
            $this->wikindx[$messages->text("menu", "logout")] = 'index.php?action=logout';
        }
        else
        {
            $this->wikindx[$messages->text("menu", "userLogon")] = 'index.php?action=initLogon';
        }
        if (WIKINDX_MULTIUSER && WIKINDX_USER_REGISTRATION && WIKINDX_MAIL_USE && !$this->userId)
        {
            $this->wikindx[$messages->text("menu", "register")] = 'index.php?action=initRegisterUser';
        }
        $this->wikindx[$messages->text("menu", "about")] = 'index.php?action=help_ABOUT_CORE';
        
                
        // RESOURCE MENU
        list($collBrowseSub, $collEditSub) = $this->collectionArray();
        list($pubBrowseSub, $pubEditSub) = $this->publisherArray();
        
        $this->res = [
            $messages->text("menu", "res") => 'index.php?action=noMenu&method=res',
        ];
        if ($this->write)
        {
            $this->res[$messages->text("menu", "new")] = 'index.php?action=resource_RESOURCEFORM_CORE';
        }
        
        if (!empty($this->basketList))
        {
            $this->res['basketSub'] = [
                $messages->text("menu", "basketSub") => FALSE,
                $messages->text("menu", "basketView") => 'index.php?action=basket_BASKET_CORE&method=view' . "&browserTabID=" . $this->browserTabID,
                $messages->text("menu", "basketDelete") => 'index.php?action=basket_BASKET_CORE&method=delete' . "&browserTabID=" . $this->browserTabID,
            ];
        }
            
        $this->res['bookmarkSub'] = [
            $messages->text("menu", "bookmarkSub") => FALSE,
        ];
        
        if ($this->bookmarkAdd)
        {
            $found = FALSE;
            for ($i = 1; $i <= 6; $i++)
            {
                if (
                    array_key_exists($i . "_name", $this->bookmarkArray)
                    && array_key_exists($i . "_id", $this->bookmarkArray)
                    && $this->bookmarkArray[$i . "_id"] == $this->lastSolo
                    && $this->bookmarkView == 'solo'
                ) {
                    $found = TRUE;

                    break;
                }
                elseif (
                    array_key_exists($i . "_name", $this->bookmarkArray)
                    && array_key_exists($i . "_multi", $this->bookmarkArray)
                    && ($this->bookmarkView == 'multi')
                ) {
                    $encodedSql = base64_encode($this->stmt);
                    $bk = \UTF8\mb_explode('|', $this->bookmarkArray[$i . "_multi"]); // statement, multi, listParams
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
            }
        }
        if (count($this->bookmarkArray) > 2)
        {
            $this->res['bookmarkSub'][$messages->text("menu", "bookmarkDelete")] = 'index.php?action=bookmarks_BOOKMARK_CORE&method=deleteInit';
            for ($i = 1; $i <= 20; $i++)
            {
                if (array_key_exists($i . "_name", $this->bookmarkArray) && array_key_exists($i . "_id", $this->bookmarkArray))
                {
                    $this->res['bookmarkSub'][stripslashes($this->bookmarkArray[$i . "_name"])] = "index.php?action=resource_RESOURCEVIEW_CORE&id=" . $this->bookmarkArray[$i . "_id"];
                }
                elseif (array_key_exists($i . "_name", $this->bookmarkArray) && array_key_exists($i . "_multi", $this->bookmarkArray))
                {
                    $this->res['bookmarkSub'][stripslashes($this->bookmarkArray[$i . "_name"])] = 'index.php?action=bookmarks_BOOKMARK_CORE&method=multiView&id=' . $i;
                }
            }
        }
        
        // Disable menu items if there are not yet resources
        if ($this->resourcesExist)
        {
            $this->res[$messages->text("menu", "randomResource")] = 'index.php?action=resource_RESOURCEVIEW_CORE&method=random' . "&browserTabID=" . $this->browserTabID;
        }
        if ($this->lastSolo)
        {
            $this->res[$messages->text("menu", "lastSolo")] = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->lastSolo . "&browserTabID=" . $this->browserTabID;
        }
        if ($this->lastMulti)
        {
            $BT = $this->browserTabID ? '&browserTabID=' . $this->browserTabID : FALSE;
            $this->res[$messages->text("menu", "lastMulti")] = 'index.php?' . $this->lastMulti . '&type=lastMulti' . $BT;
        }
        
        if ($this->write)
        {
            // Disable menu items if there are not yet resources
            if ($this->resourcesExist)
            {
                if (($this->write && WIKINDX_GLOBAL_EDIT) || $this->superAdmin)
                {
                    $this->res['editSub'] = [
                        $messages->text("menu", "editSub") => FALSE,
                        $messages->text("menu", "creator") => 'index.php?action=edit_EDITCREATOR_CORE',
                        $messages->text("menu", "keyword") => 'index.php?action=edit_EDITKEYWORD_CORE',
                        $messages->text("menu", "keywordGroup") => 'index.php?action=edit_EDITKEYWORDGROUP_CORE',
                    ];
                    
                    if (!empty($collEditSub))
                    {
                        array_push($this->res['editSub'], $collEditSub);
                    }
                    
                    if (!empty($collEditSub))
                    {
                        array_push($this->res['editSub'], $pubEditSub);
                    }
                }
            }
        }
        
// import sub first . . .
        if ($this->write)
        {
            $this->res['importSub'] = [
                $messages->text("menu", "importSub") => FALSE,
            ];          
            if ((WIKINDX_MAX_PASTE > 0) || $this->superAdmin)
            {
                $this->res['importSub'][$messages->text("menu", "pasteBibtex")] = 'index.php?action=import_PASTEBIBTEX_CORE';
            }
            if (WIKINDX_IMPORT_BIB || $this->superAdmin)
            {
                $this->res['importSub'][$messages->text("menu", "importBibtex")] = 'index.php?action=import_BIBTEXFILE_CORE';
           		$this->res['importSub'][$messages->text('menu', 'importEndnote')] = 'index.php?action=import_ENDNOTEIMPORT_CORE';
				$this->res['importSub'][$messages->text('menu', 'importPubMed')] = 'index.php?action=import_PUBMED_CORE';
            }
			$this->res[$messages->text('menu', 'bibutils')] = 'index.php?action=bibutils_BIBUTILS_CORE';
        }
// then export sub . . .
		if ($this->basketList) {
			$this->res['exportbasketSub'] = [
				$messages->text("menu", "exportbasket") => FALSE,
			];
			$this->res['exportbasketSub'][$messages->text("menu", 'exportRtf')] = "initRtfExportB";
			$this->res['exportbasketSub'][$messages->text("menu", 'exportBibtex')] = 
				'index.php?action=export_BIBTEXEXPORT_CORE&method=initBibtexExportB';
			$this->res['exportbasketSub'][$messages->text("menu", 'exportHTML')] = 
				'index.php?action=export_HTMLEXPORT_CORE&method=initHtmlExportB';
			$this->res['exportbasketSub'][$messages->text("menu", 'exportEndNote')] = 'initEndnoteExportB';
			$this->res['exportbasketSub'][$messages->text("menu", 'exportRIS')] = 'initRisExportB';
		}
		if ($this->lastMulti) {
			$this->res['exportlistSub'] = [
				$messages->text("menu", "exportlist") => FALSE,
			];
			$this->res['exportlistSub'][$messages->text("menu", 'exportRtf')] = "initRtfExportL";
			$this->res['exportlistSub'][$messages->text("menu", 'exportBibtex')] = 
				'index.php?action=export_BIBTEXEXPORT_CORE&method=initBibtexExportL';
			$this->res['exportlistSub'][$messages->text("menu", 'exportHTML')] = 
				'index.php?action=export_HTMLEXPORT_CORE&method=initHtmlExportL';
			$this->res['exportlistSub'][$messages->text("menu", 'exportEndNote')] = 'initEndnoteExportB';
			$this->res['exportlistSub'][$messages->text("menu", 'exportRIS')] = 'initRisExportB';
		}
		if ($this->session->getVar("fileExports")) {
			$this->res['exportSub'][$messages->text("menu", "listFiles")] = 'index.php?action=export_FILES_CORE&method=listFiles';
		}
        
        
        // SEARCH MENU
        $this->search = [];
        
        $this->search[$messages->text("menu", "search")] = 'index.php?action=noMenu&method=search';
        $this->search[$messages->text("menu", "quickSearch")] = 'index.php?action=list_QUICKSEARCH_CORE' . "&browserTabID=" . $this->browserTabID;
        $this->search[$messages->text("menu", "advancedSearch")] = 'index.php?action=list_SEARCH_CORE' . "&browserTabID=" . $this->browserTabID;
        if ($this->resourcesExist)
        {
            $this->search[$messages->text("menu", "categoryTree")] = 'index.php?action=browse_CATEGORYTREE_CORE';
        }
        $this->search['listSub'] = [
            $messages->text("menu", "listSub") => FALSE,
            $messages->text("menu", "listCreator") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=creator' . "&browserTabID=" . $this->browserTabID,
            $messages->text("menu", "listTitle") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=title' . "&browserTabID=" . $this->browserTabID,
            $messages->text("menu", "listPublisher") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=publisher' . "&browserTabID=" . $this->browserTabID,
            $messages->text("menu", "listYear") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=year' . "&browserTabID=" . $this->browserTabID,
            $messages->text("menu", "listTimestamp") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=timestamp' . "&browserTabID=" . $this->browserTabID,
            $messages->text("menu", "listMaturity") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=maturityIndex' . "&browserTabID=" . $this->browserTabID,
            // $messages->text("menu", "listViews") => 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=viewsIndex' . "&browserTabID=" . $this->browserTabID,
        ];
        $this->search['browseSub'] = [
            $messages->text("menu", "browseSub") => FALSE,
            $messages->text("menu", "browseType") => 'index.php?action=browse_BROWSETYPE_CORE',
            $messages->text("menu", "browseCreator") => 'index.php?action=browse_BROWSECREATOR_CORE',
            $messages->text("menu", "browseCited") => 'index.php?action=browse_BROWSECITED_CORE',
            $messages->text("menu", "browseYear") => 'index.php?action=browse_BROWSEYEAR_CORE',
            $messages->text("menu", "browseKeyword") => 'index.php?action=browse_BROWSEKEYWORD_CORE',
            $messages->text("menu", "browseKeywordGroup") => 'index.php?action=browse_BROWSEKEYWORDGROUP_CORE',
            $messages->text("menu", "browseCategory") => 'index.php?action=browse_BROWSECATEGORY_CORE',
            $messages->text("menu", "browseSubcategory") => 'index.php?action=browse_BROWSESUBCATEGORY_CORE',
            $messages->text("menu", "browseLanguage") => 'index.php?action=browse_BROWSELANGUAGE_CORE',
            $messages->text("menu", "browseUser") => 'index.php?action=browse_BROWSEUSER_CORE&method=user',
            $messages->text("menu", "browseDept") => 'index.php?action=browse_BROWSEUSER_CORE&method=department',
            $messages->text("menu", "browseInst") => 'index.php?action=browse_BROWSEUSER_CORE&method=institution',
        ];
        
        if (!empty($collBrowseSub))
        {
            array_push($this->search['browseSub'], $collBrowseSub);
        }
        if (!empty($pubBrowseSub))
        {
            array_push($this->search['browseSub'], $pubBrowseSub);
        }
        
        if ($this->write)
        {
            $this->search['browseSub'][$messages->text("menu", "browseBibliography")] = 'index.php?action=browse_BROWSEBIBLIOGRAPHY_CORE';
            
            $userTagsObject = FACTORY_USERTAGS::getInstance();
            $userTags = $userTagsObject->grabAll(GLOBALS::getUserVar('BrowseBibliography'));
            if (!empty($userTags))
            {
                $this->search['browseSub'][$messages->text("menu", "browseUserTags")] = 'index.php?action=browse_BROWSEUSERTAGS_CORE';
            }
            
            // $this->search['listSub'][$messages->text("menu", "listDownloads")] = 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=downloadsIndex' . "&browserTabID=" . $this->browserTabID;
            // $this->search['listSub'][$messages->text("menu", "listPopularity")] = 'index.php?action=list_LISTRESOURCES_CORE&method=processGeneral&list_Order=popularityIndex' . "&browserTabID=" . $this->browserTabID;
        }
        
        
        // METADATA MENU
        if ($this->enableMetadataMenu)
        {
            $this->metadata = [];
            $this->metadata[$messages->text("menu", "text")] = 'index.php?action=noMenu&method=text';
            
            if ($this->write)
            {
                $this->metadata[$messages->text("menu", "addIdea")] = 'index.php?action=ideas_IDEAS_CORE&method=ideaEdit';
            }
            
            $this->metadata['randomSub'] = [
                $messages->text("menu", "randomSub") => FALSE,
            ];
            
            $this->metadata['browseKeywordSub'] = [
                $messages->text("menu", "browseKeywordSub") => FALSE,
            ];
            
            $this->metadata['browseKeywordGroupSub'] = [
                $messages->text("menu", "browseKeywordGroupSub") => FALSE,
            ];
            
            if ($this->metadataExist)
            {
                $this->metadata['randomSub'][$messages->text("menu", "randomQuotes")] = 'index.php?action=metadata_RANDOMMETADATA_CORE&method=randomQuote';
                $this->metadata['randomSub'][$messages->text("menu", "randomParaphrases")] = 'index.php?action=metadata_RANDOMMETADATA_CORE&method=randomParaphrase';
                
                if ($this->write)
                {
                    $this->metadata['randomSub'][$messages->text("menu", "randomMusings")] = 'index.php?action=metadata_RANDOMMETADATA_CORE&method=randomMusing';
                }
                
                $this->metadata['browseKeywordSub'][$messages->text("menu", "browseKeywordAll")] = 'index.php?action=browse_BROWSEKEYWORD_CORE&metadata=1&mType=all';
                $this->metadata['browseKeywordSub'][$messages->text("menu", "browseKeywordQuotes")] = 'index.php?action=browse_BROWSEKEYWORD_CORE&metadata=1&mType=quotes';
                $this->metadata['browseKeywordSub'][$messages->text("menu", "browseKeywordParaphrases")] = 'index.php?action=browse_BROWSEKEYWORD_CORE&metadata=1&mType=paraphrases';
                $this->metadata['browseKeywordSub'][$messages->text("menu", "browseKeywordMusings")] = 'index.php?action=browse_BROWSEKEYWORD_CORE&metadata=1&mType=musings';
                
                $this->metadata['browseKeywordGroupSub'][$messages->text("menu", "browseKeywordAll")] = 'index.php?action=browse_BROWSEKEYWORDGROUP_CORE&metadata=1&mType=all';
                $this->metadata['browseKeywordGroupSub'][$messages->text("menu", "browseKeywordQuotes")] = 'index.php?action=browse_BROWSEKEYWORDGROUP_CORE&metadata=1&mType=quotes';
                $this->metadata['browseKeywordGroupSub'][$messages->text("menu", "browseKeywordParaphrases")] = 'index.php?action=browse_BROWSEKEYWORDGROUP_CORE&metadata=1&mType=paraphrases';
                $this->metadata['browseKeywordGroupSub'][$messages->text("menu", "browseKeywordMusings")] = 'index.php?action=browse_BROWSEKEYWORDGROUP_CORE&metadata=1&mType=musings';
                        
                if ($this->lastMultiMeta)
                {
                    $this->metadata[$messages->text("menu", "lastMultiMeta")] = 'index.php?action=lastMultiMeta';
                    $this->metadata[$messages->text("menu", "rtfexp")] = 'index.php?action=metaExportRtf';
                }
                if ($this->lastIdeaSearch)
                {
                    $this->metadata[$messages->text("menu", "lastIdeaSearch")] = "index.php?action=list_SEARCH_CORE&method=reprocess&type=displayIdeas";
                }
            }
            if ($this->ideasExist)
            {
                $this->metadata[$messages->text("menu", "listIdeas")] = 'index.php?action=ideas_IDEAS_CORE&method=ideaList';
                
                if ($this->write)
                {
                    $this->metadata['randomSub'][$messages->text("menu", "randomIdeas")] = 'index.php?action=metadata_RANDOMMETADATA_CORE&method=randomIdea';
                }
                
                $this->metadata['browseKeywordSub'][$messages->text("menu", "browseKeywordIdeas")] = 'index.php?action=browse_BROWSEKEYWORD_CORE&metadata=1&mType=ideas';
                $this->metadata['browseKeywordSub'][$messages->text("menu", "browseKeywordNotIdeas")] = 'index.php?action=browse_BROWSEKEYWORD_CORE&metadata=1&mType=notIdeas';
                                
                $this->metadata['browseKeywordGroupSub'][$messages->text("menu", "browseKeywordIdeas")] = 'index.php?action=browse_BROWSEKEYWORDGROUP_CORE&metadata=1&mType=ideas';
                $this->metadata['browseKeywordGroupSub'][$messages->text("menu", "browseKeywordNotIdeas")] = 'index.php?action=browse_BROWSEKEYWORDGROUP_CORE&metadata=1&mType=notIdeas';
                
                if ($this->lastThread)
                {
                    $this->metadata[$messages->text("menu", "lastIdea")] ='index.php?action=ideas_IDEAS_CORE&method=threadView&resourcemetadataId=' . $this->lastThread;
                }
                $this->metadata[$messages->text('menu', 'exportIdeas')] = 'index.php?action=ideas_IDEAEXPORT_CORE';
            }
        }
        
        
        // ADMIN MENU
        $this->admin = [];
        
        $this->admin[$messages->text("menu", "admin")] = 'index.php?action=noMenu&method=admin';
        $this->admin[$messages->text("menu", "conf")] = 'index.php?action=admin_CONFIGURE_CORE';
        $this->admin[$messages->text("menu", "components")] = 'index.php?action=admin_ADMINCOMPONENTS_CORE';
        
        if (WIKINDX_MULTIUSER)
        {
            $this->admin['userSub'] = [
                $messages->text("menu", "userSub") => FALSE,
                $messages->text("menu", "userAdd") => 'index.php?action=admin_ADMINUSER_CORE&method=addInit',
                $messages->text("menu", "userEdit") => 'index.php?action=admin_ADMINUSER_CORE&method=editInit',
                $messages->text("menu", "userDelete") => 'index.php?action=admin_ADMINUSER_CORE&method=deleteInit',
                $messages->text("menu", "userBlock") => 'index.php?action=admin_ADMINUSER_CORE&method=blockInit',
                $messages->text("menu", "userRegistration") => 'index.php?action=admin_ADMINUSER_CORE&method=registrationInit',
            ];
        }
        
        $this->admin[$messages->text("menu", "news")] = 'index.php?action=news_NEWS_CORE&method=init';
        $this->admin[$messages->text("menu", "categories")] = 'index.php?action=admin_ADMINCATEGORIES_CORE&method=catInit';
        $this->admin[$messages->text("menu", "subcategories")] = 'index.php?action=admin_ADMINCATEGORIES_CORE&method=subInit';
        $this->admin[$messages->text("menu", "custom")] = 'index.php?action=admin_ADMINCUSTOM_CORE&method=init';
        $this->admin[$messages->text("menu", "images")] = 'index.php?action=admin_DELETEIMAGES_CORE';
        $this->admin[$messages->text("menu", "language")] = 'index.php?action=admin_ADMINLANGUAGES_CORE&method=init';
        
        
        if ($this->resourcesExist)
        {
            $this->admin['keywordSub'] = [
                $messages->text("menu", "keywordSub") => FALSE,
                $messages->text("menu", "keywordEdit") => 'index.php?action=admin_ADMINKEYWORD_CORE&method=editInit',
                $messages->text("menu", "keywordMerge") => 'index.php?action=admin_ADMINKEYWORD_CORE&method=mergeInit',
                $messages->text("menu", "keywordDelete") => 'index.php?action=admin_ADMINKEYWORD_CORE&method=deleteInit',
            ];
            $this->admin['creatorSub'] = [
                $messages->text("menu", "creatorSub") => FALSE,
                $messages->text("menu", "creatorEdit") => 'index.php?action=edit_EDITCREATOR_CORE',
                $messages->text("menu", "creatorMerge") => 'index.php?action=admin_ADMINCREATOR_CORE&method=mergeInit',
                $messages->text("menu", "creatorGroup") => 'index.php?action=admin_ADMINCREATOR_CORE&method=groupInit',
            ];
            
            $this->admin[$messages->text("menu", "delete")] = 'index.php?action=admin_DELETERESOURCE_CORE';
        }
        
        if (WIKINDX_QUARANTINE && $this->checkQuarantine())
        {
            $this->admin[$messages->text("menu", "quarantine")] = 
            	'index.php?action=list_LISTSOMERESOURCES_CORE&method=quarantineProcess' . "&browserTabID=" . $this->browserTabID;
        }
        
        
        // PLUGIN1 MENU
        $this->plugin1 = [$messages->text("menu", "plugin1") => 'index.php?action=noMenu&method=plugin1'];
        
        
        // PLUGIN2 MENU
        $this->plugin2 = [$messages->text("menu", "plugin2") => 'index.php?action=noMenu&method=plugin2'];
        
        
        // PLUGIN3 MENU
        $this->plugin3 = [$messages->text("menu", "plugin3") => 'index.php?action=noMenu&method=plugin3'];
    }
    /**
     * Insert available modules into menu system
     */
    private function menuInsert()
    {
        $menuHeadings = ["wikindx", "res", "search", "metadata", "admin", "plugin1", "plugin2", "plugin3"];
        
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "startup", "LOADPLUGINS.php"]));
        $loadmodules = new LOADPLUGINS();
        $moduleList = $loadmodules->readPluginsDirectory();
        
        foreach ($moduleList as $dirName)
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, $dirName, "index.php"]));
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
            if (($class->authorize == 1) && !$this->write)
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
                                    // Ignore empty sub-menus
                                    elseif (is_array($value) && count($value) > 0)
                                    {
                                        // Ignore empty sub-menus
                                        if (count($value) == 1 && $value[array_key_first($value)] == FALSE)
                                        {
                                            continue;
                                        }
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
                    // Ignore empty sub-menu
                    if (count($subLink) == 0)
                    {
                        continue;
                    }
                    // Ignore empty sub-menu
                    elseif (count($subLink) == 1 && $link[array_key_first($subLink)] == FALSE)
                    {
                        continue;
                    }
                    elseif ($this->reduceMenuLevel == 1)
                    {
                        // remove one submenu level
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
                    {
                        // keep all submenus
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
                    // Ignore empty sub-menu
                    if (count($link) == 0)
                    {
                        continue;
                    }
                    // Ignore empty sub-menu
                    elseif (count($link) == 1 && $link[array_key_first($link)] == FALSE)
                    {
                        continue;
                    }
                    elseif ($this->reduceMenuLevel == 2)
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
        $editArray[$messages->text("collection", 'all')] = 'index.php?action=edit_EDITCOLLECTION_CORE&method=editChooseCollection&collectionType=0';
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$row['collectionType'])
            {
                continue;
            }
            $browseArray[$messages->text("collection", $row['collectionType'])] = 'index.php?action=browse_BROWSECOLLECTION_CORE&method=display&collectionType=' . $row['collectionType'];
            $editArray[$messages->text("collection", $row['collectionType'])] = 'index.php?action=edit_EDITCOLLECTION_CORE&method=editChooseCollection&collectionType=' . $row['collectionType'];
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
        $this->db->limit(1, 0);
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
        if ($userId = $this->session->getVar("setup_UserId"))
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
