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
        if (array_key_exists('ids', $this->formData))
        {
            foreach ($this->formData['ids'] as $key => $var)
            {
                $split = \UTF8\mb_explode('_', $key);
                if ($split[0] == 'urlEditId')
                {
                    $editIds[$split[1]] = $var;
                }
            }
        }
        if (array_key_exists('links', $this->formData))
        {
            foreach ($this->formData['links'] as $key => $var)
            {
                $split = \UTF8\mb_explode('_', $key);
                if ($split[0] == 'urlEditLink')
                {
                    $editLinks[$split[1]] = $var;
                }
            }
        }
        if (array_key_exists('names', $this->formData))
        {
            foreach ($this->formData['names'] as $key => $var)
            {
                $split = \UTF8\mb_explode('_', $key);
                if ($split[0] == 'urlEditName')
                {
                    $editNames[$split[1]] = $var;
                }
            }
        }
        if (array_key_exists('ids', $this->formData))
        {
            foreach ($this->formData['ids'] as $key => $var)
            {
                $split = \UTF8\mb_explode('_', $key);
                if ($split[0] == 'urlEditId')
                {
                    $editIds[$split[1]] = $var;
                }
            }
        }
        if (array_key_exists('deletes', $this->formData))
        {
            foreach ($this->formData['deletes'] as $key => $var)
            {
                $split = \UTF8\mb_explode('_', $key);
                if ($split[0] == 'urlDelete')
                {
                    if (array_key_exists($split[1], $editIds))
                    {
                        $deletes[$split[1]] = $editIds[$split[1]];
                    }
                }
            }
        }
        
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
            $values[] = 0;
            
            $this->db->insert('resource_url', $fields, $values);
        }
        
        // Edit URLs
        if (isset($editLinks))
        {
            foreach ($editLinks as $number => $link)
            {
                $updateArray['resourceurlUrl'] = $link;
                $updateArray['resourceurlName'] = array_key_exists($number, $editNames) ? $editNames[$number] : "";
                $updateArray['resourceurlPrimary'] = $this->formData['urlPrimary'] == $number ? 1 : 0;
                $this->db->formatConditions(["resourceurlId" => $editIds[$number]]);
                $this->db->formatConditions(["resourceurlResourceId" => $this->resourceId]);
                $this->db->update('resource_url', $updateArray);
            }
        }
        
        if (isset($deletes))
        {
            $this->db->formatConditionsOneField($deletes, 'resourceurlId');
            $this->db->formatConditions(["resourceurlResourceId" => $this->resourceId]);
            $this->db->delete('resource_url');
        }
        
        $this->setPrimaryUrl($this->resourceId);
        
        // send back to view this resource with success message (deleteConfirm breaks out before this)
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->resourceId, 'urlEdit');
    }
    
    /**
     * Set the primary URL
     *
     * @param int $resourceurlResourceId
     */
    private function setPrimaryUrl($resourceurlResourceId)
    {
        
        // Find the previous first URL
        $this->db->formatConditions(["resourceurlResourceId" => $resourceurlResourceId]);
        $this->db->formatConditions(["resourceurlPrimary" => 1]);
        $minArray = $this->db->selectMin('resource_url', 'resourceurlId');
        $resourceurlId = $minArray[0]['resourceurlId'];
        
        if ($resourceurlId == NULL)
        {
            // Find the first URL inserted
            $this->db->formatConditions(["resourceurlResourceId" => $resourceurlResourceId]);
            $minArray = $this->db->selectMin('resource_url', 'resourceurlId');
            $resourceurlId = $minArray[0]['resourceurlId'];
            
            // Set the primary URL
            $updateArray['resourceurlPrimary'] = 1;
            $this->db->formatConditions(["resourceurlId" => $resourceurlId]);
            $this->db->formatConditions(["resourceurlResourceId" => $resourceurlResourceId]);
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
        $ids = [];
        $links = [];
        $names = [];
        $primaries = [];
        while ($row = $this->db->fetchRow($recordset))
        {
            $ids[] = $row['resourceurlId'];
            $links[] = $row['resourceurlUrl'];
            $names[] = $row['resourceurlName'];
            $primaries[] = $row['resourceurlPrimary'];
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
            $pString .= \HTML\td(\FORM\textInput(FALSE, "url", $field, 70), 'left bottom');
            $field = array_key_exists('name', $this->formData) ? $this->formData['name'] : FALSE;
            $pString .= \HTML\td(\FORM\textInput(FALSE, "name", $field, 50), 'left bottom');
            $pString .= \HTML\td("&nbsp;");
            $pString .= \HTML\td("&nbsp;");
        $pString .= \HTML\trEnd();
        
        $numLinks = count($links);
        $index = 0;
        
        foreach ($links as $k => $link)
        {
            $tdId = \FORM\hidden("urlEditId_$index", $ids[$index]);
            
            $field = array_key_exists('links', $this->formData) && array_key_exists("urlEditLink_$index", $this->formData['links']) ?
                $this->formData['links']["urlEditLink_$index"] : $link;
            $tdUrl = \FORM\textInput(FALSE, "urlEditLink_$index", $field, 70);
            
            $field = array_key_exists('names', $this->formData) && array_key_exists("urlEditName_$index", $this->formData['names']) ?
                $this->formData['names']["urlEditName_$index"] : \HTML\dbToFormTidy($names[$index]);
            $tdName = \FORM\textInput(FALSE, "urlEditName_$index", $field, 50);
            
            
            $checked = array_key_exists('deletes', $this->formData) && array_key_exists("urlDelete_$index", $this->formData['deletes']) ?
                TRUE : FALSE;
            $tdDelete = \FORM\checkBox( FALSE, "urlDelete_$index", $checked);
            
            
            $tdUrlPrimary = "";
            if ($numLinks > 1)
            {
                if (empty($this->formData))
                {
                    if ($primaries[$index])
                    {
                        $tdUrlPrimary .= \FORM\radioButton(FALSE, 'urlPrimary', $index, TRUE);
                    }
                    else
                    {
                        $tdUrlPrimary .= \FORM\radioButton(FALSE, 'urlPrimary', $index);
                    }
                }
                else
                {
                    if (array_key_exists('urlPrimary', $this->formData) && ($index == $this->formData['urlPrimary']))
                    {
                        $tdUrlPrimary .= \FORM\radioButton(FALSE, 'urlPrimary', $index, TRUE);
                    }
                    else
                    {
                        $tdUrlPrimary .= \FORM\radioButton(FALSE, 'urlPrimary', $index);
                    }
                }
            }
            ++$index;
            
            $pString .= \HTML\trStart();
                $pString .= \HTML\td($tdId . $tdUrl);
                $pString .= \HTML\td($tdName);
                $pString .= \HTML\td($tdUrlPrimary);
                $pString .= \HTML\td($tdDelete);
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
        // Get primary URL if multiple URLs
        $this->formData['urlPrimary'] = array_key_exists('urlPrimary', $this->vars) ? $this->vars['urlPrimary'] : FALSE;
        // find any URLs to edit and files to delete
        foreach ($this->vars as $key => $var)
        {
            $delete = FALSE;
            $split = \UTF8\mb_explode('_', $key);
            if (($split[0] == 'urlEditId'))
            {
                $this->formData['ids'][$key] = trim($var);
            }
            if (($split[0] == 'urlEditLink'))
            {
                if (trim($var))
                {
                    $this->formData['links'][$key] = trim($var);
                }
                else
                {
                    $this->formData['deletes']['urlDelete_' . $split[1]] = 'on';
                }
            }
            if (($split[0] == 'urlEditName') && $var)
            {
                $this->formData['names'][$key] = trim($var);
            }
            if ($split[0] == 'urlDelete')
            {
                $this->formData['deletes'][$key] = $var;
            }
        }
        if (array_key_exists('names', $this->formData))
        {
            foreach ($this->formData['names'] as $key => $var)
            {
                $split = \UTF8\mb_explode('_', $key);
                if (array_key_exists('links', $this->formData) && !array_key_exists('urlEditLink_' . $split[1], $this->formData['links']))
                {
                    $error = $this->errors->text("inputError", "missing");
                }
            }
        }
        if ($error)
        {
            $this->badInput->close($error, $this, 'editInit');
        }
    }
}
