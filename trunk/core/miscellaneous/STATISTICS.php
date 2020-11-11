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
 * STATISTICS
 *
 * WIKINDX statistics
 *
 * @package wikindx\core\miscellaneous
 */
class STATISTICS
{
    /** int */
    public $downloadRatio = FALSE;
    /** int */
    public $accessRatio = FALSE;
    /** boolean */
    public $list = FALSE;
    /** object */
    private $db;
    /** array */
    private $vars;
    /** object */
    private $session;
    /** object */
    private $messages;
    /** int */
    private static $maxAccesses = FALSE;
    /** int */
    private static $maxDownloads = FALSE;
    /** int */
    private static $AR = FALSE;
    /** int */
    private static $DR = FALSE;
    /** int */
    private static $MaxAR = FALSE;
    /** int */
    private static $MaxDR = FALSE;

    /**
     * STATISTICS
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
    }
    /**
     * Check if statistics need compiling and emailing to registered users.
     *
     * Compiling is done if the date a user accesses wikindx is at least a month after the last compile time.
     */
    /**
     * Calculate the maximum download ratio in the database
     *
     * @return int
     */
    public function getMaxDownloadRatio()
    {
        if (self::$MaxDR !== FALSE)
        {
            return self::$MaxDR;
        }
        $dateDiffClause = $this->db->dateDiffRatio('count', 'min', 'max', 'MAX');
        $sumClause = $this->db->sum('statisticsattachmentdownloadsCount', 'count');
        $this->db->formatConditions(['statisticsattachmentdownloadsAttachmentId' => 'IS NOT NULL']);
        $this->db->leftJoin('resource_attachments', 'resourceattachmentsId', 'statisticsattachmentdownloadsAttachmentId');
        $this->db->groupBy('statisticsattachmentdownloadsResourceId');
        $subQ = $this->db->subQuery(
            $this->db->selectNoExecute(
                'statistics_attachment_downloads',
                [$sumClause, $this->db->minStmt($this->db->formatFields('resourceattachmentsTimestamp'), $this->db->formatFields('min'))],
                FALSE,
                FALSE
            ),
            't',
            TRUE,
            TRUE
        );
        $resultSet = $this->db->selectFromSubQuery(FALSE, $dateDiffClause, $subQ, FALSE, FALSE);
        self::$MaxDR = $this->db->fetchOne($resultSet);
        if (!self::$MaxDR)
        {
            return 1;
        }
        else
        {
            return self::$MaxDR;
        }
    }
    /**
     * Calculate the maximum access ratio in the database
     *
     * @return int
     */
    public function getMaxAccessRatio()
    {
        if (self::$MaxAR !== FALSE)
        {
            return self::$MaxAR;
        }
        $dateDiffClause = $this->db->dateDiffRatio('count', 'resourcetimestampTimestampAdd', 'max', 'MAX');
        $sumClause = $this->db->sum('statisticsresourceviewsCount', 'count');
        $this->db->leftJoin('resource_timestamp', 'resourcetimestampId', 'statisticsresourceviewsResourceId');
        $this->db->groupBy('statisticsresourceviewsResourceId');
        $subQ = $this->db->subQuery(
            $this->db->selectNoExecute(
                'statistics_resource_views',
                [$sumClause, $this->db->formatFields('resourcetimestampTimestampAdd')],
                FALSE,
                FALSE
            ),
            't',
            TRUE,
            TRUE
        );
        $resultSet = $this->db->selectFromSubQuery(FALSE, $dateDiffClause, $subQ, FALSE, FALSE);
        self::$MaxAR = $this->db->fetchOne($resultSet);
        if (!self::$MaxAR)
        {
            return 1;
        }
        else
        {
            return self::$MaxAR;
        }
    }
    /**
     * return the popularity index for a resource
     *
     * @param int $id Resource ID
     *
     * @return int
     */
    public function getPopularityIndex($id)
    {
        if ($this->list)
        {
            $this->accessRatio = $this->downloadRatio = FALSE;
        }
        if (!$this->accessRatio && !$this->downloadRatio)
        {
            $this->accessDownloadRatio($id);
        }
        $ratio = (($this->downloadRatio * WIKINDX_POPULARITY_DOWNLOADS_WEIGHT) +
            ($this->accessRatio * WIKINDX_POPULARITY_VIEWS_WEIGHT)); // Give more weight to downloads
        //		$maxRatio = (($this->getMaxDownloadRatio() * 1.5) + ($this->getMaxAccessRatio() * 0.5)) / 2;
        if ($ratio == 0)
        {
            return 0;
        }

        return $ratio;
    }
    /**
     * Return resource access and download ratio of attachments for one resource
     *
     * @param int $id Resource ID
     * @param bool $arOnly = FALSE
     */
    public function accessDownloadRatio($id, $arOnly = FALSE)
    {
        if (!$this->list && (self::$AR !== FALSE) && (self::$DR !== FALSE))
        {
            $this->accessRatio = self::$AR * 100;
            $this->downloadRatio = self::$DR * 100;

            return;
        }
        if (!$arOnly)
        {
            $this->getDownloadRatio($id);
        }
        $this->getAccessRatio($id);
    }
    /**
     * Run the statistics compilation and manage any emailing required
     */
    public function runCompile()
    {
        // Delete the last date of compilation cached in session
        // and check in db if the statistics have not been compiled by another running process
        // If compiled, abort
        if (!$this->db->monthDiff(WIKINDX_STATISTICS_COMPILED))
        {
            return;
        }
        // Note that we run statistics compilation from the first day of the current month
        // We use db functions because we need a date free of users locals
        $this->db->formatConditions(['configName' => 'configStatisticsCompiled']);
        $this->db->updateTimestamp('config', ['configDatetime' => $this->db->firstDayOfCurrentMonth()]);
        $emailStats = WIKINDX_EMAIL_STATISTICS;
        if (WIKINDX_EMAIL_STATISTICS && WIKINDX_MAIL_USE)
        {
            $this->emailStats();
        }
    }
    /**
     * Get a resource's access ratio
     *
     * @param int $id Resource ID
     */
    private function getAccessRatio($id)
    {
        $setSumAR = $this->getMaxAccessRatio();
        $dateDiffClause = $this->db->dateDiffRatio('count', 'resourcetimestampTimestampAdd', 'accessRatio');
        $sumClause = $this->db->sum('statisticsresourceviewsCount', 'count');
        $this->db->formatConditions(['statisticsresourceviewsResourceId' => $id]);
        $this->db->leftJoin('resource_timestamp', 'resourcetimestampId', 'statisticsresourceviewsResourceId');
        $subQ = $this->db->subQuery(
            $this->db->selectNoExecute(
                'statistics_resource_views',
                [$sumClause, $this->db->formatFields('resourcetimestampTimestampAdd')],
                FALSE,
                FALSE
            ),
            't',
            TRUE,
            TRUE
        );
        $resultSet = $this->db->selectFromSubQuery(FALSE, $dateDiffClause, $subQ, FALSE, FALSE);
        $accessRatio = $this->db->fetchOne($resultSet);
        if (!$setSumAR)
        {
            self::$AR = 0;
        }
        else
        {
            self::$AR = round(($accessRatio / $setSumAR), 2);
        }
        $this->accessRatio = self::$AR * 100;
    }
    /**
     * Get a resource's download ratio
     *
     * @param int $id Resource ID
     */
    private function getDownloadRatio($id)
    {
        $setSumDR = $this->getMaxDownloadRatio();
        $dateDiffClause = $this->db->dateDiffRatio('count', 'min', 'downloadRatio', '', 3, FALSE, FALSE, 'min');
        $sumClause = $this->db->sum('statisticsattachmentdownloadsCount', 'count');
        $this->db->formatConditions(['statisticsattachmentdownloadsAttachmentId' => 'IS NOT NULL']);
        $this->db->formatConditions(['statisticsattachmentdownloadsResourceId' => $id]);
        $this->db->leftJoin('resource_attachments', 'resourceattachmentsId', 'statisticsattachmentdownloadsAttachmentId');
        $subQ = $this->db->subQuery(
            $this->db->selectNoExecute(
                'statistics_attachment_downloads',
                [$sumClause, $this->db->minStmt($this->db->formatFields('resourceattachmentsTimestamp'), $this->db->formatFields('min'))],
                FALSE,
                FALSE
            ),
            't',
            TRUE,
            TRUE
        );
        $resultSet = $this->db->selectFromSubQuery(FALSE, $dateDiffClause, $subQ, FALSE, FALSE);
        $downloadRatio = $this->db->fetchOne($resultSet);
        if (!$setSumDR)
        {
            self::$DR = 0;
        }
        else
        {
            self::$DR = round(($downloadRatio / $setSumDR), 2);
        }
        $this->downloadRatio = self::$DR * 100;
    }
    /**
     * Email users various stats if the user is a creator
     */
    private function emailStats()
    {
        $res = FACTORY_RESOURCECOMMON::getInstance();
        $bibStyle = FACTORY_BIBSTYLE::getInstance('plain');
        $usersArray = $resArray = [];
        $this->list = TRUE;
        // get statistics for the last month
        $month = date('Ym');
        // Shift of one month back
        $month = $month - 1;
        // Month 0 doesn't exist, so shift one year back on December
        $month = ($month % 100 == 0) ? $month - 100 + 12 : $month;
        $this->db->formatConditions(['statisticsresourceviewsMonth' => $month]);
        $this->db->formatConditions(['statisticsresourceviewsCount' => ' IS NOT NULL']);
        $resultset = $this->db->select('statistics_resource_views', ['statisticsresourceviewsResourceId', 'statisticsresourceviewsCount']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $resArray[$row['statisticsresourceviewsResourceId']]['monthViews'] = $row['statisticsresourceviewsCount'];
            $resIds[] = $row['statisticsresourceviewsResourceId'];
        }
        if (!isset($resIds))
        {
            return;
        }
        $this->db->formatConditions(['statisticsattachmentdownloadsMonth' => $month]);
        $this->db->formatConditions(['statisticsattachmentdownloadsCount' => ' IS NOT NULL']);
        $this->db->leftJoin('resource_attachments', 'resourceattachmentsId', 'statisticsattachmentdownloadsAttachmentId');
        $resultset = $this->db->select(
            'statistics_attachment_downloads',
            ['statisticsattachmentdownloadsResourceId', 'statisticsattachmentdownloadsAttachmentId', 'statisticsattachmentdownloadsCount',
                'resourceattachmentsFileName', ]
        );
        while ($row = $this->db->fetchRow($resultset))
        {
            $resArray[$row['statisticsattachmentdownloadsResourceId']]['monthDownloads'][$row['statisticsattachmentdownloadsAttachmentId']] =
                $row['statisticsattachmentdownloadsCount'];
            $resArray[$row['statisticsattachmentdownloadsResourceId']]['filename'][$row['statisticsattachmentdownloadsAttachmentId']] =
                $row['resourceattachmentsFileName'];
        }
        $this->db->formatConditionsOneField($resIds, 'resourcecreatorResourceId');
        $this->db->formatConditions('resourcecreatorCreatorId', 'IS NOT NULL');
        $resultset = $this->db->select('resource_creator', ['resourcecreatorCreatorId', 'resourcecreatorResourceId']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $creatorIds[$row['resourcecreatorResourceId']] = $row['resourcecreatorCreatorId'];
        }
        $this->db->formatConditionsOneField($resIds, 'resourcecreatorResourceId');
        $this->db->formatConditions('creatorSameAs', 'IS NOT NULL');
        $this->db->leftJoin('resource_creator', 'resourcecreatorCreatorId', 'creatorId');
        $resultset = $this->db->select('creator', ['creatorSameAs', 'resourcecreatorResourceId']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $creatorIds[$row['resourcecreatorResourceId']] = $row['creatorSameAs'];
        }
        if (!isset($creatorIds))
        {
            return;
        }
        $this->db->formatConditionsOneField(array_unique($creatorIds), 'usersIsCreator');
        $resultset = $this->db->select('users', ['usersId', 'usersFullname', 'usersEmail', 'usersIsCreator']);
        while ($row = $this->db->fetchRow($resultset))
        {
            if (!array_key_exists($row['usersId'], $usersArray))
            {
                $usersArray[$row['usersId']]['email'] = $row['usersEmail'];
                $usersArray[$row['usersId']]['name'] = $row['usersFullname'];
            }
            foreach ($creatorIds as $rId => $uId)
            {
                if ($row['usersIsCreator'] == $uId)
                {
                    $resource = $res->getResource($rId);
                    $title = $bibStyle->process($this->db->fetchRow($resource), TRUE);
                    $usersArray[$row['usersId']]['resources'][$rId] = $title;
                }
            }
        }
        if (!empty($usersArray))
        {
            $smtp = FACTORY_MAIL::getInstance();
            $subject = $this->messages->text('statistics', 'emailSubject', WIKINDX_TITLE);
            foreach ($usersArray as $uArray)
            {
                $message = $uArray['name'] . LF . LF;
                $message .= $this->messages->text('statistics', 'emailIntro', WIKINDX_TITLE) . LF . LF;
                foreach ($uArray['resources'] as $rId => $title)
                {
                    $this->db->formatConditions(['statisticsresourceviewsResourceId' => $rId]);
                    $sum = $this->db->selectFirstField(
                        'statistics_resource_views',
                        $this->db->sum('statisticsresourceviewsCount', 'count'),
                        FALSE,
                        FALSE
                    );
                    $popIndex = $this->getPopularityIndex($rId);
                    $message .= $title . LF;
                    $message .= TAB . $this->messages->text('statistics', 'emailViewsMonth', $resArray[$rId]['monthViews']) . ' ' .
                        $this->messages->text('statistics', 'emailViewsTotal', $sum) . ', ' .
                        $this->messages->text('viewResource', 'viewIndex', $this->accessRatio) . ', ' .
                        $this->messages->text('viewResource', 'download', $this->downloadRatio) . ', ' .
                        $this->messages->text('viewResource', 'popIndex', $popIndex) . LF;
                    if (array_key_exists('monthDownloads', $resArray[$rId]))
                    {
                        foreach ($resArray[$rId]['monthDownloads'] as $id => $count)
                        {
                            $this->db->formatConditions(['statisticsattachmentdownloadsAttachmentId' => $id]);
                            $sum = $this->db->selectFirstField(
                                'statistics_attachment_downloads',
                                $this->db->sum('statisticsattachmentdownloadsCount', 'count'),
                                FALSE,
                                FALSE
                            );
                            $name = $resArray[$rId]['filename'][$id];
                            $message .= TAB . $name . ': ' . $this->messages->text('statistics', 'emailDownloadsMonth', $count) . ' ' .
                                $this->messages->text('statistics', 'emailDownloadsTotal', $sum) . LF;
                        }
                    }
                    $message .= LF;
                }
                $smtp->sendEmail($uArray['email'], $subject, $message);
            }
        }
    }
}
