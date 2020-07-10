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
 * Map BibTeX fields
 *
 * @package wikindx\core\importexport
 */
class BIBTEXMAP
{
    /** array */
    public $types;
    /** array */
    public $genres;
    /** array */
    public $basic;
    /** array */
    public $validFields;
    /** array */
    public $noMap;
    /** array */
    public $book;
    /** array */
    public $book_article;
    /** array */
    public $book_chapter;
    /** array */
    public $journal_article;
    /** array */
    public $newspaper_article;
    /** array */
    public $magazine_article;
    /** array */
    public $proceedings_article;
    /** array */
    public $thesis;
    /** array */
    public $web_site;
    /** array */
    public $web_article;
    /** array */
    public $web_encyclopaedia;
    /** array */
    public $web_encyclopaedia_article;
    /** array */
    public $film;
    /** array */
    public $broadcast;
    /** array */
    public $software;
    /** array */
    public $artwork;
    /** array */
    public $audiovisual;
    /** array */
    public $case;
    /** array */
    public $legal_ruling;
    /** array */
    public $bill;
    /** array */
    public $classical;
    /** array */
    public $conference_paper;
    /** array */
    public $miscellaneous;
    /** array */
    public $miscellaneous_section;
    /** array */
    public $government_report;
    /** array */
    public $report;
    /** array */
    public $hearing;
    /** array */
    public $database;
    /** array */
    public $manuscript;
    /** array */
    public $map;
    /** array */
    public $chart;
    /** array */
    public $statute;
    /** array */
    public $patent;
    /** array */
    public $personal;
    /** array */
    public $proceedings;
    /** array */
    public $music_album;
    /** array */
    public $music_track;
    /** array */
    public $music_score;
    /** array */
    public $unpublished;
    /** array */
    public $brochure;

    /**
     * BIBTEXMAP
     */
    public function __construct()
    {
        $this->loadMap();
    }
    /**
     * Create arrays based on database table and resource type.
     * The arrays are named for WIKINDX resource types.
     * The key of the resource_xxx arrays is the database field within the WKX_resource_xxx database table. For exporting
     * BibTeX files this is not needed but isused for BibTeX import.
     * The key of the resource_xxx array is the WIKINDX field in that table while the value is the BibTeX
     * equivalent field.  For an export, these keys can be found as the fields in the SQL $row assoc. array in
     * core/file/export/BIBTEX.php.
     *
     * The database matrix can be found in docs/resourceDatabaseMatrix.html
     *
     * Some things must be sorted out on the fly in the import/export code:
     * 1/ BibTeX only has a field 'Pages' whereas WIKINDX uses pageStart and pageEnd fields.
     * 2/ The 'howpublished' key is intended for bibTex's misc types.  For web_article and
     * database resource types, this is added in the code so don't add it here.
     * 3/ The resource_creator array has keys that represent the `resourcecreatorRole` field in the `resource_creator` table. Here, '1'
     * is the primary author while '2' is the secondary author (usually editor for bibtex).
     * 4/ Most dates (newspaper, magazine, hearing etc.) have to be created on the fly from database fields `miscField2`
     * (day) and `miscField3` (month).
     * 5/ A WIKINDX 'title' is comprised of `subtitle`, `title` and `noSort` fields.
     *
     * When importing a bibTeX file, you must ignore the 'howpublished' key as defined here and deal with any @misc howpublished
     * field manually in the code.
     *
     * The 'possible' array lists ALL possible fields that wikindx will accept for each type when importing.
     */
    public function loadMap()
    {
        // WIKINDX resource types (keys) and their BibTeX equivalent (values).
        // NB - mastersthesis and phdthesis sorted out on the fly in code.
        $this->types = [
            'book' => 'book',
            'book_article' => 'incollection',
            'book_chapter' => 'inbook',
            'journal_article' => 'article',
            'newspaper_article' => 'article',
            'magazine_article' => 'article',
            'proceedings' => 'proceedings',
            'conference_paper' => 'misc',
            'conference_poster' => 'misc',
            'proceedings_article' => 'inproceedings',
            'web_article' => 'misc',
            'web_site' => 'misc',
            'web_encyclopedia' => 'misc',
            'web_encyclopedia_article' => 'misc',
            'film' => 'misc',
            'broadcast' => 'misc',
            'music_album' => 'misc',
            'music_track' => 'misc',
            'music_score' => 'misc',
            'artwork' => 'misc',
            'software' => 'misc',
            'audiovisual' => 'misc',
            'database' => 'misc',
            'government_report' => 'misc',
            'report' => 'techreport',
            'hearing' => 'misc',
            'statute' => 'misc',
            'legal_ruling' => 'misc',
            'case' => 'misc',
            'bill' => 'misc',
            'patent' => 'patent',
            'personal' => 'misc',
            'unpublished' => 'unpublished',
            'classical' => 'misc',
            'manuscript' => 'misc',
            'map' => 'misc',
            'chart' => 'misc',
            'miscellaneous' => 'misc',
            'miscellaneous_section' => 'misc',
            'brochure' => 'misc',
        ];
        // Valid bibtex fields that WIKINDX recognizes
        $this->validFields = [
            'abstract', 'address', 'author', 'booktitle', 'edition', 'editor', 'howpublished',
            'institution', 'chapter', 'doi',
            'journal', 'month', 'note', 'number', 'organization', 'pages', 'publisher', 'school',
            'series', 'title', 'type', 'volume', 'year', 'url', 'isbn', 'issn', 'keywords',
        ];
        // Forbid mapping of unknown import fields to these fields
        $this->noMap = ['title', 'editor', 'author'];
        // Book resource type
        $this->book = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'series', // Series Title
                'resourceField2' => 'edition',
                'resourceField3' => 'number', // Series number
                'resourceField4' => 'volume', // Volume number
            ],
            'resource_misc' => [
                'resourcemiscField6' => 'pages', // num pages
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
                2 => 'editor',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->book['resource_publisher']['publisherName'] = 'publisher';
        $this->book['resource_publisher']['publisherLocation'] = 'address';
        $this->book['resource_collection'] = []; // need empty array
        $this->book['possible'] = ['title', 'url', 'isbn', 'series', 'edition', 'number', 'volume', 'year',
            'author', 'editor', 'publisher', 'address', 'doi', 'pages', ];
        // book article resource type
        $this->book_article = $this->book;
        $this->book_article['resource_collection']['collectionTitle'] = 'booktitle';
        $this->book_article['possible'][] = 'booktitle';
        unset($this->book_article['resource_misc']['resourcemiscField6']);
        $this->book_article['possible'][] = 'pages';
        $this->book_article['possible'][] = 'chapter';
        // book chapter resource type
        $this->book_chapter = $this->book;
        $this->book_chapter['resource_collection']['collectionTitle'] = 'booktitle';
        $this->book_chapter['possible'][] = 'booktitle';
        unset($this->book_chapter['resource_misc']['resourcemiscField6']);
        $this->book_chapter['possible'][] = 'pages';
        $this->book_chapter['possible'][] = 'chapter';
        // Journal article resource type
        $this->journal_article = [
            'resource' => [
                'resourceIsbn' => 'ISSN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'volume',
                'resourceField2' => 'number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->journal_article['resource_publisher'] = []; // need empty array
        $this->journal_article['resource_collection']['collectionTitle'] = 'journal';
        $this->journal_article['possible'] = ['title', 'url', 'issn', 'number', 'volume', 'year', 'author',
            'journal', 'pages', 'month', 'doi', ];
        // Newspaper article type
        $this->newspaper_article = [
            'resource' => [
                'resourceIsbn' => 'ISSN',
                'resourceDoi' => 'DOI',
                'resourceField2' => 'address',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->newspaper_article['resource_publisher'] = []; // need empty array
        $this->newspaper_article['resource_collection']['collectionTitle'] = 'journal';
        $this->newspaper_article['possible'] = ['title', 'url', 'issn', 'year', 'author', 'journal', 'pages',
            'address', 'month', 'doi', ];
        // Magazine article type
        $this->magazine_article = [
            'resource' => [
                'resourceIsbn' => 'ISSN',
                'resourceDoi' => 'DOI',
                'resourceField4' => 'volume',
                'resourceField3' => 'number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->magazine_article['resource_publisher'] = []; // need empty array
        $this->magazine_article['resource_collection']['collectionTitle'] = 'journal';
        $this->magazine_article['possible'] = ['title', 'url', 'issn', 'number', 'volume', 'year', 'author',
            'journal', 'pages', 'month', 'doi', ];
        // proceedings article
        $this->proceedings_article = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'series', // Series Title
                'resourceField3' => 'number', // Series number
                'resourceField4' => 'volume', // Volume number
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear2' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
                2 => 'editor',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->proceedings_article['resource_collection']['conferenceOrganiser'] = 'organization';
        $this->proceedings_article['resource_collection']['conferenceLocation'] = 'location';
        $this->proceedings_article['resource_publisher']['publisherName'] = 'publisher';
        $this->proceedings_article['resource_publisher']['publisherLocation'] = 'address';
        $this->proceedings_article['resource_collection']['collectionTitle'] = 'booktitle';
        $this->proceedings_article['possible'] = ['title', 'url', 'isbn', 'year', 'author', 'editor',
            'publisher', 'address', 'organization', 'location', 'journal', 'pages', 'booktitle',
            'series', 'volume', 'doi', ];
        // Conference Paper resource type -> 'misc'
        $this->conference_paper = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->conference_paper['resource_publisher']['publisherName'] = 'organization';
        $this->conference_paper['resource_publisher']['publisherLocation'] = 'location';
        $this->conference_paper['resource_collection'] = []; // need empty array
        $this->conference_paper['possible'] = ['title', 'url', 'isbn', 'year', 'author', 'organization', 'location', 'doi'];
        // Conference Poster resource type -> 'misc'
        $this->conference_poster = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->conference_poster['resource_publisher']['publisherName'] = 'organization';
        $this->conference_poster['resource_publisher']['publisherLocation'] = 'location';
        $this->conference_poster['resource_collection'] = []; // need empty array
        $this->conference_poster['possible'] = ['title', 'url', 'isbn', 'year', 'author', 'organization', 'location', 'doi'];
        // proceedings (complete set)
        $this->proceedings = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear2' => 'year',
            ],
            'resource_creator' => [
                2 => 'editor',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->proceedings['resource_publisher']['publisherName'] = 'organization';
        $this->proceedings['resource_publisher']['publisherLocation'] = 'location';
        $this->proceedings['resource_collection'] = []; // need empty array
        $this->proceedings['possible'] = ['title', 'url', 'isbn', 'year', 'organization', 'location', 'doi'];
        // Thesis resource type
        $this->thesis = [
            'resource' => [
                'resourceIsbn' => 'ISSN',
                'resourceDoi' => 'DOI',
                'resourceField3' => 'volume',
                'resourceField4' => 'number',
            ],
            'resource_misc' => [  // empty array required
                'resourcemiscField6' => 'pages', // num pages
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->thesis['resource_publisher']['publisherName'] = 'school';
        $this->thesis['resource_publisher']['publisherLocation'] = 'address';
        $this->thesis['resource_collection']['collectionTitle'] = 'journal';
        $this->thesis['possible'] = ['title', 'url', 'issn', 'number', 'volume', 'year', 'author',
            'school', 'address', 'journal', 'pages', 'doi', ];
        // Web article resource type -> 'misc' - URL dealt with in code
        $this->web_article = [
            'resource' => [
                'resourceIsbn' => 'ISSN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'volume',
                'resourceField2' => 'number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
                2 => 'editor',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->web_article['resource_publisher'] = []; // need empty array
        $this->web_article['resource_collection']['collectionTitle'] = 'journal';
        $this->web_article['possible'] = ['title', 'url', 'issn', 'number', 'volume', 'year', 'author',
            'journal', 'pages', 'doi', ];
        // Web site resource type
        $this->web_site = [
            'resource' => [
                'resourceIsbn' => 'ISSN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'author',
                2 => 'editor',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->web_site['resource_publisher'] = []; // need empty array
        $this->web_site['resource_collection'] = []; // need empty array
        $this->web_site['possible'] = ['title', 'url', 'issn', 'year', 'author', 'doi'];
        // Web encyclopedia resource type
        $this->web_encyclopedia = [
            'resource' => [
                'resourceIsbn' => 'ISSN',
                'resourceDoi' => 'DOI',
                'resourceField2' => 'edition',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'author',
                2 => 'editor',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->web_encyclopedia['resource_publisher']['publisherName'] = 'publisher';
        $this->web_encyclopedia['resource_publisher']['publisherLocation'] = 'address';
        $this->web_encyclopedia['resource_collection'] = []; // need empty array
        $this->web_encyclopedia['possible'] = ['title', 'url', 'issn', 'year', 'author', 'editor', 'edition', 'doi'];
        // Web encyclopedia article resource type
        $this->web_encyclopedia_article = [
            'resource' => [
                'resourceIsbn' => 'ISSN',
                'resourceDoi' => 'DOI',
                'resourceField2' => 'edition',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'author',
                2 => 'editor',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->web_encyclopedia_article['resource_publisher']['publisherName'] = 'publisher';
        $this->web_encyclopedia_article['resource_publisher']['publisherLocation'] = 'address';
        $this->web_encyclopedia_article['resource_collection']['collectionTitle'] = 'booktitle';
        $this->web_encyclopedia_article['possible'] = ['title', 'url', 'issn', 'year', 'author', 'editor', 'edition', 'doi'];
        // Film resource type -> misc
        $this->film = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'address',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author', // director
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'film',
        ];
        $this->film['resource_publisher']['publisherName'] = 'publisher'; // distributor
        $this->film['resource_collection'] = []; // need empty array
        $this->film['possible'] = ['title', 'url', 'isbn', 'year', 'author', 'publisher', 'address', 'doi'];
        // Broadcast resource type -> misc
        $this->broadcast = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author', // director
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'broadcast',
        ];
        $this->broadcast['resource_publisher']['publisherName'] = 'publisher'; // channel
        $this->broadcast['resource_publisher']['publisherLocation'] = 'address';
        $this->broadcast['resource_collection'] = []; // need empty array
        $this->broadcast['possible'] = ['title', 'url', 'isbn', 'year', 'author',
            'publisher', 'address', 'doi', ];
        // Music Album type -> misc
        $this->music_album = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author', // Performer
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'resourceField2',
        ];
        $this->music_album['resource_publisher']['publisherName'] = 'publisher';
        $this->music_album['resource_publisher']['publisherLocation'] = 'address';
        $this->music_album['resource_collection'] = []; // need empty array
        $this->music_album['possible'] = ['title', 'url', 'isbn', 'year', 'author', 'publisher', 'address', 'doi'];
        // Music Track type -> misc
        $this->music_track = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'resourceField2',
        ];
        $this->music_track['resource_publisher']['publisherName'] = 'publisher';
        $this->music_track['resource_publisher']['publisherLocation'] = 'address';
        $this->music_track['resource_collection']['collectionTitle'] = 'booktitle'; // album/collection
        $this->music_track['possible'] = ['title', 'url', 'isbn', 'year', 'author', 'publisher', 'address',
            'booktitle', 'doi', ];
        // Music Score type -> misc
        $this->music_score = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField3' => 'edition',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author', // Composer
                2 => 'editor', // Editor
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'Music Score',
        ];
        $this->music_score['resource_publisher']['publisherName'] = 'publisher';
        $this->music_score['resource_publisher']['publisherLocation'] = 'address';
        $this->music_score['resource_collection'] = []; // need empty array
        $this->music_score['possible'] = ['title', 'url', 'isbn', 'edition', 'year', 'author',
            'editor', 'publisher', 'address', 'doi', ];
        // Software resource type -> misc
        $this->software = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField4' => 'edition', // Version
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author', // Programmer
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'resourceField2',
        ];
        $this->software['resource_publisher']['publisherName'] = 'publisher';
        $this->software['resource_publisher']['publisherLocation'] = 'address';
        $this->software['resource_collection'] = []; // need empty array
        $this->software['possible'] = ['title', 'url', 'isbn', 'edition', 'year', 'author', 'publisher', 'address', 'doi'];
        // Artwork resource type -> misc
        $this->artwork = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'resourceField2',
        ];
        $this->artwork['resource_publisher']['publisherName'] = 'publisher';
        $this->artwork['resource_publisher']['publisherLocation'] = 'address';
        $this->artwork['resource_collection'] = []; // need empty array
        $this->artwork['possible'] = ['title', 'url', 'isbn', 'year', 'author', 'publisher', 'address', 'doi'];
        // Audiovisual resource type -> misc
        $this->audiovisual = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'series',
                'resourceField3' => 'edition',
                'resourceField5' => 'volume',
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
                'creator5' => 'editor', // Series Editor
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'resourceField2',
        ];
        $this->audiovisual['resource_publisher']['publisherName'] = 'publisher';
        $this->audiovisual['resource_publisher']['publisherLocation'] = 'address';
        $this->audiovisual['resource_collection'] = []; // need empty array
        $this->audiovisual['possible'] = ['title', 'url', 'isbn', 'series', 'edition', 'volume',
            'year', 'author', 'editor', 'publisher', 'address', 'doi', ];
        // Online database resource type -> misc
        $this->database = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear2' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->database['resource_publisher']['publisherName'] = 'publisher';
        $this->database['resource_collection'] = []; // need empty array
        $this->database['possible'] = ['title', 'url', 'isbn', 'year', 'author', 'publisher', 'doi'];
        // Government Report/Documentation resource type -> misc
        $this->government_report = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField2' => 'institution', // Department
                'resourceField3' => 'edition',
                'resourceField4' => 'volume',
                'resourceField5' => 'number', // Issue number
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
                2 => 'editor',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'Government Report',
        ];
        $this->government_report['resource_publisher']['publisherName'] = 'publisher';
        $this->government_report['resource_publisher']['publisherLocation'] = 'location';
        $this->government_report['resource_collection'] = []; // need empty array
        $this->government_report['possible'] = ['title', 'url', 'isbn', 'institution', 'edition',
            'volume', 'number', 'year', 'author', 'editor', 'publisher', 'location', 'pages', 'doi', ];
        // Report/Documentation resource type
        $this->report = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'series', // Series Title
                'resourceField2' => 'type',
                'resourceField5' => 'number', // Report number
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
                2 => 'editor',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->report['resource_publisher']['publisherName'] = 'institution';
        $this->report['resource_publisher']['publisherLocation'] = 'location';
        $this->report['resource_collection'] = []; // need empty array
        $this->report['possible'] = ['title', 'url', 'isbn', 'series', 'type', 'number',
            'year', 'author', 'editor', 'institution', 'location', 'address', 'pages', 'doi', ];
        // Hearing resource type -> misc
        $this->hearing = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'booktitle', // Committee
                'resourceField2' => 'journal', // Legislative Body
                'field3' => 'edition', // Session
                'resourceField4' => 'number', // Document Number
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [// empty array required
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'Hearing',
        ];
        $this->hearing['resource_publisher']['publisherName'] = 'publisher';
        $this->hearing['resource_publisher']['publisherLocation'] = 'address';
        $this->hearing['resource_collection'] = []; // need empty array
        $this->hearing['possible'] = ['title', 'url', 'isbn', 'booktitle', 'journal', 'edition', 'number',
            'year', 'publisher', 'address', 'pages', 'doi', ];
        // Statute type -> misc
        $this->statute = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'number', // Public Law Number
                'resourceField2' => 'booktitle', // Code
                'resourceField3' => 'edition', // Session
                'resourceField5' => 'volume', // Code Number
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [// empty array required
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'Statute',
        ];
        $this->statute['resource_publisher'] = []; // need empty array
        $this->statute['resource_collection'] = []; // need empty array
        $this->statute['possible'] = ['title', 'url', 'isbn', 'number', 'booktitle', 'edition', 'volume',
            'year', 'pages', 'doi', ];
        // Legal Rule/Regulation resource type -> misc
        $this->legal_ruling = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'volume', // Section Number
                'resourceField2' => 'type',
                'resourceField3' => 'edition',
                'resourceField4' => 'number', // Rule Number
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'Legal Ruling/Regulation',
        ];
        $this->legal_ruling['resource_publisher']['publisherName'] = 'publisher';
        $this->legal_ruling['resource_publisher']['publisherLocation'] = 'address';
        $this->legal_ruling['resource_collection'] = []; // need empty array
        $this->legal_ruling['possible'] = ['title', 'url', 'isbn', 'volume', 'type', 'edition', 'number',
            'year', 'author', 'publisher', 'address', 'pages', 'doi', ];
        // case resource type -> misc
        $this->case = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'booktitle', // Reporter
                'resourceField4' => 'volume', // Reporter Volume
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                'creator3' => 'author', // Counsel
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'Legal Case',
        ];
        $this->case['resource_publisher']['publisherName'] = 'publisher';
        $this->case['resource_collection'] = []; // need empty array
        $this->case['possible'] = ['title', 'url', 'isbn', 'booktitle', 'volume', 'year', 'author', 'publisher', 'pages', 'doi'];
        // bill resource type -> misc
        $this->bill = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField2' => 'booktitle', // Code
                'resourceField3' => 'volume', // Code Volume
                'resourceField4' => 'edition', // Session
                'resourceField5' => 'number', // Bill Number
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [// we require an empty array to still be present
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'Bill',
        ];
        $this->bill['resource_publisher'] = []; // need empty array
        $this->bill['resource_collection'] = []; // need empty array
        $this->bill['possible'] = ['title', 'url', 'isbn', 'booktitle', 'volume', 'edition', 'number', 'year', 'pages', 'doi'];
        // Patent type
        $this->patent = [
            'resource' => [
                'resourceDoi' => 'DOI',
                'resourceField8' => 'ISBN', // Patent Number
                'resourceDield1' => 'booktitle', // Published Source
                'resourceField2' => 'volume', // Patent Version Number
                'resourceField3' => 'number', // Application Number
                'resourceField4' => 'type', // Patent Type
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author', // Inventor
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->patent['resource_publisher']['publisherName'] = 'publisher'; // assignee
        $this->patent['resource_publisher']['publisherLocation'] = 'address';
        $this->patent['resource_collection'] = []; // need empty array
        $this->patent['possible'] = ['title', 'url', 'isbn', 'volume', 'booktitle', 'number', 'type',
            'year', 'author', 'publisher', 'address', 'doi', ];
        // Brochure type -> misc
        $this->brochure = [
            'resource' => [
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author', // company
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->brochure['resource_publisher'] = []; // need empty array
        $this->brochure['resource_collection'] = []; // need empty array
        $this->brochure['possible'] = ['title', 'url', 'author', 'doi'];
        // Personal Communication type -> misc
        $this->personal = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
                2 => 'editor', // Recipient
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'resourceField2',
        ];
        $this->personal['resource_publisher'] = []; // need empty array
        $this->personal['resource_collection'] = []; // need empty array
        $this->personal['possible'] = ['title', 'url', 'isbn', 'year', 'author', 'editor', 'doi'];
        // Unpublished type
        $this->unpublished = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField2' => 'type',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
        ];
        $this->unpublished['resource_publisher']['publisherName'] = 'institution'; // Institution
        $this->unpublished['resource_publisher']['publisherLocation'] = 'address'; // Institution Location
        $this->unpublished['resource_collection'] = []; // need empty array
        $this->unpublished['possible'] = ['title', 'url', 'isbn', 'type', 'year', 'author', 'institution',
            'address', 'pages', 'doi', ];
        // classical resource type -> misc
        $this->classical = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField4' => 'volume',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author', // Attribution
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'Classical Work',
        ];
        $this->classical['resource_publisher'] = []; // need empty array
        $this->classical['resource_collection'] = []; // need empty array
        $this->classical['possible'] = ['title', 'url', 'isbn', 'volume', 'year', 'author', 'doi'];
        // Manuscript type -> misc
        $this->manuscript = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField3' => 'number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'resourceField2',
        ];
        $this->manuscript['resource_publisher'] = []; // need empty array
        $this->manuscript['resource_collection']['collectionTitle'] = 'booktitle';
        $this->manuscript['possible'] = ['title', 'url', 'isbn', 'number', 'year', 'author', 'booktitle', 'pages', 'doi'];
        // Map type -> misc
        $this->map = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField1' => 'series', // Series Title
                'resourceField3' => 'edition',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author', // Cartographer
                'creator5' => 'editor', // Series Editor
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'resourceField2',
        ];
        $this->map['resource_publisher']['publisherName'] = 'publisher';
        $this->map['resource_publisher']['publisherLocation'] = 'address';
        $this->map['resource_collection'] = []; // need empty array
        $this->map['possible'] = ['title', 'url', 'isbn', 'series', 'edition', 'year', 'author', 'editor',
            'publisher', 'address', 'doi', ];
        // Chart type -> misc
        $this->chart = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
                'resourceField3' => 'size',
                'resourceField5' => 'edition', // Version
                'resourceField6' => 'number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author', // Created By
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'field4',
        ];
        $this->chart['resource_publisher']['publisherName'] = 'publisher';
        $this->chart['resource_publisher']['publisherLocation'] = 'address';
        $this->chart['resource_collection'] = []; // need empty array
        $this->chart['possible'] = ['title', 'url', 'isbn', 'size', 'edition', 'number', 'year', 'author',
            'publisher', 'address', 'doi', ];
        // Miscellaneous resource type -> misc
        $this->miscellaneous = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'resourceField2',
        ];
        $this->miscellaneous['resource_publisher']['publisherName'] = 'publisher';
        $this->miscellaneous['resource_publisher']['publisherLocation'] = 'address';
        $this->miscellaneous['resource_collection'] = []; // need empty array
        $this->miscellaneous['possible'] = ['title', 'url', 'isbn', 'year', 'author', 'publisher', 'address', 'doi'];
        // Miscellaneous Section resource type -> misc
        $this->miscellaneous_section = [
            'resource' => [
                'resourceIsbn' => 'ISBN',
                'resourceDoi' => 'DOI',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'year',
            ],
            'resource_creator' => [
                1 => 'author',
            ],
            'resource_text' => [
                'resourcetextUrls' => 'URL',
            ],
            'howpublished' => 'resourceField2',
        ];
        $this->miscellaneous_section['resource_publisher']['publisherName'] = 'publisher';
        $this->miscellaneous_section['resource_publisher']['publisherLocation'] = 'address';
        $this->miscellaneous_section['resource_collection']['collectionTitle'] = 'booktitle';
        $this->miscellaneous_section['possible'] = ['title', 'url', 'isbn', 'year', 'author',
            'publisher', 'address', 'booktitle', 'doi', ];
    }
}
