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
 * Define style mapping
 *
 * @package wikindx\core\bibcitation
 */
class STYLEMAP
{
    /**
     * STYLEMAP
     */
    public function __construct()
    {
        $this->loadMap();
    }
    /**
     * loadMap: Load the map into arrays based on resource type.
     *
     * The basic() array contains database fields that are common to all types of resources.
     * The key is the database field and the value is displayed to the user to be part of the style definition.
     * e.g. if the user enters:
     * author. |title. |publisherName|: publisherLocation|.
     * for a style definition for a book, we know that 'author' is the database field 'creator1', 'title' is
     * the database field 'title' etc.
     * There are some exceptions as defined by WIKINDX (other systems may have different methods).  Because these may be
     * represented in different ways in different systems, you will need to explicitly define these.  See BIBSTYLE.php
     * for examples of how WIKINDX does this.  The comments below relate to how WIKINDX stores such values in its database:
     * 1/ 'originalPublicationYear doesn't exist in the database but is used to re-order publicationYear and reprintYear
     * for book and book_article resource types.
     * 2/ 'pages' doesn't exist in the database but is created on the fly in BIBSTYLE.php as an amalgamation of
     * the database fields pageStart and pageEnd.
     * 3/ 'date' doesn't exist in the database but is created on the fly in BIBSTYLE.php as an amalgamation of
     * the database fields miscField2 (day) and miscField3 (month).
     * 4/ 'runningTime' doesn't exist in the database but is created on the fly in BIBSTYLE.php as an amalgamation of
     * the database fields miscField1 (minute) and miscField4 (hour) for film/broadcast.
     * 5/ 'transTitle' doesn't exist in the database but is created on the fly in BIBSTYLE.php as an amalgamation of
     * the database fields transTitle, transSubtitle and transNoSort.
     */
    private function loadMap()
    {
        /**
         * What fields are available to the in-text citation template? This array should NOT be changed.
         */
        $this->citation = [
            "creator" => "creator",
            "title" => "title",
            "year" => "year",
            "pages" => "pages",
            "shortTitle" => "shortTitle",
            "URL" => "URL",
        ];
        /**
         * What fields are available to the in-text citation template for endnote-style citations? This array should NOT be changed.
         */
        $this->citationEndnoteInText = [
            "id" => "id",
            "pages" => "pages",
        ];
        /**
         * What fields are available to the endnote citation template for endnote-style citations? This array should NOT be changed.
         */
        $this->citationEndnote = [
            "citation" => "citation",
            "creator" => "creator",
            "title" => "title",
            "year" => "year",
            "pages" => "pages",
        ];
        /**
         * NB NB NB NB NB NB NB NB NB NB NB
         *
         * Map between OSBib's resource types (keys) and the bibliographic system's resource types (values). You must
         * NOT remove any elements NOR change the generic types. You may edit the value of each element. If your system
         * does not have a particular resource type, then you should set the value to FALSE (e.g. 'film' => FALSE,)
         */
        $this->types = [
            // The generic types must be present and unchanged.  DO NOT CHANGE THE VALUE OF THESE THREE!
            'genericBook' => 'genericBook',
            'genericArticle' => 'genericArticle',
            'genericMisc' => 'genericMisc',
            // Edit values if necessary
            'book' => 'book',
            'book_article' => 'book_article',
            'book_chapter' => 'book_chapter',
            'journal_article' => 'journal_article',
            'newspaper_article' => 'newspaper_article',
            'magazine_article' => 'magazine_article',
            'proceedings' => 'proceedings',
            'conference_paper' => 'conference_paper',
            'conference_poster' => 'conference_poster',
            'proceedings_article' => 'proceedings_article',
            'thesis' => 'thesis',
            'web_site' => 'web_site',
            'web_article' => 'web_article',
            'web_encyclopedia' => 'web_encyclopedia',
            'web_encyclopedia_article' => 'web_encyclopedia_article',
            'film' => 'film',
            'broadcast' => 'broadcast',
            'music_album' => 'music_album',
            'music_track' => 'music_track',
            'music_score' => 'music_score',
            'artwork' => 'artwork',
            'software' => 'software',
            'audiovisual' => 'audiovisual',
            'database' => 'database',
            'government_report' => 'government_report',
            'report' => 'report',
            'hearing' => 'hearing',
            'statute' => 'statute',
            'brochure' => 'brochure',
            'legal_ruling' => 'legal_ruling',
            'case' => 'case',
            'bill' => 'bill',
            'patent' => 'patent',
            'personal' => 'personal',
            'unpublished' => 'unpublished',
            'classical' => 'classical',
            'manuscript' => 'manuscript',
            'map' => 'map',
            'chart' => 'chart',
            'miscellaneous' => 'miscellaneous',
            'miscellaneous_section' => 'miscellaneous_section',
        ];
        /**
         * Basic array of elements common to all types - change the key to map the database field that stores that value.
         */
        $this->basic = [
            'resourceTitle' => 'title',
            'resourceyearYear1' => 'publicationYear',
            'URL' => 'URL',
            'DOI' => 'DOI',
        ];
        /**
         * Creator mapping.  OSBib uses 'creator1' .. 'creator5' for internally managing creator names such as
         * author, editor, series editor, translator, reviser, artist, inventor, composer etc.  The associative
         * array (SQL row) you submit to $this->bibformat->preProcess() MUST use these fields for the creators.
         * Furthermore, you may NOT change any keys (or values) in the arrays below that are 'creator1' ... 'creator5'.
         */

        /**
         * NB NB NB NB NB NB NB NB NB NB NB
         *
         * For the following arrays, the only things you should change are the keys of each array (except 'creator1'
         * .. 'creator5' - see above).  These keys are your database fieldnames for resources.
         * The values are displayed to the user when creating/editing a style and
         * must NOT change or be removed.  If your database does not store a particular value, then it should still
         * exist in the array and must have a null key (e.g. $this->book[] = 'publisherName'; in the case of a database
         * that does not store publisher names for books ;-)).
         *
         **************
         **************
         * Do NOT remove arrays.
         * Do not remove array elements.
         * Do not add array elements.
         **************
         **************
         *
         * You do not need to edit arrays where the value in $this->types above is FALSE as the array will then simply be
         * ignored.  So, although 36 resource types are defined here, if you system only has 6 resource types, you only need
         * to edit those 6 types.
         *
         * If you do not conform to this, XML style definition sheets you produce will not be compatible with other systems.
         */
        // Three Generic fallback types used when there's no style definition for one of the resources below.
        // Generic Book type - no collection data, like a book
        $this->genericBook = $this->basic;
        $this->genericBook['creator1'] = 'creator';
        $this->genericBook['creator2'] = 'editor';
        $this->genericBook['publisherName'] = 'publisherName';
        $this->genericBook['publisherLocation'] = 'publisherLocation';
        $this->genericBook['resourceIsbn'] = 'ID';
        $this->genericBook['resourceShortTitle'] = 'shortTitle';
        // Generic Article type - in a collection like an article
        $this->genericArticle = $this->basic;
        $this->genericArticle['creator1'] = 'creator';
        $this->genericArticle['creator2'] = 'editor';
        $this->genericArticle['collectionTitle'] = 'collection';
        $this->genericArticle['publisherName'] = 'publisherName';
        $this->genericArticle['publisherLocation'] = 'publisherLocation';
        $this->genericArticle['date'] = 'date';
        $this->genericArticle['pages'] = 'pages';
        $this->genericArticle['resourceIsbn'] = 'ID';
        $this->genericArticle['resourceShortTitle'] = 'shortTitle';
        // Generic Miscellaneous type - whatever is best not put in the above two fall back types....?
        $this->genericMisc = $this->basic;
        $this->genericMisc['creator1'] = 'creator';
        $this->genericMisc['publisherName'] = 'publisherName';
        $this->genericMisc['publisherLocation'] = 'publisherLocation';
        $this->genericMisc['resourceField2'] = 'type';
        $this->genericMisc['date'] = 'date';
        $this->genericMisc['resourceIsbn'] = 'ID';
        $this->genericMisc['resourceShortTitle'] = 'shortTitle';

        // Resource specific mappings. The order here is the display order when editing/creating styles.
        // BOOK
        $this->book = $this->basic;
        $this->book['creator1'] = 'author';
        $this->book['creator2'] = 'editor';
        $this->book['creator3'] = 'translator';
        $this->book['creator4'] = 'reviser';
        $this->book['creator5'] = 'seriesEditor';
        $this->book['resourceField1'] = 'seriesTitle';
        $this->book['resourceField2'] = 'edition';
        $this->book['resourceField3'] = 'seriesNumber';
        $this->book['resourcemiscField4'] = 'numberOfVolumes';
        $this->book['resourceField4'] = 'volumeNumber';
        $this->book['resourcemiscField6'] = 'numPages';
        $this->book['resourceyearYear2'] = 'originalPublicationYear';
        $this->book['resourceyearYear3'] = 'volumePublicationYear';
        $this->book['resourceyearYear4'] = 'transPublicationYear';
        $this->book['publisherName'] = 'publisherName';
        $this->book['publisherLocation'] = 'publisherLocation';
        $this->book['transPublisherName'] = 'transPublisherName';
        $this->book['transPublisherLocation'] = 'transPublisherLocation';
        $this->book['resourceIsbn'] = 'resourceIsbn';
        $this->book['resourceShortTitle'] = 'shortTitle';
        $this->book['transTitle'] = 'transTitle';
        $this->book['transNoSort'] = 'transNoSort';
        // BOOK ARTICLE
        $this->book_article = $this->basic;
        $this->book_article['creator1'] = 'author';
        $this->book_article['creator2'] = 'editor';
        $this->book_article['creator3'] = 'translator';
        $this->book_article['creator4'] = 'reviser';
        $this->book_article['creator5'] = 'seriesEditor';
        $this->book_article['resourceField1'] = 'seriesTitle';
        $this->book_article['resourceField2'] = 'edition';
        $this->book_article['resourceField3'] = 'seriesNumber';
        $this->book_article['resourcemiscField4'] = 'numberOfVolumes';
        $this->book_article['resourceField4'] = 'volumeNumber';
        $this->book_article['resourceField6'] = 'numPages';
        $this->book_article['resourceyearYear2'] = 'originalPublicationYear';
        $this->book_article['resourceyearYear3'] = 'volumePublicationYear';
        $this->book_article['resourceyearYear4'] = 'transPublicationYear';
        $this->book_article['publisherName'] = 'publisherName';
        $this->book_article['publisherLocation'] = 'publisherLocation';
        $this->book_article['transPublisherName'] = 'transPublisherName';
        $this->book_article['transPublisherLocation'] = 'transPublisherLocation';
        $this->book_article['collectionTitle'] = 'book';
        $this->book_article['collectionTitleShort'] = 'shortBook';
        $this->book_article['pages'] = 'pages';
        $this->book_article['resourceIsbn'] = 'resourceIsbn';
        $this->book_article['resourceShortTitle'] = 'shortTitle';
        $this->book_article['transTitle'] = 'transTitle';
        // BOOK CHAPTER
        $this->book_chapter = $this->basic;
        $this->book_chapter['creator1'] = 'author';
        $this->book_chapter['creator2'] = 'editor';
        $this->book_chapter['creator3'] = 'translator';
        $this->book_chapter['creator4'] = 'reviser';
        $this->book_chapter['creator5'] = 'seriesEditor';
        $this->book_chapter['resourceField1'] = 'seriesTitle';
        $this->book_chapter['resourceField2'] = 'edition';
        $this->book_chapter['resourceField3'] = 'seriesNumber';
        $this->book_chapter['resourcemiscField4'] = 'numberOfVolumes';
        $this->book_chapter['resourceField4'] = 'volumeNumber';
        $this->book_chapter['resourceField6'] = 'numPages';
        $this->book_chapter['resourceyearYear2'] = 'originalPublicationYear';
        $this->book_chapter['resourceyearYear3'] = 'volumePublicationYear';
        $this->book_chapter['resourceyearYear4'] = 'transPublicationYear';
        $this->book_chapter['publisherName'] = 'publisherName';
        $this->book_chapter['publisherLocation'] = 'publisherLocation';
        $this->book_chapter['transPublisherName'] = 'transPublisherName';
        $this->book_chapter['transPublisherLocation'] = 'transPublisherLocation';
        $this->book_chapter['collectionTitle'] = 'book';
        $this->book_chapter['collectionTitleShort'] = 'shortBook';
        $this->book_chapter['pages'] = 'pages';
        $this->book_chapter['resourceIsbn'] = 'resourceIsbn';
        // JOURNAL ARTICLE
        $this->journal_article = $this->basic;
        $this->journal_article['creator1'] = 'author';
        $this->journal_article['creator2'] = 'translator';
        $this->journal_article['resourceField1'] = 'volume';
        $this->journal_article['resourceField2'] = 'issue';
        $this->journal_article['publisherName'] = 'publisherName';
        $this->journal_article['publisherLocation'] = 'publisherLocation';
        $this->journal_article['date'] = 'issueDate';
        $this->journal_article['collectionTitle'] = 'journal';
        $this->journal_article['collectionTitleShort'] = 'shortJournal';
        $this->journal_article['pages'] = 'pages';
        $this->journal_article['resourceIsbn'] = 'ISSN';
        $this->journal_article['resourceShortTitle'] = 'shortTitle';
        // NEWSPAPER ARTICLE
        $this->newspaper_article = $this->basic;
        $this->newspaper_article['resourceyearYear1'] = 'issueYear'; // override publicationYear
        $this->newspaper_article['date'] = 'issueDate';
        $this->newspaper_article['creator1'] = 'author';
        $this->newspaper_article['creator2'] = 'translator';
        $this->newspaper_article['collectionTitle'] = 'newspaper';
        $this->newspaper_article['collectionTitleShort'] = 'shortNewspaper';
        $this->newspaper_article['resourceField1'] = 'section';
        $this->newspaper_article['resourceField2'] = 'city';
        $this->newspaper_article['pages'] = 'pages';
        $this->newspaper_article['resourceIsbn'] = 'ISSN';
        $this->newspaper_article['resourceShortTitle'] = 'shortTitle';
        // MAGAZINE ARTICLE
        $this->magazine_article = $this->basic;
        $this->magazine_article['resourceyearYear1'] = 'issueYear'; // override publicationYear
        $this->magazine_article['date'] = 'issueDate';
        $this->magazine_article['creator1'] = 'author';
        $this->magazine_article['collectionTitle'] = 'magazine';
        $this->magazine_article['collectionTitleShort'] = 'shortMagazine';
        $this->magazine_article['resourceField1'] = 'edition';
        $this->magazine_article['resourceField2'] = 'type';
        $this->magazine_article['resourceField4'] = 'volume';
        $this->magazine_article['resourceField3'] = 'number';
        $this->magazine_article['pages'] = 'pages';
        $this->magazine_article['resourceIsbn'] = 'ISSN';
        $this->magazine_article['resourceShortTitle'] = 'shortTitle';
        // PROCEEDINGS ARTICLE
        $this->proceedings_article = $this->basic;
        $this->proceedings_article['resourceField1'] = 'seriesTitle';
        $this->proceedings_article['resourceField3'] = 'seriesNumber';
        $this->proceedings_article['resourceField4'] = 'volumeNumber';
        $this->proceedings_article['creator1'] = 'author';
        $this->proceedings_article['creator2'] = 'editor';
        $this->proceedings_article['collectionTitle'] = 'conference';
        $this->proceedings_article['collectionTitleShort'] = 'shortConference';
        $this->proceedings_article['publisherName'] = 'conferenceOrganiser';
        $this->proceedings_article['publisherLocation'] = 'conferenceLocation';
        $this->proceedings_article['date'] = 'conferenceDate';
        $this->proceedings_article['resourceyearYear2'] = 'conferenceYear';
        $this->proceedings_article['publisher'] = 'publisherName'; // on the fly in BIBSTYLE
        $this->proceedings_article['location'] = 'publisherLocation'; // on the fly in BIBSTYLE
        $this->proceedings_article['pages'] = 'pages';
        $this->proceedings_article['resourceIsbn'] = 'ISSN';
        $this->proceedings_article['resourceShortTitle'] = 'shortTitle';
        // THESIS
        $this->thesis = $this->basic;
        // overwrite publicationYear
        $this->thesis['resourceyearYear1'] = 'awardYear';
        $this->thesis['creator1'] = 'author';
        $this->thesis['creator2'] = 'supervisor';
        $this->thesis['resourceField2'] = 'type'; // 'Masters', 'PhD', 'Doctoral', 'Diploma' etc.
        $this->thesis['resourceField1'] = 'label'; // 'thesis', 'dissertation'
        $this->thesis['publisherName'] = 'institution';
        $this->thesis['publisherLocation'] = 'institutionLocation';
        $this->thesis['resourceField5'] = 'department';
        $this->thesis['collectionTitle'] = 'journal';
        $this->thesis['collectionTitleShort'] = 'shortJournal';
        $this->thesis['resourceField3'] = 'volumeNumber';
        $this->thesis['resourceField4'] = 'issueNumber';
        $this->thesis['resourceyearYear2'] = 'abstractYear';
        $this->thesis['pages'] = 'pages';
        $this->thesis['resourceIsbn'] = 'ID';
        $this->thesis['resourceShortTitle'] = 'shortTitle';
        $this->thesis['resourcemiscField6'] = 'numPages';
        // WEB SITE
        $this->web_site = $this->basic;
        $this->web_site['creator1'] = 'author';
        $this->web_site['creator2'] = 'editor';
        //		$this->web_site['URL'] = 'URL';
        $this->web_site['date'] = 'accessDate';
        $this->web_site['resourceyearYear2'] = 'accessYear';
        $this->web_site['resourceIsbn'] = 'ID';
        $this->web_site['resourceShortTitle'] = 'shortTitle';
        // WEB ARTICLE
        $this->web_article = $this->basic;
        $this->web_article['creator1'] = 'author';
        $this->web_article['creator2'] = 'editor';
        $this->web_article['collectionTitle'] = 'journal';
        $this->web_article['collectionTitleShort'] = 'shortJournal';
        $this->web_article['resourceField1'] = 'volume';
        $this->web_article['resourceField2'] = 'issue';
        $this->web_article['pages'] = 'pages';
        //		$this->web_article['URL'] = 'URL';
        $this->web_article['date'] = 'accessDate';
        $this->web_article['publicationDate'] = 'publicationDate';
        $this->web_article['resourceyearYear2'] = 'accessYear';
        $this->web_article['publisherName'] = 'publisherName';
        $this->web_article['publisherLocation'] = 'publisherLocation';
        $this->web_article['resourceIsbn'] = 'ID';
        $this->web_article['resourceShortTitle'] = 'shortTitle';
        // WEB ENCYCLOPEDIA
        $this->web_encyclopedia = $this->basic;
        $this->web_encyclopedia['creator1'] = 'author';
        $this->web_encyclopedia['creator2'] = 'editor';
        $this->web_encyclopedia['resourceField2'] = 'edition';
        //		$this->web_encyclopedia['URL'] = 'URL';
        $this->web_encyclopedia['date'] = 'accessDate';
        $this->web_encyclopedia['resourceyearYear2'] = 'accessYear';
        $this->web_encyclopedia['resourceyearYear3'] = 'revisionYear';
        $this->web_encyclopedia['publisherName'] = 'publisherName';
        $this->web_encyclopedia['publisherLocation'] = 'publisherLocation';
        $this->web_encyclopedia['resourceIsbn'] = 'ID';
        $this->web_encyclopedia['resourceShortTitle'] = 'shortTitle';
        // WEB ENCYCLOPEDIA ARTICLE
        $this->web_encyclopedia_article = $this->basic;
        $this->web_encyclopedia_article['creator1'] = 'author';
        $this->web_encyclopedia_article['creator2'] = 'editor';
        $this->web_encyclopedia_article['resourceField2'] = 'edition';
        //		$this->web_encyclopedia_article['URL'] = 'URL';
        $this->web_encyclopedia_article['date'] = 'accessDate';
        $this->web_encyclopedia_article['resourceyearYear2'] = 'accessYear';
        $this->web_encyclopedia_article['resourceyearYear3'] = 'revisionYear';
        $this->web_encyclopedia_article['publisherName'] = 'publisherName';
        $this->web_encyclopedia_article['publisherLocation'] = 'publisherLocation';
        $this->web_encyclopedia_article['collectionTitle'] = 'encyclopedia';
        $this->web_encyclopedia_article['collectionTitleShort'] = 'encyclopediaShort';
        $this->web_encyclopedia_article['pages'] = 'pages';
        $this->web_encyclopedia_article['resourceIsbn'] = 'ID';
        $this->web_encyclopedia_article['resourceShortTitle'] = 'shortTitle';
        // FILM
        $this->film = $this->basic;
        $this->film['creator1'] = 'director';
        $this->film['creator2'] = 'producer';
        $this->film['resourceField1'] = 'country';
        $this->film['runningTime'] = 'runningTime';
        $this->film['publisherName'] = 'distributor';
        $this->film['resourceIsbn'] = 'ID';
        $this->film['resourceShortTitle'] = 'shortTitle';
        // BROADCAST
        $this->broadcast = $this->basic;
        $this->broadcast['creator1'] = 'director';
        $this->broadcast['creator2'] = 'producer';
        $this->broadcast['runningTime'] = 'runningTime';
        $this->broadcast['date'] = 'broadcastDate';
        $this->broadcast['resourceyearYear1'] = 'broadcastYear'; // override
        $this->broadcast['publisherName'] = 'channel';
        $this->broadcast['publisherLocation'] = 'channelLocation';
        $this->broadcast['resourceIsbn'] = 'ID';
        $this->broadcast['resourceShortTitle'] = 'shortTitle';
        // SOFTWARE
        $this->software = $this->basic;
        $this->software['creator1'] = 'author';
        $this->software['resourceField2'] = 'type';
        $this->software['resourceField4'] = 'version';
        $this->software['publisherName'] = 'publisherName';
        $this->software['publisherLocation'] = 'publisherLocation';
        $this->software['resourceIsbn'] = 'ID';
        $this->software['resourceShortTitle'] = 'shortTitle';
        // ARTWORK
        $this->artwork = $this->basic;
        $this->artwork['creator1'] = 'artist';
        $this->artwork['resourceField2'] = 'medium';
        $this->artwork['publisherName'] = 'publisherName';
        $this->artwork['publisherLocation'] = 'publisherLocation';
        $this->artwork['resourceIsbn'] = 'ID';
        $this->artwork['resourceShortTitle'] = 'shortTitle';
        // AUDIOVISUAL
        $this->audiovisual = $this->basic;
        $this->audiovisual['creator1'] = 'author';
        $this->audiovisual['creator2'] = 'performer';
        $this->audiovisual['creator5'] = 'seriesEditor';
        $this->audiovisual['resourceField1'] = 'seriesTitle';
        $this->audiovisual['resourceField4'] = 'seriesNumber';
        $this->audiovisual['resourceField3'] = 'edition';
        $this->audiovisual['resourcemiscField4'] = 'numberOfVolumes';
        $this->audiovisual['resourceField5'] = 'volumeNumber';
        $this->audiovisual['resourceyearYear3'] = 'volumePublicationYear';
        $this->audiovisual['publisherName'] = 'publisherName';
        $this->audiovisual['publisherLocation'] = 'publisherLocation';
        $this->audiovisual['resourceField2'] = 'medium';
        $this->audiovisual['resourceIsbn'] = 'ID';
        $this->audiovisual['resourceShortTitle'] = 'shortTitle';
        // (LEGAL) CASE
        $this->case = $this->basic;
        $this->case['resourceField1'] = 'reporter';
        $this->case['creator3'] = 'counsel';
        $this->case['resourceField4'] = 'reporterVolume';
        $this->case['date'] = 'caseDecidedDate';
        $this->case['resourceyearYear1'] = 'caseDecidedYear'; // override
        $this->case['publisherName'] = 'court';
        $this->case['resourceIsbn'] = 'resourceIsbn';
        $this->case['resourceShortTitle'] = 'shortTitle';
        // LEGAL RULING/REGULATION
        $this->legal_ruling = $this->basic;
        $this->legal_ruling['creator1'] = 'author';
        $this->legal_ruling['resourceField1'] = 'section';
        $this->legal_ruling['resourceField2'] = 'type';
        $this->legal_ruling['resourceField4'] = 'number';
        $this->legal_ruling['resourceField3'] = 'edition';
        $this->legal_ruling['date'] = 'codeEditionDate';
        $this->legal_ruling['resourceyearYear1'] = 'codeEditionYear'; // override
        $this->legal_ruling['publisherName'] = 'publisherName';
        $this->legal_ruling['publisherLocation'] = 'publisherLocation';
        $this->legal_ruling['pages'] = 'pages';
        $this->legal_ruling['resourceIsbn'] = 'resourceIsbn';
        $this->legal_ruling['resourceShortTitle'] = 'shortTitle';
        // (PARLIAMENTARY) BILL
        $this->bill = $this->basic;
        $this->bill['resourceField2'] = 'code';
        $this->bill['resourceField3'] = 'codeVolume';
        $this->bill['resourceField1'] = 'codeSection';
        $this->bill['resourceField5'] = 'number';
        $this->bill['resourceField4'] = 'session';
        $this->bill['resourceyearYear1'] = 'sessionYear'; // override publicationYear
        $this->bill['publisherName'] = 'legislativeBody';
        $this->bill['publisherLocation'] = 'publisherLocation';
        $this->bill['pages'] = 'pages';
        $this->bill['resourceIsbn'] = 'ID';
        $this->bill['resourceShortTitle'] = 'shortTitle';
        // CLASSICAL WORK
        $this->classical = $this->basic;
        $this->classical['creator1'] = 'attributedTo';
        $this->classical['resourceField4'] = 'volume';
        $this->classical['resourceIsbn'] = 'resourceIsbn';
        $this->classical['resourceShortTitle'] = 'shortTitle';
        // CONFERENCE PAPER
        $this->conference_paper = $this->basic;
        $this->conference_paper['creator1'] = 'author';
        $this->conference_paper['collectionTitle'] = 'conference';
        $this->conference_paper['collectionTitleShort'] = 'shortConference';
        $this->conference_paper['publisherName'] = 'conferenceOrganiser';
        $this->conference_paper['publisherLocation'] = 'conferenceLocation';
        $this->conference_paper['date'] = 'conferenceDate';
        $this->conference_paper['resourceyearYear2'] = 'conferenceYear';
        $this->conference_paper['resourceIsbn'] = 'ISSN';
        $this->conference_paper['resourceShortTitle'] = 'shortTitle';
        // no publication year as conference_po is unpublished
        unset($this->conference_paper['resourceyearYear1']);
        // CONFERENCE POSTER
        $this->conference_poster = $this->basic;
        $this->conference_poster['creator1'] = 'author';
        $this->conference_poster['collectionTitle'] = 'conference';
        $this->conference_poster['collectionTitleShort'] = 'shortConference';
        $this->conference_poster['publisherName'] = 'conferenceOrganiser';
        $this->conference_poster['publisherLocation'] = 'conferenceLocation';
        $this->conference_poster['date'] = 'conferenceDate';
        $this->conference_poster['resourceyearYear2'] = 'conferenceYear';
        $this->conference_poster['resourceIsbn'] = 'ISSN';
        $this->conference_poster['resourceShortTitle'] = 'shortTitle';
        // no publication year as conference_poster is unpublished
        unset($this->conference_poster['resourceyearYear1']);
        // MISCELLANEOUS
        $this->miscellaneous = $this->basic;
        $this->miscellaneous['creator1'] = 'creator';
        $this->miscellaneous['resourceField2'] = 'medium';
        $this->miscellaneous['publisherName'] = 'publisherName';
        $this->miscellaneous['publisherLocation'] = 'publisherLocation';
        $this->miscellaneous['resourceIsbn'] = 'ID';
        $this->miscellaneous['resourceShortTitle'] = 'shortTitle';
        // MISCELLANEOUS SECTION
        $this->miscellaneous_section = $this->basic;
        $this->miscellaneous_section['creator1'] = 'creator';
        $this->miscellaneous_section['resourceField2'] = 'medium';
        $this->miscellaneous_section['publisherName'] = 'publisherName';
        $this->miscellaneous_section['publisherLocation'] = 'publisherLocation';
        $this->miscellaneous_section['collectionTitle'] = 'collection';
        $this->miscellaneous_section['collectionTitleShort'] = 'collectionShort';
        $this->miscellaneous_section['resourceIsbn'] = 'ID';
        $this->miscellaneous_section['resourceShortTitle'] = 'shortTitle';
        $this->miscellaneous_section['pages'] = 'pages';
        // GOVERNMENT REPORT/DOCUMENTATION
        $this->government_report = $this->basic;
        $this->government_report['creator1'] = 'author';
        $this->government_report['creator2'] = 'editor';
        $this->government_report['resourceField2'] = 'department';
        $this->government_report['resourceField1'] = 'section';
        $this->government_report['resourceField4'] = 'volume';
        $this->government_report['resourceField5'] = 'issueNumber';
        $this->government_report['resourceField3'] = 'edition';
        $this->government_report['publisherName'] = 'publisherName';
        $this->government_report['publisherLocation'] = 'publisherLocation';
        $this->government_report['pages'] = 'pages';
        $this->government_report['resourceIsbn'] = 'ISSN';
        $this->government_report['resourceShortTitle'] = 'shortTitle';
        // REPORT/DOCUMENTATION
        $this->report = $this->basic;
        $this->report['creator1'] = 'author';
        $this->report['creator2'] = 'editor';
        $this->report['resourceField2'] = 'type';
        $this->report['resourceField1'] = 'seriesTitle';
        $this->report['resourceField5'] = 'number';
        $this->report['publisherName'] = 'institution';
        $this->report['publisherLocation'] = 'institutionLocation';
        $this->report['date'] = 'reportDate';
        $this->report['resourceyearYear1'] = 'reportYear'; // override
        $this->report['pages'] = 'pages';
        $this->report['resourceIsbn'] = 'ISSN';
        $this->report['resourceShortTitle'] = 'shortTitle';
        // GOVERNMENT/LEGAL HEARING
        $this->hearing = $this->basic;
        $this->hearing['resourceField1'] = 'committee';
        $this->hearing['resourceField2'] = 'legislativeBody';
        $this->hearing['resourceField3'] = 'session';
        $this->hearing['resourcemiscField4'] = 'numberOfVolumes';
        $this->hearing['resourceField4'] = 'documentNumber';
        $this->hearing['date'] = 'hearingDate';
        $this->hearing['resourceyearYear1'] = 'hearingYear'; // override
        $this->hearing['publisherName'] = 'publisherName';
        $this->hearing['publisherLocation'] = 'publisherLocation';
        $this->hearing['pages'] = 'pages';
        $this->hearing['resourceIsbn'] = 'ISSN';
        $this->hearing['resourceShortTitle'] = 'shortTitle';
        // ONLINE DATABASE
        $this->database = $this->basic;
        $this->database['creator1'] = 'author';
        //		$this->database['URL'] = 'URL';
        $this->database['date'] = 'accessDate';
        $this->database['resourceyearYear2'] = 'accessYear';
        $this->database['publisherName'] = 'publisherName';
        $this->database['publisherLocation'] = 'publisherLocation';
        $this->database['resourceIsbn'] = 'ID';
        $this->database['resourceShortTitle'] = 'shortTitle';
        // MANUSCRIPT
        $this->manuscript = $this->basic;
        $this->manuscript['creator1'] = 'author';
        $this->manuscript['collectionTitle'] = 'collection';
        $this->manuscript['collectionTitleShort'] = 'collectionShort';
        $this->manuscript['resourceField3'] = 'number';
        $this->manuscript['resourceField2'] = 'type';
        $this->manuscript['date'] = 'issueDate';
        $this->manuscript['resourceyearYear1'] = 'issueYear'; // override
        $this->manuscript['pages'] = 'pages';
        $this->manuscript['resourceIsbn'] = 'resourceIsbn';
        $this->manuscript['resourceShortTitle'] = 'shortTitle';
        // MAP
        $this->map = $this->basic;
        $this->map['creator1'] = 'cartographer';
        $this->map['creator5'] = 'seriesEditor';
        $this->map['resourceField1'] = 'seriesTitle';
        $this->map['resourceField2'] = 'type';
        $this->map['resourceField3'] = 'edition';
        $this->map['publisherName'] = 'publisherName';
        $this->map['publisherLocation'] = 'publisherLocation';
        $this->map['resourceIsbn'] = 'resourceIsbn';
        $this->map['resourceShortTitle'] = 'shortTitle';
        // CHART
        $this->chart = $this->basic;
        $this->chart['creator1'] = 'creator';
        $this->chart['resourceField1'] = 'fileName';
        $this->chart['resourceField2'] = 'program';
        $this->chart['resourceField3'] = 'size';
        $this->chart['resourceField4'] = 'type';
        $this->chart['resourceField5'] = 'version';
        $this->chart['resourceField6'] = 'number';
        $this->chart['publisherName'] = 'publisherName';
        $this->chart['publisherLocation'] = 'publisherLocation';
        $this->chart['resourceIsbn'] = 'ID';
        $this->chart['resourceShortTitle'] = 'shortTitle';
        // STATUTE
        $this->statute = $this->basic;
        $this->statute['resourceField2'] = 'code';
        $this->statute['resourceField5'] = 'codeNumber';
        $this->statute['resourceField1'] = 'publicLawNumber';
        $this->statute['resourceField3'] = 'session';
        $this->statute['resourceField4'] = 'section';
        $this->statute['date'] = 'statuteDate';
        $this->statute['resourceyearYear1'] = 'statuteYear'; // override
        $this->statute['pages'] = 'pages';
        $this->statute['resourceIsbn'] = 'ID';
        $this->statute['resourceShortTitle'] = 'shortTitle';
        // PATENT
        $this->patent = $this->basic;
        $this->patent['creator1'] = 'inventor';
        $this->patent['creator2'] = 'issuingOrganisation';
        $this->patent['creator3'] = 'agent';
        $this->patent['creator4'] = 'intAuthor';
        $this->patent['resourceField8'] = 'patentNumber';
        $this->patent['resourceField2'] = 'versionNumber';
        $this->patent['resourceField3'] = 'applicationNumber';
        $this->patent['resourceField6'] = 'intTitle';
        $this->patent['resourceField5'] = 'intPatentNumber';
        $this->patent['resourceField7'] = 'intClassification';
        $this->patent['resourceField1'] = 'publishedSource';
        $this->patent['resourceField9'] = 'legalStatus';
        $this->patent['resourceField4'] = 'type';
        $this->patent['publisherName'] = 'assignee';
        $this->patent['publisherLocation'] = 'assigneeLocation';
        $this->patent['date'] = 'issueDate';
        $this->patent['resourceyearYear1'] = 'issueYear'; // override
        $this->patent['resourceIsbn'] = 'ID';
        $this->patent['resourceShortTitle'] = 'shortTitle';
        // BROCHURE
        $this->brochure = $this->basic;
        $this->brochure['creator1'] = 'company';
        // PERSONAL COMMUNICATION
        $this->personal = $this->basic;
        $this->personal['creator1'] = 'author';
        $this->personal['creator2'] = 'recipient';
        $this->personal['resourceField2'] = 'type';
        $this->personal['date'] = 'date';
        $this->personal['resourceyearYear1'] = 'resourceyearYear'; // override
        $this->personal['resourceIsbn'] = 'ID';
        $this->personal['resourceShortTitle'] = 'shortTitle';
        // PROCEEDINGS (complete set of)
        $this->proceedings = $this->basic;
        $this->proceedings['creator2'] = 'editor';
        $this->proceedings['publisherName'] = 'conferenceOrganiser';
        $this->proceedings['publisherLocation'] = 'conferenceLocation';
        $this->proceedings['date'] = 'conferenceDate';
        $this->proceedings['resourceyearYear2'] = 'conferenceYear';
        $this->proceedings['publisher'] = 'publisherName'; // on the fly in BIBSTYLE
        $this->proceedings['location'] = 'publisherLocation'; // on the fly in BIBSTYLE
        $this->proceedings['resourceIsbn'] = 'ISSN';
        $this->proceedings['resourceShortTitle'] = 'shortTitle';
        // MUSIC ALBUM
        $this->music_album = $this->basic;
        $this->music_album['creator1'] = 'performer';
        $this->music_album['creator2'] = 'composer';
        $this->music_album['creator3'] = 'conductor';
        $this->music_album['resourceField2'] = 'medium';
        $this->music_album['publisherName'] = 'publisherName';
        $this->music_album['resourceIsbn'] = 'ID';
        $this->music_album['resourceShortTitle'] = 'shortTitle';
        // MUSIC TRACK
        $this->music_track = $this->basic;
        $this->music_track['creator1'] = 'performer';
        $this->music_track['creator2'] = 'composer';
        $this->music_track['creator3'] = 'conductor';
        $this->music_track['collectionTitle'] = 'album';
        $this->music_track['collectionTitleShort'] = 'shortAlbum';
        $this->music_track['resourceField2'] = 'medium';
        $this->music_track['publisherName'] = 'publisherName';
        $this->music_track['resourceIsbn'] = 'ID';
        $this->music_track['resourceShortTitle'] = 'shortTitle';
        // MUSIC SCORE
        $this->music_score = $this->basic;
        $this->music_score['creator1'] = 'composer';
        $this->music_score['creator2'] = 'editor';
        $this->music_score['resourceField3'] = 'edition';
        $this->music_score['publisherName'] = 'publisherName';
        $this->music_score['publisherLocation'] = 'publisherLocation';
        $this->music_score['resourceIsbn'] = 'resourceIsbn';
        $this->music_score['resourceShortTitle'] = 'shortTitle';
        // UNPUBLISHED WORK
        $this->unpublished = $this->basic;
        $this->unpublished['resourceyearYear1'] = 'resourceyearYear';
        $this->unpublished['creator1'] = 'author';
        $this->unpublished['resourceField2'] = 'type';
        $this->unpublished['publisherName'] = 'institution';
        $this->unpublished['publisherLocation'] = 'institutionLocation';
        $this->unpublished['resourceIsbn'] = 'ID';
        $this->unpublished['resourceShortTitle'] = 'shortTitle';
    }
}
