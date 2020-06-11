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
 *	ADMINCATEGORIES class.
 *
 *	Administration of categories and subcategories
 */
class ADMINCATEGORIES
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $gatekeep;
    private $badInput;
    private $categories;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();

        $this->category = FACTORY_CATEGORY::getInstance();

        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();

        $this->gatekeep->requireSuper = TRUE;
        $this->gatekeep->init();
        $this->session->clearArray('edit');
        $this->categories = $this->category->grabAll();
        if (empty($this->categories)) {
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "editCategory"));
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noCategories"));

            return;
        }
        // Administering 'General' category (key == 1) is not allowed.
        unset($this->categories[1]);
    }
    /**
     * Add a category - display options
     *
     * @param false|string $message
     */
    public function catInit($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "editCategory"));
        $pString = $message ? \HTML\p($message, "error", "center") : FALSE;
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        // Add
        $td = \FORM\formHeader("admin_ADMINCATEGORIES_CORE");
        $td .= \FORM\hidden("method", "addCat");
        $td .= \FORM\textInput($this->messages->text("category", "addCategory"), "categoryAdd", FALSE, 30, 255);
        $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")));
        $td .= \FORM\formEnd();
        $pString .= \HTML\td($td, 'generalTable borderStyleSolid left');
        if (!empty($this->categories)) {
            // Edit
            // If preferences reduce long categories, we want to transfer the original rather than the condensed version.
            // Store the base64-encoded value for retrieval in the javascript.
            foreach ($this->categories as $key => $value) {
                $key = $key . '_' . base64_encode($value);
                $categories[$key] = $value;
            }
            $td = \FORM\formHeader("admin_ADMINCATEGORIES_CORE");
            $td .= \FORM\hidden("method", "editCat");
            $td .= \FORM\selectFBoxValue(
                $this->messages->text("category", "editCategory"),
                'categoryId',
                $categories,
                10
            );
            $td .= \HTML\p($this->transferArrow('transferCategory'));
            $td .= \HTML\p(\FORM\textInput(FALSE, "categoryEdit", FALSE, 30, 255));
            $td .= \FORM\hidden('categoryEditId', FALSE);
            $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td, 'generalTable borderStyleSolid left');
            // Delete
            $td = \FORM\formHeader("admin_ADMINCATEGORIES_CORE");
            $td .= \FORM\hidden("method", "deleteCatConfirm");
            $td .= \FORM\selectFBoxValueMultiple(
                $this->messages->text("category", "deleteCategory"),
                'categoryIds',
                $this->categories,
                10
            ) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint');
            $td .= \HTML\p($this->messages->text("category", "deleteWarning"));
            $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Delete")));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td, 'generalTable borderStyleSolid left');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        \AJAX\loadJavascript([WIKINDX_BASE_URL . '/core/modules/admin/categoryEdit.js']);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Add a subcategory - display options
     *
     * @param false|string $message
     */
    public function subInit($message = FALSE)
    {
        if (empty($this->categories)) {
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "editCategory"));
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noCategories"));

            return;
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "editSubcategory"));
        $pString = $message ? \HTML\p($message, "error", "center") : FALSE;
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        // Add
        $td = \FORM\formHeader("admin_ADMINCATEGORIES_CORE");
        $td .= \FORM\hidden("method", "addSub");
        $td .= \FORM\textInput($this->messages->text("category", "addSubcategory"), "addSubcategory", FALSE, 30, 255);
        $categories = $this->category->grabAll();
        $td .= \HTML\p(\FORM\selectFBoxValue(
            $this->messages->text('resources', 'subcategoryPart'),
            'categoryId',
            $categories,
            10
        ));
        $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")));
        $td .= \FORM\formEnd();
        $pString .= \HTML\td($td, 'generalTable borderStyleSolid left');
        // Edit
        $subcategories = $this->category->grabSubAll();
        if (is_array($subcategories)) {
            $td = \FORM\formHeader("admin_ADMINCATEGORIES_CORE");
            $td .= \FORM\hidden("method", "editSub");
            $jScript = 'index.php?action=admin_ADMINCATEGORIES_CORE&method=subcatIsPartCat';
            // Amend category list depending upon which subcategory is chosen
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'subcategoryId',
                'targetDiv' => 'categoryIdDiv',
            ];
            $js = \AJAX\jActionForm('onclick', $jsonArray);
            // If preferences reduce long subcategories, we want to transfer the original rather than the condensed version.
            // Store the base64-encoded value for retrieval in the javascript.
            foreach ($subcategories as $key => $value) {
                $key = $key . '_' . base64_encode($value);
                $subcats[$key] = $value;
            }
            $td1 = \HTML\td(\FORM\selectFBoxValue(
                $this->messages->text("category", "editSubcategory"),
                'subcategoryId',
                $subcats,
                10,
                FALSE,
                $js
            ));
            // Don't collapse the three lines that follow
            // PHP is angry if array_shift get the result of array_keys passed by reference
            $subcategoryIdcond = array_keys($subcategories);
            $subcategoryIdcond = array_shift($subcategoryIdcond);
            $subcategoryIdcond = ['subcategoryId' => $subcategoryIdcond];
            $this->db->formatConditions($subcategoryIdcond);
            $selected = $this->db->selectFirstField('subcategory', 'subcategoryCategoryId');
            $td1 .= \HTML\td(\HTML\div('categoryIdDiv', \FORM\selectedBoxValue(
                $this->messages->text('resources', 'subcategoryPart'),
                'categoryId',
                $categories,
                $selected,
                10
            )));
            $td2 = \HTML\p($this->transferArrow('transferSubcategory'));
            $td2 .= \HTML\p(\FORM\textInput(FALSE, "subcategoryEdit", FALSE, 30, 255));
            $td2 .= \FORM\hidden('subcategoryEditId', FALSE);
            $td2 .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
            $td .= \HTML\tableStart();
            $td .= \HTML\trStart();
            $td .= $td1;
            $td .= \HTML\trEnd();
            $td .= \HTML\trStart();
            $td .= \HTML\td($td2, '', 2);
            $td .= \HTML\trEnd();
            $td .= \HTML\tableEnd();
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td, 'generalTable borderStyleSolid left');
            // Delete
            $td = \FORM\formHeader("admin_ADMINCATEGORIES_CORE");
            $td .= \FORM\hidden("method", "deleteSubcatConfirm");
            $td .= \FORM\selectFBoxValueMultiple(
                $this->messages->text("category", "deleteSubcategory"),
                'subcategoryIds',
                $subcategories,
                10
            ) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint');
            $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Delete")));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td, 'generalTable borderStyleSolid left');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        \AJAX\loadJavascript([WIKINDX_BASE_URL . '/core/modules/admin/categoryEdit.js']);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Amend category select box depening on selection in subcategory select box
     */
    public function subcatIsPartCat()
    {
        $this->db->formatConditions(['subcategoryId' => $this->vars['ajaxReturn']]);
        $selected = $this->db->selectFirstField('subcategory', 'subcategoryCategoryId');
        $categories = $this->category->grabAll();
        $div = \FORM\selectedBoxValue(
            $this->messages->text('resources', 'subcategoryPart'),
            'categoryId',
            $categories,
            $selected,
            10
        );
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Add a category
     */
    public function addCat()
    {
        if (!$input = $this->validateInput('addCat')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'catInit');
        }
        // database match is case insensitive.
        $this->db->formatConditions(['categoryCategory' => $input]);
        $categoryId = $this->db->selectFirstField('category', 'categoryId');
        $pString = $this->success->text("categoryAdd");
        // If category already exists quietly return without error.
        if ($categoryId) {
            return $this->catInit($pString);
        }
        $this->db->insert('category', 'categoryCategory', $input);
        $this->categories = $this->category->grabAll();
        unset($this->categories[1]);

        return $this->catInit($pString);
    }
    /**
     * Add a subcategory
     */
    public function addSub()
    {
        if (!$input = $this->validateInput('addSub')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'subInit');
        }
        // database match is case insensitive.
        $this->db->formatConditions(['subcategorySubcategory' => $input['addSubcategory']]);
        $this->db->formatConditions(['subcategoryCategoryId' => $input['categoryId']]);
        $subcategoryId = $this->db->selectFirstField('subcategory', 'subcategoryId');
        $pString = $this->success->text("subcategoryAdd");
        // If subcategory already exists quietly return without error.
        if ($subcategoryId) {
            return $this->subInit($pString);
        }
        $fields[] = 'subcategorySubcategory';
        $values[] = $input['addSubcategory'];
        $fields[] = 'subcategoryCategoryId';
        $values[] = $input['categoryId'];
        $this->db->insert('subcategory', $fields, $values);

        return $this->subInit($pString);
    }
    /**
     * Ask for confirmation of delete categories
     */
    public function deleteCatConfirm()
    {
        if (!$input = $this->validateInput('deleteCat')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'catInit');
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "editCategory"));
        $categories = "'" . implode("', '", array_keys(array_intersect(array_flip($this->categories), $input))) . "'";
        $categories = html_entity_decode($categories);
        $pString = \HTML\p($this->messages->text("category", "deleteCatConfirm", ": $categories"));
        $pString .= \FORM\formHeader("admin_ADMINCATEGORIES_CORE");
        $pString .= \FORM\hidden("method", "deleteCat");
        $pString .= \FORM\hidden('categoryIds', base64_encode(serialize($input)));
        $pString .= BR . \FORM\formSubmit($this->messages->text("submit", "Confirm"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Ask for confirmation of delete subcategories
     */
    public function deleteSubCatConfirm()
    {
        if (!$input = $this->validateInput('deleteSub')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'subInit');
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "editSubcategory"));
        $this->db->formatConditionsOneField($input, 'subcategoryId');
        $recordset = $this->db->select('subcategory', 'subcategorySubCategory');
        while ($row = $this->db->fetchRow($recordset)) {
            $list[] = \HTML\nlToHtml($row['subcategorySubCategory']);
        }
        $subcategories = html_entity_decode(implode(', ', $list));
        $pString = \HTML\p($this->messages->text("category", "deleteSubConfirm", ": $subcategories"));
        $pString .= \FORM\formHeader("admin_ADMINCATEGORIES_CORE");
        $pString .= \FORM\hidden("method", "deleteSub");
        $pString .= \FORM\hidden('subcategoryIds', base64_encode(serialize($input)));
        $pString .= BR . \FORM\formSubmit($this->messages->text("submit", "Confirm"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete categories
     */
    public function deleteCat()
    {
        if (!$input = $this->validateInput('deleteCatConfirm')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'catInit');
        }
        // ensure that category 1 'General' is never deleted
        if ($key = array_search(1, $input)) {
            unset($input[$key]);
        }
        if (!$this->deleteSql($input)) {
            $this->badInput($this->errors->text("inputError", "invalid"), 'catInit');
        }
        $pString = $this->success->text("categoryDelete");
        $this->categories = $this->category->grabAll();
        unset($this->categories[1]);

        return $this->catInit($pString);
    }
    /**
     * Delete subcategories
     */
    public function deleteSub()
    {
        if (!$input = $this->validateInput('deleteSubConfirm')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'subInit');
        }
        if (!$this->deleteSubSql($input)) {
            $this->badInput($this->errors->text("inputError", "invalid"), 'subInit');
        }
        $pString = $this->success->text("subcategoryDelete");

        return $this->subInit($pString);
    }
    /**
     * Edit categories
     */
    public function editCat()
    {
        if (!$input = $this->validateInput('editCat')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'catInit');
        }
        $this->db->formatConditions(['categoryId' => $input['id']]);
        $categoryId = $this->db->select('category', 'categoryId');
        if (!$categoryId) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'catInit');
        }
        $update['categoryCategory'] = $input['text'];
        $this->db->formatConditions(['categoryId' => $input['id']]);
        $this->db->update('category', $update);
        $pString = $this->success->text("categoryEdit");
        $this->categories = $this->category->grabAll();
        unset($this->categories[1]);

        return $this->catInit($pString);
    }
    /**
     * Edit a subcategory
     */
    public function editSub()
    {
        if (!$input = $this->validateInput('editSub')) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'subInit');
        }
        $this->db->formatConditions(['subcategoryId' => $input['subcategoryEditId']]);
        $oldCatId = $this->db->selectFirstField('subcategory', 'subcategoryCategoryId');
        // Need to insert new rows to resource_category if the category has changed -- get resource Id from this subCategory
        if ($oldCatId != $input['categoryId']) {
            $this->db->formatConditions(['resourcecategorySubcategoryId' => $input['subcategoryEditId']]);
            $resultset = $this->db->select('resource_category', ['resourcecategoryCategoryId', 'resourcecategoryResourceId'], TRUE);
            while ($row = $this->db->fetchRow($resultset)) {
                $this->db->formatConditions(['resourcecategoryCategoryId' => $input['categoryId']]);
                $this->db->formatConditions(['resourcecategoryResourceId' => $row['resourcecategoryResourceId']]);
                if (!$this->db->numRows($this->db->select('resource_category', '*'))) {
                    $this->db->insert(
                        'resource_category',
                        ['resourcecategoryResourceId', 'resourcecategoryCategoryId'],
                        [$row['resourcecategoryResourceId'], $input['categoryId']]
                    );
                }
            }
        }
        $pString = $this->success->text("subcategoryEdit");
        $update['subcategorySubcategory'] = $input['subcategoryEdit'];
        $update['subcategoryCategoryId'] = $input['categoryId'];
        $this->db->formatConditions(['subcategoryId' => $input['subcategoryEditId']]);
        $this->db->update('subcategory', $update);

        return $this->subInit($pString);
    }
    /**
     * Delete category(s) from category table
     *
     * 'General' category always has id 1.
     *
     * @param array $input Array of ids in WKX_category
     *
     * @return true
     */
    private function deleteSql($input)
    {
        // Delete resource_category rows having the category IDs
        $this->db->formatConditionsOneField($input, 'resourcecategoryCategoryId');
        $this->db->delete('resource_category');
        // Find subcategories that are part of the categories and delete those rows from resource_category
        $this->db->formatConditionsOneField($input, 'subcategoryCategoryId');
        $recordset = $this->db->select('subcategory', 'subcategoryId');
        while ($row = $this->db->fetchRow($recordset)) {
            $subcatIds[] = $row['subcategoryId'];
        }
        if (isset($subcatIds)) {
            $this->db->formatConditionsOneField($subcatIds, 'resourcecategorySubcategoryId');
            $this->db->delete('resource_category');
        }
        // Ensure that all resources not listed in resource_category have at least the 'General' category
        $subStmt = $this->db->subQuery($this->db->selectNoExecute('resource_category', 'resourcecategoryResourceId'), FALSE, FALSE, TRUE);
        $this->db->formatConditions($this->db->formatFields('resourceId') . $this->db->inClause($subStmt, TRUE));
        $recordset = $this->db->select('resource', 'resourceId');
        while ($row = $this->db->fetchRow($recordset)) {
            $this->db->insert('resource_category', ['resourcecategoryResourceId', 'resourcecategoryCategoryId'], [$row['resourceId'], '1']);
        }
        // Finally delete categories and any subcategories
        foreach ($input as $value) {
            $this->db->formatConditions(['categoryId' => $value]);
            $this->db->delete('category');
            $this->db->formatConditions(['subcategoryCategoryId' => $value]);
            $this->db->delete('subcategory');
        }

        return TRUE;
    }
    /**
     * Delete subcategory(s) from subcategory table and from subcategories field in WKX_resource_category.
     *
     * @param array $input Array of ids in WKX_subcategory
     *
     * @return true
     */
    private function deleteSubSql($input)
    {
        // Delete resource_category rows having the subcategory IDs
        $this->db->formatConditionsOneField($input, 'resourcecategorySubcategoryId');
        $this->db->delete('resource_category');
        // Delete subcategories
        foreach ($input as $value) {
            $this->db->formatConditions(['subcategoryId' => $value]);
            $this->db->delete('subcategory');
        }

        return TRUE;
    }
    /**
     * validate input
     *
     * @param array $type
     *
     * @return array
     */
    private function validateInput($type)
    {
        if ($type == 'addCat') {
            if (!$input = trim($this->vars['categoryAdd'])) {
                return FALSE;
            }
        } elseif ($type == 'addSub') {
            if (!trim($this->vars['addSubcategory']) || !$this->vars['categoryId']) {
                return FALSE;
            }
            $input['addSubcategory'] = trim($this->vars['addSubcategory']);
            $input['categoryId'] = $this->vars['categoryId'];
        } elseif ($type == 'deleteCat') {
            if (!$this->vars['categoryIds']) {
                return FALSE;
            }
            $input = $this->vars['categoryIds'];
        } elseif ($type == 'deleteCatConfirm') {
            if (!$this->vars['categoryIds']) {
                return FALSE;
            }
            $input = unserialize(base64_decode($this->vars['categoryIds']));
        } elseif ($type == 'deleteSub') {
            if (!$this->vars['subcategoryIds']) {
                return FALSE;
            }
            $input = $this->vars['subcategoryIds'];
        } elseif ($type == 'deleteSubConfirm') {
            if (!$this->vars['subcategoryIds']) {
                return FALSE;
            }
            $input = unserialize(base64_decode($this->vars['subcategoryIds']));
        } elseif ($type == 'editCat') {
            if (!trim($this->vars['categoryEdit']) || !$this->vars['categoryEditId']) {
                return FALSE;
            }
            $input['text'] = trim($this->vars['categoryEdit']);
            $input['id'] = $this->vars['categoryEditId'];
        } elseif ($type == 'editSub') {
            if (!trim($this->vars['subcategoryEdit']) || !array_key_exists('subcategoryEditId', $this->vars) ||
                !array_key_exists('categoryId', $this->vars) || !$this->vars['subcategoryEditId'] || !$this->vars['categoryId']) {
                return FALSE;
            }
            $input['subcategoryEdit'] = trim($this->vars['subcategoryEdit']);
            $input['subcategoryEditId'] = $this->vars['subcategoryEditId'];
            $input['categoryId'] = $this->vars['categoryId'];
        }

        return $input;
    }
    /**
     * transferArrow
     *
     * @param string $function
     *
     * @return string
     */
    private function transferArrow($function)
    {
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => $function,
        ];
        $image = \AJAX\jActionIcon('toBottom', 'onclick', $jsonArray);

        return $image;
    }
}
