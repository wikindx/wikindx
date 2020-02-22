<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * Common functions for importing resources into wikindx
 *
 * @package wikindx\core\importexport
 */
class IMPORT
{
    /** int */
    public $resourceId;
    /** object */
    private $db;
    /** array */
    private $vars;
    /** object */
    private $messages;
    /** object */
    private $errors;
    /** object */
    private $session;
    /** object */
    private $config;
    /** object */
    private $creator;
    /** object */
    private $keyword;
    /** object */
    private $collection;
    /** object */
    private $collectionMap;
    /** object */
    private $publisher;
    /** object */
    private $publisherMap;
    /** object */
    private $bibConfig;

    /**
     *	IMPORT
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();

        $this->config = FACTORY_CONFIG::getInstance();
        $this->creator = FACTORY_CREATOR::getInstance();
        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->collection = FACTORY_COLLECTION::getInstance();
        $this->collectionMap = FACTORY_COLLECTIONMAP::getInstance();
        $this->publisher = FACTORY_PUBLISHER::getInstance();
        $this->publisherMap = FACTORY_PUBLISHERMAP::getInstance();
        $this->bibConfig = FACTORY_BIBTEXCONFIG::getInstance();
        $this->bibConfig->bibtex();
        $bibtexKeys = [];
        $recordset = $this->db->select('resource', 'resourceBibtexKey');
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->bibtexKeys[] = $row['resourceBibtexKey'];
        }
    }
    /**
     * Check for duplicate title/resourceType
     *
     * If the session variable 'import_ImportDuplicates' is TRUE, the function quits immediately.
     * NoSort, Title, Subtitle and resource type must match.
     *
     * @param string $noSort (A word like 'the', 'a' or translated equivalents that is at the start of the raw title)
     * @param string $title
     * @param string $subtitle
     * @param string $type
     *
     * @return bool TRUE if resource already exists, FALSE if not.
     */
    public function checkDuplicates($noSort, $title, $subtitle, $type)
    {
        if ($this->session->getVar('import_ImportDuplicates'))
        {
            return FALSE; // i.e. allow duplicates
        }
        if ($subtitle)
        {
            $subtitle = str_replace(['{', '}'], '', $subtitle);
            $this->db->formatConditions($this->db->replace($this->db->replace('resourceSubtitle', '{', ''), '}', '', FALSE) .
                $this->db->like(FALSE, $subtitle, FALSE));
        }
        else
        {
            $this->db->formatConditions(['resourceSubtitle' => ' IS NULL']);
        }
        if ($noSort)
        {
            $noSort = str_replace(['{', '}'], '', $noSort);
            $this->db->formatConditions($this->db->replace($this->db->replace('resourceNoSort', '{', ''), '}', '', FALSE) .
                $this->db->like(FALSE, $noSort, FALSE));
        }
        else
        {
            $this->db->formatConditions(['resourceNoSort' => ' IS NULL']);
        }
        $this->db->formatConditions(['resourceType' => $type]);
        $title = str_replace(['{', '}'], '', $title);
        $this->db->formatConditions($this->db->replace($this->db->replace('resourceTitle', '{', ''), '}', '', FALSE) .
            $this->db->equal . $this->db->tidyInput($title));
        $resultset = $this->db->select('resource', 'resourceId');

        return ($this->db->numRows($resultset));
    }
    /**
     * Print a list of discarded resources.
     *
     * @param int $numDiscarded Num. resources discarded
     * @param array $titlesDiscarded Titles of discarded resources
     *
     * @return string
     */
    public function printDuplicates($numDiscarded, $titlesDiscarded)
    {
        if (($numDiscarded <= 50) && !empty($titlesDiscarded))
        {
            $reject = $this->messages->text("import", "discarded", " " . $numDiscarded);
            foreach ($titlesDiscarded as $title)
            {
                $reject .= BR . $title;
            }

            return \HTML\p($reject);
        }

        return \HTML\p($this->messages->text("import", "discarded", " " . $numDiscarded));
    }
    /**
     * split title into WIKINDX component parts (noSort, title : subtitle)
     *
     * title / subtitle split is calculated on the session variable 'import_TitleSubtitleSeparator'.
     *
     * @param $title
     *
     * @return array (noSort, title, subtitle)
     */
    public function splitTitle($title)
    {
        if (!trim($title))
        {
            return [FALSE, FALSE, FALSE];
        }
        $noSort = $subtitle = FALSE;
        $split = $this->session->getVar('import_TitleSubtitleSeparator');
        if ($split)
        { // split title and subtitle
            switch ($split)
            {
                case 1:
                    $split = ': ';

                    break;
                case 2:
                    $split = '; ';

                    break;
                case 3:
                    $split = '[.!?] ';

                    break;
                case 4:
                    $split = '--';

                    break;
                case 5:
                    $split = '-';

                    break;
            }
            $array = preg_split("/$split/u", $title, 2);
            if (count($array) > 1)
            {
                $title = trim(array_shift($array));
                $subtitle = trim(implode('', $array));
                // Correct any incomplete braces
                if ((mb_strpos($title, '{') !== FALSE) && (mb_strpos($title, '}') === FALSE))
                {
                    $title .= '}';
                }
                if ((mb_strpos($subtitle, '}') !== FALSE) && (mb_strpos($subtitle, '{') === FALSE))
                {
                    $subtitle = '{' . $subtitle;
                }
            }
            else
            {
                $title = trim($array[0]);
            }
        }
        foreach ($this->config->WIKINDX_NO_SORT as $pattern)
        {
            if (preg_match("/^($pattern)\\s(.*)|^\\{($pattern)\\s(.*)/ui", $title, $matches))
            {
                if (array_key_exists(3, $matches))
                { // found second set of matches
                    $title = '{' . trim(\HTML\removeNl($matches[4]));
                    $noSort = trim(\HTML\removeNl($matches[3]));
                }
                else
                {
                    $title = trim(\HTML\removeNl($matches[2]));
                    $noSort = trim(\HTML\removeNl($matches[1]));
                }

                break;
            }
        }

        return [$noSort, $title, $subtitle];
    }
    /**
     * Select box for specifying keyword separation character in source bibliography
     *
     * @return string
     */
    public function keywordSeparator()
    {
        $sessVar = $this->session->issetVar('import_KeywordSeparator') ?
            $this->session->getVar('import_KeywordSeparator') : FALSE;
        $array = [
            $this->messages->text('misc', 'keywordImport1'),
            $this->messages->text('misc', 'keywordImport2'),
            $this->messages->text('misc', 'keywordImport3'),
            $this->messages->text('misc', 'keywordImport4'),
        ];
        if ($sessVar !== FALSE)
        {
            $pString = \FORM\selectedBoxValue(
                $this->messages->text('misc', 'keywordImport'),
                'import_KeywordSeparator',
                $array,
                $sessVar,
                4
            );
        }
        else
        {
            $pString = \FORM\selectFBoxValue(
                $this->messages->text('misc', 'keywordImport'),
                'import_KeywordSeparator',
                $array,
                4
            );
        }
        $sessVar = $this->session->issetVar('import_KeywordIgnore') ? TRUE : FALSE;

        return $pString .= \HTML\p(\FORM\checkBox(
            $this->messages->text('misc', 'keywordIgnore'),
            'import_KeywordIgnore',
            $sessVar
        ));
    }
    /**
     * Select box for specifying title/subtitle separation character in source bibliography
     *
     * @return string
     */
    public function titleSubtitleSeparator()
    {
        $sessVar = $this->session->getVar('import_TitleSubtitleSeparator');
        $array = [
            $this->messages->text('misc', 'titleSubtitleSeparator1'),
            $this->messages->text('misc', 'titleSubtitleSeparator2'),
            $this->messages->text('misc', 'titleSubtitleSeparator3'),
            $this->messages->text('misc', 'titleSubtitleSeparator4'),
            $this->messages->text('misc', 'titleSubtitleSeparator5'),
            $this->messages->text('misc', 'titleSubtitleSeparator6'),
        ];
        if ($sessVar !== FALSE)
        {
            return \FORM\selectedBoxValue(
                $this->messages->text('misc', 'titleSubtitleSeparator'),
                'import_TitleSubtitleSeparator',
                $array,
                $sessVar,
                6
            );
        }
        else
        {
            return \FORM\selectedBoxValue(
                $this->messages->text('misc', 'titleSubtitleSeparator'),
                'import_TitleSubtitleSeparator',
                $array,
                1,
                6
            );
        }
    }
    /**
     * Selext box for selecting user bibliographies to import into
     *
     * @return string
     */
    public function bibliographySelect()
    {
        // Get this user's bibliographies
        if ($this->session->getVar('mywikindx_Bibliographies'))
        {
            $bibsRaw = unserialize($this->session->getVar("mywikindx_Bibliographies"));
            foreach ($bibsRaw as $key => $value)
            {
                $bibsU[$key] = \HTML\dbToFormTidy($value);
            }
        }
        // Get this user's user group bibliographies
        if ($this->session->getVar('mywikindx_Groupbibliographies'))
        {
            $bibsRaw = unserialize($this->session->getVar("mywikindx_Groupbibliographies"));
            foreach ($bibsRaw as $key => $value)
            {
                $bibsUG[$key] = \HTML\dbToFormTidy($value);
            }
        }
        $bib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $bibsU = $bib->getUserBibs();
        $bibsUG = $bib->getGroupBibs();
        if (!empty($bibsU))
        {
            $bibsArray[-1] = $this->messages->text('user', 'userBibs');
            foreach ($bibsU as $key => $value)
            {
                $bibsArray[$key] = $value;
            }
        }
        if (!empty($bibsUG))
        {
            $bibsArray[-2] = $this->messages->text('user', 'userGroupBibs');
            foreach ($bibsUG as $key => $value)
            {
                $bibsArray[$key] = $value;
            }
        }
        if (isset($bibsArray))
        {
            return \FORM\selectFBoxValueMultiple($this->messages->text("user", 'bib'), "import_BibId", $bibsArray, 5);
        }
        else
        {
            return FALSE;
        }
    }
    /**
     * Write the resource table
     *
     * @param array $fields
     * @param array $values
     *
     * @return int New resource Id
     */
    public function writeResourceTable($fields, $values)
    {
        $this->db->insert('resource', $fields, $values);
        $this->resourceId = $this->db->lastAutoID();

        return $this->resourceId;
    }
    /**
     * Write the resource_misc table
     *
     * @param array $fields
     * @param array $values
     */
    public function writeResourcemiscTable($fields, $values)
    {
        $fields[] = 'resourcemiscId';
        $values[] = $this->resourceId;
        if (($this->session->getVar('setup_Superadmin') != 1) && ($this->session->getVar('setup_Quarantine')))
        {
            $fields[] = 'resourcemiscQuarantine';
            $values[] = 'Y';
        }
        $this->db->insert('resource_misc', $fields, $values);
    }
    /**
     * Write creator and resource_creator tables
     *
     * @param array ordered array of creators
     * @param mixed $creators
     */
    public function writeCreatorTables($creators)
    {
        if (empty($creators))
        { // need blank row for list operations
            $this->db->insert('resource_creator', ['resourcecreatorResourceId'], [$this->resourceId]);

            return;
        }
        $mainName = $mainId = $rowWritten = $deleteCache = FALSE;
        foreach ($creators as $role => $roleArray)
        {
            foreach ($roleArray as $order => $creatorArray)
            {
                $creatorId = FALSE;
                if ($creatorArray['surname'])
                { // entry in surname takes precedence
                    unset($creatorArray['select']);
                    $initials = $this->creator->formatInitials($creatorArray['initials']);
                    $creatorId = $this->creator->checkExists(
                        $creatorArray['surname'],
                        $creatorArray['firstname'],
                        $initials,
                        $creatorArray['prefix']
                    );
                    if (!$creatorId)
                    { // new creator
                        $writeArray = [];
                        $writeArray['creatorSurname'] = trim($creatorArray['surname']);
                        if ($creatorArray['firstname'])
                        {
                            $writeArray['creatorFirstname'] = trim($creatorArray['firstname']);
                        }
                        if ($creatorArray['prefix'])
                        {
                            $writeArray['creatorPrefix'] = trim($creatorArray['prefix']);
                        }
                        if (trim($initials))
                        {
                            $writeArray['creatorInitials'] = trim($initials);
                        }
                        $this->db->insert('creator', array_keys($writeArray), array_values($writeArray));
                        $creatorId = $this->db->lastAutoID();
                        if (!$mainName)
                        {
                            $mainName = $writeArray['creatorSurname'];
                            $mainId = $creatorId;
                        }
                        $deleteCache = TRUE;
                    }
                }
                elseif (array_key_exists('select', $creatorArray) && $creatorArray['select'])
                {
                    $creatorId = $creatorArray['select'];
                }
                if ($creatorId)
                {
                    if (!$mainName)
                    {
                        $this->db->formatConditions(['creatorId' => $creatorId]);
                        $mainName = $this->db->selectFirstField('creator', 'creatorSurname');
                        $mainId = $creatorId;
                    }
                    $writeArray = [];
                    $writeArray['resourcecreatorCreatorId'] = $creatorId;
                    $writeArray['resourcecreatorResourceId'] = $this->resourceId;
                    $writeArray['resourcecreatorCreatorMain'] = $mainId;
                    // remove all punctuation (keep apostrophe and dash for names such as Grimshaw-Aagaard and D'Eath)
                    $writeArray['resourcecreatorCreatorSurname'] = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s\-\']/u', '', $mainName));
                    $writeArray['resourcecreatorOrder'] = $order;
                    $writeArray['resourcecreatorRole'] = $role;
                    $this->db->insert('resource_creator', array_keys($writeArray), array_values($writeArray));
                    $rowWritten = TRUE;
                }
            }
        }
    }
    /**
     * Write the publisher table
     *
     * @param string $name Publisher name
     * @param string $location Publisher location
     * @param string $wkType wikindx resource type
     *
     * @return int Publisher ID
     */
    public function writePublisherTable($name, $location, $wkType)
    {
        $publisherId = FALSE;
        if ($name && (!$publisherId = $this->publisher->checkExists($name, $location)))
        {
            if ($name)
            {
                $writeArray['publisherName'] = $name;
            }
            if ($location)
            {
                $writeArray['publisherLocation'] = $location;
            }
            if (array_key_exists($wkType, $this->publisherMap->publisherTypes))
            {
                $writeArray['publisherType'] = $this->publisherMap->publisherTypes[$wkType];
            }
            $this->db->insert('publisher', array_keys($writeArray), array_values($writeArray));
            $publisherId = $this->db->lastAutoID();
        }

        return $publisherId;
    }
    /**
     * Write collection table
     *
     * @param string $title Collection title
     * @param string $titleShort Collection short title
     * @param string $wkType wikindx resource type
     *
     * @return int Collection ID
     */
    public function writeCollectionTable($title, $titleShort, $wkType)
    {
        if ($title)
        {
            $writeArray['collectionTitle'] = $title;
        }
        if ($titleShort)
        {
            $writeArray['collectionTitleShort'] = $titleShort;
        }
        $type = FALSE;
        if (array_key_exists($wkType, $this->collectionMap->collectionTypes))
        {
            $writeArray['collectionType'] = $type = $this->collectionMap->collectionTypes[$wkType];
        }
        if ($title && (!$collectionId = $this->collection->checkExists(FALSE, $title, $titleShort, $type)))
        {
            $this->db->insert('collection', array_keys($writeArray), array_values($writeArray));
            $collectionId = $this->db->lastAutoID();
        }

        return $collectionId;
    }
    /**
     * writeResourceTimestampTable - write timestamp to resource_timestamp table an add blank statistics row
     */
    public function writeResourceTimestampTable()
    {
        $this->db->insert(
            'resource_timestamp',
            ['resourcetimestampId', 'resourcetimestampTimestamp',
                'resourcetimestampTimestampAdd', ],
            [$this->resourceId, $this->db->formatTimestamp(), $this->db->formatTimestamp()]
        );
        $this->db->insert('statistics', ['statisticsResourceId'], [$this->resourceId]);
    }
    /**
     * Wite resource_page table
     *
     * @param array $fields
     * @param array $values
     */
    public function writePageTable($fields, $values)
    {
        $fields[] = 'resourcepageId';
        $values[] = $this->resourceId;
        $this->db->insert('resource_page', $fields, $values);
    }
    /**
     * Wite resource_year table
     *
     * @param array $fields
     * @param array $values
     */
    public function writeYearTable($fields, $values)
    {
        $fields[] = 'resourceyearId';
        $values[] = $this->resourceId;
        $this->db->insert('resource_year', $fields, $values);
    }
    /**
     * Write resource_text table
     *
     * @param string $notes
     * @param string $abstract
     * @param string $url Default is FALSE
     */
    public function writeResourcetextTable($notes, $abstract, $url = FALSE)
    {
        $fields[] = 'resourcetextId';
        $values[] = $this->resourceId;
        if ($notes)
        {
            $fields[] = 'resourcetextNote';
            $values[] = $notes;
            $fields[] = 'resourcetextAddUserIdNote';
            $values[] = $this->session->getVar("setup_UserId");
        }
        if ($abstract)
        {
            $fields[] = 'resourcetextAbstract';
            $values[] = $abstract;
            $fields[] = 'resourcetextAddUserIdAbstract';
            $values[] = $this->session->getVar("setup_UserId");
        }
        if ($url)
        {
            if (!is_array($url))
            {
                $url = [$url];
            }
            $fields[] = 'resourcetextUrls';
            $values[] = base64_encode(serialize($url));
        }
        $this->db->insert('resource_text', $fields, $values);
    }
    /**
     * Write keyword tables
     *
     * @param array $keywords
     */
    public function writeKeywordTables($keywords)
    {
        if ($this->session->getVar('import_KeywordIgnore'))
        {
            return;
        }
        foreach ($keywords as $kWord)
        {
            if (!$kId = $this->keyword->checkExists($kWord))
            {
                $this->db->insert('keyword', ['keywordKeyword'], [$kWord]);
                $kId = $this->db->lastAutoID();
            }
            $this->db->insert(
                'resource_keyword',
                ['resourcekeywordResourceId', 'resourcekeywordKeywordId'],
                [$this->resourceId, $kId]
            );
        }
    }
    /**
     * Write resource_category table
     *
     * @param string $categories Comma-delimited list of category IDs.  At the very least, this must comprise '1' (the 'General' category)
     */
    public function writeResourcecategoryTable($categories)
    {
        foreach (UTF8::mb_explode(',', $categories) as $cId)
        {
            $this->db->insert(
                'resource_category',
                ['resourcecategoryResourceId', 'resourcecategoryCategoryId'],
                [$this->resourceId, $cId]
            );
        }
    }
    /**
     * Write resource_custom tables
     *
     * @param string $string
     * @param int $customId
     */
    public function writeResourcecustomTable($string, $customId)
    {
        $writeArray = [];
        $this->db->formatConditions(['customId' => $customId]);
        if ($this->db->selectFirstField('custom', 'customSize') == 'S')
        {
            $writeArray['resourcecustomShort'] = $string;
        }
        else
        {
            $writeArray['resourcecustomLong'] = $string;
        }
        $writeArray['resourcecustomAddUserIdCustom'] = $this->session->getVar('setup_UserId');
        $writeArray['resourcecustomCustomId'] = $customId;
        $writeArray['resourcecustomResourceId'] = $this->resourceId;
        $this->db->insert('resource_custom', array_keys($writeArray), array_values($writeArray));
    }
    /**
     * writeImportRawTable - write import_raw table
     *
     * @param array $rejectedArray Rejected input values for this resource
     * @param int $bibtexStringId ID of the BibTeX string in the bibtex_string table. Default is FALSE
     * @param string $importType Default is FALSE
     */
    public function writeImportrawTable($rejectedArray, $bibtexStringId = FALSE, $importType = FALSE)
    {
        if (empty($rejectedArray) || !$this->session->getVar("import_Raw"))
        {
            return;
        }
        if (!$importType)
        {
            $importType = 'bibtex';
        }
        $rejected = '';
        foreach ($rejectedArray as $key => $value)
        {
            $rejected .= "$key = $value" . LF;
        }
        $fields[] = 'importrawId';
        $values[] = $this->resourceId;
        if ($bibtexStringId)
        {
            $fields[] = 'importrawStringId';
            $values[] = $bibtexStringId;
        }
        $fields[] = 'importrawText';
        $values[] = base64_encode(serialize($rejected));
        $fields[] = 'importrawImportType';
        $values[] = $importType;
        $this->db->insert('import_raw', $fields, $values);
    }
    /**
     * Write user_bibliography_resource table
     *
     * @param string $bibId Comma-delimited string of user_bibliography ids
     */
    public function writeUserbibliographyresourceTable($bibId)
    {
        if (!$bibId)
        {
            return;
        }
        foreach (UTF8::mb_explode(',', $bibId) as $bId)
        {
            $this->db->insert(
                'user_bibliography_resource',
                ['userbibliographyresourceResourceId', 'userbibliographyresourceBibliographyId'],
                [$this->resourceId, $bId]
            );
        }
    }
    /**
     * writeTagTable - write import tag to tag table
     *
     * @return int|FALSE lastAutoId
     */
    public function writeTagTable()
    {
        if ($tagId = $this->session->getVar('import_TagId'))
        {
            return $tagId;
        }
        if (!$tag = $this->session->getVar('import_Tag'))
        {
            return FALSE;
        }
        $this->db->insert('tag', ['tagTag'], [\HTML\removeNl($tag)]);

        return $this->db->lastAutoID();
    }
    /**
     * Write the bibtexKey field for a new resource
     */
    public function writeBibtexKey()
    {
        $letters = range('a', 'z');
        $sizeof = count($letters);
        $this->db->formatConditions(['resourceyearId' => $this->resourceId]);
        $recordset = $this->db->select(['resource_year'], ['resourceyearYear1',
            'resourceyearYear2', 'resourceyearYear3', 'resourceyearYear4', ]);
        $row = $this->db->fetchRow($recordset);
        if ($row['resourceyearYear1'])
        {
            $year = $row['resourceyearYear1'];
        }
        elseif ($row['resourceyearYear2'])
        {
            $year = $row['resourceyearYear2'];
        }
        elseif ($row['resourceyearYear3'])
        {
            $year = $row['resourceyearYear3'];
        }
        elseif ($row['resourceyearYear4'])
        {
            $year = $row['resourceyearYear4'];
        }
        else
        {
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
        if ((!is_array($row) || !array_key_exists('creatorSurname', $row) || !$row['creatorSurname']))
        { // anonymous
            $base = 'anon' . $year;
        }
        else
        {
            $prefix = '';
            if ($row['creatorPrefix'])
            {
                $prefix = utf8_decode($row['creatorPrefix']);
                foreach ($this->bibConfig->bibtexSpChPlain as $key => $value)
                {
                    $char = preg_quote(UTF8::mb_chr($key), '/');
                    $prefix = preg_replace("/$char/u", $value, $prefix);
                }
                $prefix = preg_replace("/\\W/u", '', $prefix);
            }
            $surname = utf8_decode($row['creatorSurname']);
            foreach ($this->bibConfig->bibtexSpChPlain as $key => $value)
            {
                $char = preg_quote(UTF8::mb_chr($key), '/');
                $surname = preg_replace("/$char/u", $value, $surname);
            }
            $surname = preg_replace("/\\W/u", '', $surname);
            $base = $prefix . $surname . $year;
        }
        $bibtexKey = $base;
        for ($i = 0; $i < $sizeof; $i++)
        {
            if (array_search($bibtexKey, $this->bibtexKeys) === FALSE)
            {
                $keyMade = TRUE;

                break;
            }
            $bibtexKey = $base . $letters[$i];
        }
        if (!$keyMade)
        {
            $bibtexKey = $base . '.' . $this->resourceId; // last resort
        }
        $bibtexKey = str_replace(' ', '', $bibtexKey);
        $this->bibtexKeys[] = $bibtexKey;
        $this->db->formatConditions(['resourceId' => $this->resourceId]);
        $this->db->update('resource', ['resourceBibtexKey' => $bibtexKey]);
    }
    /**
     * Tidy up the database by checking for creators etc. that do not have resources.
     *
     * This should be called just once at the end of multiple inserts.
     */
    public function tidyTables()
    {
        $this->creator->removeHanging();
        $this->publisher->removeHanging();
        $this->collection->removeHanging();
        $this->keyword->removeHanging();
    }
    /**
     * promptFieldNames - print field name mapping to browser as an intermediate stage when invalid field names are found in the input.
     *
     * This allows users to map them to any existing custom fields.
     *
     * @param array $entries
     * @param array $inputTypes
     * @param array $map
     * @param array $invalidFieldNames
     * @param mixed $strings array|FALSE. Default is FALSE
     * @param string $importType
     *
     * @return array 1st element is error message or FALSE, 2nd element is string for display
     */
    public function promptFieldNames($entries, $inputTypes, $map, $invalidFieldNames, $strings = FALSE, $importType = FALSE)
    {
        // Do some system management
        FILE\tidyFiles();
        
        // Write entries as serialised array temporarily to a data folder
        $serArray = base64_encode(serialize($entries));
        
        $dirName = WIKINDX_DIR_DATA_FILES;
        $fileName = sha1($serArray);
        $fullFileName = $dirName . DIRECTORY_SEPARATOR . $fileName;
        if (file_put_contents($fullFileName, $serArray) === FALSE)
        {
            return [$this->errors->text("file", "write", ": " . $fileName), FALSE];
        }

        // Write filename to session
        $this->session->setVar("import_FileNameEntries", $fullFileName);

        // Write $this->strings as serialised array temporarily to a data folder
        if ($strings)
        {
            $stringArray = base64_encode(serialize($strings));
            $fileName = sha1($stringArray);
            $fullFileName = $dirName . DIRECTORY_SEPARATOR . $fileName;
            if (file_put_contents($fullFileName, $stringArray) === FALSE)
            {
                return [$this->errors->text("file", "write", ": " . $fileName), FALSE];
            }

            // Write filename to session
            $this->session->setVar("import_FileNameStrings", $fullFileName);
        }
        // Write unrecognised fields to session as serialised array
        $serArray = base64_encode(serialize($invalidFieldNames));
        $this->session->setVar("import_UnrecognisedFields", $serArray);
        // Create select boxes of all valid field names in WIKINDX including custom fields
        $possibleFields[0] = $this->messages->text('misc', 'ignore');
        foreach ($map->types as $wkType => $extType)
        {
            if (array_search($extType, $inputTypes) === FALSE)
            {
                continue;
            }
            foreach ($map->{$wkType}['possible'] as $field)
            {
                if (array_search($field, $map->noMap) === FALSE)
                {
                    $possibleFields[] = $field;
                }
            }
        }
        $possibleFields = array_unique($possibleFields);
        // Get custom fields
        $recordset = $this->db->select('custom', ['customId', 'customLabel']);
        $customFound = FALSE;
        while ($row = $this->db->fetchRow($recordset))
        {
            $possibleFields[] = $row['customId'] . '&nbsp;&nbsp;custom:&nbsp;&nbsp;' . $row['customLabel'];
            $customFound = TRUE;
        }
        if (!$customFound)
        {
            return [FALSE, FALSE];
        }
        if ($importType == 'endnote')
        {
            $pString = \HTML\p($this->messages->text('import', 'invalidField3'));
        }
        else
        {
            $pString = \HTML\p($this->messages->text('import', 'invalidField1'));
        }
        if (!$importType || ($importType == 'bibtex'))
        {
            $pString .= \FORM\formHeader("import_IMPORTBIBTEX_CORE");
            $pString .= \FORM\hidden('method', 'stage2Invalid');
        }
        if (($importType == 'endnote'))
        {
            $pString .= \FORM\formHeader("importexportbib_importEndnote");
            $pString .= \FORM\hidden('method', 'stage2Invalid');
        }
        if (array_key_exists('type', $this->vars))
        {
            $pString .= \FORM\hidden('type', $this->vars['type']);
        }
        if (isset($this->vars['import_Tag']) && $this->vars['import_Tag'])
        {
            $pString .= \FORM\hidden('import_Tag', $this->vars['import_Tag']);
        }
        if (isset($this->vars['import_Categories']) && $this->vars['import_Categories'])
        {
            $pString .= \FORM\hidden('import_Categories', trim(implode(',', ($this->vars['import_Categories']))));
        }
        if (isset($this->vars['import_KeywordSeparator']))
        {
            $pString .= \FORM\hidden('import_KeywordSeparator', $this->vars['import_KeywordSeparator']);
        }
        if (isset($this->vars['import_KeywordIgnore']))
        {
            $pString .= \FORM\hidden('import_KeywordIgnore', $this->vars['import_KeywordIgnore']);
        }
        if (isset($this->vars['import_TitleSubtitleSeparator']))
        {
            $pString .= \FORM\hidden('import_TitleSubtitleSeparator', $this->vars['import_TitleSubtitleSeparator']);
        }
        if (isset($this->vars['import_Raw']) && $this->vars['import_Raw'])
        {
            $pString .= \FORM\hidden('import_Raw', $this->vars['import_Raw']);
        }
        if (isset($this->vars['import_ImportDuplicates']) && $this->vars['import_ImportDuplicates'])
        {
            $pString .= \FORM\hidden('import_ImportDuplicates', $this->vars['import_ImportDuplicates']);
        }
        if (isset($this->vars['import_BibId']) && $this->vars['import_BibId'])
        {
            $pString .= \FORM\hidden('import_BibId', trim(implode(',', ($this->vars['import_BibId']))));
        }
        foreach ($invalidFieldNames as $invalidField)
        {
            $pString .= \HTML\p(\FORM\selectFBox(
                \HTML\strong($invalidField),
                'import_' . $invalidField,
                $possibleFields,
                5
            ));
        }
        if (($importType == 'bibtex'))
        {
            $pString .= \HTML\p(\FORM\checkbox(
                $this->messages->text('import', 'invalidField2'),
                "import_Precedence"
            ));
        }
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Submit")));

        return [FALSE, $pString];
    }
    /**
     * getUnrecognised fields
     *
     * Follows from prompting for field mapping
     *
     * @return array 1st element is error message or FALSE, 2nd element is array of custom fields, 3rd element is array of unrecognized fields
     */
    public function getUnrecognisedFields()
    {
        $unrecognisedFields =
            unserialize(base64_decode($this->session->getVar("import_UnrecognisedFields")));
        $mapFields = $customFields = [];
        foreach ($unrecognisedFields as $key)
        {
            $importKey = 'import_' . $key;
            if (array_key_exists($importKey, $this->vars) && ($this->vars[$importKey] != $this->messages->text('misc', 'ignore')) &&
                (array_search($this->vars[$importKey], $mapFields) !== FALSE))
            {
                return [$this->errors->text("file", "fieldMap"), [], []];
            }
            elseif ($this->vars[$importKey] != $this->messages->text('misc', 'ignore'))
            {
                $mapFields[] = $this->vars[$importKey];
            }
            if (array_key_exists($importKey, $this->vars) && ($this->vars[$importKey] == $this->messages->text('misc', 'ignore')))
            {
                unset($this->vars[$importKey]);
            }
            elseif (array_key_exists($importKey, $this->vars) &&
                (count($split = UTF8::mb_explode("custom:", str_replace('&nbsp;&nbsp;', '', $this->vars[$importKey]))) == 2))
            {
                $customFields[$key] = $split[0];
                unset($this->vars[$importKey]);
            }
        }

        return [FALSE, $customFields, $unrecognisedFields];
    }
    /**
     * Set collectionDefault column in the collections table
     */
    public function collectionDefaults()
    {
        include_once("core/collection/COLLECTIONDEFAULTMAP.php");
        $defaultMap = new COLLECTIONDEFAULTMAP();
        $typesArray = array_unique(array_values($defaultMap->collectionTypes));
        $collectionArray = [];
        $maxPacket = $this->db->getMaxPacket();
        // For each 1MB max_allowed_packet (1048576 bytes), 600 updates in one go seems fine as a value for $maxCounts (based on trial and error)
        $maxCounts = floor(600 * ($maxPacket / 1048576));
        foreach ($typesArray as $type)
        {
            $fieldNames = [];
            foreach ($defaultMap->{$type} as $typeKey => $typeKeyArray)
            {
                $typeKey = str_replace('_', '', $typeKey);
                if (($typeKey == 'resource') && !empty($typeKeyArray))
                {
                    $this->db->leftJoin('resource', 'resourceId', 'resourcemiscId');
                    foreach ($typeKeyArray as $key => $value)
                    {
                        $fieldName = $typeKey . $key;
                        $fieldNames[] = $fieldName;
                    }
                }
                elseif (($typeKey == 'resourcemisc') && !empty($typeKeyArray))
                {
                    foreach ($typeKeyArray as $key => $value)
                    {
                        $fieldName = $typeKey . $key;
                        $fieldNames[] = $fieldName;
                    }
                }
                elseif (($typeKey == 'resourceyear') && !empty($typeKeyArray))
                {
                    $this->db->leftJoin('resource_year', 'resourceyearId', 'resourcemiscId');
                    foreach ($typeKeyArray as $key => $value)
                    {
                        $fieldName = $typeKey . $key;
                        $fieldNames[] = $fieldName;
                    }
                }
            }
            if (empty($fieldNames))
            {
                continue;
            }
            $fieldNames[] = 'collectionId';
            $fieldNames[] = 'resourcemiscId';
            $this->db->formatConditions(['resourcemiscCollection' => ' IS NOT NULL']);
            $this->db->formatConditions(['collectionDefault' => ' IS NULL']);
            $this->db->formatConditions(['collectionType' => $type]);
            $this->db->leftJoin('collection', 'collectionId', 'resourcemiscCollection');
            $resultset = $this->db->select('resource_misc', $fieldNames, TRUE);
            while ($row = $this->db->fetchRow($resultset))
            {
                foreach ($fieldNames as $fieldName)
                {
                    if (($fieldName == 'collectionId') || ($fieldName == 'resourcemiscId'))
                    {
                        continue;
                    }
                    if (
                        !array_key_exists($row['collectionId'], $collectionArray)
                        ||
                        (array_key_exists($row['collectionId'], $collectionArray)
                            && (!array_key_exists($fieldName, $collectionArray[$row['collectionId']])))
                        ) {
                        if ($row[$fieldName])
                        {
                            $collectionArray[$row['collectionId']][$fieldName] = $row[$fieldName];
                        }
                    }
                }
                if (array_key_exists('resource_creator', $defaultMap->{$type}) && !empty($defaultMap->{$type}['resource_creator']))
                {
                    $creators = [];
                    $roles = array_keys($defaultMap->{$type}['resource_creator']);
                    $this->db->formatConditions(['resourcecreatorResourceId' => $row['resourcemiscId']]);
                    $this->db->formatConditionsOneField($roles, 'resourcecreatorRole');
                    $this->db->orderBy('resourcecreatorOrder', TRUE, FALSE);
                    $resultsetC = $this->db->select('resource_creator', ['resourcecreatorCreatorId', 'resourcecreatorRole', 'resourcecreatorOrder']);
                    while ($rowC = $this->db->fetchRow($resultsetC))
                    {
                        $order = $rowC['resourcecreatorOrder'] - 1;
                        $creators['Creator' . $rowC['resourcecreatorRole'] . '_' . $order . '_select'] = $rowC['resourcecreatorCreatorId'];
                    }
                    if (!empty($creators))
                    {
                        $collectionArray[$row['collectionId']]['creators'] = $creators;
                    }
                }
            }
            if (!empty($collectionArray))
            {
                $count = 0;
                $updateArray = [];
                foreach ($collectionArray as $collectionId => $array)
                {
                    ++$count;
                    $updateArray[$collectionId] = base64_encode(serialize($array));
                    if ($count >= $maxCounts)
                    {
                        $this->db->multiUpdate('collection', 'collectiondefault', 'collectionId', $updateArray);
                        $updateArray = [];
                        $count = 0;
                    }
                    //					$this->db->formatConditions(array('collectionId' => $collectionId));
//					$this->db->update('collection', array('collectionDefault' => $default));
                }
                if (!empty($updateArray))
                { // do the remainder
                    $this->db->multiUpdate('collection', 'collectiondefault', 'collectionId', $updateArray);
                }
            }
        }
    }
}
