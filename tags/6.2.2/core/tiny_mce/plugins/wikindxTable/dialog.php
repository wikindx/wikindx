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
    array_splice($split, -4); // get back to trunk
    $newPath = implode(DIRECTORY_SEPARATOR, $split);
    chdir($newPath);
}

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");

$script = '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/tiny_mce/tiny_mce_popup.js"></script>';
$script .= '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/tiny_mce/plugins/' . basename(__DIR__) . '/js/wikindxTabledialog.js"></script>';
GLOBALS::addTplVar('scripts', $script);

$class = new WPTableDialog();

class WPTableDialog
{
    private $messages;
    private $config;

    public function __construct()
    {
        $this->config = FACTORY_CONFIG::getInstance();

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
