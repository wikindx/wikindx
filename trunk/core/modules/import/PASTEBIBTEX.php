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
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "IMPORTCOMMON.php"]));
        $this->import = new IMPORTCOMMON();
    }
    /**
     * check we are allowed to paste and then load display()
     */
    public function init()
    {
        $this->gatekeep->init();
        $this->display();
    }
    /**
     * Display form for pasting bibtex
     *
     * @param mixed $message
     */
    public function display($message = FALSE)
    {
        $formData = [];
        if (is_array($message)) {
            $formData = $message[1]; // For some reason, this need to be taken first . . .
            $message = $message[0];
        } elseif (array_key_exists('message', $this->vars)) {
        	$message = $this->vars['message'];
        }
        $pString = $message;
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "help", "HELPMESSAGES.php"]));
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('pasteBibtex'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibtexPaste"));
        $category = FACTORY_CATEGORY::getInstance();
        $categories = $category->grabAll();
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
        $pString .= \HTML\tableStart('generalTable borderSpacingMedium');
        $pString .= \HTML\trStart();
        $categoryTd = FALSE;
        if (count($categories) > 1) {
        	$cats = $this->import->categorySelect($categories, $formData);
            $pString .= \HTML\td($cats . BR .
                \HTML\span(\HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", 
            	$this->messages->text("hint", "multiples")), 'hint'));
            $categoryTd = TRUE;
        }
        if ($bibs = $this->import->bibliographySelect($formData)) {
            $pString .= \HTML\td($bibs . BR .
                \HTML\span(\HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", 
            	$this->messages->text("hint", "multiples")), 'hint'));
        }
        $paste = array_key_exists("import_Paste", $formData) ? $formData["import_Paste"] : FALSE;
        $pString .= \HTML\td(\FORM\textareaInput(
            $this->messages->text("import", "pasteBibtex2"),
            "import_Paste",
            $paste,
            80,
            20
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        $pString .= BR . "&nbsp;" . BR;

        $pString .= \HTML\tableStart('generalTable borderSpacingMedium');
        $pString .= \HTML\trStart();
        $td = '';
        if (WIKINDX_QUARANTINE) {
			$field = array_key_exists("import_Quarantine", $formData) ? TRUE : FALSE;
			$td .= $this->messages->text("import", "quarantine") . "&nbsp;&nbsp;" .
				\FORM\checkbox(FALSE, "import_Quarantine", $field);
		}
        $field = array_key_exists("import_ImportDuplicates", $formData) ? TRUE : FALSE;
        $td .= \HTML\p($this->messages->text("import", "importDuplicates") . "&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, 'import_ImportDuplicates', $field));
        $field = array_key_exists("import_Raw", $formData) ? TRUE : FALSE;
        $td .= \HTML\p($this->messages->text("import", "storeRawLabel") . "&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, 'import_Raw', $field) . BR .
                \HTML\span(\HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", 
            	$this->messages->text("hint", "storeRawBibtex")), 'hint'));
        $pString .= \HTML\td($td);
        $pString .= \HTML\td($this->import->keywordSeparator($formData));
        $pString .= \HTML\td($this->import->titleSubtitleSeparator($formData));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Submit")));
        $pString .= \FORM\formEnd();
        $this->session->clearArray("import");
        GLOBALS::addTplVar('content', $pString);
    }
}
