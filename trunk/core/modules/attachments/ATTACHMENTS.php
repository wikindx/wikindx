<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * ATTACHMENTS class
 */
class ATTACHMENTS
{
    private $db;
    private $vars;
    private $badInput;
    private $session;
    private $errors;
    private $messages;
    private $success;
    private $gatekeep;
    private $attachment;
    private $dateObject;
    private $resourceId;
    private $embargoArray = [];
    private $embargoNew;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->dateObject = FACTORY_DATE::getInstance();
        $this->attachment = FACTORY_ATTACHMENT::getInstance();
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
     * delete all attachments
     */
    public function deleteConfirmAll()
    {
        if (!array_key_exists('deleteAll', $this->vars))
        {
            $navigate = FACTORY_NAVIGATE::getInstance();
            $navigate->resource($this->resourceId, $this->errors->text("inputError", "missing"));

            return;
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", $this->messages->text('misc', 'delete')));
        $pString = \FORM\formHeader("attachments_ATTACHMENTS_CORE");
        $pString .= \FORM\hidden('function', 'delete');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        $pString .= $this->messages->text('resources', 'deleteConfirmAttach') . ':' . BR;
        $this->db->formatConditions(["resourceattachmentsResourceId" => $this->resourceId]);
        $recordSet = $this->db->select(
            ["resource_attachments"],
            ['resourceattachmentsHashFilename', 'resourceattachmentsFileName']
        );
        while ($row = $this->db->fetchRow($recordSet))
        {
            $pString .= \FORM\checkBox(FALSE, 'attachmentDelete_' . $row['resourceattachmentsHashFilename'], TRUE) .
                '&nbsp;' . $row['resourceattachmentsFileName'] . BR;
        }
        $pString .= \HTML\p('&nbsp;' . BR . \FORM\formSubmit($this->messages->text("submit", "Confirm")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /** 
     *download an attachment to a user
     */
    public function downloadAttachment()
    {
        $dirName = WIKINDX_DIR_DATA_ATTACHMENTS;
        $hash = $this->vars['filename'];
        $this->db->formatConditions(['resourceattachmentsId' => $this->vars['id']]);
        $recordset = $this->db->select(
            'resource_attachments',
            ['resourceattachmentsFileType', 'resourceattachmentsFileSize',
                'resourceattachmentsHashFilename', 'resourceattachmentsFileName', 'resourceattachmentsTimestamp', ]
        );
        $row = $this->db->fetchRow($recordset);
        $type = $row['resourceattachmentsFileType'];
        $size = $row['resourceattachmentsFileSize'];
        $filename = $row['resourceattachmentsFileName'];
        $lastmodified = date('r', strtotime($row['resourceattachmentsTimestamp']));
        unset($row);
        if (file_exists($dirName . "/" . $hash) === FALSE)
        {
            $this->badInput->closeType = 'closePopup';
            $this->badInput->close($this->errors->text("file", "missing"));
            die;
        }
        FILE\setHeaders($type, $size, $filename, $lastmodified);
        FILE\readfile_chunked($dirName . "/" . $hash);
        $this->attachment->incrementDownloadCounter($this->vars['id']);
        die;
    }
    /**
     * Initial editing/adding form
     */
    private function editInit()
    {
        $this->session->delVar('attachLock');
        $fields = $this->attachment->listFiles($this->resourceId);
        if (!empty($fields))
        { // attachments exist for this resource
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", '(' . $this->messages->text('misc', 'edit') . ')'));
            GLOBALS::addTplVar('content', $this->fileAttachEdit($fields));
        }
        else
        { // add a new attachment
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", '(' . $this->messages->text('misc', 'add') . ')'));
            GLOBALS::addTplVar('content', HTML\p($this->messages->text("resources", "fileAttachments")));
            GLOBALS::addTplVar('content', $this->fileAttachAdd());
        }
    }
    /**
     * add an attachment
     */
    private function add()
    {
        if ($this->session->getVar('attachLock'))
        {
            $this->badInput->close($this->errors->text("done", "attachAdd"));
        }
        $this->getEmbargo();
        $navigate = FACTORY_NAVIGATE::getInstance();
        if (!$this->storeFile())
        { // FALSE if attachment already exists
            $navigate->resource($this->resourceId, $this->errors->text("file", "attachmentExists"));

            return;
        }
        // Lock re-uploading
        $this->session->setVar('attachLock', TRUE);
        // send back to view this resource with success message
        $navigate->resource($this->resourceId, $this->success->text("attachAdd"));
    }
    /**
     * drag and drop multiple attachments.
     * NB – some lack of control over errors given the javascript . . .
     * Navigation controlled in the javascript
     */
    private function addDragAndDrop()
    {
        $this->getEmbargo();
        if (!$this->storeFile())
        { // FALSE if attachment already exists
            return;
        }
    }
    /**
     * add multiple attachments
     *
     * (not the drag-and-drop type which go singly through addDragAndDrop() above)
     */
    private function addMultipleFiles()
    {
        if ($this->session->getVar('attachLock'))
        {
            $this->badInput->close($this->errors->text("done", "attachAdd"));
        }
        $this->getEmbargo();
        $navigate = FACTORY_NAVIGATE::getInstance();
        if (!$this->storeFile(TRUE))
        { // FALSE if attachment already exists
            $navigate->resource($this->resourceId, $this->errors->text("file", "attachmentExists"));

            return;
        }
        // Lock re-uploading
        $this->session->setVar('attachLock', TRUE);
        // send back to view this resource with success message
        $navigate->resource($this->resourceId, $this->success->text("attachAdd"));
    }
    /**
     * edit attachments
     */
    private function edit()
    {
        // Get primary attachment if multiple attachments
        $primary = array_key_exists('attachmentPrimary', $this->vars) ? $this->vars['attachmentPrimary'] : FALSE;
        // find any files to edit and files to delete
        foreach ($this->vars as $key => $var)
        {
            $split = UTF8::mb_explode('_', $key);
            if ($split[0] == 'attachmentEdit')
            {
                $edits[$split[1]] = $var;
            }
            if ($key == 'embargo')
            {
                $this->getEmbargo();
            }
            elseif ($split[0] == 'embargo')
            { // checkbox
                $this->getEmbargo($split[1]);
            }
            if ($split[0] == 'attachmentDelete')
            {
                $deletes[$split[1]] = $var;
            }
            if ($split[0] == 'attachmentDescription')
            {
                $descriptions[$split[1]] = $var;
            }
        }
        $message = FALSE;
        // Edit files
        if (isset($edits))
        {
            foreach ($edits as $hash => $filename)
            {
                $updateArray = [];
                $updateArray['resourceattachmentsFileName'] = $filename;
                if (array_key_exists($hash, $this->embargoArray))
                {
                    $updateArray['resourceattachmentsEmbargo'] = 'Y';
                    $updateArray['resourceattachmentsEmbargoUntil'] = $this->embargoArray[$hash];
                }
                else
                {
                    $updateArray['resourceattachmentsEmbargo'] = 'N';
                }
                if ($descriptions[$hash])
                {
                    $updateArray['resourceattachmentsDescription'] = $descriptions[$hash];
                }
                else
                {
                    $this->db->formatConditions(["resourceattachmentsResourceId" => $this->resourceId]);
                    $this->db->formatConditions(["resourceattachmentsHashFilename" => $hash]);
                    $this->db->updateNull('resource_attachments', 'resourceattachmentsDescription');
                }
                $this->db->formatConditions(["resourceattachmentsResourceId" => $this->resourceId]);
                $this->db->formatConditions(["resourceattachmentsHashFilename" => $hash]);
                $this->db->update('resource_attachments', $updateArray);
            }
            $message = $this->success->text("attachEdit");
        }
        // set primary attachment
        $this->setPrimaryAttachment($primary);
        $navigate = FACTORY_NAVIGATE::getInstance();
        // Store any new file
        if (array_key_exists('file', $_FILES) && $_FILES['file']['tmp_name'])
        {
            if ($this->session->getVar('attachLock'))
            {
                $message = $this->errors->text("done", "attachAdd");
            }
            if (!$this->storeFile())
            { // FALSE if attachment already exists
                $navigate->resource($this->resourceId, $this->errors->text("file", "attachmentExists"));

                return;
            }
            // Lock re-uploading
            $this->session->setVar('attachLock', TRUE);
        }
        if (isset($deletes))
        {
            $this->deleteConfirm($deletes);

            return;
        }
        // send back to view this resource with success message (deleteConfirm breaks out before this)
        $navigate->resource($this->resourceId, $this->success->text("attachEdit"));
    }
    /**
     * Grab and sort embargo date
     */
    private function getEmbargo($hash = FALSE)
    {
        if ($hash)
        {
            $arrayIndex = $hash;
            $hash = "_$hash";
        }
        // date comes in as 'yyyy-mm-dd' (but displayed on web form as 'dd / mm / yyyy').
        // all three fields must have a valid value else $this->vars["date"] is FALSE
        if (array_key_exists("date", $this->vars) && $this->vars["date"])
        {
            list($year, $month, $day) = $this->dateObject->splitDate($this->vars["date"]);
        }
        else
        {
            return;
        }
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        if ($timestamp === FALSE)
        { // exceed UNIX timestamp capacity
            if ($hash)
            {
                $this->embargoArray[$arrayIndex] = "$year-$month-$day 00:00:00";
            }
            else
            {
                $this->embargoNew = "$year-$month-$day 00:00:00";
            }

            return;
        }
        elseif ($timestamp <= time())
        {
            return;
        }
        if ($hash)
        {
            $this->embargoArray[$arrayIndex] = $this->db->formatTimestamp($timestamp);
        }
        else
        {
            $this->embargoNew = $this->db->formatTimestamp($timestamp);
        }
    }
    /**
     *set primary attachment
     */
    private function setPrimaryAttachment($primary)
    {
        $this->db->formatConditions(["resourceattachmentsResourceId" => $this->resourceId]);
        $recordSet = $this->db->update('resource_attachments', ['resourceattachmentsPrimary' => 'N']);
        $this->db->formatConditions(['resourceattachmentsResourceId' => $this->resourceId]);
        $this->db->formatConditions(['resourceattachmentsHashFilename' => $primary]);
        $recordSet = $this->db->update('resource_attachments', ['resourceattachmentsPrimary' => 'Y']);
    }
    /**
     * confirm delete attachments
     */
    private function deleteConfirm($deletes)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", $this->messages->text('misc', 'delete')));
        $pString = \FORM\formHeader("attachments_ATTACHMENTS_CORE");
        $pString .= \FORM\hidden('function', 'delete');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        $pString .= $this->messages->text('resources', 'deleteConfirmAttach') . ':' . BR;
        $this->db->formatConditions(["resourceattachmentsResourceId" => $this->resourceId]);
        $recordSet = $this->db->select(
            ["resource_attachments"],
            ['resourceattachmentsHashFilename', 'resourceattachmentsFileName']
        );
        while ($row = $this->db->fetchRow($recordSet))
        {
            if (array_key_exists($row['resourceattachmentsHashFilename'], $deletes))
            {
                $pString .= \FORM\checkBox(FALSE, 'attachmentDelete_' . $row['resourceattachmentsHashFilename'], TRUE) .
                    '&nbsp;' . $row['resourceattachmentsFileName'] . BR;
            }
        }
        $pString .= \HTML\p('&nbsp;' . BR . \FORM\formSubmit($this->messages->text("submit", "Confirm")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * delete attachments
     */
    private function delete()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", $this->messages->text('misc', 'delete')));
        foreach ($this->vars as $key => $var)
        {
            $split = UTF8::mb_explode('_', $key);
            if ($split[0] == 'attachmentDelete')
            {
                $deletes[] = $split[1];
            }
        }
        // remove reference from this resource first
        foreach ($deletes as $hash)
        {
            $this->db->formatConditions(["resourceattachmentsResourceId" => $this->resourceId]);
            $this->db->formatConditions(["resourceattachmentsHashFilename" => $hash]);
            $this->db->delete('resource_attachments');
            // remove file from attachments and cache directories if there's no reference to it in any other resource
            $this->db->formatConditions(["resourceattachmentsHashFilename" => $hash]);
            if (!$this->db->numRows($this->db->select('resource_attachments', 'resourceattachmentsHashFilename')))
            {
                @unlink(WIKINDX_DIR_DATA_ATTACHMENTS . DIRECTORY_SEPARATOR . $hash);
                @unlink(WIKINDX_DIR_CACHE_ATTACHMENTS . DIRECTORY_SEPARATOR . $hash);
            }
        }
        // send back to view this resource with success message
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->resource($this->resourceId, $this->success->text("attachDelete"));
    }
    /**
     * Store attachment
     *
     * @param bool $multiple
     *
     * @return bool
     */
    private function storeFile($multiple = FALSE)
    {
        $varFileName = array_key_exists('fileName', $this->vars) ? $this->vars['fileName'] : FALSE;
        if ($multiple)
        {
            $filesArray = FILE\fileUpload($varFileName, $multiple);
            if (empty($filesArray))
            {
                $this->badInput->close($this->errors->text("file", "upload"));
            }
            foreach ($filesArray as $array)
            {
                // $array[0] = file name
                // $array[1] = hash name
                // $array[2] = file type
                // $array[3] = file size
                // $array[4] = index of array in $_FILES['file']
                if (!$array[1])
                {
                    $this->badInput->close($this->errors->text("file", "upload"));
                }
                if (!$this->actuallyStoreFile($array[0], $array[1], $array[2], $array[3], $array[4]))
                {
                    return FALSE;
                }
            }
        }
        else
        {
            list($filename, $hash, $type, $size) = FILE\fileUpload($varFileName);
            if (!$hash)
            {
                $this->badInput->close($this->errors->text("file", "upload"));
            }
            if (!$this->actuallyStoreFile($filename, $hash, $type, $size, FALSE))
            {
                return FALSE;
            }
        }

        return TRUE;
    }
    /**
     * Actually store the file(s)
     *
     * @param string $filename
     * @param string $hash
     * @param string $type
     * @param int $size
     * @param int $index
     *
     * @return bool
     */
    private function actuallyStoreFile($filename, $hash, $type, $size, $index)
    {
        if (!FILE\fileStore(WIKINDX_DIR_DATA_ATTACHMENTS, $hash, $index))
        {
            $this->badInput->close($this->errors->text("file", "upload"));
        }
        // Convert to text and store in the cache directory if of PDF, DOC or DOCX type
        $fileNameCache = WIKINDX_DIR_CACHE_ATTACHMENTS . DIRECTORY_SEPARATOR . $hash;
        if ((($type == WIKINDX_MIMETYPE_PDF) || ($type == WIKINDX_MIMETYPE_DOCX) || ($type == WIKINDX_MIMETYPE_DOC))
            && !file_exists($fileNameCache))
        {
            $fileName = WIKINDX_DIR_DATA_ATTACHMENTS . DIRECTORY_SEPARATOR . $hash;
            include_once("core/modules/list/FILETOTEXT.php");
            $ftt = new FILETOTEXT();
            @file_put_contents($fileNameCache, $ftt->convertToText($fileName, $type)); // we do not halt on failure
        }
        $this->db->formatConditions(["resourceattachmentsHashFilename" => $hash]);
        $this->db->formatConditions(["resourceattachmentsResourceId" => $this->resourceId]);
        $recordSet = $this->db->select('resource_attachments', 'resourceattachmentsId');
        if ($this->db->numRows($recordSet))
        { // attachment already part of this resource
            return FALSE;
        }
        else
        {	// insert
            $fields[] = 'resourceattachmentsResourceId';
            $values[] = $this->resourceId;
            $fields[] = 'resourceattachmentsHashFilename';
            $values[] = $hash;
            $fields[] = 'resourceattachmentsFileName';
            $values[] = $filename;
            $fields[] = 'resourceattachmentsFileType';
            $values[] = $type;
            $fields[] = 'resourceattachmentsFileSize';
            $values[] = $size;
            if (array_key_exists('embargo', $this->vars))
            {
                $fields[] = 'resourceattachmentsEmbargo';
                $values[] = 'Y';
                $fields[] = 'resourceattachmentsEmbargoUntil';
                $values[] = $this->embargoNew;
            }
            else
            {
                $fields[] = 'resourceattachmentsEmbargoUntil';
                $values[] = '2012-01-01 01:01:01';
            }
            $field[] = 'resourceattachmentsTimestamp';
            $value[] = '2012-01-01 01:01:01';
            if (array_key_exists('fileDescription', $this->vars) && $this->vars['fileDescription'])
            {
                $fields[] = 'resourceattachmentsDescription';
                $values[] = $this->vars['fileDescription'];
            }
            $this->db->insert('resource_attachments', $fields, $values);
        }

        return TRUE;
    }
    /**
     * form for editing, deleting and adding (another) attachments
     *
     * @param array $fields
     *
     * @return string
     */
    private function fileAttachEdit($fields)
    {
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $tinyEditors[] = 'fileDescription';
        foreach ($fields as $hash => $null)
        {
            $tinyEditors[] = $hash;
        }
        $maxSize = FILE\fileMaxSize();
        $this->session->setVar('attachMaxSize', $maxSize);
        // Form elements for adding another attachment
        $pString = \HTML\tableStart('generalTable left');
        $pString .= \HTML\trStart();
        // Quick and dirty multiple upload
        GLOBALS::addTplVar('scripts', '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/modules/attachments/multipleUpload.js"></script>');
        GLOBALS::addTplVar('scripts', '<script type="text/javascript">var rId = ' . $this->resourceId .
            '; var maxSize = ' . $maxSize . '; </script>');
        $error = base64_encode($this->errors->text("file", "upload"));
        $closeUrl = 'index.php?action=resource_RESOURCEVIEW&id=' . $this->resourceId . '&message=' . $error;
        GLOBALS::addTplVar('scripts', '<script type="text/javascript">var errorUrl = "' . $closeUrl . '"; </script>');
        $success = base64_encode($this->success->text("attachAdd"));
        $closeUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&message=' . $success;
        GLOBALS::addTplVar('scripts', '<script type="text/javascript">var successUrl = "' . $closeUrl . '"; </script>');
        $td = '<div id="uploader">' . $this->messages->text("resources", "fileAttachDragAndDrop") . '</div>';
        GLOBALS::addTplVar('scripts', '<script type="text/javascript">var fallback = "' .
            $this->messages->text("resources", "fileAttachFallback") . '"; </script>');
        $td .= '<div id="fallback"></div>';
        $pString .= \HTML\td($td, 'attachmentBorder');
        // Single upload
        $td = $tinymce->loadBasicTextArea($tinyEditors, 400);
        $td .= \FORM\formMultiHeader("attachments_ATTACHMENTS_CORE");
        $td .= \FORM\hidden('function', 'add');
        $td .= \FORM\hidden('resourceId', $this->resourceId);
        $td .= \FORM\hidden("MAX_FILE_SIZE", $maxSize);
        $td .= \FORM\fileUpload(
            $this->messages->text("resources", "fileAttach"),
            "file",
            50
        );
        $td .= \HTML\p(\FORM\textInput($this->messages->text("resources", "fileName"), "fileName"));
        $td .= $this->embargoForm();
        $td .= \HTML\p(\FORM\textareaInput(
            $this->messages->text('resources', 'attachmentDescription'),
            "fileDescription",
            FALSE,
            60
        ), '', 3);
        $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));
        $td .= \FORM\formEnd();
        $pString .= \HTML\td($td, 'attachmentBorder');
        // Multiple file upload with embargo
        $td = \FORM\formMultiHeader("attachments_ATTACHMENTS_CORE");
        $td .= \FORM\hidden('function', 'addMultipleFiles');
        $td .= \FORM\hidden('resourceId', $this->resourceId);
        $td .= \FORM\hidden("MAX_FILE_SIZE", $maxSize);
        $td .= \FORM\fileUploadMultiple($this->messages->text("resources", "fileAttachMultiple"), "file[]", 50);
        $td .= $this->embargoForm(FALSE, TRUE);
        $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));
        $td .= \FORM\formEnd();
        $pString .= \HTML\td($td, 'attachmentBorder');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\hr();
        $pString .= \HTML\h($this->messages->text('resources', 'currentAttachments'), FALSE, 4);
        $numFiles = count($fields);
        $index = $count = 1;
        // Delete all attachments if more than 1
        if ($numFiles > 1)
        {
            $pString .= \HTML\tableStart('generalTable left');
            $pString .= \HTML\trStart();
            $td = \FORM\formMultiHeader("attachments_ATTACHMENTS_CORE");
            $td .= \FORM\hidden('function', 'deleteConfirmAll');
            $td .= \FORM\hidden('resourceId', $this->resourceId);
            $td .= $this->messages->text('misc', 'fileAttachDeleteAll') . ':&nbsp;' . \FORM\checkBox(FALSE, "deleteAll");
            $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td);
            $pString .= \HTML\td('&nbsp;');
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
        }
        $pString .= \FORM\formMultiHeader("attachments_ATTACHMENTS_CORE");
        $pString .= \FORM\hidden('function', 'edit');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        $pString .= \FORM\hidden("MAX_FILE_SIZE", $maxSize);
        // Edit individual attachments
        $pString .= \HTML\tableStart('generalTable left');
        $pString .= \HTML\trStart();
        foreach ($fields as $hash => $fileName)
        {
            $td = \FORM\textInput(
                $this->messages->text('resources', 'fileName'),
                "attachmentEdit_$hash",
                $fileName,
                50
            );
            $td1 = $this->messages->text('misc', 'delete') . ':&nbsp;' . \FORM\checkBox(
                FALSE,
                "attachmentDelete_$hash"
            );
            if ($numFiles > 1)
            {
                $td1 .= '&nbsp;&nbsp;' . $this->messages->text('resources', 'primaryAttachment') . ':&nbsp;';
                if (($index == 1) && !$this->attachment->primary)
                {
                    $td1 .= \FORM\radioButton(FALSE, 'attachmentPrimary', $hash, TRUE);
                    ++$index;
                }
                elseif ($this->attachment->primary == $hash)
                {
                    $td1 .= \FORM\radioButton(FALSE, 'attachmentPrimary', $hash, TRUE);
                }
                else
                {
                    $td1 .= \FORM\radioButton(FALSE, 'attachmentPrimary', $hash);
                }
            }
            $td .= \HTML\p($td1);
            $td .= $this->embargoForm($hash);
            $this->db->formatConditions(['resourceattachmentsHashFilename' => $hash]);
            $this->db->formatConditions(['resourceattachmentsResourceId' => $this->resourceId]);
            $desc = $this->db->selectFirstField('resource_attachments', 'resourceattachmentsDescription');
            $td .= \HTML\p(\FORM\textareaInput(
                $this->messages->text('resources', 'attachmentDescription'),
                "attachmentDescription_$hash",
                \HTML\nlToHtml($desc),
                60
            ), '', 2);
            $pString .= \HTML\td($td, 'attachmentBorder');
            if ($count % 2 === 0)
            {
                $pString .= \HTML\trEnd();
                if ($count != $numFiles)
                {
                    $pString .= \HTML\trStart();
                }
            }
            ++$count;
        }
        if ($count % 2 === 0)
        {
            $pString .= \HTML\trEnd();
        }
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * form input for adding an attachment
     *
     * @return string
     */
    private function fileAttachAdd()
    {
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $maxSize = FILE\fileMaxSize();
        $this->session->setVar('attachMaxSize', $maxSize);
        // Three ways to do this:
        // Quick and dirty multiple upload
        GLOBALS::addTplVar('scripts', '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/modules/attachments/multipleUpload.js"></script>');
        GLOBALS::addTplVar('scripts', '<script type="text/javascript">var rId = ' . $this->resourceId .
            '; var maxSize = ' . $maxSize . '; </script>');
        $error = base64_encode($this->errors->text("file", "upload"));
        $closeUrl = 'index.php?action=resource_RESOURCEVIEW&id=' . $this->resourceId . '&message=' . $error;
        GLOBALS::addTplVar('scripts', '<script type="text/javascript">var errorUrl = "' . $closeUrl . '"; </script>');
        $success = base64_encode($this->success->text("attachAdd"));
        $closeUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&message=' . $success;
        GLOBALS::addTplVar('scripts', '<script type="text/javascript">var successUrl = "' . $closeUrl . '"; </script>');
        $td1 = '<div id="uploader">' . $this->messages->text("resources", "fileAttachDragAndDrop") . '</div>';
        GLOBALS::addTplVar('scripts', '<script type="text/javascript">var fallback = "' .
            $this->messages->text("resources", "fileAttachFallback") . '"; </script>');
        $td1 .= '<div id="fallback"></div>';
        // Single file upload with filename, description and embargo
        $td2 = \FORM\formMultiHeader("attachments_ATTACHMENTS_CORE");
        $td2 .= \FORM\hidden('function', 'add');
        $td2 .= \FORM\hidden('resourceId', $this->resourceId);
        $td2 .= \FORM\hidden("MAX_FILE_SIZE", $maxSize);
        $td2 .= \FORM\fileUpload($this->messages->text("resources", "fileAttach"), "file", 50);
        $td2 .= \HTML\p(\FORM\textInput($this->messages->text("resources", "fileName"), "fileName"));
        $td2 .= $this->embargoForm();
        $td2 .= $tinymce->loadBasicTextArea(["fileDescription"], 400);
        $td2 .= \HTML\p(\FORM\textareaInput(
            $this->messages->text('resources', 'attachmentDescription'),
            "fileDescription",
            FALSE,
            60
        ), FALSE, FALSE, FALSE, 3);
        $td2 .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")), '', 3);
        $td2 .= \FORM\formEnd();
        // Multiple file upload with embargo
        $td3 = \FORM\formMultiHeader("attachments_ATTACHMENTS_CORE");
        $td3 .= \FORM\hidden('function', 'addMultipleFiles');
        $td3 .= \FORM\hidden('resourceId', $this->resourceId);
        $td3 .= \FORM\hidden("MAX_FILE_SIZE", $maxSize);
        $td3 .= \FORM\fileUploadMultiple($this->messages->text("resources", "fileAttachMultiple"), "file[]", 50);
        $td3 .= $this->embargoForm(FALSE, TRUE);
        $td3 .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")), '', 3);
        $td3 .= \FORM\formEnd();
        $pString = \HTML\tableStart();
        $pString .= \HTML\trStart('top');
        $pString .= \HTML\td($td1, 'attachmentBorder');
        $pString .= \HTML\td($td2, 'attachmentBorder');
        $pString .= \HTML\td($td3, 'attachmentBorder');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * construct table for embargo form items
     *
     * @param string|FALSE $hash
     * @param bool $multiple
     *
     * @return string
     */
    private function embargoForm($hash = FALSE, $multiple = FALSE)
    {
        if (!$this->session->getVar('setup_Superadmin'))
        {
            return '&nbsp;';
        }
        $day = $month = 01;
        $embargo = $year = $dateString = FALSE;
        if ($hash)
        {
            $this->db->formatConditions(['resourceattachmentsHashFilename' => $hash]);
            $this->db->formatConditions(['resourceattachmentsResourceId' => $this->resourceId]);
            $row = $this->db->selectFirstRow('resource_attachments', ['resourceattachmentsEmbargo', 'resourceattachmentsEmbargoUntil']);
            if ($row['resourceattachmentsEmbargo'] == 'Y')
            {
                $embargo = 'CHECKED';
            }
            $hash = '_' . $hash;
            $split = UTF8::mb_explode(' ', $row['resourceattachmentsEmbargoUntil']);
            $date = UTF8::mb_explode('-', $split[0]);
            if ($date[0] != '0000')
            {
                $year = $date[0];
            }
            if ($date[1] != '00')
            {
                $month = $date[1];
            }
            if ($date[2] != '00')
            {
                $day = $date[2];
            }
            $year ? $dateString = $year . '-' . $month . '-' . $day : FALSE;
        }
        $td = \HTML\tableStart('left width95percent');
        $td .= \HTML\trStart();
        $embargoMessage = $multiple ? $this->messages->text("resources", 'attachEmbargoMultiple') :
            $this->messages->text("resources", 'attachEmbargo');
        $td1 = BR . $embargoMessage . '&nbsp;' . \FORM\checkbox(FALSE, 'embargo' . $hash, $embargo);
        $td1 .= '&nbsp;&nbsp;&nbsp;&nbsp;' . \FORM\dateInput(FALSE, 'date' . $hash, $dateString);
        $td .= \HTML\td($td1);
        $td .= \HTML\trEnd();
        $td .= \HTML\tableEnd();

        return $td;
    }
}
