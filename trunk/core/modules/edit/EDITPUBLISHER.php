<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * EDITPUBLISHER class
 */
class EDITPUBLISHER
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $publisher;
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

        $this->publisher = FACTORY_PUBLISHER::getInstance();

        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();

        GLOBALS::setTplVar('heading', $this->messages->text("heading", "edit", " (" .
            $this->messages->text("resources", "publisher") . ")"));
    }
    /**
     * check we are allowed to edit and load appropriate method
     *
     * @param string|FALSE $message
     */
    public function init($message = FALSE)
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        if (array_key_exists('PublisherType', $this->vars))
        {
            $publisherType = $this->vars['PublisherType'];
        }
        else
        {
            $publisherType = FALSE;
        }
        $publishers = $this->publisher->grabAll($publisherType);
        if (!$publishers)
        {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noPublishers'));

            return;
        }
        $pString = $message ? \HTML\p($message, "error", "center") : FALSE;
        $pString .= \FORM\formHeader('edit_EDITPUBLISHER_CORE');
        $pString .= \FORM\hidden("method", "edit");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValue(FALSE, "publisherIds", $publishers, 20));
        $pString .= \HTML\td($this->transferArrow());
        $pString .= \HTML\td(\HTML\div('publisherDiv', $this->displayPublisher(TRUE)));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
        $pString .= \FORM\formEnd();
        \AJAX\loadJavascript();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display interface to edit publisher
     *
     * @param bool $initialDisplay
     */
    public function displayPublisher($initialDisplay = FALSE)
    {
        $name = $location = $publisherId = FALSE;
        if (!$initialDisplay)
        {
            $this->db->formatConditions(['publisherId' => $this->vars['ajaxReturn']]);
            $recordset = $this->db->select('publisher', ['publisherName', 'publisherLocation']);
            $row = $this->db->fetchRow($recordset);
            $location = \HTML\dbToFormTidy($row['publisherLocation']);
            $name = \HTML\dbToFormTidy($row['publisherName']);
            $publisherId = $this->vars['ajaxReturn'];
        }
        $pString = \FORM\hidden("editPublisherId", $publisherId);
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\textInput($this->messages->text('resources', 'publisherName'), 'publisherName', $name, 30, 255));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text('resources', 'publisherLocation'),
            'publisherLocation',
            $location,
            30,
            255
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        if ($initialDisplay)
        {
            return $pString;
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $pString]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Write to the database
     */
    public function edit()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        if (!array_key_exists('editPublisherId', $this->vars) || !$this->vars['editPublisherId'])
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $name = array_key_exists('publisherName', $this->vars) ? trim($this->vars['publisherName']) : FALSE;
        $location = array_key_exists('publisherLocation', $this->vars) ?
            trim($this->vars['publisherLocation']) : FALSE;
        if (!$name & !$location)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        if ($publisherExistId = $this->publisher->checkExists($name, $location))
        {
            if ($publisherExistId != $this->vars['editPublisherId'])
            {
                return $this->confirmDuplicate($publisherExistId);
            }
        }
        if ($name)
        {
            $updateArray['publisherName'] = $name;
        }
        else
        {
            $nulls[] = 'publisherName';
        }
        if ($location)
        {
            $updateArray['publisherLocation'] = $location;
        }
        else
        {
            $nulls[] = 'publisherLocation';
        }
        if (isset($updateArray))
        {
            $this->db->formatConditions(['publisherId' => $this->vars['editPublisherId']]);
            $this->db->update('publisher', $updateArray);
        }
        if (isset($nulls))
        {
            $this->db->formatConditions(['publisherId' => $this->vars['editPublisherId']]);
            $this->db->updateNull('publisher', $nulls);
        }
        // remove cache files for keywords
        $this->db->deleteCache('cacheResourcePublishers');
        $this->db->deleteCache('cacheMetadataPublishers');
        // send back to editDisplay with success message
        $this->init($this->success->text("publisher"));
    }
    /**
     * Write to the database
     */
    public function editConfirm()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        if (!array_key_exists('editPublisherId', $this->vars) || !$this->vars['editPublisherId'])
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        if (!array_key_exists('editPublisherExistId', $this->vars) || !$this->vars['editPublisherExistId'])
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $editId = $this->vars['editPublisherId'];
        $existId = $this->vars['editPublisherExistId'];
        // Delete old publisher
        $this->db->formatConditions(['publisherId' => $editId]);
        $this->db->delete('publisher');
        // remove cache files for publishers
        $this->db->deleteCache('cacheResourcePublishers');
        $this->db->deleteCache('cacheMetadataPublishers');
        // Select all resources referencing this old publisher and replace reference with existing publisher
        $this->db->conditionSeparator = $this->db->or;
        $this->db->formatConditions(['resourcemiscPublisher' => $editId,
            'resourcemiscField1' => $editId, ]);
        $this->db->leftJoin('resource', 'resourceId', 'resourcemiscId');
        $recordset = $this->db->select(
            'resource_misc',
            ['resourcemiscId', 'resourcemiscPublisher', 'resourcemiscField1', 'resourceType']
        );
        while ($row = $this->db->fetchRow($recordset))
        {
            $updateArray = [];
            if ((($row['resourceType'] == 'proceedings_article') || ($row['resourceType'] == 'proceedings_article'))
                && $row['resourcemiscField1'])
            {
                $updateArray['resourcemiscField1'] = $existId;
            }
            elseif (($row['resourceType'] != 'proceedings_article') && ($row['resourceType'] != 'proceedings_article')
                && $row['resourcemiscPublisher'])
            {
                $updateArray['resourcemiscPublisher'] = $existId;
            }
            if (!empty($updateArray))
            {
                $this->db->formatConditions(['resourcemiscId' => $row['resourcemiscId']]);
                $this->db->update('resource_misc', $updateArray);
            }
        }
        // send back to editDisplay with success message
        $this->init($this->success->text("publisher"));
    }
    /**
     * transferArrow
     *
     * @return string
     */
    private function transferArrow()
    {
        $jsonArray = [];
        $jScript = 'index.php?action=edit_EDITPUBLISHER_CORE&method=displayPublisher';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'publisherIds',
            'targetDiv' => 'publisherDiv',
        ];
        $image = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);

        return $image;
    }
    /**
     * The new publisher equals one already in the database. Confirm that this edited one is to be removed and
     * all references to it replaced by the existing one.
     *
     * @param mixed $publisherExistId
     */
    private function confirmDuplicate($publisherExistId)
    {
        $pString = $this->errors->text("warning", "publisherExists");
        $pString .= \HTML\p($this->messages->text("misc", "publisherExists"));
        $pString .= \FORM\formHeader("edit_EDITPUBLISHER_CORE");
        $pString .= \FORM\hidden("editPublisherId", $this->vars['editPublisherId']);
        $pString .= \FORM\hidden("editPublisherExistId", $publisherExistId);
        $pString .= \FORM\hidden("method", 'editConfirm');
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")), FALSE, "right");
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
}
