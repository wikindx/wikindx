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
 *	ADMINCOMPONENTS class.
 *
 *	Administration of components including plugins, bibliography styles and templates.
 */
class ADMINCOMPONENTS
{
    /** By changing the name of the file downloaded from the update server to each version,
     * even a minor one, we ensure that it is not possible to downgrade a component by accident
     * when we have not reloaded the list without having need dates or an exhaustive list of old hashes.
     * On the other hand, it is compulsory to deliver the components for all versions,
     * even if they do not change.
     */
    protected $serverComponentsListPath = WIKINDX_DIR_BASE . DIRECTORY_SEPARATOR . WIKINDX_DIR_CACHE . DIRECTORY_SEPARATOR . "components_" . WIKINDX_PUBLIC_VERSION . ".json";
    private $db;
    private $vars;
    private $session;
    private $errors;
    private $messages;
    private $success;
    private $gatekeep;
    private $update;
    private $possibleMenus;
    private $possibleContainers;
    private $co;
    private $messageString = '';
    private $messageStringId = '';
    private $messageStringType = '';

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->co = FACTORY_CONFIGDBSTRUCTURE::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->gatekeep->requireSuper = TRUE;
        $this->gatekeep->init();
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('plugins'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminComponents"));
    }
    /**
     * checkUpdatesOnline
     *
     * @param mixed $message
     */
    public function checkUpdatesOnline($message = FALSE)
    {
        if (WIKINDX_IS_TRUNK)
        {
            $upd_srv_link = WIKINDX_COMPONENTS_UPDATE_SERVER . "?version=" . "trunk";
        }
        else
        {
            $upd_srv_link = WIKINDX_COMPONENTS_UPDATE_SERVER . "?version=" . WIKINDX_PUBLIC_VERSION;
        }
        
        if (\UTILS\download_sf_file($upd_srv_link, $this->serverComponentsListPath))
        {
            $this->init($this->success->text("componentUpToDate"));
        }
        else
        {
            $this->init($this->success->text("componentUpToDate"));
        }
    }
    /**
     * Display options
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        $stringByType = ["plugin" => "", "style" => "", "template" => "", "vendor" => ""];
        $coutByType = ["plugin" => 0, "style" => 0, "template" => 0, "vendor" => 0];
        $rootPathByType = [
            'plugin' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS]),
            'style' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES]),
            'template' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES]),
            'vendor' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR]),
        ];
        
        // Local components
        $componentsInstalled = \UTILS\readComponentsList(TRUE);
        
        // Components released on the update server
        $componentsRelease = [];
        $AllowUpdate = TRUE;
        
        $InstalledExtensions = get_loaded_extensions();
        
        $pString = "";
        // Without curl we can't download
        if (!in_array("curl", $InstalledExtensions))
        {
            $AllowUpdate = FALSE;
            $pString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'missingCurl'));
        }
        // Without a decompressor we can't update
        elseif (!in_array("zip", $InstalledExtensions))
        {
            $AllowUpdate = FALSE;
            $pString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'missingCompression'));
        }
        // Retrieve the list
        else
        {
            if (file_exists($this->serverComponentsListPath))
            {
                $componentsRelease = \FILE\read_json_file($this->serverComponentsListPath);
                if ($componentsRelease === NULL)
                {
                    $componentsRelease = [];
                    $AllowUpdate = FALSE;
                    $pString .= $this->errors->text("components", "parse");
                }
            }
            else
            {
                $componentsRelease = [];
                $AllowUpdate = FALSE;
                $pString .= "The component list has not yet been downloaded.";
            }
        }
        
        // abb. for hash key names that use the name of the hashing algo
        $hashkeyid = "component_" . WIKINDX_PACKAGE_HASH_ALGO;
        $lasthashkeyid = "component_" . WIKINDX_PACKAGE_HASH_ALGO . "_latest";
        
        // Merge components lists
        $componentslistMerged = [];
        
        // 1. Search installed components with a version online
        foreach ($componentsInstalled as $ki => $ci)
        {
            foreach ($componentsRelease as $kr => $cr)
            {
                if ($ci["component_type"] == $cr["component_type"] && $ci["component_id"] == $cr["component_id"])
                {
                    // Use the metadata from the last component and keep additional fields of the installed component
                    $cr["component_version_latest"] = $cr["component_version"];
                    $cr["component_version"] = $ci["component_version"];
                    
                    $cr[$lasthashkeyid] = $cr[$hashkeyid];
                    $cr[$hashkeyid] = $ci[$hashkeyid];
                    
                    $cr["component_integrity"] = $ci["component_integrity"];
                    $cr["component_status"] = $ci["component_status"];
                    
                    // Decides on possible actions
                    $action = [];
                    if ($cr["component_status"] == "enabled" && $cr["component_builtin"] == "false" && $cr["component_type"] != "vendor")
                    {
                        $action["disable"] = $this->messages->text("components", "disable");
                    }
                    if ($cr["component_status"] == "disabled" && $cr["component_integrity"] == 0 && $ci["component_type"] != "vendor")
                    {
                        $action["enable"] = $this->messages->text("components", "enable");
                    }
                    if ($cr["component_builtin"] == "false" && $cr["component_type"] != "vendor")
                    {
                        $action["uninstall"] = $this->messages->text("components", "uninstall");
                    }
                    if ($cr["component_updatable"] == "true" && $cr[$lasthashkeyid] == $cr[$hashkeyid] && $AllowUpdate)
                    {
                        $action["reinstall"] = $this->messages->text("components", "reinstall");
                    }
                    if ($cr["component_updatable"] == "true" && $cr[$lasthashkeyid] != $cr[$hashkeyid] && $AllowUpdate)
                    {
                        $action["update"] = $this->messages->text("components", "update");
                    }
                    if ($cr["component_type"] == "plugin")
                    {
                        $action["configure"] = $this->messages->text("components", "configure");
                    }
                    
                    $cr["component_action"] = $action;
                    
                    $componentslistMerged[] = $cr;
                }
            }
        }
        
        // 2. Search installed components without online alternative
        foreach ($componentsInstalled as $ki => $ci)
        {
            $match = FALSE;
            foreach ($componentsRelease as $kr => $cr)
            {
                if ($ci["component_type"] == $cr["component_type"] && $ci["component_id"] == $cr["component_id"])
                {
                    $match = TRUE;

                    break;
                }
            }
            if (!$match)
            {
                // Decides on possible actions
                $action = [];
                if ($ci["component_status"] == "enabled" && $ci["component_builtin"] == "false" && $ci["component_type"] != "vendor")
                {
                    $action["disable"] = $this->messages->text("components", "disable");
                }
                if ($ci["component_status"] == "disabled" && $ci["component_integrity"] == 0 && $ci["component_type"] != "vendor")
                {
                    $action["enable"] = $this->messages->text("components", "enable");
                }
                if ($ci["component_builtin"] == "false" && $ci["component_type"] != "vendor")
                {
                    $action["uninstall"] = $this->messages->text("components", "uninstall");
                }
                if ($ci["component_type"] == "plugin")
                {
                    $action["configure"] = $this->messages->text("components", "configure");
                }
                
                $ci["component_action"] = $action;
                
                $componentslistMerged[] = $ci;
            }
        }
        
        // 3. Search components available online and not installed
        foreach ($componentsRelease as $kr => $cr)
        {
            $match = FALSE;
            foreach ($componentsInstalled as $ki => $ci)
            {
                if ($ci["component_type"] == $cr["component_type"] && $ci["component_id"] == $cr["component_id"])
                {
                    $match = TRUE;

                    break;
                }
            }
            if (!$match)
            {
                $cr["component_version_latest"] = $cr["component_version"];
                $cr["component_version"] = "";
                
                $cr[$lasthashkeyid] = $cr[$hashkeyid];
                $cr[$hashkeyid] = "";
                
                // Safe by default
                $cr["component_integrity"] = 0;
                
                // Disabled by default
                $cr["component_status"] = "disabled";
                    
                // Decides on possible actions
                if ($AllowUpdate)
                {
                    $cr["component_action"] = ["install" => $this->messages->text("components", "install")];
                }
                else
                {
                    $cr["component_action"] = [];
                }
                
                $componentslistMerged[] = $cr;
            }
        }
        
        // Ignore installed language components (this type have been removed in 6.3.6)
        // TODO: remove this code after two or three releases
        foreach ($componentslistMerged as $k => $cmp)
        {
            if ($cmp["component_type"] == "language")
            {
                unset($componentslistMerged[$k]);
            }
        }
        
        foreach ($componentslistMerged as $k => $cmp)
        {
            $sortArray[] = $cmp["component_name"];
        }
        
        natsort($sortArray);
        
        foreach ($stringByType as $type => $unused)
        {
            $h = HTML\trStart();
            $h .= HTML\td(HTML\aName($type, "&nbsp;") . $type . " " . HTML\a("link", "&uarr;", "#topnav"), "smallcaps alternate3 middle center padding5px", "6");
            $h .= HTML\trEnd();
            $h .= HTML\trStart();
            $h .= HTML\th($this->messages->text("components", "description"), "alternate2 padding5px width40percent");
            $h .= HTML\th($this->messages->text("components", "credits"), "alternate2 padding5px");
            $h .= HTML\th($this->messages->text("components", "version"), "alternate2 padding5px");
            $h .= HTML\th($this->messages->text("components", "licence"), "alternate2 padding5px");
            $h .= HTML\th($this->messages->text("components", "package"), "alternate2 padding5px");
            $h .= HTML\th($this->messages->text("components", "action"), "alternate2 padding5px");
            $h .= HTML\trEnd();
            $h .= "\n";
            $stringByType[$type] .= $h;
        }
        
        foreach ($sortArray as $cmpkey => $unused)
        {
            $cmp = $componentslistMerged[$cmpkey];
            $coutByType[$cmp["component_type"]]++;
            $rootPath = $rootPathByType[$cmp["component_type"]];
            $h = "";
            if ($this->messageString && ($this->messageStringId == $cmp["component_id"]) && ($this->messageStringType == $cmp["component_type"]))
            {
                $h .= HTML\trStart("alternate" . (1 + $coutByType[$cmp["component_type"]] % 2));
                $h .= HTML\td(HTML\aName($cmp["component_type"] . $coutByType[$cmp["component_type"]]) . $this->messageString, '', 6);
                $h .= HTML\trEnd();
            }
            $h .= HTML\trStart("alternate" . (1 + $coutByType[$cmp["component_type"]] % 2));
            $h .= HTML\tdStart("padding5px");
            if (!$this->messageString)
            {
                $h .= HTML\aName($cmp["component_type"] . $coutByType[$cmp["component_type"]]);
            }
            $h .= "<h4>";
            $h .= $cmp["component_name"];
            if (array_key_exists("component_website", $cmp))
            {
                $h .= " " . HTML\a("link", "&#x1f310;", $cmp["component_website"], "blank");
            }
            $h .= "</h4>";
            $h .= "<p>" . $cmp["component_description"] . "</p>";
            if (file_exists($rootPath . DIRECTORY_SEPARATOR . $cmp["component_id"] . DIRECTORY_SEPARATOR . 'README.txt'))
            {
                $js = "onClick=\"coreOpenPopup('index.php?action=admin_ADMINCOMPONENTS_CORE&method=readMe&type=" . $cmp["component_type"] . "&file=" . $cmp["component_id"] . "&dummy=" . \UTILS\uuid() . "'); return false\"";
                $h .= \HTML\div("divReadMeMenu", \HTML\aBrowse(
                    'green',
                    '1em',
                    $this->messages->text('misc', 'openReadme'),
                    '#',
                    '',
                    '',
                    $js
                ));
            }
            if (array_key_exists("component_integrity", $cmp) && $cmp["component_integrity"] != 0)
            {
                $h .= \HTML\p("Integrity Error " . $cmp["component_integrity"] . ": " . \UTILS\componentIntegrityErrorMessage($cmp["component_integrity"]), "error");
            }
            $h .= HTML\tdEnd();
            
            $h .= HTML\tdStart("padding5px");
            if (array_key_exists("component_authors", $cmp))
            {
                $h .= "<ul>";
                foreach ($cmp["component_authors"] as $author)
                {
                    $h .= "<li>";
                    if (array_key_exists("author_email", $author))
                    {
                        $email = \HTML\nlToHtml($author["author_email"]);
                        $author["author_email"] = \HTML\a("link", "&#x1f4e7;", "mailto:$email");
                    }
                    if (array_key_exists("author_website", $author))
                    {
                        $author["author_website"] = HTML\a("link", "&#x1f310;", $author["author_website"], "blank");
                    }
                                
                    $h .= implode(", ", $author);
                    $h .= "</li>";
                }
                $h .= "</ul>";
            }
            $h .= HTML\tdEnd();
                
            $h .= HTML\tdStart("padding5px");
            $h .= $this->messages->text("components", "installed");
            $h .= array_key_exists("component_version", $cmp) && $cmp["component_version"] != "" ? $cmp["component_version"] : "--";
            $h .= array_key_exists($hashkeyid, $cmp) && $cmp[$hashkeyid] != "" ? " <span title=\"" . WIKINDX_PACKAGE_HASH_ALGO . " hash: " . $cmp[$hashkeyid] . "\">&#x1f511;</span>" : "";
            $h .= "<br>";
            $h .= $this->messages->text("components", "latest");
            $h .= array_key_exists("component_version_latest", $cmp) && $cmp["component_version_latest"] != "" ? $cmp["component_version_latest"] : "--";
            $h .= array_key_exists($lasthashkeyid, $cmp) && $cmp[$lasthashkeyid] != "" ? " <span title=\"" . WIKINDX_PACKAGE_HASH_ALGO . " hash: " . $cmp[$lasthashkeyid] . "\">&#x1f511;</span>" : "";
            $h .= HTML\tdEnd();
                
            $h .= HTML\tdStart("padding5px");
            $h .= array_key_exists("component_licence", $cmp) ? $cmp["component_licence"] : "&nbsp;";
            $h .= HTML\tdEnd();
            
            $h .= HTML\tdStart("padding5px");
            if (array_key_exists("component_packages", $cmp))
            {
                foreach ($cmp["component_packages"] as $dlink)
                {
                    if (\UTILS\matchSuffix($dlink["package_location"], ".zip") && in_array("zip", $InstalledExtensions))
                    {
                        $h .= "" . \HTML\a("link", "&#x1f4e6;", $dlink["package_location"], "blank", $dlink["package_location"]);
                        $h .= "&nbsp;<span title=\"" . WIKINDX_PACKAGE_HASH_ALGO . " hash: " . $dlink["package_" . WIKINDX_PACKAGE_HASH_ALGO] . "\">&#x1f511;</span>";
                        $h .= "&nbsp;<span class=\"small\">zip</span>";
                        $h .= "&nbsp;<span class=\"small\">" . \FILE\formatSize($dlink["package_size"]) . "</span>";
                        $h .= "<br>";
                    }
                }
            }
            $h .= HTML\tdEnd();
                
            $h .= HTML\tdStart("padding5px");
            foreach ($cmp["component_action"] as $action => $label)
            {
                // Map an admin action name to a class function
                $actionlink = "index.php"
                    . "?action=admin_ADMINCOMPONENTS_CORE"
                    . "&amp;method=" . $action
                    . "&amp;component_type=" . $cmp["component_type"]
                    . "&amp;component_id=" . $cmp["component_id"]
                    . "&amp;dummy=" . \UTILS\uuid()
                    // Automatically return to the last modified component
                    . "#" . $cmp["component_type"] . $coutByType[$cmp["component_type"]];
                
                $h .= HTML\a("link", $label, $actionlink) . "<br>";
            }
            $h .= HTML\trEnd();
            $h .= "\n";
            $stringByType[$cmp["component_type"]] .= $h;
        }
        
        $nav = HTML\aName("topnav", "&nbsp;");
        $nav .= "Browse by type: ";
        
        foreach (array_keys($stringByType) as $type)
        {
            $aLink[] = HTML\a(
                "link",
                $type,
                "#$type"
            );
        }
        
        $nav .= implode(", ", $aLink) . ".";
        
        
        $h = "";
        
        // Status message of the last action
        if ($message)
        {
            $h .= \HTML\p($message);
            $h .= "\n";
        }
        
        // Global admin actions
        if (in_array("curl", $InstalledExtensions))
        {
            if (file_exists($this->serverComponentsListPath))
            {
                $datedl = date_create();
                date_timestamp_set($datedl, filemtime($this->serverComponentsListPath));
                $datedl = $datedl->format("c");
            }
            else
            {
                $datedl = "none";
            }
            $h .= HTML\p(\HTML\a("link", $this->messages->text("components", "checkUpdates"), "index.php?action=admin_ADMINCOMPONENTS_CORE&amp;method=checkUpdatesOnline&amp;dummy=" . \UTILS\uuid()) . " (" . $this->messages->text("components", "lastUpdate") . "&nbsp;" . $datedl . ")", "", "right");
            $h .= "\n";
        }
        
        // Action of fixing misconfigured user preferences
        if ($this->checkMisconfiguredUserPreferences($componentsInstalled))
        {
            $h .= HTML\p($this->messages->text("components", "defaultQuery")
                . " " . \HTML\a("link", $this->messages->text("components", "defaultInstall"), "index.php?action=admin_ADMINCOMPONENTS_CORE&amp;method=fixUsersPreferences&amp;dummy=" . \UTILS\uuid(), "", "right"));
            $h .= "\n";
        }
        
        // Display the upload form only if an archive format is supported
        if (in_array("zip", $InstalledExtensions))
        {
            $h .= \FORM\formMultiHeader("admin_ADMINCOMPONENTS_CORE");
            $h .= \FORM\hidden('method', 'installByUpload');
            $h .= \FORM\hidden('type', 'file');
            $h .= \FORM\hidden('dummy', \UTILS\uuid());
            
            $h .= \HTML\p($this->messages->text("components", "manualComponent"));
            $h .= \HTML\p($this->messages->text("components", "packageFile") . \FORM\fileUpload("", "packaqefile", 30));
            $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "hashFile"));
            $h .= \HTML\p($this->messages->text("components", "hashFile") . \FORM\fileUpload("", "hashfile", 30) . BR . \HTML\span($hint, 'hint'));
            $h .= \FORM\formSubmit($this->messages->text("submit", "Submit"));
        }
        
        $h .= HTML\p($nav, "", "right");
        $h .= "\n";
        
        $h .= HTML\tableStart("generalTable borderStyleSolid");
        $h .= HTML\tbodyStart();
        $h .= "\n";
        $h .= implode("", $stringByType);
        $h .= "";
        $h .= HTML\tbodyEnd();
        $h .= HTML\tableEnd();
        $h .= HTML\p("&nbsp;");
        $h .= "\n";
        $pString .= $h . "\n";
        
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * configure action
     *
     * @param false|string $message
     */
    public function configure($message = FALSE)
    {
        if (array_key_exists('component_id', $this->vars) && array_key_exists('component_type', $this->vars))
        {
            $this->possibleMenus = [
                'wikindx' => 'Wikindx',
                'res' => $this->messages->text('menu', 'res'),
                'search' => $this->messages->text('menu', 'search'),
                'text' => $this->messages->text('menu', 'text'),
                'admin' => $this->messages->text('menu', 'admin'),
                'plugin1' => $this->messages->text('menu', 'plugin1'),
                'plugin2' => $this->messages->text('menu', 'plugin2'),
                'plugin3' => $this->messages->text('menu', 'plugin3'),
            ];
            $this->possibleContainers = [
                'inline1' => 'inline1',
                'inline2' => 'inline2',
                'inline3' => 'inline3',
                'inline4' => 'inline4',
            ];
            
            $pString = '';
            if ($message)
            {
                $pString .= \HTML\p($message);
            }
            $pString .= $this->displayConfig($this->vars["component_type"], $this->vars["component_id"]);
        }
        else
        {
            $pString = $this->errors->text("components", "unknown");
        }
        
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * disable
     */
    public function disable()
    {
        $pString = '';
        if (array_key_exists('component_id', $this->vars) && array_key_exists('component_type', $this->vars))
        {
            $this->messageStringId = $this->vars['component_id'];
            $this->messageStringType = $this->vars['component_type'];
            
            if (\UTILS\disableComponent($this->vars['component_type'], $this->vars['component_id']))
            {
                $this->messageString = $this->success->text("componentSuccess");
            }
            else
            {
                $this->messageString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'disable'));
            }
        }
        else
        {
            $pString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'wrongParameters'));
        }
        
        $this->init($pString);
    }
    /**
     * enable action
     */
    public function enable()
    {
        $pString = '';
        if (array_key_exists('component_id', $this->vars) && array_key_exists('component_type', $this->vars))
        {
            $this->messageStringId = $this->vars['component_id'];
            $this->messageStringType = $this->vars['component_type'];
            
            if (\UTILS\enableComponent($this->vars['component_type'], $this->vars['component_id']))
            {
                $this->messageString = $this->success->text("componentSuccess");
            }
            else
            {
                $this->messageString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'disable'));
            }
        }
        else
        {
            $pString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'wrongParameters'));
        }
        
        $this->init($pString);
    }
    /**
     * install action
     */
    public function install()
    {
        $pString = '';
        if (array_key_exists('component_id', $this->vars) && array_key_exists('component_type', $this->vars))
        {
            $this->messageStringId = $this->vars['component_id'];
            $this->messageStringType = $this->vars['component_type'];
            $AllowUpdate = TRUE;
            $InstalledExtensions = get_loaded_extensions();
            
            // Without curl we can't download
            if (!in_array("curl", $InstalledExtensions))
            {
                $AllowUpdate = FALSE;
                $this->messageString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'missingCurl'));
            }
            // Without a decompressor we can't update
            elseif (!in_array("zip", $InstalledExtensions))
            {
                $AllowUpdate = FALSE;
                $this->messageString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'missingCompression'));
            }
            // Install
            else
            {
                $componentsRelease = \FILE\read_json_file($this->serverComponentsListPath);
                
                $rootPathByType = [
                    'plugin' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS]),
                    'style' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES]),
                    'template' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES]),
                    'vendor' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR]),
                ];
                
                
                $dlpkg = [];
                
                // Keep the packages with a compression algo supported on the current system
                foreach ($componentsRelease as $k => $cr)
                {
                    if ($cr["component_type"] == $this->vars['component_type'] && $cr["component_id"] == $this->vars['component_id'])
                    {
                        foreach ($cr["component_packages"] as $kd => $dlink)
                        {
                            if (\UTILS\matchSuffix($dlink["package_location"], ".zip") && in_array("zip", $InstalledExtensions))
                            {
                                $dlpkg[$dlink["package_size"]] = $dlink;
                            }
                        }
                    }
                }
                
                // Sort by size
                ksort($dlpkg);
                
                // Try to install the component from the smallest package to the largest
                // The first successful installation completes the operation
                foreach ($dlpkg as $pkg)
                {
                    // Purge and recreate the setup cache before each attempt
                    // for cleaning any byproduct of a failed setup
                    $pkgcachedir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE, "setup"]);
                    if (file_exists($pkgcachedir))
                    {
                        \FILE\recurse_rmdir($pkgcachedir);
                    }
                    if (!file_exists($pkgcachedir))
                    {
                        mkdir($pkgcachedir, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
                    }
                    
                    $pkgcachefile = $pkgcachedir . DIRECTORY_SEPARATOR . basename($pkg["package_location"]);
                    $cmpdstdir = $rootPathByType[$this->vars['component_type']];
                    
                    if (\UTILS\download_sf_file($pkg["package_location"], $pkgcachefile))
                    {
                        $pkghash = \UTILS\hash_path($pkgcachefile, WIKINDX_PACKAGE_HASH_ALGO);
                        if ($pkghash == $pkg["package_" . WIKINDX_PACKAGE_HASH_ALGO])
                        {
                            if (\FILE\extractComponentPackage($pkgcachefile, $cmpdstdir))
                            {
                                $this->messageString = $this->success->text("componentSuccess");
                                \FILE\rmfile($pkgcachefile);

                                break;
                            }
                            else
                            {
                                $this->messageString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'installError', basename($pkg["package_location"])));
                            }
                        }
                        else
                        {
                            $this->messageString = $this->errors->text(
                                "components",
                                'adminFailed',
                                $this->messages->text("components", 'downloadSignature', $pkg["package_location"]) .
                                $this->messages->text("components", 'corruptDownload', $pkg["package_" . WIKINDX_PACKAGE_HASH_ALGO]) .
                                $this->messages->text("components", 'computedHash', $pkghash)
                            );
                        }
                    }
                    else
                    {
                        $this->messageString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'downloadError', basename($pkg["package_location"])));
                    }
                }
            }
        }
        else
        {
            $pString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'wrongParameters'));
        }
        
        $this->init($pString);
    }
    /**
     * installByUpload action
     */
    public function installByUpload()
    {
        $rootPathByType = [
            'plugin' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS]),
            'style' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES]),
            'template' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES]),
            'vendor' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR]),
        ];
        
        $pString = "";
        
        // Purge and recreate the setup cache before each attempt
        // for cleaning any byproduct of a failed setup
        $pkgcachedir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE, "setup"]);
        if (file_exists($pkgcachedir))
        {
            \FILE\recurse_rmdir($pkgcachedir);
        }
        if (!file_exists($pkgcachedir))
        {
            mkdir($pkgcachedir, WIKINDX_UNIX_PERMS_DEFAULT, TRUE);
        }
        
        $pkghashorigin = "";
        if (isset($_FILES["hashfile"]))
        {
            if ($_FILES["hashfile"]["error"] == UPLOAD_ERR_OK)
            {
                // Get the package uploaded
                $tmp_name = $_FILES["hashfile"]["tmp_name"];
                // basename() may prevent filesystem traversal attacks;
                // further validation/sanitation of the filename may be appropriate
                $name = basename($_FILES["hashfile"]["name"]);
                $hashcachefile = $pkgcachedir . DIRECTORY_SEPARATOR . $name;
                move_uploaded_file($tmp_name, $hashcachefile);
                $pkghashorigin = file_get_contents($hashcachefile);
            }
        }
        
        if (isset($_FILES["packaqefile"]))
        {
            if ($_FILES["packaqefile"]["error"] == UPLOAD_ERR_OK)
            {
                // Get the package uploaded
                $tmp_name = $_FILES["packaqefile"]["tmp_name"];
                // basename() may prevent filesystem traversal attacks;
                // further validation/sanitation of the filename may be appropriate
                $name = basename($_FILES["packaqefile"]["name"]);
                $pkgcachefile = $pkgcachedir . DIRECTORY_SEPARATOR . $name;
                move_uploaded_file($tmp_name, $pkgcachefile);
                
                if (\UTILS\matchSuffix($pkgcachefile, ".zip"))
                {
                    // Extract its metadata
                    $pkg = \FILE\extractComponentPackageDefinition($pkgcachefile);
                    $cmpdstdir = $rootPathByType[$pkg['component_type']];
                    
                    // Verify the signature and install it
                    $pkghash = \UTILS\hash_path($pkgcachefile, WIKINDX_PACKAGE_HASH_ALGO);
                    if ($pkghash == $pkghashorigin || $pkghashorigin == "" || $pkghashorigin === FALSE)
                    {
                        if (\FILE\extractComponentPackage($pkgcachefile, $cmpdstdir))
                        {
                            $pString = $this->success->text("componentSuccess", $this->messages->text("components", "installSuccess", $name));
                            \FILE\rmfile($pkgcachefile);
                        }
                        else
                        {
                            $pString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'installError'));
                        }
                    }
                    else
                    {
                        $pString = $this->errors->text(
                            "components",
                            'adminFailed',
                            $this->messages->text("components", 'downloadSignature', $pkg["package_location"]) .
                            $this->messages->text("components", 'corruptDownload', $pkg["package_" . WIKINDX_PACKAGE_HASH_ALGO]) .
                            $this->messages->text("components", 'computedHash', $pkghash)
                        );
                    }
                }
                else
                {
                    $pString = $this->errors->text(
                        "components",
                        'adminFailed',
                        "Archive format unknown. Only ZIP is used."
                    );
                }
            }
        }
        
        if ($pString == "")
        {
            $pString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'nothingToDo'));
        }
        
        $this->init($pString);
    }
    /**
     * reinstall action
     */
    public function reinstall()
    {
        // An update action is just a disguised installation
        $this->install();
    }
    /**
     * uninstall action
     */
    public function uninstall()
    {
        $pString = '';
        if (array_key_exists('component_id', $this->vars) && array_key_exists('component_type', $this->vars))
        {
            $this->messageStringId = $this->vars['component_id'];
            $this->messageStringType = $this->vars['component_type'];
            if ($this->vars['component_type'] == "template" && $this->vars['component_id'] == "default")
            {
                $this->messageString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'uninstallError', $this->vars['component_id'] . " " . $this->vars['component_type']));
            }
            elseif ($this->vars['component_type'] == "style" && $this->vars['component_id'] == "apa")
            {
                $this->messageString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'uninstallError', $this->vars['component_id'] . " " . $this->vars['component_type']));
            }
            elseif ($this->vars['component_type'] == "vendor")
            {
                $this->messageString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'vendorUninstallError'));
            }
            else
            {
                $rootPathByType = [
                    'plugin' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS]),
                    'style' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES]),
                    'template' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES]),
                    'vendor' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR]),
                ];
                
                \FILE\recurse_rmdir($rootPathByType[$this->vars['component_type']] . DIRECTORY_SEPARATOR . $this->vars['component_id']);
                
                $pString = $this->success->text("componentSuccess");
            }
        }
        else
        {
            $pString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'wrongParameters'));
        }
        
        $this->init($pString);
    }
    /**
     * update action
     */
    public function update()
    {
        // An update action is just a disguised installation
        $this->install();
    }
    /**
     * Display README file
     */
    public function readMe()
    {
        $rootPathByType = [
            'plugin' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS]),
            'style' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES]),
            'template' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES]),
            'vendor' => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR]),
        ];
        
        $readmefile = $rootPathByType[$this->vars['type']] . DIRECTORY_SEPARATOR . $this->vars['file'] . DIRECTORY_SEPARATOR . 'README.txt';
        $pString = FALSE;
        if (file_exists($readmefile) && is_readable($readmefile))
        {
            $pString = "<pre>" . file_get_contents($readmefile) . "</pre>";
        }
        if ($pString === FALSE)
        {
            $pString = $this->errors->text('file', 'read');
        }
        $pString .= HTML\p(\FORM\closePopup($this->messages->text("misc", "closePopup")), "right");
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
    /**
     * AJAX-based DIV content creator for configuration of plugin menus
     */
    public function initConfigMenu()
    {
        list($enabledMenu) = $this->configurablePlugins();
        $div = \HTML\div('divMenu', $this->getConfigDetailsMenu($enabledMenu));
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * AJAX-based DIV content creator for configuration of inline plugins
     */
    public function initConfigInline()
    {
        list(, $enabledInline) = $this->configurablePlugins();
        $div = \HTML\div('divInline', $this->getConfigDetailsInline($enabledInline));
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Change menu plugin configuration settings
     */
    public function writeConfigMenu()
    {
        if (array_key_exists('component_id', $this->vars) && array_key_exists('component_type', $this->vars))
        {
            $this->messageStringId = $this->vars['component_id'];
            $this->messageStringType = $this->vars['component_type'];
            $config = $this->writeTempConfigFile($this->vars['configFileMenu'], $this->vars['configConfig']);
            if (!is_object($config))
            {
                $this->messageString = $config;

                return $this->init();
            }
            foreach ($config->menus as $menu)
            {
                if (array_search($menu, ['wikindx', 'res', 'search', 'text', 'admin', 'plugin1', 'plugin2', 'plugin3']) === FALSE)
                {
                    $this->messageString = $this->errors->text('components', 'invalidMenu');

                    return $this->init();
                }
            }
            $configFile = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, $this->vars['configFileMenu'], 'config.php']);
            if (file_put_contents($configFile, $this->vars['configConfig']) === FALSE)
            {
                $this->messageSTring = $this->errors->text('file', 'write');
            }
            else
            {
                $this->messageString = $this->success->text("plugins");
            }

            return $this->init();
        }
    }
    /**
     * Change inline plugin configuration settings
     */
    public function writeConfigInline()
    {
        $pString = '';
        if (array_key_exists('component_id', $this->vars) && array_key_exists('component_type', $this->vars))
        {
            $this->messageStringId = $this->vars['component_id'];
            $this->messageStringType = $this->vars['component_type'];
            $usedContainers = [];
            $array[] = $this->vars['configFileInline'];
            list($enabledMenu, $enabledInline) = $this->configurablePlugins();
            foreach ($enabledInline as $file => $null)
            {
                if ($this->vars['configFileInline'] === $file)
                {
                    continue;
                }
                include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, $file, "config.php"]));
                $configClass = $file . '_CONFIG';
                $configOrigine = new $configClass();
                if (($index = array_search($configOrigine->container, $usedContainers)) === FALSE)
                {
                    $usedContainers[$file] = $configOrigine->container;
                    $array[] = $file;
                }
            }
            $configNew = $this->writeTempConfigFile($this->vars['configFileInline'], $this->vars['configConfig']);
            if (!is_object($configNew))
            {
                $this->messageString = $configNew;

                return $this->init();
            }
            if (array_search($configNew->container, $usedContainers) !== FALSE)
            {
                $this->messageString = $this->errors->text('components', 'pluginConflict', implode(', ', $array));

                return $this->init();
            }
            if (array_search($configNew->container, ['inline1', 'inline2', 'inline3', 'inline4']) === FALSE)
            {
                $this->messageString = $this->errors->text('components', 'invalidInline');

                return $this->init();
            }
            $configFile = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, $this->vars['configFileInline'], 'config.php']);
            if (file_put_contents($configFile, $this->vars['configConfig']) === FALSE)
            {
                $this->messageString = $this->errors->text('file', 'write');
            }
            else
            {
                $this->messageString = $this->success->text("plugins");
            }
        }
        else
        {
            $pString = $this->errors->text("components", 'adminFailed', $this->messages->text("components", 'wrongParameters'));
        }
        
        $this->init($pString);
    }
    /**
     * fixUsersPreferences
     */
    public function fixUsersPreferences()
    {
        $list_tpl = [];
        $list_style = [];
        
        $componentsInstalled = \UTILS\readComponentsList();
        
        foreach ($componentsInstalled as $cmp)
        {
            if ($cmp["component_status"] == "enabled")
            {
                if ($cmp["component_type"] == "template")
                {
                    $list_tpl[] = $cmp["component_id"];
                }
                elseif ($cmp["component_type"] == "style")
                {
                    $list_style[] = $cmp["component_id"];
                }
            }
        }
        
        // Reset users templates
        $this->db->formatConditionsOneField($list_tpl, 'usersTemplate', TRUE);
        $this->db->update('users', ['usersTemplate' => WIKINDX_TEMPLATE_DEFAULT]);
        
        // Reset system template
        $sys_tpl = $this->co->getOne('configTemplate');
        if (!array_search($sys_tpl, $list_tpl) !== FALSE)
        {
            $this->co->updateOne('configTemplate', WIKINDX_TEMPLATE_DEFAULT);
        }
        
        // Reset current user template
        $this->session->delVar("setup_Template");
        
        // Reset users styles
        $this->db->formatConditionsOneField($list_style, 'usersStyle', TRUE);
        $this->db->update('users', ['usersStyle' => WIKINDX_STYLE_DEFAULT]);
        
        // Reset system style
        $sys_style = $this->co->getOne('configStyle');
        if (!array_search($sys_style, $list_style) !== FALSE)
        {
            $this->co->updateOne('configStyle', WIKINDX_STYLE_DEFAULT);
        }
        
        // Reset current user style
        $this->session->delVar("setup_Style");
        
        $this->init();
    }
    /**
     * Write new class to temp file
     *
     * @param string $componentId
     * @param string $configString
     *
     * @return array
     */
    private function writeTempConfigFile($componentId, $configString)
    {
        $id = 'a' . \UTILS\uuid(); // $id is used for the class name in the temporary file – ensure it does not begin with a number
        $tempFile = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_FILES, $id]);
        // rewrite temp class name so PHP doesn't complain about class being reused ('name is already in use'). . .
        $configString = str_replace($componentId . '_CONFIG', $id . '_CONFIG', $configString);
        if (file_put_contents($tempFile, $configString) === FALSE)
        {
            return $this->init($this->errors->text('file', 'write'));
        }
        else
        {
            $include_success = include_once($tempFile);
            if ($include_success)
            {
                $class = $id . '_CONFIG';
                if (class_exists($class))
                {
                    try
                    {
                        $config = new $class();
                        
                        if (property_exists($config, "wikindxVersion"))
                        {
                            return $config;
                        }
                        else
                        {
                            return $this->errors->text('components', 'missingConfigClassMember', '$wikindxVersion');
                        }
                    }
                    catch (Exception $e)
                    {
                        return $this->errors->text('components', 'invalidConfigLoading');
                    }
                }
                else
                {
                    return $this->errors->text('components', 'invalidConfigClassName');
                }
            }
            else
            {
                return $this->errors->text('file', 'read');
            }
        }
    }
    /**
     * Display the config settings for the plugin
     *
     * @param string $component_type
     * @param string $component_id
     *
     * @return false|string
     */
    private function displayConfig($component_type, $component_id)
    {
        list($enabledMenu, $enabledInline) = $this->configurablePlugins();
        
        foreach ($enabledMenu as $k => $v)
        {
            if ($k != $component_id)
            {
                unset($enabledMenu[$k]);
            }
        }
        
        foreach ($enabledInline as $k => $v)
        {
            if ($k != $component_id)
            {
                unset($enabledInline[$k]);
            }
        }
        
        if (empty($enabledMenu) && empty($enabledInline))
        {
            return FALSE;
        }
        $pString = '';
        
        if (!empty($enabledMenu))
        {
            $pString .= \FORM\formHeader("admin_ADMINCOMPONENTS_CORE");
            $pString .= \FORM\hidden("method", "writeConfigMenu");
            $pString .= \HTML\tableStart('');
            $pString .= \HTML\trStart();
            $pString .= \FORM\hidden("configFileMenu", $component_id);
            $pString .= \FORM\hidden("component_id", $this->vars['component_id']);
            $pString .= \FORM\hidden("component_type", $this->vars['component_type']);
            $pString .= \HTML\td(\HTML\div('divMenu', $this->getConfigDetailsMenu($enabledMenu)), 'left top width80percent');
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
            $pString .= \FORM\formEnd();
        }
        if (!empty($enabledInline))
        {
            if (!empty($enabledMenu))
            {
                $pString .= \HTML\hr();
            }
            $pString .= \FORM\formHeader("admin_ADMINCOMPONENTS_CORE");
            $pString .= \FORM\hidden("method", "writeConfigInline");
            $pString .= \HTML\tableStart();
            $pString .= \HTML\trStart();
            $pString .= \FORM\hidden("configFileInline", $component_id);
            $pString .= \FORM\hidden("component_id", $this->vars['component_id']);
            $pString .= \FORM\hidden("component_type", $this->vars['component_type']);
            $pString .= \HTML\td(\HTML\div('divInline', $this->getConfigDetailsInline($enabledInline)), 'left top width80percent');
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
            $pString .= \FORM\formEnd();
        }

        return $pString;
    }
    /**
     * return list of plugins that are configurable
     *
     * @return array
     */
    private function configurablePlugins()
    {
        $enabledMenu = $enabledInline = [];
        
        $componentsInstalled = \UTILS\readComponentsList();
        foreach ($componentsInstalled as $cmp)
        {
            if ($cmp["component_type"] == "plugin")
            {
                $type = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, $cmp["component_id"], 'plugintype.txt']);
                
                if (file_exists($type))
                {
                    if ($fh = fopen($type, "r"))
                    {
                        // read one line
                        $type = fgets($fh);
                        fclose($fh);
                    }
                }
                
                if ($type === FALSE)
                {
                    $type = '';
                }
                
                if ($type == 'menu')
                {
                    $enabledMenu[$cmp["component_id"]] = $cmp["component_name"];
                }
                elseif ($type == 'inline')
                {
                    $enabledInline[$cmp["component_id"]] = $cmp["component_name"];
                }
            }
        }

        return [$enabledMenu, $enabledInline];
    }
    /**
     * Get config details for menu plugins and put into form elements
     *
     * @param mixed $enabled
     *
     * @return string
     */
    private function getConfigDetailsMenu($enabled)
    {
        if (array_key_exists('ajaxReturn', $this->vars))
        {
            $file = $this->vars['ajaxReturn'];
        }
        else
        { // grab the first of the list
            foreach ($enabled as $file => $null)
            {
                break;
            }
        }

        $pString = '';
        
        if ($fh = fopen(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, $file, 'config.php']), "r"))
        {
            $string = '';
            while (!feof($fh))
            {
                $string .= fgets($fh);
            }
            fclose($fh);
            $pString .= \HTML\p($this->messages->text('misc', 'pluginConfigHelp'));
            $pString .= \FORM\textareaInput($this->messages->text('misc', 'pluginConfig'), 'configConfig', $string, 100, 8);
        }

        return $pString;
    }
    /**
     * Get config details for inline plugins and put into form elements
     *
     * @param mixed $enabled
     *
     * @return string
     */
    private function getConfigDetailsInline($enabled)
    {
        if (array_key_exists('ajaxReturn', $this->vars))
        {
            $file = $this->vars['ajaxReturn'];
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, $file, "config.php"]));
            $configClass = $file . '_CONFIG';
        }
        else
        { // grab the first of the list
            foreach ($enabled as $file => $null)
            {
                break;
            }
        }
        $pString = '';
        if ($fh = fopen(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, $file, 'config.php']), "r"))
        {
            $string = '';
            while (!feof($fh))
            {
                $string .= fgets($fh);
            }
            fclose($fh);
            $pString .= \HTML\p($this->messages->text('misc', 'pluginConfigHelp'));
            $pString .= \FORM\textareaInput($this->messages->text('misc', 'pluginConfig'), 'configConfig', $string, 100, 8);
        }

        return $pString;
    }
    /**
     * If templates have been disabled, check they no longer exist in user preferences.
     * If they do, replace with the first template available
     *
     * @param mixed $componentsInstalled
     *
     * @return bool
     */
    private function checkMisconfiguredUserPreferences($componentsInstalled)
    {
        $list_tpl = [];
        $list_style = [];
        
        foreach ($componentsInstalled as $cmp)
        {
            if ($cmp["component_status"] == "enabled")
            {
                if ($cmp["component_type"] == "template")
                {
                    $list_tpl[] = $cmp["component_id"];
                }
                elseif ($cmp["component_type"] == "style")
                {
                    $list_style[] = $cmp["component_id"];
                }
            }
        }
        
        $this->db->formatConditionsOneField($list_tpl, 'usersTemplate', TRUE);
        $this->db->formatConditionsOneField($list_style, 'usersStyle', TRUE);
        $recordset = $this->db->select('users', 'usersId');
        
        return ($this->db->fetchRow($recordset) !== FALSE);
    }
}
