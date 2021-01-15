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
 *	Quarantine and approve resources
 */
class QUARANTINE
{
    private $db;
    private $vars;
    private $gatekeep;
    private $badInput;
    private $errors;
    private $navigate;
    private $success;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->navigate = FACTORY_NAVIGATE::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->gatekeep->requireSuper = TRUE;
    }
    /**
     * Approve resource
     */
    public function approve()
    {
        $this->gatekeep->init();
        if (!array_key_exists('resourceId', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $this->db->formatConditions(['resourcemiscId' => $this->vars['resourceId']]);
        $updateArray['resourcemiscQuarantine'] = 'N';
        $this->db->update('resource_misc', $updateArray);
        $this->navigate->resource($this->vars['resourceId'], "quarantineApprove");
    }
    /**
     * Quarantine resource
     */
    public function putInQuarantine()
    {
        $this->gatekeep->init();
        if (!array_key_exists('resourceId', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $this->db->formatConditions(['resourcemiscId' => $this->vars['resourceId']]);
        $updateArray['resourcemiscQuarantine'] = 'Y';
        $this->db->update('resource_misc', $updateArray);
        $this->navigate->resource($this->vars['resourceId'], "quarantined");
    }
    /**
     * Quickly check if there are any quarantined resources (used in MENU)
     *
     * @return string
     */
    public function checkQuarantine()
    {
        $this->db->formatConditions(['resourcemiscQuarantine' => 'Y']);
        $resultset = $this->db->select('resource_misc', 'resourcemiscId');
        $nbQuarantined = $this->db->numRows($resultset);

        return ($nbQuarantined > 0);
    }
}
