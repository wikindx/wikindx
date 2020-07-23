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
 * LISTCOMMON common functions for listing, searching, selecting etc. resources
 *
 * @package wikindx\core\lists
 */
class LISTCOMMON
{
    /** object */
    public $pagingObject = FALSE;
    /** boolean */
    public $keepHighlight = FALSE;
    /** string */
    public $patterns = FALSE;
    /** string */
    public $navigate = 'list';
    /** boolean */
    public $listQuarantined = FALSE;
    /** boolean */
    public $metadata = FALSE;
    /** string */
    public $metadataKeyword = FALSE;
    /** array */
    public $metadataText = [];
    /** array */
    public $metadataTextCite = [];
    /** array */
    public $metadataTextJoin = [];
    /** array */
    public $metadataTextCond = [];
    /** boolean */
    public $quickSearch = FALSE;
    /** string */
    public $browse = FALSE;
    /** boolean */
    public $metadataPaging = FALSE;
    /** booolean */
    public $ideasFound = FALSE;
    /** object */
    private $db;
    /** array */
    private $vars;
    /** object */
    private $icons;
    /** object */
    private $session;
    /** object */
    private $messages;
    /** object */
    private $user;
    /** object */
    private $stats;
    /** object */
    private $bibStyle;
    /** object */
    private $commonBib;
    /** object */
    private $cite;
    /** object */
    private $resCommon;
    /** object */
    private $languageClass;
    /** array */
    private $rows = [];
    /** array */
    public $attachmentHashnames = [];


    /**
     * LISTCOMMON
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->icons = FACTORY_LOADICONS::getInstance();

        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->stats = FACTORY_STATISTICS::getInstance();
        $this->bibStyle = FACTORY_BIBSTYLE::getInstance();

        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->cite = FACTORY_CITE::getInstance();
        $this->resCommon = FACTORY_RESOURCECOMMON::getInstance();
        $this->languageClass = FACTORY_CONSTANTS::getInstance();
        $this->stats->list = TRUE;
    }
    /**
     * Check there are resources to display
     *
     * @return bool
     */
    public function resourcesExist()
    {
        $recordset = $this->db->select('database_summary', 'databaseSummaryTotalResources');
        if (!$this->db->fetchOne($recordset)) {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noResources'));

            return FALSE;
        }
        if ($useBib = $this->session->getVar("mywikindx_Bibliography_use")) {
            $this->db->formatConditions(['userbibliographyresourceBibliographyId' => $useBib]);
            $this->db->formatConditions($this->db->formatFields('userbibliographyresourceResourceId') . $this->db->equal .
                $this->db->formatFields('resourceId'));
            $resultset = $this->db->select(['resource', 'user_bibliography_resource'], 'resourceId');
            if (!$this->db->numRows($resultset)) {
                GLOBALS::addTplVar('content', $this->messages->text('misc', 'noResourcesBib'));

                return FALSE;
            }
        } else {
            if ($this->db->tableIsEmpty('resource')) {
                GLOBALS::addTplVar('content', $this->messages->text('misc', 'noResourcesBib'));

                return FALSE;
            }
        }

        return TRUE;
    }
    /**
     * Display a list from the lastMulti menu item
     *
     * @param false|string $listType (list, search etc)
     */
    public function lastMulti($listType = FALSE)
    {
        $this->session->delVar("mywikindx_PagingStart");
        $this->session->delVar("list_NextPreviousIds");
        $sql = $this->session->getVar("sql_ListStmt");
        // set back to beginning
        $limit = $this->db->limit(GLOBALS::getUserVar('Paging'), $this->pagingObject->start, TRUE); // "LIMIT $limitStart, $limit";
        $this->display($sql . $limit, $listType);
        $this->session->saveState(['list', 'sql', 'bookmark']);
        $this->session->setVar("list_SubQuery", $this->session->getVar("list_SubQueryMulti"));
    }
    /**
     * Produce a list of resources
     *
     * @param string $sql
     * @param false|string $listType Default is FALSE
     *
     * @return bool
     */
    public function display($sql, $listType = FALSE)
    {
        $this->session->setVar("list_On", TRUE);
        if (!$this->keepHighlight) {
            $this->session->delVar("search_Highlight");
        }
        $this->bibStyle->bibformat->patterns = $this->patterns;
        if (GLOBALS::getUserVar('ListLink')) {
            $this->bibStyle->linkUrl = FALSE;
        }
        if ($listType != 'cite') {
            $this->session->setVar("bookmark_View", 'multi');
        }
        // $SQL can be FALSE if browsing a keyword that is not attached to resources but only to ideas.
        if (!$sql) {
            $this->noResources($listType);

            return TRUE;
        }
        if (($listType == 'front') || ($listType == 'cite')) {
            $recordset = $this->db->query($sql); // Don't mess up Last Multi by saving querystring
            if ($listType == 'cite') {
                if (!$this->db->numRows($recordset)) {
                    return FALSE;
                }
            }
        } else {
            $recordset = $this->db->query($sql, TRUE);
        }
        if ($recordset === FALSE) {
            $this->noResources($listType);

            return TRUE;
        }
        // Displaying only attachments?
        if ($this->session->getVar($listType . '_DisplayAttachment')) {
            $this->listAttachments($listType);
            $this->session->setVar("sql_DisplayAttachment", $listType . '_DisplayAttachment');

            return;
        }
        $this->session->delVar("sql_DisplayAttachment");
        $multiUserSwitch = (WIKINDX_MULTIUSER);
        if ($multiUserSwitch) {
            GLOBALS::addTplVar('multiUser', TRUE);
        }
        $quarantineSwitch = (WIKINDX_QUARANTINE);
        $useDateFormatMethod = method_exists($this->languageClass, "dateFormat");
        //$citeRadioButtonFirst = TRUE;

        if ($this->metadataKeyword) {
            $listMetadataMethod = 'listMetadata';
        } elseif (!empty($this->metadataText)) {
            $listMetadataMethod = 'listMetadataText';
        } elseif ($listType == 'cite' && !empty($this->metadataTextCite)) {
            $listMetadataMethod = 'listMetadataText';
        } else {
            $listMetadataMethod = '';
        }

        $resourceList = [];
        $resources = [];
        $resIds = [];
        while ($row = $this->db->fetchRow($recordset)) {
            // will be the case if ideas have been found through a keyword
            if (!$row['resourceId']) {
                continue;
            }

            // Don't return twice the same resource
            if (array_key_exists($row['resourceId'], $resources) !== FALSE) {
                continue;
            }

            $this->rows[$row['resourceId']] = $row;

            if ($listMetadataMethod != '') {
                $mArray = $this->{$listMetadataMethod}($row['resourceId']);
                if (!empty($mArray)) {
                    $resourceList[$row['resourceId']]['metadata'] = $mArray;
                    unset($mArray);
                }
            }

            // e.g. from the TinyMCE insert cite button of resource metadata
            if ($listType != 'cite') {
                if ($quarantineSwitch && ($row['resourcemiscQuarantine'] == 'Y')) {
                    $resourceList[$row['resourceId']]['quarantine'] = $this->icons->getHTML("quarantine");
                }

                if ($multiUserSwitch) {
                    $resourceList[$row['resourceId']]['user'] = $this->user->displayUserAddEdit($row);
                    $resourceList[$row['resourceId']]['maturity'] = $row['resourcemiscMaturityIndex'] ?
                        "&nbsp;" . $this->messages->text("misc", "matIndex") .
                        "&nbsp;" . $row['resourcemiscMaturityIndex'] . "/10" . BR
                        : FALSE;
                }

                if ($useDateFormatMethod) {
                    $resourceList[$row['resourceId']]['timestamp'] = \LOCALES\dateFormat($row['resourcetimestampTimestamp']);
                } else {
                    $resourceList[$row['resourceId']]['timestamp'] = $row['resourcetimestampTimestamp'];
                }
            }

            // Although quotes and paraphrases are not useful in all cases,
            // we add them to force the count in the subsequent procedure.
            $resources[$row['resourceId']] = ['quotes' => $row['resourcesummaryQuotes'], 'paraphrases' => $row['resourcesummaryParaphrases']];
            $resIds[] = $row['resourceId'];
            unset($row);
        }

        if (count($resources) > 0) {
            $this->session->setVar("list_NextPreviousIds", base64_encode(serialize($resIds)));
            $this->formatResources($listType, $resourceList, $resources);
            $this->createLinks($listType, $resourceList, $resources);

            if (!$this->listQuarantined && ($listType != 'cite')) {
                if ($this->pagingObject) {
                    $this->displayListInfo($listType, TRUE);
                }
            }

            // Templates expect list ordered from 0,
            // so we renumber from zero
            GLOBALS::setTplVar('resourceList', array_values($resourceList));
            unset($resourceList);
        } else {
            $this->noResources($listType);
        }
        $this->rows = NULL;
        unset($resources);
        unset($resourceList);

        return TRUE;
    }
    /**
     * Tidy display when there are no resources
     *
     * @param string $listType
     *
     * @return bool
     */
    public function noResources($listType)
    {
        $this->session->delVar("list_AllIds");
        $this->session->delVar("list_NextPreviousIds");
        if ($this->pagingObject && ($listType != 'cite')) {
            $this->displayListInfo($listType, FALSE);
        } else { // from SEARCH.php if only ideas are searched on
            $this->pagingObject = FACTORY_PAGINGALPHA::getInstance();
            $this->displayListInfo($listType, FALSE);
        }

        return TRUE;
    }
    /**
     * Print radio buttons ascending, descending for ordering
     *
     * @param string $type
     *
     * @return string
     */
    public function displayAscDesc($type)
    {
        if ($ascDesc = trim($this->session->getVar($type . "_AscDesc"))) {
            if ($ascDesc == 'ASC') {
                return \FORM\radioButton(FALSE, $type . "_AscDesc", 'ASC', TRUE) .
                    $this->messages->text("list", "ascending") .
                    BR . \FORM\radioButton(FALSE, $type . "_AscDesc", 'DESC') .
                    $this->messages->text("list", "descending");
            } else {
                return \FORM\radioButton(FALSE, $type . "_AscDesc", 'ASC') .
                    $this->messages->text("list", "ascending") .
                    BR . \FORM\radioButton(FALSE, $type . "_AscDesc", 'DESC', TRUE) .
                    $this->messages->text("list", "descending");
            }
        } else {
            return \FORM\radioButton(FALSE, $type . "_AscDesc", 'ASC', TRUE) .
                $this->messages->text("list", "ascending") .
                BR . \FORM\radioButton(FALSE, $type . "_AscDesc", 'DESC') .
                $this->messages->text("list", "descending");
        }
    }
    /**
     * Set the paging object if paging is alphabetic or not
     *
     * @param string $sql
     * @param string $listType
     * @param string $order
     * @param string $queryString
     * @param bool $conditions Array of conditions to SQL (default is FALSE)
     * @param bool $joins Array of table joins to SQL (array(table => array(rightField, leftField)) (Default is FALSE)
     * @param bool $conditionsOneField
     * @param string $table default is 'resource'
     * @param string $subQ Optional SQL subquery for input to COUNT operations - default is FALSE
     * @param bool $QS From QUICKSEARCH or not - default is FALSE
     */
    public function pagingStyle(
        $sql,
        $listType,
        $order,
        $queryString,
        $conditions = FALSE,
        $joins = FALSE,
        $conditionsOneField = FALSE,
        $table = 'resource',
        $subQ = FALSE, 
        $QS = FALSE
    ) {
        if ((GLOBALS::getUserVar('PagingStyle') == 'A') && in_array($order, ['title', 'creator', 'attachments'])) {
            $this->pagingObject = FACTORY_PAGINGALPHA::getInstance();
            if ($this->metadataPaging) {
                $this->pagingObject->metadata = TRUE;
            }
            $this->pagingObject->listType = $listType;
            $this->pagingObject->order = $order;
            $this->pagingObject->queryString = $queryString;
            $this->pagingObject->getPaging($conditions, $joins, $conditionsOneField, $table, $subQ, $QS);
        } else {
            $this->pagingObject = FACTORY_PAGING::getInstance();
            $this->pagingObject->queryString = $queryString;
            $this->pagingObject->getPaging();
        }
    }
    /**
     * Ordering options for select and quicksearch
     *
     * @param string $type
     * @param bool $reorder Default is FALSE
     *
     * @return string
     */
    public function displayOrder($type, $reorder = FALSE)
    {
        if (($type == 'list') && !$this->browse) {
//            if (WIKINDX_FILE_VIEW_LOGGEDON_ONLY && !$this->session->getVar("setup_UserId")) {
                $order = [
                    "creator" => $this->messages->text("list", "creator"),
                    "title" => $this->messages->text("list", "title"),
                    "publisher" => $this->messages->text("list", "publisher"),
                    "year" => $this->messages->text("list", "year"),
                    "timestamp" => $this->messages->text("list", "timestamp"),
                    "maturityIndex" => $this->messages->text("list", "maturity"),
                ];
/*            } else {
                $order = [
                    "creator" => $this->messages->text("list", "creator"),
                    "title" => $this->messages->text("list", "title"),
                    "publisher" => $this->messages->text("list", "publisher"),
                    "year" => $this->messages->text("list", "year"),
                    "timestamp" => $this->messages->text("list", "timestamp"),
                    "popularityIndex" => $this->messages->text("list", "popularity"),
                    "viewsIndex" => $this->messages->text("list", "views"),
                    "downloadsIndex" => $this->messages->text("list", "downloads"),
                    "maturityIndex" => $this->messages->text("list", "maturity"),
                ];
            }
*/        } 
		  else {
            $order = [
                "creator" => $this->messages->text("list", "creator"),
                "title" => $this->messages->text("list", "title"),
                "publisher" => $this->messages->text("list", "publisher"),
                "year" => $this->messages->text("list", "year"),
                "timestamp" => $this->messages->text("list", "timestamp"),
            ];
        }
        if ($type == 'basket') {
            $type = 'list';
        }
        if (!$reorder) {
            $size = '5';
        } else {
            $size = '2';
        }
        if ($selected = $this->session->getVar($type . "_Order")) {
            $pString = \FORM\selectedBoxValue(
                $this->messages->text("list", "order"),
                $type . "_Order",
                $order,
                $selected,
                1
            );
        } else {
            $pString = \FORM\selectFBoxValue(
                $this->messages->text("list", "order"),
                $type . "_Order",
                $order,
                1
            );
        }
        if (!$reorder) {
            $pString .= \HTML\p($this->displayAscDesc($type));
        } else {
            $pString .= BR . $this->displayAscDesc($type);
        }

        return $pString;
    }
    /**
     * Get metadata for this resource when browsing a keyword
     *
     * @param int $resourceId
     *
     * @return array
     */
    private function listMetadata($resourceId)
    {
        $array = [];
        // quotes
        $this->db->formatConditions(['resourcekeywordKeywordId' => $this->metadataKeyword]);
        $this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
        $this->db->formatConditions(['resourcemetadataResourceId' => $resourceId]);
        $this->db->leftJoin('resource_metadata', 'resourcemetadataId', 'resourcekeywordMetadataId');
        $resultset = $this->db->select('resource_keyword', 'resourcemetadataText');
        while ($row = $this->db->fetchRow($resultset)) {
            $array[] = $this->cite->parseCitations(\HTML\nlToHtml($row['resourcemetadataText']), 'htmlNoBib', FALSE);
        }

        return $array;
    }
    /**
     * Get metadata for this resource when selecting or searching metadata
     *
     * @param int $resourceId
     *
     * @return array
     */
    private function listMetadataText($resourceId)
    {
        $array = [];
        if (!empty($this->metadataText)) { // i.e. not called from the word processor so no need for radio buttons on quotes, comments etc.
            $cite = FALSE;
            $cycleArray = $this->metadataText;
        } else {
            $cite = TRUE;
            $cycleArray = $this->metadataTextCite;
        }
        foreach ($cycleArray as $sql) {
            $sql = str_replace('RESID', $this->db->tidyInput($resourceId), $sql);
            $resultset = $this->db->query($sql);
            while ($row = $this->db->fetchRow($resultset)) {
                if ($cite) {
                    $array[] = \FORM\radioButton(FALSE, 'cite', $resourceId . '_' .
                    base64_encode(\HTML\dbToTinyMCE($row['text']))) .
                    '&nbsp;' .
                    $this->resCommon->doHighlight($this->cite->parseCitations(\HTML\nlToHtml($row['text']), 'htmlNoBib', FALSE));
                } else {
                    $array[] = $this->resCommon->doHighlight($this->cite->parseCitations(\HTML\nlToHtml($row['text']), 'htmlNoBib', FALSE));
                }
            }
        }

        return $array;
    }
    /**
     * list only attachments
     *
     * @param string $listType
     */
    private function listAttachments($listType)
    {
        // Are only logged on users allowed to view this file and is this user logged on?
        if (WIKINDX_FILE_VIEW_LOGGEDON_ONLY && !$this->session->getVar("setup_UserId")) {
            $this->displayListInfo($listType, FALSE);
        }
        include_once("core/miscellaneous/ATTACHMENT.php");
        $attachments = new ATTACHMENT();
        $files = [];
        $zip = $this->session->getVar($listType . '_DisplayAttachmentZip') ? TRUE : FALSE;
        $this->db->formatConditionsOneField($this->attachmentHashnames, 'resourceattachmentsId');
        $recordset = $this->db->select('resource_attachments', ['resourceattachmentsFileName', 'resourceattachmentsHashFilename', 
        	'resourceattachmentsId', 'resourceattachmentsResourceId']);
        while ($row = $this->db->fetchRow($recordset)) {
            if ($zip) {
                $files[$row['resourceattachmentsFileName']] = $row['resourceattachmentsHashFilename'];
            } else {
                $files[] = $attachments->makeLink($row, $row['resourceattachmentsResourceId'], TRUE, FALSE);
                $ids[] = \HTML\a($this->icons->getClass("view"), $this->icons->getHTML("view"), "index.php?action=resource_RESOURCEVIEW_CORE" .
                    htmlentities("&id=" . $row['resourceattachmentsResourceId']));
            }
        }

        if (empty($files)) {
            $this->displayListInfo($listType, FALSE);

            return;
        }
        if ($zip) { // zip the files
            if (!$zipfile = FILE\zip($files, WIKINDX_DIR_DATA_ATTACHMENTS))
            {
                $errors = FACTORY_ERRORS::getInstance();
                $badInput = FACTORY_BADINPUT::getInstance();
                $badInput->close($errors->text("file", "write"));
            }
            $link[] = \HTML\a("link", 'ZIP', str_replace(DIRECTORY_SEPARATOR, "/", $zipfile), "_blank");
            GLOBALS::addTplVar('fileList', $link);

            return;
        }
        $this->displayListInfo($listType);
        GLOBALS::addTplVar('fileList', $files);
        GLOBALS::addTplVar('fileListIds', $ids);
    }
    /**
     * Format resource according to bibliographic style when viewing a list, search results or a results prior to inserting a citation link into metadata
     *
     * @param string $listType Type of list to format
     * @param array $resourceList Reference to $resourceList
     * @param array $resources Reference to $resources
     */
    private function formatResources($listType, &$resourceList, &$resources)
    {
        $resultSet = $this->getCreators(array_keys($resources));

        $creators = [];

        while ($cRow = $this->db->fetchRow($resultSet)) {
            $creators[$cRow['resourcecreatorResourceId']][$cRow['resourcecreatorRole']][] = $cRow['creatorId'];
            $array = [
                'surname' => $cRow['surname'],
                'firstname' => $cRow['firstname'],
                'initials' => $cRow['initials'],
                'prefix' => $cRow['prefix'],
                'creatorId' => $cRow['creatorId'],
            ];
            $this->bibStyle->creators[$cRow['creatorId']] = array_map([$this->bibStyle, "removeSlashes"], $array);
        }

        foreach ($this->rows as $rId => $row) {
            if (empty($creators) || !array_key_exists($rId, $creators) || empty($creators[$rId])) {
                for ($index = 1; $index <= 5; $index++) {
                    $row["creator$index"] = ''; // need empty fields for BIBSTYLE
                }
            } else {
                for ($index = 1; $index <= 5; $index++) {
                    if (array_key_exists($index, $creators[$rId])) {
                        $row["creator$index"] = implode(',', $creators[$rId][$index]);
                    } else {
                        $row["creator$index"] = '';
                    }
                }
            }

            $resourceList[$row['resourceId']]['resource'] = $this->bibStyle->process($row, FALSE, FALSE);
        }
    }
    /**
     * Get SQL resultset for creator details before formatting resources
     *
     * @param array $resourceIds
     *
     * @return object SQL resultset
     */
    private function getCreators($resourceIds)
    {
        $this->db->formatConditionsOneField($resourceIds, 'resourcecreatorResourceId');
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->orderBy('resourcecreatorResourceId', TRUE, FALSE);
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourcecreatorRole', TRUE, FALSE);
        $this->db->orderBy('resourcecreatorOrder', TRUE, FALSE);

        return $this->db->select('resource_creator', ['resourcecreatorResourceId', ['creatorSurname' => 'surname'],
            ['creatorFirstname' => 'firstname'], ['creatorInitials' => 'initials'], ['creatorPrefix' => 'prefix'],
            'creatorId', 'resourcecreatorRole', ]);
    }
    /**
     * Create links for viewing, editing deleting etc. resources
     *
     * @param string $listType Type of list to format
     * @param array $resourceList Reference to $resourceList
     * @param array $resources
     */
    private function createLinks($listType, &$resourceList, $resources)
    {
        if ($listType == 'cite') {
            $citeRadioButtonFirst = TRUE;
            foreach ($resourceList as $resourceId => $resourceArray) {
                $resourceList[$resourceId]['links']['checkbox'] = \FORM\radioButton(FALSE, 'cite', $resourceId, $citeRadioButtonFirst);
                $citeRadioButtonFirst = FALSE;
            }
        } else {
            $write = $this->session->getVar("setup_Write");
            $superAdmin = $this->session->getVar("setup_Superadmin");
            $userId = $this->session->getVar("setup_UserId");
            $attachments = $musings = [];
            $edit = FALSE;
// Disabled temporarily for some later dates when statistics can be calculated in the database code.
/*            if ($this->session->getVar("setup_UserId") && ($this->session->getVar("list_Order") == 'popularityIndex')) {
                foreach ($resourceList as $resourceId => $resourceArray) {
                    $resourceList[$resourceId]['popIndex'] = $this->messages->text("misc", "popIndex", $this->stats->getPopularityIndex($resourceId));
                }
            }
*/            // Check if these resources have metadata and display view icons accordingly
            $this->db->formatConditionsOneField(array_keys($resources), 'resourcemetadataResourceId');
            $this->db->formatConditionsOneField(['q', 'p', 'm'], 'resourcemetadataType');
            $resultSet = $this->db->select('resource_metadata', ['resourcemetadataPrivate', 'resourcemetadataAddUserId',
                'resourcemetadataResourceId', ]);
            while ($row = $this->db->fetchRow($resultSet)) {
                if (($row['resourcemetadataPrivate'] == 'N') || ($userId == $row['resourcemetadataAddUserId'])) {
                    $musings[$row['resourcemetadataResourceId']] = TRUE;
                }
            }

            $isHyperlinked = (GLOBALS::getUserVar('ListLink'));

            foreach ($resources as $resourceId => $resourceArray) {
                if ($resourceArray['quotes'] || $resourceArray['paraphrases'] || array_key_exists($resourceId, $musings)) {
                    if (array_key_exists($resourceId, $attachments)) {
                        $view = $this->icons->getHTML("viewmetaAttach");
                    } else {
                        $view = $this->icons->getHTML("viewmeta");
                    }
                } elseif (array_key_exists($resourceId, $attachments)) {
                    $view = $this->icons->getHTML("viewAttach");
                } else {
                    $view = $this->icons->getHTML("view");
                }
                if ($isHyperlinked) {
                    $resourceLink = "index.php?action=resource_RESOURCEVIEW_CORE" . htmlentities("&id=" . $resourceId);
                    $resourceList[$resourceId]['resource'] =
                        \HTML\a('rLink', $resourceList[$resourceId]['resource'], $resourceLink);
                }
                if (($this->pagingObject && $this->session->getVar("setup_Write")) || ($listType != 'front')) {
                    $resourceList[$resourceId]['links']['checkbox'] = \FORM\checkBox(FALSE, "bib_" . $resourceId);
                }

                if ($write && !WIKINDX_ORIGINATOR_EDIT_ONLY) {
                    $resourceList[$resourceId]['links']['edit'] = \HTML\a(
                        $this->icons->getClass("edit"),
                        $this->icons->getHTML("edit"),
                        "index.php?action=resource_RESOURCEFORM_CORE&amp;type=edit" . htmlentities("&id=" . $resourceId)
                    );
                    if (is_array($row) && $row['resourcemiscAddUserIdResource'] == $userId) {
                        $resourceList[$resourceId]['links']['delete'] = \HTML\a(
                            $this->icons->getClass("delete"),
                            $this->icons->getHTML("delete"),
                            "index.php?action=admin_DELETERESOURCE_CORE" . htmlentities('&function=deleteResourceConfirm&navigate=' .
                            $this->navigate . '&resource_id=' . $resourceId)
                        );
                    }
                    $edit = TRUE;
                } elseif ($write && is_array($row) && ($row['resourcemiscAddUserIdResource'] == $userId)) {
                    $resourceList[$resourceId]['links']['edit'] = \HTML\a(
                        $this->icons->getClass("edit"),
                        $this->icons->getHTML("edit"),
                        "index.php?action=resource_RESOURCEFORM_CORE&amp;type=edit" . htmlentities("&id=" . $resourceId)
                    );
                    $resourceList[$resourceId]['links']['delete'] = \HTML\a(
                        $this->icons->getClass("delete"),
                        $this->icons->getHTML("delete"),
                        "index.php?action=admin_DELETERESOURCE_CORE" . htmlentities('&function=deleteResourceConfirm&navigate=' .
                        $this->navigate . '&resource_id=' . $resourceId)
                    );
                    $edit = TRUE;
                }
                if ($superAdmin) {
                    if (!$edit) {
                        $resourceList[$resourceId]['links']['edit'] = \HTML\a(
                            $this->icons->getClass("edit"),
                            $this->icons->getHTML("edit"),
                            "index.php?action=resource_RESOURCEFORM_CORE&amp;type=edit" . htmlentities("&id=" . $resourceId)
                        );
                    }
                    $resourceList[$resourceId]['links']['delete'] = \HTML\a(
                        $this->icons->getClass("delete"),
                        $this->icons->getHTML("delete"),
                        "index.php?action=admin_DELETERESOURCE_CORE" . htmlentities('&function=deleteResourceConfirm&navigate=' .
                        $this->navigate . '&resource_id=' . $resourceId)
                    );
                }
                // display CMS link if required
                // link is actually a JavaScript call
                if (GLOBALS::getUserVar('DisplayCmsLink') && WIKINDX_CMS_ALLOW) {
                    $resourceList[$resourceId]['links']['cms'] = \HTML\a(
                        'cmsLink',
                        "CMS:&nbsp;" . $resourceId,
                        "javascript:coreOpenPopup('index.php?action=cms_CMS_CORE&amp;method=display" . "&amp;id=" . $resourceId . "',
    					90)"
                    );
                }
                // display bibtex link if required
                // link is actually a JavaScript call
                if (GLOBALS::getUserVar('DisplayBibtexLink')) {
                    $resourceList[$resourceId]['links']['bibtex'] = \HTML\a(
                        $this->icons->getClass("bibtex"),
                        $this->icons->getHTML("bibtex"),
                        "javascript:coreOpenPopup('index.php?action=resource_VIEWBIBTEX_CORE&amp;method=display" .
                        "&amp;id=" . $resourceId . "', 90)"
                    );
                }
                // Display a resource
                $resourceList[$resourceId]['links']['view'] = \HTML\a($this->icons->getClass("view"), $view, "index.php?action=resource_RESOURCEVIEW_CORE" .
                    htmlentities("&id=" . $resourceId));
            }

            unset($resources);
        }
    }
    /**
     * Check for user bibliographies
     *
     * @return array (usingBib, bibUserId, bibs)
     */
    private function getUserBib()
    {
        $usingBib = $bibUserId = FALSE;
        $bibs = [];
        $uBibs = $this->commonBib->getUserBibs();
        $gBibs = $this->commonBib->getGroupBibs();
        $bibs = array_merge($uBibs, $gBibs);
        $useBib = $this->session->getVar("mywikindx_Bibliography_use");
        if ($useBib) {
            $this->db->formatConditions(['userbibliographyId' => $useBib]);
            $recordset = $this->db->select('user_bibliography', ['userbibliographyTitle', 'userbibliographyUserId']);
            $row = $this->db->fetchRow($recordset);
            $usingBib = stripslashes($row['userbibliographyTitle']);
            $bibUserId = $row['userbibliographyUserId'];
            if (array_key_exists($useBib, $bibs)) {
                unset($bibs[$useBib]); // Remove the currently used one from the list
            }
        }

        return [$usingBib, $bibUserId, $bibs];
    }
    /**
     * Display list information and userBib, category and keyword select box to add items to
     *
     * @param string $listType
     * @param bool $resourcesExist
     */
    private function displayListInfo($listType, $resourcesExist = TRUE)
    {
        list($usingBib, $bibUserId, $bibs) = $this->getUserBib();
        if ($usingBib) {
            $linksInfo['info'] = $this->pagingObject->linksInfo($usingBib);
        } else {
            $linksInfo['info'] = $this->pagingObject->linksInfo();
        }
        $linksInfo['params'] = $this->listParams($listType);
        if ($this->ideasFound) {
            if ($listType == 'search') {
                $linksInfo['info'] .= '&nbsp;' . \HTML\a('link', $this->messages->text('search', 'ideasFound'), "index.php?action=list_SEARCH_CORE" .
                    htmlentities("&method=reprocess&type=displayIdeas"));
            } else {
                $linksInfo['info'] .= '&nbsp;' . \HTML\a('link', $this->messages->text('search', 'ideasFound'), "index.php?action=ideas_IDEAS_CORE" .
                    htmlentities("&method=" . 'keywordIdeaList') . htmlentities("&resourcekeywordKeywordId=" . $this->metadataKeyword));
            }
        }
        if (!$resourcesExist) {
            GLOBALS::setTplVar('resourceListInfo', $linksInfo);
            unset($linksInfo);

            return;
        }
        if (!$this->session->getVar($listType . '_DisplayAttachment')) {
            $linksInfo['selectformheader'] = \FORM\formHeaderName('list_LISTADDTO_CORE', 'formSortingAddingListInfo', FALSE);
            $linksInfo['selectformfooter'] = \FORM\formEnd();
            $linksInfo['select'] = $this->createAddToBox($bibUserId, $bibs, $listType);
            if ($listType == 'list') {
                if ($this->session->getVar("list_SomeResources")) {
                    $formHeader = 'list_LISTSOMERESOURCES_CORE';
                } else {
                    $formHeader = 'list_LISTRESOURCES_CORE';
                }
                $linksInfo['reorder'] =
                    \FORM\hidden("method", "reorder") . $this->displayOrder($listType, TRUE) .
                    BR . \FORM\formSubmit($this->messages->text("submit", "Proceed"), 'Submit', "onclick=\"document.forms['formSortingAddingListInfo'].elements['action'].value='$formHeader'\"");
            } elseif ($listType == 'basket') {
                $linksInfo['reorder'] =
                    \FORM\hidden("method", "reorder") . $this->displayOrder('basket', TRUE) .
                    BR . \FORM\formSubmit($this->messages->text("submit", "Proceed"), 'Submit', "onclick=\"document.forms['formSortingAddingListInfo'].elements['action'].value='basket_BASKET_CORE'\"");
            } elseif ($listType == 'search') {
                if ($this->quickSearch) {
                    $linksInfo['reorder'] =
                        \FORM\hidden("method", "reprocess") . $this->displayOrder($listType, TRUE) .
                        BR . \FORM\formSubmit($this->messages->text("submit", "Proceed"), 'Submit', "onclick=\"document.forms['formSortingAddingListInfo'].elements['action'].value='list_QUICKSEARCH_CORE'\"");
                } else {
                    $linksInfo['reorder'] =
                        \FORM\hidden("method", "reprocess") . $this->displayOrder($listType, TRUE) .
                        BR . \FORM\formSubmit($this->messages->text("submit", "Proceed"), 'Submit', "onclick=\"document.forms['formSortingAddingListInfo'].elements['action'].value='list_SEARCH_CORE'\"");
                }
            }
        }
        // display CMS link if required
        // link is actually a JavaScript call
        if (GLOBALS::getUserVar('DisplayCmsLink') && WIKINDX_CMS_ALLOW && WIKINDX_CMS_SQL) {
            $linksInfo['cms'] = \HTML\a(
                'cmsLink',
                "CMS",
                "javascript:coreOpenPopup('index.php?action=cms_CMS_CORE&amp;method=displayList" . "', 90)"
            );
        }
        GLOBALS::setTplVar('resourceListInfo', $linksInfo);
        unset($linksInfo);
    }
    /**
     * Create select box allowing users to add to categories, keywords etc.
     *
     * @param int $bibUserId
     * @param array $bibs
     * @param string $listType
     *
     * @return false|string
     */
    private function createAddToBox($bibUserId, $bibs, $listType)
    {
        if ($this->session->getVar("setup_Write")) {
            $array[1] = $this->messages->text("resources", "organize");
        }
        if (!empty($bibs)) {
            $array[0] = $this->messages->text("resources", "addToBib");
        }
        if ($this->session->getVar("setup_UserId") && ($this->session->getVar("setup_UserId") == $bibUserId)) {
            $array[3] = $this->messages->text('resources', 'deleteFromBib');
        } elseif ($this->session->getVar("resourceSelectedTo") == '3') { // previous operation was 'deleteFromBib'
            $this->session->delVar("resourceSelectedTo");
        }
        if ($listType == 'basket') {
            $array[8] = $this->messages->text('resources', 'basketRemove');
        } else {
            $array[7] = $this->messages->text('resources', 'basketAdd');
        }
        if ($this->session->getVar("setup_Superadmin")) {
            $array[4] = $this->messages->text('resources', 'deleteResource');
        }
        $array[9] = $this->messages->text('resources', 'exportCoins1');
        if (!isset($array)) {
            return FALSE;
        }
        $t = \HTML\tableStart('right');
        $t .= \HTML\trStart('right');
        $sessVar = $this->session->getVar("resourceSelectedTo");
        /*
        if (GLOBALS::getUserVar('PagingStyle') == 'A')
            $radios = $this->messages->text("resources", "selectCheck") . '&nbsp;' . \FORM\radioButton(FALSE, 'selectWhat', 'checked') .
            BR .
            $this->messages->text("resources", "selectDisplay") . '&nbsp;' . \FORM\radioButton(FALSE, 'selectWhat', 'display', TRUE);
        else
        */
        $radios = $this->messages->text("resources", "selectCheck") . '&nbsp;' . \FORM\radioButton(FALSE, 'selectWhat', 'checked', TRUE) .
            BR .
            $this->messages->text("resources", "selectDisplay") . '&nbsp;' . \FORM\radioButton(FALSE, 'selectWhat', 'display') .
            BR .
            $this->messages->text("resources", "selectAll") . '&nbsp;' . \FORM\radioButton(FALSE, 'selectWhat', 'all');

        \FORM\checkbox(FALSE, "selectWhat", FALSE);
        if ($sessVar !== FALSE) {
            $select = \FORM\selectedBoxValue(FALSE, "resourceSelectedTo", $array, $sessVar, 1);
        } else {
            $select = \FORM\selectFBoxValue(FALSE, "resourceSelectedTo", $array, 1);
        }
        $t .= \HTML\td($select, FALSE, 'right');
        $t .= \HTML\trEnd();
        $t .= \HTML\trStart('right');
        $tr = \HTML\tableStart('right');
        $tr .= \HTML\trStart('right');
        $tr .= \HTML\td($radios, FALSE, 'right');
        $tr .= \HTML\td(\FORM\formSubmit($this->messages->text("submit", "Proceed"), 'Submit', "onclick=\"document.forms['formSortingAddingListInfo'].elements['action'].value='list_LISTADDTO_CORE';document.forms['formSortingAddingListInfo'].elements['method'].value='init';\""), 'right bottom width1percent');
        $tr .= \HTML\trEnd();
        $tr .= \HTML\tableEnd();
        $t .= \HTML\td($tr);
        $t .= \HTML\trEnd();
        $t .= \HTML\tableEnd();

        return $t;
    }
    /**
     * Display some information about the search/select/list parameters
     *
     * @param string $listType
     *
     * @return false|string
     */
    private function listParams($listType)
    {
        $strings = [];
        // Bookmarked multi view?
        if ($this->session->getVar("bookmark_MultiView")) {
            $strings = unserialize(base64_decode($this->session->getVar("sql_ListParams")));
            if (!is_array($strings) && $strings) { // From advanced search
                return \HTML\aBrowse(
                    'green',
                    '1em',
                    $this->messages->text('listParams', 'listParams'),
                    '#',
                    "",
                    \HTML\dbToHtmlPopupTidy(\HTML\nlToHtml($strings))
                ) . BR;
            }
            if (empty($strings)) {
                return FALSE;
            }
            $this->session->delVar("bookmark_MultiView");

            return $this->messages->text('listParams', 'listParams') . BR . implode(BR, $strings);
        }
        if (array_key_exists('statistics', $this->vars) && ($this->vars['statistics'] == 'Type')) {
            $strings[] = $this->messages->text('listParams', 'type') . ':&nbsp;&nbsp;' . $this->vars['id'];
        } elseif ($id = $this->session->getVar($listType . "_Type")) {
            $ids = UTF8::mb_explode(',', $id);
            if (count($ids) > 1) {
                $strings[] = $this->messages->text('listParams', 'type') . ':&nbsp;&nbsp;' .
                    \HTML\em($this->messages->text('listParams', 'listParamMultiple'));
            } else {
                $strings[] = $this->messages->text('listParams', 'type') . ':&nbsp;&nbsp;' . $this->messages->text('resourceType', $id);
            }
        }
        if ($listType == 'select') {
            if ($id = $this->session->getVar($listType . '_Tag')) {
                $ids = UTF8::mb_explode(',', $id);
                if (count($ids) > 1) {
                    $strings[] = $this->messages->text('listParams', 'tag') . ':&nbsp;&nbsp;' .
                        \HTML\em($this->messages->text('listParams', 'listParamMultiple'));
                } else {
                    $this->db->formatConditions(['tagId' => $id]);
                    $strings[] = $this->messages->text('listParams', 'tag') . ':&nbsp;&nbsp;' .
                        \HTML\nlToHtml($this->db->selectFirstField('tag', 'tagTag'));
                }
            }
            if ($id = $this->session->getVar($listType . "_attachment")) {
                $strings[] = $this->messages->text('listParams', 'attachment');
            }
        }
        if (($listType == 'listCategory') || ($id = $this->session->getVar($listType . '_Category'))) {
            if ($listType == 'listCategory') {
                $id = array_key_exists("id", $this->vars) ?
                    $this->vars["id"] : $this->session->getVar("list_Ids");
            }
            $cats = UTF8::mb_explode(',', $id);
            if (count($cats) > 1) {
                $strings[] = $this->messages->text('listParams', 'category') . ':&nbsp;&nbsp;' .
                    \HTML\em($this->messages->text('listParams', 'listParamMultiple'));
            } else {
                $this->db->formatConditions(['categoryId' => $id]);
                $strings[] = $this->messages->text('listParams', 'category') . ':&nbsp;&nbsp;' .
                    \HTML\nlToHtml($this->db->selectFirstField('category', 'categoryCategory'));
            }
        }
        if (($listType == 'listSubcategory') || ($id = $this->session->getVar($listType . '_Subcategory'))) {
            if ($listType == 'listSubcategory') {
                $id = array_key_exists("id", $this->vars) ?
                    $this->vars["id"] : $this->session->getVar("list_Ids");
            }
            $cats = UTF8::mb_explode(',', $id);
            if (count($cats) > 1) {
                $strings[] = $this->messages->text('listParams', 'subcategory') . ':&nbsp;&nbsp;' .
                    \HTML\em($this->messages->text('listParams', 'listParamMultiple'));
            } else {
                $this->db->formatConditions(['subcategoryId' => $id]);
                $strings[] = $this->messages->text('listParams', 'subcategory') . ':&nbsp;&nbsp;' .
                    \HTML\nlToHtml($this->db->selectFirstField('subcategory', 'subcategorySubcategory'));
            }
        }
        if (($listType == 'listUserTag') || ($id = $this->session->getVar($listType . '_UserTag'))) {
            if ($listType == 'listUserTag') {
                $id = array_key_exists("id", $this->vars) ?
                    $this->vars["id"] : $this->session->getVar("list_Ids");
            }
            $cats = UTF8::mb_explode(',', $id);
            if (count($cats) > 1) {
                $strings[] = $this->messages->text('listParams', 'userTag') . ':&nbsp;&nbsp;' .
                    \HTML\em($this->messages->text('listParams', 'listParamMultiple'));
            } else {
                $this->db->formatConditions(['usertagsId' => $id]);
                $strings[] = $this->messages->text('listParams', 'userTag') . ':&nbsp;&nbsp;' .
                    \HTML\nlToHtml($this->db->selectFirstField('user_tags', 'usertagsTag'));
            }
        }
        if (($listType == 'listCollection') || ($id = $this->session->getVar($listType . '_Collection'))) {
            if ($listType == 'listCollection') {
                $id = array_key_exists("id", $this->vars) ?
                    $this->vars["id"] : $this->session->getVar("list_Ids");
            }
            $cats = UTF8::mb_explode(',', $id);
            if (count($cats) > 1) {
                $strings[] = $this->messages->text('listParams', 'collection') . ':&nbsp;&nbsp;' .
                    \HTML\em($this->messages->text('listParams', 'listParamMultiple'));
            } else {
                $this->db->formatConditions(['collectionId' => $id]);
                $strings[] = $this->messages->text('listParams', 'collection') . ':&nbsp;&nbsp;' .
                    \HTML\nlToHtml($this->db->selectFirstField('collection', 'collectionTitle'));
            }
        }
        if (($listType == 'listPublisher') || ($id = $this->session->getVar($listType . '_Publisher'))) {
            if ($listType == 'listPublisher') {
                $id = array_key_exists("id", $this->vars) ?
                    $this->vars["id"] : $this->session->getVar("list_Ids");
            }
            $cats = UTF8::mb_explode(',', $id);
            if (count($cats) > 1) {
                $strings[] = $this->messages->text('listParams', 'publisher') . ':&nbsp;&nbsp;' .
                    \HTML\em($this->messages->text('listParams', 'listParamMultiple'));
            } else {
                $this->db->formatConditions(['publisherId' => $id]);
                $recordset = $this->db->select('publisher', ['publisherName', 'publisherLocation']);
                $row = $this->db->fetchRow($recordset);
                if ($row['publisherLocation']) {
                    $loc = ' (' . stripslashes($row['publisherLocation']) . ')';
                } else {
                    $loc = FALSE;
                }
                $publisher = stripslashes($row['publisherName']) . $loc;
                $strings[] = $this->messages->text('listParams', 'publisher') . ':&nbsp;&nbsp;' .
                    \HTML\nlToHtml($publisher);
            }
        }
        if (($listType == 'listKeyword') || ($id = $this->session->getVar($listType . '_Keyword'))) {
            if ($listType == 'listKeyword') {
                $id = array_key_exists("id", $this->vars) ?
                    $this->vars["id"] : $this->session->getVar("list_Ids");
            }
            $ids = UTF8::mb_explode(',', $id);
            if (count($ids) > 1) {
                $strings[] = $this->messages->text('listParams', 'keyword') . ':&nbsp;&nbsp;' .
                    \HTML\em($this->messages->text('listParams', 'listParamMultiple'));
            } else {
                $this->db->formatConditions(['keywordId' => $id]);
                $strings[] = $this->messages->text('listParams', 'keyword') . ':&nbsp;&nbsp;' .
                    \HTML\nlToHtml($this->db->selectFirstField('keyword', 'keywordKeyword'));
            }
        }
        if ($id = $this->session->getVar($listType . '_Language')) {
            $ids = UTF8::mb_explode(',', $id);
            if (count($ids) > 1) {
                $strings[] = $this->messages->text('listParams', 'language') . ':&nbsp;&nbsp;' .
                    \HTML\em($this->messages->text('listParams', 'listParamMultiple'));
            } else {
                $this->db->formatConditions(['languageId' => $id]);
                $recordset = $this->db->select('language', 'languageLanguage');
                $strings[] = $this->messages->text('listParams', 'language') . ':&nbsp;&nbsp;' .
                    \HTML\nlToHtml($this->db->selectFirstField('language', 'languageLanguage'));
            }
        }
        if (($listType == 'listCreator') || ($listType == 'select')) {
            if ($id = $this->session->getVar($listType . '_BibId')) {
                $this->db->formatConditions(['userbibliographyId' => $id]);
                $strings[] = $this->messages->text('listParams', 'notInUserBib') . ':&nbsp;&nbsp;' .
                    \HTML\nlToHtml($this->db->selectFirstField('user_bibliography', 'userbibliographyTitle'));
            }
            if (($listType == 'listCreator') || ($id = $this->session->getVar($listType . '_Creator'))) {
                if ($listType == 'listCreator') {
                    $id = array_key_exists("id", $this->vars) ?
                    $this->vars["id"] : $this->session->getVar("list_Ids");
                }
                $ids = UTF8::mb_explode(',', $id);
                if (count($ids) > 1) {
                    $strings[] = $this->messages->text('listParams', 'creator') . ':&nbsp;&nbsp;' .
                        \HTML\em($this->messages->text('listParams', 'listParamMultiple'));
                } else {
                    $this->db->formatConditions(['creatorId' => $id]);
                    $recordset = $this->db->select('creator', ['creatorPrefix', 'creatorSurname']);
                    $row = $this->db->fetchRow($recordset);
                    $name = $row['creatorPrefix'] ? $row['creatorPrefix'] . ' ' .
                        $row['creatorSurname'] : $row['creatorSurname'];
                    $strings[] = $this->messages->text('listParams', 'creator') . ':&nbsp;&nbsp;' .
                        \HTML\nlToHtml($name);
                }
            }
        }
        if ($listType == 'search') {
            if ($param = $this->session->getVar("advancedSearch_listParams")) {
                return \HTML\aBrowse(
                    'green',
                    '1em',
                    $this->messages->text('listParams', 'listParams'),
                    '#',
                    "",
                    \HTML\dbToHtmlPopupTidy(\HTML\nlToHtml($param))
                ) . BR;
            } else {
                if ($id = $this->session->getVar($listType . '_Field')) {
                    $ids = UTF8::mb_explode(',', $id);
                    if (count($ids) > 1) {
                        $strings[] = $this->messages->text('listParams', 'field') . ':&nbsp;&nbsp;' .
                            \HTML\em($this->messages->text('listParams', 'listParamMultiple'));
                    } else {
                        if (mb_strpos($id, 'Custom_') === 0) {
                            $customField = UTF8::mb_explode('_', $id);
                            $this->db->formatConditions(['customId' => $customField[2]]);
                            $id = $this->db->selectFirstField('custom', 'customLabel');
                        } else {
                            $id = $this->messages->text("search", $id);
                        }
                        $strings[] = $this->messages->text('listParams', 'field') . ':&nbsp;&nbsp;' . $id;
                    }
                }
                if ($id = $this->session->getVar($listType . '_Word')) {
                    if ($this->session->getVar($listType . '_Partial') == 'on') {
                        $id .= "&nbsp;(" . $this->messages->text('listParams', 'partial') . ")";
                    }
                    $strings[] = $this->messages->text('listParams', 'word') . ':&nbsp;&nbsp;' . \HTML\nlToHtml($id);
                }
            }
        }
        if ($listType == 'list') {
            if (array_key_exists('id', $this->vars)) {
                $id = $this->vars['id'];
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'processAdd')) {
                $id = $this->vars['list_AddedBy'];
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'processEdit')) {
                $id = $this->vars['list_EditedBy'];
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'processGeneral')) {
                $id = FALSE;
            } elseif (array_key_exists('department', $this->vars)) {
                $id = base64_decode($this->vars['department']);
            } elseif (array_key_exists('institution', $this->vars)) {
                $id = base64_decode($this->vars['institution']);
            }
            if (!$id) {
                $strings[] = $this->messages->text('listParams', 'listAll');
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'typeProcess')) {
                $strings[] = $this->messages->text('listParams', 'type') . ':&nbsp;&nbsp;' . $this->messages->text('resourceType', $id);
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'creatorProcess')) {
                if (array_key_exists('department', $this->vars)) {
                    $strings[] = $this->messages->text('listParams', 'department') . ':&nbsp;&nbsp;' . $id;
                } elseif (array_key_exists('institution', $this->vars)) {
                    $strings[] = $this->messages->text('listParams', 'institution') . ':&nbsp;&nbsp;' . $id;
                } else {
                    $this->db->formatConditions(['creatorId' => $id]);
                    $id = $this->db->selectFirstField('creator', 'creatorSurname');
                    $strings[] = $this->messages->text('listParams', 'creator') . ':&nbsp;&nbsp;' . \HTML\nlToHtml($id);
                }
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'citeProcessCreator')) {
                $strings[] = $this->messages->text('listParams', 'cited');
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'collectionProcess')) {
                $this->db->formatConditions(['collectionId' => $id]);
                $id = $this->db->selectFirstField('collection', 'collectionTitle');
                $strings[] = $this->messages->text('listParams', 'collection') . ':&nbsp;&nbsp;' . \HTML\nlToHtml($id);
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'publisherProcess')) {
                $this->db->formatConditions(['publisherId' => $id]);
                $row = $this->db->selectFirstRow('publisher', ['publisherName', 'publisherLocation']);
                if ($row['publisherLocation']) {
                    $id = $row['publisherName'] . '(' . $row['publisherLocation'] . ')';
                } else {
                    $id = $row['publisherName'];
                }
                $strings[] = $this->messages->text('listParams', 'publisher') . ':&nbsp;&nbsp;' . \HTML\nlToHtml($id);
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'yearProcess')) {
                $strings[] = $this->messages->text('listParams', 'year') . ':&nbsp;&nbsp;' . base64_decode($id);
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'keywordProcess')) {
                $this->db->formatConditions(['keywordId' => $id]);
                $id = $this->db->selectFirstField('keyword', 'keywordKeyword');
                $strings[] = $this->messages->text('listParams', 'keyword') . ':&nbsp;&nbsp;' . \HTML\nlToHtml($id);
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'categoryProcess')) {
                $this->db->formatConditions(['categoryId' => $id]);
                $id = $this->db->selectFirstField('category', 'categoryCategory');
                $strings[] = $this->messages->text('listParams', 'category') . ':&nbsp;&nbsp;' . \HTML\nlToHtml($id);
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'subcategoryProcess')) {
                $this->db->formatConditions(['subcategoryId' => $id]);
                $id = $this->db->selectFirstField('subcategory', 'subcategorySubcategory');
                $strings[] = $this->messages->text('listParams', 'subcategory') . ':&nbsp;&nbsp;' . \HTML\nlToHtml($id);
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'languageProcess')) {
                $this->db->formatConditions(['languageId' => $id]);
                $id = $this->db->selectFirstField('language', 'languageLanguage');
                $strings[] = $this->messages->text('listParams', 'language') . ':&nbsp;&nbsp;' . \HTML\nlToHtml($id);
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'bibliographyProcess')) {
                $this->db->formatConditions(['userbibliographyId' => $id]);
                $id = $this->db->selectFirstField('user_bibliography', 'userbibliographyTitle');
                $strings[] = $this->messages->text('listParams', 'bibliography') . ':&nbsp;&nbsp;' . \HTML\nlToHtml($id);
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'processAdd')) {
                $this->db->formatConditions(['usersId' => $id]);
                $row = $this->db->selectFirstRow('users', ['usersFullname', 'usersUsername']);
                $id = $row['usersFullname'] ? $row['usersFullname'] : $row['usersUsername'];
                $strings[] = $this->messages->text('listParams', 'addedBy', \HTML\nlToHtml($id));
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'processEdit')) {
                $this->db->formatConditions(['usersId' => $id]);
                $row = $this->db->selectFirstRow('users', ['usersFullname', 'usersUsername']);
                $id = $row['usersFullname'] ? $row['usersFullname'] : $row['usersUsername'];
                $strings[] = $this->messages->text('listParams', 'editedBy', \HTML\nlToHtml($id));
            } elseif (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'usertagProcess')) {
                $this->db->formatConditions(['usertagsId' => $id]);
                $id = $this->db->selectFirstField('user_tags', 'usertagsTag');
                $strings[] = $this->messages->text('listParams', 'userTag') . ':&nbsp;&nbsp;' . \HTML\nlToHtml($id);
            }
        }
        if (empty($strings)) {
            $this->session->delVar("sql_ListParams");

            return FALSE;
        }
        $this->session->setVar("sql_ListParams", base64_encode(serialize($strings)));

        return $this->messages->text('listParams', 'listParams') . BR . implode(BR, $strings);
    }
}
