<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * RESOURCECOMMON class
 *
 * Common functions for quotes and paraphrases
 *
 * @package wikindx\core\resources
 */
class RESOURCECOMMON
{
    /** boolean */
    public $withUnixTimestamp = FALSE;
    /** array */
    public $listFields;
    /** array */
    public $resourceFields;
    /** array */
    public $resourceMiscFields;
    /** array */
    public $textFields;
    /** array */
    public $yearFields;
    /** array */
    public $pageFields;
    /** array */
    public $summaryFields;
    /** array */
    public $timestampFields;
    /** array */
    public $unixTimestampFields;
    /** array */
    public $publisherFields;
    /** array */
    public $collectionFields;
    /** array */
    public $userFields;
    /** array */
    public $attachmentFields;
    /** array */
    public $categoryFields;
    /** array */
    public $keywordFields;
    /** array */
    public $musingFields;
    /** array */
    public $languageFields;
    /** boolean */
    public $groupByResourceId = TRUE;
    /** string */
    public $limit = 0; // Used to externally set a limit on getResource(). If 0, no limit.
    /** object */
    private $db;
    /** object */
    private $session;
    /** object */
    private $commonBib;
    /** object */
    private $commonBrowse;
    /** object */
    private $messages;
    /** array */
    private $highlightPatterns = [];
    /** object */
    private $errors;
    /** object */
    private $resourceMap;
    /** object */
    private $gatekeep; //!< there are some functions requiring write access

    /**
     * RESOURCECOMMON
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();

        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->resourceMap = FACTORY_RESOURCEMAP::getInstance();
        include_once("core/browse/BROWSECOMMON.php");
        $this->commonBrowse = new BROWSECOMMON();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
    }
    /**
     * Set up highlighting of text if required (after a search operation).
     *
     * Creates array of regexp expressions for search terms
     */
    public function setHighlightPatterns()
    {
        if ($this->session->getVar("search_Highlight") && empty($this->highlightPatterns)) {
            $searchTerms = UTF8::mb_explode(",", $this->session->getVar("search_Highlight"));
            foreach ($searchTerms as $term) {
                //				$this->highlightPatterns[] = "/($term)(?!\S*\" \S*>)/ui";
                $this->highlightPatterns[] = "/($term)(?=[^>]*(<|$))/u";
            }
        }
    }
    /**
     * Do pattern highlighting after a SEARCH operation
     *
     * @param string $text
     *
     * @return string
     */
    public function doHighlight($text)
    {
        if ($this->session->getVar("search_Highlight")) {
            if (empty($this->highlightPatterns)) {
                $this->setHighlightPatterns();
            }
            /**
             * Temporarily replace any URL - works for just one URL in the output string.
             */
            $url = FALSE;
            if (preg_match("/(<a.*>)/Uui", $text, $match)) {
                $url = preg_quote($match[1], '/');
                $text = preg_replace("/$url/u", "OSBIB__URL__OSBIB", $text);
            }
            // Recover any URL
            if ($url) {
                $text = str_replace("OSBIB__URL__OSBIB", $match[1], $text);
            }
            // UTF8 safe
            $text = preg_replace($this->highlightPatterns, \HTML\span("$1", "highlight"), $text);
        }

        return $text;
    }
    /**
     * show citations about this resource
     *
     * @param int $resourceId
     * @param bool $countOnly Default is FALSE
     *
     * @return false|string
     */
    public function showCitations($resourceId, $countOnly = FALSE)
    {
        $search = "[cite]$resourceId";
        // Abstract and note and metadata
        $this->commonBrowse->userBibCondition('resourcemetadataResourceId');
        $this->db->leftJoin('resource_text', 'resourcetextId', 'resourcemetadataResourceId');
        $matchAgainst[] = $this->db->fulltextSearch(['resourcetextAbstract'], $search);
        $matchAgainst[] = $this->db->fulltextSearch(['resourcetextNote'], $search);
        $matchAgainst[] = $this->db->fulltextSearch(['resourcemetadataText'], $search);
        $this->db->formatConditions(join(' ' . $this->db->or . ' ', $matchAgainst));
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute(
            'resource_metadata',
            [['resourcemetadataResourceId' => 'rId']]
        ));
        if ($countOnly) {
            $resultSet = $this->db->query($this->db->selectNoExecuteFromSubQuery(
                FALSE,
                'rId',
                $this->db->subQuery($this->db->union($unions), 'u')
            ));
            if ($this->db->numRows($resultSet)) {
                return \HTML\a(
                    'link',
                    $this->messages->text("resources", "citedResources", ' ' .
                    \HTML\nlToHtml($this->commonBib->displayBib())),
                    'index.php?action=list_LISTSOMERESOURCES_CORE&method=citeProcess&id=' . $resourceId
                );
            } else {
                return FALSE;
            }
        } else {
            return $this->db->union($unions);
        }
    }
    /**
     * Insert new resource into database.
     *
     * @todo Needs code-review!  Is this even where this function should be?
     *
     * @param array $v POST/GET variables needed to create resource (the actual required and optional keys should be specified)
     * @param bool $alreadyExisted Indicates if the resource already exists. Reference, default is FALSE
     * @param array $errs Array to push any errors that occur. Reference, default is empty array
     *
     * @return mixed FALSE|Resource ID Id of the (new or already existing) resource or returns FALSE on failure.
     */
    public function insert($v, &$alreadyExisted = FALSE, &$errs = [])
    {
        $this->gatekeep->init();

        if (trim($v['type'])) {
            $type = $v['type'];
        } else {
            array_push($errs, $this->error->text('inputError', 'missing', 'type'));
        }

        $id = FALSE;
        $tables = $this->resourceMap->getTables($type); // resource is always first value

        foreach ($tables as $tableName) {
            $fieldPrefix = str_replace('_', '', $tableName);

            $fields = [];
            if ($tableName == "resource") {
                $fields[$fieldPrefix . 'Type'] = $type;
            }
            if ($id !== FALSE) {
                $fields[$fieldPrefix . 'Id'] = $id;
            }

            $keys = $this->db->listFields($tableName);
            foreach ($keys as $key) {
                $base = mb_substr($key, mb_strlen($fieldPrefix));
                $base[0] = mb_strtolower($base[0]);

                if (array_key_exists($base, $v) && trim($v[$base])) {
                    $fields[$key] = $v[$base];
                }
            }

            $msgKeys = $this->resourceMap->getMsgKeys($type);
            foreach ($msgKeys as $msgKey) {
                if (array_key_exists($msgKey, $v) && trim($v[$msgKey])) {
                    addField(
                        $fields,
                        $fieldPrefix,
                        $this->resourceMap->lookupDBfield($type, $msgKey),
                        $v[$msgKey]
                    );
                }
            }
            // don't add table if there is no information or if there are errors
            if (!empty($fields) && empty($errs)) {
                $this->db->insert($tableName, array_keys($fields), array_values($fields));

                // @todo I currently assume that db->insert doesn't return any errors!!!
                // @mark -- insert, delete, select, update use SQL::query() which places db error messages in the public SQL->error string variable
                if ($id === FALSE && $tableName == 'resource') {
                    $id = $this->db->lastAutoId();
                } elseif ($id == FALSE) {
                    // @todo need to push the error onto $errors before returning FALSE
                    return FALSE;
                }
            }
        }

        return $id;
    }
    /**
     * Return resultset for one or more resources.
     *
     * NB -- resource ID conditions can be set elsewhere
     * $orderX is for footnote ordering in CITESTYLE/CITEFORMAT or $order1 is for the ordering of CMS output.
     *
     * @param array $ids Resource IDs, default is FALSE
     * @param string $order1 Default is FALSE
     * @param string $order2 Default is FALSE
     * @param string $order3 Default is FALSE
     * @param string $subQuery Can come from elsewhere such as core/cms/CMS.php. Default is FALSE
     * @param bool $sqlOnly Don't execute, just return SQL statement. Default is FALSE
     *
     * @return mixed SQL statement|SQL resource object
     */
    public function getResource($ids = FALSE, $order1 = FALSE, $order2 = FALSE, $order3 = FALSE, $subQuery = FALSE, $sqlOnly = FALSE)
    {
        $this->tableSetup();
        if (is_array($ids)) {
            $this->db->formatConditionsOneField($ids, 'resourceId');
        } elseif ($ids) {
            $this->db->formatConditions(['resourceId' => $ids]);
        }
        if (!$subQuery) {
            $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'resourceId');
            if ($this->withUnixTimestamp) {
                $subQuery = $this->db->subQuery($this->db->queryNoExecute($this->db->selectNoExecuteWithExceptions(
                    'resource',
                    array_merge(
                        $this->resourceFields,
                        $this->unixTimestampFields,
                        [['resourcecreatorCreatorSurname' => 'creatorSurname'], 'resourcecreatorResourceId']
                    ),
                    TRUE
                )), 't1');
            } else {
                $subQuery = $this->db->subQuery($this->db->queryNoExecute($this->db->selectNoExecute(
                    'resource',
                    array_merge(
                        $this->resourceFields,
                        [['resourcecreatorCreatorSurname' => 'creatorSurname'], 'resourcecreatorResourceId']
                    ),
                    TRUE
                )), 't1');
            }
        }
        $this->db->leftJoin('resource_text', "resourcetextId", 'resourceId');
        $this->db->leftJoin('resource_misc', "resourcemiscId", 'resourceId');
        $this->db->leftJoin('resource_year', "resourceyearId", 'resourceId');
        $this->db->leftJoin('resource_page', "resourcepageId", 'resourceId');
        $this->db->leftJoin('resource_timestamp', "resourcetimestampId", 'resourceId');
        $this->db->leftJoin('resource_summary', 'resourcesummaryId', 'resourceId');
        $this->db->leftJoin('publisher', "publisherId", 'resourcemiscPublisher');
        $this->db->leftJoin('collection', "collectionId", 'resourcemiscCollection');
        $this->db->leftJoin('users', $this->db->formatFields('usersId'), $this->db->caseWhen(
            'resourcemiscEditUserIdResource',
            'IS NOT NULL',
            'resourcemiscEditUserIdResource',
            'resourcemiscAddUserIdResource'
        ), FALSE);
        if ($order1) {
            $this->db->orderBy($order1, FALSE);
        }
        if ($order2) {
            $this->db->orderBy($order2, FALSE);
        }
        if ($order3) {
            $this->db->orderBy($order3, FALSE);
        }
        //		if($this->groupByResourceId)
        //			$this->db->groupBy('resourceId');
        if ($this->withUnixTimestamp) {
            if ($sqlOnly) {
                $fields = array_merge(
                    $this->resourceFields,
                    $this->pageFields,
                    $this->yearFields,
                    $this->resourceMiscFields,
                    $this->summaryFields,
                    $this->textFields,
                    $this->timestampFields,
                    $this->unixTimestampFields,
                    $this->userFields,
                    $this->collectionFields,
                    $this->publisherFields
                );
                //				if($this->groupByResourceId)
                //					$this->db->group .= ', ' . $this->db->formatFields($fields);
                return $this->db->selectNoExecuteFromSubQuery(
                    FALSE,
                    $this->db->formatFields($fields, TRUE),
                    $subQuery,
                    FALSE,
                    FALSE,
                    TRUE
                );
            }
            $fields = array_merge(
                $this->resourceFields,
                $this->pageFields,
                $this->yearFields,
                $this->resourceMiscFields,
                $this->summaryFields,
                $this->textFields,
                $this->timestampFields,
                $this->unixTimestampFields,
                $this->userFields,
                $this->collectionFields,
                $this->publisherFields
            );
            if ($this->limit) {
                $this->db->limit($this->limit, 0);
            }
            //			if($this->groupByResourceId)
            //				$this->db->group .= ', ' . $this->db->formatFields($fields);
            return $this->db->query($this->db->selectNoExecuteFromSubQuery(
                FALSE,
                $this->db->formatFields($fields, TRUE),
                $subQuery,
                FALSE,
                FALSE,
                TRUE
            ));
        } else {
            if ($sqlOnly) {
                $fields = array_merge(
                    $this->resourceFields,
                    $this->pageFields,
                    $this->yearFields,
                    $this->resourceMiscFields,
                    $this->summaryFields,
                    $this->textFields,
                    $this->timestampFields,
                    $this->userFields,
                    $this->collectionFields,
                    $this->publisherFields
                );
                //				if($this->groupByResourceId)
                //					$this->db->group .= ', ' . $this->db->formatFields($fields);
                return $this->db->selectNoExecuteFromSubQuery(
                    FALSE,
                    $this->db->formatFields($fields),
                    $subQuery,
                    FALSE,
                    FALSE,
                    TRUE
                );
            }
            $fields = array_merge(
                $this->resourceFields,
                $this->pageFields,
                $this->yearFields,
                $this->resourceMiscFields,
                $this->summaryFields,
                $this->textFields,
                $this->timestampFields,
                $this->userFields,
                $this->collectionFields,
                $this->publisherFields
            );
            if ($this->limit) {
                $this->db->limit($this->limit, 0);
            }
            //			if($this->groupByResourceId)
            //				$this->db->group .= ', ' . $this->db->formatFields($fields);
            return $this->db->query($this->db->selectNoExecuteFromSubQuery(
                FALSE,
                $this->db->formatFields($fields),
                $subQuery,
                FALSE,
                FALSE,
                TRUE
            ));
        }
    }
    /**
     * set DB stuff including list operation fields
     */
    public function tableSetup()
    {
        // table fields used when listing resources
        $this->listFields = ['resourceId', 'creatorSurname', 'resourceType', 'resourceTitle', 'resourceSubtitle', 'resourceShortTitle',
            'resourceTransTitle', 'resourceTransSubtitle', 'resourceTransShortTitle', 'resourceField1', 'resourceField2', 'resourceField3',
            'resourceField4', 'resourceField5', 'resourceField6', 'resourceField7', 'resourceField8', 'resourceField9', 'resourceNoSort',
            'resourceTransNoSort', 'resourceIsbn', 'resourceBibtexKey', 'resourceDoi', 'resourcetextId', 'resourcetextNote', 'resourcetextAbstract',
            'resourcetextUrls', 'resourcetextUrlText', 'resourcetextEditUserIdNote', 'resourcetextAddUserIdNote', 'resourcetextEditUserIdAbstract',
            'resourcetextAddUserIdAbstract', 'resourceyearId', 'resourceyearYear1', 'resourceyearYear2', 'resourceyearYear3', 'resourceyearYear4',
            'resourcepageId', 'resourcepagePageStart', 'resourcepagePageEnd', 'resourcesummaryId', 'resourcesummaryQuotes', 'resourcesummaryParaphrases',
            'resourcesummaryMusings', 'resourcetimestampId', 'resourcetimestampTimestamp', 'resourcetimestampTimestampAdd', 'publisherId', 'publisherName',
            'publisherLocation', 'publisherType', 'collectionId', 'collectionTitle', 'collectionTitleShort', 'collectionType', 'usersId', 'usersUsername',
            'usersFullname', 'resourcemiscId', 'resourcemiscCollection', 'resourcemiscPublisher', 'resourcemiscField1', 'resourcemiscField2',
            'resourcemiscField3', 'resourcemiscField4', 'resourcemiscField5', 'resourcemiscField6', 'resourcemiscTag', 'resourcemiscAddUserIdResource',
            'resourcemiscEditUserIdResource', 'resourcemiscMaturityIndex', 'resourcemiscPeerReviewed', 'resourcemiscQuarantine', ];

        $this->resourceFields = ['resourceId', 'resourceType', 'resourceTitle', 'resourceSubtitle', 'resourceShortTitle', 'resourceTitleSort',
            'resourceTransTitle', 'resourceTransSubtitle', 'resourceTransShortTitle', 'resourceField1', 'resourceField2', 'resourceField3', 'resourceField4',
            'resourceField5', 'resourceField6', 'resourceField7', 'resourceField8', 'resourceField9', 'resourceNoSort', 'resourceTransNoSort', 'resourceIsbn',
            'resourceBibtexKey', 'resourceDoi', ];

        $this->resourceMiscFields = ['resourcemiscId', 'resourcemiscCollection', 'resourcemiscPublisher', 'resourcemiscField1', 'resourcemiscField2',
            'resourcemiscField3', 'resourcemiscField4', 'resourcemiscField5', 'resourcemiscField6', 'resourcemiscTag', 'resourcemiscAddUserIdResource',
            'resourcemiscEditUserIdResource', 'resourcemiscMaturityIndex', 'resourcemiscPeerReviewed', 'resourcemiscQuarantine', ];

        $this->textFields = ['resourcetextId', 'resourcetextNote', 'resourcetextAbstract', 'resourcetextUrls', 'resourcetextUrlText',
            'resourcetextEditUserIdNote', 'resourcetextAddUserIdNote', 'resourcetextEditUserIdAbstract', 'resourcetextAddUserIdAbstract', ];

        $this->yearFields = ['resourceyearId', 'resourceyearYear1', 'resourceyearYear2', 'resourceyearYear3', 'resourceyearYear4'];

        $this->pageFields = ['resourcepageId', 'resourcepagePageStart', 'resourcepagePageEnd'];

        $this->summaryFields = ['resourcesummaryId', 'resourcesummaryQuotes', 'resourcesummaryParaphrases', 'resourcesummaryMusings'];

        $this->timestampFields = ['resourcetimestampId', 'resourcetimestampTimestamp', 'resourcetimestampTimestampAdd'];

        $this->unixTimestampFields = [["UNIX_TIMESTAMP(resourcetimestampTimestampAdd)" => 'addUnixTimestamp'],
            ["UNIX_TIMESTAMP(resourcetimestampTimestamp)" => 'editUnixTimestamp'], ];

        $this->publisherFields = ['publisherId', 'publisherName', 'publisherLocation', 'publisherType'];

        $this->collectionFields = ['collectionId', 'collectionTitle', 'collectionTitleShort', 'collectionType'];

        $this->userFields = ['usersId', 'usersUsername', 'usersFullname'];

        $this->attachmentFields = ['resourceattachmentsId', 'resourceattachmentsTimestamp',
            'resourceattachmentsResourceId', 'resourceattachmentsFileName', 'resourceattachmentsHashFilename', ];

        $this->categoryFields = ['resourcecategoryId', 'resourcecategoryCategories', 'resourcecategorySubcategories'];

        $this->keywordFields = ['resourcekeywordId', 'resourcekeywordResourceId', 'resourcekeywordKeywordId'];

        $this->languageFields = ['resourcelanguageId', 'resourcelanguageResourceId', 'resourcelanguageLanguageId'];

        $this->metadataFields = ['resourcemetadataPrivate', 'resourcemetadataAddUserId'];
    }
    /**
     * Format field name
     *
     * @param string $prefix
     * @param string $base
     *
     * @return string
     */
    private function fieldName($prefix, $base)
    {
        $base[0] = mb_strtoupper($base[0]);

        return $prefix . $base;
    }
    /**
     * Add field to fields array
     *
     * @param array $fields Reference
     * @param string $prefix
     * @param string $name
     * @param string $value
     */
    private function addField(&$fields, $prefix, $name, $value)
    {
        $fields[fieldName($prefix, $name)] = $value;
    }
}
