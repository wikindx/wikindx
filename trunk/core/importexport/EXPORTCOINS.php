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
 *	EXPORTCOINS
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "EXPORTER.php"]));

/**
 * Export COINS
 *
 * @package wikindx\core\importexport
 */
class EXPORTCOINS extends EXPORTER
{
    /**
     * Extends EXPORTER
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "COINSMAP.php"]));
        $this->map = new COINSMAP();
    }
    /**
     * Display coins data for one resource (from resource lists)
     *
     * @param array $row
     * @param array $creators
     *
     * @return string
     */
    public function export($row, $creators)
    {
        $this->creators = $creators;
        $this->entry = $this->authors = [];
        $genre = $authors = $entry = $url = $doi = $abstract = FALSE;
        $this->getData($row);
        $type = $this->map->types[$row['resourceType']];
        if ($this->map->genres[$row['resourceType']]) {
            $genre = "&amp;rft.genre=" . urlencode($this->map->genres[$row['resourceType']]);
        }
        if (!empty($this->entry)) {
            $entry = $this->convertEntry();
        }
        if (!empty($this->authors)) {
            $authors = $this->convertEntryAuthors();
        }
        if (array_key_exists('resourcetextUrls', $row) && $row['resourcetextUrls']) {
            $urls = unserialize(base64_decode($row['resourcetextUrls']));
            $url = '&amp;rft_id=' . urlencode(array_shift($urls));
        }
        if (array_key_exists('resourceDoi', $row) && $row['resourceDoi']) {
            $doi = '&amp;rft_id=info:doi/' . $this->uEncode($row['resourceDoi']);
        }
        if (array_key_exists('resourcetextAbstract', $row) && $row['resourcetextAbstract']) {
            $abstract = '&amp;rft_id=info:abstract/' . $this->uEncode($row['resourcetextAbstract']);
        }
        $sid = "&amp;rfr_sid=info:sid/" . WIKINDX_BASE_URL . $_SERVER['SCRIPT_NAME'];
        $return = "<span " . $this->map->coinsBase . "$type" . "$genre" .
            $entry . $authors . $url . $doi . $sid . $abstract . "\"></span>";

        return $return;
    }

    /**
     * Convert raw array of data to appropriate coins format
     *
     * @return string
     */
    protected function convertEntry()
    {
        $array = [];
        array_map([$this, "uEncode"], $this->entry);
        foreach ($this->entry as $key => $value) {
            $array[] = "rft." . $key . "=" . $value;
        }

        return "&amp;" . implode("&amp;", $array);
    }
    /**
     * Convert raw array of authors to appropriate coins format*
     *
     * @return string
     */
    protected function convertEntryAuthors()
    {
        $array = [];
        array_map([$this, "uEncode"], $this->authors);
        foreach ($this->authors as $value) {
            $array[] = "rft.au=" . $value;
        }

        return "&amp;" . implode("&amp;", $array);
    }
    /**
     * Callback for convertEntry()
     *
     * @param string $element
     *
     * @return string
     */
    protected function uEncode($element)
    {
        return urlencode($element);
    }
}
