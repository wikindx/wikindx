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
 * HTMLEXPORT class
 *
 * Export HTML
 */
class HTMLEXPORT
{
    private $db;
    private $vars;
    private $session;
    private $messages;
    private $errors;
    private $common;
    private $browserTabID = FALSE;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->common = FACTORY_EXPORTCOMMON::getInstance('html');
        $this->browserTabID = GLOBALS::getBrowserTabID();
    }
    /**
     * initHtmlExportB
     *
     * Export a basket to HTML
     */
    public function initHtmlExportB()
    {
        $this->session->setVar("exportBasket", TRUE);
        $this->initHtmlExport();
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * initHtmlExportL
     *
     * Export a list to HTML
     */
    public function initHtmlExportL()
    {
        $this->session->delVar("exportBasket", TRUE);
        $this->initHtmlExport();
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * initHtmlExport
     */
    private function initHtmlExport()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "htmlExport"));
        $pString = $this->exportOptions();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display options for exporting
     *
     * @return string
     */
    private function exportOptions()
    {
        if ($this->browserTabID && !\TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'sql_ListStmt')) {
        	if (!$this->session->getVar("sql_ListStmt")) {
            	$this->failure(HTML\p($this->messages->text("importexport", "noList"), 'error'));
            }
        }
        $pString = FORM\formHeader("export_HTMLEXPORT_CORE");
        $pString .= FORM\hidden('method', 'process');
        $checked = $this->session->getVar("exportHyperlink") ? 'CHECKED' : FALSE;
        $pString .= $this->messages->text("importexport", "exportHyperlink") . FORM\checkbox(FALSE, "exportHyperlink", $checked);
        $pString .= HTML\p(FORM\formSubmit($this->messages->text("submit", "Submit")));
        $pString .= FORM\formEnd();

        return $pString;
    }
    /**
     * exportHtml
     *
     * @param false|string $message
     */
    public function process($message = FALSE)
    {
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        ini_set('memory_limit', '-1');
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "htmlExport"));
        $pString = $message ? $message : FALSE;
        //GLOBALS::addTplVar('content', 'under construction'); return;
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'process')) {
            $pString .= $this->processExport();
        }
        GLOBALS::addTplVar('content', $pString);
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * Export html and write file (written over if it exists)
     */
    private function processExport()
    {
        $sql = $this->common->getSQL();
        if (!$sql)
        {
            $this->failure(HTML\p($this->messages->text("importexport", "noList"), 'error'));
        }
        if (array_key_exists('exportHyperlink', $this->vars))
        {
            global $_SERVER;
            $link = implode("/", [WIKINDX_URL_BASE, "index.php?action=resource_RESOURCEVIEW_CORE&id="]);
            $this->session->setVar("exportHyperlink", TRUE);
            $wikindxTitle = stripslashes(WIKINDX_TITLE);
        }
        else
        {
            $link = $wikindxTitle = FALSE;
            $this->session->delVar("exportHyperlink");
        }
        if (!$this->common->openFile('.html'))
        {
            $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
        }
        if (!$this->htmlHeader())
        {
            $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
        }
        $sqlArray = unserialize(base64_decode($sql));
        foreach ($sqlArray as $sql)
        {
            $recordset = $this->db->query($sql);
            if (!$this->getData($recordset, $wikindxTitle, $link))
            {
                $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
            }
        }
        if (!$this->htmlFooter())
        {
            $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
        }
        if ($this->common->fullFileName)
        {
            fclose($this->common->fp);
        }
        $pString = HTML\p($this->messages->text("importexport", 'exported') . ": " . $this->common->fileName, 'success');
        $this->common->writeFilenameToSession($this->common->fileName);
        header("Location: index.php?action=export_FILES_CORE&method=listFiles&message=$pString&browserTabID=" . $this->browserTabID);
        die;
    }
    /*
     * get data from database and format it
     *
     * @return bool
     */
    private function getData($recordset, $wikindxTitle, $link)
    {
        $rows = [];
        while ($row = $this->db->fetchRow($recordset))
        {
            $rows[$row['resourceId']] = $row;
        }
        if (!$this->common->formatResources($rows, 'html', $wikindxTitle, $link, TRUE))
        {
            return FALSE;
        }
        unset($rows);

        return TRUE;
    }
    /*
     * Write the HTML header to $this->pString
     *
     * @return bool
     */
    private function htmlHeader()
    {
        $header = '<!DOCTYPE html>' . LF;
        $header .= '<html>' . LF;
        $header .= '<head>' . LF;
        $header .= '<title>WIKINDX export</title>' . LF;
        $header .= '<meta charset="UTF-8">' . LF;
        $header .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">' . LF;
        $header .= '</head>' . LF;
        $header .= '<body>' . LF;
        $header .= LF;

        return (!(!$this->common->fp || !fwrite($this->common->fp, $header)));
    }
    /*
     * Write the HTML footer to $this->pString
     *
     * @return bool
     */
    private function htmlFooter()
    {
        $Footer = LF;
        $Footer = '</body>' . LF;
        $Footer .= '</html>' . LF;

        return (!(!$this->common->fp || !fwrite($this->common->fp, $Footer)));
    }
    /*
     * failure
     *
     * @param string $error
     */
    private function failure($error)
    {
        GLOBALS::addTplVar('content', $error);
        FACTORY_CLOSE::getInstance();
    }
}
