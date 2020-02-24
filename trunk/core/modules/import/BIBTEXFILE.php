<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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


        $this->import = FACTORY_IMPORT::getInstance();
        $this->tag = FACTORY_TAG::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibtexImport"));
    }
    /**
     * check we are allowed to delete and load appropriate method
     */
    public function init()
    {
        if (!WIKINDX_IMPORT_BIB)
        {
            $this->gatekeep->requireSuper = TRUE;
        }
        $this->gatekeep->init();
        if (array_key_exists('function', $this->vars))
        {
            $function = $this->vars['function'];
            $this->{$function}();
        }
        else
        {
            $this->display();
        }
    }
    /**
     * Display form for pasting bibtex
     *
     * @param string|FALSE $message
     */
    public function display($message = FALSE)
    {
        $this->session->delVar("importLock");
        $category = FACTORY_CATEGORY::getInstance();
        $categories = $category->grabAll();
        $pString = $message;
        if (count($categories) > 1)
        {
            $pString .= \HTML\p($this->messages->text("import", "categoryPrompt"));
        }
        $pString .= \FORM\formMultiHeader("import_IMPORTBIBTEX_CORE");
        $pString .= \FORM\hidden('method', 'stage1');
        $pString .= \FORM\hidden('type', 'file');
        $pString .= \HTML\tableStart('generalTable borderSpacingMedium');
        $pString .= \HTML\trStart();
        // Load tags
        $tags = $this->tag->grabAll();
        $tagInput = \FORM\textInput($this->messages->text("import", "tag"), "import_Tag", FALSE, 30, 255);
        if ($tags)
        {
            // add 0 => IGNORE to tags array
            $temp[0] = $this->messages->text("misc", "ignore");
            foreach ($tags as $key => $value)
            {
                $temp[$key] = $value;
            }
            $tags = $temp;
            $sessionTag = $this->session->issetVar("import_TagId") ?
                $this->session->getVar("import_TagId") : FALSE;
            if ($sessionTag)
            {
                $element = \FORM\selectedBoxValue(FALSE, 'import_TagId', $tags, 5);
            }
            else
            {
                $element = \FORM\selectFBoxValue(FALSE, 'import_TagId', $tags, 5);
            }
            $pString .= \HTML\td($tagInput . '&nbsp;&nbsp;' . $element);
        }
        else
        {
            $pString .= \HTML\td($tagInput);
        }
        $categoryTd = FALSE;
        if (count($categories) > 1)
        {
            if ($sessionCategories = $this->session->getVar("import_Categories"))
            {
                $sCategories = UTF8::mb_explode(",", $sessionCategories);
                $element = \FORM\selectedBoxValueMultiple($this->messages->text(
                    "import",
                    "category"
                ), 'import_Categories', $categories, $sCategories, 5);
            }
            else
            {
                $element = \FORM\selectFBoxValueMultiple($this->messages->text(
                    "import",
                    "category"
                ), 'import_Categories', $categories, 5);
            }
            $pString .= \HTML\td($element . BR .
                \HTML\span($this->messages->text("hint", "multiples"), 'hint'));
            $categoryTd = TRUE;
        }
        if ($bibs = $this->import->bibliographySelect())
        {
            $pString .= \HTML\td($bibs . BR .
                \HTML\span($this->messages->text("hint", "multiples"), 'hint'), FALSE, "left", "bottom");
        }
        $pString .= \HTML\td(\FORM\fileUpload(
            $this->messages->text("import", "file"),
            "import_File",
            30
        ), FALSE, "left", "bottom");
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trStart();
        if ($categoryTd)
        {
            $pString .= \HTML\td("&nbsp;");
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\tableStart('generalTable borderSpacingMedium');
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
        $this->session->clearArray("import");
        GLOBALS::addTplVar('content', $pString);
    }
}
