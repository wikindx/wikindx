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
 * BROWSECATEGORY class
 *
 * Browse categories as a 'tag cloud'
 */
class BROWSECATEGORY
{
    public $category;
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
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "browse", "BROWSECOMMON.php"]));
        $this->common = new BROWSECOMMON();

        $this->messages = FACTORY_MESSAGES::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseCategory"));
    }
    /**
     * display categories
     */
    public function init()
    {
        $this->sum = $this->category = [];
        $this->getCategories();
        natcasesort($this->sum);
        $this->category = $this->common->paging($this->category);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        $this->common->pagingLinks('action=browse_BROWSECATEGORY_CORE');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get categories from db
     */
    public function getCategories()
    {
        $this->common->userBibCondition('resourcecategoryResourceId');
        $this->db->formatConditions($this->db->formatFields('resourcecategoryCategoryId') . ' IS NOT NULL');
        $subQ = $this->db->subQuery($this->db->selectCountsNoExecute(
            'resource_category',
            'resourcecategoryCategoryId',
            FALSE,
            FALSE,
            TRUE,
            TRUE
        ), 't');
        $this->db->leftJoin('category', 'categoryId', 'resourcecategoryCategoryId');
        $this->db->orderBy('categoryCategory');
        $recordset = $this->db->selectFromSubQuery(FALSE, ['categoryId', 'categoryCategory', 'count'], $subQ);
        while ($row = $this->db->fetchRow($recordset)) {
            $this->collate($row);
        }
        // Place 'General' first
        if (array_key_exists(1, $this->category)) {
            $general[1] = $this->category[1];
            unset($this->category[1]);
        }
        foreach ($this->category as $id => $category) {
            $general[$id] = $category;
        }
        $this->category = $general;
    }
    /**
     * Add categories to array and sum totals
     *
     * @param mixed $row
     */
    private function collate($row)
    {
        $this->category[$row['categoryId']] = preg_replace(
            "/{(.*)}/Uu",
            "$1",
            \HTML\nlToHtml($row['categoryCategory'])
        );
        $this->sum[$row['categoryId']] = $row['count'];
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
        foreach ($this->category as $id => $name) {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=categoryProcess&id=' . $id)) .
                "&nbsp;[" . $this->sum[$id] . "]";
        }

        return implode("&nbsp; ", $links);
    }
}
