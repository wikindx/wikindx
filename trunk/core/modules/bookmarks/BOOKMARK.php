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
 * BOOKMARK class
 * Manage user's bookmarks.
 */
class BOOKMARK
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $badInput;
    private $userId;
    private $browserTabID = FALSE;
    private $useDBnorow = FALSE;
    public $useDB = FALSE; // accessed elsewhere

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
        $this->userId = $this->session->getVar("setup_UserId");

        $this->badInput = FACTORY_BADINPUT::getInstance();
    }
    /**
     * Get bookmarks
     *
     * @param $userId Default: $this->userId
     *
     * @return array
     */
    public function getBookmarks($userId = FALSE)
    {
    	if (!$userId) {
    		$userId = $this->userId;
    	}
    	$bookmarks = [];
    	$this->db->formatConditions(['usersbookmarksUserId' => $userId]);
        $resultSet = $this->db->select('users_bookmarks', ['usersbookmarksBookmarks']);
        $row = $this->db->fetchRow($resultSet);
        if ($row) { // a row exists for this user
        	$bookmarks = unserialize($row['usersbookmarksBookmarks']);
        	$this->useDB = TRUE;
        } else if ($userId) { // write access but no row yet – the routine checking this will insert rather than update is used in add() below
        	$this->useDBnorow = TRUE;
        } else if ($this->browserTabID) {
        	$bookmarks = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'bookmarks');
        } else {
        	$sessionBookmarks = $this->session->getArray("bookmark");
        	for ($i = 1; $i <= 20; $i++) {
				if (array_key_exists($i . "_name", $sessionBookmarks)) {
					if (array_key_exists($i . "_id", $sessionBookmarks)) {
						$bookmarks[$i . "_name"] = $sessionBookmarks[$i . "_name"];
						$bookmarks[$i . "_id"] = $sessionBookmarks[$i . "_id"];
					} else if (array_key_exists($i . "_multi", $sessionBookmarks)) {
						$bookmarks[$i . "_name"] = $sessionBookmarks[$i . "_name"];
						$bookmarks[$i . "_multi"] = $sessionBookmarks[$i . "_multi"];
					}
				}
			}
        }
    // Check that resource still exists so that menu item at least can be dealt with (another user might have deleted the resource)
    	if (!empty($bookmarks) && !$this->useDB) { // From session or TEMPSTORAGE only. DELETERESOURCE already checks all users_bookmarks rows
    		$this->db->formatConditionsOneField($bookmarks, 'resourceId');
    		$resultSet = $this->db->select('resource', 'resourceId');
    		if (!$this->db->numRows($resultSet)) {
    			$this->session->delVar("bookmark");
    			if ($this->browserTabID) {
        			\TEMPSTORAGE\deleteKeys($this->db, $this->browserTabID, ['bookmarks']);
        		}
    			$bookmarks = [];
    		}
    	}
        if (!is_array($bookmarks)) {
        	return [];
        }
        return $bookmarks;
    }
    /**
     * Display form for adding a bookmark
     *
     * @param mixed $error
     */
    public function init($error = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bookmark"));
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= \HTML\p($this->messages->text("misc", "bookmark"));
        $pString .= \FORM\formHeader("bookmarks_BOOKMARK_CORE");
        $pString .= \FORM\hidden("method", "add");
        $pString .= \FORM\hidden("browserTabID", $this->browserTabID);
        $pString .= \HTML\p(\FORM\textInput($this->messages->text("misc", "bookmarkName"), "name", FALSE, 16, 15));
        $bookmarks = $this->getBookmarks();
        if (sizeof($bookmarks) > 20)
        { // no space left so display list for replacement
			$pString .= \HTML\p($this->messages->text("misc", "bookmarkDelete"));
			$checked = TRUE;
			for ($i = 1; $i <= 20; $i++)
			{
				if (array_key_exists($i . "_name", $bookmarks))
				{
					$pString .= \HTML\p(\FORM\radioButton(FALSE, "bookmark_replace", $i, $checked) .
					"&nbsp;&nbsp;" . stripslashes($bookmarks[$i . "_name"]));
					$checked = FALSE;
				}
			}
        }
        $pString .= \FORM\formSubmit($this->messages->text("submit", "Add"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Add a bookmark
     */
    public function add()
    {
        if (!array_key_exists("name", $this->vars)) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $name = \UTF8\mb_trim($this->vars['name']);
        if (!$name) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $bookmarks = $this->getBookmarks();
        $id = 1; // default
        if (array_key_exists('bookmark_replace', $this->vars)) {
            $id = $this->vars['bookmark_replace'];
            if ($this->useDB) {
            	unset($bookmarks[$id . "_name"]);
            	unset($bookmarks[$id . "_id"]);
            	unset($bookmarks[$id . "_multi"]);
    			$this->db->formatConditions(['usersbookmarksUserId' => $this->userId]);
				$this->db->update('users_bookmarks', ['usersbookmarksBookmarks' => serialize($bookmarks)]);
			} else {
				if ($this->browserTabID) {
					GLOBALS::setTempStorage(['bookmarks' => $bookmarks]);
					\TEMPSTORAGE\store($this->db, $this->browserTabID, GLOBALS::getTempStorage());
				}
            	$this->session->delVar("bookmark_" . $id . "_name");
            	$this->session->delVar("bookmark_" . $id . "_id");
            	$this->session->delVar("bookmark_" . $id . "_multi");
			}
        } else {
            if (!empty($bookmarks)) {
                if ($key = array_search($name, $bookmarks))  {
                    $split = \UTF8\mb_explode('_', $key);
                    $id = $split[0];
                } else {
                    for ($i = 1; $i <= 20; $i++) {
                        if (!array_key_exists($i . "_name", $bookmarks)) {
                            $id = $i;

                            break;
                        }
                    }
                }
            }
        }
        $this->session->delVar("bookmark_DisplayAdd");
        if ($this->session->getVar("bookmark_View") == 'solo') {
        	$bookmarks[$id . "_name"] = $name;
        	$bookmarks[$id . "_id"] = $this->session->getVar("sql_LastSolo");
        	if ($this->useDB) {
				$this->db->formatConditions(['usersbookmarksUserId' => $this->userId]);
				$this->db->update('users_bookmarks', ['usersbookmarksBookmarks' => serialize($bookmarks)]);
			} else if ($this->useDBnorow) {
				$this->db->insert('users_bookmarks', ['usersbookmarksUserId', 'usersbookmarksBookmarks'], [$this->userId, serialize($bookmarks)]);
			} else {
        		$this->session->setVar("bookmark_" . $id . "_name", $name);
            	$this->session->setVar("bookmark_" . $id . "_id", $this->session->getVar("sql_LastSolo"));
				if ($this->browserTabID) {
					GLOBALS::setTempStorage(['bookmarks' => $bookmarks]);
					\TEMPSTORAGE\store($this->db, $this->browserTabID, GLOBALS::getTempStorage());
				}
			}
            // send back to view this resource with success message
            GLOBALS::addTplVar('content', $this->success->text("bookmark"));
            include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "resource", "RESOURCEVIEW.php"]));
            $resource = new RESOURCEVIEW();
            $resource->init($this->session->getVar("sql_LastSolo"));
        } else { // multi view
        	$bookmark = [];
            $bookmark['sql_ListParams'] = $this->session->getVar("sql_ListParams");
            $bookmark['sql_ListStmt'] = $this->session->getVar("sql_ListStmt");
            $bookmark['sql_LastMulti'] = $this->session->getVar("sql_LastMulti");
            $bookmark['sql_DisplayAttachment'] = $this->session->getVar("sql_DisplayAttachment");
            $bookmark['sql_CountStmt'] = $this->session->getVar("sql_CountStmt");
            $bookmark['sql_LastIdeaSearch'] = $this->session->getVar("sql_LastIdeaSearch");
            $bookmark['sql_CountAlphaStmt'] = $this->session->getVar("sql_CountAlphaStmt");
            $bookmark['sql_SubQueryMulti'] = $this->session->getVar("list_SubQueryMulti");
            $bookmark['sql_SubQuery'] = $this->session->getVar("list_SubQuery");
            preg_match("/_(.*)_CORE/u", $this->session->getVar("sql_LastMulti"), $match);
            if ($match[1] == 'SEARCH') {
                $bookmark['Highlight'] = $this->session->getVar("search_Highlight");
                $bookmark['Patterns'] = $this->session->getVar("search_Patterns");
                $bookmark['sql_ListParams'] = $this->session->getVar("advancedSearch_listParams");
                $listType = 'advancedSearch';
            } elseif ($match[1] == 'QUICKSEARCH') {
                $bookmark['Highlight'] = $this->session->getVar("search_Highlight");
                $bookmark['Patterns'] = $this->session->getVar("search_Patterns");
                $listType = 'search';
            } elseif (($match[1] == 'LISTRESOURCES') || ($match[1] == 'LISTSOMERESOURCES')) {
                $listType = 'list';
            } elseif (($match[1] == 'BASKET')) {
                $listType = 'basket';
            }
            $bookmark['listType'] = $listType;
            $bookmark['listTypeArray'] = base64_encode(serialize($this->session->getArray($listType)));
            
        	$bookmarks[$id . "_name"] = $name;
        	$bookmarks[$id . "_multi"] = serialize($bookmark);
        	if ($this->useDB) {
				$this->db->formatConditions(['usersbookmarksUserId' => $this->userId]);
				$this->db->update('users_bookmarks', ['usersbookmarksBookmarks' => serialize($bookmarks)]);
			} else if ($this->useDBnorow) {
				$this->db->insert('users_bookmarks', ['usersbookmarksUserId', 'usersbookmarksBookmarks'], [$this->userId, serialize($bookmarks)]);
			} else {
        		$this->session->setVar("bookmark_" . $id . "_name", $name);
            	$this->session->setVar("bookmark_" . $id . "_multi", serialize($bookmark));
				if ($this->browserTabID) {
					GLOBALS::setTempStorage(['bookmarks' => $bookmarks]);
					\TEMPSTORAGE\store($this->db, $this->browserTabID, GLOBALS::getTempStorage());
				}
			}
            // send back to view list with success message
            $navigate = FACTORY_NAVIGATE::getInstance();
            $navigate->listView("bookmark");
        }
    }
    /**
     * display bookmarks for deletion
     *
     * @param mixed $message
     */
    public function deleteInit($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bookmark"));
        $bookmarks = $this->getBookmarks();
        $bookmarkArray = [];
        $pString = $message ? \HTML\p($message, "error", "center") : '';
        for ($i = 1; $i <= 20; $i++)
        {
            if (array_key_exists($i . "_name", $bookmarks) &&
                array_key_exists($i . "_id", $bookmarks))
            {
                $bookmarkArray[$i] = stripslashes($bookmarks[$i . "_name"]);
            }
            elseif (array_key_exists($i . "_name", $bookmarks) &&
                array_key_exists($i . "_multi", $bookmarks))
            {
                $bookmarkArray[$i] = stripslashes($bookmarks[$i . "_name"]);
            }
        }
        if (empty($bookmarkArray))
        { // none left
            GLOBALS::addTplVar('content', $pString);

            return;
        }
        $pString .= \FORM\formHeader("bookmarks_BOOKMARK_CORE");
        $pString .= \FORM\hidden("method", "delete");
        $pString .= \FORM\hidden("browserTabID", $this->browserTabID);
        $pString .= \FORM\selectFBoxValueMultiple(
            $this->messages->text("misc", "bookmarkDeleteInit"),
            "bookmark_id",
            $bookmarkArray,
            10
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint') .
            BR . \FORM\formSubmit($this->messages->text("submit", "Delete"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete bookmark(s)
     */
    public function delete()
    {
        if (!array_key_exists("bookmark_id", $this->vars)) {
            $this->badInput($this->errors->text("inputError", "missing"), 'deleteInit');
        }
        $bookmarks = $this->getBookmarks();
        $sessionDeletes = [];
        foreach ($this->vars['bookmark_id'] as $i) {
        	unset($bookmarks[$i . '_name']);
        	$sessionDeletes[] = $i . '_name';
            if (array_key_exists($i . "_id", $bookmarks)) {
            	unset($bookmarks[$i . '_id']);
        		$sessionDeletes[] = "bookmark_" . $i . '_id';
            } else if (array_key_exists($i . "_multi", $bookmarks)) {
            	unset($bookmarks[$i . '_multi']);
        		$sessionDeletes[] = "bookmark_" . $i . '_multi';
            }
        }
        if ($this->useDB) {
    		$this->db->formatConditions(['usersbookmarksUserId' => $this->userId]);
    		if (!empty($bookmarks)) {
	    		$this->db->update('users_bookmarks', ['usersbookmarksBookmarks' => serialize($bookmarks)]);
	    	} else {
	    		$this->db->delete('users_bookmarks');
	    	}
        } else {
			if ($this->browserTabID) {
				GLOBALS::setTempStorage(['bookmarks' => $bookmarks]);
				\TEMPSTORAGE\store($this->db, $this->browserTabID, GLOBALS::getTempStorage());
			}
			foreach ($sessionDeletes as $key) {
				$this->session->delVar($key);
			}
		}
        // Any bookmarks left?
        if (!empty($bookmarks)) { // Send back to front
            header("Location: index.php?success=bookmarkDelete");
            die;
        } else {
            $this->deleteInit($this->success->text('bookmarkDelete'));
        }
    }
    /**
     * view a multi list bookmark
     */
    public function multiView()
    {
        $bookmarks = $this->getBookmarks();
        $bookmark = unserialize($bookmarks[$this->vars['id'] . '_multi']);
        if (array_key_exists('sql_MetadataTxt', $bookmark))
        {
            $this->session->setVar("sql_MetadataText", $bookmark['sql_MetadataText']);
        }
        if (array_key_exists('Highlight', $bookmark))
        {
            $this->session->setVar("search_Highlight", $bookmark['Highlight']);
        }
        if (array_key_exists('Patterns', $bookmark))
        {
            $this->session->setVar("search_Patterns", $bookmark['Patterns']);
        }
        $this->session->setVar("sql_LastMulti", $bookmark['sql_LastMulti']);
        if (array_key_exists('sql_LastIdeaSearch', $bookmark))
        {
            $this->session->setVar("sql_LastIdeaSearch", $bookmark['sql_LastIdeaSearch']);
        }
        $this->session->setVar("sql_CountStmt", $bookmark['sql_CountStmt']);
        $this->session->setVar("sql_CountAlphaStmt", $bookmark['sql_CountAlphaStmt']);
        $this->session->setVar("bookmark_MultiView", TRUE);
        $this->session->delVar("select_DisplayAttachment");
        $this->session->delVar("search_DisplayAttachment");
        $this->session->setVar("sql_DisplayAttachment", $bookmark['sql_DisplayAttachment']);
        $this->session->setVar("sql_ListParams", $bookmark['sql_ListParams']);
        $this->session->clearArray($bookmark['listType']);
        $this->session->writeArray(unserialize(base64_decode($bookmark['listTypeArray'])), $bookmark['listType']);
        $this->session->delVar("mywikindx_PagingStart");
        $this->session->delVar("mywikindx_PagingStartAlpha");
        $this->session->setVar("list_SubQueryMulti", $bookmark['sql_SubQueryMulti']);
        $this->session->setVar("list_SubQuery", $bookmark['sql_SubQuery']);
        $this->session->delVar("list_NextPreviousIds");
        $navigate = FACTORY_NAVIGATE::getInstance();
        $navigate->listView();
    }
}
