<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
 
/**
  * importexportbib class.
  *
  * Import/export of bibligraphies.
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");


class importexportbib_MODULE
{
    public $authorize;
    public $menus;
    private $vars;
    private $pluginmessages;
    private $session;
    private $db;
    private $configImport;
    private $bibutilsImport;
    private $common;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        $this->session = FACTORY_SESSION::getInstance();
        include_once("core/messages/PLUGINMESSAGES.php");
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "config.php");
        $this->bibutilsImport = new importexportbib_BIBUTILSCONFIG();
        $configExport = new importexportbib_EXPORTCONFIG();
        $this->configImport = new importexportbib_IMPORTCONFIG();
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "EXPORTCOMMON.php");
        $this->common = new EXPORTCOMMON();
        $this->db = FACTORY_DB::getInstance();
        if (!$this->configImport->bibutilsPath)
        {
            $this->configImport->bibutilsPath = '/usr/local/bin/'; // default *NIX location
        }
        if (!$this->bibutilsImport->bibutilsPath)
        {
            $this->bibutilsImport->bibutilsPath = '/usr/local/bin/'; // default *NIX location
        }

        $authorize = FACTORY_AUTHORIZE::getInstance();

        if ($authorize->isPluginExecutionAuthorised($this->bibutilsImport->authorize))
        { // if TRUE, we're authorised so display menus
            $this->authorize = $this->bibutilsImport->authorize;
            if ($menuInit)
            { // portion of constructor used for menu initialisation
                $this->makeMenu($this->bibutilsImport->menus, 'bibutils');
            }
        }
        if ($this->common->setIdeasCondition())
        {
            if ($this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId')))
            {
                $this->menus[$this->configImport->menus[0]]['importexportbibpluginSub'][$this->pluginmessages->text('menuIdeaExport')] =
                    'initIdeaExport';
            }
        }
        if ($authorize->isPluginExecutionAuthorised($configExport->authorize))
        { // if TRUE, we're authorised so display menus
            $this->authorize = $configExport->authorize;
            if ($menuInit)
            { // portion of constructor used for menu initialisation
                $this->makeMenu($configExport->menus, 'export');
            }
        }
        if ($authorize->isPluginExecutionAuthorised($this->configImport->authorize))
        { // if TRUE, we're authorised so display menus
            $this->authorize = $this->configImport->authorize;
            if ($menuInit)
            { // portion of constructor used for menu initialisation
                $this->makeMenu($this->configImport->menus, 'import');
            }
        }
        if ($menuInit)
        {
            return; // Need do nothing more as this is simply menu initialisation.
        }

        $this->vars = GLOBALS::getVars();
    }
    /**
     * listFiles
     *
     * @param string|FALSE $message
     * @param string|FALSE $errorMethod
     *
     * @return string
     */
    public function listFiles($message = FALSE, $errorMethod = FALSE)
    {
        $errors = FACTORY_ERRORS::getInstance();
        // Perform some system admin
        FILE\tidyFiles();
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerListFiles"));
        list($dirName, $deletePeriod, $fileArray) = FILE\listFiles();

        if (!$dirName)
        {
            if (!$fileArray)
            {
                GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text("noContents"), 'error'));
            }
            elseif (!$errorMethod)
            {
                GLOBALS::addTplVar('content', $errors->text('file', "read"));
            }
            else
            {
                $this->{$errorMethod}($errors->text("file", "read"));
            }

            return;
        }
        $pString = $message ? $message : FALSE;
        $filesDir = TRUE;
        $pString .= HTML\p($this->pluginmessages->text("contents"));
        $minutes = $deletePeriod / 60;
        if (!empty($fileArray))
        {
            foreach ($fileArray as $key => $value)
            {
                $pString .= date(DateTime::W3C, filemtime($dirName . DIRECTORY_SEPARATOR . $key)) . ': ';
                $pString .= HTML\a("link", $key, "index.php?action=importexportbib_downloadFile" .
                htmlentities("&filename=" . $key), "_blank") . BR . LF;
            }
        }
        $pString .= HTML\hr();
        $pString .= HTML\p($this->pluginmessages->text("warning", " $minutes "));
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * downloadFile
     */
    public function downloadFile()
    {
        $dirName = WIKINDX_DIR_DATA_FILES;
        $filename = $this->vars['filename'];
        $filepath = $dirName . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($filepath))
        {
            switch (pathinfo($filepath)['extension'])
            {
                case 'bib':
                    $type = WIKINDX_MIMETYPE_BIB;
                    $charset = 'UTF-8';

                break;
                case 'html':
                    $type = WIKINDX_MIMETYPE_HTM;
                    $charset = 'UTF-8';

                break;
                case 'ris':
                    $type = WIKINDX_MIMETYPE_RIS;
                    $charset = 'UTF-8';

                break;
                case 'rtf':
                    $type = WIKINDX_MIMETYPE_RTF;
                    $charset = 'Windows-1252';

                break;
                case 'xml':
                    $type = WIKINDX_MIMETYPE_ENDNOTE;
                    $charset = 'UTF-8';

                break;
            }
            $size = filesize($filepath);
            $lastmodified = date(DateTime::RFC1123, filemtime($filepath));
            FILE\setHeaders($type, $size, $filename, $lastmodified, $charset);
            FILE\readfile_chunked($filepath);
        }
        else
        {
            $this->badInput->closeType = 'closePopup';
            $this->badInput->close($this->errors->text("file", "missing"));
        }
        die;
    }
    /**
     * initEndnoteImport
     *
     * @param string|FALSE $message
     */
    public function initEndnoteImport($message = FALSE)
    {
        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->configImport->authorize))
        { // if FALSE, we're not authorised
            $auth = FACTORY_AUTHORIZE::getInstance();
            $auth->initLogon();
            FACTORY_CLOSE::getInstance();
        }
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "ENDNOTE.php");
        $endnote = new ENDNOTE($this);
        $this->session->delVar("importLock");
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerEndnoteImport"));
        $this->session->clearArray("import");
        GLOBALS::addTplVar('content', $message . $endnote->displayImport());
    }
    /**
     * initPubMedImport
     *
     * @param string|FALSE $message
     */
    public function initPubMedImport($message = FALSE)
    {
        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->configImport->authorize))
        { // if FALSE, we're not authorised
            $auth = FACTORY_AUTHORIZE::getInstance();
            $auth->initLogon();
            FACTORY_CLOSE::getInstance();
        }
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "PUBMED.php");
        $pubmed = new PUBMED($this);
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'wikindx'))
        {
            GLOBALS::addTplVar('content', AJAX\encode_jArray(['innerHTML' => $pubmed->wikindxImportFields()]));
            FACTORY_CLOSERAW::getInstance();

            return;
        }
        $this->session->delVar("importLock");
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerPubMedImport"));
        $pString = $message ? $message : FALSE;
        $pString .= $pubmed->displayImport();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * initBibutils
     *
     * @param string|FALSE $message
     */
    public function initBibutils($message = FALSE)
    {
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "BIBUTILS.php");
        $bibutils = new BIBUTILS($this);
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'ajax'))
        {
            $div = $bibutils->createOutputTypes();
            GLOBALS::addTplVar('content', AJAX\encode_jArray(['innerHTML' => $div]));
            FACTORY_CLOSERAW::getInstance();
        }
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerBibutils"));
        $pString = $message ? $message : FALSE;
        $pString .= $bibutils->init();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * initEndnoteExportB
     */
    public function initEndnoteExportB()
    {
        $this->session->setVar("exportBasket", TRUE);
        $this->initEndnoteExport();
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * initEndnoteExportL
     */
    public function initEndnoteExportL($message = FALSE)
    {
        $this->session->delVar("exportBasket", TRUE);
        GLOBALS::addTplVar('content', $message);
        $this->initEndnoteExport();
    }
    /**
     * initEndnoteExport
     *
     * @param string|FALSE $message
     */
    public function initEndnoteExport($message = FALSE)
    {
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "ENDNOTE.php");
        $endnote = new ENDNOTE($this);
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerEndnoteExport"));
        $pString = $message ? $message : FALSE;
        $pString .= $endnote->displayExport();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * initRtfExportB
     */
    public function initRtfExportB()
    {
        $this->session->setVar("exportBasket", TRUE);
        $this->initRtfExport();
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * initRtfExportL
     */
    public function initRtfExportL()
    {
        $this->session->delVar("exportBasket", TRUE);
        $this->initRtfExport();
    }
    /**
     * initRtfExport
     *
     * @param string|FALSE $message
     */
    public function initRtfExport($message = FALSE)
    {
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "RTF.php");
        $rtf = new RTF($this);
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerRtfExport"));
        $pString = $message ? $message : FALSE;
        $pString .= $rtf->display();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * initBibtexExportB
     */
    public function initBibtexExportB()
    {
        $this->session->setVar("exportBasket", TRUE);
        $this->initBibtexExport();
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * initBibtexExportL
     */
    public function initBibtexExportL()
    {
        $this->session->delVar("exportBasket", TRUE);
        $this->initBibtexExport();
    }
    /**
     * initBibtexExport
     *
     * @param string|FALSE $message
     */
    public function initBibtexExport($message = FALSE)
    {
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "BIBTEX.php");
        $bibtex = new BIBTEX();
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerBibtexExport"));
        $pString = $message ? $message : FALSE;
        $pString .= $bibtex->exportOptions();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * initHtmlExportB
     */
    public function initHtmlExportB()
    {
        $this->session->setVar("exportBasket", TRUE);
        $this->initHtmlExport();
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * initHtmlExportL
     */
    public function initHtmlExportL()
    {
        $this->session->delVar("exportBasket", TRUE);
        $this->initHtmlExport();
    }
    /**
     * initHtmlExport
     *
     * @param string|FALSE $message
     */
    public function initHtmlExport($message = FALSE)
    {
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "HTML.php");
        $htmlExport = new HTMLEXPORT();
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerHtmlExport"));
        $pString = $message ? $message : FALSE;
        $pString .= $htmlExport->exportOptions();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * importEndnote
     */
    public function importEndnote()
    {
        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->configImport->authorize))
        { // if FALSE, we're not authorised
            $auth = FACTORY_AUTHORIZE::getInstance();
            $auth->initLogon();
            FACTORY_CLOSE::getInstance();
        }
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerEndnoteImport"));
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'process'))
        {
            include_once(__DIR__ . DIRECTORY_SEPARATOR . 'ENDNOTEIMPORT.php');
            $endnote = new ENDNOTEIMPORT($this);
            $endnote->process();
        }
        elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'continueImport'))
        {
            include_once(__DIR__ . DIRECTORY_SEPARATOR . 'ENDNOTEIMPORT.php');
            $endnote = new ENDNOTEIMPORT($this);
            $endnote->continueImport();
        }
        elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'stage2Invalid'))
        {
            include_once(__DIR__ . DIRECTORY_SEPARATOR . 'ENDNOTEIMPORT.php');
            $endnote = new ENDNOTEIMPORT($this);
            $endnote->stage2Invalid();
        }
    }
    /**
     * importPubMed
     */
    public function importPubMed()
    {
        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->configImport->authorize))
        { // if FALSE, we're not authorised
            $auth = FACTORY_AUTHORIZE::getInstance();
            $auth->initLogon();
            FACTORY_CLOSE::getInstance();
        }
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'process'))
        {
            GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerPubMedImport"));
            include_once(__DIR__ . DIRECTORY_SEPARATOR . 'PUBMED.php');
            $pubmed = new PUBMED($this);
            $pubmed->processPubMed();
        }
    }
    /**
     * exportEndnote
     *
     * @param string|FALSE $message
     */
    public function exportEndnote($message = FALSE)
    {
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        ini_set('memory_limit', '-1');
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerEndnoteExport"));
        $pString = $message ? $message : FALSE;
        include_once(__DIR__ . DIRECTORY_SEPARATOR . 'ENDNOTEEXPORT.php');
        $endnote = new ENDNOTEEXPORT($this);
        $endnote->process();
        GLOBALS::addTplVar('content', $pString);
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * exportRtf
     *
     * @param string|FALSE $message
     */
    public function exportRtf($message = FALSE)
    {
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        ini_set('memory_limit', '-1');
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerRtfExport"));
        $pString = $message ? $message : FALSE;
        //GLOBALS::addTplVar('content', 'under construction'); return;
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'process'))
        {
            include_once(__DIR__ . DIRECTORY_SEPARATOR . 'RTFEXPORT.php');
            $rtf = new RTFEXPORT($this);
            $pString .= $rtf->process();
        }
        GLOBALS::addTplVar('content', $pString);
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * exportBibtex
     *
     * @param string|FALSE $message
     */
    public function exportBibtex($message = FALSE)
    {
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        ini_set('memory_limit', '-1');
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerBibtexExport"));
        $pString = $message ? $message : FALSE;
        //GLOBALS::addTplVar('content', 'under construction'); return;
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'process'))
        {
            include_once(__DIR__ . DIRECTORY_SEPARATOR . "BIBTEX.php");
            $bibtex = new BIBTEX($this);
            $pString .= $bibtex->processExport();
        }
        GLOBALS::addTplVar('content', $pString);
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * exportHtml
     *
     * @param string|FALSE $message
     */
    public function exportHtml($message = FALSE)
    {
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        ini_set('memory_limit', '-1');
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerHtmlExport"));
        $pString = $message ? $message : FALSE;
        //GLOBALS::addTplVar('content', 'under construction'); return;
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'process'))
        {
            include_once(__DIR__ . DIRECTORY_SEPARATOR . "HTML.php");
            $htmlExport = new HTMLEXPORT($this);
            $pString .= $htmlExport->processExport();
        }
        GLOBALS::addTplVar('content', $pString);
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * initRisExportB
     */
    public function initRisExportB()
    {
        $this->session->setVar("exportBasket", TRUE);
        $this->initRisExport();
    }
    /**
     * initRisExportL
     */
    public function initRisExportL()
    {
        $this->session->delVar("exportBasket", TRUE);
        $this->initRisExport();
    }
    /**
     * initRisExport
     *
     * @param string|FALSE $message
     */
    public function initRisExport($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerRisExport"));
        $pString = $message ? $message : FALSE;
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "RIS.php");
        $ris = new RIS($this);
        $ris->process();
        GLOBALS::clearTplVar('pagingList');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * initIdeaExport
     *
     * @param string|FALSE $message
     */
    public function initIdeaExport($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerIdeaExport"));
        $pString = $message ? $message : FALSE;
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "IDEA.php");
        $idea = new IDEA($this);
        $pString .= $idea->exportOptions();
        GLOBALS::clearTplVar('pagingList');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * exportIdea
     *
     * @param string|FALSE $message
     */
    public function exportIdea($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerIdeaExport"));
        $pString = $message ? $message : FALSE;
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "IDEAEXPORT.php");
        $idea = new IDEAEXPORT($this);
        $pString .= $idea->process();
        GLOBALS::clearTplVar('pagingList');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * processBibutils
     */
    public function processBibutils()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerBibutils"));
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "BIBUTILS.php");
        $bibutils = new BIBUTILS($this);
        $pString = $bibutils->startProcess();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * makeMenu
     *
     * @param array $menuArray
     * @param string $type
    */
    private function makeMenu($menuArray, $type)
    {
        if (empty($this->menus))
        { // initialization of menu structure
            $this->menus = [
                $menuArray[0] => ['importexportbibpluginSub' => [
                    $this->pluginmessages->text('menu') => FALSE,
                ],
                ],
            ];
        }
        if (($type == 'bibutils') && (FILE\command_exists($this->configImport->bibutilsPath . 'bib2xml')))
        {
            $this->menus[$menuArray[0]]['importexportbibpluginSub'][$this->pluginmessages->text('menuBibutils')] = "initBibutils";
        }
        elseif (($type == 'export') && ($this->session->getVar("sql_LastMulti") || $this->session->getVar("basket_List")))
        {
            if ($this->session->getVar("sql_LastMulti") && $this->session->getVar("basket_List"))
            {
                $array = [$this->pluginmessages->text('menuRtfExport') => FALSE,
                    $this->pluginmessages->text('menuExportBasket') => "initRtfExportB",
                    $this->pluginmessages->text('menuExportList') => "initRtfExportL", ];
            }
            elseif ($this->session->getVar("basket_List"))
            {
                $array = [$this->pluginmessages->text('menuRtfExport') => FALSE,
                    $this->pluginmessages->text('menuExportBasket') => "initRtfExportB", ];
            }
            elseif ($this->session->getVar("sql_LastMulti"))
            {
                $array = [$this->pluginmessages->text('menuRtfExport') => FALSE,
                    $this->pluginmessages->text('menuExportList') => "initRtfExportL", ];
            }
            $this->menus[$menuArray[0]]['importexportbibpluginSub'][] = $array;

            if ($this->session->getVar("sql_LastMulti") && $this->session->getVar("basket_List"))
            {
                $array = [$this->pluginmessages->text('menuBibtexExport') => FALSE,
                    $this->pluginmessages->text('menuExportBasket') => "initBibtexExportB",
                    $this->pluginmessages->text('menuExportList') => "initBibtexExportL", ];
            }
            elseif ($this->session->getVar("basket_List"))
            {
                $array = [$this->pluginmessages->text('menuBibtexExport') => FALSE,
                    $this->pluginmessages->text('menuExportBasket') => "initBibtexExportB", ];
            }
            elseif ($this->session->getVar("sql_LastMulti"))
            {
                $array = [$this->pluginmessages->text('menuBibtexExport') => FALSE,
                    $this->pluginmessages->text('menuExportList') => "initBibtexExportL", ];
            }
            $this->menus[$menuArray[0]]['importexportbibpluginSub'][] = $array;

            if ($this->session->getVar("sql_LastMulti") && $this->session->getVar("basket_List"))
            {
                $array = [$this->pluginmessages->text('menuHtmlExport') => FALSE,
                    $this->pluginmessages->text('menuExportBasket') => "initHtmlExportB",
                    $this->pluginmessages->text('menuExportList') => "initHtmlExportL", ];
            }
            elseif ($this->session->getVar("basket_List"))
            {
                $array = [$this->pluginmessages->text('menuHtmlExport') => FALSE,
                    $this->pluginmessages->text('menuExportBasket') => "initHtmlExportB", ];
            }
            elseif ($this->session->getVar("sql_LastMulti"))
            {
                $array = [$this->pluginmessages->text('menuHtmlExport') => FALSE,
                    $this->pluginmessages->text('menuExportList') => "initHtmlExportL", ];
            }
            $this->menus[$menuArray[0]]['importexportbibpluginSub'][] = $array;

            if ($this->session->getVar("sql_LastMulti") && $this->session->getVar("basket_List"))
            {
                $array = [$this->pluginmessages->text('menuEndnoteExport') => FALSE,
                    $this->pluginmessages->text('menuExportBasket') => "initEndnoteExportB",
                    $this->pluginmessages->text('menuExportList') => "initEndnoteExportL", ];
            }
            elseif ($this->session->getVar("basket_List"))
            {
                $array = [$this->pluginmessages->text('menuEndnoteExport') => FALSE,
                    $this->pluginmessages->text('menuExportBasket') => "initEndnoteExportB", ];
            }
            elseif ($this->session->getVar("sql_LastMulti"))
            {
                $array = [$this->pluginmessages->text('menuEndnoteExport') => FALSE,
                    $this->pluginmessages->text('menuExportList') => "initEndnoteExportL", ];
            }
            $this->menus[$menuArray[0]]['importexportbibpluginSub'][] = $array;

            if ($this->session->getVar("sql_LastMulti") && $this->session->getVar("basket_List"))
            {
                $array = [$this->pluginmessages->text('menuRisExport') => FALSE,
                    $this->pluginmessages->text('menuExportBasket') => "initRisExportB",
                    $this->pluginmessages->text('menuExportList') => "initRisExportL", ];
            }
            elseif ($this->session->getVar("basket_List"))
            {
                $array = [$this->pluginmessages->text('menuRisExport') => FALSE,
                    $this->pluginmessages->text('menuExportBasket') => "initRisExportB", ];
            }
            elseif ($this->session->getVar("sql_LastMulti"))
            {
                $array = [$this->pluginmessages->text('menuRisExport') => FALSE,
                    $this->pluginmessages->text('menuExportList') => "initRisExportL", ];
            }
            $this->menus[$menuArray[0]]['importexportbibpluginSub'][] = $array;

            if ($this->session->getVar("fileExports"))
            {
                $this->menus[$menuArray[0]]['importexportbibpluginSub'][$this->pluginmessages->text('menuListFiles')] = 'listFiles';
            }
        }
        elseif ($type == 'import')
        { // import
            $this->menus[$menuArray[0]]['importexportbibpluginSub'][$this->pluginmessages->text('menuEndnoteImport')] = "initEndnoteImport";
            if (FILE\command_exists($this->configImport->bibutilsPath . 'med2xml'))
            {
                $this->menus[$menuArray[0]]['importexportbibpluginSub'][$this->pluginmessages->text('menuPubMedImport')] = "initPubMedImport";
            }
        }
    }
}
