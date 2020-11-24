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
    private $icons;
    private $gatekeep;
    private $attachment;
    private $resourceId;
    private $embargoArray = [];
    private $embargoNew;
    private $browserTabID = FALSE;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->attachment = FACTORY_ATTACHMENT::getInstance();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
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
            $message = rawurlencode($this->errors->text("inputError", "missing"));
            header("Location: index.php?action=front&message=$message");
            die;
        }
        $this->resourceId = $this->vars['resourceId'];
        // Warnings about file size are handled BEFORE the script so set a custom error handler here and redirect with header()
        if (isset($_SERVER["CONTENT_LENGTH"]))
        {
            if ($_SERVER["CONTENT_LENGTH"] > \FILE\fileMaxSize())
            {
                $error = rawurlencode($this->errors->text('file', 'uploadSize'));
                $url = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&message=' . $error;
                header("Location: $url");
                die;
            }
        }
        $function = $this->vars['function'];
        $this->{$function}();
    }
    /**
     * delete all attachments
     */
    public function deleteConfirmAll()
    {
        if (!array_key_exists('deleteAll', $this->vars))
        {
            $id = $this->resourceId;
            $message = rawurlencode($this->errors->text("inputError", "missing"));
            header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
            die;
        }
        $return = \HTML\a(
            $this->icons->getClass("edit"),
            $this->icons->getHTML("Return"),
            'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&browserTabID=' . $this->browserTabID
        );
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", $this->messages->text('misc', 'delete') . '&nbsp;&nbsp;' . $return));
        $pString = \FORM\formHeader("attachments_ATTACHMENTS_CORE");
        $pString .= \FORM\hidden('function', 'delete');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        $pString .= $this->messages->text('resources', 'deleteConfirmAttach') . ':' . BR;
        $this->db->formatConditions(["resourceattachmentsResourceId" => $this->resourceId]);
        $recordSet = $this->db->select(
            ["resource_attachments"],
            ['resourceattachmentsId', 'resourceattachmentsHashFilename', 'resourceattachmentsFileName']
        );
        while ($row = $this->db->fetchRow($recordSet))
        {
            $pString .= \FORM\checkBox(
                FALSE,
                'attachmentDelete_' . $row['resourceattachmentsId'] . '_' . $row['resourceattachmentsHashFilename'],
                TRUE
            ) . '&nbsp;' . $row['resourceattachmentsFileName'] . BR;
        }
        $pString .= \HTML\p('&nbsp;' . BR . \FORM\formSubmit($this->messages->text("submit", "Confirm")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * download an attachment to a user
     */
    public function downloadAttachment()
    {
        $dirName = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS]);
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
        
        if (file_exists($dirName . DIRECTORY_SEPARATOR . $hash) === FALSE)
        {
            $id = $this->vars['resourceId'];
            $message = rawurlencode($this->errors->text("file", "missing"));
            header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
            die;
        }
        
        $this->refreshCache($hash);
        
        FILE\setHeaders($type, $size, $filename, $lastmodified);
        FILE\readfile_chunked($dirName . DIRECTORY_SEPARATOR . $hash);
        $this->attachment->incrementDownloadCounter($this->vars['id'], $this->vars['resourceId']);
        die;
    }
    /**
     * Initial editing/adding form
     */
    private function editInit()
    {
        $return = \HTML\a(
            $this->icons->getClass("edit"),
            $this->icons->getHTML("Return"),
            'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&browserTabID=' . $this->browserTabID
        );
        $fields = $this->attachment->listFiles($this->resourceId);
        if (!empty($fields))
        { // attachments exist for this resource
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", '(' . $this->messages->text('misc', 'edit') . ')' .
                '&nbsp;&nbsp;' . $return));
            GLOBALS::addTplVar('content', $this->fileAttachEdit($fields));
        }
        else
        { // add a new attachment
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", '(' . $this->messages->text('misc', 'add') . ')' .
                '&nbsp;&nbsp;' . $return));
            GLOBALS::addTplVar('content', HTML\p($this->messages->text("resources", "fileAttachments")));
            GLOBALS::addTplVar('content', $this->fileAttachAdd());
        }
    }
    /**
     * add an attachment
     */
    private function add()
    {
        $id = $this->resourceId;
        $this->getEmbargo();
        if (!$this->storeFile())
        { // FALSE if attachment already exists
            $message = rawurlencode($this->errors->text("file", "attachmentExists"));
            header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
            die;
        }
        // send back to view this resource with success message
        $message = rawurlencode($this->success->text("attachAdd"));
        header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
        die;
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
        $this->getEmbargo();
        $id = $this->resourceId;
        if (!$this->storeFile(TRUE))
        { // FALSE if attachment already exists
            $message = rawurlencode($this->errors->text("file", "attachmentExists"));
            header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
            die;
        }
        // send back to view this resource with success message
        $message = rawurlencode($this->success->text("attachAdd"));
        header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
        die;
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
            $split = \UTF8\mb_explode('_', $key);
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
        // Edit files
        if (isset($edits))
        {
            foreach ($edits as $hash => $filename)
            {
                if (!trim($filename))
                { // Must have a name . . .
                    $this->db->formatConditions(["resourceattachmentsResourceId" => $this->resourceId]);
                    $this->db->formatConditions(["resourceattachmentsHashFilename" => $hash]);
                    $filename = $this->db->selectFirstField('resource_attachments', 'resourceattachmentsFileName');
                }
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
        }
        // set primary attachment
        $this->setPrimaryAttachment($primary);
        $id = $this->resourceId;
        // Store any new file
        if (array_key_exists('file', $_FILES) && $_FILES['file']['tmp_name'])
        {
            if (!$this->storeFile())
            { // FALSE if attachment already exists
                $message = rawurlencode($this->errors->text("file", "attachmentExists"));
                header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
                die;
            }
        }
        if (isset($deletes))
        {
            $this->deleteConfirm($deletes);

            return;
        }
        // send back to view this resource with success message (deleteConfirm breaks out before this)
        $message = rawurlencode($this->success->text("attachEdit"));
        header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
        die;
    }
    /**
     * Grab and sort embargo date
     *
     * @param mixed $hash
     */
    private function getEmbargo($hash = FALSE)
    {
        if ($hash)
        {
            $arrayIndex = $hash;
            $hash = "_$hash";
            $date = 'date' . $hash;
        }
        else
        {
            $date = 'date';
        }
        // date comes in as 'yyyy-mm-dd' (but displayed on web form as 'dd / mm / yyyy').
        // all three fields must have a valid value else $this->vars["date"] is FALSE
        if (array_key_exists($date, $this->vars) && $this->vars[$date])
        {
            list($year, $month, $day) = \UTILS\splitDate($this->vars[$date]);
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
     *
     * @param mixed $primary
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
     *
     * @param mixed $deletes
     */
    private function deleteConfirm($deletes)
    {
        $return = \HTML\a(
            $this->icons->getClass("edit"),
            $this->icons->getHTML("Return"),
            'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&browserTabID=' . $this->browserTabID
        );
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", $this->messages->text('misc', 'delete') .
            '&nbsp;&nbsp;' . $return));
        $pString = \FORM\formHeader("attachments_ATTACHMENTS_CORE");
        $pString .= \FORM\hidden('function', 'delete');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        $pString .= $this->messages->text('resources', 'deleteConfirmAttach') . ':' . BR;
        $this->db->formatConditions(["resourceattachmentsResourceId" => $this->resourceId]);
        $recordSet = $this->db->select(
            ["resource_attachments"],
            ['resourceattachmentsId', 'resourceattachmentsHashFilename', 'resourceattachmentsFileName']
        );
        while ($row = $this->db->fetchRow($recordSet))
        {
            if (array_key_exists($row['resourceattachmentsHashFilename'], $deletes))
            {
                $pString .= \FORM\checkBox(
                    FALSE,
                    'attachmentDelete_' . $row['resourceattachmentsId'] . '_' . $row['resourceattachmentsHashFilename'],
                    TRUE
                ) . '&nbsp;' . $row['resourceattachmentsFileName'] . BR;
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
        $return = \HTML\a(
            $this->icons->getClass("edit"),
            $this->icons->getHTML("Return"),
            'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&browserTabID=' . $this->browserTabID
        );
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", $this->messages->text('misc', 'delete') .
            '&nbsp;&nbsp;' . $return));
        foreach ($this->vars as $key => $var)
        {
            $split = \UTF8\mb_explode('_', $key);
            if ($split[0] == 'attachmentDelete')
            {
                $deletes[] = $split[2];
                $attachmentIds[] = $split[1];
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
                @unlink(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS, $hash]));
                @unlink(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_ATTACHMENTS, $hash]));
            }
            // remove reference in statistics_attachment_downloads
            $this->db->formatConditions(['statisticsattachmentdownloadsResourceId' => $this->resourceId]);
            $this->db->formatConditions(['statisticsattachmentdownloadsAttachmentId' => array_shift($attachmentIds)]);
            $this->db->delete('statistics_attachment_downloads');
        }
        // send back to view this resource with success message
        $id = $this->resourceId;
        $message = rawurlencode($this->success->text("attachDelete"));
        header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
        die;
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
        $id = $this->resourceId;
        if ($multiple)
        {
            $filesArray = FILE\fileUpload($varFileName, $multiple);
            if (empty($filesArray))
            {
                $message = rawurlencode($this->errors->text("file", "upload"));
                header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
                die;
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
                    $message = rawurlencode($this->errors->text("file", "upload"));
                    header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
                    die;
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
                $message = rawurlencode($this->errors->text("file", "upload"));
                header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
                die;
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
        if (!FILE\fileStore(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS]), $hash, $index))
        {
            $id = $this->resourceId;
            $message = rawurlencode($this->errors->text("file", "upload"));
            header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&message=$message");
            die;
        }
        
        $this->refreshCache($hash);
        
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
            if (array_key_exists('embargo', $this->vars) && $this->embargoNew)
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
     * Write or update the cache file of an attachment file
     *
     * @param string $filename // Attachment filename
     * @param bool $force
     *
     * @return bool TRUE on success, FALSE otherwise
     */
    public function refreshCache($filename, $force = FALSE)
    {
        $dirData = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS]);
        $dirCache = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_ATTACHMENTS]);
        
        $pathData = implode(DIRECTORY_SEPARATOR, [$dirData, $filename]);
        $pathCache = implode(DIRECTORY_SEPARATOR, [$dirCache, $filename]);
        
        // Impossible to go further without the original file
        if (!file_exists($pathData))
        {
            return FALSE;
        }
        
        // When the cache file exists and is newer than (or equal) the original file there is nothing to do
        if (!$force && file_exists($pathCache) && filemtime($pathCache) >= filemtime($pathData))
        {
            return TRUE;
        }
        
        // Make the cachd file
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "list", "FILETOTEXT.php"]));
        $ftt = new FILETOTEXT();
        $contentCache = $ftt->convertToText($pathData);
        if (file_put_contents($pathCache, $contentCache) === FALSE)
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
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
        // Form elements for adding another attachment
        $pString = \HTML\tableStart('generalTable left');
        $pString .= \HTML\trStart();
        // Quick and dirty multiple upload
        GLOBALS::addTplVar('scripts', '<script src="' . WIKINDX_URL_BASE . '/core/modules/attachments/multipleUpload.js?ver=' .
            WIKINDX_PUBLIC_VERSION . '"></script>');
        GLOBALS::addTplVar('scripts', '<script>var rId = ' . $this->resourceId . '; </script>');
        $error = rawurlencode($this->errors->text("file", "upload"));
        $closeUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&message=' . $error;
        GLOBALS::addTplVar('scripts', '<script>var errorUrl = "' . $closeUrl . '"; </script>');
        $error = rawurlencode($this->errors->text("file", "uploadSize", $maxSize));
        $sizeErrorUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&message=' . $error;
        GLOBALS::addTplVar('scripts', '<script>var sizeErrorUrl = "' . $sizeErrorUrl . '"; </script>');
        $success = rawurlencode($this->success->text("attachAdd"));
        $closeUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&message=' . $success;
        GLOBALS::addTplVar('scripts', '<script>var successUrl = "' . $closeUrl . '"; </script>');
        GLOBALS::addTplVar('scripts', '<script>var max_file_size = "' . \FILE\fileMaxSize() . '"; </script>');
        $td = '<div id="uploader">' . $this->messages->text("resources", "fileAttachDragAndDrop") . '</div>';
        GLOBALS::addTplVar('scripts', '<script>var fallback = "' .
            $this->messages->text("resources", "fileAttachFallback") . '"; </script>');
        $td .= '<div id="fallback"></div>';
        $pString .= \HTML\td($td, 'attachmentBorder');
        // Single upload
        $td = "";
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
        $maxSize = FILE\fileMaxSize();
        // Three ways to do this:
        // Quick and dirty multiple upload
        GLOBALS::addTplVar('scripts', '<script src="' . WIKINDX_URL_BASE . '/core/modules/attachments/multipleUpload.js?ver=' .
            WIKINDX_PUBLIC_VERSION . '"></script>');
        GLOBALS::addTplVar('scripts', '<script>var rId = ' . $this->resourceId . '; </script>');
        $error = rawurlencode($this->errors->text("file", "upload"));
        $closeUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&message=' . $error;
        GLOBALS::addTplVar('scripts', '<script>var errorUrl = "' . $closeUrl . '"; </script>');
        $error = rawurlencode($this->errors->text("file", "uploadSize", $maxSize));
        $sizeErrorUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&message=' . $error;
        GLOBALS::addTplVar('scripts', '<script>var sizeErrorUrl = "' . $sizeErrorUrl . '"; </script>');
        $success = rawurlencode($this->success->text("attachAdd"));
        $closeUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&message=' . $success;
        GLOBALS::addTplVar('scripts', '<script>var successUrl = "' . $closeUrl . '"; </script>');
        GLOBALS::addTplVar('scripts', '<script>var max_file_size = "' . \FILE\fileMaxSize() . '"; </script>');
        $td1 = '<div id="uploader">' . $this->messages->text("resources", "fileAttachDragAndDrop") . '</div>';
        GLOBALS::addTplVar('scripts', '<script>var fallback = "' .
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
     * @param false|string $hash
     * @param bool $multiple
     *
     * @return string
     */
    private function embargoForm($hash = FALSE, $multiple = FALSE)
    {
        if (!$this->session->getVar("setup_Superadmin"))
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
            $split = \UTF8\mb_explode(' ', $row['resourceattachmentsEmbargoUntil']);
            $date = \UTF8\mb_explode('-', $split[0]);
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
