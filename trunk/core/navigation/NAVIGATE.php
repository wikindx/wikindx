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
 * NAVIGATE
 *
 * Configure, create and print menus
 *
 * @package wikindx\core\navigation
 */
class NAVIGATE
{
    /** object */
    private $db;
    /** object */
    private $session;
    /** object */
    private $messages;

    /**
     * NAVIGATE
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
    }
    /**
     * Navigate back to a list view
     *
     * @param false|string $message
     */
    public function listView($message = FALSE)
    {
        $listCommon = FACTORY_LISTCOMMON::getInstance();
        $queryString = $this->session->getVar("sql_LastMulti");
        if (!$queryString) { // default
            include_once("core/display/FRONT.php");
            $front = new FRONT($message); // __construct() runs on autopilot
            FACTORY_CLOSE::getInstance();
        }
        preg_match("/_(.*)_CORE/u", $queryString, $match);
        if ($match[1] == 'SEARCH') {
            GLOBALS::addTplVar('content', $message);
            $listType = 'search';
            $listCommon->quickSearch = FALSE;
            $listCommon->keepHighlight = TRUE;
            if ($this->session->getVar("sql_LastIdeaSearch")) {
                $listCommon->ideasFound = TRUE;
            }
            $listCommon->patterns = unserialize(base64_decode($this->session->getVar("search_Patterns")));
            include_once('core/modules/list/SEARCH.php');
            $s = new SEARCH();
            $s->reprocess();

            return;
        } elseif ($match[1] == 'QUICKSEARCH') {
            GLOBALS::addTplVar('content', $message);
            $listType = 'search';
            $listCommon->quickSearch = TRUE;
            $listCommon->keepHighlight = TRUE;
            $listCommon->patterns = unserialize(base64_decode($this->session->getVar("search_Patterns")));
            include_once('core/modules/list/QUICKSEARCH.php');
            $qs = new QUICKSEARCH();
            $qs->reprocess();

            return;
        } elseif ($match[1] == 'LISTRESOURCES') {
            GLOBALS::addTplVar('content', $message);
            include_once('core/modules/list/LISTRESOURCES.php');
            $list = new LISTRESOURCES('reorder');

            return;
        } elseif ($match[1] == 'LISTSOMERESOURCES') {
            GLOBALS::addTplVar('content', $message);
            include_once('core/modules/list/LISTSOMERESOURCES.php');
            $list = new LISTSOMERESOURCES();
            $list->reorder();

            return;
        } elseif ($match[1] == 'BASKET') {
            GLOBALS::addTplVar('content', $message);
            include_once('core/modules/basket/BASKET.php');
            $basket = new BASKET();
            $basket->view();
            FACTORY_CLOSE::getInstance();

            return;
        } else { // default
            include_once("core/display/FRONT.php");
            $front = new FRONT($message); // __construct() runs on autopilot
            FACTORY_CLOSE::getInstance();
        }
        /*		GLOBALS::addTplVar('content', $message);
                if($this->session->getVar($listType . '_DisplayAttachment'))
                    $order = 'attachments';
                else
                    $order = $this->session->getVar($listType . '_Order');
                if(!$order)
                    $order = 'creator';
                $listCommon->pagingStyle($countQuery, $listType, $order, $queryString, $countAlphaQuery);
                $listCommon->display($sql, $listType);
        */
    }
    /**
     * Navigate back to a single resource
     *
     * @param int $resourceId
     * @param string $message
     */
    public function resource($resourceId, $message)
    {
        include_once('core/modules/resource/RESOURCEVIEW.php');
        $resource = new RESOURCEVIEW();
        $resource->init($resourceId, $message);
    }
    /**
     * Navigate back to idea thread
     *
     * @param int $ideaId
     * @param string $message
     */
    public function ideaThread($ideaId, $message)
    {
        include_once('core/modules/ideas/IDEAS.php');
        $idea = new IDEAS();
        $idea->threadView($ideaId, $message);
    }
}
