<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

if(!function_exists("SetWikindxBasePath")) {
    function SetWikindxBasePath()
    {
        $wikindxBasePath = __DIR__;
        while (!in_array(basename($wikindxBasePath), ["", "components", "core"])) {
            $wikindxBasePath = dirname($wikindxBasePath);
        }
        if (basename($wikindxBasePath) == "") {
            die("
                \$WIKINDX_WIKINDX_PATH in config.php is set incorrectly
                and WIKINDX is unable to set the installation path automatically.
                You should set \$WIKINDX_WIKINDX_PATH in config.php.
            ");
        }
        chdir(dirname($wikindxBasePath));
    }
}

SetWikindxBasePath();

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");

$script = '<script src="' . WIKINDX_BASE_URL . '/core/tiny_mce/tiny_mce_popup.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
$script .= '<script src="' . WIKINDX_BASE_URL . '/core/tiny_mce/plugins/' . basename(__DIR__) . '/js/wikindxTabledialog.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
GLOBALS::addTplVar('scripts', $script);

$class = new WPTableDialog();

class WPTableDialog
{
    private $messages;

    public function __construct()
    {
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->tableDialogue();
    }

    /**
     * Add table dialogue
     *
     * @return string
     */
    private function tableDialogue()
    {
        $pString = \HTML\h($this->messages->text('tinymce', "headingAddTable"), FALSE, 3);
        $js = "onsubmit=\"return tableDialog();\"";
        $pString .= \FORM\formHeaderVisibleAction("dialog.php", "insertTable", $js);
        $pString .= \FORM\hidden("method", "addTable");
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $td = \FORM\textInput($this->messages->text('tinymce', "tableColumns"), 'columns', 2, 3);
        $pString .= \HTML\td($td);
        $td = \FORM\textInput($this->messages->text('tinymce', "tableRows"), 'rows', 2, 3);
        $pString .= \HTML\td($td);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Submit"), 'insert'));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
}
