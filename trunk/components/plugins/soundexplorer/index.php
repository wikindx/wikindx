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
 * soundexplorer class.
 */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));


class soundexplorer_MODULE
{
    public $authorize;
    public $menus;
    private $pluginmessages;
    private $coremessages;
    private $session;
    private $db;
    private $vars;
    private $scripts = [];
    private $formData;
    private $qs;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        $this->session = FACTORY_SESSION::getInstance();
        // only available for logged in users
        if ($this->session->getVar("setup_ReadOnly"))
        {
            return;
        }
        $this->db = FACTORY_DB::getInstance();
        $this->checkTables();
        $this->vars = GLOBALS::getVars();
        // plugin folder name and generic message filename
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('soundexplorer', 'soundexplorerMessages');
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $config = new soundexplorer_CONFIG();
        $this->authorize = $config->authorize;
        $this->scripts[] = WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_PLUGINS . '/' . basename(__DIR__) . '/soundExplorer.js?ver=' . WIKINDX_PUBLIC_VERSION;
        if (!array_key_exists('action', $this->vars) || (array_key_exists('action', $this->vars) && ($this->vars['action'] != 'soundexplorer_seConfigure')))
        {
            GLOBALS::setTplVar($config->container, $this->display());
        }
        if ($menuInit)
        { // portion of constructor used for menu initialisation
            return; // Need do nothing more as this is simply menu initialisation.
        }
        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
    }
    /**
     * seConfigure
     */
    public function seConfigure()
    {
        if (array_key_exists('success', $this->vars)) {
            $this->seConfigureDisplay(\HTML\p($this->pluginmessages->text($this->vars['success']), 'success', 'center'));
        } else {
            $this->seConfigureDisplay();
        }
        FACTORY_CLOSEPOPUP::getInstance();
    }
    /**
     * seToggle
     */
    public function seToggle()
    {
        $pString = $this->seConfigureDisplay();
        if (array_key_exists('seToggle', $this->vars))
        {
            if ($this->vars['seToggle'] == 'on')
            {
                $color = 'green';
                $sessionPluginState = TRUE;
            }
            elseif ($this->vars['seToggle'] == 'off')
            {
                $color = 'red';
                $sessionPluginState = FALSE;
            }

            $this->session->setVar("seplugin_On", $sessionPluginState);
            $js = "onClick=\"coreOpenPopup('index.php?action=soundexplorer_seConfigure', 90); return false\"";
            $innerHtml = base64_encode(HTML\aBrowse($color, '1em', $this->pluginmessages->text('se'), '#', '', '', $js));

            $script = LF . "<script>window.onload = seChangeStatus('$innerHtml');</script>" . LF;
            GLOBALS::addTplVar('scripts', $script);
        }
        $pString .= HTML\p($this->pluginmessages->text('seToggleSuccess'), 'success', 'center');
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
    /**
     * sepluginSearchTarget
     */
    public function sepluginSearchTarget()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "SOUNDEXPLORERQUICKSEARCH.php"]));
        $this->qs = new SOUNDEXPLORERQUICKSEARCH();
        if (!$this->vars['ajaxReturn'])
        { // i.e. key is 0 so we want a new search
            // temp store plugin status (on/off) and plugin database status
            $status = $this->session->getVar("seplugin_On");
            $dbStatus = $this->session->getVar("seplugin_DatabaseCreated");
            $foundResources = $this->session->getVar("seplugin_FoundResources");
            $this->session->clearArray("seplugin");
            $this->session->setVar("seplugin_On", $status);
            $this->session->setVar("seplugin_DatabaseCreated", $dbStatus);
            $this->session->setVar("seplugin_FoundResources", $foundResources);
            $pString = $this->qs->display();
        }
        else
        {
            $pString = $this->qs->display($this->vars['ajaxReturn']);
        }
        $div = HTML\div('sepluginSearchTarget', $pString);
        GLOBALS::addTplVar('content', AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * seStoreSearch
     */
    public function seStoreSearch()
    {
        if (array_key_exists('seplugin_SearchDelete', $this->vars))
        {
            $this->seDeleteSearch();
            // temp store plugin status (on/off) and plugin database status
            $status = $this->session->getVar("seplugin_On");
            $dbStatus = $this->session->getVar("seplugin_DatabaseCreated");
            $this->session->clearArray("seplugin");
            $this->session->setVar("seplugin_On", $status);
            $this->session->setVar("seplugin_DatabaseCreated", $dbStatus);
            $message = 'seDeleteSuccess';
        }
        else
        {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "SOUNDEXPLORERQUICKSEARCH.php"]));
            $this->qs = new SOUNDEXPLORERQUICKSEARCH();
            $error = $this->qs->checkInput();
            if (!$error)
            {
                if (array_key_exists('sepluginId', $this->vars))
                {
                    $this->seUpdateSearch();
                }
                else
                {
                    $this->seInsertSearch();
                }
                $pString = $this->seConfigureDisplay(HTML\p($this->pluginmessages->text('seStoreSuccess'), 'success', 'center'));
                $message = 'seStoreSuccess';
            }
            else
            {
                $pString = $this->seConfigureDisplay($error);
                GLOBALS::addTplVar('content', $pString);
                FACTORY_CLOSEPOPUP::getInstance();
            }
        }
        // redirect to display with success message
        header("Location: index.php?action=soundexplorer_seConfigure&success=$message");
        die;
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
            
            if (array_search('plugin_soundexplorer', $tables) === FALSE)
            {
                $this->db->queryNoError("
                    CREATE TABLE `" . WIKINDX_DB_TABLEPREFIX . "plugin_soundexplorer` (
                        `pluginsoundexplorerId` int(11) NOT NULL AUTO_INCREMENT,
                        `pluginsoundexplorerUserId` int(11) NOT NULL,
                        `pluginsoundexplorerLabel` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
                        `pluginsoundexplorerArray` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
                        PRIMARY KEY (`pluginsoundexplorerId`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
    			");
            }
            
            \UPDATE\setPluginInternalVersion($this->db, mb_strtolower(basename(__DIR__)), 1);
        }
    }
    /**
     * display
     *
     * @return string
     */
    private function display()
    {
        $display = '';
        if ($this->session->getVar("seplugin_On"))
        {
            $seColor = 'green';
        }
        else
        {
            $seColor = 'red';
        }
        $return = $this->soundList();
        if (is_array($return))
        {
            $this->scriptIncludes($return);
        }
        else
        {
            $this->scriptIncludes();
        }
        $js = "onClick=\"coreOpenPopup('index.php?action=soundexplorer_seConfigure', 90); return false\"";
        $display .= HTML\div("soundExplorerStatus", HTML\aBrowse($seColor, '1em', $this->pluginmessages->text('se'), '#', '', '', $js));

        return $display;
    }
    /**
     * Play a sound if stored search found in list
     *
     * @return bool
     */
    private function soundList()
    {
        // run if on FRONT page or displaying results of a list operation
        if (
            $this->session->getVar("seplugin_On")
            && (
                !array_key_exists('action', $this->vars) // FRONT
                || $this->session->getVar("list_On")
                || (
                    array_key_exists('action', $this->vars)
                    && array_key_exists('method', $this->vars)
                    && (mb_strpos($this->vars['action'], 'list_') === 0)
                    && (($this->vars['method'] == 'process') || ($this->vars['method'] == 'reprocess'))
                )
            )
        ) {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "SOUNDEXPLORERQUICKSEARCH.php"]));
            $this->qs = new SOUNDEXPLORERQUICKSEARCH();
            $return = $this->qs->process();
            if (is_array($return))
            {
                return $return;
            }
        }
        $this->session->delVar("seplugin_FoundResources");

        return FALSE;
    }
    /**
     * configure
     *
     * @ param string $message
     *
     * @param mixed $message
     */
    private function seConfigureDisplay($message = FALSE)
    {
        $this->scriptIncludes();
        AJAX\loadJavascript();
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('se'));
        $pString = HTML\p($this->pluginmessages->text('seExplain'));
        if ($this->session->getVar("seplugin_On"))
        {
            $selected = 'on';
        }
        else
        {
            $selected = 'off';
        }
        $selectArray = ['on' => $this->pluginmessages->text('seOn'), 'off' => $this->pluginmessages->text('seOff')];
        $pString .= HTML\tableStart();
        $pString .= HTML\trStart();
        $td = FORM\formHeader("soundexplorer_seToggle");
        $td .= FORM\selectedBoxValue(FALSE, "seToggle", $selectArray, $selected, 1);
        $td .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $td .= FORM\formEnd();
        $pString .= HTML\td($td);
        if ($this->session->getVar("seplugin_FoundResources"))
        {
            $pString .= HTML\td('&nbsp;');
            $rc = FACTORY_RESOURCECOMMON::getInstance();
            $bibStyle = FACTORY_BIBSTYLE::getInstance();
            $pString .= HTML\tdStart();
            $pString .= HTML\tableStart('generalTable borderStyleSolid');
            $pString .= HTML\trStart();
            $pString .= HTML\td(HTML\strong(str_replace(' ', '&nbsp;', $this->pluginmessages->text("seMatchedSearches"))));
            $pString .= HTML\td('&nbsp;');
            $pString .= HTML\trEnd();
            foreach ($this->session->getVar("seplugin_FoundResources") as $label => $ids)
            {
                $thisLabelPrinted = FALSE;
                $resultset = $rc->getResource($ids);
                while ($row = $this->db->fetchRow($resultset))
                {
                    $pString .= HTML\trStart();
                    if (!$thisLabelPrinted)
                    {
                        $pString .= HTML\td(HTML\em($label));
                        $thisLabelPrinted = TRUE;
                    }
                    else
                    {
                        $pString .= HTML\td('&nbsp;');
                    }
                    $pString .= HTML\td($bibStyle->process($row));
                    $pString .= HTML\trEnd();
                }
            }
            $pString .= HTML\tableEnd();
            $pString .= HTML\tdEnd();
        }
        $pString .= HTML\trEnd() . HTML\tableEnd();
        $pString .= HTML\hr();
        $pString .= FORM\formHeader(FALSE);
        $this->db->formatConditions(['pluginsoundexplorerUserId' => $this->session->getVar("setup_UserId")]);
        $resultset = $this->db->select('plugin_soundexplorer', ['pluginsoundexplorerId', 'pluginsoundexplorerLabel']);
        $searches = [0 => $this->pluginmessages->text('seNewSearch')];
        while ($row = $this->db->fetchRow($resultset))
        {
            $searches[$row['pluginsoundexplorerId']] = $row['pluginsoundexplorerLabel'];
        }
        $jScript = 'index.php?action=soundexplorer_sepluginSearchTarget';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'seType',
            'targetDiv' => 'sepluginSearchTarget',
        ];
        $js = AJAX\jActionForm('onchange', $jsonArray);
        array_key_exists('sepluginId', $this->vars) ? $id = $this->vars['sepluginId'] : $id = FALSE;
        $size = count($searches);
        if ($size > 5)
        {
            $size = 5;
        }
        if (!$id)
        {
            $pString .= FORM\selectFBoxValue(FALSE, "seType", $searches, $size, FALSE, $js);
        }
        else
        {
            $pString .= FORM\selectedBoxValue(FALSE, "seType", $searches, $id, $size, FALSE, $js);
        }
        $pString .= FORM\formEnd();
        $pString .= HTML\hr();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "SOUNDEXPLORERQUICKSEARCH.php"]));
        $this->qs = new SOUNDEXPLORERQUICKSEARCH();
        $pString .= HTML\div('sepluginSearchTarget', $this->qs->display($id, $message));
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * seDeleteSearch
     */
    private function seDeleteSearch()
    {
        $this->db->formatConditions(['pluginsoundexplorerId' => $this->vars['sepluginId']]);
        $this->db->delete('plugin_soundexplorer');
    }
    /**
     * seUpdateSearch
     */
    private function seUpdateSearch()
    {
        $array['pluginsoundexplorerLabel'] = $this->qs->formData["Label"];
        $array['pluginsoundexplorerArray'] = $this->seArrayToDatabase();
        $this->db->formatConditions(['pluginsoundexplorerId' => $this->vars['sepluginId']]);
        $this->db->update('plugin_soundexplorer', $array);
    }
    /**
     * seInsertSearch
     */
    private function seInsertSearch()
    {
        $fields[] = 'pluginsoundexplorerUserId';
        $values[] = $this->session->getVar("setup_UserId");
        $fields[] = 'pluginsoundexplorerLabel';
        $values[] = $this->qs->formData["Label"];
        $fields[] = 'pluginsoundexplorerArray';
        $values[] = $this->seArrayToDatabase();
        $this->db->insert('plugin_soundexplorer', $fields, $values);
    }
    /**
     * seArrayToDatabase
     *
     * @return string
     */
    private function seArrayToDatabase()
    {
        foreach ($this->qs->formData as $key => $value)
        {
            if (($key == 'On') || ($key == 'Label') || ($key == 'DatabaseCreated'))
            {
                continue;
            }
            $array[$key] = $value;
        }

        return base64_encode(serialize($array));
    }
    /**
     * scriptIncludes
     *
     * @param array|false $array
     */
    private function scriptIncludes($array = FALSE)
    {
        $scriptInsert = '';

        foreach ($this->scripts as $script)
        {
            $scriptInsert .= '<script src="' . $script . '"></script>';
        }

        if (is_array($array))
        {
            $waves = "['" . implode("', '", $array) . "']";
            $scriptInsert .= '<script>window.onload = sePlay(' . $waves . ');</script>';
        }

        GLOBALS::addTplVar('scripts', $scriptInsert);
    }
}
