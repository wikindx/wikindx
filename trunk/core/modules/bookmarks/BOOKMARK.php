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

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();


        $this->badInput = FACTORY_BADINPUT::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bookmark"));
    }
    /**
     *Display form for adding a bookmark
     *
     * @param mixed $error
     */
    public function init($error = FALSE)
    {
        $pString = $error ? \HTML\p($error, "error", "center") : '';
        $pString .= \HTML\p($this->messages->text("misc", "bookmark"));
        $pString .= \FORM\formHeader("bookmarks_BOOKMARK_CORE");
        $pString .= \FORM\hidden("method", "add");
        $pString .= \HTML\p(\FORM\textInput($this->messages->text("misc", "bookmarkName"), "name", FALSE, 16, 15));
        $bookmarks = $this->session->getArray("bookmark");
        if (count($bookmarks) > 0)
        { // no space left so display list for replacement
            $max = TRUE;
            for ($i = 1; $i <= 20; $i++)
            {
                if (!array_key_exists($i . "_name", $bookmarks))
                {
                    $max = FALSE;

                    break;
                }
            }
            if ($max)
            {
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
        if (!array_key_exists("name", $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $name = \UTF8\mb_trim($this->vars['name']);
        if (!$name)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $bookmarks = $this->session->getArray("bookmark");
        $id = 1; // default
        if (array_key_exists('bookmark_replace', $this->vars))
        {
            $id = $this->vars['bookmark_replace'];
            $this->session->delVar("bookmark_" . $id . "_id");
            $this->session->delVar("bookmark_" . $id . "_multi");
        }
        else
        {
            if (count($bookmarks) > 0)
            {
                if ($key = array_search($name, $bookmarks))
                {
                    $split = \UTF8\mb_explode('_', $key);
                    $id = $split[0];
                }
                else
                {
                    for ($i = 1; $i <= 20; $i++)
                    {
                        if (!array_key_exists($i . "_name", $bookmarks))
                        {
                            $id = $i;

                            break;
                        }
                    }
                }
            }
        }
        $this->session->setVar("bookmark_" . $id . "_name", $name);
        if ($this->session->getVar("bookmark_View") == 'solo')
        {
            $this->session->setVar("bookmark_" . $id . "_id", $this->session->getVar("sql_LastSolo"));
            $this->session->saveState('bookmark');
            // send back to view this resource with success message
            GLOBALS::addTplVar('content', $this->success->text("bookmark"));
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "resource", "RESOURCEVIEW.php"]));
            $resource = new RESOURCEVIEW();
            $resource->init($this->session->getVar("sql_LastSolo"));
        }
        else
        { // multi view
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
            if ($match[1] == 'SEARCH')
            {
                $bookmark['Highlight'] = $this->session->getVar("search_Highlight");
                $bookmark['Patterns'] = $this->session->getVar("search_Patterns");
                $bookmark['sql_ListParams'] = $this->session->getVar("advancedSearch_listParams");
                $listType = 'advancedSearch';
            }
            elseif ($match[1] == 'QUICKSEARCH')
            {
                $bookmark['Highlight'] = $this->session->getVar("search_Highlight");
                $bookmark['Patterns'] = $this->session->getVar("search_Patterns");
                $listType = 'search';
            }
            elseif (($match[1] == 'LISTRESOURCES') || ($match[1] == 'LISTSOMERESOURCES'))
            {
                $listType = 'list';
            }
            elseif (($match[1] == 'BASKET'))
            {
                $listType = 'basket';
            }
            $bookmark['listType'] = $listType;
            $bookmark['listTypeArray'] = base64_encode(serialize($this->session->getArray($listType)));
            $this->session->setVar("bookmark_" . $id . "_multi", serialize($bookmark));
            $this->session->saveState('bookmark');
            // send back to view list with success message
            $navigate = FACTORY_NAVIGATE::getInstance();
            $navigate->listView($this->success->text("bookmark"));
        }
    }
    /**
     * display bookmarks for deletion
     *
     * @param mixed $message
     */
    public function deleteInit($message = FALSE)
    {
        $bookmarks = $this->session->getArray("bookmark");
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
        if (!array_key_exists("bookmark_id", $this->vars))
        {
            $this->badInput($this->errors->text("inputError", "missing"), 'deleteInit');
        }
        $deletes = 0;
        $bookmarks = $this->session->getArray("bookmark");
        for ($i = 1; $i <= 20; $i++)
        {
            if (array_search($i, $this->vars['bookmark_id']) !== FALSE)
            {
                if (array_key_exists($i . "_id", $bookmarks))
                {
                    $this->session->delVar("bookmark_" . $i . '_id');
                }
                elseif (array_key_exists($i . "_multi", $bookmarks))
                {
                    $this->session->delVar("bookmark_" . $i . '_multi');
                }
                $this->session->delVar("bookmark_" . $i . '_name');
                ++$deletes;
            }
        }
        $this->session->saveState('bookmark');
        // Any bookmarks left?
        if (count($this->session->getArray("bookmark")) == 2)
        { // Send back to front
            $message = rawurlencode($this->success->text("bookmarkDelete"));
            header("Location: index.php?message=$message");
            die;
        }
        else
        {
            $this->deleteInit($this->success->text('bookmarkDelete'));
        }
    }
    /**
     * view a multi list bookmark
     */
    public function multiView()
    {
        $bookmarks = $this->session->getArray("bookmark");
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
