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
 * RESOURCEPARAPHRASE class
 *
 * Deal with resource's paraphrases
 */
class RESOURCEPARAPHRASE
{
    private $gatekeep;
    private $db;
    private $vars;
    private $textqp;
    private $session;
    private $messages;
    private $errors;
    private $success;
    private $navigate;
    private $badInput;

    // Constructor
    public function __construct()
    {
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->gatekeep->init();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "TEXTQP.php"]));
        $this->textqp = new TEXTQP();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();

        $this->navigate = FACTORY_NAVIGATE::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "paraphrases"));
        if (!array_key_exists('resourceId', $this->vars) || !$this->vars['resourceId'] ||
            !array_key_exists('method', $this->vars) || !$this->vars['method']) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $function = $this->vars['method'];
        if (!method_exists($this, $function)) {
            $this->navigate->resource($this->vars['resourceId'], $this->errors->text("inputError", "invalid"));
        }
    }
    /**
     * display the editing form
     *
     * Only the original poster can edit the paraphrase and only the original posters can edit their comments
     */
    public function paraphraseEdit()
    {
        $this->checkInput();
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $pString = \FORM\formHeader('resource_RESOURCEPARAPHRASE_CORE');
        $pString .= \FORM\hidden('method', 'edit');
        $pString .= $tinymce->loadMetadataTextarea(['Text', 'Comment']);
        $metadata = $this->textqp->editdisplay('paraphrase');
        $metadata['hidden'] = $pString;
        $metadata['formfoot'] = \FORM\formEnd();
        GLOBALS::setTplVar('metadata', $metadata);
        unset($metadata);
        $this->session->delVar("resourceParaphraseLock".$this->vars['resourceId']."-".$this->vars['resourcemetadataId']);
    }
    /**
     * write to the database
     *
     * if there is no 'paraphraseId' input, we are adding a new paraphrase.  Otherwise, editing one.
     */
    public function edit()
    {
        if ($this->session->getVar("resourceParaphraseLock".$this->vars['resourceId']."-".$this->vars['resourcemetadataId'])) {
            $this->badInput->close($this->errors->text("done", "paraphrase"));
        }
        $this->checkInput();
        if (!$addEdit = $this->textqp->edit('paraphrase')) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        if ($addEdit == 'added') {
            $message = $this->success->text("paraphraseAdd");
        } elseif ($addEdit == 'edited') {
            $message = $this->success->text("paraphraseEdit");
        } elseif ($addEdit == 'deleted') {
            $message = $this->success->text("paraphraseDelete");
        }
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['resourceId']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        // lock reload
        $this->session->setVar("resourceParaphraseLock".$this->vars['resourceId']."-".$this->vars['resourcemetadataId'], TRUE);
        // send back to view this resource with success message
        $this->navigate->resource($this->vars['resourceId'], $message);
    }
    /**
     * Ask for confirmation for paraphrase to be deleted
     */
    public function deleteInit()
    {
        $this->session->delVar("resourceParaphraseLock".$this->vars['resourceId']."-".$this->vars['resourcemetadataId']);
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "paraphraseDelete"));
        $pString = \FORM\formHeader('resource_RESOURCEPARAPHRASE_CORE');
        $pString .= \FORM\hidden("method", 'delete');
        $pString .= \FORM\hidden("resourceId", $this->vars['resourceId']);
        $pString .= \FORM\hidden("resourcemetadataId", $this->vars['resourcemetadataId']);
        $pString .= \FORM\hidden("summaryType", 'resourcesummaryParaphrases');
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Confirm")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete the musing and all peripheral data
     *
     */
    public function delete()
    {
        if ($this->session->getVar("resourceParaphraseLock".$this->vars['resourceId']."-".$this->vars['resourcemetadataId'])) {
            $this->badInput->close($this->errors->text("done", "paraphrase"));
        }
    	if (!array_key_exists('resourcemetadataId', $this->vars) || !array_key_exists('summaryType', $this->vars)) {
    		$this->badInput->close($this->errors->text("inputError", "missing"));
    	}
        $this->textqp->delete($this->vars['summaryType']);
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['resourceId']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        // lock reload
        $this->session->setVar("resourceParaphraseLock".$this->vars['resourceId']."-".$this->vars['resourcemetadataId'], TRUE);
        // send back to view this resource with success message
        $this->navigate->resource($this->vars['resourceId'], $this->success->text("paraphraseDelete"));
    }
    /**
     * Check we have appropriate input.
     *
     * Page and comment are optional
     */
    private function checkInput()
    {
        if (!array_key_exists('resourceId', $this->vars) || !$this->vars['resourceId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
    }
}
