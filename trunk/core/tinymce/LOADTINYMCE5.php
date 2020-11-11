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
class LOADTINYMCE5
{
    /** string */
    protected $path = 'core/tinymce';
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
     * Load tinymce -- blank textarea configuration -- no tinyMCE tools
     *
     * @param array $elements Array of textinput ids to apply editor to. Default is array()
     * @param mixed $Label
     * @param mixed $Name
     * @param mixed $Value
     * @param mixed $width
     * @param mixed $height
     *
     * @return string
     */
    public function loadBlankTextarea($Label, $Name, $Value, $width = "200px", $height = "1.5em")
    {
        $cssPath = $this->cssPath;
        $cssPopupPath = $this->cssPopupPath;
        $tinymcePath = $this->getIncludeTinyMceLib();
        $textarea = \FORM\textareaInput($Label, $Name, \HTML\dbToTinyMCE($Value));
        //$textarea = "<div id=\"$Name\" class=\"tinymce_area_inline\" >$Value</div>";
        $textarea = "<div id=\"$Name\" class=\"tinymce_area_inline\" style=\"width:$width;width:height:$height\">$Value</div>";
        //$textarea = "<input type=\"text\" id=\"$Name\"value=\"$Value\">";
        
        $locale = $this->determine_locale();
        $language_url = $this->getLocalizationJS($locale);
        
        // Wrap only the config if the locale is available otherwise TinyMCE throws JS errors
        $locale = !empty($locale) ? "language: '" . $locale . "'," : "";
        $language_url = !empty($language_url) ? "language_url: '" . $language_url . "'," : "";
        $language_url = "";

        return <<<END
$tinymcePath
<script>
    tinymce.init({
        {$locale}
        {$language_url}
        inline: true,
        content_css : 'http://wikindx.test/components/templates/default/tinymce5.css',
        min_width: 1024, /* Make sure that the toolbar is long enough otherwise it takes the size of the text area !!! */
        plugins: 'code',
        toolbar: 'undo redo styleselect bold italic alignleft aligncenter alignright bullist numlist outdent indent code',
        selector: '#{$Name}'
    });
</script>
$textarea
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
        if ($this->pathLoaded)
        {
            return '';
        }
        else
        {
            $this->pathLoaded = TRUE;

            return '<script src="' . WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_VENDOR . '/tinymce/tinymce.min.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
        }
    }
    /**
     * Determines from the user's preferred locale the closest locale available in TinyMCE
     *
     * @return string Code of a locale (format: ll[_CC])
     */
    protected function determine_locale()
    {
        // TinyMCE translations are available online at:
        // https://www.tiny.cloud/get-tiny/language-packages/
        $locale = \LOCALES\determine_locale();
        $aScripts = \FILE\fileInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR, "tinymce", "langs"]));

        return in_array($locale . ".js", $aScripts) ? $locale : "";
    }
    /**
     * Include tiny_mce compressor
     *
     * @param bool $compressorLib Default is FALSE
     * @param mixed $locale
     *
     * @return string
     */
    protected function getLocalizationJS($locale)
    {
        if (empty($locale))
        {
            return '';
        }
        else
        {
            return WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_VENDOR . '/tinymce/languages/' . $locale . '.js';
        }
    }
}
