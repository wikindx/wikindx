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
 * RESOURCEQUOTE class
 *
 * Deal with resource's quotes
 */
class RESOURCEQUOTE
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
    private $icons;
    private $return;
    private $browserTabID = FALSE;

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
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->navigate = FACTORY_NAVIGATE::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
        if (!array_key_exists('resourceId', $this->vars) || !$this->vars['resourceId'] ||
            !array_key_exists('method', $this->vars) || !$this->vars['method'])
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $function = $this->vars['method'];
        if (!method_exists($this, $function))
        {
            $this->navigate->resource($this->vars['resourceId'], "inputError_invalid", TRUE);
        }
        $this->return = '&nbsp;&nbsp;' . \HTML\a(
            $this->icons->getClass("edit"),
            $this->icons->getHTML("return"),
            'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->vars['resourceId'] . '&browserTabID=' . $this->browserTabID
        );
    }
    /**
     * display the editing form
     *
     * Only the original posters can edit their comments
     */
    public function quoteEdit()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "quotes") . $this->return);
        $this->textqp->type = 'quote';
        $this->textqp->editdisplay();
    }
    /**
     * write to the database
     */
    public function edit()
    {
        $this->textqp->type = 'quote';
        $addEdit = $this->textqp->edit();
        if ($addEdit == 'added')
        {
            $messages[] = $this->success->text("quoteAdd");
        }
        elseif ($addEdit == 'edited')
        {
            $messages[] = $this->success->text("quoteEdit");
        }
        elseif ($addEdit == 'deleted')
        {
            $messages[] = $this->success->text("quoteDelete");
        }
        else
        {
            $messages[] = $addEdit; // $addEdit is an error message
        }
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['resourceId']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        // send back to view this resource with success message
        $this->navigate->resource($this->vars['resourceId'], $messages);
    }
    /**
     * Ask for confirmation for quote to be deleted
     */
    public function deleteInit()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "quoteDelete") . $this->return);
        $pString = \FORM\formHeader('resource_RESOURCEQUOTE_CORE');
        $pString .= \FORM\hidden("method", 'delete');
        $pString .= \FORM\hidden("resourceId", $this->vars['resourceId']);
        $pString .= \FORM\hidden("resourcemetadataId", $this->vars['resourcemetadataId']);
        $pString .= \FORM\hidden("browserTabID", $this->browserTabID);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Confirm")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete the quote and all peripheral data
     */
    public function delete()
    {
        if (!array_key_exists('resourcemetadataId', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $this->textqp->delete();
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['resourceId']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        // send back to view this resource with success message
        $this->navigate->resource($this->vars['resourceId'], "quoteDelete");
    }
}
