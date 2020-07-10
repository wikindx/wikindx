<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
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
$script .= '<script src="' . WIKINDX_BASE_URL . '/core/tiny_mce/utils/mctabs.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
$script .= '<script src="' . WIKINDX_BASE_URL . '/core/tiny_mce/utils/form_utils.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
$script .= '<script src="' . WIKINDX_BASE_URL . '/core/tiny_mce/utils/validate.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
$script .= '<script src="' . WIKINDX_BASE_URL . '/core/tiny_mce/plugins/' . basename(__DIR__) . '/js/advlink.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
GLOBALS::addTplVar('scripts', $script);

$class = new linkDialog();

class linkDialog
{
    private $messages;

    public function __construct()
    {
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
