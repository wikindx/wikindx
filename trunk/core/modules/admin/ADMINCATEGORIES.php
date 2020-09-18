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
    private $formData = [];

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
        if (array_key_exists('message', $this->vars)) {
        	$message = $this->vars['message'];
        }
        $pString = $message;
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
            $jsonArray = [];
			$jsonArray[] = [
				'startFunction' => 'transferCategory',
			];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
			foreach ($categories as $categoryEditId => $initialCategory) {
				break;
			}
            $td .= \FORM\selectFBoxValue(
                $this->messages->text("category", "editCategory"),
                'categoryId',
                $categories,
                10,
                FALSE,
                $js
            );
            $td .= \HTML\p(\FORM\textInput(FALSE, "categoryEdit", $initialCategory, 30, 255));
            $split = explode('_', $categoryEditId);
            $categoryEditId = $split[0];
            $td .= \FORM\hidden('categoryEditId', $categoryEditId);
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
            ) . BR . \HTML\span(\HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", 
            	$this->messages->text("hint", "hashFile")), 'hint');
            $td .= \HTML\p($this->messages->text("category", "deleteWarning"));
            $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Delete")));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td, 'generalTable borderStyleSolid left');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        \AJAX\loadJavascript([WIKINDX_URL_BASE . '/core/modules/admin/categoryEdit.js?ver=' . WIKINDX_PUBLIC_VERSION]);
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
        if (array_key_exists('message', $this->vars)) {
        	$message = $this->vars['message'];
        }
        $pString = $message;
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
			$jsonArray[] = [
				'startFunction' => 'transferSubcategory',
			];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
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
            if (array_key_exists('categoryId', $this->formData)) {
            	$selected = $this->formData['categoryId'];
        	} else {
				$subcategoryIdcond = array_keys($subcategories);
				$subcategoryIdcond = array_shift($subcategoryIdcond);
				$subcategoryIdcond = ['subcategoryId' => $subcategoryIdcond];
				$this->db->formatConditions($subcategoryIdcond);
				$selected = $this->db->selectFirstField('subcategory', 'subcategoryCategoryId');
			}
            $td1 .= \HTML\td(\HTML\div('categoryIdDiv', \FORM\selectedBoxValue(
                $this->messages->text('resources', 'subcategoryPart'),
                'categoryId',
                $categories,
                $selected,
                10
            )));
			foreach ($subcategories as $subcategoryEditId => $initialSubcategory) {
				break;
			}
            $split = explode('_', $subcategoryEditId);
            $subcategoryEditId = $split[0];
            $td2 = \HTML\p(\FORM\textInput(FALSE, "subcategoryEdit", $initialSubcategory, 30, 255));
            $td2 .= \FORM\hidden('subcategoryEditId', $subcategoryEditId);
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
            ) . BR . \HTML\span(\HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", 
            	$this->messages->text("hint", "hashFile")), 'hint');
            $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Delete")));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td, 'generalTable borderStyleSolid left');
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        \AJAX\loadJavascript([WIKINDX_URL_BASE . '/core/modules/admin/categoryEdit.js?ver=' . WIKINDX_PUBLIC_VERSION]);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Amend category select box depending on selection in subcategory select box
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
        $this->validateInput('addCat');
        // database match is case insensitive.
        $this->db->formatConditions(['categoryCategory' => $this->formData['categoryAdd']]);
        $categoryId = $this->db->selectFirstField('category', 'categoryId');
    	$message = rawurlencode($this->success->text("categoryAdd"));
        // If category already exists quietly return without error.
        if ($categoryId) {
			header("Location: index.php?action=admin_ADMINCATEGORIES_CORE&method=catInit&message=$message");
			die;
        }
        $this->db->insert('category', 'categoryCategory', $this->formData);
    	header("Location: index.php?action=admin_ADMINCATEGORIES_CORE&method=catInit&message=$message");
    	die;
    }
    /**
     * Add a subcategory
     */
    public function addSub()
    {
        $this->validateInput('addSub');
        // database match is case insensitive.
        $this->db->formatConditions(['subcategorySubcategory' => $this->formData['addSubcategory']]);
        $this->db->formatConditions(['subcategoryCategoryId' => $this->formData['categoryId']]);
        $subcategoryId = $this->db->selectFirstField('subcategory', 'subcategoryId');
    	$message = rawurlencode($this->success->text("subcategoryAdd"));
        // If subcategory already exists quietly return without error.
        if ($subcategoryId) {
			header("Location: index.php?action=admin_ADMINCATEGORIES_CORE&method=subInit&message=$message");
			die;
        }
        $fields[] = 'subcategorySubcategory';
        $values[] = $this->formData['addSubcategory'];
        $fields[] = 'subcategoryCategoryId';
        $values[] = $this->formData['categoryId'];
        $this->db->insert('subcategory', $fields, $values);
    	header("Location: index.php?action=admin_ADMINCATEGORIES_CORE&method=subInit&message=$message");
    	die;
    }
    /**
     * Ask for confirmation of delete categories
     */
    public function deleteCatConfirm()
    {
        $this->validateInput('deleteCat');
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "editCategory"));
        $categories = "'" . implode("', '", array_keys(array_intersect(array_flip($this->categories), $this->formData['categoryIds']))) . "'";
        $categories = html_entity_decode($categories);
        $pString = \HTML\p($this->messages->text("category", "deleteCatConfirm", ": $categories"));
        $pString .= \FORM\formHeader("admin_ADMINCATEGORIES_CORE");
        $pString .= \FORM\hidden("method", "deleteCat");
        $pString .= \FORM\hidden('categoryIds', base64_encode(serialize($this->formData['categoryIds'])));
        $pString .= BR . \FORM\formSubmit($this->messages->text("submit", "Confirm"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Ask for confirmation of delete subcategories
     */
    public function deleteSubCatConfirm()
    {
        $this->validateInput('deleteSub');
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "editSubcategory"));
        $this->db->formatConditionsOneField($this->formData['subcategoryIds'], 'subcategoryId');
        $recordset = $this->db->select('subcategory', 'subcategorySubCategory');
        while ($row = $this->db->fetchRow($recordset)) {
            $list[] = \HTML\nlToHtml($row['subcategorySubCategory']);
        }
        $subcategories = html_entity_decode(implode(', ', $list));
        $pString = \HTML\p($this->messages->text("category", "deleteSubConfirm", ": $subcategories"));
        $pString .= \FORM\formHeader("admin_ADMINCATEGORIES_CORE");
        $pString .= \FORM\hidden("method", "deleteSub");
        $pString .= \FORM\hidden('subcategoryIds', base64_encode(serialize($this->formData['subcategoryIds'])));
        $pString .= BR . \FORM\formSubmit($this->messages->text("submit", "Confirm"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete categories
     */
    public function deleteCat()
    {
        $this->validateInput('deleteCatConfirm');
        // ensure that category 1 'General' is never deleted
        if ($key = array_search(1, $this->formData['categoryIds'])) {
            unset($this->formData['categoryIds'][$key]);
        }
        $this->deleteSql($this->formData['categoryIds']);
    	$message = rawurlencode($this->success->text("categoryDelete"));
		header("Location: index.php?action=admin_ADMINCATEGORIES_CORE&method=catInit&message=$message");
		die;
    }
    /**
     * Delete subcategories
     */
    public function deleteSub()
    {
        $this->validateInput('deleteSubConfirm');
        if (!$this->deleteSubSql($this->formData['subcategoryIds'])) {
            $this->badInput($this->errors->text("inputError", "invalid"), 'subInit');
        }
    	$message = rawurlencode($this->success->text("subcategoryDelete"));
		header("Location: index.php?action=admin_ADMINCATEGORIES_CORE&method=subInit&message=$message");
		die;
    }
    /**
     * Edit categories
     */
    public function editCat()
    {
        $this->validateInput('editCat');
        $this->db->formatConditions(['categoryId' => $this->formData['id']]);
        $categoryId = $this->db->select('category', 'categoryId');
        if (!$categoryId) {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'catInit');
        }
        $update['categoryCategory'] = $this->formData['text'];
        $this->db->formatConditions(['categoryId' => $this->formData['id']]);
        $this->db->update('category', $update);
    	$message = rawurlencode($this->success->text("categoryEdit"));
		header("Location: index.php?action=admin_ADMINCATEGORIES_CORE&method=catInit&message=$message");
		die;
    }
    /**
     * Edit a subcategory
     */
    public function editSub()
    {
        $this->validateInput('editSub');
        $this->db->formatConditions(['subcategoryId' => $this->formData['subcategoryEditId']]);
        $oldCatId = $this->db->selectFirstField('subcategory', 'subcategoryCategoryId');
        // Need to insert new rows to resource_category if the category has changed -- get resource Id from this subCategory
        if ($oldCatId != $this->formData['categoryId']) {
            $this->db->formatConditions(['resourcecategorySubcategoryId' => $this->formData['subcategoryEditId']]);
            $resultset = $this->db->select('resource_category', ['resourcecategoryCategoryId', 'resourcecategoryResourceId'], TRUE);
            while ($row = $this->db->fetchRow($resultset)) {
                $this->db->formatConditions(['resourcecategoryCategoryId' => $this->formData['categoryId']]);
                $this->db->formatConditions(['resourcecategoryResourceId' => $row['resourcecategoryResourceId']]);
                if (!$this->db->numRows($this->db->select('resource_category', '*'))) {
                    $this->db->insert(
                        'resource_category',
                        ['resourcecategoryResourceId', 'resourcecategoryCategoryId'],
                        [$row['resourcecategoryResourceId'], $this->formData['categoryId']]
                    );
                }
            }
        }
        $update['subcategorySubcategory'] = $this->formData['subcategoryEdit'];
        $update['subcategoryCategoryId'] = $this->formData['categoryId'];
        $this->db->formatConditions(['subcategoryId' => $this->formData['subcategoryEditId']]);
        $this->db->update('subcategory', $update);
    	$message = rawurlencode($this->success->text("subcategoryEdit"));
		header("Location: index.php?action=admin_ADMINCATEGORIES_CORE&method=subInit&message=$message");
		die;
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
     */
    private function validateInput($type)
    {
    	$error = '';
        if ($type == 'addCat') {
            if (!trim($this->vars['categoryAdd'])) {
            	$error = $this->errors->text("inputError", "missing");
            	$function = 'catInit';
            }
            $this->formData['categoryAdd'] = trim($this->vars['categoryAdd']);
        } elseif ($type == 'addSub') {
            if (!trim($this->vars['addSubcategory']) || !$this->vars['categoryId']) {
            	$error = $this->errors->text("inputError", "missing");
            	$function = 'subInit';
            }
            $this->formData['addSubcategory'] = trim($this->vars['addSubcategory']);
            $this->formData['categoryId'] = $this->vars['categoryId'];
        } elseif ($type == 'deleteCat') {
            if (!array_key_exists('categoryIds', $this->vars) || !$this->vars['categoryIds']) {
            	$error = $this->errors->text("inputError", "missing");
            	$function = 'catInit';
            }
            if (array_key_exists('categoryIds', $this->vars)) {
	            $this->formData['categoryIds'] = $this->vars['categoryIds'];
	        }
        } elseif ($type == 'deleteCatConfirm') {
            if (!array_key_exists('categoryIds', $this->vars) || !$this->vars['categoryIds']) {
            	$error = $this->errors->text("inputError", "missing");
            	$function = 'catInit';
            }
            if (array_key_exists('categoryIds', $this->vars)) {
            	$this->formData['categoryIds'] = unserialize(base64_decode($this->vars['categoryIds']));
            }
        } elseif ($type == 'deleteSub') {
            if (!array_key_exists('subcategoryIds', $this->vars) || !$this->vars['subcategoryIds']) {
            	$error = $this->errors->text("inputError", "missing");
            	$function = 'subInit';
            }
            if (array_key_exists('subcategoryIds', $this->vars)) {
            	$this->formData['subcategoryIds'] = $this->vars['subcategoryIds'];
            }
        } elseif ($type == 'deleteSubConfirm') {
            if (!array_key_exists('subcategoryIds', $this->vars) || !$this->vars['subcategoryIds']) {
            	$error = $this->errors->text("inputError", "missing");
            	$function = 'subInit';
            }
            if (array_key_exists('subcategoryIds', $this->vars)) {
            	$this->formData['subcategoryIds'] = unserialize(base64_decode($this->vars['subcategoryIds']));
            }
        } elseif ($type == 'editCat') {
            if (!trim($this->vars['categoryEdit']) || !array_key_exists('categoryEditId', $this->vars)) {
            	$error = $this->errors->text("inputError", "missing");
            	$function = 'catInit';
            }
            $this->formData['text'] = trim($this->vars['categoryEdit']);
            if (array_key_exists('categoryEditId', $this->vars)) {
            	$this->formData['id'] = $this->vars['categoryEditId'];
            }
        } elseif ($type == 'editSub') {
            if (!trim($this->vars['subcategoryEdit']) || !array_key_exists('subcategoryEditId', $this->vars) ||
                !array_key_exists('categoryId', $this->vars) || !$this->vars['subcategoryEditId'] || !$this->vars['categoryId']) {
            	$error = $this->errors->text("inputError", "missing");
            	$function = 'subInit';
            }
            $this->formData['subcategoryEdit'] = trim($this->vars['subcategoryEdit']);
            if (array_key_exists('subcategoryEditId', $this->vars)) {
	            $this->formData['subcategoryEditId'] = $this->vars['subcategoryEditId'];
	        }
            if (array_key_exists('categoryId', $this->vars)) {
	            $this->formData['categoryId'] = $this->vars['categoryId'];
	        }
        }
        if ($error) {
            $this->badInput->close($error, $this, $function);
        }
    }
}
