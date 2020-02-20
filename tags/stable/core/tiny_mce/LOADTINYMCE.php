<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * LOADTINYMCE
 *
 * Interface for tiny_mce elements in various pages.
 *
 * @package wikindx\core\tiny_mce
 */
class LOADTINYMCE
{
    /** string */
    protected $path = 'core/tiny_mce';
    /** boolean */
    protected $pathLoaded = FALSE;
    /** string */
    protected $cssPath;
    /** string */
    protected $cssPopupPath;

    /**
     * LOADTINYMCE
     */
    public function __construct()
    {
        $session = FACTORY_SESSION::getInstance();
        $this->config = FACTORY_CONFIG::getInstance();
        // Allows e.g. plugins to set different modes:  word processor plugin has a 'wordProcessor' mode.
        $session->setVar('tinyMCE_mode', 'standard');
        $this->cssPath = $this->cssPopupPath = $this->config->WIKINDX_BASE_URL . '/' . str_replace("\\", "/", WIKINDX_DIR_COMPONENT_TEMPLATES) . '/' . GLOBALS::getUserVar('Template') . '/template.css';
    }
    /**
     * Load tiny_mce using compressor script
     *
     * @return string
     */
    public function loadCompressor()
    {
        $cssPath = $this->cssPath;
        $cssPopupPath = $this->cssPopupPath;
        $tinymcePath = $this->getIncludeTinyMceLib(TRUE);

        return <<<END
$tinymcePath
<script>
function setup() {
   tinyMCE_GZ.init({
      themes : "advanced",
      plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,wikindxContextMenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,wikindxSpecialChars",
      languages : "en",
      disk_cache : true
   }, function() {
      tinyMCE.init({
         mode : "textareas",
         theme : "advanced",
         element_format : "html",
         plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,wikindxContextMenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
         theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,removeformat,code,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
         theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
         theme_advanced_buttons3 : "tablecontrols,|,hr,visualaid,|,sub,sup,|,wikindxSpecialChars,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
         theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,wikindxCite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
         theme_advanced_toolbar_location : "top",
         theme_advanced_toolbar_align : "left",
         theme_advanced_statusbar_location : "bottom",
         theme_advanced_resizing : true,

         // Example content CSS (should be your site CSS)
         content_css : "$cssPath",
         popup_css : "$cssPopupPath"
      });
   });
}
</script>
END;
    }
    /**
     * Load tinymce -- full configuration
     *
     * @return string
     */
    public function loadFull()
    {
        $cssPath = $this->cssPath;
        $cssPopupPath = $this->cssPopupPath;
        $tinymcePath = $this->getIncludeTinyMceLib();

        return <<<END
$tinymcePath
<script>
tinyMCE.init({
        // General options
        mode : "textareas",
		editor_deselector : "mceNoEditor",
        theme : "advanced",
        element_format : "html",
// Keep as UTF8
        entity_encoding : "raw",
        plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,wikindxContextMenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,wikindxCite,wikindxSpecialChars",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,removeformat,code,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,visualaid,|,sub,sup,|,wikindxSpecialChars,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,wikindxCite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,
        theme_advanced_statusbar_location : "none",

        // Skin options
        skin : "o2k7",
        skin_variant : "silver",

        // Example content CSS (should be your site CSS)
        content_css : "$cssPath",
        popup_css : "$cssPopupPath",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js"
});
</script>
END;
    }
    /**
     * Load tinymce -- metadata textarea configuration
     *
     * @param array $elements Array of textinput ids to apply editor to. Default is array()
     *
     * @return string
     */
    public function loadMetadataTextarea($elements = [])
    {
        $cssPath = $this->cssPath;
        $cssPopupPath = $this->cssPopupPath;
        $tinymcePath = $this->getIncludeTinyMceLib();

        if (!empty($elements))
        {
            $mode = 'mode : "exact"';
            $ids = 'elements : "' . implode(',', $elements) . '"';
        }
        else
        {
            $mode = 'mode : "textareas"';
            $ids = 'elements : ""';
        }

        return <<<END
$tinymcePath
<script>
tinyMCE.init({
        // General options
		$mode,
		$ids,
        theme : "advanced",
        element_format : "html",
// Keep as UTF8
        entity_encoding : "raw",
        plugins : "paste, wikindxCite, wikindxImage, advlist, wikindxLink, wikindxContextMenu, directionality, nonbreaking, wikindxSpecialChars",
		editor_deselector : "mceNoEditor",
		nonbreaking_force_tab : true,
//		forced_root_block : false,
//		force_br_newlines : true,
//		force_p_newlines : false,




        // Theme options
        theme_advanced_buttons1 : "cut,copy,paste,pastetext,pasteword,|,bold,italic,underline,removeformat,code,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,",
        theme_advanced_buttons2 : "forecolor,|,bullist,numlist,|,wikindxLink,unlink,wikindxImage,|,sub,sup,|,wikindxSpecialChars,wikindxCite,nonbreaking,ltr,rtl,",
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,
        theme_advanced_statusbar_location : "none",

        // Skin options
        skin : "o2k7",
        skin_variant : "silver",

        // Example content CSS (should be your site CSS)
        content_css : "$cssPath",
        popup_css : "$cssPopupPath",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js"
});
</script>
END;
    }
    /**
     * Load tinymce -- metadata textarea configuration
     *
     * @param array $elements Array of textinput ids to apply editor to. Default is array()
     *
     * @return string
     */
    public function loadGlossaryTextarea()
    {
        $cssPath = $this->cssPath;
        $cssPopupPath = $this->cssPopupPath;
        $tinymcePath = $this->getIncludeTinyMceLib();

        $mode = 'mode : "textareas"';
        $ids = 'elements : ""';

        return <<<END
$tinymcePath
<script>
tinyMCE.init({
        // General options
		$mode,
		$ids,
        theme : "advanced",
        element_format : "html",
// Keep as UTF8
        entity_encoding : "raw",
        plugins : "paste, wikindxCite, advlist, wikindxLink, wikindxContextMenu, directionality, nonbreaking, wikindxSpecialChars",
		editor_deselector : "mceNoEditor",
		nonbreaking_force_tab : true,
//		forced_root_block : false,
//		force_br_newlines : true,
//		force_p_newlines : false,

        // Theme options
        theme_advanced_buttons1 : "cut,copy,paste,pastetext,pasteword,|,bold,italic,underline,removeformat,code,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,forecolor,|,bullist,numlist,|,wikindxLink,unlink,|,sub,sup,|,wikindxSpecialChars,wikindxCite,nonbreaking,ltr,rtl,",
        theme_advanced_buttons2 : "",
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,
        theme_advanced_statusbar_location : "none",

        // Skin options
        skin : "o2k7",
        skin_variant : "silver",

        // Example content CSS (should be your site CSS)
        content_css : "$cssPath",
        popup_css : "$cssPopupPath",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js"
});
tinyMCE.execCommand('mceAddControl', true, 'glossaryDiv');
</script>
END;
    }
    /**
     * Load tinymce -- minimal textarea configuration
     *
     * @param array $elements Array of textinput ids to apply editor to. Default is array()
     * @param bool $configurePage Default is FALSE. If TRUE, tiny_mce is being loaded for the Admin Configure page.
     *
     * @return string
     */
    public function loadMinimalTextarea($elements = [], $configurePage = FALSE)
    {
        $cssPath = $this->cssPath;
        $cssPopupPath = $this->cssPopupPath;
        $tinymcePath = $this->getIncludeTinyMceLib();

        if (!empty($elements))
        {
            $mode = 'mode : "exact"';
            $ids = 'elements : "' . implode(',', $elements) . '"';
        }
        else
        {
            $mode = 'mode : "textareas"';
            $ids = 'elements : ""';
        }
        if ($configurePage)
        {
            $plugins = 'plugins : "paste,wikindxTable,wikindxLink,wikindxSpecialChars,wikindxImage,table,wikindxContextMenu"';
            $buttons2 = 'theme_advanced_buttons2 : "wikindxTable,delete_table,delete_col,delete_row,col_before,col_after,row_before,row_after,"';
        }
        else
        {
            $plugins = 'plugins : "paste,wikindxSpecialChars"';
            $buttons2 = 'theme_advanced_buttons2 : ""';
        }

        return <<<END
$tinymcePath
<script>
tinyMCE.init({
        // General options
		$mode,
		$ids,
		editor_deselector : "mceNoEditor",
        theme : "advanced",
        element_format : "html",
// Keep as UTF8
        entity_encoding : "raw",
        $plugins,

        // Theme options
        theme_advanced_buttons1 : "paste,pastetext,pasteword,|,bold,italic,underline,removeformat,code,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,forecolor,|,bullist,numlist,|,wikindxLink,unlink,wikindxImage,|,sub,sup,|,wikindxSpecialChars,",
        $buttons2,
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,
        theme_advanced_statusbar_location : "none",

        // Skin options
        skin : "o2k7",
        skin_variant : "silver",

        // Example content CSS (should be your site CSS)
        content_css : "$cssPath",
        popup_css : "$cssPopupPath",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js"
});
</script>
END;
    }
    /**
     * Load tinymce -- basic textarea configuration
     *
     * @return string
     */
    public function loadBasicTextarea()
    {
        $cssPath = $this->cssPath;
        $cssPopupPath = $this->cssPopupPath;
        $tinymcePath = $this->getIncludeTinyMceLib();

        return <<<END
$tinymcePath
<script>
tinyMCE.init({
        // General options
        mode : "textareas",
        theme : "advanced",
        element_format : "html",
// Keep as UTF8
        entity_encoding : "raw",
		editor_deselector : "mceNoEditor",
		plugins : "paste,wikindxSpecialChars",


        // Theme options
        theme_advanced_buttons1 : "paste,pastetext,pasteword,|bold,italic,underline,removeformat,code,|,formatselect,fontselect,fontsizeselect,forecolor,|,sub,sup,|,wikindxSpecialChars,",
        theme_advanced_buttons2 : "",
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,
        theme_advanced_statusbar_location : "none",

        // Skin options
        skin : "o2k7",
        skin_variant : "silver",

        // Example content CSS (should be your site CSS)
        content_css : "$cssPath",
        popup_css : "$cssPopupPath",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js"
});
</script>
END;
    }
    /**
     * Load tinymce -- blank textarea configuration -- no tinyMCE tools
     *
     * @param array $elements Array of textinput ids to apply editor to. Default is array()
     *
     * @return string
     */
    public function loadBlankTextarea($elements = [])
    {
        $cssPath = $this->cssPath;
        $cssPopupPath = $this->cssPopupPath;
        $tinymcePath = $this->getIncludeTinyMceLib();

        if (!empty($elements))
        {
            $mode = 'mode : "exact"';
            $ids = 'elements : "' . implode(',', $elements) . '"';
        }
        else
        {
            $mode = 'mode : "textareas"';
            $ids = 'elements : ""';
        }

        return <<<END
$tinymcePath
<script>
tinyMCE.init({
        // General options
		$mode,
		$ids,
        theme : "advanced",
        element_format : "html",
// Keep as UTF8
        entity_encoding : "raw",
		editor_deselector : "mceNoEditor",


        // Theme options
        theme_advanced_buttons1 : "",
        theme_advanced_buttons2 : "",
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "false",
        theme_advanced_statusbar_location : "none",

        // Skin options
        skin : "o2k7",
        skin_variant : "silver",

        // Example content CSS (should be your site CSS)
        content_css : "$cssPath",
        popup_css : "$cssPopupPath"
});
</script>
END;
    }
    /**
     * Load tinymce -- basic textinput configuration
     *
     * @param array $elements Array of textinput ids to apply editor to. Default is array()
     * @param int $width
     *
     * @return string
     */
    public function loadBasicTextinput($elements, $width)
    {
        $cssPath = $this->cssPath;
        $cssPopupPath = $this->cssPopupPath;
        $tinymcePath = $this->getIncludeTinyMceLib();

        $ids = implode(',', $elements);
        foreach ($elements as $id)
        {
            $countIdsArray[] = "'$id'";
            $countSizesArray[] = '255';
        }
        $countIds = implode(',', $countIdsArray);
        $countSizes = implode(',', $countSizesArray);

        return <<<END
$tinymcePath
<script>
tinyMCE.init({
        // General options
        mode : "exact",
		elements:  "$ids",
		editor_deselector : "mceNoEditor",
        theme : "advanced",
        element_format : "html",
// Keep as UTF8
        entity_encoding : "raw",
        remove_linebreaks : true,
        forced_root_block:  false,
        plugins : "paste,wikindxSpecialChars",

        // Theme options
        theme_advanced_buttons1 : "paste,pastetext,pasteword,|,bold,italic,underline,removeformat,code,|,sub,sup,|,wikindxSpecialChars,",
        theme_advanced_buttons2 : "",
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,
        theme_advanced_statusbar_location : "none",
		width : "$width",
		height : "3em",
		nowrap : true,
        content_css : "$cssPath",
        popup_css : "$cssPopupPath",

		// Limit chars and provide alert if user goes over
		setup : function(ed) {
			tinyMCEArray = [[$countIds],[$countSizes]]; // Setup a matrix here.
			ed.onKeyPress.add(function(ed, e)  // disable use of RETURN key
			{
				if ((e.charCode || e.keyCode) == 13)
				{
					tinyMCE.activeEditor.selection.setContent("");
					return tinymce.dom.Event.cancel(e);
				}
          });
		},
		onchange_callback     : "tinyMceOnChange",
		handle_event_callback : "tinyMceEventHandler"
});

// For IE implementations that don't use indexOf()
if (!Array.prototype.indexOf)
{
  Array.prototype.indexOf = function(elt /*, from*/)
  {
	var len = this.length >>> 0;

	var from = Number(arguments[1]) || 0;
	from = (from < 0)
		 ? Math.ceil(from)
		 : Math.floor(from);
	if (from < 0)
	  from += len;

	for (; from < len; from++)
	{
	  if (from in this &&
		  this[from] === elt)
		return from;
	}
	return -1;
  };
}

var tinyMceBuffers    = new Object();
var tinyMceCharCounts = new Object();
var tinyMCEArray; 	// Will hold my array of settings.

// Event fired when something in TinyMCE changes such as when an undo
// level is added like after a copy/paste.
function tinyMceOnChange(inst){
	if(navigator.appName == 'Microsoft Internet Explorer')
	{
		var IEArray = tinyMCEArray[0];
		var index = IEArray.indexOf(inst.id);
		if (index != -1) { //IE does not like to have the array.indexOf being called directly.
			tinyMceCheckContentLength(inst.id, tinyMCEArray[1][index]);
		}
	}
	else
	{
		if (tinyMCEArray[0].indexOf(inst.id) != -1) {
			var index = tinyMCEArray[0].indexOf(inst.id);
			tinyMceCheckContentLength(inst.id, tinyMCEArray[1][index]);
		}
	}
}

// Event fired for any normal UI event such as a key or mouse press.
function tinyMceEventHandler(e){
    switch (e.type) {
        case 'keyup': tinyMceOnChange(tinyMCE.activeEditor); break;
    }
    return true;
}
// Strips all html tags from a given string, leaving only plain text
function stripHtmlTags(strContent) { return strContent.replace(/(<([^>]+)>)/ig, ""); }

function tinyMceCheckContentLength(strEditorId, intMaxLength) {
    var editorInstance   = tinyMCE.get(strEditorId);
    if (editorInstance   == null || editorInstance   == undefined) { alert('NO EDITOR'); }

    var contentContainer = editorInstance.getBody();
    if (contentContainer == null || contentContainer == undefined) { alert('NO CONTENT CONTAINER'); }
// March 2013 -- v4.2.x increases the size of the title and subtitle fields so this is no longer needed
return;
    var strContent       = contentContainer.innerHTML;
    var intContentLength = strContent.length;
    var intCharCount     = stripHtmlTags(strContent).length;

    if (intContentLength <= intMaxLength) {
        // The user has not passed the maximum amount of content in the editor...

        // Save away the current contents in case they have typed too much and we have to
        // revert back to this state.
        tinyMceBuffers   [strEditorId] = strContent;
        tinyMceCharCounts[strEditorId] = intCharCount;

    } else {
        // The user has put more than the maximum amount of content in the editor...
        // We must now revert back to the last good contents that we had for the editor,
        // or else whatever fits if we never had anything saved from there.
        var bm = editorInstance.selection.getBookmark(); // Stores a bookmark of the current selection
        editorInstance.setContent((tinyMceBuffers[strEditorId]) ? tinyMceBuffers[strEditorId] : strContent.substring(0, intMaxLength - 10));
        var intDelta = intCharCount - tinyMceCharCounts[strEditorId];
        if (bm['start'] && bm['start'] > intDelta) {
            bm['start'] -= intDelta;
            bm['end']   =  bm['start'];
        }
        editorInstance.selection.moveToBookmark(bm); // Restore the selection bookmark

        alert('You have exceeded the maximum size for this text, including formatting, and we have undone your last change.');
    }
}

function searchArray(array)
{
	if (!Array.prototype.indexOf)
	{
	  Array.prototype.indexOf = function(elt /*, from*/)
	  {
		var len = this.length >>> 0;

		var from = Number(arguments[1]) || 0;
		from = (from < 0)
			 ? Math.ceil(from)
			 : Math.floor(from);
		if (from < 0)
		  from += len;

		for (; from < len; from++)
		{
		  if (from in this &&
			  this[from] === elt)
			return from;
		}
		return -1;
	  };
	}
}
</script>
END;
    }
    /**
     * Include tiny_mce compressor
     *
     * @param bool $compressorLib Default is FALSE
     *
     * @return string
     */
    protected function getIncludeTinyMceLib($compressorLib = FALSE)
    {
        if ($this->pathLoaded)
        {
            $includeLib = '';
        }
        else
        {
            if (!$compressorLib)
            {
                $includeLib = '<script src="' . $this->config->WIKINDX_BASE_URL . '/core/tiny_mce/tiny_mce.js"></script>';
            }
            else
            {
                $includeLib = '<script src="' . $this->config->WIKINDX_BASE_URL . '/core/tiny_mce/tiny_mce_gzip.js"></script>';
            }
            $this->pathLoaded = TRUE;
        }

        return $includeLib;
    }
}
