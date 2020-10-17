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
 *	ADMINCUSTOM class.
 *
 *	Administration of custom fields
 */
class ADMINCUSTOM
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $gatekeep;
    private $badInput;
    private $formData = [];

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->gatekeep->init();
    }
    /**
     * display options
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminCustom"));
    	if (array_key_exists('message', $this->vars)) {
    		$message = $this->vars['message'];
    	}
        $pString = $message;
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        // Add
        $td = \FORM\formHeader("admin_ADMINCUSTOM_CORE");
        $td .= \FORM\hidden("method", "addCustom");
        $array = ["small" => $this->messages->text("custom", "small"),
            "large" => $this->messages->text("custom", "large"), ];
        if (array_key_exists('size', $this->formData)) {
        	$size = $this->formData['size'];
        } else {
        	$size = 'small';
        }
        $td .= \HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("custom", "size"),
            "custom_size",
            $array,
            $size,
            2
        );
        $label = array_key_exists('label', $this->formData) ? $this->formData['label'] : FALSE;
        $td .= \HTML\p(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("custom", "addLabel"),
            "custom_label",
            $label,
            50,
            255
        ));
        $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")));
        $td .= \FORM\formEnd();
        $pString .= \HTML\td($td);
        $customs = [];
        $recordset = $this->db->select('custom', ['customId', 'customLabel']);
        while ($row = $this->db->fetchRow($recordset)) {
            $customs[$row['customId']] = \HTML\dbToFormTidy($row['customLabel']);
        }
        if (!empty($customs)) {
            // Edit
            // If preferences reduce long custom labels, we want to transfer the original rather than the condensed version.
            // Store the base64-encoded value for retrieval in the javascript.
            foreach ($customs as $key => $value) {
                $key = $key . '_' . base64_encode($value);
                $fields[$key] = $value;
            }
			if (array_key_exists('id', $this->formData)) { // thus missing label so get the original
				$label = base64_encode($customs[$this->formData['id']]);
				$id = $this->formData['id'] . '_' . $label;
				$hiddenId = $this->formData['id'];
				$initialCustom = $customs[$this->formData['id']];
			}
			else {
	            foreach ($customs as $id => $initialCustom) {
    	        	break;
        	    }
        	    $hiddenId = $id;
        	}
            $td = \FORM\formHeader("admin_ADMINCUSTOM_CORE");
            $td .= \FORM\hidden("method", "editCustom");
            $jsonArray = [];
			$jsonArray[] = [
				'startFunction' => 'transferCustom',
			];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
			$td .= \FORM\selectedBoxValue(
				$this->messages->text("custom", "editLabel"),
				'customId',
				$fields,
				$id,
				10,
				FALSE,
				$js
			);
            $td .= \HTML\p(\FORM\textInput(FALSE, "customEdit", $initialCustom, 30, 255));
            $td .= \FORM\hidden('customEditId', $hiddenId);
            $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td);
            // Delete
            $td = \FORM\formHeader("admin_ADMINCUSTOM_CORE");
            $td .= \FORM\hidden("method", "deleteConfirm");
            $td .= \FORM\selectFBoxValueMultiple(
                $this->messages->text("custom", "deleteLabel"),
                'customIds',
                $customs,
                10
            ) . BR . \HTML\span(\HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", 
            	$this->messages->text("hint", "multiples")), 'hint');
            $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Delete")));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td);
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        \AJAX\loadJavascript([WIKINDX_URL_BASE . '/core/modules/admin/customEdit.js?ver=' . WIKINDX_PUBLIC_VERSION]);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Create the new field
     */
    public function addCustom()
    {
        $this->validateInput('add');
        $fields = ["customLabel", "customSize"];
        $values[] = trim($this->formData['label']);
        if ($this->formData['size'] == 'large') {
            $values[] = 'L';
        } else {
            $values[] = 'S';
        }
        $this->db->insert('custom', $fields, $values);
        $message = rawurlencode($this->success->text("fieldAdd"));
        header("Location: index.php?action=admin_ADMINCUSTOM_CORE&method=init&message=$message");
        die;
    }
    /**
     * Edit field
     */
    public function editCustom()
    {
        $this->validateInput('edit');
        $split = explode('_', $this->formData['id']);
        $id = $split[0];
        $this->db->formatConditions(['customId' => $id]);
        $this->db->update('custom', ["customLabel" => $this->formData['label']]);
        $message = rawurlencode($this->success->text("fieldEdit"));
        header("Location: index.php?action=admin_ADMINCUSTOM_CORE&method=init&message=$message");
        die;
    }
    /**
     * Ask for confirmation of delete field(s)
     */
    public function deleteConfirm()
    {
        $this->validateInput('delete');
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminCustom"));
        $pString = \HTML\p(\HTML\strong($this->messages->text("custom", "warning")));
        $pString .= \FORM\formHeader('admin_ADMINCUSTOM_CORE');
        $pString .= \FORM\hidden('method', 'deleteCustom');
        foreach ($this->formData['ids'] as $id) {
            $pString .= \FORM\hidden("delete_" . $id, $id);
            $ids[] = $this->db->tidyInput($id);
        }
        $this->db->formatConditions($this->db->formatFields('customId') . $this->db->equal .
            implode($this->db->or . $this->db->formatFields('customId') . $this->db->equal, $ids));
        $recordset = $this->db->select('custom', 'customLabel');
        while ($row = $this->db->fetchRow($recordset)) {
            $fieldValues[] = "'" . \HTML\nlToHtml($row['customLabel']) . "'";
        }
        $pString .= \HTML\p($this->messages->text("custom", "deleteConfirm", ": " . implode(", ", $fieldValues)));
        $pString .= BR . \FORM\formSubmit($this->messages->text("submit", "Confirm"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete field(s)
     */
    public function deleteCustom()
    {
        $this->validateInput('deleteConfirm');
        $this->db->formatConditionsOneField($this->formData['ids'], 'customId');
        $this->db->delete('custom');
        $this->db->formatConditionsOneField($this->formData['ids'], 'resourcecustomCustomId');
        $this->db->delete('resource_custom');
        $message = rawurlencode($this->success->text("fieldDelete"));
        header("Location: index.php?action=admin_ADMINCUSTOM_CORE&method=init&message=$message");
        die;
    }
    /**
     * validate input
     *
     * @param string $type
     *
     * @return bool|int
     */
    private function validateInput($type)
    {
    	$error = '';
        if ($type == 'add') {
        	if (!array_key_exists('custom_label', $this->vars) || !\UTF8\mb_trim($this->vars['custom_label'])) {
        		$error = $this->errors->text("inputError", "missing");
        	}
        	else {
            	$this->db->formatConditions($this->db->lower('customLabel') . 
            		$this->db->like(FALSE, mb_strtolower(\UTF8\mb_trim($this->vars['custom_label'])), FALSE));
				$recordset = $this->db->select('custom', ['customLabel']);
				if ($this->db->numRows($recordset)) {
					$error = $this->errors->text("inputError", "labelExists");
				}
			}
            $this->formData['size'] = $this->vars['custom_size'];
            $this->formData['label'] = \UTF8\mb_trim($this->vars['custom_label']);
        } elseif ($type == 'edit') {
        	if (!array_key_exists('customEdit', $this->vars) || !\UTF8\mb_trim($this->vars['customEdit'])) {
        		$error = $this->errors->text("inputError", "missing");
        	}
        	else {
            	$this->db->formatConditions($this->db->lower('customLabel') . 
            		$this->db->like(FALSE, mb_strtolower(\UTF8\mb_trim($this->vars['customEdit'])), FALSE));
				$recordset = $this->db->select('custom', ['customId', 'customLabel']);
				if ($this->db->numRows($recordset)) {
					$row = $this->db->fetchRow($recordset);
					if ($row['customId'] != $this->vars['customEditId']) {
						$error = $this->errors->text("inputError", "labelExists");
					}
				}
			}
			$this->formData['label'] = \UTF8\mb_trim($this->vars['customEdit']);
			$this->formData['id'] = \UTF8\mb_trim($this->vars['customEditId']);
        } elseif ($type == 'delete') {
            if (!array_key_exists('customIds', $this->vars) || empty($this->vars['customIds'])) {
        		$error = $this->errors->text("inputError", "missing");
            } else {
            	$this->formData['ids'] = $this->vars['customIds'];
            }
        } elseif ($type == 'deleteConfirm') {
            foreach ($this->vars as $key => $value) {
                if (!preg_match("/delete_/u", $key)) {
                    continue;
                }
                $this->formData['ids'][] = $value;
            }
            if (empty($this->formData['ids'])) {
                $error = $this->errors->text("inputError", "missing");
            }
        }
        if ($error) {
        	$this->badInput->close($error, $this, 'init');
        }
    }
}
