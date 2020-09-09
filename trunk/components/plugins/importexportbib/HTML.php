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
 * HTML class
 *
 * Import HTML
 */
class HTMLEXPORT
{
    private $db;
    private $vars;
    private $session;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $common;
    private $parentClass;

    /**
     * Constructor
     *
     * @param mixed $parentClass
     */
    public function __construct($parentClass = FALSE)
    {
        $this->parentClass = $parentClass;
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "EXPORTCOMMON.php"]));
        $this->common = new EXPORTCOMMON('html');
    }
    /**
     * Display options for exporting
     *
     * @return string
     */
    public function exportOptions()
    {
        if (!$this->session->getVar("sql_ListStmt")) {
            $this->failure(HTML\p($this->pluginmessages->text("noList"), 'error'));
        }
        $pString = FORM\formHeader("importexportbib_exportHtml");
        $pString .= FORM\hidden('method', 'process');
        $checked = $this->session->getVar("exportHyperlink") ? 'CHECKED' : FALSE;
        $pString .= $this->pluginmessages->text("exportHyperlink") . FORM\checkbox(FALSE, "exportHyperlink", $checked);
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();

        return $pString;
    }
    /**
     * Export html and write file (written over if it exists)
     */
    public function processExport()
    {
        $sql = $this->common->getSQL();
        if (!$sql) {
            $this->failure(HTML\p($this->pluginmessages->text("noList"), 'error'));
        }
        if (array_key_exists('exportHyperlink', $this->vars)) {
            global $_SERVER;
            $link = implode("/", [WIKINDX_URL_BASE, "index.php?action=resource_RESOURCEVIEW_CORE&id="]);
            $this->session->setVar("exportHyperlink", TRUE);
            $wikindxTitle = stripslashes(WIKINDX_TITLE);
        } else {
            $link = $wikindxTitle = FALSE;
            $this->session->delVar("exportHyperlink");
        }
        if (!$this->common->openFile(FALSE, '.html')) {
            $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
        }
        if (!$this->htmlHeader()) {
            $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
        }
        $sqlArray = unserialize(base64_decode($sql));
        foreach ($sqlArray as $sql) {
            $recordset = $this->db->query($sql);
            if (!$this->getData($recordset, $wikindxTitle, $link)) {
                $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
            }
        }
        if (!$this->htmlFooter()) {
            $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
        }
        if ($this->common->fullFileName) {
            fclose($this->common->fp);
        }
        $pString = HTML\p($this->pluginmessages->text('exported') . ": " . $this->common->fileName, 'success');
        $this->common->writeFilenameToSession($this->common->fileName);
        $this->parentClass->listFiles($pString, 'initHtmlExport');
    }
    /*
     * get data from database and format it
     *
     * @return bool
     */
    private function getData($recordset, $wikindxTitle, $link)
    {
        $rows = [];
        while ($row = $this->db->fetchRow($recordset)) {
            $rows[$row['resourceId']] = $row;
        }
        if (!$this->common->formatResources($rows, 'html', $wikindxTitle, $link, TRUE)) {
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
        $header .= '<title>Wikindx export</title>' . LF;
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
