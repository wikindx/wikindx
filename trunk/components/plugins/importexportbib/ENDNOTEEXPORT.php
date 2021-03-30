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
 * ENDNOTE (XML & tabbed file) export class
 */
class ENDNOTEEXPORT
{
    private $db;
    private $vars;
    private $session;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $common;
    private $parentClass;
    private $map;
    private $pString;
    private $authorFound;
    private $rawEntries;
    // 2012: Endnote currently has a bug regarding tabbed files and reference field: Electronic Resource Number. Therefore, for now, tabbed file exporting is disabled.
    private $xml = TRUE;
    private $badInput;
    private $customMap = [];

    /**
     * Constructor
     *
     * @param mixed $parentClass
     */
    public function __construct($parentClass = FALSE)
    {
        $this->parentClass = $parentClass;
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "EXPORTCOMMON.php"]));
        $this->common = new EXPORTCOMMON();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "ENDNOTEMAP.php"]));
        $this->map = new ENDNOTEMAP();
    }
    /**
     * write $this->pString to file.  If file exists, it is written over.
     */
    public function process()
    {
        if (array_key_exists('mergeStored', $this->vars))
        {
            $this->session->setVar("exportMergeStored", $this->vars['mergeStored']);
        }
        else
        {
            $this->session->delVar("exportMergeStored");
        }
        // Check for any custom fields – write session first before notifying error
        $error = FALSE;
        foreach ($this->vars as $key => $value)
        {
            if (strstr($key, 'MapInternal_'))
            {
                $index = str_replace('MapInternal_', '', $key);
                if ($value && (array_search($value, $this->customMap) !== FALSE))
                {
                    $error = TRUE;
                }
                if ($value)
                {
                    $this->customMap[$index] = $value;
                }
                $this->session->setVar("exportMapInternal_" . $index, $value);
            }
        }
        if ($error)
        {
            $this->badInput->close($this->errors->text("inputError", "duplicateCustomMap"), $this->parentClass, 'initEndnoteExportL');
        }
        // Disabled due to tabbed file bug above
        /*		if (array_key_exists('endnoteFileType', $this->vars))
                    $this->session->setVar("exportEndnoteFileType", $this->vars['endnoteFileType']);
                else
                    $this->session->delVar("exportEndnoteFileType");
        */
        $sql = $this->common->getSQL();
        if (!$sql)
        {
            $this->failure(HTML\p($this->pluginmessages->text("noList"), 'error'));
        }
        if (!$this->common->openFile('.xml', 'a'))
        {
            $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
        }
        $sqlArray = unserialize(base64_decode($sql));
        // $sql = preg_replace("/\s*LIMIT \d*,\s*\d*/u", '', $sql);
        // Disabled due to tabbed file bug above
        /*
                if ($this->session->getVar("exportEndnoteFileType") == 1) // tabbed
                {
                    // Initial generic field
                    $this->pString = "*Generic\r";
                    $this->getData($sql);
                    $this->pString = HTML\stripHtml($this->pString);
                    list($fileName, $fullFileName) = $this->common->createFileName($this->pString, '.txt');
                }
                else // 2 or assume XML
                {
        */
        $this->xml = TRUE;
        if (!$this->xmlHeader())
        {
            $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
        }
        foreach ($sqlArray as $sql)
        {
            $recordset = $this->db->query($sql);
            if (!$this->getData($recordset))
            {
                $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
            }
        }
        if (!$this->xmlFooter())
        {
            $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
        }
        //		}
        if ($this->common->fullFileName)
        {
            fclose($this->common->fp);
        }
        $pString = HTML\p($this->pluginmessages->text('exported') . ": " . $this->common->fileName, 'success');
        $this->common->writeFilenameToSession($this->common->fileName);
        $this->parentClass->listFiles($pString, 'initEndnoteExport');
    }
    /**
     * decode()
     *
     * @param mixed $input
     *
     * @return string
     */
    private function decode($input)
    {
        return preg_replace_callback("/(&amp;#[0-9]+;)/u", function ($m) {
            return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
        }, $input);
    }
    /**
     * get data from database
     *
     * @param mixed $recordset
     *
     * @return true
     */
    private function getData($recordset)
    {
        $recordsetCopy = $recordset;
        $this->pString = '';
        // load default arrays (English constants, day and month conversions etc.
        $this->loadArrays();
        if (!$this->xml)
        {
            // Get all possible field names from $this->map
            $fieldNameArray = $this->map->generic;
            // Put 'Reference Type' at the front
            array_unshift($fieldNameArray, 'Reference Type');
            // Field names line
            $this->pString .= implode(TAB, $fieldNameArray) . CR;
        }
        $exportRawFields = $this->map->exportRawFields;
        /**
         * Need to get raw data first and add its keys to $fieldNameArray
         */
        if ($this->session->getVar("exportMergeStored"))
        {
            $resourceIds = [];
            while ($row = $this->db->fetchRow($recordset))
            {
                if (array_search($row['resourceId'], $resourceIds) === FALSE)
                {
                    $resourceIds[] = $row['resourceId'];
                }
                else
                {
                    continue;
                }
                if ($raw = $this->raw($row))
                {
                    foreach ($raw as $key => $value)
                    {
                        if (!array_key_exists($key, $exportRawFields))
                        {
                            continue;
                        }
                        $key = $exportRawFields[$key];
                        if (array_search($key, $fieldNameArray) === FALSE)
                        {
                            $fieldNameArray[] = $key;
                        }
                    }
                    $rawEntries[$row['resourceId']] = $raw;
                }
            }
        }
        $resourceIds = [];
        $count = 0;
        while ($row = $this->db->fetchRow($recordsetCopy))
        {
            if (array_search($row['resourceId'], $resourceIds) === FALSE)
            {
                $resourceIds[] = $row['resourceId'];
            }
            else
            {
                continue;
            }
            $this->authorFound = FALSE;
            $lineArray = $this->rawEntries = $contributors = $titles = [];
            if ($this->xml)
            {
                $this->pString .= "<record>";
            }
            $this->raw($row);
            if (!$this->xml)
            {
                for ($index = 0; $index < count($fieldNameArray); $index++)
                {
                    $lineArray[] = '';
                }
            }
            // Do we need to switch `year1` (publicationYear) and `year2` (reprintYear)
            if ((($row['resourceType'] == 'book') || ($row['resourceType'] == 'book_article') || ($row['resourceType'] == 'book_chapter'))
                && $row['resourceyearYear1'] && $row['resourceyearYear2'])
            {
                $tempYear2 = $row['resourceyearYear2'];
                $row['resourceyearYear2'] = $row['resourceyearYear1'];
                $row['resourceyearYear1'] = $tempYear2;
            }
            // else, always use `year2` (conference year etc.) in preference to `year1` except for web_article
            elseif ($row['resourceyearYear2'] && ($row['resourceType'] != 'web_article')
                 && ($row['resourceType'] != 'web_site') && ($row['resourceType'] != 'web_encyclopedia')
                  && ($row['resourceType'] != 'web_encyclopedia_article'))
            {
                $row['resourceyearYear1'] = $row['resourceyearYear2'];
                unset($row['resourceyearYear2']);
            }
            // Handle dates
            if ($this->xml && ($dates = $this->dateFormatXml($row)))
            {
                $this->pString .= $dates;
            }
            foreach ($this->map->{$row['resourceType']} as $table => $tableArray)
            {
                if ($table == 'resource_creator')
                {
                    continue;
                }
                foreach ($tableArray as $wkField => $enField)
                {
                    if ($this->xml)
                    {
                        $field = array_search($enField, $this->map->endnoteXmlFields8);
						if ($field == 'url')
						{
						    $this->db->formatConditions(['resourceurlResourceId' => $row['resourceId']]);
							$resultSet2 = $this->db->select('resource_url', 'resourceurlUrl');
							$urls = '';
							while ($row2 = $this->db->fetchRow($resultSet2)) {
								$urls .= "<$field><style>" . $this->spCharFormat($row2['resourceurlUrl']) . "</style></$field>";
							}
							if ($urls) {
								$this->pString .= "<urls><related-urls>$urls</related-urls></urls>";
							}
						}
                        elseif (($field !== FALSE) && array_key_exists($wkField, $row) && $row[$wkField])
                        {
                            if (($field == 'secondary-title') || ($field == 'tertiary-title') ||
                                    ($field == 'alt-title') || ($field == 'short-title'))
                            {
                                $titles[] = "<$field><style>" . $this->spCharFormat(HTML\stripHtml(stripslashes($row[$wkField]))) .
                                    "</style></$field>";
                            }
                            else
                            {
                                $this->pString .= "<$field><style>" .
                                    $this->spCharFormat(HTML\stripHtml(stripslashes($row[$wkField]))) . "</style></$field>";
                            }
                        }
                    }
                    else
                    {
                        $fieldNameIndex = array_search($enField, $fieldNameArray);
						if ($enField == 'URL')
						{ // grab primary URL
							$this->db->formatConditions(['resourceurlResourceId' => $row['resourceId'], 'resourceurlPrimary' => 1]);
    						$resultSet = $this->db->select('resource_url', 'resourceurlUrl');
							if ($this->db->numRows($resultSet)) {
								$lineArray[$fieldNameIndex] = $this->db->fetchOne($resultSet);
							}
						}
                        elseif (array_key_exists($wkField, $row) && $row[$wkField])
                        {
                                $lineArray[$fieldNameIndex] = stripslashes($row[$wkField]);
                        }
                    }
                }
            }
            // The title
            if ($this->xml)
            {
                $titles[] = "<title><style>" . $this->spCharFormat(HTML\stripHtml($this->common->titleFormat($row))) .
                    "</style></title>";
                $this->pString .= "<titles>" . implode('', $titles) . "</titles>";
            }
            else
            {
                // We now have an array padded with blank elements and filled with populated fields for that resource in
                // the correct order.  Have to add in the special fields we array_merged above.
                $lineArray[array_search('Title', $fieldNameArray)] = $this->common->titleFormat($row);
            }
            // creators
            if ($this->xml)
            {
                foreach ($this->map->{$row['resourceType']}['resource_creator'] as $wkCreator => $enCreator)
                {
                    if (!$creators = $this->nameFormat($row, $wkCreator))
                    {
                        continue;
                    }
                    $contributors[] = $creators;
                }
                if (!empty($contributors))
                {
                    $this->pString .= "<contributors>" . implode("", $contributors) . "</contributors>";
                }
            }
            else
            {
                foreach ($this->map->{$row['resourceType']}['resource_creator'] as $wkCreator => $enCreator)
                {
                    if (!$creators = $this->nameFormat($row, $wkCreator))
                    {
                        continue;
                    }
                    if (!$this->authorFound and ($row['resourceType'] == 'book'))
                    { // 'Edited Book'
                        $lineArray[array_search('Author', $fieldNameArray)] = $creators;
                    }
                    else
                    {
                        $lineArray[array_search($enCreator, $fieldNameArray)] = $creators;
                    }
                }
            }
            // Reference Type.  If !$this->authorFound and $row['type'] == 'book', this is actually
            // Endnote's 'Edited Book' type.
            if ($this->xml)
            {
                if (!$this->authorFound and ($row['resourceType'] == 'book'))
                {
                    $this->pString .= "<source-app name=\"EndNote\" version=\"8.0\">EndNote</source-app><ref-type name=\"Edited Book\">28</ref-type>";
                }
                else
                {
                    if (!array_key_exists($row['resourceType'], $this->map->exportTypes8))
                    {
                        $refType = 'Generic';
                        $refNum = 13;
                    }
                    else
                    {
                        $refType = $this->map->types[$row['resourceType']];
                        $refNum = $this->map->exportTypes8[$row['resourceType']];
                    }
                    $this->pString .= "<source-app name=\"EndNote\" version=\"8.0\">EndNote</source-app><ref-type name=\"$refType\">$refNum</ref-type>";
                }
            }
            else
            {
                if (!$this->authorFound and ($row['resourceType'] == 'book'))
                {
                    $lineArray[array_search('Reference Type', $fieldNameArray)] = 'Edited Book';
                }
                else
                {
                    $lineArray[array_search('Reference Type', $fieldNameArray)] =
                        stripslashes($this->map->types[$row['resourceType']]);
                }
            }
            // web_article Access Year is stored in 'Volume' and Access Date in 'Number'
            if ($this->xml)
            {
                if ($output = $this->spCharFormat($this->common->pageFormat($row, 'endnoteXml')))
                {
                    $this->pString .= "<pages><style>$output</style></pages>";
                }
                if ($output = $this->common->keywordFormat($row, 'endnoteXml'))
                {
                    $string = '';
                    if (is_array($output))
                    {
                        foreach ($output as $kw)
                        {
                            $string .= "<keyword><style>" . $this->spCharFormat($kw) . "</style></keyword>";
                        }
                    }
                    if ($string)
                    {
                        $this->pString .= "<keywords>$string</keywords>";
                    }
                }
                if ($output = $this->spCharFormat(HTML\stripHtml($this->common->grabAbstract($row, 'endnoteXml'))))
                {
                    $this->pString .= "<abstract><style>$output</style></abstract>";
                }
                if ($output = $this->spCharFormat(HTML\stripHtml($this->common->grabNote($row, 'endnoteXml'))))
                {
                    $this->pString .= "<notes><style>$output</style></notes>";
                }
            }
            else
            {
                $this->dateFormatTabbed($row, $lineArray, $fieldNameArray);
                $lineArray[array_search('Pages', $fieldNameArray)] = $this->common->pageFormat($row, 'endnoteTabbed');
                $lineArray[array_search('Keywords', $fieldNameArray)] = $this->common->keywordFormat($row, 'endnoteTabbed');
                $lineArray[array_search('Abstract', $fieldNameArray)] = $this->common->grabAbstract($row, 'endnoteTabbed');
                $lineArray[array_search('Notes', $fieldNameArray)] = $this->common->grabNote($row, 'endnoteTabbed');
            }
            // Do we want to export previously saved unused fields?
            if (!empty($rawEntries) && array_key_exists($row['resourceId'], $rawEntries))
            {
                foreach ($rawEntries[$row['resourceId']] as $key => $value)
                {
                    // WIKINDX stores only integer type dates and will reject other types such as 'Spring'. If we're exporting raw entries and a 'Date' field exists in $rawEntries
                    // and there is 'Date' field being used in $lineArray, insert $rawEntries['Date']
                    if ($this->xml)
                    {
                        if (array_key_exists($key, $exportRawFields))
                        {
                            $this->pString .= "<$key><style>" . $this->spCharFormat($value) . "</style></$key>";
                        }
                    }
                    else
                    {
                        if (($key == 'Date') && !$lineArray[array_search('Date', $fieldNameArray)])
                        {
                            $lineArray[array_search('Date', $fieldNameArray)] = $value;
                        }
                        elseif (array_key_exists($key, $exportRawFields))
                        {
                            $lineArray[array_search($exportRawFields[$key], $fieldNameArray)] = $value;
                        }
                    }
                }
            }
            // Check for any custom fields
            if ($this->xml && !empty($this->customMap))
            { // Only done for XML output
                $this->db->formatConditionsOneField(array_keys($this->customMap), 'resourcecustomCustomId');
                $this->db->formatConditions(['resourcecustomResourceId' => $row['resourceId']]);
                $customSet = $this->db->select('resource_custom', ['resourcecustomCustomId', 'resourcecustomShort', 'resourcecustomLong']);
                $custom = '';
                while ($customRow = $this->db->fetchRow($customSet))
                {
                    $field = "custom" . $this->customMap[$customRow['resourcecustomCustomId']];
                    if ($customRow['resourcecustomShort'])
                    {
                        $custom .= "<$field><style>" . $this->spCharFormat(HTML\stripHtml(stripslashes($customRow['resourcecustomShort']))) .
                            "</style></$field>";
                    }
                    elseif ($customRow['resourcecustomLong'])
                    {
                        $custom .= "<$field><style>" . $this->spCharFormat(HTML\stripHtml(stripslashes($customRow['resourcecustomLong']))) .
                            "</style></$field>";
                    }
                }
                $this->pString .= $custom;
            }
            if ($this->xml)
            {
                $this->pString .= "</record>";
            }
            else
            {
                $this->pString .= implode(TAB, $lineArray) . CR;
            }
            // memory usage can be high so write in small chunks
            if ($count >= 100)
            {
                if (!fwrite($this->common->fp, $this->pString))
                {
                    return FALSE;
                }
                $this->pString = '';
                $count = 0;
            }
            $count++;
        }
        if ($this->pString)
        {
            if (!fwrite($this->common->fp, $this->pString))
            {
                return FALSE;
            }
            $this->pString = '';
        }

        return TRUE;
    }
    /**
     * XML headers strings
     *
     * @return bool
     */
    private function xmlHeader()
    {
        return (fwrite($this->common->fp, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><xml><records>') !== FALSE);
    }
    /**
     * XML footer strings
     *
     * @return bool
     */
    private function xmlFooter()
    {
        return (fwrite($this->common->fp, '</records></xml>') !== FALSE);
    }
    /**
     * format names
     *
     * @param mixed $row
     * @param mixed $type
     *
     * @return string
     */
    private function nameFormat($row, $type)
    {
        if ($this->xml)
        {
            $xmlAuthorType = array_search($this->map->{$row['resourceType']}['resource_creator'][$type], $this->map->endnoteXmlFields8);
            if ($xmlAuthorType === FALSE)
            {
                return '';
            }
        }
        if ($type == 1)
        { // author
            $this->authorFound = TRUE;
        }
        $this->db->formatConditions(['resourcecreatorResourceId' => $row['resourceId']]);
        $this->db->formatConditions(['resourcecreatorRole' => $type]);
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $recordset = $this->db->select(
            'resource_creator',
            ['creatorSurname', 'creatorFirstname', 'creatorInitials', 'creatorPrefix', 'creatorId']
        );
        if ($this->db->numRows($recordset))
        {
            $nameArray = [];
            while ($creatorRow = $this->db->fetchRow($recordset))
            {
                if ($this->xml)
                {
                    $name = $this->common->formatName($creatorRow, 'endnoteXml');
                }
                else
                {
                    $name = $this->common->formatName($creatorRow, 'endnoteTabbed');
                }
                if ($name)
                {
                    $mapName[$creatorRow['creatorId']] = $name;
                }
            }
            if (!isset($mapName) || empty($mapName))
            {
                return '';
            }
            if ($this->xml)
            {
                foreach ($mapName as $name)
                {
                    $nameArray[] = "<author><style>" . HTML\stripHtml($name) . "</style></author>";
                }

                return "<$xmlAuthorType>" . implode("", $nameArray) . "</$xmlAuthorType>";
            }
            else
            {
                return implode(CR, $mapName);
            }
        }

        return ''; // We shouldn't get here but just in case...
    }
    /**
     * format date for newspaper/magazine etc. for endnote Tabbed
     *
     * @param mixed $row
     * @param mixed $lineArray
     * @param mixed $fieldNameArray
     */
    private function dateFormatTabbed($row, &$lineArray, $fieldNameArray)
    {
        if (($row['resourceType'] == 'web_article') || ($row['resourceType'] == 'web_site') ||
            ($row['resourceType'] == 'web_encyclopedia') || ($row['resourceType'] == 'web_encyclopedia_article'))
        {
            // Access Year -> 'Volume' and access date -> 'Number'
            $day = $month = FALSE;
            if ($row['resourcemiscField3'])
            {
                $month = $this->monthArray[$row['resourcemiscField3']];
            }
            if ($row['resourcemiscField2'])
            {
                $day = $row['resourcemiscField2'] < 10 ? '0' . $row['resourcemiscField2'] : $row['resourcemiscField2'];
            }
            if ($month)
            {
                $lineArray[array_search('Number', $fieldNameArray)] = $month . ' ' . $day;
            }
            $lineArray[array_search('Volume', $fieldNameArray)] = $row['resourceyearYear1'];
        }
        elseif (isset($row['resourceyearYear1']) && $row['resourceyearYear1'] && $row['resourcemiscField3'])
        {
            $date[] = $this->monthArray[$row['resourcemiscField3']];
            if ($row['resourcemiscField2'])
            {
                $date[] = $row['resourcemiscField2'] < 10 ? '0' . $row['resourcemiscField2'] : $row['resourcemiscField2'];
            }
            $lineArray[array_search('Date', $fieldNameArray)] = implode(' ', $date);
        }
    }
    /**
     * format date for newspaper/magazine etc. for endnote XML
     *
     * web_article Access Year is stored in 'Volume' and Access Date in 'Number'.  Why?
     *
     * @param mixed $row
     *
     * @return false|string
     */
    private function dateFormatXml(&$row)
    {
        $day = $month = FALSE;
        $dates = [];
        if ($row['resourceType'] == 'web_article')
        {
            // Access Year -> 'Volume' and access date -> 'Number'
            if ($row['resourcemiscField3'])
            {
                $month = $this->monthArray[$row['resourcemiscField3']];
                unset($row['resourcemiscField3']);
            }
            if ($row['resourcemiscField2'])
            {
                $day = $row['resourcemiscField2'] < 10 ? '0' . $row['resourcemiscField2'] : $row['resourcemiscField2'];
                unset($row['resourcemiscField2']);
            }
            if ($month)
            {
                $this->pString .= "<number><style>" . $month . ' ' . $day . "</style></number>";
            }
            if (isset($row['resourceyearYear1']) && $row['resourceyearYear1'])
            {
                $dates[] = "<year><style>" . $this->spCharFormat($row['resourceyearYear1']) .
                    "</style></year>";
                unset($row['resourceyearYear1']);
            }
            if (isset($row['resourceyearYear2']) && $row['resourceyearYear2'])
            {
                $this->pString .= "<volume><style>" . $this->spCharFormat($row['resourceyearYear2']) .
                    "</style></volume>";
                unset($row['resourceyearYear2']);
            }
        }
        elseif (isset($row['resourceyearYear1']) && $row['resourceyearYear1'] && $row['resourcemiscField3'])
        {
            $monthEnd = $dayEnd = FALSE;
            $month = $this->monthArray[$row['resourcemiscField3']];
            unset($row['resourcemiscField3']);
            if ($row['resourcemiscField2'])
            { // start Day
                $day = $row['resourcemiscField2'] < 10 ? '0' . $row['resourcemiscField2'] : $row['resourcemiscField2'];
                unset($row['resourcemiscField2']);
                $month .= ' ' . $day;
            }
            if ($row['resourcemiscField6'])
            { // end Month
                $monthEnd = $this->monthArray[$row['resourcemiscField6']];
                unset($row['resourcemiscField6']);
                $month .= ' ' . $monthEnd;
                if ($row['resourcemiscField5'])
                { // end Day
                    $dayEnd = $row['resourcemiscField5'] < 10 ? '0' . $row['resourcemiscField5'] : $row['resourcemiscField5'];
                    unset($row['resourcemiscField5']);
                    $month .= ' ' . $dayEnd;
                }
            }
            if ($month)
            {
                $dates[] = "<pub-dates><date><style>" . $month . "</style></date></pub-dates>";
            }
            $year = $this->spCharFormat($row['resourceyearYear1']);
            if ($row['resourceyearYear3'])
            { // End Year
                $year .= ' ' . $this->spCharFormat($row['resourceyearYear3']);
            }
            $dates[] = "<year><style>" . $year . "</style></year>";
            unset($row['resourceyearYear1']);
        }
        elseif (isset($row['resourceyearYear1']) && $row['resourceyearYear1'])
        {
            $dates[] = "<year><style>" . $this->spCharFormat($row['resourceyearYear1']) . "</style></year>";
            unset($row['resourceyearYear1']);
        }
        if (!empty($dates))
        {
            return "<dates>" . implode('', $dates) . "</dates>";
        }
        else
        {
            return FALSE;
        }
    }
    /**
     * grab any stored data for this resource from WKX_import_raw
     *
     * @param mixed $row
     *
     * @return array|false
     */
    private function raw($row)
    {
        $this->db->formatConditions(['importrawId' => $row['resourceId']]);
        $this->db->formatConditions(['importrawImportType' => 'endnote']);
        $recordset = $this->db->select('import_raw', ['importrawText', 'importrawImportType']);
        if (!$this->db->numRows($recordset))
        {
            return FALSE;
        }
        while ($rawRow = $this->db->fetchRow($recordset))
        {
            $rawEntries = unserialize(base64_decode($rawRow['importrawText']));
        }
        if ($rawEntries)
        {
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
                    $this->rawCitation = trim($entry[1]);
                }
                else
                {
                    $key = trim($entry[0]);
                    $value = trim($entry[1]);
                    $rawEntry[$key] = $value;
                }
            }
        }
        if (isset($rawEntry))
        {
            return $rawEntry;
        }
        else
        {
            return FALSE;
        }
    }
    /**
     * load default arrays
     */
    private function loadArrays()
    {
        // need to use English constants for ENDNOTE
        $constants = FACTORY_CONSTANTS::getFreshInstance(TRUE);
        $constants->convertNumbers();
        $this->editionArray = $constants->cardinalToOrdinalWord();
        $this->monthArray = $constants->monthToLongName();
    }
    /**
     * Format special characters for XML
     *
     * @param mixed $string
     *
     * @return string
     */
    private function spCharFormat($string)
    {
        $match = ["/&/u", '/"/u', "/'/u", "/</u", "/>/u"];
        $replace = ["&amp;", "&quot;", "&apos;", "&lt;", "&gt;"];
        $string = preg_replace($match, $replace, $string);

        return preg_replace("/\\[.*\\]|\\[\\/.*\\]/Uu", "", $string);
    }
    /**
     * failure()
     *
     * @param mixed $error
     */
    private function failure($error)
    {
        GLOBALS::addTplVar('content', $error);
        FACTORY_CLOSE::getInstance();
    }
}
