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
$script .= '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/tiny_mce/utils/mctabs.js"></script>';
$script .= '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/tiny_mce/utils/form_utils.js"></script>';
$script .= '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/tiny_mce/utils/validate.js"></script>';
$script .= '<script src="' . FACTORY_CONFIG::getInstance()->WIKINDX_BASE_URL . '/core/tiny_mce/plugins/' . basename(__DIR__) . '/js/advlink.js"></script>';
GLOBALS::addTplVar('scripts', $script);

$class = new linkDialog();

class linkDialog
{
    private $messages;
    private $config;

    public function __construct()
    {
        $this->config = FACTORY_CONFIG::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();


        $this->linkDialogue();
    }


    /**
     * Add link dialogue
     *
     * @return string
     */
    private function linkDialogue()
    {
        $pString = \HTML\h($this->messages->text('tinymce', "headingAddLink"), FALSE, 3);
        $js = "onsubmit=\"insertAction();return false;\"";
        $pString .= \FORM\formHeaderVisibleAction("dialog.php", "insertLink", $js);
        $pString .= \FORM\hidden("method", "addImage");
        $linkPath = $this->messages->text('tinymce', "linkPath");
        $pString .= <<<END
	<fieldset>

		<table border="0" cellpadding="4" cellspacing="0" role="presentation">
			<tr>
				<td class="nowrap"><label id="hreflabel" for="href">$linkPath</label></td>
					<td><table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td><input id="href" name="href" type="text" class="mceFocus" value="" onchange="selectByValue(this.form,'linklisthref',this.value);" aria-required="true"></td>
					<td id="hrefbrowsercontainer">&nbsp;</td>
				</tr>
				</table></td>
			</tr>
		</table>
	</fieldset>
END;
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Submit"), 'insert'));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
}
