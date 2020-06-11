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
 * ENDNOTEDATEPARSE: Endnote date import class
 *
 * Endnote field can come in as:
 * September 11
 * September 11th
 * 11th September
 * 11 Sept.
 * 11th. Sept
 * etc. etc. etc. as there's no control over the format.  We'll try our best...
 */
class ENDNOTEDATEPARSE
{
    private $constants;

    /**
     * Constructor
     */
    public function __construct()
    {
        // need to use English constants
        $this->constants = FACTORY_CONSTANTS::getFreshInstance(TRUE);
    }
    /**
     * init()
     *
     * @param mixed $dateField
     *
     * @return array
     */
    public function init($dateField)
    {
        $day = $month = $year = FALSE;
        if (mb_strpos($dateField, '/') !== FALSE) {
            $date = UTF8::mb_explode("/", $dateField);
        } else {
            $date = UTF8::mb_explode(" ", $dateField);
        }
        if ((count($date) == 3) && ((mb_strlen($date[0]) == 4))) { // e.g. yyyy/mm/dd
            $year = $date[0];
            if ($year) {
                $month = $this->parseMonth($date[1]);
                if ($month) {
                    $day = $this->parseDay($date[2]);
                }
            }
        } elseif ((count($date) == 3) && ((mb_strlen($date[2]) == 4))) { // dd/mm/yyyy
            $year = $date[2];
            if ($year) {
                $month = $this->parseMonth($date[1]);
                if ($month) {
                    $day = $this->parseDay($date[0]);
                }
            }
        } elseif (count($date) == 2) { // February 31 or 31 February and no year
            if (!is_numeric($date[0])) {
                $month = $this->parseMonth($date[0]);
                $day = $this->parseDay($date[1]);
            } else {
                $month = $this->parseMonth($date[1]);
                $day = $this->parseDay($date[0]);
            }
        }
        if ($month > 12) { // i.e. format is actually yyyy/dd/mm or mm/dd/yyyy
            $temp = $day;
            $day = $month;
            $month = $temp;
        }

        return [$month, $day, $year];
    }
    /**
     * Extract day of month from field
     *
     * @param mixed $d
     *
     * @return mixed
     */
    private function parseDay($d)
    {
        if (preg_match("/([0-9]+)/u", $d, $matches)) {
            return (int)$matches[1];
        } else {
            return FALSE;
        }
    }
    /**
     * Extract month
     *
     * @param mixed $d
     *
     * @return mixed
     */
    private function parseMonth($d)
    {
        if (is_numeric($d)) {
            return (int)$d;
        }
        if (preg_match("/(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)/ui", $d, $matches)) {
            if ($month = array_search(ucfirst($matches[1]), $this->constants->monthToShortName())) {
                return $month;
            }
        }
        if (preg_match("/(january|february|march|april|may|june|july|august|september|october|november|december)/ui", $d, $matches)) {
            if ($month = array_search(ucfirst($matches[1]), $this->constants->monthToLongName())) {
                return $month;
            }
        }

        return FALSE;
    }
    /**
     * Extract year
     *
     * @param mixed $d
     *
     * @return mixed
     */
    private function parseYear($d)
    {
        if (preg_match("/(\\d{4,4})/u", $d, $matches)) {
            return $matches[1];
        } else {
            return FALSE;
        }
    }
}
