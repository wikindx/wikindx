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
 * \brief Provides interface for external programs and for WIKINDX AJAX.
 * Currently it is the "default" class that API functions get put into that
 * don't yet fit into other classes.  Eventually, this class may be removed altogether.
 */
class API
{
    private $db;
    private $vars;
    private $icons;
    private $stmt;
    private $errors;
    private $messages;
    private $bibtex;
    private $bibStyle;
    private $stats;
    private $session;
    private $user;
    private $commonBib;
    private $badInput;
    private $common;
    private $abstract;
    private $note;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->bibtex = FACTORY_EXPORTBIBTEX::getInstance();
        $this->bibStyle = FACTORY_BIBSTYLE::getInstance();
        $this->stats = FACTORY_STATISTICS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->common = FACTORY_RESOURCECOMMON::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "resource", "RESOURCEABSTRACT.php"]));
        $this->abstract = new RESOURCEABSTRACT();
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "resource", "RESOURCENOTE.php"]));
        $this->note = new RESOURCENOTE();
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "resource", "RESOURCEMETA.php"]));
        $this->meta = new RESOURCEMETA();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "resources"));
    }
    /**
     * fetchId
     */
    public function fetchId()
    {
        if (!array_key_exists('bibtexKey', $this->vars) || !$this->vars['bibtexKey'])
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }

        $this->db->formatConditions(['resourceBibtexKey' => $this->vars['bibtexKey']]);
        $resultset = $this->db->select('resource', ['resourceId']);
        if (!$this->db->numRows($resultset))
        {
            $this->badInput->close($this->messages->text("resources", "noResult"));
        }
        $row = $this->db->fetchRow($resultset);

        GLOBALS::addTplVar('content', $row['resourceId']);
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * getBibtex
     */
    public function getBibtex()
    {
        if (!array_key_exists('id', $this->vars) || !$this->vars['id'])
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }

        $this->stmt->listJoin();
        $this->db->formatConditions(['resourceId' => $this->vars['id']]);
        $resultset = $this->db->select('resource', $this->stmt->listFields());
        if (!$this->db->numRows($resultset))
        {
            $this->badInput->close($this->messages->text("resources", "noResult"));
        }
        $row = $this->db->fetchRow($resultset);

        $this->bibStyle->process($row);
        $body = $this->bibtex->export($row, $this->bibStyle->coinsCreators);

        GLOBALS::addTplVar('content', $body);
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * createBibtexRow
     *
     * @param mixed $varname
     * @param mixed $val
     *
     * @return string
     */
    private static function createBibtexRow($varname, $val)
    {
        return "| $varname = $val" . LF;
    }
}
