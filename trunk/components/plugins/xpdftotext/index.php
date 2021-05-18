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
  * xpdftotext_MODULE class.
  *
  * XpdftoText tool
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));


class xpdftotext_MODULE
{
    // Cache directory of this plugin
    const CACHE_DIRECTORY = WIKINDX_DIR_BASE . DIRECTORY_SEPARATOR . WIKINDX_DIR_CACHE_PLUGINS . DIRECTORY_SEPARATOR . "xpdftotext";
    
    public $authorize;
    public $menus;
    
    private $pluginmessages;
    private $coremessages;
    private $config;
    private $vars;
    private $session;
    private $formData = [];

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        $this->session = FACTORY_SESSION::getInstance();
        // Conform to admin's configuration
        if ($this->session->getVar("setup_Superadmin"))
        {
            $this->displayItem = TRUE;
        }
        elseif (WIKINDX_METADATA_USERONLY && $this->session->getVar("setup_UserId"))
        {
            $this->displayItem = TRUE;
        }
        elseif (WIKINDX_METADATA_ALLOW)
        {
            $this->displayItem = TRUE;
        }
        if (!$this->displayItem)
        {
            return;
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('xpdftotext', 'xpdftotextMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->coreerrors = FACTORY_ERRORS::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $this->config = new xpdftotext_CONFIG();
        $this->authorize = $this->config->authorize;
        if ($menuInit)
        {
            $this->makeMenu($this->config->menus);

            return; // Need do nothing more as this is simply menu initialisation.
        }

        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('heading'));
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('xpdftotext'));
        $this->vars = GLOBALS::getVars();
        
        // Make sure the utilities are executable
        $bindir = implode(DIRECTORY_SEPARATOR, [__DIR__, "bin"]);
        // Select a binary for the current OS
        switch (\UTILS\OSName())
        {
            case "windows":
                $pathpdftotext = implode(DIRECTORY_SEPARATOR, [$bindir, "pdftotext-win.exe"]);
                $pathpdfinfo = implode(DIRECTORY_SEPARATOR, [$bindir, "pdfinfo-win.exe"]);
            break;
            case "mac":
                $pathpdftotext = implode(DIRECTORY_SEPARATOR, [$bindir, "pdftotext-mac"]);
                $pathpdfinfo = implode(DIRECTORY_SEPARATOR, [$bindir, "pdfinfo-mac"]);
            break;
            default:
                $pathpdftotext = implode(DIRECTORY_SEPARATOR, [$bindir, "pdftotext-lin"]);
                $pathpdfinfo = implode(DIRECTORY_SEPARATOR, [$bindir, "pdfinfo-lin"]);
            break;
        }
        
        foreach ([$pathpdftotext, $pathpdfinfo] as $path)
        {
            if (!is_executable($path))
            {
                @chmod($path, 0777);
                clearstatcache(TRUE, $path);
            }
        }
        foreach ([$pathpdftotext, $pathpdfinfo] as $path)
        {
            if (!is_executable($path))
            {
                die(
                    \HTML\p($this->pluginmessages->text("chmodInvit")) .
                    "<ul>
                        <li>" . htmlspecialchars('chmod +x "' . realpath($pathpdftotext) . '"') . "</li>
                        <li>" . htmlspecialchars('chmod +x "' . realpath($pathpdfinfo) . '"')   . "</li>
                    </ul>"
                );
            }
        }
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
     * This is the initial method called from the menu item
     */
    public function init()
    {
        return $this->display("");
    }
    
    /**
     * display
     *
     * @param mixed $message
     * @param string $fileNametxt
     */
    public function display($message = FALSE, $fileNametxt = "")
    {
        $pString = "";
        $pString .= $message ?? "";
        $pString .= \HTML\p($this->pluginmessages->text("introduction"));
        
        if (file_exists($fileNametxt))
        {
            $pString .= \HTML\hr();
            $pString .= \HTML\h($this->pluginmessages->text("convertedFileHeading"));
            
            $pString .= \HTML\p(\HTML\a("link", basename($fileNametxt), "index.php?action=xpdftotext_downloadFile&amp;filename=" . htmlentities(basename($fileNametxt)), "_blank"));
        }
        
        $pString .= \HTML\hr();
        $pString .= \HTML\h($this->pluginmessages->text("submissionFormHeading"));
        
        if (ini_get("file_uploads"))
        {
            $pString .= \FORM\formMultiHeader("xpdftotext_convert");
            $pString .= \HTML\tableStart('widthauto');
            $pString .= \HTML\tbodyStart();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("file"));
            $pString .= \HTML\td(
                \FORM\fileUpload(
                    "",
                    "pdf_File",
                    30,
                    ".pdf"
                )
                . " (max.&nbsp;" . \FILE\formatSize(\FILE\fileUploadMaxSize()) . ")"
            );
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("firstPage"));
            $pString .= \HTML\td(\FORM\textInput("", "f", "", 4));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("lastPage"));
            $pString .= \HTML\td(\FORM\textInput("", "l", "", 4));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("mode"));
            $pString .= \HTML\td(\FORM\selectedBox("", "mode", ["default", "layout", "lineprinter", "raw", "simple", "simple2", "table"], "default", 1));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("characterPitch"));
            $pString .= \HTML\td(\FORM\textInput("", "fixed", "", 4));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("lineSpacing"));
            $pString .= \HTML\td(\FORM\textInput("", "linespacing", "", 4));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("clip"));
            $pString .= \HTML\td(\FORM\checkbox("", "clip", FALSE));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("nodiag"));
            $pString .= \HTML\td(\FORM\checkbox("", "nodiag", FALSE));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("nopgbrk"));
            $pString .= \HTML\td(\FORM\checkbox("", "nopgbrk", FALSE));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("marginl"));
            $pString .= \HTML\td(\FORM\textInput("", "marginl", 0, 4));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("marginr"));
            $pString .= \HTML\td(\FORM\textInput("", "marginr", 0, 4));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("margint"));
            $pString .= \HTML\td(\FORM\textInput("", "margint", 0, 4));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("marginb"));
            $pString .= \HTML\td(\FORM\textInput("", "marginb", 0, 4));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("opw"));
            $pString .= \HTML\td(\FORM\textInput("", "opw", ""));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\trStart();
            $pString .= \HTML\td($this->pluginmessages->text("upw"));
            $pString .= \HTML\td(\FORM\textInput("", "upw", ""));
            $pString .= \HTML\trEnd();
            $pString .= \HTML\tbodyEnd();
            $pString .= \HTML\tableEnd();
            $pString .= \HTML\p(\FORM\formSubmit($this->coremessages->text("submit", "Submit")));
        }
        else
        {
            $pString .= \HTML\td($this->coremessages->text("misc", "uploadDisabled"));
        }
        
        foreach (\FILE\fileInDirToArray(self::CACHE_DIRECTORY) as $file)
        {
            $path = implode(DIRECTORY_SEPARATOR, [self::CACHE_DIRECTORY, $file]);
            // Delete outdated files (at least 5 minutes old)
            if ((time() - filemtime($path)) >= 60 * 5)
            {
                @unlink($path);
            }
        }
        
        GLOBALS::addTplVar('content', $pString);
    }
    
    /**
     * Convert a PDF to text
     *
     * @param mixed $message
     */
    public function convert()
    {
        $vars = GLOBALS::getVars();
        $session = FACTORY_SESSION::getInstance();
        $message = "";
        $fileNametxt = "";
        
        // Check for file input
        if (isset($_FILES['pdf_File']))
        {
            $fileNamepdf = implode(DIRECTORY_SEPARATOR, [self::CACHE_DIRECTORY, \UTILS\uuid() . ".pdf"]);
            $fileNametxt = implode(DIRECTORY_SEPARATOR, [self::CACHE_DIRECTORY, \UTILS\uuid() . ".txt"]);
            if (move_uploaded_file($_FILES['pdf_File']['tmp_name'], $fileNamepdf))
            {
                $options = [];
                
                // Options with a numeric arg
                foreach (["f", "l", "fixed", "linespacing", "marginl", "marginr", "margint", "marginb"] as $optname)
                {
                    if (isset($vars[$optname]))
                    {
                        $value = trim($vars[$optname]);
                        if (is_numeric($value))
                        {
                            $value = intval($value);
                            if ($value >= 0)
                            {
                                $options[$optname] = $value;
                            }
                        }
                    }
                }
                
                // Options with a string arg
                foreach (["opw", "upw"] as $optname)
                {
                    if (isset($vars[$optname]))
                    {
                        $options[$optname] = $vars[$optname];
                    }
                }
                
                // Boolean options
                foreach (["clip", "nodiag", "nopgbrk"] as $optname)
                {
                    if (isset($vars[$optname]))
                    {
                        $options[$optname] = "";
                    }
                }
                
                // Mode options
                if (isset($vars["mode"]))
                {
                    if ($vars["mode"] != "default")
                    {
                        $options[$vars["mode"]] = "";
                    }
                }
            
                include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "XPDFREADER.php"]));
                
                $text = \XPDFREADER\pdftotext($fileNamepdf, $options);
                file_put_contents($fileNametxt, $text);
                @unlink($fileNamepdf);
                
                $message = "";
            }
            else
            {
                $message = $this->coreerrors->text("file", "upload");
                
            }
        }
        else
        {
            $message = $this->coreerrors->text("file", "upload");
        }
        
        $this->display($message, $fileNametxt);
    }
    
    /**
     * downloadFile
     */
    public function downloadFile()
    {
        $dirName = self::CACHE_DIRECTORY;
        $filename = $this->vars['filename'];
        $filepath = $dirName . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($filepath))
        {
            $type = 'plain/text';
            $size = filesize($filepath);
            $lastmodified = date(DateTime::RFC1123, filemtime($filepath));
            \FILE\setHeaders($type, $size, $filename, $lastmodified);
            \FILE\readfile_chunked($filepath);
        }
        else
        {
            header('HTTP/1.0 404 Not Found');
            $this->badInput->closeType = 'closePopup';
            $this->badInput->close($this->coreerrors->text("file", "missing"));
        }
        die;
    }
}
