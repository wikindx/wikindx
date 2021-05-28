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
 * DELETERESOURCE class
 *
 * Delete resources
 */
class DELETERESOURCE
{
    public $resourceIds = [];
    public $navigate = FALSE;
    public $nextResourceId;
    private $db;
    private $vars;
    private $messages;
    private $errors;
    private $success;
    private $icons;
    private $session;
    private $badInput;
    private $gatekeep;
    private $deleteType = 'resource';
    private $idsRaw;
    private $checkPublishers = [];
    private $checkConfPublishers = [];
    private $checkCollections = [];
    private $checkTags = [];
    private $browserTabID = FALSE;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
    }
    /**
     * check we are allowed to delete and load appropriate method
     */
    public function init()
    {
        $this->gatekeep->requireSuper = FALSE; // only admins can delete resources if set to TRUE
        $this->gatekeep->init();
        if (array_key_exists('function', $this->vars))
        {
            $function = $this->vars['function'];
            $this->{$function}();
        }
        else
        {
            $this->display();
        }
    }
    /**
     * Ask for confirmation of delete resource
     *
     * @param bool $deleteWithinList default FALSE
     */
    public function deleteResourceConfirm($deleteWithinList = FALSE)
    {
        if (!$this->validateInput())
        {
            $this->display($this->errors->text("inputError", "missing"));
            FACTORY_CLOSE::getInstance();
        }
        if ($this->deleteType == 'tag')
        {
            $this->vars['resource_id'] = $this->collectResourceFromTag();
        }
        $res = FACTORY_RESOURCECOMMON::getInstance();
        if (is_array($this->vars['resource_id']))
        {
            $this->db->formatConditionsOneField($this->vars['resource_id'], 'resourceId');
            $return = FALSE;
        }
        else
        { // just the one resource so add navigation link
            $this->db->formatConditions(['resourceId' => $this->vars['resource_id']]);
            $return = '&nbsp;&nbsp;' . \HTML\a(
                $this->icons->getClass("edit"),
                $this->icons->getHTML("return"),
                'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->vars['resource_id'] . '&browserTabID=' . $this->browserTabID
            );
        }
        $recordset = $res->getResource(FALSE, $this->db->formatFields('creatorSurname'));
        if (!$numDeletes = $this->db->numRows($recordset))
        {
            $this->display($this->messages->text("resources", "noResult"));
            FACTORY_CLOSE::getInstance();
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "delete") . $return);
        // Rather than print 100s or 1000s of resources, we limit display to <= 'PagingMaxLinks'
        if ($numDeletes <= GLOBALS::getUserVar('PagingMaxLinks'))
        {
            $resourceList = [];
            $bibStyle = FACTORY_BIBSTYLE::getInstance();
            $bibStyle->output = 'html';
            while ($row = $this->db->fetchRow($recordset))
            {
                $resourceList[]['resource'] = $bibStyle->process($row);
            }
            // Templates expect list ordered from 0, so we renumber from zero
            $rL = array_values($resourceList);
            GLOBALS::setTplVar('resourceList', $rL);
            GLOBALS::addTplVar('submit', \FORM\formSubmit($this->messages->text("submit", "Delete")) . \FORM\formEnd());
            unset($resourceList, $rL);
            $pString = '';
        }
        else
        {
            $pString = $this->messages->text("misc", "confirmDelete", " " . $numDeletes . " ");
        }
        $pString .= \FORM\formHeader('admin_DELETERESOURCE_CORE');
        $pString .= \FORM\hidden('function', 'process');
        $pString .= \FORM\hidden("browserTabID", $this->browserTabID);
        $pString .= \FORM\hidden('deleteWithinList', $deleteWithinList);
        if ($this->navigate)
        {
            $pString .= \FORM\hidden('navigate', $this->navigate);
        }
        if ($this->nextResourceId)
        {
            $pString .= \FORM\hidden('nextResourceId', $this->nextResourceId);
        }
        if (is_array($this->vars['resource_id']))
        {
            $numIds = count($this->vars['resource_id']);
            $oldMaxSize = ini_get('max_input_vars');
            if ($numIds > ($oldMaxSize - 20))
            {
                ini_set('max_input_vars', $numIds + 21);
                $newMaxSize = ini_get('max_input_vars');
                if ($newMaxSize <= $oldMaxSize)
                { // i.e. unable to increase max_input_vars
                    $this->display($this->errors->text("inputError", "maxInputVars", "$oldMaxSize"));
                    FACTORY_CLOSE::getInstance();
                }
            }
            $uuid = \TEMPSTORAGE\getUuid($this->db);
            \TEMPSTORAGE\store($this->db, $uuid, $this->vars['resource_id']);
            $pString .= \FORM\hidden("uuid", $uuid);
        }
        else
        {
            $pString .= \FORM\hidden("resource_id", $this->vars['resource_id']);
        }
        if (array_key_exists('nextDelete', $this->vars))
        {
            $pString .= \FORM\hidden("nextDelete", $this->vars['nextDelete']);
        }
        $pString .= BR . "&nbsp;" . BR;
        if ($numDeletes > GLOBALS::getUserVar('PagingMaxLinks'))
        {
            $pString .= \FORM\formSubmit($this->messages->text("submit", "Delete")) . \FORM\formEnd();
        }
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * display select box of resources to delete
     *
     * @param false|string $message
     */
    public function display($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "delete"));
        if (!$this->resources = $this->grabAll())
        {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noResources'));

            return;
        }
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "miscellaneous", "TAG.php"]));
        $tag = new TAG();
        $tags = $tag->grabAll();
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= \FORM\formHeader('admin_DELETERESOURCE_CORE');
        $pString .= \FORM\hidden('function', 'deleteResourceConfirm');
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(FALSE, "resource_id", $this->resources, 20, 80) .
            BR . \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "multiples")
            ), 'hint') . BR .
            BR . \FORM\formSubmit($this->messages->text("submit", "Confirm")));
        if (is_array($tags))
        {
            // add 0 => IGNORE to tags array
            $temp[0] = $this->messages->text("misc", "ignore");
            foreach ($tags as $key => $value)
            {
                $temp[$key] = $value;
            }
            $tags = $temp;
            $pString .= \HTML\td(\FORM\selectFBoxValueMultiple($this->messages->text("misc", "tag"), 'tagId', $tags, 5) .
            BR . \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "multiples")
            ), 'hint'));
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * process
     */
    private function process()
    {
        if (!$this->validateInput())
        {
            $this->display($this->errors->text("inputError", "missing"));
            FACTORY_CLOSE::getInstance();
        }
        if (array_key_exists('uuid', $this->vars))
        { // i.e. large numbers of deletes
            $this->idsRaw = \TEMPSTORAGE\fetch($this->db, $this->vars['uuid']);
            \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
        }
        else
        {
            $this->idsRaw = \UTF8\mb_explode(',', $this->vars['resource_id']);
        }
        $this->reallyDelete();
        $this->checkHanging();
        // If we have 0 resources left, remove 'sql_stmt' etc. from session so it doesn't cause problems with
        // exporting bibliographies etc.
        if ($this->db->selectCountOnly("resource", "resourceId") == 0)
        {
            $this->session->delVar("sql_ListStmt");
            $this->session->delVar("sql_LastMulti");
            $this->session->delVar("sql_LastSolo");
            if ($this->browserTabID)
            {
                \TEMPSTORAGE\deleteKeys($this->db, $this->browserTabID, ['sql_ListStmt', 'sql_LastMulti', 'sql_LastSolo']);
            }
        }
        elseif (!$lastSolo = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'sql_LastSolo'))
        { // If the row doesn't exist, FALSE
            $lastSolo = $this->session->getVar("sql_LastSolo");
            if (is_array($this->idsRaw))
            {
                $diff = array_diff($this->session->getVar('list_NextPreviousIds'), $this->idsRaw);
                foreach ($diff as $id)
                {
                    $newDiff[] = $id;
                }
                $this->session->setVar('list_NextPreviousIds', $newDiff);
                if (in_array($lastSolo, $this->idsRaw))
                {
                    $this->session->delVar("sql_LastSolo");
                }
            }
            else
            {
                $diff = $this->session->getVar('list_NextPreviousIds');
                unset($diff[$this->idsRaw]);
                foreach ($diff as $id)
                {
                    $newDiff[] = $id;
                }
                $this->session->setVar('list_NextPreviousIds', $newDiff);
                if ($lastSolo == $this->idsRaw)
                {
                    $this->session->delVar("sql_LastSolo");
                }
            }
        }
        else
        {
            if (is_array($this->idsRaw))
            {
                $diff = array_diff(\TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_NextPreviousIds'), $this->idsRaw);
                foreach ($diff as $id)
                {
                    $newDiff[] = $id;
                }
                \TEMPSTORAGE\store($this->db, $this->browserTabID, ['list_NextPreviousIds' => $newDiff]);
                if (in_array($lastSolo, $this->idsRaw))
                {
                    \TEMPSTORAGE\deleteKeys($this->db, $this->browserTabID, ['sql_LastSolo']);
                }
            }
            else
            {
                $diff = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_NextPreviousIds');
                unset($diff[$this->idsRaw]);
                foreach ($diff as $id)
                {
                    $newDiff[] = $id;
                }
                \TEMPSTORAGE\store($this->db, $this->browserTabID, ['list_NextPreviousIds' => $newDiff]);
                if ($lastSolo == $this->idsRaw)
                {
                    \TEMPSTORAGE\deleteKeys($this->db, $this->browserTabID, ['sql_LastSolo']);
                }
            }
        }
        if ($this->vars['deleteWithinList'] || ($this->navigate == 'list'))
        {
            // i.e. from the organize list select box – need to recalculate list total we return to.
            $newPagingTotal = $this->session->getVar("setup_PagingTotal") - count($this->idsRaw);
            $this->session->setVar("setup_PagingTotal", $newPagingTotal);
            $this->session->delVar("list_PagingAlphaLinks");
            if ($this->browserTabID)
            {
                \TEMPSTORAGE\store($this->db, $this->browserTabID, ['setup_PagingTotal' => $newPagingTotal]);
                \TEMPSTORAGE\deleteKeys($this->db, $this->browserTabID, ['list_PagingAlphaLinks']);
            }
        }
        // Which page do we return to?
        if (GLOBALS::getUserVar('BrowseBibliography'))
        {
            $this->db->formatConditions(['userbibliographyresourceBibliographyId' => GLOBALS::getUserVar('BrowseBibliography')]);
            $resultset = $this->db->select('user_bibliography_resource', ['userbibliographyresourceId']);
            if (!$this->db->numRows($resultset))
            {
                $this->db->formatConditions(['usersId' => $this->session->getVar('setup_UserId')]);
                $this->db->update('users', ['usersBrowseBibliography' => 0]);
                header("Location: index.php?success=resourceDelete");
                die;
            }
        }
        if ($this->session->getVar("setup_PagingTotal") == 0)
        { // Return to home page
            header("Location: index.php?success=resourceDelete");
            die;
        }
        if ($this->navigate == 'nextResource')
        { // next single view
            $navigate = FACTORY_NAVIGATE::getInstance();
            $navigate->resource($this->nextResourceId, "resourceDelete");
        }
        elseif ($this->navigate == 'list')
        { // previous multi list
            $navigate = FACTORY_NAVIGATE::getInstance();
            $navigate->listView("resourceDelete");
        }
        elseif ($this->navigate == 'front')
        { // Return to home page
            header("Location: index.php?success=resourceDelete");
            die;
        }
        else
        { // return to multiple resource delete page -- $this->navigate == FALSE
            header("Location: index.php?action=admin_DELETERESOURCE_CORE&method=display&success=resourceDelete");
            die;
        }
        FACTORY_CLOSE::getInstance();
    }
    /**
     * delete resources and meta data
     */
    private function reallyDelete()
    {
        $this->db->formatConditionsOneField($this->idsRaw, 'resourceId');
        $this->db->leftJoin('resource_misc', 'resourcemiscId', 'resourceId');
        $recordset = $this->db->select('resource', ['resourcemiscPublisher', 'resourcemiscCollection',
            'resourcemiscTag', 'resourceId', 'resourceType', 'resourcemiscField1', ]);
        while ($row = $this->db->fetchRow($recordset))
        {
            if ($row['resourcemiscPublisher'])
            {
                $this->checkPublishers[$row['resourcemiscPublisher']] = FALSE;
            }
            if (($row['resourceType'] == 'proceedings_article') && $row['resourcemiscField1'])
            {
                $this->checkConfPublishers[$row['resourcemiscField1']] = FALSE;
            }
            if ($row['resourcemiscCollection'])
            {
                $this->checkCollections[$row['resourcemiscCollection']] = FALSE;
            }
            if ($row['resourcemiscTag'])
            {
                $this->checkTags[$row['resourcemiscTag']] = FALSE;
            }
        }
        // now start delete
        $this->db->formatConditionsOneField($this->idsRaw, 'resourceId');
        $this->db->delete('resource');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcemiscId');
        $this->db->delete('resource_misc');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcetextId');
        $this->db->delete('resource_text');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourceurlResourceId');
        $this->db->delete('resource_url');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcecreatorResourceId');
        $this->db->delete('resource_creator');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcekeywordResourceId');
        $this->db->delete('resource_keyword');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourceusertagsResourceId');
        $this->db->delete('resource_user_tags');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcecategoryResourceId');
        $this->db->delete('resource_category');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcepageId');
        $this->db->delete('resource_page');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcetimestampId');
        $this->db->delete('resource_timestamp');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourceyearId');
        $this->db->delete('resource_year');
        $this->db->formatConditionsOneField($this->idsRaw, 'importrawId');
        $this->db->delete('import_raw');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcecustomResourceId');
        $this->db->delete('resource_custom');
        $this->db->formatConditionsOneField($this->idsRaw, 'statisticsresourceviewsResourceId');
        $this->db->delete('statistics_resource_views');
        $this->db->formatConditionsOneField($this->idsRaw, 'statisticsattachmentdownloadsResourceId');
        $this->db->delete('statistics_attachment_downloads');
        $this->deleteBasket();
        $this->deleteBookmark();
        $this->deleteMetadata();
        $this->checkBibtexStringTable();
        // delete these ids from any user bibliographies
        $this->db->formatConditionsOneField($this->idsRaw, 'userbibliographyresourceResourceId');
        $this->db->delete('user_bibliography_resource');
        // check file attachments
        $this->db->formatConditionsOneField($this->idsRaw, 'resourceattachmentsResourceId');
        $recordSet = $this->db->select(
            'resource_attachments',
            ['resourceattachmentsId', 'resourceattachmentsHashFilename']
        );
        while ($row = $this->db->fetchRow($recordSet))
        {
            $hashes[$row['resourceattachmentsId']] = $row['resourceattachmentsHashFilename'];
        }
        if (isset($hashes))
        {
            foreach ($hashes as $id => $hash)
            {
                $this->db->formatConditions(['resourceattachmentsId' => $id]);
                $this->db->delete('resource_attachments');
                // Is file used by other resources?  If not, unlink it
                $this->db->formatConditions(['resourceattachmentsHashFilename' => $hash]);
                $recordSet = $this->db->select('resource_attachments', 'resourceattachmentsHashFilename');
                if (!$this->db->numRows($recordSet))
                { // Unlink it
                    @unlink(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_ATTACHMENTS, $hash]));
                }
            }
        }
    }
    /**
     * Delete resource from all user baskets
     */
     private function deleteBasket()
     {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "basket", "BASKET.php"]));
        $basketObj = new BASKET();
// Do this user and storage first . . .
        $basket = $basketObj->getBasket($this->session->getVar('setup_UserId'));
        if (!empty($basket)) {
			$basketIds = array_diff($basket, $this->idsRaw);
			if (empty($basketIds)) {
				if ($basketObj->useDB) {
                	$this->db->formatConditions(['usersbasketUserId' => $this->session->getVar('setup_UserId')]);
                	$this->db->delete('users_basket');
				} else {
					$this->session->delVar("basket_List");
					\TEMPSTORAGE\deleteKeys($this->db, $this->browserTabID, ['basket_List']);
				}
			}
			else {
				if ($basketObj->useDB) {
					$this->db->formatConditions(['usersbasketUserId' => $this->session->getVar('setup_UserId')]);
					$this->db->update('users_basket', ['usersbasketBasket' => serialize($basketIds)]);
				} else {
					$this->session->setVar("basket_List", $basketIds);
					if ($this->browserTabID) {
						\TEMPSTORAGE\store($this->db, $this->browserTabID, ['basket_List' => $basketIds]);
					}
				}
			}
		}
// Then other users' baskets . . .
		$this->db->formatConditions(['usersbasketUserId' => $this->session->getVar('setup_UserId')], TRUE); // Not this user's
		$resultSet = $this->db->select('users_basket', ['usersbasketUserId', 'usersbasketBasket']);
		while ($row = $this->db->fetchRow($resultSet)) {
			$basket = unserialize($row['usersbasketBasket']);
			$basketIds = array_diff($basket, $this->idsRaw);
			if (empty($basketIds)) {
                $this->db->formatConditions(['usersbasketUserId' => $row['usersbasketUserId']]);
            	$this->db->delete('users_basket');
			} else {
				$this->db->formatConditions(['usersbasketUserId' => $row['usersbasketUserId']]);
				$this->db->update('users_basket', ['usersbasketBasket' => serialize($basketIds)]);
			}
		}
	}
    
    /**
     * Delete resource from all user bookmarks
     */
     private function deleteBookmark()
     {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "bookmarks", "BOOKMARK.php"]));
        $bookmarkObj = new BOOKMARK();
// Do this user and storage first . . .
        $bookmarks = $bookmarkObj->getBookmarks($this->session->getVar('setup_UserId'));
        if (!empty($bookmarks)) {
			foreach ($bookmarks as $key => $value) {
				if (array_search($value, $this->idsRaw) !== FALSE) {
					$split = explode('_', $key);
					if ($split[1] != 'id') { // Don't want to match on name but on id instead
						continue;
					}
					unset($bookmarks[$split[0] . '_name']);
					unset($bookmarks[$split[0] . '_id']);
					$this->session->delVar('bookmark_' . $split[0] . '_name');
					$this->session->delVar('bookmark_' . $split[0] . '_id');
				}
			}
			if (empty($bookmarks)) {
				if ($bookmarkObj->useDB) {
                	$this->db->formatConditions(['usersbookmarksUserId' => $this->session->getVar('setup_UserId')]);
                	$this->db->delete('users_bookmarks');
				} else {
					if ($this->browserTabID) {
						\TEMPSTORAGE\deleteKeys($this->db, $this->browserTabID, ['bookmarks']);
					}
				}
			}
			else {
				if ($bookmarkObj->useDB) {
					$this->db->formatConditions(['usersbookmarksUserId' => $this->session->getVar('setup_UserId')]);
					$this->db->update('users_bookmarks', ['usersbookmarksBookmarks' => serialize($bookmarks)]);
				} else {
					if ($this->browserTabID) {
						GLOBALS::setTempStorage(['bookmarks' => $bookmarks]);
						\TEMPSTORAGE\store($this->db, $this->browserTabID, GLOBALS::getTempStorage());
					}
				}
			}
		}
// Then other users' bookmarks . . .
		$this->db->formatConditions(['usersbookmarksUserId' => $this->session->getVar('setup_UserId')], TRUE); // Not this user's
		$resultSet = $this->db->select('users_bookmarks', ['usersbookmarksUserId', 'usersbookmarksBookmarks']);
		while ($row = $this->db->fetchRow($resultSet)) {
			$bookmarks = unserialize($row['usersbookmarksBookmarks']);
			foreach ($bookmarks as $key => $value) {
				if (array_search($value, $this->idsRaw) !== FALSE) {
					$split = explode('_', $key);
					if ($split[1] != 'id') { // Don't want to match on name but on id instead
						continue;
					}
					unset($bookmarks[$split[0] . '_name']);
					unset($bookmarks[$split[0] . '_id']);
				}
			}
			if (empty($bookmarks)) {
                $this->db->formatConditions(['usersbookmarksUserId' => $row['usersbookmarksUserId']]);
            	$this->db->delete('users_bookmarks');
			} else {
				$this->db->formatConditions(['usersbookmarksUserId' => $row['usersbookmarksUserId']]);
				$this->db->update('users_bookmarks', ['usersbookmarksBookmarks' => serialize($bookmarks)]);
			}
		}
	}
    
    /**
     * checkHanging
     *
     * check that delete of resources hasn't left any resource-less creators, keywords, publisher, collections etc.
     * If so, delete them.
     */
    private function checkHanging()
    {
        $creator = FACTORY_CREATOR::getInstance();
        $keyword = FACTORY_KEYWORD::getInstance();
        $publisher = FACTORY_PUBLISHER::getInstance();
        // Collections -- must be done first as they might contain publishers and creators
        if (!empty($this->checkCollections))
        {
            $removeCollections = $this->checkCollections;
            foreach ($this->checkCollections as $collectionId => $null)
            {
                $this->db->formatConditions(['resourcemiscCollection' => $collectionId]);
                $recordset = $this->db->select('resource_misc', 'resourcemiscCollection');
                if ($this->db->numRows($recordset))
                {
                    unset($removeCollections[$collectionId]);
                }
            }
            if (!empty($removeCollections))
            {
                $this->db->formatConditionsOneField(array_keys($removeCollections), 'collectionId');
                $this->db->delete('collection');
                $this->db->deleteCache('cacheResourceCollections');
                $this->db->deleteCache('cacheResourceCollectionTitles');
                $this->db->deleteCache('cacheResourceCollectionShorts');
                $this->db->deleteCache('cacheMetadataCollections');
            }
        }
        // Creators
        $creator->removeHanging();
        // Keywords
        $keyword->removeHanging();
        // Publishers
        $publisher->removeHanging();
        // Tags
        foreach ($this->checkTags as $tagId => $void)
        {
            $this->db->formatConditions(['resourcemiscTag' => $tagId]);
            if (!$this->db->selectFirstField('resource_misc', 'resourcemiscTag'))
            {
                $this->db->formatConditions(['tagId' => $tagId]);
                $this->db->delete('tag');
            }
        }
    }
    /**
     * check @strings still have resources in import_raw - else delete string entries
     */
    private function checkBibtexStringTable()
    {
        $recordset = $this->db->select('import_raw', ['importrawId', 'importrawStringId']);
        // Delete all from `bibtex_string`
        if (!$this->db->numRows($recordset))
        {
            $this->db->delete('bibtex_string');

            return;
        }
        $rawStringIds = [];
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$row['importrawStringId'])
            {
                continue;
            }
            $rawStringIds[] = $row['importrawStringId'];
        }
        if (empty($rawStringIds))
        {
            return;
        }
        foreach (array_unique($rawStringIds) as $id)
        {
            $deleteIds['bibtexstringId'] = $id;
        }
        $this->db->formatConditions($deleteIds, TRUE); // not equal to...
        $this->db->delete('bibtex_string');
    }
    /**
     * validate input
     *
     * @return bool
     */
    private function validateInput()
    {
        if (array_key_exists('navigate', $this->vars))
        {
            $this->navigate = $this->vars['navigate'];
        }
        if (array_key_exists('nextResourceId', $this->vars))
        {
            $this->nextResourceId = $this->vars['nextResourceId'];
        }
        if (!empty($this->resourceIds))
        {
            $this->vars = array_merge($this->vars, $this->resourceIds);
        }
        if (array_key_exists('tagId', $this->vars))
        {
            foreach ($this->vars['tagId'] as $tag)
            {
                if ($tag)
                {
                    $this->deleteType = 'tag';

                    return TRUE;
                }
            }
        }
        if (array_key_exists('uuid', $this->vars))
        {
            return TRUE;
        }

        return array_key_exists('resource_id', $this->vars);
    }
    /**
     * run SQL delete statements on meta data
     */
    private function deleteMetadata()
    {
        $ids = [];
        // First get meta data ids for deleting from resource_keyword
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcemetadataResourceId');
        $recordset = $this->db->select('resource_metadata', 'resourcemetadataId');
        while ($row = $this->db->fetchRow($recordset))
        {
            $ids[] = $row['resourcemetadataId'];
        }
        // Delete meta data parent row
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcemetadataResourceId');
        $this->db->delete('resource_metadata');
        if (empty($ids))
        {
            return;
        }
        // Delete any quote or paraphrase comments
        $this->db->formatConditionsOneField($ids, 'resourcemetadataMetadataId');
        $this->db->delete('resource_metadata');
        // Delete metadata keywords
        $this->db->formatConditionsOneField($ids, 'resourcekeywordMetadataId');
        $this->db->delete('resource_keyword');
    }
    /**
     * Grab ids and titles of resources
     *
     * @retrun array
     */
    private function grabAll()
    {
        $titles = [];
        $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'resourceId');
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorMain');
        $this->db->leftJoin('resource_year', 'resourceyearId', 'resourceId');
        $fields[] = $this->db->formatFields(['resourceId', 'resourceType', 'resourceTitleSort', 'creatorSurname']);
        $fields[] = $this->db->coalesce(['resourceyearYear1', 'resourceyearYear2'], 'year');
        $this->db->groupBy(['resourceId', 'resourceType', 'resourceTitleSort', 'creatorSurname', 'year']);
        $this->db->orderBy('creatorSurname');
        $this->db->orderBy('year');
        $this->db->orderBy('resourceTitleSort', TRUE, FALSE);
        $recordset = $this->db->select('resource', implode(',', $fields), FALSE, FALSE);
        while ($row = $this->db->fetchRow($recordset))
        {
            $final = [];
            if ($row['creatorSurname'])
            {
                $final[] = $row['creatorSurname'];
            }
            if ($row['year'])
            {
                $final[] = '(' . $row['year'] . ')';
            }
            $final[] = $row['resourceTitleSort'];
            $final[] = '[' . $row['resourceType'] . ']';
            $titles[$row['resourceId']] = \HTML\dbToFormTidy(implode(', ', $final));
        }

        return $titles;
    }
    /**
     * get array of resource ids belonging to tag ids
     *
     * @return array
     */
    private function collectResourceFromTag()
    {
        $this->db->formatConditionsOneField($this->vars['tagId'], 'resourcemiscTag');
        $recordset = $this->db->select('resource_misc', 'resourcemiscId');
        if (!$this->db->numRows($recordset))
        {
            $this->display($this->messages->text("resources", "noResult"));
            FACTORY_CLOSE::getInstance();
        }
        while ($row = $this->db->fetchRow($recordset))
        {
            $ids[] = $row['resourcemiscId'];
        }

        return $ids;
    }
}
