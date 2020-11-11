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
 *	PASTEBIBTEX -- Users can copy 'n' paste bibtex entries into wikindx
 */
class PASTEBIBTEX
{
    private $gatekeep;
    private $import;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "IMPORTCOMMON.php"]));
        $this->import = new IMPORTCOMMON();
        $this->import->importType = 'pasteBibtex';
    }
    /**
     * check we are allowed to paste and then load display()
     *
     * @param mixed $message
     */
    public function init($message = FALSE)
    {
        $this->gatekeep->init();
        $this->import->display($message);
    }
}
