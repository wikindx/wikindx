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
  * backupMysql class.
  *
  * Backup the database
  *
  * Support MySQL db engine
  *
  * Database dumps are written into the cache directory of this plugin which should be writeable by the webserver user
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");


class backupmysql_MODULE
{
    // The dump directory is the cache dircetory of this plugin
    const DUMP_DIRECTORY = WIKINDX_DIR_CACHE_PLUGINS . DIRECTORY_SEPARATOR . "backupmysql";

    public $authorize;
    public $menus;

    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $config;
    private $session;
    private $vars;
    private $badInput;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        include_once("core/messages/PLUGINMESSAGES.php");
        $this->pluginmessages = new PLUGINMESSAGES('backupmysql', 'backupmysqlMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "config.php");
        $this->config = new backupmysql_CONFIG();
        $this->session = FACTORY_SESSION::getInstance();
        $this->authorize = $this->config->authorize;
        $this->badInput = FACTORY_BADINPUT::getInstance();
        if ($menuInit) {
            $this->makeMenu($this->config->menus);

            return; // Need do nothing more as this is simply menu initialisation.
        }

        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize)) { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }

        $this->vars = GLOBALS::getVars();
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('heading'));
    }
    /**
     * This is the initial method called from the menu item
     */
    public function init()
    {
        return $this->display();
    }
    /**
     * display
     *
     * @param mixed $message
     */
    public function display($message = FALSE)
    {
    	if ($message) {
            $pString = $message;
        }
        else {
            $pString = '';
        }
        $pString .= HTML\tableStart('generalTable borderStyleSolid');
        $pString .= HTML\trStart();
        $td = FORM\formHeader("backupmysql_backup");
        $td .= HTML\p(FORM\formSubmit($this->pluginmessages->text('heading')));
        $td .= FORM\formEnd();
        $pString .= HTML\td($td);
        $files = $this->listFiles();
        if (!empty($files)) {
        	$icons = FACTORY_LOADICONS::getInstance();
            $td = '';
            foreach ($files as $fileName => $tStamp) {
                $td .= HTML\a("link", $fileName, "index.php?action=backupmysql_downloadFile" .
                htmlentities("&filename=" . $fileName), "_blank") . BR . LF;
            }
            $pString .= HTML\td($td);
        }
        if (!empty($files)) {
            $td = HTML\td($this->deleteList(array_keys($files)));
            $pString .= HTML\td($td);
        }
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        if (!empty($files)) {
        	$pString .= $this->renameInit($files);
        }
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * downloadFile
     */
    public function downloadFile()
    {
        $dirName = self::DUMP_DIRECTORY;
        $filename = $this->vars['filename'];
        $filepath = $dirName . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($filepath)) {
            $type = 'application/x-sql+gzip';
            $size = filesize($filepath);
            $lastmodified = date(DateTime::RFC1123, filemtime($filepath));
            FILE\setHeaders($type, $size, $filename, $lastmodified);
            FILE\readfile_chunked($filepath);
        } else {
            $this->badInput->closeType = 'closePopup';
            $this->badInput->close($this->errors->text("file", "missing"));
        }
        die;
    }
    /**
    * Form for renaming file
    */
    private function renameInit($files)
    {
        foreach ($files as $file => $null) {
            $fileArray[str_replace('.sql.gz', '', $file)] = $file;
        }
    	$pString = \FORM\formHeader('backupmysql_rename');
        $pString .= \HTML\tableStart('generalTable borderStyleSolid');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValue($this->pluginmessages->text("rename"), "renameFiles", $fileArray, 10));
        $pString .= \FORM\formEnd();
        $pString .= \HTML\td($this->transferArrow());
        $td = \HTML\tableStart();
        $td .= \HTML\trStart();
        $td .= \HTML\td(\HTML\div('fileDiv', $this->displayFile(TRUE)));
        $td .= \HTML\trEnd();
        $td .= \HTML\tableEnd();
        $pString .= \HTML\td($td);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        \AJAX\loadJavascript();
        return $pString;
    }
    /**
     * Display interface to edit filename
     *
     * @param bool $initialDisplay
     */
    public function displayFile($initialDisplay = FALSE)
    {
    	$file = '';
        if (!$initialDisplay) {
            $file = $this->vars['ajaxReturn'];
            $this->session->setVar('backupmysql_oldFileName', $this->vars['ajaxReturn']);
        }
        $hint = \HTML\aBrowse('green', '', $this->coremessages->text("hint", "hint"), '#', "", 
        	htmlentities($this->pluginmessages->text("invalidChars")));
        $pString = \FORM\textInput($this->pluginmessages->text("newFileName") . \HTML\span('*', 'required'),
            'newFileName',
            $file,
            30,
            255
        ) . '.sql.gz' . BR . LF . $hint;
        $pString .= \HTML\p(\FORM\formSubmit($this->pluginmessages->text("rename")));
        if ($initialDisplay) {
            return $pString;
        }
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$pString"]));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * transferArrow
     *
     * @return string
     */
    private function transferArrow()
    {
        $jScript = 'index.php?action=backupmysql_displayFile';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'renameFiles',
            'targetDiv' => 'fileDiv',
        ];
        $image = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
        return $image;
    }
    /**
    * Rename a file
    */
    public function rename()
    {
    	if (!array_key_exists('newFileName', $this->vars) 
    		|| !$this->vars['newFileName'] || !$this->session->getVar('backupmysql_oldFileName')) {
            $this->display($this->errors->text("inputError", "invalid"));
            return;
        }
        $oldFileName = self::DUMP_DIRECTORY . DIRECTORY_SEPARATOR . $this->session->getVar('backupmysql_oldFileName') . '.sql.gz';
    	$this->session->delVar('backupmysql_oldFileName');
// Sanitize filename
    	$file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $this->vars['newFileName']);
    	$file = mb_ereg_replace("([\.]{2,})", '', $file);
    	if (!$file) {
            $this->display($this->errors->text("inputError", "invalid"));
            return;
    	}
        $newFileName = self::DUMP_DIRECTORY . DIRECTORY_SEPARATOR . $file . '.sql.gz';
        if (!rename($oldFileName, $newFileName)) {
            $this->display(HTML\p($this->errors->text("file", "write", $newFileName)));
            return;
        }
        else {
	        $this->display(HTML\p($this->pluginmessages->text("renamed"), 'success'));
	        return;
	    }
    }
    /**
     * Delete chosen files
     */
    public function delete()
    {
        if (!array_key_exists('files', $this->vars)) {
            $this->display($this->errors->text("inputError", "missing"));
            return;
        }
        foreach ($this->vars['files'] as $file) {
            @unlink(self::DUMP_DIRECTORY . DIRECTORY_SEPARATOR . $file);
        }
        $this->display(HTML\p($this->pluginmessages->text("deleted"), 'success'));
    }
    /**
     * backup the database
     */
    public function backup()
    {
        // Save memory limit configuration
        $memory_limit = ini_get('memory_limit');

        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        ini_set('memory_limit', '-1');
        
        // Check the cache directory is writable
        if (!is_writable(self::DUMP_DIRECTORY)) {
            $this->display(HTML\p($this->pluginmessages->text('noWrite', mb_substr(sprintf('%o', fileperms(self::DUMP_DIRECTORY)), -4)), 'error'));

            return;
        }
        require_once 'mysql_backup.class.php';
        $backup_obj = new MySQL_Backup();
        
        //Tables you wish to backup. All tables in the database will be backed up if this array is null.
        $backup_obj->tables = [];
        
        //Add DROP TABLE IF EXISTS queries before CREATE TABLE in backup file.
        $backup_obj->drop_tables = TRUE;

        //Only structure of the tables will be backed up if true.
        $backup_obj->struct_only = FALSE;

        //Include comments in backup file if true.
        $backup_obj->comments = TRUE;

        //Directory on the server where the backup file will be placed. Used only if task parameter equals MSB_SAVE.
        $backup_obj->backup_dir = self::DUMP_DIRECTORY;

        //Default file name format.
        $backup_obj->fname_format = 'd_m_y__H_i_s';
        
        // Create the backup file on the server.
        $task = MSB_SAVE;

        //Optional name of backup file if using 'MSB_SAVE' or 'MSB_DOWNLOAD'. If nothing is passed, the default file name format will be used.
        $filename = '';

        //Use GZip compression if using 'MSB_SAVE' or 'MSB_DOWNLOAD'?
        $use_gzip = TRUE;

        if (!$backup_obj->Execute($task, $filename, $use_gzip)) {
            $output = HTML\p($backup_obj->error, 'error');
        } else {
            $output = HTML\p('Operation Completed Successfully At: <strong>' . date('g:i:s A') . '</strong><em> ( Local Server Time )</em>', 'success');
        }
        $this->display($output);

        // Restore memory limit configuration
        ini_set('memory_limit', $memory_limit);
    }
    /**
     * Make the menus
     *
     * @param mixed $menuArray
     */
    private function makeMenu($menuArray)
    {
        $this->menus = [$menuArray[0] => [$this->pluginmessages->text('menu') => "init"]];
    }
    /**
     * List all dumps for deleting
     *
     * @param mixed $files
     *
     * @return string
     */
    private function deleteList($files)
    {
        foreach ($files as $file) {
            $fileArray[$file] = $file;
        }
        $td = FORM\formHeader("backupmysql_delete");
        $td .= FORM\selectFBoxValueMultiple(FALSE, 'files', $fileArray, 10) .
            BR . HTML\span($this->coremessages->text("hint", "multiples"), 'hint');
        $td .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Delete")));
        $td .= FORM\formEnd();

        return $td;
    }
    /**
     * List all files in the cache directory
     *
     * @return array $fileArray
     */
    private function listFiles()
    {
        $fileArray = [];
        
        foreach (\FILE\fileInDirToArray(self::DUMP_DIRECTORY) as $file) {
            $fileArray[$file] = filemtime(self::DUMP_DIRECTORY . DIRECTORY_SEPARATOR . $file);
        }

        asort($fileArray, SORT_NUMERIC);
        $fileArray = array_reverse($fileArray);

        return $fileArray;
    }
}
