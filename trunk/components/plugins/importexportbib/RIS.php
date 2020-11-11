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
 *	RIS export class
 */
class RIS
{
    private $db;
    private $vars;
    private $session;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $common;
    private $map;
    private $parentClass;

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
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "EXPORTCOMMON.php"]));
        $this->common = new EXPORTCOMMON();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "RISMAP.php"]));
        $this->map = new RISMAP();
    }
    /*
     * Write export to file
     */
    public function process()
    {
        $sql = $this->common->getSQL();
        if (!$sql)
        {
            $this->failure(HTML\p($this->pluginmessages->text("noList"), 'error'));
        }
        if (!$this->common->openFile(FALSE, '.ris'))
        {
            $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
        }
        $sqlArray = unserialize(base64_decode($sql));

        foreach ($sqlArray as $sql)
        {
            $recordset = $this->db->query($sql);
            if (!$this->getData($recordset))
            {
                $this->failure($this->errors->text("file", "write", ": " . $this->common->fileName));
            }
        }
        if ($this->common->fullFileName)
        {
            fclose($this->common->fp);
        }
        $pString = HTML\p($this->pluginmessages->text('exported') . ": " . $this->common->fileName, 'success');
        $this->common->writeFilenameToSession($this->common->fileName);
        $this->parentClass->listFiles($pString, 'initRisExport');
    }
    /*
     * get data from database
     *
     * @param object $recordset
     *
     * @return bool
     */
    private function getData($recordset)
    {
        $resourceIds = $entryArray = $rowTypes = [];
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$row['resourceId'])
            { // not sure why, but sometimes $row is empty.
                continue;
            }
            if (array_search($row['resourceId'], $resourceIds) === FALSE)
            {
                $resourceIds[] = $row['resourceId'];
            }
            else
            {
                continue;
            }
            $rowTypes[$row['resourceId']]['resourceType'] = $row['resourceType'];
            // Do we need to switch `year1` (publicationYear) and `year2` (reprintYear)?
            if ((($row['resourceType'] == 'book') || ($row['resourceType'] == 'book_article'))
                && $row['resourceyearYear1'] && $row['resourceyearYear2'])
            {
                $row['resourceyearYear1'] = $row['resourceyearYear2'];
            }
            // else, always use `year2` in preference to `year1` except for web_article, database,
            // proceedings and proceedings_article
            elseif ($row['resourceyearYear2'] && ($row['resourceType'] != 'web_article') &&
            ($row['resourceType'] != 'web_site') && ($row['resourceType'] != 'web_encyclopedia') &&
            ($row['resourceType'] != 'web_encyclopedia_article') &&
            ($row['resourceType'] != 'proceedings_article') && ($row['resourceType'] != 'proceedings') &&
            ($row['resourceType'] != 'database'))
            {
                $row['resourceyearYear1'] = $row['resourceyearYear2'];
                unset($row['resourceyearYear2']);
            }
            $entryArray[$row['resourceId']][] = 'TY  - ' . $this->map->types[$row['resourceType']];
            $entryArray[$row['resourceId']][] = 'T1  - ' . $this->common->titleFormat($row);
            foreach ($this->map->{$row['resourceType']} as $table => $tableArray)
            {
                if ($table == 'resource_creator')
                {
                    continue;
                }
                foreach ($tableArray as $wkField => $risField)
                {
                    $wkField = str_replace('_', '', $table) . ucfirst($wkField);
                    if (array_key_exists($wkField, $row) && $row[$wkField])
                    {
                        // asterisk (character 42) is not allowed in the author, keywords, or periodical name fields - replace with '#'
                        if ($risField == 'JF')
                        {
                            $entryArray[$row['resourceId']][] = $risField . '  - ' . preg_replace("/\\*/u", "#", stripslashes($row[$wkField]));
                        }
                        elseif (($risField == 'UR') && (($row['resourceType'] == 'web_article') ||
                            ($row['resourceType'] == 'web_site') || ($row['resourceType'] == 'web_encyclopedia') ||
                            ($row['resourceType'] == 'web_encyclopedia_article') ||
                            ($row['resourceType'] == 'database')) &&
                            ($item = $this->webFormat($row)))
                        {
                            $entryArray[$row['resourceId']][] = $item;
                        }
                        else
                        {
                            if ($risField == 'UR')
                            {
                                $tmp = base64_decode($row['resourcetextUrls']);
                                $tmp = unserialize($tmp);
                                $tmp = array_values($tmp);
                                $tmp = implode(';', $tmp);
                            }
                            else
                            {
                                $tmp = stripslashes($row[$wkField]);
                            }

                            $entryArray[$row['resourceId']][] = $risField . '  - ' . $tmp;
                        }
                    }
                }
            }
            if ($item = $this->year1Format($row))
            {
                $entryArray[$row['resourceId']][] = 'PY  - ' . $item;
            }
            if (isset($row['resourceyearYear2']) && $row['resourceyearYear2'] && (($row['resourceType'] == 'proceedings') ||
                ($row['resourceType'] == 'proceedings_article')) && ($item = $this->year2Format($row)))
            {
                $entryArray[$row['resourceId']][] = $item;
            }
            if ($row['resourcemiscField4'] && (($row['resourceType'] == 'film') || ($row['resourceType'] == 'broadcast'))
                && ($item = $this->timeFormat($row)))
            {
                $entryArray[$row['resourceId']][] = $item;
            }
            // RefMan doesn't like pages on some types
            if (($row['resourceType'] != 'hearing') && ($row['resourceType'] != 'unpublished'))
            {
                if ($item = $this->common->pageFormat($row, 'ris'))
                {
                    $entryArray[$row['resourceId']][] = $item;
                }
            }
        }
        // Get creators
        $this->grabNames($rowTypes, $entryArray, $resourceIds);
        // Get notes and abstracts
        $this->grabNoteAbstract($entryArray, $resourceIds);
        // Get keywords
        $this->grabKeywords($entryArray, $resourceIds);
        // Write entries to file
        foreach ($entryArray as $array)
        {
            if ($this->common->fp)
            {
                if (!fwrite($this->common->fp, implode(CR, $array) . CR . "ER  - " . CR . CR))
                {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }
    /*
     * grabNames
     *
     * @param array $rowTypes
     * @param array $entryArray
     * @param array $rIds
     */
    private function grabNames(&$rowTypes, &$entryArray, $rIds)
    {
        $mapName = [];
        $this->db->formatConditionsOneField($rIds, 'resourcecreatorResourceId');
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->orderBy('resourcecreatorResourceId', TRUE, FALSE);
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourcecreatorRole', TRUE, FALSE);
        $this->db->orderBy('resourcecreatorOrder', TRUE, FALSE);
        $resultSet = $this->db->select('resource_creator', ['resourcecreatorResourceId', 'creatorSurname',
            'creatorFirstname', 'creatorInitials', 'creatorPrefix', 'resourcecreatorRole', ]);
        while ($row = $this->db->fetchRow($resultSet))
        {
            $wndxField = 'creator' . $row['resourcecreatorRole'];
            if (!array_key_exists($wndxField, $this->map->{$rowTypes[$row['resourcecreatorResourceId']]['resourceType']}['resource_creator']))
            {
                continue;
            }
            $risField = $this->map->{$rowTypes[$row['resourcecreatorResourceId']]['resourceType']}['resource_creator'][$wndxField];
            $name = $this->common->formatName($row, 'ris');
            if ($name)
            {
                $mapName[$row['resourcecreatorResourceId']][] = $risField . '  - ' . preg_replace("/\\*/u", "#", $name);
            }
        }
        foreach ($rIds as $rId)
        {
            if (array_key_exists($rId, $mapName))
            {
                $entryArray[$rId][] = implode(CR, $mapName[$rId]);
            }
        }
    }
    /*
     * web_article,  URL and accessed date
     *
     * @param array $row
     *
     * @return string
     */
    private function webFormat($row)
    {
        $url = $year = $month = $day = FALSE;
        $tmp = base64_decode($row['resourcetextUrls']);
        $tmp = unserialize($tmp);
        $tmp = array_values($tmp);
        $tmp = implode(';', $tmp);
        $url = "L2  - " . $tmp;
        if (array_key_exists('resourceyearYear2', $row) && $row['resourceyearYear2'])
        {
            $year = stripslashes($row['resourceyearYear2']);
        }
        if ($row['resourcemiscField3'])
        {
            $month = $row['resourcemiscField3'] < 10 ? '0' . $row['resourcemiscField3'] : $row['resourcemiscField3'];
        }
        if ($row['resourcemiscField2'])
        {
            $day = $row['resourcemiscField2'] < 10 ? '0' . $row['resourcemiscField2'] : $row['resourcemiscField2'];
        }

        return $url . CR . "Y2  - " . $year . '/' . $month . '/' . $day . '/';
    }
    /*
     * format YYYY/MM/DD of conference dates etc
     *
     * @param array $row
     *
     * @return string
     */
    private function year2Format($row)
    {
        $year = $month = $day = FALSE;
        $year = stripslashes($row['resourceyearYear2']);
        if ($row['resourcemiscField3'])
        {
            $month = $row['resourcemiscField3'] < 10 ? '0' . $row['resourcemiscField3'] : $row['resourcemiscField3'];
        }
        if ($row['resourcemiscField2'])
        {
            $day = $row['resourcemiscField2'] < 10 ? '0' . $row['resourcemiscField2'] : $row['resourcemiscField2'];
        }

        return "Y2  - " . $year . '/' . $month . '/' . $day . '/';
    }
    /*
     * format YYYY/MM/DD of publication
     *
     * @param array $row
     *
     * @return string
     */
    private function year1Format($row)
    {
        $year = $month = $day = FALSE;
        if ($row['resourceyearYear1'])
        {
            $year = stripslashes($row['resourceyearYear1']);
        }
        if (($row['resourceType'] != 'web_article') && ($row['resourceType'] != 'web_site') &&
        ($row['resourceType'] != 'web_encyclopedia') &&
        ($row['resourceType'] != 'web_encyclopedia_article') && ($row['resourceType'] != 'proceedings_article') &&
            ($row['resourceType'] != 'proceedings') && ($row['resourceType'] != 'database'))
        {
            if ($row['resourcemiscField3'])
            {
                $month = $row['resourcemiscField3'] < 10 ? '0' . $row['resourcemiscField3'] : $row['resourcemiscField3'];
            }
            if ($row['resourcemiscField2'])
            {
                $day = $row['resourcemiscField2'] < 10 ? '0' . $row['resourcemiscField2'] : $row['resourcemiscField2'];
            }
        }

        return $year . '/' . $month . '/' . $day . '/';
    }
    /*
     * format running time for films
     *
     * @param array $row
     *
     * @return string
     */
    private function timeFormat($row)
    {
        $hours = $minutes = FALSE;
        $hours = stripslashes($row['resourcemiscField4']);
        if ($row['resourcemiscField1'])
        {
            $minutes = $row['resourcemiscField1'] < 10 ? '0' . $row['resourcemiscField1'] : $row['resourcemiscField1'];
        }

        return "M2  - " . $hours . "'" . $minutes . "\"";
    }
    /*
     * grabNoteAbstract
     *
     * @param array $entryArray
     * @param array $rIds
     */
    private function grabNoteAbstract(&$entryArray, $rIds)
    {
        $this->db->formatConditionsOneField($rIds, 'resourcetextId');
        $resultSet = $this->db->select('resource_text', ['resourcetextId', 'resourcetextNote', 'resourcetextAbstract']);
        while ($row = $this->db->fetchRow($resultSet))
        {
            if ($row['resourcetextNote'])
            {
                $entryArray[$row['resourcetextId']][] = 'N1  - ' . $this->common->grabNote($row, 'ris');
            }
            if ($row['resourcetextAbstract'])
            {
                $entryArray[$row['resourcetextId']][] = 'N2  - ' . $this->common->grabAbstract($row, 'ris');
            }
        }
    }
    /*
     * grabKeywords
     *
     * @param array $entryArray
     * @param array $rIds
     */
    private function grabKeywords(&$entryArray, $rIds)
    {
        $kws = [];
        $this->db->formatConditionsOneField($rIds, 'resourcekeywordResourceId');
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
        $recordset = $this->db->select('resource_keyword', ['resourcekeywordResourceId', 'keywordKeyword']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $kws[$row['resourcekeywordResourceId']][] = 'KW  - ' . preg_replace("/\\*/u", "#", $row['keywordKeyword']);
        }
        foreach ($kws as $rId => $kwArray)
        {
            $entryArray[$rId][] = implode(CR, $kwArray);
        }
    }
    /*
     * failure
     *
     * @param string $error
     */
    private function failure($error)
    {
        GLOBALS::addTplVar('content', $error);
        FACTORY_CLOSE::getInstance();
    }
}
