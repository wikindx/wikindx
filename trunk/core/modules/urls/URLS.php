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
 * URLS class
 */
class URLS
{
    private $db;
    private $vars;
    private $badInput;
    private $errors;
    private $messages;
    private $success;
    private $gatekeep;
    private $resourceId;
    private $formData = [];
    private $error = '';
    private $icons;
    private $browserTabID = FALSE;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->resourceId = $this->vars['resourceId'];
        $this->browserTabID = GLOBALS::getBrowserTabID();
    }
    /**
     * add, edit, delete resource attachments
     */
    public function init()
    {
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->gatekeep->init();
        if (!array_key_exists('resourceId', $this->vars) || !array_key_exists('function', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $function = $this->vars['function'];
        $this->resourceId = $this->vars['resourceId'];
        $this->{$function}();
    }
    /**
     * Initial editing/adding form
     *
     * @param string $message
     */
    public function editInit($message = FALSE)
    {
        $return = \HTML\a(
            $this->icons->getClass("edit"),
            $this->icons->getHTML("return"),
            'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&browserTabID=' . $this->browserTabID
        );
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "url", $this->messages->text('misc', 'edit') . '&nbsp;&nbsp;' . $return));
        $this->db->formatConditions(['resourceurlResourceId' => $this->resourceId]);
        $this->db->orderBy('resourceurlId');
        $recordset = $this->db->select('resource_url', ['resourceurlId', 'resourceurlUrl', 'resourceurlName', 'resourceurlPrimary']);
        if ($this->db->numRows($recordset))
        { // URLs exist for this resource
            GLOBALS::addTplVar('content', $this->urlEditForm($recordset, $message));
        }
        else
        { // add a new URL
            GLOBALS::addTplVar('content', $this->urlAddForm($message));
        }
    }
    /**
     * add a URL
     */
    private function add()
    {
        $this->validateInput('add');
        
        // Insert
        $fields[] = 'resourceurlResourceId';
        $values[] = $this->resourceId;
        
        $fields[] = 'resourceurlUrl';
        $values[] = $this->formData['url'];
        
        $fields[] = 'resourceurlName';
        $values[] = $this->formData['name'];
        
        $fields[] = 'resourceurlPrimary';
        $values[] = 1;
        
        $this->db->insert('resource_url', $fields, $values);
        
        // send back to view this resource with success message
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->resourceId, "urlAdd");
    }
    /**
     * edit urls
     */
    private function edit()
    {
        $this->validateInput();
// Add any new URL first
        if (array_key_exists('url', $this->formData))
        {
            // Insert
            $fields[] = 'resourceurlResourceId';
            $values[] = $this->resourceId;
            
            $fields[] = 'resourceurlUrl';
            $values[] = $this->formData['url'];
            
            $fields[] = 'resourceurlName';
            $values[] = array_key_exists('name', $this->formData) ? $this->formData['name'] : "";
            
            $fields[] = 'resourceurlPrimary';
            $values[] = $this->vars['primary'] == 'new' ? 1 : 0;
            
            $this->db->insert('resource_url', $fields, $values);
            unset($this->formData['url']);
            unset($this->formData['name']);
        }
        $deletes = [];
        foreach ($this->formData as $id => $array) {
        	$updateArray = [];
        	if (array_key_exists('delete', $array)) {
        		$deletes[] = $id;
        		continue;
        	}
			$updateArray['resourceurlUrl'] = $this->formData[$id]['url'];
			$updateArray['resourceurlName'] = array_key_exists('name', $this->formData[$id]) ? $this->formData[$id]['name'] : "";
			$updateArray['resourceurlPrimary'] = array_key_exists('primary', $this->formData[$id]) ? 1 : 0;
			$this->db->formatConditions(["resourceurlId" => $id]);
			$this->db->update('resource_url', $updateArray);
        }
        // Delete
        if (!empty($deletes))
        {
            $this->db->formatConditionsOneField($deletes, 'resourceurlId');
            $this->db->delete('resource_url');
        }

        $this->checkPrimaryUrl();
        
        // send back to view this resource with success message (deleteConfirm breaks out before this)
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->resourceId, 'urlEdit');
    }
    
    /**
     * Check there is a primary URL
     */
    private function checkPrimaryUrl()
    {
        // Find the previous first URL
        $this->db->formatConditions(["resourceurlResourceId" => $this->resourceId]);
        $this->db->formatConditions(["resourceurlPrimary" => 1]);
        $minArray = $this->db->selectMin('resource_url', 'resourceurlId');
        $resourceurlId = $minArray[0]['resourceurlId'];
        
        if ($resourceurlId == NULL)
        {
            // Find the first URL inserted
            $this->db->formatConditions(["resourceurlResourceId" => $this->resourceId]);
            $minArray = $this->db->selectMin('resource_url', 'resourceurlId');
            $resourceurlId = $minArray[0]['resourceurlId'];
            
            // Set the primary URL
            $updateArray['resourceurlPrimary'] = 1;
            $this->db->formatConditions(["resourceurlId" => $resourceurlId]);
            $this->db->formatConditions(["resourceurlResourceId" => $this->resourceId]);
            $this->db->update('resource_url', $updateArray);
        }
    }
    
    /**
     * Form for editing urls
     *
     * @param mixed $recordset
     * @param string $message
     *
     * @return string
     */
    private function urlEditForm($recordset, $message = FALSE)
    {
        while ($row = $this->db->fetchRow($recordset)) {
            $urls[$row['resourceurlId']] = ['url' => $row['resourceurlUrl'], 
            	'name' => $row['resourceurlName'], 'primary' => $row['resourceurlPrimary']];
        }
        
        $pString = $message;
        $pString .= \FORM\formHeader("urls_URLS_CORE");
        $pString .= \FORM\hidden('function', 'edit');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        $pString .= \FORM\hidden("browserTabID", $this->browserTabID);
        
        // Form elements for adding another URL
        $pString .= \HTML\tableStart('left');
        
        $pString .= \HTML\trStart();
            $pString .= \HTML\th($this->messages->text("resources", "url"));
            $pString .= \HTML\th($this->messages->text("resources", "urlLabel"));
            $pString .= \HTML\th($this->messages->text('resources', 'primaryUrl'));
            $pString .= \HTML\th($this->messages->text('misc', 'delete'));
        $pString .= \HTML\trEnd();
        
        $pString .= \HTML\trStart();
            $field = array_key_exists('url', $this->formData) ? $this->formData['url'] : WIKINDX_RESOURCE_URL_PREFIX;
            $pString .= \HTML\td(\FORM\textInput($this->messages->text("misc", "add"), "url", $field, 70), 'left bottom');
            $field = array_key_exists('name', $this->formData) ? $this->formData['name'] : FALSE;
            $pString .= \HTML\td(\FORM\textInput(FALSE, "name", $field, 50), 'left bottom');
            $pString .= \HTML\td(\FORM\radioButton(FALSE, 'primary', 'new'), 'left bottom');
            $pString .= \HTML\td("&nbsp;");
        $pString .= \HTML\trEnd();
        
        foreach ($urls as $id => $array)
        {
            $tdId = \FORM\hidden("editId_$id", $id);
            
            $field = array_key_exists($id, $this->formData) && array_key_exists('url', $this->formData[$id]) ? 
            	$this->formData[$id]['url'] : $array['url'];
            $tdUrl = \FORM\textInput($this->messages->text("misc", "edit"), "editUrl_$id", $field, 70);
            
            $field = array_key_exists($id, $this->formData) && array_key_exists('names', $this->formData[$id]) ? 
            	$this->formData[$id]['name'] : \HTML\dbToFormTidy($array['name']);
            $tdName = \FORM\textInput(FALSE, "editName_$id", $field, 50);
            
            $checked = array_key_exists($id, $this->formData) && array_key_exists('delete', $this->formData[$id]) ? TRUE : FALSE;
            $tdDelete = \FORM\checkBox( FALSE, "delete_$id", $checked);
            
            $tdUrlPrimary = "";
			if (empty($this->formData))
			{
				if ($array['primary'])
				{
					$tdUrlPrimary .= \FORM\radioButton(FALSE, 'primary', $id, TRUE);
				}
				else
				{
					$tdUrlPrimary .= \FORM\radioButton(FALSE, 'primary', $id);
				}
			}
			else
			{
				if (array_key_exists('primary', $array))
				{
					$tdUrlPrimary .= \FORM\radioButton(FALSE, 'primary', $id, TRUE);
				}
				else
				{
					$tdUrlPrimary .= \FORM\radioButton(FALSE, 'primary', $id);
				}
			}
            
            $pString .= \HTML\trStart();
                $pString .= \HTML\td($tdId . $tdUrl, 'left bottom');
                $pString .= \HTML\td($tdName, 'left bottom');
                $pString .= \HTML\td($tdUrlPrimary, 'left bottom');
                $pString .= \HTML\td($tdDelete, 'left bottom');
            $pString .= \HTML\trEnd();
        }
        
        $pString .= \HTML\tableEnd();
        
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));
        
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * Form for adding a URL
     *
     * @param string $message
     *
     * @return string
     */
    private function urlAddForm($message = FALSE)
    {
        $pString = $message;
        $pString .= \FORM\formHeader("urls_URLS_CORE");
        
        $pString .= \FORM\hidden('function', 'add');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        $pString .= \FORM\hidden("browserTabID", $this->browserTabID);
        
        $pString .= \HTML\tableStart('left');
        
            $pString .= \HTML\trStart();
                $pString .= \HTML\td($this->messages->text("resources", "url") . ": " .
                    \FORM\textInput(FALSE, "url", WIKINDX_RESOURCE_URL_PREFIX, 70), 'left bottom');
                $field = array_key_exists('name', $this->formData) ? $this->formData['name'] : FALSE;
                $pString .= \HTML\td($this->messages->text("resources", "urlLabel") . ": " .
                    \FORM\textInput(FALSE, "name", $field, 50), 'left bottom');
            $pString .= \HTML\trEnd();
            
        $pString .= \HTML\tableEnd();
        
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));
        
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * Validate input
     *
     * @param string $type
     */
    private function validateInput($type = 'edit')
    {
        $error = '';
        // If both url and name fields are empty, we are just editing existing urls as in edit(). If a name exists, so must the url.
        if (\UTF8\mb_trim($this->vars['url']))
        {
            $this->formData['url'] = \UTF8\mb_trim($this->vars['url']);
        }
        if (\UTF8\mb_trim($this->vars['name']))
        {
            $this->formData['name'] = \UTF8\mb_trim($this->vars['name']);
            if (!array_key_exists('url', $this->formData))
            {
                $error = $this->errors->text("inputError", "missing");
            }
        }
        if (($type == 'add') && !array_key_exists('url', $this->formData))
        {
            $error = $this->errors->text("inputError", "missing");
        }
// Get IDs first
        foreach ($this->vars as $key => $var)
        {
            $split = \UTF8\mb_explode('_', $key);
            if (($split[0] == 'editId')) {
            	$this->formData[$split[1]]['id'] = $split[1];
            }
        }
        // find any URLs to edit and files to delete
        foreach ($this->vars as $key => $var)
        {
            $delete = $name = $url = FALSE;
            $split = \UTF8\mb_explode('_', $key);
            if (($split[0] == 'editUrl'))
            {
                if (trim($var))
                {
                    $this->formData[$split[1]]['url'] = trim($var);
                    if ($this->vars['primary'] == $split[1]) {
                    	$this->formData[$split[1]]['primary'] = TRUE;
                    }
                }
            }
            else if (($split[0] == 'editName') && $var)
            {
                if (trim($var))
                {
                	$this->formData[$split[1]]['name'] = trim($var);
                }
            }
            else if ($split[0] == 'delete')
            {
                $this->formData[$split[1]]['delete'] = TRUE;
            }
        }
		foreach ($this->formData as $id => $array)
		{
			if (array_key_exists('name', $array) && !array_key_exists('url', $array))
			{
				$error = $this->errors->text("inputError", "missing");
			}
		}
        if ($error)
        {
            $this->badInput->close($error, $this, 'editInit');
        }
    }
}
