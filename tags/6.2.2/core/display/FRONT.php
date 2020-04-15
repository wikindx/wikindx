<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * Front page of the system.
 *
 * @package wikindx\core\display
 */
class FRONT
{
    /** object */
    private $db;
    /** object */
    private $session;
    /** object */
    private $messages;
    /** object */
    private $stmt;
    /** object */
    private $listCommon;
    /** string */
    private $externalMessage;
    /** object */
    private $configDbStructure;

    /**
     * FRONT
     *
     * @param string $message
     */
    public function __construct($message = FALSE)
    {
        $this->db = FACTORY_DB::getInstance();
        $this->externalMessage = $message;
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->listCommon = FACTORY_LISTCOMMON::getInstance();
        $this->configDbStructure = FACTORY_CONFIGDBSTRUCTURE::getInstance();
        $this->listCommon->navigate = 'front';
        GLOBALS::setTplVar('heading', ''); // blank
        include_once("core/modules/help/HELPMESSAGES.php");
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('front'));
        $this->init();
    }
    /**
     * Display front page information.  If $noMenu, display WIKINDX submenu links
     */
    private function init()
    {
        $this->session->delVar('search_Highlight');
        $this->session->delVar('list_AllIds');
        $co = FACTORY_CONFIGDBSTRUCTURE::getInstance();
        $row = $this->configDbStructure->getData(['configDescription', 'configContactEmail']);
        $field = 'configDescription_' . \LOCALES\determine_locale();
        $this->db->formatConditions(['configName' => $field]);
        $input = $this->db->fetchOne($this->db->select('config', 'configText'));
        if ($input)
        {
            $row[$field] = $input;
        }
        // Check if the row exists because at the installation, 'config' table is empty.
        if (!empty($row))
        {
            if (array_key_exists($field, $row) === FALSE)
            {
                $field = 'configDescription';
            }
            $pString = \HTML\dbToHtmlTidy($row[$field]);
        }
        else
        {
            $pString = '';
        }

        // Do we want the quick search form to be displayed?
        if (mb_substr_count($pString, '$QUICKSEARCH$'))
        {
            include_once('core/modules/list/QUICKSEARCH.php');
            $qs = new QUICKSEARCH();
            $replace = $qs->init(FALSE, FALSE, TRUE);
            $pString = str_replace('$QUICKSEARCH$', $replace, $pString);
        }
        if ($lastChanges = $this->session->getVar("setup_LastChanges"))
        {
            if ($this->getChanges($lastChanges))
            {
                $pString .= \HTML\p(\HTML\h($this->messages->text("resources", "lastChanges"), FALSE, 4));
            }
        }
        $pString .= $this->externalMessage;
        GLOBALS::addTplVar('content', $pString);
        if ($row['configContactEmail'])
        {
            $email = \HTML\dbToHtmlTidy($row['configContactEmail']);
            GLOBALS::setTplVar('contactEmail', $email);
        }
    }
    /**
     * Get recently added/edited resources
     *
     * @param int $limit
     *
     * @return string|FALSE
     */
    private function getChanges($limit)
    {
        // If no resources, return FALSE
        if ($this->db->tableIsEmpty('resource'))
        {
            return FALSE;
        }
        $this->db->ascDesc = $this->db->desc; // descending order
        if ($this->session->getVar("setup_LastChangesType") == 'days')
        { // Display from last $limit days
            if (($limitResources = $this->session->getVar("setup_LastChangesDayLimit")) < 0)
            {
                $limitResources = FALSE;
            }
            $sql = $this->stmt->frontSetDays($limit, $limitResources);
        }
        else
        { // Display set number
            $sql = $this->stmt->frontSetNumber($limit);
        }

        return $this->listCommon->display($sql, 'front');
    }
}