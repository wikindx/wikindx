<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
session_start();
if (isset($_SESSION) && array_key_exists('wikindxBasePath', $_SESSION) && $_SESSION['wikindxBasePath'])
{
    chdir($_SESSION['wikindxBasePath']); // tinyMCE changes the phpbasepath
}
else
{
    $oldPath = dirname(__FILE__);
    $split = preg_split('/' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/u', $oldPath);
    array_splice($split, -3); // get back to trunk
    $newPath = implode(DIRECTORY_SEPARATOR, $split);
    chdir($newPath);
}

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");

include_once("core/messages/PLUGINMESSAGES.php");

$script = '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/tiny_mce/tiny_mce_popup.js"></script>';
$script .= '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/' . str_replace("\\", "/", WIKINDX_DIR_COMPONENT_PLUGINS) . '/wordprocessor/wikindxWPcommon.js"></script>';
$script .= '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/' . str_replace("\\", "/", WIKINDX_DIR_COMPONENT_PLUGINS) . '/wordprocessor/' . basename(__DIR__) . '/js/wikindxWPSavedialog.js"></script>';
GLOBALS::addTplVar('scripts', $script);

$class = new WPSaveDialog();

class WPSaveDialog
{
    private $pluginmessages;
    private $coremessages;
    private $session;
    private $vars;

    public function __construct()
    {
        $this->config = FACTORY_CONFIG::getInstance();
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->pluginmessages = new PLUGINMESSAGES('wordprocessor', 'wordprocessorMessages');


        $this->session = FACTORY_SESSION::getInstance();
        $this->vars = GLOBALS::getVars();
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] = 'save'))
        {
            include_once(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "WPCOMMON.php");
            $common = new WPCOMMON();
            $common->save();
            FACTORY_CLOSEPOPUP::getInstance();
        }
        else
        {
            $this->saveDialogue();
        }
    }

    /**
     * Save paper dialogue
     *
     * @return string
     */
    private function saveDialogue()
    {
        $savedMessage = "<span class=\\'success\\'>" . $this->pluginmessages->text("savedStatus") . "</span>";
        $notSavedMessage = "<span class=\\'error\\'>" . $this->pluginmessages->text("notSavedStatus") . "</span>";
        $pString = HTML\h($this->pluginmessages->text("headingSavePaper"), FALSE, 3);
        $js = "onsubmit=\"return wordprocessorSave('$savedMessage', '$notSavedMessage');\"";
        $pString .= FORM\formHeaderVisibleAction("dialog.php", "wpSave", $js);
        $pString .= FORM\hidden("method", "save");
        if ($hashFilename = $this->session->getVar('wp_HashFilename'))
        {
            $pString .= FORM\hidden("hashFilename", $hashFilename);
        }
        if ($id = $this->session->getVar('wp_Id'))
        {
            $pString .= FORM\hidden("id", $id);
        }
        $pString .= "<input type=\"hidden\" id=\"hdnpaperText\" name=\"hdnpaperText\" value=\"\">";
        if ($this->session->getVar("wp_Title"))
        {
            $title = base64_decode($this->session->getVar("wp_Title"));
        }
        else
        {
            $title = '';
        }
        //		$export = $this->session->getVar("wp_ExportAndSave") ? TRUE : FALSE;
        $pString .= HTML\p(FORM\textInput($this->pluginmessages->text("paperTitle"), "title", $title, 40) .
            BR .
            $this->pluginmessages->text("saveAsNewVersion") . ':&nbsp;&nbsp;' . FORM\checkbox(FALSE, 'saveAsNewVersion'));
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Save"), 'insert'));
        $pString .= FORM\formEnd();
        // Tell save() that we're saving, not exporting
        $this->session->delVar("wp_Exporting");
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
}
