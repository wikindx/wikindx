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
    /** string */
    private $browserTabID = FALSE;

    /**
     * NAVIGATE
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
    }
    /**
     * Navigate back to a list view
     *
     * @param false|string $message
     */
    public function listView($message = FALSE)
    {
        if (!$queryString = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'sql_LastMulti')) {
	        $queryString = $this->session->getVar("sql_LastMulti");
	    }
        if (!$queryString) {// default
            header("Location: index.php?success=$message");
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
            if (!$patterns = GLOBALS::getTempStorage('search_Patterns')) {
                $patterns = $this->session->getVar("search_Patterns");
            }
            $patterns = base64_encode(serialize($patterns));
            header("Location: index.php?action=list_SEARCH_CORE&method=reprocess&success=$message&quickSearch=0&keepHighlight=1&ideasFound=$ideasFound&patterns=$patterns");
            die;
        }
        elseif ($match[1] == 'QUICKSEARCH') {
            if (!$patterns = GLOBALS::getTempStorage('search_Patterns')) {
                $patterns = $this->session->getVar("search_Patterns");
            }
            $patterns = base64_encode(serialize($patterns));
            header("Location: index.php?action=list_QUICKSEARCH_CORE&method=reprocess&success=$message&quickSearch=1&keepHighlight=1&patterns=$patterns&navigate=1");
            die;
        }
        elseif ($match[1] == 'LISTRESOURCES') {
            header("Location: index.php?action=list_LISTRESOURCES_CORE&method=reorder&success=$message");
            die;
        }
        elseif ($match[1] == 'LISTSOMERESOURCES') {
            header("Location: index.php?action=list_LISTSOMERESOURCES_CORE&method=reorder&success=$message");
            die;
        }
        elseif ($match[1] == 'BASKET') {
            header("Location: index.php?action=basket_BASKET_CORE&method=view&success=$message");
            die;
        }
        else { // default
            header("Location: index.php?success=$message");
            die;
        }
    }
    /**
     * Navigate back to a single resource
     *
     * @param int $resourceId
     * @param mixed $message
     * @param bool $error Default FALSE
     */
    public function resource($resourceId, $message, $error = FALSE)
    {
    	if (is_array($message)) {
    		if ($this->browserTabID) {
    			$uuid = $this->browserTabID;
    		} else {
	    		$uuid = \TEMPSTORAGE\getUuid($this->db);
	    	}
    		\TEMPSTORAGE\store($this->db, $uuid, ['resourceMessages' => $message]);
    		$url = "index.php?action=resource_RESOURCEVIEW_CORE&messages=$uuid&id=$resourceId&browserTabID=" . $this->browserTabID;
    	} else {
    		if ($error) {
	    		$url = "index.php?action=resource_RESOURCEVIEW_CORE&error=$message&id=$resourceId&browserTabID=" . $this->browserTabID;
	    	} else {
	    		$url = "index.php?action=resource_RESOURCEVIEW_CORE&success=$message&id=$resourceId&browserTabID=" . $this->browserTabID;
	    	}
    	}
    	if ($this->browserTabID) {
    		$url .= '&browserTabID=' . $this->browserTabID;
    	}
        header("Location: " . $url);
        die;
    }
    /**
     * Navigate back to idea thread
     *
     * @param int $ideaId
     * @param string $message
     *
     * TODO: Check if still used (there is no method view() . . .)
     */
    public function ideaThread($ideaId, $message)
    {
        $message = rawurlencode($message);
        header("Location: index.php?action=ideas_IDEAS_CORE&method=view&success=$message&resourcemetadataId=$ideaId");
        die;
    }
}
