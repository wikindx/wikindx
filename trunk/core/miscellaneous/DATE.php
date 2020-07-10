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
 * DATE
 *
 * Common date and time functions
 *
 * @package wikindx\core\miscellaneous
 */
class DATE
{
    /**
     * DATE
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
    }
    /**
     * Take input from HTML FORM <input type=date> and split into separate fields.
     * Date comes in as 'yyyy-mm-dd' (but displayed on web form as 'dd / mm / yyyy').
     * All three fields must have a valid value else the form input is FALSE. This should be tested before calling this function.
     *
     * @param string $dateInput
     *
     * @return array array(year, month, day)
     */
    public function splitDate($dateInput)
    {
        $date = UTF8::mb_explode('-', $dateInput);

        return [$date[0], $date[1], $date[2]];
    }
}
