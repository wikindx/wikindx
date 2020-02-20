<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

function SetWikindxBasePath()
{
    $wikindxBasePath = __DIR__;
    while (!in_array(basename($wikindxBasePath), ["", "components"]))
    {
        $wikindxBasePath = dirname($wikindxBasePath);
    }
    if (basename($wikindxBasePath) == "")
    {
        die("
            \$WIKINDX_WIKINDX_PATH in config.php is set incorrectly
            and WIKINDX is unable to set the installation path automatically.
            You should set \$WIKINDX_WIKINDX_PATH in config.php.
        ");
    }
    chdir(dirname($wikindxBasePath));
}

SetWikindxBasePath();

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");

include_once("core/messages/PLUGINMESSAGES.php");

$script = '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/tiny_mce/tiny_mce_popup.js"></script>';
$script .= '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/' . str_replace("\\", "/", WIKINDX_DIR_COMPONENT_PLUGINS) . '/wordprocessor/wikindxWPcommon.js"></script>';
$script .= '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/' . str_replace("\\", "/", WIKINDX_DIR_COMPONENT_PLUGINS) . '/wordprocessor/' . basename(__DIR__) . '/js/wikindxWPFootnotedialog.js"></script>';
GLOBALS::addTplVar('scripts', $script);

$class = new WPFootnoteDialog();

class WPFootnoteDialog
{
    private $pluginmessages;
    private $coremessages;
    private $config;

    public function __construct()
    {
        $this->config = FACTORY_CONFIG::getInstance();
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->pluginmessages = new PLUGINMESSAGES('wordprocessor', 'wordprocessorMessages');
        
        $this->footnoteDialogue();
    }

    /**
     * Save paper dialogue
     */
    private function footnoteDialogue()
    {
        $pString = HTML\h($this->pluginmessages->text("headingAddFootnote"), FALSE, 3);
        $js = "onsubmit=\"return footnoteDialog();\"";
        $pString .= FORM\formHeaderVisibleAction("dialog.php", "insertFootnote", $js);
        $pString .= FORM\hidden("method", "addFootnote");
        $pString .= HTML\p(FORM\textareaInput(FALSE, "footnote", FALSE, 60, 3));
        $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit"), 'insert'));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
}
