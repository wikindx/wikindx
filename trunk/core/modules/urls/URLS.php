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
    private $session;
    private $errors;
    private $messages;
    private $success;
    private $gatekeep;
    private $resourceId;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();

        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
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
     */
    private function editInit()
    {
        $this->session->delVar("urlLock");
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "url", $this->messages->text('misc', 'edit')));
        $this->db->formatConditions(['resourcetextId' => $this->resourceId]);
        $recordset = $this->db->select('resource_text', ['resourcetextUrls', 'resourcetextUrlText']);
        if ($this->db->numRows($recordset)) { // URLs exist for this resource
            GLOBALS::addTplVar('content', $this->urlEditForm($recordset));
        } else { // add a new URL
            GLOBALS::addTplVar('content', $this->urlAddForm());
        }
    }
    /**
     * add a URL
     */
    private function add()
    {
        if ($this->session->getVar("urlLock")) {
            $this->badInput->close($this->errors->text("done", "urlAdd"));
        }
        $this->storeUrl();
        // Lock re-uploading
        $this->session->setVar("urlLock", TRUE);
        // send back to view this resource with success message
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->resourceId, $this->success->text("urlAdd"));
    }
    /**
     * edit urls
     */
    private function edit()
    {
        // Get primary URL if multiple URLs
        $primary = array_key_exists('urlPrimary', $this->vars) ? $this->vars['urlPrimary'] : FALSE;
        // find any URLs to edit and files to delete
        foreach ($this->vars as $key => $var) {
            $split = UTF8::mb_explode('_', $key);
            if ($split[0] == 'urlEditLink') {
                $editLinks[$split[1]] = $var;
            }
            if ($split[0] == 'urlEditName') {
                $editNames[$split[1]] = $var;
            } elseif ($split[0] == 'urlDelete') {
                $deletes[$split[1]] = $var;
            }
        }
        $message = FALSE;
        // Edit URLs
        if (isset($editLinks)) {
            $this->db->formatConditions(['resourcetextId' => $this->resourceId]);
            $recordset = $this->db->select('resource_text', ['resourcetextUrls', 'resourcetextUrlText']);
            $row = $this->db->fetchRow($recordset);
            $links = \URL\getUrls($row['resourcetextUrls']);
            $urlExists = FALSE;
            if ($row['resourcetextUrlText']) {
                $names = \URL\getUrls($row['resourcetextUrlText']);
                $urlExists = TRUE;
            } else {
                $names = [];
            }
            foreach ($editLinks as $number => $link) {
                $links[$number] = $link;
                if (trim($editNames[$number])) {
                    $urlExists = TRUE;
                    $names[$number] = $editNames[$number];
                } else {
                    $names[$number] = FALSE;
                }
            }
            $updateArray['resourcetextUrls'] = base64_encode(serialize($links));
            if ($urlExists) {
                $updateArray['resourcetextUrlText'] = base64_encode(serialize($names));
            }
            $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
            $this->db->update('resource_text', $updateArray);
            $message = $this->success->text("urlEdit");
        }
        // set primary URL
        $this->setPrimaryUrl($primary);
        $this->storeUrl();
        $this->session->setVar("urlLock", TRUE);
        if (isset($deletes)) {
            $this->deleteConfirm($deletes, $message);
        }
        // send back to view this resource with success message (deleteConfirm breaks out before this)
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->resourceId, $this->success->text("urlEdit"));
    }
    /**
     * confirm delete URLs
     *
     * @param mixed $deletes
     * @param mixed $message
     */
    private function deleteConfirm($deletes, $message)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "url", $this->messages->text('misc', 'delete')));
        $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
        $recordSet = $this->db->select(["resource_text"], ["resourcetextUrls", "resourcetextUrlText"]);
        $row = $this->db->fetchRow($recordSet);
        $links = \URL\getUrls($row['resourcetextUrls']);
        if ($row['resourcetextUrlText']) {
            $names = \URL\getUrls($row['resourcetextUrlText']);
        } else {
            $names = [];
        }
        $pString = $message;
        $pString .= \FORM\formHeader("urls_URLS_CORE");
        $pString .= \FORM\hidden('function', 'delete');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        $pString .= $this->messages->text('resources', 'deleteConfirmUrl') . ':' . BR;
        foreach ($deletes as $index => $var) {
            if (!empty($names) && $names[$index]) {
                $label = $names[$index];
            } else {
                $label = $links[$index];
            }
            $pString .= \FORM\checkBox(FALSE, "urlDelete_$index", TRUE) . '&nbsp;' . $label . BR;
        }
        $pString .= \HTML\p('&nbsp;' . BR . \FORM\formSubmit($this->messages->text("submit", "Confirm")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * delete URLs
     */
    private function delete()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "url", $this->messages->text('misc', 'delete')));
        foreach ($this->vars as $key => $var) {
            $split = UTF8::mb_explode('_', $key);
            if ($split[0] == 'urlDelete') {
                $deletes[] = $split[1];
            }
        }
        $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
        $recordSet = $this->db->select(["resource_text"], ["resourcetextUrls", "resourcetextUrlText"]);
        $row = $this->db->fetchRow($recordSet);
        $links = \URL\getUrls($row['resourcetextUrls']);
        if ($row['resourcetextUrlText']) {
            $names = \URL\getUrls($row['resourcetextUrlText']);
        } else {
            $names = [];
        }
        foreach ($deletes as $index) {
            array_splice($links, $index, 1);
            if (!empty($names)) {
                array_splice($names, $index, 1);
            }
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
        // send back to view this resource with success message
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->resourceId, $this->success->text("urlDelete"));
    }
    /**
     * set primary URL
     *
     * @param mixed $primary
     */
    private function setPrimaryUrl($primary)
    {
        if (!$primary) { // already primary so do nothing
            return;
        }
        $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
        $recordSet = $this->db->select(["resource_text"], ["resourcetextUrls", "resourcetextUrlText"]);
        $row = $this->db->fetchRow($recordSet);
        $links = \URL\getUrls($row['resourcetextUrls']);
        if ($row['resourcetextUrlText']) {
            $names = \URL\getUrls($row['resourcetextUrlText']);
        } else {
            $names = [];
        }
        $link = $links[$primary];
        unset($links[$primary]);
        array_unshift($links, $link);
        $updateArray['resourcetextUrls'] = base64_encode(serialize($links));
        if (!empty($names)) {
            $name = $names[$primary];
            unset($names[$primary]);
            array_unshift($names, $name);
            $updateArray['resourcetextUrlText'] = base64_encode(serialize($names));
        }
        $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
        $this->db->update('resource_text', $updateArray);
    }
    /**
     * Store new URL
     */
    private function storeUrl()
    {
        if (array_key_exists('url', $this->vars) && trim($this->vars['url'])) {
            $link = $this->vars['url'];
        } else {
            return;
        }
        if (array_key_exists('name', $this->vars)) {
            $name = $this->vars['name'];
        } else {
            $name = FALSE;
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
            $links[] = $link;
            if (empty($names)) {
                $names[] = FALSE;
            }
            $names[] = $name;
            $updateArray['resourcetextUrls'] = base64_encode(serialize($links));
            $updateArray['resourcetextUrlText'] = base64_encode(serialize($names));
            $this->db->formatConditions(["resourcetextId" => $this->resourceId]);
            $this->db->update('resource_text', $updateArray);
        } else {	// insert
            $links[] = $link;
            if ($name) { // else leave as default NULL
                $names[] = $name;
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
     *
     * @return string
     */
    private function urlEditForm($recordset)
    {
        $names = [];
        while ($row = $this->db->fetchRow($recordset)) {
            $links = \URL\getUrls($row['resourcetextUrls']);
            if ($row['resourcetextUrlText']) {
                $names = \URL\getUrls($row['resourcetextUrlText']);
            }
        }
        $pString = \FORM\formHeader("urls_URLS_CORE");
        $pString .= \FORM\hidden('function', 'edit');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        // Form elements for adding another URL
        $pString .= \HTML\tableStart('left');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($this->messages->text("resources", "url") . ":&nbsp;" . \FORM\textInput(FALSE, "url", FALSE, 70), 'left bottom');
        $pString .= \HTML\td($this->messages->text("resources", "urlLabel") . ":&nbsp;" . \FORM\textInput(FALSE, "name", FALSE, 50), 'left bottom');
        $pString .= \HTML\trEnd();
        $numLinks = count($links);
        $index = 0;
        foreach ($links as $link) {
            $pString .= \HTML\tableEnd();
            $pString .= \HTML\p('&nbsp;');
            $pString .= \HTML\tdEnd() . \HTML\trEnd() . \HTML\trStart() . \HTML\tdStart();
            $pString .= \HTML\tableStart('left');
            $pString .= \HTML\trStart();
            $td = $this->messages->text('resources', 'url') . ':&nbsp;' . \FORM\textInput(
                FALSE,
                "urlEditLink_$index",
                $link,
                70
            );
            if (!empty($names)) {
                $td .= '&nbsp;&nbsp;' . $this->messages->text('resources', 'urlLabel') . ':&nbsp;' .
                    \FORM\textInput(FALSE, "urlEditName_$index", \HTML\dbToFormTidy(array_shift($names)), 50);
            } else {
                $td .= '&nbsp;&nbsp;' . $this->messages->text('resources', 'urlLabel') . ':&nbsp;' .
                    \FORM\textInput(FALSE, "urlEditName_$index", FALSE, 50);
            }
            $td .= '&nbsp;&nbsp;' . $this->messages->text('misc', 'delete') . ':&nbsp;' . \FORM\checkBox(
                FALSE,
                "urlDelete_$index"
            );
            if ($numLinks > 1) {
                $td .= '&nbsp;&nbsp;' . $this->messages->text('resources', 'primaryUrl') . ':&nbsp;';
                if ($index == 0) {
                    $td .= \FORM\radioButton(FALSE, 'urlPrimary', $index, TRUE);
                } else {
                    $td .= \FORM\radioButton(FALSE, 'urlPrimary', $index);
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
     * @return string
     */
    private function urlAddForm()
    {
        $pString = \FORM\formHeader("urls_URLS_CORE");
        $pString .= \FORM\hidden('function', 'add');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        $pString .= \HTML\tableStart('left');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td($this->messages->text("resources", "url") . ":&nbsp;" .
            \FORM\textInput(FALSE, "url", FALSE, 70), 'left bottom');
        $pString .= \HTML\td($this->messages->text("resources", "urlLabel") . ":&nbsp;" .
            \FORM\textInput(FALSE, "name", FALSE, 50), 'left bottom');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\trEnd() . \HTML\tdEnd() . \HTML\trStart() . \HTML\tdStart();
        $pString .= \HTML\p('&nbsp;' . BR . \FORM\formSubmit($this->messages->text("submit", "Save")));
        $pString .= \FORM\formEnd();

        return $pString;
    }
}
