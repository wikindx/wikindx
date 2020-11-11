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
 * RESOURCECATEGORYEDIT class
 *
 * Edit category, keyword and user tags for a single resource..
 */
class RESOURCECATEGORYEDIT
{
    public $userTags = [];
    public $formData = []; // Used by modules/list/LISTADDTO.php
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $icons;
    private $session;
    private $badInput;
    private $category;
    private $keyword;
    private $userTagsObject;
    private $categories = [];
    private $subcategories = [];
    private $keywords = [];
    private $subcatArray = [];

    public function __construct()
    {
        $gatekeep = FACTORY_GATEKEEP::getInstance();
        $gatekeep->init();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->category = FACTORY_CATEGORY::getInstance();
        $this->keyword = FACTORY_KEYWORD::getInstance();

        $this->session->clearArray('edit');
        $this->categories = $this->category->grabAll();
        $this->keywords = $this->keyword->grabAll();
        $this->userTagsObject = FACTORY_USERTAGS::getInstance();
        $this->userTags = $this->userTagsObject->grabAll();
        if (array_key_exists('id', $this->vars))
        {
            $return = '&nbsp;&nbsp;' . \HTML\a(
                $this->icons->getClass("edit"),
                $this->icons->getHTML("Return"),
                'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->vars['id']
            );
        }
        else
        {
            $return = FALSE;
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "resourceEdit") . $return);
    }
    /**
     * Display form for editing
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        if (!array_key_exists('id', $this->vars) || !$this->vars['id'])
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $pString = $message;
        $pString .= \FORM\formHeader('resource_RESOURCECATEGORYEDIT_CORE', "onsubmit=\"selectAll();return true;\"");
        $pString .= \FORM\hidden('method', 'edit');
        $pString .= \FORM\hidden("id", $this->vars['id']);
        $pString .= $this->getTable();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));
        $pString .= \FORM\formEnd();
        \AJAX\loadJavascript(WIKINDX_URL_BASE . '/core/modules/resource/resourceCategoryEdit.js?ver=' . WIKINDX_PUBLIC_VERSION);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display categories etc. in a table.
     *
     * Can be called from modules/list/LISTADDTO in which case $multiIds = TRUE
     *
     * @param array|false $multiIds
     *
     * @return string
     */
    public function getTable($multiIds = FALSE)
    {
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\HTML\div('categoryDiv', $this->displayCategory($multiIds)));
        $pString .= \HTML\td(\HTML\div('subcategoryDiv', $this->displaySubcategory($multiIds)));
        $pString .= \HTML\td(\HTML\div('languageDiv', $this->displayLanguage($multiIds)));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;', FALSE, FALSE, FALSE, 2) . \HTML\trEnd() . \HTML\trStart();
        $pString .= \HTML\td(\HTML\div('keywordDiv', $this->displayKeyword($multiIds)));
        if (!empty($this->userTags))
        {
            $pString .= \HTML\td(\HTML\div('usertagDiv', $this->displayUserTags($multiIds)));
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * edit
     */
    public function edit()
    {
        if (!array_key_exists('id', $this->vars) || !$this->vars['id'])
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        } // force to 'General' if nothing there
        if (!array_key_exists('categoryIds', $this->vars) || empty($this->vars['categoryIds']))
        {
            $this->vars['categoryIds'][0] = 1;
        }
        $this->storeData();
        $this->writeLanguageTable();
        $this->writeCategoryTable();
        $this->writeKeywordTable();
        $this->writeUserTagsTable();
        // update timestamp
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['id']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        // Update edit user
        $this->db->formatConditions(['resourcemiscId' => $this->vars['id']]);
        $this->db->update('resource_misc', ['resourcemiscEditUserIdResource' => $this->session->getVar("setup_UserId")]);
        $this->navigate($this->success->text("categoryKeywordEdit"));
    }
    /**
     * display language form
     *
     * @param array $multiIds
     *
     * @return false|string
     */
    public function displayLanguage($multiIds)
    {
        $this->db->orderBy('languageLanguage');
        $resultset = $this->db->select('language', ['languageId', 'languageLanguage']);
        if ($this->db->numRows($resultset))
        {
            $pString = FALSE;
            if (!$multiIds)
            {
                $pString .= \HTML\tableStart()
                    . \HTML\trStart()
                    . \HTML\td('&nbsp;', '', 3)
                    . \HTML\trEnd()
                    . \HTML\tableEnd();
            }
            $pString .= \HTML\tableStart('generalTable');
            $pString .= \HTML\trStart();
            $languages[0] = $this->messages->text("misc", "ignore");
            while ($row = $this->db->fetchRow($resultset))
            {
                $languages[$row['languageId']] = \HTML\dbToFormtidy($row['languageLanguage']);
            }
            $selected = [];
            if (!$multiIds)
            {
                $this->db->formatConditions(['resourcelanguageResourceId' => $this->vars['id']]);
                $resultset = $this->db->select('resource_language', 'resourcelanguageLanguageId');
                while ($row = $this->db->fetchRow($resultset))
                {
                    $selected[] = $row['resourcelanguageLanguageId'];
                }
                if (!empty($selected))
                {
                    $pString .= \HTML\td(\FORM\selectedBoxValueMultiple($this->messages->text(
                        'resources',
                        'languages'
                    ), 'languageIds', $languages, $selected, 6) . BR .
                    \HTML\span(\HTML\aBrowse(
                        'green',
                        '',
                        $this->messages->text("hint", "hint"),
                        '#',
                        "",
                        $this->messages->text("hint", "multiples")
                    ), 'hint'), 'padding3px');
                }
                else
                {
                    $pString .= \HTML\td(\FORM\selectFBoxValueMultiple($this->messages->text(
                        'resources',
                        'languages'
                    ), 'languageIds', $languages, 6) . BR .
                    \HTML\span(\HTML\aBrowse(
                        'green',
                        '',
                        $this->messages->text("hint", "hint"),
                        '#',
                        "",
                        $this->messages->text("hint", "multiples")
                    ), 'hint'), 'padding3px');
                }
            }
            else
            {
                $checkbox = FALSE;
                if ($multiIds)
                {
                    $jScript = 'index.php?action=list_LISTADDTO_CORE&method=displayLanguage';
                    $jsonArray[] = [
                        'startFunction' => 'triggerFromSelect',
                        'script' => "$jScript",
                        'triggerField' => 'displayLanguage',
                        'targetDiv' => 'languageDiv',
                    ];
                    $js = \AJAX\jActionForm('onchange', $jsonArray);
                    $check = !empty($this->formData) && !array_key_exists('displayLanguage', $this->formData) ? FALSE : TRUE;
                    $checkbox = "&nbsp;&nbsp;" . \FORM\checkbox(FALSE, "displayLanguage", $check, '', $js = '');
                }
                $pString .= \HTML\td(\FORM\selectFBoxValueMultiple($this->messages->text(
                    'resources',
                    'languages'
                ) . $checkbox, 'languageIds', $languages, 6) . BR .
                    \HTML\span(\HTML\aBrowse(
                        'green',
                        '',
                        $this->messages->text("hint", "hint"),
                        '#',
                        "",
                        $this->messages->text("hint", "multiples")
                    ), 'hint'), 'padding3px');
            }
            if ($multiIds)
            {
                if (!empty($this->formData) && array_key_exists('languageRadio', $this->formData) &&
                    ($this->formData['languageRadio'] == 'remove'))
                {
                    $add = FALSE;
                    $remove = 'CHECKED';
                }
                else
                {
                    $add = 'CHECKED';
                    $remove = FALSE;
                }
                $radios = \HTML\span(\FORM\radioButton(FALSE, 'languageRadio', 'add', $add) .
                    $this->messages->text('misc', 'add'), "small") . BR;
                $radios .= \HTML\span(\FORM\radioButton(FALSE, 'languageRadio', 'remove', $remove) .
                    $this->messages->text('misc', 'remove'), "small");
                $pString .= \HTML\td($radios, 'padding3px');
            }
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();

            return $pString;
        }

        return FALSE;
    }
    /**
     * display category form
     *
     * @param array $multiIds
     *
     * @return string
     */
    public function displayCategory($multiIds)
    {
        $catArray = [];
        if (!empty($this->formData))
        {
            if (array_key_exists('categoryIds', $this->formData))
            {
                $catArray = $this->formData['categoryIds'];
            }
        }
        elseif (!$multiIds)
        {
            $this->db->formatConditions(['resourcecategoryResourceId' => $this->vars['id']]);
            $resultset = $this->db->select('resource_category', ['resourcecategoryCategoryId', 'resourcecategorySubcategoryId']);
            while ($row = $this->db->fetchRow($resultset))
            {
                $catArray[] = $row['resourcecategoryCategoryId'];
                if ($row['resourcecategorySubcategoryId'])
                {
                    $this->subcatArray[] = $row['resourcecategorySubcategoryId'];
                }
            }
        }
        $selectedCategories = [];
        foreach ($catArray as $key)
        {
            if (!array_key_exists($key, $this->categories))
            {
                continue;
            }
            $selectedCategories[$key] = $this->categories[$key];
            unset($this->categories[$key]);
        }
        $td = FALSE;
        if (!$multiIds)
        {
            $td .= \HTML\tableStart() . \HTML\trStart() .
                \HTML\td($this->messages->text("hint", "categories"), '', 3) .
                \HTML\trEnd() . \HTML\tableEnd();
            $radios = FALSE;
        }
        $td .= \HTML\tableStart('generalTable');
        $td .= \HTML\trStart();
        $checkbox = FALSE;
        if ($multiIds)
        {
            $jScript = 'index.php?action=list_LISTADDTO_CORE&method=displayCategory';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'displayCategory',
                'targetDiv' => 'categoryDiv',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            $check = !empty($this->formData) && !array_key_exists('displayCategory', $this->formData) ? FALSE : TRUE;
            $checkbox = "&nbsp;&nbsp;" . \FORM\checkbox(FALSE, "displayCategory", $check, '', $js = '');
            if (!empty($this->formData) && array_key_exists('categoryRadio', $this->formData) &&
                ($this->formData['categoryRadio'] == 'remove'))
            {
                $add = FALSE;
                $remove = 'CHECKED';
            }
            else
            {
                $add = 'CHECKED';
                $remove = FALSE;
            }
            $radios = \HTML\span(\FORM\radioButton(FALSE, 'categoryRadio', 'add', $add) .
                $this->messages->text('misc', 'add'), "small") . BR;
            $radios .= \HTML\span(\FORM\radioButton(FALSE, 'categoryRadio', 'remove', $remove) .
                $this->messages->text('misc', 'remove'), "small");
        }
        $td .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "availableCategory") . $checkbox,
            'availableCategory',
            $this->categories,
            6
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint'), 'padding3px left width18percent');
        list($toRightImage, $toLeftImage) = $this->category->transferArrowsCat();
        $td .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage) . $radios, 'padding3px left width5percent');
        $td .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "category"),
            'categoryIds',
            $selectedCategories,
            6
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint'), 'padding3px left width18percent');
        $td .= \HTML\trEnd();
        $td .= \HTML\tableEnd();

        return $td;
    }
    /**
     * display subcategory form
     *
     * @param array $multiIds
     *
     * @return false|string
     */
    public function displaySubcategory($multiIds)
    {
        if (!is_array($this->subcategories = $this->category->grabSubAll(TRUE)))
        {
            return FALSE;
        }
        $pString = FALSE;
        $checkbox = FALSE;
        $selectedSubcategories = [];
        if (!empty($this->formData))
        {
            if (array_key_exists('subcategoryIds', $this->formData))
            {
                $this->subcatArray = $this->formData['subcategoryIds'];
            }
            else
            {
                $this->subcatArray = [];
            }
        }
        foreach ($this->subcatArray as $key)
        {
            if (!array_key_exists($key, $this->subcategories))
            {
                continue;
            }
            $selectedSubcategories[$key] = $this->subcategories[$key];
            unset($this->subcategories[$key]);
        }
        if (!$multiIds)
        {
            $pString .= \HTML\tableStart()
                . \HTML\trStart()
                . \HTML\td('&nbsp;', '', 3)
                . \HTML\trEnd() . \HTML\tableEnd();
            $radios = FALSE;
        }
        else
        {
            $jScript = 'index.php?action=list_LISTADDTO_CORE&method=displaySubcategory';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'displaySubcategory',
                'targetDiv' => 'subcategoryDiv',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            $check = !empty($this->formData) && !array_key_exists('displaySubcategory', $this->formData) ? FALSE : TRUE;
            $checkbox = "&nbsp;&nbsp;" . \FORM\checkbox(FALSE, "displaySubcategory", $check, '', $js = '');
            if (!empty($this->formData) && array_key_exists('subcategoryRadio', $this->formData) &&
                ($this->formData['subcategoryRadio'] == 'remove'))
            {
                $add = FALSE;
                $remove = 'CHECKED';
            }
            else
            {
                $add = 'CHECKED';
                $remove = FALSE;
            }
            $radios = \HTML\span(\FORM\radioButton(FALSE, 'subcategoryRadio', 'add', $add) .
                $this->messages->text('misc', 'add'), "small") . BR;
            $radios .= \HTML\span(\FORM\radioButton(FALSE, 'subcategoryRadio', 'remove', $remove) .
                $this->messages->text('misc', 'remove'), "small");
        }
        $pString .= \HTML\tableStart('generalTable');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "availableSubcategory") . $checkbox,
            'availableSubcategory',
            $this->subcategories,
            6
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint'), 'padding3px left width18percent');
        list($toRightImage, $toLeftImage) = $this->category->transferArrowsSubcat();
        $pString .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage) . $radios, 'padding3px left width5percent');
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text('select', "subcategory"),
            'subcategoryIds',
            $selectedSubcategories,
            6
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint'), 'padding3px left width18percent');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * display keyword form
     *
     * @param array $multiIds
     *
     * @return string
     */
    public function displayKeyword($multiIds)
    {
        $checkbox = FALSE;
        $keywordString = FALSE;
        $pString = \HTML\tableStart('generalTable');
        $pString .= \HTML\trStart();
        if (!empty($this->keywords))
        {
            if (!$multiIds)
            {
                $radios = FALSE;
            }
            else
            {
                $jScript = 'index.php?action=list_LISTADDTO_CORE&method=displayKeyword';
                $jsonArray[] = [
                    'startFunction' => 'triggerFromSelect',
                    'script' => "$jScript",
                    'triggerField' => 'displayKeyword',
                    'targetDiv' => 'keywordDiv',
                ];
                $js = \AJAX\jActionForm('onchange', $jsonArray);
                $check = !empty($this->formData) && !array_key_exists('displayKeyword', $this->formData) ? FALSE : TRUE;
                $checkbox = "&nbsp;&nbsp;" . \FORM\checkbox(FALSE, "displayKeyword", $check, '', $js = '');
                if (!empty($this->formData) && array_key_exists('keywordRadio', $this->formData) &&
                    ($this->formData['keywordRadio'] == 'remove'))
                {
                    $add = FALSE;
                    $remove = 'CHECKED';
                }
                else
                {
                    $add = 'CHECKED';
                    $remove = FALSE;
                }
                $radios = \HTML\span(\FORM\radioButton(
                    $this->messages->text('misc', 'add'),
                    'keywordRadio',
                    'add',
                    $add
                ), "small") . BR;
                $radios .= \HTML\span(\FORM\radioButton(
                    $this->messages->text('misc', 'remove'),
                    'keywordRadio',
                    'remove',
                    $remove
                ), "small");
                $radios = \HTML\p($radios);
            }
            $keywords = $keywordArray = [];
            if (!empty($this->formData) && trim($this->formData['keywords']))
            {
                $keywordString = $this->formData['keywords'];
            }
            else
            {
                if (!$multiIds)
                {
                    $this->db->formatConditions(['resourcekeywordResourceId' => $this->vars['id']]);
                    $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
                    $this->db->orderBy('keywordKeyword');
                    $resultset = $this->db->select('resource_keyword', ['resourcekeywordKeywordId', 'keywordKeyword']);
                    while ($row = $this->db->fetchRow($resultset))
                    {
                        $keywordArray[] = $row['keywordKeyword'];
                    }
                    $keywordString = isset($keywordArray) ? implode(', ', $keywordArray) : FALSE;
                }
                else
                {
                    $keywordString = FALSE;
                }
            }
            // If preferences reduce long keywords, we want to transfer the original rather than the condensed version.
            // Store the base64-encoded value for retrieval in the javascript.
            foreach ($this->keywords as $key => $value)
            {
                $key = $key . '_' . base64_encode($value);
                $keywords[$key] = html_entity_decode($value);
            }
            $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
                $this->messages->text('resources', 'availableKeywords') . $checkbox,
                'fromKeywords',
                $keywords,
                6
            ) . BR . \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "multiples")
            ), 'hint'), 'padding3px left width18percent');
            $jsonArray = [];
            $jsonArray[] = [
                'startFunction' => 'transferKeyword',
            ];
            $toLeftImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
            $pString .= \HTML\td($toLeftImage . $radios, 'padding3px', 'left', FALSE, FALSE, '5%');
            // Entry text area
            $pString .= \HTML\td(\FORM\textareaInput(
                $this->messages->text("resources", "keywords"),
                "keywords",
                \HTML\dbToFormTidy($keywordString),
                50,
                5
            ) . BR . \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "keywords")
            ), 'hint'), 'padding3px left width18percent');
        }
        else
        {
            $pString .= \HTML\td(\FORM\textareaInput(
                $this->messages->text("resources", "keywords"),
                "keywords",
                $keywordString,
                50,
                5
            ) . BR . \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "multiples")
            ), 'hint'), 'padding3px left width18percent');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * display userTags form
     *
     * @param array $multiIds
     *
     * @return string
     */
    public function displayUserTags($multiIds)
    {
        $checkbox = FALSE;
        $userTagString = '';
        if (!$multiIds)
        {
            $radios = FALSE;
        }
        else
        {
            $jScript = 'index.php?action=list_LISTADDTO_CORE&method=displayUsertag';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'displayUsertag',
                'targetDiv' => 'usertagDiv',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            $check = !empty($this->formData) && !array_key_exists('displayUsertag', $this->formData) ? FALSE : TRUE;
            $checkbox = "&nbsp;&nbsp;" . \FORM\checkbox(FALSE, "displayUsertag", $check, '', $js = '');
            if (!empty($this->formData) && array_key_exists('usertagRadio', $this->formData) &&
                ($this->formData['usertagRadio'] == 'remove'))
            {
                $add = FALSE;
                $remove = 'CHECKED';
            }
            else
            {
                $add = 'CHECKED';
                $remove = FALSE;
            }
            $radios = \HTML\span(\FORM\radioButton(
                $this->messages->text('misc', 'add'),
                'usertagRadio',
                'add',
                $add
            ), "small") . BR;
            $radios .= \HTML\span(\FORM\radioButton(
                $this->messages->text('misc', 'remove'),
                'usertagRadio',
                'remove',
                $remove
            ), "small");
            $radios = \HTML\p($radios);
        }
        $tagIds = $userTags = [];
        if (!empty($this->formData) && trim($this->formData['userTags']))
        {
            $userTagString = $this->formData['userTags'];
        }
        else
        {
            if (!$multiIds)
            {
                $this->db->formatConditions(['resourceusertagsResourceId' => $this->vars['id']]);
                $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
                $this->db->leftJoin('user_tags', 'usertagsId', 'resourceusertagsTagId');
                $resultset = $this->db->select('resource_user_tags', 'resourceusertagsTagId');
                while ($row = $this->db->fetchRow($resultset))
                {
                    $tagIds[] = $this->userTags[$row['resourceusertagsTagId']];
                }
                $userTagString = isset($tagIds) ? implode(', ', $tagIds) : FALSE;
            }
            else
            {
                $userTagString = FALSE;
            }
        }
        foreach ($this->userTags as $userTagId => $userTagName)
        {
            // If preferences reduce long userTags, we want to transfer the original rather than the condensed version.
            // Store the base64-encoded value for retrieval in the javascript.
            $userTagId = $userTagId . '_' . base64_encode($userTagName);
            $userTags[$userTagId] = html_entity_decode($userTagName);
        }
        $pString = \HTML\tableStart('generalTable');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
            $this->messages->text('resources', 'availableUserTags') . $checkbox,
            'fromUserTags',
            $userTags,
            6
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint'), 'padding3px left width18percent');
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'transferUserTag',
        ];
        $toLeftImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $pString .= \HTML\td($toLeftImage . $radios, 'padding3px left width5percent');
        // Entry text area
        $pString .= \HTML\td(\FORM\textareaInput(
            $this->messages->text("resources", "userTags"),
            "userTags",
            $userTagString,
            50,
            5
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "userTags")
        ), 'hint'), 'padding3px left width18percent');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Store form data in case of missing languageIds
     */
    public function storeData()
    {
        if (array_key_exists('languageIds', $this->vars))
        {
            $this->formData['languageIds'] = $this->vars['languageIds'];
        }
        if (array_key_exists('languageRadio', $this->vars))
        {
            $this->formData['languageRadio'] = $this->vars['languageRadio'];
        }
        if (array_key_exists('displayLanguage', $this->vars))
        {
            $this->formData['displayLanguage'] = $this->vars['displayLanguage'];
        }
        if (array_key_exists('categoryIds', $this->vars))
        {
            $this->formData['categoryIds'] = $this->vars['categoryIds'];
        }
        if (array_key_exists('categoryRadio', $this->vars))
        {
            $this->formData['categoryRadio'] = $this->vars['categoryRadio'];
        }
        if (array_key_exists('displayCategory', $this->vars))
        {
            $this->formData['displayCategory'] = $this->vars['displayCategory'];
        }
        if (array_key_exists('subcategoryIds', $this->vars))
        {
            $this->formData['subcategoryIds'] = $this->vars['subcategoryIds'];
        }
        if (array_key_exists('subcategoryRadio', $this->vars))
        {
            $this->formData['subcategoryRadio'] = $this->vars['subcategoryRadio'];
        }
        if (array_key_exists('displaySubcategory', $this->vars))
        {
            $this->formData['displaySubcategory'] = $this->vars['displaySubcategory'];
        }
        if (array_key_exists('keywords', $this->vars))
        {
            $this->formData['keywords'] = \UTF8\mb_trim($this->vars['keywords']);
        }
        if (array_key_exists('keywordRadio', $this->vars))
        {
            $this->formData['keywordRadio'] = $this->vars['keywordRadio'];
        }
        if (array_key_exists('displayKeyword', $this->vars))
        {
            $this->formData['displayKeyword'] = $this->vars['displayKeyword'];
        }
        if (array_key_exists('userTags', $this->vars))
        {
            $this->formData['userTags'] = \UTF8\mb_trim($this->vars['userTags']);
        }
        if (array_key_exists('usertagRadio', $this->vars))
        {
            $this->formData['usertagRadio'] = $this->vars['usertagRadio'];
        }
        if (array_key_exists('displayUsertag', $this->vars))
        {
            $this->formData['displayUsertag'] = $this->vars['displayUsertag'];
        }
        if (array_key_exists('replaceExisting', $this->vars))
        {
            $this->formData['replaceExisting'] = TRUE;
        }
    }
    /**
     * write to resource_language
     */
    private function writeLanguageTable()
    {
        // remove all old languages from resource
        $this->db->formatConditions(['resourcelanguageResourceId' => $this->vars['id']]);
        $this->db->delete('resource_language');
        // add new languages
        foreach ($this->vars['languageIds'] as $lId)
        {
            if (!$lId)
            { // IGNORE
                continue;
            }
            $this->db->insert(
                'resource_language',
                ['resourcelanguageResourceId', 'resourcelanguageLanguageId'],
                [$this->vars['id'], $lId]
            );
        }
    }
    /**
     * write to resource_category
     */
    private function writeCategoryTable()
    {
        // remove all old categories and subcategories from resource
        $this->db->formatConditions(['resourcecategoryResourceId' => $this->vars['id']]);
        $this->db->delete('resource_category');
        // add new categories
        foreach ($this->vars['categoryIds'] as $cId)
        {
            $this->db->insert(
                'resource_category',
                ['resourcecategoryResourceId', 'resourcecategoryCategoryId'],
                [$this->vars['id'], $cId]
            );
        }
        if (array_key_exists('subcategoryIds', $this->vars))
        {
            $this->writeSubcategoryTable();
        }
    }
    /**
     * writeSubcategoryTable
     */
    private function writeSubcategoryTable()
    {
        // No subcategories to be added
        if (empty($this->vars['subcategoryIds']))
        {
            return;
        }
        // Find categories subcategories belong to in case category needs adding to resource_category.CategoryId
        foreach ($this->vars['subcategoryIds'] as $subcategoryId)
        {
            $this->db->formatConditions(['subcategoryId' => $subcategoryId]);
            $scIds[] = $this->db->selectFirstField('subcategory', 'subcategoryCategoryId');
            $this->db->insert(
                'resource_category',
                ['resourcecategoryResourceId', 'resourcecategorySubcategoryId'],
                [$this->vars['id'], $subcategoryId]
            );
        }
        // If category of which subcategory is a member is not in table row, add it
        if (isset($scIds))
        {
            foreach (array_unique($scIds) as $cId)
            {
                $this->db->formatConditions(['resourcecategoryResourceId' => $this->vars['id']]);
                $this->db->formatConditions(['resourcecategoryCategoryId' => $cId]);
                $resourcecategoryId = $this->db->selectFirstField('resource_category', 'resourcecategoryId');
                if (!$resourcecategoryId)
                {
                    $this->db->insert(
                        'resource_category',
                        ['resourcecategoryResourceId', 'resourcecategoryCategoryId'],
                        [$this->vars['id'], $cId]
                    );
                }
            }
        }
    }
    /**
     * write to resource_keyword
     */
    private function writeKeywordTable()
    {
        $keywordIds = $this->keyword->writeKeywordTable($this->vars);
        // remove old keywords from resource
        $this->db->formatConditions(['resourcekeywordResourceId' => $this->vars['id']]);
        $this->db->delete('resource_keyword');
        // add new keywords
        if (is_array($keywordIds))
        {
            foreach ($keywordIds as $kId)
            {
                $this->db->formatConditions(['resourcekeywordResourceId' => $this->vars['id']]);
                $this->db->formatConditions(['resourcekeywordKeywordId' => $kId]);
                $resourcekeywordId = $this->db->selectFirstField('resource_keyword', 'resourcekeywordId');
                if (!$resourcekeywordId)
                {
                    $this->db->insert(
                        'resource_keyword',
                        ['resourcekeywordResourceId', 'resourcekeywordKeywordId'],
                        [$this->vars['id'], $kId]
                    );
                }
            }
        }
        // remove keyword(s) if they no longer have resource attached to them
        $this->keyword->removeHanging();
    }
    /**
     * write to resource_user_tags
     */
    private function writeUserTagsTable()
    {
        if (!array_key_exists('userTags', $this->vars))
        {
            return;
        }
        $tagIds = [];
        if (\UTF8\mb_trim($this->vars['userTags']))
        {
            foreach (\UTF8\mb_explode(',', $this->vars['userTags']) as $inputTag)
            {
                $fields = $values = [];
                if (!$inputTag = trim($inputTag))
                {
                    continue;
                }
                if ($id = $this->userTagsObject->checkExists($inputTag))
                {
                    $tagIds[] = $id;
                }
                else
                { // write to user_tags table
                    $fields[] = 'usertagsTag';
                    $values[] = $inputTag;
                    $fields[] = 'usertagsUserId';
                    $values[] = $this->session->getVar("setup_UserId");
                    $this->db->insert('user_tags', $fields, $values);
                    $tagIds[] = $this->db->lastAutoID();
                }
            }
        }
        // Delete current user tags for this resource
        $this->db->formatConditions(['resourceusertagsResourceId' => $this->vars['id']]);
        $this->db->delete('resource_user_tags');
        if (empty($tagIds))
        {
            return;
        }
        foreach ($tagIds as $tId)
        {
            $fields = $values = [];
            $fields[] = 'resourceusertagsTagId';
            $values[] = $tId;
            $fields[] = "resourceusertagsResourceId";
            $values[] = $this->vars['id'];
            $this->db->insert('resource_user_tags', $fields, $values);
        }
    }
    /**
     * Navigate to last resource with message (success or error)
     *
     * @param mixed $message
     */
    private function navigate($message)
    {
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->vars['id'], $message);
        FACTORY_CLOSE::getInstance();
    }
}
