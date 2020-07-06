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
 * Common methods for categories
 *
 * @package wikindx\core\category
 */
class CATEGORY
{
    /** object */
    private $db;
    /** object */
    private $messages;
    /** object */
    private $commonBib;

    /**
     * CATEGORY
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();

        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
    }
    /**
     * Get category names from WKX_category.  Return associative array of id => category.
     *
     * @param bool $userBib FALSE (default) or TRUE (return only resource types within current user bibliography)
     * @param bool $extant FALSE (default) or TRUE (return only categories for which resources exist)
     * @param mixed $typeArray FALSE (default) or limits categories to those belonging to these types
     * @param bool $withGeneral FALSE (default) or include 'General' category in returned array
     *
     * @return mixed associative array or FALSE
     */
    public function grabAll($userBib = FALSE, $extant = FALSE, $typeArray = FALSE, $withGeneral = TRUE)
    {
        if ($userBib) {
            $this->db->leftJoin('resource_category', 'resourcecategoryCategoryId', 'categoryId');
            $this->commonBib->userBibCondition('resourceCategoryId');
        } else {
            if ($extant) {
                $this->db->leftJoin('resource_category', 'resourcecategoryCategoryId', 'categoryId');
                $userBib = TRUE;
            }
        }
        if (is_array($typeArray) && !empty($typeArray)) {
            if (!$userBib) {
                $this->db->leftJoin('resource_category', 'resourcecategoryCategoryId', 'categoryId');
            }
            $this->db->leftJoin('resource', 'resourceId', 'resourcecategoryResourceId');
            foreach ($typeArray as $type) {
                $conditions[] = $type;
            }
            $this->db->formatConditionsOneField($conditions, 'resourceType');
        }
        if ($withGeneral) {
            $categories[1] = 'General';
        }
        $this->db->orderBy('categoryCategory');
        $recordset = $this->db->select('category', ["categoryId", "categoryCategory"], TRUE);
        while ($row = $this->db->fetchRow($recordset)) {
            if ($withGeneral && $row['categoryCategory'] == 'General') {
                continue;
            }
            $categories[$row['categoryId']] = \HTML\dbToFormTidy($row['categoryCategory']);
        }
        if (isset($categories)) {
            // $categories[1] is hard-wired to 'General' in the database -- for display, change it to the required localization
            // 4/March/2013 -- keep 'General' as is in database but ensure it is placed first in list
            //			$categories[1] = $this->messages->text('resources', 'general');
            return $categories;
        }
        // Shouldn't ever happen since 1 => 'General' should always be set and each resource must belong to at least one category.
        return FALSE;
    }
    /**
     * Get subcategory names from WKX_subcategory.  Return associative array of id => subcategory.
     *
     * @param bool $withCategory FALSE (default) or TRUE (return list of subcategories repended by their relevant category name)
     * @param bool $userBib FALSE (default) or TRUE (return only resource types within current user bibliography)
     * @param mixed $matchCategoryIds FALSE (default) or array of category IDs (returned subcategories must belong to those categories)
     * @param bool $extant FALSE (default) or TRUE (return only resource types (or subcategories ?) for which resources exist)
     *
     * @return array|false
     */
    public function grabSubAll($withCategory = FALSE, $userBib = FALSE, $matchCategoryIds = FALSE, $extant = FALSE)
    {
        if ($userBib) {
            $this->db->leftJoin('resource_category', 'resourcecategorySubcategoryId', 'subcategoryId');
            $this->commonBib->userBibCondition('resourceCategoryId');
        } else {
            if ($extant) {
                $this->db->leftJoin('resource_category', 'resourcecategorySubcategoryId', 'subcategoryId');
            }
        }
        $this->db->orderBy('subcategorySubcategory');
        $recordset = $this->db->select('subcategory', ["subcategoryId", "subcategorySubcategory", "subcategoryCategoryId"], TRUE);
        while ($row = $this->db->fetchRow($recordset)) {
            // Gather only subcategories within specified categories if $matchCategoryIds is given
            if (!$matchCategoryIds ||
                (is_array($matchCategoryIds) && (array_search($row['subcategoryCategoryId'], $matchCategoryIds) !== FALSE))) {
                $subcategories[$row['subcategoryId']] = \HTML\dbToFormTidy($row['subcategorySubcategory']);
            }
        }
        if (isset($subcategories)) {
            if ($withCategory) {
                $categories = $this->grabAll($userBib);
                foreach ($subcategories as $key => $value) {
                    $this->db->formatConditions(['subcategoryId' => $key]);
                    $catKey = $this->db->selectFirstField('subcategory', 'subcategoryCategoryId');
                    $list[$key] = $categories[$catKey] . ' -- ' . $value;
                }
                natcasesort($list);

                return $list;
            } else {
                return $subcategories;
            }
        }

        return FALSE;
    }
    /**
     * Make the category select transfer arrows to transfer categories between select boxes with onclick
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrowsCat()
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'selectCategory',
        ];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'discardCategory',
        ];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);

        return [$toRightImage, $toLeftImage];
    }
    /**
     * Make the subcategory select transfer arrows to transfer subcategories between select boxes with onclick
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrowsSubcat()
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'selectSubcategory',
        ];
        $toRightImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'discardSubcategory',
        ];
        $toLeftImage = \AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);

        return [$toRightImage, $toLeftImage];
    }
    /**
     * Make the category select transfer arrows to transfer categories between select boxes with onclick (Up and Down)
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrowsCatUpDown()
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'selectCategory',
        ];
        $toBottomImage = \AJAX\jActionIcon('toBottom', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'discardCategory',
        ];
        $toTopImage = \AJAX\jActionIcon('toTop', 'onclick', $jsonArray);

        return [$toBottomImage, $toTopImage];
    }
    /**
     * Make the subcategory select transfer arrows to transfer subcategories between select boxes with onclick (Up and Down)
     *
     * @return array (toRightImage, toLeftImage)
     */
    public function transferArrowsSubcatUpDown()
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'selectSubcategory',
        ];
        $toBottomImage = \AJAX\jActionIcon('toBottom', 'onclick', $jsonArray);
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'discardSubcategory',
        ];
        $toTopImage = \AJAX\jActionIcon('toTop', 'onclick', $jsonArray);

        return [$toBottomImage, $toTopImage];
    }
}
