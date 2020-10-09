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
 * Return to various pages within WIKINDX (e.g. from DELETEREOURCES and LISTADDTO . . .)
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
    	$message = rawurlencode($message);
        $queryString = $this->session->getVar("sql_LastMulti");
        if (!$queryString) {// default
			header("Location: index.php?message=$message");
			die;
        }
        $listCommon = FACTORY_LISTCOMMON::getInstance();
        preg_match("/_(.*)_CORE/u", $queryString, $match);
        if ($match[1] == 'SEARCH') {
            if ($this->session->getVar("sql_LastIdeaSearch")) {
                $ideasFound = 1;
            } else {
            	$ideasFound = 0;
            }
            $patterns = base64_encode(serialize($this->session->getVar("search_Patterns")));
			header("Location: index.php?action=list_QUICKSEARCH_CORE&method=reprocess&message=$message&quickSearch=0&keepHighlight=1&ideasFound=$ideasFound&patterns=$patterns");
			die;
        } elseif ($match[1] == 'QUICKSEARCH') {
            $patterns = base64_encode(serialize($this->session->getVar("search_Patterns")));
			header("Location: index.php?action=list_QUICKSEARCH_CORE&method=reprocess&message=$message&quickSearch=1&keepHighlight=1&patterns=$patterns");
			die;
        } elseif ($match[1] == 'LISTRESOURCES') {
			header("Location: index.php?action=list_LISTRESOURCES_CORE&method=reorder&message=$message&url=1");
			die;
        } elseif ($match[1] == 'LISTSOMERESOURCES') {
            GLOBALS::addTplVar('content', $message);
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "modules", "list", "LISTSOMERESOURCES.php"]));
            $list = new LISTSOMERESOURCES();
            $list->reorder();
			header("Location: index.php?action=list_LISTSOMERESOURCES_CORE&method=reorder&message=$message");
			die;
        } elseif ($match[1] == 'BASKET') {
			header("Location: index.php?action=basket_BASKET_CORE&method=view&message=$message");
			die;
        } else { // default
			header("Location: index.php?message=$message");
			die;
        }
    }
    /**
     * Navigate back to a single resource
     *
     * @param int $resourceId
     * @param string $message
     */
    public function resource($resourceId, $message)
    {
		header("Location: index.php?action=resource_RESOURCEVIEW_CORE&message=$message&id=$resourceId");
		die;
    }
    /**
     * Navigate back to idea thread
     *
     * @param int $ideaId
     * @param string $message
     */
    public function ideaThread($ideaId, $message)
    {
    	$message = rawurlencode($message);
		header("Location: index.php?action=ideas_IDEAS_CORE&method=view&message=$message&resourcemetadataId=$ideaId");
		die;
    }
}
