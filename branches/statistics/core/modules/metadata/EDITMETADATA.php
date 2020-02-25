<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */


/**
 * Edit Metadata:  notes, abstract, quotes, paraphrases, musings.
 */
class EDITMETADATA
{
    private $db;
    private $vars;
    private $badInput;
    private $gatekeep;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $tinymce;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->badInput = FACTORY_BADINPUT::getInstance();


        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->tinymce = FACTORY_LOADTINYMCE::getInstance();
    }
    /**
     * init
     */
    public function init()
    {
        if (!array_key_exists('type', $this->vars) || !array_key_exists('id', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        if (($this->vars['type'] == 'abstractInit') || ($this->vars['type'] == 'noteInit') ||
            ($this->vars['type'] == 'noteDeleteInit') || ($this->vars['type'] == 'abstractDeleteInit') ||
            ($this->vars['type'] == 'noteDelete') || ($this->vars['type'] == 'abstractDelete') ||
            ($this->vars['type'] == 'abstractEdit') || ($this->vars['type'] == 'noteEdit'))
        {
            $this->gatekeep->init();
        }
        else
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        // proceed
        $type = $this->vars['type'];
        $this->{$type}();
    }
    /**
     * Ask for confirmation for abstract to be deleted
     */
    public function abstractDeleteInit()
    {
        $this->session->delVar("metadataLock");
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "abstractDelete"));
        $pString = \FORM\formHeader('metadata_EDITMETADATA_CORE');
        $pString .= \FORM\hidden("type", "abstractDelete");
        $pString .= \FORM\hidden("id", $this->vars['id']);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Confirm")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete abstract following confirmation
     */
    public function abstractDelete()
    {
        $this->db->formatConditions(['resourcetextId' => $this->vars['id']]);
        $this->db->updateNull('resource_text', ['resourcetextAbstract', 'resourcetextAddUserIdAbstract',
            'resourcetextEditUserIdAbstract', ]);
        $this->checkDeleteRow();
        // update resource timestamp
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['id']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        $this->notify();
        // lock reload
        $this->session->setVar("metadataLock", TRUE);
        $this->navigate($this->success->text("abstractDelete"));
    }
    /**
     * Ask for confirmation for note to be deleted
     */
    public function noteDeleteInit()
    {
        $this->session->delVar("metadataLock");
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "notesDelete"));
        $pString = \FORM\formHeader('metadata_EDITMETADATA_CORE');
        $pString .= \FORM\hidden("type", "noteDelete");
        $pString .= \FORM\hidden("id", $this->vars['id']);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Confirm")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete notes following confirmation
     */
    public function noteDelete()
    {
        $this->db->formatConditions(['resourcetextId' => $this->vars['id']]);
        $this->db->updateNull('resource_text', ['resourcetextNote', 'resourcetextAddUserIdNote',
            'resourcetextEditUserIdNote', ]);
        $this->checkDeleteRow();
        // update resource timestamp
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['id']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        $this->notify();
        // lock reload
        $this->session->setVar("metadataLock", TRUE);
        $this->navigate($this->success->text("noteDelete"));
    }
    /**
     * Display form for editing abstract
     */
    private function abstractInit()
    {
        $this->session->delVar("metadataLock");
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "abstract"));
        // Create citation link for this type
        //		include_once("core/cite/CITE.php");
        //		$citeLink = CITE::makeCiteLink('abstract');
        $pString = \FORM\formHeader('metadata_EDITMETADATA_CORE');
        $pString .= \FORM\hidden("type", "abstractEdit");
        $pString .= \FORM\hidden("id", $this->vars['id']);
        $pString .= $this->tinymce->loadMetadataTextarea();
        $this->db->formatConditions(['resourcetextId' => $this->vars['id']]);
        $recordset = $this->db->select('resource_text', 'resourcetextAbstract');
        $row = $this->db->fetchRow($recordset);
        if (!$this->db->numRows($recordset))
        {
            $pString .= \FORM\hidden('sql', 'insert');
        }
        else
        {
            $pString .= \FORM\hidden('sql', 'update');
        }
        $pString .= \FORM\textareaInput(
            FALSE,
            "text",
            \HTML\dbToFormTidy(is_array($row) ? $row['resourcetextAbstract'] : ""),
            80,
            15
        );
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display form for editing note
     */
    private function noteInit()
    {
        $this->session->delVar("metadataLock");
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "notes"));
        // Create citation link for this type
        //		include_once("core/cite/CITE.php");
        //		$citeLink = CITE::makeCiteLink('note');
        $pString = \FORM\formHeader('metadata_EDITMETADATA_CORE');
        $pString .= \FORM\hidden("type", "noteEdit");
        $pString .= \FORM\hidden("id", $this->vars['id']);
        $pString .= $this->tinymce->loadMetadataTextarea();
        $this->db->formatConditions(['resourcetextId' => $this->vars['id']]);
        $recordset = $this->db->select('resource_text', 'resourcetextNote');
        $row = $this->db->fetchRow($recordset);
        if (!$this->db->numRows($recordset))
        {
            $pString .= \FORM\hidden('sql', 'insert');
        }
        else
        {
            $pString .= \FORM\hidden('sql', 'update');
        }
        $pString .= \FORM\textareaInput(
            FALSE,
            "text",
            \HTML\dbToFormTidy(is_array($row) ? $row['resourcetextNote'] : ""),
            80,
            15
        );
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Edit abstract
     */
    private function abstractEdit()
    {
        if (!array_key_exists('type', $this->vars) || !array_key_exists('id', $this->vars) ||
            !array_key_exists('sql', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        if ($this->session->getVar("metadataLock"))
        {
            $this->navigate($this->errors->text("done", "abstract"));
        }
        $abstract = trim($this->vars['text']);
        $userId = $this->session->getVar("setup_UserId");
        if ($this->vars['sql'] == 'insert')
        {
            if (!$abstract)
            {
                $this->navigate($this->errors->text("inputError", "missing"));
            }
            $message = $this->success->text("abstractAdd");
            $fields[] = "resourcetextId";
            $values[] = $this->vars['id'];
            $fields[] = "resourcetextAbstract";
            $values[] = $abstract;
            if ($userId)
            {
                $fields[] = "resourcetextAddUserIdAbstract";
                $values[] = $userId;
            }
            $this->db->insert('resource_text', $fields, $values);
        }
        else
        { // update
            $this->db->formatConditions(['resourcetextId' => $this->vars['id']]);
            // if abstractText is empty, set fields to null
            if (!$abstract)
            {
                $message = $this->success->text("abstractDelete");
                $this->db->updateNull('resource_text', ['resourcetextAbstract', 'resourcetextAddUserIdAbstract',
                    'resourcetextEditUserIdAbstract', ]);
                $this->checkDeleteRow();
            }
            else
            {
                $message = $this->success->text("abstractEdit");
                if (!$this->db->selectFirstField('resource_text', 'resourcetextAddUserIdAbstract') && $userId)
                {
                    $updateArray["resourcetextAddUserIdAbstract"] = $userId;
                }
                $updateArray["resourcetextAbstract"] = $abstract;
                if ($userId)
                {
                    $updateArray["resourcetextEditUserIdAbstract"] = $userId;
                }
                $this->db->formatConditions(['resourcetextId' => $this->vars['id']]);
                $this->db->update('resource_text', $updateArray);
            }
        }
        // update resource timestamp
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['id']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        $this->notify();
        // lock reload
        $this->session->setVar("metadataLock", TRUE);
        $this->navigate($message);
    }
    /**
     * Edit note
     */
    private function noteEdit()
    {
        if (!array_key_exists('type', $this->vars) || !array_key_exists('id', $this->vars) ||
            !array_key_exists('sql', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        if ($this->session->getVar("metadataLock"))
        {
            $this->navigate($this->errors->text("done", "note"));
        }
        $note = trim($this->vars['text']);
        $userId = $this->session->getVar("setup_UserId");
        if ($this->vars['sql'] == 'insert')
        {
            if (!$note)
            {
                $this->navigate($this->errors->text("inputError", "missing"));
            }
            $message = $this->success->text("noteAdd");
            $fields[] = "resourcetextId";
            $values[] = $this->vars['id'];
            $fields[] = "resourcetextNote";
            $values[] = $note;
            if ($userId)
            {
                $fields[] = "resourcetextAddUserIdNote";
                $values[] = $userId;
            }
            $this->db->insert('resource_text', $fields, $values);
        }
        else
        { // update
            $this->db->formatConditions(['resourcetextId' => $this->vars['id']]);
            if (!$this->db->selectFirstField('resource_text', 'resourcetextAddUserIdNote') && $userId)
            {
                $updateArray["resourcetextAddUserIdNote"] = $userId;
            }
            // if noteText is empty, set fields to null
            if (!$note)
            {
                $message = $this->success->text("noteDelete");
                $this->db->updateNull('resource_text', ['resourcetextNote', 'resourcetextAddUserIdNote',
                    'resourcetextEditUserIdNote', ]);
                $this->checkDeleteRow();
            }
            else
            {
                $message = $this->success->text("noteEdit");
                $updateArray["resourcetextNote"] = $note;
                if ($userId)
                {
                    $updateArray["resourcetextEditUserIdNote"] = $userId;
                }
                $this->db->formatConditions(['resourcetextId' => $this->vars['id']]);
                $this->db->update('resource_text', $updateArray);
            }
        }
        // update resource timestamp
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['id']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        $this->notify();
        // lock reload
        $this->session->setVar("metadataLock", TRUE);
        $this->navigate($message);
    }
    /**
     * If all abstract, note and url fields are null, delete the row
     */
    private function checkDeleteRow()
    {
        $this->db->formatConditions(['resourcetextId' => $this->vars['id']]);
        $recordset = $this->db->select('resource_text', ['resourcetextAbstract', 'resourcetextNote',
            'resourcetextUrls', ]);
        $row = $this->db->fetchRow($recordset);
        if (!$row['resourcetextAbstract'] && !$row['resourcetextNote'] && !$row['resourcetextUrls'])
        {
            $this->db->formatConditions(['resourcetextId' => $this->vars['id']]);
            $this->db->delete('resource_text');
        }
    }
    /**
     * email notifications
     */
    private function notify()
    {
        include_once("core/modules/email/EMAIL.php");
        $email = new EMAIL();
        $email->notify($this->vars['id']);
    }
    /**
     * Navigate to last resource with message (success or error)
     *
     * @param mixed $message
     */
    private function navigate($message)
    {
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->vars['id'], $message);
        FACTORY_CLOSE::getInstance();
    }
}
