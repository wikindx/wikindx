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
 * GATEKEEP
 *
 * Test authority to do write operations.
 *
 * @package wikindx\core\miscellaneous
 */
class GATEKEEP
{
    /** boolean */
    public $requireSuper = FALSE;
    /** object */
    private $session;

    /**
     * GATEKEEP class
     */
    public function __construct()
    {
        $this->session = FACTORY_SESSION::getInstance();
    }
    /**
     * Admins can do everything
     *
     * Either return TRUE or stop the execution and display the login prompt
     *
     * @param bool $globalEdit Default is FALSE
     * @param bool $originatorEditOnly Default is FALSE
     *
     * @return bool
     */
    public function init($globalEdit = FALSE, $originatorEditOnly = FALSE)
    {
        if ($this->session->getVar("setup_Superadmin")) {
            return TRUE;
        } elseif ($this->requireSuper) {
            $authorize = FACTORY_AUTHORIZE::getInstance();
            $authorize->initLogon();
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        if ($this->session->getVar("setup_Write")) {
            if ($globalEdit && defined('WIKINDX_GLOBAL_EDIT') && !WIKINDX_GLOBAL_EDIT) {
                $authorize = FACTORY_AUTHORIZE::getInstance();
                $authorize->initLogon();
                FACTORY_CLOSENOMENU::getInstance(); // die
            }
            if ($originatorEditOnly && defined('WIKINDX_ORIGINATOR_EDIT_ONLY') && !WIKINDX_ORIGINATOR_EDIT_ONLY) {
                $authorize = FACTORY_AUTHORIZE::getInstance();
                $authorize->initLogon();
                FACTORY_CLOSENOMENU::getInstance(); // die
            }

            return TRUE;
        }
        // Failure to authorize so provide logon prompt
        $authorize = FACTORY_AUTHORIZE::getInstance();
        $authorize->initLogon();
        FACTORY_CLOSENOMENU::getInstance(); // die
    }
}
