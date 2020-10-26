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
 * LISTSHOWHIDE class
 *
 * AJAX for LIST -- show or hide select boxes according to actions on other form elements
 */
class LISTSHOWHIDE
{
    private $categories;
    private $subcategories;
    private $category;
    private $keyword;
    private $keywords;
    private $types;
    private $creator;
    private $creators;
    private $publisher;
    private $publishers;
    private $collection;
    private $collections;
    private $session;
    private $messages;
    private $vars;
    private $radioButtons;
    private $type;
    private $previousSelect = [];
    private $typeObj;
    private $metadataTypeArray = ['quote', 'paraphrase', 'quoteComment', 'paraphraseComment', 'musing'];

    public function __construct()
    {
        $this->session = FACTORY_SESSION::getInstance();


        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->vars = GLOBALS::getVars();

        $this->typeObj = FACTORY_TYPE::getInstance();
    }
    /**
     * AJAX-based DIV content creator for resource types -- only used for select and search metadata
     */
    public function initTypes()
    {
        // if no ajaxReturn, quietly exit
        $div = \HTML\div('type', \HTML\td("&nbsp;")); // default
        $this->type = $this->vars['type'];
        if (!array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean all metadata types
            $this->checkMetadataExists();
            $this->types = $this->typeObj->grabAll(TRUE, $this->metadataTypeArray);
        } else {
            $this->types = $this->typeObj->grabAll(TRUE, \UTF8\mb_explode(',', $this->vars['ajaxReturn']));
        }
        $this->grabPreviouslySelected('ajaxReturn2');
        if (is_array($this->types)) {
            $div = \HTML\div('type', \HTML\td($this->makeFormMultiple($this->types, 'type', 'Type')));
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * AJAX-based DIV content creator for categories
     */
    public function initCategories()
    {
        $this->category = FACTORY_CATEGORY::getInstance();
        $this->type = $this->vars['type'];
        if (!array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean all resource types
            $this->categories = $this->category->grabAll(
                GLOBALS::getUserVar('BrowseBibliography'),
                TRUE,
                array_keys($this->typeObj->grabAll())
            );
        } else {
            $this->categories = $this->category->grabAll(
                GLOBALS::getUserVar('BrowseBibliography'),
                TRUE,
                \UTF8\mb_explode(',', $this->vars['ajaxReturn'])
            );
        }
        $this->grabPreviouslySelected('ajaxReturn2');
        $this->makeRadioButtons('CategoryMethod');
        if (is_array($this->categories)) {
            $categoryTable = \HTML\tableStart('generalTable');
            $categoryTable .= \HTML\trStart();
            $selectedCategories = [];
            if ($selected = $this->session->getVar($this->type . "_Category")) {
                $selected = \UTF8\mb_explode(',', $selected);
                foreach ($selected as $key) {
                    if (!array_key_exists($key, $this->categories)) {
                        continue;
                    }
                    $selectedCategories[$key] = $this->categories[$key];
                    unset($this->categories[$key]);
                }
            }
            $categoryTable .= \HTML\td($this->makeFormMultiple($this->categories, "availableCategory", "AvailableCategory", TRUE, TRUE), 'left width18percent');
            list($toRightImage, $toLeftImage) = $this->category->transferArrowsCat();
            $categoryTable .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'left width5percent');
            $categoryTable .= \HTML\td($this->makeFormMultiple($selectedCategories, "category", "Category", TRUE), 'left width18percent');
            $categoryTable .= \HTML\trEnd();
            $categoryTable .= \HTML\tableEnd();
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$categoryTable"]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * AJAX-based DIV content creator for languages
     */
    public function initLanguages()
    {
        // if no ajaxReturn, quietly exit
        $div = \HTML\td(\HTML\div('language', "&nbsp;")); // default
        $this->type = $this->vars['type'];
        $db = FACTORY_DB::getInstance();
        if (array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean specific resource types
            $db->leftJoin('resource', 'resourceId', 'resourcelanguageResourceId');
            $db->formatConditionsOneField(\UTF8\mb_explode(',', $this->vars['ajaxReturn']), 'resourceType');
        }
        $languages = [];
        $db->orderBy('languageLanguage');
        $db->leftJoin('language', 'languageId', 'resourcelanguageLanguageId');
        $resultset = $db->select('resource_language', ['resourcelanguageLanguageId', 'languageLanguage'], TRUE);
        while ($row = $db->fetchRow($resultset)) {
            $languages[$row['resourcelanguageLanguageId']] = $row['languageLanguage'];
        }
        $this->makeRadioButtons('LanguageMethod', FALSE);
        if (!empty($languages)) {
            $div = \HTML\td(\HTML\div('language', $this->makeFormMultiple($languages, 'language', 'Language')));
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * AJAX-based DIV content creator for keywords
     */
    public function initKeywords()
    {
        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->type = $this->vars['type'];
        if (array_key_exists('metadata', $this->vars)) {
            if (!array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean all metadata types
                $this->checkMetadataExists();
                $this->keywords = $this->keyword->grabAll(TRUE, $this->metadataTypeArray);
            } else {
                $this->keywords = $this->keyword->grabAll(TRUE, \UTF8\mb_explode(',', $this->vars['ajaxReturn']));
            }
        } else {
            if (!array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean all resource types
                $this->keywords = $this->keyword->grabAll(
                    GLOBALS::getUserVar('BrowseBibliography'),
                    'resource',
                    array_keys($this->typeObj->grabAll())
                );
            } else {
                $this->keywords = $this->keyword->grabAll(
                    GLOBALS::getUserVar('BrowseBibliography'),
                    'resource',
                    \UTF8\mb_explode(',', $this->vars['ajaxReturn'])
                );
            }
        }
        $this->grabPreviouslySelected('ajaxReturn2');
        $this->makeRadioButtons('KeywordMethod');
        if (is_array($this->keywords)) {
            $keywordTable = \HTML\tableStart('generalTable');
            $keywordTable .= \HTML\trStart();
            $selectedKeywords = [];
            if ($selected = $this->session->getVar($this->type . "Keyword")) {
                $selected = \UTF8\mb_explode(',', $selected);
                foreach ($selected as $key) {
                    if (array_key_exists($key, $this->keywords)) {
                        $selectedKeywords[$key] = $this->keywords[$key];
                    }
                }
            }
            $keywordTable .= \HTML\td(
                $this->makeFormMultiple($this->keywords, "availableKeyword", "AvailableKeyword", TRUE, TRUE),
                'left width20percent'
            );
            list($toRightImage, $toLeftImage) = $this->keyword->transferArrows();
            $keywordTable .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'left width5pencent');
            $keywordTable .= \HTML\td($this->makeFormMultiple($selectedKeywords, "keyword", 'Keyword', TRUE), 'left width20percent');
        } else {
            $keywordTable = \HTML\tableStart();
            $keywordTable .= \HTML\trStart();
            $keywordTable .= \HTML\td("&nbsp;"); // default
        }
        $keywordTable .= \HTML\trEnd();
        $keywordTable .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $keywordTable]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * AJAX-based DIV content creator for subcategories
     */
    public function initSubcategories()
    {
        $this->category = FACTORY_CATEGORY::getInstance();
        $this->type = $this->vars['type'];
        if (!array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean all resource types
            $categories = $this->category->grabAll(
                GLOBALS::getUserVar('BrowseBibliography'),
                TRUE,
                array_keys($this->typeObj->grabAll()),
                FALSE
            );
        } else {
            $categories = $this->category->grabAll(
                GLOBALS::getUserVar('BrowseBibliography'),
                TRUE,
                \UTF8\mb_explode(',', $this->vars['ajaxReturn']),
                FALSE
            );
        }
        $this->subcategories = $this->category->grabSubAll(
            TRUE,
            GLOBALS::getUserVar('BrowseBibliography'),
            array_keys($categories),
            TRUE
        );
        if ($this->subcategories) {
            $this->grabPreviouslySelected('ajaxReturn2');
            $this->makeRadioButtons('SubcategoryMethod');
            $subcatTable = \HTML\tableStart('generalTable');
            $subcatTable .= \HTML\trStart();
            $selectedSubcats = [];
            if ($selected = $this->session->getVar("search_Subcategory")) {
                $selected = \UTF8\mb_explode(',', $selected);
                foreach ($selected as $key) {
                    if (!array_key_exists($key, $this->subcategories)) {
                        continue;
                    }
                    $selectedSubcats[$key] = $this->subcategories[$key];
                    unset($this->subcategories[$key]);
                }
            }
            $subcatTable .= \HTML\td(
                $this->makeFormMultiple($this->subcategories, "availableSubcategory", "AvailableSubcategory", TRUE, TRUE),
                'left width18percent'
            );
            list($toRightImage, $toLeftImage) = $this->category->transferArrowsSubcat();
            $subcatTable .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'left width5percent');
            $subcatTable .= \HTML\td($this->makeFormMultiple($selectedSubcats, "subcategory", "Subcategory", TRUE), 'left width18percent');
        } else {
            $subcatTable = \HTML\tableStart();
            $subcatTable .= \HTML\trStart();
            $subcatTable .= \HTML\td("&nbsp;");
        }
        $subcatTable .= \HTML\trEnd();
        $subcatTable .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $subcatTable]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * DIV content creator for subcategories because subcategories are dependent upon categories
     *
     * @param mixed $matchCategoryIds
     *
     * @return array
     */
    public function initSubcategoriesDependent($matchCategoryIds)
    {
        $div = \HTML\div('subcategory', \HTML\td("&nbsp;")); // default
        if (empty($matchCategoryIds)) {
            return ['subcategory', $div];
        }
        $this->type = $this->vars['type'];
        $this->subcategories = $this->category->grabSubAll(
            TRUE,
            GLOBALS::getUserVar('BrowseBibliography'),
            $matchCategoryIds,
            TRUE
        );
        if (is_array($this->subcategories)) {
            $div = \HTML\div(
                'subcategory',
                \HTML\td($this->makeFormMultiple($this->subcategories, 'subcategory', 'Subcategory'))
            );
        }

        return ['subcategory', $div];
    }
    /**
     * AJAX-based DIV content creator for creators
     */
    public function initCreators()
    {
        $this->creator = FACTORY_CREATOR::getInstance();
        $this->type = $this->vars['type'];
        if (array_key_exists('metadata', $this->vars)) {
            if (!array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean all metadata types
                $this->checkMetadataExists();
                $this->creators = $this->creator->grabAll(TRUE, FALSE, $this->metadataTypeArray);
            } else {
                $this->creators = $this->creator->grabAll(TRUE, FALSE, \UTF8\mb_explode(',', $this->vars['ajaxReturn']));
            }
        } else {
            if (!array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean all resource types
                $this->creators = $this->creator->grabAll(
                    GLOBALS::getUserVar('BrowseBibliography'),
                    array_keys($this->typeObj->grabAll())
                );
            } else {
                $this->creators = $this->creator->grabAll(
                    GLOBALS::getUserVar('BrowseBibliography'),
                    \UTF8\mb_explode(',', $this->vars['ajaxReturn']),
                    FALSE,
                    TRUE
                );
            }
        }
        $this->grabPreviouslySelected('ajaxReturn2');
        $this->makeRadioButtons('CreatorMethod');
        if (is_array($this->creators)) {
            $creatorTable = \HTML\tableStart('generalTable');
            $creatorTable .= \HTML\trStart();
            $selectedCreators = [];
            if ($selected = $this->session->getVar($this->type . "_Creator")) {
                $selected = \UTF8\mb_explode(',', $selected);
                foreach ($selected as $key) {
                    if (!array_key_exists($key, $this->creators)) {
                        continue;
                    }
                    $selectedCreators[$key] = $this->creators[$key];
                    unset($this->creators[$key]);
                }
            }
            $creatorTable .= \HTML\td($this->makeFormMultiple($this->creators, "availableCreator", "AvailableCreator", TRUE, TRUE), 'left width20percent');
            list($toRightImage, $toLeftImage) = $this->creator->transferArrows();
            $creatorTable .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'left width5percent');
            $creatorTable .= \HTML\td($this->makeFormMultiple($selectedCreators, "creator", "Creator", TRUE), 'left width20percent');
            $creatorTable .= \HTML\trEnd();
            $creatorTable .= \HTML\tableEnd();
        } else {
            $creatorTable = \HTML\tableStart();
            $creatorTable .= \HTML\trStart();
            $creatorTable .= \HTML\td("&nbsp;"); // default
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$creatorTable"]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * AJAX-based DIV content creator for publishers
     */
    public function initPublishers()
    {
        $this->publisher = FACTORY_PUBLISHER::getInstance();
        $this->type = $this->vars['type'];
        if (!array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean all resource types
            $this->publishers = $this->publisher->grabAll(
                FALSE,
                GLOBALS::getUserVar('BrowseBibliography'),
                array_keys($this->typeObj->grabAll())
            );
        } else {
            $this->publishers = $this->publisher->grabAll(
                FALSE,
                GLOBALS::getUserVar('BrowseBibliography'),
                \UTF8\mb_explode(',', $this->vars['ajaxReturn'])
            );
        }
        $this->grabPreviouslySelected('ajaxReturn2');
        if (is_array($this->publishers)) {
            $publisherTable = \HTML\tableStart('generalTable');
            $publisherTable .= \HTML\trStart();
            $selectedPublishers = [];
            if ($selected = $this->session->getVar($this->type . "_Publisher")) {
                $selected = \UTF8\mb_explode(',', $selected);
                foreach ($selected as $key) {
                    if (!array_key_exists($key, $this->publishers)) {
                        continue;
                    }
                    $selectedPublishers[$key] = $this->publishers[$key];
                    unset($this->publishers[$key]);
                }
            }
            $publisherTable .= \HTML\td($this->makeFormMultiple($this->publishers, "availablePublisher", "AvailablePublisher", TRUE, TRUE), 'left width20percent');
            list($toRightImage, $toLeftImage) = $this->publisher->transferArrows();
            $publisherTable .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'left width5percent');
            $publisherTable .= \HTML\td($this->makeFormMultiple($selectedPublishers, "publisher", "Publisher", TRUE), 'left width20percent');
            $publisherTable .= \HTML\trEnd();
            $publisherTable .= \HTML\tableEnd();
        } else {
            $publisherTable = \HTML\tableStart();
            $publisherTable .= \HTML\trStart();
            $publisherTable .= \HTML\td("&nbsp;"); // default
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$publisherTable"]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * AJAX-based DIV content creator for collections
     */
    public function initCollections()
    {
        $this->collection = FACTORY_COLLECTION::getInstance();
        $this->type = $this->vars['type'];
        if (!array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean all resource types
            $this->collections = $this->collection->grabAll(
                FALSE,
                GLOBALS::getUserVar('BrowseBibliography'),
                array_keys($this->typeObj->grabAll())
            );
        } else {
            $this->collections = $this->collection->grabAll(
                FALSE,
                GLOBALS::getUserVar('BrowseBibliography'),
                \UTF8\mb_explode(',', $this->vars['ajaxReturn'])
            );
        }
        $this->grabPreviouslySelected('ajaxReturn2');
        if (is_array($this->collections)) {
            $collectionTable = \HTML\tableStart('generalTable');
            $collectionTable .= \HTML\trStart();
            $selectedCollections = [];
            if ($selected = $this->session->getVar($this->type . "_Collection")) {
                $selected = \UTF8\mb_explode(',', $selected);
                foreach ($selected as $key) {
                    if (!array_key_exists($key, $this->collections)) {
                        continue;
                    }
                    $selectedCollections[$key] = $this->collections[$key];
                    unset($this->collections[$key]);
                }
            }
            $collectionTable .= \HTML\td($this->makeFormMultiple($this->collections, "availableCollection", "AvailableCollection", TRUE, TRUE), 'left width30percent');
            list($toRightImage, $toLeftImage) = $this->collection->transferArrows();
            $collectionTable .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), 'left width5percent');
            $collectionTable .= \HTML\td($this->makeFormMultiple($selectedCollections, "collection", "Collection", TRUE), 'left width30percent');
            $collectionTable .= \HTML\trEnd();
            $collectionTable .= \HTML\tableEnd();
        } else {
            $collectionTable = \HTML\tableStart();
            $collectionTable .= \HTML\trStart();
            $collectionTable .= \HTML\td("&nbsp;"); // default
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$collectionTable"]));
        FACTORY_CLOSERAW::getInstance();


        // if no ajaxReturn, quietly exit
        $div = \HTML\div('collection', \HTML\td("&nbsp;")); // default
        $this->type = $this->vars['type'];
        if (!array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean all resource types
            $this->collections = $this->collection->grabAll(
                FALSE,
                GLOBALS::getUserVar('BrowseBibliography'),
                array_keys($this->typeObj->grabAll())
            );
        } else {
            $this->collections = $this->collection->grabAll(
                FALSE,
                GLOBALS::getUserVar('BrowseBibliography'),
                \UTF8\mb_explode(',', $this->vars['ajaxReturn'])
            );
        }
        $this->grabPreviouslySelected('ajaxReturn2');
        if (is_array($this->collections)) {
            $div = \HTML\div('collection', \HTML\td($this->makeFormMultiple(
                $this->collections,
                'collection',
                'Collection'
            )));
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * AJAX-based DIV content creator for user tags
     */
    public function initUsertags()
    {
        $this->userTag = FACTORY_USERTAGS::getInstance();
        $this->type = $this->vars['type'];
        if (!array_key_exists('ajaxReturn', $this->vars)) { // i.e. we mean all resource types
            $this->userTags = $this->userTag->grabAll(
                GLOBALS::getUserVar('BrowseBibliography'),
                array_keys($this->typeObj->grabAll()),
                TRUE
            );
        } else {
            $this->userTags = $this->userTag->grabAll(
                GLOBALS::getUserVar('BrowseBibliography'),
                \UTF8\mb_explode(',', $this->vars['ajaxReturn']),
                TRUE
            );
        }
        $this->grabPreviouslySelected('ajaxReturn2');
        $this->makeRadioButtons('UserTagMethod');
        if (!empty($this->userTags)) {
            $utTable = \HTML\tableStart('generalTable');
            $utTable .= \HTML\trStart();
            $selectedUTs = [];
            if ($selected = $this->session->getVar($this->type . "_UserTag")) {
                $selected = \UTF8\mb_explode(',', $selected);
                foreach ($selected as $key) {
                    if (!array_key_exists($key, $this->userTags)) {
                        continue;
                    }
                    $selectedUTs[$key] = $this->userTags[$key];
                    unset($this->userTags[$key]);
                }
            }
            $utTable .= \HTML\td(
                $this->makeFormMultiple($this->userTags, "availableUserTag", "AvailableUserTag", TRUE, TRUE),
                'left width20percent'
            );
            list($toRightImage, $toLeftImage) = $this->userTag->transferArrows();
            $utTable .= \HTML\td(\HTML\p($toRightImage) . \HTML\p($toLeftImage), FALSE, 'left width5percent');
            $utTable .= \HTML\td($this->makeFormMultiple($selectedUTs, "userTag", 'UserTag', TRUE), 'left width20percent');
        } else {
            $utTable = \HTML\tableStart();
            $utTable .= \HTML\trStart();
            $utTable .= \HTML\td("&nbsp;"); // default
        }
        $utTable .= \HTML\trEnd();
        $utTable .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $utTable]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * make a self-contained form with a multiple select box
     *
     * @param array $array
     * @param string $message
     * @param string $box
     * @param bool $noIgnore
     * @param bool $noRadio
     *
     * @return string
     */
    private function makeFormMultiple($array, $message, $box, $noIgnore = FALSE, $noRadio = FALSE)
    {
        // add 0 => IGNORE to $array
        if (!$noIgnore) {
            $temp[0] = $this->messages->text("misc", "ignore");
            $temp += $array;
        } else {
            $temp = $array;
        }
        $this->previousSelect = array_intersect($this->previousSelect, array_keys($temp));
        if ($this->type == 'search') {
            if (!empty($this->previousSelect)) {
                $pString = \FORM\selectedBoxValueMultiple(
                    $this->messages->text("search", $message),
                    "search_" . $box,
                    $temp,
                    $this->previousSelect,
                    "5"
                );
            } else {
                $pString = \FORM\selectFBoxValueMultiple($this->messages->text("search", $message), "search_" . $box, $temp, "5");
            }
        } else {
            if (!empty($this->previousSelect)) {
                $pString = \FORM\selectedBoxValueMultiple(
                    $this->messages->text("select", $message),
                    "select_" . $box,
                    $temp,
                    $this->previousSelect,
                    "5"
                );
            } else {
                $pString = \FORM\selectFBoxValueMultiple($this->messages->text("select", $message), "select_" . $box, $temp, "5");
            }
        }
        $pString .= BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint');
        if ($this->radioButtons && !$noRadio) {
            $pString .= BR . $this->radioButtons . BR;
        }

        return $pString;
    }
    /**
     * Create radio buttons for AND and OR
     *
     * @param string $method
     * @param bool $andDefault
     */
    private function makeRadioButtons($method, $andDefault = TRUE)
    {
        $type = $this->type . '_' . $method;
        if ($andDefault) {
            $pString = \HTML\span(\FORM\radioButton(FALSE, $type, 'OR') . " OR", "small") . BR;
            $pString .= \HTML\span(\FORM\radioButton(FALSE, $type, 'AND', TRUE) . " AND", "small");
        } else {
            $pString = \HTML\span(\FORM\radioButton(FALSE, $type, 'OR', TRUE) . " OR", "small") . BR;
            $pString .= \HTML\span(\FORM\radioButton(FALSE, $type, 'AND') . " AND", "small");
        }
        $this->radioButtons = $pString;
    }
    /**
     * Store previously selected options
     *
     * @param mixed $qsElement
     */
    private function grabPreviouslySelected($qsElement)
    {
        if (array_key_exists($qsElement, $this->vars)) {
            $this->previousSelect = \UTF8\mb_explode(',', $this->vars[$qsElement]);
            if (($index = array_search(0, $this->previousSelect)) !== FALSE) {
                unset($this->previousSelect[$index]); // remove 'IGNORE' selected
            }
        }
    }
    /**
     * Check we have metadata
     *
     * @return boot
     */
    private function checkMetadataExists()
    {
        $db = FACTORY_DB::getInstance();
        $commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $commonBib->userBibCondition('resourcemetadataResourceId');
        $db->formatConditions(['resourcemetadataType' => 'q']);
        $recordset = $db->select('resource_metadata', 'resourcemetadataId');
        if (!$db->numRows($recordset)) {
            unset($this->metadataTypeArray['quote']);
        }
        $commonBib->userBibCondition('resourcemetadataResourceId');
        $db->formatConditions(['resourcemetadataType' => 'p']);
        $recordset = $db->select('resource_metadata', 'resourcemetadataId');
        if (!$db->numRows($recordset)) {
            unset($this->metadataTypeArray['paraphrase']);
        }
        $commonBib->userBibCondition('resourcemetadataResourceId');
        $db->formatConditions(['resourcemetadataType' => 'm']);
        $recordset = $db->select('resource_metadata', 'resourcemetadataId');
        if (!$db->numRows($recordset)) {
            unset($this->metadataTypeArray['musing']);
        }
        $commonBib->userBibCondition('resourcemetadataResourceId');
        $db->formatConditions(['resourcemetadataType' => 'qc']);
        $recordset = $db->select('resource_metadata', 'resourcemetadataId');
        if (!$db->numRows($recordset)) {
            unset($this->metadataTypeArray['quoteComment']);
        }
        $commonBib->userBibCondition('resourcemetadataResourceId');
        $db->formatConditions(['resourcemetadataType' => 'pc']);
        $recordset = $db->select('resource_metadata', 'resourcemetadataId');
        if (!$db->numRows($recordset)) {
            unset($this->metadataTypeArray['paraphraseComment']);
        }

        return TRUE;
    }
}
