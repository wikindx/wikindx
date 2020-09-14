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
        // Allows e.g. plugins to set different modes:  word processor plugin has a 'wordProcessor' mode.
        $session->setVar("tinyMCE_mode", "standard");
        $this->cssPath = WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_TEMPLATES . '/' . GLOBALS::getUserVar('Template') . '/tinymce.css?ver=' . WIKINDX_PUBLIC_VERSION;
        $this->cssPopupPath = WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_TEMPLATES . '/' . GLOBALS::getUserVar('Template') . '/template.css?ver=' . WIKINDX_PUBLIC_VERSION;
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

        if (!empty($elements)) {
            $mode = 'mode : "exact"';
            $ids = 'elements : "' . implode(',', $elements) . '"';
        } else {
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

        if (!empty($elements)) {
            $mode = 'mode : "exact"';
            $ids = 'elements : "' . implode(',', $elements) . '"';
        } else {
            $mode = 'mode : "textareas"';
            $ids = 'elements : ""';
        }
        if ($configurePage) {
            $plugins = 'plugins : "paste,wikindxTable,wikindxLink,wikindxSpecialChars,wikindxImage,table,wikindxContextMenu"';
            $buttons2 = 'theme_advanced_buttons2 : "wikindxTable,delete_table,delete_col,delete_row,col_before,col_after,row_before,row_after,"';
        } else {
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
        foreach ($elements as $id) {
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
        paste_text_sticky_default: true,
        paste_text_sticky: true,

        // Theme options
        theme_advanced_buttons1 : "paste,|,bold,italic,underline,removeformat,code,|,sub,sup,|,wikindxSpecialChars,",
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
        popup_css : "$cssPopupPath"
});
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
    protected function getIncludeTinyMceLib()
    {
        if ($this->pathLoaded) {
            return '';
        } else {
            $this->pathLoaded = TRUE;
            return '<script src="' . WIKINDX_URL_BASE . '/core/tiny_mce/tiny_mce.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        }
    }
}
