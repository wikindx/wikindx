<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * RESOURCEMAP
 *
 * Provides mappings between resource types and the database tables.
 *
 * @package wikindx\core\resources
 */
class RESOURCEMAP
{
    /** array */
    private $types;
    /** array */
    private $typesRaw;
    /** array */
    private $typeMap = []; //@< used to lookup the readable names given database names
    /** array */
    private $returnTypeMap = []; //@< optimized version of $typeMap
    /** array */
    private $reverseTypeMap = []; //@< used to lookup the database names given readable names
    /** array */
    private $optional = [];

    /**
     * RESOURCEMAP
     */
    public function __construct()
    {
        $config = FACTORY_CONFIG::getInstance();
        $this->loadMap();

        foreach ($config->WIKINDX_DEACTIVATE_RESOURCE_TYPES as $removeType)
        {
            if (($index = array_search($removeType, $this->types)) !== FALSE)
            {
                unset($this->types[$index]);
                unset($this->typeMap[$removeType]);
            }
        }
    }
    /**
     * Get readable name of resource type from database field
     *
     * @param string $type
     * @param string $tableName
     * @param string $dbName
     *
     * @return string
     */
    public function lookupReadableName($type, $tableName, $dbName)
    {
        return $this->typeMap[$type][$tableName][$dbName];
    }
    /**
     * Get database field from readable name of resource type
     *
     * @param string $type
     * @param string $readableName
     *
     * @return string
     */
    public function lookupDBfield($type, $readableName)
    {
        if (array_key_exists($readableName, $this->reverseTypeMap[$type]))
        {
            return $this->reverseTypeMap[$type][$readableName];
        }
        else
        {
            return $readableName;
        }
    }
    /**
     * Return list of all required fields for each resource type
     *
     * @return array
     */
    public function getRequired()
    {
        $array = [];
        foreach ($this->types as $type)
        {
            if (array_key_exists('required', $this->typeMap[$type]))
            {
                $array[$type] = $this->typeMap[$type]['required'];
            }
        }

        return $array;
    }
    /**
     * Return list of db tables used for each resource type.
     *
     * Ensure the resource table exists and always comes first.
     *
     * @param string $type
     *
     * @return array of db tables
     */
    public function getTables($type)
    {
        $ret = [];
        foreach ($this->typeMap[$type] as $key => $value)
        {
            if ($key == 'optional')
            {
                foreach ($this->typeMap[$type]['optional'] as $optKey => $optValue)
                {
                    if (array_search($optKey, $this->optional) !== FALSE)
                    {
                        foreach ($this->typeMap[$type]['optional'][$optKey] as $typeOptKey => $typeOptValue)
                        {
                            if (($typeOptKey == 'title') || ($typeOptKey == 'insertBefore') || ($typeOptKey == 'insertAfter')
                                || ($typeOptKey == 'insertTitle')
                                || ($typeOptKey == 'titleHint') || ($typeOptKey == 'resource') || (mb_strpos($typeOptKey, 'hint_') === 0))
                            {
                                continue;
                            }
                            if (array_search($typeOptKey, $ret) === FALSE)
                            {
                                $ret[] = $typeOptKey;
                            }
                        }
                    }
                }
            }
            else
            {
                $ret[] = $key;
            }
        }
        array_unshift($ret, 'resource');

        return $ret;
    }
    /**
     * Get keys of resource type messages from array
     *
     * @param string $type
     *
     * @return array
     */
    public function getMsgKeys($type)
    {
        $msgKeys = array_keys($this->reverseTypeMap[$type]);

        return $msgKeys;
    }
    /**
     * Return array of resource types' mapping and perform some optimization and error checking.
     *
     * @return array
     */
    public function getTypeMap()
    {
        // Check if $returnTypeMap is not empty -- if populated, we've already run this optimizing function so return the array.
        if (!empty($this->returnTypeMap))
        {
            return $this->returnTypeMap;
        }
        $this->returnTypeMap = $this->typeMap;
        // Remove invalid ['optional'] elements or, if ['optional'] does not exist, add an empty array (this voids requirements
        // in user loop functions to check if ['optional'] exists).
        foreach ($this->typeMap as $type => $value)
        {
            if (array_key_exists('optional', $value))
            {
                foreach ($this->typeMap[$type]['optional'] as $optKey => $optValue)
                {
                    if (array_search($optKey, $this->optional) === FALSE)
                    {
                        unset($this->returnTypeMap[$type]['optional'][$optKey]);
                    }
                }
            }
            else
            {
                $this->returnTypeMap[$type]['optional'] = [];
            }
        }

        return $this->returnTypeMap;
    }
    /**
     * Return array of types without any deactivation of types
     *
     * @return array
     */
    public function getTypesRaw()
    {
        return $this->typesRaw;
    }
    /**
     * Get optional array
     *
     * @return array
     */
    public function getOptional()
    {
        return $this->optional;
    }
    /**
     * Get getTypes array with deactivation of types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }
    /**
     * Create arrays based on database table and resource type.
     *
     * The key of the resource_xxx arrays is the database field within the WKX_resource_xxx database table.
     * The value of the resource_xxx arrays is the key input to the MESSAGES class for language localizations.
     */
    private function loadMap()
    {
        // WIKINDX resource types and user-displayed description.  The value is the key of the MESSAGES.php resourceType array.
        // The order given here is the order displayed in form select boxes.
        $this->types = ['book', 'book_article', 'book_chapter', 'journal_article', 'newspaper_article',
            'magazine_article',
            'proceedings', 'conference_paper', 'conference_poster', 'proceedings_article', 'thesis', 'web_site', 'web_article',
            'web_encyclopedia', 'web_encyclopedia_article',
            'database', 'film', 'broadcast', 'music_album', 'music_track', 'music_score',
            'artwork', 'software', 'audiovisual', 'government_report', 'report', 'hearing',
            'statute', 'legal_ruling', 'case', 'bill', 'patent', 'brochure', 'personal', 'unpublished',
            'classical', 'manuscript', 'map', 'chart', 'miscellaneous', 'miscellaneous_section',];
        $this->typesRaw = $this->types;
        // Fields such as title, creators, URL, abstract etc. are expected.  This array gives extra groups of fields the
        // presence of which as an array in $this->$type['optional'] triggers the printing of the requisite table/form cells
        // in RESOURCEFORM.  Anything in $this->$type['optional'] not listed here is ignored.
        $this->optional = ['conference', 'collection', 'publisher', 'miscellaneous', 'series', 'translation'];
        // Book resource type
        $this->book = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'editor',
                'Creator3' => 'translator',
                'Creator4' => 'reviser',
                'Creator5' => 'seriesEditor',
            ],
        ];
        // Each resource type array has an ['optional'] array that automates the layout of the resource input form --
// titles and creators not required as they're already displayed first.  Other things like doi, ISBN, abstract etc. are
// always displayed.  The order of ['optional'] is the order of printing the table cell groups and the order of the elements
// of the subArray (e.g. ['optional']['resourceyear']) is the order of printing the cell group's elements.  RESOURCEFORM makes
// its own decisions as to how many elements to print in a row.
// As an example, $this->book['optional']['publisher']:
// 1. ['publisher'] exists in $this->optional (above) so is a valid cell group in which all elements will be printed together.
// 2. Its elements comprise arrays the keys of which are the database table (e.g. 'resourceyear').
// 3. Each of these has keys which are the database field name while the values are the keys of the
// resource array in MESSAGES.php.
// 4. RESOURCEFORM will make an HTML td cell of each database table array printing each element vertically and each new td cell to the right.
// 5. If the previous database table array contains the key 'continue', the next database table array elements will continue printing
// vertically in the same td cell otherwise, a new HTML td cell is assumed. The value in 'continue' is ignored.
// 6. If the database table array contains the key 'break' (whose value will be ignored'), a new HTML td cell to the right will be started.
// To have multiple 'break' keys within one array, use 'break1', 'break2' etc.
// 7. If $this->book['optional']['publisher'], in this example, contains the key 'insertBefore' or 'insertAfter', a table element
// will be inserted before or after the given key.  What is inserted, is defined in RESOURCEFORM.php but is typically a select box.
// The optional 'insertTitle' gives the title of the select box.
// 8. If $this->book['optional']['publisher'], in this example, contains an element whose key begins with 'hint_xxx', the
// 'xxx' defines the key following which a hint message will be inserted as given by hint[value] in MESSAGES.php.  'hint_xxx' should
// be in its member array at a position above that element it references.
// 9. If $this->book['optional']['publisher'], in this example, contains the key 'title', then the value of that key is the key of the
// resources array in MESSAGES.php whose value gives the cell group's title. Otherwise, a default title is used by RESOURCEFORM.
// 10. If $this->book['optional']['publisher'], in this example, contains the key 'titleHint', then the value of that key is the key of the
// hint array in MESSAGES.php whose value gives the cell group's hint printed below the title.
// 11. The ['virtual'] array contains database tables whose fields are typically ID numbers such as publisherId, collectionId etc.  The keys
// are the database fields while the values are what the edit form fields and session variables are.
// 12. The ['virtualFields'] array contains arrays of fields whose values should be tested for from the resource edit form and the relevant
// ID removed if present.  If the array name is 'publisherId' and one of the values stored in that array ('publisherName', 'publisherLocation')
// is present in the input, then 'publisherId' should be removed from the input because values in the input fields override select
// box values.
        $this->book['optional']['publisher'] = // RESOURCEFORM cell (see $this->optional above)
            [
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [ // database table
                    'publisherName' => 'publisherName', // table field name and 'resource' message in MESSAGES.php
                    'publisherLocation' => 'publisherLocation',
                ],
                'resource' => [
                    'Field2' => 'edition',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                    'Year2' => 'reprintYear',
                ],
            ];
        $this->book['optional']['translation'] =
            [
                'insertAfter' => 'TransShortTitle',
                'insertBefore' => 'Year4',
                'hint_Year4' => 'publicationYear',
                'resource' => [
                    'TransTitle' => 'title',
                    'TransSubtitle' => 'subtitle',
                    'TransShortTitle' => 'shortTitle',
                ],
                'publisher' => [
                    'transPublisherName' => 'publisherName',
                    'transPublisherLocation' => 'publisherLocation',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year4' => 'publicationYear',
                ],
            ];
        $this->book['optional']['miscellaneous'] =
            [
                'hint_Field6' => 'arabicNumeral1',
                'resource' => [
                    'Field4' => 'bookVolumeNumber',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year3' => 'volumeYear',
                    'continue' => TRUE,
                ],
                'hint_Field4' => 'arabicNumeral1',
                'resourcemisc' => [
                    'Field4' => 'numberOfVolumes',
                    'break' => TRUE,
                    'Field6' => 'numPages',
                    'PeerReviewed' => 'peerReviewed',
                ],
            ];
        $this->book['optional']['series'] =
            [
                'insertAfter' => 'Field1',
                'resource' => [
                    'Field1' => 'seriesTitle',
                    'break' => TRUE,
                    'Field3' => 'seriesNumber',
                ],
            ];
        $this->book['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                    'Field1' => 'transPublisherId',
                ],
            ];
        $this->book['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
                'transPublisherId' => ['publishertransPublisherName', 'publishertransPublisherLocation'],
            ];
        $this->typeMap['book'] = $this->book;
        // book article resource type
        $this->book_article = $this->book;
        $this->book_article['optional']['miscellaneous'] =
            [
                'hint_Field6' => 'arabicNumeral1',
                'resource' => [
                    'Field4' => 'bookVolumeNumber',
                    'Field6' => 'numPages',
                ],
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->book_article['optional']['collection'] =
            [
                'title' => 'book',
                'titleHint' => 'collection',
                'insertAfter' => 'TitleShort',
                'insertTitle' => 'book',
                'collection' => [
                    'Title' => 'title',
                    'break' => TRUE,
                    'TitleShort' => 'shortTitle',
                ],
            ];
        $this->book_article['virtual']['resourcemisc']['Collection'] = 'collectionId';
        $this->book_article['virtualFields']['collectionId'] = ['collectionTitle', 'collectionTitleShort'];
        $this->book_article['required'] = ['collectionTitle'];
        $this->typeMap['book_article'] = $this->book_article;
        // Book chapter resource type
        $this->book_chapter = $this->book_article;
        $this->typeMap['book_chapter'] = $this->book_chapter;
        // Journal article resource type
        $this->journal_article = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'translator',
            ],
        ];
        $this->journal_article['optional']['publisher'] =
            [
                'insertAfter' => 'publisherLocation',
                'publisher' => [ // database table
                    'publisherName' => 'publisherName', // table field name and 'resource' message in MESSAGES.php
                    'break' => TRUE,
                    'publisherLocation' => 'publisherLocation',
                ],
            ];
        $this->journal_article['optional']['collection'] =
            [
                'title' => 'journal',
                'titleHint' => 'collection',
                'insertTitle' => 'journal',
                'insertAfter' => 'TitleShort',
                'collection' => [
                    'Title' => 'title',
                    'break' => TRUE,
                    'TitleShort' => 'shortTitle',
                ],
            ];
        $this->journal_article['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field1' => 'journalVolumeNumber',
                    'Field2' => 'journalIssueNumber',
                    'continue' => TRUE,
                ],
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                    'break' => TRUE,
                    'Field3' => 'startMonth',
                    'Field6' => 'endMonth',
                ],
                'resourceyear' => [
                    'Year1' => 'startYear',
                    'Year3' => 'endYear',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->journal_article['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                    'Collection' => 'collectionId',
                ],
            ];
        $this->journal_article['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
                'collectionId' => ['collectionTitle', 'collectionTitleShort'],
            ];
        $this->journal_article['required'] = ['collectionTitle'];
        $this->typeMap['journal_article'] = $this->journal_article;
        // Newspaper article type
        $this->newspaper_article = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'translator',
            ],
        ];
        $this->newspaper_article['optional']['collection'] =
            [
                'insertAfter' => 'TitleShort',
                'title' => 'newspaper',
                'titleHint' => 'collection',
                'insertTitle' => 'newspaper',
                'collection' => [
                    'Title' => 'title',
                    'break' => TRUE,
                    'TitleShort' => 'shortTitle',
                ],
            ];
        $this->newspaper_article['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field1' => 'section',
                    'Field2' => 'city',
                ],
                'resourcemisc' => [
                    'publicationDate' => 'publicationDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->newspaper_article['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'collectionId',
                ],
            ];
        $this->newspaper_article['virtualFields'] =
            [
                'collectionId' => ['collectionTitle', 'collectionTitleShort'],
            ];
        $this->newspaper_article['required'] = ['collectionTitle'];
        $this->typeMap['newspaper_article'] = $this->newspaper_article;
        // Magazine article type
        $this->magazine_article = [
            'resourcecreator' => [
                'Creator1' => 'author',
            ],
        ];
        $this->magazine_article['optional']['collection'] =
            [
                'title' => 'magazine',
                'titleHint' => 'collection',
                'insertAfter' => 'TitleShort',
                'collection' => [
                    'Title' => 'title',
                    'TitleShort' => 'shortTitle',
                ],
                'resource' => [
                    'Field2' => 'typeOfArticle',
                    'Field1' => 'edition',
                    'break' => TRUE,
                    'Field4' => 'volume',
                    'Field3' => 'issueNumber',
                ],
            ];
        $this->magazine_article['optional']['miscellaneous'] =
            [
                'resourcemisc' => [
                    'startDate' => 'startDate',
                    'Field2' => 'startDay',
                    'Field3' => 'startMonth',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'startYear',
                    'break' => TRUE,
                ],
                'resourcemisc*' => [
                    'endDate' => 'endDate',
                    'Field5' => 'endDay',
                    'Field6' => 'endMonth',
                    'continue' => TRUE,
                ],
                'resourceyear*' => [
                    'Year3' => 'endYear',
                    'break' => TRUE,
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->magazine_article['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'collectionId',
                ],
            ];
        $this->magazine_article['virtualFields'] =
            [
                'collectionId' => ['collectionTitle', 'collectionTitleShort'],
            ];
        $this->magazine_article['required'] = ['collectionTitle'];
        $this->typeMap['magazine_article'] = $this->magazine_article;
        // proceedings article
        $this->proceedings_article = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'editor',
            ],
        ];
        $this->proceedings_article['optional']['publisher'] =
            [
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [ // database table
                    'publisherName' => 'publisherName', // table field name and 'resource' message in MESSAGES.php
                    'publisherLocation' => 'publisherLocation',
                ],
                'resource' => [
                    'Field4' => 'proceedingsVolumeNumber',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->proceedings_article['optional']['collection'] =
            [
                'title' => 'organiser',
                'insertAfter' => 'conferenceOrganiser',
                'insertTitle' => 'organiser',
                'publisher' => [
                    'conferenceOrganiser' => 'conferenceOrganiser',
                    'break2' => TRUE,
                    'conferenceLocation' => 'conferenceLocation',
                ],
            ];
        $this->proceedings_article['optional']['conference'] =
            [
                'title' => 'conference',
                'titleHint' => 'collection',
                'insertAfter' => 'TitleShort',
                'insertTitle' => 'conference',
                'hint_Year2' => 'conferenceDate',
                'collection' => [
                    'Title' => 'title',
                    'TitleShort' => 'shortTitle',
                    'continue' => TRUE,
                ],
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                    'break1' => TRUE,
                    'startDate' => 'startDate',
                    'Field2' => 'startDay',
                    'Field3' => 'startMonth',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year2' => 'startYear',
                    'break2' => TRUE,
                ],
                'resourcemisc*' => [
                    'endDate' => 'endDate',
                    'Field5' => 'endDay',
                    'Field6' => 'endMonth',
                    'continue' => TRUE,
                ],
                'resourceyear*' => [
                    'Year3' => 'endYear',
                    'break2' => TRUE,
                ],
            ];
        $this->proceedings_article['optional']['series'] =
            [
                'insertAfter' => 'Field1',
                'resource' => [
                    'Field1' => 'seriesTitle',
                    'break' => TRUE,
                    'Field3' => 'seriesNumber',
                ],
            ];
        $this->proceedings_article['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'conferenceId',
                    'Publisher' => 'organizerId',
                    'Field1' => 'publisherId',
                ],
            ];
        $this->proceedings_article['virtualFields'] =
            [
                'organizerId' => ['collectionTitle', 'collectionTitleShort'],
                'publisherId' => ['publisherName', 'publisherLocation'],
                'conferenceId' => ['conferenceName', 'conferenceLocation'],
            ];
        $this->proceedings_article['required'] = ['collectionTitle'];
        $this->typeMap['proceedings_article'] = $this->proceedings_article;
        // Conference Paper resource type
        $this->conference_paper = [
            'resourcecreator' => [
                'Creator1' => 'author',
            ],
        ];
        $this->conference_paper['optional']['collection'] =
            [
                'title' => 'organiser',
                'insertAfter' => 'publisherName',
                'insertTitle' => 'organiser',
                'publisher' => [
                    'publisherName' => 'conferenceOrganiser',
                    'break2' => TRUE,
                    'publisherLocation' => 'conferenceLocation',
                ],
            ];
        $this->conference_paper['optional']['conference'] =
            [
                'title' => 'conference',
                'titleHint' => 'collection',
                'insertAfter' => 'TitleShort',
                'insertTitle' => 'conference',
                'hint_Year2' => 'conferenceDate',
                'collection' => [
                    'Title' => 'title',
                    'TitleShort' => 'shortTitle',
                    'continue' => TRUE,
                ],
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                    'break1' => TRUE,
                    'startDate' => 'startDate',
                    'Field2' => 'startDay',
                    'Field3' => 'startMonth',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year2' => 'startYear',
                    'break2' => TRUE,
                ],
                'resourcemisc*' => [
                    'endDate' => 'endDate',
                    'Field5' => 'endDay',
                    'Field6' => 'endMonth',
                    'continue' => TRUE,
                ],
                'resourceyear*' => [
                    'Year3' => 'endYear',
                    'break2' => TRUE,
                ],
            ];
        $this->conference_paper['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'conferenceId',
                    'Publisher' => 'organizerId',
                ],
            ];
        $this->conference_paper['virtualFields'] =
            [
                'conferenceId' => ['collectionTitle', 'collectionTitleShort'],
                'organizerId' => ['publisherName', 'publisherLocation'],
            ];
        $this->conference_paper['required'] = ['collectionTitle'];
        $this->typeMap['conference_paper'] = $this->conference_paper;
        // Conference Poster resource type
        $this->conference_poster = [
            'resourcecreator' => [
                'Creator1' => 'author',
            ],
        ];
        $this->conference_poster['optional']['collection'] =
            [
                'title' => 'organiser',
                'insertAfter' => 'publisherName',
                'insertTitle' => 'organiser',
                'publisher' => [
                    'publisherName' => 'conferenceOrganiser',
                    'break2' => TRUE,
                    'publisherLocation' => 'conferenceLocation',
                ],
            ];
        $this->conference_poster['optional']['conference'] =
            [
                'title' => 'conference',
                'titleHint' => 'collection',
                'insertAfter' => 'TitleShort',
                'insertTitle' => 'conference',
                'hint_Year2' => 'conferenceDate',
                'collection' => [
                    'Title' => 'title',
                    'TitleShort' => 'shortTitle',
                    'continue' => TRUE,
                ],
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                    'break1' => TRUE,
                    'startDate' => 'startDate',
                    'Field2' => 'startDay',
                    'Field3' => 'startMonth',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year2' => 'startYear',
                    'break2' => TRUE,
                ],
                'resourcemisc*' => [
                    'endDate' => 'endDate',
                    'Field5' => 'endDay',
                    'Field6' => 'endMonth',
                    'continue' => TRUE,
                ],
                'resourceyear*' => [
                    'Year3' => 'endYear',
                    'break2' => TRUE,
                ],
            ];
        $this->conference_poster['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'conferenceId',
                    'Publisher' => 'organizerId',
                ],
            ];
        $this->conference_poster['virtualFields'] =
            [
                'conferenceId' => ['collectionTitle', 'collectionTitleShort'],
                'organizerId' => ['publisherName', 'publisherLocation'],
            ];
        $this->conference_poster['required'] = ['collectionTitle'];
        $this->typeMap['conference_poster'] = $this->conference_poster;
        // proceedings (complete set)
        $this->proceedings = [
            'resourcecreator' => [
                'Creator2' => 'editor',
            ],
        ];
        $this->proceedings['optional']['publisher'] =
            [
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [ // database table
                    'publisherName' => 'publisherName', // table field name and 'resource' message in MESSAGES.php
                    'publisherLocation' => 'publisherLocation',
                ],
                'resource' => [
                    'Field4' => 'proceedingsVolumeNumber',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->proceedings['optional']['collection'] =
            [
                'title' => 'organiser',
                'insertAfter' => 'conferenceOrganiser',
                'insertTitle' => 'organiser',
                'publisher' => [
                    'conferenceOrganiser' => 'conferenceOrganiser',
                    'break2' => TRUE,
                    'conferenceLocation' => 'conferenceLocation',
                ],
            ];
        $this->proceedings['optional']['conference'] =
            [
                'title' => 'conference',
                'titleHint' => 'collection',
                'insertAfter' => 'TitleShort',
                'insertTitle' => 'conference',
                'hint_Year2' => 'conferenceDate',
                'collection' => [
                    'Title' => 'title',
                    'TitleShort' => 'shortTitle',
                    'continue' => TRUE,
                ],
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                    'break1' => TRUE,
                    'startDate' => 'startDate',
                    'Field2' => 'startDay',
                    'Field3' => 'startMonth',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year2' => 'startYear',
                    'break2' => TRUE,
                ],
                'resourcemisc*' => [
                    'endDate' => 'endDate',
                    'Field5' => 'endDay',
                    'Field6' => 'endMonth',
                    'continue' => TRUE,
                ],
                'resourceyear*' => [
                    'Year3' => 'endYear',
                    'break2' => TRUE,
                ],
            ];
        $this->proceedings['optional']['series'] =
            [
                'insertAfter' => 'Field1',
                'resource' => [
                    'Field1' => 'seriesTitle',
                    'break' => TRUE,
                    'Field3' => 'seriesNumber',
                ],
            ];
        $this->proceedings['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'conferenceId',
                    'Publisher' => 'organizerId',
                    'Field1' => 'publisherId',
                ],
            ];
        $this->proceedings['virtualFields'] =
            [
                'organizerId' => ['collectionTitle', 'collectionTitleShort'],
                'publisherId' => ['publisherName', 'publisherLocation'],
                'conferenceId' => ['conferenceName', 'conferenceLocation'],
            ];
        $this->proceedings['required'] = ['collectionTitle'];
        $this->typeMap['proceedings'] = $this->proceedings;
        // Thesis resource type
        $this->thesis = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'supervisor',
            ],
        ];
        $this->thesis['optional']['collection'] =
            [
                'title' => 'journal',
                'titleHint' => 'thesisAbstract',
                'insertAfter' => 'TitleShort',
                'insertTitle' => 'journal',
                'collection' => [
                    'Title' => 'title',
                    'TitleShort' => 'shortTitle',
                ],
                'resource' => [
                    'Field3' => 'journalVolumeNumber',
                    'Field4' => 'journalIssueNumber',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->thesis['optional']['publisher'] =
            [
                'title' => 'institution',
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'Field5',
                'insertTitle' => 'institution',
                'publisher' => [
                    'publisherName' => 'institutionName',
                    'publisherLocation' => 'institutionLocation',
                ],
                'resource' => [
                    'Field5' => 'department',
                ],
            ];
        $this->thesis['optional']['miscellaneous'] =
            [
                'hint_Field1' => 'thesisLabel',
                'hint_Field2' => 'thesisType',
                'resource' => [
                    'Field1' => 'thesisLabel',
                    'Field2' => 'thesis',
                ],
                'resourcemisc' => [
                    'Field6' => 'numPages',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'thesisYear',
                ],
            ];
        $this->thesis['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'collectionId',
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->thesis['virtualFields'] =
            [
                'collectionId' => ['collectionTitle', 'collectionTitleShort'],
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['thesis'] = $this->thesis;
        // Web site resource type
        $this->web_site = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'editor',
            ],
        ];
        $this->web_site['optional']['miscellaneous'] =
            [
                'hint_Year2' => 'dateAccessed',
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                    'break' => TRUE,
                    'accessDate' => 'accessDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year2' => 'year',
                    'break' => TRUE,
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->typeMap['web_site'] = $this->web_site;
        // Web article resource type
        $this->web_article = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'editor',
            ],
        ];
        $this->web_article['optional']['collection'] =
            [
                'title' => 'journal',
                'titleHint' => 'collection',
                'insertAfter' => 'TitleShort',
                'title' => 'journal',
                'insertTitle' => 'journal',
                'hint_Field2' => 'arabicNumeral1',
                'collection' => [
                    'Title' => 'title',
                    'TitleShort' => 'shortTitle',
                ],
                'resource' => [
                    'Field1' => 'journalVolumeNumber',
                    'Field2' => 'journalIssueNumber',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->web_article['optional']['publisher'] =
            [
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [ // database table
                    'publisherName' => 'publisherName', // table field name and 'resource' message in MESSAGES.php
                    'publisherLocation' => 'publisherLocation',
                ],
                'resourcemisc' => [
                    'publicationDate' => 'publicationDate',
                    'Field5' => 'publicationDay',
                    'Field6' => 'publicationMonth',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->web_article['optional']['miscellaneous'] =
            [
                'hint_Year2' => 'dateAccessed',
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                    'break' => TRUE,
                    'accessDate' => 'accessDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year2' => 'year',
                ],
            ];
        $this->web_article['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'collectionId',
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->web_article['virtualFields'] =
            [
                'collectionId' => ['collectionTitle', 'collectionTitleShort'],
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['web_article'] = $this->web_article;
        // Web encyclopedia resource type
        $this->web_encyclopedia = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'editor',
            ],
        ];
        $this->web_encyclopedia['optional']['publisher'] =
            [
                'hint_Year1' => 'publicationYear',
                'hint_Year3' => 'revisionYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [ // database table
                    'publisherName' => 'publisherName', // table field name and 'resource' message in MESSAGES.php
                    'publisherLocation' => 'publisherLocation',
                ],
                'resource' => [
                    'Field2' => 'edition',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                    'Year3' => 'revisionYear',
                ],
            ];
        $this->web_encyclopedia['optional']['miscellaneous'] =
            [
                'hint_Year2' => 'dateAccessed',
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                    'break' => TRUE,
                    'accessDate' => 'accessDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year2' => 'year',
                ],
            ];
        $this->web_encyclopedia['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->web_encyclopedia['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['web_encyclopedia'] = $this->web_encyclopedia;
        // Web encyclopedia article resource type
        $this->web_encyclopedia_article = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'editor',
            ],
        ];
        $this->web_encyclopedia_article['optional']['collection'] =
            [
                'title' => 'encyclopedia',
                'titleHint' => 'collection',
                'insertAfter' => 'TitleShort',
                'insertTitle' => 'encyclopedia',
                'collection' => [
                    'Title' => 'title',
                    'TitleShort' => 'shortTitle',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->web_encyclopedia_article['optional']['publisher'] =
            [
                'hint_Year1' => 'publicationYear',
                'hint_Year3' => 'revisionYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [ // database table
                    'publisherName' => 'publisherName', // table field name and 'resource' message in MESSAGES.php
                    'publisherLocation' => 'publisherLocation',
                ],
                'resource' => [
                    'Field2' => 'edition',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                    'Year3' => 'revisionYear',
                ],
            ];
        $this->web_encyclopedia_article['optional']['miscellaneous'] =
            [
                'hint_Year2' => 'dateAccessed',
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                    'break' => TRUE,
                    'accessDate' => 'accessDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year2' => 'year',
                ],
            ];
        $this->web_encyclopedia_article['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'collectionId',
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->web_encyclopedia_article['virtualFields'] =
            [
                'collectionId' => ['collectionTitle', 'collectionTitleShort'],
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->web_encyclopedia_article['required'] = ['collectionTitle'];
        $this->typeMap['web_encyclopedia_article'] = $this->web_encyclopedia_article;
        // Online database resource type
        $this->database = [
            'resourcecreator' => [
                'Creator1' => 'author',
            ],
        ];
        $this->database['optional']['publisher'] =
            [
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->database['optional']['miscellaneous'] =
            [
                'hint_Year2' => 'dateAccessed',
                'resourcemisc' => [
                    'accessDate' => 'accessDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year2' => 'year',
                ],
            ];
        $this->database['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->database['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['database'] = $this->database;
        // Software resource type
        $this->software = [
            'resourcecreator' => [
                'Creator1' => 'author',
            ],
        ];
        $this->software['optional']['publisher'] =
            [
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resource' => [
                    'Field2' => 'typeOfSoftware',
                    'Field4' => 'version',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->software['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->software['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['software'] = $this->software;
        // Audiovisual resource type
        $this->audiovisual = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'performer',
                'Creator5' => 'seriesEditor',
            ],
        ];
        $this->audiovisual['optional']['publisher'] =
            [
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resource' => [
                    'Field2' => 'medium',
                    'Field3' => 'edition',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->audiovisual['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field4' => 'bookVolumeNumber',
                ],
            ];
        $this->audiovisual['optional']['series'] =
            [
                'insertAfter' => 'Field3',
                'resource' => [
                    'Field1' => 'seriesTitle',
                    'Field3' => 'seriesNumber',
                ],
                'hint_Field4' => 'arabicNumeral1',
                'resourcemisc' => [
                    'Field4' => 'numberOfVolumes',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year3' => 'volumeYear',
                ],
            ];
        $this->audiovisual['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->audiovisual['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['audiovisual'] = $this->audiovisual;
        // case resource type
        $this->case = [
            'resourcecreator' => [
                'Creator3' => 'counsel',
                'Creator4' => 'judge',
            ],
        ];
        $this->case['optional']['publisher'] =
            [
                'title' => 'court',
                'hint_Year1' => 'dateDecided',
                'insertAfter' => 'publisherName',
                'publisher' => [
                    'publisherName' => 'court',
                ],
                'resourcemisc' => [
                    'publicationDate' => 'publicationDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'caseYear',
                ],
            ];
        $this->case['optional']['collection'] =
            [
                'title' => 'reporter',
                'resource' => [
                    'Field1' => 'title',
                    'Field4' => 'reporterVolume',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->case['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->case['virtualFields'] =
            [
                'publisherId' => ['publisherName'],
            ];
        $this->typeMap['case'] = $this->case;
        // bill resource type
        $this->bill = [
        ];
        $this->bill['optional']['publisher'] =
            [
                'title' => 'legislativeBody',
                'insertAfter' => 'Field1',
                'publisher' => [
                    'publisherName' => 'title',
                    'publisherLocation' => 'legislativeLocation',
                    'continue' => TRUE,
                ],
                'resource' => [
                    'Field1' => 'section',
                    'break' => TRUE,
                    'Field4' => 'session',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'sessionYear',
                ],
            ];
        $this->bill['optional']['collection'] =
            [
                'title' => 'code',
                'resource' => [
                    'Field2' => 'code',
                    'Field3' => 'codeVolume',
                    'Field5' => 'billNumber',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->bill['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->bill['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['bill'] = $this->bill;
        // Legal Rule/Regulation resource type
        $this->legal_ruling = [
            'resourcecreator' => [
                'Creator1' => 'author',
            ],
        ];
        $this->legal_ruling['optional']['publisher'] =
            [
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resourcemisc' => [
                    'publicationDate' => 'publicationDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->legal_ruling['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field1' => 'section',
                    'Field3' => 'edition',
                    'break' => TRUE,
                    'Field2' => 'ruleType',
                    'Field4' => 'ruleNumber',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->legal_ruling['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->legal_ruling['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['legal_ruling'] = $this->legal_ruling;
        // Government Report/Documentation resource type
        $this->government_report = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'editor',
            ],
        ];
        $this->government_report['optional']['publisher'] =
            [
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->government_report['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field1' => 'section',
                    'Field2' => 'department',
                    'break' => TRUE,
                    'Field3' => 'edition',
                    'Field4' => 'volume',
                    'Field5' => 'issueNumber',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->government_report['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->government_report['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['government_report'] = $this->government_report;
        // Report/Documentation resource type
        $this->report = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'editor',
            ],
        ];
        $this->report['optional']['publisher'] =
            [
                'title' => 'institution',
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'institutionName',
                    'publisherLocation' => 'institutionLocation',
                ],
                'resourcemisc' => [
                    'publicationDate' => 'publicationDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->report['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field2' => 'typeOfReport',
                    'Field5' => 'issueNumber',
                    'continue' => TRUE,
                ],
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->report['optional']['series'] =
            [
                'insertAfter' => 'Field1',
                'resource' => [
                    'Field1' => 'seriesTitle',
                ],
            ];
        $this->report['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->report['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['report'] = $this->report;
        // Hearing resource type
        $this->hearing = [
            'resourcecreator' => [// empty array required
            ],
        ];
        $this->hearing['optional']['publisher'] =
            [
                'title' => 'hearing',
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'hearing',
                    'publisherLocation' => 'legislativeLocation',
                ],
            ];
        $this->hearing['optional']['collection'] =
            [
                'title' => 'committee',
                'hint_Year1' => 'hearingDate',
                'resource' => [
                    'Field1' => 'committee',
                    'Field2' => 'legislativeBody',
                    'Field3' => 'session',
                    'break' => TRUE,
                    'Field4' => 'documentNumber',
                    'continue' => TRUE,
                ],
                'hint_Field4' => 'arabicNumeral1',
                'resourcemisc' => [
                    'Field4' => 'numberOfVolumes',
                    'break' => TRUE,
                    'publicationDate' => 'publicationDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->hearing['optional']['miscellaneous'] =
            [
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->hearing['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->hearing['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['hearing'] = $this->hearing;
        // Statute type
        $this->statute = [
        ];
        $this->statute['optional']['miscellaneous'] =
            [
                'hint_Year1' => 'dateEnacted',
                'resource' => [
                    'Field1' => 'publicLawNumber',
                    'Field2' => 'code',
                    'Field3' => 'session',
                    'break' => TRUE,
                    'Field4' => 'section',
                    'Field5' => 'codeNumber',
                ],
                'resourcemisc' => [
                    'publicationDate' => 'publicationDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->typeMap['statute'] = $this->statute;
        // Patent type
        $this->patent = [
            'resourcecreator' => [
                'Creator1' => 'inventor',
                'Creator2' => 'issuingOrganisation',
                'Creator3' => 'agent',
                'Creator4' => 'intAuthor',
            ],
        ];
        $this->patent['optional']['publisher'] =
            [
                'title' => 'assignee',
                'insertAfter' => 'publisherLocation',
                'hint_Year1' => 'issueDate',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resourcemisc' => [
                    'publicationDate' => 'publicationDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->patent['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field6' => 'intPatentTitle',
                    'Field4' => 'patentType',
                    'Field1' => 'publishedSource',
                    'break2' => TRUE,
                    'Field7' => 'intPatentClassification',
                    'Field9' => 'legalStatus',
                    'Field3' => 'applicationNumber',
                    'break1' => TRUE,
                    'Field8' => 'patentNumber',
                    'Field5' => 'intPatentNumber',
                    'Field2' => 'patentVersionNumber',
                ],
            ];
        $this->patent['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->patent['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['patent'] = $this->patent;
        // Brochure resource type
        $this->brochure = [
            'resourcecreator' => [
                'Creator1' => 'company',
            ],
        ];
        $this->brochure['optional']['miscellaneous'] =
            [
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->typeMap['brochure'] = $this->brochure;
        // classical resource type
        $this->classical = [
            'resourcecreator' => [
                'Creator1' => 'attributedTo',
            ],
        ];
        $this->classical['optional']['miscellaneous'] =
                                [
                                    'hint_Year1' => 'publicationYear',
                                    'resource' => [
                                        'Field4' => 'volume',
                                    ],
                                    'resourceyear' => [
                                        'Year1' => 'publicationYear',
                                    ],
                                ];
        $this->typeMap['classical'] = $this->classical;
        // Manuscript type
        $this->manuscript = [
            'resourcecreator' => [
                'Creator1' => 'author',
            ],
        ];
        $this->manuscript['optional']['collection'] =
            [
                'insertAfter' => 'TitleShort',
                'titleHint' => 'collection',
                'collection' => [
                    'Title' => 'title',
                    'TitleShort' => 'shortTitle',
                ],
                'resource' => [
                    'Field2' => 'typeOfManuscript',
                    'Field3' => 'manuscriptNumber',
                ],
            ];
        $this->manuscript['optional']['miscellaneous'] =
            [
                'resourcemisc' => [
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->manuscript['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'collectionId',
                ],
            ];
        $this->manuscript['virtualFields'] =
            [
                'collectionId' => ['collectionTitle', 'collectionTitleShort'],
            ];
        $this->typeMap['manuscript'] = $this->manuscript;
        // Map type
        $this->map = [
            'resourcecreator' => [
                'Creator1' => 'cartographer',
                'Creator5' => 'seriesEditor',
            ],
        ];
        $this->map['optional']['publisher'] =
            [
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->map['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field2' => 'typeOfMap',
                    'break' => TRUE,
                    'Field3' => 'edition',
                ],
            ];
        $this->map['optional']['series'] =
            [
                'insertAfter' => 'Field1',
                'resource' => [
                    'Field1' => 'seriesTitle',
                ],
            ];
        $this->map['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->map['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['map'] = $this->map;
        // Chart type
        $this->chart = [
            'resource' => [
                'Field1' => 'nameOfFile',
                'Field2' => 'imageProgram',
                'Field3' => 'imageSize',
                'Field4' => 'imageType',
                'Field5' => 'version',
                'Field6' => 'number',
            ],
            'resourcecreator' => [
                'Creator1' => 'creator',
            ],
        ];
        $this->chart['optional']['publisher'] =
            [
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->chart['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field1' => 'nameOfFile',
                    'Field5' => 'version',
                    'Field6' => 'number',
                    'break' => TRUE,
                    'Field2' => 'imageProgram',
                    'Field3' => 'imageSize',
                    'Field4' => 'imageType',
                ],
            ];
        $this->chart['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->chart['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['chart'] = $this->chart;
        // Film resource type
        $this->film = [
            'resourcemisc' => [
                'Field1' => 'minutes',
                'Field4' => 'hours',
            ],
            'resourcecreator' => [
                'Creator1' => 'director',
                'Creator2' => 'producer',
            ],
        ];
        $this->film['optional']['publisher'] =
            [
                'title' => 'distributor',
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherName',
                'publisher' => [
                    'publisherName' => 'distributor',
                ],
                'resource' => [
                    'Field1' => 'country',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->film['optional']['miscellaneous'] =
            [
                'hint_Field1' => 'runningTime',
                'resourcemisc' => [
                    'Field4' => 'hours',
                    'Field1' => 'minutes',
                ],
            ];
        $this->film['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->film['virtualFields'] =
            [
                'publisherId' => ['publisherName'],
            ];
        $this->typeMap['film'] = $this->film;
        // Broadcast resource type
        $this->broadcast = [
            'resourceyear' => [
                'Year1' => 'publicationYear',
            ],
            'resourcecreator' => [
                'Creator1' => 'director',
                'Creator2' => 'producer',
            ],
        ];
        $this->broadcast['optional']['publisher'] =
            [
                'title' => 'channel',
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'channel',
                    'publisherLocation' => 'channelLocation',
                ],
                'resourcemisc' => [
                    'publicationDate' => 'publicationDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->broadcast['optional']['miscellaneous'] =
            [
                'hint_Field1' => 'runningTime',
                'resourcemisc' => [
                    'Field4' => 'hours',
                    'Field1' => 'minutes',
                ],
            ];
        $this->broadcast['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->broadcast['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['broadcast'] = $this->broadcast;
        // Music Album type
        $this->music_album = [
            'resourcecreator' => [
                'Creator1' => 'performer',
                'Creator2' => 'composer',
                'Creator3' => 'conductor',
            ],
        ];
        $this->music_album['optional']['publisher'] =
            [
                'title' => 'recordLabel',
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherName',
                'publisher' => [
                    'publisherName' => 'recordLabel',
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                    'continue' => TRUE,
                ],
                'resource' => [
                    'Field2' => 'medium',
                ],
            ];
        $this->music_album['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->music_album['virtualFields'] =
            [
                'publisherId' => ['publisherName'],
            ];
        $this->typeMap['music_album'] = $this->music_album;
        // Music Track type
        $this->music_track = [
            'resourcecreator' => [
                'Creator1' => 'performer',
                'Creator2' => 'composer',
                'Creator3' => 'conductor',
            ],
        ];
        $this->music_track['optional']['publisher'] =
            [
                'title' => 'recordLabel',
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherName',
                'publisher' => [
                    'publisherName' => 'recordLabel',
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                    'continue' => TRUE,
                ],
                'resource' => [
                    'Field2' => 'medium',
                ],
            ];
        $this->music_track['optional']['collection'] =
            [
                'title' => 'album',
                'insertAfter' => 'TitleShort',
                'titleHint' => 'collection',
                'collection' => [
                    'Title' => 'title',
                    'TitleShort' => 'shortTitle',
                ],
            ];
        $this->music_track['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'collectionId',
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->music_track['virtualFields'] =
            [
                'collectionId' => ['collectionTitle', 'collectionTitleShort'],
                'publisherId' => ['publisherName'],
            ];
        $this->music_track['required'] = ['collectionTitle'];
        $this->typeMap['music_track'] = $this->music_track;
        // Music Score type
        $this->music_score = [
            'resourcecreator' => [
                'Creator1' => 'composer',
                'Creator2' => 'editor',
            ],
        ];
        $this->music_score['optional']['publisher'] =
            [
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                    'continue' => TRUE,
                ],
                'resource' => [
                    'Field3' => 'edition',
                ],
            ];
        $this->music_score['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->music_score['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['music_score'] = $this->music_score;
        // Artwork resource type
        $this->artwork = [
            'resourcecreator' => [
                'Creator1' => 'artist',
            ],
        ];
        $this->artwork['optional']['publisher'] =
            [
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resource' => [
                    'Field2' => 'medium',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->artwork['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->artwork['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['artwork'] = $this->artwork;
        // Miscellaneous resource type
        $this->miscellaneous = [
            'resourcecreator' => [
                'Creator1' => 'author',
            ],
        ];
        $this->miscellaneous['optional']['publisher'] =
            [
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->miscellaneous['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field2' => 'medium',
                ],
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                ],
            ];
        $this->miscellaneous['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->miscellaneous['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['miscellaneous'] = $this->miscellaneous;
        // Miscellaneous Section resource type
        $this->miscellaneous_section = [
            'resource' => [
                'Field2' => 'medium',
            ],
            'resourcemisc' => [// we require an empty array to still be present
            ],
            'resourceyear' => [
                'Year1' => 'publicationYear',
            ],
            'resourcecreator' => [
                'Creator1' => 'author',
            ],
        ];
        $this->miscellaneous_section['optional']['collection'] =
            [
                'insertAfter' => 'TitleShort',
                'titleHint' => 'collection',
                'collection' => [
                    'Title' => 'title',
                    'TitleShort' => 'shortTitle',
                ],
            ];
        $this->miscellaneous_section['optional']['publisher'] =
            [
                'hint_Year1' => 'publicationYear',
                'insertAfter' => 'publisherLocation',
                'publisher' => [
                    'publisherName' => 'publisherName',
                    'publisherLocation' => 'publisherLocation',
                ],
                'resourceyear' => [
                    'Year1' => 'publicationYear',
                ],
            ];
        $this->miscellaneous_section['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field2' => 'medium',
                ],
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                ],
            ];
        $this->miscellaneous_section['virtual'] =
            [
                'resourcemisc' => [
                    'Collection' => 'collectionId',
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->miscellaneous_section['virtualFields'] =
            [
                'collectionId' => ['collectionTitle', 'collectionTitleShort'],
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->miscellaneous_section['required'] = ['collectionTitle'];
        $this->typeMap['miscellaneous_section'] = $this->miscellaneous_section;
        // Personal Communication type
        $this->personal = [
            'resourcecreator' => [
                'Creator1' => 'author',
                'Creator2' => 'recipient',
            ],
        ];
        $this->personal['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field2' => 'typeOfCommunication',
                ],
                'resourcemisc' => [
                    'publicationDate' => 'publicationDate',
                    'Field2' => 'day',
                    'Field3' => 'month',
                    'continue' => TRUE,
                ],
                'resourceyear' => [
                    'Year1' => 'year',
                ],
            ];
        $this->typeMap['personal'] = $this->personal;
        // Unpublished type
        $this->unpublished = [
            'resourcecreator' => [
                'Creator1' => 'author',
            ],
        ];
        $this->unpublished['optional']['publisher'] =
            [
                'title' => 'institution',
                'insertAfter' => 'publisherLocation',
                'insertTitle' => 'institution',
                'publisher' => [
                    'publisherName' => 'institutionName',
                    'publisherLocation' => 'institutionLocation',
                ],
            ];
        $this->unpublished['optional']['miscellaneous'] =
            [
                'resource' => [
                    'Field2' => 'typeOfWork',
                ],
                'resourceyear' => [
                    'Year1' => 'year',
                    'continue' => TRUE,
                ],
                'resourcemisc' => [
                    'PeerReviewed' => 'peerReviewed',
                ],
                'resourcepage' => [
                    'PageStart' => 'pageStart',
                    'PageEnd' => 'pageEnd',
                ],
            ];
        $this->unpublished['virtual'] =
            [
                'resourcemisc' => [
                    'Publisher' => 'publisherId',
                ],
            ];
        $this->unpublished['virtualFields'] =
            [
                'publisherId' => ['publisherName', 'publisherLocation'],
            ];
        $this->typeMap['unpublished'] = $this->unpublished;
    }
}
