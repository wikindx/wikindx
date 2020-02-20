<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * BROWSESUBCATEGORY class
 *
 * Browse subcategories as a 'tag cloud'
 */
class BROWSESUBCATEGORY
{
    public $subcategory;
    public $categories;
    public $sum;
    public $bib;
    private $db;
    private $vars;
    private $common;
    private $messages;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once("core/browse/BROWSECOMMON.php");
        $this->common = new BROWSECOMMON();

        $this->messages = FACTORY_MESSAGES::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseSubcategory"));
    }
    /**
     * display categories
     */
    public function init()
    {
        $this->sum = $this->subcategory = [];
        $this->getSubcategories();
        if (empty($this->subcategory))
        {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noSubcategories"));

            return;
        }
        natcasesort($this->sum);
        $this->subcategory = $this->common->paging($this->subcategory);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        $this->common->pagingLinks('action=browse_BROWSESUBCATEGORY_CORE');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get subcategories from db
     */
    public function getSubcategories()
    {
        $this->common->userBibCondition('resourcecategoryResourceId');
        $this->db->leftJoin('subcategory', 'subcategoryId', 'resourcecategorySubcategoryId');
        $this->db->leftJoin('category', 'categoryId', 'subcategoryCategoryId');
        $this->db->groupBy('resourcecategorySubcategoryId', TRUE, $this->db->formatFields('count') .
            $this->db->greater . $this->db->tidyInput(0));
        $this->db->orderBy('subcategorySubcategory');
        $recordset = $this->db->selectCounts(
            'resource_category',
            'resourcecategorySubcategoryId',
            ['subcategorySubcategory', 'subcategoryCategoryId', 'categoryId', 'categoryCategory']
        );
        while ($row = $this->db->fetchRow($recordset))
        {
            if ($row['resourcecategorySubcategoryId'])
            {
                $this->categories[$row['resourcecategorySubcategoryId']] = \HTML\dbToHtmlTidy($row['categoryCategory']);
            }
            $this->collate($row);
        }
    }
    /**
     * Add categories to array and sum totals
     */
    public function collate($row)
    {
        if (!$row['subcategorySubcategory'])
        {
            return;
        }
        $this->subcategory[$row['resourcecategorySubcategoryId']] = preg_replace(
            "/{(.*)}/Uu",
            "$1",
            \HTML\dbToHtmlTidy($row['subcategorySubcategory'])
        );
        $this->sum[$row['resourcecategorySubcategoryId']] = $row['count'];
    }
    /**
     * Process and display results
     *
     * @return string
     */
    private function process()
    {
        $lowestSum = current($this->sum);
        $highestSum = end($this->sum);
        foreach ($this->subcategory as $id => $name)
        {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=subcategoryProcess&id=' . $id)) .
                '&nbsp;(' . $this->categories[$id] . ")&nbsp;[" . $this->sum[$id] . "]";
        }

        return implode("&nbsp; ", $links);
    }
}
