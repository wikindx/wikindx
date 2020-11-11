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
 * EDITPUBLISHER class
 */
class EDITPUBLISHER
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
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

        $this->publisher = FACTORY_PUBLISHER::getInstance();

        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();

        GLOBALS::setTplVar('heading', $this->messages->text("heading", "edit", " (" .
            $this->messages->text("resources", "publisher") . ")"));
    }
    /**
     * check we are allowed to edit and load appropriate method
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        $initialPublisherId = FALSE;
        if (array_key_exists('PublisherType', $this->vars))
        {
            $publisherType = $this->vars['PublisherType'];
        }
        else
        {
            $publisherType = FALSE;
        }
        if (array_key_exists('message', $this->vars))
        {
            $pString = $this->vars['message'];
            if (array_key_exists('id', $this->vars))
            {
                $initialPublisherId = $this->vars['id'];
            }
        }
        elseif (is_array($message))
        { // error has occurred . . .
            $error = array_shift($message);
            $pString = \HTML\p($error, "error", "center");
            $initialPublisherId = array_shift($message);
        }
        else
        {
            $pString = $message;
        }
        $publishers = $this->publisher->grabAll($publisherType);
        if (!is_array($publishers))
        {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noPublishers'));

            return;
        }
        if (!$initialPublisherId)
        {
            foreach ($publishers as $id => $value)
            {
                $initialPublisherId = $id;

                break;
            }
        }
        $jsonArray = [];
        $jScript = 'index.php?action=edit_EDITPUBLISHER_CORE&method=displayPublisher';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'publisherIds',
            'targetDiv' => 'publisherDiv',
        ];
        $js = \AJAX\jActionForm('onchange', $jsonArray);
        $pString .= \FORM\formHeader('edit_EDITPUBLISHER_CORE');
        $pString .= \FORM\hidden("method", "edit");
        $pString .= \FORM\hidden("PublisherType", $this->vars['PublisherType']);
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectedBoxValue(FALSE, "publisherIds", $publishers, $initialPublisherId, 20, FALSE, $js));
        $pString .= \HTML\td(\HTML\div('publisherDiv', $this->displayPublisher(TRUE, $initialPublisherId)));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display interface to edit publisher
     *
     * @param bool $initialDisplay
     * @param int publisherId
     * @param mixed $publisherId
     */
    public function displayPublisher($initialDisplay = FALSE, $publisherId = FALSE)
    {
        $name = $location = FALSE;
        if ($initialDisplay)
        {
            $this->db->formatConditions(['publisherId' => $publisherId]);
        }
        else
        {
            $this->db->formatConditions(['publisherId' => $this->vars['ajaxReturn']]);
            $publisherId = $this->vars['ajaxReturn'];
        }
        $recordset = $this->db->select('publisher', ['publisherName', 'publisherLocation']);
        $row = $this->db->fetchRow($recordset);
        $location = \HTML\dbToFormTidy($row['publisherLocation']);
        $name = \HTML\dbToFormTidy($row['publisherName']);
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
        $this->validateInput();
        $name = \UTF8\mb_trim($this->vars['publisherName']);
        $location = \UTF8\mb_trim($this->vars['publisherLocation']);
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
        $message = rawurlencode($this->success->text("publisher"));
        header("Location: index.php?action=edit_EDITPUBLISHER_CORE&method=init&message=$message&id=" . $this->vars['editPublisherId'] .
            "&PublisherType=" . $this->vars['PublisherType']);
        die;
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
        $message = rawurlencode($this->success->text("publisher"));
        header("Location: index.php?action=edit_EDITPUBLISHER_CORE&method=init&message=$message&id=$existId&PublisherType=" .
            $this->vars['PublisherType']);
        die;
    }
    /**
     * The new publisher equals one already in the database. Confirm that this edited one is to be removed and
     * all references to it replaced by the existing one.
     */
    public function confirmDuplicate()
    {
        $pString = $this->errors->text("warning", "publisherExists");
        $pString .= \HTML\p($this->messages->text("misc", "publisherExists"));
        $pString .= \FORM\formHeader("edit_EDITPUBLISHER_CORE");
        $pString .= \FORM\hidden("PublisherType", $this->vars['PublisherType']);
        $pString .= \FORM\hidden("editPublisherId", $this->vars['editId']);
        $pString .= \FORM\hidden("editPublisherExistId", $this->vars['existId']);
        $pString .= \FORM\hidden("method", 'editConfirm');
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")), FALSE, "right");
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Validate the form input
     */
    private function validateInput()
    {
        // First check for input
        $error = '';
        if (!array_key_exists('editPublisherId', $this->vars) || !$this->vars['editPublisherId'])
        {
            $error = $this->errors->text("inputError", "missing");
        }
        $name = array_key_exists('publisherName', $this->vars) ? \UTF8\mb_trim($this->vars['publisherName']) : FALSE;
        $location = array_key_exists('publisherLocation', $this->vars) ? \UTF8\mb_trim($this->vars['publisherLocation']) : FALSE;
        if (!$name && !$location)
        {
            $error = $this->errors->text("inputError", "missing");
        }
        if ($error)
        {
            $this->badInput->close($error, $this, ['init', $this->vars['editPublisherId']]);
        }
        // Then check for duplicates
        if ($existId = $this->publisher->checkExists($name, $location))
        {
            if ($existId != $this->vars['editPublisherId'])
            {
                $editId = $this->vars['editPublisherId'];
                header("Location: index.php?action=edit_EDITPUBLISHER_CORE&method=confirmDuplicate&editId=$editId&existId=$existId&PublisherType=" .
                    $this->vars['PublisherType']);
                die;
            }
        }
    }
}
