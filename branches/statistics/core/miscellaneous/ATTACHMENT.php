<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * ATTACHMENT
 *
 * Handle attachments
 *
 * @package wikindx\core\miscellaneous
 */
class ATTACHMENT
{
    /** string */
    public $primary = FALSE;
    /** object */
    private $db;
    /** array */
    private $vars;

    /**
     * ATTACHMENT
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
    }
    /**
     * Make hyperlink of attachment
     *
     * @param array $row
     * @param int $resourceId
     * @param bool $list
     * @param bool $reduce
     * @param bool $hyperlink
     *
     * @return string
     */
    public function makeLink($row, $resourceId = FALSE, $list = FALSE, $reduce = TRUE, $hyperlink = TRUE)
    {
        $id = $row['resourceattachmentsId'];
        $icons = FACTORY_LOADICONS::getInstance();
        $session = FACTORY_SESSION::getInstance();

        if (!$list)
        {
            if (!$hyperlink)
            {
                return "index.php?action=attachments_ATTACHMENTS_CORE" .
                    htmlentities("&method=downloadAttachment&id=$id&resourceId=$resourceId&filename=" . 
                    $row['resourceattachmentsHashFilename']);
            }
            else
            {
                if (array_key_exists('resourceattachmentsFileName', $row) && !empty($row['resourceattachmentsFileName']))
                {
                    $name = \HTML\nlToHtml($row['resourceattachmentsFileName']) . " " .
                    $icons->getIconForAFileExtension($row['resourceattachmentsFileName']);
                }
                else
                {
                    $name = $icons->getHTML("file");
                }

                return \HTML\a($icons->getClass("file"), $name, "index.php?action=attachments_ATTACHMENTS_CORE" .
                    htmlentities("&method=downloadAttachment&id=$id&resourceId=$resourceId&filename=" . 
                    $row['resourceattachmentsHashFilename']), "_new");
            }
        }
        else
        {
            if (array_key_exists('resourceattachmentsFileName', $row) && !empty($row['resourceattachmentsFileName']))
            {
                $name = \HTML\nlToHtml($row['resourceattachmentsFileName']) . " " .
                    $icons->getIconForAFileExtension($row['resourceattachmentsFileName']);
                $label = $row['resourceattachmentsFileName'];
            }
            else
            {
                if ($reduce)
                {
                    $name = \FORM\reduceLongText(\HTML\nlToHtml($row['resourceattachmentsFileName']));
                }
                else
                {
                    $name = \HTML\nlToHtml($row['resourceattachmentsFileName']);
                }
            }
            if (!$hyperlink)
            {
                return "index.php?action=attachments_ATTACHMENTS_CORE" .
                    htmlentities("&method=downloadAttachment&id=$id&resourceId=$resourceId&filename=" . 
                    $row['resourceattachmentsHashFilename']);
            }
            else
            {
                return \HTML\a('link', $name, "index.php?action=attachments_ATTACHMENTS_CORE" .
                    htmlentities("&method=downloadAttachment&id=$id&resourceId=$resourceId&filename=" . 
                    $row['resourceattachmentsHashFilename']), "_new");
            }
        }
    }
    /**
     * List all attachments for this resource
     *
     * @param int $resourceId
     *
     * @return array
     */
    public function listFiles($resourceId)
    {
        $array = [];
        $this->db->formatConditions(['resourceattachmentsResourceId' => $resourceId]);
        $this->db->orderBy('resourceattachmentsFileName');
        $recordSet = $this->db->select('resource_attachments', ['resourceattachmentsHashFilename',
            'resourceattachmentsFileName', 'resourceattachmentsPrimary', ]);
        while ($row = $this->db->fetchRow($recordSet))
        {
            $array[$row['resourceattachmentsHashFilename']] = \HTML\dbToFormTidy($row['resourceattachmentsFileName']);
            if ($row['resourceattachmentsPrimary'] == 'Y')
            {
                $this->primary = $row['resourceattachmentsHashFilename'];
            }
        }

        return $array;
    }
    /**
     * Increment the accesses and downloads counter for the attachment in the one resource
     *
     * @param int $id
     * @param int $resourceId
     */
    public function incrementDownloadCounter($id, $resourceId)
    {
		$month = date('Ym');
		$this->db->formatConditions(['statisticsattachmentdownloadsResourceId' => $resourceId]);
		$this->db->formatConditions(['statisticsattachmentdownloadsAttachmentId' => $id]);
		$this->db->formatConditions(['statisticsattachmentdownloadsMonth' => $month]);
// insert new month row for this attachment
		if (!$this->db->selectFirstRow('statistics_attachment_downloads', ['statisticsattachmentdownloadsCount'])) 
		{
			$this->db->insert('statistics_attachment_downloads', 
					['statisticsattachmentdownloadsResourceId',
						'statisticsattachmentdownloadsAttachmentId', 
						'statisticsattachmentdownloadsMonth', 
						'statisticsattachmentdownloadsCount'], 
					[$resourceId, 
						$id,
						$month, 
						1]
				); 
		}
		else
		{
			$this->db->formatConditions(['statisticsattachmentdownloadsResourceId' => $resourceId]);
			$this->db->formatConditions(['statisticsattachmentdownloadsAttachmentId' => $id]);
			$this->db->formatConditions(['statisticsattachmentdownloadsMonth' => $month]);
			$this->db->updateSingle('statistics_attachment_downloads', $this->db->formatFields('statisticsattachmentdownloadsCount') . "=" .
				$this->db->formatFields('statisticsattachmentdownloadsCount') . "+" . $this->db->tidyInput(1));
		}
    }
    /**
     * checkAttachmentRows
     * 
     * 1. Check for any rows in table that do not have attachments – if found, delete the row
     * 2. Check for attachments not in the table – if found, delete the attachment
     */
    public function checkAttachmentRows()
    {
        // Delete uncorrelated rows
        $deletes = [];
        $recordSet = $this->db->select('resource_attachments', ['resourceattachmentsId', 'resourceattachmentsHashFilename']);
        while ($row = $this->db->fetchRow($recordSet))
        {
            $fileName = WIKINDX_DIR_DATA_ATTACHMENTS . DIRECTORY_SEPARATOR . $row['resourceattachmentsHashFilename'];
            if (!file_exists($fileName))
            {
                $deletes[] = $row['resourceattachmentsId'];
                $fileNameCache = WIKINDX_DIR_CACHE_ATTACHMENTS . DIRECTORY_SEPARATOR . $row['resourceattachmentsHashFilename'];
                if (file_exists($fileNameCache))
                {
                    @unlink($fileNameCache);
                }
            }
        }
        if (!empty($deletes))
        {
            foreach ($deletes as $id)
            {
                $this->db->formatConditions(["resourceattachmentsId" => $id]);
                $this->db->delete('resource_attachments');
            }
        }
        // Delete uncorrelated attachments
        $deletes = $files = [];
        $cdir = FILE\dirToArray(WIKINDX_DIR_DATA_ATTACHMENTS);
        if (count($cdir) > 0)
        {
            foreach ($cdir as $v)
            {
                if (is_file(WIKINDX_DIR_DATA_ATTACHMENTS . DIRECTORY_SEPARATOR . $v))
                {
                    $files[] = $v;
                }
            }
            foreach ($files as $file)
            {
                $this->db->formatConditions(['resourceattachmentsHashFilename' => $file]);
                if (!$this->db->numRows($this->db->select('resource_attachments', 'resourceattachmentsId')))
                {
                    $deletes[] = $file;
                }
            }
            foreach ($deletes as $file)
            {
                $fileName = WIKINDX_DIR_DATA_ATTACHMENTS . DIRECTORY_SEPARATOR . $file;
                @unlink($fileName);
                $fileNameCache = WIKINDX_DIR_CACHE_ATTACHMENTS . DIRECTORY_SEPARATOR . $file;
                @unlink($fileNameCache);
            }
        }
    }
}
