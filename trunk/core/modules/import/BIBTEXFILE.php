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
 *	BIBTEXFILE -- Import a bibtex file
 */
class BIBTEXFILE
{
    private $session;
    private $import;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->session = FACTORY_SESSION::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "IMPORTCOMMON.php"]));
        $this->import = new IMPORTCOMMON();
        $this->import->importType = 'bibtex';
    }
    /**
     * check we are allowed to delete and load appropriate method
     *
     * @param mixed $message
     */
    public function init($message = FALSE)
    {
    	if (!WIKINDX_IMPORT_BIB || !$this->session->getVar("setup_Write")) { 
    		if (!$this->session->getVar("setup_Superadmin")) {
				$auth = FACTORY_AUTHORIZE::getInstance();
				$auth->initLogon();
				FACTORY_CLOSE::getInstance();
			}
        }
        $this->import->display($message);
    }
}
