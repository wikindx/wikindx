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
 * EXPORTER
 *
 * parent/base exporter class. The specific exporter classes should inherit.
 *
 * @package wikindx\core\importexport
 */
abstract class EXPORTER
{
    /** object */
    protected $db;
    /** object */
    protected $map;
    /** array */
    protected $entry;
    /** array */
    protected $authors;
    /** array */
    protected $creators;
    /**
     * This is the main public function for exporting
     *
     * @param array $row
     * @param array $creators
     */
    abstract public function export($row, $creators);
    /**
     * titleFormat - format and return the resource title from the supplied SQL $row
     *
     * @param array $row
     * @param bool $bibtex
     *
     * @return string
     */
    public function titleFormat($row, $bibtex = FALSE)
    {
        // For book_chapter, 'title' is bibtex 'chapter' and 'collectionTitle' is bibtex 'title'
        if ($bibtex && ($row['resourceType'] == 'book_chapter')) {
            return stripslashes($row['collectionTitle']);
        }
        $noSort = $row['resourceNoSort'] ? stripslashes($row['resourceNoSort']) . ' ' : FALSE;
        if ($row['resourceSubtitle']) {
            $string = $noSort . stripslashes($row['resourceTitle']) . ": " . stripslashes($row['resourceSubtitle']);
        } else {
            $string = $noSort . stripslashes($row['resourceTitle']);
        }
        if ($bibtex) {
            return $string;
        }
        // If !bibtex, remove any braces that have been inserted to maintain case of characters - only required for resource title
        return preg_replace("/[{|}]/u", "", $string);
    }
    /**
     * formatName - format one name depending on the export type
     *
     * @param array $creatorRow
     * @param string $exportType
     *
     * @return string
     */
    public function formatName($creatorRow, $exportType)
    {
        $surname = $firstname = $initials = '';
        // WIKINDX stores Jr., IV etc. at end of surname...
        if ($creatorRow['creatorSurname']) {
            if ($creatorRow['creatorPrefix']) {
                $surname = stripslashes($creatorRow['creatorPrefix']) . " " .
                stripslashes($creatorRow['creatorSurname']);
            } else {
                $surname = stripslashes($creatorRow['creatorSurname']);
            }
        }
        if ($creatorRow['creatorFirstname']) {
            $firstname = stripslashes($creatorRow['creatorFirstname']);
        }
        if ($creatorRow['creatorInitials']) {
            if ($exportType == 'endnote') {
                $initials = implode(' ', UTF8::mb_explode(' ', stripslashes($creatorRow['creatorInitials'])));
            } elseif ($exportType == 'ris') {
                $initials = implode('.', UTF8::mb_explode(' ', stripslashes($creatorRow['creatorInitials']))) . ".";
            } elseif ($exportType == 'bibtex') {
                $initials = implode('. ', UTF8::mb_explode(' ', stripslashes($creatorRow['creatorInitials']))) . ".";
            }
        }
        if ($exportType == 'ris') {
            if ($firstname && $initials) {
                return $surname . ',' . $firstname . ',' . $initials;
            } elseif ($firstname) {
                return $surname . ',' . $firstname;
            } elseif ($initials) {
                return $surname . ',' . $initials;
            }
        } elseif ($exportType == 'endnote') {
            if ($firstname && $initials) {
                return $surname . ',' . $firstname . ' ' . $initials;
            } elseif ($firstname) {
                return $surname . ',' . $firstname;
            } elseif ($initials) {
                return $surname . ',' . $initials;
            }
        } elseif ($exportType == 'bibtex') {
            if (preg_match("/(.*)(Sr\\.|jr\\.)/ui", $surname, $matches)) {
                $surname = trim($matches[1]) . ", " . trim($matches[2]);
            }
            if (preg_match("/(.*)\\s(I|II|III|IV|V|VI|VII|VIII|IX|X)$/u", $surname, $matches)) {
                $surname = trim($matches[1]) . ", " . trim($matches[2]);
            }
            if ($firstname && $initials) {
                return $surname . ", " . $firstname . ' ' . $initials;
            } elseif ($firstname) {
                return $surname . ", " . $firstname;
            } elseif ($initials) {
                return $surname . ", " . $initials;
            }
        }

        return $surname; // if all else fails
    }
    /**
     * Url encode
     *
     * @param string $element
     */
    abstract protected function uEncode($element);

    /**
     * Convert raw array of data to the appropriate format
     */
    abstract protected function convertEntry();

    /**
     * get and format data
     *
     * @param array $row
     */
    protected function getData($row)
    {
        $this->entry[$this->map->{$row['resourceType']}['resourceTitle']] = $this->uEncode($this->titleFormat($row));
        $this->nameFormat();
        if ((!array_key_exists('resourceyearYear1', $row)
            || !$row['resourceyearYear1']) && array_key_exists('resourceyearYear2', $row)
            && $row['resourceyearYear2']) {
            $row['resourceyearYear1'] = $row['resourceyearYear2'];
        }
        if (array_key_exists('resourceyearYear1', $row) && $row['resourceyearYear1']) {
            $this->dateFormat($row);
        }
        // Add everything else
        foreach ($this->map->{$row['resourceType']} as $key => $value) {
            if (!array_key_exists($key, $row) || !$row[$key]) {
                continue;
            }
            if ($key == 'resourceTitle') {
                continue;
            }
            $this->entry[$value] = $this->uEncode(stripslashes($row[$key]));
        }
    }
    /**
     * format names
     */
    protected function nameFormat()
    {
        foreach ($this->creators as $array) {
            foreach ($array as $field => $value) {
                $creatorRow['creator' . ucfirst($field)] = $value;
            }
            $name = $this->formatName($creatorRow, 'bibtex'); // use bibTeX format
            if ($name) {
                $this->authors[] = $name;
            }
        }
    }
    /**
     * Create date
     *
     * @param array $row
     */
    protected function dateFormat($row)
    {
        if ($row['resourceType'] == 'web_article') {
            $startDay = array_key_exists('resourcemiscField5', $row) ? $row['resourcemiscField5'] : FALSE;
            $startMonth = array_key_exists('resourcemiscField6', $row) ? $row['resourcemiscField6'] : FALSE;
        } else {
            $startDay = array_key_exists('resourcemiscField2', $row) ? $row['resourcemiscField2'] : FALSE;
            $startMonth = array_key_exists('resourcemiscField3', $row) ? $row['resourcemiscField3'] : FALSE;
        }
        $startDay = ($startDay == 0) ? FALSE : $startDay;
        $startMonth = ($startMonth == 0) ? FALSE : $startMonth;
        if (!$startMonth) {
            $date = $row['resourceyearYear1'];
        } elseif (!$startDay) {
            if ($startMonth < 10) {
                $startMonth = '0' . $startMonth;
            }
            $date = $row['resourceyearYear1'] . '-' . $startMonth;
        } else {
            if ($startMonth < 10) {
                $startMonth = '0' . $startMonth;
            }
            if ($startDay < 10) {
                $startDay = '0' . $startDay;
            }
            $date = $row['resourceyearYear1'] . '-' . $startMonth . '-' . $startDay;
        }
        $this->entry['date'] = $date;
    }
}
