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
 *	PASTEBIBTEX -- Users can copy 'n' paste bibtex entries into wikindx
 */
class PASTEBIBTEX
{
    private $db;
    private $vars;
    private $messages;
    private $session;
    private $gatekeep;
    private $import;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();


        $this->import = FACTORY_IMPORT::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibtexImport"));
    }
    /**
     * check we are allowed to delete and load appropriate method
     */
    public function init()
    {
        $this->gatekeep->init();
        if (array_key_exists('function', $this->vars)) {
            $function = $this->vars['function'];
            $this->{$function}();
        } else {
            $this->display();
        }
    }
    /**
     * Display form for pasting bibtex
     *
     * @param false|string $message
     */
    public function display($message = FALSE)
    {
        if (!$message) {
            $this->session->delVar("import_Paste");
        }
        include_once("core/modules/help/HELPMESSAGES.php");
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('pasteBibtex'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibtexImport"));
        $this->session->delVar("importLock");
        $category = FACTORY_CATEGORY::getInstance();
        $categories = $category->grabAll();
        $pString = $message;
        if (count($categories) > 1) {
            $pString .= \HTML\p($this->messages->text("import", "categoryPrompt"));
        }
        $pString .= \FORM\formHeader("import_IMPORTBIBTEX_CORE");
        $pString .= \FORM\hidden('method', 'stage1');
        $pString .= \FORM\hidden('type', 'paste');
        if (!$this->session->getVar("setup_Superadmin")) { // Admin can paste unlimited entries
            $pString .= \HTML\p($this->messages->text(
                "import",
                "pasteBibtex",
                ' ' . WIKINDX_MAX_PASTE . " "
            ));
        }
        $pString .= \HTML\tableStart('borderSpacingMedium');
        $pString .= \HTML\trStart();
        $categoryTd = FALSE;
        if (count($categories) > 1) {
            if ($sessionCategories = $this->session->getVar("import_Categories")) {
                $sCategories = UTF8::mb_explode(",", $sessionCategories);
                $element = \FORM\selectedBoxValueMultiple($this->messages->text(
                    "import",
                    "category"
                ), 'import_Categories', $categories, $sCategories, 5);
            } else {
                $element = \FORM\selectFBoxValueMultiple($this->messages->text(
                    "import",
                    "category"
                ), 'import_Categories', $categories, 5);
            }
            $pString .= \HTML\td($element . BR .
                \HTML\span($this->messages->text("hint", "multiples"), 'hint'));
            $categoryTd = TRUE;
        }
        if ($bibs = $this->import->bibliographySelect()) {
            $pString .= \HTML\td($bibs . BR .
                \HTML\span($this->messages->text("hint", "multiples"), 'hint'), 'left bottom');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        $paste = $this->session->issetVar("import_Paste") ?
            unserialize(base64_decode($this->session->getVar("import_Paste"))) : FALSE;
        $pString .= \HTML\td(BR . "&nbsp;" . BR . \FORM\textareaInput(
            FALSE,
            "import_Paste",
            $paste,
            80,
            20
        ), 'left bottom');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        $pString .= BR . "&nbsp;" . BR;

        $pString .= \HTML\tableStart('borderSpacingMedium');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($this->messages->text("import", "quarantine") . "&nbsp;&nbsp;" .
        	\FORM\checkbox(FALSE, "import_Quarantine"));
        $pString .= \HTML\td($this->messages->text("import", "importDuplicates") . "&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, 'import_ImportDuplicates'));
        $td = $this->messages->text("import", "storeRawBibtex");
        $pString .= \HTML\td($td . "&nbsp;&nbsp;" . $this->messages->text("import", "storeRawLabel") . "&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, 'import_Raw'));
        $pString .= \HTML\td($this->import->keywordSeparator());
        $pString .= \HTML\td($this->import->titleSubtitleSeparator());
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Submit")), FALSE, "right");
        $pString .= \FORM\formEnd();
        $this->session->clearArray("import");
        GLOBALS::addTplVar('content', $pString);
    }
}
