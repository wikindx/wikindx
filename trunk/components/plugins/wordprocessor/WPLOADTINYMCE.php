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
 *	TinyMCE for wordProcessor class.
 */
class WPLOADTINYMCE extends LOADTINYMCE
{
    // Constructor
    public function __construct()
    {
        $session = FACTORY_SESSION::getInstance();
        $session->setVar("tinyMCE_mode", "wordprocessor");
        $this->cssPath = WIKINDX_BASE_URL . '/' . WIKINDX_URL_COMPONENT_TEMPLATES . '/' . GLOBALS::getUserVar('Template') . '/tinymce.css?ver=' . WIKINDX_PUBLIC_VERSION;
        $this->cssPopupPath = WIKINDX_BASE_URL . '/' . WIKINDX_URL_COMPONENT_TEMPLATES . '/' . GLOBALS::getUserVar('Template') . '/template.css?ver=' . WIKINDX_PUBLIC_VERSION;
    }
    /**
     * Load tinymce -- full configuration for the word processor
     *
     * @return string JS code
     */
    public function loadWPTextarea()
    {
        $path = $this->path;
        $externalPluginPath = WIKINDX_BASE_URL . '/' . WIKINDX_URL_COMPONENT_PLUGINS . '/' . basename(__DIR__) . '/';
        $exportPluginPath = $externalPluginPath . 'wikindxWPExport/editor_plugin.js?ver=' . WIKINDX_PUBLIC_VERSION;
        $savePluginPath = $externalPluginPath . 'wikindxWPSave/editor_plugin.js?ver=' . WIKINDX_PUBLIC_VERSION;
        $statsPluginPath = $externalPluginPath . 'wikindxWPStats/editor_plugin.js?ver=' . WIKINDX_PUBLIC_VERSION;
        $footnotePluginPath = $externalPluginPath . 'wikindxWPFootnote/editor_plugin.js?ver=' . WIKINDX_PUBLIC_VERSION;
        $cssPath = $this->cssPath;
        $cssPopupPath = $this->cssPopupPath;
        $tinymcePath = $this->getIncludeTinyMceLib();

        // If the pspell or enchant extension are not available, disable the spell checker
        // Pspell is not installable on Windows (no recent binaries available), so it is always disable on this OS
        $EnableSpellChecker = extension_loaded('enchant') && extension_loaded('pspell') && defined("PSPELL_FAST") && (\UTILS\OSName() != "windows");

        $plugins_list = implode(",", [
            "wikindxSpecialChars",
            "-wikindxWPFootnote",
            "-wikindxWPSave",
            "-wikindxWPExport",
            "wikindxCite",
            "-wikindxWPStats",
            "wikindxLink",
            "lists",
            $EnableSpellChecker ? "spellchecker" : "",
            "wikindxTable",
            "table",
            "wikindxImage",
            "insertdatetime",
            "searchreplace",
            "print",
            "wikindxContextMenu",
            "paste",
            "fullscreen",
        ]);

        $theme_advanced_buttons1_List = implode(",", [
            "fullscreen",
            "wikindxWPSave",
            "wikindxWPExport",
            "newdocument",
            "|",
            "undo",
            "redo",
            "|",
            "cut",
            "copy",
            "paste",
            "pastetext",
            "pasteword",
            "|",
            "bold",
            "italic",
            "underline",
            "|",
            "justifyleft",
            "justifycenter",
            "justifyright",
            "justifyfull",
            "|",
            "outdent",
            "indent",
            "|",
            "bullist",
            "numlist",
            "|",
            "fontselect",
            "fontsizeselect",
            "|",
            "forecolor",
            "|",
            "sub",
            "sup",
        ]);

        $theme_advanced_buttons2_List = implode(",", [
            "removeformat",
            "code",
            "|",
            "wikindxLink",
            "unlink",
            "wikindxImage",
            "wikindxSpecialChars",
            "hr",
            "|",
            "insertdate",
            "inserttime",
            "|",
            "wikindxCite",
            "wikindxWPFootnote",
            "|",
            "wikindxTable",
            "delete_table",
            "delete_col",
            "delete_row",
            "col_before",
            "col_after",
            "row_before",
            "row_after",
            "|",
            "cleanup",
            "|",
            "search",
            "replace",
            $EnableSpellChecker ? "|" : "",
            $EnableSpellChecker ? "spellchecker" : "",
            "|",
            "print",
            "wikindxWPStats",
        ]);

        return <<<END
$tinymcePath
<script>
var viewportwidth;
var viewportheight;
var paperStatusMessage; // set externally in wordprocessor/index.php
var iframeMessageSet = false;

screenWidthHeight();
//document.write('<p>Your viewport width is '+viewportwidth+'x'+viewportheight+'</p>');

tinymce.PluginManager.load('wikindxWPExport', '$exportPluginPath');
tinymce.PluginManager.load('wikindxWPSave', '$savePluginPath');
tinymce.PluginManager.load('wikindxWPStats', '$statsPluginPath');
tinymce.PluginManager.load('wikindxWPFootnote', '$footnotePluginPath');

tinyMCE.init({
        // General options
        mode : "textareas",
		editor_deselector : "mceNoEditor",
        theme : "advanced",
        element_format : "html",
// Keep as UTF8
        entity_encoding : "raw",
        plugins : "$plugins_list",

        // Theme options
        theme_advanced_buttons1 : "$theme_advanced_buttons1_List",
        theme_advanced_buttons2 : "$theme_advanced_buttons2_List",
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,
        theme_advanced_statusbar_location : "none",
        height : viewportheight/1.4,
		width : "100%",

        // Skin options
        skin : "o2k7",
        skin_variant : "silver",

        // Example content CSS (should be your site CSS)
        content_css : "$cssPath",
// This is removed for the word processor tiny_mce instance because we want to use the original searchreplace CSS, for example.
//        popup_css : "$cssPopupPath",

		onchange_callback     : "resetMessageCell",
		handle_event_callback : "resetMessageCell"
});

/**
* get browser screen width and height
*/
function screenWidthHeight()
{
	// the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight

	if (typeof window.innerWidth != 'undefined')
	{
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
	}

	// IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)

	else if (typeof document.documentElement != 'undefined'
		 && typeof document.documentElement.clientWidth !=
		 'undefined' && document.documentElement.clientWidth != 0)
	{
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
	}

	 // older versions of IE

	else
	{
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
}

// When paper has been saved
function paperSaved(savedMessage, notSavedMessage, title)
{
	iframeMessageSet = false;
	paperStatusMessage = savedMessage;
	resetMessageCell();
	paperStatusMessage = notSavedMessage;
	var messageCell = document.getElementById("wpTitle");
	messageCell.innerHTML = title;
}

// When paper has been exported
function paperExported(savedMessage, notSavedMessage, title)
{
	iframeMessageSet = false;
	paperStatusMessage = savedMessage;
	resetMessageCell();
	paperStatusMessage = notSavedMessage;
	var messageCell = document.getElementById("wpTitle");
	messageCell.innerHTML = title;
	var messageCell2 = document.getElementById("wpExportFile");
//	if(title)
//		messageCell2.innerHTML = 'exportFile Link will go here!';
	if(!title)
		messageCell2.innerHTML = '';
}

// Reset message field (paper status) in parent window
function resetMessageCell()
{
//	if(!iframeMessageSet)
//	{
		var messageCell = document.getElementById("wpStatus");
		messageCell.innerHTML = paperStatusMessage;
// clear any export file
		var messageCell2 = document.getElementById("wpExportFile");
		messageCell2.innerHTML = ' ';
//		iframeMessageSet = true;
//	}
}
</script>
END;
    }
}
