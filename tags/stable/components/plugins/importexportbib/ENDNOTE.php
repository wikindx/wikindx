<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
class ENDNOTE
{
    private $coremessages;
    private $pluginmessages;
    private $session;
    private $importCommon;
    private $tag;
    private $category;
    private $parentClass;

    /**
     * Constructor
     *
     * @param mixed $parentClass
     */
    public function __construct($parentClass)
    {
        $this->parentClass = $parentClass;
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        include_once("core/messages/PLUGINMESSAGES.php");
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        $this->session = FACTORY_SESSION::getInstance();
        $this->importCommon = FACTORY_IMPORT::getInstance();
        $this->tag = FACTORY_TAG::getInstance();
        $this->category = FACTORY_CATEGORY::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
    }
    /**
     * dislay options for importing
     *
     * @return string
     */
    public function displayImport()
    {
        $categories = $this->category->grabAll();
        $pString = HTML\p($this->pluginmessages->text('introEndnoteImport'));
        if (count($categories) > 1)
        {
            $pString .= HTML\p($this->pluginmessages->text('categoryPrompt'));
        }
        $pString .= FORM\formMultiHeader("importexportbib_importEndnote");
        $pString .= FORM\hidden('method', 'process');
        $pString .= HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= HTML\trStart();
        // Load tags
        $tags = $this->tag->grabAll();
        $tagInput = FORM\textInput($this->pluginmessages->text('tag'), "import_Tag", FALSE, 30, 255);
        if ($tags)
        {
            // add 0 => IGNORE to tags array
            $temp[0] = $this->coremessages->text("misc", "ignore");
            foreach ($tags as $key => $value)
            {
                $temp[$key] = $value;
            }
            $tags = $temp;
            $sessionTag = $this->session->issetVar('import_TagId') ? $this->session->getVar('import_TagId') : FALSE;
            if ($sessionTag && array_key_exists($sessionTag, $tags))
            {
                $element = FORM\selectedBoxValue(FALSE, 'import_TagId', $tags, 5);
            }
            else
            {
                $element = FORM\selectFBoxValue(FALSE, 'import_TagId', $tags, 5);
            }
            $pString .= HTML\td($tagInput . '&nbsp;&nbsp;' . $element);
        }
        else
        {
            $pString .= HTML\td($tagInput);
        }
        $categoryTd = FALSE;
        if (count($categories) > 1)
        {
            if ($sessionCategories = $this->session->getVar('import_Categories'))
            {
                $sCategories = UTF8::mb_explode(",", $sessionCategories);
                $element = FORM\selectedBoxValueMultiple(
                    $this->pluginmessages->text('category'),
                    'import_Categories',
                    $categories,
                    $sCategories,
                    5
                );
            }
            else
            {
                $element = FORM\selectFBoxValueMultiple(
                    $this->pluginmessages->text('category'),
                    'import_Categories',
                    $categories,
                    5
                );
            }
            $pString .= HTML\td($element . BR .
                HTML\span($this->coremessages->text("hint", "multiples"), 'hint'));
            $categoryTd = TRUE;
        }
        if ($bibs = $this->importCommon->bibliographySelect())
        {
            $pString .= HTML\td($bibs . BR .
                HTML\span($this->coremessages->text("hint", "multiples"), 'hint'), FALSE, "left", "bottom");
        }
        $pString .= HTML\td(FORM\fileUpload(
            $this->coremessages->text("import", "file"),
            "import_File",
            30
        ), FALSE, "left", "bottom");
        $pString .= HTML\trEnd();
        $pString .= HTML\trStart();
        //		if($categoryTd)
        //			$pString .= HTML\td("&nbsp;");
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= HTML\trStart();
        $checked = $this->session->getVar('import_ImportDuplicates') ? TRUE : FALSE;
        $td = HTML\p($this->pluginmessages->text('importDuplicates') . "&nbsp;&nbsp;" .
            FORM\checkbox(FALSE, 'import_ImportDuplicates'), $checked);
        $checked = $this->session->getVar('import_KeywordIgnore') ? TRUE : FALSE;
        $td .= HTML\p($this->pluginmessages->text('importKeywordIgnore') . "&nbsp;&nbsp;" .
            FORM\checkbox(FALSE, 'import_KeywordIgnore', $checked));
        $pString .= HTML\td($td);
        //		$pString .= HTML\td($this->pluginmessages->text('storeRawEndnoteImport') . "&nbsp;&nbsp;" .
        //			FORM\checkbox(FALSE, 'import_Raw'));
        $pString .= HTML\td($this->importCommon->titleSubtitleSeparator());
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")), FALSE, "right");
        $pString .= FORM\formEnd();

        return $pString;
    }
    /**
     * Display options for exporting
     *
     * @return string
     */
    public function displayExport()
    {
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "EXPORTCOMMON.php");
        $common = new EXPORTCOMMON();
        $sql = $common->getSQL();
        if (!$sql)
        {
            $this->parentClass->initEndnoteExport(HTML\p($this->coremessages->text("noList"), 'error'));

            return;
        }
        $pString = FORM\formHeader("importexportbib_exportEndnote");
        $pString .= FORM\hidden('method', 'process');
        $pString .= HTML\tableStart('left');
        $pString .= HTML\trStart();
        $checked = $this->session->getVar("exportMergeStored") ? 'CHECKED' : FALSE;
        $pString .= HTML\td($this->coremessages->text('misc', "mergeStored") . FORM\checkbox(FALSE, "mergeStored", $checked));
        if ($custom = $common->getCustomFields('endnote'))
        {
        	$pString .= HTML\trEnd();
        	$pString .= HTML\trStart();
        	$pString .= HTML\td($custom);
        }
        // Disabled due to tabbed file bug above
        /*
        $types = array(1 => $this->pluginmessages->text("exportEndnoteTabbed"),
                    2 => $this->pluginmessages->text("exportEndnoteXml"));
        if($selected = $this->session->getVar("exportEndnoteFileType"))
            $pString .= HTML\td(FORM\selectedBoxValue($this->pluginmessages->text("exportEndnoteFileType"),
                "endnoteFileType", $types, $selected, 2));
        else
            $pString .= HTML\td(FORM\selectFBoxValue($this->pluginmessages->text("exportEndnoteFileType"),
                "endnoteFileType", $types, 2));
        */
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        $pString .= FORM\formEnd();

        return $pString;
    }
}
