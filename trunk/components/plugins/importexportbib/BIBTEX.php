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
 * BIBTEX class
 *
 * Import/Export BibTeX
 */
class BIBTEX
{
    private $db;
    private $session;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $common;
    private $bibtex;
    private $parentClass;
    private $browserTabID = FALSE;

    /**
     * Constructor
     *
     * @param mixed $parentClass
     */
    public function __construct($parentClass = FALSE)
    {
        $this->parentClass = $parentClass;
        $this->db = FACTORY_DB::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "EXPORTCOMMON.php"]));
        $this->common = new EXPORTCOMMON();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "modules", "resource", "VIEWBIBTEX.php"]));
        $this->bibtex = new VIEWBIBTEX();
        $this->browserTabID = GLOBALS::getBrowserTabID();
    }
    /**
     * Display options for exporting
     *
     * @return string
     */
    public function exportOptions()
    {
        if ($this->browserTabID && !\TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'sql_ListStmt')) {
        	if (!$this->session->getVar("sql_ListStmt")) {
            	$this->failure(HTML\p($this->pluginmessages->text("noList"), 'error'));
            }
        }
        $cString = $this->common->getCustomFields();
        $pString = FORM\formHeader("importexportbib_exportBibtex");
        $pString .= FORM\hidden('method', 'process');
        $pString .= $this->bibtex->options($cString);

        return $pString;
    }
    /**
     * Export bibtex and write file
     */
    public function processExport()
    {
        $this->bibtex->writeSession();
        $sql = $this->common->getSQL();
        if (!$sql)
        {
            $this->failure(HTML\p($this->pluginmessages->text("noList"), 'error'));
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
        $pString = HTML\p($this->pluginmessages->text('exported') . ": " . $this->common->fileName, 'success');
        $this->common->writeFilenameToSession($this->common->fileName);
        $this->parentClass->listFiles($pString, 'initBibtexExport');
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
