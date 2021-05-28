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
 * Common functions for importing resources into wikindx
 *
 * @package wikindx\core\modules\import
 */
class IMPORTCOMMON
{
    /** int */
    public $resourceId;
    /** bool */
    public $kwIgnore = FALSE;
    /** bool */
    public $quarantine = FALSE;
    /** string */
    public $importType = 'bibtex';
    /** object */
    private $db;
    /** array */
    private $vars;
    /** object */
    private $messages;
    /** object */
    private $success;
    /** object */
    private $errors;
    /** object */
    private $session;
    /** object */
    private $creator;
    /** object */
    private $keyword;
    /** object */
    private $collection;
    /** object */
    private $collectionMap;
    /** object */
    private $publisher;
    /** object */
    private $publisherMap;
    /** object */
    private $bibConfig;
    /** object */
    private $tag;

    /**
     *	IMPORTCOMMON
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->tag = FACTORY_TAG::getInstance();
        $this->creator = FACTORY_CREATOR::getInstance();
        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->collection = FACTORY_COLLECTION::getInstance();
        $this->collectionMap = FACTORY_COLLECTIONMAP::getInstance();
        $this->publisher = FACTORY_PUBLISHER::getInstance();
        $this->publisherMap = FACTORY_PUBLISHERMAP::getInstance();
        $this->bibConfig = FACTORY_BIBTEXCONFIG::getInstance();
        $this->bibConfig->bibtex();
        $bibtexKeys = [];
        $recordset = $this->db->select('resource', 'resourceBibtexKey');
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->bibtexKeys[] = $row['resourceBibtexKey'];
        }
    }
    /**
     * Display form for importing bibliographies
     *
     * @param mixed $message
     */
    public function display($message = FALSE)
    {
        $formData = [];
        if (is_array($message))
        {
            $formData = $message[1]; // For some reason, this need to be taken first . . .
            $message = $message[0];
        }
        elseif (array_key_exists('message', $this->vars))
        {
            $message = $this->vars['message'];
        }
        $pString = $message;
        if ($this->importType == 'bibtex')
        {
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibtexImport"));
            $pString .= \HTML\p($this->messages->text("import", "bibtexImport"));
        }
        elseif ($this->importType == 'pasteBibtex')
        {
            GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('pasteBibtex'));
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibtexPaste"));
        }
        $category = FACTORY_CATEGORY::getInstance();
        $categories = $category->grabAll();
        if (count($categories) > 1)
        {
            $pString .= \HTML\p($this->messages->text("import", "categoryPrompt"));
        }
        if ($this->importType == 'endnote')
        {
            $pString .= FORM\formMultiHeader("import_ENDNOTEIMPORT_CORE");
            $pString .= FORM\hidden('method', 'process');
        }
        else
        {
            $pString .= \FORM\formMultiHeader("import_IMPORTBIBTEX_CORE");
            $pString .= \FORM\hidden('method', 'stage1');
        }
        if ($this->importType == 'pasteBibtex')
        {
            $pString .= \FORM\hidden('type', 'paste');
        }
        else
        {
            $pString .= \FORM\hidden('type', 'file');
        }
        $pString .= \HTML\tableStart('generalTable borderSpacingMedium');
        $pString .= \HTML\trStart();
        if ($this->importType == 'pasteBibtex')
        {
            $paste = array_key_exists("import_Paste", $formData) ? base64_decode($formData["import_Paste"]) : FALSE;
            if (!$this->session->getVar('setup_Superadmin')) {
            	$extraString = $this->messages->text("import", "pasteBibtex", \HTML\strong(WIKINDX_MAX_PASTE)) . '&nbsp;';
            } else {
            	$extraString = '';
            }
            $pString .= \HTML\td(\FORM\textareaInput(
                $extraString . $this->messages->text("import", "pasteBibtex2"),
                "import_Paste",
                $paste,
                80,
                20
            ));
        }
        else
        {
            if (ini_get("file_uploads"))
            {
                $pString .= \HTML\td(
                    \FORM\fileUpload(
                        $this->messages->text("import", "file"),
                        "import_File",
                        30
                    )
                    . " (max.&nbsp;" . \FILE\formatSize(\FILE\fileUploadMaxSize()) . ")"
                );
            }
            else
            {
                $pString .= \HTML\td($this->messages->text("misc", "uploadDisabled"));
            }
        }
        // Load tags
        if (($this->importType != 'pasteBibtex') || $this->session->getVar('setup_Superadmin'))
        {
            $tags = $this->tag->grabAll();
            $field = array_key_exists('import_Tag', $formData) ? $formData['import_Tag'] : FALSE;
            $tagInput = \FORM\textInput($this->messages->text("import", "tag"), "import_Tag", $field, 30, 255) . BR .
                \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "tagImport")
                ), 'hint');
            if ($tags)
            {
                // add 0 => IGNORE to tags array
                $temp[0] = $this->messages->text("misc", "ignore");
                foreach ($tags as $key => $value)
                {
                    $temp[$key] = $value;
                }
                $tags = $temp;
                if (array_key_exists("import_TagId", $formData))
                {
                    $element = \FORM\selectedBoxValue(FALSE, 'import_TagId', $tags, $formData['import_TagId'], 5);
                }
                else
                {
                    $element = \FORM\selectFBoxValue(FALSE, 'import_TagId', $tags, 5);
                }
                $tagTable = \HTML\tableStart('generalTable') . \HTML\trStart();
                $tagTable .= \HTML\td($tagInput);
                $tagTable .= \HTML\td($element);
                $tagTable .= \HTML\trEnd();
                $tagTable .= \HTML\tableEnd();
                $pString .= \HTML\td($tagTable);
            }
            else
            {
                $pString .= \HTML\td($tagInput);
            }
        }
        $categoryTd = FALSE;
        if (count($categories) > 1)
        {
            $cats = $this->categorySelect($categories, $formData);
            $pString .= \HTML\td($cats . BR .
                \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "multiples")
                ), 'hint'));
            $categoryTd = TRUE;
        }
        if ($bibs = $this->bibliographySelect($formData))
        {
            $pString .= \HTML\td($bibs . BR .
                \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "multiples")
                ), 'hint'));
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\tableStart('generalTable borderSpacingMedium');
        $pString .= \HTML\trStart();
        $td = '';
        if (WIKINDX_QUARANTINE && $this->session->getVar("setup_Superadmin") == 1)
        {
            $field = array_key_exists("import_Quarantine", $formData) ? TRUE : FALSE;
            $td .= $this->messages->text("import", "quarantine") . "&nbsp;&nbsp;" . \FORM\checkbox(FALSE, "import_Quarantine", $field);
        }
        $field = array_key_exists("import_ImportDuplicates", $formData) ? TRUE : FALSE;
        $td .= \HTML\p($this->messages->text("import", "importDuplicates") .
            "&nbsp;&nbsp;" . \FORM\checkbox(FALSE, 'import_ImportDuplicates', $field));
        if ($this->importType != 'endnote')
        {
            $field = array_key_exists("import_Raw", $formData) ? TRUE : FALSE;
            $td .= \HTML\p($this->messages->text("import", "storeRawLabel") . "&nbsp;&nbsp;" .
                \FORM\checkbox(FALSE, 'import_Raw', $field) . BR .
                    \HTML\span(\HTML\aBrowse(
                        'green',
                        '',
                        $this->messages->text("hint", "hint"),
                        '#',
                        "",
                        $this->messages->text("hint", "storeRawBibtex")
                    ), 'hint'));
        }
        $pString .= \HTML\td($td);
        $pString .= \HTML\td($this->keywordSeparator($formData));
        $pString .= \HTML\td($this->titleSubtitleSeparator($formData));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Submit")));
        
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Print details of successful import and do some tidying up
     */
    public function importSuccess()
    {
        $data = \TEMPSTORAGE\fetch($this->db, $this->vars['uuid']); // FALSE if nothing there (reloading page?), else array
        if (is_array($data))
        {
            foreach ($data['garbageFiles'] as $fileName => $null)
            {
                unlink($fileName); // remove garbage - ignore errors
            }
            if ($data['resourceAdded'])
            {
                include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "email", "EMAIL.php"]));
                $email = new EMAIL();
                $email->notify(FALSE, TRUE);
            }
            if (array_key_exists('success', $this->vars)) {
    		    $pString = $this->success->text($this->vars['success']);
	        } elseif (array_key_exists('importMessages', $data)) {
	        	$pString = $data['importMessages'];
	        } else {
	        	$pString = '';
	        }
            $pString .= \HTML\p($this->messages->text("import", "added", " " . $data['resourceAdded']));
            $pString .= $this->printDuplicates($data['resourceDiscarded'], $data['rejectTitles']);
            $pString .= \HTML\hr();
            if (!empty($data['rIds']) && (count($data['rIds']) <= GLOBALS::getUserVar('PagingMaxLinks')))
            {
                $resourceList = [];
                $rCommon = FACTORY_RESOURCECOMMON::getInstance();
                $bibStyle = FACTORY_BIBSTYLE::getInstance();
                $bibStyle->output = 'html';
                $this->db->formatConditionsOneField($data['rIds'], 'resourceId');
                $recordset = $rCommon->getResource(FALSE, $this->db->formatFields('creatorSurname'));
                while ($row = $this->db->fetchRow($recordset))
                {
                    $link = \HTML\a('link', $bibStyle->process($row), "index.php?action=resource_RESOURCEFORM_CORE" .
                        htmlentities("&type=edit&id=" . $row['resourceId']));
                    $resourceList[]['resource'] = $link;
                }
                // Templates expect list ordered from 0, so we renumber from zero
                $rL = array_values($resourceList);
                GLOBALS::setTplVar('resourceList', $rL);
            }
            $this->deleteCaches($data);
            \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
            $this->tidyTables();
            $this->session->delVar("sql_LastMulti");
        }
        else {
        	$pString = $this->success->text($this->vars['success']);
        }
        GLOBALS::setTplVar('content', $pString);
    }
    /**
     * Print invalid fields for mapping to wikindx fields
     */
    public function importInvalidFields()
    {
        $data = \TEMPSTORAGE\fetch($this->db, $this->vars['uuid']);
        $pString = $data['form'];
        GLOBALS::setTplVar('heading', $data['heading']);
        GLOBALS::setTplVar('content', $pString);
    }
    /**
     * Print continuation message when importing in chunks
     */
    public function importContinue()
    {
        $data = \TEMPSTORAGE\fetch($this->db, $this->vars['uuid']);
        $pString = $data['form'];
        GLOBALS::setTplVar('heading', $data['heading']);
        GLOBALS::setTplVar('content', $pString);
    }
    /**
     * Check for duplicate title/resourceType
     *
     * If the session variable 'import_ImportDuplicates' is TRUE, the function quits immediately.
     * NoSort, Title, Subtitle and resource type must match.
     *
     * @param string $noSort (A word like 'the', 'a' or translated equivalents that is at the start of the raw title)
     * @param string $title
     * @param string $subtitle
     * @param string $type
     * @param array $formData
     *
     * @return bool TRUE if resource already exists, FALSE if not.
     */
    public function checkDuplicates($noSort, $title, $subtitle, $type, $formData = [])
    {
        if (array_key_exists("import_ImportDuplicates", $formData))
        {
            return FALSE; // i.e. allow duplicates
        }
        if ($subtitle)
        {
            $subtitle = str_replace(['{', '}'], '', $subtitle);
            $this->db->formatConditions($this->db->replace($this->db->replace('resourceSubtitle', '{', ''), '}', '', FALSE) .
                $this->db->like(FALSE, $subtitle, FALSE));
        }
        else
        {
            $this->db->formatConditions(['resourceSubtitle' => ' IS NULL']);
        }
        if ($noSort)
        {
            $noSort = str_replace(['{', '}'], '', $noSort);
            $this->db->formatConditions($this->db->replace($this->db->replace('resourceNoSort', '{', ''), '}', '', FALSE) .
                $this->db->like(FALSE, $noSort, FALSE));
        }
        else
        {
            $this->db->formatConditions(['resourceNoSort' => ' IS NULL']);
        }
        $this->db->formatConditions(['resourceType' => $type]);
        $title = str_replace(['{', '}'], '', $title);
        $this->db->formatConditions($this->db->replace($this->db->replace('resourceTitle', '{', ''), '}', '', FALSE) .
            $this->db->equal . $this->db->tidyInput($title));
        $resultset = $this->db->select('resource', 'resourceId');

        return ($this->db->numRows($resultset));
    }
    /**
     * Print a list of discarded resources.
     *
     * @param int $numDiscarded Num. resources discarded
     * @param array $titlesDiscarded Titles of discarded resources
     *
     * @return string
     */
    public function printDuplicates($numDiscarded, $titlesDiscarded)
    {
        if (($numDiscarded <= 50) && !empty($titlesDiscarded))
        {
            $reject = $this->messages->text("import", "discarded", " " . $numDiscarded);
            foreach ($titlesDiscarded as $title)
            {
                $reject .= BR . $title;
            }

            return \HTML\p($reject);
        }

        return \HTML\p($this->messages->text("import", "discarded", " " . $numDiscarded));
    }
    /**
     * split title into WIKINDX component parts (noSort, title : subtitle)
     *
     * title / subtitle split is calculated on the session variable 'import_TitleSubtitleSeparator'.
     *
     * @param $title
     * @param array $formData
     *
     * @return array (noSort, title, subtitle)
     */
    public function splitTitle($title, $formData = [])
    {
        if (!trim($title))
        {
            return [FALSE, FALSE, FALSE];
        }
        $noSort = $subtitle = FALSE;
        $split = $formData["import_TitleSubtitleSeparator"];
        if ($split)
        { // split title and subtitle
            switch ($split) {
                case 1:
                    $split = ': ';

                    break;
                case 2:
                    $split = '; ';

                    break;
                case 3:
                    $split = '[.!?] ';

                    break;
                case 4:
                    $split = '--';

                    break;
                case 5:
                    $split = '-';

                    break;
            }
            $array = preg_split("/$split/u", $title, 2);
            if (count($array) > 1)
            {
                $title = trim(array_shift($array));
                $subtitle = trim(implode('', $array));
                // Correct any incomplete braces
                if ((mb_strpos($title, '{') !== FALSE) && (mb_strpos($title, '}') === FALSE))
                {
                    $title .= '}';
                }
                if ((mb_strpos($subtitle, '}') !== FALSE) && (mb_strpos($subtitle, '{') === FALSE))
                {
                    $subtitle = '{' . $subtitle;
                }
            }
            else
            {
                $title = trim($array[0]);
            }
        }
        foreach (WIKINDX_NO_SORT as $pattern)
        {
            if (preg_match("/^($pattern)\\s(.*)|^\\{($pattern)\\s(.*)/ui", $title, $matches))
            {
                if (array_key_exists(3, $matches))
                { // found second set of matches
                    $title = '{' . trim(\HTML\removeNl($matches[4]));
                    $noSort = trim(\HTML\removeNl($matches[3]));
                }
                else
                {
                    $title = trim(\HTML\removeNl($matches[2]));
                    $noSort = trim(\HTML\removeNl($matches[1]));
                }

                break;
            }
        }

        return [$noSort, $title, $subtitle];
    }
    /**
     * Select box for specifying keyword separation character in source bibliography
     *
     * @param array $formData
     *
     * @return string
     */
    public function keywordSeparator($formData = [])
    {
        $pString = '';
        if ($this->importType != 'endnote')
        {
            $sessVar = is_array($formData) && array_key_exists("import_KeywordSeparator", $formData) ?
                $formData["import_KeywordSeparator"] : FALSE;
            $array = [
                $this->messages->text('misc', 'keywordImport1'),
                $this->messages->text('misc', 'keywordImport2'),
                $this->messages->text('misc', 'keywordImport3'),
                $this->messages->text('misc', 'keywordImport4'),
            ];
            if ($sessVar !== FALSE)
            {
                $pString .= \FORM\selectedBoxValue(
                    $this->messages->text('misc', 'keywordImport'),
                    'import_KeywordSeparator',
                    $array,
                    $sessVar,
                    4
                ) . BR .
                \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "keywordImport")
                ), 'hint');
            }
            else
            {
                $pString .= \FORM\selectFBoxValue(
                    $this->messages->text('misc', 'keywordImport'),
                    'import_KeywordSeparator',
                    $array,
                    4
                ) . BR .
                \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "keywordImport")
                ), 'hint');
            }
        }
        $sessVar = is_array($formData) && array_key_exists("import_KeywordIgnore", $formData) ? TRUE : FALSE;

        return $pString .= \HTML\p(\FORM\checkBox(
            $this->messages->text('misc', 'keywordIgnore'),
            'import_KeywordIgnore',
            $sessVar
        ));
    }
    /**
     * Select box for specifying title/subtitle separation character in source bibliography
     *
     * @param array $formData
     *
     * @return string
     */
    public function titleSubtitleSeparator($formData = [])
    {
        $sessVar = is_array($formData) && array_key_exists("import_TitleSubtitleSeparator", $formData) ?
            $formData["import_TitleSubtitleSeparator"] : FALSE;
        $array = [
            $this->messages->text('misc', 'titleSubtitleSeparator1'),
            $this->messages->text('misc', 'titleSubtitleSeparator2'),
            $this->messages->text('misc', 'titleSubtitleSeparator3'),
            $this->messages->text('misc', 'titleSubtitleSeparator4'),
            $this->messages->text('misc', 'titleSubtitleSeparator5'),
            $this->messages->text('misc', 'titleSubtitleSeparator6'),
        ];
        if ($sessVar !== FALSE)
        {
            return \FORM\selectedBoxValue(
                $this->messages->text('misc', 'titleSubtitleSeparator'),
                'import_TitleSubtitleSeparator',
                $array,
                $sessVar,
                6
            ) . BR .
                \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "splitImport")
                ), 'hint');
        }
        else
        {
            return \FORM\selectedBoxValue(
                $this->messages->text('misc', 'titleSubtitleSeparator'),
                'import_TitleSubtitleSeparator',
                $array,
                1,
                6
            ) . BR .
                \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "splitImport")
                ), 'hint');
        }
    }
    /**
     * Selext box for selecting user bibliographies to import into
     *
     * @param array $formData
     *
     * @return string
     */
    public function bibliographySelect($formData = [])
    {
        // Get this user's bibliographies
        if ($this->session->getVar("mywikindx_Bibliographies"))
        {
            $bibsRaw = unserialize($this->session->getVar("mywikindx_Bibliographies"));
            foreach ($bibsRaw as $key => $value)
            {
                $bibsU[$key] = \HTML\dbToFormTidy($value);
            }
        }
        // Get this user's user group bibliographies
        if ($this->session->getVar("mywikindx_Groupbibliographies"))
        {
            $bibsRaw = unserialize($this->session->getVar("mywikindx_Groupbibliographies"));
            foreach ($bibsRaw as $key => $value)
            {
                $bibsUG[$key] = \HTML\dbToFormTidy($value);
            }
        }
        $bib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $bibsU = $bib->getUserBibs();
        $bibsUG = $bib->getGroupBibs();
        if (!empty($bibsU))
        {
            $bibsArray[-1] = $this->messages->text('user', 'userBibs');
            foreach ($bibsU as $key => $value)
            {
                $bibsArray[$key] = $value;
            }
        }
        if (!empty($bibsUG))
        {
            $bibsArray[-2] = $this->messages->text('user', 'userGroupBibs');
            foreach ($bibsUG as $key => $value)
            {
                $bibsArray[$key] = $value;
            }
        }
        if (isset($bibsArray))
        {
            $field = is_array($formData) && array_key_exists("import_BibId", $formData) ? $formData["import_BibId"] : [-1];
            if ((sizeof($field) > 1) && in_array(-1, $field))
            {
                unset($field[array_search(-1, $field)]);
            }
            if (in_array(-2, $field))
            {
                unset($field[array_search(-2, $field)]);
                if (empty($field))
                {
                    $field = [-1];
                }
            }

            return \FORM\selectedBoxValueMultiple($this->messages->text("user", 'bib'), "import_BibId", $bibsArray, $field, 5);
        }
        else
        {
            return FALSE;
        }
    }
    /**
     * Selext box for selecting categories to import into
     *
     * @param array $categories
     * @param array $formData
     *
     * @return string
     */
    public function categorySelect($categories, $formData = [])
    {
        if (array_key_exists("import_Categories", $formData))
        {
            return \FORM\selectedBoxValueMultiple($this->messages->text(
                "import",
                "category"
            ), 'import_Categories', $categories, $formData['import_Categories'], 5);
        }
        else
        {
            return \FORM\selectFBoxValueMultiple($this->messages->text(
                "import",
                "category"
            ), 'import_Categories', $categories, 5);
        }
    }
    /**
     * Write the resource table
     *
     * @param array $fields
     * @param array $values
     *
     * @return int New resource Id
     */
    public function writeResourceTable($fields, $values)
    {
        $this->db->insert('resource', $fields, $values);
        $this->resourceId = $this->db->lastAutoID();

        return $this->resourceId;
    }
    /**
     * Write the resource_misc table
     *
     * @param array $fields
     * @param array $values
     */
    public function writeResourcemiscTable($fields, $values)
    {
        $fields[] = 'resourcemiscId';
        $values[] = $this->resourceId;
        if (WIKINDX_QUARANTINE && (($this->session->getVar("setup_Superadmin") != 1) || $this->quarantine))
        {
            $fields[] = 'resourcemiscQuarantine';
            $values[] = 'Y';
        }
        $this->db->insert('resource_misc', $fields, $values);
    }
    /**
     * Write creator and resource_creator tables
     *
     * @param array $creators ordered array of creators
     */
    public function writeCreatorTables($creators)
    {
        if (empty($creators))
        { // need blank row for list operations
            $this->db->insert('resource_creator', ['resourcecreatorResourceId'], [$this->resourceId]);

            return;
        }
        $mainName = $mainId = $rowWritten = $deleteCache = FALSE;
        foreach ($creators as $role => $roleArray)
        {
            foreach ($roleArray as $order => $creatorArray)
            {
                $creatorId = FALSE;
                if ($creatorArray['surname'])
                { // entry in surname takes precedence
                    unset($creatorArray['select']);
                    $initials = $this->creator->formatInitials($creatorArray['initials']);
                    $creatorId = $this->creator->checkExists(
                        $creatorArray['surname'],
                        $creatorArray['firstname'],
                        $initials,
                        $creatorArray['prefix']
                    );
                    if (!$creatorId)
                    { // new creator
                        $writeArray = [];
                        $writeArray['creatorSurname'] = trim($creatorArray['surname']);
                        if ($creatorArray['firstname'])
                        {
                            $writeArray['creatorFirstname'] = trim($creatorArray['firstname']);
                        }
                        if ($creatorArray['prefix'])
                        {
                            $writeArray['creatorPrefix'] = trim($creatorArray['prefix']);
                        }
                        if (trim($initials))
                        {
                            $writeArray['creatorInitials'] = trim($initials);
                        }
                        $this->db->insert('creator', array_keys($writeArray), array_values($writeArray));
                        $creatorId = $this->db->lastAutoID();
                        if (!$mainName)
                        {
                            $mainName = $writeArray['creatorSurname'];
                            $mainId = $creatorId;
                        }
                        $deleteCache = TRUE;
                    }
                }
                elseif (array_key_exists('select', $creatorArray) && $creatorArray['select'])
                {
                    $creatorId = $creatorArray['select'];
                }
                if ($creatorId)
                {
                    if (!$mainName)
                    {
                        $this->db->formatConditions(['creatorId' => $creatorId]);
                        $mainName = $this->db->selectFirstField('creator', 'creatorSurname');
                        $mainId = $creatorId;
                    }
                    $writeArray = [];
                    $writeArray['resourcecreatorCreatorId'] = $creatorId;
                    $writeArray['resourcecreatorResourceId'] = $this->resourceId;
                    $writeArray['resourcecreatorCreatorMain'] = $mainId;
                    // remove all punctuation (keep apostrophe and dash for names such as Grimshaw-Aagaard and D'Eath)
                    $writeArray['resourcecreatorCreatorSurname'] = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s\-\']/u', '', $mainName));
                    $writeArray['resourcecreatorOrder'] = $order;
                    $writeArray['resourcecreatorRole'] = $role;
                    $this->db->insert('resource_creator', array_keys($writeArray), array_values($writeArray));
                    $rowWritten = TRUE;
                }
            }
        }
    }
    /**
     * Write the publisher table
     *
     * @param string $name Publisher name
     * @param string $location Publisher location
     * @param string $wkType wikindx resource type
     *
     * @return int Publisher ID
     */
    public function writePublisherTable($name, $location, $wkType)
    {
        $publisherId = FALSE;
        if ($name && (!$publisherId = $this->publisher->checkExists($name, $location)))
        {
            if ($name)
            {
                $writeArray['publisherName'] = $name;
            }
            if ($location)
            {
                $writeArray['publisherLocation'] = $location;
            }
            if (array_key_exists($wkType, $this->publisherMap->publisherTypes))
            {
                $writeArray['publisherType'] = $this->publisherMap->publisherTypes[$wkType];
            }
            $this->db->insert('publisher', array_keys($writeArray), array_values($writeArray));
            $publisherId = $this->db->lastAutoID();
        }

        return $publisherId;
    }
    /**
     * Write collection table
     *
     * @param string $title Collection title
     * @param string $titleShort Collection short title
     * @param string $wkType wikindx resource type
     *
     * @return int Collection ID
     */
    public function writeCollectionTable($title, $titleShort, $wkType)
    {
        if ($title)
        {
            $writeArray['collectionTitle'] = $title;
        }
        if ($titleShort)
        {
            $writeArray['collectionTitleShort'] = $titleShort;
        }
        $type = FALSE;
        if (array_key_exists($wkType, $this->collectionMap->collectionTypes))
        {
            $writeArray['collectionType'] = $type = $this->collectionMap->collectionTypes[$wkType];
        }
        if ($title && (!$collectionId = $this->collection->checkExists($title, $titleShort, $type)))
        {
            $this->db->insert('collection', array_keys($writeArray), array_values($writeArray));
            $collectionId = $this->db->lastAutoID();
        }

        return $collectionId;
    }
    /**
     * writeResourceTimestampTable - write timestamp to resource_timestamp table an add blank statistics row
     */
    public function writeResourceTimestampTable()
    {
        $this->db->insert(
            'resource_timestamp',
            ['resourcetimestampId', 'resourcetimestampTimestamp',
                'resourcetimestampTimestampAdd', ],
            [$this->resourceId, $this->db->formatTimestamp(), $this->db->formatTimestamp()]
        );
    }
    /**
     * Wite resource_page table
     *
     * @param array $fields
     * @param array $values
     */
    public function writePageTable($fields, $values)
    {
        $fields[] = 'resourcepageId';
        $values[] = $this->resourceId;
        $this->db->insert('resource_page', $fields, $values);
    }
    /**
     * Wite resource_year table
     *
     * @param array $fields
     * @param array $values
     */
    public function writeYearTable($fields, $values)
    {
        $fields[] = 'resourceyearId';
        $values[] = $this->resourceId;
        $this->db->insert('resource_year', $fields, $values);
    }
    /**
     * Write resource_text table
     *
     * @param string $notes
     * @param string $abstract
     * @param string $url Default is FALSE
     */
    public function writeResourcetextTable($notes, $abstract, $url = FALSE)
    {
        $fields[] = 'resourcetextId';
        $values[] = $this->resourceId;
        if ($notes)
        {
            $fields[] = 'resourcetextNote';
            $values[] = $notes;
            $fields[] = 'resourcetextAddUserIdNote';
            $values[] = $this->session->getVar("setup_UserId");
        }
        if ($abstract)
        {
            $fields[] = 'resourcetextAbstract';
            $values[] = $abstract;
            $fields[] = 'resourcetextAddUserIdAbstract';
            $values[] = $this->session->getVar("setup_UserId");
        }
        $this->db->insert('resource_text', $fields, $values);
// For historical reasons, writing the URLs also takes place here (once upon a time, URLs were part of the resource_text table)
        if ($url)
        {
            if (!is_array($url))
            {
                $url = [$url];
            }
            $setPrimary = FALSE;
            foreach ($url as $u) {
            	$fields = ['resourceurlResourceId', 'resourceurlUrl'];
            	$values = [$this->resourceId, $u];
            	if (!$setPrimary) {
            		array_push($fields, 'resourceurlPrimary');
            		array_push($values, 1);
            		$setPrimary = TRUE;
            	}
            	$this->db->insert('resource_url', $fields, $values);
            }
        }
    }
    /**
     * Write keyword tables
     *
     * @param array $keywords
     */
    public function writeKeywordTables($keywords)
    {
        if ($this->kwIgnore)
        {
            return;
        }
        foreach ($keywords as $kWord)
        {
            if (!$kId = $this->keyword->checkExists($kWord))
            {
                $this->db->insert('keyword', ['keywordKeyword'], [$kWord]);
                $kId = $this->db->lastAutoID();
            }
            $this->db->insert(
                'resource_keyword',
                ['resourcekeywordResourceId', 'resourcekeywordKeywordId'],
                [$this->resourceId, $kId]
            );
        }
    }
    /**
     * Write resource_category table
     *
     * @param string $categories Comma-delimited list of category IDs.  At the very least, this must comprise '1' (the 'General' category)
     */
    public function writeResourcecategoryTable($categories)
    {
        foreach ($categories as $cId)
        {
            $this->db->insert(
                'resource_category',
                ['resourcecategoryResourceId', 'resourcecategoryCategoryId'],
                [$this->resourceId, $cId]
            );
        }
    }
    /**
     * Write resource_custom tables
     *
     * @param string $string
     * @param int $customId
     */
    public function writeResourcecustomTable($string, $customId)
    {
        $writeArray = [];
        $this->db->formatConditions(['customId' => $customId]);
        if ($this->db->selectFirstField('custom', 'customSize') == 'S')
        {
            $writeArray['resourcecustomShort'] = $string;
        }
        else
        {
            $writeArray['resourcecustomLong'] = $string;
        }
        $writeArray['resourcecustomAddUserIdCustom'] = intval($this->session->getVar("setup_UserId")); // No idea why, but needed
        $writeArray['resourcecustomCustomId'] = intval($customId); // No idea why, but needed
        $writeArray['resourcecustomResourceId'] = $this->resourceId;
        $this->db->insert('resource_custom', array_keys($writeArray), array_values($writeArray));
    }
    /**
     * writeImportRawTable - write import_raw table
     *
     * @param array $rejectedArray Rejected input values for this resource
     * @param int $bibtexStringId ID of the BibTeX string in the bibtex_string table. Default is FALSE
     * @param array $formData
     */
    public function writeImportrawTable($rejectedArray, $bibtexStringId = FALSE, $formData = [])
    {
        if (empty($rejectedArray) || !array_key_exists("import_Raw", $formData))
        {
            return;
        }
        $rejected = '';
        foreach ($rejectedArray as $key => $value)
        {
            $rejected .= "$key = $value" . LF;
        }
        $fields[] = 'importrawId';
        $values[] = $this->resourceId;
        if ($bibtexStringId)
        {
            $fields[] = 'importrawStringId';
            $values[] = $bibtexStringId;
        }
        $fields[] = 'importrawText';
        $values[] = base64_encode(serialize($rejected));
        $fields[] = 'importrawImportType';
        $values[] = $this->importType;
        $this->db->insert('import_raw', $fields, $values);
    }
    /**
     * Write user_bibliography_resource table
     *
     * @param string $bibId Comma-delimited string of user_bibliography ids
     */
    public function writeUserbibliographyresourceTable($bibId)
    {
        if (!is_array($bibId))
        {
            return;
        }
        foreach ($bibId as $bId)
        {
            if (!$bId || ($bId < 0))
            {
                continue;
            }
            $this->db->insert(
                'user_bibliography_resource',
                ['userbibliographyresourceResourceId', 'userbibliographyresourceBibliographyId'],
                [$this->resourceId, $bId]
            );
        }
    }
    /**
     * writeTagTable - write import tag to tag table
     *
     * @param array $formData
     *
     * @return false|int lastAutoId
     */
    public function writeTagTable($formData = [])
    {
        if (array_key_exists('import_TagId', $formData))
        {
            return $formData["import_TagId"];
        }
        if (!array_key_exists('import_Tag', $formData) || (!$tag = $formData["import_Tag"]))
        {
            return FALSE;
        }
        $this->db->insert('tag', ['tagTag'], [\HTML\removeNl($tag)]);

        return $this->db->lastAutoID();
    }
    /**
     * Write the bibtexKey field for a new resource
     */
    public function writeBibtexKey()
    {
        $letters = range('a', 'z');
        $sizeof = count($letters);
        $year = FALSE;
        $this->db->formatConditions(['resourceyearId' => $this->resourceId]);
        $recordset = $this->db->select(['resource_year'], ['resourceyearYear1',
            'resourceyearYear2', 'resourceyearYear3', 'resourceyearYear4', ]);
        $row = $this->db->fetchRow($recordset);
        if (is_array($row))
        {
            if ($row['resourceyearYear1'])
            {
                $year = $row['resourceyearYear1'];
            }
            elseif ($row['resourceyearYear2'])
            {
                $year = $row['resourceyearYear2'];
            }
            elseif ($row['resourceyearYear3'])
            {
                $year = $row['resourceyearYear3'];
            }
            elseif ($row['resourceyearYear4'])
            {
                $year = $row['resourceyearYear4'];
            }
        }
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorMain');
        $this->db->formatConditions(['resourcecreatorResourceId' => $this->resourceId]);
        $this->db->formatConditions(['resourcecreatorOrder' => '1']);
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourcecreatorRole', TRUE, FALSE);
        $this->db->limit(1, 0); // pick just the first one
        $recordset = $this->db->select(['resource_creator'], ['creatorSurname', 'creatorPrefix']);
        $row = $this->db->fetchRow($recordset);
        $keyMade = FALSE;
        if ((!is_array($row) || !array_key_exists('creatorSurname', $row) || !$row['creatorSurname']))
        { // anonymous
            $base = 'anon' . $year;
        }
        else
        {
            $prefix = '';
            if ($row['creatorPrefix'])
            {
                $prefix = utf8_decode($row['creatorPrefix']);
                foreach ($this->bibConfig->bibtexSpChPlain as $key => $value)
                {
                    $char = preg_quote(\UTF8\mb_chr($key), '/');
                    $prefix = preg_replace("/$char/u", $value, $prefix);
                }
                $prefix = preg_replace("/\\W/u", '', $prefix);
            }
            $surname = utf8_decode($row['creatorSurname']);
            foreach ($this->bibConfig->bibtexSpChPlain as $key => $value)
            {
                $char = preg_quote(\UTF8\mb_chr($key), '/');
                $surname = preg_replace("/$char/u", $value, $surname);
            }
            $surname = preg_replace("/\\W/u", '', $surname);
            $base = $prefix . $surname . $year;
        }
        $bibtexKey = $base;
        for ($i = 0; $i < $sizeof; $i++)
        {
            if (array_search($bibtexKey, $this->bibtexKeys) === FALSE)
            {
                $keyMade = TRUE;

                break;
            }
            $bibtexKey = $base . $letters[$i];
        }
        if (!$keyMade)
        {
            $bibtexKey = $base . '.' . $this->resourceId; // last resort
        }
        $bibtexKey = str_replace(' ', '', $bibtexKey);
        $this->bibtexKeys[] = $bibtexKey;
        $this->db->formatConditions(['resourceId' => $this->resourceId]);
        $this->db->update('resource', ['resourceBibtexKey' => $bibtexKey]);
    }
    /**
     * Tidy up the database by checking for creators etc. that do not have resources.
     *
     * This should be called just once at the end of multiple inserts.
     */
    public function tidyTables()
    {
        $this->creator->removeHanging();
        $this->publisher->removeHanging();
        $this->collection->removeHanging();
        $this->keyword->removeHanging();
    }
    /**
     * promptFieldNames - print field name mapping to browser as an intermediate stage when invalid field names are found in the input.
     *
     * This allows users to map them to any existing custom fields.
     *
     * @param array $entries
     * @param array $inputTypes
     * @param array $map
     * @param array $invalidFieldNames
     * @param array $formData
     * @param mixed $strings array|FALSE. Default is FALSE
     *
     * @return array 1st element is error message or FALSE, 2nd element is string for display, 3rd element is the temp_storage table id
     */
    public function promptFieldNames($entries, $inputTypes, $map, $invalidFieldNames, $formData, $strings = FALSE)
    {
        // Do some system management
        FILE\tidyFiles();
        
        // Write entries as serialised array temporarily to a data folder
        $serArray = base64_encode(serialize($entries));
        
        $dirName = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]);
        $fileName = sha1($serArray);
        $entriesFileName = $fullFileName = $dirName . DIRECTORY_SEPARATOR . $fileName;
        if (file_put_contents($fullFileName, $serArray) === FALSE)
        {
            return [$this->errors->text("file", "write", ": " . $fileName), FALSE];
        }
        // Write $this->strings as serialised array temporarily to a data folder
        if ($strings)
        {
            $stringArray = base64_encode(serialize($strings));
            $fileName = sha1($stringArray);
            $stringsFileName = $fullFileName = $dirName . DIRECTORY_SEPARATOR . $fileName;
            if (file_put_contents($fullFileName, $stringArray) === FALSE)
            {
                return [$this->errors->text("file", "write", ": " . $fileName), FALSE];
            }
        }
        $formData["import_UnrecognisedFields"] = $invalidFieldNames;
        // Create select boxes of all valid field names in WIKINDX including custom fields
        $possibleFields[0] = $this->messages->text('misc', 'ignore');
        foreach ($map->types as $wkType => $extType)
        {
            if (array_search($extType, $inputTypes) === FALSE)
            {
                continue;
            }
            foreach ($map->{$wkType}['possible'] as $field)
            {
                if (array_search($field, $map->noMap) === FALSE)
                {
                    $possibleFields[] = $field;
                }
            }
        }
        $possibleFields = array_unique($possibleFields);
        // Get custom fields
        $recordset = $this->db->select('custom', ['customId', 'customLabel']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $possibleFields[] = $row['customId'] . '&nbsp;&nbsp;custom:&nbsp;&nbsp;' . $row['customLabel'];
        }
        if ($this->importType == 'endnote')
        {
            $pString = \HTML\p($this->messages->text('import', 'invalidField3'));
        }
        else
        {
            $pString = \HTML\p($this->messages->text('import', 'invalidField1'));
        }
        if ($this->importType == 'bibtex')
        {
            $pString .= \FORM\formHeader("import_IMPORTBIBTEX_CORE");
            $pString .= \FORM\hidden('method', 'stage2Invalid');
        }
        if (($this->importType == 'endnote'))
        {
            $pString .= \FORM\formHeader("importexportbib_importEndnote");
            $pString .= \FORM\hidden('method', 'stage2Invalid');
        }
        if (array_key_exists('type', $this->vars))
        {
            $pString .= \FORM\hidden('type', $this->vars['type']);
        }
        foreach ($invalidFieldNames as $invalidField)
        {
            $pString .= \HTML\p(\FORM\selectFBox(
                \HTML\strong($invalidField),
                'import_' . $invalidField,
                $possibleFields,
                5
            ));
        }
        if (($this->importType == 'bibtex'))
        {
            $pString .= \HTML\p(\FORM\checkbox(
                $this->messages->text('import', 'invalidField2'),
                "import_Precedence"
            ));
        }
        $uuid = \TEMPSTORAGE\getUuid($this->db);
        $pString .= \FORM\hidden('uuid', $uuid);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Submit")));
        // store data
        $formData["import_FileNameEntries"] = $entriesFileName;
        if (isset($stringsFileName))
        {
            $formData["import_FileNameStrings"] = $stringsFileName;
        }
        \TEMPSTORAGE\store($this->db, $uuid, $formData);

        return [FALSE, $pString, $uuid];
    }
    /**
     * getUnrecognised fields
     *
     * Follows from prompting for field mapping
     *
     * @param array $formData
     *
     * @return array 1st element is error message or FALSE, 2nd element is array of custom fields, 3rd element is array of unrecognized fields
     */
    public function getUnrecognisedFields($formData = [])
    {
        $mapFields = $customFields = [];
        foreach ($formData["import_UnrecognisedFields"] as $key)
        {
            $importKey = 'import_' . $key;
            if (array_key_exists($importKey, $this->vars) && ($this->vars[$importKey] != $this->messages->text('misc', 'ignore')) &&
                (array_search($this->vars[$importKey], $mapFields) !== FALSE))
            {
                return [$this->errors->text("file", "fieldMap"), [], []];
            }
            elseif ($this->vars[$importKey] != $this->messages->text('misc', 'ignore'))
            {
                $mapFields[] = $this->vars[$importKey];
            }
            if (array_key_exists($importKey, $this->vars) && ($this->vars[$importKey] == $this->messages->text('misc', 'ignore')))
            {
                unset($this->vars[$importKey]);
            }
            elseif (array_key_exists($importKey, $this->vars) &&
                (count($split = \UTF8\mb_explode("custom:", str_replace('&nbsp;&nbsp;', '', $this->vars[$importKey]))) == 2))
            {
                $customFields[$key] = $split[0];
                unset($this->vars[$importKey]);
            }
        }

        return [FALSE, $customFields, $formData["import_UnrecognisedFields"]];
    }
    /**
     * Set collectionDefault column in the collections table
     */
    public function collectionDefaults()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "collection", "COLLECTIONDEFAULTMAP.php"]));
        $defaultMap = new COLLECTIONDEFAULTMAP();
        $typesArray = array_unique(array_values($defaultMap->collectionTypes));
        $collectionArray = [];
        // For each 1MB max_allowed_packet (1048576 bytes), 600 updates in one go seems fine as a value for $maxCounts (based on trial and error)
        $maxCounts = floor(600 * (WIKINDX_DB_MAX_ALLOWED_PACKET / 1048576));
        foreach ($typesArray as $type)
        {
            $fieldNames = [];
            foreach ($defaultMap->{$type} as $typeKey => $typeKeyArray)
            {
                $typeKey = str_replace('_', '', $typeKey);
                if (($typeKey == 'resource') && !empty($typeKeyArray))
                {
                    $this->db->leftJoin('resource', 'resourceId', 'resourcemiscId');
                    foreach ($typeKeyArray as $key => $value)
                    {
                        $fieldName = $typeKey . $key;
                        $fieldNames[] = $fieldName;
                    }
                }
                elseif (($typeKey == 'resourcemisc') && !empty($typeKeyArray))
                {
                    foreach ($typeKeyArray as $key => $value)
                    {
                        $fieldName = $typeKey . $key;
                        $fieldNames[] = $fieldName;
                    }
                }
                elseif (($typeKey == 'resourceyear') && !empty($typeKeyArray))
                {
                    $this->db->leftJoin('resource_year', 'resourceyearId', 'resourcemiscId');
                    foreach ($typeKeyArray as $key => $value)
                    {
                        $fieldName = $typeKey . $key;
                        $fieldNames[] = $fieldName;
                    }
                }
            }
            if (empty($fieldNames))
            {
                continue;
            }
            $fieldNames[] = 'collectionId';
            $fieldNames[] = 'resourcemiscId';
            $this->db->formatConditions(['resourcemiscCollection' => ' IS NOT NULL']);
            $this->db->formatConditions(['collectionDefault' => ' IS NULL']);
            $this->db->formatConditions(['collectionType' => $type]);
            $this->db->leftJoin('collection', 'collectionId', 'resourcemiscCollection');
            $resultset = $this->db->select('resource_misc', $fieldNames, TRUE);
            while ($row = $this->db->fetchRow($resultset))
            {
                foreach ($fieldNames as $fieldName)
                {
                    if (($fieldName == 'collectionId') || ($fieldName == 'resourcemiscId'))
                    {
                        continue;
                    }
                    if (
                        !array_key_exists($row['collectionId'], $collectionArray)
                        ||
                        (array_key_exists($row['collectionId'], $collectionArray)
                            && (!array_key_exists($fieldName, $collectionArray[$row['collectionId']])))
                        ) {
                        if ($row[$fieldName])
                        {
                            $collectionArray[$row['collectionId']][$fieldName] = $row[$fieldName];
                        }
                    }
                }
                if (array_key_exists('resource_creator', $defaultMap->{$type}) && !empty($defaultMap->{$type}['resource_creator']))
                {
                    $creators = [];
                    $roles = array_keys($defaultMap->{$type}['resource_creator']);
                    $this->db->formatConditions(['resourcecreatorResourceId' => $row['resourcemiscId']]);
                    $this->db->formatConditionsOneField($roles, 'resourcecreatorRole');
                    $this->db->orderBy('resourcecreatorOrder', TRUE, FALSE);
                    $resultsetC = $this->db->select('resource_creator', ['resourcecreatorCreatorId', 'resourcecreatorRole', 'resourcecreatorOrder']);
                    while ($rowC = $this->db->fetchRow($resultsetC))
                    {
                        $order = $rowC['resourcecreatorOrder'] - 1;
                        $creators['Creator' . $rowC['resourcecreatorRole'] . '_' . $order . '_select'] = $rowC['resourcecreatorCreatorId'];
                    }
                    if (!empty($creators))
                    {
                        $collectionArray[$row['collectionId']]['creators'] = $creators;
                    }
                }
            }
            if (!empty($collectionArray))
            {
                $count = 0;
                $updateArray = [];
                foreach ($collectionArray as $collectionId => $array)
                {
                    ++$count;
                    $updateArray[$collectionId] = serialize($array);
                    if ($count >= $maxCounts)
                    {
                        $this->db->multiUpdate('collection', 'collectiondefault', 'collectionId', $updateArray);
                        $updateArray = [];
                        $count = 0;
                    }
                }
                if (!empty($updateArray))
                { // do the remainder
                    $this->db->multiUpdate('collection', 'collectiondefault', 'collectionId', $updateArray);
                }
            }
        }
    }
    /**
     * Delete caches if required.  Must be deleted if various creators, publishers etc. have been added with this import
     *
     * @param mixed $data
     */
    private function deleteCaches($data)
    {
        if ($data['deleteCacheCreators'])
        {
            // remove cache files for creators
            $this->db->deleteCache('cacheResourceCreators');
            $this->db->deleteCache('cacheMetadataCreators');
        }
        if ($data['deleteCachePublishers'])
        {
            // remove cache files for publishers
            $this->db->deleteCache('cacheResourcePublishers');
            $this->db->deleteCache('cacheMetadataPublishers');
            $this->db->deleteCache('cacheConferenceOrganisers');
        }
        if ($data['deleteCacheCollections'])
        {
            // remove cache files for collections
            $this->db->deleteCache('cacheResourceCollections');
            $this->db->deleteCache('cacheMetadataCollections');
            $this->db->deleteCache('cacheResourceCollectionTitles');
            $this->db->deleteCache('cacheResourceCollectionShorts');
        }
        if ($data['deleteCacheKeywords'])
        {
            // remove cache files for keywords
            $this->db->deleteCache('cacheResourceKeywords');
            $this->db->deleteCache('cacheMetadataKeywords');
            $this->db->deleteCache('cacheQuoteKeywords');
            $this->db->deleteCache('cacheParaphraseKeywords');
            $this->db->deleteCache('cacheMusingKeywords');
            $this->db->deleteCache('cacheKeywords');
        }
    }
}
