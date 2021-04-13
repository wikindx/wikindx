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
 * RESOURCEVIEW class
 *
 * View a single resource.
 */
class RESOURCEVIEW
{
    private $db;
    private $vars;
    private $icons;
    private $errors;
    private $success;
    private $messages;
    private $coins;
    private $gs;
    private $bibStyle;
    private $stats;
    private $session;
    private $user;
    private $commonBib;
    private $badInput;
    private $common;
    private $abstract;
    private $note;
    private $userId;
    private $nextDelete = FALSE;
    private $custom;
    private $allowEdit = FALSE;
    private $multiUser = FALSE;
    private $execNP = TRUE;
    private $startNP = FALSE;
    private $browserTabID = FALSE;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->coins = FACTORY_EXPORTCOINS::getInstance();
        $this->gs = FACTORY_EXPORTGOOGLESCHOLAR::getInstance();
        $this->bibStyle = FACTORY_BIBSTYLE::getInstance();
        $this->stats = FACTORY_STATISTICS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->common = FACTORY_RESOURCECOMMON::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "browse", "BROWSECOMMON.php"]));
        $this->commonBib = new BROWSECOMMON();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "RESOURCEABSTRACT.php"]));
        $this->abstract = new RESOURCEABSTRACT();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "RESOURCENOTE.php"]));
        $this->note = new RESOURCENOTE();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "RESOURCEMETA.php"]));
        $this->meta = new RESOURCEMETA();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "RESOURCECUSTOM.php"]));
        $this->custom = new RESOURCECUSTOM();
        $this->browserTabID = GLOBALS::getBrowserTabID();
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('resource'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "resources"));
    }
    /**
     * init
     *
     * @param mixed $id
     * @param mixed $message
     */
    public function init($id = FALSE, $message = FALSE)
    {
        if ($id === FALSE)
        {
            if (array_key_exists('id', $this->vars))
            {
                $id = $this->vars['id'];
            }
        }
        if ($id)
        {
            // Sanitize the id
            $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT); // NB, $id is now a string
            $this->vars['id'] = $id;
        }
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        } elseif (array_key_exists('messages', $this->vars) && $this->vars['messages']) {
            $message = join('', \TEMPSTORAGE\fetchOne($this->db, $this->vars['messages'], 'resourceMessages'));
        }
        $qs = $this->session->getArray('QueryStrings');
        if (empty($qs) || (mb_strpos($qs[0], 'RESOURCEFORM_CORE') !== FALSE))
        {
            $this->execNP = FALSE; // don't show next/previous links as this might be a new resource or from an RSS feed
        }
        if (!array_key_exists('id', $this->vars) || !$this->vars['id'])
        {
            if ($querySession = $this->session->getVar("sql_ListStmt"))
            { // Numeric paging (see SQLSTATEMENTS.php)
                if (array_key_exists('np', $this->vars) && ($this->vars['np'] == 'forward'))
                {
                    // check we're not reloading
                    if ($_SERVER['REQUEST_URI'] == $qs[0])
                    {
                        list($this->startNP, $this->vars['id']) = $this->setPreviousNext($querySession, 'forward', TRUE);
                    }
                    else
                    {
                        list($this->startNP, $this->vars['id']) = $this->setPreviousNext($querySession, 'forward');
                    }
                }
                elseif (array_key_exists('np', $this->vars) && ($this->vars['np'] == 'backward'))
                {
                    // check we're not reloading
                    if ($_SERVER['REQUEST_URI'] == $qs[0])
                    {
                        list($this->startNP, $this->vars['id']) = $this->setPreviousNext($querySession, 'backward', TRUE);
                    }
                    else
                    {
                        list($this->startNP, $this->vars['id']) = $this->setPreviousNext($querySession, 'backward');
                    }
                }
                else
                {
                    $this->badInput->close($this->errors->text("inputError", "missing"));
                }
            }
            else
            {
                $this->badInput->close($this->errors->text("inputError", "missing"));
            }
        }
        $this->session->setVar("sql_LastSolo", $this->vars['id']);
        $this->userId = $this->session->getVar("setup_UserId");
        $this->common->setHighlightPatterns();
        $this->updateAccesses();
        $this->displayResource($message);
        \TEMPSTORAGE\store($this->db, $this->browserTabID, ['sql_LastSolo' => $this->vars['id']]);
    }
    /**
     * Display popup for all resource's bibliographic details
     *
     * @param mixed $row
     *
     * @return string
     */
    public function viewDetails($row)
    {
        $resourceMap = FACTORY_RESOURCEMAP::getInstance($row['resourceType']);
        $typeMaps = $resourceMap->getTypeMap();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "bibcitation", "STYLEMAP.php"]));
        $styleMap = new STYLEMAP();
        // Grab all creator IDs for this resource
        $creators = $tempArray = [];
        $this->db->formatConditions(['resourcecreatorResourceId' => $row['resourceId']]);
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourcecreatorRole', TRUE, FALSE);
        $this->db->orderBy('resourcecreatorOrder', TRUE, FALSE);
        $resultSet = $this->db->select('resource_creator', ['resourcecreatorCreatorId', 'resourcecreatorRole']);
        while ($cRow = $this->db->fetchRow($resultSet))
        {
            $creators[$cRow['resourcecreatorRole']][] = $cRow['resourcecreatorCreatorId'];
        }
        if (!empty($creators))
        {
            $tempArray = $creators;
            unset($creators);
            foreach ($tempArray as $cRole => $array)
            {
                if (empty($array))
                {
                    $creators = [];

                    break;
                }
                $roleArray = [];
                foreach ($array as $cId)
                {
                    $cArray = [];
                    $this->db->formatConditions(['creatorId' => $cId]);
                    $resultset = $this->db->select('creator', ['creatorPrefix', 'creatorSurname', 'creatorFirstname', 'creatorInitials']);
                    $cRow = $this->db->fetchRow($resultset);
                    if (is_array($cRow))
                    {
                        if ($cRow['creatorFirstname'])
                        {
                            $cArray[] = $cRow['creatorFirstname'];
                        }
                        if ($cRow['creatorInitials'])
                        {
                            $cArray[] = $cRow['creatorInitials'];
                        }
                        if ($cRow['creatorPrefix'])
                        {
                            $cArray[] = $cRow['creatorPrefix'];
                        }
                        if ($cRow['creatorSurname'])
                        {
                            $cArray[] = $cRow['creatorSurname'];
                        }
                        if (count($cArray) > 0)
                        {
                            $roleArray[] = implode(' ', $cArray);
                        }
                    }
                }
                if (count($roleArray) > 0)
                {
                    $creators[$styleMap->{$row['resourceType']}['creator' . $cRole]] = implode(', ', $roleArray);
                }
            }
        }
        $styleMap->{$row['resourceType']}['resourceSubtitle'] = 'subTitle';
        foreach ($resourceMap->getTables($row['resourceType']) as $table)
        {
            foreach ($resourceMap->getOptional() as $optional)
            {
                if (!array_key_exists($optional, $typeMaps[$row['resourceType']]['optional']))
                {
                    continue;
                }
                if (array_key_exists($table, $typeMaps[$row['resourceType']]['optional'][$optional]))
                {
                    foreach ($typeMaps[$row['resourceType']]['optional'][$optional][$table] as $key => $value)
                    {
                        $rowKey = $table . $key;
                        if (!array_key_exists($rowKey, $styleMap->{$row['resourceType']}))
                        {
                            $styleMap->{$row['resourceType']}[$rowKey] = $value;
                        }
                    }
                }
            }
        }
        $pString = '';
        if (!empty($creators))
        {
            $cArray = [];
            foreach ($creators as $key => $value)
            {
                $cArray[] = $this->messages->text('creators', $key) . ":  $value";
            }
            $pString .= implode(CR . LF, $cArray) . CR . LF;
        }
        if (array_key_exists('resourceyearYear1', $row) && array_key_exists('resourceyearYear2', $row) && $row['resourceyearYear2']
            &&
            ($row['resourceType'] != 'web_article') && ($row['resourceType'] != 'web_encyclopedia')
             && ($row['resourceType'] != 'web_encyclopedia_article') && ($row['resourceType'] != 'web_site'))
        {
            $temp = $row['resourceyearYear1'];
            $row['resourceyearYear1'] = $row['resourceyearYear2'];
            $row['resourceyearYear2'] = $temp;
        }
        foreach ($row as $key => $value)
        {
            if (($key == 'resourceTitle') && array_key_exists('resourceNoSort', $row))
            {
                $value = $row['resourceNoSort'] . ' ' . $value;
            }
            elseif (($key == 'resourceTransTitle') && array_key_exists('resourceTransNoSort', $row) && $value)
            {
                if ($row['resourceTransNoSort'])
                {
                    $value = $row['resourceTransNoSort'] . ' ' . $value;
                }
                $rArray[] = $this->messages->text('resources', 'transTitle') . ":  $value";

                continue;
            }
            elseif (($key == 'resourceTransSubtitle') && $value)
            {
                $rArray[] = $this->messages->text('resources', 'transSubtitle') . ":  $value";

                continue;
            }
            if (array_key_exists($key, $styleMap->{$row['resourceType']}) && $value)
            {
                $fieldId = $styleMap->{$row['resourceType']}[$key];
                $fieldRefNamelist = [
                    "resourceIsbn" => "isbn",
                    "volumeNumber" => "bookVolumeNumber",
                    "volumePublicationYear" => "volumeYear",
                    "originalPublicationYear" => "publicationYear",
                ];
                if (array_key_exists($fieldId, $fieldRefNamelist))
                {
                    $msgkey = $fieldRefNamelist[$fieldId];
                }
                else
                {
                    $msgkey = $fieldId;
                }
                $fieldName = $this->messages->text('resources', $msgkey);
                $rArray[] = $fieldName . ":  $value";
            }
        }
        $pString .= implode(CR . LF, $rArray);

        return \HTML\dbToHtmlPopupTidy($pString);
    }
    /**
     * Display popup of attachment description
     */
    public function readMe()
    {
        $this->db->formatConditions(['resourceattachmentsId' => $this->vars['id']]);
        $pString = \HTML\nlToHtml($this->db->fetchOne($this->db->select('resource_attachments', 'resourceattachmentsDescription')));
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
    /**
     * Select a random resource ID for viewing
     */
    public function random()
    {
        $this->db->limit(1, 1);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource', 'resourceId');
        if (!$this->db->numRows($resultset))
        {
            $this->badInput->close($this->messages->text("misc", "noResources"));
        }
        $this->vars['id'] = $this->db->fetchOne($resultset);
        $this->vars['method'] = 'random';
        $this->displayResource();
    }
    /**
     * Display resource from database
     *
     * @param false|string $message
     */
    private function displayResource($message = FALSE)
    {
        $res = FACTORY_RESOURCECOMMON::getInstance();
        $resultset = $res->getResource($this->vars['id']);
        if (!$this->db->numRows($resultset))
        {
            $this->badInput->close($this->messages->text("resources", "noResult"));
        }
        $row = $this->db->fetchRow($resultset);
        $lastmodified = date('r', strtotime($row['resourcetimestampTimestamp']));
        @header("Last-Modified: $lastmodified");
        $this->multiUser = WIKINDX_MULTIUSER;
        if ((WIKINDX_QUARANTINE) && ($row['resourcemiscQuarantine'] == 'Y'))
        {
            if (!$this->session->getVar("setup_Superadmin") && ($this->session->getVar("setup_UserId") != $row['resourcemiscAddUserIdResource']))
            {
                $this->badInput->close($this->errors->text("warning", "quarantine"));
            }
            $resourceSingle['quarantine'] = $this->icons->getHTML("quarantine");
        }
        $resourceSingle['message'] = $message;
        if ($this->multiUser)
        {
            list($resourceSingle['userAdd'], $resourceSingle['userEdit']) = $this->user->displayUserAddEdit($row, TRUE);
            $resourceSingle['timestampAdd'] = \LOCALES\dateFormatFromString($row['resourcetimestampTimestampAdd']);
            if (
                $row['resourcetimestampTimestamp'] &&
                $resourceSingle['userEdit'] &&
                ($row['resourcetimestampTimestampAdd'] != $row['resourcetimestampTimestamp'])
            ){
                $resourceSingle['timestampEdit'] = \LOCALES\dateFormatFromString($row['resourcetimestampTimestamp']);
            }
            $month = date('Ym');
            $this->db->formatConditions(['statisticsresourceviewsResourceId' => $this->vars['id']]);
            $this->db->formatConditions(['statisticsresourceviewsMonth' => $month]);
            if (!$views = $this->db->selectFirstField('statistics_resource_views', ['statisticsresourceviewsCount']))
            {
                $views = 1; // i.e. this view – actual field is updated later
            }
            $sumClause = $this->db->sum('statisticsresourceviewsCount', 'sum');
            $this->db->formatConditions(['statisticsresourceviewsResourceId' => $this->vars['id']]);
            if (!$totalViews = $this->db->selectFirstField('statistics_resource_views', $sumClause, FALSE, FALSE))
            {
                $totalViews = 1;
            }
            $resourceSingle['accesses'] = $this->messages->text(
                "viewResource",
                "numAccesses",
                $views . '/' . $totalViews
            );
            $this->stats->accessDownloadRatio($this->vars['id']);
            if (WIKINDX_FILE_VIEW_LOGGEDON_ONLY && !$this->session->getVar("setup_UserId"))
            {
                // display nothing
            }
            else
            {
                if ($this->stats->downloadRatio)
                {
                    $resourceSingle['download'] = $this->messages->text("viewResource", "download", $this->stats->downloadRatio);
                }
            }
            $viewIndex = $this->messages->text("viewResource", "viewIndex", $this->stats->accessRatio);
            $popularityIndex = $this->messages->text("viewResource", "popIndex", $this->stats->getPopularityIndex($this->vars['id']));
            if ($this->session->getVar("setup_Superadmin"))
            {
                $maturityIndex = \FORM\formHeader('statistics_STATS_CORE');
                $maturityIndex .= \FORM\hidden("method", 'setMaturityIndex');
                $maturityIndex .= \FORM\hidden("resourceId", $row['resourceId']);
       			$maturityIndex .= \FORM\hidden("browserTabID", $this->browserTabID);
                $maturityIndex .= \HTML\span($this->messages->text("viewResource", "maturityIndex")) .
                    "&nbsp;&nbsp;" . \FORM\textInput(
                        FALSE,
                        "maturityIndex",
                        $row['resourcemiscMaturityIndex'],
                        5,
                        4
                    );
                $maturityIndex .= "&nbsp;" . \FORM\formSubmit($this->messages->text("submit", "Submit"));
                $maturityIndex .= BR .
                    $this->messages->text("hint", "maturityIndex");
                $maturityIndex .= \FORM\formEnd();
            }
            else
            {
                $maturityIndex = $row['resourcemiscMaturityIndex'] ?
                    $this->messages->text("misc", "matIndex") .
                    $row['resourcemiscMaturityIndex'] . "/10" . BR
                    :
                    FALSE;
            }
            $resourceSingle['popIndex'] = $popularityIndex;
            $resourceSingle['viewIndex'] = $viewIndex;
            $resourceSingle['maturity'] = $maturityIndex;
            GLOBALS::addTplVar('multiUser', TRUE);
        }
        $return = $this->previousNextLinks($row['resourceId']);
        if (!empty($return))
        {
            $resourceSingle['navigation'] = $return;
        }
        $resourceSingle['links'] = $this->createLinks($row);
        $resourceSingle['resource'] = $this->bibStyle->process($row) .
            $this->coins->export($row, $this->bibStyle->coinsCreators);
        if ($row['resourceType'])
        {
            $typeLabel = $this->messages->text('resourceType', $row['resourceType']);
            $resourceSingle['info']['type'] = $this->messages->text('viewResource', 'type') . ':&nbsp;' .
                \HTML\a('link', $typeLabel, 'index.php?action=list_LISTSOMERESOURCES_CORE' .
                htmlentities('&method=typeProcess&id=' . $row['resourceType'] . '&browserTabID=' . $this->browserTabID));
        }
        if ($return = $this->displayLanguages($row))
        {
            $resourceSingle['info']['language'] = $return;
        }
        if ($row['resourcemiscPeerReviewed'] == 'Y')
        {
            $resourceSingle['info']['peerReviewed'] = $this->messages->text('resources', 'peerReviewed');
        }
        if ($return = \FORM\reduceLongText(\HTML\nlToHtml($row['resourceDoi']), 80))
        {
            $resourceSingle['info']['doi'] = $this->messages->text('resources', 'doi') . ':&nbsp;' .
                \HTML\a("link", $return, 'https://dx.doi.org/' .
                \HTML\nlToHtml($row['resourceDoi']), "_new");
        }
        if ($return = \HTML\nlToHtml($row['resourceIsbn']))
        {
            if (($row['resourceType'] == 'book') || ($row['resourceType'] == 'book_article') || ($row['resourceType'] == 'book_chapter'))
            {
                $return = \HTML\a(
                    'link',
                    $return,
                    'https://en.wikipedia.org/w/index.php?title=Special%3ABookSources&isbn=' . $return,
                    '_blank'
                );
                $resourceSingle['info']['isbn'] = $this->messages->text('resources', 'isbn') . ':&nbsp;' . $return;
            }
            else
            {
                $resourceSingle['info']['isbn'] = $this->messages->text('resources', 'isbn') . ':&nbsp;' . $return;
            }
        }
        if ($return = $this->displayKey($row))
        {
            $resourceSingle['info']['keyid'] = $this->messages->text('misc', 'bibtexKey') . ':&nbsp;' . $return;
        }
        if (
            (
                (WIKINDX_QUARANTINE && ($row['resourcemiscQuarantine'] == 'N'))
                || !WIKINDX_QUARANTINE
            )
            && ($return = $this->displayEmailFriendLink($row))
        ) {
            $resourceSingle['info']['email'] = $return;
        }
        $resourceSingle['info']['viewDetails'] = \HTML\aBrowse(
            'green',
            '1em',
            $this->messages->text('viewResource', 'viewDetails'),
            '#',
            "",
            $this->viewDetails($row)
        );
        $resourceSingle['info']['basket'] = $this->displayBasket($row);
        if (WIKINDX_QUARANTINE && $this->session->getVar("setup_Superadmin"))
        {
            $resourceSingle['info']['approveResource'] = $this->displayQuarantine($row);
        }
        if ($return = $this->displayCategories($row))
        {
            $resourceSingle['lists']['categories'] = $return;
        }
        if ($return = $this->displaySubcategories($row))
        {
            $resourceSingle['lists']['subcategories'] = $return;
        }
        if ($return = $this->displayKeywords($row))
        {
            $resourceSingle['lists']['keywords'] = $return;
        }
        if ($return = $this->displayUserTags($row))
        {
            $resourceSingle['lists']['usertags'] = $return;
        }
        if ($return = $this->displayCreators($row))
        {
            $resourceSingle['lists']['creators'] = $return;
        }
        if ($return = $this->displayPublisher($row))
        {
            $resourceSingle['lists']['publisher'] = $return;
        }
        if ($return = $this->displayCollection($row))
        {
            $resourceSingle['lists']['collection'] = $return;
        }
        if ($return = $this->displayBibliographies($row))
        {
            $resourceSingle['lists']['bibliographies'] = $return;
        }
        if ($return = $this->common->showCitations($row['resourceId'], TRUE))
        {
            $resourceSingle['lists']['cited'] = $return;
        }
        $resourceSingle['attachments'] = $this->attachedFiles($row['resourceId'], $row['resourcemiscAddUserIdResource']);
        $resourceSingle['urls'] = $this->urls($row['resourceId'], $row['resourcemiscAddUserIdResource']);
        $return = $this->custom->view($this->vars['id']);
        if (!empty($return))
        {
            $resourceSingle['custom'] = $return;
        }
        $return = $this->abstract->view($row);
        if (!empty($return))
        {
            $resourceSingle['abstract'] = $return;
        }
        $return = $this->note->view($row);
        if (!empty($return))
        {
            $resourceSingle['note'] = $return;
        }
        if (
            (
                (
                    WIKINDX_METADATA_ALLOW
                    || (!WIKINDX_METADATA_ALLOW && WIKINDX_METADATA_USERONLY && $this->session->getVar("setup_UserId"))
                )
                && $row['resourcemiscQuarantine'] == 'N'
            )
            || ($this->session->getVar("setup_Superadmin"))
        ) {
            $return = $this->meta->viewQuotes($row);
            if (!empty($return))
            {
                $resourceSingle['quotesTitle'] = $return['title'];
                unset($return['title']);
                if (array_key_exists('editLink', $return))
                {
                    $resourceSingle['quotesEditLink'] = $return['editLink'];
                    unset($return['editLink']);
                }
                $resourceSingle['quotes'] = $return;
            }
            $return = $this->meta->viewParaphrases($row);
            if (!empty($return))
            {
                $resourceSingle['paraphrasesTitle'] = $return['title'];
                unset($return['title']);
                if (array_key_exists('editLink', $return))
                {
                    $resourceSingle['paraphrasesEditLink'] = $return['editLink'];
                    unset($return['editLink']);
                }
                $resourceSingle['paraphrases'] = $return;
            }
            $return = $this->meta->viewMusings($row);
            if (!empty($return))
            {
                $resourceSingle['musingsTitle'] = $return['title'];
                unset($return['title']);
                if (array_key_exists('editLink', $return))
                {
                    $resourceSingle['musingsEditLink'] = $return['editLink'];
                    unset($return['editLink']);
                }
                $resourceSingle['musings'] = $return;
            }
        }
        GLOBALS::setTplVar('resourceSingle', $resourceSingle);
        unset($resourceSingle);
        if (WIKINDX_GS_ALLOW)
        {
            if ($gs = $this->gs->export($row, $this->bibStyle->coinsCreators))
            {
                GLOBALS::addTplVar('gsMetaTags', $gs);
            }
        }
        $this->session->setVar("sql_LastSolo", $row['resourceId']);
        \TEMPSTORAGE\store($this->db, $this->browserTabID, ['sql_LastSolo' => $row['resourceId']]);
        // Turn on the 'add bookmark' menu item
        $this->session->setVar("bookmark_DisplayAdd", TRUE);
        $this->session->setVar("bookmark_View", 'solo');
    }
    /**
     * attachedFiles
     *
     * @param int $resourceId
     * @param int $userAddId
     *
     * @return array
     */
    private function attachedFiles($resourceId, $userAddId)
    {
        // Are only logged on users allowed to view this file and is this user logged on?
        if (WIKINDX_FILE_VIEW_LOGGEDON_ONLY && !$this->session->getVar("setup_UserId"))
        {
            return [];
        }
        $attach = FACTORY_ATTACHMENT::getInstance();
        $this->db->formatConditions(['resourceattachmentsResourceId' => $resourceId]);
        $this->db->orderBy('resourceattachmentsFilename');
        $recordset = $this->db->select(
            'resource_attachments',
            ['resourceattachmentsId', 'resourceattachmentsHashFilename', 'resourceattachmentsFileName', 'resourceattachmentsDescription',
                'resourceattachmentsPrimary', 'resourceattachmentsEmbargo', ]
        );
        $multiple = $this->db->numRows($recordset) > 1 ? TRUE : FALSE;
        $primary = FALSE;
        $month = date('Ym');
        while ($row = $this->db->fetchRow($recordset))
        {
            if ($row['resourceattachmentsDescription'])
            {
                $readme = \HTML\aBrowse(
                    'green',
                    '1em',
                    $this->messages->text('resources', 'attachmentReadMe'),
                    '#',
                    "",
                    $row['resourceattachmentsDescription']
                );
            }
            else
            {
                $readme = '';
            }
            if (!$this->session->getVar("setup_Superadmin") && ($row['resourceattachmentsEmbargo'] == 'Y'))
            {
                continue;
            }
            if ($this->multiUser)
            {
                $this->db->formatConditions(['statisticsattachmentdownloadsAttachmentId' => $row['resourceattachmentsId']]);
                $this->db->formatConditions(['statisticsattachmentdownloadsMonth' => $month]);
                if (!$views = $this->db->selectFirstField('statistics_attachment_downloads', ['statisticsattachmentdownloadsCount']))
                {
                    $views = 0;
                }
                $sumClause = $this->db->sum('statisticsattachmentdownloadsCount', 'sum');
                $this->db->formatConditions(['statisticsattachmentdownloadsAttachmentId' => $row['resourceattachmentsId']]);
                if (!$totalViews = $this->db->selectFirstField('statistics_attachment_downloads', $sumClause, FALSE, FALSE))
                {
                    $totalViews = 0;
                }
                $downloads = '[' . $views . '/' . $totalViews . ']';
            }
            else
            {
                $downloads = FALSE;
            }
            if ($multiple && ($row['resourceattachmentsPrimary'] == 'Y'))
            {
                $primary = $attach->makeLink($row, $resourceId, $multiple, TRUE, TRUE) . $readme . $downloads;
            }
            else
            {
                $files[] = $attach->makeLink($row, $resourceId, $multiple) . $readme . $downloads;
            }
        }
        if ($primary)
        {
            array_unshift($files, $primary);
        }
        if (
            $this->session->getVar("setup_Superadmin") ||
            (WIKINDX_ORIGINATOR_EDIT_ONLY && WIKINDX_FILE_ATTACH && ($userAddId == $this->userId)) ||
            (!WIKINDX_ORIGINATOR_EDIT_ONLY && WIKINDX_FILE_ATTACH && $this->session->getVar("setup_Write"))
        ) {
            if (isset($files))
            {
                $attachments['attachments'] = $files;
                $attachments['editLink'] =
                    \HTML\a(
                        $this->icons->getClass("edit"),
                        $this->icons->getHTML("edit"),
                        'index.php?action=attachments_ATTACHMENTS_CORE' .
                        htmlentities('&function=editInit&resourceId=' . $resourceId) . '&browserTabID=' . $this->browserTabID
                    );
            }
            else
            {
                $attachments['attachments'] = [];
                $attachments['editLink'] = \HTML\a(
                    $this->icons->getClass("add"),
                    $this->icons->getHTML("add"),
                    'index.php?action=attachments_ATTACHMENTS_CORE' .
                    htmlentities('&function=editInit&resourceId=' . $resourceId) . '&browserTabID=' . $this->browserTabID
                );
            }
        }
        elseif (!WIKINDX_FILE_VIEW_LOGGEDON_ONLY)
        { // Anyone can view
            if (isset($files))
            {
                $attachments['attachments'] = $files;
            }
            else
            {
                $attachments['attachments'] = [];
            }
        }
        else
        {
            return [];
        }
        $attachments['title'] = $this->messages->text('viewResource', 'attachments');

        return $attachments;
    }
    /**
     * urls
     *
     * @param int $resourceId
     * @param int $userAddId
     *
     * @return array
     */
    private function urls($resourceId, $userAddId)
    {
        $this->db->formatConditions(['resourceurlResourceId' => $resourceId]);
        $this->db->ascDesc = $this->db->desc;
        $this->db->orderBy('resourceurlPrimary');
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourceurlId');
        $recordset = $this->db->select('resource_url', ['resourceurlUrl', 'resourceurlName']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $url = $row['resourceurlUrl'];
            $name = $row['resourceurlName'] ? $row['resourceurlName'] : $url;
            $urls[] = \HTML\a('link', \URL\reduceUrl(\HTML\nlToHtml($name)), $url, '_new');
        }
        if ($this->session->getVar("setup_Write") && (!WIKINDX_ORIGINATOR_EDIT_ONLY || ($userAddId == $this->userId)
            || $this->session->getVar("setup_Superadmin")))
        {
            if (isset($urls))
            {
                $return['urls'] = $urls;
                if ($this->session->getVar("setup_Write"))
                {
                    $return['editLink'] = \HTML\a(
                        $this->icons->getClass("edit"),
                        $this->icons->getHTML("edit"),
                        'index.php?action=urls_URLS_CORE' . htmlentities("&function=editInit&resourceId=" . $resourceId
                         . '&browserTabID=' . $this->browserTabID)
                    );
                }
            }
            else
            {
                $return['urls'] = [];
                $return['editLink'] = \HTML\a(
                    $this->icons->getClass("add"),
                    $this->icons->getHTML("add"),
                    'index.php?action=urls_URLS_CORE' . htmlentities("&function=editInit&resourceId=" . $resourceId
                     . '&browserTabID=' . $this->browserTabID)
                );
            }
        }
        elseif (isset($urls))
        {
            $return['urls'] = $urls;
        }
        else
        {
            return [];
        }
        $return['title'] = $this->messages->text('viewResource', 'urls');

        return $return;
    }
    /**
     * Show previous and next resource hyperlinks
     *
     * @param int $thisId
     *
     * @return array
     */
    private function previousNextLinks($thisId)
    {
        if (array_key_exists('method', $this->vars) && ($this->vars['method'] == 'random'))
        {
            return $this->nextRandomLink($thisId);
        }
        // Check if opening this resource from a list into a new tab (sql_ListStmt not stored in temp_storage)
        if (!$this->session->getVar('list_Front') && ($this->browserTabID && !\TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'sql_ListStmt')))
        {
            return [];
        }
        if (!$this->execNP)
        {
            return [];
        }
        if ($this->startNP === FALSE)
        {
            $start = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'mywikindx_PagingStart');
            if (is_bool($start))
            {
                $start = $this->session->getVar("mywikindx_PagingStart", 0);
            }
        }
        else
        {
            $start = $this->startNP;
        }
        $allIds = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_NextPreviousIds');
        if (is_bool($allIds))
        {
            $allIds = $this->session->getVar("list_NextPreviousIds");
        }
        if (is_bool($allIds) || empty($allIds))
        {
            return [];
        }
        $thisKey = array_search($thisId, $allIds);
        if ($thisKey === FALSE)
        {
            return [];
        }
        $total = $this->session->getVar('list_Front');
        if (!$total) {
			if (!$total = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'setup_PagingTotal'))
			{
				$total = $this->session->getVar("setup_PagingTotal");
			}
        }
        if (!$order = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'sql_LastOrder'))
        {
            $order = $this->session->getVar("sql_LastOrder");
        }
        $alpha = (GLOBALS::getUserVar('PagingStyle') == 'A') && in_array($order, ['title', 'creator', 'attachments']);
        if ($thisKey)
        {
            $array['back'] = \HTML\a(
                $this->icons->getClass("previous"),
                $this->icons->getHTML("previous"),
                "index.php?action=resource_RESOURCEVIEW_CORE" . htmlentities("&id=" . $allIds[$thisKey - 1]) . 
            	"&browserTabID=" . $this->browserTabID
            );
        }
        elseif ($start && !$alpha)
        {
            $array['back'] = \HTML\a(
                $this->icons->getClass("previous"),
                $this->icons->getHTML("previous"),
                "index.php?action=resource_RESOURCEVIEW_CORE" . htmlentities("&np=backward") . 
            	"&browserTabID=" . $this->browserTabID
            );
        }
        else
        {
            $array['back'] = FALSE;
        }
        if ($thisKey < (count($allIds) - 1))
        {
            $array['forward'] = \HTML\a(
                $this->icons->getClass("next"),
                $this->icons->getHTML("next"),
                "index.php?action=resource_RESOURCEVIEW_CORE" . htmlentities("&id=" . $allIds[$thisKey + 1]) . 
            	"&browserTabID=" . $this->browserTabID
            );
        }
        elseif (($start + GLOBALS::getUserVar('Paging') < $total) && !$alpha)
        {
            $array['forward'] = \HTML\a(
                $this->icons->getClass("next"),
                $this->icons->getHTML("next"),
                "index.php?action=resource_RESOURCEVIEW_CORE" . htmlentities("&np=forward") . 
            	"&browserTabID=" . $this->browserTabID
            );
        }
        else
        {
            $array['forward'] = FALSE;
        }
        if ($this->session->getVar("setup_Superadmin"))
        {
            if (array_key_exists($thisKey + 1, $allIds))
            {
                $this->nextDelete = $allIds[$thisKey + 1];
            }
            elseif (array_key_exists($thisKey - 1, $allIds))
            {
                $this->nextDelete = $allIds[$thisKey - 1];
            }
        }

        return $array;
    }
    /**
     * Set next/previous resource links initially and when going forward and backward across paging
     *
     * @param mixed $querySession
     * @param mixed $returnId
     * @param mixed $reload
     * @param mixed $thisId
     *
     * @return array
     */
    private function setPreviousNext($querySession, $returnId = FALSE, $reload = FALSE, $thisId = FALSE)
    {
        $allIds = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_NextPreviousIds');
        if (is_bool($allIds))
        {
            $allIds = $this->session->getVar("list_NextPreviousIds");
        }
        if (!$start = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'mywikindx_PagingStart'))
        {
            $start = $this->session->getVar("mywikindx_PagingStart");
        }
        if ($start)
        {
            if ($returnId == 'forward')
            {
                $this->session->setVar("mywikindx_PagingStart", $start + GLOBALS::getUserVar('Paging'));
            }
            else
            {
                $this->session->setVar("mywikindx_PagingStart", $start - GLOBALS::getUserVar('Paging'));
            }
        }
        else
        {
            $this->session->setVar("mywikindx_PagingStart", count($allIds));
        }
        $start = $this->session->getVar("mywikindx_PagingStart");
        \TEMPSTORAGE\store($this->db, $this->browserTabID, ['mywikindx_PagingStart' => $start]);
        $limit = $this->db->limit(GLOBALS::getUserVar('Paging'), $start, TRUE); // "LIMIT $limitStart, $limit";
        $query = $querySession . $limit;
        $resultset = $this->db->query($query);
        while ($row = $this->db->fetchRow($resultset))
        {
            if (array_key_exists('rId', $row))
            {
                $totalIds[] = $row['rId'];
            }
            else
            {
                $totalIds[] = $row['resourcemiscId'];
            }
        }
        if (isset($totalIds))
        {
            $this->session->setVar("list_NextPreviousIds", $totalIds);
            \TEMPSTORAGE\store($this->db, $this->browserTabID, ['list_NextPreviousIds' => $totalIds]);
        }
        if (isset($totalIds) && ($returnId == 'forward'))
        { // moving forwards
            return [$start, $totalIds[0]];
        }
        elseif (isset($totalIds) && ($returnId == 'backward'))
        { // moving backwards
            return [$start, $totalIds[count($totalIds) - 1]];
        }

        return [$start, FALSE];
    }
    /**
     * Create links for viewing, editing deleting etc. resources
     *
     * @param mixed $row
     *
     * @return array
     */
    private function createLinks($row)
    {
        $write = $this->session->getVar("setup_Write");
        $links = [];
        $edit = FALSE;
        if ($write && (!WIKINDX_ORIGINATOR_EDIT_ONLY || ($row['resourcemiscAddUserIdResource'] == $this->userId)))
        {
            $links['edit'] = \HTML\a(
                $this->icons->getClass("edit"),
                $this->icons->getHTML("edit"),
                "index.php?action=resource_RESOURCEFORM_CORE&type=edit" . htmlentities("&id=" . $row['resourceId'])
                . "&browserTabID=" . $this->browserTabID
            );
            if ($row['resourcemiscAddUserIdResource'] == $this->userId)
            {
                $links['delete'] =
                    "index.php?action=admin_DELETERESOURCE_CORE" . htmlentities('&function=deleteResourceConfirm') .
                    htmlentities('&navigate=front&resource_id=' . $row['resourceId']. "&browserTabID=" . $this->browserTabID);
// Delete? Seems not to be necessary
//                $links['delete'] = \HTML\a($this->icons->getClass("delete"), $this->icons->getHTML("delete"), $links['delete']);
            }
            $edit = $this->allowEdit = TRUE;
        }
        if ($this->session->getVar("setup_Superadmin"))
        {
            $this->allowEdit = TRUE;
            if (!$edit)
            {
                $links['edit'] = \HTML\a(
                    $this->icons->getClass("edit"),
                    $this->icons->getHTML("edit"),
                    "index.php?action=resource_RESOURCEFORM_CORE&type=edit" . htmlentities("&id=" . $row['resourceId'])
                    . "&browserTabID=" . $this->browserTabID
                );
            }
            $links['delete'] =
                "index.php?action=admin_DELETERESOURCE_CORE" . htmlentities('&function=deleteResourceConfirm');
            if ($this->nextDelete)
            {
                $links['delete'] .= htmlentities('&navigate=nextResource&resource_id=' . $row['resourceId']) .
                htmlentities('&nextResourceId=') . $this->nextDelete . 
            	"&browserTabID=" . $this->browserTabID;
            }
            else
            {
                $links['delete'] .= htmlentities('&navigate=front&resource_id=' . $row['resourceId']) . 
            	"&browserTabID=" . $this->browserTabID;
            }
            $links['delete'] = \HTML\a($this->icons->getClass("delete"), $this->icons->getHTML("delete"), $links['delete']);
        }
        // display CMS link if required
        // link is actually a JavaScript call
        if (GLOBALS::getUserVar('DisplayCmsLink') && WIKINDX_CMS_ALLOW)
        {
            $links['cms'] = \HTML\a(
                'cmsLink',
                "CMS:&nbsp;" . $row['resourceId'],
                "javascript:coreOpenPopup('index.php?action=cms_CMS_CORE&amp;method=display" .
                "&amp;id=" . $row['resourceId'] . "', 90)"
            );
        }
        // display bibtex link if required
        // link is actually a JavaScript call
        if (GLOBALS::getUserVar('DisplayBibtexLink'))
        {
            $links['bibtex'] = \HTML\a(
                $this->icons->getClass("bibtex"),
                $this->icons->getHTML("bibtex"),
                "javascript:coreOpenPopup('index.php?action=resource_VIEWBIBTEX_CORE&amp;method=display" .
                "&amp;id=" . $row['resourceId'] . "', 90)"
            );
        }

        return $links;
    }
    /**
     * create link to edit categories, subcategories and keywords
     *
     * @param mixed $resourceId
     *
     * @return mixed
     */
    private function createCatEditLink($resourceId)
    {
    // Does the user have user_tags?
    	$this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
    	$resultSet = $this->db->select('user_tags', ['usertagsId']);
        if ($this->allowEdit)
        {
            return '&nbsp;&nbsp;' . \HTML\a(
                $this->icons->getClass("edit"),
                $this->icons->getHTML("edit"),
                "index.php?action=resource_RESOURCECATEGORYEDIT_CORE" . htmlentities("&id=" . $resourceId) . 
                '&browserTabID=' . $this->browserTabID
            );
        }
        elseif ($this->db->numRows($resultSet))
        {
            return '&nbsp;&nbsp;' . \HTML\a(
                $this->icons->getClass("edit"),
                $this->icons->getHTML("edit"),
                "index.php?action=resource_RESOURCECATEGORYEDIT_CORE" . htmlentities("&id=" . $resourceId) . htmlentities("&tagEditOnly=1") . 
                '&browserTabID=' . $this->browserTabID
            );
        }
        else
        {
            return FALSE;
        }
    }
    /**
     * show resource languages
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayLanguages($row)
    {
        $rId = $row['resourceId'];
        $this->commonBib->userBibCondition('resourcelanguageResourceId');
        $this->db->formatConditions(['resourcelanguageResourceId' => $rId]);
        $this->db->leftJoin('language', 'languageId', 'resourcelanguageLanguageId');
        $this->db->orderBy('languageLanguage');
        $resultset = $this->db->select('resource_language', ['resourcelanguageLanguageId', 'languageLanguage']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->formatConditions(['resourcelanguageLanguageId' => $row['resourcelanguageLanguageId']]);
            $this->db->groupBy('resourcelanguageLanguageId', TRUE, $this->db->count('resourcelanguageLanguageId') .
                $this->db->greater . $this->db->tidyInput(0));
            $resultset2 = $this->db->selectCounts('resource_language', 'resourcelanguageLanguageId');
            $row2 = $this->db->fetchRow($resultset2);
            if ($row2['count'] > 1)
            { // i.e. more than one resource for this language
                $array[] = \HTML\a("link", \HTML\nlToHtml($row['languageLanguage']), 'index.php?' .
                    htmlentities('action=list_LISTSOMERESOURCES_CORE&method=languageProcess&id=' . $row2['resourcelanguageLanguageId']
                     . '&browserTabID=' . $this->browserTabID));
            }
            else
            {
                $array[] = \HTML\nlToHtml($row['languageLanguage']);
            }
        }
        if (!isset($array))
        { // probably because of browsing a user bibliography, so need to get just the languages
            $this->db->formatConditions(['resourcelanguageResourceId' => $rId]);
            $this->db->leftJoin('language', 'languageId', 'resourcelanguageLanguageId');
            $this->db->orderBy('languageLanguage');
            $resultset = $this->db->select('resource_language', ['resourcelanguageLanguageId', 'languageLanguage']);
            while ($row = $this->db->fetchRow($resultset))
            {
                $array[] = \HTML\nlToHtml($row['languageLanguage']);
            }
            if (!isset($array))
            {
                return FALSE;
            }
        }
        $link = $this->createCatEditLink($rId);
        $title = $this->messages->text("resources", "languages");

        return $title . ': ' . implode(', ', $array) . $link;
    }
    /**
     * Show resource categories
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayCategories($row)
    {
        $rId = $row['resourceId'];
        $this->commonBib->userBibCondition('resourcecategoryResourceId');
        $this->db->formatConditions(['resourcecategoryResourceId' => $rId]);
        $this->db->formatConditions($this->db->formatFields('resourcecategoryCategoryId') . ' IS NOT NULL');
        $this->db->leftJoin('category', 'categoryId', 'resourcecategoryCategoryId');
        $this->db->orderBy('categoryCategory');
        $resultset = $this->db->select('resource_category', ['resourcecategoryCategoryId', 'categoryCategory']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->formatConditions(['resourcecategoryCategoryId' => $row['resourcecategoryCategoryId']]);
            $this->db->groupBy('resourcecategoryCategoryId', TRUE, $this->db->formatFields('count') .
                $this->db->greater . $this->db->tidyInput(0));
            $resultset2 = $this->db->selectCounts('resource_category', 'resourcecategoryCategoryId');
            $row2 = $this->db->fetchRow($resultset2);
            if ($row2['count'] > 1)
            { // i.e. more than one resource for this category
                $array[] = \HTML\a("link", \HTML\nlToHtml($row['categoryCategory']), 'index.php?' .
                    htmlentities('action=list_LISTSOMERESOURCES_CORE&method=categoryProcess&id=' . $row2['resourcecategoryCategoryId']
                     . '&browserTabID=' . $this->browserTabID));
            }
            else
            {
                $array[] = \HTML\nlToHtml($row['categoryCategory']);
            }
        }
        if (!isset($array))
        { // probably because of browsing a user bibliography, so need to get just the category names
            $this->db->formatConditions(['resourcecategoryResourceId' => $rId]);
            $this->db->formatConditions($this->db->formatFields('resourcecategoryCategoryId') . ' IS NOT NULL');
            $this->db->leftJoin('category', 'categoryId', 'resourcecategoryCategoryId');
            $this->db->orderBy('categoryCategory');
            $resultset = $this->db->select('resource_category', ['resourcecategoryCategoryId', 'categoryCategory']);
            while ($row = $this->db->fetchRow($resultset))
            {
                $array[] = \HTML\nlToHtml($row['categoryCategory']);
            }
            if (!isset($array))
            {
                return FALSE;
            }
        }
        $link = $this->createCatEditLink($rId);
        $title = $this->messages->text("resources", "categories");

        return $title . ': ' . implode(', ', $array) . $link;
    }
    /**
     * Show resource subcategories
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displaySubcategories($row)
    {
        $rId = $row['resourceId'];
        $this->commonBib->userBibCondition('resourcecategoryResourceId');
        $this->db->formatConditions(['resourcecategoryResourceId' => $rId]);
        $this->db->formatConditions($this->db->formatFields('resourcecategorySubcategoryId') . ' IS NOT NULL');
        $this->db->leftJoin('subcategory', 'subcategoryId', 'resourcecategorySubcategoryId');
        $this->db->orderBy('subcategorySubcategory');
        $resultset = $this->db->select('resource_category', ['resourcecategorySubcategoryId', 'subcategorySubcategory']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->formatConditions(['resourcecategorySubcategoryId' => $row['resourcecategorySubcategoryId']]);
            $this->db->groupBy('resourcecategorySubcategoryId', TRUE, $this->db->formatFields('count') .
                $this->db->greater . $this->db->tidyInput(0));
            $resultset2 = $this->db->selectCounts('resource_category', 'resourcecategorySubcategoryId');
            $row2 = $this->db->fetchRow($resultset2);
            if ($row2['count'] > 1)
            { // i.e. more than one resource for this subcategory
                $array[] = \HTML\a("link", \HTML\nlToHtml($row['subcategorySubcategory']), 'index.php?' .
                    htmlentities('action=list_LISTSOMERESOURCES_CORE&method=subcategoryProcess&id=' . $row2['resourcecategorySubcategoryId']
                     . '&browserTabID=' . $this->browserTabID));
            }
            else
            {
                $array[] = \HTML\nlToHtml($row['subcategorySubcategory']);
            }
        }
        if (!isset($array))
        { // probably because of browsing a user bibliography, so need to get just the subcategory names
            $this->db->formatConditions(['resourcecategoryResourceId' => $rId]);
            $this->db->formatConditions($this->db->formatFields('resourcecategorySubcategoryId') . ' IS NOT NULL');
            $this->db->leftJoin('subcategory', 'subcategoryId', 'resourcecategorySubcategoryId');
            $this->db->orderBy('subcategorySubcategory');
            $resultset = $this->db->select('resource_category', ['resourcecategorySubcategoryId', 'subcategorySubcategory']);
            while ($row = $this->db->fetchRow($resultset))
            {
                $array[] = \HTML\nlToHtml($row['subcategorySubcategory']);
            }
            if (!isset($array))
            {
                return FALSE;
            }
        }
        $link = $this->createCatEditLink($rId);
        $title = $this->messages->text("resources", "subcategories");

        return $title . ': ' . implode(', ', $array) . $link;
    }
    /**
     * Show resource publisher
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayPublisher($row)
    {
        if (!$row['publisherName'] && !$row['publisherLocation'])
        {
            return FALSE;
        }
        $useBib = GLOBALS::getUserVar('BrowseBibliography');
        if ($useBib)
        {
            $this->commonBib->userBibCondition('resourcemiscId');
        }
        if ((($row['resourceType'] == 'proceedings_article') || ($row['resourceType'] == 'proceedings')) && $row['resourcemiscField1'])
        {
            $this->db->formatConditions(['resourcemiscField1' => $row['resourcemiscField1']]);
            $resultset = $this->db->selectCounts('resource_misc', 'resourcemiscField1');
            $publisherId = $row['resourcemiscField1'];
        }
        else
        {
            $this->db->formatConditions(['resourcemiscPublisher' => $row['resourcemiscPublisher']]);
            $resultset = $this->db->selectCounts('resource_misc', 'resourcemiscPublisher');
            $publisherId = $row['resourcemiscPublisher'];
        }
        $name = \HTML\nlToHtml($row['publisherLocation'] ? $row['publisherName'] .
            ' (' . $row['publisherLocation'] . ')' : $row['publisherName']);
        $countRow = $this->db->fetchRow($resultset);
        if (!is_bool($countRow) && $countRow['count'] > 1)
        { // i.e. more than one resource for this publisher
            $name = \HTML\a("link", $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=publisherProcess&id=' . $publisherId 
                . '&browserTabID=' . $this->browserTabID));
        }
        $title = $this->messages->text("resources", "publisher");

        return $title . ": $name";
    }
    /**
     * Show resource collection
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayCollection($row)
    {
        if (!$row['collectionTitle'])
        {
            return FALSE;
        }
        $useBib = GLOBALS::getUserVar('BrowseBibliography');
        if ($useBib)
        {
            $this->commonBib->userBibCondition('resourcemiscId');
        }
        $this->db->formatConditions(['resourcemiscCollection' => $row['resourcemiscCollection']]);
        $resultset = $this->db->selectCounts('resource_misc', 'resourcemiscCollection');
        $name = preg_replace("/{(.*)}/Uu", "$1", \HTML\nlToHtml($row['collectionTitle']));
        $countRow = $this->db->fetchRow($resultset);
        if (!is_bool($countRow) && $countRow['count'] > 1)
        { // i.e. more than one resource for this collection
            $name = \HTML\a("link", $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=collectionProcess&id=' .
                $row['resourcemiscCollection'] . '&browserTabID=' . $this->browserTabID));
        }
        $title = $this->messages->text("resources", "collection");

        return $title . ": $name";
    }
    /**
     * Show resource keywords
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayKeywords($row)
    {
        $rId = $row['resourceId'];
        $this->commonBib->userBibCondition('resourcekeywordResourceId');
        $this->db->formatConditions(['resourcekeywordResourceId' => $rId]);
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
        $this->db->orderBy('keywordKeyword');
        $resultset = $this->db->select('resource_keyword', ['resourcekeywordKeywordId', 'keywordKeyword', 'keywordGlossary']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->formatConditions($this->db->formatFields('resourcekeywordResourceId') . ' IS NOT NULL');
            $this->db->formatConditions(['resourcekeywordKeywordId' => $row['resourcekeywordKeywordId']]);
            $this->db->groupBy('resourcekeywordKeywordId', TRUE, $this->db->formatFields('count') .
                $this->db->greater . $this->db->tidyInput(0));
            $resultset2 = $this->db->selectCounts('resource_keyword', 'resourcekeywordKeywordId');
            $row2 = $this->db->fetchRow($resultset2);
            if ($row2['count'] > 1)
            { // i.e. more than one resource for this keyword
                $array[] = \HTML\a(
                    "link",
                    \HTML\nlToHtml($row['keywordKeyword']),
                    'index.php?' .
                    htmlentities('action=list_LISTSOMERESOURCES_CORE&method=keywordProcess&id=' . $row2['resourcekeywordKeywordId']) 
                     . '&browserTabID=' . $this->browserTabID,
                    "",
                    \HTML\dbToHtmlPopupTidy($row['keywordGlossary'])
                );
            }
            elseif ($row['keywordGlossary'])
            {
                $array[] = \HTML\aBrowse(
                    'green',
                    '1em',
                    \HTML\nlToHtml($row['keywordKeyword']),
                    '#',
                    "",
                    \HTML\dbToHtmlPopupTidy($row['keywordGlossary'])
                );
            }
            else
            {
                $array[] = \HTML\nlToHtml($row['keywordKeyword']);
            }
        }
        if (!isset($array))
        { // probably because of browsing a user bibliography
            $this->db->formatConditions(['resourcekeywordResourceId' => $rId]);
            $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
            $this->db->orderBy('keywordKeyword');
            $resultset = $this->db->select('resource_keyword', ['resourcekeywordKeywordId', 'keywordKeyword']);
            while ($row = $this->db->fetchRow($resultset))
            {
                $array[] = \HTML\nlToHtml($row['keywordKeyword']);
            }
            if (!isset($array))
            {
                return FALSE;
            }
        }
        $link = $this->createCatEditLink($rId);
        $title = $this->messages->text("resources", "keywords");

        return $title . ': ' . implode(', ', $array) . $link;
    }
    /**
     * Show resource user tags
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayUserTags($row)
    {
        // get user tags in this resource
        $this->db->formatConditions(['resourceusertagsResourceId' => $row['resourceId']]);
        $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->leftJoin('user_tags', 'usertagsId', 'resourceusertagsTagId');
        $resultset = $this->db->select('resource_user_tags', ['resourceusertagsTagId', 'usertagsTag']);
        if (!$this->db->numRows($resultset))
        {
            return;
        }
        $link = $this->createCatEditLink($row['resourceId']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $this->db->formatConditions(['resourceusertagsTagId' => $row['resourceusertagsTagId']]);
            $this->db->groupBy('resourceusertagsTagId', TRUE, $this->db->formatFields('count') .
                $this->db->greater . $this->db->tidyInput(0));
            $resultset2 = $this->db->selectCounts('resource_user_tags', 'resourceusertagsTagId');
            $row2 = $this->db->fetchRow($resultset2);
            if ($row2['count'] > 1)
            { // i.e. more than one resource for this usertag
                $array[] = \HTML\a("link", \HTML\nlToHtml($row['usertagsTag']), 'index.php?' .
                    htmlentities('action=list_LISTSOMERESOURCES_CORE&method=usertagProcess&id=' . $row2['resourceusertagsTagId']
                     . '&browserTabID=' . $this->browserTabID));
            }
            else
            {
                $array[] = \HTML\nlToHtml($row['usertagsTag']);
            }
        }
        $title = $this->messages->text("resources", "userTags");

        return $title . ': ' . implode(', ', $array) . $link;
    }
    /**
     * Show resource creators
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayCreators($row)
    {
        if (empty($this->bibStyle->resourceCreators))
        {
            return FALSE;
        }
        $creators = [];
        // is e.g. Array([1] => Array([0] => 631, [1] => 234)) where [1] is the creatorRole with an array of ordered creator ids
        foreach ($this->bibStyle->resourceCreators as $cArray)
        {
            foreach ($cArray as $creatorId)
            {
                if (!$creatorId)
                {
                    continue;
                }
                if (array_search($creatorId, $creators) === FALSE)
                {
                    $creators[] = $creatorId;
                }
            }
        }
        if (empty($creators))
        {
            return FALSE;
        }
        list($gCreators, $alias) = $this->creatorGroupMembers($creators);
        $this->commonBib->userBibCondition('resourcecreatorResourceId');
        $this->db->formatConditionsOneField($gCreators, 'resourcecreatorCreatorId');
        // Count no. appearances of each creator
        $subSql = $this->db->queryNoExecute(
            $this->db->selectNoExecute('resource_creator', ['resourcecreatorResourceId', 'resourcecreatorCreatorId'], TRUE)
        );
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->groupBy(
            ['resourcecreatorCreatorId', 'creatorPrefix', 'creatorSurname'],
            TRUE,
            $this->db->count('resourcecreatorCreatorId') . $this->db->greater . $this->db->tidyInput(0)
        );
        $this->db->orderBy('creatorSurname');
        $resultset = $this->db->selectCounts(
            FALSE,
            'resourcecreatorCreatorId',
            ['creatorPrefix', 'creatorSurname'],
            $this->db->subQuery($subSql, 'rc', FALSE),
            FALSE
        );
        while ($catRow = $this->db->fetchRow($resultset))
        {
            $name = ($catRow['creatorPrefix'] ? \HTML\nlToHtml($catRow['creatorPrefix']) . '&nbsp;' : '') .
                \HTML\nlToHtml($catRow['creatorSurname']);
            if ($catRow['count'] > 1)
            { // i.e. more than one resource for this creator
                if (array_key_exists($catRow['resourcecreatorCreatorId'], $alias))
                {
                    $aliastmp = $this->messages->text('creators', 'alias', implode(', ', $alias[$catRow['resourcecreatorCreatorId']]));
                }
                else
                {
                    $aliastmp = '';
                }
                $array[] = \HTML\a(
                    "link",
                    $name,
                    'index.php?' .
                    htmlentities('action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&id=' . $catRow['resourcecreatorCreatorId']
                     . '&browserTabID=' . $this->browserTabID),
                    FALSE,
                    $aliastmp
                );
            }
            else
            {
                $array[] = $name;
            }
        }
        if (!isset($array))
        { // probably because of browsing a user bibliography, so need to get just the creator names
            $this->db->formatConditionsOneField($gCreators, 'resourcecreatorCreatorId');
            // Count no. appearances of each creator
            $subSql = $this->db->queryNoExecute(
                $this->db->selectNoExecute('resource_creator', ['resourcecreatorResourceId', 'resourcecreatorCreatorId'], TRUE)
            );
            $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
            $this->db->groupBy(
                ['resourcecreatorCreatorId', 'creatorPrefix', 'creatorSurname'],
                TRUE,
                $this->db->count('resourcecreatorCreatorId') . $this->db->greater . $this->db->tidyInput(0)
            );
            $this->db->orderBy('creatorSurname');
            $resultset = $this->db->selectCounts(
                FALSE,
                'resourcecreatorCreatorId',
                ['creatorPrefix', 'creatorSurname'],
                $this->db->subQuery($subSql, 'rc', FALSE),
                FALSE
            );
            while ($row = $this->db->fetchRow($resultset))
            {
                $array[] = ($row['creatorPrefix'] ? \HTML\nlToHtml($row['creatorPrefix']) . '&nbsp;' : '') .
                    \HTML\nlToHtml($row['creatorSurname']);
            }
            if (!isset($array))
            {
                return FALSE;
            }
        }
        $title = $this->messages->text('creators', 'creators');

        return $title . ': ' . implode(', ', $array);
    }
    /**
     * Replace creators who are members of a group with the group master -- not written to session but used only to process the select
     *
     * @param mixed $creators
     *
     * @return array
     */
    private function creatorGroupMembers($creators)
    {
        $alias = $ids = [];
        $this->db->formatConditionsOneField($creators, 'creatorId');
        $resultSet = $this->db->select('creator', ['creatorId', 'creatorSameAs']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            if (array_search($row['creatorSameAs'], $ids) === FALSE)
            {
                if ($row['creatorSameAs'])
                {
                    $ids[$row['creatorId']] = $row['creatorSameAs'];
                }
                else
                {
                    $ids[$row['creatorId']] = $row['creatorId'];
                }
            }
        }
        $this->db->formatConditionsOneField($ids, 'creatorSameAs');
        $resultSet = $this->db->select('creator', ['creatorSameAs', 'creatorSurname', 'creatorPrefix']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            $row['creatorPrefix'] !== FALSE ? $name = \HTML\nlToHtml($row['creatorPrefix']) . ' ' . \HTML\nlToHtml($row['creatorSurname']) :
                $name = \HTML\nlToHtml($row['creatorSurname']);
            if (!array_key_exists($row['creatorSameAs'], $alias))
            {
                $alias[$row['creatorSameAs']][] = $name;
            }
            elseif (array_search($name, $alias[$row['creatorSameAs']]) === FALSE)
            {
                $alias[$row['creatorSameAs']][] = $name;
            }
        }

        return [$ids, $alias];
    }
    /**
     * Show users bibliographies this resource belongs to
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayBibliographies($row)
    {
        if (!$this->session->getVar("setup_UserId"))
        {
            return;
        }
        $title = $this->messages->text("resources", "bibliographies");
        $this->db->formatConditions(['userbibliographyresourceResourceId' => $row['resourceId']]);
        $this->db->leftJoin('user_bibliography_resource', 'userbibliographyresourceBibliographyId', 'userbibliographyId');
        $this->db->orderBy('userbibliographyTitle');
        $recordset = $this->db->select('user_bibliography', ['userbibliographyId', 'userbibliographyTitle']);
        if (!$this->db->numRows($recordset))
        {
            return FALSE;
        }
        while ($line = $this->db->fetchRow($recordset))
        {
            $array[] = \HTML\a(
                "link",
                \HTML\nlToHtml($line['userbibliographyTitle']),
                "index.php?action=bibliography_CHOOSEBIB_CORE" .
                htmlentities("&method=init") . htmlentities("&BibId=" . $line['userbibliographyId'])
            );
        }

        return $title . ": " . implode(', ', $array);
    }
    /** Email this link to a friend
     * Only available in multi_user setup where direct (i.e. non-login) READONLY is available
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayEmailFriendLink($row)
    {
        if (WIKINDX_MULTIUSER && WIKINDX_READ_ONLY_ACCESS && WIKINDX_MAIL_USE)
        {
            $linkStyle = "link linkCiteHidden";
            $link = $this->messages->text("misc", "emailToFriend");
            // link is actually a JavaScript call
            $id = $row['resourceId'];

            return \HTML\a(
                $linkStyle,
                $link,
                "javascript:coreOpenPopup('index.php?action=email_EMAIL_CORE&amp;method=emailFriendDisplay" .
                "&amp;id=$id', 65)"
            );
        }

        return FALSE;
    }
    /**
     * Add controls for adding to and removing from resource basket
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayBasket($row)
    {
    	if ($this->browserTabID) {
    		$basket = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'basket_List');
    	} else {
    		$basket = $this->session->getVar("basket_List");
    	}
        if (is_array($basket) && !empty($basket))
        {
            if (array_search($row['resourceId'], $basket) !== FALSE)
            {
                return \HTML\a(
                    $this->icons->getClass("remove"),
                    $this->icons->getHTML("basketRemove"),
                    "index.php?" . htmlentities("action=basket_BASKET_CORE&method=remove&resourceId=" . $row['resourceId']) . 
                    	"&browserTabID=" . $this->browserTabID
                );
            }
        }

        return \HTML\a(
            $this->icons->getClass("add"),
            $this->icons->getHTML("basketAdd"),
            "index.php?" . htmlentities("action=basket_BASKET_CORE&resourceId=" . $row['resourceId']) . 
            "&browserTabID=" . $this->browserTabID
        );
    }
    /**
     * Add controls for quarantining or approving the resource
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayQuarantine($row)
    {
        if ($row['resourcemiscQuarantine'] == 'Y')
        {
            return \HTML\a(
                $this->icons->getClass("putInQuarantine"),
                $this->icons->getHTML("removeFromQuarantine"),
                "index.php?" . htmlentities("action=admin_QUARANTINE_CORE&method=approve&resourceId=" . $row['resourceId']) . 
                "&browserTabID=" . $this->browserTabID
            );
        }
        else
        {
            return \HTML\a(
                $this->icons->getClass("removeFromQuarantine"),
                $this->icons->getHTML("putInQuarantine"),
                "index.php?" . htmlentities("action=admin_QUARANTINE_CORE&method=putInQuarantine&resourceId=" . $row['resourceId']) . 
                "&browserTabID=" . $this->browserTabID
            );
        }
    }
    /**
     * Display the bibtex or wikindx key
     *
     * @param mixed $row
     *
     * @return string
     */
    private function displayKey($row)
    {
        if (GLOBALS::getUserVar("UseBibtexKey"))
        {
            $this->db->formatConditions(['importrawId' => $row['resourceId']]);
            $this->db->formatConditions(['importrawImportType' => 'bibtex']);
            $resultset = $this->db->select('import_raw', ['importrawText', 'importrawImportType']);
            if ($this->db->numRows($resultset))
            {
                $rawRow = $this->db->fetchRow($resultset);
                $rawEntries = unserialize(base64_decode($rawRow['importrawText']));
                $rawEntries = \UTF8\mb_explode(LF, $rawEntries);
                array_pop($rawEntries); // always an empty array at end so get rid of it.
                foreach ($rawEntries as $entries)
                {
                    $entry = \UTF8\mb_explode("=", $entries, 2);
                    if (!trim($entry[1]))
                    {
                        continue;
                    }
                    if (trim($entry[0]) == 'citation')
                    {
                        return trim($entry[1]);
                    }
                }
            }
        }
        // Not using bibtexKey
        if (GLOBALS::getUserVar("UseWikindxKey"))
        {
            $this->db->formatConditions(['resourcecreatorResourceId' => $row['resourceId']]);
            $name = $this->db->selectFirstField('resource_creator', 'resourcecreatorCreatorSurname');
            $name = $name ? \HTML\nlToHtml($name) : 'anon';
            $name = preg_replace("/\\W/u", '', $name);
            //  JDS suggestion for generating unique and consistent bibtex keys for every export.
            return $name . '.' . $row['resourceId'];
        }
        else
        {
            return $row['resourceBibtexKey'];
        }
    }
    /**
     * Increment the accesses counter for this resource
     */
    private function updateAccesses()
    {
        // Only increment when viewing from a list/select/search operation or after displaying metadata i.e. not from front page, lastSolo  etc.)
        if (!array_key_exists('action', $this->vars) || ($this->vars['action'] == 'front'))
        {
            return;
        }
        // Don't increment if this resource has already been viewed in this session.
        $viewedIds = unserialize(base64_decode($this->session->getVar("viewedIds")));
        if (is_array($viewedIds) && (array_search($this->vars['id'], $viewedIds) !== FALSE))
        {
            return;
        }
        $month = date('Ym');
        $this->db->formatConditions(['statisticsresourceviewsResourceId' => $this->vars['id']]);
        $this->db->formatConditions(['statisticsresourceviewsMonth' => $month]);
        if (!$this->db->selectFirstRow('statistics_resource_views', ['statisticsresourceviewsCount']))
        { // insert new month row for this resource
            $this->db->insert(
                'statistics_resource_views',
                ['statisticsresourceviewsResourceId',
                    'statisticsresourceviewsMonth',
                    'statisticsresourceviewsCount', ],
                [$this->vars['id'],
                    $month,
                    1, ]
            );
        }
        else
        {
            $this->db->formatConditions(['statisticsresourceviewsResourceId' => $this->vars['id']]);
            $this->db->formatConditions(['statisticsresourceviewsMonth' => $month]);
            $this->db->updateSingle('statistics_resource_views', $this->db->formatFields('statisticsresourceviewsCount') . "=" .
                $this->db->formatFields('statisticsresourceviewsCount') . "+" . $this->db->tidyInput(1));
        }
        if (!is_array($viewedIds))
        {
            $viewedIds = [];
        }
        $viewedIds[] = $this->vars['id'];
        $this->session->setVar("viewedIds", base64_encode(serialize($viewedIds)));
    }
    /**
     * Show random resource hyperlink
     *
     * @param mixed $thisId
     *
     * @return array
     */
    private function nextRandomLink($thisId)
    {
        $this->nextDelete = FALSE;
        /*
        if (($raw = $this->session->getVar("list_AllIds")) === FALSE)
            return FALSE;
        $allIds = unserialize(base64_decode($raw));
        $thisKey = array_search($thisId, $allIds);
        */
        $array['forward'] = \HTML\a(
            $this->icons->getClass("next"),
            $this->icons->getHTML("next"),
            htmlentities('index.php?action=resource_RESOURCEVIEW_CORE&method=random&browserTabID=' . $this->browserTabID)
        );
        /*
        if ($this->session->getVar("setup_Superadmin"))
        {
            if (array_key_exists($thisKey + 1, $allIds))
                $this->nextDelete = $allIds[$thisKey + 1];
            else if (array_key_exists($thisKey - 1, $allIds))
                $this->nextDelete = $allIds[$thisKey - 1];
        }
        */
        return $array;
    }
}
