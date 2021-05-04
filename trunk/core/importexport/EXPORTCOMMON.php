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
 * EXPORT COMMON class
 *
 * Provides methods common to the export scripts.
 */
class EXPORTCOMMON
{
    public $filesDir;
    public $fullFileName = FALSE;
    public $fileName;
    public $context;
    public $fp;
    private $db;
    private $session;
    private $messages;
    private $errors;
    private $cite;
    private $browserTabID = FALSE;

    /*
     * Constructor
     *
     * @param string $outputType
     */
    public function __construct($outputType = 'plain')
    {
        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->cite = FACTORY_CITE::getInstance($outputType);
        $this->filesDir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]);
        $this->browserTabID = GLOBALS::getBrowserTabID();
        // Perform some system admin
        \FILE\tidyFiles();
    }
    /**
     * Create a file name
     *
     * @param string $string
     * @param string $extension
     *
     * @return array
     */
    public function createFileName($string, $extension)
    {
        return list($this->fileName, $this->fullFileName) = FILE\createFileName($this->filesDir, $string, $extension);
    }
    /**
     * Open/create a file
     *
     * @param string $extension
     * @param string $mode – default 'w'
     * @param false|string $string – default FALSE
     *
     * @return bool
     */
    public function openFile($extension, $mode = 'w', $string = FALSE)
    {
        if (!$string)
        {
            $string = \UTILS\uuid();
        }
        $this->context = stream_context_create();
        list($this->fileName, $this->fullFileName) = FILE\createFileName($this->filesDir, $string, $extension);
        $fullFileName = $this->fullFileName;
        if (!$this->fp = fopen($fullFileName, $mode, FALSE, $this->context))
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }
    /**
     * Close a file
     */
    public function closeFile()
    {
        if ($this->fp)
        {
            fclose($this->fp);
        }
    }
    /**
     * Write/append to a file creating it if necessary
     *
     * @param mixed $string
     *
     * @return bool
     */
    public function writeToFile($string)
    {
        if (!$this->fullFileName)
        { // file not yet created and $fp not yet opened
            list($this->fileName, $this->fullFileName) = $this->createFileName($string, '.bib');
            if (!$this->fullFileName)
            {
                return FALSE;
            }
            $fullFileName = $this->fullFileName;
            $this->context = stream_context_create();
            if ($this->fp = fopen($fullFileName, 'w', FALSE, $this->context))
            {
                if (fwrite($this->fp, $string) === FALSE)
                {
                    return FALSE;
                }
            }
            else
            {
                return FALSE;
            }
        }
        elseif (fwrite($this->fp, $string) === FALSE)
        { // appending because $this->fp has not been closed yet
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }
    /**
     * writeFilenameToSession - add filename to session array
     *
     * @param string $fileName
     */
    public function writeFilenameToSession($fileName)
    {
        if ($sessVar = $this->session->getVar("fileExports"))
        {
            $sessArray = $sessVar;
        }
        else
        {
            $sessArray = [];
        }
        if (array_search($fileName, $sessArray) === FALSE)
        {
            $sessArray[] = $fileName;
            $this->session->setVar("fileExports", $sessArray);
        }
    }
    /*
     * get the SQL either from the basket or from the last view
     *
     * @return string
     */
    public function getSQL()
    {
        $stmt = FACTORY_SQLSTATEMENTS::getInstance();
        if ($this->session->getVar("exportBasket"))
        {
        	if (!$tempAllIds = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'list_AllIds')) {
	            $tempAllIds = $this->session->getVar("list_AllIds");
	        }
        	if (!$tempListStmt = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'sql_ListStmt')) {
	            $tempListStmt = $this->session->getVar("sql_ListStmt");
	        }
        	if (!$basket = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, 'basket_List')) {
	            $basket = $this->session->getVar("basket_List");
	        }
            $this->session->setVar("list_AllIds", $basket);
            if ($this->browserTabID) {
            	\TEMPSTORAGE\store($this->db, $this->browserTabID, ['basket_List' => $basket]);
            }
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "modules", "basket", "BASKET.php"]));
            $basket = new BASKET();
            $sqlEncoded = base64_encode(serialize([$basket->returnBasketSql(FALSE, 'creator')]));
            $this->session->setVar("list_AllIds", $tempAllIds);
            $this->session->setVar("sql_ListStmt", $tempListStmt);
            if ($this->browserTabID) {
            	\TEMPSTORAGE\store($this->db, $this->browserTabID, ['list_AllIds' => $tempAllIds, 'sql_ListStmt' => $tempListStmt]);
            }

            return $sqlEncoded;
        }
        else
        {
            $sql = $stmt->getExportSql();

            return $sql;
        }
    }
    /*
     * Check if there any custom fields in the database and provide options to map these to bibtex fields
     *
     * @param $export Export type (currently 'bibtex' – DEFAULT and 'endnote')
     * @return string
     */
    public function getCustomFields($export = 'bibtex')
    {
        if ($this->db->tableIsEmpty('resource_custom'))
        {
            return FALSE;
        }
        $this->db->leftJoin('resource_custom', 'resourcecustomCustomId', 'customId');
        $recordset = $this->db->select('custom', ['resourcecustomCustomId', 'customLabel']);
        while ($row = $this->db->fetchRow($recordset))
        {
            if ($row['resourcecustomCustomId'])
            {
                $customLabels[$row['resourcecustomCustomId']] = stripslashes($row['customLabel']);
            }
        }
        if (!isset($customLabels) || empty($customLabels))
        {
            return FALSE;
        }
        $customLabels = array_unique($customLabels);
        if ($export == 'bibtex')
        {
            $pString = HTML\p(HTML\strong($this->messages->text("misc", "customFieldMap")) .
                ' ' . $this->messages->text("misc", "customFieldMap2"));
            foreach ($customLabels as $id => $label)
            {
                $text = $this->session->getVar("export_Map_$id");
                $pString .= HTML\p(FORM\textInput($label, "Map_$id", $text));
            }
        }
        elseif ($export == 'endnote')
        {
            $pString = HTML\p(HTML\strong($this->messages->text("misc", "customFieldMap")));
            $mapArray = [
                "0" => $this->messages->text('misc', 'ignore'),
                "1" => "Custom 1",
                "2" => "Custom 2",
                "3" => "Custom 3",
                "4" => "Custom 4",
                "5" => "Custom 5",
                "6" => "Custom 6",
                "7" => "Custom 7",
            ];
            $keys = array_keys($mapArray);
            foreach ($customLabels as $id => $label)
            {
                $key = array_shift($keys);
                $selected = $this->session->getVar("exportMapInternal_" . $id);
                if ($selected === FALSE)
                {
                    $selected = $key;
                }
                $select = \FORM\selectedBoxValue(
                    FALSE,
                    "MapInternal_$id",
                    $mapArray,
                    $selected,
                    4
                );
                $pString .= HTML\p($label . '&nbsp;&nbsp;' . $select);
            }
        }
        else
        {
            return FALSE;
        }

        return $pString;
    }
    /**
     * formatName - format one name depending on the export type
     *
     * @param assocArray $creatorRow
     * @param string $exportType
     *
     * @return string
     */
    public function formatName($creatorRow, $exportType)
    {
        $surname = $firstname = $initials = '';
        // WIKINDX stores Jr., IV etc. at end of surname...
        if ($creatorRow['creatorSurname'])
        {
            if ($creatorRow['creatorPrefix'])
            {
                $surname = stripslashes($creatorRow['creatorPrefix']) . " " .
                stripslashes($creatorRow['creatorSurname']);
            }
            else
            {
                $surname = stripslashes($creatorRow['creatorSurname']);
            }
        }
        if ($creatorRow['creatorFirstname'])
        {
            $firstname = stripslashes($creatorRow['creatorFirstname']);
        }
        if ($creatorRow['creatorInitials'])
        {
            if (($exportType == 'endnoteTabbed') || ($exportType == 'endnoteXml'))
            {
                $initials = implode(' ', \UTF8\mb_explode(' ', stripslashes($creatorRow['creatorInitials'])));
            }
            elseif ($exportType == 'ris')
            {
                $initials = implode('.', \UTF8\mb_explode(' ', stripslashes($creatorRow['creatorInitials']))) . ".";
            }
            elseif ($exportType == 'bibtex')
            {
                $initials = implode('. ', \UTF8\mb_explode(' ', stripslashes($creatorRow['creatorInitials']))) . ".";
            }
        }
        if ($exportType == 'ris')
        {
            if ($firstname && $initials)
            {
                return $surname . ',' . $firstname . ',' . $initials;
            }
            elseif ($firstname)
            {
                return $surname . ',' . $firstname;
            }
            elseif ($initials)
            {
                return $surname . ',' . $initials;
            }
        }
        elseif (($exportType == 'endnoteTabbed') || ($exportType == 'endnoteXml'))
        {
            if ($firstname && $initials)
            {
                return $surname . ',' . $firstname . ' ' . $initials;
            }
            elseif ($firstname)
            {
                return $surname . ',' . $firstname;
            }
            elseif ($initials)
            {
                return $surname . ',' . $initials;
            }
        }
        elseif ($exportType == 'bibtex')
        {
            if (preg_match("/(.*)(Sr\\.|jr\\.)/ui", $surname, $matches))
            {
                $surname = trim($matches[1]) . ", " . trim($matches[2]);
            }
            if (preg_match("/(.*)\\s(I|II|III|IV|V|VI|VII|VIII|IX|X)$/u", $surname, $matches))
            {
                $surname = trim($matches[1]) . ", " . trim($matches[2]);
            }
            if ($firstname && $initials)
            {
                return $surname . ", " . $firstname . ' ' . $initials;
            }
            elseif ($firstname)
            {
                return $surname . ", " . $firstname;
            }
            elseif ($initials)
            {
                return $surname . ", " . $initials;
            }
        }

        return $surname; // if all else fails
    }
    /**
     * titleFormat - format and return the resource title from the supplied SQL $row
     *
     * @param assocArray $row
     * @param bool $bibtex
     *
     * @return string
     */
    public function titleFormat($row, $bibtex = FALSE)
    {
        // For book_chapter, 'title' is bibtex 'chapter' and 'collectionTitle' is bibtex 'title'
        if ($bibtex && ($row['resourceType'] == 'book_chapter'))
        {
            return stripslashes($row['collectionTitle']);
        }
        $noSort = $row['resourceNoSort'] ? stripslashes($row['resourceNoSort']) . ' ' : FALSE;
        if ($row['resourceSubtitle'])
        {
            $string = $noSort . stripslashes($row['resourceTitle']) . ": " . stripslashes($row['resourceSubtitle']);
        }
        else
        {
            $string = $noSort . stripslashes($row['resourceTitle']);
        }
        if ($bibtex)
        {
            return $string;
        }
        // If !bibtex, remove any braces that have been inserted to maintain case of characters - only required for resource title
        return preg_replace("/[{|}]/u", "", $string);
    }
    /**
     * grabNote - grab note from resource_note and strip (optionally) multiple whitespace
     *
     * @param assocArray $row
     * @param bool $exportType
     *
     * @return string
     */
    public function grabNote($row, $exportType)
    {
        if ($row['resourcetextNote'])
        {
            if (($exportType == 'ris') || ($exportType == 'endnoteTabbed'))
            {
                $text = $this->cite->parseCitations(stripslashes($row['resourcetextNote']), $exportType, FALSE);
            }
            else
            {
                $text = stripslashes($row['resourcetextNote']);
            }
            // replace all whitespace (TABS, CR, \n etc.) with single space.
            if (($exportType == 'ris') || ($exportType == 'endnoteTabbed') || ($exportType == 'endnoteXml'))
            {
                return preg_replace("/\\s/u", " ", $text);
            }
            // For bibtex, ensure first letter is capitalized
            if ($exportType == 'bibtex')
            {
                return \UTF8\mb_ucfirst($text);
            }

            return $text;
        }

        return '';
    }
    /**
     * grabAbstract - abstract from resource_abstract and strip (optionally) multiple whitespace
     *
     * @param assocArray $row
     * @param bool $exportType
     *
     * @return string
     */
    public function grabAbstract($row, $exportType)
    {
        if ($row['resourcetextAbstract'])
        {
            if (($exportType == 'ris') || ($exportType == 'endnoteTabbed'))
            {
                $text = $this->cite->parseCitations(stripslashes($row['resourcetextAbstract']), $exportType, FALSE);
            }
            else
            {
                $text = stripslashes($row['resourcetextAbstract']);
            }
            // replace all whitespace (TABS, CR, \n etc.) with single space.
            if (($exportType == 'ris') || ($exportType == 'endnoteTabbed') || ($exportType == 'endnoteXml'))
            {
                return preg_replace("/\\s/u", " ", $text);
            }
            // For bibtex, ensure first letter is capitalized
            if ($exportType == 'bibtex')
            {
                return \UTF8\mb_ucfirst($text);
            }

            return $text;
        }

        return '';
    }
    /**
     * pageFormat - return formatted pageStart and pageEnd with different delimiters
     *
     * @param assocArray $row
     * @param string $exportType
     *
     * @return string
     */
    public function pageFormat($row, $exportType)
    {
        $page = FALSE;
        if ($row['resourcepagePageStart'])
        {
            if (($exportType == 'endnoteTabbed') || ($exportType == 'endnoteXml'))
            {
                $page = stripslashes($row['resourcepagePageStart']);
            }
            elseif ($exportType == 'ris')
            {
                $page = 'SP  - ' . stripslashes($row['resourcepagePageStart']);
            }
            elseif ($exportType == 'bibtex')
            {
                $page = stripslashes($row['resourcepagePageStart']);
            }
        }
        if ($row['resourcepagePageEnd'])
        {
            if (($exportType == 'endnoteTabbed') || ($exportType == 'endnoteXml'))
            {
                $page .= '-' . stripslashes($row['resourcepagePageEnd']);
            }
            elseif ($exportType == 'ris')
            {
                $page .= CR . 'EP  - ' . stripslashes($row['resourcepagePageEnd']);
            }
            elseif ($exportType == 'bibtex')
            {
                $page .= '--' . stripslashes($row['resourcepagePageEnd']);
            }
        }
        if ($page)
        {
            return $page;
        }

        return '';
    }
    /**
     * keywordFormat - return formatted keywords with different delimiters
     *
     * @param assocArray $row
     * @param string $exportType
     *
     * @return string
     */
    public function keywordFormat($row, $exportType)
    {
        $this->db->formatConditions(['resourcekeywordResourceId' => $row['resourceId']]);
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
        $recordset = $this->db->select('resource_keyword', 'keywordKeyword');
        if ($this->db->numRows($recordset))
        {
            while ($kw = $this->db->fetchRow($recordset))
            {
                $k[] = stripslashes($kw['keywordKeyword']);
            }
            if ($exportType == 'endnoteTabbed')
            { // tabbed file
                return implode(";", $k);
            }
            elseif ($exportType == 'endnoteXml')
            { // XML
                return $k;
            }
            elseif ($exportType == 'ris')
            {
                // asterisk (character 42) is not allowed in the author, keywords, or periodical name fields - replace with '#'
                foreach ($k as $key => $value)
                {
                    $k[$key] = 'KW  - ' . preg_replace("/\\*/u", "#", $value);
                }

                return implode(CR, $k);
            }
            elseif ($exportType == 'bibtex')
            {
                return implode(",", $k);
            }
        }

        return '';
    }
    /**
     * Format resource according to bibliographic style when exporting to a formatted export format (HTML or RTF for example)
     *
     * @param array $rows
     * @param string $exportType
     * @param string $param1 Option parameter (default is FALSE)
     * @param string $param2 Option parameter (default is FALSE)
     * @param string $writeToFile Option write each row to file rather than returning a concatenated string (default is FALSE)
     *
     * @return bool|string (TRUE = success writing to file, FALSE = failure writing to file)
     */
    public function formatResources(&$rows, $exportType, $param1 = FALSE, $param2 = FALSE, $writeToFile = FALSE)
    {
        $bibStyle = FACTORY_BIBSTYLE::getInstance($exportType);
        $creators = [];
        $pString = '';
        $resultSet = $this->getCreators(array_keys($rows));
        while ($cRow = $this->db->fetchRow($resultSet))
        {
            $creators[$cRow['resourcecreatorResourceId']][$cRow['resourcecreatorRole']][] = $cRow['creatorId'];
            $array = [
                'surname' => $cRow['surname'],
                'firstname' => $cRow['firstname'],
                'initials' => $cRow['initials'],
                'prefix' => $cRow['prefix'],
                'creatorId' => $cRow['creatorId'],
            ];
            $bibStyle->creators[$cRow['creatorId']] = array_map([$bibStyle, "removeSlashes"], $array);
        }
        unset($cRow);
        if ($exportType == 'html')
        {
            foreach ($rows as $rId => $row)
            {
                if (empty($creators) || !array_key_exists($rId, $creators) || empty($creators[$rId]))
                {
                    for ($index = 1; $index <= 5; $index++)
                    {
                        $row["creator$index"] = ''; // need empty fields for BIBSTYLE
                    }
                }
                else
                {
                    for ($index = 1; $index <= 5; $index++)
                    {
                        if (array_key_exists($index, $creators[$rId]))
                        {
                            $row["creator$index"] = implode(',', $creators[$rId][$index]);
                        }
                        else
                        {
                            $row["creator$index"] = '';
                        }
                    }
                }
                $hyperlink = $param2 ? "&nbsp;&nbsp;" . HTML\a('link', "[$param1]", $param2 . $rId) : $param2;
                $string = HTML\p($bibStyle->process($row, FALSE, FALSE) . $hyperlink) . LF;
                if ($writeToFile)
                {
                    if (!$this->fp || !fwrite($this->fp, $string))
                    {
                        return FALSE;
                    }
                }
                else
                {
                    $pString .= $string;
                }
            }
        }
        if ($pString)
        {
            return $pString;
        }
        else
        {
            return TRUE;
        }
    }
    /**
     * set user/group ID conditions for ideas
     *
     * @return bool
     */
    public function setIdeasCondition()
    {
        if ($userId = $this->session->getVar("setup_UserId"))
        {
            $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
            $this->db->formatConditions($this->db->formatFields('usergroupsusersGroupId') . $this->db->equal .
                $this->db->formatFields('resourcemetadataPrivate'));
            $subSql = $this->db->selectNoExecute('user_groups_users', 'usergroupsusersId', FALSE, TRUE, TRUE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('N')
                . $this->db->and .
                $this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('Y');
            $case1 = $this->db->caseWhen($subject, FALSE, $subSql, FALSE, FALSE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('Y');
            $result = $this->db->formatFields('resourcemetadataAddUserId') . $this->db->equal . $this->db->tidyInput($userId);
            $case2 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('N');
            $result = $this->db->tidyInput(1);
            $case3 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
            $this->db->formatConditions($case1 . $this->db->or . $case2 . $this->db->or . $case3);
            $this->db->formatConditions(['resourcemetadataType' => 'i']);

            return TRUE;
        }

        return FALSE;
    }
    /**
     * Get SQL resultset for creator details before formatting resources
     *
     * @param array $resourceIds
     *
     * @return object SQL resultset
     */
    private function getCreators($resourceIds)
    {
        $this->db->formatConditionsOneField($resourceIds, 'resourcecreatorResourceId');
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->orderBy('resourcecreatorResourceId', TRUE, FALSE);
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourcecreatorRole', TRUE, FALSE);
        $this->db->orderBy('resourcecreatorOrder', TRUE, FALSE);

        return $this->db->select('resource_creator', ['resourcecreatorResourceId', ['creatorSurname' => 'surname'],
            ['creatorFirstname' => 'firstname'], ['creatorInitials' => 'initials'], ['creatorPrefix' => 'prefix'],
            'creatorId', 'resourcecreatorRole', ]);
    }
}
