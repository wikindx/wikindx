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
 *	BIBTEXFILE -- Import a bibtex file
 */
class BIBTEXFILE
{
    private $db;
    private $vars;
    private $messages;
    private $session;
    private $gatekeep;
    private $import;
    private $tag;

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
        $this->tag = FACTORY_TAG::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibtexImport"));
    }
    /**
     * check we are allowed to delete and load appropriate method
     */
    public function init()
    {
        if (!WIKINDX_IMPORT_BIB) {
            $this->gatekeep->requireSuper = TRUE;
        }
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
        $category = FACTORY_CATEGORY::getInstance();
        $categories = $category->grabAll();
        if (count($categories) > 1) {
            $pString .= \HTML\p($this->messages->text("import", "categoryPrompt"));
        }
        $pString .= \FORM\formMultiHeader("import_IMPORTBIBTEX_CORE");
        $pString .= \FORM\hidden('method', 'stage1');
        $pString .= \FORM\hidden('type', 'file');
        $pString .= \HTML\tableStart('generalTable borderSpacingMedium');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\fileUpload(
            $this->messages->text("import", "file"),
            "import_File",
            30
        ));
        // Load tags
        $tags = $this->tag->grabAll();
        $field = array_key_exists('import_Tag', $formData) ? $formData['import_Tag'] : FALSE;
        $tagInput = \FORM\textInput($this->messages->text("import", "tag"), "import_Tag", $field, 30, 255);
        if ($tags) {
            // add 0 => IGNORE to tags array
            $temp[0] = $this->messages->text("misc", "ignore");
            foreach ($tags as $key => $value) {
                $temp[$key] = $value;
            }
            $tags = $temp;
            if (array_key_exists("import_TagId", $formData)) {
                $element = \FORM\selectedBoxValue(FALSE, 'import_TagId', $tags, $formData['import_TagId'], 5);
            } else {
                $element = \FORM\selectFBoxValue(FALSE, 'import_TagId', $tags, 5);
            }
            $pString .= \HTML\td($tagInput . '&nbsp;&nbsp;' . $element);
        } else {
            $pString .= \HTML\td($tagInput);
        }
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
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart('generalTable borderSpacingMedium');
        $pString .= \HTML\trStart();
        $td = '';
        if (WIKINDX_QUARANTINE) {
			$field = array_key_exists("import_Quarantine", $formData) ? TRUE : FALSE;
			$td .= $this->messages->text("import", "quarantine") . "&nbsp;&nbsp;" . \FORM\checkbox(FALSE, "import_Quarantine", $field);
		}
        $field = array_key_exists("import_ImportDuplicates", $formData) ? TRUE : FALSE;
        $td .= \HTML\p($this->messages->text("import", "importDuplicates") . 
        	"&nbsp;&nbsp;" . \FORM\checkbox(FALSE, 'import_ImportDuplicates', $field));
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
        $this->session->clearArray("import");
        GLOBALS::addTplVar('content', $pString);
    }
}
