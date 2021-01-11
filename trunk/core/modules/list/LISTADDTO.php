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
 * LISTADDTO class
 *
 * Add selected resources in list to user bibliographies, categories, keywords etc. (also handles remove from . . .)
 */
class LISTADDTO
{
    private $db;
    private $vars;
    private $messages;
    private $errors;
    private $session;
    private $commonBib;
    private $user;
    private $badInput;
    private $navigate;
    private $catForm;
    private $browserTabID = FALSE;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "resource", "RESOURCECATEGORYEDIT.php"]));
        $this->catForm = new RESOURCECATEGORYEDIT();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->navigate = FACTORY_NAVIGATE::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
    }
    /**
     * init
     */
    public function init()
    {
        if (!array_key_exists('resourceSelectedTo', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        $this->session->setVar("resourceSelectedTo", $this->vars['resourceSelectedTo']);
        if ($this->browserTabID) {
	    	\TEMPSTORAGE\store($this->db, $this->browserTabID, ['resourceSelectedTo' => $this->vars['resourceSelectedTo']]);
	    }
        if ($this->vars['resourceSelectedTo'] == 1)
        {	// add to categories
            return $this->organizeInit();
        }
        elseif ($this->vars['resourceSelectedTo'] == 3)
        {	// remove resources from user bibliography
            return $this->deleteFromBib();
        }
        elseif ($this->vars['resourceSelectedTo'] == 4)
        {	// delete resources from WIKINDX
            return $this->deleteFromWikindx();
        }
        elseif ($this->vars['resourceSelectedTo'] == 5)
        {	// approve quarantined resources
            return $this->unquarantine();
        }
        elseif ($this->vars['resourceSelectedTo'] == 7)
        {	// add to basket
            return $this->addToBasket();
        }
        elseif ($this->vars['resourceSelectedTo'] == 8)
        {	// remove from basket
            return $this->RemoveFromBasket();
        }
        elseif ($this->vars['resourceSelectedTo'] == 9)
        {	// displayCOinS
            return $this->displayCoins();
        }
        else
        {	// 0 -> add to user bibs
            return $this->addToUserBibInit();
        }
    }
    /**
     * remove slashes from array
     *
     * @param string $element
     *
     * @return string
     */
    public function removeSlashes($element)
    {
        return stripslashes($element);
    }
    /**
     * Update resources with new or removed selections of categories, subcategories, languages, keywords and usertags
     */
    public function organize()
    {
        $this->catForm->storeData();
        if (!array_key_exists("displayCategory", $this->vars) && !array_key_exists("displaySubcategory", $this->vars) &&
            !array_key_exists("displayLanguage", $this->vars) && !array_key_exists("displayKeyword", $this->vars) &&
            !array_key_exists("displayUsertag", $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, ['organizeInit', $this->vars['uuid']]);
        }
        if (array_key_exists("displayLanguage", $this->vars) &&
            (!array_key_exists("languageIds", $this->vars) || empty($this->vars["languageIds"])))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, ['organizeInit', $this->vars['uuid']]);

            return;
        }
        $resourceIds = $this->getHiddenIds();
        // Categories
        if (array_key_exists("displayCategory", $this->vars))
        {
            $categoryIds = array_key_exists('categoryIds', $this->vars) ? $this->vars['categoryIds'] : [];
            if (array_key_exists("replaceExisting", $this->vars) && array_key_exists("categoryRadio", $this->vars) &&
            ($this->vars['categoryRadio'] == 'add'))
            {
                // remove all old categories and subcategories from resource if adding categories
                $this->db->formatConditionsOneField($resourceIds, 'resourcecategoryResourceId');
                $this->db->delete('resource_category');
            }
            // remove categories
            if (array_key_exists("categoryRadio", $this->vars) && $this->vars['categoryRadio'] == 'remove')
            {
                foreach ($categoryIds as $cId)
                {
                    foreach ($resourceIds as $rId)
                    {
                        $this->db->formatConditions(['resourcecategoryResourceId' => $rId]);
                        $this->db->formatConditions(['resourcecategoryCategoryId' => $cId]);
                        $this->db->delete('resource_category');
                    }
                }
            }
            // add new categories
            else
            {
                $values = [];
                foreach ($categoryIds as $cId)
                {
                    $foundIds = [];
                    $this->db->formatConditionsOneField($resourceIds, 'resourcecategoryResourceId');
                    $this->db->formatConditions(['resourcecategoryCategoryId' => $cId]);
                    $resultSet = $this->db->select('resource_category', 'resourcecategoryResourceId');
                    while ($row = $this->db->fetchRow($resultSet))
                    {
                        $foundIds[] = $row['resourcecategoryResourceId'];
                    }
                    foreach (array_diff($resourceIds, $foundIds) as $rId)
                    {
                        $values[] = [$rId, $cId];
                    }
                }
                if (!empty($values))
                {
                    $this->db->insert('resource_category', ['resourcecategoryResourceId', 'resourcecategoryCategoryId'], $values);
                }
            }
        }
        // Subcategories
        if (array_key_exists("displaySubcategory", $this->vars))
        {
            $subcategoryIds = array_key_exists('subcategoryIds', $this->vars) ? $this->vars['subcategoryIds'] : [];
            if (array_key_exists("replaceExisting", $this->vars) && array_key_exists("subcategoryRadio", $this->vars) &&
            ($this->vars['subcategoryRadio'] == 'add'))
            {
                // remove all old subcategories from resource if adding subcategories
                $this->db->formatConditionsOneField($resourceIds, 'resourcecategoryResourceId');
                $this->db->formatConditionsOneField($subcategoryIds, 'resourcecategorySubcategoryId');
                $this->db->delete('resource_category');
            }
            // remove subcategories
            if (array_key_exists("subcategoryRadio", $this->vars) && $this->vars['subcategoryRadio'] == 'remove')
            {
                foreach ($subcategoryIds as $cId)
                {
                    foreach ($resourceIds as $rId)
                    {
                        $this->db->formatConditions(['resourcecategoryResourceId' => $rId]);
                        $this->db->formatConditions(['resourcecategorySubcategoryId' => $cId]);
                        $this->db->delete('resource_category');
                    }
                }
            }
            // add subcategories
            else
            {
                // Find categories subcategories belong to in case category needs adding to resource_category.CategoryId
                foreach ($subcategoryIds as $subcategoryId)
                {
                    $this->db->formatConditions(['subcategoryId' => $subcategoryId]);
                    $scIds[] = $this->db->selectFirstField('subcategory', 'subcategoryCategoryId');
                }
            }
            // If category of which subcategory is a member is not in table row, add it
            if (isset($scIds))
            {
                $values = [];
                foreach ($subcategoryIds as $subcategoryId)
                {
                    foreach (array_unique($scIds) as $cId)
                    {
                        $foundIds = [];
                        $this->db->formatConditionsOneField($resourceIds, 'resourcecategoryResourceId');
                        $this->db->formatConditions(['resourcecategoryCategoryId' => $cId]);
                        $this->db->formatConditions(['resourcecategorySubcategoryId' => ' IS NOT NULL']);
                        $resultSet = $this->db->select('resource_category', 'resourcecategoryResourceId');
                        while ($row = $this->db->fetchRow($resultSet))
                        {
                            $foundIds[] = $row['resourcecategoryResourceId'];
                        }
                        foreach (array_diff($resourceIds, $foundIds) as $rId)
                        {
                            $values[] = [$rId, $cId, $subcategoryId];
                        }
                    }
                }
                if (!empty($values))
                {
                    $this->db->insert('resource_category', ['resourcecategoryResourceId', 'resourcecategoryCategoryId',
                        'resourcecategorySubcategoryId', ], $values);
                }
            }
        }
        // Languages
        if (array_key_exists("displayLanguage", $this->vars))
        {
            $languageIds = array_key_exists('languageIds', $this->vars) ? $this->vars['languageIds'] : [];
            if (array_key_exists("replaceExisting", $this->vars) && array_key_exists("languageRadio", $this->vars) &&
            ($this->vars['languageRadio'] == 'add'))
            {
                // remove all old languages from resource when adding
                $this->db->formatConditionsOneField($resourceIds, 'resourcelanguageResourceId');
                $this->db->delete('resource_language');
            }
            // remove languages
            if (array_key_exists("languageRadio", $this->vars) && $this->vars['languageRadio'] == 'remove')
            {
                foreach ($languageIds as $lId)
                {
                    if (!$lId)
                    { // IGNORE
                        continue;
                    }
                    foreach ($resourceIds as $rId)
                    {
                        $this->db->formatConditions(['resourcelanguageResourceId' => $rId]);
                        $this->db->formatConditions(['resourcelanguageLanguageId' => $lId]);
                        $this->db->delete('resource_language');
                    }
                }
            }
            // add new languages
            else
            {
                $values = [];
                foreach ($languageIds as $lId)
                {
                    if (!$lId)
                    {
                        continue;
                    }
                    $foundIds = [];
                    $this->db->formatConditionsOneField($resourceIds, 'resourcelanguageResourceId');
                    $this->db->formatConditions(['resourcelanguageLanguageId' => $lId]);
                    $resultSet = $this->db->select('resource_language', 'resourcelanguageResourceId');
                    while ($row = $this->db->fetchRow($resultSet))
                    {
                        $foundIds[] = $row['resourcelanguageResourceId'];
                    }
                    foreach (array_diff($resourceIds, $foundIds) as $rId)
                    {
                        $values[] = [$rId, $lId];
                    }
                }
                if (!empty($values))
                {
                    $this->db->insert('resource_language', ['resourcelanguageResourceId', 'resourcelanguageLanguageId'], $values);
                }
            }
        }
        // Keywords
        if (array_key_exists("displayKeyword", $this->vars))
        {
            $keyword = FACTORY_KEYWORD::getInstance();
            $keywordIds = [];
            if (array_key_exists('keywords', $this->vars) && \UTF8\mb_trim($this->vars['keywords']))
            {
                $keywordTexts = \UTF8\mb_explode(',', \UTF8\mb_trim($this->vars['keywords']));
            }
            else
            {
                $keywordTexts = [];
            }
            foreach ($keywordTexts as $text)
            {
                $keywordText = trim($text);
                if ($keywordText)
                {
                    if ($id = $keyword->checkExists($keywordText))
                    {
                        if (array_search($id, $keywordIds) === FALSE)
                        {
                            $keywordIds[] = $id;
                        }
                    }
                    else
                    {
                        // given keyword doesn't exist so now write to db
                        $fields = $values = [];
                        $fields[] = "keywordKeyword";
                        $values[] = $keywordText;
                        $this->db->insert('keyword', $fields, $values);
                        $keywordIds[] = $this->db->lastAutoId();
                    }
                }
            }
            if (array_key_exists("replaceExisting", $this->vars) && array_key_exists("keywordRadio", $this->vars) &&
            ($this->vars['keywordRadio'] == 'add'))
            {
                // remove old keywords from all selected resources when adding
                $this->db->formatConditionsOneField($resourceIds, 'resourcekeywordResourceId');
                $this->db->delete('resource_keyword');
            }
            // remove keywords
            if (array_key_exists("keywordRadio", $this->vars) && $this->vars['keywordRadio'] == 'remove')
            {
                foreach ($keywordIds as $kId)
                {
                    foreach ($resourceIds as $rId)
                    {
                        $this->db->formatConditions(['resourcekeywordResourceId' => $rId]);
                        $this->db->formatConditions(['resourcekeywordKeywordId' => $kId]);
                        $this->db->delete('resource_keyword');
                    }
                }
            }
            // add new keywords
            else
            {
                $values = [];
                foreach ($keywordIds as $kId)
                {
                    $foundIds = [];
                    $this->db->formatConditionsOneField($resourceIds, 'resourcekeywordResourceId');
                    $this->db->formatConditions(['resourcekeywordKeywordId' => $kId]);
                    $resultSet = $this->db->select('resource_keyword', 'resourcekeywordResourceId');
                    while ($row = $this->db->fetchRow($resultSet))
                    {
                        $foundIds[] = $row['resourcekeywordResourceId'];
                    }
                    foreach (array_diff($resourceIds, $foundIds) as $rId)
                    {
                        $values[] = [$rId, $kId];
                    }
                }
                if (!empty($values))
                {
                    $this->db->insert('resource_keyword', ['resourcekeywordResourceId', 'resourcekeywordKeywordId'], $values);
                }
            }
            // remove keyword(s) if they no longer have resources attached to them
            $keyword->removeHanging();
        }
        // remove cache files for keywords
        $this->db->deleteCache('cacheResourceKeywords');
        $this->db->deleteCache('cacheMetadataKeywords');
        $this->db->deleteCache('cacheQuoteKeywords');
        $this->db->deleteCache('cacheParaphraseKeywords');
        $this->db->deleteCache('cacheMusingKeywords');
        $this->db->deleteCache('cacheKeywords');
        // User tags
        if (array_key_exists("displayUsertag", $this->vars))
        {
            $usertag = FACTORY_USERTAGS::getInstance();
            $usertagIds = [];
            if (array_key_exists('userTags', $this->vars) && \UTF8\mb_trim($this->vars['userTags']))
            {
                $usertagTexts = \UTF8\mb_explode(',', \UTF8\mb_trim($this->vars['userTags']));
            }
            else
            {
                $usertagTexts = [];
            }
            foreach ($usertagTexts as $text)
            {
                $usertagText = trim($text);
                if ($usertagText)
                {
                    if ($id = $usertag->checkExists($usertagText))
                    {
                        if (array_search($id, $usertagIds) === FALSE)
                        {
                            $usertagIds[] = $id;
                        }
                    }
                    else
                    {
                        // given usertag doesn't exist so now write to db
                        $fields = $values = [];
                        $fields[] = 'usertagsTag';
                        $values[] = $usertagText;
                        $fields[] = 'usertagsUserId';
                        $values[] = $this->session->getVar("setup_UserId");
                        $this->db->insert('user_tags', $fields, $values);
                        $usertagIds[] = $this->db->lastAutoId();
                    }
                }
            }
            if (array_key_exists("replaceExisting", $this->vars) && array_key_exists("usertagRadio", $this->vars) &&
            ($this->vars['usertagRadio'] == 'add'))
            {
                // remove old usertags from all selected resources
                $this->db->formatConditionsOneField($resourceIds, 'resourceusertagsResourceId');
                $this->db->delete('resource_user_tags');
            }
            // remove usertags
            if (array_key_exists("usertagRadio", $this->vars) && $this->vars['usertagRadio'] == 'remove')
            {
                foreach ($usertagIds as $uId)
                {
                    foreach ($resourceIds as $rId)
                    {
                        $this->db->formatConditions(['resourceusertagsResourceId' => $rId]);
                        $this->db->formatConditions(['resourceusertagsTagId' => $uId]);
                        $this->db->delete('resource_user_tags');
                    }
                }
            }
            // add new usertags
            else
            {
                $values = [];
                foreach ($usertagIds as $uId)
                {
                    $foundIds = [];
                    $this->db->formatConditionsOneField($resourceIds, 'resourceusertagsResourceId');
                    $this->db->formatConditions(['resourceusertagsTagId' => $uId]);
                    $resultSet = $this->db->select('resource_user_tags', 'resourceusertagsResourceId');
                    while ($row = $this->db->fetchRow($resultSet))
                    {
                        $foundIds[] = $row['resourceusertagsResourceId'];
                    }
                    foreach (array_diff($resourceIds, $foundIds) as $rId)
                    {
                        $values[] = [$rId, $uId];
                    }
                }
                if (!empty($values))
                {
                    $this->db->insert('resource_user_tags', ['resourceusertagsResourceId', 'resourceusertagsTagId'], $values);
                }
            }
        }
        // Check that each of these resources still belongs to at least one category, if not, set to 'General'
        $values = [];
        $foundIds = [];
        $this->db->formatConditionsOneField($resourceIds, 'resourcecategoryResourceId');
        $this->db->formatConditions(['resourcecategoryCategoryId' => ' IS NOT NULL']);
        $resultSet = $this->db->select('resource_category', 'resourcecategoryResourceId');
        while ($row = $this->db->fetchRow($resultSet))
        {
            $foundIds[] = $row['resourcecategoryResourceId'];
        }
        foreach (array_diff($resourceIds, $foundIds) as $rId)
        {
            $values[] = [$rId, 1];
        }
        if (!empty($values))
        {
            $this->db->insert('resource_category', ['resourcecategoryResourceId', 'resourcecategoryCategoryId'], $values);
        }
        $success = FACTORY_SUCCESS::getInstance();
        $this->navigate->listView($success->text("organized"));
        FACTORY_CLOSE::getInstance(); // die
    }
    /**
     * Add resources to a bibliography
     */
    public function addResourceToBib()
    {
        if (!array_key_exists('bibId', $this->vars) || ($this->vars['bibId'] < 1))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, ['addToUserBibInit', $this->vars['uuid']]);
        }
        $ids = $this->getHiddenIds();
        // valid user?
        $userBibs = $this->commonBib->getUserBibs();
        $groupBibs = $this->commonBib->getGroupBibs();
        if (!array_key_exists($this->vars['bibId'], $userBibs) && !array_key_exists($this->vars['bibId'], $groupBibs))
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this->navigate, 'listView');
        }
        foreach ($ids as $id)
        {
            $this->db->formatConditions(['userbibliographyresourceResourceId' => $id]);
            $this->db->formatConditions(['userbibliographyresourceBibliographyId' => $this->vars['bibId']]);
            if (!$this->db->numRows($this->db->select('user_bibliography_resource', 'userbibliographyresourceId')))
            {
                $this->db->insert(
                    'user_bibliography_resource',
                    ['userbibliographyresourceBibliographyId', 'userbibliographyresourceResourceId'],
                    [$this->vars['bibId'], $id]
                );
            }
        }
        $this->session->setVar("mywikindx_Bibliography_add", $this->vars['bibId']);
        $this->session->setVar("setup_Bibliographies", TRUE);
        $success = FACTORY_SUCCESS::getInstance();
        $this->navigate->listView($success->text("addBib"));
        FACTORY_CLOSE::getInstance(); // die
    }
    /**
     * Delete resources from WIKINDX
     */
    public function deleteFromWikindx()
    {
        $gatekeep = FACTORY_GATEKEEP::getInstance();
        $gatekeep->requireSuper = TRUE; // only admins can delete resources
        $gatekeep->init();
        list($idFound, $string) = $this->checkIdInput();
        if (!$idFound)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        if (!is_array($string) && ($string == 'display'))
        {
        	if (is_bool($ids = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_NextPreviousIds'))) {
	            $ids = $this->session->getVar("list_NextPreviousIds");
	        }
        }
        elseif (!is_array($string) && ($string == 'all'))
        {
            $ids = $this->getAllIds();
        }
        else
        {
            $ids = unserialize(base64_decode($string));
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "admin", "DELETERESOURCE.php"]));
        $deleteResource = new DELETERESOURCE();
        $deleteResource->resourceIds['resource_id'] = $ids;
        $deleteResource->navigate = 'list';

        return $deleteResource->deleteResourceConfirm(TRUE);
    }
    /**
     * add/remove selected resources to categories etc.  Display the form
     *
     * @param mixed $message
     */
    public function organizeInit($message = FALSE)
    {
        $pString = '';
        $uuid = FALSE;
        if (is_array($message))
        {
            $pString = $message[0];
            $uuid = $message[1];
        }
        $pString .= \HTML\strong($this->messages->text("resources", "warningOrganize"));
        $pString .= \FORM\formHeader('list_LISTADDTO_CORE', "onsubmit=\"selectAll();return true;\"");
        $pString .= \FORM\hidden("method", "organize");
        $pString .= \FORM\hidden('browserTabID', $this->browserTabID);
        $display = ['categoryDisplay' => $this->messages->text("resources", "categories"),
            'subcategoryDisplay' => $this->messages->text("resources", "subcategories"),
            'languageDisplay' => $this->messages->text("resources", "languages"),
            'keywordDisplay' => $this->messages->text("resources", "keywords"), ];
        if (!empty($this->catForm->userTags))
        {
            $display['usertagDisplay'] = $this->messages->text("resources", "usertags");
        }
        list($idFound, $string) = $this->checkIdInput($uuid);
        if (!$idFound)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        if (!$uuid)
        {
            $uuid = \TEMPSTORAGE\getUuid($this->db);
            \TEMPSTORAGE\store($this->db, $uuid, $string);
        }
        $pString .= $this->catForm->getTable(TRUE);
        $pString .= \FORM\hidden('uuid', $uuid);
        $check = !empty($this->catForm->formData) && array_key_exists('replaceExisting', $this->catForm->formData) ? TRUE : FALSE;
        $pString .= \HTML\p(\FORM\checkbox($this->messages->text("resources", "replaceExisting"), "replaceExisting", $check) . BR .
            \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "replaceExisting")
            ), 'hint'));
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));
        $pString .= \FORM\formEnd();
        \AJAX\loadJavascript(WIKINDX_URL_BASE . '/core/modules/resource/resourceCategoryEdit.js?ver=' . WIKINDX_PUBLIC_VERSION);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * addToUserBibInit
     *
     * @param mixed $message
     */
    public function addToUserBibInit($message = FALSE)
    {
        $pString = $uuid = $usingBib = FALSE;
        $uuid = FALSE;
        if (is_array($message))
        {
            $pString = $message[0];
            $uuid = $message[1];
        }
        $bibs = [];
        $useBib = GLOBALS::getUserVar('BrowseBibliography');
        if ($useBib)
        {
            $this->db->formatConditions(['userbibliographyId' => $useBib]);
            $usingBib = $this->db->selectFirstField('user_bibliography', 'userbibliographyTitle');
        }
        $tempU = $this->commonBib->getUserBibs();
        $tempUG = $this->commonBib->getGroupBibs();
        if (!empty($tempU))
        {
            $bibs[0] = $this->messages->text('user', 'userBibs');
            foreach ($tempU as $key => $value)
            {
                $bibs[$key] = $value;
            }
        }
        if (!empty($tempUG))
        {
            $bibs[-1] = $this->messages->text('user', 'userGroupBibs');
            foreach ($tempUG as $key => $value)
            {
                $bibs[$key] = $value;
            }
        }
        if (!empty($bibs))
        {
            list($idFound, $string) = $this->checkIdInput($uuid);
            if (!$idFound)
            {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
            }
            if (!$uuid)
            {
                $uuid = \TEMPSTORAGE\getUuid($this->db);
                \TEMPSTORAGE\store($this->db, $uuid, $string);
            }
            $pString .= \FORM\formHeader('list_LISTADDTO_CORE');
            $pString .= \FORM\hidden("method", "addResourceToBib");
            $pString .= \FORM\hidden('uuid', $uuid);
        	$pString .= \FORM\hidden('browserTabID', $this->browserTabID);
            $pString .= \HTML\tableStart();
            $pString .= \HTML\trStart();
            $sessVar = $this->session->getVar("mywikindx_Bibliography_add");
            if ($sessVar)
            {
                $pString .= \HTML\td(\FORM\selectedBoxValue(FALSE, "bibId", $bibs, $sessVar, 10) .
                    \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add"))));
            }
            else
            {
                $pString .= \HTML\td(\FORM\selectFBoxValue(FALSE, "bibId", $bibs, 10) .
                    \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add"))));
            }
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \FORM\formEnd();
        }
        else
        {
            $pString = $this->errors->text("warning", "noBibliographies");
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "addToBib"));
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Check for proper resource ID input and produce hidden fields
     *
     * @param mixed $uuid default FALSE
     *
     * @return array
     */
    private function checkIdInput($uuid = FALSE)
    {
        if ($uuid)
        {
            $string = \TEMPSTORAGE\fetch($this->db, $uuid);

            return [TRUE, $string];
        }
        if (array_key_exists("selectWhat", $this->vars) && ($this->vars['selectWhat'] != 'checked'))
        {
            if ($this->vars['selectWhat'] == 'all')
            {
                return [TRUE, 'all'];
            }
            if ($this->vars['selectWhat'] == 'display')
            {
                return [TRUE, 'display'];
            }

            return [FALSE, FALSE];
        }
        foreach ($this->vars as $key => $value)
        {
            if (mb_strstr($key, "bib_"))
            {
                $string[] = mb_substr($key, 4);
            }
        }
        if (isset($string))
        {
            return [TRUE, base64_encode(serialize($string))];
        }
        else
        {
            return [FALSE, FALSE];
        }
    }
    /**
     * Display COinS data
     */
    private function displayCoins()
    {
        $coins = FACTORY_EXPORTCOINS::getInstance();
        $res = FACTORY_RESOURCECOMMON::getInstance();
        list($idFound, $string) = $this->checkIdInput();
        $ids = [];
        if (!is_array($string) && ($string == 'display'))
        {
        	if (is_bool($ids = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_NextPreviousIds'))) {
	            $ids = $this->session->getVar("list_NextPreviousIds");
	        }
        }
        elseif (!is_array($string) && ($string == 'all'))
        {
            $ids = $this->getAllIds();
        }
        else
        {
            $ids = unserialize(base64_decode($string));
        }
        if (empty($ids))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');

            return;
        }
        $resultset = $res->getResource($ids);
        $creators = [];
        $this->getCreators($ids, $creators);
        while ($row = $this->db->fetchRow($resultset))
        {
            GLOBALS::addTplVar('content', $coins->export($row, $creators[$row['resourceId']]));
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "exportCoins"));
        GLOBALS::addTplVar('content', \HTML\p($this->messages->text('resources', 'exportCoins2')));
    }
    /**
     * Getcreator details for COinS export
     *
     * @param mixed $resourceIds
     * @param mixed $creators
     */
    private function getCreators($resourceIds, &$creators)
    {
        $this->db->formatConditionsOneField($resourceIds, 'resourcecreatorResourceId');
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->orderBy('resourcecreatorResourceId', TRUE, FALSE);
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourcecreatorRole', TRUE, FALSE);
        $this->db->orderBy('resourcecreatorOrder', TRUE, FALSE);
        $resultset = $this->db->select('resource_creator', ['resourcecreatorResourceId', ['creatorSurname' => 'surname'],
            ['creatorFirstname' => 'firstname'], ['creatorInitials' => 'initials'], ['creatorPrefix' => 'prefix'],
            'creatorId', 'resourcecreatorRole', ]);
        while ($row = $this->db->fetchRow($resultset))
        {
            $array = [
                'surname' => $row['surname'],
                'firstname' => $row['firstname'],
                'initials' => $row['initials'],
                'prefix' => $row['prefix'],
                'creatorId' => $row['creatorId'],
            ];
            $creators[$row['resourcecreatorResourceId']][$row['creatorId']] = array_map([$this, "removeSlashes"], $array);
        }
    }
    /**
     * Add selected resources to the basket
     */
    private function addToBasket()
    {
        list($idFound, $string) = $this->checkIdInput();
        if (!$idFound)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        if (!is_array($string) && ($string == 'display'))
        {
        	if (is_bool($ids = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_NextPreviousIds'))) {
	            $ids = $this->session->getVar("list_NextPreviousIds");
	        }
        }
        elseif (!is_array($string) && ($string == 'all'))
        {
            $ids = $this->getAllIds();
        }
        else
        {
            $ids = unserialize(base64_decode($string));
        }
        if (is_bool($basket = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'basket_List'))) {
	        $basket = $this->session->getVar("basket_List");
	    }
        if (!is_array($basket)) {
            $basket = [];
        }
        foreach ($ids as $resourceId)
        {
            if (array_search($resourceId, $basket) === FALSE)
            {
                $basket[] = $resourceId;
            }
        }
        // Ensure array is unique
        array_unique($basket);
        $this->session->setVar("basket_List", $basket);
        if ($this->browserTabID) {
	    	\TEMPSTORAGE\store($this->db, $this->browserTabID, ['basket_List' => $basket]);
	    }
        $success = FACTORY_SUCCESS::getInstance();
        $this->navigate->listView($success->text("basketAdd"));
        FACTORY_CLOSE::getInstance(); // die
    }
    /**
     * Remove selected resources from the basket
     */
    private function removeFromBasket()
    {
        list($idFound, $string) = $this->checkIdInput();
        if (!$idFound)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        if (!is_array($string) && ($string == 'display'))
        {
            $ids = $this->session->getVar("list_NextPreviousIds");
        }
        elseif (!is_array($string) && ($string == 'all'))
        {
            $ids = $this->getAllIds();
        }
        else
        {
            $ids = unserialize(base64_decode($string));
        }
        if (is_bool($basket = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'basket_List'))) {
	        $basket = $this->session->getVar("basket_List");
	    }
        if (!is_array($basket)) {
            $basket = [];
        }
        foreach ($ids as $resourceId)
        {
            if (($key = array_search($resourceId, $basket)) !== FALSE)
            {
                unset($basket[$key]);
            }
        }
        if (empty($basket))
        {
            $this->session->delVar("basket_List");
            \TEMPSTORAGE\deleteKeys($this->db, $this->browserTabID, ['basket_List']);
        }
        else
        {
            $this->session->setVar("basket_List", $basket);
            if ($this->browserTabID) {
	    		\TEMPSTORAGE\store($this->db, $this->browserTabID, ['basket_List' => $basket]);
	    	}
        }
        $success = FACTORY_SUCCESS::getInstance();
        if (empty($basket))
        {
            $message = rawurlencode($success->text("basketRemove"));
            header("Location: index.php?message=$message");
            die;
        }
        $this->navigate->listView($success->text("basketRemove"));
        FACTORY_CLOSE::getInstance(); // die
    }
    /**
     * Get resource ids ready for writing to database
     *
     * @return array
     */
    private function getHiddenIds()
    {
        if (!array_key_exists("uuid", $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        $idsString = \TEMPSTORAGE\fetch($this->db, $this->vars['uuid']);
        \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
        if ($idsString == 'display')
        {
        	if (is_bool($ids = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_NextPreviousIds'))) {
	            $ids = $this->session->getVar("list_NextPreviousIds");
	        }
        }
        elseif ($idsString == 'all')
        {
            $ids = $this->getAllIds();
        }
        else
        {
            $ids = unserialize(base64_decode($idsString));
        }
        if (!isset($ids))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }

        return $ids;
    }
    /**
     * Delete resources from a user bibliography
     */
    private function deleteFromBib()
    {
        list($idFound, $string) = $this->checkIdInput();
        if (!$idFound)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        if (!is_array($string) && ($string == 'display'))
        {
        	if (is_bool($ids = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_NextPreviousIds'))) {
	            $ids = $this->session->getVar("list_NextPreviousIds");
	        }
        }
        elseif (!is_array($string) && ($string == 'all'))
        {
            $ids = $this->getAllIds();
        }
        else
        {
            $ids = unserialize(base64_decode($string));
        }
        $this->db->formatConditions(['userbibliographyresourceBibliographyId' => GLOBALS::getUserVar('BrowseBibliography')]);
        $this->db->formatConditionsOneField($ids, 'userbibliographyresourceResourceId');
        $this->db->delete('user_bibliography_resource');
        $bibs = $this->commonBib->getUserBibs();
        $success = FACTORY_SUCCESS::getInstance();
        if (!empty($bibs))
        {
            $this->session->setVar("setup_Bibliographies", TRUE);
        }
        $this->db->formatConditions(['userbibliographyresourceBibliographyId' => GLOBALS::getUserVar('BrowseBibliography')]);
        $resultset = $this->db->select('user_bibliography_resource', ['userbibliographyresourceId']);
        if (!$this->db->numRows($resultset))
        {
            $this->db->formatConditions(['usersId' => $this->session->getVar('setup_UserId')]);
            $this->db->update('users', ['usersBrowseBibliography' => 0]);
            $message = rawurlencode($success->text("deleteFromBib"));
            header("Location: index.php?message=$message");
            die;
        }
        $this->navigate->listView($success->text("deleteFromBib"));
        FACTORY_CLOSE::getInstance(); // die
    }
    /**
     * Approve quarantined resources
     */
    private function unquarantine()
    {
        $gatekeep = FACTORY_GATEKEEP::getInstance(); // superadmin required
        $gatekeep->requireSuper = TRUE;
        $gatekeep->init();
        list($idFound, $string) = $this->checkIdInput();
        if (!$idFound)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        if (!is_array($string) && ($string == 'display'))
        {
        	if (is_bool($ids = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_NextPreviousIds'))) {
	            $ids = $this->session->getVar("list_NextPreviousIds");
	        }
        }
        elseif (!is_array($string) && ($string == 'all'))
        {
            $ids = $this->getAllIds();
        }
        else
        {
            $ids = unserialize(base64_decode($string));
        }
        $this->db->formatConditionsOneField($ids, 'resourcemiscId');
        $updateArray = ['resourcemiscQuarantine' => 'N'];
        $this->db->update('resource_misc', $updateArray);
        $success = FACTORY_SUCCESS::getInstance();
        $this->db->formatConditions(['resourcemiscQuarantine' => 'Y']);
        $resultset = $this->db->select('resource_misc', ['resourcemiscId']);
        if (!$this->db->numRows($resultset))
        {
            $message = rawurlencode($success->text("unquarantineResource"));
            header("Location: index.php?message=$message");
            die;
        }
        $this->navigate->listView($success->text("unquarantineResource"));
        FACTORY_CLOSE::getInstance(); // die
    }
    /**
     * Get all resource ids when session list_AllIds == 'all'
     *
     * @return array
     */
    private function getAllIds()
    {
    	if (is_bool($ids = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_AllIds'))) {
	        $ids = $this->session->getVar("list_AllIds");
	    }
        return $ids;
    }
}
