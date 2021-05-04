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
 * BIBTEXEXPORT class
 *
 * Export BibTeX
 */
class BIBTEXEXPORT
{
    private $db;
    private $session;
    private $messages;
    private $errors;
    private $common;
    private $bibtex;
    private $files;
    private $browserTabID = FALSE;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->common = FACTORY_EXPORTCOMMON::getInstance('bibtex');
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "FILES.php"]));
        $this->files = new FILES();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "resource", "VIEWBIBTEX.php"]));
        $this->bibtex = new VIEWBIBTEX();
        $this->browserTabID = GLOBALS::getBrowserTabID();
    }
    /**
     * export a basket to bibtex
     */
    public function initBibtexExportB()
    {
        $this->session->setVar("exportBasket", TRUE);
        $this->initBibtexExport();
        GLOBALS::clearTplVar('pagingList');
    }
    /**
     * Export a list to bibtex
     */
    public function initBibtexExportL()
    {
        $this->session->delVar("exportBasket", TRUE);
        $this->initBibtexExport();
    }
    /**
     * initBibtexExport
     *
     */
    private function initBibtexExport()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibtexExport"));
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
        $cString = $this->common->getCustomFields();
        $pString = FORM\formHeader("export_BIBTEXEXPORT_CORE");
        $pString .= FORM\hidden('method', 'processExport');
        $pString .= $this->bibtex->options($cString);

        return $pString;
    }
    /**
     * Export bibtex and write file
     */
    public function processExport()
    {
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        ini_set('memory_limit', '-1');
        $this->bibtex->writeSession();
        $sql = $this->common->getSQL();
        if (!$sql)
        {
            $this->failure(HTML\p($this->messages->text("importexport", "noList"), 'error'));
        }
        if (!$this->common->openFile('.bib'))
        {
            $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
        }
        $sqlArray = unserialize(base64_decode($sql));
        foreach ($sqlArray as $sql)
        {
            $recordset = $this->db->query($sql);
            if (!$this->bibtex->getData($recordset, $this->common->fp))
            {
                $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
            }
            $this->bibtex->formatRawString();
        }
        if ($this->common->fullFileName)
        {
            fclose($this->common->fp);
        }
        if (!empty($this->bibtex->rawStringArray))
        { // Prepend @STRING strings
            $rawString = implode("\n", $this->bibtex->rawStringArray) . "\n\n";
            $fp = fopen($this->common->fullFileName, 'r', 1, $this->common->context);
            $tmpname = $this->common->filesDir . DIRECTORY_SEPARATOR . \UTILS\uuid();
            file_put_contents($tmpname, $rawString);
            file_put_contents($tmpname, $fp, FILE_APPEND);
            fclose($fp);
            unlink($this->common->fullFileName);
            rename($tmpname, $this->common->fullFileName);
        }
        $pString = HTML\p($this->messages->text("importexport", 'exported') . ": " . $this->common->fileName, 'success');
        $this->common->writeFilenameToSession($this->common->fileName);
        $this->files->listFiles($pString);
    }
    /**
     * failure()
     *
     * @param mixed $error
     */
    private function failure($error)
    {
        GLOBALS::addTplVar('content', $error);
        FACTORY_CLOSE::getInstance();
    }
}
