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
 *	Endnote Map class.
 *	Provides mappings for Endnote export/import
 */
class ENDNOTEMAP
{
    public $types;
    public $typesv8;
    public $importTypes8;
    public $exportTypes8;
    public $generic;
    public $importTypesPre8;
    public $endnoteXmlFields8;
    public $endnoteXmlFieldsPre8;
    public $exportRawFields;
    public $exportRawFieldsXml;
    public $noMap;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadMap();
    }
    /**
     * Create arrays based on database table and resource type.
     * The arrays are named for WIKINDX resource types.
     * The key of the resource_xxx arrays is the database resourceField within the WKX_resource_xxx database table. For exporting
     * Endnote tabbed files this is not needed but will prove useful should there be an eventual Endnote import.
     * The key of the resource_xxx array is the WIKINDX resourceField in that table while the value is the Endnote
     * equivalent resourceField (Generic).  For an export, these keys can be found as the resourceFields in the SQL $row assoc. array.
     *
     * The database matrix can be found in docs/resourceDatabaseMatrix.html
     *
     * Some things must be sorted out on the fly in the import/export code:
     * 1/ Endnote only has a resourceField 'Pages' whereas WIKINDX uses pageStart and pageEnd resourceFields.
     * 2/ Endnote has a separate type for an 'Edited Book' - WIKINDX makes no such distinction but, if a WIKINDX book
     * has editors but no author, then it is an Endnote 'Edited Book'.
     * 3/ WIKINDX book and book_article types can have a reprintYear in `resourceyearYear2`.  If a book has this in addition to a
     * publicationYear in `resourceyearYear1` then we need to switch the two resourceFields so that `resourceyearYear1` is Endnote's 'Original Publication'
     * resourceField and `resourceyearYear2` is Endnote's 'Year'.  They are defined here as if there were no reprintYear.
     * 4/ Most dates (newspaper, magazine, hearing etc.) have to be created on the fly from database resourceFields `resourcemiscField2`
     * (day) and `resourcemiscField3` (month).
     * 5/ A WIKINDX 'title' is comprised of `subtitle`, `title` and `noSort` resourceFields.
     */
    private function loadMap()
    {
        // WIKINDX resource types (keys) and their Endnote tabbed file equivalent (values).
        $this->types = [
            'book' => 'Book',
            'book_article' => 'Book Section',
            'book_chapter' => 'Book Section',
            'journal_article' => 'Journal Article',
            'newspaper_article' => 'Newspaper Article',
            'magazine_article' => 'Magazine Article',
            'proceedings' => 'Generic',
            'conference_paper' => 'Conference Paper',
            'proceedings_article' => 'Conference Proceedings',
            'thesis' => 'Thesis',
            'web_article' => 'Electronic Source',
            'web_site' => 'Online Multimedia',
            'web_encyclopedia' => 'Electronic Source',
            'web_encyclopedia_article' => 'Electronic Source',
            'film' => 'Film',
            'broadcast' => 'Broadcast',
            'music_album' => 'Generic',
            'music_track' => 'Generic',
            'music_score' => 'Generic',
            'artwork' => 'Artwork',
            'software' => 'Computer Program',
            'audiovisual' => 'Audiovisual Material',
            'database' => 'Online Database',
            'government_report' => 'Government Report/Document',
            'report' => 'Report',
            'hearing' => 'Hearing',
            'statute' => 'Statute',
            'legal_ruling' => 'Legal Rule/Regulation',
            'case' => 'Case',
            'bill' => 'Bill',
            'patent' => 'Patent',
            'personal' => 'Personal Communication',
            'unpublished' => 'Unpublished Work',
            'classical' => 'Classical Work',
            'manuscript' => 'Manuscript',
            'map' => 'Map',
            'chart' => 'Chart or Table',
            'miscellaneous' => 'Generic',
            'miscellaneous_section' => 'Generic',
            'conference_poster' => 'Generic',
            'brochure' => 'Generic',
        ];
        // WIKINDX resource types (keys) and their Endnote v8 XML equivalent (values).
        $this->typesv8 = [
            'book' => 'Book',
            'book_article' => 'Book Section',
            'book_chapter' => 'Book Section',
            'journal_article' => 'Journal Article',
            'newspaper_article' => 'Newspaper Article',
            'magazine_article' => 'Magazine Article',
            'proceedings' => 'Conference Proceeding',
            'conference_paper' => 'Conference Paper',
            'proceedings_article' => 'Conference Proceeding',
            'thesis' => 'Thesis',
            'web_article' => 'Electronic Source',
            'web_site' => 'Online Multimedia',
            'web_encyclopedia' => 'Electronic Source',
            'web_encyclopedia_article' => 'Electronic Source',
            'film' => 'Film',
            'broadcast' => 'Broadcast',
            'music_album' => 'Generic',
            'music_track' => 'Generic',
            'music_score' => 'Generic',
            'artwork' => 'Artwork',
            'software' => 'Computer Program',
            'audiovisual' => 'Audiovisual Material',
            'database' => 'Online Database',
            'government_report' => 'Government Report/Document',
            'report' => 'Report',
            'hearing' => 'Hearing',
            'statute' => 'Statute',
            'legal_ruling' => 'Legal Rule/Regulation',
            'case' => 'Case',
            'bill' => 'Bill',
            'patent' => 'Patent',
            'personal' => 'Personal Communication',
            'unpublished' => 'Unpublished Work',
            'classical' => 'Classical Work',
            'manuscript' => 'Manuscript',
            'map' => 'Map',
            'chart' => 'Chart or Table',
            'miscellaneous' => 'Generic',
            'miscellaneous_section' => 'Generic',
            'conference_poster' => 'Generic',
            'brochure' => 'Generic',
        ];
        // Endnote versions earlier than 8.0 have a _very_ different XML format and type naming to version 8.0 *&^$*&^!
        // XML import mappings for Endnote v8.0 and above?
        $this->importTypes8 = array_unique($this->types);
        unset($this->importTypes8[array_search('Generic', $this->importTypes8)]);
        $this->importTypes8['miscellaneous'] = 'Generic';
        $this->importTypes8['miscellaneous_section'] = 'Generic';
        // v8 Endnote reference type numbers (used when exporting XML)
        $this->exportTypes8 = [
            'book' => 6,
            'book_article' => 5,
            'book_chapter' => 5,
            'journal_article' => 8, // also 11, 15, 16, 17, 18, 22, 24, 29, 30, 33
            'newspaper_article' => 23,
            'magazine_article' => 19,
            'proceedings_article' => 10,
            'thesis' => 32,
            'web_article' => 12,
            'web_site' => 48,
            'web_encyclopedia' => 12,
            'web_encyclopedia_article' => 12,
            'film' => 21,
            'broadcast' => 21,
            'artwork' => 2,
            'software' => 9,
            'audiovisual' => 3,
            'report' => 27,
            'hearing' => 14,
            'case' => 7,
            'bill' => 4,
            'statute' => 31,
            'patent' => 25,
            'personal' => 26,
            'manuscript' => 36,
            'map' => 20,
            'chart' => 38,
            'unpublished' => 34,
            'database' => 45,
            'government_report' => 46,
            'conference_paper' => 47,
            'classical' => 49,
            'legal_ruling' => 50,
            'miscellaneous' => 13, // Generic
            'miscellaneous_section' => 13, // Generic
            'conference_poster' => 13, // Generic
            'brochure' => 13, // Generic
        ];
        // Pre-Endnote v8 resource types
        $this->importTypesPre8 = [
            'book' => 1,
            'book_article' => 7,
            'book_chapter' => 7,
            'journal_article' => 0,
            'newspaper_article' => 5,
            'magazine_article' => 8,
            'proceedings_article' => 3,
            'thesis' => 2,
            'web_article' => 16,
            'web_site' => 16,
            'web_encyclopedia' => 16,
            'web_encyclopedia_article' => 16,
            'film' => 21,
            'artwork' => 13,
            'software' => 6,
            'audiovisual' => 12,
            'report' => 10,
            'hearing' => 19,
            'case' => 18,
            'bill' => 17,
            'statute' => 22,
            'patent' => 15,
            'personal' => 4,
            'manuscript' => 20,
            'map' => 11,
            'chart' => 26,
            'miscellaneous' => 31,
            'miscellaneous_section' => 31,
            'conference_poster' => 31, // Generic
            'brochure' => 31, // Generic
        ];
        // Export uses the '*Generic' type so we must list all the possible Endnote generic resourceFields that WIKINDX can use.
        $this->generic = [
            'Author',
            'Year',
            'Title',
            'Secondary Author',
            'Secondary Title',
            'Short Title',
            'Place Published',
            'Publisher',
            'Volume',
            'Number of Volumes',
            'Number',
            'Pages',
            'Section',
            'Tertiary Author',
            'Tertiary Title',
            'Edition',
            'Date',
            'Type of Work',
            'Subsidiary Author',
            'ISBN/ISSN',
            'Original Publication',
            'Keywords',
            'Abstract',
            'Notes',
            'URL',
            'Electronic Resource Number',
        ];
        // Map Endnote XML resourceField names to Endnote tabbed file equivalents (why are they different? -- &@^#*&@!).  Keys
        // are XML resourceFields, values are tabbed resourceFields and should match values in the specific WIKINDX type arrays below.
        // Some resourceFields are dealt with on the fly in the code so are not listed here.
        $this->endnoteXmlFields8 = [
            'secondary-title' => 'Secondary Title', // Collection or Series Title
            'tertiary-title' => 'Tertiary Title',
            'short-title' => 'Short Title',
            'volume' => 'Volume',
            'number' => 'Number',
            'url' => 'URL',
            'num-vols' => 'Number of Volumes',
            'publisher' => 'Publisher',
            'pub-location' => 'Place Published',
            'isbn' => 'ISBN/ISSN',
            'orig-pub' => 'Original Publication',
            'edition' => 'Edition',
            'authors' => 'Author',
            'secondary-authors' => 'Secondary Author',
            'tertiary-authors' => 'Tertiary Author',
            'subsidiary-authors' => 'Subsidiary Author',
            'year' => 'Year',
            'abstract' => 'Abstract',
            'notes' => 'Notes',
            'section' => 'Section',
            'work-type' => 'Type of Work',
            'pages' => 'Pages',
            'electronic-resource-num' => 'Electronic Resource Number',
        ];
        $this->endnoteXmlFieldsPre8 = [
            'SECONDARY_TITLE' => 'Secondary Title', // Collection or Series Title
            'TERTIARY_TITLE' => 'Tertiary Title',
            'SHORT_TITLE' => 'Short Title',
            'VOLUME' => 'Volume',
            'NUMBER' => 'Number',
            'URL' => 'URL',
            'NUMBER_OF_VOLUMES' => 'Number of Volumes',
            'PUBLISHER' => 'Publisher',
            'PLACE_PUBLISHED' => 'Place Published',
            'ISBN' => 'ISBN/ISSN',
            'ORIGINAL_PUB' => 'Original Publication',
            'EDITION' => 'Edition',
            'AUTHOR' => 'Author',
            'SECONDARY_AUTHOR' => 'Secondary Author',
            'TERTIARY_AUTHOR' => 'Tertiary Author',
            'SUBSIDIARY_AUTHOR' => 'Subsidiary Author',
            'YEAR' => 'Year',
            'ABSTRACT' => 'Abstract',
            'NOTES' => 'Notes',
            'SECTION' => 'Section',
            'TYPE_OF_WORK' => 'Type of Work',
            'PAGES' => 'Pages',
        ];
        // For exporting, these are resourceFields that WIKINDX does not store but that the user may request to be exported.
        // The mapping is Endnote XML export resourceFields => Endnote tabbed file import resourceFields.
        // Commented out items are just not accepted by Endnote when importing a tabbed file but _should_ be the correct resourceField names ... buggy commercial software! ;)
        $this->exportRawFields = [
            "accession-num" => "Accession Number",
            "custom1" => "Custom 1",
            "custom2" => "Custom 2",
            "custom3" => "Custom 3",
            "custom4" => "Custom 4",
            "custom5" => "Custom 5",
            "custom6" => "Custom 6",
            "custom7" => "Custom 7",
            "auth-address" => "Author Address",
            "reprint-edition" => "Reprint Edition",
            "call-num" => "Call Number",
            "label" => "Label",
            "caption" => "Caption",
            "reviewed-item" => "Reviewed Item",
            "electronic-resource-num" => "Electronic Resource Number",
            //				"remote-database-name"	=>	"Name of Database",
            //				"remote-database-provider"	=>	"Database Provider",
            //				"research-notes"	=>		"Research Notes",
            //				"language"			=>		"Language",
            //				"modified-date"		=>		"Last Modified Date",
            "alt-title" => "Alternate Title",
            //				"translated-title"	=>		"Translated Title"
        ];
        $this->exportRawFieldsXml = [
            "accession-num" => "Accession Number",
            "custom1" => "Custom 1",
            "custom2" => "Custom 2",
            "custom3" => "Custom 3",
            "custom4" => "Custom 4",
            "custom5" => "Custom 5",
            "custom6" => "Custom 6",
            "custom7" => "Custom 7",
            "auth-address" => "Author Address",
            "reprint-edition" => "Reprint Edition",
            "call-num" => "Call Number",
            "label" => "Label",
            "caption" => "Caption",
            "reviewed-item" => "Reviewed Item",
            "electronic-resource-num" => "Electronic Resource Number",
            "remote-database-name" => "Name of Database",
            "remote-database-provider" => "Database Provider",
            "research-notes" => "Research Notes",
            "language" => "Language",
            "modified-date" => "Last Modified Date",
            "alt-title" => "Alternate Title",
            "translated-title" => "Translated Title",
        ];
        // Forbid mapping of unknown import fields to these fields
        $this->noMap = ['title', 'editor', 'author'];
        // Book resource type
        $this->book = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Secondary Title', // Series Title
                'resourceField2' => 'Edition',
                'resourceField4' => 'Volume',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [
                'resourcemiscField4' => 'Number of Volumes',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
                'resourceyearYear2' => 'Original Publication',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Secondary Author', // Editor
                3 => 'Subsidiary Author', // Translator
                5 => 'Tertiary Author', // Series Editor
            ],
        ];
        $this->book['resource_publisher']['publisherName'] = 'Publisher';
        $this->book['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->book['resource_collection'] = []; // need empty array
        // book article resource type
        $this->book_article = $this->book;
        $this->book_article['resource']['resourceField1'] = 'Tertiary Title'; // overwrite
        $this->book_article['resource_collection']['collectionTitle'] = 'Secondary Title';
        $this->book_article['resource_collection']['collectionTitleShort'] = 'Short Title';
        $this->book_article['resource_page'] = [];
        // book chapter resource type
        $this->book_chapter = $this->book;
        $this->book_chapter['resource']['resourceField1'] = 'Tertiary Title'; // overwrite
        $this->book_chapter['resource_collection']['collectionTitle'] = 'Secondary Title';
        // Journal article resource type
        $this->journal_article = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Volume',
                'resourceField2' => 'Number',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [ // we require an empty array to still be present
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->journal_article['resource_publisher'] = []; // need empty array
        $this->journal_article['resource_collection']['collectionTitle'] = 'Secondary Title';
        $this->journal_article['resource_collection']['collectionTitleShort'] = 'Short Title';
        $this->journal_article['resource_page'] = [];
        // Newspaper article type
        $this->newspaper_article = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Section',
                'resourceField2' => 'Place Published',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [
                'resourcemiscField2' => 'Day',
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->newspaper_article['resource_publisher'] = []; // need empty array
        $this->newspaper_article['resource_collection']['collectionTitle'] = 'Secondary Title';
        $this->newspaper_article['resource_collection']['collectionTitleShort'] = 'Short Title';
        $this->newspaper_article['resource_page'] = [];
        // proceedings article
        $this->proceedings_article = [
            'resource' => [
                'resourceField1' => 'Tertiary Title', // Series Title
                'resourceField3' => 'Volume',
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->proceedings_article['resource_publisher']['publisherName'] = 'Publisher';
        $this->proceedings_article['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->proceedings_article['resource_collection']['collectionTitle'] = 'Secondary Title';
        $this->proceedings_article['resource_collection']['collectionTitleShort'] = 'Short Title';
        $this->proceedings_article['resource_page'] = [];
        // Web article resource type
        $this->web_article = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [ // we require an empty array to still be present
                'resourcemiscField5' => 'Day',
                'resourcemiscField6' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Secondary Author', // Editor
            ],
        ];
        $this->web_article['resource_publisher']['publisherName'] = 'Publisher';
        $this->web_article['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->web_article['resource_collection'] = []; // need empty array
        $this->web_article['resource_page'] = [];
        // Web site resource type
        $this->web_site = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Secondary Author', // Editor
            ],
        ];
        $this->web_site['resource_publisher'] = []; // need empty array
        $this->web_site['resource_collection'] = []; // need empty array
// Web encyclopedia resource type
        $this->web_encyclopedia = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Edition',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Secondary Author', // Editor
            ],
        ];
        $this->web_encyclopedia['resource_publisher']['publisherName'] = 'Publisher';
        $this->web_encyclopedia['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->web_encyclopedia['resource_collection'] = []; // need empty array
        // Web encyclopedia article resource type
        $this->web_encyclopedia_article = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Edition',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Secondary Author', // Editor
            ],
        ];
        $this->web_encyclopedia_article['resource_publisher']['publisherName'] = 'Publisher';
        $this->web_encyclopedia_article['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->web_encyclopedia_article['resource_collection']['collectionTitle'] = 'Secondary Title';
        $this->web_encyclopedia_article['resource_collection']['collectionTitleShort'] = 'Short Title';
        $this->web_encyclopedia_article['resource_page'] = [];
        // Thesis resource type
        $this->thesis = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Volume', // thesis or dissertation?
                'resourceField2' => 'Type of Work',
                'resourceField5' => 'Secondary Title', // Department
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->thesis['resource_publisher']['publisherName'] = 'Publisher';
        $this->thesis['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->thesis['resource_collection'] = []; // need empty array
        $this->thesis['resource_page'] = [];
        // Film resource type
        $this->film = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Place Published',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Subsidiary Author',
            ],
        ];
        $this->film['resource_publisher']['publisherName'] = 'Publisher';
        $this->film['resource_collection'] = []; // need empty array
        // Broadcast resource type
        $this->broadcast = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [  // empty array required
                'resourcemiscField2' => 'Day',
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Subsidiary Author',
            ],
        ];
        $this->broadcast['resource_publisher']['publisherName'] = 'Publisher';
        $this->broadcast['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->broadcast['resource_collection'] = []; // need empty array
        // Software resource type
        $this->software = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Type of Work',
                'resourceField4' => 'Edition', // Version
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author', // Programmer
            ],
        ];
        $this->software['resource_publisher']['publisherName'] = 'Publisher';
        $this->software['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->software['resource_collection'] = []; // need empty array
        // Artwork resource type
        $this->artwork = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Type of Work',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->artwork['resource_publisher']['publisherName'] = 'Publisher';
        $this->artwork['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->artwork['resource_collection'] = []; // need empty array
        // Audiovisual resource type
        $this->audiovisual = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Secondary Title',
                'resourceField2' => 'Type of Work',
                'resourceField3' => 'Edition',
                'resourceField5' => 'Number',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [
                'resourcemiscField4' => 'Number of Volumes',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Subsidiary Author', // Performers
                5 => 'Secondary Author', // Series Editor
            ],
        ];
        $this->audiovisual['resource_publisher']['publisherName'] = 'Publisher';
        $this->audiovisual['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->audiovisual['resource_collection'] = []; // need empty array
        // case resource type
        $this->case = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Secondary Title', // Reporter
                'resourceField4' => 'Volume', // Reporter Volume
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [ // we require an empty array to still be present
                'resourcemiscField2' => 'Day',
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                3 => 'Subsidiary Author', // Counsel
            ],
        ];
        $this->case['resource_publisher']['publisherName'] = 'Publisher';
        $this->case['resource_collection'] = []; // need empty array
        $this->case['resource_page'] = [];
        // bill resource type
        $this->bill = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Section', // Code Section
                'resourceField2' => 'Secondary Title', // Code
                'resourceField3' => 'Volume', // Code Volume
                'resourceField4' => 'Edition', // Session
                'resourceField5' => 'Number', // Bill Number
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [// we require an empty array to still be present
            ],
        ];
        $this->bill['resource_publisher'] = []; // need empty array
        $this->bill['resource_collection'] = []; // need empty array
        $this->bill['resource_page'] = [];
        // classical resource type
        $this->classical = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField4' => 'Volume',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author', // Attribution
            ],
        ];
        $this->classical['resource_publisher'] = []; // need empty array
        $this->classical['resource_collection'] = []; // need empty array
// Conference Paper resource type
        $this->conference_paper = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->conference_paper['resource_publisher'] = []; // need empty array
        $this->conference_paper['resource_collection']['collectionTitle'] = 'Secondary Title';
        $this->conference_paper['resource_collection']['collectionTitleShort'] = 'Short Title';
        // Conference Poster resource type
        $this->conference_poster = [
            'resource' => [
                'resourceIsbn' => 'ISBN/ISSN',
                'resourcetextUrls' => 'URL',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->conference_poster['resource_publisher'] = []; // need empty array
        $this->conference_poster['resource_collection']['collectionTitle'] = 'Secondary Title';
        $this->conference_poster['resource_collection']['collectionTitleShort'] = 'Short Title';
        // Legal Rule/Regulation resource type
        $this->legal_ruling = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Section', // Section Number
                'resourceField2' => 'Type of Work',
                'resourceField3' => 'Edition',
                'resourceField4' => 'Volume', // Rule Number
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [ // we require an empty array to still be present
                'resourcemiscField2' => 'Day',
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->legal_ruling['resource_publisher']['publisherName'] = 'Publisher';
        $this->legal_ruling['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->legal_ruling['resource_collection'] = []; // need empty array
        $this->legal_ruling['resource_page'] = [];
        // Government Report/Documentation resource type
        $this->government_report = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Section',
                'resourceField2' => 'Secondary Author', // Department
                'resourceField3' => 'Edition',
                'resourceField4' => 'Volume',
                'resourceField5' => 'Number', // Issue number
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Secondary Author', // Editor
            ],
        ];
        $this->government_report['resource_publisher']['publisherName'] = 'Publisher';
        $this->government_report['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->government_report['resource_collection'] = []; // need empty array
        $this->government_report['resource_page'] = [];
        // Report/Documentation resource type
        $this->report = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceField1' => 'Secondary Title', // Series Title
                'resourceField2' => 'Type of Work',
                'resourceField5' => 'ISBN/ISSN', // Report number
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [  // empty array required
                'resourcemiscField2' => 'Day',
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Secondary Author', // Editor
            ],
        ];
        $this->report['resource_publisher']['publisherName'] = 'Publisher';
        $this->report['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->report['resource_collection'] = []; // need empty array
        $this->report['resource_page'] = [];
        // Hearing resource type
        $this->hearing = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Secondary Title', // Committee
                'resourceField2' => 'Tertiary Title', // Legislative Body
                'resourceField3' => 'Edition', // Session
                'resourceField4' => 'Number', // Document Number
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [
                'resourcemiscField4' => 'Number of Volumes',
                'resourcemiscField2' => 'Day',
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [// empty array required
            ],
        ];
        $this->hearing['resource_publisher']['publisherName'] = 'Publisher';
        $this->hearing['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->hearing['resource_collection'] = []; // need empty array
        $this->hearing['resource_page'] = [];
        // Online database resource type
        $this->database = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->database['resource_publisher']['publisherName'] = 'Publisher';
        $this->database['resource_collection'] = []; // need empty array
        // Magazine article type
        $this->magazine_article = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Type of Work',
                'resourceField4' => 'Volume',
                'resourceField3' => 'Number',
                'resourceField1' => 'Edition',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [  // empty array required
                'resourcemiscField2' => 'Day',
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->magazine_article['resource_publisher'] = []; // need empty array
        $this->magazine_article['resource_collection']['collectionTitle'] = 'Secondary Title';
        $this->magazine_article['resource_collection']['collectionTitleShort'] = 'Short Title';
        $this->magazine_article['resource_page'] = [];
        // Manuscript type
        $this->manuscript = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Type of Work',
                'resourceField3' => 'Number',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [  // empty array required
                'resourcemiscField2' => 'Day',
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->manuscript['resource_publisher'] = []; // need empty array
        $this->manuscript['resource_collection']['collectionTitle'] = 'Secondary Title';
        $this->manuscript['resource_collection']['collectionTitleShort'] = 'Short Title';
        $this->manuscript['resource_page'] = [];
        // Map type
        $this->map = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Secondary Title', // Series Title
                'resourceField2' => 'Type of Work',
                'resourceField3' => 'Edition',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author', // Cartographer
                5 => 'Secondary Author', // Series Editor
            ],
        ];
        $this->map['resource_publisher']['publisherName'] = 'Publisher';
        $this->map['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->map['resource_collection'] = []; // need empty array
        // Chart type
        $this->chart = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Secondary Author', // Name of File
                'resourceField2' => 'Secondary Title', // Image Source Program
                'resourceField3' => 'Volume', // Image Size
                'resourceField4' => 'Type of Work',
                'resourceField5' => 'Edition', // Version
                'resourceField6' => 'Number',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author', // Created By
            ],
        ];
        $this->chart['resource_publisher']['publisherName'] = 'Publisher';
        $this->chart['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->chart['resource_collection'] = []; // need empty array
        // Statute type
        $this->statute = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField1' => 'Number', // Public Law Number
                'resourceField2' => 'Secondary Title', // Code
                'resourceField3' => 'Edition', // Session
                'resourceField4' => 'Section',
                'resourceField5' => 'Volume', // Code Number
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [  // empty array required
                'resourcemiscField2' => 'Day',
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [// empty array required
            ],
        ];
        $this->statute['resource_publisher'] = []; // need empty array
        $this->statute['resource_collection'] = []; // need empty array
        $this->statute['resource_page'] = [];
        // Patent type
        $this->patent = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceField1' => 'Secondary Title', // Published Source
                'resourceField2' => 'Volume', // Patent Version Number
                'resourceField3' => 'Number', // Application Number
                'resourceField4' => 'Type of Work', // Patent Type
                'resourceField5' => 'Section', // International Patent Number
                'resourceField6' => 'Tertiary Author', // International Title (careful!)
                'resourceField7' => 'Edition', // International Patent Classification
                'resourceField8' => 'ISBN/ISSN', // Patent Number
                'resourceField9' => 'Custom 6', // Legal Status
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [  // empty array required
                'resourcemiscField2' => 'Day',
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author', // Inventor
                2 => 'Secondary Author', // Issuing Organisation
                3 => 'Custom 4', // Attorney/Agent
                4 => 'Tertiary Title', // International Author (careful!)
            ],
        ];
        $this->patent['resource_publisher']['publisherName'] = 'Publisher';
        $this->patent['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->patent['resource_collection'] = []; // need empty array
        // Personal Communication type
        $this->personal = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Type of Work',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [  // empty array required
                'resourcemiscField2' => 'Day',
                'resourcemiscField3' => 'Month',
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Secondary Author', // Recipient
            ],
        ];
        $this->personal['resource_publisher'] = []; // need empty array
        $this->personal['resource_collection'] = []; // need empty array
// Unpublished type
        $this->unpublished = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Type of Work',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->unpublished['resource_publisher']['publisherName'] = 'Publisher'; // Institution
        $this->unpublished['resource_publisher']['publisherLocation'] = 'Place Published'; // Institution Location
        $this->unpublished['resource_collection'] = []; // need empty array
        $this->unpublished['resource_page'] = [];
        // proceedings (complete set)
        $this->proceedings = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                2 => 'Secondary Author', // Editor
            ],
        ];
        $this->proceedings['resource_publisher']['publisherName'] = 'Publisher';
        $this->proceedings['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->proceedings['resource_collection'] = []; // need empty array
        // Music Album type
        $this->music_album = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Type of Work',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author', // Performer
                2 => 'Subsidiary Author', // Composer
                3 => 'Tertiary Author', // Conductor
            ],
        ];
        $this->music_album['resource_publisher']['publisherName'] = 'Publisher';
        $this->music_album['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->music_album['resource_collection'] = []; // need empty array
        // Music Track type
        $this->music_track = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Type of Work',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
                2 => 'Subsidiary Author',
                3 => 'Tertiary Author',
            ],
        ];
        $this->music_track['resource_publisher']['publisherName'] = 'Publisher';
        $this->music_track['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->music_track['resource_collection']['collectionTitle'] = 'Secondary Title';
        $this->music_track['resource_collection']['collectionTitleShort'] = 'Short Title';
        // Music Score type
        $this->music_score = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField3' => 'Edition',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// empty array required
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author', // Composer
                2 => 'Secondary Author', // Editor
            ],
        ];
        $this->music_score['resource_publisher']['publisherName'] = 'Publisher';
        $this->music_score['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->music_score['resource_collection'] = []; // need empty array
        // Miscellaneous resource type
        $this->miscellaneous = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Type of Work',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->miscellaneous['resource_publisher']['publisherName'] = 'Publisher';
        $this->miscellaneous['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->miscellaneous['resource_collection'] = []; // need empty array
        // Miscellaneous Section resource type
        $this->miscellaneous_section = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
                'resourceField2' => 'Type of Work',
                'resourceDoi' => 'Electronic Resource Number',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->miscellaneous_section['resource_publisher']['publisherName'] = 'Publisher';
        $this->miscellaneous_section['resource_publisher']['publisherLocation'] = 'Place Published';
        $this->miscellaneous_section['resource_collection']['collectionTitle'] = 'Secondary Title';
        $this->miscellaneous_section['resource_collection']['collectionTitleShort'] = 'Short Title';
        // Brochure resource type
        $this->brochure = [
            'resource' => [
                'resourcetextUrls' => 'URL',
                'resourceIsbn' => 'ISBN/ISSN',
            ],
            'resource_misc' => [// we require an empty array to still be present
            ],
            'resource_year' => [
                'resourceyearYear1' => 'Year',
            ],
            'resource_creator' => [
                1 => 'Author',
            ],
        ];
        $this->brochure['resource_publisher'] = []; // need empty array
        $this->brochure['resource_collection'] = []; // need empty array
// Possible wikindx keys used for mapping custom Endnote resourceFields to wikindx resourceFields in an Endnote import.
        foreach (array_keys($this->types) as $type)
        {
            $this->{$type}['possible'] = array_values($this->{$type}['resource']);
            $this->{$type}['possible'] = array_merge(
                $this->{$type}['possible'],
                array_values($this->{$type}['resource_misc'])
            );
            $this->{$type}['possible'] = array_merge(
                $this->{$type}['possible'],
                array_values($this->{$type}['resource_year'])
            );
            $this->{$type}['possible'] = array_merge(
                $this->{$type}['possible'],
                array_values($this->{$type}['resource_creator'])
            );
            $this->{$type}['possible'] = array_merge(
                $this->{$type}['possible'],
                array_values($this->{$type}['resource_publisher'])
            );
            $this->{$type}['possible'] = array_merge(
                $this->{$type}['possible'],
                array_values($this->{$type}['resource_collection'])
            );
        }
    }
}
