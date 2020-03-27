<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * BibTeX MONTH import class
 *
 * BibTeX month field can come in as:
 * jan
 * "8~" # jan
 * jan#"~8"
 *
 * where # is concatenation and '~' can be any non-numeric character. The number must be extracted for use in the WIKINDX 'day' field.
 *
 * Entries of type jun # "-" # aug are reduced to just the first month.
 *
 * @package wikindx\core\importexport
 */
class BIBTEXMONTHPARSE
{
    /** string */
    private $startDay;
    /** string */
    private $endDay;
    
    /**
     * Start the process
     *
     * @param string $monthField
     *
     * @return array (startMonth, startDay, endMonth, endDay)
     */
    public function init($monthField)
    {
        // need to use English constants for BibTeX
        $constants = FACTORY_CONSTANTS::getFreshInstance(TRUE);
        $startMonth = $this->startDay = $endMonth = $this->endDay = FALSE;
        $date = UTF8::mb_explode(' ', $monthField); // ' # ' has been replaced by ' ' in PARSEBIBTEX.php
        foreach ($date as $field)
        {
            $field = ucfirst(mb_strtolower(trim($field)));
            if ($month = array_search($field, $constants->monthToLongName()))
            {
                if (!$startMonth)
                {
                    $startMonth = $month;
                }
                else
                {
                    $endMonth = $month;
                }

                continue;
            }
            elseif ($month = array_search($field, $constants->monthToShortName()))
            {
                if (!$startMonth)
                {
                    $startMonth = $month;
                }
                else
                {
                    $endMonth = $month;
                }

                continue;
            }
            $this->parseDay($field);
        }
        if ($this->endDay && !$endMonth)
        {
            $endMonth = $startMonth;
        }

        return [$startMonth, $this->startDay, $endMonth, $this->endDay];
    }
    /**
     * extract day of month from field
     *
     * @param string $dayField
     */
    private function parseDay($dayField)
    {
        preg_match("/([0-9]+).*([0-9]+)|([0-9]+)/u", $dayField, $array);
        if (array_key_exists(3, $array))
        {
            if (!$this->startDay)
            {
                $this->startDay = $array[3];
            }
            elseif (!$this->endDay)
            {
                $this->endDay = $array[3];
            }
        }
        else
        {
            if (array_key_exists(1, $array))
            {
                $this->startDay = $array[1];
            }
            if (array_key_exists(2, $array))
            {
                $this->endDay = $array[2];
            }
        }
    }
}
