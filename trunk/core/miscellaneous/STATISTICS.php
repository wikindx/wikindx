<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
    /** object */
    private $co;
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
        $this->co = FACTORY_CONFIGDBSTRUCTURE::getInstance();
    }
    /**
     * Check if statistics need compiling and emailing to registered users.
     *
     * Compiling is done if the date a user accesses wikindx is at least a month after the last compile time.
     */
    public function compile()
    {
        if ($this->session->issetVar('lastStatisticsCompilation'))
        {
            $lastStatisticsCompilation = $this->session->getVar('lastStatisticsCompilation');
        }
        else
        {
            $lastStatisticsCompilation = WIKINDX_STATISTICS_COMPILED;
            $this->session->setVar('lastStatisticsCompilation', $lastStatisticsCompilation);
        }
        // We have to use compute date diff with db functions because we need a date free of user's locals
        $this->runCompile();
    }
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
        $dateDiffClause = $this->db->dateDiffRatio('resourceattachmentsDownloads', 'resourceattachmentsTimestamp', 'downloadRatio', 'AVG');
        $this->db->groupBy('resourceattachmentsResourceId');
        $subQ = $this->db->subQuery(
            $this->db->selectNoExecute('resource_attachments', $dateDiffClause, FALSE, FALSE),
            't',
            TRUE,
            TRUE
        );
        self::$MaxDR = $this->db->selectMax(FALSE, 'downloadRatio', 'max', FALSE, $subQ)['max'];
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
        $dateDiffClause = $this->db->dateDiffRatio('resourcemiscAccesses', 'resourcetimestampTimestampAdd', 'max', 'MAX');
        $this->db->leftJoin('resource_timestamp', 'resourcetimestampId', 'resourcemiscId');
        self::$MaxAR = $this->db->selectFirstField('resource_misc', $dateDiffClause, FALSE, FALSE);
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
        $ratio = (($this->downloadRatio * 1.5) + ($this->accessRatio * 0.5)) / 2; // Give more weight to downloads
        //		$maxRatio = (($this->getMaxDownloadRatio() * 1.5) + ($this->getMaxAccessRatio() * 0.5)) / 2;
        if ($ratio == 0)
        {
            return 0;
        }

        return $ratio;
    }
    /**
     * Return views ratio for each resource
     *
     * @param int $id Resource ID
     *
     * @return int
     */
    public function getAccessRatio($id)
    {
        if (!$this->list && (self::$AR !== FALSE))
        {
            return self::$AR * 100;
        }
        $dateDiffClause = $this->db->dateDiffRatio('resourcemiscAccesses', 'resourcetimestampTimestampAdd', 'accessRatio');
        $this->db->formatConditions(['resourcemiscId' => $id]);
        $this->db->leftJoin('resource_timestamp', 'resourcetimestampId', 'resourcemiscId');
        $thisSum = $this->db->selectFirstField('resource_misc', $dateDiffClause, FALSE, FALSE);
        $setSum = $this->getMaxAccessRatio();
        if ($setSum == 0)
        {
            self::$AR = 0;

            return self::$AR;
        }
        self::$AR = round(($thisSum / $setSum), 2);

        return self::$AR * 100;
    }
    /**
     * Return download ratio of attachments for each resource
     *
     * @param int $id Resource ID
     *
     * @return int
     */
    public function getDownloadRatio($id)
    {
        if (!$this->list && (self::$DR !== FALSE))
        {
            return self::$DR * 100;
        }
        $dateDiffClause = $this->db->dateDiffRatio('resourceattachmentsDownloads', 'resourceattachmentsTimestamp', 'downloadRatio', 'AVG');
        $this->db->formatConditions(['resourceattachmentsResourceId' => $id]);
        $this->db->groupBy('resourceattachmentsResourceId');
        $thisSum = $this->db->selectFirstField('resource_attachments', $dateDiffClause, FALSE, FALSE);
        $setSum = $this->getMaxDownloadRatio();
        if ($setSum == 0)
        {
            self::$DR = 0;

            return self::$DR;
        }
        self::$DR = round(($thisSum / $setSum), 2);

        return self::$DR * 100;
    }
    /**
     * Return resource access and download ratio of attachments for one resource
     *
     * @param int $id Resource ID
     */
    public function accessDownloadRatio($id)
    {
        if (!$this->list && (self::$AR !== FALSE) && (self::$DR !== FALSE))
        {
            $this->accessRatio = self::$AR * 100;
            $this->downloadRatio = self::$DR * 100;

            return;
        }
        $setSumAR = $this->getMaxAccessRatio();
        $setSumDR = $this->getMaxDownloadRatio();
        $aClause = $this->db->dateDiffRatio('resourcemiscAccesses', 'resourcetimestampTimestampAdd', 'accessRatio');
        $dlClause = $this->db->dateDiffRatio('resourceattachmentsDownloads', 'resourceattachmentsTimestamp', 'downloadRatio', 'AVG');
        $this->db->formatConditions(['resourcemiscId' => $id]);
        $this->db->leftJoin('resource_timestamp', 'resourcetimestampId', 'resourcemiscId');
        $this->db->leftJoin('resource_attachments', 'resourceattachmentsResourceId', 'resourcemiscId');
        $this->db->groupBy(['resourcemiscAccesses', 'resourcetimestampTimestampAdd']);
        $resultSet = $this->db->select('resource_misc', [$aClause, $dlClause], FALSE, FALSE);
        while ($row = $this->db->fetchRow($resultSet))
        {
            if ($setSumAR == 0)
            {
                self::$AR = 0;
            }
            else
            {
                self::$AR = round(($row['accessRatio'] / $setSumAR), 2);
            }
            if ($setSumDR == 0)
            {
                self::$DR = 0;
            }
            else
            {
                self::$DR = round(($row['downloadRatio'] / $setSumDR), 2);
            }
            $this->accessRatio = self::$AR * 100;
            $this->downloadRatio = self::$DR * 100;
        }
    }
    /**
     * Run the statistics compilation and manage any emailing required
     */
    private function runCompile()
    {
        // Delete the last date of compilation cached in session
        // and check in db if the statistics have not been compiled by another running process
        // If compiled, abort
        $this->session->delVar('lastStatisticsCompilation');
        $lastStatisticsCompilation = WIKINDX_STATISTICS_COMPILED;
        if (!$this->db->monthDiff($lastStatisticsCompilation))
        {
            return;
        }
        // Note that we run statistics compilation the first day of the current month
        // We use db functions because we need a date free of users locals
        $this->db->formatConditions(['configName' => 'configStatisticsCompiled']);
        $this->db->updateTimestamp('config', ['configDatetime' => $this->db->firstDayOfCurrentMonth()]);
        $lastStatisticsCompilation = WIKINDX_STATISTICS_COMPILED;
        $this->session->setVar('lastStatisticsCompilation', $lastStatisticsCompilation);
        $emailStats = WIKINDX_EMAIL_STATISTICS;
        if ($emailStats && WIKINDX_MAIL_USE)
        {
            $emailStats = TRUE;
        }
        else
        {
            $emailStats = FALSE;
        }
        $thisMonth = date('Ym');
        $resArray = [];
        $maxPacket = $this->db->getMaxPacket();
// For each 1MB max_allowed_packet (1048576 bytes), the value of 10 here seems to be fine – based on trial and error – at balancing 
// efficiency/time against memory use.
        $maxCounts = floor(10 * ($maxPacket / 1048576));
        $numberOfResources = $this->db->selectFirstField("database_summary", "databasesummaryTotalResources");
        // Add all resource views and attachment downloads for this period to statistics table and gather any user details
        // Resource statistics first
        $index = 0;
		$extraConditions[] = $this->db->formatConditions(['statisticsAttachmentId' => ' IS NULL'], FALSE, TRUE);
        do
        {
        	$count = 0;    
			$this->db->leftJoin('statistics', 'statisticsResourceId', 'resourcemiscId');
			$this->db->formatConditions(['statisticsAttachmentId' => ' IS NULL']);
			$this->db->limit($maxCounts, $index);
			$resultset = $this->db->select('resource_misc', ['resourcemiscId', 'resourcemiscAccessesPeriod',
				'resourcemiscAccesses', 'statisticsStatistics', ]);
			$updateArray = [];
			while ($row = $this->db->fetchRow($resultset))
			{
				if ($emailStats)
				{
					$resArray[$row['resourcemiscId']] = ['viewsPeriod' => $row['resourcemiscAccessesPeriod'], 'viewsTotal' => $row['resourcemiscAccesses']];
				}
				$rowStats = $row['statisticsStatistics'];
				if ($rowStats)
				{
					$stats = unserialize(base64_decode($rowStats));
				}
				else
				{
					$stats = [];
				}
				$stats[$thisMonth] = $row['resourcemiscAccessesPeriod'];
				$updateArray[$row['resourcemiscId']] = base64_encode(serialize($stats));
				++$count;
        	}
        	if (count($updateArray) > 0)
        	{
			    $this->db->multiUpdate('statistics', 'statisticsStatistics', 'statisticsResourceId', $updateArray, $extraConditions);
			}
        	$index += $count;
        }
        while ($index < $numberOfResources);
        $this->db->update('resource_misc', ['resourcemiscAccessesPeriod' => '0']);
        $this->db->update('resource_attachments', ['resourceattachmentsDownloadsPeriod' => '0']);
        if ($emailStats)
        {
            $this->emailStats($resArray);
        }
    }
    /**
     * Email users various stats
     *
     * @param array $resArray
     */
    private function emailStats($resArray)
    {
        $res = FACTORY_RESOURCECOMMON::getInstance();
        $bibStyle = FACTORY_BIBSTYLE::getInstance('plain');
        $usersArray = [];
        $this->list = TRUE;
        foreach ($resArray as $rId => $rArray)
        {
            $union = [];
            $this->db->formatConditions(['resourcecreatorResourceId' => $rId]);
            $union[] = $this->db->selectNoExecute('resource_creator', 'resourcecreatorCreatorId', FALSE, TRUE, TRUE);
            $this->db->formatConditions(['resourcecreatorResourceId' => $rId]);
            $this->db->leftJoin('resource_creator', 'resourcecreatorCreatorId', 'creatorId');
            $union[] = $this->db->selectNoExecute('creator', 'creatorSameAs', TRUE, TRUE, TRUE);
            $this->db->formatConditions($this->db->formatFields('usersIsCreator') . $this->db->inClause($this->db->union($union)));
            $resultset = $this->db->select('users', ['usersId', 'usersFullname', 'usersEmail']);
            while ($row = $this->db->fetchRow($resultset))
            {
                if (!array_key_exists($row['usersId'], $usersArray))
                {
                    $usersArray[$row['usersId']]['email'] = $row['usersEmail'];
                    $usersArray[$row['usersId']]['name'] = $row['usersFullname'];
                }
                $title = $bibStyle->process($this->db->fetchRow($res->getResource($rId)), TRUE);
                $usersArray[$row['usersId']]['resources'][$rId] = $title;
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
                    //					$viewsIndex = $this->accessRatio($rId);
                    //					$downloadsIndex = $this->downloadRatio($rId);
                    $this->accessDownloadRatio($rId);
                    $popIndex = $this->getPopularityIndex($rId);
                    $message .= $title . LF;
                    $message .= TAB . $this->messages->text('statistics', 'emailViewsMonth', $resArray[$rId]['viewsPeriod']) . ' ' .
                        $this->messages->text('statistics', 'emailViewsTotal', $resArray[$rId]['viewsTotal']) . ', ' .
                        $this->messages->text('viewResource', 'viewIndex', $this->accessRatio) . ', ' .
                        $this->messages->text('viewResource', 'download', $this->downloadRatio) . ', ' .
                        $this->messages->text('viewResource', 'popIndex', $popIndex) . LF;
                    if (array_key_exists('downloads', $resArray[$rId]))
                    {
                        if (count($resArray[$rId]['downloads']) == 1)
                        { // just the one attachment
                            $dArray = array_shift($resArray[$rId]['downloads']);
                            $message .= TAB . $this->messages->text('statistics', 'emailDownloadsMonth', $dArray[2]) . ' ' .
                                $this->messages->text('statistics', 'emailDownloadsTotal', $dArray[1]) . LF;
                        }
                        else
                        {
                            foreach ($resArray[$rId]['downloads'] as $dArray)
                            {
                                $message .= TAB . $dArray[0] . ' -- ' .
                                    $this->messages->text('statistics', 'emailDownloadsMonth', $dArray[2]) . ' ' .
                                    $this->messages->text('statistics', 'emailDownloadsTotal', $dArray[1]) . LF;
                            }
                        }
                    }
                    $message .= LF;
                }
                $smtp->sendEmail($uArray['email'], $subject, $message);
            }
        }
    }
    /**
     * Return the no. attachment downloads for the resource with the greatest number of downloads
     *
     * @return int
     */
    private function getMaxDownloads()
    {
        if (self::$maxDownloads !== FALSE)
        {
            return self::$maxDownloads;
        }
        $this->db->groupBy('resourceattachmentsResourceId');
        $subQ = $this->db->subQuery($this->db->selectNoExecute(
            'resource_attachments',
            $this->db->sum('resourceattachmentsDownloads', 'sum'),
            FALSE,
            FALSE
        ), 't', TRUE, TRUE);
        $resultset = $this->db->selectMax(FALSE, 'sum', 'max', FALSE, $subQ);
        self::$maxDownloads = $resultset['max'];

        return self::$maxDownloads;
    }
    /**
     * Return the no. views for the resource with the greatest number of views
     *
     * @return int
     */
    private function getMaxAccesses()
    {
        if (self::$maxAccesses !== FALSE)
        {
            return self::$maxAccesses;
        }
        else
        {
            $resultset = $this->db->selectMax("resource_misc", 'resourcemiscAccesses');
            self::$maxAccesses = $resultset['resourcemiscAccesses'];

            return self::$maxAccesses;
        }
    }
}
