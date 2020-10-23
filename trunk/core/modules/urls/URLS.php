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
    }
    /**
     * add, edit, delete resource attachments
     */
    public function init()
    {
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->gatekeep->init();
        if (!array_key_exists('resourceId', $this->vars) || !array_key_exists('function', $this->vars)) {
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
			$this->icons->getHTML("Return"),
			'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId
		);
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "url", $this->messages->text('misc', 'edit') . '&nbsp;&nbsp;' . $return));
        $this->db->formatConditions(['resourcetextId' => $this->resourceId]);
        $recordset = $this->db->select('resource_text', ['resourcetextUrls', 'resourcetextUrlText']);
        if ($this->db->numRows($recordset)) { // URLs exist for this resource
            GLOBALS::addTplVar('content', $this->urlEditForm($recordset, $message));
        } else { // add a new URL
            GLOBALS::addTplVar('content', $this->urlAddForm($message));
        }
    }
    /**
     * add a URL
     */
    private function add()
    {
    	$this->validateInput('add');
        $this->storeUrl();
        // send back to view this resource with success message
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->resourceId, $this->success->text("urlAdd"));
    }
    /**
     * edit urls
     */
    private function edit()
    {
    	$this->validateInput();
        if (array_key_exists('links', $this->formData)) {
			foreach ($this->formData['links'] as $key => $var) {
				$split = \UTF8\mb_explode('_', $key);
				if ($split[0] == 'urlEditLink') {
					$editLinks[$split[1]] = $var;
				}
			}
		}
        if (array_key_exists('names', $this->formData)) {
			foreach ($this->formData['names'] as $key => $var) {
				$split = \UTF8\mb_explode('_', $key);
				if ($split[0] == 'urlEditName') {
					$editNames[$split[1]] = $var;
				}
			}
		}
        if (array_key_exists('deletes', $this->formData)) {
			foreach ($this->formData['deletes'] as $key => $var) {
				$split = \UTF8\mb_explode('_', $key);
				if ($split[0] == 'urlDelete') {
					$deletes[$split[1]] = $var;
				}
			}
		}
        // Edit URLs
        if (isset($editLinks)) {
        	$names = [];
          	$urlTextExists = FALSE;
			foreach ($editLinks as $number => $link) {
                $links[$number] = $link;
                if (isset($editNames) && array_key_exists($number, $editNames)) {
                    $urlTextExists = TRUE;
                    $names[$number] = $editNames[$number];
                } else {
                    $names[$number] = FALSE;
                }
            }
            $updateArray['resourcetextUrls'] = base64_encode(serialize($links));
            if ($urlTextExists) {
                $updateArray['resourcetextUrlText'] = base64_encode(serialize($names));
            }
            // Remove first then edit
            $this->db->formatConditions(['resourcetextId' => $this->resourceId]);
            $this->db->updateNull('resource_text', ['resourcetextUrls', 'resourcetextUrlText']);
            $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
            $this->db->update('resource_text', $updateArray);
        }
        // store new URL
        $this->storeUrl();
        $message = $this->success->text("urlEdit");
        if (isset($deletes)) {
            $this->delete($deletes);
        }
        // set primary URL
        $this->setPrimaryUrl();
        // send back to view this resource with success message (deleteConfirm breaks out before this)
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->resourceId, $message);
    }
    /**
     * delete URLs
     *
     * @param array $deletes
     */
    private function delete($deletes)
    {
        $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
        $recordSet = $this->db->select(["resource_text"], ["resourcetextUrls", "resourcetextUrlText"]);
        $row = $this->db->fetchRow($recordSet);
        $links = array_diff_key(\URL\getUrls($row['resourcetextUrls']), $deletes);
        if ($row['resourcetextUrlText']) {
            $names = array_diff_key(\URL\getUrls($row['resourcetextUrlText']), $deletes);
        } else {
            $names = [];
        }
        $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
        $recordset = $this->db->select('resource_text', ['resourcetextAbstract', 'resourcetextNote']);
        $row = $this->db->fetchRow($recordset);
        // If all fields are null, delete the row
        if (empty($links) && !$row['resourcetextAbstract'] && !$row['resourcetextNote']) {
            $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
            $this->db->delete('resource_text');
        } elseif (empty($links)) {
            $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
            $this->db->updateNull('resource_text', ['resourcetextUrls', 'resourcetextUrlText']);
        } else {
            $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
            $updateArray['resourcetextUrls'] = base64_encode(serialize($links));
            if (empty($names) || ((count($names) == 1) && !$names[0])) { // set to NULL
                $this->db->updateNull('resource_text', 'resourcetextUrlText');
                $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
            } else {
                $updateArray['resourcetextUrlText'] = base64_encode(serialize($names));
            }
            $this->db->update('resource_text', $updateArray);
        }
        // set primary URL
        $this->setPrimaryUrl();
        // send back to view this resource with success message
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->resourceId, $this->success->text("urlEdit"));
    }
    /**
     * set primary URL
     *
     * Key 0 in the list is the primary URL so this is a matter of re-ordering
     *
     */
    private function setPrimaryUrl()
    {
        $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
        $recordSet = $this->db->select(["resource_text"], ["resourcetextUrls", "resourcetextUrlText"]);
        $row = $this->db->fetchRow($recordSet);
        $links = \URL\getUrls($row['resourcetextUrls']);
        if (empty($links)) { // Nothing to do
        	return;
        }
        if ($row['resourcetextUrlText']) {
            $names = \URL\getUrls($row['resourcetextUrlText']);
        } else {
            $names = [];
        }
        if (array_key_exists('urlPrimary', $this->formData) && array_key_exists($this->formData['urlPrimary'], $links)) {
			$link = $links[$this->formData['urlPrimary']];
			unset($links[$this->formData['urlPrimary']]);
			array_unshift($links, $link);
			if (!empty($names)) {
				$name = $names[$this->formData['urlPrimary']];
				unset($names[$this->formData['urlPrimary']]);
				array_unshift($names, $name);
			}
        }
// Ensure we start from key 0
		$index = 0;
		if (!array_key_exists(0, $links)) {
			foreach ($links as $key => $var) {
				$newLinks[$index] = $var;
				if (array_key_exists($key, $names)) {
					$newNames[$index] = $names[$key];
				}
				++$index;
			}
			if (isset($newLinks)) {
				$links = $newLinks;
			}
			if (isset($newNames)) {
				$names = $newNames;
			}
		}
        $updateArray['resourcetextUrls'] = base64_encode(serialize($links));
        $updateArray['resourcetextUrlText'] = base64_encode(serialize($names));
        $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
        $this->db->update('resource_text', $updateArray);
    }
    /**
     * Store new URL
     *
     */
    private function storeUrl()
    {
    	$names = [];
		if (!array_key_exists('url', $this->formData)) { // Nothing to do
        	return;
        }
        $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
        $recordSet = $this->db->select(["resource_text"], ["resourcetextUrls", "resourcetextUrlText"]);
        if ($this->db->numRows($recordSet)) { // URLs already exist for this resource so need to update rather than insert
            $row = $this->db->fetchRow($recordSet);
            $links = \URL\getUrls($row['resourcetextUrls']);
            if ($row['resourcetextUrlText']) {
                $names = \URL\getUrls($row['resourcetextUrlText']);
            } else {
                $names = [];
            }
            $links[] = $this->formData['url'];
            if (empty($names)) {
                $names[] = FALSE;
            }
            $names[] = $this->formData['name'];
            $updateArray['resourcetextUrls'] = base64_encode(serialize($links));
            $updateArray['resourcetextUrlText'] = base64_encode(serialize($names));
        	$this->db->formatConditions(["resourcetextId" => $this->resourceId]);
			$this->db->update('resource_text', $updateArray);
        } else {	// insert
            $links[] = $this->formData['url'];
            if ($this->formData['name']) { // else leave as default NULL
                $names[] = $this->formData['name'];
                $fields[] = 'resourcetextUrlText';
                $values[] = base64_encode(serialize($names));
            }
            $fields[] = 'resourcetextId';
            $values[] = $this->resourceId;
            $fields[] = 'resourcetextUrls';
            $values[] = base64_encode(serialize($links));
            $this->db->insert('resource_text', $fields, $values);
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
        $names = [];
        while ($row = $this->db->fetchRow($recordset)) {
            $links = \URL\getUrls($row['resourcetextUrls']);
            if ($row['resourcetextUrlText']) {
                $names = \URL\getUrls($row['resourcetextUrlText']);
            }
        }
        $pString = $message;
        $pString .= \FORM\formHeader("urls_URLS_CORE");
        $pString .= \FORM\hidden('function', 'edit');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        // Form elements for adding another URL
        $pString .= \HTML\tableStart('left');
        $pString .= \HTML\trStart();
        $field = array_key_exists('url', $this->formData) ? $this->formData['url'] : WIKINDX_RESOURCE_URL_PREFIX;
        $pString .= \HTML\td($this->messages->text("resources", "url") . ":&nbsp;" . \FORM\textInput(FALSE, "url", $field, 70), 'left bottom');
        $field = array_key_exists('name', $this->formData) ? $this->formData['name'] : FALSE;
        $pString .= \HTML\td($this->messages->text("resources", "urlLabel") . ":&nbsp;" . \FORM\textInput(FALSE, "name", $field, 50), 'left bottom');
        $pString .= \HTML\trEnd();
        $numLinks = count($links);
        $index = 0;
        foreach ($links as $link) {
            $pString .= \HTML\tableEnd();
            $pString .= \HTML\p('&nbsp;');
            $pString .= \HTML\tdEnd() . \HTML\trEnd() . \HTML\trStart() . \HTML\tdStart();
            $pString .= \HTML\tableStart('left');
            $pString .= \HTML\trStart();
        	$field = array_key_exists('links', $this->formData) && array_key_exists("urlEditLink_$index", $this->formData['links']) ? 
        		$this->formData['links']["urlEditLink_$index"] : $link;
            $td = $this->messages->text('resources', 'url') . ':&nbsp;' . \FORM\textInput(
                FALSE,
                "urlEditLink_$index",
                $field,
                70
            );
            if (!empty($names)) {
        		$field = array_key_exists('names', $this->formData) && array_key_exists("urlEditName_$index", $this->formData['names']) ? 
        			$this->formData['names']["urlEditName_$index"] : \HTML\dbToFormTidy(array_shift($names));
                $td .= '&nbsp;&nbsp;' . $this->messages->text('resources', 'urlLabel') . ':&nbsp;' .
                    \FORM\textInput(FALSE, "urlEditName_$index", $field, 50);
            } else {
        		$field = array_key_exists('names', $this->formData) && array_key_exists("urlEditName_$index", $this->formData['names']) ? 
        			$this->formData['names']["urlEditName_$index"] : FALSE;
                $td .= '&nbsp;&nbsp;' . $this->messages->text('resources', 'urlLabel') . ':&nbsp;' .
                    \FORM\textInput(FALSE, "urlEditName_$index", $field, 50);
            }
        	$checked = array_key_exists('deletes', $this->formData) && array_key_exists("urlDelete_$index", $this->formData['deletes']) ? 
        		TRUE : FALSE;
            $td .= '&nbsp;&nbsp;' . $this->messages->text('misc', 'delete') . ':&nbsp;' . \FORM\checkBox(
                FALSE,
                "urlDelete_$index",
                $checked
            );
            if ($numLinks > 1) {
                $td .= '&nbsp;&nbsp;' . $this->messages->text('resources', 'primaryUrl') . ':&nbsp;';
                if (empty($this->formData)) {
					if ($index == 0) {
						$td .= \FORM\radioButton(FALSE, 'urlPrimary', $index, TRUE);
					} else {
						$td .= \FORM\radioButton(FALSE, 'urlPrimary', $index);
					}
                }
                else
                {
					if (array_key_exists('urlPrimary', $this->formData) && ($index == $this->formData['urlPrimary'])) {
						$td .= \FORM\radioButton(FALSE, 'urlPrimary', $index, TRUE);
					} else {
						$td .= \FORM\radioButton(FALSE, 'urlPrimary', $index);
					}
                }
            }
            ++$index;
            $pString .= \HTML\td($td);
            $pString .= \HTML\trEnd();
        }
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\trEnd() . \HTML\tdEnd() . \HTML\trStart() . \HTML\tdStart();
        $pString .= \HTML\p('&nbsp;' . BR . \FORM\formSubmit($this->messages->text("submit", "Save")));
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
        $pString .= \HTML\tableStart('left');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($this->messages->text("resources", "url") . ":&nbsp;" .
            \FORM\textInput(FALSE, "url", WIKINDX_RESOURCE_URL_PREFIX, 70), 'left bottom');
        $field = array_key_exists('name', $this->formData) ? $this->formData['name'] : FALSE;
        $pString .= \HTML\td($this->messages->text("resources", "urlLabel") . ":&nbsp;" .
            \FORM\textInput(FALSE, "name", $field, 50), 'left bottom');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\trEnd() . \HTML\tdEnd() . \HTML\trStart() . \HTML\tdStart();
        $pString .= \HTML\p('&nbsp;' . BR . \FORM\formSubmit($this->messages->text("submit", "Save")));
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * Validate input
     *
     * @param string $type
     *
     */
    private function validateInput($type = 'edit')
    {
    	$error = '';
// If both url and name fields are empty, we are just editing existing urls as in edit(). If a name exists, so must the url.
        if (\UTF8\mb_trim($this->vars['url'])) {
            $this->formData['url'] = \UTF8\mb_trim($this->vars['url']);
        }
        if (\UTF8\mb_trim($this->vars['name'])) {
            $this->formData['name'] = \UTF8\mb_trim($this->vars['name']);
            if (!array_key_exists('url', $this->formData)) {
            	$error = $this->errors->text("inputError", "missing");
        	}
        }
        if (($type == 'add') && !array_key_exists('url', $this->formData)) {
            $error = $this->errors->text("inputError", "missing");
        }
        // Get primary URL if multiple URLs
        $this->formData['urlPrimary'] = array_key_exists('urlPrimary', $this->vars) ? $this->vars['urlPrimary'] : FALSE;
        // find any URLs to edit and files to delete
        foreach ($this->vars as $key => $var) {
        	$delete = FALSE;
            $split = \UTF8\mb_explode('_', $key);
            if (($split[0] == 'urlEditLink')) {
            	if (trim($var)) {
	                $this->formData['links'][$key] = trim($var);
	            } else {
    	        	$this->formData['deletes']['urlDelete_' . $split[1]] = 'on';
    	        }
            }
            if (($split[0] == 'urlEditName') && $var) {
                $this->formData['names'][$key] = trim($var);
            }
            if ($split[0] == 'urlDelete') {
                $this->formData['deletes'][$key] = $var;
            }
        }
        if (array_key_exists('names', $this->formData)) {
        	foreach ($this->formData['names'] as $key => $var) {
            	$split = \UTF8\mb_explode('_', $key);
                if (array_key_exists('links', $this->formData) && !array_key_exists('urlEditLink_' . $split[1], $this->formData['links'])) {
            		$error = $this->errors->text("inputError", "missing");
                }
            }
        }
        if ($error) {
            $this->badInput->close($error, $this, 'editInit');
        }
    }
}
