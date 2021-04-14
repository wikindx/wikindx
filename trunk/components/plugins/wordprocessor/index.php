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
 * wordprocessor class.
 *
 * Logged in users can use a WYSIWYG word processor for the writing of articles.
 */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));


class wordprocessor_MODULE
{
    public $authorize;
    public $menus;
    private $db;
    private $vars;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $session;
    private $badInput;
    private $config;
    private $papersDir = WIKINDX_DIR_BASE . DIRECTORY_SEPARATOR . WIKINDX_DIR_DATA_PLUGINS . DIRECTORY_SEPARATOR . "wordprocessor";

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->pluginmessages = new PLUGINMESSAGES('wordprocessor', 'wordprocessorMessages');
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $this->config = new wordprocessor_CONFIG();
        $this->authorize = $this->config->authorize;
        if ($menuInit)
        { // portion of constructor used for menu initialisation
            $this->makeMenu($this->config->menus);

            return; // Need do nothing more as this is simply menu initialisation.
        }
        $this->session = FACTORY_SESSION::getInstance();
        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        $this->db = FACTORY_DB::getInstance();
        $this->checkTables();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();


        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->checkTable();
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('wordprocessor'));
    }
    
    /**
     * checkTables
     */
    private function checkTables()
    {
        $version = \UPDATE\getPluginInternalVersion($this->db, mb_strtolower(basename(__DIR__)));
        
        if ($version == 0)
        {
            // NB: Windows MySQL lowercases any table name
            // To be sure, it is necessary to lowercase all table elements
            $tables = $this->db->listTables(FALSE);
            foreach ($tables as $k => $v)
            {
                $tables[$k] = mb_strtolower($v);
            }
            
            if (array_search('plugin_wordprocessor', $tables) === FALSE)
            {
                $this->db->queryNoError("
                    CREATE TABLE `" . WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor` (
                        `pluginwordprocessorId` int(11) NOT NULL AUTO_INCREMENT,
                        `pluginwordprocessorHashFilename` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                        `pluginwordprocessorFilename` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                        `pluginwordprocessorUserId` int(11) NOT NULL,
                        `pluginwordprocessorTimestamp` datetime NOT NULL,
                        PRIMARY KEY (`pluginwordprocessorId`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
    			");
            }
            
            \UPDATE\setPluginInternalVersion($this->db, mb_strtolower(basename(__DIR__)), 1);
        }
    }
    
    /**
     * Open a new WP paper
     */
    public function newInit()
    {
        GLOBALS::setTplVar('heading', '');
        FACTORY_LOADTINYMCE::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "WPLOADTINYMCE.php"]));
        $tinyMce = new WPLOADTINYMCE();
        $pString = $this->heading();
        $message = "<span class='error'>" . $this->pluginmessages->text("notSavedStatus") . "</span>";
        $pString .= "<script>var paperStatusMessage=\"$message\";</script>\n";
        // Complete form not required -- e.g. <form> ... </form> elements not needed here
        $pString .= $tinyMce->loadWPTextarea();
        $pString .= FORM\textareaInput(FALSE, "paperText", FALSE);
        // reset session variables
        $this->session->clearArray('wp');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Select a WP paper to open or append
     *
     * @param false|string $message
     * @param bool $append
     */
    public function openInit($message = FALSE, $append = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingOpen'));
        // all messages coming in are error messages
        if ($message)
        {
            $message = "<span class='error'>$message</span>";
        }
        $pString = $message ? $message : '';
        list($papers, $papersTimestamp) = $this->grabPapers();
        if ($append)
        {
            $title = base64_decode($this->session->getVar("wp_Title"));
            if (($key = array_search($title, $papers)) !== FALSE)
            {
                unset($papers[$key]);
            }
        }
        if (!empty($papers))
        {
            foreach ($papers as $id => $title)
            {
                $entries[$id] = $title . " (" . $papersTimestamp[$id] . ")";
            }
            if ($append)
            {
                $pString .= FORM\formHeader("wordprocessor_loadAppend");
                $pString .= FORM\selectFBoxValue(FALSE, "id", $entries, 20, 80) .
                    BR . FORM\formSubmit($this->pluginmessages->text("loadAppendPaper"));
            }
            else
            {
                $pString .= FORM\formHeader("wordprocessor_open");
                $pString .= FORM\selectFBoxValue(FALSE, "id", $entries, 20, 80) .
                    BR . FORM\formSubmit($this->coremessages->text("submit", "Select"));
            }
            $pString .= FORM\formEnd();
        }
        else
        {
            $pString = HTML\p($this->pluginmessages->text("noPapers"));
        }
        if ($append)
        {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "libs", "CLOSE.php"]));
            $this->template->setVar('body', $pString);
            new CLOSEPOPUP($this->template->process());
        }
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Open an existing WP paper
     *
     * @param false|string $message
     * @param false|string $text
     * @param false|string $title
     */
    public function open($message = FALSE, $text = FALSE, $title = FALSE)
    {
        FACTORY_LOADTINYMCE::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "WPLOADTINYMCE.php"]));
        $tinyMce = new WPLOADTINYMCE();
        GLOBALS::setTplVar('heading', '');
        if (!$text)
        { // i.e. not importing but opening a file for example
            $fields = ["pluginwordprocessorId", "pluginwordprocessorHashFilename", "pluginwordprocessorFilename",
                "pluginwordprocessorTimestamp", ];
            $this->db->formatConditions(['pluginwordprocessorId' => $this->vars['id']]);
            $row = $this->db->selectFirstRow("plugin_wordprocessor", $fields);
            if (!($text = @file_get_contents($this->papersDir . DIRECTORY_SEPARATOR . $row['pluginwordprocessorHashFilename'])))
            {
                $this->badInput->close($this->errors->text("file", "read"), $this, "openInit");
            }
            $title = $row["pluginwordprocessorFilename"];
            // Already set when importing so only needed when opening
            $this->session->setVar("wp_Title", base64_encode($title));
            $this->session->setVar("wp_HashFilename", $row['pluginwordprocessorHashFilename']);
            $this->session->setVar("wp_Id", $row['pluginwordprocessorId']);
        }
        $text = stripslashes($text);
        $pString = $this->heading($title, $message);
        $message = "<span class='error'>" . $this->pluginmessages->text("notSavedStatus") . "</span>\n";
        $pString .= "<script>var paperStatusMessage=\"$message\";</script>\n";
        $pString .= $tinyMce->loadWPTextarea();
        // Complete form not required -- e.g. <form> ... </form> elements not needed here
        $pString .= '<textarea class="formElements" name="paperText" id="paperText">' . $text . '</textarea>';
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Import a WP paper from an external location
     *
     * @param false|string $message
     */
    public function importInit($message = FALSE)
    {
        // all messages coming in are error messages
        if ($message)
        {
            $message = "<span class='error'>$message</span>";
        }
        $pString = $message ? $message : '';
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingImport'));
        $pString .= HTML\p($this->pluginmessages->text("import2"));
        $pString .= FORM\formMultiHeader("wordprocessor_import");
        if (ini_get("file_uploads"))
        {
            $pString .= HTML\p(
                FORM\fileUpload($this->pluginmessages->text("import"), "import_file", 30)
                . " (max.&nbsp;" . \FILE\formatSize(\FILE\fileUploadMaxSize()) . ")"
            );
            $pString .= HTML\p(FORM\textInput($this->pluginmessages->text("import3"), "import_fileName", FALSE, 30));
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Proceed")));
        }
        else
        {
            $pString .= \HTML\p($this->coremessages->text("misc", "uploadDisabled"));
        }
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Import paper
     */
    public function import()
    {
        if (!isset($_FILES['import_file']))
        {
            $this->badInput->close($this->errors->text("file", "upload"), $this, 'importInit');
        }
        $userId = $this->session->getVar("setup_UserId");
        // Check for file input
        if (!($text = @file_get_contents($_FILES['import_file']['tmp_name'])))
        {
            $this->badInput->close($this->errors->text("file", "read"), $this, "importInit");
        }
        $text = $text;
        if (trim($this->vars['import_fileName']))
        {
            $fileName = trim($this->vars['import_fileName']);
        }
        else
        {
            $fileName = $_FILES['import_file']['name'];
        }
        // Remove any extension
        $split = \UTF8\mb_explode(".", $fileName);
        if (count($split) > 1)
        {
            array_pop($split);
            $fileName = implode(".", $split);
        }
        $hashFileName = sha1($userId . $fileName . $text);
        $fullFileName = $this->papersDir . DIRECTORY_SEPARATOR . $hashFileName;
        // If file exists, we don't import
        if (file_exists($fullFileName))
        {
            $this->badInput->close($this->pluginmessages->text("paperExists"), $this, 'importInit');
        }
        // Else, write file and database entry
        if (!move_uploaded_file($_FILES['import_file']['tmp_name'], $fullFileName))
        {
            $this->badInput->close($this->errors->text("file", "upload"), $this, "importInit");
        }
        // If filename exists in the database, don't write a new row.  This is probably an import from wikindx v3.x
        $this->db->formatConditions(['pluginwordprocessorHashFilename' => $fileName]);
        $pluginwordprocessorId = $this->db->select('plugin_wordprocessor', 'pluginwordprocessorId');
        if ($pluginwordprocessorId)
        {
            $databaseId = $pluginwordprocessorId;
        }
        else
        {
            $fields[] = 'pluginwordprocessorHashFilename';
            $values[] = $hashFileName;
            $fields[] = 'pluginwordprocessorUserId';
            $values[] = $userId;
            $fields[] = 'pluginwordprocessorFilename';
            $values[] = $fileName;
            $fields[] = 'pluginwordprocessorTimestamp';
            $values[] = $this->db->formatTimestamp();
            $this->db->insert('plugin_wordprocessor', $fields, $values);
            $databaseId = $this->db->lastAutoId();
        }
        $this->session->setVar("wp_Title", base64_encode($fileName));
        $this->session->setVar("wp_HashFilename", $hashFileName);
        $this->session->setVar("wp_Id", $databaseId);
        $message = "<span class='success'>" . $this->pluginmessages->text("importPaper") . "</span>";
        $this->open($message, $text, $fileName);
    }
    /**
     * Delete a WP paper
     *
     * @param false|string $message
     */
    public function deleteInit($message = FALSE)
    {
        // all messages coming in are error messages
        if ($message)
        {
            $message = "<span class='error'>$message</span>";
        }
        $pString = $message ? $message : '';
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingDelete'));
        list($papers, $papersTimestamp) = $this->grabPapers();
        if (!empty($papers))
        {
            foreach ($papers as $id => $title)
            {
                $entries[$id] = $title . " (" . $papersTimestamp[$id] . ")";
            }
            $pString .= FORM\formHeader("wordprocessor_deleteConfirm");
            $pString .= FORM\selectFBoxValueMultiple(FALSE, "id", $entries, 20, 80) .
                BR . $this->coremessages->text("hint", "multiples") . BR .
                BR . FORM\formSubmit($this->coremessages->text("submit", "Confirm"));
            $pString .= FORM\formEnd();
        }
        else
        {
            $pString = HTML\p($this->pluginmessages->text("noPapers"));
        }
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Ask for confirmation of delete papers
     */
    public function deleteConfirm()
    {
        if (!array_key_exists('id', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'deleteInit');
        }
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingDelete'));
        $this->session->delVar("wp_LockDelete");
        $userId = $this->session->getVar("setup_UserId");
        $pString = FORM\formHeader("wordprocessor_delete");
        foreach ($this->vars['id'] as $id)
        {
            $conditions[] = $id;
            $pString .= FORM\hidden("paperDelete_" . $id, $id);
        }
        $this->db->formatConditionsOneField($conditions, 'pluginwordprocessorId');
        $this->db->formatConditions(['pluginwordprocessorUserId' => $userId]);
        // We do a further check here that these files really do belong to the user.
        $recordSet = $this->db->select("plugin_wordprocessor", "pluginwordprocessorFilename");
        while ($row = $this->db->fetchRow($recordSet))
        {
            $papers[] = \HTML\nlToHtml($row['pluginwordprocessorFilename']);
        }
        $paper = implode(", ", $papers);
        $pString .= HTML\p($this->pluginmessages->text("deleteConfirm") . $paper);
        $pString .= BR . FORM\formSubmit($this->coremessages->text("submit", "Confirm"));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete papers(s)
     */
    public function delete()
    {
        if ($this->session->getVar("wp_LockDelete"))
        {
            $this->badInput->close($this->pluginmessages->text('deletedPaper'), $this, 'deleteInit');
        }
        $userId = $this->session->getVar("setup_UserId");
        $ids = [];
        foreach ($this->vars as $key => $value)
        {
            if (!$value)
            {
                continue;
            }
            if (!preg_match("/paperDelete_/u", $key))
            {
                continue;
            }
            $ids[] = $value;
        }
        if (empty($ids))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'deleteInit');
        }
        foreach ($ids as $id)
        {
            $this->db->formatConditions(['pluginwordprocessorUserId' => $userId]);
            $this->db->formatConditions(['pluginwordprocessorId' => $id]);
            $pluginwordprocessorHashFilename = $this->db->selectFirstField("plugin_wordprocessor", 'pluginwordprocessorHashFilename');
            @unlink($this->papersDir . DIRECTORY_SEPARATOR . $pluginwordprocessorHashFilename);
            $this->db->formatConditions(['pluginwordprocessorUserId' => $userId]);
            $this->db->formatConditions(['pluginwordprocessorId' => $id]);
            $this->db->delete("plugin_wordprocessor");
        }
        $pString = "<span class='success'>" . $this->pluginmessages->text("deletePaper") . "</span>";
        $this->session->setVar("wp_LockDelete", TRUE);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * List existing WP papers
     *
     * @param false|string $message
     */
    public function listPapers($message = FALSE)
    {
        // all messages coming in are error messages
        if ($message)
        {
            $message = "<span class='error'>$message</span>";
        }
        $pString = $message ? $message : '';
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('headingListPapers'));
        list($papers, $papersTimestamp) = $this->grabPapers(TRUE);
        if (!empty($papers))
        {
            foreach ($papers as $id => $paperArray)
            {
                $hash = $paperArray[0];
                $title = $paperArray[1];
                $timestamp = $papersTimestamp[$id];
                $fileLinks[] = $timestamp . ': ' . HTML\a(
                    "link",
                    $title,
                    "index.php?action=wordprocessor_wpFileDownload" . htmlentities("&hash=$hash&filename=$title"),
                    "_new"
                );
            }
            $pString .= HTML\p($this->pluginmessages->text("backup"));
            $pString .= implode(BR . LF, $fileLinks);
        }
        else
        {
            $pString .= HTML\p($this->pluginmessages->text("noPapers"));
        }
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Download a file providing the real filename instead of the hash filename
     */
    public function wpFileDownload()
    {
        if (!array_key_exists('hash', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'listPapers');
        }
        $fileName = $this->vars['filename'];
        ob_start();
        header("Content-Disposition: attachment; filename=\"$fileName\"; charset=UTF-8");
        header("Expires:  0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");
        readfile($this->papersDir . "/" . $this->vars['hash']);
        ob_end_flush();
        die;
    }
    /**
     * For file exports saved outside web server directory (i.e. not in wikindx4/files/) as set in config.php
     */
    public function wpExportDownload()
    {
        if (!array_key_exists('hash', $this->vars))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "WPCOMMON.php"]));
            $common = new WPCOMMON();
            $common->failure($this->errors->text("inputError", "missing"));
        }
        $dirFilesName = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]);
        $extension = $this->vars['extension'];
        $fileName = $this->vars['filename'];
        if ($this->vars['extension'] == 'rtf')
        {
            $type = WIKINDX_MIMETYPE_RTF;
            $charset = '';
        }
        else
        {
            $type = WIKINDX_MIMETYPE_TXT;
            $charset = WIKINDX_CHARSET;
        }
        ob_start();
        header("Content-type: $type;" . ($charset != '' ? "charset=$charset" : ''));
        header("Content-Disposition: inline; filename=\"$fileName\";");
        header("Expires:  0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");
        readfile($dirFilesName . DIRECTORY_SEPARATOR . $this->vars['hash'] . "." . $extension);
        ob_end_flush();
        die;
    }
    /**
     * Make the menus
     *
     * @param false|string $menuArray
     */
    private function makeMenu($menuArray)
    {
        $this->menus = [
            $menuArray[0] => ['wppluginSub' => [
                $this->pluginmessages->text('wpSub') => FALSE,
                $this->pluginmessages->text('wpNew') => "newInit",
                $this->pluginmessages->text('wpOpen') => "openInit",
                $this->pluginmessages->text('wpImport') => "importInit",
                $this->pluginmessages->text('wpDelete') => "deleteInit",
                $this->pluginmessages->text('wpList') => "listPapers",
            ],
            ],
        ];
    }
    /**
     * Check we have the plugin_wordprocessor table, if not, create it
     */
    private function checkTable()
    {
        // NB: Windows MySQL lowercases any table name
        // To be sure, it is necessary to lowercase all table elements
        $tables = $this->db->listTables(FALSE);

        foreach ($tables as $k => $v)
        {
            $tables[$k] = mb_strtolower($v);
        }

        if (array_search('plugin_wordprocessor', $tables) === FALSE)
        {
            $this->db->queryNoError("
                CREATE TABLE `" . WIKINDX_DB_TABLEPREFIX . "plugin_wordprocessor` (
                	`pluginwordprocessorId` int(11) NOT NULL AUTO_INCREMENT,
                	`pluginwordprocessorHashFilename` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                	`pluginwordprocessorFilename` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                	`pluginwordprocessorUserId` int(11) NOT NULL,
                	`pluginwordprocessorTimestamp` datetime NOT NULL,
                	PRIMARY KEY (`pluginwordprocessorId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
			");
        }
        // Unlink papers and delete from papers table if a user has been deleted
        $resultset1 = $this->db->select('plugin_wordprocessor', 'pluginwordprocessorUserId');
        while ($row1 = $this->db->fetchRow($resultset1))
        {
            $this->db->formatConditions(['usersId' => $row1['pluginwordprocessorUserId']]);
            if (!$this->db->selectFirstField('users', 'usersId'))
            {
                $this->db->formatConditions(['pluginwordprocessorUserId' => $row1['pluginwordprocessorUserId']]);
                $resultset2 = $this->db->select('plugin_wordprocessor', 'pluginwordprocessorHashFilename');
                while ($row2 = $this->db->fetchRow($resultset2))
                {
                    $file = $this->papersDir . DIRECTORY_SEPARATOR . $row2['pluginwordprocessorHashFilename'];
                    @unlink($file);
                }
                $this->db->formatConditions(['pluginwordprocessorUserId' => $row1['pluginwordprocessorUserId']]);
                $this->db->delete('plugin_wordprocessor');
            }
        }
    }
    /**
     * Heading viewed across top of editor
     *
     * @param false|string $title
     * @param string $status
     * @param string $exportFile
     *
     * @return string
     */
    private function heading($title = FALSE, $status = '&nbsp;', $exportFile = '&nbsp;')
    {
        $heading = HTML\tableStart();
        $heading .= HTML\trStart();
        $heading .= HTML\td(HTML\h($this->pluginmessages->text('headingPaper'), FALSE, 3), 'width30percent');
        if ($title)
        {
            $heading .= "<td id=\"wpTitle\" name=\"wpTitle\" align=\"left\" width=\"30%\">$title</td>\n";
        }
        else
        {
            $heading .= "<td id=\"wpTitle\" name=\"wpTitle\" align=\"left\" width=\"30%\">" . $this->pluginmessages->text('new') . "</td>\n";
        }
        $heading .= "<td id=\"wpStatus\" name=\"wpStatus\" align=\"left\">$status</td>\n";
        $heading .= "<td id=\"wpExportFile\" name=\"wpExportFile\" align=\"left\">$exportFile</td>\n";
        $heading .= HTML\trEnd();
        $heading .= HTML\tableEnd();

        return $heading;
    }
    /**
     * Get all this user's papers' titles and ids
     *
     * @param bool|string $download
     *
     * @return array
     */
    private function grabPapers($download = FALSE)
    {
        $papers = $papersTimestamp = [];
        $fields = ["pluginwordprocessorId", "pluginwordprocessorHashFilename", "pluginwordprocessorFilename",
            "pluginwordprocessorTimestamp", ];
        $userId = $this->session->getVar("setup_UserId");
        $this->db->ascDesc = $this->db->desc;
        $this->db->formatConditions(['pluginwordprocessorUserId' => $userId]);
        $this->db->orderBy('pluginwordprocessorTimestamp');
        $recordSet = $this->db->select("plugin_wordprocessor", $fields);
        while ($row = $this->db->fetchRow($recordSet))
        {
            if (!$this->checkFileExists($row['pluginwordprocessorHashFilename']))
            {
                continue;
            }
            if ($download)
            {
                $papers[$row['pluginwordprocessorId']][0] = $row['pluginwordprocessorHashFilename'];
                $papers[$row['pluginwordprocessorId']][1] = HTML\dbToFormTidy($row['pluginwordprocessorFilename']);
                $papersTimestamp[$row['pluginwordprocessorId']] = HTML\dbToFormTidy($row['pluginwordprocessorTimestamp']);
            }
            else
            {
                $papers[$row['pluginwordprocessorId']] = HTML\dbToFormTidy($row['pluginwordprocessorFilename']);
                $papersTimestamp[$row['pluginwordprocessorId']] = HTML\dbToFormTidy($row['pluginwordprocessorTimestamp']);
            }
        }

        return [$papers, $papersTimestamp];
    }
    /**
     * Check file exists
     *
     * @param string $hashname Hashname of file
     *
     * @return bool TRUE (file exists) or FALSE (file does not exist)
     */
    private function checkFileExists($hashname)
    {
        return file_exists($this->papersDir . DIRECTORY_SEPARATOR . $hashname);
    }
}
