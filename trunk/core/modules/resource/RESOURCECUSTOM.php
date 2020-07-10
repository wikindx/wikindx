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
 * RESOURCECUSTOM class
 *
 * Deal with resource's custom fields
 */
class RESOURCECUSTOM
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $icons;
    private $user;
    private $common;
    private $gatekeep;
    private $badInput;
    private $cite;

    // Constructor
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->common = FACTORY_RESOURCECOMMON::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->cite = FACTORY_CITE::getInstance();
    }
    /**
     * Display resource's custom fields
     *
     * @param int $rId
     *
     * @return array
     */
    public function view($rId)
    {
        $array = [];
        $index = 0;
        if ($this->session->getVar("setup_Write")) {
            $this->db->formatConditions(['resourcecustomResourceId' => $rId]);
            $subQ = $this->db->subQuery($this->db->selectNoExecute('resource_custom', 'resourcecustomCustomId'), FALSE, FALSE, TRUE);
            $this->db->formatConditions($this->db->formatFields('customId') . $this->db->inClause($subQ, TRUE));
            $recordset = $this->db->select('custom', ['customId', 'customLabel']);
            while ($row = $this->db->fetchRow($recordset)) {
                $array[$index]['editLink'] = \HTML\a(
                    $this->icons->getClass("edit"),
                    $this->icons->getHTML("edit"),
                    "index.php?action=resource_RESOURCECUSTOM_CORE" . htmlentities("&method=writeInit&id=" . $row['customId'] .
                    '&resourceId=' . $rId)
                );
                $array[$index]['title'] = \HTML\nlToHtml($row['customLabel']);
                ++$index;
            }
        }
        $this->db->leftJoin('resource_custom', 'resourcecustomCustomId', 'customId');
        $this->db->formatConditions(['resourcecustomResourceId' => $rId]);
        $recordset = $this->db->select('custom', ['resourcecustomId', 'customLabel', 'customSize', 'resourcecustomShort',
            'resourcecustomLong', 'resourcecustomAddUserIdCustom', 'resourcecustomEditUserIdCustom', ]);
        while ($row = $this->db->fetchRow($recordset)) {
            if ($this->session->getVar("setup_Write")) {
                $array[$index]['editLink'] = \HTML\a(
                    $this->icons->getClass("edit"),
                    $this->icons->getHTML("edit"),
                    "index.php?action=resource_RESOURCECUSTOM_CORE" . htmlentities("&method=editInit&id=" . $row['resourcecustomId'] .
                    '&size=' . $row['customSize'])
                );
            }
            $text = FALSE;
            if ($row['resourcecustomShort']) {
                $text = trim($row['resourcecustomShort']);
            }
            if ($row['resourcecustomLong']) {
                $text = $this->cite->parseCitations(trim($row['resourcecustomLong']), 'html');
            }
            if (!$text) {
                continue;
            }
            $array[$index]['text'] = $this->common->doHighlight(\HTML\nlToHtml($text));
            if ($row['resourcecustomEditUserIdCustom']) {
                $this->db->formatConditions(['usersId' => $row['resourcecustomEditUserIdCustom']]);
            } else {
                $this->db->formatConditions(['usersId' => $row['resourcecustomAddUserIdCustom']]);
            }
            $user = $this->db->selectFirstRow('users', ['usersUsername', 'usersFullname']);
            $user['resourcecustomAddUserIdCustom'] = $row['resourcecustomAddUserIdCustom'];
            $user['resourcecustomEditUserIdCustom'] = $row['resourcecustomEditUserIdCustom'];
            list($array[$index]['userAdd'], $array[$index]['userEdit']) = $this->user->displayUserAddEdit($user, TRUE, 'custom');
            $array[$index]['title'] = \HTML\nlToHtml($row['customLabel']);
            ++$index;
        }

        return $array;
    }
    /**
     * display the editing form
     */
    public function editInit()
    {
        $this->gatekeep->init();
        $this->checkInput(['id', 'size']);
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $this->session->delVar("resourceCustomLock");
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userEditField"));
        if ($this->vars['size'] == 'L') {
            $fieldName = 'resourcecustomLong';
        } else {
            $fieldName = 'resourcecustomShort';
        }
        $this->db->leftJoin('custom', 'customId', 'resourcecustomCustomId');
        $this->db->formatConditions(['resourcecustomId' => $this->vars['id']]);
        $recordset = $this->db->select('resource_custom', [$fieldName, 'customLabel', 'resourcecustomResourceId']);
        $row = $this->db->fetchRow($recordset);
        $pString = \FORM\formHeader('resource_RESOURCECUSTOM_CORE');
        $pString .= \FORM\hidden("method", 'edit');
        $pString .= \FORM\hidden("id", $this->vars['id']);
        $pString .= \FORM\hidden("resourceId", $row['resourcecustomResourceId']);
        $pString .= \FORM\hidden("size", $this->vars['size']);
        $pString .= $tinymce->loadMetadataTextarea();
        // The second parameter ('customText') to textareaInput is the textarea name
        if ($this->vars['size'] == 'L') {
            $pString .= \FORM\textareaInput(
                \HTML\strong(\HTML\nlToHtml($row['customLabel'])),
                "customText",
                \HTML\dbToFormTidy($row[$fieldName]),
                80,
                10
            );
        } else {
            $pString .= \FORM\textInput(
                \HTML\strong(\HTML\nlToHtml($row['customLabel'])),
                'customText',
                \HTML\dbToFormTidy($row[$fieldName]),
                80,
                255
            );
        }
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * write edit to the database
     */
    public function edit()
    {
        if ($this->session->getVar("resourceCustomLock")) {
            $this->badInput->close($this->errors->text("done", "custom"));
        }
        $this->gatekeep->init();
        $this->checkInput(['id', 'size', 'resourceId']);
        $userId = $this->session->getVar("setup_UserId");
        // if customText is empty, delete the row
        if (!trim($this->vars['customText'])) {
            $message = $this->success->text("fieldDelete");
            $this->db->formatConditions(['resourcecustomId' => $this->vars['id']]);
            $this->db->delete('resource_custom');
        } else {
            $message = $this->success->text("fieldEdit");
            if ($this->vars['size'] == 'S') {
                $field = 'resourcecustomShort';
            } else {
                $field = 'resourcecustomLong';
            }
            $updateArray[$field] = trim($this->vars['customText']);
            if ($userId) {
                $updateArray["resourcecustomEditUserIdCustom"] = $userId;
            }
            $this->db->formatConditions(['resourcecustomId' => $this->vars['id']]);
            $this->db->update('resource_custom', $updateArray);
        }
        // update resource timestamp
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['resourceId']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        // email notification
        include_once("core/modules/email/EMAIL.php");
        $email = new EMAIL($this->db);
        $email->notify($this->vars['resourceId']);
        // lock reload
        $this->session->setVar("resourceCustomLock", TRUE);
        // send back to view this resource with success message
        $this->navigate($message);
    }
    /**
     * display the writing form
     */
    public function writeInit()
    {
        $this->gatekeep->init();
        $this->checkInput(['id', 'resourceId']);
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $this->session->delVar("resourceCustomLock");
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "userEditField"));
        $this->db->formatConditions(['customId' => $this->vars['id']]);
        $row = $this->db->selectFirstRow('custom', ['customLabel', 'customSize']);
        $pString = \FORM\formHeader('resource_RESOURCECUSTOM_CORE');
        $pString .= \FORM\hidden("method", 'write');
        $pString .= \FORM\hidden("id", $this->vars['id']);
        $pString .= \FORM\hidden("resourceId", $this->vars['resourceId']);
        $pString .= \FORM\hidden("size", $row['customSize']);
        $pString .= $tinymce->loadMetadataTextarea();
        // The second parameter ('customText') to textareaInput is the textarea name
        if ($row['customSize'] == 'L') {
            $pString .= \FORM\textareaInput(
                \HTML\strong(\HTML\nlToHtml($row['customLabel'])),
                "customText",
                FALSE,
                80,
                10
            );
        } else {
            $pString .= \FORM\textInput(
                \HTML\strong(\HTML\nlToHtml($row['customLabel'])),
                'customText',
                FALSE,
                80,
                255
            );
        }
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * write to the database
     */
    public function write()
    {
        if ($this->session->getVar("resourceCustomLock")) {
            $this->badInput->close($this->errors->text("done", "custom"));
        }
        $this->gatekeep->init();
        $this->checkInput(['id', 'size', 'resourceId', 'customText']);
        $userId = $this->session->getVar("setup_UserId");
        if (!trim($this->vars['customText'])) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        } else {
            if ($this->vars['size'] == 'S') {
                $field = 'resourcecustomShort';
            } else {
                $field = 'resourcecustomLong';
            }
            $array[$field] = trim($this->vars['customText']);
            if ($userId) {
                $array["resourcecustomAddUserIdCustom"] = $userId;
            }
            $array['resourcecustomCustomId'] = $this->vars['id'];
            $array['resourcecustomResourceId'] = $this->vars['resourceId'];
            $this->db->insert('resource_custom', array_keys($array), array_values($array));
        }
        // update resource timestamp
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['resourceId']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        // email notification
        include_once("core/modules/email/EMAIL.php");
        $email = new EMAIL($this->db);
        $email->notify($this->vars['resourceId']);
        // lock reload
        $this->session->setVar("resourceCustomLock", TRUE);
        // send back to view this resource with success message
        $this->navigate($this->success->text("fieldAdd"));
    }
    /**
     * Check we have appropriate input
     *
     * @param array $array
     */
    public function checkInput($array)
    {
        foreach ($array as $item) {
            if (!array_key_exists($item, $this->vars) || !$this->vars[$item]) {
                $this->badInput->close($this->errors->text("inputError", "missing"));
            }
        }
    }
    /**
     * Navigate to last resource with message (success or error)
     *
     * @param mixed $message
     */
    private function navigate($message)
    {
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->vars['resourceId'], $message);
        FACTORY_CLOSE::getInstance();
    }
}
