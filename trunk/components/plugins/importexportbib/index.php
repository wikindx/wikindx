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
  * importexportbib class.
  *
  * Import/export of bibligraphies.
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));


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
    private $browserTabID = FALSE;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {return; // while shifting plugin to core . . .
        $this->session = FACTORY_SESSION::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $this->bibutilsImport = new importexportbib_BIBUTILSCONFIG();
        $configExport = new importexportbib_EXPORTCONFIG();
        $this->configImport = new importexportbib_IMPORTCONFIG();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "EXPORTCOMMON.php"]));
        $this->common = new EXPORTCOMMON();
        $this->db = FACTORY_DB::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
        if (!$this->configImport->bibutilsPath)
        {
//            $this->configImport->bibutilsPath = '/usr/local/bin/'; // default *NIX location
// Almost all *NIX have bibutils packaged and in the PATH
			$this->configImport->bibutilsPath = '';
        }
        if (!$this->bibutilsImport->bibutilsPath)
        {
//            $this->bibutilsImport->bibutilsPath = '/usr/local/bin/'; // default *NIX location
// Almost all *NIX have bibutils packaged and in the PATH
			$this->bibutilsImport->bibutilsPath = '';
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
     * @param false|string $message
     * @param false|string $errorMethod
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
        if (array_key_exists('uuid', $this->vars))
        {
            $data = \TEMPSTORAGE\fetch($this->db, $this->vars['uuid']);
            if (is_array($data))
            { // FALSE if no longer there (reloading page e.g.)
                \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
                $message = $data['message'];
            }
        }
        $pString = $message;
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
     * /**
     * downloadFile
     */
    public function downloadFile()
    {
        $filepath = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES, $this->vars['filename']]);
        if (file_exists($filepath))
        {
            switch (pathinfo($filepath)['extension']) {
                case 'bib':
                    $type = WIKINDX_MIMETYPE_BIB;
                    $charset = 'UTF-8';

                break;
                case 'html':
                    $type = WIKINDX_MIMETYPE_HTML;
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
            FILE\setHeaders($type, $size, basename($filepath), $lastmodified, $charset);
            FILE\readfile_chunked($filepath);
        }
        else
        {
            header('HTTP/1.0 404 Not Found');
            $this->badInput->closeType = 'closePopup';
            $this->badInput->close($this->errors->text("file", "missing"));
        }
        die;
    }
    /**
     * initEndnoteImport
     *
     * @param false|string $message
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
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "ENDNOTE.php"]));
        $endnote = new ENDNOTE($this);
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerImportEndnote"));
        $pString = $message ? $message : FALSE;
        $pString .= HTML\p($this->pluginmessages->text('introEndnoteImport'));
        $endnote->displayImport($pString);
    }
    /**
     * initPubMedImport
     *
     * @param false|string $message
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
        if (!\FILE\command_exists($this->configImport->bibutilsPath . 'med2xml')) {
			GLOBALS::setTplVar('heading', $this->pluginmessages->text("hheaderImportPubMedh"));
			$pString = \HTML\p($this->pluginmessages->text("bibutilsnoPrograms", $this->configImport->bibutilsPath), "error", "center");
			$pString .= \HTML\p($this->pluginmessages->text("importPubMedNoBibutils"));
			$pString .= \HTML\p($this->pluginmessages->text("bibutilscredit", \HTML\a(
				"link",
				'Bibutils',
				'https://sourceforge.net/p/bibutils/home/Bibutils/',
				'_blank'
			)));
			GLOBALS::addTplVar('content', $pString);
            FACTORY_CLOSE::getInstance();
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "PUBMED.php"]));
        $pubmed = new PUBMED($this);
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'wikindx'))
        {
            GLOBALS::addTplVar('content', AJAX\encode_jArray(['innerHTML' => $pubmed->wikindxImportFields()]));
            FACTORY_CLOSERAW::getInstance();

            return;
        }
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("hheaderImportPubMedh"));
        $pString = $message ? $message : FALSE;
        $pString .= $pubmed->displayImport();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * initBibutils
     *
     * @param false|string $message
     */
    public function initBibutils($message = FALSE)
    {
    	if (!\FILE\command_exists($this->configImport->bibutilsPath . 'bib2xml')) {
			GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerBibutils"));
			$pString = \HTML\p($this->pluginmessages->text("bibutilsnoPrograms", $this->configImport->bibutilsPath), "error", "center");
			$pString .= \HTML\p($this->pluginmessages->text("bibutilscredit", \HTML\a(
				"link",
				'Bibutils',
				'https://sourceforge.net/p/bibutils/home/Bibutils/',
				'_blank'
			)));
			GLOBALS::addTplVar('content', $pString);
            FACTORY_CLOSE::getInstance();
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "BIBUTILS.php"]));
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
     *
     * @param mixed $message
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
     * @param false|string $message
     */
    public function initEndnoteExport($message = FALSE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "ENDNOTE.php"]));
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
     * @param false|string $message
     */
    public function initRtfExport($message = FALSE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "RTF.php"]));
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
     * @param false|string $message
     */
    public function initBibtexExport($message = FALSE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "BIBTEX.php"]));
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
     * @param false|string $message
     */
    public function initHtmlExport($message = FALSE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "HTML.php"]));
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
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerImportEndnote"));
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
            GLOBALS::setTplVar('heading', $this->pluginmessages->text("hheaderImportPubMedh"));
            include_once(__DIR__ . DIRECTORY_SEPARATOR . 'PUBMED.php');
            $pubmed = new PUBMED($this);
            $pubmed->processPubMed();
        }
    }
    /**
     * exportEndnote
     *
     * @param false|string $message
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
     * @param false|string $message
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
     * @param false|string $message
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
     * @param false|string $message
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
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "HTML.php"]));
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
     * @param false|string $message
     */
    public function initRisExport($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerRisExport"));
        $pString = $message ? $message : FALSE;
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "RIS.php"]));
        $ris = new RIS($this);
        $ris->process();
        GLOBALS::clearTplVar('pagingList');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * initIdeaExport
     *
     * @param false|string $message
     */
    public function initIdeaExport($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerIdeaExport"));
        $pString = $message ? $message : FALSE;
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "IDEA.php"]));
        $idea = new IDEA($this);
        $pString .= $idea->exportOptions();
        GLOBALS::clearTplVar('pagingList');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * exportIdea
     *
     * @param false|string $message
     */
    public function exportIdea($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerIdeaExport"));
        $pString = $message ? $message : FALSE;
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "IDEAEXPORT.php"]));
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
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "BIBUTILS.php"]));
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
                $menuArray[0] => [
                    'importexportbibpluginSub' => [
                        $this->pluginmessages->text('menu') => FALSE,
                        'importexportbibpluginImportSub' => [
                            $this->pluginmessages->text('menuimport') => FALSE,
                        ],
                        'importexportbibpluginExportBasketSub' => [
                            $this->pluginmessages->text('menuexportbasket') => FALSE,
                        ],
                        'importexportbibpluginExportListSub' => [
                            $this->pluginmessages->text('menuexportlist') => FALSE,
                        ],
                    ],
                ],
            ];
        }
		if (!$lastMulti = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'sql_LastMulti')) {
			$lastMulti = $this->session->getVar("sql_LastMulti");
		}
		if (!$basket = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'basket_List')) {
			$basket = $this->session->getVar("basket_List");
		}
        if ($type == 'bibutils') // Test for existence later as command_exists command slows things down (which we don't want on every page).
        {
            $this->menus[$menuArray[0]]['importexportbibpluginSub'][$this->pluginmessages->text('menuBibutils')] = "initBibutils";
        }
        elseif (($type == 'export') && ($lastMulti || $basket))
        {
            if ($basket)
            {
                $this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginExportBasketSub'][$this->pluginmessages->text('menuExportRtf')] = "initRtfExportB";
                $this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginExportBasketSub'][$this->pluginmessages->text('menuExportBibtex')] = "initBibtexExportB";
                $this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginExportBasketSub'][$this->pluginmessages->text('menuExportHTML')] = 'initHtmlExportB';
                $this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginExportBasketSub'][$this->pluginmessages->text('menuExportEndNote')] = 'initEndnoteExportB';
                $this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginExportBasketSub'][$this->pluginmessages->text('menuExportRIS')] = 'initRisExportB';
            }
            if ($lastMulti)
            {
                $this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginExportListSub'][$this->pluginmessages->text('menuExportRtf')] = "initRtfExportL";
                $this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginExportListSub'][$this->pluginmessages->text('menuExportBibtex')] = "initBibtexExportL";
                $this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginExportListSub'][$this->pluginmessages->text('menuExportHTML')] = 'initHtmlExportL';
                $this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginExportListSub'][$this->pluginmessages->text('menuExportEndNote')] = 'initEndnoteExportL';
                $this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginExportListSub'][$this->pluginmessages->text('menuExportRIS')] = 'initRisExportL';
            }
            if ($this->session->getVar("fileExports"))
            {
                $this->menus[$menuArray[0]]['importexportbibpluginSub'][$this->pluginmessages->text('menuListFiles')] = 'listFiles';
            }
        }
        elseif ($type == 'import')
        { // import
            $this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginImportSub'][$this->pluginmessages->text('menuImportEndnote')] = "initEndnoteImport";
			$this->menus[$menuArray[0]]['importexportbibpluginSub']['importexportbibpluginImportSub'][$this->pluginmessages->text('menuImportPubMed')] = "initPubMedImport";
		}
    }
}
