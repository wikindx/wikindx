<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * Parse the bibliographic style's XML
 *
 * Conversion to use with PHP's simpleXML by Ritesh Agrawal and Mark Grimshaw-Aagaard 2007/2008
 *
 * @package wikindx\core\bibcitation
 */
class PARSEXML
{
    /** array */
    public $info = [];
    /** array */
    public $citation = [];
    /** array */
    public $footnote = [];
    /** array */
    public $common = [];
    /** array */
    public $types = [];
    
    /**
     * Read the chosen bibliographic style and create arrays based on resource type.
     *
     * @param string $output 'html', plain', 'rtf'. Default is 'html'
     * @param bool $export The requested bibliographic output style.
     *
     * @return bool
     */
    public function loadStyle($output, $export)
    {
        $setupStyle = $this->getStyle($output, $export);
        if (!$this->loadCache($setupStyle)) {
            $this->extractEntries(WIKINDX_DIR_COMPONENT_STYLES . DIRECTORY_SEPARATOR . $setupStyle . DIRECTORY_SEPARATOR . $setupStyle . ".xml");
            $this->createCache($setupStyle);
        }

        return TRUE;
    }
    /**
     * Extract entries from file
     *
     * @param string $file - Location of StyleFile
     */
    public function extractEntries($file)
    {
        $xmlString = simplexml_load_file($file);
        $this->info = $this->XMLToArray($xmlString->info);
        $this->getStyleTypes($xmlString);
        $this->getFootnotes($xmlString);
        $this->common = $this->XMLToArray($xmlString->bibliography->common);
        $this->citation = $this->XMLToArray($xmlString->citation);
        //		$this->footnote = $this->XMLToArray($xmlString->footnote);
        unset($xmlString);
    }
    /**
     * Load style cache file if available
     *
     * @param string $style A style name
     *
     * @return bool
     */
    private function loadCache($style)
    {
        $styleFilePath = WIKINDX_DIR_COMPONENT_STYLES . DIRECTORY_SEPARATOR . $style . DIRECTORY_SEPARATOR . $style . ".xml";
        $styleCacheFilePath = WIKINDX_DIR_CACHE_STYLES . DIRECTORY_SEPARATOR . $style;

        // If the cache file is missing, abort loading
        if (!file_exists($styleCacheFilePath)) {
            return FALSE;
        }
        // If the cache file is expired, delete it and abort loading
        if (filemtime($styleFilePath) >= filemtime($styleCacheFilePath)) {
            unlink($styleCacheFilePath);

            return FALSE;
        }
        // Load cache file, if readable
        if (FALSE !== ($fh = fopen($styleCacheFilePath, "r"))) {
            $this->info = unserialize(fgets($fh));
            $this->citation = unserialize(fgets($fh));
            $this->footnote = unserialize(fgets($fh));
            $this->common = unserialize(fgets($fh));
            $this->types = unserialize(fgets($fh));

            fclose($fh);

            return TRUE;
        }

        // Fallback case: Cache not loaded!
        return FALSE;
    }
    /**
     * Create style cache
     *
     * @param string $style A style name
     */
    private function createCache($style)
    {
        if (FALSE !== ($fh = fopen(WIKINDX_DIR_CACHE_STYLES . DIRECTORY_SEPARATOR . $style, "w"))) {
            // Serialize each array and write as one line to cache file
            fwrite($fh, serialize($this->info) . "\n");
            fwrite($fh, serialize($this->citation) . "\n");
            fwrite($fh, serialize($this->footnote) . "\n");
            fwrite($fh, serialize($this->common) . "\n");
            fwrite($fh, serialize($this->types) . "\n");

            fclose($fh);
        }

        // Fallback case: do nothing -- i.e. continue to read directly from XML files
    }
    /**
     * Get the bibliographic style file
     *
     * @param string $output 'html', plain', 'rtf'. Default is 'html'
     * @param bool $export The requested bibliographic output style.
     */
    private function getStyle($output, $export)
    {
        $session = FACTORY_SESSION::getInstance();
        $style = NULL;
        if ($output == 'rtf') {
            $style = $session->getVar($export ? "exportPaper_Style" : "wp_ExportStyle", NULL);
        }
        if ($style == NULL) {
            $style = GLOBALS::getUserVar("Style", WIKINDX_STYLE_DEFAULT);
        }
        $style = array_key_exists($style, \LOADSTYLE\loadDir()) ? $style : WIKINDX_STYLE_DEFAULT;

        return strtolower($style);
    }
    /**
     * Convert XML to array
     *
     * code borrowed from http://php.net
     *
     * @param string $xml
     *
     * @return mixed
     */
    private function XMLToArray($xml)
    {
        if ($xml instanceof SimpleXMLElement) {
            $children = $xml->children();
            $return = NULL;
        }
        foreach ($children as $element => $value) {
            if ($value instanceof SimpleXMLElement) {
                $values = (array)$value->children();
                if (count($values) > 0) {
                    $return[$element] = $this->XMLToArray($value);
                } else {
                    if (!isset($return[$element])) {
                        $return[$element] = (string)$value;
                    } else {
                        if (!is_array($return[$element])) {
                            $return[$element] = [$return[$element], (string)$value];
                        } else {
                            $return[$element][] = (string)$value;
                        }
                    }
                }
            }
        }
        if (is_array($return)) {
            return $return;
        } else {
            return FALSE;
        }
    }
    /**
     * Cycle through XML
     *
     * @param string $xmlString
     */
    private function getFootnotes($xmlString)
    {
        foreach ($xmlString->footnote->resource as $value) {
            $name = NULL;
            foreach ($value->attributes() as $id => $v) {
                if ($id == "name") {
                    $name = trim($v);
                }
            }
            $this->footnote[$name] = $this->XMLToArray($value);
        }
    }
    /**
     * Cycle through XML
     *
     * @param string $xmlString
     */
    private function getStyleTypes($xmlString)
    {
        foreach ($xmlString->bibliography->resource as $value) {
            $name = NULL;
            $creatorRewriteArray = [];
            foreach ($value->attributes() as $id => $v) {
                if ($id == "name") {
                    $name = trim($v);
                } else {
                    $creatorRewriteArray[$id] = (string)$v;
                }
            }
            $this->types[$name] = $this->XMLToArray($value) + $creatorRewriteArray;
            if (isset($value->fallbackstyle)) {
                $this->types['fallback'][$name] = trim((string)($value->fallbackstyle));
            }
        }
    }
}
