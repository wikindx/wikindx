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
 *	RIS Map class.
 *	Provides mappings for Endnote export/import
 */
class RISMAP
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadMap();
    }
    /**
     * Create arrays based on database table and resource type
     *
     * The arrays are named for WIKINDX resource types.
     * The key of the resource_xxx arrays is the database field within the WKX_resource_xxx database table. For exporting
     * RIS files this is not needed but will prove useful should there be an eventual RIS import.
     * The key of the resource_xxx array is the WIKINDX field in that table while the value is the RIS
     * equivalent field.  For an export, these keys can be found as the fields in the SQL $row assoc. array.
     *
     * The database matrix can be found in docs/resourceDatabaseMatrix.html
     *
     * Some things must be sorted out on the fly in the import/export code:
     * 1/ Running time for films comes from miscField1 and miscField4.
     * 2/ All year1 fields are dealt with in the import code.
     * 3/ WIKINDX book and book_article types can have a reprintYear in `year2`.  If a book has this in addition to a
     * publicationYear in `year1` then we need to switch the two fields so that `year1` is Endnote's 'Original Publication'
     * field and `year2` is Endnote's 'Year'.
     * 4/ Most dates (newspaper, magazine, hearing etc.) have to be created on the fly from database fields `miscField2`
     * (day) and `miscField3` (month).
     * 5/ A WIKINDX 'title' is comprised of `subtitle`, `title` and `noSort` fields.
     */
    public function loadMap()
    {
        // WIKINDX resource types (keys) and their RIS equivalent (values).
        $this->types = [
            'book' => 'BOOK',
            'book_article' => 'CHAP',
            'book_chapter' => 'CHAP',
            'journal_article' => 'JOUR',
            'newspaper_article' => 'NEWS',
            'magazine_article' => 'MGZN',
            'proceedings' => 'GEN',
            'conference_paper' => 'GEN',
            'proceedings_article' => 'CONF',
            'thesis' => 'THES',
            'web_article' => 'ELEC',
            'web_site' => 'ELEC',
            'web_encyclopedia' => 'ELEC',
            'web_encyclopedia_article' => 'ELEC',
            'film' => 'MPCT',
            'broadcast' => 'GEN',
            'music_album' => 'SOUND',
            'music_track' => 'SOUND',
            'music_score' => 'MUSIC',
            'artwork' => 'ART',
            'software' => 'COMP',
            'audiovisual' => 'ADVS',
            'database' => 'ELEC',
            'government_report' => 'GEN',
            'report' => 'RPRT',
            'hearing' => 'HEAR',
            'statute' => 'STAT',
            'legal_ruling' => 'GEN',
            'case' => 'CASE',
            'bill' => 'BILL',
            'patent' => 'PAT',
            'personal' => 'PCOMM',
            'unpublished' => 'UNPB',
            'classical' => 'GEN',
            'manuscript' => 'GEN',
            'map' => 'MAP',
            'chart' => 'GEN',
            'miscellaneous' => 'GEN',
            'miscellaneous_section' => 'GEN',
            'brochure' => 'PAMP',
            'conference_poster' => 'GEN',
        ];
        // Book resource type
        $this->book = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field1' => 'T3', // Series Title
                'field2' => 'VL', // Edition
                'field4' => 'IS', // Volume
            ],
            'resource_misc' => [
                'miscField4' => 'M1', // Number of Volumes
            ],
            'resource_year' => [// empty array required
            ],
            'resource_creator' => [
                'creator1' => 'A1', // Author
                'creator2' => 'A2', // Editor
                'creator5' => 'A3', // Series Editor
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->book['resource_publisher']['publisherName'] = 'PB';
        $this->book['resource_publisher']['publisherLocation'] = 'CY';
        $this->book['resource_collection'] = []; // need empty array
        // book article resource type
        $this->book_article = $this->book;
        $this->book_article['resource_collection']['collectionTitle'] = 'T2';
        // book chapter resource type
        $this->book_chapter = $this->book_article;
        // Journal article resource type
        $this->journal_article = [
            'resource' => [
                'isbn' => 'SN',
                'field1' => 'VL', // Volume
                'field2' => 'IS', // Issue
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [// empty array required
            ],
            'resource_creator' => [
                'creator1' => 'A1', // Author
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->journal_article['resource_publisher'] = []; // need empty array
        $this->journal_article['resource_collection']['collectionTitle'] = 'JF'; // Journal Full Name
// Newspaper article type
        $this->newspaper_article = [
            'resource' => [
                'isbn' => 'SN',
                'field2' => 'CY', // Place Published
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [// empty array required
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->newspaper_article['resource_publisher'] = []; // need empty array
        $this->newspaper_article['resource_collection']['collectionTitle'] = 'JF';
        // proceedings article
        $this->proceedings_article = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field1' => 'T3', // Series Title
                'field3' => 'IS', // Volume
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->proceedings_article['resource_publisher']['publisherName'] = 'PB';
        $this->proceedings_article['resource_publisher']['publisherLocation'] = 'CY';
        $this->proceedings_article['resource_collection']['collectionTitle'] = 'T2';
        // Web article resource type
        $this->web_article = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field1' => 'VL', // Volume
                'field2' => 'IS', // Issue
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
                'creator2' => 'A2',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->web_article['resource_publisher']['publisherName'] = 'PB';
        $this->web_article['resource_publisher']['publisherLocation'] = 'CY';
        $this->web_article['resource_collection']['collectionTitle'] = 'JF';
        // Web site resource type
        $this->web_site = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
                'creator2' => 'A2',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->web_site['resource_publisher'] = []; // need empty array
        $this->web_site['resource_collection'] = [];
        // Web encyclopedia resource type
        $this->web_encyclopedia = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field2' => 'VL', // Edition
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
                'creator2' => 'A2', // Editor
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->web_encyclopedia['resource_publisher']['publisherName'] = 'PB';
        $this->web_encyclopedia['resource_publisher']['publisherLocation'] = 'CY';
        $this->web_encyclopedia['resource_collection'] = [];
        // Web encyclopedia article resource type
        $this->web_encyclopedia_article = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field2' => 'VL', // Edition
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
                'creator2' => 'A2',  // Editor
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->web_encyclopedia_article['resource_publisher']['publisherName'] = 'PB';
        $this->web_encyclopedia_article['resource_publisher']['publisherLocation'] = 'CY';
        $this->web_encyclopedia_article['resource_collection']['collectionTitle'] = 'T2';
        // Thesis resource type
        $this->thesis = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field3' => 'VL', // Volume
                'field4' => 'IS', // Issue
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->thesis['resource_publisher']['publisherName'] = 'PB';
        $this->thesis['resource_publisher']['publisherLocation'] = 'CY';
        $this->thesis['resource_collection']['collectionTitle'] = 'JF'; // Abstract journal
        // Film resource type
        $this->film = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field1' => 'CY',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1', // director
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->film['resource_publisher']['publisherName'] = 'A3'; // distributor
        $this->film['resource_collection'] = []; // need empty array
// Broadcast resource type
        $this->broadcast = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1', // director
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->broadcast['resource_publisher']['publisherName'] = 'A3'; // distributor
        $this->broadcast['resource_publisher']['publisherLocation'] = 'CY';
        $this->broadcast['resource_collection'] = []; // need empty array
        // Software resource type
        $this->software = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field2' => 'M3', // Medium
                'field4' => 'IS', // Version
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1', // Programmer
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->software['resource_publisher']['publisherName'] = 'PB';
        $this->software['resource_publisher']['publisherLocation'] = 'CY';
        $this->software['resource_collection'] = []; // need empty array
        // Artwork resource type
        $this->artwork = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field2' => 'M1', // Medium
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->artwork['resource_publisher']['publisherName'] = 'PB';
        $this->artwork['resource_publisher']['publisherLocation'] = 'CY';
        $this->artwork['resource_collection'] = []; // need empty array
        // Audiovisual resource type
        $this->audiovisual = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->audiovisual['resource_publisher']['publisherName'] = 'PB';
        $this->audiovisual['resource_publisher']['publisherLocation'] = 'CY';
        $this->audiovisual['resource_collection'] = []; // need empty array
        // case resource type
        $this->case = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field1' => 'CY', // Reporter
                'field4' => 'IS', // Reporter Volume
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator3' => 'A1', // Counsel
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->case['resource_publisher']['publisherName'] = 'PB'; // Court
        $this->case['resource_collection'] = []; // need empty array
// bill resource type
        $this->bill = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field2' => 'CY', // Code
                'field5' => 'VL', // Bill Number
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [// we require an empty array to still be present
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->bill['resource_publisher'] = []; // need empty array
        $this->bill['resource_collection'] = []; // need empty array
// classical resource type
        $this->classical = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field4' => 'T2', // Volume
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1', // Attribution
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->classical['resource_publisher'] = []; // need empty array
        $this->classical['resource_collection'] = []; // need empty array
// Conference Paper resource type
        $this->conference_paper = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->conference_paper['resource_publisher']['publisherName'] = 'PB';
        $this->conference_paper['resource_publisher']['publisherLocation'] = 'CY';
        $this->conference_paper['resource_collection'] = []; // need empty array
        // Conference Poster resource type
        $this->conference_poster = [
            'resource' => [
                'isbn' => 'SN',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->conference_poster['resource_publisher']['publisherName'] = 'PB';
        $this->conference_poster['resource_publisher']['publisherLocation'] = 'CY';
        $this->conference_poster['resource_collection']['collectionTitle'] = 'T1';
        // Legal Rule/Regulation resource type
        $this->legal_ruling = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field3' => 'VL', // Edition
                'field4' => 'IS', // Rule Number
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->legal_ruling['resource_publisher']['publisherName'] = 'PB';
        $this->legal_ruling['resource_publisher']['publisherLocation'] = 'CY';
        $this->legal_ruling['resource_collection'] = []; // need empty array
        // Government Report/Documentation resource type
        $this->government_report = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field2' => 'A3', // Department
                'field3' => 'VL', // Edition
                'field5' => 'IS', // Rule Number
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
                'creator2' => 'A2', // Editor
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->government_report['resource_publisher']['publisherName'] = 'PB';
        $this->government_report['resource_publisher']['publisherLocation'] = 'CY';
        $this->government_report['resource_collection'] = []; // need empty array
        // Report/Documentation resource type
        $this->report = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field2' => 'M1', // Type of Work
                'field5' => 'VL', // Report number
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
                'creator2' => 'A2', // Editor
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->report['resource_publisher']['publisherName'] = 'PB';
        $this->report['resource_publisher']['publisherLocation'] = 'CY';
        $this->report['resource_collection'] = []; // need empty array
        // Hearing resource type
        $this->hearing = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
                'field1' => 'CY', // Committee
                'field2' => 'PB', // Legislative Body
                'field4' => 'VL', // Document Number
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
            ],
            'resource_creator' => [// empty array required
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->hearing['resource_publisher']['publisherName'] = []; // need empty array
        $this->hearing['resource_collection'] = []; // need empty array
// Online database resource type
        $this->database = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->database['resource_publisher']['publisherName'] = 'PB';
        $this->database['resource_collection'] = []; // need empty array
        // Magazine article type
        $this->magazine_article = [
            'resource' => [
                'isbn' => 'SN',
                'field4' => 'VL',
                'field3' => 'IS',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [// empty array required
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->magazine_article['resource_publisher'] = []; // need empty array
        $this->magazine_article['resource_collection']['collectionTitle'] = 'JF';
        // Manuscript type
        $this->manuscript = [
            'resource' => [
                'isbn' => 'SN',
                'field2' => 'M1', // Type of Work
                'field3' => 'IS', // Edition
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->manuscript['resource_publisher'] = []; // need empty array
        $this->manuscript['resource_collection']['collectionTitle'] = 'T2';
        // Map type
        $this->map = [
            'resource' => [
                'isbn' => 'SN',
                'field2' => 'M2', // Type of Work
                'field3' => 'VL', // Edition
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1', // Cartographer
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->map['resource_publisher']['publisherName'] = 'PB';
        $this->map['resource_publisher']['publisherLocation'] = 'CY';
        $this->map['resource_collection'] = []; // need empty array
        // Chart type
        $this->chart = [
            'resource' => [
                'isbn' => 'SN',
                'field1' => 'T2', // Name of File
                'field2' => 'T3', // Image Source Program
                'field5' => 'VL', // Version
                'field6' => 'IS',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1', // Created By
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->chart['resource_publisher']['publisherName'] = 'PB';
        $this->chart['resource_publisher']['publisherLocation'] = 'CY';
        $this->chart['resource_collection'] = []; // need empty array
        // Statute type
        $this->statute = [
            'resource' => [
                'isbn' => 'SN',
                'field2' => 'CY', // Code
                'field5' => 'VL', // Code Number
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [// empty array required
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->statute['resource_publisher'] = []; // need empty array
        $this->statute['resource_collection'] = []; // need empty array
// Patent type
        $this->patent = [
            'resource' => [
                'isbn' => 'SN',
                'field3' => 'VL', // Application Number
                'field4' => 'M3', // Patent Type
                'field7' => 'M1', // International Patent Classification
                'field8' => 'IS', // Patent Number
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1', // Inventor
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->patent['resource_publisher']['publisherName'] = 'A2'; // Assignees
        $this->patent['resource_publisher']['publisherLocation'] = 'CY';
        $this->patent['resource_collection'] = []; // need empty array
        // Personal Communication type
        $this->personal = [
            'resource' => [
                'isbn' => 'SN',
                'field2' => 'M1',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
                'creator2' => 'A2', // Recipient
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->personal['resource_publisher'] = []; // need empty array
        $this->personal['resource_collection'] = []; // need empty array
// Unpublished type
        $this->unpublished = [
            'resource' => [
                'isbn' => 'SN',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->unpublished['resource_publisher'] = []; // need empty array
        $this->unpublished['resource_collection'] = []; // need empty array
// proceedings (complete set)
        $this->proceedings = [
            'resource' => [
                'isbn' => 'SN', // ISBN/ISSN
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [// we require an empty array to still be present
            ],
            'resource_creator' => [
                'creator2' => 'A2', // Editor
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->proceedings['resource_publisher']['publisherName'] = 'PB';
        $this->proceedings['resource_publisher']['publisherLocation'] = 'CY';
        $this->proceedings['resource_collection'] = []; // need empty array
        // Music Album type
        $this->music_album = [
            'resource' => [
                'isbn' => 'SN',
                'field2' => 'M3', // medium
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A2', // Performer
                'creator2' => 'A1', // Composer
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->music_album['resource_publisher']['publisherName'] = 'PB';
        $this->music_album['resource_publisher']['publisherLocation'] = 'CY';
        $this->music_album['resource_collection'] = []; // need empty array
        // Music Track type
        $this->music_track = [
            'resource' => [
                'isbn' => 'SN',
                'field2' => 'M3', // medium
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A2', // Performer
                'creator2' => 'A1', // Composer
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->music_track['resource_publisher']['publisherName'] = 'PB';
        $this->music_track['resource_publisher']['publisherLocation'] = 'CY';
        $this->music_track['resource_collection']['collectionTitle'] = 'M1';
        // Music Score type
        $this->music_score = [
            'resource' => [
                'isbn' => 'SN',
                'field3' => 'VL', // Edition
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1', // Composer
                'creator2' => 'A2', // Editor
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->music_score['resource_publisher']['publisherName'] = 'PB';
        $this->music_score['resource_publisher']['publisherLocation'] = 'CY';
        $this->music_score['resource_collection'] = []; // need empty array
        // Miscellaneous resource type
        $this->miscellaneous = [
            'resource' => [
                'isbn' => 'SN',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->miscellaneous['resource_publisher']['publisherName'] = 'PB';
        $this->miscellaneous['resource_publisher']['publisherLocation'] = 'CY';
        $this->miscellaneous['resource_collection'] = []; // need empty array
        // Miscellaneous Section resource type
        $this->miscellaneous_section = [
            'resource' => [
                'isbn' => 'SN',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
        $this->miscellaneous_section['resource_publisher']['publisherName'] = 'PB';
        $this->miscellaneous_section['resource_publisher']['publisherLocation'] = 'CY';
        $this->miscellaneous_section['resource_collection']['collectionTitle'] = 'M1';
        // Brochure resource type
        $this->brochure = [
            'resource' => [
                'isbn' => 'SN',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                'creator1' => 'A1',
            ],
            'resource_text' => [
                'urls' => 'UR', // URL
            ],
        ];
    }
}
