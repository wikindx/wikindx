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
 * Load image icons used throughout WIKINDX
 *
 * @package wikindx\core\libs\LOADICONS
 */
class LOADICONS
{
    /** array */
    private $icons;
    /** string */
    private $templateDir;
    /** boolean */
    private $setupDone = FALSE;

    /**
     * LOADICONS
     *
     * Always call $this->setupIcons($singleiconbasename) after initialization
     *
     * @param string $singleiconbasename Default is "".
     */
    public function __construct($singleiconbasename = "")
    {
        $session = FACTORY_SESSION::getInstance();
        if (!$this->templateDir = GLOBALS::getUserVar('Template'))
        {
            $this->templateDir = WIKINDX_TEMPLATE_DEFAULT;
        }
        
        $this->setupIcons($singleiconbasename);
    }
    /**
     * Set up icons for display.
     *
     * If $singleiconbasename is given run only that icon setup
     * if the whole lsetup have not been yet. When the whole setup
     * is already done, don't do it twice.
     *
     * @param string $singleiconbasename Default is "".
     */
    public function setupIcons($singleiconbasename = "")
    {
        if ($this->setupDone)
        {
            return;
        }
        
        if ($singleiconbasename != "")
        {
            $basenames = [$singleiconbasename => "misc"];
        }
        else
        {
            $basenames = [
                "add" => "misc",
                "basketAdd" => "resources",
                "basketRemove" => "resources",
                "putInQuarantine" => "resources",
                "removeFromQuarantine" => "resources",
                "bibtex" => "misc",
                "cite" => "cite",
                "delete" => "misc",
                "edit" => "misc",
                "file" => "misc",
                "help" => "misc",
                "next" => "resources",
                "previous" => "resources",
                "quarantine" => "misc",
                "remove" => "misc",
                "toBottom" => "misc",
                "toLeft" => "misc",
                "toRight" => "misc",
                "toTop" => "misc",
                "view" => "misc",
                "viewAttach" => "misc",
                "viewmeta" => "misc",
                "viewmetaAttach" => "misc",
                "return" => "submit",
            ];
            $this->setupDone = TRUE;
        }

        $messages = FACTORY_MESSAGES::getInstance();
        foreach ($basenames as $basename => $msgkey)
        {
            $this->storeIconInfo($basename, $messages->text($msgkey, $basename));
        }
    }
    /**
     * Reset the icon initialisation routines
     */
    public function resetSetup()
    {
        $this->setupDone = FALSE;
    }
    /**
     * Get the HTML IMG tag of a standard icon from its basename
     *
     * @param string $basename
     */
    public function getHTML($basename)
    {
        return $this->icons[$basename]["html"];
    }
    /**
     * Get the HTML IMG tag of a standard icon from its basename
     *
     * @param string $basename
     */
    public function getClass($basename)
    {
        return $this->icons[$basename]["class"];
    }

    /**
     * Return the HTML code of an image for the file type of the file name in argument
     *
     * @see getIconRealFileName()
     *
     * @param string $file Basename of a file icon
     *
     * @return string HTML link to an image
     */
    public function getIconForAFileExtension($file)
    {
        // Extension of a MIME/type
        if (array_key_exists('extension', pathinfo(strtolower($file))))
        {
            $basename = "file_extension_" . pathinfo(strtolower($file))['extension'];
        }
        else
        {
            $basename = "file";
        }
        $iconfb = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES, WIKINDX_TEMPLATE_DEFAULT, 'icons', "file.png"]);
        $iconfburl = implode("/", [WIKINDX_URL_BASE, WIKINDX_URL_COMPONENT_TEMPLATES, WIKINDX_TEMPLATE_DEFAULT, 'icons', "file.png"]);
        $icon = $this->getIconRealFileName($basename, $iconfb, $iconfburl);
        
        // Disable a useless warning if the default file is missing
        $size = @getimagesize($icon["path"]);
        if ($size === FALSE)
        {
            $size[0] = "16";
            $size[1] = "16";
        }

        return \HTML\img($icon["url"], $size[0], $size[1], $file);
    }
    /**
     * Store in the class members a link for each standard icon
     *
     * @see getIconRealFileName()
     *
     * @param string $basename
     * @param mixed $title
     */
    private function storeIconInfo($basename, $title)
    {
        $link = $basename . 'Link';

        $icon = $this->getIconRealFileName($basename, "");

        if ($icon != "")
        {
            // get image size data
            $size = @getimagesize($icon["path"]);
            if ($size === FALSE)
            {
                $size[0] = "16";
                $size[1] = "16";
            }

            $this->icons[$basename]["html"] = \HTML\img($icon["url"], $size[0], $size[1], $title);
            $this->icons[$basename]["class"] = "imgLink";
        }
        else
        {
            $this->icons[$basename]["html"] = $title;
            $this->icons[$basename]["class"] = "link";
        }
    }
    
    /**
     * Return the real path of an icon form its basename
     *
     * Return the path of the first icon available in the user's preferred template
     * and in the default template if missing. The file is searched with the extensions
     * in the following order: gif, jpg, png, svg, webp.
     *
     * @param string $basename Basename of a file icon
     * @param string $filenameFallback A fallback path
     * @param mixed $urlFallback
     *
     * @return array Path to an icon file or $filenameFallback value
     */
    private function getIconRealFileName($basename, $filenameFallback = "", $urlFallback = "")
    {
        $filename = $filenameFallback;
        $url = $urlFallback;
        $tplSearch = [];
        // Don't test the default template twice
        if ($this->templateDir != WIKINDX_TEMPLATE_DEFAULT)
        {
            $tplSearch[] = $this->templateDir;
        }
        // Sometimes a directory may have been removed but that component is still in the session or database preferences
        // ... fall back to default and hope it's still there ;)
        $tplSearch[] = WIKINDX_TEMPLATE_DEFAULT;
        
        // Search the best icon
        foreach ($tplSearch as $tpl)
        {
            foreach (["gif", "jpeg", "jpg", "png", "svg", "webp"] as $ext)
            {
                $tmp = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES, $tpl, "icons", $basename . "." . $ext]);
                if (file_exists($tmp))
                {
                    if (is_file($tmp))
                    {
                        $filename = $tmp;
                        $url = implode("/", [WIKINDX_URL_BASE, WIKINDX_URL_COMPONENT_TEMPLATES, $tpl, "icons", $basename . "." . $ext]);

                        break;
                    }
                }
            }
        }

        return ["path" => $filename, "url" => $url];
    }
}
