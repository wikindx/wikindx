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
    /** string */
    private $browserTabID = FALSE;

    /**
     * FRONT
     *
     * @param string $message (Can come from BASKET::deleteConfirm())
     */
    public function __construct($message = FALSE)
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
        	$success = FACTORY_SUCCESS::getInstance();
            $this->externalMessage = $success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$errors = FACTORY_ERRORS::getInstance();
        	$split = explode('_', $this->vars['error']);
            $this->externalMessage = $errors->text($split[0], $split[1]);
        }
        else
        {
            $this->externalMessage = $message;
        }
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->stmt = FACTORY_SQLSTATEMENTS::getInstance();
        $this->listCommon = FACTORY_LISTCOMMON::getInstance();
        $this->listCommon->navigate = 'front';
        $this->browserTabID = GLOBALS::getBrowserTabID();
        GLOBALS::setTplVar('heading', ''); // blank
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('front'));
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
        if ($this->browserTabID) {
            GLOBALS::unsetTempStorage(['search_Highlight']);
        	\TEMPSTORAGE\deleteKeys($this->db, $this->browserTabID, ['search_Highlight', 'list_AllIds']);
        }
        
        $input = WIKINDX_DESCRIPTION;
        
        if ($this->db->tableExists("plugin_localedescription"))
        {
            $recordset = $this->db->select('plugin_localedescription', ['pluginlocaledescriptionLocale', 'pluginlocaledescriptionText']);
            if ($this->db->numRows($recordset) > 0)
            {
                $aLocales = \LOCALES\determine_locale_priority_stack();
                foreach ($aLocales as $loc => $null)
                {
                    // Find the first exact matching localized description
                    foreach ($recordset as $row)
                    {
                        if ($row['pluginlocaledescriptionLocale'] == $loc)
                        {
                            $input = $row['pluginlocaledescriptionText'];
                            break 2;
                        }
                    }
                    
                    // Find the first matching localized description for a family of language only
                    foreach ($recordset as $row)
                    {
                        if ($row['pluginlocaledescriptionLocale'] == \Locale::getPrimaryLanguage($loc))
                        {
                            $input = $row['pluginlocaledescriptionText'];
                            break 2;
                        }
                    }
                }
            }
        }
        
        $pString = $input;
        $pString = \HTML\nlToHtml($pString);

        // Do we want the quick search form to be displayed?
        if (mb_substr_count($pString, '$QUICKSEARCH$'))
        {
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "list", "QUICKSEARCH.php"]));
            $qs = new QUICKSEARCH();
            $replace = $qs->init(FALSE, FALSE, TRUE);
            $pString = str_replace('$QUICKSEARCH$', $replace, $pString);
        }
        if ($lastChanges = WIKINDX_LAST_CHANGES)
        {
            if ($this->getChanges($lastChanges))
            {
                if (GLOBALS::getUserVar('HomeBib') && GLOBALS::getUserVar('BrowseBibliography'))
                {
                    $this->db->formatConditions(['userbibliographyId' => GLOBALS::getUserVar('BrowseBibliography')]);
                    $bib = $this->db->queryFetchFirstField($this->db->selectNoExecute('user_bibliography', ['userbibliographyTitle']));
                }
                else
                {
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
        if ($this->db->tableIsEmpty('resource'))
        {
            return FALSE;
        }
        $this->db->ascDesc = $this->db->desc; // descending order
        if (WIKINDX_LAST_CHANGES_TYPE == 'days')
        { // Display from last $limit days
            if (($limitResources = WIKINDX_LAST_CHANGES_DAY_LIMIT) < 0)
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
