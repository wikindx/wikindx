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
            header("Location: index.php?action=front&error=inputError_missing");
            die;
        }
        $this->resourceId = $this->vars['resourceId'];
        // Warnings about file size are handled BEFORE the script so set a custom error handler here and redirect with header()
        if (isset($_SERVER["CONTENT_LENGTH"]))
        {
            if ($_SERVER["CONTENT_LENGTH"] > \FILE\fileAttachUploadMaxSize())
            {
                $url = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&error=file_uploadSize' . 
                	'&browserTabID=' . $this->browserTabID;
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
            header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&error=inputError_missing&browserTabID=" . $this->browserTabID);
            die;
        }
        $return = \HTML\a(
            $this->icons->getClass("edit"),
            $this->icons->getHTML("return"),
            'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&browserTabID=' . $this->browserTabID
        );
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", $this->messages->text('misc', 'delete') . '&nbsp;&nbsp;' . $return));
        $pString = \FORM\formHeader("attachments_ATTACHMENTS_CORE");
        $pString .= \FORM\hidden('function', 'delete');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        $pString .= \FORM\hidden("browserTabID", $this->browserTabID);
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
        
        $id = $this->vars['id'];
        $hash = $this->vars['filename'];
        $resourceId = $this->vars['resourceId'];
        
        $this->db->formatConditions(['resourceattachmentsId' => $id]);
        $this->db->formatConditions(['resourceattachmentsHashFilename' => $hash]);
        $recordset = $this->db->select(
            'resource_attachments',
            ['resourceattachmentsFileType', 'resourceattachmentsFileSize',
                'resourceattachmentsHashFilename', 'resourceattachmentsFileName', 'resourceattachmentsTimestamp', ]
        );
        
        if ($this->db->numRows($recordset) == 0 || file_exists($dirName . DIRECTORY_SEPARATOR . $hash) === FALSE)
        {
            header('HTTP/1.0 404 Not Found');
            die($this->errors->text("file", "missing"));
        }
        else
        {
            $row = $this->db->fetchRow($recordset);
            $type = $row['resourceattachmentsFileType'];
            $size = $row['resourceattachmentsFileSize'];
            $filename = $row['resourceattachmentsFileName'];
            $lastmodified = date('r', strtotime($row['resourceattachmentsTimestamp']));
            
            FILE\setHeaders($type, $size, $filename, $lastmodified);
            FILE\readfile_chunked($dirName . DIRECTORY_SEPARATOR . $hash);
            $this->attachment->incrementDownloadCounter($id, $resourceId);
            die;
        }
    }
    /**
     * Initial editing/adding form
     */
    private function editInit()
    {
        $return = \HTML\a(
            $this->icons->getClass("edit"),
            $this->icons->getHTML("return"),
            'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&browserTabID=' . $this->browserTabID
        );
        $fields = $this->attachment->listFiles($this->resourceId);
        if (!empty($fields))
        {
            // attachments exist for this resource
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", '(' . $this->messages->text('misc', 'edit') . ')' .
                '&nbsp;&nbsp;' . $return));
            GLOBALS::addTplVar('content', $this->fileAttachEdit($fields));
        }
        else
        {
            // add a new attachment
            GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", '(' . $this->messages->text('misc', 'add') . ')' .
                '&nbsp;&nbsp;' . $return));
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
            header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&error=file_attachmentExists&browserTabID=" . $this->browserTabID);
            die;
        }
        // send back to view this resource with success message
        header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&success=attachAdd&browserTabID=" . $this->browserTabID);
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
            header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&error=file_attachmentExists&browserTabID=" . $this->browserTabID);
            die;
        }
        // send back to view this resource with success message
        $message = rawurlencode($this->success->text("attachAdd"));
        header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&success_attachAdd&browserTabID=" . $this->browserTabID);
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
                header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&error=file_attachmentExists&browserTabID=" 
                	. $this->browserTabID);
                die;
            }
        }
        if (isset($deletes))
        {
            $this->deleteConfirm($deletes);

            return;
        }
        // send back to view this resource with success message (deleteConfirm breaks out before this)
        header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&success_attachEdit&browserTabID=" . $this->browserTabID);
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
            $this->icons->getHTML("return"),
            'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&browserTabID=' . $this->browserTabID
        );
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "attach", $this->messages->text('misc', 'delete') .
            '&nbsp;&nbsp;' . $return));
        $pString = \FORM\formHeader("attachments_ATTACHMENTS_CORE");
        $pString .= \FORM\hidden('function', 'delete');
        $pString .= \FORM\hidden('resourceId', $this->resourceId);
        $pString .= \FORM\hidden("browserTabID", $this->browserTabID);
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
            $this->icons->getHTML("return"),
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
            }
            // remove reference in statistics_attachment_downloads
            $this->db->formatConditions(['statisticsattachmentdownloadsResourceId' => $this->resourceId]);
            $this->db->formatConditions(['statisticsattachmentdownloadsAttachmentId' => array_shift($attachmentIds)]);
            $this->db->delete('statistics_attachment_downloads');
        }
        // send back to view this resource with success message
        $id = $this->resourceId;
        header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&success=attachDelete&browserTabID=" . $this->browserTabID);
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
                header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&error=file_upload&browserTabID=" . $this->browserTabID);
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
                    header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&error=file_upload&browserTabID=" . $this->browserTabID);
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
                header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&error=file_upload&browserTabID=" . $this->browserTabID);
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
            header("Location: index.php?action=resource_RESOURCEVIEW_CORE&id=$id&error=file_upload&browserTabID=" . $this->browserTabID);
            die;
        }
        
        $this->db->formatConditions(["resourceattachmentsHashFilename" => $hash]);
        $this->db->formatConditions(["resourceattachmentsResourceId" => $this->resourceId]);
        $recordSet = $this->db->select('resource_attachments', 'resourceattachmentsId');
        if ($this->db->numRows($recordSet))
        {
            // attachment already part of this resource
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
            
            $this->refreshCache($hash);
        }

        return TRUE;
    }
    
    /**
     * Launch the update of one attachment cache file
     *
     * This function should only be called from an HTTP request that does not expect a return.
     */
    public function curlRefreshCache()
    {
        if (array_key_exists("filename", $this->vars))
        {
            $this->refreshCache($this->vars['filename'], TRUE);
        }
        die();
    }
    
    /**
     * Extract the text of an attachment file in a cache column of the database
     *
     * @param string $filename // Attachment filename
     * @param bool $force
     *
     * @return bool TRUE on success, FALSE otherwise
     */
    public function refreshCache($filename, $force = FALSE)
    {
        $dirData = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS]);
        $pathData = implode(DIRECTORY_SEPARATOR, [$dirData, $filename]);
        
        // Impossible to go further without the original file
        if (!file_exists($pathData))
        {
            $this->db->formatConditions(["resourceattachmentsHashFilename" => $filename]);
            $this->db->update("resource_attachments", ["resourceattachmentsText" => ""]);
            return FALSE;
        }
        
        if (!$force)
        {
            // Check if the text is not yet extracted
            $this->db->formatConditions(["resourceattachmentsText" => 'IS NOT NULL']);
            $this->db->formatConditions(["resourceattachmentsHashFilename" => $filename]);
            $this->db->formatConditions(['resourceattachmentsResourceId' => $this->resourceId]);
            $resultSet = $this->db->select('resource_attachments', 'resourceattachmentsId');
            if ($this->db->numRows($resultSet)) {
                return TRUE;
            }
        }
        
        // Extract the text
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "libs", "FILETOTEXT.php"]));
        $ftt = new FILETOTEXT();
        
        // Save the text
        $this->db->formatConditions(["resourceattachmentsHashFilename" => $filename]);
        $resourceattachmentsFileType = $this->db->selectFirstField('resource_attachments', ['resourceattachmentsFileType']);
        $contentCache = $ftt->convertToText($pathData, $resourceattachmentsFileType);
        
        $this->db->formatConditions(["resourceattachmentsHashFilename" => $filename]);
        $this->db->update("resource_attachments", ["resourceattachmentsText" => $contentCache]);
        return TRUE;
    }

    /**
     * Caches all attachments that are not yet with cUrl
     */
    public function checkCache()
    {
        $db = FACTORY_DB::getInstance();
        $vars = GLOBALS::getVars();
        $session = FACTORY_SESSION::getInstance();
        $curl_version_infos = curl_version();
        $curl_ms_timeout_available = version_compare($curl_version_infos["version"], '7.16.2', '>=');
            
        // Don't launch a cache action when we are executing one 
        $action = $vars['action'] ?? "";
        $method = $vars['method'] ?? "";
        if ($action == "attachments_ATTACHMENTS_CORE" && $method == "curlRefreshCache")
        {
            return;
        }
        
        // 20 * 100 ms or 2 * 1 s implies a penality of 2 seconds
        $db->limit($curl_ms_timeout_available ? 20 : 2, 0);
        $db->formatConditions(["resourceattachmentsText" => 'IS NULL']);
        $resultSet = $db->select('resource_attachments', ['resourceattachmentsHashFilename']);
        while ($row = $db->fetchRow($resultSet))
        {
            $curlTarget = WIKINDX_URL_BASE . '/index.php' .
            '?action=attachments_ATTACHMENTS_CORE' .
            '&method=curlRefreshCache' .
            '&filename=' . urlencode($row['resourceattachmentsHashFilename']);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $curlTarget);
            curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            if ($curl_ms_timeout_available)
            {
                // LkpPo(HACK): https://www.php.net/manual/fr/function.curl-setopt.php#104597
                // For OSes that can't handle a timeout below 1 s, we disable signals to achieve queries in quasi async mode
                curl_setopt($ch, CURLOPT_NOSIGNAL, TRUE);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 100); // 100 ms
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); // 100 ms
            }
            else
            {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); // 1 ms
                curl_setopt($ch, CURLOPT_TIMEOUT, 1); // 1 ms
            }
            curl_exec($ch);
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
        $pString = "";
        
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $tinyEditors[] = 'fileDescription';
        foreach ($fields as $hash => $null)
        {
            $tinyEditors[] = $hash;
        }
        
        if (ini_get("file_uploads"))
        {
            GLOBALS::addTplVar('content', HTML\p($this->messages->text("resources", "fileAttachments")));
            
            // Form elements for adding another attachment
            $pString .= \HTML\tableStart('generalTable left');
            $pString .= \HTML\trStart();
            
            // Quick and dirty multiple upload
            GLOBALS::addTplVar('scripts', '<script src="' . WIKINDX_URL_BASE . '/core/modules/attachments/multipleUpload.js?ver=' .
                WIKINDX_PUBLIC_VERSION . '"></script>');
            GLOBALS::addTplVar('scripts', '<script>var rId = ' . $this->resourceId . '; </script>');
            $closeUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&error=file_upload';
            GLOBALS::addTplVar('scripts', '<script>var errorUrl = "' . $closeUrl . '"; </script>');
            $sizeErrorUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&error=file_uploadSize';
            GLOBALS::addTplVar('scripts', '<script>var sizeErrorUrl = "' . $sizeErrorUrl . '"; </script>');
            $closeUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&success=attachAdd';
            GLOBALS::addTplVar('scripts', '<script>var successUrl = "' . $closeUrl . '"; </script>');
            GLOBALS::addTplVar('scripts', '<script>var max_file_size = "' . \FILE\fileAttachUploadMaxSize() . '"; </script>');
            GLOBALS::addTplVar('scripts', '<script>var browserTabID = "' . $this->browserTabID . '"; </script>');
            $td = '<div id="uploader">' . $this->messages->text("resources", "fileAttachDragAndDrop")
            . " (max.&nbsp;" . \FILE\formatSize(\FILE\fileAttachUploadMaxSize()) . " and " . ini_get("max_file_uploads") . "&nbsp;files) " . '</div>';
            GLOBALS::addTplVar('scripts', '<script>var fallback = "' .
                $this->messages->text("resources", "fileAttachFallback") . '"; </script>');
            $td .= '<div id="fallback"></div>';
            $pString .= \HTML\td($td, 'attachmentBorder');
            
            // Single upload
            $td = "";
            $td .= \FORM\formMultiHeader("attachments_ATTACHMENTS_CORE");
            $td .= \FORM\hidden('function', 'add');
            $td .= \FORM\hidden('resourceId', $this->resourceId);
            $td .= \FORM\hidden("MAX_FILE_SIZE", \FILE\fileAttachUploadMaxSize());
            $td .= \FORM\hidden("browserTabID", $this->browserTabID);
            $td .= \FORM\fileUpload(
                $this->messages->text("resources", "fileAttach"),
                "file",
                50
            );
            $td .= " (max.&nbsp;" . \FILE\formatSize(\FILE\fileAttachUploadMaxSize()) . ") ";
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
            $td .= \FORM\hidden("MAX_FILE_SIZE", \FILE\fileAttachUploadMaxSize());
            $td .= \FORM\hidden("browserTabID", $this->browserTabID);
            $td .= \FORM\fileUploadMultiple($this->messages->text("resources", "fileAttachMultiple"), "file[]", 50);
            $td .= " (max.&nbsp;" . \FILE\formatSize(\FILE\fileAttachUploadMaxSize()) . " and " . ini_get("max_file_uploads") . "&nbsp;files) ";
            $td .= $this->embargoForm(FALSE, TRUE);
            $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td, 'attachmentBorder');
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tableEnd();
        }
        else
        {
            $pString .= $this->messages->text("misc", "uploadDisabled");
        }
        
        // Edit currents attachments
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
        	$td .= \FORM\hidden("browserTabID", $this->browserTabID);
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
        $pString .= \FORM\hidden("MAX_FILE_SIZE", \FILE\fileAttachUploadMaxSize());
        $pString .= \FORM\hidden("browserTabID", $this->browserTabID);
        
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
        if (ini_get("file_uploads"))
        {
            // Three ways to do this:
            GLOBALS::addTplVar('content', HTML\p($this->messages->text("resources", "fileAttachments")));
            
            // Quick and dirty multiple upload
            GLOBALS::addTplVar('scripts', '<script src="' . WIKINDX_URL_BASE . '/core/modules/attachments/multipleUpload.js?ver=' .
                WIKINDX_PUBLIC_VERSION . '"></script>');
            GLOBALS::addTplVar('scripts', '<script>var rId = ' . $this->resourceId . '; </script>');
            $closeUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&error=file_upload';
            GLOBALS::addTplVar('scripts', '<script>var errorUrl = "' . $closeUrl . '"; </script>');
            $sizeErrorUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&error=file_uploadSize';
            GLOBALS::addTplVar('scripts', '<script>var sizeErrorUrl = "' . $sizeErrorUrl . '"; </script>');
            $closeUrl = 'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->resourceId . '&success=attachAdd';
            GLOBALS::addTplVar('scripts', '<script>var successUrl = "' . $closeUrl . '"; </script>');
            GLOBALS::addTplVar('scripts', '<script>var max_file_size = "' . \FILE\fileAttachUploadMaxSize() . '"; </script>');
            GLOBALS::addTplVar('scripts', '<script>var browserTabID = "' . $this->browserTabID . '"; </script>');
            $td1 = '<div id="uploader">' . $this->messages->text("resources", "fileAttachDragAndDrop")
                . " (max.&nbsp;" . \FILE\formatSize(\FILE\fileAttachUploadMaxSize()) . " and " . ini_get("max_file_uploads") . "&nbsp;files) " . '</div>';
            GLOBALS::addTplVar('scripts', '<script>var fallback = "' .
                $this->messages->text("resources", "fileAttachFallback") . '"; </script>');
            $td1 .= '<div id="fallback"></div>';
            
            // Single file upload with filename, description and embargo
            $td2 = \FORM\formMultiHeader("attachments_ATTACHMENTS_CORE");
            $td2 .= \FORM\hidden('function', 'add');
            $td2 .= \FORM\hidden('resourceId', $this->resourceId);
            $td2 .= \FORM\hidden("MAX_FILE_SIZE", \FILE\fileAttachUploadMaxSize());
            $td2 .= \FORM\hidden("browserTabID", $this->browserTabID);
            $td2 .= \FORM\fileUpload($this->messages->text("resources", "fileAttach"), "file", 50);
            $td2 .= " (max.&nbsp;" . \FILE\formatSize(\FILE\fileAttachUploadMaxSize()) . ") ";
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
            $td3 .= \FORM\hidden("MAX_FILE_SIZE", \FILE\fileAttachUploadMaxSize());
            $td3 .= \FORM\hidden("browserTabID", $this->browserTabID);
            $td3 .= \FORM\fileUploadMultiple($this->messages->text("resources", "fileAttachMultiple"), "file[]", 50);
            $td3 .= " (max.&nbsp;" . \FILE\formatSize(\FILE\fileAttachUploadMaxSize()) . " and " . ini_get("max_file_uploads") . "&nbsp;files)";
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
        else
        {
            return $pString .= $this->messages->text("misc", "uploadDisabled");
        }
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
