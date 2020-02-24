<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
    private $session;
    private $gatekeep;
    private $badInput;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();


        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();

        $this->gatekeep->init();
        $this->session->clearArray('custom');
    }
    /**
     * display options
     *
     * @param string|FALSE $message
     */
    public function init($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminCustom"));
        $pString = $message ? \HTML\p($message, "error", "center") : FALSE;
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        // Add
        $td = \FORM\formHeader("admin_ADMINCUSTOM_CORE");
        $td .= \FORM\hidden("method", "addCustom");
        $array = ["small" => $this->messages->text("custom", "small"),
            "large" => $this->messages->text("custom", "large"), ];
        if (!$size = $this->session->getVar("custom_size"))
        {
            $size = "small";
        }
        $td .= \FORM\selectedBoxValue(
            $this->messages->text("custom", "size"),
            "custom_size",
            $array,
            $size,
            2
        ) . " " . \HTML\span('*', 'required');
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text("custom", "addLabel"),
            "custom_label",
            FALSE,
            50,
            255
        ) . " " . \HTML\span('*', 'required'));
        $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")));
        $td .= \FORM\formEnd();
        $pString .= \HTML\td($td);
        $recordset = $this->db->select('custom', ['customId', 'customLabel']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $customs[$row['customId']] = \HTML\dbToFormTidy($row['customLabel']);
        }
        if (isset($customs))
        {
            // Edit
            // If preferences reduce long custom labels, we want to transfer the original rather than the condensed version.
            // Store the base64-encoded value for retrieval in the javascript.
            foreach ($customs as $key => $value)
            {
                $key = $key . '_' . base64_encode($value);
                $fields[$key] = $value;
            }
            $td = \FORM\formHeader("admin_ADMINCUSTOM_CORE");
            $td .= \FORM\hidden("method", "editCustom");
            $td .= \FORM\selectFBoxValue(
                $this->messages->text("custom", "editLabel"),
                'customId',
                $fields,
                10
            );
            $td .= \HTML\p($this->transferArrow('transferCustom'));
            $td .= \HTML\p(\FORM\textInput(FALSE, "customEdit", FALSE, 30, 255));
            $td .= \FORM\hidden('customEditId', FALSE);
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
            ) . BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint');
            $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Delete")));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td);
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        \AJAX\loadJavascript([WIKINDX_BASE_URL . '/core/modules/admin/customEdit.js']);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Create the new field
     */
    public function addCustom()
    {
        if (!$this->validateInput('add'))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $fields = ["customLabel", "customSize"];
        $values[] = trim($this->label);
        if ($this->size == 'large')
        {
            $values[] = 'L';
        }
        else
        {
            $values[] = 'S';
        }
        $this->db->insert('custom', $fields, $values);
        $pString = $this->success->text("fieldAdd");

        return $this->init($pString);
    }
    /**
     * Edit field
     */
    public function editCustom()
    {
        if (!$this->validateInput('edit'))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $this->db->formatConditions(['customId' => $this->vars['customEditId']]);
        $this->db->update('custom', ["customLabel" => $this->label]);
        $pString = $this->success->text("fieldEdit");

        return $this->init($pString);
    }
    /**
     * Ask for confirmation of delete field(s)
     */
    public function deleteConfirm()
    {
        if (!$this->validateInput('delete'))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "adminCustom"));
        $pString = \HTML\p(\HTML\strong($this->messages->text("custom", "warning")));
        $pString .= \FORM\formHeader('admin_ADMINCUSTOM_CORE');
        $pString .= \FORM\hidden('method', 'deleteCustom');
        foreach ($this->vars['customIds'] as $id)
        {
            $pString .= \FORM\hidden("delete_" . $id, $id);
            $ids[] = $this->db->tidyInput($id);
        }
        $this->db->formatConditions($this->db->formatFields('customId') . $this->db->equal .
            implode($this->db->or . $this->db->formatFields('customId') . $this->db->equal, $ids));
        $recordset = $this->db->select('custom', 'customLabel');
        while ($row = $this->db->fetchRow($recordset))
        {
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
        if (!$ids = $this->validateInput('deleteConfirm'))
        {
            $this->badInput($this->errors->text("inputError", "invalid"), 'init');
        }
        // $ids is an array of field IDs
        $this->db->formatConditions($this->db->formatFields('customId') . $this->db->equal .
            implode($this->db->or . $this->db->formatFields('customId') . $this->db->equal, $ids));
        $this->db->delete('custom');
        $this->db->formatConditions($this->db->formatFields('resourcecustomId') . $this->db->equal .
            implode($this->db->or . $this->db->formatFields('resourcecustomId') . $this->db->equal, $ids));
        $this->db->delete('resource_custom');
        $pString = $this->success->text("fieldDelete");

        return $this->init($pString);
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
    /**
     * validate input
     *
     * @param string $type
     *
     * @return int|bool
     */
    private function validateInput($type)
    {
        if ($type == 'add')
        {
            // Write to session
            $this->size = isset($this->vars['custom_size']) ? $this->vars['custom_size'] : FALSE;
            $this->label = isset($this->vars['custom_label']) ? trim($this->vars['custom_label']) : FALSE;
            $this->session->setVar("custom_size", $this->size);
            $this->session->setVar("custom_label", $this->label);
            if (!$this->size)
            {
                return FALSE;
            }
            if (!$this->label)
            {
                return FALSE;
            }
        }
        elseif ($type == 'edit')
        {
            $this->label = isset($this->vars['customEdit']) ? trim($this->vars['customEdit']) : FALSE;
            if (!array_key_exists('customEditId', $this->vars) || !$this->label)
            {
                return FALSE;
            }
        }
        elseif ($type == 'delete')
        {
            if (!array_key_exists('customIds', $this->vars) || empty($this->vars['customIds']))
            {
                return FALSE;
            }
        }
        elseif ($type == 'deleteConfirm')
        {
            $ids = [];
            foreach ($this->vars as $key => $value)
            {
                if (!preg_match("/delete_/u", $key))
                {
                    continue;
                }
                $ids[] = $value;
            }
            if (empty($ids))
            {
                return FALSE;
            }
            else
            {
                return $ids;
            }
        }

        return TRUE;
    }
}
