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
 * BIBLIOGRAPHY STYLE class
 *
 * Select a bibliographic style and perform some preprocessing
 *
 * @package wikindx\core\bibcitation
 */
class BIBSTYLE
{
    /** array */
    public $creators = [];
    /** object */
    public $parsexml;
    /** array */
    public $coinsCreators = [];
    /** string */
    public $output = 'html';
    /** string */
    public $shortOutput;
    /** boolean */
    public $export = FALSE;
    /** boolean */
    public $setupStyle = FALSE;
    /** object */
    public $bibformat;
    /** array */
    public $resourceCreators;
    /** boolean */
    public $linkUrl = TRUE;
    /** object */
    private $db;
    /** array */
    private $row = NULL;

    /**
     * BIBSTYLE
     *
     * @param string $output 'html', plain', 'rtf'. Default is 'html'
     */
    public function __construct($output = 'html')
    {
        $this->output = $output;
        $this->db = FACTORY_DB::getInstance();
        $this->parsexml = FACTORY_PARSEXML::getInstance();
        $this->bibformat = FACTORY_BIBFORMAT::getInstance($this->output);
        $this->init();
    }
    /**
     * Accept a SQL result row of raw bibliographic data and process it.
     *
     * We build up the $bibformat->item array with formatted parts from the raw $row.
     *
     * @param array $row
     * @param bool $shortOutput If TRUE, output just a short citation. Default is FALSE.
     * @param bool $singleResource If TRUE, we format just a single resource as in RESOURCEVIEW and so gather creator details here.
     *                             Otherwise, gathering creator details is done in LISTCOMMON::formatResources(). Default is TRUE.
     *
     * @return string
     */
    public function process($row, $shortOutput = FALSE, $singleResource = TRUE)
    {
        $row = array_map([$this, "removeSlashes"], $row);
        $this->row = $row;
        $this->shortOutput = $shortOutput;
        $type = $row['resourceType']; // WIKINDX type
        unset($row);
        // For WIKINDX, if type == book, book_chapter or book article and there exists both 'year1'
        // and 'year2' in $row (entered as publication year and reprint year respectively), then
        // switch these around as 'year1' is entered in the style template as 'originalPublicationYear'
        // and 'year2' should be 'publicationYear'.
        if (($type == 'book') || ($type == 'book_chapter') || ($type == 'book_article'))
        {
            $year2 = $this->row['resourceyearYear2'];
            if ($year2)
            {
                if (!$this->row['resourceyearYear1'])
                {
                    unset($this->row['resourceyearYear2']);
                }
                else
                {
                    $this->row['resourceyearYear2'] = $this->row['resourceyearYear1'];
                }

                $this->row['resourceyearYear1'] = $year2;
            }

            unset($year2);
        }

        if ($singleResource)
        {
            // Grab all creator IDs for this resource and normalize to OsBib's expected array keys for creators
            $creators = [];
            $this->db->formatConditions(['resourcecreatorResourceId' => $this->row['resourceId']]);
            $this->db->ascDesc = $this->db->asc;
            $this->db->orderBy('resourcecreatorRole', TRUE, FALSE);
            $this->db->orderBy('resourcecreatorOrder', TRUE, FALSE);
            $resultSet = $this->db->select('resource_creator', ['resourcecreatorCreatorId', 'resourcecreatorRole']);
            while ($cRow = $this->db->fetchRow($resultSet))
            {
                $creators[$cRow['resourcecreatorRole']][] = $cRow['resourcecreatorCreatorId'];
            }


            // Make creators public for use in e.g. RESOURCEVIEW()
            $this->resourceCreators = $creators;
            if (empty($creators))
            {
                for ($index = 1; $index <= 5; $index++)
                {
                    $this->row["creator$index"] = ''; // need empty fields for BIBSTYLE
                }
            }
            else
            {
                for ($index = 1; $index <= 5; $index++)
                {
                    if (array_key_exists($index, $creators))
                    {
                        $this->row["creator$index"] = implode(',', $creators[$index]);
                    }
                    else
                    {
                        $this->row["creator$index"] = '';
                    }
                }
            }
        }
        if ($this->parsexml->citation['citationStyle'] && ($this->parsexml->citation['endnoteStyle'] == 2))
        {
            // footnotes
            $this->bibformat->citationFootnote = TRUE;
        }
        $this->row = $this->bibformat->preProcess($type, $this->row);
        // Return $type is the OSBib resource type ($this->book, $this->web_article etc.) as used in STYLEMAP
        $type = $this->bibformat->type;
        $this->preProcess($type, $singleResource);
        // WIKINDX specific
        if ($this->shortOutput)
        {
            $pString = '';
            if ($this->row['creator1'])
            {
                $pString .= $this->bibformat->item[$this->bibformat->styleMap->{$type}['creator1']] . " ";
            }
            if ($this->row['resourceyearYear1'])
            {
                $pString .= $this->row['resourceyearYear1'] . " ";
            }
            if ($type == 'book_chapter')
            {
                $pString .= 'Ch. ' . $this->row['resourceTitle'] . ' ';
            }
            else
            {
                $pString .= $this->row['resourceTitle'];
            }
            if ($this->row['resourceSubtitle'])
            {
                $pString .= ": " . $this->row['resourceSubtitle'];
            }
            if ($type == 'book_chapter')
            {
                $pString .= 'In ' . $this->row['collectionTitle'];
            }
            $pString .= " [$type]";

            return preg_replace("/{(.*)}/Uu", "$1", $pString);
        }
        // We now have an array for this item where the keys match the key names of $this->styleMap->$type
        // where $type is book, journal_article, thesis etc. and are now ready to map this against the defined
        // bibliographic style for each resource ($this->book, $this->book_article etc.).
        // This bibliographic style array not only provides the formatting and punctuation for each field but also
        // provides the order. If a field name does not exist in this style array, we print nothing.
        $pString = $this->bibformat->map();
        // bibTeX ordinals such as 5$^{th}$
        $pString = preg_replace_callback("/(\\d+)\\$\\^\\{(.*)\\}\\$/u", [$this, "ordinals"], $pString);
        // remove extraneous {...}
        return preg_replace("/{(.*)}/Uu", "$1", $pString);
    }
    /**
     * Remove slashes depending upon output
     *
     * @param string $element
     *
     * @return string
     */
    public function removeSlashes($element)
    {
        if ($this->output == 'rtf')
        {
            return str_replace('\\', '\\\\', stripslashes($element));
        }
        else
        {
            return stripslashes($element);
        }
    }
    /**
     * Initialize the bib system
     */
    private function init()
    {
        if ($this->output == 'html')
        {
            $this->bibformat->output = $this->output;
        }
        // WIKINDX-specific
        $this->bibformat->wikindx = TRUE;
        $this->bibformat->initialise();
        /**
         * CSS class for highlighting search terms
         */
        $this->bibformat->patternHighlight = "highlight";
        if (empty($this->parsexml->info))
        {
            // not yet loaded
            $this->parsexml->loadStyle($this->output, $this->export);
        }
        $this->bibformat->getStyle($this->parsexml->common, $this->parsexml->types, $this->parsexml->footnote);
    }
    /**
     * Perform some pre-processing
     *
     * @param string $type
     * @param bool $singleResource If TRUE, we format just a single resource as in RESOURCEVIEW and so gather creator details here.
     */
    private function preProcess($type, $singleResource)
    {
        $localBibType = $this->bibformat->styleMap->$type;

        // Various types of creator
        for ($index = 1; $index <= 5; $index++)
        {
            if ($this->shortOutput && ($index > 1))
            {
                break;
            }
            if (!$this->row['creator' . $index])
            {
                continue;
            }
            if (!array_key_exists('creator' . $index, $localBibType))
            {
                continue;
            }
            else
            {
                $this->grabNames('creator' . $index, $singleResource);
            }
        }
        // The title of the resource
        $this->createTitle();
        if (!$this->shortOutput)
        {
            // custom fields
            if (array_key_exists($type, $this->bibformat->customTypes))
            {
                $custom = [];
                foreach ($localBibType as $key => $value)
                {
                    $split = \UTF8\mb_explode('_', $key);
                    if ((count($split) == 2) && ($split[0] == 'custom'))
                    {
                        $custom[] = $value;
                    }
                }
                if (!empty($custom))
                {
                    $this->createCustom($custom);
                }
            }
            // edition
            if ($editionKey = array_search('edition', $localBibType))
            {
                $this->createEdition($editionKey);
            }
            // pageStart and pageEnd
            $this->pages = FALSE; // indicates not yet created pages for articles
            if (array_key_exists('pages', $localBibType))
            {
                $this->createPages();
            }
            // Date
            if (array_key_exists('date', $localBibType))
            {
                $this->createDate();
            }
            // runningTime for film/broadcast
            if (array_key_exists('runningTime', $localBibType))
            {
                $this->createRunningTime();
            }
            // web_article URL
            if (array_key_exists('URL', $localBibType))
            {
                if ($itemElement = $this->createUrl())
                {
                    $this->bibformat->addItem($itemElement, 'URL');
                }
            }
            // DOI
            if (array_key_exists('DOI', $localBibType))
            {
                if ($itemElement = $this->createDoi())
                {
                    $this->bibformat->addItem($itemElement, 'DOI');
                }
            }
            // proceedings_article and proceedings can have publisher as well as organiser/location. Publisher is in 'miscField1'
            if (($type == 'proceedings_article') || ($type == 'proceedings'))
            {
                if ($this->row['resourcemiscField1'])
                {
                    $this->db->formatConditions(['publisherId' => $this->row['resourcemiscField1']]);
                    $recordset = $this->db->select('publisher', ["publisherName", "publisherLocation"]);
                    $pubRow = $this->db->fetchRow($recordset);

                    if (is_array($pubRow) && $pubRow['publisherName'])
                    {
                        $this->bibformat->addItem($pubRow['publisherName'], 'publisher');
                    }
                    if (is_array($pubRow) && $pubRow['publisherLocation'])
                    {
                        $this->bibformat->addItem($pubRow['publisherLocation'], 'location');
                    }
                }
            }
            // book and book_article can have a translated work's original publisher's details in `miscField1`
            elseif (($type == 'book') || ($type == 'book_article'))
            {
                if ($this->row['resourcemiscField1'])
                {
                    $this->db->formatConditions(['publisherId' => $this->row['resourcemiscField1']]);
                    $recordset = $this->db->select('publisher', ["publisherName", "publisherLocation"]);
                    $pubRow = $this->db->fetchRow($recordset);

                    if (is_array($pubRow) && $pubRow['publisherName'])
                    {
                        $this->bibformat->addItem($pubRow['publisherName'], 'transPublisherName');
                    }
                    if (is_array($pubRow) && $pubRow['publisherLocation'])
                    {
                        $this->bibformat->addItem($pubRow['publisherLocation'], 'transPublisherLocation');
                    }
                }
            }
            // For WIKINDX, resources of type thesis, have the thesis type stored as integers in $row['field1'] and the label stored in $row['field2']
            elseif ($type == 'thesis')
            {
                $field1 = [
                    0 => "UNKNOWN",
                    1 => "master's",
                    2 => "doctoral",
                    3 => "PhD",
                    4 => "diploma",
                    5 => "EdD", ];
                $field2 = [
                    1 => "thesis",
                    2 => "dissertation", ];
            }

            // publisher field no longer needed....
            unset($this->row['resourcemiscPublisher']);

            // the rest...  All other database resource fields that do not require special formatting/conversion.
            $this->bibformat->addAllOtherItems($this->row);
        }
        // Add the publication year for short output.
        elseif (array_key_exists('year1', $localBibType) && $this->row['year1'])
        {
            $this->bibformat->addItem($this->row['year1'], 'year1');
        }
    }
    /**
     * callback for ordinals
     *
     * @param array $matches
     *
     * @return string
     */
    private function ordinals($matches)
    {
        if ($this->output == 'html')
        {
            return $matches[1] . "<sup>" . $matches[2] . "</sup>";
        }
        elseif ($this->output == 'rtf')
        {
            return $matches[1] . "{{\\up5 " . $matches[2] . "}}";
        }
        else
        {
            return $matches[1] . $matches[2];
        }
    }
    /**
     * Create the resource title
     */
    private function createTitle()
    {
        $pString = $this->row['resourceNoSort'] . ' ' . $this->row['resourceTitle'];
        // If title ends in a sentence-end marker, don't add titleSubtitleSeparator
        if ($this->row['resourceSubtitle'])
        {
            if (preg_match("/[?!¡¿.]$/u", $this->row['resourceTitle']))
            {
                /*
                Why should there be no linebreak and two spaces between title and subtitle?
                if ($this->output == 'html')
                    $pString .= "&nbsp;&nbsp;";
                else
                */
                $pString .= ' ';
            }
            else
            {
                $pString .= $this->bibformat->style['titleSubtitleSeparator'];
            }
        }
        // anything enclosed in {...} is to be left as is
        $this->bibformat->formatTitle($pString, "{", "}"); // title
        if ($this->row['resourceSubtitle'])
        {
            $this->bibformat->formatTitle($this->row['resourceSubtitle'], "{", "}"); // subTitle
        }
        // Title of the original work from which a translation has been made.
        $pString = $this->row['resourceTransNoSort'] . ' ' . $this->row['resourceTransTitle'];

        if ($this->row['resourceTransSubtitle'])
        {
            if (preg_match("/[?!¡¿.]$/u", $this->row['resourceTransTitle']))
            {
                /*
                Why should there be no linebreak and two spaces between title and subtitle?
                if($this->output == 'html')
                    $pString .= "&nbsp;&nbsp;";
                else
                */
                $pString .= ' ';
            }
            else
            {
                $pString .= $this->bibformat->style['titleSubtitleSeparator'];
            }
        }

        // anything enclosed in {...} is to be left as is
        $this->bibformat->formatTransTitle($pString, "{", "}");
        if ($this->row['resourceTransSubtitle'])
        {
            $this->bibformat->formatTransTitle($this->row['resourceTransSubtitle'], "{", "}");
        }
        if ($this->row['resourceShortTitle'])
        {
            $this->bibformat->formatShortTitle($this->row['resourceShortTitle'], "{", "}");
        }
    }
    /**
     * Grab any custom fields for this resource
     *
     * @param array $array (resourcecustomCustomId)
     */
    private function createCustom($array)
    {
        $this->db->formatConditionsOneField($array, 'resourcecustomCustomId');
        $this->db->formatConditions(['resourcecustomResourceId' => $this->row['resourceId']]);
        $resource = $this->db->select('resource_custom', ['resourcecustomShort', 'resourcecustomLong', 'resourcecustomCustomId']);

        while ($row = $this->db->fetchRow($resource))
        {
            if ($row['resourcecustomShort'])
            {
                $this->bibformat->item['custom_' . $row['resourcecustomCustomId']] = $row['resourcecustomShort'];
            }
            elseif ($row['resourcecustomLong'])
            {
                $this->bibformat->item['custom_' . $row['resourcecustomCustomId']] = $row['resourcecustomLong'];
            }
        }
    }
    /**
     * Create the URL
     *
     * @return false|string
     */
    private function createUrl()
    {
        if (!$this->row['resourcetextUrls'])
        {
            return FALSE;
        }
        $urls = \URL\getUrls($this->row['resourcetextUrls']);
        if (empty($urls))
        {
            return FALSE;
        }
        // In $urls array, [0] index is primary URL
        $url = ($this->output == 'html') ? htmlspecialchars($urls[0]) : $urls[0];
        unset($this->row['resourcetextUrls']);
        if ($this->output == 'html')
        {
            $label = \URL\reduceUrl($url, 50);
            if ($this->linkUrl)
            {
                return \HTML\a('rLink', $label, $url, "_blank");
            }
            else
            {
                return $label;
            }
        }
        else
        {
            return $url;
        }
    }
    /**
     * Create the DOI
     *
     * @return false|string
     */
    private function createDoi()
    {
        if (!$this->row['resourceDoi'])
        {
            return FALSE;
        }
        // In $urls array, [0] index is primary URL
        $doi = ($this->output == 'html') ? htmlspecialchars($this->row['resourceDoi']) : $this->row['resourceDoi'];
        unset($this->row['resourceDoi']);
        $doi = 'https://dx.doi.org/' . $doi;
        if ($this->output == 'html')
        {
            $label = \URL\reduceUrl($doi, 50);
            if ($this->linkUrl)
            {
                return \HTML\a('rLink', $label, $doi, "_blank");
            }
            else
            {
                return $label;
            }
        }
        else
        {
            return $doi;
        }
    }
    /**
     * Create date
     */
    private function createDate()
    {
        $startDay = isset($this->row['resourcemiscField2']) ? $this->row['resourcemiscField2'] : FALSE;
        $startMonth = isset($this->row['resourcemiscField3']) ? $this->row['resourcemiscField3'] : FALSE;
        unset($this->row['resourcemiscField2']);
        unset($this->row['resourcemiscField3']);
        $endDay = isset($this->row['resourcemiscField5']) ? $this->row['resourcemiscField5'] : FALSE;
        $endMonth = isset($this->row['resourcemiscField6']) ? $this->row['resourcemiscField6'] : FALSE;
        unset($this->row['resourcemiscField5']);
        unset($this->row['resourcemiscField6']);
        $startDay = ($startDay == 0) ? FALSE : $startDay;
        $startMonth = ($startMonth == 0) ? FALSE : $startMonth;
        if (!$startMonth)
        {
            return;
        }
        $endDay = ($endDay == 0) ? FALSE : $endDay;
        $endMonth = ($endMonth == 0) ? FALSE : $endMonth;
        if ($this->row['resourceType'] == 'web_article')
        {
            if ($endDay && !$endMonth)
            {
                $endDay == FALSE;
            }
            elseif ($endMonth)
            {
                $this->bibformat->formatDate($endDay, $endMonth, FALSE, FALSE, TRUE);
                $endDay = $endMonth = FALSE;
            }
        }
        $this->bibformat->formatDate($startDay, $startMonth, $endDay, $endMonth);
    }
    /**
     * Create runningTime for film/broadcast
     */
    private function createRunningTime()
    {
        $minutes = $this->row['resourcemiscField1'];
        $hours = $this->row['resourcemiscField4'];
        if (!$hours && !$minutes)
        {
            return;
        }
        if (!$hours)
        {
            $hours = 0;
        }
        $this->bibformat->formatRunningTime($minutes, $hours);
    }
    /**
     * Create the edition number
     *
     * @param int $editionKey
     *
     * @return mixed
     */
    private function createEdition($editionKey)
    {
        if (!$this->row[$editionKey])
        {
            return FALSE;
        }
        $edition = $this->row[$editionKey];
        $this->bibformat->formatEdition($edition);
    }
    /**
     * Create page start and page end
     */
    private function createPages()
    {
        if (!$this->row['resourcepagePageStart'] || $this->pages)
        { // empty field or page format already done
            $this->pages = TRUE;

            return;
        }
        $this->pages = TRUE;
        $start = trim($this->row['resourcepagePageStart']);
        $end = $this->row['resourcepagePageEnd'] ? trim($this->row['resourcepagePageEnd']) : FALSE;
        $this->bibformat->formatPages($start, $end);
    }
    /**
     * get names from database for creator, editor, translator etc.
     *
     * @param string $nameType
     * @param bool $singleResource If TRUE, we format just a single resource as in RESOURCEVIEW and so gather creator details here.
     */
    private function grabNames($nameType, $singleResource)
    {
        $this->coinsCreators = [];
        $nameIds = \UTF8\mb_explode(",", $this->row[$nameType]);
        foreach ($nameIds as $nameId)
        {
            if (array_key_exists($nameId, $this->creators))
            {
                $rowSql[$nameId] = $this->creators[$nameId];

                continue;
            }
            $conditions[] = $this->db->formatFields("creatorId") . $this->db->equal . $this->db->tidyInput($nameId);
        }
        if (!isset($conditions))
        {
            $this->bibformat->formatNames($rowSql, $nameType);
            $this->coinsCreators = $rowSql;

            return;
        }
        if ($singleResource)
        {
            $this->db->formatConditions(implode($this->db->or, $conditions));
            $recordset = $this->db->select('creator', [["creatorSurname" => 'surname'],
                ["creatorFirstname" => 'firstname'],
                ["creatorInitials" => 'initials'], ["creatorPrefix" => 'prefix'], "creatorId", ]);
            // Reorder $row so that creator order is correct and not that returned by SQL
            while ($row = $this->db->fetchRow($recordset))
            {
                $this->creators[$row['creatorId']] = $rowSql[$row['creatorId']] = array_map([$this, "removeSlashes"], $row);
            }
        }
        if (!isset($rowSql))
        {
            return FALSE;
        }
        foreach ($nameIds as $id)
        {
            if (array_key_exists($id, $rowSql))
            {
                $rowTemp[] = $this->coinsCreators[] = $rowSql[$id];
            }
        }
        $this->bibformat->formatNames($rowTemp, $nameType);
    }
}
