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
 * Front page of the system.
 *
 * @package wikindx\core\libs\FRONT
 */
class FRONT
{
    /** object */
    private $db;
    /** object */
    private $vars;
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

    /**
     * FRONT
     *
     * @param string $message
     */
    public function __construct($message = FALSE)
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        if (array_key_exists('message', $this->vars) && $this->vars['message']) {
        	$this->externalMessage = $this->vars['message'];
        }
        else {
	        $this->externalMessage = $message;
        }
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->listCommon = FACTORY_LISTCOMMON::getInstance();
        $this->listCommon->navigate = 'front';
        GLOBALS::setTplVar('heading', ''); // blank
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "modules", "help", "HELPMESSAGES.php"]));
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('front'));
        $this->init();
		$this->session->setVar("bookmark_DisplayAdd", FALSE);
    }
    /**
     * Display front page information.  If $noMenu, display WIKINDX submenu links
     */
    private function init()
    {
        $this->session->delVar("search_Highlight");
        $this->session->delVar("list_AllIds");
        
        $this->db->formatConditions(['configName' => 'configDescription_' . \LOCALES\determine_locale()]);
        $input = $this->db->fetchOne($this->db->select('config', 'configText'));
        
        $pString = WIKINDX_DESCRIPTION;
        $pString = $input ? $input : WIKINDX_DESCRIPTION;
        $pString = \HTML\nlToHtml($pString);

        // Do we want the quick search form to be displayed?
        if (mb_substr_count($pString, '$QUICKSEARCH$')) {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "modules", "list", "QUICKSEARCH.php"]));
            $qs = new QUICKSEARCH();
            $replace = $qs->init(FALSE, FALSE, TRUE);
            $pString = str_replace('$QUICKSEARCH$', $replace, $pString);
        }
        if ($lastChanges = WIKINDX_LAST_CHANGES) {
            if ($this->getChanges($lastChanges)) {
            	if (GLOBALS::getUserVar('HomeBib') && GLOBALS::getUserVar('BrowseBibliography')) {
            		$this->db->formatConditions(['userbibliographyId' => GLOBALS::getUserVar('BrowseBibliography')]);
            		$bib = $this->db->queryFetchFirstField($this->db->selectNoExecute('user_bibliography', ['userbibliographyTitle']));
            	} else {
            		$bib = $this->messages->text("user", "masterBib");
            	}
                $pString .= \HTML\p(\HTML\h($this->messages->text("resources", "lastChanges") . "&nbsp;($bib)", FALSE, 4));
            }
        }
        $pString .= $this->externalMessage;
        GLOBALS::addTplVar('content', $pString);
        GLOBALS::setTplVar('contactEmail', WIKINDX_CONTACT_EMAIL);
    }
    /**
     * Get recently added/edited resources
     *
     * @param int $limit
     *
     * @return false|string
     */
    private function getChanges($limit)
    {
        // If no resources, return FALSE
        if ($this->db->tableIsEmpty('resource')) {
            return FALSE;
        }
        $this->db->ascDesc = $this->db->desc; // descending order
        if (WIKINDX_LAST_CHANGES_TYPE == 'days') { // Display from last $limit days
            if (($limitResources = WIKINDX_LAST_CHANGES_DAY_LIMIT) < 0) {
                $limitResources = FALSE;
            }
            $sql = $this->stmt->frontSetDays($limit, $limitResources);
        } else { // Display set number
            $sql = $this->stmt->frontSetNumber($limit);
        }

        return $this->listCommon->display($sql, 'front');
    }
}
