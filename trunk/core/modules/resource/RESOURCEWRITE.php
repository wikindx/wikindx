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
 * RESOURCEWRITE -- Enter or edit a new resource -- database write
 */
class RESOURCEWRITE
{
    private $db;
    private $vars;
    private $messages;
    private $success;
    private $session;
    private $gatekeep;
    private $resourceInput = [];
    private $resourceType;
    private $typeMaps;
    private $edit = FALSE;
    private $resourceId = FALSE;
    private $userId = FALSE;
    private $resourceMap;
    private $collectionMap;
    private $publisherMap;
    private $collection;
    private $publisher;
    private $creator;
    private $keyword;
    private $userTag;
    private $badInput;
    private $navigate;

    public function __construct()
    {
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->gatekeep->init();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();

        $this->resourceMap = FACTORY_RESOURCEMAP::getInstance();
        $this->collectionMap = FACTORY_COLLECTIONMAP::getInstance();
        $this->publisherMap = FACTORY_PUBLISHERMAP::getInstance();
        $this->collection = FACTORY_COLLECTION::getInstance();
        $this->publisher = FACTORY_PUBLISHER::getInstance();
        $this->creator = FACTORY_CREATOR::getInstance();
        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->userTag = FACTORY_USERTAGS::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->navigate = FACTORY_NAVIGATE::getInstance();
        $this->typeMaps = $this->resourceMap->getTypeMap();
        $this->userId = $this->session->getVar("setup_UserId");
    }
    /**
     * Start the process
     */
    public function init()
    {
        if ($this->session->getVar("resourceFormType") == 'new') {
            if (($this->session->getVar("setup_Superadmin") != 1) && (WIKINDX_QUARANTINE)) {
                $success = $this->success->text("resourceAdd") . \HTML\p($this->success->text('quarantined'));
            } else {
                $success = $this->success->text("resourceAdd");
            }
            GLOBALS::setTplVar('heading', $this->messages->text('heading', 'newResource'));
        } else {
            $success = $this->success->text("resourceEdit");
            GLOBALS::setTplVar('heading', $this->messages->text('heading', 'editResource'));
        }
        if ($this->session->getVar("resourceLock")) {
            $this->badInput->close($this->errors->text("done", "resource"));
        }
        $this->gatherInput();
        if (!$this->edit && !$this->checkDuplicate()) {
            return;
        }
        $this->writeTables();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
        $emailClass = new EMAIL();
        $newResource = $this->edit === FALSE ? TRUE : FALSE;
        if (!$emailClass->notify($this->resourceId, $newResource)) {
            $this->badInput->close($this->errors->text("inputError", "mail", GLOBALS::getError()));
        }
        $this->session->clearArray('resourceForm');
        $this->session->setVar("resourceLock", TRUE);
        $this->navigate->resource($this->resourceId, $success);
    }

    /**
     * Deal with tinyMCE whitespace bug:  http://virtudraft.com/blog/work-around-to-the-tinymce-whitespace-bug.html
     * Trim string value
     *
     * @param string $string source text
     * @param string $charlist defined characters to be trimmed
     *
     * @see http://php.net/manual/en/function.trim.php
     *
     * @return false|string trimmed text
     */
    public function trimString($string, $charlist = NULL)
    {
        $string = htmlentities($string, FALSE, 'UTF-8');
        // blame TinyMCE!
        $string = preg_replace('/(&Acirc;&nbsp;)+/ui', '', $string);
        if ($charlist === NULL) {
            $string = trim($string);
        } else {
            $string = trim($string, $charlist);
        }

        if (empty($string)) {
            return FALSE;
        } else {
            return html_entity_decode($string, FALSE, 'UTF-8');
        }
    }
    /**
     * Check for duplicate title/resourceType
     *
     * @return bool
     */
    private function checkDuplicate()
    {
        if (array_key_exists('allowDuplicate', $this->vars)) {
            return TRUE;
        }
        $noSort = $subTitle = FALSE;
        $title = str_replace(['{', '}'], '', $this->resourceInput['resource']['resourceTitle']);
        if (array_key_exists('resourceSubtitle', $this->resourceInput['resource'])) {
            $subTitle = str_replace(['{', '}'], '', $this->resourceInput['resource']['resourceSubtitle']);
            $this->db->formatConditions($this->db->replace($this->db->replace('resourceSubtitle', '{', ''), '}', '', FALSE) .
                $this->db->like(FALSE, $subTitle, FALSE));
        } else {
            $this->db->formatConditions(['resourceSubtitle' => ' IS NULL']);
        }
        if (array_key_exists('resourceNoSort', $this->resourceInput['resource'])) {
            $noSort = str_replace(['{', '}'], '', $this->resourceInput['resource']['resourceNoSort']);
            $this->db->formatConditions($this->db->replace($this->db->replace('resourceNoSort', '{', ''), '}', '', FALSE) .
                $this->db->like(FALSE, $noSort, FALSE));
        } else {
            $this->db->formatConditions(['resourceNoSort' => ' IS NULL']);
        }
        if ($this->edit) {
            $this->db->formatConditions(['resourceId' => $this->vars['resourceId']], TRUE); // not equal to
        }
        $this->db->formatConditions(['resourceType' => $this->resourceType]);
        $this->db->formatConditions($this->db->replace($this->db->replace('resourceTitle', '{', ''), '}', '', FALSE) .
            $this->db->equal . $this->db->tidyInput($title));
        $resultset = $this->db->select('resource', $this->db->formatFields('resourceId') . ', ' .
            $this->db->replace($this->db->replace('resourceTitle', '{', ''), '}', '', FALSE) . ', ' .
            $this->db->replace($this->db->replace('resourceSubtitle', '{', ''), '}', '', FALSE) . ', ' .
            $this->db->replace($this->db->replace('resourceNoSort', '{', ''), '}', '', FALSE), TRUE, FALSE);
        if ($this->db->numRows($resultset)) {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "RESOURCEFORM.php"]));
            $rForm = new RESOURCEFORM();

            $res = FACTORY_RESOURCECOMMON::getInstance();
            $bibStyle = FACTORY_BIBSTYLE::getInstance();
            $pString = $this->errors->text('warning', 'resourceExists');
            $row = $this->db->fetchRow($resultset);
            $resultset = $res->getResource($row['resourceId']);
            $row = $this->db->fetchRow($resultset);
            $pString .= \HTML\p($bibStyle->process($row));
            $pString .= \HTML\p($this->messages->text('resources', 'duplicate') . '&nbsp;&nbsp;' .
                \FORM\checkbox(FALSE, 'allowDuplicate'));
            $rForm->init($pString);

            return FALSE;
        }

        return TRUE;
    }
    /**
     * Write tables.  When editing, typically we delete appropriate rows first then re-isert them -- it is important to keep the
     * resource ID constant across tables.
     */
    private function writeTables()
    {
        $newCollection = FALSE;
        $collectionDefaults = [];
        // resource table
        $writeArray = $this->resourceInput['resource'];
        if ($this->edit) {
            $this->db->formatConditions(['resourceId' => $this->resourceId]);
            $this->db->delete('resource');
            $writeArray['resourceId'] = $this->resourceId;
            $this->db->insert('resource', array_keys($writeArray), array_values($writeArray));
        } else {
            $this->db->insert('resource', array_keys($writeArray), array_values($writeArray));
            $this->resourceId = $this->db->lastAutoID();
        }
        // Years
        $this->db->formatConditions(['resourceyearId' => $this->resourceId]);
        $this->db->delete('resource_year');
        if (array_key_exists('resourceyear', $this->resourceInput)) {
            $writeArray = $this->resourceInput['resourceyear'];
            $writeArray['resourceyearId'] = $this->resourceId;
            $this->db->insert('resource_year', array_keys($writeArray), array_values($writeArray));
        } else { // need blank row for list operations
            $this->db->insert('resource_year', ['resourceyearId'], [$this->resourceId]);
        }
        // Page numbers
        $this->db->formatConditions(['resourcepageId' => $this->resourceId]);
        $this->db->delete('resource_page');
        if (array_key_exists('resourcepage', $this->resourceInput)) {
            $writeArray = $this->resourceInput['resourcepage'];
            $writeArray['resourcepageId'] = $this->resourceId;
            $this->db->insert('resource_page', array_keys($writeArray), array_values($writeArray));
        }
        // Abstract, notes, URLS.  NB, if editing, we don't edit URLs from this form so need to store any existing URLs and labels
        if ($this->edit) {
            $this->db->formatConditions(['resourcetextId' => $this->resourceId]);
            $resultset = $this->db->select('resource_text', ['resourcetextUrls', 'resourcetextUrlText']);
            if ($this->db->numRows($resultset)) {
                $row = $this->db->fetchRow($resultset);
                $existingUrls = $row['resourcetextUrls'];
                $existingUrlLabels = $row['resourcetextUrlText'];
            }
        }
        $this->db->formatConditions(['resourcetextId' => $this->resourceId]);
        $this->db->delete('resource_text');
        $writeArray = [];
        if (array_key_exists('resourcetext', $this->resourceInput)) {
            $writeArray['resourcetextId'] = $this->resourceId;
            if (array_key_exists('resourcetextAbstract', $this->resourceInput['resourcetext'])) {
                $writeArray['resourcetextAbstract'] = $this->resourceInput['resourcetext']['resourcetextAbstract'];
            }
            if (array_key_exists('resourcetextNote', $this->resourceInput['resourcetext'])) {
                $writeArray['resourcetextNote'] = $this->resourceInput['resourcetext']['resourcetextNote'];
            }
            // We can only edit the first URL of multiple URLs so need to remove the existing initial URL and label first
            if ($this->edit && array_key_exists('resourcetextUrl', $this->resourceInput['resourcetext'])) {
                $urlSet = FALSE;
                if (isset($existingUrls)) {
                    $urlArray = unserialize(base64_decode($existingUrls));
                    $urlArray[0] = $this->resourceInput['resourcetext']['resourcetextUrl'];
                    $writeArray['resourcetextUrls'] = base64_encode(serialize($urlArray));
                    $urlSet = TRUE;
                } else { // new URL
                    $urlArray = [];
                    $urlArray[0] = $this->resourceInput['resourcetext']['resourcetextUrl'];
                    $writeArray['resourcetextUrls'] = base64_encode(serialize($urlArray));
                    $urlSet = TRUE;
                }
                if (isset($existingUrlLabels) && $urlSet && array_key_exists('resourcetextUrlText', $this->resourceInput['resourcetext'])) {
                    $urlArray = unserialize(base64_decode($existingUrlLabels));
                    $urlArray[0] = $this->resourceInput['resourcetext']['resourcetextUrlText'];
                    $writeArray['resourcetextUrlText'] = base64_encode(serialize($urlArray));
                } elseif ($urlSet && array_key_exists('resourcetextUrlText', $this->resourceInput['resourcetext'])) {
                    $urlArray = [];
                    $urlArray[0] = $this->resourceInput['resourcetext']['resourcetextUrlText'];
                    $writeArray['resourcetextUrlText'] = base64_encode(serialize($urlArray));
                }
            } elseif (array_key_exists('resourcetextUrl', $this->resourceInput['resourcetext'])) {
                $writeArray['resourcetextUrls'] =
                    base64_encode(serialize([$this->resourceInput['resourcetext']['resourcetextUrl']]));
                if (array_key_exists('resourcetextUrlText', $this->resourceInput['resourcetext'])) {
                    $writeArray['resourcetextUrlText'] =
                    base64_encode(serialize([$this->resourceInput['resourcetext']['resourcetextUrlText']]));
                }
            }
            if (array_key_exists('resourcetextNote', $this->resourceInput)) {
                $writeArray['resourcetextAddUserIdNote'] = $this->userId;
            }
            if (array_key_exists('resourcetextAbstract', $this->resourceInput)) {
                $writeArray['resourcetextAddUserIdAbstract'] = $this->userId;
            }
            $this->db->insert('resource_text', array_keys($writeArray), array_values($writeArray));
        }
        // Collection
        $collectionId = FALSE;
        if (array_key_exists('collection', $this->resourceInput)) {
            $title = array_key_exists('collectionTitle', $this->resourceInput['collection']) ?
                $this->resourceInput['collection']['collectionTitle'] : FALSE;
            $short = array_key_exists('collectionTitleShort', $this->resourceInput['collection']) ?
                $this->resourceInput['collection']['collectionTitleShort'] : FALSE;
            $type = array_key_exists($this->resourceType, $this->collectionMap->collectionTypes) ?
                $this->collectionMap->collectionTypes[$this->resourceType] : FALSE;
            if ($title && !$collectionId = $this->collection->checkExists(FALSE, $title, $short, $type)) {
                $writeArray = $this->resourceInput['collection'];
                if ($type) {
                    $writeArray['collectionType'] = $this->collectionMap->collectionTypes[$this->resourceType];
                }
                $this->db->insert('collection', array_keys($writeArray), array_values($writeArray));
                $collectionId = $this->db->lastAutoID();
                $newCollection = $collectionId;
                // remove cache files for collections
                $this->db->deleteCache('cacheResourceCollections');
                $this->db->deleteCache('cacheMetadataCollections');
                $this->db->deleteCache('cacheResourceCollectionTitles');
                $this->db->deleteCache('cacheResourceCollectionShorts');
            }
            if ($collectionId) {
                if (($this->resourceType == 'proceedings') || ($this->resourceType == 'proceedings_article') ||
                ($this->resourceType == 'conference_paper') || ($this->resourceType == 'conference_poster')) {
                    $field = array_search('conferenceId', $this->typeMaps[$this->resourceType]['virtual']['resourcemisc']);
                } else {
                    $field = array_search('collectionId', $this->typeMaps[$this->resourceType]['virtual']['resourcemisc']);
                }
                if ($field) {
                    $this->resourceInput['resourcemisc']['resourcemisc' . $field] = $collectionId;
                }
            }
        }
        // Publisher
        $publisherId = $transPublisherId = $field1Id = $deleteCache = FALSE;
        if (array_key_exists('publisher', $this->resourceInput)) {
            $name = array_key_exists('publisherpublisherName', $this->resourceInput['publisher']) ?
                $this->resourceInput['publisher']['publisherpublisherName'] : FALSE;
            $location = array_key_exists('publisherpublisherLocation', $this->resourceInput['publisher']) ?
                $this->resourceInput['publisher']['publisherpublisherLocation'] : FALSE;
            if ($name && !$publisherId = $this->publisher->checkExists($name, $location)) {
                $writeArray = [];
                if ($name) {
                    $writeArray['publisherName'] = $name;
                }
                if ($location) {
                    $writeArray['publisherLocation'] = $location;
                }
                if (array_key_exists($this->resourceType, $this->publisherMap->publisherTypes)) {
                    $writeArray['publisherType'] = $this->publisherMap->publisherTypes[$this->resourceType];
                }
                $this->db->insert('publisher', array_keys($writeArray), array_values($writeArray));
                $publisherId = $this->db->lastAutoID();
                $deleteCache = TRUE;
            }
            if ($publisherId) {
                if (($this->resourceType == 'proceedings') || ($this->resourceType == 'proceedings_article') ||
                ($this->resourceType == 'conference_paper') || ($this->resourceType == 'conference_poster')) {
                    $field = array_search('organizerId', $this->typeMaps[$this->resourceType]['virtual']['resourcemisc']);
                } else {
                    $field = array_search('publisherId', $this->typeMaps[$this->resourceType]['virtual']['resourcemisc']);
                }
                if ($field) {
                    $this->resourceInput['resourcemisc']['resourcemisc' . $field] = $publisherId;
                }
            }
            if (($this->resourceType == 'proceedings') || ($this->resourceType == 'proceedings_article') ||
                ($this->resourceType == 'conference_paper') || ($this->resourceType == 'conference_poster')) {
                $name = array_key_exists('publisherconferenceOrganiser', $this->resourceInput['publisher']) ?
                    $this->resourceInput['publisher']['publisherconferenceOrganiser'] : FALSE;
                $location = array_key_exists('publisherconferenceLocation', $this->resourceInput['publisher']) ?
                    $this->resourceInput['publisher']['publisherconferenceLocation'] : FALSE;
                if ($name && !$field1Id = $this->publisher->checkExists($name, $location)) {
                    $writeArray = [];
                    if ($name) {
                        $writeArray['publisherName'] = $name;
                    }
                    if ($location) {
                        $writeArray['publisherLocation'] = $location;
                    }
                    if (array_key_exists($this->resourceType, $this->publisherMap->publisherTypes)) {
                        $writeArray['publisherType'] = $this->publisherMap->publisherTypes[$this->resourceType];
                    }
                    $this->db->insert('publisher', array_keys($writeArray), array_values($writeArray));
                    $field1Id = $this->db->lastAutoID();
                    $deleteCache = TRUE;
                }
                if ($field1Id) {
                    $field = array_search('publisherId', $this->typeMaps[$this->resourceType]['virtual']['resourcemisc']);
                    if ($field) {
                        $this->resourceInput['resourcemisc']['resourcemisc' . $field] = $field1Id;
                    }
                }
            }
            $name = array_key_exists('publishertransPublisherName', $this->resourceInput['publisher']) ?
                $this->resourceInput['publisher']['publishertransPublisherName'] : FALSE;
            $location = array_key_exists('publishertransPublisherLocation', $this->resourceInput['publisher']) ?
                $this->resourceInput['publisher']['publishertransPublisherLocation'] : FALSE;
            if ($name && !$transPublisherId = $this->publisher->checkExists($name, $location)) {
                $writeArray = [];
                if ($name) {
                    $writeArray['publisherName'] = $name;
                }
                if ($location) {
                    $writeArray['publisherLocation'] = $location;
                }
                if (array_key_exists($this->resourceType, $this->publisherMap->publisherTypes)) {
                    $writeArray['publisherType'] = $this->publisherMap->publisherTypes[$this->resourceType];
                }
                $this->db->insert('publisher', array_keys($writeArray), array_values($writeArray));
                $transPublisherId = $this->db->lastAutoID();
                $field = array_search('transPublisherId', $this->typeMaps[$this->resourceType]['virtual']['resourcemisc']);
                if ($field) {
                    $this->resourceInput['resourcemisc']['resourcemisc' . $field] = $transPublisherId;
                }
                $deleteCache = TRUE;
            }
            if ($deleteCache) {
                // remove cache files for publishers
                $this->db->deleteCache('cacheResourcePublishers');
                $this->db->deleteCache('cacheMetadataPublishers');
                $this->db->deleteCache('cacheConferenceOrganisers');
            }
        }
        // resource_misc table
        $writeArray = array_key_exists('resourcemisc', $this->resourceInput) ?
            $this->resourceInput['resourcemisc'] : [];
        if ($this->edit) {
            $this->db->formatConditions(['resourcemiscId' => $this->resourceId]);
            $resultset = $this->db->select('resource_misc', ['resourcemiscTag', 'resourcemiscAddUserIdResource',
                'resourcemiscMaturityIndex', 'resourcemiscQuarantine', ]);
            while ($row = $this->db->fetchRow($resultset)) {
                if ($row['resourcemiscTag']) {
                    $writeArray['resourcemiscTag'] = $row['resourcemiscTag'];
                }
                if ($row['resourcemiscAddUserIdResource']) {
                    $writeArray['resourcemiscAddUserIdResource'] = $row['resourcemiscAddUserIdResource'];
                }
                if ($row['resourcemiscMaturityIndex']) {
                    $writeArray['resourcemiscMaturityIndex'] = $row['resourcemiscMaturityIndex'];
                }
                $writeArray['resourcemiscQuarantine'] = $row['resourcemiscQuarantine'];
            }
            $writeArray['resourcemiscEditUserIdResource'] = $this->userId;
            $this->db->formatConditions(['resourcemiscId' => $this->resourceId]);
            $this->db->delete('resource_misc');
        } elseif (($this->session->getVar("setup_Superadmin") != 1) && (WIKINDX_QUARANTINE)) {
            $writeArray['resourcemiscQuarantine'] = 'Y';
        }
        $writeArray['resourcemiscId'] = $this->resourceId;
        if (!$this->edit) {
            $writeArray['resourcemiscAddUserIdResource'] = $this->userId;
        }
        $this->db->insert('resource_misc', array_keys($writeArray), array_values($writeArray));
        // Check if there are any collections and publishers left hanging
        $this->collection->removeHanging();
        $this->publisher->removeHanging();
        // Categories/subcategories
        $this->db->formatConditions(['resourcecategoryResourceId' => $this->resourceId]);
        $this->db->delete('resource_category');
        if (array_key_exists('resourcecategory', $this->resourceInput)) {
            if (array_key_exists('resourcecategoryCategories', $this->resourceInput['resourcecategory'])) {
                if (!is_array($this->resourceInput['resourcecategory']['resourcecategoryCategories'])) {
                    $this->db->insert(
                        'resource_category',
                        ['resourcecategoryResourceId', 'resourcecategoryCategoryId'],
                        [$this->resourceId, '1']
                    ); // force to 'general' category
                } else {
                    foreach ($this->resourceInput['resourcecategory']['resourcecategoryCategories'] as $cId) {
                        $this->db->insert(
                            'resource_category',
                            ['resourcecategoryResourceId', 'resourcecategoryCategoryId'],
                            [$this->resourceId, $cId]
                        );
                    }
                }
            }
            if (array_key_exists('resourcecategorySubcategories', $this->resourceInput['resourcecategory'])) {
                foreach ($this->resourceInput['resourcecategory']['resourcecategorySubcategories'] as $scId) {
                    $this->db->insert(
                        'resource_category',
                        ['resourcecategoryResourceId', 'resourcecategorySubcategoryId'],
                        [$this->resourceId, $scId]
                    );
                }
            }
        }
        // Keywords
        $this->db->formatConditions(['resourcekeywordResourceId' => $this->resourceId]);
        $this->db->delete('resource_keyword');
        if (array_key_exists('resourcekeyword', $this->resourceInput)) {
            if (array_key_exists('keywordList', $this->resourceInput['resourcekeyword'])) {
                $deleteCache = FALSE;
                foreach ($this->resourceInput['resourcekeyword']['keywordList'] as $kWord) {
                    if (!$kWord) {
                        continue;
                    }
                    if (!$kId = $this->keyword->checkExists($kWord)) {
                        $this->db->insert('keyword', ['keywordKeyword'], [$kWord]);
                        $kId = $this->db->lastAutoID();
                        $deleteCache = TRUE;
                    }
                    $this->db->insert(
                        'resource_keyword',
                        ['resourcekeywordResourceId', 'resourcekeywordKeywordId'],
                        [$this->resourceId, $kId]
                    );
                }
            }
            if ($deleteCache) {
                // remove cache files for keywords
                $this->db->deleteCache('cacheResourceKeywords');
                $this->db->deleteCache('cacheMetadataKeywords');
                $this->db->deleteCache('cacheQuoteKeywords');
                $this->db->deleteCache('cacheParaphraseKeywords');
                $this->db->deleteCache('cacheMusingKeywords');
                $this->db->deleteCache('cacheKeywords');
            }
            $this->keyword->removeHanging();
        }
        // User Tags
        $this->db->formatConditions(['usertagsUserId' => $this->userId]);
        $resultset = $this->db->select('user_tags', 'usertagsId');
        while ($row = $this->db->fetchRow($resultset)) {
            $tagIds[] = $row['usertagsId'];
        }
        if (isset($tagIds)) {
            $this->db->formatConditions(['resourceusertagsResourceId' => $this->resourceId]);
            $this->db->formatConditionsOneField($tagIds, 'resourceusertagsTagId');
            $this->db->delete('resource_user_tags');
        }
        if (array_key_exists('resourceusertags', $this->resourceInput)) {
            if (array_key_exists('userTagList', $this->resourceInput['resourceusertags'])) {
                foreach ($this->resourceInput['resourceusertags']['userTagList'] as $uWord) {
                    if (!$uWord) {
                        continue;
                    }
                    if (!$uId = $this->userTag->checkExists($uWord)) {
                        $this->db->insert('user_tags', ['usertagsTag', 'usertagsUserId'], [$uWord, $this->userId]);
                        $uId = $this->db->lastAutoID();
                    }
                    $this->db->insert(
                        'resource_user_tags',
                        ['resourceusertagsResourceId', 'resourceusertagsTagId'],
                        [$this->resourceId, $uId]
                    );
                }
            }
        }
        // Creators
        $this->db->formatConditions(['resourcecreatorResourceId' => $this->resourceId]);
        $this->db->delete('resource_creator');
        if (array_key_exists('resourcecreator', $this->resourceInput)) {
            $collectionCreators = [];
            $mainSurname = $mainId = $rowWritten = $deleteCache = FALSE;
            foreach ($this->resourceInput['resourcecreator'] as $role => $roleArray) {
                foreach ($roleArray as $order => $creatorArray) {
                    $creatorId = FALSE;
                    if ($creatorArray['surname']) { // entry in surname takes precedence
                        unset($creatorArray['select']);
                        $initials = $this->creator->formatInitials($creatorArray['initials']);
                        $creatorId = $this->creator->checkExists(
                            $creatorArray['surname'],
                            $creatorArray['firstname'],
                            $initials,
                            $creatorArray['prefix']
                        );
                        if (!$creatorId) { // new creator
                            $writeArray = [];
                            $writeArray['creatorSurname'] = $creatorArray['surname'];
                            if ($creatorArray['firstname']) {
                                $writeArray['creatorFirstname'] = $creatorArray['firstname'];
                            }
                            if ($creatorArray['prefix']) {
                                $writeArray['creatorPrefix'] = $creatorArray['prefix'];
                            }
                            if ($initials) {
                                $writeArray['creatorInitials'] = $initials;
                            }
                            $this->db->insert('creator', array_keys($writeArray), array_values($writeArray));
                            $creatorId = $this->db->lastAutoID();
                            if (!$mainSurname) {
                                $mainSurname = $creatorArray['surname'];
                                $mainId = $creatorId;
                            }
                            $deleteCache = TRUE;
                        }
                    } elseif (array_key_exists('select', $creatorArray) && $creatorArray['select']) {
                        $creatorId = $creatorArray['select'];
                    }
                    if ($creatorId) {
                        if ($role != 1) {
                            $defaultOrder = $order - 1;
                            $defaultKey = 'Creator' . $role . '_' . $defaultOrder . '_select';
                            $collectionCreators[$defaultKey] = $creatorId;
                        }
                        if (!$mainSurname) {
                            $this->db->formatConditions(['creatorId' => $creatorId]);
                            $mainSurname = $this->db->selectFirstField('creator', 'creatorSurname');
                            $mainId = $creatorId;
                        }
                        $writeArray = [];
                        $writeArray['resourcecreatorCreatorId'] = $creatorId;
                        $writeArray['resourcecreatorResourceId'] = $this->resourceId;
                        $writeArray['resourcecreatorCreatorMain'] = $mainId;
                        // remove all punctuation (keep apostrophe and dash for names such as Grimshaw-Aagaard and D'Eath)
                        $writeArray['resourcecreatorCreatorSurname'] = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s\-\']/u', '', $mainSurname));
                        $writeArray['resourcecreatorOrder'] = $order;
                        $writeArray['resourcecreatorRole'] = $role;
                        $this->db->insert('resource_creator', array_keys($writeArray), array_values($writeArray));
                        $rowWritten = TRUE;
                    }
                }
            }
            if (!$rowWritten) { // need blank row for list operations
                $this->db->insert('resource_creator', ['resourcecreatorResourceId'], [$this->resourceId]);
            }
            if ($deleteCache) {
                // remove cache files for creators
                $this->db->deleteCache('cacheResourceCreators');
                $this->db->deleteCache('cacheMetadataCreators');
            }
            $this->creator->removeHanging();
        } else { // need blank row for list operations
            $this->db->insert('resource_creator', ['resourcecreatorResourceId'], [$this->resourceId]);
            // remove cache files for creators
            $this->db->deleteCache('cacheResourceCreators');
            $this->db->deleteCache('cacheMetadataCreators');
        }
        // custom fields
        $this->db->formatConditions(['resourcecustomResourceId' => $this->resourceId]);
        $this->db->delete('resource_custom');
        if (array_key_exists('resourcecustom', $this->resourceInput)) {
            foreach ($this->resourceInput['resourcecustom'] as $id => $value) {
                $writeArray = [];
                $this->db->formatConditions(['customId' => $id]);
                if ($this->db->selectFirstField('custom', 'customSize') == 'S') {
                    $writeArray['resourcecustomShort'] = $value;
                } else {
                    $writeArray['resourcecustomLong'] = $value;
                }
                $writeArray['resourcecustomAddUserIdCustom'] = $this->session->getVar("setup_UserId");
                $writeArray['resourcecustomCustomId'] = $id;
                $writeArray['resourcecustomResourceId'] = $this->resourceId;
                $this->db->insert('resource_custom', array_keys($writeArray), array_values($writeArray));
            }
        }
        // bibliographies
        if (array_key_exists('userbibliographyresource', $this->resourceInput)) {
            if (array_key_exists('userbibliographyresourceBibliographyId', $this->resourceInput['userbibliographyresource'])) {
                $this->db->formatConditions(['userbibliographyresourceResourceId' => $this->resourceId]);
                $this->db->formatConditionsOneField(
                    $this->resourceInput['userbibliographyresource']['userbibliographyresourceBibliographyId'],
                    'userbibliographyresourceBibliographyId'
                );
                $this->db->delete('user_bibliography_resource');
                foreach ($this->resourceInput['userbibliographyresource']['userbibliographyresourceBibliographyId'] as $bId) {
                    $this->db->insert(
                        'user_bibliography_resource',
                        ['userbibliographyresourceResourceId', 'userbibliographyresourceBibliographyId'],
                        [$this->resourceId, $bId]
                    );
                }
            }
        }
        // language
        $this->db->formatConditions(['resourcelanguageResourceId' => $this->resourceId]);
        $this->db->delete('resource_language');
        if (array_key_exists('resourcelanguage', $this->resourceInput)) {
            if (array_key_exists('resourcelanguageLanguages', $this->resourceInput['resourcelanguage'])) {
                if (is_array($this->resourceInput['resourcelanguage']['resourcelanguageLanguages'])) {
                    foreach ($this->resourceInput['resourcelanguage']['resourcelanguageLanguages'] as $lId) {
                        $this->db->insert(
                            'resource_language',
                            ['resourcelanguageResourceId', 'resourcelanguageLanguageId'],
                            [$this->resourceId, $lId]
                        );
                    }
                }
            }
        }
        $this->writeBibtexKey();
        // timestamp and summary tables
        $writeArray = [];
        if ($this->edit) {
            $this->db->formatConditions(['resourcetimestampId' => $this->resourceId]);
            $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        } else {
            $this->db->updateSingle(
                'database_summary',
                $this->db->formatFields('databasesummaryTotalResources') . $this->db->equal .
                $this->db->formatFields('databasesummaryTotalResources') . $this->db->plus . $this->db->tidyInput(1)
            );
            $writeArray['resourcetimestampId'] = $this->resourceId;
            $writeArray['resourcetimestampTimestamp'] = $this->db->formatTimestamp();
            $writeArray['resourcetimestampTimestampAdd'] = $this->db->formatTimestamp();
            $this->db->insert('resource_timestamp', array_keys($writeArray), array_values($writeArray));
        }
        // If there is a new collection
        if ($newCollection && ($this->collectionMap->collectionTypes[$this->resourceType] != 'thesis')) {
            // Gather defaults for this new collection
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "collection", "COLLECTIONDEFAULTMAP.php"]));
            $defaultMap = new COLLECTIONDEFAULTMAP();
            $collectionType = $this->collectionMap->collectionTypes[$this->resourceType];
            $defaults = $collectionDefaults = [];
            foreach ($defaultMap->{$collectionType}['resource'] as $key => $value) {
                $defaults[] = 'resource' . $key;
            }
            if (array_key_exists('resource', $this->resourceInput)) {
                foreach (array_intersect(array_keys($this->resourceInput['resource']), $defaults) as $key) {
                    $collectionDefaults[$key] = $this->resourceInput['resource'][$key];
                }
            }
            $defaults = [];
            foreach ($defaultMap->{$collectionType}['resource_misc'] as $key => $value) {
                $defaults[] = 'resourcemisc' . $key;
            }
            if (array_key_exists('resourcemisc', $this->resourceInput)) {
                foreach (array_intersect(array_keys($this->resourceInput['resourcemisc']), $defaults) as $key) {
                    $collectionDefaults[$key] = $this->resourceInput['resourcemisc'][$key];
                }
            }
            $defaults = [];
            foreach ($defaultMap->{$collectionType}['resource_year'] as $key => $value) {
                $defaults[] = 'resourceyear' . $key;
            }
            if (array_key_exists('resourceyear', $this->resourceInput)) {
                foreach (array_intersect(array_keys($this->resourceInput['resourceyear']), $defaults) as $key) {
                    $collectionDefaults[$key] = $this->resourceInput['resourceyear'][$key];
                }
            }
            // add collection creators
            if (!empty($collectionCreators)) {
                $collectionDefaults['creators'] = $collectionCreators;
            }
            if (!empty($collectionDefaults)) {
                $this->db->formatConditions(['collectionId' => $newCollection]);
                $this->db->update('collection', ['collectionDefault' => base64_encode(serialize($collectionDefaults))]);
            }
        }
    }
    /**
     * Gather input and place in the $resourceInput array
     */
    private function gatherInput()
    {
        $this->session->clearArray('resourceForm');
        $this->resourceType = $this->vars['resourceType'];
        $this->session->setVar("resourceForm_resourceType", $this->resourceType);
        unset($this->vars['resourceType']);
        $input = $dates = [];
        foreach ($this->vars as $inputKey => $inputValue) {
            if ($inputKey == 'action') {
                continue;
            }
            if (is_array($inputValue)) {
                if ((array_search(0, $inputValue)) !== FALSE) {
                    unset($inputValue[0]); // remove IGNORE
                }
                if (!empty($inputValue) && ($inputKey != 'resourceusertagsTagId') && ($inputKey != 'resourcekeywordKeywords')) {
                    $input[$inputKey] = $inputValue;
                    $this->session->setVar("resourceForm_" . $inputKey, implode(',', $inputValue));
                }
            } elseif (($inputKey == 'series') && (base64_decode($inputValue) == 'IGNORE')) {
                continue;
            } else {
                $inputValue = trim($inputValue);
                if ($inputValue) {
                    $this->session->setVar("resourceForm_" . $inputKey, $inputValue);
                }
            }
            if ($inputValue && (mb_strpos($inputKey, 'customId') === 0)) {
                $input[$inputKey] = $inputValue;
            }
            // need to keep empty creator fields for existCreator() check
            elseif ($inputValue || ((mb_strpos($inputKey, 'Creator') === 0) && (mb_strpos($inputKey, 'select') === FALSE))) {
                $input[$inputKey] = $inputValue;
            }
        }
        if (array_key_exists('resourceId', $input)) { // i.e. Editing a resource
            $this->resourceId = $input['resourceId'];
            unset($input['resourceId']);
            $this->edit = TRUE;
        }
        $this->gatherInputTitle($input);
        $this->gatherInputCreators($input);
        $this->gatherInputVirtual($input);
        $this->gatherInputMiscellaneous($input);
        $this->gatherInputCustom($input);
        foreach ($this->resourceMap->getOptional() as $optional) {
            if (!array_key_exists($optional, $this->typeMaps[$this->resourceType]['optional'])) {
                continue;
            }
            if (!is_array($this->typeMaps[$this->resourceType]['optional'][$optional])) {
                continue;
            }
            foreach ($this->typeMaps[$this->resourceType]['optional'][$optional] as $table => $tableArray) {
                $table = str_replace('*', '', $table); // RESOURCEMAP: a '*' is sometimes appended to the table name in order to help with the visual display
                if (!is_array($tableArray)) {
                    continue;
                }
                foreach ($tableArray as $fieldKey => $fieldValue) {
                    $field = $table . $fieldKey;
                    if (array_key_exists($field, $input)) {
                        if ($field == 'resourcemiscPeerReviewed') {
                            $this->resourceInput[$table][$field] = 'Y';
                        } else {
                            $this->resourceInput[$table][$field] = $input[$field];
                        }
                        unset($input[$field]);
                    } elseif (($fieldKey == 'publicationDate') || ($fieldKey == 'accessDate') || ($fieldKey == 'startDate') || ($fieldKey == 'endDate')) {
                        if (array_key_exists($fieldKey, $input) && $input[$fieldKey]) {
                            $dates[$fieldKey] = $input[$fieldKey];
                            unset($input[$fieldKey]);

                            continue;
                        }
                    }
                }
            }
        }
        // Deal with date fields _ see RESOURCEMAP
        if (!empty($dates)) {
            foreach ($dates as $key => $date) {
                list($year, $month, $day) = \UTILS\splitDate($date);
                if ($key == 'publicationDate') {
                    if ($this->resourceType == 'web_article') {
                        $this->resourceInput['resourcemisc']['resourcemiscField5'] = $day;
                        $this->resourceInput['resourcemisc']['resourcemiscField6'] = $month;
                        $this->resourceInput['resourceyear']['resourceyearYear1'] = $year;
                    } else {
                        $this->resourceInput['resourcemisc']['resourcemiscField2'] = $day;
                        $this->resourceInput['resourcemisc']['resourcemiscField3'] = $month;
                        $this->resourceInput['resourceyear']['resourceyearYear1'] = $year;
                    }
                } elseif ($key == 'accessDate') {
                    $this->resourceInput['resourcemisc']['resourcemiscField2'] = $day;
                    $this->resourceInput['resourcemisc']['resourcemiscField3'] = $month;
                    $this->resourceInput['resourceyear']['resourceyearYear2'] = $year;
                } elseif ($key == 'startDate') {
                    $this->resourceInput['resourcemisc']['resourcemiscField2'] = $day;
                    $this->resourceInput['resourcemisc']['resourcemiscField3'] = $month;
                    if ($this->resourceType == 'magazine_article') {
                        $this->resourceInput['resourceyear']['resourceyearYear1'] = $year;
                    } else {
                        $this->resourceInput['resourceyear']['resourceyearYear2'] = $year;
                    }
                } elseif ($key == 'endDate') {
                    $this->resourceInput['resourcemisc']['resourcemiscField5'] = $day;
                    $this->resourceInput['resourcemisc']['resourcemiscField6'] = $month;
                    $this->resourceInput['resourceyear']['resourceyearYear3'] = $year;
                }
            }
        }
    }
    /**
     * Place title elements and optional transTitle elements into resourceInput array.  Remove accepted elements from $input array
     *
     * @param mixed $input
     */
    private function gatherInputTitle(&$input)
    {
        $this->resourceInput['resource']['resourceType'] = $this->resourceType;
        $noSortFound = FALSE;
        // tinyMCE adds P and BR tags in some cases
        $input['resourceTitle'] = str_ireplace(["<p>", "</p>", "</br>", "<br>"], '', $input['resourceTitle']);
        $input['resourceTitle'] = $this->trimString($input['resourceTitle']);
        foreach (WIKINDX_NO_SORT as $pattern) {
            if (preg_match("/^($pattern)\\s(.*)|^\\{($pattern)\\s(.*)/ui", $input['resourceTitle'], $matches)) {
                if (array_key_exists(3, $matches)) { // found second set of matches
                    $resourceTitleSort = trim(\HTML\removeNl($matches[4]));
                    $this->resourceInput['resource']['resourceTitle'] = '{' . $resourceTitleSort;
                    $this->resourceInput['resource']['resourceNoSort'] = trim(\HTML\removeNl($matches[3]));
                } else {
                    $this->resourceInput['resource']['resourceTitle'] = $resourceTitleSort = trim(\HTML\removeNl($matches[2]));
                    $this->resourceInput['resource']['resourceNoSort'] = trim(\HTML\removeNl($matches[1]));
                }
                $noSortFound = TRUE;

                break;
            }
        }
        if (!$noSortFound) {
            $this->resourceInput['resource']['resourceTitle'] = $resourceTitleSort = $input['resourceTitle'];
        }
        unset($input['resourceTitle']);
        if (array_key_exists('resourceSubtitle', $input)) {
            // tinyMCE adds P and BR tags in some cases
            $subTitle = str_ireplace(["<p>", "</p>", "</br>", "<br>"], '', $input['resourceSubtitle']);
            $subTitle = $this->trimString($subTitle);
            if ($subTitle) {
                $this->resourceInput['resource']['resourceSubtitle'] = $subTitle;
                $resourceTitleSort .= ' ' . $input['resourceSubtitle'];
            }
            unset($input['resourceSubtitle']);
        }
        $resourceTitleSort = str_replace(['{', '}'], '', \HTML\stripHtml($resourceTitleSort));
        $this->resourceInput['resource']['resourceTitleSort'] = preg_replace('/[^\p{L}\p{N}\s]/u', '', $resourceTitleSort);
        if (array_key_exists('resourceShortTitle', $input)) {
            $this->resourceInput['resource']['resourceShortTitle'] = $input['resourceShortTitle'];
            unset($input['resourceShortTitle']);
        }
        if (array_key_exists('resourceTransTitle', $input)) {
            $noSortFound = FALSE;
            foreach (WIKINDX_NO_SORT as $pattern) {
                if (preg_match("/^($pattern)\\s(.*)|^\\{($pattern)\\s(.*)/ui", $input['resourceTransTitle'], $matches)) {
                    if (array_key_exists(3, $matches)) { // found second set of matches
                        $this->resourceInput['resource']['resourceTransTitle'] = '{' . trim(\HTML\removeNl($matches[4]));
                        $this->resourceInput['resource']['resourceTransNoSort'] = trim(\HTML\removeNl($matches[3]));
                    } else {
                        $this->resourceInput['resource']['resourceTransTitle'] = trim(\HTML\removeNl($matches[2]));
                        $this->resourceInput['resource']['resourceTransNoSort'] = trim(\HTML\removeNl($matches[1]));
                    }
                    $noSortFound = TRUE;

                    break;
                }
            }
            if (!$noSortFound) {
                $this->resourceInput['resource']['resourceTransTitle'] = $input['resourceTransTitle'];
            }
            unset($input['resourceTransTitle']);
            if (array_key_exists('resourceTransSubtitle', $input)) {
                $this->resourceInput['resource']['resourceTransSubtitle'] = $input['resourceTransSubtitle'];
                unset($input['resourceTransSubtitle']);
            }
            if (array_key_exists('resourceTransShortTitle', $input)) {
                $this->resourceInput['resource']['resourceTransShortTitle'] = $input['resourceTransShortTitle'];
                unset($input['resourceTransShortTitle']);
            }
        }
    }
    /**
     * Place all creator elements in the resourceInput array
     *
     * @param mixed $input
     */
    private function gatherInputCreators(&$input)
    {
        if (!array_key_exists('resourcecreator', $this->typeMaps[$this->resourceType])) {
            return;
        }
        $removeFromInput = [];
        // extract all creator fields from $input
        foreach ($input as $key => $value) {
            if (mb_strpos($key, 'Creator') === 0) {
                $removeFromInput[] = $key;
                $explode = \UTF8\mb_explode('_', $key);
                $this->resourceInput['resourcecreator'][trim($explode[0], 'Creator')][$explode[1] + 1][$explode[2]] = $value;
            }
        }
        // remove creator fields from $input
        foreach ($removeFromInput as $removeKey) {
            unset($input[$removeKey]);
        }
    }
    /**
     * Place all custom elements in the resourceInput array
     *
     * @param mixed $input
     */
    private function gatherInputCustom(&$input)
    {
        $removeFromInput = [];
        // extract all custom fields from $input
        foreach ($input as $key => $value) {
            if (mb_strpos($key, 'customId') === 0) {
                $removeFromInput[] = $key;
                $this->resourceInput['resourcecustom'][trim($key, 'customId')] = $value;
            }
        }
        // remove custom fields from $input
        foreach ($removeFromInput as $removeKey) {
            unset($input[$removeKey]);
        }
    }
    /**
     * Place all virtual elements from RESOURCEMAP in resourceInput array
     *
     * @param mixed $input
     */
    private function gatherInputVirtual(&$input)
    {
        if (!array_key_exists('virtual', $this->typeMaps[$this->resourceType])) {
            return;
        }
        foreach ($this->typeMaps[$this->resourceType]['virtual'] as $table => $tableArray) {
            foreach ($tableArray as $fieldKey => $fieldValue) {
                if (array_key_exists($fieldValue, $this->typeMaps[$this->resourceType]['virtualFields'])) {
                    $found = FALSE;
                    foreach ($this->typeMaps[$this->resourceType]['virtualFields'][$fieldValue] as $vField) {
                        if (array_key_exists($vField, $input)) {
                            $field = $table . $fieldKey;
                            if (($vField == 'publishertransPublisherName') || ($vField == 'publishertransPublisherLocation')) {
                                $this->resourceInput['publisher'][$vField] = $input[$vField];
                            } else {
                                $this->resourceInput['collection'][$vField] = $input[$vField];
                            }
                            unset($input[$fieldValue]);
                            $found = TRUE;
                        }
                    }
                    if (!$found) {
                        $field = $table . $fieldKey;
                        if (array_key_exists($fieldValue, $input)) {
                            $this->resourceInput[$table][$field] = $input[$fieldValue];
                            unset($input[$fieldValue]);
                        }
                    }
                }
            }
        }
    }
    /**
     * Place all miscellaneous elements in resourceInput array
     *
     * @param mixed $input
     */
    private function gatherInputMiscellaneous(&$input)
    {
        if (array_key_exists('resourcecategoryCategories', $input)) {
            $this->resourceInput['resourcecategory']['resourcecategoryCategories'] = $input['resourcecategoryCategories'];
            unset($input['resourcecategoryCategories']);
        }
        if (array_key_exists('resourcecategorySubcategories', $input)) {
            $this->resourceInput['resourcecategory']['resourcecategorySubcategories'] = $input['resourcecategorySubcategories'];
            foreach ($input['resourcecategorySubcategories'] as $subCat) {
                $this->db->formatConditions(['subcategoryId' => $subCat]);
                $cId = $this->db->selectFirstField('subcategory', 'subcategoryCategoryId');
                if (!array_key_exists('resourcecategoryCategories', $this->resourceInput['resourcecategory'])) {
                    $this->resourceInput['resourcecategory']['resourcecategoryCategories'][] = $cId;
                } elseif (array_search($cId, $this->resourceInput['resourcecategory']['resourcecategoryCategories']) === FALSE) {
                    $this->resourceInput['resourcecategory']['resourcecategoryCategories'][] = $cId;
                }
            }
            unset($input['resourcecategorySubcategories']);
        }
        if (!array_key_exists('resourcecategory', $this->resourceInput)) { // force to 'General'
            $this->resourceInput['resourcecategory']['resourcecategoryCategories'] = 1;
        }
        if (array_key_exists('resourcekeywordKeywords', $input)) {
            $this->resourceInput['resourcekeyword']['resourcekeywordKeywords'] = $input['resourcekeywordKeywords'];
            unset($input['resourcekeywordKeywords']);
        }
        if (array_key_exists('keywordList', $input)) {
            foreach (\UTF8\mb_explode(',', $input['keywordList']) as $word) {
                $this->resourceInput['resourcekeyword']['keywordList'][] = trim($word);
            }
            unset($input['keywordList']);
        }
        if (array_key_exists('language', $input)) {
            $this->resourceInput['resourcelanguage']['resourcelanguageLanguages'] = $input['language'];
            unset($input['language']);
        }
        if (array_key_exists('resourceusertagsTagId', $input)) {
            $this->resourceInput['resourceusertags']['resourceusertagsTagId'] = $input['resourceusertagsTagId'];
            unset($input['resourceusertagsTagId']);
        }
        if (array_key_exists('userTagList', $input)) {
            foreach (\UTF8\mb_explode(',', $input['userTagList']) as $word) {
                $this->resourceInput['resourceusertags']['userTagList'][] = trim($word);
            }
            unset($input['userTagList']);
        }
        if (array_key_exists('bibliographies', $input)) {
            $this->resourceInput['userbibliographyresource']['userbibliographyresourceBibliographyId'] = $input['bibliographies'];
            unset($input['userbibliographyresource']);
        }
        if (array_key_exists('resourcetextAbstract', $input) && $input['resourcetextAbstract']) {
            $this->resourceInput['resourcetext']['resourcetextAbstract'] = $input['resourcetextAbstract'];
            unset($input['resourcetextAbstract']);
        }
        if (array_key_exists('resourcetextNote', $input) && $input['resourcetextNote']) {
            $this->resourceInput['resourcetext']['resourcetextNote'] = $input['resourcetextNote'];
            unset($input['resourcetextNote']);
        }
        if (array_key_exists('resourcetextUrl', $input) && ($input['resourcetextUrl'] != 'http://') && ($input['resourcetextUrl'] != 'https://')) {
            $this->resourceInput['resourcetext']['resourcetextUrl'] = $input['resourcetextUrl'];
            unset($input['resourcetextUrl']);
        }
        if (array_key_exists('resourcetext', $this->resourceInput) && array_key_exists('resourcetextUrl', $this->resourceInput['resourcetext'])
            && array_key_exists('resourcetextUrlText', $input) && $input['resourcetextUrlText']) {
            $this->resourceInput['resourcetext']['resourcetextUrlText'] = $input['resourcetextUrlText'];
            unset($input['resourcetextUrlText']);
        }
        if (array_key_exists('resourceIsbn', $input) && $input['resourceIsbn']) {
            $this->resourceInput['resource']['resourceIsbn'] = $input['resourceIsbn'];
            unset($input['resourceIsbn']);
        }
        if (array_key_exists('resourceDoi', $input) && $input['resourceDoi'] && ($input['resourceDoi'] != 'doi:')) {
            $this->resourceInput['resource']['resourceDoi'] = $input['resourceDoi'];
            unset($input['resourceDoi']);
        }
    }
    /**
     * Write the bibtexKey field for new or edited resources
     */
    private function writeBibtexKey()
    {
        $config = FACTORY_BIBTEXCONFIG::getInstance();
        $config->bibtex();
        $recordset = $this->db->select('resource', 'resourceBibtexKey');
        while ($row = $this->db->fetchRow($recordset)) {
            $bibtexKeys[] = $row['resourceBibtexKey'];
        }
        $letters = range('a', 'z');
        $sizeof = count($letters);
        $this->db->formatConditions(['resourceyearId' => $this->resourceId]);
        $recordset = $this->db->select(['resource_year'], ['resourceyearYear1',
            'resourceyearYear2', 'resourceyearYear3', 'resourceyearYear4', ]);
        $row = $this->db->fetchRow($recordset);
        if ($row['resourceyearYear1']) {
            $year = $row['resourceyearYear1'];
        } elseif ($row['resourceyearYear2']) {
            $year = $row['resourceyearYear2'];
        } elseif ($row['resourceyearYear3']) {
            $year = $row['resourceyearYear3'];
        } elseif ($row['resourceyearYear4']) {
            $year = $row['resourceyearYear4'];
        } else {
            $year = FALSE;
        }
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorMain');
        $this->db->formatConditions(['resourcecreatorResourceId' => $this->resourceId]);
        $this->db->formatConditions(['resourcecreatorOrder' => '1']);
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourcecreatorRole', TRUE, FALSE);
        $this->db->limit(1, 0); // pick just the first one
        $recordset = $this->db->select(['resource_creator'], ['creatorSurname', 'creatorPrefix']);
        $row = $this->db->fetchRow($recordset);
        $keyMade = FALSE;
        if ((!is_array($row) || !array_key_exists('creatorSurname', $row) || !$row['creatorSurname'])) { // anonymous
            $base = 'anon' . $year;
        } else {
            $prefix = '';
            if ($row['creatorPrefix']) {
                $prefix = utf8_decode($row['creatorPrefix']);
                foreach ($config->bibtexSpChPlain as $key => $value) {
                    $prefix = preg_replace("/" . \UTF8\mb_chr($key) . "/u", $value, $prefix);
                }
                $prefix = preg_replace("/\\W/u", '', $prefix);
            }
            $surname = utf8_decode($row['creatorSurname']);
            foreach ($config->bibtexSpChPlain as $key => $value) {
                $surname = preg_replace("/" . \UTF8\mb_chr($key) . "/u", $value, $surname);
            }
            $surname = preg_replace("/\\W/u", '', $surname);
            $base = $prefix . $surname . $year;
        }
        $bibtexKey = $base;
        for ($i = 0; $i < $sizeof; $i++) {
            if (array_search($bibtexKey, $bibtexKeys) === FALSE) {
                $keyMade = TRUE;

                break;
            }
            $bibtexKey = $base . $letters[$i];
        }
        if (!$keyMade) {
            $bibtexKey = $base . '.' . $this->resourceId; // last resort
        }
        $bibtexKey = str_replace(' ', '', $bibtexKey);
        $this->db->formatConditions(['resourceId' => $this->resourceId]);
        $this->db->update('resource', ['resourceBibtexKey' => $bibtexKey]);
    }
}
