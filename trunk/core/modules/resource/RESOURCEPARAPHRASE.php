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
        $this->textqp->type = 'paraphrase';
        $this->textqp->editdisplay();
    }
    /**
     * write to the database
     *
     */
    public function edit()
    {
        $this->textqp->type = 'paraphrase';
        $addEdit = $this->textqp->edit();
        if ($addEdit == 'added') {
            $message = $this->success->text("paraphraseAdd");
        } elseif ($addEdit == 'edited') {
            $message = $this->success->text("paraphraseEdit");
        } elseif ($addEdit == 'deleted') {
            $message = $this->success->text("paraphraseDelete");
        } else {
        	$message = $addEdit; // $addEdit is an error message
        }
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['resourceId']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        // send back to view this resource with success message
        $this->navigate->resource($this->vars['resourceId'], $message);
    }
    /**
     * Ask for confirmation for paraphrase to be deleted
     */
    public function deleteInit()
    {
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
        if (!array_key_exists('resourcemetadataId', $this->vars) || !array_key_exists('summaryType', $this->vars)) {
    		$this->badInput->close($this->errors->text("inputError", "missing"));
    	}
        $this->textqp->delete($this->vars['summaryType']);
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['resourceId']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        // send back to view this resource with success message
        $this->navigate->resource($this->vars['resourceId'], $this->success->text("paraphraseDelete"));
    }
}
