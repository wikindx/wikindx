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
 * EXPORTBIBTEX
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "EXPORTER.php"]));

/**
 * Export BibTeX
 *
 * @package wikindx\core\importexport
 */
class EXPORTBIBTEX extends EXPORTER
{
    /** object */
    protected $db;
    /** object */
    protected $map;

    /**
     * Extends EXPORTER
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->map = FACTORY_BIBTEXMAP::getInstance();
    }

    /**
     * display bibtex data for one resource (from resource lists)
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
        $authors = $entry = FALSE;
        $type = $this->map->types[$row['resourceType']];

        $this->getData($row);

        if (!empty($this->entry))
        {
            $entry = $this->convertEntry();
        }
        if (!empty($this->authors))
        {
            $authors = $this->convertEntryAuthors();
        }

        $ret = '@' . mb_strtoupper($type) . "{" . $row['resourceBibtexKey'] . "\n";
        $ret .= $authors;
        $ret .= $entry;
        $ret .= "\n}\n";

        return $ret;
    }

    /**
     * Convert raw array of data to bibtex format
     *
     * @return string
     */
    protected function convertEntry()
    {
        $array = [];
        array_map([$this, "uEncode"], $this->entry);
        foreach ($this->entry as $key => $value)
        {
            $array[] = "$key = $value";
        }

        return implode(LF, $array);
    }
    /**
     * call back for convertEntry()
     *
     * @param string $element
     *
     * @return string
     */
    protected function uEncode($element)
    {
        return $element;
    }
    /**
     * create author string
     *
     * @return string
     */
    private function convertEntryAuthors()
    {
        return 'author = ' . implode(" and ", $this->authors) . "\n";
    }
}
