<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();

        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->navigate = FACTORY_NAVIGATE::getInstance();
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
     * hide or display fields
     */
    public function displayCategory()
    {
        include_once('core/modules/resource/RESOURCECATEGORYEDIT.php');
        $catForm = new RESOURCECATEGORYEDIT();

        if ($this->session->getVar("organize_CategoryHide"))
        { // currently hidden so show
            $div = $catForm->displayCategory(TRUE);
            $this->session->setVar("organize_CategoryHide", FALSE);
        }
        else
        { // currently visible so hide
            $div = \HTML\tableStart('generalTable');
            $div .= \HTML\trStart();
            $jScript = 'index.php?action=list_LISTADDTO_CORE&method=displayCategory';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'displayCategory',
                'targetDiv' => 'categoryDiv',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            $div .= \HTML\td($this->messages->text('select', "availableCategory") . "&nbsp;&nbsp;" .
                \FORM\checkbox(FALSE, "displayCategory", FALSE, '', $js = ''), 'padding3px');
            $div .= \HTML\trEnd();
            $div .= \HTML\tableEnd();
            $this->session->setVar("organize_CategoryHide", TRUE);
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$div"]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * hide or display fields
     */
    public function displaySubcategory()
    {
        include_once('core/modules/resource/RESOURCECATEGORYEDIT.php');
        $catForm = new RESOURCECATEGORYEDIT();

        if ($this->session->getVar("organize_SubcategoryHide"))
        { // currently hidden so show
            $div = $catForm->displaySubcategory(TRUE);
            $this->session->setVar("organize_SubcategoryHide", FALSE);
        }
        else
        { // currently visible so hide
            $div = \HTML\tableStart('generalTable');
            $div .= \HTML\trStart();
            $jScript = 'index.php?action=list_LISTADDTO_CORE&method=displaySubcategory';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'displaySubcategory',
                'targetDiv' => 'subcategoryDiv',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            $div .= \HTML\td($this->messages->text('select', "availableSubcategory") . "&nbsp;&nbsp;" .
                \FORM\checkbox(FALSE, "displaySubcategory", FALSE, '', $js = ''), 'padding3px');
            $div .= \HTML\trEnd();
            $div .= \HTML\tableEnd();
            $this->session->setVar("organize_SubcategoryHide", TRUE);
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$div"]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * hide or display fields
     */
    public function displayLanguage()
    {
        include_once('core/modules/resource/RESOURCECATEGORYEDIT.php');
        $catForm = new RESOURCECATEGORYEDIT();

        if ($this->session->getVar("organize_LanguageHide"))
        { // currently hidden so show
            $div = $catForm->displayLanguage(TRUE);
            $this->session->setVar("organize_LanguageHide", FALSE);
        }
        else
        { // currently visible so hide
            $div = \HTML\tableStart();
            $div .= \HTML\trStart();
            $jScript = 'index.php?action=list_LISTADDTO_CORE&method=displayLanguage';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'displayLanguage',
                'targetDiv' => 'languageDiv',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            $div .= \HTML\td($this->messages->text('select', "language") . "&nbsp;&nbsp;" .
                \FORM\checkbox(FALSE, "displayLanguage", FALSE, '', $js = ''));
            $div .= \HTML\trEnd();
            $div .= \HTML\tableEnd();
            $this->session->setVar("organize_LanguageHide", TRUE);
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$div"]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * hide or display fields
     */
    public function displayKeyword()
    {
        include_once('core/modules/resource/RESOURCECATEGORYEDIT.php');
        $catForm = new RESOURCECATEGORYEDIT();

        if ($this->session->getVar("organize_KeywordHide"))
        { // currently hidden so show
            $div = $catForm->displayKeyword(TRUE);
            $this->session->setVar("organize_KeywordHide", FALSE);
        }
        else
        { // currently visible so hide
            $div = \HTML\tableStart('generalTable');
            $div .= \HTML\trStart();
            $jScript = 'index.php?action=list_LISTADDTO_CORE&method=displayKeyword';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'displayKeyword',
                'targetDiv' => 'keywordDiv',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            $div .= \HTML\td($this->messages->text('select', "availableKeyword") . "&nbsp;&nbsp;" .
                \FORM\checkbox(FALSE, "displayKeyword", FALSE, '', $js = ''), 'padding3px');
            $div .= \HTML\trEnd();
            $div .= \HTML\tableEnd();
            $this->session->setVar("organize_KeywordHide", TRUE);
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$div"]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * hide or display fields
     */
    public function displayUsertag()
    {
        include_once('core/modules/resource/RESOURCECATEGORYEDIT.php');
        $catForm = new RESOURCECATEGORYEDIT();

        if ($this->session->getVar("organize_UsertagHide"))
        { // currently hidden so show
            $div = $catForm->displayUserTags(TRUE);
            $this->session->setVar("organize_UsertagHide", FALSE);
        }
        else
        { // currently visible so hide
            $div = \HTML\tableStart('generalTable');
            $div .= \HTML\trStart();
            $jScript = 'index.php?action=list_LISTADDTO_CORE&method=displayUsertag';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'displayUsertag',
                'targetDiv' => 'usertagDiv',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            $div .= \HTML\td($this->messages->text('resources', 'availableUserTags') . "&nbsp;&nbsp;" .
                \FORM\checkbox(FALSE, "displayUsertag", FALSE, '', $js = ''), 'padding3px');
            $div .= \HTML\trEnd();
            $div .= \HTML\tableEnd();
            $this->session->setVar("organize_UsertagHide", TRUE);
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$div"]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Update resources with new or removed selections of categories, subcategories, languages, keywords and usertags
     */
    public function organize()
    {
        if (!array_key_exists("languageIds", $this->vars) && !array_key_exists("categoryIds", $this->vars) &&
            !array_key_exists("subcategoryIds", $this->vars) && !array_key_exists("keywords", $this->vars) &&
            !array_key_exists("usertags", $this->vars) && !array_key_exists("replaceExisting", $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        $resourceIds = $this->getHiddenIds();
        // Categories
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
        // Subcategories
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
        // Languages
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
        // Keywords
        $keyword = FACTORY_KEYWORD::getInstance();
        $keywordIds = [];
        if (array_key_exists('keywords', $this->vars) && trim($this->vars['keywords']))
        {
            $keywordTexts = UTF8::mb_explode(',', trim($this->vars['keywords']));
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
        // remove cache files for keywords
        $this->db->deleteCache('cacheResourceKeywords');
        $this->db->deleteCache('cacheMetadataKeywords');
        $this->db->deleteCache('cacheQuoteKeywords');
        $this->db->deleteCache('cacheParaphraseKeywords');
        $this->db->deleteCache('cacheMusingKeywords');
        $this->db->deleteCache('cacheKeywords');
        // User tags
        $usertag = FACTORY_USERTAGS::getInstance();
        $usertagIds = [];
        if (array_key_exists('userTags', $this->vars) && trim($this->vars['userTags']))
        {
            $usertagTexts = UTF8::mb_explode(',', trim($this->vars['userTags']));
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
        $this->session->setVar("addToKeywordCategory", TRUE);
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
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
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
        if ($string == 'display')
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_NextPreviousIds")));
        }
        elseif ($string == 'all')
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_AllIds")));
        }
        else
        {
            $ids = unserialize(base64_decode($string));
        }
        $this->session->setVar("addToKeywordCategory", TRUE);
        include_once("core/modules/admin/DELETERESOURCE.php");
        $deleteResource = new DELETERESOURCE();
        $deleteResource->resourceIds['resource_id'] = $ids;
        $deleteResource->navigate = 'list';

        return $deleteResource->deleteResourceConfirm();
    }
    /**
     * Check for proper resource ID input and produce hidden fields
     *
     * @return array
     */
    private function checkIdInput()
    {
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
        if ($string == 'display')
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_NextPreviousIds")));
        }
        elseif ($string == 'all')
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_AllIds")));
        }
        else
        {
            $ids = unserialize(base64_decode($string)); // checked resources
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
        if ($this->session->issetVar("basket_List"))
        {
            $basket = unserialize($this->session->getVar("basket_List"));
        }
        else
        {
            $basket = [];
        }
        if ($string == 'display')
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_NextPreviousIds")));
        }
        elseif ($string == 'all')
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_AllIds")));
        }
        else
        {
            $ids = unserialize(base64_decode($string));
        }
        //$this->session->delVar("basket_List");
        foreach ($ids as $resourceId)
        {
            if (array_search($resourceId, $basket) === FALSE)
            {
                $basket[] = $resourceId;
            }
        }
        // Ensure array is unique
        array_unique($basket);
        $this->session->setVar("basket_List", serialize($basket));
        $this->session->saveState('basket');
        $this->session->setVar("addToKeywordCategory", TRUE);
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
        if ($this->session->issetVar("basket_List"))
        {
            $basket = unserialize($this->session->getVar("basket_List"));
        }
        else
        {
            $basket = [];
        }
        if ($string == 'display')
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_NextPreviousIds")));
        }
        elseif ($string == 'all')
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_AllIds")));
        }
        else
        {
            $ids = unserialize(base64_decode($string));
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
        }
        else
        {
            $this->session->setVar("basket_List", serialize($basket));
        }
        $this->session->saveState('basket');
        $this->session->setVar("addToKeywordCategory", TRUE);
        $success = FACTORY_SUCCESS::getInstance();
        $this->navigate->listView($success->text("basketRemove"));
        FACTORY_CLOSE::getInstance(); // die
    }
    /**
     * add/remove selected resources to categories etc.  Display the form
     */
    private function organizeInit()
    {
        $this->session->clearArray('organize');
        include_once('core/modules/resource/RESOURCECATEGORYEDIT.php');
        $catForm = new RESOURCECATEGORYEDIT();

        $pString = \HTML\strong($this->messages->text("resources", "warningOrganize"));
        $pString .= \FORM\formHeader('list_LISTADDTO_CORE', "onsubmit=\"selectAll();return true;\"");
        $pString .= \FORM\hidden("method", "organize");
        $display = ['categoryDisplay' => $this->messages->text("resources", "categories"),
            'subcategoryDisplay' => $this->messages->text("resources", "subcategories"),
            'languageDisplay' => $this->messages->text("resources", "languages"),
            'keywordDisplay' => $this->messages->text("resources", "keywords"), ];
        if (!empty($catForm->userTags))
        {
            $display['usertagDisplay'] = $this->messages->text("resources", "usertags");
        }
        list($idFound, $string) = $this->checkIdInput();
        if (!$idFound)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        $pString .= \FORM\hidden("ids", $string);
        $pString .= $catForm->getTable(TRUE);
        $pString .= \HTML\p($this->messages->text("resources", "replaceExisting") . "&nbsp;&nbsp;" .
            \FORM\checkbox(FALSE, "replaceExisting") . '&nbsp;&nbsp;' . \FORM\formSubmit($this->messages->text("submit", "Save")));
        $pString .= \FORM\formEnd();
        \AJAX\loadJavascript(WIKINDX_BASE_URL . '/core/modules/resource/resourceCategoryEdit.js');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * addToUserBibInit
     */
    private function addToUserBibInit()
    {
        $usingBib = $pString = FALSE;
        $bibs = [];
        $useBib = $this->session->getVar("mywikindx_Bibliography_use");
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
            $pString = \FORM\formHeader('list_LISTADDTO_CORE');
            $pString .= \FORM\hidden("method", "addResourceToBib");
            list($idFound, $string) = $this->checkIdInput();
            if (!$idFound)
            {
                $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
            }
            $pString .= \FORM\hidden("ids", $string);
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
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "addToBib"));
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get resource ids ready for writing to database
     *
     * @return array
     */
    private function getHiddenIds()
    {
        if (!array_key_exists("ids", $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        if ($this->vars['ids'] == 'display')
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_NextPreviousIds")));
        }
        elseif ($this->vars['ids'] == 'all')
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_AllIds")));
        }
        else
        {
            $ids = unserialize(base64_decode($this->vars['ids']));
        }
        if (!isset($ids))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }

        return $ids;
    }
    /**
     * Delete resources from a user bibliogaphy
     */
    private function deleteFromBib()
    {
        list($idFound, $string) = $this->checkIdInput();
        if (!$idFound)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this->navigate, 'listView');
        }
        if (array_key_exists('id', $this->vars) && ($this->vars['ids'] == 'display'))
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_NextPreviousIds")));
        }
        elseif (array_key_exists('id', $this->vars) && ($this->vars['ids'] == 'all'))
        {
            $ids = unserialize(base64_decode($this->session->getVar("list_AllIds")));
        }
        else
        {
            $ids = unserialize(base64_decode($string));
        }
        $this->db->formatConditions(['userbibliographyresourceBibliographyId' => $this->session->getVar("mywikindx_Bibliography_use")]);
        $this->db->formatConditionsOneField($ids, 'userbibliographyresourceResourceId');
        $this->db->delete('user_bibliography_resource');
        $bibs = $this->commonBib->getUserBibs();
        if (empty($bibs))
        {
            $bibs = $this->commonBib->getUserBibs();
        }
        if (!empty($bibs))
        {
            $this->session->setVar("setup_Bibliographies", TRUE);
        }
        $this->session->setVar("addToKeywordCategory", TRUE);
        $success = FACTORY_SUCCESS::getInstance();
        $this->navigate->listView($success->text("deleteFromBib"));
        FACTORY_CLOSE::getInstance(); // die
    }
}
