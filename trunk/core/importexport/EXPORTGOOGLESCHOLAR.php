<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * Extends EXPORTER
 */
require_once("core/importexport/EXPORTER.php");

/**
 * Make WIKINDX resources available to Google Scholar
 *
 * @package wikindx\core\importexport
 */
class EXPORTGOOGLESCHOLAR extends EXPORTER
{
    /** object */
    protected $db;
    /** object */
    protected $map;
    /** object */
    private $session;
    /** boolean */
    private $noGs = FALSE;

    /**
     *	EXPORTGOOGLESCHOLAR
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        include_once("core/importexport/GOOGLESCHOLARMAP.php");
        $this->map = new GOOGLESCHOLARMAP();
    }
    /**
     * Display googlescholar data for one resource
     *
     * @param array $row
     * @param array $creators
     *
     * @return string
     */
    public function export($row, $creators)
    {
        $this->entry = $this->authors = [];
        $entry = FALSE;
        global $_SERVER;
        if ($attach = $this->attachedFiles($row['resourceId']))
        {
            $entry .= '<meta name="citation_pdf_url" content="' . WIKINDX_BASE_URL . '/' . $attach . '">';
        }
        if ($this->noGs)
        {
            return FALSE;
        }
        $this->creators = $creators;
        $this->getData($row);
        if (!empty($this->entry))
        {
            $entry .= $this->convertEntry();
        }
        if (!empty($this->authors))
        {
            $entry .= $this->convertEntryAuthors();
        }
        if ($publisher = $this->publisher($row))
        {
            $entry .= '<meta name="citation_publisher" content="' . $this->uEncode($publisher) . '">';
        }
        if (array_key_exists('resourceDoi', $row) && $row['resourceDoi'])
        {
            $entry .= '<meta name="citation_doi" content="' . $this->uEncode(str_replace('doi:', '', $row['resourceDoi'])) . '">';
        }
        $entry .= $this->keywords($row);
        $entry .= $this->languages($row);

        return $entry;
    }

    /**
     * Convert raw array of data to appropriate google scholar format
     *
     * @return string
     */
    protected function convertEntry()
    {
        $array = [];
        foreach ($this->entry as $key => $value)
        {
            if ($key == 'date')
            {
                $array[] = '<meta name="citation_publication_date" content="' . str_replace('-', '/', $value) . '">';

                continue;
            }
            $array[] = '<meta name="' . $key . '" content="' . $value . '">';
        }

        return implode('', $array);
    }
    /**
     * Convert raw array of authors to appropriate google scholar format
     *
     * @return string
     */
    protected function convertEntryAuthors()
    {
        $array = [];
        foreach ($this->authors as $value)
        {
            $array[] = '<meta name="citation_author" content="' . $this->uEncode($value) . '">';
        }

        return implode('', $array);
    }
    /**
     * URL encode
     *
     * @param string $element
     *
     * @return string
     */
    protected function uEncode($element)
    {
        return htmlentities($element, ENT_QUOTES, "UTF-8");
    }
    /**
     * Make attached files available
     *
     * @param int $resourceId
     *
     * @return string|FALSE
     */
    private function attachedFiles($resourceId)
    {
        // Are only logged on users allowed to view this file?
        if (WIKINDX_FILE_VIEW_LOGGEDON_ONLY)
        {
            return FALSE;
        }
        $attach = FACTORY_ATTACHMENT::getInstance();
        $this->db->formatConditions(['resourceattachmentsResourceId' => $resourceId]);
        $this->db->orderBy('resourceattachmentsFilename');
        $recordset = $this->db->select(
            'resource_attachments',
            ['resourceattachmentsId', 'resourceattachmentsHashFilename', 'resourceattachmentsFileName',
                'resourceattachmentsPrimary', 'resourceattachmentsEmbargo', ]
        );
        if (!$this->db->numRows($recordset))
        {
            if (WIKINDX_GS_ATTACHMENT)
            {
                $this->noGs = TRUE;
            }

            return FALSE;
        }
        $multiple = $this->db->numRows($recordset) > 1 ? TRUE : FALSE;
        $primary = FALSE;
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$this->session->getVar("setup_Superadmin") && ($row['resourceattachmentsEmbargo'] == 'Y'))
            {
                continue;
            }
            if ($multiple && ($row['resourceattachmentsPrimary'] == 'Y'))
            {
                $primary = $attach->makeLink($row, $multiple, FALSE, FALSE);

                break;
            }
            elseif (!$multiple)
            {
                $primary = $attach->makeLink($row, $multiple, FALSE, FALSE);
            }
        }

        return $primary;
    }
    /**
     * Get resource publisher
     *
     * @param array $row
     *
     * @return string|FALSE
     */
    private function publisher($row)
    {
        if (!$row['publisherName'] && !$row['publisherLocation'])
        {
            return FALSE;
        }

        return $row['publisherLocation'] ? $row['publisherName'] .
            ': ' . $row['publisherLocation'] : $row['publisherName'];
    }
    /**
     * Get resource keywords
     *
     * @param array $row
     *
     * @return string|FALSE
     */
    private function keywords($row)
    {
        $rId = $row['resourceId'];
        $this->db->formatConditions(['resourcekeywordResourceId' => $rId]);
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
        $this->db->orderBy('keywordKeyword');
        $resultset = $this->db->select('resource_keyword', ['resourcekeywordKeywordId', 'keywordKeyword']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $array[] = '<meta name="citation_keyword" content="' . $this->uEncode($row['keywordKeyword']) . '">';
        }
        if (!isset($array))
        {
            return FALSE;
        }

        return implode('', $array);
    }
    /**
     * get resource languages
     *
     * @param array $row
     *
     * @return string|FALSE
     */
    private function languages($row)
    {
        $rId = $row['resourceId'];
        $this->db->formatConditions(['resourcelanguageResourceId' => $rId]);
        $this->db->leftJoin('language', 'languageId', 'resourcelanguageLanguageId');
        $this->db->orderBy('languageLanguage');
        $resultset = $this->db->select('resource_language', ['resourcelanguageLanguageId', 'languageLanguage']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $array[] = '<meta name="citation_language" content="' . $this->uEncode($row['languageLanguage']) . '">';
        }
        if (!isset($array))
        {
            return FALSE;
        }

        return implode('', $array);
    }
}
