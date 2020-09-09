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
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));

include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));

$script  = '<script src="' . WIKINDX_URL_BASE . '/core/tiny_mce/tiny_mce_popup.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
$script .= '<script src="' . WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_PLUGINS . '/wordprocessor/wikindxWPcommon.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
$script .= '<script src="' . WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_PLUGINS . '/wordprocessor/' . basename(__DIR__) . '/js/wikindxWPFootnotedialog.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
GLOBALS::addTplVar('scripts', $script);

$class = new WPFootnoteDialog();

class WPFootnoteDialog
{
    private $pluginmessages;
    private $coremessages;

    public function __construct()
    {
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
