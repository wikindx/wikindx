<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 *	CATEGORYTREE class.
 */
class CATEGORYTREE
{
    private $db;
    private $common;
    private $categories;
    private $keywords;
    private $subcategories;
    private $messages;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        include_once("core/browse/BROWSECOMMON.php");
        $this->common = new BROWSECOMMON();
        include_once("core/modules/browse/BROWSECATEGORY.php");
        $this->categories = new BROWSECATEGORY();
        include_once("core/modules/browse/BROWSEKEYWORD.php");
        $this->keywords = new BROWSEKEYWORD();
        include_once("core/modules/browse/BROWSESUBCATEGORY.php");
        $this->subcategories = new BROWSESUBCATEGORY();

        $this->messages = FACTORY_MESSAGES::getInstance();
    }
    /**
     * init
     */
    public function init()
    {
        include_once("core/modules/help/HELPMESSAGES.php");
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('categoryTree'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "categoryTree"));
        $this->categories->sum = $this->categories->category = [];
        $this->categories->getCategories();
        $list['info'] = $this->categories->bib;
        GLOBALS::setTplVar('resourceListInfo', $list);
        unset($list);
        $this->catSum = $this->categories->sum;
        $this->category = $this->categories->category;
        // 3 columns in table
        $pString = \HTML\tableStart('generalTable borderStyleSolid left');
        $index = 1;
        $rows = 0;
        foreach ($this->category as $catId => $catName) {
            $tdKey = $tdSubcat = FALSE;
            $this->keywords->sum = $this->keywords->keyword = $this->subcategories->sum = $this->subcategories->subcategory = [];
            $this->getKeywords($catId);
            $this->kwSum = $this->keywords->sum;
            $keyword = $this->keywords->keyword;
            $this->getSubcategories($catId);
            $this->scSum = $this->subcategories->sum;
            $subcategory = $this->subcategories->subcategory;
            if (($index % 3) == 1) {
                $pString .= \HTML\trStart();
            }
            $td = \HTML\strong(\HTML\a("link", $catName, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=categoryProcess&id=' . $catId))) .
                '&nbsp;[' . $this->catSum[$catId] . ']' . BR;
            if (!empty($keyword)) {
                $tdKey = $this->messages->text("misc", "categoryTreeKeywords");
                foreach ($keyword as $kwId => $kwName) {
                    if (!$kwId) {
                        continue;
                    }
                    if (array_key_exists($kwId, $this->keywords->glossary)) {
                        $glossary = $this->keywords->glossary[$kwId];
                    } else {
                        $glossary = "";
                    }
                    $tdKey .= BR . '&nbsp;&nbsp;&nbsp;&nbsp;' .
                        \HTML\a("link", $kwName, 'index.php?' .
                        htmlentities("action=list_LISTSOMERESOURCES_CORE&method=keywordProcess&id=$kwId&catId=$catId"), "", $glossary) .
                        '&nbsp;[' . $this->kwSum[$kwId] . ']';
                }
            }
            if (!empty($subcategory)) {
                $tdSubcat = $this->messages->text("misc", "categoryTreeSubcategories");
                foreach ($subcategory as $scId => $scName) {
                    if (!$scId) {
                        continue;
                    }
                    $tdSubcat .= BR . '&nbsp;&nbsp;&nbsp;&nbsp;' .
                        \HTML\a("link", $scName, 'index.php?' .
                        htmlentities("action=list_LISTSOMERESOURCES_CORE&method=subcategoryProcess&id=$scId&catId=$catId")) .
                        '&nbsp;[' . $this->scSum[$scId] . ']';
                }
            }
            if ($tdKey && $tdSubcat) { // two column table within TD element
                $tdBoth = \HTML\tableStart('left');
                $tdBoth .= \HTML\trStart();
                $tdBoth .= \HTML\td($tdKey);
                $tdBoth .= \HTML\td($tdSubcat);
                $tdBoth .= \HTML\trEnd();
                $tdBoth .= \HTML\tableEnd();

                $pString .= \HTML\td($td . $tdBoth);
            } else {
                $pString .= \HTML\td($td . $tdKey . $tdSubcat, 'generalTable borderStyleSolid left');
            }
            if (!($index % 3)) {
                $rows++;
                $pString .= \HTML\trEnd();
            }
            $index++;
        }
        // If we are not still on the first row, add blank cells
        if ($rows) {
            if (($index % 3) == 2) { // 1 populated cell so add 2 blank cells
                $pString .= \HTML\td('&nbsp;');
                $pString .= \HTML\td('&nbsp;');
            } elseif (!($index % 3)) { // 2 populated cells so add 1 blank cell
                $pString .= \HTML\td("&nbsp;");
            }
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * getKeywords
     *
     * @param int $catId
     */
    private function getKeywords($catId)
    {
        $this->common->userBibCondition('resourcecategoryResourceId');
        $this->db->formatConditions(['resourcecategoryCategoryId' => $catId]);
        $this->db->groupBy('resourcecategoryResourceId');
        $subSubQ = $this->db->subQuery($this->db->selectNoExecute('resource_category', 'resourcecategoryResourceId'), 'c', TRUE, TRUE);
        $this->db->formatConditions($this->db->formatFields('resourcekeywordResourceId') . ' IS NOT NULL');
        $this->db->leftJoin('resource_keyword', 'resourcekeywordResourceId', 'resourcecategoryResourceId');
        $this->db->groupBy('resourcekeywordKeywordId');
        $subQ = $this->db->subQuery($this->db->selectCountsNoExecute(FALSE, 'resourcekeywordKeywordId', FALSE, $subSubQ, TRUE, TRUE), 'k');
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
        $this->db->orderBy('keywordKeyword');
        $recordset = $this->db->selectFromSubQuery(FALSE, ['resourcekeywordKeywordId', 'keywordKeyword', 'keywordGlossary', 'count'], $subQ);
        while ($row = $this->db->fetchRow($recordset)) {
            $this->keywords->collate($row);
        }
    }
    /**
     * getSubcategories
     *
     * @param int $catId
     */
    private function getSubcategories($catId)
    {
        $this->common->userBibCondition('resourcecategoryResourceId');
        $this->db->leftJoin('subcategory', 'subcategoryId', 'resourcecategorySubcategoryId');
        $this->db->leftJoin('category', 'categoryId', 'subcategoryCategoryId');
        $this->db->formatConditions(['categoryId' => $catId]);
        $having = $this->db->count('resourcecategorySubcategoryId') . $this->db->greater . $this->db->tidyInput(0);
        $this->db->groupBy(['resourcecategorySubcategoryId', 'subcategorySubcategory'], TRUE, $having);
        $this->db->orderBy('subcategorySubcategory');
        $recordset = $this->db->selectCounts('resource_category', 'resourcecategorySubcategoryId', 'subcategorySubcategory', FALSE, FALSE);
        while ($row = $this->db->fetchRow($recordset)) {
            $this->subcategories->collate($row);
        }
    }
}
