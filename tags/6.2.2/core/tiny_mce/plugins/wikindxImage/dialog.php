<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

// Portions of this code (for browsing images on the server) taken and adapted from Encode Explorer: http://encode-explorer.siineiolekala.net/

session_start();
if (isset($_SESSION) && array_key_exists('wikindxBasePath', $_SESSION) && $_SESSION['wikindxBasePath'])
{
    chdir($_SESSION['wikindxBasePath']); // tinyMCE changes the phpbasepath
}
else
{
    $oldPath = dirname(__FILE__);
    $split = preg_split('/' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/u', $oldPath);
    array_splice($split, -4); // get back to trunk
    $newPath = implode(DIRECTORY_SEPARATOR, $split);
    chdir($newPath);
}

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");

$script = '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/tiny_mce/tiny_mce_popup.js"></script>';
$script .= '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/tiny_mce/plugins/' . basename(__DIR__) . '/js/wikindxImagedialog.js"></script>';
GLOBALS::addTplVar('scripts', $script);

$class = new imageDialog();

class imageDialog
{
    private $messages;
    private $session;
    private $config;

    public function __construct()
    {
        $this->config = FACTORY_CONFIG::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->imageDialogueRun();
    }

    /**
     * Add image dialogue
     *
     * @return string
     */
    private function imageDialogueRun()
    {
        $pString = \HTML\h($this->messages->text('tinymce', "headingAddImage"), FALSE, 3);
        $pString .= $this->urlForm();
        $pString .= \HTML\hr();
        $pString .= \HTML\p();
        GLOBALS::addTplVar('content', $pString);
        $configDbStructure = FACTORY_CONFIGDBSTRUCTURE::getInstance();
        include_once("core/file/images.php");
        if ($configDbStructure->getOne('configImagesAllow'))
        {
            // As user can upload images, we check again that user is registered
            if ($this->session->getVar('setup_UserId'))
            {
//
                // This is where the system is activated.
                // We check if the user wants an image and show it. If not, we show the explorer.
//
                if (!ImageServer::showImage())
                {
                    $encodeExplorer = new EncodeExplorer();
                    $encodeExplorer->init();
                    $location = new Location();
                    $fileManager = new FileManager();
                    $fileManager->run($location);
                    $encodeExplorer->run($location);
                }
            }
        }
        FACTORY_CLOSEPOPUP::getInstance();
    }
    /**
     * URL upload form
     *
     * @return string
     */
    private function urlForm()
    {
        $js = "onsubmit=\"return imageDialogUrl();\"";
        $pString = \FORM\formHeaderVisibleAction("dialog.php", "insertImage", $js);
        $pString .= \FORM\hidden("method", "addImage");
        $pString .= \FORM\textInput($this->messages->text('tinymce', "imagePath"), 'imagePath', 'http://', 50);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Submit"), 'insert'));
        $pString .= \FORM\formEnd();

        return $pString;
    }
}