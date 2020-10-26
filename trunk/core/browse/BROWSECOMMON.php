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
 * Common methods for user bibliographies
 *
 * @package wikindx\core\browse
 */
class BROWSECOMMON
{
    /** object */
    private $db;
    /** array */
    private $vars;
    /** object */
    private $messages;
    /** object */
    private $session;
    /** string */
    private $highColour;
    /** string */
    private $lowColour;
    /** string */
    private $highSize;
    /** string */
    private $lowSize;
    /** string */
    private $sizeDiff;
    /** object */
    private $commonBib;
    /** string */
    private $bibInfo;
    /** int */
    private $userId;

    /**
     * BROWSECOMMON
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();

        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->start = $this->total = 0;
        $this->lowColour = WIKINDX_TAG_LOW_COLOUR;
        $this->highColour = WIKINDX_TAG_HIGH_COLOUR;
        // The CSS font size is in em and the config factor is 100 times the em size
        $this->lowSize = WIKINDX_TAG_LOW_FACTOR / 100;
        $this->highSize = WIKINDX_TAG_HIGH_FACTOR / 100;
        $this->sizeDiff = $this->highSize - $this->lowSize;
        $this->userId = $this->session->getVar('setup_UserId');
    }
    /**
     * Return a SQL condition clause if we are browsing a user bibliography to ensure that
     * listed, selected or searched resources come only from that user bibliography.
     *
     * @param string $field SQL field to join 'userbibliographyresourceResourceId' to
     * @param bool $bibInfo If TRUE, gather bibliography details into $this->bibInfo. Default is TRUE
     */
    public function userBibCondition($field, $bibInfo = TRUE)
    {
        if ($bibInfo) {
            $this->bibInfo = \HTML\nlToHtml($this->commonBib->displayBib());
        }
        if ($useBib = GLOBALS::getUserVar('BrowseBibliography')) {
            $this->db->formatConditions(['userbibliographyresourceBibliographyId' => $useBib]);
            $this->db->leftJoin('user_bibliography_resource', 'userbibliographyresourceResourceId', $field);
        }
    }
    /**
     * Set database conditions for browsing musings and ideas where some entries might be private or available only to groups
     *
     */
    public function setPrivateConditions()
    {
		if ($this->session->getVar("setup_ReadOnly")) {
			$this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
		} elseif ($this->userId) {
			$this->db->formatConditions(['usergroupsusersUserId' => $this->userId]);
			$this->db->formatConditions($this->db->formatFields('usergroupsusersGroupId') . $this->db->equal .
				$this->db->formatFields('resourcemetadataPrivate'));
			$subSql = $this->db->selectNoExecute('user_groups_users', 'usergroupsusersId', FALSE, TRUE, TRUE);
			$subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('N')
				. $this->db->and .
				$this->db->formatFields('resourcemetadataPrivate') . $this->db->notEqual . $this->db->tidyInput('Y');
			$case1 = $this->db->caseWhen($subject, FALSE, $subSql, FALSE, FALSE);
			$subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('Y');
			$result = $this->db->formatFields('resourcemetadataAddUserId') . $this->db->equal . $this->db->tidyInput($this->userId);
			$case2 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
			$subject = $this->db->formatFields('resourcemetadataPrivate') . $this->db->equal . $this->db->tidyInput('N');
			$result = $this->db->tidyInput(1);
			$case3 = $this->db->caseWhen($subject, FALSE, $result, FALSE, FALSE);
			$this->db->formatConditions($case1 . $this->db->or . $case2 . $this->db->or . $case3);
		}
	}
    /**
     * Work out text colour based on field frequency.
     *
     * @param int $lowestSum
     * @param int $highestSum
     * @param int $frequency
     *
     * @return string
     */
    public function colourText($lowestSum, $highestSum, $frequency)
    {
        $highestSum = $highestSum == 0 ? $frequency : $highestSum;
        if ($frequency == $lowestSum) {
            return "#" . $this->lowColour;
        }
        if ($frequency == $highestSum) {
            return "#" . $this->highColour;
        }
        $ratio = $frequency / $highestSum;
        $invRatio = 1 - $ratio;
        // red
        $high = hexdec(mb_substr($this->highColour, 0, 2));
        $low = hexdec(mb_substr($this->lowColour, 0, 2));
        $r = round(($high * $ratio) - ($low * $invRatio));
        $r = $r < 0 ? $r * -1 : $r;
        // green
        $high = hexdec(mb_substr($this->highColour, 2, 2));
        $low = hexdec(mb_substr($this->lowColour, 2, 2));
        $g = round(($high * $ratio) - ($low * $invRatio));
        $g = $g < 0 ? $g * -1 : $g;
        // blue
        $high = hexdec(mb_substr($this->highColour, 4, 2));
        $low = hexdec(mb_substr($this->lowColour, 4, 2));
        $b = round(($high * $ratio) - ($low * $invRatio));
        $b = $b < 0 ? $b * -1 : $b;

        $r = $r < 16 ? '0' . dechex($r) : dechex($r);
        $g = $g < 16 ? '0' . dechex($g) : dechex($g);
        $b = $b < 16 ? '0' . dechex($b) : dechex($b);

        return "#" . $r . $g . $b;
    }
    /**
     * Work out text size based on field frequency.
     *
     * Size range is 1em to 1.5em for items with $index of <= 255 and more for anything larger.
     * Limit maximum size to 2.
     *
     * @param int $lowestSum
     * @param int $highestSum
     * @param int $frequency
     *
     * @return string
     */
    public function sizeText($lowestSum, $highestSum, $frequency)
    {
        $highestSum = $highestSum == 0 ? $frequency : $highestSum;
        $ratio = $frequency / $highestSum;
        $add = $this->sizeDiff * $ratio;
        $size = round($this->lowSize + $add, 2);
        $size = $size < (WIKINDX_TAG_FACTOR_MIN / 100) ? WIKINDX_TAG_FACTOR_MIN / 100 : $size;
        $size = $size > (WIKINDX_TAG_FACTOR_MAX / 100) ? WIKINDX_TAG_FACTOR_MAX / 100 : $size;

        return $size . "em";
    }
    /**
     * Return limited set from final array if paging required
     *
     * @param array $inputArray
     *
     * @return array
     */
    public function paging($inputArray)
    {
        $this->setPaging();
        if ($this->paging <= 0) { // unlimited
            return $inputArray;
        }
        $this->total = count($inputArray);
        // NB - array_slice does not preserve keys
        $keys = array_keys($inputArray);
        $values = array_values($inputArray);
        $keySlice = array_slice($keys, $this->start, $this->paging);
        $valueSlice = array_slice($values, $this->start, $this->paging);
        foreach ($keySlice as $key) {
            $finalArray[$key] = array_shift($valueSlice);
        }

        return $finalArray;
    }
    /**
     * paging links if required
     *
     * @param string $queryString
     */
    public function pagingLinks($queryString)
    {
        if (($this->paging <= 0) || ($this->total <= $this->paging)) {
            return FALSE;
        }
        $end = $advanced = 0;
        $index = $maxLinks = 1;
        $advance = $this->start;
        while ($advance >= (($this->maxLinksHalf * $this->paging) - $this->paging)) {
            $end += $this->paging;
            $index += $this->paging;
            $advance -= $this->paging;
            $advanced++;
        }
        if ($advanced) {
            $links[] = \HTML\a(
                "page",
                $this->messages->text("resources", "pagingStart"),
                "index.php?" . htmlentities($queryString . "&PagingStart=0")
            );
            $maxLinks++;
        }
        while ($index <= $this->total) {
            if ($maxLinks++ >= $this->maxLinks) {
                break;
            }
            $end += $this->paging;
            if ($end > $this->total) {
                $end = $this->total;
            }
            $start = $index - 1;
            $link = htmlentities($queryString . "&PagingStart=$start");
            $name = $index . " - " . $end;
            if ($this->start == $start) {
                $links[] = $name;
            } else {
                $links[] = \HTML\a("page", $name, "index.php?" . $link);
            }
            $index += $this->paging;
        }
        if ($end < $this->total) {
            if ($this->start && count($links) == 1) {
                $links = [\HTML\a(
                    "page",
                    $this->messages->text("resources", "pagingStart"),
                    "index.php?" . htmlentities($queryString . "&PagingStart=0")
                )];
            } elseif (count($links) > 1) {
                $start = $this->total - ($this->total % $this->paging);
                if ($start == $this->total) {
                    $start = $this->total - $this->paging;
                }
                $links[] = \HTML\a(
                    "page",
                    $this->messages->text("resources", "pagingEnd"),
                    "index.php?" . htmlentities($queryString . "&PagingStart=$start")
                );
            }
        }
        GLOBALS::setTplVar('pagingList', $links);
        unset($links);
    }
    /**
     * Format display information string
     */
    public function linksInfo()
    {
        if ($this->paging <= 0) { // unlimited
            return FALSE;
        }
        $displayEnd = $this->start + $this->paging;
        if (($this->paging <= 0) || ($displayEnd > $this->total)) {
            $displayEnd = $this->total;
        }
        $displayStart = $this->start + 1;
        $list['info'] = \HTML\p($this->messages->text("hint", "pagingInfo", " $displayStart - $displayEnd&nbsp;") .
            $this->messages->text("hint", "pagingInfoOf", $this->total));
        GLOBALS::setTplVar('resourceListInfo', $list);
        unset($list);
    }
    /**
     * Set the paging counter
     */
    private function setPaging()
    {
        if (array_key_exists('PagingStart', $this->vars)) {
            $this->start = $this->vars['PagingStart'];
        } else {
            $this->start = 0;
        }
        $this->paging = GLOBALS::getUserVar("PagingTagCloud");
        $this->maxLinks = GLOBALS::getUserVar('PagingMaxLinks');
        $this->maxLinksHalf = round($this->maxLinks / 2);
    }
}
